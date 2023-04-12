<?php

namespace App\Http\Controllers\MasterData;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportCompany;

class CompanyController extends Controller
{
    public function index()
    {
        $data = [
            'title'   => 'Perusahaan',
            'content' => 'admin.master_data.company'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'address',
            'province_id',
            'city_id',
            'npwp_no',
            'npwp_name',
            'npwp_address'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Company::count();
        
        $query_data = Company::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('address', 'like', "%$search%")
                            ->orWhere('npwp_no', 'like', "%$search%")
                            ->orWhere('npwp_name', 'like', "%$search%")
                            ->orWhere('npwp_address', 'like', "%$search%")
                            ->orWhereHas('province', function ($query) use ($search) {
                                    $query->where('name', 'like', "%$search%");
                                }
                            )->orWhereHas('city', function ($query) use ($search) {
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

        $total_filtered = Company::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('address', 'like', "%$search%")
                            ->orWhere('npwp_no', 'like', "%$search%")
                            ->orWhere('npwp_name', 'like', "%$search%")
                            ->orWhere('npwp_address', 'like', "%$search%")
                            ->orWhereHas('province', function ($query) use ($search) {
                                    $query->where('name', 'like', "%$search%");
                                }
                            )->orWhereHas('city', function ($query) use ($search) {
                                    $query->where('name', 'like', "%$search%");
                                }
                            );
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
                    $val->id,
                    $val->code,
                    $val->name,
                    $val->address,
                    $val->province->name,
                    $val->city->name,
                    $val->npwp_no,
                    $val->npwp_name,
                    $val->npwp_address,
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
            'name' 				=> 'required',
            'address'           => 'required',
            'province_id'       => 'required',
            'city_id'           => 'required'
        ], [
            'name.required' 	    => 'Nama tidak boleh kosong.',
            'address.required'      => 'Alamat tidak boleh kosong.',
            'province_id.required'  => 'Provinsi tidak boleh kosong.',
            'city_id.required'      => 'Kota tidak boleh kosong.',
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
                    $query = Company::find($request->temp);
                    $query->name            = $request->name;
                    $query->address	        = $request->address;
                    $query->province_id     = $request->province_id;
                    $query->city_id         = $request->city_id;
                    $query->npwp_no         = $request->npwp_no;
                    $query->npwp_name       = $request->npwp_name;
                    $query->npwp_address    = $request->npwp_address;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = Company::create([
                        'code'          => Company::generateCode(),
                        'name'			=> $request->name,
                        'address'	    => $request->address,
                        'province_id'   => $request->province_id,
                        'city_id'       => $request->city_id,
                        'npwp_no'       => $request->npwp_no,
                        'npwp_name'     => $request->npwp_name,
                        'npwp_address'  => $request->npwp_address,
                        'status'        => $request->status ? $request->status : '2'
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                activity()
                    ->performedOn(new Company())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit company.');

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
        $Company = Company::find($request->id);
        $Company['province_name'] = $Company->province->name;
        $Company['city_name'] = $Company->city->name;
        				
		return response()->json($Company);
    }

    public function destroy(Request $request){
        $query = Company::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new Company())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the Company data');

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

        $search = $request->search;
        $status = $request->status;

        $data = [
            'title' => 'COMPANY REPORT',
            'data' => Company::where(function ($query) use ($search, $status) {
                if ($search) {
                    $query->where(function ($query) use ($search, $status) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('address', 'like', "%$search%")
                            ->orWhere('npwp_no', 'like', "%$search%")
                            ->orWhere('npwp_name', 'like', "%$search%")
                            ->orWhere('npwp_address', 'like', "%$search%")
                            ->orWhereHas('province', function ($query) use ($search, $status) {
                                    $query->where('name', 'like', "%$search%");
                                }
                            )->orWhereHas('city', function ($query) use ($search, $status) {
                                    $query->where('name', 'like', "%$search%");
                                }
                            );
                    });
                    
                }
                if($status){
                    $query->where('status', $status);
                }
            })->get()
		];
		
		return view('admin.print.master_data.company', $data);

    }

    public function export(Request $request){
        $search = $request->search ? $request->search : '';
		$status = $request->status ? $request->status : '';
		
		return Excel::download(new ExportCompany($search,$status), 'company_'.uniqid().'.xlsx');
    }
}