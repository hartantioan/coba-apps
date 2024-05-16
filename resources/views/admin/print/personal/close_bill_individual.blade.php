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

            .center-align {
                text-align: center !important;
            }

            .right-align {
                text-align: right !important;
            }

            @media only screen and (max-width : 768px) {
                .invoice-print-area {
                    zoom:0.4;
                }
            }
        
            @media only screen and (max-width : 992px) {
                .invoice-print-area {
                    zoom:0.8;
                    font-size:11px !important;
                }

                table > thead > tr > th {
                    font-size:13px !important;
                    font-weight: 800 !important;
                }
                td{
                    font-size:0.9em !important;
                }
                .tb-header td{
                    font-size:0.8em !important;
                }
                .tbl-info td{
                    font-size:1em !important;
                }
                .table-data-item td{
                    font-size:0.8em !important;
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
                    <td width="33%" class="left-align" >
                        <tr>
                            <td>
                                <span class="invoice-number mr-1"># {{ $data->code }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h2 class="indigo-text">{{ $title }}</h2>
                            </td>
                        </tr>
                                
                        
                    </td>
                    <td width="33%" class="right-align">
                        
                        
                   
                    </td>
                    
                    <td width="34%" align="right">
                        
                            <img src="{{ $image }}" width="50%" style="position: absolute; top:5px; width:20%;right:0;">
                       
                    </td>
                </tr>
                
            </table>
            <hr style="border-top: 3px solid black; margin-top:-20px">
        </header>
        <main style="margin-top:20px;">
            <div class="card">
                <div class="card-content invoice-print-area">
                    <table border="0" width="100%">
                        <tr>
                            <td width="33%" class="left-align" style="vertical-align:top;">
                                <table border="0" width="100%" class="tbl-info">
                                    <tr>
                                        <td width="40%" style="vertical-align:top;">
                                            Nama
                                        </td>
                                        <td width="1%" style="vertical-align:top;">
                                            :
                                        </td>
                                        <td width="60%">
                                            {{ $data->user->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%" style="vertical-align:top;">
                                            Telp.
                                        </td>
                                        <td width="1%" style="vertical-align:top;">
                                            :
                                        </td>
                                        <td width="60%">
                                            {{ $data->user->phone }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="33%" class="left-align" style="vertical-align:top;">
                                <table border="0" width="100%" class="tbl-info">
                                    <tr>
                                        <td width="40%" style="vertical-align:top;">
                                            Posisi
                                        </td>
                                        <td width="1%" style="vertical-align:top;">
                                            :
                                        </td>
                                        <td width="60%">
                                            {{ $data->user->position_id ? $data->user->position->name : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%" style="vertical-align:top;">
                                            Tgl.Pengajuan
                                        </td>
                                        <td width="1%" style="vertical-align:top;">
                                            :
                                        </td>
                                        <td width="60%">
                                            {{ date('d/m/Y',strtotime($data->post_date)) }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="33%" class="left-align" style="vertical-align:top;">
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
                                </table>
                            </td>
                        </tr>
                    </table>
                    <!-- product details table-->
                    <div class="invoice-product-details mt-2">
                        <table class="bordered table-with-breaks table-data-item " border="1" style="border-collapse:collapse;" width="100%"  >
                            <thead>
                                <tr>
                                    <th class="center" colspan="4">Daftar Dokumen Terpakai</th>
                                </tr>
                                <tr>
                                    <th class="center">No</th>
                                    <th class="center">No.Dokumen</th>
                                    <th class="center">Keterangan</th>
                                    <th class="center">Grandtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data->personalCloseBillDetail as $key => $row)
                                <tr>
                                    <td class="center-align">{{ $key + 1 }}</td>
                                    <td>{{ $row->fundRequest->code.' - '.$row->fundRequest->listPaymentRequest() }}</td>
                                    <td>{{ $row->note }}</td>
                                    <td class="right-align">{{ number_format($row->nominal,2,',','.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            
                        </table>
                    </div>
                    <div class="invoice-product-details" style="margin-top:10px;">
                        <table class="bordered table-with-breaks table-data-item " border="1" style="border-collapse:collapse;" width="100%"  >
                            <thead>
                                <tr>
                                    <th class="center" colspan="8">Daftar Biaya</th>
                                </tr>
                                <tr>
                                    <th class="center">Item</th>
                                    <th class="center">Jum.</th>
                                    <th class="center">Sat.</th>
                                    <th class="center">Harga @</th>
                                    <th class="center">Subtotal</th>
                                    <th class="center">PPN</th>
                                    <th class="center">PPh</th>
                                    <th class="center">Grandtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data->personalCloseBillCost as $row)
                                <tr>
                                    <td>{{ $row->note }}</td>
                                    <td class="center-align">{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
                                    <td class="center-align">{{ $row->unit->code }}</td>
                                    <td class="right-align">{{ number_format($row->price,2,',','.') }}</td>
                                    <td class="right-align">{{ number_format($row->total,2,',','.') }}</td>
                                    <td class="right-align">{{ number_format($row->tax,2,',','.') }}</td>
                                    <td class="right-align">{{ number_format($row->wtax,2,',','.') }}</td>
                                    <td class="right-align">{{ number_format($row->grandtotal,2,',','.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            
                        </table>
                    </div>
                    <!-- invoice subtotal -->
                    <div class="invoice-subtotal break-row">
                        <div class="row">
                            <div class="column1">
                                <table style="width:100%">
                                    <tr class="break-row">
                                        <td>
                                            <div class="mt-3">
                                                Catatan : {{ $data->note }}
                                            </div>
                                        </td>
                                        
                                    </tr>
                                </table>
                            </div>
                            <div class="column2" align="right">
                                <table style="border-collapse:collapse;right:0;" width="95%" class="table-bot" style="font-size:1.1rem !important;">
                                    <tr class="break-row">
                                        <td class="right-align" width="50%">Total</td>
                                        <td class="right-align" width="50%" style="border:0.6px solid black;">{{ number_format($data->total,2,',','.') }}</td>
                                    </tr>
                                    <tr class="break-row">
                                        <td class="right-align">PPN</td>
                                        <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->tax,2,',','.') }}</td>
                                    </tr class="break-row">
                                    <tr class="break-row">
                                        <td class="right-align">PPh</td>
                                        <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->wtax,2,',','.') }}</td>
                                    </tr class="break-row">
                                    <tr>
                                        <td class="right-align">Grandtotal</td>
                                        <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->grandtotal,2,',','.') }}</td>
                                    </tr class="break-row">                          
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