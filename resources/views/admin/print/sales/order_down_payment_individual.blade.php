@php
    use App\Helpers\CustomHelper;

@endphp
<!doctype html>
<html lang="en">
    <head>
        <style>

            .break-row {
                page-break-inside: avoid;
                margin-top:20px;
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
            <table border="0" width="100%">
                <tr>
                    <td width="60%" class="left-align">
                        <tr>
                            <td align="center">
                                <span class="invoice-number mr-1" style="font-size:15px;font-weight:800;margin-bottom:0px">
                                    KWITANSI {{ $data->code }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="margin-top: -2px;">
                                <small style="font-size:10px">Tgl.Berlaku:</small>
                                <span style="font-size:10px;">{{ date('d/m/Y',strtotime($data->post_date)) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h5 style="margin-top: -2px">AR Down Payment</h5>
                            </td>
                        </tr>
                    </td>
                    <td width="40%" class="right-align">
                        <img src="{{ $image }}" width="50%" style="position: absolute; top:5px; right:25px;width:20%">
                        <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($data->code, 'C128')}}" alt="barcode" style="position: absolute; top:50px;width:200px;right:0px;" height="20%" />
                    </td>
                </tr>
                
            </table>
        </header>
        <main>
            <div class="card">
                <div class="card-content invoice-print-area">
                    <!-- header section -->
                    <table border="0" width="80%" style="margin-left: auto;margin-right: auto;font-size:12px;font-weight:800;">
                        <tr>
                            <td width="25%">Telah terima dari</td>
                            <td width="1%">:</td>
                            <td width="74%">{{ $data->account->name }}</td>
                        </tr>
                        <tr>
                            <td>Total Nominal</td>
                            <td>:</td>
                            <td>{{ number_format($data->grandtotal,2,',','.') }}</td>
                        </tr>
                        <tr>
                            <td>Terbilang</td>
                            <td>:</td>
                            <td><i>{{ CustomHelper::terbilangWithKoma($data->grandtotal).' '.ucwords(strtolower($data->currency->document_text)) }}</i></td>
                        </tr>
                        <tr>
                            <td>Catatan</td>
                            <td>:</td>
                            <td>{{ $data->note }}</td>
                        </tr>
                    </table>

                    <table border="0" width="80%" style="font-size:12px;margin-top:15px;">
                        <tr>
                            <td>
                                NB : Bukan merupakan bukti penerimaan, pembayaran dianggap sah jika : 
                                <ol>
                                    <li>Cek/Giro telah dicairkan di rekening</li>
                                    <li>Transfer dana telah diterima di rekening</li>
                                </ol>
                            </td>
                        </tr>
                    </table>

                    <table border="0" width="100%" style="font-size:12px;margin-top:15px;">
                        <tr>
                            <td width="40%" style="border:1px solid black;padding:5px;">
                                Mohon ditransfer ke :
                                <h5>BANK MANDIRI KCP DARMO PERMAI 14100 798 77999</h5>
                                atas nama {{ $data->company->name }}
                            </td>
                            <td width="10%" style="padding-left:10px;">

                            </td>
                            <td width="50%" style="padding-left:10px;">
                                {!! ucwords(strtolower($data->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
                                <div style="margin-top:100px;">
                                    Tanda Tangan dan Nama Terang
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </main>
    </body>
</html>