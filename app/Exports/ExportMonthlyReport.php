<?php

namespace App\Exports;

use App\Models\AttendanceMonthlyReport;
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

      
        return view('admin.exports.attendance_monthly_report', [
            'data' => $query_data_monthly,
        ]);
    }
}
