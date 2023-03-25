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
                            <li class="breadcrumb-item active">{{ Str::title(str_replace('_',' ',Request::segment(3))) }}
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
                                                <option value="1">Menunggu</option>
                                                <option value="2">Dalam Proses</option>
                                                <option value="3">Selesai</option>
                                                <option value="4">Ditolak</option>
                                                <option value="5">Ditutup</option>
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
                                                        <th rowspan="2">#</th>
                                                        <th rowspan="2">Code</th>
                                                        <th colspan="4" class="center-align">Tanggal</th>
                                                        <th rowspan="2">Keterangan</th>
                                                        <th rowspan="2">Dokumen</th>
                                                        <th rowspan="2">Proyek</th>
                                                        <th rowspan="2">Gudang</th>
                                                        <th rowspan="2">Pabrik/Kantor</th>
                                                        <th rowspan="2">Status</th>
                                                        <th rowspan="2">Action</th>
                                                    </tr>
                                                    <tr>
                                                        <th>Pengajuan</th>
                                                        <th>Kadaluwarsa</th>
                                                        <th>Dokumen</th>
                                                        <th>Pemakaian</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 80% !important;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Add/Edit {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="row">
                            <div class="input-field col m4 s12">
                                <input type="hidden" id="temp" name="temp">
                                <textarea id="note" name="note" placeholder="Catatan / Keterangan" rows="1"></textarea>
                                <label class="active" for="note">Keterangan</label>
                            </div>
                            <div class="input-field col m4 s12">
                                <input id="post_date" name="post_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);">
                                <label class="active" for="post_date">Tgl. Posting</label>
                            </div>
                            <div class="input-field col m4 s12">
                                <input id="due_date" name="due_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. posting">
                                <label class="active" for="due_date">Tgl. Kadaluwarsa</label>
                            </div>
                            <div class="input-field col m4 s12">
                                <input id="document_date" name="document_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. posting">
                                <label class="active" for="document_date">Tgl. Dokumen</label>
                            </div>
                            <div class="input-field col m4 s12">
                                <input id="required_date" name="required_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. posting">
                                <label class="active" for="required_date">Tgl. Dipakai</label>
                            </div>
                            <div class="file-field input-field col m4 s12">
                                <div class="btn">
                                    <span>File</span>
                                    <input type="file" name="file" id="file">
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text">
                                </div>
                            </div>
                            <div class="input-field col m4 s12">
                                <select class="form-control" id="place_id" name="place_id">
                                    @foreach ($place as $rowplace)
                                        <option value="{{ $rowplace->id }}">{{ $rowplace->name.' - '.$rowplace->company->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="place_id">Pabrik/Kantor</label>
                            </div>
                            <div class="input-field col m4 s12">
                                <select class="browser-default" id="warehouse_id" name="warehouse_id"></select>
                                <label class="active" for="warehouse_id">Gudang Tujuan</label>
                            </div>
                            <div class="input-field col m4 s12">
                                <select class="browser-default" id="project_id" name="project_id"></select>
                                <label for="project_id" class="active">Link Proyek (Jika ada) :</label>
                            </div>
                            <div class="col m12 s12">
                                <p class="mt-2 mb-2">
                                    <h4>Detail Produk</h4>
                                    <table class="bordered">
                                        <thead>
                                            <tr>
                                                <th width="40%" class="center">Item</th>
                                                <th width="10%" class="center">Qty</th>
                                                <th width="10%" class="center">Satuan</th>
                                                <th width="20%" class="center">Keterangan</th>
                                                <th width="15%" class="center">Tgl.Dipakai</th>
                                                <th width="5%" class="center">Hapus</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-item">
                                            <tr class="row_item">
                                                <td>
                                                    <select class="browser-default item-array" id="arr_item0" name="arr_item[]" onchange="getRowUnit(0)"></select>
                                                </td>
                                                <td>
                                                    <input name="arr_qty[]" type="text" value="0" onkeyup="formatRupiah(this)">
                                                </td>
                                                <td class="center">
                                                    <span id="arr_satuan0">-</span>
                                                </td>
                                                <td>
                                                    <input name="arr_note[]" type="text" placeholder="Keterangan barang...">
                                                </td>
                                                <td>
                                                    <input name="arr_required_date[]" type="date" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}">
                                                </td>
                                                <td class="center">
                                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                                        <i class="material-icons">delete</i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr id="last-row-item">
                                                <td colspan="6" class="center">
                                                    <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addItem()" href="javascript:void(0);">
                                                        <i class="material-icons left">add</i> New Item
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </p>
                            </div>
                            <div class="col s12 mt-3">
                                <button class="btn waves-effect waves-light right submit" onclick="save();">Simpan <i class="material-icons right">send</i></button>
                            </div>
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

        loadDataTable();
        
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                $('#post_date').attr('min','{{ date("Y-m-d") }}');
                $('#due_date').attr('min','{{ date("Y-m-d") }}');
                $('#document_date').attr('min','{{ date("Y-m-d") }}');
                $('#required_date').attr('min','{{ date("Y-m-d") }}');
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
                $('#project_id,#warehouse_id').empty();
                $('.row_item').remove();
            }
        });

        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
        });

        select2ServerSide('#arr_item0', '{{ url("admin/select2/purchase_item") }}');
        select2ServerSide('#project_id', '{{ url("admin/select2/project") }}');
        select2ServerSide('#warehouse_id', '{{ url("admin/select2/warehouse") }}');
    });

    function getRowUnit(val){
        $('#arr_satuan' + val).text($("#arr_item" + val).select2('data')[0].buy_unit);
    }

    function addItem(){
        var count = makeid(10);
        $('#last-row-item').before(`
            <tr class="row_item">
                <td>
                    <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                </td>
                <td>
                    <input name="arr_qty[]" type="text" value="0" onkeyup="formatRupiah(this)">
                </td>
                <td class="center">
                    <span id="arr_satuan` + count + `">-</span>
                </td>
                <td>
                    <input name="arr_note[]" type="text" placeholder="Keterangan barang...">
                </td>
                <td>
                    <input name="arr_required_date[]" type="date" value="{{ date('Y-m-d') }}" min="` + $('#post_date').val() + `">
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        select2ServerSide('#arr_item' + count, '{{ url("admin/select2/purchase_item") }}');
    }

    function changeDateMinimum(val){
        if(val){
            $('#due_date,#document_date,#required_date').attr("min",val);
            $('input[name^="arr_required_date"]').each(function(){
                $(this).attr("min",val);
            });
        }
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
                { name: 'date_post', className: 'center-align' },
                { name: 'date_due', className: 'center-align' },
                { name: 'date_doc', className: 'center-align' },
                { name: 'date_use', className: 'center-align' },
                { name: 'note', className: 'center-align' },
                { name: 'document', searchable: false, orderable: false, className: 'center-align' },
                { name: 'project_id', searchable: false, orderable: false, className: 'center-align' },
                { name: 'warehouse_id', searchable: false, orderable: false, className: 'center-align' },
                { name: 'place_id', searchable: false, orderable: false, className: 'center-align' },
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
                $('#note').val(response.note);
                $('#post_date').val(response.post_date);
                $('#due_date').val(response.due_date);
                $('#document_date').val(response.document_date);
                $('#required_date').val(response.required_date);
                $('#post_date').removeAttr('min');
                $('#due_date').removeAttr('min');
                $('#document_date').removeAttr('min');
                $('#required_date').removeAttr('min');
                $('#place_id').val(response.place_id).formSelect();

                if(response.project_id){
                    $('#project_id').empty();
                    $('#project_id').append(`
                        <option value="` + response.project_id + `">` + response.project_name + `</option>
                    `);
                }
                
                $('#warehouse_id').empty();
                $('#warehouse_id').append(`
                    <option value="` + response.warehouse_id + `">` + response.warehouse_name + `</option>
                `);

                if(response.details.length > 0){
                    $('.row_item').each(function(){
                        $(this).remove();
                    });

                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        $('#last-row-item').before(`
                            <tr class="row_item">
                                <td>
                                    <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                                </td>
                                <td>
                                    <input name="arr_qty[]" type="text" value="` + val.qty + `" onkeyup="formatRupiah(this)">
                                </td>
                                <td class="center">
                                    <span id="arr_satuan` + count + `">` + val.unit + `</span>
                                </td>
                                <td>
                                    <input name="arr_note[]" type="text" placeholder="Keterangan barang..." value="` + val.note + `">
                                </td>
                                <td>
                                    <input name="arr_required_date[]" type="date" value="` + val.date + `" min="` + $('#post_date').val() + `">
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                        $('#arr_item' + count).append(`
                            <option value="` + val.item_id + `">` + val.item_name + `</option>
                        `);
                        select2ServerSide('#arr_item' + count, '{{ url("admin/select2/purchase_item") }}');
                    });
                }
                
                $('.modal-content').scrollTop(0);
                $('#note').focus();
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
</script>