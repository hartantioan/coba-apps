<!-- BEGIN: Page Main-->
<style>
     #modal_confirmation {
        top:0px !important;
    }
    #modal4 {
        top:0px !important;
    }
    #modal1 {
        top:0px !important;
    }
</style>
<div id="main">
    <div class="row">
        <div class="col s12">
            <div class="container">
                
            </div>
            <div class="content-overlay"></div>
        </div>
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
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">Filter</h4>
                                    <div class="row">
                                        <div class="col m4 s6 ">
                                            <label for="start-date" style="font-size:1rem;">Tanggal Mulai :</label>
                                            <div class="input-field col s12">
                                            <input type="date" max="{{ date('9999'.'-12-31') }}" id="start-date" name="start-date"  onchange="loadDataTable()">
                                            </div>
                                        </div>
                                        <div class="col m4 s6 ">
                                            <label for="finish-date" style="font-size:1rem;">Tanggal Akhir :</label>
                                            <div class="input-field col s12">
                                                <input type="date" max="{{ date('9999'.'-12-31') }}" id="finish-date" name="finish-date"  onchange="loadDataTable()">
                                            </div>
                                        </div>
                                        <div class="col m4 s6 ">
                                            <div class="input-field col s12">
                                                <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="excelMultiData();">
                                                    <i class="material-icons hide-on-med-and-up">view_list</i>
                                                    <span class="hide-on-small-onl">EXCEL</span>
                                                    <i class="material-icons right">view_list</i>
                                                </a>
                                            </div>
                                            <div class="input-field col s12">
                                                <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="excelFilterTable();">
                                                    <i class="material-icons hide-on-med-and-up">subtitles</i>
                                                    <span class="hide-on-small-onl">EXCEL DATA TABLE</span>
                                                    <i class="material-icons right">subtitles</i>
                                                </a>
                                            </div>
                                           
                                        </div>
                                        <div class="col m10 s6 ">
                                            <label for="textarea_multiple">Multiple Find (,)</label>
                                            <div class="input-field col s12">
                                                <textarea type="text" id="textarea_multiple" name="textarea_multiple" class="materialize-textarea" onchange="loadDataTable()"></textarea>
                                            </div>
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
                                                        <th align="center"  style="background-color: navy; color: white;border: 1px solid white;">No</th>
                                                        <th align="center"  style="background-color: navy; color: white;border: 1px solid white;">Kode</th>
                                                        <th align="center"  style="background-color: navy; color: white;border: 1px solid white;">User</th>
                                                        <th align="center"  style="background-color: navy; color: white;border: 1px solid white;">Tanggal Post</th>

                                                        <th align="center"  style="background-color: navy; color: white;border: 1px solid white;">Approval</th>
     
                                                        <th align="center"  style="background-color: navy; color: white;border: 1px solid white;">Status</th>
                                                        <th align="center"  style="background-color: navy; color: white;border: 1px solid white;">Action</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Tambah/Edit Serah Terima Pajak</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <fieldset>
                            <legend>Informasi Utama</legend>
                            <div class="row">
                                <div class="input-field col m3 s12 step1">
                                    <input type="hidden" id="temp" name="temp">
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
                                    <select class="form-control" id="company_id" name="company_id">
                                        @foreach ($company as $row)
                                            <option value="{{ $row->id }}">{{ $row->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="" for="company_id">Perusahaan</label>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset style="min-width: 100%;overflow:auto;" class="step5">
                            <legend>List Faktur Pajak</legend>
                            <div class="col m12 s12" style="overflow:auto;width:100% !important;" id="table-item">
                                <p class="mt-1 mb-2">
                                <div id="datatable_buttons_multi"></div>
                                <div class="right">
                                    <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="refreshTax();">
                                        <i class="material-icons hide-on-med-and-up">refresh</i>
                                        <span class="hide-on-small-onl">Refresh</span>
                                        <i class="material-icons right">refresh</i>
                                    </a>
                                </div>
                                <div id="right">Silahkan pilih baris hingga berwarna abu-abu untuk bisa dimasukkan ke daftar.</div>
                                <table id="table_item">
                                    <thead>
                                        <tr>
                                            <th align="center" rowspan="2"  style="background-color: navy; color: white;border: 1px solid white;">ID</th>
                                            <th align="center" rowspan="2"  style="background-color: navy; color: white;border: 1px solid white;">No</th>
                                            <th align="center" colspan="2" style="background-color: navy; color: white;border: 1px solid white;">Faktur Pajak</th>
                                            <th align="center" colspan="3" style="background-color: navy; color: white;border: 1px solid white;">Supplier</th>
                                            <th align="center" rowspan="2" style="background-color: navy; color: white;border: 1px solid white;">DPP</th>
                                            <th align="center" rowspan="2" style="background-color: navy; color: white;border: 1px solid white;">PPN</th>
                                            <th align="center" rowspan="2" style="background-color: navy; color: white;border: 1px solid white;">Nama Barang</th>
                                        </tr>
                                        <tr>
                                            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Tanggal</th>
                                            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Nomor</th>
                                            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">NPWP</th>
                                            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Nama </th>
                                            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Alamat Lengkap</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body-item"></tbody>
                                </table>
                                </p>
                            </div>
                        </fieldset>
                        <div class="row">
                            
                            <div class="col s12 mt-3">
                                <button class="btn waves-effect waves-light right submit step10" onclick="save();">Simpan <i class="material-icons right">send</i></button>
                            </div>
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
<div id="modal_confirmation" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="">
                <div class="card">
                    <div class="card-content row">
                        <h4 class="card-title">Barcode Scanner</h4>
                        <div class="col s12">
                            <div id="validation_alert_barcode" style="display:none;"></div>
                        </div>
                        <form id="barcode-form" action="{{ Request::url() }}/confirm_scan" method="POST">
                            @csrf
                            <input type="hidden" id="temp_con" name="temp_confirmation">
                            <input type="text" name="barcode" id="barcode-input" autofocus>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col s12" id="">
                <input type="hidden" id="temp_confirmation" name="temp_confirmation">
                <form class="row" id="form_detail" onsubmit="return false;">
                    <div class="col s12 m6" id="document_tax_code" style="margin-bottom:2em;">
                       
                    </div>
                    <div class="col s12 m6" id="document_tax_post_date" style="margin-bottom:2em;">

                    </div>
                    <div class="col s12 m6" id="document_tax_note">

                    </div>
                    <div class="col s12 m6" id="document_tax_created_by">

                    </div>
                    <div class="col s12 m12">
                        <table>
                            <thead>
                                <tr>
                                    <th align="center" rowspan="1"  style="background-color: navy; color: white;border: 1px solid white;">No</th>
                                    <th align="center" colspan="1" style="background-color: navy; color: white;border: 1px solid white;">Faktur Pajak</th>
                                    <th align="center" colspan="1" style="background-color: navy; color: white;border: 1px solid white;">Supplier</th>
                                    <th align="center" rowspan="1" style="background-color: navy; color: white;border: 1px solid white;">Nama Barang</th>
                                    <th align="center" rowspan="1" style="background-color: navy; color: white;border: 1px solid white;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="body_confirm">
                            </tbody>
                        </table>
                    </div>
                    <div class="col s12 mt-3">
                        <button class="btn waves-effect waves-light right submit step10" onclick="saveDetail();">Simpan <i class="material-icons right">send</i></button>
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
    var table_multi;
    var arr_tax=[];
    $(function() {

        loadDataTable();

        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
                $('#code').focus();
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                table_multi = $('#table_item').DataTable({
                    "responsive": true,
                    scrollY: '50vh',
                    scrollCollapse: true,
                    "iDisplayInLength": 10,
                    dom: 'Blfrtip',
                    buttons: [
                        'selectAll',
                        'selectNone'
                    ],
                    "order": [[0, 'desc']],
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
                    "columns": [
                        {"data": 'id', visible: false },
                        {"data": "no"},
                        {"data": "post_date"},
                        {"data": "code"},
                        {"data": "npwp_number", className:"center-align"},
                        {"data": "npwp_name", className:"center-align"},
                        {"data": "npwp_address", className:"center-align"},
                        {"data": "total", className:"center-align"},
                        {"data": "tax", className:"center-align"},
                        {"data": "item", className:"center-align"},
                      
                    ],
                    rowCallback: function( row, data ) {
                        if ($(row).hasClass('selected')) {
                            this.api().row( row ).select();
                        }
                    },
                    createdRow: function(row, data, dataIndex) {
                        $(row).addClass('row_item');
                        $(row).attr('data-id', data.id);
                        $(row).attr('data-type', data.type);
                        $(row).attr('data-balance', data.final);
                        if(data.selected){
                            $(row).addClass('selected');
                        }
                    },
                });
                $('#table_item_wrapper > .dt-buttons').appendTo('#datatable_buttons_multi');
                $('select[name="table_item_length"]').addClass('browser-default');

                getTaxDocument();
                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                M.updateTextFields();
                window.onbeforeunload = function() {
                    return null;
                };
                $('#table_item').DataTable().clear().destroy();
                M.updateTextFields();
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

        $('#modal_confirmation').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#body_confirm').empty();
                $('#temp_confirmation').empty();
                $('#temp_con').empty();
                $('#document_tax_note').empty();
                $('#document_tax_created_by').empty();
                $('#document_tax_post_date').empty();
                $('#document_tax_code').empty();
            }
        });
    });

    $('#barcode-form').submit(function(event) {
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
                    $('#validation_alert_barcode').hide();
                    $('#validation_alert_barcode').html('');
                },
                success: function(response) {
                    if(response.status == 200) {
                        success();
                        confirm($('#temp_con').val());
                        M.toast({
                            html: response.message
                        });

                        $('#barcode-form')[0].reset();
                    }else if(response.status == 422) {
                        $('#barcode-form')[0].reset();
                        $('#validation_alert_barcode').show();
                        $('.modal-content').scrollTop(0);
                        $('#validation_alert_barcode').append(`
                            <div class="card-alert card red">
                                <div class="card-content white-text">
                                    <p>` +response.error+` </p>
                                </div>
                                <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                        `);
                    } else {
                        $('#barcode-form')[0].reset();
                        M.toast({
                            html: response.message
                        });
                    }
                },
                error: function(response) {
                    $('#barcode-form')[0].reset();
                }
            });

    });

    function loadDataTable() {
		window.table = $('#datatable_serverside').DataTable({
            "scrollCollapse": true,
            "scrollY": '400px',
            "responsive": false,
            "scrollX":true,
           
            "serverSide": true,
            "deferRender": true,
            "destroy": true,
            "iDisplayInLength": 10,
            
            "order": [[1, 'desc']],
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    start_date : $('#start-date').val(),
                    finish_date : $('#finish-date').val(),
                    multiple : $('#textarea_multiple').val(),
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
                        { name: 'id', searchable: false, className: 'center-align' },
                        { name: 'transaction_code', className: 'center-align' },
                        { name: 'date', className: 'center-align' },
                        { name: 'npwp_number', className: 'center-align' },
                        { name: 'npwp_name', className: 'center-align' },
                        { name: 'npwp_address', className: 'center-align' },
                        { name: 'action', searchable: false, orderable: false, className: 'center-align' }
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

    function refreshTax(){
        let passed = true;
        $.map(table_multi.rows('.selected').nodes(), function (item) {
            passed = false;
        });
        if(passed){
            getTaxDocument();
        }else{
            swal({
                title: "Apakah anda yakin?",
                text: "Data yang terpilih akan tereset!",
                icon: 'warning',
                dangerMode: true,
                buttons: {
                cancel: 'Tidak, jangan!',
                delete: 'Ya, lanjutkan!'
                }
            }).then(function (willDelete) {
                if (willDelete) {
                    getTaxDocument();
                }
            });
        }
    }

    function getTaxDocument(){
        table_multi.rows().remove();
        $.ajax({
            url: '{{ Request::url() }}/get_tax_for_handover_tax',
            type: 'POST',
            dataType: 'JSON',
            data: {
             
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            success: function(response) {
                loadingClose('.modal-content');

                if(response.length > 0){
                    if(!$('#temp').val()){
                        table_multi.rows().remove();
                    }
                    $.each(response, function(i, val) {
                        table_multi.row.add({
                            'id': val.id,
                            'no': val.no,
                            'post_date': val.post_date,
                            'code': val.code,
                            'npwp_number': val.npwp_number,
                            'npwp_name': val.npwp_name,
                            'npwp_address': val.npwp_address,
                            'total': val.total,
                            'tax': val.tax,
                            'item': val.item,
                            'selected': '',
                        });
                    });
                }else{
                    $('#body-item').empty();
                }

                table_multi.draw();
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

    function save(){
        
        var formData = new FormData($('#form_data')[0]);	
        $.map(table_multi.rows('.selected').nodes(), function (item) {
            console.log($(item).data('id'));
            formData.append('arr_tax[]',$(item).data('id'));
            passed = true;
        });
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
                loadingClose('.modal-content');
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
            }
        });
    }

    function saveDetail(){
        var checkedIds = [];
        var uncheckedIds = [];

        $('input[name="arr_tax_detail"]').each(function() {
            if ($(this).is(':checked')) {
                checkedIds.push($(this).data('id'));
            } else {
                uncheckedIds.push($(this).data('id'));
            }
        });
        $.ajax({
            url: '{{ Request::url() }}/save_detail',
            type: 'POST',
            dataType: 'JSON',
            data: {
                checkedIds: checkedIds,
                uncheckedIds: uncheckedIds,
                temp:$('#temp_confirmation').val(),
            },
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
                    $('#modal_confirmation').modal('close');
                    success();
                    M.toast({
                        html: response.message
                    });
                } else if(response.status == 422) {
                    $('#validation_alert').show();
                    $('.modal-content').scrollTop(0);
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
                loadingClose('.modal-content');
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
            }
        });
    }

    function excelMultiData(){
        var arr_id_temp=[];
        $.map(window.table.rows('.selected').nodes(), function (item) {
            var poin = $(item).find('td:nth-child(3)').text().trim();
            arr_id_temp.push(poin);
        });

        window.location = "{{ Request::url() }}/export?no_faktur=" + arr_id_temp; 
    }

    function excelFilterTable(){
        var endDate = $('#finish-date').val();
        var startDate = $('#start-date').val();
        var search = window.table.search();
        var multiple = $('#textarea_multiple').val();
        window.location = "{{ Request::url() }}/export_data_table?start_date=" + startDate+ "&finish_date=" + endDate + "&search=" + search + "&multiple=" + multiple; 
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
    


    function success(){
        loadDataTable();
        $('#modal1').modal('close');
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

    function confirm(id){
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
                if (!$('#modal_confirmation').hasClass('open')) {
                    $('#modal_confirmation').modal('open');
                }
                $('#document_tax_note').empty();
                $('#document_tax_created_by').empty();
                $('#document_tax_post_date').empty();
                $('#document_tax_code').empty();
                $('#temp_con').val(id);
                
                $('#temp_confirmation').val(id);
               
                $('#document_tax_post_date').append(`<p>Post Date:`+response.post_date+`<p>`);
                $('#document_tax_created_by').append(`<p>Created By:`+response.users+`<p>`);
                $('#document_tax_code').append(`<p>Kode Tanda Terima:`+response.code+`<p>`);
                    if(response.details.length > 0){
                        $('#body_confirm').empty();
                        $.each(response.details, function(i, val) {
                            $('#body_confirm').append(
                                ` <tr class="row_item">
                                    <td>
                                        ` +val.no + `
                                    </td>
                                    <td>
                                        ` +val.code + `
                                    </td>
                                    <td>
                                        ` +val.npwp_name + `
                                    </td>
                                    <td>
                                        ` +val.item + `
                                    </td>
                                    <td>
                                        <label>
                                             ` +val.check+ `
                                            <span></span>
                                        </label>
                                    </td>
                                </tr>`
                            );
                            
                        });

                       
                    }

                $('.modal-content').scrollTop(0);
                $('#note').focus();
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
                $('#code').val(response.code);
                $('#post_date').val(response.post_date);
                $('#code_place_id').val(response.place_id).formSelect();
                $('#company_id').val(response.company_id).formSelect();
                $('#note').val(response.note);
                setTimeout(function(){
                    if(response.details.length > 0){
                        $('#body-item').empty();
                        $.each(response.details, function(i, val) {
                            table_multi.row.add({
                                'id': val.id,
                                'no': val.no,
                                'post_date': val.post_date,
                                'code': val.code,
                                'npwp_number': val.npwp_number,
                                'npwp_name': val.npwp_name,
                                'npwp_address': val.npwp_address,
                                'total': val.total,
                                'tax': val.tax,
                                'item': val.item,
                                'selected': '1',
                            });
                        });

                        table_multi.draw();
                    }
                }, 1000);

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

    function print(){
        var search = window.table.search();
        
        $.ajax({
            type : "POST",
            url  : '{{ Request::url() }}/print',
            data : {
                search : search
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            cache: false,
            success: function(data){
                var w = window.open('about:blank');
                w.document.open();
                w.document.write(data);
                w.document.close();
            }
        });
    }
</script>