@php
    use App\Helpers\CustomHelper;

@endphp
<!doctype html>
<html lang="en">
    <head>
        <style>
            html
            {
                font-family: Tahoma, "Trebuchet MS", sans-serif;
            }

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
                margin-right: 60%;
                float: left;
                width: 50%;
                padding: 5px;
            }

            .row::after {
            content: "";
            clear: both;
            display: table;
            }

            

            @media only screen and (max-width : 768px) {
                .invoice-print-area {
                    zoom:0.6;
                }
            }
        
            @media only screen and (max-width : 992px) {
                .invoice-print-area {
                    zoom:0.8;
                    font-size:11px !important;
                }

                table > thead > tr > th {
                    font-size:13px !important;
                    font-weight: 800 !important;
                }
                td{
                    font-size:0.9em !important;
                }
                .tb-header td{
                    font-size:0.8em !important;
                }
                .tbl-info td{
                    font-size:1em !important;
                }
                .table-data-item td{
                    font-size:1em !important;
                }
                .table-data-item th{
                    border:0.6px solid black;
                }
                .table-bot td{
                    font-size:0.8em !important;
                }
                .table-bot1 td{
                    font-size:0.9em !important;
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

            @page { margin: 6em 3em 6em 3em; }
            header { position: fixed; top: -95px; left: 0px; right: 0px; height: 150px; margin-bottom: 10em }
                
            td {
                vertical-align: top !important;
            }
           
            .mt-5 {
                margin-top:35px;
            }
        </style>
    </head>
    <body>
        <header style="margin-top:20px;">
            <table border="0" width="100%" style="font-size:1em" class="tb-header">
                <tr>
                    <td width="33%" class="left-align" >
                        <span class="invoice-number mr-1"># {{ $data->code }}</span>
                        <small>Diajukan:{{ date('d/m/Y',strtotime($data->post_date)) }}</small>
                        <h2 class="indigo-text">Tiket Timbangan</h2>
                    </td>
                    <td width="33%" class="right-align">
                    </td>
                    <td width="34%" class="right-align">
                        <img src="{{ $image }}" width="50%" style="position: absolute; top:15px; width:20%;right:0;">
                    </td>
                </tr>
                
            </table>
            <hr style="border-top: 1px solid black; margin-top:-10px">
        </header>
        <main style="margin-top:20px;">
            <div class="card">
                <div class="card-content invoice-print-area ">
                    <table border="0" width="100%">
                        <tr>
                            <td width="40%" class="left-align">
                                <table border="0" width="100%" class="tbl-info">
                                    <tr>
                                        <td width="25%">
                                            Name
                                        </td>
                                        <td width="50%">
                                            {{ $data->user->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="25%">
                                            Posisi
                                        </td>
                                        <td width="50%">
                                            {{ $data->user->position_id ? $data->user->position->Level->name : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="25%">
                                            Depart.
                                        </td>
                                        <td width="50%">
                                            {{ $data->user->position_id ? $data->user->position->division->name : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="25%">
                                            HP
                                        </td>
                                        <td width="50%">
                                            {{ $data->user->phone }}
                                        </td>
                                    </tr>
                                    @if($data->item->is_hide_supplier)

                                    @else
                                        <tr>
                                            <td width="25%">
                                                Supplier
                                            </td>
                                            <td width="50%">
                                                {{ $data->account->name }}
                                            </td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td>
                                           Plant
                                        </td>
                                        <td>
                                            {{ $data->place->name }}
                                         </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            NO.SJ
                                         </td>
                                         <td>
                                             {{ $data->delivery_no }}
                                          </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            No.Kendaraan
                                         </td>
                                         <td>
                                             {{ $data->vehicle_no }}
                                          </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Nama Supir
                                         </td>
                                         <td>
                                             {{ $data->driver }}
                                          </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Nomor PO
                                         </td>
                                         <td>
                                             {{ $data->purchaseOrderDetail->purchaseOrder->code }}
                                          </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="40%" class="left-align">
                                <table border="0" width="100%" class="tbl-info">
                                    <tr>
                                        <td>
                                            Item
                                        </td>
                                        <td>
                                             {{ $data->item->code.' - '.$data->item->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Berat Bruto
                                        </td>
                                        <td>
                                             {{ CustomHelper::formatConditionalQty($data->qty_in) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Waktu Timbang Masuk
                                        </td>
                                        <td>
                                             {{ $data->time_scale_in ? date('d/m/Y H:i:s',strtotime($data->time_scale_in)) : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Berat Tara
                                        </td>
                                        <td>
                                             {{ CustomHelper::formatConditionalQty($data->qty_out) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Waktu Timbang Keluar
                                        </td>
                                        <td>
                                             {{ $data->time_scale_out ? date('d/m/Y H:i:s',strtotime($data->time_scale_out)) : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Berat Netto
                                        </td>
                                        <td>
                                             {{ CustomHelper::formatConditionalQty($data->qty_balance) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Satuan
                                        </td>
                                        <td>
                                             {{ $data->itemUnit->unit->code }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Status QC
                                        </td>
                                        <td>
                                             {{ $data->statusQcRaw() }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Keterangan QC
                                        </td>
                                        <td>
                                             {{ $data->note_qc }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <!-- product details table-->
                    {{-- <div class="invoice-subtotal break-row">
                        HASIL PEMERIKSAAN QC
                        <table class="bordered table-with-breaks table-data-item " border="1" style="border-collapse:collapse;font-size:9px !important;" width="75%">
                            <thead>
                                <tr>
                                    <th class="center">No</th>
                                    <th class="center">Nama</th>
                                    <th class="center">Nominal</th>
                                    <th class="center">Satuan</th>
                                    <th class="center">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($data->qualityControl()->exists())
                                    @foreach($data->qualityControl as $keydetail => $rowdetail)
                                    <tr>
                                        <td align="center">{{ ($keydetail + 1) }}</td>
                                        <td>{{ $rowdetail->name }}</td>
                                        <td align="right">{{ CustomHelper::formatConditionalQty($rowdetail->nominal) }}</td>
                                        <td align="center">{{ $rowdetail->unit }}</td>
                                        <td>{{ $rowdetail->note }}</td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5">DATA TIDAK DITEMUKAN</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div> --}}
                    <!-- invoice subtotal -->
                    <div class="invoice-subtotal break-row">
                        <div class="row">
                        <div class="column1">
                            <table style="width:100%">
                                <tr class="break-row">
                                    <td>
                                        <div class="mt-3">
                                            Catatan : {{ $data->note }}
                                        </div>
                                    </td>
                                    
                                </tr>
                            </table>
                        </div>
                        <div class="column2">
                        </div>
                        </div>
                        <table class="table-bot1" width="100%" border="0">
                            <tr>
                                <td class="center-align">
                                    {!! ucwords(strtolower($data->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
                                    <br>
                                    Dibuat oleh,
                                    @if($data->user->signature)
                                        <div>{!! $data->user->signature() !!}</div>
                                    @endif
                                    <div class="{{ $data->user->signature ? '' : 'mt-5' }}">{{ $data->user->name }}</div>
                                    <div class="mt-1">{{ $data->user->position()->exists() ? $data->user->position->Level->name.' - '.$data->user->position->division->name : '-' }}</div>
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
                                <td class="center-align">
                                    <br>
                                    <br>
                                    Supir,
                                    <div class="mt-5">{{ $data->driver }}</div>
                                </td>
                            </tr>
                        </table>  
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>