<?php

namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Line;
use App\Models\Place;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\CostDistribution;
use App\Models\CostDistributionDetail;
use Illuminate\Support\Facades\DB;

class CostDistributionController extends Controller
{
    public function index()
    {
        $data = [
            'title'         => 'Distribusi Biaya',
            'content'       => 'admin.master_data.cost_distribution',
            'place'         => Place::where('status','1')->get(),
            'line'          => Line::where('status','1')->get(),
            'department'    => Department::where('status','1')->get(),
            'warehouse'     => Warehouse::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = CostDistribution::count();
        
        $query_data = CostDistribution::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
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

        $total_filtered = CostDistribution::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
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
                    '<button class="btn-floating green btn-small" data-id="' . $val->id . '"><i class="material-icons">add</i></button>',
                    $val->code,
                    $val->name,
                    $val->status(),
                    '
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

    public function rowDetail(Request $request){
        
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'code' 				=> $request->temp ? ['required', Rule::unique('cost_distributions', 'code')->ignore($request->temp)] : 'required|unique:cost_distributions,code',
            'name'              => 'required',
            'arr_place.*'       => 'required',
            'arr_percentage.*'  => 'required',
        ], [
            'code.required' 	            => 'Kode tidak boleh kosong.',
            'code.unique'                   => 'Kode telah terpakai.',
            'name.required'                 => 'Nama tidak boleh kosong.',
            'arr_place.*.required'          => 'Plant tidak boleh kosong',
            'arr_percentage.*.required'     => 'Prosentase distribusi tidak boleh kosong',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            DB::beginTransaction();
            try {

                if($request->temp){
                    $query = CostDistribution::find($request->temp);
                    $query->code            = $request->code;
                    $query->name	        = $request->name;
                    $query->note            = $request->note;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();

                    $query->costDistributionDetail()->delete();
                }else{
                    $query = CostDistribution::create([
                        'code'          => $request->code,
                        'place_id'      => $request->place_id,
                        'name'			=> $request->name,
                        'note'          => $request->note,
                        'status'        => $request->status ? $request->status : '2'
                    ]);
                }
                
                if($query) {

                    foreach($request->arr_place as $key => $row){
                        CostDistributionDetail::create([
                            'cost_distribution_id'  => $query->id,
                            'place_id'              => $row,
                            'line_id'               => $request->arr_line[$key] ? $request->arr_line[$key] : NULL,
                            'department_id'         => $request->arr_department[$key] ? $request->arr_department[$key] : NULL,
                            'warehouse_id'          => $request->arr_warehouse[$key] ? $request->arr_warehouse[$key] : NULL,
                            'percentage'            => str_replace(',','.',str_replace('.','',$request->arr_percentage[$key])),
                        ]);
                    }

                    activity()
                        ->performedOn(new CostDistribution())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit cost distribution.');

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

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
		}
		
		return response()->json($response);
    }

    public function show(Request $request){
        $cd = CostDistribution::find($request->id);
        
        $details = [];
        foreach($cd->costDistributionDetail as $cdd){
            $details[] = [
                'place_id'      => $cdd->place_id,
                'line_id'       => $cdd->line_id ? $cdd->line_id : '',
                'department_id' => $cdd->department_id ? $cdd->department_id : '',
                'warehouse_id'  => $cdd->warehouse_id ? $cdd->warehouse_id : '',
                'percentage'    => number_format($cdd->percentage,2,',','.'),
            ];
        }

        $cd['details'] = $details;
        				
		return response()->json($cd);
    }

    public function destroy(Request $request){
        $query = CostDistribution::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new CostDistribution())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the cost distribution data');

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