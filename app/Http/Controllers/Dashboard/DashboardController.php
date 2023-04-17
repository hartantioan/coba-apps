<?php

namespace App\Http\Controllers\Dashboard;
use App\Models\ItemCogs;
use App\Models\PurchaseRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Dashboard',
            'content'   => 'admin.dashboard.main',
            'itemcogs'  => ItemCogs::all(),
            'pr'        => PurchaseRequest::all(),
            'pr1'       => PurchaseRequest::find(3),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }
}
