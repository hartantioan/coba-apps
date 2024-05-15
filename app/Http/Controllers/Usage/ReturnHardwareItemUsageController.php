<?php

namespace App\Http\Controllers\Usage;

use App\Http\Controllers\Controller;
use App\Models\HardwareItem;
use App\Models\ReceptionHardwareItemsUsage;
use App\Models\ReturnHardwareItemsUsage;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Helpers\PrintHelper;
class ReturnHardwareItemUsageController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Pengembalian Hardware Item',
            'content' => 'admin.usage.return_hardware_items_usages'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'code',
            'reception_hardware_item_usage_id',
            'date',
            'info',
            'status',
        ];

        
        
        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ReturnHardwareItemsUsage::count();
        
        $query_data = ReturnHardwareItemsUsage::where(function($query) use ($search, $request) {
                
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhere('code', 'like', "%$search%")
                            ->orWhere('date', 'like', "%$search%")
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

        

        $total_filtered = ReturnHardwareItemsUsage::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhere('code', 'like', "%$search%")
                            ->orWhere('date', 'like', "%$search%")
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
                    $val->receptionHardwareItem->hardwareItem->item->name,
                    $val->date,
                    $val->info,
                    $val->status(),
                    '
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light brown darken-2 white-text btn-small" data-popup="tooltip" title="Return" onclick="printData(' . $val->id . ')"><i class="material-icons dp48">filter_frames</i></button>
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

    public function print( Request $request){

        $RH = ReturnHardwareItemsUsage::find($request->id);
        $user_r = User::find(session('bo_id'));
        if($RH){
            $data = [
                'data' => $RH,
                'user' => $RH->receptionHardwareItem->user,
                'pic'  => $user_r
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

    public function store_w_barcode(Request $request){
        $barcode = $request->input('barcode');       

        DB::beginTransaction();
        try{
            $query_code = HardwareItem::where('code',$barcode)->first();
            $query_hardware_item_id = $query_code->id;
            $lastInsertedData = ReceptionHardwareItemsUsage::where('hardware_item_id', $query_hardware_item_id)
                            ->latest()
                            ->first();
        
            if($lastInsertedData->status == '4' ){
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
                $query = ReturnHardwareItemsUsage::create([
                    'code' => ReturnHardwareItemsUsage::generateCode(),
                    'reception_hardware_item_usage_id' => $lastInsertedData->id,
                    'date' => date('Y-m-d H:i:s'),
                    'info' => 'Kembali Ke Gudang',
                    'status' => 1,
                ]);

                if($query){
                    $lastInsertedData->update([
                        'status'    => '4',
                    ]);

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

    public function destroy(Request $request){
        $query = ReturnHardwareItemsUsage::find($request->id);
        
        if($query->delete()) {
           
            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);
           
            activity()
            ->performedOn(new ReturnHardwareItemsUsage())
            ->causedBy(session('bo_id'))
            ->withProperties($query)
            ->log('Delete the Return Hardware Items Usage data');
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
}
