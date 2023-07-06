<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;

class InventoryReportController extends Controller
{
    public function __construct(){
        $user = User::find(session('bo_id'));
    }
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Inventory Report',
            'content'   => 'admin.inventory.report',
            'menus'     =>  Menu::where('parent_id','14')
                                ->whereHas('menuUser', function ($query) {
                                    $query->where('user_id', session('bo_id'))
                                        ->where('type','view');
                                })
                                ->get()
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }
}
