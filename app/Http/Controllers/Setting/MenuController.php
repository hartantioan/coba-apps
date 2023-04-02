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
use App\Models\Menu;
use App\Models\Approval;
use App\Models\ApprovalTable;
use App\Models\ApprovalTableDetail;
use App\Models\User;
use App\Models\Position;
use App\Models\Department;
use App\Models\MenuUser;

class MenuController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Menu',
            'menus'     => Menu::whereNull('parent_id')->get(),
            'content'   => 'admin.setting.menu'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'name',
            'url',
            'icon',
            'table_name',
            'parent',
            'order',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Menu::count();
        
        $query_data = Menu::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('name', 'like', "%$search%")
                            ->orWhere('url', 'like', "%$search%")
                            ->orWhere('icon', 'like', "%$search%")
                            ->orWhere('table_name', 'like', "%$search%")
                            ->orWhere('order', 'like', "%$search%")
                            ->orWhereHas('parentSub', function ($query) use ($search) {
                                $query->where('name', 'like', "%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = Menu::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('name', 'like', "%$search%")
                            ->orWhere('url', 'like', "%$search%")
                            ->orWhere('icon', 'like', "%$search%")
                            ->orWhere('table_name', 'like', "%$search%")
                            ->orWhere('order', 'like', "%$search%")
                            ->orWhereHas('parentSub', function ($query) use ($search) {
                                $query->where('name', 'like', "%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    $val->id,
                    $val->name,
                    $val->url,
                    '<i class="material-icons dp48">'.$val->icon.'</i>',
                    $val->table_name,
                    $val->parentsub()->exists() ? $val->parentSub->name : 'None',
                    $val->order,
                    $val->status(),
                    $val->isMaintenance(),
                    !$val->sub()->exists() ?
                    '
                        <a href="'.url('admin/setting/menu/operation_access').'/'.$val->id.'" class="btn-floating mb-1 btn-flat waves-effect waves-light purple accent-2 white-text" data-popup="tooltip" title="Edit hak akses operasional halaman"><i class="material-icons dp48">folder_shared</i></a>
					' : '',
                    !$val->sub()->exists() ?
                    '
                        <a href="'.url('admin/setting/menu/approval_map').'/'.$val->id.'" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan accent-2 white-text" data-popup="tooltip" title="Edit hak akses operasional halaman"><i class="material-icons dp48">thumbs_up_down</i></a>
					' : '',
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
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
			'name' 				=> 'required',
			'url'			    =>  $request->temp ? ['required', Rule::unique('menus', 'url')->ignore($request->temp)] : 'required|unique:menus,url',
			'icon'		        => 'required',
			'order'		        => 'required',
		], [
			'name.required' 					=> 'Nama menu tidak boleh kosong.',
			'url.required' 					    => 'Url tidak boleh kosong.',
            'url.unique'                        => 'Url telah terpakai',
			'icon.required'			            => 'Icon tidak boleh kosong.',
			'order.required'				    => 'Urutan tidak boleh kosong',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            if($request->parent_id){
                $parent = Menu::find($request->parent_id);

                if($parent->menuUser()->exists()){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'The parent menu already have(s) operation access rules, please delete it to continue add this menu as parent.'
                    ]);
                }
            }

			if($request->temp){
                if($request->table_name){
                    $cek = Menu::where('table_name',$request->table_name)->where('id','<>',$request->temp)->first();

                    if($cek){
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Maaf. Tabel telah terpakai.'
                        ]);
                    }
                }

                DB::beginTransaction();
                try {
                    $query = Menu::find($request->temp);
                    $query->name = $request->name;
                    $query->url = $request->url;
                    $query->icon = $request->icon;
                    $query->table_name = $request->table_name;
                    $query->parent_id = $request->parent_id ? $request->parent_id : NULL;
                    $query->order = $request->order;
                    $query->status = $request->status ? $request->status : '2';
                    $query->is_maintenance = $request->maintenance ? $request->maintenance : NULL;
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

			}else{

                if($request->table_name){
                    $cek = Menu::where('table_name',$request->table_name)->first();

                    if($cek){
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Maaf. Tabel telah terpakai.'
                        ]);
                    }
                }

                DB::beginTransaction();
                try {
                    $query = Menu::create([
                        'name'			    => $request->name,
                        'url'			    => $request->url,
                        'icon'		        => $request->icon,
                        'table_name'	    => $request->table_name,
                        'parent_id'	        => $request->parent_id ? $request->parent_id : NULL,
                        'order'             => $request->order,
                        'status'            => $request->status ? $request->status : '2',
                        'is_maintenance'    => $request->maintenance ? $request->maintenance : '2'
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                activity()
                    ->performedOn(new Menu())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit menu.');

                $newdata = [];

                $newdata[] = '<option value="">Parent (Utama)</option>';

                foreach(Menu::whereNull('parent_id')->get() as $m){
                    $newdata[] = '<option value="'.$m->id.'">'.$m->name.'</option>';
                    foreach($m->sub as $m2){
                        $newdata[] = '<option value="'.$m2->id.'"> - '.$m2->name.'</option>';
                        foreach($m2->sub as $m3){
                            $newdata[] = '<option value="'.$m3->id.'"> - - '.$m3->name.'</option>';
                            foreach($m3->sub as $m4){
                                $newdata[] = '<option value="'.$m4->id.'"> - - - '.$m4->name.'</option>';
                            }
                        }
                    }
                }

				$response = [
					'status'    => 200,
					'message'   => 'Data successfully saved.',
                    'data'      => $newdata
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
        $menu = Menu::find($request->id);
        $menu['parent_id'] = $menu['parent_id'] ? $menu['parent_id'] : '';
        				
		return response()->json($menu);
    }

    public function getMenus(Request $request){
        $listItems = [];

        foreach(Menu::where('status','1')->get() as $row){
            ///if(!$row->sub()->exists()){
                $listItems[] = [
                    'url'       => !$row->sub()->exists() ? url('admin').'/'.$row->fullUrl() : 'javascript:void(0);',
                    'name'      => $row->name,
                    'icon'      => $row->icon,
                    'category'  => $row->parentsub()->exists() ? $row->parentsub->name : 'Parent Pages'
                ];
            //}
        }

        return response()->json([
            'status'    => 200,
            'listItems' => $listItems
        ]);
    }

    public function destroy(Request $request){
        $query = Menu::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new Menu())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the menu data');

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

    public function operationAccessIndex(Request $request, $id){
        $menu = Menu::find($id);

        $data = [
            'title'     => 'Pengaturan Akses Transaksi',
            'menu'      => $menu,
            'user'      => User::join('departments','departments.id','=','users.department_id')->select('departments.name as department_name','users.*')->orderBy('department_name')->get(),
            'content'   => 'admin.setting.menu_operation_access'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function operationAccessCreate(Request $request){
        $menu = $request->id;
        $val = $request->val;
        $user = $request->ps;
        $type = $request->tp;

        $cekmenu = Menu::find($menu);

        if (!$cekmenu->sub()->exists()) {

            $query = MenuUser::where('menu_id', $menu)->where('user_id', $user)->where('type', $type)->first();

            if ($query) {
                if ($val) {

                } else {
                    $query->delete();
                }
            } else {
                if ($val) {
                    DB::beginTransaction();
                    try {
                        MenuUser::create([
                            'menu_id'       => $menu,
                            'user_id'   => $user,
                            'type'          => $type
                        ]);
                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollback();
                    }                    
                }
            }

            $response = [
                'status' => 200,
                'message' => 'Data updated successfully.'
            ];

        }else{
            $response = [
                'status' => 500,
                'message' => 'Data failed to update. This menu is not meant to be.'
            ];
        }

        return response()->json($response);
    }

    public function approvalAccessIndex(Request $request, $id){
        $menu = Menu::find($id);

        $data = [
            'title'     => 'Pengaturan Approval Dokumen Menu - '.$menu->fullName(),
            'menu'      => $menu,
            'approval'  => Approval::where('status','1')->get(),
            'position'  => Position::where('status','1')->get(),
            'content'   => 'admin.setting.menu_approval_access'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function approvalAccessDatatable(Request $request,$id){
        $column = [
            'id',
            'code',
            'approval_id',
            'user_id',
            'position_id',
            'level',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ApprovalTable::where('menu_id',$id)->count();
        
        $query_data = ApprovalTable::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('level', 'like', "%$search%")
                            ->orWhereHas('approval',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('document_text','like',"%$search%");
                            })
                            ->orWhereHas('position',function($query) use($search){
                                $query->where('name','like',"%$search%");
                            })
                            ->orWhereHas('approvalTableDetail', function($query) use($search){
                                $query->orWhereHas('user',function($query) use($search){
                                    $query->where('name','like',"%$search%");
                                });
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

            })
            ->where('menu_id',$id)
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = ApprovalTable::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('level', 'like', "%$search%")
                            ->orWhereHas('approval',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('document_text','like',"%$search%");
                            })
                            ->orWhereHas('position',function($query) use($search){
                                $query->where('name','like',"%$search%");
                            })
                            ->orWhereHas('approvalTableDetail', function($query) use($search){
                                $query->orWhereHas('user',function($query) use($search){
                                    $query->where('name','like',"%$search%");
                                });
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-id="' . $val->id . '"><i class="material-icons">add</i></button>',
                    $val->code,
                    $val->approval->name.' - '.$val->approval->document_text,
                    $val->level,
                    $val->is_check_nominal ? 'Ya' : 'Tidak',
                    $val->is_check_nominal ? $val->sign : '',
                    $val->is_check_nominal ? number_format($val->nominal,3,',','.') : '',
                    $val->min_approve,
                    $val->min_reject,
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

    public function approvalAccessCreate(Request $request){
        if($request->is_check_nominal){
            $validation = Validator::make($request->all(), [
                'code' 				=> $request->temp ? ['required', Rule::unique('approval_tables', 'code')->ignore($request->temp)] : 'required|unique:approval_tables,code',
                'approval_id'       => 'required',
                'level'             => 'required',
                'is_check_nominal'  => 'required',
                'sign'              => 'required',
                'nominal'           => 'required',
                'min_approve'       => 'required',
                'min_reject'        => 'required',
                'arr_user'          => 'required|array'
            ], [
                'code.required' 	        => 'Kode tidak boleh kosong.',
                'code.unique' 	            => 'Kode telah terpakai.',
                'approval_id.required' 	    => 'Tipe Approval tidak boleh kosong.',
                'level.required'	        => 'Level tidak boleh kosong.',
                'is_check_nominal.required' => 'is Check nominal tidak boleh kosong.',
                'sign.required'             => 'Tanda matematika tidak boleh kosong.',
                'nominal.required'          => 'Nominal tidak boleh kosong.',
                'min_approve.required'      => 'Minimal approve tidak boleh kosong.',
                'min_reject.required'       => 'Minimal reject tidak boleh kosong.',
                'arr_user.required'         => 'Data karyawan tidak boleh kosong.',
                'arr_user.array'            => 'Data karyawan harus array'
            ]);
        }else{
            $validation = Validator::make($request->all(), [
                'code' 				=> $request->temp ? ['required', Rule::unique('approval_tables', 'code')->ignore($request->temp)] : 'required|unique:approval_tables,code',
                'approval_id'       => 'required',
                'level'             => 'required',
                'min_approve'       => 'required',
                'min_reject'        => 'required',
                'arr_user'          => 'required|array'
            ], [
                'code.required' 	        => 'Kode tidak boleh kosong.',
                'code.unique' 	            => 'Kode telah terpakai.',
                'approval_id.required' 	    => 'Tipe Approval tidak boleh kosong.',
                'level.required'	        => 'Level tidak boleh kosong.',
                'min_approve.required'      => 'Minimal approve tidak boleh kosong.',
                'min_reject.required'       => 'Minimal reject tidak boleh kosong.',
                'arr_user.required'         => 'Data karyawan tidak boleh kosong.',
                'arr_user.array'            => 'Data karyawan harus array'
            ]);
        }
        
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            if($request->is_check_nominal){
                $menu = Menu::find($request->tempMenu);

                if(!Schema::hasColumn($menu->table_name, 'grandtotal')){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Ups, menu ini tidak memiliki field `grandtotal`, silahkan hubungi tim EDP.'
                    ]);
                }
            }

            $menu = Menu::find($request->tempMenu);

            if($request->temp){
                DB::beginTransaction();
                try {
                    $query = ApprovalTable::find($request->temp);
                    $query->code = $request->code;
                    $query->approval_id = $request->approval_id;
                    $query->menu_id = $request->tempMenu;
                    $query->table_name = $menu->table_name ? $menu->table_name : NULL;
                    $query->level = $request->level;
                    $query->is_check_nominal = $request->is_check_nominal ? $request->is_check_nominal : NULL;
                    $query->sign = $request->is_check_nominal ? $request->sign : NULL;
                    $query->nominal = $request->is_check_nominal ? str_replace(',','.',str_replace('.','',$request->nominal)) : NULL;
                    $query->status = $request->status ? $request->status : '2';
                    $query->min_approve = $request->min_approve;
                    $query->min_reject = $request->min_reject;
                    $query->save();

                    foreach($query->approvalTableDetail as $row){
                        $row->delete();
                    }

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

            }else{
                $cek = ApprovalTable::where('table_name',$menu->table_name)->where('level',$request->level)->count();

                if($cek > 0){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Ups, satu table tidak bisa memiliki lebih dari 1 level yang sama.'
                    ]);
                }

                DB::beginTransaction();
                try {
                    $query = ApprovalTable::create([
                        'code'			        => $request->code,
                        'approval_id'			=> $request->approval_id,
                        'menu_id'               => $request->tempMenu,
                        'table_name'            => $menu->table_name ? $menu->table_name : NULL,
                        'level'                 => $request->level,
                        'is_check_nominal'      => $request->is_check_nominal ? $request->is_check_nominal : NULL,
                        'sign'                  => $request->is_check_nominal ? $request->sign : NULL,
                        'nominal'               => $request->is_check_nominal ? str_replace(',','.',str_replace('.','',$request->nominal)) : NULL,
                        'status'                => $request->status ? $request->status : '2',
                        'min_approve'           => $request->min_approve,
                        'min_reject'            => $request->min_reject
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
            }
            
            if($query) {

                foreach($request->arr_user as $key => $row){
                    DB::beginTransaction();
                    try {
                        ApprovalTableDetail::create([
                            'approval_table_id'     => $query->id,
                            'user_id'               => $row,
                        ]);
                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollback();
                    }
                }

                activity()
                    ->performedOn(new ApprovalTable())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit approval rules table data.');

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

    public function approvalAccessShow(Request $request){
        $approval = ApprovalTable::find($request->id);
        $approval['user_id'] = $approval->user_id ? $approval->user_id : '';
        $approval['user_name'] = $approval->user_id ? $approval->user->name : '';
        $approval['nominal'] = $approval->is_check_nominal ? number_format($approval->nominal,3,',','.') : '';
        
        $details = [];

        foreach($approval->approvalTableDetail as $row){
            $details[] = [
                'user_id'   => $row->user_id,
                'user_name' => $row->user->name.' - '.$row->user->phone.' Pos. '.$row->user->position->name.' Dep. '.$row->user->department->name,
            ];
        }

        $approval['details'] = $details;
        				
		return response()->json($approval);
    }

    public function approvalAccessRowDetail(Request $request){
        $data   = ApprovalTable::find($request->id);
        
        $string = '<div class="row"><div class="col s12 mt-2"><table style="max-width:500px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="'.count($data->approvalTableDetail).'">Approval</th>
                            ';

        foreach($data->approvalTableDetail as $key => $row){                
            $string .= '<th class="center-align">'.$row->user->profilePicture().'<br>'.$row->user->name.'</th>';
        }

        $string .= '</tr></thead></table></div></div>';
		
        return response()->json($string);
    }

    public function approvalAccessDestroy(Request $request){
        $query = ApprovalTable::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new ApprovalTable())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the approval table rules data');

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

    public function getPageStatusMaintenance(Request $request){

        $query = Menu::where('url',$request->value)->first();
		
        if($query) {
            if($query->is_maintenance){
                $response = [
                    'status'    => 300,
                    'title'     => 'Halaman sedang dalam perbaikan!',
                    'message'   => 'Mohon maaf, halaman sedang dalam perbaikan, mohon untuk tidak diakses. Terima kasih.'
                ];
            }else{
                $response = [
                    'status'    => 200,
                    'title'     => '',
                    'message'   => ''
                ];
            }
        }else{
            $response = [
                'status'    => 200,
                'title'     => '',
                'message'   => ''
            ];
        }

        return response()->json($response);
    }
}
