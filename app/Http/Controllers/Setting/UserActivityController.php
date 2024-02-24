<?php

namespace App\Http\Controllers\Setting;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Models\ApprovalStage;
use App\Models\Approval;

class UserActivityController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Aktivitas User',
            'content'   => 'admin.setting.user_activity',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'causer_id',
            'description',
            'subject_type',
            'properties',
            'updated_at',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ActivityLog::count();
        
        $query_data = ActivityLog::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search) {
                        $query->where('description', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = ActivityLog::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search) {
                        $query->where('description', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    $nomor,
                    $val->user()->exists() ? $val->user->employee_no.' - '.$val->user->name : 'System',
                    $val->description,
                    $val->subject_type,
                    $val->properties,
                    date('d/m/Y,',strtotime($val->updated_at)),
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
}