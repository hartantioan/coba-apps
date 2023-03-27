<?php

namespace App\Http\Controllers\Inventory;
use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\ApprovalMatrix;
use App\Models\ApprovalSource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\GoodReceipt;
use App\Models\User;
use App\Models\GoodReceiptDetail;
use App\Helpers\CustomHelper;
use App\Exports\ExportGoodReceipt;

class GoodReceiptPOController extends Controller
{
    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user->userPlaceArray();
    }

    public function index()
    {
        $data = [
            'title'     => 'Penerimaan Barang PO',
            'content'   => 'admin.inventory.good_receipt',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'user_id',
            'code',
            'account_id',
            'receiver_name',
            'post_date',
            'due_date',
            'document_date',
            'place_id',
            'warehouse_id',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = GoodReceipt::whereIn('place_id',$this->dataplaces)->count();
        
        $query_data = GoodReceipt::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('due_date', 'like', "%$search%")
                            ->orWhere('document_date', 'like', "%$search%")
                            ->orWhere('receiver_name', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('goodReceiptDetail',function($query) use($search, $request){
                                $query->whereHas('item',function($query) use($search, $request){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->warehouse){
                    $query->whereIn('warehouse_id', $request->warehouse);
                }
            })
            ->whereIn('place_id',$this->dataplaces)
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = GoodReceipt::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('due_date', 'like', "%$search%")
                            ->orWhere('document_date', 'like', "%$search%")
                            ->orWhere('receiver_name', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('goodReceiptDetail',function($query) use($search, $request){
                                $query->whereHas('item',function($query) use($search, $request){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->warehouse){
                    $query->whereIn('warehouse_id', $request->warehouse);
                }
            })
            ->whereIn('place_id',$this->dataplaces)
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-id="' . $val->id . '"><i class="material-icons">add</i></button>',
                    $val->user->name,
                    $val->code,
                    $val->supplier->name,
                    $val->receiver_name,
                    date('d M Y',strtotime($val->post_date)),
                    date('d M Y',strtotime($val->due_date)),
                    date('d M Y',strtotime($val->document_date)),
                    $val->place->name,
                    $val->warehouse->name,
                    $val->note,
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
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

    public function getPurchaseOrder(Request $request){
        $data = PurchaseOrder::where('id',$request->id)->where('status','2')->first();
        $data['ecode'] = CustomHelper::encrypt($data->code);

        if($data->used()->exists()){
            $data['status'] = '500';
            $data['message'] = 'Purchase Order '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.';
        }else{
            CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Good Receipt');
            $details = [];
            foreach($data->purchaseOrderDetail as $row){
                $details[] = [
                    'item_id'   => $row->item_id,
                    'item_name' => $row->item->code.' - '.$row->item->name,
                    'qty'       => $row->getBalanceReceipt(),
                    'unit'      => $row->item->buyUnit->code
                ];
            }

            $data['details'] = $details;
        }

        return response()->json($data);
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
			'receiver_name'			    => 'required',
			'post_date'		            => 'required',
			'due_date'		            => 'required',
            'document_date'		        => 'required',
            'warehouse_id'              => 'required',
            'arr_item'                  => 'required|array',
            'arr_qty'                   => 'required|array',
		], [
            'receiver_name.required'            => 'Nama penerima tidak boleh kosong.',
			'post_date.required' 				=> 'Tanggal posting tidak boleh kosong.',
			'due_date.required' 				=> 'Tanggal kadaluwarsa tidak boleh kosong.',
            'document_date.required' 			=> 'Tanggal dokumen tidak boleh kosong.',
			'warehouse_id.required'				=> 'Gudang tujuan tidak boleh kosong',
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

            $purchase_order = PurchaseOrder::find($request->purchase_order_id);

            $total = 0;
            $discount = $purchase_order->discount;
            $subtotal = $purchase_order->subtotal;
            $bobot = 0;
            $tax = 0;
            $grandtotal = 0;

            if($purchase_order){
                foreach($request->arr_item as $key => $row){
                    $rowprice = 0;
                    $datarow = $purchase_order->purchaseOrderDetail()->where('item_id',$row)->first();
                    if($datarow){
                        $bobot = $datarow->subtotal / $subtotal;
                        $rowprice = round($datarow->subtotal / $datarow->qty,3);
                    }

                    $total += ($rowprice * floatval(str_replace(',','.',str_replace('.','',$request->arr_qty[$key])))) - ($bobot * $discount);
                }

                if($purchase_order->is_tax == '1' && $purchase_order->is_include_tax == '1'){
                    $total = $total / (1 + ($purchase_order->percent_tax / 100));
                }

                if($purchase_order->is_tax == '1'){
                    $tax = round($total * ($purchase_order->percent_tax / 100),3);
                }

                $grandtotal = $total + $tax;
            }

			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = GoodReceipt::where('code',CustomHelper::decrypt($request->temp))->first();

                    if($query->approval()){
                        foreach($query->approval()->approvalMatrix as $row){
                            if($row->status == '2'){
                                return response()->json([
                                    'status'  => 500,
                                    'message' => 'Purchase Request telah diapprove, anda tidak bisa melakukan perubahan.'
                                ]);
                            }
                        }
                    }

                    if($query->status == '1'){
                        if($request->has('file')) {
                            if(Storage::exists($query->document)){
                                Storage::delete($query->document);
                            }
                            $document = $request->file('file')->store('public/good_receipts');
                        } else {
                            $document = $query->document;
                        }
                        
                        $query->user_id = session('bo_id');
                        $query->account_id = $purchase_order->account_id;
                        $query->receiver_name = $request->receiver_name;
                        $query->post_date = $request->post_date;
                        $query->due_date = $request->due_date;
                        $query->document_date = $request->document_date;
                        $query->company_id = $purchase_order->place->company_id;
                        $query->place_id = $purchase_order->place_id;
                        $query->department_id = $purchase_order->department_id;
                        $query->warehouse_id = $request->warehouse_id;
                        $query->currency_id = $purchase_order->currency_id;
                        $query->currency_rate = $purchase_order->currency_rate;
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->total = round($total,3);
                        $query->tax = $tax;
                        $query->grandtotal = $grandtotal;

                        $query->save();

                        foreach($query->goodReceiptDetail as $row){
                            $row->delete();
                        }

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status purchase request sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = GoodReceipt::create([
                        'code'			        => GoodReceipt::generateCode(),
                        'user_id'		        => session('bo_id'),
                        'account_id'            => $purchase_order->account_id,
                        'receiver_name'         => $request->receiver_name,
                        'post_date'             => $request->post_date,
                        'due_date'              => $request->due_date,
                        'document_date'         => $request->document_date,
                        'company_id'		    => $purchase_order->place->company_id,
                        'place_id'		        => $purchase_order->place_id,
                        'department_id'		    => $purchase_order->department_id,
                        'warehouse_id'          => $request->warehouse_id,
                        'currency_id'           => $purchase_order->currency_id,
                        'currency_rate'         => $purchase_order->currency_rate,
                        'document'              => $request->file('document') ? $request->file('document')->store('public/good_receipts') : NULL,
                        'note'                  => $request->note,
                        'total'                 => round($total,3),
                        'tax'                   => $tax,
                        'grandtotal'            => $grandtotal,
                        'status'                => '1',
                    ]);

                    CustomHelper::sendApproval('good_receipts',$query->id,$query->note);
                    CustomHelper::sendNotification('good_receipts',$query->id,'Pengajuan Penerimaan Barang No. '.$query->code,$query->note,session('bo_id'));

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                
                foreach($request->arr_item as $key => $row){
                    DB::beginTransaction();
                    try {
                        GoodReceiptDetail::create([
                            'good_receipt_id'       => $query->id,
                            'item_id'               => $row,
                            'qty'                   => $request->arr_qty[$key],
                            'note'                  => $request->arr_note[$key]
                        ]);
                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollback();
                    }
                }

                activity()
                    ->performedOn(new GoodReceipt())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit penerimaan barang.');

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
        $data   = GoodReceipt::find($request->id);
        
        $string = '<div class="row pt-1 pb-1 lime lighten-4"><div class="col s12"><table style="max-width:500px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="10">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Satuan</th>
                                <th class="center-align">Keterangan</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->goodReceiptDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->item->name.'</td>
                <td class="center-align">'.$row->qty.'</td>
                <td class="center-align">'.$row->item->buyUnit->code.'</td>
                <td class="center-align">'.$row->note.'</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="max-width:500px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="4">Approval</th>
                            </tr>
                            <tr>
                                <th class="center-align">Level</th>
                                <th class="center-align">Kepada</th>
                                <th class="center-align">Status</th>
                                <th class="center-align">Catatan</th>
                            </tr>
                        </thead><tbody>';
        
        if($data->approval() && $data->approval()->approvalMatrix()->exists()){                
            foreach($data->approval()->approvalMatrix as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.$row->approvalTable->level.'</td>
                    <td class="center-align">'.$row->user->profilePicture().'<br>'.$row->user->name.'</td>
                    <td class="center-align">'.($row->status == '1' ? '<i class="material-icons">hourglass_empty</i>' : ($row->approved ? '<i class="material-icons">thumb_up</i>' : ($row->rejected ? '<i class="material-icons">thumb_down</i>' : '<i class="material-icons">hourglass_empty</i>'))).'<br></td>
                    <td class="center-align">'.$row->note.'</td>
                </tr>';
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="4">Approval tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function approval(Request $request,$id){
        
        $pr = GoodReceipt::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Good Receipt (Penerimaan Barang)',
                'data'      => $pr
            ];

            return view('admin.approval.good_receipt', $data);
        }else{
            abort(404);
        }
    }

    public function show(Request $request){
        $gr = GoodReceipt::where('code',CustomHelper::decrypt($request->id))->first();
        $gr['warehouse_name'] = $gr->warehouse->branch->name.' - '.$gr->warehouse->name;
        $arr = [];

        foreach($gr->goodReceiptDetail as $row){
            $arr[] = [
                'item_id'   => $row->item_id,
                'item_name' => $row->item->name,
                'qty'       => $row->qty,
                'unit'      => $row->item->buyUnit->code,
                'note'      => $row->note,
            ];
        }

        $gr['details'] = $arr;
        				
		return response()->json($gr);
    }

    public function voidStatus(Request $request){
        $query = GoodReceipt::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {
            if($query->status == '5'){
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
    
                activity()
                    ->performedOn(new GoodReceipt())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the good receipt data');
    
                CustomHelper::sendNotification('good_receipts',$query->id,'Good Receipt No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeJournal('good_receipts',$query->id);
                CustomHelper::removeCogs('good_receipts',$query->id);
                CustomHelper::removeApproval('good_receipts',$query->id);
                
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
        $query = GoodReceipt::where('code',CustomHelper::decrypt($request->id))->first();

        if($query->approval() || in_array($query->status,['2','3'])){
            foreach($query->approval()->approvalMatrix as $row){
                if($row->status == '2'){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Purchase Order telah diapprove / sudah dalam progres, anda tidak bisa melakukan perubahan.'
                    ]);
                }
            }
        }
        
        if($query->delete()) {

            $query->goodReceiptDetail()->delete();

            CustomHelper::removeJournal('good_receipts',$query->id);
            CustomHelper::removeCogs('good_receipts',$query->id);
            CustomHelper::removeApproval('good_receipts',$query->id);

            activity()
                ->performedOn(new GoodReceipt())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the good receipt data');

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

    public function print(Request $request){

        $data = [
            'title' => 'GOOD RECEIPT PO REPORT',
            'data' => GoodReceipt::where(function ($query) use ($request) {
                if($request->search) {
                    $query->where(function($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('post_date', 'like', "%$request->search%")
                            ->orWhere('due_date', 'like', "%$request->search%")
                            ->orWhere('document_date', 'like', "%$request->search%")
                            ->orWhere('receiver_name', 'like', "%$request->search%")
                            ->orWhere('note', 'like', "%$request->search%")
                            ->orWhereHas('goodReceiptDetail',function($query) use($request){
                                $query->whereHas('item',function($query) use($request){
                                    $query->where('code', 'like', "%$request->search%")
                                        ->orWhere('name','like',"%$request->search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($request){
                                $query->where('name','like',"%$request->search%")
                                    ->orWhere('employee_no','like',"%$request->search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->warehouse){
                    $query->whereIn('warehouse_id', $request->warehouse);
                }
            })
            ->whereIn('place_id',$this->dataplaces)
            ->get()
		];
		
		return view('admin.print.inventory.good_receipt', $data);
    }

    public function export(Request $request){
		return Excel::download(new ExportGoodReceipt($request->search,$request->status,$request->warehouse,$this->dataplaces), 'good_receipt_'.uniqid().'.xlsx');
    }

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData('purchase_orders',$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }
}