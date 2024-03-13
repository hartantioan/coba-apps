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
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="loadDataTable();">
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
                                            <table id="datatable_serverside" class="display responsive-table wrap">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Kode</th>
                                                        <th>Item</th>
                                                        <th>Group</th>
                                                       
                                                        <th>IP Address</th>
                                                        <th>Nominal</th>
                                                        <th>Lokasi</th>
                                                        <th>Info</th>
                                                        
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
                        <div class="input-field col s6">
                            <select class="browser-default" id="item_id" name="item_id">&nbsp;</select>
                            <label class="active" for="item_id">Pilih Item dari inventory</label>
                        </div>
                        <div class="input-field col s6">
                            <select class="browser-default" id="item_group_id" name="item_group_id" onchange="getDepartment()">&nbsp;</select>
                            <label class="active" for="item_group_id">Group Departement</label>
                        </div>
                        <div class="input-field col s6">
                            <input type="hidden" id="temp" name="temp">
                            <input id="code" name="code" type="text" placeholder="Kode">
                            <label class="active" for="code">Kode</label>
                        </div>
                        <div class="input-field col s6">
                            <select class="form-control" id="place_id" name="place_id">
                                @foreach ($place as $rowplace)
                                    <option value="{{ $rowplace->code }}">{{ $rowplace->code }}</option>
                                @endforeach
                            </select>
                            <label class="" for="place_id">Lokasi</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="ip_address" name="ip_address" type="text" placeholder="192.168.0.1">
                            <label class="active" for="ip_address">IP Address(bila ada)</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="nominal" name="nominal" type="text" placeholder="Nominal" onkeyup="formatRupiah(this);">
                            <label class="active" for="nominal">Nominal</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="info" name="info" type="text" placeholder="Keterangan">
                            <label class="active" for="info">info</label>
                        </div>
                        
                        <div class="input-field col s6">
                            <div class="switch mb-1">
                                <label for="order">Status</label>
                                <label>
                                    Non-Active
                                    <input checked type="checkbox" id="status" name="status" value="1">
                                    <span class="lever"></span>
                                    Active
                                </label>
                            </div>
                        </div>
                        <div class="col s12 mt-3">
                            <button class="btn waves-effect waves-light right submit" onclick="save();">Simpan <i class="material-icons right">send</i></button>
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
                                <th class="center-align">Code</th>
                                <th class="center-align">Date</th>
                                <th class="center-align">User</th>
                                <th class="center-align">Info</th>
                                <th class="center-align">Action</th>
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
                                <th class="center-align">Code</th>
                                <th class="center-align">Date</th>
                                <th class="center-align">User</th>
                                <th class="center-align">Info</th>
                                <th class="center-align">Action</th>
                            </tr>                  
                    </thead>
                    <tbody id="body-history-goods1">
                    </tbody>
                </table>
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
    document.addEventListener('focusin', function (event) {
        const select2Container = event.target.closest('.modal-content .select2');
        const activeSelect2 = document.querySelector('.modal-content .select2.tab-active');
        if (event.target.closest('.modal-content')) {
            document.body.classList.add('tab-active');
        }
        
        // Remove highlighting from previous Select2 input
        if (activeSelect2 && !select2Container) {
            activeSelect2.classList.remove('tab-active');
        }

        // Add highlighting to the new Select2 input
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
        select2ServerSide('#item_id', '{{ url("admin/select2/item_for_hardware_item") }}');
        select2ServerSide('#item_group_id', '{{ url("admin/select2/hardware_item_group") }}');
    });

    function loadDataTable() {
		window.table = $('#datatable_serverside').DataTable({
            "scrollCollapse": true,
            "scrollY": '400px',
            "responsive": true,
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
                { name: 'ip_address', className: 'center-align' },
                { name: 'location', className: 'center-align' },
                { name: 'nominal', className: 'center-align' },
                { name: 'info', className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle' 
            ]
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
                $('#item_id').val(response.name);
                $('#item_id').append(`
                    <option value="` + response.item.id + `">`+response.item.name+`</option>
                `);
                $('#item_group_id').append(`
                    <option value="` + response.group_item.id + `">` + response.group_item.code+`-`+response.group_item.name+`</option>
                `);
                $('#info').val(response.info);
                $('#place_id').val(response.place_id).formSelect();
                $('#ip_address').val(response.ip_address);
                $('#nominal').val(response.nominal);
                if(response.status == '1'){
                    $('#status').prop( "checked", true);
                }else{
                    $('#status').prop( "checked", false);
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
</script>