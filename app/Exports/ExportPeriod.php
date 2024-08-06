<?php

namespace App\Exports;

use App\Http\Controllers\HR\AttendancePresenceReportController;
use App\Models\AttendanceDailyReports;
use App\Models\AttendanceMonthlyReport;
use App\Models\PresenceReport;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExportPeriod implements WithMultipleSheets
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    protected $period_id;
    public function __construct(string $period_id)
    {
        $this->period_id = $period_id ? $period_id : '';
    }

    public function sheets(): array
    {
        
        $sheets = [];
        // Create sheets for each dataset
        $sheets[] = new ExportDailyReport($this->period_id);
        $sheets[] = new ExportMonthlyReport($this->period_id);
        $sheets[] = new ExportPresenceReport($this->period_id);
        $sheets[] = new ExportPunishReport($this->period_id);
        $sheets[] = new ExportSalaryReport($this->period_id);
        $sheets[] = new ExportSalaryReportDailyPayment($this->period_id);
        $sheets[] = new ExportAttendancePeriod($this->period_id);
        return $sheets;
    }

}
