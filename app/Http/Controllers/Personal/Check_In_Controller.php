<?php

namespace App\Http\Controllers\Personal;

use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Http\Controllers\Controller;
use App\Models\Attendances;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
class Check_In_Controller extends Controller
{
    public function index()
    {
        $userCode = session('bo_id');

        $data = [
            'title'         => 'Absensi - Personal',
            'content'       => 'admin.personal.check_in',
            'data_user'     => User::find(session('bo_id')),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

       
}
