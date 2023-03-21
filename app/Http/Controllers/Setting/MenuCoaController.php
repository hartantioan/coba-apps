<?php

namespace App\Http\Controllers\Setting;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Menu;
use App\Models\MenuCoa;

class MenuCoaController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Menu x Coa',
            'content'   => 'admin.setting.menu_coa',
            'menu'      => Menu::whereDoesntHave('sub')->whereNotNull('table_name')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'name',
            'url',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Menu::whereDoesntHave('sub')->whereNotNull('table_name')->where('status','1')->count();
        
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
            })
            ->whereDoesntHave('sub')
            ->whereNotNull('table_name')
            ->where('status','1')
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
            })
            ->whereDoesntHave('sub')
            ->whereNotNull('table_name')
            ->where('status','1')
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    $nomor,
                    $val->fullName(),
                    $val->url,
                    $val->journalable() ?

                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')" style="border-radius:0%;"><i class="material-icons dp48">create</i><span class="badge badge pill '.(count($val->menuCoa) > 0 ? 'green' : 'red').'" style="position:absolute;top:0;right:0;">'.count($val->menuCoa).'</span></button>
					' : ' Not available '
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

    public function show(Request $request){
        $menu = Menu::find($request->id);
        $menu['fields'] = json_encode($menu->getColumnTypes());
        $menu['fullname'] = $menu->fullName();

        $cek = MenuCoa::where('menu_id',$request->id)->get();

        $arrDetails = [];

        foreach($cek as $row){
            $arrDetails[] = [
                'menu_id'       => $row->menu_id,
                'coa_id'        => $row->coa_id,
                'coa_name'      => $row->coa->code.' - '.$row->coa->name,
                'field_name'    => $row->field_name,
                'type'          => $row->type,
                'percentage'    => number_format($row->percentage,3,',','.')
            ];
        }

        $menu['details'] = $arrDetails;
        				
		return response()->json($menu);
    }

    public function create(Request $request){
        
        if($request->arr_coa){
            $validation = Validator::make($request->all(), [
                'temp'                      => 'required',
                'arr_type'                  => 'required|array',
                'arr_coa'                   => 'required|array',
                'arr_percent'               => 'required|array',
                'arr_field'                 => 'required|array',
            ], [
                'temp.required'             => 'Menu tidak boleh kosong.',
                'arr_type.required'         => 'Tipe tidak boleh kosong.',
                'arr_type.array'            => 'Tipe haruslah dalam bentuk array',
                'arr_coa.required'          => 'Coa tidak boleh kosong.',
                'arr_coa.array'             => 'Coa haruslah dalam bentuk array',
                'arr_percent.required'      => 'Prosentase tidak boleh kosong.',
                'arr_percent.array'         => 'Prosentase haruslah dalam bentuk array',
                'arr_field.required'        => 'Kolom tidak boleh kosong.',
                'arr_field.array'           => 'Kolom haruslah dalam bentuk array',
            ]);
        }else{
            $validation = Validator::make($request->all(), [
                'temp'                      => 'required',
            ], [
                'temp.required'             => 'Menu tidak boleh kosong.',
            ]);
        }

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            DB::beginTransaction();
            try {

                $cek = MenuCoa::where('menu_id',$request->temp)->get();

                foreach($cek as $rowcek){
                    $rowcek->delete();
                }

                if($request->arr_coa){
                    foreach($request->arr_type as $key => $row){
                        $query = MenuCoa::create([
                            'user_id'       => session('bo_id'),
                            'menu_id'       => $request->temp,
                            'coa_id'        => $request->arr_coa[$key],
                            'field_name'    => $request->arr_field[$key],
                            'type'          => $row,
                            'percentage'    => str_replace(',','.',str_replace('.','',$request->arr_percent[$key]))
                        ]);
    
                        if($query) {               
                            activity()
                                ->performedOn(new MenuCoa())
                                ->causedBy(session('bo_id'))
                                ->withProperties($query)
                                ->log('Add / edit menu coa data.');
                        }
                    }
                }
                
                $response = [
                    'status'    => 200,
                    'message'   => 'Data successfully saved.',
                ];

                DB::commit();
            }catch(\Exception $e){
                 DB::rollback(); 
                $response = [
                    'status'  => 500,
                    'message' => 'Data failed to save.'
                ];
            }
		}
		
		return response()->json($response);
    }
}