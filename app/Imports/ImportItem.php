<?php

namespace App\Imports;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\ItemShading;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportItem implements OnEachRow, WithHeadingRow, WithValidation, WithBatchInserts,WithMultipleSheets
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
      
        $item_group_code = explode('#',$row['item_group_id'])[0];
        $item_group_id=ItemGroup::where('code',$item_group_code)->first();
        
        $item_unit_code = explode('#',$row['uom_unit'])[0];
        $item_unit_id=Unit::where('code',$item_unit_code)->first();
        
        $query = Item::create([
            'code' => $row['code'],
            'name' => $row['name'],
            'other_name' => $row['other_name'],
            'item_group_id' =>$item_group_id->id,
            'uom_unit' => $item_unit_id->id,
            'tolerance_gr' => $row['tolerance_gr'],
            'is_inventory_item' => $row['is_inventory_item'],
            'is_sales_item' => $row['is_sales_item'],
            'is_purchase_item' => $row['is_purchase_item'],
            'is_service' => $row['is_service'],
            'note' => $row['note'],
            'status' => '1',
            'shading' => $row['shading'],
        ]);

        if($row['shading']){
            $arrShading = explode('|',$row['shading']);
            foreach($arrShading as $rowshading){
                ItemShading::create([
                    'item_id'   => $query->id,
                    'code'      => $rowshading,
                ]);
            }
        }
    }

    public function rules(): array
    {
      
        return [
            '*.code' => 'required|unique:items,code',
            '*.name' => 'required|string',
            '*.other_name' => 'nullable',
            '*.item_group_id' => 'required',
            '*.uom_unit' => 'required',
            '*.tolerance_gr' => 'nullable',
            '*.is_inventory_item' => 'nullable',
            '*.is_sales_item' => 'nullable',
            '*.is_purchase_item' => 'nullable',
            '*.is_service' => 'nullable',
            '*.note' => 'nullable',
            '*.min_stock' => 'nullable|required_with:max_stock', // Allow empty, but if min_stock is present, max_stock must also be present
            '*.max_stock' => 'nullable|required_with:min_stock', // Allow empty, but if max_stock is present, min_stock must also be present
            '*.shading'   => 'nullable',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        $errors = [];

        foreach ($failures as $failure) {
            $errors[] = [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values(),
            ];
        }

        throw new ValidationException(null, null, $errors);
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function onSheetStart(int $sheetIndex)
    {
        // Check if the current sheet is the target sheet
        $sheetName = $row->getDelegate()->getActiveSheet()->getTitle();

        if ($sheetName !== $this->targetSheetName) {
            return false; // Skip this sheet
        }

        return true; // Process rows for the target sheet
    }
}