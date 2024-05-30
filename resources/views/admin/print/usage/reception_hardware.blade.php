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
                    font-size:1em !important;
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
                                <span class="invoice-number mr-1"></span>
                            </td>
                        </tr>
                        <tr>
                            <td style="margin-top: -2px;">
                                <small>Diajukan:</small>
                                <span>{{ date('d/m/Y',strtotime($data->date)) }}</span>
                                <br>
                                <h3 class="indigo-text">MEMO INTERNAL</h3>
                            </td>
                        </tr>
                        <tr>
                            <td>
                               
                            </td>
                        </tr>
                                
                        
                    </td>
                    <td width="33%" class="right-align">
                        
                        
                   
                    </td>
                    
                    <td width="34%" class="right-align">                        
                        <img src="{{ $image }}" width="50%" style="position: absolute; top:5px; width:20%" align="right">
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
                                    <td style="font-size: 0.9rem !important;">
                                        Hal	:	Serah Terima Inventaris <br>
                                        No. Pencatatan	:{{$data->code}}                                     
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
                        <td width="33%" class="left-align" style="vertical-align: top !important;">
                            
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
                
                <div class="invoice-product-details mt-2" style="font-size:0.8rem;">
                    Dengan hormat,<br>
                    Saya yang bertanda tangan di bawah ini :<br>
                    <table border="0" width="40%" style="font-size:1.2rem;margin-left:4rem">
                        <tr>
                            <td>
                                Nama Lengkap
                            </td>
                            <td>
                                :
                            </td>
                            <td>
                                {{$data->account->name ?? '-'}}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Departemen	
                            </td>
                            <td>
                                :
                            </td>
                            <td>
                                {{$data->account->position->division->name}}
                            </td>
                        </tr>
                    </table>
                    <br>
                    Menerima inventaris dari pihak PT.SUPERIOR PORCELAIN SUKSES :<br>
                    <table border="0" width="51%" style="font-size:1.2rem;margin-left:4rem">
                        <tr>
                            <td>
                                No Inventaris
                            </td>
                            <td>
                                :
                            </td>
                            <td>
                                {{ $data->hardwareItem->code}}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Jenis	
                            </td>
                            <td>
                                :
                            </td>
                            <td>
                               {{$data->hardwareItem->hardwareItemGroup->name}}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Deskripsi Detail	:	
                            </td>
                            <td>
                                :
                            </td>
                            <td style="font-weight: bold">
                                {{$data->hardwareItem->item}}
                                @if($data->hardwareItem->hardwareItemDetail()->exists())
                                    ||
                                    @foreach($data->hardwareItem->hardwareItemDetail as $key => $row)
                                        {{$row->specification}}
                                    @endforeach
                                @endif
                            </td>
                        </tr>
                    </table>
                    <div style="text-align: justify">
                        <br>        
                        Dan dengan surat pernyataan ini pula, 
                        menyatakan bertanggung jawab atas inventaris yang telah diserah terimakan kepada Saya, dan mempergunakannya hanya untuk mendukung tugas dan kewajiban Saya sebagai seorang staff PT. SUPERIOR PORCELAIN SUKSES.
                        <br>
                        <br>
                        Demikian surat pernyataan ini dibuat dan ditanda tangani dengan penuh tanggung jawab dan kesadaran Saya.
                    </div>
                </div>
                <!-- invoice subtotal -->
                <div class="invoice-subtotal break-row"  style="margin-top: 2rem">
                    <div class="row">
                    <table style="width:100%">
                        <tr class="break-row">
                            <td width:"70%">
                                <br>
                            </td>
                            <td width:"30%" align="right" style="font-size: 0.8rem !important">
                                {!! ucwords(strtolower($user->company->city->name)).', '.CustomHelper::tgl_indo($data->date) !!}
                                
                            </td>
                        </tr>
                    </table>
                    <table class="mt-3" width="100%" border="0" >
                        <tr>
                            <td align="right" style="font-size: 0.8rem !important">
                               
                                <br>
                                Dibuat oleh,
                                @if($data->user->signature)
                                    <div>{!! $data->user->signature() !!}</div>
                                @endif
                                <div class="{{ $data->user->signature ? '' : 'mt-5' }}">{{ $data->user->name }}</div>
                                <div class="mt-1">{{ $data->user->position()->exists() ? $data->user->position->Level->name.' - '.$data->user->position->division->name : '-' }}</div>
                            </td>
                        </tr>
                    </table>  
                </div>
                
                
            </div>
        </main>
    </body>
</html>

