<?php

namespace App\Http\Controllers\Accounting;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Division;
use App\Models\Line;
use App\Models\Machine;
use App\Models\Place;
use App\Models\User;
use App\Models\Journal;
use App\Models\JournalDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportJournal;
use App\Exports\ExportTemplateJournalCopy;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\CustomHelper;
use App\Models\Menu;
use App\Models\Project;

class JournalController extends Controller
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
            'title'     => 'Jurnal',
            'content'   => 'admin.accounting.journal',
            'company'   => Company::where('status','1')->get(),
            'currency'  => Currency::where('status','1')->get(),
            'place'     => Place::whereIn('id',$this->dataplaces)->where('status','1')->get(),
            'department'=> Division::where('status','1')->orderBy('name')->get(),
            'line'      => Line::where('status','1')->get(),
            'machine'   => Machine::where('status','1')->orderBy('name')->get(),
            'minDate'   => $request->get('minDate'),
            'maxDate'   => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code,
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = Journal::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'post_date',
            'note'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Journal::count();
        
        $query_data = Journal::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name', 'like', "%$search%");
                            })
                            ->orWhereHas('journalDetail',function($query) use ($search, $request){
                                $query->where('nominal', 'like', "%$search%")
                                    ->orWhereHas('coa',function($query) use ($search, $request){
                                        $query->where('code', 'like', "%$search%")
                                            ->orWhere('name', 'like', "%$search%");
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
                    $query->whereIn('status', $request->status);
                }
                
                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
                }

            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = Journal::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name', 'like', "%$search%");
                            })
                            ->orWhereHas('journalDetail',function($query) use ($search, $request){
                                $query->where('nominal', 'like', "%$search%")
                                    ->orWhereHas('coa',function($query) use ($search, $request){
                                        $query->where('code', 'like', "%$search%")
                                            ->orWhere('name', 'like', "%$search%");
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
                    $query->whereIn('status', $request->status);
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
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->note,
                    $val->lookable_id ? $val->lookable->code : '-',
                    $val->status(),
                    /* !$val->lookable_id ?  */'
                    <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                    <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">delete</i></button>
					'/*  : '-' */,
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

    public function rowDetail(Request $request){
        $data   = Journal::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" rowspan="2">No.</th>
                                <th class="center-align" rowspan="2">Coa</th>
                                <th class="center-align" rowspan="2">Perusahaan</th>
                                <th class="center-align" rowspan="2">Partner Bisnis</th>
                                <th class="center-align" rowspan="2">Plant</th>
                                <th class="center-align" rowspan="2">Line</th>
                                <th class="center-align" rowspan="2">Mesin</th>
                                <th class="center-align" rowspan="2">Divisi</th>
                                <th class="center-align" rowspan="2">Proyek</th>
                                <th class="center-align" rowspan="2">Keterangan 1</th>
                                <th class="center-align" rowspan="2">Keterangan 2</th>
                                <th class="center-align" colspan="2">Mata Uang Asli</th>
                                <th class="center-align" colspan="2">Mata Uang Konversi</th>
                            </tr>
                            <tr>
                                <th class="center-align">Debit</th>
                                <th class="center-align">Kredit</th>
                                <th class="center-align">Debit</th>
                                <th class="center-align">Kredit</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->journalDetail()->where(function($query){
            $query->whereHas('coa',function($query){
                $query->orderBy('code');
            })
            ->orderBy('type');
        })
        ->get() as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td>'.$row->coa->code.' - '.$row->coa->name.'</td>
                <td class="center-align">'.$row->coa->company->name.'</td>
                <td class="center-align">'.($row->account_id ? $row->account->name : '-').'</td>
                <td class="center-align">'.($row->place_id ? $row->place->code : '-').'</td>
                <td class="center-align">'.($row->line_id ? $row->line->name : '-').'</td>
                <td class="center-align">'.($row->machine_id ? $row->machine->name : '-').'</td>
                <td class="center-align">'.($row->department_id ? $row->department->name : '-').'</td>
                <td class="center-align">'.($row->project()->exists() ? $row->project->name : '-').'</td>
                <td class="">'.$row->note.'</td>
                <td class="">'.$row->note2.'</td>
                <td class="right-align">'.($row->type == '1' ? number_format($row->nominal_fc,2,',','.') : '').'</td>
                <td class="right-align">'.($row->type == '2' ? number_format($row->nominal_fc,2,',','.') : '').'</td>
                <td class="right-align">'.($row->type == '1' ? number_format($row->nominal,2,',','.') : '').'</td>
                <td class="right-align">'.($row->type == '2' ? number_format($row->nominal,2,',','.') : '').'</td>
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

    public function create(Request $request){
        DB::beginTransaction();
        try {
            $validation = Validator::make($request->all(), [
                'code'                      => 'required',
               /*  'code' 				        => $request->temp ? ['required', Rule::unique('journals', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|unique:journals,code', */
                'code_place_id'             => 'required',
                'company_id'                => 'required',
                'note'                      => 'required',
                'post_date'                 => 'required',
                'currency_id'               => 'required',
                'currency_rate'             => 'required',
                'arr_type'                  => 'required|array',
                'arr_place'                 => 'required|array',
                'arr_department'            => 'required|array',
                'arr_nominal'               => 'required|array',
            ], [
                'code.required' 				    => 'Kode/No tidak boleh kosong.',
                'code_place_id.required'            => 'Plant Tidak boleh kosong',
                'company_id.required'               => 'Perusahaan tidak boleh kosong',
                'note.required'                     => 'Catatan tidak boleh kosong',
                'post_date.required'                => 'Tgl post tidak boleh kosong.',
                'currency_rate.required'            => 'Konversi tidak boleh kosong.',
                'currency_id.required'              => 'Mata uang tidak boleh kosong.',
                'arr_type.required'                 => 'Tipe tidak boleh kosong.',
                'arr_type.array'                    => 'Tipe harus dalam bentuk array.',
                'arr_place.required'                => 'Penempatan tidak boleh kosong.',
                'arr_place.array'                   => 'Penempatan harus dalam bentuk array.',
                'arr_department.required'           => 'Departemen tidak boleh kosong.',
                'arr_department.array'              => 'Departemen harus dalam bentuk array.',
                'arr_nominal.required'              => 'Nominal tidak boleh kosong.',
                'arr_nominal.array'                 => 'Nominal harus dalam bentuk array.',
            ]);

            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            } else {
                
                $totalDebit = 0; 
                $totalCredit = 0;
                foreach($request->arr_nominal as $key => $row){
                    if($request->arr_type[$key] == '1'){
                        $totalDebit += str_replace(',','.',str_replace('.','',$row));
                    }elseif($request->arr_type[$key] == '2'){
                        $totalCredit += str_replace(',','.',str_replace('.','',$row));
                    }
                }

                if($totalDebit - $totalCredit > 0 || $totalDebit - $totalCredit < 0){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Total debit dan kredit selisih '.(number_format($totalDebit - $totalCredit,2,',','.'))
                    ]);
                }

                if($request->temp){
                    $query = Journal::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Jurnal entri telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','6'])){

                        $query->code = $request->code;
                        $query->lookable_type = $request->journal_id ? 'journals' : NULL;
                        $query->lookable_id = $request->journal_id ? $request->journal_id : NULL;
                        $query->user_id = session('bo_id');
                        $query->company_id = $request->company_id;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->post_date = $request->post_date;
                        $query->note = $request->note;
                        $query->status = '1';

                        $query->save();

                        foreach($query->journalDetail as $row){
                            $row->delete();
                        }

                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Status jurnal sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }else{
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=Journal::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    $query = Journal::create([
                        'code'			            => $newCode,
                        'lookable_type'             => $request->journal_id ? 'journals' : NULL,
                        'lookable_id'               => $request->journal_id ? $request->journal_id : NULL,
                        'user_id'		            => session('bo_id'),
                        'currency_id'               => $request->currency_id,
                        'company_id'                => $request->company_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'post_date'                 => $request->post_date,
                        'note'                      => $request->note,
                        'status'                    => '1'
                    ]);
                }
                
                if($query) {
                    
                    if($request->arr_type){
                        foreach($request->arr_type as $key => $row){
                            JournalDetail::create([
                                'journal_id'                    => $query->id,
                                'cost_distribution_detail_id'   => $request->arr_cost_distribution_detail[$key] == 'NULL' ? NULL : $request->arr_cost_distribution_detail[$key],
                                'coa_id'                        => $request->arr_coa[$key],
                                'place_id'                      => $request->arr_place[$key] == 'NULL' ? NULL : $request->arr_place[$key],
                                'line_id'                       => $request->arr_line[$key] == 'NULL' ? NULL : $request->arr_line[$key],
                                'machine_id'                    => $request->arr_machine[$key] == 'NULL' ? NULL : $request->arr_machine[$key],
                                'account_id'                    => $request->arr_account[$key] == 'NULL' ? NULL : $request->arr_account[$key],
                                'department_id'                 => $request->arr_department[$key],
                                'project_id'                    => $request->arr_project[$key] == 'NULL' ? NULL : $request->arr_project[$key],
                                'note'                          => $request->arr_note[$key] == '' ? NULL : $request->arr_note[$key],
                                'note2'                         => $request->arr_note2[$key] == '' ? NULL : $request->arr_note2[$key],
                                'type'                          => $row,
                                'nominal'                       => str_replace(',','.',str_replace('.','',$request->arr_nominal[$key])),
                                'nominal_fc'                    => str_replace(',','.',str_replace('.','',$request->arr_nominal_fc[$key])),
                            ]);
                        }
                    }

                    CustomHelper::sendApproval('journals',$query->id,$query->note);
                    CustomHelper::sendNotification('journals',$query->id,'Pengajuan Jurnal No. '.$query->code,$query->note,session('bo_id'));

                    activity()
                        ->performedOn(new Journal())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit journal.');

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
            
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
        }

        return response()->json($response);
    }

    public function createMulti(Request $request){
        $validation = Validator::make($request->all(), [
            'arr_multi_code'                          => 'required|array',
		], [
            'arr_multi_code.required'                 => 'Kode multi tidak boleh kosong.',
            'arr_multi_code.array'                    => 'Kode multi harus dalam bentuk array.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            
            $totalDebit = 0; 
            $totalCredit = 0;
            $totalDebitFC = 0; 
            $totalCreditFC = 0;
            foreach($request->arr_multi_debit as $key => $row){
                $totalDebit += floatval($row);
            }

            foreach($request->arr_multi_debit_fc as $key => $row){
                $totalDebitFC += floatval($row);
            }

            foreach($request->arr_multi_kredit as $key => $row){
                $totalCredit += floatval($row);
            }

            foreach($request->arr_multi_kredit_fc as $key => $row){
                $totalCreditFC += floatval($row);
            }

            $cekCoa = true;
            $coaNotAvailable = [];
            $coaAvailable = [];

            foreach($request->arr_multi_coa as $key => $row){
                $coaAda = null;
                $coaAda = Coa::where('code',explode('|',$row)[0])->where('status','1')->first();
                if(!$coaAda){
                    $cekCoa = false;
                    $coaNotAvailable[] = $row;
                }else{
                    $coaAvailable[] = $coaAda->id;
                }
            }

            if($cekCoa == false){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Coa tidak ditemukan '.implode(',',$coaNotAvailable).'.'
                ]);
            }            

            if($totalDebit - $totalCredit > 0 || $totalDebit - $totalCredit < 0){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Total debit dan kredit selisih '.(number_format($totalDebit - $totalCredit,2,',','.'))
                ]);
            }

            if($totalDebitFC - $totalCreditFC > 0 || $totalDebitFC - $totalCreditFC < 0){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Total debit mata uang asli dan kredit mata uang asli terdapat selisih '.(number_format($totalDebitFC - $totalCreditFC,2,',','.'))
                ]);
            }

            $cekSameCode = Journal::whereIn('code',$request->arr_multi_code)->count();

            if($cekSameCode > 0){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Kode jurnal telah terpakai, silahkan gunakan yang lainnya.'
                ]);
            }
        
            DB::beginTransaction();
            try {

                $temp = '';
                foreach($request->arr_multi_code as $key => $row){

                    if($temp !== $row){
                        $currency = Currency::where('code',explode('|',$request->arr_multi_currency[$key])[0])->first();
                        $company = Company::where('code',explode('|',$request->arr_multi_company[$key])[0])->first();

                        $query = Journal::create([
                            'code'			            => $row,
                            'user_id'		            => session('bo_id'),
                            'currency_id'               => $currency->id,
                            'company_id'                => $company->id,
                            'currency_rate'             => $request->arr_multi_conversion[$key] ? $request->arr_multi_conversion[$key] : NULL,
                            'post_date'                 => $request->arr_multi_post_date[$key] ? date('Y-m-d',strtotime($request->arr_multi_post_date[$key])) : NULL,
                            'note'                      => $request->arr_multi_note[$key] ? $request->arr_multi_note[$key] : NULL,
                            'status'                    => '1'
                        ]);

                        CustomHelper::sendApproval('journals',$query->id,$query->note);
                        CustomHelper::sendNotification('journals',$query->id,'Pengajuan Jurnal No. '.$query->code,$query->note,session('bo_id'));

                        activity()
                            ->performedOn(new Journal())
                            ->causedBy(session('bo_id'))
                            ->withProperties($query)
                            ->log('Add / edit journal.');
                    }

                    if($query) {
                        $account = User::where('employee_no',explode('|',$request->arr_multi_bp[$key])[0])->first();
                        $place = Place::where('code',explode('|',$request->arr_multi_place[$key])[0])->first();
                        $line = Line::where('code',explode('|',$request->arr_multi_line[$key])[0])->first();
                        $machine = Machine::where('code',explode('|',$request->arr_multi_machine[$key])[0])->first();
                        $project = Project::where('code',explode('|',$request->arr_multi_project[$key])[0])->first();
                        $department = Department::where('code',explode('|',$request->arr_multi_department[$key])[0])->first();

                        if(floatval($request->arr_multi_debit[$key]) > 0){
                            JournalDetail::create([
                                'journal_id'        => $query->id,
                                'coa_id'            => $coaAvailable[$key],
                                'account_id'        => $account ? $account->id : NULL,
                                'place_id'          => $place ? $place->id : NULL,
                                'line_id'           => $line ? $line->id : NULL,
                                'machine_id'        => $machine ? $machine->id : NULL,
                                'project_id'        => $project ? $project->id : NULL,
                                'department_id'     => $department ? $department->id : NULL,
                                'type'              => '1',
                                'nominal'           => floatval($request->arr_multi_debit[$key]),
                                'nominal_fc'        => floatval($request->arr_multi_debit_fc[$key]),
                                'note'              => $request->arr_multi_note_detail[$key],
                                'note2'             => $request->arr_multi_note_detail2[$key],
                            ]);
                        }

                        if(floatval($request->arr_multi_kredit[$key]) > 0){
                            JournalDetail::create([
                                'journal_id'        => $query->id,
                                'coa_id'            => $coaAvailable[$key],
                                'account_id'        => $account ? $account->id : NULL,
                                'place_id'          => $place ? $place->id : NULL,
                                'line_id'           => $line ? $line->id : NULL,
                                'machine_id'        => $machine ? $machine->id : NULL,
                                'project_id'        => $project ? $project->id : NULL,
                                'department_id'     => $department ? $department->id : NULL,
                                'type'              => '2',
                                'nominal'           => floatval($request->arr_multi_kredit[$key]),
                                'nominal_fc'        => floatval($request->arr_multi_kredit_fc[$key]),
                                'note'              => $request->arr_multi_note_detail[$key],
                                'note2'             => $request->arr_multi_note_detail2[$key],
                            ]);
                        }
                    }
                    
                    $temp = $row;
                }

                $response = [
					'status'    => 200,
					'message'   => 'Data successfully saved.',
				];

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
        }

        return response()->json($response);
    }

    public function show(Request $request){
        $jou = Journal::where('code',CustomHelper::decrypt($request->id))->first();
        $jou['currency_rate'] = number_format($jou->currency_rate,2,',','.');
        $jou['code_place_id'] = substr($jou->code,7,2);
        $jou['journal_id'] = $jou->lookable_type == 'journals' ? $jou->lookable_id : '';
        $jou['journal_name'] = $jou->lookable_id ? $jou->lookable->code.' - '.$jou->lookable->note : '';

        $arr = [];
        
        foreach($jou->journalDetail()->where(function($query){
            $query->whereHas('coa',function($query){
                $query->orderBy('code');
            })
            ->orderBy('type');
        })->get() as $row){
            $arr[] = [
                'type'                          => $row->type,
                'cost_distribution_detail_id'   => $row->cost_distribution_detail_id ? $row->cost_distribution_detail_id : '', 
                'coa_id'                        => $row->coa_id,
                'coa_name'                      => $row->coa->code.' - '.$row->coa->name,
                'place_id'                      => $row->place_id ? $row->place_id : '',
                'account_id'                    => $row->account_id ? $row->account_id : '',
                'account_name'                  => $row->account_id ? $row->account->name : '',
                'line_id'                       => $row->line_id ? $row->line_id : '',
                'line_name'                     => $row->line_id ? $row->line->code.' - '.$row->line->name : '',
                'machine_id'                    => $row->machine()->exists() ? $row->machine_id : '',
                'machine_name'                  => $row->machine()->exists() ? $row->machine->name : '',
                'department_id'                 => $row->department_id ? $row->department_id : '',
                'project_id'                    => $row->project()->exists() ? $row->project_id : '',
                'project_name'                  => $row->project()->exists() ? $row->project->name : '',
                'nominal'                       => number_format($row->nominal,2,',','.'),
                'nominal_fc'                    => number_format($row->nominal_fc,2,',','.'),
                'note'                          => $row->note ? $row->note : '',
                'note2'                         => $row->note2 ? $row->note2 : '',
            ];
        }

        $jou['details'] = $arr;
        				
		return response()->json($jou);
    }

    public function destroy(Request $request){
        
        $query = Journal::where('code',CustomHelper::decrypt($request->id))->first();
        
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
                'message' => 'Jurnal sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);

            $query->journalDetail()->delete();

            CustomHelper::removeApproval('journals',$query->id);

            activity()
                ->performedOn(new Journal())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the journal data');

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

    public function voidStatus(Request $request){
        $query = Journal::where('code',CustomHelper::decrypt($request->id))->first();
        
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

                foreach($query->journalDetail as $row){
                    $row->delete();
                }
    
                activity()
                    ->performedOn(new Journal())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the journal data');
    
                CustomHelper::sendNotification('journals',$query->id,'Jurnal No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);

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
                $pr = Journal::where('code',$row)->first();
                
                if($pr){
                    $data = [
                        'title'     => 'Journal',
                        'data'      => $pr
                    ];
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $pdf = Pdf::loadView('admin.print.accounting.journal_individual', $data)->setPaper('a4', 'landscape');
                    $pdf->render();
                    $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                    $pdf->getCanvas()->page_text(650, 550, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(650, 560, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                    $content = $pdf->download()->getOriginalContent();
                    $temp_pdf[]=$content;
                }
                    
            }
            $merger = new Merger();
            foreach ($temp_pdf as $pdfContent) {
                $merger->addRaw($pdfContent);
            }


            $result = $merger->merge();


            $randomString = Str::random(10); 

         
            $filePath = 'public/pdf/' . $randomString . '.pdf';
            

            Storage::put($filePath, $result);
            
            $document_po = asset(Storage::url($filePath));
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
                        $lastSegment = $request->lastsegment;
                      
                        $menu = Menu::where('url', $lastSegment)->first();
                        $nomorLength = strlen($nomor);
                        
                        // Calculate the number of zeros needed for padding
                        $paddingLength = max(0, 8 - $nomorLength);

                        // Pad $nomor with leading zeros to ensure it has at least 8 digits
                        $nomorPadded = str_repeat('0', $paddingLength) . $nomor;
                        $x =$menu->document_code.$request->year_range.$request->code_place_range.'-'.$nomorPadded; 
                        $query = Journal::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $data = [
                                'title'     => 'Print Journal',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.accounting.journal_individual', $data)->setPaper('a4', 'landscape');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(650, 550, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(650, 560, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;
                           
                        }
                    }
                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }


                    $result = $merger->merge();


                    $randomString = Str::random(10); 

         
                    $filePath = 'public/pdf/' . $randomString . '.pdf';
                    

                    Storage::put($filePath, $result);
                    
                    $document_po = asset(Storage::url($filePath));
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
                        $etNumbersArray = explode(',', $request->tabledata);
                        $query = Journal::where('Code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $data = [
                                'title'     => 'Print Journal',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.accounting.journal_individual', $data)->setPaper('a4', 'landscape');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(650, 550, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(650, 560, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;
                           
                        }
                    }
                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }
    
    
                    $result = $merger->merge();
    
    
                    $randomString = Str::random(10); 

         
                    $filePath = 'public/pdf/' . $randomString . '.pdf';
                    

                    Storage::put($filePath, $result);
                    
                    $document_po = asset(Storage::url($filePath));
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

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';
		return Excel::download(new ExportJournal($post_date,$end_date,$mode), 'journal_'.uniqid().'.xlsx');
    }

    public function printIndividual(Request $request,$id){
        
        $pr = Journal::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');        
        if($pr){
            $data = [
                'title'     => 'Print Capitalization',
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
             
            $pdf = Pdf::loadView('admin.print.accounting.journal_individual', $data)->setPaper('a4', 'landscape');
            $pdf->render();
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(650, 550, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(650, 560, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            $randomString = Str::random(10); 

         
            $filePath = 'public/pdf/' . $randomString . '.pdf';
            

            Storage::put($filePath,$content);
            
            $document_po = asset(Storage::url($filePath));
    
            return $document_po;
        }else{
            abort(404);
        }
    }

    public function approval(Request $request,$id){
        
        $cap = Journal::where('code',CustomHelper::decrypt($id))->first();
                
        if($cap){
            $data = [
                'title'     => 'Approval Journal',
                'data'      => $cap
            ];

            return view('admin.approval.journal', $data);
        }else{
            abort(404);
        }
    }

    public function getImportExcel(){
        return Excel::download(new ExportTemplateJournalCopy(), 'format_journal_copy'.uniqid().'.xlsx');
    }
}