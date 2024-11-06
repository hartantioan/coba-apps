<?php

namespace App\Exports;

use App\Models\MarketingOrderDeliveryProcessDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class ExportReportGoodScaleItemFG implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $finish_date;

    public function __construct(string $start_date, string $finish_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
    }
    private $headings =
    [
        'No',
        'Dokumen',
        'Status',
        'Tgl.Post',

        'Item Code',
        'Item Name',
        'Brand',

        'Qty (M2)',
        'Satuan',
        'Berat',

    ];



    public function collection()
    {

        $mo = MarketingOrderDeliveryProcessDetail::whereHas('marketingOrderDeliveryProcess', function ($query) {
            $query->where('post_date', '>=', $this->start_date)
                ->where('post_date', '<=', $this->finish_date);
        })->get();


        foreach ($mo as $key=>$row) {

            $arr[] = [
                'no'  => ($key+1),
                'code'              => $row->marketingOrderDeliveryProcess->code,
                'status'              => $row->marketingOrderDeliveryProcess->statusRaw(),

                'post_date'         => date('d/m/Y', strtotime($row->marketingOrderDeliveryProcess->post_date)),

                'itemcode' => $row->marketingOrderDeliveryDetail->item->code,
                'itemname' => $row->marketingOrderDeliveryDetail->item->name,
                'brand' => $row->itemStock->item->brand->name,


                'qty' => $row->qty * $row->marketingOrderDeliveryDetail->getQtyM2(),

                'satuan' => $row->itemStock->item->uomUnit->code,
                'berat' => round(($row->qty *$row->marketingOrderDeliveryDetail->getQtyM2() / $row->marketingOrderDeliveryProcess->totalQty()) * $row->marketingOrderDeliveryProcess->weight_netto,3),

            ];
        }


        return collect($arr);
    }

    public function title(): string
    {
        return 'Report Item FG Timbangan';
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
