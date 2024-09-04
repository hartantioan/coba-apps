<!-- BEGIN: Page Main-->
<style>
    #modal6 {
        top:0px !important;
    }
    
    body.tab-active input:focus {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .modal-content .select2.tab-active {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }
</style>
<link rel="stylesheet" type="text/css" href="{{ url('app-assets/css/pages/page-timeline.css') }}">
<div id="main">
    <div class="row">
        
        <div class="pt-3 pb-1" id="breadcrumbs-wrapper">
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
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3 modal-trigger" href="#modal2">
                            <i class="material-icons hide-on-med-and-up">file_download</i>
                            <span class="hide-on-small-onl">{{ __('translations.import') }}</span>
                            <i class="material-icons right">file_download</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="loadDataTable();">
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
                            <div class="card-panel">
                                <div class="row">
                                    <div class="col m4 s12 ">
                                        <label for="filter_status" style="font-size:1.2rem;">{{ __('translations.filter_status') }} :</label>
                                        <div class="input-field inline" style="margin-top: 0;margin-bottom: 0;">
                                            <select class="form-control" id="filter_status" onchange="loadDataTable()">
                                                <option value="">{{ __('translations.all') }}</option>
                                                <option value="1">{{ __('translations.active') }}</option>
                                                <option value="2">{{ __('translations.non_active') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col m4 s12 ">
                                        <label for="filter_group" style="font-size:1.2rem;">Filter Group :</label>
                                        <div class="input-field inline" style="margin-top: 0;margin-bottom: 0;">
                                            <select class="form-control" id="filter_group" name="filter_group" onchange="loadDataTable()">
                                                <option value="">Semua</option>
                                                @foreach($group as $row)
                                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col m4 s12">
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
                                    <h4 class="card-title">{{ __('translations.list_data') }}</h4>
                                    <div class="row">
                                        <div class="col s12">
                                            <div id="datatable_buttons"></div>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">{{ __('translations.refresh') }}</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="printDataA4();">
                                                <i class="material-icons hide-on-med-and-up">local_printshop</i>
                                                <span class="hide-on-small-onl">{{ __('translations.print') }} A4 Multi</span>
                                                <i class="material-icons right">local_printshop</i>
                                            </a>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="printDataStickerMulti();">
                                                <i class="material-icons hide-on-med-and-up">local_printshop</i>
                                                <span class="hide-on-small-onl">{{ __('translations.print') }} Sticker Multi</span>
                                                <i class="material-icons right">local_printshop</i>
                                            </a>
                                            
                                            <table id="datatable_serverside" >
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{ __('translations.code') }}</th>
                                                        <th>{{ __('translations.item') }}</th>
                                                        <th>Group</th>
                                                    
                                                        <th>Detail</th>
                                                        <th>Tipe</th>
                                                        <th>Kode Serah Terima</th>
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
                            <select class="browser-default" id="item_id" name="item_id" onchange="itemChanged()">&nbsp;</select>
                            <label class="active" for="item_id">Pilih Item dari inventory</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <select class="browser-default" id="item_group_id" name="item_group_id" onchange="getDepartment();" >&nbsp;</select>
                            <label class="active" for="item_group_id">Group Departement</label>
                        </div>
                        <div class="input-field col s12 m12">
                        <table class="bordered"id="table-detail">
                            <thead>
                                <tr>
                                    <th>{{ __('translations.delete') }}</th>
                                    <th>{{ __('translations.code') }}</th>
                                    <th>Detail 1</th>
                                </tr>
                            </thead>
                            <tbody id="body-item">
                                <tr id="last-row-item">
                                    <td colspan="4">
                                        <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" id="button-add-item" onclick="addItem()" href="javascript:void(0);" disabled>
                                            <i class="material-icons left">add</i> New Item
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                        <div class="input-field col s12 m6">
                            <div class="switch mb-1">
                                <label for="status">{{ __('translations.status') }}</label>
                                <label>
                                    {{ __('translations.non_active') }}
                                    <input checked type="checkbox" id="status" name="status">
                                    <span class="lever"></span>
                                   {{ __('translations.active') }}
                                </label>
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

<div id="modal2" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;max-width:90%;min-width:90%;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>{{ __('translations.import') }} Excel</h4>
                <div class="col s12">
                    <div id="validation_alertImport" style="display:none;"></div>
                </div>
                <form class="row" action="{{ Request::url() }}/import" method="POST" enctype="multipart/form-data" id="form_dataimport">
                    @csrf
                    <div class="file-field input-field col m6 s12">
                        <div class="btn">
                            <span>Dokumen Excel</span>
                            <input type="file" class="form-control-file" id="fileExcel" name="file">
                        </div>
                        <div class="file-path-wrapper">
                            <input class="file-path validate" type="text">
                        </div>
                    </div>
                    <div class="input-field col m6 s12">
                        <h6>Anda bisa menggunakan fitur upload dokumen excel. Silahkan klik <a href="{{ Request::url() }}/get_import_excel" target="_blank">disini</a> untuk mengunduh.</h6>
                    </div>
                    <div class="input-field col m12 s12">
                        <button type="submit" class="btn cyan btn-primary btn-block right">Kirim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>

<div id="modalEdit" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Tambah Edit {{ $title }}</h4>
                <form class="row" id="form_data_edit" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert_edit"></div>
                    </div>
                    <div class="col s12">
                        {{-- <div class="input-field col s12 m6">
                            <select class="browser-default" id="item_id_edit" name="item_id_edit">&nbsp;</select>
                            <label class="active" for="item_id_edit">Pilih Item dari inventory</label>
                        </div> --}}
                        <div class="input-field col s12 m6">
                          
                            <input id="code" name="code" type="text" placeholder="Kode" disabled>
                            <label class="active" for="code">{{ __('translations.code') }}</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <input type="hidden" id="temp" name="temp">
                            <input id="item" name="item" type="text" placeholder="Nama Item">
                            <label class="active" for="item">{{ __('translations.item') }}</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <select class="browser-default" id="item_group_id_edit" name="item_group_id_edit" onchange="getDepartment();">&nbsp;</select>
                            <label class="active" for="item_group_id_edit">Group Departement</label>
                        </div>
                        
                        <div class="input-field col s12 m6">
                            <input id="detail1_edit" name="detail1_edit" type="text" placeholder="Keterangan">
                            <label class="active" for="detail1_edit">Detail </label>
                        </div>

                        <div class="input-field col s12 m6">
                            <div class="switch mb-1">
                                <label for="order">{{ __('translations.status') }}</label>
                                <label>
                                    {{ __('translations.non_active') }}
                                    <input checked type="checkbox" id="status_edit" name="status" value="1">
                                    <span class="lever"></span>
                                   {{ __('translations.active') }}
                                </label>
                            </div>
                        </div>
                        <div class="col s12 mt-3">
                            <button class="btn waves-effect waves-light right submit" onclick="saveEdit();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
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

<div id="modal6" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;width:100%;top:0 !important;">
    <div class="modal-content">
        <div class="row" >
            <div class="col m3 s12">
                
            </div>
            <div class="col m6 s12">
                <h4 id="title_data" style="text-align:center"></h4>
                <h5 id="code_data" style="text-align:center"></h5>
            </div>
            <div class="col m3 s12 right-align">
                <img src="{{ url('website/logo_web_fix.png') }}" width="40%" height="60%">
            </div>
        </div>
        <ul class="tabs">
            <li class="tab">
                <a href="#user_history" class="" id="part-tabs-btn">
                <span>User History</span>
                </a>
            </li>
            <li class="tab">
                <a href="#goods_history" class="">
                <span>Goods History</span>
                </a>
            </li>
            <li class="indicator" style="left: 0px; right: 0px;"></li>
        </ul>
        <div class="row mt-2">
            
            <div id="user_history" style="display: block;" class="">
                <ul class="">
                    <li class="tab">
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" id="table-btn">
                        <span>Tabel</span>
                        </a>
                    </li>
                    <li class="tab">
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" id="timeline-btn" style="margin-bottom: 2rem;">
                        <span>Time Line</span>
                        </a>
                    </li>
                    <li class="indicator" style="left: 0px; right: 0px;"></li>
                </ul>
                <ul class="timeline" id="body-history-user" style="padding-left: 4rem; padding-right:4rem;">
                    
                    
                </ul>
                <table class="bordered Highlight striped" id="table_history_user">
                    <thead>
                            <tr>
                                <th class="center-align">No</th>
                                <th class="center-align">{{ __('translations.code') }}</th>
                                <th class="center-align">Date</th>
                                <th class="center-align">User</th>
                                <th class="center-align">Info</th>
                                <th class="center-align">{{ __('translations.action') }}</th>
                            </tr>                  
                    </thead>
                    <tbody id="body-history-user1">
                    </tbody>
                </table>
                
            </div>
            <div id="goods_history" style="display: none;" class="">
                <ul class="">
                    <li class="tab">
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" id="table-btn_good">
                        <span>Tabel</span>
                        </a>
                    </li>
                    <li class="tab">
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" id="timeline-btn_good" style="margin-bottom: 2rem;">
                        <span>Time Line</span>
                        </a>
                    </li>
                    <li class="indicator" style="left: 0px; right: 0px;"></li>
                </ul>
                <ul class="timeline" id="body-history-goods" style="padding-left: 4rem; padding-right:4rem;">
                    
                    
                </ul>
                <table class="bordered Highlight striped" id="table_history_goods">
                    <thead>
                            <tr>
                                <th class="center-align">No</th>
                                <th class="center-align">{{ __('translations.code') }}</th>
                                <th class="center-align">Date</th>
                                <th class="center-align">User</th>
                                <th class="center-align">Detail 1</th>
                                <th class="center-align">{{ __('translations.action') }}</th>
                            </tr>                  
                    </thead>
                    <tbody id="body-history-goods1">
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modalEdit" onclick="getCode();">
        <i class="material-icons">add</i>
    </a>
</div>

<!-- END: Page Main-->
<script>
    var itemStock = 0;
    var itemOnAdd = 0;
    var currentHW = 0;
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
        $(document).keydown(function(e) {
            if (e.ctrlKey && e.keyCode == 13) {
                if($('#modalEdit').length > 0){
                    $('#modalEdit').modal('open');
                    $('#modal1').modal('close');
                }
                if($('#modal1').length > 0){
                    $('#modal1').modal('close');
                }
            }
        });
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
                $('#temp').val('');
                M.updateTextFields();
            }
        });

        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
            itemOnAdd--;
        });

        $('#modal6').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#title_data').empty();
                $('#body-history-user').empty();
                $('#body-history-user1').empty();
                $('#body-history-goods').empty();
                $('#body-history-goods1').empty();
            }
        });
        $('#modal2').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onCloseEnd: function(modal, trigger){
                $('#form_dataimport')[0].reset();
            }
        });
        $('#modalEdit').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
                $('#name').focus();
                $('#validation_alert_edit').hide();
                $('#validation_alert_edit').html('');
                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data_edit')[0].reset();
                $('#temp').val('');
                $('#item_id_edit').empty();
                $('#item_group_id_edit').empty();
                M.updateTextFields();
            }
        });
        select2ServerSide('#item_id_edit', '{{ url("admin/select2/item_for_hardware_item") }}');
        select2ServerSide('#item_group_id_edit', '{{ url("admin/select2/hardware_item_group") }}');
        select2ServerSide('#item_id', '{{ url("admin/select2/item_for_hardware_item") }}');
        select2ServerSide('#item_group_id', '{{ url("admin/select2/hardware_item_group") }}');

        $('#form_dataimport').submit(function(event) {
            event.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                type: $(this).attr('method'),
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('#validation_alertImport').hide();
                    $('#validation_alertImport').html('');
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    loadingClose('.modal-content');
                    loadDataTable();
                    $('#modal2').modal('close');
                    if(response.status === 200) {
                        successImport();
                        M.toast({
                            html: response.message
                        });
                    } else if(response.status === 400 || response.status === 432) {
                        $('#validation_alertImport').show();
                        $('.modal-content').scrollTop(0);
                        
                       
                    } else {
                        M.toast({
                            html: response.message
                        });
                    }
                },
                error: function(response) {
                    loadingClose('.modal-content');

                    if(response.status === 422) {
                        $('#validation_alertImport').show();
                        $('.modal-content').scrollTop(0);

                        swal({
                            title: 'Ups! Validation',
                            text: 'Check your form.',
                            icon: 'warning'
                        });

                        let errorMessage = '';
                        response.responseJSON.errors.forEach(function(error) {
                            errorMessage += error.errors.join('\n') + '\n';
                        });

                        $('#validation_alertImport').html(`
                            <div class="card-alert card red">
                                <div class="card-content white-text">
                                    <p>${errorMessage}</p>
                                </div>
                                <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                        `).show();
                    }else if(response.status === 400 || response.status === 432) {
                        $('#validation_alertImport').show();
                        $('.modal-content').scrollTop(0);
                       
                        let errorMessage = response.status === 400 ? 
                            `<p> Baris <b>${response.responseJSON.row}</b> </p><p>${response.responseJSON.error}</p><p> di Lembar ${response.responseJSON.sheet}</p><p> Kolom : ${response.responseJSON.column}</p>` : 
                            `<p>${response.responseJSON.message}</p><p> di Lembar ${response.responseJSON.sheet}</p>`;

                        $('#validation_alertImport').append(`
                            <div class="card-alert card red">
                                <div class="card-content white-text">
                                    ${errorMessage}
                                </div>
                                <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                        `);
                    } else {
                        M.toast({
                            html: response.message
                        });
                    }
                }
            });
        });
    });

    function addItem(){
        var count = makeid(10);
        var totalx = currentHW + itemOnAdd;

        if(itemStock > parseInt(totalx)){
            $('#last-row-item').before(`
                <tr class="row_item">
                    <input type="hidden" name="arr_data[]" value="0">
                    <input type="hidden" name="arr_type[]" value="">
                    <td class="center">
                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                            <i class="material-icons">delete</i>
                        </a>
                    </td>
                    <td>
                        <input name="arr_code[]" class="materialize-textarea" type="text" placeholder="Kode Barang">
                    </td>
                    <td>
                        <input name="arr_detail1[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 1...">
                    </td>
                </tr>
            `);
            itemOnAdd++;
        }else{
            
            alert("Jumlah Stock sudah sama dengan total inventaris yang akan ditambahkan");
        }
        
    }

    function loadDataTable() {
		window.table = $('#datatable_serverside').DataTable({
            "scrollCollapse": true,
            "scrollY": '400px',
            "responsive": false,
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
                { name: 'id', searchable: false, className: 'center-align details-control' },
                { name: 'code', className: 'center-align' },
                { name: 'item_id', className: 'center-align' },
                { name: 'hardware_item_group_id', className: 'center-align' },
                { name: 'info', className: 'center-align' },
                { name: 'info', className: 'center-align' },
                { name: 'code-ref', className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ],
            select: {
                style: 'multi'
            },
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle',
                'selectAll',
                'selectNone',
            ],
        });
        $('.dt-buttons').appendTo('#datatable_buttons');

        $('select[name="datatable_serverside_length"]').addClass('browser-default');
	}

    function getDepartment(){
        var selectElement = document.getElementById("item_group_id");
        var selectedOption = selectElement.options[selectElement.selectedIndex];
        
        if(selectedOption){
            var selectedText = selectedOption.text;
            var textBeforeHyphen = selectedText.split("-")[0];
            document.getElementById("code").value = textBeforeHyphen+'-';
        }
        
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
    function saveEdit(){
			
        var formData = new FormData($('#form_data_edit')[0]);
        
        $.ajax({
            url: '{{ Request::url() }}/edit',
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
                $('#validation_alert_edit').hide();
                $('#validation_alert_edit').html('');
                loadingOpen('.modal-content');
            },
            success: function(response) {
                loadingClose('.modal-content');
                if(response.status == 200) {
                    $('#modalEdit').modal('close');
                    loadDataTable();
                    M.toast({
                        html: response.message
                    });
                } else if(response.status == 422) {
                    $('#validation_alert_edit').show();
                    $('.modal-content').scrollTop(0);
                    
                    swal({
                        title: 'Ups! Validation',
                        text: 'Check your form.',
                        icon: 'warning'
                    });

                    $.each(response.error, function(i, val) {
                        $.each(val, function(i, val) {
                            $('#validation_alert_edit').append(`
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
                $('#modalEdit').modal('open');
                $('#temp').val(id);
                $('#code').val(response.code);
                $('#item').val(response.item);
                
                $('#item_group_id_edit').append(`
                    <option value="` + response.group_item.id + `">` +response.group_item.name+`</option>
                `);
                $('#detail1_edit').val(response.detail1);
        
                if(response.status == '1'){
                    $('#status_edit').prop( "checked", true);
                }else{
                    $('#status_edit').prop( "checked", false);
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

    function printDataA4(){
        var arr_id_temp=[];
        $.map(window.table.rows('.selected').nodes(), function (item) {
            var poin = $(item).find('td:nth-child(2)').text().trim();
            arr_id_temp.push(poin);
        });
        $.ajax({
            url: '{{ Request::url() }}/print_multi_a4',
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
                window.open(response.message, '_blank');
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

    function printDataStickerMulti(){
        var arr_id_temp=[];
        $.map(window.table.rows('.selected').nodes(), function (item) {
            var poin = $(item).find('td:nth-child(2)').text().trim();
            arr_id_temp.push(poin);
        });
        $.ajax({
            url: '{{ Request::url() }}/print_multi_sticker',
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
                window.open(response.message, '_blank');
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
   

    function itemChanged(){
        itemStock = 0;
        currentHW = 0;
        itemOnAdd = 0;
        $('#body-item').empty();
        $('#body-item').append(`
            <tr id="last-row-item">
                <td colspan="4">
                    <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" id="button-add-item" onclick="addItem()" href="javascript:void(0);">
                        <i class="material-icons left">add</i> New Item
                    </a>
                </td>
            </tr>
        `);
        if($('#item_id').val()){
            itemStock = parseInt($('#item_id').select2('data')[0].total_stock);
            currentHW = $('#item_id').select2('data')[0].total_hw_item;
        }else{
            itemStock = 0;
            currentHW = 0;
        }
    }

    function historyUsage(id){
        $('#table_history_user').show();
        $('#body-history-user').hide();
        $('#table_history_goods').show();
        $('#body-history-goods').hide();

        $('#table-btn_good').click(function() {
            $('#table_history_goods').show();
            $('#body-history-goods').hide();
        });

        $('#timeline-btn_good').click(function() {
            $('#table_history_goods').hide();
            $('#body-history-goods').show();
        });

        $('#table-btn').click(function() {
            $('#table_history_user').show();
            $('#body-history-user').hide();
        });

        $('#timeline-btn').click(function() {
            $('#table_history_user').hide();
            $('#body-history-user').show();
        });
        $.ajax({
            url: '{{ Request::url() }}/history_usage',
            type: 'POST',
            dataType: 'JSON',
            data: {
                id: id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            complete: function() {
                
            },
            success: function(data){
                loadingClose('.modal-content');
                if(data.status == '500'){
                    M.toast({
                        html: data.message
                    });
                }else{
                    $('#modal6').modal('open');
                    $('#title_data').append(``+data.title+``);
                    $('#body-history-user').append(data.tbody);
                    $('#body-history-user1').append(data.tbody1);
                    $('#body-history-goods').append(data.tbodyR);
                    $('#body-history-goods1').append(data.tbody1R);
                    $(".tooltipped").tooltip({
                        delay: 50
                    });
                }
            }
        });
    }

    function printBarcode(id){
         $.ajax({
            type : "POST",
            url  : '{{ Request::url() }}/print_barcode',
            data : {
                id:id   
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            cache: false,
            success: function(data){
               
                window.open(data, '_blank');
            }
        });
    }
    function getReception(id){
        
        $.ajax({
            url: '{{ Request::url() }}/get_reception',
            type: 'POST',
            dataType: 'JSON',
            data: {
                id: id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            success: function(response) {
                loadingClose('.modal-content');
                if (response.ada) {
                    disableFields();
                } else {
                    enableFields();
                }
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

    function disableFields() {
        $('#code').prop('disabled', true);
        $('#item').prop('disabled', true);
        $('#item_group_id_edit').parent().css({
            'pointer-events': 'none',
            'opacity': '0.5'
        });
        $('#detail1_edit').prop('disabled', true);
    }

    function enableFields() {
        $('#item').prop('disabled', false);
        $('#detail1_edit').prop('disabled', false);
        
        $('#item_group_id_edit').parent().css({
            'pointer-events': '',
            'opacity': ''
        });
    }

    function getCode(){
        enableFields();
        if($('#temp').val()){
            
        }else{
            
            $.ajax({
                url: '{{ Request::url() }}/get_code',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    
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

    function exportExcel(){
        var search = window.table.search();
        var status = $('#filter_status').val();
        var group = $('#filter_group').val();
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status + "&group=" + group;
    }
</script>