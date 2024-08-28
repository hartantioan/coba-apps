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

            

            @media only screen and (max-width : 768px) {
                .invoice-print-area {
                    zoom:0.6;
                }
            }
        
            @media only screen and (max-width : 992px) {
                .invoice-print-area {
                    zoom:0.8;
                    font-size:10px !important;
                }

                
                td{
                    font-size:0.9em !important;
                }
                .tb-header td{
                    font-size:1.2em !important;
                }
                .tbl-info td{
                    font-size:10.5px !important;
                }
                .table-data-item td{
                    font-size:1.001em !important;
                }
                .table-data-item th{
                    border:0.6px solid black;
                }
                .table-bot td{
                    font-size:0.9em !important;
                }
                .table-bot1 td{
                    font-size:0.9em !important;
                }
            }
        
            @media print {
                .invoice-print-area {
                    font-size:200px !important;
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

            @page { margin: 6em 3em 3em 3em; }
            header { position: fixed; top: -95px; left: 0px; right: 0px; height: 150px; margin-bottom: 10em }
                
            td {
                vertical-align: top !important;
            }
           
            .mt-5 {
                margin-top:30px;
            }
            .mt-2 {
                margin-top:10px;
            }
        </style>
    </head>
    <body>
        <header style="margin-top:35px;">
            <table border="0" width="100%" style="font-size:0.8em" class="tb-header">
                <tr>
                    <td width="66%" class="left-align" style="padding-top:15px;">
                        <span class="invoice-number mr-1">Tiket Timbangan # {{ $data->code }}</span>
                        <br><span>Diajukan:{{ date('d/m/Y',strtotime($data->post_date)) }}</span>
                        <h4 class="indigo-text"></h4>
                    </td>
                    <td width="34%" class="right-align">
                        <img src="{{ $image }}" width="50%" style="position: absolute; top:15px; width:20%;right:0;">
                    </td>
                </tr>
                
            </table>
            <hr style="border-top: 1px solid black; margin-top:-35px">
        </header>
        <main style="margin-top:15px;">
            <div class="card">
                <div class="card-content invoice-print-area ">
                    <table border="0" width="100%">
                        <tr>
                            <td width="40%" class="left-align">
                                <table border="0" width="100%" class="tbl-info">
                                    <tr>
                                        <td width="25%">
                                            Name
                                        </td>
                                        <td width="50%">
                                            {{ $data->user->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                           Plant
                                        </td>
                                        <td>
                                            {{ $data->place->name }}
                                         </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Area
                                         </td>
                                         <td>
                                             {{ $data->area->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Line
                                         </td>
                                         <td>
                                             {{ $data->line->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Mesin
                                         </td>
                                         <td>
                                             {{ $data->machine->name }}
                                          </td>
                                    </tr>
                                    
                                   
                                </table>
                            </td>
                            <td width="40%" class="left-align">
                                <table border="0" width="100%" class="tbl-info">
                                    <tr>
                                        <td width="40%">
                                            Group
                                        </td>
                                        <td width="60%">
                                            {{$data->group}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Shift
                                         </td>
                                         <td>
                                            {{ $data->shift->name }}
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <td>
                                            Catatan
                                         </td>
                                         <td>
                                            {{ $data->note }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <!-- product details table-->
                    <div class="invoice-subtotal break-row">
                            Daftar Waktu
                        <table class="bordered table-with-breaks table-data-item " border="1" style="border-collapse:collapse;font-size:9px !important;" width="100%">
                            <thead>
                                <tr>
                                    <th class="center">No</th>
                                    <th class="center">Proses</th>
                                    <th class="center">Keterangan</th>
                                    <th class="center">Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($data->productionWorkingHourDetail()->exists())
                                    @foreach($data->productionWorkingHourDetail as $keydetail => $rowdetail)
                                    <tr>
                                        <td align="center">{{ ($keydetail + 1) }}</td>
                                        <td>{{ $rowdetail->type() }}</td>
                                        <td align="right">{{ $rowdetail->note }}</td>
                                        <td align="center">{{ $rowdetail->working_hour }}</td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4">DATA TIDAK DITEMUKAN</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <!-- invoice subtotal -->
                    <div class="invoice-subtotal break-row">
                        <div class="row">
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
                                    <div class="{{ $data->user->signature ? 'mt-2' : 'mt-5' }}">{{ $data->user->name }}</div>
                                </td>
                                @if($data->approval())
                                    @foreach ($data->approval() as $detail)
                                        @foreach ($detail->approvalMatrix()->where('status','2')->get() as $row)
                                            <td class="center-align">
                                                {{ $row->approvalTemplateStage->approvalStage->approval->document_text }}
                                                @if($row->user->signature)
                                                    <div>{!! $row->user->signature() !!}</div>
                                                @endif
                                                <div class="{{ $row->user->signature ? 'mt-2' : 'mt-5' }}">{{ $row->user->name }}</div>
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