<?php

namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Allowance;
use Illuminate\Support\Facades\DB;

class AllowanceController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Komponen Gaji',
            'content'   => 'admin.master_data.allowance'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'name',
            'type',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Allowance::count();
        
        $query_data = Allowance::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where('name', 'like', "%$search%");
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = Allowance::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where('name', 'like', "%$search%");
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    $nomor,
                    $val->name,
                    $val->type(),
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
			'name' 				=> 'required',
			'type'			    => 'required',
		], [
			'name.required' 	=> 'Nama menu tidak boleh kosong.',
			'type.required' 	=> 'Tipe tidak boleh kosong.',
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
                    $query = Allowance::find($request->temp);
                    $query->user_id = session('bo_id');
                    $query->name = $request->name;
                    $query->type = $request->type;
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = Allowance::create([
                        'user_id'   => session('bo_id'),
                        'name'		=> $request->name,
                        'type'		=> $request->type,
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

    public function show(Request $request){
        $allowance = Allowance::find($request->id);
		return response()->json($allowance);
    }

    public function destroy(Request $request){
        $query = Allowance::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new Allowance())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the allowance data');

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