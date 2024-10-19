<?php

namespace App\Exports;

use App\Models\MarketingOrderDeliveryProcessDetail;
use App\Models\MarketingOrderDeliveryProcessTrack;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReportDeliveryOnTheWay implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $finish_date;

    public function __construct(string $start_date, string $finish_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
    }

    private $headings = [
        'Status DO',
        'No DO',
        'Tanggal DO',
        'Barang Diterima',
        'Item Code',
        'Item Name',
        'Qty',
        'Satuan',
        'Qty M2',
        'Value'
    ];




    public function collection()
    {


        $delivery_process = MarketingOrderDeliveryProcessDetail::where('deleted_at',null)
        ->whereHas('marketingOrderDeliveryProcess',function ($query)  {
            $query->whereIn('status',["2","3"])
            ->where('post_date', '>=',$this->start_date)
            ->where('post_date', '<=', $this->finish_date)
            ->whereHas('marketingOrderDeliveryProcessTrack',function($query){
                $query->whereIn('status',['2','3']);
            });
        })->get();


        $arr = [];

        foreach ($delivery_process as $key => $row) {
            if($row->marketingOrderDeliveryProcess->marketingOrderDeliveryProcessTrack->last()->status != '2') {
                $tgl_smpai =date('d/m/Y', strtotime($row->marketingOrderDeliveryProcess->marketingOrderDeliveryProcessTrack->last()->created_at));
            }else{
                $tgl_smpai = '';
            }
            $arr[] = [
                'status'              => $row->marketingOrderDeliveryProcess->statusRaw(),
                'code'              => $row->marketingOrderDeliveryProcess->code,
                'post_date'         => date('d/m/Y', strtotime($row->marketingOrderDeliveryProcess->post_date)),
                'barang_diterima' =>  $tgl_smpai,
                'itemcode' => $row->marketingOrderDeliveryDetail->item->code,
                'itemname' => $row->marketingOrderDeliveryDetail->item->name,
                'qty' => $row->qty ,
                'satuan' => $row->itemStock->item->uomUnit->code,
                'qtym2' => $row->marketingOrderDeliveryDetail->getQtyM2(),
                'value' => $row->total,
            ];
        }


        return collect($arr);
    }

    public function title(): string
    {
        return 'Report Delivery On The Way';
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
