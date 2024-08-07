<?php

namespace App\Exports;

use App\Models\ProductionReceive;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportProductionReceive implements FromView, ShouldAutoSize
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
            $data = ProductionReceive::where(function($query){
                $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date);
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
