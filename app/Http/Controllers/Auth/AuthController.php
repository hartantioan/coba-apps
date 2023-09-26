<?php

namespace App\Http\Controllers\Auth;
use App\Helpers\CustomHelper;
use App\Mail\SendMail;
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

class AuthController extends Controller
{
    public function login()
    {
        if(session('bo_id')) {
            return redirect('admin/dashboard');
        }

        return view('admin.auth.login');
    }

    public function auth(Request $request){
        $user = User::where('employee_no', $request->id_card)->where('type','1')->where('status','1')->first();
		if($user) {
            if(Hash::check($request->password, $user->password)) {
                session([
                    'bo_id'             => $user->id,
                    'bo_photo'          => $user->photo(),
                    'bo_name'           => $user->name,
                    'bo_employee_no'    => $user->employee_no,
                    'bo_company_id'     => $user->company_id,
                    'bo_place_id'       => $user->place_id,
                    'bo_department_id'  => $user->department_id,
                    'bo_position_id'    => $user->position_id,
                    'bo_is_lock'        => 0,
                ]);
                $token = md5(uniqid());

                User::where('employee_no', $request->id_card)->where('type','1')->where('status','1')->update([ 'token' => $token ]);
                Auth::login($user);
                $response = [
                    'status' 	=> 200,
                    'message'	=> 'Successfull logged in. Please wait!'
                ];

            } else {
                $response = [
                    'status' 	=> 422,
                    'message'	=> 'Account not found'
                ];
            }
		} else {
			$response = [
				'status' 	=> 422,
				'message'	=> 'Account not found'
			];
		}

        return response()->json($response);
    }

    public function logout(){
        session()->flush();
        Auth::logout();
        return redirect('admin/login');
    }
    public function enable(){
        session([
            'bo_is_lock' => 1,
            'bo_last_url' => url()->previous(),
        ]);
        
        return redirect('admin/lock');
    }

    public function disable(Request $request){
        $user = User::where('employee_no', $request->id_card)->where('type','1')->where('status','1')->first();
		if($user) {
            if(Hash::check($request->password, $user->password)) {
                session([
                    'bo_is_lock' => 0,
                ]);
                $response = [
                    'status'    => 200,
                    'url'       => session('bo_last_url'),
                    'message'	=> 'Sukses! Halaman akan dialihkan.'
                ];
            } else {
                $response = [
                    'status' 	=> 422,
                    'message'	=> 'Password tidak sesuai.'
                ];
            }
		} else {
			$response = [
				'status' 	=> 422,
				'message'	=> 'Pengguna tidak ditemukan.'
			];
		}
        
        return response()->json($response);
    }
    
    public function lock(){
        if(session('bo_is_lock') == 1){
            $data = [
                'title'     => 'Profil Pengguna',
                'content'   => 'admin.personal.profile',
                'data'      => User::find(session('bo_id')),
                
            ];
            return view('admin.personal.lock', ['data' => $data]);
        }else{
            return redirect(url()->previous());
        }
    }

    public function index()
    {
        $data = [
            'title'     => 'Profil Pengguna',
            'content'   => 'admin.personal.profile',
            'data'      => User::find(session('bo_id'))
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function update(Request $request){
        
        if($request->hasFile('file')){
            $validation = Validator::make($request->all(), [
                'file'              => 'image|max:100|mimes:jpg,jpeg,png',
                'name' 				=> 'required',
                'phone'		        => ['required', Rule::unique('users', 'phone')->ignore(session('bo_id'))],
                'address'           => 'required',
                'id_card'           => 'required',
                'province_id'       => 'required',
                'city_id'           => 'required',
            ], [
                'file.image'            => 'Foto harus gambar.',
                'file.max'              => 'Foto maksimal 100Kb.',
                'file.mimes'            => 'Foto harus dalam format jpg, jpeg, png.',
                'name.required' 	    => 'Nama tidak boleh kosong.',
                'phone.required'        => 'Telepon tidak boleh kosong.',
                'phone.unique'          => 'Telepon telah terpakai.',
                'address.required'      => 'Alamat tidak boleh kosong.',
                'id_card.required'      => 'No Identitas tidak boleh kosong.',
                'province_id.required'  => 'Provinsi tidak boleh kosong.',
                'city_id.required'      => 'Kota tidak boleh kosong.',
            ]);
        }else{
            $validation = Validator::make($request->all(), [
                'name' 				=> 'required',
                'phone'		        => ['required', Rule::unique('users', 'phone')->ignore(session('bo_id'))],
                'address'           => 'required',
                'id_card'           => 'required',
                'province_id'       => 'required',
                'city_id'           => 'required',
            ], [
                'name.required' 	    => 'Nama tidak boleh kosong.',
                'phone.required'        => 'Telepon tidak boleh kosong.',
                'phone.unique'          => 'Telepon telah terpakai.',
                'address.required'      => 'Alamat tidak boleh kosong.',
                'id_card.required'      => 'No Identitas tidak boleh kosong.',
                'province_id.required'  => 'Provinsi tidak boleh kosong.',
                'city_id.required'      => 'Kota tidak boleh kosong.',
            ]);
        }
        

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $query = User::find(session('bo_id'));

            $passed = false;

            if($request->new_password){
                if(Hash::check($request->old_password, $query->password)) {
                    $passed = true;
                }
            }else{
                $passed = true;
            }

            if($passed == false){
                return response()->json([
                    'status'    => 500,
                    'message'   => 'Password lama tidak sama, silahkan cek kembali.'
                ]);
            }

            DB::beginTransaction();
            try {

                if($request->hasFile('file')) {
                    if($query->photo){
                        if(Storage::exists($query->photo)){
                            Storage::delete($query->photo);
                        }
                    }
                    $photo = $request->file('file')->store('public/users');
                } else {
                    $photo = $query->photo;
                }

                $query->name                    = $request->name;
                $query->password                = $request->new_password ? bcrypt($request->new_password) : $query->password;
                $query->phone	                = $request->phone;
                $query->address	                = $request->address;
                $query->id_card                 = $request->id_card ? $request->id_card : NULL;
                $query->province_id             = $request->province_id;
                $query->city_id                 = $request->city_id;
                $query->photo                   = $photo;
                $query->last_change_password    = now();
                $query->save();

                session([
                    'bo_photo' => User::find(session('bo_id'))->photo(),
                ]);

                if($query) {
                    activity()
                        ->performedOn(new User())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Update user information.');
    
                    $response = [
                        'status'    => 200,
                        'message'   => 'Data successfully saved.',
                        'photo'     => $query->photo()
                    ];
                } else {
                    $response = [
                        'status'  => 500,
                        'message' => 'Data failed to save.'
                    ];
                }

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
		}
		
		return response()->json($response);
    }

    public function uploadSign(Request $request){
        if($request->signdata){
			$validation = Validator::make($request->all(), [
				'signdata'  => 'required'
			], [
				'signdata.required' => 'Tanda tangan tidak boleh kosong.'
			]);
		}elseif($request->hasFile('file')){
			$validation = Validator::make($request->all(), [
				'file'  => 'required'
			], [
				'file.required' => 'Tanda tangan tidak boleh kosong.'
			]);
		}
		
		if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            $query = User::find(session('bo_id'));
            
            if($request->signdata) {
                if($query->signature){
                    if(Storage::exists($query->signature)) {
                        Storage::delete($query->signature);
                    }
                }
				
				$folderPath = Storage::path('public/user_signs/');
				
				$image_parts = explode(";base64,", $request->signdata);
				$image_type_aux = explode("image/", $image_parts[0]);
				$image_type = $image_type_aux[1];
				$image_base64 = base64_decode($image_parts[1]);
				
				$newname = Str::random(40).'.'.$image_type;
				
				$file = $folderPath.$newname;
				
				file_put_contents($file, $image_base64);
				
				$image = 'public/user_signs/'.$newname;
            }elseif($request->hasFile('file')) {
                if($query->signature){
                    if(Storage::exists($query->signature)) {
                        Storage::delete($query->signature);
                    }
                }

				$image = $request->file('file')->store('public/user_signs');
			} else {
                $image = $query->signature;
            }

            $query->update([
                'signature'  => $image
            ]);

            if($query) {
                $response = [
                    'status'  => 200,
                    'message' => 'Data added successfully.'
                ];
            } else {
                $response = [
                    'status'  => 500,
                    'message' => 'Data failed to add.'
                ];
            }
        }

        return response()->json($response);
    }

    public function forget()
    {
        if(session('bo_id')) {
            return redirect('admin/dashboard');
        }

        return view('admin.auth.forget');
    }

    public function createReset(Request $request){
        
        $query = User::where('email',$request->email)->where('status','1')->where('type','1')->first();
        
        if($query) {
            $code = Str::random(50);
            $encryptCode = CustomHelper::encrypt($code);

            $query->update([
                'reset_code' => $code
            ]);

            $data = [
                'subject'   => 'Reset Akun',
                'view'      => 'admin.mail.reset_password',
                'code'      => $encryptCode,
                'result'    => $query,
            ];

            Mail::to($request->email)->send(new SendMail($data));

            if(Mail::flushMacros()){
                $response = [
                    'status'  => 500,
                    'message' => 'Terdapat kesalahan sistem, mohon ditunggu.'
                ];
            }else{
                activity()
                    ->performedOn(new User())
                    ->withProperties($query)
                    ->log('Send Link Reset Password '.$query->name);

                $response = [
                    'status'  => 200,
                    'message' => 'Link reset berhasil dikirimkan ke email anda.'
                ];
            }
            
        } else {
            $response = [
                'status'  => 500,
                'message' => 'Email tidak ditemukan.'
            ];
        }

        return response()->json($response);
    }

    public function resetPage(Request $request)
    {

        if(session('bo_id')) {
            return redirect('admin/dashboard');
        }

        $data = User::where('reset_code',CustomHelper::decrypt($request->data))->first();

        if($data){
            $data = [
                'title'     => 'Profil Pengguna',
                'data'      => $data,
                'code'      => $request->data,
            ];

            return view('admin.auth.reset_page', ['data' => $data]);
        }else{
            abort(404);
        }
    }

    public function changePassword(Request $request){
        
        $query = User::where('status','1')->where('reset_code',CustomHelper::decrypt($request->code))->first();
        
        if($query) {

            $query->update([
                'reset_code'            => NULL,
                'password'              => bcrypt($request->password),
                'last_change_password'  => date('Y-m-d H:i:s'),
            ]);

            $data = [
                'subject'   => 'Berhasil Reset Password',
                'view'      => 'admin.mail.success_reset_password',
                'result'    => $query,
            ];

            Mail::to($query->email)->send(new SendMail($data));

            if(Mail::flushMacros()){
                $response = [
                    'status'  => 500,
                    'message' => 'Terdapat kesalahan sistem, mohon ditunggu.'
                ];
            }else{
                activity()
                    ->performedOn(new User())
                    ->withProperties($query)
                    ->log('Successfully Reset Password '.$query->name);

                $response = [
                    'status'  => 200,
                    'message' => 'Berhasil reset password. Halaman akan dialihkan.'
                ];
            }
            
        } else {
            $response = [
                'status'  => 500,
                'message' => 'Data tidak ditemukan.'
            ];
        }

        return response()->json($response);
    }
}
