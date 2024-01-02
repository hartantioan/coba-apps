<style>
    .modal {
        top:0px !important;
    }

    .select-wrapper, .select2-container {
        height:3.6rem !important;
    }
    .error-input{
        border: 1px solid red;
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
                    <div class="col s4 m6 l6">
                        
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="printData();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">Print</span>
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
                                    <div class="collapsible-header"><i class="material-icons">filter_list</i> FILTER</div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col m4 s6 ">
                                                <label for="filter_status" style="font-size:1rem;">Filter Status :</label>
                                                <div class="input-field col s12">
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
                                                <label for="start_date" style="font-size:1rem;">Start Date (Tanggal Mulai) :</label>
                                                <div class="input-field col s12">
                                                    <input type="date" id="start_date" name="start_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="finish_date" style="font-size:1rem;">End Date (Tanggal Berhenti) :</label>
                                                <div class="input-field col s12">
                                                    <input type="date" id="finish_date" name="finish_date"  onchange="loadDataTable()">
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
                                    <div class="row mt-2">
                                        <div class="col s12">
                                            <div id="datatable_buttons"></div>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">Refresh</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <table id="datatable_serverside" class="display responsive-table wrap">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="2">#</th>
                                                        <th rowspan="2">Code</th>
                                                        <th rowspan="2">Pengguna</th>
                                                        <th rowspan="2">Perusahaan</th>
                                                        <th colspan="2" class="center-align">Tanggal</th>
                                                        <th rowspan="2">Keterangan</th>
                                                        <th rowspan="2">Dokumen</th>
                                                        <th rowspan="2">Status</th>
                                                        <th rowspan="2">Operasi</th>
                                                    </tr>
                                                    <tr>
                                                        <th>Pengajuan</th>
                                                        <th>Kadaluwarsa</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;">
    <div class="modal-content" style="overflow-x:hidden !important;">
        <div class="row">
            <div class="col s12">
                <h4>Tambah/Edit {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <fieldset>
                            <legend>1. Informasi Utama</legend>
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
                                    <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);">
                                    <label class="active" for="post_date">Tgl. Posting</label>
                                </div>
                                <div class="input-field col m4 s12 step4">
                                    <input id="due_date" name="due_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. posting">
                                    <label class="active" for="due_date">Tgl. Kadaluwarsa</label>
                                </div>
                                <div class="file-field input-field col m4 s12 step6">
                                    <div class="btn">
                                        <span>File</span>
                                        <input type="file" name="file" id="file">
                                    </div>
                                    <div class="file-path-wrapper">
                                        <input class="file-path validate" type="text">
                                    </div>
                                </div>
                                <div class="input-field col m4 s12 step7">
                                    <select class="form-control" id="company_id" name="company_id">
                                        @foreach ($company as $row)
                                            <option value="{{ $row->id }}">{{ $row->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="" for="company_id">Perusahaan</label>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend>2. Material Request (Jika Ada)</legend>
                            <div class="mt-1 mb-1">
                                <b>Tarik data dari Material Request - jika ada stok yang tidak mencukupi maka qty akan diisi dengan selisih permintaan material stok dengan stok saat ini. Hanya item yang telah disetujui akan masuk disini.</b>
                            </div>
                            <div class="row">
                                <div class="input-field col m5 step9">
                                    <select class="browser-default" id="material_request_id" name="material_request_id"></select>
                                    <label class="active" for="material_request_id">Daftar Material Request</label>
                                </div>
                                <div class="col m4 step10">
                                    <a class="waves-effect waves-light cyan btn-small mb-1 mr-1 mt-5" onclick="getMaterialRequest();" href="javascript:void(0);">
                                        <i class="material-icons left">add</i> Material Request
                                    </a>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col m12 s12 step11">
                                    <h6>Hapus untuk bisa diakses pengguna lain : <i id="list-used-data"></i></h6>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset style="min-width: 100%;overflow:auto;">
                            <legend>3. Detail Produk</legend>
                            <div class="row">
                                <div class="col m12 s12 step12">
                                    <p class="mt-2 mb-2">
                                        <table class="bordered" style="width:2000px;" id="table-detail">
                                            <thead>
                                                <tr>
                                                    <th class="center">Item</th>
                                                    <th class="center">Qty</th>
                                                    <th class="center">Satuan PO</th>
                                                    <th class="center">Keterangan 1</th>
                                                    <th class="center">Keterangan 2</th>
                                                    <th class="center">Tgl.Dipakai</th>
                                                    <th class="center">Plant</th>
                                                    <th class="center">Line</th>
                                                    <th class="center">Mesin</th>
                                                    <th class="center">Gudang Tujuan</th>
                                                    <th class="center">Departemen</th>
                                                    <th class="center">Requester</th>
                                                    <th class="center">Proyek</th>
                                                    <th class="center">Hapus</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-item">
                                                <tr class="row_item" data-id="">
                                                    <input type="hidden" name="arr_lookable_type[]" value="">
                                                    <input type="hidden" name="arr_lookable_id[]" value="">
                                                    <td>
                                                        <select class="browser-default item-array" id="arr_item0" name="arr_item[]" onchange="getRowUnit(0)"></select>
                                                    </td>
                                                    <td>
                                                        <input name="arr_qty[]" onfocus="emptyThis(this);" type="text" value="0" onkeyup="formatRupiahNoMinus(this)">
                                                    </td>
                                                    <td class="center">
                                                        <span id="arr_satuan0">-</span>
                                                    </td>
                                                    <td>
                                                        <input name="arr_note[]" type="text" placeholder="Keterangan barang 1...">
                                                    </td>
                                                    <td>
                                                        <input name="arr_note2[]" type="text" placeholder="Keterangan barang 2...">
                                                    </td>
                                                    <td>
                                                        <input name="arr_required_date[]" type="date" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}">
                                                    </td>
                                                    <td>
                                                        <select class="browser-default" id="arr_place0" name="arr_place[]">
                                                            @foreach ($place as $rowplace)
                                                                <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                                                            @endforeach
                                                        </select>    
                                                    </td>
                                                    <td>
                                                        <select class="browser-default" id="arr_line0" name="arr_line[]" onchange="changePlace(this);">
                                                            <option value="">--Kosong--</option>
                                                            @foreach ($line as $rowline)
                                                                <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                                                            @endforeach
                                                        </select>    
                                                    </td>
                                                    <td>
                                                        <select class="browser-default" id="arr_machine0" name="arr_machine[]" onchange="changeLine(this);">
                                                            <option value="">--Kosong--</option>
                                                            @foreach ($machine as $row)
                                                                <option value="{{ $row->id }}" data-line="{{ $row->line_id }}">{{ $row->name }}</option>
                                                            @endforeach    
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="browser-default" id="arr_warehouse0" name="arr_warehouse[]">\
                                                            <option value="">--Silahkan pilih item--</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="browser-default" id="arr_department0" name="arr_department[]">
                                                            <option value="">--Kosong--</option>
                                                            @foreach ($department as $rowdept)
                                                                <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                                                            @endforeach
                                                        </select>    
                                                    </td>
                                                    <td>
                                                        <input name="arr_requester[]" type="text" placeholder="Yang meminta barang / requester" required>
                                                    </td>
                                                    <td>
                                                        <select class="browser-default" id="arr_project0" name="arr_project[]"></select>
                                                    </td>
                                                    <td class="center">
                                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                                            <i class="material-icons">delete</i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <tr id="last-row-item">
                                                    <td colspan="14">
                                                        <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addItem()" href="javascript:void(0);">
                                                            <i class="material-icons left">add</i> Tambah 1
                                                        </a>
                                                        <a class="waves-effect waves-light green btn-small mb-1 mr-1" onclick="addItemFromStock()" href="javascript:void(0);">
                                                            <i class="material-icons left">add</i> Tambah Dari Stok
                                                        </a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </p>
                                </div>
                            </div>
                        </fieldset>
                        <div class="row">
                            <div class="input-field col m4 s12 step13">
                                <input type="hidden" id="temp" name="temp">
                                <textarea id="note" name="note" placeholder="Catatan / Keterangan" rows="1" class="materialize-textarea"></textarea>
                                <label class="active" for="note">Keterangan</label>
                            </div>
                            <div class="col s12 mt-3">
                                <button class="btn waves-effect waves-light right submit step14" onclick="save();">Simpan <i class="material-icons right">send</i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light purple" onclick="startIntro();">Panduan <i class="material-icons right">help_outline</i></button>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Tutup</a>
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
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>
<div id="modal3" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="show_structure">
                <div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;">

                </div>
                <div id="visualisation">
                </div>
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
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat mr-1">Close</a>
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
                                    <th class="center">Item</th>
                                    <th class="center">Satuan</th>
                                    <th class="center">Qty Req.</th>
                                    <th class="center">Qty PO</th>
                                    <th class="center">Qty Gantungan</th>
                                </tr>
                            </thead>
                            <tbody id="body-done"></tbody>
                        </table>
                    </p>
                    <button class="btn waves-effect waves-light right submit" onclick="saveDone();">Simpan <i class="material-icons right">send</i></button>
                </form>
                <p>Info : Item yang tertutup akan dianggap sudah menjadi PO secara keseluruhan, sehingga tidak akan muncul di form Order Pembelian.</p>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal7" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <div id="validation_alert_done" style="display:none;"></div>
                <p class="mt-2 mb-2">
                    <h4>Daftar Item <= Min Stock</h4>
                    <table class="bordered" style="width:100%;">
                        <thead>
                            <tr>
                                <th class="center">Pilih</th>
                                <th class="center">Item</th>
                                <th class="center">Satuan</th>
                                <th class="center">Qty Stok</th>
                                <th class="center">Qty PR</th>
                                <th class="center">Qty PO</th>
                                <th class="center">Min Stok</th>
                                <th class="center">Max Stok</th>
                                <th class="center">Qty Order</th>
                            </tr>
                        </thead>
                        <tbody id="body-stock"></tbody>
                    </table>
                </p>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light right submit" onclick="useStock();">Gunakan <i class="material-icons right">send</i></button>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
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
    $(function() {

        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
            
        });

        loadDataTable();

        window.table.search('{{ $code }}').draw();

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
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#show_detail').empty();
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

        $('#modal7').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) {

            },
            onCloseEnd: function(modal, trigger){
                $('#body-stock').empty();
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
                $('#form_data')[0].reset();
                $('#temp').val('');
                M.updateTextFields();
                $('#project_id,#warehouse_id').empty();
                $('.row_item').remove();
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }
                $('#material_request_id').empty();
                window.onbeforeunload = function() {
                    return null;
                };
                $('.error-input').css('border', '');
                $('.error-input').removeClass('error-input');
            }
        });

        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
        });

        $('#arr_place0,#arr_department0').formSelect();
        select2ServerSide('#arr_item0', '{{ url("admin/select2/purchase_item") }}');
        select2ServerSide('#material_request_id', '{{ url("admin/select2/material_request_pr") }}');
        select2ServerSide('#arr_project0', '{{ url("admin/select2/project") }}');

        $("#table-detail th").resizable({
            minWidth: 100,
        });
    });

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

        // Split the path by slashes and get the last segment
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
                loadingOpen('.modal-content');
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
            "order": [[0, 'asc']],
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    'status' : $('#filter_status').val(),
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
                { name: 'code', className: 'center-align' },
                { name: 'name', className: 'center-align' },
                { name: 'company_id', className: 'center-align' },
                { name: 'date_post', className: 'center-align' },
                { name: 'date_due', className: 'center-align' },
                { name: 'note', className: '' },
                { name: 'document', searchable: false, orderable: false, className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'operation', searchable: false, orderable: false, className: 'center-align' },
            ],
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
        });
        $('.dt-buttons').appendTo('#datatable_buttons');

        $('select[name="datatable_serverside_length"]').addClass('browser-default');
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
                                    ` + val.qty_po + `
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
                
                var formData = new FormData($('#form_data')[0]);

                formData.delete("arr_line[]");
                formData.delete("arr_machine[]");
                formData.delete("arr_project[]");

                $('select[name^="arr_line"]').each(function(index){
                    formData.append('arr_line[]',($(this).val() ? $(this).val() : ''));
                });
                $('select[name^="arr_machine"]').each(function(index){
                    formData.append('arr_machine[]',($(this).val() ? $(this).val() : ''));
                });
                $('select[name^="arr_project[]"]').each(function(index){
                    formData.append('arr_project[]',($(this).val() ? $(this).val() : ''));
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
                        $('input').css('border', 'none');
                        $('input').css('border-bottom', '0.5px solid black');
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
                $('#code_place_id').val(response.code_place_id).attr('readonly',true).formSelect();
                $('#code').val(response.code);
                $('#note').val(response.note);
                $('#post_date').val(response.post_date);
                $('#due_date').val(response.due_date);
                $('#required_date').val(response.required_date);
                $('#due_date').removeAttr('min');
                $('#required_date').removeAttr('min');
                $('#company_id').val(response.company_id).formSelect();

                if(response.details.length > 0){
                    $('.row_item').each(function(){
                        $(this).remove();
                    });

                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        $('#last-row-item').before(`
                            <tr class="row_item" data-id="` + val.reference_id + `">
                                <input type="hidden" name="arr_lookable_type[]" value="` + val.lookable_type + `">
                                <input type="hidden" name="arr_lookable_id[]" value="` + val.lookable_id + `">
                                <td>
                                    <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                                </td>
                                <td>
                                    <input name="arr_qty[]" onfocus="emptyThis(this);" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinusNoMinus(this)">
                                </td>
                                <td class="center">
                                    <span id="arr_satuan` + count + `">` + val.unit + `</span>
                                </td>
                                <td>
                                    <input name="arr_note[]" type="text" placeholder="Keterangan barang 1..." value="` + val.note + `">
                                </td>
                                <td>
                                    <input name="arr_note2[]" type="text" placeholder="Keterangan barang 2..." value="` + val.note2 + `">
                                </td>
                                <td>
                                    <input name="arr_required_date[]" type="date" value="` + val.date + `" min="` + $('#post_date').val() + `">
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
                                        <option value="">--Kosong--</option>
                                        @foreach ($line as $rowline)
                                            <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                                        @endforeach
                                    </select>    
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_machine` + count + `" name="arr_machine[]" onchange="changeLine(this);">
                                        <option value="">--Kosong--</option>
                                        @foreach ($machine as $row)
                                            <option value="{{ $row->id }}" data-line="{{ $row->line_id }}">{{ $row->name }}</option>
                                        @endforeach    
                                    </select>
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]"></select>
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                                        <option value="">--Kosong--</option>
                                        @foreach ($department as $rowdept)
                                            <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                                        @endforeach
                                    </select>    
                                </td>
                                <td>
                                    <input name="arr_requester[]" type="text" placeholder="Yang meminta barang / requester" value="` + val.requester + `" required>
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                        $('#arr_item' + count).append(`
                            <option value="` + val.item_id + `">` + val.item_name + `</option>
                        `);
                        select2ServerSide('#arr_item' + count, '{{ url("admin/select2/purchase_item") }}');
                        $('#arr_warehouse' + count).append(`
                            <option value="` + val.warehouse_id + `">` + val.warehouse_name + `</option>
                        `);
                        $('#arr_place' + count).val(val.place_id);
                        $('#arr_line' + count).val(val.line_id);
                        $('#arr_machine' + count).val(val.machine_id);
                        $('#arr_department' + count).val(val.department_id);
                        if(val.project_id){
                            $('#arr_project' + count).append(`
                                <option value="` + val.project_id + `">` + val.project_name + `</option>
                            `);
                        }
                        select2ServerSide('#arr_project' + count, '{{ url("admin/select2/project") }}');
                    });
                }
                
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

    function duplicate(id){
        swal({
            title: "Apakah anda yakin ingin salin?",
            text: "Pastikan item yang ingin anda salin sudah sesuai!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
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
                        
                        $('#note').val(response.note);
                        $('#post_date').val(response.post_date);
                        $('#due_date').val(response.due_date);
                        $('#required_date').val(response.required_date);
                        $('#due_date').removeAttr('min');
                        $('#required_date').removeAttr('min');
                        $('#company_id').val(response.company_id).formSelect();

                        if(response.project_id){
                            $('#project_id').empty();
                            $('#project_id').append(`
                                <option value="` + response.project_id + `">` + response.project_name + `</option>
                            `);
                        }

                        if(response.details.length > 0){
                            $('.row_item').each(function(){
                                $(this).remove();
                            });

                            $.each(response.details, function(i, val) {
                                var count = makeid(10);
                                $('#last-row-item').before(`
                                    <tr class="row_item" data-id="` + val.reference_id + `">
                                        <input type="hidden" name="arr_lookable_type[]" value="` + val.lookable_type + `">
                                        <input type="hidden" name="arr_lookable_id[]" value="` + val.lookable_id + `">
                                        <td>
                                            <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                                        </td>
                                        <td>
                                            <input name="arr_qty[]" onfocus="emptyThis(this);" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinusNoMinus(this)">
                                        </td>
                                        <td class="center">
                                            <span id="arr_satuan` + count + `">` + val.unit + `</span>
                                        </td>
                                        <td>
                                            <input name="arr_note[]" type="text" placeholder="Keterangan barang 1..." value="` + val.note + `">
                                        </td>
                                        <td>
                                            <input name="arr_note2[]" type="text" placeholder="Keterangan barang 2..." value="` + val.note2 + `">
                                        </td>
                                        <td>
                                            <input name="arr_required_date[]" type="date" value="` + val.date + `" min="` + $('#post_date').val() + `">
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
                                                <option value="">--Kosong--</option>
                                                @foreach ($line as $rowline)
                                                    <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                                                @endforeach
                                            </select>    
                                        </td>
                                        <td>
                                            <select class="browser-default" id="arr_machine` + count + `" name="arr_machine[]" onchange="changeLine(this);">
                                                <option value="">--Kosong--</option>
                                                @foreach ($machine as $row)
                                                    <option value="{{ $row->id }}" data-line="{{ $row->line_id }}">{{ $row->name }}</option>
                                                @endforeach    
                                            </select>
                                        </td>
                                        <td>
                                            <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]"></select>
                                        </td>
                                        <td>
                                            <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                                                <option value="">--Kosong--</option>
                                                @foreach ($department as $rowdept)
                                                    <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                                                @endforeach
                                            </select>    
                                        </td>
                                        <td>
                                            <input name="arr_requester[]" type="text" placeholder="Yang meminta barang / requester" value="` + val.requester + `" required>
                                        </td>
                                        <td>
                                            <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                                        </td>
                                        <td class="center">
                                            <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                                <i class="material-icons">delete</i>
                                            </a>
                                        </td>
                                    </tr>
                                `);
                                $('#arr_item' + count).append(`
                                    <option value="` + val.item_id + `">` + val.item_name + `</option>
                                `);
                                select2ServerSide('#arr_item' + count, '{{ url("admin/select2/purchase_item") }}');
                                $('#arr_warehouse' + count).append(`
                                    <option value="` + val.warehouse_id + `">` + val.warehouse_name + `</option>
                                `);
                                $('#arr_place' + count).val(val.place_id);
                                $('#arr_line' + count).val(val.line_id);
                                $('#arr_machine' + count).val(val.machine_id);
                                $('#arr_department' + count).val(val.department_id);
                                if(val.project_id){
                                    $('#arr_project' + count).append(`
                                        <option value="` + val.project_id + `">` + val.project_name + `</option>
                                    `);
                                }
                                select2ServerSide('#arr_project' + count, '{{ url("admin/select2/project") }}');
                            });
                        }
                        
                        $('.modal-content').scrollTop(0);
                        $('#note').focus();
                        M.updateTextFields();

                        $('#code_place_id').val(response.code_place_id).formSelect().trigger('change');
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

    function getRowUnit(val){
        $("#arr_warehouse" + val).empty();
        if($("#arr_item" + val).val()){
            $('#arr_satuan' + val).text($("#arr_item" + val).select2('data')[0].buy_unit);
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
        }else{
            $("#arr_warehouse" + val).append(`
                <option value="">--Silahkan pilih item--</option>
            `);
        }
    }

    function getMaterialRequest(){
        if($('#material_request_id').val()){
            let datakuy = $('#material_request_id').select2('data')[0];
            $.ajax({
                url: '{{ Request::url() }}/send_used_data',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: $('#material_request_id').val(),
                    type: datakuy.table,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    loadingClose('.modal-content');

                    if(response.status == 500){
                        swal({
                            title: 'Ups!',
                            text: response.message,
                            icon: 'warning'
                        });
                    }else{

                        $('#list-used-data').append(`
                            <div class="chip purple darken-4 gradient-shadow white-text">
                                ` + datakuy.code + `
                                <i class="material-icons close data-used" onclick="removeUsedData('` + datakuy.table + `','` + $('#material_request_id').val() + `')">close</i>
                            </div>
                        `);

                        $('.row_item[data-id=""]').remove();

                        $.each(datakuy.details, function(i, val) {
                            var count = makeid(10);
                            $('#last-row-item').before(`
                                <tr class="row_item" data-id="` + $('#material_request_id').val() + `">
                                    <input type="hidden" name="arr_lookable_type[]" value="` + val.type + `">
                                    <input type="hidden" name="arr_lookable_id[]" value="` + val.id + `">
                                    <td>
                                        <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                                    </td>
                                    <td>
                                        <input name="arr_qty[]" onfocus="emptyThis(this);" type="text" value="` + val.qty_balance + `" onkeyup="formatRupiahNoMinus(this)">
                                    </td>
                                    <td class="center">
                                        <span id="arr_satuan` + count + `">` + val.unit + `</span>
                                    </td>
                                    <td>
                                        <input name="arr_note[]" type="text" placeholder="Keterangan barang 1..." value="` + val.note + `">
                                    </td>
                                    <td>
                                        <input name="arr_note2[]" type="text" placeholder="Keterangan barang 2...">
                                    </td>
                                    <td>
                                        <input name="arr_required_date[]" type="date" value="` + val.date + `">
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
                                            <option value="">--Kosong--</option>
                                            @foreach ($line as $rowline)
                                                <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                                            @endforeach
                                        </select>    
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_machine` + count + `" name="arr_machine[]" onchange="changeLine(this);">
                                            <option value="">--Kosong--</option>
                                            @foreach ($machine as $row)
                                                <option value="{{ $row->id }}" data-line="{{ $row->line_id }}">{{ $row->name }}</option>
                                            @endforeach    
                                        </select>
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">
                                            <option value="">--Silahkan pilih item--</option>    
                                        </select>
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                                            <option value="">--Kosong--</option>
                                            @foreach ($department as $rowdept)
                                                <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                                            @endforeach
                                        </select>    
                                    </td>
                                    <td>
                                        <input name="arr_requester[]" type="text" placeholder="Yang meminta barang / requester" value="` + val.requester + `" required>
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                                    </td>
                                    <td class="center">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                </tr>
                            `);
                            $('#arr_place' + count).val(val.place_id);
                            $('#arr_line' + count).val(val.line_id);
                            $('#arr_machine' + count).val(val.machine_id);
                            $('#arr_department' + count).val(val.department_id);
                            $('#arr_warehouse' + count).empty();
                            $.each(val.list_warehouse, function(j, value) {
                                $('#arr_warehouse' + count).append(`
                                    <option value="` + value.id + `" ` + (value.id == val.warehouse_id ? 'selected' : '') + `>` + value.name + `</option>
                                `);
                            });
                            $('#arr_item' + count).append(`
                                <option value="` + val.item_id + `">` + val.item_name + `</option>
                            `);
                            select2ServerSide('#arr_item' + count, '{{ url("admin/select2/purchase_item") }}');
                            if(val.project_id){
                                $('#arr_project' + count).append(`
                                    <option value="` + val.project_id + `">` + val.project_name + `</option>
                                `)
                            }
                            select2ServerSide('#arr_project' + count, '{{ url("admin/select2/project") }}');
                        });

                        $('#material_request_id').empty();
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
    }

    function removeUsedData(type,id){
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

    function addItem(){
        var count = makeid(10);
        $('#last-row-item').before(`
            <tr class="row_item" data-id="">
                <input type="hidden" name="arr_lookable_type[]" value="">
                <input type="hidden" name="arr_lookable_id[]" value="">
                <td>
                    <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                </td>
                <td>
                    <input name="arr_qty[]" onfocus="emptyThis(this);" type="text" value="0" onkeyup="formatRupiahNoMinus(this)">
                </td>
                <td class="center">
                    <span id="arr_satuan` + count + `">-</span>
                </td>
                <td>
                    <input name="arr_note[]" type="text" placeholder="Keterangan barang 1...">
                </td>
                <td>
                    <input name="arr_note2[]" type="text" placeholder="Keterangan barang 2...">
                </td>
                <td>
                    <input name="arr_required_date[]" type="date" value="{{ date('Y-m-d') }}" min="` + $('#post_date').val() + `">
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
                        <option value="">--Kosong--</option>
                        @foreach ($line as $rowline)
                            <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                        @endforeach
                    </select>    
                </td>
                <td>
                    <select class="browser-default" id="arr_machine` + count + `" name="arr_machine[]" onchange="changeLine(this);">
                        <option value="">--Kosong--</option>
                        @foreach ($machine as $row)
                            <option value="{{ $row->id }}" data-line="{{ $row->line_id }}">{{ $row->name }}</option>
                        @endforeach    
                    </select>
                </td>
                <td>
                    <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">
                        <option value="">--Silahkan pilih item--</option>    
                    </select>
                </td>
                <td>
                    <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                        <option value="">--Kosong--</option>
                        @foreach ($department as $rowdept)
                            <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                        @endforeach
                    </select>    
                </td>
                <td>
                    <input name="arr_requester[]" type="text" placeholder="Yang meminta barang / requester" required>
                </td>
                <td>
                    <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        select2ServerSide('#arr_item' + count, '{{ url("admin/select2/purchase_item") }}');
        select2ServerSide('#arr_project' + count, '{{ url("admin/select2/project") }}');
    }

    function useStock(){
        let passed = false, arr_id = [], arr_name = [], arr_unit = [], arr_qty = [], arr_warehouse = [];
        $('input[name^="arr_value_stock[]"]').each(function(index){
            if($(this).is(':checked')){
                passed = true;
                arr_id.push($('input[name^="arr_id_item[]"]').eq(index).val());
                arr_name.push($('span[name^="arr_item_name[]"]').eq(index).text());
                arr_unit.push($('span[name^="arr_item_unit[]"]').eq(index).text());
                arr_qty.push($('input[name^="arr_qty_order[]"]').eq(index).val());
                arr_warehouse.push($('input[name^="arr_warehouse_item[]"]').eq(index).val());
            }
        });
        if(passed){
            $('.row_item').remove();
            for(let i = 0; i < arr_id.length;i++){
                var count = makeid(10);
                $('#last-row-item').before(`
                    <tr class="row_item">
                        <td>
                            <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                        </td>
                        <td>
                            <input name="arr_qty[]" onfocus="emptyThis(this);" type="text" value="` + arr_qty[i] + `" onkeyup="formatRupiahNoMinus(this)">
                        </td>
                        <td class="center">
                            <span id="arr_satuan` + count + `">` + arr_unit[i] + `</span>
                        </td>
                        <td>
                            <input name="arr_note[]" type="text" placeholder="Keterangan barang 1...">
                        </td>
                        <td>
                            <input name="arr_note2[]" type="text" placeholder="Keterangan barang 2...">
                        </td>
                        <td>
                            <input name="arr_required_date[]" type="date" value="{{ date('Y-m-d') }}" min="` + $('#post_date').val() + `">
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
                                <option value="">--Kosong--</option>
                                @foreach ($line as $rowline)
                                    <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                                @endforeach
                            </select>    
                        </td>
                        <td>
                            <select class="browser-default" id="arr_machine` + count + `" name="arr_machine[]" onchange="changeLine(this);">
                                <option value="">--Kosong--</option>
                                @foreach ($machine as $row)
                                    <option value="{{ $row->id }}" data-line="{{ $row->line_id }}">{{ $row->name }}</option>
                                @endforeach    
                            </select>
                        </td>
                        <td>
                            <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">
                                <option value="">--Silahkan pilih item--</option>    
                            </select>
                        </td>
                        <td>
                            <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                                <option value="">--Kosong--</option>
                                @foreach ($department as $rowdept)
                                    <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                                @endforeach
                            </select>    
                        </td>
                        <td>
                            <input name="arr_requester[]" type="text" placeholder="Yang meminta barang / requester" required>
                        </td>
                        <td>
                            <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                        </td>
                        <td class="center">
                            <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                <i class="material-icons">delete</i>
                            </a>
                        </td>
                    </tr>
                `);
                select2ServerSide('#arr_item' + count, '{{ url("admin/select2/purchase_item") }}');
                select2ServerSide('#arr_project' + count, '{{ url("admin/select2/project") }}');
                $('#arr_item' + count).append(`
                    <option value="` + arr_id[i] + `">` + arr_name[i] + `</option>
                `);
                $("#arr_warehouse" + count).empty();
                if(JSON.parse(arr_warehouse[i]).length > 0){
                    $.each(JSON.parse(arr_warehouse[i]), function(i, value) {
                        $('#arr_warehouse' + count).append(`
                            <option value="` + value.id + `">` + value.name + `</option>
                        `);
                    });
                }else{
                    $("#arr_warehouse" + count).append(`
                        <option value="">--Gudang tidak diatur di master data Grup Item--</option>
                    `);
                }
            }
            $('#modal7').modal('close');
            
        }else{
            swal({
                title: 'Ups!',
                text: 'Silahkan pilih / centang item untuk memindahkan.',
                icon: 'warning'
            });
        }
    }

    function addItemFromStock(){
        $.ajax({
            url: '{{ Request::url() }}/get_items_from_stock',
            type: 'POST',
            dataType: 'JSON',
            data: {

            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('#main');
            },
            success: function(response) {
                if(response.status == '200'){
                    $('#modal7').modal('open');

                    if(response.details.length > 0){
                        $.each(response.details, function(i, val) {
                            var count = makeid(10);
                            $('#body-stock').append(`
                                <tr class="row_stock">
                                    <input type="hidden" name="arr_id_item[]" id="arr_id_item` + count + `" value="` + val.item_id + `">
                                    <input type="hidden" name="arr_warehouse_item[]" id="arr_warehouse_item` + count + `" value='` + JSON.stringify(val.list_warehouse) + `'>
                                    <td class="center-align">
                                        <label>
                                            <input type="checkbox" id="arr_value_stock` + count + `" name="arr_value_stock[]" value="1">
                                            <span>Pilih</span>
                                        </label>
                                    </td>
                                    <td class="">
                                        <span name="arr_item_name[]">` + val.item_name + `</span>
                                    </td>
                                    <td class="center-align">
                                        <span name="arr_item_unit[]">` + val.unit + `</span>
                                    </td>
                                    <td class="right-align">
                                        ` + val.in_stock + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.in_pr + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.in_po + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.min_stock + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.max_stock + `
                                    </td>
                                    <td class="right-align">
                                        <input name="arr_qty_order[]" onfocus="emptyThis(this);" type="text" value="` + val.qty_request + `" onkeyup="formatRupiahNoMinus(this)">
                                    </td>
                                </tr>
                            `);
                        });
                    }

                    $('.modal-content').scrollTop(0);
                    M.updateTextFields();
                }else{
                    swal({
                        title: 'Ups!',
                        text: response.message,
                        icon: 'warning'
                    });
                }
                loadingClose('#main');
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

    function changePlace(element){
        if($(element).val()){
            $(element).parent().prev().find('select[name="arr_place[]"]').val($(element).find(':selected').data('place'));
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
                    title : 'Purchase Request',
                    intro : 'Form ini digunakan untuk menambahkan permintaan pembelian barang sebelum purchase order. Silahkan ikuti panduan ini untuk penjelasan mengenai isian form.'
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
                    title : 'Tgl. Posting',
                    element : document.querySelector('.step3'),
                    intro : 'Tanggal post akan menentukan tanggal jurnal untuk beberapa form yang terhubung dengan jurnal. Hati - hati dalam menentukan tanggal posting.' 
                },
                {
                    title : 'Tgl. Kadaluarsa',
                    element : document.querySelector('.step4'),
                    intro : 'Tanggal kadaluarsa digunakan untuk menentukan masa berlaku dokumen hingga tanggal ini ditentukan.' 
                },
                {
                    title : 'File Lampiran',
                    element : document.querySelector('.step6'),
                    intro : 'Silahkan unggah file lampiran. untuk saat ini hanya bisa mengakomodir 1 file lampiran saja. Jika ingin menambahkan file lebih dari 1, silahkan gabungkan file anda menjadi pdf.' 
                },
                {
                    title : 'Perusahaan',
                    element : document.querySelector('.step7'),
                    intro : 'Perusahaan dimana dokumen ini dibuat.' 
                },
                {
                    title : 'Link Proyek',
                    element : document.querySelector('.step8'),
                    intro : 'Jika Purchase Request memiliki hubungan dengan proyek, maka silahkan tentukan disini.' 
                },
                {
                    title : 'Daftar Material Request',
                    element : document.querySelector('.step9'),
                    intro : 'Jika Purchase Request memiliki hubungan dengan Material Request, maka silahkan pilih untuk dimasukkan ke dalam Detail Produk. Anda bisa memasukkan lebih dari satu Material Request ke dalam Purchase Request.' 
                },
                {
                    title : 'Tombol tambah Material Request',
                    element : document.querySelector('.step10'),
                    intro : 'Tekan tombol setelah memilih Material Request untuk mengambil data Material Request ke dalam tabel detail produk.' 
                },
                {
                    title : 'Dafter dokumen Material Request Terpakai (Kunci dokumen)',
                    element : document.querySelector('.step11'),
                    intro : 'Material Request yang terpakai pada form ini akan ditampilkan disini, silahkan tekan tombol X (hapus) agar bisa diakses oleh pengguna lainnya.' 
                },
                {
                    title : 'Detail produk',
                    element : document.querySelector('.step12'),
                    intro : 'Silahkan tambahkan produk anda disini, lengkap dengan keterangan detail tentang produk tersebut. Hati-hati dalam menentukan Plant, dan Gudang Tujuan, karena itu nantinya akan menentukan dimana barang ketika diterima.' 
                },
                {
                    title : 'Keterangan',
                    element : document.querySelector('.step13'),
                    intro : 'Silahkan isi / tambahkan keterangan untuk dokumen ini untuk dimunculkan di bagian bawah tabel detail produk nantinya, ketika dicetak.' 
                },
                {
                    title : 'Tombol Simpan',
                    element : document.querySelector('.step14'),
                    intro : 'Silahkan tekan tombol ini untuk menyimpan data, namun pastikan data yang akan anda masukkan benar.' 
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
</script>