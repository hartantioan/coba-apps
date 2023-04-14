<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Notification;
use App\Models\ApprovalMatrix;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\CustomHelper;

class NotificationController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Notifikasi',
            'content'   => 'admin.personal.notification',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function refresh(Request $request){

        $notifs = Notification::where('to_user_id',session('bo_id'))->orderByDesc('id')->limit(5)->get()->sortBy('id');
        $notifnew = count($notifs->where('status','1'));

        $user = User::find(session('bo_id'));

        $approvals = ApprovalMatrix::where('user_id',session('bo_id'))->where('status','1')->count();

        $arrnotif = [];

        foreach($notifs as $row){
            $row['icon'] = $row->icon();
            $row['from_name'] = $row->from_user_id == session('bo_id') ? 'Anda' : $row->fromUser->name;
            $row['time'] = $row->getTimeAgo();
            $arrnotif[] = $row;
        }

        $response = [
            'status'            => 200,
            'message'           => 'Test success.',
            'notif_list'        => $arrnotif,
            'notif_count'       => $notifnew,
            'approval_count'    => $approvals,
            'need_change_pass'  => $user->needChangePassword() ? '1' : ''
        ];
        
        return response()->json($response);
    }

    public function updateNotification(Request $request){
        Notification::where('to_user_id',session('bo_id'))->update([
            'status' => '2'
        ]);
    }
}