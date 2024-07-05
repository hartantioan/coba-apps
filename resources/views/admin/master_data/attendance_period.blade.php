<!-- BEGIN: Page Main-->
<style>
    .modal {
        top:0px !important;
    }
    .fixed {
        left: 10px;
        z-index: 20;
    }

    body.tab-active input:focus {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .modal-content .select2.tab-active {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .fixed2 {
        left: 100px; /* Adjust as needed */
        z-index: 20;
    }

    .fixed1 {
        left: 200px; /* Adjust as needed */
        z-index: 20;
    }
    #kambing td:first-child,
    {
        position: sticky;
        left: 10px;
        background-color: aliceblue;
    }
    #kambing td:nth-child(2) {
        position: sticky;
        left: 100px;
        background-color: aliceblue;
    }
    #kambing td:nth-child(3) {
        position: sticky;
        left: 200px;
        background-color: aliceblue;
    }
</style>
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
                    <div class="col s12 m6 l6">
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="print();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">{{ __('translations.print') }}</span>
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
                            <div class="card-panel">
                                <div class="row"> 
                                    <div class="col s12 ">
                                        <label for="filter_status" style="font-size:1.2rem;">{{ __('translations.filter_status') }} :</label>
                                        <div class="input-field inline" style="margin-top: 0;margin-bottom: 0;">
                                            <select class="form-control" id="filter_status" onchange="loadDataTable()">
                                                <option value="">{{ __('translations.all') }}</option>
                                                <option value="1">{{ __('translations.active') }}</option>
                                                <option value="2">{{ __('translations.non_active') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">{{ __('translations.list_data') }}</h4>
                                    <div class="row">
                                        <div class="col s12">
                                            <div id="datatable_buttons"></div>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">{{ __('translations.refresh') }}</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <table id="datatable_serverside" >
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{ __('translations.code') }}</th>
                                                        <th>{{ __('translations.name') }}</th>
                                                        <th>Periode Mulai</th>
                                                        <th>Periode Akhir</th>
                                                        <th>{{ __('translations.plant') }}</th>
                                                        <th>{{ __('translations.action') }}</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>{{ __('translations.add') }}/{{ __('translations.edit') }} {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="input-field col s12">
                            <input id="code" name="code" type="text" placeholder="Nama Periode">
                            <label class="active" for="code">{{ __('translations.code') }}</label>
                        </div>
                        <div class="input-field col s12">
                            <input type="hidden" id="temp" name="temp">
                            <input id="name" name="name" type="text" placeholder="Nama Periode">
                            <label class="active" for="name">{{ __('translations.name') }}</label>
                        </div>
                        <div class="input-field col s12">
                            <select id="plant_id" name="plant_id">
                                @foreach($place as $row)
                                    <option value="{{ $row->id }}">{{ $row->code }}</option>
                                @endforeach
                            </select>
                            <label for="plant_id">{{ __('translations.plant') }}</label>
                        </div>
                        <div class="input-field col m6 s12">
                            <input id="start_date" name="start_date" type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                            <label class="active" for="start_date">Tanggal Awal</label>
                        </div>
                        <div class="input-field col m6 s12">
                            <input id="end_date" name="end_date"  type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                            <label class="active" for="end_date">Tanggal Akhir</label>
                        </div>
                        <div class="col s12 mt-3">
                            <button class="btn waves-effect waves-light right submit" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>

<div id="modal2" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12 m6">
                <h4 class="card-title">
                    Hasil
                </h4>
            </div>
            <div class="col s12 m6">
                <div class="row">
                    <div class="col s12 m6">
                        <span style="display: inline-flex;"><i class="material-icons" style="color: green;    font-weight: 700;">check</i><p>: Tepat Waktu</p></span>
                    </div>
                    <div class="col s12 m6">
                        <span style="display: inline-flex;"><i class="material-icons" style="color: goldenrod;    font-weight: 700;">check</i><p>: Tidak Check Pulang</p></span>
                    </div>
                    <div class="col s12 m6">
                        <span style="display: inline-flex;"><i class="material-icons" style="color: purple;    font-weight: 700;">check</i><p>: Tidak Check Masuk</p></span>
                    </div>
                    <div class="col s12 m6">
                        <span style="display: inline-flex;"><i class="material-icons" style="color: blue;    font-weight: 700;">check</i><p>: Telat Masuk Saja</p></span>
                    </div>
                    <div class="col s12 m6">
                        <span style="display: inline-flex;"><i class="material-icons" style="color: crimson;    font-weight: 700;">check</i><p>: Telat Masuk Tidak Check Pulang</p></span>
                    </div>
                    <div class="col s12 m6">
                        <span style="display: inline-flex;"><i class="material-icons" style="color: red;    font-weight: 700;">close</i><p>: Absent</p></span>
                    </div>
                </div>
            </div>
        </div>
        
        
        <div class="row">
            <div class="col s12 m12" style="overflow: auto">
                <div class="result" style="width:2500px;">
                    <table class="bordered" style="font-size:10px;">
                        <tbody id="detail_kehadiran">
                            
                        </tbody>
                    </table>
                </div>  
                
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>

<div id="modal3" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12 m6">
                <h4 class="card-title">
                    Lateness Daily Report
                </h4>
            </div>
            
        </div>
        
        
        <div class="row">
            <div class="col s12 m12" style="overflow: auto">
                <div class="result" style="width:2500px;">
                    <table id="kambing" class="bordered" style="font-size:10px;">
                        <thead>
                            <tr>
                                <th class="center-align fixed">{{ __('translations.no') }}.</th>
                                <th class="center-align fixed2">NIK</th>
                                <th class="center-align fixed1">{{ __('translations.name') }}</th>
                                <th class="center-align">{{ __('translations.date') }}</th>
                                <th class="center-align">Nama Shift</th>
                                <th class="center-align">Shift Awal</th>
                                <th class="center-align">Shift Masuk</th>
                                <th class="center-align">Check In</th>
                                
                                <th class="center-align">Shift Pulang</th>
                                <th class="center-align">Check Out</th>
                                <th class="center-align">Shift Akhir</th>
                                <th class="center-align">{{ __('translations.status') }}</th>
                            </tr>
                        </thead>
                        <tbody id="daily_report">
                            
                        </tbody>
                    </table>
                </div>  
                
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>

<div id="modal4" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;min-width:100%;max-width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12 m6">
                <h4 class="card-title">
                   Report Denda
                </h4>
            </div>
            
        </div>
        
        
        <div class="row">
            <div class="col s12 m12" style="overflow: auto">
                <div class="result" style="width:2500px;">
                    <table id="report_punishment_table" class="bordered" style="font-size:10px;">
                        <thead>
                            <tr>
                                <th class="center-align fixed">{{ __('translations.no') }}.</th>
                                <th class="center-align fixed2">NIK</th>
                                <th class="center-align fixed1">{{ __('translations.name') }}</th>
                                <th class="center-align">Periode</th>
                                <th class="center-align">Tipe Denda</th>
                                <th class="center-align">Frekuensi</th>
                                <th class="center-align">{{ __('translations.date') }}</th>
                                <th class="center-align">{{ __('translations.total') }}</th>
                            </tr>
                        </thead>
                        <tbody id="punish_report">
                            
                        </tbody>
                    </table>
                </div>  
                
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>

<div id="modal5" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;min-width:100%;max-width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <ul class="tabs">
                    <li class="tab col m3"><a class="active" href="#monthly">Monthly Payment </a></li>
                    <li class="tab col m3"><a  href="#daily">Daily Payment</a></li>
                </ul>
            </div>
            <div class="col s12 m12" >
                <div id="monthly" class="col s12"  style="overflow: auto">
                    <div class="result" style="width:2500px;" id="salary_canvas"  ></div>  
                </div>
                <div id="daily" class="col s12" style="overflow: auto">
                    <div class="result" style="width:2500px;" id="salary_canvas2"  ></div>  
                </div>
                
                       
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
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
    $(function() {
        loadDataTable();

        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
        });
        
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) {
                $('#title').focus();
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                M.updateTextFields();
            }
        });

        $('#modal2').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) {
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#detail_kehadiran').empty();
                M.updateTextFields();
            }
        });

        $('#modal3').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) {
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#daily_report').empty();
                M.updateTextFields();
            }
        });

        $('#modal4').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) {
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#punish_report').empty();
                M.updateTextFields();
            }
        });
        $('#modal5').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) {
            },
            onCloseEnd: function(modal, trigger){
                $('#salary_canvas2').empty();
                $('#salary_canvas').empty();
                M.updateTextFields();
            }
        });

        select2ServerSide('#province_id', '{{ url("admin/select2/province") }}');
        select2ServerSide('#city_id', '{{ url("admin/select2/city") }}');
    });

    function exportExcel(id){
        window.location = "{{ Request::url() }}/export?period_id=" + id;
    }

    function goToMonth(url){
        var baseUrl = window.location.origin;
        var redirectUrl = baseUrl + "/admin/hr/hr_report/recap_periode?code=" + url;
        window.location.href = redirectUrl;
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
                    status : $('#filter_status').val()
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
                { name: 'code', className: 'center-align' },
                { name: 'name', className: '' },
                { name: 'start_date', className: '' },
                { name: 'end_date', className: 'center-align'},
                { name: 'plant_id', className: 'center-align'},
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

    function reportPresence(id){
        $.ajax({
            url: '{{ Request::url() }}/presence_report',
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
                if(response.status == 200) {
                    success();
                    $('#detail_kehadiran').append(response.message);
                    $('#modal2').modal('open');
                    
                }
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

    function reportDaily(id){
        $.ajax({
            url: '{{ Request::url() }}/daily_report',
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
                if(response.status == 200) {
                    success();
                    $('#daily_report').append(response.message);
                    $('#modal3').modal('open');
                    
                }
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

    function reportPunishment(id){
        $.ajax({
            url: '{{ Request::url() }}/punishment_report',
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
                if(response.status == 200) {
                    success();
                    console.log(response.message);
                    $('#punish_report').append(response.message);
                    $('#modal4').modal('open');
                    
                }
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
                $('#name').val(response.name);
                $("#start_date").val(response.start_date);
                $("#end_date").val(response.end_date);
                $("#code").val(response.code);

                $('.modal-content').scrollTop(0);
                $('#name').focus();
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

    function reportSalaryMonthly(id){
        $.ajax({
            url: '{{ Request::url() }}/salary_report',
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
                if(response.status == 200) {
                    $('#modal5').modal('open');
                    console.log(response);                    var string = '';
                    var string2='';
                    $.each(response.title, function(i, val) {
                        string += '<h4> Report Salary for '+val
                            +'</h4> <table> ';
                 
                        string+=response.message[i];
                       
                        string+='</table>';
                    });
                    $.each(response.title, function(i, val) {
                        string2 += '<h4> Report Salary for '+val
                            +'</h4> <table> ';
                 
                        string2+=response.perday[i];
                       
                        string2+='</table>';
                    });
                    console.log(string2);
                    $('#salary_canvas').append(string);
                    $('#salary_canvas2').append(string2);
                }
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

    function closed(id){
        swal({
            title: "Apakah anda yakin?",
            text: "Anda tidak bisa mengembalikan Periode yang telah ditutup!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                $.ajax({
                    url: '{{ Request::url() }}/close',
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
                        console.log(response);
                        loadingClose('#main');
                        loadDataTable();
                        M.updateTextFields();
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

    function reOpen(id){
        swal({
            title: "Apakah anda yakin?",
            text: "Anda tidak bisa mengembalikan Periode yang telah ditutup!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                $.ajax({
                    url: '{{ Request::url() }}/reopen',
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
                        console.log(response);
                        loadingClose('#main');
                        loadDataTable();
                        M.updateTextFields();
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

</script>