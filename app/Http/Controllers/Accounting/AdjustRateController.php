<?php

namespace App\Http\Controllers\Accounting;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Company;
use App\Models\JournalDetail;
use App\Models\Place;
use App\Models\User;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportClosingJournal;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Models\AdjustRate;
use App\Models\AdjustRateDetail;
use App\Models\Currency;
use App\Models\GoodReceipt;
use App\Models\Journal;
use App\Models\LockPeriod;
use App\Models\Menu;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;

class AdjustRateController extends Controller
{
    protected $dataplaces, $dataplacecode;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
    }

    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));
        $menu = Menu::where('url', $lastSegment)->first();
        $data = [
            'title'         => 'Adjust Kurs',
            'content'       => 'admin.accounting.adjust_rate',
            'company'       => Company::where('status','1')->get(),
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code,
            'place'         => Place::whereIn('id',$this->dataplaces)->where('status','1')->get(),
            'currency'      => Currency::where('status','1')->where('type','2')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = AdjustRate::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'company_id',
            'post_date',
            'currency_id',
            'currency_rate',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = AdjustRate::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = AdjustRate::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%");
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
                    $query->whereIn('status', $request->status);
                }
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = AdjustRate::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%");
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
                    $query->whereIn('status', $request->status);
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
                    $btn_jurnal ='<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light grey darken-3 white-tex btn-small disabled" data-popup="tooltip" title="Journal" ><i class="material-icons dp48">note</i></button>';
                }
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->company->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->currency->name,
                    number_format($val->currency_rate,2,',','.'),
                    $val->note,
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
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

    public function preview(Request $request){
        
        $cek = AdjustRate::where('company_id',$request->company_id)
                ->where('post_date',$request->post_date)
                ->where('currency_id',$request->currency_id)
                ->whereIn('status',['1','2','3'])->first();

        if(!$cek){
            $result = [];

            $datagr = GoodReceipt::whereDoesntHave('used')->whereIn('status',['2','3'])->where('post_date','<=',$request->post_date)->whereHas('journal',function($query)use($request){
                $query->where('currency_id',$request->currency_id);
            })->get();

            $dataapdp = PurchaseDownPayment::whereDoesntHave('used')->whereIn('status',['2','3','7'])->where('post_date','<=',$request->post_date)->whereHas('journal',function($query)use($request){
                $query->where('currency_id',$request->currency_id);
            })->get();

            $datainvoice = PurchaseInvoice::whereDoesntHave('used')->whereIn('status',['2','3','7'])->where('post_date','<=',$request->post_date)->where('currency_id',$request->currency_id)->get();

            $arrCoa = Coa::where('status','1')->where('currency_id',$request->currency_id)->whereNotNull('is_cash_account')->get();

            foreach($arrCoa as $coacash){
                $datadebitfc = JournalDetail::whereHas('journal',function($query)use($request){
                    $query->whereIn('status',['2','3'])->where('post_date','<=',$request->post_date);
                })->where('coa_id',$coacash->id)->where('type','1')->sum('nominal_fc');
                $datacreditfc = JournalDetail::whereHas('journal',function($query)use($request){
                    $query->whereIn('status',['2','3'])->where('post_date','<=',$request->post_date);
                })->where('coa_id',$coacash->id)->where('type','2')->sum('nominal_fc');
                $datadebitrp = JournalDetail::whereHas('journal',function($query)use($request){
                    $query->whereIn('status',['2','3'])->where('post_date','<=',$request->post_date);
                })->where('coa_id',$coacash->id)->where('type','1')->sum('nominal');
                $datacreditrp = JournalDetail::whereHas('journal',function($query)use($request){
                    $query->whereIn('status',['2','3'])->where('post_date','<=',$request->post_date);
                })->where('coa_id',$coacash->id)->where('type','2')->sum('nominal');
                $balancefc = $datadebitfc - $datacreditfc;
                if($balancefc > 0){
                    $balancerp = $datadebitrp - $datacreditrp;
                    $currency_rate = round($balancerp / $balancefc,2);
                    $result[] = [
                        'coa_id'        => $coacash->id,
                        'lookable_type' => $coacash->getTable(),
                        'lookable_id'   => $coacash->id,
                        'code'          => $coacash->code.' - '.$coacash->name,
                        'type_document' => 'Kas Mata Uang Asing',
                        'nominal_fc'    => number_format($balancefc,2,',','.'),
                        'latest_rate'   => number_format($currency_rate,2,',','.'),
                        'nominal_rp'    => number_format($balancerp,2,',','.'),
                        'type'          => '1',
                    ];
                }
            }

            $coahutangusahabelumditagih = Coa::where('code','200.01.03.01.02')->where('company_id',$request->company_id)->first();
            $coahutangusaha = Coa::where('code','200.01.03.01.01')->where('company_id',$request->company_id)->first();

            foreach($datagr as $row){
                $latest_rate = $row->latestCurrencyRateByDate($request->post_date);
                $total = $row->balanceTotal();
                if($total > 0){
                    $result[] = [
                        'coa_id'        => $coahutangusahabelumditagih->id,
                        'lookable_type' => $row->getTable(),
                        'lookable_id'   => $row->id,
                        'code'          => $row->code,
                        'type_document' => 'GRPO',
                        'nominal_fc'    => number_format($total,2,',','.'),
                        'latest_rate'   => number_format($latest_rate,2,',','.'),
                        'nominal_rp'    => number_format($latest_rate * $total,2,',','.'),
                        'type'          => '2',
                    ];
                }
            }

            foreach($dataapdp as $row){
                $latest_rate = $row->latestCurrencyRateByDate($request->post_date);
                $total = $row->balancePayment();
                if($total > 0){
                    $result[] = [
                        'coa_id'        => $coahutangusaha->id,
                        'lookable_type' => $row->getTable(),
                        'lookable_id'   => $row->id,
                        'code'          => $row->code,
                        'type_document' => 'APDP',
                        'nominal_fc'    => number_format($total,2,',','.'),
                        'latest_rate'   => number_format($latest_rate,2,',','.'),
                        'nominal_rp'    => number_format($latest_rate * $total,2,',','.'),
                        'type'          => '2',
                    ];
                }
            }

            foreach($datainvoice as $row){
                $latest_rate = $row->latestCurrencyRateByDate($request->post_date);
                $total = $row->balancePayment();
                if($total > 0){
                    $result[] = [
                        'coa_id'        => $coahutangusaha->id,
                        'lookable_type' => $row->getTable(),
                        'lookable_id'   => $row->id,
                        'code'          => $row->code,
                        'type_document' => 'APIN',
                        'nominal_fc'    => number_format($total,2,',','.'),
                        'latest_rate'   => number_format($latest_rate,2,',','.'),
                        'nominal_rp'    => number_format($latest_rate * $total,2,',','.'),
                        'type'          => '2',
                    ];
                }
            }

            $response = [
                'status'    => 200,
                'message'   => '',
                'result'    => $result,
            ];

        }else{
            $response = [
                'status'    => 500,
                'message'   => 'Mohon maaf tanggal '.date('d/m/Y',strtotime($cek->post_date)).' untuk mata uang '.$cek->currency->name.' dan perusahaan '.$cek->company->name.' telah dibuat Perbaikan Kurs, silahkan pilih tanggal lainnya.'
            ];
        }

        return response()->json($response);
    }

    function checkArray($array,$val){
        $index = -1;
        foreach($array as $key => $value){
            if($value['coa_id'] == $val){
                $index = $key;
            }
        }
        return $index;
    }

    public function create(Request $request){

        /* DB::beginTransaction();
        try { */
            $validation = Validator::make($request->all(), [
                'code'                      => 'required',
                'code_place_id'             => 'required',
                'post_date'			        => 'required',
                'company_id'		        => 'required',
                'currency_id'               => 'required',
                'currency_rate'             => 'required',
                'note'                      => 'required',
                'arr_type'                  => 'required|array',
                'arr_coa_id'                   => 'required|array',
                'arr_nominal_fc'            => 'required|array',
                'arr_latest_rate'           => 'required|array',
                'arr_nominal_rp'            => 'required|array',
                'arr_nominal_new'           => 'required|array',
                'arr_balance'               => 'required|array',
            ], [
                'code.required' 				    => 'Kode/No tidak boleh kosong.',
                'code_place_id.required'            => 'Plant Tidak boleh kosong',
                'post_date.required' 			    => 'Tanggal post tidak boleh kosong.',
                'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
                'currency_id.required' 			    => 'Mata Uang tidak boleh kosong.',
                'currency_rate.required' 			=> 'Kurs tidak boleh kosong.',
                'note.required' 			        => 'Keterangan / catatan tidak boleh kosong.',
                'arr_type.required'                 => 'Tipe tidak boleh kosong',
                'arr_type.array'                    => 'Tipe harus dalam bentuk array.',
                'arr_coa_id.required'               => 'Coa tidak boleh kosong',
                'arr_coa_id.array'                  => 'Coa harus dalam bentuk array.',
                'arr_nominal_fc.required'           => 'Nominal FC tidak boleh kosong',
                'arr_nominal_fc.array'              => 'Nominal FC harus dalam bentuk array.',
                'arr_latest_rate.required'          => 'Nominal Kurs Terakhir tidak boleh kosong',
                'arr_latest_rate.array'             => 'Nominal Kurs Terakhir harus dalam bentuk array.',
                'arr_nominal_rp.required'           => 'Nominal Rupiah Sisa tidak boleh kosong',
                'arr_nominal_rp.array'              => 'Nominal Rupiah Sisa harus dalam bentuk array.',
                'arr_nominal_new.required'          => 'Nominal Rupiah Terbaru tidak boleh kosong',
                'arr_nominal_new.array'             => 'Nominal Rupiah Terbaru harus dalam bentuk array.',
                'arr_balance.required'              => 'Nominal Selisih tidak boleh kosong',
                'arr_balance.array'                 => 'Nominal Selisih harus dalam bentuk array.',
            ]);

            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            } else {

                if($request->temp){
                    
                    $query = AdjustRate::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Adjust Kurs telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','6'])){

                        $query->code = $request->code;
                        $query->user_id = session('bo_id');
                        $query->company_id = $request->company_id;
                        $query->post_date = $request->post_date;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->note = $request->note;
                        $query->status = '1';
                        $query->save();

                        $query->adjustRateDetail()->delete();

                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Status adjust kurs sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }else{
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode = AdjustRate::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    $query = AdjustRate::create([
                        'code'			=> $newCode,
                        'user_id'		=> session('bo_id'),
                        'company_id'    => $request->company_id,
                        'post_date'	    => $request->post_date,
                        'currency_id'   => $request->currency_id,
                        'currency_rate' => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'status'        => '1',
                        'note'          => $request->note,
                    ]);
                }
                
                if($query) {
                    $new_currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                    foreach($request->arr_nominal_fc as $key => $row){
                        $latest_currency_rate = str_replace(',','.',str_replace('.','',$request->arr_latest_rate[$key]));
                        $nominal_rp = str_replace(',','.',str_replace('.','',$row)) * $latest_currency_rate;
                        $nominal_new = str_replace(',','.',str_replace('.','',$row)) * $new_currency_rate;
                        $balance = $nominal_new - $nominal_rp;
                        AdjustRateDetail::create([
                            'adjust_rate_id'        => $query->id,
                            'lookable_type'         => $request->arr_lookable_type[$key],
                            'lookable_id'           => $request->arr_lookable_id[$key],
                            'coa_id'                => $request->arr_coa_id[$key],
                            'nominal_fc'            => str_replace(',','.',str_replace('.','',$row)),
                            'nominal_rate'          => str_replace(',','.',str_replace('.','',$request->arr_latest_rate[$key])),
                            'nominal_rp'            => $nominal_rp,
                            'nominal_new'           => $nominal_new,
                            'nominal'               => $balance,
                            'type'                  => $request->arr_type[$key],
                        ]);
                    }

                    CustomHelper::sendApproval($query->getTable(),$query->id,$query->note);
                    CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Adjust Kurs No. '.$query->code,$query->note,session('bo_id'));

                    activity()
                        ->performedOn(new AdjustRate())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit adjust rate.');

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

            /* DB::commit(); */
            
            return response()->json($response);
        /* }catch(\Exception $e){
            DB::rollback();
        } */
    }

    public function rowDetail(Request $request){
        $data   = AdjustRate::where('code',CustomHelper::decrypt($request->id))->first();
        $total = 0;
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center">No.</th>
                                <th class="center">Nomor</th>
                                <th class="center">Coa</th>
                                <th class="center">Nominal Sisa (FC)</th>
                                <th class="center">Kurs Terakhir</th>
                                <th class="center">Nominal Sisa (Rp)</th>
                                <th class="center">Nominal Terbaru (Rp)</th>
                                <th class="center">Nominal Selisih (Rp)</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->adjustRateDetail as $key => $row){
            $total += round($row->nominal);
            $string .= '<tr>

                <td class="center-align">'.($key + 1).'</td>
                <td>'.$row->lookable->code.'</td>
                <td>'.$row->coa->code.' - '.$row->coa->name.'</td>
                <td class="right-align">'.number_format($row->nominal_fc,2,',','.').'</td>
                <td class="right-align">'.number_format($row->nominal_rate,2,',','.').'</td>
                <td class="right-align">'.number_format($row->nominal_rp,2,',','.').'</td>
                <td class="right-align">'.number_format($row->nominal_new,2,',','.').'</td>
                <td class="right-align">'.number_format($row->nominal,2,',','.').'</td>
            </tr>';
        }
        
        $string .= '</tbody>
                        <tfoot>
                            <tr>
                                <th colspan="7" class="right-align">TOTAL</th>
                                <td class="right-align">'.number_format($total,2,',','.').'</td>
                            </tr>
                        </tfoot>
                    </table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="5">Approval</th>
                            </tr>
                            <tr>
                                <th class="center-align">Level</th>
                                <th class="center-align">Kepada</th>
                                <th class="center-align">Status</th>
                                <th class="center-align">Catatan</th>
                                <th class="center-align">Tanggal</th>
                            </tr>
                        </thead><tbody>';
        
        if($data->approval() && $data->hasDetailMatrix()){
            foreach($data->approval() as $detail){
                $string .= '<tr>
                    <td class="center-align" colspan="5"><h6>'.$detail->getTemplateName().'</h6></td>
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
                        <td class="center-align">' . ($row->date_process ? \Carbon\Carbon::parse($row->date_process)->format('d/m/Y H:i:s') : '-') . '</td>
                    </tr>';
                }
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="5">Approval tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function show(Request $request){
        $ret = AdjustRate::where('code',CustomHelper::decrypt($request->id))->first();
        $ret['code_place_id'] = substr($ret->code,7,2);
        $ret['currency_rate'] = number_format($ret->currency_rate,2,',','.');

        $arr = [];
        $total = 0;
        foreach($ret->adjustRateDetail as $row){
            $arr[] = [
                'coa_id'        => $row->coa_id,
                'lookable_type' => $row->lookable_type,
                'lookable_id'   => $row->lookable_id,
                'code'          => $row->lookable->code,
                'type_document' => $row->getType(),
                'nominal_fc'    => number_format($row->nominal_fc,2,',','.'),
                'latest_rate'   => number_format($row->nominal_rate,2,',','.'),
                'nominal_rp'    => number_format($row->nominal_rp,2,',','.'),
                'nominal_new'   => number_format($row->nominal_new,2,',','.'),
                'balance'       => number_format($row->nominal,2,',','.'),
                'type'          => $row->type,
            ];
            $total += $row->nominal;
        }

        $ret['details'] = $arr;
        $ret['total'] = number_format($total,2,',','.');
        				
		return response()->json($ret);
    }

    public function voidStatus(Request $request){
        $query = AdjustRate::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {

            if(!CustomHelper::checkLockAcc($query->post_date)){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                ]);
            }

            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }else{

                CustomHelper::removeJournal($query->getTable(),$query->id);

                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new AdjustRate())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the adjust rate data');
    
                CustomHelper::sendNotification($query->getTable(),$query->id,'Adjust Kurs No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval($query->getTable(),$query->id);
                if(in_array($query->status,['2','3'])){
                    CustomHelper::removeJournal($query->getTable(),$query->id);
                }

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
        $query = AdjustRate::where('code',CustomHelper::decrypt($request->id))->first();

        if($query->delete()) {

            if(in_array($query->status,['2','3'])){
                CustomHelper::removeJournal($query->getTable(),$query->id);
            }

            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);

            CustomHelper::removeApproval($query->getTable(),$query->id);
            
            foreach($query->adjustRateDetail as $row){
                $row->delete();
            }

            activity()
                ->performedOn(new AdjustRate())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the adjust rate data');

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
                $pr = AdjustRate::where('code',$row)->first();
                
                if($pr){
                    $pdf = PrintHelper::print($pr,'Adjust Kurs','a4','portrait','admin.print.accounting.adjust_rate_individual');
                    $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                    $pdf->getCanvas()->page_text(495, 770, "Jumlah Print, ". $pr->printCounter()->count(), $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(505, 780, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(422, 790, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                    $content = $pdf->download()->getOriginalContent();
                    $temp_pdf[]=$content;
                }
                    
            }
            $merger = new Merger();
            foreach ($temp_pdf as $pdfContent) {
                $merger->addRaw($pdfContent);
            }


            $result = $merger->merge();


            $document_po = PrintHelper::savePrint($result);

            $response =[
                'status'=>200,
                'message'  =>$document_po
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
                        $lastSegment = $request->lastsegment;
                      
                        $menu = Menu::where('url', $lastSegment)->first();
                        $nomorLength = strlen($nomor);
                        
                        // Calculate the number of zeros needed for padding
                        $paddingLength = max(0, 8 - $nomorLength);

                        // Pad $nomor with leading zeros to ensure it has at least 8 digits
                        $nomorPadded = str_repeat('0', $paddingLength) . $nomor;
                        $x =$menu->document_code.$request->year_range.$request->code_place_range.'-'.$nomorPadded; 
                        $query = AdjustRate::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Adjust Kurs','a4','portrait','admin.print.accounting.adjust_rate_individual');
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 770, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(505, 780, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 790, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;
                           
                        }
                    }
                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }


                    $result = $merger->merge();


                    $document_po = PrintHelper::savePrint($result);
        
                    $response =[
                        'status'=>200,
                        'message'  =>$document_po
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
                        $etNumbersArray = explode(',', $request->tabledata);
                        $query = AdjustRate::where('code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Adjust Kurs','a4','portrait','admin.print.accounting.adjust_rate_individual');
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 770, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(505, 780, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 790, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;
                           
                        }
                    }
                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }
    
    
                    $result = $merger->merge();
    
    
                    $document_po = PrintHelper::savePrint($result);
        
                    $response =[
                        'status'=>200,
                        'message'  =>$document_po
                    ];
                }
            }
        }
        return response()->json($response);
    }

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';
		return Excel::download(new ExportClosingJournal($post_date,$end_date,$mode), 'closing_journal_'.uniqid().'.xlsx');
    }

    public function approval(Request $request,$id){
        
        $cap = AdjustRate::where('code',CustomHelper::decrypt($id))->first();
                
        if($cap){
            $data = [
                'title'     => 'Adjust Kurs',
                'data'      => $cap
            ];

            return view('admin.approval.adjust_rate', $data);
        }else{
            abort(404);
        }
    }

    public function viewJournal(Request $request,$id){
        $total_debit_asli = 0;
        $total_debit_konversi = 0;
        $total_kredit_asli = 0;
        $total_kredit_konversi = 0;
        $query = AdjustRate::where('code',CustomHelper::decrypt($id))->first();
        if($query->journal()->exists()){
            $rowmain = $query->journal()->first();
            $response = [
                'title'     => 'Journal',
                'status'    => 200,
                'message'   => $rowmain,
                'user'      => $query->user->name,
                'reference' => $query->code,
                'company'   => $query->company()->exists() ? $query->company->name : '-',
                'code'      => $rowmain->code,
                'note'      => $query->note,
                'post_date' => date('d/m/Y',strtotime($query->post_date)),
            ];
            $string='';
            foreach($rowmain->journalDetail()->where(function($query){
                $query->whereHas('coa',function($query){
                    $query->orderBy('code');
                })
                ->orderBy('type');
            })->get() as $key => $row){

                if($row->type == '1'){
                    $total_debit_asli += $row->nominal_fc;
                    $total_debit_konversi += $row->nominal;
                }
                if($row->type == '2'){
                    $total_kredit_asli += $row->nominal_fc;
                    $total_kredit_konversi += $row->nominal;
                }

                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td>'.$row->coa->code.' - '.$row->coa->name.'</td>
                    <td class="center-align">'.($row->account_id ? $row->account->name : '-').'</td>
                    <td class="center-align">'.($row->place_id ? $row->place->code : '-').'</td>
                    <td class="center-align">'.($row->line_id ? $row->line->name : '-').'</td>
                    <td class="center-align">'.($row->machine_id ? $row->machine->name : '-').'</td>
                    <td class="center-align">'.($row->department_id ? $row->department->name : '-').'</td>
                    <td class="center-align">'.($row->warehouse_id ? $row->warehouse->name : '-').'</td>
                    <td class="center-align">'.($row->project_id ? $row->project->name : '-').'</td>
                    <td class="center-align">'.($row->note ? $row->note : '').'</td>
                    <td class="center-align">'.($row->note2 ? $row->note2 : '').'</td>
                    <td class="right-align">'.($row->type == '1' ? number_format($row->nominal_fc,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '2' ? number_format($row->nominal_fc,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '1' ? number_format($row->nominal,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '2' ? number_format($row->nominal,2,',','.') : '').'</td>
                </tr>';
            }
            $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="11"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_debit_asli, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_kredit_asli, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_debit_konversi, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_kredit_konversi, 2, ',', '.') . '</td>
            </tr>';
            $response["tbody"] = $string; 
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Data masih belum di approve.'
            ]; 
        }
        return response()->json($response);
    }

    public function printIndividual(Request $request,$id){
        
        $pr = AdjustRate::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');        
        if($pr){
            $pdf = PrintHelper::print($pr,'Adjust Kurs','a4','portrait','admin.print.accounting.adjust_rate_individual');
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(495, 770, "Jumlah Print, ". $pr->printCounter()->count(), $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(505, 780, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 790, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            $randomString = Str::random(10); 

         
            $filePath = 'public/pdf/' . $randomString . '.pdf';
            

            Storage::put($filePath, $content);
            
            $document_po = asset(Storage::url($filePath));
    
    
            return $document_po;
        }else{
            abort(404);
        }
    }
}