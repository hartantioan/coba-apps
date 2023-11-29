<?php

namespace App\Http\Controllers\MasterData;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\UserSpecial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
class UserSpecialController extends Controller
{
    public function index()
    {
        $data = [
            'title'         => 'Special User',
            'content'       => 'admin.master_data.user_special',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }
    public function datatable(Request $request){
        $column = [
            'user_id',
            'name',
            'type',
            'start_date',
            'end_date',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = UserSpecial::count();
        
        $query_data = UserSpecial::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('name', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search,$request){
                                $query->where('name','like',"%$search%");
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

        $total_filtered = UserSpecial::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('name', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search,$request){
                                $query->where('name','like',"%$search%");
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
               
                $btn = 
                '
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                ';
                      
                $response['data'][] = [
                    $nomor,
                    $val->name,                 
                    $val->user->name,
                    $val->type,
                    $val->start_date,
                    $val->end_date,
                    $val->limit ?? 'No Limit',
                    $val->punishment->code ?? 'Tidak Memiliki Batas Hukuman',
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

    public function create(Request $request){
        
        $validation = Validator::make($request->all(), [
            'name'                  => 'required',
            'start_date'            => 'required',
            'end_date'              => 'required',
            'user_id'               => 'required',
        ], [
            'name.required'                 => 'Nama tidak boleh kosong.',
            'start_date.required'           => 'Tanggal Mulai tidak boleh kosong.',
            'end_date.required'             => 'Tanggal akhir tidak boleh kosong.',
            'user_id.required'              => 'Harap pilih pegawai tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = UserSpecial::find($request->temp);
                    $query->name                    = $request->name;
                    $query->start_date              = $request->start_date;
                    $query->end_date                = $request->end_date;
                    $query->user_id                 = $request->user_id;
                    $query->punishment_id           = $request->punishment_id;
                    $query->limit                   = $request->limit;
                    $query->status                  = $request->status ? $request->status : '1';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = UserSpecial::create([
                        'name'			    => $request->name,
                        'start_date'	    => $request->start_date,
                        'end_date'	        => $request->end_date,
                        'user_id'	        => $request->user_id,
                        'type'	            => $request->type,
                        'punishment_id'	    => $request->punishment_id,
                        'limit'	            => $request->limit,
                        'status'            => $request->status ? $request->status : '1',
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                activity()
                    ->performedOn(new UserSpecial())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit User Special data.');

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
        $special = UserSpecial::find($request->id);
        $special['user_name'] = $special->user->name;
        if($special->punishment()->exists()){
            $special['punishment'] = $special->punishment->code.'|'.$special->punishment->name;
        }else{
            $special['punishment'] ='';
        }
        
       			
		return response()->json($special);
    }
}
