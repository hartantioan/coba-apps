<?php

namespace App\Http\Controllers\Auth;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Models\Company;
use App\Models\Group;
use App\Models\Place;
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
        if(session('bo_id')){
            return redirect('admin/dashboard');
        }else{
            return view('admin.auth.register');
        }
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

        if($request->type == 'upload'){
            $validation = Validator::make($request->all(), [
                'document' 				=> 'required|mimes:pdf|max:10240',
            ], [
                'document.required' 	=> 'Dokumen tidak boleh kosong.',
                'document.mimes'        => 'Dokumen harus dalam bentuk PDF.',
                'document.max'          => 'Ukuran dokumen maksimal 30 Mb',
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

    public function hrIndex(Request $request)
    {
        
        $data = [
            'title'     => 'Registrasi Non-Staff',
            'content'   => 'admin.hr.register',
            'company'   => Company::where('status','1')->get(),
            'groups'    => Group::where('status','1')->where('type','1')->get(),
            'place'     => Place::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function hrDatatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'username',
            'address',
            'email',
            'hp',
            'document',
            'status',
            'add_to_user',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Registration::count();
        
        $query_data = Registration::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('username', 'like', "%$search%")
                            ->orWhere('address','like',"%$search%")
                            ->orWhere('email','like',"%$search%")
                            ->orWhere('hp','like',"%$search%");
                    });
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = Registration::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('username', 'like', "%$search%")
                            ->orWhere('address','like',"%$search%")
                            ->orWhere('email','like',"%$search%")
                            ->orWhere('hp','like',"%$search%");
                    });
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                if($val->add_to_user){
                    $editBtn = '-';
                }else{
                    $editBtn = '<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>';
                }
				
                $response['data'][] = [
                    $nomor,
                    $val->code,
                    $val->name,
                    $val->username,
                    $val->address,
                    $val->email,
                    $val->hp,
                      $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                    $val->status(),
                    $val->add_to_user ? 'Terdaftar - '.$val->account->employee_no : 'Tidak Terdaftar',
                    $editBtn
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

    public function hrShow(Request $request){
        $reg = Registration::where('code',CustomHelper::decrypt($request->id))->first();
        				
		return response()->json($reg);
    }

    public function hrCreate(Request $request){
        $validation = Validator::make($request->all(), [
            'temp'              => 'required',
            'name' 				=> 'required',
            'username'          => 'required|unique:users,username',
            'address'           => 'required',
            'hp'		        => 'required|unique:users,phone',
            'email'             => 'required|unique:users,email',
            'status'            => 'required',
        ], [
            'temp.required'                 => 'Data tidak boleh kosong.',
            'name.required' 	            => 'Nama tidak boleh kosong.',
            'username.required'             => 'Username tidak boleh kosong.',
            'username.unique'               => 'Username telah terpakai.',
            'hp.required'                   => 'Telepon tidak boleh kosong.',
            'hp.unique'                     => 'Telepon telah terpakai.',
            'email.required'	            => 'Email tidak boleh kosong.',
            'email.unique'                  => 'Email telah terpakai.',
            'address.required'              => 'Alamat tidak boleh kosong.',
            'status.required'               => 'Status tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            DB::beginTransaction();
            try {

                $query = Registration::where('code',CustomHelper::decrypt($request->temp))->first();

                $query->update([
                    'user_id'       => session('bo_id'),
                    'name'          => $request->name,
                    'username'      => $request->username,
                    'password'      => $request->password ? bcrypt($request->password) : $query->password,
                    'address'       => $request->address,
                    'email'         => $request->email,
                    'hp'            => $request->hp,
                    'status'        => $request->status,
                    'add_to_user'   => $request->add_to_user ? $request->add_to_user : NULL,
                ]);

                if($request->add_to_user && $request->status == '3'){
                    $user = User::create([
                        'name'			        => $query->name,
                        'employee_no'           => User::generateCode('1','2',$request->place_id),
                        'username'	            => $query->username,
                        'password'		        => $request->password ? bcrypt($request->password) : $query->password,
                        'phone'	                => $query->hp,
                        'email'	                => $query->email,
                        'address'	            => $query->address,
                        'type'                  => '1',
                        'group_id'              => $request->group_id ? $request->group_id : NULL,
                        'id_card'	            => $request->id_card,
                        'id_card_address'       => $request->id_card_address,
                        'company_id'	        => $request->company_id,
                        'place_id'	            => $request->place_id,
                        'province_id'	        => $request->province_id,
                        'city_id'               => $request->city_id,
                        'district_id'           => $request->district_id,
                        'subdistrict_id'        => $request->subdistrict_id,
                        'tax_id'                => $request->tax_id,
                        'tax_name'              => $request->tax_name,
                        'tax_address'           => $request->tax_address,
                        'limit_credit'          => $request->limit_credit ? str_replace(',','.',str_replace('.','',$request->limit_credit)) : NULL,
                        'count_limit_credit'    => 0,
                        'top'                   => 0,
                        'top_internal'          => 0,
                        'status'                => '1',
                        'gender'                => $request->gender,
                        'married_status'        => $request->married_status,
                        'married_date'          => $request->married_date,
                        'children'              => $request->children,
                        'country_id'            => $request->country_id,
                        'connection_id'         => 0,
                        'user_status'           => 'Offline',
                        'employee_type'         => '2',
                        'registration_id'       => $query->id,
                        'last_change_password'  => date('Y-m-d H:i:s'),
                    ]);

                    $data = [
                        'subject'   => 'Informasi Akun',
                        'view'      => 'admin.mail.account',
                        'password'  => $request->password ? $request->password : 'Yang anda masukkan ketika mendaftar sebagai pegawai non-staff.',
                        'result'    => $user,   
                    ];

                    Mail::to($request->email)->send(new SendMail($data));

                    if(Mail::flushMacros()){
                        
                    }else{
                        activity()
                            ->performedOn(new Registration())
                            ->withProperties($query)
                            ->log('Update Registration Non-Staff '.$query->name);
                    }
                }     

                $response = [
                    'status'    => 200,
                    'message'   => 'Data anda berhasil dikirimkan.'
                ];

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
        }

        return response()->json($response);
    }
}