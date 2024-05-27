<?php

namespace App\Imports;

use App\Models\Pattern;
use App\Models\Brand;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportPattern implements OnEachRow, WithHeadingRow, WithValidation, WithBatchInserts,WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }
    public function onRow(Row $row)
    {
        $row = $row->toArray();
        $brand_code= explode('#',$row['brand_id'])[0];
        $brand = Brand::where('code',$brand_code)->first();
        $nameNoSpace = str_replace(' ','',$row['name']);
        $cek = Pattern::whereRaw("REPLACE(name,' ','') = '$nameNoSpace'")->where('status','1')->first();
        $cekCode = Pattern::where('code',$row['code'])->where('status','1')->first();

        if(!$cek && !$cekCode && $brand){
            $query = Pattern::create([
                'brand_id'  => $brand->id,
                'code'      => $row['code'],
                'name'      => $row['name'],
                'status'    => '1',
            ]);
            activity()
                ->performedOn(new Brand())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('From excel brand to database.');
        }
    }

    public function rules(): array
    {
        return [
            '*.code'                => 'required',
            '*.brand_id'            => 'required',
            '*.name'                => 'required',
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

}