<?php

namespace App\Imports;

use App\Models\FgGroup;
use App\Models\Item;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportFgGroup implements OnEachRow, WithHeadingRow, WithValidation, WithBatchInserts,WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }
    public function onRow(Row $row)
    {
        $item = Item::where('code',$row['code_item_child'])->first();
        $parent = Item::where('code',$row['code_item_parent'])->first();
        if($item && $parent){
            $cek = FgGroup::where('item_id',$item->id)->first();
            if($cek){

            }else{
                FgGroup::create([
                    'parent_id' => $parent->id,
                    'item_id'   => $item->id,
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            '*.code_item_child'     => 'required',
            '*.code_item_parent'    => 'required',
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

}