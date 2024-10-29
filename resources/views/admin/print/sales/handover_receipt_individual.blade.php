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

            .page-break {
                page-break-after: always;
            }

            @page { margin: 2em 2em 5em 2em; }

        </style>
    </head>
    <body>
        <div>
            <table border="0" width="100%">
                <tr>
                    <td align="center">
                        <h5>
                            {{ $data->company->name }}
                            <br>TANDA TERIMA
                            <br>{{ date('d/m/Y',strtotime($data->post_date)) }}
                            <br><br>{{ $data->account->name }}
                        </h5>
                    </td>
                </tr>
            </table>
        </div>
        <main>
            <div class="card break-row">
                <div class="card-content invoice-print-area">
                    <!-- header section -->
                    <table border="1" width="100%" style="border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th>{{ __('translations.no') }}</th>
                                <th>No.Invoice</th>
                                <th>{{ __('translations.date') }}</th>
                                <th>{{ __('translations.grandtotal') }}</th>
                                <th>Dibayar</th>
                                <th>Sisa</th>
                                <th>Surat Jalan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data->marketingOrderReceiptDetail as $key => $row)
                                <tr class="{{ $key % 35 == 0 ? 'page-break' : '' }}">
                                    <td align="center">{{ ($key + 1)  }}</td>
                                    <td>{{ $row->lookable->code }}</td>
                                    <td align="center">{{ date('d/m/Y',strtotime($row->lookable->post_date)) }}</td>
                                    <td align="right">{{ number_format($row->lookable->grandtotal,2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->lookable->totalPay(),2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->lookable->balancePaymentIncoming(),2,',','.') }}</td>
                                    <td>{{ $row->lookable->marketingOrderDeliveryProcess->code.' - '.date('d/m/Y',strtotime($row->lookable->marketingOrderDeliveryProcess->post_date)) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td align="right" colspan="5"><b>Total</b></td>
                                <td align="right"><b>{{ number_format($data->grandtotal,2,',','.') }}</b></td>
                                <td>-</td>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="invoice-subtotal break-row" style="margin-top:30px;">
                        <table border="0" width="100%">
                            <tr>
                                <td width="50%" align="center">
                                    Dibuat Oleh
                                    <br><br><br><br><br>
                                    (.........................)
                                </td>
                                <td width="50%" align="center">
                                    Penerima
                                    <br><br><br><br><br>
                                    (.........................)
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>