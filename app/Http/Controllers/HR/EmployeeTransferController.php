<?php

namespace App\Http\Controllers\HR;
use Illuminate\Support\Str;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\EmployeeTransfer;
use App\Models\Place;
use App\Models\Position;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Menu;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EmployeeTransferController extends Controller
{
    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));
        $menu = Menu::where('url', $lastSegment)->first();
        $data = [
            'title'         => '',
            'place'         => Place::where('status','1')->get(),
            'warehouse'     => Warehouse::where('status','1')->get(),
            'department'    => Department::where('status','1')->get(),
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'employee_code' => $request->employee_code ? CustomHelper::decrypt($request->code) : '',
            'position'      => Position::where('status','1')->get(),
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code,
            'content'       => 'admin.hr.employee_transfer'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }
    
    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'account_id',
            'type',
            'post_date',
            'note',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = EmployeeTransfer::count();
        
        $query_data = EmployeeTransfer::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                        ->orWhere('note','like', "%$search%")
                        ->orWhereHas('account', function ($query) use ($search) {
                            $query->where('name', 'like', "%$search%");
                        });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->type){
                    $query->where('type', $request->type);
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = EmployeeTransfer::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                        ->orWhere('note','like', "%$search%")
                        ->orWhereHas('account', function ($query) use ($search) {
                            $query->where('name', 'like', "%$search%");
                        });;
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->type){
                    $query->where('type', $request->type);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $btn = 
                ' <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->id) . '`)"><i class="material-icons dp48">create</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->id) . '`)"><i class="material-icons dp48">delete</i></button>';

                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->account->name ?? '-',
                    $val->typeRaw(),
                    date('d/m/y',strtotime($val->post_date)),
                    $val->note,
                    $val->status(),
                    $btn
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

    public function rowDetail(Request $request)
    {
        $data   = EmployeeTransfer::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12"><table style="min-width:100%;">
                    <tbody>';
            $string .= '
            <tr>
                <td class="center-align" style="font-weight: 700;background: cornsilk;">Kode</td>
                <td class="">'.($data->code).'</td>
            </tr>
            <tr>
                <td class="center-align" style="font-weight: 700;background: cornsilk;">User</td>
                <td class="">'.($data->user->name).'</td>
            </tr>
            <tr>
                <td class="center-align" style="font-weight: 700;background: cornsilk;">Tipe</td>
                <td class="">'.($data->typeRaw()).'</td>
            </tr>
            <tr>
                <td class="center-align" style="font-weight: 700;background: cornsilk;">Manager</td>
                <td class="">'.($data->manager()->exists() ? $data->manager->name : '-').'</td>
            </tr>
            <tr>
                <td class="center-align" style="font-weight: 700;background: cornsilk;">Tanggal Mulai</td>
                <td class="">'.($data->post_date).'</td>
            </tr>
            <tr>
                <td class="center-align" style="font-weight: 700;background: cornsilk;">Tanggal Akhir</td>
                <td class="">'.($data->valid_date).'</td>
            </tr>
            <tr>
                <td class="center-align" style="font-weight: 700;background: cornsilk;">Penempatan</td>
                <td class="">'.($data->place->name).'</td>
            </tr>
            <tr>
                <td class="center-align" style="font-weight: 700;background: cornsilk;">Posisi</td>
                <td class="">'.($data->position->name).'</td>
            </tr>
            <tr>   
                <td class="center-align"style="font-weight: 700;background: cornsilk;">Keterangan</td>
                <td class="">'.($data->note).'</td>
            </tr>';
        
        
        $string .= '</tbody></table></div>';
		
        return response()->json($string);
    }
    
    public function showFromCode(Request $request){
        $line = EmployeeTransfer::where('code',CustomHelper::decrypt($request->id))->first();
        if ($line->manager()->exists()) {
            $line['manager'] = $line->manager;
        }
        $line['user'] = $line->user;
        $line['position'] = $line->position;			
		return response()->json($line);
    }

    public function instantFormwCode(Request $request){
        $line = User::Find(CustomHelper::decrypt($request->id));
        if ($line->manager()->exists()) {
            $line['manager'] = $line->manager;
        }
        $line['position'] = $line->position;			
		return response()->json($line);
    }

    public function create(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'employee_id'          => 'required',
            'type'      => 'required',
            'post_date'       => 'required',
            'valid_date'       => 'required',
            'note'       => 'required',
        ], [
            'employee_id.required'         => 'Nama Pegawai tidak boleh kosong.',
            'post_date.required'     => 'Tanggal tidak boleh kosong.',
            'type.required'      => 'Tipe Transfer tidak boleh kosong.',
            'valid_date.required'      => 'Tanggal diberlakukan tidak boleh kosong.',
            'note.required'      => 'Keterangan tidak boleh kosong.',
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
                    
                    $query = EmployeeTransfer::find(CustomHelper::decrypt($request->temp));
                    $approved = false;
                    $revised = false;

                    if($query->approval()->exists()){
                        foreach ($query->approval() as $detail){
                            foreach($detail->approvalMatrix as $row){
                                if($row->approved){
                                    $approved = true;
                                }

                                if($row->revised){
                                    $revised = true;
                                }
                            }
                        }
                    }

                    if($approved && !$revised){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Purchase Order telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','6'])){

                        $query->account_id          = $request->employee_id;
                        $query->plant_id         = $request->plant_id;
                        $query->manager_id               = $request->manager_id;
                       
                        $query->position_id               = $request->position_id;
                        $query->type          = $request->type;
                        $query->note            = $request->note;
                        $query->valid_date      =$request->valid_date;
                        $query->post_date       =$request->post_date;
                        $query->save();
                        DB::commit();
                    }
                    else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status purchase order sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{

                DB::beginTransaction();
                try {
                    $query = EmployeeTransfer::create([
                        'code'              => EmployeeTransfer::generateCode(),
                        'user_id'           => session('bo_id'),
                        'account_id'	    => $request->employee_id,
                        'plant_id'          => $request->plant_id,
                        'manager_id'        => $request->manager_id,
                        
                        'position_id'       => $request->position_id,
                        'type'              => $request->type,
                        'note'              => $request->note,
                        'status'            => '1',
                        'valid_date'        => $request->valid_date,
                        'post_date'         => $request->post_date,
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                
                CustomHelper::sendApproval('employee_transfers',$query->id,$query->note);
                CustomHelper::sendNotification('employee_transfers',$query->id,'Pengajuan Employee Transfer No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new EmployeeTransfer())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit  Employee Transfer.');

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
        $line = EmployeeTransfer::find(CustomHelper::decrypt($request->id));
        $line['employee']=$line->account;
        $line['manager']=$line->manager;
        $line['position'] = $line->position;				
		return response()->json($line);
    }

    public function destroy(Request $request){
        $query = EmployeeTransfer::find($request->id);
        $approved = false;
        $revised = false;

        if($query->approval()){
            foreach ($query->approval() as $detail){
                foreach($detail->approvalMatrix as $row){
                    if($row->approved){
                        $approved = true;
                    }

                    if($row->revised){
                        $revised = true;
                    }
                }
            }
        }

        if($approved && !$revised){
            return response()->json([
                'status'  => 500,
                'message' => 'Purchase Order telah diapprove, anda tidak bisa melakukan perubahan.'
            ]);
        }
		if(in_array($query->status,['2','3','4','5'])){
            return response()->json([
                'status'  => 500,
                'message' => 'Jurnal sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        if($query->delete()) {

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

    public function voidStatus(Request $request){
        $query = EmployeeTransfer::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {
            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new EmployeeTransfer())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the Employee Transfer data');
    
                CustomHelper::sendNotification('employee_transfers',$query->id,'Employee Transfer No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('employee_transfers',$query->id);
                CustomHelper::revertBackEmployeeTransfer($query);
                $response = [
                    'status'  => 200,
                    'message' => 'Data closed successfully.'
                ];
            }
        } else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }

        return response()->json($response);
    }

    public function approval(Request $request,$id){
        
        $pr = EmployeeTransfer::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Employee Transfer',
                'data'      => $pr
            ];

            return view('admin.approval.employee_transfer', $data);
        }else{
            abort(404);
        }
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
            $var_link=[];
            $currentDateTime = Date::now();
            $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
            foreach($request->arr_id as $key =>$row){
                $pr = EmployeeTransfer::where('code',$row)->first();
                if($pr){
                    $data = [
                        'title'     => '',
                        'data'      => $pr
                    ];
                    
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $pdf = Pdf::loadView('admin.print.hr.employee_transfer', $data)->setPaper('a5', 'landscape');
                    $pdf->render();
                    $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                    $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                    $content = $pdf->download()->getOriginalContent();
                    $temp_pdf[]=$content;
                }
                    
            }
            $merger = new Merger();
            foreach ($temp_pdf as $pdfContent) {
                $merger->addRaw($pdfContent);
            }


            $result = $merger->merge();


            $randomString = Str::random(10); 

         
                    $filePath = 'public/pdf/' . $randomString . '.pdf';
                    

                    Storage::put($filePath, $result);
                    
                    $document_po = asset(Storage::url($filePath));
                    $var_link=$document_po;

            $response =[
                'status'=>200,
                'message'  =>$var_link
            ];
        }
        
		
		return response()->json($response);

    }

    public function printByRange(Request $request){
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
        if($request->type_date == 1){
            $validation = Validator::make($request->all(), [
                'range_start'                => 'required',
                'range_end'                  => 'required',
            ], [
                'range_start.required'       => 'Isi code awal yang ingin di pilih menjadi awal range',
                'range_end.required'         => 'Isi code terakhir yang menjadi akhir range',
            ]);
            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            }else{
                $total_pdf = intval($request->range_end)-intval($request->range_start);
                $temp_pdf=[];
                if($request->range_start>$request->range_end){
                    $kambing["kambing"][]="code awal lebih besar daripada code akhir";
                    $response = [
                        'status' => 422,
                        'error'  => $kambing
                    ]; 
                }
                elseif($total_pdf>31){
                    $kambing["kambing"][]="PDF lebih dari 30 buah";
                    $response = [
                        'status' => 422,
                        'error'  => $kambing
                    ];
                }else{   
                    for ($nomor = intval($request->range_start); $nomor <= intval($request->range_end); $nomor++) {
                        $lastSegment = $request->lastsegment;
                      
                        $menu = Menu::where('url', $lastSegment)->first();
                        $nomorLength = strlen($nomor);
                        
                        // Calculate the number of zeros needed for padding
                        $paddingLength = max(0, 8 - $nomorLength);

                        // Pad $nomor with leading zeros to ensure it has at least 8 digits
                        $nomorPadded = str_repeat('0', $paddingLength) . $nomor;
                        $x =$menu->document_code.$request->year_range.$request->code_place_range.'-'.$nomorPadded; 
                        $query = EmployeeTransfer::where('code', 'LIKE', '%'.$x)->first();
                        
                        if($query){
                            $data = [
                                'title'     => 'Fund Request',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.hr.employee_transfer', $data)->setPaper('a5', 'landscape');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;
                           
                        }
                    }
                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }


                    $result = $merger->merge();


                    $randomString = Str::random(10); 

         
                    $filePath = 'public/pdf/' . $randomString . '.pdf';
                    

                    Storage::put($filePath, $result);
                    
                    $document_po = asset(Storage::url($filePath));
                    $var_link=$document_po;
                } 

            }
        }elseif($request->type_date == 2){
            $validation = Validator::make($request->all(), [
                'range_comma'                => 'required',
                
            ], [
                'range_comma.required'       => 'Isi input untuk comma',
                
            ]);
            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            }else{
                $arr = explode(',', $request->range_comma);
                
                $merged = array_unique(array_filter($arr));

                if(count($merged)>31){
                    $kambing["kambing"][]="PDF lebih dari 30 buah";
                    $response = [
                        'status' => 422,
                        'error'  => $kambing
                    ];
                }else{
                    foreach($merged as $code){
                        $etNumbersArray = explode(',', $request->tabledata);
                        $query = EmployeeTransfer::where('code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $data = [
                                'title'     => 'Fund Request',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.hr.employee_transfer', $data)->setPaper('a5', 'landscape');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;
                           
                        }
                    }
                    
                    
                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }
    
    
                    $result = $merger->merge();
    
    
                    $randomString = Str::random(10); 

         
                    $filePath = 'public/pdf/' . $randomString . '.pdf';
                    

                    Storage::put($filePath, $result);
                    
                    $document_po = asset(Storage::url($filePath));
                    $var_link=$document_po;
        
                    $response =[
                        'status'=>200,
                        'message'  =>$var_link
                    ];
                }
            }
        }
        return response()->json($response);
    }

    public function printIndividual(Request $request,$id){
        
        $pr = EmployeeTransfer::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');        
        if($pr){

            $data = [
                'title'     => 'Fund Request',
                'data'      => $pr
            ];

            $opciones_ssl=array(
                "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
                ),
            );
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path, false, stream_context_create($opciones_ssl));
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
             
            $pdf = Pdf::loadView('admin.print.hr.employee_transfer', $data)->setPaper('a5', 'landscape');
            $pdf->render();
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            $randomString = Str::random(10); 
         
            $filePath = 'public/pdf/' . $randomString . '.pdf';
            

            Storage::put($filePath, $content);
            
            $document_po = asset(Storage::url($filePath));
         
    
    
            return $document_po;
        }else{
            abort(404);
        }
    }
}
