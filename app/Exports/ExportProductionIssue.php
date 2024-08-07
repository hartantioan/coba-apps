<?php

namespace App\Exports;

use App\Models\ProductionIssue;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportProductionIssue implements FromView, ShouldAutoSize
{
    protected $start_date, $end_date, $mode;
    public function __construct(string $start_date, string $end_date, string $mode)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->mode = $mode ? $mode : '';
    }

    public function view(): View
    {
        if($this->mode == '1'){
            $data = ProductionIssue::where(function($query){
                $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<='   , $this->end_date);
            })
            ->get();
            activity()
                ->performedOn(new ProductionIssue())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export production issue.');
            return view('admin.exports.production_issue', [
                'data' => $data
            ]);
        }elseif($this->mode == '2'){
            $data = ProductionIssue::withTrashed()->where(function($query){
                $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date);
            })
            ->get();
            activity()
                ->performedOn(new ProductionIssue())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export production issue.');
            return view('admin.exports.production_issue', [
                'data' => $data
            ]);
        }
    }
}
