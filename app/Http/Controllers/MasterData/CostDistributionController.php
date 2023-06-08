<?php

namespace App\Http\Controllers\MasterData;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Line;
use App\Models\Machine;
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
            'machine'       => Machine::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'coa_id',
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
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->name,
                    $val->coa_id ? $val->coa->name : '-',
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
        $data   = CostDistribution::where('code',CustomHelper::decrypt($request->id))->first();

        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Plant</th>
                                <th class="center-align">Line</th>
                                <th class="center-align">Mesin</th>
                                <th class="center-align">Departemen</th>
                                <th class="center-align">Gudang</th>
                                <th class="center-align">Prosentase(%)</th>
                            </tr>
                        </thead><tbody>';

        foreach($data->costDistributionDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->place->code.' - '.$row->place->name.'</td>
                <td class="center-align">'.($row->line_id ? $row->line->code.' - '.$row->line->name : '-').'</td>
                <td class="center-align">'.($row->machine_id ? $row->machine->code.' - '.$row->machine->name : '-').'</td>
                <td class="center-align">'.($row->department_id ? $row->department->code.' - '.$row->department->name : '-').'</td>
                <td class="center-align">'.($row->warehouse_id ? $row->warehouse->code.' - '.$row->warehouse->name : '-').'</td>
                <td class="center-align">'.number_format($row->percentage,2,',','.').'</td>
            </tr>';
        }

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
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
                    $query->coa_id          = $request->coa_id ? $request->coa_id : NULL;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();

                    $query->costDistributionDetail()->delete();
                }else{
                    $query = CostDistribution::create([
                        'code'          => $request->code,
                        'place_id'      => $request->place_id,
                        'name'			=> $request->name,
                        'coa_id'        => $request->coa_id ? $request->coa_id : NULL,
                        'status'        => $request->status ? $request->status : '2'
                    ]);
                }
                
                if($query) {

                    foreach($request->arr_place as $key => $row){
                        CostDistributionDetail::create([
                            'cost_distribution_id'  => $query->id,
                            'place_id'              => $row,
                            'line_id'               => $request->arr_line[$key] ? $request->arr_line[$key] : NULL,
                            'machine_id'            => $request->arr_machine[$key] ? $request->arr_machine[$key] : NULL,
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
                'machine_id'    => $cdd->machine_id ? $cdd->machine_id : '',
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
            $query->costDistributionDetail()->delete();

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