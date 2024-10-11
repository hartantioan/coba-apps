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
            'user_id',
            'account_name',
            'brand_id',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = UserBrand::count();

        $query_data = UserBrand::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhereHas('user', function($querys) use ($search, $request) {
                            $querys->where('employee_no', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                        })->orWhereHas('account',function($query) use($search, $request){
                            $query->where('name','like',"%$search%")
                                ->orWhere('employee_no','like',"%$search%");
                        })->orWhereHas('brand',function($query) use($search, $request){
                            $query->where('name','like',"%$search%")
                                ->orWhere('employee_no','like',"%$search%");
                        });
                    });
                }


            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = UserBrand::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhereHas('user', function($querys) use ($search, $request) {
                            $querys->where('employee_no', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                        })->orWhereHas('account',function($query) use($search, $request){
                            $query->where('name','like',"%$search%")
                                ->orWhere('employee_no','like',"%$search%");
                        })->orWhereHas('brand',function($query) use($search, $request){
                            $query->where('name','like',"%$search%")
                                ->orWhere('employee_no','like',"%$search%");
                        });
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
                    $val->account->name,
                    $val->brand->code,
                    $val->brand->name,
                    '
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

    // public function create(Request $request){
    //     $validation = Validator::make($request->all(), [
    //         'code' 				=> $request->temp ? ['required', Rule::unique('UserBrands', 'code')->ignore($request->temp)] : 'required|unique:UserBrands,code',
    //         'name'              => 'required',
    //         'company_id'        => 'required',
    //         'account_no'        => 'required',
    //         'account_name'      => 'required',
    //     ], [
    //         'code.required' 	    => 'Kode tidak boleh kosong.',
    //         'code.unique'           => 'Kode telah terpakai.',
    //         'name.required'         => 'Nama tidak boleh kosong.',
    //         'company_id.required'   => 'Perusahaan tidak boleh kosong.',
    //         'account_no.required'   => 'Nomor rekening tidak boleh kosong.',
    //         'account_name.required' => 'Atas nama rekening tidak boleh kosong.',
    //     ]);

    //     if($validation->fails()) {
    //         $response = [
    //             'status' => 422,
    //             'error'  => $validation->errors()
    //         ];
    //     } else {
	// 		if($request->temp){
    //             DB::beginTransaction();
    //             try {
    //                 $query = UserBrand::find($request->temp);
    //                 $query->code            = $request->code;
    //                 $query->name	        = $request->name;
    //                 $query->account_name    = $request->account_name;
    //                 $query->account_no      = $request->account_no;
    //                 $query->company_id      = $request->company_id;
    //                 $query->branch          = $request->branch;
    //                 $query->is_show         = $request->is_show ? $request->is_show : NULL;
    //                 $query->status          = $request->status ? $request->status : '2';
    //                 $query->save();
    //                 DB::commit();
    //             }catch(\Exception $e){
    //                 DB::rollback();
    //             }
	// 		}else{
    //             DB::beginTransaction();
    //             try {
    //                 $query = UserBrand::create([
    //                     'code'          => $request->code,
    //                     'name'			=> $request->name,
    //                     'account_name'  => $request->account_name,
    //                     'account_no'    => $request->account_no,
    //                     'company_id'    => $request->company_id,
    //                     'branch'        => $request->branch,
    //                     'is_show'       => $request->is_show ? $request->is_show : NULL,
    //                     'status'        => $request->status ? $request->status : '2'
    //                 ]);
    //                 DB::commit();
    //             }catch(\Exception $e){
    //                 DB::rollback();
    //             }
	// 		}

	// 		if($query) {

    //             activity()
    //                 ->performedOn(new UserBrand())
    //                 ->causedBy(session('bo_id'))
    //                 ->withProperties($query)
    //                 ->log('Add / edit UserBrand.');

	// 			$response = [
	// 				'status'  => 200,
	// 				'message' => 'Data successfully saved.'
	// 			];
	// 		} else {
	// 			$response = [
	// 				'status'  => 500,
	// 				'message' => 'Data failed to save.'
	// 			];
	// 		}
	// 	}

	// 	return response()->json($response);
    // }


    public function show(Request $request){
        $UserBrand = UserBrand::find($request->id);

		return response()->json($UserBrand);
    }

    public function destroy(Request $request){
        $query = UserBrand::find($request->id);

        if($query->delete()) {
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
