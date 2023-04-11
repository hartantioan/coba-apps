<?php

namespace App\Http\Controllers\Personal;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ChatController extends Controller
{

    public function index()
    {
        $data = [
            'title'         => 'Obrolan - Pengguna',
            'content'       => 'admin.personal.chat'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

}