<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Jobs\reportProductionResultJob;
use App\Models\Area;
use App\Models\Company;
use App\Models\ItemShading;
use App\Models\ItemStock;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\Request;

use App\Exports\ExportReportProductionResult;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReportProductionResultController extends Controller
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
            'title'     => 'Report Hasil Produksi (ST)',
            'content'   => 'admin.production.report_production_result',
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }
    public function export(Request $request){
        $user_id = session('bo_id');
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $startdate = Carbon::parse($start_date);
        $enddate = Carbon::parse($end_date);

        // Check if the difference between the two dates is more than 30 days
        if ($startdate->diffInDays($enddate) > 30) {
            return response()->json([
                'message' => 'The date range cannot be more than 30 days.'
            ]);
        }

        reportProductionResultJob::dispatch($start_date, $end_date,$user_id);

		return response()->json(['message' => 'Your export is being processed. Anda akan diberi notifikasi apabila report anda telah selesai']);
    }
}
