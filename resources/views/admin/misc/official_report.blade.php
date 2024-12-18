<style>
    .modal {
        top:0px !important;
    }

    table > thead > tr > th {
        font-size: 13px !important;
    }

    .select-wrapper, .select2-container {
        height:auto !important;
    }
    .ql-editor strong{
     font-weight:bold;
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
                            <li class="breadcrumb-item active">{{ Str::title(str_replace('_',' ',Request::segment(3))) }}
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
                            <ul class="collapsible collapsible-accordion">
                                <li>
                                    <div class="collapsible-header"><i class="material-icons">filter_list</i>{{ __('translations.filter') }}</div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col m4 s6 ">
                                                <label for="filter_status" style="font-size:1rem;">Status :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_status" onchange="loadDataTable()">
                                                        <option value="">{{ __('translations.all') }}</option>
                                                        <option value="1">{{ __('translations.active') }}</option>
                                                        <option value="2">{{ __('translations.non_active') }}</option>
                                                    </select>
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
                                    <div class="row mt-2">
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
                                                        <th>Kode</th>
                                                        <th>{{ __('translations.user') }}</th>
                                                        <th>Perusahaan</th>
                                                        <th>Partner Bisnis</th>
                                                        <th>Tgl.BA</th>
                                                        <th>Tgl.Kejadian</th>
                                                        <th>Lokasi</th>
                                                        <th>Dokumen Bermasalah</th>
                                                        <th>Dokumen Pengalihan</th>
                                                        <th>Kronologi</th>
                                                        <th>Aksi/Tindakan</th>
                                                        <th>Keterangan</th>
                                                        <th>Lampiran</th>
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
                        <h6 class="red-text">EDIT AKAN MENGHAPUS FILE YANG ADA SEBELUMNYA. HATI-HATI!</h6>  
                        <div class="row">
                            <div class="col m12 s12">
                                <fieldset>
                                    <legend>1. Info Utama</legend>
                                    <div class="input-field col m2 s12">
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
                                    <div class="input-field col m3 s12">
                                        <input type="hidden" id="temp" name="temp">
                                        <select class="form-control" id="company_id" name="company_id">
                                            @foreach ($company as $rowcompany)
                                                <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                            @endforeach
                                        </select>
                                        <label class="" for="company_id">{{ __('translations.company') }}</label>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. post" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);">
                                        <label class="active" for="post_date">Tgl. Post</label>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <input id="incident_date" name="incident_date" max="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. Kejadian" value="{{ date('Y-m-d') }}">
                                        <label class="active" for="incident_date">Tgl. Kejadian</label>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <select class="browser-default" id="account_id" name="account_id"></select>
                                        <label class="active" for="account_id">Partner Bisnis</label>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <select class="form-control" id="place_id" name="place_id">
                                            @foreach ($place as $row)
                                                <option value="{{ $row->id }}">{{ $row->code }}</option>
                                            @endforeach
                                        </select>
                                        <label class="" for="place_id">Lokasi</label>
                                    </div>
                                    <div class="file-field input-field col m6 s12">
                                        <div class="btn">
                                            <span>Dokumen PO</span>
                                            <input type="file" name="file[]" id="file" multiple accept=".pdf, .xlsx, .xls, .jpeg, .jpg, .png, .gif, .word">
                                        </div>
                                        <div class="file-path-wrapper">
                                            <input class="file-path validate" type="text">
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col m12 s12">
                                <fieldset>
                                    <legend>2. Detail Informasi</legend>
                                    <div class="input-field col m4 s12">
                                        <h6>Sumber Dokumen</h6>
                                        <textarea class="browser-default" id="source_document" name="source_document" rows="10"></textarea>
                                    </div>
                                    <div class="input-field col m4 s12">
                                        <h6>Dokumen Pengalihan</h6>
                                        <textarea class="browser-default" id="target_document" name="target_document" rows="10"></textarea>
                                    </div>
                                    <div class="input-field col m4 s12">
                                        <h6>Kronologi / Kejadian / Penyebab</h6>
                                        <textarea class="browser-default" id="chronology" name="chronology" rows="10"></textarea>
                                    </div>
                                    <div class="input-field col m4 s12">
                                        <h6>Aksi / Tindakan</h6>
                                        <textarea class="browser-default" id="action" name="action" rows="10"></textarea>
                                    </div>
                                    <div class="input-field col m4 s12">
                                        <h6>Keterangan Tambahan</h6>
                                        <textarea class="browser-default" id="note" name="note" rows="10"></textarea>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col m12 s12">
                                <fieldset>
                                    <legend>3. Approver</legend>
                                    <div class="col m6 s12">
                                        <table class="bordered">
                                            <thead>
                                                <tr>
                                                    <th class="center">Nama</th>
                                                    <th class="center">{{ __('translations.delete') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-user">
                                                <tr id="last-row-user">
                                                    <td colspan="2" class="center">
                                                        <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addUser()" href="javascript:void(0);">
                                                            <i class="material-icons left">add</i> Tambah Karyawan
                                                        </a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light right submit" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
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
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div>

<script>
    var arrUser = [];

    $(function() {
        CKEDITOR.replace('note');
        CKEDITOR.replace('source_document');
        CKEDITOR.replace('target_document');
        CKEDITOR.replace('chronology');
        CKEDITOR.replace('action');
        
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        loadDataTable();

        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
               
            },
            onOpenEnd: function(modal, trigger) {
                window.onbeforeunload = function() {
                    return 'You will lose all changes made since your last save';
                };
                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                CKEDITOR.instances.note.setData('');
                CKEDITOR.instances.action.setData('');
                CKEDITOR.instances.chronology.setData('');
                CKEDITOR.instances.target_document.setData('');
                CKEDITOR.instances.source_document.setData('');
                window.onbeforeunload = function() {
                    return null;
                };
                $('#account_id').empty();
                M.updateTextFields();
                $('.row_user').remove();
                arrUser = [];
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

        select2ServerSide('#account_id', '{{ url("admin/select2/user") }}');

        $('#body-user').on('click', '.delete-data-user', function() {
            $(this).closest('tr').remove();
        });
    });

    function fillArrayUser(){
        arrUser = [];
        $('select[name^="arr_user[]"]').each(function(){
            if($(this).val()){
                arrUser.push($(this).val());
            }
        });
    }

    function addUser(){
        var count = makeid(10);
        $('#last-row-user').before(`
            <tr class="row_user">
                <td>
                    <select class="browser-default" id="arr_user` + count + `" name="arr_user[]" onchange="fillArrayUser();"></select>
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-user" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        $('#arr_user' + count).select2({
            placeholder: '-- Pilih approver --',
            minimumInputLength: 3,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/employee_for_ba") }}',
                type: 'GET',
                dataType: 'JSON',
                data: function(params) {
                    return {
                        search: params.term,
                        arr_user : arrUser,
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

    function exportExcel(){
        var search = window.table.search();
        var item = $('#item_id').val() ? $('#item_id').val():'';
        var inventory_type = $('#filter_type').val();
        window.location = "{{ Request::url() }}/export?search=" + search + "&item=" + item+"&inventory_type=" + inventory_type;
    }

    function loadDataTable() {
		window.table = $('#datatable_serverside').DataTable({
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
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle',
                'selectAll',
                'selectNone',
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
                },
            },
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    inventory_type : $('#filter_type').val(),
                    item : $('#item').val(),
                    modedata : '{{ $modedata }}',
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
                { name: 'code', className: 'center-align ' },
                { name: 'user_id', className: 'center-align' },
                { name: 'company_id', className: 'center-align' },
                { name: 'account_id', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'incident_date', className: 'center-align' },
                { name: 'plant_id', className: 'center-align' },
                { name: 'source_document', className: '' },
                { name: 'target_document', className: '' },
                { name: 'chronology', className: '' },
                { name: 'action', className: '' },
                { name: 'note', className: '' },
                { name: 'attachment', className: 'center-align' },
                { name: 'status', className: 'center-align' },
                { name: 'action', className: 'center-align' },
            ],
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

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
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

                let passedUser = true;

                if($('select[name^="arr_user[]"]').length > 0){
                    $('select[name^="arr_user[]"]').each(function(index){
                        if(!$(this).val()){
                            passedUser = false;
                        }
                    });
                }else{
                    passedUser = false;
                }

                if(!passedUser){
                    swal({
                        title: 'Ups, hayo!',
                        text: 'Approver tidak boleh kosong.',
                        icon: 'error'
                    });
                    return false;
                }

                var formData = new FormData($('#form_data')[0]);
                formData.append('source_document',CKEDITOR.instances.source_document.getData());
                formData.append('target_document',CKEDITOR.instances.target_document.getData());
                formData.append('note',CKEDITOR.instances.note.getData());
                formData.append('chronology',CKEDITOR.instances.chronology.getData());
                formData.append('action',CKEDITOR.instances.action.getData());

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
                        loadingOpen('#modal1');
                    },
                    success: function(response) {
                        loadingClose('#modal1');

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
                        loadingClose('#modal1');
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
                $('#code_place_id').val(response.code_place_id).formSelect();
                $('#code').val(response.code);
                $('#account_id').empty();
                $('#account_id').append(`
                    <option value="` + response.account_id + `">` + response.account_name + `</option>
                `);
                $('#account_id').append(`
                    <option value="` + response.account_id + `">` + response.account_name + `</option>
                `);
                $('#place_id').val(response.place_id).formSelect();
                $('#post_date').val(response.post_date);
                $('#incident_date').val(response.incident_date);
                CKEDITOR.instances.note.setData(response.note);
                CKEDITOR.instances.source_document.setData(response.source_document);
                CKEDITOR.instances.target_document.setData(response.target_document);
                CKEDITOR.instances.action.setData(response.action);
                CKEDITOR.instances.chronology.setData(response.chronology);
                $('.row_user').remove();
                $.each(response.approver, function(i, val) {
                    var count = makeid(10);
                    $('#last-row-user').before(`
                        <tr class="row_user">
                            <td>
                                <select class="browser-default" id="arr_user` + count + `" name="arr_user[]"></select>
                            </td>
                            <td class="center">
                                <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-user" href="javascript:void(0);">
                                    <i class="material-icons">delete</i>
                                </a>
                            </td>
                        </tr>
                    `);
                    $('#arr_user' + count).append(`
                        <option value="` + val.id + `">` + val.text + `</option>
                    `);
                    $('#arr_user' + count).select2({
                        placeholder: '-- Pilih approver --',
                        minimumInputLength: 3,
                        allowClear: true,
                        cache: true,
                        width: 'resolve',
                        dropdownParent: $('body').parent(),
                        ajax: {
                            url: '{{ url("admin/select2/employee_for_ba") }}',
                            type: 'GET',
                            dataType: 'JSON',
                            data: function(params) {
                                return {
                                    search: params.term,
                                    arr_user : arrUser,
                                };
                            },
                            processResults: function(data) {
                                return {
                                    results: data.items
                                }
                            }
                        }
                    });
                });

                fillArrayUser();
                
                $('.modal-content').scrollTop(0);
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
                    data: { id : id, msg : message  },
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