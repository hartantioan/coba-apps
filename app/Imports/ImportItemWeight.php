<?php

namespace App\Imports;

use App\Exceptions\RowImportException;
use App\Models\Item;
use Illuminate\Support\Str;

use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Illuminate\Support\Facades\DB;
use App\Models\ItemWeightFg;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportItemWeight implements WithMultipleSheets
{
    protected $error = '';

    public function sheets(): array
    {
        activity()
        ->performedOn(new ItemWeightFg())
        ->causedBy(session('bo_id'))
        ->withProperties(null)
        ->log('From excel item Weight to database.');
        return [
            0 => new handleItemWeight(),
        ];
    }
}

class handleItemWeight implements   OnEachRow, WithHeadingRow
{
    public $error = null;

    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
            if (isset($row['item']) && $row['item']) {
                $item_code= explode('#',$row['item'])[0];
                $item = Item::where('code',$item_code)->first();


                if(!$item && $this->error ==null){
                    $this->error = "Item.";
                }

                if(!$this->error){
                    $query_update = ItemWeightFg::where('item_id',$item->id)->first();

                    if(!$query_update){
                        $query = ItemWeightFg::create([
                            'code'              => strtoupper(Str::random(15)),
                            'user_id'           => session('bo_id'),
                            'item_id'           => $item->id,
                            'gross_weight'           => $row['berat_gross'],
                            'netto_weight'          => $row['berat_netto'],
                        ]);
                    }else{
                        $query_update->update([
                            'user_id'               => session('bo_id'),
                            'gross_weight'          => $row['berat_gross'],
                            'netto_weight'          => $row['berat_netto'],
                        ]);
                    }

                }else{

                    $sheet='Header';
                    throw new RowImportException("data kurang lengkap", $row->getIndex(),$this->error,$sheet);
                }

            }else{
                $sheet='Header';
                throw new RowImportException("Item Belum terisi", $row->getIndex(),'mohon diisi',$sheet);
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
