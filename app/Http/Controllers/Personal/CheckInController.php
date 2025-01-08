<?php

namespace App\Http\Controllers\Personal;

use App\Helpers\CustomHelper;
use Illuminate\Support\Str;
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
            'serverTime' => Carbon::now()->toIso8601String(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function create(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'latitude'             => 'required',
            'longitude'			    => 'required',
            'img'      => 'required',
        ], [
            'latitude.required' 	                => 'Latitude / Longitude tidak boleh kosong.',
            'longitude.required'                       => 'Longitude / Latitude tidak boleh kosong.',
            'img.required'                       => 'Gambar tidak boleh kosong.',
        ]);
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors(),
                'message'=> 'BELUM MEMILIKI GAMBAR/LOKASI'
            ];
        }else {
            DB::beginTransaction();
            $now = Carbon::now();
            $formattedDateTime = $now->format('Y-m-d\TH:i:s.uP');
            try {

                if($request->img){
                    // $image = $request->img;  // your base64 encoded
                    // $image = str_replace('data:image/png;base64,', '', $image);
                    // $image = str_replace(' ', '+', $image);
                    $imageName = Str::random(35).'.png';
                    $path=storage_path('app/public/attendances/'.$imageName);
                    $newFile = CustomHelper::compress($request->img,$path,30);
                    $basePath = storage_path('app');
                    $desiredPath = explode($basePath.'/', $newFile)[1];
                    info($desiredPath);
                }
                $query = Attendances::create([
                    'code'			                => $request->code,
                    'employee_no'		            => session('bo_employee_no'),
                    'date'                          => $formattedDateTime,
                    'verify_type'	                => '4',
                    'location'                      => $request->location,
                    'latitude'                      => $request->latitude,
                    'longitude'                     => $request->longitude,
                    'image'                         => $desiredPath ? $desiredPath : NULL,

                ]);

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
            info($query);
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
