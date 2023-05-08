<?php

namespace App\Http\Controllers\maintenance;

use App\Exports\ExportWorkOrder;
use App\Http\Controllers\Controller;
use App\Helpers\CustomHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderPartDetail;
use App\Models\WorkOrderPersonInChargeDetail;
use App\Models\WorkOrderAttachmentDetail;
use App\Models\Place;
use App\Models\Area;
use App\Models\Activity;
use App\Models\EquipmentPart;


class WorkOrderController extends Controller
{
    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user->userPlaceArray();
    }

    public function index()
    {
        $data = [
            'title'         => 'Maintenance',
            'content'       => 'admin.maintenance.workorder',
            'place'         => Place::where('status','1')->get(),
            'area'          => Area::where('status','1')->get(),
            'activity'      => Activity::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }
    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'place_id',
            'equipment_id',
            'activity_id',
            'area_id',
            'user_id',
            'maintenance_type',
            'priority',
            'work_order_type',
            'suggested_completion_date',
            'request_date',
            'estimated_fix_time',
            'detail_issue',
            'expected_result', 
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = WorkOrder::count();
        
        $query_data = WorkOrder::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('priority', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%");
                            })
                            ->orWhereHas('place',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%");
                            })
                            ->orWhereHas('area',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = WorkOrder::where(function($query) use ($search, $request) {
            if($search) {
                $query->where(function($query) use ($search, $request) {
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('priority', 'like', "%$search%")
                        ->orWhere('note', 'like', "%$search%")
                        ->orWhereHas('user',function($query) use ($search, $request){
                            $query->where('name','like',"%$search%");
                        })
                        ->orWhereHas('place',function($query) use ($search, $request){
                            $query->where('name','like',"%$search%");
                        })
                        ->orWhereHas('area',function($query) use ($search, $request){
                            $query->where('name','like',"%$search%");
                        });
                });
            }

            if($request->status){
                $query->where('status', $request->status);
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
                    $val->place->name,
                    $val->equipment->name,
                    $val->activity->title,
                    $val->area->name,
                    $val->user->name,
                    $val->maintenance_type,
                    $val->priority,
                    $val->work_order_type,
                    $val->suggested_completion_date,
                    $val->request_date,
                    $val->estimated_fix_time,
                    $val->detail_issue,
                    $val->expected_result,
                    $val->status(),
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light teal lighten-1 white-tex btn-small" data-popup="tooltip" title="Add PIC" onclick="addPIC(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">group_add</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat cyan darken-4 white-text btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
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

    public function getEquipmentPart(Request $request){
        $rows = EquipmentPart::where('equipment_id',$request->id)->where('status','1')->get();
        
        $arrdata = [];

        foreach($rows as $data){
            $equipment_part = [
                'user_id'       => $data->user_id,
                'equipment_id'  =>$data->equipment_id,
                'type'          => 'equipmentpart',
                'code'          => CustomHelper::encrypt($data->code),
                'rawcode'       => $data->code,
                'name'          =>$data->name,
                'specification' =>$data->specification,
                'status'        =>$data->status
            ];
            $arrdata[]=$equipment_part;
        }

        return response()->json($arrdata);
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
			'place_id'                  => 'required',
            'equipment_id'              => 'required',
            'activity_id'               => 'required',
            'area_id'                   => 'required',
            'user_id'                   => 'required',
            'maintenance_type'          => 'required',
            'priority'                  => 'required',
            'work_order_type'           => 'required',
            'suggested_completion_date' => 'required',
            'request_date'              => 'required',
            
		], [
			'place_id.required' 			    => 'Penempatan pabrik/kantor tidak boleh kosong.',
            'equipment_id.required'                 => 'Peralatan tidak boleh kosong.',
            'activity_id.required'            => 'Aktivitas tidak boleh kosong.',
            'area_id.required'                => 'Area tidak boleh kosong.',
            'user_id.required'                 => 'Login tidak boleh kosong.',
            'maintenance_type.required'                 => 'Tanggal bayar tidak boleh kosong.',
            'priority.required'              => 'Prioritas tidak boleh kosong.',
            'work_order_type.required'            => 'Tipe WO tidak boleh kosong.',
            'suggested_completion_date.required'    => 'Tanggal penyelesaian tidak boleh kosong.',
            'request_date.required'               => 'Tanggal permintaan tidak boleh kosong.',
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
                    $query = WorkOrder::where('code',CustomHelper::decrypt($request->temp))->first();
                   
                    

                    if($query->status == '1'){
                        
                        /* if($request->has('document')) {
                            if(Storage::exists($query->document)){
                                Storage::delete($query->document);
                            }
                            $document = $request->file('document')->store('public/payment_requests');
                        } else {
                            $document = $query->document;
                        } */

                        $query->user_id = $request->user_id;
                       
                        $query->area_id = $request->area_id;
                       
                        $query->place_id = $request->place_id;
                        
                        $query->activity_id = $request->activity_id;
                        
                        $query->equipment_id = $request->equipment_id;
                       
                        $query->suggested_completion_date = $request->suggested_completion_date;
                        
                        $query->estimated_fix_time = $request->estimated_fix_time;
                        
                        $query->request_date = $request->request_date;
                        
                        $query->priority = $request->priority;
                       
                        $query->work_order_type = $request->work_order_type;
                        
                        $query->maintenance_type = $request->maintenance_type;
                        $query->detail_issue = $request->note;
                        $query->expected_result = $request->expected_result;
                       
                        $query->save();
                      
                        foreach($query->WorkOrderPartDetail as $row){
                            
                            $row->delete();
                        }
                        foreach($query->workOrderAttachmentDetail as $row){
                            
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
                    $query = WorkOrder::create([
                        'code'			            => WorkOrder::generateCode(),
                        'user_id'		            => session('bo_id'),
                        'place_id'                  => $request->place_id,
                        'equipment_id'              => $request->equipment_id,
                        'activity_id'               => $request->activity_id,
                        'area_id'                   => $request->area_id,
                        'maintenance_type'          => $request->maintenance_type,
                        'priority'                  => $request->priority,
                        'work_order_type'           => $request->work_order_type,
                        'suggested_completion_date' => $request->suggested_completion_date,
                        'request_date'              => $request->request_date,
                        'estimated_fix_time'        => $request->estimated_fix_time,
                        'detail_issue'              => $request->note,
                        'expected_result'           => $request->expected_result,
                        'status'                    => '1',
                    ]);

                 DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                if($request->arr_typefile){
                    
                    foreach($request->arr_typefile as $key => $row){
                        if($request->arr_ada[$key] == 0){
                           
                            $folderPath = Storage::path('public/work_order/');
				
                            $image_parts = explode(";base64,", $request->arr_file_path[$key]);
                            $image_type_aux = explode("image/", $image_parts[0]);
                            $image_type = $image_type_aux[1];
                            $image_base64 = base64_decode($image_parts[1]);
                            
                            $newname = Str::random(40).'.'.$image_type;
                            
                            $file = $folderPath.$newname;
                            
                            file_put_contents($file, $image_base64);
                            
                            $image = 'public/work_order/'.$newname;
                        }else{
                            $image = $request->arr_file_path[$key];
                        }
                        

                        try{
                            WorkOrderAttachmentDetail::create([
                                'work_order_id'                   => $query->id,
                                'file_name'                       => $request->arr_file_name[$key],
                                'path'                            => $image
                            ]);
                        }catch(\Exception $e){
                            info($e);
                            DB::rollback();
                        }

                    }
                    
                }
                if($request->arr_type){
                    DB::beginTransaction();
                    
                    try {
                        foreach($request->arr_type as $key => $row){
                            info($key);
                            info($row);
                            $code = CustomHelper::decrypt($request->arr_code[$key]);
                           
                            if($row == 'equipmentpart'){
                                info($code);
                                try{
                                    $idDetail = EquipmentPart::where('code',$code)->first()->id;
                                } catch(\Exception $e){
                                    info($e);
                                    DB::rollback();
                                }
                                
                                
                            }
                            
                            try {
                                
                                WorkOrderPartDetail::create([
                                    'work_order_id'                 => $query->id,
                                    'part_id'                       => $idDetail,
                                ]);
                                CustomHelper::sendApproval('work_orders',$query->id,$query->note);
                                CustomHelper::sendNotification('work_orders',$query->id,'Pengajuan Request Work Order No. '.$query->code,$query->note,session('bo_id'));
                               
                                DB::commit();
                            } catch(\Exception $e){
                                info($e);
                                DB::rollback();
                            }
                        }
                    }catch(\Exception $e){
                        DB::rollback();
                    }
                }
                activity()
                    ->performedOn(new WorkOrder())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit Work Order ');

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

    public function show(Request $request){
        Storage::deleteDirectory('public/temp');
        Storage::makeDirectory('public/temp');
        //menghapus temp dan membuat direktori temp
        $wo = WorkOrder::where('code',CustomHelper::decrypt($request->id))->first();
        $wo['user_name'] = $wo->user->name;
        $wo['equipment_name'] = $wo->equipment->name;
        $equipment_part=[];
        $work_order_detail_part=[];
        $PIC=[];
        $Attachments=[];

        // mengambil part yang ada pada equipment
        foreach($wo->equipment->equipmentPart as $row){
                $equipment_part[] = [
                    'type'          =>"equipmentpart",
                    'id'            =>$row->id,
                    'user_id'       =>$row->user_id,
                    'equipment_id'  =>$row->equipment_id,
                    'code'          =>CustomHelper::encrypt($row->code),
                    'rawcode'       =>$row->code,
                    'name'          =>$row->name,
                    'specification' =>$row->specification,
                    'status'        =>$row->status
                ];
        }
        
        $wo['equipment_part'] = $equipment_part;
        //mengambil part yang dipilih didalam work order
        foreach($wo->workOrderPartDetail as $row){
            $work_order_detail_part []= [
                'id'=>$row->id,
                'code'=>$row->code,
                'work_order_id'=>$row->work_order_id,
                'part_id'=>$row->part_id
            ];
        }
        // mengambil emplotee yang ditugaskan pada work order
        foreach($wo->workOrderPersonInChargeDetail as $row){
            $employee = [
                'id'=>$row->id,
                'name'=>$row->user->name,
                'work_order_id'=>$row->work_order_id,
                'user_id'=>$row->user_id,
                'pic_id'=>$row->pic_id,
            ];
            $PIC[]=$employee;
        }
        // mengambil attachment pada work_order
        foreach($wo->workOrderAttachmentDetail as $row){
            $file = [
                'id'=>$row->id,
                'path'=>$row->path,
                'file_name'=>$row->file_name,
                'attachment'=>$row->attachment()
            ];
            $Attachments[]=$file;
        }
        $requestSparepart=[];
        foreach($wo->requestSparepart as $key => $row){
            $request_parent = [
                'code'=> $row->code,
                'spareparts'=>[],
            ];
            foreach($row->requestSparePartDetail as $row_sparepart){
                $requested_sparepart=[
                    'name' => $row_sparepart->equipmentSparepart->equipmentPart->name,
                    'qty_request'=> $row_sparepart->qty_request,
                    'qty_repair'=> $row_sparepart->qty_repair,
                    'qty_return'=>$row_sparepart->qty_return,
                    'qty_usage' =>$row_sparepart->qty_usage
                ];
                $request_parent["spareparts"][]=$requested_sparepart;
            }
            $requestSparepart[]=$request_parent;
        }
        $wo['requested_sparepart'] = $requestSparepart;
        $wo['work_order_part_detail']= $work_order_detail_part;

        

        $wo['person_in_charge']=$PIC;	
        $wo['attachments']=$Attachments;			
		return response()->json($wo);
    }

    public function voidStatus(Request $request){
        $query = WorkOrder::where('code',CustomHelper::decrypt($request->id))->first();
        
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
                    ->performedOn(new WorkOrder())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the purchase request data');
    
                CustomHelper::sendNotification('work_orders',$query->id,'Work Order No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('work_orders',$query->id);

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

    public function getDecode(Request $request){
        $folderPath = Storage::path('public/temp/');
				
        $image_parts = explode(";base64,", $request->base64);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        
        $newname = Str::random(40).'.'.$image_type;
        
        $file = $folderPath.$newname;
        
        file_put_contents($file, $image_base64);
        
        $image =asset(Storage::url('public/temp/'.$newname));

        return response()->json([
            'status'  => 200,
            'result' => $image
        ]);
       
    }

    public function destroy(Request $request){
        $query = WorkOrder::where('code',CustomHelper::decrypt($request->id))->first();

        if($query->approval()){
            foreach($query->approval()->approvalMatrix as $row){
                if($row->status == '2'){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Work Order telah diapprove / sudah dalam progres, anda tidak bisa melakukan perubahan.'
                    ]);
                }
            }
        }

        if(in_array($query->status,['2','3','4','5'])){
            return response()->json([
                'status'  => 500,
                'message' => 'Work Order sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {


            CustomHelper::removeApproval('work_order',$query->id);

            $query->WorkOrderPartDetail()->delete();

            activity()
                ->performedOn(new WorkOrder())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the Work Order Data');

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

    public function rowDetail(Request $request)
    {
        $data   = WorkOrder::find($request->id);
        
        $string = '<div class="row pt-1 pb-1 lime lighten-4"><div class="col s12"><table style="min-width:50%;max-width:70%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="6">Daftar Equipment Part</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->workOrderPartDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->equipmentPart->name.'</td>
            </tr>';
        }
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:50%;max-width:70%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="5">Request Sparepart</th>
                            </tr>
                            <tr>
                                <th class="center-align">Nama Item</th>
                                <th class="center-align">Qty Request</th>
                                <th class="center-align">Qty Repair</th>
                                <th class="center-align">Qty Return</th>
                                <th class="center-align">Qty Usage</th>
                            </tr>
                        </thead><tbody>';
        foreach($data->requestSparepart as $key => $row){
            foreach($row->requestSparePartDetail as $row_sparepart){
                info($row_sparepart->equipmentSparepart);
                $string .= '<tr>
                <td class="center-align">'.$row_sparepart->equipmentSparepart->equipmentPart->name.'-'.$row_sparepart->equipmentSparepart->item->name.'</td>
                <td class="center-align">'.$row_sparepart->qty_request.'</td>
                <td class="center-align">'.$row_sparepart->qty_repair.'</td>
                <td class="center-align">'.$row_sparepart->qty_return.'</td>
                <td class="center-align">'.$row_sparepart->qty_usage.'</td>
            </tr>';
            }
        }
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:50%;max-width:70%;">
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
    
    public function getPIC(Request $request){
        $rows = User::where('id',$request->id)->where('status','1')->get();

        return response()->json($rows);
    }

    public function saveUser(Request $request){
        
        $validation = Validator::make($request->all(), [
			'arr_id'                  => 'required',
		], [
			'arr_id.required' 	    => 'Setidaknya harus memiliki 1 user dalam PIC form.',
		]);
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        }else{
            if($request->temp_wo){
                
                $query = WorkOrder::where('code',CustomHelper::decrypt($request->temp_wo))->first();

                foreach($query->workOrderPersonInChargeDetail as $row){
                    $row->delete();
                }

                
                foreach($request->arr_types as $key => $row){
                    if($row == 'pic'){ 
                        try {
                            WorkOrderPersonInChargeDetail::create([
                                'work_order_id'                 =>  $query->id,
                                'user_id'                       =>  session('bo_id'),
                                'pic_id'                        =>  $request->arr_id[$key],
                                'status'                        =>  '1',
                            ]);
                            DB::commit();
                        } catch(\Exception $e){
                            info($e);
                            DB::rollback();
                        }
                    }
                    
                    
                    
                }
                $response = [
                    'status'    => 200,
                    'message'   => 'Data successfully saved.',
                ];
            }else{
                $response = [
                    'status'    => 500,
                    'message'   => 'Data failed to save.',
                ];
            }
            
        }
        
        return response()->json($response);
    }

    public function deleteAttachment(Request $request){
        $query = WorkOrderAttachmentDetail::where('id',$request->id)->first();

        $path = $query->path;
        $deleted = Storage::delete($path);
        if ($deleted) {
            if($query->delete()) {
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
        } else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }
        return response()->json($response);
    }

    public function approval(Request $request,$id){
        
        $pr = WorkOrder::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Work Order',
                'data'      => $pr
            ];

            return view('admin.approval.work_order', $data);
        }else{
            abort(404);
        }
    }

    public function print(Request $request){

        $data = [
            'title' => 'WORK ORDER REPORT',
            'data' => WorkOrder::where(function ($query) use ($request) {
                if($request->search) {
                    $query->where(function($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('priority', 'like', "%$request->search%")
                            ->orWhere('detail_issue', 'like', "%$request->search%")
                            ->orWhereHas('user',function($query) use ($request){
                                $query->where('name','like',"%$request->search%")
                                    ->orWhere('employee_no','like',"%$request->search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->get()
		];
		
		return view('admin.print.maintenance.work_order', $data);
    }

    public function export(Request $request){
		return Excel::download(new ExportWorkOrder($request->search,$request->status), 'work_order'.uniqid().'.xlsx');
    }

    public function viewStructureTree(Request $request){
        $query = WorkOrder::where('code',CustomHelper::decrypt($request->id))->first();
        $data_go_chart = [];
        $data_link = [];

        $data_id_wo = [];

        $work_order_main = [
                'key'   => $query->code,
                "name"  => $query->code,
                "color" => "lightblue",
                'properties'=> [
                     ['name'=> "Tanggal : ".date('d/m/y',strtotime($query->request_date))],
                     ['name'=> "Requested By :".$query->user->name]
                  ],
                'url'   =>request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($query->code),
            ];
        $data_go_chart[]=$work_order_main;
        if($query) {

            //Pengambilan Main Branch beserta id terkait
           if($query->requestSparepart()->exists()){
            foreach($query->requestSparepart as $row_requestsp){
                $data_request_spare_tempura = [
                    'key'   => $row_requestsp->code,
                    "name"  => $row_requestsp->code,
                  
                    'properties'=> [
                        ['name'=> "Tanggal : ".date('d/m/y',strtotime($row_requestsp->request_date))],
                        ['name'=> "Requested By :".$row_requestsp->user->name]
                    ],
                    'url'   =>request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($row_requestsp->code),
                ];
                $data_go_chart[]=$data_request_spare_tempura;
                $data_link[]=[
                    'from'=>$row_requestsp->code,
                    'to'=>$query->code,
                ];
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
