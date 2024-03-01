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
                <span class="invoice-number mr-1">Barang Masuk # {{ $data->code }}</span>
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
                <h5 class="indigo-text">Barang Masuk</h5>
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
                        {{ $data->user->position_id ? $data->user->position->Level->name : '-' }}
                    </div>
                    <div class="col s3">
                        Depart.
                    </div>
                    <div class="col s9">
                        {{ $data->user->position_id ? $data->user->position->division->name : '-' }}
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
                        {!! $data->status().''.($data->void_id ? '<div class="mt-2">oleh '.$data->voidUser->name.' tgl. '.date('d/m/Y',strtotime($data->void_date)).' alasan : '.$data->void_note.'</div>' : '') !!}
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
                    <th class="center">Item</th>
                    <th class="center">Jum.</th>
                    <th class="center">Sat.</th>
                    <th class="center">Tipe Biaya</th>
                    <th class="center">Coa</th>
                    <th class="center">Dari Plant</th>
                    <th class="center">Dari Gudang</th>
                    <th class="center">Area</th>
                    <th class="center">Shading</th>
                    <th class="center">Dist.Biaya</th>
                    <th class="center">Plant</th>
                    <th class="center">Line</th>
                    <th class="center">Mesin</th>
                    <th class="center">Divisi</th>
                    <th class="center">Proyek</th>
                    <th class="center">Requester</th>
                    <th class="center">Jum.Kembali</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data->goodIssueDetail as $row)
                <tr>
                    <td>{{ $row->itemStock->item->code.' - '.$row->itemStock->item->name }}</td>
                    <td class="center-align">{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
                    <td class="center-align">{{ $row->itemStock->item->uomUnit->code }}</td>
                    <td class="center-align">{{ $row->inventoryCoa()->exists() ? $row->inventoryCoa->name : '-' }}</td>
                    <td class="center-align">{{ $row->coa()->exists() ? $row->coa->code.' - '.$row->coa->name : '-' }}</td>
                    <td class="center-align">{{ $row->itemStock->place->code }}</td>
                    <td class="center-align">{{ $row->itemStock->warehouse->name }}</td>
                    <td class="center-align">{{ $row->itemStock->area()->exists() ? $row->itemStock->area->name : '-' }}</td>
                    <td class="center-align">{{ $row->itemShading()->exists() ? $row->itemShading->code : '-' }}</td>
                    <td class="center-align">{{ $row->costDistribution()->exists() ? $row->costDistribution->code.' - '.$row->costDistribution->name : '-' }}</td>
                    <td class="center-align">{{ $row->place()->exists() ? $row->place->code : '-' }}</td>
                    <td class="center-align">{{ $row->line()->exists() ? $row->line->name : '-' }}</td>
                    <td class="center-align">{{ $row->machine()->exists() ? $row->machine->name : '-' }}</td>
                    <td class="center-align">{{ $row->department()->exists() ? $row->department->name : '-' }}</td>
                    <td class="center-align">{{ $row->project()->exists() ? $row->project->name : '-' }}</td>
                    <td class="center-align">{{ $row->requester ? $row->requester : '-' }}</td>
                    <td class="center-align">{{ number_format($row->qty_return,3,',','.') }}</td>
                </tr>
                <tr>
                    <td colspan="16">Keterangan 1 : {{ $row->note }}</td>
                </tr>
                <tr>
                    <td colspan="16">Keterangan 2 : {{ $row->note2 }}</td>
                </tr>
                <tr>
                    <td colspan="16">Serial : {{ $row->listSerial() }}</td>
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
                    {!! ucwords(strtolower($data->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
                </div>
                <div class="col m6 s6 l6">
                    Catatan : {{ $data->note }}
                </div>
            </div>
            <table class="mt-3" width="100%" border="0">
                <tr>
                    <td class="">
                        
                        
                        <div >Dibuat oleh, {{ $data->user->name }} {{ $data->user->position->Level->name}} {{ ($data->post_date ? \Carbon\Carbon::parse($data->updated_at)->format('d/m/Y H:i:s') : '-') }}</div></div>
                       
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
                                            {{ $row->user->position->Level->name }}
                                            @endif
                                            {{ ($row->date_process ? \Carbon\Carbon::parse($row->date_process)->format('d/m/Y H:i:s') : '-') }}</div>
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