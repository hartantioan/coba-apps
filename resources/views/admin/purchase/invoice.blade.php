<script src="{{ url('app-assets/js/sweetalert2.js') }}"></script>
<style>
    .modal {
        top:0px !important;
    }

    table > thead > tr > th {
        font-size: 13px !important;
    }

    body.tab-active input:focus {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    #dropZone {
        border: 2px dashed #ccc;
    }
    #imagePreview {
        max-width: 20em;
        max-height: 20em;
        min-height: 5em;
        margin: 2px auto;
    }

    .modal-content .select2.tab-active {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    table.bordered th {
        padding: 5px !important;
    }

    .browser-default {
        height: 2rem !important;
    }
    #modal5{
        position: fixed;
        top: 50% !important;
        left: 50% !important;
        /* bring your own prefixes */
        transform: translate(-50%, -50%) !important;
    }

    @media (min-width: 960px) {
        #modal4 {
            width:60%;
        }
    }

    @media (max-width: 960px) {
        #modal4 {
            width:100%;
        }
    }

    /* .select-wrapper, .select2-container {
        height:3.6rem !important;
    } */
</style>
<!-- BEGIN: Page Main-->
<div id="main">
    <div class="row">
        <div class="pt-3 pb-1" id="breadcrumbs-wrapper">
            <!-- Search for small screen-->
            <div class="container">
                <div class="row">
                    <div class="col s8 m6 l6">
                        <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ $title }}</span></h5>
                        <ol class="breadcrumbs mb-0">
                            <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item"><a href="#">{{ Str::title(str_replace('_',' ',Request::segment(2))) }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{ Str::title(str_replace('_',' ',Request::segment(3))) }}
                            </li>
                        </ol>
                    </div>
                    <div class="col s4 m6 l6">

                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="printData();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">{{ __('translations.print') }}</span>
                            <i class="material-icons right">local_printshop</i>
                        </a>

                    </div>
                </div>
            </div>
        </div>
        <div class="col s12">
            <div class="container">
                <div class="section section-data-tables">
                    <!-- DataTables example -->
                    <div class="row">
                        <div class="col s12">
                            <ul class="collapsible collapsible-accordion">
                                <li>
                                    <div class="collapsible-header"><i class="material-icons">filter_list</i>{{ __('translations.filter') }}</div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col m4 s6 ">
                                                <label for="filter_status" style="font-size:1rem;">Status :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_status" onchange="loadDataTable()" multiple>
                                                        <option value="1">Menunggu</option>
                                                        <option value="2">Dalam Proses</option>
                                                        <option value="7">Schedule</option>
                                                        <option value="3">Selesai</option>
                                                        <option value="4">Ditolak</option>
                                                        <option value="5">Ditutup</option>
                                                        <option value="6">Direvisi</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_type" style="font-size:1rem;">Tipe :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_type" onchange="loadDataTable()">
                                                        <option value="">{{ __('translations.all') }}</option>
                                                        <option value="1">Cash</option>
                                                        <option value="2">Credit</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_company" style="font-size:1rem;">Perusahaan :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_company" onchange="loadDataTable()">
                                                        <option value="">{{ __('translations.all') }}</option>
                                                        @foreach ($company as $rowcompany)
                                                            <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_account" style="font-size:1rem;">Supplier/Vendor :</label>
                                                <div class="input-field">
                                                    <select class="browser-default" id="filter_account" name="filter_account" multiple="multiple" style="width:100% !important;" onchange="loadDataTable()"></select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="start_date" style="font-size:1rem;">{{ __('translations.start_date') }} : </label>
                                                <div class="input-field col s12">
                                                <input type="date" max="{{ date('9999'.'-12-31') }}" id="start_date" name="start_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="finish_date" style="font-size:1rem;">{{ __('translations.end_date') }} :</label>
                                                <div class="input-field col s12">
                                                    <input type="date" max="{{ date('9999'.'-12-31') }}" id="finish_date" name="finish_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">
                                        List Data
                                    </h4>
                                    <div class="row">
                                        <div class="col s12">
                                            <div id="datatable_buttons"></div>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">{{ __('translations.refresh') }}</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-2" href="javascript:void(0);" onclick="exportExcel();">
                                                <i class="material-icons hide-on-med-and-up">view_headline</i>
                                                <span class="hide-on-small-onl">Export</span>
                                                <i class="material-icons right">view_headline</i>
                                            </a>
                                            <table id="datatable_serverside">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="2">#</th>
                                                        <th rowspan="2">{{ __('translations.code') }}</th>
                                                        <th rowspan="2">{{ __('translations.user') }}</th>
                                                        <th rowspan="2">Sup/Ven</th>
                                                        <th rowspan="2">{{ __('translations.company') }}</th>
                                                        <th colspan="4" class="center-align">{{ __('translations.date') }}</th>
                                                        <th colspan="2" class="center-align">{{ __('translations.conversion') }}</th>
                                                        <th rowspan="2">{{ __('translations.type') }}</th>
                                                        <th rowspan="2">Dokumen</th>
                                                        <th rowspan="2">{{ __('translations.note') }}</th>
                                                        <th rowspan="2">No.Faktur Pajak</th>
                                                        <th rowspan="2">No.Bukti Potong</th>
                                                        <th rowspan="2">Tgl.Bukti Potong</th>
                                                        <th rowspan="2">No.SPK</th>
                                                        <th rowspan="2">No.Invoice</th>
                                                        <th rowspan="2">{{ __('translations.subtotal') }}</th>
                                                        <th colspan="2" class="center-align">Diskon</th>
                                                        <th rowspan="2">{{ __('translations.total') }}</th>
                                                        <th rowspan="2">{{ __('translations.tax') }}</th>
                                                        <th rowspan="2">{{ __('translations.wtax') }}</th>
                                                        <th rowspan="2">Pembulatan</th>
                                                        <th rowspan="2">{{ __('translations.grandtotal') }}</th>
                                                        <th rowspan="2">Downpayment</th>
                                                        <th rowspan="2">Balance</th>
                                                        <th rowspan="2">{{ __('translations.status') }}</th>
                                                        <th rowspan="2">By</th>
                                                        <th rowspan="2">{{ __('translations.action') }}</th>
                                                    </tr>
                                                    <tr>
                                                        <th>Post</th>
                                                        <th>Terima</th>
                                                        <th>Tenggat</th>
                                                        <th>Dokumen</th>
                                                        <th>{{ __('translations.currency') }}</th>
                                                        <th>{{ __('translations.conversion') }}</th>
                                                        <th>{{ __('translations.percentage') }}</th>
                                                        <th>{{ __('translations.nominal') }}</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="content-overlay"></div>
        </div>
    </div>
</div>

<div id="modal1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content" style="overflow-x: hidden;max-width: 100%;">
        <div class="row">
            <div class="col s12">

                    <h4 class="mt-2">{{ __('translations.add') }}/{{ __('translations.edit') }} {{ $title }}</h4>
                    <i>Silahkan pilih supplier / vendor untuk mengambil data dokumen GRPO, PO Lain-lain, Landed Cost, atau AP DP.</i>
                    <form class="row" id="form_data" onsubmit="return false;">
                        <div class="col s12">
                            <div id="validation_alert" style="display:none;"></div>
                        </div>
                        <div class="col s12">
                            <div class="row">
                                <div class="input-field col m3 s12 step1">
                                    <input id="code" name="code" type="text" value="{{ $newcode }}" readonly>
                                    <label class="active" for="code">No. Dokumen</label>
                                </div>
                                <div class="input-field col m1 s12 step2">
                                    <select class="form-control" id="code_place_id" name="code_place_id" onchange="getCode(this.value);">
                                        <option value="">--Pilih--</option>
                                        @foreach ($place as $rowplace)
                                            <option value="{{ $rowplace->code }}">{{ $rowplace->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="input-field col m4 s12 step3">
                                    <select class="form-control" id="type_detail" name="type_detail" onchange="viewDetail();">
                                        <option value="1">Normal</option>
                                        <option value="2">Multi dari Excel</option>
                                    </select>
                                    <label class="" for="type_detail">Tipe Detail</label>
                                </div>
                                <div class="input-field col m4 s12 step6">
                                    <select class="form-control" id="company_id" name="company_id">
                                        @foreach ($company as $rowcompany)
                                            <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="" for="company_id">{{ __('translations.company') }}</label>
                                </div>
                                <div class="input-field col m2 s12 step4">
                                    <input type="hidden" id="temp" name="temp">
                                    <select class="browser-default" id="account_id" name="account_id" onchange="getAccountData('1');"></select>
                                    <label class="active" for="account_id">Supplier / Vendor</label>
                                </div>
                                <div class="input-field col m1 s12 center-align">
                                    <a href="javascript:void(0);" class="btn-floating mb-1 btn-flat waves-effect waves-light pink accent-2 white-text" onclick="getAccountData('1');" id="btn-show"><i class="material-icons right">receipt</i></a>
                                    <label class="active">&nbsp;</label>
                                </div>
                                <div class="input-field col m3 s12 step5">
                                    <select class="form-control" id="type" name="type">
                                        <option value="2">Transfer</option>
                                        <option value="1">Tunai</option>
                                        <option value="3">Cek/BG</option>
                                    </select>
                                    <label class="" for="type">{{ __('translations.type') }}</label>
                                </div>
                                <div class="col s12 m12 l12"></div>


                                <div class="input-field col m3 s12 step7">
                                    <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);loadCurrency();">
                                    <label class="active" for="post_date">{{ __('translations.post_date') }}</label>
                                </div>
                                <div class="input-field col m3 s12 step8">
                                    <input id="received_date" name="received_date" type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. Terima" value="{{ date('Y-m-d') }}" onchange="addDays();">
                                    <label class="active" for="received_date">Tgl. Terima</label>
                                </div>
                                <div class="input-field col m3 s12 step9">
                                    <input id="top" name="top" min="0" type="number" value="30" onchange="addDays();">
                                    <label class="active" for="top">TOP (hari) Autofill dari GRPO</label>
                                </div>
                                <div class="input-field col m3 s12 step10">
                                    <input id="due_date" name="due_date" min="{{ date('Y-m-d') }}" type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. Jatuh Tempo">
                                    <label class="active" for="due_date">Tgl. Jatuh Tempo</label>
                                </div>
                                <div class="col s12 m12 l12"></div>
                                <div class="input-field col m3 s12 step11">
                                    <input id="document_date" name="document_date" type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. dokumen">
                                    <label class="active" for="document_date">Tgl. Invoice</label>
                                </div>
                                <div class="input-field col m3 s12 step16">
                                    <input id="invoice_no" name="invoice_no" type="text" placeholder="Nomor Invoice dari Suppplier/Vendor">
                                    <label class="active" for="invoice_no">No. Invoice</label>
                                </div>
                                <div class="col s12 m12 l12"></div>
                                <div class="input-field col m4 s12 step12">
                                    <input id="tax_no" name="tax_no" type="text" placeholder="Nomor faktur pajak...">
                                    <label class="active" for="tax_no">No. Faktur Pajak</label>
                                </div>
                                <div class="input-field col m4 s12 step13">
                                    <input id="tax_cut_no" name="tax_cut_no" type="text" placeholder="Nomor bukti potong...">
                                    <label class="active" for="tax_cut_no">No. Bukti Potong</label>
                                </div>
                                <div class="input-field col m4 s12 step14">
                                    <input id="cut_date" name="cut_date" type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. Bukti potong">
                                    <label class="active" for="cut_date">Tgl. Bukti Potong</label>
                                </div>
                                <div class="input-field col m6 s12 step15">
                                    <input id="spk_no" name="spk_no" type="text" placeholder="Nomor SPK...">
                                    <label class="active" for="spk_no">No. SPK</label>
                                </div>
                                <div class="input-field col m6 s12 stepdokumen">
                                    <input id="document_no" name="document_no" type="text" placeholder="Nomor Dokumen...">
                                    <label class="active" for="document_no">No. Dokumen</label>
                                </div>
                                <div class="col s12 m12 l12"></div>
                                <div class="input-field col m4 s12 stepcurrency">
                                    <select class="form-control" id="currency_id" name="currency_id" onchange="loadCurrency();">
                                        @foreach ($currency as $row)
                                            <option value="{{ $row->id }}" data-code="{{ $row->code }}">{{ $row->code.' '.$row->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="" for="currency_id">{{ __('translations.currency') }}</label>
                                </div>
                                <div class="input-field col m4 s12 stepconversion">
                                    <input id="currency_rate" name="currency_rate" type="text" value="1" onkeyup="formatRupiah(this);countAll();">
                                    <label class="active" for="currency_rate">{{ __('translations.conversion') }}</label>
                                </div>

                                <div class="input-field col m4 s12 step18">
                                    <input id="scan_barcode" name="scan_barcode" type="text" placeholder="Ketik nomor dokumen dan tekan Enter...">
                                    <label class="active" for="scan_barcode">Scan Barcode (Single Input)</label>
                                </div>
                                <div class="col m12 s12 l12"></div>
                                <div class="col m6 s12 step17">
                                    <label class="">Bukti Upload</label>
                                    <br>
                                    <input type="file" name="file" id="fileInput" accept="image/*" style="display: none;">
                                    <div  class="col m8 s12 " id="dropZone" ondrop="dropHandler(event);" ondragover="dragOverHandler(event);" style="margin-top: 0.5em;height: 5em;">
                                        Drop image here or <a href="javascript:void(0);" id="uploadLink">upload</a>
                                        <br>

                                    </div>
                                    <a class="waves-effect waves-light cyan btn-small" style="margin-top: 0.5em;margin-left:0.2em" id="clearButton" href="javascript:void(0);">
                                       Clear
                                    </a>
                                </div>
                                <div class="col m6 s12">
                                    <div id="fileName"></div>
                                    <img src="" alt="Preview" id="imagePreview" style="display: none;">
                                </div>
                                <div class="col m12 s12">
                                    <ul class="collapsible">
                                        <li class="active step19" id="detailOne" onclick="resetTable();">
                                            <div class="collapsible-header purple darken-1 text-white" style="color:white;"><i class="material-icons">assignment</i>Single Input</div>
                                            <div class="collapsible-body" style="display:block;">
                                                <div class="row">
                                                    <div class="col m12" style="overflow:auto;width:100% !important;">
                                                        <p class="mt-2 mb-2">
                                                            <h6>Detail Goods Receipt PO / Landed Cost / Purchase Order Jasa / Coa</h6>
                                                            <div style="overflow:auto;">
                                                                <table class="bordered" style="width:3500px !important;" id="table-detail">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="center">{{ __('translations.delete') }}</th>
                                                                            <th class="center">GR/LC/PO/Coa No.</th>
                                                                            <th class="center">Keterangan 1</th>
                                                                            <th class="center">Keterangan 2</th>
                                                                            <th class="center">NO.PO/GRPO/FR</th>
                                                                            <th class="center">No.SJ</th>
                                                                            <th class="center">Item / Coa Jasa</th>
                                                                            <th class="center">{{ __('translations.unit') }}</th>
                                                                            <th class="center">Qty Diterima</th>
                                                                            <th class="center">Qty Kembali</th>
                                                                            <th class="center">Qty Sisa</th>
                                                                            <th class="center">Qty Stok</th>
                                                                            <th class="center">Satuan Stok</th>
                                                                            <th class="center">Harga@</th>
                                                                            <th class="center">Tgl.Post</th>
                                                                            <th class="center">Tgl.Tenggat</th>
                                                                            <th class="center">{{ __('translations.total') }}</th>
                                                                            <th class="center">PPN (%)</th>
                                                                            <th class="center">Termasuk PPN</th>
                                                                            <th class="center">PPN (Rp)</th>
                                                                            <th class="center">PPh (%)</th>
                                                                            <th class="center">PPh (Rp)</th>
                                                                            <th class="center">{{ __('translations.grandtotal') }}</th>

                                                                            <th class="center">{{ __('translations.plant') }}</th>
                                                                            <th class="center">{{ __('translations.line') }}</th>
                                                                            <th class="center">{{ __('translations.engine') }}</th>
                                                                            <th class="center">{{ __('translations.division') }}</th>
                                                                            <th class="center">{{ __('translations.warehouse') }}</th>
                                                                            <th class="center">Proyek</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="body-detail">
                                                                        <tr id="last-row-detail">
                                                                            <td colspan="29">
                                                                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addItem()" href="javascript:void(0);">
                                                                                    <i class="material-icons left">add</i> Pembulatan Manual
                                                                                </a>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li id="detailMulti" class="step20" onclick="resetTable();">
                                            <div class="collapsible-header purple darken-1 text-white" style="color:white;"><i class="material-icons">library_books</i>Multi Input</div>
                                            <div class="collapsible-body">
                                                <div class="row">
                                                    <div class="col m12" style="overflow:auto;width:100% !important;">
                                                        <h6>Anda bisa menggunakan fitur copy paste dari format excel yang telah disediakan. Silahkan klik <a href="{{-- {{ asset(Storage::url('format_imports/format_copas_ap_invoice_2.xlsx')) }} --}}{{ Request::url() }}/get_import_excel" target="_blank">disini</a> untuk mengunduh. Jangan menyalin kolom paling atas (bagian header), dan tempel pada isian paling kiri di tabel di bawah ini.</h6>
                                                        <h6>Fitur ini hanya untuk transaksi yang langsung menjadi biaya pada hutang usaha.</h6>
                                                        <p class="mt-2 mb-2">
                                                            <table class="bordered" style="min-width:2700px;zoom:0.7;">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="center">Coa</th>
                                                                        <th class="center">{{ __('translations.qty') }}</th>
                                                                        <th class="center">Harga Satuan</th>
                                                                        <th class="center">{{ __('translations.total') }}</th>
                                                                        <th class="center">ID PPN</th>
                                                                        <th class="center">{{ __('translations.tax') }}</th>
                                                                        <th class="center">ID PPh</th>
                                                                        <th class="center">{{ __('translations.wtax') }}</th>
                                                                        <th class="center">{{ __('translations.grandtotal') }}</th>
                                                                        <th class="center">Ket.1</th>
                                                                        <th class="center">Ket.2</th>
                                                                        <th class="center">{{ __('translations.plant') }}</th>
                                                                        <th class="center">{{ __('translations.line') }}</th>
                                                                        <th class="center">{{ __('translations.engine') }}</th>
                                                                        <th class="center">{{ __('translations.division') }}</th>
                                                                        <th class="center">{{ __('translations.warehouse') }}</th>
                                                                        <th class="center">Proyek</th>
                                                                        <th class="center">{{ __('translations.delete') }}</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="body-multi">
                                                                    <tr id="last-row-multi">
                                                                        <td colspan="18">
                                                                            <a class="waves-effect waves-light cyan btn-small mb-1 mr-1 step_2_1 " onclick="addLine()" href="javascript:void(0);">
                                                                                <i class="material-icons left">add</i> Tambah 1 Baris
                                                                            </a>
                                                                            <a class="waves-effect waves-light red btn-small mb-1 mr-1" onclick="addMulti()" href="javascript:void(0);">
                                                                                <i class="material-icons left">add</i> Tambah Multi Baris
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col m12 s12 step21">
                                    <p class="mt-2 mb-2">
                                        <h5>
                                            Detail Down Payment Partner Bisnis
                                            <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="getAccountData('2');" href="javascript:void(0);">
                                                <i class="material-icons left">add</i> Ambil Data
                                            </a>
                                        </h5>
                                        <div style="overflow:auto;">
                                            <table class="bordered">
                                                <thead>
                                                    <tr>
                                                        <th class="center">{{ __('translations.delete') }}</th>
                                                        <th class="center">Purchase DP No.</th>
                                                        <th class="center">Payment Req No.</th>
                                                        <th class="center">Tgl.Post</th>
                                                        <th class="center">{{ __('translations.nominal') }}</th>
                                                        <th class="center">Sisa</th>
                                                        <th class="center">Dipakai</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="body-detail-dp">
                                                    <tr id="empty-detail-dp">
                                                        <td colspan="7" class="center">
                                                            Pilih supplier/vendor untuk memulai...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </p>
                                </div>
                                <div class="input-field col m6 s12 step22">
                                    <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                    <label class="active" for="note">{{ __('translations.note') }}</label>
                                </div>
                                <div class="input-field col m6 s12 step23">
                                    <table width="100%" class="bordered">
                                        <thead>
                                            <tr>
                                                <td width="33%"></td>
                                                <td width="33%" class="center-align">Mata Uang Asli</td>
                                                <td width="33%" class="center-align">Mata Uang Konversi</td>
                                            </tr>
                                            <tr>
                                                <td>Total</td>
                                                <td class="right-align"><span id="total">0,00</span></td>
                                                <td class="right-align"><span id="total_convert">0,00</span></td>
                                            </tr>
                                            <tr>
                                                <td>PPN</td>
                                                <td class="right-align"><span id="tax">0,00</span></td>
                                                <td class="right-align"><span id="tax_convert">0,00</span></td>
                                            </tr>
                                            <tr>
                                                <td>PPh</td>
                                                <td class="right-align">
                                                    <input class="browser-default" id="wtax" name="wtax" onfocus="emptyThis(this);" type="text" value="0,00" onkeyup="formatRupiah(this);countGrandtotal();" style="text-align:right;width:100%;">
                                                </td>
                                                <td class="right-align"><span id="wtax_convert">0,00</span></td>
                                            </tr>
                                            <tr>
                                                <td>Pembulatan</td>
                                                <td class="right-align">
                                                    <input class="browser-default" id="rounding" name="rounding" onfocus="emptyThis(this);" type="text" value="0,00" onkeyup="formatRupiah(this);countGrandtotal();" style="text-align:right;width:100%;">
                                                </td>
                                                <td class="right-align"><span id="rounding_convert">0,00</span></td>
                                            </tr>
                                            <tr>
                                                <td>Uang Muka</td>
                                                <td class="right-align">
                                                    <input class="browser-default" id="downpayment" name="downpayment" type="text" value="0,00" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100%;" readonly>
                                                </td>
                                                <td class="right-align"><span id="downpayment_convert">0,00</span></td>
                                            </tr>
                                            <tr>
                                                <td>Grandtotal</td>
                                                <td class="right-align"><span id="balance">0,00</span></td>
                                                <td class="right-align"><span id="balance_convert">0,00</span></td>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light purple mr-1" onclick="startIntro1();">Panduan <i class="material-icons right">help_outline</i></button>
        <button class="btn waves-effect waves-light mr-1 step24" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>

<div id="modal2" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="show_print">

            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>

<div id="modal3" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="col s12" id="show_structure">
            <div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;">

            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>

<div id="modal4_1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="show_detail">

            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>

<div id="modal4" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h5>Daftar Tunggakan Dokumen <b id="account_name"></b></h5>
                <div class="row">
                    <div class="col s12 mt-2">
                        <ul class="collapsible">
                            <li class="active">
                                <div class="collapsible-header purple lightrn-1 white-text">
                                    <i class="material-icons">layers</i> Goods Receipt / Landed Cost / Purchase Order (Jasa) / Req. Dana (Vendor-Lengkap)
                                </div>
                                <div class="collapsible-body">
                                    <div id="datatable_buttons_multi"></div>
                                    <i class="right">Gunakan *pilih semua* untuk memilih seluruh data yang anda inginkan. Atau pilih baris untuk memilih data yang ingin dipindahkan.</i>
                                    <table id="table_multi" class="display" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">GR/LC/PO/FR No.</th>
                                                <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Detail Item</th>
                                                <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">No SJ/PO</th>
                                                <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Tgl.Post</th>
                                                <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.total') }}</th>
                                                <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Ter-Invoice</th>
                                                <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Sisa</th>
                                                <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.note') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-detail-multi"></tbody>
                                    </table>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat mr-1">{{ __('translations.close') }}</a>
        <button class="btn waves-effect waves-light purple right submit" onclick="applyDocuments('main');">Gunakan <i class="material-icons right">forward</i></button>
    </div>
</div>

<div id="modal7" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h5>Daftar AP Down Payment <b id="account_name_dp"></b></h5>
                <div class="row">
                    <div class="col s12 mt-2">
                        <ul class="collapsible">
                            <li class="active">
                                <div class="collapsible-header cyan white-text">
                                    <i class="material-icons">more</i> Purchase Down Payment
                                </div>
                                <div class="collapsible-body">
                                    <div id="datatable_buttons_multi_dp"></div>
                                    <table id="table_multi_dp" class="display" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="center">Purchase DP No.</th>
                                                <th class="center">NO PREQ.</th>
                                                <th class="center">Tgl.Post</th>
                                                <th class="center">{{ __('translations.nominal') }}</th>
                                                <th class="center">Sisa</th>
                                                <th class="center">Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-detail-dp-multi"></tbody>
                                    </table>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat mr-1">{{ __('translations.close') }}</a>
        <button class="btn waves-effect waves-light purple right submit" onclick="applyDocuments('dp');">Gunakan <i class="material-icons right">forward</i></button>
    </div>
</div>

<div id="modal5" class="modal modal-fixed-footer" style="height: 70% !important;width:50%">
    <div class="modal-header ml-6 mt-2">
        <h6>Range Printing</h6>
    </div>
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <form class="row" id="form_data_print_multi" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert_multi" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <ul class="tabs">
                            <li class="tab">
                                <a href="#range-tabs" class="" id="part-tabs-btn">
                                <span>By No</span>
                                </a>
                            </li>
                            <li class="tab">
                                <a href="#date-tabs" class="">
                                <span>By Date</span>
                                </a>
                            </li>
                            <li class="indicator" style="left: 0px; right: 0px;"></li>
                        </ul>
                        <div id="range-tabs" style="display: block;" class="">
                            <div class="row ml-2 mt-2">
                                <div class="row">
                                    <div class="input-field col m2 s12">
                                        <p>{{ $menucode }}</p>
                                    </div>
                                    <div class="input-field col m2 s12">
                                        <select class="form-control" id="code_place_range" name="code_place_range">
                                            <option value="">--Pilih--</option>
                                            @foreach ($place as $rowplace)
                                                <option value="{{ $rowplace->code }}">{{ $rowplace->code }}</option>
                                            @endforeach
                                        </select>
                                        <label class="" for="code_place_range">Plant / Place</label>
                                    </div>
                                    <div class="input-field col m2 s12">
                                        <input id="year_range" name="year_range" min="0" type="number" placeholder="23">
                                        <label class="active" for="year_range">Tahun</label>
                                    </div>
                                    <div class="input-field col m1 s12">
                                        <input id="range_start" name="range_start" min="0" type="number" placeholder="1">
                                        <label class="" for="range_end">No Awal</label>
                                    </div>

                                    <div class="input-field col m1 s12">
                                        <input id="range_end" name="range_end" min="0" type="number" placeholder="1">
                                        <label class="active" for="range_end">No akhir</label>
                                    </div>
                                    <div class="input-field col m2 s12">
                                        <label>
                                            <input name="type_date" type="radio" checked value="1"/>
                                            <span>Dengan range biasa</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                <div class="input-field col m8 s12">
                                    <input id="range_comma" name="range_comma" type="text" placeholder="1,2,5....">
                                    <label class="" for="range_end">Masukkan angka dengan koma</label>
                                </div>

                                <div class="input-field col m1 s12">
                                    <label>
                                        <input name="type_date" type="radio" value="2"/>
                                        <span>Dengan Range koma</span>
                                    </label>
                                </div>
                                </div>
                                <div class="col s12 mt-3">
                                    <button class="btn waves-effect waves-light right submit" onclick="printMultiSelect();">Print <i class="material-icons right">send</i></button>
                                </div>
                            </div>
                        </div>
                        <div id="date-tabs" style="display: none;" class="">

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat mr-1">{{ __('translations.close') }}</a>
    </div>
</div>

<div id="modal6" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row" >
            <div class="col m3 s12">

            </div>
            <div class="col m6 s12">
                <h4 id="title_data" style="text-align:center"></h4>
                <h5 id="code_data" style="text-align:center"></h5>
            </div>
            <div class="col m3 s12 right-align">
                <img src="{{ url('website/logo_web_fix.png') }}" width="40%" height="60%">
            </div>
        </div>
        <div class="divider mb-1 mt-2"></div>
        <div class="row">
            <div class="col" id="user_jurnal">
            </div>
            <div class="col" id="post_date_jurnal">
            </div>
            <div class="col" id="note_jurnal">
            </div>
            <div class="col" id="ref_jurnal">
            </div>
            <div class="col" id="company_jurnal">
            </div>
        </div>
        <div class="row mt-2">
            <table class="bordered Highlight striped" style="zoom:0.7;">
                <thead>
                        <tr>
                            <th class="center-align" rowspan="2">No</th>
                            <th class="center-align" rowspan="2">Coa</th>
                            <th class="center-align" rowspan="2">{{ __('translations.bussiness_partner') }}</th>
                            <th class="center-align" rowspan="2">{{ __('translations.plant') }}</th>
                            <th class="center-align" rowspan="2">{{ __('translations.line') }}</th>
                            <th class="center-align" rowspan="2">{{ __('translations.engine') }}</th>
                            <th class="center-align" rowspan="2">{{ __('translations.division') }}</th>
                            <th class="center-align" rowspan="2">{{ __('translations.warehouse') }}</th>
                            <th class="center-align" rowspan="2">Proyek</th>
                            <th class="center-align" rowspan="2">Ket.1</th>
                            <th class="center-align" rowspan="2">Ket.2</th>
                            <th class="center-align" colspan="2">Mata Uang Asli</th>
                            <th class="center-align" colspan="2">Mata Uang Konversi</th>
                        </tr>
                        <tr>
                            <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Debit</th>
                            <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Kredit</th>
                            <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Debit</th>
                            <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Kredit</th>
                        </tr>

                </thead>
                <tbody id="body-journal-table">
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div>
<div style="bottom: 50px; right: 80px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-amber-amber gradient-shadow modal-trigger tooltipped"  data-position="top" data-tooltip="Range Printing" href="#modal5">
        <i class="material-icons">view_comfy</i>
    </a>
</div>
<!-- END: Page Main-->
<script>
    const dropZone = document.getElementById('dropZone');
    const uploadLink = document.getElementById('uploadLink');
    const fileInput = document.getElementById('fileInput');
    const imagePreview = document.getElementById('imagePreview');
    const clearButton = document.getElementById('clearButton');
    const fileNameDiv = document.getElementById('fileName');
    dropZone.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', (e) => {
        handleFile(e.target.files[0]);
    });

    function dragOverHandler(event) {
        event.preventDefault();
        dropZone.style.backgroundColor = '#f0f0f0';
    }

    function dropHandler(event) {
        event.preventDefault();
        dropZone.style.backgroundColor = '#fff';

        handleFile(event.dataTransfer.files[0]);
    }

    function handleFile(file) {
        if (file) {
        const reader = new FileReader();
        const fileType = file.type.split('/')[0];
        const maxSize = 10 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('File size exceeds the maximum limit of 10 MB.');
            return;
        }

        reader.onload = () => {

            fileNameDiv.textContent = 'File uploaded: ' + file.name;

            if (fileType === 'image') {

                imagePreview.src = reader.result;
                imagePreview.style.display = 'inline-block';
                clearButton.style.display = 'inline-block';
            } else {

                imagePreview.style.display = 'none';

            }
        };

        reader.readAsDataURL(file);
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);


        fileInput.files = dataTransfer.files;

        }
    }

    clearButton.addEventListener('click', () => {
        imagePreview.src = '';
        imagePreview.style.display = 'none';
        fileInput.value = '';
        fileNameDiv.textContent = '';
    });

    document.addEventListener('paste', (event) => {
        const items = event.clipboardData.items;
        if (items) {
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    const file = items[i].getAsFile();
                    handleFile(file);
                    break;
                }
            }
        }
    });

    function displayFile(fileLink) {
        const fileType = getFileType(fileLink);

        fileNameDiv.textContent = 'File uploaded: ' + getFileName(fileLink);

        if (fileType === 'image') {

            imagePreview.src = fileLink;
            imagePreview.style.display = 'inline-block';

        } else {

            imagePreview.style.display = 'none';


            const fileExtension = getFileExtension(fileLink);
            if (fileExtension === 'pdf' || fileExtension === 'xlsx' || fileExtension === 'docx') {

                const downloadLink = document.createElement('a');
                downloadLink.href = fileLink;
                downloadLink.download = getFileName(fileLink);
                downloadLink.textContent = 'Download ' + fileExtension.toUpperCase();
                fileNameDiv.appendChild(downloadLink);
            }
        }
    }

    function getFileType(fileLink) {
        const fileExtension = getFileExtension(fileLink);
        if (fileExtension === 'jpg' || fileExtension === 'jpeg' || fileExtension === 'png' || fileExtension === 'gif') {
            return 'image';
        } else {
            return 'other';
        }
    }

    function getFileExtension(fileLink) {
        return fileLink.split('.').pop().toLowerCase();
    }

    function getFileName(fileLink) {
        return fileLink.split('/').pop();
    }
    document.addEventListener('focusin', function (event) {
        const select2Container = event.target.closest('.modal-content .select2');
        const activeSelect2 = document.querySelector('.modal-content .select2.tab-active');
        if (event.target.closest('.modal-content')) {
            document.body.classList.add('tab-active');
        }


        if (activeSelect2 && !select2Container) {
            activeSelect2.classList.remove('tab-active');
        }


        if (select2Container) {
            select2Container.classList.add('tab-active');
        }
    });

    document.addEventListener('mousedown', function () {
        const activeSelect2 = document.querySelector('.modal-content .select2.tab-active');
        document.body.classList.remove('tab-active');
        if (activeSelect2) {
            activeSelect2.classList.remove('tab-active');
        }
    });
    var table_multi, table_multi_dp;
    $(function() {
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
        });

        loadDataTable();

        window.table.search('{{ $code }}').draw();

        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                $('#post_date').attr('min','{{ $minDate }}');
                $('#post_date').attr('max','{{ $maxDate }}');
                $('#due_date').attr('min','{{ date("Y-m-d") }}');
            },
            onOpenEnd: function(modal, trigger) {
                $('#name').focus();
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
                window.onbeforeunload = function() {
                    return 'You will lose all changes made since your last save';
                };
                loadCurrency();
                $('.tabs').tabs();
            },
            onCloseEnd: function(modal, trigger){
                clearButton.click();
                $('#form_data')[0].reset();
                $('input').css('border', 'none');
                $('input').css('border-bottom', '0.5px solid black');
                $('#temp').val('');
                $('.row_purchase').each(function(){
                    $(this).remove();
                });
                M.updateTextFields();
                $('.row_detail,.row_detail_dp').remove();
                $('#account_id').empty();
                $('#total,#tax,#balance').text('0,00');
                $('#subtotal,#discount,#downpayment').val('0,00');
                window.onbeforeunload = function() {
                    return null;
                };
                $('#type_detail').trigger('change').formSelect();
                $('.row_multi').remove();
                countAllMulti();
            }
        });

        $('#modal2').modal({
            onOpenStart: function(modal,trigger) {

            },
            onOpenEnd: function(modal, trigger) {
                window.print();
            },
            onCloseEnd: function(modal, trigger){
                $('#show_print').html('');
            }
        });

        $('#modal3').modal({
            onOpenStart: function(modal,trigger) {

            },
            onOpenEnd: function(modal, trigger) {
            },
            onCloseEnd: function(modal, trigger){
                $('#myDiagramDiv').remove();
                $('#show_structure').append(
                    `<div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;"></div>
                    `
                );
            }
        });

        $('#modal4').modal({
            onOpenStart: function(modal,trigger) {
                $('.collapsible').collapsible({
                    accordion:false
                });
            },
            onOpenEnd: function(modal, trigger) {
                table_multi = $('#table_multi').DataTable({
                    "responsive": true,
                    scrollY: '50vh',
                    scrollCollapse: true,
                    "iDisplayInLength": 10,
                    "order": [[0, 'desc']],
                    dom: 'Blfrtip',
                    buttons: [
                        'selectAll',
                        'selectNone'
                    ],
                    "language": {
                        "lengthMenu": "Menampilkan _MENU_ data per halaman",
                        "zeroRecords": "Data tidak ditemukan / kosong",
                        "info": "Menampilkan halaman _PAGE_ / _PAGES_ dari total _TOTAL_ data",
                        "infoEmpty": "Data tidak ditemukan / kosong",
                        "infoFiltered": "(disaring dari _MAX_ total data)",
                        "search": "Cari",
                        "paginate": {
                            first:      "<<",
                            previous:   "<",
                            next:       ">",
                            last:       ">>"
                        },
                        "buttons": {
                            selectAll: "Pilih semua",
                            selectNone: "Hapus pilihan"
                        },
                        "select": {
                            rows: "%d baris terpilih"
                        }
                    },
                    select: {
                        style: 'multi'
                    }
                });

                $('#table_multi_wrapper > .dt-buttons').appendTo('#datatable_buttons_multi');
                $('select[name="table_multi_length"]').addClass('browser-default');
            },
            onCloseEnd: function(modal, trigger){
                $('#body-detail-multi').empty();
                $('#account_name').text('');
                $('#table_multi').DataTable().clear().destroy();
            }
        });

        $('#modal7').modal({
            onOpenStart: function(modal,trigger) {
                $('.collapsible').collapsible({
                    accordion:false
                });
            },
            onOpenEnd: function(modal, trigger) {
                table_multi_dp = $('#table_multi_dp').DataTable({
                    "responsive": true,
                    scrollY: '50vh',
                    scrollCollapse: true,
                    "iDisplayInLength": 10,
                    "order": [[0, 'desc']],
                    dom: 'Blfrtip',
                    buttons: [
                        'selectAll',
                        'selectNone'
                    ],
                    "language": {
                "lengthMenu": "Menampilkan _MENU_ data per halaman",
                "zeroRecords": "Data tidak ditemukan / kosong",
                "info": "Menampilkan halaman _PAGE_ / _PAGES_ dari total _TOTAL_ data",
                "infoEmpty": "Data tidak ditemukan / kosong",
                "infoFiltered": "(disaring dari _MAX_ total data)",
                "search": "Cari",
                "paginate": {
                    first:      "<<",
                    previous:   "<",
                    next:       ">",
                    last:       ">>"
                },
                "buttons": {
                    selectAll: "Pilih semua",
                    selectNone: "Hapus pilihan"
                },
                "select": {
                    rows: "%d baris terpilih"
                }
            },
                    select: {
                        style: 'multi'
                    }
                });

                $('#table_multi_dp_wrapper > .dt-buttons').appendTo('#datatable_buttons_multi_dp');
                $('select[name="table_multi_dp_length"]').addClass('browser-default');
            },
            onCloseEnd: function(modal, trigger){
                $('#body-detail-dp-multi').empty();
                $('#account_name_dp').text('');
                $('#table_multi_dp').DataTable().clear().destroy();
            }
        });

        $('#modal4_1').modal({
            onOpenStart: function(modal,trigger) {

            },
            onOpenEnd: function(modal, trigger) {
            },
            onCloseEnd: function(modal, trigger){
                $('#show_detail').empty();
            }
        });

        $('#modal5').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {

            },
            onOpenEnd: function(modal, trigger) {
                $('#validation_alert_multi').hide();
                $('#validation_alert_multi').html('');
                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');

            }
        });
        $('#modal6').modal({
            onOpenStart: function(modal,trigger) {

            },
            onOpenEnd: function(modal, trigger) {
            },
            onCloseEnd: function(modal, trigger){
                $('#title_data').empty();
                $('#code_data').empty();
                $('#body-journal-table').empty();
                $('#user_jurnal').empty();
                $('#note_jurnal').empty();
                $('#ref_jurnal').empty();
                $('#company_jurnal').empty();
                $('#post_date_jurnal').empty();
            }
        });

        $('#body-multi').on('click', '.delete-data-multi', function() {
            $(this).closest('tr').remove();
            countAllMulti();
        });

        $('#body-detail').on('click', '.delete-data-detail', function() {
            $(this).closest('tr').remove();
            countAll();
        });

        $('#body-detail-dp').on('click', '.delete-data-detail-dp', function() {
            $(this).closest('tr').remove();
            countAll();
        });

        select2ServerSide('#account_id,#filter_account', '{{ url("admin/select2/supplier_vendor") }}');

        $("#table-detail th").resizable({
            minWidth: 100,
        });

        $("#scan_barcode").on( "keypress", function(e) {
            var key = e.which;
            if(key == 13){
                if($(this).val()){
                    let code = $(this).val();
                    $.ajax({
                        url: '{{ Request::url() }}/get_scan_barcode',
                        type: 'POST',
                        dataType: 'JSON',
                        data: {
                            code: code,
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        beforeSend: function() {
                            loadingOpen('.modal-content');
                        },
                        success: function(response) {
                            loadingClose('.modal-content');
                            if(response.status == 200){
                                if(response.details.length > 0){
                                    $.each(response.details, function(i, val) {
                                        var count = makeid(10);
                                        $('#last-row-detail').before(`
                                            <tr class="row_detail">
                                                <input type="hidden" name="arr_type[]" value="` + val.type + `" data-id="` + count + `">
                                                <input type="hidden" name="arr_price[]" value="` + val.price + `" data-id="` + count + `">
                                                <input type="hidden" name="arr_total[]" value="` + val.total + `" data-id="` + count + `">
                                                <input type="hidden" name="arr_grandtotal[]" value="` + val.grandtotal + `" data-id="` + count + `">
                                                <input type="hidden" name="arr_tax[]" value="` + val.tax + `" data-id="` + count + `">
                                                <input type="hidden" name="arr_wtax[]" value="` + val.wtax + `" data-id="` + count + `">
                                                <input type="hidden" id="arr_place` + count + `" name="arr_place[]" value="` + val.place_id + `" data-id="` + count + `">
                                                <input type="hidden" id="arr_line` + count + `" name="arr_line[]" value="` + val.line_id + `" data-id="` + count + `">
                                                <input type="hidden" id="arr_machine` + count + `" name="arr_machine[]" value="` + val.machine_id + `" data-id="` + count + `">
                                                <input type="hidden" id="arr_department` + count + `" name="arr_department[]" value="` + val.department_id + `" data-id="` + count + `">
                                                <input type="hidden" id="arr_warehouse` + count + `" name="arr_warehouse[]" value="` + val.warehouse_id + `" data-id="` + count + `">
                                                <input type="hidden" id="arr_project` + count + `" name="arr_project[]" value="` + val.project_id + `" data-id="` + count + `">
                                                <input type="hidden" name="arr_code[]" value="` + val.id + `" data-id="` + count + `">
                                                <input type="hidden" name="arr_temp_qty[]" value="` + val.qty_balance + `" data-id="` + count + `">
                                                <td class="center">
                                                    ` + val.rawcode + `
                                                </td>
                                                <td>
                                                    <input type="text" name="arr_note[]" value="` + val.note + `" data-id="` + count + `">
                                                </td>
                                                <td>
                                                    <input type="text" name="arr_note2[]" value="` + val.note2 + `" data-id="` + count + `">
                                                </td>
                                                <td class="center">
                                                    ` + val.purchase_no + `
                                                </td>
                                                <td class="center">
                                                    ` + val.delivery_no + `
                                                </td>
                                                <td class="">
                                                    ` + val.name + `
                                                </td>
                                                <td class="center">
                                                    ` + val.buy_unit + `
                                                </td>
                                                <td class="center">
                                                    ` + val.qty_received + `
                                                </td>
                                                <td class="center">
                                                    ` + val.qty_returned + `
                                                </td>
                                                <td class="center">
                                                    <input class="browser-default" type="text" name="arr_qty[]" onfocus="emptyThis(this);" value="` + val.qty_balance + `" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();countStock(this);" data-conversion="` + val.qty_conversion + `">
                                                </td>
                                                <td class="center" id="qty_stock` + count + `">
                                                    ` + val.qty_stock + `
                                                </td>
                                                <td class="center" id="unit_stock` + count + `">
                                                    ` + val.unit_stock + `
                                                </td>
                                                <td class="right-align">
                                                    ` + val.price + `
                                                </td>
                                                <td class="center">
                                                    ` + val.post_date + `
                                                </td>
                                                <td class="center">
                                                    ` + val.due_date + `
                                                </td>
                                                <td class="right-align row_total" id="row_total` + count + `">
                                                    ` + val.total + `
                                                </td>
                                                <td class="center">
                                                    <select class="browser-default" id="arr_percent_tax` + count + `" name="arr_percent_tax[]" data-id="` + count + `" onchange="countAll();">
                                                        <option value="0.00000" data-id="">-- Non-PPN --</option>
                                                        @foreach ($tax as $row1)
                                                            <option value="{{ $row1->percentage }}" data-id="{{ $row1->id }}">{{ $row1->name.' - '.number_format($row1->percentage,2,',','.').'%' }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="center">
                                                    <select class="browser-default" id="arr_include_tax` + count + `" name="arr_include_tax[]" data-id="` + count + `" onchange="countAll();">
                                                        <option value="0">Tidak</option>
                                                        <option value="1">Ya</option>
                                                    </select>
                                                </td>
                                                <td class="right-align" id="row_tax` + count + `">
                                                    ` + val.tax + `
                                                </td>
                                                <td class="center">
                                                    <select class="browser-default" id="arr_percent_wtax` + count + `" name="arr_percent_wtax[]" data-id="` + count + `" onchange="countAll();">
                                                        <option value="0.00000" data-id="">-- Non-PPh --</option>
                                                        @foreach ($wtax as $row2)
                                                            <option value="{{ $row2->percentage }}" data-id="{{ $row2->id }}">{{ $row2->name.' - '.number_format($row2->percentage,2,',','.').'%' }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="right-align" id="row_wtax` + count + `">
                                                    ` + val.wtax + `
                                                </td>
                                                <td class="right-align row_grandtotal" id="row_grandtotal` + count + `">
                                                    ` + val.grandtotal + `
                                                </td>

                                                <td class="center">
                                                    ` + val.place_name + `
                                                </td>
                                                <td class="center">
                                                    ` + val.line_name + `
                                                </td>
                                                <td class="center">
                                                    ` + val.machine_name + `
                                                </td>
                                                <td class="center">
                                                    ` + val.department_name + `
                                                </td>
                                                <td class="center">
                                                    ` + val.warehouse_name + `
                                                </td>
                                                <td class="center">
                                                    ` + val.project_name + `
                                                </td>
                                            </tr>
                                        `);

                                        $('#arr_percent_tax' + count).val(val.percent_tax);
                                        $('#arr_percent_wtax' + count).val(val.percent_wtax);
                                        $('#arr_include_tax' + count).val(val.include_tax);

                                        $('#top').val(val.top);

                                        $('#received_date').val(val.received_date);
                                        $('#due_date').val(val.due_date);
                                        $('#document_date').val(val.document_date);
                                        $('#tax_no').val(val.tax_no);
                                        $('#tax_cut_no').val(val.tax_cut_no);
                                        $('#cut_date').val(val.cut_date);
                                        $('#spk_no').val(val.spk_no);
                                        $('#invoice_no').val(val.invoice_no);

                                    });
                                }else{
                                    $('.row_detail').remove();
                                    $('#total,#tax,#balance').text('0,00');
                                }

                                if(!$('#received_date').val()){
                                    addDays();
                                }

                                $('.modal-content').scrollTop(400);
                                M.updateTextFields();

                                countAll();

                            }else{
                                swal({
                                    title: 'Ups!',
                                    text: 'Mohon maaf data tidak ditemukan.',
                                    icon: 'error'
                                });
                            }

                            $('#scan_barcode').val('');
                        },
                        error: function() {
                            $('.modal-content').scrollTop(0);
                            loadingClose('.modal-content');
                            swal({
                                title: 'Ups!',
                                text: 'Check your internet connection.',
                                icon: 'error'
                            });
                        }
                    });
                }
                e.preventDefault();
            }
        });
    });

    String.prototype.replaceAt = function(index, replacement) {
        return this.substring(0, index) + replacement + this.substring(index + replacement.length);
    };

    function resetTable(){
        setTimeout(function() {
            if($('#detailOne').hasClass('active')){
                $('#type_detail').val('1').formSelect();
            }
            if($('#detailMulti').hasClass('active')){
                $('#type_detail').val('2').formSelect();
            }
        }, 500);
    }

    function viewDetail(){
        $('.row_detail,.row_detail_dp,.row_multi').remove();
        if($('#type_detail').val() == '1'){
            $('#detailOne .collapsible-body').show();
            $('#detailOne').addClass('active');
            $('#detailMulti .collapsible-body').hide();
            $('#detailMulti').removeClass('active');
        }else if($('#type_detail').val() == '2'){
            $('#detailOne .collapsible-body').hide();
            $('#detailOne').removeClass('active');
            $('#detailMulti .collapsible-body').show();
            $('#detailMulti').addClass('active');
        }
        countAll();
    }

    function getCode(val){
        if(val){
            if($('#temp').val()){
                let newcode = $('#code').val().replaceAt(7,val);
                $('#code').val(newcode);
            }else{
                if($('#code').val().length > 7){
                    $('#code').val($('#code').val().slice(0, 7));
                }
                $.ajax({
                    url: '{{ Request::url() }}/get_code',
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        val: $('#code').val() + val,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        loadingOpen('.modal-content');
                    },
                    success: function(response) {
                        loadingClose('.modal-content');
                        $('#code').val(response);
                    },
                    error: function() {
                        swal({
                            title: 'Ups!',
                            text: 'Check your internet connection.',
                            icon: 'error'
                        });
                    }
                });
            }
        }
    }

    function changeDateMinimum(val){
        if(val){
            let newcode = $('#code').val().replaceAt(5,val.split('-')[0].toString().substr(-2));
            if($('#code').val().substring(5, 7) !== val.split('-')[0].toString().substr(-2)){
                if(newcode.length > 9){
                    newcode = newcode.substring(0, 9);
                }
            }
            $('#code').val(newcode);
            $('#code_place_id').trigger('change');
        }
    }

    function addLine(){
        $('#last-row-multi').before(`
            <tr class="row_multi">
                <td>
                    <input type="text" name="arr_multi_coa[]" placeholder="ID COA">
                </td>
                <td>
                    <input type="text" name="arr_multi_qty[]" value="0" placeholder="Jumlah Qty Barang/Jasa" onkeyup="countAllMulti()">
                </td>
                <td>
                    <input type="text" name="arr_multi_price[]" value="0" placeholder="Harga Satuan" onkeyup="countAllMulti()">
                </td>
                <td>
                    <input type="text" name="arr_multi_total[]" value="0" placeholder="Harga Total" onkeyup="countAllMulti()">
                </td>
                <td>
                    <input type="text" name="arr_multi_tax_id[]" placeholder="ID PPN">
                </td>
                <td>
                    <input type="text" name="arr_multi_ppn[]" placeholder="Pajak PPN" value="0" onkeyup="countAllMulti()">
                </td>
                <td>
                    <input type="text" name="arr_multi_wtax_id[]" placeholder="ID PPh">
                </td>
                <td>
                    <input type="text" name="arr_multi_pph[]" placeholder="Pajak PPh" value="0" onkeyup="countAllMulti()">
                </td>
                <td>
                    <input type="text" name="arr_multi_grandtotal[]" placeholder="Grandtotal" value="0" onkeyup="countAllMulti()">
                </td>
                <td>
                    <input type="text" name="arr_multi_note_1[]" placeholder="Keterangan 1">
                </td>
                <td>
                    <input type="text" name="arr_multi_note_2[]" placeholder="Keterangan 2">
                </td>
                <td>
                    <input type="text" name="arr_multi_place[]" placeholder="ID Plant">
                </td>
                <td>
                    <input type="text" name="arr_multi_line[]" placeholder="ID Line">
                </td>
                <td>
                    <input type="text" name="arr_multi_machine[]" placeholder="ID Mesin">
                </td>
                <td>
                    <input type="text" name="arr_multi_department[]" placeholder="ID Divisi">
                </td>
                <td>
                    <input type="text" name="arr_multi_warehouse[]" placeholder="ID Gudang">
                </td>
                <td>
                    <input type="text" name="arr_multi_project[]" placeholder="ID Gudang">
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-multi" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        $('#body-multi :input').off('paste');
        $('#body-multi :input').on('paste', function (e) {
            var $this = $(this);
            $.each(e.originalEvent.clipboardData.items, function(i, v){
                if (v.type === 'text/plain'){
                    v.getAsString(function(text){
                        var x = $this.closest('td').index(),
                            y = $this.closest('tr').index()+1,
                            obj = {};
                        text = text.trim('\r\n');
                        $.each(text.split('\r\n'), function(i2, v2){
                            $.each(v2.split('\t'), function(i3, v3){
                                var row = y+i2, col = x+i3;
                                obj['cell-'+row+'-'+col] = v3;
                                $this.closest('table').find('tr:eq('+row+') td:eq('+col+') input').val(v3);
                            });
                        });

                    });
                }
            });
            countAll();
            return false;
        });
    }

    function addMulti(){
        var count = 0;
        swal({
            title: "Input Jumlah Baris Yang Diinginkan!",
            text: "Maksimal tambah multi adalah 50 baris.",
            buttons: true,
            content: {
                element: "input",
                attributes: {
                    min: 1,
                    max: 50,
                    type: "number",
                    value: 1,
                }
            },
            closeOnClickOutside: false,
        })
        .then(() => {
            if ($('.swal-content__input').val() != "" && $('.swal-content__input').val() != null) {
                count = parseInt($('.swal-content__input').val());
                if(parseInt(count) > 50){
                    swal({
                        title: 'Baris tidak boleh lebih dari 50.',
                        icon: 'error'
                    });
                }else{
                    for(var i = 0;i < count;i++){
                        $('#last-row-multi').before(`
                            <tr class="row_multi">
                                <td>
                                    <input type="text" name="arr_multi_coa[]" placeholder="ID COA">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_qty[]" value="0" placeholder="Jumlah Qty Barang/Jasa" onkeyup="countAllMulti()">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_price[]" value="0" placeholder="Harga Satuan" onkeyup="countAllMulti()">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_total[]" value="0" placeholder="Harga Total" onkeyup="countAllMulti()">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_tax_id[]" placeholder="ID PPN">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_ppn[]" placeholder="Pajak PPN" value="0" onkeyup="countAllMulti()">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_wtax_id[]" placeholder="ID PPh">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_pph[]" placeholder="Pajak PPh" value="0" onkeyup="countAllMulti()">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_grandtotal[]" placeholder="Grandtotal" value="0" onkeyup="countAllMulti()">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_note_1[]" placeholder="Keterangan 1">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_note_2[]" placeholder="Keterangan 2">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_place[]" placeholder="ID Plant">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_line[]" placeholder="ID Line">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_machine[]" placeholder="ID Mesin">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_department[]" placeholder="ID Divisi">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_warehouse[]" placeholder="ID Gudang">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_project[]" placeholder="ID Proyek">
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-multi" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                    }

                    $('#body-multi :input').off('paste');
                    $('#body-multi :input').on('paste', function (e) {
                        var $start = $(this);
                        var source;

                        if (window.clipboardData !== undefined) {
                            source = window.clipboardData;
                        } else {
                            source = e.originalEvent.clipboardData;
                        }
                        var data = source.getData("Text");
                        if (data.length > 0) {
                            if (data.indexOf("\t") > -1) {
                                var columns = data.split("\n");
                                $.each(columns, function () {
                                    var values = this.split("\t");
                                    $.each(values, function () {
                                        $start.val(this);
                                        if($start.closest('td').next('td').find('input')[0] != undefined) {
                                            $start = $start.closest('td').next('td').find('input');
                                        }else{
                                            return false;
                                        }
                                    });
                                    $start = $start.closest('td').parent().next('tr').children('td:first').find('input');
                                });
                                e.preventDefault();
                            }
                            countAllMulti();
                            M.toast({
                                html: 'Sukses ditempel.'
                            });
                        }
                    });
                }
            }
        });
    }

    function countAllMulti(){
        let total = 0, ppn = 0, pph = 0, grandtotal = 0, rounding = parseFloat($('#rounding').val().replaceAll(".", "").replaceAll(",",".")), downpayment = parseFloat($('#downpayment').val().replaceAll(".", "").replaceAll(",",".")), balance = 0, currency_rate = parseFloat($('#currency_rate').val().replaceAll(".", "").replaceAll(",","."));

        $('input[name^="arr_multi_total"]').each(function(index){
            total += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
        });
        $('input[name^="arr_multi_ppn"]').each(function(index){
            ppn += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
        });
        $('input[name^="arr_multi_pph"]').each(function(index){
            pph += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
        });
        $('input[name^="arr_multi_grandtotal"]').each(function(index){
            grandtotal += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
        });

        /* $('#totalMulti').text(formatRupiahIni(total.toFixed(2).toString().replace('.',',')));
        $('#ppnMulti').text(formatRupiahIni(ppn.toFixed(2).toString().replace('.',',')));
        $('#pphMulti').text(formatRupiahIni(pph.toFixed(2).toString().replace('.',',')));
        $('#grandtotalMulti').text(formatRupiahIni(grandtotal.toFixed(2).toString().replace('.',','))); */

        balance = grandtotal - downpayment + rounding;

        let total_convert = total * currency_rate, tax_convert = tax * currency_rate, wtax_convert = wtax * currency_rate, rounding_convert = rounding * currency_rate, downpayment_convert = downpayment * currency_rate, balance_convert = balance * currency_rate;

        $('#total').text(
            formatRupiahIni(total.toFixed(2).toString().replace('.',','))
        );
        $('#total_convert').text(
            (total_convert >= 0 ? '' : '-') + formatRupiahIni(total_convert.toFixed(2).toString().replace('.',','))
        );
        $('#tax').text(
            formatRupiahIni(ppn.toFixed(2).toString().replace('.',','))
        );
        $('#tax_convert').text(
            (tax_convert >= 0 ? '' : '-') + formatRupiahIni(tax_convert.toFixed(2).toString().replace('.',','))
        );
        $('#wtax').val(
            formatRupiahIni(pph.toFixed(2).toString().replace('.',','))
        );
        $('#wtax_convert').text(
            (wtax_convert >= 0 ? '' : '-') + formatRupiahIni(wtax_convert.toFixed(2).toString().replace('.',','))
        );
        $('#balance').text(
            formatRupiahIni(balance.toFixed(2).toString().replace('.',','))
        );
        $('#balance_convert').text(
            (balance_convert >= 0 ? '' : '-') + formatRupiahIni(balance_convert.toFixed(2).toString().replace('.',','))
        );
    }

    function makeTreeOrg(data,link){
        var $ = go.GraphObject.make;

        myDiagram =
        $(go.Diagram, "myDiagramDiv",
        {
            initialContentAlignment: go.Spot.Center,
            "undoManager.isEnabled": true,
            layout: $(go.TreeLayout,
            {
                angle: 180,
                path: go.TreeLayout.PathSource,
                setsPortSpot: false,
                setsChildPortSpot: false,
                arrangement: go.TreeLayout.ArrangementHorizontal
            })
        });
        $("PanelExpanderButton", "METHODS",
            { row: 2, column: 1, alignment: go.Spot.TopRight },
            {
                visible: true,
                click: function(e, obj) {
                    var node = obj.part.parent;
                    var diagram = node.diagram;
                    var data = node.data;
                    diagram.startTransaction("Collapse/Expand Methods");
                    diagram.model.setDataProperty(data, "isTreeExpanded", !data.isTreeExpanded);
                    diagram.commitTransaction("Collapse/Expand Methods");
                }
            },
            new go.Binding("visible", "methods", function(arr) { return arr.length > 0; })
        );
        myDiagram.addDiagramListener("ObjectDoubleClicked", function(e) {
            var part = e.subject.part;
            if (part instanceof go.Link) {


            } else if (part instanceof go.Node) {
                window.open(part.data.url);
                if (part.isTreeExpanded) {
                    part.collapseTree();
                } else {
                    part.expandTree();
                }

            }
        });
        myDiagram.nodeTemplate =
        $(go.Node, "Auto",
            {
            locationSpot: go.Spot.Center,
            fromSpot: go.Spot.AllSides,
            toSpot: go.Spot.AllSides,
            portId: "",

            },
            { isTreeExpanded: false },
            $(go.Shape, { fill: "lightgrey", strokeWidth: 0 },
            new go.Binding("fill", "color")),
            $(go.Panel, "Table",
            { defaultRowSeparatorStroke: "black" },
            $(go.TextBlock,
                {
                row: 0, columnSpan: 2, margin: 3, alignment: go.Spot.Center,
                font: "bold 12pt sans-serif",
                isMultiline: false, editable: true
                },
                new go.Binding("text", "name").makeTwoWay()
            ),
            $(go.TextBlock, "Properties",
                { row: 1, font: "italic 10pt sans-serif" },
                new go.Binding("visible", "visible", function(v) { return !v; }).ofObject("PROPERTIES")
            ),
            $(go.Panel, "Vertical", { name: "PROPERTIES" },
                new go.Binding("itemArray", "properties"),
                {
                row: 1, margin: 3, stretch: go.GraphObject.Fill,
                defaultAlignment: go.Spot.Left,
                }
            ),

            $(go.Panel, "Auto",
                { portId: "r" },
                { margin: 6 },
                $(go.Shape, "Circle", { fill: "transparent", stroke: null, desiredSize: new go.Size(8, 8) })
            ),
            ),

            $("TreeExpanderButton",
            { alignment: go.Spot.Right, alignmentFocus: go.Spot.Right, width: 14, height: 14 }
            )
        );
        myDiagram.model.root = data[0].key;


        myDiagram.addDiagramListener("InitialLayoutCompleted", function(e) {
        setTimeout(function() {

            var rootKey = data[0].key;
            var rootNode = myDiagram.findNodeForKey(rootKey);
            if (rootNode !== null) {
                rootNode.collapseTree();
            }
        }, 100);
        });

        myDiagram.layout = $(go.TreeLayout);

        myDiagram.addDiagramListener("InitialLayoutCompleted", e => {
           e.diagram.findTreeRoots().each(r => r.expandTree(3));
            e.diagram.nodes.each(node => {
                node.findTreeChildrenNodes().each(child => child.expandTree(10));
            });
            e.diagram.nodes.each(node => {
                node.findTreeChildrenNodes().each(child => child.expandTree(10));
            });
        });

        myDiagram.model = $(go.GraphLinksModel,
        {
            copiesArrays: true,
            copiesArrayObjects: true,
            nodeDataArray: data,
            linkDataArray: link
        });

    }

    function printMultiSelect(){
        var formData = new FormData($('#form_data_print_multi')[0]);
        var table = $('#datatable_serverside').DataTable();
        var data = table.data().toArray();
        var etNumbers = data.map(item => item[1]);
        var path = window.location.pathname;
        path = path.replace(/^\/|\/$/g, '');


        var segments = path.split('/');
        var lastSegment = segments[segments.length - 1];
        formData.append('tabledata',etNumbers);
        formData.append('lastsegment',lastSegment);
        swal({
            title: "Apakah Anda ingin mengeprint dokumen ini?",
            text: "pastikan bahwa isian sudah benar.",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                    $.ajax({
                    url: '{{ Request::url() }}/print_by_range',
                    type: 'POST',
                    dataType: 'JSON',
                    data: formData,
                    contentType: false,
                    processData: false,
                    cache: true,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $('#validation_alert_multi').html('');
                        loadingOpen('.modal-content');
                    },
                    success: function(response) {
                        loadingClose('.modal-content');
                        if(response.status == 200) {
                            $('#modal5').modal('close');
                        /*  printService.submit({
                                'type': 'INVOICE',
                                'url': response.message
                            }) */
                            M.toast({
                                html: response.message
                            });
                        } else if(response.status == 422) {
                            $('#validation_alert_multi').show();
                            $('.modal-content').scrollTop(0);

                            swal({
                                title: 'Ups! Validation',
                                text: 'Check your form.',
                                icon: 'warning'
                            });

                            $.each(response.error, function(i, val) {
                                $.each(val, function(i, val) {
                                    $('#validation_alert_multi').append(`
                                        <div class="card-alert card red">
                                            <div class="card-content white-text">
                                                <p>` + val + `</p>
                                            </div>
                                            <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                        </div>
                                    `);
                                });
                            });
                        } else {
                            M.toast({
                                html: response.message
                            });
                        }
                    },
                    error: function() {
                        $('.modal-content').scrollTop(0);
                        loadingClose('.modal-content');
                        swal({
                            title: 'Ups!',
                            text: 'Check your internet connection.',
                            icon: 'error'
                        });
                    }

                });
            }
        });

    }

    function applyDocuments(type){
        swal({
            title: "Apakah anda yakin?",
            text: "Jika sudah ada di dalam tabel detail form, maka akan tergantikan dengan pilihan baru anda saat ini.",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                let passed = true, arr_id = [], arr_type = [], sametype = true;

                if(type == 'main'){
                    $.map(table_multi.rows('.selected').nodes(), function (item) {
                        arr_id.push($(item).data('id'));
                        arr_type.push($(item).data('type'));
                    });
                }

                if(type == 'dp'){
                    $.map(table_multi_dp.rows('.selected').nodes(), function (item) {
                        arr_id.push($(item).data('id'));
                        arr_type.push($(item).data('type'));
                    });
                }

                if(arr_type.length > 0){
                    let arrResult = arr_type.filter((element, index) => {
                        return arr_type.indexOf(element) === index;
                    });

                    let indexkuy = arrResult.indexOf('purchase_down_payments');
                    arrResult.splice(indexkuy, 1);

                    if(arrResult.length > 1){
                        sametype = false;
                    }
                }

                if(passed == true){
                    if(sametype == true){
                        $.ajax({
                            url: '{{ Request::url() }}/get_gr_lc',
                            type: 'POST',
                            dataType: 'JSON',
                            data: {
                                arr_id: arr_id,
                                arr_type: arr_type,
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            beforeSend: function() {
                                loadingOpen('.modal-content');
                            },
                            success: function(response) {
                                loadingClose('.modal-content');
                                if(type == 'main'){
                                    /* $('.row_detail').remove(); */
                                    if(response.details.length > 0){
                                        $.each(response.details, function(i, val) {
                                            var count = makeid(10);
                                            if(val.type == 'fund_request_details'){
                                                $('#last-row-detail').before(`
                                                    <tr class="row_detail">
                                                        <input type="hidden" name="arr_code[]" value="" data-id="` + count + `">
                                                        <input type="hidden" name="arr_frd_id[]" value="` + val.id + `" data-id="` + count + `">
                                                        <input type="hidden" name="arr_type[]" value="coas" data-id="` + count + `">
                                                        <input type="hidden" name="arr_total[]" value="0" data-id="` + count + `">
                                                        <input type="hidden" name="arr_tax[]" value="0" data-id="` + count + `">
                                                        <input type="hidden" name="arr_wtax[]" value="0" data-id="` + count + `">
                                                        <input type="hidden" name="arr_grandtotal[]" value="0" data-id="` + count + `">
                                                        <input type="hidden" name="arr_temp_qty[]" value="` + val.qty_balance + `" data-id="` + count + `">
                                                        <td class="center">
                                                            <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                                                                <i class="material-icons">delete</i>
                                                            </a>
                                                        </td>
                                                        <td class="center">
                                                            <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]"></select>
                                                        </td>
                                                        <td class="center">
                                                            ` + val.rawcode + `
                                                        </td>
                                                        <td>
                                                            <input type="text" name="arr_note[]" value="` + val.note + `" data-id="` + count + `">
                                                        </td>
                                                        <td>
                                                            <input type="text" name="arr_note2[]" value="-" data-id="` + count + `">
                                                        </td>
                                                        <td class="center">
                                                            -
                                                        </td>
                                                        <td class="center">
                                                            -
                                                        </td>
                                                        <td class="center">
                                                            -
                                                        </td>
                                                        <td class="center">
                                                            -
                                                        </td>
                                                        <td class="center">
                                                            -
                                                        </td>
                                                        <td class="center">
                                                            <input class="browser-default" type="text" name="arr_qty[]" onfocus="emptyThis(this);" value="` + val.qty_balance + `" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();">
                                                        </td>
                                                        <td class="center">
                                                            -
                                                        </td>
                                                        <td class="center">
                                                            -
                                                        </td>
                                                        <td class="center">
                                                            <input class="browser-default" type="text" name="arr_price[]" onfocus="emptyThis(this);" value="` + val.price + `" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();">
                                                        </td>
                                                        <td class="center">
                                                            -
                                                        </td>
                                                        <td class="center">
                                                            -
                                                        </td>
                                                        <td class="right-align row_total" id="row_total` + count + `">
                                                            ` + val.total + `
                                                        </td>
                                                        <td class="center">
                                                            <select class="browser-default" id="arr_percent_tax` + count + `" name="arr_percent_tax[]" data-id="` + count + `" onchange="countAll();">
                                                                <option value="0.00000" data-id="">-- Non-PPN --</option>
                                                                @foreach ($tax as $row1)
                                                                    <option value="{{ $row1->percentage }}" data-id="{{ $row1->id }}">{{ $row1->name.' - '.number_format($row1->percentage,2,',','.').'%' }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="center">
                                                            <select class="browser-default" id="arr_include_tax` + count + `" name="arr_include_tax[]" data-id="` + count + `" onchange="countAll();">
                                                                <option value="0">Tidak</option>
                                                                <option value="1">Ya</option>
                                                            </select>
                                                        </td>
                                                        <td class="right-align" id="row_tax` + count + `">
                                                            <input class="browser-default" type="text" name="arr_tax[]" value="0" data-id="` + count + `" onkeyup="formatRupiah(this);" readonly>
                                                        </td>
                                                        <td class="center">
                                                            <select class="browser-default" id="arr_percent_wtax` + count + `" name="arr_percent_wtax[]" data-id="` + count + `" onchange="countAll();">
                                                                <option value="0.00000" data-id="">-- Non-PPh --</option>
                                                                @foreach ($wtax as $row2)
                                                                    <option value="{{ $row2->percentage }}" data-id="{{ $row2->id }}">{{ $row2->name.' - '.number_format($row2->percentage,2,',','.').'%' }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="right-align" id="row_wtax` + count + `">
                                                            <input class="browser-default" type="text" name="arr_wtax[]" value="0" data-id="` + count + `" onkeyup="formatRupiah(this);" readonly>
                                                        </td>
                                                        <td class="right-align row_grandtotal" id="row_grandtotal` + count + `">
                                                            ` + val.grandtotal + `
                                                        </td>

                                                        <td class="center">
                                                            <select class="browser-default" id="arr_place` + count + `" name="arr_place[]">
                                                                @foreach ($place as $rowplace)
                                                                    <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="browser-default" id="arr_line` + count + `" name="arr_line[]" onchange="changePlace(this);">
                                                                <option value="">--{{ __('translations.empty') }}--</option>
                                                                @foreach ($line as $rowline)
                                                                    <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="browser-default" id="arr_machine` + count + `" name="arr_machine[]" onchange="changeLine(this);">
                                                                <option value="">--{{ __('translations.empty') }}--</option>
                                                                @foreach ($machine as $rowmachine)
                                                                    <option value="{{ $rowmachine->id }}" data-line="{{ $rowmachine->line_id }}">{{ $rowmachine->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="center">
                                                            <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                                                                @foreach ($department as $rowdept)
                                                                    <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="center">
                                                            <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">
                                                                <option value="">--{{ __('translations.empty') }}--</option>
                                                                @foreach ($warehouse as $row)
                                                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="center">
                                                            <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                                                        </td>
                                                    </tr>
                                                `);
                                                select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa_no_cash") }}');
                                                select2ServerSide('#arr_project' + count, '{{ url("admin/select2/project") }}');
                                                if(val.place_id){
                                                    $('#arr_place' + count).val(val.place_id);
                                                }
                                                if(val.line_id){
                                                    $('#arr_line' + count).val(val.line_id);
                                                }
                                                if(val.machine_id){
                                                    $('#arr_machine' + count).val(val.machine_id);
                                                }
                                                if(val.department_id){
                                                    $('#arr_department' + count).val(val.department_id);
                                                }
                                                if(val.project_id){
                                                    $('#arr_project' + count).append(`
                                                        <option value="` + val.project_id + `">` + val.project_name + `</value>
                                                    `);
                                                }
                                                $('#due_date').val(val.due_date);
                                            }else{
                                                $('#last-row-detail').before(`
                                                    <tr class="row_detail">
                                                        <input type="hidden" name="arr_type[]" value="` + val.type + `" data-id="` + count + `">
                                                        <input type="hidden" name="arr_price[]" value="` + val.raw_price + `" data-id="` + count + `">
                                                        <input type="hidden" name="arr_total[]" value="` + val.total + `" data-id="` + count + `">
                                                        <input type="hidden" name="arr_grandtotal[]" value="` + val.grandtotal + `" data-id="` + count + `">
                                                        <input type="hidden" name="arr_tax[]" value="` + val.tax + `" data-id="` + count + `">
                                                        <input type="hidden" name="arr_wtax[]" value="` + val.wtax + `" data-id="` + count + `">
                                                        <input type="hidden" id="arr_place` + count + `" name="arr_place[]" value="` + val.place_id + `" data-id="` + count + `">
                                                        <input type="hidden" id="arr_line` + count + `" name="arr_line[]" value="` + val.line_id + `" data-id="` + count + `">
                                                        <input type="hidden" id="arr_machine` + count + `" name="arr_machine[]" value="` + val.machine_id + `" data-id="` + count + `">
                                                        <input type="hidden" id="arr_department` + count + `" name="arr_department[]" value="` + val.department_id + `" data-id="` + count + `">
                                                        <input type="hidden" id="arr_warehouse` + count + `" name="arr_warehouse[]" value="` + val.warehouse_id + `" data-id="` + count + `">
                                                        <input type="hidden" id="arr_project` + count + `" name="arr_project[]" value="` + val.project_id + `" data-id="` + count + `">
                                                        <input type="hidden" name="arr_code[]" value="` + val.id + `" data-id="` + count + `">
                                                        <input type="hidden" name="arr_temp_qty[]" value="` + val.qty_balance + `" data-id="` + count + `">
                                                        <td class="center">
                                                            <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                                                                <i class="material-icons">delete</i>
                                                            </a>
                                                        </td>
                                                        <td class="center">
                                                            ` + val.rawcode + `
                                                        </td>
                                                        <td>
                                                            <input type="text" name="arr_note[]" value="` + val.note + `" data-id="` + count + `">
                                                        </td>
                                                        <td>
                                                            <input type="text" name="arr_note2[]" value="-" data-id="` + count + `">
                                                        </td>
                                                        <td class="center">
                                                            ` + val.purchase_no + `
                                                        </td>
                                                        <td class="center">
                                                            ` + val.delivery_no + `
                                                        </td>
                                                        <td class="">
                                                            ` + val.name + `
                                                        </td>
                                                        <td class="center">
                                                            ` + val.buy_unit + `
                                                        </td>
                                                        <td class="center">
                                                            ` + val.qty_received + `
                                                        </td>
                                                        <td class="center">
                                                            ` + val.qty_returned + `
                                                        </td>
                                                        <td class="center">
                                                            <input class="browser-default" type="text" name="arr_qty[]" onfocus="emptyThis(this);" value="` + val.qty_balance + `" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();countStock(this);" data-conversion="` + val.qty_conversion + `">
                                                        </td>
                                                        <td class="center" id="qty_stock` + count + `">
                                                            ` + val.qty_stock + `
                                                        </td>
                                                        <td class="center" id="unit_stock` + count + `">
                                                            ` + val.unit_stock + `
                                                        </td>
                                                        <td class="right-align">
                                                            ` + val.price + `
                                                        </td>
                                                        <td class="center">
                                                            ` + val.post_date + `
                                                        </td>
                                                        <td class="center">
                                                            ` + val.due_date + `
                                                        </td>
                                                        <td class="right-align row_total" id="row_total` + count + `">
                                                            ` + val.total + `
                                                        </td>
                                                        <td class="center">
                                                            <select class="browser-default" id="arr_percent_tax` + count + `" name="arr_percent_tax[]" data-id="` + count + `" onchange="countAll();">
                                                                <option value="0.00000" data-id="">-- Non-PPN --</option>
                                                                @foreach ($tax as $row1)
                                                                    <option value="{{ $row1->percentage }}" data-id="{{ $row1->id }}">{{ $row1->name.' - '.number_format($row1->percentage,2,',','.').'%' }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="center">
                                                            <select class="browser-default" id="arr_include_tax` + count + `" name="arr_include_tax[]" data-id="` + count + `" onchange="countAll();">
                                                                <option value="0">Tidak</option>
                                                                <option value="1">Ya</option>
                                                            </select>
                                                        </td>
                                                        <td class="right-align" id="row_tax` + count + `">
                                                            ` + val.tax + `
                                                        </td>
                                                        <td class="center">
                                                            <select class="browser-default" id="arr_percent_wtax` + count + `" name="arr_percent_wtax[]" data-id="` + count + `" onchange="countAll();">
                                                                <option value="0.00000" data-id="">-- Non-PPh --</option>
                                                                @foreach ($wtax as $row2)
                                                                    <option value="{{ $row2->percentage }}" data-id="{{ $row2->id }}">{{ $row2->name.' - '.number_format($row2->percentage,2,',','.').'%' }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="right-align" id="row_wtax` + count + `">
                                                            ` + val.wtax + `
                                                        </td>
                                                        <td class="right-align row_grandtotal" id="row_grandtotal` + count + `">
                                                            ` + val.grandtotal + `
                                                        </td>

                                                        <td class="center">
                                                            ` + val.place_name + `
                                                        </td>
                                                        <td class="center">
                                                            ` + val.line_name + `
                                                        </td>
                                                        <td class="center">
                                                            ` + val.machine_name + `
                                                        </td>
                                                        <td class="center">
                                                            ` + val.department_name + `
                                                        </td>
                                                        <td class="center">
                                                            ` + val.warehouse_name + `
                                                        </td>
                                                        <td class="center">
                                                            ` + val.project_name + `
                                                        </td>
                                                    </tr>
                                                `);
                                            }

                                            $('#arr_percent_tax' + count).val(val.percent_tax);
                                            $('#arr_percent_wtax' + count).val(val.percent_wtax);
                                            /* $('#arr_include_tax' + count).val(val.include_tax); */

                                            if(val.is_expedition){
                                                $('#arr_percent_wtax' + count).val('2.00000');
                                            }

                                            $('#top').val(val.top);

                                            $('#received_date').val(val.received_date);
                                            $('#due_date').val(val.due_date);
                                            $('#document_date').val(val.document_date);
                                            $('#document_no').val(val.document_no);
                                            $('#tax_no').val(val.tax_no);
                                            $('#tax_cut_no').val(val.tax_cut_no);
                                            $('#cut_date').val(val.cut_date);
                                            $('#spk_no').val(val.spk_no);
                                            $('#invoice_no').val(val.invoice_no);
                                            $('#note').val(val.header_note);
                                            $('#currency_rate').val(val.currency_rate);
                                            $('#currency_id').val(val.currency_id).formSelect();
                                            $('#rounding').val(val.rounding);

                                            if(!val.due_date && val.top && val.received_date){
                                                addDays();
                                            }
                                        });
                                    }else{
                                        $('.row_detail').remove();
                                        $('#total,#tax,#balance').text('0,00');
                                    }
                                }

                                if(type == 'dp'){
                                    /* $('#body-detail-dp').empty(); */
                                    if(response.downpayments.length > 0){
                                        $.each(response.downpayments, function(i, val) {
                                            var count = makeid(10);
                                            $('#body-detail-dp').append(`
                                                <tr class="row_detail_dp">
                                                    <input type="hidden" name="arr_dp_code[]" value="` + val.code + `" data-id="` + count + `">
                                                    <td class="center">
                                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail-dp" href="javascript:void(0);">
                                                            <i class="material-icons">delete</i>
                                                        </a>
                                                    </td>
                                                    <td class="center">
                                                        ` + val.rawcode + `
                                                    </td>
                                                    <td class="center">
                                                        ` + val.pyr_code + `
                                                    </td>
                                                    <td class="center">
                                                        ` + val.post_date + `
                                                    </td>
                                                    <td class="center">
                                                        ` + val.grandtotal + `
                                                    </td>
                                                    <td class="center">
                                                        ` + val.balance + `
                                                    </td>
                                                    <td class="center">
                                                        <input name="arr_nominal[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.balance + `" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100% !important;" id="rowNominal`+ count +`">
                                                    </td>
                                                </tr>
                                            `);
                                        });
                                    }else{
                                        $('#body-detail-dp').empty().append(`
                                            <tr id="empty-detail-dp">
                                                <td colspan="7" class="center">
                                                    Pilih supplier/vendor untuk memulai...
                                                </td>
                                            </tr>
                                        `);

                                        $('#downpayment').val('0,00');
                                    }
                                }

                                if(!$('#received_date').val()){
                                    addDays();
                                }

                                $('.modal-content').scrollTop(0);
                                M.updateTextFields();

                                /* start count */

                                countAll();

                                /* end count */
                            },
                            error: function() {
                                $('.modal-content').scrollTop(0);
                                loadingClose('.modal-content');
                                swal({
                                    title: 'Ups!',
                                    text: 'Check your internet connection.',
                                    icon: 'error'
                                });
                            }
                        });
                        if(type == 'main'){
                            $('#modal4').modal('close');
                        }
                        if(type == 'dp'){
                            $('#modal7').modal('close');
                        }
                    }else{
                        swal({
                            title: 'Ups!',
                            text: 'Anda tidak boleh memilih tipe dokumen yang berbeda.',
                            icon: 'warning'
                        });
                    }
                }else{
                    swal({
                        title: 'Ups!',
                        text: 'Silahkan, pilih GRPO, LC, atau PO Jasa yang ingin anda masukkan.',
                        icon: 'warning'
                    });
                }
            }
        });
    }

    function countStock(element){
        let code = $(element).data('id'),
        qty = parseFloat($(element).val().replaceAll(".", "").replaceAll(",",".")),
        conversion = parseFloat($(element).data('conversion').toString().replaceAll(".", "").replaceAll(",",".")),
        qtyConversion = conversion * qty;

        $('#qty_stock' + code).text(formatRupiahIni(qtyConversion.toFixed(3).toString().replace('.',',')));
    }

    function viewStructureTree(id){
        $.ajax({
            url: '{{ Request::url() }}/viewstructuretree',
            type: 'GET',
            dataType: 'JSON',
            data: {
                id : id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {

            },
            success: function(response) {
                loadingClose('.modal-content');
                makeTreeOrg(response.message,response.link);

                $('#modal3').modal('open');
            },
            error: function() {
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
            }
        });
    }

    function getAccountData(kind){
        if($('#account_id').val() && $('#type_detail').val() == '1'){
            $.ajax({
                url: '{{ Request::url() }}/get_account_data',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: $('#account_id').val()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    loadingClose('.modal-content');
                    if(kind == '1'){
                        $('#modal4').modal('open');
                        $('#account_name').text($('#account_id').select2('data')[0].text);

                        if(response.details.length > 0){

                            $.each(response.details, function(i, val) {
                                $('#body-detail-multi').append(`
                                    <tr data-type="` + val.type + `" data-id="` + val.id + `">
                                        <td>
                                            ` + val.code + `
                                        </td>
                                        <td>
                                            ` + val.list_item + `
                                        </td>
                                        <td>
                                            ` + val.purchase_order + `
                                        </td>
                                        <td class="center">
                                            ` + val.post_date + `
                                        </td>
                                        <td class="right-align">
                                            ` + val.grandtotal + `
                                        </td>
                                        <td class="right-align">
                                            ` + val.invoice + `
                                        </td>
                                        <td class="right-align">
                                            ` + val.balance + `
                                        </td>
                                        <td class="center-align">
                                            ` + val.info + `
                                        </td>
                                    </tr>
                                `);
                            });
                        }
                    }else if(kind == '2'){
                        $('#modal7').modal('open');
                        $('#account_name_dp').text($('#account_id').select2('data')[0].text);
                        if(response.downpayments.length > 0){
                            $.each(response.downpayments, function(i, val) {
                                var count = makeid(10);
                                $('#body-detail-dp-multi').append(`
                                    <tr data-type="` + val.type + `" data-id="` + val.id + `">
                                        <td>
                                            ` + val.rawcode + `
                                        </td>
                                        <td>
                                            ` + val.pyr_code + `
                                        </td>
                                        <td class="center">
                                            ` + val.post_date + `
                                        </td>
                                        <td class="right-align">
                                            ` + val.grandtotal + `
                                        </td>
                                        <td class="right-align">
                                            ` + val.balance + `
                                        </td>
                                        <td class="center">
                                            ` + val.note + `
                                        </td>
                                    </tr>
                                `);
                            });
                        }
                    }

                    $('.modal-content').scrollTop(0);
                    M.updateTextFields();
                },
                error: function() {
                    $('.modal-content').scrollTop(0);
                    loadingClose('.modal-content');
                    swal({
                        title: 'Ups!',
                        text: 'Check your internet connection.',
                        icon: 'error'
                    });
                }
            });
        }else{
            if(kind == '1'){
                $('.row_detail').remove();
            }else{
                $('#body-detail-dp').empty().append(`
                    <tr id="empty-detail-dp">
                        <td colspan="7" class="center">
                            Pilih supplier/vendor untuk memulai...
                        </td>
                    </tr>
                `);
            }
            countAll();
        }
    }

    function addItem(){
        var count = makeid(10);
        $('#last-row-detail').before(`
            <tr class="row_detail">
                <input type="hidden" name="arr_code[]" value="" data-id="` + count + `">
                <input type="hidden" name="arr_frd_id[]" value="" data-id="` + count + `">
                <input type="hidden" name="arr_type[]" value="coas" data-id="` + count + `">
                <input type="hidden" name="arr_total[]" value="0" data-id="` + count + `">
                <input type="hidden" name="arr_tax[]" value="0" data-id="` + count + `">
                <input type="hidden" name="arr_wtax[]" value="0" data-id="` + count + `">
                <input type="hidden" name="arr_grandtotal[]" value="0" data-id="` + count + `">
                <input type="hidden" name="arr_temp_qty[]" value="1" data-id="` + count + `">
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]"></select>
                </td>
                <td>
                    <input type="text" name="arr_note[]" value="Keterangan 1..." data-id="` + count + `">
                </td>
                <td>
                    <input type="text" name="arr_note2[]" value="Keterangan 2..." data-id="` + count + `">
                </td>
                <td class="center">
                    -
                </td>
                <td class="center">
                    -
                </td>
                <td class="center">
                    -
                </td>
                <td class="center">
                    -
                </td>
                <td class="center">
                    -
                </td>
                <td class="center">
                    -
                </td>
                <td class="center">
                    <input class="browser-default" type="text" name="arr_qty[]" onfocus="emptyThis(this);" value="0" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();">
                </td>
                <td class="center">
                    -
                </td>
                <td class="center">
                    -
                </td>
                <td class="center">
                    <input class="browser-default" type="text" name="arr_price[]" onfocus="emptyThis(this);" value="0" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();">
                </td>
                <td class="center">
                    -
                </td>
                <td class="center">
                    -
                </td>
                <td class="right-align row_total" id="row_total` + count + `">
                    0
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_percent_tax` + count + `" name="arr_percent_tax[]" data-id="` + count + `" onchange="countAll();">
                        <option value="0.0000" data-id="">-- Non-PPN --</option>
                        @foreach ($tax as $row1)
                            <option value="{{ $row1->percentage }}" data-id="{{ $row1->id }}">{{ $row1->name.' - '.number_format($row1->percentage,2,',','.').'%' }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_include_tax` + count + `" name="arr_include_tax[]" data-id="` + count + `" onchange="countAll();">
                        <option value="0">Tidak</option>
                        <option value="1">Ya</option>
                    </select>
                </td>
                <td class="right-align" id="row_tax` + count + `">
                    <input class="browser-default" type="text" name="arr_tax[]" value="0" data-id="` + count + `" onkeyup="formatRupiah(this);" readonly>
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_percent_wtax` + count + `" name="arr_percent_wtax[]" data-id="` + count + `" onchange="countAll();">
                        <option value="0.00000" data-id="">-- Non-PPh --</option>
                        @foreach ($wtax as $row2)
                            <option value="{{ $row2->percentage }}" data-id="{{ $row2->id }}">{{ $row2->name.' - '.number_format($row2->percentage,2,',','.').'%' }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="right-align" id="row_wtax` + count + `">
                    <input class="browser-default" type="text" name="arr_wtax[]" value="0" data-id="` + count + `" onkeyup="formatRupiah(this);" readonly>
                </td>
                <td class="right-align row_grandtotal" id="row_grandtotal` + count + `">
                    0
                </td>

                <td class="center">
                    <select class="browser-default" id="arr_place` + count + `" name="arr_place[]">
                        @foreach ($place as $rowplace)
                            <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select class="browser-default" id="arr_line` + count + `" name="arr_line[]" onchange="changePlace(this);">
                        <option value="">--{{ __('translations.empty') }}--</option>
                        @foreach ($line as $rowline)
                            <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select class="browser-default" id="arr_machine` + count + `" name="arr_machine[]" onchange="changeLine(this);">
                        <option value="">--{{ __('translations.empty') }}--</option>
                        @foreach ($machine as $rowmachine)
                            <option value="{{ $rowmachine->id }}" data-line="{{ $rowmachine->line_id }}">{{ $rowmachine->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                        @foreach ($department as $rowdept)
                            <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">
                        <option value="">--{{ __('translations.empty') }}--</option>
                        @foreach ($warehouse as $row)
                            <option value="{{ $row->id }}">{{ $row->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                </td>
            </tr>
        `);
        select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa_no_cash") }}');
        select2ServerSide('#arr_project' + count, '{{ url("admin/select2/project") }}');
    }

    function changePlace(element){
        $(element).parent().next().find('select[name="arr_machine[]"] option').show();
        if($(element).val()){
            $(element).parent().prev().find('select[name="arr_place[]"]').val($(element).find(':selected').data('place'));
            $(element).parent().next().find('select[name="arr_machine[]"] option[data-line!="' + $(element).val() + '"]').hide();
        }else{
            $(element).parent().prev().find('select[name="arr_place[]"]').val($(element).parent().prev().find('select[name="arr_place[]"] option:first').val());
        }
    }

    function changeLine(element){
        if($(element).val()){
            $(element).parent().prev().find('select[name="arr_line[]"]').val($(element).find(':selected').data('line')).trigger('change');
        }else{
            $(element).parent().prev().find('select[name="arr_line[]"]').val($(element).parent().prev().find('select[name="arr_line[]"] option:first').val()).trigger('change');
        }
    }

    function countAll(){
        if($('#type_detail').val() == '1'){
            var total = 0, tax = 0, grandtotal = 0, balance = 0, wtax = 0, downpayment = 0, rounding = parseFloat($('#rounding').val().replaceAll(".", "").replaceAll(",",".")), currency_rate = parseFloat($('#currency_rate').val().replaceAll(".", "").replaceAll(",","."));

            $('input[name^="arr_code"]').each(function(){
                let element = $(this);
                var rowgrandtotal = 0, rowtotal = 0, rowtax = 0, rowwtax = 0, percent_tax = parseFloat($('select[name^="arr_percent_tax"][data-id="' + element.data('id') + '"]').val()), percent_wtax = parseFloat($('select[name^="arr_percent_wtax"][data-id="' + element.data('id') + '"]').val()), rowprice = parseFloat($('input[name^="arr_price"][data-id="' + element.data('id') + '"]').val().replaceAll(".", "").replaceAll(",",".")), rowqty = parseFloat($('input[name^="arr_qty"][data-id="' + element.data('id') + '"]').val().replaceAll(".", "").replaceAll(",","."));
                rowtotal = rowprice * rowqty;
                if(percent_tax > 0 && $('#arr_include_tax' + element.data('id')).val() == '1'){
                    rowtotal = rowtotal / (1 + (percent_tax / 100));
                }
                rowtotal = Math.round(rowtotal * 100) / 100;
                rowtax = Math.floor(rowtotal * (percent_tax / 100));
                rowwtax = Math.floor(rowtotal * (percent_wtax / 100));
                $('input[name^="arr_total"][data-id="' + element.data('id') + '"]').val(
                    (rowtotal >= 0 ? '' : '-') + formatRupiahIni(rowtotal.toFixed(2).toString().replace('.',','))
                );
                $('#row_total' + element.data('id')).text(
                    (rowtotal >= 0 ? '' : '-') + formatRupiahIni(rowtotal.toFixed(2).toString().replace('.',','))
                );
                $('input[name^="arr_tax"][data-id="' + element.data('id') + '"]').val(
                    (rowtax >= 0 ? '' : '-') + formatRupiahIni(rowtax.toFixed(2).toString().replace('.',','))
                );
                $('#row_tax' + element.data('id')).text(
                    (rowtax >= 0 ? '' : '-') + formatRupiahIni(rowtax.toFixed(2).toString().replace('.',','))
                );
                $('input[name^="arr_wtax"][data-id="' + element.data('id') + '"]').val(
                    (rowwtax >= 0 ? '' : '-') + formatRupiahIni(rowwtax.toFixed(2).toString().replace('.',','))
                );
                $('#row_wtax' + element.data('id')).text(
                    (rowwtax >= 0 ? '' : '-') + formatRupiahIni(rowwtax.toFixed(2).toString().replace('.',','))
                );
                total += rowtotal;
                tax += rowtax;
                wtax += rowwtax;
                rowgrandtotal = rowtotal + rowtax - rowwtax;
                $('input[name^="arr_grandtotal"][data-id="' + element.data('id') + '"]').val(
                    (rowgrandtotal >= 0 ? '' : '-') + formatRupiahIni(rowgrandtotal.toFixed(2).toString().replace('.',','))
                );
                $('#row_grandtotal' + element.data('id')).text(
                    (rowgrandtotal >= 0 ? '' : '-') + formatRupiahIni(rowgrandtotal.toFixed(2).toString().replace('.',','))
                );
            });

            grandtotal = total + tax - wtax + rounding;

            $('input[name^="arr_dp_code"]').each(function(index){
                downpayment += parseFloat($('input[name^="arr_nominal"]').eq(index).val().replaceAll(".", "").replaceAll(",","."));
            });

            balance = grandtotal - downpayment;

            let total_convert = total * currency_rate, tax_convert = tax * currency_rate, wtax_convert = wtax * currency_rate, rounding_convert = rounding * currency_rate, downpayment_convert = downpayment * currency_rate, balance_convert = balance * currency_rate;

            $('#downpayment').val(
                (downpayment >= 0 ? '' : '-') + formatRupiahIni(downpayment.toFixed(2).replace('.',','))
            );
            $('#downpayment_convert').text(
                (downpayment_convert >= 0 ? '' : '-') + formatRupiahIni(downpayment_convert.toFixed(2).replace('.',','))
            );
            $('#total').text(
                (total >= 0 ? '' : '-') + formatRupiahIni(total.toFixed(2).toString().replace('.',','))
            );
            $('#total_convert').text(
                (total_convert >= 0 ? '' : '-') + formatRupiahIni(total_convert.toFixed(2).toString().replace('.',','))
            );
            $('#tax').text(
                (tax >= 0 ? '' : '-') + formatRupiahIni(tax.toFixed(2).toString().replace('.',','))
            );
            $('#tax_convert').text(
                (tax_convert >= 0 ? '' : '-') + formatRupiahIni(tax_convert.toFixed(2).toString().replace('.',','))
            );
            $('#wtax').val(
                (wtax >= 0 ? '' : '-') + formatRupiahIni(wtax.toFixed(2).toString().replace('.',','))
            );
            $('#wtax_convert').text(
                (wtax_convert >= 0 ? '' : '-') + formatRupiahIni(wtax_convert.toFixed(2).toString().replace('.',','))
            );
            $('#rounding_convert').text(
                (rounding_convert >= 0 ? '' : '-') + formatRupiahIni(rounding_convert.toFixed(2).toString().replace('.',','))
            );
            $('#balance').text(
                (balance >= 0 ? '' : '-') + formatRupiahIni(balance.toFixed(2).toString().replace('.',','))
            );
            $('#balance_convert').text(
                (balance_convert >= 0 ? '' : '-') + formatRupiahIni(balance_convert.toFixed(2).toString().replace('.',','))
            );
        }else if($('#type_detail').val() == '2'){
            countAllMulti();
        }
    }

    function countGrandtotal(){
        if($('#type_detail').val() == '1'){
            let total = parseFloat($('#total').text().replaceAll(".", "").replaceAll(",",".")), tax = parseFloat($('#tax').text().replaceAll(".", "").replaceAll(",",".")), wtax = parseFloat($('#wtax').val().replaceAll(".", "").replaceAll(",",".")), downpayment = 0, balance = 0, rounding = parseFloat($('#rounding').val().replaceAll(".", "").replaceAll(",",".")), currency_rate = parseFloat($('#currency_rate').val().replaceAll(".", "").replaceAll(",",".")), rounding_convert = rounding * currency_rate;

            let grandtotal = total + tax - wtax + rounding;

            $('input[name^="arr_dp_code"]').each(function(index){
                downpayment += parseFloat($('input[name^="arr_nominal"]').eq(index).val().replaceAll(".", "").replaceAll(",","."));
            });

            balance = grandtotal - downpayment;

            let balance_convert = balance * currency_rate;

            $('#rounding_convert').text(
                (rounding_convert >= 0 ? '' : '-') + formatRupiahIni(rounding_convert.toFixed(2).toString().replace('.',','))
            );

            $('#balance').text(
                (balance >= 0 ? '' : '-') + formatRupiahIni(balance.toFixed(2).toString().replace('.',','))
            );

            $('#balance_convert').text(
                (balance_convert >= 0 ? '' : '-') + formatRupiahIni(balance_convert.toFixed(2).toString().replace('.',','))
            );

            $('input[name^="arr_code"]').each(function(){
                let element = $(this);
                var rowgrandtotal = 0, rowtotal = 0, rowtax = 0, rowwtax = 0, percent_tax = parseFloat($('select[name^="arr_percent_tax"][data-id="' + element.data('id') + '"]').val()), percent_wtax = parseFloat($('select[name^="arr_percent_wtax"][data-id="' + element.data('id') + '"]').val()), rowprice = parseFloat($('input[name^="arr_price"][data-id="' + element.data('id') + '"]').val().replaceAll(".", "").replaceAll(",",".")), rowqty = parseFloat($('input[name^="arr_qty"][data-id="' + element.data('id') + '"]').val().replaceAll(".", "").replaceAll(",","."));
                rowtotal = rowprice * rowqty;
                if(percent_tax > 0 && $('#arr_include_tax' + element.data('id')).val() == '1'){
                    rowtotal = rowtotal / (1 + (percent_tax / 100));
                }
                rowtotal = Math.round(rowtotal * 100) / 100;
                rowtax = Math.floor(rowtotal * (percent_tax / 100));
                rowwtax = Math.floor(rowtotal * (percent_wtax / 100));
                $('input[name^="arr_total"][data-id="' + element.data('id') + '"]').val(
                    (rowtotal >= 0 ? '' : '-') + formatRupiahIni(rowtotal.toFixed(2).toString().replace('.',','))
                );
                $('#row_total' + element.data('id')).text(
                    (rowtotal >= 0 ? '' : '-') + formatRupiahIni(rowtotal.toFixed(2).toString().replace('.',','))
                );
                $('input[name^="arr_tax"][data-id="' + element.data('id') + '"]').val(
                    (rowtax >= 0 ? '' : '-') + formatRupiahIni(rowtax.toFixed(2).toString().replace('.',','))
                );
                $('#row_tax' + element.data('id')).text(
                    (rowtax >= 0 ? '' : '-') + formatRupiahIni(rowtax.toFixed(2).toString().replace('.',','))
                );
                $('input[name^="arr_wtax"][data-id="' + element.data('id') + '"]').val(
                    (rowwtax >= 0 ? '' : '-') + formatRupiahIni(rowwtax.toFixed(2).toString().replace('.',','))
                );
                $('#row_wtax' + element.data('id')).text(
                    (rowwtax >= 0 ? '' : '-') + formatRupiahIni(rowwtax.toFixed(2).toString().replace('.',','))
                );
                rowgrandtotal = rowtotal + rowtax - rowwtax;
                $('input[name^="arr_grandtotal"][data-id="' + element.data('id') + '"]').val(
                    (rowgrandtotal >= 0 ? '' : '-') + formatRupiahIni(rowgrandtotal.toFixed(2).toString().replace('.',','))
                );
                $('#row_grandtotal' + element.data('id')).text(
                    (rowgrandtotal >= 0 ? '' : '-') + formatRupiahIni(rowgrandtotal.toFixed(2).toString().replace('.',','))
                );
            });
        }else if($('#type_detail').val() == '2'){
            let total = 0, ppn = 0, pph = parseFloat($('#wtax').val().replaceAll(".", "").replaceAll(",",".")), grandtotal = 0, rounding = parseFloat($('#rounding').val().replaceAll(".", "").replaceAll(",",".")), downpayment = parseFloat($('#downpayment').val().replaceAll(".", "").replaceAll(",",".")), balance = 0;

            $('input[name^="arr_multi_total"]').each(function(index){
                total += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
            });
            $('input[name^="arr_multi_ppn"]').each(function(index){
                ppn += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
            });
            $('input[name^="arr_multi_grandtotal"]').each(function(index){
                grandtotal += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
            });

            balance = total + ppn - pph - downpayment + rounding;

            $('#total').text(
                formatRupiahIni(total.toFixed(2).toString().replace('.',','))
            );
            $('#tax').text(
                formatRupiahIni(ppn.toFixed(2).toString().replace('.',','))
            );
            $('#balance').text(
                formatRupiahIni(balance.toFixed(2).toString().replace('.',','))
            );
        }
    }

    function loadDataTable() {
		window.table = $('#datatable_serverside').DataTable({
            "scrollCollapse": true,
            "scrollY": '400px',
            "responsive": false,
            "scrollX": true,
            "stateSave": true,
            "serverSide": true,
            "deferRender": true,
            "destroy": true,
            "iDisplayInLength": 10,
            "fixedColumns": {
                left: 2,
                right: 1
            },
            "order": [[0, 'desc']],
            dom: 'Blfrtip',
            buttons: [
                'selectNone'
            ],
            "language": {
                "lengthMenu": "Menampilkan _MENU_ data per halaman",
                "zeroRecords": "Data tidak ditemukan / kosong",
                "info": "Menampilkan halaman _PAGE_ / _PAGES_ dari total _TOTAL_ data",
                "infoEmpty": "Data tidak ditemukan / kosong",
                "infoFiltered": "(disaring dari _MAX_ total data)",
                "search": "Cari",
                "paginate": {
                    first:      "<<",
                    previous:   "<",
                    next:       ">",
                    last:       ">>"
                },
                "buttons": {
                    selectAll: "Pilih semua",
                    selectNone: "Hapus pilihan"
                },
                "select": {
                    rows: "%d baris terpilih"
                }
            },
            select: {
                style: 'multi',
                selector: 'td:not(.btn-floating)'
            },
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    'status' : $('#filter_status').val(),
                    type : $('#filter_type').val(),
                    'account_id[]' : $('#filter_account').val(),
                    company_id : $('#filter_company').val(),
                    start_date : $('#start_date').val(),
                    finish_date : $('#finish_date').val(),
                    'modedata' : '{{ $modedata }}',
                },
                beforeSend: function() {
                    loadingOpen('#datatable_serverside');
                },
                complete: function() {
                    loadingClose('#datatable_serverside');
                },
                error: function() {
                    loadingClose('#datatable_serverside');
                    swal({
                        title: 'Ups!',
                        text: 'Check your internet connection.',
                        icon: 'error'
                    });
                }
            },
            columns: [
                { name: 'id', searchable: false, className: 'center-align details-control' },
                { name: 'code', className: 'center-align' },
                { name: 'user_id', className: 'center-align' },
                { name: 'account_id', className: 'center-align' },
                { name: 'company_id', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'received_date', className: 'center-align' },
                { name: 'due_date', className: 'center-align' },
                { name: 'document_date', className: 'center-align' },
                { name: 'currency_id', className: 'center-align' },
                { name: 'currency_rate', className: 'center-align' },
                { name: 'type', className: 'center-align' },
                { name: 'document', searchable: false, orderable: false, className: 'center-align' },
                { name: 'note', className: 'center-align' },
                { name: 'tax_no', className: 'center-align' },
                { name: 'tax_cut_no', className: 'center-align' },
                { name: 'cut_date', className: 'center-align' },
                { name: 'spk_no', className: 'center-align' },
                { name: 'invoice_no', className: 'center-align' },
                { name: 'subtotal', className: 'right-align' },
                { name: 'percent_discount', className: 'right-align' },
                { name: 'nominal_discount', className: 'right-align' },
                { name: 'total', className: 'right-align' },
                { name: 'tax', className: 'right-align' },
                { name: 'wtax', className: 'right-align' },
                { name: 'rounding', className: 'right-align' },
                { name: 'grandtotal', className: 'right-align' },
                { name: 'downpayment', className: 'right-align' },
                { name: 'balance', className: 'right-align' },
              { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'by', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle'
            ]
        });
        $('.dt-buttons').appendTo('#datatable_buttons');

        $('select[name="datatable_serverside_length"]').addClass('browser-default');
	}

    function rowDetail(data) {
        $.ajax({
            url: '{{ Request::url() }}/row_detail',
            type: 'GET',
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            data: {
                id: data
            },
            success: function(response) {
                $('#modal4_1').modal('open');
                $('#show_detail').html(response);
                loadingClose('.modal-content');
            },
            error: function() {
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
            }
        });
	}

    function save(){
		swal({
            title: "Apakah anda yakin ingin simpan?",
            text: "Silahkan cek kembali form, dan jika sudah yakin maka lanjutkan!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                var formData = new FormData($('#form_data')[0]), passedQty = true, passed = true;

                if($('#type_detail').val() == '1'){
                    formData.delete("arr_code[]");
                    formData.delete("arr_frd_id[]");
                    formData.delete("arr_type[]");
                    formData.delete("arr_total[]");
                    formData.delete("arr_tax[]");
                    formData.delete("arr_wtax[]");
                    formData.delete("arr_grandtotal[]");
                    formData.delete("arr_dp_code[]");
                    formData.delete("arr_nominal[]");
                    formData.delete("arr_note[]");
                    formData.delete("arr_note2[]");
                    formData.delete("arr_place[]");
                    formData.delete("arr_line[]");
                    formData.delete("arr_machine[]");
                    formData.delete("arr_department[]");
                    formData.delete("arr_warehouse[]");
                    formData.delete("arr_project[]");

                    $('select[name^="arr_percent_tax"]').each(function(){
                        formData.append('arr_tax_id[]',($(this).find(':selected').data('id') ? $(this).find(':selected').data('id') : ''));
                    });

                    $('select[name^="arr_percent_wtax"]').each(function(){
                        formData.append('arr_wtax_id[]',($(this).find(':selected').data('id') ? $(this).find(':selected').data('id') : ''));
                    });

                    $('input[name^="arr_code"]').each(function(){
                        passed = true;
                        if($('input[name^="arr_type"][data-id="' + $(this).data('id') + '"]').val() == 'coas'){
                            if($('#arr_coa' + $(this).data('id')).val()){
                                formData.append('arr_code[]',$('#arr_coa' + $(this).data('id')).val());
                                formData.append('arr_frd_id[]',$('input[name^="arr_frd_id[]"][data-id="' + $(this).data('id') + '"]').val());
                            }else{
                                passed = false;
                            }
                        }else{
                            formData.append('arr_code[]',$(this).val());
                        }
                        if(passed == true){
                            formData.append('arr_type[]',$('input[name^="arr_type"][data-id="' + $(this).data('id') + '"]').val());
                            formData.append('arr_total[]',$('input[name^="arr_total"][data-id="' + $(this).data('id') + '"]').val());
                            formData.append('arr_tax[]',$('input[name^="arr_tax"][data-id="' + $(this).data('id') + '"]').val());
                            formData.append('arr_wtax[]',$('input[name^="arr_wtax"][data-id="' + $(this).data('id') + '"]').val());
                            formData.append('arr_grandtotal[]',$('input[name^="arr_grandtotal"][data-id="' + $(this).data('id') + '"]').val());
                            formData.append('arr_note[]',$('input[name^="arr_note[]"][data-id="' + $(this).data('id') + '"]').val());
                            formData.append('arr_note2[]',$('input[name^="arr_note2[]"][data-id="' + $(this).data('id') + '"]').val());
                            formData.append('arr_place[]',$('#arr_place' + $(this).data('id')).val());
                            formData.append('arr_line[]',($('#arr_line' + $(this).data('id')).val() ? $('#arr_line' + $(this).data('id')).val() : ''));
                            formData.append('arr_machine[]',($('#arr_machine' + $(this).data('id')).val() ? $('#arr_machine' + $(this).data('id')).val() : ''));
                            formData.append('arr_department[]',($('#arr_department' + $(this).data('id')).val() ? $('#arr_department' + $(this).data('id')).val() : ''));
                            formData.append('arr_warehouse[]',($('#arr_warehouse' + $(this).data('id')).val() ? $('#arr_warehouse' + $(this).data('id')).val() : ''));
                            formData.append('arr_project[]',($('#arr_project' + $(this).data('id')).val() ? $('#arr_project' + $(this).data('id')).val() : ''));
                        }
                        let qtyreal = parseFloat($('input[name^="arr_temp_qty"][data-id="' + $(this).data('id') + '"]').val().replaceAll(".", "").replaceAll(",",".")), qtynow = parseFloat($('input[name^="arr_qty"][data-id="' + $(this).data('id') + '"]').val().replaceAll(".", "").replaceAll(",","."));

                        if(qtynow > qtyreal){
                            passedQty = false;
                        }
                    });
                }else if($('#type_detail').val() == '2'){
                    formData.delete("arr_multi_coa[]");
                    formData.delete("arr_multi_qty[]");
                    formData.delete("arr_multi_price[]");
                    formData.delete("arr_multi_total[]");
                    formData.delete("arr_multi_tax_id[]");
                    formData.delete("arr_multi_ppn[]");
                    formData.delete("arr_multi_wtax_id[]");
                    formData.delete("arr_multi_pph[]");
                    formData.delete("arr_multi_grandtotal[]");
                    formData.delete("arr_multi_note_1[]");
                    formData.delete("arr_multi_note_2[]");
                    formData.delete("arr_multi_place[]");
                    formData.delete("arr_multi_line[]");
                    formData.delete("arr_multi_machine[]");
                    formData.delete("arr_multi_department[]");
                    formData.delete("arr_multi_warehouse[]");
                    formData.delete("arr_multi_project[]");

                    $('input[name^="arr_multi_coa[]"]').each(function(index){
                        if($(this).val()){
                            formData.append('arr_multi_coa[]',($('input[name^="arr_multi_coa"]').eq(index).val() ? $('input[name^="arr_multi_coa"]').eq(index).val() : ''));
                            formData.append('arr_multi_qty[]',($('input[name^="arr_multi_qty"]').eq(index).val() ? $('input[name^="arr_multi_qty"]').eq(index).val() : ''));
                            formData.append('arr_multi_price[]',($('input[name^="arr_multi_price"]').eq(index).val() ? $('input[name^="arr_multi_price"]').eq(index).val() : ''));
                            formData.append('arr_multi_total[]',($('input[name^="arr_multi_total"]').eq(index).val() ? $('input[name^="arr_multi_total"]').eq(index).val() : ''));
                            formData.append('arr_multi_tax_id[]',($('input[name^="arr_multi_tax_id"]').eq(index).val() ? $('input[name^="arr_multi_tax_id"]').eq(index).val() : ''));
                            formData.append('arr_multi_wtax_id[]',($('input[name^="arr_multi_wtax_id"]').eq(index).val() ? $('input[name^="arr_multi_wtax_id"]').eq(index).val() : ''));
                            formData.append('arr_multi_ppn[]',($('input[name^="arr_multi_ppn"]').eq(index).val() ? $('input[name^="arr_multi_ppn"]').eq(index).val() : ''));
                            formData.append('arr_multi_pph[]',($('input[name^="arr_multi_pph"]').eq(index).val() ? $('input[name^="arr_multi_pph"]').eq(index).val() : ''));
                            formData.append('arr_multi_grandtotal[]',($('input[name^="arr_multi_grandtotal"]').eq(index).val() ? $('input[name^="arr_multi_grandtotal"]').eq(index).val() : ''));
                            formData.append('arr_multi_note_1[]',($('input[name^="arr_multi_note_1"]').eq(index).val() ? $('input[name^="arr_multi_note_1"]').eq(index).val() : ''));
                            formData.append('arr_multi_note_2[]',($('input[name^="arr_multi_note_2"]').eq(index).val() ? $('input[name^="arr_multi_note_2"]').eq(index).val() : ''));
                            formData.append('arr_multi_place[]',($('input[name^="arr_multi_place"]').eq(index).val() ? $('input[name^="arr_multi_place"]').eq(index).val() : ''));
                            formData.append('arr_multi_line[]',($('input[name^="arr_multi_line"]').eq(index).val() ? $('input[name^="arr_multi_line"]').eq(index).val() : ''));
                            formData.append('arr_multi_machine[]',($('input[name^="arr_multi_machine"]').eq(index).val() ? $('input[name^="arr_multi_machine"]').eq(index).val() : ''));
                            formData.append('arr_multi_department[]',($('input[name^="arr_multi_department"]').eq(index).val() ? $('input[name^="arr_multi_department"]').eq(index).val() : ''));
                            formData.append('arr_multi_warehouse[]',($('input[name^="arr_multi_warehouse"]').eq(index).val() ? $('input[name^="arr_multi_warehouse"]').eq(index).val() : ''));
                            formData.append('arr_multi_project[]',($('input[name^="arr_multi_project"]').eq(index).val() ? $('input[name^="arr_multi_project"]').eq(index).val() : ''));
                        }
                    });
                }

                $('input[name^="arr_dp_code"]').each(function(index){
                    formData.append('arr_dp_code[]',$(this).val());
                    formData.append('arr_nominal[]',$('input[name^="arr_nominal"]').eq(index).val());
                });

                if(passedQty == true){
                    if(passed == true){
                        var path = window.location.pathname;
                    path = path.replace(/^\/|\/$/g, '');


                    var segments = path.split('/');
                    var lastSegment = segments[segments.length - 1];

                    formData.append('lastsegment',lastSegment);

                        $.ajax({
                            url: '{{ Request::url() }}/create',
                            type: 'POST',
                            dataType: 'JSON',
                            data: formData,
                            contentType: false,
                            processData: false,
                            cache: true,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            beforeSend: function() {
                                $('#validation_alert').hide();
                                $('#validation_alert').html('');
                                loadingOpen('#modal1');
                            },
                            success: function(response) {
                                loadingClose('#modal1');
                                $('input').css('border', 'none');
                                $('input').css('border-bottom', '0.5px solid black');
                                if(response.status == 200) {
                                    success();
                                    M.toast({
                                        html: response.message
                                    });
                                } else if(response.status == 422) {
                                    $('#validation_alert').show();
                                    $('#modal1').scrollTop(0);
                                    $.each(response.error, function(field, errorMessage) {
                                        $('#' + field).addClass('error-input');
                                        $('#' + field).css('border', '1px solid red');

                                    });
                                    swal({
                                        title: 'Ups! Validation',
                                        text: 'Check your form.',
                                        icon: 'warning'
                                    });

                                    $.each(response.error, function(i, val) {
                                        $.each(val, function(i, val) {
                                            $('#validation_alert').append(`
                                                <div class="card-alert card red">
                                                    <div class="card-content white-text">
                                                        <p>` + val + `</p>
                                                    </div>
                                                    <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
                                                        <span aria-hidden="true">×</span>
                                                    </button>
                                                </div>
                                            `);
                                        });
                                    });
                                } else {
                                    M.toast({
                                        html: response.message
                                    });
                                }
                            },
                            error: function() {
                                $('.modal-content').scrollTop(0);
                                loadingClose('#modal1');
                                swal({
                                    title: 'Ups!',
                                    text: 'Check your internet connection.',
                                    icon: 'error'
                                });
                            }
                        });
                    }else{
                        M.toast({
                            html: 'Silahkan cek detail form anda. Pastikan tidak ada data yang kosong.'
                        });
                    }
                }else{
                    M.toast({
                        html: 'Salah satu item melebihi jumlah qty dari yang seharusnya.'
                    });
                }
            }
        });
    }

    function saveMulti(){
		swal({
            title: "Apakah anda yakin ingin simpan?",
            text: "Silahkan cek kembali form, dan jika sudah yakin maka lanjutkan!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                var formData = new FormData($('#form_data')[0]);

                formData.delete("arr_multi_code[]");
                formData.delete("arr_multi_supplier[]");
                formData.delete("arr_multi_company[]");
                formData.delete("arr_multi_type[]");
                formData.delete("arr_multi_note[]");
                formData.delete("arr_multi_post_date[]");
                formData.delete("arr_multi_received_date[]");
                formData.delete("arr_multi_top[]");
                formData.delete("arr_multi_due_date[]");
                formData.delete("arr_multi_document_date[]");
                formData.delete("arr_multi_tax_no[]");
                formData.delete("arr_multi_tax_cut_no[]");
                formData.delete("arr_multi_cut_date[]");
                formData.delete("arr_multi_spk_no[]");
                formData.delete("arr_multi_invoice_no[]");
                formData.delete("arr_multi_coa[]");
                formData.delete("arr_multi_qty[]");
                formData.delete("arr_multi_price[]");
                formData.delete("arr_multi_total[]");
                formData.delete("arr_multi_tax_id[]");
                formData.delete("arr_multi_ppn[]");
                formData.delete("arr_multi_wtax_id[]");
                formData.delete("arr_multi_pph[]");
                formData.delete("arr_multi_grandtotal[]");
                formData.delete("arr_multi_note_1[]");
                formData.delete("arr_multi_note_2[]");
                formData.delete("arr_multi_place[]");
                formData.delete("arr_multi_line[]");
                formData.delete("arr_multi_machine[]");
                formData.delete("arr_multi_department[]");
                formData.delete("arr_multi_warehouse[]");

                $('input[name^="arr_multi_code"]').each(function(index){
                    if($(this).val()){
                        formData.append('arr_multi_code[]',$(this).val());
                        formData.append('arr_multi_supplier[]',($('input[name^="arr_multi_supplier"]').eq(index).val() ? $('input[name^="arr_multi_supplier"]').eq(index).val() : ''));
                        formData.append('arr_multi_company[]',($('input[name^="arr_multi_company"]').eq(index).val() ? $('input[name^="arr_multi_company"]').eq(index).val() : ''));
                        formData.append('arr_multi_type[]',($('input[name^="arr_multi_type"]').eq(index).val() ? $('input[name^="arr_multi_type"]').eq(index).val() : ''));
                        formData.append('arr_multi_note[]',($('input[name^="arr_multi_note"]').eq(index).val() ? $('input[name^="arr_multi_note"]').eq(index).val() : ''));
                        formData.append('arr_multi_post_date[]',($('input[name^="arr_multi_post_date"]').eq(index).val() ? $('input[name^="arr_multi_post_date"]').eq(index).val() : ''));
                        formData.append('arr_multi_received_date[]',($('input[name^="arr_multi_received_date"]').eq(index).val() ? $('input[name^="arr_multi_received_date"]').eq(index).val() : ''));
                        formData.append('arr_multi_top[]',($('input[name^="arr_multi_top"]').eq(index).val() ? $('input[name^="arr_multi_top"]').eq(index).val() : ''));
                        formData.append('arr_multi_due_date[]',($('input[name^="arr_multi_due_date"]').eq(index).val() ? $('input[name^="arr_multi_due_date"]').eq(index).val() : ''));
                        formData.append('arr_multi_document_date[]',($('input[name^="arr_multi_document_date"]').eq(index).val() ? $('input[name^="arr_multi_document_date"]').eq(index).val() : ''));
                        formData.append('arr_multi_tax_no[]',($('input[name^="arr_multi_tax_no"]').eq(index).val() ? $('input[name^="arr_multi_tax_no"]').eq(index).val() : ''));
                        formData.append('arr_multi_tax_cut_no[]',($('input[name^="arr_multi_tax_cut_no"]').eq(index).val() ? $('input[name^="arr_multi_tax_cut_no"]').eq(index).val() : ''));
                        formData.append('arr_multi_cut_date[]',($('input[name^="arr_multi_cut_date"]').eq(index).val() ? $('input[name^="arr_multi_cut_date"]').eq(index).val() : ''));
                        formData.append('arr_multi_spk_no[]',($('input[name^="arr_multi_spk_no"]').eq(index).val() ? $('input[name^="arr_multi_spk_no"]').eq(index).val() : ''));
                        formData.append('arr_multi_invoice_no[]',($('input[name^="arr_multi_invoice_no"]').eq(index).val() ? $('input[name^="arr_multi_invoice_no"]').eq(index).val() : ''));
                        formData.append('arr_multi_coa[]',($('input[name^="arr_multi_coa"]').eq(index).val() ? $('input[name^="arr_multi_coa"]').eq(index).val() : ''));
                        formData.append('arr_multi_qty[]',($('input[name^="arr_multi_qty"]').eq(index).val() ? $('input[name^="arr_multi_qty"]').eq(index).val() : ''));
                        formData.append('arr_multi_price[]',($('input[name^="arr_multi_price"]').eq(index).val() ? $('input[name^="arr_multi_price"]').eq(index).val() : ''));
                        formData.append('arr_multi_total[]',($('input[name^="arr_multi_total"]').eq(index).val() ? $('input[name^="arr_multi_total"]').eq(index).val() : ''));
                        formData.append('arr_multi_tax_id[]',($('input[name^="arr_multi_tax_id"]').eq(index).val() ? $('input[name^="arr_multi_tax_id"]').eq(index).val() : ''));
                        formData.append('arr_multi_wtax_id[]',($('input[name^="arr_multi_wtax_id"]').eq(index).val() ? $('input[name^="arr_multi_wtax_id"]').eq(index).val() : ''));
                        formData.append('arr_multi_ppn[]',($('input[name^="arr_multi_ppn"]').eq(index).val() ? $('input[name^="arr_multi_ppn"]').eq(index).val() : ''));
                        formData.append('arr_multi_pph[]',($('input[name^="arr_multi_pph"]').eq(index).val() ? $('input[name^="arr_multi_pph"]').eq(index).val() : ''));
                        formData.append('arr_multi_grandtotal[]',($('input[name^="arr_multi_grandtotal"]').eq(index).val() ? $('input[name^="arr_multi_grandtotal"]').eq(index).val() : ''));
                        formData.append('arr_multi_note_1[]',($('input[name^="arr_multi_note_1"]').eq(index).val() ? $('input[name^="arr_multi_note_1"]').eq(index).val() : ''));
                        formData.append('arr_multi_note_2[]',($('input[name^="arr_multi_note_2"]').eq(index).val() ? $('input[name^="arr_multi_note_2"]').eq(index).val() : ''));
                        formData.append('arr_multi_place[]',($('input[name^="arr_multi_place"]').eq(index).val() ? $('input[name^="arr_multi_place"]').eq(index).val() : ''));
                        formData.append('arr_multi_line[]',($('input[name^="arr_multi_line"]').eq(index).val() ? $('input[name^="arr_multi_line"]').eq(index).val() : ''));
                        formData.append('arr_multi_machine[]',($('input[name^="arr_multi_machine"]').eq(index).val() ? $('input[name^="arr_multi_machine"]').eq(index).val() : ''));
                        formData.append('arr_multi_department[]',($('input[name^="arr_multi_department"]').eq(index).val() ? $('input[name^="arr_multi_department"]').eq(index).val() : ''));
                        formData.append('arr_multi_warehouse[]',($('input[name^="arr_multi_warehouse"]').eq(index).val() ? $('input[name^="arr_multi_warehouse"]').eq(index).val() : ''));
                    }
                });

                $.ajax({
                    url: '{{ Request::url() }}/create_multi',
                    type: 'POST',
                    dataType: 'JSON',
                    data: formData,
                    contentType: false,
                    processData: false,
                    cache: true,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $('#validation_alert_multi').hide();
                        $('#validation_alert_multi').html('');
                        loadingOpen('.modal-content');
                    },
                    success: function(response) {
                        loadingClose('.modal-content');
                        if(response.status == 200) {
                            success();
                            M.toast({
                                html: response.message
                            });
                        } else if(response.status == 422) {
                            $('#validation_alert_multi').show();
                            $('.modal-content').scrollTop(0);

                            swal({
                                title: 'Ups! Validation',
                                text: 'Check your form.',
                                icon: 'warning'
                            });

                            $.each(response.error, function(i, val) {
                                $.each(val, function(i, val) {
                                    $('#validation_alert_multi').append(`
                                        <div class="card-alert card red">
                                            <div class="card-content white-text">
                                                <p>` + val + `</p>
                                            </div>
                                            <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                        </div>
                                    `);
                                });
                            });
                        } else {
                            M.toast({
                                html: response.message
                            });
                        }
                    },
                    error: function() {
                        $('.modal-content').scrollTop(0);
                        loadingClose('.modal-content');
                        swal({
                            title: 'Ups!',
                            text: 'Check your internet connection.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    }

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
    }

    function printPreview(code,aslicode){
        swal({
            title: "Apakah Anda ingin mengeprint dokumen ini?",
            text: "Dengan Kode "+aslicode,
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                $.ajax({
                    url: '{{ Request::url() }}/print_individual/' + code,
                    type:'GET',
                    beforeSend: function() {
                        loadingOpen('.modal-content');
                    },
                    complete: function() {

                    },
                    success: function(data){
                        loadingClose('.modal-content');
                        printService.submit({
                            'type': 'INVOICE',
                            'url': data
                        })
                    }
                });
            }
        });

    }

    function show(id){
        $.ajax({
            url: '{{ Request::url() }}/show',
            type: 'POST',
            dataType: 'JSON',
            data: {
                id: id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('#main');
            },
            success: function(response) {
                loadingClose('#main');
                if(response.status == '500'){
                    M.toast({
                        html: response.message
                    });
                }else{

                    if(response.document){
                        const baseUrl = 'http://127.0.0.1:8000/storage/';
                        const filePath = response.document.replace('public/', '');
                        const fileUrl = baseUrl + filePath;
                        displayFile(fileUrl);
                    }
                    $('#modal1').modal('open');
                    $('#temp').val(id);
                    $('#code_place_id').val(response.code_place_id).formSelect();
                    $('#code').val(response.code);
                    $('#account_id').empty();
                    $('#account_id').append(`
                        <option value="` + response.account_id + `">` + response.account_name + `</option>
                    `);
                    $('#type').val(response.type).formSelect();
                    $('#company_id').val(response.company_id).formSelect();
                    $('#post_date').val(response.post_date);
                    $('#received_date').val(response.received_date);
                    $('#due_date').val(response.due_date);
                    $('#document_date').val(response.document_date);
                    $('#note').val(response.note);
                    $('#tax_no').val(response.tax_no);
                    $('#tax_cut_no').val(response.tax_cut_no);
                    $('#cut_date').val(response.cut_date);
                    $('#spk_no').val(response.spk_no);
                    $('#invoice_no').val(response.invoice_no);
                    $('#document_no').val(response.document_no);
                    $('#downpayment').val(response.downpayment);
                    $('#rounding').val(response.rounding);
                    $('#currency_rate').val(response.currency_rate);
                    $('#currency_id').val(response.currency_id).formSelect();
                    $('#top').val(response.top);

                    if(response.details.length > 0){
                        $('.row_detail').remove();
                        $.each(response.details, function(i, val) {
                            var count = makeid(10);
                            if(val.type == 'coas'){
                                $('#last-row-detail').before(`
                                    <tr class="row_detail">
                                        <input type="hidden" name="arr_code[]" value="" data-id="` + count + `">
                                        <input type="hidden" name="arr_frd_id[]" value="` + val.frd_id + `" data-id="` + count + `">
                                        <input type="hidden" name="arr_type[]" value="` + val.type + `" data-id="` + count + `">
                                        <input type="hidden" name="arr_total[]" value="` + val.total + `" data-id="` + count + `">
                                        <input type="hidden" name="arr_tax[]" value="` + val.tax + `" data-id="` + count + `">
                                        <input type="hidden" name="arr_wtax[]" value="` + val.wtax + `" data-id="` + count + `">
                                        <input type="hidden" name="arr_grandtotal[]" value="` + val.grandtotal + `" data-id="` + count + `">
                                        <input type="hidden" name="arr_temp_qty[]" value="` + val.qty_balance + `" data-id="` + count + `">
                                        <td class="center">
                                            <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                                                <i class="material-icons">delete</i>
                                            </a>
                                        </td>
                                        <td class="center">
                                            <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]"></select>
                                        </td>
                                        <td>
                                            <input type="text" name="arr_note[]" value="` + val.info + `" data-id="` + count + `">
                                        </td>
                                        <td>
                                            <input type="text" name="arr_note2[]" value="` + val.note2 + `" data-id="` + count + `">
                                        </td>
                                        <td class="center">
                                            -
                                        </td>
                                        <td class="center">
                                            -
                                        </td>
                                        <td class="center">
                                            -
                                        </td>
                                        <td class="center">
                                            -
                                        </td>
                                        <td class="center">
                                            -
                                        </td>
                                        <td class="center">
                                            -
                                        </td>
                                        <td class="center">
                                            <input class="browser-default" type="text" name="arr_qty[]" onfocus="emptyThis(this);" value="` + val.qty_balance + `" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();" style="width:75px !important;">
                                        </td>
                                        <td class="center" id="qty_stock` + count + `">
                                            ` + val.qty_stock + `
                                        </td>
                                        <td class="center" id="unit_stock` + count + `">
                                            ` + val.unit_stock + `
                                        </td>
                                        <td class="center">
                                            <input class="browser-default" type="text" name="arr_price[]" onfocus="emptyThis(this);" value="` + val.price + `" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();">
                                        </td>
                                        <td class="center">
                                            -
                                        </td>
                                        <td class="center">
                                            -
                                        </td>
                                        <td class="right-align row_total" id="row_total` + count + `">
                                            ` + val.total + `
                                        </td>
                                        <td class="center">
                                            <select class="browser-default" id="arr_percent_tax` + count + `" name="arr_percent_tax[]" data-id="` + count + `" onchange="countAll();">
                                                <option value="0.00000" data-id="">-- Non-PPN --</option>
                                                @foreach ($tax as $row1)
                                                    <option value="{{ $row1->percentage }}" data-id="{{ $row1->id }}">{{ $row1->name.' - '.number_format($row1->percentage,2,',','.').'%' }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="center">
                                            <select class="browser-default" id="arr_include_tax` + count + `" name="arr_include_tax[]" data-id="` + count + `" onchange="countAll();">
                                                <option value="0">Tidak</option>
                                                <option value="1">Ya</option>
                                            </select>
                                        </td>
                                        <td class="right-align" id="row_tax` + count + `">
                                            <input class="browser-default" type="text" name="arr_tax[]" value="` + val.tax + `" data-id="` + count + `" onkeyup="formatRupiah(this);" readonly>
                                        </td>
                                        <td class="center">
                                            <select class="browser-default" id="arr_percent_wtax` + count + `" name="arr_percent_wtax[]" data-id="` + count + `" onchange="countAll();">
                                                <option value="0.00000" data-id="">-- Non-PPh --</option>
                                                @foreach ($wtax as $row2)
                                                    <option value="{{ $row2->percentage }}" data-id="{{ $row2->id }}">{{ $row2->name.' - '.number_format($row2->percentage,2,',','.').'%' }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="right-align" id="row_wtax` + count + `">
                                            <input class="browser-default" type="text" name="arr_wtax[]" value="` + val.wtax + `" data-id="` + count + `" onkeyup="formatRupiah(this);" readonly>
                                        </td>
                                        <td class="right-align row_grandtotal" id="row_grandtotal` + count + `">
                                            ` + val.grandtotal + `
                                        </td>

                                        <td class="center">
                                            <select class="browser-default" id="arr_place` + count + `" name="arr_place[]">
                                                @foreach ($place as $rowplace)
                                                    <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select class="browser-default" id="arr_line` + count + `" name="arr_line[]" onchange="changePlace(this);">
                                                <option value="">--{{ __('translations.empty') }}--</option>
                                                @foreach ($line as $rowline)
                                                    <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select class="browser-default" id="arr_machine` + count + `" name="arr_machine[]" onchange="changeLine(this);">
                                                <option value="">--{{ __('translations.empty') }}--</option>
                                                @foreach ($machine as $rowmachine)
                                                    <option value="{{ $rowmachine->id }}" data-line="{{ $rowmachine->line_id }}">{{ $rowmachine->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="center">
                                            <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                                                @foreach ($department as $rowdept)
                                                    <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="center">
                                            <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">
                                                <option value="">--{{ __('translations.empty') }}--</option>
                                                @foreach ($warehouse as $row)
                                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="center">
                                            <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                                        </td>
                                    </tr>
                                `);
                                $('#arr_percent_wtax' + count).val(val.percent_wtax);
                                $('#arr_percent_tax' + count).val(val.percent_tax);
                                $('#arr_include_tax' + count).val(val.include_tax);
                                $('#arr_place' + count).val(val.place_id);
                                $('#arr_line' + count).val(val.line_id);
                                $('#arr_machine' + count).val(val.machine_id);
                                $('#arr_department' + count).val(val.department_id);
                                $('#arr_warehouse' + count).val(val.warehouse_id);
                                $('#arr_coa' + count).append(`
                                    <option value="` + val.id + `">` + val.name + `</option>
                                `);
                                select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa_no_cash") }}');
                                if(val.project_id){
                                    $('#arr_project' + count).append(`
                                        <option value="` + val.project_id + `">` + val.project_name + `</option>
                                    `);
                                }
                                select2ServerSide('#arr_project' + count, '{{ url("admin/select2/project") }}');
                            }else{
                                $('#last-row-detail').before(`
                                    <tr class="row_detail">
                                        <input type="hidden" name="arr_type[]" value="` + val.type + `" data-id="` + count + `">
                                        <input type="hidden" name="arr_price[]" value="` + val.price_raw + `" data-id="` + count + `">
                                        <input type="hidden" name="arr_total[]" value="` + val.total + `" data-id="` + count + `">
                                        <input type="hidden" name="arr_grandtotal[]" value="` + val.grandtotal + `" data-id="` + count + `">
                                        <input type="hidden" name="arr_tax[]" value="` + val.tax + `" data-id="` + count + `">
                                        <input type="hidden" name="arr_wtax[]" value="` + val.wtax + `" data-id="` + count + `">
                                        <input type="hidden" id="arr_place` + count + `" name="arr_place[]" value="` + val.place_id + `" data-id="` + count + `">
                                        <input type="hidden" id="arr_line` + count + `" name="arr_line[]" value="` + val.line_id + `" data-id="` + count + `">
                                        <input type="hidden" id="arr_machine` + count + `" name="arr_machine[]" value="` + val.machine_id + `" data-id="` + count + `">
                                        <input type="hidden" id="arr_department` + count + `" name="arr_department[]" value="` + val.department_id + `" data-id="` + count + `">
                                        <input type="hidden" id="arr_warehouse` + count + `" name="arr_warehouse[]" value="` + val.warehouse_id + `" data-id="` + count + `">
                                        <input type="hidden" id="arr_project` + count + `" name="arr_project[]" value="` + val.project_id + `" data-id="` + count + `">
                                        <input type="hidden" name="arr_code[]" value="` + val.id + `" data-id="` + count + `">
                                        <input type="hidden" name="arr_temp_qty[]" value="` + val.qty_balance + `" data-id="` + count + `">
                                        <td class="center">
                                            <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                                                <i class="material-icons">delete</i>
                                            </a>
                                        </td>
                                        <td class="center">
                                            ` + val.rawcode + `
                                        </td>
                                        <td>
                                            <input type="text" name="arr_note[]" value="` + val.note + `" data-id="` + count + `">
                                        </td>
                                        <td>
                                            <input type="text" name="arr_note2[]" value="` + val.note2 + `" data-id="` + count + `">
                                        </td>
                                        <td class="center">
                                            ` + val.purchase_no + `
                                        </td>
                                        <td class="center">
                                            ` + val.delivery_no + `
                                        </td>
                                        <td class="">
                                            ` + val.name + `
                                        </td>
                                        <td class="center">
                                            ` + val.buy_unit + `
                                        </td>
                                        <td class="center">
                                            ` + val.qty_received + `
                                        </td>
                                        <td class="center">
                                            ` + val.qty_returned + `
                                        </td>
                                        <td class="center">
                                            <input class="browser-default" type="text" name="arr_qty[]" onfocus="emptyThis(this);" value="` + val.qty_balance + `" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();countStock(this);" data-conversion="` + val.qty_conversion + `">
                                        </td>
                                        <td class="center" id="qty_stock` + count + `">
                                            ` + val.qty_stock + `
                                        </td>
                                        <td class="center" id="unit_stock` + count + `">
                                            ` + val.unit_stock + `
                                        </td>
                                        <td class="right-align">
                                            ` + val.price + `
                                        </td>
                                        <td class="center">
                                            ` + val.post_date + `
                                        </td>
                                        <td class="center">
                                            ` + val.due_date + `
                                        </td>
                                        <td class="right-align row_total" id="row_total` + count + `">
                                            ` + val.total + `
                                        </td>
                                        <td class="center">
                                            <select class="browser-default" id="arr_percent_tax` + count + `" name="arr_percent_tax[]" data-id="` + count + `" onchange="countAll();">
                                                <option value="0.00000">-- Non-PPN --</option>
                                                @foreach ($tax as $row1)
                                                    <option value="{{ $row1->percentage }}" data-id="{{ $row1->id }}">{{ $row1->name.' - '.number_format($row1->percentage,2,',','.').'%' }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="center">
                                            <select class="browser-default" id="arr_include_tax` + count + `" name="arr_include_tax[]" data-id="` + count + `" onchange="countAll();">
                                                <option value="0">Tidak</option>
                                                <option value="1">Ya</option>
                                            </select>
                                        </td>
                                        <td class="right-align" id="row_tax` + count + `">
                                            ` + val.tax + `
                                        </td>
                                        <td class="center">
                                            <select class="browser-default" id="arr_percent_wtax` + count + `" name="arr_percent_wtax[]" data-id="` + count + `" onchange="countAll();">
                                                <option value="0.00000">-- Non-PPh --</option>
                                                @foreach ($wtax as $row2)
                                                    <option value="{{ $row2->percentage }}" data-id="{{ $row2->id }}">{{ $row2->name.' - '.number_format($row2->percentage,2,',','.').'%' }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="right-align" id="row_wtax` + count + `">
                                            ` + val.wtax + `
                                        </td>
                                        <td class="right-align row_grandtotal" id="row_grandtotal` + count + `">
                                            ` + val.grandtotal + `
                                        </td>

                                        <td class="center">
                                            ` + val.place_name + `
                                        </td>
                                        <td class="center">
                                            ` + val.line_name + `
                                        </td>
                                        <td class="center">
                                            ` + val.machine_name + `
                                        </td>
                                        <td class="center">
                                            ` + val.department_name + `
                                        </td>
                                        <td class="center">
                                            ` + val.warehouse_name + `
                                        </td>
                                        <td class="center">
                                            ` + val.project_name + `
                                        </td>
                                    </tr>
                                `);

                                $('#arr_percent_wtax' + count).val(val.percent_wtax);
                                $('#arr_percent_tax' + count).val(val.percent_tax);
                                $('#arr_include_tax' + count).val(val.include_tax);
                            }
                        });
                    }

                    if(response.downpayments.length > 0){
                        $('#body-detail-dp').empty();
                        $.each(response.downpayments, function(i, val) {
                            var count = makeid(10);
                            $('#body-detail-dp').append(`
                                <tr class="row_detail_dp">
                                    <input type="hidden" name="arr_dp_code[]" value="` + val.code + `" data-id="` + count + `">
                                    <td class="center">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail-dp" href="javascript:void(0);">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                    <td class="center">
                                        ` + val.rawcode + `
                                    </td>
                                    <td class="center">
                                        ` + val.pyr_code + `
                                    </td>
                                    <td class="center">
                                        ` + val.post_date + `
                                    </td>
                                    <td class="center">
                                        ` + val.grandtotal + `
                                    </td>
                                    <td class="center">
                                        ` + val.nominal + `
                                    </td>
                                    <td class="center">
                                        <input name="arr_nominal[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.nominal + `" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100% !important;" id="rowNominal`+ count +`">
                                    </td>
                                </tr>
                            `);
                        });
                    }
                }



                $('.modal-content').scrollTop(0);
                $('#note').focus();
                M.updateTextFields();
                countAll();
            },
            error: function() {
                $('.modal-content').scrollTop(0);
                loadingClose('#main');
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
            }
        });
    }

    function voidStatus(id){
        var msg = '';
        swal({
            title: "Alasan mengapa anda menutup!",
            text: "Anda tidak bisa mengembalikan data yang telah ditutup.",
            buttons: true,
            content: "input",
        })
        .then(message => {
            if (message != "" && message != null) {
                $.ajax({
                    url: '{{ Request::url() }}/void_status',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { id : id, msg : message },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        loadingOpen('#main');
                    },
                    success: function(response) {
                        loadingClose('#main');
                        M.toast({
                            html: response.message
                        });
                        loadDataTable();
                    },
                    error: function() {
                        loadingClose('#main');
                        swal({
                            title: 'Ups!',
                            text: 'Check your internet connection.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    }

    function cancelStatus(id){
        Swal.fire({
            title: "Pilih tanggal tutup!",
            input: "date",
            showCancelButton: true,
            confirmButtonText: "Lanjut",
            cancelButtonText: "Batal",
            cancelButtonColor: "#d33",
            confirmButtonColor: "#3085d6",
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: '{{ Request::url() }}/cancel_status',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { id : id, cancel_date : result.value },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        loadingOpen('#main');
                    },
                    success: function(response) {
                        loadingClose('#main');
                        M.toast({
                            html: response.message
                        });
                        loadDataTable();
                    },
                    error: function() {
                        loadingClose('#main');
                        swal({
                            title: 'Ups!',
                            text: 'Check your internet connection.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    }

    function destroy(id){
        var msg = '';
        swal({
            title: "Alasan mengapa anda menghapus!",
            text: "Anda tidak bisa mengembalikan data yang telah dihapus.",
            buttons: true,
            content: "input",
        })
        .then(message => {
            if (message != "" && message != null) {
                $.ajax({
                    url: '{{ Request::url() }}/destroy',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { id : id, msg : message },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        loadingOpen('#main');
                    },
                    success: function(response) {
                        loadingClose('#main');
                        M.toast({
                            html: response.message
                        });
                        loadDataTable();
                    },
                    error: function() {
                        loadingClose('#main');
                        swal({
                            title: 'Ups!',
                            text: 'Check your internet connection.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    }
    var printService = new WebSocketPrinter({
        onConnect: function () {

        },
        onDisconnect: function () {
            /* M.toast({
                html: 'Aplikasi penghubung printer tidak terinstall. Silahkan hubungi tim EDP.'
            }); */
        },
        onUpdate: function (message) {

        },
    });

    function printData(){
        var arr_id_invoice=[];
        $.map(window.table.rows('.selected').nodes(), function (item) {
            var poin = $(item).find('td:nth-child(2)').text().trim();
            arr_id_invoice.push(poin);
        });
        $.ajax({
            url: '{{ Request::url() }}/print',
            type: 'POST',
            dataType: 'JSON',
            data: {
                arr_id: arr_id_invoice,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
            },
            success: function(response) {
                $.each(response.data, function(i, val) {
                    if(i%2==0){
                        printService.submit({
                            'type': 'INVOICE',
                            'url': val
                        })
                    }else{
                        printService.submit({
                            'type': 'INVOICE',
                            'url': val
                        })
                    }
                });
            },
            error: function() {
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
            }
        });

    }

    function addDays(){
        if($('#top').val()){
            if($('#received_date').val()){
                var result = new Date($('#received_date').val());
                result.setDate(result.getDate() + parseInt($('#top').val()));
                $('#due_date').val(result.toISOString().split('T')[0]);
            }
        }else{
            $('#due_date').val(null);
        }
    }

    function viewJournal(id){
        $.ajax({
            url: '{{ Request::url() }}/view_journal/' + id,
            type:'GET',
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            complete: function() {

            },
            success: function(data){
                loadingClose('.modal-content');
                if(data.status == '500'){
                    M.toast({
                        html: data.message
                    });
                }else{
                    $('#modal6').modal('open');
                    $('#title_data').append(``+data.title+``);
                    $('#code_data').append(data.code);
                    $('#body-journal-table').append(data.tbody);
                    $('#user_jurnal').append(`Pengguna : `+data.user);
                    $('#note_jurnal').append(`Keterangan : `+data.note);
                    $('#ref_jurnal').append(`Referensi : `+data.reference);
                    $('#company_jurnal').append(`Perusahaan : `+data.company);
                    $('#post_date_jurnal').append(`Tanggal : `+data.post_date);
                }
            }
        });
    }

    function startIntro1(){
        introJs().setOptions({
            exitOnOverlayClick : false,
            steps: [
                {
                    title : 'Purchase Invoice',
                    intro : 'Form ini digunakan untuk menerbitkan Invoice dari Purchase Order yang telah diselesaikan.'
                },
                {
                    title : 'Nomor Dokumen',
                    element : document.querySelector('.step1'),
                    intro : 'Nomor dokumen wajib diisikan, dengan kombinasi 4 huruf kode dokumen, tahun pembuatan dokumen, kode plant, serta nomor urut. Nomor ini bersifat unik, tidak akan sama, dan nomor urut paling belakang akan ter-reset secara otomatis berdasarkan tahun tanggal post.'
                },
                {
                    title : 'Kode Plant',
                    element : document.querySelector('.step2'),
                    intro : 'Pilih kode plant untuk nomor dokumen bisa secara otomatis ter-generate.'
                },
                {
                    title : 'Tipe Detail',
                    element : document.querySelector('.step3'),
                    intro : 'Tipe Detail dari invoice yang nantinya akan menentukan inputan dari invoice ini.'
                },
                {
                    title : 'Supplier',
                    element : document.querySelector('.step4'),
                    intro : 'Supplier adalah Partner Bisnis tipe penyedia barang / jasa. Jika ingin menambahkan data baru, silahkan ke form Master Data - Organisasi - Partner Bisnis. Pada inputan disini akan menambahkan ke detail grpo po lc yang ada dibawah begitu juga pada tabel detail down payment yang berkaitan dengan supplier dan tipenya.'
                },
                {
                    title : 'Tipe',
                    element : document.querySelector('.step5'),
                    intro : 'Tipe Pembayaran menggunakan cash / transfer / giro / check dari perusahaan.'
                },
                {
                    title : 'Perusahaan',
                    element : document.querySelector('.step6'),
                    intro : 'Perusahaan dimana dokumen ini dibuat.'
                },
                {
                    title : 'Tgl. Posting',
                    element : document.querySelector('.step7'),
                    intro : 'Tanggal post akan menentukan tanggal jurnal untuk beberapa form yang terhubung dengan jurnal. Hati - hati dalam menentukan tanggal posting.'
                },
                {
                    title : 'Tgl. Terima',
                    element : document.querySelector('.step8'),
                    intro : 'Tanggal Terima dari Invoice ini.'
                },
                {
                    title : 'TOP',
                    element : document.querySelector('.step9'),
                    intro : 'Tenggat waktu pembayaran dalam hari yang juga dapat terisi secara otomatis melalui GRPO .'
                },
                {
                    title : 'Tgl. Jatuh Tempo',
                    element : document.querySelector('.step10'),
                    intro : 'Tanggal berlaku  dari dokumen ini.'
                },
                {
                    title : 'Tgl. Dokumen',
                    element : document.querySelector('.step11'),
                    intro : 'Tanggal yang nantinya digunakan saat dokumen dicetak.'
                },
                {
                    title : 'No Fakur Pajak',
                    element : document.querySelector('.step12'),
                    intro : ' No faktur pajak yang digunakan / terkait dalam invoice.'
                },
                {
                    title : 'No Bukti Potong',
                    element : document.querySelector('.step13'),
                    intro : ' No bukti potong yang digunakan dalam invoice.'
                },
                {
                    title : 'Tgl. Bukti Potong',
                    element : document.querySelector('.step14'),
                    intro : ' Tanggal yang ada dalam bukti potong'
                },
                {
                    title : 'No SPK',
                    element : document.querySelector('.step15'),
                    intro : ' Merupakan nomor perintah kerja dari dokumen (jika ada)'
                },
                {
                    title : 'No Dokumen',
                    element : document.querySelector('.stepdokumen'),
                    intro : 'No Dokumen berkaitan dengan form ini'
                },
                {
                    title : 'No Invoice',
                    element : document.querySelector('.step16'),
                    intro : ' Nomor Invoice terkait yang berasal dari vendor atau supplier(harap diisi jika ada)'
                },
                {
                    title : 'Mata Uang',
                    element : document.querySelector('.stepcurrency'),
                    intro : 'Mata uang, silahkan pilih mata uang lain, untuk mata uang asing.'
                },
                {
                    title : 'Konversi',
                    element : document.querySelector('.stepconversion'),
                    intro : ' Nomor Invoice terkait yang berasal dari vendor atau supplier(harap diisi jika ada)'
                },

                {
                    title : 'Scan Barcode',
                    element : document.querySelector('.step18'),
                    intro : 'Digunakan untuk memasukan inputan detail ke form dengan menggunakan alat scanner barcode'
                },
                {
                    title : 'File Lampiran',
                    element : document.querySelector('.step17'),
                    intro : 'Silahkan unggah file lampiran. untuk saat ini hanya bisa mengakomodir 1 file lampiran saja. Jika ingin menambahkan file lebih dari 1, silahkan gabungkan file anda menjadi pdf.'
                },
                {
                    title : 'Detail Goods Receipt PO / Landed Cost / Purchase Order Jasa / Coa',
                    element : document.querySelector('.step19'),
                    intro : 'Penambahan GRPO terotomisasi melalui pemilihan supplier/vendor yang nantinya akan masuk kesini .Penambahan GRPO LC PO jasa dan COA terkait secara manual dapat dilakukan disini'
                },
                {
                    title : 'Detail Multi',
                    element : document.querySelector('.step20'),
                    intro : 'Disini merupakan tempat untuk melakukan copy paste dari excel guna mempermudah penginputan.'
                },
                {
                    title : 'Detail Down Payment Partner Bisnis',
                    element : document.querySelector('.step21'),
                    intro : 'Detail Purchase DP dari partner bisnis yang dipilih akan ditampilan disini.'
                },
                {
                    title : 'Keterangan',
                    element : document.querySelector('.step22'),
                    intro : 'Silahkan isi / tambahkan keterangan untuk dokumen ini untuk dimunculkan di bagian bawah tabel detail produk nantinya, ketika dicetak.'
                },
                {
                    title : 'Uang Muka dan Pembulatan',
                    element : document.querySelector('.step23'),
                    intro : 'Pada inputan Uang muka tidak dapat di edit hanya pada pembulatan yang bisa di edit untuk melakukan pembulatan ke atas / ke bawah.'
                },
                {
                    title : 'Tombol Simpan',
                    element : document.querySelector('.step24'),
                    intro : 'Silahkan tekan tombol ini untuk menyimpan data, namun pastikan data yang akan anda masukkan benar.'
                },
            ]
        }).start();
    }

    function startIntro2(){
        introJs().setOptions({
            exitOnOverlayClick : false,
            steps: [
                {
                    title : 'Purchase Invoice Multi',
                    intro : 'Form ini digunakan untuk menerbitkan Invoice dari Purchase Order yang telah diselesaikan secara multi atau lebih dari 1 langsung.'
                },
                {
                    title : 'Tambah 1 Baris & Tambah Multi Baris',
                    element : document.querySelector('.step_2_1'),
                    intro : 'Pada Tabel dibawah ini terdapat tabel yang menampilkan data yang nantinya akan dimasukkan. Penambahan Baris 1 dan Multi akan menambahkan total jumlah baris yang dapat digunakan untuk mengisi data entah itu satu atau lebih dengan limit yaitu 50 baris.'
                },
            ]
        }).start();
    }
    function whatPrinting(code){
        $.ajax({
            url: '{{ Request::url() }}/print_individual/' + code,
            type:'GET',
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            complete: function() {

            },
            success: function(data){
                loadingClose('.modal-content');
                window.open(data, '_blank');
            }
        });
    }

    function done(id){
        var msg = '';
        swal({
            title: "Apakah anda yakin ingin menyelesaikan dokumen ini?",
            text: "Data yang sudah terupdate tidak dapat dikembalikan.",
            icon: 'warning',
            dangerMode: true,
            buttons: true,
            content: "input",
        })
        .then(message => {
            if (message != "" && message != null) {
                $.ajax({
                    url: '{{ Request::url() }}/done',
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        id: id,
                        msg : message
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        loadingOpen('#main');
                    },
                    success: function(response) {
                        loadingClose('#main');
                        if(response.status == 200) {
                            loadDataTable();
                            M.toast({
                                html: response.message
                            });
                        } else {
                            M.toast({
                                html: response.message
                            });
                        }
                    },
                    error: function() {
                        $('.modal-content').scrollTop(0);
                        loadingClose('#main');
                        swal({
                            title: 'Ups!',
                            text: 'Check your internet connection.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    }
    function exportExcel(){
        var search = table.search();
        var status = $('#filter_status').val();;
        var company = $('#filter_company').val();
        var type_pay = $('#filter_type').val();
        var supplier = $('#filter_account').val();
        var start_date = $('#start_date').val();
        var end_date = $('#finish_date').val();
        var modedata = '{{ $modedata }}';

        window.location = "{{ Request::url() }}/export_from_page?search=" + search + "&status=" + status + "&company=" + company + "&type_pay=" + type_pay + "&supplier=" + supplier + "&end_date=" + end_date + "&start_date=" + start_date + "&modedata=" + modedata;

    }
</script>
