<?php

namespace App\Exports;

use App\Models\AttendanceDailyReports;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\User;
use Carbon\Carbon;
class ExportDailyReport implements FromView,ShouldAutoSize,WithTitle
{
    protected $period_id;
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
        
        // $query_data_daily = AttendanceDailyReports::where(function($query)  {
        //     if($this->period_id) {
        //         $query->where('period_id', $this->period_id);
        //     }
        // })
        // ->orderBy('id','ASC')
        // ->get();

        $query_data_daily = AttendanceDailyReports::where(function($query)  {
            if($this->period_id) {
                $query->where('period_id', $this->period_id);
            }
        })
        ->orderBy('user_id','ASC')
        ->orderByRaw("STR_TO_DATE(date, '%d/%m/%Y') ASC")
        ->get();
        
        $distinctDates = $query_data_daily->pluck('date')->unique()->toArray();
        $distinctUserIds = $query_data_daily->pluck('user_id')->unique()->toArray();
        $distinctUsers = User::whereIn('id', $distinctUserIds)->get();
        $attendanceDetail=[];
        $shift_per_date = [];
        
        $attendance_user = [];
        foreach($query_data_daily as $row_daily){
            $attendanceDetail[$row_daily->user->employee_no][]=[
                'user_id'=>$row_daily->user->employee_no??'',
                'user_name'=>$row_daily->user->name??'',
                'nama_shift'=>$row_daily->shift->name ?? 'tidak ada shift',
                'limit_masuk'=>$row_daily->shift->time_in ?? 'tidak ada shift',
                'limit_keluar'=>$row_daily->shift->time_out ?? 'tidak ada shift',
                'masuk'=>$row_daily->masuk,
                'pulang'=>$row_daily->pulang,
                'date'=>$row_daily->date,
                'status'=>$row_daily->rawStatus(),
            ];
        }
        // foreach($distinctDates as $key_date=>$row_dates){
        //     $shifts_for_date = [];
        //     $nama_shift = [];
        //     foreach($query_data_daily as $key_daily=>$row_daily){
        //         if($row_daily['date']==$row_dates){
        //             if($row_daily->shift != null){
        //                 $min_time_in = Carbon::parse($row_daily->shift->time_in)->subHours($row_daily->shift->tolerant)->toTimeString();
        //                 $max_time_out = Carbon::parse($row_daily->shift->time_out)->addHours($row_daily->shift->tolerant)->toTimeString();
        //             }else{
        //                 $min_time_in = "tidak ada shift";
        //                 $max_time_out = "tidak ada shift";
        //             }
        //             if($row_daily->shift != null){
        //                 if(!in_array($row_daily->shift->name, $nama_shift)){
        //                     $nama_shift[]= $row_daily->shift->name;
        //                     $shifts_for_date[]=[
        //                         'nama'=> $row_daily->shift->name,
        //                         'limit_masuk'=>$row_daily->shift->time_in ?? 'tidak ada shift',
        //                         'limit_keluar'=>$row_daily->shift->time_out ?? 'tidak ada shift',
        //                     ]; 
        //                 }
        //             }
        //             $attendanceDetail[]=[
        //                 'user_id'=>$row_daily->user->employee_no??'',
        //                 'user_name'=>$row_daily->user->name??'',
        //                 'nama_shift'=>$row_daily->shift->name ?? 'tidak ada shift',
        //                 'min_masuk'=>$min_time_in,
        //                 'max_keluar'=>$max_time_out,
        //                 'limit_masuk'=>$row_daily->shift->time_in ?? 'tidak ada shift',
        //                 'limit_keluar'=>$row_daily->shift->time_out ?? 'tidak ada shift',
        //                 'masuk'=>$row_daily->masuk,
        //                 'pulang'=>$row_daily->pulang,
        //                 'date'=>$row_daily->date,
        //                 'status'=>$row_daily->rawStatus(),
        //             ];
                    
        //         }
        //     }
        //     $shift_per_date[]=[
        //         'date' =>   $row_dates,
        //         'shift'=>   $shifts_for_date,
        //     ];
            
            
        // }
        // foreach($shift_per_date as $row_shift){
                
        //     foreach($query_data_daily as $key_daily=>$row_daily){
               
        //         if($row_daily['date']==$row_shift['date']){
        //             if(count($row_shift['shift']) > 0){
        //                 foreach($row_shift['shift'] as $row_shift_date){
        //                     if($row_daily->shift()->exists()){
        //                         if($row_daily->shift->name == $row_shift_date['nama']){
        //                             $attendance_user[$row_daily->user->id][]=[
        //                                 'user_id'=>$row_daily->user->employee_no??'',
        //                                 'user_name'=>$row_daily->user->name??'',
        //                                 'nama_shift'=>$row_daily->shift->name ?? 'tidak ada shift',
        //                                 'min_masuk'=>$min_time_in,
        //                                 'max_keluar'=>$max_time_out,
        //                                 'limit_masuk'=>$row_daily->shift->time_in ?? 'tidak ada shift',
        //                                 'limit_keluar'=>$row_daily->shift->time_out ?? 'tidak ada shift',
        //                                 'masuk'=>$row_daily->masuk,
        //                                 'pulang'=>$row_daily->pulang,
        //                                 'date'=>$row_daily->date,
        //                                 'status'=>$row_daily->rawStatus(),
        //                             ];
        //                         }
        //                     }
        //                     else{
        //                         $attendance_user[$row_daily->user->id][]=[
        //                             'user_id'=>$row_daily->user->employee_no??'',
        //                             'user_name'=>$row_daily->user->name??'',
        //                             'nama_shift'=> 'tidak ada shift',
        //                             'min_masuk'=>'tidak ada shift',
        //                             'max_keluar'=>'tidak ada shift',
        //                             'limit_masuk'=>'tidak ada shift',
        //                             'limit_keluar'=>'tidak ada shift',
        //                             'masuk'=>'tidak ada shift',
        //                             'pulang'=>'tidak ada shift',
        //                             'date'=>$row_daily->date,
        //                             'status'=>$row_daily->rawStatus(),
        //                         ];
        //                     }
        //                 }
        //             }else{
        //                 $attendance_user[$row_daily->user->id][]=[
        //                     'user_id'=>$row_daily->user->employee_no??'',
        //                     'user_name'=>$row_daily->user->name??'',
        //                     'nama_shift'=> 'tidak ada shift',
        //                     'min_masuk'=>'tidak ada shift',
        //                     'max_keluar'=>'tidak ada shift',
        //                     'limit_masuk'=>'tidak ada shift',
        //                     'limit_keluar'=>'tidak ada shift',
        //                     'masuk'=>'tidak ada shift',
        //                     'pulang'=>'tidak ada shift',
        //                     'date'=>$row_daily->date,
        //                     'status'=>$row_daily->rawStatus(),
        //                 ];
        //             }
                    
        //         }
        //     }
        // }
        $distinctDatesCount = count($distinctDates);
        return view('admin.exports.attendance_daily_report_v3', [
            'data' => $attendanceDetail,
            'date' => $distinctDates,
            'user_id'=>$distinctUsers,
            'attendanceUser'=>$attendance_user,
            'shift_per_date' => $shift_per_date,
            'distinctDatesCount' => $distinctDatesCount,
        ]);
    }
}
