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
                    font-size:11px !important;
                }

                table > thead > tr > th {

                    font-size:13px !important;
                    font-weight: 800 !important;
                }
                td{
                    font-size:1em !important;

                }
            }
            .left-col {
                width: 70%;
            }
            .right-col {
                text-align: right;
                font-weight: bold;
                vertical-align: top;
            }

            @media print {
                .invoice-print-area {
                    font-size:13px !important;
                }

                table > thead > tr > th {
                    font-size:15px !important;
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
                    font-size:12px !important;
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
            .half-width{
                width: 50%;
            }

            @page { margin: 5em 3em 6em 3em; }
            header { position: fixed; top: -70px; left: 0px; right: 0px; height: 150px; margin-bottom: 10em }



        </style>
    </head>
    <body>
        <header>
            <table border="0" width="100%">
                <tr>
                    <td align="center" width="33%">
                        <img src="{{ $image }}" width="50%" style="position: absolute; top:5px; width:20%">
                    </td>
                    <td align="center" width="33%" style="padding-top:10px;">

                    </td>
                    <td align="center" width="33%" style="padding-top:50px;">

                    </td>
                </tr>
            </table>
        </header>
        <main>
            <div class="card">
                <div class="card-content invoice-print-area">
                    <!-- header section -->
                    <table border="1" width="100%" style="border-collapse:collapse;">
                        <tr>
                            <th colspan="2">PACKING LIST</th>
                        </tr>
                        <tr>
                            <td class="half-width">
                                <strong>SUPPLIER :</strong> <br>
                                PT SUPERIOR PORCELAIN SUKSES <br>
                                <em>JL. RAYA CIPEUNDEUY PABUARAN, KP. SUKAGENAH RT 021 RW 010 DESA KEDAWUNG, KECAMATAN PABUARAN, KABUPATEN SUBANG - 41262</em>
                            </td>
                            <td class="half-width">
                                <strong>DELIVERY ORDER NO. :</strong>{{ $data->code }}  <br>
                                <strong>DATE :</strong> {{ date('d/m/Y',strtotime($data->post_date)) }} <br>
                                <hr style="border: none;
        height: 0.5px;
        background-color: grey;">
                                <strong>PO NO. :</strong> {{ $data->getPoCustomer() ?? '-' }} <br>
                                <strong>DATE :</strong> {{ $data->getPoCustomerDate() ?? '-' }} <br>
                            </td>
                        </tr>
                        <tr>
                            <td class="half-width">
                                <strong>CONSIGNEE :</strong> <br>
                                {{ $data->marketingOrderDelivery->customer->name ?? '-' }}<br>
                                <em> {{ $data->marketingOrderDelivery->destination_address }}</em>
                            </td>
                            <td class="half-width">
                                <strong>NOTIFY PARTY :</strong> <br>
                                {{ $data->marketingOrderDelivery->customer->name ?? '-' }}<br>
                                <em> {{ $data->marketingOrderDelivery->customer->address }}</em>
                            </td>
                        </tr>
                    </table>
                    <br>
                    <div class="invoice-product-details mt-2" style="overflow:auto;">
                        <table class="item-table" border="1" width="100%" style="border-collapse:collapse;">

                            <tr>
                                <th>NO.</th>
                                <th>ITEM NAME</th>
                                <th>TOTAL PALETTE</th>
                                <th>TOTAL BOX</th>
                                <th>TOTAL M²</th>
                                <th>NET WEIGHT<br>(kg)</th>
                                <th>GROSS WEIGHT<br>(kg)</th>
                            </tr>
                            @php
                                $key=0;
                            @endphp
                            @foreach ( $data->qtyPerShading()['data'] as $row)

                                <tr>
                                    <td align="center">{{$key+1}}</td>
                                    <td align="center">{{ $row['item'] }} {{ $row['shading'] }}</td>

                                    <td align="right">{{ CustomHelper::formatConditionalQty($row['total_palet'])  }}</td>
                                    <td align="right">{{ CustomHelper::formatConditionalQty($row['total_box']) }}</td>
                                    <td align="right">{{ CustomHelper::formatConditionalQty($row['total_conversion'])  }}</td>
                                    <td align="right">{{ CustomHelper::formatConditionalQty($row['total_netto'])  }}</td>
                                    <td align="right">{{ CustomHelper::formatConditionalQty($row['total_gross'])  }}</td>
                                </tr>
                                @php
                                    $key++;
                                @endphp
                            @endforeach
                            <tr>
                                <td colspan="2"><strong>TOTAL</strong></td>
                                <td align="right">{{ CustomHelper::formatConditionalQty($data->qtyPerShading()['total_palet']) }}</td>
                                <td align="right">{{ CustomHelper::formatConditionalQty($data->qtyPerShading()['total_box']) }}</td>
                                <td align="right">{{ CustomHelper::formatConditionalQty($data->qtyPerShading()['total_qty']) }}</td>
                                <td align="right">{{ CustomHelper::formatConditionalQty($data->qtyPerShading()['total_netto']) }}</td>
                                <td align="right">{{ CustomHelper::formatConditionalQty($data->qtyPerShading()['total_gross']) }}</td>
                            </tr>
                        </table>
                    </div>
                    <br>

                    <table class="description-table" style="width:100% !important;border-collapse:collapse;" border="1" >
                        <tr>
                            <td class="left-col" style="width: 50%">
                                <strong>DESCRIPTION OF GOODS :</strong> GRANITE TILE <br><br>
                                <strong>PLACE OF LOADING :</strong> SUBANG, WEST JAVA <br><br>
                                <strong>CONTAINER NO. :</strong> {{ $data->no_container ?? '-'.' / Seal : '.($data->seal_no ?? '-' ) }} <br><br>
                                <strong>DATE :</strong> {{ date('d/m/Y') }}
                            </td>
                            <td class="right-col" align="center">
                               TTD
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </main>
    </body>
</html>
