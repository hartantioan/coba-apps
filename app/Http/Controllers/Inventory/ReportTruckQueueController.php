<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\ExportReportTruckQueue;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DateTime;

use App\Models\User;
use App\Models\ItemGroup;
use App\Models\Place;
use App\Models\TruckQueue;
use App\Models\Warehouse;
use Maatwebsite\Excel\Facades\Excel;

class ReportTruckQueueController extends Controller
{
    protected $dataplaces,$dataplacecode, $datawarehouses;
    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
    }
    public function index(Request $request)
    {
        $itemGroup = ItemGroup::whereHas('childSub',function($query){
            $query->whereHas('itemGroupWarehouse',function($query){
                $query->whereIn('warehouse_id',$this->datawarehouses);
            });
        })->get();
        $data = [
            'title'     => 'Laporan Antrian Truk',
            'group'     =>  $itemGroup,
            'content'   => 'admin.inventory.report_truck_queue',
            'place'     =>  Place::where('status','1')->get(),
            'warehouse' =>  Warehouse::where('status',1)->get()
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filter(Request $request){
        $start_time = microtime(true);
        $query = TruckQueue::where(function($query) use($request) {
            $query->whereDate('date', '>=', $request->start_date)
                ->whereDate('date', '<=', $request->finish_date);
            if($request->status){
                if (in_array(7, $request->status)) {
                    $query->where('status','!=',6);
                }else{
                    $query->whereIn('status', $request->status);
                }
            }
        })->get();
        $arr=[];
        foreach ($query as $key => $row) {
            $date1 = new DateTime($row->time_load_fg);
            $date2 = new DateTime($row->time_done_load_fg);
            $diff = $date1->diff($date2);
            $hours = $diff->h;
            $minutes = $diff->i;
            $diff_time = $hours.' jam '.$minutes.' menit';
            $gs_code="-";
            $gs_time_out="-";
            $sj_code="-";
            $gs_time_out="-";
            $sj_keluar="-";
            if($row->truckQueueDetail->goodScale()->exists()){
                $gs_code = $row->truckQueueDetail->goodScale->code;
                $gs_time_out=$row->truckQueueDetail->goodScale->time_scale_out;
                $sj_code = $row->truckQueueDetail->goodScale->getSalesSuratJalan();
                $gs_time_out=$row->truckQueueDetail->goodScale->time_scale_out;
                $sj_keluar=$row->truckQueueDetail->goodScale->getSuratJalanKeluarPabrik();
            }
            $arr[] = [
                'no'    => ($key+1),
                'status'=> $row->status(),
                'code'              => $row->code,
                'User'          => $row->user->name,
                'Supir' => $row->name,
                'No. pol'=> $row->no_pol,
                'Truk'=>$row->truck,
                'Tipe'=>$row->type(),
                'Kelengkapan Dokumen'=>$row->documentStatus(),
                'Kode Barcode'=>$row->code_barcode,
                'Antri'=>$row->date,
                'No Timbangan'=>$gs_code,
                'Timbang Masuk'=>$row->truckQueueDetail->time_in,
                'Muat FG'=>$row->time_load_fg,
                'Selesai Muat FG'=>$row->time_done_load_fg,
                'Lama Muat'=>$diff_time,
                'Timbang Keluar'=>$gs_time_out,
                'Kode SJ'=>$sj_code,
                'Keluar Pabrik'=>$sj_keluar,
                'Jam Ganti Dokumen'=>$row->change_status,


            ];
        }
        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time);
        $response =[
            'status'=>200,
            'message'  =>$arr,
            'time'  => " Waktu proses : ".$execution_time." detik"
        ];
        return response()->json($response);
    }


    public function export(Request $request){
        $start_date = $request->start_date ? $request->start_date : '';
        $finish_date = $request->finish_date ? $request->finish_date : '';
        $status = $request->status ? $request->status : '';
		return Excel::download(new ExportReportTruckQueue($start_date,$finish_date,$status), 'truck_queue_report'.uniqid().'.xlsx');
    }
}
