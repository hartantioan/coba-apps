<?php

namespace App\Exports;

use App\Models\AttendanceDailyReports;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;
class ExportDailyReport implements FromView,ShouldAutoSize,WithTitle
{
   
    public function __construct(string $period_id)
    {
        $this->period_id = $period_id ? $period_id : '';

    }

    public function title(): string
    {
        return 'Daily report'; // Set the custom name for the first sheet
    }
    public function view(): View
    {
        
        $query_data_daily = AttendanceDailyReports::where(function($query)  {
            if($this->period_id) {
                $query->where('period_id', $this->period_id)
                ->where('id',1);
            }
        })
        ->orderBy('id','ASC')
        ->get();
        
        $distinctDates = $query_data_daily->pluck('date')->unique()->toArray();
        $attendanceDetail=[];
        foreach($distinctDates as $key_date=>$row_dates){
            foreach($query_data_daily as $key_daily=>$row_daily){
                if($row_daily['date']==$row_dates){
                    if($row_daily->shift != null){
                        $min_time_in = Carbon::parse($row_daily->shift->time_in)->subHours($row_daily->shift->tolerant)->toTimeString();
                        $max_time_out = Carbon::parse($row_daily->shift->time_out)->addHours($row_daily->shift->tolerant)->toTimeString();
                    }else{
                        $min_time_in = "tidak ada shift";
                        $max_time_out = "tidak ada shift";
                    }
                    
                    $attendanceDetail[$key_date][]=[
                        'user_id'=>$row_daily->user->employee_no??'',
                        'user_name'=>$row_daily->user->name??'',
                        'nama_shift'=>$row_daily->shift->name ?? 'tidak ada shift',
                        'min_masuk'=>$min_time_in,
                        'max_keluar'=>$max_time_out,
                        'limit_masuk'=>$row_daily->shift->time_in ?? 'tidak ada shift',
                        'limit_keluar'=>$row_daily->shift->time_out ?? 'tidak ada shift',
                        'masuk'=>$row_daily->masuk,
                        'pulang'=>$row_daily->pulang,
                        'date'=>$row_daily->date,
                        'status'=>$row_daily->rawStatus(),
                    ];
                }
            }
            
        }

      
        return view('admin.exports.attendance_daily_report', [
            'data' => $attendanceDetail,
        ]);
    }
}
