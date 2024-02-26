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
                    <td width="33%" class="left-align">
                        <span class="invoice-number mr-1" style="font-size:1em"># {{ $data->code }}</span>
                        <br>
                        <small style="font-size:1em">Diajukan: {{ date('d/m/Y',strtotime($data->post_date)) }}</small>
                    </td>
                    <td width="33%" align="center">
                        <h2>Landed Cost</h2>
                    </td>
                    <td width="34%" align="right">
                        <img src="{{ $image }}" width="50%" style="position: absolute; top:5px; width:20%;right:0;">
                    </td>
                </tr>
                
            </table>
            <hr style="border-top: 3px solid black; margin-top:5px">
        </header>
        <main>
            <div class="card">
                <div class="card-content invoice-print-area">
                    <table border="0" width="100%" class="tbl-info">
                        <tr>
                            <td width="66%" class="left-align" style="vertical-align: top !important;">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="20%">
                                            Broker
                                        </td>
                                        <td width="80%">
                                            {{ $data->vendor->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Alamat
                                        </td>
                                        <td>
                                            {{ $data->vendor->address }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Telepon
                                        </td>
                                        <td>
                                            {{ $data->vendor->phone.' / '.$data->vendor->office_no }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            No. Referensi
                                        </td>
                                        <td>
                                            {{ $data->reference }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="33%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td align="center">
                                            <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($data->code, 'C128')}}" alt="barcode" style="width:80%;" height="2%" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center">
                                            <h3>{{ $data->code }}</h3>
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
                                    <th align="center" colspan="9">Daftar Item</th>
                                </tr>
                                <tr>
                                    <th class="center">No</th>
                                    <th class="center">Item</th>
                                    <th class="center">Plant</th>
                                    <th class="center">Gudang</th>
                                    <th class="center">Qty</th>
                                    <th class="center">Satuan</th>
                                    <th class="center">Surat Jalan</th>
                                    <th class="center">Landed Cost</th>
                                    <th class="center">Grandtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data->landedCostDetail as $key => $row)
                                <tr>
                                    <td class="center" style="text-align: center">{{ ($key + 1) }}</td>
                                    <td>{{ $row->item->code.' - '.$row->item->name }}</td>
                                    <td>{{ $row->place->code  }}</td>
                                    <td>{{ $row->warehouse->name }}</td>
                                    <td align="right">{{ $row->qty }}</td>
                                    <td align="center">{{ $row->item->uomUnit->code }}</td>
                                    <td>{{ $row->goodReceiptDetail() ? $row->lookable->goodReceipt->delivery_no : '-' }}</td>
                                    <td align="right">{{ number_format($row->nominal,2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->priceFinalCogs() * $row->qty,2,',','.') }}</td>
                                </tr>
                                @endforeach
                                
                            </tbody>
                        </table>
                    </div>

                    <div class="invoice-product-details" style="margin-top:25px;">
                        <table class="bordered table-data-item" border="1" style="border-collapse:collapse;" width="100%">
                            <thead>
                                <tr>
                                    <th align="center" colspan="6">Daftar Biaya</th>
                                </tr>
                                <tr>
                                    <th>No</th>
                                    <th>Deskripsi</th>
                                    <th>Total</th>
                                    <th>PPN</th>
                                    <th>PPh</th>
                                    <th>Grandtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data->landedCostFeeDetail as $key => $row)
                                <tr>
                                    <td align="center">{{ $key + 1 }}</td>
                                    <td>{{ $row->landedCostFee->name }}</td>
                                    <td align="right">{{ number_format($row->total,2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->tax,2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->wtax,2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->grandtotal,2,',','.') }}</td>
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
                                        Terbilang : <i>{{ CustomHelper::terbilangWithKoma($data->grandtotal).' '.$data->currency->document_text }}
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
                                    <td class="right-align" style="padding-right:15px">PPh</td>
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
                                    {!! ucwords(strtolower($data->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
                                    <br>
                                    Dibuat oleh,
                                    @if($data->user->signature)
                                        <div>{!! $data->user->signature() !!}</div>
                                    @endif
                                    <div class="{{ $data->user->signature ? '' : 'mt-5' }}">{{ $data->user->name }}</div>
                                    <div class="mt-1">{{ $data->user->position->Level->name.' - '.$data->user->position->division->name }}</div>
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
