@php
    use App\Helpers\CustomHelper;
@endphp
<link rel="stylesheet" type="text/css" href="{{ url('app-assets/css/pages/app-invoice.css') }}">
<style>
    @media only screen and (max-width : 320px) {
        .invoice-print-area {
            zoom:0.2;
        }
    }

    @media only screen and (max-width : 480px) {
        .invoice-print-area {
            zoom:0.3;
        }
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

    @page {
        margin: 10mm;
    }

    table {
        border-collapse:unset;
    }

    td {
        padding: 3px 1px;
    }
    
    table.bordered th, table.bordered td {
        padding:3px !important;
    }
</style>
<div class="card">
    <div class="card-content invoice-print-area">
        <!-- header section -->
        <div class="row invoice-date-number">
            <div class="col xl4 s5">
                <span class="invoice-number mr-1">{{ $title }} # {{ $data->code }}</span>
            </div>
            <div class="col xl8 s7">
                <div class="invoice-date display-flex align-items-right flex-wrap" style="right:0px !important;">
                    <div class="mr-2">
                        <small>Diajukan:</small>
                        <span>{{ date('d/m/Y',strtotime($data->post_date)) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- logo and title -->
        <div class="row mt-1 invoice-logo-title">
            <div class="col m6 s12">
                <h5 class="indigo-text">{{ $title }}</h5>
            </div>
            <div class="col m6 s12 right-align">
                <img src="{{ url('website/logo_web_fix.png') }}" width="35%">
            </div>
        </div>
        <div class="divider mb-1 mt-1"></div>
        <!-- invoice address and contact -->
        <div class="row">
            <div class="col s4 row mt-2">
                <div class="col s12 center-align">
                    INFO UTAMA
                </div>
                <div class="col s4">
                    Nama
                </div>
                <div class="col s8">
                    {{ $data->user->name }}
                </div>
                <div class="col s4">
                    Plant
                </div>
                <div class="col s8">
                    {{ $data->productionOrder->productionSchedule->place->code.' - '.$data->productionOrder->warehouse->name.($data->productionOrder->area()->exists() ? ' - '.$data->productionOrder->area->name : '') }}
                </div>
                <div class="col s4">
                    No.PROD
                </div>
                <div class="col s8">
                    {{ $data->productionOrder->code }}
                </div>
                <div class="col s4">
                    No.Jadwal
                </div>
                <div class="col s8">
                    {{ $data->productionOrder->productionSchedule->code }}
                </div>
            </div>
            <div class="col s4 row mt-2">
                <div class="col s12 center-align">
                    JADWAL
                </div>
                <div class="col s4">
                    Tgl.
                </div>
                <div class="col s8">
                    {{ date('d/m/Y',strtotime($data->productionOrder->productionScheduleDetail->production_date)) }}
                </div>
                <div class="col s4">
                    Shift
                </div>
                <div class="col s8">
                    {{ $data->productionOrder->productionScheduleDetail->shift->code.' - '.$data->productionOrder->productionScheduleDetail->shift->name }}
                </div>
                <div class="col s4">
                    Line
                </div>
                <div class="col s8">
                    {{ $data->productionOrder->productionScheduleDetail->line->code }}
                </div>
                <div class="col s4">
                    Grup
                </div>
                <div class="col s8">
                    {{ $data->productionOrder->productionScheduleDetail->group }}
                </div>
            </div>
            <div class="col s4 row mt-2">
                <div class="col s12 center-align">
                    LAIN-LAIN
                </div>
                <div class="col s4">
                    Tgl.Post
                </div>
                <div class="col s8">
                    {{ date('d/m/Y',strtotime($data->post_date)) }}
                </div>
            </div>
        </div>
        
        <div class="invoice-product-details mt-2" style="overflow:auto;">
            <table class="bordered">
                <thead>
                    <tr>
                        <th colspan="6" class="center-align">Daftar Coa/Item Issue (Terpakai)</th>
                    </tr>
                    <tr>
                        <th class="center">No.</th>
                        <th class="center">Item/Coa</th>
                        <th class="center">Qty Planned</th>
                        <th class="center">Qty Real</th>
                        <th class="center">Satuan Produksi</th>
                        <th class="center">Plant & Gudang</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data->productionIssueReceiveDetail()->where('type','1')->get() as $key => $row)
                        <tr>
                            <td class="center-align">{{ $key+1 }}.</td>
                            <td>{{ $row->item()->exists() ? $row->item->code.' - '.$row->item->name : $row->coa->code.' - '.$row->coa->name }}</td>
                            <td class="right-align">{{ $row->item()->exists() ? CustomHelper::formatConditionalQty($row->productionOrderDetail->qty) : '-' }}</td>
                            <td class="right-align">{{ $row->item()->exists() ? CustomHelper::formatConditionalQty($row->qty) : '-' }}</td>
                            <td class="center-align">{{ $row->item()->exists() ? $row->item->productionUnit->code : '-' }}</td>
                            <td>{{ $row->item()->exists() ? $row->itemStock->fullName() : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="invoice-product-details mt-2" style="overflow:auto;">
            <table class="bordered">
                <thead>
                    <tr>
                        <th colspan="9" class="center-align">Daftar Item Receive (Diterima)</th>
                    </tr>
                    <tr>
                        <th class="center-align" width="5%">No.</th>
                        <th class="center-align" width="15%">Item/Coa</th>
                        <th class="center-align" width="10%">Qty Planned (Prod.)</th>
                        <th class="center-align" width="10%">Qty Real (Prod.)</th>
                        <th class="center-align" width="10%">Qty UoM</th>
                        <th class="center-align" width="10%">Qty Jual</th>
                        <th class="center-align" width="10%">Qty Pallet</th>
                        <th class="center-align" width="15%">Shading</th>
                        <th class="center-align" width="15%">Batch</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data->productionIssueReceiveDetail()->where('type','2')->get() as $key => $row)
                        <tr>
                            <td class="center-align">{{ $key+1 }}</td>
                            <td>{{ $row->item->code.' - '.$row->item->name }}</td>
                            <td class="right-align">{{ CustomHelper::formatConditionalQty($data->productionOrder->productionScheduleDetail->qty).' '.$row->item->productionUnit->code }}</td>
                            <td class="right-align">{{ CustomHelper::formatConditionalQty($row->qty).' '.$row->item->productionUnit->code }}</td>
                            <td class="right-align">{{ CustomHelper::formatConditionalQty($row->qty * $row->item->production_convert).' '.$row->item->uomUnit->code }}</td>
                            <td class="right-align">{{ CustomHelper::formatConditionalQty(($row->qty * $row->item->production_convert) / $row->item->sell_convert).' '.$row->item->sellUnit->code }}</td>
                            <td class="right-align">{{ CustomHelper::formatConditionalQty((($row->qty * $row->item->production_convert) / $row->item->sell_convert) / $row->item->pallet_convert).' '.$row->item->palletUnit->code }}</td>
                            <td class="center-align">{{ $row->shading }}</td>
                            <td class="center-align">{{ $row->batch_no }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- invoice subtotal -->
        <div class="invoice-subtotal mt-2">
            <div class="row">
                <div class="col m6 s6 l6">
                    {!! ucwords(strtolower($data->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
                </div>
                <div class="col m6 s6 l6">
                    
                </div>
            </div>
            <table class="mt-3" width="100%" border="0">
                <tr>
                    <td class="">
                        <div >Dibuat oleh, {{ $data->user->name }} {{ $data->user->position()->exists() ? $data->user->position->name : '-' }} {{ ($data->post_date ? \Carbon\Carbon::parse($data->updated_at)->format('d/m/Y H:i:s') : '-') }}</div></div>
                    </td>
                </tr>
                    @if($data->approval())
                        @foreach ($data->approval() as $detail)
                            @foreach ($detail->approvalMatrix()->where('status','2')->get() as $row)
                            <tr>    
                                <td>
                                        
                                        
                                        <div>{{ $row->approvalTemplateStage->approvalStage->approval->document_text }}
                                            {{ $row->user->name }} 
                                            @if ($row->user->position()->exists())
                                            {{ $row->user->position->name }}
                                            @endif
                                            {{ ($row->date_process ? \Carbon\Carbon::parse($row->date_process)->format('d/m/Y H:i:s') : '-') }}</div>
                                        <div class="{{ $row->user->date_process ? '' : 'mt-2' }}"></div>
                                        
                                </td>
                            </tr>
                            @endforeach
                        @endforeach
                    @endif
                
            </table>   
        </div>
    </div>
</div>