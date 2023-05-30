<?php

namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;
use App\Models\Line;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Machine;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportMachine;

class MachineController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Mesin',
            'content'   => 'admin.master_data.machine',
            'line'      => Line::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'line_id',
            'name',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Machine::count();
        
        $query_data = Machine::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('line',function($query) use ($search, $request) {
                                $query->where('code', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%")
                                    ->orWhereHas('place',function($query) use ($search, $request) {
                                        $query->where('code', 'like', "%$search%")
                                            ->orWhere('name', 'like', "%$search%");
                                    });
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

        $total_filtered = Machine::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('line',function($query) use ($search, $request) {
                                $query->where('code', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%")
                                    ->orWhereHas('place',function($query) use ($search, $request) {
                                        $query->where('code', 'like', "%$search%")
                                            ->orWhere('name', 'like', "%$search%");
                                    });
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
                    $nomor,
                    $val->code,
                    $val->line->name.' - '.$val->line->place->name,
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
            'code' 				=> $request->temp ? ['required', Rule::unique('machines', 'code')->ignore($request->temp)] : 'required|unique:machines,code',
            'line_id'           => 'required',
            'name'              => 'required',
        ], [
            'code.required' 	    => 'Kode tidak boleh kosong.',
            'code.unique'           => 'Kode telah terpakai.',
            'line_id.required'      => 'Line tidak boleh kosong.',
            'name.required'         => 'Nama tidak boleh kosong.',
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
                    $query = Machine::find($request->temp);
                    $query->code            = $request->code;
                    $query->line_id         = $request->line_id;
                    $query->name	        = $request->name;
                    $query->note            = $request->note;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = Machine::create([
                        'code'          => $request->code,
                        'line_id'       => $request->line_id,
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

                activity()
                    ->performedOn(new Machine())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit machine.');

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
        $line = Machine::find($request->id);
        				
		return response()->json($line);
    }

    public function destroy(Request $request){
        $query = Machine::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new Machine())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the machine data');

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
            'title' => 'MACHINE REPORT',
            'data' => Machine::where(function ($query) use ($request) {
                if ($request->search) {
                    $query->where(function($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('name', 'like', "%$request->search%")
                            ->orWhere('note', 'like', "%$request->search%")
                            ->orWhereHas('line',function($query) use ($request) {
                                $query->where('code', 'like', "%$request->search%")
                                    ->orWhere('name', 'like', "%$request->search%")
                                    ->orWhereHas('place',function($query) use ($request) {
                                        $query->where('code', 'like', "%$request->search%")
                                            ->orWhere('name', 'like', "%$request->search%");
                                    });
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })->get()
		];
		
		return view('admin.print.master_data.machine', $data);
    }

    public function export(Request $request){
        $search = $request->search ? $request->search : '';
		$status = $request->status ? $request->status : '';
		
		return Excel::download(new ExportMachine($search,$status), 'line_'.uniqid().'.xlsx');
    }
}