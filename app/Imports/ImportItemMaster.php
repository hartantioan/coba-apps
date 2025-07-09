<?php

namespace App\Imports;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\ItemStock;
use App\Models\ItemStockNew;
use App\Models\ItemUnit;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Row;

class ImportItem implements WithMultipleSheets
{
    public function sheets(): array
    {
        activity()
        ->performedOn(new Item())
        ->causedBy(session('bo_id'))
        ->withProperties('')
        ->log('Add / edit from excel item data.');

        return [
            0 => new handleItemSheet(),
        ];
    }

}
class handleItemSheet implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
            $check = null;
            $row = $row->toArray();
            if(isset($row['code']) && $row['code']){
                $check = Item::withTrashed()->where('code',$row['code'])->first();

                $item_group_code = explode('#',$row['item_group'])[0];
                $item_group_id = ItemGroup::where('code',$item_group_code)->first();

                $item_unit_code = explode('#',$row['unit'])[0];
                $item_unit_id= Unit::where('code',$item_unit_code)->first();

                if(!$check){
                    $query = Item::create([
                        'code'              => $row['code'],
                        'name'              => $row['name'],
                        'item_group_id'     => $item_group_id->id,
                        'uom_unit'          => $item_unit_id->id,
                        'tolerance_gr'      => $row['toleransi_gr'],
                        'is_inventory_item' => $row['is_invent_item'],
                        'is_sales_item'     => $row['is_sales_item'],
                        'is_purchase_item'  => $row['is_purchase'],
                        'is_service'        => $row['is_service'],
                        'is_production'     => $row['is_production'],
                        'note'              => $row['note'],
                        'status'            => '1',
                    ]);

                    ItemStockNew::create([
                        'item_id'       => $query->id,
                        'qty'           => 0
                    ]);




                }else{

                    if(!$check->hasChildDocument()){
                        if ($check->trashed()) {
                            $check->restore();
                        }
                        $check->name = $row['name'];
                        $check->item_group_id=$item_group_id->id;
                        $check->uom_unit=$item_unit_id->id;
                        $check->tolerance_gr=$row['toleransi_gr'];
                        $check->is_inventory_item=$row['is_invent_item'];
                        $check->is_sales_item=$row['is_sales_item'];
                        $check->is_purchase_item=$row['is_purchase'];
                        $check->is_service=$row['is_service'];
                        $check->is_production = $row['is_production'];
                        $check->note =  $row['note'];

                        $check->save();

                    }
                }
            }
            else{
                info('mbeng');
            }
            DB::commit();
        }catch (\Exception $e) {
            Log::error($e);
            DB::rollback();
        }
    }

    public function startRow(): int
    {
        return 2; // If you want to skip the first row (heading row)
    }
}
