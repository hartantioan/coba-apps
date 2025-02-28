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
                                                    <p>Info : Input Sampel tidak boleh memiliki kode yang sama.</p>
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
                                                        <th>#</th>
                                                        <th>code</th>
                                                        <th>Tipe Sampel</th>
                                                        <th>Supplier</th>
                                                        <th>CP Supplier</th>
                                                        <th>Provinsi</th>
                                                        <th>Kota</th>
                                                        <th>Kelurahan</th>
                                                        <th>Telepon Supplier</th>
                                                        <th>Tanggal Sampel</th>
                                                        <th>Status</th>
                                                        <th>Link Peta</th>
                                                        <th>Jenis Izin</th>
                                                        <th>Nama Izin</th>
                                                        <th>Perizinan Komoditas</th>
                                                        <th>Periode Izin</th>
                                                        <th>Kapasitas yang Diterima</th>
                                                        <th>Estimasi Harga Loco</th>
                                                        <th>Estimasi Harga Franco</th>
                                                        <th>Kode Sampel Supplier</th>
                                                        <th>Kode Sampel Perusahaan</th>
                                                        <th>Dokumen</th>
                                                        <th>Catatan</th>
                                                        <th>Aksi</th>
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
                <h4> {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="row">
                            <div class="col s12">
                                <fieldset>
                                    <div class="row">
                                        <div class="col s12 m6">
                                            <div class="switch mb-1">
                                                <label for="with_test">Hasil Uji</label>
                                            </div>
                                        </div>
                                        <div class="col s12 m6">
                                        </div>
                                    </div>
                                    <div class="card-alert card red" style="display: none">

                                        <div class="card-content white-text">
                                            <p></p>
                                        </div>
                                    </div>
                                    <legend>1. {{ __('translations.main_info') }}</legend>
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
                                    <div class="input-field col s12 m3">
                                        <select class="select2 browser-default" id="sample_type_id" name="sample_type_id">
                                            <option value="">--{{ __('translations.select') }}--</option>
                                        </select>
                                        <label class="active" for="sample_type_id">Jenis Sampel</label>
                                    </div>
                                    <div class="input-field col m3 s12 step12">
                                        <select class="form-control" id="type" name="type">
                                            <option value="1">QC Lab</option>
                                            <option value="3">QC Packing</option>
                                            <option value="2">Pihak ke-3</option>
                                            <option value="">Belum Diuji</option>
                                        </select>
                                        <label class="active" for="type">Di Uji Oleh:</label>
                                    </div>

                                    <div class="input-field col m5">
                                        <select class="browser-default" id="account_id" name="account_id"></select>
                                        <label class="active" for="account_id" style="font-size:1rem;">PIC SAMPEL:</label>
                                    </div>
                                    <div class="input-field col s12 m3">
                                        <select class="browser-default" id="province_id" name="province_id" onchange="getCity();"></select>
                                        <label class="active" for="province_id">{{ __('translations.province') }}</label>
                                    </div>

                                    <div class="input-field col s12 m12"></div>
                                    <div class="input-field col s12 m3">
                                        <select class="select2 browser-default" id="city_id" name="city_id" onchange="getDistrict();">
                                            <option value="">--{{ __('translations.select') }}--</option>
                                        </select>
                                        <label class="active" for="city_id">{{ __('translations.city') }}</label>
                                    </div>
                                    <div class="input-field col s12 m3">
                                        <select class="select2 browser-default" id="district_id" name="district_id">
                                            <option value="">--{{ __('translations.select') }}--</option>
                                        </select>
                                        <label class="active" for="district_id">{{ __('translations.district') }}</label>
                                    </div>
                                    <div class="input-field col m3 s12 step3">
                                        <input id="village_name" name="village_name" type="text" placeholder="Desa / Kecamatan">
                                        <label class="active" for="village_name">Desa / Kecamatan</label>
                                    </div>
                                    <div class="input-field col m3 s12 step3">
                                        <input id="link_map" name="link_map" type="text" placeholder="Link Map">
                                        <label class="active" for="link_map">Link Map</label>
                                    </div>
                                    <div class="input-field col m3 s12 step3">
                                        <input id="sample_date" name="sample_date" type="date" placeholder="Nama Izin">
                                        <label class="active" for="sample_date">Tanggal Sampel</label>
                                    </div>
                                    <div class="input-field col m3 s12 step3">
                                        <input type="hidden" id="temp" name="temp">
                                        <input id="supplier" name="supplier" type="text" placeholder="Supplier">
                                        <label class="active" for="supplier"> Supplier</label>
                                    </div>
                                    <div class="input-field col m3 s12 step3">

                                        <input id="supplier_name" name="supplier_name" type="text" placeholder="CP Supplier">
                                        <label class="active" for="supplier_name">CP Supplier</label>
                                    </div>
                                    <div class="input-field col m3 s12 step3">
                                        <input id="supplier_phone" name="supplier_phone" type="text" placeholder="Telp CP Supplier">
                                        <label class="active" for="supplier_phone">Telp CP Supplier</label>
                                    </div>
                                    <div class="input-field col m3 s12 step3">
                                        <input id="permission_type" name="permission_type" type="text" placeholder="Jenis Izin">
                                        <label class="active" for="permission_type">Jenis Izin</label>
                                    </div>
                                    <div class="input-field col m3 s12 step3">
                                        <input id="permission_name" name="permission_name" type="text" placeholder="Nama Izin">
                                        <label class="active" for="permission_name">Nama Izin</label>
                                    </div>
                                    <div class="input-field col m3 s12 step3">
                                        <input id="commodity_permits" name="commodity_permits" type="text" placeholder="Komoditas Izin">
                                        <label class="active" for="commodity_permits">Komoditas Izin</label>
                                    </div>
                                    <div class="input-field col m3 s12 step3">
                                        <input id="permits_period" name="permits_period" type="text" placeholder="Masa Berlaku Izin">
                                        <label class="active" for="permits_period">Masa Berlaku Izin</label>
                                    </div>
                                    <div class="input-field col m3 s12 step26">
                                        <input id="receiveable_capacity" name="receiveable_capacity" type="text" value="0" onkeyup="formatRupiah(this)">
                                        <label class="active" for="receiveable_capacity">Kapasitas yang bisa didapatkan(perbulan)</label>
                                    </div>
                                    <div class="input-field col m3 s12 step26">
                                        <input id="price_estimation_loco" name="price_estimation_loco" type="text" value="0" onkeyup="formatRupiah(this)">
                                        <label class="active" for="price_estimation_loco">Estimasi Harga Loco</label>
                                    </div><div class="input-field col m3 s12 step26">
                                        <input id="price_estimation_franco" name="price_estimation_franco" type="text" value="0" onkeyup="formatRupiah(this)">
                                        <label class="active" for="price_estimation_franco">Estimasi Harga Franco</label>
                                    </div>
                                    <div class="input-field col m3 s12 step3">
                                        <input id="supplier_sample_code" name="supplier_sample_code" type="text" placeholder="Kode dari Supplier">
                                        <label class="active" for="supplier_sample_code">Kode dari Supplier</label>
                                    </div>
                                    <div class="input-field col m3 s12 step3">
                                        <input id="company_sample_code" name="company_sample_code" type="text" placeholder="Kode dari Perusahaan">
                                        <label class="active" for="company_sample_code">Kode Sampel ( No Duplicate )</label>
                                    </div>
                                    <div class="file-field input-field col m12 s12 step18">
                                        <div class="btn">
                                            <span>Bukti Upload</span>
                                            <input type="file" name="file[]" id="file" multiple accept=".pdf, .xlsx, .xls, .jpeg, .jpg, .png, .gif, .word">
                                        </div>
                                        <div class="file-path-wrapper">
                                            <input class="file-path validate" type="text">
                                        </div>
                                    </div>
                                    <div class="input-field col m3 s12 step3">

                                        <input id="note" name="note" type="text" placeholder="Catatan Sampel">
                                        <label class="active" for="note"> Catatan Sampel</label>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light mr-1" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
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

    $(document).ready(function() {
        toggleLabNameField($('#lab_type').val());

        $('#lab_type').change(function() {
            toggleLabNameField($(this).val());
        });

        function toggleLabNameField(value) {
            if (value == "2") {
                $('#lab_name_field').show();
            } else {
                $('#lab_name_field').hide();
            }
        }
    });
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

        select2ServerSide('#account_id', '{{ url("admin/select2/employee") }}');
        $('#with_test').change(function() {
            if ($(this).is(':checked')) {
                $('#modal-fieldset').show();
            } else {
                $('#modal-fieldset').hide();
            }
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
            },
            onCloseEnd: function(modal, trigger){
                $('input').css('border', 'none');
                $('input').css('border-bottom', '0.5px solid black');
                $('#form_data')[0].reset();
                $('input').css('border', 'none');
                $('input').css('border-bottom', '0.5px solid black');
                $('#temp').val('');
                $('#supplier_id').empty();
                $('#province_id,#district_id,#city_id').empty().append(`
                    <option value="">--{{ __('translations.select') }}--</option>
                `);
                $('#account_id').empty();
                $('#sample_type_id').empty();
                $('#province_id').empty();
                $('#city_id').empty();
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

        select2ServerSide('#province_id', '{{ url("admin/select2/province") }}');
        select2ServerSide('#sample_type_id', '{{ url("admin/select2/sample_type") }}');

        $("#table-detail th").resizable({
            minWidth: 100,
        });
    });



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
                { name: 'sample_type', className: 'center-align' },
                { name: 'sample_type', className: 'center-align' },
                { name: 'supplier', className: '' },
                { name: 'supplier_name', className: '' },
                { name: 'province_name', className: '' },
                { name: 'city_name', className: '' },
                { name: 'subdistrict_name', className: '' },
                { name: 'supplier_phone', className: '' },
                { name: 'sample_date', className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'link_map', className: '' },
                { name: 'permission_type', className: '' },
                { name: 'permission_name', className: '' },
                { name: 'commodity_permits', className: '' },
                { name: 'permits_period', className: '' },
                { name: 'receiveable_capacity', className: 'right-align' },
                { name: 'price_estimation', className: 'right-align' },
                { name: 'supplier_sample_code', className: '' },
                { name: 'company_sample_code', className: '' },
                { name: 'document', className: '' },
                { name: 'document', className: '' },
                { name: 'note', className: '' },
                { name: 'action', searchable: false, orderable: false, className: 'right-align' }
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



                    var formData = new FormData($('#form_data')[0]);

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
                $('#sample_type_id').empty();
                $('#sample_type_id').append(`
                    <option value="` + response.sample_type_id + `">` + response.sample_type_name + `</option>
                `);
                $('#account_id').empty();
                $('#account_id').append(`
                    <option value="` + response.account_id + `">` + response.customer + `</option>
                `);

                $('#province_id').empty();
                $('#province_id').append(`
                    <option value="` + response.province_id + `">` + response.province_name + `</option>
                `);

                $('#city_id').empty();
                $('#city_id').append(`
                    <option value="` + response.city_id + `">` + response.city_name + `</option>
                `);

                $('#district_id').empty();
                $('#district_id').append(`
                    <option value="` + response.subdistrict_id + `">` + response.subdistrict_name + `</option>
                `);
                $('#link_map').val(response.link_map);
                $('#supplier_phone').val(response.supplier_phone);
                $('#village_name').val(response.village_name);
                $('#sample_date').val(response.sample_date);
                $('#supplier').val(response.supplier);
                $('#supplier_name').val(response.supplier_name);
                $('#permission_type').val(response.permission_type);
                $('#permission_name').val(response.permission_name);
                $('#commodity_permits').val(response.commodity_permits);
                $('#permits_period').val(response.permits_period);
                $('#price_estimation').val(response.price_estimation);
                $('#receiveable_capacity').val(response.receiveable_capacity);
                $('#supplier_sample_code').val(response.supplier_sample_code);
                $('#company_sample_code').val(response.company_sample_code);
                $('#type').val(response.type).formSelect();
                $('#note').val(response.note);

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

    function getCity(){
        $('#city_id,#district_id').empty().append(`
            <option value="">--{{ __('translations.select') }}--</option>
        `);
        if($('#province_id').val()){
            city = $('#province_id').select2('data')[0].cities;
            $.each(city, function(i, val) {
                $('#city_id').append(`
                    <option value="` + val.id + `">` + val.name + `</option>
                `);
            });
        }else{
            city = [];
            district = [];
        }
    }

    function getDistrict(){
        $('#district_id').empty().append(`
            <option value="">--{{ __('translations.select') }}--</option>
        `);
        if($('#city_id').val()){
            let index = -1;
            $.each(city, function(i, val) {
                if(val.id == $('#city_id').val()){
                    index = i;
                }
            });

            $.each(city[index].districts, function(i, value) {
                $('#district_id').append(`
                    <option value="` + value.id + `">` + value.name + `</option>
                `);
            });
        }else{
            district = [];
        }
    }



    function exportExcel(){
        var search = table.search();
        var status = $('#filter_status').val();
        var start_date = $('#start_date').val();
        var end_date = $('#finish_date').val();

        window.location = "{{ Request::url() }}/export_from_page?search=" + search + "&status=" + status +"&end_date=" + end_date + "&start_date=" + start_date;

    }

    function formatRupiahNominal(angka){
        let decimal = 2;
        if($('#currency_id').val() !== '1'){
            decimal = 11;
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
