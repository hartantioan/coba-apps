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
                    font-size:12.5px !important;
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
                    font-size:12.5px !important;
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
                    <td align="center" width="33%" style="padding-top:10px;font-size:20px !important;">
                        <b>{{ $title }}</b>
                    </td>
                    <td align="center" width="33%" style="padding-top:10px;">
                        <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($data->code, 'C128')}}" alt="barcode" style="margin-left:35px;top:5px;width:150px;" height="30px" />
                        <span class="invoice-number mr-1" style="font-size:15px;font-weight:800;margin-left:40px;top:50px;">
                            {{ $data->code }}
                        </span>
                        <div class="invoice-number mr-1" style="font-size:10px;font-weight:800;margin-left:40px;">
                            *Untuk Customer
                        </div>
                    </td>
                </tr>
            </table>
        </header>
        <main style="margin-bottom:20px;">
            <div class="card">
                <div class="card-content invoice-print-area">
                    <!-- header section -->
                    <table border="0" width="100%">
                        <tr>
                            <td width="100%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="20%">
                                            Telah terima dari
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="80%">
                                            {{ $data->account->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Untuk pembayaran
                                        </td>
                                        <td>:</td>
                                        <td>
                                            {{ implode(', ',$data->arrInvoice()) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Keterangan
                                        </td>
                                        <td>:</td>
                                        <td>
                                            {{ $data->note }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Total Nominal
                                        </td>
                                        <td>:</td>
                                        <td>
                                            Rp. {{ number_format($data->grandtotal,2,',','.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Terbilang
                                        </td>
                                        <td>:</td>
                                        <td>
                                            {{ CustomHelper::terbilangWithKoma($data->grandtotal) }} Rupiah
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    
                    <!-- invoice subtotal -->
                    <div class="invoice-subtotal break-row" style="margin-top:2px;">
                        <table border="0" width="100%">
                            <tr>
                                <td width="100%" class="left-align">
                                    NB : Bukan merupakan bukti penerimaan, pembayaran dianggap sah jika :
                                    <ol>
                                        <li>Cek/Giro telah dicairkan di rekening</li>
                                        <li>Transfer dana telah diterima di rekening</li>
                                    </ol>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="invoice-subtotal break-row" style="margin-top:2px;">
                        <table border="0" width="100%">
                            <tr>
                                <td width="70%" style="border:1px solid black;padding:10px;">
                                    Mohon ditransfer ke :<br>
                                    <b>
                                        <h5>BANK MANDIRI KCP DARMO PERMAI 14100 798 77999</h5>
                                        atas nama {{ $data->company->name }}
                                    </b>
                                </td>
                                <td width="30%" align="center">
                                    {{ $data->company->city->name.', '.CustomHelper::tgl_indo($data->post_date) }}
                                    <br><br><br><br><br><br>
                                    __________________________
                                    <br>
                                    Tanda Tangan dan Nama Terang
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </main>
        <main style="margin-top:15px;page-break-before: always;">
            <div class="card">
                <div class="card-content invoice-print-area">
                    <!-- header section -->
                    <table border="0" width="100%">
                        <tr>
                            <td width="100%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="20%">
                                            Telah terima dari
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="80%">
                                            {{ $data->account->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Untuk pembayaran
                                        </td>
                                        <td>:</td>
                                        <td>
                                            {{ implode(', ',$data->arrInvoice()) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Keterangan
                                        </td>
                                        <td>:</td>
                                        <td>
                                            {{ $data->note }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Total Nominal
                                        </td>
                                        <td>:</td>
                                        <td>
                                            Rp. {{ number_format($data->grandtotal,2,',','.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Terbilang
                                        </td>
                                        <td>:</td>
                                        <td>
                                            {{ CustomHelper::terbilangWithKoma($data->grandtotal) }} Rupiah
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    
                    <!-- invoice subtotal -->
                    <div class="invoice-subtotal break-row" style="margin-top:2px;">
                        <table border="0" width="100%">
                            <tr>
                                <td width="100%" class="left-align">
                                    NB : Bukan merupakan bukti penerimaan, pembayaran dianggap sah jika :
                                    <ol>
                                        <li>Cek/Giro telah dicairkan di rekening</li>
                                        <li>Transfer dana telah diterima di rekening</li>
                                    </ol>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="invoice-subtotal break-row" style="margin-top:2px;">
                        <table border="0" width="100%">
                            <tr>
                                <td width="70%" style="border:1px solid black;padding:10px;">
                                    Mohon ditransfer ke :<br>
                                    <b>
                                        <h5>BANK MANDIRI KCP DARMO PERMAI 14100 798 77999</h5>
                                        atas nama {{ $data->company->name }}
                                    </b>
                                </td>
                                <td width="30%" align="center">
                                    {{ $data->company->city->name.', '.CustomHelper::tgl_indo($data->post_date) }}
                                    <br><br><br><br><br><br>
                                    __________________________
                                    <br>
                                    Tanda Tangan dan Nama Terang
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>