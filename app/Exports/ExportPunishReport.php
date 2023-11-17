<?php

namespace App\Exports;

use App\Models\AttendancePunishment;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExportPunishReport implements FromView,ShouldAutoSize,WithTitle
{
    public function __construct(string $period_id)
    {
        $this->period_id = $period_id ? $period_id : '';

    }

    public function title(): string
    {
        return 'Punish Monthly Report'; // Set the custom name for the first sheet
    }

    public function view(): View
    {
        $query_data_punish = AttendancePunishment::where(function($query)  {
            if($this->period_id) {
                $query->where('period_id', $this->period_id);
            }
        })
        ->orderBy('employee_id','ASC')
        ->get();

      
        return view('admin.exports.attendance_punishment_report', [
            'data' => $query_data_punish,
        ]);
    }
}
