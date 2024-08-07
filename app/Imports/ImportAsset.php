<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\AssetGroup;
use App\Models\HardwareItem;
use App\Models\Place;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportAsset implements OnEachRow, WithHeadingRow, WithValidation, WithBatchInserts,WithMultipleSheets
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
        $asset_group_code=explode('#',$row['asset_group_id'])[0];
        $asset_group = AssetGroup::where('code',$asset_group_code)->first();
        $method =explode('#',$row['method'])[0];
        $inventaris_code =explode('#',$row['inventaris'])[0];
        $inventaris = HardwareItem::where('code',$inventaris_code)->first() ?? null;
        $row = $row->toArray();
        $query = Asset::create([
            'code' => $row['code'],
            'user_id' => session('bo_id'),
            'place_id' => $place->id,
            'name' => $row['name'],
            'asset_group_id' =>$asset_group->id,
            'method' => $method,
            'note' => $row['note'],
            'hardware_item_id' => $inventaris->id,
            'status' => '1',
        ]);
        activity()
            ->performedOn(new Asset())
            ->causedBy(session('bo_id'))
            ->withProperties($query)
            ->log('From excel asset to database.');
    }

    public function rules(): array
    {
        return [
            '*.code'            => 'required|unique:assets,code',
            '*.place_id'        => 'required',
            '*.name'            => 'required',
            '*.asset_group_id'  => 'required',
            '*.method'          => 'required',
            '*.note'          => 'required',
            '*.status'          => 'nullable',
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