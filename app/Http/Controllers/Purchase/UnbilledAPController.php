<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;

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
}
