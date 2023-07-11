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

    public function datatable(Request $request){
        $column = [
            'id',
            'from_user_id',
            'title',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Notification::count();
        
        $query_data = Notification::where(function($query) use ($search, $request) {
            $query->where('to_user_id',session('bo_id'));
            if($search) {
                info($search);
                $query->where(function($query) use ($search, $request) {
                    $query->where('title', 'like', "%$search%")
                    ->where('to_user_id',session('bo_id'))
                    ->orWhereHas('fromUser', function ($query) use ($search) {
                        $query->where('name', 'like', "%$search%");
                    });
                });
            }
            if($request->start_date && $request->finish_date) {
                $query->whereDate('created_at', '>=', $request->start_date)
                    ->whereDate('created_at', '<=', $request->finish_date);
            } else if($request->start_date) {
                $query->whereDate('created_at','>=', $request->start_date);
            } else if($request->finish_date) {
                $query->whereDate('created_at','<=', $request->finish_date);
            }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy('created_at', 'desc')
            ->get();

        $total_filtered = Notification::where(function($query) use ($search, $request) {
            $query->where('to_user_id',session('bo_id'));
                if($search) {
                    $query->where('title', 'like', "%$search%")
                        ->where('to_user_id',session('bo_id'))
                        ->orWhereHas('fromUser', function ($query) use ($search) {
                            $query->where('name', 'like', "%$search%");
                        });
                }
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('created_at', '>=', $request->start_date)
                        ->whereDate('created_at', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('created_at','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('created_at','<=', $request->finish_date);
                }
            })
            ->count();
        $rootUrl = url('/');
        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                
                $response['data'][] = [
                    $nomor,
                    $val->fromUser->name,
                    $val->title,
                    '
                        <a type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Kunjungi Halaman" href="'.$rootUrl.'/admin/'.$val->getURL().'?code='.CustomHelper::encrypt($val->lookable->code ?? '').'"><i class="material-icons dp48">keyboard_tab</i></a>
                    '

                ];

                $nomor++;
            }
        }

        $response['recordsTotal'] = 0;
        if($total_data <> FALSE) {
            $response['recordsTotal'] = $total_data;
        }

        $response['recordsFiltered'] = 0;
        if($total_filtered <> FALSE) {
            $response['recordsFiltered'] = $total_filtered;
        }

        return response()->json($response);
    }

    public function refresh(Request $request){

        $notifs = Notification::where('to_user_id',session('bo_id'))->orderByDesc('id')->limit(5)->get()->sortBy('id');
        $notifnew = count($notifs->where('status','1'));

        $user = User::find(session('bo_id'));

        $approvals = ApprovalMatrix::where('user_id',session('bo_id'))->where('status','1')->count();

        $arrnotif = [];
        $arrlink = [];
        $rootUrl = url('/');
        foreach($notifs as $row){
            $row['icon'] = $row->icon();
            $row['from_name'] = $row->from_user_id == session('bo_id') ? 'Anda' : $row->fromUser->name;
            $row['time'] = $row->getTimeAgo();
            $arrnotif[] = $row;
            $arrlink[] = $rootUrl.'/admin/'.$row->getURL().'?code='.CustomHelper::encrypt($row->lookable->code ?? '');
        }

        $response = [
            'status'            => 200,
            'message'           => 'Test success.',
            'notif_list'        => $arrnotif,
            'link_list'         => $arrlink,
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