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

            .break-row {
                page-break-inside: avoid;
            }

            .last-header {
                page-break-after: always !important;
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
                .last-header {
                    page-break-after: always !important;
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
                    font-size:0.7em !important;
                }

                .last-header {
                    page-break-after: always !important;
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
                .last-header {
                    page-break-after: always !important;
                }
            }
            
            .invoice-product-details{
                border:1px solid black;
                min-height: auto;
            }

            @page { margin: 3em 3em 6em 3em; }
            /* header { position: fixed; top: -70px; left: 0px; right: 0px; height: 150px; margin-bottom: 10em } */
                
            .preserveLines {
                white-space: pre-line;
            }

        </style>
    </head>
    <body>
        <header>
            <table border="0" width="100%" style="font-size:1em" class="tb-header">
                <tr>
                    <td width="33%">
                        <span class="invoice-number mr-1"># {{ $data->code }}</span>
                        <h3 class="indigo-text">Purchase Down Payment</h3>
                    </td>
                    <td width="33%" align="center">
                        <h3>Untuk Payment Request</h3>
                    </td>
                    <td width="33%">
                        <img src="{{ $image }}" width="50%" style="position: absolute; top:5px; width:20%;right:0;">
                    </td>
                </tr>   
            </table>
            <hr style="border-top: 3px solid black; margin-top:5px">
        </header>
        <main>
            <div class="card">
                <div class="card-content invoice-print-area ">
                    <table border="0" width="100%">
                        <tr>
                            <td width="57%" class="left-align">
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
                            <td width="10%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td align="right">
                                           
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                           <br>
                                        </td>
                                        
                                    </tr>
                                    <tr>
                                        <td >
                                            <br>
                                        </td>
                                        
                                    </tr>
                                </table>
                            </td>
                            <td width="33%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td align="center">
                                            <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($data->code, 'C128')}}" alt="barcode" style="width:80%;" height="5%" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center">
                                            <h1>{{ $data->code }}</h1>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td >
                                            <br>
                                        </td>
                                        
                                    </tr>
                                </table>
                            </td>
                            
                        </tr>
                    </table>
                    <!-- product details table-->
                    
                    @if(count($data->purchaseDownPaymentDetail) > 0)
                    <h6 class="center mt-3">Referensi Order Pembelian</h6>
                        <div class="invoice-product-details">
                            @if(count($data->purchaseDownPaymentDetail) > 0)
                                
                                <table border="1" style="border-collapse:collapse" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="center-align">PO No.</th>
                                            
                                            <th class="center-align">PR No.</th>
                                            
                                            <th class="center-align">Tgl.Post</th>
                                            
                                            <th class="center-align">Tgl.Kirim</th>
                                            <th class="center-align">Keterangan</th>
                                            <th class="center-align">Total</th>
                                            <th class="center-align">DP Total</th>
                                           
                                        </tr>
                                        
                                    </thead>
                                @foreach($data->purchaseDownPaymentDetail as $key => $row)
                                @php
                                $arr_pr=[];
                                    foreach ($row->purchaseOrder->purchaseOrderDetail as $key => $row_detail_po) {
                                        $arr_pr[]=$row_detail_po->purchaseRequestDetail->purchaseRequest->code;
                                    }
                                    
                                @endphp
                                    
                                        
                                        <tbody>
                                            <tr>
                                                <td class="center-align">{{ $row->purchaseOrder->code }}</td>
                                                <td class="center-align">{{ implode(', ',$arr_pr) }}</td>
                                                <td class="center-align">{{ date('d/m/Y',strtotime($row->purchaseOrder->post_date)) }}</td>
                                                <td class="center-align">{{ date('d/m/Y',strtotime($row->purchaseOrder->delivery_date)) }}</td>
                                                <td class="center-align">{{ $row->note }}</td>                                             
                                                <td class="center-align" style="text-align: right">{{ number_format($row->purchaseOrder->grandtotal,2,',','.') }}</td>
                                                <td class="center-align" style="text-align: right">{{ number_format($row->nominal,2,',','.') }}</td>
                                            </tr>
                                        </tbody>
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
                                        <div class="preserveLines" style="text-align:left !important;">
                                            {{ $data->note_external }}
                                        </div>
                                        Terbilang : <i>{{ CustomHelper::terbilangWithKoma($data->grandtotal).' '.$data->currency->document_text }}
                                    </td>
                                    
                                </tr>
                            </table>
                        </div>
                        <div class="column2">
                            <table style="border-collapse:collapse;text-align: right; padding-right:6%;" width="100%">
                                <tr class="break-row">
                                    <td class="right-align" style="padding-right:15px">Subtotal</td>
                                    <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->subtotal,2,',','.') }}</td>
                                </tr>
                                <tr class="break-row">
                                    <td class="right-align" style="padding-right:15px">Diskon</td>
                                    <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->discount,2,',','.') }}</td>
                                </tr>
                                <tr>
                                    <td class="right-align" style="padding-right:15px">Total</td>
                                    <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->total,2,',','.') }}</td>
                                </tr>
                                @if($data->tax > 0)
                                <tr class="break-row">
                                    <td class="right-align" style="padding-right:15px">PPN</td>
                                    <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->tax,2,',','.') }}</td>
                                </tr>
                                @endif
                                @if($data->wtax > 0)
                                <tr class="break-row">
                                    <td class="right-align" style="padding-right:15px">PPh</td>
                                    <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->wtax,2,',','.') }}</td>
                                </tr>
                                @endif
                                <tr class="break-row">
                                    <td class="right-align" style="padding-right:15px">Grandtotal</td>
                                    <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->grandtotal,2,',','.') }}</td>
                                </tr>
                            </table>
                        </div>
                        </div>
                        <table class="table-bot1" width="100%" border="0">
                            <tr>
                                <td class="center-align">
                                    {!! ucwords(strtolower($data->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
                                    <br>
                                    Dibuat oleh,
                                    @if($data->user->signature)
                                        <div>{!! $data->user->signature() !!}</div>
                                    @endif
                                    <div class="{{ $data->user->signature ? '' : 'mt-5' }}">{{ $data->user->name }}</div>
                                    <div class="mt-1">{{ $data->user->position()->exists() ? $data->user->position->Level->name.' - '.$data->user->position->division->name : '-' }}</div>
                                </td>
                                @if($data->approval())
                                    @foreach ($data->approval() as $detail)
                                        @foreach ($detail->approvalMatrix()->where('status','2')->get() as $row)
                                            <td class="center-align">
                                                {{ $row->approvalTemplateStage->approvalStage->approval->document_text }}
                                                @if($row->user->signature)
                                                    <div>{!! $row->user->signature() !!}</div>
                                                @endif
                                                <div class="{{ $row->user->signature ? '' : 'mt-5' }}">{{ $row->user->name }}</div>
                                                @if ($row->user->position()->exists())
                                        <div class="mt-1">{{ $row->user->position->Level->name.' - '.$row->user->position->division->name }}</div>
                                    @endif
                                            </td>
                                        @endforeach
                                    @endforeach
                                @endif
                            </tr>
                        </table>  
                    </div>
                    
                </div>
            </div>
        </main>
        <div class="last-header"></div>
        <header>
            <table border="0" width="100%" style="font-size:1em" class="tb-header">
                <tr>
                    <td width="33%">
                        <span class="invoice-number mr-1"># {{ $data->code }}</span>
                        <h3 class="indigo-text">Purchase Down Payment</h3>
                    </td>
                    <td width="33%" align="center">
                        <h3>Untuk Tutupan</h3>
                    </td>
                    <td width="33%">
                        <img src="{{ $image }}" width="50%" style="position: absolute; top:5px; width:20%;right:0;">
                    </td>
                </tr>   
            </table>
            <hr style="border-top: 3px solid black; margin-top:5px">
        </header>
        <main>
            <div class="card">
                <div class="card-content invoice-print-area ">
                    <table border="0" width="100%">
                        <tr>
                            <td width="57%" class="left-align">
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
                            <td width="10%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td align="right">
                                           
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                           <br>
                                        </td>
                                        
                                    </tr>
                                    <tr>
                                        <td >
                                            <br>
                                        </td>
                                        
                                    </tr>
                                </table>
                            </td>
                            <td width="33%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td align="center">
                                            <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($data->code, 'C128')}}" alt="barcode" style="width:80%;" height="5%" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center">
                                            <h1>{{ $data->code }}</h1>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td >
                                            <br>
                                        </td>
                                        
                                    </tr>
                                </table>
                            </td>
                            
                        </tr>
                    </table>
                    <!-- product details table-->
                    
                    @if(count($data->purchaseDownPaymentDetail) > 0)
                    <h6 class="center mt-3">Referensi Order Pembelian</h6>
                        <div class="invoice-product-details">
                            @if(count($data->purchaseDownPaymentDetail) > 0)
                                
                                <table border="1" style="border-collapse:collapse" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="center-align">PO No.</th>
                                            
                                            <th class="center-align">PR No.</th>
                                            
                                            <th class="center-align">Tgl.Post</th>
                                            
                                            <th class="center-align">Tgl.Kirim</th>
                                            <th class="center-align">Keterangan</th>
                                            <th class="center-align">Total</th>
                                            <th class="center-align">DP Total</th>
                                           
                                        </tr>
                                        
                                    </thead>
                                @foreach($data->purchaseDownPaymentDetail as $key => $row)
                                @php
                                $arr_pr=[];
                                    foreach ($row->purchaseOrder->purchaseOrderDetail as $key => $row_detail_po) {
                                        $arr_pr[]=$row_detail_po->purchaseRequestDetail->purchaseRequest->code;
                                    }
                                    
                                @endphp
                                    
                                        
                                        <tbody>
                                            <tr>
                                                <td class="center-align">{{ $row->purchaseOrder->code }}</td>
                                                <td class="center-align">{{ implode(', ',$arr_pr) }}</td>
                                                <td class="center-align">{{ date('d/m/Y',strtotime($row->purchaseOrder->post_date)) }}</td>
                                                <td class="center-align">{{ date('d/m/Y',strtotime($row->purchaseOrder->delivery_date)) }}</td>
                                                <td class="center-align">{{ $row->note }}</td>                                             
                                                <td class="center-align" style="text-align: right">{{ number_format($row->purchaseOrder->grandtotal,2,',','.') }}</td>
                                                <td class="center-align" style="text-align: right">{{ number_format($row->nominal,2,',','.') }}</td>
                                            </tr>
                                        </tbody>
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
                                        <div class="preserveLines" style="text-align:left !important;">
                                            {{ $data->note_external }}
                                        </div>
                                        Terbilang : <i>{{ CustomHelper::terbilangWithKoma($data->grandtotal).' '.$data->currency->document_text }}
                                    </td>
                                    
                                </tr>
                            </table>
                        </div>
                        <div class="column2">
                            <table style="border-collapse:collapse;text-align: right; padding-right:6%;" width="100%">
                                <tr class="break-row">
                                    <td class="right-align" style="padding-right:15px">Subtotal</td>
                                    <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->subtotal,2,',','.') }}</td>
                                </tr>
                                <tr class="break-row">
                                    <td class="right-align" style="padding-right:15px">Diskon</td>
                                    <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->discount,2,',','.') }}</td>
                                </tr>
                                <tr>
                                    <td class="right-align" style="padding-right:15px">Total</td>
                                    <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->total,2,',','.') }}</td>
                                </tr>
                                @if($data->tax > 0)
                                <tr class="break-row">
                                    <td class="right-align" style="padding-right:15px">PPN</td>
                                    <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->tax,2,',','.') }}</td>
                                </tr>
                                @endif
                                @if($data->wtax > 0)
                                <tr class="break-row">
                                    <td class="right-align" style="padding-right:15px">PPh</td>
                                    <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->wtax,2,',','.') }}</td>
                                </tr>
                                @endif
                                <tr class="break-row">
                                    <td class="right-align" style="padding-right:15px">Grandtotal</td>
                                    <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->grandtotal,2,',','.') }}</td>
                                </tr>
                            </table>
                        </div>
                        </div>
                        <table class="table-bot1" width="100%" border="0">
                            <tr>
                                <td class="center-align">
                                    {!! ucwords(strtolower($data->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
                                    <br>
                                    Dibuat oleh,
                                    @if($data->user->signature)
                                        <div>{!! $data->user->signature() !!}</div>
                                    @endif
                                    <div class="{{ $data->user->signature ? '' : 'mt-5' }}">{{ $data->user->name }}</div>
                                    <div class="mt-1">{{ $data->user->position()->exists() ? $data->user->position->Level->name.' - '.$data->user->position->division->name : '-' }}</div>
                                </td>
                                @if($data->approval())
                                    @foreach ($data->approval() as $detail)
                                        @foreach ($detail->approvalMatrix()->where('status','2')->get() as $row)
                                            <td class="center-align">
                                                {{ $row->approvalTemplateStage->approvalStage->approval->document_text }}
                                                @if($row->user->signature)
                                                    <div>{!! $row->user->signature() !!}</div>
                                                @endif
                                                <div class="{{ $row->user->signature ? '' : 'mt-5' }}">{{ $row->user->name }}</div>
                                                @if ($row->user->position()->exists())
                                        <div class="mt-1">{{ $row->user->position->Level->name.' - '.$row->user->position->division->name }}</div>
                                    @endif
                                            </td>
                                        @endforeach
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
