<?php

namespace App\Http\Controllers\Usage;

use App\Http\Controllers\Controller;
use App\Models\HardwareItem;
use App\Models\ReceptionHardwareItemsUsage;
use App\Models\ReturnHardwareItemsUsage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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
                    ->doesntHave('receptionHardwareItemsUsage')
                    ->get();
        $item_ready=[];
        foreach ($InStorage as $item) {
            $itemName = $item->item->name;
         
            $itemData = [
                'item_id' => $item->id,
                'itemName' => $itemName,
                
            ];
            $item_ready[] = $itemData;
        }

        $response['itemInStorage']=$item_ready;
        return response()->json($response);
    }

    public function saveTargeted(Request $request){
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
                'user_id'           => $request->user_id1,
                'hardware_item_id'  => $request->tempe,
                'info'              => $request->info1,
                'date'              => $request->date1,
                'status'            => $request->status1,
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
            'user_id',
            'hardware_item_id',
            'info',
            'date',
            'status',
            'location'
        ];

        
        
        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ReceptionHardwareItemsUsage::count();
        
        $query_data = ReceptionHardwareItemsUsage::where(function($query) use ($search, $request) {
               
                $query->where('status', '1');
                
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhere('code', 'like', "%$search%")
                            ->orWhere('location', 'like', "%$search%")
                            ->where('status', '1');
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
                if(!$search) {
                    $query->where('status', '1');
                }
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhere('code', 'like', "%$search%")
                            ->orWhere('location', 'like', "%$search%")
                            ->where('status', '1');
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
                    $val->user->name ?? '-',
                    $val->hardwareItem->item->name,
                    $val->location,
                    $val->date,
                    $val->info,
                    $val->status(),
                    '
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light brown darken-2 white-text btn-small" data-popup="tooltip" title="Return" onclick="printData(' . $val->id . ')"><i class="material-icons dp48">filter_frames</i></button>
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue accent-2 white-text btn-small" data-popup="tooltip" title="Return" onclick="returnItem(' . $val->id . ')"><i class="material-icons dp48">call_missed_outgoing</i></button>
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
        $reception = ReceptionHardwareItemsUsage::find($request->id);
        $reception['item']=$reception->hardwareItem;
        $reception['user']=$reception->user;
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
                try {
                    $query = ReceptionHardwareItemsUsage::find($request->temp);
                    $query->status = '4';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                
                $query = ReturnHardwareItemsUsage::create([
                    'code'                              => ReturnHardwareItemsUsage::generateCode(),
                    'reception_hardware_item_usage_id'  => $request->temp,
                    'date'                              => $request->date,
                    'info'                              => $request->info,
                    'status'                            => $request->status,
                ]);
                DB::commit();
                
            $response = [
                'status'  => 200,
                'message' => 'Item Berhasil Dikembalikan.'
            ];
        }
        return response()->json($response);
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'hardware_item_id'              => 'required',
            'user_id'                       => 'required',
            'date'                          => 'required',
            'info'                          => 'required',
        ], [
            'hardware_item_id.required' 	        => 'Harap Pilih Item Terlebih dahulu',
            'user_id.required'                => 'Pilih User untuk Penyerahan',
            'date.required'             => 'Tanggal tidak boleh kosong.',
            'info.required'    => 'Info tidak boleh kosong.',
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
                    $query = ReceptionHardwareItemsUsage::find($request->temp);
                    $query->hardware_item_id	= $request->hardware_item_id;
                    $query->date	= $request->date;
                    $query->location	= $request->location;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = ReceptionHardwareItemsUsage::create([
                        'code'          => ReceptionHardwareItemsUsage::generateCode(),
                        'user_id'          => $request->user_id,
                        'hardware_item_id'          => $request->hardware_item_id,
                        'info'          => $request->info,
                        'date'          => $request->date,
                        'status'          => $request->status,
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
            $randomString = Str::random(10); 

         
                    $filePath = 'public/pdf/' . $randomString . '.pdf';
                    

                    Storage::put($filePath, $content);
                    
                    $document_po = asset(Storage::url($filePath));
                    $var_link=$document_po;

            return $document_po;
        }else{
            abort(404);
        }

    }
}
