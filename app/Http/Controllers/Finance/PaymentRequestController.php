<?php

namespace App\Http\Controllers\Finance;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Company;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestDetail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\FundRequest;
use App\Models\FundRequestDetail;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceDetail;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseDownPaymentDetail;
use App\Models\Currency;
use App\Models\ItemCogs;
use App\Helpers\CustomHelper;
use App\Exports\ExportPaymentRequest;
use App\Models\Place;
use App\Models\User;
use App\Models\Department;
use App\Models\OutgoingPayment;
use Illuminate\Database\Eloquent\Builder;

class PaymentRequestController extends Controller
{

    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user->userPlaceArray();
    }
    public function index()
    {
        $data = [
            'title'         => 'Permintaan Pembayaran',
            'content'       => 'admin.finance.payment_request',
            'currency'      => Currency::where('status','1')->get(),
            'company'       => Company::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'company_id',
            'coa_source_id',
            'payment_type',
            'payment_no',
            'post_date',
            'pay_date',
            'currency_id',
            'currency_rate',
            'admin',
            'grandtotal',
            'document',
            'account_bank',
            'account_no',
            'account_name',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = PaymentRequest::count();
        
        $query_data = PaymentRequest::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('admin', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('account_bank', 'like', "%$search%")
                            ->orWhere('account_no', 'like', "%$search%")
                            ->orWhere('account_name', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('paymentRequestDetail',function($query) use($search, $request){
                                $query->whereHasMorph('lookable',
                                    [FundRequest::class, PurchaseDownPayment::class, PurchaseInvoice::class],
                                    function (Builder $query) use ($search) {
                                        $query->where('code','like',"%$search%");
                                    });
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }

                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
                }

                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = PaymentRequest::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('admin', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('account_bank', 'like', "%$search%")
                            ->orWhere('account_no', 'like', "%$search%")
                            ->orWhere('account_name', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('paymentRequestDetail',function($query) use($search, $request){
                                $query->whereHasMorph('lookable',
                                    [FundRequest::class, PurchaseDownPayment::class, PurchaseInvoice::class],
                                    function (Builder $query) use ($search) {
                                        $query->where('code','like',"%$search%");
                                    });
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }

                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
                }

                if($request->company_id){
                    $query->where('company_id',$request->company_id);
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
                    $val->account->name,
                    $val->company->name,
                    $val->coaSource->name,
                    $val->paymentType(),
                    $val->payment_no,
                    date('d/m/y',strtotime($val->post_date)),
                    date('d/m/y',strtotime($val->pay_date)),
                    $val->currency->code,
                    number_format($val->currency_rate,2,',','.'),
                    number_format($val->admin,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->account_bank,
                    $val->account_no,
                    $val->account_name,
                    $val->note,
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">delete</i></button>
					',
                    $val->status == '2' && !$val->outgoingPayment()->exists() ?
                    '<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="cashBankOut(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">screen_share</i></button>' : ($val->outgoingPayment()->exists() ? $val->outgoingPayment->code : $val->statusRaw() )
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

    public function getAccountData(Request $request){
        $data = User::find($request->id);

        $banks = [];
        $details = [];

        if($data){
            foreach($data->userBank()->orderByDesc('is_default')->get() as $row){
                $banks[] = [
                    'bank_id'   => $row->bank_id,
                    'name'      => $row->name,
                    'bank_name' => $row->bank->name,
                    'no'        => $row->no,
                ];
            }

            foreach($data->fundRequest as $row){
                if(!$row->used()->exists()){
                    CustomHelper::sendUsedData($row->getTable(),$row->id,'Form Payment Request');
                    $coa = Coa::where('code','100.01.03.04.02')->where('company_id',$row->place->company_id)->first();
                    $details[] = [
                        'id'            => $row->id,
                        'type'          => 'fund_requests',
                        'code'          => CustomHelper::encrypt($row->code),
                        'rawcode'       => $row->code,
                        'post_date'     => date('d/m/y',strtotime($row->post_date)),
                        'due_date'      => date('d/m/y',strtotime($row->required_date)),
                        'total'         => number_format($row->total,2,',','.'),
                        'tax'           => number_format($row->tax,2,',','.'),
                        'wtax'          => number_format($row->wtax,2,',','.'),
                        'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                        'balance'       => number_format($row->balancePaymentRequest(),2,',','.'),
                        'coa_id'        => $row->type == '1' ? ($coa ? $coa->id : '') : '',
                        'coa_name'      => $row->type == '1' ? ($coa ? $coa->code.' - '.$coa->name : '') : '',
                    ];
                }
            }

            foreach($data->purchaseDownPayment as $row){
                if(!$row->used()->exists()){
                    CustomHelper::sendUsedData($row->getTable(),$row->id,'Form Payment Request');
                    $coa = Coa::where('code','200.01.03.01.01')->where('company_id',$row->company_id)->first();
                    $details[] = [
                        'id'            => $row->id,
                        'type'          => 'purchase_down_payments',
                        'code'          => CustomHelper::encrypt($row->code),
                        'rawcode'       => $row->code,
                        'post_date'     => date('d/m/y',strtotime($row->post_date)),
                        'due_date'      => date('d/m/y',strtotime($row->due_date)),
                        'total'         => number_format($row->total,2,',','.'),
                        'tax'           => number_format($row->tax,2,',','.'),
                        'wtax'          => number_format($row->wtax,2,',','.'),
                        'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                        'balance'       => number_format($row->balancePaymentRequest(),2,',','.'),
                        'coa_id'        => $coa ? $coa->id : '',
                        'coa_name'      => $coa ? $coa->code.' - '.$coa->name : '',
                    ];
                }
            }

            foreach($data->purchaseInvoice as $row){
                if(!$row->used()->exists()){
                    CustomHelper::sendUsedData($row->getTable(),$row->id,'Form Payment Request');
                    $coa = Coa::where('code','200.01.03.01.01')->where('company_id',$row->company_id)->first();
                    $details[] = [
                        'id'            => $row->id,
                        'type'          => 'purchase_invoices',
                        'code'          => CustomHelper::encrypt($row->code),
                        'rawcode'       => $row->code,
                        'post_date'     => date('d/m/y',strtotime($row->post_date)),
                        'due_date'      => date('d/m/y',strtotime($row->due_date)),
                        'total'         => number_format($row->total,2,',','.'),
                        'tax'           => number_format($row->tax,2,',','.'),
                        'wtax'          => number_format($row->wtax,2,',','.'),
                        'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                        'balance'       => number_format($row->balance,2,',','.'),
                        'coa_id'        => $coa ? $coa->id : '',
                        'coa_name'      => $coa ? $coa->code.' - '.$coa->name : '',
                    ];
                }
            }
        }

        $data['banks'] = $banks;
        $data['details'] = $details;

        return response()->json($data);
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
			'account_id' 			=> 'required',
            'company_id'            => 'required',
            'coa_source_id'         => 'required',
            'payment_type'          => 'required',
            'post_date'             => 'required',
            'pay_date'              => 'required',
            'currency_id'           => 'required',
            'currency_rate'         => 'required',
            'admin'                 => 'required',
            'grandtotal'            => 'required',
            'arr_type'              => 'required|array',
            'arr_code'              => 'required|array',
            'arr_pay'               => 'required|array',
            'arr_coa'               => 'required|array',
		], [
			'account_id.required' 			    => 'Supplier/Vendor tidak boleh kosong.',
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
            'coa_source_id.required'            => 'Kas/Bank tidak boleh kosong.',
            'payment_type.required'             => 'Tipe pembayaran tidak boleh kosong.',
            'post_date.required'                => 'Tanggal posting tidak boleh kosong.',
            'pay_date.required'                 => 'Tanggal bayar tidak boleh kosong.',
            'currency_id.required'              => 'Mata uang tidak boleh kosong.',
            'currency_rate.required'            => 'Konversi mata uang tidak boleh kosong.',
            'admin.required'                    => 'Biaya admin tidak boleh kosong, minimal 0.',
            'grandtotal.required'               => 'Total bayar tidak boleh kosong.',
            'arr_type.required'                 => 'Tipe dokumen tidak boleh kosong.',
            'arr_type.array'                    => 'Tipe dokumen harus dalam bentuk array.',
            'arr_code.required'                 => 'Kode tidak boleh kosong.',
            'arr_code.array'                    => 'Kode harus dalam bentuk array.',
            'arr_pay.required'                  => 'Baris bayar tidak boleh kosong.',
            'arr_pay.array'                     => 'Baris bayar harus dalam bentuk array.',
            'arr_coa.required'                  => 'Baris coa tidak boleh kosong.',
            'arr_coa.array'                     => 'Baris coa harus dalam bentuk array.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = PaymentRequest::where('code',CustomHelper::decrypt($request->temp))->first();

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

                        if($request->has('document')) {
                            if(Storage::exists($query->document)){
                                Storage::delete($query->document);
                            }
                            $document = $request->file('document')->store('public/payment_requests');
                        } else {
                            $document = $query->document;
                        }

                        $query->user_id = session('bo_id');
                        $query->account_id = $request->account_id;
                        $query->company_id = $request->company_id;
                        $query->coa_source_id = $request->coa_source_id;
                        $query->payment_type = $request->payment_type;
                        $query->payment_no = $request->payment_no;
                        $query->post_date = $request->post_date;
                        $query->pay_date = $request->pay_date;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->admin = str_replace(',','.',str_replace('.','',$request->admin));
                        $query->grandtotal = str_replace(',','.',str_replace('.','',$request->grandtotal));
                        $query->document = $document;
                        $query->account_bank = $request->account_bank;
                        $query->account_no = $request->account_no;
                        $query->account_name = $request->account_name;
                        $query->note = $request->note;

                        $query->save();

                        foreach($query->paymentRequestDetail as $row){
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
                    $query = PaymentRequest::create([
                        'code'			            => PaymentRequest::generateCode(),
                        'user_id'		            => session('bo_id'),
                        'account_id'                => $request->account_id,
                        'company_id'                => $request->company_id,
                        'coa_source_id'             => $request->coa_source_id,
                        'payment_type'              => $request->payment_type,
                        'payment_no'                => $request->payment_no,
                        'post_date'                 => $request->post_date,
                        'pay_date'                  => $request->pay_date,
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'admin'                     => str_replace(',','.',str_replace('.','',$request->admin)),
                        'grandtotal'                => str_replace(',','.',str_replace('.','',$request->grandtotal)),
                        'document'                  => $request->file('document') ? $request->file('document')->store('public/payment_requests') : NULL,
                        'account_bank'              => $request->account_bank,
                        'account_no'                => $request->account_no,
                        'account_name'              => $request->account_name,
                        'note'                      => $request->note,
                        'status'                    => '1',
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                
                if($request->arr_type){
                    DB::beginTransaction();
                    try {
                        foreach($request->arr_type as $key => $row){
                            $code = CustomHelper::decrypt($request->arr_code[$key]);

                            if($row == 'fund_requests'){
                                $idDetail = FundRequest::where('code',$code)->first()->id;
                            }elseif($row == 'purchase_down_payments'){
                                $idDetail = PurchaseDownPayment::where('code',$code)->first()->id;
                            }elseif($row == 'purchase_invoices'){
                                $idDetail = PurchaseInvoice::where('code',$code)->first()->id;
                            }
                            
                            PaymentRequestDetail::create([
                                'payment_request_id'            => $query->id,
                                'lookable_type'                 => $row,
                                'lookable_id'                   => $idDetail,
                                'coa_id'                        => $request->arr_coa[$key],
                                'nominal'                       => str_replace(',','.',str_replace('.','',$request->arr_pay[$key])),
                                'note'                          => $request->arr_note[$key]
                            ]);
                        }
                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollback();
                    }
                }

                CustomHelper::sendApproval('payment_requests',$query->id,$query->note);
                CustomHelper::sendNotification('payment_requests',$query->id,'Permintaan Pembayaran No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new PaymentRequest())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit payment request.');

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

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData($request->table,$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function rowDetail(Request $request){
        $data   = PaymentRequest::find($request->id);
        
        $string = '<div class="row pt-1 pb-1 lime lighten-4"><div class="col s12"><table style="max-width:500px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="10">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Referensi</th>
                                <th class="center-align">Tipe</th>
                                <th class="center-align">Bayar</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Coa</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->paymentRequestDetail as $key => $row){
            
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->lookable->code.'</td>
                <td class="center-align">'.$row->type().'</td>
                <td class="right-align">'.number_format($row->nominal,3,',','.').'</td>
                <td class="center-align">'.$row->note.'</td>
                <td class="center-align">'.$row->coa->code.' - '.$row->coa->name.'</td>
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

    public function show(Request $request){
        $pr = PaymentRequest::where('code',CustomHelper::decrypt($request->id))->first();
        $pr['account_name'] = $pr->account->name;
        $pr['coa_source_name'] = $pr->coaSource->code.' - '.$pr->coaSource->name.' - '.$pr->coaSource->company->name;
        $pr['currency_rate'] = number_format($pr->currency_rate,3,',','.');
        $pr['admin'] = number_format($pr->admin,3,',','.');
        $pr['grandtotal'] = number_format($pr->grandtotal,3,',','.');
        $pr['top'] = $pr->account->top;

        $arr = [];
        $banks = [];

        foreach($pr->account->userBank()->orderByDesc('is_default')->get() as $row){
            $banks[] = [
                'bank_id'   => $row->bank_id,
                'name'      => $row->name,
                'bank_name' => $row->bank->name,
                'no'        => $row->no,
            ];
        }

        foreach($pr->paymentRequestDetail as $row){
            $code = CustomHelper::encrypt($row->lookable->code);
            $arr[] = [
                'id'            => $row->lookable_id,
                'type'          => $row->lookable_type,
                'code'          => $code,
                'rawcode'       => $row->lookable->code,
                'post_date'     => $row->lookable->post_date,
                'due_date'      => isset($row->lookable->due_date) ? $row->lookable->due_date : $row->lookable->post_date,
                'total'         => number_format($row->lookable->total,3,',','.'),
                'tax'           => number_format($row->lookable->tax,3,',','.'),
                'wtax'          => number_format($row->lookable->wtax,3,',','.'),
                'grandtotal'    => number_format($row->lookable->grandtotal,3,',','.'),
                'nominal'       => number_format($row->nominal,3,',','.'),
                'note'          => $row->note,
                'coa_id'        => $row->coa_id,
                'coa_name'      => $row->coa->code.' - '.$row->coa->name
            ];
        }

        $pr['details'] = $arr;
        $pr['banks'] = $banks;
        				
		return response()->json($pr);
    }

    public function voidStatus(Request $request){
        $query = PaymentRequest::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {
            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }elseif($query->hasChildDocument()){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah digunakan pada Outgoing Payment / Kas Bank Keluar.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new PaymentRequest())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the payment requests data');
    
                CustomHelper::sendNotification('payment_requests',$query->id,'Permintaan Pembayaran No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('payment_requests',$query->id);

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
        $query = PaymentRequest::where('code',CustomHelper::decrypt($request->id))->first();

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
                'message' => 'Jurnal / dokumen sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            $query->paymentRequestDetail()->delete();

            CustomHelper::removeApproval('payment_requests',$query->id);

            activity()
                ->performedOn(new PaymentRequest())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the payment request data');

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
            'title' => 'PAYMENT REQUEST REPORT',
            'data' => PaymentRequest::where(function($query) use ($request) {
                if($request->search) {
                    $query->where(function($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('grandtotal', 'like', "%$request->search%")
                            ->orWhere('admin', 'like', "%$request->search%")
                            ->orWhere('note', 'like', "%$request->search%")
                            ->orWhere('account_bank', 'like', "%$request->search%")
                            ->orWhere('account_no', 'like', "%$request->search%")
                            ->orWhere('account_name', 'like', "%$request->search%")
                            ->orWhereHas('user',function($query) use($request){
                                $query->where('name','like',"%$request->search%")
                                    ->orWhere('employee_no','like',"%$request->search%");
                            })
                            ->orWhereHas('account',function($query) use($request){
                                $query->where('name','like',"%$request->search%")
                                    ->orWhere('employee_no','like',"%$request->search%");
                            })
                            ->orWhereHas('paymentRequestDetail',function($query) use($request){
                                $query->whereHasMorph('lookable',
                                    [FundRequest::class, PurchaseDownPayment::class, PurchaseInvoice::class],
                                    function (Builder $query) use ($request) {
                                        $query->where('code','like',"%$request->search%");
                                    });
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->account){
                    $query->whereIn('account_id',$request->account);
                }

                if($request->currency){
                    $query->whereIn('currency_id',$request->currency);
                }

                if($request->company){
                    $query->where('company_id',$request->company);
                }
            })
            ->get()
		];
		
		return view('admin.print.finance.payment_request', $data);
    }

    public function export(Request $request){
		return Excel::download(new ExportPaymentRequest($request->search,$request->status,$request->company,$request->account,$request->currency,$this->dataplaces), 'payment_request'.uniqid().'.xlsx');
    }
    
    public function approval(Request $request,$id){
        
        $pr = PaymentRequest::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Permintaan Pembayaran',
                'data'      => $pr
            ];

            return view('admin.approval.payment_request', $data);
        }else{
            abort(404);
        }
    }

    public function getPaymentData(Request $request){
        $data = PaymentRequest::where('code',CustomHelper::decrypt($request->code))->first();

        if($data){
            if(!$data->used()->exists() && !$data->outgoingPayment()->exists()){
                CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Permintaan Pembayaran (Payment Request)');

                $html = '<div class="row pt-1 pb-1"><div class="col s12"><table>
                        <thead>
                            <tr>
                                <th class="center-align" colspan="10">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Referensi</th>
                                <th class="center-align">Tipe</th>
                                <th class="center-align">Bayar</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Coa</th>
                            </tr>
                        </thead><tbody>';
        
                foreach($data->paymentRequestDetail as $key => $row){
                    
                    $html .= '<tr>
                        <td class="center-align">'.($key + 1).'</td>
                        <td class="center-align">'.$row->lookable->code.'</td>
                        <td class="center-align">'.$row->type().'</td>
                        <td class="right-align">'.number_format($row->nominal,3,',','.').'</td>
                        <td class="center-align">'.$row->note.'</td>
                        <td class="center-align">'.$row->coa->code.' - '.$row->coa->name.'</td>
                    </tr>';
                }
                
                $html .= '</tbody></table></div>';

                $html .= '<div class="col s12 mt-1"><table style="max-width:500px;">
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
                        $html .= '<tr>
                            <td class="center-align">'.$row->approvalTable->level.'</td>
                            <td class="center-align">'.$row->user->profilePicture().'<br>'.$row->user->name.'</td>
                            <td class="center-align">'.($row->status == '1' ? '<i class="material-icons">hourglass_empty</i>' : ($row->approved ? '<i class="material-icons">thumb_up</i>' : ($row->rejected ? '<i class="material-icons">thumb_down</i>' : '<i class="material-icons">hourglass_empty</i>'))).'<br></td>
                            <td class="center-align">'.$row->note.'</td>
                        </tr>';
                    }
                }else{
                    $html .= '<tr>
                        <td class="center-align" colspan="4">Approval tidak ditemukan.</td>
                    </tr>';
                }

                $html .= '</tbody></table></div></div>';

                return response()->json([
                    'status'    => 200,
                    'data'      => $data,
                    'html'      => $html,
                ]);
            }elseif($data->outgoingPayment()->exists()){
                return response()->json([
                    'status'    => 500,
                    'message'   => 'Permintaan Pembayaran '.$data->used->lookable->code.' telah memiliki kas bank out.'
                ]);
            }else{
                return response()->json([
                    'status'    => 500,
                    'message'   => 'Permintaan Pembayaran '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.'
                ]);
            }
        }else{
            return response()->json([
                'status'    => 500,
                'message'   => 'Data tidak ditemukan.',
            ]);
        }
    }

    public function createPay(Request $request){
        $validation = Validator::make($request->all(), [
            'pay_date_pay'              => 'required',
		], [
            'pay_date_pay.required'     => 'Tanggal bayar tidak boleh kosong.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            
			if($request->tempPay){
                DB::beginTransaction();
                try {
                    $cek = PaymentRequest::where('code',CustomHelper::decrypt($request->tempPay))->first();

                    $query = OutgoingPayment::create([
                        'code'			            => OutgoingPayment::generateCode(),
                        'user_id'		            => session('bo_id'),
                        'company_id'                => $cek->company_id,
                        'account_id'                => $cek->account_id,
                        'payment_request_id'        => $cek->id,
                        'coa_source_id'             => $cek->coa_source_id,
                        'post_date'                 => date('Y-m-d'),
                        'pay_date'                  => $request->pay_date_pay,
                        'currency_id'               => $cek->currency_id,
                        'currency_rate'             => $cek->currency_rate,
                        'admin'                     => $cek->admin,
                        'grandtotal'                => $cek->grandtotal,
                        'document'                  => $request->file('documentPay') ? $request->file('documentPay')->store('public/outgoing_payments') : NULL,
                        'note'                      => $request->notePay,
                        'status'                    => '3',
                    ]);

                    $cek->update([
                        'pay_date'                  => $request->pay_date_pay,
                    ]);

                    DB::commit();
                    
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                CustomHelper::sendNotification('outgoing_payments',$query->id,'Kas Bank Out / Kas Keluar No. '.$query->code,$query->note,session('bo_id'));
                CustomHelper::sendJournal('outgoing_payments',$query->id,$query->account_id);

                activity()
                    ->performedOn(new OutgoingPayment())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit payment request.');

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
}