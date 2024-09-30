<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportOutstandingAP;
use App\Exports\ExportOutstandingMOD;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;


class MarketingOrderOutstandingMODController extends Controller
{
    public function __construct(){
        $user = User::find(session('bo_id'));
    }
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Laporan Outstanding MOD',
            'content'   => 'admin.sales.outstanding_mod',
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

   

    public function export(Request $request){
		return Excel::download(new ExportOutstandingMOD(), 'outstanding_mod_'.uniqid().'.xlsx');
    }
}
