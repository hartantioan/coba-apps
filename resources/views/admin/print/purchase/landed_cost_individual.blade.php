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
                    font-size:0.9em !important;
                }
                .table-data-item th{
                    border:0.6px solid black;
                }
                .table-bot td{
                    font-size:1em !important;
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
            header { position: fixed; top: -70px; left: 0px; right: 0px; height: 150px; margin-bottom: 10em }
        </style>
    </head>
    <body>
        <header>
            <table border="0" width="100%" style="font-size:1em" class="tb-header">
                <tr>
                    <td width="83%" class="left-align" >
                        <tr>
                            <td>
                                <span class="invoice-number mr-1" style="font-size:1em">NO # {{ $data->code }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="margin-top: -2px;">
                                <small style="font-size:1em">Diajukan: {{ date('d/m/y',strtotime($data->post_date)) }}</small>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h5 style="margin-top: -2px">Landed Cost</h5>
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
                <div class="card-content invoice-print-area">
                    <table border="0" width="100%" class="tbl-info">
                        <tr>
                            <td width="33%" class="left-align" style="vertical-align: top !important;">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="40%">
                                            Dari
                                        </td>
                                        <td width="60%">
                                            {{ $data->user->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Posisi
                                        </td>
                                        <td width="60%">
                                            {{ $data->user->position->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Depart.
                                        </td>
                                        <td width="60%">
                                            {{ $data->user->department->name }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="33%" class="left-align" style="vertical-align: top !important;">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="40%">
                                            Vendor
                                        </td>
                                        <td width="60%">
                                            {{ $data->vendor->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Alamat
                                        </td>
                                        <td width="60%">
                                            {{ $data->vendor->address }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Telepon
                                        </td>
                                        <td width="60%">
                                            {{ $data->vendor->phone.' / '.$data->vendor->office_no }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="33%" class="left-align" style="vertical-align: top !important;">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="40%">
                                            Lampiran
                                        </td>
                                        <td width="60%">
                                            <a href="{{ $data->attachment() }}" target="_blank"><i class="material-icons">attachment</i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Status
                                        </td>
                                        <td width="60%">
                                            {!! $data->status().''.($data->void_id ? '<div class="mt-2">oleh '.$data->voidUser->name.' tgl. '.date('d M Y',strtotime($data->void_date)).' alasan : '.$data->void_note.'</div>' : '') !!}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <div class="invoice-product-details mt-3  ">
                        <table class="bordered table-data-item" border="1" style="border-collapse:collapse;" width="100%">
                            <thead>
                                <tr>
                                    <th class="center">No</th>
                                    <th class="center">Plant</th>
                                    <th class="center">Departemen</th>
                                    <th class="center">Gudang</th>
                                    <th class="center">Item</th>
                                    <th class="center">Qty</th>
                                    <th class="center">Satuan</th>
                                    <th class="center">Harga Total</th>
                                    <th class="center">Harga Satuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data->landedCostDetail as $key => $row)
                                <tr>
                                    <td class="center" style="text-align: center">{{ ($key + 1) }}</td>
                                    <td>{{ $row->place->name.' - '.$row->place->company->name }}</td>
                                    <td>{{ $row->department->name }}</td>
                                    <td>{{ $row->warehouse->name }}</td>
                                    <td>{{ $row->item->name }}</td>
                                    <td style="text-align: center">{{ $row->qty }}</td>
                                    <td style="text-align: center">{{ $row->item->uomUnit->code }}</td>
                                    <td class="right-align" style="text-align: right">{{ number_format($row->nominal,2,',','.') }}</td>
                                    <td class="right-align" style="text-align: right">{{ number_format(round($row->nominal / $row->qty,3),2,',','.') }}</td>
                                </tr>
                                @endforeach
                                
                            </tbody>
                        </table>
                    </div>

                    <div class="invoice-subtotal break-row">
                        <div class="row">
                        <div class="column1">
                            <table style="width:100%" class="table-bot">
                                <tr class="break-row">
                                    <td>
                                        Terbilang : <i>{{ CustomHelper::terbilang($data->grandtotal) }}
                                    </td>
                                    
                                </tr>
                            </table>
                        </div>
                        <div class="column2" >
                            <table class="table-bot" style="border-collapse:collapse;text-align: right; padding-right:6%;" width="100%">
                                <tr>
                                    <td class="right-align" style="padding-right:15px" >Total</td>
                                    <td class="right-align" style="border:0.6px solid black;padding-left:20px;" width="25.5%">{{ number_format($data->total,2,',','.') }}</td>
                                </tr class="break-row">
                                <tr class="break-row">
                                    <td class="right-align" style="padding-right:15px">PPN</td>
                                    <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->tax,2,',','.') }}</td>
                                </tr>
                                <tr class="break-row">
                                    <td class="right-align" style="padding-right:15px">PPH</td>
                                    <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->wtax,2,',','.') }}</td>
                                </tr>
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
