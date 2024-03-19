<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class UnbilledAPController extends Controller
{
    protected $dataplaces, $lasturl, $mindate, $maxdate;
    public function __construct(){
        $user = User::find(session('bo_id'));
    }
    public function index(Request $request)
    {
        $parentSegment = request()->segment(2);
        $menu = Menu::where('url', $parentSegment)->first();
        $data = [
            'title'     => 'Laporan Hutang Belum Ditagihkan',
            'content'   => 'admin.purchase.unbilled_ap',
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filterByDate(Request $request){
        $array_filter = [];
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $results = DB::select("
            SELECT 
                *
                FROM good_receipts gr
                LEFT JOIN users u
                    ON u.id = gr.account_id
                WHERE 
                    gr.post_date <= :dateend
                    AND gr.post_date >= :datestart
                    AND gr.status IN ('2','3')
                    AND gr.deleted_at IS NULL
        ", array(
            'datestart' => $start_date,
            'dateend'   => $end_date,
        ));
    }
}
