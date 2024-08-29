<?php

namespace App\Exports;

use App\Models\ProductionHandover;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportProductionHandover implements FromView, ShouldAutoSize
{
    protected $start_date, $end_date, $mode, $modedata, $nominal;
    public function __construct(string $start_date, string $end_date, string $mode, string $modedata, string $nominal)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->mode = $mode ? $mode : '';
        $this->modedata = $modedata ?? '';
        $this->nominal = $nominal ?? '';
    }
    public function view(): View
    {
        if($this->mode == '1'){
            $data = ProductionHandover::where(function($query){
                $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<='   , $this->end_date);
                    if(!$this->modedata){
                        $query->where('user_id',session('bo_id'));
                    }
            })
            ->get();
            activity()
                ->performedOn(new ProductionHandover())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export production handover.');
            return view('admin.exports.production_handover', [
                'data'      => $data,
                'nominal'   => $this->nominal,
            ]);
        }elseif($this->mode == '2'){
            $data = ProductionHandover::withTrashed()->where(function($query){
                $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date);
                    if(!$this->modedata){
                        $query->where('user_id',session('bo_id'));
                    }
            })
            ->get();
            activity()
                ->performedOn(new ProductionHandover())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export production handover.');
            return view('admin.exports.production_handover', [
                'data'      => $data,
                'nominal'   => $this->nominal,
            ]);
        }
    }
}
