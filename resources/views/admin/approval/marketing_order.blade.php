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
                <span class="invoice-number mr-1">Sales Order # {{ $data->code }}</span>
            </div>
            <div class="col xl8 s7">
                <div class="invoice-date display-flex align-items-right flex-wrap" style="right:0px !important;">
                    <div class="mr-2">
                        <small>Diajukan:</small>
                        <span>{{ date('d/m/Y',strtotime($data->post_date)) }}</span>
                    </div>
                    <div class="mr-2">
                        <small>Valid Hingga:</small>
                        <span>{{ date('d/m/Y',strtotime($data->valid_date)) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- logo and title -->
        <div class="row mt-1 invoice-logo-title">
            <div class="col m6 s12">
                <h5 class="indigo-text">Sales Order</h5>
            </div>
            <div class="col m6 s12 right-align">
                <img src="{{ url('website/logo_web_fix.png') }}" width="35%">
            </div>
        </div>
        <div class="divider mb-1 mt-1"></div>
        <!-- invoice address and contact -->
        <div class="row">
            <div class="col s6 m6 row mt-2">
                <div class="col s12 center-align">
                    CUSTOMER
                </div>
                <div class="col s4">
                    Nama
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
                <div class="col s4">
                    Tipe Penjualan
                </div>
                <div class="col s8">
                    {{ $data->type() }}
                </div>
            </div>
            <div class="col s6 m6 row mt-2">
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
                    Sales
                </div>
                <div class="col s8">
                    {{ $data->sales->name }}
                </div>
                <div class="col s4">
                    No Referensi / PO
                </div>
                <div class="col s8">
                    {{ $data->document_no }}
                </div>
                <div class="col s4">
                    Bukti
                </div>
                <div class="col s8">
                    <a href="{{ $data->attachment() }}" target="_blank">Lihat</a>
                </div>
            </div>
            <div class="col s6 m6 row mt-2">
                <div class="col s12 center-align">
                    PENGIRIMAN
                </div>
                <div class="col s4">
                    Tipe
                </div>
                <div class="col s8">
                    {{ $data->deliveryType() }}
                </div>
                <div class="col s4">
                    Broker
                </div>
                <div class="col s8">
                    {{ $data->sender->name }}
                </div>
                <div class="col s4">
                    Tipe Transport
                </div>
                <div class="col s8">
                    {{ $data->transportation->name }}
                </div>
                <div class="col s4">
                    Tgl.Kirim
                </div>
                <div class="col s8">
                    {{ date('d/m/Y',strtotime($data->delivery_date)) }}
                </div>
                <div class="col s4">
                    Almt Penagihan
                </div>
                <div class="col s8">
                    {{ $data->billing_address }}
                </div>
                <div class="col s4">
                    Outlet
                </div>
                <div class="col s8">
                    {{ $data->outlet->name }}
                </div>
                <div class="col s4">
                    Almt Tujuan
                </div>
                <div class="col s8">
                    {{ $data->destination_address.', '.ucwords(strtolower($data->subdistrict->name.' - '.$data->district->name.' - '.$data->city->name.' - '.$data->province->name)) }}
                </div>
            </div>
            <div class="col s6 m6 row mt-2">
                <div class="col s12 center-align">
                    PEMBAYARAN
                </div>
                <div class="col s4">
                    Tipe
                </div>
                <div class="col s8">
                    {{ $data->paymentType() }}
                </div>
                <div class="col s4">
                    TOP Internal
                </div>
                <div class="col s8">
                    {{ $data->top_internal }} hari
                </div>
                <div class="col s4">
                    TOP Customer
                </div>
                <div class="col s8">
                    {{ $data->top_customer }} hari
                </div>
                <div class="col s4">
                    Garansi
                </div>
                <div class="col s8">
                    {{ $data->isGuarantee() }}
                </div>
                <div class="col s4">
                    Mata Uang
                </div>
                <div class="col s8">
                    {{ $data->currency->name }}
                </div>
                <div class="col s4">
                    Konversi
                </div>
                <div class="col s8">
                    {{ number_format($data->currency_rate,2,',','.').' '.$data->currency->code }}
                </div>
                <div class="col s4">
                    DP (%)
                </div>
                <div class="col s8">
                    {{ number_format($data->percent_dp,2,',','.') }} %
                </div>
            </div>
        </div>
        
        <div class="invoice-product-details mt-2" style="overflow:auto;">
            <table class="bordered">
                <thead>
                    <tr>
                        <th class="center-align">No.</th>
                        <th class="center-align">Item</th>
                        <th class="center-align">Qty</th>
                        <th class="center-align">Satuan</th>
                        <th class="center-align">Harga</th>
                        <th class="center-align">Margin</th>
                        <th class="center-align">Disc.1 (%)</th>
                        <th class="center-align">Disc.2 (%)</th>
                        <th class="center-align">Disc.3 (Rp)</th>
                        <th class="center-align">Other Fee</th>
                        <th class="center-align">Harga Final</th>
                        <th class="center-align">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data->marketingOrderDetail as $key => $row)
                    <tr>
                        <td class="center-align" rowspan="2">{{ ($key + 1) }}</td>
                        <td class="center-align">{{ $row->item->code.' - '.$row->item->name }}</td>
                        <td class="center-align">{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
                        <td class="center-align">{{ $row->itemUnit->unit->code }}</td>
                        <td class="right-align">{{ number_format($row->price,2,',','.') }}</td>
                        <td class="right-align">{{ number_format($row->margin,2,',','.') }}</td>
                        <td class="center-align">{{ number_format($row->percent_discount_1,2,',','.') }}</td>
                        <td class="center-align">{{ number_format($row->percent_discount_2,2,',','.') }}</td>
                        <td class="right-align">{{ number_format($row->discount_3,2,',','.') }}</td>
                        <td class="right-align">{{ number_format($row->other_fee,2,',','.') }}</td>
                        <td class="right-align">{{ number_format($row->price_after_discount,2,',','.') }}</td>
                        <td class="right-align">{{ number_format($row->total,2,',','.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="8">Keterangan: {{ $row->note }}</td>
                        <td colspan="4">Ambil dari: {{ $row->place->code.' - Gudang '.$row->warehouse->code.' - Area '.($row->area()->exists() ? $row->area->name : '-') 
                        }}</td>
                    </tr>
                    
                    @endforeach
                    <tr>
                        <td colspan="9" rowspan="8">
                            Rekening :
                            {!! $data->company->banks() !!}
                            <div class="mt-3">
                                Catatan Internal : {{ $data->note_internal }}
                            </div>
                            <div class="mt-3">
                                Catatan Eksternal : {{ $data->note_external }}
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
                        <td class="right-align" colspan="2">PPN</td>
                        <td class="right-align">{{ number_format($data->tax,2,',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="2">Total Stlh Pajak</td>
                        <td class="right-align">{{ number_format($data->total_after_tax,2,',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="2">Rounding</td>
                        <td class="right-align">{{ number_format($data->rounding,2,',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="2"><h6>Grandtotal</h6></td>
                        <td class="right-align"><h6>{{ number_format($data->grandtotal,2,',','.') }}</h6></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="12">Terbilang : <i>{{ CustomHelper::terbilangWithKoma($data->grandtotal).' '.ucwords(strtolower($data->currency->document_text)) }}</i></th>
                    </tr>
                </tfoot>
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