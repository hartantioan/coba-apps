<?php

namespace App\Exports;

use App\Models\ItemCogs;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportStockMovement implements FromView,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct(string $plant, string $item, string $start_date, string $finish_date)
    {
        $this->plant = $plant ? $plant : '';
		$this->item = $item ? $item : '';
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
    }
    public function view(): View
    {
        $query_data = ItemCogs::where(function($query)  {
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
            if($this->plant){
                $query->whereHas('place',function($query){
                    $query->where('id',$this->plant);
                });
            }
            
        })
        ->orderBy('date','ASC')
        ->orderBy('id','ASC')
        ->get();
        
        $array_filter = [];
        $firstDate = null;
        $array_filter=[];
        $uom_unit = null;

        foreach($query_data as $row){
            $data_tempura = [
                'keterangan' => $row->lookable->code.'-'.$row->lookable->note,
                'date' =>  date('d/m/y',strtotime($row->date)),
                'masuk'=> number_format($row->qty_in,3,',','.') ?? '-',
                'keluar'=>number_format($row->qty_out,3,',','.') ?? '-',
                'final'=>number_format($row->qty_final,3,',','.'),
            ];
            $array_filter[]=$data_tempura;
            if ($firstDate === null) {
                $firstDate = $row->date;
            }
            if($uom_unit ===null){
                $uom_unit = $row->item->uomUnit->code;
            }
        }
        $last_nominal=0;
        if($firstDate != null){
            $query_first = ItemCogs::where('date', '<', $firstDate)
            ->where('item_id',$this->item)
            ->where('place_id',$this->plant)
            ->orderBy('date', 'desc')
            ->first();
            if($query_first){
                $last_nominal=number_format($query_first->qty_final,3,',','.');
            }
            
        }

      
        return view('admin.exports.stock_movement', [
            'data' => $array_filter,
            'latest'   =>$last_nominal,
            'uomunit'  =>$uom_unit,
        ]);
    }
}
