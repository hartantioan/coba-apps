<?php

namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;
use App\Models\AssetGroup;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Exports\ExportTemplateMasterAsset;
use App\Models\Asset;
use App\Models\Place;
use App\Models\Department;
use App\Helpers\PrintHelper;
use App\Imports\ImportAsset;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use App\Exports\ExportAsset;

class AssetController extends Controller
{

    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
    }

    public function index()
    {
        $data = [
            'title'         => 'Aset',
            'content'       => 'admin.master_data.asset',
            'place'         => Place::where('status','1')->get(),
            'group'         => AssetGroup::where('status','1')->get()
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'asset_group_id',
            'date',
            'nominal',
            'accumulation_total',
            'book_balance',
            'count_balance',
            'method',
            'note',
            'status',
            'place_id',
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

                if($request->balance){
                    if($request->balance == '1'){
                        $query->where('book_balance', '>', 0)->whereNotNull('book_balance');
                    }elseif($request->balance == '2'){
                        $query->where('book_balance', '=', 0)->whereNotNull('book_balance');
                    }
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

                if($request->balance){
                    if($request->balance == '1'){
                        $query->where('book_balance', '>', 0)->whereNotNull('book_balance');
                    }elseif($request->balance == '2'){
                        $query->where('book_balance', '=', 0)->whereNotNull('book_balance');
                    }
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
                    $val->assetGroup->name,
                    $val->date ? date('d/m/Y',strtotime($val->date)) : '<span class=""><div class="chip red white-text z-depth-4">Belum dikapitalisasi.</div></span>',
                    $val->nominal > 0 ? number_format($val->nominal,2,',','.') : '<span class=""><div class="chip red white-text z-depth-4">Belum dikapitalisasi.</div></span>',
                    $val->nominal > 0 ? number_format($val->accumulation_total,2,',','.') : '<span class=""><div class="chip red white-text z-depth-4">Belum dikapitalisasi.</div></span>',
                    number_format($val->book_balance,2,',','.'),
                    number_format($val->count_balance,0,',','.'),
                    $val->method(),
                    $val->note,
                    $val->status(),
                    $val->place()->exists() ? $val->place->code : '-',
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
            'code' 				    => $request->temp ? ['required', Rule::unique('assets', 'code')->ignore($request->temp)] : 'required|unique:assets,code',
            'name'                  => 'required',
            'nominal'               => 'required',
            'method'                => 'required',
            'asset_group_id'        => 'required',
        ], [
            'code.required' 	            => 'Kode tidak boleh kosong.',
            'code.unique'                   => 'Kode telah terpakai.',
            'name.required'                 => 'Nama tidak boleh kosong.',
            'nominal.required'              => 'Nominal tidak boleh kosong.',
            'method.required'               => 'Metode hitung tidak boleh kosong.',
            'asset_group_id.required'       => 'Grup aset tidak boleh kosong.'
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
                    $query->name	        = $request->name;
                    $query->asset_group_id	= $request->asset_group_id;
                    $query->date	        = $request->date;
                    $query->nominal	        = str_replace(',','.',str_replace('.','',$request->nominal));
                    $query->method          = $request->method;
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
                        'name'              => $request->name,
                        'asset_group_id'    => $request->asset_group_id,
                        'date'              => $request->date,
                        'nominal'           => str_replace(',','.',str_replace('.','',$request->nominal)),
                        'method'            => $request->method,
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
        $asset['nominal'] = number_format($asset->nominal,2,',','.');
        				
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
		return Excel::download(new ExportAsset($request->search,$request->status,$request->balance,$this->dataplaces), 'asset_'.uniqid().'.xlsx');
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
                $pr[]= Asset::where('code',$row)->first();

            }
            $data = [
                'title'     => 'Master BOM',
                'data'      => $pr
            ];  
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path);
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
            $pdf = Pdf::loadView('admin.print.master_data.asset', $data)->setPaper('a5', 'landscape');
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
        return Excel::download(new ExportTemplateMasterAsset(), 'format_master_asset'.uniqid().'.xlsx');
    }
}