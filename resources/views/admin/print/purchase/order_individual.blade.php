@php
    use App\Helpers\CustomHelper;
    use Carbon\Carbon;
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
                vertical-align: top;
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
            header { position: fixed; top: -60px; left: 0px; right: 0px; height: 100px; margin-bottom: 10em }
                
            .preserveLines {
                white-space: pre-line;
            }
           
        </style>
    </head>
    <body>
        <header>
            <table border="0" width="100%">
                <tr>
                    <td width="33%" class="left-align">
                        <span class="invoice-number mr-1" style="font-size:10px;margin-bottom:0px"># {{ $data->code }}</span>
                    </td>
                    <td width="33%" align="center">
                        <h2 style="margin-top: 5px">Purchase Order</h2>
                    </td>
                    <td width="34%" class="right-align">
                        <img src="{{ $image }}" width="50%" style="position: absolute; top:5px; width:20%;right:0px;">
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
                            <td width="38%" style="vertical-align:top;">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="30%" style="vertical-align:top;">
                                            Supplier
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ $data->supplier->name ?? ''}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align:top;">
                                            Alamat
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ $data->supplier->address ?? '' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align:top;">
                                            Telepon
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ $data->supplier->phone ?? '' .' / '.$data->supplier->office_no ?? '' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align:top;">
                                            NO. NPWP
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ $data->supplier->tax_id ? $data->supplier->tax_id : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align:top;">
                                            Tipe Bayar
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ $data->paymentType() }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align:top;">
                                            Termin
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ $data->payment_term }} hari
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="37%" style="vertical-align:top;">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="30%" style="vertical-align:top;">
                                            Penerima
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ $data->receiver_name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align:top;">
                                            Alamat
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ $data->receiver_address }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align:top;">
                                            Kontak
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ $data->receiver_phone }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align:top;">
                                            Tgl. Kirim
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ date('d/m/Y',strtotime($data->delivery_date)) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align:top;">
                                            Pengiriman
                                        </td>
                                        <td width="1%">:</td>
                                        <td>
                                            {{ $data->shippingType() }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="25%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td align="center">
                                            <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($data->code, 'C128')}}" alt="barcode" style="width:80%;" height="2%" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center">
                                            <h3>{{ $data->code }}</h3>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <div class="invoice-product-details mt-2">
                        <table class="bordered table-with-breaks" border="1" style="border-collapse:collapse;" width="100%"  >
                            <thead style="border-collapse:collapse;border:1px solid black;">
                                <tr>
                                    <th class="center-align">No.</th>
                                    <th class="center-align">Item/Jasa</th>
                                    <th class="center-align">Qty</th>
                                    <th class="center-align">Satuan</th>
                                    <th class="center-align">Harga</th>
                                    <th class="center-align">Disc.1 (%)</th>
                                    <th class="center-align">Disc.2 (%)</th>
                                    <th class="center-align">Disc.3 (Rp)</th>
                                    <th class="center-align">Subtotal</th>
                                </tr>
                            </thead>
                            
                            <tbody id="bodybros">
                                @foreach($data->purchaseOrderDetail as $key => $row)
                                    @if($row->item()->exists())
                                        <tr>
                                            <td align="center" rowspan="3">{{ ($key + 1) }}</td>
                                            <td align="left">{{ $row->item->code.' - '.$row->item->name.' - '.$row->item->other_name }}</td>
                                            <td align="center">{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
                                            <td align="center">{{ $row->itemUnit()->exists() ? $row->itemUnit->unit->code : '-' }}</td>
                                            <td align="right">{{ number_format($row->price,2,',','.') }}</td>
                                            <td align="center">{{ number_format($row->percent_discount_1,2,',','.') }}</td>
                                            <td align="center">{{ number_format($row->percent_discount_2,2,',','.') }}</td>
                                            <td align="right">{{ number_format($row->discount_3,2,',','.') }}</td>
                                            <td align="right">{{ number_format($row->subtotal,2,',','.') }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="8">Keterangan 1: {{ $row->note }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="8">Keterangan 2: {{ $row->note2 }}</td>
                                        </tr>
                                    @elseif($row->coa()->exists())
                                        <tr>
                                            <td align="center" rowspan="2">{{ ($key + 1) }}</td>
                                            <td align="left">{{ $row->note }}</td>
                                            <td align="center">{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
                                            <td align="center">{{ $row->coaUnit()->exists() ? $row->coaUnit->code : '-' }}</td>
                                            <td align="right">{{ number_format($row->price,2,',','.') }}</td>
                                            <td align="center">{{ number_format($row->percent_discount_1,2,',','.') }}</td>
                                            <td align="center">{{ number_format($row->percent_discount_2,2,',','.') }}</td>
                                            <td align="right">{{ number_format($row->discount_3,2,',','.') }}</td>
                                            <td align="right">{{ number_format($row->subtotal,2,',','.') }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="8">Keterangan : {{ $row->note2 }}</td>
                                        </tr>
                                    @endif
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
                                        Rekening :
                                        {{ $data->supplier->defaultBank() ? $data->supplier->defaultBank() : ' - ' }}
                                        <div class="mt-3">
                                            Catatan : {{ $data->note }}
                                        </div>
                                        <div class="preserveLines" style="text-align:left !important;">
                                            {{ $data->note_external }}
                                        </div>
                                        Terbilang : <i>{{ CustomHelper::terbilangWithKoma($data->grandtotal).' '.ucwords(strtolower($data->currency->document_text)) }}
                                    </td>
                                    
                                </tr>
                            </table>
                        </div>
                        <div class="column2">
                            <table style="border-collapse:collapse;" width="74%">
                                <tr class="break-row">
                                    <td class="right-align">Subtotal</td>
                                    <td class="right-align" align="right" style="border:0.6px solid black;">{{ number_format($data->subtotal,2,',','.') }}</td>
                                </tr>
                                <tr class="break-row">
                                    <td class="right-align">Diskon</td>
                                    <td class="right-align" align="right" style="border:0.6px solid black;">{{ number_format($data->discount,2,',','.') }}</td>
                                </tr class="break-row">
                                <tr>
                                    <td class="right-align">Total</td>
                                    <td class="right-align" align="right" style="border:0.6px solid black;">{{ number_format($data->total,2,',','.') }}</td>
                                </tr class="break-row">
                                <tr class="break-row">
                                    <td class="right-align">PPN</td>
                                    <td class="right-align" align="right" style="border:0.6px solid black;">{{ number_format($data->tax,2,',','.') }}</td>
                                </tr>
                                <tr class="break-row">
                                    <td class="right-align">PPh</td>
                                    <td class="right-align" align="right" style="border:0.6px solid black;">{{ number_format($data->wtax,2,',','.') }}</td>
                                </tr>
                                <tr class="break-row">
                                    <td class="right-align">Pembulatan</td>
                                    <td class="right-align" align="right" style="border:0.6px solid black;">{{ number_format($data->rounding,2,',','.') }}</td>
                                </tr>
                                <tr class="break-row">
                                    <td class="right-align">Grandtotal</td>
                                    <td class="right-align" align="right" style="border:0.6px solid black;">{{ number_format($data->grandtotal,2,',','.') }}</td>
                                </tr>
                            </table>
                        </div>
                        </div>
                        
                    </div>
                    <div class="invoice-subtotal break-row">
                        Remark :
                        <ol>
                            <li>Harap cantumkan Nomor PO di dokumen DO atau Surat Jalan.</li>
                            <li>Penjual harus menandatangani PO ini paling lambat 3 (tiga) hari kerja dari tanggal PO.</li>
                            <li>Pihak supplier tidak diperbolehkan memberikan uang dan/atau hadiah dalam bentuk apapun kepada karyawan/staff {{ $data->company->name }}. Jika melanggar akan diproses secara hukum dan seluruh sisa tagihan dianggap lunas.</li>
                        </ol>
                    </div>
                    <table width="100%">
                        <tr>
                            <td style="vertical-align:top;" width="25%">
                                Dibuat oleh, {{ $data->user->name }}
                            </td>
                            <td style="vertical-align:top;" width="25%">
                                Tgl : {{ date('d/m/Y') }}
                            </td>
                            <td style="vertical-align:top;" width="25%">
                                TTD : 
                                @if($data->user->signature)
                                    <span style="float: right;margin-right:50px;margin-top:-15px;">{!! $data->user->signature() !!}</span>
                                @else
                                    _______________
                                @endif
                            </td>
                            <td style="vertical-align:top;" width="25%">
                                Supplier : _______________
                            </td>
                        </tr>
                    </table>
                    <table class="table-bot1" width="100%" border="0">
                            
                            @if($data->approval())
                                @foreach ($data->approval() as $detail)
                                    @foreach ($detail->approvalMatrix()->where('status','2')->get() as $row)
                                    <tr>
                                        <td class="center-align" width="100%">
                                            @php
                                                $carbonInstance = Carbon::parse($row->updated_at);
                                                $dayName = $carbonInstance->format('l');
                                                $hour = $carbonInstance->hour;
                                                $minute = $carbonInstance->minute;
                                                $date = $carbonInstance->format('d/m/Y');
                                                $formattedHour = sprintf('%02d', $hour);
                                                $formattedMinute = sprintf('%02d', $minute);
                                            @endphp
                                            <div>{{ $row->approvalTemplateStage->approvalStage->approval->document_text }} <span style="font-weight: bold">{{ $row->user->name }}</span> Hari <span style="font-weight: bold">{{CustomHelper::hariIndo($dayName)}}</span>  tanggal <span style="font-weight: bold">{{$date}}</span>  jam <span style="font-weight: bold">{{$formattedHour}}:{{$formattedMinute}}</span></div>
                                      
                                        </td>
                                    </tr>
                                    @endforeach
                                @endforeach
                            @endif
                        
                    </table>
                </div>
            </div>
        </main>
       
    </body>
    
    
</html>