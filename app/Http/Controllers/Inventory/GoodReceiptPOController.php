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
use App\Models\GoodReceiptMain;
use App\Models\User;
use App\Models\Place;
use App\Models\Department;
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
            'place'     => Place::where('status','1')->get(),
            'department'=> Department::where('status','1')->get(),
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

        $total_data = GoodReceiptMain::whereIn('place_id',$this->dataplaces)->count();
        
        $query_data = GoodReceiptMain::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('due_date', 'like', "%$search%")
                            ->orWhere('document_date', 'like', "%$search%")
                            ->orWhere('receiver_name', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('goodReceipt', function($query) use($search, $request){
                                $query->whereHas('goodReceiptDetail',function($query) use($search, $request){
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

        $total_filtered = GoodReceiptMain::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('due_date', 'like', "%$search%")
                            ->orWhere('document_date', 'like', "%$search%")
                            ->orWhere('receiver_name', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('goodReceipt', function($query) use($search, $request){
                                $query->whereHas('goodReceiptDetail',function($query) use($search, $request){
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
            if($data->hasBalance()){
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
            }else{
                $data['status'] = '500';
                $data['message'] = 'Seluruh item pada purchase order '.$data->code.' telah diterima di gudang.';
            }
        }

        return response()->json($data);
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'place_id'                  => 'required',
			'receiver_name'			    => 'required',
			'post_date'		            => 'required',
			'due_date'		            => 'required',
            'document_date'		        => 'required',
            'warehouse_id'              => 'required',
            'arr_item'                  => 'required|array',
            'arr_qty'                   => 'required|array',
		], [
            'place_id.required'                 => 'Penempatan tidak boleh kosong.',
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
            
            $total = 0;
            $bobot = 0;
            $tax = 0;
            $grandtotal = 0;
            $discount = 0;
            $subtotal = 0;
            $totalall = 0;
            $taxall = 0;
            $grandtotalall = 0;

            $arrDetail = [];

            foreach($request->arr_purchase as $key => $row){
                $index = -1;
                $detail_po = [];

                foreach($arrDetail as $keycek => $rowcek){
                    if(CustomHelper::decrypt($row) == $rowcek['po']->code){
                        $index = $keycek;
                    }
                }

                $purchase_order = PurchaseOrder::where('code',CustomHelper::decrypt($request->arr_purchase[$key]))->first();

                if($purchase_order){

                    $discount = $purchase_order->discount;
                    $subtotal = $purchase_order->subtotal;

                    $rowprice = 0;

                    $datarow = $purchase_order->purchaseOrderDetail()->where('item_id',$request->arr_item[$key])->first();

                    if($datarow){
                        $bobot = $datarow->subtotal / $subtotal;
                        $rowprice = round($datarow->subtotal / $datarow->qty,3);
                    }

                    $total = ($rowprice * floatval(str_replace(',','.',str_replace('.','',$request->arr_qty[$key])))) - ($bobot * $discount);

                    if($purchase_order->is_tax == '1' && $purchase_order->is_include_tax == '1'){
                        $total = $total / (1 + ($purchase_order->percent_tax / 100));
                    }

                    if($purchase_order->is_tax == '1'){
                        $tax = round($total * ($purchase_order->percent_tax / 100),3);
                    }

                    $grandtotal = $total + $tax;

                    $totalall += $total;
                    $taxall += $tax;
                    $grandtotalall += $grandtotal;

                    if($index >= 0){
                        $arrDetail[$index]['detail'][] = [
                            'item_id'           => $request->arr_item[$key],
                            'qty'               => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            'note'              => $request->arr_note[$key],
                        ];
                        
                        $arrDetail[$index] = [
                            'po'                => $purchase_order,
                            'detail'            => $arrDetail[$index]['detail'],
                            'total'             => $arrDetail[$index]['total'] + round($total,3),
                            'tax'               => $arrDetail[$index]['tax'] + $tax,
                            'grandtotal'        => $arrDetail[$index]['grandtotal'] + $grandtotal,
                        ];
                    }else{
                        $detail_po[] = [
                            'item_id'           => $request->arr_item[$key],
                            'qty'               => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            'note'              => $request->arr_note[$key],
                        ];
                        $arrDetail[] = [
                            'po'                => $purchase_order,
                            'detail'            => $detail_po,
                            'total'             => round($total,3),
                            'tax'               => $tax,
                            'grandtotal'        => $grandtotal
                        ];
                    }
                }
            }

			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = GoodReceiptMain::where('code',CustomHelper::decrypt($request->temp))->first();

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
                        $query->receiver_name = $request->receiver_name;
                        $query->post_date = $request->post_date;
                        $query->due_date = $request->due_date;
                        $query->document_date = $request->document_date;
                        $query->place_id = $purchase_order->place_id;
                        $query->warehouse_id = $request->warehouse_id;
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->total = $totalall;
                        $query->tax = $taxall;
                        $query->grandtotal = $grandtotalall;
                        $query->save();

                        foreach($query->goodReceipt as $row){
                            foreach($row->goodReceiptDetail as $rowdetail){
                                $rowdetail->delete();
                            }
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
                    $query = GoodReceiptMain::create([
                        'code'			        => GoodReceiptMain::generateCode(),
                        'user_id'		        => session('bo_id'),
                        'receiver_name'         => $request->receiver_name,
                        'post_date'             => $request->post_date,
                        'due_date'              => $request->due_date,
                        'document_date'         => $request->document_date,
                        'place_id'		        => $purchase_order->place_id,
                        'warehouse_id'          => $request->warehouse_id,
                        'document'              => $request->file('document') ? $request->file('document')->store('public/good_receipts') : NULL,
                        'note'                  => $request->note,
                        'status'                => '1',
                        'total'                 => $totalall,
                        'tax'                   => $taxall,
                        'grandtotal'            => $grandtotalall
                    ]);

                    CustomHelper::sendApproval('good_receipt_mains',$query->id,$query->note);
                    CustomHelper::sendNotification('good_receipt_mains',$query->id,'Pengajuan Penerimaan Barang No. '.$query->code,$query->note,session('bo_id'));

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                DB::beginTransaction();
                try {
                    foreach($arrDetail as $key => $row){
                        
                        $querydetail = GoodReceipt::create([
                            'good_receipt_main_id'      => $query->id,
                            'purchase_order_id'         => $row['po']->id,
                            'account_id'                => $row['po']->account_id,
                            'company_id'                => $row['po']->place->company_id,
                            'place_id'                  => $row['po']->place_id,
                            'department_id'             => $row['po']->department_id,
                            'currency_id'               => $row['po']->currency_id,
                            'currency_rate'             => $row['po']->currency_rate,
                            'total'                     => $row['total'],
                            'tax'                       => $row['tax'],
                            'grandtotal'                => $row['grandtotal']
                        ]);
                        
                        foreach($row['detail'] as $rowdetail){
                            GoodReceiptDetail::create([
                                'good_receipt_id'       => $querydetail->id,
                                'item_id'               => $rowdetail['item_id'],
                                'qty'                   => $rowdetail['qty'],
                                'note'                  => $rowdetail['note']
                            ]);
                        }

                        CustomHelper::removeUsedData('purchase_orders',$row['po']->id);
                    }
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                activity()
                    ->performedOn(new GoodReceiptMain())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit penerimaan barang.');

				$response = [
					'status'    => 200,
					'message'   => 'Data successfully saved.',
                    'test'      => json_encode($arrDetail)
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
        $data   = GoodReceiptMain::find($request->id);
        
        $string = '<div class="row pt-1 pb-1 lime lighten-4">
                        <div class="col s12">
                            <table class="bordered">
                                <thead>
                                    <tr>
                                        <th class="center">PO No.</th>
                                        <th class="center">Supplier</th>
                                        <th class="center">Perusahaan</th>
                                        <th class="center">Pabrik/Kantor</th>
                                        <th class="center">Departemen</th>
                                    </tr>
                                </thead>
                                <tbody>';

        foreach($data->goodReceipt as $row){
            $string .= '
            <tr align="center" style="background-color:#eee;">
                <td class="center">'.$row->purchaseOrder->code.'</td>
                <td class="center">'.$row->supplier->name.'</td>
                <td class="center">'.$row->company->name.'</td>
                <td class="center">'.$row->place->name.'</td>
                <td class="center">'.$row->department->name.'</td>
            </tr>
            <tr>
                <td colspan="5" style="border-right-style: none !important;">
            ';

            $string .='<table style="max-width:500px;">
                            <thead>
                                <tr>
                                    <th class="center-align" colspan="5">Daftar Item</th>
                                </tr>
                                <tr>
                                    <th class="center-align">No.</th>
                                    <th class="center-align">Item</th>
                                    <th class="center-align">Qty</th>
                                    <th class="center-align">Satuan</th>
                                    <th class="center-align">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>';
        
            foreach($row->goodReceiptDetail as $key => $rowdetail){
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td class="center-align">'.$rowdetail->item->name.'</td>
                    <td class="center-align">'.$rowdetail->qty.'</td>
                    <td class="center-align">'.$rowdetail->item->buyUnit->code.'</td>
                    <td class="center-align">'.$rowdetail->note.'</td>
                </tr>';
            }
            
            $string .= '</tbody></table>';

            $string .= '</td></tr>';
        }
        

        $string .= '</tbody></table></div><div class="col s12 mt-1"><table style="max-width:500px;">
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

        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="max-width:500px;">
                <thead>
                    <tr>
                        <th class="center-align" colspan="6">Landed Cost</th>
                    </tr>
                    <tr>
                        <th class="center-align">Nomor/Kode</th>
                        <th class="center-align">Vendor</th>
                        <th class="center-align">Keterangan</th>
                        <th class="center-align">Total</th>
                        <th class="center-align">Pajak</th>
                        <th class="center-align">Grandtotal</th>
                    </tr>
                </thead><tbody>';

        if($data->landedCost()->exists()){
            foreach($data->landedCost as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.$row->code.'</td>
                <td class="center-align">'.$row->vendor->name.'</td>
                <td class="center-align">'.$row->note.'</td>
                <td class="center-align">'.number_format($row->total,2,',','.').'</td>
                <td class="center-align">'.number_format($row->tax,2,',','.').'</td>
                <td class="center-align">'.number_format($row->grandtotal,2,',','.').'</td>
            </tr>';
            }
        }else{
            $string .= '<tr>
            <td class="center-align" colspan="6">Landed cost tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function approval(Request $request,$id){
        
        $pr = GoodReceiptMain::where('code',CustomHelper::decrypt($id))->first();
                
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
        $grm = GoodReceiptMain::where('code',CustomHelper::decrypt($request->id))->first();
        $grm['warehouse_name'] = $grm->warehouse->name;

        $arr = [];
        
        foreach($grm->goodReceipt as $row){
            foreach($row->goodReceiptDetail as $rowdetail){
                $arr[] = [
                    'code'      => $row->purchaseOrder->code,
                    'ecode'     => CustomHelper::encrypt($row->purchaseOrder->code),
                    'item_id'   => $rowdetail->item_id,
                    'item_name' => $rowdetail->item->name,
                    'qty'       => $rowdetail->qty,
                    'unit'      => $rowdetail->item->buyUnit->code,
                    'note'      => $rowdetail->note,
                ];
            }
        }

        $grm['details'] = $arr;
        				
		return response()->json($grm);
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

                foreach($query->goodReceipt as $gr){
                    CustomHelper::removeJournal('good_receipts',$gr->id);
                    CustomHelper::removeCogs('good_receipts',$gr->id);
                }
    
                activity()
                    ->performedOn(new GoodReceipt())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the good receipt data');
    
                CustomHelper::sendNotification('good_receipt_mains',$query->id,'Good Receipt No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('good_receipt_mains',$query->id);
                
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

            foreach($query->goodReceipt as $gr){
                CustomHelper::removeJournal('good_receipts',$gr->id);
                CustomHelper::removeCogs('good_receipts',$gr->id);
            }

            $query->goodReceiptDetail()->delete();

            CustomHelper::removeApproval('good_receipt_mains',$query->id);

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
            'data' => GoodReceiptMain::where(function ($query) use ($request) {
                if($request->search) {
                    $query->where(function($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('post_date', 'like', "%$request->search%")
                            ->orWhere('due_date', 'like', "%$request->search%")
                            ->orWhere('document_date', 'like', "%$request->search%")
                            ->orWhere('receiver_name', 'like', "%$request->search%")
                            ->orWhere('note', 'like', "%$request->search%")
                            ->orWhereHas('goodReceipt', function($query) use($request){
                                $query->whereHas('goodReceiptDetail',function($query) use($request){
                                    $query->whereHas('item',function($query) use($request){
                                        $query->where('code', 'like', "%$request->search%")
                                            ->orWhere('name','like',"%$request->search%");
                                    });
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