<?php

namespace App\Http\Controllers\Setting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Place;
use App\Models\Warehouse;
use Maatwebsite\Excel\Facades\Excel;

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
