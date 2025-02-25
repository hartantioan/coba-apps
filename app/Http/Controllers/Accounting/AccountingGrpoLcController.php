<?php

namespace App\Http\Controllers\Accounting;

use App\Exports\ExportSubsidiaryLedger;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Http\Controllers\Controller;
use App\Jobs\GoodReceiptLandedCostJob;
use App\Jobs\SubsidiaryLedgerExportJob;
use App\Models\Coa;
use App\Models\Company;
use App\Models\IncomingPaymentDetail;
use App\Models\JournalDetail;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderDeliveryProcessDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AccountingGrpoLcController extends Controller
{
    public function index(Request $request)
    {

        $data = [
            'title'     => '',
            'content'   => 'admin.accounting.grpo_lc',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function export(Request $request){
        $datestart = $request->datestart ? $request->datestart : date('Y-m-d');
        $dateend = $request->dateend ? $request->dateend : date('Y-m-d');
        $user_id = session('bo_id');
        GoodReceiptLandedCostJob::dispatch( $datestart,$dateend,$user_id);

        return response()->json(['message' => 'Your export is being processed. Anda akan diberi notifikasi apabila report anda telah selesai']);

    }
}
