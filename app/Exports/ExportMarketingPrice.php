<?php

namespace App\Exports;

use App\Models\MarketingOrderDetail;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class ExportMarketingPrice implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct(string $search, string $item)
    {
        $this->search = $search ? $search : '';
		$this->item = $item ? $item : '';

    }
    public function view(): View
    {
        $query_data= MarketingOrderDetail::where(function($query) {
            if($this->search) {
                $query->whereHas('marketingOrder',function($query){
                    $query->where('code', 'like', "%$this->search%");
                })->orWhereHas('item',function($query){
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('name', 'like', "%$this->search%");
                });
            }
            if($this->item){
                $query->where('item_id',$this->item);
            }
        })
        ->whereHas('marketingOrder',function($query){
            $query->whereIn('status',['2','3']);
        })
        ->get();
        
        $array_filter = [];
        foreach($query_data as $val) {
            
            $finalpricedisc1 = ($val->price - $val->margin) * ($val->percent_discount_1 / 100);
            $finalpricedisc2 = (($val->price - $val->margin) - $finalpricedisc1) * ($val->percent_discount_2 / 100);

            $array_filter[]=[
                'item'          => $val->item->code.' - '.$val->item->name,
                'customer'      => $val->marketingOrder->account->name,
                'code'          => $val->marketingOrder->code,
                'date'          => date('d/m/Y',strtotime($val->marketingOrder->post_date)),
                'place'         => $val->place->code, 
                'price'         => round($val->price,2),
                'margin'        => round($val->margin,2),
                'disc1'         => round($finalpricedisc1,2),
                'disc2'         => round($finalpricedisc2,2),
                'disc3'         => round($val->discount_3,2),
                'final_price'   => round($val->price_after_discount,2),
            ];
            
        }
      
        return view('admin.exports.sales_price', [
            'data' => $array_filter,
        ]);
    }


}
