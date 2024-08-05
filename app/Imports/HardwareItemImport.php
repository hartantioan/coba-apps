<?php

namespace App\Imports;

use App\Exceptions\RowImportException;
use App\Models\HardwareItem;
use App\Models\HardwareItemGroup;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class HardwareItemImport implements WithMultipleSheets
{
    protected $error = '';

    public function sheets(): array
    {
        return [
            0 => new handleHardwareSheet(),
        ];
    }
}

class handleHardwareSheet implements OnEachRow, WithHeadingRow
{
    public $error = null;
    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
            if (isset($row['group']) && $row['group']) {
                $group = explode('#', $row['group'])[0];
                $group_id = HardwareItemGroup::where('code', $group)->first();
                if(!$group && $this->error ==null){
                    $this->error = "Group Hardware";
                }

                if ($group_id) {
                    
                    if (isset($row['detail_1']) && $row['detail_1']) {
                       
                        if (isset($row['barang']) && $row['barang']) {
                          
                            $query = HardwareItem::create([
                                'code' => HardwareItem::generateCode(),
                                'item' => $row['barang'],
                                'user_id' => session('bo_id'),
                                'hardware_item_group_id' => $group_id->id,
                                'detail1' => $row['detail_1'],
                                'status' => '1',
                            ]);
                        }else{
                            DB::rollback();
                            $this->error = 'barang';
                            $sheet='BOM';
                            throw new RowImportException('Ada yang tidak Lengkap', $row->getIndex(),$this->error,$sheet);
                        }
                    }else{
                        DB::rollback();
                        $this->error = 'detail';
                        $sheet='BOM';
                        throw new RowImportException('Ada yang tidak Lengkap', $row->getIndex(),$this->error,$sheet);
                    }

                    activity()
                        ->performedOn(new HardwareItem())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit from excel HardwareItem data.');
                }
            }else{
                return null;
            } 
            DB::commit();
        }catch (\Exception $e) {
            DB::rollback();
            $sheet='BOM';
            throw new RowImportException($e->getMessage(), $row->getIndex(),$this->error,$sheet);
        }
    }
    
    public function startRow(): int
    {
        return 2; // If you want to skip the first row (heading row)
    }
}
