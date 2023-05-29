<style>
    .modal {
        top:0px !important;
    }

    table > thead > tr > th {
        font-size: 13px !important;
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
                                            <table id="datatable_serverside" class="display responsive-table wrap">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Code</th>
                                                        <th>Nama</th>
                                                        <th>Coa</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;width:80%;">
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
                            <input type="hidden" id="temp" name="temp">
                            <input id="code" name="code" type="text" placeholder="Kode">
                            <label class="active" for="code">Kode</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="name" name="name" type="text" placeholder="Nama">
                            <label class="active" for="name">Nama</label>
                        </div>
                        <div class="input-field col s6">
                            <select class="browser-default" id="coa_id" name="coa_id"></select>
                            <label class="active" for="coa_id">Coa (Opsional)</label>
                        </div>
                        <div class="input-field col s6">
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
                            <table class="bordered">
                                <thead>
                                    <tr>
                                        <th class="center">Plant</th>
                                        <th class="center">Mesin</th>
                                        <th class="center">Departemen</th>
                                        <th class="center">Gudang</th>
                                        <th class="center">Prosentase</th>
                                        <th class="center">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody id="body-detail">
                                    <tr id="last-row-detail">
                                        <td colspan="6" class="center">
                                            <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addLine()" href="javascript:void(0);">
                                                <i class="material-icons left">add</i> Tambah Baris
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
        loadDataTable();
        
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
                $('.row_distribution').remove();
                M.updateTextFields();
                $('#coa_id').empty();
            }
        });

        $('#body-detail').on('click', '.delete-data-detail', function() {
            $(this).closest('tr').remove();
        });

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

        select2ServerSide('#coa_id', '{{ url("admin/select2/coa_journal") }}');
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

    function addLine(){
        var count = makeid(10);
        $('#last-row-detail').before(`
            <tr class="row_distribution">
                <td>
                    <select class="browser-default" id="arr_place` + count + `" name="arr_place[]">
                        @foreach ($place as $rowplace)
                            <option value="{{ $rowplace->id }}">{{ $rowplace->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select class="browser-default" id="arr_line` + count + `" name="arr_line[]">
                        <option value="">--Kosong--</option>
                        @foreach ($line as $rowline)
                            <option value="{{ $rowline->id }}">{{ $rowline->name }}</option>
                        @endforeach    
                    </select>
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                        <option value="">--Kosong--</option>
                        @foreach ($department as $rowdept)
                            <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">
                        <option value="">--Kosong--</option>
                        @foreach ($warehouse as $rowwarehouse)
                            <option value="{{ $rowwarehouse->id }}">{{ $rowwarehouse->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="center">
                    <input id="arr_percentage` + count + `" name="arr_percentage[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);" style="width:100%;text-align:right;">
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
    }

    function loadDataTable() {
		window.table = $('#datatable_serverside').DataTable({
            "responsive": true,
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
                { name: 'name', className: 'center-align' },
                { name: 'coa_id', className: 'center-align' },
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
			
        var formData = new FormData($('#form_data')[0]), passed = true, total = 0;

        $('input[name^="arr_percentage"]').each(function(){
            total += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
        });

        if(total !== 100){
            passed = false;
        }
        
        if(passed){
            formData.delete("arr_line[]");
            formData.delete("arr_department[]");
            formData.delete("arr_warehouse[]");

            $('select[name^="arr_place"]').each(function(index){
                formData.append('arr_line[]',($('select[name^="arr_line"]').eq(index).val() ? $('select[name^="arr_line"]').eq(index).val() : ''));
                formData.append('arr_department[]',($('select[name^="arr_department"]').eq(index).val() ? $('select[name^="arr_department"]').eq(index).val() : ''));
                formData.append('arr_warehouse[]',($('select[name^="arr_warehouse"]').eq(index).val() ? $('select[name^="arr_warehouse"]').eq(index).val() : ''));
            });
            
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
        }else{
            swal({
                title: 'Ups!',
                text: 'Total prosentase tidak sama dengan 100%.',
                icon: 'error'
            });
        }
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
                if(response.coa_name){
                    $('#coa_id').empty().append(`
                        <option value="` + response.coa_id + `">` + response.coa_name + `</option>
                    `);
                }else{
                    $('#coa_id').empty();
                }
                if(response.status == '1'){
                    $('#status').prop( "checked", true);
                }else{
                    $('#status').prop( "checked", false);
                }

                $('.row_distribution').remove();

                $.each(response.details, function(i, val) {
                    var count = makeid(10);
                    $('#last-row-detail').before(`
                        <tr class="row_distribution">
                            <td>
                                <select class="browser-default" id="arr_place` + count + `" name="arr_place[]">
                                    @foreach ($place as $rowplace)
                                        <option value="{{ $rowplace->id }}">{{ $rowplace->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select class="browser-default" id="arr_line` + count + `" name="arr_line[]">
                                    <option value="">--Kosong--</option>
                                    @foreach ($line as $rowline)
                                        <option value="{{ $rowline->id }}">{{ $rowline->name }}</option>
                                    @endforeach    
                                </select>
                            </td>
                            <td class="center">
                                <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                                    <option value="">--Kosong--</option>
                                    @foreach ($department as $rowdept)
                                        <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">
                                    <option value="">--Kosong--</option>
                                    @foreach ($warehouse as $rowwarehouse)
                                        <option value="{{ $rowwarehouse->id }}">{{ $rowwarehouse->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="center">
                                <input id="arr_percentage` + count + `" name="arr_percentage[]" class="browser-default" type="text" value="` + val.percentage + `" onkeyup="formatRupiah(this);" style="width:100%;text-align:right;">
                            </td>
                            <td class="center">
                                <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                                    <i class="material-icons">delete</i>
                                </a>
                            </td>
                        </tr>
                    `);
                    $('#arr_place' + count).val(val.place_id);
                    $('#arr_line' + count).val(val.line_id);
                    $('#arr_department' + count).val(val.department_id);
                    $('#arr_warehouse' + count).val(val.warehouse_id);
                });

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
</script>