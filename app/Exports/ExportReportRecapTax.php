<?php

namespace App\Exports;

use App\Helpers\CustomHelper;
use App\Models\MarketingOrderInvoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReportRecapTax implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $finish_date;

    public function __construct(string $start_date, string $finish_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
    }

    private $headings = [
        'No Urut',
        'Jenis Dokumen',
        'Tipe Penjualan',
        'No Seri Pajak',
        'No Dokumen',
        'Tgl Dokumen',
        'Nama NPWP',
        'No NPWP',
        'Alamat NPWP',
        'Nama Barang',
        'DPP Harga Satuan',
        'Jumlah Barang (Qty)',
        '% Diskon',
        '% Diskon 2',
        'Diskon 3',
        'DPP Diskon',
        'Total Harga Barang (DPP)',
        'Uang Muka (DP)',
        'Total',
        'DPP FP',
        'PPN FP',
        'Status Cancel',
        'Tipe Pembayaran',
        'Pembuat',
    ];

    public function collection()
    {
        $query_data = MarketingOrderInvoice::where(function ($query) {
            if ($this->start_date && $this->finish_date) {
                $query->whereDate('post_date', '>=', $this->start_date)
                    ->whereDate('post_date', '<=', $this->finish_date);
            } else if ($this->start_date) {
                $query->whereDate('post_date', '>=', $this->start_date);
            } else if ($this->finish_date) {
                $query->whereDate('post_date', '<=', $this->finish_date);
            }
        })
            ->get();

        $arr = [];
        foreach ($query_data as $key => $row) {
            $detail = [];
            $dpp_discount_total = 0;
            $dpp_total = 0;
            $dpp_fp_total = 0;
            $ppn_fp_total = 0;
            $price_dpp_total = 0;
            $price_satuan = 0;
            $total_all = 0;

            $freeAreaTax = $row->marketingOrderDeliveryProcess()->exists() ? ($row->marketingOrderDeliveryProcess->marketingOrderDelivery->getMaxTaxType() == '2' ? '18' : '') : '';
            foreach ($row->marketingOrderInvoiceDetail as $keyd => $row_detail) {
                if ($row_detail->lookable_type == 'marketing_order_delivery_details' || $row_detail->lookable_type == 'marketing_order_delivery_process_details') {
                    $dpp_discount_detail = 0;
                    $dpp_total_detail = 0;
                    $dpp_fp_detail = 0;
                    $ppn_fp_detail = 0;
                    $price_dpp_detail = 0;
                    $total_detail = 0;
                    $price_satuan = 0;

                    $percentTax = 1;

                    $jumlah_barang = 0;

                    if ($row_detail->getMarketingOrder()) {

                        $boxQty = '';
                        if ($row_detail->lookable->isPallet()) {

                            $boxQty = ' ( ' . CustomHelper::formatConditionalQty($row_detail->qty * $row_detail->getBoxConversion()) . ' BOX )';
                        }
                        $hscode = '';
                        if ($freeAreaTax) {
                            $hscode = ' ' . $row_detail->getHSCode();
                        }

                        if ($row_detail->is_include_tax == 1) {
                            $percentTax = ($row_detail->getMarketingOrder->percent_tax + 100) / 100;
                        }

                        //  $price_satuan = $row_detail->getMarketingOrder->priceWTax();
                        $price_satuan = round($row_detail->getMarketingOrder->price / $percentTax, 7);
                        $jumlah_barang = $row_detail->getMarketingOrder->qty_uom;

                        $dpp_discount_detail = round($row_detail->getMarketingOrder->price / $percentTax - $row_detail->getMarketingOrder->price_after_discount / $percentTax, 2);
                        $dpp_discount_total += $dpp_discount_detail;

                        $dpp_total_detail = round($row_detail->getMarketingOrder->price_after_discount *  $row_detail->getMarketingOrder->qty_uom / $percentTax, 2);

                        $dpp_total += $dpp_total_detail;

                        // $total_detail = round($row_detail->getMarketingOrder->total / $percentTax, 2);
                        $total_detail = round($row_detail->total, 2);
                        $total_all += $total_detail;


                        $ppn_fp_detail = $row_detail->tax;
                        $ppn_fp_total += $ppn_fp_detail;

                        // $price_dpp_detail = round((($row_detail->getMarketingOrder->price_after_discount * $row_detail->getMarketingOrder->qty_uom) - $dpp_discount_detail) / $percentTax, 2);
                        $price_dpp_detail = round((($row_detail->getMarketingOrder->price_after_discount / $percentTax)), 7);
                        $price_dpp_total += $price_dpp_detail;
                    }
                    $detail[] = [
                        'No Urut' => '',
                        'Jenis Dokumen' => $row->invoiceType(),
                        'Tipe Penjualan' => $row->soType(),
                        'No Seri Pajak' => $row->tax_no,
                        'No Dokumen' => $row->code,
                        'Tgl Dokumen' => date('d/m/Y', strtotime($row->post_date)),
                        'Nama NPWP' => $row->userData->user->name,
                        'No NPWP' => $row->getNpwp(),
                        'Alamat NPWP' => $row->userData->address,
                        'Nama Barang' => $row_detail->getPrintName() . $boxQty . $hscode,
                        'DPP Harga Satuan' => $price_satuan,
                        'Jumlah Barang (Qty)' => $jumlah_barang,
                        '% Diskon' => $row_detail->getMarketingOrder() ? $row_detail->getMarketingOrder->percent_discount_1 : '',
                        '% Diskon 2' => $row_detail->getMarketingOrder() ? $row_detail->getMarketingOrder->percent_discount_2 : '',
                        'Diskon 3' => $row_detail->getMarketingOrder() ? $row_detail->getMarketingOrder->discount_3 : '',
                        'DPP Diskon' => $dpp_discount_detail,
                        'Total Harga Barang (DPP)' => $price_dpp_detail,
                        'Uang Muka (DP)' => '',
                        'Total' =>  $total_detail,
                        'DPP FP' => $total_detail,
                        'PPN FP' => $ppn_fp_detail,
                        'Status Cancel' => '',
                        'Tipe Pembayaran' => '',
                        'Pembuat' => '',
                    ];
                }
            }


            $header = [
                'No Urut' => ($key + 1),
                'Jenis Dokumen' => $row->invoiceType(),
                'Tipe Penjualan' => $row->soType(),
                'No Seri Pajak' => $row->tax_no,
                'No Dokumen' => $row->code,
                'Tgl Dokumen' => date('d/m/Y', strtotime($row->post_date)),
                'Nama NPWP' => $row->userData->user->name,
                'No NPWP' => $row->getNpwp(),
                'Alamat NPWP' => $row->userData->address,
                'Nama Barang' => '',
                'DPP Harga Satuan' => '',
                'Jumlah Barang (Qty)' => '',
                '% Diskon' => '',
                '% Diskon 2' => '',
                'Diskon 3' => '',
                'DPP Diskon' => '',
                'Total Harga Barang (DPP)' => '',
                'Uang Muka (DP)' => $row->downpayment,
                'Total' =>  $row->total,
                'DPP FP' => $row->total,
                'PPN FP' => $row->tax,
                'Status Cancel' => $row->statusRaw(),
                'Tipe Pembayaran' => $row->type(),
                'Pembuat' => $row->user->name,
            ];

            $arr[] = $header;
            $arr[] = $detail;
        }


        return collect($arr);
    }

    public function title(): string
    {
        return 'Invoice REKAP Penjualan';
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
