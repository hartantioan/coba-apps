<?php

namespace App\Exports;

use App\Models\ItemCogs;
use App\Models\ItemStock;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReportSalesSummaryStockFg implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $finish_date;

    public function __construct(string $finish_date)
    {
        $this->finish_date = $finish_date ? $finish_date : '';
    }

    private $headings = [
        'item code',
        'name',
        'shading',
        'in',
        'out',
        'total',
    ];
    // public function collection()
    // {
    //     $query_data = ItemStock::where(function($querys){
    //         $querys->whereHas('item',function($query){
    //             $query->where('status',1);
    //         });
    //         // if($request->item_shading_id != 'null'){
           
    //         //     $querys->where('item_shading_id',$request->item_shading_id);
    //         // }
    //         // if($request->production_batch_id != 'null'){
           
    //         //     $querys->where('production_batch_id',$request->production_batch_id);
    //         // }
    //         // if($request->area != 'all'){
    //         //     $querys->where('area_id',$request->area);
    //         // }
    //         // if($request->plant != 'all'){
    //         //     $querys->where('place_id',$request->plant);
    //         // }
    //     })
    //     ->join('items', 'item_stocks.item_id', '=', 'items.id') 
    //     ->selectRaw('item_stocks.*, items.code, items.name, SUM(item_stocks.qty) as total_quantity') 
    //     ->groupBy('item_stocks.item_id', 'items.code', 'items.name') 
    //     ->get();

    //     $x=1;
    //     foreach($query_data as $key => $row){
            
    //         'pallet_conversion'=>number_format(($arr['qty']/$row->item->sellConversion()),3,',','.'),
    //         'box_conversion'=>number_format(($arr['qty']/$row->item->sellConversion())*$row->item->pallet->box_conversion,3,',','.'),
    //         $subtotal = $row->subtotal * $row->currency_rate;
    //         $discount = $row->discount * $row->currency_rate;
    //         $total = $subtotal - $discount;
    //         $arr[] = [
    //             'item_code'      => $row->item->code,
    //             'item_name'      => $row->item->name,
    //             'shading'        => $row->itemShading->code,
    //             'qty_m2'         => $row->total_quantity,
    //             'box'            => $row->total_quantity/$row-,
    //             'palet'          => $row->marketingOrderDeliveryDetail->first()->marketingOrderDelivery->marketingOrderDeliveryProcess->marketingOrderInvoice->code??'-',
                
    //         ];
    //         $x++;
            
        
            
    //     }

    //     return collect($arr);
    // }

    public function collection()
    {
        // $query_data = ItemCogs::whereRaw("id IN (SELECT MAX(id) FROM item_cogs WHERE deleted_at IS NULL AND date <= '".$this->finish_date."' GROUP BY item_id, item_shading_id)")
        //     ->where(function($query) {
        //         $query->whereHas('item',function($query){
        //             $query->whereIn('status',['1','2']);
        //         });
        //         if($this->finish_date) {
        //             $query->whereDate('date','<=', $this->finish_date);
        //         }
                
        //     })
        //     ->orderBy('date', 'desc')
        //     ->orderBy('id', 'desc')
        //     ->get();
        $query_data = "call report_stock_fg('".$this->finish_date."');";
        $submit = DB::select($query_data);

        foreach ($submit as $row) {
        
            $arr[] = [
                'item_code' => $row->itemcode, // Directly use the itemcode from the object
                'item_name' => $row->name, // Use the name from the object
                'shading' => $row->shading, // Use the shading from the object
                
                'IN' => $row->IN, // Use the IN value from the object
                'out' => $row->out, // Use the out value from the object
                'total' => $row->total, // Use the total value from the object
            ];
        }

        return collect($arr);

        $x=1;
        

    }

    public function title(): string
    {
        return 'Marketing Order Detail 2';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}
