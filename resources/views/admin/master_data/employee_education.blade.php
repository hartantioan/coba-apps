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

    body.tab-active input:focus {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .modal-content .select2.tab-active {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
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
                            <li class="breadcrumb-item active">{{ Str::title(str_replace('_',' ',Request::segment(5))) }}
                            </li>
                        </ol>
                    </div>
                    <div class="col s4 m6 l6">
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="loadDataTable()">
                            <i class="material-icons hide-on-med-and-up">refresh</i>
                            <span class="hide-on-small-onl">{{ __('translations.refresh') }}</span>
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
                                                        <th>Nama Sekolah</th>
                                                        <th>Major/Jurusan</th>
                                                        <th>Jenjang</th>
                                                        <th>IPK/Final Skor</th>
                                                        <th>Tahun Masuk</th>
                                                        <th>Tahun Selesai</th>
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
                        <div class="input-field col s12 m6">
                            <input type="hidden" id="temp" name="temp">
                            <input id="school_name" name="school_name" type="text" placeholder="Nama">
                            <label class="school_name" for="name">Nama Intitusi</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <input id="major" name="major" type="text" placeholder="">
                            <label class="major" for="major">Jurusan</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <input id="final_score" name="final_score" type="number" placeholder="">
                            <label class="final_score" for="final_score">IPK/Skor Akhir</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <select id="stage" name="stage">
                                <option value="1">SD</option>
                                <option value="2">SMP</option>
                                <option value="3">SMA</option>
                                <option value="4">D1</option>
                                <option value="5">D2</option>
                                <option value="6">D3</option>
                                <option value="7">D4</option>
                                <option value="8">S1</option>
                                <option value="9">S2</option>
                                <option value="10">S3</option>
                            </select>
                            <label for="stage">Tingkat</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <input id="year_start" name="year_start" type="number" placeholder="Tahun Masuk">
                            <label class="year_start" for="note">Tahun Masuk</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <input id="year_end" name="year_end" type="number" placeholder="Tahun Lulus">
                            <label class="year_end" for="note">Tahun Selesai</label>
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

<div id="modal4_1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="show_detail">

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
    var tempuser = 0;
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');
    $(function() {
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        loadDataTable();
        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
        });

        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
                $('#school_name').focus();
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

        $('#modal4_1').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#show_detail').empty();
            }
        });


        
    });

    function save(){
        
        const urlParams = new URLSearchParams(window.location.search);
        const id = urlParams.get('id');	
        var formData = new FormData($('#form_data')[0]);
        formData.append('id',id);
        console.log(formData);
        $.ajax({
            url: '{{ URL::to('/') }}/admin/master_data/master_hr/employee/create_education',
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


    function loadDataTable() {
        const urlParams = new URLSearchParams(window.location.search);
        const id = urlParams.get('id');
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
                url: '{{ URL::to('/') }}/admin/master_data/master_hr/employee/datatable_education',
                type: 'GET',
                data: {
                    status : $('#filter_status').val(),
                    type : $('#filter_type').val(),
                    id: id
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
                { name: 'stage', className: 'center-align' },
                { name: 'school_name', className: 'center-align' },
                { name: 'major', className: 'center-align' },
                { name: 'final_score', className: 'center-align' },
                { name: 'year_start', className: 'center-align' },
                { name: 'year_start', className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'right-align' },
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
                "select": {
                    rows: "%d baris terpilih"
                }
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
            url: '{{ URL::to('/') }}/admin/master_data/master_hr/employee/show_education',
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
               
                $('#school_name').val(response.school_name);
                $('#major').val(response.major);
                $('#final_score').val(response.final_score);
                $("#year_start").val(response.year_start);
                $("#year_end").val(response.year_end);
                $("#stage").val(response.stage).formSelect();

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
                    url: '{{ URL::to('/') }}/admin/master_data/master_hr/employee/destroy_education',
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


</script>