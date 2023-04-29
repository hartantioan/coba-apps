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
                <span class="invoice-number mr-1">Permohonan Dana # {{ $data->code }}</span>
            </div>
            <div class="col xl8 s7">
                <div class="invoice-date display-flex align-items-right flex-wrap" style="right:0px !important;">
                    <div class="mr-2">
                        <small>Diajukan:</small>
                        <span>{{ date('d/m/y',strtotime($data->post_date)) }}</span>
                    </div>
                    <div>
                        <small>Dibayar:</small>
                        <span>{{ date('d/m/y',strtotime($data->pay_date)) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- logo and title -->
        <div class="row mt-3 invoice-logo-title">
            <div class="col m6 s12">
                <h5 class="indigo-text">Permintaan Pembayaran</h5>
            </div>
            <div class="col m6 s12">
                <img src="{{ url('website/logo_web_fix.png') }}" width="80%">
            </div>
        </div>
        <div class="divider mb-3 mt-3"></div>
        <!-- invoice address and contact -->
        <div class="row invoice-info">
            <div class="col m6 s6">
                <h6 class="invoice-from">Dari</h6>
                <div class="row">
                    <div class="col s3">
                        Name
                    </div>
                    <div class="col s9">
                        {{ $data->user->name }}
                    </div>
                    <div class="col s3">
                        Posisi
                    </div>
                    <div class="col s9">
                        {{ $data->user->position->name }}
                    </div>
                    <div class="col s3">
                        Depart.
                    </div>
                    <div class="col s9">
                        {{ $data->user->department->name }}
                    </div>
                    <div class="col s3">
                        HP
                    </div>
                    <div class="col s9">
                        {{ $data->user->phone }}
                    </div>
                </div>
            </div>
            <div class="col m6 s6">
                <h6 class="invoice-from">Lain-lain</h6>
                <div class="row">
                    <div class="col s3">
                        Bisnis Partner
                    </div>
                    <div class="col s9">
                        {{ $data->account->name }}
                    </div>
                    @if($data->payment_type == '2')
                    <div class="col s3">
                        Rek. Tujuan
                    </div>
                    <div class="col s9">
                        {{ $data->account_bank.' - '.$data->account_no.' - '.$data->account_name }}
                    </div>
                    @endif
                    <div class="col s3">
                        Tipe Pembayaran
                    </div>
                    <div class="col s9">
                        {{ $data->paymentType() }}
                    </div>
                    <div class="col s3">
                        Lampiran
                    </div>
                    <div class="col s9">
                        <a href="{{ $data->attachment() }}" target="_blank"><i class="material-icons">attachment</i></a>
                    </div>
                    <div class="col s3">
                        Status
                    </div>
                    <div class="col s9">
                        {!! $data->status().''.($data->void_id ? '<div class="mt-2">oleh '.$data->voidUser->name.' tgl. '.date('d M Y',strtotime($data->void_date)).' alasan : '.$data->void_note.'</div>' : '') !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="divider mb-3 mt-3"></div>
        <!-- product details table-->
        <div class="invoice-product-details">
            <table class="bordered">
                <thead>
                    <tr>
                        <th class="center">Referensi</th>
                        <th class="center">Tipe</th>
                        <th class="center">Keterangan</th>
                        <th class="center">Coa</th>
                        <th class="center">Bayar</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total = 0;
                    @endphp
                    @foreach($data->paymentRequestDetail as $row)
                    <tr>
                        <td>{{ $row->lookable->code }}</td>
                        <td class="center-align">{{ $row->type() }}</td>
                        <td>{{ $row->note }}</td>
                        <td>{{ $row->coa->code.' - '.$row->coa->name }}</td>
                        <td class="right-align">{{ number_format($row->nominal,3,',','.') }}</td>
                    </tr>
                    @php
                        $total += $row->nominal;
                    @endphp
                    @endforeach
                    <tr>
                        <td colspan="4" class="right-align">Total</td>
                        <td class="right-align">{{ number_format($total,3,',','.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="right-align">Admin</td>
                        <td class="right-align">{{ number_format($data->admin,3,',','.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="right-align">Grandtotal</td>
                        <td class="right-align">{{ number_format($data->grandtotal,3,',','.') }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="10">Terbilang : <i>{{ CustomHelper::terbilang($data->grandtotal).' '.ucwords($data->currency->document_text) }}</i></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    <!-- invoice subtotal -->
    <div class="divider mt-3 mb-3"></div>
        <div class="invoice-subtotal">
            <div class="row">
                <div class="col m6 s6 l6">
                    {!! ucwords(strtolower($data->user->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
                </div>
                <div class="col m6 s6 l6">
                    Catatan : {{ $data->note }}
                </div>
            </div>
            <table class="mt-3" width="100%" border="0">
                <tr>
                    <td class="center-align">
                        Dibuat oleh,
                        @if($data->user->signature)
                            <div>{!! $data->user->signature() !!}</div>
                        @endif
                        <div class="{{ $data->user->signature ? '' : 'mt-5' }}">{{ $data->user->name }}</div>
                        <div class="mt-1">{{ $data->user->position->name.' '.$data->user->department->name }}</div>
                    </td>
                    @if($data->approval())
                    @foreach ($data->approval()->approvalMatrix()->where('status','2')->get() as $row)
                        <td class="center-align">
                            {{ $row->approvalTable->approval->document_text }}
                            @if($row->user->signature)
                                <div>{!! $row->user->signature() !!}</div>
                            @endif
                            <div class="{{ $data->user->signature ? '' : 'mt-5' }}">{{ $row->user->name }}</div>
                            <div class="mt-1">{{ $row->user->position->name.' - '.$row->user->department->name }}</div>
                        </td>
                    @endforeach
                @endif
                </tr>
            </table>   
        </div>
    </div>
</div>