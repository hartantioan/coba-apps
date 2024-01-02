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

    .select-wrapper {
        height: 3.6rem !important;
    }

    .select2-container {
        height: 3.6rem !important;
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
                            
                        </ol>
                    </div>
                    <div class="col s4 m6 l6">
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="printData();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">Print</span>
                            <i class="material-icons right">local_printshop</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="loadDataTable()">
                            <i class="material-icons hide-on-med-and-up">refresh</i>
                            <span class="hide-on-small-onl">Refresh</span>
                            <i class="material-icons right">refresh</i>
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
                            <input type="hidden" id="selectedCode" value="{{ request()->query('code') }}">
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
                                            <table id="datatable_serverside" class="display responsive-table wrap">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Kode</th>
                                                        <th>Pembuat</th>
                                                        <th>Perusahaan</th>
                                                        <th>Orang yang Bersangkutan</th>
                                                        <th>Tanggal Post</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="height: 100% !important;width: 100% !important;min-height:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Tambah/Edit {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    
                    <div class="col s12">
                        <input type="hidden" id="temp" name="temp">
                        <div class="input-field col m2 s12">
                            <input id="code" name="code" type="text" value="{{ $newcode }}" readonly>
                            <label class="active" for="code">No. Dokumen</label>
                        </div>
                        <div class="input-field col m1 s12">
                            <select class="form-control" id="code_place_id" name="code_place_id" onchange="getCode(this.value);">
                                <option value="">--Pilih--</option>
                                @foreach ($place as $rowplace)
                                    <option value="{{ $rowplace->code }}">{{ $rowplace->code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-field col s6">
                            <select class="select2 browser-default" id="account_id" name="account_id" >
                                <option value="">--Pilih ya--</option>
                            </select>
                            <label class="active" for="account_id">Select Employee</label>
                        </div>
                        <div class="input-field col s6 employee_inputs" id="place_select">
                            <select id="company_id" name="company_id">
                                <option value=""></option>
                                @foreach($company as $row)
                                    <option value="{{ $row->id }}">{{ $row->code }}</option>
                                @endforeach
                            </select>
                            <label for="company_id">Perusahaan</label>
                        </div>
                        <div class="col m12 s12 step9" style="overflow:auto;width:100% !important;">
                            <p class="mt-2 mb-2">
                                <h4>Detail Jadwal Yang ingin diganti</h4>
                                <table class="bordered" style="width:1800px;">
                                    <thead>
                                        <tr>
                                            <th class="center">Schedule</th>
                                            <th class="center">Date</th>
                                            <th class="center">Shift</th>
                                            <th class="center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body-item">
                                        <tr class="row_item">
                                            <td>
                                                <select class="browser-default item-array" id="arr_schedule0" name="arr_schedule[]"></select>
                                            </td>
                                            <td>
                                                <input name="arr_date[]" type="date">
                                            </td>
                                            <td class="center">
                                                <select class="browser-default shift_array" id="arr_shift0" name="arr_shift[]"></select>
                                            </td>
                                            <td class="center">
                                                <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                                    <i class="material-icons">delete</i>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr id="last-row-item">
                                            <td colspan="3">
                                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addItem()" href="javascript:void(0);">
                                                    <i class="material-icons left">add</i> Tambah 1
                                                </a>
                                               
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </p>
                        </div>
                    
                        <div class="file-field input-field col m3 s12 step16">
                            <input id="post_date" name="post_date"   type="date" placeholder="Tanggal Post">
                            <label class="active" for="post_date">Tanggal Post</label>
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
    var tempuser = 0;
    $(function() {
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });
   
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                $('#start_time_div, #end_time_div').hide();
                M.updateTextFields();
            }
        });
        

        loadDataTable();
        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
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
        
        $('#arr_schedule0').select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/schedule") }}',
                type: 'GET',
                dataType: 'JSON',
                data: function(params) {
                    let schedule_id = [];
                    $('select[name^="arr_schedule[]"]').each(function(index){
                        if($(this).val()){
                            schedule_id.push($(this).val());
                        }
                    });
                    return {
                        search: params.term,
                        account_id: $('#account_id').val(),
                        shift_request_id: schedule_id,
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.items
                    }
                }
            }
        });
       
        $('#arr_shift0').select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/shift_by_department") }}',
                type: 'GET',
                dataType: 'JSON',
                data: function(params) {
                    let shift_ids = [];
                    $('select[name^="arr_shift[]"]').each(function(index){
                        if($(this).val()){
                            shift_ids.push($(this).val());
                        }
                    });
                    return {
                        search: params.term,
                        account_id: $('#account_id').val(),
                        shift_id:shift_ids,
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.items
                    }
                }
            }
        });

        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
           
        });
        select2ServerSide('#account_id', '{{ url("admin/select2/employee") }}');
        select2ServerSide('#leave_type_id', '{{ url("admin/select2/leave_type") }}');
        
    });

    function addItem(){
        var count = makeid(10);
        $('#last-row-item').before(`
        <tr class="row_item">
                <td>
                    <select class="browser-default item-array" id="arr_schedule` + count + `" name="arr_schedule[]"></select>
                </td>
                <td>
                    <input name="arr_date[]" type="date">
                </td>
                <td class="center">
                    <select class="browser-default shift_array" id="arr_shift`+count+`" name="arr_shift[]"></select>
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
       
        
        $('#arr_schedule'+count).select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/schedule") }}',
                type: 'GET',
                dataType: 'JSON',
                data: function(params) {
                    let schedule_id = [];
                    $('select[name^="arr_schedule[]"]').each(function(index){
                        if($(this).val()){
                            schedule_id.push($(this).val());
                        }
                    });
                    return {
                        search: params.term,
                        account_id: $('#account_id').val(),
                        shift_request_id: schedule_id,
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.items
                    }
                }
            }
        });

        $('#arr_shift'+count).select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/shift_by_department") }}',
                type: 'GET',
                dataType: 'JSON',
                data: function(params) {
                    let shift_ids = [];
                    $('select[name^="arr_shift[]"]').each(function(index){
                        if($(this).val()){
                            shift_ids.push($(this).val());
                        }
                    });
                    return {
                        search: params.term,
                        account_id: $('#account_id').val(),
                        shift_id:shift_ids,
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

    function changeIjin(){
        if($("#leave_type_id").val()){
            if($("#leave_type_id").select2('data')[0].type=='1'){
                $('#start_time_div, #end_time_div').hide();
            }else{
                $('#start_time_div, #end_time_div').show();
            }
        }else{
            $('#start_time_div, #end_time_div').hide();
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
                    type : $('#filter_type').val(),
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
                { name: 'user_id', searchable: false, className: 'center-align details-control' },
                { name: 'code', className: 'center-align' },
                { name: 'nomor', className: 'center-align' },
                { name: 'account_id', className: 'center-align' },
                { name: 'company_id', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'status', className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'right-align' },
            ],
            dom: 'Blfrtip',
            buttons: [
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
                style: 'multi',
                selector: 'td:not(.btn-floating)'
            },
        });
        $('.dt-buttons').appendTo('#datatable_buttons');
        $('select[name="datatable_serverside_length"]').addClass('browser-default');
	}

    function save(){
		const urlParams = new URLSearchParams(window.location.search);
        const id = urlParams.get('id');	
        var formData = new FormData($('#form_data')[0]);
        formData.append('id',id);
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
               
                $('#account_id').empty().append(`
                    <option value="` + response.account_id + `">` + response.account.name + `</option>
                `);

                $('#code').val(response.code);

                $('#leave_type_id').empty().append(`
                    <option value="` + response.leave_type_id + `">` + response.leaveType.name + `</option>
                `);
                
                $('#start_time').val(response.start_item);
                $('#end_time').val(response.end_item);
                
                $('#company_id').val(response.company_id).formSelect();
                
                $("#post_date").val(response.post_date);
                $("#end_date").val(response.end_date);
                $("#start_date").val(response.start_date);
                $("#note").val(response.note);
                
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
        var document = $('#filter-document').val();
            
        },
        onDisconnect: function () {
           
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
    
</script>