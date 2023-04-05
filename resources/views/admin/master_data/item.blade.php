<style>
    .select2-container--default .select2-selection--multiple, .select2-container--default.select2-container--focus .select2-selection--multiple {
        height: auto !important;
    }

    table > thead > tr > th {
        font-size: 13px !important;
    }

    table.bordered th {
        padding: 5px !important;
    }
</style>
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
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3 modal-trigger" href="#modal2">
                            <i class="material-icons hide-on-med-and-up">file_upload</i>
                            <span class="hide-on-small-onl">Import</span>
                            <i class="material-icons right">file_upload</i>
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

                                        <label for="filter_type" style="font-size:1.2rem;">Filter Tipe :</label>
                                        <div class="input-field inline" style="margin-top: 0;margin-bottom: 0;min-width:35% !important;max-width:100%;">
                                            <select class="select2 browser-default" multiple="multiple" id="filter_type" name="filter_type" onchange="loadDataTable()">
                                                <option value="" disabled>Semua</option>
                                                <option value="1">Item Stok</option>
                                                <option value="2">Item Penjualan</option>
                                                <option value="3">Item Pembelian</option>
                                                <option value="4">Item Service</option>
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
                                            <table id="datatable_serverside" class="display responsive-table wrap">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Kode</th>
                                                        <th>Nama</th>
                                                        <th>Grup</th>
                                                        <th>UOM</th>
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

<div id="modal2" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;max-width:90%;min-width:70%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>import Excel</h4>
                <div class="col s12">
                    <div id="validation_alertImport" style="display:none;"></div>
                </div>
                <form action="{{ Request::url() }}/import" method="POST" enctype="multipart/form-data" id="form_dataimport">
                    @csrf
                    <div class="file-field input-field">
                        <div class="form-group">
                            <div class="btn">
                                <span>Choose Excel file to import</span>
                                <input type="file" class="form-control-file" id="fileExcel" name="file">
                            </div>
                            <div class="file-path-wrapper">
                                <input class="file-path validate" type="text">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Import</button>
                </form>
                
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal1" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;max-width:90%;min-width:70%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Add/Edit Item</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="input-field col s6">
                            <input type="hidden" id="temp" name="temp">
                            <input id="code" name="code" type="text" placeholder="Kode Item">
                            <label class="active" for="code">Kode</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="name" name="name" type="text" placeholder="Nama Item">
                            <label class="active" for="name">Nama</label>
                        </div>
                        <div class="input-field col s6">
                            <select class="select2 browser-default" id="item_group_id" name="item_group_id">
                                @foreach($group->whereNull('parent_id') as $c)
                                        @if(!$c->childSub()->exists())
                                            <option value="{{ $c->id }}"> - {{ $c->name.' COA '.$c->coa->code.' - '.$c->coa->name }}</option>
                                        @else
                                            <optgroup label=" - {{ $c->code.' - '.$c->name }}">
                                            @foreach($c->childSub as $bc)
                                                @if(!$bc->childSub()->exists())
                                                    <option value="{{ $bc->id }}"> -  - {{ $bc->name.' COA '.$bc->coa->code.' - '.$bc->coa->name }}</option>
                                                @else
                                                    <optgroup label=" -  - {{ $bc->code.' - '.$bc->name }}">
                                                        @foreach($bc->childSub as $bcc)
                                                            @if(!$bcc->childSub()->exists())
                                                                <option value="{{ $bcc->id }}"> -  -  - {{ $bcc->name.' COA '.$bcc->coa->code.' - '.$bcc->coa->name }}</option>
                                                            @else
                                                                <optgroup label=" -  -  - {{ $bcc->code.' - '.$bcc->name }}">
                                                                    @foreach($bcc->childSub as $bccc)
                                                                        @if(!$bccc->childSub()->exists())
                                                                            <option value="{{ $bccc->id }}"> -  -  -  - {{ $bccc->name.' COA '.$bccc->coa->code.' - '.$bccc->coa->name }}</option>
                                                                        @else
                                                                            <optgroup label=" -  -  -  - {{ $bccc->code.' - '.$bccc->name }}">
                                                                                @foreach($bccc->childSub as $bcccc)
                                                                                    @if(!$bcccc->childSub()->exists())
                                                                                        <option value="{{ $bcccc->id }}"> -  -  -  -  - {{ $bcccc->name.' COA '.$bcccc->coa->code.' - '.$bcccc->coa->name }}</option>
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
                            <label class="active" for="item_group_id">Grup Item</label>
                        </div>
                        <div class="input-field col s6">
                            <div class="switch mb-1">
                                <label for="status">Status</label>
                                <label class="right">
                                    Non-Active
                                    <input checked type="checkbox" id="status" name="status" value="1">
                                    <span class="lever"></span>
                                    Active
                                </label>
                            </div>
                        </div>
                        <div class="col s12">
                            <div class="col s4">
                                <div class="input-field col s12">
                                    <select class="form-control" id="buy_unit" name="buy_unit">
                                        @foreach ($unit as $row)
                                            <option value="{{ $row->id }}">{{ $row->name.' - '.$row->code }}</option>
                                        @endforeach
                                    </select>
                                    <label class="" for="buy_unit">Satuan Beli</label>
                                </div>
                                <div class="input-field col s12">
                                    <select class="form-control" id="sell_unit" name="sell_unit">
                                        @foreach ($unit as $row)
                                            <option value="{{ $row->id }}">{{ $row->name.' - '.$row->code }}</option>
                                        @endforeach
                                    </select>
                                    <label class="" for="sell_unit">Satuan Jual</label>
                                </div>
                            </div>
                            <div class="col s4">
                                <div class="input-field col s12">
                                    <input id="buy_convert" name="buy_convert" type="text" placeholder="Ex: 1 SAK Beli = *50* KG Stok" onkeyup="formatRupiah(this);">
                                    <label class="active" for="buy_convert">Konversi Satuan Beli ke Stok</label>
                                </div>
                                <div class="input-field col s12">
                                    <input id="sell_convert" name="sell_convert" type="text" placeholder="Ex: 1 Truk Jual = *100* KG Stok" onkeyup="formatRupiah(this);">
                                    <label class="active" for="sell_convert">Konversi Satuan Jual ke Stok</label>
                                </div>
                            </div>
                            <div class="col s4">
                                <div class="input-field col s12" style="top: 30px;">
                                    <select class="form-control" id="uom_unit" name="uom_unit">
                                        @foreach ($unit as $row)
                                            <option value="{{ $row->id }}">{{ $row->name.' - '.$row->code }}</option>
                                        @endforeach
                                    </select>
                                    <label class="" for="uom_unit">Satuan Stok & Produksi</label>
                                </div>
                            </div>
                        </div>
                        <div class="input-field col s6">
                            <div class="switch mb-1">
                                <label for="is_inventory_item">Item untuk Inventori</label>
                                <label class="right">
                                    Tidak
                                    <input type="checkbox" id="is_inventory_item" name="is_inventory_item" value="1">
                                    <span class="lever"></span>
                                    Ya
                                </label>
                            </div>
                            <div class="switch mb-1">
                                <label for="is_sales_item">Item untuk Penjualan</label>
                                <label class="right">
                                    Tidak
                                    <input type="checkbox" id="is_sales_item" name="is_sales_item" value="1">
                                    <span class="lever"></span>
                                    Ya
                                </label>
                            </div>
                            <div class="switch mb-1">
                                <label for="is_purchase_item">Item untuk Pembelian</label>
                                <label class="right">
                                    Tidak
                                    <input type="checkbox" id="is_purchase_item" name="is_purchase_item" value="1">
                                    <span class="lever"></span>
                                    Ya
                                </label>
                            </div>
                            <div class="switch mb-1">
                                <label for="is_service">Item untuk Service</label>
                                <label class="right">
                                    Tidak
                                    <input type="checkbox" id="is_service" name="is_service" value="1">
                                    <span class="lever"></span>
                                    Ya
                                </label>
                            </div>
                        </div>
                        <div class="input-field col s6">
                            <select class="select2 browser-default" multiple="multiple" id="warehouse_id" name="warehouse_id[]">
                                @foreach ($warehouse as $row)
                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                @endforeach
                            </select>
                            <label class="active" for="warehouse_id">Gudang</label>
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

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div>

<!-- END: Page Main-->
<script>
    $(function() {
        $('#datatable_serverside').on('click', 'td.details-control', function() {
            var tr    = $(this).closest('tr');
            var badge = tr.find('button.btn-floating');
            var icon  = tr.find('i');
            var row   = table.row(tr);

            if(row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
                badge.first().removeClass('red');
                badge.first().addClass('green');
                icon.first().html('add');
            } else {
                row.child(rowDetail(row.data())).show();
                tr.addClass('shown');
                badge.first().removeClass('green');
                badge.first().addClass('red');
                icon.first().html('remove');
            }
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
                },
                success: function(response) {
                    if(response.status == 200) {
                        success();
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
                                            <p> baris ke ` +val.row+ ` pada kolom ` +val.attribute+ ` </p>
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
                                            <p> ` +val+`</p>
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
                },
                error: function(response) {
                    console.log(respose);
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
        
        loadDataTable();
        $('#modal2').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onCloseEnd: function(modal, trigger){
                $('#form_dataimport')[0].reset();
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
                M.updateTextFields();
            }
        });

        $("#item_group_id,#warehouse_id").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        $("#filter_type").select2({
            placeholder: "Kosong untuk semua tipe.",
            dropdownAutoWidth: true,
            width: '100%',
        });
    });

    function rowDetail(data) {
        var content = '';
        $.ajax({
            url: '{{ Request::url() }}/row_detail',
            type: 'GET',
            async: false,
            data: {
                id: $(data[0]).data('id')
            },
            success: function(response) {
                content += response;
            },
            error: function() {
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
            }
        });

        return content;
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
            "order": [[0, 'asc']],
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    status : $('#filter_status').val(),
                    'type[]' : $('#filter_type').val()
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
                { name: 'group', className: 'center-align' },
                { name: 'uom', className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle' /* or colvis */
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
                $('#name').val(response.name);
                $('#item_group_id').val(response.item_group_id).trigger('change');
                $('#uom_unit').val(response.uom_unit).formSelect();
                $('#buy_unit').val(response.buy_unit).formSelect();
                $('#buy_convert').val(response.buy_convert);
                $('#sell_unit').val(response.sell_unit).formSelect();
                $('#sell_convert').val(response.sell_convert);
                $('#warehouse_id').val(response.warehouses).trigger('change');

                if(response.is_inventory_item == '1'){
                    $('#is_inventory_item').prop( "checked", true);
                }else{
                    $('#is_inventory_item').prop( "checked", false);
                }

                if(response.is_sales_item == '1'){
                    $('#is_sales_item').prop( "checked", true);
                }else{
                    $('#is_sales_item').prop( "checked", false);
                }

                if(response.is_purchase_item == '1'){
                    $('#is_purchase_item').prop( "checked", true);
                }else{
                    $('#is_purchase_item').prop( "checked", false);
                }

                if(response.is_service == '1'){
                    $('#is_service').prop( "checked", true);
                }else{
                    $('#is_service').prop( "checked", false);
                }

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

    function print(){
        var search = window.table.search();
        var status = $('#filter_status').val();
        var type = $('#filter_type').val();
        
        $.ajax({
            type : "POST",
            url  : '{{ Request::url() }}/print',
            data : {
                search : search,
                status : status,
                type : type
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

    function exportExcel(){
        var search = window.table.search();
        var status = $('#filter_status').val();
        var type = $('#filter_type').val();
        
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status + "&type=" + type;
    }

</script>