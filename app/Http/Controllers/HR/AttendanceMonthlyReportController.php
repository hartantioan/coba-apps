<?php

namespace App\Http\Controllers\HR;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\AttendanceMonthlyReport;
use App\Models\AttendancePeriod;
use App\Models\AttendancePunishment;
use App\Models\Punishment;
use App\Models\User;
use Illuminate\Http\Request;

class AttendanceMonthlyReportController extends Controller
{
    public function index(Request $request)
    {
        $code =  $request->code ? CustomHelper::decrypt($request->code) : '';
        
        $data = [
            'title'         => 'Rekap Periode',
            'user'          =>  User::where('type','1')->where('status',1)->get(),
            'content'       => 'admin.hr.attendance_monthly_report',
            'code'          => $code
        ];
        if($code){
            $attendance_period = AttendancePeriod::find($code);
            if($attendance_period->getPunishment()){
                $data['period_id'] = $attendance_period->id;
                $data['punishment_name'] = $attendance_period->code .' - ' . $attendance_period->name;
                $data['punishment_code'] = $attendance_period->getPunishment();   
            }
        }

        return view('admin.layouts.index', ['data' => $data]); 
    }
    public function datatable(Request $request){
        if($request->temp){
           
            $query_period = AttendancePeriod::find($request->temp);
        }else{
            $query_period = AttendancePeriod::find($request->period_id);
        }
        
        $query_punish = Punishment::where('place_id',$query_period->plant_id)
                        ->where('type','1')
                        ->get();
        
        $column = [
            'user_id',
            'period_id',
            'effective_day',
        ];
        $array_nama = [];
        foreach ($query_punish as $row_punish) {
            $array_nama[] = $row_punish->code;
        }
        
        $additionalColumns = [
            'absent',          // masuk
            'special_occasion',
            'sick',
            'outstation',      // dinas keluar
            'furlough',        // cuti
            'dispen',
            'alpha',           // tidak masuk
            'wfh',
            'late',
            'leave_early',
            'arrived_on_time',
            'out_on_time',
            'out_log_forget',
            'arrived_forget',
        ];
        $combinedArray = array_merge($column, $array_nama, $additionalColumns);
        $start  = $request->start;
        $length = $request->length;
        $order  = $combinedArray[$request->input('order.0.column')];
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
        $array=[];
        foreach($query_data as $row_data){
            $entry = [];
            $entry["user_id"]=$row_data->user_id;
            $entry["username"] = $row_data->user->name;
            $entry["effective_day"] = $row_data->effective_day;
            foreach($array_nama as $row_punish){
                $entry[$row_punish]=0;
            }
            $entry["absent"] = $row_data->absent;
            $entry["special_occasion"] = $row_data->special_occasion;
            $entry["sick"] = $row_data->sick;
            
            $entry["outstation"] = $row_data->outstation;
            $entry["furlough"] = $row_data->furlough;
            $entry["dispen"] = $row_data->dispen;
            $entry["alpha"] = $row_data->alpha;
            $entry["wfh"] = $row_data->wfh;
            $entry["late"] = $row_data->late;
            $entry["leave_early"] = $row_data->leave_early;
            $entry["arrived_on_time"] = $row_data->arrived_on_time;
            $entry["out_on_time"] = $row_data->out_on_time;
            $entry["out_log_forget"] = $row_data->out_log_forget;
            $entry["arrived_forget"] = $row_data->arrived_forget;
            
            $array[] = $entry;
        }
        
        $query_attendance_punish = AttendancePunishment::where('period_id',$request->period_id)
                                ->orWhere('period_id',$request->temp)->get();
        
        foreach($query_attendance_punish as $row_punish){
           foreach($array as $key_array=>$row_array){
                if($row_array["user_id"]==$row_punish->employee_id){
                    if(array_key_exists($row_punish->punishment->code,$row_array)){
                        
                        $array[$key_array][$row_punish->punishment->code]=$row_punish->frequent;
                        
                    }
                }
           }
        }

        
        $response['data'] = $array;
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($array as $key=>$val_array) {
                $response['data'][$key] = [
                    $nomor
                ];
				foreach($val_array as $key_b=>$row_val){
                    if($key_b!="user_id"){
                        
                        $response['data'][$key][] = [
                            $row_val
                        ];
                    }
                    
                }
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
           
        }
    }

    public function takePlant(Request $request){
        if($request->id){
            $query = AttendancePeriod::where('id',$request->id)->first();
            $query_punish = Punishment::where('place_id',$query->plant_id)
                        ->where('type','1')
                        ->get();
            $string_tambahan="";
            foreach($query_punish as $row_punish){
                $string_tambahan.="
                    <th>".$row_punish->code."</th>
                ";
            }
            $string_th="<tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Jumlah Shift</th>
                            ".$string_tambahan."
                            <th>Tepat waktu</th>
                            <th>Ijin Kusus</th>
                            <th>Sakit</th>
                            <th>Dinas Keluar</th>
                            <th>Cuti</th>
                            <th>Dispen</th>
                            <th>Alpha</th>
                            <th>WFH</th>
                            <th>Datang Tepat Waktu</th>
                            <th>Pulang Tepat Waktu</th>
                            <th>Lupa Check Clock Pulang</th>
                            <th>Lupa Check Clock Datang</th>
                        </tr>";
            
            $response =[
                'status'=>200,
                'message'  =>$string_th,
            ];
        }
       else{
            $response =[
                'status'  =>500,
                'message' =>'ada yang error'
            ];
       }
       
       return response()->json($response);
    }
}
