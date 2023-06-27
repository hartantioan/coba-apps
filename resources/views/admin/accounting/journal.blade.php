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

    .browser-default {
        height: 2rem !important;
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
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                            <i class="material-icons hide-on-med-and-up">refresh</i>
                            <span class="hide-on-small-onl">Refresh</span>
                            <i class="material-icons right">refresh</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="printData();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">Print</span>
                            <i class="material-icons right">local_printshop</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="exportExcel();">
                            <i class="material-icons hide-on-med-and-up">view_list</i>
                            <span class="hide-on-small-onl">Excel</span>
                            <i class="material-icons right">view_list</i>
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
                                                <label for="filter_status" style="font-size:1rem;">Status :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_status" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
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
                                                <label for="filter_currency" style="font-size:1rem;">Mata Uang :</label>
                                                <div class="input-field">
                                                    <select class="select2 browser-default" multiple="multiple" id="filter_currency" name="filter_currency" onchange="loadDataTable()">
                                                        <option value="" disabled>Semua</option>
                                                        @foreach ($currency as $row)
                                                            <option value="{{ $row->id }}">{{ $row->code }}</option>
                                                        @endforeach
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
                                </li>
                            </ul>
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
                                                        <th>Kode</th>
                                                        <th>Pengguna</th>
                                                        <th>Tanggal</th>
                                                        <th>Keterangan</th>
                                                        <th>Ref No.</th>
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
                <ul class="tabs">
                    <li class="tab col m6"><a class="active" href="#inputOne">Input Satu Data</a></li>
                    <li class="tab col m6"><a href="#inputMulti">Input Multi Data</a></li>
                </ul>
                <div id="inputOne" class="col s12 active">
                    <h4 class="mt-2">Tambah/Edit Satu {{ $title }}</h4>
                    <form class="row" id="form_data" onsubmit="return false;">
                        <div class="col s12">
                            <div id="validation_alert" style="display:none;"></div>
                        </div>
                        <div class="col s12">
                            <div class="input-field col m3 s12">
                                <input type="hidden" id="temp" name="temp">
                                <select class="form-control" id="company_id" name="company_id">
                                    @foreach ($company as $row)
                                        <option value="{{ $row->id }}">{{ $row->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="company_id">Perusahaan</label>
                            </div>
                            <div class="input-field col m3 s12">
                                
                                <input id="note" name="note" type="text" placeholder="Keterangan">
                                <label class="active" for="note">Keterangan</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                <label class="active" for="post_date">Tgl. Posting</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="due_date" name="due_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. Kadaluarsa">
                                <label class="active" for="due_date">Tgl. Kadaluarsa</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="currency_id" name="currency_id">
                                    @foreach ($currency as $row)
                                        <option value="{{ $row->id }}">{{ $row->code.' '.$row->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="currency_id">Mata Uang</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="currency_rate" name="currency_rate" type="text" value="1" onkeyup="formatRupiah(this)">
                                <label class="active" for="currency_rate">Konversi</label>
                            </div>
                            <div class="col s12">
                                <h5>Tambah dari Distribusi (Opsional)</h5>
                                <div class="input-field col s3">
                                    <select class="browser-default" id="cost_distribution_id" name="cost_distribution_id"></select>
                                    <label class="active" for="cost_distribution_id">Distribusi Biaya</label>
                                </div>
                                <div class="input-field col s2">
                                    <input name="nominal" id="nominal" type="text" value="0" onkeyup="formatRupiah(this);">
                                    <label class="active" for="nominal">Nominal</label>
                                </div>
                                <div class="input-field col s2">
                                    <select class="" id="type" name="type">
                                        <option value="1">Debit</option>
                                        <option value="2">Kredit</option>
                                    </select>
                                    <label class="" for="type">Tipe</label>
                                </div>
                                <div class="input-field col s3">
                                    <a class="waves-effect waves-light green btn mr-1" onclick="addCostDistribution()" href="javascript:void(0);">
                                        <i class="material-icons left">add</i> Tambah
                                    </a>
                                </div>
                            </div>
                            <div class="col s12 mt-2" style="overflow:auto;width:100% !important;">
                                <h5>Detail Coa</h5>
                                <p class="mt-2 mb-2">
                                    <table class="bordered" style="min-width:1500px;">
                                        <thead>
                                            <tr>
                                                <th class="center">Coa</th>
                                                <th class="center">BP</th>
                                                <th class="center">Plant</th>
                                                <th class="center">Line</th>
                                                <th class="center">Mesin</th>
                                                <th class="center">Departemen</th>
                                                <th class="center">Gudang</th>
                                                <th class="center">Debit</th>
                                                <th class="center">Kredit</th>
                                                <th class="center">Hapus</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-coa">
                                            <tr id="last-row-coa">
                                                <td colspan="10" class="center">
                                                    <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addCoa('1')" href="javascript:void(0);">
                                                        <i class="material-icons left">add</i> Tambah Debit
                                                    </a>
                                                    <a class="waves-effect waves-light red btn-small mb-1 mr-1" onclick="addCoa('2')" href="javascript:void(0);">
                                                        <i class="material-icons left">add</i> Tambah Kredit
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </p>
                            </div>
                            <div class="col s6 mt-1 center"><h5>Total Debit : <b id="totalDebit">0,000</b></h5></div>
                            <div class="col s6 mt-1 center"><h5>Total Credit : <b id="totalCredit">0,000</b></h5></div>
                            <div class="col s12 mt-3">
                                <button class="btn waves-effect waves-light right submit" onclick="save();">Simpan <i class="material-icons right">send</i></button>
                            </div>
                        </div>
                    </form>
                </div>
                <div id="inputMulti" class="col s12">
                    <h4 class="mt-2">Tambah/Edit Multi {{ $title }}</h4>
                    <form class="row" id="form_data_multi" onsubmit="return false;">
                        <div class="col s12">
                            <div id="validation_alert_multi" style="display:none;"></div>
                        </div>
                        <div class="col s12">
                            <div class="col s12" style="overflow:auto;width:100% !important;">
                                <h6>Anda bisa menggunakan fitur copy paste dari format excel yang telah disediakan. Silahkan klik <a href="{{ asset(Storage::url('format_imports/format_copas_journal.xlsx')) }}" target="_blank">disini</a> untuk mengunduh. Jangan menyalin kolom paling atas (bagian header), dan tempel pada isian paling kiri di tabel di bawah ini.</h6>
                                <p class="mt-2 mb-2">
                                    <table class="bordered" style="min-width:2700px;zoom:0.7;">
                                        <thead>
                                            <tr>
                                                <th class="center">Kode Jurnal</th>
                                                <th class="center" style="width:75px;">Perusahaan</th>
                                                <th class="center">Keterangan</th>
                                                <th class="center">Tgl.Post</th>
                                                <th class="center">Tgl.Tenggat</th>
                                                <th class="center">Mata Uang</th>
                                                <th class="center" style="width:75px;">Konversi</th>
                                                <th class="center" style="width:75px;">Coa</th>
                                                <th class="center" style="width:75px;">BP</th>
                                                <th class="center" style="width:75px;">Plant</th>
                                                <th class="center" style="width:75px;">Line</th>
                                                <th class="center" style="width:75px;">Mesin</th>
                                                <th class="center" style="width:75px;">Departemen</th>
                                                <th class="center" style="width:75px;">Gudang</th>
                                                <th class="center">Debit</th>
                                                <th class="center">Kredit</th>
                                                <th class="center">Hapus</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-coa-multi">
                                            <tr id="last-row-coa-multi">
                                                <td colspan="17" class="center">
                                                    <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addLine()" href="javascript:void(0);">
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
                            <div class="col s6 mt-1 center"><h5>Total Debit : <b id="totalDebitMulti">0,000</b></h5></div>
                            <div class="col s6 mt-1 center"><h5>Total Credit : <b id="totalCreditMulti">0,000</b></h5></div>
                            <div class="col s12 mt-3">
                                <button class="btn waves-effect waves-light right submit" onclick="saveMulti();">Simpan <i class="material-icons right">send</i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal2" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;width:100%;">
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

<div id="modal4" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;width:100%;">
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
                                    <div class="input-field col m4 s12">
                                        <input id="range_start" name="range_start" min="0" type="number" placeholder="1">
                                        <label class="" for="range_end">No Awal</label>
                                    </div>
                                    
                                    <div class="input-field col m4 s12">
                                        <input id="range_end" name="range_end" min="0" type="number" placeholder="1">
                                        <label class="active" for="range_end">No akhir</label>
                                    </div>
                                    <div class="input-field col m4 s12">
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
                               
                                <div class="input-field col m4 s12">
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
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
                $('.tabs').tabs();
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                M.updateTextFields();
                resetDetailForm();
                $('.row_coa').remove();
                $('#cost_distribution_id').empty();
                countAll();
                $('.row_coa_multi').remove();
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
        
        $("#item_id").on("select2:unselecting", function(e) {
            $('#code').val('');
            $('#name').val('');
        });

        $('#body-coa').on('click', '.delete-data-coa', function() {
            $(this).closest('tr').remove();
            countAll();
        });

        $('#body-coa-multi').on('click', '.delete-data-coa-multi', function() {
            $(this).closest('tr').remove();
            countAllMulti();
        });

        select2ServerSide('#cost_distribution_id', '{{ url("admin/select2/cost_distribution") }}');
    });

    function resetDetailForm(){
        $('.row_material').each(function(){
            $(this).remove();
        });

        $('.row_cost').each(function(){
            $(this).remove();
        });
    }

    function addCostDistribution(){
        if($('#cost_distribution_id').val() && $('#nominal').val() && parseFloat($('#nominal').val().replaceAll(".", "").replaceAll(",",".")) > 0){
            var total = parseFloat($('#nominal').val().replaceAll(".", "").replaceAll(",",".")), type = $('#type').val();
            $.each($('#cost_distribution_id').select2('data')[0].details, function (i, val) {
                var count = makeid(10), nominal = parseFloat(total * (val.percentage / 100));
                $('#last-row-coa').before(`
                    <tr class="row_coa">
                        <input type="hidden" name="arr_type[]" value="` + type + `">
                        <input type="hidden" name="arr_cost_distribution_detail[]" value="` + val.id + `">
                        <td>
                            <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]"></select>
                        </td>
                        <td>
                            <select class="browser-default" id="arr_account` + count + `" name="arr_account[]"></select>    
                        </td>
                        <td>
                            <select class="browser-default" id="arr_place` + count + `" name="arr_place[]" style="width:200px !important;">
                                <option value="">--Kosong--</option>
                                @foreach ($place as $row)
                                    <option value="{{ $row->id }}">{{ $row->code }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select class="browser-default" id="arr_line` + count + `" name="arr_line[]" style="width:200px !important;" onchange="changePlace(this);">
                                <option value="">--Kosong--</option>
                                @foreach ($line as $rowline)
                                    <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                                @endforeach
                            </select>    
                        </td>
                        <td>
                            <select class="browser-default" id="arr_machine` + count + `" name="arr_machine[]" style="width:200px !important;" onchange="changeLine(this);">
                                <option value="">--Kosong--</option>
                                @foreach ($machine as $row)
                                    <option value="{{ $row->id }}" data-line="{{ $row->line_id }}">{{ $row->name }}</option>
                                @endforeach    
                            </select>
                        </td>
                        <td>
                            <select class="browser-default" id="arr_department` + count + `" name="arr_department[]" style="width:200px !important;">
                                <option value="">--Kosong--</option>
                                @foreach ($department as $row)
                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]"></select>
                        </td>
                        <td>
                            ` + (type == '1' ? `<input name="arr_nominal[]" type="text" value="` + formatRupiahIni(roundTwoDecimal(nominal).toString().replace('.',',')) + `" style="width:150px !important;" onkeyup="formatRupiah(this);countAll();">` : `-`) + `
                        </td>
                        <td>
                            ` + (type == '2' ? `<input name="arr_nominal[]" type="text" value="` + formatRupiahIni(roundTwoDecimal(nominal).toString().replace('.',',')) + `" style="width:150px !important;" onkeyup="formatRupiah(this);countAll();">` : `-`) + `
                        </td>
                        <td class="center">
                            <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-coa" href="javascript:void(0);">
                                <i class="material-icons">delete</i>
                            </a>
                        </td>
                    </tr>
                `);

                if(val.warehouse_id){
                    $('#arr_warehouse' + count).append(`
                        <option value="` + val.warehouse_id + `">` + val.warehouse_name + `</option>
                    `);
                }

                if($('#cost_distribution_id').select2('data')[0].coa_id){
                    $('#arr_coa' + count).append(`
                        <option value="` + $('#cost_distribution_id').select2('data')[0].coa_id + `">` + $('#cost_distribution_id').select2('data')[0].coa_name + `</option>
                    `);
                }
                
                select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa_journal") }}');
                select2ServerSide('#arr_cost_distribution' + count, '{{ url("admin/select2/cost_distribution") }}');
                select2ServerSide('#arr_warehouse' + count, '{{ url("admin/select2/warehouse") }}');
                select2ServerSide('#arr_account' + count, '{{ url("admin/select2/business_partner") }}');
                $('#arr_place' + count).val(val.place_id).formSelect();
                $('#arr_department' + count).val(val.department_id).formSelect();
                $('#arr_line' + count).val(val.line_id);
                $('#arr_machine' + count).val(val.machine_id);
            });

            countAll();
        }
        $('#cost_distribution_id').empty();
        $('#nominal').val('0');
        $('#type').val('1').formSelect();
    }

    function addCoa(type){
        var count = makeid(10);

        $('#last-row-coa').before(`
            <tr class="row_coa">
                <input type="hidden" name="arr_type[]" value="` + type + `">
                <input type="hidden" name="arr_cost_distribution_detail[]" value="">
                <td>
                    <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]"></select>
                </td>
                <td>
                    <select class="browser-default" id="arr_account` + count + `" name="arr_account[]"></select>    
                </td>
                <td>
                    <select class="browser-default" id="arr_place` + count + `" name="arr_place[]" style="width:200px !important;">
                        <option value="">--Kosong--</option>
                        @foreach ($place as $row)
                            <option value="{{ $row->id }}">{{ $row->code }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select class="browser-default" id="arr_line` + count + `" name="arr_line[]" style="width:200px !important;" onchange="changePlace(this);">
                        <option value="">--Kosong--</option>
                        @foreach ($line as $rowline)
                            <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                        @endforeach
                    </select>    
                </td>
                <td>
                    <select class="browser-default" id="arr_machine` + count + `" name="arr_machine[]" style="width:200px !important;" onchange="changeLine(this);">
                        <option value="">--Kosong--</option>
                        @foreach ($machine as $row)
                            <option value="{{ $row->id }}" data-line="{{ $row->line_id }}">{{ $row->name }}</option>
                        @endforeach    
                    </select>
                </td>
                <td>
                    <select class="browser-default" id="arr_department` + count + `" name="arr_department[]" style="width:200px !important;">
                        <option value="">--Kosong--</option>
                        @foreach ($department as $row)
                            <option value="{{ $row->id }}">{{ $row->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]"></select>
                </td>
                <td>
                    ` + (type == '1' ? `<input name="arr_nominal[]" type="text" value="0" style="width:150px !important;" onkeyup="formatRupiah(this);countAll();">` : `-`) + `
                </td>
                <td>
                    ` + (type == '2' ? `<input name="arr_nominal[]" type="text" value="0" style="width:150px !important;" onkeyup="formatRupiah(this);countAll();">` : `-`) + `
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-coa" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        
        select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa_journal") }}');
        select2ServerSide('#arr_cost_distribution' + count, '{{ url("admin/select2/cost_distribution") }}');
        select2ServerSide('#arr_warehouse' + count, '{{ url("admin/select2/warehouse") }}');
        select2ServerSide('#arr_account' + count, '{{ url("admin/select2/business_partner") }}');
        $('#arr_place' + count).formSelect();
        $('#arr_department' + count).formSelect();
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

    function addLine(){
        $('#last-row-coa-multi').before(`
            <tr class="row_coa_multi">
                <td>
                    <input type="text" name="arr_multi_code[]" placeholder="Kode Jurnal">
                </td>
                <td>
                    <input type="text" name="arr_multi_company[]" placeholder="ID Perusahaan">
                </td>
                <td>
                    <input type="text" name="arr_multi_note[]" placeholder="Keterangan">
                </td>
                <td>
                    <input type="text" name="arr_multi_post_date[]" placeholder="Tgl.Post format dd/mm/yy ex:15/12/23">
                </td>
                <td>
                    <input type="text" name="arr_multi_due_date[]" placeholder="Tgl.Tenggat format dd/mm/yy ex:15/12/23">
                </td>
                <td>
                    <input type="text" name="arr_multi_currency[]" placeholder="ID Mata Uang">
                </td>
                <td>
                    <input type="text" name="arr_multi_conversion[]" placeholder="Konversi">
                </td>
                <td>
                    <input type="text" name="arr_multi_coa[]" placeholder="ID Coa">
                </td>
                <td>
                    <input type="text" name="arr_multi_bp[]" placeholder="ID Partner Bisnis">    
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
                    <input type="text" name="arr_multi_department[]" placeholder="ID Departemen">
                </td>
                <td>
                    <input type="text" name="arr_multi_warehouse[]" placeholder="ID Gudang">
                </td>
                <td>
                    <input type="text" name="arr_multi_debit[]" placeholder="Nominal Debit" value="0" onkeyup="countAllMulti()">
                </td>
                <td>
                    <input type="text" name="arr_multi_kredit[]" placeholder="Nominal Kredit" value="0" onkeyup="countAllMulti()">
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-coa-multi" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        $('#body-coa-multi :input').off('paste');
        $('#body-coa-multi :input').on('paste', function (e) {
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
                        $('#last-row-coa-multi').before(`
                            <tr class="row_coa_multi">
                                <td>
                                    <input type="text" name="arr_multi_code[]" placeholder="Kode Jurnal">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_company[]" placeholder="ID Perusahaan">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_note[]" placeholder="Keterangan">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_post_date[]" placeholder="Tgl.Post format dd/mm/yy ex:15/12/23">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_due_date[]" placeholder="Tgl.Tenggat format dd/mm/yy ex:15/12/23">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_currency[]" placeholder="ID Mata Uang">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_conversion[]" placeholder="Konversi">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_coa[]" placeholder="ID Coa">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_bp[]" placeholder="ID Partner Bisnis">    
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_place[]" placeholder="ID Plant">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_line[]" placeholder="ID Line">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_machine[]" placeholder="ID Line">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_department[]" placeholder="ID Departemen">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_warehouse[]" placeholder="ID Gudang">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_debit[]" placeholder="Nominal Debit" value="0" onkeyup="countAllMulti()">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_kredit[]" placeholder="Nominal Kredit" value="0" onkeyup="countAllMulti()">
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-coa-multi" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                    }

                    $('#body-coa-multi :input').off('paste');
                    $('#body-coa-multi :input').on('paste', function (e) {
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

    function countAll(){
        let totalDebit = 0, totalCredit = 0;

        $('input[name^="arr_nominal"]').each(function(index){
            let nominal = parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
            if($('input[name^="arr_type"]').eq(index).val() == '1'){
                totalDebit += nominal;
            }else{
                totalCredit += nominal;
            }
        });

        $('#totalDebit').text(formatRupiahIni(totalDebit.toFixed(2).toString().replace('.',',')));
        $('#totalCredit').text(formatRupiahIni(totalCredit.toFixed(2).toString().replace('.',',')));
    }

    function countAllMulti(){
        let totalDebit = 0, totalCredit = 0;

        $('input[name^="arr_multi_debit"]').each(function(index){
            totalDebit += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
        });
        $('input[name^="arr_multi_kredit"]').each(function(index){
            totalCredit += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
        });

        $('#totalDebitMulti').text(formatRupiahIni(totalDebit.toFixed(2).toString().replace('.',',')));
        $('#totalCreditMulti').text(formatRupiahIni(totalCredit.toFixed(2).toString().replace('.',',')));
    }

    function getRowUnit(val){
        $('#arr_satuan' + val).text($("#arr_item" + val).select2('data')[0].uom);
    }

    function getCodeAndName(){
        $('#code').val($("#item_id").select2('data')[0].code);
        $('#name').val($("#item_id").select2('data')[0].name);
        $('.uom-unit').text($("#item_id").select2('data')[0].uom);
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
            "language": {
                "lengthMenu": "Menampilkan _MENU_ data per halaman",
                "zeroRecords": "Data tidak ditemukan / kosong",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
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
            columns: [
                { name: 'id', searchable: false, className: 'center-align details-control' },
                { name: 'code', className: 'center-align' },
                { name: 'name', className: 'center-align' },
                { name: 'date', className: 'center-align' },
                { name: 'note', className: '' },
                { name: 'ref', searchable: false, orderable: false, className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle',
                'selectNone' 
            ],
            select: {
                style: 'multi'
            },
        });
        $('.dt-buttons').appendTo('#datatable_buttons');
        $('select[name="datatable_serverside_length"]').addClass('browser-default');
	}

    function save(){
        if(checkMustBp()){
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

                    formData.delete("arr_type[]");
                    formData.delete("arr_cost_distribution_detail[]");
                    formData.delete("arr_coa[]");
                    formData.delete("arr_place[]");
                    formData.delete("arr_line[]");
                    formData.delete("arr_machine[]");
                    formData.delete("arr_account[]");
                    formData.delete("arr_item[]");
                    formData.delete("arr_department[]");
                    formData.delete("arr_warehouse[]");
                    formData.delete("arr_nominal[]");

                    $('input[name^="arr_type"]').each(function(index){
                        formData.append('arr_type[]',$(this).val());
                        formData.append('arr_coa[]',($('select[name^="arr_coa"]').eq(index).val() ? $('select[name^="arr_coa"]').eq(index).val() : 'NULL'));
                        formData.append('arr_cost_distribution_detail[]',($('input[name^="arr_cost_distribution_detail"]').eq(index).val() ? $('input[name^="arr_cost_distribution_detail"]').eq(index).val() : 'NULL'));
                        formData.append('arr_place[]',($('select[name^="arr_place"]').eq(index).val() ? $('select[name^="arr_place"]').eq(index).val() : 'NULL'));
                        formData.append('arr_line[]',($('select[name^="arr_line"]').eq(index).val() ? $('select[name^="arr_line"]').eq(index).val() : 'NULL'));
                        formData.append('arr_machine[]',($('select[name^="arr_machine"]').eq(index).val() ? $('select[name^="arr_machine"]').eq(index).val() : 'NULL'));
                        formData.append('arr_account[]',($('select[name^="arr_account"]').eq(index).val() ? $('select[name^="arr_account"]').eq(index).val() : 'NULL'));
                        formData.append('arr_item[]',($('select[name^="arr_item"]').eq(index).val() ? $('select[name^="arr_item"]').eq(index).val() : 'NULL'));
                        formData.append('arr_department[]',$('select[name^="arr_department"]').eq(index).val());
                        formData.append('arr_warehouse[]',($('select[name^="arr_warehouse"]').eq(index).val() ? $('select[name^="arr_warehouse"]').eq(index).val() : 'NULL'));
                        formData.append('arr_nominal[]',($('input[name^="arr_nominal"]').eq(index).val() ? $('input[name^="arr_nominal"]').eq(index).val() : 'NULL'));
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
            });
        }else{
            swal({
                title: 'Ups! Error di detail.',
                text: 'Salah satu coa harus memiliki bisnis partner.',
                icon: 'error'
            });
        }
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
                formData.delete("arr_multi_company[]");
                formData.delete("arr_multi_note[]");
                formData.delete("arr_multi_post_date[]");
                formData.delete("arr_multi_due_date[]");
                formData.delete("arr_multi_currency[]");
                formData.delete("arr_multi_conversion[]");
                formData.delete("arr_multi_coa[]");
                formData.delete("arr_multi_place[]");
                formData.delete("arr_multi_bp[]");
                formData.delete("arr_multi_line[]");
                formData.delete("arr_multi_machine[]");
                formData.delete("arr_multi_department[]");
                formData.delete("arr_multi_warehouse[]");
                formData.delete("arr_multi_debit[]");
                formData.delete("arr_multi_kredit[]");

                $('input[name^="arr_multi_code"]').each(function(index){
                    if($(this).val()){
                        formData.append('arr_multi_code[]',$(this).val());
                        formData.append('arr_multi_company[]',($('input[name^="arr_multi_company"]').eq(index).val() ? $('input[name^="arr_multi_company"]').eq(index).val() : ''));
                        formData.append('arr_multi_note[]',($('input[name^="arr_multi_note"]').eq(index).val() ? $('input[name^="arr_multi_note"]').eq(index).val() : ''));
                        formData.append('arr_multi_post_date[]',($('input[name^="arr_multi_post_date"]').eq(index).val() ? $('input[name^="arr_multi_post_date"]').eq(index).val() : ''));
                        formData.append('arr_multi_due_date[]',($('input[name^="arr_multi_due_date"]').eq(index).val() ? $('input[name^="arr_multi_due_date"]').eq(index).val() : ''));
                        formData.append('arr_multi_currency[]',($('input[name^="arr_multi_currency"]').eq(index).val() ? $('input[name^="arr_multi_currency"]').eq(index).val() : ''));
                        formData.append('arr_multi_conversion[]',($('input[name^="arr_multi_conversion"]').eq(index).val() ? $('input[name^="arr_multi_conversion"]').eq(index).val() : ''));
                        formData.append('arr_multi_coa[]',($('input[name^="arr_multi_coa"]').eq(index).val() ? $('input[name^="arr_multi_coa"]').eq(index).val() : ''));
                        formData.append('arr_multi_place[]',($('input[name^="arr_multi_place"]').eq(index).val() ? $('input[name^="arr_multi_place"]').eq(index).val() : ''));
                        formData.append('arr_multi_bp[]',($('input[name^="arr_multi_bp"]').eq(index).val() ? $('input[name^="arr_multi_bp"]').eq(index).val() : ''));
                        formData.append('arr_multi_line[]',($('input[name^="arr_multi_line"]').eq(index).val() ? $('input[name^="arr_multi_line"]').eq(index).val() : ''));
                        formData.append('arr_multi_machine[]',($('input[name^="arr_multi_machine"]').eq(index).val() ? $('input[name^="arr_multi_machine"]').eq(index).val() : ''));
                        formData.append('arr_multi_department[]',($('input[name^="arr_multi_department"]').eq(index).val() ? $('input[name^="arr_multi_department"]').eq(index).val() : ''));
                        formData.append('arr_multi_warehouse[]',($('input[name^="arr_multi_warehouse"]').eq(index).val() ? $('input[name^="arr_multi_warehouse"]').eq(index).val() : ''));
                        formData.append('arr_multi_debit[]',($('input[name^="arr_multi_debit"]').eq(index).val() ? $('input[name^="arr_multi_debit"]').eq(index).val() : '0'));
                        formData.append('arr_multi_kredit[]',($('input[name^="arr_multi_kredit"]').eq(index).val() ? $('input[name^="arr_multi_kredit"]').eq(index).val() : '0'));
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

    function checkMustBp(){
        let passed = true;
        $('select[name^="arr_coa"]').each(function(index){
            if($(this).val()){
                if($(this).select2('data')[0].must_bp == '1'){
                    if(!$('select[name^="arr_account"]').eq(index).val()){
                        passed = false;
                    }
                }
            }
        });

        return passed;
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
                $('#company_id').val(response.company_id).formSelect();
                $('#note').val(response.note);
                $('#post_date').val(response.post_date);
                $('#due_date').val(response.due_date);
                $('#currency_id').val(response.currency_id).formSelect();
                $('#currency_rate').val(response.currency_rate);

                $('.row_coa').remove();

                $.each(response.details, function(i, val) {
                    let count = makeid(10);
                    $('#last-row-coa').before(`
                        <tr class="row_coa">
                            <input type="hidden" name="arr_type[]" value="` + val.type + `">
                            <input type="hidden" name="arr_cost_distribution_detail[]" value="` + val.cost_distribution_detail_id + `">
                            <td>
                                <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]"></select>
                            </td>
                            <td>
                                <select class="browser-default" id="arr_account` + count + `" name="arr_account[]"></select>    
                            </td>
                            <td>
                                <select class="browser-default" id="arr_place` + count + `" name="arr_place[]" style="width:200px !important;">
                                    <option value="">--Kosong--</option>
                                    @foreach ($place as $row)
                                        <option value="{{ $row->id }}">{{ $row->code }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select class="browser-default" id="arr_line` + count + `" name="arr_line[]" onchange="changePlace(this);" style="width:200px !important;">
                                    <option value="">--Kosong--</option>
                                    @foreach ($line as $rowline)
                                        <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                                    @endforeach
                                </select>    
                            </td>
                            <td>
                                <select class="browser-default" id="arr_machine` + count + `" name="arr_machine[]" onchange="changeLine(this);" style="width:200px !important;">
                                    <option value="">--Kosong--</option>
                                    @foreach ($machine as $row)
                                        <option value="{{ $row->id }}" data-line="{{ $row->line_id }}">{{ $row->name }}</option>
                                    @endforeach    
                                </select>
                            </td>
                            <td>
                                <select class="browser-default" id="arr_department` + count + `" name="arr_department[]" style="width:200px !important;">
                                    <option value="">--Kosong--</option>
                                    @foreach ($department as $row)
                                        <option value="{{ $row->id }}">{{ $row->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]"></select>
                            </td>
                            <td>
                                ` + (val.type == '1' ? `<input name="arr_nominal[]" type="text" value="` + val.nominal + `" style="width:150px !important;" onkeyup="formatRupiah(this);countAll();">` : `-`) + `
                            </td>
                            <td>
                                ` + (val.type == '2' ? `<input name="arr_nominal[]" type="text" value="` + val.nominal + `" style="width:150px !important;" onkeyup="formatRupiah(this);countAll();">` : `-`) + `
                            </td>
                            <td class="center">
                                <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-coa" href="javascript:void(0);">
                                    <i class="material-icons">delete</i>
                                </a>
                            </td>
                        </tr>
                    `);
                    
                    select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa_journal") }}');
                    select2ServerSide('#arr_warehouse' + count, '{{ url("admin/select2/warehouse") }}');
                    select2ServerSide('#arr_account' + count, '{{ url("admin/select2/business_partner") }}');
                    $('#arr_place' + count).val(val.place_id);
                    $('#arr_department' + count).val(val.department_id);
                    $('#arr_coa' + count).append(`
                        <option value="` + val.coa_id + `">` + val.coa_name + `</option>
                    `);
                    $('#arr_line' + count).val(val.line_id);
                    $('#arr_machine' + count).val(val.machine_id);
                    if(val.account_id){
                        $('#arr_account' + count).append(`
                            <option value="` + val.account_id + `">` + val.account_name + `</option>
                        `);
                    }
                    if(val.warehouse_id){
                        $('#arr_warehouse' + count).append(`
                            <option value="` + val.warehouse_id + `">` + val.warehouse_name + `</option>
                        `);
                    }
                });

                $('.modal-content').scrollTop(0);
                $('#code').focus();
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

    var printService = new WebSocketPrinter({
        onConnect: function () {
            
        },
        onDisconnect: function () {
           
        },
        onUpdate: function (message) {
            
        },
    });

    function printData(){
        var search = window.table.search(), status = $('#filter_status').val(), type = $('#filter_type').val(), company = $('#filter_company').val(), account = $('#filter_account').val();
        arr_id_temp=[];
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

    function printMultiSelect(){
        var formData = new FormData($('#form_data_print_multi')[0]);
        console.log(formData);
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
                    console.log(response.error);
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

    function exportExcel(){
        var search = window.table.search(), status = $('#filter_status').val(), currency = $('#filter_currency').val();
        
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status + "&currency=" + currency;
    }

    function printPreview(code){
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
</script>