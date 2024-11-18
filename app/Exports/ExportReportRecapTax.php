<?php

namespace App\Exports;

use App\Helpers\CustomHelper;
use App\Models\MarketingOrderDownPayment;
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
        'Kode Barang',
        'Nama Barang',
        'DPP Harga Satuan',
        'Jumlah Barang (Qty)',

        '% Diskon',
        '% Diskon 2',
        'Diskon 3',
        'DPP Diskon / Qty',

        'Total Diskon',
        'Uang Muka (DP)',
        'Total',
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
            $total_discount = 0;
            $total_before_disc = 0;

            $freeAreaTax = $row->marketingOrderDeliveryProcess()->exists() ? ($row->marketingOrderDeliveryProcess->marketingOrderDelivery->getMaxTaxType() == '2' ? '18' : '') : '';
            foreach ($row->marketingOrderInvoiceDetail as $keyd => $row_detail) {
                if ($row_detail->lookable_type == 'marketing_order_delivery_details' || $row_detail->lookable_type == 'marketing_order_delivery_process_details') {
                    $dpp_discount_detail = 0;
                    $dpp_total_detail = 0;

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

                        $dpp_discount_detail = $row_detail->getMarketingOrder->price / $percentTax - $row_detail->getMarketingOrder->price_after_discount / $percentTax;
                        $dpp_discount_total += $dpp_discount_detail;

                        $total_discount += $dpp_discount_detail * $jumlah_barang;

                        $dpp_total_detail = round($row_detail->getMarketingOrder->price_after_discount *  $row_detail->getMarketingOrder->qty_uom / $percentTax, 2);

                        $dpp_total += $dpp_total_detail;

                        // $total_detail = round($row_detail->getMarketingOrder->total / $percentTax, 2);
                        $total_detail = round($row_detail->total, 2);
                        $total_all += $total_detail;

                        $total_before_disc += $price_satuan * $jumlah_barang;

                        $ppn_fp_detail = $row_detail->tax;
                        $ppn_fp_total += $ppn_fp_detail;

                        // $price_dpp_detail = round((($row_detail->getMarketingOrder->price_after_discount * $row_detail->getMarketingOrder->qty_uom) - $dpp_discount_detail) / $percentTax, 2);
                        $price_dpp_detail = round((($row_detail->getMarketingOrder->price_after_discount / $percentTax)), 7);
                        $price_dpp_total += $price_dpp_detail;
                    }
                    $detail[] = [
                        'No Urut' => '',
                        'Jenis Dokumen' => 'AR INVOICE',
                        'Tipe Penjualan' => $row->soType(),
                        'No Seri Pajak' => $row->tax_no,
                        'No Dokumen' => $row->code,
                        'Tgl Dokumen' => date('d/m/Y', strtotime($row->post_date)),
                        'Nama NPWP' => $row->userData->title,
                        'No NPWP' => $row->getNpwp(),
                        'Alamat NPWP' => $row->userData->address,
                        'Kode Barang' => $row_detail->getItemCode(),
                        'Nama Barang' => $row_detail->getPrintName() . $boxQty . $hscode,
                        'DPP Harga Satuan' => $price_satuan,
                        'Jumlah Barang (Qty)' => $jumlah_barang,

                        '% Diskon' => $row_detail->getMarketingOrder() ? $row_detail->getMarketingOrder->percent_discount_1 : '',
                        '% Diskon 2' => $row_detail->getMarketingOrder() ? $row_detail->getMarketingOrder->percent_discount_2 : '',
                        'Diskon 3' => $row_detail->getMarketingOrder() ? $row_detail->getMarketingOrder->discount_3 : '',
                        'DPP Diskon / Qty' => $dpp_discount_detail,
                        'Total Diskon' => $dpp_discount_detail * $jumlah_barang,
                        'Total Harga Barang (DPP)' => $price_dpp_detail,
                        'Uang Muka (DP)' => '',
                        'Total' =>  $price_satuan * $jumlah_barang,
                      
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
                'Jenis Dokumen' => 'AR INVOICE',
                'Tipe Penjualan' => $row->soType(),
                'No Seri Pajak' => $row->tax_no,
                'No Dokumen' => $row->code,
                'Tgl Dokumen' => date('d/m/Y', strtotime($row->post_date)),
                'Nama NPWP' => $row->userData->title,
                'No NPWP' => $row->getNpwp(),
                'Alamat NPWP' => $row->userData->address,
                'Kode Barang' => '',
                'Nama Barang' => '',
                'DPP Harga Satuan' => '',
                'Jumlah Barang (Qty)' => '',

                '% Diskon' => '',
                '% Diskon 2' => '',
                'Diskon 3' => '',
                'DPP Diskon' => '',
                'Total Diskon' => $total_discount,
                'Total Harga Barang (DPP)' => '',
                'Uang Muka (DP)' => $row->downpayment,
                'Total' =>  $total_before_disc,

                'DPP FP' => $row->total,
                'PPN FP' => $row->tax,
                'Status Cancel' => $row->statusRaw(),
                'Tipe Pembayaran' => $row->type(),
                'Pembuat' => $row->user->name,
            ];

            $header2 = [
                'No Urut' => '',
                'Jenis Dokumen' => '',
                'Tipe Penjualan' => '',
                'No Seri Pajak' => '',
                'No Dokumen' => '',
                'Tgl Dokumen' => '',
                'Nama NPWP' => '',
                'No NPWP' => '',
                'Alamat NPWP' => '',
                'Kode Barang' => '',
                'Nama Barang' => '',
                'DPP Harga Satuan' => '',
                'Jumlah Barang (Qty)' => '',

                '% Diskon' => '',
                '% Diskon 2' => '',
                'Diskon 3' => '',
                'DPP Diskon' => '',
                'Total Diskon' => '',
                'Total Harga Barang (DPP)' => '',
                'Uang Muka (DP)' => '',
                'Total' =>  '',

                'DPP FP' => '',
                'PPN FP' => '',
                'Status Cancel' => '',
                'Tipe Pembayaran' => '',
                'Pembuat' => '',
            ];

            $arr[] = $header;
            $arr[] = $detail;
            $arr[] = $header2;
        }




        $query_dp = MarketingOrderDownPayment::where(function ($query) {
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

        foreach ($query_dp as $index => $row_arr) {
            $user = $row_arr->user->name;
            $account = $row_arr->account->name;
            $arr[] = [
                'No Urut' => $index + 1,
                'Jenis Dokumen' => 'AR DP',
                'Tipe Penjualan' => '',
                'No Seri Pajak' => $row_arr->tax_no,
                'No Dokumen' => $row_arr->code,
                'Tgl Dokumen' => date('d/m/Y', strtotime($row_arr->post_date)),
                'No Npwp' => $row_arr->account->userDataDefault()->title,
                'Nama Npwp' => $row_arr->getNpwp(),

                'Alamat Npwp' => $row_arr->account->userDataDefault()->address,
                'Kode Barang' => '',
                'Nama Barang' => $row_arr->note,
                'DPP Harga Satuan' => $row_arr->total,
                'Jumlah Barang (Qty)' => '1',

                '% Diskon' => '0',
                '% Diskon 2' => '0',
                'Diskon 3' => '0',
                'DPP Diskon' => '0',
                'Total Diskon' => '',
                'Total Harga Barang (DPP)' => $row_arr->total,
                'Uang Muka (DP)' => '0',
                'Total' => $row_arr->total,

                'DPP FP' => $row_arr->total,
                'PPN FP' => $row_arr->tax,
                'Status Cancel' => $row_arr->statusRaw(),
                'Tipe Pembayaran' => '',
                'Pembuat' => $row_arr->user->name,
            ];
        }


        return collect($arr);
    }

    public function title(): string
    {
        return 'Invoice REKAP Pajak';
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
