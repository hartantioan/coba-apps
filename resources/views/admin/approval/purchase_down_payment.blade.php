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
            zoom:0.5;
            font-size:11px !important;
        }

        table > thead > tr > th {
            font-size:11px !important;
            font-weight: 800 !important;
        }
    }
    
    table > thead > tr > th {
        font-size:13px !important;
        font-weight: 800 !important;
    }

    table.bordered th {
        padding:5px !important;
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
                <span class="invoice-number mr-1">INVOICE # {{ $data->code }}</span>
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
                <h5 class="indigo-text">Purchase Down Payment</h5>
            </div>
            <div class="col m6 s12 right-align">
                <img src="{{ url('website/logo_web_fix.png') }}" width="40%">
            </div>
        </div>
        <div class="divider mb-3 mt-3"></div>
        <!-- invoice address and contact -->
        <table border="0" width="100%">
            <tr>
                <td width="50%" class="left-align">
                    <table border="0" width="100%">
                        <tr>
                            <td width="50%">
                                Supplier
                            </td>
                            <td width="50%">
                                {{ $data->supplier->name }}
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                Alamat
                            </td>
                            <td width="50%">
                                {{ $data->supplier->address }}
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                Telepon
                            </td>
                            <td width="50%">
                                {{ $data->supplier->phone.' / '.$data->supplier->office_no }}
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                Tipe Pembayaran
                            </td>
                            <td width="50%">
                                {{ $data->type() }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="50%" class="left-align">
                    <div class="row">
                        <div class="col s6">
                            Lampiran
                        </div>
                        <div class="col s6">
                            <a href="{{ $data->attachment() }}" target="_blank"><i class="material-icons">attachment</i></a>
                        </div>
                        <div class="col s6">
                            Mata Uang
                        </div>
                        <div class="col s6">
                            {{ $data->currency->code }}
                        </div>
                        <div class="col s6">
                            Konversi
                        </div>
                        <div class="col s6">
                            {{ $data->currency_rate }}
                        </div>
                        <div class="col s6">
                            Status
                        </div>
                        <div class="col s6">
                            {!! $data->status().''.($data->void_id ? '<div class="mt-2">oleh '.$data->voidUser->name.' tgl. '.date('d/m/Y',strtotime($data->void_date)).' alasan : '.$data->void_note.'</div>' : '') !!}
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <!-- product details table-->
        <div class="invoice-product-details">
            @if(count($data->purchaseDownPaymentDetail) > 0)
                <h6 class="center mt-3">Referensi Order Pembelian</h6>
                @foreach($data->purchaseDownPaymentDetail as $key => $row)
                @php
                $arr_pr=[];
                    foreach ($row->purchaseOrder->purchaseOrderDetail as $key => $row_detail_po) {
                        $arr_pr[]=$row_detail_po->purchaseRequestDetail->purchaseRequest->code;
                    }
                    
                @endphp
                    <table class="bordered mt-3 purple lighten-5">
                        <thead>
                            <tr>
                                <th class="center-align">PO No.</th>
                                <th class="center-align">{{ $row->purchaseOrder->code }}</th>
                                <th class="center-align">PR No.</th>
                                <th class="center-align">{{ implode(', ',$arr_pr) }}</th>
                                <th class="center-align">Tgl.Post</th>
                                <th class="center-align">{{ date('d/m/Y',strtotime($row->purchaseOrder->post_date)) }}</th>
                                <th class="center-align">Tgl.Kirim</th>
                                <th class="center-align">{{ date('d/m/Y',strtotime($row->purchaseOrder->delivery_date)) }}</th>
                            </tr>
                            <tr>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">{{ $row->note }}</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">{{ number_format($row->purchaseOrder->grandtotal,2,',','.') }}</th>
                                <th class="center-align">DP Total</th>
                                <th class="center-align">{{ number_format($row->nominal,2,',','.') }}</th>
                                <th class="center-align"></th>
                                <th class="center-align"></th>
                            </tr>
                            <tr>
                                <th class="center-align">Daftar Item</th>
                                <th class="center-align" colspan="7">
                                    <ol>
                                    @foreach ($row->purchaseOrderDetail as $rowdetail)
                                        <li>{{ ($rowdetail->item_id ? $row->item->code.' - '.$rowdetail->item->name : $rowdetail->coa->code.' - '.$rowdetail->coa->name).' Qty : '.number_format($rowdetail->qty,3,',','.').' Sat. '.($rowdetail->item_id ? $rowdetail->itemUnit->unit->code : '-') }}</li>
                                    @endforeach
                                    </ol>
                                </th>
                            </tr>
                        </thead>
                    </table>
                @endforeach
            @endif
            <table class="bordered mt-3">
                <tbody>
                    <tr>
                        <td colspan="7" rowspan="6">
                            Rekening :
                            {{ $data->supplier->defaultBank() ? $data->supplier->defaultBank() : ' - ' }}
                            <div class="mt-3">
                                Catatan : {{ $data->note }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="2">Subtotal</td>
                        <td class="right-align">{{ number_format($data->subtotal,2,',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="2">Diskon</td>
                        <td class="right-align">{{ number_format($data->discount,2,',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="2">Total</td>
                        <td class="right-align">{{ number_format($data->total,2,',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="2">Pajak</td>
                        <td class="right-align">{{ number_format($data->tax,2,',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="2"><h6>Grandtotal</h6></td>
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
        <div class="divider mt-3 mb-3"></div>
        <div class="invoice-subtotal">
            <div class="row">
                <div class="col m6 s6 l6">
                    {!! ucwords(strtolower($data->user->place->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
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