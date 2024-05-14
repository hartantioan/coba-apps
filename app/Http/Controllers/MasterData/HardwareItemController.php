<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\HardwareItem;
use App\Models\MaintenanceHardwareItemsUsage;
use App\Models\Place;
use App\Models\ReceptionHardwareItemsUsage;
use App\Models\RequestRepairHardwareItemsUsage;
use App\Models\ReturnHardwareItemsUsage;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
class HardwareItemController extends Controller
{
    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
    }
    public function index()
    {
        $data = [
            'title' => 'Item Hardware',
            'content' => 'admin.master_data.hardware_item',
            'department'    => Department::where('status','1')->get(),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'code',
            'item_id',
            'user_id',
            'hardware_item_group_id',
            'detail1',
            'detail2',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = HardwareItem::count();
        
        $query_data = HardwareItem::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhere('name', 'like', "%$search%")
                            ->orWhere('detail1', 'like', "%$search%")
                            ->orWhere('detail2', 'like', "%$search%");
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

        $total_filtered = HardwareItem::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhere('name', 'like', "%$search%")
                            ->orWhere('detail1', 'like', "%$search%")
                            ->orWhere('detail2', 'like', "%$search%");
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
                    $val->item->name,
                    $val->hardwareItemGroup->code.' - '.$val->hardwareItemGroup->name,
                    $val->detail1,
                    $val->detail2,
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue lighten-3 white-text btn-small" data-popup="tooltip" title="Barcode" onclick="printBarcode(' . $val->id . ')"><i class="material-icons dp48">reorder
                        </i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light brown darken-2 white-text btn-small" data-popup="tooltip" title="History" onclick="historyUsage(' . $val->id . ')"><i class="material-icons dp48">change_history
                        </i></button>
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
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

    public function Edit(Request $request){
        $validation = Validator::make($request->all(), [
            'item_id_edit'               => 'required',
            'item_group_id_edit'         => 'required',
            'detail1_edit'               => 'required',
            'code'                       =>  $request->temp ? ['required', Rule::unique('hardware_items', 'code')->ignore($request->temp)] : 'required|unique:hardware_items,code',
        ], [
            'code.required' 	    => 'Kode tidak boleh kosong.',
            'code.unique'           => 'Kode telah terpakai.',
            'item_id_edit.required'          => 'Harap pilih Item.',
            'detail1_edit.required'      => 'Harap isi detail item',
            'item_group_id_edit.required'    => 'Harap pilih Group item Asset.',
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
                    $query = HardwareItem::find($request->temp);
                    $query->code            = $request->code;
                    $query->item_id	        = $request->item_id_edit;
                    $query->hardware_item_group_id	        = $request->item_group_id_edit;
                    $query->detail1	        = $request->detail1_edit;
                    $query->detail2	        = $request->detail2_edit;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                

                activity()
                    ->performedOn(new HardwareItem())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit Hardware Items.');

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

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'item_id'               => 'required',
            'item_group_id'         => 'required',
            'arr_detail1'           => 'required',
            'arr_code'               =>  $request->temp ? ['required', Rule::unique('hardware_items', 'code')->ignore($request->temp)] : 'required|unique:hardware_items,code',
        ], [
            'arr_code.required' 	    => 'Kode tidak boleh kosong.',
            'arr_code.unique'           => 'Kode telah terpakai.',
            'item_id.required'          => 'Harap pilih Item.',
            'arr_detail1.required'      => 'Harap isi detail item',
            'item_group_id.required'    => 'Harap pilih Group item Asset.',
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
                    $query = HardwareItem::find($request->temp);
                    $query->code            = $request->code;
                    $query->item_id	        = $request->item_id;
                    $query->hardware_item_group_id	        = $request->item_group_id;
                    $query->detail1	        = $request->detail1;
                    $query->detail2	        = $request->detail2;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                foreach($request->arr_code as $key => $row){
                    $query = HardwareItem::create([
                        'code'                      => $row,
                        'item_id'			        => $request->item_id,
                        'user_id'			        => session('bo_id'),
                        'hardware_item_group_id'    => $request->item_group_id,
                        'detail1'			        => $request->arr_detail1[$key],
                        'detail2'			        => $request->arr_detail2[$key],
                        'status'                    => $request->status ? $request->status : '2'
                    ]);
                }
                    

                DB::commit();
                
			}
			
			if($query) {
                

                activity()
                    ->performedOn(new HardwareItem())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit Hardware Items.');

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

    public function show(Request $request){
        $hardwareItem = HardwareItem::find($request->id);
        $hardwareItem['item']=$hardwareItem->item;
        $hardwareItem['user']=$hardwareItem->user;
        $hardwareItem['group_item']=$hardwareItem->hardwareItemGroup;

		return response()->json($hardwareItem);
    }

    public function historyUsage(Request $request){
        $query_reception = ReceptionHardwareItemsUsage::where('hardware_item_id', $request->id)->get();
        $temp_return = [];
        $temp_reception = [];
        $title = '';
        $temp_request = [];
        $temp_mt = [];
        if($query_reception){
            foreach ($query_reception as $reception) {
                // Access and display properties of each reception item
                $temp_data_rec=[
                    'image'     => $reception->user()->exists() ?  $reception->user->profilePicture(): '',
                    'code'      => $reception->code,
                    'date'      => $reception->reception_date,
                    'post_date' => $reception->reception_date,
                    'user'      => $reception->user->name ?? '',
                    'info'      => $reception->info,
                    'action'    =>'Penyerahan'
                ];
                $temp_reception[]=$temp_data_rec;
    
                
                $title='History Usage '.$reception->hardwareItem->item->name;
                // ...
            }
            $query_return = ReceptionHardwareItemsUsage::where('hardware_item_id', $request->id)->where('return_date','!=',null)->get();
    
            foreach($query_return as $return){
                
                $temp_data=[
                    'post_date' => $return->return_date,
                    'code'      => $return->code,
                    'date'      => $return->return_date,
                    'user'      => $return->user->name ?? '',
                    'info'      => $return->return_note,
                    'action'    =>'Pengembalian'
                ];
                $temp_return[]=$temp_data;
            }
            $combined = array_merge($temp_return, $temp_reception);
    
            usort($combined, function($a, $b) {
                return strtotime($a['post_date']) - strtotime($b['post_date']);
            });
            
            $string = '';
            $string1 = '';
            foreach ($combined as $key => $row) {
                if($row['action']=="Penyerahan"){
                    $string .='<li>
                            <div class="timeline-badge blue">
                            <a class="tooltipped" data-position="top" data-tooltip="' . $row['date'] . '"><i class="material-icons white-text">trending_flat</i></a>
                            </div>
                            <div class="timeline-panel">
                            <div class="card m-0 hoverable" id="profile-card" style="overflow: visible;">
                                <div class="card-content">
                                <div style="display:-webkit-box;">
                                    ' . $row['image'] . '
                                    <h5 class="card-title activator grey-text text-darken-4 mt-1 ml-3">' . $row['user'] . '</h5>
                                </div>
                                <p><i class="material-icons profile-card-i">copyright</i>' . $row['code'] . '</p>
                                <p><i class="material-icons profile-card-i">insert_invitation</i>' . $row['date'] . '</p>
                                <p><i class="material-icons profile-card-i">insert_comment</i> ' . $row['info'] . '</p>
                                <p><i class="material-icons profile-card-i">eject</i> ' . $row['action'] . '</p>
                                </div>
                            </div>
                            </div>
                        </li>';
                }else{
                    $string .='<li class="timeline-inverted">
                            <div class="timeline-badge blue">
                            <a class="tooltipped" data-position="top" data-tooltip="' . $row['date'] . '"><i class="material-icons white-text">undo</i></a>
                            </div>
                            <div class="timeline-panel">
                            <div class="card m-0 hoverable" id="profile-card" style="overflow: visible;">
                                <div class="card-content">
                                <p><i class="material-icons profile-card-i">copyright</i>' . $row['code'] . '</p>
                                <p><i class="material-icons profile-card-i">insert_invitation</i>' . $row['date'] . '</p>
                                <p><i class="material-icons profile-card-i">insert_comment</i> ' . $row['info'] . '</p>
                                <p><i class="material-icons profile-card-i">eject</i> ' . $row['action'] . '</p>
                                </div>
                            </div>
                            </div>
                        </li>';
                }
                
                $string1 .= '<tr>
                    <td class="center-align">' . ($key + 1) . '</td>
                    <td>' . $row['code'] . '</td>
                    <td class="center-align">' . $row['date'] . '</td>
                    <td class="center-align">' . $row['user'] . '</td>
                    <td class="center-align">' . $row['info'] . '</td>
                    <td class="center-align">' . $row['action'] . '</td>
                </tr>';
            }
            
            $string.='<li class="clearfix" style="float: none;"></li>';
            $response["tbody"] = $string;
            $response["tbody1"] = $string1;
            $response["title"] = $title;

            $query_request = RequestRepairHardwareItemsUsage::where('hardware_item_id', $request->id)->get();
            foreach($query_request as $request){
                $temp_data=[
                    'image'     => $reception->user()->exists() ?  $reception->user->profilePicture(): '',
                    'code'      => $request->code,
                    'date'      => $request->post_date,
                    'user'      => $request->user->name,
                    'info'      => $return->complaint,
                    'action'    => 'Request Repair'
                ];
                $temp_request[]=$temp_data;

                $query_maintenance = MaintenanceHardwareItemsUsage::where('request_repair_hardware_items_usage_id',$request->id)->get();
                foreach($query_maintenance as $maintenance){
                   
                    $temp_data1=[
                        'image'     => $reception->user()->exists() ?  $reception->user->profilePicture(): '',
                        'code'      => $maintenance->code,
                        'date'      => $maintenance->end_date,
                        'user'      => $maintenance->user->name,
                        'info'      => $maintenance->solution,
                        'action'    => 'Perbaikan'
                    ];
                    $temp_mt[]=$temp_data1;
                }
                $combine_rm = array_merge($temp_request, $temp_mt);
    
                usort($combine_rm, function($a, $b) {
                    return strtotime($a['date']) - strtotime($b['date']);
                });
                $string = '';
                $string1 = '';
                foreach ($combine_rm as $key => $row) {
                    if($row['action']=="Request Repair"){
                    $string .='<li>
                            <div class="timeline-badge deep-purple">
                            <a class="tooltipped" data-position="top" data-tooltip="' . $row['date'] . '"><i class="material-icons white-text">present_to_all</i></a>
                            </div>
                            <div class="timeline-panel">
                            <div class="card m-0 hoverable" id="profile-card" style="overflow: visible;">
                                <div class="card-content">
                                <div style="display:-webkit-box;">
                                    ' . $row['image'] . '
                                    <h5 class="card-title activator grey-text text-darken-4 mt-1 ml-3">' . $row['user'] . '</h5>
                                </div>
                                <p><i class="material-icons profile-card-i">copyright</i>' . $row['code'] . '</p>
                                <p><i class="material-icons profile-card-i">insert_invitation</i>' . $row['date'] . '</p>
                                <p><i class="material-icons profile-card-i">insert_comment</i> ' . $row['info'] . '</p>
                                <p><i class="material-icons profile-card-i">eject</i> ' . $row['action'] . '</p>
                                </div>
                            </div>
                            </div>
                        </li>';
                    }else{
                        $string .='<li class="timeline-inverted">
                            <div class="timeline-badge teal darken-4">
                            <a class="tooltipped" data-position="top" data-tooltip="' . $row['date'] . '"><i class="material-icons white-text">wb_iridescent</i></a>
                            </div>
                            <div class="timeline-panel">
                            <div class="card m-0 hoverable" id="profile-card" style="overflow: visible;">
                                <div class="card-content">
                                <div style="display:-webkit-box;">
                                    ' . $row['image'] . '
                                    <h5 class="card-title activator grey-text text-darken-4 mt-1 ml-3">' . $row['user'] . '</h5>
                                </div>
                                <p><i class="material-icons profile-card-i">copyright</i>' . $row['code'] . '</p>
                                <p><i class="material-icons profile-card-i">insert_invitation</i>' . $row['date'] . '</p>
                                <p><i class="material-icons profile-card-i">insert_comment</i> ' . $row['info'] . '</p>
                                <p><i class="material-icons profile-card-i">eject</i> ' . $row['action'] . '</p>
                                </div>
                            </div>
                            </div>
                        </li>';
                    }
                    
                    
                    $string1 .= '<tr>
                        <td class="center-align">' . ($key + 1) . '</td>
                        <td>' . $row['code'] . '</td>
                        <td class="center-align">' . $row['date'] . '</td>
                        <td class="center-align">' . $row['user'] . '</td>
                        <td class="center-align">' . $row['info'] . '</td>
                        <td class="center-align">' . $row['action'] . '</td>
                    </tr>';
                }
                $string.='<li class="clearfix" style="float: none;"></li>';
                $response["tbodyR"] = $string;
                $response["tbody1R"] = $string1;
            }

        }else{
            $response = [
                'status'  => 500,
                'message' => 'Data Tidak Dapat Diambil.'
            ]; 
        }
        
        return response()->json($response);
        
    }

    public function printBarcode( Request $request){

        $RH = HardwareItem::find($request->id);
        if($RH){
            $data = [
                'data' => $RH,
            ];
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path);
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;

            $pdf = Pdf::loadView('admin.print.usage.hardware_item_barcode', $data);
            $content = $pdf->download()->getOriginalContent();
            $randomString = Str::random(10); 

         
            $filePath = 'public/pdf/' . $randomString . '.pdf';
            

            Storage::put($filePath, $content);
            
            $document_po = asset(Storage::url($filePath));
            $var_link=$document_po;

            return $document_po;
        }else{
            abort(404);
        }

    }
}
