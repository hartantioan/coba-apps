@php
    use App\Helpers\CustomHelper;

@endphp
<!doctype html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
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
                                <span class="invoice-number mr-1">Permohonan Dana # {{ $data->code }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h2 class="indigo-text">Permohonan Dana</h2>
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
                                        <td width="40%">
                                            Nama
                                        </td>
                                        <td width="60%">
                                            : {{ $data->user->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            NIK
                                        </td>
                                        <td width="60%">
                                            : {{ $data->user->employee_no }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Posisi
                                        </td>
                                        <td width="60%">
                                            : {{ $data->user->position_id ? $data->user->position->name : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Divisi
                                        </td>
                                        <td width="60%">
                                            : {{ $data->division()->exists() ? $data->division->name : '' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Tgl.Pengajuan
                                        </td>
                                        <td width="60%">
                                            : {{ date('d/m/Y',strtotime($data->post_date)) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Req.Pembayaran
                                        </td>
                                        <td width="60%">
                                            : {{ date('d/m/Y',strtotime($data->required_date)) }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="33%" class="left-align" style="vertical-align:top;">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="40%">
                                            Partner Bisnis
                                        </td>
                                        <td width="60%">
                                            : {{ $data->account->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Rek. Penerima
                                        </td>
                                        <td width="60%">
                                            : {{ $data->name_account }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            No. Rekening
                                        </td>
                                        <td width="60%">
                                            : {{ $data->no_account }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Bank Tujuan
                                        </td>
                                        <td width="60%">
                                            : {{ $data->bank_account }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Tipe
                                        </td>
                                        <td width="60%">
                                            : {{ $data->type() }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%">
                                            Status Dokumen
                                        </td>
                                        <td width="60%">
                                            : {{ $data->documentStatus() }}
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
                                    <th class="center">{{ __('translations.item') }}</th>
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
                                @php
                                    $uniquePphArray = [];
                                @endphp
                                @foreach($data->fundRequestDetail as $row)
                                <tr>
                                    @php
                                        if (!in_array( CustomHelper::formatConditionalQty($row->percent_wtax).'%', $uniquePphArray)) {
                                            $uniquePphArray[] = CustomHelper::formatConditionalQty($row->percent_wtax).'%';
                                        }
                                    @endphp
                                    <td>{{ $row->note }}</td>
                                    <td class="center-align">{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
                                    <td class="center-align">{{ $row->unit->code }}</td>
                                    <td class="right-align">{{ $data->currency->code.'. '.number_format($row->price,2,',','.') }}</td>
                                    <td class="right-align">{{ $data->currency->code.'. '.number_format($row->total,2,',','.') }}</td>
                                    <td class="right-align">{{ number_format($row->tax,2,',','.') }}</td>
                                    <td class="right-align">{{ number_format($row->wtax,2,',','.') }}</td>
                                    <td class="right-align">{{ $data->currency->code.'. '.number_format($row->grandtotal,2,',','.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            
                        </table>
                    </div>
                    @php
                        $uniquePphString = implode(', ', $uniquePphArray);
                    @endphp
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
                                        <td class="right-align"></td>
                                        <td class="right-align" width="25%">Total</td>
                                        <td class="right-align" width="50%" style="border:0.6px solid black;">{{ $data->currency->code.'. '.number_format($data->total,2,',','.') }}</td>
                                    </tr>
                                    <tr class="break-row">
                                        <td class="right-align"></td>
                                        <td class="right-align">PPN</td>
                                        <td class="right-align" style="border:0.6px solid black;">{{ $data->currency->code.'. '.number_format($data->tax,2,',','.') }}</td>
                                    </tr class="break-row">
                                    
                                    <tr class="break-row">
                                        <td class="right-align">PPh(%): {{ $uniquePphString }}</td>
                                        <td class="right-align">PPh</td>
                                        <td class="right-align" style="border:0.6px solid black;">{{ $data->currency->code.'. '.number_format($data->wtax,2,',','.') }}</td>
                                    </tr class="break-row">
                                    <tr>
                                        <td class="right-align"></td>
                                        <td class="right-align">Grandtotal</td>
                                        <td class="right-align" style="border:0.6px solid black;">{{ $data->currency->code.'. '.number_format($data->grandtotal,2,',','.') }}</td>
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
            @if($data->type == '1' && $data->document_status == '3' && $data->account->type == '1')
                <div class="part2" style="margin-top:1%">
                    <table border="0" width="100%" style="font-size:1em" class="tb-header">
                        <tr>
                            <td width="83%" class="left-align" >
                                <img src="{{ $image }}" width="50%" style=" width:35%">
                            </td>
                            <td width="33%" class="right-align">
                            </td>
                            <td width="34%" class="right-align">
                                SERAH TERIMA
                                <br>CEK / TUNAI
                            </td>
                        </tr>
                        
                    </table>
                    <hr style="border-top: 3px solid black; margin-top:20px">
                
                    <div class="card">
                        <div class="card-content invoice-print-area ">
                            <table border="0" width="100%">
                                <tr>
                                    <td class="left-align">
                                        Pada hari ini, <b>{{ CustomHelper::hariIndo(date('l',strtotime($data->post_date))) }}</b> Tanggal <b>{{ date('d/m/Y',strtotime($data->post_date)) }}</b>, telah diterima dari <b>{{ $data->company->name }}</b>.
                                    </td>
                                </tr>
                                <tr>
                                    <td class="left-align" style="padding-left: 30px;">
                                        <table border="0" width="100%" class="tbl-info">
                                            <tr>
                                                <td width="25%">
                                                    Nama
                                                </td>
                                                <td width="75%">
                                                    : {{ strtoupper($data->account->name) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    Jabatan
                                                </td>
                                                <td>
                                                    : {{ $data->account->position()->exists() ? strtoupper($data->account->position->name) : '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    Uang Tunai / Cek Bank
                                                </td>
                                                <td>
                                                    : {{ strtoupper($data->listCekBG()) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    Sebesar
                                                </td>
                                                <td>
                                                    : {{ strtoupper($data->currency->code.' '.number_format($data->grandtotal,2,',','.')) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    Terbilang
                                                </td>
                                                <td>
                                                    : {{ strtoupper(CustomHelper::terbilangWithKoma($data->grandtotal).' '.ucwords(strtolower($data->currency->document_text))) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    Keperluan
                                                </td>
                                                <td>
                                                    : {{ strtoupper($data->note) }}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            <div class="invoice-subtotal break-row">
                                <table class="table-bot1" width="100%" border="0" style="margin-top:50px;">
                                    <tr>
                                        <td class="center-align" width="50%">
                                            Dibuat oleh,
                                            <br><br><br><br>
                                            (......................................)
                                        </td>
                                        <td class="center-align">
                                            Diterima oleh,
                                            <br><br><br><br>
                                            (......................................)
                                        </td>
                                    </tr>
                                </table>
                                <p style="font-size:13px !important;">
                                    Note:
                                    <br>&nbsp;&nbsp;&nbsp;Dana yang diterima hanya untuk dipergunakan sesuai keperluan tercantum
                                    <br>&nbsp;&nbsp;&nbsp;Pelaku penggelapan dalam jabatan diancam pidana penjara maksimal 5 (lima) tahun sesuai Pasal 374 KUHP
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </main>
    </body>
</html>