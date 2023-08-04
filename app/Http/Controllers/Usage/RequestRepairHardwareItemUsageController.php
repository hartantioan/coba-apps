<?php

namespace App\Http\Controllers\Usage;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\AttachmentRequestRepairHardwareItemsUsage;
use App\Models\Company;
use App\Models\RequestRepairHardwareItemsUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RequestRepairHardwareItemUsageController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Permintaan Perbaikan Hardware Item',
            'content'   => 'admin.usage.request_repair_hardware_items_usages',
            'company'   => Company::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
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
            $query->where('user_id',session('bo_id'));
            
            if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhere('code', 'like', "%$search%");
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

        $total_filtered = RequestRepairHardwareItemsUsage::where(function($query) use ($search, $request) {
            $query->where('user_id',session('bo_id'));    
            if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhere('code', 'like', "%$search%");
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
                    $nomor,
                    $val->code,
                    $val->hardwareItem->item->name,
                    $val->complaint,
                    $val->post_date,
                    $val->status(),
                    '
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Attachment" onclick="show(`' . $val->id  . '`)"><i class="material-icons dp48">photo</i></button>
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' .  CustomHelper::encrypt($val->id) . '`)"><i class="material-icons dp48">close</i></button>
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
			'hardware_item_id'          => 'required',
            'complaint'                 => 'required',
            
		], [
			'hardware_item_id.required' 	    => 'Item tidak boleh kosong.',
            'complaint.required'                => 'Tolong Isi keluhan anda.',
		]);
        if($request->temp){
            
            DB::beginTransaction();
            try {
                $query = RequestRepairHardwareItemsUsage::where('id',$request->temp)->first();
                
             

                if($query->status == '1'){
                    
                    if($query->attachmentRequestRepairHardwareItemsUsage()->exists()){
                        foreach($query->attachmentRequestRepairHardwareItemsUsage as $row){
                        
                            $row->delete();
                        }
                    }
                    DB::commit();
                    if($request->arr_typefile1){
                        
                        foreach($request->arr_typefile1 as $key => $row){
                            if($request->arr_ada[$key] == 0){
                               
                                $folderPath = Storage::path('public/request_repair_hw/');
                    
                                $image_parts = explode(";base64,", $request->arr_file_path[$key]);
                                $image_type_aux = explode("image/", $image_parts[0]);
                                $image_type = $image_type_aux[1];
                                $image_base64 = base64_decode($image_parts[1]);
                                
                                $newname = Str::random(40).'.'.$image_type;
                                
                                $file = $folderPath.$newname;
                                
                                file_put_contents($file, $image_base64);
                                
                                $image = 'public/request_repair_hw/'.$newname;
                            }else{
                                $image = $request->arr_file_path[$key];
                            }
                            
    
                            try{
                                AttachmentRequestRepairHardwareItemsUsage::create([
                                    'request_repair_hardware_items_usage_id'    => $query->id,
                                    'file_name'                                 => $request->arr_file_name[$key],
                                    'path'                                      => $image
                                ]);
                            }catch(\Exception $e){
                                DB::rollback();
                            }
    
                        }
                        
                    }
                    $response = [
                        'status'    => 200,
                        'message'   => 'Data successfully saved.',
                    ];
                }else{
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Status purchase order sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                    ]);
                }
            }catch(\Exception $e){
                info($e);
                DB::rollback();
            }
        }else{
            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            } else {
               
                
                DB::beginTransaction();
                try {
                    $query = RequestRepairHardwareItemsUsage::create([
                        'code'			            => RequestRepairHardwareItemsUsage::generateCode(),
                        'user_id'		            => session('bo_id'),
                        'complaint'                 => $request->complaint,
                        'post_date'                 => $request->post_date,
                        'activity_id'               => $request->activity_id,
                        'hardware_item_id'          => $request->hardware_item_id,
                        'status'                    => '1',
                    ]);
    
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
                
                
                if($query) {
                    if($request->arr_typefile){
                        
                        foreach($request->arr_typefile as $key => $row){
                            if($request->arr_ada[$key] == 0){
                               
                                $folderPath = Storage::path('public/request_repair_hw/');
                    
                                $image_parts = explode(";base64,", $request->arr_file_path[$key]);
                                $image_type_aux = explode("image/", $image_parts[0]);
                                $image_type = $image_type_aux[1];
                                $image_base64 = base64_decode($image_parts[1]);
                                
                                $newname = Str::random(40).'.'.$image_type;
                                
                                $file = $folderPath.$newname;
                                
                                file_put_contents($file, $image_base64);
                                
                                $image = 'public/request_repair_hw/'.$newname;
                            }else{
                                $image = $request->arr_file_path[$key];
                            }
                            
    
                            try{
                                AttachmentRequestRepairHardwareItemsUsage::create([
                                    'request_repair_hardware_id'      => $query->id,
                                    'file_name'                       => $request->arr_file_name[$key],
                                    'path'                            => $image
                                ]);
                            }catch(\Exception $e){
                                DB::rollback();
                            }
    
                        }
                        
                    }
                    CustomHelper::sendApproval('request_repair_hardware_items_usages',$query->id,$query->complaint);
                    CustomHelper::sendNotification('request_repair_hardware_items_usages',$query->id,'Pengajuan Request Repair Hardware Item. '.$query->code,$query->note,session('bo_id'));
                    activity()
                        ->performedOn(new RequestRepairHardwareItemsUsage())
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
        }
        
		
		return response()->json($response);
    }

    public function show(Request $request){
        Storage::deleteDirectory('public/temp');
        Storage::makeDirectory('public/temp');
        //menghapus temp dan membuat direktori temp
        $Attachments=[];
        // mengambil attachment pada req
        $attachment_request = AttachmentRequestRepairHardwareItemsUsage::where('request_repair_hardware_items_usage_id',$request->id)->get();
        foreach($attachment_request as $row){
            $file = [
                'id'=>$row->id,
                'created_at'=> date('d/m/y',strtotime($row->created_at)),
                'file_name'=>$row->file_name,
                'attachment'=>$row->attachment()
            ];
            $Attachments[]=$file;
        }			
		return response()->json($Attachments);
    }

    public function deleteAttachment(Request $request){
        $query = AttachmentRequestRepairHardwareItemsUsage::where('id',$request->id)->first();

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

    public function voidStatus(Request $request){
        $query = RequestRepairHardwareItemsUsage::where('code',CustomHelper::decrypt($request->id))->first();
        
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
                    ->performedOn(new RequestRepairHardwareItemsUsage())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the Request Repair Hardware data');
    
                CustomHelper::sendNotification('request_repair_hardware_items_usages',$query->id,'Request Repair Hardware. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('request_repair_hardware_items_usages',$query->id);

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

    public function approval(Request $request,$id){
        
        $maintenance = RequestRepairHardwareItemsUsage::where('code',CustomHelper::decrypt($id))->first();
                
        if($maintenance){
            $data = [
                'title'     => 'Request Repair Hardware Items Usages',
                'data'      => $maintenance
            ];

            return view('admin.approval.request_repair_hardware_items_usages', $data);
        }else{
            abort(404);
        }
    }
}
