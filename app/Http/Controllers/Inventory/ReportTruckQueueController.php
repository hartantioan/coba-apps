<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\ExportReportTruckQueue;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\ItemGroup;
use App\Models\Place;
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


    public function export(Request $request){
        $start_date = $request->start_date ? $request->start_date : '';
        $finish_date = $request->finish_date ? $request->finish_date : '';

		return Excel::download(new ExportReportTruckQueue($start_date,$finish_date), 'truck_queue_report'.uniqid().'.xlsx');
    }
}
