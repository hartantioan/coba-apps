@php
    use App\Helpers\CustomHelper;

@endphp
<!doctype html>
<html lang="en">
    <head>
        <style>

            @font-face { font-family: 'china'; font-style: normal; src: url({{ storage_path('fonts/chinese_letter.ttf') }}) format('truetype'); }
            body { font-family: 'china', Tahoma, Arial, sans-serif;}
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
                
            #table-info > tbody > tr > td {
                padding: 5px;
            }
           
        </style>
    </head>
    <body>
        <header>
            <table border="0" width="100%">
                <tr>
                    <td width="34%">
                        <img src="{{ $image }}" width="50%" style="top:5px; width:80%">
                    </td>
                    <td width="33%" align="center">
                        <h5 style="margin-top:0px;">DELIVERY ORDER</h5>
                        <h5 style="margin-top:-15px;">{{ $data->code }}</h5>
                        <div>
                            <small style="font-size:10px">Tanggal:</small>
                            <span style="font-size:10px;">{{ date('d/m/Y',strtotime($data->post_date)) }}</span>
                        </div>
                    </td>
                    <td width="33%">
                        {{-- <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($data->code, 'C128')}}" alt="barcode" style="top:50px;width:100%;right:75px;" height="" />
                        <tr>
                            <td style="margin-top: -2px;">
                                <small style="font-size:10px">Tanggal:</small>
                                <span style="font-size:10px;">{{ date('d/m/Y',strtotime($data->post_date)) }}</span>
                            </td>
                        </tr> --}}
                    </td>
                </tr>
            </table>
        </header>
        <main style="margin-top:25px;">
            @if ($data->marketingOrderDelivery->so_type == '4')
                <div style="position:absolute;top:30%;left:35%;width:150px;height:50px;padding:15px 15px 15px 15px;font-size:12px;text-align:center;border:1px solid black;border-radius:15px;opacity: 0.5;">
                    SAMPLE
                    <hr>
                    TIDAK UNTUK DIJUAL
                </div>
            @endif
            <div class="card">
                <div class="card-content invoice-print-area">
                    <!-- header section -->
                    <table border="0" width="100%">
                        <tr>
                            <td width="50%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="35%">
                                            PLANT
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="65%">
                                            {{ $data->getPlace() }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="35%">
                                            WAREHOUSE
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="65%">
                                            {{ $data->getWarehouse() }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="35%">
                                            NO. MOD
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="65%">
                                            {{ $data->marketingOrderDelivery->code }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="35%">
                                            NAMA EKSPEDISI
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="65%">
                                            {{ $data->account->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="35%">
                                            SHIPPING TYPE
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="65%">
                                            {{ $data->marketingOrderDelivery->deliveryType() }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="35%">
                                            JENIS TRANSPORT
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="65%">
                                            {{ $data->marketingOrderDelivery->transportation->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="35%">
                                            NOMOR POLISI
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="65%">
                                            {{ $data->vehicle_no }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="35%">
                                            NAMA SOPIR
                                        </td>
                                        <td width="1%">:</td>
                                        <td width=65%">
                                            {{ $data->driver_name }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="50%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="30%">
                                            PO CUSTOMER
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="70%">
                                            {{ $data->getPoCustomer() }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="30%">
                                            CUSTOMER
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="70%">
                                            {{ $data->marketingOrderDelivery->customer->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="30%">
                                            OUTLET NAME
                                        </td>
                                        <td width="1%">:</td>
                                        <td width="70%">
                                            {{ strtoupper($data->getOutlet()) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            PROJECT NAME
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ strtoupper($data->getProject()) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            ALAMAT
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ strtoupper($data->marketingOrderDelivery->destination_address) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            KECAMATAN
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ strtoupper($data->marketingOrderDelivery->district->name) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            KOTA
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ strtoupper($data->marketingOrderDelivery->city->name) }}
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
                                    <th>NAMA BARANG</th>
                                    <th>BATCH</th>
                                    <th>QTY</th>
                                    <th>UOM</th>
                                    <th>QTY PALET</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data->marketingOrderDeliveryProcessDetail as $key => $row)
                                <tr>
                                    <td>{{ $row->itemStock->item->code.' - '.$row->itemStock->item->name }}</td>
                                    <td></td>
                                    <td align="right">{{ CustomHelper::formatConditionalQty(round($row->qty * $row->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion,3)) }}</td>
                                    <td align="center">{{ $row->itemStock->item->uomUnit->code }}</td>
                                    <td align="right">{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
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
                                            <div class="mt-3">
                                                Catatan : {{ $data->note_external }}
                                            </div>
                                        </td>
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
                                    <div class="mt-1">{{ $data->user->position()->exists() ? $data->user->position->Level->name.' - '.$data->user->position->division->name : '-' }}</div>
                                </td>
                                <td width="">
                                    Supir,
                                    <div style="margin-top:50px;">{{ $data->driver_name }}</div>
                                </td>
                                <td width="">
                                    Customer,
                                    <div style="margin-top:50px;">...............</div>
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
                    <div class="invoice-subtotal break-row">
                        <table border="1" style="border-collapse:collapse;font-size:8px;" id="table-info" width="100%">
                            <tbody>
                                <tr>
                                    <td width="50%">Komplain harap menyertakan bukti foto dan/atau video sebelum saat proses bongkar.</td>
                                    <td width="50%">Dicetak pada : {{ date('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td width="100%" colspan="2">
                                        Penerima membubuhkan tanda tangan dan stempel setelah : <br>
                                        1. Mengetahui dan memeriksa barang yang diterima sesuai betul yang dicantumkan di Surat Jalan, baik jenis maupun jumlahnya.<br>
                                        2. Mengetahui dan menyetujui bahwa barang yang belum lunas, masih merupakan milik PENJUAL, sehingga tidak keberatan apabila sewaktu-waktu ditarik kembali.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>