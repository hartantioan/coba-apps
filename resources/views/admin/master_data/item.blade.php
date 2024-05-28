<style>
    .select2-container--default .select2-selection--multiple, .select2-container--default.select2-container--focus .select2-selection--multiple {
        height: auto !important;
    }

    .select-wrapper, .select2-container {
        height:3.6rem !important;
    }

    table > thead > tr > th {
        font-size: 13px !important;
    }

    table.bordered th {
        padding: 5px !important;
    }

    .select2-container {
        min-width: 200px !important;
    }

    .modal {
        top:0px !important;
    }

    #modal3 {
        top:50px !important;
    }

    .form-control-feedback {
        right:0px !important;
    }
    body.tab-active input:focus {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .modal-content .select2.tab-active {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .input-field label.active {
        color:black;
    }

    label {
        color: black;
    }
</style>
<!-- BEGIN: Page Main-->
<div id="main">
    <div class="row">
        <div class="pt-3 pb-1" id="breadcrumbs-wrapper">
            <!-- Search for small screen-->
            <div class="container">
                <div class="row">
                    <div class="col s12 m6 l6">
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
                    <div class="col s12 m6 l6">
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="printBarcode();">
                            <i class="material-icons hide-on-med-and-up">graphic_eq</i>
                            <span class="hide-on-small-onl">Barcode</span>
                            <i class="material-icons right">graphic_eq</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="print();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">Rekap</span>
                            <i class="material-icons right">local_printshop</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="exportExcel();">
                            <i class="material-icons hide-on-med-and-up">view_list</i>
                            <span class="hide-on-small-onl">Excel</span>
                            <i class="material-icons right">view_list</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3 modal-trigger" href="#modal2">
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
                            <ul class="collapsible collapsible-accordion">
                                <li>
                                    <div class="collapsible-header"><i class="material-icons">filter_list</i> FILTER</div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col m4 s12 ">
                                                <label for="filter_status" style="font-size:1rem;">Filter Status :</label>
                                                <div class="input-field col s12">
                                                    <select class="form-control" id="filter_status" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        <option value="1">Aktif</option>
                                                        <option value="2">Non-Aktif</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s12 ">
                                                <label for="filter_type" style="font-size:1rem;">Filter Tipe :</label>
                                                <div class="input-field col s12">
                                                    <select class="select2 browser-default" multiple="multiple" id="filter_type" name="filter_type" onchange="loadDataTable()">
                                                        <option value="" disabled>Semua</option>
                                                        <option value="1">Item Stok</option>
                                                        <option value="2">Item Penjualan</option>
                                                        <option value="3">Item Pembelian</option>
                                                        <option value="4">Item Service</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s12 ">
                                                <label for="filter_group" style="font-size:1rem;">Filter Group :</label>
                                                <div class="input-field col s12">
                                                    <select class="select2 browser-default" multiple="multiple" id="filter_group" name="filter_group" onchange="loadDataTable()">
                                                        @foreach($group->whereNull('parent_id') as $c)
                                                            @if(!$c->childSub()->exists())
                                                                <option value="{{ $c->id }}"> - {{ $c->name }}</option>
                                                            @else
                                                                <optgroup label=" - {{ $c->code.' - '.$c->name }}">
                                                                @foreach($c->childSub as $bc)
                                                                    @if(!$bc->childSub()->exists())
                                                                        <option value="{{ $bc->id }}"> -  - {{ $bc->name }}</option>
                                                                    @else
                                                                        <optgroup label=" -  - {{ $bc->code.' - '.$bc->name }}">
                                                                            @foreach($bc->childSub as $bcc)
                                                                                @if(!$bcc->childSub()->exists())
                                                                                    <option value="{{ $bcc->id }}"> -  -  - {{ $bcc->name }}</option>
                                                                                @else
                                                                                    <optgroup label=" -  -  - {{ $bcc->code.' - '.$bcc->name }}">
                                                                                        @foreach($bcc->childSub as $bccc)
                                                                                            @if(!$bccc->childSub()->exists())
                                                                                                <option value="{{ $bccc->id }}"> -  -  -  - {{ $bccc->name }}</option>
                                                                                            @else
                                                                                                <optgroup label=" -  -  -  - {{ $bccc->code.' - '.$bccc->name }}">
                                                                                                    @foreach($bccc->childSub as $bcccc)
                                                                                                        @if(!$bcccc->childSub()->exists())
                                                                                                            <option value="{{ $bcccc->id }}"> -  -  -  -  - {{ $bcccc->name }}</option>
                                                                                                        @endif
                                                                                                    @endforeach
                                                                                                </optgroup>
                                                                                            @endif
                                                                                        @endforeach
                                                                                    </optgroup>
                                                                                @endif
                                                                            @endforeach
                                                                        </optgroup>
                                                                    @endif
                                                                @endforeach
                                                                </optgroup>
                                                            @endif
                                                    @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <div class="card">
                                <div class="card-content">
                                    <div class="row">
                                        <div class="col s12">
                                            <h4 class="card-title">List Data</h4>
                                            
                                        </div>
                                        <div class="col s12">
                                            @if ($itemsh == 1)
                                                <input type="hidden" id="adaSh" name="adaSh">
                                                <a class="btn btn-floating waves-effect waves-light red darken-4 breadcrumbs-btn right" href="javascript:void(0);" onclick="filterShade()">
                                                    <i class="material-icons hide-on-med-and-up">no shade</i>
                                            
                                                    <i class="material-icons right">sim_card_alert</i>
                                                </a>
                                            @endif
                                            @if ($itemex == 1)
                                                <input type="hidden" id="adaUnit" name="adaUnit">
                                                <a class="btn btn-floating waves-effect waves-light red darken-4 breadcrumbs-btn right" href="javascript:void(0);" onclick="filterUnit()">
                                                    <i class="material-icons hide-on-med-and-up">no unit</i>
                                            
                                                    <i class="material-icons right">perm_scan_wifi</i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col s12">
                                            <div class="card-alert card green">
                                                <div class="card-content white-text">
                                                    <p>Info 1 : Silahkan tekan tombol <a href="javascript:void(0);" class="btn-floating mb-1 btn-flat waves-effect waves-light amber darken-3 accent-2 white-text btn-small" data-popup="tooltip" title="Shading Item"><i class="material-icons dp48">devices_other</i></a> untuk melihat jumlah kode shading item (khusus untuk Item Penjualan).</p>
                                                </div>
                                            </div>
                                            <div class="card-alert card red">
                                                <div class="card-content white-text">
                                                    <p>Info 2 : Item yang terpakai pada transaksi, Satuan tidak akan bisa dirubah.</p>
                                                </div>
                                            </div>
                                            <div class="card-alert card purple">
                                                <div class="card-content white-text">
                                                    <p>Info 3 : Silahkan tekan tombol <a href="javascript:void(0);" type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan darken-4 white-text btn-small" data-popup="tooltip" title="Document Relasi"><i class="material-icons dp48">device_hub</i></a> untuk melihat relasi dokumen pada item terpilih.</p>
                                                </div>
                                            </div>
                                            {{-- <div class="card-alert card blue">
                                                <div class="card-content white-text">
                                                    <p>Info : Khusus untuk Item Penjualan, pengguna harus menentukan <b>Tipe, Ukuran, Jenis, Motif, Warna, Grade, dan Brand </b>, dimana Kode Item dan Nama Item akan otomatis diambil dari gabungan 7 komponen tersebut.</p>
                                                </div>
                                            </div> --}}
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
                                                        <th>Kode</th>
                                                        <th>Nama</th>
                                                        <th>Nama Asing</th>
                                                        <th>Grup</th>
                                                        <th>UOM</th>
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

<div id="modal3" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;max-width:90%;min-width:90%;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Shading Item - <b id="text-shading"></b></h4>
                <div class="row">
                    <div class="col s12">
                        <form class="row" id="form_data_shading" onsubmit="return false;">
                            <div class="col s12">
                                <div id="validation_alert_shading" style="display:none;"></div>
                            </div>
                            <div class="col s12">
                                <div class="row">
                                    <div class="input-field col m2 s2">
                                        <input type="hidden" id="tempShading" name="tempShading">
                                        <input id="shading_code" name="shading_code" type="text" placeholder="Kode Shading">
                                        <label class="active" for="shading_code">Kode Shading</label>
                                    </div>
                                    <div class="input-field col m2 s2">
                                        <button class="btn waves-effect waves-light right submit" onclick="saveShading();">Simpan <i class="material-icons right">send</i></button>
                                    </div>
                                    <div class="input-field col m2 s2">
                                        <h6>Daftar Shading</h6>
                                    </div>
                                    <div class="input-field col m6 s12" id="list-shading">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal2" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;max-width:90%;min-width:90%;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Import Excel</h4>
                <div class="col s12">
                    <div id="validation_alertImport" style="display:none;"></div>
                </div>
                <form class="row" action="{{ Request::url() }}/import" method="POST" enctype="multipart/form-data" id="form_dataimport">
                    @csrf
                    <div class="file-field input-field col m6 s12">
                        <div class="btn">
                            <span>Dokumen Excel</span>
                            <input type="file" class="form-control-file" id="fileExcel" name="file">
                        </div>
                        <div class="file-path-wrapper">
                            <input class="file-path validate" type="text">
                        </div>
                    </div>
                    <div class="input-field col m6 s12">
                        <h6>Anda bisa menggunakan fitur upload dokumen excel. Silahkan klik <a href="{{-- {{ asset(Storage::url('format_imports/format_copas_ap_invoice_2.xlsx')) }} --}}{{ Request::url() }}/get_import_excel" target="_blank">disini</a> untuk mengunduh. Untuk Satuan dan Grup Item, silahkan pilih dari dropdown yang tersedia.</h6>
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

<div id="modal1" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;max-width:100%;min-width:90%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Tambah/Edit Item</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12 row">
                        <div class="input-field col s12 m12" id="item-sale-show" style="display:none;border:solid red 3px;border-radius:30px;">
                            <div class="card-alert card green">
                                <div class="card-content white-text">
                                    <p>Info : Kode & nama item akan otomatis terbuat dari gabungan komposisi kode & nama master data dibawah ini.</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-field col m3 s12">
                                    <select class="browser-default" id="type_id" name="type_id" onchange="generateCode();"></select>
                                    <label class="active" for="type_id">Group</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <select class="browser-default" id="size_id" name="size_id" onchange="generateCode();"></select>
                                    <label class="active" for="size_id">Ukuran</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <select class="browser-default" id="variety_id" name="variety_id" onchange="generateCode();"></select>
                                    <label class="active" for="variety_id">Jenis</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <select class="browser-default" id="pattern_id" name="pattern_id" onchange="generateCode();"></select>
                                    <label class="active" for="pattern_id">Motif & Warna</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <select class="browser-default" id="pallet_id" name="pallet_id" onchange="generateCode();"></select>
                                    <label class="active" for="pallet_id">Palet</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <select class="browser-default" id="grade_id" name="grade_id" onchange="generateCode();"></select>
                                    <label class="active" for="grade_id">Grade</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <select class="browser-default" id="brand_id" name="brand_id" onchange="generateCode();"></select>
                                    <label class="active" for="brand_id">Brand</label>
                                </div>
                            </div>
                        </div>
                        <div class="col s12 m8 row">
                            <div class="input-field col s12 m4">
                                <input type="hidden" id="temp" name="temp">
                                <input id="code" name="code" type="text" placeholder="Kode Item">
                                <label class="active" for="code">Kode</label>
                            </div>
                            <div class="input-field col s12 m4">
                                <input id="name" name="name" type="text" placeholder="Nama Item">
                                <label class="active" for="name">Nama</label>
                            </div>
                            <div class="input-field col s12 m4">
                                <input id="other_name" name="other_name" type="text" placeholder="Nama Item (Ex : Spoon)">
                                <label class="active" for="other_name">Nama Item (Bahasa Asing)</label>
                            </div>
                            <div class="input-field col s12 m4">
                                <input id="note" name="note" type="text" placeholder="Keterangan : sparepart, aktiva, tools, etc">
                                <label class="active" for="note">Keterangan</label>
                            </div>
                            <div class="input-field col s12 m4 unit-inputs">
                                <select class="select2 browser-default" id="item_group_id" name="item_group_id">
                                    @foreach($group->whereNull('parent_id') as $c)
                                            @if(!$c->childSub()->exists())
                                                <option value="{{ $c->id }}"> - {{ $c->name }}</option>
                                            @else
                                                <optgroup label=" - {{ $c->code.' - '.$c->name }}">
                                                @foreach($c->childSub as $bc)
                                                    @if(!$bc->childSub()->exists())
                                                        <option value="{{ $bc->id }}"> -  - {{ $bc->name }}</option>
                                                    @else
                                                        <optgroup label=" -  - {{ $bc->code.' - '.$bc->name }}">
                                                            @foreach($bc->childSub as $bcc)
                                                                @if(!$bcc->childSub()->exists())
                                                                    <option value="{{ $bcc->id }}"> -  -  - {{ $bcc->name }}</option>
                                                                @else
                                                                    <optgroup label=" -  -  - {{ $bcc->code.' - '.$bcc->name }}">
                                                                        @foreach($bcc->childSub as $bccc)
                                                                            @if(!$bccc->childSub()->exists())
                                                                                <option value="{{ $bccc->id }}"> -  -  -  - {{ $bccc->name }}</option>
                                                                            @else
                                                                                <optgroup label=" -  -  -  - {{ $bccc->code.' - '.$bccc->name }}">
                                                                                    @foreach($bccc->childSub as $bcccc)
                                                                                        @if(!$bcccc->childSub()->exists())
                                                                                            <option value="{{ $bcccc->id }}"> -  -  -  -  - {{ $bcccc->name }}</option>
                                                                                        @endif
                                                                                    @endforeach
                                                                                </optgroup>
                                                                            @endif
                                                                        @endforeach
                                                                    </optgroup>
                                                                @endif
                                                            @endforeach
                                                        </optgroup>
                                                    @endif
                                                @endforeach
                                                </optgroup>
                                            @endif
                                    @endforeach
                                </select>
                                <label class="active" for="item_group_id">Grup Item</label>
                            </div>
                            <div class="input-field col s12 m4">
                                <input id="tolerance_gr" name="tolerance_gr" type="text" value="0" onkeyup="formatRupiah(this);">
                                <label class="active" for="tolerance_gr">Toleransi Penerimaan Qty Barang (%)</label>
                            </div>
                            <div class="input-field col s12 m4">
                                <div class="switch mb-1">
                                    <label for="is_hide_supplier">Item Top Secret</label>
                                    <label class="right">
                                        Tidak
                                        <input type="checkbox" id="is_hide_supplier" name="is_hide_supplier" value="1">
                                        <span class="lever"></span>
                                        Ya
                                    </label>
                                </div>
                            </div>
                            <div class="input-field col s12 m4">
                                <div class="switch mb-1">
                                    <label for="status">Status</label>
                                    <label class="right">
                                        Non-Active
                                        <input checked type="checkbox" id="status" name="status" value="1">
                                        <span class="lever"></span>
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col s12 m4 row">
                            <div class="input-field col s12" style="margin:0 0 0 0 !important;">
                                <div class="switch">
                                    <label for="is_inventory_item">Item untuk Inventori</label>
                                    <label class="right">
                                        Tidak
                                        <input type="checkbox" id="is_inventory_item" name="is_inventory_item" value="1">
                                        <span class="lever"></span>
                                        Ya
                                    </label>
                                </div>
                            </div>
                            <div class="input-field col s12" style="margin:0 0 0 0 !important;">
                                <div class="switch">
                                    <label for="is_sales_item">Item untuk Penjualan</label>
                                    <label class="right">
                                        Tidak
                                        <input type="checkbox" id="is_sales_item" name="is_sales_item" value="1" onclick="showSalesComposition();">
                                        <span class="lever"></span>
                                        Ya
                                    </label>
                                </div>
                            </div>
                            <div class="input-field col s12" style="margin:0 0 0 0 !important;">
                                <div class="switch">
                                    <label for="is_purchase_item">Item untuk Pembelian</label>
                                    <label class="right">
                                        Tidak
                                        <input type="checkbox" id="is_purchase_item" name="is_purchase_item" value="1">
                                        <span class="lever"></span>
                                        Ya
                                    </label>
                                </div>
                            </div>
                            <div class="input-field col s12" style="margin:0 0 0 0 !important;">
                                <div class="switch">
                                    <label for="is_service">Item untuk Service</label>
                                    <label class="right">
                                        Tidak
                                        <input type="checkbox" id="is_service" name="is_service" value="1">
                                        <span class="lever"></span>
                                        Ya
                                    </label>
                                </div>
                            </div>
                            <div class="input-field col s12" style="margin:0 0 0 0 !important;">
                                <div class="switch">
                                    <label for="is_production">Item untuk Produksi</label>
                                    <label class="right">
                                        Tidak
                                        <input type="checkbox" id="is_production" name="is_production" value="1">
                                        <span class="lever"></span>
                                        Ya
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col s12 m7 row">
                            <div class="col s12">
                                <div class="input-field col s12 unit-inputs">
                                    <select class="select2 browser-default" id="uom_unit" name="uom_unit" onchange="getUnitStock();">
                                        <option value="">--Silahkan pilih--</option>
                                        @foreach ($unit as $row)
                                            <option value="{{ $row->id }}" data-code="{{ $row->code }}">{{ $row->code.' - '.$row->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="active" for="uom_unit">Satuan Stock</label>
                                </div>
                            </div>
                            <div class="col s12">
                                <div class="center">
                                    <h6>Satuan Konversi</h6>
                                </div>
                                <table class="bordered">
                                    <thead>
                                        <tr>
                                            <th class="center" width="10%">Satuan</th>
                                            <th class="center">Konversi</th>
                                            <th class="center">Jual</th>
                                            <th class="center">Beli</th>
                                            <th class="center">Default</th>
                                            <th class="center">Hapus</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body-unit">
                                        <tr id="empty-unit">
                                            <td colspan="6" class="center">Silahkan tambahkan satuan konversi</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="6" class="center">
                                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addUnit();" href="javascript:void(0);">
                                                    <i class="material-icons left">add</i> Tambah
                                                </a>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="col s12 m5">
                            <div class="center">
                                <h6>Stok Buffer (Satuan Stok/terkecil)</h6>
                            </div>
                            <table class="bordered">
                                <thead>
                                    <tr>
                                        <th class="center">Plant</th>
                                        <th class="center">Minimum Stok</th>
                                        <th class="center">Maksimum Stok</th>
                                    </tr>
                                </thead>
                                <tbody id="body-buffer">
                                    @foreach($place as $row)
                                        <input name="arr_place_buffer[]" type="hidden" value="{{ $row->id }}">
                                        <td>
                                            {{ $row->code }}
                                        </td>
                                        <td>
                                            <input name="arr_min_buffer[]" id="arr_min_buffer{{ $row->id }}" type="text" value="0,00" onkeyup="formatRupiahNoMinus(this)">
                                        </td>
                                        <td>
                                            <input name="arr_max_buffer[]" id="arr_max_buffer{{ $row->id }}" type="text" value="0,00" onkeyup="formatRupiahNoMinus(this)">
                                        </td>
                                    @endforeach
                                </tbody>
                            </table>
                            <br>
                            <hr>
                            <div class="center mt-3">
                                <h6>Isi jika ada pengecekan QC</h6>
                            </div>
                            <div class="row">
                                <div class="input-field col s12 m12">
                                    <div class="switch mb-1">
                                        <label for="status">Pengecekan QC</label>
                                        <label class="right">
                                            Tidak
                                            <input type="checkbox" id="is_quality_check" name="is_quality_check" value="1" onclick="showQcParameter();">
                                            <span class="lever"></span>
                                            Ya
                                        </label>
                                    </div>
                                </div>
                                <div class="col s12 hide" id="quality_parameters">
                                    <table class="bordered">
                                        <thead>
                                            <tr>
                                                <th class="center">Nama/Keterangan</th>
                                                <th class="center">Satuan</th>
                                                <th class="center">Mengurangi Qty?</th>
                                                <th class="center">Hapus</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-parameter">
                                            <tr id="empty-parameter">
                                                <td colspan="4" class="center">Silahkan tambahkan parameter</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="4" class="center">
                                                    <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addParameter();" href="javascript:void(0);">
                                                        <i class="material-icons left">add</i> Tambah
                                                    </a>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
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
        <button class="btn waves-effect waves-light mr-1" onclick="save();">Simpan <i class="material-icons right">send</i></button>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
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
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal7d" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="show_relation_table">

            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
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
    var selected = [], arrCode = [], arrName = [], mainUnit = '';
    
    $(function() {
        
        M.Modal.prototype._handleFocus = function (e) {
            if (!this.el.contains(e.target) && this._nthModalOpened === M.Modal._modalsOpen) {
                var s2 = 'select2-search__field';
                if (e.target.className.indexOf(s2)<0) {
                    this.el.focus();
                }
            }
        };
        
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
        });

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
                    loadingOpen('.modal-content');
                    $('#validation_alertImport').hide();
                    $('#validation_alertImport').html('');
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
                                            <p> ` +val+`</p>
                                        </div>
                                        <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                    </div>
                                `);
                        });
                    } else {
                        console.log(response);
                    }
                    loadingClose('.modal-content');
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

        $('#modal2').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onCloseEnd: function(modal, trigger){
                $('#form_dataimport')[0].reset();
            }
        });

        $('#modal3').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onCloseEnd: function(modal, trigger){
                $('#text-shading').text('');
                $('#form_data_shading')[0].reset();
                $('#tempShading').val('');
                $('#list-shading').html('');
                loadDataTable();
            }
        });
        
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
                $('#code').focus();
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#uom_unit').val('').trigger('change');
                M.updateTextFields();
                $('.stock-unit').text('-');
                $('#type_id,#size_id,#variety_id,#pattern_id,#pallet_id,#grade_id,#brand_id').empty();
                $('#item-sale-show').hide();
                arrCode = [];
                arrName = [];
                $('#body-unit').empty().append(`
                    <tr id="empty-unit">
                        <td colspan="6" class="center">Silahkan tambahkan satuan konversi</td>
                    </tr>
                `);
                $('.unit-inputs').css('pointer-events','auto');
                $("#item_group_id").val($("#item_group_id option:first").val()).trigger('change');
                $('#temp').val('');
                $('#code,#name,#other_name').prop('readonly',false);
                $('#body-parameter').empty().append(`
                    <tr id="empty-parameter">
                        <td colspan="4" class="center">Silahkan tambahkan parameter</td>
                    </tr>
                `);
                $('#quality_parameters').addClass('hide');
            }
        });

        $('#modal7d').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#show_relation_table').empty();
            }
        });

        $("#item_group_id").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        $("#filter_type").select2({
            placeholder: "Kosong untuk semua tipe.",
            dropdownAutoWidth: true,
            width: '100%',
        });

        $('#datatable_serverside tbody').on('click', 'tr', function () {
            var poin = $(this).find('td:nth-child(2)').text().trim();
            var index = $.inArray(poin, selected);
            if ( index === -1 ) {
                selected.push(poin);
            } else {
                selected.splice( index, 1 );
            }
        });

        $('.buttons-select-all[aria-controls="datatable_serverside"]').on('click', function (e) {
            selectDeselectRow();
        });
        
        $('.buttons-select-none[aria-controls="datatable_serverside"]').on('click', function (e) {
            selectDeselectRow();
        });

        select2ServerSide('#type_id', '{{ url("admin/select2/type") }}');
        select2ServerSide('#size_id', '{{ url("admin/select2/size") }}');
        select2ServerSide('#variety_id', '{{ url("admin/select2/variety") }}');
        select2ServerSide('#pattern_id', '{{ url("admin/select2/pattern") }}');
        select2ServerSide('#pallet_id', '{{ url("admin/select2/pallet") }}');
        select2ServerSide('#grade_id', '{{ url("admin/select2/grade") }}');
        select2ServerSide('#brand_id', '{{ url("admin/select2/brand") }}');

        /* $('.select2').each(function () {
            $(this).select2({
                dropdownParent: $(this).parent(),
            });
        }); */
        
        $(document).on('select2:close', '.select2', function (e) {
            var evt = "scroll.select2";
            $(e.target).parents().off(evt);
            $(window).off(evt);
        });

        $('#body-unit').on('click', '.delete-data-unit', function() {
            $(this).closest('tr').remove();
            if($('.row_unit').length == 0){
                $('#body-unit').append(`
                    <tr id="empty-unit">
                        <td colspan="6" class="center">Silahkan tambahkan satuan konversi</td>
                    </tr>
                `);
            }
        });

        $('#body-parameter').on('click', '.delete-data-parameter', function() {
            $(this).closest('tr').remove();
            if($('.row_parameter').length == 0){
                $('#body-parameter').append(`
                    <tr id="empty-parameter">
                        <td colspan="4" class="center">Silahkan tambahkan parameter</td>
                    </tr>
                `);
            }
        });
    });

    function showQcParameter(){
        if($('#is_quality_check').is(':checked')){
            $('#quality_parameters').removeClass('hide');
        }else{
            $('#quality_parameters').addClass('hide');
            $('.row_parameter').remove();
        }
    }

    function addParameter(){
        if($('#empty-parameter').length > 0){
            $('#empty-parameter').remove();
        }
        $('#body-parameter').append(`
            <tr class="row_parameter">
                <td>
                    <input name="arr_name_parameter[]" type="text">
                </td>
                <td>
                    <input name="arr_unit_parameter[]" type="text">
                </td>
                <td class="center-align">
                    <label>
                        <input type="checkbox" name="arr_is_affect_qty[]" value="1">
                        <span>&nbsp;</span>
                    </label>
                </td>
                <td class="center-align">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-parameter" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
    }

    function addUnit(){
        if($('#empty-unit').length > 0){
            $('#empty-unit').remove();
        }
        let unit = $("#uom_unit").select2().find(":selected").data("code") ? $("#uom_unit").select2().find(":selected").data("code") : '-';
        let count = makeid(10);
        $('#body-unit').append(`
            <tr class="row_unit">
                <td class="unit-inputs">
                    <select class="select2 browser-default" id="arr_unit` + count + `" name="arr_unit[]">
                        @foreach ($unit as $row)
                            <option value="{{ $row->id }}">{{ $row->code.' - '.$row->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="unit-inputs">
                    <div class="input-field">
                        <input name="arr_conversion[]" onfocus="emptyThis(this);" type="text" value="0" onkeyup="formatRupiahNoMinus(this)">
                        <div class="form-control-feedback stock-unit">` + unit + `</div>
                    </div>
                </td>
                <td class="center-align unit-inputs">
                    <label>
                        <input type="checkbox" id="arr_sell_unit` + count + `" name="arr_sell_unit[]" value="1">
                        <span>&nbsp;</span>
                    </label>
                </td>
                <td class="center-align unit-inputs">
                    <label>
                        <input type="checkbox" id="arr_buy_unit` + count + `" name="arr_buy_unit[]" value="1">
                        <span>&nbsp;</span>
                    </label>
                </td>
                <td class="center-align unit-inputs">
                    <label>
                        <input type="radio" id="arr_default` + count + `" name="arr_default" value="1">
                        <span>&nbsp;</span>
                    </label>
                </td>
                <td class="center-align unit-inputs">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-unit" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        $('select[name="arr_unit[]"]').select2({
            dropdownAutoWidth: true,
            width: '100%',
        });
    }

    function getUnitStock(){
        if($('#uom_unit').val()){
            $('.stock-unit').text($("#uom_unit").select2().find(":selected").data("code"));
        }else{
            $('.stock-unit').text('-');
        }
    }

    function shading(id,name){
        $('#text-shading').text(name);
        $('#tempShading').val(id);
        $('#modal3').modal('open');
        refreshShading(id);
    }

    function refreshShading(id){
        $.ajax({
            url: '{{ Request::url() }}/show_shading',
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

                $('#list-shading').html('');
                
                if(response.shadings.length > 0){
                    $.each(response.shadings, function(i, val) {
                        $('#list-shading').append(`
                            <div class="chip gradient-45deg-purple-deep-orange white-text" style="font-size: 15px !important;line-height: 30px !important;font-weight: 700 !important;">
                                ` + val.code + `
                                <i class="material-icons close" onclick="destroyShading(` + val.id + `,` + val.item_id + `,this);return false;">close</i>
                            </div>
                        `);
                    });
                    $('.chip > .close').click(function() {
                        return false; 
                    });
                }else{
                    $('#list-shading').html(`
                        <div class="card-alert card red" style="margin: 0 0 0 0 !important;">
                            <div class="card-content white-text">
                                <p>Shading tidak ditemukan.</p>
                            </div>
                            <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                    `);
                }

                $('.modal-content').scrollTop(0);
                $('#shading_code').focus();
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

    function showSalesComposition(){
        if($('#is_sales_item').is(':checked')){
            $('#item-sale-show').show();
        }else{
            $('#item-sale-show').hide();
            $('#type_id,#size_id,#variety_id,#pattern_id,#pallet_id,#grade_id,#brand_id').empty();
        }
    }

    function generateCode(){
        arrCode = [];
        arrName = [];
        if($('#type_id').val() || $('#size_id').val() || $('#variety_id').val() || $('#pattern_id').val() || $('#pallet_id').val() || $('#grade_id').val() || $('#brand_id').val()){
            if($('#type_id').val()){
                arrCode.push($('#type_id').select2('data')[0].code ? $('#type_id').select2('data')[0].code : $('#type_id').find(":selected").data("code"));
                arrName.push($('#type_id').select2('data')[0].name ? $('#type_id').select2('data')[0].name : $('#type_id').find(":selected").data("name"));
            }
            if($('#size_id').val()){
                arrCode.push($('#size_id').select2('data')[0].code ? $('#size_id').select2('data')[0].code : $('#size_id').find(":selected").data("code"));
                arrName.push($('#size_id').select2('data')[0].name ? $('#size_id').select2('data')[0].name : $('#size_id').find(":selected").data("name"));
            }
            if($('#variety_id').val()){
                arrCode.push($('#variety_id').select2('data')[0].code ? $('#variety_id').select2('data')[0].code : $('#variety_id').find(":selected").data("code"));
                arrName.push($('#variety_id').select2('data')[0].name ? $('#variety_id').select2('data')[0].name : $('#variety_id').find(":selected").data("name"));
            }
            if($('#pattern_id').val()){
                let pattern_code = $('#pattern_id').select2('data')[0].code ? $('#pattern_id').select2('data')[0].code.split('.') : $('#pattern_id').find(":selected").data("code").split('.');
                arrCode.push(pattern_code[1]);
                arrName.push($('#pattern_id').select2('data')[0].name ? $('#pattern_id').select2('data')[0].name : $('#pattern_id').find(":selected").data("name"));
            }
            if($('#pallet_id').val()){
                arrCode.push($('#pallet_id').select2('data')[0].code ? $('#pallet_id').select2('data')[0].code : $('#pallet_id').find(":selected").data("code"));
                arrName.push($('#pallet_id').select2('data')[0].name ? $('#pallet_id').select2('data')[0].name : $('#pallet_id').find(":selected").data("name"));
            }
            if($('#grade_id').val()){
                arrCode.push($('#grade_id').select2('data')[0].code ? $('#grade_id').select2('data')[0].code : $('#grade_id').find(":selected").data("code"));
                arrName.push($('#grade_id').select2('data')[0].name ? $('#grade_id').select2('data')[0].name : $('#grade_id').find(":selected").data("name"));
            }
            if($('#brand_id').val()){
                arrCode.push($('#brand_id').select2('data')[0].code ? $('#brand_id').select2('data')[0].code : $('#brand_id').find(":selected").data("code"));
                arrName.push($('#brand_id').select2('data')[0].name ? $('#brand_id').select2('data')[0].name : $('#brand_id').find(":selected").data("name"));
            }            
            let newCode = arrCode.join('.');
            let newName = arrName.join(' ');
            $('#code').val(newCode);
            $('#name').val(newName);
        }else{
            $('#code,#name').val('');
        }
    }

    function selectDeselectRow(){
        $.map(window.table.rows().nodes(), function (item) {
            if($(item).hasClass('selected')){
                var poin = $(item).find('td:nth-child(2)').text().trim();
                var index = $.inArray(poin, selected);
                if ( index === -1 ) {
                    selected.push(poin);
                }
            }else{
                var poinkuy = $(item).find('td:nth-child(2)').text().trim();
                var indexkuy = $.inArray(poinkuy, selected);
                if ( indexkuy >= 0 ) {
                    selected.splice( indexkuy, 1 );
                }
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

    function documentRelation(data) {
        $.ajax({
            url: '{{ Request::url() }}/document_relation',
            type: 'GET',
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            data: {
                id: data
            },
            success: function(response) {
                $('#modal7d').modal('open');
                $('#show_relation_table').html(response);
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
                    adaUnit : $('#adaUnit').val(),
                    adaShading : $('#adaSh').val(),
                    status : $('#filter_status').val(),
                    'type[]' : $('#filter_type').val(),
                    'group[]' : $('#filter_group').val()
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
                { name: 'code', className: '' },
                { name: 'name', className: '' },
                { name: 'other_name', className: '' },
                { name: 'group', className: '' },
                { name: 'uom', className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
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
            "rowCallback": function( row, data ) {
                if ( $.inArray(data[1], selected) !== -1 ) {
                    this.api().row(row).select();
                }
            }
        });
        $('.dt-buttons').appendTo('#datatable_buttons');
        $('select[name="datatable_serverside_length"]').addClass('browser-default');
	}

    function isDuplicate(arr,val){
        let ada = false;
        for(let i=0;i<arr.length;i++){
            if(arr[i] == val){
                ada = true;
            }
        }
        return ada;
    }

    function save(){
			
        var formData = new FormData($('#form_data')[0]), passed = true, passedSameUnit = true;

        formData.delete("arr_sell_unit[]");
        formData.delete("arr_is_affect_qty[]");
        formData.delete("arr_buy_unit[]");
        formData.delete("arr_default");

        let arrUnit = [];
        $('select[name^="arr_unit[]"]').each(function(index){
            if(!$(this).val()){
                passed = false;
            }else{
                if(isDuplicate(arrUnit,$(this).val())){
                    passedSameUnit = false;
                }
                arrUnit.push($(this).val());
            }
        });
        $('input[name^="arr_conversion[]"]').each(function(index){
            if($(this).val() == '' || parseFloat($(this).val().replaceAll(".", "").replaceAll(",",".")) == 0){
                passed = false;
            }
        });
        $('input[name^="arr_sell_unit[]"]').each(function(index){
            formData.append('arr_sell_unit[]',($(this).is(':checked') ? $(this).val() : ''));
        });
        $('input[name^="arr_buy_unit[]"]').each(function(index){
            formData.append('arr_buy_unit[]',($(this).is(':checked') ? $(this).val() : ''));
        });
        $('input[name^="arr_default"]').each(function(index){
            formData.append('arr_default[]',($(this).is(':checked') ? $(this).val() : ''));
        });
        $('input[name^="arr_is_affect_qty[]"]').each(function(index){
            formData.append('arr_is_affect_qty[]',($(this).is(':checked') ? $(this).val() : ''));
        });
        
        if(passedSameUnit){
            if(passed){
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
                        if(response.status == 200) {
                            $('#parent_id').empty();

                            $.each(response.data, function(i, val) {
                                $('#parent_id').append(val);
                            });

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
                    text: 'Mohon maaf, satuan konversi tidak boleh kosong.',
                    icon: 'error'
                });
            }
        }else{
            swal({
                title: 'Ups!',
                text: 'Mohon maaf, satuan konversi tidak boleh ada yang sama.',
                icon: 'error'
            });
        }
    }

    function saveShading(){
			
        var formData = new FormData($('#form_data_shading')[0]);
        
        $.ajax({
            url: '{{ Request::url() }}/create_shading',
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
                $('#validation_alert_shading').hide();
                $('#validation_alert_shading').html('');
                loadingOpen('.modal-content');
            },
            success: function(response) {
                loadingClose('.modal-content');
                if(response.status == 200) {
                    refreshShading($('#tempShading').val());
                    M.toast({
                        html: response.message
                    });
                    $('#shading_code').val('');
                } else if(response.status == 422) {
                    $('#validation_alert_shading').show();
                    $('.modal-content').scrollTop(0);
                    
                    swal({
                        title: 'Ups! Validation',
                        text: 'Check your form.',
                        icon: 'warning'
                    });

                    $.each(response.error, function(i, val) {
                        $.each(val, function(i, val) {
                            $('#validation_alert_shading').append(`
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

    function successImport(){
        loadDataTable();
        $('#modal2').modal('close');
    }

    function filterUnit(){
        if($('#adaUnit').val()==1){
            $('#adaUnit').val('');
        }else{
            $('#adaUnit').val('{{$itemex}}');
        }
       
        $('#adaSh').val('');
        
        loadDataTable();
    }

    function filterShade(){
        if($('#adaSh').val()==1){
            $('#adaSh').val('');
        }else{
            $('#adaSh').val('{{$itemsh}}');
        }
        
        $('#adaUnit').val('');
        
        loadDataTable();
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

                arrCode = [];
                arrName = [];
                
                $('#temp').val(id);
                $('#code').val(response.code);
                $('#name').val(response.name);
                $('#other_name').val(response.other_name);
                $('#note').val(response.note);
                $('#item_group_id').val(response.item_group_id).trigger('change');
                $('#uom_unit').val(response.uom_unit_id).trigger('change');
                $('#warehouse_id').val(response.warehouses).trigger('change');
                $('#tolerance_gr').val(response.tolerance_gr);
                $('.stock-unit').text(response.uom_code);

                if(response.is_inventory_item == '1'){
                    $('#is_inventory_item').prop( "checked", true);
                }else{
                    $('#is_inventory_item').prop( "checked", false);
                }

                if(response.is_quality_check == '1'){
                    $('#is_quality_check').prop( "checked", true);
                }else{
                    $('#is_quality_check').prop( "checked", false);
                }

                if(response.is_hide_supplier == '1'){
                    $('#is_hide_supplier').prop( "checked", true);
                }else{
                    $('#is_hide_supplier').prop( "checked", false);
                }

                if(response.is_sales_item == '1'){
                    $('#is_sales_item').trigger('click');
                    if(response.type_name){
                        $('#type_id').empty().append(`
                            <option value="` + response.type_id + `" data-code="` + response.type_code + `" data-name="` + response.type_name_real + `">` + response.type_name + `</option>
                        `);
                    }
                    if(response.size_name){
                        $('#size_id').empty().append(`
                            <option value="` + response.size_id + `" data-code="` + response.size_code + `" data-name="` + response.size_name_real + `">` + response.size_name + `</option>
                        `);
                    }
                    if(response.variety_name){
                        $('#variety_id').empty().append(`
                            <option value="` + response.variety_id + `" data-code="` + response.variety_code + `" data-name="` + response.variety_name_real + `">` + response.variety_name + `</option>
                        `);
                    }
                    if(response.pattern_name){
                        $('#pattern_id').empty().append(`
                            <option value="` + response.pattern_id + `" data-code="` + response.pattern_code + `" data-name="` + response.pattern_name_real + `">` + response.pattern_name + `</option>
                        `);
                    }
                    if(response.pallet_name){
                        $('#pallet_id').empty().append(`
                            <option value="` + response.pallet_id + `" data-code="` + response.pallet_code + `" data-name="` + response.pallet_name_real + `">` + response.pallet_name + `</option>
                        `);
                    }
                    if(response.grade_name){
                        $('#grade_id').empty().append(`
                            <option value="` + response.grade_id + `" data-code="` + response.grade_code + `" data-name="` + response.grade_name_real + `">` + response.grade_name + `</option>
                        `);
                    }
                    if(response.brand_name){
                        $('#brand_id').empty().append(`
                            <option value="` + response.brand_id + `" data-code="` + response.brand_code + `" data-name="` + response.brand_name_real + `">` + response.brand_name + `</option>
                        `);
                    }
                }else{
                    $('#is_sales_item').prop( "checked", false);
                }

                if(response.is_purchase_item == '1'){
                    $('#is_purchase_item').prop( "checked", true);
                }else{
                    $('#is_purchase_item').prop( "checked", false);
                }

                if(response.is_service == '1'){
                    $('#is_service').prop( "checked", true);
                }else{
                    $('#is_service').prop( "checked", false);
                }

                if(response.is_production == '1'){
                    $('#is_production').prop( "checked", true);
                }else{
                    $('#is_production').prop( "checked", false);
                }

                if(response.status == '1'){
                    $('#status').prop( "checked", true);
                }else{
                    $('#status').prop( "checked", false);
                }

                if(response.buffers.length > 0){
                    $.each(response.buffers, function(i, val) {
                        $('#arr_min_buffer' + val.place_id).val(val.min_stock);
                        $('#arr_max_buffer' + val.place_id).val(val.max_stock);
                    });
                }

                if(response.units.length > 0){
                    $('#body-unit').empty();

                    $.each(response.units, function(i, val) {
                        let unit = response.uom_code;
                        let count = makeid(10);
                        $('#body-unit').append(`
                            <tr class="row_unit">
                                <td class="unit-inputs">
                                    <select class="select2 browser-default" id="arr_unit` + count + `" name="arr_unit[]">
                                        @foreach ($unit as $row)
                                            <option value="{{ $row->id }}">{{ $row->code.' - '.$row->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="unit-inputs">
                                    <div class="input-field">
                                        <input name="arr_conversion[]" onfocus="emptyThis(this);" type="text" value="` + val.conversion + `" onkeyup="formatRupiahNoMinus(this)">
                                        <div class="form-control-feedback stock-unit">` + unit + `</div>
                                    </div>
                                </td>
                                <td class="center-align unit-inputs">
                                    <label>
                                        <input type="checkbox" id="arr_sell_unit` + count + `" name="arr_sell_unit[]" value="1" ` + (val.is_sell_unit ? 'checked' : '' ) + `>
                                        <span>&nbsp;</span>
                                    </label>
                                </td>
                                <td class="center-align unit-inputs">
                                    <label>
                                        <input type="checkbox" id="arr_buy_unit` + count + `" name="arr_buy_unit[]" value="1" ` + (val.is_buy_unit ? 'checked' : '' ) + `>
                                        <span>&nbsp;</span>
                                    </label>
                                </td>
                                <td class="center-align unit-inputs">
                                    <label>
                                        <input type="radio" id="arr_default` + count + `" name="arr_default" value="1" ` + (val.is_default ? 'checked' : '' ) + `>
                                        <span>&nbsp;</span>
                                    </label>
                                </td>
                                <td class="center-align unit-inputs">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-unit" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                        $('select[name="arr_unit[]"]').select2({
                            dropdownAutoWidth: true,
                            width: '100%',
                        });
                        $('#arr_unit' + count).val(val.unit_id).trigger('change');
                    });
                }

                if(response.parameters.length > 0){
                    $('#quality_parameters').removeClass('hide');

                    $('#body-parameter').empty();

                    $.each(response.parameters, function(i, val) {
                        $('#body-parameter').append(`
                            <tr class="row_parameter">
                                <td>
                                    <input name="arr_name_parameter[]" type="text" value="` + val.name + `">
                                </td>
                                <td>
                                    <input name="arr_unit_parameter[]" type="text" value="` + val.unit + `">
                                </td>
                                <td class="center-align">
                                    <label>
                                        <input ` + (val.is_affect_qty ? 'checked' : '') + ` type="checkbox" name="arr_is_affect_qty[]" value="1">
                                        <span>&nbsp;</span>
                                    </label>
                                </td>
                                <td class="center-align">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-parameter" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                    });
                }

                if(response.used){
                    $('#code,#name,#other_name').prop('readonly',true);
                    $('.unit-inputs').css('pointer-events','none');
                }

                $('.modal-content').scrollTop(0);
                $('#code').focus();
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

    function destroyShading(id,item,element){
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
                    url: '{{ Request::url() }}/destroy_shading',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { id : id },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        loadingOpen('.modal-content');
                    },
                    success: function(response) {
                        loadingClose('.modal-content');
                        refreshShading(item);
                        M.toast({
                            html: response.message
                        });
                        $(element).parent().remove();
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
        
        $.map(selected, function (item) {
            arr_id_temp.push(item);
        });
        
        if(arr_id_temp.length > 0){
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
        }else{
            swal({
                title: 'Ups!',
                text: 'Silahkan pilih item untuk cetak rekap.',
                icon: 'warning'
            });
        }
    }

    function printBarcode(){
        
        var arr_id_temp = [];

        $.map(selected, function (item) {
            arr_id_temp.push(item);
        });

        if(arr_id_temp.length > 0){
            $.ajax({
                url: '{{ Request::url() }}/print_barcode',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    arr_id: arr_id_temp,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('#main');
                },
                success: function(response) {
                    loadingClose('#main');
                    printService.submit({
                        'type': 'INVOICE',
                        'url': response.message
                    })
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
        }else{
            swal({
                title: 'Ups!',
                text: 'Silahkan pilih item untuk cetak barcode.',
                icon: 'warning'
            });
        }
    }

    function exportExcel(){
        var search = window.table.search();
        var status = $('#filter_status').val();
        var type = $('#filter_type').val();
        var group = $('#filter_group').val();
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status + "&type=" + type+ "&group=" + group;
    }

</script>