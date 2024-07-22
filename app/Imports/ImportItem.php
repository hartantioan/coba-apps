<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Grade;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\ItemShading;
use App\Models\ItemStock;
use App\Models\ItemUnit;
use App\Models\Pallet;
use App\Models\Pattern;
use App\Models\Size;
use App\Models\Type;
use App\Models\Unit;
use App\Models\Variety;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Events\BeforeImport;

class ImportItem implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            0 => new handleItemSheet(),
            1 => new handleConversionSheet(),
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
                $check = Item::where('code',$row['code'])->first();
                if($check){
                    $check->itemUnit()->delete();
                }
                $item_group_code = explode('#',$row['item_group'])[0];
                $item_group_id = ItemGroup::where('code',$item_group_code)->first();
                
                $item_unit_code = explode('#',$row['unit'])[0];
                $item_unit_id= Unit::where('code',$item_unit_code)->first();
                $type = Type::where('code',explode('#',$row['type_fg'])[0])->first();
                $size = Size::where('code',explode('#',$row['size_fg'])[0])->first();
                $variety = Variety::where('code',explode('#',$row['variety_fg'])[0])->first();
                $pattern = Pattern::where('code',explode('#',$row['pattern_fg'])[0])->first();
                $pallet = Pallet::where('code',explode('#',$row['pallet_fg'])[0])->first();
                $grade = Grade::where('code',explode('#',$row['grade_fg'])[0])->first();
                $brand = Brand::where('code',explode('#',$row['brand_fg'])[0])->first();
                if(!$check){
                    $query = Item::create([
                        'code' => $row['code'],
                        'name' => $row['name'],
                        'other_name' => $row['other_name'],
                        'item_group_id' =>$item_group_id->id,
                        'uom_unit' => $item_unit_id->id,
                        'tolerance_gr' => $row['toleransi_gr'],
                        'is_inventory_item' => $row['is_invent_item'],
                        'is_sales_item' => $row['is_sales_item'],
                        'is_purchase_item' => $row['is_purchase'],
                        'is_service' => $row['is_service'],
                        'is_production' => $row['is_production'],
                        'is_quality_check' => $row['is_quality_check'],
                        'is_hide_supplier' => $row['is_top_secret'],
                        'type_id' => $type ? $type->id : NULL,
                        'size_id' => $size ? $size->id : NULL,
                        'variety_id' => $variety ? $variety->id : NULL,
                        'pattern_id' => $pattern ? $pattern->id : NULL,
                        'pallet_id' => $pallet ? $pallet->id : NULL,
                        'grade' => $grade ? $grade->id : NULL,
                        'brand_id' => $brand ? $brand->id : NULL,
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
        
                    activity()
                        ->performedOn(new Item())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit from excel item data.');
                        
                    DB::commit();
                }else{

                    $check->name = $row['name'];
                    $check->other_name=$row['other_name'];
                    $check->item_group_id=$item_group_id->id;
                    $check->uom_unit=$item_unit_id->id;
                    $check->tolerance_gr=$row['toleransi_gr'];
                    $check->is_inventory_item=$row['is_invent_item'];
                    $check->is_sales_item=$row['is_sales_item'];
                    $check->is_purchase_item=$row['is_purchase'];
                    $check->is_service=$row['is_service'];
                    $check->is_hide_supplier = $row['is_production'];
                    $check->type_id= $type ? $type->id : NULL;
                    $check->size_id = $size ? $size->id : NULL;
                    $check->variety_id = $variety ? $variety->id : NULL;
                    $check->pattern_id = $pattern ? $pattern->id : NULL;
                    $check->pallet_id = $pallet ? $pallet->id : NULL;
                    $check->grade = $grade ? $grade->id : NULL;
                    $check->brand_id = $brand ? $brand->id : NULL;
                    $check->note =  $row['note'];
                    $check->min_stock = $row['min_stock'];
                    $check->max_stock = $ $row['max_stock'];
                    $check->save();                    
                }
            }
            else{
                return null;
            }  
            DB::commit();
        }catch (\Exception $e) {
            DB::rollback();
           
        }
    }
    
    public function startRow(): int
    {
        return 2; // If you want to skip the first row (heading row)
    }
}

class handleConversionSheet implements OnEachRow, WithHeadingRow{
    
    public static function beforeImport(BeforeImport $event)
    {
        $worksheet = $event->getReader()->getActiveSheet();
        $rowCount = $worksheet->getHighestRow();

        if ($rowCount < 2) {
            return null;
        }
    }
    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
            $check = null;
            $row = $row->toArray();
            if(isset($row['item_code']) && $row['item_code']){
                $check = Item::where('code',$row['item_code'])->first();
                $code_unit = explode('#', $row['unit'])[0];
                $unit = Unit::where('code',$code_unit)->first();
                if ($check) {
                    $x=ItemUnit::create([
                        'item_id'       => $check->id,
                        'unit_id'       => $unit->id,
                        'conversion'    => $row['konversi'],
                        'is_buy_unit'   => $row['beli'],
                        'is_sell_unit'   => $row['jual'],
                        'is_default'    => $row['default'],
                    ]);
                }
            }
            else{
                return null;
            }
            DB::commit();
        }catch (\Exception $e) {
            DB::rollback();
           
        }  
       
    }
    public function startRow(): int
    {
        return 2; // If you want to skip the first row (heading row)
    }
}
