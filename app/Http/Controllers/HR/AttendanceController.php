<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $data = [
            'title'         => '',
            
            'content'       => 'admin.hr.attendance'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }
}
