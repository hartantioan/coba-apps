<?php

namespace App\Exports;

use App\Models\AttendanceMonthlyReport;
use App\Models\AttendancePeriod;
use App\Models\AttendancePunishment;
use App\Models\Punishment;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExportMonthlyReport implements FromView,ShouldAutoSize,WithTitle
{
    public function __construct(string $period_id)
    {
        $this->period_id = $period_id ? $period_id : '';

    }

    public function title(): string
    {
        return 'Monthly Report'; // Set the custom name for the first sheet
    }

    public function view(): View
    {
        $query_data_monthly = AttendanceMonthlyReport::where(function($query)  {
            if($this->period_id) {
                $query->where('period_id', $this->period_id);
            }
        })
        ->orderBy('id','ASC')
        ->get();
        $query_period = AttendancePeriod::find($this->period_id);
        $query_punish = Punishment::where('place_id',$query_period->plant_id)
                        ->where('type','1')
                        ->get();
        $array=[];
        foreach($query_data_monthly as $row_data){
            $entry = [];
            $entry["user_id"]=$row_data->user->employee_no;
            $entry["username"] = $row_data->user->name;
            $entry["effective_day"] = $row_data->effective_day;
            foreach($query_punish as $row_punish){
                $entry[$row_punish->code]=0;
               
            }
            $entry["late"] = $row_data->late;
            $entry["leave_early"] = $row_data->leave_early;
            $entry["absent"] = $row_data->absent;
            $entry["special_occasion"] = $row_data->special_occasion;
            $entry["sick"] = $row_data->sick;
            $entry["permit"] = $row_data->permit;
            $entry["outstation"] = $row_data->outstation;
            $entry["furlough"] = $row_data->furlough;
            $entry["dispen"] = $row_data->dispen;
            $entry["alpha"] = $row_data->alpha;
            $entry["wfh"] = $row_data->wfh;
            $entry["arrived_on_time"] = $row_data->arrived_on_time;
            $entry["out_on_time"] = $row_data->out_on_time;
            $entry["out_log_forget"] = $row_data->out_log_forget;
            $entry["arrived_forget"] = $row_data->arrived_forget;
            
            $array[] = $entry;
        }
        
        $query_attendance_punish = AttendancePunishment::where('period_id',$this->period_id)
                                    ->get();
        foreach($query_attendance_punish as $row_punish){
            foreach($array as $key_array=>$row_array){
                if($row_array["user_id"]==$row_punish->user_id){
                    
                    if(array_key_exists($row_punish->punishment->code,$row_array)){
                        
                        $array[$key_array][$row_punish->punishment->code]=$row_punish->frequent;
                        
                    }
                }
            }
        }
        return view('admin.exports.attendance_monthly_report', [
            'data' => $array,
            'punish'=> $query_punish,
        ]);
    }
}
