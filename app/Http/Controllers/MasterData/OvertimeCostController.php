<?php

namespace App\Http\Controllers\MasterData;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\OvertimeCost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OvertimeCostController extends Controller
{
    public function index()
    {
        $data = [
            'title'         => 'Tarif Upah lembur',
            'content'       => 'admin.master_data.overtime_cost',
            
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'place_id',
            'level_id',
            'nominal',
            'type',
            'start_date',
            'end_date',
            'name',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = OvertimeCost::where('status',1)->count();
        
        $query_data = OvertimeCost::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('name', 'like', "%$search%")
                            ->orWhere('start_date', 'like', "%$search%")
                            ->orWhere('end_date', 'like', "%$search%");
                    });
                }

            })
        
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = OvertimeCost::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('name', 'like', "%$search%")
                            ->orWhere('start_date', 'like', "%$search%")
                            ->orWhere('end_date', 'like', "%$search%");
                    });
                }
                
            })
           
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $url=request()->root()."/admin/hr/employee_transfer?employee_code=".CustomHelper::encrypt($val->id);
				
                $btn = 
                '
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
                ';
                
                $response['data'][] = [
                    $nomor,
                    $val->name,
                    $val->place->name,
                    $val->level->name,
                    $val->type(),
                    $val->nominal,
                    $val->start_date,
                    $val->end_date,
                    
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

    public function destroy(Request $request){
        $query = OvertimeCost::find($request->id);
		
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

        return response()->json($response);
    }
    public function show(Request $request){
        $project = OvertimeCost::find($request->id);
        $project['place_name'] = $project->place->name;
        $project['level_name'] = $project->level->name;			
		return response()->json($project);
    }

    public function create(Request $request){
        
        $validation = Validator::make($request->all(), [
            
            'name'          => 'required',
            'place_id'      => 'required',
            'nominal'       => 'required',
            'level_id'      => 'required',
            'type'          => 'required',
            'start_date'    => 'required',
            'end_date'      => 'required',
            
        ], [
            'name.required' 	        => 'Tanggal tidak boleh kosong.',
            'place_id.required'               => 'Harap Pilih plant',
            'nominal.required'               => 'Nominal tidak boleh kosong',
            'level_id.required'               => 'Jabatan tidak boleh kosong',
            'type.required'               => 'Harap isi Tipe',
            'start_date.required'               => 'Tanggal Mulai Berlakutiidak boleh kosong',
            'end_date.required'               => 'Akhir Tanggal tidak boleh kosogn',
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
                    $query = OvertimeCost::find($request->temp);
                    
                    $query->name                = $request->name;
                    $query->place_id                = $request->place_id;
                    $query->level_id                = $request->level_id;
                    $query->start_date                = $request->start_date;
                    $query->end_date                = $request->end_date;
                    $query->type                = $request->type;
                    $query->nominal                = $request->nominal;
                    $query->status                 = $request->status;
                    $query->save();

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = OvertimeCost::create([
                        'name'              => $request->name,
                        'place_id'			    => $request->place_id,
                        'level_id'			    => $request->level_id,
                        'start_date'			    => $request->start_date,
                        'end_date'			    => $request->end_date,
                        'type'			    => $request->type,
                        'nominal'			    => $request->nominal,
                        'status'			    => $request->status,
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

             
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
    
}
