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
        <!-- logo and title -->
        <div class="row mt-1 invoice-logo-title">
            <div class="col m5 s12">
                <span class="invoice-number mr-1">{{ $title }} # {{ $data->code }}</span>
            </div>
            <div class="col m1 s12 center-align">
                <h5 class="indigo-text">{{ $title }}</h5>
            </div>
            <div class="col m6 s12 right-align">
                <img src="{{ url('website/logo_web_fix.png') }}" width="25%">
            </div>
        </div>
        <div class="divider mb-1 mt-1"></div>
        <!-- invoice address and contact -->
        <div class="row">
            <div class="col s12 row mt-2">
                <div class="col s3 m3">
                    Telah terima dari
                </div>
                <div class="col s9 m9">
                    : {{ $data->account->name }}
                </div>
                <div class="col s3 m3">
                    Untuk pembayaran
                </div>
                <div class="col s9 m9">
                    : {{ implode(', ',$data->arrInvoice()) }}
                </div>
                <div class="col s3 m3">
                    Keterangan
                </div>
                <div class="col s9 m9">
                    : {{ $data->note }}
                </div>
                <div class="col s3 m3">
                    Total Nominal
                </div>
                <div class="col s9 m9">
                    : Rp. {{ number_format($data->grandtotal,2,',','.') }}
                </div>
                <div class="col s3 m3">
                    Terbilang
                </div>
                <div class="col s9 m9">
                    : {{ CustomHelper::terbilangWithKoma($data->grandtotal) }}
                </div>
            </div>
            <div class="col s12 mt-2">
                NB : Bukan merupakan bukti penerimaan, pembayaran dianggap sah jika :
                <ol>
                    <li>Cek/Giro telah dicairkan di rekening</li>
                    <li>Transfer dana telah diterima di rekening</li>
                </ol>
            </div>
            <div class="col s7 mt-2" style="border:1px solid black;">
                Mohon ditransfer ke :<br>
                <b>{!! $data->company->banks() !!}</b><br>
            </div>
            <div class="col s5 mt-2 center-align">
                {{ $data->company->city->name.', '.date('d-M-Y',strtotime($data->post_date)) }}
                <br><br><br><br>
                __________________________
                <br>
                Tanda Tangan dan Nama Terang
            </div>
        </div>
    </div>
</div>