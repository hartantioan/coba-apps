<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\View\View;
class ExportTrialBalance implements  FromView,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $month_start, $month_end, $level, $company;

    public function __construct(string $month_start, string $month_end,int $level,int $company)
    {
        $this->month_start = $month_start ? $month_start : '';
		$this->month_end = $month_end ? $month_end : '';
        $this->level = $level ? $level : 1;
        $this->company = $company ? $company : '';
    }
    public function view(): View
    {
        return view('admin.exports.trial_balance', [
            'month_start'   => $this->month_start,
            'month_end'     => $this->month_end,
            'level'         => $this->level,
            'company_id'    => $this->company,       
        ]);
    }
}
