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
                                <span class="invoice-number mr-1" style="font-size:1em">{{ $title }} # {{ $data->code }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="margin-top: -2px;">
                                <small style="font-size:1em">Diajukan: {{ date('d/m/y',strtotime($data->post_date)) }}</small>
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
                            <td width="40%" class="left-align">
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
                                        <td>
                                            Plant & Gudang
                                        </td>
                                        <td>
                                            :
                                        </td>
                                        <td>
                                            {{ $data->productionOrder->productionSchedule->place->code.' - '.$data->productionOrder->warehouse->name }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="40%" class="left-align">
                                <table border="0" width="100%">
                                    
                                    <tr>
                                        <td width="34%">
                                            Shift
                                        </td>
                                        <td width="1%">
                                            :
                                        </td>
                                        <td width="65%">
                                            {{ date('d/m/y',strtotime($data->productionOrder->productionScheduleDetail->production_date)).' - '.$data->productionOrder->productionScheduleDetail->shift->code.' - '.$data->productionOrder->productionScheduleDetail->shift->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Line
                                        </td>
                                        <td width="1%">
                                            :
                                        </td>
                                        <td>
                                            {{ $data->productionOrder->productionScheduleDetail->line->code }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Grup
                                        </td>
                                        <td width="1%">
                                            :
                                        </td>
                                        <td>
                                            {{ $data->productionOrder->productionScheduleDetail->group }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="20%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td>
                                            <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($data->code, 'C128')}}" alt="barcode" style="width:100%;" height="5%" />
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
                </div>
                    <!-- product details table-->
                <div class="invoice-product-details">
                    <table class="bordered" border="1" width="100%" class="table-data-item" style="border-collapse:collapse">
                        <thead>
                            <tr>
                                <th align="center" colspan="6" style="font-size:16px !important;">Daftar Item/Coa Issue (Terpakai)</th>
                            </tr>
                            <tr>
                                <th align="center">No.</th>
                                <th align="center">Item/Coa</th>
                                <th align="center">Qty Planned</th>
                                <th align="center">Qty Real</th>
                                <th align="center">Satuan Produksi</th>
                                <th align="center">Plant & Gudang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data->productionIssueReceiveDetail()->where('type','1')->get() as $key => $row)
                                <tr>
                                    <td align="center">{{ $key+1 }}.</td>
                                    <td>{{ $row->item()->exists() ? $row->item->code.' - '.$row->item->name : $row->coa->code.' - '.$row->coa->name }}</td>
                                    <td align="right">{{ $row->item()->exists() ? number_format($row->productionOrderDetail->qty,3,',','.') : '-' }}</td>
                                    <td align="right">{{ $row->item()->exists() ? number_format($row->qty,3,',','.') : '-' }}</td>
                                    <td align="center">{{ $row->item()->exists() ? $row->item->productionUnit->code : '-' }}</td>
                                    <td>{{ $row->item()->exists() ? $row->itemStock->fullName() : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="invoice-product-details">
                    <table class="bordered" border="1" width="100%" class="table-data-item" style="border-collapse:collapse">
                        <thead>
                            <tr>
                                <th align="center" colspan="9" style="font-size:16px !important;">Daftar Item Receive (Diterima)</th>
                            </tr>
                            <tr>
                                <th align="center" width="5%">No.</th>
                                <th align="center" width="15%">Item/Coa</th>
                                <th align="center" width="10%">Qty Planned (Prod.)</th>
                                <th align="center" width="10%">Qty Real (Prod.)</th>
                                <th align="center" width="10%">Qty UoM</th>
                                <th align="center" width="10%">Qty Jual</th>
                                <th align="center" width="10%">Qty Pallet</th>
                                <th align="center" width="15%">Shading</th>
                                <th align="center" width="15%">Batch</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data->productionIssueReceiveDetail()->where('type','2')->get() as $key => $row)
                                <tr>
                                    <td align="center">{{ $key+1 }}</td>
                                    <td>{{ $row->item->code.' - '.$row->item->name }}</td>
                                    <td align="right">{{ number_format($data->productionOrder->productionScheduleDetail->qty,3,',','.').' '.$row->item->productionUnit->code }}</td>
                                    <td align="right">{{ number_format($row->qty,3,',','.').' '.$row->item->productionUnit->code }}</td>
                                    <td align="right">{{ number_format($row->qty * $row->item->production_convert,3,',','.').' '.$row->item->uomUnit->code }}</td>
                                    <td align="right">{{ number_format(($row->qty * $row->item->production_convert) / $row->item->sell_convert,3,',','.').' '.$row->item->sellUnit->code }}</td>
                                    <td align="right">{{ number_format((($row->qty * $row->item->production_convert) / $row->item->sell_convert) / $row->item->pallet_convert,3,',','.').' '.$row->item->palletUnit->code }}</td>
                                    <td align="center">{{ $row->shading }}</td>
                                    <td align="center">{{ $row->batch_no }}</td>
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