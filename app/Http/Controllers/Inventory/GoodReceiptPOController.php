<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\ApprovalMatrix;
use App\Models\ApprovalSource;
use App\Models\PurchaseOrderDetail;
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
            'company'   => Company::where('status','1')->get(),
            'place'     => Place::whereIn('id',$this->dataplaces)->where('status','1')->get(),
            'department'=> Department::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'user_id',
            'account_id',
            'code',
            'account_id',
            'receiver_name',
            'post_date',
            'due_date',
            'document_date',
            'note',
            'delivery_no',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = GoodReceipt::count();
        
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
            })
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
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-id="' . $val->id . '"><i class="material-icons">add</i></button>',
                    $val->user->name,
                    $val->account->name,
                    $val->company->name,
                    $val->code,
                    $val->receiver_name,
                    date('d M Y',strtotime($val->post_date)),
                    date('d M Y',strtotime($val->due_date)),
                    date('d M Y',strtotime($val->document_date)),
                    $val->note,
                    $val->delivery_no,
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan darken-4 white-tex btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
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
        $data['account_name'] = $data->supplier->employee_no.' - '.$data->supplier->name;

        if($data->used()->exists()){
            $data['status'] = '500';
            $data['message'] = 'Purchase Order '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.';
        }else{
            if($data->hasBalance()){
                CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Good Receipt');
                $details = [];
                foreach($data->purchaseOrderDetail as $row){
                    $details[] = [
                        'purchase_order_detail_id'  => $row->id,
                        'item_id'                   => $row->item_id,
                        'item_name'                 => $row->item->code.' - '.$row->item->name,
                        'qty'                       => number_format($row->getBalanceReceipt(),3,',','.'),
                        'unit'                      => $row->item->buyUnit->code,
                        'place_id'                  => $row->place_id,
                        'place_name'                => $row->place->name.' - '.$row->place->company->name,
                        'department_id'             => $row->department_id,
                        'department_name'           => $row->department->name,
                        'warehouse_id'              => $row->warehouse_id,
                        'warehouse_name'            => $row->warehouse->name,
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

    public function getPurchaseOrderAll(Request $request){
        $rows = PurchaseOrder::where('account_id',$request->id)->where('status','2')->get();
        
        $arrdata = [];
        
        foreach($rows as $data){
            if(!$data->used()->exists()){
                if($data->hasBalance()){
                    CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Good Receipt');
                    $details = [];

                    foreach($data->purchaseOrderDetail as $row){
                        $details[] = [
                            'purchase_order_detail_id'  => $row->id,
                            'item_id'                   => $row->item_id,
                            'item_name'                 => $row->item->code.' - '.$row->item->name,
                            'qty'                       => number_format($row->getBalanceReceipt(),3,',','.'),
                            'unit'                      => $row->item->buyUnit->code,
                            'place_id'                  => $row->place_id,
                            'place_name'                => $row->place->name.' - '.$row->place->company->name,
                            'department_id'             => $row->department_id,
                            'department_name'           => $row->department->name,
                            'warehouse_id'              => $row->warehouse_id,
                            'warehouse_name'            => $row->warehouse->name,
                        ];
                    }
    
                    $data['details'] = $details;
                    $arrdata[] = $data;
                }
            }
        }

        return response()->json($arrdata);
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'account_id'                => 'required',
            'company_id'                => 'required',
			'receiver_name'			    => 'required',
			'post_date'		            => 'required',
			'due_date'		            => 'required',
            'document_date'		        => 'required',
            'delivery_no'		        => 'required',
            'arr_item'                  => 'required|array',
            'arr_qty'                   => 'required|array',
		], [
            'account_id.required'               => 'Supplier/vendor tidak boleh kosong.',
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
            'receiver_name.required'            => 'Nama penerima tidak boleh kosong.',
			'post_date.required' 				=> 'Tanggal posting tidak boleh kosong.',
			'due_date.required' 				=> 'Tanggal kadaluwarsa tidak boleh kosong.',
            'document_date.required' 			=> 'Tanggal dokumen tidak boleh kosong.',
            'delivery_no.required' 			    => 'No surat jalan tidak boleh kosong.',
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
            
            $totalall = 0;
            $taxall = 0;
            $wtaxall = 0;
            $grandtotalall = 0;
            $overtolerance = false;
            $wtax = 0;
            $total = 0;
            $grandtotal = 0;
            $tax = 0;

            foreach($request->arr_purchase as $key => $row){

                $pod = PurchaseOrderDetail::find(intval($row));

                if($pod){

                    $tolerance_gr = User::find($request->account_id)->tolerance_gr;

                    if($tolerance_gr){
                        $balance = floatval(str_replace(',','.',str_replace('.','',$request->arr_qty[$key]))) - $pod->qty;
                        $percent_balance = round(($balance / $pod->qty) * 100,2);
                        if($percent_balance > $tolerance_gr){
                            $overtolerance = true;
                        }
                    }

                    $discount = $pod->purchaseOrder->discount;
                    $subtotal = $pod->purchaseOrder->subtotal;

                    $rowprice = 0;

                    $bobot = $pod->subtotal / $subtotal;
                    $rowprice = round($pod->subtotal / $pod->qty,3);

                    $total = ($rowprice * floatval(str_replace(',','.',str_replace('.','',$request->arr_qty[$key])))) - ($bobot * $discount);

                    if($pod->is_tax == '1' && $pod->is_include_tax == '1'){
                        $total = $total / (1 + ($pod->percent_tax / 100));
                    }

                    if($pod->is_tax == '1'){
                        $tax = round($total * ($pod->percent_tax / 100),3);
                    }

                    if($pod->is_wtax == '1'){
                        $wtax = round($total * ($pod->percent_wtax / 100),3);
                    }

                    $grandtotal = $total + $tax - $wtax;

                    $totalall += $total;
                    $taxall += $tax;
                    $wtaxall += $wtax;
                    $grandtotalall += $grandtotal;
                }
            }

            if($overtolerance){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Prosentase qty diterima melebihi prosentase toleransi yang telah diatur.'
                ]);
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
                                    'message' => 'Purchase Order / Pembelian telah diapprove, anda tidak bisa melakukan perubahan.'
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
                        $query->account_id = $request->account_id;
                        $query->company_id = $request->company_id;
                        $query->receiver_name = $request->receiver_name;
                        $query->post_date = $request->post_date;
                        $query->due_date = $request->due_date;
                        $query->document_date = $request->document_date;
                        $query->delivery_no = $request->delivery_no;
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->total = $totalall;
                        $query->tax = $taxall;
                        $query->wtax = $wtaxall;
                        $query->grandtotal = $grandtotalall;
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
                        'account_id'            => $request->account_id,
                        'company_id'            => $request->company_id,
                        'receiver_name'         => $request->receiver_name,
                        'post_date'             => $request->post_date,
                        'due_date'              => $request->due_date,
                        'document_date'         => $request->document_date,
                        'delivery_no'           => $request->delivery_no,
                        'document'              => $request->file('document') ? $request->file('document')->store('public/good_receipts') : NULL,
                        'note'                  => $request->note,
                        'status'                => '1',
                        'total'                 => $totalall,
                        'tax'                   => $taxall,
                        'wtax'                  => $wtaxall,
                        'grandtotal'            => $grandtotalall
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                DB::beginTransaction();
                try {
                    foreach($request->arr_purchase as $key => $row){
                        GoodReceiptDetail::create([
                            'good_receipt_id'           => $query->id,
                            'purchase_order_detail_id'  => $row,
                            'item_id'                   => $request->arr_item[$key],
                            'qty'                       => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            'note'                      => $request->arr_note[$key],
                            'remark'                    => $request->arr_remark[$key],
                            'place_id'                  => $request->arr_place[$key],
                            'department_id'             => $request->arr_department[$key],
                            'warehouse_id'              => $request->arr_warehouse[$key]
                        ]);
                    }

                    CustomHelper::sendApproval('good_receipts',$query->id,$query->note);
                    CustomHelper::sendNotification('good_receipts',$query->id,'Pengajuan Penerimaan Barang No. '.$query->code,$query->note,session('bo_id'));
                    
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
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
        
        $string = '<div class="row pt-1 pb-1 lime lighten-4">
                        <div class="col s12">
                            <table class="bordered" style="width:auto;">
                                <thead>
                                    <tr>
                                        <th class="center-align" colspan="9">Daftar Item</th>
                                    </tr>
                                    <tr>
                                        <th class="center-align">No.</th>
                                        <th class="center-align">Item</th>
                                        <th class="center-align">Qty</th>
                                        <th class="center-align">Satuan</th>
                                        <th class="center-align">Keterangan</th>
                                        <th class="center-align">Remark</th>
                                        <th class="center-align">Site</th>
                                        <th class="center-align">Departemen</th>
                                        <th class="center-align">Gudang</th>
                                    </tr>
                                </thead>
                                <tbody>';
        
        foreach($data->goodReceiptDetail as $key => $rowdetail){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$rowdetail->item->name.'</td>
                <td class="center-align">'.number_format($rowdetail->qty,3,',','.').'</td>
                <td class="center-align">'.$rowdetail->item->buyUnit->code.'</td>
                <td class="center-align">'.$rowdetail->note.'</td>
                <td class="center-align">'.$rowdetail->remark.'</td>
                <td class="center-align">'.$rowdetail->place->name.' - '.$rowdetail->place->company->name.'</td>
                <td class="center-align">'.$rowdetail->department->name.'</td>
                <td class="center-align">'.$rowdetail->warehouse->name.'</td>
            </tr>';
        }
        
        $string .= '</tbody></table>';

        $string .= '</td></tr>';

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
        $grm = GoodReceipt::where('code',CustomHelper::decrypt($request->id))->first();
        $grm['account_name'] = $grm->account->name;

        $arr = [];
        
        foreach($grm->goodReceiptDetail as $row){
            $arr[] = [
                'purchase_order_detail_id'  => $row->purchase_order_detail_id,
                'item_id'       => $row->item_id,
                'item_name'     => $row->item->name,
                'qty'           => $row->qty,
                'unit'          => $row->item->buyUnit->code,
                'note'          => $row->note,
                'place_id'      => $row->place_id,
                'place_name'    => $row->place->name.' - '.$row->place->company->name,
                'department_id' => $row->department_id,
                'department_name'   => $row->department->name,
                'warehouse_id'  => $row->warehouse_id,
                'warehouse_name'=> $row->warehouse->name
            ];
        }

        $grm['details'] = $arr;
        				
		return response()->json($grm);
    }

    public function voidStatus(Request $request){
        $query = GoodReceipt::where('code',CustomHelper::decrypt($request->id))->first();
        
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

                CustomHelper::removeJournal('good_receipts',$query->id);
                CustomHelper::removeCogs('good_receipts',$query->id);
                CustomHelper::sendNotification('good_receipts',$query->id,'Good Receipt No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('good_receipts',$query->id);
    
                activity()
                    ->performedOn(new GoodReceipt())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the good receipt data');
                
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

        if($query->approval()){
            foreach($query->approval()->approvalMatrix as $row){
                if($row->status == '2'){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Purchase Order telah diapprove / sudah dalam progres, anda tidak bisa melakukan perubahan.'
                    ]);
                }
            }
        }

        if(in_array($query->status,['2','3','4','5'])){
            return response()->json([
                'status'  => 500,
                'message' => 'Jurnal sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            CustomHelper::removeJournal('good_receipts',$query->id);
            CustomHelper::removeCogs('good_receipts',$query->id);
            CustomHelper::removeApproval('good_receipts',$query->id);

            $query->goodReceiptDetail()->delete();

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
            ->get()
		];
		
		return view('admin.print.inventory.good_receipt', $data);
    }

    public function export(Request $request){
		return Excel::download(new ExportGoodReceipt($request->search,$request->status,$this->dataplaces), 'good_receipt_'.uniqid().'.xlsx');
    }
    
    public function viewStructureTree(Request $request){
        $query = GoodReceipt::where('code',CustomHelper::decrypt($request->id))->first();
        $data_link=[];

        $data_good_receipts=[];
        $data_purchase_requests=[];
        $data_go_chart=[];

        $data_id_po = [];
        $data_id_gr = [];
        $data_id_invoice=[];
       
        $data_pos = [];
        if($query) {
            $data_good_receipt = [
                "name"=>$query->code,
                "key" => $query->code,
                "color"=>"lightblue",
                'properties'=> [
                    ['name'=> "Tanggal :".$query->post_date],
                ],
                'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($query->code),
            ];
            $data_go_chart[]=$data_good_receipt;
            $data_good_receipts[]=$data_good_receipt;
            $data_id_gr[]=$query->id;
            
            foreach($query->goodReceiptDetail as $good_receipt_detail){
                $data_po = [
                    "key" => $good_receipt_detail->purchaseOrderDetail->purchaseOrder->code,
                    "name" => $good_receipt_detail->purchaseOrderDetail->purchaseOrder->code,
                    'grandtotal'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->grandtotal,
                    'properties'=> [
                        ['name'=> "Tanggal :".$good_receipt_detail->purchaseOrderDetail->purchaseOrder->post_date],
                    ],
                    'arrowDirection'=>"left",
                    'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($good_receipt_detail->purchaseOrderDetail->purchaseOrder->code),
                ];
                
                $purchase_order_detail = $good_receipt_detail->purchaseOrderDetail;
                
                if($purchase_order_detail->purchaseRequestDetail()->exists()){
                    
                    $purchase_request_detail=$purchase_order_detail->purchaseRequestDetail;
                    $pr = [
                        "key" => $purchase_request_detail->purchaseRequest->code,
                        "name" => $purchase_request_detail->purchaseRequest->code,
                        'properties'=> [
                            ['name'=> "Tanggal :".$purchase_request_detail->purchaseRequest->post_date],
                        ],
                        'arrowDirection'=>"left",
                        'url'=>request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($purchase_request_detail->purchaseRequest->code),
                    ];
                    if(count($data_purchase_requests)<1){
                        $data_purchase_requests[]=$pr;
                        $data_go_chart[]=$pr;
                        $data_link[]=[
                            'from'=>$pr["key"],
                            'to'=>$data_po["key"],
                        ];  
                        
                    }else{
                        $found = false;
                        foreach ($data_purchase_requests as $key => $row_pos) {
                            if ($row_pos["key"] == $pr["key"]) {
                                $found = true;
                                break;
                            }
                        }
                        if($found){
                            $data_links=[
                                'from'=>$pr["key"],
                                'to'=>$data_po["key"],
                            ];  
                            $found_inlink = false;
                            foreach($data_link as $key=>$row_link){
                                if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                    $found_inlink = true;
                                    break;
                                }
                            }
                            if(!$found_inlink){
                                $data_link[] = $data_links;
                            }
                            
                        }
                        if (!$found) {
                            $data_purchase_requests[]=$pr;
                            $data_go_chart[]=$pr;
                            $data_link[]=[
                                'from'=>$pr["key"],
                                'to'=>$data_po["key"],
                            ];  
                        }
                    }
                }
                if(count($data_pos)<1){
                    $data_pos[]=$data_po;
                    $data_go_chart[]=$data_po;
                    $data_link[]=[
                        'from'=>$data_po["key"],
                        'to'=>$query->code,
                    ];  
                    $data_id_po[]=$good_receipt_detail->purchaseOrderDetail->purchaseOrder->id;
                }else{
                    $found = false;
                    foreach ($data_pos as $key => $row_pos) {
                        if ($row_pos["key"] == $data_po["key"]) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $data_pos[] = $data_po;
                        $data_go_chart[]=$data_po;
                        $data_link[]=[
                            'from'=>$data_po["key"],
                            'to'=>$query->code,
                        ];
                    $data_id_po[]=$good_receipt_detail->purchaseOrderDetail->purchaseOrder->id;  
                    }
                }
            }

            //pengambilan foreign branch
            $data_lcs=[];
            $data_invoices=[];
            $added = true;
            while($added){
                $added=false;
                //Pengambilan foreign branch gr
                foreach($data_id_gr as $gr_id){
                    $query_gr = GoodReceipt::where('id',$gr_id)->first();
                    foreach($query_gr->goodReceiptDetail as $good_receipt_detail){
                        $po = [
                            'properties'=> [
                                ['name'=> "Tanggal: ".$good_receipt_detail->purchaseOrderDetail->purchaseOrder->post_date],
                            ],
                            'key'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code,
                            'name'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code,
                            'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($good_receipt_detail->purchaseOrderDetail->purchaseOrder->code),
                        ];
                        if(count($data_pos)<1){
                            $data_pos[]=$po;
                            $data_go_chart[]=$po;
                            $data_link[]=[
                                'from'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code,
                                'to'=>$query_gr->code,
                            ];
                            $data_id_po[]= $good_receipt_detail->purchaseOrderDetail->purchaseOrder->id; 
                            
                        }else{
                            $found = false;
                            foreach ($data_pos as $key => $row_pos) {
                                if ($row_pos["key"] == $po["key"]) {
                                    $found = true;
                                    break;
                                }
                            }
                            if (!$found) {
                                $data_pos[] = $po;
                                $data_link[]=[
                                    'from'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code,
                                    'to'=>$query_gr->code,
                                ];  
                                $data_go_chart[]=$po;
                                $data_id_po[]= $good_receipt_detail->purchaseOrderDetail->purchaseOrder->id;
                            }
                        }

                    }

                    //landed cost searching
                    if($query_gr->landedCost()->exists()){
                        foreach($query_gr->landedCost as $landed_cost){
                            $data_lc=[
                                'properties'=> [
                                    ['name'=> "Tanggal : ".$landed_cost->post_date],
                                ],
                                'key'=>$landed_cost->code,
                                'name'=>$landed_cost->code,
                                'url'=>request()->root()."/admin/purchase/landed_cost?code=".CustomHelper::encrypt($landed_cost->code),    
                            ];
                            if(count($data_lcs)<1){
                                $data_lcs[]=$data_lc;
                                $data_go_chart[]=$data_lc;
                                $data_link[]=[
                                    'from'=>$query_gr->code,
                                    'to'=>$landed_cost->code,
                                ];
                                $data_id_lc = $landed_cost->id;
                            }else{
                                $found = false;
                                foreach ($data_lcs as $key => $row_lc) {
                                    if ($row_lc["key"] == $data_lc["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $data_lcs[]=$data_lc;
                                    $data_go_chart[]=$data_lc;
                                    $data_link[]=[
                                        'from'=>$query_gr->code,
                                        'to'=>$landed_cost->code,
                                    ];
                                    $data_id_lc = $landed_cost->id;
                                }
                            }
                            
                        }
                    }
                    //invoice searching
                    if($query_gr->purchaseInvoiceDetail()->exists()){
                        foreach($query_gr->purchaseInvoiceDetail as $invoice_detail){
                            $invoice_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal : ".$invoice_detail->purchaseInvoice->post_date],
                                ],
                                'key'=>$invoice_detail->purchaseInvoice->code,
                                'name'=>$invoice_detail->purchaseInvoice->code,
                                'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($invoice_detail->purchaseInvoice->code)
                            ];
                            if(count($data_invoices)<1){
                                $data_invoices[]=$invoice_tempura;
                                $data_go_chart[]=$invoice_tempura;
                                $data_link[]=[
                                    'from'=>$query_gr->code,
                                    'to'=>$invoice_detail->purchaseInvoice->code,
                                ];
                                $data_id_invoice[]=$invoice_detail->purchaseInvoice->id;
                            }else{
                                $found = false;
                                foreach ($data_invoices as $key => $row_invoice) {
                                    if ($row_invoice["key"] == $invoice_tempura["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $data_invoices[]=$invoice_tempura;
                                    $data_go_chart[]=$invoice_tempura;
                                    $data_link[]=[
                                        'from'=>$query_gr->code,
                                        'to'=>$invoice_detail->purchaseInvoice->code,
                                    ];
                                    $data_id_invoice[]=$invoice_detail->purchaseInvoice->id;
                                }
                            }
                        }
                    }

                }

                foreach($data_id_invoice as $invoice_id){
                    $query_invoice = PurchaseInvoice::where('id',$invoice_id)->first();
                    foreach($query_invoice->purchaseInvoiceDetail as $row){
                        if($row->purchaseOrder()->exists()){
                            foreach($row->purchaseOrder as $row_po){
                                $po =[
                                    "name"=>$row_po->code,
                                    "key" => $row_po->code,
                                    "color"=>"lightblue",
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_po->post_date],
                                     ],
                                    'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($row_po->post_date),           
                                ];
                                /*memasukkan ke node data dan linknya*/
                                if(count($data_pos)<1){
                                    $data_pos[]=$po;
                                    $data_go_chart[]=$po;
                                    $data_link[]=[
                                        'from'=>$query_invoice->code,
                                        'to'=>$row_po->code,
                                    ]; 
                                    $data_id_po[]= $purchase_order_detail->purchaseOrder->id;  
                                    
                                }else{
                                    $found = false;
                                    foreach ($data_pos as $key => $row_pos) {
                                        if ($row_pos["key"] == $po["key"]) {
                                            $found = true;
                                            break;
                                        }
                                    }
                                    //po yang memiliki request yang sama
                                    if($found){
                                        $data_link[]=[
                                            'from'=>$query_invoice->code,
                                            'to'=>$row_po->code,
                                        ]; 
                                        $found_inlink = false;
                                        foreach($data_link as $key=>$row_link){
                                            if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                                $found_inlink = true;
                                                break;
                                            }
                                        }
                                        if(!$found_inlink){
                                            $data_link[] = $data_links;
                                        }
                                        
                                    }
                                    if (!$found) {
                                        $data_pos[] = $po;
                                        $data_link[]=[
                                            'from'=>$query_invoice->code,
                                            'to'=>$row_po->code,
                                        ];  
                                        $data_go_chart[]=$po;
                                        $data_id_po[]= $purchase_order_detail->purchaseOrder->id; 
                                    }
                                }
                                //memasukkan dengan yang sama atau tidak
                                
                                foreach($row_po->purchaseOrderDetail as $po_detail){
                                    if($po_detail->goodReceiptDetail->exists()){
                                        foreach($po_detail->goodReceiptDetail as $good_receipt_detail){
                                            $data_good_receipt=[
                                                'properties'=> [
                                                    ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                                                    ['name'=> "url", 'type'=> request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code)],
                                                 ],
                                                "key" => $good_receipt_detail->goodReceipt->code,
                                                "name" => $good_receipt_detail->goodReceipt->code,
                                                'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code),
                                            ];
                                            if(count($data_good_receipts)<1){
                                                $data_good_receipts[]=$data_good_receipt;
                                                $data_go_chart[]=$data_good_receipt;
                                                $data_link[]=[
                                                    'from'=>$row_po->code,
                                                    'to'=>$data_good_receipt["key"],
                                                ];
                                                $data_id_gr[]=$good_receipt_detail->goodReceipt->id;  
                                            }else{
                                                $found = false;
                                                foreach ($data_good_receipts as $key => $row_pos) {
                                                    if ($row_pos["key"] == $data_good_receipt["key"]) {
                                                        $found = true;
                                                        break;
                                                    }
                                                }
                                                if (!$found) {
                                                    $data_good_receipts[]=$data_good_receipt;
                                                    $data_go_chart[]=$data_good_receipt;
                                                    $data_link[]=[
                                                        'from'=>$row_po->code,
                                                        'to'=>$data_good_receipt["key"],
                                                    ]; 
                                                    $data_id_gr[]=$good_receipt_detail->goodReceipt->id; 
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        /*  melihat apakah ada hubungan grpo tanpa po */
                        if($row->goodReceipt()->exists()){
        
                            $data_good_receipt=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->goodReceipt->post_date],
                                ],
                                "key" => $row->goodReceipt->code,
                                "name" => $row->goodReceipt->code,
                                'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($row->goodReceipt->code),
                            ];
        
                            if(count($data_good_receipts)<1){
                                $data_good_receipts[]=$data_good_receipt;
                                $data_go_chart[]=$data_good_receipt;
                                $data_link[]=[
                                    'from'=>$query_invoice->code,
                                    'to'=>$data_good_receipt["key"],
                                ];
                                $data_id_gr[]=$row->goodReceipt->id;   
                            }else{
                                $found = false;
                                foreach ($data_good_receipts as $key => $row_pos) {
                                    if ($row_pos["key"] == $data_good_receipt["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $data_good_receipts[]=$data_good_receipt;
                                    $data_go_chart[]=$data_good_receipt;
                                    $data_link[]=[
                                        'from'=>$query_invoice->code,
                                        'to'=>$data_good_receipt["key"],
                                    ]; 
                                    $data_id_gr[]=$row->goodReceipt->id; 
                                }
                            } 
                        }
                        /* melihat apakah ada hubungan lc */
                        if($row->landedCost()->exists()){
                            $data_lc=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->landedCost->post_date],
                                ],
                                "key" => $row->landedCost->code,
                                "name" => $row->landedCost->code,
                                'url'=>request()->root()."/admin/inventory/landed_cost?code=".CustomHelper::encrypt($row->landedCost->code),
                            ];
                            if(count($data_lcs)<1){
                                $data_lcs[]=$data_lc;
                                $data_go_chart[]=$data_lc;
                                $data_link[]=[
                                    'from'=>$query_invoice->code,
                                    'to'=>$row->landedCost->code,
                                ];
                                $data_id_lc = $row->landedCost->id;
                            }else{
                                $found = false;
                                foreach ($data_lcs as $key => $row_lc) {
                                    if ($row_lc["key"] == $data_lc["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $data_lcs[]=$data_lc;
                                    $data_go_chart[]=$data_lc;
                                    $data_link[]=[
                                        'from'=>$query_invoice->code,
                                        'to'=>$row->landedCost->code,
                                    ];
                                    $data_id_lc = $row->landedCost->id;
                                }
                            }
                        }
                        
                    }
                    if($query_invoice->purchaseInvoiceDp()->exists()){
                        foreach($query_invoice->purchaseInvoiceDp as $row_pi){
                            $data_down_payment=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pi->purchaseDownPayment->post_date],
                                ],
                                "key" => $row_pi->purchaseDownPayment->code,
                                "name" => $row_pi->purchaseDownPayment->code,
                                'url'=>request()->root()."/admin/inventory/landed_cost?code=".CustomHelper::encrypt($row_pi->purchaseDownPayment->code),
                            ];
                            $data_go_chart[]=$data_down_payment;
                            $data_link[]=[
                                'from'=>$row_pi->purchaseDownPayment->code,
                                'to'=>$query_invoice->code,
                            ];
                        }
                    }
                }

                //Pengambilan foreign branch po
                foreach($data_id_po as $po_id){
                    $query_po = PurchaseOrder::find($po_id);
                    info($query_po);
                    foreach($query_po->purchaseOrderDetail as $purchase_order_detail){
                        info($purchase_order_detail);
                        if($purchase_order_detail->purchaseRequestDetail()->exists()){
                            info("halo");
                            $pr_tempura=[
                                'key'   => $purchase_order_detail->purchaseRequestDetail->purchaseRequest->code,
                                "name"  => $purchase_order_detail->purchaseRequestDetail->purchaseRequest->code,
                            
                                'properties'=> [
                                    ['name'=> "Tanggal: ".$purchase_order_detail->purchaseRequestDetail->purchaseRequest->post_date],
                                ],
                                'url'   =>request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($purchase_order_detail->purchaseRequestDetail->purchaseRequest->code),
                            ];
                            if($data_purchase_requests < 1){
                                $data_purchase_requests[]=$pr_tempura;
                                $data_go_chart[]=$pr_tempura;
                                $data_link[]=[
                                    'from'=>$purchase_order_detail->purchaseRequestDetail->purchaseRequest->code,
                                    'to'=>$query_po->code,
                                ];
                            }else{
                                $found = false;
                                foreach ($data_purchase_requests as $key => $row_pr) {
                                    if ($row_pr["key"] == $pr_tempura["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                //pr yang memiliki request yang sama
                                if($found){
                                    $data_links=[
                                        'from'=>$purchase_order_detail->purchaseRequestDetail->purchaseRequest->code,
                                        'to'=>$query_po->code,
                                    ];  
                                    $found_inlink = false;
                                    foreach($data_link as $key=>$row_link){
                                        if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                            $found_inlink = true;
                                            break;
                                        }
                                    }
                                    if(!$found_inlink){
                                        $data_link[] = $data_links;
                                    }
                                    
                                }
                                if (!$found) {
                                    $data_purchase_requests[]=$pr_tempura;
                                    $data_go_chart[]=$pr_tempura;
                                    $data_link[]=[
                                        'from'=>$purchase_order_detail->purchaseRequestDetail->purchaseRequest->code,
                                        'to'=>$query_po->code,
                                    ];
                                }
                            }
                        }
                        if($purchase_order_detail->goodReceiptDetail()->exists()){
                            foreach($purchase_order_detail->goodReceiptDetail as $good_receipt_detail){
                                $data_good_receipt = [
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                                        ['name'=> "url", 'type'=> request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code)],
                                     ],
                                    "key" => $good_receipt_detail->goodReceipt->code,
                                    "name" => $good_receipt_detail->goodReceipt->code,
                                    
                                    'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code),
                                    
                                ];
                                if(count($data_good_receipts)<1){
                                    $data_good_receipts[]=$data_good_receipt;
                                    $data_link[]=[
                                        'from'=>$purchase_order_detail->purchaseOrder->code,
                                        'to'=>$data_good_receipt["key"],
                                    ];
                                   
                                    $data_go_chart[]=$data_good_receipt;  
                                }else{
                                    $found = false;
                                    foreach($data_good_receipts as $tempdg){
                                        if ($tempdg["key"] == $data_good_receipt["key"]) {
                                            $found = true;
                                            break;
                                        }
                                    }
                                    if($found){
                                        $data_links=[
                                            'from'=>$purchase_order_detail->purchaseOrder->code,
                                            'to'=>$data_good_receipt["key"],
                                        ];  
                                        $found_inlink = false;
                                        foreach($data_link as $key=>$row_link){
                                            if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                                $found_inlink = true;
                                                break;
                                            }
                                        }
                                        if(!$found_inlink){
                                            $data_link[] = $data_links;
                                        }
                                        
                                    }
                                    if (!$found) {
                                        $data_good_receipts[]=$data_good_receipt;
                                        $data_link[]=[
                                            'from'=>$purchase_order_detail->purchaseOrder->code,
                                            'to'=>$data_good_receipt["key"],
                                        ];  
                                       
                                        $data_go_chart[]=$data_good_receipt; 
                                    }
                                }
                                if(!in_array($good_receipt_detail->goodReceipt->id, $data_id_gr)){
                                    $data_id_gr[] = $good_receipt_detail->goodReceipt->id;
                                    $added = true;
                                }
                            }
                        }
                    }

                }
            }
            
            $response = [
                'status'  => 200,
                'message' => $data_go_chart,
                'link'    => $data_link
            ];
        } else {
            $data_good_receipt = [];
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }
        return response()->json($response);
    }

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData('purchase_orders',$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }
}