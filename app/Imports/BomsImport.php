<?php

namespace App\Imports;

use App\Models\Bom;
use App\Models\BomAlternative;
use App\Models\BomDetail;
use App\Models\CostDistribution;
use App\Models\Item;
use App\Models\Place;
use App\Models\Resource;
use App\Models\Warehouse;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BomsImport implements WithMultipleSheets
{
    protected $currentSheetIndex = 0;

    public function sheets(): array
    {
        return [
            0 => new handleBomSheet(),
            1 => new handleAlternativeSheet(),
            2 => new handleDetailSheet(),
        ];
    }
}
class handleBomSheet implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
            $row = $row->toArray();
            if (isset($row['code']) && $row['code']) {
                $check = Bom::where('code', $row['code'])->first();
                $item_output_code = explode('#', $row['item_output'])[0];
                $item_output = Item::where('code', $item_output_code)->first();

                $item_reject_code = explode('#', $row['item_reject'])[0];
                $item_reject_id = Item::where('code', $item_reject_code)->first();
                $place = Place::where('code', explode('#', $row['plant'])[0])->first();
                $warehouse = Warehouse::where('code', explode('#', $row['gudang'])[0])->first();
                if (!$check) {
                    

                    $query = Bom::create([
                        'code' => $row['code'],
                        'name' => $row['name'],
                        'user_id' => session('bo_id'),
                        'item_id' => $item_output->id,
                        'item_reject_id' => $item_reject_id->id,
                        'place_id' => $place->id,
                        'warehouse_id' => $warehouse->id,
                        'qty_output' => $row['qty_output'],
                        'group' => $row['group'],
                        'status' => '1',
                    ]);

                    activity()
                        ->performedOn(new Bom())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit from excel bom data.');
                }else{
                    $check->bomDetail()->delete();
                    $check->bomAlternative()->delete();

                    $check->name = $row['name'];
                    $check->user_id = session('bo_id');
                    $check->item_id = $item_output->id;
                    $check->item_reject_id = $item_reject_id->id;
                    $check->place_id = $place->id;
                    $check->warehouse_id = $warehouse->id;
                    $check->qty_output = $row['qty_output'];
                    $check->group = $row['group'];

                    $check->save();
                }
            }else{
                return null;
            } 
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
        }
    }
    
    public function startRow(): int
    {
        return 2; // If you want to skip the first row (heading row)
    }
}

class handleAlternativeSheet implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
            $row = $row->toArray();

            if ($row['kode_bom_header']) {
                $check = Bom::where('code', $row['kode_bom_header'])->first();
                $checkalternative = BomAlternative::where('code', $row['kode_alternative'])->first();
                if ($check) {
                    $query = BomAlternative::create([
                        'code' => $row['kode_alternative'] ? $row['kode_alternative'] : strtoupper(Str::random(25)),
                        'name' => $row['nama_alternative'] ? $row['nama_alternative'] : strtoupper(Str::random(25)),
                        'is_default' => $row['is_default'] == '0' || $row['is_default'] == '' ? NULL : $row['is_default'],
                        'bom_id' => $check->id,
                    ]);
                }
            }else{
                return null;
            } 
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
        }
    }
   
    public function startRow(): int
    {
        return 2; // If you want to skip the first row (heading row)
    }
}
class handleDetailSheet implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
            $row = $row->toArray();

            if ($row['kode_bom_header']) {
                $check = Bom::where('code', $row['kode_bom_header'])->first();
                $checkalternative = BomAlternative::where('code', $row['kode_alternative'])->where('bom_id',$check->id)->first();
                if ($check) {

                    $method = explode('#', $row['metode'])[1];
                
                    if($row['type']=='items'){
                        $item_code = explode('#', $row['item_code'])[0];
                        $item_output = Item::where('code', $item_code)->first();
                        
                        $nominal = 0;
                        $total = 0;
                        $cost_distribution_id = null;
                        BomDetail::create([
                            'bom_id'       => $check->id,
                            'bom_alternative_id' => $checkalternative->id,
                            'lookable_type'=> $row['type'],
                            'lookable_id'  => $item_output->id,
                            'qty'          => $row['qty'],
                            'nominal'      => $nominal,
                            'total'        => $total,
                            'description'  => $row['description'],
                            'issue_method'       => $method,
                            'cost_distribution_id'=> $cost_distribution_id,
                        ]);
                    }elseif($row['type']=='resources'){
                        $item_code = explode('#', $row['resource_code'])[0];
                        $item_output = Resource::where('code', $item_code)->first();
                    
                        $nominal =$item_output->cost;
                        $total = $row['qty'] * $item_output->cost;
                        $cost_distribution_code = explode('#', $row['distribusi_biaya'])[0];
                        $cost_distribution = CostDistribution::where('code', $cost_distribution_code)->first();
                        $cost_distribution_id = $cost_distribution ? $cost_distribution->id : NULL;
                        BomDetail::create([
                            'bom_id'       => $check->id,
                            'bom_alternative_id' => $checkalternative->id,
                            'lookable_type'=> $row['type'],
                            'lookable_id'  => $item_output->id,
                            'qty'          => $row['qty'],
                            'nominal'      => $nominal,
                            'total'        => $total,
                            'description'  => $row['description'],
                            'issue_method'       => $method,
                            'cost_distribution_id'=> $cost_distribution_id,
                        ]);
                    }
                }
            }else{
                return null;
            }  
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
        }
    }

    
    public function startRow(): int
    {
        return 2; // If you want to skip the first row (heading row)
    }
}
