<script src="{{ url('app-assets/js/dropzone.min.js') }}"></script>
<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
<style>
    .modal {
        top:0px !important;
    }

    table > thead > tr > th {
        font-size: 13px !important;
    }

    table.bordered th {
        padding: 5px !important;
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
                            <i class="material-icons hide-on-med-and-up">file_upload</i>
                            <span class="hide-on-small-onl">Import</span>
                            <i class="material-icons right">file_upload</i>
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
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">List Data</h4>
                                    <div class="row">
                                        <div class="col s12">
                                            <div id="datatable_buttons"></div>
                                            <table id="datatable_serverside" class="display responsive-table wrap">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Nama</th>
                                                        <th>Username</th>
                                                        <th>NIK/Code</th>
                                                        <th>Tipe</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;min-width:100%;max-width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Tambah/Edit {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="input-field col s3">
                            <select id="type" name="type" onchange="changeMode(this);refreshGroup();">
                                <option value="1">Pegawai</option>
                                <option value="2">Customer</option>
                                <option value="3">Supplier</option>
                                <option value="4">Ekspedisi</option>
                            </select>
                            <label for="type">Tipe</label>
                        </div>
                        <div class="input-field col s3">
                            <input type="hidden" id="temp" name="temp">
                            <input id="name" name="name" type="text" placeholder="Nama">
                            <label class="active" for="name">Nama</label>
                        </div>
                        <div class="input-field col s3 employee_inputs">
                            <input id="username" name="username" type="text" placeholder="Username">
                            <label class="active" for="username">Username</label>
                        </div>
                        <div class="input-field col s3 employee_inputs">
                            <input id="password" name="password" type="password" placeholder="Password">
                            <label class="active" for="password">Password</label>
                        </div>
                        <div class="input-field col s3">
                            <input id="phone" name="phone" type="text" placeholder="Phone">
                            <label class="active" for="phone">Telepon</label>
                        </div>
                        <div class="input-field col s3">
                            <input id="email" name="email" type="text" placeholder="Email">
                            <label class="active" for="email">Email</label>
                        </div>
                        <div class="input-field col s3">
                            <input id="address" name="address" type="text" placeholder="Alamat">
                            <label class="active" for="address">Alamat</label>
                        </div>
                        <div class="input-field col s3">
                            <input id="id_card" name="id_card" type="text" placeholder="No KTP" class="ktp">
                            <label class="active" for="id_card">No KTP</label>
                        </div>
                        <div class="input-field col s3">
                            <input id="id_card_address" name="id_card_address" type="text" placeholder="Alamat KTP">
                            <label class="active" for="id_card_address">Alamat KTP</label>
                        </div>
                        <div class="input-field col s3">
                            <input id="tax_id" name="tax_id" type="text" placeholder="No. NPWP" class="npwp">
                            <label class="active" for="tax_id">No. NPWP</label>
                        </div>
                        <div class="input-field col s3">
                            <input id="tax_name" name="tax_name" type="text" placeholder="Nama di NPWP">
                            <label class="active" for="tax_name">Nama NPWP</label>
                        </div>
                        <div class="input-field col s3">
                            <input id="tax_address" name="tax_address" type="text" placeholder="Alamat di NPWP">
                            <label class="active" for="tax_address">Alamat NPWP</label>
                        </div>
                        <div class="input-field col s3 employee_inputs">
                            <select id="married_status" name="married_status">
                                <option value="1">Single</option>
                                <option value="2">Menikah</option>
                                <option value="3">Cerai</option>
                            </select>
                            <label for="married_status">Status Pernikahan</label>
                        </div>
                        <div class="input-field col s3 employee_inputs">
                            <input id="married_date" name="married_date" type="date">
                            <label class="active" for="married_date">Tgl.Pernikahan</label>
                        </div>
                        <div class="input-field col s3 employee_inputs">
                            <input id="children" name="children" type="number" value="0">
                            <label class="active" for="children">Jumlah Anak</label>
                        </div>
                        <div class="input-field col s3 employee_inputs">
                            <select id="company_id" name="company_id">
                                @foreach($company as $row)
                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                @endforeach
                            </select>
                            <label for="company_id">Perusahaan</label>
                        </div>
                        <div class="input-field col s3 employee_inputs">
                            <select id="place_id" name="place_id">
                                @foreach($place as $row)
                                    <option value="{{ $row->id }}">{{ $row->code }}</option>
                                @endforeach
                            </select>
                            <label for="place_id">Penempatan</label>
                        </div>
                        <div class="input-field col s3 employee_inputs">
                            <select id="department_id" name="department_id">
                                @foreach($department as $row)
                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                @endforeach
                            </select>
                            <label for="department_id">Departemen</label>
                        </div>
                        <div class="input-field col s3 employee_inputs">
                            <select id="position_id" name="position_id">
                                @foreach($position as $row)
                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                @endforeach
                            </select>
                            <label for="position_id">Posisi/Level</label>
                        </div>
                        <div class="input-field col s3">
                            <select id="gender" name="gender">
                                <option value="1">Laki-laki</option>
                                <option value="2">Wanita</option>
                                <option value="3">Lainnya</option>
                            </select>
                            <label for="gender">Jenis Kelamin</label>
                        </div>
                        <div class="input-field col s3 other_inputs" style="display:none;">
                            <input id="pic" name="pic" type="text" placeholder="PIC">
                            <label class="active" for="pic">PIC</label>
                        </div>
                        <div class="input-field col s3 other_inputs" style="display:none;">
                            <input id="pic_no" name="pic_no" type="text" placeholder="Kontak PIC">
                            <label class="active" for="pic_no">Kontak PIC</label>
                        </div>
                        <div class="input-field col s3 other_inputs" style="display:none;">
                            <input id="office_no" name="office_no" type="text" placeholder="Kontak Kantor">
                            <label class="active" for="office_no">Kontak Kantor</label>
                        </div>
                        <div class="input-field col s3 other_inputs" style="display:none;">
                            <input id="limit_credit" name="limit_credit" type="text" placeholder="Limit Kredit" onkeyup="formatRupiah(this)">
                            <label class="active" for="limit_credit">Limit Kredit</label>
                        </div>
                        <div class="input-field col s3 other_inputs" style="display:none;">
                            <input id="top" name="top" type="number" min="0" step="1" value="0">
                            <label class="active" for="top">TOP (Tempo Pembayaran)</label>
                        </div>
                        <div class="input-field col s3 other_inputs" style="display:none;">
                            <input id="top_internal" name="top_internal" type="number" min="0" step="1" value="0">
                            <label class="active" for="top_internal">TOP Internal</label>
                        </div>
                        <div class="input-field col s3">
                            <select class="browser-default" id="province_id" name="province_id"></select>
                            <label class="active" for="province_id">Provinsi</label>
                        </div>
                        <div class="input-field col s3">
                            <select class="browser-default" id="city_id" name="city_id"></select>
                            <label class="active" for="city_id">Kota/Kabupaten</label>
                        </div>
                        <div class="input-field col s3">
                            <select class="browser-default" id="country_id" name="country_id"></select>
                            <label class="active" for="country_id">Negara Asal</label>
                        </div>
                        <div class="input-field col s3">
                            <select id="group_id" name="group_id"></select>
                            <label for="group_id">Kelompok Partner Bisnis</label>
                        </div>
                        <div class="input-field col s3">
                            <div class="switch mb-1">
                                <label for="order">Status</label>
                                <label>
                                    Non-Active
                                    <input checked type="checkbox" id="status" name="status" value="1">
                                    <span class="lever"></span>
                                    Active
                                </label>
                            </div>
                        </div>
                        <div class="col s12 mt-3">
                            <ul class="tabs">
                                <li class="tab col m6"><a class="active" href="#rekform">Rekening</a></li>
                                <li class="tab col m6"><a href="#dataform">Info Tambahan</a></li>
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
                            <div id="dataform" class="col s12">
                                <h5 class="center">Daftar Info Tambahan</h5>
                                <p class="mt-2 mb-2">
                                    <table class="bordered">
                                        <thead>
                                            <tr>
                                                <th width="30%" class="center">Judul</th>
                                                <th width="60%" class="center">Keterangan</th>
                                                <th width="10%" class="center">Hapus</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-info">
                                            <tr id="last-row-info">
                                                <td colspan="3" class="center">
                                                    <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addInfo()" href="javascript:void(0);">
                                                        <i class="material-icons left">add</i> Tambah Informasi
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

<div id="modal2" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;min-width:100%;max-width:100%;">
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

<div id="modal3" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;min-width:80%;max-width:100%;">
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
                                        <thead style="position:sticky;top: -25px !important;background-color:rgb(176, 212, 212) !important;">
                                            <tr>
                                                <th width="40%" class="center" rowspan="3">Menu</th>
                                                <th width="60%" class="center" colspan="4">Akses</th>
                                            </tr>
                                            <tr>
                                                <th width="15%" class="center">View</th>
                                                <th width="15%" class="center">Create/Update</th>
                                                <th width="15%" class="center">Delete</th>
                                                <th width="15%" class="center">Void</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-menu">
                                            @foreach($menu as $m)
                                                <tr>
                                                    <td>
                                                        {{ $m->name }}
                                                    </td>
                                                    <td>
                                                        @if (!$m->childHasChild())
                                                            <label>
                                                                <input type="checkbox" class="checkboxView" onclick="checkAll(this,{{ $m->id }},'view')"/>
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
                                                </tr>
                                                @if($m->sub()->exists())
                                                    @foreach($m->sub()->where('status','1')->oldest('order')->get() as $msub)
                                                        @if($msub->sub()->exists())
                                                            <tr>
                                                                <td>
                                                                    {!! '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$msub->name !!}
                                                                </td>
                                                                <td>
                                                                    <label>
                                                                        <input type="checkbox" class="checkboxView" onclick="checkAll(this,{{ $msub->id }},'view')"/>
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
                                                            </tr>
                                                            @foreach($msub->sub()->where('status','1')->oldest('order')->get() as $msub2)
                                                                @if($msub2->sub()->exists())
    
                                                                @else
                                                                    <tr>
                                                                        <td>
                                                                            {!! '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$msub2->name !!}
                                                                        </td>
                                                                        <td class="center">
                                                                            <label>
                                                                                <input type="checkbox" name="checkboxView[]" id="checkboxView{{ $msub2->id }}" value="{{ $msub2->id }}" data-parent="{{ $msub2->parentsub->id }}"/>
                                                                                <span>Pilih</span>
                                                                            </label>
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
                                                                        <input type="checkbox" name="checkboxView[]" id="checkboxView{{ $msub->id }}" value="{{ $msub->id }}" data-parent="{{ $msub->parentsub->id }}"/>
                                                                        <span>Pilih</span>
                                                                    </label>
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
                                                                <input type="checkbox" name="checkboxView[]" id="checkboxView{{ $m->id }}" value="{{ $m->id }}" data-parent="{{ $m->parentsub->id }}"/>
                                                                <span>Pilih</span>
                                                            </label>
                                                        </td>
                                                        <td class="center">
                                                            <label>
                                                                <input type="checkbox" name="checkboxUpdate[]" id="checkboxUpdate{{ $m->id }}" value="{{ $m->id }}" data-parent="{{ $m->parentsub->id }}"/>
                                                                <span>Pilih</span>
                                                            </label>
                                                        </td>
                                                        <td class="center">
                                                            <label>
                                                                <input type="checkbox" name="checkboxDelete[]" id="checkboxDelete{{ $m->id }}" value="{{ $m->id }}" data-parent="{{ $m->parentsub->id }}"/>
                                                                <span>Pilih</span>
                                                            </label>
                                                        </td>
                                                        <td class="center">
                                                            <label>
                                                                <input type="checkbox" name="checkboxVoid[]" id="checkboxVoid{{ $m->id }}" value="{{ $m->id }}" data-parent="{{ $m->parentsub->id }}"/>
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
    var arrgroup = @json($group);
    var tempuser = 0;
    $(function() {
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        

        loadDataTable();
        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
        });
        
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
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
                $('#type').val('1');
                $('#province_id,#city_id,#country_id').empty();
                M.updateTextFields();
                $('.row_bank').remove();
                $('.row_info').remove();
                refreshGroup();
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
                        loadingOpen('.modal-content');
                    },
                    success: function(response) {
                        loadingClose('.modal-content');

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
							<div class="col s3 center" id="picture` + val.code + `">
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

    function rowDetail(id, element) {
        var content = '';
        $.ajax({
            url: '{{ Request::url() }}/row_detail',
            type: 'GET',
            async: false,
            data: {
                id: id
            },
            success: function(response) {
                var tr    = $(element).closest('tr');
                var badge = tr.find('button.btn-floating');
                var icon  = tr.find('i');
                var row   = table.row(tr);

                if(row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                    badge.first().removeClass('red');
                    badge.first().addClass('green');
                    icon.first().html('add');
                } else {
                    row.child(response).show();
                    tr.addClass('shown');
                    badge.first().removeClass('green');
                    badge.first().addClass('red');
                    icon.first().html('remove');
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

    function addBank(){
        var count = $('select[name^="arr_bank"]').length;
        $('#last-row-bank').before(`
            <tr class="row_bank">
                <td>
                    <select class="browser-default bank-array" id="arr_bank` + count + `" name="arr_bank[]"></select>
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
                        <input class="with-gap" name="check" type="radio" value="` + count + `">
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
        $('#last-row-info').before(`
            <tr class="row_info">
                <td>
                    <input name="arr_title[]" type="text" placeholder="Judul informasi tambahan">
                </td>
                <td class="center">
                    <input name="arr_content[]" type="text" placeholder="Isi informasi tambahan">
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-info" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
    }

    function loadDataTable() {
		window.table = $('#datatable_serverside').DataTable({
            "responsive": false,
            "scrollX": true,
            "stateSave": true,
            "serverSide": true,
            "deferRender": true,
            "destroy": true,
            "iDisplayInLength": 10,
            "order": [[0, 'asc']],
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    status : $('#filter_status').val(),
                    type : $('#filter_type').val()
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
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'right-align' },
            ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle',
                'selectNone' 
            ],
            language: {
                buttons: {
                    selectNone: "Hapus pilihan"
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
            $('.employee_inputs').show();
        }else{
            $('.other_inputs').show();
            $('.employee_inputs').hide();
        }
    }

    function save(){
			
        var formData = new FormData($('#form_data')[0]);
        
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
                $('#phone').val(response.phone);
                $('#email').val(response.email);
                $("#address").val(response.address);
                $('#type').val(response.type).trigger('change').formSelect();

                refreshGroup();
                
                $('#province_id,#city_id,#country_id').empty();

                $('#province_id').append(`
                    <option value="` + response.province_id + `">` + response.province_name + `</option>
                `);
                $('#city_id').append(`
                    <option value="` + response.city_id + `">` + response.city_name + `</option>
                `);

                $('#country_id').append(`
                    <option value="` + response.country_id + `">` + response.country_name + `</option>
                `);

                $('#tax_id').val(response.tax_id);
                $('#tax_name').val(response.tax_name);
                $('#tax_address').val(response.tax_address);
                $('#id_card').val(response.id_card);
                $('#id_card_address').val(response.id_card_address);
                $('#gender').val(response.gender).formSelect();
                $('#group_id').val(response.group_id).formSelect();

                if(response.type == '1'){
                    $('#company_id').val(response.company_id).formSelect();
                    $('#place_id').val(response.place_id).formSelect();
                    $('#department_id').val(response.department_id).formSelect();
                    $('#position_id').val(response.position_id).formSelect();
                    $('#married_status').val(response.married_status).formSelect();
                    $('#married_date').val(response.married_date);
                    $('#children').val(response.children);
                }else{
                    $('#pic').val(response.pic);
                    $('#pic_no').val(response.pic_no);
                    $('#office_no').val(response.office_no);
                    $('#limit_credit').val(response.limit_credit);
                    $('#top').val(response.top);
                    $('#top_internal').val(response.top_internal);
                }

                $('.row_bank').remove();

                if(response.banks.length > 0){
                    $.each(response.banks, function(i, val) {
                        $('#last-row-bank').before(`
                            <tr class="row_bank">
                                <td>
                                    <select class="browser-default bank-array" id="arr_bank` + i + `" name="arr_bank[]"></select>
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
                        $('#last-row-info').before(`
                            <tr class="row_info">
                                <td>
                                    <input name="arr_title[]" type="text" placeholder="Judul informasi tambahan" value="` + val.title + `">
                                </td>
                                <td class="center">
                                    <input name="arr_content[]" type="text" placeholder="Isi informasi tambahan" value="` + val.content + `">
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-info" href="javascript:void(0);">
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
            console.log(poin);
           
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
        
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status + "&type=" + type;
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