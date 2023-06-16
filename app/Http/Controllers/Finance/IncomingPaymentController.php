<?php

namespace App\Http\Controllers\Finance;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CostDistribution;
use App\Models\Currency;
use App\Models\IncomingPayment;
use App\Models\IncomingPaymentDetail;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\CustomHelper;
use App\Exports\ExportCloseBill;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
                    number_format($val->total,2,',','.'),
                    $val->wTaxMaster->code,
                    number_format($val->percent_wtax,2,',','.'),
                    number_format($val->wtax,2,',','.'),
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
            'company_id'            => 'required',
            'coa_id'                => 'required',
            'post_date'             => 'required',
            'currency_rate'         => 'required',
            'currency_id'           => 'required',
            'total'                 => 'required',
            'wtax'                  => 'required',
            'grandtotal'            => 'required',
            'arr_coa'               => 'required|array',
            'arr_total'             => 'required|array',
            'arr_rounding'          => 'required|array',
            'arr_subtotal'          => 'required|array',
		], [
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
            'coa_id.required'                   => 'Coa Kas / Bank masuk tidak boleh kosong.',
            'post_date.required'                => 'Tanggal posting tidak boleh kosong.',
            'currency_rate.required'            => 'Konversi mata uang tidak boleh kosong.',
            'currency_id.required'              => 'Mata uang tidak boleh kosong.',
            'total.required'                    => 'Total tidak boleh kosong.',
            'wtax.required'                     => 'PPH tidak boleh kosong.',
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
                        foreach($query->approval()->approvalMatrix as $row){
                            if($row->approved){
                                $approved = true;
                            }

                            if($row->revised){
                                $revised = true;
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

                        $query->user_id = session('bo_id');
                        $query->company_id = $request->company_id;
                        $query->account_id = $request->account_id ? $request->account_id : NULL;
                        $query->coa_id = $request->coa_id;
                        $query->post_date = $request->post_date;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->wtax_id = $request->wtax_id;
                        $query->percent_wtax = str_replace(',','.',str_replace('.','',$request->percent_wtax));
                        $query->total = str_replace(',','.',str_replace('.','',$request->total));
                        $query->wtax = str_replace(',','.',str_replace('.','',$request->wtax));
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
                        'code'			            => IncomingPayment::generateCode($request->post_date),
                        'user_id'		            => session('bo_id'),
                        'company_id'                => $request->company_id,
                        'account_id'                => $request->account_id ? $request->account_id : NULL,
                        'coa_id'                    => $request->coa_id,
                        'post_date'                 => $request->post_date,
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'wtax_id'                   => $request->wtax_id,
                        'percent_wtax'              => str_replace(',','.',str_replace('.','',$request->percent_wtax)),
                        'total'                     => str_replace(',','.',str_replace('.','',$request->total)),
                        'wtax'                      => str_replace(',','.',str_replace('.','',$request->wtax)),
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
                            'lookable_id'           => $request->arr_coa[$key],
                            'cost_distribution_id'  => $request->arr_cost_distribution[$key] ? $request->arr_cost_distribution[$key] : NULL,
                            'total'                 => str_replace(',','.',str_replace('.','',$request->arr_total[$key])),
                            'rounding'              => str_replace(',','.',str_replace('.','',$request->arr_rounding[$key])),
                            'subtotal'              => str_replace(',','.',str_replace('.','',$request->arr_subtotal[$key])),
                            'note'                  => $request->arr_note[$key],
                        ]);
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
}