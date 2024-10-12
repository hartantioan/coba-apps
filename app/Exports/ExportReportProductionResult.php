<?php

namespace App\Exports;

use App\Models\ProductionHandover;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReportProductionResult implements FromView, ShouldAutoSize
{
    protected $start_date, $end_date;
    public function __construct(string $start_date, string $end_date)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
    }
    public function view(): View
    {
            $data = ProductionHandover::where(function($query){
                $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<='   , $this->end_date);
            })
            ->get();
            activity()
                ->performedOn(new ProductionHandover())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export production result.');
            return view('admin.exports.production_result', [
                'data'      => $data,
            ]);
    }
}
