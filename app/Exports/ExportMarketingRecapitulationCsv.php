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
            $transactionCode = substr($firstcode, 0, 2);
            //revCode untuk penanda kalau invoicenya 011 - direvisi
            $revCode = substr_count($firstcode, '0') == 2 ? 0 : 1;
            array_splice($arrTemp, 0, 1);
            $tax_no = implode('', $arrTemp);
            $month = date('n', strtotime($row->post_date));
            $year = date('Y', strtotime($row->post_date));
            $newdate = date('d/n/Y', strtotime($row->post_date));
            $arr[] = [
                '1'     => 'FK;' . $transactionCode . ';' . $revCode . ';' . $tax_no . ';' . $month . ';' . $year . ';' . $newdate . ';' . $row->getNpwp() . ';' . $row->account->userDataDefault()->title . ';' . $row->account->userDataDefault()->address . ';' . round($row->total, 0) . ';' . round($row->tax, 0) . ';0;;1;' . floor($row->total) . ';' . floor($row->tax) . ';0;' . $row->code . ';;'
            ];
            $arr[] = [
                '1'     => 'OF;1;' . $row->note . ';' . round($row->total, 2) . ';1.00;' . round($row->total, 2) . ';0;' . round($row->total, 2) . ';' . round($row->tax, 2) . ';0;0;;;;;;;;;;',
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
                    '1'     => 'FK;' . $transactionCode . ';' . $revCode . ';' . $tax_no . ';' . $month . ';' . $year . ';' . $newdate . ';' . $row->getNpwp() . ';' . $row->userData->title . ';' . $row->userData->address . ';' . floor($row->total) . ';' . floor($row->tax) . ';0;' . $freeAreaTax . ';0;0;0;0;' . $row->code . ';' . ($row->no_pjb ?? '') . ';'
                ];
                $balance = floor($row->tax);
            } else {
                $arr[] = [
                    '1'     => 'FK;' . $transactionCode . ';' . $revCode . ';' . $tax_no . ';' . $month . ';' . $year . ';' . $newdate . ';' . $row->getNpwp() . ';' . $row->userData->title . ';' . $row->userData->address . ';' . floor($row->subtotal) . ';' . floor($row->subtotal * ($row->taxMaster->percentage / 100)) . ';0;' . $freeAreaTax . ';2;0;0;0;' . $row->code . ';' . ($row->no_pjb ?? '') . ';'
                ];
                $balance = floor($row->subtotal * ($row->taxMaster->percentage / 100));
            }
            foreach ($row->marketingOrderInvoiceDetail()->where('lookable_type', 'marketing_order_delivery_process_details')->get() as $key2 => $rowdetail) {
                if ($key2 == ($row->marketingOrderInvoiceDetail()->count() - 1)) {
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

                $price = $rowdetail->priceBeforeTax();
                $totalBeforeTax = round($rowdetail->totalBeforeTax(), 2);
                $totalDiscountBeforeTax = round($rowdetail->totalDiscountBeforeTax(), 2);

                $arr[] = [
                    '1'     => 'OF;' . $rowdetail->lookable->itemStock->item->code . ';' . $rowdetail->lookable->itemStock->item->print_name . $boxQty . $hscode . ';' . round($price, 2) . ';' . round($rowdetail->qty * $rowdetail->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion, 2) . ';' . $totalBeforeTax . ';' . $totalDiscountBeforeTax . ';' . round($rowdetail->total, 2) . ';' . $tax . ';0;0;;;;;;;;;;',
                ];
                $balance -= $tax;
            }
            foreach ($row->marketingOrderInvoiceDetail()->where('lookable_type', 'marketing_order_delivery_details')->get() as $key2 => $rowdetail) {

                if ($key2 == ($row->marketingOrderInvoiceDetail()->count() - 1)) {
                    $tax = $balance;
                } else {
                    $tax = $rowdetail->proportionalTaxFromHeader().'-kambing';
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

                $price = $rowdetail->priceBeforeTax();
                $totalBeforeTax = round($rowdetail->totalBeforeTax(), 2);
                $totalDiscountBeforeTax = round($rowdetail->totalDiscountBeforeTax(), 2);

                $arr[] = [
                    '1'     => 'OF;' . $rowdetail->lookable->item->code . ';' . $rowdetail->lookable->item->print_name . $boxQty . $hscode . ';' . round($price, 2) . ';' . round($rowdetail->qty * $rowdetail->lookable->marketingOrderDetail->qty_conversion, 2) . ';' . $totalBeforeTax . ';' . $totalDiscountBeforeTax . ';' . round($rowdetail->total, 2) . ';' . $tax . ';0;0;;;;;;;;;;',
                ];
                $balance -= $tax;
            }
            foreach ($row->marketingOrderInvoiceDetail()->whereNull('lookable_type')->get() as $key2 => $rowdetail) {
                $price = $rowdetail->priceBeforeTax();
                $totalBeforeTax = round($rowdetail->totalBeforeTax(), 2);
                $totalDiscountBeforeTax = round($rowdetail->totalDiscountBeforeTax(), 2);
                $arr[] = [
                    '1'     => 'OF;;' . $rowdetail->description . ';' . round($price, 2) . ';' . round($rowdetail->qty, 2) . ';' . round($rowdetail->total, 2) . ';0;' . round($rowdetail->total, 2) . ';' . round($rowdetail->tax,2) . ';0;0;;;;;;;;;;',
                ];
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
