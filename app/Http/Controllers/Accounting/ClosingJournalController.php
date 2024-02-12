<?php

namespace App\Http\Controllers\Accounting;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Company;
use App\Models\ItemCogs;
use App\Models\JournalDetail;
use App\Models\Place;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\ClosingJournal;
use App\Models\ClosingJournalDetail;
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
use App\Models\LockPeriod;
use App\Models\Menu;
class ClosingJournalController extends Controller
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
            'title'         => 'Tutup Periode Jurnal',
            'content'       => 'admin.accounting.closing_journal',
            'company'       => Company::where('status','1')->get(),
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code,
            'place'         => Place::whereIn('id',$this->dataplaces)->where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = ClosingJournal::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'company_id',
            'date',
            'month',
            'note',
            'grandtotal',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ClosingJournal::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = ClosingJournal::where(function($query) use ($search, $request) {
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

        $total_filtered = ClosingJournal::where(function($query) use ($search, $request) {
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
                    date('F Y',strtotime($val->month)),
                    $val->note,
                    number_format($val->grandtotal,2,',','.'),
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
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
        
        $cek = ClosingJournal::where('company_id',$request->company_id)->where('month',$request->month)->whereIn('status',['1','2','3'])->first();

        if(!$cek){
            $data = JournalDetail::whereHas('coa', function($query) use($request){
                $query->where('company_id',$request->company_id)
                    ->whereRaw("SUBSTRING(code,1,1) IN ('4','5','6','7','8')")
                    ->where('level','5');
            })
            ->whereHas('journal', function($query)use($request){
                $query->whereRaw("post_date like '$request->month%'");
            })
            ->get();

            $arr = [];

            foreach($data as $row){
                $cekIndex = $this->checkArray($arr,$row->coa_id);
                if($cekIndex < 0){
                    $arr[] = [
                        'coa_id'    => $row->coa_id,
                        'coa_code'  => $row->coa->code,
                        'coa_name'  => $row->coa->name,
                        'nominal'   => $row->type == '1' ? $row->nominal : -1 * $row->nominal,
                        'nominal_fc'=> $row->type == '1' ? $row->nominal_fc : -1 * $row->nominal_fc,
                    ];
                }else{
                    $arr[$cekIndex]['nominal'] += $row->type == '1' ? $row->nominal : -1 * $row->nominal;
                    $arr[$cekIndex]['nominal_fc'] += $row->type == '1' ? $row->nominal_fc : -1 * $row->nominal_fc;
                }
            }

            $profitLoss = 0;

            foreach($arr as $row){
                $profitLoss += $row['nominal'];
            }

            $profitLoss = round($profitLoss,2);

            $collection = collect($arr);

            $result = $collection->sortBy('coa_code')->values()->all();

            $coalabarugi = Coa::where('code','300.03.02.01.01')->where('company_id',$request->company_id)->first();

            foreach($result as $key => $row){
                $result[$key]['nominal'] = -1 * round(floatval($result[$key]['nominal']),2);
                $result[$key]['nominal_fc'] = -1 * round(floatval($result[$key]['nominal_fc']),2);
            }

            $result[] = [
                'coa_id'    => $coalabarugi->id,
                'coa_code'  => $coalabarugi->code,
                'coa_name'  => $coalabarugi->name,
                'nominal'   => $profitLoss,
                'nominal_fc'=> $profitLoss,
            ];

            $response = [
                'status'    => 200,
                'message'   => '',
                'result'    => $result
            ];

        }else{
            $response = [
                'status'    => 500,
                'message'   => 'Mohon maaf periode '.date('F Y',strtotime($cek->month)).' untuk perusahaan '.$cek->company->name.' telah ditutup.'
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

        DB::beginTransaction();
        try {
            $validation = Validator::make($request->all(), [
                'code'                      => 'required',
                'code_place_id'             => 'required',
                /* 'code' 				    => $request->temp ? ['required', Rule::unique('closing_journals', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:closing_journals,code', */
                'post_date'			    => 'required',
                'company_id'		    => 'required',
                'month'		            => 'required',
                'note'                  => 'required',
                'arr_coa_id'            => 'required|array',
                'arr_nominal'           => 'required|array',
                'arr_nominal_fc'        => 'required|array',
            ], [
                'code.required' 				    => 'Kode/No tidak boleh kosong.',
             /*    'code.string'                       => 'Kode harus dalam bentuk string.',
                'code.min'                          => 'Kode harus minimal 18 karakter.',
                'code.unique' 				        => 'Kode/No telah dipakai.', */
                'code_place_id.required'            => 'Plant Tidak boleh kosong',
                'post_date.required' 			    => 'Tanggal post tidak boleh kosong.',
                'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
                'month.required' 			        => 'Periode bulan tidak boleh kosong.',
                'note.required' 			        => 'Keterangan / catatan tidak boleh kosong.',
                'arr_coa_id.required'               => 'Coa tidak boleh kosong',
                'arr_coa_id.array'                  => 'Coa harus dalam bentuk array.',
                'arr_nominal.required'              => 'Nominal tidak boleh kosong',
                'arr_nominal.array'                 => 'Nominal harus dalam bentuk array.',
                'arr_nominal_fc.required'           => 'Nominal mata uang asli tidak boleh kosong',
                'arr_nominal_fc.array'              => 'Nominal mata uang asli harus dalam bentuk array.',
            ]);

            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            } else {

                $grandtotal = abs($request->arr_nominal[count($request->arr_nominal)-1]);

                if($grandtotal == 0){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Data tidak bisa disimpan karena nilai laba/rugi adalah 0.',
                    ]);
                }

                $passedClosingLockPeriod = false;

                $datalockperiod = LockPeriod::where('month',$request->month)->where('status_closing','2')->whereIn('status',['2','3'])->first();

                if($datalockperiod){
                    $passedClosingLockPeriod = true;
                }

                if(!$passedClosingLockPeriod){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Kunci / Lock Periode tidak ditemukan, silakan rubah status menjadi tutup.',
                    ]);
                }

                if($request->temp){
                    
                    $query = ClosingJournal::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Penutupan Jurnal telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','6'])){

                        if($request->has('document')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('document')->store('public/closing_journals');
                        } else {
                            $document = $query->document;
                        }

                        $query->code = $request->code;
                        $query->user_id = session('bo_id');
                        $query->company_id = $request->company_id;
                        $query->post_date = $request->post_date;
                        $query->month = $request->month;
                        $query->note = $request->note;
                        $query->document = $document;
                        $query->grandtotal = $grandtotal;
                        $query->status = '1';
                        $query->save();

                        foreach($query->closingJournalDetail as $row){
                            $row->delete();
                        }

                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Status penutupan jurnal sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }else{
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=ClosingJournal::generateCode($menu->document_code.date('y').$request->code_place_id);
                    $query = ClosingJournal::create([
                        'code'			=> $newCode,
                        'user_id'		=> session('bo_id'),
                        'company_id'    => $request->company_id,
                        'post_date'	    => $request->post_date,
                        'month'         => $request->month,
                        'document'      => $request->file('document') ? $request->file('document')->store('public/closing_journals') : NULL,
                        'status'        => '1',
                        'note'          => $request->note,
                        'grandtotal'    => $grandtotal
                    ]);
                }
                
                if($query) {

                    $updateLockPeriod = LockPeriod::where('month',$request->month)->update([
                        'status_closing'    => '3'
                    ]);
                    
                    foreach($request->arr_coa_id as $key => $row){
                        ClosingJournalDetail::create([
                            'closing_journal_id'    => $query->id,
                            'coa_id'                => $row,
                            'type'                  => str_replace(',','.',str_replace('.','',$request->arr_nominal[$key])) >= 0 ? '1' : '2',
                            'nominal'               => abs(str_replace(',','.',str_replace('.','',$request->arr_nominal[$key]))),
                            'nominal_fc'            => abs(str_replace(',','.',str_replace('.','',$request->arr_nominal_fc[$key]))),
                        ]);
                    }

                    CustomHelper::sendApproval($query->getTable(),$query->id,$query->note);
                    CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan penutupan jurnal No. '.$query->code,$query->note,session('bo_id'));

                    activity()
                        ->performedOn(new ClosingJournal())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit closing journal.');

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
            
            return response()->json($response);
        }catch(\Exception $e){
            DB::rollback();
        }
    }

    public function rowDetail(Request $request){
        $data   = ClosingJournal::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Coa</th>
                                <th class="center-align">Debit</th>
                                <th class="center-align">Kredit</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->closingJournalDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td>'.$row->coa->code.' - '.$row->coa->name.'</td>
                <td class="right-align">'.($row->type == '1' ? number_format($row->nominal,2,',','.') : '0,00').'</td>
                <td class="right-align">'.($row->type == '2' ? number_format($row->nominal,2,',','.') : '0,00').'</td>
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

    public function show(Request $request){
        $ret = ClosingJournal::where('code',CustomHelper::decrypt($request->id))->first();
        $ret['code_place_id'] = substr($ret->code,7,2);

        $arr = [];
        
        foreach($ret->closingJournalDetail as $row){
            $arr[] = [
                'coa_id'    => $row->coa_id,
                'coa_code'  => $row->coa->code,
                'coa_name'  => $row->coa->name,
                'nominal'   => $row->type == '1' ? $row->nominal : -1 * $row->nominal,
                'nominal_fc'=> $row->type == '1' ? $row->nominal_fc : -1 * $row->nominal_fc,
            ];
        }

        $ret['details'] = $arr;
        				
		return response()->json($ret);
    }

    public function voidStatus(Request $request){
        $query = ClosingJournal::where('code',CustomHelper::decrypt($request->id))->first();
        
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
                LockPeriod::where('month',$query->month)->update([
                    'status_closing'    => '2'
                ]);

                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new ClosingJournal())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the closing journal data');
    
                CustomHelper::sendNotification($query->getTable(),$query->id,'Penutupan Jurnal No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
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
        $query = ClosingJournal::where('code',CustomHelper::decrypt($request->id))->first();

        /* $approved = false;
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
                'message' => 'Closing Journal sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        } */
        
        if($query->delete()) {

            if(in_array($query->status,['2','3'])){
                CustomHelper::removeJournal($query->getTable(),$query->id);
            }

            LockPeriod::where('month',$query->month)->update([
                'status_closing'    => '2'
            ]);

            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);

            CustomHelper::removeApproval($query->getTable(),$query->id);
            
            foreach($query->closingJournalDetail as $row){
                $row->delete();
            }

            activity()
                ->performedOn(new ClosingJournal())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the closing journal data');

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
                $pr = ClosingJournal::where('code',$row)->first();
                
                if($pr){
                    $data = [
                        'title'     => 'Penutupan Jurnal',
                        'data'      => $pr
                    ];
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $pdf = Pdf::loadView('admin.print.accounting.closing_journal_individual', $data)->setPaper('a5', 'landscape');
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
                        $query = ClosingJournal::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $data = [
                                'title'     => 'Penutupan Journal',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.accounting.closing_journal_individual', $data)->setPaper('a5', 'landscape');
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
                        $query = ClosingJournal::where('code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $data = [
                                'title'     => 'Penutupan Jurnal',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.accounting.closing_journal_individual', $data)->setPaper('a5', 'landscape');
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
		return Excel::download(new ExportClosingJournal($post_date,$end_date,$mode), 'closing_journal_'.uniqid().'.xlsx');
    }

    public function approval(Request $request,$id){
        
        $cap = ClosingJournal::where('code',CustomHelper::decrypt($id))->first();
                
        if($cap){
            $data = [
                'title'     => 'Closing Journal',
                'data'      => $cap
            ];

            return view('admin.approval.closing_journal', $data);
        }else{
            abort(404);
        }
    }

    public function viewJournal(Request $request,$id){
        $query = ClosingJournal::where('code',CustomHelper::decrypt($id))->first();
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
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td>'.$row->coa->code.' - '.$row->coa->name.'</td>
                    <td class="center-align">'.($row->account_id ? $row->account->name : '-').'</td>
                    <td class="center-align">'.($row->place_id ? $row->place->code : '-').'</td>
                    <td class="center-align">'.($row->line_id ? $row->line->name : '-').'</td>
                    <td class="center-align">'.($row->machine_id ? $row->machine->name : '-').'</td>
                    <td class="center-align">'.($row->department_id ? $row->department->name : '-').'</td>
                    <td class="center-align">'.($row->project_id ? $row->project->name : '-').'</td>
                    <td>'.$row->note.'</td>
                    <td>'.$row->note2.'</td>
                    <td class="right-align">'.($row->type == '1' ? number_format($row->nominal_fc,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '2' ? number_format($row->nominal_fc,2,',','.') : '').'</td>
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

    public function printIndividual(Request $request,$id){
        
        $pr = ClosingJournal::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');        
        if($pr){
            $data = [
                'title'     => 'Penutupan Journal',
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
             
            $pdf = Pdf::loadView('admin.print.accounting.closing_journal_individual', $data)->setPaper('a5', 'landscape');
            $pdf->render();
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            
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

    public function checkStock(Request $request){
        $company_id = $request->company_id;
        $month = $request->month;

        $coas = Coa::where('company_id',$company_id)->where('status','1')->where('level','5')->whereRaw("SUBSTRING(code,1,9) IN ('100.01.04','100.01.05')")->get();

        $arr = [];
        $passed = 1;
        foreach($coas as $key => $row){
            $arrError = [];
            $data = $row->journalDetail()->whereHas('journal',function($query)use($month){
                $query->where('post_date','like',"$month%");
            })->get();
            $balance = 0;
            foreach($data as $detail){
                $balance += ($detail->type == '1' ? $detail->nominal : -1 * $detail->nominal);
                if($balance < 0){
                    $arrError[] = [
                        'date'      => date('d/m/Y',strtotime($detail->journal->post_date)),
                        'code'      => $detail->journal->code,
                        'note'      => ($detail->journal->note ? $detail->journal->note : '').' - '.($detail->note ? $detail->note : ''),
                        'balance'   => number_format($balance,2,',','.'),
                    ];
                }
            }
            if($balance < 0){
                $passed = 0;
            }
            $arr[] = [
                'coa_id'    => $row->id,
                'coa_code'  => $row->code,
                'coa_name'  => $row->name,
                'balance'   => number_format($balance,2,',','.'),
                'errors'    => $arrError,
                'passed'    => $passed,
            ];
        }

        if($passed == 1){
            $response = [
                'status'    => 200,
                'message'   => ''
            ];
        }else{
            $response = [
                'status'    => 422,
                'message'   => '',
                'data'      => $arr
            ];
        }
        
        return response()->json($response);
    }

    public function checkCash(Request $request){
        $company_id = $request->company_id;
        $month = $request->month;
        
        $coas = Coa::where('company_id',$company_id)->where('status','1')->where('level','5')->whereRaw("SUBSTRING(code,1,9) = '100.01.01'")->get();

        $arr = [];
        $passed = 1;
        /* foreach($coas as $key => $row){
            $arrError = [];
            $data = $row->journalDetail()->whereHas('journal',function($query)use($month){
                $query->where('post_date','like',"$month%");
            })->get();
            $balance = 0;
            foreach($data as $detail){
                $balance += ($detail->type == '1' ? $detail->nominal : -1 * $detail->nominal);
                if($balance < 0){
                    $arrError[] = [
                        'date'      => date('d/m/Y',strtotime($detail->journal->post_date)),
                        'code'      => $detail->journal->code,
                        'note'      => ($detail->journal->note ? $detail->journal->note : '').' - '.($detail->note ? $detail->note : ''),
                        'balance'   => number_format($balance,2,',','.'),
                    ];
                }
            }
            if($balance < 0){
                $passed = 0;
            }
            $arr[] = [
                'coa_id'    => $row->id,
                'coa_code'  => $row->code,
                'coa_name'  => $row->name,
                'balance'   => number_format($balance,2,',','.'),
                'errors'    => $arrError,
                'passed'    => $passed,
            ];
        } */

        if($passed == 1){
            $response = [
                'status'    => 200,
                'message'   => ''
            ];
        }/* else{
            $response = [
                'status'    => 422,
                'message'   => '',
                'data'      => $arr
            ];
        } */

        return response()->json($response);
    }

    public function checkQty(Request $request){
        $company_id = $request->company_id;
        $month = $request->month;
        
        $itemcogs = ItemCogs::where('company_id',$company_id)->where('date','like',"$month%")->get();

        $arr = [];
        $passed = 1;
        foreach($itemcogs as $key => $row){
            if($row->qty_final < 0){
                $passed = 0;
                $arr[] = [
                    'item_name'         => $row->item->code.' - '.$row->item->name,
                    'place_name'        => $row->place->code,
                    'warehouse_code'    => $row->warehouse->code,
                    'date'              => date('d/m/Y',strtotime($row->date)),
                    'code'              => $row->lookable->code,
                    'note'              => $row->lookable->note ? $row->lookable->note : '',
                    'balance'           => number_format($row->qty_final,2,',','.'),
                ];
            }
        }

        if($passed == 1){
            $response = [
                'status'    => 200,
                'message'   => ''
            ];
        }else{
            $response = [
                'status'    => 422,
                'message'   => '',
                'data'      => $arr
            ];
        }

        return response()->json($response);
    }
}