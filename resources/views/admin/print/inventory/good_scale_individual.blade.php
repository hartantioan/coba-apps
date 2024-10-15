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
                    font-size:10px !important;
                }


                td{
                    font-size:0.9em !important;
                }
                .tb-header td{
                    font-size:1.2em !important;
                }
                .tbl-info td{
                    font-size:10.5px !important;
                }
                .table-data-item td{
                    font-size:1.001em !important;
                }
                .table-data-item th{
                    border:0.6px solid black;
                }
                .table-bot td{
                    font-size:0.9em !important;
                }
                .table-bot1 td{
                    font-size:0.9em !important;
                }
            }

            @media print {
                .invoice-print-area {
                    font-size:200px !important;
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

            @page { margin: 6em 3em 3em 3em; }
            header { position: fixed; top: -95px; left: 0px; right: 0px; height: 150px; margin-bottom: 10em }

            td {
                vertical-align: top !important;
            }

            .mt-5 {
                margin-top:30px;
            }
            .mt-2 {
                margin-top:10px;
            }
        </style>
    </head>
    <body>
        <header style="margin-top:35px;">
            <table border="0" width="100%" style="font-size:0.8em" class="tb-header">
                <tr>
                    <td width="66%" class="left-align" style="padding-top:15px;">
                        <span class="invoice-number mr-1">Tiket Timbangan # {{ $data->code }}</span>
                        <br><span>Diajukan:{{ date('d/m/Y',strtotime($data->post_date)) }} | Tipe : {{ $data->type() }}</span>
                        <h4 class="indigo-text"></h4>
                    </td>
                    <td width="34%" class="right-align">
                        <img src="{{ $image }}" width="50%" style="position: absolute; top:15px; width:20%;right:0;">
                    </td>
                </tr>

            </table>
            <hr style="border-top: 1px solid black; margin-top:-35px">
        </header>
        <main style="margin-top:15px;">
            <div class="card">
                <div class="card-content invoice-print-area ">
                    <table border="0" width="100%">
                        <tr>
                            <td width="40%" class="left-align">
                                <table border="0" width="100%" class="tbl-info">
                                    <tr>
                                        <td width="25%" style="font-size: 13px !important">
                                            Name
                                        </td>
                                        <td width="50%" style="font-size: 13px !important">
                                            {{ $data->user->name }}
                                        </td>
                                    </tr>
                                    @if($data->item()->exists() && $data->item->is_hide_supplier)

                                    @else
                                        <tr>
                                            <td width="25%" style="font-size: 13px !important">
                                                Supplier
                                            </td>
                                            <td width="50%" style="font-size: 13px !important">
                                                {{ $data->account()->exists() ? $data->account->name : '-' }}
                                            </td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td style="font-size: 13px !important">
                                           Plant
                                        </td>
                                        <td style="font-size: 13px !important">
                                            {{ $data->place->name }}
                                         </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 13px !important">
                                            NO.SJ
                                         </td>
                                         <td style="font-size: 13px !important">
                                             {{ $data->delivery_no }}
                                          </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 13px !important">
                                            No.Kendaraan
                                         </td>
                                         <td style="font-size: 13px !important">
                                             {{ $data->vehicle_no }}
                                          </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 13px !important">
                                            Nama Supir
                                         </td>
                                         <td style="font-size: 13px !important">
                                             {{ $data->driver }}
                                          </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 13px !important">
                                            Nomor PO/MOD
                                         </td>
                                         <td style="font-size: 13px !important">
                                            {{ $data->referencePO() }}
                                          </td>
                                    </tr>
                                    {{-- <tr>
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
                                    </tr> --}}
                                </table>
                            </td>
                            <td width="40%" class="left-align">
                                <table border="0" width="100%" class="tbl-info">
                                    <tr>
                                        <td width="40%" style="font-size: 13px !important">
                                            Item
                                        </td>
                                        <td width="60%" style="font-size: 13px !important">
                                             {{ $data->item()->exists() ? $data->item->code.' - '.$data->item->name : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 13px !important">
                                            Satuan
                                        </td>
                                        <td style="font-size: 13px !important">
                                             {{ $data->itemUnit()->exists() ? $data->itemUnit->unit->code : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 13px !important">
                                            Berat Bruto
                                        </td>
                                        <td style="font-size: 13px !important">
                                             {{ CustomHelper::formatConditionalQty($data->qty_in) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 13px !important">
                                            Waktu Timbang Masuk
                                        </td>
                                        <td style="font-size: 13px !important">
                                             {{ $data->time_scale_in ? date('d/m/Y H:i:s',strtotime($data->time_scale_in)) : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 13px !important">
                                            Berat Tara
                                        </td>
                                        <td style="font-size: 13px !important">
                                             {{ CustomHelper::formatConditionalQty($data->qty_out) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 13px !important">
                                            Waktu Timbang Keluar
                                        </td>
                                        <td style="font-size: 13px !important">
                                             {{ $data->time_scale_out ? date('d/m/Y H:i:s',strtotime($data->time_scale_out)) : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 13px !important">
                                            Berat Netto
                                        </td>
                                        <td style="font-size: 13px !important">
                                             {{ CustomHelper::formatConditionalQty($data->qty_balance) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 13px !important">
                                            Catatan
                                         </td>
                                         <td style="font-size: 13px !important">
                                           {{ $data->note }}
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
                                    <th class="center">{{ __('translations.name') }}</th>
                                    <th class="center">{{ __('translations.nominal') }}</th>
                                    <th class="center">{{ __('translations.unit') }}</th>
                                    <th class="center">{{ __('translations.note') }}</th>
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
                        <div class="column2">
                        </div>
                        </div>
                        <table class="table-bot1" width="100%" border="0">
                            <tr>
                                <td class="center-align" style="font-size: 13px !important">
                                    {!! ucwords(strtolower($data->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
                                    <br>
                                    Dibuat oleh,
                                    @if($data->user->signature)
                                        <div>{!! $data->user->signature() !!}</div>
                                    @endif
                                    <div class="{{ $data->user->signature ? 'mt-2' : 'mt-5' }}">{{ $data->user->name }}</div>
                                </td>
                                @if($data->approval())
                                    @foreach ($data->approval() as $detail)
                                        @foreach ($detail->approvalMatrix()->where('status','2')->get() as $row)
                                            <td class="center-align" style="font-size: 13px !important">
                                                {{ $row->approvalTemplateStage->approvalStage->approval->document_text }}
                                                @if($row->user->signature)
                                                    <div>{!! $row->user->signature() !!}</div>
                                                @endif
                                                <div class="{{ $row->user->signature ? 'mt-2' : 'mt-5' }}">{{ $row->user->name }}</div>
                                            </td>
                                        @endforeach
                                    @endforeach
                                @endif
                                <td class="center-align" style="font-size: 13px !important">
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <div class="mt-4">  Supir, {{ $data->driver }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
