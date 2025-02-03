<?php

namespace App\Imports;

use App\Exceptions\RowImportException;
use App\Models\Item;
use App\Models\ToleranceScale;
use Illuminate\Support\Str;

use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Illuminate\Support\Facades\DB;
use App\Models\ItemWeightFg;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportToleranceScale implements WithMultipleSheets
{
    protected $error = '';

    public function sheets(): array
    {
        activity()
        ->performedOn(new ToleranceScale())
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
            if (isset($row['item_code']) && $row['item_code']) {
                $item_code= $row['item_code'];
                $item = Item::where('code',$item_code)->where('is_sales_item','1')->where('status','1')->first();

                if(!$item && $this->error ==null){
                    $this->error = "Item.";
                }

                if(!$this->error){
                    $query_update = ToleranceScale::where('item_id',$item->id)->first();

                    if(!$query_update){
                        $query = ToleranceScale::create([
                            'user_id'           => session('bo_id'),
                            'item_id'           => $item->id,
                            'percentage'        => $row['percentage'],
                        ]);
                    }else{
                        $query_update->update([
                            'user_id'               => session('bo_id'),
                            'percentage'            => $row['percentage'],
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
