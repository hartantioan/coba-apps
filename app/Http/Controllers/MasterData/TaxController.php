<?php

namespace App\Http\Controllers\MasterData;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Tax;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportWarehouse;

class TaxController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Pajak',
            'content'   => 'admin.master_data.tax',
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
            'percentage',
            'is_default_ppn',
            'is_default_pph',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Tax::count();
        
        $query_data = Tax::where(function($query) use ($search, $request) {
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

        $total_filtered = Tax::where(function($query) use ($search, $request) {
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
                    number_format($val->percentage,2,',','.'),
                    $val->isDefaultPpn(),
                    $val->isDefaultPph(),
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
            'code' 				=> $request->temp ? ['required', Rule::unique('banks', 'code')->ignore($request->temp)] : 'required|unique:banks,code',
            'name'              => 'required',
            'coa_id'            => 'required',
            'type'              => 'required',
            'percentage'        => 'required',
        ], [
            'code.required' 	    => 'Kode tidak boleh kosong.',
            'code.unique'           => 'Kode telah terpakai.',
            'name.required'         => 'Nama tidak boleh kosong.',
            'coa_id.required'       => 'Coa tidak boleh kosong.',
            'type.required'         => 'Tipe pajak tidak boleh kosong.',
            'percentage.required'   => 'Prosentase tidak boleh kosong.'
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
                    $query = Tax::find($request->temp);
                    $query->code            = $request->code;
                    $query->name	        = $request->name;
                    $query->coa_id          = $request->coa_id;
                    $query->type            = $request->type;
                    $query->percentage      = str_replace(',','.',str_replace('.','',$request->percentage));
                    $query->is_default_ppn  = $request->is_default_ppn ? $request->is_default_ppn : '0';
                    $query->is_default_pph  = $request->is_default_pph ? $request->is_default_pph : '0';
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                }else{
                    $query = Tax::create([
                        'code'              => $request->code,
                        'name'			    => $request->name,
                        'coa_id'            => $request->coa_id,
                        'type'              => $request->type,
                        'percentage'        => str_replace(',','.',str_replace('.','',$request->percentage)),
                        'is_default_ppn'    => $request->is_default_ppn ? $request->is_default_ppn : '0',
                        'is_default_pph'    => $request->is_default_pph ? $request->is_default_pph : '0',
                        'status'            => $request->status ? $request->status : '2'
                    ]);
                }

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
			
			if($query) {

                activity()
                    ->performedOn(new Tax())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit tax master data.');

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
        $tax = Tax::find($request->id);
        $tax['percentage'] = number_format($tax->percentage,2,',','.');
        $tax['coa_name'] = $tax->coa()->exists() ? $tax->coa->name.' - '.$tax->coa->name : '-';
        				
		return response()->json($tax);
    }

    public function destroy(Request $request){
        $query = Tax::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new Tax())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the tax data');

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
