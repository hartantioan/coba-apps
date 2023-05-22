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
                <span class="invoice-number mr-1">NO # {{ $data->code }}</span>
            </div>
            <div class="col xl8 s7">
                <div class="invoice-date display-flex align-items-right flex-wrap" style="right:0px !important;">
                    <div class="mr-2">
                        <small>Diajukan:</small>
                        <span>{{ date('d/m/y',strtotime($data->post_date)) }}</span>
                    </div>
                    <div class="mr-2">
                        <small>Hingga:</small>
                        <span>{{ date('d/m/y',strtotime($data->due_date)) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- logo and title -->
        <div class="row mt-3 invoice-logo-title">
            <div class="col m6 s12">
                <h5 class="indigo-text">Landed Cost</h5>
            </div>
            <div class="col m6 s12 right-align">
                <img src="{{ url('website/logo_web_fix.png') }}" width="40%">
            </div>
        </div>
        <table border="0" width="100%" class="mt-3">
            <tr>
                <td width="33%" class="left-align" style="vertical-align: top !important;">
                    <table border="0" width="100%">
                        <tr>
                            <td width="40%">
                                Dari
                            </td>
                            <td width="60%">
                                {{ $data->user->name }}
                            </td>
                        </tr>
                        <tr>
                            <td width="40%">
                                Posisi
                            </td>
                            <td width="60%">
                                {{ $data->user->position->name }}
                            </td>
                        </tr>
                        <tr>
                            <td width="40%">
                                Depart.
                            </td>
                            <td width="60%">
                                {{ $data->user->department->name }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="33%" class="left-align" style="vertical-align: top !important;">
                    <table border="0" width="100%">
                        <tr>
                            <td width="40%">
                                Vendor
                            </td>
                            <td width="60%">
                                {{ $data->vendor->name }}
                            </td>
                        </tr>
                        <tr>
                            <td width="40%">
                                Alamat
                            </td>
                            <td width="60%">
                                {{ $data->vendor->address }}
                            </td>
                        </tr>
                        <tr>
                            <td width="40%">
                                Telepon
                            </td>
                            <td width="60%">
                                {{ $data->vendor->phone.' / '.$data->vendor->office_no }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="33%" class="left-align" style="vertical-align: top !important;">
                    <table border="0" width="100%">
                        <tr>
                            <td width="40%">
                                Lampiran
                            </td>
                            <td width="60%">
                                <a href="{{ $data->attachment() }}" target="_blank"><i class="material-icons">attachment</i></a>
                            </td>
                        </tr>
                        <tr>
                            <td width="40%">
                                Status
                            </td>
                            <td width="60%">
                                {!! $data->status().''.($data->void_id ? '<div class="mt-2">oleh '.$data->voidUser->name.' tgl. '.date('d M Y',strtotime($data->void_date)).' alasan : '.$data->void_note.'</div>' : '') !!}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <div class="invoice-product-details mt-3">
            <table class="bordered">
                <thead>
                    <tr>
                        <th class="center">No</th>
                        <th class="center">Plant</th>
                        <th class="center">Departemen</th>
                        <th class="center">Gudang</th>
                        <th class="center">Item</th>
                        <th class="center">Qty</th>
                        <th class="center">Satuan</th>
                        <th class="center">Harga Total</th>
                        <th class="center">Harga Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data->landedCostDetail as $key => $row)
                    <tr>
                        <td class="center">{{ ($key + 1) }}</td>
                        <td>{{ $row->place->name.' - '.$row->place->company->name }}</td>
                        <td>{{ $row->department->name }}</td>
                        <td>{{ $row->warehouse->name }}</td>
                        <td>{{ $row->item->name }}</td>
                        <td class="center">{{ $row->qty }}</td>
                        <td class="center">{{ $row->item->uomUnit->code }}</td>
                        <td class="right-align">{{ number_format($row->nominal,2,',','.') }}</td>
                        <td class="right-align">{{ number_format(round($row->nominal / $row->qty,3),2,',','.') }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td class="right-align" colspan="7">Total</td>
                        <td class="right-align">{{ number_format($data->total,2,',','.') }}</td>
                        <td class="right-align"></td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="7">PPN</td>
                        <td class="right-align">{{ number_format($data->tax,2,',','.') }}</td>
                        <td class="right-align"></td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="7">PPH</td>
                        <td class="right-align">{{ number_format($data->wtax,2,',','.') }}</td>
                        <td class="right-align"></td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="7">Grandtotal</td>
                        <td class="right-align">{{ number_format($data->grandtotal,2,',','.') }}</td>
                        <td class="right-align"></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="9">Terbilang : <i>{{ CustomHelper::terbilang($data->grandtotal).' '.ucwords($data->currency->document_text) }}</i></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="invoice-subtotal mt-3">
            <div class="row">
                <div class="col m6 s6 l6">
                    {!! ucwords(strtolower($data->user->place->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
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
                        <div class="mt-1">{{ $data->user->position->name.' - '.$data->user->department->name }}</div>
                    </td>
                    @if($data->approval())
                    @foreach ($data->approval()->approvalMatrix()->where('status','2')->get() as $row)
                        <td class="center-align">
                            {{ $row->approvalTemplateStage->approvalStage->approval->document_text }}
                            @if($row->user->signature)
                                <div>{!! $row->user->signature() !!}</div>
                            @endif
                            <div class="{{ $row->user->signature ? '' : 'mt-5' }}">{{ $row->user->name }}</div>
                            <div class="mt-1">{{ $row->user->position->name.' - '.$row->user->department->name }}</div>
                        </td>
                    @endforeach
                @endif
                </tr>
            </table>   
        </div>
    </div>
</div>