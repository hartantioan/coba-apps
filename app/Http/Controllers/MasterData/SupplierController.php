<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class SupplierController extends Controller
{
    public function index()
    {
        $data = [
            'title'   => 'Supplier',
            'content' => 'admin.master_data.supplier'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'user_id',
            'no_telp',
            'address',
            'group_id',
            'total',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Supplier::count();

        $query_data = Supplier::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('address', 'like', "%$search%")
                            ->orWhere('no_telp', 'like', "%$search%")
                            ->orWhereHas('user', function ($query) use ($search) {
                                    $query->where('name', 'like', "%$search%");
                                }
                            )->orWhereHas('group', function ($query) use ($search) {
                                    $query->where('name', 'like', "%$search%");
                                }
                            );
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

        $total_filtered = Supplier::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('address', 'like', "%$search%")
                            ->orWhere('no_telp', 'like', "%$search%")
                            ->orWhereHas('user', function ($query) use ($search) {
                                    $query->where('name', 'like', "%$search%");
                                }
                            )->orWhereHas('group', function ($query) use ($search) {
                                    $query->where('name', 'like', "%$search%");
                                }
                            );
                    });
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
                    $val->name,
                    $val->address,
                    $val->no_telp,
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
            'code' 				=> 'required',
            'name' 				=> 'required',
            'address'           => 'required',
        ], [
            'code.required' 	    => 'Kode tidak boleh kosong.',
            'name.required' 	    => 'Nama tidak boleh kosong.',
            'address.required'      => 'Alamat tidak boleh kosong.',
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
                    $query          = Supplier::find($request->temp);
                    $query->code    = $request->code;
                    $query->name    = $request->name;
                    $query->address = $request->address;
                    $query->no_telp = $request->no_telp;
                    $query->status  = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = Supplier::create([
                        'code'          => $request->code,
                        'name'			=> $request->name,
                        'address'	    => $request->address,
                        'no_telp'       => $request->no_telp,
                        'status'        => $request->status ? $request->status : '2'
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}

			if($query) {

                activity()
                    ->performedOn(new Supplier())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit Supplier.');

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
        $Supplier = Supplier::find($request->id);

		return response()->json($Supplier);
    }

    public function destroy(Request $request){
        $query = Supplier::find($request->id);

        if($query->delete()) {
            activity()
                ->performedOn(new Supplier())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the Supplier data');

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


    // public function export(Request $request){
    //     $search = $request->search ? $request->search : '';
	// 	$status = $request->status ? $request->status : '';

	// 	return Excel::download(new ExportSupplier($search,$status), 'Supplier_'.uniqid().'.xlsx');
    // }
}
