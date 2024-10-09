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

                            <div class="card-content row" style="display: flex; flex-direction: column; align-items: center; justify-content: center;">

                                <div style="text-align: center;">
                                    <video id="video" width="100%" height="auto" style="border: 1px solid gray;"></video>
                                </div>

                                <div id="sourceSelectPanel" style="display:none">
                                    <label for="sourceSelect">Change video source:</label>
                                    <select id="sourceSelect" style="max-width:400px" class="browser-default">
                                    </select>
                                </div>

                                <div style="margin-top: 10px; text-align: center;">
                                    <a class="btn btn-small waves-effect waves-light breadcrumbs-btn" id="startButton">Start</a>
                                    <a class="btn btn-small waves-effect waves-light breadcrumbs-btn" id="resetButton">Reset</a>
                                </div>


                            </div>

                            <div class="card-content row">
                                <h4 class="card-title">Barcode Scanner</h4>
                                <div class="col s12">
                                    <div id="validation_alert_barcode" style="display:none;"></div>
                                </div>
                                <form id="barcode-form" action="{{ Request::url() }}/show_from_barcode" method="POST">
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
                                    <div class="row" style="display:flex;justify-content: flex-end;">
                                        <h4 class="card-title" id="status_document">Status :  </h4>
                                    </div>
                                    <div class="row">
                                        <div class="col s12">
                                            <form class="row" id="form_data" onsubmit="return false;">
                                                <div class="col s12">
                                                    <div id="validation_alert" style="display:none;"></div>
                                                </div>
                                                <div class="col s12">
                                                    <div class="input-field col s12">

                                                        <input type="hidden" id="temp" name="temp">
                                                        <input id="code" name="code" type="text" placeholder="Code">
                                                        <label class="active" for="code">No. Dokumen</label>
                                                    </div>
                                                    <div class="input-field col s12">
                                                        <input id="no_pol" name="no_pol" type="text" placeholder="No Pol">
                                                        <label class="active" for="no_pol">No. Pol</label>
                                                    </div>
                                                    <div class="input-field col s12">
                                                        <input id="driver" name="driver" type="text" placeholder="Nama Supir">
                                                        <label class="active" for="driver">Supir</label>
                                                    </div>
                                                    <div class="input-field col s12">
                                                        <input id="type" name="type" type="text" placeholder="Truk">
                                                        <label class="active" for="type">Truk</label>
                                                    </div>
                                                    <div class="row" id="table_detail">
                                                        <table class="bordered" style="font-size:10px;">
                                                            <thead id="t_head">
                                                                <tr>
                                                                    <th class="center-align">Item Code</th>
                                                                    <th class="center-align">Item Name</th>
                                                                    <th class="center-align">Qty Jual</th>
                                                                    <th class="center-align">Satuan</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="table_body">
                                                            </tbody>
                                                        </table>

                                                    </div>


                                                    <div class="col s12 mt-3">
                                                        <button class="btn waves-effect waves-light right submit" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="row mt-5">
                                        <div class="col s12 m12">
                                            <div class="card-alert card gradient-45deg-purple-amber">
                                                <div class="card-content white-text">
                                                    <p>Info : SJ yang ditampilkan hanya SJ yang telah di scan 2 hari kebelakang.</p>
                                                </div>
                                            </div>
                                            <div class="col s12">
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
                                                            <th>Tanggal Di Scan</th>
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
            </div>
            <div class="content-overlay"></div>
        </div>
    </div>
</div>

{{-- <div id="modal1" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>{{ __('translations.add') }}/{{ __('translations.edit') }} Country</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="input-field col s6">
                            <input type="hidden" id="temp" name="temp">
                            <input id="code" name="code" type="text" placeholder="Kode Negara">
                            <label class="active" for="code">{{ __('translations.code') }}</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="name" name="name" type="text" placeholder="Nama Negara">
                            <label class="active" for="name">{{ __('translations.name') }}</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="phone_code" name="phone_code" type="text" placeholder="Kode Telepon">
                            <label class="active" for="phone_code">{{ __('translations.telephone_code') }}</label>
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
</div> --}}

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

{{-- <div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div> --}}

<!-- END: Page Main-->
<script type="text/javascript" src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>
<script>

    window.addEventListener('load', function () {
        let selectedDeviceId;
        const codeReader = new ZXing.BrowserMultiFormatReader();

        codeReader.listVideoInputDevices()
        .then((videoInputDevices) => {
            const sourceSelect = document.getElementById('sourceSelect');
            selectedDeviceId = videoInputDevices[0].deviceId;
            if (videoInputDevices.length >= 1) {
            videoInputDevices.forEach((element) => {
                $('#sourceSelect').append(`<option value="${element.deviceId}">${element.label}</option>`);
            });

            sourceSelect.onchange = () => {
                selectedDeviceId = sourceSelect.value;
            };

            const sourceSelectPanel = document.getElementById('sourceSelectPanel');
            sourceSelectPanel.style.display = 'block';
            }

            document.getElementById('startButton').addEventListener('click', () => {
            codeReader.decodeFromVideoDevice(selectedDeviceId, 'video', (result, err) => {
                if (result) {
                document.getElementById('barcode-input').value = result.text;
                submitBarcode();
                }
                if (err && !(err instanceof ZXing.NotFoundException)) {

                }
            });

            });

            document.getElementById('resetButton').addEventListener('click', () => {
            codeReader.reset();
            });

        })
        .catch((err) => {

        });
    });

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

                        if(response.mop){
                            var status = response.status_s;
                            $('#temp').val(response.id);
                            $('#status_document').text('Status: ').append(status);
                            $('#code').val(response.mop['code']);
                            $('#no_pol').val(response.mop['vehicle_no']);
                            $('#driver').val(response.mop['driver_name']);
                            $('#type').val(response.mop['vehicle_name']);
                            $('#table_body').empty();
                            $.each(response.detail, function(i, val) {
                                $('#table_body').append(`
                                    <tr>
                                        <td >`+val.item_code+`</td>
                                        <td >`+val.item_name+`</td>
                                        <td >`+val.qty_jual+`</td>
                                        <td >`+val.satuan+`</td>
                                    </tr>
                                `);
                            });

                        }

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
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    'status' : $('#filter_status').val(),
                    start_date : $('#start_date').val(),
                    finish_date : $('#finish_date').val(),
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

    function submitBarcode() {
        var formData = new FormData($('#barcode-form')[0]);
        $.ajax({
            url: "{{ Request::url() }}/show_from_barcode",
            type: 'POST',
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

                    if(response.mop){
                        var status = response.status_s;
                        $('#temp').val(response.id);
                        $('#status_document').text('Status: ').append(status);
                        $('#code').val(response.mop['code']);
                        $('#no_pol').val(response.mop['vehicle_no']);
                        $('#driver').val(response.mop['driver_name']);
                        $('#type').val(response.mop['vehicle_name']);
                        $('#table_body').empty();
                        $.each(response.detail, function(i, val) {
                            $('#table_body').append(`
                                <tr>
                                    <td >`+val.item_code+`</td>
                                    <td >`+val.item_name+`</td>
                                    <td >`+val.qty_jual+`</td>
                                    <td >`+val.satuan+`</td>
                                </tr>
                            `);
                        });

                    }

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
                            loadDataTable();
                            $('#form_data')[0].reset();
                            $('#table_body').empty();
                            $('#status_document').empty().text('Status: ');
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
        });
    }


    function excelFilterTable(){
        var endDate = $('#finish-date').val();
        var startDate = $('#start-date').val();
        var search = window.table.search();
        var multiple = $('#textarea_multiple').val();
        window.location = "{{ Request::url() }}/export_data_table?start_date=" + startDate+ "&finish_date=" + endDate + "&search=" + search + "&multiple=" + multiple;
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
