<?php

namespace App\Http\Controllers\Finance;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Company;
use App\Models\GoodReceipt;
use App\Models\GoodReturnPO;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestDetail;
use App\Models\PurchaseOrder;
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
    public function index(Request $request)
    {
        $data = [
            'title'         => 'Permintaan Pembayaran',
            'content'       => 'admin.finance.payment_request',
            'currency'      => Currency::where('status','1')->get(),
            'company'       => Company::where('status','1')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
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
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
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
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
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
                        <button type="button" class="btn-floating mb-1 btn-flat cyan darken-4 white-text btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
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
                if(!$row->used()->exists() && $row->balancePaymentRequest() > 0){
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
                if(!$row->used()->exists() && $row->balancePaymentRequest() > 0){
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
                if(!$row->used()->exists() && $row->balance > 0){
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
                    <td class="center-align">'.$row->approvalTemplateStage->approvalStage->level.'</td>
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
                            <td class="center-align">'.$row->approvalTemplateStage->approvalStage->level.'</td>
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

    public function viewStructureTree(Request $request){
        $query = PaymentRequest::where('code',CustomHelper::decrypt($request->id))->first();
        $data_go_chart = [];
        $data_link = [];
        $fr = [
                'key'   => $query->code,
                "name"  => $query->code,
                "color" => "lightblue",
                'properties'=> [
                     ['name'=> "Tanggal: ".date('d/m/y',strtotime($query->post_date))],
                  ],
                'url'   =>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($query->code),
                "title" =>$query->code,
            ];
        $data_go_chart[]=$fr;
        $data_good_receipts = [];
        $data_purchase_requests = [];
        
        $data_id_dp=[];
        $data_id_po = [];
        $data_id_gr = [];
        $data_id_invoice=[];
        $data_purchase_downpayment=[];
        $data_id_pyrs=[];
        $data_id_frs=[];
        $data_id_greturns=[];
        $data_frs=[];
        $data_pyrs = [];
        $data_good_returns = [];
        $data_outgoingpayments = [];
        $data_lcs=[];
        $data_invoices=[];

        $data_pyrs[]=$fr;

        $data_pos=[];
        if($query) {

            foreach($query->paymentRequestDetail as $row_pyr_detail){
                        
                $data_pyr_tempura=[
                    'properties'=> [
                        ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                        ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                    ],
                    "key" => $row_pyr_detail->paymentRequest->code,
                    "name" => $row_pyr_detail->paymentRequest->code,
                    'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                ];
                if($row_pyr_detail->fundRequest()){
                    
                    $data_fund_tempura=[
                        'properties'=> [
                            ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                            ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                        ],
                        "key" => $row_pyr_detail->lookable->code,
                        "name" => $row_pyr_detail->lookable->code,
                        'url'=>request()->root()."/admin/finace/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                    ];
                    if(count($data_frs)<1){
                        $data_frs[]=$data_fund_tempura;
                        $data_go_chart[]=$data_fund_tempura;
                        $data_link[]=[
                            'from'=>$row_pyr_detail->lookable->code,
                            'to'=>$row_pyr_detail->paymentRequest->code,
                        ]; 
                        $data_id_frs[]= $row_pyr_detail->lookable->id;  
                        
                    }else{
                        $found = false;
                        foreach ($data_frs as $key => $row_fundreq) {
                            if ($row_fundreq["key"] == $data_fund_tempura["key"]) {
                                $found = true;
                                break;
                            }
                        }
                        
                        if($found){
                            $data_links=[
                                'from'=>$row_pyr_detail->lookable->code,
                                'to'=>$row_pyr_detail->paymentRequest->code,
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
                            $data_frs[]=$data_fund_tempura;
                            $data_go_chart[]=$data_fund_tempura;
                            $data_link[]=[
                                'from'=>$row_pyr_detail->lookable->code,
                                'to'=>$row_pyr_detail->paymentRequest->code,
                            ]; 
                            $data_id_frs[]= $row_pyr_detail->lookable->id;   
                        }
                    }
                    
                }
                if($row_pyr_detail->purchaseDownPayment()){
                    $data_downp_tempura = [
                        'properties'=> [
                            ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                            ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                        ],
                        "key" => $row_pyr_detail->lookable->code,
                        "name" => $row_pyr_detail->lookable->code,
                        'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                    ];
                    if(count($data_purchase_downpayment)<1){
                        
                        $data_purchase_downpayment[]=$data_downp_tempura;
                        $data_go_chart[]=$data_downp_tempura;
                        $data_link[]=[
                            'from'=>$row_pyr_detail->lookable->code,
                            'to'=>$row_pyr_detail->paymentRequest->code,
                        ];
                        
                        
                        
                    }else{
                        $found = false;
                        foreach ($data_purchase_downpayment as $key => $row_dp) {
                            if ($row_dp["key"] == $data_downp_tempura["key"]) {
                                $found = true;
                                break;
                            }
                        }
                        
                        if($found){
                            
                            $data_links=[
                                'from'=>$row_pyr_detail->lookable->code,
                                'to'=>$row_pyr_detail->paymentRequest->code,
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
                            $data_purchase_downpayment[]=$data_downp_tempura;
                            $data_go_chart[]=$data_downp_tempura;
                            $data_link[]=[
                                'from'=>$row_pyr_detail->lookable->code,
                                'to'=>$row_pyr_detail->paymentRequest->code,
                            ]; 
                            
                        }
                        if(count($data_id_dp)<1){
                            $data_id_dp[] = $row_pyr_detail->lookable->id;
                            $added = true;
                            
                        }
                        
                        
                    }
                    if(!in_array($row_pyr_detail->lookable->id, $data_id_dp)){
                        $data_id_dp[] = $row_pyr_detail->lookable->id;
                        $added = true; 
                    }
                }
                if($row_pyr_detail->purchaseInvoice()){
                    $data_invoices_tempura = [
                        'properties'=> [
                            ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                            ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                        ],
                        "key" => $row_pyr_detail->lookable->code,
                        "name" => $row_pyr_detail->lookable->code,
                        'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                    ];
                    if(count($data_invoices)<1){
                           
                        $data_go_chart[]=$data_invoices_tempura;
                        $data_link[]=[
                            'from'=>$row_pyr_detail->lookable->code,
                            'to'=>$row_pyr_detail->paymentRequest->code,
                        ];
                        
                        $data_invoices[]=$data_invoices_tempura;
                    }else{
                        $found = false;
                        foreach ($data_invoices as $key => $row_invoice) {
                            if ($row_invoice["key"] == $data_invoices_tempura["key"]) {
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                           
                            $data_go_chart[]=$data_invoices_tempura;
                            $data_link[]=[
                               'from'=>$row_pyr_detail->lookable->code,
                               'to'=>$row_pyr_detail->paymentRequest->code,
                            ];
                        
                            $data_invoices[]=$data_invoices_tempura;
                        }
                    }
                    if(!in_array($row_pyr_detail->lookable->id, $data_id_invoice)){
                        $data_id_invoice[] = $row_pyr_detail->lookable->id;
                    }
                }
            }
            

            
            $added = true;
           
            while($added){
               
                $added=false;
                // Pengambilan foreign branch gr
                foreach($data_id_gr as $gr_id){
                    $query_gr = GoodReceipt::where('id',$gr_id)->first();
                    foreach($query_gr->goodReceiptDetail as $good_receipt_detail){
                        $po = [
                            'properties'=> [
                                ['name'=> "Tanggal: ".$good_receipt_detail->purchaseOrderDetail->purchaseOrder->post_date],
                                ['name'=> "Vendor  : ".$good_receipt_detail->purchaseOrderDetail->purchaseOrder->supplier->name],
                                ['name'=> "Nominal : Rp.:".number_format($good_receipt_detail->purchaseOrderDetail->purchaseOrder->grandtotal,2,',','.')]
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

                        if($good_receipt_detail->goodReturnPODetail()->exists()){
                            $good_return_tempura =[
                                "name"=> $good_receipt_detail->goodReturnPODetail->goodReturnPO->code,
                                "key" =>  $good_receipt_detail->goodReturnPODetail->goodReturnPO->code,
                                
                                'properties'=> [
                                    ['name'=> "Tanggal :". $good_receipt_detail->goodReturnPODetail->goodReturnPO->post_date],
                                ],
                                'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt( $good_receipt_detail->goodReturnPODetail->goodReturnPO->code),
                            ];
                            if(count($data_good_returns)<1){
                                $data_good_returns[]=$good_return_tempura;
                                $data_go_chart[] = $good_return_tempura;;
                                $data_link[]=[
                                    'from'=> $good_receipt_detail->goodReturnPODetail->goodReturnPO->code,
                                    'to'=>$query_gr->code,
                                ];
                                $data_id_greturns[]=  $good_receipt_detail->goodReturnPODetail->goodReturnPO->id; 
                                
                            }else{
                                $found = false;
                                foreach ($data_good_returns as $key => $row_return) {
                                    if ($row_return["key"] == $good_return_tempura["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $data_good_returns[]=$good_return_tempura;
                                    $data_go_chart[] = $good_return_tempura;;
                                    $data_link[]=[
                                        'from'=> $good_receipt_detail->goodReturnPODetail->goodReturnPO->code,
                                        'to'=>$query_gr->code,
                                    ];
                                    $data_id_greturns[]=  $good_receipt_detail->goodReturnPODetail->goodReturnPO->id; 
                                }
                            }
                        }

                    }

                    //landed cost searching
                    if($query_gr->landedCost()->exists()){
                        foreach($query_gr->landedCost as $landed_cost){
                            $data_lc=[
                                'properties'=> [
                                    ['name'=> "Tanggal : ".$landed_cost->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($landed_cost->grandtotal,2,',','.')]
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
                                    ['name'=> "Nominal : Rp.".number_format($invoice_detail->purchaseInvoice->grandtotal,2,',','.')]
                                    
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
                                    
                                }
                                
                            }
                            if(!in_array($invoice_detail->purchaseInvoice->id, $data_id_invoice)){
                                $data_id_invoice[] = $invoice_detail->purchaseInvoice->id;
                                $added = true; 
                            }
                        }
                    }

                }

                //mencari goodreturn foreign
                foreach($data_id_greturns as $good_return_id){
                    $query_return = GoodReturnPO::where('id',$good_return_id)->first();
                    foreach($query_return->goodReturnPODetail as $good_return_detail){
                        $data_good_receipt = [
                            "name"=>$good_return_detail->goodReceiptDetail->goodReceipt->code,
                            "key" => $good_return_detail->goodReceiptDetail->goodReceipt->code,
                            "color"=>"lightblue",
                            'properties'=> [
                                ['name'=> "Tanggal :".$good_return_detail->goodReceiptDetail->goodReceipt->post_date],
                            ],
                            'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_return_detail->goodReceiptDetail->goodReceipt->code),
                        ];
                        if(count($data_good_receipts)<1){
                            $data_good_receipt[]=$data_good_receipt;
                            $data_go_chart[]=$data_good_receipt;
                            $data_link[]=[
                                'from'=>$good_return_detail->goodReceiptDetail->goodReceipt->code,
                                'to'=>$data_good_receipt["key"],
                            ];
                        }else{
                            $found = false;
                            foreach ($data_good_receipts as $key => $row_gr) {
                                if ($row_gr["key"] == $data_good_receipt["key"]) {
                                    $found = true;
                                    break;
                                }
                            }
                            if($found){
                                $data_links=[
                                    'from'=>$good_return_detail->goodReceiptDetail->goodReceipt->code,
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
                                $data_good_receipt[]=$data_good_receipt;
                                $data_go_chart[]=$data_good_receipt;
                                $data_link[]=[
                                    'from'=>$good_return_detail->goodReceiptDetail->goodReceipt->code,
                                    'to'=>$data_good_receipt["key"],
                                ];
                                  
                            }
                        }
                        if(!in_array($good_return_detail->goodReceiptDetail->goodReceipt->id, $data_id_gr)){
                            $data_id_gr[] = $good_return_detail->goodReceiptDetail->goodReceipt->id;
                            $added = true;
                        }
                    }
                }

                // invoice insert foreign

                foreach($data_id_invoice as $invoice_id){
                    $query_invoice = PurchaseInvoice::where('id',$invoice_id)->first();
                    foreach($query_invoice->purchaseInvoiceDetail as $row){
                        if($row->purchaseOrder()){
                            $row_po=$row->lookable;
                                $po =[
                                    "name"=>$row_po->code,
                                    "key" => $row_po->code,
                                    "color"=>"lightblue",
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_po->post_date],
                                        ['name'=> "Vendor  : ".$row_po->supplier->name],
                                        ['name'=> "Nominal : Rp.:".number_format($row_po->grandtotal,2,',','.')]
                                     ],
                                    'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($row_po->post_date),           
                                ];
                                /*memasukkan ke node data dan linknya*/
                                if(count($data_pos)<1){
                                    $data_pos[]=$po;
                                    $data_go_chart[]=$po;
                                    $data_link[]=[
                                        'from'=>$row_po->code,
                                        'to'=>$query_invoice->code,
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
                                            'from'=>$row_po->code,
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
                                    if (!$found) {
                                        $data_pos[] = $po;
                                        $data_link[]=[
                                            'from'=>$row_po->code,
                                            'to'=>$query_invoice->code,
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
                                                    ['name'=> "Nominal : Rp.".number_format($good_receipt_detail->goodReceipt->grandtotal,2,',','.')],
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
                        /*  melihat apakah ada hubungan grpo tanpa po */
                        if($row->goodReceipt()){
        
                            $data_good_receipt=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->lookable->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row->lookable->code,
                                "name" => $row->lookable->code,
                                'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($row->lookable->code),
                            ];
        
                            if(count($data_good_receipts)<1){
                                $data_good_receipts[]=$data_good_receipt;
                                $data_go_chart[]=$data_good_receipt;
                                $data_link[]=[
                                    'from'=>$data_good_receipt["key"],
                                    'to'=>$query_invoice->code,
                                ];
                               
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
                                        'from'=>$data_good_receipt["key"],
                                        'to'=>$query_invoice->code,
                                    ]; 
                                   
                                }
                            }
                            if(!in_array($row->goodReceipt->id, $data_id_gr)){
                                $data_id_gr[] = $row->goodReceipt->id; 
                                $added = true;
                            } 
                        }
                        /* melihat apakah ada hubungan lc */
                        if($row->landedCost()){
                            $data_lc=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->lookable->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row->lookable->code,
                                "name" => $row->lookable->code,
                                'url'=>request()->root()."/admin/inventory/landed_cost?code=".CustomHelper::encrypt($row->lookable->code),
                            ];
                            if(count($data_lcs)<1){
                                $data_lcs[]=$data_lc;
                                $data_go_chart[]=$data_lc;
                                $data_link[]=[
                                    'from'=>$query_invoice->code,
                                    'to'=>$row->lookable->code,
                                ];
                                $data_id_lc = $row->lookable->id;
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
                                        'to'=>$row->lookable->code,
                                    ];
                                    $data_id_lc = $row->lookable->id;
                                }
                            }
                        }
                        
                    }
                    if($query_invoice->purchaseInvoiceDp()->exists()){
                        foreach($query_invoice->purchaseInvoiceDp as $row_pi){
                            $data_down_payment=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pi->purchaseDownPayment->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row_pi->purchaseDownPayment->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pi->purchaseDownPayment->code,
                                "name" => $row_pi->purchaseDownPayment->code,
                                'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pi->purchaseDownPayment->code),
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
                            if($row_pi->purchaseDownPayment->hasPaymentRequestDetail()->exists()){
                                foreach($row_pi->purchaseDownPayment->hasPaymentRequestDetail as $row_pyr_detail){
                                    $data_pyr_tempura=[
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                                            ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                                        ],
                                        "key" => $row_pyr_detail->paymentRequest->code,
                                        "name" => $row_pyr_detail->paymentRequest->code,
                                        'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                                    ];

                                    if(count($data_pyrs)<1){
                                        $data_pyrs[]=$data_pyr_tempura;
                                        $data_go_chart[]=$data_pyr_tempura;
                                        $data_link[]=[
                                            'from'=>$row_pi->purchaseDownPayment->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
                                        ]; 
                                        $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;  
                                        
                                    }else{
                                        $found = false;
                                        foreach ($data_pyrs as $key => $row_pyr) {
                                            if ($row_pyr["key"] == $data_pyr_tempura["key"]) {
                                                $found = true;
                                                break;
                                            }
                                        }
                                     
                                        if($found){
                                            $data_links=[
                                                'from'=>$row_pi->purchaseDownPayment->code,
                                                'to'=>$row_pyr_detail->paymentRequest->code,
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
                                            $data_pyrs[]=$data_pyr_tempura;
                                            $data_go_chart[]=$data_pyr_tempura;
                                            $data_link[]=[
                                                'from'=>$row_pi->purchaseDownPayment->code,
                                                'to'=>$row_pyr_detail->paymentRequest->code,
                                            ]; 
                                            $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;   
                                        }
                                    }

                                    if($row_pyr_detail->fundRequest()){
                                        $data_fund_tempura=[
                                            'properties'=> [
                                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                                ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                            ],
                                            "key" => $row_pyr_detail->lookable->code,
                                            "name" => $row_pyr_detail->lookable->code,
                                            'url'=>request()->root()."/admin/finace/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                                        ];
                                        if(count($data_frs)<1){
                                            $data_frs[]=$data_fund_tempura;
                                            $data_go_chart[]=$data_fund_tempura;
                                            $data_link[]=[
                                                'from'=>$row_pyr_detail->lookable->code,
                                                'to'=>$row_pyr_detail->paymentRequest->code,
                                            ]; 
                                            $data_id_frs[]= $row_pyr_detail->lookable->id;  
                                            
                                        }else{
                                            $found = false;
                                            foreach ($data_frs as $key => $row_fundreq) {
                                                if ($row_fundreq["key"] == $data_fund_tempura["key"]) {
                                                    $found = true;
                                                    break;
                                                }
                                            }
                                            
                                            if($found){
                                                $data_links=[
                                                    'from'=>$row_pyr_detail->lookable->code,
                                                    'to'=>$row_pyr_detail->paymentRequest->code,
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
                                                $data_frs[]=$data_fund_tempura;
                                                $data_go_chart[]=$data_fund_tempura;
                                                $data_link[]=[
                                                    'from'=>$row_pyr_detail->lookable->code,
                                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                                ]; 
                                                $data_id_frs[]= $row_pyr_detail->lookable->id;   
                                            }
                                        }
                                        
                                    }
                                    if($row_pyr_detail->purchaseDownPayment()){
                                        $data_downp_tempura = [
                                            'properties'=> [
                                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                                ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                            ],
                                            "key" => $row_pyr_detail->lookable->code,
                                            "name" => $row_pyr_detail->lookable->code,
                                            'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                                        ];
                                        if(count($data_purchase_downpayment)<1){
                                            $data_purchase_downpayment[]=$data_downp_tempura;
                                            $data_go_chart[]=$data_downp_tempura;
                                            $data_link[]=[
                                                'from'=>$row_pyr_detail->lookable->code,
                                                'to'=>$row_pyr_detail->paymentRequest->code,
                                            ]; 
                                            $data_id_dp[]= $row_pyr_detail->lookable->id;  
                                            
                                        }else{
                                            $found = false;
                                            foreach ($data_purchase_downpayment as $key => $row_dp) {
                                                if ($row_dp["key"] == $data_downp_tempura["key"]) {
                                                    $found = true;
                                                    break;
                                                }
                                            }
                                            
                                            if($found){
                                                $data_links=[
                                                    'from'=>$row_pyr_detail->lookable->code,
                                                    'to'=>$row_pyr_detail->paymentRequest->code,
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
                                                $data_purchase_downpayment[]=$data_downp_tempura;
                                                $data_go_chart[]=$data_downp_tempura;
                                                $data_link[]=[
                                                    'from'=>$row_pyr_detail->lookable->code,
                                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                                ]; 
                                                $data_id_dp[]= $row_pyr_detail->lookable->id;    
                                            }
                                        }
                                    }
                                    if($row_pyr_detail->purchaseInvoice()){
                                        $data_invoices_tempura = [
                                            'properties'=> [
                                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                                ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                            ],
                                            "key" => $row_pyr_detail->lookable->code,
                                            "name" => $row_pyr_detail->lookable->code,
                                            'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                                        ];
                                        if(count($data_invoices)<1){
                                               
                                            $data_go_chart[]=$data_invoices_tempura;
                                            $data_link[]=[
                                                'from'=>$row_pyr_detail->lookable->code,
                                                'to'=>$row_pyr_detail->paymentRequest->code,
                                            ];
                                            
                                            $data_invoices[]=$data_invoices_tempura;
                                        }else{
                                            $found = false;
                                            foreach ($data_invoices as $key => $row_invoice) {
                                                if ($row_invoice["key"] == $data_invoices_tempura["key"]) {
                                                    $found = true;
                                                    break;
                                                }
                                            }
                                            if (!$found) {
                                               
                                                $data_go_chart[]=$data_invoices_tempura;
                                                $data_link[]=[
                                                   'from'=>$row_pyr_detail->lookable->code,
                                                   'to'=>$row_pyr_detail->paymentRequest->code,
                                                ];
                                            
                                                $data_invoices[]=$data_invoices_tempura;
                                            }
                                        }
                                        if(!in_array($row_pyr_detail->lookable->id, $data_id_invoice)){
                                            $data_id_invoice[] = $row_pyr_detail->lookable->id;
                                            $added=true;
                                        }
                                    }

                                }
                            }
                        }
                    }
                    if($query_invoice->hasPaymentRequestDetail()->exists()){
                        foreach($query_invoice->hasPaymentRequestDetail as $row_pyr_detail){
                            $data_pyr_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pyr_detail->paymentRequest->code,
                                "name" => $row_pyr_detail->paymentRequest->code,
                                'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                            ];
                            if(count($data_pyrs)<1){
                                $data_pyrs[]=$data_pyr_tempura;
                                $data_go_chart[]=$data_pyr_tempura;
                                $data_link[]=[
                                    'from'=>$query_invoice->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                ]; 
                                $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;  
                                
                            }else{
                                $found = false;
                                foreach ($data_pyrs as $key => $row_pyr) {
                                    if ($row_pyr["key"] == $data_pyr_tempura["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                             
                                if($found){
                                    $data_links=[
                                        'from'=>$query_invoice->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
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
                                    $data_pyrs[]=$data_pyr_tempura;
                                    $data_go_chart[]=$data_pyr_tempura;
                                    $data_link[]=[
                                        'from'=>$query_invoice->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                    ]; 
                                    $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;   
                                }
                            }
                            if($row_pyr_detail->fundRequest()){
                                $data_fund_tempura=[
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                        ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row_pyr_detail->lookable->code,
                                    "name" => $row_pyr_detail->lookable->code,
                                    'url'=>request()->root()."/admin/finace/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                                ];
                                if(count($data_frs)<1){
                                    $data_frs[]=$data_fund_tempura;
                                    $data_go_chart[]=$data_fund_tempura;
                                    $data_link[]=[
                                        'from'=>$row_pyr_detail->lookable->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                    ]; 
                                    $data_id_frs[]= $row_pyr_detail->lookable->id;  
                                    
                                }else{
                                    $found = false;
                                    foreach ($data_frs as $key => $row_fundreq) {
                                        if ($row_fundreq["key"] == $data_fund_tempura["key"]) {
                                            $found = true;
                                            break;
                                        }
                                    }
                                    
                                    if($found){
                                        $data_links=[
                                            'from'=>$row_pyr_detail->lookable->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
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
                                        $data_frs[]=$data_fund_tempura;
                                        $data_go_chart[]=$data_fund_tempura;
                                        $data_link[]=[
                                            'from'=>$row_pyr_detail->lookable->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
                                        ]; 
                                        $data_id_frs[]= $row_pyr_detail->lookable->id;   
                                    }
                                }
                                
                            }
                            if($row_pyr_detail->purchaseDownPayment()){
                                $data_downp_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                        ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row_pyr_detail->lookable->code,
                                    "name" => $row_pyr_detail->lookable->code,
                                    'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                                ];
                                if(count($data_purchase_downpayment)<1){
                                    $data_purchase_downpayment[]=$data_downp_tempura;
                                    $data_go_chart[]=$data_downp_tempura;
                                    $data_link[]=[
                                        'from'=>$row_pyr_detail->lookable->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                    ]; 
                                    $data_id_dp[]= $row_pyr_detail->lookable->id;  
                                    
                                }else{
                                    $found = false;
                                    foreach ($data_purchase_downpayment as $key => $row_dp) {
                                        if ($row_dp["key"] == $data_downp_tempura["key"]) {
                                            $found = true;
                                            break;
                                        }
                                    }
                                    
                                    if($found){
                                        $data_links=[
                                            'from'=>$row_pyr_detail->lookable->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
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
                                        $data_purchase_downpayment[]=$data_downp_tempura;
                                        $data_go_chart[]=$data_downp_tempura;
                                        $data_link[]=[
                                            'from'=>$row_pyr_detail->lookable->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
                                        ]; 
                                        $data_id_dp[]= $row_pyr_detail->lookable->id;    
                                    }
                                }
                            }
                            if($row_pyr_detail->purchaseInvoice()){
                                $data_invoices_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                        ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row_pyr_detail->lookable->code,
                                    "name" => $row_pyr_detail->lookable->code,
                                    'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                                ];
                                if(count($data_invoices)<1){
                                       
                                    $data_go_chart[]=$data_invoices_tempura;
                                    $data_link[]=[
                                        'from'=>$row_pyr_detail->lookable->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                    ];
                                    
                                    $data_invoices[]=$data_invoices_tempura;
                                }else{
                                    $found = false;
                                    foreach ($data_invoices as $key => $row_invoice) {
                                        if ($row_invoice["key"] == $data_invoices_tempura["key"]) {
                                            $found = true;
                                            break;
                                        }
                                    }
                                    if (!$found) {
                                       
                                        $data_go_chart[]=$data_invoices_tempura;
                                        $data_link[]=[
                                           'from'=>$row_pyr_detail->lookable->code,
                                           'to'=>$row_pyr_detail->paymentRequest->code,
                                        ];
                                    
                                        $data_invoices[]=$data_invoices_tempura;
                                    }
                                }
                                if(!in_array($row_pyr_detail->lookable->id, $data_id_invoice)){
                                    $data_id_invoice[] = $row_pyr_detail->lookable->id;
                                    $added=true;
                                }
                            }
                        }
                    }
                }

                foreach($data_id_pyrs as $payment_request_id){
                    $query_pyr = PaymentRequest::find($payment_request_id);

                    if($query_pyr->outgoingPayment()->exists()){
                        $outgoing_payment = [
                            'properties'=> [
                                ['name'=> "Tanggal :".$query_pyr->outgoingPayment->post_date],
                                ['name'=> "Nominal : Rp.".number_format($query_pyr->outgoingPayment->grandtotal,2,',','.')]
                            ],
                            "key" => $query_pyr->outgoingPayment->code,
                            "name" => $query_pyr->outgoingPayment->code,
                            'url'=>request()->root()."/admin/finance/outgoing_payment?code=".CustomHelper::encrypt($query_pyr->outgoingPayment->code),  
                        ];
                        if(count($data_outgoingpayments) < 1){
                            $data_outgoingpayments[]=$outgoing_payment;
                            $data_go_chart[]=$outgoing_payment;
                            $data_link[]=[
                                'from'=>$query_pyr->code,
                                'to'=>$query_pyr->outgoingPayment->code,
                            ]; 
                        }else{
                            $found = false;
                            foreach($data_outgoingpayments as $row_op){
                                if($outgoing_payment["key"]== $row_op["key"]){
                                    $found = true;
                                    break;
                                }
                            }
                            if($found){
                                $data_links=[
                                    'from'=>$query_pyr->code,
                                    'to'=>$query_pyr->outgoingPayment->code, 
                                ];
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
                                $data_outgoingpayments[]=$outgoing_payment;
                                $data_go_chart[]=$outgoing_payment;
                                $data_link[]=[
                                    'from'=>$query_pyr->code,
                                    'to'=>$query_pyr->outgoingPayment->code,
                                ]; 
                            }
                        }
                    }
                    
                    foreach($query_pyr->paymentRequestDetail as $row_pyr_detail){
                        
                        $data_pyr_tempura=[
                            'properties'=> [
                                ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                                ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                            ],
                            "key" => $row_pyr_detail->paymentRequest->code,
                            "name" => $row_pyr_detail->paymentRequest->code,
                            'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                        ];
                        if($row_pyr_detail->fundRequest()){
                            
                            $data_fund_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                    ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pyr_detail->lookable->code,
                                "name" => $row_pyr_detail->lookable->code,
                                'url'=>request()->root()."/admin/finace/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                            ];
                            if(count($data_frs)<1){
                                $data_frs[]=$data_fund_tempura;
                                $data_go_chart[]=$data_fund_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                ]; 
                                $data_id_frs[]= $row_pyr_detail->lookable->id;  
                                
                            }else{
                                $found = false;
                                foreach ($data_frs as $key => $row_fundreq) {
                                    if ($row_fundreq["key"] == $data_fund_tempura["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                
                                if($found){
                                    $data_links=[
                                        'from'=>$row_pyr_detail->lookable->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
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
                                    $data_frs[]=$data_fund_tempura;
                                    $data_go_chart[]=$data_fund_tempura;
                                    $data_link[]=[
                                        'from'=>$row_pyr_detail->lookable->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                    ]; 
                                    $data_id_frs[]= $row_pyr_detail->lookable->id;   
                                }
                            }
                            
                        }
                        if($row_pyr_detail->purchaseDownPayment()){
                            $data_downp_tempura = [
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pyr_detail->lookable->code,
                                "name" => $row_pyr_detail->lookable->code,
                                'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                            ];
                            if(count($data_purchase_downpayment)<1){
                                
                                $data_purchase_downpayment[]=$data_downp_tempura;
                                $data_go_chart[]=$data_downp_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                ]; 
                                
                                
                            }else{
                                $found = false;
                                foreach ($data_purchase_downpayment as $key => $row_dp) {
                                    if ($row_dp["key"] == $data_downp_tempura["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                
                                if($found){
                                    
                                    $data_links=[
                                        'from'=>$row_pyr_detail->lookable->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
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
                                    $data_purchase_downpayment[]=$data_downp_tempura;
                                    $data_go_chart[]=$data_downp_tempura;
                                    $data_link[]=[
                                        'from'=>$row_pyr_detail->lookable->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                    ]; 
                                    
                                }
                                if(count($data_id_dp)<1){
                                    $data_id_dp[] = $row_pyr_detail->lookable->id;
                                    $added = true;
                                    
                                }
                                
                                
                            }
                            if(!in_array($row_pyr_detail->lookable->id, $data_id_dp)){
                                $data_id_dp[] = $row_pyr_detail->lookable->id;
                                $added = true; 
                               
                            }else{
                            }
                        }
                        if($row_pyr_detail->purchaseInvoice()){
                            $data_invoices_tempura = [
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pyr_detail->lookable->code,
                                "name" => $row_pyr_detail->lookable->code,
                                'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                            ];
                            if(count($data_invoices)<1){
                                   
                                $data_go_chart[]=$data_invoices_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                ];
                                
                                $data_invoices[]=$data_invoices_tempura;
                            }else{
                                $found = false;
                                foreach ($data_invoices as $key => $row_invoice) {
                                    if ($row_invoice["key"] == $data_invoices_tempura["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                   
                                    $data_go_chart[]=$data_invoices_tempura;
                                    $data_link[]=[
                                       'from'=>$row_pyr_detail->lookable->code,
                                       'to'=>$row_pyr_detail->paymentRequest->code,
                                    ];
                                
                                    $data_invoices[]=$data_invoices_tempura;
                                }
                            }
                            if(!in_array($row_pyr_detail->lookable->id, $data_id_invoice)){
                                $data_id_invoice[] = $row_pyr_detail->lookable->id;
                                $added=true;
                            }
                        }
                    }
                    
                }
                foreach($data_id_dp as $downpayment_id){
                    $query_dp = PurchaseDownPayment::find($downpayment_id);
                    foreach($query_dp->purchaseDownPaymentDetail as $row){
                        if($row->purchaseOrder->exists()){
                            $po=[
                                "name"=>$row->purchaseOrder->code,
                                "key" => $row->purchaseOrder->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->purchaseOrder->post_date],
                                    ['name'=> "Vendor  : ".$row->purchaseOrder->supplier->name],
                                    ['name'=> "Nominal : Rp.:".number_format($row->purchaseOrder->grandtotal,2,',','.')],
                                ],
                                'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($row->purchaseOrder->code),
                            ];
                            if(count($data_pos)<1){
                                $data_go_chart[]=$po;
                                $data_link[]=[
                                    'from'=>$row->purchaseOrder->code,
                                    'to'=>$query_dp->code,
                                ];
                                $data_pos[]=$po;
                                
                                $data_id_po []=$row->purchaseOrder->id; 
                                
                            }else{
                                $found = false;
                                foreach ($data_pos as $key => $row_pos) {
                                    if ($row_pos["key"] == $po["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $data_go_chart[]=$po;
                                    $data_link[]=[
                                        'from'=>$row->purchaseOrder->code,
                                        'to'=>$query_dp->code,
                                    ];
                                    $data_pos[]=$po;
                                
                                    $data_id_po []=$row->purchaseOrder->id;
                                }
                            }
                           
                            
                            
                            /* mendapatkan request po */
                            foreach($row->purchaseOrder->purchaseOrderDetail as $po_detail){

                                if($po_detail->purchaseRequestDetail()->exists()){
                                   
                                    $pr = [
                                        "key" => $po_detail->purchaseRequestDetail->purchaseRequest->code,
                                        'name'=> $po_detail->purchaseRequestDetail->purchaseRequest->code,
                                        'properties'=> [
                                            ['name'=> "Tanggal: ".$po_detail->purchaseRequestDetail->purchaseRequest->post_date],
                                           
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
                                                ['name'=> "Nominal : Rp.:".number_format($good_receipt_detail->goodReceipt->grandtotal,2,',','.')],
                                             ],
                                            "key" => $good_receipt_detail->goodReceipt->code,
                                            "name" => $good_receipt_detail->goodReceipt->code,
                                            'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code),  
                                        ];
                    
                                        if(count($data_good_receipts)<1){
                                            
                                            $data_good_receipts[]=$data_good_receipt;
                                            $data_go_chart[]=$data_good_receipt;
                                            $data_link[]=[
                                                'from'=>$row->purchaseOrder->code,
                                                'to'=>$data_good_receipt["key"],
                                            ];
                                           
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

                    foreach($query_dp->purchaseInvoiceDp as $purchase_invoicedp){
                        
                        $invoice_tempura = [
                            "name"=>$purchase_invoicedp->purchaseInvoice->code,
                            "key" => $purchase_invoicedp->purchaseInvoice->code,
                            'properties'=> [
                                ['name'=> "Tanggal :".$purchase_invoicedp->purchaseInvoice->post_date],
                                ['name'=> "Nominal : Rp.:".number_format($purchase_invoicedp->purchaseInvoice->grandtotal,2,',','.')],
                                ],
                            'url'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($purchase_invoicedp->purchaseInvoice->code),           
                        ];
                        if(count($data_invoices)<1){
                           
                            $data_go_chart[]=$invoice_tempura;
                            $data_link[]=[
                                'from'=>$query_dp->code,
                                'to'=>$purchase_invoicedp->purchaseInvoice->code,
                            ];
                            
                            $data_invoices[]=$invoice_tempura;
                        }else{
                            $found = false;
                            foreach ($data_invoices as $key => $row_invoice) {
                                if ($row_invoice["key"] == $invoice_tempura["key"]) {
                                    $found = true;
                                    break;
                                }
                            }
                            if (!$found) {
                               
                                $data_go_chart[]=$invoice_tempura;
                                $data_link[]=[
                                    'from'=>$query_dp->code,
                                    'to'=>$purchase_invoicedp->purchaseInvoice->code,
                                ];
                                
                                $data_invoices[]=$invoice_tempura;
                            }
                        }
                        if(!in_array($purchase_invoicedp->purchaseInvoice->id, $data_id_invoice)){
                            
                            $data_id_invoice[] = $purchase_invoicedp->purchaseInvoice->id;
                            $added = true; 
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
                                        ['name'=> "Nominal : Rp.".number_format($good_receipt_detail->goodReceipt->grandtotal,2,',','.')]
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
                'link'    => $data_link,
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
}