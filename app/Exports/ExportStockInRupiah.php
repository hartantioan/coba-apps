<?php

namespace App\Exports;

use App\Models\ItemCogs;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportStockInRupiah implements FromView,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct(string $plant, string $item,string $warehouse, string $start_date, string $finish_date)
    {
        $this->plant = $plant ? $plant : '';
		$this->item = $item ? $item : '';
        $this->warehouse = $warehouse ? $warehouse : '';
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
    }

    public function view(): View
    {
       
        $query_data = ItemCogs::where(function($query) {
            if($this->start_date && $this->finish_date) {
                $query->whereDate('date', '>=', $this->start_date)
                    ->whereDate('date', '<=', $this->finish_date);
            } else if($this->start_date) {
                $query->whereDate('date','>=', $this->start_date);
            } else if($this->finish_date) {
                $query->whereDate('date','<=', $this->finish_date);
            }
            if($this->item) {
                $query->whereHas('item',function($query){
                    $query->where('id',$this->item);
                });
            }
            if($this->plant != 'all'){
                $query->whereHas('place',function($query){
                    $query->where('id',$this->plant);
                });
            }
            if($this->warehouse != 'all'){
                $query->whereHas('warehouse',function($query){
                    $query->where('id',$this->warehouse);
                });
            }
        })->get();
        
        $array_filter = [];
        $array_filter=[];
        foreach($query_data as $row){
            $data_tempura = [
                'item' => $row->item->code.'-'.$row->item->name,
                'final'=>number_format($row->price_final,2,',','.'),
                'totalfinal'=>number_format($row->total_final,2,',','.'),
                'qtyfinal'=>number_format($row->qty_final,3,',','.'),
                'date' =>  date('d/m/Y',strtotime($row->date)),
            ];
            $array_filter[]=$data_tempura;
        }
        return view('admin.exports.stock_in_rupiah', [
            'data' => $array_filter,
        ]);
    }
}
