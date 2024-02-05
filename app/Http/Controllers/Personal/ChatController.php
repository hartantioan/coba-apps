<?php

namespace App\Http\Controllers\Personal;

use App\Helpers\CustomHelper;
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
        
        foreach($data as $row){
            $lm = $row->chat()->orderByDesc('id')->first();
            $lastMessage = $lm ? $lm->chat_message : '';
            $lastTime = $lm ? $lm->getTimeAgo() : '';
            $realLastTime = $lm ? $lm->updated_at : '';
            if($row->from_user_id == session('bo_id')){
                $listAllRoom[] = [
                    'code'          => CustomHelper::encrypt($row->code),
                    'name'          => $row->toUser->name,
                    'photo'         => $row->toUser->photo(),
                    'last_message'  => $lastMessage ? $lastMessage : '',
                    'last_time'     => $lastTime ? $lastTime : '',
                    'real_last_time'=> $realLastTime,
                ];
            }

            if($row->to_user_id == session('bo_id')){
                $listAllRoom[] = [
                    'code'          => CustomHelper::encrypt($row->code),
                    'name'          => $row->fromUser->name,
                    'photo'         => $row->fromUser->photo(),
                    'last_message'  => $lastMessage ? $lastMessage : '',
                    'last_time'     => $lastTime ? $lastTime : '',
                    'real_last_time'=> $realLastTime,
                ];
            }
        }

        $collect = collect($listAllRoom)->sortByDesc('real_last_time');

        $response = [
            'status'        => 200,
            'message'       => 'Data successfully loaded.',
            'data'          => $collect,
        ];
        return response()->json($response);
    }

    public function getMessage(Request $request)
    {
        $data = Chat::whereHas('chatRequest',function($query)use($request){
            $query->where('code',CustomHelper::decrypt($request->code));
        })->get();

        $listChat = [];

        foreach($data as $row){
            $listChat[] = [
                'photo'     => $row->fromUser->photo(),
                'message'   => $row->chat_message,
                'status'    => $row->message_status,
                'time'      => $row->updated_at,
                'is_me'     => $row->from_user_id == session('bo_id') ? '1' : '',
            ];
        }

        $response = [
            'status'        => 200,
            'message'       => 'Data successfully loaded.',
            'data'          => $listChat,
        ];
        return response()->json($response);
    }
}