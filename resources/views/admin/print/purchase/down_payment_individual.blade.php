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
                    font-size:11px !important;
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
                min-height: 23%;
            }

            @page { margin: 5em 3em 6em 3em; }
            header { position: fixed; top: -80px; left: 0px; right: 0px; height: 150px; margin-bottom: 10em }
                
        
           
        </style>
    </head>
    <body>
        <header>
            <table border="0" width="100%" style="font-size:1em" class="tb-header">
                <tr>
                    <td width="83%" class="left-align" >
                        <tr>
                            <td>
                                <span class="invoice-number mr-1">INVOICE # {{ $data->code }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="margin-top: -2px;">
                                <small style="font-size:1em">Diajukan: {{ date('d/m/y',strtotime($data->post_date)) }}</small>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h5 class="indigo-text">Purchase Down Payment</h5>
                            </td>
                        </tr>
                                
                        
                    </td>
                    <td width="33%" class="right-align">
                        
                        
                   
                    </td>
                    
                    <td width="34%" class="right-align">
                        
                            <img src="{{ $image }}" width="50%" style="position: absolute; top:5px; width:20%">
                       
                    </td>
                </tr>
                
            </table>
            <hr style="border-top: 3px solid black; margin-top:-2%">
        </header>
        <main>
            <div class="card">
                <div class="card-content invoice-print-area ">
                    <table border="0" width="100%">
                        <tr>
                            <td width="50%" class="left-align">
                                <table border="0" width="50%" class="tbl-info">
                                    <tr>
                                        <td width="25%">
                                            Supplier
                                        </td>
                                        <td width="50%">
                                            {{ $data->supplier->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="50%">
                                            Alamat
                                        </td>
                                        <td width="50%">
                                            {{ $data->supplier->address }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="50%">
                                            Telepon
                                        </td>
                                        <td width="50%">
                                            {{ $data->supplier->phone.' / '.$data->supplier->office_no }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="50%">
                                            Tipe Pembayaran
                                        </td>
                                        <td width="50%">
                                            {{ $data->type() }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            
                        </tr>
                    </table>
                    <!-- product details table-->
                    @if(count($data->purchaseDownPaymentDetail) > 0)
                        <div class="invoice-product-details">
                            @if(count($data->purchaseDownPaymentDetail) > 0)
                                <h6 class="center mt-3">Referensi Order Pembelian</h6>
                                @foreach($data->purchaseDownPaymentDetail as $key => $row)
                                    <table class="bordered mt-3 purple lighten-5">
                                        <thead>
                                            <tr>
                                                <th class="center-align">PO No.</th>
                                                <th class="center-align">{{ $row->purchaseOrder->code }}</th>
                                                <th class="center-align">PR No.</th>
                                                <th class="center-align">{{ $row->purchaseOrder->purchaseRequest()->exists() ? $row->purchaseOrder->purchaseRequest->code : '-' }}</th>
                                                <th class="center-align">Tgl.Post</th>
                                                <th class="center-align">{{ date('d/m/y',strtotime($row->purchaseOrder->post_date)) }}</th>
                                                <th class="center-align">Tgl.Kirim</th>
                                                <th class="center-align">{{ date('d/m/y',strtotime($row->purchaseOrder->delivery_date)) }}</th>
                                            </tr>
                                            <tr>
                                                <th class="center-align">Keterangan</th>
                                                <th class="center-align">{{ $row->note }}</th>
                                                <th class="center-align">Total</th>
                                                <th class="center-align">{{ number_format($row->purchaseOrder->grandtotal,2,',','.') }}</th>
                                                <th class="center-align">DP Total</th>
                                                <th class="center-align">{{ number_format($row->nominal,2,',','.') }}</th>
                                                <th class="center-align"></th>
                                                <th class="center-align"></th>
                                            </tr>
                                        </thead>
                                    </table>
                                @endforeach
                            @endif
                        </div>
                    @endif
                    <!-- invoice subtotal -->
                    <div class="invoice-subtotal break-row">
                        <div class="row">
                        <div class="column1">
                            <table style="width:100%">
                                <tr class="break-row">
                                    <td>
                                        Rekening :
                                        {{ $data->supplier->defaultBank() ? $data->supplier->defaultBank() : ' - ' }}
                                        <div class="mt-3">
                                            Catatan : {{ $data->note }}
                                        </div>
                                        Terbilang : <i>{{ CustomHelper::terbilang($data->grandtotal).' '.$data->currency->document_text }}
                                    </td>
                                    
                                </tr>
                            </table>
                        </div>
                        <div class="column2">
                            <table style="border-collapse:collapse; " width="74%">
                                <tr class="break-row">
                                    <td class="right-align">Subtotal</td>
                                    <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->subtotal,2,',','.') }}</td>
                                </tr>
                                <tr class="break-row">
                                    <td class="right-align">Diskon</td>
                                    <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->discount,2,',','.') }}</td>
                                </tr>
                                <tr>
                                    <td class="right-align">Total</td>
                                    <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->total,2,',','.') }}</td>
                                </tr>
                                <tr class="break-row">
                                    <td class="right-align">PPN</td>
                                    <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->tax,2,',','.') }}</td>
                                </tr>
                                <tr class="break-row">
                                    <td class="right-align">Grandtotal</td>
                                    <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->grandtotal,2,',','.') }}</td>
                                </tr>
                            </table>
                        </div>
                        </div>
                        <table class="table-bot1" width="100%" border="0">
                            <tr>
                                <td class="center-align">
                                    {!! ucwords(strtolower($data->user->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
                                    <br>
                                    Dibuat oleh,
                                    @if($data->user->signature)
                                        <div>{!! $data->user->signature() !!}</div>
                                    @endif
                                    <div class="{{ $data->user->signature ? '' : 'mt-5' }}">{{ $data->user->name }}</div>
                                    <div class="mt-1">{{ $data->user->position->name.' - '.$data->user->department->name }}</div>
                                </td>
                                @if($data->approval())
                                    @foreach ($data->approval()->approvalMatrix()->where('status','2')->get() as $row)
                                        <td class="center-align">
                                            {{ $row->approvalTemplateStage->approvalStage->approval->document_text }}
                                            @if($row->user->signature)
                                                <div>{!! $row->user->signature() !!}</div>
                                            @endif
                                            <div class="{{ $row->user->signature ? '' : 'mt-5' }}">{{ $row->user->name }}</div>
                                            <div class="mt-1">{{ $row->user->position->name.' - '.$row->user->department->name }}</div>
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                        </table>  
                    </div>
                    
                </div>
            </div>
        </main>
    </body>
</html>
