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
            'user_id' => $row['user_id'],
            'place_id' => $row['place_id'],
            'department_id' => $row['department_id'],
            'item_id' => $row['item_id'],
            'name' => $row['name'],
            'date_start' =>($row['date_start']),
            'date_end' =>($row['date_end']),
            'nominal' => $row['nominal'],
            'method' => $row['method'],
            'cost_coa_id' => $row['cost_coa_id'],
            'note' => $row['note'],
            'status' => $row['status']
        ]);
    }
    public function transformDate($value, $format = 'Y-m-d')
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
    }

    public function rules(): array
    {
        return [
            '*.code' => 'required',
            '*.user_id' => 'required',
            '*.place_id' => 'required',
            '*.department_id' => 'required',
            '*.item_id' => 'required',
            '*.date_start' => 'required|date_format:Y-m-d',
            '*.date_end' => 'required|date_format:Y-m-d|after_or_equal:date_start',
            '*.nominal' => 'required|numeric',
            '*.method' => 'required',
            '*.cost_coa_id' => 'required',
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