<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use App\Exceptions\RowImportException;
use App\Models\Brand;
use App\Models\User;
use App\Models\UserBrand;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportUserBrand implements WithMultipleSheets
{
    public function sheets(): array
    {

        UserBrand::truncate();
        return [
            0 => new handleUserBrand(),

        ];
    }
}

class handleUserBrand implements   OnEachRow, WithHeadingRow
{
    public $error = null;

    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {

            // $customer_code = explode('#', $row['customer'])[0];
            // $customer = User::where('employee_no',$customer_code)->first();

            $customer = User::where('employee_no',$row['customer_code'])->first();
            $brand_code = explode('#', $row['brand_code'])[0];
            $brand = Brand::where('code',$brand_code)->first();


            if(!$customer && $this->error ==null){
                $this->error = "Customer.";
            }elseif(!$brand && $this->error ==null){
                $this->error = "BRAND.";
            }

            if(!$this->error){
                $query = UserBrand::create([

                    'user_id'           => session('bo_id'),
                    'account_id'        => $customer->id,
                    'brand_id'          => $brand->id,
                ]);

            }else{

                $sheet='Header';
                throw new RowImportException("data kurang lengkap", $row->getIndex(),$this->error,$sheet);
            }


            DB::commit();
        }catch (\Exception $e) {
            DB::rollback();
            $sheet='Header';
            throw new RowImportException($e->getMessage(), $row->getIndex(),$this->error,$sheet);
        }
    }

    public function startRow(): int
    {
        return 2; // If you want to skip the first row (heading row)
    }
}
