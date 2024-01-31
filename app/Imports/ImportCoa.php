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
            'id' => intval($row['id']),
            'code' => $row['code'],
            'prefix' => $row['prefix'],
            'name'=> $row['name'],
            'company_id'=> $row['company_id'],
            'parent_id'=> $row['parent_id'],
            'level'=> $row['level'],
            'is_cash_account'=> $row['is_cash_account'],
            'is_hidden'=> $row['is_hidden'],
            'show_journal'=> $row['show_journal'],
            'bp_journal'=> $row['bp_journal'],
            'status'=> $row['status'],
        ]);
    }
    public function rules(): array
    {
        return [
            '*.id'  => 'required|unique:coas,id',
            '*.code' => 'required',
            '*.prefix' => 'nullable',
            '*.name' => 'required|string',
            '*.company_id' => 'required|numeric',
            '*.parent_id' => 'nullable',
            '*.level' => 'required|integer',
            '*.is_cash_account' => 'nullable',
            '*.is_hidden' => 'nullable',
            '*.show_journal' => 'nullable',
            '*.bp_journal' => 'nullable',
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