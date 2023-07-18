<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Http\Request;

class AccountingReportController extends Controller
{
    protected $dataplaces, $lasturl, $mindate, $maxdate;
    public function __construct(){
        $user = User::find(session('bo_id'));
    }
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Laporan Rekapitulasi Akunting',
            'content'   => 'admin.accounting.report',
            'menus'     => Menu::whereIn('parent_id', [17, 87])
                            ->whereHas('menuUser', function ($query) {
                                $query->where('user_id', session('bo_id'))
                                    ->where('type','view');
                            })
                            ->get()
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }
}
