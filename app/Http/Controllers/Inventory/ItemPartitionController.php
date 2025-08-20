<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\ExportItemPartitionDetail;
use App\Helpers\CustomHelper;
use Illuminate\Support\Facades\Storage;
use App\Helpers\PrintHelper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Division;
use App\Models\ItemMove;
use App\Models\ItemPartition;
use App\Models\ItemPartitionDetail;
use App\Models\ItemStockNew;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\Place;
use App\Models\User;
use iio\libmergepdf\Merger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ItemPartitionController extends Controller
{
    public function __construct(){
        $user = User::find(session('bo_id'));
    }

    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $data = [
            'title'     => 'Partisi Item di Gudang',
            'content'   => 'admin.inventory.item_partition',
            'company'   => Company::where('status','1')->get(),
            'place'     => Place::where('status','1')->get(),
            'department'=> Division::where('status','1')->orderBy('name')->get(),
            'minDate'   => $request->get('minDate'),
            'maxDate'   => $request->get('maxDate'),
            'newcode'   => $menu->document_code.date('y'),
            'menucode'  => $menu->document_code,
            'code'      => $request->code ? CustomHelper::decrypt($request->code) : '',
            'modedata'  => $menuUser->mode ? $menuUser->mode : '',
        ];
        $document_code = $menu->document_code . date('y');
        session(['document_code' => $document_code]);

        return view('admin.layouts.index', ['data' => $data]);
    }


   public function getCode(Request $request){
        $lastSegment = request()->segment(count(request()->segments())); // 'inventory_issue'
        $segments = request()->segments();
        $lastSegment = $segments[count($segments) - 2];

        $menu = Menu::where('url', $lastSegment)->first();
        $document_code = $menu?->document_code . date('y');

        session(['document_code' => $document_code]);

        if (!$document_code) {
            return response()->json(['error' => 'Document code not found'], 400);
        }

        $code = ItemPartition::generateCode($document_code);

        return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'company_id',
            'post_date',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ItemPartition::/* whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")-> */where(function($query)use($request){
            if(!$request->modedata){
                $query->where('user_id',session('bo_id'));
            }
        })->count();

        $query_data = ItemPartition::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('ItemPartitionDetail', function($query) use($search, $request){
                                $query->whereHas('itemStock',function($query) use($search, $request){
                                    $query->whereHas('item',function($query) use($search, $request){
                                        $query->where('code', 'like', "%$search%")
                                            ->orWhere('name','like',"%$search%");
                                    });
                                });
                            })
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }


            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = ItemPartition::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('ItemPartitionDetail', function($query) use($search, $request){
                                $query->whereHas('itemStock',function($query) use($search, $request){
                                    $query->whereHas('item',function($query) use($search, $request){
                                        $query->where('code', 'like', "%$search%")
                                            ->orWhere('name','like',"%$search%");
                                    });
                                });
                            })
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }

            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $dis = '';


                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">info_outline</i></button>',
                    $val->code,
                    $val->user->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->note,
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat purple accent-2 white-text btn-small" data-popup="tooltip" title="Selesai" onclick="done(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">gavel</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" '.$dis.' onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>

                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">delete</i></button>
					'
                ];

                $nomor++;
            }
        }

        $response['recordsTotal'] = 0;
        if($total_data <> FALSE) {
            $response['recordsTotal'] = $total_data;
        }

        $response['recordsFiltered'] = 0;
        if($total_filtered <> FALSE) {
            $response['recordsFiltered'] = $total_filtered;
        }

        return response()->json($response);
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'code'                      => 'required',
			'post_date'		            => 'required',
            'arr_item_stock'            => 'required|array',
            'arr_qty'                   => 'required|array',
		], [
            'code.required' 				    => 'Kode/No tidak boleh kosong.',
			'post_date.required' 				=> 'Tanggal posting tidak boleh kosong.',
            'arr_item_stock.required'           => 'Item stok tidak boleh kosong',
            'arr_item_stock.array'              => 'Item stok harus dalam bentuk array',
            'arr_qty.required'                  => 'Qty item tidak boleh kosong',
            'arr_qty.array'                     => 'Qty item harus dalam bentuk array',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
                $grandtotal = 0;
                $passed = true;
                $passedQtyMinus = true;
                $arrItemNotPassed = [];
                $passedZeroQty = true;
                $arr_item_stock = $request->arr_item_stock;
                $arr_qty = $request->arr_qty;


                $keys = array_keys($arr_item_stock);

                array_multisort($arr_item_stock, SORT_ASC, $arr_qty, $keys);
                $cumulative_qty = [];

                $x=0;
                foreach ($arr_item_stock as $key_arr => $row_stock) {
                    // Remove commas and convert qty to int
                    $qty = str_replace(',','.',str_replace('.','',$arr_qty[$key_arr]));
                    $qty = floatval($qty);

                    // If the current item matches the previous one, accumulate the quantity
                    if ($key_arr > 0 && $row_stock == $arr_item_stock[$key_arr - 1]) {
                        $cumulative_qty[$x] = $cumulative_qty[$x] + $qty;
                    } else { // Otherwise, set the quantity directly
                        if($key_arr>0){
                            $x++;
                        }
                        $cumulative_qty[$x] = $qty;
                    }
                }
                $unique_array = array_unique($arr_item_stock);
                $unique_array = array_values($unique_array);


                foreach($unique_array as $key => $row){
                    if($cumulative_qty[$key] <= 0){
                        $passedZeroQty = false;
                    }
                    $rowprice = NULL;
                    $item_stock = ItemStockNew::where('id',$row)->first();
                    $rowprice = $item_stock->priceDate($request->post_date);
                    $grandtotal += round($rowprice * $cumulative_qty[$key],2);
                    if($item_stock){

                        $qtyout = round($cumulative_qty[$key],3);

                        $itemCogsBefore = ItemMove::where('item_id',$item_stock->item_id)->orderByDesc('date')->orderByDesc('id')->first();
                        $itemCogsAfter = ItemMove::where('item_id',$item_stock->item_id)->whereDate('date','>',$request->post_date)->orderBy('date')->orderBy('id')->get();

                        if($itemCogsBefore){
                            $cogs = $itemCogsBefore->infoFg();
                            if(!$request->temp){
                                if(round(($cogs['qty'] - $qtyout),3) < 0){
                                    $passedQtyMinus = false;
                                    $arrItemNotPassed[] = $item_stock->item->name;
                                }
                                $startqty = $cogs['qty'] - $qtyout;
                                foreach($itemCogsAfter as $row){
                                    if($row->type == 1){
                                        $startqty += round($row->qty_in,3);
                                    }elseif($row->type == 2){
                                        $startqty -= round($row->qty_out,3);
                                    }
                                    if($startqty < 0){
                                        $passedQtyMinus = false;
                                        $arrItemNotPassed[] = $item_stock->item->name;
                                    }
                                }
                            }else{
                                if(round(($cogs['qty'] + $qtyout),3) < $qtyout){
                                    $passedQtyMinus = false;
                                    $arrItemNotPassed[] = $item_stock->item->name;
                                }
                            }

                        }else{
                            $passed = false;
                            $arrItemNotPassed[] = $item_stock->item->name;
                        }

                    }
                }

                if(!$request->temp){

                    if($passedZeroQty == false){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Maaf, qty tidak boleh 0.',
                        ]);
                    }

                    if($passedQtyMinus == false){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Maaf, pada tanggal setelah tanggal posting terdapat qty minus pada stok. Barang '.implode(", ",$arrItemNotPassed),
                        ]);
                    }

                    if($passed == false){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Maaf, pada tanggal '.date('d/m/Y',strtotime($request->post_date)).', barang '.implode(", ",$arrItemNotPassed).', stok tidak tersedia atau melebihi stok yang tersedia.',
                        ]);
                    }

                }else{
                    if($passedQtyMinus == false){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Maaf, pada tanggal setelah tanggal posting terdapat qty minus pada stok. Barang '.implode(", ",$arrItemNotPassed),
                        ]);
                    }
                }

                if($request->temp){
                    $query = ItemPartition::where('code',CustomHelper::decrypt($request->temp))->first();


                    if(in_array($query->status,['1','2','3','6']) && in_array(session('bo_division_id'),[20,18])){
                        if($request->has('file')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('file')->store('public/delivery_receives');
                        } else {
                            $document = $query->document;
                        }
                        $query->user_id = $request->account_id;
                        $query->receiver_name = $request->receiver_name;
                        $query->post_date = $request->post_date;
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->grandtotal = round($grandtotal,2);
                        $query->status = '3';

                        $query->save();

                        $existingDetails = ItemPartitionDetail::where('item_partition_id', $query->id)->get();

                        foreach ($existingDetails as $detail) {
                            // OUT movement reversal
                            $item_out = ItemStockNew::where('id', $detail->to_item_stock_new_id)->first();
                            if ($item_out) {
                                $item_out->qty += $detail->qty; // restore the qty that was reduced
                                $item_out->save();
                            }

                            // IN movement reversal
                            $item_in = ItemStockNew::where('item_id', $detail->item_stock_new_id)->first(); // assuming you store the 'item_id' for store
                            if ($item_in) {
                                $item_in->qty -= $detail->qty_store_item; // remove the qty that was added
                                $item_in->save();
                            }

                            // Remove both move logs (in and out)
                            ItemMove::where('lookable_type', $query->getTable())
                                ->where('lookable_id', $query->id)
                                ->where(function ($q) use ($detail) {
                                    $q->where('item_id', $detail->item_id)
                                    ->orWhere('item_id', optional($detail->itemStockNew)->item_id);
                                })->delete(); // or forceDelete()

                            // Optional: soft delete the detail if not already
                            $detail->delete();
                        }


                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Status barang keluar sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }else{
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=ItemPartition::generateCode($menu->document_code.date('y',strtotime($request->post_date)));

                    $query = ItemPartition::create([
                        'code'			        => $newCode,
                        'user_id'		        => session('bo_id'),
                        'post_date'             => $request->post_date,
                        'document'              => $request->file('file') ? $request->file('file')->store('public/inventory_issues') : NULL,
                        'note'                  => $request->note,
                        'status'                => '3',
                        'grandtotal'            => round($grandtotal,2)
                    ]);
                }

                if($query) {

                    if(!$request->temp){




                    }else{
                        $oldDetails = ItemPartitionDetail::onlyTrashed()
                            ->where('item_partition_id', $query->id)
                            ->get();

                        foreach ($oldDetails as $old) {
                            $item_out = ItemStockNew::where('id', $old->item_stock_new_id)->first();
                            $item_in  = ItemStockNew::where('id', $old->to_item_stock_new_id)->first();
                            $qty = $old->qty;
                            $total = $old->total;

                            // revert stock out
                            if ($item_out) {
                                $item_out->qty += $qty;
                                $item_out->save();

                                // add reversal move
                                ItemMove::create([
                                    'lookable_type' => $query->getTable(),
                                    'lookable_id' => $query->id,
                                    'item_id' => $item_out->item_id,
                                    'qty_in' => $qty,
                                    'price_in' => $old->price,
                                    'total_in' => $total,
                                    'qty_out' => 0,
                                    'price_out' => 0,
                                    'total_out' => 0,
                                    'qty_final' => 0,
                                    'price_final' => 0,
                                    'total_final' => 0,
                                    'date' => now(),
                                    'type' => 1, // reversal
                                ]);
                            }

                            // revert stock in
                            if ($item_in) {
                                $item_in->qty -= $qty;
                                $item_in->save();

                                ItemMove::create([
                                    'lookable_type' => $query->getTable(),
                                    'lookable_id' => $query->id,
                                    'item_id' => $item_in->item_id,
                                    'qty_in' => 0,
                                    'price_in' => 0,
                                    'total_in' => 0,
                                    'qty_out' => $qty,
                                    'price_out' => $old->price,
                                    'total_out' => $total,
                                    'qty_final' => 0,
                                    'price_final' => 0,
                                    'total_final' => 0,
                                    'date' => now(),
                                    'type' => 2,
                                ]);
                            }
                        }

                        // Now restore the soft-deleted details so they don't pile up
                        ItemPartitionDetail::onlyTrashed()->where('item_partition_id', $query->id)->forceDelete();

                        // ✅ Now re-run the "create new" logic exactly as you have in the `if` block
                        // Suggestion: You can extract the big foreach in the `if` into a separate function
                        // and call it from both blocks to reduce duplication.
                    }
                    foreach($request->arr_item_stock as $key => $row){
                        $rowprice = NULL;
                        $item_stock = ItemStockNew::where('item_id',$row)->first();
                        $rowprice = $item_stock->priceDate($query->post_date);
                        $total = $rowprice * str_replace(',','.',str_replace('.','',$request->arr_qty[$key]));
                        $gid = ItemPartitionDetail::create([
                            'item_partition_id'    => $query->id,
                            'item_stock_new_id'    => $row,
                            'to_item_stock_new_id' => $request->arr_item_stock_store[$key],
                            'qty'                  => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            'price'                => $rowprice,
                            'total'                => $total,
                            'note'                 => $request->arr_note[$key],
                            'qty_partition'        => str_replace(',','.',str_replace('.','',$request->arr_qty_store[$key])),
                        ]);

                        $item_stock->qty -= str_replace(',', '.', str_replace('.', '', $request->arr_qty[$key]));
                        $item_stock->save();
                        $qty_out = (float) str_replace(',', '.', str_replace('.', '', $request->arr_qty[$key]));

                        $qty_in_total = ItemMove::where('item_id', $row)->where('type', 1)->sum('qty_in');
                        $qty_out_total = ItemMove::where('item_id', $row)->where('type', 2)->sum('qty_out');
                        $new_qty_final = $qty_in_total - $qty_out_total - $qty_out; // subtract new out

                        $total_in = ItemMove::where('item_id', $row)->where('type', 1)->sum('total_in');
                        $total_out = ItemMove::where('item_id', $row)->where('type', 2)->sum('total_out');
                        $new_total_final = $total_in - $total_out - $total; // subtract new out

                        $new_price_final = $new_qty_final > 0 ? $new_total_final / $new_qty_final : 0;

                        ItemMove::create([
                            'lookable_type' => $query->getTable(),
                            'lookable_id' => $query->id,
                            'item_id' => $row,
                            'qty_in' => 0,
                            'price_in' => 0,
                            'total_in' => 0,
                            'qty_out' => str_replace(',', '.', str_replace('.', '', $request->arr_qty[$key])),
                            'price_out' => $rowprice,
                            'total_out' => $total,
                            'qty_final' => $new_qty_final,
                            'price_final' => $new_price_final,
                            'total_final' => $new_total_final,
                            'date' => now(),
                            'type' => 2,
                        ]);

                        //item stock keluar

                        //itemstock masuk store

                        $itemId = $request->arr_item_stock_store[$key];
                        $qty_in = (float) str_replace(',', '.', str_replace('.', '', $request->arr_qty_store[$key]));
                        $total_store = $total;
                        $price_in = round($total/$qty_in,2);

                        // get previous totals across all types
                        $total_qty_in = ItemMove::where('item_id', $itemId)->where('type', 1)->sum('qty_in');
                        $total_qty_out = ItemMove::where('item_id', $itemId)->where('type', 2)->sum('qty_out');
                        $total_in_value = ItemMove::where('item_id', $itemId)->where('type', 1)->sum('total_in');
                        $total_out_value = ItemMove::where('item_id', $itemId)->where('type', 2)->sum('total_out');

                        // apply the new "in" movement
                        $new_qty_final_store = ($total_qty_in + $qty_in) - $total_qty_out;
                        $new_total_final_store = ($total_in_value + $total_store) - $total_out_value;
                        $new_price_final_store = $new_qty_final_store > 0 ? $new_total_final_store / $new_qty_final_store : 0;

                        ItemMove::create([
                            'lookable_type' => $query->getTable(),
                            'lookable_id' => $query->id,
                            'item_id' => $request->arr_item_stock_store[$key],
                            'qty_in' => str_replace(',', '.', str_replace('.', '', $request->arr_qty_store[$key])),
                            'price_in' => $price_in,
                            'total_in' => $total_store,
                            'qty_out' => 0,
                            'price_out' => 0,
                            'total_out' => 0,
                            'qty_final' => $new_qty_final_store,
                            'price_final' => $new_price_final_store,
                            'total_final' => $new_total_final_store,
                            'date' => now(),
                            'type' => 1,
                        ]);

                        $item_stock_store = ItemStockNew::where('item_id',$request->arr_item_stock_store[$key])->first();
                        if($item_stock_store){

                            $item_stock_store->qty += str_replace(',', '.', str_replace('.', '', $request->arr_qty_store[$key]));
                            $item_stock_store->save();
                        }else{
                            ItemStockNew::create([
                                'item_id' => $request->arr_item_stock_store[$key],
                                'qty' => str_replace(',', '.', str_replace('.', '', $request->arr_qty_store[$key])),
                                'item_stock_new_id' => $row,
                            ]);
                        }


                    }
                    CustomHelper::sendNotification('inventory_issues',$query->id,'Barang Keluar No. '.$query->code,$query->note,session('bo_id'));

                    activity()
                        ->performedOn(new ItemPartition())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit penggunaan barang.');

                    $response = [
                        'status'    => 200,
                        'message'   => 'Data successfully saved.',
                    ];
                } else {
                    $response = [
                        'status'  => 500,
                        'message' => 'Data failed to save.'
                    ];
                }

		}

		return response()->json($response);
    }

    public function rowDetail(Request $request){
        $data   = ItemPartition::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.$x.'</div><div class="col s12">
                    <table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="19">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Satuan</th>
                                <th class="center-align">Item Partisi</th>
                                <th class="center-align">Qty Konversi</th>
                                <th class="center-align">Satuan Item Konversi</th>
                                <th class="center-align">Ket</th>
                            </tr>
                        </thead><tbody>';
        $totalqty=0;
        $totalqtyreturn=0;
        foreach($data->ItemPartitionDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="">'.$row->fromStock->item->code.' - '.$row->fromStock->item->name.'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                <td class="center-align">'.$row->fromStock->item->uomUnit->code.'</td>
                <td class="">'.$row->toStock->item->code.' - '.$row->toStock->item->name.'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($row->qty_partition).'</td>
                <td class="center-align">'.$row->toStock->item->uomUnit->code.'</td>
                <td class="">'.$row->note.'</td>
            </tr>';
        }

        $string .= '</tbody></table></div>';

        return response()->json($string);
    }

    public function show(Request $request){
        $gr = ItemPartition::where('code',CustomHelper::decrypt($request->id))->first();

        $arr = [];
        foreach($gr->itemPartitionDetail as $row){
            $arr[] = [
                'id'                     => $row->id,
                'item_id'                => $row->fromStock->item_id,
                'item_stock_new_name'    => $row->fromStock->item->code.' - '.$row->fromStock->item->name,
                'unit'                   => $row->fromStock->item->uomUnit->code,
                'item_stock_new_id'      => $row->item_stock_new_id,
                'stock'                  => CustomHelper::formatConditionalQty($row->fromStock->qty),
                'qty'                    => CustomHelper::formatConditionalQty($row->qty),
                'qty_partition'          => CustomHelper::formatConditionalQty($row->qty_partition),
                'to_item_stock_item_id'  => $row->toStock->item_id,
                'to_item_stock_new_name' => $row->toStock->item->code.' - '.$row->toStock->item->name,
                'unit_partition'         => $row->toStock->item->uomUnit->code,
                'to_item_stock_new_id'   => $row->to_item_stock_new_id,
                'stock_partition'        => CustomHelper::formatConditionalQty($row->qty_store_item),
                'note'                   => $row->note,
            ];
        }

        $gr['details'] = $arr;
		return response()->json($gr);
    }

    public function voidStatus(Request $request){
        $query = ItemPartition::where('code',CustomHelper::decrypt($request->id))->first();

        if($query) {

            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                foreach($query->ItemPartitionDetail as $row){
                    $rowprice = NULL;
                    $item_stock = ItemStockNew::find(intval($row->item_stock_id));
                    $rowprice = $item_stock->priceDate($query->post_date);
                    $total = $row->total;

                    $item_stock->qty += $row->qty;
                    $item_stock->save();
                    $prev_qty_final = ItemMove::where('item_id', $row)
                                    ->where('type', 2)
                                    ->sum('qty_final');

                    $prev_total_final = ItemMove::where('item_id', $row)
                        ->where('type', 2)
                        ->sum('total_final');

                    $new_qty_final = $prev_qty_final - $row->qty;
                    $new_total_final = $prev_total_final - $total;
                    $new_price_final = $new_qty_final > 0 ? $new_total_final / $new_qty_final : 0;

                    ItemMove::create([
                        'lookable_type' => $query->getTable(),
                        'lookable_id' => $query->id,
                        'item_id' => $row->item_stock_id,
                        'qty_in' => $row->qty,
                        'price_in' => $row->price,
                        'total_in' => $total,
                        'qty_out' => 0,
                        'price_out' => 0,
                        'total_out' => 0,
                        'qty_final' => $new_qty_final,
                        'price_final' => $new_price_final,
                        'total_final' => $new_total_final,
                        'date' => now(),
                        'type' => 1,
                    ]);

                    //item stock keluar

                    //itemstock masuk store

                    ItemMove::create([
                        'lookable_type' => $query->getTable(),
                        'lookable_id' => $query->id,
                        'item_id' => $row->store_item_stock_id,
                        'qty_in' => 0,
                        'price_in' => 0,
                        'total_in' => 0,
                        'qty_out' => $row->qty,
                        'price_out' => $row->price,
                        'total_out' => $total,
                        'qty_final' => $new_qty_final,
                        'price_final' => $new_price_final,
                        'total_final' => $new_total_final,
                        'date' => now(),
                        'type' => 2,
                    ]);

                    $item_stock_store = ItemStockNew::find(intval($row->store_item_stock_id));
                    $item_stock_store->qty -= $row->qty;
                    $item_stock_store->save();
                }

                activity()
                    ->performedOn(new ItemPartition())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the inventory issue data');

                CustomHelper::sendNotification('inventory_issues',$query->id,'Barang Keluar No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);

                $response = [
                    'status'  => 200,
                    'message' => 'Data closed successfully.'
                ];
            }
        } else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }

        return response()->json($response);
    }

    public function destroy(Request $request){
        $query = ItemPartition::where('code',CustomHelper::decrypt($request->id))->first();

        $approved = false;
        $revised = false;

        if($query->approval()){
            foreach ($query->approval() as $detail){
                foreach($detail->approvalMatrix as $row){
                    if($row->approved){
                        $approved = true;
                    }

                    if($row->revised){
                        $revised = true;
                    }
                }
            }
        }

        if(!CustomHelper::checkLockAcc($query->post_date)){
            return response()->json([
                'status'  => 500,
                'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
            ]);
        }

        if($approved && !$revised){
            return response()->json([
                'status'  => 500,
                'message' => 'Dokumen telah diapprove, anda tidak bisa melakukan perubahan.'
            ]);
        }

        if(in_array($query->status,['2','3','4','5'])){
            return response()->json([
                'status'  => 500,
                'message' => 'Jurnal sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }

        if($query->delete()) {

            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);

            foreach($query->ItemPartitionDetail as $row){
                $row->itemSerial()->update([
                    'usable_type'   => NULL,
                    'usable_id'     => NULL,
                ]);
            }

            CustomHelper::removeJournal('inventory_issues',$query->id);
            CustomHelper::removeCogs('inventory_issues',$query->id);

            /* $query->ItemPartitionDetail()->delete(); */

            CustomHelper::removeApproval('inventory_issues',$query->id);

            activity()
                ->performedOn(new ItemPartition())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the good receive data');

            $response = [
                'status'  => 200,
                'message' => 'Data deleted successfully.'
            ];
        } else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }

        return response()->json($response);
    }

    public function approval(Request $request,$id){

        $gr = ItemPartition::where('code',CustomHelper::decrypt($id))->first();

        if($gr){
            $data = [
                'title'     => 'Print Goods Receive (Barang Keluar)',
                'data'      => $gr
            ];

            return view('admin.approval.good_issue', $data);
        }else{
            abort(404);
        }
    }

    public function print(Request $request){

        $validation = Validator::make($request->all(), [
            'arr_id'                => 'required',
        ], [
            'arr_id.required'       => 'Tolong pilih Item yang ingin di print terlebih dahulu.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            $var_link=[];
            $currentDateTime = Date::now();
            $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
            foreach($request->arr_id as $key =>$row){
                $pr = ItemPartition::where('code',$row)->first();

                if($pr){
                    $pdf = PrintHelper::print($pr,'inventory issue','a5','landscape','admin.print.inventory.item_partition_individual');
                    $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                    $pdf->getCanvas()->page_text(495, 340, "Jumlah Print, ". $pr->printCounter()->count(), $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                    $content = $pdf->download()->getOriginalContent();
                    $temp_pdf[]=$content;
                }

            }
            $merger = new Merger();
            foreach ($temp_pdf as $pdfContent) {
                $merger->addRaw($pdfContent);
            }


            $result = $merger->merge();


            $document_po = PrintHelper::savePrint($result);

            $response =[
                'status'=>200,
                'message'  =>$document_po
            ];
        }


		return response()->json($response);

    }

    public function printByRange(Request $request){
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
        if($request->type_date == 1){
            $validation = Validator::make($request->all(), [
                'range_start'                => 'required',
                'range_end'                  => 'required',
            ], [
                'range_start.required'       => 'Isi code awal yang ingin di pilih menjadi awal range',
                'range_end.required'         => 'Isi code terakhir yang menjadi akhir range',
            ]);
            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            }else{
                $total_pdf = intval($request->range_end)-intval($request->range_start);
                $temp_pdf=[];
                if($request->range_start>$request->range_end){
                    $kambing["kambing"][]="code awal lebih besar daripada code akhir";
                    $response = [
                        'status' => 422,
                        'error'  => $kambing
                    ];
                }
                elseif($total_pdf>31){
                    $kambing["kambing"][]="PDF lebih dari 30 buah";
                    $response = [
                        'status' => 422,
                        'error'  => $kambing
                    ];
                }else{
                    for ($nomor = intval($request->range_start); $nomor <= intval($request->range_end); $nomor++) {
                        $lastSegment = $request->lastsegment;

                        $menu = Menu::where('url', $lastSegment)->first();
                        $nomorLength = strlen($nomor);

                        // Calculate the number of zeros needed for padding
                        $paddingLength = max(0, 8 - $nomorLength);

                        // Pad $nomor with leading zeros to ensure it has at least 8 digits
                        $nomorPadded = str_repeat('0', $paddingLength) . $nomor;
                        $x =$menu->document_code.$request->year_range.$request->code_place_range.'-'.$nomorPadded;
                        $query = ItemPartition::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'inventory issue','a5','landscape','admin.print.inventory.item_partition_individual');
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 340, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;

                        }
                    }
                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }


                    $result = $merger->merge();


                    $document_po = PrintHelper::savePrint($result);

                    $response =[
                        'status'=>200,
                        'message'  =>$document_po
                    ];
                }

            }
        }elseif($request->type_date == 2){
            $validation = Validator::make($request->all(), [
                'range_comma'                => 'required',

            ], [
                'range_comma.required'       => 'Isi input untuk comma',

            ]);
            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            }else{
                $arr = explode(',', $request->range_comma);

                $merged = array_unique(array_filter($arr));

                if(count($merged)>31){
                    $kambing["kambing"][]="PDF lebih dari 30 buah";
                    $response = [
                        'status' => 422,
                        'error'  => $kambing
                    ];
                }else{
                    foreach($merged as $code){
                        $etNumbersArray = explode(',', $request->tabledata);
                        $query = ItemPartition::where('code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'inventory issue','a5','landscape','admin.print.inventory.item_partition_individual');
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 340, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;

                        }
                    }


                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }


                    $result = $merger->merge();


                    $document_po = PrintHelper::savePrint($result);

                    $response =[
                        'status'=>200,
                        'message'  =>$document_po
                    ];
                }
            }
        }
        return response()->json($response);
    }

    public function printIndividual(Request $request,$id){
        $lastSegment = request()->segment(count(request()->segments())-2);

        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();

        $pr = ItemPartition::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
        if($pr){
            $pdf = PrintHelper::print($pr,'inventory issue','a5','landscape','admin.print.inventory.item_partition_individual',$menuUser->mode);

            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));

            $content = $pdf->download()->getOriginalContent();

            $document_po = PrintHelper::savePrint($content);     $var_link=$document_po;


            return $document_po;
        }else{
            abort(404);
        }
    }

    // public function export(Request $request){
    //     $menu = Menu::where('url','good_issue')->first();
    //     $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','report')->first();
    //     $post_date = $request->start_date? $request->start_date : '';
    //     $end_date = $request->end_date ? $request->end_date : '';
    //     $mode = $request->mode ? $request->mode : '';
    //     $nominal = $menuUser->show_nominal ?? '';
	// 	return Excel::download(new ExportItemPartition($post_date,$end_date,$mode,$nominal), 'good_issue_'.uniqid().'.xlsx');
    // }

    public function exportFromTransactionPage(Request $request){
        $search = $request->search? $request->search : '';
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $status = $request->status ? $request->status : '';
		return Excel::download(new ExportItemPartitionDetail($search,$status,$end_date,$post_date), 'partisi_item'.uniqid().'.xlsx');
    }


    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData($request->type,$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function done(Request $request){
        $query_done = ItemPartition::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);

                activity()
                        ->performedOn(new ItemPartition())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the inventory issue data');

                $response = [
                    'status'  => 200,
                    'message' => 'Data updated successfully.'
                ];
            }else{
                $response = [
                    'status'  => 500,
                    'message' => 'Data tidak bisa diselesaikan karena status bukan MENUNGGU / PROSES.'
                ];
            }

            return response()->json($response);
        }
    }
}
