<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;

class FinanceReportController extends Controller
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
            'title'     => 'Finance Report',
            'content'   => 'admin.finance.report',
            'menus'     => Menu::where('parent_id',$menu->id)
                            ->whereHas('menuUser', function ($query) {
                                $query->where('user_id', session('bo_id'))
                                    ->where('type','view');
                            })
                            ->orderBy('order')->get()
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }
}
