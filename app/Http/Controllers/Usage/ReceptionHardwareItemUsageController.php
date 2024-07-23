<?php

namespace App\Http\Controllers\Usage;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use App\Models\HardwareItem;
use App\Helpers\CustomHelper;
use App\Models\ReceptionHardwareItemsUsage;
use App\Exports\ExportReceptionHardwareUsage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Helpers\PrintHelper;
class ReceptionHardwareItemUsageController extends Controller
{
    public function index(Request $request)
    {
        $data = [
            'minDate'       => $request->get('minDate'),
            'title' => 'Penyerahan Hardware Item',
            'content' => 'admin.usage.reception_hardware_items_usages'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function fetchStorage(Request $request){
        $InStorage = HardwareItem::where('status', '1')
                    ->whereHas('receptionHardwareItemsUsage', function ($query) {
                        $query->where('status', '1');
                    }, '=', 0)
                    ->orDoesntHave('receptionHardwareItemsUsage')
                    ->get();
        $item_ready=[];
        foreach ($InStorage as $item) {
            $itemName = $item->item;
         
            $itemData = [
                'item_id' => $item->id,
                'itemName' => $itemName,
                'itemCode' => $item->code,
                'itemdetail' => $item->detail1. (isset($item->detail2) ? ' - ' . $item->detail2 : ''),
            ];
            $item_ready[] = $itemData;
        }

        $response['itemInStorage']=$item_ready;
        return response()->json($response);
    }

    public function export(Request $request){
		$start_date = $request->start_date ? $request->start_date : ''   ;
        $finish_date = $request->finish_date ? $request->finish_date : '';
        $search = $request->search ? $request->search : '';

		return Excel::download(new ExportReceptionHardwareUsage($search),'serah_terima_inventaris'.uniqid().'.xlsx');
    }

    public function store_w_barcode(Request $request){
        $barcode = $request->input('barcode');       

        DB::beginTransaction();
        try{
            $query_code = HardwareItem::where('code',$barcode)->first();
            $query_hardware_item_id = $query_code->id;
            $lastInsertedData = ReceptionHardwareItemsUsage::where('hardware_item_id', $query_hardware_item_id)
                            ->latest()
                            ->first();
        
            if($lastInsertedData->status == '4'|| $lastInsertedData->status == '2' ){
                $response = [
                    'status'    => 500,
                    'message'   => 'Permintaan Pengembalian Barang ditolak karena barang masih berada di gudang.'
                ];
            }elseif($lastInsertedData->status == '0'){
                $response = [
                    'status'    => 500,
                    'message'   => 'Barang masih belum memiliki tuan'
                ];
            }else{
               
                $lastInsertedData->status = '2';
                $lastInsertedData->status_item = '2';
                $lastInsertedData->return_date = $request->date;
                $lastInsertedData->user_return = session('bo_id');
                $lastInsertedData->return_note = "Dikembalikan ke gudang dengan barcode";
                $lastInsertedData->save();

                if($lastInsertedData){

                    DB::commit();
                    $response = [
                        'status'    => 200,
                        'message'   => 'Data successfully saved.',
                    ];
                }
                
                

            }
            

        }catch(\Exception $e){
            DB::rollback();
            $response = [
                'status'    => 500,
                'message'   => 'Data failed to save cause'. $e -> getMessage(),
            ];
        }
        
        
        
       
        return response()->json($response);
    }

    public function saveTargeted(Request $request){
        info($request);
        $validation = Validator::make($request->all(), [
            'user_id1'                       => 'required',
            'date1'                          => 'required',
            'info1'                          => 'required',
        ], [
            'user_id1.required'                => 'Pilih User untuk Penyerahan',
            'date1.required'             => 'Tanggal tidak boleh kosong.',
            'info1.required'    => 'Info tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
			
			
            DB::beginTransaction();
            
            $query = ReceptionHardwareItemsUsage::create([
                'code'              => ReceptionHardwareItemsUsage::generateCode(),
                'user_id'           => session('bo_id'),
                'account_id'        => $request->user_id1,
                'hardware_item_id'  => $request->tempes,
                'info'              => $request->info1,
                'date'              => now(),
                'division'          => $request->division1,
                'reception_date'    => $request->date1,
                'status'            => 1,
                'status_item'       => 1,
                'location'			=> $request->location1,
            ]);
            DB::commit();
            
			
			
			if($query) {

                activity()
                    ->performedOn(new ReceptionHardwareItemsUsage())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit reception hardware item usage.');

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

    public function datatable(Request $request){
        $column = [
            'code',
            'account_id',
            'hardware_item_id',
            'location',
            'date',
            'reception_date',
            'info',
            'return_date', 
            'return_note',
            'status',
            'user_return',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ReceptionHardwareItemsUsage::count();
        
        $query_data = ReceptionHardwareItemsUsage::where(function($query) use ($search, $request) {
               
               
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhere('code', 'like', "%$search%")
                            ->orWhere('location', 'like', "%$search%")
                            ->where('status_item', '1');
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

        

        $total_filtered = ReceptionHardwareItemsUsage::where(function($query) use ($search, $request) {
                
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhere('code', 'like', "%$search%")
                            ->orWhere('location', 'like', "%$search%")
                            ->where('status_item', '1');
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
				if($val->status == 2 || $val->status == 4 || $val->status == 0){
                    $button = '-';
                }if($val->status == 1 ){
                    $button = '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light brown darken-2 white-text btn-small" data-popup="tooltip" title="Print" data-item-id="'. $val->id .'" onclick="openmodal('. $val->id .')"><i class="material-icons dp48">filter_frames</i></button>
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue accent-2 white-text btn-small" data-popup="tooltip" title="Return" onclick="returnItem(' . $val->id . ')"><i class="material-icons dp48">call_missed_outgoing</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' .  $val->id . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
					';
                }if($val->status == 2 ){
                    $button = '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light brown darken-2 white-text btn-small" data-popup="tooltip" title="Print" data-item-id="'. $val->id .'" onclick="openmodal('. $val->id .')"><i class="material-icons dp48">filter_frames</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
					';
                }
                $response['data'][] = [
                    $nomor,
                    $val->code,
                    $val->user->name ?? '-',
                    $val->hardwareItem->code ?? '',
                    $val->hardwareItem->item ?? '',
                    $val->location,
                    date('d/m/Y',strtotime($val->date)),
                    date('d/m/Y',strtotime($val->reception_date)),
                    $val->info,
                    $val->account->name ?? '',
                    $val->return_date ? date('d/m/Y', strtotime($val->return_date)) : ' ',
                    $val->return_note,
                    $val->status(),
                    $button
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

    public function printModal(Request $request){
        $reception = ReceptionHardwareItemsUsage::find($request->id);
       
        $string='';
        $string.='<div class="col s12 center-align" >
                    <h4>Print</h4>
                    </div>
                    <div class="col s12 m6 center-align">
                        <button class="btn waves-effect waves-light  submit" onclick="printData(' . $request->id . ');">Penyerahan <i class="material-icons right">send</i></button>
                    </div>
                   ';
        if($reception->return_date){
            $string.=' <div class="col s12 m6 center-align">
                <button class="btn waves-effect waves-light  submit" onclick="printDataReturn(' . $request->id . ');">Pengembalian <i class="material-icons right">send</i></button>
            </div>';
        }
        $string.='</div>';
        return response()->json($string);
    }

    public function show(Request $request){
        $reception = ReceptionHardwareItemsUsage::find($request->id);
        $reception['item']=$reception->hardwareItem;
        $reception['name']=$reception->hardwareItem->item;
        $reception['detail1']=$reception->hardwareItem->detail1;
        $reception['user']=$reception->account;
        $reception['division']=$reception->division ?? null;
		return response()->json($reception);
    }
    public function showItem(Request $request){
        $reception = HardwareItem::find($request->id);
        $reception['name']=$reception->item;
		return response()->json($reception);
    }

    public function diversion(Request $request){
        $validation = Validator::make($request->all(), [
            'date'                          => 'required',
           
            'info'                          => 'required',
        ], [
            'date.required'                 => 'Tanggal tidak boleh kosong.',
           
            'info.required'                 => 'Info tidak boleh kosong.',
        ]);
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            DB::beginTransaction();
            
            $user = null;
                try {
                    $query_item= ReceptionHardwareItemsUsage::find($request->temp);
                    if($request->date < $query_item->reception_date){
                        $kambing["kambing"][]="Tanggal Pengembalian kurang dari tanggal Penyerahan";
                        $response = [
                            'status' => 422,
                            'error'  => $kambing
                        ];
                        return response()->json($response);
                    }
                    $query_item->status = '2';
                    $query_item->status_item = '2';
                    $query_item->return_date = $request->date;
                    $query_item->user_return = session('bo_id');
                    $query_item->return_note = $request->info;
                    $query_item->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
                DB::commit();
                
            $response = [
                'status'  => 200,
                'message' => 'Item Berhasil Dikembalikan.'
            ];
        }
        return response()->json($response);
    }

    public function create(Request $request){
        
        if($request->tempe){
            $validation = Validator::make($request->all(), [
                
                'date'                          => 'required',
                'info'                          => 'required',
                'location'                      => 'required',
            ], [
                
                'date.required'                         => 'Tanggal tidak boleh kosong.',
                'info.required'                         => 'Keterangan tidak boleh kosong.',
                'location.required'                     => 'Lokasi tidak boleh kosong.',
            ]);
        }else{
            $validation = Validator::make($request->all(), [
                'hardware_item_id'              => 'required',
                'user_id'                       => 'required',
                'date'                          => 'required',
                'info'                          => 'required',
                'location'                      => 'required',
            ], [
                'hardware_item_id.required' 	        => 'Harap Pilih Item Terlebih dahulu',
                'user_id.required'                      => 'Pilih User untuk Penyerahan',
                'date.required'                         => 'Tanggal tidak boleh kosong.',
                'info.required'                         => 'Keterangan tidak boleh kosong.',
                'location.required'                     => 'Lokasi tidak boleh kosong.',
            ]);
        }
        

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
			if($request->tempe){
                DB::beginTransaction();
                try {
                    $query = ReceptionHardwareItemsUsage::find($request->tempe);
                    $query->date	            = $request->date;
                    $query->location	        = $request->location;
                    $query->division	        = $request->division;
                    $query->info	            = $request->info;
                    // $query->status              = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = ReceptionHardwareItemsUsage::create([
                        'code'              => ReceptionHardwareItemsUsage::generateCode(),
                        'user_id'           => session('bo_id'),
                        'account_id'        => $request->user_id,
                        'hardware_item_id'  => $request->hardware_item_id,
                        'info'              => $request->info,
                        'date'              => now(),
                        'division'          => $request->division,
                        'reception_date'    => $request->date,
                        'status'            => 1,
                        'status_item'       => 1,
                        'location'			=> $request->location,
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                activity()
                    ->performedOn(new ReceptionHardwareItemsUsage())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit reception hardware item usage.');

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

    public function destroy(Request $request){
        $query = ReceptionHardwareItemsUsage::find($request->id);
    
        if($query->delete()) {
            
            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);
            

            activity()
            ->performedOn(new ReceptionHardwareItemsUsage())
            ->causedBy(session('bo_id'))
            ->withProperties($query)
            ->log('Delete the reception hardwareitemusage data');
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

    public function print( Request $request){

        $RH = ReceptionHardwareItemsUsage::find($request->id);
        if($RH){
            $data = [
                'title' => 'Surat Serah Terima',
                'data' => $RH,
                'user' => $RH->user
            ];
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path);
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
            
            $pdf = Pdf::loadView('admin.print.usage.reception_hardware', $data)->setPaper('a4', 'portrait');
  
            $content = $pdf->download()->getOriginalContent();
            $document_po = PrintHelper::savePrint($content);     $var_link=$document_po;

            return $document_po;
        }else{
            abort(404);
        }

    }

    public function printReturn( Request $request){

        $RH = ReceptionHardwareItemsUsage::find($request->id);
        if($RH){
            $data = [
                'title' => 'Surat Pengembalian',
                'data' => $RH,
                'user' => $RH->user
            ];
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path);
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
            
            $pdf = Pdf::loadView('admin.print.usage.return_hardware', $data)->setPaper('a4', 'portrait');
            

           
            $content = $pdf->download()->getOriginalContent();
            $document_po = PrintHelper::savePrint($content);     $var_link=$document_po;

            return $document_po;
        }else{
            abort(404);
        }

    }
}
