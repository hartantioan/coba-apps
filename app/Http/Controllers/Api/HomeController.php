<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Weight;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Validator;

class HomeController extends Controller
{
    public function login(Request $request) {

        $validator = Validator::make($request->all(), [ 
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 401);
        }

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $token = auth()->user()->createApiToken(); #Generate token
            return response()->json(['status' => 'Authorised', 'token' => $token ], 200);
        } else { 
            return response()->json(['status'=>'Unauthorised'], 401);
        }
    }

    public function updateWeight(Request $request) {
        $cek = User::where('api_token',$request->bearerToken())->count();

        if($cek > 0){
            $weight = Weight::where('place_id',$request->place_id)->first();
            if($weight){
                $weight->update([
                    'code'      => Str::random(25),
                    'place_id'  => $request->place_id,
                    'nominal'   => $request->nominal,
                    'rawdata'   => $request->rawdata,
                ]);
            }else{
                $weight = Weight::create([
                    'code'      => Str::random(25),
                    'place_id'  => $request->place_id,
                    'nominal'   => $request->nominal,
                    'rawdata'   => $request->rawdata,
                ]);
            }
            return response()->json(['status' => 'success'], 200);
        }else{
            return response()->json(['status' => 'failed'], 401);
        }
    }
}
