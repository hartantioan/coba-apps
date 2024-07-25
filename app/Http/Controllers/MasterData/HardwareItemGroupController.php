<?php

namespace App\Http\Controllers\MasterData;

use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\HardwareItemGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HardwareItemGroupController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Item Hardware Group',
            'content' => 'admin.master_data.hardware_item_group',
            'department'    => Division::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
          
            'status',
            'action'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = HardwareItemGroup::count();
        
        $query_data = HardwareItemGroup::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhere('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                            
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

        $total_filtered = HardwareItemGroup::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhere('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
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
                    $val->id,
                    $val->code,
                    $val->name,
                    $val->status(),
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ');getReception('.$val->id.')"><i class="material-icons dp48">create</i></button>
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

    public function getReception(Request $request){
        $query = HardwareItemGroup::find($request->id);
        $ada=false;
        if($query->hardwareItem()->exists()){
            foreach($query->hardwareItem as $row_hwitem){
                if($row_hwitem->receptionHardwareItemsUsageALL()->exists()){
                    $ada = true;
                }
            }
        }
        
        $response['ada']=$ada;
        return response()->json($response);
    }

    public function show(Request $request){
        $HardwareItemGroup = HardwareItemGroup::find($request->id);  
		return response()->json($HardwareItemGroup);
    }

    public function create(Request $request){
        $query=null;
        if($request->temp){
            $query = HardwareItemGroup::find($request->temp);
        }
        if($query){
            $ada=false;
            if($query->hardwareItem()->exists()){
                foreach($query->hardwareItem as $row_hwitem){
                    if($row_hwitem->receptionHardwareItemsUsageALL()->exists()){
                        $ada = true;
                    }
                }
            }
            if($ada == true){
                $validation = Validator::make($request->all(), [
                
                ], [
                    
                
                ]);
            }else{
                $validation = Validator::make($request->all(), [
                    'code'              => $request->temp ? ['required', Rule::unique('hardware_item_groups', 'code')->ignore($request->temp)] : 'required|unique:hardware_item_groups,code',
                    'name'              => 'required',
                   
                ], [
                    'code.required' 	        => 'Kode tidak boleh kosong.',
                    'code.unique'               => 'Kode telah terpakai.',
                    'name.required'             => 'Nama tidak boleh kosong.',
                ]);
            }
        }else{
            $validation = Validator::make($request->all(), [
                'code'              => $request->temp ? ['required', Rule::unique('hardware_item_groups', 'code')->ignore($request->temp)] : 'required|unique:hardware_item_groups,code',
                'name'              => 'required',
               
            ], [
                'code.required' 	        => 'Kode tidak boleh kosong.',
                'code.unique'               => 'Kode telah terpakai.',
                'name.required'             => 'Nama tidak boleh kosong.',
            ]);
    
        }
        
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = HardwareItemGroup::find($request->temp);
                    $ada = false;
                    if($query->hardwareItem()->exists()){
                        foreach($query->hardwareItem as $row_hwitem){
                            if($row_hwitem->receptionHardwareItemsUsageALL()->exists()){
                                $ada = true;
                            }
                        }
                    }

                    if($ada == true){
                        $query->status          = $request->status ? $request->status : '2';
                        $query->save();
                        DB::commit();
                    }else{
                        $query->code            = $request->code;
                        $query->name	        = $request->name;
                        $query->status          = $request->status ? $request->status : '2';
                        $query->save();
                        DB::commit();
                    }
                    
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = HardwareItemGroup::create([
                        'code'          => $request->code,
                        'name'			=> $request->name,
                        'status'        => $request->status ? $request->status : '2'
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                activity()
                    ->performedOn(new HardwareItemGroup())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit Hardware item Group.');

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

}
