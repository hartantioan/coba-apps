<?php

namespace App\Console\Commands;

use App\Models\Item;
use App\Models\ItemCogs;
use Illuminate\Console\Command;

class AccumulateCogsByItem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accumulatecogs:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Itung ulang nilai akumulasi item.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $place_id = 1;
        $company_id = 1;
        $data = Item::where('item_group_id',7)->get();
        $date = '2024-10-01';

        foreach($data as $item){
            $item_id = $item->id;
            $itemcogs2 = ItemCogs::where('date','>=',$date)->where('company_id',$company_id)->where('place_id',$place_id)->where('item_id',$item_id)->orderBy('date')->orderBy('id')->get();
            $old_data2 = ItemCogs::where('date','<',$date)->where('company_id',$company_id)->where('place_id',$place_id)->where('item_id',$item_id)->orderByDesc('date')->orderByDesc('id')->first();

            $total_final = 0;
            $qty_final = 0;
            $price_final = 0;
            foreach($itemcogs2 as $key2 => $row){
                if($key2 == 0){
                    if($old_data2){
                        if($row->type == 'IN'){
                            $total_final = $old_data2->total_final + $row->total_in;
                            $qty_final = $old_data2->qty_final + $row->qty_in;
                        }elseif($row->type == 'OUT'){
                            $total_final = $old_data2->total_final - $row->total_out;
                            $qty_final = $old_data2->qty_final - $row->qty_out;
                        }

                        $price_final = $qty_final > 0 ? round($total_final / $qty_final,5) : 0;
                    }else{
                        if($row->type == 'IN'){
                            $total_final = $row->total_in;
                            $qty_final = $row->qty_in;
                        }elseif($row->type == 'OUT'){
                            $total_final = 0 - $row->total_out;
                            $qty_final = 0 - $row->qty_out;
                        }

                        $price_final = $qty_final > 0 ? round($total_final / $qty_final,5) : 0;
                    }
                    $row->update([
                        'price_final'	=> $price_final,
                        'qty_final'		=> round($qty_final,3),
                        'total_final'	=> round($total_final,2),
                    ]);
                }else{
                    if($row->type == 'IN'){
                        $total_final += $row->total_in;
                        $qty_final += $row->qty_in;
                    }elseif($row->type == 'OUT'){
                        $total_final -= $row->total_out;
                        $qty_final -= $row->qty_out;
                    }
                    $price_final = $qty_final > 0 ? round($total_final / $qty_final,5) : 0;
                    $row->update([
                        'price_final'	=> $price_final,
                        'qty_final'		=> round($qty_final,3),
                        'total_final'	=> round($total_final,2),
                    ]);
                }
            }
        }
    }
}
