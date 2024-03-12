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
                                <span class="invoice-number mr-1">Tutupan BS # {{ $data->code }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h2 class="indigo-text">Tutupan BS</h2>
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
            <hr style="border-top: 3px solid black; margin-top:-2%">
        </header>
        <main>
            <div class="card">
                <div class="card-content invoice-print-area ">
                    <table border="0" width="100%">
                        <tr>
                            <td width="66%" class="left-align">
                                <table border="0" width="50%" class="tbl-info">
                                    <tr>
                                        <td width="40%">
                                            Nama
                                        </td>
                                        <td width="60%">
                                            {{ $data->user->name.' - '.$data->user->phone }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Posisi
                                        </td>
                                        <td width="60%">
                                            {{ $data->user->position_id ? $data->user->position->name : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Depart.
                                        </td>
                                        <td width="60%">
                                            {{ $data->user->position_id ? $data->user->position->division->name : '-' }}
                                        </td>
                                    </tr>
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
                                            Status
                                        </td>
                                        <td width="60%">
                                            {!! $data->status().''.($data->void_id ? '<div class="mt-2">oleh '.$data->voidUser->name.' tgl. '.date('d/m/Y',strtotime($data->void_date)).' alasan : '.$data->void_note.'</div>' : '') !!}
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
                    <!-- product details table-->
                    
                    <div class="invoice-product-details mt-2">
                        <table class="bordered table-with-breaks table-data-item " border="1" style="border-collapse:collapse;" width="100%"  >
                            <thead>
                                <tr>
                                    <th align="center" colspan="6">Daftar Outgoing Payment (BS)</th>
                                </tr>
                                <tr>
                                    <th align="center">No.</th>
                                    <th align="center">OP No.</th>
                                    <th align="center">Partner Bisnis</th>
                                    <th align="center">Tgl.Bayar</th>
                                    <th align="center">Keterangan</th>
                                    <th align="center">Nominal Terpakai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data->closeBillDetail as $key => $row)
                                <tr>
                                    <td align="center">{{ ($key + 1) }}</td>
                                    <td align="center">{{ $row->outgoingPayment->code }}</td>
                                    <td align="center">{{ $row->outgoingPayment->account->employee_no.' - '.$row->outgoingPayment->account->name }}</td>
                                    <td align="center">{{ date('d/m/Y',strtotime($row->outgoingPayment->pay_date)) }}</td>
                                    <td>{{ $row->note }}</td>
                                    <td align="right">{{ number_format($row->nominal,2,',','.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <table class="bordered table-with-breaks table-data-item " border="1" style="border-collapse:collapse;" width="100%"  >
                            <thead>
                                <tr>
                                    <th align="center" colspan="11">Daftar Biaya</th>
                                </tr>
                                <tr>
                                    <th align="center">Coa</th>
                                    <th align="center">Dist.Biaya</th>
                                    <th align="center">Plant</th>
                                    <th align="center">Line</th>
                                    <th align="center">Mesin</th>
                                    <th align="center">Divisi</th>
                                    <th align="center">Proyek</th>
                                    <th align="center">Debit FC</th>
                                    <th align="center">Kredit FC</th>
                                    <th align="center">Debit Rp</th>
                                    <th align="center">Kredit Rp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data->closeBillCost as $key => $row)
                                <tr>
                                    <td>{{ $row->coa->code.' - '.$row->coa->name }}</td>
                                    <td>{{ ($row->costDistribution()->exists() ? $row->costDistribution->code.' - '.$row->costDistribution->name : '-') }}</td>
                                    <td>{{ ($row->place()->exists() ? $row->place->code : '-') }}</td>
                                    <td>{{ ($row->line()->exists() ? $row->line->code : '-') }}</td>
                                    <td>{{ ($row->machine()->exists() ? $row->machine->name : '-') }}</td>
                                    <td>{{ ($row->division()->exists() ? $row->division->code : '-') }}</td>
                                    <td>{{ ($row->project()->exists() ? $row->project->name : '-') }}</td>
                                    <td align="right">{{ number_format($row->nominal_debit_fc,2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->nominal_credit_fc,2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->nominal_debit,2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->nominal_credit,2,',','.') }}</td>
                                </tr>
                                <tr>
                                    <td colspan="11">Ket 1 : {{ $row->note }}</td>
                                </tr>
                                <tr>
                                    <td colspan="11">Ket 2 : {{ $row->note2 }}</td>
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
                                        {!! ucwords(strtolower($data->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
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
                                <td align="center">
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
                                            <td align="center">
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