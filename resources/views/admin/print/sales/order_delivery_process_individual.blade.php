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

                    font-size:13px !important;
                    font-weight: 800 !important;
                }
                td{
                    font-size:1em !important;

                }
            }

            @media print {
                .invoice-print-area {
                    font-size:10px !important;
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
                min-height: auto;
            }

            @page { margin: 5em 3em 6em 3em; }
            header { position: fixed; top: -70px; left: 0px; right: 0px; height: 150px; margin-bottom: 10em }

            #table-info > tbody > tr > td {
                padding: 5px;
            }

        </style>
    </head>
    <body>
        <header>
            <table border="0" width="100%">
                <tr>
                    <td width="34%">

                    </td>
                    <td width="33%" align="center">
                        <h5 style="margin-top:30px;"><br></h5>
                        <h5 style="margin-top:0px;margin-left:-15px;">{{ $data->code }}</h5>
                        <div style="margin-top:-10px">

                            <span style="font-size:10px;margin-left:-15px;">{{ date('d/m/Y',strtotime($data->post_date)) }}</span>
                        </div>
                    </td>
                    <td width="33%">

                    </td>
                </tr>
            </table>
            <!-- header section -->
            {{-- <table border="0" width="100%" style="padding-top: 13px;margin-left:-20px;font-size:8px !important">
                <tr>
                    <td width="50%" class="left-align">
                        <table border="0" width="100%" style="border-spacing: 0;">
                            <tr style="margin:0px;">
                                <td width="35%">

                                </td>

                                <td width="65%"  style="padding-top: 0px; padding-bottom: 0px;" >
                                    {{ $data->getPlace() }}
                                </td>
                            </tr>
                            <tr>
                                <td width="35%">

                                </td>

                                <td width="65%"  style="padding-top: 0px; padding-bottom: 0px;">
                                    {{ $data->getWarehouse() }}
                                </td>
                            </tr>
                            <tr>
                                <td width="35%">

                                </td>

                                <td width="65%"  style="padding-top: 0px; padding-bottom: 0px;">
                                    {{ $data->marketingOrderDelivery->code }}
                                </td>
                            </tr>
                            <tr>
                                <td width="35%">

                                </td>

                                <td width="65%"  style="padding-top: 0px; padding-bottom: 0px;">
                                    {{ $data->account->name }}
                                </td>
                            </tr>
                            <tr>
                                <td width="35%">

                                </td>

                                <td width="65%"  style="padding-top: 0px; padding-bottom: 0px;">
                                    {{ $data->marketingOrderDelivery->deliveryType() }}
                                </td>
                            </tr>
                            <tr>
                                <td width="35%">

                                </td>

                                <td width="65%"  style="padding-top: 0px; padding-bottom: 0px;">
                                    {{ $data->marketingOrderDelivery->transportation->name }}/
                                    {{ $data->no_container ?? '-'.' / Seal : '.($data->seal_no ?? '-' ) }}
                                </td>
                            </tr>
                            <tr>
                                <td width="35%">

                                </td>

                                <td width="65%"  style="padding-top: 0px; padding-bottom: 0px;">
                                    {{ $data->vehicle_no }}
                                </td>
                            </tr>
                            <tr>
                                <td width="35%">

                                </td>

                                <td width="65%"  style="padding-top: 0px; padding-bottom: 0px;">
                                    {{ $data->driver_name }}
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td width="50%" class="left-align">
                        <table border="0" width="100%" style="border-spacing: 0;">
                            <tr >
                                <td width="30%">

                                </td>

                                <td width="70%"  style="padding-top: 0px; padding-bottom: 0px;">
                                    {{ $data->getPoCustomer() ?? '-' }}
                                </td>
                            </tr>
                            <tr >
                                <td width="30%">

                                </td>

                                <td width="70%"  style="padding-top: 0px; padding-bottom: 0px;">
                                    {{ $data->marketingOrderDelivery->customer->name ?? '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td width="30%">

                                </td>

                                <td width="70%"  style="padding-top: 0px; padding-bottom: 0px;">
                                   {{ strtoupper($data->getOutlet()) ?? '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td>

                                </td>

                                <td>
                                    {{ strtoupper($data->getProject()) ?? '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td>

                                </td>

                                <td >
                                   <div style="min-height:36px !important">
                                    {{ strtoupper($data->marketingOrderDelivery->destination_address) }}
                                    <div>
                                </td>
                            </tr>
                            <tr>
                                <td>

                                </td>

                                <td>
                                    {{ strtoupper($data->marketingOrderDelivery->district->name) }}
                                </td>
                            </tr>
                            <tr>
                                <td>

                                </td>

                                <td>
                                    {{ strtoupper($data->marketingOrderDelivery->city->name) }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table> --}}
            {{-- <div style="padding-left: 30px; height:60px;padding-top:0px;margin-top:0px;font-size:9px;">{{ $data->note_external }}</div> --}}
        </header>
        <main style="margin-top:25px;">
            @if ($data->marketingOrderDelivery->so_type == '4' || $data->marketingOrderDelivery->so_type == '3' )
                <div style="position:absolute;top:50%;left:35%;width:180px;height:25px;padding:15px 15px 15px 15px;font-size:15px;text-align:center;border:1px solid black;border-radius:15px;opacity: 0.5;">
                    TIDAK UNTUK DIJUAL
                </div>
            @endif
            <div class="card">
                <div class="card-content invoice-print-area">
                    <!-- header section -->
                    <table border="0" width="100%" style="padding-top: 45px;margin-left:-20px;font-size:8px !important">
                        <tr>
                            <td width="50%" class="left-align">
                                <table border="0" width="100%" style="border-spacing: 0;">
                                    <tr style="margin:0px;">
                                        <td width="35%">

                                        </td>

                                        <td width="65%"  style="padding-top: 0px; padding-bottom: 0px;" >
                                            {{ $data->getPlace() }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="35%">

                                        </td>

                                        <td width="65%"  style="padding-top: 0px; padding-bottom: 0px;">
                                            {{ $data->getWarehouse() }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="35%">

                                        </td>

                                        <td width="65%"  style="padding-top: 0px; padding-bottom: 0px;">
                                            {{ $data->marketingOrderDelivery->code }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="35%">

                                        </td>

                                        <td width="65%"  style="padding-top: 0px; padding-bottom: 0px;">
                                            {{ $data->account->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="35%">

                                        </td>

                                        <td width="65%"  style="padding-top: 0px; padding-bottom: 0px;">
                                            {{ $data->marketingOrderDelivery->deliveryType() }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="35%">

                                        </td>

                                        <td width="65%"  style="padding-top: 0px; padding-bottom: 0px;">
                                            {{ $data->marketingOrderDelivery->transportation->name }}/
                                            {{ ($data->no_container ?? '-').' - NO. SEAL '.$data->seal_no }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="35%">

                                        </td>

                                        <td width="65%"  style="padding-top: 0px; padding-bottom: 0px;">
                                            {{ $data->vehicle_no }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="35%">

                                        </td>

                                        <td width="65%"  style="padding-top: 0px; padding-bottom: 0px;">
                                            {{ $data->driver_name }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="50%" class="left-align">
                                <table border="0" width="100%" style="border-spacing: 0;">
                                    <tr >
                                        <td width="30%">

                                        </td>

                                        <td width="70%"  style="padding-top: 0px; padding-bottom: 0px;">
                                            {{ $data->getPoCustomer() ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr >
                                        <td width="30%">

                                        </td>

                                        <td width="70%"  style="padding-top: 0px; padding-bottom: 0px;">
                                            {{ $data->marketingOrderDelivery->customer->name ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="30%">

                                        </td>

                                        <td width="70%"  style="padding-top: 0px; padding-bottom: 0px;">
                                           {{ strtoupper($data->getOutlet()) ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>

                                        </td>

                                        <td>
                                            {{ strtoupper($data->getProject()) ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>

                                        </td>

                                        <td >
                                           <div style="min-height:36px !important">
                                            {{ strtoupper($data->marketingOrderDelivery->destination_address) }}
                                            <div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>

                                        </td>

                                        <td>
                                            {{ strtoupper($data->marketingOrderDelivery->district->name) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>

                                        </td>

                                        <td>
                                            {{ strtoupper($data->marketingOrderDelivery->city->name) }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <div style="padding-left: 30px; height:60px;padding-top:0px;margin-top:-10px">{{ $data->note_external }}</div>
                    <div class="invoice-product-details mt-2" style="overflow:auto;margin-top:10px;">
                        <table  style="border-collapse:collapse;font-size:10px !important" width="100%">

                            <tbody>
                                @php
                                    $unitcode =  $data->marketingOrderDeliveryProcessDetail->first();
                                @endphp
                                @foreach ( $data->qtyPerShading()['data'] as $row)
                                    <tr>
                                        <td style="width:47%">{{ $row['item'] }}</td>
                                        <td style="width:30%">
                                            {{ $row['shading'] }} ({{ CustomHelper::formatConditionalQty($row['total_box']) }} BOX)
                                        </td>
                                        <td style="width:10%" align="right">{{ CustomHelper::formatConditionalQty($row['total_conversion']) }}</td>
                                        <td style="width:10%" align="center">{{ $row['unit_code'] }}</td>
                                        <td style="width:10%" align="center">{{ CustomHelper::formatConditionalQty($row['total_palet'])  }} PLT</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <table  style="border-collapse:collapse;font-size:10px !important;position: fixed;bottom: 250px; left: 0px; right: 0px;" width="100%">

                            <tbody>

                                <tr>
                                    <td style="width:40%"></td>
                                    <td style="width:30%"></td>
                                    <td style="width:10%" align="right"><strong>{{ CustomHelper::formatConditionalQty($data->qtyPerShading()['total_qty']) }}</strong></td>
                                    <td style="width:10%;padding-bottom:0.5px" align="center"><strong>{{$unitcode->itemStock->item->uomUnit->code}}</strong></td>
                                    <td style="width:10%" align="center"><strong>{{ CustomHelper::formatConditionalQty($data->qtyPerShading()['total_palet']) }}</td>
                                </tr>

                            </tbody>
                        </table>
                    </div>


                    <div style="position: fixed;bottom: 137px; left: 40px; right: 0px;">

                        {{ date('d/m/Y H:i') }} <span style="padding-left: 80px">{{$data->user->name}}</span><span style="padding-left: 80px">Print Ke-{{$data->printCounter()->count()}} | Revisi Ke-{{ $data->revision_counter }}</span>


                    </div>

                </div>
            </div>
        </main>
    </body>
</html>
