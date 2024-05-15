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
            <div class="col m4 s12">
                <span class="invoice-number mr-1"># {{ $data->code }}</span>
            </div>
            <div class="col m4 s12 center-align">
                <h5 class="indigo-text">{{ $title }}</h5>
            </div>
            <div class="col m4 s12 right-align">
                <img src="{{ url('website/logo_web_fix.png') }}" width="55%">
            </div>
        </div>
        <div class="divider mb-1 mt-1"></div>
        <!-- invoice address and contact -->
        <div class="row">
            <div class="col s6 row mt-2">
                <div class="col s12 center-align">
                    INFO UTAMA
                </div>
                <div class="col s4">
                    Perusahaan
                </div>
                <div class="col s8">
                    {{ $data->company->name }}
                </div>
                <div class="col s4">
                    Collector
                </div>
                <div class="col s8">
                    {{ $data->account->name }}
                </div>
            </div>
            <div class="col s6 row mt-2">
                <div class="col s4">
                    Bukti
                </div>
                <div class="col s8">
                    <a href="{{ $data->attachment() }}" target="_blank">Lihat</a>
                </div>
                <div class="col s4">
                    Keterangan
                </div>
                <div class="col s8">
                    {{ $data->note }}
                </div>
            </div>
        </div>
    </div>

    <div class="invoice-product-details mt-2" style="overflow:auto;">
        <table class="bordered">
            <thead>
                <tr>
                    <th class="center-align" width="5%">No.</th>
                    <th class="center-align" width="15%">No.Kwitansi</th>
                    <th class="center-align" width="20%">Customer</th>
                    <th class="center-align" width="30%">Alamat</th>
                    <th class="center-align" width="10%">Tgl</th>
                    <th class="center-align" width="20%">Tagihan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data->marketingOrderHandoverReceiptDetail as $key => $row)
                <tr>
                    <td class="center-align">{{ ($key + 1) }}</td>
                    <td class="">{{ $row->marketingOrderReceipt->code }}</td>
                    <td class="">{{ $row->marketingOrderReceipt->account->name }}</td>
                    <td class="">{{ $row->marketingOrderReceipt->account->address }}</td>
                    <td class="center-align">{{ date('d/m/Y',strtotime($row->marketingOrderReceipt->post_date)) }}</td>
                    <td class="right-align">{{ number_format($row->marketingOrderReceipt->grandtotal,2,',','.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>