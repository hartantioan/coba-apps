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
                <span class="invoice-number mr-1">MOD # {{ $data->code }}</span>
            </div>
            <div class="col xl8 s7">
                <div class="invoice-date display-flex align-items-right flex-wrap" style="right:0px !important;">
                    <div class="mr-2">
                        <small>Diajukan:</small>
                        <span>{{ date('d/m/y',strtotime($data->post_date)) }}</span>
                    </div>
                    <div class="mr-2">
                        <small>Dikirimkan:</small>
                        <span>{{ date('d/m/y',strtotime($data->delivery_date)) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- logo and title -->
        <div class="row mt-1 invoice-logo-title">
            <div class="col m6 s12">
                <h5 class="indigo-text">Marketing Order Delivery</h5>
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
                    CUSTOMER
                </div>
                <div class="col s4">
                    Nama
                </div>
                <div class="col s8">
                    {{ $data->marketingOrder->account->name }}
                </div>
                <div class="col s4">
                    Alamat
                </div>
                <div class="col s8">
                    {{ $data->marketingOrder->account->address }}
                </div>
                <div class="col s4">
                    Telepon
                </div>
                <div class="col s8">
                    {{ $data->marketingOrder->account->phone.' / '.$data->marketingOrder->account->office_no }}
                </div>
                <div class="col s12 center-align mt-3">
                    EKSPEDISI
                </div>
                <div class="col s4">
                    Partner Bisnis
                </div>
                <div class="col s8">
                    {{ $data->account->name }}
                </div>
                <div class="col s4">
                    Alamat
                </div>
                <div class="col s8">
                    {{ $data->account->address }}
                </div>
                <div class="col s4">
                    Telepon
                </div>
                <div class="col s8">
                    {{ $data->account->phone.' / '.$data->account->office_no }}
                </div>
            </div>
            <div class="col s6 row mt-2">
                <div class="col s12 center-align">
                    PENGIRIMAN
                </div>
                <div class="col s4">
                    Tipe
                </div>
                <div class="col s8">
                    {{ $data->marketingOrder->deliveryType() }}
                </div>
                <div class="col s4">
                    Tgl.Kirim
                </div>
                <div class="col s8">
                    {{ date('d/m/y',strtotime($data->delivery_date)) }}
                </div>
                <div class="col s4">
                    Almt Kirim
                </div>
                <div class="col s8">
                    {{ $data->marketingOrder->shipment_address }}
                </div>
                <div class="col s4">
                    Almt Tujuan
                </div>
                <div class="col s8">
                    {{ $data->marketingOrder->destination_address.', '.ucwords(strtolower($data->marketingOrder->subdistrict->name.' - '.$data->marketingOrder->city->name.' - '.$data->marketingOrder->province->name)) }}
                </div>
            </div>
        </div>
        
        <div class="invoice-product-details mt-2" style="overflow:auto;">
            <table class="bordered">
                <thead>
                    <tr>
                        <th class="center-align">No.</th>
                        <th class="center-align">Item</th>
                        <th class="center-align">Stok</th>
                        <th class="center-align">Qty</th>
                        <th class="center-align">Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data->marketingOrderDeliveryDetail as $key => $row)
                    <tr>
                        <td class="center-align" rowspan="2">{{ ($key + 1) }}</td>
                        <td class="center-align">{{ $row->item->name }}</td>
                        <td class="">{{ $row->itemStock->place->name.' - '.$row->itemStock->warehouse->name }}</td>
                        <td class="center-align">{{ number_format($row->qty,3,',','.') }}</td>
                        <td class="center-align">{{ $row->item->sellUnit->code }}</td>
                    </tr>
                    <tr>
                        <td colspan="5">Keterangan: {{ $row->note }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="5">
                            <div class="mt-3">
                                Catatan Internal : {{ $data->note_internal }}
                            </div>
                            <div class="mt-3">
                                Catatan Eksternal : {{ $data->note_external }}
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- invoice subtotal -->
        <div class="invoice-subtotal mt-2">
            <div class="row">
                <div class="col m6 s6 l6">
                    {!! ucwords(strtolower($data->user->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
                </div>
                <div class="col m6 s6 l6">
                    
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
                        <div class="mt-1">{{ $data->user->position->name.' - '.$data->user->department->name }}</div>
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
                                    <div class="mt-1">{{ $row->user->position->name.' - '.$row->user->department->name }}</div>
                                </td>
                            @endforeach
                        @endforeach
                    @endif
                </tr>
            </table>   
        </div>
    </div>
</div>