@php
    use App\Helpers\CustomHelper;
    use App\Helpers\PrintHelper;
    use Carbon\Carbon;
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
                    
                    font-size:11px !important;
                    font-weight: 800 !important;
                }
                td{
                    font-size:0.5em !important;
            
                }
                .tb-header td{
                    font-size:0.6em !important;
                }
                .tbl-info td{
                    font-size:1em !important;
                }
                .table-data-item td{
                    font-size:0.5em !important;
                }
                .table-data-item th{
                    border:1px solid black;
                }
                .table-bot td{
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
                    vertical:top !important;
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
                    <td width="83%" class="left" >
                        <tr>
                            <td>
                                <span class="invoice-number mr-1" style="font-size:1em"># {{ $data->code }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="margin-top: -2px;">
                                <small style="font-size:1em">Diajukan: {{ date('d/m/Y',strtotime($data->post_date)) }}</small>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h2 style="margin-top: -2px">{{ $title }}</h2>
                            </td>
                        </tr>
                                
                        
                    </td>
                    <td width="33%" class="right">
                        
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
                <div class="card-content invoice-print-area">
                    <table border="0" width="100%" class="tbl-info">
                        <tr>
                            <td width="40%" class="left" style="vertical: top;">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="34%">
                                            Name
                                        </td>
                                        <td width="1%">
                                            :
                                        </td>
                                        <td width="65%">
                                            {{ $data->user->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Production Order
                                        </td>
                                        <td>
                                            :
                                        </td>
                                        <td>
                                            {{ $data->productionFgReceive->productionOrderDetail->productionOrder->code }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Production Receive FG
                                        </td>
                                        <td>
                                            :
                                        </td>
                                        <td>
                                            {{ $data->productionFgReceive->code }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="40%" class="left" style="vertical: top;">
                                <table border="0" width="100%">
                                    <tr>
                                        <td>
                                            Plant
                                        </td>
                                        <td>
                                            :
                                        </td>
                                        <td>
                                            {{ $data->productionFgReceive->place->code }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Line
                                        </td>
                                        <td>
                                            :
                                        </td>
                                        <td>
                                            {{ $data->productionFgReceive->line->code }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Keterangan
                                        </td>
                                        <td width="1%">
                                            :
                                        </td>
                                        <td>
                                            {{ $data->note }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="20%" class="left">
                                <table border="0" width="100%">
                                    <tr>
                                        <td align="center">
                                            <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($data->code, 'C128')}}" alt="barcode" style="width:100%;" height="30px" />
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
                </div>
                    <!-- product details table-->
                <div class="invoice-product-details">
                    <table class="bordered" border="1" width="100%" class="table-data-item" style="border-collapse:collapse">
                        <thead>
                            <tr>
                                <th align="center" colspan="12" style="font-size:16px !important;">Daftar Item Diterima</th>
                            </tr>
                            <tr>
                                <th align="center">No.</th>
                                <th align="center">No.Batch Palet/Curah</th>
                                <th align="center">Item</th>
                                <th align="center">{{ __('translations.shading') }}</th>
                                <th align="center">Qty Input</th>
                                <th align="center">Qty Reject</th>
                                <th align="center">Qty Diterima</th>
                                <th align="center">Satuan</th>
                                <th align="center">Konversi</th>
                                <th align="center">{{ __('translations.plant') }}</th>
                                <th align="center">{{ __('translations.warehouse') }}</th>
                                <th align="center">{{ __('translations.area') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data->productionHandoverDetail()->orderBy('id')->get() as $key => $row)
                                <tr>
                                    <td align="center">{{ ($key+1) }}</td>
                                    <td>{{ $row->productionFgReceiveDetail->pallet_no }}</td>
                                    <td>{{ $row->item->code.' - '.$row->item->name }}</td>
                                    <td>{{ $row->shading }}</td>
                                    <td align="right">{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
                                    <td align="right">{{ CustomHelper::formatConditionalQty($row->qty_reject) }}</td>
                                    <td align="right">{{ CustomHelper::formatConditionalQty($row->qty_received) }}</td>
                                    <td align="center">{{ $row->productionFgReceiveDetail->itemUnit->unit->code }}</td>
                                    <td align="right">{{ CustomHelper::formatConditionalQty($row->productionFgReceiveDetail->conversion) }}</td>
                                    <td align="">{{ $row->place->code }}</td>
                                    <td align="">{{ $row->warehouse->name }}</td>
                                    <td align="">{{ $row->area->code }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <table class="bordered" border="1" width="350px" class="table-data-item" style="border-collapse:collapse;margin-top:25px;">
                        <thead>
                            <tr>
                                <th colspan="6" align="center">Daftar Batch Terpakai</th>
                            </tr>
                            <tr>
                                <th align="center">{{ __('translations.no') }}.</th>
                                <th align="center">No.Batch</th>
                                <th align="center">Item Parent</th>
                                <th align="center">Item Child</th>
                                <th align="center">Qty</th>
                                <th align="center">Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data->productionHandoverDetail as $key => $row)
                                <tr>
                                    <td align="center">{{ ($key+1) }}</td>
                                    <td>{{ $row->productionBatchUsage->productionBatch->code }}</td>
                                    <td>{{ $data->productionFgReceive->productionOrderDetail->productionScheduleDetail->item->code.' - '.$data->productionFgReceive->productionOrderDetail->productionScheduleDetail->item->name }}</td>
                                    <td>{{ $row->productionBatchUsage->productionBatch->item->code.' - '.$row->productionBatchUsage->productionBatch->item->name }}</td>
                                    <td align="right">{{ CustomHelper::formatConditionalQty($row->productionBatchUsage->qty) }}</td>
                                    <td align="center">{{ $row->productionBatchUsage->productionBatch->item->uomUnit->code }}</td>
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
                                <td class="center">
                                    {!! ucwords(strtolower($data->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
                                </td>
                            </tr>
                            <tr>
                                <td class="center">
                                    
                                </td>
                            </tr>
                        </table>
                        <table class="table-bot" width="100%" border="0">
                            <tr>
                                <td class="center">
                                    Dibuat oleh,
                                    @if($data->user->signature)
                                        <div>{!! $data->user->signature() !!}</div>
                                    @endif
                                    <div class="{{ $data->user->signature ? '' : 'mt-5' }}">{{ $data->user->name }}</div>
                                    <div class="mt-1">{!! $data->user->position()->exists() ? $data->user->position->name : '-' !!}</div>
                                </td>
                                @if($data->approval())
                                    @foreach ($data->approval() as $detail)
                                        @foreach ($detail->approvalMatrix()->where('status','2')->get() as $row)
                                            <td class="center">
                                                {{ $row->approvalTemplateStage->approvalStage->approval->document_text }}
                                                @if($row->user->signature)
                                                    <div>{!! $row->user->signature() !!}</div>
                                                @endif
                                                <div class="{{ $row->user->signature ? '' : 'mt-5' }}">{{ $row->user->name }}</div>
                                                <div class="mt-1">{!! $row->user->position()->exists() ? $row->user->position->name : '-' !!}</div>
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