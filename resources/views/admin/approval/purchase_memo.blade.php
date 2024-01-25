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
                <span class="invoice-number mr-1">Order # {{ $data->code }}</span>
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
                <h5 class="indigo-text">Purchase Memo</h5>
            </div>
            <div class="col m6 s12 right-align">
                <img src="{{ url('website/logo_web_fix.png') }}" width="35%">
            </div>
        </div>
        <div class="divider mb-1 mt-1"></div>
        <!-- invoice address and contact -->
        <table border="0" width="100%">
            <tr>
                <td width="50%" class="left-align">
                    <table border="0" width="100%">
                        <tr>
                            <td width="50%">
                                Partner Bisnis
                            </td>
                            <td width="50%">
                                {{ $data->account->name }}
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                Alamat
                            </td>
                            <td width="50%">
                                {{ $data->account->address }}
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                Telepon
                            </td>
                            <td width="50%">
                                {{ $data->account->phone.' / '.$data->account->office_no }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="50%" class="left-align">
                    <table border="0" width="100%">
                        <tr>
                            <td width="50%">
                                No. Faktur Pajak Balikan
                            </td>
                            <td width="50%">
                                {{ $data->return_tax_no }}
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                Tanggal Retur
                            </td>
                            <td width="50%">
                                {{ date('d/m/Y',strtotime($data->return_date)) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <div class="invoice-product-details mt-2">
            <table class="bordered">
                <thead>
                    <tr>
                        <th class="center-align">No.</th>
                        <th class="center-align">Referensi</th>
                        <th class="center-align">Qty</th>
                        <th class="center-align">Keterangan</th>
                        <th class="center-align">Total</th>
                        <th class="center-align">PPN</th>
                        <th class="center-align">PPh</th>
                        <th class="center-align">Grandtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data->purchaseMemoDetail as $key => $row)
                    <tr>
                        <td class="center-align" rowspan="3">{{ ($key + 1) }}</td>
                        <td class="center-align">{{ $row->getCode() }}</td>
                        <td class="right-align">{{ number_format($row->qty,3,',','.') }}</td>
                        <td class="right-align">{{ number_format($row->total,2,',','.') }}</td>
                        <td class="right-align">{{ number_format($row->tax,2,',','.') }}</td>
                        <td class="right-align">{{ number_format($row->wtax,2,',','.') }}</td>
                        <td class="right-align">{{ number_format($row->grandtotal,2,',','.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="7">Keterangan 1 : {{ $row->description }}</td>
                    </tr>
                    <tr>
                        <td colspan="7">Keterangan 2 : {{ $row->description2 }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="7" rowspan="7">
                            Rekening :
                            {{ $data->supplier->defaultBank() ? $data->supplier->defaultBank() : ' - ' }}
                            <div class="mt-3">
                                Catatan : {{ $data->note }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="6">Total</td>
                        <td class="right-align">{{ number_format($data->total,2,',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="6">PPN</td>
                        <td class="right-align">{{ number_format($data->tax,2,',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="6">PPh</td>
                        <td class="right-align">{{ number_format($data->wtax,2,',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="6"><h6>Pembulatan</h6></td>
                        <td class="right-align"><h6>{{ number_format($data->rounding,2,',','.') }}</h6></td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="6"><h6>Grandtotal</h6></td>
                        <td class="right-align"><h6>{{ number_format($data->grandtotal,2,',','.') }}</h6></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="10">Terbilang : <i>{{ CustomHelper::terbilangWithKoma($data->grandtotal).' '.$data->currency->document_text }}</i></th>
                    </tr>
                </tfoot>
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
                    <td class="">
                        Dibuat oleh,
                        @if($data->user->signature)
                            <div>{!! $data->user->signature() !!}</div>
                        @endif
                        <div class="{{ $data->user->signature ? '' : 'mt-5' }}">{{ $data->user->name }}</div>
                        <div class="mt-1">{{ $data->user->position->Level->name.' - '.$data->user->position->division->name }}</div>
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