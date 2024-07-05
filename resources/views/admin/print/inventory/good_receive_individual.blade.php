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
                    font-size:10px !important;
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
                    <td width="83%" class="left-align" >
                        <tr>
                            <td>
                                <span class="invoice-number mr-1">Barang Masuk # {{ $data->code }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="margin-top: -2px;">
                                <small>Diajukan:</small>
                                <span>{{ date('d/m/Y',strtotime($data->post_date)) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h2 class="indigo-text">Barang Masuk</h2>
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
            <hr style="border-top: 3px solid black; margin-top:-20px">
        </header>
        <main>
            <div class="card">
                <div class="card-content invoice-print-area ">
                    <table border="0" width="100%">
                        <tr>
                            <td width="33%" class="left-align">
                                <table border="0" width="50%" class="tbl-info">
                                    <tr>
                                        <td width="25%">
                                            Name
                                        </td>
                                        <td width="50%">
                                            {{ $data->user->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="50%">
                                            Posisi
                                        </td>
                                        <td width="50%">
                                            {{ $data->user->position_id ? $data->user->position->Level->name : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="50%">
                                            Depart.
                                        </td>
                                        <td width="50%">
                                            {{ $data->user->position_id ? $data->user->position->division->name : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="50%">
                                            HP
                                        </td>
                                        <td width="50%">
                                            {{ $data->user->phone }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="33%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        
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
                                    <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.item') }}</th>
                                    <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.destination') }}</th>
                                    <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.qty') }}.</th>
                                    <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.unit') }}.</th>
                                    <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.price') }} @</th>
                                    <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.total_price') }}.</th>
                                    <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.receive_type') }}</th>
                                    <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Coa</th>
                                    <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.cost_distribution') }}</th>
                                    <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.plant') }}</th>
                                    <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.line') }}</th>
                                    <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.engine') }}</th>
                                    <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.department') }}</th>
                                    <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.area') }}</th>
                                    <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.shading') }}</th>
                                    <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.project') }}</th>
                                    <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.note') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data->goodReceiveDetail as $row)
                                <tr>
                                    <td>{{ $row->item->code.' - '.$row->item->name }}</td>
                                    <td class="center-align">{{ $row->place->code.' - '.$row->warehouse->name }}</td>
                                    <td class="center-align">{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
                                    <td class="center-align">{{ $row->item->uomUnit->code }}</td>
                                    <td class="right-align">{{ number_format($row->price,2,',','.') }}</td>
                                    <td class="right-align">{{ number_format($row->total,2,',','.') }}</td>
                                    <td class="center-align">{{ $row->inventoryCoa()->exists() ? $row->inventoryCoa->code.' - '.$row->inventoryCoa->name : '-' }}</td>
                                    <td class="center-align">{{ $row->coa()->exists() ? $row->coa->code.' - '.$row->coa->name : '-' }}</td>
                                    <td class="center-align">{{ $row->costDistribution()->exists() ? $row->costDistribution->name : '-' }}</td>
                                    <td class="center-align">{{ $row->getPlace() }}</td>
                                    <td class="center-align">{{ $row->getLine() }}</td>
                                    <td class="center-align">{{ $row->getMachine() }}</td>
                                    <td class="center-align">{{ $row->getDepartment() }}</td>
                                    <td class="center-align">{{ $row->area()->exists() ? $row->area->name : '-' }}</td>
                                    <td class="center-align">{{ $row->itemShading()->exists() ? $row->itemShading->code : '-' }}</td>
                                    <td class="center-align">{{ $row->project()->exists() ? $row->project->name : '-' }}</td>
                                    <td>{{ $row->note }}</td>
                                </tr>
                                <tr>
                                    <td colspan="17">No. Serial : {{ $row->listSerial() }}</td>
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