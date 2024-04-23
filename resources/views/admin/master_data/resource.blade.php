<!-- BEGIN: Page Main-->
<style>
    .modal {
        top:0px !important;
    }

    .select2-container--default .select2-selection--multiple, .select2-container--default.select2-container--focus .select2-selection--multiple {
        height: auto !important;
    }

    .select-wrapper, .select2-container {
        height:3.6rem !important;
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
                    <div class="col s4 m6 l6">
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right modal-trigger" href="#modal2">
                            <i class="material-icons hide-on-med-and-up">file_download</i>
                            <span class="hide-on-small-onl">Import</span>
                            <i class="material-icons right">file_download</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="print();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">Print</span>
                            <i class="material-icons right">local_printshop</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="exportExcel();">
                            <i class="material-icons hide-on-med-and-up">view_list</i>
                            <span class="hide-on-small-onl">Excel</span>
                            <i class="material-icons right">view_list</i>
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
                            <ul class="collapsible collapsible-accordion">
                                <li>
                                    <div class="collapsible-header"><i class="material-icons">filter_list</i> FILTER</div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col m3 s3 ">
                                                <label for="filter_status" style="font-size:1rem;">Status :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_status" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        <option value="1">Aktif</option>
                                                        <option value="2">Non-Aktif</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>  
                                    </div>
                                </li>
                            </ul>
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
                                                        <th>#</th>
                                                        <th>Code</th>
                                                        <th>Nama</th>
                                                        <th>Nama Lain</th>
                                                        <th>Grup</th>
                                                        <th>Qty</th>
                                                        <th>Satuan</th>
                                                        <th>Biaya Rp / 1 Qty</th>
                                                        <th>Plant</th>
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


<div id="modal1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Tambah/Edit {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="input-field col m3 s6">
                            <input type="hidden" id="temp" name="temp">
                            <input id="code" name="code" type="text" placeholder="Kode">
                            <label class="active" for="code">Kode</label>
                        </div>
                        <div class="input-field col m3 s6">
                            <select class="form-control" id="place_id" name="place_id">
                                <option value="">--Kosong--</option>
                                @foreach ($place as $rowplace)
                                    <option value="{{ $rowplace->id }}" {{ $rowplace->id == session('bo_place_id') ? 'selected' : '' }}>{{ $rowplace->code }}</option>
                                @endforeach
                            </select>
                            <label class="" for="place_id">Plant</label>
                        </div>
                        <div class="input-field col m3 s6">
                            <input id="name" name="name" type="text" placeholder="Nama">
                            <label class="active" for="name">Nama</label>
                        </div>
                        <div class="input-field col m3 s6">
                            <input id="other_name" name="other_name" type="text" placeholder="Nama Lain">
                            <label class="active" for="other_name">Nama Lain</label>
                        </div>
                        <div class="input-field col m3 s6">
                            <select class="browser-default" id="uom_unit" name="uom_unit" onchange="getUnitStock();">
                                <option value="">--Silahkan pilih--</option>
                                @foreach ($unit as $row)
                                    <option value="{{ $row->id }}" data-code="{{ $row->code }}">{{ $row->code.' - '.$row->name }}</option>
                                @endforeach
                            </select>
                            <label class="active" for="uom_unit">Satuan</label>
                        </div>
                        <div class="input-field col m3 s6">
                            <input name="qty" id="qty" type="text" value="0,000" onkeyup="formatRupiahNoMinus(this);">
                            <div class="form-control-feedback stock-unit">-</div>
                            <label class="active" for="qty">Qty</label>
                        </div>
                        <div class="input-field col m3 s6">
                            <input name="cost" id="cost" type="text" value="0,00" onkeyup="formatRupiahTwoDecimal(this);">
                            <label class="active" for="cost">Biaya Rp / 1 Qty</label>
                        </div>
                        <div class="input-field col m3 s6">
                            <select class="select2 browser-default" id="resource_group_id" name="resource_group_id">
                                @foreach($group->whereNull('parent_id') as $c)
                                        @if(!$c->childSub()->exists())
                                            <option value="{{ $c->id }}"> - {{ $c->name }}</option>
                                        @else
                                            <optgroup label=" - {{ $c->code.' - '.$c->name }}">
                                            @foreach($c->childSub as $bc)
                                                @if(!$bc->childSub()->exists())
                                                    <option value="{{ $bc->id }}"> -  - {{ $bc->name }}</option>
                                                @else
                                                    <optgroup label=" -  - {{ $bc->code.' - '.$bc->name }}">
                                                        @foreach($bc->childSub as $bcc)
                                                            @if(!$bcc->childSub()->exists())
                                                                <option value="{{ $bcc->id }}"> -  -  - {{ $bcc->name }}</option>
                                                            @else
                                                                <optgroup label=" -  -  - {{ $bcc->code.' - '.$bcc->name }}">
                                                                    @foreach($bcc->childSub as $bccc)
                                                                        @if(!$bccc->childSub()->exists())
                                                                            <option value="{{ $bccc->id }}"> -  -  -  - {{ $bccc->name }}</option>
                                                                        @else
                                                                            <optgroup label=" -  -  -  - {{ $bccc->code.' - '.$bccc->name }}">
                                                                                @foreach($bccc->childSub as $bcccc)
                                                                                    @if(!$bcccc->childSub()->exists())
                                                                                        <option value="{{ $bcccc->id }}"> -  -  -  -  - {{ $bcccc->name }}</option>
                                                                                    @endif
                                                                                @endforeach
                                                                            </optgroup>
                                                                        @endif
                                                                    @endforeach
                                                                </optgroup>
                                                            @endif
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                            @endforeach
                                            </optgroup>
                                        @endif
                                @endforeach
                            </select>
                            <label class="active" for="resource_group_id">Grup Resource</label>
                        </div>
                        <div class="input-field col m3 s12">
                            <div class="switch mb-1">
                                <label for="status">Status</label>
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
<div id="modal2" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;max-width:90%;min-width:70%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Import Excel</h4>
                <div class="col s12">
                    <div id="validation_alertImport" style="display:none;"></div>
                </div>
                <form class="row" action="{{ Request::url() }}/import" method="POST" enctype="multipart/form-data" id="form_dataimport">
                    @csrf
                    <div class="file-field input-field col m6 s12">
                        <div class="btn">
                            <span>Dokumen</span>
                            <input type="file" class="form-control-file" id="fileExcel" name="file">
                        </div>
                        <div class="file-path-wrapper">
                            <input class="file-path validate" type="text">
                        </div>
                    </div>
                    <div class="input-field col m6 s12">
                        <h6>Anda bisa menggunakan fitur copy paste dari format excel yang telah disediakan. Silahkan klik <a href="{{-- {{ asset(Storage::url('format_imports/format_copas_ap_invoice_2.xlsx')) }} --}}{{ Request::url() }}/get_import_excel" target="_blank">disini</a> untuk mengunduh. Jangan menyalin kolom paling atas (bagian header), dan tempel pada isian paling kiri di tabel di bawah ini.</h6>
                    </div>
                    <div class="input-field col m12 s12">
                        <button type="submit" class="btn cyan btn-primary btn-block right">Kirim</button>
                    </div>
                </form>
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
                    if(response.status == 200) {
                        successImport();
                        M.toast({
                            html: response.message
                        });
                    } else if(response.status == 422) {
                        $('#validation_alertImport').show();
                        $('.modal-content').scrollTop(0);

                        $.each(response.error, function(i, val) {
                            
                            $('#validation_alertImport').append(`
                                    <div class="card-alert card red">
                                        <div class="card-content white-text">
                                            <p> Line <b>` + val.row + `</b> in column <b>` + val.attribute + `</b> </p>
                                            <p> `+val.errors[0]+`</p>
                                        </div>
                                        <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                    </div>
                                `);
                        });
                    }else if(response.status == 432) {
                        $('#validation_alertImport').show();
                        $('.modal-content').scrollTop(0);

                        $.each(response.error, function(i, val) {
                            $('#validation_alertImport').append(`
                                    <div class="card-alert card red">
                                        <div class="card-content white-text">
                                            <p>` +val+`</p>
                                        </div>
                                        <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                    </div>
                                `);
                        });
                    } else {
                        M.toast({
                            html: response.message
                        });
                    }
                    loadingClose('.modal-content');
                },
                error: function(response) {
                    var errors = response.responseJSON.errors;
                    var errorMessage = '';
                    if(response.status == 422) {
                        $('#validation_alertImport').show();
                        $('.modal-content').scrollTop(0);
                        
                        swal({
                            title: 'Ups! Validation',
                            text: 'Check your form.',
                            icon: 'warning'
                        });

                        $.each(errors, function(index, error) {
                        var message = '';

                        $.each(error.errors, function(index, value) {
                            message += value + '\n';
                        });

                        errorMessage += errors.file;
                    });

                    $('#validation_alertImport').html(`
                        <div class="card-alert card red">
                            <div class="card-content white-text">
                                <p>` + errorMessage + `</p>
                            </div>
                            <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                    `).show();

                    }
                    
                   
                }
            });

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
                $('#temp').val('');
                $("#resource_group_id").val($("#resource_group_id option:first").val()).trigger('change');
                $("#uom_unit").val($("#uom_unit option:first").val()).trigger('change');
                M.updateTextFields();
            }
        });
        $('#modal2').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
                $('#validation_alertImport').hide();
                $('#validation_alertImport').html('');
            },
            onCloseEnd: function(modal, trigger){
                $('#form_dataimport')[0].reset();
            }
        });

        $('#place_id').val("{{ session('bo_place_id') }}").formSelect();
        
        $("#uom_unit,#resource_group_id").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        $('.stock-unit').text('-');
    });

    function getUnitStock(){
        if($('#uom_unit').val()){
            $('.stock-unit').text($("#uom_unit").select2().find(":selected").data("code"));
        }else{
            $('.stock-unit').text('-');
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
                    balance : $('#filter_balance').val(),
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
                { name: 'name', className: '' },
                { name: 'other_name', className: '' },
                { name: 'resource_group_id', className: '' },
                { name: 'qty', className: 'center-align' },
                { name: 'unit', className: 'center-align' },
                { name: 'cost', className: 'right-align' },
                { name: 'place_id', className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
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

    function successImport(){
        loadDataTable();
        $('#modal2').modal('close');
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
                $('#other_name').val(response.other_name);
                $('#place_id').val(response.place_id).formSelect();
                $('#resource_group_id').val(response.resource_group_id).trigger('change');
                $('#uom_unit').val(response.uom_unit).trigger('change');
                $('#qty').val(response.qty);
                $('#cost').val(response.cost);

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
    function saveImport(){
			
            var formData = new FormData($('#form_dataImport')[0]);
            
            $.ajax({
                url: '{{ Request::url() }}/import',
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
                    $('#validation_alertImport').hide();
                    $('#validation_alertImport').html('');
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
                        $('#validation_alertImport').show();
                        $('.modal-content').scrollTop(0);
                        
                        swal({
                            title: 'Ups! Validation',
                            text: 'Check your form.',
                            icon: 'warning'
                        });
    
                        $.each(response.error, function(i, val) {
                            $.each(val, function(i, val) {
                                $('#validation_alertImport').append(`
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
        var search = window.table.search(), status = $('#filter_status').val();
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