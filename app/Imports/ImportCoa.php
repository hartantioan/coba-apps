<?php

namespace App\Imports;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Models\Coa;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class ImportCoa implements ToModel,WithHeadingRow, WithValidation,WithBatchInserts
{
    public function model(array $row)
    {
        return new Coa([
            'id' => $row['id'],
            'code' => $row['code'],
            'name'=> $row['name'],
            'company_id'=> $row['company_id'],
            'parent_id'=> $row['parent_id'],
            'level'=> $row['level'],
            'is_confidential'=> $row['is_confidential'],
            'is_control_account'=> $row['is_control_account'],
            'is_cash_account'=> $row['is_cash_account'],
            'status'=> $row['status'],
        ]);
    }
    public function rules(): array
    {
        return [
            '*.id'  => 'required|numeric|unique:coas,id',
            '*.code' => 'required',
            '*.name' => 'required|string',
            '*.company_id' => 'required|numeric',
            '*.parent_id' => 'nullable',
            '*.level' => 'required|integer',
            '*.is_confidential' => 'nullable',
            '*.is_control_account' => 'nullable',
            '*.is_cash_account' => 'nullable',
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