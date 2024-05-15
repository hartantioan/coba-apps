<?php

namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;
use App\Models\ResourceGroup;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Exports\ExportTemplateMasterAsset;
use App\Models\Asset;
use App\Models\Place;
use App\Helpers\PrintHelper;
use App\Imports\ImportAsset;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use App\Exports\ExportAsset;
use App\Exports\ExportResource;
use App\Exports\ExportTemplateMasterResource;
use App\Imports\ImportResource;
use App\Models\Resource;
use App\Models\Unit;

class ResourceController extends Controller
{

    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
    }

    public function index()
    {
        $data = [
            'title'         => 'Resource',
            'content'       => 'admin.master_data.resource',
            'place'         => Place::where('status','1')->get(),
            'group'         => ResourceGroup::where('status','1')->get(),
            'unit'          => Unit::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'other_name',
            'resource_group_id',
            'uom_unit',
            'qty',
            'cost',
            'place_id',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Resource::count();
        
        $query_data = Resource::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('other_name','like',"%$search%");
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

        $total_filtered = Resource::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('other_name','like',"%$search%");
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
                    $val->other_name,
                    $val->resourceGroup->name,
                    number_format($val->qty,3,',','.'),
                    $val->uomUnit->code,
                    number_format($val->cost,0,',','.'),
                    $val->place()->exists() ? $val->place->code : '-',
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
            'code' 				    => $request->temp ? ['required', Rule::unique('resources', 'code')->ignore($request->temp)] : 'required|unique:resources,code',
            'name'                  => 'required',
            'place_id'              => 'required',
            'uom_unit'              => 'required',
            'qty'                   => 'required',
            'cost'                  => 'required',
            'resource_group_id'     => 'required',
        ], [
            'code.required' 	            => 'Kode tidak boleh kosong.',
            'code.unique'                   => 'Kode telah terpakai.',
            'name.required'                 => 'Nama tidak boleh kosong.',
            'place_id.required'             => 'Plant tidak boleh kosong.',
            'uom_unit.required'             => 'Satuan tidak boleh kosong.',
            'qty.required'                  => 'Jumlah qty tidak boleh kosong.',
            'cost.required'                 => 'Biaya tidak boleh kosong.',
            'resource_group_id.required'    => 'Grup Resource tidak boleh kosong.',
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
                    $query = Resource::find($request->temp);
                    $query->code                = $request->code;
                    $query->place_id            = $request->place_id ? $request->place_id : NULL;
                    $query->name	            = $request->name;
                    $query->other_name	        = $request->other_name;
                    $query->resource_group_id	= $request->resource_group_id;
                    $query->uom_unit	        = $request->uom_unit;
                    $query->qty	                = str_replace(',','.',str_replace('.','',$request->qty));
                    $query->cost	            = str_replace(',','.',str_replace('.','',$request->cost));
                    $query->status              = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = Resource::create([
                        'code'                      => $request->code,
                        'place_id'                  => $request->place_id ? $request->place_id : NULL,
                        'name'	                    => $request->name,
                        'other_name'	            => $request->other_name,
                        'resource_group_id'	        => $request->resource_group_id,
                        'uom_unit'	                => $request->uom_unit,
                        'qty'	                    => str_replace(',','.',str_replace('.','',$request->qty)),
                        'cost'	                    => str_replace(',','.',str_replace('.','',$request->cost)),
                        'status'                    => $request->status ? $request->status : '2',
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                activity()
                    ->performedOn(new Resource())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit resource.');

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
        $resource = Resource::find($request->id);
        $resource['cost'] = number_format($resource->cost,2,',','.');
        $resource['qty'] = number_format($resource->qty,3,',','.');
        				
		return response()->json($resource);
    }

    public function destroy(Request $request){
        $query = Resource::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new Resource())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the resource data');

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
            Excel::import(new ImportResource, $request->file('file'));

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
            $response = [
                'status'  => 500,
                'message' => "Data failed to save"
            ];
            return response()->json($response);
        }
    }

    public function export(Request $request){
		return Excel::download(new ExportResource($request->search,$request->status), 'resource_'.uniqid().'.xlsx');
    }

    public function print(Request $request){

        $validation = Validator::make($request->all(), [
            'arr_id'                => 'required',
        ], [
            'arr_id.required'       => 'Tolong pilih baris yang ingin di print terlebih dahulu.',
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
                $pr[] = Resource::where('code',$row)->first();
            }
            $data = [
                'title'     => 'Master Resource',
                'data'      => $pr
            ];  
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path);
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
            $pdf = Pdf::loadView('admin.print.master_data.resource', $data)->setPaper('a5', 'landscape');
            $pdf->render();
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            $content = $pdf->download()->getOriginalContent();


            $document_po = PrintHelper::savePrint($content);     $var_link=$document_po;

            $response =[
                'status'=>200,
                'message'  =>$document_po
            ];
        }
        
		
		return response()->json($response);

    }

    public function getImportExcel(){
        return Excel::download(new ExportTemplateMasterResource(), 'format_master_resource'.uniqid().'.xlsx');
    }
}