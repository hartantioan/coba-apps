<?php

namespace App\Imports;

use App\Models\BottomPrice;

use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class ImportBottomPrice implements ToModel, WithHeadingRow, WithValidation,WithBatchInserts
{
    public function model(array $row)
    {
        return new BottomPrice([
            'code'      => Str::random(30),
            'user_id'   => session('bo_id'),
            'item_id'   => $row['item_id'],
            'place_id'  => $row['place_id'],
            'nominal'   => $row['nominal'],
        ]);
    }

    public function rules(): array
    {
        return [
            '*.item_id'         => 'required',
            '*.place_id'        => 'required',
            '*.nominal'         => 'required|numeric',
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