<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\IncomingPaymentDetail;
use App\Models\IncomingPayment;
use App\Helpers\CustomHelper;

class ExportIncomingPaymentAR implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $tanggal1, $tanggal2;

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

        $data = IncomingPayment::where(function ($query) {
            $query->where('post_date', '>=', $this->tanggal1)
                ->where('post_date', '<=', $this->tanggal2)->where('account_id', '!=', 'null');
        })
            ->get();
        $array = [];
        foreach ($data as $row) {
            foreach ($row->incomingPaymentDetail as $rowDetail) {
                $array[] = [
                    'tanggal' => date('d/m/Y', strtotime($row->post_date)),
                    'code' => $row->code,
                    'bank' => $row->coa->name,
                    'customer' => $row->account->name,
                    'no' => $rowDetail->getCode(),
                    'nominal' => $rowDetail->subtotal,
                    'note' => $row->note,
                ];
            }
        }

        return collect($array);
    }
}
