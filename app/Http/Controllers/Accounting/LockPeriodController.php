<?php

namespace App\Http\Controllers\Accounting;

use App\Exports\ExportLockPeriod;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Company;
use App\Models\ItemCogs;
use App\Models\JournalDetail;
use App\Models\Place;
use App\Models\User;
use App\Models\LockPeriod;
use App\Models\LockPeriodDetail;
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
use App\Exports\ExportRetirement;
use App\Helpers\CustomHelper;
use App\Models\Menu;

class LockPeriodController extends Controller
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
            'title'         => 'Kunci Periode',
            'content'       => 'admin.accounting.lock_period',
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
        $code = LockPeriod::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'status_closing',
            'user_id',
            'company_id',
            'month',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = LockPeriod::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = LockPeriod::where(function($query) use ($search, $request) {
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

        $total_filtered = LockPeriod::where(function($query) use ($search, $request) {
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
                $option = '';
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    '
                        <select class="browser-default" onfocus="updatePrevious(this);" onchange="updateSendStatus(`'.CustomHelper::encrypt($val->code).'`,this)" style="width:150px;">
                            <option value="1" '.($val->status_closing == '1' ? 'selected' : '').'>Buka</option>
                            <option value="2" '.($val->status_closing == '2' ? 'selected' : '').'>Tutup</option>
                            <option value="3" '.($val->status_closing == '3' ? 'selected' : '').'>Kunci</option>
                        </select>
                    ',
                    $val->user()->exists() ? $val->user->name : 'Oleh Sistem',
                    $val->company()->exists() ? $val->company->name : '-',
                    date('F Y',strtotime($val->month)),
                    $val->note,
                    $val->status(),
                    /* '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">delete</i></button>
					' */
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

        DB::beginTransaction();
        try {
            $validation = Validator::make($request->all(), [
                'code'                      => 'required',
                'code_place_id'             => 'required',
                /* 'code' 				    => $request->temp ? ['required', Rule::unique('lock_periods', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:lock_periods,code', */
                'post_date'			    => 'required',
                'company_id'		    => 'required',
                'month'		            => 'required',
            ], [
                'code.required' 				    => 'Kode/No tidak boleh kosong.',
                'code_place_id.required'            => 'Plant Tidak boleh kosong',
                'post_date.required' 			    => 'Tanggal post tidak boleh kosong.',
                'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
                'month.required' 			        => 'Periode bulan tidak boleh kosong.',
            ]);

            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            } else {

                $totalClosing = ClosingJournal::where('month',$request->month)->whereIn('status',['2','3'])->count();

                if($totalClosing == 0){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Closing Jurnal / Tutup Periode pada periode '.date('F Y',strtotime($request->month)).' tidak ditemukan atau belum di approve. Silahkan buat pada form Akunting - Tutup Periode.',
                    ]);
                }

                $totalLock = LockPeriod::where('month',$request->month)->whereIn('status',['1','2','3'])->count();

                if($totalLock > 0 && !$request->temp){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Periode '.date('F Y',strtotime($request->month)).' telah ditambahkan ke form Kunci Periode.',
                    ]);
                }

                if($request->temp){
                    
                    $query = LockPeriod::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Kunci Periode telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','6'])){

                        $query->code = $request->code;
                        $query->user_id = session('bo_id');
                        $query->company_id = $request->company_id;
                        $query->post_date = $request->post_date;
                        $query->month = $request->month;
                        $query->note = $request->note;
                        $query->status = '1';
                        $query->save();

                        if($request->arr_user){
                            foreach($query->lockPeriodDetail()->whereNotIn('user_id',$request->arr_user)->get() as $row){
                                $row->delete();
                            }
                        }

                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Status Kunci Periode sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }else{
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=LockPeriod::generateCode($menu->document_code.date('y').$request->code_place_id);
                    $query = LockPeriod::create([
                        'code'			=> $newCode,
                        'user_id'		=> session('bo_id'),
                        'company_id'    => $request->company_id,
                        'post_date'	    => $request->post_date,
                        'month'         => $request->month,
                        'status_closing'=> '3',
                        'status'        => '1',
                        'note'          => $request->note,
                    ]);
                }
                
                if($query) {
                    
                    if($request->arr_user){
                        foreach($request->arr_user as $key => $row){
                            $countCheck = LockPeriodDetail::where('lock_period_id',$query->id)->where('user_id',$row)->count();
                            if($countCheck == 0){
                                LockPeriodDetail::create([
                                    'lock_period_id'        => $query->id,
                                    'user_id'               => $row,
                                ]);
                            }
                        }
                    }

                    CustomHelper::sendApproval($query->getTable(),$query->id,$query->note);
                    CustomHelper::sendNotification($query->getTable(),$query->id,'Tutup Periode No. '.$query->code,$query->note,session('bo_id'));

                    activity()
                        ->performedOn(new LockPeriod())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit lock period.');

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
        $data   = LockPeriod::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s6"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                            <th class="center-align" colspan="2">Pengguna Spesial</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Nama</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->lockPeriodDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td>'.$row->user->employee_no.'-'.$row->user->name.'</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s6 mt-1"><table style="min-width:100%;max-width:100%;">
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
        $ret = LockPeriod::where('code',CustomHelper::decrypt($request->id))->first();
        $ret['code_place_id'] = substr($ret->code,7,2);

        $arr = [];
        
        foreach($ret->lockPeriodDetail as $row){
            $arr[] = [
                'user_id'    => $row->user_id,
                'user_name'  => $row->user->name,
            ];
        }

        $ret['details'] = $arr;
        				
		return response()->json($ret);
    }

    public function voidStatus(Request $request){
        $query = LockPeriod::where('code',CustomHelper::decrypt($request->id))->first();
        
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
    
                activity()
                    ->performedOn(new LockPeriod())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the lock period data');
    
                CustomHelper::sendNotification($query->getTable(),$query->id,'Kunci Periode No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval($query->getTable(),$query->id);

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
        $query = LockPeriod::where('code',CustomHelper::decrypt($request->id))->first();

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
                'message' => 'Kunci Periode sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);

            CustomHelper::removeApproval($query->getTable(),$query->id);
            
            $query->lockPeriodDetail()->delete();
            
            activity()
                ->performedOn(new LockPeriod())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the kunci periode data');

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

    public function approval(Request $request,$id){
        
        $cap = LockPeriod::where('code',CustomHelper::decrypt($id))->first();
                
        if($cap){
            $data = [
                'title'     => 'Kunci Periode',
                'data'      => $cap
            ];

            return view('admin.approval.lock_period', $data);
        }else{
            abort(404);
        }
    }

    public function updateStatus(Request $request){
        $data = LockPeriod::where('code',CustomHelper::decrypt($request->code))->first();
        if($data){

            /* if($request->status == '3'){
                $totalClosing = ClosingJournal::where('month',$data->month)->whereIn('status',['2','3'])->count();

                if($totalClosing == 0){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Closing Jurnal / Tutup Periode pada periode '.date('F Y',strtotime($data->month)).' tidak ditemukan atau belum di approve. Silahkan buat pada form Akunting - Tutup Periode.',
                    ]);
                }
            } */

            $data->update([
                'status_closing'   => $request->status ? $request->status : NULL,
            ]);

            /* if($request->status == '1' || $request->status == '2'){
                $dataClosingJournal = ClosingJournal::where('month',$data->month)->whereIn('status',['1','2','3'])->get();
                if($dataClosingJournal){
                    foreach($dataClosingJournal as $row){
                        $row->update([
                            'status'    => '5',
                            'void_id'   => session('bo_id'),
                            'void_note' => 'Telah ditutup karena perubahan status Kunci Periode No. '.$data->code,
                            'void_date' => date('Y-m-d H:i:s')
                        ]);
                        if($row->journal()->exists()){
                            CustomHelper::removeJournal($row->getTable(),$row->id);
                        }
                    }
                }
            } */

            CustomHelper::sendNotification($data->getTable(),$data->id,'Status Kunci Periode No. '.$data->code.' telah diupdate','Status kunci periode dokumen '.$data->code.' telah di-'.$data->statusClosing().'.',session('bo_id'));

            $response = [
                'status'  => 200,
                'message' => 'Status berhasil dirubah.',
                'value'   => $data->send_status ? $data->send_status : '',
            ];
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Maaf, data tidak ditemukan.',
                'value'   => '',
            ];
        }

        return response()->json($response);
    }

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';
		return Excel::download(new ExportLockPeriod($post_date,$end_date,$mode), 'lock_period_'.uniqid().'.xlsx');
    }
}