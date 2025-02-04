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
            <div class="col xl4 s12">
                <span class="invoice-number mr-1">{{ __('translations.good_scale') }} # {{ $data->code }}</span>
            </div>
            <div class="col xl8 s12">
                <div class="invoice-date display-flex align-items-right flex-wrap" style="right:0px !important;">
                    <div class="mr-2">
                        <small>{{ __('translations.submitted') }}:</small>
                        <span>{{ date('d/m/Y',strtotime($data->post_date)) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- logo and title -->
        <div class="row mt-3 invoice-logo-title">
            <div class="col m6 s12">
                <h5 class="indigo-text">{{ __('translations.good_scale') }}</h5>
            </div>
            <div class="col m6 s12">
                <img src="{{ url('website/logo_web_fix.png') }}" width="80%">
            </div>
        </div>
        <div class="divider mb-3 mt-3"></div>
        <!-- invoice address and contact -->
        <div class="row invoice-info">
            <div class="col m6 s6">
                <h6 class="invoice-from">{{ __('translations.from') }}</h6>
                <div class="row">
                    <div class="col s12 m5">
                        {{ __('translations.name') }}
                    </div>
                    <div class="col s12 m7">
                        {{ $data->user->name }}
                    </div>
                    <div class="col s12 m5">
                        {{ __('translations.position') }}
                    </div>
                    <div class="col s12 m7">
                        {{ $data->user->position_id ? $data->user->position->Level->name : '-' }}
                    </div>
                    <div class="col s12 m5">
                        {{ __('translations.department') }}.
                    </div>
                    <div class="col s12 m7">
                        {{ $data->user->position_id ? $data->user->position->division->name : '-' }}
                    </div>
                    <div class="col s12 m5">
                        {{ __('translations.license_plate') }}
                    </div>
                    <div class="col s12 m7">
                        {{ $data->vehicle_no }}
                    </div>
                    <div class="col s12 m5">
                        {{ __('translations.driver') }}
                    </div>
                    <div class="col s12 m7">
                        {{ $data->driver ?? '-' }}
                    </div>
                    <div class="col s12 m5">
                        No.SJ
                    </div>
                    <div class="col s12 m7">
                        {{ $data->delivery_no ?? '-' }}
                    </div>
                    <div class="col s12 m5">
                        {{ __('translations.plant') }}
                    </div>
                    <div class="col s12 m7">
                        {{ $data->place->code }}
                    </div>
                </div>
            </div>
            <div class="col m6 s6">
                <h6 class="invoice-from">Lain-lain</h6>
                <div class="row">
                    <div class="col s12 m5">
                        {{ __('translations.status') }}
                    </div>
                    <div class="col s12 m7">
                        {!! $data->statusRaw().''.($data->void_id ? '<div class="mt-2">oleh '.$data->voidUser->name.' tgl. '.date('d/m/Y',strtotime($data->void_date)).' alasan : '.$data->void_note.'</div>' : '') !!}
                    </div>
                    <div class="col s12 m5">
                        Berat Bruto (Kg)
                    </div>
                    <div class="col s12 m7">
                        {{ $data->type == '2' ? CustomHelper::formatConditionalQty($data->qty_in) : CustomHelper::formatConditionalQty($data->qty_out) }}
                    </div>
                    <div class="col s12 m5">
                        Berat Tara (Kg)
                    </div>
                    <div class="col s12 m7">
                        {{ $data->type == '2' ? CustomHelper::formatConditionalQty($data->qty_out) : CustomHelper::formatConditionalQty($data->qty_in) }}
                    </div>
                    <div class="col s12 m5">
                        Berat Netto (Kg)
                    </div>
                    <div class="col s12 m7">
                        {{ CustomHelper::formatConditionalQty($data->qty_balance) }}
                    </div>
                    <div class="col s12 m5">
                        Keterangan
                    </div>
                    <div class="col s12 m7">
                        {{ $data->note }}
                    </div>
                </div>
            </div>
        </div>
        <div class="divider mb-3 mt-3"></div>
        <!-- product details table-->
        <div class="row invoice-info">
            <div class="col s12 m12" style="width:100%;overflow:auto;">
                <h6><i>Detail Item/Dokumen Terhubung (Total ada di bawah)</i></h6>
                <table class="bordered">
                    <thead>
                        <tr>
                            <th class="center-align">{{ __('translations.no') }}.</th>
                            <th class="center-align">No. MOD</th>
                            <th class="center-align">No. SJ</th>
                            <th class="center-align">Berat SJ (Kg)</th>
                            <th class="center-align">Item</th>
                            <th class="center-align">Berat Netto Standar (Kg/M2)</th>
                            <th class="center-align">Berat Netto Real (Kg/M2)</th>
                            <th class="center-align">Toleransi Standar (%)</th>
                            <th class="center-align">Selisih Standar (Kg/M2)</th>
                            <th class="center-align">Toleransi Real (%)</th>
                            <th class="center-align">Selisih Real (Kg/M2)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalNettoStandar = 0;
                            $totalNettoReal = 0;
                            $totalBalanceStandar = 0;
                            $totalBalanceReal = 0;
                            $percentTolerance = 0;
                            $totalBalance = 0;
                            $no = 1;
                        @endphp
                        @foreach($data->goodScaleDetail->where('lookable_type','marketing_order_deliveries') as $key => $row)
                            @foreach ($row->lookable->marketingOrderDeliveryProcess->marketingOrderDeliveryProcessDetail as $rowdetail)
                                @php
                                    $weight = $rowdetail->weight();
                                    $weightStandar = $rowdetail->itemStock->item->itemWeightFg()->exists() ? round($rowdetail->itemStock->item->itemWeightFg->netto_weight * $rowdetail->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion,3) : 0;
                                    $balanceStandar = $rowdetail->itemStock->item->toleranceScale()->exists() ? round(($rowdetail->itemStock->item->toleranceScale->percentage / 100) * $weightStandar,2) : 0;
                                    $balanceReal = $weight - $weightStandar;
                                    $toleranceReal = round(($balanceReal / $weightStandar) * 100,2);
                                    $totalNettoStandar += $weightStandar;
                                    $totalNettoReal += $weight;
                                    $totalBalanceReal += $balanceReal;
                                    $totalBalanceStandar += $balanceStandar;
                                @endphp
                                <tr>
                                    <td class="center-align">{{ $no }}</td>
                                    <td>{{ $row->lookable->code }}</td>
                                    <td>{{ $row->lookable->marketingOrderDeliveryProcess->code }}</td>
                                    <td class="right-align">{{ CustomHelper::formatConditionalQty($rowdetail->marketingOrderDeliveryProcess->weight_netto,2,',','.') }}</td>
                                    <td>{{ $rowdetail->itemStock->item->name.' - '.$rowdetail->itemStock->productionBatch->code }}</td>
                                    <td class="right-align">{{ CustomHelper::formatConditionalQty($weightStandar) }}</td>
                                    <td class="right-align">{{ CustomHelper::formatConditionalQty($weight) ?? 0 }}</td>
                                    <td class="right-align">{{ CustomHelper::formatConditionalQty($rowdetail->itemStock->item->toleranceScale->percentage) ?? 0 }}</td>
                                    <td class="right-align">{{ CustomHelper::formatConditionalQty($balanceStandar) }}</td>
                                    <td class="right-align">{{ CustomHelper::formatConditionalQty($toleranceReal) }}</td>
                                    <td class="right-align">{{ CustomHelper::formatConditionalQty($balanceReal) }}</td>
                                </tr>
                                @php
                                    $no++;
                                @endphp
                            @endforeach
                        @endforeach
                        @php
                            $totalBalance = $totalNettoReal - $totalNettoStandar;
                            $percentTolerance = round(($totalBalance / $totalNettoStandar) * 100,2);
                        @endphp
                        <tr>
                            <th class="center-align" colspan="5">TOTAL</th>
                            <th class="right-align">{{ CustomHelper::formatConditionalQty($totalNettoStandar) }}</th>
                            <th class="right-align">{{ CustomHelper::formatConditionalQty($totalNettoReal) ?? 0 }}</th>
                            <th class="right-align">-</th>
                            <th class="right-align">-</th>
                            <th class="right-align">{{ CustomHelper::formatConditionalQty($percentTolerance) }}%</th>
                            <th class="right-align">{{ CustomHelper::formatConditionalQty($totalBalance) }}</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    <!-- invoice subtotal -->
    <div class="divider mt-3 mb-3"></div>
        <div class="invoice-subtotal">
            <div class="row">
                <div class="col m6 s6 l6">
                    {!! ucwords(strtolower($data->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
                </div>
                <div class="col m6 s6 l6">
                    {{ __('translations.note') }} : {{ $data->note }}
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