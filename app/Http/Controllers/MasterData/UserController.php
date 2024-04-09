<?php

namespace App\Http\Controllers\MasterData;
use App\Models\Company;
use App\Models\UserDriver;
use App\Models\SalaryComponent;
use App\Models\EmployeeSalaryComponent;
use App\Models\UserPlace;
use App\Models\UserWarehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserBank;
use App\Models\UserFile;
use App\Models\UserData;
use App\Models\Place;
use App\Models\Warehouse;
use App\Models\Department;
use App\Models\Position;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\Group;
use App\Models\Region;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportUser;
use App\Helpers\CustomHelper;
use App\Imports\ImportUser;
use App\Models\NonStaffCompany;

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
            'position'      => Position::where('status','1')->get(),
            'group'         => Group::where('status','1')->get(['id','name','type'])->toArray(),
            'menu'          => Menu::whereNull('parent_id')->where('status','1')->oldest('order')->get(),
            'province'      => Region::whereRaw("LENGTH(code) = 2")->get(),
            'city'          => Region::whereRaw("LENGTH(code) = 5")->get(),
            'content'       => 'admin.master_data.user'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function companyIndex(Request $request)
    {
        $employee = User::where('employee_type','2')->where('employee_no',CustomHelper::decrypt($request->id))->where('status','1')->first();

        if($employee){
            $data = [
                'title'         => 'Induk Perusahaan Pegawai Non-Staff - '.$employee->employee_no.' - '.$employee->name,
                'content'       => 'admin.master_data.user_company',
                'employee'      => $employee,
                'code'          => $request->id,
            ];

            return view('admin.layouts.index', ['data' => $data]);
        }else{
            abort(404);
        }
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

        $total_data = User::/* where('type','<>','1')-> */count();
        
        $query_data = User::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('name', 'like', "%$search%")
                            ->orWhere('username', 'like', "%$search%")
                            ->orWhere('employee_no', 'like', "%$search%")
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
                if($request->group){
                    $query->whereIn('group_id', $request->group);
                }
            })
            /* ->where('type','<>','1') */
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = User::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('name', 'like', "%$search%")
                            ->orWhere('username', 'like', "%$search%")
                            ->orWhere('employee_no', 'like', "%$search%")
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
                if($request->group){
                    $query->whereIn('group_id', $request->group);
                }
            })
            /* ->where('type','<>','1') */
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $btn = $val->employee_type == '2' ? '<a href="user/parent_company/'.CustomHelper::encrypt($val->employee_no).'" class="btn-floating mb-1 btn-flat waves-effect waves-light purple accent-2 white-text btn-small" data-popup="tooltip" title="Atur Perusahaan Induk"><i class="material-icons dp48">account_balance</i></a> ' : '';

                $btn .= ($val->type == '1' ? '<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Atur Akses Menu/Form" onclick="access(' . $val->id . ',`'.$val->name.'`)"><i class="material-icons dp48">folder_shared</i></button> ' : '').'<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue accent-2 white-text btn-small" data-popup="tooltip" title="Upload lampiran" onclick="attachment(' . $val->id . ')"><i class="material-icons dp48">perm_media</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>';

                if($val->type==1){
                    $position = $val->position()->exists() ? $val->position->name : '<div id="no_position">belum memiliki posisi</div>';
                }else{
                    $position = '-';
                }
                
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->employee_no).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->name,
                    $val->username,
                    $val->employee_no,
                    $val->type(),
                    $val->group()->exists() ? $val->group->name : '-',
                    $position,
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
				'type'	    => ucfirst($row->type),
                'mode'      => $row->mode ? $row->mode : '',
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
        $data   = User::where('employee_no',CustomHelper::decrypt($request->id))->first();

        $banks = [];
        $infos = [];
        $drivers = [];

        foreach($data->userBank as $row){
            $banks[] = $row->bank.' No. rek '.$row->no.' Cab. '.$row->branch.' '.$row->isDefault();
        }

        foreach($data->userData as $row){
            $country = $row->country()->exists() ? $row->country->name : '';
            $province = $row->province()->exists() ? $row->province->name : '';
            $city = $row->city()->exists() ? $row->city->name : '';
            $district = $row->district()->exists() ? $row->district->name : '';
            $subdistrict = $row->subdistrict()->exists() ? $row->subdistrict->name : '';
            $infos[] = $row->title.' '.$row->content.' '.$row->npwp.' '.$row->address.' - '.$subdistrict.' - '.$district.' - '.$city.' - '.$province.' - '.$country;
        }

        foreach($data->userDriver as $row){
            $drivers[] = $row->name.' '.$row->hp;
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
                                <th>Kelurahan</th>
                                <th>'.($data->subdistrict_id ? $data->subdistrict->name : '-').'</th>
                            </tr>
                            <tr>
                                <th>Kecamatan</th>
                                <th>'.($data->district_id ? $data->district->name : '-').'</th>
                            </tr>
                            <tr>
                                <th>Kota/Kabupaten</th>
                                <th>'.($data->city()->exists() ? $data->city->name : '-').'</th>
                            </tr>
                            <tr>
                                <th>Provinsi</th>
                                <th>'.($data->province()->exists() ? $data->province->name : '-').'</th>
                            </tr>
                            <tr>
                                <th>Kota/Kabupaten</th>
                                <th>'.($data->country()->exists() ? $data->country->name : '-').'</th>
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
                                <th>'.date('d/m/Y',strtotime($data->married_date)).'</th>
                            </tr>
                            <tr>
                                <th>Jumlah Anak</th>
                                <th>'.$data->children.'</th>
                            </tr>
                            <tr>
                                <th>Posisi</th>
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
                                <th>'.number_format($data->deposit,0,',','.').'</th>
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
                                <th>Daftar Rekening</th>
                                <th>'.implode('<br>',$banks).'</th>
                            </tr>
                            <tr>
                                <th>Alamat Penagihan</th>
                                <th>'.implode('<br>',$infos).'</th>
                            </tr>
                            <tr>
                                <th>Daftar Supir</th>
                                <th>'.implode('<br>',$drivers).'</th>
                            </tr>
                            <tr>
                                <th>Kelompok</th>
                                <th>'.($data->group()->exists() ? $data->group->name : '-').'</th>
                            </tr>
                            <tr>
                                <th>Terakhir Ubah Password</th>
                                <th>'.$data->last_change_password.'</th>
                            </tr>
                            <tr>
                                <th>Tipe Pegawai</th>
                                <th>'.$data->employeeType().'</th>
                            </tr>
                            <tr>
                                <th>Auto Generate AR Invoice (dari SJ)</th>
                                <th>'.$data->arInvoice().'</th>
                            </tr>
                            <tr>
                                <th>Pengguna Spesial (Kunci Periode)</th>
                                <th>'.$data->isSpecial().'</th>
                            </tr>
                        </thead>
                    </table>';
		
        return response()->json($string);
    }

    public function create(Request $request){
        if($request->type == '1'){
            $validation = Validator::make($request->all(), [
                // 'name' 				=> 'required|uppercase',
                'username'			=> $request->temp ? ['required', Rule::unique('users', 'username')->ignore($request->temp)] : 'required|unique:users,username',
                'employee_no'		=> $request->employee_no ? ($request->temp ? [Rule::unique('users', 'employee_no')->ignore($request->temp)] : 'unique:users,employee_no') : '',
                /* 'phone'		        => $request->temp ? ['required', Rule::unique('users', 'phone')->ignore($request->temp)] : 'required|unique:users,phone', */
                /* 'email'             => $request->temp ? ['required', Rule::unique('users', 'email')->ignore($request->temp)] : 'required|unique:users,email', */
                // 'address'           => 'required',
                'type'              => 'required',
                // 'id_card'           => 'required',
                // 'id_card_address'   => 'required',
                // 'company_id'        => 'required',
                /* 'province_id'       => 'required',
                'city_id'           => 'required',
                'district_id'       => 'required',
                'subdistrict_id'    => 'required', */
                // 'country_id'        => 'required',
                // 'limit_credit'      => 'required',
                // 'employee_type'     => 'required',
                // 'place_id'          => 'required',
            ], [
                // 'name.required' 	            => 'Nama tidak boleh kosong.',
                // 'name.uppercase' 	            => 'Nama harus menggunakan huruf kapital.',
                'username.required'             => 'Username tidak boleh kosong.',
                'username.unique'               => 'Username telah terpakai.',
                'employee_no.unique'            => 'Kode BP / NIK telah terpakai.',
                /* 'phone.required'             => 'Telepon tidak boleh kosong.',
                'phone.unique'                  => 'Telepon telah terpakai.', */
                /* 'email.required'	            => 'Email tidak boleh kosong.',
                'email.unique'                  => 'Email telah terpakai.', */
                // 'address.required'              => 'Alamat tidak boleh kosong.',
                'type.required'	                => 'Tipe pengguna tidak boleh kosong.',
                // 'id_card.required'              => 'No Identitas tidak boleh kosong.',
                // 'id_card_address.required'      => 'Alamat Identitas tidak boleh kosong.',
                // 'company.required'              => 'Perusahaan tidak boleh kosong.',
                /* 'province_id.required'          => 'Provinsi tidak boleh kosong.',
                'city_id.required'              => 'Kota tidak boleh kosong.',
                'district_id.required'          => 'Kecamatan tidak boleh kosong.',
                'subdistrict_id.required'       => 'Kelurahan tidak boleh kosong.', */
                // 'country_id.required'           => 'Negara tidak boleh kosong.',
                // 'limit_credit.required'         => 'Limit BS Karyawan tidak boleh kosong.',
                // 'employee_type.required'        => 'Tipe Pegawai tidak boleh kosong.',
                // 'place_id.required'             => 'Plant pegawai tidak boleh kosong.'
            ]);
        }else{
            $validation = Validator::make($request->all(), [
                'name' 				=> $request->temp ? ['required','uppercase', Rule::unique('users', 'name')->ignore($request->temp)] : 'required|uppercase|unique:users,name',
                /* 'phone'		        => $request->temp ? ['required', Rule::unique('users', 'phone')->ignore($request->temp)] : 'required|unique:users,phone', */
                'employee_no'		=> $request->employee_no ? ($request->temp ? [Rule::unique('users', 'employee_no')->ignore($request->temp)] : 'unique:users,employee_no') : '',
                /* 'email'             => $request->temp ? ['required', Rule::unique('users', 'email')->ignore($request->temp)] : 'required|unique:users,email', */
                /* 'address'           => 'required',
                'type'              => 'required',
                'province_id'       => 'required',
                'city_id'           => 'required',
                'district_id'       => 'required',
                'subdistrict_id'    => 'required',
                'pic'               => 'required',
                'pic_no'            => 'required', */
                'office_no'         => 'required',
                'limit_credit'      => 'required',
                'country_id'        => 'required',
            ], [
                'name.required' 	            => 'Nama tidak boleh kosong.',
                'name.uppercase' 	            => 'Nama harus menggunakan huruf kapital.',
                'name.unique'                   => 'Nama telah terpakai.',
                'phone.required'                => 'Telepon tidak boleh kosong.',
                'phone.unique'                  => 'Telepon telah terpakai.',
                'employee_no.unique'            => 'Kode BP / NIK telah terpakai.',
                /* 'email.required'	            => 'Email tidak boleh kosong.',
                'email.unique'                  => 'Email telah terpakai.', */
                'address.required'              => 'Alamat tidak boleh kosong.',
                'type.required'	                => 'Tipe pengguna tidak boleh kosong.',
                /* 'province_id.required'          => 'Provinsi tidak boleh kosong.',
                'city_id.required'              => 'Kota tidak boleh kosong.',
                'district_id.required'          => 'Kecamatan tidak boleh kosong.',
                'subdistrict_id.required'       => 'Kelurahan tidak boleh kosong.', */
                'pic.required'                  => 'PIC tidak boleh kosong.',
                'pic_no.required'               => 'Nomor PIC tidak boleh kosong.',
                'office_no.required'            => 'Nomor Kantor tidak boleh kosong.',
                'limit_credit.required'         => 'Limit credit tidak boleh kosong.',
                'country_id.required'           => 'Negara tidak boleh kosong.',
            ]);
        }

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            if($request->type == '3' || $request->type == '4'){
                $arrUserAllowedSupplierVendor = ['323020','323002','112005','323004','323014','324001'];
                if(!in_array(session('bo_employee_no'),$arrUserAllowedSupplierVendor)){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Mohon maaf, untuk user yang boleh memasukkan data supplier dan ekspedisi adalah : '.implode(', ',$arrUserAllowedSupplierVendor),
                    ]);
                }
            }

            if($request->type == '1' || $request->type == '2'){
                $arrUserAllowedCustomerEmployee = ['323020'];
                if(in_array(session('bo_employee_no'),$arrUserAllowedCustomerEmployee)){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Mohon maaf, untuk user anda tidak boleh memasukkan data customer dan pegawai.',
                    ]);
                }
            }

            $passed = true;

            if($request->arr_bank){
                foreach($request->arr_bank as $key => $row){
                    if(isset($request->arr_name[$key]) && isset($request->arr_no[$key]) && isset($request->arr_branch[$key])){
                        if($request->arr_name[$key] == '' || $request->arr_no[$key] == '' || $request->arr_branch[$key] == ''){
                            $passed = false;
                        }
                    }else{
                        $passed = false;
                    }
                }
            }

            if(!$passed){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Silahkan cek detail informasi bank rekening anda, tidak boleh ada kosong.'
                ]);
            }

			if($request->temp){
                /* DB::beginTransaction();
                try { */
                    $query = User::find($request->temp);
                    if(!$query->manager_id){
                        $query->manager_id = $request->manager_id;
                    }
                    $query->name            = $request->name;
                    $query->username        = $request->username ? $request->username : NULL;
                    $query->employee_no     = $request->employee_no ? $request->employee_no : $query->employee_no;
                    $query->password        = $request->password ? bcrypt($request->password) : $query->password;
                    $query->phone	        = $request->phone ? $request->phone : NULL;
                    $query->email           = $request->email;
                    $query->address	        = $request->address;
                    $query->type            = $request->type;
                    $query->group_id        = $request->group_id ? $request->group_id : NULL;
                    $query->id_card         = $request->id_card ? $request->id_card : NULL;
                    $query->id_card_address = $request->id_card_address;
                    $query->company_id	    = $request->company_id ? $request->company_id : NULL;
                    $query->place_id	    = $request->type == '1' ? $request->place_id : NULL;
                    $query->province_id     = $request->province_id;
                    $query->city_id         = $request->city_id;
                    $query->district_id     = $request->district_id;
                    $query->subdistrict_id  = $request->subdistrict_id;
                    $query->tax_id          = $request->tax_id;
                    $query->tax_name        = $request->tax_name;
                    $query->tax_address     = $request->tax_address;
                    $query->pic             = $request->pic ? $request->pic : NULL;
                    $query->pic_no          = $request->pic_no ? $request->pic_no : NULL;
                    $query->office_no       = $request->office_no ? $request->office_no : NULL;
                    $query->limit_credit    = $request->limit_credit ? str_replace(',','.',str_replace('.','',$request->limit_credit)) : ($query->limit_credit > 0 ? $query->limit_credit : 0);
                    $query->top             = $request->top;
                    $query->top_internal    = $request->top_internal;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->gender          = $request->gender;
                    $query->married_status  = $request->type == '1' ? $request->married_status : NULL;
                    $query->married_date    = $request->type == '1' ? $request->married_date : NULL;
                    $query->children        = $request->type == '1' ? $request->children : NULL;
                    $query->country_id      = $request->country_id;
                    $query->employee_type   = $request->type == '1' ? $request->employee_type : NULL;
                    $query->is_ar_invoice   = $request->type == '2' ? ($request->is_ar_invoice ? $request->is_ar_invoice : NULL) : NULL;
                    $query->last_change_password =  $request->password ? date('Y-m-d H:i:s') : $query->last_change_password;
                    $query->is_special_lock_user = $request->is_special_lock_user ?? NULL;
                    $query->save();

                    /* DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                } */
			}else{
                /* DB::beginTransaction();
                try { */
                    $query = User::create([
                        'name'			        => $request->name,
                        'employee_no'           => $request->employee_no ? $request->employee_no : User::generateCode($request->type,$request->employee_type,$request->place_id),
                        'username'	            => $request->username ? $request->username : NULL,
                        'password'		        => $request->password ? bcrypt($request->password) : NULL,
                        'phone'	                => $request->phone ? $request->phone : NULL,
                        'email'	                => $request->email ? $request->email : NULL,
                        'address'	            => $request->address ? $request->address : NULL,
                        'type'                  => $request->type,
                        'group_id'              => $request->group_id ? $request->group_id : NULL,
                        'id_card'	            => $request->id_card ? $request->id_card : NULL,
                        'id_card_address'       => $request->id_card_address ? $request->id_card_address: NULL,
                        'company_id'	        => $request->company_id ? $request->company_id : NULL,
                        'place_id'	            => $request->type == '1' ? $request->place_id : NULL,
                        'province_id'	        => $request->province_id ? $request->province_id : NULL,
                        'city_id'               => $request->city_id ? $request->city_id : NULL,
                        'district_id'           => $request->district_id ? $request->district_id : NULL,
                        'subdistrict_id'        => $request->subdistrict_id ? $request->subdistrict_id : NULL,
                        'tax_id'                => $request->tax_id ? $request->tax_id : NULL,
                        'tax_name'              => $request->tax_name ? $request->tax_name : NULL,
                        'tax_address'           => $request->tax_address ? $request->tax_address : NULL,
                        'pic'                   => $request->pic ? $request->pic : NULL,
                        'pic_no'                => $request->pic_no ? $request->pic_no : NULL,
                        'office_no'             => $request->office_no ? $request->office_no : NULL,
                        'limit_credit'          => $request->limit_credit ? str_replace(',','.',str_replace('.','',$request->limit_credit)) : NULL,
                        'count_limit_credit'    => 0,
                        'top'                   => $request->top ? $request->top : NULL,
                        'top_internal'          => $request->top_internal ? $request->top_internal : NULL,
                        'status'                => $request->status ? $request->status : '2',
                        'gender'                => $request->gender ? $request->gender : NULL,
                        'married_status'        => $request->type == '1' ? ($request->married_status ? $request->married_status : NULL) :NULL,
                        'married_date'          => $request->type == '1' ? ($request->married_date ? $request->married_date : NULL) : NULL,
                        'children'              => $request->type == '1' ? ($request->children ? $request->children : NULL) : NULL,
                        'country_id'            => $request->country_id ? $request->country_id : NULL,
                        'connection_id'         => 0,
                        'user_status'           => 'Offline',
                        'employee_type'         => $request->type == '1' ? $request->employee_type : NULL,
                        'is_ar_invoice'         => $request->type == '2' ? ($request->is_ar_invoice ? $request->is_ar_invoice : NULL) : NULL,
                        // 'last_change_password'  => date('Y-m-d H:i:s'),
                        'is_special_lock_user'  => $request->is_special_lock_user ?? NULL,
                    ]);
                    if($request->type == 1){
                        $query_salary_component = SalaryComponent::where('status',1)->get();
                        foreach($query_salary_component as $row_salary_component){
                            $query_save = EmployeeSalaryComponent::create([
                                'user_id'			    => $query->id,
                                'salary_component_id'   => $row_salary_component->id,
                                'nominal'	            => 0
                            ]);
                        }
                    }
                    
                    /* DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                } */
			}
			
			if($query) {
                if($request->arr_bank){
                    $query->userBank()->whereNotIn('id',$request->arr_id_bank)->delete();
                    $checked = isset($request->check) ? intval($request->check) : '';
                    foreach($request->arr_bank as $key => $row){
                        if($request->arr_id_bank[$key]){
                            UserBank::find(intval($request->arr_id_bank[$key]))->update([
                                'user_id'	    => $query->id,
                                'bank'	        => $row,
                                'name'          => $request->arr_name[$key],
                                'no'            => $request->arr_no[$key],
                                'branch'        => $request->arr_branch[$key],
                                'is_default'    => $key == $checked ? '1' : '0',
                            ]);
                        }else{
                            UserBank::create([
                                'user_id'	    => $query->id,
                                'bank'	        => $row,
                                'name'          => $request->arr_name[$key],
                                'no'            => $request->arr_no[$key],
                                'branch'        => $request->arr_branch[$key],
                                'is_default'    => $key == $checked ? '1' : '0',
                            ]);
                        }
                    }
                }

                if($request->arr_title){
                    $query->userData()->whereNotIn('id',$request->arr_id_data)->delete();
                    foreach($request->arr_title as $key => $row){
                        if($request->arr_id_data[$key]){
                            UserData::find(intval($request->arr_id_data[$key]))->update([
                                'title'         => $row,
                                'content'       => isset($request->arr_content[$key]) ? $request->arr_content[$key] : NULL,
                                'npwp'          => $request->arr_npwp[$key] ? $request->arr_npwp[$key] : NULL,
                                'address'       => $request->arr_address[$key] ? $request->arr_address[$key] : NULL,
                                'country_id'    => $request->arr_country[$key] ? $request->arr_country[$key]: NULL,
                                'province_id'   => $request->arr_province[$key] ? $request->arr_province[$key] : NULL,
                                'city_id'       => $request->arr_city[$key] ? $request->arr_city[$key] : NULL,
                                'district_id'   => $request->arr_district[$key] ? $request->arr_district[$key] : NULL,
                                'subdistrict_id'=> $request->arr_subdistrict[$key] ? $request->arr_subdistrict[$key] : NULL,
                            ]);
                        }else{
                            UserData::create([
                                'user_id'	    => $query->id,
                                'title'         => $row,
                                'content'       => isset($request->arr_content[$key]) ? $request->arr_content[$key] : NULL,
                                'npwp'          => $request->arr_npwp[$key] ? $request->arr_npwp[$key] : NULL,
                                'address'       => $request->arr_address[$key] ? $request->arr_address[$key] : NULL,
                                'country_id'    => $request->arr_country[$key] ? $request->arr_country[$key]: NULL,
                                'province_id'   => $request->arr_province[$key] ? $request->arr_province[$key] : NULL,
                                'city_id'       => $request->arr_city[$key] ? $request->arr_city[$key] : NULL,
                                'district_id'   => $request->arr_district[$key] ? $request->arr_district[$key] : NULL,
                                'subdistrict_id'=> $request->arr_subdistrict[$key] ? $request->arr_subdistrict[$key] : NULL,
                            ]);
                        }
                    }
                }

                if($request->arr_driver_name){
                    $query->userDriver()->whereNotIn('id',$request->arr_id_driver)->delete();
                    foreach($request->arr_driver_name as $key => $row){
                        if($request->arr_id_driver[$key]){
                            UserDriver::find(intval($request->arr_id_driver[$key]))->update([
                                'user_id'	    => $query->id,
                                'name'          => $row,
                                'hp'            => $request->arr_driver_hp[$key],
                            ]);
                        }else{
                            UserDriver::create([
                                'user_id'	    => $query->id,
                                'name'          => $row,
                                'hp'            => $request->arr_driver_hp[$key],
                            ]);
                        }
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

            $arrUserNotAllowedEmployeeAccess = ['323020'];
            if(in_array(session('bo_employee_no'),$arrUserNotAllowedEmployeeAccess)){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Mohon maaf, untuk user anda tidak boleh mengubah data akses.',
                ]);
            }
			
            DB::beginTransaction();
            try {
                
                if($request->checkboxView){
                    MenuUser::where('user_id',$request->tempuseraccess)->whereNotIn('menu_id',$request->checkboxView)->where('type','view')->delete();
                    if($request->arr_user){
                        foreach($request->arr_user as $rowuser){
                            MenuUser::where('user_id',$rowuser)->whereNotIn('menu_id',$request->checkboxView)->where('type','view')->delete();
                        }
                    }

                    foreach($request->checkboxView as $row){
                        $cek = MenuUser::where('user_id',$request->tempuseraccess)->where('menu_id',$row)->where('type','view');
                        if($cek->count() == 0){
                            MenuUser::create([
                                'user_id'   => $request->tempuseraccess,
                                'menu_id'   => $row,
                                'type'      => 'view'
                            ]);
                        }else{
                            foreach($cek->get() as $rowcek){
                                $rowcek->update([
                                    'mode'      => NULL,
                                ]);
                            }
                        }

                        if($request->arr_user){
                            foreach($request->arr_user as $rowuser){
                                $cek2 = MenuUser::where('user_id',$rowuser)->where('menu_id',$row)->where('type','view');
                                if($cek2->count() == 0){
                                    MenuUser::create([
                                        'user_id'   => $rowuser,
                                        'menu_id'   => $row,
                                        'type'      => 'view'
                                    ]);
                                }else{
                                    foreach($cek2->get() as $rowcek){
                                        $rowcek->update([
                                            'mode'      => NULL,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }else{
                    MenuUser::where('user_id',$request->tempuseraccess)->where('type','view')->delete();
                    if($request->arr_user){
                        foreach($request->arr_user as $rowuser){
                            MenuUser::where('user_id',$rowuser)->where('type','view')->delete();
                        }
                    }
                }

                if($request->checkboxViewData){
                    foreach($request->checkboxViewData as $row){
                        $cek = MenuUser::where('user_id',$request->tempuseraccess)->where('menu_id',$row)->where('type','view');
                        if($cek->count() > 0){
                            foreach($cek->get() as $rowcek){
                                $rowcek->update([
                                    'mode'  => 'all'
                                ]);
                            }
                        }

                        if($request->arr_user){
                            foreach($request->arr_user as $rowuser){
                                $cek2 = MenuUser::where('user_id',$rowuser)->where('menu_id',$row)->where('type','view');
                                if($cek2->count() > 0){
                                    foreach($cek2->get() as $rowcek){
                                        $rowcek->update([
                                            'mode'  => 'all'
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }

                if($request->checkboxUpdate){
                    MenuUser::where('user_id',$request->tempuseraccess)->whereNotIn('menu_id',$request->checkboxUpdate)->where('type','update')->delete();
                    if($request->arr_user){
                        foreach($request->arr_user as $rowuser){
                            MenuUser::where('user_id',$rowuser)->whereNotIn('menu_id',$request->checkboxUpdate)->where('type','update')->delete();
                        }
                    }

                    foreach($request->checkboxUpdate as $row){
                        $cek = MenuUser::where('user_id',$request->tempuseraccess)->where('menu_id',$row)->where('type','update')->count();
                        if($cek == 0){
                            MenuUser::create([
                                'user_id'   => $request->tempuseraccess,
                                'menu_id'   => $row,
                                'type'      => 'update'
                            ]);
                        }

                        if($request->arr_user){
                            foreach($request->arr_user as $rowuser){
                                $cek2 = MenuUser::where('user_id',$rowuser)->where('menu_id',$row)->where('type','update')->count();
                                if($cek2 == 0){
                                    MenuUser::create([
                                        'user_id'   => $rowuser,
                                        'menu_id'   => $row,
                                        'type'      => 'update'
                                    ]);
                                }
                            }
                        }
                    }
                }else{
                    MenuUser::where('user_id',$request->tempuseraccess)->where('type','update')->delete();
                    if($request->arr_user){
                        foreach($request->arr_user as $rowuser){
                            MenuUser::where('user_id',$rowuser)->where('type','update')->delete();
                        }
                    }
                }

                if($request->checkboxDelete){
                    MenuUser::where('user_id',$request->tempuseraccess)->whereNotIn('menu_id',$request->checkboxDelete)->where('type','delete')->delete();
                    if($request->arr_user){
                        foreach($request->arr_user as $rowuser){
                            MenuUser::where('user_id',$rowuser)->whereNotIn('menu_id',$request->checkboxDelete)->where('type','delete')->delete();
                        }
                    }

                    foreach($request->checkboxDelete as $row){
                        $cek = MenuUser::where('user_id',$request->tempuseraccess)->where('menu_id',$row)->where('type','delete')->count();
                        if($cek == 0){
                            MenuUser::create([
                                'user_id'   => $request->tempuseraccess,
                                'menu_id'   => $row,
                                'type'      => 'delete'
                            ]);
                        }

                        if($request->arr_user){
                            foreach($request->arr_user as $rowuser){
                                $cek2 = MenuUser::where('user_id',$rowuser)->where('menu_id',$row)->where('type','delete')->count();
                                if($cek2 == 0){
                                    MenuUser::create([
                                        'user_id'   => $rowuser,
                                        'menu_id'   => $row,
                                        'type'      => 'delete'
                                    ]);
                                }
                            }
                        }
                    }
                }else{
                    MenuUser::where('user_id',$request->tempuseraccess)->where('type','delete')->delete();
                    if($request->arr_user){
                        foreach($request->arr_user as $rowuser){
                            MenuUser::where('user_id',$rowuser)->where('type','delete')->delete();
                        }
                    }
                }

                if($request->checkboxVoid){
                    MenuUser::where('user_id',$request->tempuseraccess)->whereNotIn('menu_id',$request->checkboxVoid)->where('type','void')->delete();
                    if($request->arr_user){
                        foreach($request->arr_user as $rowuser){
                            MenuUser::where('user_id',$rowuser)->whereNotIn('menu_id',$request->checkboxVoid)->where('type','void')->delete();
                        }
                    }

                    foreach($request->checkboxVoid as $row){
                        $cek = MenuUser::where('user_id',$request->tempuseraccess)->where('menu_id',$row)->where('type','void')->count();
                        if($cek == 0){
                            MenuUser::create([
                                'user_id'   => $request->tempuseraccess,
                                'menu_id'   => $row,
                                'type'      => 'void'
                            ]);
                        }

                        if($request->arr_user){
                            foreach($request->arr_user as $rowuser){
                                $cek2 = MenuUser::where('user_id',$rowuser)->where('menu_id',$row)->where('type','void')->count();
                                if($cek2 == 0){
                                    MenuUser::create([
                                        'user_id'   => $rowuser,
                                        'menu_id'   => $row,
                                        'type'      => 'void'
                                    ]);
                                }
                            }
                        }
                    }
                }else{
                    MenuUser::where('user_id',$request->tempuseraccess)->where('type','void')->delete();
                    if($request->arr_user){
                        foreach($request->arr_user as $rowuser){
                            MenuUser::where('user_id',$rowuser)->where('type','void')->delete();
                        }
                    }
                }

                if($request->checkboxJournal){
                    MenuUser::where('user_id',$request->tempuseraccess)->whereNotIn('menu_id',$request->checkboxJournal)->where('type','journal')->delete();
                    if($request->arr_user){
                        foreach($request->arr_user as $rowuser){
                            MenuUser::where('user_id',$rowuser)->whereNotIn('menu_id',$request->checkboxJournal)->where('type','journal')->delete();
                        }
                    }

                    foreach($request->checkboxJournal as $row){
                        $cek = MenuUser::where('user_id',$request->tempuseraccess)->where('menu_id',$row)->where('type','journal')->count();
                        if($cek == 0){
                            MenuUser::create([
                                'user_id'   => $request->tempuseraccess,
                                'menu_id'   => $row,
                                'type'      => 'journal'
                            ]);
                        }

                        if($request->arr_user){
                            foreach($request->arr_user as $rowuser){
                                $cek2 = MenuUser::where('user_id',$rowuser)->where('menu_id',$row)->where('type','journal')->count();
                                if($cek2 == 0){
                                    MenuUser::create([
                                        'user_id'   => $rowuser,
                                        'menu_id'   => $row,
                                        'type'      => 'journal'
                                    ]);
                                }
                            }
                        }
                    }
                }else{
                    MenuUser::where('user_id',$request->tempuseraccess)->where('type','journal')->delete();
                    if($request->arr_user){
                        foreach($request->arr_user as $rowuser){
                            MenuUser::where('user_id',$rowuser)->where('type','journal')->delete();
                        }
                    }
                }

                if($request->checkplace){
                    UserPlace::where('user_id',$request->tempuseraccess)->whereNotIn('place_id',$request->checkplace)->delete();
                    if($request->arr_user){
                        foreach($request->arr_user as $rowuser){
                            UserPlace::where('user_id',$rowuser)->whereNotIn('place_id',$request->checkplace)->delete();
                        }
                    }

                    foreach($request->checkplace as $row){
                        $cek = UserPlace::where('user_id',$request->tempuseraccess)->where('place_id',$row)->count();
                        if($cek == 0){
                            UserPlace::create([
                                'user_id'   => $request->tempuseraccess,
                                'place_id'  => $row
                            ]);
                        }

                        if($request->arr_user){
                            foreach($request->arr_user as $rowuser){
                                $cek2 = UserPlace::where('user_id',$rowuser)->where('place_id',$row)->count();
                                if($cek2 == 0){
                                    UserPlace::create([
                                        'user_id'   => $rowuser,
                                        'place_id'  => $row
                                    ]);
                                }
                            }
                        }
                    }
                }else{
                    UserPlace::where('user_id',$request->tempuseraccess)->delete();
                    if($request->arr_user){
                        foreach($request->arr_user as $rowuser){
                            UserPlace::where('user_id',$rowuser)->delete();
                        }
                    }
                }

                if($request->checkwarehouse){
                    UserWarehouse::where('user_id',$request->tempuseraccess)->whereNotIn('warehouse_id',$request->checkwarehouse)->delete();
                    if($request->arr_user){
                        foreach($request->arr_user as $rowuser){
                            UserWarehouse::where('user_id',$rowuser)->whereNotIn('warehouse_id',$request->checkwarehouse)->delete();
                        }
                    }

                    foreach($request->checkwarehouse as $row){
                        $cek = UserWarehouse::where('user_id',$request->tempuseraccess)->where('warehouse_id',$row)->count();
                        if($cek == 0){
                            UserWarehouse::create([
                                'user_id'       => $request->tempuseraccess,
                                'warehouse_id'  => $row
                            ]);
                        }

                        if($request->arr_user){
                            foreach($request->arr_user as $rowuser){
                                $cek2 = UserWarehouse::where('user_id',$rowuser)->where('warehouse_id',$row)->count();
                                if($cek2 == 0){
                                    UserWarehouse::create([
                                        'user_id'       => $rowuser,
                                        'warehouse_id'  => $row
                                    ]);
                                }
                            }
                        }
                    }
                }else{
                    UserWarehouse::where('user_id',$request->tempuseraccess)->delete();
                    if($request->arr_user){
                        foreach($request->arr_user as $rowuser){
                            UserWarehouse::where('user_id',$rowuser)->delete();
                        }
                    }
                }

                activity()
                    ->performedOn(new MenuUser())
                    ->causedBy(session('bo_id'))
                    ->log('Add / edit access data.');

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
        $user['province_name'] = $user->province()->exists() ? $user->province->code.' - '.$user->province->name : '';
        $user['city_name'] = $user->city()->exists() ? $user->city->code.' - '.$user->city->name : '';
        $user['country_name'] = $user->country()->exists() ? $user->country->name : '';
        $user['limit_credit'] = $user->limit_credit ? number_format($user->limit_credit, 0, ',', '.') : '';
        $user['cities'] = $user->province()->exists() ? $user->province->getCity() : '';
        $user['has_document'] = $user->hasDocument() ? '1' : '';

        $banks = [];
		
		foreach($user->userBank as $row){
			$banks[] = [
                'id'            => $row->id,
                'bank'          => $row->bank,
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
                'id'                => $row->id,
                'title'             => $row->title,
                'content'           => $row->content,
                'npwp'              => $row->npwp,
                'address'           => $row->address,
                'country_id'        => $row->country_id ? $row->country_id : '',
                'country_name'      => $row->country()->exists() ? $row->country->code.' - '.$row->country->name : '',
                'province_id'       => $row->province_id ? $row->province_id : '',
                'city_id'           => $row->city_id ? $row->city_id : '',
                'city_name'         => $row->city()->exists() ? $row->city->code.' - '.$row->city->name : '',
                'district_id'       => $row->district_id ? $row->district_id : '',
                'district_name'     => $row->district()->exists() ? $row->district->code.' - '.$row->district->name : '',
                'subdistrict_id'    => $row->subdistrict_id ? $row->subdistrict_id : '',
                'subdistrict_name'  => $row->subdistrict()->exists() ? $row->subdistrict->code.' - '.$row->subdistrict->name : '',
            ];
		}
		
		$user['datas'] = $datas;

        $drivers = [];

        foreach($user->userDriver as $row){
            $drivers[] = [
                'id'    => $row->id,
                'name'  => $row->name,
                'hp'    => $row->hp,
            ];
        }

        $user['drivers'] = $drivers;
        				
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

        $validation = Validator::make($request->all(), [
            'arr_id'                => 'required',
        ], [
            'arr_id.required'       => 'Tolong pilih Item yang ingin di print terlebih dahulu.',
        ]);
        
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            $pr=[];
            $currentDateTime = Date::now();
            $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
            foreach($request->arr_id as $key =>$row){
                $pr[]= User::where('employee_no',$row)->first();

            }
            $data = [
                'title'     => 'Master Item Group',
                'data'      => $pr
            ];  
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path);
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
            $pdf = Pdf::loadView('admin.print.master_data.user', $data)->setPaper('a5', 'landscape');
            $pdf->render();
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            $content = $pdf->download()->getOriginalContent();


            $randomString = Str::random(10); 

         
            $filePath = 'public/pdf/' . $randomString . '.pdf';
            

            Storage::put($filePath, $content);
            
            $document_po = asset(Storage::url($filePath));
            $var_link=$document_po;

            $response =[
                'status'=>200,
                'message'  =>$var_link
            ];
        }
        
		
		return response()->json($response);

    }

    public function export(Request $request){
        $search = $request->search ? $request->search : '';
		$status = $request->status ? $request->status : '';
        $type = $request->type ? $request->type : '';
        $group = $request->group ? $request->group : '';
		
		return Excel::download(new ExportUser($search,$status,$type,$group), 'user_'.uniqid().'.xlsx');
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'mimes:xlsx',
                'max:2048',
                function ($attribute, $value, $fail) {
                    $rows = Excel::toArray([], $value)[0];
                    if (count($rows) < 2) {
                        $fail('The file must contain at least two rows.');
                    }
                }
            ]
        ]);

        if ($validator->fails()) {
            $response = [
                'status' => 432,
                'error'  => $validator->errors()
            ];
            return response()->json($response);
        }

        try {
            Excel::import(new ImportUser, $request->file('file'));

            return response()->json([
                'status'    => 200,
                'message'   => 'Import sukses!'
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values(),
                ];
            }
            $response = [
                'status' => 422,
                'error'  => $errors
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            $response = [
                'status'  => 500,
                'message' => "Data failed to save : ".$e->getMessage()
            ];
            return response()->json($response);
        }
    }

    public function companyDatatable(Request $request){
        $employee_no = CustomHelper::decrypt($request->employee);

        $column = [
            'id',
            'code',
            'vendor_id',
            'post_date',
            'document_no',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = NonStaffCompany::whereHas('account',function($query)use($employee_no){
            $query->where('employee_no',$employee_no);
        })->count();
        
        $query_data = NonStaffCompany::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('document_no', 'like', "%$search%")
                            ->orWhere('note','like',"%$search%")
                            ->orWhereHas('vendor',function($query)use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->whereHas('account',function($query)use($employee_no){
                $query->where('employee_no',$employee_no);
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = NonStaffCompany::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('document_no', 'like', "%$search%")
                            ->orWhere('note','like',"%$search%")
                            ->orWhereHas('vendor',function($query)use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->whereHas('account',function($query)use($employee_no){
                $query->where('employee_no',$employee_no);
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    $nomor,
                    $val->code,
                    $val->vendor->employee_no.' - '.$val->vendor->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->document_no,
                    $val->note,
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

    public function createCompany(Request $request){
        $validation = Validator::make($request->all(), [
            'vendor_id'             => 'required',
            'user_code'             => 'required',
            'date'                  => 'required',
        ], [
            'vendor_id.required'    => 'Vendor tidak boleh kosong.',
            'user_code.required'    => 'Pengguna tidak boleh kosong.',
            'date.required'         => 'Tanggal tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            $employee = User::where('employee_no',CustomHelper::decrypt($request->user_code))->first();

            if(!$employee){
                return response()->json([
                    'status'	=> 500,
                    'message'	=> 'Picture not found.'
                ]);
            }

			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = NonStaffCompany::find($request->temp);
                    $query->user_id	        = session('bo_id');
                    $query->account_id      = $employee->id;
                    $query->vendor_id       = $request->vendor_id;
                    $query->post_date       = $request->date;
                    $query->document_no     = $request->document_no;
                    $query->note            = $request->note;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = NonStaffCompany::create([
                        'code'          => strtoupper(Str::random(15)),
                        'user_id'       => session('bo_id'),
                        'account_id'    => $employee->id,
                        'vendor_id'     => $request->vendor_id,
                        'post_date'     => $request->date,
                        'document_no'   => $request->document_no,
                        'note'			=> $request->note,
                        'status'        => $request->status ? $request->status : '2'
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                activity()
                    ->performedOn(new NonStaffCompany())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit company non-staff.');

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

    public function showCompany(Request $request){
        $nsc = NonStaffCompany::find($request->id);
        $nsc['vendor_name'] = $nsc->vendor->name;
        				
		return response()->json($nsc);
    }

    public function destroyCompany(Request $request){
        $query = NonStaffCompany::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new NonStaffCompany())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the non staff company');

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
}