<?php

namespace App\Http\Controllers\MasterData;
use App\Models\Company;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Bank;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportWarehouse;

class BankController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Bank',
            'content'   => 'admin.master_data.bank',
            'company'   => Company::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'account_name',
            'account_no',
            'company_id',
            'branch',
            'is_show',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Bank::count();
        
        $query_data = Bank::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('account_no', 'like', "%$search%");
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

        $total_filtered = Bank::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('account_no', 'like', "%$search%");
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
                    $val->account_name,
                    $val->account_no,
                    $val->company->name,
                    $val->branch,
                    $val->isShow(),
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
            'company_id'        => 'required',
            'account_no'        => 'required',
            'account_name'      => 'required',
        ], [
            'code.required' 	    => 'Kode tidak boleh kosong.',
            'code.unique'           => 'Kode telah terpakai.',
            'name.required'         => 'Nama tidak boleh kosong.',
            'company_id.required'   => 'Perusahaan tidak boleh kosong.',
            'account_no.required'   => 'Nomor rekening tidak boleh kosong.',
            'account_name.required' => 'Atas nama rekening tidak boleh kosong.',
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
                    $query = Bank::find($request->temp);
                    $query->code            = $request->code;
                    $query->name	        = $request->name;
                    $query->account_name    = $request->account_name;
                    $query->account_no      = $request->account_no;
                    $query->company_id      = $request->company_id;
                    $query->branch          = $request->branch;
                    $query->is_show         = $request->is_show ? $request->is_show : NULL;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = Bank::create([
                        'code'          => $request->code,
                        'name'			=> $request->name,
                        'account_name'  => $request->account_name,
                        'account_no'    => $request->account_no,
                        'company_id'    => $request->company_id,
                        'branch'        => $request->branch,
                        'is_show'       => $request->is_show ? $request->is_show : NULL,
                        'status'        => $request->status ? $request->status : '2'
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                activity()
                    ->performedOn(new Bank())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit bank.');

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
        $bank = Bank::find($request->id);
        				
		return response()->json($bank);
    }

    public function destroy(Request $request){
        $query = Bank::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new Bank())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the Bank data');

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
