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
                    font-size:0.8em !important;
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
                                <span class="invoice-number mr-1">Retirement # {{ $data->code }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="margin-top: -2px;">
                                <small>Diajukan:</small>
                                <span>{{ date('d/m/y',strtotime($data->post_date)) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h5 class="indigo-text">Retirement Aset Perusahaan</h5>
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
                                        Perusahaan
                                    </td>
                                    <td width="60%">
                                        {{ $data->company->name }}
                                    </td>
                                </tr>
                                <tr>
                                    <td width="40%">
                                        Catatan
                                    </td>
                                    <td width="60%">
                                        {{ $data->note }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td width="33%" class="left-align">
                            <table border="0" width="100%">
                                <tr>
                                    <td align="right">
                                        <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($data->code, 'C128')}}" alt="barcode" style="width:80%;" height="5%" />
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
                    </tr>
                </table>
                
                <div class="invoice-product-details mt-2">
                    <table class="bordered table-with-breaks table-data-item " border="1" style="border-collapse:collapse;" width="100%"  >
                        <thead>
                            <tr>
                                <th class="center">No.</th>
                                <th class="center">Aset</th>
                                <th class="center">Qty</th>
                                <th class="center">Satuan</th>
                                <th class="center">Nominal Aset</th>
                                <th class="center">Nominal Retirement</th>
                                <th class="center">Keterangan</th>
                                <th class="center">Coa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data->retirementDetail as $key => $row)
                                <tr>
                                    <td class="center-align">{{ $key + 1 }}</td>
                                    <td>{{ $row->asset->name }}</td>
                                    <td class="center-align">{{ number_format($row->qty,3,',','.') }}</td>
                                    <td class="center-align">{{ $row->unit->code }}</td>
                                    <td class="right-align">{{ number_format($row->asset->nominal,3,',','.') }}</td>
                                    <td class="right-align">{{ number_format($row->retirement_nominal,3,',','.') }}</td>
                                    <td>{{ $row->note }}</td>
                                    <td>{{ $row->coa->code.' - '.$row->coa->name }}</td>
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
                                    {!! ucwords(strtolower($data->user->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
                                    <div class="mt-3">
                                        Catatan : {{ $data->note }}
                                    </div>
                                   
                                </td>
                                
                            </tr>
                        </table>
                    </div>
                    <div class="column2">
                    </div>
                    </div>
                    <table class="mt-3" width="100%" border="0">
                        <tr>
                            <td class="center-align">
                               
                                <br>
                                Dibuat oleh,
                                @if($data->user->signature)
                                    <div>{!! $data->user->signature() !!}</div>
                                @endif
                                <div class="{{ $data->user->signature ? '' : 'mt-5' }}">{{ $data->user->name }}</div>
                                <div class="mt-1">{{ $data->user->position->name.' - '.$data->user->department->name }}</div>
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
                                            <div class="mt-1">{{ $row->user->position->name.' - '.$row->user->department->name }}</div>
                                        </td>
                                    @endforeach
                                @endforeach
                            @endif
                        </tr>
                    </table>  
                </div>
                
                
            </div>
        </main>
    </body>
</html>

