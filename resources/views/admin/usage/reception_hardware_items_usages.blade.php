<!-- BEGIN: Page Main-->
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
                            <span class="hide-on-small-onl">{{ __('translations.refresh') }}</span>
                            <i class="material-icons right">refresh</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="excel();">
                            <i class="material-icons hide-on-med-and-up">subtitles</i>
                            <span class="hide-on-small-onl">EXCEL DATA TABLE</span>
                            <i class="material-icons right">subtitles</i>
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
                                        {{-- <label for="filter_status" style="font-size:1.2rem;">{{ __('translations.filter_status') }} :</label>
                                        <div class="input-field inline" style="margin-top: 0;margin-bottom: 0;">
                                            <select class="form-control" id="filter_status" onchange="loadDataTable()">
                                                <option value="">{{ __('translations.all') }}</option>
                                                <option value="1">{{ __('translations.active') }}</option>
                                                <option value="2">{{ __('translations.non_active') }}</option>
                                            </select>
                                        </div> --}}
                                    </div>
                                    <div class="col s12 ">
                                        <div class="row">
                                            <div class="col s4 m4"></div>
                                            <div class="col s4 m4"><h6 style="text-align: center;"> Idle Item</h6></div>
                                            <div class="col s4 m4 right-align">
                                                <a class="btn-flat mb-1 waves-effect"  href="#modalbarcode">
                                                    <i class="material-icons left">scanner</i> 
                                                    Barcode Scanner
                                                </a>
                                            </div>
                                            <div class="col s12 m12">
                                                <div class="row" id="in_storage">
                                                </div>
                                            </div>
                                        </div>
                                        
                                            
                                        
                                        
                                    </div>
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
                                                        <th>User</th>
                                                        <th>Kode Inventaris</th>
                                                        <th>{{ __('translations.item') }}</th>
                                                        <th>Detail</th>
                                                        <th>{{ __('translations.location') }}</th>
                                                        <th>Tanggal Penyerahan</th>
                                                        <th>Keterangan Penyerahan</th>
                                                        <th>Penerima</th>
                                                        <th>Tanggal Pengembalian</th>
                                                        <th>Keterangan Pengembalian</th>
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
                <h4>{{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="input-field col s12 m6 step1" id="tab_hardware">
                            <select class="browser-default" id="hardware_item_id" name="hardware_item_id"  onchange="getDetail();">&nbsp;</select>
                            <label class="active" for="hardware_item_id">Pilih Item dari inventory</label>
                        </div>
                        <div class="input-field col s12 m6 step1" id="tab_hardware_edit" style="display: none">
                            <input id="item_edit" name="item_edit" disabled></input>
                            <label class="active" for="item_edit">Item dari inventory</label>
                        </div>
                        <div class="input-field col s12 m6 ">
                            <input type="hidden" id="tempe" name="tempe"> 
                            <input id="detail1" name="detail1" disabled></input>
                            <label class="active" for="detail1">Detail 1</label>
                        </div>
                        
                        <div class="input-field col s12 m6 step2" id="tab_user">
                            <select class="browser-default" id="user_id" name="user_id" onchange="getDetail()">&nbsp;</select>
                            <label class="active" for="user_id">Pilih User(jika ada)</label>
                        </div>
                        <div class="input-field col s12 m6 step2" id="tab_user_edit" style="display: none">
                            <input id="user_edit" name="user_edit" disabled></input>
                            <label class="active" for="user_edit">User</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <input id="division" name="division"></input>
                            <label class="active" for="division">DIVISI</label>
                        </div>
                        <div class="input-field col s12 m6 step3">
                            <input id="location" name="location" type="text" placeholder="Keterangan">
                            <label class="active" for="location">{{ __('translations.location') }}</label>
                        </div>
                        <div class="input-field col s12 m6 step4"> 
                            <input type="date" id="date" name="date" min="{{ $minDate }}">
                            <label class="active" for="date">Date(tanggal)</label>
                        </div>
                        <div class="input-field col s12 step5">
                            <input id="info" name="info" type="text" placeholder="Info">
                            <label class="active" for="info">{{ __('translations.note') }}</label>
                        </div>
                        <div class="input-field col s12 m6 step6">
                            <div class="switch mb-1">
                                <label for="order">{{ __('translations.status') }}</label>
                                <label>
                                    {{ __('translations.non_active') }}
                                    <input checked type="checkbox" id="status" name="status" value="1">
                                    <span class="lever"></span>
                                   {{ __('translations.active') }}
                                </label>
                            </div>
                        </div>
                        <div class="col s12 mt-3 step7">
                            <button class="btn waves-effect waves-light right submit" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
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

<div id="modal2" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Pengembalian <span id="code_recept_id"><span></h4>
                <form class="row" id="form_data1" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert1" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="input-field col s12 m6">
                            <input type="hidden" id="temp" name="temp"> 
                            <input type="date" id="date_kembali" name="date"  onchange="loadDataTable()">
                            <label class="active" for="date">Date(tanggal)</label>
                        </div>
                        <div class="input-field col s12">
                            <input id="info_kembali" name="info" type="text" placeholder="Info">
                            <label class="active" for="info">{{ __('translations.note') }}</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <div class="switch mb-1">
                                <label for="order">{{ __('translations.status') }}</label>
                                <label>
                                    {{ __('translations.non_active') }}
                                    <input checked type="checkbox" id="status" name="status" value="1">
                                    <span class="lever"></span>
                                   {{ __('translations.active') }}
                                </label>
                            </div>
                        </div>
                        <div class="col s12 mt-3">
                            <button class="btn waves-effect waves-light right submit" onclick="rReturn();">SIMPAN <i class="material-icons right">send</i></button>
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

<div id="modal3" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;width:60%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Penyerahan <span id="name_item"><span></h4>
                <form class="row" id="form_data2" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert2" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="input-field col s12 m6">
                            <input type="hidden" id="tempes" name="tempes"> 
                            <input id="items" name="items" disabled></input>
                            <label class="active" for="items">{{ __('translations.item') }}</label>
                        </div>
                        <div class="input-field col s12 m6 ">
                            <input id="detail1s" name="detail1s" disabled></input>
                            <label class="active" for="detail1s">Detail 1</label>
                        </div>
                        
                        <div class="input-field col s12 m6">
                            <select class="browser-default" id="user_id1" name="user_id1" onchange="getDetail()">&nbsp;</select>
                            <label class="active" for="user_id1" >Pilih User(jika ada)</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <input id="division1" name="division1" ></input>
                            <label class="active" for="division1">DIVISI</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <input id="location1" name="location1" type="text" placeholder="Keterangan">
                            <label class="active" for="location1">{{ __('translations.location') }}</label>
                        </div>
                        <div class="input-field col s12 m6">
                          
                            <input type="date" id="date1" name="date1"  onchange="loadDataTable()">
                            <label class="active" for="date1">Date(tanggal)</label>
                        </div>
                        <div class="input-field col s12">
                            <input id="info1" name="info1" type="text" placeholder="Info">
                            <label class="active" for="info">Info</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <div class="switch mb-1">
                                <label for="order">{{ __('translations.status') }}</label>
                                <label>
                                    {{ __('translations.non_active') }}
                                    <input checked type="checkbox" id="status1" name="status1" value="1">
                                    <span class="lever"></span>
                                   {{ __('translations.active') }}
                                </label>
                            </div>
                        </div>
                        <div class="col s12 mt-3">
                            <button class="btn waves-effect waves-light right submit" onclick="saveTargeted();">SIMPAN <i class="material-icons right">send</i></button>
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
<div id="modalbarcode" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4 class="card-title">Barcode Scanner</h4>
                <div class="col s12">
                    <div id="validation_alert_barcode" style="display:none;"></div>
                </div>
                <form id="barcode-form" action="{{ Request::url() }}/store_w_barcode" method="POST">
                    @csrf
                    <input type="text" name="barcode" id="barcode-input" autofocus>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>
<div id="modal_print" class="modal modal-fixed-footer" style="">
    <div class="modal-content">
        <div class="row" id="body_print">
            
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
<style>
    .#card_idle {
        transition: box-shadow 0.3s;
    }

    .card_idle:hover {
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
    }
</style>
<script>
    $(function() {
        loadDataTable();
        fetchStorage();
        select2ServerSide('#user_id', '{{ url("admin/select2/employee") }}');
        select2ServerSide('#user_id1', '{{ url("admin/select2/employee") }}');
        select2ServerSide('#hardware_item_id', '{{ url("admin/select2/hardware_item_for_reception") }}');
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
                $('#hardware_item_id').empty();
                $('#user_id').empty();
                $('#tab_user').show();
                $('#tab_hardware').show();
                $('#tab_user_edit').hide();
                $('#tab_hardware_edit').hide();
                $('#temp').val('');
                M.updateTextFields();
            }
        });
        $('#modalbarcode').modal({
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
               
                M.updateTextFields();
            }
        });

        $('#modal_print').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                    
            },
            onOpenEnd: function(modal, trigger) { 

                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                $('#body_print').empty();
                M.updateTextFields();
            }
        });

        $('#modal2').modal({
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
                $('#form_data1')[0].reset();
                $('#info_kembali').empty();
                $('#date_kembali').empty();
                $('#temp').val('');
                M.updateTextFields();
            }
        });

        $('#modal3').modal({
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
                $('#form_data2')[0].reset();
                $('#user_id1').empty();
                $('#date1').empty();
                $('#info1').empty();
                $('#tempes').val('');
                M.updateTextFields();
            }
        });

    });

    function getDetail(){
        
        if($('#hardware_item_id').val()){
            let params = $('#hardware_item_id').select2('data')[0].detail1;
            $('#detail1').val(params);
        }else{
            $('#detail1').val('');
        }
        if($('#user_id').val()){
            let paramsdivisi = $('#user_id').select2('data')[0].division;
            $('#division').val(paramsdivisi);
        
        }
        if($('#user_id1').val()){
            let paramsdivisi = $('#user_id1').select2('data')[0].division;
            $('#division1').val(paramsdivisi);
        
        }
        
        
    }

    function excel(){
        var search = window.table.search();
        
        window.location = "{{ Request::url() }}/export?search=" + search; 
    }

    function fetchStorage(){
        $.ajax({
            url: '{{ Request::url() }}/fetch_storage',
            type: 'GET',
            dataType: 'JSON',
            contentType: false,
            processData: false,
            cache: true,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#in_storage').empty();
                
                
                $.each(response.itemInStorage, function(i, val) {
                    $('#in_storage').append(
                        `
                        <div class="col s12 m12 l12">
                            <div class="border-radius-6  card_idle grey lighten-1" style="max-height:0.5em;overflow:hidden;padding:1rem;padding-bottom:2rem;border: 2px solid black;" onclick="targeted_item(`+val.item_id+`,'`+btoa(val.itemName)+`')">
                                <div>
                                    <div style="text-align:center;font-size:1em;"> `+val.itemName+`-`+val.itemCode+` : `+val.itemdetail+`</div>
                                    
                                </div>
                            </div>
                        </div>
                        `
                    );
                });
                
            },
        });
    }
    function openmodal(id){
        var itemId = $(this).data('item-id');
        
        $.ajax({
            url: '{{ Request::url() }}/modal_print',
            type: 'POST',
            dataType: 'JSON',
            data: {
                id: id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#modal_print').modal('open');
                $('#body_print').empty();
                $('#body_print').append(
                    response
                );
                
            },
        });
    }
    function loadDataTable() {
		window.table = $('#datatable_serverside').DataTable({
            "scrollCollapse": true,
            "scrollY": '400px',
            "scrollX": true,
            "responsive": false,
            "stateSave": true,
            "serverSide": true,
            "deferRender": true,
            "destroy": true,
            "fixedColumns": {
                left: 2,
                right: 1
            },
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
                { name: 'user_id', className: 'center-align' },
                { name: 'hardware_item_code', className: 'center-align' },
                { name: 'info', className: 'center-align' },
                { name: 'hardware_item_id', className: 'center-align' },
                { name: 'lokasi', className: 'center-align' },
                { name: 'info', className: 'center-align' },
                { name: 'date', className: 'center-align' },
                { name: 'date', className: 'center-align' },
                { name: 'date', className: 'center-align' },
                { name: 'date', className: 'center-align' },
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
                    fetchStorage();
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

    function saveTargeted(){
			
            var formData = new FormData($('#form_data2')[0]);
            
            $.ajax({
                url: '{{ Request::url() }}/save_targeted',
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
                    $('#validation_alert2').hide();
                    $('#validation_alert2').html('');
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    loadingClose('.modal-content');
                    if(response.status == 200) {
                        $('#modal3').modal('close');
                        loadDataTable();
                        M.toast({
                            html: response.message
                        });
                        fetchStorage();
                    } else if(response.status == 422) {
                        $('#validation_alert2').show();
                        $('.modal-content').scrollTop(0);
                        
                        swal({
                            title: 'Ups! Validation',
                            text: 'Check your form.',
                            icon: 'warning'
                        });
    
                        $.each(response.error, function(i, val) {
                            $.each(val, function(i, val) {
                                $('#validation_alert2').append(`
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

    function targeted_item(id,name){
        $.ajax({
            url: '{{ Request::url() }}/show_item',
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
                $('#items').val(response['name']+'-'+response['code']);
                $('#detail1s').val(response['detail1']);
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
        $('#name_item').empty();
        $('#modal3').modal('open');
        $('#tempes').val(id);
        $('#name_item').append(atob(name));
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
                $('#tempe').val(id);
                $('#detail1').val(response.detail1);
                $('#info').val(response.info);
                $('#date').val(response.date);
                $('#location').val(response.location);
                $('#tab_user').hide();
               
                $('#tab_hardware').hide();
                $('#tab_user_edit').show();
                $('#tab_hardware_edit').show();
                $('#item_edit').val(response.name);
                $('#user_edit').val(response.user.name);
                $('#division').val(response.division);
                
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

    function returnItem(id){
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
                $('#code_recept_id').empty();
                $('#modal2').modal('open');
                $('#temp').val(id);
                $('#code_recept_id').append(response.code);
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

    function rReturn(){
        var formData = new FormData($('#form_data1')[0]);
        
        $.ajax({
            url: '{{ Request::url() }}/diversion',
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
                $('#validation_alert1').hide();
                $('#validation_alert1').html('');
                loadingOpen('.modal-content');
            },
            success: function(response) {
                loadingClose('.modal-content');
                if(response.status == 200) {
                    loadDataTable();
                    $('#modal2').modal('close');
                    M.toast({
                        html: response.message
                    });
                    fetchStorage();
                } else if(response.status == 422) {
                    $('#validation_alert1').show();
                    $('.modal-content').scrollTop(0);
                    
                    swal({
                        title: 'Ups! Validation',
                        text: 'Check your form.',
                        icon: 'warning'
                    });

                    $.each(response.error, function(i, val) {
                        $.each(val, function(i, val) {
                            $('#validation_alert1').append(`
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

    function printData(id){
        
         $.ajax({
            type : "POST",
            url  : '{{ Request::url() }}/print',
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

    function printDataReturn(id){
       
         $.ajax({
            type : "POST",
            url  : '{{ Request::url() }}/print_return',
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

    function startIntro(){
        introJs().setOptions({
            exitOnOverlayClick : false,
            steps: [
                {
                    title : 'Form Penyerahan Barang',
                    intro : 'Form ini digunakan untuk mencatat penyerahan barang kepada user siapa yang nantinya akan menggunakan inventaris di form ini.'
                },
                {
                    title : 'Pilih User',
                    element : document.querySelector('.step1'),
                    intro : 'Memilih user yang berkaitan dengan penyerahan item'
                },
                {
                    title : 'Kode Plant',
                    element : document.querySelector('.step2'),
                    intro : 'Pilih kode plant untuk nomor dokumen bisa secara otomatis ter-generate.'
                },
                {
                    title : 'Lokasi',
                    element : document.querySelector('.step3'),
                    intro : 'Keterangan Lokasi barang nantinya akan berada dimana.' 
                },
                {
                    title : 'Date(tanggal)',
                    element : document.querySelector('.step4'),
                    intro : 'Merupakan tanggal dimana penyerahan pada form ini akan dilakukan.' 
                },
              
                {
                    title : 'Keterngan',
                    element : document.querySelector('.step5'),
                    intro : 'Keterangan tambahan yang akan dicantumkan pada form ini.' 
                },
                {
                    title : 'Status',
                    element : document.querySelector('.step6'),
                    intro : 'Merupakan status aktif atau tidaknya penyerahan ini.' 
                },
                {
                    title : 'Tombol Simpan',
                    element : document.querySelector('.step7'),
                    intro : 'Silahkan tekan tombol ini untuk menyimpan data, namun pastikan data yang akan anda masukkan benar' 
                },
                
            ]
        })/* .onbeforechange(function(targetElement){
            alert(this._currentStep);
        }) */.start();
    }
    

    function destroy(id){
        var msg = '';
        swal({
            title: "Alasan mengapa anda menghapus!",
            text: "Anda tidak bisa mengembalikan data yang telah dihapus.",
            buttons: true,
            content: "input",
        }).then(message => {
            if (message != "" && message != null) {
                $.ajax({
                    url: '{{ Request::url() }}/destroy',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { id : id, msg : message },
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