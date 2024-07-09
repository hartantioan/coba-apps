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
                        <small>{{ __('translations.submitted') }}:</small>
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
                    {{ __('translations.production_schedule') }}
                </div>
                <div class="col s4">
                    {{ __('translations.name') }}
                </div>
                <div class="col s8">
                    {{ $data->user->name }}
                </div>
                <div class="col s4">
                    {{ __('translations.company') }}
                </div>
                <div class="col s8">
                    {{ $data->company->name }}
                </div>
                <div class="col s4">
                    {{ __('translations.plant') }}
                </div>
                <div class="col s8">
                    {{ $data->place->code }}
                </div>
            </div>
            <div class="col s6 row mt-2">
                <div class="col s12 center-align">
                    {{ __('translations.others') }}
                </div>
                <div class="col s4">
                    {{ __('translations.post_date') }}
                </div>
                <div class="col s8">
                    {{ date('d/m/Y',strtotime($data->post_date)) }}
                </div>
                <div class="col s4">
                    {{ __('translations.note') }}
                </div>
                <div class="col s8">
                    {{ $data->note }}
                </div>
            </div>
        </div>
        
        <div class="invoice-product-details mt-2" style="overflow:auto;">
            <table class="bordered" width="100%">
                <thead>
                    <tr>
                        <th colspan="7" class="center-align">{{ __('translations.list_of_production_target') }}</th>
                    </tr>
                    <tr>
                        <th class="center-align">{{ __('translations.no') }}.</th>
                        <th class="center-align">MOP</th>
                        <th class="center-align">{{ __('translations.item') }}</th>
                        <th class="center-align">{{ __('translations.qty') }}</th>
                        <th class="center-align">{{ __('translations.unit') }}</th>
                        <th class="center-align">{{ __('translations.request_date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $arrmop = [];
                    @endphp
                    @foreach($data->productionScheduleTarget as $key => $row)
                    <tr>
                        <td class="center-align" rowspan="2">{{ ($key + 1) }}</td>
                        <td class="center-align">{{ $row->marketingOrderPlanDetail->marketingOrderPlan->code }}</td>
                        <td class="center-align">{{ $row->marketingOrderPlanDetail->item->code.' - '.$row->marketingOrderPlanDetail->item->name }}</td>
                        <td class="right-align">{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
                        <td class="center-align">{{ $row->marketingOrderPlanDetail->item->uomUnit->code }}</td>
                        <td class="center-align">{{ date('d/m/Y',strtotime($row->marketingOrderPlanDetail->request_date)) }}</td>
                    </tr>
                    <tr>
                        <td colspan="5">{{ __('translations.note') }}: {{ $row->marketingOrderPlanDetail->note }}</td>
                    </tr>
                    @php
                        $arrmop[] = $row->marketing_order_plan_detail_id;
                    @endphp
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="invoice-product-details mt-2 mr-1 ml-1" style="overflow:auto;padding:1px;">
            <table class="bordered" width="100%">
                <thead>
                    <tr>
                        <th colspan="13" class="center-align">Daftar Jadwal Produksi</th>
                    </tr>
                    <tr>
                        <th class="center-align">{{ __('translations.process') }}</th>
                        <th class="center-align">{{ __('translations.no') }}.</th>
                        <th class="center-align" style="min-width:150px !important;">{{ __('translations.item_code') }}</th>
                        <th class="center-align" style="min-width:150px !important;">{{ __('translations.item_name') }}</th>
                        <th class="center-align" style="min-width:150px !important;">{{ __('translations.BOM_code') }}</th>
                        <th class="center-align" style="min-width:150px !important;">Tgl.Produksi</th>
                        <th class="center-align" style="min-width:150px !important;">{{ __('translations.qty') }}</th>
                        <th class="center-align" style="min-width:150px !important;">{{ __('translations.uom_unit') }}</th>
                        <th class="center-align" style="min-width:150px !important;">{{ __('translations.line') }}</th>
                        <th class="center-align" style="min-width:150px !important;">{{ __('translations.warehouse') }}</th>
                        <th class="center-align" style="min-width:150px !important;">{{ __('translations.status') }}</th>
                        <th class="center-align" style="min-width:150px !important;">{{ __('translations.pdo_no') }}</th>
                        <th class="center-align" style="min-width:150px !important;">{{ __('translations.type') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($arrmop as $rowmop)
                        @foreach($data->productionScheduleDetail()->where('marketing_order_plan_detail_id',$rowmop)->orderBy('id')->get() as $key => $row)
                        <tr>
                            @if($key == 0)
                                <td class="center-align" rowspan="{{ $data->productionScheduleDetail()->where('marketing_order_plan_detail_id',$rowmop)->count() * 2 }}">
                                    @if ($row->status == '2')
                                        {!! $row->status() !!}
                                    @else
                                        <label>
                                            <input type="checkbox" id="arr_status_production_schedule{{ $key }}" name="arr_status_production_schedule[]" value="{{ $row->id }}" {{ $row->status == '1' ? 'checked' : '' }}>
                                            <span>{{ __('translations.select') }}</span>
                                        </label>
                                    @endif
                                </td>
                            @endif
                            <td class="center-align" rowspan="2">{{ ($key + 1) }}</td>
                            <td>{{ $row->item->code }}</td>
                            <td>{{ $row->item->name }}</td>
                            <td>{{ $row->bom->code.' - '.$row->bom->name }}</td>
                            <td class="center-align">{{ date('d/m/Y',strtotime($row->production_date)) }}</td>
                            <td class="right-align">{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
                            <td class="center-align">{{ $row->item->uomUnit->code }}</td>
                            <td class="center-align">{{ $row->line->code }}</td>
                            <td class="center-align">{{ $row->warehouse->name }}</td>
                            <td class="center-align">{{ $row->status() }}</td>           
                            <td class="center-align">{{ ($row->productionOrderDetail()->exists() ? $row->productionOrderDetail->productionOrder->code : '-') }}</td>
                            <td class="center-align">{{ $row->type() }}</td>
                        </tr>
                        <tr>
                            <td colspan="12">Keterangan : {{ $row->note }}</td>
                        </tr>
                        @endforeach
                    @endforeach
                    @foreach($data->productionScheduleDetail()->whereNull('marketing_order_plan_detail_id')->orderBy('id')->get() as $key => $row)
                    <tr>
                        <td class="center-align" rowspan="2">
                            @if ($row->status == '2')
                                {!! $row->status() !!}
                            @else
                                <label>
                                    <input type="checkbox" id="arr_status_production_schedule{{ $key }}" name="arr_status_production_schedule[]" value="{{ $row->id }}" {{ $row->status == '1' ? 'checked' : '' }}>
                                    <span>{{ __('translations.select') }}</span>
                                </label>
                            @endif
                        </td>
                        <td class="center-align" rowspan="2">{{ ($key + 1) }}</td>
                        <td>{{ $row->item->code }}</td>
                        <td>{{ $row->item->name }}</td>
                        <td>{{ $row->bom->code.' - '.$row->bom->name }}</td>
                        <td class="center-align">{{ date('d/m/Y',strtotime($row->production_date)) }}</td>
                        <td class="right-align">{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
                        <td class="center-align">{{ $row->item->uomUnit->code }}</td>
                        <td class="center-align">{{ $row->line->code }}</td>
                        <td class="center-align">{{ $row->warehouse->name }}</td>
                        <td class="center-align">{{ $row->status() }}</td>           
                        <td class="center-align">{{ ($row->productionOrderDetail()->exists() ? $row->productionOrderDetail->productionOrder->code : '-') }}</td>
                        <td class="center-align">{{ $row->type() }}</td>
                    </tr>
                    <tr>
                        <td colspan="12">Keterangan : {{ $row->note }}</td>
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