<?php

namespace App\Http\Controllers\Accounting;

use App\Exports\ExportReportTransactionCogs;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Company;
use App\Models\ItemShading;
use App\Models\ItemStock;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;

class ReportTransaction_CogsController extends Controller
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
            'title'     => 'Report Item Cogs X Transaction ',
            'content'   => 'admin.accounting.report_transaction_cogs',
            'shading'      => ItemShading::get(),
            'company'       => Company::where('status','1')->get(),
            'area'       => Area::where('status','1')->get(),
            'place'       => Place::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }
    public function export(Request $request){
        $start_date = $request->start_date;
        $finish_date = $request->end_date;
		return Excel::download(new ExportReportTransactionCogs($start_date,$finish_date), 'transaction_x_cogs'.uniqid().'.xlsx');
    }
}
