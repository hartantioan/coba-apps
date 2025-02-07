<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\BeforeImport;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use DateTime;

use App\Models\MitraPriceList;
use App\Models\Variety;
use App\Models\Type;
use App\Models\Pallet;
use App\Models\Unit;
use App\Models\User;
use App\Models\MitraApiSyncData;

class ImportMitraPriceList implements WithMultipleSheets
{
    public function sheets(): array
    {
        activity()
        ->performedOn(new MitraPriceList())
        ->causedBy(session('bo_id'))
        ->withProperties('')
        ->log('Add / edit from excel item data.');

        return [
            0 => new handleMainSheet(),
        ];
    }

}

class handleMainSheet implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
            $check = null;
            $row = $row->toArray();
            if(isset($row['sales_area_code']) && $row['sales_area_code']){
                $check   = MitraPriceList::withTrashed()->where('price_group_code',$row['price_group_code'])->where('sales_area_code',$row['sales_area_code'])->first();
                $mitra   = User::where('employee_no', explode('#',$row['mitra'])[0])->first();
                $variety = Variety::where('code', explode('#',$row['variety'])[0])->first();
                $type    = Type::where('code', explode('#',$row['type'])[0])->first();
                $pallet  = Pallet::where('prefix_code', explode('#',$row['package'])[0])->first();
                $unit    = Unit::where('code', explode('#',$row['uom'])[0])->first();

                $effective_date = DateTime::createFromFormat('U', ($row['effective_date'] - 25569) * 86400);
                if(!$check){
                    //Insert to table
                    $query = MitraPriceList::create([
                        'sales_area_code'  => $row['sales_area_code'],
                        'variety_id'       => $variety->id,
                        'type_id'          => $type->id,
                        'package_id'       => $pallet->id,
                        'effective_date'   => $effective_date->format('Y/m/d'),
                        'uom_id'           => $unit->id,
                        'min_qty'          => $row['min_qty'],
                        'price_exclude'    => $row['price_exclude'],
                        'price_include'    => $row['price_include'],
                        'mitra_id'         => $mitra->id,
                        'price_group_code' => $row['price_group_code'],
                        'status'           => '1',
                    ]);
                    
                    //Insert to API Data Sync Table
                    $payload = [
                        'salesAreaCode'  => $row['sales_area_code'],
                        'itemVariety'    => $variety->name,
                        'itemType'       => $type->name,
                        'itemPackage'    => $pallet->prefix_code,
                        'effectiveDate'  => $effective_date->format('Y/m/d'),
                        'uomCode'        => $unit->code,
                        'minQty'         => $row['min_qty'],
                        'priceExclude'   => $row['price_exclude'],
                        'priceInclude'   => $row['price_include'],
                        'priceGroupCode' => $row['price_group_code'],
                    ];
                    // jgn dihapus, utk ngeccek lsg insert api
                    // $temp = new TirtaKencanaController();
                    // $temp->postPriceListStore($payload);

                    $query->mitraApiSyncDatas()->create([
                        'mitra_id'  => $query->mitra->id,
                        'operation' => 'store',
                        'payload'   => json_encode($payload),
                        'status'    => '0',
                        'attempts'  => 0,
                    ]);

                }else{
                    //Update data in table
                    // if(!$check->hasChildDocument()){
                        if ($check->trashed()) {
                            $check->restore();
                        }
                        $check->name = $row['name'];
                        $check->type = $row['type'];
                        $check->mitra = $mitra ? $mitra->id : NULL;
                        $check->save();

                        //Update to API
                        
                    // }
                }
            }
            else{
                return null;
            }
            DB::commit();
        }catch (\Exception $e) {
            Log::error($e);
            DB::rollback();
        }
    }

    public function startRow(): int
    {
        return 2; // If you want to skip the first row (heading row)
    }
}
