<?php

namespace App\Http\Controllers\maintenance;

use App\Exports\ExportRequestSparepart;
use App\Http\Controllers\Controller;
use App\Helpers\CustomHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\User;
use App\Models\RequestSparepart;
use App\Models\RequestSparepartDetail;
use App\Models\EquipmentSparepart;
use App\Models\WorkOrder;
use App\Models\Place;
use App\Models\Area;
use App\Models\Activity;

class RequestSparepartController extends Controller
{
    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
    }

    public function index(Request $request)
    {
        $data = [
            'title'         => 'Sparepart',
            'content'       => 'admin.maintenance.request_sparepart',
            'place'         => Place::where('status','1')->get(),
            'area'          => Area::where('status','1')->get(),
            'activity'      => Activity::where('status','1')->get(),
            'code'      => $request->code ? CustomHelper::decrypt($request->code) : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }
    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'work_order_id',
            'user_id',
            'area',
            'request_date',
            'summary_issue',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = RequestSparepart::count();
        
        $query_data = RequestSparepart::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%");
                            })
                            ->orWhereHas('workOrder',function($query) use ($search, $request){
                                $query->where('code','like',"%$search%");
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

        $total_filtered = RequestSparePart::where(function($query) use ($search, $request) {
            if($search) {
                $query->where(function($query) use ($search, $request) {
                    $query->where('code', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%");
                            })
                            ->orWhereHas('workOrder',function($query) use ($search, $request){
                                $query->where('code','like',"%$search%");
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
                    '<button class="btn-floating green btn-small" onclick="rowDetail('.$val->id.',this)"><i class="material-icons">add</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->workOrder->code,
                    $val->workOrder->area->name,
                    $val->request_date,
                    $val->summary_issue,
                    $val->status(),
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat cyan darken-4 white-text btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
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

    public function getWorkOrderInfo(Request $request){
        $data = WorkOrder::find($request->id);
        
        $part_details = [];
        $spare_part = [];
        $equipment_name = $data->equipment->name;
        $user_name = $data->user->name;
        if($data->workOrderPartDetail()->exists()){
            foreach($data->workOrderPartDetail as $work_order_part_detail){
                $part_detail=[
                    "id"   => $work_order_part_detail->equipmentPart->id,
                    "name" => $work_order_part_detail->equipmentPart->name,
                    "code" => $work_order_part_detail->equipmentPart->code,
                ];
                $spareparts=[];
                $equipment_part=$work_order_part_detail->equipmentPart;
                foreach($equipment_part->sparepart as $sparepart_temp){
                    $sparepart=[
                        "id"   => $sparepart_temp->id,
                        'code'          => CustomHelper::encrypt($sparepart_temp->code),
                        'rawcode'       => $sparepart_temp->code,
                        "name" => $sparepart_temp->item->name,
                        "type" => "sparepart",
                        "stock" =>$sparepart_temp->item->currentStock($this->dataplaces),
                    ];
                    $spareparts[]=$sparepart;
                }
                
                $part_detail["sparepart"]=$spareparts;
                $part_details[]=$part_detail;
                
            }
        }
        $data["equipment_name"]=$equipment_name;
        $data["user_name"]=$user_name;
        $data["equipment_part"]=$part_details;
        
        return response()->json($data);
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
			'work_order_id'             =>  'required',
            'request_date'              =>  'required',
            'arr_code'                  =>  'required',
            
		], [
			'work_order_id.required' 			    => 'Work Order tidak boleh kosong.',
            'request_date.required'                 => 'Tanggal Request tidak boleh kosong.',
            'arr_code'                              => 'Request harus memiliki sparepart yang dipilih',
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
                    $query = RequestSparepart::where('code',CustomHelper::decrypt($request->temp))->first();
                    
                    if($query->workOrder->status == 1){
                        if($query->status == '1'){
                       
                            $query->work_order_id = $request->work_order_id;
                           
                            $query->request_date = $request->request_date;
                            
                            $query->summary_issue = $request->note;
                           
                            $query->save();
                          
                            foreach($query->requestSparePartDetail as $row){
                                $row->delete();
                            }
    
                            DB::commit();
                        }
                        elseif($query->status =='2'){
                            $query->work_order_id = $request->work_order_id;
                           
                            $query->request_date = $request->request_date;
                            
                            $query->summary_issue = $request->note;
                           
                            $query->save();
                          
                            foreach($query->requestSparePartDetail as $row){
                                $row->delete();
                            }
    
                            DB::commit();
                        }
                        else{
                            return response()->json([
                                'status'  => 500,
                                'message' => 'Status request sparepart sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                            ]);
                        }
                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Work Order milik request sparepart ini sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan .'
                        ]);
                    }

                    
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    
                    $query = RequestSparepart::create([
                        'code'			            => RequestSparepart::generateCode(),
                        'user_id'		            => session('bo_id'),
                        'work_order_id'             => $request->work_order_id,
                        'request_date'              => $request->request_date,
                        'summary_issue'             => $request->note,
                        'status'                    => '1',
                    ]);
                    
                 DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                
                if($request->arr_code){
                    DB::beginTransaction();
                    
                    try {
                        foreach($request->arr_code as $key => $row){
                            $code = CustomHelper::decrypt($request->arr_code[$key]);
                            
                           
                                
                                
                                $idDetail = EquipmentSparepart::where('code',$code)->first()->id;
                                info($request->arr_stock[$key]);
                            
                                
                            
                                
                                RequestSparepartDetail::create([
                                    'request_sparepart_id'          => $query->id,
                                    'equipment_sparepart_id'        => $idDetail,
                                    'item_stock_id'                 => $request->arr_stock[$key],
                                    'qty_request'                   => $request->arr_qty_req[$key],
                                    'qty_usage'                     => $request->arr_qty_usage[$key],
                                    'qty_return'                    => $request->arr_qty_return[$key],
                                    'qty_repair'                    => $request->arr_qty_repair[$key],
                                ]);
                                
                                DB::commit(); 
                            
                        }
                    }catch(\Exception $e){
                        DB::rollback();
                    }
                }
                CustomHelper::sendApproval('request_spareparts',$query->id,$query->summary_issue);
                CustomHelper::sendNotification('request_spareparts',$query->id,'Pengajuan Request Sparepart No. '.$query->code,$query->summary_issue,session('bo_id'));
                activity()
                    ->performedOn(new RequestSparepart())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit Request Sparepart ');

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
        $request_sp = RequestSparepart::where('code',CustomHelper::decrypt($request->id))->first();
        $request_sp['user_name'] = $request_sp->user->name;
        $request_sp['equipment_name'] = $request_sp->workOrder->equipment->name;
        $request_sp['work_order_code'] = $request_sp->workOrder->code;
        $equipment_parts=[];
        
        $request_sp_details=[];
        foreach($request_sp->workOrder->workOrderPartDetail as $row){
            $equipment_part = [
                'code'          =>$row->equipmentPart->code,
                'name'          =>$row->equipmentPart->name,
            ];
            $spareparts=[];
            foreach($row->equipmentPart->sparepart as $sparepart_temp){
                $sparepart=[
                    "id"            => $sparepart_temp->id,
                    'code'          => CustomHelper::encrypt($sparepart_temp->code),
                    'rawcode'       => $sparepart_temp->code,
                    "name"          => $sparepart_temp->item->name,
                    "stock"         => $sparepart_temp->item->currentStock($this->dataplaces),
                    "type"          => "sparepart",
                ];
                $spareparts[]=$sparepart;
            }
            $equipment_part["sparepart"]=$spareparts;
            $equipment_parts[]=$equipment_part;
        }
        
        foreach($request_sp->requestSparePartDetail as $row){
            $request_sp_detail=[
                'request_sparepart_id' =>$row->request_sparepart_id,
                'equipment_sparepart_id'=>$row->equipment_sparepart_id,
                'stock' =>[
                    "id"            => $row->itemStock->id,
                    'qty'           => number_format($row->itemStock->qty,3,',','.').' '.$row->itemStock->item->uomUnit->code,
                    'warehouse'     => $row->itemStock->warehouse->name,
                ],
                'qty_request'=>$row->qty_request,
                'qty_usage'=>$row->qty_usage,
                'qty_return'=>$row->qty_return,
                'qty_repair'=>$row->qty_repair,
            ];
            $request_sp_details[]=$request_sp_detail;
            
        }


        $request_sp['equipment_part'] = $equipment_parts;
        $request_sp['request_sp_detail'] = $request_sp_details;				
		return response()->json($request_sp);
    }

    public function voidStatus(Request $request){
        $query = RequestSparepart::where('code',CustomHelper::decrypt($request->id))->first();
        
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
                    ->performedOn(new RequestSparepart())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the RequestSparepart');
    
                CustomHelper::sendNotification('request_spareparts',$query->id,'Request Sparepart  No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('request_spareparts',$query->id);

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
        $query = RequestSparepart::where('code',CustomHelper::decrypt($request->id))->first();

        if($query->approval()){
            foreach($query->approval()->approvalMatrix as $row){
                if($row->status == '2'){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Request Sparepart telah diapprove / sudah dalam progres, anda tidak bisa melakukan perubahan.'
                    ]);
                }
            }
        }

        if(in_array($query->status,['2','3','4','5'])){
            return response()->json([
                'status'  => 500,
                'message' => 'Request Sparepart sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            CustomHelper::removeApproval('request_spareparts',$query->id);

            $query->requestSparePartDetail()->delete();

            activity()
                ->performedOn(new RequestSparepart())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the request_spareparts Data');

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
        $data   = RequestSparepart::find($request->id);
        
        $string = '<div class="row pt-1 pb-1 lime lighten-4"><div class="col s12"><table style="min-width:50%;max-width:70%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="6">Daftar Sparepart</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Qty Request</th>
                                <th class="center-align">Qty Usage</th>
                                <th class="center-align">Qty Return</th>
                                <th class="center-align">Qty Repair</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->requestSparePartDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->equipmentSparepart->item->name.'</td>
                <td class="center-align">'.$row->qty_request.'</td>
                <td class="center-align">'.$row->qty_usage.'</td>
                <td class="center-align">'.$row->qty_return.'</td>
                <td class="center-align">'.$row->qty_return.'</td>
            </tr>';
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
        
        /* if($data->approval() && $data->approval()->approvalMatrix()->exists()){                
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
        } */

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function approval(Request $request,$id){
        
        $pr = RequestSparepart::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Request Sparepart',
                'data'      => $pr
            ];

            return view('admin.approval.request_sparepart', $data);
        }else{
            abort(404);
        }
    }

    public function print(Request $request){

        $data = [
            'title' => 'REQUEST SPAREPART REPORT',
            'data' => RequestSparepart::where(function ($query) use ($request) {
                if($request->search) {
                    $query->where(function($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('summary_issue', 'like', "%$request->search%")
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
		
		return view('admin.print.maintenance.request_sparepart', $data);
    }

    public function export(Request $request){
		return Excel::download(new ExportRequestSparepart($request->search,$request->status), 'request_sparepart_'.uniqid().'.xlsx');
    }
    
    public function viewStructureTree(Request $request){
        $query = RequestSparepart::where('code',CustomHelper::decrypt($request->id))->first();
        $data_go_chart = [];
        $data_link = [];

        $data_id_wo = [];
        $data_request_spareparts=[];
        $request_sparepart = [
                'key'   => $query->code,
                "name"  => $query->code,
                "color" => "lightblue",
                'properties'=> [
                     ['name'=> "Tanggal : ".date('d/m/y',strtotime($query->request_date))],
                     ['name'=> "Requested By :".$query->user->name]
                  ],
                'url'   =>request()->root()."/admin/maintenance/request_sparepart?code=".CustomHelper::encrypt($query->code),
            ];
        $data_go_chart[]=$request_sparepart;
        $data_request_spareparts[]=$request_sparepart;

        if($query) {
            

            //Pengambilan Main Branch beserta id terkait
           $wo_main = $query->workOrder;
           $data_wo_main = [
                'key'   => $wo_main->code,
                "name"  => $wo_main->code,
            
                'properties'=> [
                    ['name'=> "Tanggal : ".date('d/m/y',strtotime($wo_main->request_date))],
                    ['name'=> "Requested By :".$wo_main->user->name]
                ],
                'url'   =>request()->root()."/admin/maintenance/work_order?code=".CustomHelper::encrypt($wo_main->code),
            ];
           $data_go_chart[]=$data_wo_main;
            $data_link[]=[
                'from'=>$query->code,
                'to'=>$wo_main->code,
            ];
           foreach($wo_main->requestSparepart as $row_requestsp){
                $data_request_spare_tempura = [
                    'key'   => $row_requestsp->code,
                    "name"  => $row_requestsp->code,
                
                    'properties'=> [
                        ['name'=> "Tanggal : ".date('d/m/y',strtotime($row_requestsp->request_date))],
                        ['name'=> "Requested By :".$row_requestsp->user->name]
                    ],
                    'url'   =>request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($row_requestsp->code),
                ];
                $found = false;
                foreach ($data_request_spareparts as $row_sp) {
                    if ($row_sp["key"] == $data_request_spare_tempura["key"]) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $data_go_chart[]=$data_request_spare_tempura;
                    $data_link[]=[
                        'from'=>$row_requestsp->code,
                        'to'=>$wo_main->code,
                    ];
                    $data_request_spareparts[]=$data_request_spare_tempura;
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