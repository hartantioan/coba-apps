<?php

namespace App\Imports;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\ItemShading;
use App\Models\ItemStock;
use App\Models\ItemUnit;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportItem implements OnEachRow, WithHeadingRow, WithBatchInserts,WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }
    public function onRow(Row $row)
    {
        $row = $row->toArray();
      
        if($row['code']){
            $check = Item::where('code',$row['code'])->first();
            
            if(!$check){
                $item_group_code = explode('#',$row['item_group'])[0];
                $item_group_id = ItemGroup::where('code',$item_group_code)->first();
                
                $item_unit_code = explode('#',$row['unit'])[0];
                $item_unit_id= Unit::where('code',$item_unit_code)->first();
                
                $query = Item::create([
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'item_group_id' =>$item_group_id->id,
                    'uom_unit' => $item_unit_id->id,
                    'tolerance_gr' => $row['toleransi_gr'],
                    'is_inventory_item' => $row['is_invent_item'],
                    'is_sales_item' => $row['is_sales_item'],
                    'is_purchase_item' => $row['is_purchase'],
                    'is_service' => $row['is_service'],
                    'is_quality_check' => $row['is_quality_check'],
                    'is_hide_supplier' => $row['is_hide_supplier'],
                    'note' => $row['note'],
                    'min_stock' => $row['min_stock'],
                    'max_stock' => $row['max_stock'],
                    'status' => '1',
                ]);

                foreach($query->itemGroup->itemGroupWarehouse as $row){
                    ItemStock::create([
                        'place_id'      => 1,
                        'warehouse_id'  => $row->warehouse_id,
                        'item_id'       => $query->id,
                        'qty'           => 0
                    ]);
                }
                
                ItemUnit::create([
                    'item_id'       => $query->id,
                    'unit_id'       => $item_unit_id->id,
                    'conversion'    => 1,
                    'is_buy_unit'   => '1',
                    'is_default'    => '1',
                ]);

                activity()
                    ->performedOn(new Item())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit from excel item data.');
            }
        }
    }

    public function batchSize(): int
    {
        return 1000;
    }
}