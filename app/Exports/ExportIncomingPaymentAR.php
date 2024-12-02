<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\IncomingPaymentDetail;
use App\Helpers\CustomHelper;

class ExportIncomingPaymentAR implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $tanggal1,$tanggal2;

    public function __construct(string $tanggal1, string $tanggal2)
    {
        $this->tanggal1 = $tanggal1 ? $tanggal1 : '';
        $this->tanggal2 = $tanggal2 ? $tanggal2 : '';
      
    }

    private $headings = [
        'Tanggal',
        'Nomor',
        'Bank',
        'Customer',
        'ARDP / ARIN No',
        'Tanggal',
        'Pay on the day AR of',
        'Value',
        'Remark',

    ];

    public function startCell(): string
    {
        return 'A1';
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return 'Incoming Payment';
    }

    public function collection()
    {

        $data = IncomingPaymentDetail::whereHas('IncomingPayment', function ($query) {
            $query->whereIn('status', ['2', '3'])->whereNotNull('account_id')->where('post_date', '>=', $this->tanggal1)->where('post_date', '<=', $this->tanggal2);
        })->get();
        $array = [];
        foreach ($data as $row) {
            if ($row->lookable_type == "marketing_order_invoices") {
                $array[] = [
                    'tanggal' => date('d/m/Y', strtotime($row->IncomingPayment->post_date)),
                    'code' => $row->IncomingPayment->code,
                    'bank' => $row->IncomingPayment->coa->name,
                    'customer' => $row->IncomingPayment->account->name,
                    'no' => $row->marketingOrderInvoice->code,
                    'tanggaldok' => date('d/m/Y', strtotime($row->marketingOrderInvoice->post_date)),
                    'usia' => CustomHelper::countDays($row->marketingOrderInvoice->post_date, $row->IncomingPayment->post_date),
                    'nominal' => $row->total,
                    'note' => $row->note,
                ];
            }
            if ($row->lookable_type == "marketing_order_down_payments") {
                $array[] = [
                    'tanggal' => date('d/m/Y', strtotime($row->IncomingPayment->post_date)),
                    'code' => $row->IncomingPayment->code,
                    'bank' => $row->IncomingPayment->coa->name,
                    'customer' => $row->IncomingPayment->account->name,
                    'no' => $row->marketingOrderDownPayment->code,
                    'tanggaldok' => date('d/m/Y', strtotime($row->marketingOrderDownPayment->post_date)),
                    'usia' => CustomHelper::countDays($row->marketingOrderDownPayment->post_date, $row->IncomingPayment->post_date),
                    'nominal' => $row->total,
                    'note' => $row->note,
                ];
            }
        }

        return collect($array);
    }
}
