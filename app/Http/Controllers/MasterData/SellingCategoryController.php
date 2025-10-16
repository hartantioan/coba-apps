<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\SellingCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SellingCategoryController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Kategori Penjualan',
            'content'   => 'admin.master_data.selling_category',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = SellingCategory::count();

        $query_data = SellingCategory::where(function($query) use ($search, $request) {
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

        $total_filtered = SellingCategory::where(function($query) use ($search, $request) {
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
                    $nomor,
                    $val->name,
                    number_format($val->service_charge,2,',','.'),
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
            'name'              => 'required',
        ], [
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
                    $query = SellingCategory::find($request->temp);
                    $query->name	        = $request->name;
                    $query->service_charge = str_replace(',','.',str_replace('.','',$request->service_charge));

                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = SellingCategory::create([
                        'name'           => $request->name,
                        'service_charge' => str_replace(',','.',str_replace('.','',$request->service_charge)),
                        'status'         => $request->status ? $request->status : '2'
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}

			if($query) {

                activity()
                    ->performedOn(new SellingCategory())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit SellingCategory.');

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
        $SellingCategory = SellingCategory::find($request->id);
        $SellingCategory['service_charge'] = number_format($SellingCategory->service_charge,2,',','.');
		return response()->json($SellingCategory);
    }

    public function destroy(Request $request){
        $query = SellingCategory::find($request->id);

        if($query->delete()) {
            activity()
                ->performedOn(new SellingCategory())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the SellingCategory data');

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
