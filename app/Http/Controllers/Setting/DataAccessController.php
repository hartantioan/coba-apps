<?php

namespace App\Http\Controllers\Setting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportDataAccess;

class DataAccessController extends Controller
{
    public function index()
    {
        $data = [ 
            'title'     => 'Akses Data Pegawai',
            'user'      => User::where('status','1')->where('type','1')->get(),
            'content'   => 'admin.setting.data_access'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function export(Request $request){
        $employees = $request->employee ? $request->employee : '';

        return Excel::download(new ExportDataAccess($employees), 'data_access_'.uniqid().'.xlsx');
    }
}
