<?php

namespace App\Exports;

use App\Models\AttendancePeriod;
use App\Models\Attendances;
use Carbon\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExportAttendancePeriod implements FromView,ShouldAutoSize,WithTitle
{
    protected $period_id;
    public function __construct(string $period_id)
    {
        $this->period_id = $period_id ? $period_id : '';

    }
    public function title(): string
    {
        return 'Absensi Periode ini'; // Set the custom name for the first sheet
    }

    public function view(): View
    {
        $attendance_period  = AttendancePeriod::find($this->period_id);
        $startDateTime = Carbon::parse($attendance_period->start_date)->startOfDay();
        $endDateTime = Carbon::parse($attendance_period->end_date)->endOfDay();
        $attendances = Attendances::whereBetween('date', [$startDateTime, $endDateTime])
        ->where('attendance_machine_id',5)
        ->orderBy('employee_no')
        ->orderBy('date')
        ->get();
        
        $array=[];
        foreach($attendances as $row_data){
            $carbonDate = Carbon::parse($row_data->date);

            $formattedDate = $carbonDate->format('d-m-Y');

           
            $formattedTime = $carbonDate->format('H:i:s');
            $array[]=[
                'employee_no' => $row_data->employee_no,
                'date'        => $formattedDate,
                'time'        => $formattedTime,
            ];
        }
        
        return view('admin.exports.attendance_period', [
            'data' => $array,
        ]);
        
    }
}
