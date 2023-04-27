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
use App\Models\Place;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportPlace;
use App\Models\Company;

class PlaceController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Site',
            'content'   => 'admin.master_data.place',
            'company'   => Company::all()
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'address',
            'company_id',
            'type',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Place::count();
        
        $query_data = Place::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('address', 'like', "%$search%")
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

        $total_filtered = Place::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('address', 'like', "%$search%")
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
                    $val->company->name,
                    $val->type(),
                    $val->province->name,
                    $val->city->name,
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
            'code'              => $request->temp ? ['required', Rule::unique('places', 'code')->ignore($request->temp)] : 'required|unique:places,code',
            'name' 				=> 'required',
            'address'           => 'required',
            'company_id'        => 'required',
            'type'              => 'required',
            'province_id'       => 'required',
            'city_id'           => 'required'
        ], [
            'code.required'         => 'Kode tidak boleh kosong.',
            'code.unique'           => 'Kode telah terpakai.',
            'name.required' 	    => 'Nama tidak boleh kosong.',
            'address.required'      => 'Alamat tidak boleh kosong.',
            'company_id.required'   => 'Cabang tidak boleh kosong.',
            'type.required'         => 'Tipe tidak boleh kosong.',
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
                    $query = Place::find($request->temp);
                    $query->code            = $request->code ? $request->code : $query->code;
                    $query->name            = $request->name;
                    $query->address	        = $request->address;
                    $query->company_id	    = $request->company_id;
                    $query->type	        = $request->type;
                    $query->province_id     = $request->province_id;
                    $query->city_id         = $request->city_id;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = Place::create([
                        'code'          => $request->code ? $request->code : Place::generateCode(),
                        'name'			=> $request->name,
                        'address'	    => $request->address,
                        'company_id'    => $request->company_id,
                        'type'          => $request->type,
                        'province_id'   => $request->province_id,
                        'city_id'       => $request->city_id,
                        'status'        => $request->status ? $request->status : '2'
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                activity()
                    ->performedOn(new Place())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit plant/office.');

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
        $plant = Place::find($request->id);
        $plant['province_name'] = $plant->province->name;
        $plant['city_name'] = $plant->city->name;
        				
		return response()->json($plant);
    }

    public function destroy(Request $request){
        $query = Place::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new Place())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the plant/office data');

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
            'title' => 'PENEMPATAN REPORT',
            'data' => Place::where(function ($query) use ($search, $status) {
                if ($search) {
                    $query->where(function ($query) use ($search, $status) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('address', 'like', "%$search%")
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
		
		return view('admin.print.master_data.place', $data);

    }

    public function export(Request $request){
        $search = $request->search ? $request->search : '';
		$status = $request->status ? $request->status : '';
		
		return Excel::download(new ExportPlace($search,$status), 'place_'.uniqid().'.xlsx');
    }
}