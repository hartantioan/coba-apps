<?php

namespace App\Exports;

use App\Models\ProductionFgReceive;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportProductionFgReceive implements  FromView, ShouldAutoSize
{
    protected $start_date, $end_date, $mode, $modedata, $nominal, $line_id;
    public function __construct(string $start_date, string $end_date, string $mode, string $modedata, string $nominal,string $line_id)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->mode = $mode ? $mode : '';
        $this->modedata = $modedata ?? '';
        $this->nominal = $nominal ?? '';
        $this->line_id = $line_id ?? '';
    }

    public function view(): View
    {
        if($this->mode == '1'){
            $data =  ProductionFgReceive::where(function($query){
                $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<='   , $this->end_date);
                    if(!$this->modedata){
                        $query->where('user_id',session('bo_id'));
                    }
                    if($this->line_id){
                        $query->where('line_id',$this->line_id);
                    }
            })
            ->get();
            activity()
                ->performedOn(new ProductionFgReceive())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export production fg receive.');
            return view('admin.exports.production_fg_receive', [
                'data'      => $data,
                'nominal'   => $this->nominal,
            ]);
        }elseif($this->mode == '2'){
            $data = ProductionFgReceive::withTrashed()->where(function($query){
                $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date);
                    if(!$this->modedata){
                        $query->where('user_id',session('bo_id'));
                    }
                    if($this->line_id){
                        $query->where('line_id',$this->line_id);
                    }
            })
            ->get();
            activity()
                ->performedOn(new ProductionFgReceive())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export production fg receive.');
            return view('admin.exports.production_fg_receive', [
                'data'      => $data,
                'nominal'   => $this->nominal,
            ]);
        }
    }
}
