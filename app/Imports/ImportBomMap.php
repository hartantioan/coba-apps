<?php

namespace App\Imports;

use App\Models\BomMap;
use App\Models\Bom;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportBomMap implements OnEachRow, WithHeadingRow, WithValidation, WithBatchInserts,WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }
    public function onRow(Row $row)
    {
        $child = Bom::where('code',$row['code_bom_child'])->first();
        $parent = Bom::where('code',$row['code_bom_parent'])->first();
        if($child && $parent){
            $cek = BomMap::where('child_id',$child->id)->where('parent_id',$parent->id)->first();
            if($cek){

            }else{
                BomMap::create([
                    'parent_id' => $parent->id,
                    'child_id'  => $child->id,
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            '*.code_bom_child'     => 'required',
            '*.code_bom_parent'    => 'required',
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

}