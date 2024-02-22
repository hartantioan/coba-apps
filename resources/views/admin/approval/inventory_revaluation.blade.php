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
                <span class="invoice-number mr-1">Revaluasi Barang # {{ $data->code }}</span>
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
                <h5 class="indigo-text">Revaluasi Barang</h5>
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
                </div>
            </div>
            <div class="col m6 s6">
                <h6 class="invoice-from">Lain-lain</h6>
                <div class="row">
                    <div class="col s3">
                        HP
                    </div>
                    <div class="col s9">
                        {{ $data->user->phone }}
                    </div>
                    <div class="col s3">
                        Status
                    </div>
                    <div class="col s9">
                        {!! $data->status().''.($data->void_id ? '<div class="mt-2">oleh '.$data->voidUser->name.' tgl. '.date('d/m/Y',strtotime($data->void_date)).' alasan : '.$data->void_note.'</div>' : '') !!}
                    </div>
                </div>
            </div>
        </div>
    <div class="divider mb-3 mt-3"></div>
    <!-- product details table-->
    <div class="invoice-product-details">
        <table class="bordered" style="margin-top:25px;">
            <thead>
                <tr>
                    <th class="center-align">No</th>
                    <th class="center-align">Item</th>
                    <th class="center-align">Plant</th>
                    <th class="center-align">Gudang</th>
                    <th class="center-align">Area</th>
                    <th class="center-align">Shading</th>
                    <th class="center-align">Line</th>
                    <th class="center-align">Mesin</th>
                    <th class="center-align">Divisi</th>
                    <th class="center-align">Proyek</th>
                    <th class="center-align">Qty</th>
                    <th class="center-align">Sat.</th>
                    <th class="center-align">Coa</th>
                    <th class="center-align">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data->inventoryRevaluationDetail as $keydetail => $rowdetail)
                <tr>
                    <td align="center">{{ ($keydetail + 1) }}</td>
                    <td>{{ $rowdetail->item->code.' - '.$rowdetail->item->name }}</td>
                    <td align="center">{{ $rowdetail->place->code }}</td>
                    <td align="center">{{ $rowdetail->warehouse->name }}</td>
                    <td align="center">{{ $rowdetail->itemStock->area()->exists() ? $rowdetail->itemStock->area->name : '-' }}</td>
                    <td align="center">{{ $rowdetail->itemStock->itemShading()->exists() ? $rowdetail->itemStock->itemShading->code : '-' }}</td>
                    <td align="center">{{ $rowdetail->line()->exists() ? $rowdetail->line->name : '-' }}</td>
                    <td align="center">{{ $rowdetail->machine()->exists() ? $rowdetail->machine->name : '-' }}</td>
                    <td align="center">{{ $rowdetail->department()->exists() ? $rowdetail->department->name : '-' }}</td>
                    <td align="center">{{ $rowdetail->project()->exists() ? $rowdetail->project->name : '-' }}</td>
                    <td align="center">{{ CustomHelper::formatConditionalQty($rowdetail->qty) }}</td>
                    <td align="center">{{ $rowdetail->itemUnit->unit->code }}</td>
                    <td>{{ $rowdetail->coa->name }}</td>
                    <td align="center">{{ number_format($rowdetail->nominal,2,',','.') }}</td>
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