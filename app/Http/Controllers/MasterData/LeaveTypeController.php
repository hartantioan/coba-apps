<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $data = [
            'title'         => 'Tipe Ijin',
            'content'       => 'admin.master_data.leave_type',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'code',
            'name',
            'type',
            'shift_count',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = LeaveType::count();
        
        $query_data = LeaveType::where(function($query) use ($search, $request) {
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

        $total_filtered = LeaveType::where(function($query) use ($search, $request) {
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
               
                $btn = 
                '
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
                ';
                      
                $response['data'][] = [
                    $nomor,
                    $val->code,
                    $val->name,
                    $val->type(),
                    $val->shift_count,
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

    public function create(Request $request){
        
        $validation = Validator::make($request->all(), [
            'code'			          => $request->temp ? ['required', Rule::unique('leave_types', 'code')->ignore($request->temp)] : 'required|unique:leave_types,code',
            'name'                    => 'required',
            'type'                    => 'required',
            'shift_count'             => 'required',
            'furlough_type'           => 'required'
        ], [
            'code.required' 	            => 'Kode tidak boleh kosong.',
            'code.unique'                   => 'Kode telah dipakai',
            'name.required'                 => 'Nama tidak boleh kosong.',
            'type.required'                 => 'Tipe Tidak Boleh kosong',
            'shift_count.required'          => 'Total day / Shift yang diijinkan tidak boleh kosong',
            'furlough_type.required'        => 'Tipe Cuti Harus dipilih'
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
                    $query = LeaveType::find($request->temp);
                    $query->code                = $request->code;
                    $query->name                = $request->name;
                    $query->type                = $request->type;
                    $query->shift_count         = $request->shift_count;
                    $query->status              = $request->status ? $request->status : '1';
                    $query->furlough_type       = $request->furlough_type;
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
               
                try {
                    $query = LeaveType::create([
                        'code'                  => $request->code,
                        'name'			        => $request->name,
                        'type'                  => $request->type,
                        'shift_count'           => $request->shift_count,
                        'furlough_type'         => $request->furlough_type,
                        'status'                => $request->status ? $request->status : '1',
                    ]);
                  
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
        $Level = LeaveType::find($request->id);
        				
		return response()->json($Level);
    }

    public function destroy(Request $request){
        $query = LeaveType::find($request->id);
		
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
}
