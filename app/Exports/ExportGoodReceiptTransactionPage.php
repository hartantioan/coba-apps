<?php

namespace App\Exports;

use App\Models\GoodReceipt;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportGoodReceiptTransactionPage implements FromView,ShouldAutoSize
{
    protected $search,$start_date, $end_date, $status, $modedata, $warehouses, $nominal;
    public function __construct(string $search ,string $start_date, string $end_date,string $status, string $modedata, string $nominal, array $warehouses)
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status   = $status ? explode(',',$status) : '';
        $this->modedata = $modedata ? $modedata : '';
        $this->warehouses = $warehouses;
        $this->nominal = $nominal ?? '';
    }

    public function view(): View
    {
        return view('admin.exports.good_receipt', [
            'data' => GoodReceipt::where(function ($query) {
                if($this->search) {
                    $query->where(function($query){
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('post_date', 'like', "%$this->search%")
                            ->orWhere('document_date', 'like', "%$this->search%")
                            ->orWhere('receiver_name', 'like', "%$this->search%")
                            ->orWhere('note', 'like', "%$this->search%")
                            ->orWhereHas('goodReceiptDetail',function($query){
                                $query->whereHas('item',function($query) {
                                    $query->where('code', 'like', "%$this->search%")
                                        ->orWhere('name','like',"%$this->search%");
                                });
                            })
                            ->orWhereHas('user',function($query){
                                $query->where('name','like',"%$this->search%")
                                    ->orWhere('employee_no','like',"%$this->search%");
                            });
                    });
                }
                if($this->start_date && $this->end_date) {
                    $query->whereDate('post_date', '>=', $this->start_date)
                        ->whereDate('post_date', '<=', $this->end_date);
                } else if($this->start_date) {
                    $query->whereDate('post_date','>=', $this->start_date);
                } else if($this->end_date) {
                    $query->whereDate('post_date','<=', $this->end_date);
                }

                if($this->status){
                    $query->whereIn('status', $this->status);
                }

                if(!$this->modedata){
                    $query->where('user_id',session('bo_id'));
                }
            })
            ->whereHas('goodReceiptDetail',function($query){
                $query->whereIn('warehouse_id',$this->warehouses);
            })
            ->get(),
            'modedata'  => $this->modedata,
            'nominal'   => $this->nominal,
        ]);
    }
}
