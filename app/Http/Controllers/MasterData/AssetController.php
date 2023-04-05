<?php

namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Asset;
use App\Models\Place;
use App\Models\Department;

use App\Imports\ImportAsset;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class AssetController extends Controller
{
    public function index()
    {
        $data = [
            'title'         => 'Aset',
            'content'       => 'admin.master_data.asset',
            'place'         => Place::where('status','1')->get(),
            'department'    => Department::where('status','1')->get()
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'item_id',
            'name',
            'date_start',
            'date_end',
            'nominal',
            'method',
            'cost_coa_id',
            'note',
            'status',
            'place_id',
            'department_id'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Asset::count();
        
        $query_data = Asset::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('nominal','like', "%$search%")
                            ->orWhere('note','like',"%$search%");
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

        $total_filtered = Asset::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('nominal','like', "%$search%")
                            ->orWhere('note','like',"%$search%");
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
                    $val->item->name,
                    $val->name,
                    date('d/m/y',strtotime($val->date_start)),
                    date('d/m/y',strtotime($val->date_end)),
                    number_format($val->nominal,3,',','.'),
                    $val->method(),
                    $val->costCoa->name,
                    $val->note,
                    $val->status(),
                    $val->place_id ? $val->place->name.' - '.$val->place->company->name : '-',
                    $val->department_id ? $val->department->name : '-',
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
            'code' 				=> $request->temp ? ['required', Rule::unique('assets', 'code')->ignore($request->temp)] : 'required|unique:assets,code',
            'item_id'           => 'required',
            'name'              => 'required',
            'date_start'        => 'required',
            'date_end'          => 'required',
            'nominal'           => 'required',
            'method'            => 'required',
            'cost_coa_id'       => 'required'
        ], [
            'code.required' 	    => 'Kode tidak boleh kosong.',
            'code.unique'           => 'Kode telah terpakai.',
            'item_id.required'      => 'Item tidak boleh kosong.',
            'name.required'         => 'Nama tidak boleh kosong.',
            'date_start.required'   => 'Tgl mulai hitung tidak boleh kosong.',
            'date_end.required'     => 'Tgl akhir hitung tidak boleh kosong.',
            'nominal.required'      => 'Nominal tidak boleh kosong.',
            'method.required'       => 'Metode hitung tidak boleh kosong.',
            'cost_coa_id.required'  => 'Coa biaya tidak boleh kosong.'
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
                    $query = Asset::find($request->temp);
                    $query->code            = $request->code;
                    $query->user_id	        = session('bo_id');
                    $query->place_id        = $request->place_id ? $request->place_id : NULL;
                    $query->department_id   = $request->department_id ? $request->department_id : NULL;
                    $query->item_id         = $request->item_id;
                    $query->name	        = $request->name;
                    $query->date_start	    = $request->date_start;
                    $query->date_end	    = $request->date_end;
                    $query->nominal	        = str_replace(',','.',str_replace('.','',$request->nominal));
                    $query->method          = $request->method;
                    $query->cost_coa_id     = $request->cost_coa_id;
                    $query->note            = $request->note;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = Asset::create([
                        'code'              => $request->code,
                        'user_id'			=> session('bo_id'),
                        'place_id'          => $request->place_id ? $request->place_id : NULL,
                        'department_id'     => $request->department_id ? $request->department_id : NULL,
                        'item_id'           => $request->item_id,
                        'name'              => $request->name,
                        'date_start'        => $request->date_start,
                        'date_end'          => $request->date_end,
                        'nominal'           => str_replace(',','.',str_replace('.','',$request->nominal)),
                        'method'            => $request->method,
                        'cost_coa_id'       => $request->cost_coa_id,
                        'note'              => $request->note,
                        'status'            => $request->status ? $request->status : '2'
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                activity()
                    ->performedOn(new Asset())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit asset.');

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
        $asset = Asset::find($request->id);
        $asset['item_name'] = $asset->item->name;
        $asset['cost_coa_name'] = $asset->costCoa->name;
        $asset['nominal'] = number_format($asset->nominal,3,',','.');
        				
		return response()->json($asset);
    }

    public function destroy(Request $request){
        $query = Asset::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new Asset())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the asset data');

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

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'mimes:xlsx',
                'max:2048',
                function ($attribute, $value, $fail) {
                    $rows = Excel::toArray([], $value)[0];
                    if (count($rows) < 2) {
                        $fail('The file must contain at least two rows.');
                    }
                }
            ]
        ]);

        if ($validator->fails()) {
            $response = [
                'status' => 432,
                'error'  => $validator->errors()
            ];
            return response()->json($response);
        }

        try {
            Excel::import(new ImportAsset, $request->file('file'));

            return response()->json(['message' => 'Import successful!']);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values(),
                ];
            }
            $response = [
                'status' => 422,
                'error'  => $errors
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            $response = [
                'status'  => 500,
                'message' => "Data failed to save"
                //'Data failed to save.'
            ];
            return response()->json($response);
        }
    }
}