<?php

namespace App\Imports;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Models\DeliveryCost;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class ImportDeliveryCost implements ToModel,WithHeadingRow, WithValidation,WithBatchInserts
{
    public function model(array $row)
    {
        /* $date_from = intval($row['valid_from']); */
        return new DeliveryCost([
            'code' => $row['code'],
            'user_id' => session('bo_id'),
            'account_id' => $row['account_id'],
            'name' => $row['name'],
            'valid_from' => Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['valid_from'])),
            'valid_to' => Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['valid_to'])),
            'from_city_id' => $row['from_city_id'],
            'from_subdistrict_id' => $row['from_subdistrict_id'],
            'to_city_id' => $row['to_city_id'],
            'to_subdistrict_id' => $row['to_subdistrict_id'],
            'tonnage' => $row['tonnage'],
            'nominal' => $row['nominal'],
            'status'=> $row['status'],
        ]);
    }
    public function rules(): array
    {
        return [
            '*.code' => 'required|unique:delivery_costs,code',
            '*.account_id' => 'nullable',
            '*.name' => 'required|string',
            '*.valid_from' => 'required',
            '*.valid_to' => 'required',
            '*.from_city_id' => 'required|numeric',
            '*.from_subdistrict_id' => 'required|numeric',
            '*.to_city_id' => 'required|numeric',
            '*.to_subdistrict_id' => 'required|numeric',
            '*.tonnage' => 'required|numeric',
            '*.nominal' => 'required|numeric',
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