<?php

namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Coa;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportCoa;
use Maatwebsite\Excel\Concerns\ToModel;

use App\Imports\ImportCoa;
use App\Imports\ImportCoaMaster;
use App\Models\Currency;

class CoaController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Chart of Account (COA)',
            'content'   => 'admin.master_data.coa',
            'coa'       => Coa::where('status','1')->get(),
            'company'   => Company::where('status','1')->get(),
            'currency'  => Currency::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);

        /* $coas = Coa::tree()->get()->toTree();

        return view('admin.layouts.menu', [
            'coas' => $coas
        ]); */
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'prefix',
            'code',
            'name',
            'company_id',
            'parent_id',
            'level',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Coa::count();
        
        $query_data = Coa::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('prefix', 'like', "%$search%")
                            ->orWhereHas('company',function($query) use($search, $request){
                                $query->where('code', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->company){
                    $query->where('company_id', $request->company);
                }

                if($request->type){
                    $query->where(function($query) use ($request){
                        foreach($request->type as $row){
                            if($row == '2'){
                                $query->OrWhereNotNull('show_journal');
                            }
                            if($row == '3'){
                                $query->OrWhereNotNull('is_cash_account');
                            }
                        }
                    });
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = Coa::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('prefix', 'like', "%$search%")
                            ->orWhereHas('company',function($query) use($search, $request){
                                $query->where('code', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->company){
                    $query->where('company_id', $request->company);
                }

                if($request->type){
                    $query->where(function($query) use ($request){
                        foreach($request->type as $row){
                            if($row == '2'){
                                $query->OrWhereNotNull('show_journal');
                            }
                            if($row == '3'){
                                $query->OrWhereNotNull('is_cash_account');
                            }
                        }
                    });
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $space = ' - ';
                $pretext = '';

                for($i=1;$i<=$val->level;$i++){
                    $pretext .= $space;
                }

                $response['data'][] = [
                    $val->id,
                    $val->prefix,
                    $pretext.$val->code,
                    $val->name,
                    $val->company->name,
                    $val->parentSub()->exists() ? $val->parentSub->name : 'is Parent',
                    $val->currency()->exists() ? $val->currency->name : '',
                    $val->level,
                    $val->is_cash_account ? '&#10003;' : '&#10005;',
                    $val->is_hidden ? '&#10003;' : '&#10005;',
                    $val->show_journal ? '&#10003;' : '&#10005;',
                    $val->bp_journal ? '&#10003;' : '&#10005;',
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
        $coa = Coa::find($request->id);
        $coa['parent_id'] = $coa->parentSub()->exists() ? $coa->parent_id : '';
        $coa['parent_name'] = $coa->parentSub()->exists() ? $coa->parentSub->name : '';
 		return response()->json($coa);
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
			/* 'code' 				=> $request->temp ? ['required', Rule::unique('coas', 'code')->ignore($request->temp)] : 'required|unique:coas,code', */
            'code'              => 'required',
            'name'              => 'required',
            'company_id'        => 'required',
			'level'		        => 'required',
            'currency_id'       => $request->is_cash_account ? 'required' : '',
		], [
			'code.required' 	    => 'Kode tidak boleh kosong.',
            /* 'code.unique' 	        => 'Kode telah terpakai.', */
			'name.required' 	    => 'Nama tidak boleh kosong.',
            'company_id.required'   => 'Perusahaan tidak boleh kosong.',
			'level.required'	    => 'Level tidak boleh kosong.',
            'currency_id.required'  => 'Mata uang tidak boleh kosong.',
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
                    $query = Coa::find($request->temp);
                    $query->code = $request->code;
                    $query->name = $request->name;
                    $query->company_id = $request->company_id;
                    $query->parent_id = $request->parent_id ? $request->parent_id : NULL;
                    $query->currency_id = $request->currency_id ?? NULL;
                    $query->level = $request->level;
                    $query->status = $request->status ? $request->status : '2';
                    $query->is_cash_account = $request->is_cash_account ? $request->is_cash_account : NULL;
                    $query->is_hidden = $request->is_hidden ? $request->is_hidden : NULL;
                    $query->show_journal = $request->show_journal ? $request->show_journal : NULL;
                    $query->bp_journal = $request->bp_journal ? $request->bp_journal : NULL;
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

			}else{
                DB::beginTransaction();
                try {
                    $query = Coa::create([
                        'code'			        => $request->code,
                        'name'			        => $request->name,
                        'company_id'            => $request->company_id,
                        'parent_id'	            => $request->parent_id ? $request->parent_id : NULL,
                        'currency_id'           => $request->currency_id ?? NULL,
                        'level'                 => $request->level,
                        'is_cash_account'       => $request->is_cash_account ? $request->is_cash_account : NULL,
                        'is_hidden'             => $request->is_hidden ? $request->is_hidden : NULL,
                        'show_journal'          => $request->show_journal ? $request->show_journal : NULL,
                        'bp_journal'            => $request->bp_journal ? $request->bp_journal : NULL,
                        'status'                => $request->status ? $request->status : '2'
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                activity()
                    ->performedOn(new Coa())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit coa data.');

                $newdata = [];

                $newdata[] = '<option value="">Parent (Utama)</option>';

                foreach(Coa::whereNull('parent_id')->get() as $m){
                    $newdata[] = '<option value="'.$m->id.'">'.$m->code.' '.$m->name.'</option>';
                    foreach($m->childSub as $m2){
                        $newdata[] = '<option value="'.$m2->id.'"> - '.$m2->code.' '.$m2->name.'</option>';
                        foreach($m2->childSub as $m3){
                            $newdata[] = '<option value="'.$m3->id.'"> - - '.$m3->code.' '.$m3->name.'</option>';
                            foreach($m3->childSub as $m4){
                                $newdata[] = '<option value="'.$m4->id.'"> - - - '.$m4->code.' '.$m4->name.'</option>';
                                foreach($m4->childSub as $m5){
                                    $newdata[] = '<option value="'.$m5->id.'"> - - - - '.$m5->code.' '.$m5->name.'</option>';
                                }
                            }
                        }
                    }
                }

				$response = [
					'status'    => 200,
					'message'   => 'Data successfully saved.',
                    'data'      => $newdata
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

    public function destroy(Request $request){
        $query = Coa::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new Coa())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the coa data');

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

    public function print(Request $request){

        $validation = Validator::make($request->all(), [
            'arr_id'                => 'required',
        ], [
            'arr_id.required'       => 'Tolong pilih Item yang ingin di print terlebih dahulu.',
        ]);
        
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            $pr=[];
            $currentDateTime = Date::now();
            $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
            foreach($request->arr_id as $key =>$row){
                $cleanedMessage = str_replace(['-', ' '], '', $row);
                $pr[]= Coa::where('code',$cleanedMessage)->first();

            }
            $data = [
                'title'     => 'Master COA',
                'data'      => $pr
            ];  
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path);
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
            $pdf = Pdf::loadView('admin.print.master_data.coa', $data)->setPaper('a5', 'landscape');
            $pdf->render();
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            $content = $pdf->download()->getOriginalContent();


            $randomString = Str::random(10); 

         
            $filePath = 'public/pdf/' . $randomString . '.pdf';
            

            Storage::put($filePath, $content);
            
            $document_po = asset(Storage::url($filePath));
            $var_link=$document_po;

            $response =[
                'status'=>200,
                'message'  =>$var_link
            ];
        }
        
		
		return response()->json($response);

    }

    public function export(Request $request){
        $search = $request->search ? $request->search : '';
        $status = $request->status ? $request->status : '';
        $company = $request->company ? $request->company : 0;
        $type = $request->type ? $request->type : '';
		
		return Excel::download(new ExportCoa($search,$status,$company,$type), 'coa_'.uniqid().'.xlsx');
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
            Excel::import(new ImportCoa, $request->file('file'));

            return response()->json([
                'status'    => 200,
                'message'   => 'Import sukses!'
            ]);
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
            ];
            return response()->json($response);
        }
    }

    public function importMaster(Request $request)
    {
        Excel::import(new ImportCoaMaster,$request->file('file'));

        return response()->json([
            'status'    => 200,
            'message'   => 'Import sukses!'
        ]);
    }
}