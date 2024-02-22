@php
    use App\Helpers\CustomHelper;
@endphp
<!doctype html>
<html lang="en">
    <head>
        <style>

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
                    font-size:0.7em !important;
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
                <div class="card-content invoice-print-area">
                    <table border="0" width="100%" class="tbl-info">
                        <tr>
                            <td width="40%" class="left-align" style="vertical-align: top;">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="24%">
                                            Name
                                        </td>
                                        <td width="1%">
                                            :
                                        </td>
                                        <td width="75%">
                                            {{ $data->user->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Posisi
                                        </td>
                                        <td width="1%">
                                            :
                                        </td>
                                        <td>
                                            {{ $data->user->position()->exists() ? $data->user->position->name : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Depart.
                                        </td>
                                        <td width="1%">
                                            :
                                        </td>
                                        <td>
                                            {{ $data->user->position()->exists() ? $data->user->position->division->department->name : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="24%" style="vertical-align: top;">
                                            Plant
                                        </td>
                                        <td width="1%" style="vertical-align: top;">
                                            :
                                        </td>
                                        <td width="75%" style="vertical-align: top;">
                                            {{ $data->productionSchedule->place->code }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="24%">
                                            Item
                                        </td>
                                        <td width="1%">
                                            :
                                        </td>
                                        <td width="75%">
                                            {{ $data->productionScheduleDetail->item->code.' - '.$data->productionScheduleDetail->item->name }}
                                        </td>
                                    </tr>
                                    
                                </table>
                            </td>
                            <td width="40%" class="left-align" style="vertical-align: top;">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="34%">
                                            Qty Planned
                                        </td>
                                        <td width="1%">
                                            :
                                        </td>
                                        <td width="65%">
                                            {{ number_format($data->productionScheduleDetail->qty,3,',','.').' '.$data->productionScheduleDetail->item->productionUnit->code }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Gudang & Area
                                        </td>
                                        <td width="1%">
                                            :
                                        </td>
                                        <td>
                                            {{ $data->warehouse->name.' - '.($data->area()->exists() ? $data->area->name : '-') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Shift
                                        </td>
                                        <td width="1%">
                                            :
                                        </td>
                                        <td>
                                            {{ $data->productionScheduleDetail->shift->code.' - '.$data->productionScheduleDetail->shift->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Tgl.Produksi
                                        </td>
                                        <td width="1%">
                                            :
                                        </td>
                                        <td>
                                            {{ date('d/m/Y',strtotime($data->productionScheduleDetail->production_date)) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: top;">
                                            Grup
                                        </td>
                                        <td style="vertical-align: top;">
                                            :
                                        </td>
                                        <td style="vertical-align: top;">
                                            {{ $data->productionScheduleDetail->group }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: top;">
                                            Line
                                        </td>
                                        <td style="vertical-align: top;">
                                            :
                                        </td>
                                        <td style="vertical-align: top;">
                                            {{ $data->productionScheduleDetail->line->code }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="20%" class="left-align" style="vertical-align: top;">
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
                </div>
                    <!-- product details table-->
                <div class="invoice-product-details">
                    <table class="bordered" border="1" width="100%" class="table-data-item" style="border-collapse:collapse">
                        <thead>
                            <tr>
                                <th colspan="6" class="center-align">Daftar Komposisi dari BOM Terbaru</th>
                            </tr>
                            <tr>
                                <th align="center">No.</th>
                                <th align="center">Bahan/Biaya</th>
                                <th align="center">Qty</th>
                                <th align="center">Satuan (Produksi)</th>
                                <th align="center">Nominal</th>
                                <th align="center">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data->productionOrderDetail as $key => $row)
                            <tr>
                                <td align="center">{{ ($key + 1) }}</td>
                                <td align="">{{ $row->item()->exists() ? $row->item->code.' - '.$row->item->name : ($row->coa()->exists() ? $row->coa->code.' - '.$row->coa->name : '') }}</td>
                                <td align="right">{{ $row->item()->exists() ? CustomHelper::formatConditionalQty($row->qty) : '-' }}</td>
                                <td align="center">{{ $row->item()->exists() ? $row->item->productionUnit->code : '-' }}</td>
                                <td align="right">{{ $row->coa()->exists() ? number_format($row->nominal,2,',','.') : '-' }}</td>
                                <td align="right">{{ $row->coa()->exists() ? number_format($row->total,2,',','.') : '-' }}</td>
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
                                    {!! ucwords(strtolower($data->user->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
                                </td>
                            </tr>
                            <tr>
                                <td class="center-align">
                                    
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
                                    <div class="mt-1">{!! $data->user->position()->exists() ? $data->user->position->name : '-' !!}</div>
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