<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\ExportReportGoodScalePO;
use App\Models\User;
use App\Models\ItemGroup;
use App\Models\Place;
use App\Models\Warehouse;
use Maatwebsite\Excel\Facades\Excel;

class ReportGoodScalePOController extends Controller
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
            'title'     => 'Laporan Timbangan X PO Ekspedisi',
            'group'     =>  $itemGroup,
            'content'   => 'admin.accounting.report_good_scale_po',
            'place'     =>  Place::where('status','1')->get(),
            'warehouse' =>  Warehouse::where('status',1)->get()
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }


    public function export(Request $request){
        $start_date = $request->start_date ? $request->start_date : '';
        $finish_date = $request->finish_date ? $request->finish_date : '';
        $status = $request->status ? $request->status : '';
        $type = $request->type ? $request->type : '';
        $status_qc = $request->status_qc ? $request->status_qc : '';

		return Excel::download(new ExportReportGoodScalePO($start_date,$finish_date,$status,$type,$status_qc), 'good_scale_po_'.uniqid().'.xlsx');
    }
}
