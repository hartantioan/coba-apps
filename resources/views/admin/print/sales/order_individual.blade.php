@php
    use App\Helpers\CustomHelper;
@endphp
<!doctype html>
<html lang="en">
    <head>
        <style>

            .break-row {
                page-break-inside: avoid;
            }

            .row {
            margin-left:-5px;
            margin-right:-5px;
            }
            
            .column1 {
            float: left;
            width: 50%;
            padding: 5px;
            }
            .column2 {
                margin-left: 10%;
                float: left;
                width: 50%;
                padding: 5px;
            }

            /* Clearfix (clear floats) */
            .row::after {
                content: "";
                clear: both;
                display: table;
            }

            td {
                vertical-align:top !important;
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
                td{
                    font-size:1em !important;
              
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
            
            .invoice-product-details{
                border:1px solid black;
                min-height: auto;
            }

            @page { margin: 5em 3em 6em 3em; }
            header { position: fixed; top: -70px; left: 0px; right: 0px; height: 150px; margin-bottom: 10em }
                
        
           
        </style>
    </head>
    <body>
        <header>
            <table border="0" width="100%">
                <tr>
                    <td width="83%" class="left-align">
                        <tr>
                            <td align="center">
                                <span class="invoice-number mr-1" style="font-size:15px;font-weight:800;margin-bottom:0px">
                                    {{ $data->code }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="margin-top: -2px;">
                                <small style="font-size:10px">Diajukan:</small>
                                <span style="font-size:10px;">{{ date('d/m/Y',strtotime($data->post_date)) }}</span>
                                <small style="font-size:10px">Valid Hingga:</small>
                                <span style="font-size:10px;">{{ date('d/m/Y',strtotime($data->valid_date)) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h5 style="margin-top: -2px">Sales Order</h5>
                            </td>
                        </tr>
                    </td>
                    <td width="33%" class="right-align">
                    </td>
                    
                    <td width="34%" class="right-align">
                        <img src="{{ $image }}" width="50%" style="position: absolute; top:5px; width:20%">
                        <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($data->code, 'C128')}}" alt="barcode" style="position: absolute; top:50px;width:100px;right:75px;" height="10%" />
                    </td>
                </tr>
                
            </table>
        </header>
        <main>
            <div class="card">
                <div class="card-content invoice-print-area">
                    <!-- header section -->
                    <table border="0" width="100%">
                        <tr>
                            <td width="30%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="40%">
                                            Customer
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="60%">
                                            {{ $data->account->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Alamat
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ $data->account->address }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Telepon
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ $data->account->phone.' / '.$data->account->office_no }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Tipe Penjualan
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ $data->type() }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="25%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="50%">
                                            Tipe Pengiriman
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="50%">
                                            {{ $data->deliveryType() }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Broker
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ $data->sender->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Tgl.Kirim
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ date('d/m/Y',strtotime($data->delivery_date)) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            DP (%)
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ number_format($data->percent_dp,2,',','.') }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="45%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="30%">
                                            Tipe Transport
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="70%">
                                            {{ $data->transportation->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Almt Penagihan
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ $data->billing_address }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="30%">
                                            Outlet
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="70%">
                                            {{ $data->outlet->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Almt Tujuan
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ $data->destination_address.', '.ucwords(strtolower($data->subdistrict->name.' - '.$data->district->name.' - '.$data->city->name.' - '.$data->province->name)) }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="invoice-product-details mt-2" style="overflow:auto;">
                        <table border="1" style="border-collapse:collapse" width="100%">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Satuan</th>
                                    <th>Harga</th>
                                    <th>Diskon 1</th>
                                    <th>Diskon 2</th>
                                    <th>Diskon 3</th>
                                    <th>Harga Final</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data->marketingOrderDetail as $key => $row)
                                <tr>
                                    <td align="center">{{ ($key + 1) }}</td>
                                    <td>{{ $row->item->code.' - '.$row->item->name }}</td>
                                    <td align="right">{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
                                    <td align="center">{{ $row->item->sellUnit->code }}</td>
                                    <td align="right">{{ number_format($row->price - $row->margin,2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->percent_discount_1,2,',','.') }}%</td>
                                    <td align="right">{{ number_format($row->percent_discount_2,2,',','.') }}%</td>
                                    <td align="right">{{ number_format($row->discount_3,2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->price_after_discount,2,',','.') }}</td>
                                    <td align="right">{{ number_format($row->total,2,',','.') }}</td>
                                </tr>
                                <tr>
                                    <td colspan="10">Keterangan: {{ $row->note }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- invoice subtotal -->
                    <div class="invoice-subtotal break-row">
                        <div class="row">
                        <div class="column1">
                            <table style="width:100%">
                                <tr class="break-row">
                                    <td>
                                        Rekening :<br>
                                        {!! $data->company->banks() !!}
                                        <div class="mt-3">
                                            Catatan Internal : {{ $data->note_internal }}
                                        </div>
                                        <div class="mt-3">
                                            Catatan Eksternal : {{ $data->note_external }}
                                        </div>
                                        Terbilang : <i>{{ CustomHelper::terbilangWithKoma($data->grandtotal).' '.$data->currency->document_text }}
                                    </td>
                                    
                                </tr>
                            </table>
                        </div>
                        <div class="column2">
                            <table style="border-collapse:collapse;" width="74%">
                                {{-- <tr class="break-row">
                                    <td class="right-align">Subtotal</td>
                                    <td class="right-align" align="right" style="border:0.6px solid black;">{{ number_format($data->subtotal,2,',','.') }}</td>
                                </tr>
                                <tr class="break-row">
                                    <td class="right-align">Diskon</td>
                                    <td class="right-align" align="right" style="border:0.6px solid black;">{{ number_format($data->discount,2,',','.') }}</td>
                                </tr class="break-row"> --}}
                                <tr>
                                    <td class="right-align">Total</td>
                                    <td class="right-align" align="right" style="border:0.6px solid black;">{{ number_format($data->total,2,',','.') }}</td>
                                </tr>
                                <tr class="break-row">
                                    <td class="right-align">PPN</td>
                                    <td class="right-align" align="right" style="border:0.6px solid black;">{{ number_format($data->tax,2,',','.') }}</td>
                                </tr>
                                {{-- <tr class="break-row">
                                    <td class="right-align">Total Stlh PPN</td>
                                    <td class="right-align" align="right" style="border:0.6px solid black;">{{ number_format($data->total_after_tax,2,',','.') }}</td>
                                </tr> --}}
                                <tr class="break-row">
                                    <td class="right-align">Rounding</td>
                                    <td class="right-align" align="right" style="border:0.6px solid black;">{{ number_format($data->rounding,2,',','.') }}</td>
                                </tr>
                                <tr class="break-row">
                                    <td class="right-align">Grandtotal</td>
                                    <td class="right-align" align="right" style="border:0.6px solid black;">{{ number_format($data->grandtotal,2,',','.') }}</td>
                                </tr>
                            </table>
                        </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                {!! ucwords(strtolower($data->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
                            </div>
                            <div class="col">
                                
                            </div>
                        </div>
                        <table class="mt-3" width="100%" border="0">
                            <tr>
                                <td>
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
                                            <td>
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
        </main>
       
    </body>
    
    
</html>