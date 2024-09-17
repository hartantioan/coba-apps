<?php

namespace App\Imports;

use App\Exceptions\RowImportException;
use App\Http\Controllers\MasterData\DeliveryCostController;
use App\Models\DeliveryCostStandard as ModelsDeliveryCostStandard;
use App\Models\Region;
use App\Models\Transportation;
use App\Models\Type;
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
        activity()
        ->performedOn(new ModelsDeliveryCostStandard())
        ->causedBy(session('bo_id'))
        ->withProperties('')
        ->log('Add / edit from excel customer discount data.');

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
        $check = null;
        DB::beginTransaction();
        try {
            if (isset($row['kota']) && $row['kota']) {
                info('masuk');
                if(isset($row['code']) && $row['code']){
                    $check = ModelsDeliveryCostStandard::where('code',$row['code'])->first();
                    
                }
                $price = $row['harga']; $note = $row['keterangan'];
                $city = str_replace(',', '.', explode('#', $row['kota'])[0]);
                $city_id = Region::where('code',$city)->first()->id;
                $district = str_replace(',', '.', explode('#', $row['kecamatan'])[0]);
                $tipe_code = str_replace(',', '.', explode('#', $row['tipe'])[0]);
                $firstFiveChars = substr($district, 0, 5);
                if($firstFiveChars != $city){
                    $this->error = "kecamatan bukan dari kota yang sama";
                }
                $district_id = Region::where('code',$district)->first()->id;
                $categoryTransportation = explode('#', $row['transportasi'])[0];
                $transportation_id = Transportation::where('code',$categoryTransportation)->first()->id;
                $tipe = Type::where('code',$tipe_code)->first()->id;
                $plant = explode('#', $row['plant'])[0];
                $dateTime1 = DateTime::createFromFormat('U', ($row['tanggal_start'] - 25569) * 86400);
                $dateFormatted1 = $dateTime1->format('Y/m/d');
                $dateTime2 = DateTime::createFromFormat('U', ($row['tanggal_selesai'] - 25569) * 86400);
                $dateFormatted2 = $dateTime2->format('Y/m/d');
                if(!$categoryTransportation && $this->error ==null){
                    $this->error = "Kategori Transportasi";
                }elseif(!$price && $this->error ==null && $price != '0'){
                    
                    $this->error = "Harga";
                }elseif(!$note && $this->error ==null){
                    $this->error = "Keterangan ";
                }elseif(!$city && $this->error ==null){
                    $this->error = "Kota";
                }elseif(!$district && $this->error ==null){
                    $this->error = "kecamatan";
                }elseif(!$tipe && $this->error ==null){
                    $this->error = "Tipe";
                }
                if(!$this->error){
                    if($check){
                        $query = $check;
                        $check->code = $row['code'];
                        $check->user_id = session('bo_id');
                        $check->city_id = $city_id;
                        $check->place_id   = $plant;
                        $check->district_id = $district_id;
                        $check->transportation_id = $transportation_id;
                        $check->price = $price;
                        $check->start_date = $dateFormatted1;
                        $check->end_date = $dateFormatted2;
                        $check->type_id = $tipe;
                        $check->note = $note;
                        $check->status = $row['status'] ?? 1;

                        $check->save();
                    }else{
                        $query = ModelsDeliveryCostStandard::create([
                            'code' => $row['code'],
                            'user_id' => session('bo_id'),
                            'city_id' => $city_id,
                            'place_id' => $plant,
                            'district_id' => $district_id,
                            'transportation_id' => $transportation_id,
                            'price' => $price,
                            'type_id' => $tipe,
                            'start_date' => $dateFormatted1,
                            'end_date' => $dateFormatted2,
                            'note' => $note,
                            'status'=> $row['status'] ?? 1,
                        ]);
                    }
                    
                   
                }else{
                    $sheet='Header';
                    throw new RowImportException('ada yang salah', $row->getIndex(),$this->error,$sheet);
                }
                    
            }
            else{
                $sheet='header';
                throw new RowImportException('Belum ada Kota', $row->getIndex(),$this->error,$sheet);
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
