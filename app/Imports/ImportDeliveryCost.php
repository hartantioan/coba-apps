<?php

namespace App\Imports;

use App\Models\DeliveryCost;


use App\Exceptions\RowImportException;

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
class ImportDeliveryCost implements WithMultipleSheets
{
    protected $error = '';
    protected $temp = [];

    public function sheets(): array
    {
        return [
            0 => new handleDC(),
        ];
    }
}
class handleDC implements OnEachRow, WithHeadingRow
{
    public $error = null;
    protected $temp;


    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
            if (isset($row['code']) && $row['code']) {
               

                $code = $row['code'];$name = $row['name'];$tonnage = $row['kg_price'];$ritage = $row['ritage_price'];$tonnage_weight = $row['tonage_weight'];
                $account = User::where('employee_no', explode('#', $row['supplier_ekspedisi'])[0])->first();
                
                $city = str_replace(',', '.', explode('#', $row['from_city'])[0]);
                $city_id_from = Region::where('code',$city)->first()->id; 
                $from_sub_district = str_replace(',', '.', explode('#', $row['from_subdistrict'])[0]);
                $sub_district_id_from = Region::where('code',$from_sub_district)->first()->id;

                $tocity  = str_replace(',', '.', explode('#', $row['to_city'])[0]);
                $city_id_to = Region::where('code',$tocity)->first()->id; 
                $to_sub_district  = str_replace(',', '.', explode('#', $row['to_subdistrict'])[0]);
                $sub_district_id_to = Region::where('code',$to_sub_district)->first()->id;
                
                $transport = Transportation::where('code', explode('#', $row['transport'])[0])->first();
                if(!$account && $this->error ==null){
                    $this->error = "Ekspedisi dan Supplier.";
                }elseif(!$code && $this->error ==null){
                    $this->error = "Kode.";
                }elseif(!$name && $this->error ==null){
                    $this->error = "Nama";
                }elseif(!$city_id_to && $this->error ==null){
                    $this->error = "Kota Tujuan";
                }elseif(!$sub_district_id_to && $this->error ==null){
                    $this->error = "Kecamatan Tujuan";
                }elseif(!$city_id_from && $this->error ==null){
                    $this->error = "Kota Awal";
                }elseif(!$sub_district_id_from && $this->error ==null){
                    $this->error = "Kecamatan Awal";
                }elseif(!$tonnage && $this->error ==null && $tonnage != '0'){
                    $this->error = "tonnage";
                }elseif(!$ritage && $this->error ==null && $ritage != '0'){
                    $this->error = "ritage";
                }elseif(!$tonnage_weight && $this->error ==null){
                    $this->error = "berat tonase";
                }elseif(!$transport && $this->error ==null){
                    $this->error = "Transportasi";
                }

                $dateTime1 = DateTime::createFromFormat('U', ($row['valid_from'] - 25569) * 86400);
                $dateFormatted1 = $dateTime1->format('Y/m/d');
                $dateTime2 = DateTime::createFromFormat('U', ($row['valid_to'] - 25569) * 86400);
                $dateFormatted2 = $dateTime2->format('Y/m/d');
              
                    $query = DeliveryCost::create([
                        'code' => $row['code'],
                        'user_id' => session('bo_id'),
                        'account_id' => $account->id,
                        'name' => $row['name'],
                        'valid_from' => $dateFormatted1,
                        'valid_to' => $dateFormatted2,
                        'from_city_id' => $city_id_from,
                        'from_subdistrict_id' => $sub_district_id_from,
                        'to_city_id' => $city_id_to,
                        'to_subdistrict_id' => $sub_district_id_to,
                        'transportation_id' => $transport->id,
                        'tonnage' => $tonnage,
                        'ritage' => $ritage,
                        'qty_tonnage' => $tonnage_weight,
                        'status'=> 1
                    ]);
                
                    activity()
                        ->performedOn(new DeliveryCost())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit from excel Delivery cost data.');
               
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