<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\EmployeeRewardPunishmentDetail;
use Illuminate\Http\Request;
use App\Helpers\CustomHelper;
use App\Models\EmployeeRewardPunishment;
use App\Models\Menu;
use App\Models\Place;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
class EmployeeRewardPunishmentController extends Controller
{
    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));
       
        $menu = Menu::where('url', $lastSegment)->first();
        $data = [
            'place'         => Place::where('status','1')->get(),
            'title'         => 'Reward & Punishment',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'newcode'       => $menu->document_code.date('y'),
            'content'       => 'admin.hr.employee_reward_punishment'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function show(Request $request){
        $line = EmployeeRewardPunishment::where('code',CustomHelper::decrypt($request->id))->first();
        $line['period_id'] = $line->attendancePeriod->id;
        $line['period_name'] = $line->attendancePeriod->name;
        $arr = [];
        foreach($line->employeeRewardPunishmentDetail as $key=>$row_detail){
            $arr[] = [
                'uid' => $row_detail->user_id,
                'nik' => $row_detail->user->employee_no,
                'employee_name' => $row_detail->user->name,
                'note'=>$row_detail->note,
                'nominal_total'=>$row_detail->nominal_total,
                'nominal_payment'=>$row_detail->nominal_payment,
                'instalment'=>$row_detail->instalment
            ];
        }
        $line['detail']= $arr;
		return response()->json($line);
    }

    public function datatable(Request $request){
        $column = [
            'code',
            'user_id',
            'type',
            'post_date',
            'period_id',
            'note',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = EmployeeRewardPunishment::count();
        
        $query_data = EmployeeRewardPunishment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->where('name', 'like', "%$search%");
                        })
                        ->orWhereHas('attendancePeriod', function ($query) use ($search) {
                            $query->where('start_date', 'like', "%$search%")
                            ->where('start_date', 'like', "%$search%")
                            ->where('end_date', 'like', "%$search%");
                        });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->type){
                    $query->where('type', $request->type);
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = EmployeeRewardPunishment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->where('name', 'like', "%$search%");
                        })
                        ->orWhereHas('attendancePeriod', function ($query) use ($search) {
                            $query->where('start_date', 'like', "%$search%")
                            ->where('start_date', 'like', "%$search%")
                            ->where('end_date', 'like', "%$search%");
                        });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->type){
                    $query->where('type', $request->type);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $btn = 
                ' <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">delete</i></button>';

                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->id).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name ?? '-',
                    $val->type(),
                    $val->post_date,
                    $val->attendancePeriod->name,
                    $val->note,
                   
                    $val->status(),
                    $btn
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

    public function rowDetail(Request $request)
    {
        $data   = EmployeeRewardPunishmentDetail::where('employee_reward_punishment_id',CustomHelper::decrypt($request->id))->get();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12"><table style="min-width:100%;">
                    <thead>
                        
                            <tr>
                                <th>NIK-User</th>
                                <th>Keterangan</th>
                                <th>Total</th>
                                <th>Cicilan</th>
                                <th>Nominal Pembayaran</th>
                            </tr>
                        
                    </thead>
                    <tbody>
                    ';
                    foreach($data as $key_detail => $row_detail){
                        $string .= '
                        <tr>
                            <td>'.$row_detail->user->employee_no.'-'.$row_detail->user->name.'</td>
                            <td>'.$row_detail->note.'</td>
                            <td>'.$row_detail->nominal_total.'</td>
                            <td>'.$row_detail->instalment.'</td>
                            <td>'.$row_detail->nominal_payment.'</td>
                        </tr>
                        '; 
                    }
            $string .= '';
        
        
        $string .= '</tbody></table></div>';
		
        return response()->json($string);
    }

    public function getCode(Request $request){
        $code = EmployeeRewardPunishment::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function create(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'period_id'          => 'required',
            'type'      => 'required',
            'post_date'       => 'required',
            'note'       => 'required',
            'arr_uid'    => 'required',
            'arr_nominal_total'=> 'required',
            'arr_instalment' =>'required',
            'arr_nominal_payment' =>'required',
        ], [
            'period_id.required'            => 'Periode Harus Diisi',
            'type.required'                 => 'Tipe tidak boleh kosong.',
            'post_date.required'            => 'Tanggal Post tidak boleh kosong.',
            'note.required'                 => 'Keterangan tidak boleh kosong.',
            'arr_uid.required'              => 'uid harus dipilih',
            'arr_nominal_total.required'    => 'nominal tidak boleh kosong',
            'arr_instalment.required'       => 'cicilan tidak boleh kosong',
            'arr_nominal_payment.required'  => 'nominal pembayaran tidak boleh kosong',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
    
			if($request->temp){
                DB::beginTransaction();
                
                try{
                    $query = EmployeeRewardPunishment::where('code',CustomHelper::decrypt($request->temp))->first();
                  
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
                            'message' => 'Purchase Order telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','6'])){
                        $query->user_id         = session('bo_id');
                        $query->post_date       = $request->post_date;
                        $query->period_id       = $request->period_id;
               
                        $query->type            = $request->type;
                        $query->note            = $request->note;
                        $query->status            = 1;
                     
                        $query->save();
                        
                        foreach($query->employeeRewardPunishmentDetail as $row){
                            $row->delete();
                        }

                        DB::commit();
                    }
                    else{
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
                    $query = EmployeeRewardPunishment::create([
                        'code'			    => $request->code,
                        'user_id'           => session('bo_id'),
                        'post_date'         => $request->post_date,
                        'period_id'         => $request->period_id,
                        'type'              => $request->type,
                        'note'              => $request->note,
                        'company_id'        => session('bo_company_id'),
                        'status'            => '1',
                        
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                DB::beginTransaction();
               
                    foreach($request->arr_uid as $key => $row_isi_detail){
                        $query_detail_reward_punishment = EmployeeRewardPunishmentDetail::create([
                            'user_id' => $row_isi_detail,
                            'nominal_total' =>str_replace(',','.',str_replace('.','',$request->arr_nominal_total[$key])),
                            'nominal_payment'=>str_replace(',','.',str_replace('.','',$request->arr_nominal_payment[$key])),
                            'instalment'=>$request->arr_instalment[$key],
                            'note'=>$request->arr_note[$key],
                            'employee_reward_punishment_id'=>$query->id,
                        ]);
                    }
                    DB::commit();
                   
                CustomHelper::sendApproval('employee_reward_punishments',$query->id,$query->note);
                CustomHelper::sendNotification('employee_reward_punishments',$query->id,'Pengajuan Employee Reward / Punishment No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new EmployeeRewardPunishment())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit  Employee Reward Punishment.');

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

    public function approval(Request $request,$id){
        
        $pr = EmployeeRewardPunishment::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'approval',
                'data'      => $pr
            ];

            return view('admin.approval.employee_reward_punishment', $data);
        }else{
            abort(404);
        }
    }

    public function destroy(Request $request){
        $query = EmployeeRewardPunishment::where('code',CustomHelper::decrypt($request->id))->first();

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

            $query->employeeRewardPunishmentDetail()->delete();

            CustomHelper::removeApproval('employee_reward_punishments',$query->id);

            activity()
                ->performedOn(new EmployeeRewardPunishment())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the Employee reward / Punishment');

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
