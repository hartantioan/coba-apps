<script src="{{ url('app-assets/js/dropzone.min.js') }}"></script>
<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
<link rel="stylesheet" type="text/css" href="../../../app-assets/vendors/fullcalendar/css/fullcalendar.min.css">
<link rel="stylesheet" type="text/css" href="../../../app-assets/vendors/fullcalendar/daygrid/daygrid.min.css">
<link rel="stylesheet" type="text/css" href="../../../app-assets/vendors/fullcalendar/timegrid/timegrid.min.css">
<script src="../../../app-assets/vendors/fullcalendar/js/fullcalendar.min.js"></script>
<script src="../../../app-assets/vendors/fullcalendar/daygrid/daygrid.min.js"></script>
<script src="../../../app-assets/vendors/fullcalendar/timegrid/timegrid.min.js"></script>
<script src="../../../app-assets/vendors/fullcalendar/interaction/interaction.min.js"></script>

<style>
    .modal {
        top:0px !important;
    }

    table > thead > tr > th {
        font-size: 13px !important;
    }

    body.tab-active input:focus {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .modal-content .select2.tab-active {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
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
                            <li class="breadcrumb-item active">{{ Str::title(str_replace('_',' ',Request::segment(4))) }}
                            </li>
                        </ol>
                    </div>
                    <div class="col s4 m6 l6">
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
                                                        <th>#</th>
                                                        <th>Nama</th>
                                                        <th>Username</th>
                                                        <th>NIK/Code</th>
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
<div id="modal2" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
           
            <div class="col s12">
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="input-field col m12 s12">
                        <select class="browser-default" id="period_id" name="period_id"></select>
                        <label class="active" for="period_id">Periode :</label>
                    </div>
                    <input type="hidden" id="temp_copy" name="temp_copy">
                    <table class="bordered centered">
                        <thead>
                            <tr>
                                <th colspan="3">PEGAWAI</th>
                                <th colspan="3">ACTION</th>
                            </tr>
                            <tr>
                                <th>Code</th>
                                <th>Nama</th>

                                <th>Posisi</th>
                                <th>Checkbox</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user as $row)
                            <tr>
                                <td>{{ $row->employee_no }}</td>
                                <td>{{ $row->name }}</td>
                             
                                <td>{{ $row->position->name??'' }}</td>
                                <td class="input-field">
                                    <label>
                                        <input type="checkbox" name="arr_employee[]" id="checkbox{{ $row->id }}" value="{{ $row->employee_no }}"/>
                                        <span>Pilih</span>
                                    </label>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="col s12 mt-3">
                        <button class="btn waves-effect waves-light right submit" onclick="copySchedule();">Copy <i class="material-icons right">send</i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal_salary_component" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <form class="row" id="form_data_salary_component" onsubmit="return false;">
                    <table class="bordered">
                        <tbody id="body-detail">
                            
                            <tr id="last-row-item">
                                
                            </tr>
                        </tbody>
                    </table>
                    <div id="salary_component">
                    
                        <input type="hidden" id="temp_salary" name="temp_salary">
                    </div>
                    <div class="col s12 mt-3">
                        <button class="btn waves-effect waves-light right submit" onclick="save_salary();">Simpan <i class="material-icons right">send</i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
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

<div id="modal_calendar" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <div id='fc-external-drag' style="height:100%"></div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
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
    var arrgroup = @json($group);
    var tempuser = 0;
    var calendar;
    $(function() {
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });
       

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
            editable: false,
            plugins: ["dayGrid", "timeGrid", "interaction"],
            droppable: false,
            eventClick: function (info) {
                function removeEvent() {
                    info.event.remove();
                }

                if (confirm("Are you sure you want to delete this event?")) {
                    removeEvent();
                }
            },
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
        $('#modal2').modal({
            dismissible:false,
            onOpenStart: function(modal,trigger) {
               
            },
            onOpenEnd: function(modal, trigger) { 
                
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
            }
        });
        select2ServerSide('#period_id', '{{ url("admin/select2/period") }}');
        $('#modal_salary_component').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                const tbody = document.getElementById('body-detail');

                const lastRowItem = document.getElementById('last-row-item');

                while (tbody.firstChild !== lastRowItem) {
                    tbody.removeChild(tbody.firstChild);
                }
                $('#form_data_salary_component')[0].reset();
            }
        });

        $('#modal_calendar').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
                
                setTimeout(function () {
                    calendar.render();
                }, 1500);

                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                $('fc-events-container').empty();
                calendar.removeAllEvents();

                calendar.render();
            }
        });
        
    });

    function showEducation(id){
        window.location = "{{ URL::to('/') }}/admin/master_data/master_hr/employee/education?id=" + id;
    }

    function getSalaryComponent(id){
        $.ajax({
            url: '{{ Request::url() }}/salary_component',
            type: 'GET',
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            data: {
                id: id
            },
            success: function(response) {
                $('#temp_salary').val(id);
                var count = makeid(10);
               
                $.each(response, function(i, val) {
                    $('#last-row-item').before(`
                        <tr class="row_item" data-id="">
                            <td>
                            `+val['component_name']+`
                            </td>
                            <td>
                                <input id="arr_component` + val['id_component'] + `" name="arr_component[]" type="number"  value="`+val['nominal']+`">
                                <input type="hidden" id="arr_id_component" name="arr_id_component[]" value="`+val['id_component']+`">
                            </td>
                        </tr>
                    `);
                });
                $('#modal_salary_component').modal('open');
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

    function save_salary(){
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
                var formData = new FormData($('#form_data_salary_component')[0]);

                $.ajax({
                    url: '{{ Request::url() }}/save_employee_salary_component',
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
                          
                            
                            $('#modal_salary_component').modal('close');
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
    }

    function showFamily(id){
        window.location = "{{ URL::to('/') }}/admin/master_data/master_hr/employee/family?id=" + id;
    }

    function showExperience(id){
        window.location = "{{ URL::to('/') }}/admin/master_data/master_hr/employee/work_experience?id=" + id;
    }

    function copySchedule() {
        var formData = new FormData($('#form_data')[0]);
        var id = $('#temp_copy').val();
        var period_id = $('#period_id').val();
        formData.append('user_id',id);
        formData.append('period_id',period_id);
        $.ajax({
            url: '{{ Request::url() }}/copy_schedule',
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
                    $('#modal2').modal('close');
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

    function openCopy(id){
        $('#modal2').modal('open');
        $('#temp_copy').val(id);
    }
    
    function getSchedule(id){
        $.ajax({
            url: '{{ Request::url() }}/get_schedule',
            type: 'GET',
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            data: {
                id: id
            },
            success: function(response) {
                
                $.each(response, function(i, val) {
                    console.log(val.user);
                    calendar.addEvent({
                        title: val.shift.name+'||'+val.shift.code,
                        start: val.date,
                        end: val.date,
                    });
                });
                $('#modal_calendar').modal('open');
                
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
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
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



    

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
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
        $.map(window.table.rows('.selected').nodes(), function (item) {
            var poin = $(item).find('td:nth-child(4)').text().trim();
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

    function exportExcel(){
        var search = window.table.search();
        var status = $('#filter_status').val();
        var type = $('#filter_type').val();
        
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status + "&type=" + type;
    }

    function goto(url){
        window.open(url);
    }
</script>