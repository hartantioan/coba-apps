<?php

namespace App\Exports;

use App\Models\MarketingOrderDeliveryDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\User;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportDeliveryScheduleReport implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $end_date;

    public function __construct(string $start_date, string $end_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->end_date = $end_date ? $end_date : '';
    }

    private $headings = [
        'No.',
        'SO Ref.',
        'No. Dokumen',
        'Status',
        'Post Date',
        'Status Kirim',
        'Tgl. Kirim',
        'Tipe Pengiriman',
        'Ekspedisi',
        'Kode Item',
        'Note',
        'Barang',
        'Plant',
        'Qty Konversi',
        'Satuan Konversi',
        'Qty',
        'Satuan',
        'Note Internal',
    ];
    public function collection()
    {
        $array_filter = [];
        $mo = MarketingOrderDeliveryDetail::whereHas('marketingOrderDelivery', function ($query) {
            $query->where('status','2')
                ->whereNotNull('send_status');
        })->get();


        foreach ($mo as $key=>$row) {

            $array_filter[] = [
                'No.' => ($key+1), // Assuming you have an ID or a similar unique identifier
                'SO Ref.' => $row->marketingOrderDetail->marketingOrder->code ?? '-',
                'No. Dokumen' => $row->marketingOrderDelivery->code,
                'Status' => $row->marketingOrderDelivery->statusRaw(),
                'Post Date' => date('d/m/Y', strtotime($row->marketingOrderDelivery->post_date)),
                'Status Kirim' => $row->marketingOrderDelivery->sendStatus(),
                'Tgl. Kirim' => date('d/m/Y', strtotime($row->marketingOrderDelivery->delivery_date)),
                'Tipe Pengiriman' => $row->marketingOrderDelivery->deliveryType(),
                'Ekspedisi' => $row->marketingOrderDelivery->costDeliveryType(),
                'Kode Item' => $row->item->code,
                'Note' => $row->note,
                'Barang' => $row->item->name,
                'Plant'=> $row->marketingOrderDetail->place->name,
                'Qty Konversi' => $row->marketingOrderDetail->qty_conversion,
                'satuan konversi' => $row->marketingOrderDetail->item->uomUnit->code,
                'Qty' => $row->qty,
                'satuan' => $row->marketingOrderDetail->itemUnit->unit->code,
                'Note_internal' => $row->marketingOrderDelivery->note_internal,

            ];
        }
        return collect($array_filter);
    }

    public function title(): string
    {
        return 'Delivery Schedule';
    }

    public function startCell(): string
    {
        return 'A1';
    }
	/**
	 * @return array
	 */
	public function headings() : array
	{
		return $this->headings;
	}
}
