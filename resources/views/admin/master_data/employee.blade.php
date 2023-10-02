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
                                            <table id="datatable_serverside" class="display responsive-table wrap">
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
<div id="modal2" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <form class="row" id="form_data" onsubmit="return false;">
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
                                        <input type="checkbox" name="arr_employee[]" id="checkbox{{ $row->id }}" value="{{ $row->id }}"/>
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

<div id="modal4_1" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;width:100%;">
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

<div id="modal_calendar" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;width:100%;">
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

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div>



<!-- END: Page Main-->
<script>
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
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
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

                // Rerender the calendar to display the new events
                calendar.render();
            }
        });
        
    });

    function showEducation(id){
        window.location = "{{ URL::to('/') }}/admin/master_data/master_hr/employee/education?id=" + id;
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
        formData.append('user_id',id);
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
            "order": [[0, 'asc']],
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
                $('#name').val(response.name);
                $('#username').val(response.username);
                $('#phone').val(response.phone);
                $('#email').val(response.email);
                $("#address").val(response.address);
                $('#type').val(response.type).trigger('change').formSelect();

                refreshGroup();
                
                $('#province_id,#city_id,#country_id').empty();

                $('#province_id').append(`
                    <option value="` + response.province_id + `">` + response.province_name + `</option>
                `);
                $('#city_id').append(`
                    <option value="` + response.city_id + `">` + response.city_name + `</option>
                `);

                $('#subdistrict_id').empty();
                $.each(response.subdistrict_list, function(i, value) {
                    $('#subdistrict_id').append(`
                        <option value="` + value.id + `">` + value.code + ` ` + value.name + `</option>
                    `);
                });

                $('#subdistrict_id').val(response.subdistrict_id).trigger('change');

                $('#country_id').append(`
                    <option value="` + response.country_id + `">` + response.country_name + `</option>
                `);

                $('#tax_id').val(response.tax_id);
                $('#tax_name').val(response.tax_name);
                $('#tax_address').val(response.tax_address);
                $('#id_card').val(response.id_card);
                $('#id_card_address').val(response.id_card_address);
                $('#gender').val(response.gender).formSelect();
                $('#group_id').val(response.group_id).formSelect();

                if(response.type == '1'){
                    $('#company_id').val(response.company_id).formSelect();
                    $('#place_id').val(response.place_id).formSelect();
                    $('#department_id').val(response.department_id).formSelect();
                    $('#position_id').val(response.position_id).formSelect();
                    $('#married_status').val(response.married_status).formSelect();
                    $('#married_date').val(response.married_date);
                    $('#children').val(response.children);
                }else{
                    $('#pic').val(response.pic);
                    $('#pic_no').val(response.pic_no);
                    $('#office_no').val(response.office_no);
                    $('#limit_credit').val(response.limit_credit);
                    $('#top').val(response.top);
                    $('#top_internal').val(response.top_internal);
                }

                $('.row_bank').remove();

                if(response.banks.length > 0){
                    $.each(response.banks, function(i, val) {
                        $('#last-row-bank').before(`
                            <tr class="row_bank">
                                <td>
                                    <select class="browser-default bank-array" id="arr_bank` + i + `" name="arr_bank[]"></select>
                                </td>
                                <td>
                                    <input name="arr_name[]" type="text" placeholder="Atas nama" value="` + val.name + `">
                                </td>
                                <td class="center">
                                    <input name="arr_no[]" type="text" placeholder="No rekening" value="` + val.no + `">
                                </td>
                                <td>
                                    <input name="arr_branch[]" type="text" placeholder="Cabang" value="` + val.branch + `">
                                </td>
                                <td class="center">
                                    <label>
                                        <input class="with-gap" name="check" type="radio" value="` + i + `" ` + (val.is_default == '1' ? 'checked' : '') + `>
                                        <span>Pilih</span>
                                    </label>
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-bank" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                        select2ServerSide('#arr_bank' + i, '{{ url("admin/select2/bank") }}');
                        $('#arr_bank' + i).append(`
                            <option value="` + val.bank_id + `">` + val.bank_name + `</option>
                        `);
                    });
                }

                if(response.datas.length > 0){
                    $.each(response.datas, function(i, val) {
                        $('#last-row-info').before(`
                            <tr class="row_info">
                                <td>
                                    <input name="arr_title[]" type="text" placeholder="Judul informasi tambahan" value="` + val.title + `">
                                </td>
                                <td class="center">
                                    <input name="arr_content[]" type="text" placeholder="Isi informasi tambahan" value="` + val.content + `">
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-info" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                    });
                }

                if(response.status == '1'){
                    $('#status').prop( "checked", true);
                }else{
                    $('#status').prop( "checked", false);
                }
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
            
        },
        onDisconnect: function () {
           
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