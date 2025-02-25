<?php

namespace App\Http\Controllers\Purchase;

use App\Exports\ExportReportTestResult;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Company;
use App\Models\Item;
use App\Models\ItemShading;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportTestResultController extends Controller
{
    protected $dataplaces, $dataplacecode, $datawarehouses;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];

    }
    public function index(Request $request)
    {
        $parentSegment = request()->segment(2);

        $data = [
            'title'     => 'Report Hasil Uji',
            'content'   => 'admin.purchase.report_test_result',
            'shading'      => ItemShading::get(),
            'item'      => Item::where('status','1')
            ->whereHas('itemGroup',function($query){
                $query->whereHas('itemGroupWarehouse',function($query){
                    $query->whereIn('warehouse_id',['2','3']);
                });
            })
            ->get(),
            'company'       => Company::where('status','1')->get(),
            'area'       => Area::where('status','1')->get(),
            'place'       => Place::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function export(Request $request){
        $start_date = $request->start_date ? $request->start_date : '';
        $finish_date = $request->end_date ? $request->end_date : '';

		return Excel::download(new ExportReportTestResult($start_date,$finish_date), 'report_hasil_uji_'.uniqid().'.xlsx');
    }

}
