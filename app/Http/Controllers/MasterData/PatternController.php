<?php

namespace App\Http\Controllers\MasterData;

use App\Exports\ExportTemplateMasterPattern;
use App\Http\Controllers\Controller;
use App\Imports\ImportPattern;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Pattern;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class PatternController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Motif & Warna',
            'content'   => 'admin.master_data.pattern',
            'brand'     => Brand::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'brand_id',
            'code',
            'name',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Pattern::count();
        
        $query_data = Pattern::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhereHas('brand',function($query) use ($search, $request){
                                $query->where('code', 'like', "%$search%")
                                ->orWhere('name', 'like', "%$search%");
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

        $total_filtered = Pattern::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhereHas('brand',function($query) use ($search, $request){
                                $query->where('code', 'like', "%$search%")
                                ->orWhere('name', 'like', "%$search%");
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
                    $val->id,
                    $val->brand->name,
                    $val->code,
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
            'code' 				=> $request->temp ? ['required', Rule::unique('patterns', 'code')->ignore($request->temp)] : 'required|unique:patterns,code',
            'brand_id'          => 'required',
            'name'              => 'required',
        ], [
            'code.required' 	    => 'Kode tidak boleh kosong.',
            'code.unique'           => 'Kode telah terpakai.',
            'brand_id.required'     => 'Brand tidak boleh kosong.',
            'name.required'         => 'Nama tidak boleh kosong.',
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
                    $query = Pattern::find($request->temp);

                    $cek = Pattern::whereRaw("REPLACE(name,' ','') = '$request->nameWithoutSpace'")->where('status','1')->where('id','!=',$query->id)->first();

                    if($cek){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Nama motif dan warna terpilih telah terdaftar pada brand '.$cek->brand->name,
                        ]);
                    }

                    $query->brand_id        = $request->brand_id;
                    $query->code            = $request->code;
                    $query->name	        = $request->name;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {

                    $cek = Pattern::whereRaw("REPLACE(name,' ','') = '$request->nameWithoutSpace'")->where('status','1')->first();

                    if($cek){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Nama motif dan warna terpilih telah terdaftar pada brand '.$cek->brand->name,
                        ]);
                    }

                    $query = Pattern::create([
                        'brand_id'      => $request->brand_id,
                        'code'          => $request->code,
                        'name'			=> $request->name,
                        'status'        => $request->status ? $request->status : '2'
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                activity()
                    ->performedOn(new Pattern())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit pattern.');

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
        $pattern = Pattern::find($request->id);
        				
		return response()->json($pattern);
    }

    public function destroy(Request $request){
        $query = Pattern::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new Pattern())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the pattern data');

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

    public function getImportExcel(){
        return Excel::download(new ExportTemplateMasterPattern(), 'format_master_pattern'.uniqid().'.xlsx');
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
            Excel::import(new ImportPattern, $request->file('file'));

            return response()->json([
                'status'    => 200,
                'message'   => 'Import sukses!'
            ]);
            
        } catch (ValidationException $e) {
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
            info($e);
            $response = [
                'status'  => 500,
                'message' => "Data failed to save"
            ];
            return response()->json($response);
        }
    }
}
