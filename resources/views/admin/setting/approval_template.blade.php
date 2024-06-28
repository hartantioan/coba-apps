<style>
    .modal {
        top:0px !important;
    }
</style>
<!-- BEGIN: Page Main-->
<div id="main">
    <div class="row">
        <div class="pt-3 pb-1" id="breadcrumbs-wrapper">
            <!-- Search for small screen-->
            <div class="container">
                <div class="row">
                    <div class="col s12 m6 l6">
                        <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ $title }}</span></h5>
                    </div>
                    <div class="col s12 m6 l6 right-align-md">
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
                                    <div class="col s6 ">
                                        <label for="filter_status" style="font-size:1.2rem;">{{ __('translations.filter_status') }} :</label>
                                        <div class="input-field inline" style="margin-top: 0;margin-bottom: 0;">
                                            <select class="form-control" id="filter_status" onchange="loadDataTable()">
                                                <option value="">{{ __('translations.all') }}</option>
                                                <option value="1">{{ __('translations.active') }}</option>
                                                <option value="2">{{ __('translations.non_active') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col s6">
                                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="exportExcel();">
                                            <i class="material-icons hide-on-med-and-up">view_list</i>
                                            <span class="hide-on-small-onl">Excel</span>
                                            <i class="material-icons right">view_list</i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">List Data
                                        <a href="{{ url('admin/setting/menu') }}" class="waves-effect waves-light btn gradient-45deg-purple-deep-orange gradient-shadow right">Kembali ke Menu</a>
                                    </h4>
                                    <div class="row mt-3">
                                        <div class="col s12">
                                            <table id="datatable_serverside" >
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{ __('translations.code') }}</th>
                                                        <th>{{ __('translations.user') }}</th>
                                                        <th>{{ __('translations.name') }}</th>
                                                        <th>{{ __('translations.type') }}</th>
                                                        <th>Cek Coa/Biaya?</th>
                                                        <th>Cek Nominal?</th>
                                                        <th>Cek Benchmark?</th>
                                                        <th>Tanda</th>
                                                        <th>Nominal Bawah</th>
                                                        <th>Nominal Atas</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>{{ __('translations.add') }}/{{ __('translations.edit') }} {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="input-field col s6">
                            <input type="hidden" id="temp" name="temp">
                            <input id="code" name="code" type="text" placeholder="Kode">
                            <label class="active" for="code">{{ __('translations.code') }}</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="name" name="name" type="text" placeholder="Nama">
                            <label class="active" for="name">{{ __('translations.name') }}</label>
                        </div>
                        <div class="input-field col s6">
                            <div class="switch mb-1">
                                <label for="status">{{ __('translations.status') }}</label>
                                <label class="right">
                                    {{ __('translations.non_active') }}
                                    <input checked type="checkbox" id="status" name="status" value="1">
                                    <span class="lever"></span>
                                   {{ __('translations.active') }}
                                </label>
                            </div>
                        </div>
                        <div class="input-field col s6">
                            <select class="select2 browser-default" multiple="multiple" id="item_group" name="item_group[]">
                                @foreach ($item_group as $row)
                                    <option value="{{ $row->id }}">{{ $row->code.' - '.$row->name }}</option>
                                @endforeach
                            </select>
                            <label class="active" for="item_group">Grup Item (Kosongi untuk tipe semua grup item)</label>
                        </div>
                        <div class="col s12 row">
                            <div class="col s4 row">
                                <h6 class="center-align">Apakah ada pengecekan Detail Coa/Biaya?</h6>
                                <div class="input-field col s12 center-align">
                                    <div class="switch mb-1">
                                        <label class="center">
                                            {{ __('translations.no') }}
                                            <input type="checkbox" id="is_coa_detail" name="is_coa_detail" value="1" onclick="">
                                            <span class="lever"></span>
                                            {{ __('translations.yes') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col s4 row">
                                <h6 class="center-align">Apakah ada syarat nominal?</h6>
                                <div class="input-field col s12 center-align">
                                    <div class="switch mb-1">
                                        <label class="center">
                                            {{ __('translations.no') }}
                                            <input type="checkbox" id="is_check_nominal" name="is_check_nominal" value="1" onclick="checkGrandtotal();">
                                            <span class="lever"></span>
                                            {{ __('translations.yes') }}
                                        </label>
                                    </div>
                                </div>
                            </div>  
                            <div class="col s4 row">
                                <h6 class="center-align">Apakah ada syarat benchmark?</h6>
                                <div class="input-field col s12 center-align">
                                    <div class="switch mb-1">
                                        <label class="center">
                                            {{ __('translations.no') }}
                                            <input type="checkbox" id="is_check_benchmark" name="is_check_benchmark" value="1" onclick="checkBenchmark();">
                                            <span class="lever"></span>
                                            {{ __('translations.yes') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="input-field col s3">
                                <select class="form-control" disabled id="nominal_type" name="nominal_type">
                                    <option value="1">Grandtotal (Rp)</option>
                                    <option value="2">Prosentase (%)</option>
                                </select>
                                <label class="" for="nominal_type">Tipe Nominal</label>
                            </div>
                            <div class="input-field col s3">
                                <select class="form-control" disabled id="sign" name="sign" onchange="applySign();">
                                    <option value=">">> (lebih dari) Nominal</option>
                                    <option value=">=">>= (lebih dari sama dengan) Nominal</option>
                                    <option value="=">= (sama dengan) Nominal</option>
                                    <option value="<">< (kurang dari) Nominal</option>
                                    <option value="<="><= (kurang dari sama dengan) Nominal</option>
                                    <option value="~">~ (dalam range) Nominal</option>
                                </select>
                                <label class="" for="sign">Operasi</label>
                            </div>
                            <div class="input-field col s3">
                                <input id="nominal" name="nominal" disabled type="text" placeholder="Nominal" onkeyup="formatRupiah(this)" value="0,00">
                                <label class="active" for="nominal">Nominal Batas Bawah</label>
                            </div>
                            <div class="input-field col s3" id="final-border" style="display:none;">
                                <input id="nominal_final" name="nominal_final" type="text" placeholder="Nominal" onkeyup="formatRupiah(this)">
                                <label class="active" for="nominal">Nominal Batas Atas</label>
                            </div>
                        </div>
                        <div class="col m12 s12">
                            <ul class="tabs">
                                <li class="tab col m4"><a class="active" href="#tab-originator">Originator</a></li>
                                <li class="tab col m4"><a href="#tab-stage">Tingkat (Stage)</a></li>
                                <li class="tab col m4"><a href="#tab-menu">Menu / Form</a></li>
                            </ul>
                            <div id="tab-originator" class="col s12 active">
                                <p class="mt-2 mb-2">
                                    <div style="overflow:auto;">
                                        <table class="bordered">
                                            <thead>
                                                <tr>
                                                    <th class="center">{{ __('translations.name') }}</th>
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
                                </p>
                            </div>
                            <div id="tab-stage" class="col s12">
                                <p class="mt-2 mb-2">
                                    Mohon diperhatikan, urutan stage akan berpengaruh terhadap urutan approval. Pastikan urutan sudah benar.
                                    <div style="overflow:auto;">
                                        <table class="bordered">
                                            <thead>
                                                <tr>
                                                    <th class="center">{{ __('translations.name') }}</th>
                                                    <th class="center">{{ __('translations.delete') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-stage">
                                                <tr id="last-row-stage">
                                                    <td colspan="2" class="center">
                                                        <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addStage()" href="javascript:void(0);">
                                                            <i class="material-icons left">add</i> Tambah Tingkat Approval / Stage
                                                        </a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </p>
                            </div>
                            <div id="tab-menu" class="col s12">
                                <p class="mt-2 mb-2">
                                    <div style="overflow:auto;">
                                        <table class="bordered">
                                            <thead>
                                                <tr>
                                                    <th class="center">{{ __('translations.name') }}</th>
                                                    <th class="center">{{ __('translations.delete') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-menu">
                                                <tr id="last-row-menu">
                                                    <td colspan="2" class="center">
                                                        <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addMenu()" href="javascript:void(0);">
                                                            <i class="material-icons left">add</i> Tambah Menu / Form
                                                        </a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </p>
                            </div>
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

<!-- END: Page Main-->
<script>
    $(function() {
        $(".select2").select2({
            width: '100%',
        });
        
        loadDataTable();

        $('#modal4').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#show_detail').empty();
            }
        });
        
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) {
                $('#code').focus();
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
                $('.tabs').tabs();
            },
            onCloseEnd: function(modal, trigger){
                $('#temp').val('');
                M.updateTextFields();
                $('#status').prop( "checked", true);
                $('#is_check_nominal').prop( "checked", false);
                $('#is_check_benchmark').prop( "checked", false);
                $('#sign').attr('disabled',true).formSelect();
                $('#nominal_type').attr('disabled',true).formSelect();
                $('#nominal').attr('disabled',true);
                $('#final-border').hide();
                $('#form_data')[0].reset();
                $('.row_user').each(function(){
                    $(this).remove();
                });
                $('.row_stage').each(function(){
                    $(this).remove();
                });
                $('.row_menu').each(function(){
                    $(this).remove();
                });
                $('#item_group').val(null).trigger("change");
            }
        });

        $('#body-user').on('click', '.delete-data-user', function() {
            $(this).closest('tr').remove();
        });
        
        $('#body-stage').on('click', '.delete-data-stage', function() {
            $(this).closest('tr').remove();
        });

        $('#body-menu').on('click', '.delete-data-menu', function() {
            $(this).closest('tr').remove();
        });

        $('#is_check_nominal').click(function(){
            if($(this).is(':checked')){
                $('#sign').attr('disabled',false).formSelect();
                $('#nominal').attr('disabled',false);
                $('#nominal_type').attr('disabled',false).formSelect();
                if($('#is_check_nominal').is(':checked')){
                    $('#nominal_type').val('1').formSelect();
                }
                if($('#is_check_benchmark').is(':checked')){
                    $('#is_check_benchmark').prop( "checked", false);
                }
            }else{
                $('#sign').val('>').trigger('change').formSelect();
                $('#final-border').hide();
                $('#sign').attr('disabled',true).formSelect();
                $('#nominal').attr('disabled',true);
                $('#nominal_type').attr('disabled',true).formSelect();
            }
        });

        $('#is_check_benchmark').click(function(){
            if($(this).is(':checked')){
                $('#sign').attr('disabled',false).formSelect();
                $('#nominal').attr('disabled',false);
                $('#nominal_type').attr('disabled',false).formSelect();
                if($('#is_check_benchmark').is(':checked')){
                    $('#nominal_type').val('2').formSelect();
                }
                if($('#is_check_nominal').is(':checked')){
                    $('#is_check_nominal').prop( "checked", false);
                }
            }else{
                $('#sign').val('>').trigger('change').formSelect();
                $('#final-border').hide();
                $('#sign').attr('disabled',true).formSelect();
                $('#nominal').attr('disabled',true);
                $('#nominal_type').attr('disabled',true).formSelect();
            }
        });
    });

    function applySign(){
        $('#nominal_final').val('0,00');
        if($('#sign').val() == '~'){
            $('#final-border').show();
        }else{
            $('#final-border').hide();
        }
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

    function addUser(){
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
        select2ServerSide('#arr_user' + count, '{{ url("admin/select2/employee") }}');
    }

    function addStage(){
        var count = makeid(10);
        $('#last-row-stage').before(`
            <tr class="row_stage">
                <td>
                    <select class="browser-default" id="arr_approval_stage` + count + `" name="arr_approval_stage[]"></select>
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-stage" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        select2ServerSide('#arr_approval_stage' + count, '{{ url("admin/select2/approval_stage") }}');
    }

    function addMenu(){
        var count = makeid(10);
        $('#last-row-menu').before(`
            <tr class="row_menu">
                <td>
                    <select class="browser-default" id="arr_approval_menu` + count + `" name="arr_approval_menu[]" onchange="checkGrandtotal();checkBenchmark();"></select>
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-menu" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        select2ServerSide('#arr_approval_menu' + count, '{{ url("admin/select2/menu") }}');
    }

    function checkBenchmark(){
        if($('#is_check_benchmark').is(':checked')){
            $('select[name^="arr_approval_menu"]').each(function(){
                if($(this).val()){
                    if($(this).select2('data')[0].hasGrandtotal == '0'){
                        swal({
                            title: 'Ups!',
                            text: 'Menu ini tidak memiliki grandtotal.',
                            icon: 'warning'
                        });
                        $(this).empty();
                    }
                }
            });
        }
    }

    function checkGrandtotal(){
        if($('#is_check_nominal').is(':checked')){
            $('select[name^="arr_approval_menu"]').each(function(){
                if($(this).val()){
                    if($(this).select2('data')[0].hasGrandtotal == '0'){
                        swal({
                            title: 'Ups!',
                            text: 'Menu ini tidak memiliki grandtotal.',
                            icon: 'warning'
                        });
                        $(this).empty();
                    }
                }
            });
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
                { name: 'code', className: 'center-align' },
                { name: 'user_id', className: 'center-align' },
                { name: 'name', className: 'center-align' },
                { name: 'nominal_type', className: 'center-align' },
                { name: 'is_coa_detail', className: 'center-align' },
                { name: 'is_check_nominal', className: 'center-align' },
                { name: 'is_check_benchmark', className: 'center-align' },
                { name: 'sign', className: 'center-align' },
                { name: 'nominal', className: 'center-align' },
                { name: 'nominal_final', className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ]
        });
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
            if (willDelete) {
                var passed = true, passedNominal = true;

                if($('#is_check_nominal').is(':checked')){
                    passed = false;

                    if($('#sign').val() && parseFloat($('#nominal').val().replaceAll(".", "").replaceAll(",",".")) > 0){
                        passed = true;
                    }
                    if($('#sign').val() == '~'){
                        if(parseFloat($('#nominal').val().replaceAll(".", "").replaceAll(",",".")) > parseFloat($('#nominal_final').val().replaceAll(".", "").replaceAll(",","."))){
                            passedNominal = false;
                        }
                    }
                }

                if($('#is_check_benchmark').is(':checked')){
                    passed = false;

                    if($('#sign').val() && parseFloat($('#nominal').val().replaceAll(".", "").replaceAll(",",".")) > 0){
                        passed = true;
                    }
                    if($('#sign').val() == '~'){
                        if(parseFloat($('#nominal').val().replaceAll(".", "").replaceAll(",",".")) > parseFloat($('#nominal_final').val().replaceAll(".", "").replaceAll(",","."))){
                            passedNominal = false;
                        }
                    }
                }

                if(passed == true){
                    if(passedNominal == true){
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
                                loadingOpen('#modal1');
                            },
                            success: function(response) {
                                loadingClose('#modal1');
                                if(response.status == 200) {
                                    $('#parent_id').empty();

                                    $.each(response.data, function(i, val) {
                                        $('#parent_id').append(val);
                                    });
                                    
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
                    }else{
                        M.toast({
                            html: 'Syarat nominal untuk range adalah nominal batas bawah tidak boleh lebih dari nominal batas atas.'
                        });
                    }
                }else{
                    M.toast({
                        html: 'Tanda operasi matematika dan nominal tidak boleh kosong.'
                    });
                }
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
                $('#code').val(response.code);
                $('#name').val(response.name);
                $('#item_group').val(response.itemgroups).trigger('change');

                if(response.status == '1'){
                    $('#status').prop( "checked", true);
                }else{
                    $('#status').prop( "checked", false);
                }

                if(response.details.length > 0){
                    $('.row_user').each(function(){
                        $(this).remove();
                    });

                    $('.row_stage').each(function(){
                        $(this).remove();
                    });

                    $('.row_menu').each(function(){
                        $(this).remove();
                    });

                    $.each(response.details, function(i, val) {
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

                        select2ServerSide('#arr_user' + count, '{{ url("admin/select2/employee") }}');
                        
                        $('#arr_user' + count).append(`
                            <option value="` + val.user_id + `">` + val.user_name + `</value>
                        `);
                    });

                    $.each(response.stages, function(i, val) {
                        var count = makeid(10);
                        $('#last-row-stage').before(`
                            <tr class="row_stage">
                                <td>
                                    <select class="browser-default" id="arr_approval_stage` + count + `" name="arr_approval_stage[]"></select>
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-stage" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);

                        select2ServerSide('#arr_approval_stage' + count, '{{ url("admin/select2/approval_stage") }}');
                        
                        $('#arr_approval_stage' + count).append(`
                            <option value="` + val.approval_stage_id + `">` + val.approval_stage_code + `</value>
                        `);
                    });

                    $.each(response.menus, function(i, val) {
                        var count = makeid(10);
                        $('#last-row-menu').before(`
                            <tr class="row_menu">
                                <td>
                                    <select class="browser-default" id="arr_approval_menu` + count + `" name="arr_approval_menu[]"></select>
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-menu" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);

                        select2ServerSide('#arr_approval_menu' + count, '{{ url("admin/select2/menu") }}');
                        
                        $('#arr_approval_menu' + count).append(`
                            <option value="` + val.menu_id + `">` + val.menu_name + `</value>
                        `);
                    });
                }

                if(response.sign == '~'){
                    $('#final-border').show();
                    $('#nominal_final').val(response.nominal_final);
                }else{
                    $('#final-border').hide();
                }

                if(response.is_coa_detail == '1'){
                    $('#is_coa_detail').prop( "checked", true);
                }

                if(response.is_check_benchmark == '1'){
                    $('#is_check_benchmark').prop( "checked", true);
                    $('#sign').attr('disabled',false).formSelect();
                    $('#nominal').attr('disabled',false);
                    $('#nominal_type').attr('disabled',false).formSelect();
                    $('#sign').val(response.sign).formSelect();
                    $('#nominal').val(response.nominal);
                    $('#nominal_type').val(response.nominal_type).formSelect();
                }else if(response.is_check_nominal == '1'){
                    $('#is_check_nominal').prop( "checked", true);
                    $('#sign').attr('disabled',false).formSelect();
                    $('#nominal').attr('disabled',false);
                    $('#nominal_type').attr('disabled',false).formSelect();
                    $('#sign').val(response.sign).formSelect();
                    $('#nominal').val(response.nominal);
                    $('#nominal_type').val(response.nominal_type).formSelect();
                }else{
                    $('#is_check_benchmark').prop( "checked", false);
                    $('#is_check_nominal').prop( "checked", false);
                    $('#sign').attr('disabled',true).formSelect();
                    $('#nominal').attr('disabled',true);
                }

                $('.modal-content').scrollTop(0);
                $('#code').focus();
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

    function duplicate(id){
        swal({
            title: "Apakah anda yakin ingin salin?",
            text: "Pastikan approval yang ingin anda salin sudah sesuai!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
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
                        
                        $('#temp').val('');
                        $('#code').val(response.code);
                        $('#name').val(response.name);
                        $('#item_group').val(response.itemgroups).trigger('change');

                        if(response.status == '1'){
                            $('#status').prop( "checked", true);
                        }else{
                            $('#status').prop( "checked", false);
                        }

                        if(response.details.length > 0){
                            $('.row_user').each(function(){
                                $(this).remove();
                            });

                            $('.row_stage').each(function(){
                                $(this).remove();
                            });

                            $('.row_menu').each(function(){
                                $(this).remove();
                            });

                            $.each(response.details, function(i, val) {
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

                                select2ServerSide('#arr_user' + count, '{{ url("admin/select2/employee") }}');
                                
                                $('#arr_user' + count).append(`
                                    <option value="` + val.user_id + `">` + val.user_name + `</value>
                                `);
                            });

                            $.each(response.stages, function(i, val) {
                                var count = makeid(10);
                                $('#last-row-stage').before(`
                                    <tr class="row_stage">
                                        <td>
                                            <select class="browser-default" id="arr_approval_stage` + count + `" name="arr_approval_stage[]"></select>
                                        </td>
                                        <td class="center">
                                            <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-stage" href="javascript:void(0);">
                                                <i class="material-icons">delete</i>
                                            </a>
                                        </td>
                                    </tr>
                                `);

                                select2ServerSide('#arr_approval_stage' + count, '{{ url("admin/select2/approval_stage") }}');
                                
                                $('#arr_approval_stage' + count).append(`
                                    <option value="` + val.approval_stage_id + `">` + val.approval_stage_code + `</value>
                                `);
                            });

                            $.each(response.menus, function(i, val) {
                                var count = makeid(10);
                                $('#last-row-menu').before(`
                                    <tr class="row_menu">
                                        <td>
                                            <select class="browser-default" id="arr_approval_menu` + count + `" name="arr_approval_menu[]"></select>
                                        </td>
                                        <td class="center">
                                            <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-menu" href="javascript:void(0);">
                                                <i class="material-icons">delete</i>
                                            </a>
                                        </td>
                                    </tr>
                                `);

                                select2ServerSide('#arr_approval_menu' + count, '{{ url("admin/select2/menu") }}');
                                
                                $('#arr_approval_menu' + count).append(`
                                    <option value="` + val.menu_id + `">` + val.menu_name + `</value>
                                `);
                            });
                        }

                        if(response.sign == '~'){
                            $('#final-border').show();
                            $('#nominal_final').val(response.nominal_final);
                        }else{
                            $('#final-border').hide();
                        }

                        if(response.is_coa_detail == '1'){
                            $('#is_coa_detail').prop( "checked", true);
                        }

                        if(response.is_check_benchmark == '1'){
                            $('#is_check_benchmark').prop( "checked", true);
                            $('#sign').attr('disabled',false).formSelect();
                            $('#nominal').attr('disabled',false);
                            $('#nominal_type').attr('disabled',false).formSelect();
                            $('#sign').val(response.sign).formSelect();
                            $('#nominal').val(response.nominal);
                            $('#nominal_type').val(response.nominal_type).formSelect();
                        }else if(response.is_check_nominal == '1'){
                            $('#is_check_nominal').prop( "checked", true);
                            $('#sign').attr('disabled',false).formSelect();
                            $('#nominal').attr('disabled',false);
                            $('#nominal_type').attr('disabled',false).formSelect();
                            $('#sign').val(response.sign).formSelect();
                            $('#nominal').val(response.nominal);
                            $('#nominal_type').val(response.nominal_type).formSelect();
                        }else{
                            $('#is_check_benchmark').prop( "checked", false);
                            $('#is_check_nominal').prop( "checked", false);
                            $('#sign').attr('disabled',true).formSelect();
                            $('#nominal').attr('disabled',true);
                        }

                        $('.modal-content').scrollTop(0);
                        $('#code').focus();
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

    function exportExcel(){
        var search = window.table.search();
        var status = $('#filter_status').val();
        
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status;
    }
</script>