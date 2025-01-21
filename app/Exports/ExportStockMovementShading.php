<?php


namespace App\Exports;
use App\Helpers\CustomHelper;
use App\Models\ItemCogs;
use App\Models\ItemShading;
use App\Models\Place;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\DB;

class ExportStockMovementShading implements FromArray,ShouldAutoSize, WithChunkReading
{
    protected $plant, $item, $warehouse, $start_date, $finish_date,$type,$group,$batch_id,$shading_id;
    public function __construct(string $plant, string $item,string $warehouse, string $start_date, string $finish_date , string $type , string $group, string $batch_id , string $shading_id)
    {
        $this->plant = $plant ? $plant : '';
		$this->item = $item ? $item : '';
        $this->warehouse = $warehouse ? $warehouse : '';
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
        $this->type = $type ? $type : '';
        $this->group = $group ? $group : '';
        $this->batch_id = $batch_id ? $batch_id : '';
        $this->shading_id = $shading_id ? $shading_id : '';
    }
    public function array(): array
    {
        $arr = [];

        if($this->shading_id){
            $shading = ItemShading::find($this->shading_id);
        }elseif($this->item){
            $shading = ItemShading::where('item_id',$this->item)->get();
        }else{
            $shading = ItemShading::get();
        }

        $place = Place::find($this->plant);

        if($this->type == 'final'){

            $arr[] = [
                'No.',
                'Plant',
                'Kode Item',
                'Nama Item',
                'Satuan',
                'Shading',
                'Balance Qty',
                'Balance Nominal',
            ];

            if($this->shading_id){

                if($shading){
                    $data = ItemCogs::where('item_shading_id',$shading->id)->where(function($query){
                        $query->where('date','<=',$this->finish_date);
                        if($this->plant){
                            $query->where('place_id',$this->plant);
                        }
                    })->orderByDesc('date')->orderByDesc('id')->first();
                    $qty = 0;
                    $total = 0;
                    if($data){
                        $qty = round($data->totalByShadingBeforeIncludeDate(),3);
                        $total = round($data->totalNominalByShadingBeforeIncludeDate(),2);
                    }
                    $arr[] = [
                        '1',
                        $place->code,
                        $shading->item->code,
                        $shading->item->name,
                        $shading->item->uomUnit->code,
                        $shading->code,
                        $qty,
                        $total,
                    ];
                }

            }else{
                if(count($shading) > 0){
                    foreach($shading as $key => $rowshading){
                        $data = ItemCogs::where('item_shading_id',$rowshading->id)->where(function($query){
                            $query->where('date','<=',$this->finish_date);
                            if($this->plant){
                                $query->where('place_id',$this->plant);
                            }
                        })->orderByDesc('date')->orderByDesc('id')->first();
                        $qty = 0;
                        $total = 0;
                        if($data){
                            $qty = round($data->totalByShadingBeforeIncludeDate(),3);
                            $total = round($data->totalNominalByShadingBeforeIncludeDate(),3);
                        }
                        $arr[] = [
                            ($key + 1),
                            $place->code,
                            $rowshading->item->code,
                            $rowshading->item->name,
                            $rowshading->item->uomUnit->code,
                            $rowshading->code,
                            $qty,
                            $total,
                        ];
                    }

                    $arr[] = [
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                    ];

                    #detail per batch
                    foreach($shading as $key => $rowshading){
                        $datadetail = DB::select("
                            SELECT 
                                rs.batch_code,
                                rs.shading_code,
                                rs.item_code,
                                rs.item_name,
                                rs.place_code,
                                rs.total_qty_in,
                                rs.total_qty_out,
                                (rs.total_qty_in - rs.total_qty_out) AS balance_qty,
                                rs.total_in,
                                rs.total_out,
                                (rs.total_in - rs.total_out) AS balance_nominal
                                FROM (
                                    SELECT 
                                        IFNULL(SUM(ROUND(ic.qty_in,3)),0) AS total_qty_in,
                                        IFNULL(SUM(ROUND(ic.qty_out,3)),0) AS total_qty_out,
                                        IFNULL(SUM(ROUND(ic.total_in,2)),0) AS total_in,
                                        IFNULL(SUM(ROUND(ic.total_out,2)),0) AS total_out,
                                        pb.code AS batch_code,
                                        ish.code AS shading_code,
                                        i.code AS item_code,
                                        i.name AS item_name,
                                        p.code AS place_code
                                    FROM item_cogs ic
                                        LEFT JOIN production_batches pb
                                            ON pb.id = ic.production_batch_id
                                        LEFT JOIN item_shadings ish
                                            ON ish.id = ic.item_shading_id
                                        LEFT JOIN items i
                                            ON i.id = ic.item_id
                                        LEFT JOIN places p
                                            ON p.id = ic.place_id
                                    WHERE 
                                        ic.date <= :date 
                                        AND ic.item_shading_id = :item_shading_id
                                        AND ic.deleted_at IS NULL
                                    GROUP BY ic.production_batch_id
                                ) AS rs
                            WHERE (rs.total_qty_in - rs.total_qty_out) > 0
                        ", array(
                            'date'              => $this->finish_date,
                            'item_shading_id'   => $rowshading->id,
                        ));

                        foreach($datadetail as $key => $rowdetail){
                            $arr[] = [
                                $key + 1,
                                $rowdetail->place_code,
                                $rowdetail->item_code,
                                $rowdetail->item_name,
                                'M2',
                                $rowdetail->shading_code,
                                $rowdetail->balance_qty,
                                $rowdetail->balance_nominal,
                            ];
                        }
                    }
                    
                }
            }

        }else{
            $arr[] = [
                'No.',
                'Kode Item',
                'Nama Item',
                'Plant',
                'Shading',
                'Batch',
                'Area',
                'Satuan',
                'Mutasi',
                'Balance',
            ];

            if($this->shading_id){
                if($shading){
                    $data = ItemCogs::where('item_shading_id',$shading->id)->where(function($query){
                        $query->where('date','<=',$this->finish_date)->where('date','>=',$this->start_date);
                        if($this->plant){
                            $query->where('place_id',$this->plant);
                        }
                    })->orderBy('date')->orderBy('id')->get();
                    $dataBefore = ItemCogs::where('item_shading_id',$shading->id)->where(function($query){
                        $query->where('date','<',$this->start_date);
                        if($this->plant){
                            $query->where('place_id',$this->plant);
                        }
                    })->orderByDesc('date')->orderByDesc('id')->first();
                    $balance = 0;
                    if($dataBefore){
                        $balance += round($dataBefore->totalByShadingBeforeIncludeDate(),3);
                    }
                    $arr[] = [
                        '',
                        $shading->item->code,
                        $shading->item->name,
                        $place->code,
                        '',
                        '',
                        '',
                        '',
                        'SALDO',
                        $balance,
                    ];
                    foreach($data as $key => $row){
                        $balance += $row->type == 'IN' ? round($row->qty_in,3) : round(-1 * $row->qty_out,3);
                        $arr[] = [
                            ($key + 1),
                            $shading->item->code,
                            $shading->item->name,
                            $place->code,
                            $shading->code,
                            $row->productionBatch->code,
                            $row->area->code,
                            $row->item->uomUnit->code,
                            $row->type == 'IN' ? $row->qty_in : -1 * $row->qty_out,
                            $balance,
                        ];
                    }
                }
            }else{
                if(count($shading) > 0){
                    foreach($shading as $rowshading){
                        $data = ItemCogs::where('item_shading_id',$rowshading->id)->where(function($query){
                            $query->where('date','<=',$this->finish_date)->where('date','>=',$this->start_date);
                            if($this->plant){
                                $query->where('place_id',$this->plant);
                            }
                        })->orderBy('date')->orderBy('id')->get();
                        $dataBefore = ItemCogs::where('item_shading_id',$rowshading->id)->where(function($query){
                            $query->where('date','<',$this->start_date);
                            if($this->plant){
                                $query->where('place_id',$this->plant);
                            }
                        })->orderByDesc('date')->orderByDesc('id')->first();
                        $balance = 0;
                        if($dataBefore){
                            $balance += round($dataBefore->totalByShadingBeforeIncludeDate(),3);
                        }
                        $arr[] = [
                            '',
                            $rowshading->item->code,
                            $rowshading->item->name,
                            $place->code,
                            $rowshading->code,
                            '',
                            '',
                            '',
                            'SALDO',
                            $balance,
                        ];
                        foreach($data as $key => $row){
                            $balance += $row->type == 'IN' ? round($row->qty_in,3) : round(-1 * $row->qty_out,3);
                            $arr[] = [
                                ($key + 1),
                                $rowshading->item->code,
                                $rowshading->item->name,
                                $place->code,
                                $rowshading->code,
                                $row->productionBatch->code,
                                $row->area->code,
                                $row->item->uomUnit->code,
                                $row->type == 'IN' ? $row->qty_in : -1 * $row->qty_out,
                                $balance,
                            ];
                        }
                    }
                }
            }
        }

        return $arr;
    }

    public function chunkSize(): int
    {
        return 1000;  // Process in chunks of 1000 rows
    }
}
