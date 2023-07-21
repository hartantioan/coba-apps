<?php

namespace App\Imports;

use App\Models\Asset;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class ImportAsset implements ToModel, WithHeadingRow, WithValidation,WithBatchInserts
{
    public function model(array $row)
    {
        return new Asset([
            'code' => $row['code'],
            'user_id' => session('bo_id'),
            'place_id' => $row['place_id'],
            'name' => $row['name'],
            'asset_group_id' => $row['asset_group_id'],
            'method' => $row['method'],
            'note' => $row['note'],
            'status' => $row['status']
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
            '*.status'          => 'required',
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