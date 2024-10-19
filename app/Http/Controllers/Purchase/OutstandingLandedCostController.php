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
            'title'     => 'Tunggakan PO',
            'content'   => 'admin.purchase.outstanding_lc',
        ];
        
        return view('admin.layouts.index', ['data' => $data]);

    }

    public function exportOutstandingPO(Request $request){
        $date = $request->date? $request->date : '';
		return Excel::download(new ExportOutstandingLandedCost($date), 'outstanding_lc'.uniqid().'.xlsx');
    }
}
