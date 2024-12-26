<?php

namespace App\Exports;

use App\Helpers\CustomHelper;
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
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class ExportMarketingRecapitulationCsv2 extends \PhpOffice\PhpSpreadsheet\Cell\StringValueBinder implements FromCollection, WithCustomValueBinder, WithCustomCsvSettings, WithColumnFormatting
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
            ->where('tax_no', '!=', '')
            ->get();

        $invoice = MarketingOrderInvoice::whereIn('status', ['2', '3'])
            ->whereDate('post_date', '>=', $this->start_date)
            ->whereDate('post_date', '<=', $this->end_date)
            ->whereNotNull('tax_no')
            ->where('tax_no', '!=', '')
            ->get();

        $arr = [];

        $arr[] = [
            '1'     => 'Jenis',
            '2'     => 'KD_JENIS_TRANSAKSI',
            '3'     => 'FG_PENGGANTI',
            '4'     => 'Nomor Faktur / Dokumen',
            '5'     => 'Masa Pajak',
            '6'     => 'Tahun Pajak',
            '7'     => 'Tanggal Faktur',
            '8'     => 'NPWP/Nomor Paspor',
            '9'     => 'NAMA',
            '10'    => 'ALAMAT_LENGKAP',
            '11'    => 'JUMLAH_DPP',
            '12'    => 'JUMLAH_PPN',
            '13'    => 'JUMLAH_PPNBM',
            '14'    => 'ID_KETERANGAN_TAMBAHAN',
            '15'    => 'FG_UANG_MUKA',
            '16'    => 'UANG_MUKA_DPP',
            '17'    => 'UANG_MUKA_PPN',
            '18'    => 'UANG_MUKA_PPNBM',
            '19'    => 'REFERENSI',
            '20'    => 'KODE_DOKUMEN_PENDUKUNG',
        ];

        $arr[] = [
            '1'     => 'LT',
            '2'     => 'NPWP',
            '3'     => 'NAMA',
            '4'     => 'JALAN',
            '5'     => 'BLOK',
            '6'     => 'NOMOR',
            '7'     => 'RT',
            '8'     => 'RW',
            '9'     => 'KECAMATAN',
            '10'    => 'KELURAHAN',
            '11'    => 'KABUPATEN',
            '12'    => 'PROPINSI',
            '13'    => 'KODE_POS',
            '14'    => 'NOMOR_TELEPON',
            '15'    => '',
            '16'    => '',
            '17'    => '',
            '18'    => '',
            '19'    => '',
            '20'    => '',
        ];

        $arr[] = [
            '1'     => 'OF',
            '2'     => 'KODE_OBJEK',
            '3'     => 'NAMA',
            '4'     => 'HARGA_SATUAN',
            '5'     => 'JUMLAH_BARANG',
            '6'     => 'HARGA_TOTAL',
            '7'     => 'DISKON',
            '8'     => 'DPP',
            '9'     => 'PPN',
            '10'    => 'TARIF_PPNBM',
            '11'    => 'PPNBM',
            '12'    => '',
            '13'    => '',
            '14'    => '',
            '15'    => '',
            '16'    => '',
            '17'    => '',
            '18'    => '',
            '19'    => '',
            '20'    => '',
        ];

        foreach ($ardp as $key => $row) {
            $arrTemp = explode('.', $row->tax_no);
            $firstcode = preg_replace('/\s+/', '', $arrTemp[0]);
            $transactionCode = substr($firstcode, 0, 2);
            //revCode untuk penanda kalau invoicenya 011 - direvisi
            $revCode = substr_count($firstcode, '0') == 2 ? 0 : 1;
            array_splice($arrTemp, 0, 1);
            $tax_no = implode('', $arrTemp);
            $month = date('n', strtotime($row->post_date));
            $year = date('Y', strtotime($row->post_date));
            $newdate = date('d/n/Y', strtotime($row->post_date));
            $arr[] = [
                '1'     => 'FK',
                '2'     => $transactionCode,
                '3'     => $revCode,
                '4'     => $tax_no,
                '5'     => $month,
                '6'     => $year,
                '7'     => $newdate,
                '8'     => $row->getNpwp(),
                '9'     => $row->account->userDataDefault()->title,
                '10'    => $row->account->userDataDefault()->address,
                '11'    => round($row->total, 0),
                '12'    => round($row->tax, 0),
                '13'    => '0',
                '14'    => '',
                '15'    => '1',
                '16'    => floor($row->total),
                '17'    => floor($row->tax),
                '18'    => '0',
                '19'    => $row->code,
                '20'    => '',
            ];
            $arr[] = [
                '1'     => 'OF',
                '2'     => '1',
                '3'     => $row->note,
                '4'     => round($row->total, 2),
                '5'     => '1.00',
                '6'     => round($row->total, 2),
                '7'     => '0',
                '8'     => round($row->total, 2),
                '9'     => round($row->tax, 2),
                '10'    => '0',
                '11'    => '0',
                '12'    => '',
                '13'    => '',
                '14'    => '',
                '15'    => '',
                '16'    => '',
                '17'    => '',
                '18'    => '',
                '19'    => '',
                '20'    => '',
            ];
        }

        foreach ($invoice as $key => $row) {
            $freeAreaTax = $row->marketingOrderDeliveryProcess()->exists() ? ($row->marketingOrderDeliveryProcess->marketingOrderDelivery->getMaxTaxType() == '2' ? '18' : '') : '';
            $arrTemp = explode('.', $row->tax_no);
            $firstcode = preg_replace('/\s+/', '', $arrTemp[0]);
            $transactionCode = substr($firstcode, 0, 2);
            //revCode untuk penanda kalau invoicenya 011 - direvisi
            $revCode = substr_count($firstcode, '0') == 2 ? 0 : 1;
            array_splice($arrTemp, 0, 1);
            $tax_no = implode('', $arrTemp);
            $month = date('n', strtotime($row->post_date));
            $year = date('Y', strtotime($row->post_date));
            $newdate = date('d/n/Y', strtotime($row->post_date));
            if ($row->total > 0) {
                $arr[] = [
                    '1'     => 'FK',
                    '2'     => $transactionCode,
                    '3'     => $revCode,
                    '4'     => $tax_no,
                    '5'     => $month,
                    '6'     => $year,
                    '7'     => $newdate,
                    '8'     => $row->getNpwp(),
                    '9'     => $row->userData->title,
                    '10'    => $row->userData->address,
                    '11'    => floor($row->total),
                    '12'    => floor($row->tax),
                    '13'    => '0',
                    '14'    => $freeAreaTax,
                    '15'    => '0',
                    '16'    => '0',
                    '17'    => '0',
                    '18'    => '0',
                    '19'    => $row->code,
                    '20'    => $row->no_pjb ?? '',
                ];
            } else {
                $arr[] = [
                    '1'     => 'FK',
                    '2'     => $transactionCode,
                    '3'     => $revCode,
                    '4'     => $tax_no,
                    '5'     => $month,
                    '6'     => $year,
                    '7'     => $newdate,
                    '8'     => $row->getNpwp(),
                    '9'     => $row->userData->title,
                    '10'    => $row->userData->address,
                    '11'    => floor($row->subtotal),
                    '12'    => floor($row->subtotal * ($row->taxMaster->percentage / 100)),
                    '13'    => '0',
                    '14'    => $freeAreaTax,
                    '15'    => '2',
                    '16'    => '0',
                    '17'    => '0',
                    '18'    => '0',
                    '19'    => $row->code,
                    '20'    => $row->no_pjb ?? '',
                ];
            }
            $balance = floor($row->tax);
            foreach ($row->marketingOrderInvoiceDetail()->where('lookable_type', 'marketing_order_delivery_process_details')->get() as $key => $rowdetail) {
                if ($key == ($row->marketingOrderInvoiceDetail()->count() - 1)) {
                    $tax = $balance;
                } else {
                    $tax = $rowdetail->proportionalTaxFromHeader();
                }
                $hscode = '';
                if ($freeAreaTax) {
                    $hscode = ' ' . $rowdetail->lookable->itemStock->item->type->hs_code;
                }
                $boxQty = '';
                if (date('Y-m-d', strtotime($row->created_at)) >= '2024-12-03') {
                    if ($rowdetail->lookable->isPallet() || $rowdetail->lookable->isBox()) {
                        $boxQty = ' ( ' . CustomHelper::formatConditionalQty($rowdetail->qty * $rowdetail->lookable->itemStock->item->pallet->box_conversion) . ' BOX )';
                    }
                }else{
                    if ($rowdetail->lookable->isPallet()) {
                        $boxQty = ' ( ' . CustomHelper::formatConditionalQty($rowdetail->qty * $rowdetail->lookable->itemStock->item->pallet->box_conversion) . ' BOX )';
                    }
                }

                if (date('Y-m-d', strtotime($row->created_at)) >= '2024-11-18') {
                    $price = $rowdetail->priceBeforeTax();
                    $totalBeforeTax = round($rowdetail->totalBeforeTax(), 2);
                    $totalDiscountBeforeTax = round($rowdetail->totalDiscountBeforeTax(), 2);
                } else {
                    $price = $rowdetail->price;
                    $totalBeforeTax = round($rowdetail->total, 2);
                    $totalDiscountBeforeTax = 0;
                }

                $arr[] = [
                    '1'     => 'OF',
                    '2'     => $rowdetail->lookable->itemStock->item->code,
                    '3'     => $rowdetail->lookable->itemStock->item->print_name . $boxQty . $hscode,
                    '4'     => round($price, 2),
                    '5'     => round($rowdetail->qty * $rowdetail->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion, 2),
                    '6'     => $totalBeforeTax,
                    '7'     => $totalDiscountBeforeTax,
                    '8'     => round($rowdetail->total, 2),
                    '9'     => $tax,
                    '10'    => '0',
                    '11'    => '0',
                    '12'    => '',
                    '13'    => '',
                    '14'    => '',
                    '15'    => '',
                    '16'    => '',
                    '17'    => '',
                    '18'    => '',
                    '19'    => '',
                    '20'    => '',
                ];
                $balance -= $tax;
            }
            foreach ($row->marketingOrderInvoiceDetail()->where('lookable_type', 'marketing_order_delivery_details')->get() as $key => $rowdetail) {

                if ($key == ($row->marketingOrderInvoiceDetail()->count() - 1)) {
                    $tax = $balance;
                } else {
                    $tax = $rowdetail->proportionalTaxFromHeader();
                }


                $hscode = '';
                if ($freeAreaTax) {
                    $hscode = ' ' . $rowdetail->lookable->item->type->hs_code;
                }
                $boxQty = '';
                if (date('Y-m-d', strtotime($row->created_at)) >= '2024-12-03') {
                    if ($rowdetail->lookable->isPallet() || $rowdetail->lookable->isBox()) {
                        $boxQty = ' ( ' . CustomHelper::formatConditionalQty($rowdetail->qty * $rowdetail->lookable->item->pallet->box_conversion) . ' BOX )';
                    }
                }else{
                    if ($rowdetail->lookable->isPallet()) {
                        $boxQty = ' ( ' . CustomHelper::formatConditionalQty($rowdetail->qty * $rowdetail->lookable->item->pallet->box_conversion) . ' BOX )';
                    }
                }

                if (date('Y-m-d', strtotime($row->created_at)) >= '2024-11-18') {
                    $price = $rowdetail->priceBeforeTax();
                    $totalBeforeTax = round($rowdetail->totalBeforeTax(), 2);
                    $totalDiscountBeforeTax = round($rowdetail->totalDiscountBeforeTax(), 2);
                } else {
                    $price = $rowdetail->price;
                    $totalBeforeTax = round($rowdetail->total, 2);
                    $totalDiscountBeforeTax = 0;
                }

                $arr[] = [
                    '1'     => 'OF',
                    '2'     => $rowdetail->lookable->item->code,
                    '3'     => $rowdetail->lookable->item->print_name . $boxQty . $hscode,
                    '4'     => round($price, 2),
                    '5'     => round($rowdetail->qty * $rowdetail->lookable->marketingOrderDetail->qty_conversion, 2),
                    '6'     => $totalBeforeTax,
                    '7'     => $totalDiscountBeforeTax,
                    '8'     => round($rowdetail->total, 2),
                    '9'     => $tax,
                    '10'    => '0',
                    '11'    => '0',
                    '12'    => '',
                    '13'    => '',
                    '14'    => '',
                    '15'    => '',
                    '16'    => '',
                    '17'    => '',
                    '18'    => '',
                    '19'    => '',
                    '20'    => '',
                ];
                $balance -= $tax;
            }
            foreach ($row->marketingOrderInvoiceDetail()->whereNull('lookable_type')->get() as $key => $rowdetail) {
                $price = $rowdetail->priceBeforeTax();
                $totalBeforeTax = round($rowdetail->totalBeforeTax(), 2);
                $totalDiscountBeforeTax = round($rowdetail->totalDiscountBeforeTax(), 2);
                
                $arr[] = [
                    '1'     => 'OF',
                    '2'     => '',
                    '3'     => $rowdetail->description,
                    '4'     => round($price, 2),
                    '5'     => round($rowdetail->qty, 2),
                    '6'     => round($rowdetail->total, 2),
                    '7'     => '0',
                    '8'     => round($rowdetail->total, 2),
                    '9'     => round($rowdetail->tax,2),
                    '10'    => '0',
                    '11'    => '0',
                    '12'    => '',
                    '13'    => '',
                    '14'    => '',
                    '15'    => '',
                    '16'    => '',
                    '17'    => '',
                    '18'    => '',
                    '19'    => '',
                    '20'    => '',
                ];
            }
        }

        return collect($arr);
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',  // You can set a different delimiter here if needed
            'enclosure' => '"',  // Enclosure for text values
            'escape' => '\\',    // Escape character for CSV
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT,
        ];
    }
}
