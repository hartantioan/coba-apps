
<style>
    .modal {
        top:0px !important;
    }
</style>
<div id="main">
    <div class="row">
        <div class="col s12">
            <div class="container">
                <div class="row">
                    <div class="col s12">
                    </div>
                </div>
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
                                    <ul class="collapsible collapsible-accordion">
                                        <li>
                                            <div class="collapsible-header"><i class="material-icons">filter_list</i>{{ __('translations.filter') }}</div>
                                            <div class="collapsible-body">
                                                <div class="row">
                                                    <div class="col m4 s6 ">
                                                        <label for="start-date" style="font-size:1rem;">{{ __('translations.start_date') }} : </label>
                                                        <div class="input-field col s12">
                                                        <input type="date" id="start-date" name="start-date"  onchange="loadDataTable()">
                                                        </div>
                                                    </div>
                                                    <div class="col m4 s6 ">
                                                        <label for="finish-date" style="font-size:1rem;">{{ __('translations.end_date') }} :</label>
                                                        <div class="input-field col s12">
                                                            <input type="date" id="finish-date" name="finish-date"  onchange="loadDataTable()">
                                                        </div>
                                                    </div>
                                                </div>  
                                            </div>
                                        </li>
                                    </ul>
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
                                                        <th>{{ __('translations.name') }}</th>
                                                        <th>{{ __('translations.note') }}</th>
                                                        <th>Tanggal Mulai</th>
                                                        <th>Tanggal Akhir</th>
                                                        <th>Umur</th>
                                                        <th>Limit</th>
                                                        <th>{{ __('translations.status') }}</th>
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
<div id="modal1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content" style="overflow-x: hidden;max-width: 100%;">
        <div class="row">
            <div class="col s12">
                <h4>{{ __('translations.add') }}/{{ __('translations.edit') }} {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="row">
                            <div class="input-field col m3 s12 step15">
                                <input type="hidden" id="temp" name="temp">
                                <input id="name" name="name" type="text" placeholder="Nama Task">
                                <label class="active" for="name">Nama Task</label>
                            </div>
                            <div class="input-field col m3 s12 step16">
                                <input id="note" name="note" type="text" placeholder="Keterangan">
                                <label class="active" for="note">Keterangan task</label>
                            </div>
                            <div class="input-field col m3 s12 step17">
                                <input id="start_date" name="start_date" type="date">
                                <label class="active" for="start_date">Tanggal Mulai</label>
                            </div>
                            <div class="input-field col m3 s12 step17">
                                <input id="end_date" name="end_date" type="date">
                                <label class="active" for="end_date">Tanggal Berakhir</label>
                            </div>
                            <div class="input-field col m3 s12 step17">
                                <input id="age_limit_reminder" name="age_limit_reminder" type="number">
                                <label class="active" for="age_limit_reminder">Umur Pengingat</label>
                            </div>
                            
                            <div class="col s12 mt-3">
                                <button class="btn waves-effect waves-light right submit step25" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light purple btn-panduan" onclick="startIntro();">Panduan <i class="material-icons right">help_outline</i></button>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>
<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div>

<script>
    $(function() { 
        loadDataTable();
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {

            },
            onOpenEnd: function(modal, trigger) { 
                $('#name').focus();
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
               
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('input').css('border', 'none');
                $('input').css('border-bottom', '0.5px solid black');
                $('#temp').val('');
                $('#note').val('');
                $('#start_date,#end_date,#age_limit_reminder').val('');
               
            }
        });
        let i = '{{ $code ? $code : "" }}';
        if(i !== ""){
            show(i);
        }
    });

    function kambing(asd){
        alert(asd);
    }

    function loadDataTable() {
		window.table = $('#datatable_serverside').DataTable({
            "scrollCollapse": true,
            "scrollY": '400px',
            "responsive": false,
            "scrollX":true,
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
                    start_date : $('#start-date').val(),
                    finish_date : $('#finish-date').val(),
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
                        { name: 'nama', className: 'center-align' },
                        { name: 'start_date', className: '' },
                        { name: 'note', className: '' },
                        { name: 'end_date', className: 'center-align' },
                        { name: 'status', className: 'center-align' },
                        { name: 'umur', className: 'center-align' },
                        { name: 'limit', className: 'center-align' },
                        { name: 'action', searchable: false, orderable: false, className: 'center-align' }
                    ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle' 
            ]
        });
        $('.dt-buttons').appendTo('#datatable_buttons');
        $('select[name="datatable_serverside_length"]').addClass('browser-default');
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
                $('#note').val(response.note);
                $('#start_date').val(response.start_date);
                $('#end_date').val(response.end_date);
                $('#age_limit_reminder').val(response.age_limit_reminder);
               
                
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

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
    }

</script>