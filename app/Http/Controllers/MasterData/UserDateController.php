<?php

namespace App\Http\Controllers\MasterData;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\UserDate;
use App\Models\UserDateMenu;
use App\Models\UserDateUser;
use Illuminate\Support\Facades\DB;

class UserDateController extends Controller
{
    public function index()
    {
        $menus = Menu::whereNotNull('table_name')->where('status','1')->whereDoesntHave('sub')->orderBy('parent_id','ASC')->orderBy('order','ASC')->get();

        $arrMenu = [];

        foreach($menus as $row){
            if($row->checkPostDate()){
                $arrMenu[] = $row;
            }
        }

        $data = [
            'title'     => 'Tanggal Posting Menu x Pegawai',
            'content'   => 'admin.master_data.user_date',
            'menus'     => $arrMenu,
            'employees' => User::where('type','1')->where('status','1')->orderBy('place_id','ASC')->orderBy('department_id','ASC')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'name',
            'count_backdate',
            'count_futuredate',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = UserDate::count();
        
        $query_data = UserDate::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
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

        $total_filtered = UserDate::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
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
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->name,
                    $val->count_backdate,
                    $val->count_futuredate,
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

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'code' 				=> $request->temp ? ['required', Rule::unique('user_dates', 'code')->ignore($request->temp)] : 'required|unique:user_dates,code',
            'name'              => 'required',
            'count_backdate'    => 'required',
            'count_futuredate'  => 'required',
            'checkBoxMenu'      => 'required|array',
            'checkBoxUser'      => 'required|array',
        ], [
            'code.required' 	        => 'Kode tidak boleh kosong.',
            'code.unique'               => 'Kode telah terpakai.',
            'name.required'             => 'Nama tidak boleh kosong.',
            'count_backdate.required'   => 'Tanggal mundur tidak boleh kosong.',
            'count_futuredate.required' => 'Tanggal maju tidak boleh kosong, 0 boleh.',
            'checkBoxMenu.required'     => 'Menu tidak boleh kosong.',
            'checkBoxMenu.array'        => 'Menu harus dalam bentuk array.',
            'checkBoxUser.required'     => 'Pegawai tidak boleh kosong.',
            'checkBoxUser.array'        => 'Pegawai harus dalam bentuk array.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            DB::beginTransaction();
            try {

                $passed = true;
                $errorMessage = [];

                if($request->temp){
                    foreach($request->checkBoxUser as $key => $row){
                        $cekUserDateMenu = UserDateUser::where('user_id',$row)
                        ->whereHas('userDate',function($query)use($request){
                            $query->whereHas('userDateMenu',function($query)use($request){
                                $query->whereIn('menu_id',$request->checkBoxMenu);
                            })
                            ->where('id','!=',$request->temp)
                            ->where('status','1');
                        })
                        ->get();
                        foreach($cekUserDateMenu as $row){
                            $passed = false;
                            $errorMessage[] = 'Untuk pegawai '.$row->user->name.', batas post date telah diatur pada nomor '.$row->userDate->code.'.';
                        }
                    }
                }else{
                    foreach($request->checkBoxUser as $key => $row){
                        $cekUserDateMenu = UserDateUser::where('user_id',$row)
                        ->whereHas('userDate',function($query)use($request){
                            $query->whereHas('userDateMenu',function($query)use($request){
                                $query->whereIn('menu_id',$request->checkBoxMenu);
                            })
                            ->where('status','1');
                        })
                        ->get();
                        foreach($cekUserDateMenu as $row){
                            $passed = false;
                            $errorMessage[] = 'Untuk pegawai '.$row->user->name.', batas post date telah diatur pada nomor '.$row->userDate->code.'.';
                        }
                    }
                }

                if(!$passed){
                    return response()->json([
                        'status'  => 500,
                        'message' => implode('<br>',$errorMessage),
                    ]);
                }

                if($request->temp){
                    $query = UserDate::find($request->temp);
                    $query->code                = $request->code;
                    $query->user_id             = session('bo_id');
                    $query->name	            = $request->name;
                    $query->count_backdate      = $request->count_backdate;
                    $query->count_futuredate    = $request->count_futuredate;
                    $query->status              = $request->status ? $request->status : '2';
                    $query->save();

                    $query->userDateMenu()->whereNotIn('menu_id',$request->checkBoxMenu)->delete();
                    $query->userDateUser()->whereNotIn('user_id',$request->checkBoxUser)->delete();
                }else{
                    $query = UserDate::create([
                        'code'              => $request->code,
                        'user_id'           => session('bo_id'),
                        'name'			    => $request->name,
                        'count_backdate'    => $request->count_backdate,
                        'count_futuredate'  => $request->count_futuredate,
                        'status'            => $request->status ? $request->status : '2'
                    ]);
                }

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
			
			if($query) {

                foreach($request->checkBoxMenu as $key => $row){
                    $cek = $query->whereHas('userDateMenu',function($query)use($row){
                        $query->where('menu_id',$row);
                    })->count();

                    if($cek == 0){
                        UserDateMenu::create([
                            'user_date_id'  => $query->id,
                            'menu_id'       => $row,
                        ]);
                    }
                }

                foreach($request->checkBoxUser as $key => $row){
                    $cek = $query->whereHas('userDateUser',function($query)use($row){
                        $query->where('user_id',$row);
                    })->count();

                    if($cek == 0){
                        UserDateUser::create([
                            'user_date_id'  => $query->id,
                            'user_id'       => $row,
                        ]);
                    }
                }

                activity()
                    ->performedOn(new UserDate())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit user menu post date data.');

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

    public function rowDetail(Request $request)
    {
        $data   = UserDate::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s6"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="3">Daftar Menu</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Menu</th>
                                <th class="center-align">Url</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->userDateMenu as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td>'.$row->menu->name.'</td>
                <td>'.$row->menu->fullName().'</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div>';
        
        $string .= '<div class="col s6"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="3">Daftar Pegawai</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Nama</th>
                                <th class="center-align">Plant</th>
                                <th class="center-align">Departemen</th>
                            </tr>
                        </thead><tbody>';

        foreach($data->userDateUser as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td>'.$row->user->name.'</td>
                <td>'.$row->user->place->name.'</td>
                <td>'.$row->user->position->division->name.'</td>
            </tr>';
        }

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function show(Request $request){
        $userdate = UserDate::find($request->id);

        $arrMenu = [];
        $arrUser = [];

        foreach($userdate->userDateMenu as $row){
            $arrMenu[] = $row;
        }

        foreach($userdate->userDateUser as $row){
            $arrUser[] = $row;
        }

        $userdate['menus'] = $arrMenu;
        $userdate['users'] = $arrUser;
        				
		return response()->json($userdate);
    }

    public function destroy(Request $request){
        $query = UserDate::find($request->id);
		
        if($query->delete()) {
            $query->userDateMenu()->delete();
            $query->userDateUser()->delete();

            activity()
                ->performedOn(new UserDate())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the user menu date post data');

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
