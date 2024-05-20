<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Menu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
class AnnouncementController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Announcement',
            'content'   => 'admin.setting.announcement',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'user_id',
            'description',
            'menu_id',
            'status',
            'start_date',
            'end_date',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Announcement::count();
        
        $query_data = Announcement::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('description', 'like', "%$search%")
                            ->orWhere('start_date', 'like', "%$search%")
                            ->orWhere('end_date', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
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

        $total_filtered = Announcement::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('description', 'like', "%$search%")
                            ->orWhere('start_date', 'like', "%$search%")
                            ->orWhere('end_date', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
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
                    $nomor,
                    $val->user->name,
                    $val->description,
                    $val->menuMany(),
                    $val->start_date,
                    $val->end_date,
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
            'description'          => 'required',
            'start_date'          => 'required',
            'end_date'          => 'required',
        ], [
            'description.required' => 'Nama required tidak boleh kosong.',
            'start_date.required' => 'Tanggal Mulai tidak boleh kosong.',
            'end_date.required' => 'Tanggal Akhir tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            if(count($request->menu_id) > 0){
                $menu_id = implode(',', $request->menu_id);
            }else{
                $menu_id = NULL;
            }
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = Announcement::find($request->temp);
                   
                    $query->user_id                = session('bo_id');
                    $query->description            = $request->description;
                    $query->menu_id                = $menu_id;
                    $query->start_date             = $request->start_date;
                    $query->end_date               = $request->end_date;
                    $query->status                 = $request->status ? $request->status : '2';
                    $query->save();

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{

                DB::beginTransaction();
                try {
                    $query = Announcement::create([
                        'user_id'			    => session('bo_id'),
                        'description'		=> $request->description,
                        'menu_id'            => $menu_id,
                        'start_date'            => $request->start_date,
                        'end_date'            => $request->end_date,
                        'status'            => $request->status ? $request->status : '2',
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {               

                activity()
                    ->performedOn(new Announcement())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit announcement data.');

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

    public function show(Request $request){
        $announcement = Announcement::find($request->id);
        $menu_temp = explode(',', $announcement->menu_id);
        $query_menu = Menu::whereIn('id', $menu_temp)->get();
        $menu = [];
        foreach($query_menu as $row_menu){
            $menu [] = $row_menu;
        }
        $announcement['menu']=$menu;	
		return response()->json($announcement);
    }

    public function refresh(Request $request){
        $today = Carbon::today();
        
        $menu = Menu::where('url', $request->lastSegment)->first();
        $arrnotif=[];
        
        if ($menu) {
            $menuId = $menu->id;
            $announcements = Announcement::where('status', 1)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->whereRaw("FIND_IN_SET(?, menu_id)", [$menuId])
                ->orderByDesc('id')
                ->limit(5)
                ->get()
                ->sortBy('id');
            foreach($announcements as $row){
                $arrnotif[] = $row;
            }
        }
        
        

        $response = [
            'status'            => 200,
            'message'           => 'Test success.',
            'announcement_list'        => $arrnotif,
        ];
        
        return response()->json($response);
    }
}
