<?php

namespace App\Imports;
use Illuminate\Support\Str;
use App\Models\Group;
use App\Models\Item;
use App\Models\ItemPricelist;
use App\Models\Place;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Illuminate\Support\Facades\DB;
use App\Exceptions\RowImportException;
use App\Models\Brand;
use App\Models\Grade;
use App\Models\Region;
use App\Models\Type;
use App\Models\User;
use DateTime;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportPriceList implements WithMultipleSheets
{
    protected $error = '';

    public function sheets(): array
    {
        activity()
        ->performedOn(new ItemPricelist())
        ->causedBy(session('bo_id'))
        ->withProperties(null)
        ->log('From excel item price list to database.');
        return [
            0 => new handleItemPriceList(),

        ];
    }
}
class handleItemPriceList implements   OnEachRow, WithHeadingRow
{
    public $error = null;

    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
            if (isset($row['plant']) && $row['plant']) {
                $place_code= explode('#',$row['plant'])[0];
                $place = Place::where('code',$place_code)->first();
                $item_code= explode('#',$row['type'])[0];
                $item = Type::where('code',$item_code)->first();
                $group_code = explode('#', $row['group'])[0];
                $group = Group::where('code',$group_code)->first();

                $city = str_replace(',', '.', explode('#', $row['kota'])[0]);
                $city_id = Region::where('code',$city)->first()->id;

                $province = str_replace(',', '.', explode('#', $row['provinsi'])[0]);
                $province_id = Region::where('code',$province)->first()->id ?? null;
                $grade_code = explode('#', $row['grade'])[0];
                $grade = Grade::where('code',$grade_code)->first();
                $delivery_type = explode('#', $row['tipe_delivery'])[0];

                if(!$item && $this->error ==null){
                    $this->error = "type.";
                }elseif(!$group && $this->error ==null){
                    $this->error = "Group.";
                }elseif(!$grade && $this->error ==null){
                    $this->error = "GRADE.";
                }elseif(!$province_id && $this->error ==null){
                    $this->error = "Provinsi.";
                }elseif(!$city_id && $this->error ==null){
                    $this->error = "Kota.";
                }

                if(!$this->error){
                    $query = ItemPricelist::create([
                        'code'              => strtoupper(Str::random(15)),
                        'user_id'           => session('bo_id'),
                        'type_id'           => $item->id,
                        'group_id'          => $group->id,
                        'place_id'          => $place->id,
                        'grade_id'          => $grade->id,
                        'province_id'       => $province_id,
                        'city_id'           => $city_id,
                        'type_delivery'     => $delivery_type,
                        'price'             => $row['price'],
                        'sell_price'        => $row['harga_jual'],
                        'discount'          => $row['discount'],
                        'status'            => '1',
                    ]);

                }else{

                    $sheet='Header';
                    throw new RowImportException("data kurang lengkap", $row->getIndex(),$this->error,$sheet);
                }

            }else{
                $sheet='Header';
                throw new RowImportException("Plant Belum terisi", $row->getIndex(),'mohon diisi',$sheet);
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
