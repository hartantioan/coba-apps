<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\UserBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\RowImportException;
use App\Exports\ExportTemplateUserBrand;
use App\Exports\ExportUserBrand;
use App\Imports\ImportUserBrand;
use App\Models\User;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
class UserBrandController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'UserBrand',
            'content'   => 'admin.master_data.user_brand',
            'company'   => Company::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'employee_no',
            'name',
            'brand_id',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = User::count();

        $query_data = User::whereHas('userBrand', function ($query) {
            $query->where('user_brands.user_id', '!=', DB::raw('users.id'));
        })
        ->where(function ($query) use ($search) {
            $query->where('users.name', 'like', "%$search%")
                  ->orWhere('users.employee_no', 'like', "%$search%");
        })
        ->offset($start)
        ->limit($length)
        ->orderBy($order, $dir)
        ->distinct() // Optional to avoid duplicates
        ->get();


        $total_filtered = User::whereHas('userBrand', function ($query) {
            $query->where('user_brands.user_id', '!=', DB::raw('users.id'));
        })
        ->where(function ($query) use ($search) {
            $query->where('users.name', 'like', "%$search%")
                  ->orWhere('users.employee_no', 'like', "%$search%");
        })
        ->distinct()
        ->count();


        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $response['data'][] = [
                    $nomor,
                    $val->employee_no,
                    $val->name,
                    $val->listUserBrand(),
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . $val->id . '`)"><i class="material-icons dp48">create</i></button>
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

    public function import(Request $request)
    {
        try {
            Excel::import(new ImportUserBrand, $request->file('file'));
            return response()->json(['status' => 200, 'message' => 'Import successful']);
        } catch (RowImportException $e) {
            return response()->json([
                'message' => 'Import failed',
                'error' => $e->getMessage(),
                'row' => $e->getRowNumber(),
                'column' => $e->getColumn(),
                'sheet' => $e->getSheet(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Import failed', 'error' => $e->getMessage()], 400);
        }
    }

    public function getImportExcel(){
        return Excel::download(new ExportTemplateUserBrand(), 'format_template_user_brand'.uniqid().'.xlsx');
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'account_id' 			=> 'required',
            'arr_id_brand'      => 'required',
        ], [
            'account_id.required' 	    => 'User tidak boleh kosong.',
            'arr_id_brand.required'         => 'Brand tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
			if($request->temp){
                DB::beginTransaction();
                $find_user = User::where('id', $request->temp)->first();
                $find_user->userBrand()->delete();
                foreach($request->arr_id_brand as $brand_id){
                    try {
                        $query = UserBrand::create([
                            'user_id'       => session('bo_id'),
                            'account_id'	=> $request->account_id,
                            'brand_id'      => $brand_id,
                        ]);
                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollback();
                    }
                }
			}else{
                DB::beginTransaction();
                foreach($request->arr_id_brand as $brand_id){
                    try {
                        $query = UserBrand::create([
                            'user_id'       => session('bo_id'),
                            'account_id'	=> $request->account_id,
                            'brand_id'      => $brand_id,
                        ]);
                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollback();
                    }
                }


			}

			if($query) {

                activity()
                    ->performedOn(new UserBrand())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit UserBrand.');

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
        $User = User::find($request->id);
        $brand=[];
        foreach($User->userBrand as $rowBrand){
            $brand[] = [
                'id' => $rowBrand->id,
                'name'=> $rowBrand->brand->name,
                'code'=> $rowBrand->brand->code,
            ];
        }
        $User['brand']= $brand;
		return response()->json($User);
    }

    public function destroy(Request $request){
        $query = User::find($request->id);

        if($query->userBrand()->delete()) {
            activity()
                ->performedOn(new UserBrand())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the UserBrand data');

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

    public function export(Request $request){
        $search = $request->search ? $request->search : '';

		return Excel::download(new ExportUserBrand($search), 'user_brand_'.uniqid().'.xlsx');
    }
}
