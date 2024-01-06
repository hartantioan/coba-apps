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

            @page { margin: 6em 3em 6em 3em; }
            header { position: fixed; top: -85px; left: 0px; right: 0px; height: 150px; margin-bottom: 10em }
                
        
           
        </style>
    </head>
    <body>
        <header>
            <table border="0" width="100%" style="" class="tb-header">
                <tr>
                    <td width="33%" class="left-align" >
                        <table border="0" width="100%">
                            <tr>
                                <td>
                                    <span class="invoice-number mr-1"># {{ $data->code }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="margin-top: -2px;">
                                    <small>Diajukan:</small>
                                    <span>{{ date('d/m/y',strtotime($data->post_date)) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="margin-top: -2px;">
                                    <small>Dibayar:</small>
                                    <span>
                                        {{ date('d/m/y',strtotime($data->pay_date)) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="margin-top: -2px;">
                                    <small>Outgoing Payment:</small>
                                    <span>
                                        @if ($data->outgoingPayment()->exists())
                                            {{ date('d/m/y',strtotime($data->outgoingPayment->pay_date)) }}
                                        @else
                                            -
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td width="33%" align="center">
                        <h2 class="indigo-text">Payment Request</h2>
                    </td>
                    <td width="33%" class="right-align">
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
                        <tr
                            <td width="56%" class="left-align" class="tbl-info">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="40%">
                                           Tipe Pembayaran
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="60%">
                                            {{ $data->paymentType() }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Partner Bisnis
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="60%">
                                            {{ $data->account->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Vendor Bank
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="60%">
                                            {{ $data->account_bank }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Vendor Bank Account No
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="60%">
                                            {{ $data->account_no }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Vendor Bank Account Name
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="60%">
                                            {{ $data->account_name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                           Kas / Bank
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="60%">
                                            {{ $data->coa_source_id ? $data->coaSource->name : '-' }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="10%" class="left-align">
                                
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
                                </table>
                            </td>
                        </tr>
                    </table>
                    <!-- product details table-->
                    
                    <div class="invoice-product-details mt-2">
                        <table class="bordered table-with-breaks table-data-item " border="1" style="border-collapse:collapse;" width="100%"  >
                            <thead>
                                <tr>
                                    <th class="center">Referensi</th>
                                    <th class="center">Tipe</th>
                                    <th class="center">Tgl.Tenggat</th>
                                    <th class="center">Keterangan</th>
                                    <th class="center">Coa</th>
                                    <th class="center">Plant</th>
                                    <th class="center">Line</th>
                                    <th class="center">Mesin</th>
                                    <th class="center">Departemen</th>
                                    <th class="center">Proyek</th>
                                    <th class="center">Bayar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $total = 0;
                                @endphp
                                @foreach($data->paymentRequestDetail as $row)
                                <tr>
                                    <td>{{ $row->lookable->code }}</td>
                                    <td align="center">{{ $row->type() }}</td>
                                    <td align="center">{{ $row->purchaseInvoice() ? date('d/m/y',strtotime($row->lookable->due_date)) : '-' }}</td>
                                    <td>{{ $row->note }}</td>
                                    <td>{{ $row->coa->name }}</td>
                                    <td>{{ $row->place()->exists() ? $row->place->code : '-' }}</td>
                                    <td>{{ $row->line()->exists() ? $row->line->code : '-' }}</td>
                                    <td>{{ $row->machine()->exists() ? $row->machine->code : '-' }}</td>
                                    <td>{{ $row->department()->exists() ? $row->department->code : '-' }}</td>
                                    <td>{{ $row->project()->exists() ? $row->project->name : '-' }}</td>
                                    <td align="right">{{ number_format($row->nominal,2,',','.') }}</td>
                                </tr>
                                @php
                                    $total += $row->nominal;
                                @endphp
                                @endforeach
                            </tbody>
                            
                        </table>
                    </div>
                    <!-- invoice subtotal -->
                    <div class="invoice-subtotal break-row">
                        <div class="row">
                            <div class="column1">
                                <table style="width:100%" class="table-bot">
                                    <tr class="break-row">
                                        <td>
                                            <div class="mt-3">
                                                Catatan : {{ $data->note }}
                                            </div>
                                            Terbilang : <i>{{ CustomHelper::terbilangWithKoma($data->grandtotal).' '.$data->currency->document_text }}
                                        </td>
                                        
                                    </tr>
                                </table>
                            </div>
                            <div class="column2">
                                <table style="border-collapse:collapse;text-align:right" width="74%" class="table-bot">
                                    <tr class="break-row">
                                        <td class="right-align">Total</td>
                                        <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->total,2,',','.') }}</td>
                                    </tr>
                                    <tr class="break-row">
                                        <td class="right-align">Pembulatan</td>
                                        <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->rounding,2,',','.') }}</td>
                                    </tr>
                                    <tr class="break-row">
                                        <td class="right-align">Admin</td>
                                        <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->admin,2,',','.') }}</td>
                                    </tr class="break-row">
                                    <tr>
                                        <td class="right-align">Grandtotal</td>
                                        <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->grandtotal,2,',','.') }}</td>
                                    </tr class="break-row">
                                    <tr>
                                        <td class="right-align">Bayar (Piutang)</td>
                                        <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->payment,2,',','.') }}</td>
                                    </tr class="break-row">
                                    <tr>
                                        <td class="right-align">Sisa</td>
                                        <td class="right-align" style="border:0.6px solid black;">{{ number_format($data->balance,2,',','.') }}</td>
                                    </tr class="break-row">                              
                                </table>
                            </div>
                        </div>
                        <table class="table-bot1" width="100%" border="0">
                            <tr>
                                <td class="center-align">
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
                                                <div class="mt-1">{{ $row->user->position->Level->name.' - '.$row->user->position->division->name }}</div>
                                            </td>
                                        @endforeach
                                    @endforeach
                                @endif
                                <td class="center-align">
                                    @if ($data->payment_type == '2')
                                        <img src="{{ $e_banking }}" width="50%" style="position: absolute; width:20%; right:0px;">
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>