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

    .select2 {
        max-width: 100% !important;
        height:auto !important;
    }

    .select2-selection--multiple{
        overflow: hidden !important;
        height: auto !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__clear {
        cursor: pointer;
        float: right;
        font-weight: bold;
        margin-top: 5px;
        margin-right: 0px;
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
                                                <label for="start_date" style="font-size:1rem;">Tanggal Mulai :</label>
                                                <div class="input-field col s12">
                                                <input type="date" max="{{ date('9999'.'-12-31') }}" id="start_date" name="start_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="finish_date" style="font-size:1rem;">Tanggal Akhir :</label>
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
                                            <div class="card-alert card purple">
                                                <div class="card-content white-text">
                                                    <p>Info : Item yang anda masukkan disini akan mempengaruhi qty stock saat ini.</p>
                                                </div>
                                            </div>
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
                                                        <th>Code</th>
                                                        <th>Pengguna</th>
                                                        <th>Perusahaan</th>
                                                        <th>Tanggal</th>
                                                        <th>Keterangan</th>
                                                        <th>Dokumen</th>
                                                        <th>Status</th>
                                                        <th>Operasi</th>
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
                <h4>Tambah/Edit {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <fieldset>
                            <legend>1. Informasi Utama</legend>
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
                                    <select class="form-control" id="company_id" name="company_id">
                                        @foreach ($company as $rowcompany)
                                            <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="" for="company_id">Perusahaan</label>
                                </div>
                                
                                <div class="input-field col m3 s12 step4">
                                    <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. post" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);">
                                    <label class="active" for="post_date">Tgl. Post</label>
                                </div>
                                <div class="file-field input-field col m3 s12 step5">
                                    <div class="btn">
                                        <span>Lampiran Bukti</span>
                                        <input type="file" name="document" id="document">
                                    </div>
                                    <div class="file-path-wrapper">
                                        <input class="file-path validate" type="text">
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset class="step6">
                            <legend>2. Item Request (Jika Ada)</legend>
                            <div class="mt-1 mb-1">
                                <b>Tarik data dari Item Request - qty yang muncul diambil dari selisih jumlah qty Item Request dikurangi stok saat ini. Jika selisih > 0, maka qty yang digunakan adalah qty stok. Jika selisih kurang <= 0, maka qty yang digunakan adalah qty Item Request. Hanya item yang telah disetujui akan masuk disini.</b>
                            </div>
                            <div class="row">
                                <div class="input-field col m5 step9">
                                    <select class="browser-default" id="material_request_id" name="material_request_id"></select>
                                    <label class="active" for="material_request_id">Daftar Item Request</label>
                                </div>
                                <div class="col m4 step10">
                                    <a class="waves-effect waves-light cyan btn-small mb-1 mr-1 mt-5" onclick="getMaterialRequest();" href="javascript:void(0);">
                                        <i class="material-icons left">add</i> Item Request
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
                                <div class="col m12 s12 step7">
                                    <p class="mt-2 mb-2">
                                        <h5>Detail Produk</h5>
                                        Coa debit mengikuti coa pada masing-masing grup item.
                                        <div>
                                            <table class="bordered" style="min-width:3000px;" id="table-detail">
                                                <thead>
                                                    <tr>
                                                        <th class="center">Item</th>
                                                        <th class="center">Stok</th>
                                                        <th class="center">Qty</th>
                                                        <th class="center">Satuan</th>
                                                        <th class="center" width="300px">No.Serial</th>
                                                        <th class="center">Keterangan</th>
                                                        <th class="center">Tipe Pengeluaran</th>
                                                        <th class="center">Coa</th>
                                                        <th class="center">Dist.Biaya</th>
                                                        <th class="center">Plant</th>
                                                        <th class="center">Line</th>
                                                        <th class="center">Mesin</th>
                                                        <th class="center">Divisi</th>
                                                        <th class="center">Proyek</th>
                                                        <th class="center">Requester</th>
                                                        <th class="center">Qty Kembali</th>
                                                        <th class="center">Hapus</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="body-item">
                                                    <tr id="last-row-item">
                                                        <td colspan="17">
                                                            
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </p>
                                </div>
                            </div>
                        </fieldset>
                        <button class="waves-effect waves-light cyan btn-small mb-1 mr-1 right mt-1" onclick="addItem()" href="javascript:void(0);">
                            <i class="material-icons left">add</i> Tambah Item
                        </button>
                        <div class="row">
                            <div class="input-field col m4 s12 step8">
                                <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note">Keterangan</label>
                            </div>
                            <div class="col s12 mt-3">
                                
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light purple mr-1" onclick="startIntro();">Panduan <i class="material-icons right">help_outline</i></button>
        <button class="btn waves-effect waves-light submit mr-1 step9" onclick="save();">Simpan <i class="material-icons right">send</i></button>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
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
                            <th class="center-align" rowspan="2">Partner Bisnis</th>
                            <th class="center-align" rowspan="2">Plant</th>
                            <th class="center-align" rowspan="2">Line</th>
                            <th class="center-align" rowspan="2">Mesin</th>
                            <th class="center-align" rowspan="2">Divisi</th>
                            <th class="center-align" rowspan="2">Gudang</th>
                            <th class="center-align" rowspan="2">Proyek</th>
                            <th class="center-align" rowspan="2">Ket.1</th>
                            <th class="center-align" rowspan="2">Ket.2</th>
                            <th class="center-align" colspan="2">Mata Uang Asli</th>
                            <th class="center-align" colspan="2">Mata Uang Konversi</th>
                        </tr>
                        <tr>
                            <th class="center-align">Debit</th>
                            <th class="center-align">Kredit</th>
                            <th class="center-align">Debit</th>
                            <th class="center-align">Kredit</th>
                        </tr>
                    
                </thead>
                <tbody id="body-journal-table">
                </tbody>
            </table>
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

        $("#table-detail th").resizable({
            minWidth: 100,
        });

        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
            
        });

        loadDataTable();

        window.table.search('{{ $code }}').draw();

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
                $('#post_date').attr('min','{{ $minDate }}');
                $('#post_date').attr('max','{{ $maxDate }}');
                $('#due_date').attr('min','{{ date("Y-m-d") }}');
                $('#document_date').attr('min','{{ date("Y-m-d") }}');
            },
            onOpenEnd: function(modal, trigger) {
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
                $("#form_data :input").prop("disabled", false);
                $('#form_data')[0].reset();
                $('#temp').val('');
                M.updateTextFields();
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }else{
                    $('.row_item').remove();
                }
                $('#material_request_id').empty();
                window.onbeforeunload = function() {
                    return null;
                };
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

        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
        });

        select2ServerSide('#material_request_id', '{{ url("admin/select2/material_request_gi") }}');
    });

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
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    'status' : $('#filter_status').val(),
                    'warehouse[]' : $('#filter_warehouse').val(),
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
                { name: 'date', className: 'center-align' },
                { name: 'note', className: '' },
                { name: 'document', searchable: false, orderable: false, className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'operation', searchable: false, orderable: false, className: 'center-align' },
            ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle',
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
                                        <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')" data-id="` + count + `"></select>
                                    </td>
                                    <td class="center" id="stock` + count + `">
                                        -
                                    </td>
                                    <td>
                                        <input name="arr_qty[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.qty_balance + `" onkeyup="formatRupiah(this);setStock('` + count + `')" style="text-align:right;width:100%;" id="rowQty`+ count +`">
                                    </td>
                                    <td class="center" id="unit` + count + `">
                                        ` + val.unit + `
                                    </td>
                                    <td class="center" id="serial` + count + `">
                                        -
                                    </td>
                                    <td>
                                        <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang ..." value="Untuk ` + val.place_name + ` ` + val.warehouse_name + `">
                                    </td>
                                    <td class="center">
                                        <select class="browser-default" id="arr_inventory_coa` + count + `" name="arr_inventory_coa[]" onchange="applyLock('` + count + `');"></select>
                                    </td>
                                    <td class="center" id="coa` + count + `">
                                        <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]" onchange="applyLock('` + count + `');"></select>
                                    </td>
                                    <td class="center">
                                        <select class="browser-default" id="arr_cost_distribution` + count + `" name="arr_cost_distribution[]"></select>
                                    </td>
                                    <td class="center">
                                        <select class="browser-default" id="arr_place` + count + `" name="arr_place[]" style="width:100px !important;">
                                            @foreach ($place as $row)
                                                <option value="{{ $row->id }}">{{ $row->code }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_line` + count + `" name="arr_line[]">
                                            <option value="">--Kosong--</option>
                                            @foreach ($line as $rowline)
                                                <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->code }}</option>
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
                                        <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                                            <option value="">--Kosong--</option>
                                            @foreach ($department as $rowdept)
                                                <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                                            @endforeach
                                        </select>    
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                                    </td>
                                    <td>
                                        <input name="arr_requester[]" id="arr_requester` + count + `" class="materialize-textarea" type="text" placeholder="Requester..." value="` + val.requester + `">
                                    </td>
                                    <td>
                                        <input name="arr_qty_return[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0,000" onkeyup="formatRupiah(this);setQtyReturn(this);" style="text-align:right;width:100%;" data-id="` + count + `">
                                    </td>
                                    <td class="center">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                </tr>
                            `);
                            $('#stock' + count).empty();
                            let optionStock = '<select class="browser-default" id="arr_item_stock' + count + '" name="arr_item_stock[]" required onchange="resetQty(`'+ count +'`)">';
                            if(val.stock_list.length > 0){
                                $.each(val.stock_list, function(i, value) {
                                    optionStock += '<option value="' + value.id + '" data-qty="' + value.qty_raw + '">' + value.name + ' ' + value.shading + ' ' + value.qty + '</option>';
                                });
                            }else{
                                optionStock += '<option value="" data-qty="0,000">--Stock tidak ditemukan--</option>';
                            }
                            optionStock += '</select>';

                            $('#stock' + count).append(optionStock);
                            $('#arr_place' + count).val(val.place_id);
                            $('#arr_line' + count).val(val.line_id);
                            $('#arr_machine' + count).val(val.machine_id);
                            $('#arr_department' + count).val(val.department_id);
                            $('#arr_requester' + count).val(val.requester);
                            if(val.item_id){
                                $('#arr_item' + count).append(`
                                    <option value="` + val.item_id +`">` + val.item_name + `</option>
                                `);
                            }
                            select2ServerSide('#arr_item' + count, '{{ url("admin/select2/item_issue") }}');
                            if(val.project_id){
                                $('#arr_project' + count).append(`
                                    <option value="` + val.project_id +`">` + val.project_name + `</option>
                                `);
                            }
                            select2ServerSide('#arr_project' + count, '{{ url("admin/select2/project") }}');
                            select2ServerSide('#arr_inventory_coa' + count, '{{ url("admin/select2/inventory_coa_issue") }}');
                            select2ServerSide('#arr_cost_distribution' + count, '{{ url("admin/select2/cost_distribution") }}');
                            select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');

                            if(val.is_activa){
                                $('#serial' + count).append(`
                                    <select class="browser-default" id="arr_serial` + count + `" name="arr_serial[]" multiple="multiple"></select>
                                `);

                                $('#arr_serial' + count).select2({
                                    placeholder: '-- Kosong --',
                                    minimumInputLength: 1,
                                    allowClear: true,
                                    cache: true,
                                    width: 'resolve',
                                    dropdownParent: $('body').parent(),
                                    maximumSelectionLength: parseInt(val.qty_balance.toString().replaceAll(".", "").replaceAll(",",".")),
                                    ajax: {
                                        url: '{{ url("admin/select2/item_serial") }}',
                                        type: 'GET',
                                        dataType: 'JSON',
                                        data: function(params) {
                                            return {
                                                search: params.term,
                                                item_id: $("#arr_item" + count).val(),
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

    function addItem(){
        var count = makeid(10);
        $('#last-row-item').before(`
            <tr class="row_item" data-id="">
                <input type="hidden" name="arr_lookable_type[]" value="">
                <input type="hidden" name="arr_lookable_id[]" value="">
                <td>
                    <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')" data-id="` + count + `"></select>
                </td>
                <td class="center" id="stock` + count + `">
                    -
                </td>
                <td>
                    <input name="arr_qty[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0,000" onkeyup="formatRupiah(this);setStock('` + count + `')" style="text-align:right;width:100%;" id="rowQty`+ count +`">
                </td>
                <td class="center" id="unit` + count + `">
                    -
                </td>
                <td class="center" id="serial` + count + `">
                    -
                </td>
                <td>
                    <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang ...">
                </td>
                <td class="center" id="inventory_coa` + count + `">
                    <select class="browser-default" id="arr_inventory_coa` + count + `" name="arr_inventory_coa[]" onchange="applyLock('` + count + `');"></select>
                </td>
                <td class="center" id="coa` + count + `">
                    <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]" onchange="applyLock('` + count + `');"></select>
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_cost_distribution` + count + `" name="arr_cost_distribution[]"></select>
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_place` + count + `" name="arr_place[]" style="width:100px !important;">
                        @foreach ($place as $row)
                            <option value="{{ $row->id }}">{{ $row->code }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select class="browser-default" id="arr_line` + count + `" name="arr_line[]" onchange="changePlace(this);" style="width:100px !important;">
                        @foreach ($line as $rowline)
                            <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->code }}</option>
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
                    <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                        <option value="">--Kosong--</option>
                        @foreach ($department as $rowdept)
                            <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                        @endforeach
                    </select>    
                </td>
                <td>
                    <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                </td>
                <td>
                    <input name="arr_requester[]" id="arr_requester` + count + `" class="materialize-textarea" type="text" placeholder="Requester...">
                </td>
                <td>
                    <input name="arr_qty_return[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0,000" onkeyup="formatRupiah(this);setQtyReturn(this);" style="text-align:right;width:100%;" data-id="` + count + `">
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        select2ServerSide('#arr_item' + count, '{{ url("admin/select2/item_issue") }}');
        select2ServerSide('#arr_inventory_coa' + count, '{{ url("admin/select2/inventory_coa_issue") }}');
        select2ServerSide('#arr_project' + count, '{{ url("admin/select2/project") }}');
        select2ServerSide('#arr_cost_distribution' + count, '{{ url("admin/select2/cost_distribution") }}');
        select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
    }

    function applyLock(code){
        $('#inventory_coa' + code + ',#coa' + code).css('pointer-events','auto'), passed = true;
        if($('#arr_inventory_coa' + code).val()){
            passed = false;
            $('#coa' + code).css('pointer-events','none');
            $('#arr_coa' + code).empty();
            $('#arr_coa' + code).append(`
                <option value="` + $('#arr_inventory_coa' + code).select2('data')[0].coa_id + `">` + $('#arr_inventory_coa' + code).select2('data')[0].coa_name + `</option>
            `);
        }
        if($('#arr_coa' + code).val() && passed == true){
            $('#inventory_coa' + code).css('pointer-events','none');
            $('#arr_inventory_coa' + code).empty();
        }
    }

    function setQtyReturn(element){
        let qty = parseFloat($(element).val().replaceAll(".", "").replaceAll(",","."));
        let max = parseFloat($('#rowQty' + $(element).data('id')).val().replaceAll(".", "").replaceAll(",","."));
        if(qty > max){
            $(element).val($('#rowQty' + $(element).data('id')).val());
        }
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

    function getRowUnit(val){
        $('#stock' + val).empty();
        $('#unit' + val).empty();
        $('#serial' + val).empty();
        if($("#arr_item" + val).val()){
            $('#unit' + val).text($("#arr_item" + val).select2('data')[0].uom);
            let optionStock = '<select class="browser-default" id="arr_item_stock' + val + '" name="arr_item_stock[]" required onchange="resetQty(`'+ val +'`)">';
            if($("#arr_item" + val).select2('data')[0].stock_list.length > 0){
                $.each($("#arr_item" + val).select2('data')[0].stock_list, function(i, value) {
                    optionStock += '<option value="' + value.id + '" data-qty="' + value.qty_raw + '">' + value.name + ' ' + value.shading + ' ' + value.qty + '</option>';
                });
            }else{
                optionStock += '<option value="" data-qty="0,000">--Stock tidak ditemukan--</option>';
            }
            optionStock += '</select>';
            $('#stock' + val).append(optionStock);

            if($("#arr_item" + val).select2('data')[0].is_activa){
                $('#serial' + val).append(`
                    <select class="browser-default" id="arr_serial` + val + `" name="arr_serial[]" multiple="multiple"></select>
                `);

                $('#arr_serial' + val).select2({
                    placeholder: '-- Kosong --',
                    minimumInputLength: 1,
                    allowClear: true,
                    cache: true,
                    width: 'resolve',
                    maximumSelectionLength: parseInt($('#rowQty' + val).val().replaceAll(".", "").replaceAll(",",".")),
                    dropdownParent: $('body').parent(),
                    ajax: {
                        url: '{{ url("admin/select2/item_serial") }}',
                        type: 'GET',
                        dataType: 'JSON',
                        data: function(params) {
                            return {
                                search: params.term,
                                item_id: $("#arr_item" + val).val(),
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data.items
                            }
                        }
                    }
                });
            }else{
                $('#serial' + val).append(` - `);
            }
        }else{
            $("#arr_item" + val).empty();
            $('#stock' + val).append(` - `);
            $('#unit' + val).append(` - `);
            $('#serial' + val).append(` - `);
        }
    }

    function resetQty(val){
        $('#rowQty' + val).val('0,000');
    }

    function setStock(val){
        if($("#arr_item" + val).val()){
            let qtyMax = parseFloat($('#arr_item_stock' + val).find(':selected').data('qty').toString().replaceAll(".", "").replaceAll(",","."));
            let qtyInput = parseFloat($('#rowQty' + val).val().replaceAll(".", "").replaceAll(",","."));
            if(qtyInput > qtyMax){
                $('#rowQty' + val).val(formatRupiahIni(qtyMax.toFixed(3).toString().replace('.',',')));
            }
            if($('#arr_serial' + val).length > 0){
                $('#arr_serial' + val).empty();
                $('#arr_serial' + val).select2({
                    placeholder: '-- Kosong --',
                    minimumInputLength: 1,
                    allowClear: true,
                    cache: true,
                    width: 'resolve',
                    maximumSelectionLength: parseInt($('#rowQty' + val).val().replaceAll(".", "").replaceAll(",",".")),
                    dropdownParent: $('body').parent(),
                    ajax: {
                        url: '{{ url("admin/select2/item_serial") }}',
                        type: 'GET',
                        dataType: 'JSON',
                        data: function(params) {
                            return {
                                search: params.term,
                                item_id: $("#arr_item" + val).val(),
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
        }else{
            $('#rowQty' + val).val('0,000');
        }
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
                var formData = new FormData($('#form_data')[0]), passed = true, passedSerial = true;

                formData.delete('arr_item_stock[]');
                formData.delete('arr_qty[]');
                formData.delete('arr_inventory_coa[]');
                formData.delete('arr_coa[]');
                formData.delete('arr_place[]');
                formData.delete('arr_line[]');
                formData.delete('arr_machine[]');
                formData.delete('arr_department[]');
                formData.delete('arr_project[]');
                formData.delete('arr_serial[]');
                formData.delete("arr_cost_distribution[]");
                
                $('select[name^="arr_item_stock"]').each(function(index){
                    if(!$(this).val()){
                        passed = false;
                    }else{
                        formData.append('arr_item_stock[]',$(this).val());
                    }
                });

                $('select[name^="arr_item[]"]').each(function(index){
                    /* if($('#arr_serial' + $(this).data('id')).length > 0){
                        let arr = $('#arr_serial' + $(this).data('id')).val();
                        if(arr.length > 0){
                            formData.append('arr_serial[]',$('#arr_serial' + $(this).data('id')).val());
                        }else{
                            passedSerial = false;
                        }
                    }else{ */
                        formData.append('arr_serial[]','');
                    /* } */
                });

                $('input[name^="arr_qty[]"]').each(function(index){
                    if(!$(this).val() || $(this).val() == '0'){
                        passed = false;
                    }else{
                        formData.append('arr_qty[]',$(this).val());
                    }
                });

                $('select[name^="arr_inventory_coa"]').each(function(index){
                    if(!$(this).val() && !$('select[name^="arr_coa[]"]').eq(index).val()){
                        passed = false;
                    }
                });

                $('select[name^="arr_inventory_coa"]').each(function(index){
                    formData.append('arr_inventory_coa[]',($(this).val() ? $(this).val() : ''));
                    formData.append('arr_coa[]',($('select[name^="arr_coa[]"]').eq(index).val() ? $('select[name^="arr_coa[]"]').eq(index).val() : ''));
                    formData.append('arr_cost_distribution[]',($('select[name^="arr_cost_distribution[]"]').eq(index).val() ? $('select[name^="arr_cost_distribution[]"]').eq(index).val() : ''));
                    formData.append('arr_place[]',($('select[name^="arr_place[]"]').eq(index).val() ? $('select[name^="arr_place[]"]').eq(index).val() : ''));
                    formData.append('arr_line[]',($('select[name^="arr_line[]"]').eq(index).val() ? $('select[name^="arr_line[]"]').eq(index).val() : ''));
                    formData.append('arr_machine[]',($('select[name^="arr_machine[]"]').eq(index).val() ? $('select[name^="arr_machine[]"]').eq(index).val() : ''));
                    formData.append('arr_department[]',($('select[name^="arr_department[]"]').eq(index).val() ? $('select[name^="arr_department[]"]').eq(index).val() : ''));
                    formData.append('arr_project[]',($('select[name^="arr_project[]"]').eq(index).val() ? $('select[name^="arr_project[]"]').eq(index).val() : ''));
                });

                if(passedSerial){
                    if(passed){
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
                    }else{
                        swal({
                            title: 'Ups!',
                            text: 'Mohon maaf, stok item / qty / coa tidak boleh kosong atau diisi 0. Dan coa atau tipe pengeluaran harus dipilih salah satu.',
                            icon: 'warning'
                        });
                    }
                }else{
                    swal({
                        title: 'Ups!',
                        text: 'Mohon maaf, salah satu item / lebih harus ditentukan nomor serialnya karena merupakan item aktiva.',
                        icon: 'warning'
                    });
                }
            }
        });
    }

    function success(){
        loadDataTable();
        /* $('#modal1').modal('close'); */
        $("#form_data :input").prop("disabled", true);
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
                $('.row_item').each(function(){
                    $(this).remove();
                });

                $('#empty-item').remove();
            },
            success: function(response) {
                loadingClose('#main');
                $('#modal1').modal('open');
                $('#temp').val(id);
                $('#code_place_id').val(response.code_place_id).formSelect();
                $('#code').val(response.code);
                $('#company_id').val(response.company_id).formSelect();
                $('#note').val(response.note);
                $('#post_date').val(response.post_date);
                
                if(response.details.length > 0){
                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        $('#last-row-item').before(`
                            <tr class="row_item" data-id="` + val.reference_id + `">
                                <input type="hidden" name="arr_lookable_type[]" value="` + val.lookable_type + `">
                                <input type="hidden" name="arr_lookable_id[]" value="` + val.lookable_id + `">
                                <td>
                                    <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')" data-id="` + count + `"></select>
                                </td>
                                <td class="center" id="stock` + count + `">
                                    -
                                </td>
                                <td>
                                    <input name="arr_qty[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiah(this);setStock('` + count + `')" style="text-align:right;width:100%;" id="rowQty`+ count +`">
                                </td>
                                <td class="center" id="unit` + count + `">
                                    ` + val.uom + `
                                </td>
                                <td class="center" id="serial` + count + `">
                                    -
                                </td>
                                <td>
                                    <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang ..." value="` + val.note + `">
                                </td>
                                <td class="center" id="inventory_coa` + count + `">
                                    <select class="browser-default" id="arr_inventory_coa` + count + `" name="arr_inventory_coa[]" onchange="applyLock('` + count + `');"></select>
                                </td>
                                <td class="center" id="coa` + count + `">
                                    <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]" onchange="applyLock('` + count + `');"></select>
                                </td>
                                <td class="center">
                                    <select class="browser-default" id="arr_cost_distribution` + count + `" name="arr_cost_distribution[]"></select>
                                </td>
                                <td class="center">
                                    <select class="browser-default" id="arr_place` + count + `" name="arr_place[]" style="width:100px !important;">
                                        @foreach ($place as $row)
                                            <option value="{{ $row->id }}">{{ $row->code }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_line` + count + `" name="arr_line[]" onchange="changePlace(this);" style="width:100px !important;">
                                        @foreach ($line as $rowline)
                                            <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->code }}</option>
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
                                    <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                                        <option value="">--Kosong--</option>
                                        @foreach ($department as $rowdept)
                                            <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                                        @endforeach
                                    </select>    
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                                </td>
                                <td>
                                    <input name="arr_requester[]" id="arr_requester` + count + `" class="materialize-textarea" type="text" placeholder="Keterangan barang ..." value="` + val.requester + `">
                                </td>
                                <td>
                                    <input name="arr_qty_return[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.qty_return + `" onkeyup="formatRupiah(this);setQtyReturn(this);" style="text-align:right;width:100%;" data-id="` + count + `">
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);

                        $('#stock' + count).empty();
                        let optionStock = '<select class="browser-default" id="arr_item_stock' + count + '" name="arr_item_stock[]" required onchange="resetQty(`'+ count +'`)">';
                        if(val.stock_list.length > 0){
                            $.each(val.stock_list, function(i, value) {
                                optionStock += '<option value="' + value.id + '" data-qty="' + (value.id == val.item_stock_id ? val.qtyraw : value.qty_raw ) + '" ' + (value.id == val.item_stock_id ? 'selected' : '') + '>' + value.name + ' ' + value.shading + ' ' + value.qty + '</option>';
                            });
                        }else{
                            optionStock += '<option value="" data-qty="0,000">--Stock tidak ditemukan--</option>';
                        }
                        optionStock += '</select>';

                        $('#stock' + count).append(optionStock);

                        $('#arr_item' + count).append(`
                            <option value="` + val.item_id + `">` + val.item_name + `</option>
                        `);
                        if(val.coa_id){
                            $('#arr_coa' + count).append(`
                                <option value="` + val.coa_id + `">` + val.coa_name + `</option>
                            `);
                        }
                        if(val.coa_inventory_id){
                            $('#arr_coa' + count).append(`
                                <option value="` + val.coa_inventory_id + `">` + val.coa_inventory_name + `</option>
                            `);
                        }
                        if(val.inventory_coa_id){
                            $('#arr_inventory_coa' + count).append(`
                                <option value="` + val.inventory_coa_id + `">` + val.inventory_coa_name + `</option>
                            `);
                        }
                        select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
                        select2ServerSide('#arr_item' + count, '{{ url("admin/select2/item_issue") }}');
                        select2ServerSide('#arr_inventory_coa' + count, '{{ url("admin/select2/inventory_coa_issue") }}');
                        $('#arr_place' + count).val(val.place_id);
                        $('#arr_line' + count).val(val.line_id);
                        $('#arr_machine' + count).val(val.machine_id);
                        $('#arr_department' + count).val(val.department_id);
                        if(val.project_id){
                            $('#arr_project' + count).append(`
                                <option value="` + val.project_id +`">` + val.project_name + `</option>
                            `);
                        }
                        select2ServerSide('#arr_project' + count, '{{ url("admin/select2/project") }}');

                        if(val.cost_distribution_id){
                            $('#arr_cost_distribution' + count).append(`
                                <option value="` + val.cost_distribution_id + `">` + val.cost_distribution_name + `</option>
                            `);
                        }
                        select2ServerSide('#arr_cost_distribution' + count, '{{ url("admin/select2/cost_distribution") }}');

                        if(val.is_activa){
                            $('#serial' + count).empty();
                            $('#serial' + count).append(`
                                <select class="select2 browser-default" id="arr_serial` + count + `" name="arr_serial[]" multiple="multiple"></select>
                            `);

                            $.each(val.list_serial, function(i, value) {
                                $('#arr_serial' + count).append(`
                                    <option value="` + value.serial_id + `" selected>` + value.serial_number + `</value>
                                `);
                            });

                            $('#arr_serial' + count).select2({
                                placeholder: '-- Kosong --',
                                minimumInputLength: 1,
                                allowClear: true,
                                cache: true,
                                width: 'resolve',
                                dropdownParent: $('body').parent(),
                                maximumSelectionLength: parseInt(val.qty.toString().replaceAll(".", "").replaceAll(",",".")),
                                ajax: {
                                    url: '{{ url("admin/select2/item_serial") }}',
                                    type: 'GET',
                                    dataType: 'JSON',
                                    data: function(params) {
                                        return {
                                            search: params.term,
                                            item_id: $("#arr_item" + count).val(),
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

    function printPreview(code){
        $.ajax({
            url: '{{ Request::url() }}/approval/' + code,
            type:'GET',
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            complete: function() {
                
            },
            success: function(data){
                loadingClose('.modal-content');
                $('#modal2').modal('open');
                $('#show_print').html(data);
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

    function startIntro(){
        introJs().setOptions({
            exitOnOverlayClick : false,
            steps: [
                {
                    title : 'GRPO',
                    intro : 'Form ini digunakan untuk membuat GRPO dari purchase order yang terkait '
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
                    title : 'Perusahaan',
                    element : document.querySelector('.step3'),
                    intro : 'Perusahaan tempat memo ini dibuat atau diperuntukkan' 
                },
                {
                    title : 'Tgl. Posting',
                    element : document.querySelector('.step4'),
                    intro : 'Tanggal post akan menentukan tanggal jurnal untuk beberapa form yang terhubung dengan jurnal. Hati - hati dalam menentukan tanggal posting.' 
                },
                {
                    title : 'File Lampiran',
                    element : document.querySelector('.step5'),
                    intro : 'Silahkan unggah file lampiran. untuk saat ini hanya bisa mengakomodir 1 file lampiran saja. Jika ingin menambahkan file lebih dari 1, silahkan gabungkan file anda menjadi pdf.' 
                },
                {
                    title : ' Item Request (Jika Ada)',
                    element : document.querySelector('.step6'),
                    intro : 'Digunakan apabila ada Item Request terkain yang nantinya item dari Item Request akan diinput ke dalam list detail produk.' 
                },
                {
                    title : 'Detail Produk',
                    element : document.querySelector('.step7'),
                    intro : 'List Produk yang terkait' 
                },
                {
                    title : 'Keterangan',
                    element : document.querySelector('.step8'),
                    intro : 'Silahkan isi / tambahkan keterangan untuk dokumen ini untuk dimunculkan di bagian bawah tabel detail produk nantinya, ketika dicetak.'
                },
                {
                    title : 'Tombol Simpan',
                    element : document.querySelector('.step9'),
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