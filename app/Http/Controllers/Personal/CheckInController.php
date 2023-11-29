<?php

namespace App\Http\Controllers\Personal;

use App\Http\Controllers\Controller;
use App\Models\Attendances;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
class CheckInController extends Controller
{
    public function index()
    {
        $userCode = session('bo_id');

        $data = [
            'title'         => 'Absensi - Personal',
            'content'       => 'admin.personal.check_in',
            'data_user'     => User::find(session('bo_id')),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function create(Request $request)
    {   
        info($request);
        $validation = Validator::make($request->all(), [
            'latitude'             => 'required',
            'longitude'			    => 'required',
        ], [
            'latitude.required' 	                => 'Latitude / Longitude tidak boleh kosong.',
            'longitude.required'                       => 'Longitude / Latitude tidak boleh kosong.',
        ]);
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        }else {
            DB::beginTransaction();
            try {
                $query = Attendances::create([
                    'code'			                => $request->code,
                    'employee_no'		            => session('bo_employee_no'),
                    'date'                          => Carbon::now(),
                    'verify_type'	                => '4',
                    'location'                      => $request->location,
                    'latitude'                      => $request->latitude,
                    'longitude'                     => $request->longitude,
                ]);

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
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
