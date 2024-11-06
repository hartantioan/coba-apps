<?php

namespace App\Exports;

use App\Models\ProductionReceive;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportProductionReceive implements FromView, ShouldAutoSize
{
    protected $start_date, $end_date, $mode, $line_id;
    public function __construct(string $start_date, string $end_date, string $mode,string $line_id)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->mode = $mode ? $mode : '';
        $this->line_id = $line_id ?? '';
    }
    public function view(): View
    {
        if($this->mode == '1'){
            $data = ProductionReceive::where(function($query){
                $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date);
                if($this->line_id){
                    $query->where('line_id',$this->line_id);
                }
            })
            ->get();
            activity()
                ->performedOn(new ProductionReceive())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export production receive.');
            return view('admin.exports.production_receive', [
                'data' => $data
            ]);
        }elseif($this->mode == '2'){
            $data =ProductionReceive::withTrashed()->where(function($query){
                $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date);
                if($this->line_id){
                    $query->where('line_id',$this->line_id);
                }
            })
            ->get();
            activity()
                ->performedOn(new ProductionReceive())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export production receive.');
            return view('admin.exports.production_receive', [
                'data' => $data
            ]);
        }
    }
}
