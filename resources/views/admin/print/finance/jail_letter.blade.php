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

            .center-align {
                text-align: center !important;
            }

            .right-align {
                text-align: right !important;
            }

            @media only screen and (max-width : 768px) {
                .invoice-print-area {
                    zoom:0.4;
                }
            }
        
            @media only screen and (max-width : 992px) {
                .invoice-print-area {
                    zoom:0.6;
                    font-size:18px !important;
                }

                table > thead > tr > th {
                    font-size:13px !important;
                    font-weight: 800 !important;
                }
                td{
                    font-size:0.7em !important;
                }
                .tb-header td{
                    font-size:0.6em !important;
                }
                .tbl-info td{
                    font-size:1em !important;
                }
                .table-data-item td{
                    font-size:0.6em !important;
                }
                .table-data-item th{
                    border:0.6px solid black;
                }
                .table-bot td{
                    font-size:0.6em !important;
                }
                .table-bot1 td{
                    font-size:0.7em !important;
                }
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

            @page { margin: 5em 3em 6em 3em; }
            header { position: fixed; top: -70px; left: 0px; right: 0px; height: 150px; margin-bottom: 10em }
                
        
           
        </style>
    </head>
    <body>
        <header>
            <table border="0" width="100%" style="font-size:1em" class="tb-header">
                <tr>
                    <td width="83%" class="left-align" >
                        <img src="{{ $image }}" width="50%" style="position: absolute; top:5px; width:20%">
                    </td>
                    <td width="33%" class="right-align">
                    </td>
                    <td width="34%" class="right-align">
                        SERAH TERIMA
                        <br>CEK / TUNAI
                    </td>
                </tr>
                
            </table>
            <hr style="border-top: 3px solid black; margin-top:20px">
        </header>
        <main>
            <div class="card">
                <div class="card-content invoice-print-area ">
                    <table border="0" width="100%">
                        <tr>
                            <td class="left-align">
                                Pada hari ini, <b>{{ CustomHelper::hariIndo(date('l',strtotime($data->post_date))) }}</b> Tanggal <b>{{ date('d/m/Y',strtotime($data->post_date)) }}</b>, telah diterima dari <b>{{ $data->company->name }}</b>.
                            </td>
                        </tr>
                        <tr>
                            <td class="left-align" style="padding-left: 30px;">
                                <table border="0" width="100%" class="tbl-info">
                                    <tr>
                                        <td width="25%">
                                            Nama
                                        </td>
                                        <td width="75%">
                                            : {{ strtoupper($data->user->name) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Jabatan
                                        </td>
                                        <td>
                                            : {{ $data->user->position()->exists() ? strtoupper($data->user->position->name) : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Uang Tunai / Cek Bank
                                        </td>
                                        <td>
                                            : {{ strtoupper($data->listCekBG()) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Sebesar
                                        </td>
                                        <td>
                                            : {{ strtoupper($data->currency->symbol.' '.number_format($data->grandtotal,2,',','.')) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Terbilang
                                        </td>
                                        <td>
                                            : {{ strtoupper(CustomHelper::terbilangWithKoma($data->grandtotal).' '.ucwords(strtolower($data->currency->document_text))) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Keperluan
                                        </td>
                                        <td>
                                            : {{ strtoupper($data->note) }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <div class="invoice-subtotal break-row">
                        <table class="table-bot1" width="100%" border="0" style="margin-top:50px;">
                            <tr>
                                <td class="center-align" width="50%">
                                    Dibuat oleh,
                                    <br><br><br><br>
                                    (......................................)
                                </td>
                                <td class="center-align">
                                    Diterima oleh,
                                    <br><br><br><br>
                                    (......................................)
                                </td>
                            </tr>
                        </table>
                        <p style="font-size:13px !important;">
                            Note:
                            <br>&nbsp;&nbsp;&nbsp;Dana yang diterima hanya untuk dipergunakan sesuai keperluan tercantum
                            <br>&nbsp;&nbsp;&nbsp;Pelaku penggelapan dalam jabatan diancam pidana penjara maksimal 5 (lima) tahun sesuai Pasal 374 KUHP
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>