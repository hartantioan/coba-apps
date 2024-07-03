<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\User;
use App\Models\UserWarehouse;
use App\Models\Place;
use App\Models\WareHouse;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Support\Facades\Validator;
use App\Models\UserPlace;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class MenuUserController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Hak Akses Menu',
            'content'   => 'admin.master_data.menu_user',
            'place'         => Place::where('status','1')->get(),
            'warehouse'     => Warehouse::where('status','1')->get(),
            'department'    => Department::where('status','1')->get(),
            'position'      => Position::where('status','1')->get(),
            'menu'      => Menu::whereNull('parent_id')->where('status','1')->oldest('order')->get(),
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

        $total_data = User::where('type','1')->count();
        
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
            ->where('type','1')
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
            ->where('type','1')
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as  $key=>$val) {
				
                $btn =  '';

                $btn .= '<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Atur Akses Menu/Form" onclick="access(' . $val->id . ',`'.$val->name.'`)"><i class="material-icons dp48">folder_shared</i></button> ';

                if($val->type==1){
                    $position = $val->position()->exists() ? $val->position->name : '<div id="no_position">belum memiliki posisi</div>';
                }else{
                    $position = '-';
                }
                
                $response['data'][] = [
                    $nomor,
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

                if($request->checkboxReport){
                    MenuUser::where('user_id',$request->tempuseraccess)->whereNotIn('menu_id',$request->checkboxReport)->where('type','report')->delete();
                    if($request->arr_user){
                        foreach($request->arr_user as $rowuser){
                            MenuUser::where('user_id',$rowuser)->whereNotIn('menu_id',$request->checkboxReport)->where('type','report')->delete();
                        }
                    }

                    foreach($request->checkboxReport as $row){
                        $cek = MenuUser::where('user_id',$request->tempuseraccess)->where('menu_id',$row)->where('type','report');
                        if($cek->count() == 0){
                            MenuUser::create([
                                'user_id'   => $request->tempuseraccess,
                                'menu_id'   => $row,
                                'type'      => 'report'
                            ]);
                        }else{
                            foreach($cek->get() as $rowcek){
                                $rowcek->update([
                                    'mode'          => NULL,
                                    'show_nominal'  => NULL,
                                ]);
                            }
                        }

                        if($request->arr_user){
                            foreach($request->arr_user as $rowuser){
                                $cek2 = MenuUser::where('user_id',$rowuser)->where('menu_id',$row)->where('type','report');
                                if($cek2->count() == 0){
                                    MenuUser::create([
                                        'user_id'   => $rowuser,
                                        'menu_id'   => $row,
                                        'type'      => 'report'
                                    ]);
                                }else{
                                    foreach($cek2->get() as $rowcek){
                                        $rowcek->update([
                                            'mode'          => NULL,
                                            'show_nominal'  => NULL,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }else{
                    MenuUser::where('user_id',$request->tempuseraccess)->where('type','report')->delete();
                    if($request->arr_user){
                        foreach($request->arr_user as $rowuser){
                            MenuUser::where('user_id',$rowuser)->where('type','report')->delete();
                        }
                    }
                }

                if($request->checkboxReportData){
                    foreach($request->checkboxReportData as $row){
                        $cek = MenuUser::where('user_id',$request->tempuseraccess)->where('menu_id',$row)->where('type','report');
                        if($cek->count() > 0){
                            foreach($cek->get() as $rowcek){
                                $rowcek->update([
                                    'mode'  => 'all'
                                ]);
                            }
                        }

                        if($request->arr_user){
                            foreach($request->arr_user as $rowuser){
                                $cek2 = MenuUser::where('user_id',$rowuser)->where('menu_id',$row)->where('type','report');
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

                if($request->checkboxShowNominal){
                    foreach($request->checkboxShowNominal as $row){
                        $cek = MenuUser::where('user_id',$request->tempuseraccess)->where('menu_id',$row)->where('type','report');
                        if($cek->count() > 0){
                            foreach($cek->get() as $rowcek){
                                $rowcek->update([
                                    'show_nominal'  => '1'
                                ]);
                            }
                        }

                        if($request->arr_user){
                            foreach($request->arr_user as $rowuser){
                                $cek2 = MenuUser::where('user_id',$rowuser)->where('menu_id',$row)->where('type','report');
                                if($cek2->count() > 0){
                                    foreach($cek2->get() as $rowcek){
                                        $rowcek->update([
                                            'show_nominal'  => '1'
                                        ]);
                                    }
                                }
                            }
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
				'menu_id'	    => $row->menu_id,
				'type'	        => ucfirst($row->type),
                'mode'          => $row->mode ?? '',
                'show_nominal'  => $row->show_nominal ?? '',
			];
		}

        $result = [
            'menus'         => $menus,
            'places'        => $places,
            'warehouses'    => $warehouses
        ];
		
		return response()->json($result);
	}

    public function saveAccessBatch(Request $request){
      
        DB::beginTransaction();
        foreach ($request->user as $value) {
            $user_temp = User::where('employee_no',$value)->first();
            if($request->view == 1){
                MenuUser::where('user_id',$user_temp->id)->whereNotIn('menu_id',$request->menu)->where('type','view')->delete();
            }
            if($request->update == 1){
                MenuUser::where('user_id',$user_temp->id)->whereNotIn('menu_id',$request->menu)->where('type','update')->delete();
            }
            if($request->voids == 1){
                MenuUser::where('user_id',$user_temp->id)->whereNotIn('menu_id',$request->menu)->where('type','void')->delete();
            }
            if($request->deletes == 1){
                MenuUser::where('user_id',$user_temp->id)->whereNotIn('menu_id',$request->menu)->where('type','delete')->delete();
            }
            if($request->journal == 1){
                MenuUser::where('user_id',$user_temp->id)->whereNotIn('menu_id',$request->menu)->where('type','void')->delete();
            }
            if($request->report == 1){
                MenuUser::where('user_id',$user_temp->id)->whereNotIn('menu_id',$request->menu)->where('type','journal')->delete();
            }
            foreach($request->menu as $row){
                if($request->view == 1){
                    $cek = MenuUser::where('user_id',$user_temp->id,)->where('menu_id',$row)->where('type','view');
                    if($cek->count() == 0){
                        MenuUser::create([
                            'user_id'   => $user_temp->id,
                            'menu_id'   => $row,
                            'type'      => 'view',
                            'mode'      => NULL,
                        ]);
                    }
                }
                if($request->update == 1){
                    $cek = MenuUser::where('user_id',$user_temp->id,)->where('menu_id',$row)->where('type','update');
                    if($cek->count() == 0){
                        MenuUser::create([
                            'user_id'   => $user_temp->id,
                            'menu_id'   => $row,
                            'type'      => 'update',
                            'mode'      => NULL,
                        ]);
                    }
                }
                if($request->void == 1){
                    $cek = MenuUser::where('user_id',$user_temp->id,)->where('menu_id',$row)->where('type','void');
                    if($cek->count() == 0){
                        MenuUser::create([
                            'user_id'   => $user_temp->id,
                            'menu_id'   => $row,
                            'type'      => 'void',
                            'mode'      => NULL,
                        ]);
                    }
                }
                if($request->delete == 1){
                    $cek = MenuUser::where('user_id',$user_temp->id,)->where('menu_id',$row)->where('type','delete');
                    if($cek->count() == 0){
                        MenuUser::create([
                            'user_id'   => $user_temp->id,
                            'menu_id'   => $row,
                            'type'      => 'delete',
                            'mode'      => NULL,
                        ]);
                    }
                }
                $menu_temp = Menu::find($row);
                if($menu_temp->type == '2'){
                    if($request->journal == 1){
                        $cek = MenuUser::where('user_id',$user_temp->id,)->where('menu_id',$row)->where('type','journal');
                        if($cek->count() == 0){
                            MenuUser::create([
                                'user_id'   => $user_temp->id,
                                'menu_id'   => $row,
                                'type'      => 'journal',
                                'mode'      => NULL,
                            ]);
                        }
                    }
                    if($request->report == 1){
                        $cek = MenuUser::where('user_id',$user_temp->id,)->where('menu_id',$row)->where('type','report');
                        if($cek->count() == 0){
                            MenuUser::create([
                                'user_id'   => $user_temp->id,
                                'menu_id'   => $row,
                                'type'      => 'report',
                                'mode'      => NULL,
                            ]);
                        }
                    }
                }
                

            }
           
            
        }
        DB::Commit();
        
        return $request;
    }
}
