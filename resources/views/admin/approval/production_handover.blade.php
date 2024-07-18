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
                <span class="invoice-number mr-1">{{ __('translations.issue_receive') }} # {{ $data->code }}</span>
            </div>
            <div class="col xl8 s7">
                <div class="invoice-date display-flex align-items-right flex-wrap" style="right:0px !important;">
                    <div class="mr-2">
                        <small>{{ __('translations.issue_receive') }}:</small>
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
            <div class="col s6 row mt-2">
                <div class="col s12 center-align">
                    {{ __('translations.primary_info') }}
                </div>
                <div class="col s4">
                    {{ __('translations.name') }}
                </div>
                <div class="col s8">
                    {{ $data->user->name }}
                </div>
                <div class="col s4">
                    {{ __('translations.production_code') }}
                </div>
                <div class="col s8">
                    {{ $data->productionFgReceive->productionOrderDetail->productionOrder->code }}
                </div>
                <div class="col s4">
                    {{ __('translations.schedule_code') }}
                </div>
                <div class="col s8">
                    {{ $data->productionFgReceive->productionOrderDetail->productionScheduleDetail->productionSchedule->code }}
                </div>
            </div>
            <div class="col s6 row mt-2">
                <div class="col s12 center-align">
                    {{ __('translations.others') }}
                </div>
                <div class="col s8">
                    {{ date('d/m/Y',strtotime($data->post_date)) }}
                </div>
                <div class="col s4">
                    {{ __('translations.information') }}
                </div>
                <div class="col s8">
                    {{ $data->note }}
                </div>
            </div>
        </div>
        
        <div class="invoice-product-details mt-2" style="overflow:auto;width:100%;">
            <table class="bordered" style="min-width:1400px !important;">
                <thead>
                    <tr>
                        <th colspan="10" class="center-align">Daftar Item Diterima</th>
                    </tr>
                    <tr>
                        <th class="center">No.</th>
                        <th class="center">No.Batch Palet/Curah</th>
                        <th class="center">Item</th>
                        <th class="center">{{ __('translations.shading') }}</th>
                        <th class="center">Qty Diterima</th>
                        <th class="center">Satuan</th>
                        <th class="center">Konversi</th>
                        <th class="center">{{ __('translations.plant') }}</th>
                        <th class="center">{{ __('translations.warehouse') }}</th>
                        <th class="center">{{ __('translations.area') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data->productionHandoverDetail()->orderBy('id')->get() as $key => $row)
                        <tr>
                            <td class="center-align">{{ ($key+1) }}</td>
                            <td>{{ $row->productionFgReceiveDetail->pallet_no }}</td>
                            <td>{{ $row->item->code.' - '.$row->item->name }}</td>
                            <td>{{ $row->shading }}</td>
                            <td class="right-align">{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
                            <td class="center-align">{{ $row->productionFgReceiveDetail->itemUnit->unit->code }}</td>
                            <td class="right-align">{{ CustomHelper::formatConditionalQty($row->productionFgReceiveDetail->conversion) }}</td>
                            <td class="">{{ $row->place->code }}</td>
                            <td class="">{{ $row->warehouse->name }}</td>
                            <td class="">{{ $row->area->code }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="bordered mt-2" style="width:100% !important;">
                <thead>
                    <tr>
                        <th colspan="6" class="center-align">Daftar Batch Terpakai</th>
                    </tr>
                    <tr>
                        <th class="center">{{ __('translations.no') }}.</th>
                        <th class="center">No.Batch</th>
                        <th class="center">Item Parent</th>
                        <th class="center">Item Child</th>
                        <th class="center">Qty</th>
                        <th class="center">Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data->productionHandoverDetail as $key => $row)
                        <tr>
                            <td class="center-align">{{ ($key+1) }}</td>
                            <td>{{ $row->productionBatchUsage->productionBatch->code }}</td>
                            <td>{{ $data->productionFgReceive->productionOrderDetail->productionScheduleDetail->item->code.' - '.$data->productionFgReceive->productionOrderDetail->productionScheduleDetail->item->name }}</td>
                            <td>{{ $row->productionBatchUsage->productionBatch->item->code.' - '.$row->productionBatchUsage->productionBatch->item->name }}</td>
                            <td class="right-align">{{ CustomHelper::formatConditionalQty($row->productionBatchUsage->qty) }}</td>
                            <td class="center-align">{{ $row->productionBatchUsage->productionBatch->item->uomUnit->code }}</td>
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
                        <div >{{ __('translations.created_by') }}, {{ $data->user->name }} {{ $data->user->position()->exists() ? $data->user->position->name : '-' }} {{ ($data->post_date ? \Carbon\Carbon::parse($data->updated_at)->format('d/m/Y H:i:s') : '-') }}</div></div>
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
                                            {{ ($row->date_process ? \Carbon\Carbon::parse($row->date_process)->format('d/m/Y H:i:s') : '-').' Keterangan : '.$row->note }}</div>
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