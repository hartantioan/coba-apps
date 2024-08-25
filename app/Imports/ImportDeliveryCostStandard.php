<?php

namespace App\Imports;

use App\Exceptions\RowImportException;
use App\Http\Controllers\MasterData\DeliveryCostController;
use App\Models\DeliveryCostStandard as ModelsDeliveryCostStandard;
use App\Models\Region;
use App\Models\User;

use DateTime;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ImportDeliveryCostStandard implements WithMultipleSheets
{
    protected $error = '';
    protected $temp = [];

    public function sheets(): array
    {
        return [
            0 => new deliveryCostStandard(),
        ];
    }
}

class deliveryCostStandard implements OnEachRow, WithHeadingRow
{
    public $error = null;
    protected $temp;


    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
            if (isset($row['kota']) && $row['kota']) {
                $price = $row['harga']; $note = $row['keterangan'];
                $city = str_replace(',', '.', explode('#', $row['kota'])[0]);
                $city_id = Region::where('code',$city)->first()->id;
                $district = str_replace(',', '.', explode('#', $row['kecamatan'])[0]);
                $district_id = Region::where('code',$district)->first()->id;
                $categoryTransportation = explode('#', $row['kategori_transportasi'])[0];
                $dateTime1 = DateTime::createFromFormat('U', ($row['tanggal_start'] - 25569) * 86400);
                $dateFormatted1 = $dateTime1->format('Y/m/d');
                $dateTime2 = DateTime::createFromFormat('U', ($row['tanggal_selesai'] - 25569) * 86400);
                $dateFormatted2 = $dateTime2->format('Y/m/d');
                if(!$categoryTransportation && $this->error ==null){
                    $this->error = "Kategori Transportasi";
                }elseif(!$price && $this->error ==null){
                    $this->error = "Harga";
                }elseif(!$note && $this->error ==null){
                    $this->error = "Keterangan ";
                }elseif(!$city && $this->error ==null){
                    $this->error = "Kota";
                }elseif(!$district && $this->error ==null){
                    $this->error = "kecamatan";
                }
              
                    $query = ModelsDeliveryCostStandard::create([
                        'code' => Str::random(10),
                        'user_id' => session('bo_id'),
                        'city_id' => $city_id,
                        'district_id' => $district_id,
                        'category_transportation' => $categoryTransportation,
                        'price' => $price,
                        'start_date' => $dateFormatted1,
                        'end_date' => $dateFormatted2,
                        'note' => $note,
                        'status'=> 1
                    ]);
                
                    activity()
                        ->performedOn(new ModelsDeliveryCostStandard())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit from excel customer discount data.');
               
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
