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

    .modal-content .select2.tab-active {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .select-wrapper, .select2-container {
        height:auto !important;
    }

    .preserveLines {
        white-space: pre-line;
    }
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
                                                        <option value="3">Selesai</option>
                                                        <option value="4">Ditolak</option>
                                                        <option value="5">Ditutup</option>
                                                        <option value="6">Direvisi</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_inventory" style="font-size:1rem;">Tipe Pembelian :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_inventory" onchange="loadDataTable()">
                                                        <option value="">{{ __('translations.all') }}</option>
                                                        <option value="1">Persediaan Barang</option>
                                                        <option value="2">Lain-lain</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_type" style="font-size:1rem;">Tipe PO :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_type" onchange="loadDataTable()">
                                                        <option value="">{{ __('translations.all') }}</option>
                                                        <option value="1">Standart PO</option>
                                                        <option value="2">Planned PO</option>
                                                        <option value="3">Blanked PO</option>
                                                        <option value="4">Contract PO</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_shipping" style="font-size:1rem;">Tipe Pengiriman :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_shipping" onchange="loadDataTable()">
                                                        <option value="">{{ __('translations.all') }}</option>
                                                        <option value="1">Franco</option>
                                                        <option value="2">Loco</option>
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
                                                <label for="filter_payment" style="font-size:1rem;">Tipe Pembayaran :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_payment" onchange="loadDataTable()">
                                                        <option value="">{{ __('translations.all') }}</option>
                                                        <option value="1">Cash</option>
                                                        <option value="2">Credit</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_supplier" style="font-size:1rem;">Supplier :</label>
                                                <div class="input-field">
                                                    <select class="browser-default" id="filter_supplier" name="filter_supplier" multiple="multiple" style="width:100% !important;" onchange="loadDataTable()"></select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_currency" style="font-size:1rem;">Mata Uang :</label>
                                                <div class="input-field">
                                                    <select class="select2 browser-default" multiple="multiple" id="filter_currency" name="filter_currency" onchange="loadDataTable()">
                                                        <option value="" disabled>{{ __('translations.all') }}</option>
                                                        @foreach ($currency as $row)
                                                            <option value="{{ $row->id }}">{{ $row->code }}</option>
                                                        @endforeach
                                                    </select>
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
                                            <div class="card-alert card red">
                                                <div class="card-content white-text">
                                                    <p>Info : Khusus untuk Tipe Pembelian Persediaan Barang : 1 Dokumen PO hanya untuk 1 macam jenis grup item.</p>
                                                </div>
                                            </div>
                                            <div class="card-alert card blue">
                                                <div class="card-content white-text">
                                                    <p>Info : Untuk PO selain status <b>PROSES</b> dan <b>SELESAI</b>, maka tombol preview dan cetak tidak muncul.</p>
                                                </div>
                                            </div>
                                            <div class="card-alert card green">
                                                <div class="card-content white-text">
                                                    <p>Info : 1 PO hanya untuk 50 baris item tidak bisa lebih.</p>
                                                </div>
                                            </div>
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
                                                        <th rowspan="2">Supplier</th>
                                                        <th rowspan="2">Tipe PO</th>
                                                        <th rowspan="2">Pengiriman</th>
                                                        <th rowspan="2">{{ __('translations.company') }}</th>
                                                        <th colspan="2" class="center">Proforma</th>
                                                        <th colspan="2" class="center">Pembayaran</th>
                                                        <th colspan="2" class="center">{{ __('translations.currency') }}</th>
                                                        <th colspan="2" class="center">{{ __('translations.date') }}</th>
                                                        <th colspan="3" class="center">Penerima</th>
                                                        <th rowspan="2">Tgl.Diterima</th>
                                                        <th rowspan="2">{{ __('translations.note') }}</th>
                                                        <th rowspan="2">{{ __('translations.subtotal') }}</th>
                                                        <th rowspan="2">Diskon</th>
                                                        <th rowspan="2">{{ __('translations.total') }}</th>
                                                        <th rowspan="2">{{ __('translations.tax') }}</th>
                                                        <th rowspan="2">{{ __('translations.wtax') }}</th>
                                                        <th rowspan="2">Pembulatan</th>
                                                        <th rowspan="2">{{ __('translations.grandtotal') }}</th>
                                                        <th rowspan="2">{{ __('translations.status') }}</th>
                                                        <th rowspan="2">By</th>
                                                        <th rowspan="2">{{ __('translations.action') }}</th>
                                                    </tr>
                                                    <tr>
                                                        <th>{{ __('translations.number') }}/th>
                                                        <th>Dokumen</th>
                                                        <th>{{ __('translations.type') }}</th>
                                                        <th>Termin</th>
                                                        <th>{{ __('translations.code') }}</th>
                                                        <th>{{ __('translations.conversion') }}</th>
                                                        <th>Post</th>
                                                        <th>Kirim</th>
                                                        <th>{{ __('translations.name') }}</th>
                                                        <th>{{ __('translations.address') }}</th>
                                                        <th>{{ __('translations.phone_number') }}</th>
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
                <h4>{{ __('translations.add') }}/{{ __('translations.edit') }} {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="row">
                            <div class="input-field col m2 s12 step1">
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
                            <div class="input-field col m3 s12 step3">
                                <input type="hidden" id="temp" name="temp">
                                <input type="hidden" id="savesubtotal" name="savesubtotal" value="0,00">
                                <input type="hidden" id="savetotal" name="savetotal" value="0,00">
                                <input type="hidden" id="savetax" name="savetax" value="0,00">
                                <input type="hidden" id="savewtax" name="savewtax" value="0,00">
                                <input type="hidden" id="savegrandtotal" name="savegrandtotal" value="0,00">
                                <select class="browser-default" id="supplier_id" name="supplier_id" onchange="getTopSupplier();"></select>
                                <label class="active" for="supplier_id">Supplier</label>
                            </div>
                            <div class="input-field col m3 s12 step4">
                                <select class="form-control" id="inventory_type" name="inventory_type" onchange="applyType()">
                                    <option value="1">Persediaan Barang</option>
                                    <option value="2">Lain-lain</option>
                                </select>
                                <label class="" for="inventory_type">Tipe Pembelian</label>
                            </div>
                            <div class="input-field col m3 s12 step6">
                                <select class="form-control" id="shipping_type" name="shipping_type">
                                    <option value="1">Franco</option>
                                    <option value="2">Loco</option>
                                </select>
                                <label class="" for="shipping_type">Tipe Pengiriman</label>
                            </div>
                            <div class="input-field col m3 s12 step7">
                                <select class="form-control" id="company_id" name="company_id" onchange="getCompanyAddress();">
                                    @foreach ($company as $rowcompany)
                                        <option value="{{ $rowcompany->id }}" data-address="{{ $rowcompany->address }}">{{ $rowcompany->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="company_id">{{ __('translations.company') }}</label>
                            </div>
                            <div class="input-field col m3 s12 step8">
                                <input id="document_no" name="document_no" type="text" placeholder="No. Dokumen">
                                <label class="active" for="document_no">No. Dokumen</label>
                            </div>
                            <div class="input-field col m3 s12 step9">
                                <select class="form-control" id="payment_type" name="payment_type" onchange="applyTerm()">
                                    <option value="1">Cash</option>
                                    <option value="2">Credit</option>
                                </select>
                                <label class="" for="payment_type">Tipe Pembayaran</label>
                            </div>
                            <div class="input-field col m3 s12 step10">
                                <input id="payment_term" name="payment_term" type="number" value="0" min="0" step="1" onchange="addDays();">
                                <label class="active" for="payment_term">Termin Pembayaran (hari)</label>
                            </div>
                            <div class="input-field col m3 s12 stepreceive">
                                <input id="received_date" name="received_date" type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. Terima" value="{{ date('Y-m-d') }}" onchange="addDays();">
                                <label class="active" for="received_date">Tgl. Terima (Opsional)</label>
                            </div>
                            <div class="input-field col m3 s12 step11">
                                <select class="form-control" id="currency_id" name="currency_id" onchange="loadCurrency();refreshTotal();">
                                    @foreach ($currency as $row)
                                        <option value="{{ $row->id }}" data-code="{{ $row->code }}">{{ $row->code.' '.$row->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="currency_id">{{ __('translations.currency') }}</label>
                            </div>
                            <div class="input-field col m3 s12 step12">
                                <input id="currency_rate" name="currency_rate" type="text" value="1" onkeyup="formatRupiahNominal(this);countAll();">
                                <label class="active" for="currency_rate">{{ __('translations.conversion') }}</label>
                            </div>
                            <div class="input-field col m3 s12 step13">
                                <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);loadCurrency();">
                                <label class="active" for="post_date">{{ __('translations.post_date') }}</label>
                            </div>
                            <div class="input-field col m3 s12 step14">
                                <input id="delivery_date" name="delivery_date" min="{{ date('Y-m-d') }}" max="{{ date('Y'.'-12-31') }}" type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. kirim">
                                <label class="active" for="delivery_date">Tgl. Kirim</label>
                            </div>
                            <div class="input-field col m3 s12 step15">
                                <input id="receiver_name" name="receiver_name" type="text" placeholder="Nama Penerima">
                                <label class="active" for="receiver_name">Nama Penerima (Opsional)</label>
                            </div>
                            <div class="input-field col m3 s12 step16">
                                <input id="receiver_address" name="receiver_address" type="text" placeholder="Alamat Penerima">
                                <label class="active" for="receiver_address">Alamat Penerima (Opsional)</label>
                            </div>
                            <div class="input-field col m3 s12 step17">
                                <input id="receiver_phone" name="receiver_phone" type="text" placeholder="Kontak Penerima">
                                <label class="active" for="receiver_phone">Kontak Penerima (Opsional)</label>
                            </div>
                            <div class="file-field input-field col m12 s12 step18">
                                <div class="btn">
                                    <span>Dokumen PO</span>
                                    <input type="file" name="file[]" id="file" multiple accept=".pdf, .xlsx, .xls, .jpeg, .jpg, .png, .gif, .word">
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text">
                                </div>
                            </div>
                            <div class="col m12 s12">
                                <div class="col m3 s3 step19" id="pr-show">
                                    <p class="mt-2 mb-2">
                                        <h5>Purchase Request</h5>
                                        <div class="row">
                                            <div class="input-field col m12 s12">
                                                <select class="browser-default" id="purchase_request_id" name="purchase_request_id"></select>
                                                <label class="active" for="purchase_request_id">(Jika ada)</label>
                                            </div>
                                            <div class="col m12 12">
                                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="getDetails('po');" href="javascript:void(0);">
                                                    <i class="material-icons left">add</i> Purchase Request
                                                </a>
                                            </div>
                                        </div> 
                                    </p>
                                </div>
                                <div class="col m3 s3 step20" id="gi-show">
                                    <p class="mt-2 mb-2">
                                        <h5>Goods Issue / Barang Keluar</h5>
                                        <div class="row">
                                            <div class="input-field col m12 s12">
                                                <select class="browser-default" id="good_issue_id" name="good_issue_id"></select>
                                                <label class="active" for="good_issue_id">(Jika ada)</label>
                                            </div>
                                            <div class="col m12 12">
                                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="getDetails('gi');" href="javascript:void(0);">
                                                    <i class="material-icons left">add</i> Goods Issue
                                                </a>
                                            </div>
                                        </div>
                                    </p>
                                </div>
                                <div class="col m3 s3 stepsj" id="sj-show" style="display:none;">
                                    <p class="mt-2 mb-2">
                                        <h5>Surat Jalan / Penjualan</h5>
                                        <div class="row">
                                            <div class="input-field col m12 s12">
                                                <select class="browser-default" id="marketing_order_delivery_process_id" name="marketing_order_delivery_process_id"></select>
                                                <label class="active" for="marketing_order_delivery_process_id">(Jika ada)</label>
                                            </div>
                                            <div class="col m12 12">
                                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="getDetails('sj');" href="javascript:void(0);">
                                                    <i class="material-icons left">add</i> Surat Jalan
                                                </a>
                                            </div>
                                        </div>
                                    </p>
                                </div>
                            </div>
                            <div class="col m12 s12">
                                <div class="col m6 s6 step21">
                                    <h6><b>PR/GI Terpakai</b> (hapus untuk bisa diakses pengguna lain) : <i id="list-used-data"></i></h6>
                                </div>
                            </div>
                            <div class="col m12 s12 step22" style="overflow:auto;width:100% !important;">
                                <p class="mt-2 mb-2">
                                    <h4>Detail Produk</h4>
                                    <table class="bordered" style="width:4000px;font-size:0.9rem !important;" id="table-detail">
                                        <thead>
                                            <tr>
                                                <th>{{ __('translations.no') }}.</th>
                                                <th>{{ __('translations.delete') }}</th>
                                                <th>Item / Coa Jasa</th>
                                                <th>Keterangan 1</th>
                                                <th>Keterangan 2</th>
                                                <th>Keterangan 3</th>
                                                <th width="100px">Qty PO</th>
                                                <th width="100px">Satuan PO</th>
                                                <th width="100px">Qty Stok</th>
                                                <th width="100px">Satuan Stok</th>
                                                <th>{{ __('translations.price') }}</th>
                                                <th>
                                                    PPN
                                                    <label class="pl-2">
                                                        <input type="checkbox" onclick="chooseAllPpn(this)">
                                                        <span style="padding-left: 25px;">{{ __('translations.all') }}</span>
                                                    </label>
                                                </th>
                                                <th>Termasuk PPN</th>
                                                <th>
                                                    PPh
                                                    <label class="pl-2">
                                                        <input type="checkbox" onclick="chooseAllPph(this)">
                                                        <span style="padding-left: 25px;">{{ __('translations.all') }}</span>
                                                    </label>
                                                </th>
                                                <th>Disc1(%)</th>
                                                <th>Disc2(%)</th>
                                                <th>Disc3(Rp)</th>
                                                <th>{{ __('translations.subtotal') }}</th>
                                                <th>{{ __('translations.total') }}</th>
                                                <th>{{ __('translations.tax') }}</th>
                                                <th>{{ __('translations.wtax') }}</th>
                                                <th>{{ __('translations.grandtotal') }}</th>
                                                <th>{{ __('translations.plant') }}</th>
                                                <th>{{ __('translations.line') }}</th>
                                                <th>{{ __('translations.engine') }}</th>
                                                <th>{{ __('translations.division') }}</th>
                                                <th>{{ __('translations.warehouse') }}</th>
                                                <th>Requester</th>
                                                <th>Proyek</th>
                                            </tr>
                                            <tr>
                                                
                                            </tr>
                                        </thead>
                                        <tbody id="body-item">
                                            <tr id="last-row-item">
                                                <td colspan="29">
                                                    <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" id="button-add-item" onclick="addItem()" href="javascript:void(0);">
                                                        <i class="material-icons left">add</i> New Item
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </p>
                            </div>
                            <div class="input-field col m3 s12 step23">
                                <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note">{{ __('translations.note') }}</label>
                            </div>
                            <div class="input-field col m3 s12 step24">
                                <textarea class="materialize-textarea preserveLines" id="note_external" name="note_external" placeholder="Keterangan Tambahan" rows="3"></textarea>
                                <label class="active" for="note_external">Keterangan Tambahan (muncul pada printout)</label>
                            </div>
                            <div class="input-field col m6 s12 step25">
                                <table width="100%" class="bordered">
                                    <thead>
                                        <tr>
                                            <td width="33%"></td>
                                            <td width="33%" class="center-align">Mata Uang Asli</td>
                                            <td width="33%" class="center-align">Mata Uang Konversi</td>
                                        </tr>
                                        <tr>
                                            <td>Subtotal Sblm Diskon</td>
                                            <td class="right-align"><span id="subtotal">0,00</span></td>
                                            <td class="right-align"><span id="subtotal-convert">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>Diskon</td>
                                            <td class="right-align">
                                                <input class="browser-default" onfocus="emptyThis(this);" id="discount" name="discount" type="text" value="0" onkeyup="formatRupiahNominal(this);countAll();" style="text-align:right;width:100%;">
                                            </td>
                                            <td class="right-align"><span id="discount-convert">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>Subtotal Setelah Diskon</td>
                                            <td class="right-align"><span id="total">0,00</span></td>
                                            <td class="right-align"><span id="total-convert">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>PPN</td>
                                            <td class="right-align"><span id="tax">0,00</span></td>
                                            <td class="right-align"><span id="tax-convert">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>PPh</td>
                                            <td class="right-align">
                                                <input class="browser-default" onfocus="emptyThis(this);" id="wtax" name="wtax" type="text" value="0,00" onkeyup="formatRupiahNominal(this);countGrandtotal(this.value);" style="text-align:right;width:100%;">
                                                <td class="right-align"><span id="wtax-convert">0,00</span></td>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Pembulatan</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="rounding" name="rounding" type="text" value="0,00" onkeyup="formatRupiahNominal(this);countAll();" style="text-align:right;width:100%;">
                                                <td class="right-align"><span id="rounding-convert">0,00</span></td>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Grandtotal</td>
                                            <td class="right-align"><span id="grandtotal">0,00</span></td>
                                            <td class="right-align"><span id="grandtotal-convert">0,00</span></td>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="col s12 mt-3">
                                <button class="btn waves-effect waves-light right submit step26" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light purple btn-panduan" onclick="startIntro();">Panduan <i class="material-icons right">help_outline</i></button>
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
        <div class="row">
            <div class="col s12" id="show_structure">
                <div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;">

                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>

<div id="modal4" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
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
        <div class="row">
            <div class="col s12">
                <form class="row" id="form_done" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert_done" style="display:none;"></div>
                    </div>
                    <p class="mt-2 mb-2">
                        <h4>Detail Penutupan Permintaan Pembelian per Item</h4>
                        <input type="hidden" id="tempDone" name="tempDone">
                        <table class="bordered" style="width:100%;">
                            <thead>
                                <tr>
                                    <th class="center">Tutup</th>
                                    <th class="center">{{ __('translations.item') }}</th>
                                    <th class="center">{{ __('translations.unit') }}</th>
                                    <th class="center">Qty Order</th>
                                    <th class="center">Qty Diterima</th>
                                    <th class="center">Qty Gantungan</th>
                                </tr>
                            </thead>
                            <tbody id="body-done"></tbody>
                        </table>
                    </p>
                    <button class="btn waves-effect waves-light right submit" onclick="saveDone();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
                </form>
                <p>Info : Item yang tertutup akan dianggap sudah diterima / masuk gudang secara keseluruhan, sehingga tidak akan muncul di form Penerimaan PO / Goods Receipt.</p>
            </div>
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

<script>
    var mode = '';
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
    $(function() {
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
            
        });

        loadDataTable();

        $('#modal4').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#show_detail').empty();
            }
        });

        window.table.search('{{ $code }}').draw();
        
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                $('#post_date').attr('min','{{ $minDate }}');
                $('#post_date').attr('max','{{ $maxDate }}');
                $('#due_date').attr('min','{{ date("Y-m-d") }}');
                $('#required_date').attr('min','{{ date("Y-m-d") }}');
            },
            onOpenEnd: function(modal, trigger) { 
                $('#name').focus();
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
                window.onbeforeunload = function() {
                    if($('.data-used').length > 0){
                        $('.data-used').trigger('click');
                    }
                    return 'You will lose all changes made since your last save';
                };
                if(!$('#temp').val()){
                    loadCurrency();
                    $('#company_id').trigger('change');
                }
                /* $('#pr-show,#gi-show,#sj-show').show(); */
                $('#inventory_type').formSelect().trigger('change');
            },
            onCloseEnd: function(modal, trigger){
                $('input').css('border', 'none');
                $('input').css('border-bottom', '0.5px solid black');
                $('#form_data')[0].reset();
                $('input').css('border', 'none');
                $('input').css('border-bottom', '0.5px solid black');
                $('#temp').val('');
                $('#supplier_id').empty();
                $('#savesubtotal,#savetotal,#savetax,#savewtax,#savegrandtotal').val('0,00');
                $('.row_item').each(function(){
                    $(this).remove();
                });
                M.updateTextFields();
                $('#subtotal,#total,#tax,#grandtotal,#subtotal-convert,#discount-convert,#total-convert,#tax-convert,#wtax-convert,#grandtotal-convert,#rounding-convert').text('0,00');
                $('#purchase_request_id,#good_issue_id,#marketing_order_delivery_process_id').empty();
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }
                window.onbeforeunload = function() {
                    return null;
                };
                mode = '';
                $('#button-add-item').show();
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
                $('#validation_alert_done').hide();
                $('#validation_alert_done').html('');
            },
            onCloseEnd: function(modal, trigger){
                $('#body-done').empty();
                $('#tempDone').val('');
            }
        });
        
        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
            countAll();
            if($('.row_item').length == 0){
                mode = '';
            }
        });

        select2ServerSide('#supplier_id,#filter_supplier', '{{ url("admin/select2/supplier") }}');
        select2ServerSide('#purchase_request_id', '{{ url("admin/select2/purchase_request") }}');
        select2ServerSide('#good_issue_id', '{{ url("admin/select2/good_issue") }}');
        select2ServerSide('#marketing_order_delivery_process_id', '{{ url("admin/select2/marketing_order_delivery_process_po") }}');

        $("#table-detail th").resizable({
            minWidth: 100,
        });
    });

    function addDays(){
        if($('#inventory_type').val() == '2'){
            if($('#payment_term').val()){
                var result = new Date($('#received_date').val());
                result.setDate(result.getDate() + parseInt($('#payment_term').val()));
                $('#due_date').val(result.toISOString().split('T')[0]);
            }else{
                $('#due_date').val('');
            }
        }else{
            $('#due_date').val('');
        }
    }

    function refreshTotal(){
        if($('.row_item').length > 0){
            setTimeout(function() {
                countAll();
            }, 750); 
        }
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
        });

        myDiagram.model = $(go.GraphLinksModel,
        {
            copiesArrays: true,
            copiesArrayObjects: true,
            nodeDataArray: data,
            linkDataArray: link
        });    
            
    }

    var defaultValuePpn = 0, defaultValuePph;

    function chooseAllPpn(element){
        if($(element).is(':checked')){
            $('select[name^="arr_tax"]').each(function(){
                if(parseFloat($(this).val()) > 0){
                    defaultValuePpn = $(this).val();
                }else{
                    $(this).val(defaultValuePpn.toString()).formSelect();
                }
            });
        }else{
            $('select[name^="arr_tax"]').each(function(){
                $(this).val('0');
            });
        }
        countAll();
    }

    function chooseAllPph(element){
        if($(element).is(':checked')){
            $('select[name^="arr_wtax"]').each(function(){
                if(parseFloat($(this).val()) > 0){
                    defaultValuePph = $(this).val();
                }else{
                    $(this).val(defaultValuePph.toString()).formSelect();
                }
            });
        }else{
            $('select[name^="arr_wtax"]').each(function(){
                $(this).val('0');
            });
        }
        countAll();
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
                loadingOpen('#main');
            },
            success: function(response) {
                loadingClose('#main');

                makeTreeOrg(response.message,response.link);
                
                $('#modal3').modal('open');
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
    
    function getRowUnit(val){
        $('#tempPrice' + val).empty();
        $("#arr_warehouse" + val).empty();
        $("#unit_stock" + val).empty();
        $("#qty_stock" + val).empty().text('-');
        if($("#arr_item" + val).val()){
            if($("#arr_item" + val).select2('data')[0].old_prices.length > 0){
                $.each($("#arr_item" + val).select2('data')[0].old_prices, function(i, value) {
                    if($('#supplier_id').val()){
                        if(value.supplier_id == $('#supplier_id').val()){
                            $('#tempPrice' + val).append(`
                                <option value="` + value.price + `">` + value.purchase_code + ` Supplier ` + value.supplier_name + ` Tgl ` + value.post_date + `</option>
                            `);
                        }
                    }else{
                        $('#tempPrice' + val).append(`
                            <option value="` + value.price + `">` + value.purchase_code + ` Supplier ` + value.supplier_name + ` Tgl ` + value.post_date + `</option>
                        `);
                    }
                });
            }
            if($("#arr_item" + val).select2('data')[0].list_warehouse.length > 0){
                $.each($("#arr_item" + val).select2('data')[0].list_warehouse, function(i, value) {
                    $('#arr_warehouse' + val).append(`
                        <option value="` + value.id + `">` + value.name + `</option>
                    `);
                });
            }else{
                $("#arr_warehouse" + val).append(`
                    <option value="">--Gudang tidak diatur di master data Grup Item--</option>
                `);
            }
            $('#arr_unit' + val).empty();
            if($("#arr_item" + val).select2('data')[0].buy_units.length > 0){
                $.each($("#arr_item" + val).select2('data')[0].buy_units, function(i, value) {
                    $('#arr_unit' + val).append(`
                        <option value="` + value.id + `" data-conversion="` + value.conversion + `">` + value.code + `</option>
                    `);
                });
            }else{
                $("#arr_unit" + val).append(`
                    <option value="">--Satuan tidak diatur di master data Item--</option>
                `);
            }
            $("#unit_stock" + val).text($("#arr_item" + val).select2('data')[0].uom);
        }else{
            $("#arr_item" + val).empty();
            $("#arr_unit" + val).empty().append(`
                <option value="">--Silahkan pilih item--</option>
            `);
            $("#arr_warehouse" + val).append(`
                <option value="">--Silahkan pilih item--</option>
            `);
            $("#unit_stock" + val).text('-');
        }
        countRow(val);
    }

    var tempTerm = 0;

    function resetTerm(){
        if(tempTerm > 0){
            $('#payment_type').val('2').formSelect();
        }else{
            $('#payment_type').val('1').formSelect();
        }
        applyTerm();
        addDays();
    }

    function applyType(){
        if($('#inventory_type').val() == '1'){
            $('#due_date').val('');
            $('#gi-show,#pr-show').show();
            $('#sj-show').hide();
        }else if($('#inventory_type').val() == '2'){
            addDays();
            $('#sj-show').show();
            $('#gi-show,#pr-show').hide();
        }
    }

    function applyTerm(){
        if($('#payment_type').val() == '1'){
            $('#payment_term').val('0');
        }else if($('#payment_type').val() == '2'){
            $('#payment_term').val(tempTerm);
        }
    }

    function getTopSupplier(){
        if($("#supplier_id").val()){
            tempTerm = parseInt($("#supplier_id").select2('data')[0].top);
        }else{
            tempTerm = 0;
        }
        resetTerm();
    }

    function getCompanyAddress(){
        if($('#company_id').val()){
            let address = $('#company_id').find(':selected').data('address');
            $('#receiver_address').val(address);
        }
    }

    function getDetails(type){

        let nil;

        if(type == 'po'){
            nil = $('#purchase_request_id').val();
        }else if(type == 'gi'){
            nil = $('#good_issue_id').val();
        }else if(type == 'sj'){
            /* nil = $('#marketing_order_delivery_process_id').val();
            $('#inventory_type').val('2').trigger('change').formSelect();
            $('#pr-show,#gi-show').hide(); */
            swal({
                title: 'Ups!',
                text: 'Fitur ini masih belum siap.',
                icon: 'error'
            });
            return false;
        }

        if(mode){
            if(type !== mode){
                $('#purchase_request_id,#good_issue_id,#marketing_order_delivery_process_id').empty();
                swal({
                    title: 'Ups!',
                    text: 'Satu PO tidak boleh memiliki PR GI dan Surat Jalan bersamaan.',
                    icon: 'error'
                });
                return false;
            }
        }

        mode = type;

        if(nil){
            if(type == 'po'){
                $('#gi-show,#sj-show').hide();
            }else if(type == 'gi'){
                $('#pr-show,#sj-show').hide();
            }else if(type == 'sj'){
                
            }

            $.ajax({
                url: '{{ Request::url() }}/get_details',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: nil,
                    type : type,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('#modal1');
                },
                success: function(response) {
                    loadingClose('#modal1');

                    if(response.status == 500){
                        swal({
                            title: 'Ups!',
                            text: response.message,
                            icon: 'warning'
                        });
                    }else{
                        if(response.details.length > 0){
                            let countItem = $('.row_item').length + response.details.length;

                            if(countItem > 50){
                                swal({
                                    title: 'Ups!',
                                    text: 'Satu PO tidak boleh memiliki baris item lebih dari 50.',
                                    icon: 'error'
                                });
                                removeUsedData(response.id,type);
                                return false;
                            }

                            if(type == 'sj'){
                                $('#supplier_id').empty().append(`
                                    <option value="` + response.account_id + `">` + response.account_name + `</option>
                                `);
                            }

                            $('#list-used-data').append(`
                                <div class="chip purple darken-4 gradient-shadow white-text">
                                    ` + response.code + `
                                    <i class="material-icons close data-used" onclick="removeUsedData('` + response.id + `','` + type + `')">close</i>
                                </div>
                            `);

                            let no = $('.row_item').length + 1;

                            $.each(response.details, function(i, val) {
                                var count = makeid(10);

                                if(type == 'sj'){
                                    $('#last-row-item').before(`
                                        <tr class="row_item" data-id="` + response.id + `">
                                            <input type="hidden" name="arr_data[]" value="` + val.reference_id + `">
                                            <input type="hidden" name="arr_type[]" value="` + type + `">
                                            <td class="center">
                                                ` + no + `
                                            </td>
                                            <td class="center">
                                                <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                                    <i class="material-icons">delete</i>
                                                </a>
                                            </td>
                                            <td style="pointer-events: none;">
                                                <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]"></select>
                                            </td>
                                            <td>
                                                <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 1...">
                                            </td>
                                            <td>
                                                <input name="arr_note2[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 2...">
                                            </td>
                                            <td>
                                                <input name="arr_note3[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 3...">
                                            </td>
                                            <td>
                                                <input name="arr_qty[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" data-qty="0" style="text-align:right;width:100px;" id="rowQty`+ count +`">
                                            </td>
                                            <td class="center">
                                                <select class="browser-default" id="arr_unit` + count + `" name="arr_unit[]" required></select>
                                            </td>
                                            <td class="center" id="qty_stock` + count + `">
                                                -
                                            </td>
                                            <td class="center" id="unit_stock` + count + `">
                                                ` + val.uom + `
                                            </td>
                                            <td class="center">
                                                <input name="arr_price[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;" id="rowPrice`+ count +`">
                                            </td>
                                            <td>
                                                <select class="browser-default" id="arr_tax` + count + `" name="arr_tax[]" onchange="countAll();">
                                                    <option value="0" data-id="0">-- Pilih ini jika non-PPN --</option>
                                                    @foreach ($tax as $row)
                                                        <option value="{{ $row->percentage }}" {{ $row->is_default_ppn ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <label>
                                                    <input type="checkbox" id="arr_is_include_tax` + count + `" name="arr_is_include_tax[]" value="1" onclick="countAll();">
                                                    <span>Ya/Tidak</span>
                                                </label>
                                            </td>
                                            <td>
                                                <select class="browser-default" id="arr_wtax` + count + `" name="arr_wtax[]" onchange="countAll();">
                                                    <option value="0" data-id="0">-- Pilih ini jika non-PPh --</option>
                                                    @foreach ($wtax as $row)
                                                        <option value="{{ $row->percentage }}" {{ $row->is_default_pph ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="center">
                                                <input name="arr_disc1[]" onfocus="emptyThis(this);" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc1`+ count +`">
                                            </td>
                                            <td class="center">
                                                <input name="arr_disc2[]" onfocus="emptyThis(this);" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc2`+ count +`">
                                            </td>
                                            <td class="center">
                                                <input name="arr_disc3[]" onfocus="emptyThis(this);" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;" id="rowDisc3`+ count +`">
                                            </td>
                                            <td class="center">
                                                <span id="arr_subtotal` + count + `" class="arr_subtotal">0</span>
                                            </td>
                                            <td class="center">
                                                <input name="arr_nominal_total[]" type="text" value="0,00" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_total`+ count +`" readonly>
                                            </td>
                                            <td class="center">
                                                <input name="arr_nominal_tax[]" type="text" value="0,00" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_tax`+ count +`" readonly>
                                            </td>
                                            <td class="center">
                                                <input name="arr_nominal_wtax[]" type="text" value="0,00" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_wtax`+ count +`" readonly>
                                            </td>
                                            <td class="center">
                                                <input name="arr_nominal_grandtotal[]" type="text" value="0,00" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_grandtotal`+ count +`" readonly>
                                            </td>
                                            <td>
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
                                                    @foreach ($machine as $row)
                                                        <option value="{{ $row->id }}" data-line="{{ $row->line_id }}">{{ $row->name }}</option>
                                                    @endforeach    
                                                </select>
                                            </td>
                                            <td>
                                                <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                                                    <option value="">--{{ __('translations.empty') }}--</option>
                                                    @foreach ($department as $rowdept)
                                                        <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                                                    @endforeach
                                                </select>    
                                            </td>
                                            <td class="center">
                                                -
                                            </td>
                                            <td>
                                                <input name="arr_requester[]" type="text" placeholder="Yang meminta barang & jasa / requester" value="` + val.requester + `" required>
                                            </td>
                                            <td>
                                                <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                                            </td>
                                        </tr>
                                    `);
                                    select2ServerSide('#arr_unit' + count, '{{ url("admin/select2/unit") }}');
                                    select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
                                }else{
                                    $('#last-row-item').before(`
                                        <tr class="row_item" data-id="` + response.id + `">
                                            <input type="hidden" name="arr_data[]" value="` + val.reference_id + `">
                                            <input type="hidden" name="arr_type[]" value="` + type + `">
                                            <td class="center">
                                                ` + no + `
                                            </td>
                                            <td class="center">
                                                <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                                    <i class="material-icons">delete</i>
                                                </a>
                                            </td>
                                            <td style="pointer-events: none;">
                                                <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                                            </td>
                                            <td>
                                                <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 1..." value="` + val.note + `">
                                            </td>
                                            <td>
                                                <input name="arr_note2[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 2..." value="` + val.note2 + `">
                                            </td>
                                            <td>
                                                <input name="arr_note3[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 3..." value="` + val.note3 + `">
                                            </td>
                                            <td>
                                                <input name="arr_qty[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `');" data-qty="` + val.qty + `" data-stockqty="` + val.qty + `" style="text-align:right;width:100px;" id="rowQty`+ count +`">
                                            </td>
                                            <td class="center">
                                                <select class="browser-default" id="arr_unit` + count + `" name="arr_unit[]" required onchange="countRow('` + count + `');">
                                                    <option value="">--Silahkan pilih item--</option>    
                                                </select>
                                            </td>
                                            <td class="center" id="qty_stock` + count + `">
                                                ` + (type == 'po' ? '' : val.qty) + `
                                            </td>
                                            <td class="center" id="unit_stock` + count + `">
                                                ` + val.uom + `
                                            </td>
                                            <td class="center">
                                                <input list="tempPrice` + count + `" name="arr_price[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;" id="rowPrice`+ count +`">
                                                <datalist id="tempPrice` + count + `"></datalist>
                                            </td>
                                            <td>
                                                <select class="browser-default" id="arr_tax` + count + `" name="arr_tax[]" onchange="countAll();">
                                                    <option value="0" data-id="0">-- Pilih ini jika non-PPN --</option>
                                                    @foreach ($tax as $row)
                                                        <option value="{{ $row->percentage }}" {{ $row->is_default_ppn ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <label>
                                                    <input type="checkbox" id="arr_is_include_tax` + count + `" name="arr_is_include_tax[]" value="1" onclick="countAll();">
                                                    <span>Ya/Tidak</span>
                                                </label>
                                            </td>
                                            <td>
                                                <select class="browser-default" id="arr_wtax` + count + `" name="arr_wtax[]" onchange="countAll();">
                                                    <option value="0" data-id="0">-- Pilih ini jika non-PPh --</option>
                                                    @foreach ($wtax as $row)
                                                        <option value="{{ $row->percentage }}" {{ $row->is_default_pph ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="center">
                                                <input name="arr_disc1[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc1`+ count +`">
                                            </td>
                                            <td class="center">
                                                <input name="arr_disc2[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc2`+ count +`">
                                            </td>
                                            <td class="center">
                                                <input name="arr_disc3[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;" id="rowDisc3`+ count +`">
                                            </td>
                                            <td class="center">
                                                <span id="arr_subtotal` + count + `" class="arr_subtotal">0</span>
                                            </td>
                                            <td class="center">
                                                <input name="arr_nominal_total[]" type="text" value="0,00" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_total`+ count +`" readonly>
                                            </td>
                                            <td class="center">
                                                <input name="arr_nominal_tax[]" type="text" value="0,00" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_tax`+ count +`" readonly>
                                            </td>
                                            <td class="center">
                                                <input name="arr_nominal_wtax[]" type="text" value="0,00" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_wtax`+ count +`" readonly>
                                            </td>
                                            <td class="center">
                                                <input name="arr_nominal_grandtotal[]" type="text" value="0,00" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_grandtotal`+ count +`" readonly>
                                            </td>
                                            <td>
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
                                                    @foreach ($machine as $row)
                                                        <option value="{{ $row->id }}" data-line="{{ $row->line_id }}">{{ $row->name }}</option>
                                                    @endforeach    
                                                </select>
                                            </td>
                                            <td>
                                                <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                                                    <option value="">--{{ __('translations.empty') }}--</option>
                                                    @foreach ($department as $rowdept)
                                                        <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                                                    @endforeach
                                                </select>    
                                            </td>
                                            <td>
                                                <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]"></select>
                                            </td>
                                            <td>
                                                <input name="arr_requester[]" type="text" placeholder="Yang meminta barang & jasa / requester" value="` + val.requester + `" required>
                                            </td>
                                            <td>
                                                <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                                            </td>
                                        </tr>
                                    `);
                                    $('#arr_item' + count).append(`
                                        <option value="` + val.item_id + `">` + val.item_name + `</option>
                                    `);
                                    $('#arr_warehouse' + count).append(`
                                        <option value="` + val.warehouse_id + `">` + val.warehouse_name + `</option>
                                    `);
                                    select2ServerSide('#arr_item' + count, '{{ url("admin/select2/purchase_item") }}');
                                    $('#arr_place' + count).val(val.place_id);
                                    $('#arr_line' + count).val(val.line_id);
                                    $('#arr_machine' + count).val(val.machine_id);
                                    $('#arr_department' + count).val(val.department_id);
                                    if(val.old_prices.length > 0){
                                        $.each(val.old_prices, function(i, value) {
                                            if($('#supplier_id').val()){
                                                if(value.supplier_id == $('#supplier_id').val()){
                                                    $('#tempPrice' + count).append(`
                                                        <option value="` + value.price + `">` + value.purchase_code + ` Supplier ` + value.supplier_name + ` Tgl ` + value.post_date + `</option>
                                                    `);
                                                }
                                            }else{
                                                $('#tempPrice' + count).append(`
                                                    <option value="` + value.price + `">` + value.purchase_code + ` Supplier ` + value.supplier_name + ` Tgl ` + value.post_date + `</option>
                                                `);
                                            }
                                        });
                                    }
                                }

                                if(val.project_id){
                                    $('#arr_project' + count).append(`
                                        <option value="` + val.project_id + `">` + val.project_name + `</option>
                                    `);
                                }

                                select2ServerSide('#arr_project' + count, '{{ url("admin/select2/project") }}');

                                if(val.buy_units.length > 0){
                                    $('#arr_unit' + count).empty();
                                    $.each(val.buy_units, function(i, value) {
                                        $('#arr_unit' + count).append(`
                                            <option value="` + value.id + `" ` + (value.id == val.item_unit_id ? 'selected' : '') + ` data-conversion="` + value.conversion + `">` + value.code + `</option>
                                        `);
                                    });
                                }

                                if(!val.item_unit_id){
                                    applyConversion(count);
                                }else{
                                    $('#arr_unit' + count).trigger('change');
                                }

                                no++;
                            });
                        }
                    }
                    M.updateTextFields();
                    $('#purchase_request_id,#good_issue_id,#marketing_order_delivery_process_id').empty();
                    $('#button-add-item').hide();
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
            /* $('.row_item').each(function(){
                $(this).remove();
            }); */
        }
    }

    function applyConversion(id){
        if($('#rowQty' + id).data('stockqty')){
            let qtyRaw = parseFloat($('#rowQty' + id).data('stockqty').toString().replaceAll(".", "").replaceAll(",",".")), conversion = parseFloat($('#arr_unit' + id).find(':selected').data('conversion'));
            let newQty = qtyRaw / conversion;
            $('#rowQty' + id).data('qty',formatRupiahIni(newQty.toFixed(2).toString().replace('.',',')));
            $('#rowQty' + id).val(formatRupiahIni(newQty.toFixed(2).toString().replace('.',',')));
        }
    }

    function addItem(){

        let countItem = $('.row_item').length;

        if(countItem > 49){
            swal({
                title: 'Ups!',
                text: 'Satu PO tidak boleh memiliki baris item lebih dari 50.',
                icon: 'error'
            });
            return false;
        }

        var count = makeid(10);
        let no = $('.row_item').length + 1;
        if($('#inventory_type').val() == '1'){
            $('#last-row-item').before(`
                <tr class="row_item">
                    <input type="hidden" name="arr_data[]" value="0">
                    <input type="hidden" name="arr_type[]" value="">
                    <td class="center">
                        ` + no + `
                    </td>
                    <td class="center">
                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                            <i class="material-icons">delete</i>
                        </a>
                    </td>
                    <td>
                        <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                    </td>
                    <td>
                        <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 1...">
                    </td>
                    <td>
                        <input name="arr_note2[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 2...">
                    </td>
                    <td>
                        <input name="arr_note3[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 3...">
                    </td>
                    <td>
                        <input name="arr_qty[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" data-qty="0" style="text-align:right;width:100px;" id="rowQty`+ count +`">
                    </td>
                    <td class="center">
                        <select class="browser-default" id="arr_unit` + count + `" name="arr_unit[]" required onchange="countRow('` + count + `')">
                            <option value="">--Silahkan pilih item--</option>    
                        </select>
                    </td>
                    <td class="center" id="qty_stock` + count + `">
                        -
                    </td>
                    <td class="center" id="unit_stock` + count + `">
                        -
                    </td>
                    <td class="center">
                        <input list="tempPrice` + count + `" name="arr_price[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;" id="rowPrice`+ count +`">
                        <datalist id="tempPrice` + count + `"></datalist>
                    </td>
                    <td>
                        <select class="browser-default" id="arr_tax` + count + `" name="arr_tax[]" onchange="countAll();">
                            <option value="0" data-id="0">-- Pilih ini jika non-PPN --</option>
                            @foreach ($tax as $row)
                                <option value="{{ $row->percentage }}" {{ $row->is_default_ppn ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <label>
                            <input type="checkbox" id="arr_is_include_tax` + count + `" name="arr_is_include_tax[]" value="1" onclick="countAll();">
                            <span>Ya/Tidak</span>
                        </label>
                    </td>
                    <td>
                        <select class="browser-default" id="arr_wtax` + count + `" name="arr_wtax[]" onchange="countAll();">
                            <option value="0" data-id="0">-- Pilih ini jika non-PPh --</option>
                            @foreach ($wtax as $row)
                                <option value="{{ $row->percentage }}" {{ $row->is_default_pph ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="center">
                        <input name="arr_disc1[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc1`+ count +`">
                    </td>
                    <td class="center">
                        <input name="arr_disc2[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc2`+ count +`">
                    </td>
                    <td class="center">
                        <input name="arr_disc3[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;" id="rowDisc3`+ count +`">
                    </td>
                    <td class="center">
                        <span id="arr_subtotal` + count + `" class="arr_subtotal">0</span>
                    </td>
                    <td class="center">
                        <input name="arr_nominal_total[]" type="text" value="0,00" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_total`+ count +`" readonly>
                    </td>
                    <td class="center">
                        <input name="arr_nominal_tax[]" type="text" value="0,00" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_tax`+ count +`" readonly>
                    </td>
                    <td class="center">
                        <input name="arr_nominal_wtax[]" type="text" value="0,00" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_wtax`+ count +`" readonly>
                    </td>
                    <td class="center">
                        <input name="arr_nominal_grandtotal[]" type="text" value="0,00" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_grandtotal`+ count +`" readonly>
                    </td>
                    <td>
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
                            @foreach ($machine as $row)
                                <option value="{{ $row->id }}" data-line="{{ $row->line_id }}">{{ $row->name }}</option>
                            @endforeach    
                        </select>
                    </td>
                    <td>
                        <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                            <option value="">--{{ __('translations.empty') }}--</option>
                            @foreach ($department as $rowdept)
                                <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                            @endforeach
                        </select>    
                    </td>
                    <td>
                        <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">
                            <option value="">--Silahkan pilih item--</option>
                        </select>
                    </td>
                    <td>
                        <input name="arr_requester[]" type="text" placeholder="Yang meminta barang & jasa / requester" required>
                    </td>
                    <td>
                        <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                    </td>
                </tr>
            `);
            select2ServerSide('#arr_item' + count, '{{ url("admin/select2/purchase_item") }}');
            
        }else if($('#inventory_type').val() == '2'){

            $('#last-row-item').before(`
                <tr class="row_item">
                    <input type="hidden" name="arr_data[]" value="0">
                    <input type="hidden" name="arr_type[]" value="">
                    <td class="center">
                        ` + no + `
                    </td>
                    <td class="center">
                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                            <i class="material-icons">delete</i>
                        </a>
                    </td>
                    <td>
                        <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]"></select>
                    </td>
                    <td>
                        <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 1...">
                    </td>
                    <td>
                        <input name="arr_note2[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 2...">
                    </td>
                    <td>
                        <input name="arr_note3[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 3...">
                    </td>
                    <td>
                        <input name="arr_qty[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" data-qty="0" style="text-align:right;width:100px;" id="rowQty`+ count +`">
                    </td>
                    <td class="center">
                        <select class="browser-default" id="arr_unit` + count + `" name="arr_unit[]" required></select>
                    </td>
                    <td class="center" id="qty_stock` + count + `">
                        -
                    </td>
                    <td class="center" id="unit_stock` + count + `">
                        -
                    </td>
                    <td class="center">
                        <input name="arr_price[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;" id="rowPrice`+ count +`">
                    </td>
                    <td>
                        <select class="browser-default" id="arr_tax` + count + `" name="arr_tax[]" onchange="countAll();">
                            <option value="0" data-id="0">-- Pilih ini jika non-PPN --</option>
                            @foreach ($tax as $row)
                                <option value="{{ $row->percentage }}" {{ $row->is_default_ppn ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <label>
                            <input type="checkbox" id="arr_is_include_tax` + count + `" name="arr_is_include_tax[]" value="1" onclick="countAll();">
                            <span>Ya/Tidak</span>
                        </label>
                    </td>
                    <td>
                        <select class="browser-default" id="arr_wtax` + count + `" name="arr_wtax[]" onchange="countAll();">
                            <option value="0" data-id="0">-- Pilih ini jika non-PPh --</option>
                            @foreach ($wtax as $row)
                                <option value="{{ $row->percentage }}" {{ $row->is_default_pph ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="center">
                        <input name="arr_disc1[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc1`+ count +`">
                    </td>
                    <td class="center">
                        <input name="arr_disc2[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc2`+ count +`">
                    </td>
                    <td class="center">
                        <input name="arr_disc3[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;" id="rowDisc3`+ count +`">
                    </td>
                    <td class="center">
                        <span id="arr_subtotal` + count + `" class="arr_subtotal">0</span>
                    </td>
                    <td class="center">
                        <input name="arr_nominal_total[]" type="text" value="0,00" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_total`+ count +`" readonly>
                    </td>
                    <td class="center">
                        <input name="arr_nominal_tax[]" type="text" value="0,00" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_tax`+ count +`" readonly>
                    </td>
                    <td class="center">
                        <input name="arr_nominal_wtax[]" type="text" value="0,00" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_wtax`+ count +`" readonly>
                    </td>
                    <td class="center">
                        <input name="arr_nominal_grandtotal[]" type="text" value="0,00" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_grandtotal`+ count +`" readonly>
                    </td>
                    <td>
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
                            @foreach ($machine as $row)
                                <option value="{{ $row->id }}" data-line="{{ $row->line_id }}">{{ $row->name }}</option>
                            @endforeach    
                        </select>
                    </td>
                    <td>
                        <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                            <option value="">--{{ __('translations.empty') }}--</option>
                            @foreach ($department as $rowdept)
                                <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                            @endforeach
                        </select>    
                    </td>
                    <td class="center">
                        -
                    </td>
                    <td>
                        <input name="arr_requester[]" type="text" placeholder="Yang meminta barang & jasa / requester" required>
                    </td>
                    <td>
                        <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                    </td>
                </tr>
            `);
            select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
            select2ServerSide('#arr_unit' + count, '{{ url("admin/select2/unit") }}');
        }
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

    String.prototype.replaceAt = function(index, replacement) {
        return this.substring(0, index) + replacement + this.substring(index + replacement.length);
    };

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

    function removeUsedData(id,type){
        $.ajax({
            url: '{{ Request::url() }}/remove_used_data',
            type: 'POST',
            dataType: 'JSON',
            data: { 
                id : id,
                type : type,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                
            },
            success: function(response) {
                $('.row_item[data-id="' + id + '"]').remove();
                countAll();
                if($('.row_item').length == 0){
                    mode = '';
                    $('#pr-show,#gi-show,#button-add-item').show();
                }
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
                'columnsToggle',
                'selectAll',
                'selectNone',
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
            },
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    'status' : $('#filter_status').val(),
                    inventory_type : $('#filter_inventory').val(),
                    shipping_type : $('#filter_shipping').val(),
                    'supplier_id[]' : $('#filter_supplier').val(),
                    company_id : $('#filter_company').val(),
                    payment_type : $('#filter_payment').val(),
                    'currency_id[]' : $('#filter_currency').val(),
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
                { name: 'supplier_id', className: '' },
                { name: 'inventory_type', className: 'center-align' },
                { name: 'shipping_type', className: 'center-align' },
                { name: 'company_id', className: 'center-align' },
                { name: 'document_no', className: 'center-align' },
                { name: 'document_po', className: 'center-align' },
                { name: 'payment_tye', className: 'center-align' },
                { name: 'payment_term', className: 'center-align' },
                { name: 'currency_id', className: 'center-align' },
                { name: 'currency_rate', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'delivery_date', className: 'center-align' },
                { name: 'receiver_name', className: 'center-align' },
                { name: 'receiver_address', className: 'center-align' },
                { name: 'receiver_phone', className: 'center-align' },
                { name: 'received_date', className: '' },
                { name: 'note', className: '' },
                { name: 'subtotal', className: 'right-align' },
                { name: 'discount', className: 'right-align' },
                { name: 'total', className: 'right-align' },
                { name: 'tax', className: 'right-align' },
                { name: 'wtax', className: 'right-align' },
                { name: 'rounding', className: 'right-align' },
                { name: 'grandtotal', className: 'right-align' },
              { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'by', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'right-align' },
            ],
        });
        $('.dt-buttons').appendTo('#datatable_buttons');

        $('select[name="datatable_serverside_length"]').addClass('browser-default');
	}

    function printData(){
        var arr_id_temp=[];
        $.map(window.table.rows('.selected').nodes(), function (item) {
            var poin = $(item).find('td:nth-child(2)').text().trim();
            arr_id_temp.push(poin);
        });
        $.ajax({
            url: '{{ Request::url() }}/print',
            type: 'POST',
            dataType: 'JSON',
            data: {
                arr_id: arr_id_temp,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
            },
            success: function(response) {
                printService.submit({
                    'type': 'INVOICE',
                    'url': response.message
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
                $('#modal4').modal('open');
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
                let passedUpload = true;
                var files = document.getElementById('file');

                if(files.files.length > 0){
                    for (var i = 0; i < files.files.length; i++) {
                        var imageSize = files.files[i].size;
                        if(Math.round(imageSize/1024) >= 7168){
                            passedUpload = false;
                        }
                    }
                }

                if(passedUpload){
                    var formData = new FormData($('#form_data')[0]), passedUnit = true;

                    formData.delete("arr_tax[]");
                    formData.delete("arr_is_include_tax[]");
                    formData.delete("arr_wtax[]");
                    formData.delete("arr_wtax[]");
                    formData.delete("arr_warehouse[]");
                    formData.delete("arr_line[]");
                    formData.delete("arr_project[]");

                    if($('select[name^="arr_unit[]"]').length > 0){
                        $('select[name^="arr_unit[]"]').each(function(index){
                            if(!$(this).val()){
                                passedUnit = false;
                            }
                        });
                    }

                    $('select[name^="arr_tax"]').each(function(index){
                        formData.append('arr_tax[]',$(this).val());
                        formData.append('arr_tax_id[]',$('option:selected',this).data('id'));
                        formData.append('arr_wtax_id[]',$('select[name^="arr_wtax"]').eq(index).find(':selected').data('id'));
                        formData.append('arr_is_include_tax[]',($('input[name^="arr_is_include_tax"]').eq(index).is(':checked') ? '1' : '0'));
                        formData.append('arr_wtax[]',$('select[name^="arr_wtax"]').eq(index).val());
                        formData.append('arr_line[]',($('select[name^="arr_line"]').eq(index).val() ? $('select[name^="arr_line"]').eq(index).val() : ''));
                        formData.append('arr_warehouse[]',($('select[name^="arr_warehouse"]').eq(index).val() ? $('select[name^="arr_warehouse"]').eq(index).val() : ''));
                        formData.append('arr_project[]',($('select[name^="arr_project[]"]').eq(index).val() ? $('select[name^="arr_project[]"]').eq(index).val() : ''));
                    });

                    if(passedUnit){
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
                                $('input').css('border', 'none');
                                $('input').css('border-bottom', '0.5px solid black');
                                loadingClose('#modal1');
                                if(response.status == 200) {
                                    success();
                                    M.toast({
                                        html: response.message
                                    });
                                } else if(response.status == 422) {
                                    $('#validation_alert').show();
                                    $('.modal-content').scrollTop(0);
                                    
                                    swal({
                                        title: 'Ups! Validation',
                                        text: 'Check your form.',
                                        icon: 'warning'
                                    });
                                    $.each(response.error, function(field, errorMessage) {
                                        $('#' + field).addClass('error-input');
                                        $('#' + field).css('border', '1px solid red');
                                        
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
                        swal({
                            title: 'Ups!',
                            text: 'Salah satu item belum diatur satuannya.',
                            icon: 'error'
                        });
                    }
                }else{
                    swal({
                        title: 'Ups!',
                        text: 'Ukuran masing-masing file adalah maksimal 2048 Kb / 2 Mb.',
                        icon: 'error'
                    });
                }
            }
        });
    }

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
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
                $('#modal1').modal('open');
                $('#temp').val(id);
                $('#code_place_id').val(response.code_place_id).formSelect();
                $('#code').val(response.code);
                $('#supplier_id').empty();
                $('#supplier_id').append(`
                    <option value="` + response.account_id + `">` + response.supplier_name + `</option>
                `);
                $('#inventory_type').val(response.inventory_type).formSelect();
                $('#shipping_type').val(response.shipping_type).formSelect(); 
                $('#company_id').val(response.company_id).formSelect();
                $('#document_no').val(response.document_no);
                $('#payment_type').val(response.payment_type).formSelect();
                $('#payment_term').val(response.payment_term);
                $('#currency_id').val(response.currency_id).formSelect();
                $('#currency_rate').val(response.currency_rate);
                $('#post_date').val(response.post_date);
                $('#delivery_date').val(response.delivery_date);
                $('#received_date').val(response.received_date);
                $('#percent_tax').val(response.percent_tax);
                $('#receiver_name').val(response.receiver_name);
                $('#receiver_address').val(response.receiver_address);
                $('#receiver_phone').val(response.receiver_phone);
                
                $('#note').val(response.note);
                $('#note_external').val(response.note_external);
                M.textareaAutoResize($('#note_external'));
                $('#subtotal').text(response.subtotal);
                $('#savesubtotal').val(response.subtotal);
                $('#discount').val(response.discount);
                $('#total').text(response.total);
                $('#savetotal').val(response.total);
                $('#tax').text(response.tax);
                $('#savetax').val(response.tax);
                $('#wtax').val(response.wtax);
                $('#savewtax').val(response.wtax);
                $('#grandtotal').text(response.grandtotal);
                $('#savegrandtotal').val(response.grandtotal);
                $('#rounding').val(response.rounding);

                $('#subtotal-convert').text(response.subtotal_convert);
                $('#discount-convert').text(response.discount_convert);
                $('#total-convert').text(response.total_convert);
                $('#tax-convert').text(response.tax_convert);
                $('#wtax-convert').text(response.wtax_convert);
                $('#rounding-convert').text(response.rounding_convert);
                $('#grandtotal-convert').text(response.grandtotal_convert);

                tempTerm = response.top_master;
                
                if(response.details.length > 0){
                    $('.row_item').each(function(){
                        $(this).remove();
                    });
                    if(response.inventory_type == '1'){
                        $('#button-add-item').hide();
                    }else if(response.inventory_type == '2'){
                        $('#button-add-item').show();
                    }
                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        if(response.inventory_type == '1'){
                            $('#last-row-item').before(`
                                <tr class="row_item">
                                    <input type="hidden" name="arr_data[]" value="` + val.reference_id + `">
                                    <input type="hidden" name="arr_type[]" value="` + val.type + `">
                                    <td class="center">
                                        ` + (i + 1) + `
                                    </td>
                                    <td class="center">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);" onclick="removeUsedData('` + val.id + `')">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                    <td>
                                        <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                                    </td>
                                    <td>
                                        <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 1..." value="` + val.note + `">
                                    </td>
                                    <td>
                                        <input name="arr_note2[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 2..." value="` + val.note2 + `">
                                    </td>
                                    <td>
                                        <input name="arr_note3[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 3..." value="` + val.note3 + `">
                                    </td>
                                    <td>
                                        <input name="arr_qty[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" data-qty="` + val.qty_limit + `" style="text-align:right;width:100px;" id="rowQty`+ count +`">
                                    </td>
                                    <td class="center">
                                        <select class="browser-default" id="arr_unit` + count + `" name="arr_unit[]" required onchange="countRow('` + count + `')">
                                            <option value="">--Silahkan pilih item--</option>    
                                        </select>
                                    </td>
                                    <td class="center" id="qty_stock` + count + `">
                                        ` + val.qty_stock + `
                                    </td>
                                    <td class="center" id="unit_stock` + count + `">
                                        ` + val.unit_stock + `
                                    </td>
                                    <td class="center">
                                        <input name="arr_price[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.price + `" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;" id="rowPrice`+ count +`">
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_tax` + count + `" name="arr_tax[]" onchange="countAll();">
                                            <option value="0" data-id="0">-- Pilih ini jika non-PPN --</option>
                                            @foreach ($tax as $row)
                                                <option value="{{ $row->percentage }}" data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <label>
                                            <input type="checkbox" id="arr_is_include_tax` + count + `" name="arr_is_include_tax[]" value="1" onclick="countAll();">
                                            <span>Ya/Tidak</span>
                                        </label>
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_wtax` + count + `" name="arr_wtax[]" onchange="countAll();">
                                            <option value="0" data-id="0">-- Pilih ini jika non-PPh --</option>
                                            @foreach ($wtax as $row)
                                                <option value="{{ $row->percentage }}" data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="center">
                                        <input name="arr_disc1[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.disc1 + `" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc1`+ count +`">
                                    </td>
                                    <td class="center">
                                        <input name="arr_disc2[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.disc2 + `" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc2`+ count +`">
                                    </td>
                                    <td class="center">
                                        <input name="arr_disc3[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.disc3 + `" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;" id="rowDisc3`+ count +`">
                                    </td>
                                    <td class="center">
                                        <span id="arr_subtotal` + count + `" class="arr_subtotal">` + val.subtotal + `</span>
                                    </td>
                                    <td class="center">
                                        <input name="arr_nominal_total[]" type="text" value="` + val.total  + `" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_total`+ count +`" readonly>
                                    </td>
                                    <td class="center">
                                        <input name="arr_nominal_tax[]" type="text" value="` + val.tax  + `" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_tax`+ count +`" readonly>
                                    </td>
                                    <td class="center">
                                        <input name="arr_nominal_wtax[]" type="text" value="` + val.wtax  + `" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_wtax`+ count +`" readonly>
                                    </td>
                                    <td class="center">
                                        <input name="arr_nominal_grandtotal[]" type="text" value="` + val.grandtotal  + `" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_grandtotal`+ count +`" readonly>
                                    </td>
                                    <td>
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
                                            @foreach ($machine as $row)
                                                <option value="{{ $row->id }}" data-line="{{ $row->line_id }}">{{ $row->name }}</option>
                                            @endforeach    
                                        </select>
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                                            <option value="">--{{ __('translations.empty') }}--</option>
                                            @foreach ($department as $rowdept)
                                                <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                                            @endforeach
                                        </select>    
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]"></select>
                                    </td>
                                    <td>
                                        <input name="arr_requester[]" type="text" placeholder="Yang meminta barang & jasa / requester" value="` + val.requester + `" required>
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                                    </td>
                                </tr>
                            `);
                            $('#arr_item' + count).append(`
                                <option value="` + val.item_id + `">` + val.item_name + `</option>
                            `);
                            select2ServerSide('#arr_item' + count, '{{ url("admin/select2/purchase_item") }}');
                            if(val.is_include_tax){
                                $('#arr_is_include_tax' + count).prop( "checked", true);
                            }
                            $('#arr_warehouse' + count).append(`
                                <option value="` + val.warehouse_id + `">` + val.warehouse_name + `</option>
                            `);
                            $('#arr_place' + count).val(val.place_id);
                            $('#arr_line' + count).val(val.line_id);
                            $('#arr_department' + count).val(val.department_id);
                            $('#arr_machine' + count).val(val.machine_id);
                            $("#arr_tax" + count + " option[data-id='" + val.tax_id + "']").prop("selected",true);
                            $("#arr_wtax" + count + " option[data-id='" + val.wtax_id + "']").prop("selected",true);

                            if(val.buy_units.length > 0){
                                $('#arr_unit' + count).empty();
                                $.each(val.buy_units, function(i, value) {
                                    $('#arr_unit' + count).append(`
                                        <option value="` + value.id + `" ` + (value.id == val.item_unit_id ? 'selected' : '') + ` data-conversion="` + value.conversion + `">` + value.code + `</option>
                                    `);
                                });
                            }

                        }else if(response.inventory_type == '2'){

                            $('#last-row-item').before(`
                                <tr class="row_item">
                                    <input type="hidden" name="arr_data[]" value="` + val.reference_id + `">
                                    <input type="hidden" name="arr_type[]" value="` + val.type + `">
                                    <td class="center">
                                        ` + (i + 1) + `
                                    </td>
                                    <td class="center">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]"></select>
                                    </td>
                                    <td>
                                        <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 1..." value="` + val.note + `">
                                    </td>
                                    <td>
                                        <input name="arr_note2[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 2..." value="` + val.note2 + `">
                                    </td>
                                    <td>
                                        <input name="arr_note3[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 3..." value="` + val.note3 + `">
                                    </td>
                                    <td>
                                        <input name="arr_qty[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" data-qty="` + val.qty + `" style="text-align:right;width:100px;" id="rowQty`+ count +`">
                                    </td>
                                    <td class="center">
                                        <select class="browser-default" id="arr_unit` + count + `" name="arr_unit[]" required></select>
                                    </td>
                                    <td class="center" id="qty_stock` + count + `">
                                        -
                                    </td>
                                    <td class="center" id="unit_stock` + count + `">
                                        -
                                    </td>
                                    <td class="center">
                                        <input name="arr_price[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.price + `" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;" id="rowPrice`+ count +`">
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_tax` + count + `" name="arr_tax[]" onchange="countAll();">
                                            <option value="0" data-id="0">-- Pilih ini jika non-PPN --</option>
                                            @foreach ($tax as $row)
                                                <option value="{{ $row->percentage }}" data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <label>
                                            <input type="checkbox" id="arr_is_include_tax` + count + `" name="arr_is_include_tax[]" value="1" onclick="countAll();">
                                            <span>Ya/Tidak</span>
                                        </label>
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_wtax` + count + `" name="arr_wtax[]" onchange="countAll();">
                                            <option value="0" data-id="0">-- Pilih ini jika non-PPh --</option>
                                            @foreach ($wtax as $row)
                                                <option value="{{ $row->percentage }}" data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="center">
                                        <input name="arr_disc1[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.disc1 + `" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc1`+ count +`">
                                    </td>
                                    <td class="center">
                                        <input name="arr_disc2[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.disc2 + `" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc2`+ count +`">
                                    </td>
                                    <td class="center">
                                        <input name="arr_disc3[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.disc3 + `" onkeyup="formatRupiahNominal(this);countRow('` + count + `')" style="text-align:right;" id="rowDisc3`+ count +`">
                                    </td>
                                    <td class="center">
                                        <span id="arr_subtotal` + count + `" class="arr_subtotal">` + val.subtotal + `</span>
                                    </td>
                                    <td class="center">
                                        <input name="arr_nominal_total[]" type="text" value="` + val.total  + `" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_total`+ count +`" readonly>
                                    </td>
                                    <td class="center">
                                        <input name="arr_nominal_tax[]" type="text" value="` + val.tax  + `" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_tax`+ count +`" readonly>
                                    </td>
                                    <td class="center">
                                        <input name="arr_nominal_wtax[]" type="text" value="` + val.wtax  + `" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_wtax`+ count +`" readonly>
                                    </td>
                                    <td class="center">
                                        <input name="arr_nominal_grandtotal[]" type="text" value="` + val.grandtotal  + `" onkeyup="formatRupiahNominal(this);" style="text-align:right;" id="arr_nominal_grandtotal`+ count +`" readonly>
                                    </td>
                                    <td>
                                        <select class="form-control" id="arr_place` + count + `" name="arr_place[]">
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
                                            @foreach ($machine as $row)
                                                <option value="{{ $row->id }}" data-line="{{ $row->line_id }}">{{ $row->name }}</option>
                                            @endforeach    
                                        </select>
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                                            <option value="">--{{ __('translations.empty') }}--</option>
                                            @foreach ($department as $rowdept)
                                                <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                                            @endforeach
                                        </select>    
                                    </td>
                                    <td class="center">
                                        -
                                    </td>
                                    <td>
                                        <input name="arr_requester[]" type="text" placeholder="Yang meminta barang & jasa / requester" value="` + val.requester + `" required>
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                                    </td>
                                </tr>
                            `);
                            if(val.is_include_tax){
                                $('#arr_is_include_tax' + count).prop( "checked", true);
                            }
                            if(val.coa_unit_id){
                                $('#arr_unit' + count).append(`
                                    <option value="` + val.coa_unit_id + `">` + val.coa_unit_name + `</option>
                                `);
                            }
                            select2ServerSide('#arr_unit' + count, '{{ url("admin/select2/unit") }}');
                            $('#arr_coa' + count).append(`
                                <option value="` + val.coa_id + `">` + val.coa_name + `</option>
                            `);
                            select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
                            $('#arr_place' + count).val(val.place_id);
                            $('#arr_department' + count).val(val.department_id);
                            $('#arr_line' + count).val(val.line_id);
                            $('#arr_machine' + count).val(val.machine_id);
                            $("#arr_tax" + count + " option[data-id='" + val.tax_id + "']").prop("selected",true);
                            $("#arr_wtax" + count + " option[data-id='" + val.wtax_id + "']").prop("selected",true);
                        }

                        if(val.project_id){
                            $('#arr_project' + count).append(`
                                <option value="` + val.project_id + `">` + val.project_name + `</option>
                            `);
                        }

                        select2ServerSide('#arr_project' + count, '{{ url("admin/select2/project") }}');
                    });
                }

                $('#inventory_type').trigger('change');
                
                $('.modal-content').scrollTop(0);
                $('#note').focus();
                M.updateTextFields();
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

    function countRow(id){
        if($('#arr_item' + id).val() || $('#arr_coa' + id).val()){
            var qty = parseFloat($('#rowQty' + id).val().replaceAll(".", "").replaceAll(",",".")), 
                qtylimit = parseFloat($('#rowQty' + id).data('qty').toString().replaceAll(".", "").replaceAll(",",".")), 
                price = parseFloat($('#rowPrice' + id).val().replaceAll(".", "").replaceAll(",",".")), 
                disc1 = parseFloat($('#rowDisc1' + id).val().replaceAll(".", "").replaceAll(",",".")), 
                disc2 = parseFloat($('#rowDisc2' + id).val().replaceAll(".", "").replaceAll(",",".")), 
                disc3 = parseFloat($('#rowDisc3' + id).val().replaceAll(".", "").replaceAll(",",".")),
                conversion = $('#arr_item' + id).val() ? parseFloat($('#arr_unit' + id).find(':selected').data('conversion')) : 1;

            if(qtylimit > 0){
                if(qty > qtylimit){
                    qty = qtylimit;
                    $('#rowQty' + id).val(formatRupiahIni(qty.toFixed(3).toString().replace('.',',')));
                }
            }

            let qtyConversion = qty * conversion;

            $('#qty_stock' + id).text(formatRupiahIni(qtyConversion.toFixed(3).toString().replace('.',',')));

            var finalpricedisc1 = price - (price * (disc1 / 100));
            var finalpricedisc2 = finalpricedisc1 - (finalpricedisc1 * (disc2 / 100));
            var finalpricedisc3 = finalpricedisc2 - disc3;

            if((finalpricedisc3 * qty).toFixed(2) >= 0){
                $('#arr_subtotal' + id).text(formatRupiahIni((finalpricedisc3 * qty).toFixed(2).toString().replace('.',',')));
            }else{
                $('#arr_subtotal' + id).text('-' + formatRupiahIni((finalpricedisc3 * qty).toFixed(2).toString().replace('.',',')));
            }

            countAll();
        }
    }

    function countAll(){
        var subtotal = 0, tax = 0, discount = parseFloat($('#discount').val().replaceAll(".", "").replaceAll(",",".")), total = 0, grandtotal = 0, wtax = 0, currency_rate = parseFloat($('#currency_rate').val().replaceAll(".", "").replaceAll(",",".")), subtotalconvert = 0, taxconvert = 0, discountconvert = 0, totalconvert = 0, grandtotalconvert = 0, wtaxconvert = 0, rounding = parseFloat($('#rounding').val().replaceAll(".", "").replaceAll(",",".")), roundingconvert = 0;

        discountconvert = discount * currency_rate;

        $('.arr_subtotal').each(function(index){
			subtotal += parseFloat($(this).text().replaceAll(".", "").replaceAll(",","."));
		});

        $('.arr_subtotal').each(function(index){
            let rownominal = parseFloat($(this).text().replaceAll(".", "").replaceAll(",",".")), rowtax = 0, rowwtax = 0, rowbobot = 0, rowdiscount = 0, rowgrandtotal = 0;
            rowbobot = Math.round(((rownominal / subtotal) + Number.EPSILON) * 10000) / 10000;
            rowdiscount = discount * rowbobot;
            rownominal -= rowdiscount;

            if($('select[name^="arr_tax"]').eq(index).val() !== '0'){
                let percent_tax = parseFloat($('select[name^="arr_tax"]').eq(index).val());
                if($('input[name^="arr_is_include_tax"]').eq(index).is(':checked')){
                    rownominal = rownominal / (1 + (percent_tax / 100));
                }
                rownominal = Math.round(rownominal * 100) / 100;
                rowtax = Math.floor(rownominal * (percent_tax / 100));
            }

            rownominal = Math.round(rownominal * 100) / 100;

            if($('select[name^="arr_wtax"]').eq(index).val() !== '0'){
                let percent_wtax = parseFloat($('select[name^="arr_wtax"]').eq(index).val());
                rowwtax = Math.floor(rownominal * (percent_wtax / 100));
            }

            $('input[name^="arr_nominal_total"]').eq(index).val(
                (rownominal >= 0 ? '' : '-') + formatRupiahIni(rownominal.toString().replace('.',','))
            );

            $('input[name^="arr_nominal_tax"]').eq(index).val(
                (rowtax >= 0 ? '' : '-') + formatRupiahIni(rowtax.toString().replace('.',','))
            );

            $('input[name^="arr_nominal_wtax"]').eq(index).val(
                (rowwtax >= 0 ? '' : '-') + formatRupiahIni(rowwtax.toString().replace('.',','))
            );

            rowgrandtotal = (rownominal + rowtax - rowwtax).toFixed(2);

            $('input[name^="arr_nominal_grandtotal"]').eq(index).val(
                (rowgrandtotal >= 0 ? '' : '-') + formatRupiahIni(rowgrandtotal.toString().replace('.',','))
            );
            
            tax += rowtax;
            wtax += rowwtax;
            total += rownominal;
            
        });

        tax = Math.floor(tax);
        wtax = Math.floor(wtax);

        grandtotal = total + tax - wtax + rounding;

        subtotalconvert = subtotal * currency_rate;
        totalconvert = total * currency_rate;
        taxconvert = tax * currency_rate;
        wtaxconvert = wtax * currency_rate;
        grandtotalconvert = grandtotal * currency_rate;
        roundingconvert = rounding * currency_rate;

        $('#subtotal').text(
            (subtotal >= 0 ? '' : '-') + formatRupiahIni(subtotal.toFixed(2).toString().replace('.',','))
        );
        $('#savesubtotal').val(
            (subtotal >= 0 ? '' : '-') + formatRupiahIni(subtotal.toFixed(2).toString().replace('.',','))
        );
        $('#subtotal-convert').text(
            (subtotalconvert >= 0 ? '' : '-') + formatRupiahIni(subtotalconvert.toFixed(2).toString().replace('.',','))
        );
        $('#discount-convert').text(
            (discountconvert >= 0 ? '' : '-') + formatRupiahIni(discountconvert.toFixed(2).toString().replace('.',','))
        );
        $('#total').text(
            (total >= 0 ? '' : '-') + formatRupiahIni(total.toFixed(2).toString().replace('.',','))
        );
        $('#savetotal').val(
            (total >= 0 ? '' : '-') + formatRupiahIni(total.toFixed(2).toString().replace('.',','))
        );
        $('#total-convert').text(
            (totalconvert >= 0 ? '' : '-') + formatRupiahIni(totalconvert.toFixed(2).toString().replace('.',','))
        );
        $('#tax').text(
            (tax >= 0 ? '' : '-') + formatRupiahIni(tax.toFixed(2).toString().replace('.',','))
        );
        $('#savetax').val(
            (tax >= 0 ? '' : '-') + formatRupiahIni(tax.toFixed(2).toString().replace('.',','))
        );
        $('#tax-convert').text(
            (taxconvert >= 0 ? '' : '-') + formatRupiahIni(taxconvert.toFixed(2).toString().replace('.',','))
        );
        $('#wtax').val(
            (wtax >= 0 ? '' : '-') + formatRupiahIni(wtax.toFixed(2).toString().replace('.',','))
        );
        $('#savewtax').val(
            (wtax >= 0 ? '' : '-') + formatRupiahIni(wtax.toFixed(2).toString().replace('.',','))
        );
        $('#wtax-convert').text(
            (wtaxconvert >= 0 ? '' : '-') + formatRupiahIni(wtaxconvert.toFixed(2).toString().replace('.',','))
        );
        $('#rounding-convert').text(
            (roundingconvert >= 0 ? '' : '-') + formatRupiahIni(roundingconvert.toFixed(2).toString().replace('.',','))
        );
        $('#grandtotal').text(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(grandtotal.toFixed(2).toString().replace('.',','))
        );
        $('#savegrandtotal').val(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(grandtotal.toFixed(2).toString().replace('.',','))
        );
        $('#grandtotal-convert').text(
            (grandtotalconvert >= 0 ? '' : '-') + formatRupiahIni(grandtotalconvert.toFixed(2).toString().replace('.',','))
        );
    }

    function countGrandtotal(val){
        let total = parseFloat($('#savetotal').val().replaceAll(".", "").replaceAll(",",".")), tax = parseFloat($('#savetax').val().replaceAll(".", "").replaceAll(",",".")), wtax = parseFloat(val.replaceAll(".", "").replaceAll(",","."));
        $('#savewtax').val(val);
        let grandtotal = total + tax - wtax;
        $('#grandtotal').text(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(grandtotal.toFixed(2).toString().replace('.',','))
        );
        $('#savegrandtotal').val(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(grandtotal.toFixed(2).toString().replace('.',','))
        );
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

    function startIntro(){
        introJs().setOptions({
            exitOnOverlayClick : false,
            steps: [
                {
                    title : 'Purchase Order',
                    intro : 'Form ini digunakan untuk menambahkan pembelian barang dan jasa berdasarkan Purchase Request ataupun Good Issue / Barang Keluar. Silahkan ikuti panduan ini untuk penjelasan mengenai isian form.'
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
                    title : 'Supplier',
                    element : document.querySelector('.step3'),
                    intro : 'Supplier adalah Partner Bisnis tipe penyedia barang / jasa. Jika ingin menambahkan data baru, silahkan ke form Master Data - Organisasi - Partner Bisnis.' 
                },
                {
                    title : 'Tipe Pembelian',
                    element : document.querySelector('.step4'),
                    intro : 'Tipe Pembelian berisi barang atau jasa, silahkan pilih barang jika pembelian adalah untuk barang yang ada wujudnya, dan pilih jasa, jika tipe pembelian adalah jasa. Hati-hati karena tipe pembelian barang maka, detail produk di tabel akan otomatis mengambil data dari master data item, sedangkan tipe pembelian jasa akan otomatis ke biaya / COA.' 
                },
              
                {
                    title : 'Tipe Pengiriman',
                    element : document.querySelector('.step6'),
                    intro : 'Franco, merupakan kegiatan jual beli barang di mana biaya pengiriman ditanggung oleh penjual. Sedangkan dalam Loco pembeli yang mendatangi gudang barang dan menanggung semua biaya.' 
                },
                {
                    title : 'Perusahaan',
                    element : document.querySelector('.step7'),
                    intro : 'Perusahaan dimana dokumen ini dibuat.' 
                },
                {
                    title : 'No. Dokumen',
                    element : document.querySelector('.step8'),
                    intro : 'No dokumen bisa diisikan dengan no dokumen penawaran dari supplier.' 
                },
                {
                    title : 'Tipe Pembayaran',
                    element : document.querySelector('.step9'),
                    intro : 'Tipe pembayaran PO, silahkan pilih sesuai keadaan.' 
                },
                {
                    title : 'Termin Pembayaran',
                    element : document.querySelector('.step10'),
                    intro : 'Berapa hari termin pembayaran sejak dokumen diterima. Otomatis terisi, ketika anda memilih supplier dan tipe pembayaran Credit.' 
                },
                {
                    title : 'Tgl. Terima',
                    element : document.querySelector('.stepreceive'),
                    intro : 'Tanggal untuk menentukan tanggal terima dari order.' 
                },
                {
                    title : 'Mata Uang',
                    element : document.querySelector('.step11'),
                    intro : 'Mata uang, silahkan pilih mata uang lain, untuk mata uang asing.' 
                },
                {
                    title : 'Konversi',
                    element : document.querySelector('.step12'),
                    intro : 'Nilai konversi rupiah pada saat Purchase Order dibuat. Nilai konversi secara otomatis diisi ketika form tambah baru dibuka pertama kali dan data diambil dari situs exchangerate.host. Pastikan kode mata uang benar di master data agar nilai konversi tidak error.'
                },
                {
                    title : 'Tgl. Posting',
                    element : document.querySelector('.step13'),
                    intro : 'Tanggal post akan menentukan tanggal jurnal untuk beberapa form yang terhubung dengan jurnal. Hati - hati dalam menentukan tanggal posting.' 
                },
                {
                    title : 'Tgl. Kirim',
                    element : document.querySelector('.step14'),
                    intro : 'Tanggal perkiraan kirim barang dari Supplier.' 
                },
                {
                    title : 'Nama Penerima',
                    element : document.querySelector('.step15'),
                    intro : 'Nama penerima di gudang atau atas nama pembeli.' 
                },
                {
                    title : 'Alamat Penerima',
                    element : document.querySelector('.step16'),
                    intro : 'Bisa diisikan dengan alamat gudang atau drop point barang.' 
                },
                {
                    title : 'Kontak Penerima',
                    element : document.querySelector('.step17'),
                    intro : 'Nomor telepon atau hp dari yang menerima barang di gudang, atau drop point barang.' 
                },
                {
                    title : 'File Lampiran',
                    element : document.querySelector('.step18'),
                    intro : 'Silahkan unggah file lampiran. untuk saat ini hanya bisa mengakomodir 1 file lampiran saja. Jika ingin menambahkan file lebih dari 1, silahkan gabungkan file anda menjadi pdf.' 
                },
                {
                    title : 'Purchase Request',
                    element : document.querySelector('.step19'),
                    intro : 'Pilih ini jika ingin menarik data dari Purchase Request aktif yang masih memiliki tunggakan.' 
                },
                {
                    title : 'Good Issue / Barang Keluar',
                    element : document.querySelector('.step20'),
                    intro : 'Pilih ini jika ingin menarik data dari Good Issue / Barang Keluar aktif yang ingin menerbitkan barang baru dengan status REPAIR. Gunakan untuk produk yang keluar karena diperbaiki.' 
                },
                {
                    title : 'Surat Jalan',
                    element : document.querySelector('.stepsj'),
                    intro : 'Pilih ini jika ingin menarik data dari Marketing Order Delivery Process / Surat Jalan aktif .' 
                },
                {
                    title : 'PR/GI Terpakai',
                    element : document.querySelector('.step21'),
                    intro : 'Daftar dokumen referensi yang terpakai akan muncul disini jika anda menggunakan Purchase Request ataupun Good Issue. Anda bisa menghapus dengan cara menekan tombol x pada masing-masing tombol. Fungsi lain dari fitur ini adalah, agar PR/GI tidak bisa dipakai di form selain form aktif saat ini.' 
                },
                {
                    title : 'Detail produk',
                    element : document.querySelector('.step22'),
                    intro : 'Silahkan tambahkan produk anda disini, lengkap dengan keterangan detail tentang produk tersebut. Hati-hati dalam menentukan Plant, dan Gudang Tujuan, karena itu nantinya akan menentukan dimana barang ketika diterima.' 
                },
                {
                    title : 'Keterangan',
                    element : document.querySelector('.step23'),
                    intro : 'Silahkan isi / tambahkan keterangan untuk dokumen ini untuk dimunculkan di bagian bawah tabel detail produk nantinya, ketika dicetak.' 
                },
                {
                    title : 'Keterangan Eksternal',
                    element : document.querySelector('.step24'),
                    intro : 'Keterangan tambahan yang hanya muncul pada saat dokumen dicetak.' 
                },
                {
                    title : 'Tabel Informasi Total Transaksi',
                    element : document.querySelector('.step25'),
                    intro : 'Nominal diskon, untuk diskon yang ingin dimunculkan di dalam dokumen ketika dicetak. Diskon ini mengurangi subtotal. Nominal PPh bisa disesuaikan dengan kebutuhan.' 
                },
                {
                    title : 'Tombol Simpan',
                    element : document.querySelector('.step26'),
                    intro : 'Silahkan tekan tombol ini untuk menyimpan data, namun pastikan data yang akan anda masukkan benar.' 
                },
            ]
        })/* .onbeforechange(function(targetElement){
            alert(this._currentStep);
        }) */.start();
    }

    function done(id){
        $.ajax({
            url: '{{ Request::url() }}/get_items',
            type: 'POST',
            dataType: 'JSON',
            data: {
                id: id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            success: function(response) {
                loadingClose('.modal-content');
                $('#modal6').modal('open');
                $('#tempDone').val(id);
                
                if(response.details.length > 0){
                    $('#body-done').html('');

                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        $('#body-done').append(`
                            <tr class="row_done">
                                <input type="hidden" name="arr_id[]" id="arr_id` + count + `" value="` + val.id + `">
                                <td class="center">
                                    <label>
                                        <input type="checkbox" id="arr_value` + count + `" name="arr_value[]" value="1" ` + (val.closed ? 'checked' : '') + `>
                                        <span>Tutup</span>
                                    </label>
                                </td>
                                <td>
                                    ` + val.item_name + `
                                </td>
                                <td class="center">
                                    <span id="arr_satuan` + count + `">` + val.unit + `</span>
                                </td>
                                <td class="right-align">
                                    ` + val.qty + `
                                </td>
                                <td class="right-align">
                                    ` + val.qty_gr + `
                                </td>
                                <td class="right-align">
                                    ` + val.qty_balance + `
                                </td>
                            </tr>
                        `);
                    });
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
    }

    function saveDone(){
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
                
                var formData = new FormData($('#form_done')[0]);

                formData.delete("arr_value[]");

                $('input[name^="arr_id[]"]').each(function(index){
                    formData.append('arr_value[]',($('input[name^="arr_value[]"]').eq(index).is(':checked') ? $('input[name^="arr_value[]"]').eq(index).val() : ''));
                });

                $.ajax({
                    url: '{{ Request::url() }}/create_done',
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
                        $('#validation_alert_done').hide();
                        $('#validation_alert_done').html('');
                        loadingOpen('.modal-content');
                    },
                    success: function(response) {
                        loadingClose('.modal-content');
                        if(response.status == 200) {
                            loadDataTable();
                            $('#modal6').modal('close');
                            M.toast({
                                html: response.message
                            });
                        } else if(response.status == 422) {
                            $('#validation_alert_done').show();
                            $('.modal-content').scrollTop(0);
                            
                            swal({
                                title: 'Ups! Validation',
                                text: 'Check your form.',
                                icon: 'warning'
                            });

                            $.each(response.error, function(i, val) {
                                $.each(val, function(i, val) {
                                    $('#validation_alert_done').append(`
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

    function whatPrintingChi(code){
        $.ajax({
            url: '{{ Request::url() }}/print_individual_chi/' + code,
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
        var status = $('#filter_status').val();
        var type_buy = $('#filter_inventory').val();
        var type_deliv = $('#filter_shipping').val();
        var company = $('#filter_company').val();
        var type_pay = $('#filter_payment').val();
        var supplier = $('#filter_supplier').val();
        var currency = $('#filter_currency').val();
        var start_date = $('#start_date').val();
        var end_date = $('#finish_date').val();
        var modedata = '{{ $modedata }}';

        window.location = "{{ Request::url() }}/export_from_page?search=" + search + "&status=" + status + "&type_buy=" + type_buy + "&type_deliv=" + type_deliv + "&company=" + company + "&type_pay=" + type_pay + "&supplier=" + supplier + "&currency=" + currency + "&end_date=" + end_date + "&start_date=" + start_date + "&modedata=" + modedata;
       
    }

    function formatRupiahNominal(angka){
        let decimal = 2;
        if($('#currency_id').val() !== '1'){
            decimal = 10;
        }
        let val = angka.value ? angka.value : '';
        var number_string = val.replace(/[^,\d]/g, '').toString(),
        sign = val.charAt(0),
        split   		= number_string.toString().split(','),
        sisa     		= parseFloat(split[0]).toString().length % 3,
        rupiah     		= parseFloat(split[0]).toString().substr(0, sisa),
        ribuan     		= parseFloat(split[0]).toString().substr(sisa).match(/\d{3}/gi);
    
        if(ribuan){
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        if(split[1] != undefined){
            if(split[1].length > decimal){
                rupiah = rupiah + ',' + split[1].slice(0,decimal);
            }else{
                rupiah = rupiah + ',' + split[1];
            }
        }else{
            rupiah = rupiah;
        }
    
        angka.value = sign == '-' ? sign + rupiah : rupiah;
    }
</script>