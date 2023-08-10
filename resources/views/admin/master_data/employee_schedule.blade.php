<script src="{{ url('app-assets/js/dropzone.min.js') }}"></script>
<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
<link rel="stylesheet" type="text/css" href="{{ url('app-assets/vendors/fullcalendar/css/fullcalendar.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ url('app-assets/vendors/fullcalendar/daygrid/daygrid.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ url('app-assets/vendors/fullcalendar/timegrid/timegrid.min.css') }}">

<style>
    .fc-content{
        height: -webkit-fill-available;
        display: flex;
        
    }
    .fc-next-button{
        background: darkblue !important;
    }

    .fc-today-button{
        background: black !important;
    }
    .fc-prev-button{
        background: darkblue !important;
    }

    .fc-title{
        align-self: center;
    }

    .fc-day-grid-event{
        height: 3rem !important;
    }

    .fc-external-drag{
        height: 100% !important;
    }

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
                                            <table id="datatable_serverside" class="display responsive-table wrap">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>NIK</th>
                                                        <th>User</th>
                                                        <th>Tanggal</th>
                                                        <th>Shift</th>
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

<div id="modal3" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Edit {{ $title }}</h4>
                <form class="row" id="kambing" onsubmit="return false;">
                    <ul class="tabs">
                        <li class="tab">
                            <a href="#single-tabs1" class="" id="part-tabs-btn">
                            <span>Single</span>
                            </a>
                        </li>
                        <li class="indicator" style="left: 0px; right: 0px;"></li>
                    </ul>
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div id="single-tabs1" style="display: block;" class="">                           
                        <div class="row ml-2 mt-2">
                            
                            <div class="col s12">
                                
                                <div class="input-field col s6" >
                                    <select class="select2 browser-default" id="employee_id_detail" name="employee_id_detail">
                                        <option value="">--Pilih ya--</option>
                                    </select>
                                    <label class="active" for="employee_id_detail">Select Employee</label>
                                </div>
                                <div class="input-field col s6" >
                                    <input id="date_detail" name="date_detail" type="date" placeholder="Tanggal Post">
                                    <label class="active" for="date_detail">Tanggal</label>
                                </div>
                                <div class="input-field col s6">
                                    <select class="select2 browser-default" id="shift_id_detail" name="shift_id_detail">
                                        <option value="">--Pilih ya--</option>
                                    </select>
                                    <label class="active" for="shift_id_detail">Select Shift</label>
                                </div>
                                
                                  
                                  
                                
                                <div class="col s12 mt-3">
                                    <button class="btn waves-effect waves-light right submit" onclick="saveSingle();">Simpan <i class="material-icons right">send</i></button>
                                </div>
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

<div id="modal1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Tambah/Edit {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <ul class="tabs">
                        <li class="tab">
                            <a href="#single-tabs" class="" id="part-tabs-btn">
                            <span>Single</span>
                            </a>
                        </li>
                        <li class="tab">
                            <a href="#multi-tabs" class="">
                            <span>Multi</span>
                            </a>
                        </li>
                        <li class="tab">
                            <a href="#import-tabs" class="">
                            <span>Import</span>
                            </a>
                        </li>
                        <li class="indicator" style="left: 0px; right: 0px;"></li>
                    </ul>
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div id="single-tabs" style="display: block;" class="">                           
                        <div class="row ml-2 mt-2">
                            
                            <div class="col s12">
                                
                                <div class="input-field col s6" >
                                    <select class="select2 browser-default" id="employee_id" name="employee_id">
                                        <option value="">--Pilih ya--</option>
                                    </select>
                                    <label class="active" for="employee_id">Select Employee</label>
                                </div>
                                <div class="input-field col s6" >
                                    <input id="date" name="date" type="date" placeholder="Tanggal Post">
                                    <label class="active" for="date">Tanggal</label>
                                </div>
                                <div class="input-field col s6">
                                    <select class="select2 browser-default" id="shift_id" name="shift_id">
                                        <option value="">--Pilih ya--</option>
                                    </select>
                                    <label class="active" for="shift_id">Select Shift</label>
                                </div>
                                
                                  
                                  
                                
                                <div class="col s12 mt-3">
                                    <button class="btn waves-effect waves-light right submit" onclick="saveSingle();">Simpan <i class="material-icons right">send</i></button>
                                </div>
                            </div>
                            
                        </div>                         
                    </div>
                    <div id="multi-tabs" style="display: none;" class="">
                        <div id="app-calendar">
                            <div class="row">
                                <div class="col s12">
                                    <div class="card">
                                        <div class="card-content">
                                        <h4 class="card-title">
                                            External Dragging
                                        </h4>
                                        <div class="row">
                                            <div class="col m3 s12">
                                            <div id='external-events'>
                                                <h5>Draggable Events</h5>
                                                <div class="fc-events-container mb-5">
                                                @foreach ($shift as $shift_type )
                                                    @php
                                                        $color = '#' . substr(md5(rand()), 0, 6);
                                                    @endphp
                                                    <div class='fc-event' data-color='{{ $color }}' data-id={{$shift_type->id}} >{{$shift_type->name.'||'.$shift_type->code}}</div>
                                                @endforeach
                                                <p>
                                                    <label>
                                                    <input type="checkbox" id="drop-remove" />
                                                    <span>Remove After Drop</span>
                                                    </label>
                                                </p>
                                                </div>
                                            </div>
                                            </div>
                                            <div class="col m9 s12">
                                                <div id='fc-external-drag' style="height:100%"></div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="input-field col s12">
                            <div class="row mt-3">
                                <div class="col s12">
                                    <table class="bordered centered">
                                        <thead>
                                            <tr>
                                                <th colspan="4">PEGAWAI</th>
                                                <th colspan="4">ACTION</th>
                                            </tr>
                                            <tr>
                                                <th>Code</th>
                                                <th>Nama</th>
                                                <th>Departemen</th>
                                                <th>Posisi</th>
                                                <th>Checkbox</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user as $row)
                                            <tr>
                                                <td>{{ $row->employee_no }}</td>
                                                <td>{{ $row->name }}</td>
                                                <td>{{ $row->department->name }}</td>
                                                <td>{{ $row->position->name }}</td>
                                                <td class="input-field">
                                                    <label>
                                                        <input type="checkbox" name="arr_employee[]" id="checkbox{{ $row->id }}" value="{{ $row->id }}"/>
                                                        <span>Pilih</span>
                                                    </label>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    <div class="col s12 mt-3">
                                        <button class="btn waves-effect waves-light right submit" onclick="saveMulti();">Simpan <i class="material-icons right">send</i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    <div id="import-tabs" style="display: none;" class="">
                        
                        <div class="file-field input-field col m6 s12">
                            <div class="btn">
                                <span>Dokumen Schedule</span>
                                <input type="file" class="form-control-file" id="fileExcel" name="file">
                            </div>
                            <div class="file-path-wrapper">
                                <input class="file-path validate" type="text">
                            </div>
                        </div>
                        
                        <div class="input-field col m12 s12">
                            <button class="btn waves-effect waves-light right submit" onclick="importSchedule();">Simpan <i class="material-icons right">send</i></button>
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


<script>
    var tempuser = 0;
    var calendar;
    $(function() {
        var Calendar = FullCalendar.Calendar;
        var Draggable = FullCalendarInteraction.Draggable;
        var containerEl = document.getElementById('external-events');
        var calendarEl = document.getElementById('fc-external-drag');
        var checkbox = document.getElementById('drop-remove');
        calendar = new Calendar(calendarEl, {
            header: {
            left: 'prev,next today',
            center: 'title',
            },
            editable: true,
            plugins: ["dayGrid", "timeGrid", "interaction"],
            droppable: true,
            drop: function (info) {
                if (checkbox.checked) {
                    info.draggedEl.parentNode.removeChild(info.draggedEl);
                }
            }
        });
        $('#external-events .fc-event').each(function () {
            $(this).css({ 'backgroundColor': $(this).data('color'), 'borderColor': $(this).data('color') });
        });
        var colorData;
        var id;
        $('#external-events .fc-event').mousemove(function () {
            colorData = $(this).data('color');
        });

        new Draggable(containerEl, {
            itemSelector: '.fc-event',
            eventData: function (eventEl) {
                return {
                    title: eventEl.innerText,
                    color: colorData,
                    id:eventEl.getAttribute('data-id'),
                };
            }
        });
        
            
        
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
                
                setTimeout(function () {
                    calendar.render();
                }, 1500);

                
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                $('fc-events-container').empty();
                calendar.removeAllEvents();
                calendar.render();
                $('#form_data')[0].reset();
                $('#temp').val('');
                M.updateTextFields();
            }
        });
        

        loadDataTable();
        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
        });

        $('#modal3').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#kambing')[0].reset();
                $('#temp').val('');
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
        select2ServerSide('#shift_id', '{{ url("admin/select2/shift") }}');
        select2ServerSide('#shift_id_multi', '{{ url("admin/select2/shift") }}');
        select2ServerSide('#employee_id_detail', '{{ url("admin/select2/employee") }}');
        select2ServerSide('#shift_id_detail', '{{ url("admin/select2/shift") }}');

        
    });

    function setDate(){

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
                { name: 'no', searchable: false, className: 'center-align details-control' },
                { name: 'user_id', className: 'center-align' },
                { name: 'user_code', className: 'center-align' },
                { name: 'date', className: 'center-align' },
                { name: 'shift_id', className: 'center-align' },
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

    function saveSingle(){
        var formData = new FormData($('#form_data')[0]);
    
        $.ajax({
            url: '{{ Request::url() }}/create_single',
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

    function saveMulti(){
        var formData = new FormData($('#form_data')[0]);
        var employee_shift=[];
        var events = calendar.getEvents();
        events.forEach(function(event) {
        var startDate = event.start;
        var endDate;
        if(event.end==null){
            endDate = event.start;
        }else{
            endDate = event.end;
        }
        
        
            employee_shift.push({
                'start_date': startDate.toISOString(),
                'end_date': endDate.toISOString(),
                'shift_id': event.id,
            });
        });
        formData.append('employee_shift',JSON.stringify(employee_shift));
        console.log(employee_shift);

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
                $('#modal3').modal('open');

                $('#temp').val(id);
                $('#date_detail').val(response.date);
                /* $('#employee_id_detail').append(`
                    <option value="` + response.employee_id + `">` + response.employee_name + `</option>
                `);
                $('#shift_id_detail').append(`
                    <option value="` + response.employee_id + `">` + response.shift + `</option>
                `); */
                
                
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

    function importSchedule(){
        var formData = new FormData($('#form_data')[0]);
        $.ajax({
            url: '{{ Request::url() }}/import',
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
                } else if(response.status == 422||response.status == 432) {
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
                    console.log(response);
                    M.toast({
                        html: response
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
        formData.append('tabledata',etNumbers);
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
<script src="../../../app-assets/vendors/fullcalendar/js/fullcalendar.min.js"></script>
<script src="../../../app-assets/vendors/fullcalendar/daygrid/daygrid.min.js"></script>
<script src="../../../app-assets/vendors/fullcalendar/timegrid/timegrid.min.js"></script>
<script src="../../../app-assets/vendors/fullcalendar/interaction/interaction.min.js"></script>
