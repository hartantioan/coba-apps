<?php

namespace App\Imports;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\ItemUnit;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ImportItemMaster implements ToCollection
{
    public function collection(Collection $rows)
    {
        /* DB::beginTransaction();
        try { */
            foreach ($rows as $key => $row) {
                $item = null;
                $unit = Unit::where('code',trim($row[2]))->first();
                $group = ItemGroup::where('name',trim($row[3]))->first();
                $item = Item::where('code',$row[0])->first();

                if($item){
                    $item->update([
                        'note'  => $item->note.','.$row[5],
                    ]);
                }else{
                    $item = Item::create([
                        'code'              => $row[0],
                        'name'              => $row[1],
                        'item_group_id'     => $group->id,
                        'uom_unit'          => $unit->id,
                        'tolerance_gr'      => 0,
                        'is_inventory_item' => '1',
                        'is_sales_item'     => NULL,
                        'is_purchase_item'  => '1',
                        'is_service'        => $row[4] == '2' ? '1' : NULL,
                        'note'              => $row[5],
                        'min_stock'         => 0,
                        'max_stock'         => 0,
                        'status'            => $row[4],
                        'created_at'        => date('Y-m-d H:i:s'),
                        'updated_at'        => date('Y-m-d H:i:s'),
                    ]);
                    ItemUnit::create([
                        'item_id'       => $item->id,
                        'unit_id'       => $unit->id,
                        'conversion'    => 1,
                        'is_sell_unit'  => NULL,
                        'is_buy_unit'   => '1',
                        'is_default'    => '1',
                    ]);
                }
            }
            /* DB::commit();
        }catch(\Exception $e){
            DB::rollback();
        } */
    }
}