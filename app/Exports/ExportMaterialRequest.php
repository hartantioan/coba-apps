<?php

namespace App\Exports;

use App\Models\MaterialRequest;
use App\Models\MaterialRequestDetail;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportMaterialRequest implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $start_date, $end_date, $mode, $modedata, $nominal, $warehouses;

    public function __construct(string $start_date, string $end_date, string $mode, string $modedata, string $nominal, array $warehouses)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->mode = $mode ? $mode : '';
        $this->modedata = $modedata ?? '';
        $this->nominal = $nominal ?? '';
        $this->warehouses = $warehouses;
    }

    public function view(): View
    {
        if($this->mode == '1'){
            return view('admin.exports.material_request', [
                'data' => MaterialRequestDetail::whereHas('materialRequest',function($query){
                    $query->where(function ($query) {
                        $query->where('post_date', '>=',$this->start_date)
                        ->where('post_date', '<=', $this->end_date);
                    });
                    if(!$this->modedata){
                        $query->where('user_id',session('bo_id'));
                    }
                })
                ->whereIn('warehouse_id',$this->warehouses)
                ->get()
            ]);
        }elseif($this->mode == '2'){
            return view('admin.exports.material_request', [
                'data' => MaterialRequestDetail::withTrashed()->whereHas('materialRequest',function($query){
                    $query->where(function ($query) {
                        $query->where('post_date', '>=',$this->start_date)
                        ->where('post_date', '<=', $this->end_date);
                    });
                    if(!$this->modedata){
                        $query->where('user_id',session('bo_id'));
                    }
                })
                ->where(function ($query) {
                    
                    $query->whereNull('deleted_at')
                          ->orWhereHas('materialRequest', function ($query) {
                              $query->withTrashed()->whereNotNull('deleted_at');
                          });
                })
                ->whereIn('warehouse_id',$this->warehouses)
                ->get()
            ]);
        }
    }
}
