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
            if($this->plant != 'all'){
                $query->whereHas('place',function($query){
                    $query->where('id',$this->plant);
                });
            }
            
        })
        ->orderBy('item_id')
        ->orderBy('date')
        ->orderBy('type')
        ->get();
        
        $previousId = null; // Initialize the previous ID variable
        $cum_qty = 0;
        $cum_val = 0 ;
        $firstDate = null;
        $array_filter=[];
        $uom_unit = null;

        foreach($query_data as $row){
            if ($previousId !== null && $row->item_id !== $previousId) {
                $cum_qty = 0; // Reset the count when the ID changes
                $cum_val = 0;
            }
            if($row->type=='IN'){
                $cum_qty+=$row->qty_final;
                $cum_val+=$row->total_final;
            }else{
                $cum_qty-=$row->qty_final;
                $cum_val-=$row->total_final;
            }
            
            $data_tempura = [
                'plant' => $row->place->code,
                'warehouse' => $row->warehouse->code,
                'item' => $row->item->name,
                'satuan' => $row->item->uomUnit->code,
                'kode' => $row->item->code,
                'final'=>number_format($row->price_final,2,',','.'),
                'total'=>number_format($cum_val,2,',','.'),
                'qty'=>number_format($cum_qty,3,',','.'),
                'date' =>  date('d/m/Y',strtotime($row->date)),
                'document' => $row->lookable->code,
                'cum_qty' => number_format($row->qty_final,3,',','.'),
                'cum_val' => number_format($row->total_final,2,',','.'),
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
