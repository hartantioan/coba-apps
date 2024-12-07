<?php

namespace App\Http\Controllers\Purchase;

use App\Exports\ExportOutstandingLandedCost;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\PurchaseOrderDetail;
use Maatwebsite\Excel\Facades\Excel;

class OutstandingLandedCostController extends Controller
{
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Outstanding LC',
            'content'   => 'admin.purchase.outstanding_lc',
        ];
        
        return view('admin.layouts.index', ['data' => $data]);

    }

    public function exportOutstandingLC(Request $request){
        $date = $request->date? $request->date : '';
        $type = 'all';
		return Excel::download(new ExportOutstandingLandedCost($date,$type), 'outstanding_lc'.uniqid().'.xlsx');
    }

    public function exportOutstandingLCLocal(Request $request){
        $date = $request->date? $request->date : '';
        $type = '1';
		return Excel::download(new ExportOutstandingLandedCost($date,$type), 'outstanding_lc_local'.uniqid().'.xlsx');
    }

    public function exportOutstandingLCImport(Request $request){
        $date = $request->date? $request->date : '';
        $type = '2';
		return Excel::download(new ExportOutstandingLandedCost($date,$type), 'outstanding_lc_import'.uniqid().'.xlsx');
    }
}
