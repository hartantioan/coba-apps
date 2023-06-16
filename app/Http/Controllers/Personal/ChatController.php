<?php

namespace App\Http\Controllers\Personal;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Chats;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;


class ChatController extends Controller
{

    public function index()
    {
        $userCode = session('bo_id');
        // $chats = Chat::where(function ($query) use ($userCode) {
        //             $query->where('id_user1', $userCode)
        //                 ->orWhere('id_user2', $userCode);
        //         })
        //         ->get();


        $data = [
            'title'         => 'Obrolan - Pengguna',
            'content'       => 'admin.personal.chat',
            'data_user'     => User::find(session('bo_id')),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function new_chat()
    {
        
    }

}