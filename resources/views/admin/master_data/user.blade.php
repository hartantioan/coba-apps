<script src="{{ url('app-assets/js/dropzone.min.js') }}"></script>
<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
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

    table.bordered th {
        padding: 5px !important;
    }

    .select-wrapper {
        height: 3.6rem !important;
    }

    .select2-container {
        height: 3.6rem !important;
    }

    #no_position{
        background-color: red;
        animation-name: blinking;
        animation-duration: 1s;
        animation-iteration-count: 100;
        padding: 5px;
        color: black;
        border-radius: 5px;
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
                            <li class="breadcrumb-item"><a href="#">{{ Str::title(str_replace('_',' ',Request::segment(3))) }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{ Str::title(str_replace('_',' ',Request::segment(4))) }}
                            </li>
                        </ol>
                    </div>
                    <div class="col s4 m6 l6">
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="print();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">Print</span>
                            <i class="material-icons right">local_printshop</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="exportExcel();">
                            <i class="material-icons hide-on-med-and-up">view_list</i>
                            <span class="hide-on-small-onl">Excel</span>
                            <i class="material-icons right">view_list</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3 modal-trigger" href="#modal4">
                            <i class="material-icons hide-on-med-and-up">file_download</i>
                            <span class="hide-on-small-onl">Import</span>
                            <i class="material-icons right">file_download</i>
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
                            <div class="card-panel">
                                <div class="row">
                                    <div class="col s12 ">
                                        <label for="filter_status" style="font-size:1.2rem;">Filter Status :</label>
                                        <div class="input-field inline" style="margin-top: 0;margin-bottom: 0;">
                                            <select class="form-control" id="filter_status" onchange="loadDataTable()">
                                                <option value="">Semua</option>
                                                <option value="1">Aktif</option>
                                                <option value="2">Non-Aktif</option>
                                            </select>
                                        </div>

                                        <label for="filter_type" style="font-size:1.2rem;">Filter Tipe :</label>
                                        <div class="input-field inline" style="margin-top: 0;margin-bottom: 0;">
                                            <select id="filter_type" name="filter_type" onchange="loadDataTable()">
                                                <option value="">Semua</option>
                                                <option value="1">Pegawai</option>
                                                <option value="2">Customer</option>
                                                <option value="3">Supplier</option>
                                                <option value="4">Ekspedisi</option>
                                            </select>
                                        </div>

                                        <label for="group_type" style="font-size:1.2rem;">Filter Group :</label>
                                        <div class="input-field inline" style="margin-top: 0;margin-bottom: 0;">
                                            <select id="group_type" multiple="multiple" name="group_type" onchange="loadDataTable()">
                                                @foreach($group as $row)
                                                    <option value="{{ $row['id'] }}">{{ $row['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">List Data</h4>
                                    <div class="row">
                                        <div class="col s12">
                                            <div id="datatable_buttons"></div>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">Refresh</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <table id="datatable_serverside" >
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Nama</th>
                                                        <th>Username</th>
                                                        <th>NIK/Code</th>
                                                        <th>Tipe</th>
                                                        <th>Grup</th>
                                                        <th>Posisi</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
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
    <div class="modal-content" style="overflow-x: hidden !important;">
        <div class="row">
            <div class="col s12">
                <h4>Tambah/Edit {{ $title }}</h4>
                <div class="card-alert card blue">
                    <div class="card-content white-text">
                        <p>Info : Untuk penambahan BP Supplier & Ekspedisi dibuka akses hanya pak Sandi.</p>
                    </div>
                </div>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">

                        <div class="input-field col s12 m3">
                            <select id="type" name="type" onchange="changeMode(this);refreshGroup();">
                                <option value="1">Pegawai</option>
                                <option value="2">Customer</option>
                                <option value="3">Supplier</option>
                                <option value="4">Ekspedisi</option>
                            </select>
                            <label for="type">Tipe Partner Bisnis</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <input type="hidden" id="temp" name="temp">
                            <input id="name" name="name" type="text" placeholder="Nama">
                            <label class="active" for="name">Nama</label>
                        </div>
                        <div class="input-field col s12 m3 employee_inputs">
                            <input id="username" name="username" type="text" placeholder="Username">
                            <label class="active" for="username">Username</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <input id="employee_no" name="employee_no" type="text" placeholder="Kode BP...">
                            <label class="active" for="employee_no">Kode/NIK (Kosongkan untuk autogenerate)</label>
                        </div>
                        <div class="col s12"></div>
                        <div class="input-field col s12 m3 employee_inputs">
                            <input id="password" name="password" type="password" placeholder="Password">
                            <label class="active" for="password">Password</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <input id="phone" name="phone" type="text" placeholder="Phone">
                            <label class="active" for="phone">Telepon</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <input id="email" name="email" type="text" placeholder="Email">
                            <label class="active" for="email">Email</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <input id="address" name="address" type="text" placeholder="Alamat">
                            <label class="active" for="address">Alamat</label>
                        </div>
                        <div class="col s12"></div>
                        <div class="input-field col s12 m3">
                            <input id="id_card" name="id_card" type="text" placeholder="No KTP" class="ktp">
                            <label class="active" for="id_card">No KTP / Identitas</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <input id="id_card_address" name="id_card_address" type="text" placeholder="Alamat KTP">
                            <label class="active" for="id_card_address">Alamat KTP / Identitas</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <input id="tax_id" name="tax_id" type="text" placeholder="No. NPWP" class="npwp">
                            <label class="active" for="tax_id">No. NPWP</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <input id="tax_name" name="tax_name" type="text" placeholder="Nama di NPWP">
                            <label class="active" for="tax_name">Nama NPWP</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <input id="tax_address" name="tax_address" type="text" placeholder="Alamat di NPWP">
                            <label class="active" for="tax_address">Alamat NPWP</label>
                        </div>
                        <div class="input-field col s12 m6 step5" id="manager_select">
                            
                        </div>
                        <div class="col s12"></div>
                        <div class="input-field col s12 m3 employee_inputs">
                            <select id="married_status" name="married_status">
                                <option value="1">Single</option>
                                <option value="2">Menikah</option>
                                <option value="3">Cerai</option>
                            </select>
                            <label for="married_status">Status Pernikahan</label>
                        </div>
                        <div class="input-field col s12 m3 employee_inputs">
                            <input id="married_date" name="married_date" type="date">
                            <label class="active" for="married_date">Tgl.Pernikahan</label>
                        </div>
                        <div class="input-field col s12 m3 employee_inputs">
                            <input id="children" name="children" type="number" value="0">
                            <label class="active" for="children">Jumlah Anak</label>
                        </div>
                        <div class="input-field col s12 m3 employee_inputs" id="company_select">
                            <select id="company_id" name="company_id">
                                @foreach($company as $row)
                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                @endforeach
                            </select>
                            <label for="company_id">Perusahaan</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <select id="gender" name="gender">
                                <option value="1">Laki-laki</option>
                                <option value="2">Wanita</option>
                                <option value="3">Lainnya</option>
                            </select>
                            <label for="gender">Jenis Kelamin</label>
                        </div>
                        <div class="input-field col s12 m3 other_inputs" style="display:none;">
                            <input id="pic" name="pic" type="text" placeholder="PIC">
                            <label class="active" for="pic">PIC</label>
                        </div>
                        <div class="input-field col s12 m3 other_inputs" style="display:none;">
                            <input id="pic_no" name="pic_no" type="text" placeholder="Kontak PIC">
                            <label class="active" for="pic_no">Kontak PIC</label>
                        </div>
                        <div class="input-field col s12 m3 other_inputs" style="display:none;">
                            <input id="office_no" name="office_no" type="text" placeholder="Kontak Kantor">
                            <label class="active" for="office_no">Kontak Kantor</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <input id="limit_credit" name="limit_credit" type="text" value="0" placeholder="Limit Kredit" onkeyup="formatRupiah(this)">
                            <label class="active" for="limit_credit">Limit Kredit</label>
                        </div>
                        <div class="input-field col s12 m3 other_inputs" style="display:none;">
                            <input id="top" name="top" type="number" min="0" step="1" value="0">
                            <label class="active" for="top">TOP (Tempo Pembayaran)</label>
                        </div>
                        <div class="input-field col s12 m3 other_inputs" style="display:none;">
                            <input id="top_internal" name="top_internal" type="number" min="0" step="1" value="0">
                            <label class="active" for="top_internal">TOP Internal</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <select class="browser-default" id="province_id" name="province_id" onchange="getCity();"></select>
                            <label class="active" for="province_id">Provinsi</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <select class="select2 browser-default" id="city_id" name="city_id" onchange="getDistrict();">
                                <option value="">--Pilih ya--</option>
                            </select>
                            <label class="active" for="city_id">Kota/Kabupaten</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <select class="select2 browser-default" id="district_id" name="district_id" onchange="getSubdistrict();">
                                <option value="">--Pilih ya--</option>
                            </select>
                            <label class="active" for="district_id">Kecamatan</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <select class="select2 browser-default" id="subdistrict_id" name="subdistrict_id">
                                <option value="">--Pilih ya--</option>
                            </select>
                            <label class="active" for="subdistrict_id">Kelurahan</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <select class="browser-default" id="country_id" name="country_id"></select>
                            <label class="active" for="country_id">Negara Asal</label>
                        </div>
                        <div class="input-field col s12 m3" id="group_select">
                            <select id="group_id" name="group_id"></select>
                            <label for="group_id">Kelompok Partner Bisnis</label>
                        </div>
                        <div class="input-field col s12 m3 employee_inputs">
                            <select id="employee_type" name="employee_type">
                                <option value="1">Staff</option>
                                <option value="2">Non-Staff</option>
                            </select>
                            <label for="employee_type">Tipe Pegawai</label>
                        </div>
                        <div class="input-field col s12 m3 employee_inputs">
                            <select class="form-control" id="place_id" name="place_id">
                                @foreach ($place as $rowplace)
                                    <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                                @endforeach
                            </select>
                            <label class="" for="place_id">Plant (Untuk nomor pegawai)</label>
                        </div>
                        <div class="col s12 mt-1">
                            <div class="input-field col s12 m3 customer_inputs" style="display:none;">
                                <div class="switch mb-1">
                                    <label for="is_ar_invoice">Auto Generate SJ -> AR Invoice</label>
                                    <label>
                                        Tidak
                                        <input checked type="checkbox" id="is_ar_invoice" name="is_ar_invoice" value="1">
                                        <span class="lever"></span>
                                        Ya
                                    </label>
                                </div>
                            </div>
                            <div class="input-field col s12 m3 employee_inputs">
                                <div class="switch mb-1">
                                    <label for="is_special_lock_user">Spesial (Kunci Periode)</label>
                                    <label>
                                        Tidak
                                        <input type="checkbox" id="is_special_lock_user" name="is_special_lock_user" value="1">
                                        <span class="lever"></span>
                                        Ya
                                    </label>
                                </div>
                            </div>
                            <div class="input-field col s12 m3">
                                <div class="switch mb-1">
                                    <label for="status">Status</label>
                                    <label>
                                        Non-Active
                                        <input checked type="checkbox" id="status" name="status" value="1">
                                        <span class="lever"></span>
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col s12 mt-3">
                            <ul class="tabs">
                                <li class="tab col m4"><a class="active" href="#rekform">Rekening</a></li>
                                <li class="tab col m4"><a href="#dataform">Alamat Penagihan</a></li>
                                <li class="tab col m4 other_inputs" style="display:none;""><a href="#driverform">Daftar Supir</a></li>
                            </ul>
                            <div id="rekform" class="col s12 active">
                                <h5 class="center">Daftar Rekening</h5>
                                <p class="mt-2 mb-2">
                                    <table class="bordered">
                                        <thead>
                                            <tr>
                                                <th width="30%" class="center">Bank</th>
                                                <th width="20%" class="center">Atas Nama</th>
                                                <th width="20%" class="center">No. Rekening</th>
                                                <th width="20%" class="center">Cabang</th>
                                                <th width="20%" class="center">Utama</th>
                                                <th width="10%" class="center">Hapus</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-bank">
                                            <tr id="last-row-bank">
                                                <td colspan="6" class="center">
                                                    <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addBank()" href="javascript:void(0);">
                                                        <i class="material-icons left">add</i> Tambah Bank
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </p>
                            </div>
                            <div id="dataform" class="col s12" style="overflow:auto;min-width:100%;">
                                <h5 class="center">Daftar Alamat Penagihan</h5>
                                <p class="mt-2 mb-2">
                                    <table class="bordered" style="min-width:100%;">
                                        <thead>
                                            <tr>
                                                <th class="center">Judul</th>
                                                <th class="center">Keterangan</th>
                                                <th class="center">NPWP</th>
                                                <th class="center">Alamat</th>
                                                <th class="center">Negara</th>
                                                <th class="center">Provinsi</th>
                                                <th class="center">Kota</th>
                                                <th class="center">Kecamatan</th>
                                                <th class="center">Kelurahan</th>
                                                <th width="5%" class="center">Hapus</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-info">
                                            <tr id="last-row-info">
                                                <td colspan="10" class="center">
                                                    <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addInfo()" href="javascript:void(0);">
                                                        <i class="material-icons left">add</i> Tambah Alamat
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </p>
                            </div>
                            <div id="driverform" class="col s12">
                                <h5 class="center">Daftar Supir</h5>
                                <p class="mt-2 mb-2">
                                    <table class="bordered">
                                        <thead>
                                            <tr>
                                                <th width="30%" class="center">Nama</th>
                                                <th width="60%" class="center">No HP/WA</th>
                                                <th width="10%" class="center">Hapus</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-driver">
                                            <tr id="last-row-driver">
                                                <td colspan="3" class="center">
                                                    <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addDriver()" href="javascript:void(0);">
                                                        <i class="material-icons left">add</i> Tambah Supir
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </p>
                            </div>
                        </div>
                        <div class="col s12 mt-3">
                            <button class="btn waves-effect waves-light right submit" onclick="save();">Simpan <i class="material-icons right">send</i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal2" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Tambah/Edit Lampiran Pengguna</h4>
                <div class="card-alert card green">
                    <div class="card-content white-text">
                        <p>Maksimal ukuran adalah 1 Mb, dengan jumlah 5.</p>
                    </div>
                </div>
                <form action="{{ Request::url() }}/upload_file" class="dropzone mt-3" id="dropzone_multiple">
                    <input type="hidden" name="tempuser" id="tempuser">
                    @csrf
                </form>
                <div class="row mt-3" id="list-images">
			
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
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
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal4" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;max-width:90%;min-width:70%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>import Excel</h4>
                <div class="col s12">
                    <div id="validation_alertImport" style="display:none;"></div>
                </div>
                <form class="row" action="{{ Request::url() }}/import" method="POST" enctype="multipart/form-data" id="form_dataimport">
                    @csrf
                    <div class="file-field input-field col m6 s12">
                        <div class="btn">
                            <span>File</span>
                            <input type="file" class="form-control-file" id="fileExcel" name="file">
                        </div>
                        <div class="file-path-wrapper">
                            <input class="file-path validate" type="text">
                        </div>
                    </div>
                    <div class="input-field col m6 s12">
                        Download format disini : <a href="{{ asset(Storage::url('format_imports/format_bp.xlsx')) }}" target="_blank">File</a>
                    </div>
                    <div class="input-field col m12 s12">
                        <button type="submit" class="btn cyan btn-primary btn-block right">Kirim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal3" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Tambah/Edit Hak Akses - <span id="tempname"></span></h4>
                <form class="row" id="form_data_access" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert_access" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <input type="hidden" id="tempuseraccess" name="tempuseraccess">
                        <div class="col s12">
                            <ul class="tabs">
                                <li class="tab col m4"><a class="active" href="#accessform">Akses Form/Menu</a></li>
                                <li class="tab col m4"><a href="#accessdata">Akses Data</a></li>
                                <li class="tab col m4"><a href="#copyaccess">Salin Akses ke BP lain</a></li>
                            </ul>
                            <div id="accessform" class="col s12 active">
                                <p class="mt-2 mb-2">
                                    <table class="bordered" id="table-menu-access">
                                        <thead style="position:sticky;top: 40px !important;background-color:rgb(176, 212, 212) !important;">
                                            <tr>
                                                <th width="20%" class="center" rowspan="3">Menu</th>
                                                <th width="80%" class="center" colspan="6">Akses</th>
                                            </tr>
                                            <tr>
                                                <th width="13%" class="center">View</th>
                                                <th width="13%" class="center">Create/Update/Duplicate</th>
                                                <th width="13%" class="center">Delete</th>
                                                <th width="13%" class="center">Void</th>
                                                <th width="13%" class="center">Journal</th>
                                                <th width="13%" class="center">Laporan</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-menu">
                                            @foreach($menu as $m)
                                                @if($m->sub()->exists())
                                                    <tr>
                                                        <td>
                                                            {{ $m->name }}
                                                        </td>
                                                        <td>
                                                            @if (!$m->childHasChild())
                                                                <label>
                                                                    <input type="checkbox" class="checkboxView" onclick="checkAll(this,{{ $m->id }},'view')" data-id="{{ $m->id }}"/>
                                                                    <span>Pilih</span>
                                                                </label>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (!$m->childHasChild())
                                                            <label>
                                                                <input type="checkbox" class="checkboxUpdate" onclick="checkAll(this,{{ $m->id }},'update')"/>
                                                                <span>Pilih</span>
                                                            </label>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (!$m->childHasChild())
                                                            <label>
                                                                <input type="checkbox" class="checkboxDelete" onclick="checkAll(this,{{ $m->id }},'delete')"/>
                                                                <span>Pilih</span>
                                                            </label>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (!$m->childHasChild())
                                                            <label>
                                                                <input type="checkbox" class="checkboxVoid" onclick="checkAll(this,{{ $m->id }},'void')"/>
                                                                <span>Pilih</span>
                                                            </label>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (!$m->childHasChild())
                                                            <label>
                                                                <input type="checkbox" class="checkboxJournal" onclick="checkAll(this,{{ $m->id }},'journal')"/>
                                                                <span>Pilih</span>
                                                            </label>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (!$m->childHasChild())
                                                            <label>
                                                                <input type="checkbox" class="checkboxJournal" onclick="checkAll(this,{{ $m->id }},'report')"/>
                                                                <span>Pilih</span>
                                                            </label>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @foreach($m->sub()->where('status','1')->oldest('order')->get() as $msub)
                                                        @if($msub->sub()->exists())
                                                            <tr>
                                                                <td>
                                                                    {!! '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$msub->name !!}
                                                                </td>
                                                                <td>
                                                                    <label>
                                                                        <input type="checkbox" class="checkboxView" onclick="checkAll(this,{{ $msub->id }},'view')" data-id="{{ $msub->id }}"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                </td>
                                                                <td>
                                                                    <label>
                                                                        <input type="checkbox" class="checkboxUpdate" onclick="checkAll(this,{{ $msub->id }},'update')"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                </td>
                                                                <td>
                                                                    <label>
                                                                        <input type="checkbox" class="checkboxDelete" onclick="checkAll(this,{{ $msub->id }},'delete')"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                </td>
                                                                <td>
                                                                    <label>
                                                                        <input type="checkbox" class="checkboxVoid" onclick="checkAll(this,{{ $msub->id }},'void')"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                </td>
                                                                <td>
                                                                    <label>
                                                                        <input type="checkbox" class="checkboxJournal" onclick="checkAll(this,{{ $msub->id }},'journal')"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                </td>
                                                                <td>
                                                                    <label>
                                                                        <input type="checkbox" class="checkboxReport" onclick="checkAll(this,{{ $msub->id }},'journal')"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                </td>
                                                            </tr>
                                                            @foreach($msub->sub()->where('status','1')->oldest('order')->get() as $msub2)
                                                                @if($msub2->sub()->exists())
    
                                                                @else
                                                                    <tr>
                                                                        <td>
                                                                            {!! '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$msub2->name !!}
                                                                        </td>
                                                                        <td class="center">
                                                                            @if ($msub2->type == '1')
                                                                            <label>
                                                                                <input type="checkbox" name="checkboxView[]" id="checkboxView{{ $msub2->id }}" value="{{ $msub2->id }}" data-parent="{{ $msub2->parentsub->id }}" onclick="showDataView(this);"/>
                                                                                <span>Pilih</span>
                                                                            </label>
                                                                            <div class="switch">
                                                                                <label>
                                                                                    Tidak
                                                                                    <input type="checkbox" name="checkboxViewData[]" id="checkboxViewData{{ $msub2->id }}" value="{{ $msub2->id }}" data-parent="{{ $msub2->parentsub->id }}" disabled>
                                                                                    <span class="lever"></span>
                                                                                    Semua Data
                                                                                </label>
                                                                            </div>
                                                                            @endif
                                                                        </td>
                                                                        <td class="center">
                                                                            <label>
                                                                                <input type="checkbox" name="checkboxUpdate[]" id="checkboxUpdate{{ $msub2->id }}" value="{{ $msub2->id }}" data-parent="{{ $msub2->parentsub->id }}"/>
                                                                                <span>Pilih</span>
                                                                            </label>
                                                                        </td>
                                                                        <td class="center">
                                                                            <label>
                                                                                <input type="checkbox" name="checkboxDelete[]" id="checkboxDelete{{ $msub2->id }}" value="{{ $msub2->id }}" data-parent="{{ $msub2->parentsub->id }}"/>
                                                                                <span>Pilih</span>
                                                                            </label>
                                                                        </td>
                                                                        <td class="center">
                                                                            <label>
                                                                                <input type="checkbox" name="checkboxVoid[]" id="checkboxVoid{{ $msub2->id }}" value="{{ $msub2->id }}" data-parent="{{ $msub2->parentsub->id }}"/>
                                                                                <span>Pilih</span>
                                                                            </label>
                                                                        </td>
                                                                        <td class="center">
                                                                            <label>
                                                                                <input type="checkbox" name="checkboxJournal[]" id="checkboxJournal{{ $msub2->id }}" value="{{ $msub2->id }}" data-parent="{{ $msub2->parentsub->id }}"/>
                                                                                <span>Pilih</span>
                                                                            </label>
                                                                        </td>
                                                                        <td class="center">
                                                                            @if ($msub2->type == '1')
                                                                            <label>
                                                                                <input type="checkbox" name="checkboxReport[]" id="checkboxReport{{ $msub2->id }}" value="{{ $msub2->id }}" data-parent="{{ $msub2->parentsub->id }}" onclick="showDataReport(this);"/>
                                                                                <span>Pilih</span>
                                                                            </label>
                                                                            <div class="switch">
                                                                                <label>
                                                                                    Tidak
                                                                                    <input type="checkbox" name="checkboxReportData[]" id="checkboxReportData{{ $msub2->id }}" value="{{ $msub2->id }}" data-parent="{{ $msub2->parentsub->id }}" disabled>
                                                                                    <span class="lever"></span>
                                                                                    Semua Data
                                                                                </label>
                                                                            </div>
                                                                            <div class="switch">
                                                                                <label>
                                                                                    Tidak
                                                                                    <input type="checkbox" name="checkboxShowNominal[]" id="checkboxShowNominal{{ $msub2->id }}" value="{{ $msub2->id }}" data-parent="{{ $msub2->parentsub->id }}" disabled>
                                                                                    <span class="lever"></span>
                                                                                    Nominal
                                                                                </label>
                                                                            </div>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            <tr>
                                                                <td>
                                                                    {!! '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$msub->name !!}
                                                                </td>
                                                                <td class="center">
                                                                    <label>
                                                                        <input type="checkbox" name="checkboxView[]" id="checkboxView{{ $msub->id }}" value="{{ $msub->id }}" data-parent="{{ $msub->parentsub->id }}" onclick="showDataView(this);"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                    @if ($msub->type == '1')
                                                                    <div class="switch">
                                                                        <label>
                                                                            Tidak
                                                                            <input type="checkbox" name="checkboxViewData[]" id="checkboxViewData{{ $msub->id }}" value="{{ $msub->id }}" data-parent="{{ $msub->parentsub->id }}" disabled>
                                                                            <span class="lever"></span>
                                                                            Semua Data
                                                                        </label>
                                                                    </div>
                                                                    @endif
                                                                </td>
                                                                <td class="center">
                                                                    <label>
                                                                        <input type="checkbox" name="checkboxUpdate[]" id="checkboxUpdate{{ $msub->id }}" value="{{ $msub->id }}" data-parent="{{ $msub->parentsub->id }}"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                </td>
                                                                <td class="center">
                                                                    <label>
                                                                        <input type="checkbox" name="checkboxDelete[]" id="checkboxDelete{{ $msub->id }}" value="{{ $msub->id }}" data-parent="{{ $msub->parentsub->id }}"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                </td>
                                                                <td class="center">
                                                                    <label>
                                                                        <input type="checkbox" name="checkboxVoid[]" id="checkboxVoid{{ $msub->id }}" value="{{ $msub->id }}" data-parent="{{ $msub->parentsub->id }}"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                </td>
                                                                <td class="center">
                                                                    <label>
                                                                        <input type="checkbox" name="checkboxJournal[]" id="checkboxJournal{{ $msub->id }}" value="{{ $msub->id }}" data-parent="{{ $msub->parentsub->id }}"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                </td>
                                                                <td class="center">
                                                                    @if ($msub->type == '1')
                                                                    <label>
                                                                        <input type="checkbox" name="checkboxReport[]" id="checkboxReport{{ $msub->id }}" value="{{ $msub->id }}" data-parent="{{ $msub->parentsub->id }}" onclick="showDataReport(this);"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                    <div class="switch">
                                                                        <label>
                                                                            Tidak
                                                                            <input type="checkbox" name="checkboxReportData[]" id="checkboxReportData{{ $msub->id }}" value="{{ $msub->id }}" data-parent="{{ $msub->parentsub->id }}" disabled>
                                                                            <span class="lever"></span>
                                                                            Semua Data
                                                                        </label>
                                                                    </div>
                                                                    <div class="switch">
                                                                        <label>
                                                                            Tidak
                                                                            <input type="checkbox" name="checkboxShowNominal[]" id="checkboxShowNominal{{ $msub->id }}" value="{{ $msub->id }}" data-parent="{{ $msub->parentsub->id }}" disabled>
                                                                            <span class="lever"></span>
                                                                            Nominal
                                                                        </label>
                                                                    </div>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td>
                                                            {!! $m->name !!}
                                                        </td>
                                                        <td class="center">
                                                            <label>
                                                                <input type="checkbox" name="checkboxView[]" id="checkboxView{{ $m->id }}" value="{{ $m->id }}" data-parent=""/>
                                                                <span>Pilih</span>
                                                            </label>
                                                        </td>
                                                        <td class="center">
                                                            <label>
                                                                <input type="checkbox" name="checkboxUpdate[]" id="checkboxUpdate{{ $m->id }}" value="{{ $m->id }}" data-parent=""/>
                                                                <span>Pilih</span>
                                                            </label>
                                                        </td>
                                                        <td class="center">
                                                            <label>
                                                                <input type="checkbox" name="checkboxDelete[]" id="checkboxDelete{{ $m->id }}" value="{{ $m->id }}" data-parent=""/>
                                                                <span>Pilih</span>
                                                            </label>
                                                        </td>
                                                        <td class="center">
                                                            <label>
                                                                <input type="checkbox" name="checkboxVoid[]" id="checkboxVoid{{ $m->id }}" value="{{ $m->id }}" data-parent=""/>
                                                                <span>Pilih</span>
                                                            </label>
                                                        </td>
                                                        <td class="center">
                                                            <label>
                                                                <input type="checkbox" name="checkboxJournal[]" id="checkboxJournal{{ $m->id }}" value="{{ $m->id }}" data-parent=""/>
                                                                <span>Pilih</span>
                                                            </label>
                                                        </td>
                                                        <td class="center">
                                                            <label>
                                                                <input type="checkbox" name="checkboxReport[]" id="checkboxReport{{ $m->id }}" value="{{ $m->id }}" data-parent=""/>
                                                                <span>Pilih</span>
                                                            </label>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </p>
                            </div>
                            <div id="accessdata" class="col s12">
                                <div class="row mt-1 center-align">
                                    <div class="col s12">
                                        <h5 class="card-title center">Penempatan (Plant)</h5>
                                        <table class="bordered centered">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2">NO</th>
                                                    <th rowspan="2">NAMA</th>
                                                    <th rowspan="2">TIPE</th>
                                                    <th rowspan="2">PERUSAHAAN</th>
                                                    <th>AKSES</th>
                                                </tr>
                                                <tr>
                                                    <th>
                                                        <label>
                                                            <input type="checkbox" onclick="checkAllPlace(this);" id="check-all-place">
                                                            <span>Semua</span>
                                                        </label>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($place as $row)
                                                <tr>
                                                    <td>{{ $row->code }}</td>
                                                    <td>{{ $row->name }}</td>
                                                    <td>{{ $row->type() }}</td>
                                                    <td>{{ $row->company->name }}</td>
                                                    <td>
                                                        <label>
                                                            <input type="checkbox" name="checkplace[]" id="checkplace{{ $row->id }}" value="{{ $row->id }}">
                                                            <span>Pilih</span>
                                                        </label>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col s12">
                                        <h5 class="card-title center">Gudang</h5>
                                        <table class="bordered centered">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2">NO</th>
                                                    <th rowspan="2">NAMA</th>
                                                    <th>AKSES</th>
                                                </tr>
                                                <tr>
                                                    <th>
                                                        <label>
                                                            <input type="checkbox" onclick="checkAllWarehouse(this);" id="check-all-warehouse">
                                                            <span>Semua</span>
                                                        </label>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($warehouse as $row)
                                                <tr>
                                                    <td>{{ $row->code }}</td>
                                                    <td>{{ $row->name }}</td>
                                                    <td>
                                                        <label>
                                                            <input type="checkbox" name="checkwarehouse[]" id="checkwarehouse{{ $row->id }}" value="{{ $row->id }}">
                                                            <span>Pilih</span>
                                                        </label>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div id="copyaccess" class="col s12">
                                <h5 align="center">Silahkan pilih target karyawan untuk menerima salinan.</h5>
                                <div class="row">
                                    <div class="input-field col s12">
                                        <select class="browser-default" multiple id="arr_user" name="arr_user[]"></select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col s12 mt-3">
                            
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat mr-1">Close</a>
        <button class="btn waves-effect waves-light right submit" onclick="saveAccess();">Simpan <i class="material-icons right">send</i></button>
    </div>
</div>

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div>

<!-- END: Page Main-->
<script>
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
    var arrgroup = @json($group);
    var tempuser = 0;
    var district = [], subdistrict = [];
    $(function() {
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        $('.select2').on('select2:open', function (e) {
            const evt = "scroll.select2";
            $(e.target).parents().off(evt);
            $(window).off(evt);
        });

        loadDataTable();
        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
        });
        
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                $('#type').trigger('change').formSelect();
            },
            onOpenEnd: function(modal, trigger) { 
                $('#name').focus();
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
                $('.tabs').tabs();
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                $('#type').val('2');
                $('#province_id,#city_id,#country_id').empty();
                M.updateTextFields();
                $('.row_bank').remove();
                $('.row_info').remove();
                $('.row_driver').remove();
                $('#manager_select').empty();
                refreshGroup();
                $('#subdistrict_id').empty().append(`
                    <option value="">--Pilih ya--</option>
                `);
                $('#name').prop('readonly',false);
            }
        });
       
        $('#modal2').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
                
            },
            onCloseEnd: function(modal, trigger){
                $('#tempuser').val('');
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
        
        $('#modal3').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
                $('.tabs').tabs();
                $('.modal-content').scrollTop(0);
            },
            onCloseEnd: function(modal, trigger){
                $('#tempuseraccess').val('');
                $('#tempname').text('');
                $('#form_data_access input:checkbox').prop( "checked", false);
                $('#form_data_access input[name="checkboxViewData[]"]').prop( "disabled", true);
                $('#arr_user').empty();
            }
        });

        $('#modal4').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
                
            },
            onCloseEnd: function(modal, trigger){
                $('#validation_alertImport').hide();
                $('#validation_alertImport').html('');
            }
        });

        select2ServerSide('#province_id', '{{ url("admin/select2/province") }}');
        select2ServerSide('#city_id', '{{ url("admin/select2/city") }}');
        select2ServerSide('#country_id', '{{ url("admin/select2/country") }}');
        select2ServerSide('#arr_user', '{{ url("admin/select2/employee") }}');

        $('#body-bank').on('click', '.delete-data-bank', function() {
            $(this).closest('tr').remove();
            $('input[name="check"]').each(function(i, e){
                $(this).val(i);
            });
        });

        $('#body-info').on('click', '.delete-data-info', function() {
            $(this).closest('tr').remove();
        });

        $('#body-driver').on('click', '.delete-data-driver', function() {
            $(this).closest('tr').remove();
        });

        refreshGroup();

        $('#form_dataimport').submit(function(event) {
            event.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                type: $(this).attr('method'),
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('#validation_alertImport').hide();
                    $('#validation_alertImport').html('');
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    if(response.status == 200) {
                        successImport();
                        M.toast({
                            html: response.message
                        });
                    } else if(response.status == 422) {
                        $('#validation_alertImport').show();
                        $('.modal-content').scrollTop(0);

                        $.each(response.error, function(i, val) {
                            $('#validation_alertImport').append(`
                                <div class="card-alert card red">
                                    <div class="card-content white-text">
                                        <p> Line <b>` + val.row + `</b> in column <b>` + val.attribute + `</b> </p>
                                        <p> ` + val.errors[0] + `</p>
                                    </div>
                                    <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                            `);
                        });
                    }else if(response.status == 432) {
                        $('#validation_alertImport').show();
                        $('.modal-content').scrollTop(0);

                        $.each(response.error, function(i, val) {
                            $('#validation_alertImport').append(`
                                    <div class="card-alert card red">
                                        <div class="card-content white-text">
                                            <p>`+val+`</p>
                                        </div>
                                        <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                    </div>
                                `);
                        });
                    } else {
                        M.toast({
                            html: response.message
                        });
                    }
                    loadingClose('.modal-content');
                    $('#form_dataimport')[0].reset();
                },
                error: function(response) {
                    var errors = response.responseJSON.errors;
                    var errorMessage = '';
                    if(response.status == 422) {
                        $('#validation_alertImport').show();
                        $('.modal-content').scrollTop(0);
                        
                        swal({
                            title: 'Ups! Validation',
                            text: 'Check your form.',
                            icon: 'warning'
                        });

                        $.each(errors, function(index, error) {
                        var message = '';

                        $.each(error.errors, function(index, value) {
                            message += value + '\n';
                        });

                        errorMessage += errors.file;
                    });

                    $('#validation_alertImport').html(`
                        <div class="card-alert card red">
                            <div class="card-content white-text">
                                <p>` + errorMessage + `</p>
                            </div>
                            <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                    `).show();

                    }
                }
            });

        });

        /* $('input[name^="checkboxView[]"]').click(function(){
            var ada = false;
            $('input[name^="checkboxView[]"][data-parent="' + $(this).data('parent') + '"]').each(function(){
                if($(this).is(":checked")){
                    ada = true;
                }
            });
            if(ada){
                $('.checkboxView[data-id="' + $(this).data('parent') + '"]').prop('checked', true);
            }else{
                $('.checkboxView[data-id="' + $(this).data('parent') + '"]').prop('checked', false);
            }
        }); */
    });

    function successImport(){
        loadDataTable();
        $('#modal4').modal('close');
    }

    function refreshGroup(){
        $('#group_id').empty();
        var type = $('#type').val();
        $('#group_id').append(`
            <option value="">--Kosong--</option>
        `);
        for(let i=0;i<arrgroup.length;i++){
            if(arrgroup[i]['type'] == type){
                $('#group_id').append(`
                    <option value="` + arrgroup[i]['id'] + `">` + arrgroup[i]['name'] + `</option>
                `);
            }
        }
        $('#group_id').formSelect();
    }

    function showDataView(element){
        if($(element).is(':checked')){
            $(element).parent().parent().find('input[name="checkboxViewData[]"]').prop('disabled',false);
        }else{
            if($(element).parent().parent().find('input[name="checkboxViewData[]"]').is(':checked')){
                $(element).parent().parent().find('input[name="checkboxViewData[]"]').prop('checked',false);
            }
            $(element).parent().parent().find('input[name="checkboxViewData[]"]').prop('disabled',true);
        }
    }

    function showDataReport(element){
        if($(element).is(':checked')){
            $(element).parent().parent().find('input[name="checkboxReportData[]"]').prop('disabled',false);
            $(element).parent().parent().find('input[name="checkboxShowNominal[]"]').prop('disabled',false);
        }else{
            if($(element).parent().parent().find('input[name="checkboxReportData[]"]').is(':checked')){
                $(element).parent().parent().find('input[name="checkboxReportData[]"]').prop('checked',false);
                $(element).parent().parent().find('input[name="checkboxShowNominal[]"]').prop('checked',false);
            }
            $(element).parent().parent().find('input[name="checkboxReportData[]"]').prop('disabled',true);
            $(element).parent().parent().find('input[name="checkboxShowNominal[]"]').prop('disabled',true);
        }
    }

    function access(id,name){
        $('#modal3').modal('open');
        $('#tempuseraccess').val(id);
        $('#tempname').text(name);

		$.ajax({
			 url: '{{ Request::url() }}/get_access',
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

                if(response.menus.length > 0){
                    $.each(response.menus, function(i, val) {
                        $('#checkbox' + val.type + val.menu_id).prop( "checked", true);
                        if(val.type == 'View'){
                            $('#checkbox' + val.type + 'Data' + val.menu_id).prop( "disabled", false);
                            if(val.mode == 'all'){
                                $('#checkbox' + val.type + 'Data' + val.menu_id).prop( "checked", true);
                            }
                        }
                        if(val.type == 'Report'){
                            $('#checkbox' + val.type + 'Data' + val.menu_id).prop( "disabled", false);
                            if(val.mode == 'all'){
                                $('#checkbox' + val.type + 'Data' + val.menu_id).prop( "checked", true);
                            }
                            $('#checkboxShowNominal' + val.menu_id).prop( "disabled", false);
                            if(val.show_nominal == '1'){
                                $('#checkboxShowNominal' + val.menu_id).prop( "checked", true);
                            }
                        }
                    });
                }

                if(response.places.length > 0){
                    $.each(response.places, function(i, val) {
                        $('#checkplace' + val.id).prop( "checked", true);
                    });
                }

                if(response.warehouses.length > 0){
                    $.each(response.warehouses, function(i, val) {
                        $('#checkwarehouse' + val.id).prop( "checked", true);
                    });
                }
			 },
			 error: function() {
				loadingClose('.modal-content');
				swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
			 }
		});
    }

    function getCity(){
        $('#city_id,#district_id,#subdistrict_id').empty().append(`
            <option value="">--Pilih ya--</option>
        `);
        if($('#province_id').val()){
            $.each($('#province_id').select2('data')[0].cities, function(i, value) {
                $('#city_id').append(`
                    <option value="` + value.id + `" data-district='` + JSON.stringify(value.district) + `'>` + value.code + ` - `  + value.name + `</option>
                `);
            });
        }
    }

    function getDistrict(){
        $('#district_id,#subdistrict_id').empty().append(`
            <option value="">--Pilih ya--</option>
        `);
        if($('#city_id').val()){
            $.each($("#city_id").select2().find(":selected").data("district"), function(i, value) {
                $('#district_id').append(`
                    <option value="` + value.id + `" data-subdistrict='` + JSON.stringify(value.subdistrict) + `'>` + value.code + ` - ` + value.name + `</option>
                `);
            });
        }
    }

    function getSubdistrict(){
        $('#subdistrict_id').empty().append(`
            <option value="">--Pilih ya--</option>
        `);
        if($('#district_id').val()){
            $.each($("#district_id").select2().find(":selected").data("subdistrict"), function(i, value) {
                $('#subdistrict_id').append(`
                    <option value="` + value.id + `">` + value.code + ` - ` + value.name + `</option>
                `);
            });
        }
    }

    function saveAccess(){
		swal({
            title: "Apakah anda yakin simpan akses?",
            text: "Hati-hati dalam menentukan hak akses!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                
                var formData = new FormData($('#form_data_access')[0]);
                
                $.ajax({
                    url: '{{ Request::url() }}/create_access',
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
                        $('#validation_alert_access').hide();
                        $('#validation_alert_access').html('');
                        loadingOpen('#modal3');
                    },
                    success: function(response) {
                        loadingClose('#modal3');

                        if(response.status == 200) {
                            $('#modal3').modal('close');
                            M.toast({
                                html: response.message
                            });
                        } else if(response.status == 422) {
                            $('#validation_alert_access').show();
                            $('.modal-content').scrollTop(0);
                            
                            swal({
                                title: 'Ups! Validation',
                                text: 'Check your form.',
                                icon: 'warning'
                            });

                            $.each(response.error, function(i, val) {
                                $.each(val, function(i, val) {
                                    $('#validation_alert_access').append(`
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
                        loadingClose('#modal3');
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

    function attachment(id){
        tempuser = id;
        $('#modal2').modal('open');
        $('#tempuser').val(id);

        $('#list-images').empty();
		$.ajax({
			 url: '{{ Request::url() }}/get_files',
			 type: 'POST',
			 dataType: 'JSON',
			 data: {
				id: $('#tempuser').val()
			 },
			 headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			 },
			 beforeSend: function() {
				loadingOpen('.modal-content');
			 },
			 success: function(response) {
				if(response.length > 0){
					$.each(response, function(i, val) {
						$('#list-images').append(`
							<div class="col s12 m3 center" id="picture` + val.code + `">
								` + val.image + `
								<p class="mt-3">
                                    <h6>` + val.name + `</h6>
									<button class="btn btn-danger btn-sm" onclick="destroyFile('` + val.code + `');"><i class="material-icons">delete_forever</i></button>
								</p>
							</div>
						`);
					});
				}else{
					$('#list-images').append(`
						<div class="col s12 center">
							<div class="card-alert card red">
                                <div class="card-content white-text">
                                    <p>File tidak ditemukan pada pengguna ini.</p>
                                </div>
                            </div>
						</div>
					`);
				}
				
				loadingClose('.modal-content');
			 },
			 error: function() {
				loadingClose('.modal-content');
				swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
			 }
		});
    }

    Dropzone.options.dropzoneMultiple = {
        paramName: "file",
        maxFilesize: 1,
        maxFiles: 5,
        acceptedFiles: ".jpeg,.jpg,.png,.gif,.pdf",
        init: function() {
            this.on("sending", function(file, xhr, formData){
                formData.append('id', tempuser);
            });
            this.on("success", function(file, responseText) {
                if(responseText.status == '422'){
                    M.toast({
                        html: responseText.message
                    });
                }
            });
        }
    };

    function destroyFile(val) {
		swal({
            title: "Apakah anda yakin hapus gambar?",
            text: "Anda tidak bisa mengembalikan data yang terhapus!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
				$.ajax({
                    url: '{{ Request::url() }}/destroy_file',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { id : val },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        loadingOpen('.modal-content');
                    },
                    success: function(response) {
                        loadingClose('.modal-content');
                        if(response.status == 200) {
                            $('#picture' + val).remove();
                        }

                        M.toast({
                            html: response.message
                        });
                    },
                        error: function() {
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

    function addBank(){
        var count = $('input[name^="arr_bank"]').length;
        var checked = '';
       
        if(count < 1){
            checked = 'checked';
        }
        $('#last-row-bank').before(`
            <tr class="row_bank">
                <input type="hidden" name="arr_id_bank[]" value="">
                <td>
                    <input name="arr_bank[]" type="text" placeholder="Nama Bank">
                </td>
                <td>
                    <input name="arr_name[]" type="text" placeholder="Atas nama">
                </td>
                <td class="center">
                    <input name="arr_no[]" type="text" placeholder="No rekening">
                </td>
                <td>
                    <input name="arr_branch[]" type="text" placeholder="Cabang">
                </td>
                <td class="center">
                    <label>
                        <input class="with-gap" name="check" type="radio" value="` + count + `" `+checked+`>
                        <span>Pilih</span>
                    </label>
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-bank" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        select2ServerSide('#arr_bank' + count, '{{ url("admin/select2/bank") }}');
    }

    function addInfo(){
        var count = makeid(10);
        $('#last-row-info').before(`
            <tr class="row_info">
                <input type="hidden" name="arr_id_data[]" value="">
                <td>
                    <input name="arr_title[]" type="text" placeholder="Judul informasi tambahan" style="width:200px !important;">
                </td>
                <td class="center">
                    <input name="arr_content[]" type="text" placeholder="Isi informasi tambahan" style="width:200px !important;">
                </td>
                <td class="center">
                    <input name="arr_npwp[]" type="text" placeholder="Nomor NPWP" style="width:200px !important;">
                </td>
                <td class="center">
                    <input name="arr_address[]" type="text" placeholder="Alamat Kantor" style="width:200px !important;">
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_country` + count + `" name="arr_country[]"></select>
                </td>
                <td class="center">
                    <select class="browser-default select2" id="arr_province` + count + `" name="arr_province[]">
                        <option value="">--Silahkan pilih--</option>
                        @foreach($province as $row)
                            <option value="{{ $row->id }}" data-code="{{ $row->code }}">{{ $row->code.' - '.$row->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_city` + count + `" name="arr_city[]"></select>
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_district` + count + `" name="arr_district[]"></select>
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_subdistrict` + count + `" name="arr_subdistrict[]"></select>
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-info" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        select2ServerSide('#arr_country' + count, '{{ url("admin/select2/country") }}');
        $('#arr_province' + count).select2({
            dropdownAutoWidth: true,
            width: '100%',
        });
        $('#arr_city'+ count).select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/city_by_province") }}',
                type: 'GET',
                dataType: 'JSON',
                data: function(params) {
                    return {
                        search: params.term,
                        province: $('#arr_province' + count).select2().find(":selected").data("code"),
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.items
                    }
                }
            }
        });

        $('#arr_city'+ count).bind('change', function() {
            if(!$(this).val()){
                $('#arr_district'+ count).empty();
                $('#arr_subdistrict'+ count).empty();
            }
        });

        $('#arr_district'+ count).bind('change', function() {
            if(!$(this).val()){
                $('#arr_subdistrict'+ count).empty();
            }
        });

        $('#arr_district'+ count).select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/district_by_city") }}',
                type: 'GET',
                dataType: 'JSON',
                data: function(params) {
                    return {
                        search: params.term,
                        city: $('#arr_city' + count).select2('data')[0].code,
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.items
                    }
                }
            }
        });

        $('#arr_subdistrict'+ count).select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/subdistrict_by_district") }}',
                type: 'GET',
                dataType: 'JSON',
                data: function(params) {
                    return {
                        search: params.term,
                        district: $('#arr_district' + count).select2('data')[0].code,
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.items
                    }
                }
            }
        });
    }

    function addDriver(){
        $('#last-row-driver').before(`
            <tr class="row_driver">
                <input type="hidden" name="arr_id_driver[]" value="">
                <td>
                    <input name="arr_driver_name[]" type="text" placeholder="Nama supir...">
                </td>
                <td class="center">
                    <input name="arr_driver_hp[]" type="text" placeholder="Ex: 081333313123">
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-driver" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
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
            "order": [[0, 'desc']],
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    status : $('#filter_status').val(),
                    type : $('#filter_type').val(),
                    group : $('#group_type').val()
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
                { name: 'name', className: '' },
                { name: 'username', className: 'center-align' },
                { name: 'id_card', className: 'center-align' },
                { name: 'type', searchable: false, orderable: false, className: 'center-align' },
                { name: 'group_id', searchable: false, orderable: false, className: 'center-align' },
                { name: 'group_id', searchable: false, orderable: false, className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'right-align' },
            ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle',
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
            },
        });
        $('.dt-buttons').appendTo('#datatable_buttons');
        $('select[name="datatable_serverside_length"]').addClass('browser-default');
	}

    function changeMode(element){
        if($(element).val() == '1'){
            $('.other_inputs').hide();
            $('.customer_inputs').hide();
            $('.employee_inputs').show();
        }else{
            $('.other_inputs').show();
            $('.employee_inputs').hide();
            if($(element).val() == '2'){
                $('.customer_inputs').show();
            }else{
                $('.customer_inputs').hide();
            }
        }
    }

    function save(){
			
        var formData = new FormData($('#form_data')[0]);

        formData.delete('arr_id_data[]');
        formData.delete('arr_id_bank[]');
        formData.delete('arr_id_driver[]');
        formData.delete('arr_title[]');
        formData.delete('arr_content[]');
        formData.delete('arr_npwp[]');
        formData.delete('arr_address[]');
        formData.delete('arr_country[]');
        formData.delete('arr_province[]');
        formData.delete('arr_city[]');
        formData.delete('arr_district[]');
        formData.delete('arr_subdistrict[]');

        $('input[name^="arr_id_data[]"]').each(function(){
            formData.append('arr_id_data[]',
                $(this).val() ? $(this).val() : ''
            );
        });
        
        $('input[name^="arr_id_bank[]"]').each(function(){
            formData.append('arr_id_bank[]',
                $(this).val() ? $(this).val() : ''
            );
        });

        $('input[name^="arr_id_driver[]"]').each(function(){
            formData.append('arr_id_driver[]',
                $(this).val() ? $(this).val() : ''
            );
        });

        $('input[name^="arr_title[]"]').each(function(index){
            formData.append('arr_title[]',
                $(this).val() ? $(this).val() : ''
            );
            formData.append('arr_content[]',($('input[name^="arr_content[]"]').eq(index).val() ? $('input[name^="arr_content[]"]').eq(index).val() : ''));
            formData.append('arr_npwp[]',($('input[name^="arr_npwp[]"]').eq(index).val() ? $('input[name^="arr_npwp[]"]').eq(index).val() : ''));
            formData.append('arr_address[]',($('input[name^="arr_address[]"]').eq(index).val() ? $('input[name^="arr_address[]"]').eq(index).val() : ''));
            formData.append('arr_country[]',($('select[name^="arr_country[]"]').eq(index).val() ? $('select[name^="arr_country[]"]').eq(index).val() : ''));
            formData.append('arr_province[]',($('select[name^="arr_province[]"]').eq(index).val() ? $('select[name^="arr_province[]"]').eq(index).val() : ''));
            formData.append('arr_city[]',($('select[name^="arr_city[]"]').eq(index).val() ? $('select[name^="arr_city[]"]').eq(index).val() : ''));
            formData.append('arr_district[]',($('select[name^="arr_district[]"]').eq(index).val() ? $('select[name^="arr_district[]"]').eq(index).val() : ''));
            formData.append('arr_subdistrict[]',($('select[name^="arr_subdistrict[]"]').eq(index).val() ? $('select[name^="arr_subdistrict[]"]').eq(index).val() : ''));
        });
        
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
                    $('#validation_alert').show();
                    $('.modal-content').scrollTop(0);
                    
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
                loadingClose('.modal-content');
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
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
                $('#name').val(response.name);
                $('#username').val(response.username);
                $('#employee_no').val(response.employee_no);
                $('#phone').val(response.phone);
                $('#email').val(response.email);
                $("#address").val(response.address);
                $('#type').val(response.type).trigger('change').formSelect();

                refreshGroup();
                
                $('#province_id,#country_id').empty();

                if(response.province_id){
                    $('#province_id').append(`
                        <option value="` + response.province_id + `">` + response.province_name + `</option>
                    `);
                }
                if(!response.manager_id && response.type == 1){
                    $('#manager_select').append(`
                        <select class="select2 browser-default" id="manager_id" name="manager_id">
                                    <option value="">--Pilih ya--</option>
                        </select>
                        <label class="active" for="manager_id">Select Manager</label>
                    `);
                    select2ServerSide('#manager_id', '{{ url("admin/select2/employee") }}');
                }
                
                $('#subdistrict_id,#district_id,#city_id').empty().append(`
                    <option value="">--Pilih ya--</option>
                `);

                if(response.cities){
                    $.each(response.cities, function(i, val) {
                        $('#city_id').append(`
                            <option value="` + val.id + `" ` + (val.id == response.city_id ? 'selected' : '') + `>` + val.code + ` - ` + val.name + `</option>
                        `);
                    });

                    let index = -1;

                    $.each(response.cities, function(i, val) {
                        if(val.id == response.city_id){
                            index = i;
                        }
                    });

                    if(index >= 0){
                        $.each(response.cities[index].district, function(i, value) {
                            let selected = '';
                            $('#district_id').append(`
                                <option value="` + value.id + `" ` + (value.id == response.district_id ? 'selected' : '') + ` data-subdistrict='` + JSON.stringify(value.subdistrict) + `'>` + value.code + ` - ` + value.name + `</option>
                            `);
                            if(value.id == response.district_id){
                                subdistrict = value.subdistrict;
                            }
                        });

                        $.each(subdistrict, function(i, value) {
                            $('#subdistrict_id').append(`
                                <option value="` + value.id + `" ` + (value.id == response.subdistrict_id ? 'selected' : '') + `>` + value.code + ` - ` + value.name + `</option>
                            `);
                        });
                    }
                }

                if(response.country_id){
                    $('#country_id').append(`
                        <option value="` + response.country_id + `">` + response.country_name + `</option>
                    `);
                }

                $('#tax_id').val(response.tax_id);
                $('#tax_name').val(response.tax_name);
                $('#tax_address').val(response.tax_address);
                $('#id_card').val(response.id_card);
                $('#id_card_address').val(response.id_card_address);
                $('#gender').val(response.gender).formSelect();
                $('#group_id').val(response.group_id).formSelect();
                $('#limit_credit').val(response.limit_credit);

                if(response.type == '1'){
                    $('#company_id').val(response.company_id).formSelect();
                    $('#position_id').val(response.position_id).formSelect();
                    $('#married_status').val(response.married_status).formSelect();
                    $('#married_date').val(response.married_date);
                    $('#children').val(response.children);
                    $('#employee_type').val(response.employee_type).formSelect();
                    $('#place_id').val(response.place_id).formSelect();
                }else{
                    $('#pic').val(response.pic);
                    $('#pic_no').val(response.pic_no);
                    $('#office_no').val(response.office_no);
                    $('#limit_credit').val(response.limit_credit);
                    $('#top').val(response.top);
                    $('#top_internal').val(response.top_internal);
                }

                $('.row_bank').remove();

                if(response.has_document){
                    $('#name').prop('readonly',true);
                }

                if(response.banks.length > 0){
                    $.each(response.banks, function(i, val) {
                        $('#last-row-bank').before(`
                            <tr class="row_bank">
                                <input type="hidden" name="arr_id_bank[]" value="` + val.id + `">
                                <td>
                                    <input name="arr_bank[]" type="text" placeholder="Atas nama" value="` + val.bank + `">
                                </td>
                                <td>
                                    <input name="arr_name[]" type="text" placeholder="Atas nama" value="` + val.name + `">
                                </td>
                                <td class="center">
                                    <input name="arr_no[]" type="text" placeholder="No rekening" value="` + val.no + `">
                                </td>
                                <td>
                                    <input name="arr_branch[]" type="text" placeholder="Cabang" value="` + val.branch + `">
                                </td>
                                <td class="center">
                                    <label>
                                        <input class="with-gap" name="check" type="radio" value="` + i + `" ` + (val.is_default == '1' ? 'checked' : '') + `>
                                        <span>Pilih</span>
                                    </label>
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-bank" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                        select2ServerSide('#arr_bank' + i, '{{ url("admin/select2/bank") }}');
                        $('#arr_bank' + i).append(`
                            <option value="` + val.bank_id + `">` + val.bank_name + `</option>
                        `);
                    });
                }

                if(response.datas.length > 0){
                    $.each(response.datas, function(i, val) {
                        var count = makeid(10);
                        $('#last-row-info').before(`
                            <tr class="row_info">
                                <input type="hidden" name="arr_id_data[]" value="` + val.id + `">
                                <td>
                                    <input name="arr_title[]" type="text" placeholder="Judul informasi tambahan" style="width:200px !important;" value="` + val.title + `">
                                </td>
                                <td class="center">
                                    <input name="arr_content[]" type="text" placeholder="Isi informasi tambahan" style="width:200px !important;" value="` + val.content + `">
                                </td>
                                <td class="center">
                                    <input name="arr_npwp[]" type="text" placeholder="Nomor NPWP" style="width:200px !important;" value="` + val.npwp + `">
                                </td>
                                <td class="center">
                                    <input name="arr_address[]" type="text" placeholder="Alamat Kantor" style="width:200px !important;" value="` + val.address + `">
                                </td>
                                <td class="center">
                                    <select class="browser-default" id="arr_country` + count + `" name="arr_country[]"></select>
                                </td>
                                <td class="center">
                                    <select class="browser-default select2" id="arr_province` + count + `" name="arr_province[]">
                                        <option value="">--Silahkan pilih--</option>
                                        @foreach($province as $row)
                                            <option value="{{ $row->id }}" data-code="{{ $row->code }}">{{ $row->code.' - '.$row->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="center">
                                    <select class="browser-default" id="arr_city` + count + `" name="arr_city[]"></select>
                                </td>
                                <td class="center">
                                    <select class="browser-default" id="arr_district` + count + `" name="arr_district[]"></select>
                                </td>
                                <td class="center">
                                    <select class="browser-default" id="arr_subdistrict` + count + `" name="arr_subdistrict[]"></select>
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-info" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                        if(val.country_id){
                            $('#arr_country' + count).append(`
                                <option value="` + val.country_id + `">` + val.country_name + `</option>
                            `);
                        }
                        select2ServerSide('#arr_country' + count, '{{ url("admin/select2/country") }}');
                        $('#arr_province' + count).select2({
                            dropdownAutoWidth: true,
                            width: '100%',
                        });

                        if(val.province_id){
                            $('#arr_province' + count).val(val.province_id).trigger('change');
                        }

                        if(val.city_id){
                            $('#arr_city' + count).append(`
                                <option value="` + val.city_id + `">` + val.city_name + `</option>
                            `);
                        }

                        $('#arr_city'+ count).select2({
                            placeholder: '-- Kosong --',
                            minimumInputLength: 1,
                            allowClear: true,
                            cache: true,
                            width: 'resolve',
                            dropdownParent: $('body').parent(),
                            ajax: {
                                url: '{{ url("admin/select2/city_by_province") }}',
                                type: 'GET',
                                dataType: 'JSON',
                                data: function(params) {
                                    return {
                                        search: params.term,
                                        province: $('#arr_province' + count).select2().find(":selected").data("code"),
                                    };
                                },
                                processResults: function(data) {
                                    return {
                                        results: data.items
                                    }
                                }
                            }
                        });

                        $('#arr_city'+ count).bind('change', function() {
                            if(!$(this).val()){
                                $('#arr_district'+ count).empty();
                                $('#arr_subdistrict'+ count).empty();
                            }
                        });

                        $('#arr_district'+ count).bind('change', function() {
                            if(!$(this).val()){
                                $('#arr_subdistrict'+ count).empty();
                            }
                        });

                        if(val.district_id){
                            $('#arr_district' + count).append(`
                                <option value="` + val.district_id + `">` + val.district_name + `</option>
                            `);
                        }

                        $('#arr_district'+ count).select2({
                            placeholder: '-- Kosong --',
                            minimumInputLength: 1,
                            allowClear: true,
                            cache: true,
                            width: 'resolve',
                            dropdownParent: $('body').parent(),
                            ajax: {
                                url: '{{ url("admin/select2/district_by_city") }}',
                                type: 'GET',
                                dataType: 'JSON',
                                data: function(params) {
                                    return {
                                        search: params.term,
                                        city: $('#arr_city' + count).select2('data')[0].code,
                                    };
                                },
                                processResults: function(data) {
                                    return {
                                        results: data.items
                                    }
                                }
                            }
                        });

                        if(val.subdistrict_id){
                            $('#arr_subdistrict' + count).append(`
                                <option value="` + val.subdistrict_id + `">` + val.subdistrict_name + `</option>
                            `);
                        }

                        $('#arr_subdistrict'+ count).select2({
                            placeholder: '-- Kosong --',
                            minimumInputLength: 1,
                            allowClear: true,
                            cache: true,
                            width: 'resolve',
                            dropdownParent: $('body').parent(),
                            ajax: {
                                url: '{{ url("admin/select2/subdistrict_by_district") }}',
                                type: 'GET',
                                dataType: 'JSON',
                                data: function(params) {
                                    return {
                                        search: params.term,
                                        district: $('#arr_district' + count).select2('data')[0].code,
                                    };
                                },
                                processResults: function(data) {
                                    return {
                                        results: data.items
                                    }
                                }
                            }
                        });
                    });
                }

                if(response.drivers.length > 0){
                    $.each(response.drivers , function(i, val) {
                        $('#last-row-driver').before(`
                            <tr class="row_driver">
                                <input type="hidden" name="arr_id_driver[]" value="` + val.id + `">
                                <td>
                                    <input name="arr_driver_name[]" type="text" placeholder="Nama supir..." value="` + val.name + `">
                                </td>
                                <td class="center">
                                    <input name="arr_driver_hp[]" type="text" placeholder="Ex: 081333313123" value="` + val.hp + `">
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-driver" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                    });
                }

                if(response.status == '1'){
                    $('#status').prop( "checked", true);
                }else{
                    $('#status').prop( "checked", false);
                }

                if(response.is_special_lock_user == '1'){
                    $('#is_special_lock_user').prop( "checked", true);
                }else{
                    $('#is_special_lock_user').prop( "checked", false);
                }

                if(response.is_ar_invoice == '1'){
                    $('#is_ar_invoice').prop( "checked", true);
                }else{
                    $('#is_ar_invoice').prop( "checked", false);
                }

                $('.modal-content').scrollTop(0);
                $('#name').focus();
                
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

    function destroy(id){
        swal({
            title: "Apakah anda yakin?",
            text: "Anda tidak bisa mengembalikan data yang terhapus!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                $.ajax({
                    url: '{{ Request::url() }}/destroy',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { id : id },
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

    function print(){
        var search = window.table.search(), status = $('#filter_status').val(), type = $('#filter_type').val(), company = $('#filter_company').val(), account = $('#filter_account').val();
        arr_id_temp=[];
        $.map(window.table.rows('.selected').nodes(), function (item) {
            var poin = $(item).find('td:nth-child(4)').text().trim();
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
                loadingOpen('.modal-content');
            },
            success: function(response) {
                printService.submit({
                    'type': 'INVOICE',
                    'url': response.message
                })
                
               
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

    function exportExcel(){
        var search = window.table.search();
        var status = $('#filter_status').val();
        var type = $('#filter_type').val();
        var group = $('#group_type').val();
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status + "&type=" + type + "&group=" + group;
    }

    function checkAll(element,parent,mode){
        var param = '';
        if(mode == 'view'){
            param = 'checkboxView';
        }
        if(mode == 'update'){
            param = 'checkboxUpdate';
        }
        if(mode == 'delete'){
            param = 'checkboxDelete';
        }
        if(mode == 'void'){
            param = 'checkboxVoid';
        }
        if(mode == 'journal'){
            param = 'checkboxJournal';
        }
        
        if($(element).is(':checked')){
            $('input[name^="' + param + '"][data-parent="' + parent + '"]').each(function(){
                if(!$(this).is(':checked')){
                    $(this).prop( "checked", true);
                }
            });
        }else{
            $('input[name^="' + param + '"][data-parent="' + parent + '"]').each(function(){
                if($(this).is(':checked')){
                    $(this).prop( "checked", false);
                }
            });
        }
    }

    function checkAllPlace(element){
        if($(element).is(':checked')){
            $('input[name^="checkplace"]').each(function(){
                if(!$(this).is(':checked')){
                    $(this).prop( "checked", true);
                }
            });
        }else{
            $('input[name^="checkplace"]').each(function(){
                if($(this).is(':checked')){
                    $(this).prop( "checked", false);
                }
            });
        }
    }

    function checkAllWarehouse(element){
        if($(element).is(':checked')){
            $('input[name^="checkwarehouse"]').each(function(){
                if(!$(this).is(':checked')){
                    $(this).prop( "checked", true);
                }
            });
        }else{
            $('input[name^="checkwarehouse"]').each(function(){
                if($(this).is(':checked')){
                    $(this).prop( "checked", false);
                }
            });
        }
    }
</script>