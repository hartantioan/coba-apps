<?php

namespace App\Imports;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\ItemStock;
use App\Models\ItemUnit;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ImportItemMaster implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $key => $row) {
            if($key >= 1 && $row[0]){
                $item = null;
                $unit = Unit::where('code',trim(explode('#',$row[3])[0]))->first();
                $group = ItemGroup::where('code',trim(explode('#',$row[2])[0]))->first();
                $item = Item::where('code',$row[0])->first();
                if($item){
                    $item->update([
                        'note'  => $item->note.','.$row[9],
                    ]);
                }else{
                    $item = Item::create([
                        'code'              => $row[0],
                        'name'              => $row[1],
                        'item_group_id'     => $group->id,
                        'uom_unit'          => $unit->id,
                        'tolerance_gr'      => $row[4] ?? 0,
                        'is_inventory_item' => $row[5] ?? NULL,
                        'is_sales_item'     => $row[6] ?? NULL,
                        'is_purchase_item'  => $row[7] ?? NULL,
                        'is_service'        => $row[8] ?? NULL,
                        'note'              => $row[9],
                        'min_stock'         => $row[10] ?? NULL,
                        'max_stock'         => $row[11] ?? NULL,
                        'status'            => '1',
                    ]);

                    foreach($item->itemGroup->itemGroupWarehouse as $row){
                        ItemStock::create([
                            'place_id'      => 1,
                            'warehouse_id'  => $row->warehouse_id,
                            'item_id'       => $item->id,
                            'qty'           => 0
                        ]);
                    }
                    
                    ItemUnit::create([
                        'item_id'       => $item->id,
                        'unit_id'       => $unit->id,
                        'conversion'    => 1,
                        'is_sell_unit'  => '1',
                        'is_buy_unit'   => '1',
                        'is_default'    => '1',
                    ]);
                }
                activity()
                    ->performedOn(new Item())
                    ->causedBy(session('bo_id'))
                    ->withProperties($item)
                    ->log('Import the item data');
            }
        }
    }
}