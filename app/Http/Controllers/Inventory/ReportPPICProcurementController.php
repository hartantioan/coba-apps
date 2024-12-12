<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\ExportReportPPICProcurement;
use App\Helpers\CustomHelper;
use App\Exports\ExportReportProcurement;
use App\Helpers\PrintHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Area;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Company;
use App\Models\GoodScale;
use App\Models\ItemShading;
use App\Models\ItemStock;
use App\Models\Place;
use App\Models\RuleBpScale;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Date;
class ReportPPICProcurementController extends Controller
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
            'title'     => 'Report Summary Stock Accounting',
            'content'   => 'admin.inventory.report_ppic_procurement',
            'shading'      => ItemShading::get(),
            'company'       => Company::where('status','1')->get(),
            'area'       => Area::where('status','1')->get(),
            'place'       => Place::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function export(Request $request){
        $start_date = $request->start_date ? $request->start_date : '';
        $finish_date = $request->end_date ? $request->end_date : '';
        $item_id = $request->item_id ? $request->item_id : '';

		return Excel::download(new ExportReportPPICProcurement($start_date,$finish_date,$item_id), 'report_procurement_'.uniqid().'.xlsx');
    }
}
