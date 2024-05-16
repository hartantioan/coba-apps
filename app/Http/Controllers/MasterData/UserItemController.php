<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Menu;
use App\Models\User;
use App\Models\UserItem;
use App\Models\UserItemItem;
use App\Models\UserItemUser;
use Illuminate\Support\Facades\DB;

class UserItemController extends Controller
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
            'title'     => 'Item x Customer',
            'content'   => 'admin.master_data.user_item',
            'menus'     => $arrMenu,
            'employees' => User::where('type','1')->where('status','1')->orderBy('place_id','ASC')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'code',
            'user_id',
            
            'name',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = UserItem::count();
        
        $query_data = UserItem::where(function($query) use ($search, $request) {
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

        $total_filtered = UserItem::where(function($query) use ($search, $request) {
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
            'code' 				=> $request->temp ? ['required', Rule::unique('user_items', 'code')->ignore($request->temp)] : 'required|unique:user_items,code',
            'name'              => 'required',
            'arr_item'      => 'required|array',
            'arr_user'      => 'required|array',
        ], [
            'code.required' 	        => 'Kode tidak boleh kosong.',
            'code.unique'               => 'Kode telah terpakai.',
            'name.required'             => 'Nama tidak boleh kosong.',
            'arr_user.required'     => 'Customer tidak boleh kosong.',
            'arr_user.array'        => 'Customer harus dalam bentuk array.',
            'arr_item.required'     => 'Item tidak boleh kosong.',
            'arr_item.array'        => 'Item harus dalam bentuk array.',
        ]);
        info('sid');
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
                    foreach($request->arr_user as $key => $row){
                        $cekUserItemItem = UserItemUser::where('user_id',$row)
                        ->whereHas('userItem',function($query)use($request){
                            $query->whereHas('userItemItem',function($query)use($request){
                                $query->whereIn('item_id',$request->arr_item);
                            })
                            ->where('id','!=',$request->temp)
                            ->where('status','1');
                        })
                        ->get();
                        foreach($cekUserItemItem as $row){
                            $passed = false;
                            $errorMessage[] = 'Untuk pegawai '.$row->user->name.', batas post date telah diatur pada nomor '.$row->userItem->code.'.';
                        }
                    }
                }else{
                    foreach($request->arr_user as $key => $row){
                        $cekUserItemItem = UserItemUser::where('user_id',$row)
                        ->whereHas('userItem',function($query)use($request){
                            $query->whereHas('userItemItem',function($query)use($request){
                                $query->whereIn('item_id',$request->arr_item);
                            })
                            ->where('status','1');
                        })
                        ->get();
                        foreach($cekUserItemItem as $row){
                            $passed = false;
                            $errorMessage[] = 'Untuk pegawai '.$row->user->name.', batas post date telah diatur pada nomor '.$row->userItem->code.'.';
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
                    $query = UserItem::find($request->temp);
                    $query->code                = $request->code;
                    $query->user_id             = session('bo_id');
                    $query->name	            = $request->name;
                    $query->status              = $request->status ? $request->status : '2';
                    $query->save();

                    $query->userItemItem()->delete();
                    $query->userItemUser()->delete();
                }else{
                    $query = UserItem::create([
                        'code'              => $request->code,
                        'user_id'           => session('bo_id'),
                        'name'			    => $request->name,
                        'status'            => $request->status ? $request->status : '2'
                    ]);
                }

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
			
			if($query) {

                foreach($request->arr_item as $key => $row){
                    UserItemItem::create([
                        'user_item_id'  => $query->id,
                        'item_id'       => $row,
                    ]);
                }

                foreach($request->arr_user as $key => $row){
                    UserItemUser::create([
                        'user_item_id'  => $query->id,
                        'user_id'       => $row,
                    ]);
                }

                activity()
                    ->performedOn(new UserItem())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit user item customer data.');

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

    public function show(Request $request){
        $userItem = UserItem::find($request->id);

        $arrItem = [];
        $arrUser = [];

        foreach($userItem->userItemItem as $row){
            $arrItem[] = [
                'data'=>$row,
                'item'=>$row->item
            ];
        }

        foreach($userItem->userItemUser as $row){
            $arrUser[] = [
                'data'=>$row,
                'user'=>$row->user
            ];
        }

        $userItem['items'] = $arrItem;
        $userItem['users'] = $arrUser;
        				
		return response()->json($userItem);
    }

    public function rowDetail(Request $request)
    {
        $data   = UserItem::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s6"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="3">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->userItemItem as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td>'.$row->item->code.'-'.$row->item->name.'</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div>';
        
        $string .= '<div class="col s6"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="3">Daftar Customer</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Nama</th>
                            </tr>
                        </thead><tbody>';

        foreach($data->userItemUser as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td>'.$row->user->name.'</td>
            </tr>';
        }

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function destroy(Request $request){
        $query = UserItem::find($request->id);
		
        if($query->delete()) {
            $query->userItemItem()->delete();
            $query->userItemUser()->delete();

            activity()
                ->performedOn(new UserItem())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the user item data');

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
