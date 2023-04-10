<style>
.select2-selection--single {
    height: 100% !important;
}
.select2-selection__rendered{
    word-wrap: break-word !important;
    text-overflow: inherit !important;
    white-space: normal !important;
}
.modal {
    top:0px !important;
}
table > thead > tr > th {
    font-size: 13px !important;
}

table.bordered th {
    padding: 5px !important;
}

.browser-default {
    height: 2rem !important;
}
</style>
<!-- BEGIN: Page Main-->
<div id="main">
    <div class="row">
        <div class="pt-3 pb-1" id="breadcrumbs-wrapper">
            <!-- Search for small screen-->
            <div class="container">
                <div class="row">
                    <div class="col s12 m6 l6">
                        <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ $title }}</span></h5>
                    </div>
                    <div class="col s12 m6 l6 right-align-md">
                        <ol class="breadcrumbs mb-0">
                            <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item"><a href="#">{{ Str::ucfirst(Request::segment(2)) }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{ Str::ucfirst(Request::segment(3)) }}
                            </li>
                        </ol>
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
                            {{-- <div class="card-panel">
                                <div class="row">
                                    <div class="col s12 ">
                                        <div class="card-alert card cyan lighten-5">
                                            <div class="card-content cyan-text">
                                                <p>INFO 1 : Peta jurnal bisa diaplikasikan hanya untuk menu/form dengan database yang memiliki tipe nominal (double).</p>
                                            </div>
                                        </div>
                                        <div class="card-alert card purple lighten-5">
                                            <div class="card-content purple-text">
                                                <p>INFO 2 : Khusus untuk pembelian item barang (Good Receipt) dan Landed Cost untuk stok, maka jurnal nominal sebelum ppn pada debit akan diisi dengan masing-masing coa pada kelompok item barang tersebut.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">
                                        List Menu Coa
                                        <button class="btn waves-effect waves-light mr-1 float-right btn-small" onclick="loadDataTable()">
                                            Refresh
                                            <i class="material-icons left">refresh</i>
                                        </button>
                                    </h4>
                                    <div class="row mt-2">
                                        <div class="col s12">
                                            <table id="datatable_serverside" class="display nowrap">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Nama</th>
                                                        <th>Url</th>
                                                        <th>Peta Jurnal</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="min-height: 100%;min-width:100%;height:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Add/Edit Coa Menu - <b><i><span id="title_coa_menu"></span></i></b></h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                        <input type="hidden" id="temp" name="temp">
                        <input type="hidden" id="fields" name="fields">
                    </div>
                    <div class="col s6">
                        <table class="bordered">
                            <thead>
                                <tr>
                                    <th class="center" colspan="5">Debit</th>
                                </tr>
                                <tr>
                                    <th class="center" width="40%">Coa</th>
                                    <th class="center" width="20%">Kolom</th>
                                    <th class="center" width="15%">Prosentase</th>
                                    <th class="center" width="20%">Mata Uang</th>
                                    <th class="center" width="5%">Delete</th>
                                </tr>
                            </thead>
                            <tbody id="body-debit">
                                <tr id="last-row-debit">
                                    <td colspan="5" class="center">
                                        <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addCoa('1')" href="javascript:void(0);">
                                            <i class="material-icons left">add</i> Tambah Debit
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col s6">
                        <table class="bordered">
                            <thead>
                                <tr>
                                    <th class="center" colspan="5">Kredit</th>
                                </tr>
                                <tr>
                                    <th class="center" width="40%">Coa</th>
                                    <th class="center" width="20%">Kolom</th>
                                    <th class="center" width="15%">Prosentase</th>
                                    <th class="center" width="20%">Mata Uang</th>
                                    <th class="center" width="5%">Delete</th>
                                </tr>
                            </thead>
                            <tbody id="body-credit">
                                <tr id="last-row-credit">
                                    <td colspan="5" class="center">
                                        <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addCoa('2')" href="javascript:void(0);">
                                            <i class="material-icons left">add</i> Tambah Credit
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col s12 mt-1 center-align">
                        <button class="btn waves-effect waves-light submit" onclick="save();">Simpan <i class="material-icons right">send</i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<!-- END: Page Main-->
<script>
    var optioncurrency = '';

    @foreach ($currency as $row)
        optioncurrency += '<option value="{{ $row->id }}">{{ $row->code}}</option>';
    @endforeach

    $(function() {
        $('#body-debit').on('click', '.delete-data-debit', function() {
            $(this).closest('tr').remove();
        });

        $('#body-credit').on('click', '.delete-data-credit', function() {
            $(this).closest('tr').remove();
        });

        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) {
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                $('.row_debit,.row_credit').remove();
                M.updateTextFields();
            }
        });

        loadDataTable();

    });

    function loadDataTable() {
        window.table = $('#datatable_serverside').DataTable({
            "responsive": true,
            /* "scrollX": true, */
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
                { name: 'name', className: 'center-align' },
                { name: 'url', className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ]
        });
        $('select[name="datatable_serverside_length"]').addClass('browser-default');
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
                $('#title_coa_menu').text(response.fullname);
                $('#fields').val(response.fields);
                $('.modal-content').scrollTop(0);

                if(response.details.length > 0){
                    $.each(response.details, function(i, val) {
                        var randomString = makeid(10);
                        var column = `<select class="form-control" name="arr_field[]" id="arr_field` + randomString + `" required>`;
                        var arrField = JSON.parse(response.fields);
                        
                            for (let i = 0; i < arrField.length; i++) {
                                column += `<option value="` + arrField[i]['column'] + `" ` + (val.field_name == arrField[i]['column'] ? 'selected' : '') + `>` + arrField[i]['column'] + `</option>`;
                            }

                        column += `</select>`;

                        if(val.type == '1'){
                            $('#last-row-debit').before(`
                                <tr class="row_debit">
                                    <input type="hidden" name="arr_type[]" value="1">
                                    <td>
                                        <select class="browser-default coa-array" id="arr_coa` + randomString + `" name="arr_coa[]" required style="width: 100%"></select>
                                    </td>
                                    <td>
                                        ` + column + `
                                    </td>
                                    <td>
                                        <input type="text" name="arr_percent[]" value="100" step="1" min="1" max="100" class="form-control" onkeyup="formatRupiah(this)" required>
                                    </td>
                                    <td>
                                        <select class="form-control" id="arr_currency` + randomString + `" name="arr_currency[]">` + optioncurrency + `</select>
                                    </td>
                                    <td class="center">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-debit" href="javascript:void(0);">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                </tr>
                            `);
                        }

                        if(val.type == '2'){
                            
                            $('#last-row-credit').before(`
                                <tr class="row_credit">
                                    <input type="hidden" name="arr_type[]" value="2">
                                    <td>
                                        <select class="browser-default coa-array" id="arr_coa` + randomString + `" name="arr_coa[]" required style="width: 100%"></select>
                                    </td>
                                    <td>
                                        ` + column + `
                                    </td>
                                    <td>
                                        <input type="text" name="arr_percent[]" value="` + val.percentage + `" step="1" min="1" max="100" class="form-control" onkeyup="formatRupiah(this)">
                                    </td>
                                    <td>
                                        <select class="form-control" id="arr_currency` + randomString + `" name="arr_currency[]">` + optioncurrency + `</select>
                                    </td>
                                    <td class="center">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-credit" href="javascript:void(0);">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                </tr>
                            `);
                        }

                        select2ServerSide('#arr_coa' + randomString, '{{ url("admin/select2/coa") }}');
                        $('#arr_field' + randomString).formSelect();
                        $('#arr_coa' + randomString).append(`
                            <option value="` + val.coa_id + `">` + val.coa_name + `</option>
                        `);
                        $('#arr_currency' + randomString).formSelect();
                        $('#arr_currency' + randomString).val(val.currency_id).formSelect();
                    });
                }

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

    function addCoa(type){
        var randomString = makeid(10);
        var column = `<select class="form-control" name="arr_field[]" id="arr_field` + randomString + `" required>`;
        var arrField = JSON.parse($('#fields').val());
        
            for (let i = 0; i < arrField.length; i++) {
                column += `<option value="` + arrField[i]['column'] + `">` + arrField[i]['column'] + `</option>`;
            }

        column += `</select>`;

        if(type == '1'){
            $('#last-row-debit').before(`
                <tr class="row_debit">
                    <input type="hidden" name="arr_type[]" value="1">
                    <td>
                        <select class="browser-default coa-array" id="arr_coa` + randomString + `" name="arr_coa[]" required style="width: 100%"></select>
                    </td>
                    <td>
                        ` + column + `
                    </td>
                    <td>
                        <input type="text" name="arr_percent[]" value="100" step="1" min="1" max="100" class="form-control" onkeyup="formatRupiah(this)" required>
                    </td>
                    <td>
                        <select class="form-control" id="arr_currency` + randomString + `" name="arr_currency[]">` + optioncurrency + `</select>
                    </td>
                    <td class="center">
                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-debit" href="javascript:void(0);">
                            <i class="material-icons">delete</i>
                        </a>
                    </td>
                </tr>
            `);
            select2ServerSide('#arr_coa' + randomString, '{{ url("admin/select2/coa") }}');
            $('#arr_field' + randomString).formSelect();
            $('#arr_currency' + randomString).formSelect();
        }

        if(type == '2'){
            
            $('#last-row-credit').before(`
                <tr class="row_credit">
                    <input type="hidden" name="arr_type[]" value="2">
                    <td>
                        <select class="browser-default coa-array" id="arr_coa` + randomString + `" name="arr_coa[]" required style="width: 100%"></select>
                    </td>
                    <td>
                        ` + column + `
                    </td>
                    <td>
                        <input type="text" name="arr_percent[]" value="100" step="1" min="1" max="100" class="form-control" onkeyup="formatRupiah(this)">
                    </td>
                    <td>
                        <select class="form-control" id="arr_currency` + randomString + `" name="arr_currency[]">` + optioncurrency + `</select>
                    </td>
                    <td class="center">
                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-credit" href="javascript:void(0);">
                            <i class="material-icons">delete</i>
                        </a>
                    </td>
                </tr>
            `);
            select2ServerSide('#arr_coa' + randomString, '{{ url("admin/select2/coa") }}');
            $('#arr_field' + randomString).formSelect();
            $('#arr_currency' + randomString).formSelect();
        }

        M.updateTextFields();
    }

    function save(){

        var passed = true;

        $('select[name^="arr_coa"]').each(function(){
            if(!$(this).val()){
                passed = false;
            }
        });
			
        if(passed == true){
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
        }else{
            M.toast({
                html: 'Cek coa, tidak boleh ada yang kosong, hapus jika tidak diinginkan.'
            });
        }
    }

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
    }
</script>