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

        DB::statement("SET SQL_MODE=''");

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
