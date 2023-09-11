<?php

namespace App\Http\Controllers\Sales;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\MarketingOrderDownPayment;
use App\Models\MarketingOrderInvoice;
use App\Models\MarketingOrderInvoiceDetail;
use App\Models\MarketingOrderMemo;
use App\Models\MarketingOrderMemoDetail;
use App\Models\Place;
use Illuminate\Http\Request;
use App\Helpers\CustomHelper;
use App\Models\TaxSeries;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Process;
use Illuminate\Contracts\Process\ProcessResult;

class MarketingOrderMemoController extends Controller
{
    protected $dataplaces, $dataplacecode, $datawarehouses;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];

    }
    
    public function index(Request $request)
    {
        $data = [
            'title'         => 'AR Credit Memo',
            'content'       => 'admin.sales.order_memo',
            'company'       => Company::where('status','1')->get(),
            'place'         => Place::where('status','1')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => 'SMMO-'.date('y'),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = MarketingOrderMemo::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function getTaxSeries(Request $request){
		return response()->json(TaxSeries::getTaxCode($request->company_id,$request->date));
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'company_id',
            'post_date',
            'document',
            'tax_no',
            'note',
            'total',
            'tax',
            'total_after_tax',
            'rounding',
            'grandtotal',
            'downpayment',
            'balance'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = MarketingOrderMemo::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = MarketingOrderMemo::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('total_after_tax', 'like', "%$search%")
                            ->orWhere('rounding', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('downpayment', 'like', "%$search%")
                            ->orWhere('balance', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('tax_no', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
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
                    $query->where('status', $request->status);
                }

                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }

                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = MarketingOrderMemo::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('total_after_tax', 'like', "%$search%")
                            ->orWhere('rounding', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('downpayment', 'like', "%$search%")
                            ->orWhere('balance', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('tax_no', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
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
                    $query->where('status', $request->status);
                }
                
                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }

                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
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
                    $val->account->name,
                    $val->company->name,
                    date('d/m/y',strtotime($val->post_date)),
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->tax_no,
                    $val->note,
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->total_after_tax,2,',','.'),
                    number_format($val->rounding,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    number_format($val->downpayment,2,',','.'),
                    number_format($val->balance,2,',','.'),
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan darken-4 white-tex btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
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

    public function sendUsedData(Request $request){
        if($request->type == 'marketing_order_invoices'){
            $modp = MarketingOrderInvoice::find($request->id);
        }elseif($request->type == 'marketing_order_down_payments'){
            $modp = MarketingOrderDownPayment::find($request->id);
        }
       
        if(!$modp->used()->exists()){
            CustomHelper::sendUsedData($modp->getTable(),$request->id,'Form AR Credit Memo');
            return response()->json([
                'status'    => 200,
            ]);
        }else{
            return response()->json([
                'status'    => 500,
                'message'   => 'Dokumen no. '.$modp->used->lookable->code.' telah dipakai di '.$modp->used->ref.', oleh '.$modp->used->user->name.'.'
            ]);
        }
    }

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData($request->type,$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function create(Request $request){
        
        $validation = Validator::make($request->all(), [
            'code'			                => $request->temp ? ['required', Rule::unique('marketing_order_invoices', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:marketing_order_invoices,code',
            'code_place_id'                 => 'required',
            'company_id'			        => 'required',
            'account_id'	                => 'required',
            'post_date'		                => 'required',
            'arr_lookable_id'		        => 'required|array',
            'arr_total'                     => 'required|array',
        ], [
            'code.required' 	                    => 'Kode tidak boleh kosong.',
            'code.string'                           => 'Kode harus dalam bentuk string.',
            'code.min'                              => 'Kode harus minimal 18 karakter.',
            'code.unique'                           => 'Kode telah dipakai.',
            'code_place_id.required'                => 'No plant dokumen tidak boleh kosong.',
            'account_id.required' 	                => 'Akun Partner Bisnis tidak boleh kosong.',
            'company_id.required' 			        => 'Perusahaan tidak boleh kosong.',
            'post_date.required' 			        => 'Tanggal post tidak boleh kosong.',
            'arr_lookable_id.required'              => 'Item tidak boleh kosong.',
            'arr_lookable_id.array'                 => 'Item harus dalam bentuk array.',
            'arr_total.required'                    => 'Total tidak boleh kosong.',
            'arr_total.array'                       => 'Total harus dalam bentuk array.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $total = 0;
            $tax = 0;
            $total_after_tax = 0;
            $rounding = 0;
            $grandtotal = 0;
            $downpayment = 0;
            $balance = 0;
            
            $arrNominal = [];

            foreach($request->arr_nominal as $key => $row){
                $bobot = str_replace(',','.',str_replace('.','',$row)) / str_replace(',','.',str_replace('.','',$request->arr_balance[$key]));
                $rowtotal = round($bobot * str_replace(',','.',str_replace('.','',$request->arr_total[$key])),2);
                $rowtax = round($bobot * str_replace(',','.',str_replace('.','',$request->arr_tax[$key])),2);
                $rowtotalaftertax = round($bobot * str_replace(',','.',str_replace('.','',$request->arr_total_after_tax[$key])),2);
                $rowrounding = round($bobot * str_replace(',','.',str_replace('.','',$request->arr_rounding[$key])),2);
                $rowgrandtotal = round($bobot * str_replace(',','.',str_replace('.','',$request->arr_grandtotal[$key])),2);
                $rowdownpayment = round($bobot * str_replace(',','.',str_replace('.','',$request->arr_downpayment[$key])),2);
                $rowbalance = round($bobot * str_replace(',','.',str_replace('.','',$request->arr_balance[$key])),2);
                $arrNominal[] = [
                    'total'             => $rowtotal,
                    'tax'               => $rowtax,
                    'total_after_tax'   => $rowtotalaftertax,
                    'rounding'          => $rowrounding,
                    'grandtotal'        => $rowgrandtotal,
                    'downpayment'       => $rowdownpayment,
                    'balance'           => $rowbalance,
                ];
                $total += $rowtotal;
                $tax += $rowtax;
                $total_after_tax += $rowtotalaftertax;
                $rounding += $rowrounding;
                $grandtotal += $rowgrandtotal;
                $downpayment += $rowdownpayment;
                $balance += $rowbalance;
            }

            $bp = User::find($request->account_id);

            if($balance > $bp->count_limit_credit){
                return response()->json([
                    'status'  => 500,
                    'message' => 'AR Credit Memo tidak bisa diproses karena Saldo Piutang adalah '.number_format($bp->count_limit_credit,2,',','.').', sedangkan nominal yang anda inputkan adalah '.number_format($balance,2,',','.').'.'
                ]);
            }
            
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = MarketingOrderMemo::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'AR Credit Memo telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','6'])){

                        if($request->has('document')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('document')->store('public/marketing_order_memos');
                        } else {
                            $document = $query->document;
                        }

                        $query->user_id = session('bo_id');
                        $query->code = $request->code;
                        $query->account_id = $request->account_id;
                        $query->company_id = $request->company_id;
                        $query->post_date = $request->post_date;
                        $query->status = '1';
                        $query->note = $request->note;
                        $query->document = $document;
                        $query->tax_no = $request->tax_no;
                        $query->total = $total;
                        $query->tax = $tax;
                        $query->total_after_tax = $total_after_tax;
                        $query->rounding = $rounding;
                        $query->grandtotal = $grandtotal;
                        $query->downpayment = $downpayment;
                        $query->balance = $balance;

                        $query->save();
                        
                        foreach($query->MarketingOrderMemoDetail as $row){
                            $row->delete();
                        }

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status AR Credit Memo detail sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = MarketingOrderMemo::create([
                        'code'			                => $request->code,
                        'user_id'		                => session('bo_id'),
                        'account_id'                    => $request->account_id,
                        'company_id'                    => $request->company_id,
                        'post_date'                     => $request->post_date,
                        'note'                          => $request->note,
                        'status'                        => '1',
                        'document'                      => $request->file('document') ? $request->file('document')->store('public/marketing_order_memos') : NULL,
                        'tax_no'                        => $request->tax_no,
                        'total'                         => $total,
                        'tax'                           => $tax,
                        'total_after_tax'               => $total_after_tax,
                        'rounding'                      => $rounding,
                        'grandtotal'                    => $grandtotal,
                        'downpayment'                   => $downpayment,
                        'balance'                       => $balance,
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                foreach($request->arr_lookable_id as $key => $row){
                    $momd = MarketingOrderMemoDetail::create([
                        'marketing_order_memo_id'       => $query->id,
                        'lookable_type'                 => $request->arr_lookable_type[$key],
                        'lookable_id'                   => $row,
                        'is_include_tax'                => $request->arr_is_include_tax[$key],
                        'percent_tax'                   => $request->arr_percent_tax[$key],
                        'tax_id'                        => $request->arr_tax_id[$key] > 0 ? $request->arr_tax_id[$key] : NULL,
                        'total'                         => $arrNominal[$key]['total'],
                        'tax'                           => $arrNominal[$key]['tax'],
                        'total_after_tax'               => $arrNominal[$key]['total_after_tax'],
                        'rounding'                      => $arrNominal[$key]['rounding'],
                        'grandtotal'                    => $arrNominal[$key]['grandtotal'],
                        'downpayment'                   => $arrNominal[$key]['downpayment'],
                        'balance'                       => $arrNominal[$key]['balance'],
                        'note'                          => $request->arr_note[$key],
                    ]);

                    if($request->arr_lookable_type[$key] == 'marketing_order_invoice_details'){
                        if($momd->downpayment > 0){
                            CustomHelper::addDeposit($query->account_id,$momd->downpayment);
                        }
                    }elseif($request->arr_lookable_type[$key] == 'marketing_order_down_payments'){
                        CustomHelper::removeDeposit($query->account_id,$momd->balance);
                    }
                    CustomHelper::removeCountLimitCredit($query->account_id,$momd->balance);
                }

                CustomHelper::sendApproval($query->getTable(),$query->id,$query->note);
                CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan AR Credit Memo No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new MarketingOrderMemo())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit AR Credit Memo.');

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
        $data   = MarketingOrderMemo::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12"><table style="min-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="9">Daftar Item & Surat Jalan</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Dokumen</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">PPN</th>
                                <th class="center-align">Total Stl PPN</th>
                                <th class="center-align">Pembulatan</th>
                                <th class="center-align">Grandtotal</th>
                                <th class="center-align">Downpayment</th>
                                <th class="center-align">Memo</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->marketingOrderMemoDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->getCode().'</td>
                <td class="">'.$row->note.'</td>
                <td class="right-align">'.number_format($row->total,2,',','.').'</td>
                <td class="right-align">'.number_format($row->tax,2,',','.').'</td>
                <td class="right-align">'.number_format($row->total_after_tax,2,',','.').'</td>
                <td class="right-align">'.number_format($row->rounding,2,',','.').'</td>
                <td class="right-align">'.number_format($row->grandtotal,2,',','.').'</td>
                <td class="right-align">'.number_format($row->downpayment,2,',','.').'</td>
                <td class="right-align">'.number_format($row->balance,2,',','.').'</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;">
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
        
        if($data->approval() && $data->hasDetailMatrix()){
            foreach($data->approval() as $detail){
                $string .= '<tr>
                    <td class="center-align" colspan="4"><h6>'.$detail->getTemplateName().'</h6></td>
                </tr>';
                foreach($detail->approvalMatrix as $key => $row){
                    $icon = '';
    
                    if($row->status == '1' || $row->status == '0'){
                        $icon = '<i class="material-icons">hourglass_empty</i>';
                    }elseif($row->status == '2'){
                        if($row->approved){
                            $icon = '<i class="material-icons">thumb_up</i>';
                        }elseif($row->rejected){
                            $icon = '<i class="material-icons">thumb_down</i>';
                        }elseif($row->revised){
                            $icon = '<i class="material-icons">border_color</i>';
                        }
                    }
    
                    $string .= '<tr>
                        <td class="center-align">'.$row->approvalTemplateStage->approvalStage->level.'</td>
                        <td class="center-align">'.$row->user->profilePicture().'<br>'.$row->user->name.'</td>
                        <td class="center-align">'.$icon.'<br></td>
                        <td class="center-align">'.$row->note.'</td>
                    </tr>';
                }
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
        
        $moi = MarketingOrderMemo::where('code',CustomHelper::decrypt($id))->first();
                
        if($moi){
            $data = [
                'title'     => 'Print AR Credit Memo',
                'data'      => $moi
            ];

            return view('admin.approval.marketing_order_memo', $data);
        }else{
            abort(404);
        }
    }

    public function printIndividual(Request $request,$id){
        
        $pr = MarketingOrderMemo::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Pengembalian DO',
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
             
            $pdf = Pdf::loadView('admin.print.sales.order_memo_individual', $data)->setPaper('a5', 'landscape');
            // $pdf->render();
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            Storage::put('public/pdf/bubla.pdf',$content);
            $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
    
            return $document_po;
        }else{
            abort(404);
        }
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
            foreach($request->arr_id as $key => $row){
                $pr = MarketingOrderMemo::where('code',$row)->first();
                
                if($pr){
                    $data = [
                        'title'     => 'Print Pengembalian DO',
                        'data'      => $pr,
                    ];
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $pdf = Pdf::loadView('admin.print.sales.order_memo_individual', $data)->setPaper('a5', 'landscape');
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
                        $query = MarketingOrderMemo::where('code', 'LIKE', '%'.$nomor)->first();
                        if($query){
                            $data = [
                                'title'     => 'Print Pengembalian DO',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.sales.order_memo_individual', $data)->setPaper('a5', 'landscape');
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
                        $query = MarketingOrderMemo::where('code', 'LIKE', '%'.$code)->first();
                        if($query){
                            $data = [
                                'title'     => 'Print Pengembalian DO',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.sales.order_memo_individual', $data)->setPaper('a5', 'landscape');
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
        }
        return response()->json($response);
    }

    public function viewJournal(Request $request,$id){
        $query = MarketingOrderMemo::where('code',CustomHelper::decrypt($id))->first();
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

    public function show(Request $request){
        $po = MarketingOrderMemo::where('code',CustomHelper::decrypt($request->id))->first();
        $po['code_place_id'] = substr($po->code,7,2);
        $po['account_name'] = $po->account->code.' - '.$po->account->name;
        $po['balance'] = number_format($po->balance,2,',','.');

        if($po->tax_no){
            $newprefix = '011.'.explode('.',$po->tax_no)[1].'.'.explode('.',$po->tax_no)[2];
            $po['tax_no'] = $newprefix;
        }

        $arr = [];
        $arrUsed = [];
        
        foreach($po->marketingOrderMemoDetail as $row){
            $type = $row->getType();
            $id = $row->getId();
            $code = $row->getCode();

            if($row->lookable_type !== 'coas'){
                $cekIndex = $this->getIndexArray($id,$type,$arrUsed);

                if($cekIndex < 0){
                    $arrUsed[] = [
                        'id'    => $id,
                        'type'  => $type,
                        'code'  => $code,
                    ];
                }
            }

            $arr[] = [
                'id'                                    => $id,
                'lookable_type'                         => $row->lookable_type,
                'lookable_id'                           => $row->lookable_id,
                'is_include_tax'                        => $row->is_include_tax,
                'tax_id'                                => $row->tax_id ? $row->tax_id : '0',
                'percent_tax'                           => $row->percent_tax,
                'total'                                 => number_format($row->total,2,',','.'),
                'tax'                                   => number_format($row->tax,2,',','.'),
                'total_after_tax'                       => number_format($row->total_after_tax,2,',','.'),
                'rounding'                              => number_format($row->rounding,2,',','.'),
                'grandtotal'                            => number_format($row->grandtotal,2,',','.'),
                'downpayment'                           => number_format($row->downpayment,2,',','.'),
                'balance'                               => number_format($row->balance,2,',','.'),
                'code'                                  => $code,
                'post_date'                             => $row->getDate(),
                'note'                                  => $row->note,
            ];
        }

        foreach($arrUsed as $row){
            CustomHelper::sendUsedData($row['type'],$row['id'],'Form AR Credit Memo');
        }

        $po['details'] = $arr;
        $po['used'] = $arrUsed;
        				
		return response()->json($po);
    }

    function getIndexArray($id,$type,$array){
        $index = -1;

        foreach($array as $key => $row){
            if($row['id'] == $id && $row['type'] == $type){
                $index = $key;
            }
        }

        return $index;
    }

    public function voidStatus(Request $request){
        $query = MarketingOrderMemo::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {
            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }elseif($query->hasChildDocument()){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah digunakan pada form lainnya.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                foreach($query->marketingOrderMemoDetail as $row){
                    if($row->lookable_type == 'marketing_order_invoice_details'){
                        if($row->downpayment > 0){
                            CustomHelper::removeDeposit($query->account_id,$row->downpayment);
                        }
                    }elseif($row->lookable_type == 'marketing_order_down_payments'){
                        CustomHelper::addDeposit($query->account_id,$row->balance);
                    }
                    CustomHelper::addCountLimitCredit($query->account_id,$row->balance);
                }
    
                activity()
                    ->performedOn(new MarketingOrderMemo())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the data');
    
                CustomHelper::sendNotification($query->getTable(),$query->id,'AR Credit Memo No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval($query->getTable(),$query->id);
                CustomHelper::removeJournal($query->getTable(),$query->id);
                CustomHelper::removeCountLimitCredit($query->account_id,$query->balance);

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
        $query = MarketingOrderMemo::where('code',CustomHelper::decrypt($request->id))->first();

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
                'message' => 'Dokumen sudah diupdate, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            foreach($query->marketingOrderMemoDetail as $row){
                if($row->lookable_type == 'marketing_order_invoice_details'){
                    if($row->downpayment > 0){
                        CustomHelper::removeDeposit($query->account_id,$row->downpayment);
                    }
                }elseif($row->lookable_type == 'marketing_order_down_payments'){
                    CustomHelper::addDeposit($query->account_id,$row->balance);
                }
                CustomHelper::addCountLimitCredit($query->account_id,$row->balance);
            }

            $query->marketingOrderMemoDetail()->delete();

            CustomHelper::removeApproval($query->getTable(),$query->id);

            activity()
                ->performedOn(new MarketingOrderInvoice())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the marketing order return data');

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
}