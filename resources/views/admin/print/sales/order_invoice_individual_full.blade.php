@php
    use App\Helpers\CustomHelper;
@endphp
<!doctype html>
<html lang="en">
    <head>
        <style>
            @font-face { font-family: 'china'; font-style: normal; src: url({{ storage_path('fonts/chinese_letter.ttf') }}) format('truetype'); }
            body { font-family: 'china', Tahoma, Arial, sans-serif;}
            .break-row {
                page-break-inside: avoid;
            }
            .row {
            margin-left:-5px;
            margin-right:-5px;
            }

            .column1 {
            float: left;
            width: 50%;
            padding: 5px;
            }
            .column2 {
                margin-left: 10%;
                float: left;
                width: 50%;
                padding: 5px;
            }
            /* Clearfix (clear floats) */
            .row::after {
                content: "";
                clear: both;
                display: table;
            }
            td {
                vertical-align:top !important;
            }
            @media only screen and (max-width : 768px) {
                .invoice-print-area {
                    zoom:0.4;
                }
            }

            @media only screen and (max-width : 992px) {
                .invoice-print-area {
                    zoom:0.6;
                    font-size:9px !important;
                }

                table > thead > tr > th {

                    font-size:11px !important;
                    font-weight: 800 !important;
                }
                td{
                    font-size:1em !important;

                }
            }

            @media print {
                .invoice-print-area {
                    font-size:11px !important;
                }

                table > thead > tr > th {
                    font-size:13px !important;
                    font-weight: 800 !important;
                }

                td {
                    border:none !important;
                    border-bottom: none;
                    border: solid white !important;
                    padding: 1px !important;
                    vertical-align:top !important;
                }

                body {
                    background-color:white !important;
                    zoom:0.8;
                }

                .modal {
                    background-color:white !important;
                }

                .card {
                    background-color:white !important;
                    padding:25px !important;
                }

                .invoice-print-area {
                    color: #000000 !important;
                }

                .invoice-subtotal {
                    color: #000000 !important;
                }

                .invoice-info {
                    font-size:10px !important;
                }

                .modal {
                    position: absolute;
                    left: 0;
                    top: 0;
                    margin: 0;
                    padding: 0;
                    visibility: visible;
                    overflow: visible !important;
                    min-width:100% !important;
                }

                .modal-content {
                    visibility: visible !important;
                    overflow: visible !important;
                    padding: 0px !important;
                }

                .modal-footer {
                    display:none !important;
                }

                .row .col {
                    padding:0px !important;
                }
            }

            .invoice-product-details{
                border:1px solid black;
                min-height: auto;
            }
            @page { margin: 4em 2em 4em 2em; }
            header { position: fixed; top: -50px; left: 0px; right: 0px; height: 100px; margin-bottom: 10em }



        </style>
    </head>
    <body>
        <header>
            <table border="0" width="100%">
                <tr>
                    <td width="33%">
                        <img src="{{ $image }}" width="50%" style="width:60%;">
                    </td>
                    <td width="33%" align="center">
                        <h5 style="margin-top:0px;">PROFORMA INVOICE</h5>
                        <h5 style="margin-top:-15px;">{{ $data->code }}</h5>
                    </td>
                    <td width="33%">
                        {{-- <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($data->code, 'C128')}}" alt="barcode" style="width:60%;right:75px;height:25px;"/><br>
                        {{ $data->code }} --}}
                        <h6 style="margin-top:0px;">{{ $data->company->name }}</h6>
                        <div style="margin-top:-25px;font-size:8px;">
                            {{ $data->company->npwp_address }}
                        </div>
                        <div style="font-size:8px;">
                            {{ $data->company->npwp_no }}
                        </div>
                    </td>
                </tr>
            </table>
        </header>
        <main>
            <div class="card" style="margin-top:15px;">
                <div class="card-content invoice-print-area">
                    <!-- header section -->
                    <table border="0" width="100%">
                        <tr>
                            <td width="60%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="20%">
                                            Kepada Yth
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="80%">
                                            {{ $data->userData->title }}
                                            <br>
                                            {{ $data->userData->address }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="40%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="30%">
                                            Tanggal
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="70%">
                                            {{ date('d/m/Y',strtotime($data->post_date)) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Jatuh Tempo
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ date('d/m/Y',strtotime($data->due_date)) }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <table border="0" width="100%">
                        <tr>
                            <td width="60%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="20%">
                                            No. MOD
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="80%">
                                            {{ $data->marketingOrderDeliveryProcess()->exists() ? $data->marketingOrderDeliveryProcess->marketingOrderDelivery->code : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="20%">
                                            No. DO
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="80%">
                                            {{ $data->marketingOrderDeliveryProcess()->exists() ? $data->marketingOrderDeliveryProcess->code : '-' }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="40%" class="left-align">

                            </td>
                        </tr>
                    </table>
                    @php
                        $freeAreaTax = $data->marketingOrderDeliveryProcess()->exists() ? ($data->marketingOrderDeliveryProcess->marketingOrderDelivery->getMaxTaxType() == '2' ? '18' : '') : '';
                    @endphp
                    <div class="invoice-product-details mt-2" style="overflow:auto;">
                        <table style="border-collapse:collapse;border:none;" width="100%" style="min-height: 150px !important">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Nama Barang</th>
                                    <th>Palet</th>
                                    <th>{{ __('translations.qty') }}</th>
                                    <th>UoM</th>
                                    <th>Harga</th>
                                    <th>Disc 1 (%)</th>
                                    <th>Disc 2 (%)</th>
                                    <th>Disc (Rp)</th>
                                    <th>{{ __('translations.subtotal') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data->marketingOrderInvoiceDeliveryProcessDetail as $key => $row)
                                @php
                                    $boxQty = '';
                                    /* if($row->lookable->isPallet()){
                                        $boxQty = ' ( '.CustomHelper::formatConditionalQty($row->qty * $row->lookable->itemStock->item->pallet->box_conversion).' BOX )';
                                    } */
                                    $hscode = '';
                                    if($freeAreaTax){
                                        $hscode = ' '.$row->lookable->itemStock->item->type->hs_code;
                                    }
                                @endphp
                                <tr>
                                    <td align="center">{{ ($key + 1) }}</td>
                                    <td align="">{{ $row->lookable->itemStock->item->code.' - '.$row->lookable->itemStock->item->name.$boxQty.$hscode }}</td>
                                    <td align="right">{{ CustomHelper::formatConditionalQty($row->lookable->qty) }}</td>
                                    <td align="right">{{ CustomHelper::formatConditionalQty(round($row->lookable->qty * $row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion,3)) }}</td>
                                    <td align="center">{{ $row->lookable->itemStock->item->uomUnit->code }}</td>
                                    <td align="right">{{ number_format($row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->price,2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->percent_discount_1,2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->percent_discount_2,2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->discount_3,2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->total,2,',','.') }}</td>
                                </tr>
                                @endforeach
                                @foreach($data->marketingOrderInvoiceDeliveryDetail as $key => $row)
                                @php
                                    $boxQty = '';
                                    /* if($row->lookable->isPallet()){
                                        $boxQty = ' ( '.CustomHelper::formatConditionalQty($row->qty * $row->lookable->item->pallet->box_conversion).' BOX )';
                                    } */
                                    $hscode = '';
                                    if($freeAreaTax){
                                        $hscode = ' '.$row->lookable->item->type->hs_code;
                                    }
                                @endphp
                                <tr>
                                    <td align="center">{{ ($key + 1) }}</td>
                                    <td align="">{{ $row->lookable->item->code.' - '.$row->lookable->item->name.$boxQty.$hscode }}</td>
                                    <td align="right">{{ CustomHelper::formatConditionalQty($row->lookable->qty) }}</td>
                                    <td align="right">{{ CustomHelper::formatConditionalQty(round($row->lookable->qty * $row->lookable->marketingOrderDetail->qty_conversion,3)) }}</td>
                                    <td align="center">{{ $row->lookable->item->uomUnit->code }}</td>
                                    <td align="right">{{ number_format($row->lookable->marketingOrderDetail->price,2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->lookable->marketingOrderDetail->percent_discount_1,2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->lookable->marketingOrderDetail->percent_discount_2,2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->lookable->marketingOrderDetail->discount_3,2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->total,2,',','.') }}</td>
                                </tr>
                                @endforeach
                                @foreach($data->marketingOrderInvoiceDetailManual as $key => $row)
                                <tr>
                                    <td style="width:5% !important">{{ ($key + 1) }}</td>
                                    <td style="width:40% !important;padding-left:5px">{{ $row->description }}</td>
                                    <td style="width:10% !important;padding-left:32px" align="right">0</td>
                                    <td style="width:5% !important" align="right">{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
                                    <td style="width:5% !important"align="right">{{ $row->unit->code }}</td>
                                    <td style="width:10% !important" align="right">{{ number_format($row->price,2,',','.') }}</td>
                                    <td style="width:10% !important;padding-left:7px" align="center">0</td>
                                    <td style="width:5% !important;padding-left:7px" align="">0</td>
                                    <td style="width:5% !important" align="right">0</td>
                                    <td style="width:20% !important" align="right">{{ number_format($row->total,2,',','.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- invoice subtotal -->
                    <div class="invoice-subtotal break-row">
                        <div class="row">
                        <div class="column1">
                            <table style="width:100%">
                                <tr class="break-row">
                                    <td>
                                        Terbilang : <i>{{ CustomHelper::terbilangWithKoma($data->grandtotal) }} Rupiah
                                    </td>

                                </tr>
                                <tr class="break-row">
                                    <td>
                                        Pembayaran dapat dilakukan ke:
                                        <br>
                                        PT Superior Porcelain Sukses
                                        <h3>
                                            - MANDIRI KCP DARMO PERMAI 14100 798 77999
                                        </h3>
                                        Catatan:
                                        <ol>
                                            <li>Pembayaran dengan Cek/BG lunas setelah pencairan.</li>
                                            <li>Invoice ini adalah bukti penagihan, BUKAN bukti pembayaran.</li>
                                        </ol>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="column2">
                            <table style="border:none;" width="74%">
                                <tr class="break-row">
                                    <td align="right">Subtotal</td>
                                    <td align="right" align="right">{{ number_format($data->subtotal,2,',','.') }}</td>
                                </tr>
                                <tr class="break-row">
                                    <td align="right">Downpayment</td>
                                    <td align="right" align="right">{{ number_format($data->downpayment,2,',','.') }}</td>
                                </tr>
                                <tr class="break-row">
                                    <td align="right">Total</td>
                                    <td align="right" align="right">{{ number_format($data->total,2,',','.') }}</td>
                                </tr>
                                <tr class="break-row">
                                    <td align="right">PPN</td>
                                    <td align="right" align="right">{{ number_format($data->tax,2,',','.') }}</td>
                                </tr>
                                <tr class="break-row">
                                    <td align="right">Grandtotal</td>
                                    <td align="right" align="right">{{ number_format($data->grandtotal,2,',','.') }}</td>
                                </tr>
                            </table>
                        </div>
                        </div>
                    </div>

                    {{-- @if($data->marketingOrderInvoiceDownPayment()->exists())
                    <div class="invoice-product-details break-row" style="overflow:auto;margin-top:25px;">
                        <div align="center">Downpayment Terpakai</div>
                        <table border="1" style="border-collapse:collapse" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ __('translations.no') }}.</th>
                                    <th>Dokumen</th>
                                    <th>{{ __('translations.note') }}</th>
                                    <th>{{ __('translations.total') }}</th>
                                    <th>{{ __('translations.tax') }}</th>
                                    <th>{{ __('translations.grandtotal') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data->marketingOrderInvoiceDownPayment as $key => $row)
                                <tr>
                                    <td align="center">{{ ($key + 1) }}</td>
                                    <td align="center">{{ $row->lookable->code }}</td>
                                    <td>{{ $row->note }}</td>
                                    <td align="right">{{ number_format($row->total,2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->tax,2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->grandtotal,2,',','.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif --}}
                </div>
            </div>
        </main>

    </body>
