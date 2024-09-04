<?php

namespace App\Imports;

use App\Models\BomStandard;
use App\Models\BomStandardDetail;
use App\Models\CostDistribution;
use App\Models\Item;
use App\Models\Resource;
use App\Exceptions\RowImportException;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ImportBomStandard implements WithMultipleSheets
{
    protected $error = '';

    public function sheets(): array
    {
        activity()
        ->performedOn(new BomStandard())
        ->causedBy(session('bo_id'))
        ->withProperties('')
        ->log('Add / edit from excel bom standard data.');
        return [
            0 => new handleBomSheet(),
            1 => new handleDetailSheet(),
        ];
    }
}
class handleBomSheet implements OnEachRow, WithHeadingRow
{
    public $error = null;
    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
            if (isset($row['code']) && $row['code']) {
                $check = BomStandard::where('code', $row['code'])->first();

                if (!$check) {
                    

                    $query = BomStandard::create([
                        'code'      => $row['code'],
                        'name'      => $row['name'],
                        'user_id'   => session('bo_id'),
                        'status'    => '1',
                    ]);

                    
                }else{
                    $check->bomStandardDetail()->delete();

                    $check->name = $row['name'];
                    $check->user_id = session('bo_id');

                    $check->save();
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

class handleDetailSheet implements OnEachRow, WithHeadingRow
{
    public $error = null;
    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
           

            if ($row['kode_bom_header']) {
                $check = BomStandard::where('code', $row['kode_bom_header'])->first();
                if ($check) {                
                    if($row['type']=='items'){
                        $item_code = explode('#', $row['item_code'])[0];
                        $item_output = Item::where('code', $item_code)->first();
                        if(!$item_output && $this->error ==null){
                            $this->error = "item";
                        }
                        $nominal = 0;
                        $total = 0;
                        $cost_distribution_id = null;
                        BomStandardDetail::create([
                            'bom_standard_id'       => $check->id,
                            'lookable_type'         => $row['type'],
                            'lookable_id'           => $item_output->id,
                            'qty'                   => $row['qty'],
                            'nominal'               => $nominal,
                            'total'                 => $total,
                            'description'           => $row['description'],
                            'cost_distribution_id'  => $cost_distribution_id,
                        ]);
                    }elseif($row['type']=='resources'){
                        $item_code = explode('#', $row['resource_code'])[0];
                        $item_output = Resource::where('code', $item_code)->first();
                    
                        $nominal =$item_output->cost;
                        $total = $row['qty'] * $item_output->cost;
                        $cost_distribution_code = explode('#', $row['distribusi_biaya'])[0];
                        $cost_distribution = CostDistribution::where('code', $cost_distribution_code)->first();
                        $cost_distribution_id = $cost_distribution ? $cost_distribution->id : NULL;
                        if(!$item_output && $this->error ==null){
                            $this->error = "Resource.";
                        }elseif(!$cost_distribution && $this->error ==null){
                            $this->error = "Disribusi Biaya.";
                        }
                        BomStandardDetail::create([
                            'bom_standard_id'       => $check->id,
                            'lookable_type'         => $row['type'],
                            'lookable_id'           => $item_output->id,
                            'qty'                   => $row['qty'],
                            'nominal'               => $nominal,
                            'total'                 => $total,
                            'description'           => $row['description'],
                            'cost_distribution_id'  => $cost_distribution_id,
                        ]);
                    }
                }
            }else{
                return null;
            }  
            DB::commit();
        } catch (\Exception $e) {
            $sheet='Detail';
            DB::rollback();
            throw new RowImportException($e->getMessage(), $row->getIndex(),null,$sheet);
        }
    }

    
    public function startRow(): int
    {
        return 2; // If you want to skip the first row (heading row)
    }
}
