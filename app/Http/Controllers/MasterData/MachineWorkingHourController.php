<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MachineWorkingHour;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MachineWorkingHourController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Mesin',
            'content'   => 'admin.master_data.machine_working_hour',
            
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'code',
            'user_id',
            'note',
            'status'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = MachineWorkingHour::count();
        
        $query_data = MachineWorkingHour::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request) {
                                $query->where('employee_no', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%")
                                    ;
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

        $total_filtered = MachineWorkingHour::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request) {
                                $query->where('employee_no', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%")
                                    ;
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
                    $nomor,
                    $val->code,
                    $val->user->name,
                    $val->note,
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
            'code' 				=> $request->temp ? ['required', Rule::unique('machines', 'code')->ignore($request->temp)] : 'required|unique:machines,code',
            
            'note'              => 'required',
        ], [
            'code.required' 	    => 'Kode tidak boleh kosong.',
            'code.unique'           => 'Kode telah terpakai.',
           
            'note.required'         => 'Nama tidak boleh kosong.',
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
                    $query = MachineWorkingHour::find($request->temp);
                    $query->code            = $request->code;
                    $query->user_id         = session('bo_id');
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
                    $query = MachineWorkingHour::create([
                        'code'          => $request->code,
                        'user_id'       => session('bo_id'),
                        'name'			=> $request->name,
                        'note'          => $request->note,
                        'status'        => $request->status ? $request->status : '2'
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                activity()
                    ->performedOn(new MachineWorkingHour())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit machine.');

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
        $line = MachineWorkingHour::find($request->id);
        				
		return response()->json($line);
    }

    public function destroy(Request $request){
        $query = MachineWorkingHour::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new MachineWorkingHour())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the machine working hour data');

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

    // public function print(Request $request){

    //     $validation = Validator::make($request->all(), [
    //         'arr_id'                => 'required',
    //     ], [
    //         'arr_id.required'       => 'Tolong pilih Item yang ingin di print terlebih dahulu.',
    //     ]);
        
    //     if($validation->fails()) {
    //         $response = [
    //             'status' => 422,
    //             'error'  => $validation->errors()
    //         ];
    //     } else {
    //         $pr=[];
    //         $currentDateTime = Date::now();
    //         $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
    //         foreach($request->arr_id as $key =>$row){
    //             $pr[]= Machine::where('code',$row)->first();

    //         }
    //         $data = [
    //             'title'     => 'Master Machine',
    //             'data'      => $pr
    //         ];  
    //         $img_path = 'website/logo_web_fix.png';
    //         $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
    //         $image_temp = file_get_contents($img_path);
    //         $img_base_64 = base64_encode($image_temp);
    //         $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
    //         $data["image"]=$path_img;
    //         $pdf = Pdf::loadView('admin.print.master_data.machine', $data)->setPaper('a5', 'landscape');
    //         $pdf->render();
    //         $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
    //         $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
    //         $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
    //         $content = $pdf->download()->getOriginalContent();


    //         $document_po = PrintHelper::savePrint($content);     $var_link=$document_po;

    //         $response =[
    //             'status'=>200,
    //             'message'  =>$document_po
    //         ];
    //     }
        
		
	// 	return response()->json($response);

    // }

    // public function export(Request $request){
    //     $search = $request->search ? $request->search : '';
	// 	$status = $request->status ? $request->status : '';
		
	// 	return Excel::download(new ExportMachine($search,$status), 'line_'.uniqid().'.xlsx');
    // }
}
