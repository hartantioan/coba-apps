@php
    use App\Helpers\CustomHelper;
@endphp
<!doctype html>
<html lang="en">
    <head>
        <style>

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
                    font-size:13px !important;
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
        
                .card {
                    background-color:white !important;
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
        
                .row .col {
                    padding:0px !important;
                }
            }
            
            .invoice-product-details{
                border:1px solid black;
                min-height: auto;
            }

            @page { margin: 3em 3em 6em 3em; }

        </style>
    </head>
    <body>
        <div style="position:absolute;top:0px !important;">
            <img src="{{ $image }}" height="30px">
            <br>Collector : {{ $data->account->name }}
        </div>
        <h5 align="center">
            {{ $title }}
            <br>{{ $data->code }}
            <br>{{ date('d/m/y',strtotime($data->post_date)) }}
        </h5>
        <main>
            <div class="card break-row">
                <div class="card-content invoice-print-area">
                    <!-- header section -->
                    <table border="1" width="100%" style="border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="20%">Nama</th>
                                <th width="20%">Kwitansi</th>
                                <th width="10%">Tanggal</th>
                                <th width="15%">Grandtotal</th>
                                <th width="10%">Status</th>
                                <th width="20%">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data->marketingOrderHandoverReceiptDetail as $key => $row)
                                <tr>
                                    <td align="center">{{ ($key + 1)  }}</td>
                                    <td>{{ $row->marketingOrderReceipt->account->name }}</td>
                                    <td>{{ $row->marketingOrderReceipt->code }}</td>
                                    <td align="center">{{ date('d/m/y',strtotime($row->marketingOrderReceipt->post_date)) }}</td>
                                    <td align="right">{{ number_format($row->marketingOrderReceipt->grandtotal,2,',','.') }}</td>
                                    <td align="center">{{ $row->status() }}</td>
                                    <td>{{ $row->note }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td align="right" colspan="4"><b>Total</b></td>
                                <td align="right"><b>{{ number_format($data->grandtotal,2,',','.') }}</b></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </main>
    </body>
</html>