@php
    use App\Helpers\CustomHelper;

@endphp
<!doctype html>
<html lang="en">
    <head>
        <style>

           @font-face { font-family: 'china'; font-style: normal; src: url({{ storage_path('fonts/chinese_letter.ttf') }}) format('truetype'); }
            body { font-family: 'china', Tahoma, Arial, sans-serif;}
            .break-row {
                margin-top: 2%;
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
                    font-size:1em !important;
                }
                .table-data-item th{
                    border:1px solid black;
                }
                .table-bot td{
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
            header { position: fixed; top: -64px; left: 0px; right: 0px; height: 150px; margin-bottom: 10em }
                

        
        </style>
    </head>
    <body>
        <header>
            <table border="0" width="100%" style="font-size:1em" class="tb-header">
                <tr>
                    <td width="83%" class="left-align" >
                        <tr>
                            <td>
                                <span class="invoice-number mr-1" style="font-size:1em"># {{ $data->code }}</span>
                            </td>
                        </tr>
                        {{-- <tr>
                            <td style="margin-top: -2px;">
                                <small style="font-size:1em">Diajukan: {{ date('d/m/Y',strtotime($data->post_date)) }}</small>
                            </td>
                        </tr> --}}
                        <tr>
                            <td>
                                <h2 style="margin-top: -2px">{{ $title }}</h2>
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
            <hr style="border-top: 1px solid black; margin-top:-0.5%">
        </header>
        <main>
            <div class="card">
                <div class="card-content invoice-print-area">
                    <table border="0" width="100%" class="tbl-info">
                        <tr>
                            <td width="33%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td>
                                            Name: {{ $data->user->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Posisi: {{ $data->user->position()->exists() ? $data->user->position->name : '-' }}
                                        </td>
                                        
                                    </tr>
                                    <tr>
                                        <td >
                                            Depart. {{ $data->user->position()->exists() ? $data->user->position->division->name : ''}}
                                        </td>
                                        
                                    </tr>
                                </table>
                            </td>
                            <td width="33%" class="left-align">
 
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
                                            <h3>{{ $data->code }}</h3>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                
                    <!-- product details table-->
                    <div class="invoice-product-details">
                    <table class="bordered" border="1" width="100%" class="table-data-item" style="border-collapse:collapse">
                        <thead>
                            <tr>
                                <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.no') }}</th>
                                <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.item') }}</th>
                                <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.qty') }}.</th>
                                <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.stock') }}</th>
                                <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Outstanding</th>
                                <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.unit') }}.</th>
                                <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.used_date') }}</th>
                                <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.plant') }}</th>
                                <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.warehouse') }}</th>
                                <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.line') }}</th>
                                <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.engine') }}</th>
                                <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.division') }}</th>
                                <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.project') }}</th>
                                <th align="center" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.requester') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data->goodIssueRequestDetail as $key => $row)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $row->item->code.' - '.$row->item->name }}</td>
                                <td align="right">{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
                                <td align="right">{{ CustomHelper::formatConditionalQty($row->stock) }}</td>
                                <td align="right">{{ CustomHelper::formatConditionalQty($row->outstanding) }}</td>
                                <td align="center">{{ $row->item->uomUnit->code }}</td>
                                <td align="center">{{ date('d/m/Y',strtotime($row->required_date)) }}</td>
                                <td align="center">{{ $row->place->code }}</td>
                                <td align="center">{{ $row->warehouse->name }}</td>
                                <td align="center">{{ $row->line()->exists() ? $row->line->code : '-' }}</td>
                                <td align="center">{{ $row->machine()->exists() ? $row->machine->name : '-' }}</td>
                                <td align="center">{{ $row->department()->exists() ? $row->department->name : '-' }}</td>
                                <td align="center">{{ $row->project()->exists() ? $row->project->name : '-' }}</td>
                                <td align="">{{ $row->requester }}</td>
                            </tr>
                            <tr>
                                <td colspan="14">Keterangan 1 : {{ $row->note }}</td>
                            </tr>
                            <tr>
                                <td colspan="14">Keterangan 2 : {{ $row->note2 }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- invoice subtotal -->
                <div class="divider mt-3 mb-3"></div>
                    <div class="invoice-subtotal break-row">
                        <table class="table-bot" width="100%" border="0">
                            <tr>
                                <td class="center-align">
                                    {!! ucwords(strtolower($data->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
                                </td>
                            </tr>
                            <tr>
                                <td class="center-align">
                                    Catatan : {{ $data->note }}
                                </td>
                            </tr>
                        </table>
                        <table class="table-bot" width="100%" border="0">
                            <tr>
                                <td class="center-align">
                                    Dibuat oleh,
                                    @if($data->user->signature)
                                        <div>{!! $data->user->signature() !!}</div>
                                    @endif
                                    <div class="{{ $data->user->signature ? '' : 'mt-5' }}">{{ $data->user->name }}</div>
                                    <div class="mt-1">{{ $data->user->position()->exists() ? $data->user->position->Level->code.' - '.$data->user->position->division->name : '-' }}</div>
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