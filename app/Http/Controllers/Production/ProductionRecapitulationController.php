<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;

class ProductionRecapitulationController extends Controller
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
            'title'     => 'Laporan Produksi',
            'content'   => 'admin.production.recapitulation',
            'menus'     => Menu::where('parent_id',$menu->id)
                            ->whereHas('menuUser', function ($query) {
                                $query->where('user_id', session('bo_id'))
                                    ->where('type','report');
                            })
                            ->orderBy('order')->get()
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }
}
