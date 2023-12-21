<?php

namespace App\Http\Controllers\MasterData;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Holiday;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HolidayController extends Controller
{
    public function index()
    {
        $data = [
            'title'         => 'Master Hari Libur',
            'content'       => 'admin.master_data.holiday',
            
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'date',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Holiday::count();
        
        $query_data = Holiday::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('note', 'like', "%$search%")
                            ->orWhere('date', 'like', "%$search%");
                    });
                }

            })
        
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = Holiday::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where(function($query) use ($search, $request) {
                            $query->where('note', 'like', "%$search%")
                                ->orWhere('date', 'like', "%$search%");
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
                $url=request()->root()."/admin/hr/employee_transfer?employee_code=".CustomHelper::encrypt($val->id);
				
                $btn = 
                '
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
                ';
                
                $response['data'][] = [
                    $nomor,
                    $val->date,
                    $val->note,
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
        $query = Holiday::find($request->id);
		
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
        $project = Holiday::find($request->id);
        				
		return response()->json($project);
    }

    public function create(Request $request){
        
        $validation = Validator::make($request->all(), [
            
            'date'      => 'required',
            'note'         => 'required',
            
        ], [
            'date.required' 	        => 'Tanggal tidak boleh kosong.',
            'note.required'               => 'Keterangan tidak boleh kosong',
            

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
                    $query = Holiday::find($request->temp);
                    
                    $query->note                = $request->note;
                    $query->date                = $request->date;
                    $query->save();

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = Holiday::create([
                        'date'              => $request->date,
                        'note'			    => $request->note,
                        
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
