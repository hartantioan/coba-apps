<!-- BEGIN: Page Main-->
<style>
    #modal4 {
        top:0px !important;
    }
    
</style>
<div id="main">
    <div class="row">
        <div class="col s12">
            <div class="container">
                <div class="row">
                    <div class="col s12">
                        <div class="card">
                            <div class="card-content row">
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
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="print();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">Print</span>
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
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">Filter</h4>
                                    <div class="row">
                                        <div class="col m4 s6 ">
                                            <label for="start-date" style="font-size:1rem;">Tanggal Mulai :</label>
                                            <div class="input-field col s12">
                                            <input type="date" max="{{ date('9999'.'-12-31') }}" id="start-date" name="start-date"  onchange="loadDataTable()">
                                            </div>
                                        </div>
                                        <div class="col m4 s6 ">
                                            <label for="finish-date" style="font-size:1rem;">Tanggal Akhir :</label>
                                            <div class="input-field col s12">
                                                <input type="date" max="{{ date('9999'.'-12-31') }}" id="finish-date" name="finish-date"  onchange="loadDataTable()">
                                            </div>
                                        </div>
                                        <div class="col m4 s6 ">
                                            <div class="input-field col s12">
                                                <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="excelMultiData();">
                                                    <i class="material-icons hide-on-med-and-up">view_list</i>
                                                    <span class="hide-on-small-onl">EXCEL</span>
                                                    <i class="material-icons right">view_list</i>
                                                </a>
                                            </div>
                                            <div class="input-field col s12">
                                                <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="excelFilterTable();">
                                                    <i class="material-icons hide-on-med-and-up">subtitles</i>
                                                    <span class="hide-on-small-onl">EXCEL DATA TABLE</span>
                                                    <i class="material-icons right">subtitles</i>
                                                </a>
                                            </div>
                                           
                                        </div>
                                        <div class="col m10 s6 ">
                                            <label for="textarea_multiple">Multiple Find (,)</label>
                                            <div class="input-field col s12">
                                                <textarea type="text" id="textarea_multiple" name="textarea_multiple" class="materialize-textarea" onchange="loadDataTable()"></textarea>
                                            </div>
                                        </div>
                                        {{-- <div class="col m12 s6 ">
                                            <a class="waves-effect waves-light btn-small"><i class="material-icons left">cloud</i>button</a>
                                        </div> --}}
                                    </div>
                                </div>
                            </div>
                            {{-- <ul class="collapsible collapsible-accordion">
                                <li>
                                    <div class="collapsible-header"><i class="material-icons">filter_list</i> FILTER</div>
                                    <div class="collapsible-body">
                                          
                                    </div>
                                </li>
                            </ul> --}}
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
                                                        <th align="center" rowspan="2"  style="background-color: navy; color: white;border: 1px solid white;">No</th>
                                                        <th align="center" colspan="2" style="background-color: navy; color: white;border: 1px solid white;">Faktur Pajak</th>
                                                        <th align="center" colspan="3" style="background-color: navy; color: white;border: 1px solid white;">Supplier</th>
                                                        <th align="center" rowspan="2" style="background-color: navy; color: white;border: 1px solid white;">DPP</th>
                                                        <th align="center" rowspan="2" style="background-color: navy; color: white;border: 1px solid white;">PPN</th>
                                                        <th align="center" rowspan="2" style="background-color: navy; color: white;border: 1px solid white;">Nama Barang</th>
                                                        <th align="center" rowspan="2" style="background-color: navy; color: white;border: 1px solid white;">Action</th>
                                                    </tr>
                                                    <tr>
                                                        <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Tanggal</th>
                                                        <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Nomor</th>
                                                        <th align="center" style="background-color: navy; color: white;border: 1px solid white;">NPWP</th>
                                                        <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Nama </th>
                                                        <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Alamat Lengkap</th>
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

{{-- <div id="modal1" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Tambah/Edit Country</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="input-field col s6">
                            <input type="hidden" id="temp" name="temp">
                            <input id="code" name="code" type="text" placeholder="Kode Negara">
                            <label class="active" for="code">Kode</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="name" name="name" type="text" placeholder="Nama Negara">
                            <label class="active" for="name">Nama</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="phone_code" name="phone_code" type="text" placeholder="Kode Telepon">
                            <label class="active" for="phone_code">Kode Telepon</label>
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
</div> --}}

<div id="modal4" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
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

{{-- <div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div> --}}

<!-- END: Page Main-->
<script>
    $(function() {

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
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('input').css('border', 'none');
                $('input').css('border-bottom', '0.5px solid black');
                $('#temp').val('');
                M.updateTextFields();
            }
        });
    });

    $('#barcode-form').submit(function(event) {
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
                    $('#validation_alert_barcode').hide();
                    $('#validation_alert_barcode').html('');
                },
                success: function(response) {
                    if(response.status == 200) {
                        success();
                        M.toast({
                            html: response.message
                        });
                        $('#barcode-form')[0].reset();
                    }else if(response.status == 422) {
                        $('#barcode-form')[0].reset();
                        $('#validation_alert_barcode').show();
                        $('.modal-content').scrollTop(0);
                        $('#validation_alert_barcode').append(`
                            <div class="card-alert card red">
                                <div class="card-content white-text">
                                    <p>` +response.error+` </p>
                                </div>
                                <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                        `);
                    } else {
                        $('#barcode-form')[0].reset();
                        M.toast({
                            html: response.message
                        });
                    }
                },
                error: function(response) {
                    $('#barcode-form')[0].reset();
                }
            });

    });

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
                    multiple : $('#textarea_multiple').val(),
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
                        { name: 'transaction_code', className: 'center-align' },
                        { name: 'date', className: 'center-align' },
                        { name: 'date', className: 'center-align' },
                        { name: 'npwp_number', className: 'center-align' },
                        { name: 'npwp_name', className: 'center-align' },
                        { name: 'npwp_address', className: 'center-align' },
                        { name: 'total', className: 'center-align' },
                        { name: 'tax', className: 'center-align' },
                        { name: 'action', searchable: false, orderable: false, className: 'center-align' }
                    ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle',
                'selectNone' 
            ],
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
    }

    function excelMultiData(){
        var arr_id_temp=[];
        $.map(window.table.rows('.selected').nodes(), function (item) {
            var poin = $(item).find('td:nth-child(3)').text().trim();
            arr_id_temp.push(poin);
        });

        window.location = "{{ Request::url() }}/export?no_faktur=" + arr_id_temp; 
    }

    function excelFilterTable(){
        var endDate = $('#finish-date').val();
        var startDate = $('#start-date').val();
        var search = window.table.search();
        var multiple = $('#textarea_multiple').val();
        window.location = "{{ Request::url() }}/export_data_table?start_date=" + startDate+ "&finish_date=" + endDate + "&search=" + search + "&multiple=" + multiple; 
    }



    function success(){
        loadDataTable();
        $('#modal1').modal('close');
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
                $('#phone_code').val(response.phone_code);

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

    function print(){
        var search = window.table.search();
        
        $.ajax({
            type : "POST",
            url  : '{{ Request::url() }}/print',
            data : {
                search : search
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            cache: false,
            success: function(data){
                var w = window.open('about:blank');
                w.document.open();
                w.document.write(data);
                w.document.close();
            }
        });
    }
</script>