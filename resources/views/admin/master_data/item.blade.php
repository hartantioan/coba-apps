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

    .modal {
        top:0px !important;
    }

    #modal3 {
        top:50px !important;
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
                                            <div class="col m4 s6 ">
                                                <label for="filter_status" style="font-size:1rem;">Filter Status :</label>
                                                <div class="input-field col s12">
                                                    <select class="form-control" id="filter_status" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        <option value="1">Aktif</option>
                                                        <option value="2">Non-Aktif</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
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
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">List Data</h4>
                                    <div class="row">
                                        <div class="col s12">
                                            <div class="card-alert card green">
                                                <div class="card-content white-text">
                                                    <p>Info : Silahkan tekan tombol <a href="javascript:void(0);" class="btn-floating mb-1 btn-flat waves-effect waves-light amber darken-3 accent-2 white-text btn-small" data-popup="tooltip" title="Shading Item"><i class="material-icons dp48">devices_other</i></a> untuk melihat jumlah kode shading item (khusus untuk Item Penjualan).</p>
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
                                            <table id="datatable_serverside" class="display responsive-table wrap">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Kode</th>
                                                        <th>Nama</th>
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
                                    <div class="input-field col m6 s6" id="list-shading">
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
                        Download format disini : <a href="{{ asset(Storage::url('format_imports/format_item.xlsx')) }}" target="_blank">File</a>
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
                    <div class="col s12">
                        <div class="input-field col s6">
                            <input type="hidden" id="temp" name="temp">
                            <input id="code" name="code" type="text" placeholder="Kode Item">
                            <label class="active" for="code">Kode</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="name" name="name" type="text" placeholder="Nama Item">
                            <label class="active" for="name">Nama</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="note" name="note" type="text" placeholder="Keterangan : sparepart, aktiva, tools, etc">
                            <label class="active" for="note">Keterangan</label>
                        </div>
                        <div class="input-field col s6">
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
                        <div class="col s12">
                            <div class="input-field col s3">
                                <input id="tolerance_gr" name="tolerance_gr" type="text" value="0" onkeyup="formatRupiah(this);">
                                <label class="active" for="tolerance_gr">Toleransi Penerimaan Qty Barang (%)</label>
                            </div>
                            <div class="input-field col s3">
                                <input id="min_stock" name="min_stock" type="text" value="0,000" onkeyup="formatRupiah(this);">
                                <label class="active" for="min_stock">Minimal Stock (Satuan Stock)</label>
                            </div>
                            <div class="input-field col s3">
                                <input id="max_stock" name="max_stock" type="text" value="0,000" onkeyup="formatRupiah(this);">
                                <label class="active" for="max_stock">Maksimal Stock (Satuan Stock)</label>
                            </div>
                            <div class="input-field col s3">
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
                        <div class="col s6 row">
                            <div class="col s12">
                                <div class="input-field col s12">
                                    <select class="select2 browser-default" id="uom_unit" name="uom_unit" onchange="getUnitStock();">
                                        <option value="">--Silahkan pilih--</option>
                                        @foreach ($unit as $row)
                                            <option value="{{ $row->id }}" data-code="{{ $row->code }}">{{ $row->code.' - '.$row->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="active" for="uom_unit">Satuan Stok</label>
                                </div>
                            </div>
                            <div class="col s6 row">
                                <div class="input-field col s12">
                                    <select class="select2 browser-default" id="buy_unit" name="buy_unit">
                                        @foreach ($unit as $row)
                                            <option value="{{ $row->id }}">{{ $row->code.' - '.$row->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="active" for="buy_unit">Satuan Beli</label>
                                </div>
                                <div class="input-field col s12">
                                    <select class="select2 browser-default" id="sell_unit" name="sell_unit">
                                        @foreach ($unit as $row)
                                            <option value="{{ $row->id }}">{{ $row->code.' - '.$row->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="active" for="sell_unit">Satuan Jual</label>
                                </div>
                                <div class="input-field col s12">
                                    <select class="select2 browser-default" id="production_unit" name="production_unit">
                                        @foreach ($unit as $row)
                                            <option value="{{ $row->id }}">{{ $row->code.' - '.$row->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="active" for="production_unit">Satuan Produksi</label>
                                </div>
                                <div class="input-field col s12">
                                    <select class="select2 browser-default" id="pallet_unit" name="pallet_unit">
                                        @foreach ($pallet as $row)
                                            <option value="{{ $row->id }}">{{ $row->code.' - '.$row->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="active" for="pallet_unit">Satuan Pallet</label>
                                </div>
                            </div>
                            <div class="col s6 row">
                                <div class="input-field col s12">
                                    <input id="buy_convert" name="buy_convert" type="text" placeholder="Ex: 1 SAK Beli = ? KG Stok" onkeyup="formatRupiah(this);">
                                    <label class="active" for="buy_convert">Konversi Satuan Beli ke Stok</label>
                                    <div class="form-control-feedback stock-unit">-</div>
                                </div>
                                <div class="input-field col s12">
                                    <input id="sell_convert" name="sell_convert" type="text" placeholder="Ex: 1 Truk Jual = ? KG Stok" onkeyup="formatRupiah(this);">
                                    <label class="active" for="sell_convert">Konversi Satuan Jual ke Stok</label>
                                    <div class="form-control-feedback stock-unit">-</div>
                                </div>
                                <div class="input-field col s12">
                                    <input id="production_convert" name="production_convert" type="text" placeholder="Ex: 1 PCS = ? M2 Stok" onkeyup="formatRupiah(this);">
                                    <label class="active" for="production_convert">Konversi Satuan Produksi ke Stok</label>
                                    <div class="form-control-feedback stock-unit">-</div>
                                </div>
                                <div class="input-field col s12">
                                    <input id="pallet_convert" name="pallet_convert" type="text" placeholder="Ex: 1 Pallet Kayu = ? M2 Stok" onkeyup="formatRupiah(this);">
                                    <label class="active" for="pallet_convert">Konversi Pallet ke Satuan Stok</label>
                                    <div class="form-control-feedback stock-unit">-</div>
                                </div>
                            </div>
                        </div>
                        <div class="input-field col s6">
                            <div class="switch mb-1">
                                <label for="is_inventory_item">Item untuk Inventori</label>
                                <label class="right">
                                    Tidak
                                    <input type="checkbox" id="is_inventory_item" name="is_inventory_item" value="1">
                                    <span class="lever"></span>
                                    Ya
                                </label>
                            </div>
                            <div class="switch mb-1">
                                <label for="is_sales_item">Item untuk Penjualan</label>
                                <label class="right">
                                    Tidak
                                    <input type="checkbox" id="is_sales_item" name="is_sales_item" value="1" onclick="showSalesComposition();">
                                    <span class="lever"></span>
                                    Ya
                                </label>
                            </div>
                            <div class="switch mb-1">
                                <label for="is_purchase_item">Item untuk Pembelian</label>
                                <label class="right">
                                    Tidak
                                    <input type="checkbox" id="is_purchase_item" name="is_purchase_item" value="1">
                                    <span class="lever"></span>
                                    Ya
                                </label>
                            </div>
                            <div class="switch mb-1">
                                <label for="is_service">Item untuk Service</label>
                                <label class="right">
                                    Tidak
                                    <input type="checkbox" id="is_service" name="is_service" value="1">
                                    <span class="lever"></span>
                                    Ya
                                </label>
                            </div>
                        </div>
                        <div class="input-field col s8" id="item-sale-show" style="display:none;">
                            <div class="card-alert card green">
                                <div class="card-content white-text">
                                    <p>Info : Kode & nama item akan otomatis terbuat dari gabungan komposisi kode & nama master data dibawah ini.</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-field col m4 s12">
                                    <select class="browser-default" id="type_id" name="type_id" onchange="generateCode();"></select>
                                    <label class="active" for="type_id">Tipe</label>
                                </div>
                                <div class="input-field col m4 s12">
                                    <select class="browser-default" id="size_id" name="size_id" onchange="generateCode();"></select>
                                    <label class="active" for="size_id">Ukuran</label>
                                </div>
                                <div class="input-field col m4 s12">
                                    <select class="browser-default" id="variety_id" name="variety_id" onchange="generateCode();"></select>
                                    <label class="active" for="variety_id">Jenis</label>
                                </div>
                                <div class="input-field col m4 s12">
                                    <select class="browser-default" id="pattern_id" name="pattern_id" onchange="generateCode();"></select>
                                    <label class="active" for="pattern_id">Motif</label>
                                </div>
                                <div class="input-field col m4 s12">
                                    <select class="browser-default" id="color_id" name="color_id" onchange="generateCode();"></select>
                                    <label class="active" for="color_id">Warna</label>
                                </div>
                                <div class="input-field col m4 s12">
                                    <select class="browser-default" id="grade_id" name="grade_id" onchange="generateCode();"></select>
                                    <label class="active" for="grade_id">Grade</label>
                                </div>
                                <div class="input-field col m4 s12">
                                    <select class="browser-default" id="brand_id" name="brand_id" onchange="generateCode();"></select>
                                    <label class="active" for="brand_id">Brand</label>
                                </div>
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

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div>

<!-- END: Page Main-->
<script>
    var selected = [], arrCode = [], arrName = [];
    
    $(function() {
        
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
                $('#type_id,#size_id,#variety_id,#pattern_id,#color_id,#grade_id,#brand_id').empty();
                /* $('#item-sale-show').hide(); */
                arrCode = [];
                arrName = [];
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
        select2ServerSide('#color_id', '{{ url("admin/select2/color") }}');
        select2ServerSide('#grade_id', '{{ url("admin/select2/grade") }}');
        select2ServerSide('#brand_id', '{{ url("admin/select2/brand") }}');

    });

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
        /* if($('#is_sales_item').is(':checked')){
            $('#item-sale-show').show();
        }else{
            $('#item-sale-show').hide();
        } */
    }

    function generateCode(){
        arrCode = [];
        arrName = [];
        if($('#type_id').val() && $('#size_id').val() && $('#variety_id').val() && $('#pattern_id').val() && $('#color_id').val() && $('#grade_id').val() && $('#brand_id').val()){
            arrCode.push($('#type_id').select2('data')[0].code ? $('#type_id').select2('data')[0].code : $('#type_id').find(":selected").data("code")); 
            arrCode.push($('#size_id').select2('data')[0].code ? $('#size_id').select2('data')[0].code : $('#size_id').find(":selected").data("code"));
            arrCode.push($('#variety_id').select2('data')[0].code ? $('#variety_id').select2('data')[0].code : $('#variety_id').find(":selected").data("code")); 
            arrCode.push($('#pattern_id').select2('data')[0].code ? $('#pattern_id').select2('data')[0].code : $('#pattern_id').find(":selected").data("code")); 
            arrCode.push($('#color_id').select2('data')[0].code ? $('#color_id').select2('data')[0].code : $('#color_id').find(":selected").data("code")); 
            arrCode.push($('#grade_id').select2('data')[0].code ? $('#grade_id').select2('data')[0].code : $('#grade_id').find(":selected").data("code")); 
            arrCode.push($('#brand_id').select2('data')[0].code ? $('#brand_id').select2('data')[0].code : $('#brand_id').find(":selected").data("code"));
            arrName.push($('#type_id').select2('data')[0].name ? $('#type_id').select2('data')[0].name : $('#type_id').find(":selected").data("name"));
            arrName.push($('#size_id').select2('data')[0].name ? $('#size_id').select2('data')[0].name : $('#size_id').find(":selected").data("name")),
            arrName.push($('#variety_id').select2('data')[0].name ? $('#variety_id').select2('data')[0].name : $('#variety_id').find(":selected").data("name"));
            arrName.push($('#pattern_id').select2('data')[0].name ? $('#pattern_id').select2('data')[0].name : $('#pattern_id').find(":selected").data("name"));
            arrName.push($('#color_id').select2('data')[0].name ? $('#color_id').select2('data')[0].name : $('#color_id').find(":selected").data("name"));
            arrName.push($('#grade_id').select2('data')[0].name ? $('#grade_id').select2('data')[0].name : $('#grade_id').find(":selected").data("name"));
            arrName.push($('#brand_id').select2('data')[0].name ? $('#brand_id').select2('data')[0].name : $('#brand_id').find(":selected").data("name"));
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
            "order": [[0, 'asc']],
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    status : $('#filter_status').val(),
                    'type[]' : $('#filter_type').val()
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
                loadingClose('.modal-content');
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
            }
        });
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
                $('#note').val(response.note);
                $('#item_group_id').val(response.item_group_id).trigger('change');
                $('#uom_unit').val(response.uom_unit_id).trigger('change');
                $('#buy_unit').val(response.buy_unit).trigger('change');
                $('#buy_convert').val(response.buy_convert);
                $('#sell_unit').val(response.sell_unit).trigger('change');
                $('#sell_convert').val(response.sell_convert);
                $('#pallet_unit').val(response.pallet_unit).trigger('change');
                $('#pallet_convert').val(response.pallet_convert);
                $('#production_unit').val(response.production_unit).trigger('change');
                $('#production_convert').val(response.production_convert);
                $('#warehouse_id').val(response.warehouses).trigger('change');
                $('#tolerance_gr').val(response.tolerance_gr);
                $('#min_stock').val(response.min_stock);
                $('#max_stock').val(response.max_stock);
                $('.stock-unit').text(response.uom_code);

                if(response.is_inventory_item == '1'){
                    $('#is_inventory_item').prop( "checked", true);
                }else{
                    $('#is_inventory_item').prop( "checked", false);
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
                    if(response.color_name){
                        $('#color_id').empty().append(`
                            <option value="` + response.color_id + `" data-code="` + response.color_code + `" data-name="` + response.color_name_real + `">` + response.color_name + `</option>
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

                if(response.status == '1'){
                    $('#status').prop( "checked", true);
                }else{
                    $('#status').prop( "checked", false);
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
        
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status + "&type=" + type;
    }

</script>