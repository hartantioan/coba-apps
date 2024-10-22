<?php

namespace App\Exports;

use App\Models\Item;
use App\Models\ItemCogs;
use App\Models\ItemShading;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReportStockInRupiahAccounting implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $finish_date,$place_id,$warehouse_id;

    public function __construct(string $start_date,string $place_id, string $warehouse_id)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->place_id = $place_id ? $place_id : '';
        $this->warehouse_id = $warehouse_id ? $warehouse_id : '';
    }

    private $headings = [
        'No',
        'Code',
        'Nama Item',
        'Unit',
        'Shading',
        'Qty',
        'Total',
    ];


    public function collection()
    {
        $item = ItemShading::join('items', 'item_shadings.item_id', '=', 'items.id')
        ->whereHas('item',function ($query)  {
            $query->whereNull('deleted_at');
        })->whereHas('itemCogs',function ($query) {
            $query->where('place_id',$this->place_id)
            ->where('warehouse_id',$this->warehouse_id);
        })
        ->orderBy('items.code')
        ->orderBy('items.id')
        ->select('item_shadings.*')
        ->get();

        $itemNoShading = Item::whereNotIn('id', function ($query) {
            $query->select('item_id')
                  ->from('item_shadings');
        })
        ->whereHas('itemCogs',function ($query) {
          $query->where('place_id',$this->place_id)
          ->where('warehouse_id',$this->warehouse_id);
        })
        ->whereNull('deleted_at')->get();


        $arr = [];
        $keys = 1;
        foreach ($item as $key=>$row) {

            $rp_in = 0;
            $rp_out = 0 ;

            $ItemCogsShadingIn = ItemCogs::where('deleted_at',null)
            ->where('item_shading_id',$row->id)
            ->where( 'warehouse_id',$this->warehouse_id)
            ->where( 'place_id',$this->place_id)
            ->where('date', '<=',$this->start_date)

            ->whereNotNull('qty_in')->get();

            $ItemCogsShadingOut = ItemCogs::where('deleted_at',null)
            ->where('item_shading_id',$row->id)
            ->where( 'warehouse_id',$this->warehouse_id)
            ->where( 'place_id',$this->place_id)
            ->where('date', '<=',$this->start_date)

            ->whereNotNull('qty_out')->get();
            foreach ($ItemCogsShadingIn as $inawal){
                $rp_in += $inawal->total_in;
            }

            foreach ($ItemCogsShadingOut as $inOut){
                $rp_out +=   $inOut->total_out;
            }

            $total = round( $ItemCogsShadingIn->sum('qty_in') - $ItemCogsShadingOut->sum('qty_out'),3);
            $rp_total = round($rp_in - $rp_out,3);
            $arr[] = [
                'no'=> $keys,
                'item_code' =>  $row->item->code,
                'item_name' =>  $row->item->name,
                'unit'      =>  $row->item->uomUnit->code,
                'shading'   =>  $row->code,
                'total'     =>  $total,
                'rp_total'     =>  $rp_total
            ];
            $keys++;

        }

        foreach($itemNoShading as $key =>$row){



            $rp_in = 0;
            $rp_out = 0 ;
            $ItemCogsShadingIn = ItemCogs::where('deleted_at',null)
            ->where('item_id',$row->id)
            ->where( 'warehouse_id',$this->warehouse_id)
            ->where( 'place_id',$this->place_id)
            ->where('date', '<=',$this->start_date)

            ->whereNotNull('qty_in')->get();

            $ItemCogsShadingOut = ItemCogs::where('deleted_at',null)
            ->where('item_id',$row->id)
            ->where( 'warehouse_id',$this->warehouse_id)
            ->where( 'place_id',$this->place_id)
            ->where('date', '<=',$this->start_date)

            ->whereNotNull('qty_out')->get();
            foreach ($ItemCogsShadingIn as $inawal){
                $rp_in += $inawal->total_in;
            }

            foreach ($ItemCogsShadingOut as $inOut){
                $rp_out +=   $inOut->total_out;
            }
            $total = round($ItemCogsShadingIn->sum('qty_in') - $ItemCogsShadingOut->sum('qty_out'),3);
            $rp_total = $rp_in - $rp_out;
            $arr[] = [
                'no'=> $keys,
                'item_code' =>  $row->code,
                'item_name' =>  $row->name,
                'unit'      =>  $row->uomUnit->code,
                'shading'   =>  '-',
                'total'     =>  $total,
                'rp_total'     =>  $rp_total
            ];
            $keys++;



        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Report Stock In Rupiah - Shading';
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
