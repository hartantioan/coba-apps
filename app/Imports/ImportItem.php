<?php

namespace App\Imports;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Models\Item;

use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;

class ImportItem implements OnEachRow, WithHeadingRow, WithValidation, WithBatchInserts
{

    public function onRow(Row $row)
    {
        $row = $row->toArray();

        $query = Item::create([
            'code' => $row['code'],
            'name' => $row['name'],
            'item_group_id' => $row['item_group_id'],
            'uom_unit' => $row['uom_unit'],
            'buy_unit' => $row['buy_unit'],
            'buy_convert' => $row['buy_convert'],
            'sell_unit' => $row['sell_unit'],
            'sell_convert' => $row['sell_convert'],
            'pallet_unit' => $row['pallet_unit'],
            'pallet_convert' => $row['pallet_convert'],
            'production_unit' => $row['production_unit'],
            'production_convert' => $row['production_convert'],
            'tolerance_gr' => $row['tolerance_gr'],
            'is_inventory_item' => $row['is_inventory_item'],
            'is_sales_item' => $row['is_sales_item'],
            'is_purchase_item' => $row['is_purchase_item'],
            'is_service' => $row['is_service'],
            'note' => $row['note'],
            'status' => $row['status']
        ]);
    }

    public function rules(): array
    {
        return [
            '*.code' => 'required|unique:items,code',
            '*.name' => 'required|string',
            '*.item_group_id' => 'required|integer',
            '*.uom_unit' => 'required',
            '*.buy_unit' => 'required',
            '*.buy_convert' => 'required|numeric',
            '*.sell_unit' => 'required',
            '*.sell_convert' => 'required|numeric',
            '*.pallet_unit' => 'required',
            '*.pallet_convert' => 'required|numeric',
            '*.production_unit' => 'required',
            '*.production_convert' => 'required|numeric',
            '*.tolerance_gr' => 'required|numeric',
            '*.is_inventory_item' => 'nullable',
            '*.is_sales_item' => 'nullable',
            '*.is_purchase_item' => 'nullable',
            '*.is_service' => 'nullable',
            '*.note' => 'nullable',
            '*.status' => 'required',
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
}