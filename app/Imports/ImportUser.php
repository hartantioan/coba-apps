<?php

namespace App\Imports;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use App\Models\User;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class ImportUser implements ToModel,WithHeadingRow, WithValidation,WithBatchInserts
{
    public function model(array $row)
    {
        return new User([
            'employee_no'       => User::generateCode($row['type']),
            'name'              => $row['name'],
            'password'          => bcrypt($row['password']),
            'username'          => $row['username'],
            'phone'             => $row['phone'],
            'address'           => $row['address'],
            'province_id'       => $row['province_id'],
            'city_id'           => $row['city_id'],
            'id_card'           => $row['id_card'],
            'id_card_address'   => $row['id_card'],
            'type'              => $row['type'],
            'group_id'          => $row['type'],
            'status'            => $row['status'],
            'company_id'        => $row['company_id'],
            'place_id'          => $row['place_id'],
            'department_id'     => $row['department_id'],
            'position_id'       => $row['position_id'],
            'tax_id'            => $row['tax_id'],
            'tax_name'          => $row['tax_name'],
            'tax_address'       => $row['tax_address'],
            'pic'               => $row['pic'],
            'pic_no'            => $row['pic_no'],
            'office_no'         => $row['office_no'],
            'email'             => $row['email'],
            'deposit'           => $row['deposit'],
            'limit_credit'      => $row['limit_credit'],
            'top'               => $row['top'],
            'top_internal'      => $row['top_internal'],
            'tolerance_gr'      => $row['tolerance_gr'],
            'gender'            => $row['gender'],
            'married_status'    => $row['married_status'],
            'married_date'      => $row['married_date'],
            'children'          => $row['children'],
            'country_id'        => $row['country_id'],
        ]);
    }
    public function rules(): array
    {
        return [
            '*.name'            => 'required',
            '*.password'        => 'nullable',
            '*.username'        => 'required|string|unique:users,username',
            '*.phone'           => 'required|string',
            '*.address'         => 'required|string',
            '*.province_id'     => 'required|integer',
            '*.city_id'         => 'required|integer',
            '*.id_card'         => 'nullable',
            '*.id_card_address' => 'nullable',
            '*.type'            => 'required|integer',
            '*.group_id'        => 'required|integer',
            '*.status'          => 'required|integer',
            '*.company_id'      => 'required|integer',
            '*.place_id'        => 'required|integer',
            '*.department_id'   => 'required|integer',
            '*.position_id'     => 'required|integer',
            '*.tax_id'          => 'required|string',
            '*.tax_name'        => 'required|string',
            '*.tax_address'     => 'required|string',
            '*.pic'             => 'nullable',
            '*.pic_no'          => 'nullable',
            '*.office_no'       => 'nullable',
            '*.email'           => 'required|string',
            '*.deposit'         => 'nullable',
            '*.limit_credit'    => 'nullable',
            '*.top'             => 'nullable',
            '*.top_internal'    => 'nullable',
            '*.tolerance_gr'    => 'nullable',
            '*.gender'          => 'required|integer',
            '*.married_status'  => 'nullable',
            '*.married_date'    => 'nullable',
            '*.children'        => 'nullable',
            '*.country_id'      => 'required|integer',
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