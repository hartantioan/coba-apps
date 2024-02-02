<?php

namespace App\Http\Controllers\Usage;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\AttachmentMaintenanceHardwareItemsUsage;
use App\Models\AttachmentRequestRepairHardwareItemsUsage;
use App\Models\Company;
use App\Models\MaintenanceHardwareItemsUsage;
use App\Models\RequestRepairHardwareItemsUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MaintenanceHardwareItemUsageController extends Controller
{
    public function index(Request $request)
    {
        $data = [
            'title'         => 'Maintenance Hardware Item',
            'content'       => 'admin.usage.maintenance_hardware_items_usages',
            'company'       => Company::where('status','1')->get(),
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatableRequest(Request $request){
        $column = [
            'code',
            'hardware_item_id',
            'complaint',
            'post_date',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = RequestRepairHardwareItemsUsage::count();
        
        $query_data = RequestRepairHardwareItemsUsage::where(function($query) use ($search, $request) {
            $query->where('status', 2);
            if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhere('code', 'like', "%$search%");
                    });
                }
            })
            ->doesntHave('maintenanceHardwareItem')
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = RequestRepairHardwareItemsUsage::where(function($query) use ($search, $request) {
            $query->where('status', 2);
            if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhere('code', 'like', "%$search%");
                    });
                }

            })
            ->doesntHave('maintenanceHardwareItem')
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    $nomor,
                    $val->code,
                    $val->hardwareItem->item->name,
                    $val->complaint,
                    $val->post_date,
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue white-text btn-small" data-popup="tooltip" title="Repair" onclick="show_request(`' . CustomHelper::encrypt($val->id)   . '`)"><i class="material-icons dp48">assignment_returned</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Attachment" onclick="show(`' . CustomHelper::encrypt($val->id)  . '`)"><i class="material-icons dp48">photo</i></button>
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

    public function voidStatus(Request $request){
        $query = MaintenanceHardwareItemsUsage::where('id',CustomHelper::decrypt($request->id))->first();
        
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
                    ->performedOn(new MaintenanceHardwareItemsUsage())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the Request Repair Hardware data');
    
                CustomHelper::sendNotification('maintenance_hardware_items_usages',$query->id,'Mintenance Hardware '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('maintenance_hardware_items_usages',$query->id);

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
    public function datatable(Request $request){
        $column = [
            'code',
            'request_repair_hardware_items_usage_id',
            'start_date',
            'end_date',
            'solution',
            'status',
            'user_id',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = MaintenanceHardwareItemsUsage::count();
        
        $query_data = MaintenanceHardwareItemsUsage::where(function($query) use ($search, $request) {
            $query->where('status', 2);
            if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhere('code', 'like', "%$search%");
                    });
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = MaintenanceHardwareItemsUsage::where(function($query) use ($search, $request) {
            $query->where('status', 2);
            if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhere('code', 'like', "%$search%");
                    });
                }

            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    $nomor,
                    $val->code,
                    $val->requestRepairHardwareItemUsage->hardwareItem->item->name,
                    $val->requestRepairHardwareItemUsage->user->name,
                    $val->start_date,
                    $val->end_date,
                    $val->user->name,
                    $val->solution,
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light yellow white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->id)  . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light black accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' .  CustomHelper::encrypt($val->id) . '`)"><i class="material-icons dp48">close</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
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
            'solution'              => 'required',
            'start_date'        => 'required',
            'end_date'          => 'required'
        ], [
            'solution.required' 	    => 'Solusi tidak boleh kosong.',
            'start_date.required'       => 'Tanggal mulai tidak boleh kosong.',
            'end_date.required'         => 'Tanggal akhir tidak boleh kosong.',
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
                    $query = MaintenanceHardwareItemsUsage::find(CustomHelper::decrypt($request->temp));
                    $query->start_date	                        = $request->start_date;
                    $query->end_date	                        = $request->end_date;
                    $query->solution	                        = $request->solution;
                    $query->status	                            = '2';
                    $query->save();

                    if($query->attachmentMaintenanceHardwareItemsUsage()->exists()){
                        foreach($query->attachmentMaintenanceHardwareItemsUsage as $row){
                        
                            $row->delete();
                        }
                    }



                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = MaintenanceHardwareItemsUsage::create([
                        'code'                              => MaintenanceHardwareItemsUsage::generateCode(),
                        'user_id'	                        => session('bo_id'),
                        'request_repair_hardware_items_usage_id'   => CustomHelper::decrypt($request->temp_request),
                        'start_date'			            => $request->start_date,
                        'end_date'			                => $request->end_date,
                        'post_date'                         => date('Y-m-d'),
                        'solution'                          => $request->solution,
                        'status'                            => '2'
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
                           
                            $folderPath = Storage::path('public/maintenance_hw/');
				
                            $image_parts = explode(";base64,", $request->arr_file_path[$key]);
                            $image_type_aux = explode("image/", $image_parts[0]);
                            $image_type = $image_type_aux[1];
                            $image_base64 = base64_decode($image_parts[1]);
                            
                            $newname = Str::random(40).'.'.$image_type;
                            
                            $file = $folderPath.$newname;
                            
                            file_put_contents($file, $image_base64);
                            
                            $image = 'public/maintenance_hw/'.$newname;
                        }else{
                            $image = $request->arr_file_path[$key];
                        }
                        

                        try{
                            AttachmentMaintenanceHardwareItemsUsage::create([
                                'maintenance_hardware_item_usage_id'    => $query->id,
                                'file_name'                             => $request->arr_file_name[$key],
                                'path'                                  => $image
                            ]);
                        }catch(\Exception $e){
                            DB::rollback();
                        }

                    }
                    
                }
                $query_request = RequestRepairHardwareItemsUsage::where('id',CustomHelper::decrypt($request->temp_request))->first();
                $query_request->update([
                    'status'    => '3'
                ]);
                CustomHelper::sendApproval('maintenance_hardware_items_usages',$query->id,$query->solution);
                CustomHelper::sendNotification('maintenance_hardware_items_usages',$query->id,'Pengajuan Penyelesaian Maintenance No. '.$query->code,$query->solution,session('bo_id'));
                activity()
                    ->performedOn(new MaintenanceHardwareItemsUsage())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit Maintenance Hardware Item Usage.');

				$response = [
					'status'  => 200,
					'message' => 'Data successfully saved.'
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

    public function show(Request $request){
        Storage::deleteDirectory('public/temp');
        Storage::makeDirectory('public/temp');
        //menghapus temp dan membuat direktori temp
        $Attachments=[];

        $mt = MaintenanceHardwareItemsUsage::where('id',CustomHelper::decrypt($request->id))->first();
        if($mt){
            $mt['user_name'] = $mt->user->name;
            $mt['request'] = $mt->requestRepairHardwareItemUsage->hardwareItem->item->name;
            // mengambil attachment pada req
            $attachment_request = AttachmentMaintenanceHardwareItemsUsage::where('maintenance_hardware_item_usage_id',CustomHelper::decrypt($request->id))->get();
            foreach($attachment_request as $row){
                $file = [
                    'id'=>$row->id,
                    'created_at'=> date('d/m/Y',strtotime($row->created_at)),
                    'file_name'=>$row->file_name,
                    'attachment'=>$row->attachment()
                ];
                $Attachments[]=$file;
            }
            $mt['attachments']=$Attachments;			
            return response()->json($mt);
        }
    }

    public function showRequest(Request $request){
        Storage::deleteDirectory('public/temp');
        Storage::makeDirectory('public/temp');
        //menghapus temp dan membuat direktori temp
        $Attachments=[];

        $rp = RequestRepairHardwareItemsUsage::where('id',CustomHelper::decrypt($request->id))->first();
        $rp['user_name'] = $rp->user->name;
        $rp['item'] = $rp->hardwareItem->item->name;
        // mengambil attachment pada req
        $attachment_request = AttachmentRequestRepairHardwareItemsUsage::where('request_repair_hardware_items_usage_id',$request->id)->get();
        foreach($attachment_request as $row){
            $file = [
                'id'=>$row->id,
                'created_at'=> date('d/m/Y',strtotime($row->created_at)),
                'file_name'=>$row->file_name,
                'attachment'=>$row->attachment()
            ];
            $Attachments[]=$file;
        }
        $rp['attachments']=$Attachments;			
		return response()->json($rp);
    }

    public function deleteAttachment(Request $request){
        $query = AttachmentMaintenanceHardwareItemsUsage::where('id',$request->id)->first();

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
        
        $maintenance = MaintenanceHardwareItemsUsage::where('code',CustomHelper::decrypt($id))->first();
                
        if($maintenance){
            $data = [
                'title'     => 'Maintenance Hardware Items Usages',
                'data'      => $maintenance
            ];

            return view('admin.approval.maintenance_hardware_items_usages', $data);
        }else{
            abort(404);
        }
    }

}
