<?php

namespace App\Imports;

use App\Exceptions\RowImportException;
use App\Models\Brand;
use App\Models\CustomerDiscount;
use App\Models\Region;
use App\Models\Transportation;
use App\Models\User;

use DateTime;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class ImportCustomerDiscount implements WithMultipleSheets
{
    protected $error = '';
    protected $temp = [];

    public function sheets(): array
    {

        activity()
        ->performedOn(new CustomerDiscount())
        ->causedBy(session('bo_id'))
        ->withProperties('')
        ->log('Add / edit from excel customer discount data.');

        return [
            0 => new handleCustomerDiscount(),
        ];
    }
}

class handleCustomerDiscount implements OnEachRow, WithHeadingRow
{
    public $error = null;
    protected $temp;


    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
            if (isset($row['code']) && $row['code']) {
                $code = $row['code'];$disc1 = $row['disc_1'];
                $account = User::where('employee_no', explode('#', $row['customer'])[0])->first();
                
                $city = str_replace(',', '.', explode('#', $row['kabupaten_kota'])[0]);
                $city_id = Region::where('code',$city)->first()->id;
                $brand = Brand::where('code', explode('#', $row['brand'])[0])->first(); 
                $type = explode('#', $row['varian_item'])[0];
                $payment_type = explode('#', $row['payment_type'])[0];
                if(!$account && $this->error ==null){
                    $this->error = "customer";
                }elseif(!$code && $this->error ==null){
                    $this->error = "Kode.";
                }elseif(!$brand && $this->error ==null){
                    $this->error = "brand";
                }elseif(!$city_id && $this->error ==null){
                    $this->error = "Kota ";
                }elseif(!$disc1 && $this->error ==null){
                    $this->error = "Diskon 1";
                }elseif(!$type && $this->error ==null){
                    $this->error = "varian item";
                }elseif(!$payment_type && $this->error ==null){
                    $this->error = "tipe pembayaran";
                }
              
                    $query = CustomerDiscount::create([
                        'code' => $row['code'],
                        'user_id' => session('bo_id'),
                        'account_id' => $account->id,
                        'city_id' => $city_id,
                        'brand_id' => $brand->id,
                        'type_id' => $type,
                        'payment_type' => $payment_type,
                        'disc1' => $disc1,
                        'disc2' => $row['disc_2'],
                        'disc3' => $row['disc_3'],
                        'post_date' => now(),
                        'status'=> 1
                    ]);
                
                   
            }else{
                return null;
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
