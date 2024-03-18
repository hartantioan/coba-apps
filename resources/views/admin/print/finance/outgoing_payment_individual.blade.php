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
                    zoom:0.6;
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
                    font-size:0.7em !important;
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
                    font-size:0.8em !important;
                }
                .table-bot1 td{
                    font-size:0.9em !important;
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
                
            td {
                vertical-align: top !important;
            }
           
        </style>
    </head>
    <body>
        <header>
            <table border="0" width="100%" style="font-size:1em" class="tb-header">
                <tr>
                    <td width="33%" class="left-align" >
                        <span class="invoice-number mr-1"># {{ $data->code }}</span>
                        <br>
                        <small style="font-size:1em"> <small>Tgl.Outgoing:</small>
                        <small>{{ date('d/m/Y',strtotime($data->post_date)) }}</small>
                        <h2 class="indigo-text">Outgoing Payment</h2>
                    </td>
                    <td width="33%" class="right-align">
                        
                        
                   
                    </td>
                    
                    <td width="34%" class="right-align">
                        
                        <img src="{{ $image }}" width="50%" style="position: absolute; top:5px; width:20%;right:0;">
                       
                    </td>
                </tr>
                
            </table>
            <hr style="border-top: 3px solid black; margin-top:-15px">
        </header>
        <main>
            <div class="card">
                <div class="card-content invoice-print-area ">
                    <table border="0" width="100%" class="mt-3">
                        <tr>
                            <td width="33%" class="left-align">
                                <table border="0" width="100%" class="tbl-info">
                                    <tr>
                                        <td width="40%">
                                            Partner Bisnis
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="60%">
                                            {{ $data->account()->exists() ? $data->account->name : '' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Alamat
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="60%">
                                            {{ $data->account()->exists() ? $data->account->address : '' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Telepon
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="60%">
                                            {{ $data->account()->exists() ? $data->account->phone.' / '.$data->account->office_no : '' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Dibayar dari
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="60%">
                                            {{ $data->coaSource->name }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="33%" class="left-align" style="vertical-align: top !important;">
                                <table border="0" width="100%" class="tbl-info">
                                    <tr>
                                        <td width="40%">
                                            Rekening Tujuan
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="60%">
                                            {{ $data->paymentRequest->account_no }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Bank
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="60%">
                                            {{ $data->paymentRequest->account_bank }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Nama
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="60%">
                                            {{ $data->paymentRequest->account_name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Payment Req.
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="60%">
                                            {{ $data->paymentRequest->code }}
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
                                            <h2>{{ $data->code }}</h2>
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
                                    <th class="center">Nominal PR</th>
                                    <th class="center">Biaya Admin</th>
                                    <th class="center">Total Bayar</th>
                                </tr>
                                <tr>
                                    <th class="right-align" style="font-weight: !important;font-size:1em !important">{{ number_format($data->total,2,',','.') }}</th>
                                    <th class="right-align" style="font-weight: !important;font-size:1em !important">{{ number_format($data->admin,2,',','.') }}</th>
                                    <th class="right-align" style="font-weight:normal !important;font-size:1em !important">{{ number_format($data->grandtotal,2,',','.') }}</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th colspan="3" style="border: none !important" border="0">Terbilang : <i>{{ CustomHelper::terbilangWithKoma($data->grandtotal).' '.ucwords($data->currency->document_text) }}</i></th>
                                </tr>
                                <tr>
                                    <th colspan="3" style="border: none !important" border="0">Terbilang : <i>{{ CustomHelper::terbilangWithKoma($data->grandtotal * $data->currency_rate).' Rupiah' }}</i></th>
                                </tr>
                            </tfoot>
                            
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
                        <div class="column2">
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