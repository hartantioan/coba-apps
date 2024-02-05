<?php

namespace App\Http\Controllers\Personal;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatRequest;
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

        $data = [
            'title'         => 'Obrolan - Pengguna',
            'content'       => 'admin.personal.chat',
            'data_user'     => User::find(session('bo_id')),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function sync()
    {
        $data = ChatRequest::where('status','Approved')
                        ->where(function($query){
                            $query->where('from_user_id',session('bo_id'))->orWhere('to_user_id',session('bo_id'));
                        })->get();

        $listAllRoom = [];
        
        /* foreach($data as $) */

        $response = [
            'status'    => 200,
            'message'   => 'Data successfully saved.',
        ];
        return response()->json($response);
    }

}