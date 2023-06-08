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
                        </ol>
                    </div>
                    <div class="col s4 m6 l6">
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right modal-trigger" href="#modal2">
                            <i class="material-icons hide-on-med-and-up">file_upload</i>
                            <span class="hide-on-small-onl">Import</span>
                            <i class="material-icons right">file_upload</i>
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
                                            <div class="col m3 s3 ">
                                                <label for="filter_balance" style="font-size:1rem;">Saldo Buku :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_balance" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        <option value="1">Ada</option>
                                                        <option value="2">Tidak</option>
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
                                            <table id="datatable_serverside" class="display responsive-table wrap">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Code</th>
                                                        <th>Nama</th>
                                                        <th>Grup</th>
                                                        <th>Tgl.Kapitalisasi</th>
                                                        <th>Nominal</th>
                                                        <th>Akum.Depresiasi</th>
                                                        <th>Saldo Buku</th>
                                                        <th>Metode</th>
                                                        <th>Keterangan</th>
                                                        <th>Status</th>
                                                        <th>Plant</th>
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


<div id="modal1" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;width:80%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Tambah/Edit {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="input-field col m4 s6">
                            <input type="hidden" id="temp" name="temp">
                            <input id="code" name="code" type="text" placeholder="Kode">
                            <label class="active" for="code">Kode</label>
                        </div>
                        <div class="input-field col m4 s6">
                            <select class="form-control" id="place_id" name="place_id">
                                <option value="">--Kosong--</option>
                                @foreach ($place as $rowplace)
                                    <option value="{{ $rowplace->id }}" {{ $rowplace->id == session('bo_place_id') ? 'selected' : '' }}>{{ $rowplace->code }}</option>
                                @endforeach
                            </select>
                            <label class="" for="place_id">Plant</label>
                        </div>
                        <div class="input-field col m4 s6">
                            <input id="name" name="name" type="text" placeholder="Nama">
                            <label class="active" for="name">Nama</label>
                        </div>
                        <div class="input-field col m4 s6">
                            <select class="select2 browser-default" id="asset_group_id" name="asset_group_id">
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
                            <label class="active" for="asset_group_id">Grup Aset</label>
                        </div>
                        <div class="input-field col m4 s12">
                            <select class="form-control" id="method" name="method">
                                <option value="1">Straight Line</option>
                                <option value="2">Declining Balance</option>
                            </select>
                            <label class="" for="method">Metode Hitung</label>
                        </div>
                        <div class="input-field col m4 s6">
                            <input id="date" name="date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. kapitalisasi" readonly>
                            <label class="active" for="date">Tgl. Kapitalisasi (Dari form kapitalisasi)</label>
                        </div>
                        <div class="input-field col m4 s6">
                            <input id="nominal" name="nominal" type="text" placeholder="Nominal Kapitalisasi" value="0" onkeyup="formatRupiah(this)" readonly>
                            <label class="active" for="nominal">Nominal Awal (Dari form kapitalisasi)</label>
                        </div>
                        <div class="input-field col m4 s12">
                            <textarea id="note" name="note" placeholder="Catatan / Keterangan" rows="1" class="materialize-textarea"></textarea>
                            <label class="active" for="note">Keterangan</label>
                        </div>
                        <div class="input-field col m4 s12">
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
                            <span>Dokumen PO</span>
                            <input type="file" class="form-control-file" id="fileExcel" name="file">
                        </div>
                        <div class="file-path-wrapper">
                            <input class="file-path validate" type="text">
                        </div>
                    </div>
                    <div class="input-field col m6 s12">
                        Download format disini : <a href="{{ asset(Storage::url('format_imports/format_asset.xlsx')) }}" target="_blank">File</a>
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
                            console.log(response.error);
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
                    
                    console.log(errors);
                }
            });

        });
        
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
        
        $("#asset_group_id").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });
    });

    function loadDataTable() {
		window.table = $('#datatable_serverside').DataTable({
            "responsive": false,
            "scrollX": true,
            "stateSave": true,
            "serverSide": true,
            "deferRender": true,
            "destroy": true,
            "iDisplayInLength": 10,
            "order": [[0, 'asc']],
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
                { name: 'name', className: 'center-align' },
                { name: 'asset_group_id', className: 'center-align' },
                { name: 'date', className: 'center-align' },
                { name: 'nominal', className: 'center-align' },
                { name: 'depreciation', searchable: false, orderable: false, className: 'center-align' },
                { name: 'book_balance', className: 'center-align' },
                { name: 'method', className: 'center-align' },
                { name: 'note', className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'place', searchable: false, orderable: false, className: 'center-align' },
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
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
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
                $('#place_id').val(response.place_id).formSelect();
                $('#asset_group_id').val(response.asset_group_id).trigger('change');
                $('#date').val(response.date);
                $('#nominal').val(response.nominal);
                $('#method').val(response.method).formSelect();
                $('#note').val(response.note);

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
        var balance = $('#filter_balance').val();
        
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status + "&balance=" + balance;
    }

    var printService = new WebSocketPrinter({
        onConnect: function () {
            
        },
        onDisconnect: function () {
           
        },
        onUpdate: function (message) {
            
        },
    });

    function print(){
        var search = window.table.search(), status = $('#filter_status').val(), type = $('#filter_type').val(), company = $('#filter_company').val(), account = $('#filter_account').val();
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