<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\GoodReceipt;
use App\Models\PurchaseInvoice;
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
use App\Models\User;
use App\Models\Tax;

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
            'company'       => Company::where('status','1')->get(),
            'tax'           => Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get()
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
            'company_id',
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

        $total_data = PurchaseDownPayment::count();
        
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
                
                if($request->company_id){
                    $query->where('company_id',$request->company_id);
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

                if($request->company_id){
                    $query->where('company_id',$request->company_id);
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
                    $val->company->name,
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
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan darken-4 white-tex btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
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
            'company_id'                => 'required',
            'post_date'                 => 'required',
            'due_date'                  => 'required',
            'currency_id'               => 'required',
            'currency_rate'             => 'required',
            'subtotal'                  => 'required',
		], [
			'supplier_id.required' 				=> 'Supplier tidak boleh kosong.',
			'type.required'                     => 'Tipe tidak boleh kosong',
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
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
            $percent_tax = $request->percent_tax;

            $total = $subtotal - $discount;

            if($request->tax_id > 0){
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
                        $query->company_id = $request->company_id;
                        $query->tax_id = $request->tax_id;
                        $query->is_tax = $request->tax_id > 0 ? '1' : NULL;
                        $query->is_include_tax = $request->is_include_tax ? $request->is_include_tax : '0';
                        $query->percent_tax = $request->percent_tax;
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
                        'company_id'                => $request->company_id,
                        'tax_id'                    => $request->tax_id,
                        'is_tax'                    => $request->tax_id > 0 ? '1' : NULL,
                        'is_include_tax'            => $request->is_include_tax ? $request->is_include_tax : '0',
                        'percent_tax'               => $request->percent_tax,
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
                    DB::beginTransaction();
                    try {
                        foreach($request->arr_code as $key => $row){
                            
                                PurchaseDownPaymentDetail::create([
                                    'purchase_down_payment_id'      => $query->id,
                                    'purchase_order_id'             => PurchaseOrder::where('code',CustomHelper::decrypt($row))->first()->id,
                                    'nominal'                       => str_replace(',','.',str_replace('.','',$request->arr_nominal[$key])),
                                    'note'                          => $request->arr_note[$key]
                                ]);
                                
                        }
                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollback();
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

        if(in_array($query->status,['2','3','4','5'])){
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
                
                if($request->company){
                    $query->where('company_id',$request->company);
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
            ->get()
		];
		
		return view('admin.print.purchase.down_payment', $data);
    }

    public function export(Request $request){
		return Excel::download(new ExportPurchaseDownPayment($request->search,$request->status,$request->type,$request->company,$request->is_tax,$request->is_include_tax,$request->supplier,$request->currency,$this->dataplaces), 'purchase_down_payment_'.uniqid().'.xlsx');
    }

    public function viewStructureTree(Request $request){
        $query = PurchaseDownPayment::where('code',CustomHelper::decrypt($request->id))->first();
        $data_good_receipts=[];
        $data_purchase_requests=[];

        $data_id_po = [];
        $data_id_gr = [];
        $data_pos = [];
        $data_id_invoice=[];
        

        $data_purchase_downpayment = [];
        $data_invoices=[];

        $data_go_chart=[];
        $data_link=[];


        if($query) {
           
            $data_purchase_dp = [
                "name"=>$query->code,
                "key" => $query->code,
                "color"=>"lightblue",
                'properties'=> [
                    ['name'=> "Tanggal :".$query->post_date],
                    ['name'=> "url", 'type'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($query->code)],
                 ],
                'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($query->code),           
            ];
            $data_go_chart[]=$data_purchase_dp;
            $data_purchase_downpayment[]=$data_purchase_dp;

            foreach($query->purchaseDownPaymentDetail as $row){
                if($row->purchaseOrder->exists()){
                    $po=[
                        "name"=>$row->purchaseOrder->code,
                        "key" => $row->purchaseOrder->code,
                        'properties'=> [
                            ['name'=> "Tanggal :".$row->purchaseOrder->post_date],
                        ],
                        'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($row->purchaseOrder->code),
                    ];
                   
                    $data_go_chart[]=$po;
                    $data_link[]=[
                        'from'=>$row->purchaseOrder->code,
                        'to'=>$query->code,
                    ];
                    $data_pos[]=$po;
                  
                    $data_id_po []=$row->purchaseOrder->id;
                    
                    /* mendapatkan request po */
                    foreach($row->purchaseOrder->purchaseOrderDetail as $po_detail){
                        if($po_detail->purchaseRequestDetail()->exists()){
                            $pr = [
                                "key" => $po_detail->purchaseRequestDetail->purchaseRequest->code,
                                'name'=> $po_detail->purchaseRequestDetail->purchaseRequest->code,
                                'properties'=> [
                                    ['name'=> "Tanggal: ".$po_detail->purchaseRequestDetail->purchaseRequest->post_date],
                                    ['name'=> "url", 'type'=> request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($po_detail->purchaseRequestDetail->purchaseRequest->code)],
                                 ],
                                'url'=>request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($po_detail->purchaseRequestDetail->purchaseRequest->code),
                            ];
                            if(count($data_purchase_requests)<1){
                                $data_purchase_requests[]=$pr;
                                $data_go_chart[]=$pr;
                                $data_link[]=[
                                    'from'=>$po_detail->purchaseRequestDetail->purchaseRequest->code,
                                    'to'=>$row->purchaseOrder->code,
                                ]; 
                                
                            }else{
                                $found = false;
                                foreach ($data_purchase_requests as $key => $row_pos) {
                                    if ($row_pos["key"] == $pr["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $data_purchase_requests[]=$pr;
                                    $data_go_chart[]=$pr;
                                    $data_link[]=[
                                        'from'=>$po_detail->purchaseRequestDetail->purchaseRequest->code,
                                        'to'=>$row->purchaseOrder->code,
                                    ]; 
                                }
                            }
                        }
                        /* mendapatkan gr po */
                        if($po_detail->goodReceiptDetail()->exists()){
                            foreach($po_detail->goodReceiptDetail as $good_receipt_detail){
                    
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
                                    info("masukkadfadfs");
                                    $data_good_receipts[]=$data_good_receipt;
                                    $data_go_chart[]=$data_good_receipt;
                                    $data_link[]=[
                                        'from'=>$row->purchaseOrder->code,
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
                                            'from'=>$row->purchaseOrder->code,
                                            'to'=>$data_good_receipt["key"],
                                        ];
                                        $data_id_gr[]=$good_receipt_detail->goodReceipt->id;
                                        info($data_id_gr);
                                    }
                                }
            
                            }
                        }
                    }
                     

                }
                
            }


            foreach($query->purchaseInvoiceDp as $purchase_invoicedp){
                $data_purchase_invoice = [
                    "name"=>$purchase_invoicedp->purchaseInvoice->code,
                    "key" => $purchase_invoicedp->purchaseInvoice->code,
                    'properties'=> [
                        ['name'=> "Tanggal :".$purchase_invoicedp->purchaseInvoice->post_date],
                        ],
                    'url'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($purchase_invoicedp->purchaseInvoice->code),           
                ];
                $data_go_chart[]=$data_purchase_invoice;
                $data_link[]=[
                    'from'=>$query->code,
                    'to'=>$purchase_invoicedp->purchaseInvoice->code,
                ];
                $data_id_invoice[]=$purchase_invoicedp->purchaseInvoice->id;
                $data_invoices[]=$data_purchase_invoice;
            }

            $data_lcs=[];
            
            $added = true;
            while($added){
                $added=false;
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
                                        $data_links=[
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
                                'url'=>request()->root()."/admin/inventory/purchase_down_payment?code=".CustomHelper::encrypt($row_pi->purchaseDownPayment->code),
                            ];
                            $found = false;
                            foreach($data_purchase_downpayment as $data_dp){
                                if($data_dp["key"]==$data_down_payment["key"]){
                                    $found= true;
                                    break;
                                }

                            }
                            if($found){
                                $data_links=[
                                    'from'=>$row_pi->purchaseDownPayment->code,
                                    'to'=>$query_invoice->code,
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
                            if(!$found){
                                $data_go_chart[]=$data_down_payment;
                                $data_link[]=[
                                    'from'=>$row_pi->purchaseDownPayment->code,
                                    'to'=>$query_invoice->code,
                                ];
                                $data_purchase_downpayment[]=$data_down_payment;
                            }
                        }
                    }
                }

                //Pengambilan foreign branch gr
                foreach($data_id_gr as $gr_id){
                    info($gr_id);
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
                            info("masukgrpo ke po");
                            info($po);
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
                                'url'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($invoice_detail->purchaseInvoice->code)
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

                //Pengambilan foreign branch po
                foreach($data_id_po as $po_id){
                    $query_po = PurchaseOrder::find($po_id);
                   
                    foreach($query_po->purchaseOrderDetail as $purchase_order_detail){
                       
                        if($purchase_order_detail->purchaseRequestDetail()->exists()){
                        
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
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }
        return response()->json($response);
    }
}