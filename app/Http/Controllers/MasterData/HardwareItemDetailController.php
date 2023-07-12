<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\HardwareItemDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HardwareItemDetailController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Item Hardware Detail',
            'content' => 'admin.master_data.hardware_item_detail'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'hardware_item_id',
            'specification',
            'info',
            'status',
            'action'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = HardwareItemDetail::count();
        
        $query_data = HardwareItemDetail::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhere('name', 'like', "%$search%");
                            
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

        $total_filtered = HardwareItemDetail::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhere('name', 'like', "%$search%");
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
                    $val->hardwareItem->item->name,
                    $val->specification,
                    $val->info,
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

    public function show(Request $request){
        $HardwareItemDetail = HardwareItemDetail::find($request->id);
        $HardwareItemDetail['item']=$HardwareItemDetail->hardwareItem->item;
        
		return response()->json($HardwareItemDetail);
    }

    public function create(Request $request){

        $validation = Validator::make($request->all(), [
            'hardware_item_id'               => 'required',
            'specification'                  => 'required',
        ], [
            'hardware_item_id.required' 	 => 'Item Parent tidak boleh kosong.',
            'specification.required'         => 'Spesifikasi belum diisi.',
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
                    $query = HardwareItemDetail::find($request->temp);
                    $query->hardware_item_id            = $request->hardware_item_id;
                    $query->specification	        = $request->specification;
                    $query->info	= $request->info;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
            }else{
                DB::beginTransaction();
                try {
                    $query = HardwareItemDetail::create([
                        'hardware_item_id'      => $request->hardware_item_id,
                        'specification'			=> $request->specification,
                        'info'	                => $request->info,
                        'status'                => $request->status ? $request->status : '2'
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
            }
            
            if($query) {

                activity()
                    ->performedOn(new HardwareItemDetail())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit Hardware item detail.');

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
}
