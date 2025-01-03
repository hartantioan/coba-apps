<?php

namespace App\Exports;

use App\Helpers\CustomHelper;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReportCreditLimit implements FromArray,WithTitle, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        $query = User::where('type','2')->where('status','1')->get();
        $data = [];

        foreach ($query as $key => $row) {
            $unsentModCredit = CustomHelper::grandtotalUnsentModCreditReport($row->id);
            $unsentModDp = CustomHelper::grandtotalUnsentModDpReport($row->id);
            $uninvoiceDoCredit = CustomHelper::grandtotalUninvoiceDoCreditReport($row->id);
            $uninvoiceDoDp = CustomHelper::grandtotalUninvoiceDoDpReport($row->id);
            $balance = round($row->limit_credit - $row->count_limit_credit - $unsentModCredit - $unsentModDp - $uninvoiceDoCredit - $uninvoiceDoDp,2);
            $data[]= [
                'No' => $key + 1,  // Index starts at 1
                'kode_pelanggan' => $row->employee_no,
                'nama_pelanggan' => $row->name,
                'kredit_limit' => $row->limit_credit, // assuming it's already a number
                'outstand_mod_kredit' => $unsentModCredit,
                'outstand_sj_kredit' => $uninvoiceDoCredit,
                'outstand_mod_dp' => $unsentModDp,
                'outstand_sj_dp' => $uninvoiceDoDp,
                'outstand_invoice' => $row->count_limit_credit,
                'sisa_limit' => $balance
            ];

          }

        return $data;
    }

    public function title(): string
    {
        return 'Report Credit Limit';
    }

    public function headings() : array
	{
		return $this->headings;
    }

    private $headings = [
        'No.',
        'Kode Pelanggan',
        'Nama Pelanggan',
        'Kredit Limit',
        'Outstand MOD Kredit',
        'Outstand SJ Kredit',
        'Outstand MOD DP',
        'Outstand SJ DP',
        'Outstand Invoice',
        'Sisa Limit'
    ];
}
