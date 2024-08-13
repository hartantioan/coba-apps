<?php

namespace App\Imports;


use App\Models\User;

use App\Exceptions\RowImportException;
use App\Models\BomStandard;
use App\Models\Company;
use App\Models\Country;
use App\Models\Place;
use App\Models\Region;
use App\Models\UserBank;
use App\Models\UserData;
use App\Models\UserDestination;
use App\Models\UserDriver;
use DateTime;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
class ImportUser implements WithMultipleSheets
{
    protected $error = '';
    protected $temp = [];

    public function sheets(): array
    {
        return [
            0 => new handleUser($this->temp),
            1 => new handleUsebankSheet($this->temp),
            2 => new handleUserData($this->temp),
            3 => new handleUserDestination($this->temp),
            4 => new handleUserDriver($this->temp),
        ];
    }
}
class handleUser implements OnEachRow, WithHeadingRow
{
    public $error = null;
    protected $temp;

    public function __construct(&$temp)
    {
        $this->temp = &$temp;
    }
    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
            if (isset($row['nama']) && $row['nama']) {
                $district = str_replace(',', '.', explode('#', $row['kecamatan'])[0]);
                $district_id = Region::where('code',$district)->first()->id;
                if (!isset($row['no']) && !$row['no']) {
                    $sheet='Header';
                    throw new RowImportException("data kurang lengkap", $row->getIndex(),'tidak ada nomor',$sheet); 
                }
                $city = str_replace(',', '.', explode('#', $row['kota'])[0]);
                $city_id = Region::where('code',$city)->first()->id; 
               
                $province = str_replace(',', '.', explode('#', $row['provinsi'])[0]);
                $province_id = Region::where('code',$province)->first()->id;
                 
                $country =  explode('#', $row['negara_asal'])[0];
                $country_id= Country::where('code',$country)->first()->id;
               
                $gender =  explode('#', $row['jenis_kelamin'])[0];
                $status_married =  explode('#', $row['status_pernikahan'])[0];
                $type_group =  explode('#', $row['kelompok_bisnis'])[0];
                $type = explode('#', $row['type'])[0];
                $type_pegawai = explode('#', $row['tipe_pegawai'])[0];
                
                $place = Place::where('code', explode('#', $row['plant'])[1])->first();

                $company = Company::where('id', explode('#', $row['perusahaan'])[0])->first();
                
                if(!$district_id && $this->error ==null){
                    $this->error = "Kecamatan.";
                }elseif(!$city_id && $this->error ==null){
                    $this->error = "Kota.";
                }elseif(!$place && $this->error ==null){
                    $this->error = "plant.";
                }elseif(!$province_id && $this->error ==null){
                    $this->error = "Provinsi.";
                }elseif(!$country_id && $this->error ==null){
                    $this->error = "Negara.";
                }

                if (!$this->error) {
                    $dateTime = DateTime::createFromFormat('U', ($row['tgl_pernikahan'] - 25569) * 86400);
                    $dateFormatted = $dateTime->format('Y/m/d');
                  
                    $query = User::create([
                        'employee_no'       => $row['code_bp'] ?? User::generateCode($type, $type_pegawai, $place->id),
                        'password'          => bcrypt($row['password']),
                        'phone'             => $row['no_telepon'],
                        'address'           => $row['alamat'],
                        'name'              => $row['nama'],
                        'username'          => $row['code_bp'] ?? User::generateCode($type, $type_pegawai, $place->id),
                        'province_id'       => $province_id,
                        'city_id'           => $city_id,
                        'district_id'       => $district_id,
                        'id_card'           => $row['no_ktp'],
                        'id_card_address'   => $row['alamat_ktp'],
                        'type'              => $type,
                        'limit_credit'      => $row['limit_kredit'],
                        'top'               => $row['top'],
                        'top_internal'      => $row['top_internal'],
                        'group_id'          => $type_group,
                        'employee_type'     => $type_pegawai,
                        'company_id'        => $company->id,
                        'office_no'         => $row['no_kantor'],
                        'email'             => $row['email'],
                        'deposit'           => $row['deposit'],
                        'gender'            => $gender,
                        'married_status'    => $status_married,
                        'married_date'      => $dateFormatted,
                        'children'          => $row['jumlah_anak'],
                        'country_id'        => $country_id,
                        'nib'               => $row['nib'],
                        'sppkp'             => $row['sppkp'],
                        'status'            => 1,
                    ]);

                    $this->temp[]=[
                        'id' => $query->id,
                        'no' => $row['no']
                    ];

                    activity()
                        ->performedOn(new User())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit from excel bom data.');
                }else{
                    $sheet='Header';
                    throw new RowImportException("data kurang lengkap", $row->getIndex(),$this->error,$sheet);
                }
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
        return 2; 
    }
}

class handleUsebankSheet implements OnEachRow, WithHeadingRow
{
    public $error = null;
    protected $temp;

    public function __construct(&$temp)
    {
        $this->temp = &$temp;
    }
    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
            if ($row['no_header']) {
                foreach($this->temp as $row1){
                    if ($row1['no'] == $row['no_header']) {
                        $query = UserBank::create([
                            'user_id' => $row1['id'],
                            'bank' => $row['bank'],
                            'name' => $row['atas_nama'],
                            'no'   => $row['rekening_bank'],
                            'is_default' => empty($row['default']) ? '0' : $row['default'],
                            'branch' => $row['cabang'],
                        ]);
                    }
                }
              
            }

            DB::commit();
        }catch (\Exception $e) {
            DB::rollback();
            $sheet='bank user';
            throw new RowImportException($e->getMessage(), $row->getIndex(),null,$sheet);
        }
    }
   
    public function startRow(): int
    {
        return 2; // If you want to skip the first row (heading row)
    }
}

class handleUserData implements OnEachRow, WithHeadingRow
{
    public $error = null;
    protected $temp;

    public function __construct(&$temp)
    {
        $this->temp = &$temp;
    }
    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
            if ($row['no_header']) {
                foreach($this->temp as $row1){
                    if ($row1['no'] == $row['no_header']) {
                        $district = str_replace(',', '.', explode('#', $row['kecamatan'])[0]);
                        $district_id = Region::where('code',$district)->first()->id; 
                        $city = str_replace(',', '.', explode('#', $row['kota'])[0]);
                        $city_id = Region::where('code',$city)->first()->id; 
                        $province = str_replace(',', '.', explode('#', $row['provinsi'])[0]);
                        $province_id = Region::where('code',$province)->first()->id; 
                        $country =  explode('#', $row['negara'])[0];
                        $country_id= Country::where('code',$country)->first()->id;
                        if(!$district_id && $this->error ==null){
                            $this->error = "Kecamatan.";
                        }elseif(!$city_id && $this->error ==null){
                            $this->error = "Kota.";
                        }elseif(!$province_id && $this->error ==null){
                            $this->error = "Provinsi.";
                        }elseif(!$country_id && $this->error ==null){
                            $this->error = "Negara.";
                        }
                        if (!$this->error) {
                            $query = UserData::create([
                                'user_id' => $row1['id'],
                                'title'              => $row['nama'],
                                'content' => $row['catatan'],
                                'npwp' => $row['npwp'],
                                'address'   => $row['alamat'],
                                'province_id'       => $province_id,
                                'city_id'           => $city_id,
                                'district_id'       => $district_id,
                                'country_id'        => $country_id,
                                'is_default' => empty($row['default']) ? '0' : $row['default'],
                                
                            ]);
                        }else{
                            $sheet='User Data';
                            throw new RowImportException("data kurang lengkap", $row->getIndex(),$this->error,$sheet);
                        }
                    }
                }
            }
            DB::commit();
        }catch (\Exception $e) {
            DB::rollback();
            $sheet='User Data';
            throw new RowImportException($e->getMessage(), $row->getIndex(),null,$sheet);
        }
    }
   
    public function startRow(): int
    {
        return 2; // If you want to skip the first row (heading row)
    }
}

class handleUserDestination implements OnEachRow, WithHeadingRow
{
    public $error = null;
    protected $temp;

    public function __construct(&$temp)
    {
        $this->temp = &$temp;
    }
    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
            if ($row['no_header']) {
                foreach($this->temp as $row1){
                  
                    if ($row1['no'] == $row['no_header']) {
                       
                        $district = str_replace(',', '.', explode('#', $row['kecamatan'])[0]);
                        $district_id = Region::where('code',$district)->first()->id; 
                        $city = str_replace(',', '.', explode('#', $row['kota'])[0]);
                        $city_id = Region::where('code',$city)->first()->id; 
                        $province = str_replace(',', '.', explode('#', $row['provinsi'])[0]);
                        $province_id = Region::where('code',$province)->first()->id; 
                        $country =  explode('#', $row['negara'])[0];
                        $country_id= Country::where('code',$country)->first()->id;
                        if(!$district_id && $this->error ==null){
                            $this->error = "Kecamatan.";
                        }elseif(!$city_id && $this->error ==null){
                            $this->error = "Kota.";
                        }elseif(!$province_id && $this->error ==null){
                            $this->error = "Provinsi.";
                        }elseif(!$country_id && $this->error ==null){
                            $this->error = "Negara.";
                        }
                        if (!$this->error) {
                           
                            $query = UserDestination::create([
                                'user_id' => $row1['id'],
                                'address'           => $row['alamat'],
                                'province_id'       => $province_id,
                                'city_id'           => $city_id,
                                'district_id'       => $district_id,
                                'country_id'        => $country_id,
                                'is_default' => empty($row['default']) ? '0' : $row['default'],
                            ]);
                            
                        }else{
                            $sheet='User Destination';
                            throw new RowImportException("data kurang lengkap", $row->getIndex(),$this->error,$sheet);
                        }
                    }
                }
            } 
            DB::commit();
        }catch (\Exception $e) {
            DB::rollback();
            $sheet='User Destination';
            throw new RowImportException($e->getMessage(), $row->getIndex(),null,$sheet);
        }
    }
   
    public function startRow(): int
    {
        return 2; // If you want to skip the first row (heading row)
    }
}

class handleUserDriver implements OnEachRow, WithHeadingRow
{
    public $error = null;
    protected $temp;

    public function __construct(&$temp)
    {
        $this->temp = &$temp;
    }
    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
            if ($row['no_header']) {
                foreach($this->temp as $row1){
                  
                    if ($row1['no'] == $row['no_header']) {
                        
                        
                        $query = UserDriver::create([
                            'user_id' => $row1['id'],
                            'name'   => $row['nama'],
                            'hp'       => $row['no_hp']
                        ]);
                        
                        
                    }
                }
            }
            DB::commit();
        }catch (\Exception $e) {
            DB::rollback();
            $sheet='Driver User';
            throw new RowImportException($e->getMessage(), $row->getIndex(),null,$sheet);
        }
    }
   
    public function startRow(): int
    {
        return 2; // If you want to skip the first row (heading row)
    }
}