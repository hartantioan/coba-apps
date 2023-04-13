<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PurchaseOrder;
use App\Models\Currency;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseDownPaymentDetail;
use App\Helpers\CustomHelper;
use App\Exports\ExportPurchaseDownPayment;
use App\Models\Place;
use App\Models\User;
use App\Models\Department;

class PurchaseDownPaymentController extends Controller
{
    protected $dataplaces;

    public function __construct(){
        $user = User::find(Session::get('bo_id'));

        $this->dataplaces = $user->userPlaceArray();
    }

    public function index()
    {
        $data = [
            'title'         => 'Purchase Down Payment',
            'content'       => 'admin.purchase.down_payment',
            'currency'      => Currency::where('status','1')->get(),
            'place'         => Place::whereIn('id',$this->dataplaces)->where('status','1')->get(),
            'department'    => Department::where('status','1')->get()
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getPurchaseOrder(Request $request){
        $data = PurchaseOrder::where('account_id',$request->supplier)->where('status','2')->get();

        $details = [];

        foreach($data as $row){
            $details[] = [
                'po_code'       => CustomHelper::encrypt($row->code),
                'po_no'         => $row->code,
                'post_date'     => date('d/m/y',strtotime($row->post_date)),
                'delivery_date' => date('d/m/y',strtotime($row->delivery_date)),
                'grandtotal'    => number_format($row->grandtotal,2,',','.'),
            ];
        }

        return response()->json($details);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'place_id',
            'department_id',
            'is_tax',
            'is_included_tax',
            'percent_tax',
            'type',
            'document',
            'post_date',
            'due_date',
            'currency_id',
            'currency_rate',
            'note',
            'subtotal',
            'discount',
            'total',
            'tax',
            'grandtotal'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = PurchaseDownPayment::whereIn('place_id',$this->dataplaces)->count();
        
        $query_data = PurchaseDownPayment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('due_date', 'like', "%$search%")
                            ->orWhere('subtotal', 'like', "%$search%")
                            ->orWhere('discount', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('purchaseDownPaymentDetail',function($query) use($search, $request){
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

                if($request->type){
                    $query->where('type',$request->type);
                }

                if($request->supplier_id){
                    $query->whereIn('account_id',$request->supplier_id);
                }
                
                if($request->place_id){
                    $query->where('place_id',$request->place_id);
                }

                if($request->department_id){
                    $query->where('department_id',$request->department_id);
                }
                
                if($request->is_tax){
                    if($request->is_tax == '1'){
                        $query->whereNotNull('is_tax');
                    }else{
                        $query->whereNull('is_tax');
                    }
                }

                if($request->is_include_tax){
                    $query->where('is_include_tax',$request->is_include_tax);
                }
                
                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
                }
            })
            ->whereIn('place_id',$this->dataplaces)
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = PurchaseDownPayment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('due_date', 'like', "%$search%")
                            ->orWhere('subtotal', 'like', "%$search%")
                            ->orWhere('discount', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('purchaseDownPaymentDetail',function($query) use($search, $request){
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

                if($request->type){
                    $query->where('type',$request->type);
                }

                if($request->supplier_id){
                    $query->whereIn('account_id',$request->supplier_id);
                }

                if($request->place_id){
                    $query->where('place_id',$request->place_id);
                }

                if($request->department_id){
                    $query->where('department_id',$request->department_id);
                }
                
                if($request->is_tax){
                    if($request->is_tax == '1'){
                        $query->whereNotNull('is_tax');
                    }else{
                        $query->whereNull('is_tax');
                    }
                }

                if($request->is_include_tax){
                    $query->where('is_include_tax',$request->is_include_tax);
                }
                
                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
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
                    $val->code,
                    $val->user->name,
                    $val->supplier->name,
                    $val->place->name.' - '.$val->place->company->name,
                    $val->department->name,
                    $val->isTax(),
                    $val->isIncludeTax(),
                    number_format($val->percent_tax,2,',','.'),
                    $val->type(),
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    date('d M Y',strtotime($val->post_date)),
                    date('d M Y',strtotime($val->due_date)),
                    $val->currency->code,
                    number_format($val->currency_rate,2,',','.'),
                    $val->note,
                    number_format($val->subtotal,2,',','.'),
                    number_format($val->discount,2,',','.'),
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
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

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
			'supplier_id' 				=> 'required',
			'type'                      => 'required',
            'place_id'                  => 'required',
            'department_id'             => 'required',
            'post_date'                 => 'required',
            'due_date'                  => 'required',
            'currency_id'               => 'required',
            'currency_rate'             => 'required',
            'subtotal'                  => 'required',
		], [
			'supplier_id.required' 				=> 'Supplier tidak boleh kosong.',
			'type.required'                     => 'Tipe tidak boleh kosong',
            'place_id.required'                 => 'Pabrik tidak boleh kosong.',
            'department_id.required'            => 'Departemen tidak boleh kosong.',
            'post_date.required'                => 'Tgl post tidak boleh kosong.',
            'due_date.required'                 => 'Tgl tenggat tidak boleh kosong.',
            'currency_id.required'              => 'Mata uang tidak boleh kosong.',
            'subtotal.required'                 => 'Subtotal tidak boleh kosong.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            
            $total = 0;
            $tax = 0;
            $grandtotal = 0;
            $subtotal = str_replace(',','.',str_replace('.','',$request->subtotal));
            $discount = str_replace(',','.',str_replace('.','',$request->discount));
            $percent_tax = str_replace(',','.',str_replace('.','',$request->percent_tax));

            $total = $subtotal - $discount;

            if($request->is_tax){
                if($request->is_include_tax){
                    $total = $total / (1 + ($percent_tax / 100));
                }
                $tax = $total * ($percent_tax / 100);
            }

            $grandtotal = round($total + $tax,3);


			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = PurchaseDownPayment::where('code',CustomHelper::decrypt($request->temp))->first();

                    if($query->approval()){
                        foreach($query->approval()->approvalMatrix as $row){
                            if($row->status == '2'){
                                return response()->json([
                                    'status'  => 500,
                                    'message' => 'Purchase Order Down Payment telah diapprove, anda tidak bisa melakukan perubahan.'
                                ]);
                            }
                        }
                    }

                    if($query->status == '1'){

                        CustomHelper::removeDeposit($query->account_id,$query->grandtotal);

                        if($request->has('document')) {
                            if(Storage::exists($query->document)){
                                Storage::delete($query->document);
                            }
                            $document = $request->file('document')->store('public/purchase_down_payments');
                        } else {
                            $document = $query->document;
                        }

                        $query->user_id = session('bo_id');
                        $query->account_id = $request->supplier_id;
                        $query->type = $request->type;
                        $query->place_id = $request->place_id;
                        $query->department_id = $request->department_id;
                        $query->is_tax = $request->is_tax ? $request->is_tax : NULL;
                        $query->is_include_tax = $request->is_include_tax ? $request->is_include_tax : '0';
                        $query->percent_tax = str_replace(',','.',str_replace('.','',$request->percent_tax));
                        $query->document = $document;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->post_date = $request->post_date;
                        $query->due_date = $request->due_date;
                        $query->note = $request->note;
                        $query->subtotal = round($subtotal,3);
                        $query->discount = $discount;
                        $query->total = round($total,3);
                        $query->tax = round($tax,3);
                        $query->grandtotal = round($grandtotal,3);

                        $query->save();

                        foreach($query->purchaseDownPaymentDetail as $row){
                            $row->delete();
                        }

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status purchase order sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = PurchaseDownPayment::create([
                        'code'			            => PurchaseDownPayment::generateCode(),
                        'user_id'		            => session('bo_id'),
                        'account_id'                => $request->supplier_id,
                        'type'	                    => $request->type,
                        'place_id'                  => $request->place_id,
                        'department_id'             => $request->department_id,
                        'is_tax'                    => $request->is_tax ? $request->is_tax : NULL,
                        'is_include_tax'            => $request->is_include_tax ? $request->is_include_tax : '0',
                        'percent_tax'               => str_replace(',','.',str_replace('.','',$request->percent_tax)),
                        'document'                  => $request->file('document') ? $request->file('document')->store('public/purchase_down_payments') : NULL,
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'post_date'                 => $request->post_date,
                        'due_date'                  => $request->due_date,
                        'note'                      => $request->note,
                        'subtotal'                  => round($subtotal,3),
                        'discount'                  => $discount,
                        'total'                     => round($total,3),
                        'tax'                       => round($tax,3),
                        'grandtotal'                => round($grandtotal,3),
                        'status'                    => '1'
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                
                if($request->arr_code){
                    foreach($request->arr_code as $key => $row){
                        DB::beginTransaction();
                        try {
                            PurchaseDownPaymentDetail::create([
                                'purchase_down_payment_id'      => $query->id,
                                'purchase_order_id'             => PurchaseOrder::where('code',CustomHelper::decrypt($row))->first()->id,
                                'nominal'                       => str_replace(',','.',str_replace('.','',$request->arr_nominal[$key])),
                                'note'                          => $request->arr_note[$key]
                            ]);
                            DB::commit();
                        }catch(\Exception $e){
                            DB::rollback();
                        }
                    }
                }

                CustomHelper::sendApproval('purchase_down_payments',$query->id,$query->note);
                CustomHelper::sendNotification('purchase_down_payments',$query->id,'Pengajuan Purchase Down Payment No. '.$query->code,$query->note,session('bo_id'));
                CustomHelper::addDeposit($query->account_id,$grandtotal);

                activity()
                    ->performedOn(new PurchaseDownPayment())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit purchase order down payment.');

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
        $data   = PurchaseDownPayment::find($request->id);
        
        $string = '<div class="row pt-1 pb-1 lime lighten-4"><div class="col s12"><table style="max-width:500px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="10">Daftar Order Pembelian</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">PO No.</th>
                                <th class="center-align">Tgl.Post</th>
                                <th class="center-align">Tgl.Kirim</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">Total DP</th>
                            </tr>
                        </thead><tbody>';
        
        if(count($data->purchaseDownPaymentDetail) > 0){
            foreach($data->purchaseDownPaymentDetail as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td class="center-align">'.$row->purchaseOrder->code.'</td>
                    <td class="center-align">'.date('d/m/y',strtotime($row->purchaseOrder->post_date)).'</td>
                    <td class="center-align">'.date('d/m/y',strtotime($row->purchaseOrder->delivery_date)).'</td>
                    <td class="center-align">'.$row->note.'</td>
                    <td class="right-align">'.number_format($row->purchaseOrder->grandtotal,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->nominal,2,',','.').'</td>
                </tr>';
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="8">Data referensi purchase tidak ditemukan.</td>
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
        
        $pdp = PurchaseDownPayment::where('code',CustomHelper::decrypt($id))->first();
                
        if($pdp){
            $data = [
                'title'     => 'Print Purchase Down Payment',
                'data'      => $pdp
            ];

            return view('admin.approval.purchase_down_payment', $data);
        }else{
            abort(404);
        }
    }

    public function show(Request $request){
        $pdp = PurchaseDownPayment::where('code',CustomHelper::decrypt($request->id))->first();
        $pdp['supplier_name'] = $pdp->supplier->name;
        $pdp['subtotal'] = number_format($pdp->subtotal,2,',','.');
        $pdp['discount'] = number_format($pdp->discount,2,',','.');
        $pdp['total'] = number_format($pdp->total,2,',','.');
        $pdp['tax'] = number_format($pdp->tax,2,',','.');
        $pdp['grandtotal'] = number_format($pdp->grandtotal,2,',','.');
        $pdp['percent_tax'] = number_format($pdp->percent_tax,2,',','.');

        $arr = [];

        foreach($pdp->purchaseDownPaymentDetail as $row){
            $arr[] = [
                'purchase_order_id'         => $row->purchase_order_id,
                'purchase_order_code'       => $row->purchaseOrder->code,
                'purchase_order_encrypt'    => CustomHelper::encrypt($row->purchaseOrder->code),
                'post_date'                 => date('d/m/y',strtotime($row->purchaseOrder->post_date)),
                'delivery_date'             => date('d/m/y',strtotime($row->purchaseOrder->delivery_date)),
                'note'                      => $row->note,
                'total'                     => number_format($row->purchaseOrder->grandtotal,2,',','.'),
                'total_dp'                  => number_format($row->nominal,2,',','.')
            ];
        }

        $pdp['details'] = $arr;
        				
		return response()->json($pdp);
    }

    public function voidStatus(Request $request){
        $query = PurchaseDownPayment::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {
            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }else{
                CustomHelper::removeDeposit($query->account_id,$query->grandtotal);
                CustomHelper::removeApproval('purchase_down_payments',$query->id);

                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new PurchaseDownPayment())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the purchase order down payment data');
    
                CustomHelper::sendNotification('purchase_down_payments',$query->id,'Purchase Order Down Payment No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
    
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
        $query = PurchaseDownPayment::where('code',CustomHelper::decrypt($request->id))->first();

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

        if(in_array($query->status,['2','3'])){
            return response()->json([
                'status'  => 500,
                'message' => 'Jurnal sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            CustomHelper::removeDeposit($query->account_id,$query->grandtotal);
            CustomHelper::removeApproval('purchase_down_payments',$query->id);

            $query->purchaseDownPaymentDetail()->delete();

            activity()
                ->performedOn(new PurchaseOrder())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the purchase order data');

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
            'title' => 'PURCHASE ORDER DOWN PAYMENT REPORT',
            'data' => PurchaseDownPayment::where(function ($query) use ($request) {
                if($request->search) {
                    $query->where(function($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('post_date', 'like', "%$request->search%")
                            ->orWhere('due_date', 'like', "%$request->search%")
                            ->orWhere('grandtotal', 'like', "%$request->search%")
                            ->orWhere('note', 'like', "%$request->search%")
                            ->orWhereHas('purchaseDownPaymentDetail',function($query) use($request){
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

                if($request->type){
                    $query->where('type',$request->type);
                }

                if($request->supplier){
                    $query->whereIn('account_id',$request->supplier);
                }
                
                if($request->place){
                    $query->where('place_id',$request->place);
                }

                if($request->department){
                    $query->where('department_id',$request->department);
                }            
                
                if($request->currency){
                    $query->whereIn('currency_id',$request->currency);
                }

                if($request->is_tax){
                    if($request->is_tax == '1'){
                        $query->whereNotNull('is_tax');
                    }else{
                        $query->whereNull('is_tax');
                    }
                }

                if($request->is_include_tax){
                    $query->where('is_include_tax',$request->is_include_tax);
                }
            })
            ->whereIn('place_id',$this->dataplaces)
            ->get()
		];
		
		return view('admin.print.purchase.down_payment', $data);
    }

    public function export(Request $request){
		return Excel::download(new ExportPurchaseDownPayment($request->search,$request->status,$request->type,$request->place,$request->department,$request->is_tax,$request->is_include_tax,$request->supplier,$request->currency,$this->dataplaces), 'purchase_down_payment_'.uniqid().'.xlsx');
    }
}