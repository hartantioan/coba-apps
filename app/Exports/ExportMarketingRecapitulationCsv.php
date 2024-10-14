<?php

namespace App\Exports;

use App\Models\MarketingOrderDownPayment;
use App\Models\MarketingOrderInvoice;
use App\Models\PaymentRequest;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExportMarketingRecapitulationCsv implements FromCollection, WithTitle, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $start_date, $end_date, $mode;

    public function __construct(string $start_date, string $end_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->end_date = $end_date ? $end_date : '';
    }

    public function collection()
    {
        $ardp = MarketingOrderDownPayment::whereIn('status', ['2', '3'])
            ->whereDate('post_date', '>=', $this->start_date)
            ->whereDate('post_date', '<=', $this->end_date)
            ->whereNotNull('tax_no')
            ->where('tax_no','!=','')
            ->get();

        $invoice = MarketingOrderInvoice::whereIn('status', ['2', '3'])
            ->whereDate('post_date', '>=', $this->start_date)
            ->whereDate('post_date', '<=', $this->end_date)
            ->whereNotNull('tax_no')
            ->where('tax_no','!=','')
            ->get();

        $arr = [];

        $arr[] = [
            '1'     => 'Jenis;KD_JENIS_TRANSAKSI;FG_PENGGANTI;Nomor Faktur / Dokumen;Masa Pajak;Tahun Pajak;Tanggal Faktur;NPWP/Nomor Paspor;NAMA;ALAMAT_LENGKAP;JUMLAH_DPP;JUMLAH_PPN;JUMLAH_PPNBM;ID_KETERANGAN_TAMBAHAN;FG_UANG_MUKA;UANG_MUKA_DPP;UANG_MUKA_PPN;UANG_MUKA_PPNBM;REFERENSI;KODE_DOKUMEN_PENDUKUNG;'
        ];

        $arr[] = [
            '1'     => 'LT;NPWP;NAMA;JALAN;BLOK;NOMOR;RT;RW;KECAMATAN;KELURAHAN;KABUPATEN;PROPINSI;KODE_POS;NOMOR_TELEPON;;;;;;;'
        ];

        $arr[] = [
            '1'     => 'OF;KODE_OBJEK;NAMA;HARGA_SATUAN;JUMLAH_BARANG;HARGA_TOTAL;DISKON;DPP;PPN;TARIF_PPNBM;PPNBM;;;;;;;;;;'
        ];

        foreach ($ardp as $key => $row) {
            $arrTemp = explode('.', $row->tax_no);
            $firstcode = preg_replace('/\s+/', '', $arrTemp[0]);
            $transactionCode = substr_count($firstcode, '0') == 2 ? substr($firstcode, 0, 2) : intval($firstcode);
            array_splice($arrTemp, 0, 1);
            $tax_no = implode('', $arrTemp);
            $month = date('n', strtotime($row->post_date));
            $year = date('Y', strtotime($row->post_date));
            $newdate = date('d/n/Y', strtotime($row->post_date));
            $arr[] = [
                '1'     => 'FK;' . $transactionCode . ';0;' . $tax_no . ';' . $month . ';' . $year . ';' . $newdate . ';' . $row->getNpwp() . ';' . $row->account->userDataDefault()->title . ';' . $row->account->userDataDefault()->address . ';' . round($row->total, 0) . ';' . round($row->tax, 0) . ';0;;1;' . floor($row->total) . ';' . floor($row->tax) . ';0;' . $row->code . ';;'
            ];
            $arr[] = [
                '1'     => 'OF;1;' . $row->note . ';' . round($row->total, 2) . ';1.00;' . round($row->total, 2) . ';0;' . round($row->total, 2) . ';' . round($row->tax, 2) . ';0;0;;;;;;;;;;',
            ];
        }

        foreach ($invoice as $key => $row) {
            $arrTemp = explode('.', $row->tax_no);
            $firstcode = preg_replace('/\s+/', '', $arrTemp[0]);
            $transactionCode = substr_count($firstcode, '0') == 2 ? substr($firstcode, 0, 2) : intval($firstcode);
            array_splice($arrTemp, 0, 1);
            $tax_no = implode('', $arrTemp);
            $month = date('n', strtotime($row->post_date));
            $year = date('Y', strtotime($row->post_date));
            $newdate = date('d/n/Y', strtotime($row->post_date));
            if ($row->total > 0) {
                $arr[] = [
                    '1'     => 'FK;' . $transactionCode . ';0;' . $tax_no . ';' . $month . ';' . $year . ';' . $newdate . ';' . $row->getNpwp() . ';' . $row->userData->title . ';' . $row->userData->address . ';' . floor($row->total) . ';' . floor($row->tax) . ';0;;0;0;0;0;' . $row->code . ';;'
                ];
            } else {
                $arr[] = [
                    '1'     => 'FK;' . $transactionCode . ';0;' . $tax_no . ';' . $month . ';' . $year . ';' . $newdate . ';' . $row->getNpwp() . ';' . $row->userData->title . ';' . $row->userData->address . ';' . floor($row->subtotal) . ';' . floor($row->subtotal*($row->taxMaster->percentage/100)) . ';0;;2;0;0;0;' . $row->code . ';;'
                ];
            }
            $balance = floor($row->tax);
            foreach ($row->marketingOrderInvoiceDetail()->where('lookable_type', 'marketing_order_delivery_process_details')->get() as $key => $rowdetail) {
                if($key == ($row->marketingOrderInvoiceDetail()->count() - 1)){
                    $tax = $balance;
                }else{
                    $tax = $rowdetail->proportionalTaxFromHeader();
                }
                $arr[] = [
                    '1'     => 'OF;' . $rowdetail->lookable->itemStock->item->code . ';' . $rowdetail->lookable->itemStock->item->name . ';' . round($rowdetail->price, 2) . ';' . round($rowdetail->qty * $rowdetail->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion, 2) . ';' . round($rowdetail->total, 2) . ';0;' . round($rowdetail->total, 2) . ';' . $tax . ';0;0;;;;;;;;;;',
                ];
                $balance -= $tax;
            }
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Format Csv Pajak';
    }

    public function startCell(): string
    {
        return 'A1';
    }
}
