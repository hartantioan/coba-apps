<?php

namespace App\Http\Controllers\MasterData;

use App\Exports\ExportStoreCustomer;
use App\Http\Controllers\Controller;
use App\Models\StoreCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class StoreCustomerController extends Controller
{
    public function index()
    {

        $data = [
            'title'     => 'Pelanggan ',
            'content'   => 'admin.master_data.store_customer',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'item_id',
            'item_stock_new_id',
            'qty',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = StoreCustomer::count();

        $query_data = StoreCustomer::where(function($query) use ($search, $request) {
                if ($search) {

                    $query->where('name', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$$search%")
                    ->orWhere('no_telp', 'like', "%$$search%");
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = StoreCustomer::where(function($query) use ($search, $request) {
                if ($search) {
                    $query->where('name', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$$search%")
                    ->orWhere('no_telp', 'like', "%$$search%");
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
                    $val->name ?? '-',
                    $val->no_telp,
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>',
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
            'name'    => 'required',
            'no_telp' => 'required',
        ], [
            'name.required' 	    => 'Nama tidak boleh kosong.',
            'no_telp.required'      => 'No Telepon tidak boleh kosong.',
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
                    $find = StoreCustomer::find($request->temp);
                    $query = $find->update([
                        'name'	=> $request->name,
                        'no_telp'	=> $request->no_telp,
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = StoreCustomer::create([
                        'code' => strtoupper(Str::random(8)),
                        'name'	=> $request->name,
                        'no_telp'	=> $request->no_telp,
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

			}

			if($query) {

                activity()
                    ->performedOn(new StoreCustomer())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit Pelanggan.');

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
        $User = StoreCustomer::find($request->id);


		return response()->json($User);
    }

    public function export(Request $request){
        $search = $request->search ? $request->search : '';

		return Excel::download(new ExportStoreCustomer($search), 'store_item_stock_'.uniqid().'.xlsx');
    }
}
