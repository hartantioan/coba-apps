<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\AssetGroup;
use App\Models\Place;
use App\Models\Resource;
use App\Models\ResourceGroup;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportResource implements OnEachRow, WithHeadingRow, WithValidation, WithBatchInserts,WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }
    public function onRow(Row $row)
    {
        
        $place = Place::where('code',$row['place_id'])->first();
        $resource_group_code= explode('#',$row['resource_group_id'])[0];
        $resource_group = ResourceGroup::where('code',$resource_group_code)->first();
        $uom = explode('#', $row['uom_unit'])[0];
        $uom_unit = Unit::where('code',$uom)->first();
        $row = $row->toArray();
        $query = Resource::create([
            'code'              => $row['code'],
            'name'              => $row['name'],
            'other_name'        => $row['other_name'],
            'resource_group_id' => $resource_group->id,
            'uom_unit'          => $uom_unit->id,
            'place_id'          => $place->id,
            'qty'               => $row['qty'],
            'cost'              => $row['cost'],
            'status'            => '1',
        ]);
        activity()
            ->performedOn(new Resource())
            ->causedBy(session('bo_id'))
            ->withProperties($query)
            ->log('From excel resource to database.');
    }

    public function rules(): array
    {
        return [
            '*.code'                => 'required|unique:resources,code',
            '*.place_id'            => 'required',
            '*.name'                => 'required',
            '*.resource_group_id'   => 'required',
            '*.uom_unit'            => 'required',
            '*.qty'                 => 'required',
            '*.cost'                => 'nullable',
        ];
    }

    // public function onFailure(Failure ...$failures)
    // {
    //     $errors = [];

    //     foreach ($failures as $failure) {
    //         $errors[] = [
    //             'row' => $failure->row(),
    //             'attribute' => $failure->attribute(),
    //             'errors' => $failure->errors(),
    //             'values' => $failure->values(),
    //         ];
    //     }

    //     throw new ValidationException(null, null, $errors);
    // }

    public function batchSize(): int
    {
        return 1000;
    }

}