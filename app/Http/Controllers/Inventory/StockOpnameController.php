<?php

namespace App\Http\Controllers\Inventory;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\ItemStockNew;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\UsedData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockOpnameController extends Controller
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
            'title'     => 'Stok Opname',
            'content'   => 'admin.inventory.stock_opname',
            'minDate'   => $request->get('minDate'),
            'maxDate'   => $request->get('maxDate'),
            'newcode'   => $menu->document_code.date('y'),
            'menucode'  => $menu->document_code,
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $menu = Menu::where('url', 'stock_opname')->first();
        UsedData::where('user_id', session('bo_id'))->delete();
        $code = StockOpname::generateCode($menu->document_code);

		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'user_id',
            'code',
            'post_date',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = StockOpname::count();

        $query_data = StockOpname::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
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
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = StockOpname::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
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
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {

                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">info_outline</i></button>',
                    $val->code,
                    $val->user->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->note,
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light indigo darken-4 white-text btn-small" data-popup="tooltip" title="Edit Catatan" onclick="showNote(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">mode_edit</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat cyan darken-4 white-text btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
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

                }else{
                    if($passedQtyMinus == false){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Maaf, pada tanggal setelah tanggal posting terdapat qty minus pada stok. Barang '.implode(", ",$arrItemNotPassed),
                        ]);
                    }
                }
                $lastSegment = $request->lastsegment;
                $menu = Menu::where('url', $lastSegment)->first();
                $newCode=StockOpname::generateCode($menu->document_code.date('y',strtotime($request->post_date)));

                $query = StockOpname::create([
                    'code'			        => $newCode,
                    'user_id'		        => session('bo_id'),
                    'post_date'             => $request->post_date,
                    'document'              => $request->file('file') ? $request->file('file')->store('public/stock_opname') : NULL,
                    'note'                  => $request->note,
                    'status'                => '3',
                    'grandtotal'            => round($grandtotal,2)
                ]);

                if($query) {

                    if(!$request->temp){
                        foreach($request->arr_item_stock as $key => $row){
                            $rowprice = NULL;
                            $item_stock = ItemStockNew::where('item_id',$row)->first();
                            $rowprice = $item_stock->priceDate($query->post_date);
                            $gid = StockOpnameDetail::create([
                                'stock_opname_id' => $query->id,
                                'item_id'         => $row,
                                'type'            => $request->arr_type[$key],
                                'adjustment_qty'  => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                                'actual_qty'      => str_replace(',','.',str_replace('.','',$request->arr_actual_qty[$key])),
                                'note'            => $request->arr_note[$key],
                            ]);
                        }

                        CustomHelper::sendNotification('stock_opname',$query->id,'Stock Opname No. '.$query->code,$query->note,session('bo_id'));

                        activity()
                            ->performedOn(new StockOpname())
                            ->causedBy(session('bo_id'))
                            ->withProperties($query)
                            ->log('Add stock opname.');

                    }else{

                    }

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

    public function postMovement(Request $request){
        $data   = StockOpname::where('code',CustomHelper::decrypt($request->id))->first();

    }
}
