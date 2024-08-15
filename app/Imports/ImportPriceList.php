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
use DateTime;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportPriceList implements WithMultipleSheets
{
    protected $error = '';

    public function sheets(): array
    {
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
                $item_code= explode('#',$row['item'])[0];
                $item = Item::where('code',$item_code)->first();
                $group_code = explode('#', $row['group'])[0];
                $group = Group::where('code',$group_code)->first();
                if(!$item && $this->error ==null){
                    $this->error = "Item.";
                }elseif(!$group && $this->error ==null){
                    $this->error = "Group.";
                }
                $dateTime1 = DateTime::createFromFormat('U', ($row['startdate'] - 25569) * 86400);
                $dateFormatted_1 = $dateTime1->format('Y/m/d');
                $dateTime2 = DateTime::createFromFormat('U', ($row['enddate'] - 25569) * 86400);
                $dateFormatted_2 = $dateTime2->format('Y/m/d');
                if(!$this->error){
                    $query = ItemPricelist::create([
                        'code'              => strtoupper(Str::random(15)),
                        'user_id'           => session('bo_id'),
                        'item_id'           => $item->id,
                        'group_id'          => $group->id,
                        'place_id'          => $place->id,
                        'start_date'        => $dateFormatted_1,
                        'end_date'          => $dateFormatted_2,
                        'price'             => $row['price'],
                        'status'            => '1',
                    ]);
                    
                }else{
                         
                    $sheet='Header';
                    throw new RowImportException("data kurang lengkap", $row->getIndex(),$this->error,$sheet);
                }
                
                activity()
                    ->performedOn(new ItemPricelist())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('From excel item price list to database.');
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
