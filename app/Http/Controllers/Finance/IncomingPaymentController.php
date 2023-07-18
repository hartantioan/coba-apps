<?php

namespace App\Http\Controllers\Finance;
use App\Exports\ExportIncomingPayment;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Company;
use App\Models\CostDistribution;
use App\Models\Currency;
use App\Models\IncomingPayment;
use App\Models\IncomingPaymentDetail;
use App\Models\OutgoingPayment;
use App\Models\Place;
use App\Models\Tax;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Http\Request;
use App\Helpers\CustomHelper;
use App\Exports\ExportCloseBill;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Builder;
use App\Models\FundRequest;

class IncomingPaymentController extends Controller
{

    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
    }
    public function index(Request $request)
    {
        $data = [
            'title'         => 'Kas Masuk',
            'content'       => 'admin.finance.incoming_payment',
            'company'       => Company::where('status','1')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'wtax'          => Tax::where('status','1')->where('type','-')->orderByDesc('is_default_pph')->get(),
            'distribution'  => CostDistribution::where('status','1')->get(),
            'currency'      => Currency::where('status','1')->get(),
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => 'IPYM-'.date('y'),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = IncomingPayment::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function getAccountInfo(Request $request){
        $data = User::find($request->id);

        $details = [];

        $op = OutgoingPayment::where('account_id',$data->id)
        ->whereIn('status',['2','3'])
        ->whereHas('paymentRequest',function($query){
            $query->whereHas('paymentRequestDetail',function($query){
                $query->whereHasMorph('lookable',
                [FundRequest::class],
                function (Builder $query){
                    $query->where('document_status','3');
                });
            });
        })->get();
        
        if(isset($op)){
            foreach($op as $row){
                $balance = $row->balancePaymentIncoming();
                if(!$row->used()->exists() && $balance > 0){
                    $details[] = [
                        'id'                    => $row->id,
                        'type'                  => 'outgoing_payments',
                        'code'                  => $row->code,
                        'name'                  => $row->account->name,
                        'payment_request_code'  => $row->paymentRequest->code,
                        'post_date'             => date('d/m/y',strtotime($row->post_date)),
                        'coa_name'              => $row->coaSource->name,
                        'admin'                 => number_format($row->admin,2,',','.'),
                        'total'                 => number_format($row->total,2,',','.'),
                        'grandtotal'            => number_format($row->grandtotal,2,',','.'),
                        'used'                  => number_format($row->totalUsedCross(),2,',','.'),
                        'balance'               => number_format($balance,2,',','.'),
                    ];
                }
            }
        }

        $data['details'] = $details;

        return response()->json($data);
    }

    public function getAccountData(Request $request){
        $details = [];

        if($request->arr_id){
            foreach($request->arr_id as $key => $row){
                if($request->arr_type[$key] == 'outgoing_payments'){
                    $op = OutgoingPayment::find(intval($row));
                    if($op){
                        $balance = $op->balancePaymentIncoming();
                        if(!$op->used()->exists() && $balance > 0){
                            CustomHelper::sendUsedData($op->getTable(),$op->id,'Form Payment Request');
                            $coa = Coa::where('code','100.01.03.03.02')->where('company_id',$op->company_id)->first();
                            $details[] = [
                                'id'                    => $op->id,
                                'code'                  => $op->code,
                                'rawcode'               => $op->code,
                                'type'                  => $op->getTable(),
                                'name'                  => $op->account->name,
                                'payment_request_code'  => $op->paymentRequest->code,
                                'post_date'             => date('d/m/y',strtotime($op->post_date)),
                                'coa_name'              => $op->coaSource->name,
                                'admin'                 => number_format($op->admin,2,',','.'),
                                'total'                 => number_format($op->total,2,',','.'),
                                'grandtotal'            => number_format($op->grandtotal,2,',','.'),
                                'used'                  => number_format($op->totalUsedCross(),2,',','.'),
                                'balance'               => number_format($balance,2,',','.'),
                                'coa_id'                => $coa->id,
                            ];
                        }
                    }
                }
            }
        }

        $user['details'] = $details;

        return response()->json($user);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'company_id',
            'coa_id',
            'post_date',
            'currency_id',
            'currency_rate',
            'total',
            'wtax_id',
            'percent_wtax',
            'wtax',
            'grandtotal',
            'document',
            'note',
            'project_id',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = IncomingPayment::count();
        
        $query_data = IncomingPayment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('wtax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('incomingPaymentDetail',function($query) use($search, $request){
                                $query->where('note','like',"%$search%");
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

        $total_filtered = IncomingPayment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('wtax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('incomingPaymentDetail',function($query) use($search, $request){
                                $query->where('note','like',"%$search%");
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
                if($val->journal()->exists()){
                    $btn_jurnal ='<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue darken-3 white-tex btn-small" data-popup="tooltip" title="Journal" onclick="viewJournal(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">note</i></button>';
                }else{
                    $btn_jurnal ='<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue darken-3 white-tex btn-small disabled" data-popup="tooltip" title="Journal" ><i class="material-icons dp48">note</i></button>';
                }
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->account_id ? $val->account->name : '-',
                    $val->company->name,
                    $val->coa->name,
                    date('d/m/y',strtotime($val->post_date)),
                    $val->currency->code,
                    number_format($val->currency_rate,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->note,
                    $val->project_id ? $val->project->name : '-',
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat cyan darken-4 white-text btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
                        '.$btn_jurnal.'
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
            'code'			        => $request->temp ? ['required', Rule::unique('incoming_payments', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:incoming_payments,code',
            'company_id'            => 'required',
            'coa_id'                => 'required',
            'post_date'             => 'required',
            'currency_rate'         => 'required',
            'currency_id'           => 'required',
            'grandtotal'            => 'required',
            'arr_coa'               => 'required|array',
            'arr_total'             => 'required|array',
            'arr_rounding'          => 'required|array',
            'arr_subtotal'          => 'required|array',
		], [
            'code.required' 				    => 'Kode/No tidak boleh kosong.',
            'code.string'                       => 'Kode harus dalam bentuk string.',
            'code.min'                          => 'Kode harus minimal 18 karakter.',
            'code.unique' 				        => 'Kode/No telah dipakai.',
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
            'coa_id.required'                   => 'Coa Kas / Bank masuk tidak boleh kosong.',
            'post_date.required'                => 'Tanggal posting tidak boleh kosong.',
            'currency_rate.required'            => 'Konversi mata uang tidak boleh kosong.',
            'currency_id.required'              => 'Mata uang tidak boleh kosong.',
            'grandtotal.required'               => 'Grandtotal tidak boleh kosong.',
            'arr_coa.required'                  => 'Coa tidak boleh kosong.',
            'arr_coa.array'                     => 'Coa harus array.',
            'arr_total.required'                => 'Total tidak boleh kosong.',
            'arr_total.array'                   => 'Total harus array.',
            'arr_rounding.required'             => 'Pembulatan tidak boleh kosong.',
            'arr_rounding.array'                => 'Pembulatan harus array.',
            'arr_subtotal.required'             => 'Subtotal tidak boleh kosong.',
            'arr_subtotal.array'                => 'Subtotal harus array.',
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
                    $query = IncomingPayment::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Kas / Bank Masuk telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','6'])){

                        if($request->has('document')) {
                            if(Storage::exists($query->document)){
                                Storage::delete($query->document);
                            }
                            $document = $request->file('document')->store('public/incoming_payments');
                        } else {
                            $document = $query->document;
                        }

                        $query->code = $request->code;
                        $query->user_id = session('bo_id');
                        $query->company_id = $request->company_id;
                        $query->account_id = $request->account_id ? $request->account_id : NULL;
                        $query->coa_id = $request->coa_id;
                        $query->post_date = $request->post_date;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->total = str_replace(',','.',str_replace('.','',$request->grandtotal));
                        $query->wtax = 0;
                        $query->grandtotal = str_replace(',','.',str_replace('.','',$request->grandtotal));
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->project_id = $request->project_id ? $request->project_id : NULL;
                        $query->status = '1';

                        $query->save();

                        $query->incomingPaymentDetail()->delete();

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Kas / Bank Masuk sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = IncomingPayment::create([
                        'code'			            => $request->code,
                        'user_id'		            => session('bo_id'),
                        'company_id'                => $request->company_id,
                        'account_id'                => $request->account_id ? $request->account_id : NULL,
                        'coa_id'                    => $request->coa_id,
                        'post_date'                 => $request->post_date,
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'total'                     => str_replace(',','.',str_replace('.','',$request->grandtotal)),
                        'wtax'                      => 0,
                        'grandtotal'                => str_replace(',','.',str_replace('.','',$request->grandtotal)),
                        'document'                  => $request->file('document') ? $request->file('document')->store('public/incoming_payments') : NULL,
                        'note'                      => $request->note,
                        'project_id'                => $request->project_id ? $request->project_id : NULL,
                        'status'                    => '1',
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                DB::beginTransaction();
                try {
                    foreach($request->arr_coa as $key => $row){
                        IncomingPaymentDetail::create([
                            'incoming_payment_id'   => $query->id,
                            'lookable_type'         => $request->arr_type[$key],
                            'lookable_id'           => $request->arr_type[$key] == 'coas' ? $request->arr_coa[$key] : $request->arr_id[$key],
                            'cost_distribution_id'  => $request->arr_cost_distribution[$key] ? $request->arr_cost_distribution[$key] : NULL,
                            'total'                 => str_replace(',','.',str_replace('.','',$request->arr_total[$key])),
                            'rounding'              => str_replace(',','.',str_replace('.','',$request->arr_rounding[$key])),
                            'subtotal'              => str_replace(',','.',str_replace('.','',$request->arr_subtotal[$key])),
                            'note'                  => $request->arr_note[$key],
                        ]);

                        if($request->arr_type[$key] == 'outgoing_payments'){
                            CustomHelper::removeCountLimitCredit($query->account_id,str_replace(',','.',str_replace('.','',$request->arr_total[$key])));
                        }
                    }
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                CustomHelper::sendApproval('incoming_payments',$query->id,$query->note);
                CustomHelper::sendNotification('incoming_payments',$query->id,'Kas / Bank Masuk No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new IncomingPayment())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit incoming payment.');

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
        $data   = IncomingPayment::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">
                    <table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="8">Detail Rincian</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Referensi</th>
                                <th class="center-align">Tipe</th>
                                <th class="center-align">Dist.Biaya</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">Pembulatan</th>
                                <th class="center-align">Subtotal</th>
                                <th class="center-align">Keterangan</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->incomingPaymentDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->lookable->code.'</td>
                <td class="center-align">'.class_basename($row->lookable).'</td>
                <td class="center-align">'.($row->cost_distribution_id ? $row->costDistribution->code : '-').'</td>
                <td class="right-align">'.number_format($row->total,3,',','.').'</td>
                <td class="right-align">'.number_format($row->rounding,3,',','.').'</td>
                <td class="right-align">'.number_format($row->subtotal,3,',','.').'</td>
                <td class="">'.$row->note.'</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;max-width:100%;">
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

    public function approval(Request $request,$id){
        
        $pr = IncomingPayment::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Kas / Bank Masuk',
                'data'      => $pr
            ];

            return view('admin.approval.incoming_payment', $data);
        }else{
            abort(404);
        }
    }

    public function show(Request $request){
        $ip = IncomingPayment::where('code',CustomHelper::decrypt($request->id))->first();
        $ip['code_place_id'] = substr($ip->code,7,2);
        $ip['grandtotal'] = number_format($ip->grandtotal,2,',','.');
        $ip['account_name'] = $ip->account_id ? $ip->account->name : '';
        $ip['coa_name'] = $ip->coa->code.' - '.$ip->coa->name;
        $ip['currency_rate'] = number_format($ip->currency_rate,2,',','.');
        $ip['project_name'] = $ip->project_id ? $ip->project->name : '';
        $coareceivable = Coa::where('code','100.01.03.03.02')->where('company_id',$ip->company_id)->first()->id;

        $arr = [];

        foreach($ip->incomingPaymentDetail as $row){
            $arr[] = [
                'type'                  => $row->lookable_type,
                'coa_id'                => $row->lookable_type == 'coas' ? $row->lookable_id : $coareceivable,
                'id'                    => $row->lookable_id,
                'post_date'             => $row->lookable_type == 'coas' ? '-' : date('d/m/y',strtotime($row->lookable->post_date)),
                'name'                  => $row->getCode(),
                'cost_distribution_id'  => $row->cost_distribution_id ? $row->cost_distribution_id : '',
                'cost_distribution_name'=> $row->cost_distribution_id ? $row->costDistribution->code.' - '.$row->costDistribution->name : '',
                'total'                 => number_format($row->total,'2',',','.'),
                'rounding'              => number_format($row->rounding,'2',',','.'),
                'subtotal'              => number_format($row->subtotal,'2',',','.'),
                'note'                  => $row->note,
            ];
        }

        $ip['details'] = $arr;
        				
		return response()->json($ip);
    }

    public function voidStatus(Request $request){
        $query = IncomingPayment::where('code',CustomHelper::decrypt($request->id))->first();
        
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

                foreach($query->incomingPaymentDetail as $row){
                    if($row->lookable_type == 'outgoing_payments'){
                        CustomHelper::addCountLimitCredit($query->account_id,$row->total);
                    }
                }
    
                activity()
                    ->performedOn(new IncomingPayment())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the incoming payment data');
    
                CustomHelper::sendNotification('incoming_payments',$query->id,'Kas / Bank Masuk No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('incoming_payments',$query->id);
                CustomHelper::removeJournal('incoming_payments',$query->id);

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

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData($request->table,$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function destroy(Request $request){
        $query = IncomingPayment::where('code',CustomHelper::decrypt($request->id))->first();

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
                'message' => 'Jurnal / dokumen sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            foreach($query->incomingPaymentDetail as $row){
                if($row->lookable_type == 'outgoing_payments'){
                    CustomHelper::addCountLimitCredit($query->account_id,$row->total);
                }
                $row->delete();
            }

            CustomHelper::removeApproval('incoming_payments',$query->id);

            activity()
                ->performedOn(new IncomingPayment())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the incoming payment data');

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

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
		return Excel::download(new ExportIncomingPayment($post_date,$end_date), 'incoming_payment_'.uniqid().'.xlsx');
    }

    public function viewJournal(Request $request,$id){
        $query = IncomingPayment::where('code',CustomHelper::decrypt($id))->first();
        if($query->journal()->exists()){
            $response = [
                'title'     => 'Journal',
                'status'    => 200,
                'message'   => $query->journal,
                'user'      => $query->user->name,
                'reference' =>  $query->lookable_id ? $query->lookable->code : '-',
            ];
            $string='';
            foreach($query->journal->journalDetail()->orderBy('id')->get() as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td>'.$row->coa->code.' - '.$row->coa->name.'</td>
                    <td class="center-align">'.$row->coa->company->name.'</td>
                    <td class="center-align">'.($row->account_id ? $row->account->name : '-').'</td>
                    <td class="center-align">'.($row->place_id ? $row->place->name : '-').'</td>
                    <td class="center-align">'.($row->line_id ? $row->line->name : '-').'</td>
                    <td class="center-align">'.($row->machine_id ? $row->machine->name : '-').'</td>
                    <td class="center-align">'.($row->department_id ? $row->department->name : '-').'</td>
                    <td class="center-align">'.($row->warehouse_id ? $row->warehouse->name : '-').'</td>
                    <td class="right-align">'.($row->type == '1' ? number_format($row->nominal,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '2' ? number_format($row->nominal,2,',','.') : '').'</td>
                </tr>';
            }
            $response["tbody"] = $string; 
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Data masih belum di approve.'
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
                        $query = IncomingPayment::where('Code', 'LIKE', '%'.$nomor)->first();
                        if($query){
                            $data = [
                                'title'     => 'Good Issue',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.finance.incoming_payment_individual', $data)->setPaper('a5', 'landscape');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
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


                    Storage::put('public/pdf/bubla.pdf',$result);
                    $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
                    $var_link=$document_po;
        
                    $response =[
                        'status'=>200,
                        'message'  =>$var_link
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
                        $query = IncomingPayment::where('Code', 'LIKE', '%'.$code)->first();
                        if($query){
                            $data = [
                                'title'     => 'Good Issue',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.finance.incoming_payment_individual', $data)->setPaper('a5', 'landscape');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
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
    
    
                    Storage::put('public/pdf/bubla.pdf',$result);
                    $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
                    $var_link=$document_po;
        
                    $response =[
                        'status'    =>200,
                        'message'   =>$var_link
                    ];
                }
            }
        }
        return response()->json($response);
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
                $pr = IncomingPayment::where('code',$row)->first();
                
                if($pr){
                    $data = [
                        'title'     => 'Incoming Payment',
                        'data'      => $pr
                    ];
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $pdf = Pdf::loadView('admin.print.finance.incoming_payment_individual', $data)->setPaper('a5', 'landscape');
                    $pdf->render();
                    $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
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

            Storage::put('public/pdf/bubla.pdf',$result);
            $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
            $var_link=$document_po;

            $response =[
                'status'=>200,
                'message'  =>$var_link
            ];
        }
		
		return response()->json($response);

    }

    public function printIndividual(Request $request,$id){
        
        $pr = IncomingPayment::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');        
        if($pr){
            $data = [
                'title'     => 'Incoming Payment',
                'data'      => $pr
            ];

            $opciones_ssl=array(
                "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
                ),
            );
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path, false, stream_context_create($opciones_ssl));
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
             
            $pdf = Pdf::loadView('admin.print.finance.incoming_payment_individual', $data)->setPaper('a5', 'landscape');
            $pdf->render();
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            Storage::put('public/pdf/bubla.pdf',$content);
            $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
    
    
            return $document_po;
        }else{
            abort(404);
        }
    }
}