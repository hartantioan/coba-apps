@php
    use App\Helpers\CustomHelper;

@endphp
<!doctype html>
<html lang="en">
    <head>
        <style>
            html
            {
                font-family: Tahoma, "Trebuchet MS", sans-serif;
            }

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
                margin-right: 60%;
                float: left;
                width: 50%;
                padding: 5px;
            }

            .row::after {
            content: "";
            clear: both;
            display: table;
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
                    font-size:0.5em !important;
                }
                .tb-header td{
                    font-size:0.5em !important;
                }
                .tbl-info td{
                    font-size:0.8em !important;
                    vertical-align:top !important;
                }
                .table-data-item td{
                    font-size:0.5em !important;
                }
                .table-data-item th{
                    border:0.6px solid black;
                }
                .table-bot td{
                    font-size:0.5em !important;
                }
                .table-bot1 td{
                    font-size:0.6em !important;
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

            @page { margin: 5em 3em 6em 3em; }
            header { position: fixed; top: -70px; left: 0px; right: 0px; height: 150px; margin-bottom: 10em }



        </style>
    </head>
    <body>
        <header>
            <table border="0" width="100%" style="font-size:1em" class="tb-header">
                <tr>
                    <td width="33%" class="left-align" >
                        <tr>
                            <td>
                                <span class="invoice-number mr-1" style="font-size:1em"></span>
                            </td>
                        </tr>
                    </td>
                    <td width="33%" align="center">
                        <tr>
                            <td>
                                <h2 style="margin-top: -2px">{{$data['supplier']}}</h2>
                            </td>
                        </tr>
                    </td>

                    <td width="34%" class="right-align">
                        <img src="{{ $image }}" width="50%" style="position: absolute; top:5px; width:20%;right:0;">
                    </td>
                </tr>

            </table>
            <hr style="border-top: 3px solid black; margin-top:0px">
        </header>
        <main>
            <div class="card">
                <div class="invoice-product-details mt-2">
                    <table class="bordered table-with-breaks table-data-item " border="1" style="border-collapse:collapse;" width="100%"  >
                        <thead>
                            <tr>
                                <th  >NO</th>
                                <th  >PLANT</th>
                                <th  >NO PO</th>
                                <th  >NAMA ITEM</th>
                                <th  >NO SJ</th>
                                <th  >TGL MASUK</th>
                                <th  >NO. KENDARAAN</th>
                                <th  >NETTO SJ<br>M</th>
                                <th  style="background-color: green;">NETTO SPS<br> M</th>
                                <th  style="background-color: green;">SELISIH</th>
                                <th >TOTAL BAYAR<br>KG</th>
                                <th >TOTAL PENERIMAAN<br>KG</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $keys=0;
                            @endphp
                            @foreach($data['data'] as $key => $row)
                                <tr align="center">
                                    <td>{{ $keys+1 }}</td>
                                    <td>{{ $row['PLANT'] }}</td>
                                    <td>{{ $row['NO PO'] }}</td>
                                    <td>{{ $row['NAMA ITEM'] }}</td>
                                    <td>{{ $row['NO SJ'] }}</td>
                                    <td>{{ $row['TGL MASUK'] }}</td>
                                    <td>{{ $row['NO. KENDARAAN'] }}</td>
                                    <td>{{ $row['NETTO SJ'] }}</td>
                                    <td>{{ $row['NETTO SPS'] }}</td>
                                    <td>{{ $row['SELISIH'] }}</td>
                                    <td>{{ $row['TOTAL BAYAR'] }}</td>
                                    <td>{{ $row['TOTAL PENERIMAAN'] }}</td>
                                </tr>
                                @php
                                    $keys+=1;
                                @endphp
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>
        </main>
    </body>
</html>
