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
                <span class="invoice-number mr-1">Permohonan Pembelian # {{ $data->code }}</span>
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
                    <div>
                        <small>Dipakai:</small>
                        <span>{{ date('d/m/y',strtotime($data->required_date)) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- logo and title -->
        <div class="row mt-3 invoice-logo-title">
            <div class="col m6 s12">
                <h5 class="indigo-text">Purchase Request</h5>
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
                        {{ $data->user->position->Level->name }}
                    </div>
                    <div class="col s3">
                        Depart.
                    </div>
                    <div class="col s9">
                        {{ $data->user->position->division->name }}
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
                        Lampiran
                    </div>
                    <div class="col s9">
                        <a href="{{ $data->attachment() }}" target="_blank"><i class="material-icons">attachment</i></a>
                    </div>
                    <div class="col s3">
                        Status
                    </div>
                    <div class="col s9">
                        {!! $data->status().''.($data->void_id ? '<div class="mt-2">oleh '.$data->voidUser->name.' tgl. '.date('d/m/y',strtotime($data->void_date)).' alasan : '.$data->void_note.'</div>' : '') !!}
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
                    <th class="center">No</th>
                    <th class="center">Item</th>
                    <th class="center">Jum.</th>
                    <th class="center">Sat.</th>
                    <th class="center">Catatan 1</th>
                    <th class="center">Catatan 2</th>
                    <th class="center">Tgl.Dipakai</th>
                    <th class="center">Plant</th>
                    <th class="center">Line</th>
                    <th class="center">Gudang</th>
                    <th class="center">Departemen</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data->purchaseRequestDetail as $key => $row)
                <tr>
                    <td align="center" rowspan="2">{{ $key+1 }}.</td>
                    <td>{{ $row->item->code.' - '.$row->item->name }}</td>
                    <td class="center">{{ $row->qty }}</td>
                    <td class="center">{{ $row->item->buyUnit->code }}</td>
                    <td>{{ $row->note }}</td>
                    <td>{{ $row->note2 }}</td>
                    <td class="indigo-text center">{{ date('d/m/y',strtotime($row->required_date)) }}</td>
                    <td class="center">{{ $row->place->code.' - '.$row->place->company->name }}</td>
                    <td class="center">{{ $row->line()->exists() ? $row->line->name : '-' }}</td>
                    <td class="center">{{ $row->warehouse->name }}</td>
                    <td class="center">{{ $row->department()->exists() ? $row->department->name : '-' }}</td>
                </tr>
                <tr>
                    <td colspan="10">
                        <b>Mesin</b> : {{ $row->machine()->exists() ? $row->machine->name : '-' }},
                        <b>Requester</b> : {{ $row->requester }},
                        <b>Proyek</b> : {{ $row->project()->exists() ? $row->project->name : '-' }}
                    </td>
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
                    {!! ucwords(strtolower($data->user->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
                </div>
                <div class="col m6 s6 l6">
                    Catatan : {{ $data->note }}
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
                        @if ($data->user->position()->exists())
                            <div class="mt-1">{{ $data->user->position->Level->name.' '.$data->user->position->division->name }}</div>  
                        @endif
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