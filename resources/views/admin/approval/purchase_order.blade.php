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

    .preserveLines {
        white-space: pre-line;
    }
</style>
<div class="card">
    <div class="card-content invoice-print-area">
        <!-- header section -->
        <div class="row invoice-date-number">
            <div class="col xl4 s5">
                <span class="invoice-number mr-1">{{ __('translations.order') }} # {{ $data->code }}</span>
            </div>
            <div class="col xl8 s7">
                <div class="invoice-date display-flex align-items-right flex-wrap" style="right:0px !important;">
                    <div class="mr-2">
                        <small>{{ __('translations.submitted') }}:</small>
                        <span>{{ date('d/m/Y',strtotime($data->post_date)) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- logo and title -->
        <div class="row mt-1 invoice-logo-title">
            <div class="col m6 s12">
                <h5 class="indigo-text">{{ __('translations.purchase_order') }}</h5>
            </div>
            <div class="col m6 s12 right-align">
                <img src="{{ url('website/logo_web_fix.png') }}" width="35%">
            </div>
        </div>
        <div class="divider mb-1 mt-1"></div>
        <!-- invoice address and contact -->
        <table border="0" width="100%">
            <tr>
                <td width="33%" class="left-align">
                    <table border="0" width="100%">
                        <tr>
                            <td width="50%">
                                {{ __('translations.supplier') }}
                            </td>
                            <td width="50%">
                                {{ $data->supplier->name }}
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                {{ __('translations.address') }}
                            </td>
                            <td width="50%">
                                {{ $data->supplier->address }}
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                {{ __('translations.phone_number') }}
                            </td>
                            <td width="50%">
                                {{ $data->supplier->phone.' / '.$data->supplier->office_no }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="33%" class="left-align">
                    <table border="0" width="100%">
                        <tr>
                            <td width="50%">
                                {{ __('translations.receiver') }}
                            </td>
                            <td width="50%">
                                {{ $data->receiver_name }}
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                {{ __('translations.address') }}
                            </td>
                            <td width="50%">
                                {{ $data->receiver_address }}
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                {{ __('translations.contact') }}
                            </td>
                            <td width="50%">
                                {{ $data->receiver_phone }}
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                {{ __('translations.attachment') }}
                            </td>
                            <td width="50%">
                                {!! $data->attachments() !!}
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="33%" class="left-align">
                    <table border="0" width="100%">
                        <tr>
                            <td width="50%">
                                {{ __('translations.payment_type') }}
                            </td>
                            <td width="50%">
                                {{ $data->paymentType() }}
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                {{ __('translations.term') }}
                            </td>
                            <td width="50%">
                                {{ $data->payment_term }} hari
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                {{ __('translations.sent_date') }}
                            </td>
                            <td width="50%">
                                {{ date('d/m/Y',strtotime($data->delivery_date)) }}
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                {{ __('translations.shipping_type') }}
                            </td>
                            <td width="50%">
                                {{ $data->shippingType() }}
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
                        <th class="center-align">  {{ __('translations.no') }}.</th>
                        <th class="center-align">  {{ __('translations.item/service') }}</th>
                        <th class="center-align">  {{ __('translations.item_group') }}</th>
                        <th class="center-align">  {{ __('translations.stock_in_hand') }}</th>
                        <th class="center-align">  {{ __('translations.qty') }}</th>
                        <th class="center-align">  {{ __('translations.unit') }}</th>
                        <th class="center-align">  {{ __('translations.price') }}</th>
                        <th class="center-align">  {{ __('translations.disc') }}.1 (%)</th>
                        <th class="center-align">  {{ __('translations.disc') }}.2 (%)</th>
                        <th class="center-align">  {{ __('translations.disc') }}.3 (Rp)</th>
                        <th class="center-align">  {{ __('translations.subtotal') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data->purchaseOrderDetail as $key => $row)
                    <tr>
                        <td class="center-align" rowspan="4">{{ ($key + 1) }}</td>
                        <td class="center-align">{{ $row->item_id ? $row->item->code.' - '.$row->item->name : $row->coa->code.' - '.$row->coa->name }}</td>
                        <td class="center-align">{{ $row->item_id ? $row->item->itemGroup->name : '-' }}</td>
                        <td class="center-align">{{ CustomHelper::formatConditionalQty($row->qtyStock()) }}</td>
                        <td class="center-align">{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
                        <td class="center-align">{{ ($row->itemUnit()->exists() ? $row->itemUnit->unit->code : ($row->coaUnit()->exists() ? $row->coaUnit->code : '-')) }}</td>
                        <td class="right-align">{{ number_format($row->price,2,',','.') }}</td>
                        <td class="center-align">{{ number_format($row->percent_discount_1,2,',','.') }}</td>
                        <td class="center-align">{{ number_format($row->percent_discount_2,2,',','.') }}</td>
                        <td class="right-align">{{ number_format($row->discount_3,2,',','.') }}</td>
                        <td class="right-align">{{ number_format($row->subtotal,2,',','.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="11">  {{ __('translations.note') }} 1: {{ $row->note }}</td>
                    </tr>
                    <tr>
                        <td colspan="11">  {{ __('translations.note') }} 2: {{ $row->note2 }}</td>
                    </tr>
                    <tr>
                        <td colspan="11">  {{ __('translations.note') }} 3: {{ $row->note3 }}</td>
                    </tr>
                    <tr>
                        <td colspan="11">  {{ __('translations.reference') }}: {{ $row->purchaseRequestDetail()->exists() ? $row->purchaseRequestDetail->purchaseRequest->code : '-' }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="8" rowspan="8">
                            {{ __('translations.bank_account') }} :
                            {{ $data->supplier->defaultBank() ? $data->supplier->defaultBank() : ' - ' }}
                            <div class="mt-3">
                                {{ __('translations.note') }} : {{ $data->note }}
                            </div>
                            <div class="preserveLines mt-2" style="text-align:left !important;">
                                {{ __('translations.external_notes') }} : {{ $data->note_external }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="2">  {{ __('translations.subtotal') }}</td>
                        <td class="right-align">{{ number_format($data->subtotal,2,',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="2">  {{ __('translations.disc') }}</td>
                        <td class="right-align">{{ number_format($data->discount,2,',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="2">  {{ __('translations.total') }}</td>
                        <td class="right-align">{{ number_format($data->total,2,',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="2">  {{ __('translations.tax') }}</td>
                        <td class="right-align">{{ number_format($data->tax,2,',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="2">  {{ __('translations.wtax') }}</td>
                        <td class="right-align">{{ number_format($data->wtax,2,',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="2">  {{ __('translations.rounding') }}</td>
                        <td class="right-align">{{ number_format($data->rounding,2,',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="2"><h6>  {{ __('translations.grandtotal') }}</h6></td>
                        <td class="right-align"><h6>{{ number_format($data->grandtotal,2,',','.') }}</h6></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="11">  {{ __('translations.regarded') }} : <i>{{ CustomHelper::terbilangWithKoma($data->grandtotal).' '.ucwords(strtolower($data->currency->document_text)) }}</i></th>
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
                        <div >  {{ __('translations.created_by') }}, {{ $data->user->name }} {{ $data->user->position()->exists() ? $data->user->position->name : '-' }} {{ ($data->post_date ? \Carbon\Carbon::parse($data->updated_at)->format('d/m/Y H:i:s') : '-') }}</div></div>
                    </td>
                </tr>
                    @if($data->approval())
                        @foreach ($data->approval() as $detail)
                            @foreach ($detail->approvalMatrix()->where('status','2')->get() as $row)
                            <tr>    
                                <td>
                                        
                                        
                                        <div>{{ $row->approvalTemplateStage->approvalStage->approval->document_text }}
                                            @if ($row->approvalTemplateStage->approvalStage->approval->document_text == 'Dicek oleh,' &&  app()->getLocale() == 'chi')
                                            <br>
                                                通过检查
                                            @elseif ($row->approvalTemplateStage->approvalStage->approval->document_text == 'Disetujui oleh,'  &&  app()->getLocale() == 'chi')
                                            <br>
                                                由...批准,
                                            @endif
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