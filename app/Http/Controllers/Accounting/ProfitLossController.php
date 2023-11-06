<?php

namespace App\Http\Controllers\Accounting;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Company;
use App\Models\JournalDetail;
use App\Models\User;
use Illuminate\Http\Request;

class ProfitLossController extends Controller
{
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Laba Rugi (Profit Loss)',
            'content'   => 'admin.accounting.profit_loss',
            'company'   => Company::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }
}