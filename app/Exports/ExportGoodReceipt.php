<?php

namespace App\Exports;

use App\Models\GoodReceipt;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportGoodReceipt implements FromView,ShouldAutoSize
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
            return view('admin.exports.good_receipt', [
                'data' => GoodReceipt::where(function ($query) {
                    $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date)
                    ->whereHas('goodReceiptDetail',function($query){
                        $query->whereIn('warehouse_id',$this->warehouses);
                    });
                    if(!$this->modedata){
                        $query->where('user_id',session('bo_id'));
                    }
                })
                ->get(),
                'modedata'  => $this->modedata,
            ]);
        }elseif($this->mode == '2'){
            return view('admin.exports.good_receipt', [
                'data' => GoodReceipt::withTrashed()->where(function ($query) {
                    $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date)
                    ->whereHas('goodReceiptDetail',function($query){
                        $query->whereIn('warehouse_id',$this->warehouses);
                    });
                    if(!$this->modedata){
                        $query->where('user_id',session('bo_id'));
                    }
                })
                ->get(),
                'modedata'  => $this->modedata,
            ]);
        }
    }
}
