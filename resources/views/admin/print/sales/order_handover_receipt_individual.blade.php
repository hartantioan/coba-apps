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
        <header>
            <table border="0" width="100%">
                <td align="center" width="33%">
                    <img src="{{ $image }}" width="50%" style="position: absolute; top:5px; width:20%">
                </td>
                <td align="center" width="33%" style="padding-top:10px;font-size:15px !important;">
                    <b>{{ $title }}</b>
                    <div>{{ $data->code }}</div>
                    <br>{{ date('d/m/Y',strtotime($data->post_date)) }}
                </td>
                <td align="center" width="33%" style="padding-top:10px;">
                    <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($data->code, 'C128')}}" alt="barcode" style="margin-left:35px;top:5px;width:150px;" height="30px" />
                </td>
            </table>
        </header>
        <main>
            <div class="card break-row">
                <div class="card-content invoice-print-area">
                    <!-- header section -->
                    Collector : {{ $data->account->name }}
                    <table border="1" width="100%" style="border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th width="5%" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.no') }}</th>
                                <th width="20%">Kwitansi</th>
                                <th width="20%" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.name') }}</th>
                                <th width="10%" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.date') }}</th>
                                <th width="15%" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.grandtotal') }}</th>
                                <th width="10%" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.status') }}</th>
                                <th width="20%" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.note') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data->marketingOrderHandoverReceiptDetail as $key => $row)
                                <tr>
                                    <td align="center" rowspan="2">{{ ($key + 1)  }}</td>
                                    <td>{{ $row->marketingOrderReceipt->code }}</td>
                                    <td>{{ $row->marketingOrderReceipt->account->name }}</td>
                                    <td align="center">{{ date('d/m/Y',strtotime($row->marketingOrderReceipt->post_date)) }}</td>
                                    <td align="right">{{ number_format($row->marketingOrderReceipt->grandtotal,2,',','.') }}</td>
                                    <td align="center">{{ $row->status() }}</td>
                                    <td>{{ $row->note }}</td>
                                </tr>
                                <tr>
                                    <td colspan="6">Daftar Invoice {{ $row->getListInvoice() }}</td>
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