<?php

namespace App\Http\Controllers\Setting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Place;
use App\Models\Warehouse;
use App\Models\UserPlace;
use App\Models\UserWarehouse;

class DataAccessController extends Controller
{
    public function index()
    {
        $data = [ 
            'title'     => 'Akses Data Pegawai',
            'user'      => User::where('status','1')->where('type','1')->get(),
            'place'     => Place::where('status','1')->get(),
            'warehouse' => Warehouse::where('status','1')->get(),
            'content'   => 'admin.setting.data_access'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'user_id' 			    => 'required',
        ], [
            'user_id.required' 	    => 'Pegawai tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
			
            DB::beginTransaction();
            try {
                
                if($request->checkplace){
                    UserPlace::where('user_id',$request->user_id)->whereNotIn('place_id',$request->checkplace)->delete();

                    foreach($request->checkplace as $row){
                        $cek = UserPlace::where('user_id',$request->user_id)->where('place_id',$row)->count();
                        if($cek == 0){
                            UserPlace::create([
                                'user_id'   => $request->user_id,
                                'place_id'  => $row
                            ]);
                        }
                    }
                }

                if($request->checkwarehouse){
                    UserWarehouse::where('user_id',$request->user_id)->whereNotIn('warehouse_id',$request->checkwarehouse)->delete();

                    foreach($request->checkwarehouse as $row){
                        $cek = UserWarehouse::where('user_id',$request->user_id)->where('warehouse_id',$row)->count();
                        if($cek == 0){
                            UserWarehouse::create([
                                'user_id'       => $request->user_id,
                                'warehouse_id'  => $row
                            ]);
                        }
                    }
                }

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
			
            $response = [
                'status'  => 200,
                'message' => 'Data successfully saved.'
            ];
		}
		
		return response()->json($response);
    }

    public function refresh(Request $request){
        $user = User::find($request->id);

        $places = [];
        $warehouses = [];
		
		foreach($user->userPlace as $row){
			$places[] = [
                'id'       => $row->place_id,
            ];
		}

        foreach($user->userWarehouse as $row){
			$warehouses[] = [
                'id'       => $row->warehouse_id,
            ];
		}

        $result = [
            'places'        => $places,
            'warehouses'    => $warehouses
        ];
        				
		return response()->json($result);
    }
}
