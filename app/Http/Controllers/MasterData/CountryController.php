<?php

namespace App\Http\Controllers\MasterData;
use App\Exports\ExportCountry;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Country;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CountryController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Negara',
            'content' => 'admin.master_data.country'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'phone_code',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Country::count();
        
        $query_data = Country::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%")
                        ->orWhere('phone_code', 'like', "%$search%");
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = Country::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%")
                        ->orWhere('phone_code', 'like', "%$search%");
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
                    $val->phone_code,
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
            'code'			=> $request->temp ? ['required', Rule::unique('countries', 'code')->ignore($request->temp)] : 'required|unique:countries,code',
            'name'          => 'required',
            'phone_code'    => 'required'
        ], [
            'code.required' 	    => 'Kode tidak boleh kosong.',
            'code.unique'           => 'Kode telah dipakai',
            'name.required'         => 'Nama tidak boleh kosong.',
            'phone_code.required'   => 'Kode telepon tidak boleh kosong.',
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
                    $query = Country::find($request->temp);
                    $query->code        = $request->code;
                    $query->name        = $request->name;
                    $query->phone_code  = $request->phone_code;
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = Country::create([
                        'code'          => $request->code,
                        'name'			=> $request->name,
                        'phone_code'    => $request->phone_code,
                        'status'        => '1'
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                activity()
                    ->performedOn(new Country())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit country.');

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
        $country = Country::find($request->id);
        				
		return response()->json($country);
    }

    public function destroy(Request $request){
        $query = Country::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new Country())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the country data');

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
            'title' => 'COUNTRY REPORT',
            'data' => Country::where(function ($query) use ($request) {
                if ($request->search) {
                    $query->where('code', 'like', "%$request->search%")
                        ->orWhere('name', 'like', "%$request->search%")
                        ->orWhere('phone_code', 'like', "%$request->search%");
                }
            })->get()
		];
		
		return view('admin.print.master_data.country', $data);
    }

    public function export(Request $request){
        $search = $request->search ? $request->search : '';
		
		return Excel::download(new ExportCountry($search), 'country_'.uniqid().'.xlsx');
    }
}