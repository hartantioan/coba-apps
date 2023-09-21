<?php

namespace App\Http\Controllers\Auth;
use App\Models\Registration;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Redirect;
use App\Mail\SendMail;

class RegistrationController extends Controller
{
    public function index()
    {
        return view('admin.auth.register');
    }

    public function create(Request $request){
        if($request->type == 'form'){
            $validation = Validator::make($request->all(), [
                'name' 				=> 'required',
                'username'          => 'required|unique:users,username',
                'password'          => 'required',
                'address'           => 'required',
                'hp'		        => 'required|unique:users,phone',
                'email'             => 'required|unique:users,email',
            ], [
                'name.required' 	            => 'Nama tidak boleh kosong.',
                'username.required'             => 'Username tidak boleh kosong.',
                'username.unique'               => 'Username telah terpakai.',
                'password.required' 	        => 'Password tidak boleh kosong.',
                'hp.required'                   => 'Telepon tidak boleh kosong.',
                'hp.unique'                     => 'Telepon telah terpakai.',
                'email.required'	            => 'Email tidak boleh kosong.',
                'email.unique'                  => 'Email telah terpakai.',
                'address.required'              => 'Alamat tidak boleh kosong.',
            ]);
        }

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            if($request->has('document')){
                $query = Registration::create([
                    'code'      => strtoupper(strtolower(Str::random(25))),
                    'name'      => 'Name-'.strtoupper(strtolower(Str::random(15))),
                    'username'  => 'user-'.strtoupper(strtolower(Str::random(15))),
                    'password'  => bcrypt('superiorporcelain'),
                    'address'   => '-',
                    'email'     => '-',
                    'hp'        => '-',
                    'document'  => $request->file('document')->store('public/registrations'),
                    'status'    => '1',
                ]);
            }else{
                $query = Registration::create([
                    'code'      => strtoupper(strtolower(Str::random(25))),
                    'name'      => $request->name,
                    'username'  => $request->username,
                    'password'  => bcrypt($request->password),
                    'address'   => $request->address,
                    'email'     => $request->email,
                    'hp'        => $request->hp,
                    'status'    => '1',
                ]);
            }
            
            if($request->email){
                $data = [
                    'subject'   => 'Konfirmasi Registrasi',
                    'view'      => 'admin.mail.register',
                    'code'      => $query->code,
                    'result'    => $query,    
                ];

                Mail::to($request->email)->send(new SendMail($data));

                if(Mail::flushMacros()){
                    
                }else{
                    activity()
                        ->performedOn(new Registration())
                        ->withProperties($query)
                        ->log('Registration Non-Staff '.$query->name);
                }
            }            

            $response = [
                'status'    => 200,
                'message'   => 'Data anda berhasil dikirimkan.'
            ];
        }

        return response()->json($response);
    }
}