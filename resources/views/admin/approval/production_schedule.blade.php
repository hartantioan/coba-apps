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
                <span class="invoice-number mr-1">{{ $title }} # {{ $data->code }}</span>
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
                <h5 class="indigo-text">{{ $title }}</h5>
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
                    INFO UTAMA
                </div>
                <div class="col s4">
                    Nama
                </div>
                <div class="col s8">
                    {{ $data->user->name }}
                </div>
                <div class="col s4">
                    Perusahaan
                </div>
                <div class="col s8">
                    {{ $data->company->name }}
                </div>
                <div class="col s4">
                    Plant
                </div>
                <div class="col s8">
                    {{ $data->place->code }}
                </div>
            </div>
            <div class="col s6 row mt-2">
                <div class="col s12 center-align">
                    LAIN-LAIN
                </div>
                <div class="col s4">
                    Line
                </div>
                <div class="col s8">
                    {{ $data->line->code }}
                </div>
                <div class="col s4">
                    Tgl.Post
                </div>
                <div class="col s8">
                    {{ date('d/m/Y',strtotime($data->post_date)) }}
                </div>
                <div class="col s4">
                    Keterangan
                </div>
                <div class="col s8">
                    {{ $data->note }}
                </div>
            </div>
        </div>
        
        <div class="invoice-product-details mt-2" style="overflow:auto;">
            <table class="bordered" width="100%">
                <thead>
                    <tr>
                        <th colspan="7" class="center-align">Daftar Target Produksi</th>
                    </tr>
                    <tr>
                        <th class="center-align">No.</th>
                        <th class="center-align">MOP</th>
                        <th class="center-align">Item</th>
                        <th class="center-align">Qty</th>
                        <th class="center-align">Satuan</th>
                        <th class="center-align">Tgl.Request</th>
                        <th class="center-align">Prioritas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data->productionScheduleTarget as $key => $row)
                    <tr>
                        <td class="center-align" rowspan="2">{{ ($key + 1) }}</td>
                        <td class="center-align">{{ $row->marketingOrderPlanDetail->marketingOrderPlan->code }}</td>
                        <td class="center-align">{{ $row->marketingOrderPlanDetail->item->code.' - '.$row->marketingOrderPlanDetail->item->name }}</td>
                        <td class="right-align">{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
                        <td class="center-align">{{ $row->marketingOrderPlanDetail->item->uomUnit->code }}</td>
                        <td class="center-align">{{ date('d/m/Y',strtotime($row->marketingOrderPlanDetail->request_date)) }}</td>
                        <td class="center-align">{{ $row->marketingOrderPlanDetail->priority }}</td>
                    </tr>
                    <tr>
                        <td colspan="6">Keterangan: {{ $row->marketingOrderPlanDetail->note }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="invoice-product-details mt-2 mr-1 ml-1" style="overflow:auto;padding:1px;">
            <table class="bordered" width="100%">
                <thead>
                    <tr>
                        <th colspan="14" class="center-align">Daftar Jadwal Produksi</th>
                    </tr>
                    <tr>
                        <th class="center-align">Proses</th>
                        <th class="center-align">No.</th>
                        <th class="center-align" style="min-width:150px !important;">Shift</th>
                        <th class="center-align" style="min-width:150px !important;">Kode Item</th>
                        <th class="center-align" style="min-width:150px !important;">Nama Item</th>
                        <th class="center-align" style="min-width:150px !important;">Kode BOM</th>
                        <th class="center-align" style="min-width:150px !important;">Qty</th>
                        <th class="center-align" style="min-width:150px !important;">Satuan UoM</th>
                        <th class="center-align" style="min-width:150px !important;">Grup</th>
                        <th class="center-align" style="min-width:150px !important;">Gudang</th>
                        <th class="center-align" style="min-width:150px !important;">Tgl.Mulai</th>
                        <th class="center-align" style="min-width:150px !important;">Tgl.Selesai</th>
                        <th class="center-align" style="min-width:150px !important;">Status</th>
                        <th class="center-align" style="min-width:150px !important;">NO PDO</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data->productionScheduleDetail as $key => $row)
                    <tr>
                        <td class="center-align" rowspan="2">
                            @if ($row->status == '2')
                                {!! $row->status() !!}
                            @else
                                <label>
                                    <input type="checkbox" id="arr_status_production_schedule{{ $key }}" name="arr_status_production_schedule[]" value="{{ $row->id }}" {{ $row->status == '1' ? 'checked' : '' }}>
                                    <span>Pilih</span>
                                </label>
                            @endif
                        </td>
                        <td class="center-align" rowspan="2">{{ ($key + 1) }}</td>
                        <td>{{ $row->shift->code.' - '.$row->shift->name }}</td>
                        <td>{{ $row->item->code }}</td>
                        <td>{{ $row->item->name }}</td>
                        <td>{{ $row->bom->code.' - '.$row->bom->name }}</td>
                        <td class="right-align">{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
                        <td class="center-align">{{ $row->item->uomUnit->code }}</td>
                        <td class="center-align">{{ $row->group }}</td>
                        <td class="center-align">{{ $row->warehouse->code }}</td>
                        <td class="center-align">{{ date('d/m/Y',strtotime($row->start_date)) }}</td>
                        <td class="center-align">{{ date('d/m/Y',strtotime($row->end_date)) }}</td>
                        <td class="center-align">{{ $row->status() }}</td>           
                        <td class="center-align">{{ ($row->productionOrder()->exists() ? $row->productionOrder->code : '-') }}</td>
                    </tr>
                    <tr>
                        <td colspan="12">Keterangan : {{ $row->note }}</td>
                    </tr>
                    @endforeach
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
                    <td class="">
                        <div >Dibuat oleh, {{ $data->user->name }} {{ $data->user->position()->exists() ? $data->user->position->name : '-' }} {{ ($data->post_date ? \Carbon\Carbon::parse($data->updated_at)->format('d/m/Y H:i:s') : '-') }}</div></div>
                    </td>
                </tr>
                    @if($data->approval())
                        @foreach ($data->approval() as $detail)
                            @foreach ($detail->approvalMatrix()->where('status','2')->get() as $row)
                            <tr>    
                                <td>
                                        
                                        
                                       <div>{{ $row->approvalTemplateStage->approvalStage->approval->document_text }}
                                            {{ $row->user->name }} 
                                            @if ($row->user->position()->exists())
                                            {{ $row->user->position->name }}
                                            @endif
                                            {{ ($row->date_process ? \Carbon\Carbon::parse($row->date_process)->format('d/m/Y H:i:s') : '-').' Keterangan : '.$row->note }}</div>
                                        <div class="{{ $row->user->date_process ? '' : 'mt-2' }}"></div>
                                        
                                </td>
                            </tr>
                            @endforeach
                        @endforeach
                    @endif
                
            </table>   
        </div>
    </div>
</div>