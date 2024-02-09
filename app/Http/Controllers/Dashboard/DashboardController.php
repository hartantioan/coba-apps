<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\ItemStock;
use App\Http\Controllers\Controller;
use App\Models\User;

class DashboardController extends Controller
{
    protected $user;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->user = $user;
    }
    public function index()
    {
        $data = [
            'title'         => 'Dashboard',
            'content'       => 'admin.dashboard.main',
            /* 'itemcogs'      => ItemCogs::orderByDesc('date')->orderByDesc('id')->get(), */
            'itemstocks'    => ItemStock::where('qty','>',0)->get(),
            'user'          => $this->user,
        ];
    
        return view('admin.layouts.index', ['data' => $data]);
    }
}
