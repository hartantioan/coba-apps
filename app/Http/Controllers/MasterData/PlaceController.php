<?php

namespace App\Http\Controllers\MasterData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Place;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportPlace;
use App\Models\Company;
use App\Models\Region;

class PlaceController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Plant',
            'content'   => 'admin.master_data.place',
            'company'   => Company::all(),
            'province'  => Region::whereRaw("LENGTH(code) = 2")->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'address',
            'company_id',
            'type',
            'province_id',
            'city_id',
            'district_id',
            'subdistrict_id',
            'capacity',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Place::count();
        
        $query_data = Place::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('address', 'like', "%$search%")
                            ->orWhereHas('province', function ($query) use ($search) {
                                    $query->where('name', 'like', "%$search%");
                                }
                            )->orWhereHas('city', function ($query) use ($search) {
                                    $query->where('name', 'like', "%$search%");
                            })->orWhereHas('subdistrict', function ($query) use ($search) {
                                $query->where('name', 'like', "%$search%");
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

        $total_filtered = Place::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('address', 'like', "%$search%")
                            ->orWhereHas('province', function ($query) use ($search) {
                                    $query->where('name', 'like', "%$search%");
                                }
                            )->orWhereHas('city', function ($query) use ($search) {
                                    $query->where('name', 'like', "%$search%");
                                }
                            )->orWhereHas('subdistrict', function ($query) use ($search) {
                                $query->where('name', 'like', "%$search%");
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
                    $val->code,
                    $val->name,
                    $val->address,
                    $val->company->name,
                    $val->type(),
                    $val->province->name,
                    $val->city->name,
                    $val->district->name??'',
                    $val->subdistrict->name,
                    number_format($val->capacity,3,',','.'),
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
            'code'              => $request->temp ? ['required', Rule::unique('places', 'code')->ignore($request->temp),'min:2','max:2'] : 'required|unique:places,code|min:2|max:2',
            'name' 				=> 'required',
            'address'           => 'required',
            'company_id'        => 'required',
            'type'              => 'required',
            'province_id'       => 'required',
            'city_id'           => 'required',
            'district_id'       => 'required',
            'subdistrict_id'    => 'required',
            'capacity'          => 'required',
        ], [
            'code.required'             => 'Kode tidak boleh kosong.',
            'code.unique'               => 'Kode telah terpakai.',
            'code.min'                  => 'Panjang karakter kode minimal 2.',
            'code.max'                  => 'Panjang karakter kode maksimal 2.',
            'name.required' 	        => 'Nama tidak boleh kosong.',
            'address.required'          => 'Alamat tidak boleh kosong.',
            'company_id.required'       => 'Cabang tidak boleh kosong.',
            'type.required'             => 'Tipe tidak boleh kosong.',
            'province_id.required'      => 'Provinsi tidak boleh kosong.',
            'city_id.required'          => 'Kota tidak boleh kosong.',
            'district_id.required'      => 'Kecamatan tidak boleh kosong.',
            'subdistrict_id.required'   => 'Kelurahan tidak boleh kosong.',
            'capacity.required'         => 'Kapasitas satuan terkecil tidak boleh kosong.',
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
                    $query = Place::find($request->temp);
                    $query->code            = $request->code ? $request->code : $query->code;
                    $query->name            = $request->name;
                    $query->address	        = $request->address;
                    $query->company_id	    = $request->company_id;
                    $query->type	        = $request->type;
                    $query->province_id     = $request->province_id;
                    $query->city_id         = $request->city_id;
                    $query->district_id     = $request->district_id;
                    $query->subdistrict_id  = $request->subdistrict_id;
                    $query->capacity        = str_replace(',','.',str_replace('.','',$request->capacity));
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = Place::create([
                        'code'          => $request->code ? $request->code : Place::generateCode(),
                        'name'			=> $request->name,
                        'address'	    => $request->address,
                        'company_id'    => $request->company_id,
                        'type'          => $request->type,
                        'province_id'   => $request->province_id,
                        'city_id'       => $request->city_id,
                        'district_id'   => $request->district_id,
                        'subdistrict_id'=> $request->subdistrict_id,
                        'capacity'      => str_replace(',','.',str_replace('.','',$request->capacity)),
                        'status'        => $request->status ? $request->status : '2'
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                activity()
                    ->performedOn(new Place())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit plant/office.');

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
        $plant = Place::find($request->id);
        $plant['province_name'] = $plant->province->code.' - '.$plant->province->name;
        $plant['city_name'] = $plant->city->code.' - '.$plant->city->name;
        $plant['district_name'] = $plant->district->code.' - '.$plant->district->name;
        $plant['subdistrict_name'] = $plant->subdistrict->code.' - '.$plant->subdistrict->name;
        $plant['capacity'] = number_format($plant->capacity,3,',','.');
        				
		return response()->json($plant);
    }

    public function destroy(Request $request){
        $query = Place::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new Place())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the plant/office data');

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
                $pr[]= Place::where('code',$row)->first();

            }
            $data = [
                'title'     => 'Master Shift',
                'data'      => $pr
            ];  
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path);
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
            $pdf = Pdf::loadView('admin.print.master_data.place', $data)->setPaper('a5', 'landscape');
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
		
		return Excel::download(new ExportPlace($search,$status), 'place_'.uniqid().'.xlsx');
    }
}