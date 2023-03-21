<?php

namespace App\Http\Controllers\Dashboard;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'title'   => 'Dashboard',
            'content' => 'admin.dashboard.main'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }
}
