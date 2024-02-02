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
                                                        <th>User</th>
                                                        <th>Tipe</th>
                                                        <th>Tanggal</th>
                                                        <th>Note</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;">
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
                        <div class="input-field col s6 step1">
                            <select class="select2 browser-default" id="employee_id" name="employee_id" onchange="userDetail()">
                                <option value="">--Pilih ya--</option>
                            </select>
                            <label class="active" for="employee_id">Select Employee</label>
                        </div>
                        <div class="input-field col s6 step2">
                            <select id="type" name="type" onchange="hideShow()">
                                <option value="1">Promotion</option>
                                <option value="2">Demotion</option>
                                <option value="3">Mutation</option>
                                <option value="4">Resign</option>
                            </select>
                            <label for="type">Tipe</label>
                        </div>
                        <div class="input-field col s6 employee_inputs step3" id="place_select">
                            <select id="place_id" name="place_id">
                                <option value="">--Pilih--</option>
                                @foreach($place as $row)
                                    <option value="{{ $row->id }}">{{ $row->code }}</option>
                                @endforeach
                            </select>
                            <label for="place_id">Penempatan</label>
                        </div>
                        <div class="input-field col s6 employee_inputs step4" id="position_select">
                            <select  class="select2 browser-default" id="position_id" name="position_id">
                                <option value=""></option>
                                
                            </select>
                            <label class="active" for="position_id">Posisi/Level</label>
                        </div>
                        <div class="input-field col s6 step5" id="manager_select">
                            <select class="select2 browser-default" id="manager_id" name="manager_id">
                                <option value="">--Pilih ya--</option>
                            </select>
                            <label class="active" for="manager_id">Select Manager</label>
                        </div>
                        
                        <div class="input-field col s6 step6" >
                            <input id="post_date" name="post_date"  min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tanggal Post">
                            <label class="active" for="post_date">Tanggal Post</label>
                        </div>
                        <div class="input-field col s6 step7">
                            <input id="valid_date" name="valid_date"  min="{{ date('Y-m-d') }}" type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tanggal Efektif">
                            <label class="active" for="valid_date">Tanggal Efektif</label>
                        </div>
                        <div class="input-field col s6 step8">
                            <input id="note" name="note" type="text" placeholder="">
                            <label class="active" for="note">Keterangan Transfer</label>
                        </div>
                        <div class="col s12 mt-3 step9">
                            <button class="btn waves-effect waves-light right submit" onclick="save();">Simpan <i class="material-icons right">send</i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light purple " onclick="startIntro();">Panduan <i class="material-icons right">help_outline</i></button>
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
                M.updateTextFields();
                $('#manager_id,#employee_id,#position_id').empty();
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
        select2ServerSide('#employee_id', '{{ url("admin/select2/employee") }}');
        select2ServerSide('#manager_id', '{{ url("admin/select2/employee") }}');
        select2ServerSide('#position_id', '{{ url("admin/select2/position") }}');
        // ... Your other code ...

        const urlParams = new URLSearchParams(window.location.search);
        const code = urlParams.get('code');
        const employee_code = urlParams.get('employee_code');
        const selectedCode = $('#selectedCode').val();
        console.log(selectedCode);

        if (code) {
           
            $.ajax({
                url: '{{ Request::url() }}/show_from_code',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: selectedCode
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
                    $('#temp').val(response.id);
                    $('#employee_id').empty().append(`
                        <option value="` + response.user_id + `">` + response.user.name + `</option>
                    `);
                    if(response.manager != null){
                        $('#manager_id').empty().append(`
                            <option value="` + response.manager_id + `">` + response.manager.name + `</option>
                        `);
                    }
                    if(response.position != null){
                        $('#position_id').empty().append(`
                            <option value="` + response.position_id + `">` + response.position.name + `</option>
                        `);
                    }
                    console.log(response);
                  
                    $('#place_id').val(response.plant_id).formSelect();
                    $('#post_date').val(response.post_date);
                    $('#valid_date').val(response.valid_date);
                    $('#note').val(response.note);
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
           /*  $('#employee_id').val(selectedCode).trigger('change'); // Trigger 'change' event to update Select2 UI */
        }

        if (employee_code) {
           
           $.ajax({
               url: '{{ Request::url() }}/instant_form_code',
               type: 'POST',
               dataType: 'JSON',
               data: {
                   id: employee_code
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
                   $('#employee_id').empty().append(`
                       <option value="` + response.id + `">` + response.name + `</option>
                   `);
                   if(response.manager != null){
                       $('#manager_id').empty().append(`
                           <option value="` + response.manager_id + `">` + response.manager.name + `</option>
                       `);
                   }
                   if(response.position != null){
                       $('#position_id').empty().append(`
                           <option value="` + response.position_id + `">` + response.position.name + `</option>
                       `);
                   }
                   console.log(response);
                 
                   $('#place_id').val(response.plant_id).formSelect();
                   $('#post_date').val(response.post_date);
                   $('#valid_date').val(response.valid_date);
                   $('#note').val(response.note);
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
          /*  $('#employee_id').val(selectedCode).trigger('change'); // Trigger 'change' event to update Select2 UI */
       }
        
    });

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

    function userDetail(){
        $('#position_id').val($('#employee_id').select2('data')[0].arrinfo.position_id).formSelect();
        $('#place_id').val($('#employee_id').select2('data')[0].arrinfo.place_id).formSelect();
       
    }

    function hideShow(){
        const selectedValue = $('#type').val();
        const $managerSelect = $('#manager_select');

        if (selectedValue === "4") {
            var today = new Date();
            var year = today.getFullYear();
            var month = today.getMonth() + 1;
            var day = today.getDate();
            var formattedDate = year + '-' + ('0' + month).slice(-2) + '-' + ('0' + day).slice(-2);

            $('#valid_date').val(formattedDate);
            $('#valid_date').prop('readonly', true);
            $managerSelect.val('').hide();
            $('#position_select').val('').hide();
            $('#place_select').val('').hide();
          
        } else {
            $('#valid_date').val('');
            $('#valid_date').prop('readonly', false);
            $managerSelect.val('').show();
            $('#position_select').show().val('');
            $('#place_select').show().val('');
            
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
            "order": [[0, 'desc']],
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
                { name: 'account_id', className: 'center-align' },
                { name: 'type', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'note', className: 'center-align' },
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
                $('#type').val(response.type).formSelect();
                $('#employee_id').empty().append(`
                    <option value="` + response.employee_id + `">` + response.employee.name + `</option>
                `);
                if(response.manager != null){
                    $('#manager_id').empty().append(`
                        <option value="` + response.manager_id + `">` + response.manager.name + `</option>
                    `);
                }
                console.log(response);
                $('#position_id').empty().append(`
                    <option value="` + response.position_id + `">` + response.position.code +" - "+response.position.name+ `</option>
                `);
                
                $('#place_id').val(response.plant_id).formSelect();
          
                
                $("#post_date").val(response.post_date);
                $("#valid_date").val(response.valid_date);
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
        var msg = '';
        swal({
            title: "Alasan mengapa anda menghapus!",
            text: "Anda tidak bisa mengembalikan data yang telah dihapus.",
            buttons: true,
            content: "input",
        }).then(function (willDelete) {
            if (willDelete) {
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

    function startIntro(){
        introJs().setOptions({
            exitOnOverlayClick : false,
            steps: [
                {
                    title : 'Form Reward & Punishment ',
                    intro : 'Form ini digunakan untuk mendata reward maupun punishment yang nantinya akan digunakan pada periode yang telah dipilih.'
                },
                {
                    title : 'Select Employee',
                    element : document.querySelector('.step1'),
                    intro : 'Memilih employee yang akan di update.'
                },
                {
                    title : 'Tipe Transfer',
                    element : document.querySelector('.step2'),
                    intro : 'Digunakan untuk memilih tipe dari transfer yang ingin dilakukan.'
                },
                {
                    title : 'Penempatan',
                    element : document.querySelector('.step3'),
                    intro : 'Menentukan tempat pergantian dari employee yang ingin di update.' 
                },
                {
                    title : 'Posisi/Level',
                    element : document.querySelector('.step4'),
                    intro : 'Posisi/Level digunakan untuk memilih update dari employee.' 
                },
              
                {
                    title : 'Select Manager',
                    element : document.querySelector('.step5'),
                    intro : 'Memilih penanggung jawab dari user yang berkaitan.' 
                },
                {
                    title : 'Tanggal Post',
                    element : document.querySelector('.step6'),
                    intro : 'Tanggal yang digunakan untuk kapan form ini dibuat .' 
                },
                {
                    title : 'Tanggal Efektif',
                    element : document.querySelector('.step7'),
                    intro : 'Tanggal aktif user tersebut terupdate.' 
                },
                {
                    title : 'Keterangan',
                    element : document.querySelector('.step8'),
                    intro : 'Silahkan isi / tambahkan keterangan untuk dokumen ini untuk dimunculkan di bagian bawah tabel detail produk nantinya, ketika dicetak.' 
                },
                {
                    title : 'Simpan',
                    element : document.querySelector('.step9'),
                    intro : 'Silahkan tekan tombol ini untuk menyimpan data, namun pastikan data yang akan anda masukkan benar.' 
                },
                
            ]
        })/* .onbeforechange(function(targetElement){
            alert(this._currentStep);
        }) */.start();
    }
    
</script>