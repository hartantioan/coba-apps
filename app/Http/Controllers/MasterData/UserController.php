<?php

namespace App\Http\Controllers\MasterData;
use App\Models\Company;
use App\Models\UserPlace;
use App\Models\UserWarehouse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserBank;
use App\Models\UserFile;
use App\Models\UserData;
use App\Models\Bank;
use App\Models\Place;
use App\Models\Warehouse;
use App\Models\Department;
use App\Models\Position;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\Group;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportUser;
use App\Helpers\CustomHelper;

class UserController extends Controller
{
    public function index()
    {
        $data = [
            'title'         => 'Partner Bisnis',
            'company'       => Company::where('status','1')->get(),
            'place'         => Place::where('status','1')->get(),
            'warehouse'     => Warehouse::where('status','1')->get(),
            'department'    => Department::where('status','1')->get(),
            'position'      => Position::where('status','1')->orderBy('order')->get(),
            'group'         => Group::where('status','1')->get(['id','name','type'])->toArray(),
            'menu'          => Menu::whereNull('parent_id')->where('status','1')->oldest('order')->get(),
            'content'       => 'admin.master_data.user'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'name',
            'username',
            'address',
            'id_card'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = User::count();
        
        $query_data = User::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('name', 'like', "%$search%")
                            ->orWhere('username', 'like', "%$search%")
                            ->orWhere('address', 'like', "%$search%")
                            ->orWhere('id_card', 'like', "%$search%");
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->type){
                    $query->where('type', $request->type);
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = User::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('name', 'like', "%$search%")
                            ->orWhere('username', 'like', "%$search%")
                            ->orWhere('address', 'like', "%$search%")
                            ->orWhere('id_card', 'like', "%$search%");
                    });
                }
                
                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->type){
                    $query->where('type', $request->type);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $btn = ($val->type == '1' ? '<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Atur Akses Menu/Form" onclick="access(' . $val->id . ',`'.$val->name.'`)"><i class="material-icons dp48">folder_shared</i></button> ' : '').'<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue accent-2 white-text btn-small" data-popup="tooltip" title="Upload lampiran" onclick="attachment(' . $val->id . ')"><i class="material-icons dp48">perm_media</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>';

                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-id="' . $val->id . '"><i class="material-icons">add</i></button>',
                    $val->name,
                    $val->username,
                    $val->employee_no,
                    $val->type(),
                    $val->status(),
                    $btn
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

    public function getFiles(Request $request){
		$data = UserFile::where('user_id',$request->id)->get();
		
		$result = [];
		
		foreach($data as $row){
			$result[] = [
				'code'	=> CustomHelper::encrypt($row->code),
				'image'	=> $row->fileLocation(),
				'name'	=> $row->file_name
			];
		}
		
		return response()->json($result);
	}

    public function uploadFile(Request $request){
		
		$count = UserFile::where('user_id',$request->id)->count();
		
		if($count >= 5){
			return response()->json([
				'status'		=> 422,
				'message'		=> 'You have reached maximum file uploads for this project.'
			]);
		}else{
			$query = UserFile::create([
				'code'	        => Str::random(),
                'user_id'       => $request->tempuser,
                'file_name'     => $request->file('file')->getClientOriginalName(),
				'file_storage'	=> $request->file('file') ? $request->file('file')->store('public/user_files') : ''
			]);
			
			return response()->json([
				'status'		=> 200,
				'message'		=> 'You have successfully upload the file.'
			]);
		}
	}

    public function destroyFile(Request $request){
		$data = UserFile::where('code',CustomHelper::decrypt($request->id))->first();
		
		$data->deleteFile();
		
		$data->delete();
		
		if($data){
			return response()->json([
				'status'	=> 200,
				'message'	=> 'Picture successfully deleted.' 
			]);
		}else{
			return response()->json([
				'status'	=> 422,
				'message'	=> 'Picture not found.'
			]);
		}
	}

    public function getAccess(Request $request){
		$menus = [];
        $places = [];
        $warehouses = [];
		
		foreach(UserPlace::where('user_id',$request->id)->get() as $row){
			$places[] = [
                'id'       => $row->place_id,
            ];
		}

        foreach(UserWarehouse::where('user_id',$request->id)->get() as $row){
			$warehouses[] = [
                'id'       => $row->warehouse_id,
            ];
		}
		
		foreach(MenuUser::where('user_id',$request->id)->get() as $row){
			$menus[] = [
				'menu_id'	=> $row->menu_id,
				'type'	    => ucfirst($row->type)
			];
		}

        $result = [
            'menus'         => $menus,
            'places'        => $places,
            'warehouses'    => $warehouses
        ];
		
		return response()->json($result);
	}

    public function rowDetail(Request $request)
    {
        $data   = User::find($request->id);

        $banks = [];
        $infos = [];

        foreach($data->userBank as $row){
            $banks[] = $row->bank->name.' No. rek '.$row->no.' Cab. '.$row->branch.' '.$row->isDefault();
        }

        foreach($data->userData as $row){
            $infos[] = $row->title.' '.$row->content;
        }

        $string = '<table>
                        <thead>
                            <tr>
                                <th>No Identitas</th>
                                <th>'.$data->id_card.'</th>
                            </tr>
                            <tr>
                                <th>Alamat Identitas</th>
                                <th>'.$data->id_card_address.'</th>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <th>'.$data->address.'</th>
                            </tr>
                            <tr>
                                <th>Kota/Kabupaten</th>
                                <th>'.$data->city->name.'</th>
                            </tr>
                            <tr>
                                <th>Provinsi</th>
                                <th>'.$data->province->name.'</th>
                            </tr>
                            <tr>
                                <th>Kota/Kabupaten</th>
                                <th>'.$data->country->name.'</th>
                            </tr>
                            <tr>
                                <th>Cabang</th>
                                <th>'.($data->company()->exists() ? $data->company->name : '-').'</th>
                            </tr>
                            <tr>
                                <th>Penempatan</th>
                                <th>'.($data->place()->exists() ? $data->place->name : '-').'</th>
                            </tr>
                            <tr>
                                <th>Jenis Kelamin</th>
                                <th>'.$data->gender().'</th>
                            </tr>
                            <tr>
                                <th>Status Pernikahan</th>
                                <th>'.$data->marriedStatus().'</th>
                            </tr>
                            <tr>
                                <th>Tgl. Menikah</th>
                                <th>'.date('d/m/y',strtotime($data->married_date)).'</th>
                            </tr>
                            <tr>
                                <th>Jumlah Anak</th>
                                <th>'.$data->children.'</th>
                            </tr>
                            <tr>
                                <th>Departemen</th>
                                <th>'.($data->department()->exists() ? $data->department->name : "-").'</th>
                            </tr>
                            <tr>
                                <th>Posisi/Level</th>
                                <th>'.($data->position()->exists() ? $data->position->name : "-").'</th>
                            </tr>
                            <tr>
                                <th>NPWP</th>
                                <th>'.$data->tax_id.'</th>
                            </tr>
                            <tr>
                                <th>Nama NPWP</th>
                                <th>'.$data->tax_name.'</th>
                            </tr>
                            <tr>
                                <th>Alamat NPWP</th>
                                <th>'.$data->tax_address.'</th>
                            </tr>
                            <tr>
                                <th>PIC</th>
                                <th>'.$data->pic.'</th>
                            </tr>
                            <tr>
                                <th>Kontak PIC</th>
                                <th>'.$data->pic_no.'</th>
                            </tr>
                            <tr>
                                <th>Kontak Kantor</th>
                                <th>'.$data->office_no.'</th>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <th>'.$data->email.'</th>
                            </tr>
                            <tr>
                                <th>Deposit</th>
                                <th>'.$data->deposit.'</th>
                            </tr>
                            <tr>
                                <th>Limit Credit</th>
                                <th>'.number_format($data->limit_credit,0,',','.').'</th>
                            </tr>
                            <tr>
                                <th>TOP (Tempo Pembayaran)</th>
                                <th>'.$data->top.' hari</th>
                            </tr>
                            <tr>
                                <th>TOP Internal</th>
                                <th>'.$data->top_internal.' hari</th>
                            </tr>
                            <tr>
                                <th>Toleransi Qty Barang Diterima (%)</th>
                                <th>'.$data->tolerance_gr.' %</th>
                            </tr>
                            <tr>
                                <th>Daftar Rekening</th>
                                <th>'.implode('<br>',$banks).'</th>
                            </tr>
                            <tr>
                                <th>Info Tambahan</th>
                                <th>'.implode('<br>',$infos).'</th>
                            </tr>
                            <tr>
                                <th>Kelompok</th>
                                <th>'.($data->group()->exists() ? $data->group->name : '-').'</th>
                            </tr>
                            <tr>
                                <th>Terakhir Ubah Password</th>
                                <th>'.$data->last_change_password.'</th>
                            </tr>
                        </thead>
                    </table>';
		
        return response()->json($string);
    }

    public function create(Request $request){
        if($request->type == '1'){
            $validation = Validator::make($request->all(), [
                'name' 				=> 'required',
                'username'			=> $request->temp ? ['required', Rule::unique('users', 'username')->ignore($request->temp)] : 'required|unique:users,username',
                'phone'		        => $request->temp ? ['required', Rule::unique('users', 'phone')->ignore($request->temp)] : 'required|unique:users,phone',
                'email'             => $request->temp ? ['required', Rule::unique('users', 'email')->ignore($request->temp)] : 'required|unique:users,email',
                'address'           => 'required',
                'type'              => 'required',
                'id_card'           => 'required',
                'id_card_address'   => 'required',
                'company_id'         => 'required',
                'place_id'          => 'required',
                'department_id'     => 'required',
                'position_id'       => 'required',
                'province_id'       => 'required',
                'city_id'           => 'required',
                'country_id'        => 'required',
            ], [
                'name.required' 	    => 'Nama tidak boleh kosong.',
                'username.required'     => 'Username tidak boleh kosong.',
                'username.unique'       => 'Username telah terpakai.',
                'phone.required'        => 'Telepon tidak boleh kosong.',
                'phone.unique'          => 'Telepon telah terpakai.',
                'email.required'	    => 'Email tidak boleh kosong.',
                'email.unique'          => 'Email telah terpakai.',
                'address.required'      => 'Alamat tidak boleh kosong.',
                'type.required'	        => 'Tipe pengguna tidak boleh kosong.',
                'id_card.required'      => 'No Identitas tidak boleh kosong.',
                'id_card_address.required' => 'Alamat Identitas tidak boleh kosong.',
                'company.required'      => 'Perusahaan tidak boleh kosong.',
                'place_id.required'     => 'Penempatan tidak boleh kosong.',
                'department_id.required'=> 'Departemen / Divisi tidak boleh kosong.',
                'position_id.required'  => 'Posisi / level tidak boleh kosong.',
                'province_id.required'  => 'Provinsi tidak boleh kosong.',
                'city_id.required'      => 'Kota tidak boleh kosong.',
                'country_id.required'   => 'Negara tidak boleh kosong.',
            ]);
        }else{
            $validation = Validator::make($request->all(), [
                'name' 				=> 'required',
                'phone'		        => $request->temp ? ['required', Rule::unique('users', 'phone')->ignore($request->temp)] : 'required|unique:users,phone',
                'email'             => $request->temp ? ['required', Rule::unique('users', 'email')->ignore($request->temp)] : 'required|unique:users,email',
                'address'           => 'required',
                'type'              => 'required',
                'province_id'       => 'required',
                'city_id'           => 'required',
                'pic'               => 'required',
                'pic_no'            => 'required',
                'office_no'         => 'required',
                'limit_credit'      => 'required',
                'country_id'        => 'required',
            ], [
                'name.required' 	    => 'Nama tidak boleh kosong.',
                'phone.required'        => 'Telepon tidak boleh kosong.',
                'phone.unique'          => 'Telepon telah terpakai.',
                'email.required'	    => 'Email tidak boleh kosong.',
                'email.unique'          => 'Email telah terpakai.',
                'address.required'      => 'Alamat tidak boleh kosong.',
                'type.required'	        => 'Tipe pengguna tidak boleh kosong.',
                'province_id.required'  => 'Provinsi tidak boleh kosong.',
                'city_id.required'      => 'Kota tidak boleh kosong.',
                'pic.required'          => 'PIC tidak boleh kosong.',
                'pic_no.required'       => 'Nomor PIC tidak boleh kosong.',
                'office_no.required'    => 'Nomor Kantor tidak boleh kosong.',
                'limit_credit.required' => 'Limit credit tidak boleh kosong.',
                'country_id.required'   => 'Negara tidak boleh kosong.',
            ]);
        }

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = User::find($request->temp);
                    $query->name            = $request->name;
                    $query->username        = $request->username ? $request->username : NULL;
                    $query->password        = $request->password ? bcrypt($request->password) : $query->password;
                    $query->phone	        = $request->phone;
                    $query->email           = $request->email;
                    $query->address	        = $request->address;
                    $query->type            = $request->type;
                    $query->group_id        = $request->group_id ? $request->group_id : NULL;
                    $query->id_card         = $request->id_card ? $request->id_card : NULL;
                    $query->id_card_address = $request->id_card_address;
                    $query->company_id	    = $request->company_id ? $request->company_id : NULL;
                    $query->place_id        = $request->place_id ? $request->place_id : NULL;
                    $query->department_id   = $request->type == '1' ? $request->department_id : NULL;
                    $query->position_id     = $request->type == '1' ? $request->position_id :NULL;
                    $query->province_id     = $request->province_id;
                    $query->city_id         = $request->city_id;
                    $query->tax_id          = $request->tax_id;
                    $query->tax_name        = $request->tax_name;
                    $query->tax_address     = $request->tax_address;
                    $query->pic             = $request->pic ? $request->pic : NULL;
                    $query->pic_no          = $request->pic_no ? $request->pic_no : NULL;
                    $query->office_no       = $request->office_no ? $request->office_no : NULL;
                    $query->limit_credit    = $request->limit_credit ? str_replace(',','.',str_replace('.','',$request->limit_credit)) : NULL;
                    $query->top             = $request->top;
                    $query->top_internal    = $request->top_internal;
                    $query->tolerance_gr    = $request->tolerance_gr;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->gender          = $request->gender;
                    $query->married_status  = $request->type == '1' ? $request->married_status :NULL;
                    $query->married_date    = $request->type == '1' ? $request->married_date :NULL;
                    $query->children        = $request->type == '1' ? $request->children :NULL;
                    $query->country_id      = $request->country_id;
                    $query->save();

                    $query->userBank()->delete();
                    $query->userData()->delete();

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = User::create([
                        'name'			=> $request->name,
                        'employee_no'   => User::generateCode($request->type),
                        'username'	    => $request->username ? $request->username : NULL,
                        'password'		=> $request->password ? bcrypt($request->password) : NULL,
                        'phone'	        => $request->phone,
                        'email'	        => $request->email,
                        'address'	    => $request->address,
                        'type'          => $request->type,
                        'group_id'      => $request->group_id ? $request->group_id : NULL,
                        'id_card'	    => $request->id_card,
                        'id_card_address' => $request->id_card_address,
                        'company_id'	=> $request->company_id ? $request->company_id : NULL,
                        'place_id'	    => $request->place_id ? $request->place_id : NULL,
                        'department_id' => $request->type == '1' ? $request->department_id : NULL,
                        'position_id'   => $request->type == '1' ? $request->position_id :NULL,
                        'province_id'   => $request->province_id,
                        'city_id'       => $request->city_id,
                        'tax_id'        => $request->tax_id,
                        'tax_name'      => $request->tax_name,
                        'tax_address'   => $request->tax_address,
                        'pic'           => $request->pic ? $request->pic : NULL,
                        'pic_no'        => $request->pic_no ? $request->pic_no : NULL,
                        'office_no'     => $request->office_no ? $request->office_no : NULL,
                        'limit_credit'  => $request->limit_credit ? str_replace(',','.',str_replace('.','',$request->limit_credit)) : NULL,
                        'top'           => $request->top,
                        'top_internal'  => $request->top_internal,
                        'tolerance_gr'  => $request->tolerance_gr,
                        'status'        => $request->status ? $request->status : '2',
                        'gender'        => $request->gender,
                        'married_status'=> $request->type == '1' ? $request->married_status :NULL,
                        'married_date'  => $request->type == '1' ? $request->married_date :NULL,
                        'children'      => $request->type == '1' ? $request->children :NULL,
                        'country_id'    => $request->country_id,
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                if($request->arr_bank){
                    $checked = isset($request->check) ? intval($request->check) : '';
                    foreach($request->arr_bank as $key => $row){
                        UserBank::create([
                            'user_id'	    => $query->id,
                            'bank_id'	    => $row,
                            'name'          => $request->arr_name[$key],
                            'no'            => $request->arr_no[$key],
                            'branch'        => $request->arr_branch[$key],
                            'is_default'    => $key == $checked ? '1' : '0',
                        ]);
                    }
                }

                if($request->arr_title){
                    foreach($request->arr_title as $key => $row){
                        UserData::create([
                            'user_id'	    => $query->id,
                            'title'         => $row,
                            'content'       => isset($request->arr_content[$key]) ? $request->arr_content[$key] : NULL
                        ]);
                    }
                }

                activity()
                    ->performedOn(new User())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit user.');

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

    public function createAccess(Request $request){
        $validation = Validator::make($request->all(), [
            'tempuseraccess' 			    => 'required',
        ], [
            'tempuseraccess.required' 	    => 'Pegawai tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
			
            DB::beginTransaction();
            try {
                
                if($request->checkboxView){
                    MenuUser::where('user_id',$request->tempuseraccess)->whereNotIn('menu_id',$request->checkboxView)->where('type','view')->delete();

                    foreach($request->checkboxView as $row){
                        $cek = MenuUser::where('user_id',$request->tempuseraccess)->where('menu_id',$row)->where('type','view')->count();
                        if($cek == 0){
                            MenuUser::create([
                                'user_id'   => $request->tempuseraccess,
                                'menu_id'   => $row,
                                'type'      => 'view'
                            ]);
                        }
                    }
                }else{
                    MenuUser::where('user_id',$request->tempuseraccess)->where('type','view')->delete();
                }

                if($request->checkboxUpdate){
                    MenuUser::where('user_id',$request->tempuseraccess)->whereNotIn('menu_id',$request->checkboxUpdate)->where('type','update')->delete();

                    foreach($request->checkboxUpdate as $row){
                        $cek = MenuUser::where('user_id',$request->tempuseraccess)->where('menu_id',$row)->where('type','update')->count();
                        if($cek == 0){
                            MenuUser::create([
                                'user_id'   => $request->tempuseraccess,
                                'menu_id'   => $row,
                                'type'      => 'update'
                            ]);
                        }
                    }
                }else{
                    MenuUser::where('user_id',$request->tempuseraccess)->where('type','update')->delete();
                }

                if($request->checkboxDelete){
                    MenuUser::where('user_id',$request->tempuseraccess)->whereNotIn('menu_id',$request->checkboxDelete)->where('type','delete')->delete();

                    foreach($request->checkboxDelete as $row){
                        $cek = MenuUser::where('user_id',$request->tempuseraccess)->where('menu_id',$row)->where('type','delete')->count();
                        if($cek == 0){
                            MenuUser::create([
                                'user_id'   => $request->tempuseraccess,
                                'menu_id'   => $row,
                                'type'      => 'delete'
                            ]);
                        }
                    }
                }else{
                    MenuUser::where('user_id',$request->tempuseraccess)->where('type','delete')->delete();
                }

                if($request->checkboxVoid){
                    MenuUser::where('user_id',$request->tempuseraccess)->whereNotIn('menu_id',$request->checkboxVoid)->where('type','void')->delete();

                    foreach($request->checkboxVoid as $row){
                        $cek = MenuUser::where('user_id',$request->tempuseraccess)->where('menu_id',$row)->where('type','void')->count();
                        if($cek == 0){
                            MenuUser::create([
                                'user_id'   => $request->tempuseraccess,
                                'menu_id'   => $row,
                                'type'      => 'void'
                            ]);
                        }
                    }
                }else{
                    MenuUser::where('user_id',$request->tempuseraccess)->where('type','void')->delete();
                }

                if($request->checkplace){
                    UserPlace::where('user_id',$request->tempuseraccess)->whereNotIn('place_id',$request->checkplace)->delete();

                    foreach($request->checkplace as $row){
                        $cek = UserPlace::where('user_id',$request->tempuseraccess)->where('place_id',$row)->count();
                        if($cek == 0){
                            UserPlace::create([
                                'user_id'   => $request->tempuseraccess,
                                'place_id'  => $row
                            ]);
                        }
                    }
                }else{
                    UserPlace::where('user_id',$request->tempuseraccess)->delete();
                }

                if($request->checkwarehouse){
                    UserWarehouse::where('user_id',$request->tempuseraccess)->whereNotIn('warehouse_id',$request->checkwarehouse)->delete();

                    foreach($request->checkwarehouse as $row){
                        $cek = UserWarehouse::where('user_id',$request->tempuseraccess)->where('warehouse_id',$row)->count();
                        if($cek == 0){
                            UserWarehouse::create([
                                'user_id'       => $request->tempuseraccess,
                                'warehouse_id'  => $row
                            ]);
                        }
                    }
                }else{
                    UserWarehouse::where('user_id',$request->tempuseraccess)->delete();
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

    public function show(Request $request){
        $user = User::find($request->id);
        $user['province_name'] = $user->province->name;
        $user['city_name'] = $user->city->name;
        $user['country_name'] = $user->country->name;
        $user['limit_credit'] = $user->limit_credit ? number_format($user->limit_credit, 0, ',', '.') : '';

        $banks = [];
		
		foreach($user->userBank as $row){
			$banks[] = [
                'bank_id'       => $row->bank_id,
                'bank_name'     => $row->bank->code.' - '.$row->bank->name,
                'name'          => $row->name,
                'no'            => $row->no,
                'branch'        => $row->branch,
                'is_default'    => $row->is_default
            ];
		}
		
		$user['banks'] = $banks;

        $datas = [];

        foreach($user->userData as $row){
			$datas[] = [
                'title'     => $row->title,
                'content'   => $row->content
            ];
		}
		
		$user['datas'] = $datas;
        				
		return response()->json($user);
    }

    public function destroy(Request $request){
        $query = User::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new User())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the user data');

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
            'title' => 'USER REPORT',
            'data' => User::where(function ($query) use ($request) {
                if ($request->search) {
                    $query->where(function ($query) use ($request) {
                        $query->where('name', 'like', "%$request->search%")
                            ->orWhere('employee_no', 'like', "%$request->search%")
                            ->orWhere('username', 'like', "%$request->search%")
                            ->orWhere('phone', 'like', "%$request->search%")
                            ->orWhere('address', 'like', "%$request->search%");
                    });
                }
                if($request->status){
                    $query->where('status', $request->status);
                }
                if($request->type){
                    $query->where('type', $request->type);
                }
            })->get()
		];
		
		return view('admin.print.master_data.user', $data);
    }

    public function export(Request $request){
        $search = $request->search ? $request->search : '';
		$status = $request->status ? $request->status : '';
        $type = $request->type ? $request->type : '';
		
		return Excel::download(new ExportUser($search,$status,$type), 'user_'.uniqid().'.xlsx');
    }
}