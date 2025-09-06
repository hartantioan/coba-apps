<?php

namespace App\Http\Controllers\Inventory;

use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Http\Controllers\Controller;
use App\Models\InventoryReturn;
use App\Models\InventoryReturnDetail;
use App\Models\ItemMove;
use App\Models\ItemStockNew;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\UsedData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class InventoryReturnController extends Controller
{
    protected $document_code;
    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));
        $menu = Menu::where('url', $lastSegment)->first();
        $document_code = $menu->document_code . date('y');

        session(['document_code' => $document_code]);
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $data = [
            'title'     => 'Pengembalian Barang',
            'content'   => 'admin.inventory.inventory_return',
            'newcode'   => $document_code,
            'menucode'  => $menu->document_code,
            'modedata'  => $menuUser->mode ? $menuUser->mode : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

   public function getCode(Request $request){
        UsedData::where('user_id', session('bo_id'))->delete();

        $document_code = session('document_code'); // Retrieve from session

        if (!$document_code) {
            return response()->json(['error' => 'Document code not found'], 400);
        }

        $code = InventoryReturn::generateCode($document_code);

        return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'code',
            'user_id',
            'note',
            'post_date',
            'grandtotal',
            'status',
            'void_id',
            'void_note',
            'void_date',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = InventoryReturn::where(function($query)use($request){

        })
        ->count();

        $query_data = InventoryReturn::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
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

                if($request->status){
                    $query->whereIn('status', $request->status);
                }

                if($request->codes){
                    $arrCode = explode(',',$request->codes);
                    $query->whereIn('code',$arrCode);
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = InventoryReturn::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
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

                if($request->status){
                    $query->whereIn('status', $request->status);
                }

                if($request->codes){
                    $arrCode = explode(',',$request->codes);
                    $query->whereIn('code',$arrCode);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {

                $btn_cancel = $val->status == '2' ? '<button type="button" class="btn-floating mb-1  btn-small btn-flat waves-effect waves-light purple darken-2 white-text" data-popup="tooltip" title="Cancel" onclick="cancelStatus(`' . CustomHelper::encrypt($val->code) . '`)" ><i class="material-icons dp48">cancel</i></button>' : '';
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">info_outline</i></button>',
                    $val->code,
                    $val->user->name ?? '',
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->note,
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat purple accent-2 white-text btn-small" data-popup="tooltip" title="Selesai" onclick="done(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">gavel</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        '.$btn_cancel.'
                        <!-- <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">delete</i></button> -->
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
            'arr_item'                  => 'required|array',
            'arr_qty'                   => 'required|array',
		], [
            'code.required' 	                => 'Kode tidak boleh kosong.',
			'post_date.required' 				=> 'Tanggal posting tidak boleh kosong.',
            'arr_item.required'                 => 'Item tidak boleh kosong',
            'arr_item.array'                    => 'Item harus dalam bentuk array',
            'arr_qty.required'                  => 'Qty item tidak boleh kosong',
            'arr_qty.array'                     => 'Qty item harus dalam bentuk array'
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            $grandtotalall = 0;
            foreach($request->arr_item as $key => $row){
                if(str_replace(',','.',str_replace('.','',$request->arr_qty[$key])) == 0 ||str_replace(',','.',str_replace('.','',$request->arr_qty[$key])) < 1){
                    $kambing["kambing"][]="Qty KURANG DARI 1";
                    $response = [
                        'status' => 422,
                        'error'  => $kambing
                    ];
                    return response()->json($response);
                }

                $itemStock = ItemStockNew::where('item_id', $row)->first();
                $price = $itemStock->priceDate($request->post_date);
                $grandtotal = 0;
                $total = str_replace(',','.',str_replace('.','',$request->arr_qty[$key])) * $price;


                $grandtotal = $total;

                $arrDetail[] = [
                    'total'         => $total,
                    'grandtotal'    => $grandtotal,
                ];

                $grandtotalall += $grandtotal;

            }
			if($request->temp){
                    $query = InventoryReturn::where('code',CustomHelper::decrypt($request->temp))->first();

                    if(in_array($query->status,['1','6'])){
                        if($request->has('file')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('file')->store('public/inventory_returns');
                        } else {
                            $document = $query->document;
                        }
                        $query->user_id = session('bo_id');
                        $query->post_date = $request->post_date;
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->grandtotal = $grandtotalall;
                        $query->status = '3';

                        $query->save();

                        foreach($query->InventoryReturnDetail as $row){

                            $items = ItemStockNew::where('item_id',$row->item_id)->first();
                            if ($items) {

                                $items->qty += str_replace(',', '.', str_replace('.', '', $row->qty));

                                $items->save();
                            }
                            $row->delete();
                        }

                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status GRPO sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
			}else{
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=InventoryReturn::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);

                    $query = InventoryReturn::create([
                        'code'         => $newCode,
                        'user_id'      => session('bo_id'),
                        'post_date'    => $request->post_date,
                        'document'     => $request->file('document') ? $request->file('document')->store('public/inventory_returns') : NULL,
                        'note'         => $request->note,
                        'status'       => '1',
                        'grandtotal'   => $grandtotalall,
                    ]);

			}

			if($query) {
                    foreach($request->arr_item as $key => $row){
                        $itemStock = ItemStockNew::where('item_id', $row)->first();
                        $price = $itemStock->priceDate($request->post_date);
                        $total = str_replace(',','.',str_replace('.','',$request->arr_qty[$key])) * $price;
                        $grd = InventoryReturnDetail::create([
                            'inventory_return_id' => $query->id,
                            'item_id'             => $row,
                            'qty'                 => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            'price'               => $price,
                            'total'               => $total,
                            'grandtotal'          => $arrDetail[$key]['grandtotal'],
                            'note'                => $request->arr_note[$key],
                        ]);


                        $itemId = $itemStock->item_id;

                        // Get and format the outgoing quantity
                        $qtyOut = (float) str_replace(',', '.', str_replace('.', '', $request->arr_qty[$key]));

                        // Get the latest price_final (from any previous movement)
                        $lastMove = ItemMove::where('item_id', $itemId)
                            ->latest('id') // or latest('date') if you prefer
                            ->first();

                        $priceFinal = $lastMove?->price_final ?? 0;

                        // Calculate out value
                        $totalOut = $qtyOut * $priceFinal;

                        // Get all previous in/out sums
                        $totalQtyIn = ItemMove::where('item_id', $itemId)->where('type', 1)->sum('qty_in');
                        $totalQtyOut = ItemMove::where('item_id', $itemId)->where('type', 2)->sum('qty_out');

                        $totalInValue = ItemMove::where('item_id', $itemId)->where('type', 1)->sum('total_in');
                        $totalOutValue = ItemMove::where('item_id', $itemId)->where('type', 2)->sum('total_out');

                        // Calculate new stock values
                        $newQtyFinal = ($totalQtyIn - $totalQtyOut) - $qtyOut;
                        $newTotalFinal = ($totalInValue - $totalOutValue) - $totalOut;
                        $newPriceFinal = $newQtyFinal > 0 ? $newTotalFinal / $newQtyFinal : 0;

                        ItemMove::create([
                            'lookable_type' => $query->getTable(),
                            'lookable_id' => $query->id,
                            'lookable_detail_type' => $grd->getTable(),
                            'lookable_detail_id' => $grd->id,
                            'item_id' => $itemId,
                            'qty_in' => 0,
                            'price_in' => 0,
                            'total_in' => 0,
                            'qty_out' => $qtyOut,
                            'price_out' => $priceFinal,
                            'total_out' => $totalOut,
                            'qty_final' => $newQtyFinal,
                            'price_final' => $newPriceFinal,
                            'total_final' => $newTotalFinal,
                            'date' => now(),
                            'type' => 2,
                        ]);

                        $itemStock->qty -= $qtyOut;
                        $itemStock->save();


                    }

                    CustomHelper::sendNotification('inventory_returns',$query->id,' Pengembalian Barang kode. '.$query->code,$query->note,session('bo_id'));


                activity()
                    ->performedOn(new InventoryReturn())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit pengembalian barang.');

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

    public function rowDetail(Request $request)
    {
        $data   = InventoryReturn::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
           $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }
        $string = '<div class="row pt-1 pb-1 lighten-4">
                        <div class="col s12">'.$data->code.$x.'</div>
                        <div class="col s12">
                            <table class="bordered" style="min-width:100%;max-width:100%;">
                                <thead>
                                    <tr>
                                        <th class="center-align" colspan="13">Daftar Item</th>
                                    </tr>
                                    <tr>
                                        <th class="center-align">No.</th>
                                        <th class="center-align">Item</th>
                                        <th class="center-align">Qty</th>
                                        <th class="center-align">Satuan</th>
                                        <th class="center-align">Keterangan </th>
                                    </tr>
                                </thead>
                                <tbody>';
        $totalqty=0;
        foreach($data->InventoryReturnDetail as $key => $rowdetail){
            $totalqty+=$rowdetail->qty;
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$rowdetail->item->code.' - '.$rowdetail->item->name.'</td>
                <td class="center-align">'.CustomHelper::formatConditionalQty($rowdetail->qty).'</td>
                <td class="center-align">'.$rowdetail->item->uomUnit->code.'</td>
                <td class="center-align">'.$rowdetail->note.'</td>
            </tr>';
        }
        $string .= '<tr>
                        <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="2"> Total </td>
                        <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalqty, 3, ',', '.') . '</td>
                        <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="2">  </td>
                    </tr>';

        $string .= '</tbody></table>';

        $string .= '</td></tr>';
        return response()->json($string);
    }


    public function show(Request $request){
        $grm = InventoryReturn::where('code',CustomHelper::decrypt($request->id))->first();
        $grm['customer_name'] = $grm->customer?->name ?? '';
        $arr = [];

        foreach($grm->InventoryReturnDetail()->orderBy('id')->get() as $key => $row){
            $stock = ItemStockNew::where('item_id',$row->item_id)->first();
            $arr[] = [
                'id'                        => $row->id,
                'item_id'                   => $row->item_id,
                'item_name'                 => $row->item->name,
                'qty_stock'                 => CustomHelper::formatConditionalQty($stock->qty),
                'qty'                       => CustomHelper::formatConditionalQty($row->qty),
                'price'                     => CustomHelper::formatConditionalQty($row->price),
                'total'                     => CustomHelper::formatConditionalQty($row->total),
                'note'                      => $row->note ? $row->note : '',
                'discount3'                 => CustomHelper::formatConditionalQty($row->discount_3 ? $row->discount_3 : 0),
            ];
        }

        $grm['details'] = $arr;
		return response()->json($grm);
    }

    public function voidStatus(Request $request){
        $query = InventoryReturn::where('code',CustomHelper::decrypt($request->id))->first();

        if($query) {

            $array_minus_stock=[];

            foreach($query->InventoryReturnDetail as $row_good_receipt_detail){
                $item_real_stock = $row_good_receipt_detail->item->getStockPlaceWarehouse($row_good_receipt_detail->place_id,$row_good_receipt_detail->warehouse_id);
                $item_stock_detail = $row_good_receipt_detail->qtyConvert();
                if($item_real_stock-$item_stock_detail < -1){
                    $array_minus_stock[]=$row_good_receipt_detail->item->name;
                }
            }
            if(count($array_minus_stock) > 0){
                $arrError = [];
                foreach($array_minus_stock as $row){
                    $arrError[] = $row;
                }
                return response()->json([
                    'status'  => 500,
                    'message' => 'Mohon maaf GRPO tidak dapat di void karena item stock saat ini kurang dari 0. Daftar Item : '.implode(', ',$arrError),
                ]);
            }

            if(in_array($query->status,['4','5','8'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }elseif($query->hasChildDocument()){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah digunakan pada Landed Cost / A/P Invoice.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                $query->updateRootDocumentStatusProcess();

                foreach($query->InventoryReturnDetail as $row){
                    $row->itemSerial()->delete();
                }

                if($query->cancelDocument()->exists()){
                    $query->cancelDocument->journal->journalDetail()->delete();
                    $query->cancelDocument->journal->delete();
                    $query->cancelDocument->delete();
                }

                CustomHelper::removeJournal('inventory_returns',$query->id);
                CustomHelper::removeCogs('inventory_returns',$query->id);
                CustomHelper::sendNotification('inventory_returns',$query->id,'Delivery Receive No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);


                activity()
                    ->performedOn(new InventoryReturn())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the delivery receive data');

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
        $query = InventoryReturn::where('code',CustomHelper::decrypt($request->id))->first();

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

            foreach($query->InventoryReturnDetail as $row){
                $row->itemSerial()->delete();
            }

            CustomHelper::removeJournal('inventory_returns',$query->id);
            CustomHelper::removeCogs('inventory_returns',$query->id);
            CustomHelper::removeApproval('inventory_returns',$query->id);

            $query->InventoryReturnDetail()->delete();

            activity()
                ->performedOn(new InventoryReturn())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the delivery receive data');

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



    public function printIndividual(Request $request,$id){
        $lastSegment = request()->segment(count(request()->segments())-2);

        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();

        $pr = InventoryReturn::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
        if($pr){
            $pdf = PrintHelper::print($pr,'delivery receive','a5','landscape','admin.print.sales.order_individual',$menuUser->mode);
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));

            $content = $pdf->download()->getOriginalContent();

            $document_po = PrintHelper::savePrint($content);


            return $document_po;
        }else{
            abort(404);
        }
    }

    public function exportFromTransactionPage(Request $request){
        $search = $request->search? $request->search : '';
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $status = $request->status ? $request->status : '';
		return Excel::download(new ExportInventoryReturnDetail($search,$status,$end_date,$post_date), 'penjualan_'.uniqid().'.xlsx');
    }



    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData('purchase_orders',$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }


    public function done(Request $request){
        $query_done = InventoryReturn::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);

                activity()
                        ->performedOn(new InventoryReturn())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the delivery receive data');

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
