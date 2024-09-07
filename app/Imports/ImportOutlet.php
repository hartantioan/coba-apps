<?php

namespace App\Imports;
use App\Exceptions\RowImportException;
use DateTime;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Region;
use App\Models\Group;
use App\Models\GroupOutlet;
use App\Models\Outlet;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ImportOutlet implements WithMultipleSheets
{
    public function sheets(): array
    {
        activity()
        ->performedOn(new Outlet())
        ->causedBy(session('bo_id'))
        ->withProperties(null)
        ->log('Add / edit from excel bom data.');
        return [
            0 => new handleOutlet()
        ];
    }
    
}

class handleOutlet implements OnEachRow, WithHeadingRow
{
    public $error = null;

   
    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
            if (isset($row['name']) && $row['name']) {
                $district = str_replace(',', '.', explode('#', $row['kecamatan'])[0]);
                $district_id = Region::where('code',$district)->first()->id ?? null;
                
                $city = str_replace(',', '.', explode('#', $row['kota'])[0]);
                $city_id = Region::where('code',$city)->first()->id; 
                
                $province = str_replace(',', '.', explode('#', $row['provinsi'])[0]);
                $province_id = Region::where('code',$province)->first()->id ?? null;

                $type_group =  explode('#', $row['group_bp'])[0];
                $type_group_id = Group::where('code',$type_group)->first()->id ?? null;
                $group_outlet = explode('#', $row['grouping'])[0];
                $type_group_outlet = GroupOutlet::where('code',$group_outlet)->first()->id?? null;
                
                $type = explode('#', $row['type'])[0];
                
                if(!$district_id && $this->error ==null){
                    $this->error = "Kecamatan.";
                }elseif(!$city_id && $this->error ==null){
                    $this->error = "Kota.";
                }elseif(!$type_group_id && $this->error ==null){
                    $this->error = "tipe group bp.";
                }elseif(!$province_id && $this->error ==null){
                    $this->error = "Provinsi.";
                }elseif(!$type_group_outlet && $this->error ==null){
                    $this->error = "grouping.";
                }

                if (!$this->error) {

                    $query = Outlet::create([
                        'user_id'           => session('bo_id'),
                        'code'              => $row['code'],
                        'name'              => $row['name'],
                        'type'              => $type,
                        'group_bp_id'       => $type_group_id,
                        'province_id'       => $province_id,
                        'city_id'           => $city_id,
                        'district_id'       => $district_id,
                        'outlet_group_id'   => $type_group_outlet,
                        'link_gmap'         => $row['link_outlet'],

                        'phone'             => $row['no_telp'],
                        'address'           => $row['address'],
                        
                        'status'            => 1,
                    ]);

                    
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
