<?php

namespace App\Http\Controllers\MasterData;
use App\Imports\ImportEmployeeSchedule;
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
use App\Models\Shift;
use App\Models\Place;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportShift;
use App\Helpers\PrintHelper;
use Maatwebsite\Excel\Validators\ValidationException;

class ShiftController extends Controller
{
    public function index()
    {
        $data = [
            'title'         => 'Shift',
            'content'       => 'admin.master_data.shift',
            'place'         => Place::where('status','1')->get(),
            'department'    => Department::where('status','1')->get()
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'production_code',
            'place_id',
            // 'department_id',
            'name',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Shift::count();
        
        $query_data = Shift::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('production_code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhereHas('place',function($query) use($search,$request){
                                $query->where('name','like',"%$search%");
                            });
                            // ->orWhereHas('department',function($query) use($search,$request){
                            //     $query->where('name','like',"%$search%");
                            // });
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

        $total_filtered = Shift::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('production_code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhereHas('place',function($query) use($search,$request){
                                $query->where('name','like',"%$search%");
                            });
                            // ->orWhereHas('department',function($query) use($search,$request){
                            //     $query->where('name','like',"%$search%");
                            // });
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
                    $val->production_code,
                    $val->place->code,
                  /*   $val->department->name, */
                    $val->name,
                    $val->time_in,
                    $val->time_out,
                    $val->tolerant,
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
        
        $time_in = strtotime($request->time_in);
    
       
        /* $query_department= Department::find($request->department_id); */
        $query_place= Place::find($request->place_id);
        $stime_in = date('H:i', strtotime($request->time_in));
        $stime_out = date('H:i', strtotime($request->time_out));
        $code = $query_place->id.'||'.$stime_in . ' - ' . $stime_out;

        $request->merge(['code' => $code]);

        $validation = Validator::make($request->all(), [
            'code'           => $request->temp ? ['required', Rule::unique('shifts', 'code')->ignore($request->temp)] : 'required|unique:shifts,code',
            'name'           => 'required',
            'place_id'       => 'required',
            /* 'department_id'  => 'required', */
            'time_in'        => 'required',
            'time_out'       => 'required',
            'tolerant'          => 'required',
            // 'total_shift'   => 'required',
            
            
        ], [
            'code.required' 	     => 'Kode tidak boleh kosong.',
            'code.unique'            => 'Kode telah terpakai.',
            'name.required'          => 'Nama Shift tidak boleh kosong.',
            'place_id.required'      => 'Plant tidak boleh kosong.',
            /* 'department_id.required' => 'Departemen tidak boleh kosong',
           */
            'time_in.required'       => 'Jam masuk tidak boleh kosong',
            'time_out.required'      => 'Jam pulang tidak boleh kosong',
            'tolerant.required'      => 'toleransi keterlambatan tidak boleh kosong',
            // 'total_shift'            => 'Total Shift perlu diisi minimal 1' 
        ]);
        

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            /* if($time_in < $min_time_in){
                return response()->json([
                    'status'    => 500,
                    'message'   => 'Jam masuk tidak boleh kurang dari minimum jam masuk.',
                ]);
            } */

            // if($request->total_shift < 0){
            //     return response()->json([
            //         'status'    => 500,
            //         'message'   => 'Total Shift kurang dari 1',
            //     ]);
            // }

			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = Shift::find($request->temp);
                    $query->name                = $request->name;
                    $query->production_code     = $request->production_code ?? '';
                    $query->edit_id             = session('bo_id');
                    $query->place_id            = $request->place_id;
                  /*   $query->department_id       = $request->department_id; */
                    $query->name                = $request->name;
                    $query->is_next_day         = $request->is_next_day;
                    // $query->total_shift         = $request->total_shift;
                    $query->time_in             = $request->time_in;
                    $query->time_out            = $request->time_out;
                    $query->tolerant            = $request->tolerant;
                    $query->status              = $request->status ? $request->status : '2';
                    $query->save();

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $time_in = date('H:i', strtotime($request->time_in));
                    $time_out = date('H:i', strtotime($request->time_out));
                 /*    $query_department= Department::find($request->department_id); */
                    $query_place= Place::find($request->place_id);
                    $code = $query_place->id.'||'.$time_in . ' - ' . $time_out;
                    $query = Shift::create([
                        'code'              => $code,
                        'production_code'   => $request->production_code ?? '',
                        'name'			    => $request->name,
                        'user_id'           => session('bo_id'),
                        'place_id'          => $request->place_id,
                        /* 'department_id'     => $request->department_id, */
                        // 'total_shift'       => $request->total_shift,
                        'time_in'           => $request->time_in,
                        'time_out'          => $request->time_out,
                        'is_next_day'       => $request->is_next_day ?? 0,
                        'tolerant'          => $request->tolerant,
                        'status'            => $request->status ? $request->status : '2',
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
                  
			}
			
			if($query) {               

                activity()
                    ->performedOn(new Shift())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit shift data.');

				$response = [
					'status'    => 200,
					'message'   => 'Data successfully saved.',
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
        $shift = Shift::find($request->id);
        $shift['min_time_in'] = date('H:i',strtotime($shift->min_time_in));
        $shift['time_in'] = date('H:i',strtotime($shift->time_in));
        $shift['time_out'] = date('H:i',strtotime($shift->time_out));
        $shift['max_time_out'] = date('H:i',strtotime($shift->max_time_out));
        
		return response()->json($shift);
    }

    

    public function destroy(Request $request){
        $query = Shift::find($request->id);
		
        if($query->edit_id){
            return response()->json([
                'status'    => 500,
                'message'   => 'Anda tidak bisa menghapus, waktu shift telah dirubah oleh HRD.',
            ]);
        }

        if($query->delete()) {
            activity()
                ->performedOn(new Shift())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the shift data');

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
                $pr[]= Shift::where('code',$row)->first();

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
            $pdf = Pdf::loadView('admin.print.master_data.shift', $data)->setPaper('a5', 'landscape');
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

    public function export(Request $request){
        $search = $request->search ? $request->search : '';
        $status = $request->status ? $request->status : '';
		
		return Excel::download(new ExportShift($search,$status), 'shift_'.uniqid().'.xlsx');
    }
}