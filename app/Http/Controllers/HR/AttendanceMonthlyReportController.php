<?php

namespace App\Http\Controllers\HR;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\AttendanceMonthlyReport;
use App\Models\User;
use Illuminate\Http\Request;

class AttendanceMonthlyReportController extends Controller
{
    public function index(Request $request)
    {
        $data = [
            'title'         => 'Rekap Periode',
            'user'          =>  User::join('departments','departments.id','=','users.department_id')->select('departments.name as department_name','users.*')->orderBy('department_name')->get(),
            'content'       => 'admin.hr.attendance_monthly_report',
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : ''
        ];

        return view('admin.layouts.index', ['data' => $data]); 
    }
    public function datatable(Request $request){
        $column = [
            'user_id',
            'period_id',
            'effective_day',
            't1',
            't2',
            't3',
            't4',
            'absent',//masuk
            'special_occasion',
            'sick',
            'outstation',//dinas keluar
            'furlough',//cuti
            'dispen',
            'alpha',//tidak masuk
            'wfh',
            'arrived_on_time',
            'out_on_time',
            'out_log_forget',
            'arrived_forget',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = AttendanceMonthlyReport::count();
        
        $query_data = AttendanceMonthlyReport::where(function($query) use ($search, $request) {
                if($search) {
                    $query->orWhereHas('user',function($query) use($search){
                        $query->where('name','like',"%$search%");
                    });
                }

                if($request->period_id){
                    $query->where('period_id', $request->period_id);
                }
                
                if($request->temp){
                    $query->where('period_id', $request->temp);
                }

                if($request->user_id){
                    $query->whereIn('user_id', $request->user_id);
                }

            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = AttendanceMonthlyReport::where(function($query) use ($search, $request) {
            if($search) {
                $query->orWhereHas('user',function($query) use($search){
                    $query->where('name','like',"%$search%");
                });
            }

            if($request->period_id){
                $query->where('period_id', $request->period_id);
            }

            if($request->temp){
                $query->where('period_id', $request->temp);
            }

            if($request->user_id){
                $query->whereIn('user_id', $request->user_id);
            }

        })
        ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    $nomor,
                    $val->user->name,
                    $val->effective_day,
                    $val->t1,
                    $val->t2,
                    $val->t3,
                    $val->t4,
                    $val->absent,
                    $val->special_occasion,
                    $val->sick,
                    $val->outstation,
                    $val->furlough,
                    $val->dispen,
                    $val->alpha,
                    $val->wfh,
                    $val->arrived_on_time,
                    $val->out_on_time,
                    $val->out_log_forget,
                    $val->arrived_forget,
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
    public function showPeriod(Request $request){
        if($request->id){
            $query = AttendanceMonthlyReport::where('period_id',$request->id)->get();
            info($query);
        }
    }
}
