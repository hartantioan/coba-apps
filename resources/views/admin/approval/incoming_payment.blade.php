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
            font-size:11px !important;
        }

        table > thead > tr > th {
            font-size:12px !important;
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
                <span class="invoice-number mr-1">{{ __('translations.incoming_payment') }} # {{ $data->code }}</span>
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
        <div class="row mt-3 invoice-logo-title">
            <div class="col m6 s12">
                <h5 class="indigo-text">{{ __('translations.incoming_payment') }}</h5>
            </div>
            <div class="col m6 s12">
                <img src="{{ url('website/logo_web_fix.png') }}" width="80%">
            </div>
        </div>
        <div class="divider mb-3 mt-3"></div>
        <!-- invoice address and contact -->
        <table border="0" width="100%">
            <tr>
                <td width="33%" class="left-align">
                    <table border="0" width="100%">
                        <tr>
                            <td width="40%">
                                {{ __('translations.name') }}
                            </td>
                            <td width="60%">
                                {{ $data->user->name.' - '.$data->user->phone }}
                            </td>
                        </tr>
                        <tr>
                            <td width="40%">
                                {{ __('translations.position') }}
                            </td>
                            <td width="60%">
                                {{ $data->user->position_id ? $data->user->position->Level->name : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td width="40%">
                                {{ __('translations.department') }}.
                            </td>
                            <td width="60%">
                                {{ $data->user->position_id ? $data->user->position->division->name : '-' }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="33%" class="left-align">
                    <table border="0" width="100%">
                        <tr>
                            <td width="40%">
                                {{ __('translations.bussiness_partner') }}
                            </td>
                            <td width="60%">
                                {{ $data->account()->exists() ? $data->account->name.' - '.$data->account->phone : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td width="40%">
                                {{ __('translations.bank') }}
                            </td>
                            <td width="60%">
                                {{ $data->coa->name }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="33%" class="left-align">
                    <table border="0" width="100%">
                        <tr>
                            <td width="40%">
                                {{ __('translations.company') }}
                            </td>
                            <td width="60%">
                                {{ $data->company->name }}
                            </td>
                        </tr>
                        <tr>
                            <td width="40%">
                                {{ __('translations.status') }}
                            </td>
                            <td width="60%">
                                {!! $data->status().''.($data->void_id ? '<div class="mt-2">oleh '.$data->voidUser->name.' tgl. '.date('d/m/Y',strtotime($data->void_date)).' alasan : '.$data->void_note.'</div>' : '') !!}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <div class="divider mb-3 mt-3"></div>
        <!-- product details table-->
        <div class="invoice-product-details">
        <table class="bordered">
            <thead>
                <tr>
                    <th class="center-align">{{ __('translations.no') }}.</th>
                    <th class="center-align">{{ __('translations.reference') }}</th>
                    <th class="center-align">{{ __('translations.type') }}</th>
                    <th class="center-align">{{ __('translations.cost_distribution') }}</th>
                    <th class="center-align">{{ __('translations.total') }}</th>
                    <th class="center-align">{{ __('translations.rounding') }}</th>
                    <th class="center-align">{{ __('translations.subtotal') }}</th>
                    <th class="center-align">{{ __('translations.information') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data->incomingPaymentDetail as $row)
                <tr>
                    <td class="center-align">{{ $loop->iteration }}</td>
                    <td class="center-align">{{ $row->lookable->code }}</td>
                    <td class="center-align">{{ class_basename($row->lookable) }}</td>
                    <td class="center-align">{{ $row->cost_distribution_id ? $row->costDistribution->code : '-' }}</td>
                    <td class="right-align">{{ number_format($row->total,2,',','.') }}</td>
                    <td class="right-align">{{ number_format($row->rounding,2,',','.') }}</td>
                    <td class="right-align">{{ number_format($row->subtotal,2,',','.') }}</td>
                    <td class="">{{ $row->note }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
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
                        {{ __('translations.created_by') }},
                        @if($data->user->signature)
                            <div>{!! $data->user->signature() !!}</div>
                        @endif
                        <div class="{{ $data->user->signature ? '' : 'mt-5' }}">{{ $data->user->name }}</div>
                        @if ($data->user->position()->exists())
                            <div class="mt-1">{{ $data->user->position->Level->name.' '.$data->user->position->division->name }}</div>  
                        @endif
                    </td>
                    @if($data->approval())
                    @foreach ($data->approval()->approvalMatrix()->where('status','2')->get() as $row)
                        <td class="center-align">
                            {{ $row->approvalTemplateStage->approvalStage->approval->document_text }}
                            @if($row->user->signature)
                                <div>{!! $row->user->signature() !!}</div>
                            @endif
                            <div class="{{ $data->user->signature ? '' : 'mt-5' }}">{{ $row->user->name }}</div>
                            @if ($row->user->position()->exists())
                                        <div class="mt-1">{{ $row->user->position->Level->name.' - '.$row->user->position->division->name }}</div>
                                    @endif
                        </td>
                    @endforeach
                @endif
                </tr>
            </table>   
        </div>
    </div>
</div>