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
                <span class="invoice-number mr-1">Kapitalisasi # {{ $data->code }}</span>
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
        <div class="row mt-3 invoice-logo-title">
            <div class="col m6 s12">
                <h5 class="indigo-text">Kapitalisasi Aset Perusahaan</h5>
            </div>
            <div class="col m6 s12 right-align">
                <img src="{{ url('website/logo_web_fix.png') }}" width="40%">
            </div>
        </div>
        <table border="0" width="100%" class="mt-3">
            <tr>
                <td width="50%" class="left-align" style="vertical-align: top !important;">
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
                                {{ $data->user->position_id ? $data->user->position->Level->name : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td width="40%">
                                Depart.
                            </td>
                            <td width="60%">
                                {{ $data->user->position_id ? $data->user->position->division->name : '-' }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="50%" class="left-align" style="vertical-align: top !important;">
                    <table border="0" width="100%">
                        <tr>
                            <td width="40%">
                                Perusahaan
                            </td>
                            <td width="60%">
                                {{ $data->company->name }}
                            </td>
                        </tr>
                        <tr>
                            <td width="40%">
                                Catatan
                            </td>
                            <td width="60%">
                                {{ $data->note }}
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
                        <th class="center">No.</th>
                        <th class="center">Kode Aset</th>
                        <th class="center">Nama Aset</th>
                        <th class="center-align">Plant</th>
                        <th class="center-align">Gudang</th>
                        <th class="center-align">Line</th>
                        <th class="center-align">Mesin</th>
                        <th class="center-align">Divisi</th>
                        <th class="center-align">Proyek</th>
                        <th class="center-align">Dist.Biaya</th>
                        <th class="center">Harga</th>
                        <th class="center">Qty</th>
                        <th class="center">Satuan</th>
                        <th class="center">Total</th>
                        <th class="center">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data->capitalizationDetail as $key => $row)
                        <tr>
                            <td class="center-align">{{ $key + 1 }}</td>
                            <td>{{ $row->asset->code }}</td>
                            <td>{{ $row->asset->name }}</td>
                            <td>{{ ($row->place()->exists() ? $row->place->code : '-') }}</td>
                            <td>{{ ($row->warehouse()->exists() ? $row->warehouse->name : '-') }}</td>
                            <td>{{ ($row->line()->exists() ? $row->line->code : '-') }}</td>
                            <td>{{ ($row->machine()->exists() ? $row->machine->code : '-') }}</td>
                            <td>{{ ($row->department()->exists() ? $row->department->name : '-') }}</td>
                            <td>{{ ($row->project()->exists() ? $row->project->name : '-') }}</td>
                            <td>{{ ($row->costDistribution()->exists() ? $row->costDistribution->name : '-') }}</td>
                            <td class="right-align">{{ number_format($row->price,3,',','.') }}</td>
                            <td class="center-align">{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
                            <td class="center-align">{{ $row->unit->code }}</td>
                            <td class="right-align">{{ number_format($row->total,3,',','.') }}</td>
                            <td>{{ $row->note }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="invoice-subtotal mt-3">
            <div class="row">
                <div class="col m6 s6 l6">
                    {!! ucwords(strtolower($data->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
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