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

    td, th {
        padding: 5px 1px;
        vertical-align: top !important;
    }
</style>
<div class="card">
    <div class="card-content invoice-print-area">
        <!-- header section -->
        <div class="row invoice-date-number">
            <div class="col xl4 s5">
                <span class="invoice-number mr-1">AR Down Payment # {{ $data->code }}</span>
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
                <h5 class="indigo-text">AR Down Payment</h5>
            </div>
            <div class="col m6 s12 right-align">
                <img src="{{ url('website/logo_web_fix.png') }}" width="35%">
            </div>
        </div>
        <div class="divider mb-1 mt-1"></div>
        <!-- invoice address and contact -->
        
        <div class="invoice-product-details mt-2" style="overflow:auto;">
            <table width="60%" align="center">
                <thead>
                    <tr>
                        <th width="19%">Customer</th>
                        <th width="1%">:</th>
                        <th width="80%">{{ $data->account->name }}</th>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <th>:</th>
                        <th>{{ $data->account->address }}</th>
                    </tr>
                    <tr>
                        <th>Telepon</th>
                        <th>:</th>
                        <th>{{ $data->account->phone.' / '.$data->account->office_no }}</th>
                    </tr>
                    <tr>
                        <th>Tipe Bayar</th>
                        <th>:</th>
                        <th>{{ $data->type() }}</th>
                    </tr>
                    <tr>
                        <th>Total</th>
                        <th>:</th>
                        <th>{{ number_format($data->grandtotal,2,',','.') }}</th>
                    </tr>
                    <tr>
                        <th>Terbilang</th>
                        <th>:</th>
                        <th><i>{{ CustomHelper::terbilangWithKoma($data->grandtotal).' '.ucwords(strtolower($data->currency->document_text)) }}</i></th>
                    </tr>
                    @if($data->type !== '1')
                    <tr>
                        <th>Rekening</th>
                        <th>:</th>
                        <th>{!! $data->company->banks() !!}</th>
                    </tr>
                    @endif
                    <tr>
                        <th>Catatan</th>
                        <th>:</th>
                        <th>{{ $data->note }}</th>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="invoice-product-details mt-2" style="overflow:auto;">
            <table class="bordered">
                <thead>
                    <tr>
                        <th class="center-align" colspan="4">Sales Order</th>
                    </tr>
                    <tr>
                        <th class="center-align">Nomor</th>
                        <th class="center-align">Tgl.Post</th>
                        <th class="center-align">Catatan</th>
                        <th class="center-align">Grandtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @if($data->marketingOrderDownPaymentDetail()->exists())
                        @foreach($data->marketingOrderDownPaymentDetail as $row)
                        <tr>
                            <td class="center-align">{{ $row->marketingOrder->code }}</td>
                            <td class="center-align">{{ date('d/m/Y',strtotime($row->marketingOrder->post_date)) }}</td>
                            <td class="">{{ $row->marketingOrder->note_internal.' - '.$row->marketingOrder->note_external }}</td>
                            <td class="right-align">{{ number_format($row->marketingOrder->grandtotal,2,',','.') }}</td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="center-align">Data tidak ditemukan.</td>
                        </tr>
                    @endif
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
                    <td>
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
                                <td>
                                    {{ $row->approvalTemplateStage->approvalStage->approval->document_text }}
                                    @if($row->user->signature)
                                        <div>{!! $row->user->signature() !!}</div>
                                    @endif
                                    <div class="{{ $row->user->signature ? '' : 'mt-5' }}">{{ $row->user->name }}</div>
                                    <div class="{{ $row->user->date_process ? '' : 'mt-2' }}">{{ ($row->date_process ? \Carbon\Carbon::parse($row->date_process)->format('d/m/Y H:i:s') : '-') }}</div>
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