<?php

namespace App\Http\Controllers\MasterData;
use App\Exports\ExportResidence;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Residence;
use App\Models\ResidenceDetail;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ResidenceController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Keresidenan',
            'content' => 'admin.master_data.residence'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'note',
            'phone_code',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Residence::count();
        
        $query_data = Residence::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%");
                    });
                }

                if($request->status){
                    $query->where('status',$request->status);
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = Residence::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%");
                    });
                }

                if($request->status){
                    $query->where('status',$request->status);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-id="' . $val->id . '"><i class="material-icons">add</i></button>',
                    $val->employee->name,
                    $val->code,
                    $val->name,
                    $val->note,
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

    public function create(Request $request){
        
        $validation = Validator::make($request->all(), [
            'code'			=> $request->temp ? ['required', Rule::unique('residences', 'code')->ignore($request->temp)] : 'required|unique:residences,code',
            'employee_id'   => 'required',
            'name'          => 'required',
            'arr_region'    => 'required|array'
        ], [
            'code.required' 	    => 'Kode tidak boleh kosong.',
            'code.unique'           => 'Kode telah dipakai',
            'employee_id.required'  => 'Pegawai tidak boleh kosong',
            'name.required'         => 'Nama tidak boleh kosong.',
            'arr_region.required'   => 'Wilayah tidak boleh kosong.',
            'arr_region.array'      => 'Wilayah harus dalam bentuk array.'
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            if($request->arr_region){
                $arr = [];
                foreach($request->arr_region as $row){
                    $cek = ResidenceDetail::where('region_id',$row)->whereHas('residence',function($query) use($request){
                        $query->where('employee_id',$request->employee_id)->where('status','1');
                    })->first();

                    if($cek && !$request->temp){
                        $arr[] = $cek->region->name.' pada no keresidenan '.$cek->residence->code.' oleh pegawai '.$cek->residence->employee->name;
                    }
                }

                if(count($arr) > 0){
                    return response()->json([
                        'status'    => 500,
                        'message'   => 'Wilayah telah terdaftar : '.implode(', ',$arr)
                    ]);
                }
                
            }

			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = Residence::find($request->temp);
                    $query->employee_id = $request->employee_id;
                    $query->code        = $request->code;
                    $query->name        = $request->name;
                    $query->note        = $request->note;
                    $query->status      = $request->status ? $request->status : '2';
                    $query->save();

                    $query->residenceDetail()->delete();
                    
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = Residence::create([
                        'employee_id'   => $request->employee_id,
                        'code'          => $request->code,
                        'name'			=> $request->name,
                        'note'          => $request->note,
                        'status'        => $request->status ? $request->status : '2'
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                if($request->arr_region){
                    foreach($request->arr_region as $row){
                        ResidenceDetail::create([
                            'residence_id'      => $query->id,
                            'region_id'         => $row
                        ]);
                    }
                }

                activity()
                    ->performedOn(new Residence())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit residence.');

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
        $residence = Residence::find($request->id);
        $residence['employee_name'] = $residence->employee->name;

        $details = [];

        foreach($residence->residenceDetail as $row){
            $details[] = [
                'region_id'     => $row->region_id,
                'region_name'   => $row->region->code.' - '.$row->region->name
            ];
        }

        $residence['details'] = $details;
        				
		return response()->json($residence);
    }

    public function rowDetail(Request $request)
    {
        $data   = Residence::find($request->id);
        
        $string = '<div class="row pt-1 pb-1 lime lighten-4"><div class="col s12"><table style="max-width:500px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="2">Daftar Cakupan Wilayah</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Wilayah</th>
                            </tr>
                        </thead><tbody>';
        
        if(count($data->residenceDetail) > 0){
            foreach($data->residenceDetail as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td>'.$row->region->code.' - '.$row->region->name.'</td>
                </tr>';
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="2">Data item tidak ditemukan.</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function destroy(Request $request){
        $query = Residence::find($request->id);
		
        if($query->delete()) {

            $query->residenceDetail()->delete();

            activity()
                ->performedOn(new Residence())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the residence data');

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

    public function print(Request $request){

        $data = [
            'title' => 'RESIDENCE REPORT',
            'data' => Residence::where(function ($query) use ($request) {
                if ($request->search) {
                    $query->where(function($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('name', 'like', "%$request->search%");
                    });
                }

                if($request->status){
                    $query->where('status',$request->status);
                }
            })->get()
		];
		
		return view('admin.print.master_data.residence', $data);
    }

    public function export(Request $request){
        $search = $request->search ? $request->search : '';
        $status = $request->status ? $request->status : '';
		
		return Excel::download(new ExportResidence($search,$status), 'residence_'.uniqid().'.xlsx');
    }
}