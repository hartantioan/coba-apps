<?php

namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\InventoryCoa;
use Illuminate\Support\Facades\DB;

class InventoryCoaController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Coa Persediaan',
            'content'   => 'admin.master_data.inventory_coa',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'coa_id',
            'type',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = InventoryCoa::count();
        
        $query_data = InventoryCoa::where(function($query) use ($search, $request) {
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

        $total_filtered = InventoryCoa::where(function($query) use ($search, $request) {
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
                    $nomor,
                    $val->code,
                    $val->name,
                    $val->coa()->exists() ? $val->coa->code.' - '.$val->coa->name : ' - ',
                    $val->type(),
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
            'code' 				=> $request->temp ? ['required', Rule::unique('inventory_coas', 'code')->ignore($request->temp)] : 'required|unique:inventory_coas,code',
            'name'              => 'required',
            'coa_id'            => 'required',
            'type'              => 'required',
        ], [
            'code.required' 	        => 'Kode tidak boleh kosong.',
            'code.unique'               => 'Kode telah terpakai.',
            'code.alpha_num'            => 'Format kode salah. Contoh format kode adalah 123XXX.',
            'name.required'             => 'Nama tidak boleh kosong.',
            'coa_id.required'           => 'Coa tidak boleh kosong.',
            'type.required'             => 'Tipe posisi tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            DB::beginTransaction();
            try {
                if($request->temp){
                    $query = InventoryCoa::find($request->temp);
                    $query->user_id         = session('bo_id');
                    $query->code            = $request->code;
                    $query->name	        = $request->name;
                    $query->coa_id          = $request->coa_id;
                    $query->type            = $request->type;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                }else{
                    $query = InventoryCoa::create([
                        'user_id'           => session('bo_id'),
                        'code'              => $request->code,
                        'name'			    => $request->name,
                        'coa_id'            => $request->coa_id,
                        'type'              => $request->type,
                        'status'            => $request->status ? $request->status : '2'
                    ]);
                }

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
			
			if($query) {

                activity()
                    ->performedOn(new InventoryCoa())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit inventory coa data.');

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
        $ic = InventoryCoa::find($request->id);
        $ic['coa_name'] = $ic->coa_id ? $ic->coa->code.' - '.$ic->coa->name : '';
        				
		return response()->json($ic);
    }

    public function destroy(Request $request){
        $query = InventoryCoa::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new InventoryCoa())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the inventory coa data');

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
