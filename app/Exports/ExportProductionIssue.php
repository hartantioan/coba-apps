<?php

namespace App\Exports;

use App\Models\ProductionIssue;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportProductionIssue implements FromView, ShouldAutoSize
{
    protected $start_date, $end_date, $mode, $nominal, $line_id;
    public function __construct(string $start_date, string $end_date, string $mode, string $nominal,string $line_id)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->mode = $mode ? $mode : '';
        $this->nominal = $nominal ?? '';
        $this->line_id = $line_id ?? '';
        info($this->line_id);
    }

    public function view(): View
    {
        if($this->mode == '1'){
            $data = ProductionIssue::where(function($query){
                $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<='   , $this->end_date);
                if($this->line_id){
                    $query->where('line_id',$this->line_id);
                }
            })
            ->get();
            activity()
                ->performedOn(new ProductionIssue())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export production issue.');
            return view('admin.exports.production_issue', [
                'data'      => $data,
                'nominal'   => $this->nominal,
            ]);
        }elseif($this->mode == '2'){
            $data = ProductionIssue::withTrashed()->where(function($query){
                $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date);
                if($this->line_id){
                    $query->where('line_id',$this->line_id);
                }
            })
            ->get();
            activity()
                ->performedOn(new ProductionIssue())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export production issue.');
            return view('admin.exports.production_issue', [
                'data'      => $data,
                'nominal'   => $this->nominal,
            ]);
        }
    }
}
