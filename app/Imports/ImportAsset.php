<?php

namespace App\Imports;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Models\Asset;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
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
            'cost_coa_id' => $row['cost_coa_id'],
            'note' => $row['note'],
            'status' => $row['status']
        ]);
    }
    /* public function transformDate($value, $format = 'Y-m-d')
    {
        if(!is_numeric($value)){
            info($value);
            return $value;
        }else{
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        }
    }

    public function prepareForValidation($data, $index)
    {
        $data['date_start'] = $this->transformDate($data['date_start']);
        $data['date_end'] = $this->transformDate($data['date_end']);
        return $data;
    } */

    public function rules(): array
    {
        return [
            '*.code'            => 'required|unique:assets,code',
            '*.place_id'        => 'required',
            '*.name'            => 'required',
            '*.asset_group_id'  => 'required',
            '*.method'          => 'required',
            '*.cost_coa_id'     => 'required',
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