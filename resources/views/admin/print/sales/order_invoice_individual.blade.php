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
                /* border:1px solid black; */
                min-height: auto;
            }

            @page { margin: 4em 2em 4em 1em; }
            header { position: fixed; top: -30px; left: 0px; right: 0px; height: 100px; margin-bottom: 10em }
                
        
           
        </style>
    </head>
    <body style="margin-top: 20px">
        <header>
            <table border="0" width="100%">
                <tr>
                    <td width="33%">
                        
                    </td>
                    <td width="33%" align="center">
                        <h5 style="margin-top:35px;">{{ $data->code }}</h5>
                    </td>
                    <td width="33%">
                        
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
                                           
                                        </td>
                                        
                                        <td width="80%">
                                            <div style="min-height:40px">
                                            {{ $data->userData->title }}
                                            <br>
                                            {{ $data->userData->address }}
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="40%" class="left-align" style="padding-top: 0px">
                                <table border="0" width="100%" >
                                    <tr>
                                        <td width="50%">
                                           
                                        </td>
                                        
                                        <td width="50%">
                                            {{ date('d/m/Y',strtotime($data->post_date)) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            
                                        </td>
                                        
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
                            <td width="11%">
                                
                            </td>
                            
                            <td width="80%">
                                {{ $data->marketingOrderDeliveryProcess->marketingOrderDelivery->code }}
                            </td>
                        </tr>
                        <tr>
                            <td width="11%">
                                
                            </td>
                            
                            <td width="80%">
                                {{ $data->marketingOrderDeliveryProcess->code }}
                            </td>
                        </tr>
                    </table>
                    
                    <div class="invoice-product-details mt-2" style="padding-top:40px;padding-left:10px;padding-right:47px">
                        <table style="border-collapse:collapse;border:none;" width="100%">
                            <thead>
                                
                            </thead>
                            <tbody>
                                @foreach($data->marketingOrderInvoiceDeliveryProcessDetail as $key => $row)
                                @php
                                    $boxQty = '';
                                    if($row->lookable->isPallet()){
                                        $boxQty = ' ( '.CustomHelper::formatConditionalQty($row->qty * $row->lookable->itemStock->item->pallet->box_conversion).' BOX )';
                                    }
                                @endphp
                                <tr>
                                    <td style="width:5% !important">{{ ($key + 1) }}</td>
                                    <td style="width:40% !important;padding-left:5px">{{ $row->lookable->itemStock->item->name.$boxQty }}</td>
                                    <td style="width:10% !important;padding-left:32px" align="right">{{ CustomHelper::formatConditionalQty($row->lookable->qty) }}</td>
                                    <td style="width:5% !important" align="right">{{ CustomHelper::formatConditionalQty(round($row->lookable->qty * $row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion,3)) }}</td>
                                    <td style="width:5% !important"align="right">{{ $row->lookable->itemStock->item->uomUnit->code }}</td>
                                    <td style="width:10% !important" align="right">{{ number_format($row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->price,2,',','.') }}</td>
                                    <td style="width:10% !important;padding-left:7px" align="center">{{ number_format($row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->percent_discount_1,2,',','.') }}</td>
                                    <td style="width:5% !important;padding-left:7px" align="">{{ number_format($row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->percent_discount_2,2,',','.') }}</td>
                                    <td style="width:5% !important" align="right">{{ number_format($row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->discount_3,2,',','.') }}</td>

                                    <td style="width:20% !important" align="right">{{ number_format($row->total,2,',','.') }}</td>
                                    
                                </tr>
                                
                                @endforeach
                                @foreach($data->marketingOrderInvoiceDeliveryDetail as $key => $row)
                                @php
                                    $boxQty = '';
                                    if($row->lookable->isPallet()){
                                        $boxQty = ' ( '.CustomHelper::formatConditionalQty($row->qty * $row->lookable->item->pallet->box_conversion).' BOX )';
                                    }
                                @endphp
                                <tr>
                                    <td style="width:5% !important">{{ ($key + 1) }}</td>
                                    <td style="width:40% !important;padding-left:5px">{{ $row->lookable->item->name.$boxQty }}</td>
                                    <td style="width:10% !important;padding-left:32px" align="right">{{ CustomHelper::formatConditionalQty($row->lookable->qty) }}</td>
                                    <td style="width:5% !important" align="right">{{ CustomHelper::formatConditionalQty(round($row->lookable->qty * $row->lookable->marketingOrderDetail->qty_conversion,3)) }}</td>
                                    <td style="width:5% !important"align="right">{{ $row->lookable->item->uomUnit->code }}</td>
                                    <td style="width:10% !important" align="right">{{ number_format($row->lookable->marketingOrderDetail->price,2,',','.') }}</td>
                                    <td style="width:10% !important;padding-left:7px" align="center">{{ number_format($row->lookable->marketingOrderDetail->percent_discount_1,2,',','.') }}</td>
                                    <td style="width:5% !important;padding-left:7px" align="">{{ number_format($row->lookable->marketingOrderDetail->percent_discount_2,2,',','.') }}</td>
                                    <td style="width:5% !important" align="right">{{ number_format($row->lookable->marketingOrderDetail->discount_3,2,',','.') }}</td>

                                    <td style="width:20% !important" align="right">{{ number_format($row->total,2,',','.') }}</td>
                                    
                                </tr>
                                
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- invoice subtotal -->
                    <div class="invoice-subtotal break-row" style="position: fixed;bottom: 73px; left: 0px; right: 45px;">
                        <div class="row">
                        <div class="column1">
                            <table style="width:100%">
                                <tr class="break-row">
                                    <td style="width: 20%"></td>
                                    <td>
                                        <i>{{ CustomHelper::terbilangWithKoma($data->grandtotal) }} Rupiah
                                    </td>
                                    
                                </tr>
                            </table>
                        </div>
                        <div class="column2">
                            <table style="border:none;" width="74%">
                                <tr class="break-row">
                                    <td align="right"></td>
                                    <td align="right" align="right" style="padding-top: 0px;padding-bottom:0px">{{ number_format($data->subtotal,2,',','.') }}</td>
                                </tr>
                                <tr style="margin-top: -10px !important">
                                    <td align="right"></td>
                                    <td align="right" align="right" style="padding-top: 0px;padding-bottom:0px">{{ number_format($data->downpayment,2,',','.') }}</td>
                                </tr>
                                <tr class="break-row">
                                    <td align="right"></td>
                                    <td align="right" align="right" style="padding-top: 0px;padding-bottom:0px">{{ number_format($data->total,2,',','.') }}</td>
                                </tr>
                                <tr class="break-row">
                                    <td align="right"></td>
                                    <td align="right" align="right" style="padding-top: 0px;padding-bottom:0px">{{ number_format($data->tax,2,',','.') }}</td>
                                </tr>
                                <tr class="break-row">
                                    <td align="right"></td>
                                    <td align="right" align="right" style="padding-top: 0px;padding-bottom:0px">{{ number_format($data->grandtotal,2,',','.') }}</td>
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
</html>