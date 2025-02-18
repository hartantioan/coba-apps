<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\AssetGroup;
use App\Models\CostDistribution;
use App\Models\Division;
use App\Models\HardwareItem;
use App\Models\Line;
use App\Models\Machine;
use App\Models\Place;
use App\Models\Project;
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
        activity()
        ->performedOn(new Asset())
        ->causedBy(session('bo_id'))
        ->withProperties(null)
        ->log('From excel asset to database.');
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
        $cost=explode('#',$row['cost_distribution_id'])[0];
        $costdata = CostDistribution::where('code',$cost)->first() ?? null;
        $line=explode('#',$row['line_id'])[0];
        $linedata = Line::where('code',operator: $line)->first() ?? null;
        $machine=explode('#',$row['machine_id'])[0];
        $machinedata = Machine::where('code',operator: $machine)->first() ?? null;
        $division=explode('#',$row['division_id'])[0];
        $divisiondata = Division::where('code',operator: $division)->first() ?? null;
        $project=explode('#',$row['project_id'])[0];
        $projectdata = Project::where('code',operator: $project)->first() ?? null;
        $row = $row->toArray();
        $query = Asset::create([
            'code' => $row['code'],
            'user_id' => session('bo_id'),
            'place_id' => $place->id,
            'name' => $row['name'],
            'asset_group_id' =>$asset_group->id,
            'method' => $method,
            'note' => $row['note'],
            'hardware_item_id' => $inventaris->id ?? null ,
            'cost_distribution_id' => $costdata->id ?? null ,
            'line_id' => $linedata->id ?? null ,
            'machine_id' => $machinedata->id ?? null ,
            'division_id' => $divisiondata->id ?? null ,
            'project_id' => $projectdata->id ?? null ,
            'status' => '1',
        ]);
        
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