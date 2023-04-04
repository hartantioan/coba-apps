<style>
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
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="printData();">
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
                                            <div class="col m4 s6 ">
                                                <label for="filter_status" style="font-size:1rem;">Status :</label>
                                                <div class="input-field">
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
                                            <div class="col m4 s6 ">
                                                <label for="filter_place" style="font-size:1rem;">Pabrik/Kantor :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_place" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        @foreach ($place as $rowplace)
                                                            <option value="{{ $rowplace->id }}">{{ $rowplace->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_account" style="font-size:1rem;">Target BP :</label>
                                                <div class="input-field">
                                                    <select class="browser-default" id="filter_account" name="filter_account" multiple="multiple" style="width:100% !important;" onchange="loadDataTable()"></select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_currency" style="font-size:1rem;">Mata Uang :</label>
                                                <div class="input-field">
                                                    <select class="select2 browser-default" multiple="multiple" id="filter_currency" name="filter_currency" onchange="loadDataTable()">
                                                        <option value="" disabled>Semua</option>
                                                        @foreach ($currency as $row)
                                                            <option value="{{ $row->id }}">{{ $row->code }}</option>
                                                        @endforeach
                                                    </select>
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
                                                        <th>Kode</th>
                                                        <th>Pengguna</th>
                                                        <th>Target BP</th>
                                                        <th>Tanggal</th>
                                                        <th>Keterangan</th>
                                                        <th>Ref No.</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;min-width:100%;max-width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Add/Edit {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="input-field col m3 s12">
                            <select class="form-control" id="place_id" name="place_id">
                                @foreach ($place as $rowplace)
                                    <option value="{{ $rowplace->id }}" data-name="{{ $rowplace->name.' - '.$rowplace->company->name }}" data-address="{{ $rowplace->address }}">{{ $rowplace->name }}</option>
                                @endforeach
                            </select>
                            <label class="" for="place_id">Pabrik/Kantor</label>
                        </div>
                        <div class="input-field col m3 s12">
                            <input type="hidden" id="temp" name="temp">
                            <select class="browser-default" id="account_id" name="account_id"></select>
                            <label class="active" for="account_id">Target BP</label>
                        </div>
                        <div class="input-field col m3 s12">
                            <input id="note" name="note" type="text" placeholder="Keterangan">
                            <label class="active" for="note">Keterangan</label>
                        </div>
                        <div class="input-field col m3 s12">
                            <input id="post_date" name="post_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                            <label class="active" for="post_date">Tgl. Posting</label>
                        </div>
                        <div class="input-field col m3 s12">
                            <input id="due_date" name="due_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. Kadaluarsa">
                            <label class="active" for="due_date">Tgl. Kadaluarsa</label>
                        </div>
                        <div class="input-field col m3 s12">
                            <select class="form-control" id="currency_id" name="currency_id">
                                @foreach ($currency as $row)
                                    <option value="{{ $row->id }}">{{ $row->code.' '.$row->name }}</option>
                                @endforeach
                            </select>
                            <label class="" for="currency_id">Mata Uang</label>
                        </div>
                        <div class="input-field col m3 s12">
                            <input id="currency_rate" name="currency_rate" type="text" value="1" onkeyup="formatRupiah(this)">
                            <label class="active" for="currency_rate">Konversi</label>
                        </div>
                        <div class="col s12 mt-3">
                            <ul class="tabs tabs-fixed-width tab-demo z-depth-1">
                                <li class="tab col m3"><a class="active" href="#tabmaterial">Debit</a></li>
                                <li class="tab col m3"><a href="#tabcost">Kredit</a></li>
                             </ul>
                        </div>
                        <div class="col s12">
                            <div id="tabmaterial" class="col s12">
                                <p class="mt-2 mb-2">
                                    <table class="bordered">
                                        <thead>
                                            <tr>
                                                <th class="center">Coa</th>
                                                <th class="center">Pabrik/Kantor</th>
                                                <th class="center">Item</th>
                                                <th class="center">Departemen</th>
                                                <th class="center">Gudang</th>
                                                <th class="center">Nominal</th>
                                                <th class="center">Hapus</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-debit">
                                            <tr id="last-row-debit">
                                                <td colspan="7" class="center">
                                                    <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addCoa('1')" href="javascript:void(0);">
                                                        <i class="material-icons left">add</i> Tambah Debit
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </p>
                            </div>
                            <div id="tabcost" class="col s12">
                                <p class="mt-2 mb-2">
                                    <table class="bordered">
                                        <thead>
                                            <tr>
                                                <th class="center">Coa</th>
                                                <th class="center">Pabrik/Kantor</th>
                                                <th class="center">Item</th>
                                                <th class="center">Departemen</th>
                                                <th class="center">Gudang</th>
                                                <th class="center">Nominal</th>
                                                <th class="center">Hapus</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-credit">
                                            <tr id="last-row-credit">
                                                <td colspan="7" class="center">
                                                    <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addCoa('2')" href="javascript:void(0);">
                                                        <i class="material-icons left">add</i> Tambah Credit
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </p>
                            </div>
                        </div>
                        <div class="col s6 mt-1 center"><h5>Total Debit : <b id="totalDebit">0,000</b></h5></div>
                        <div class="col s6 mt-1 center"><h5>Total Credit : <b id="totalCredit">0,000</b></h5></div>
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

<div id="modal2" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="show_print">
                
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
    /* document.addEventListener('keydown', (e) => {
        e = e || window.event;
        if(e.keyCode == 116 || (e.ctrlKey && e.keyCode == 82)){
            e.preventDefault();
        }
    }); */

    $(function() {
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
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

        loadDataTable();
        
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
                $('ul.tabs').tabs();
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                M.updateTextFields();
                resetDetailForm();
                $('.row_coa').remove();
                countAll();
            }
        });

        select2ServerSide('#account_id,#filter_account', '{{ url("admin/select2/business_partner") }}');

        $('#modal2').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) {
                window.print();
            },
            onCloseEnd: function(modal, trigger){
                $('#show_print').html('');
            }
        });
        
        $("#item_id").on("select2:unselecting", function(e) {
            $('#code').val('');
            $('#name').val('');
        });

        $('#body-debit,#body-credit').on('click', '.delete-data-coa', function() {
            $(this).closest('tr').remove();
            countAll();
        });
    });

    function resetDetailForm(){
        $('.row_material').each(function(){
            $(this).remove();
        });

        $('.row_cost').each(function(){
            $(this).remove();
        });
    }

    function addCoa(type){
        var count = makeid(10);

        $('#last-row-' + (type == '1' ? 'debit' : 'credit')).before(`
            <tr class="row_coa">
                <input type="hidden" name="arr_type[]" value="` + type + `">
                <td>
                    <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]"></select>
                </td>
                <td>
                    <select class="form-control" id="arr_place` + count + `" name="arr_place[]">
                        @foreach ($place as $row)
                            <option value="{{ $row->id }}">{{ $row->name.' - '.$row->company->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select class="browser-default" id="arr_item` + count + `" name="arr_item[]"></select>
                </td>
                <td>
                    <select class="form-control" id="arr_department` + count + `" name="arr_department[]">
                        @foreach ($department as $row)
                            <option value="{{ $row->id }}">{{ $row->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]"></select>
                </td>
                <td>
                    <input name="arr_nominal[]" type="text" value="0" onkeyup="formatRupiah(this);countAll();">
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-coa" data-type="` + type + `" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        
        select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
        select2ServerSide('#arr_warehouse' + count, '{{ url("admin/select2/warehouse") }}');
        select2ServerSide('#arr_item' + count, '{{ url("admin/select2/item") }}');
        $('#arr_place' + count).formSelect();
        $('#arr_department' + count).formSelect();
    }

    function countAll(){
        let totalDebit = 0, totalCredit = 0;

        $('input[name^="arr_nominal"]').each(function(index){
            let nominal = parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
            if($('input[name^="arr_type"]').eq(index).val() == '1'){
                totalDebit += nominal;
            }else{
                totalCredit += nominal;
            }
        });

        $('#totalDebit').text(formatRupiahIni(totalDebit.toFixed(3).toString().replace('.',',')));
        $('#totalCredit').text(formatRupiahIni(totalCredit.toFixed(3).toString().replace('.',',')));
    }

    function getRowUnit(val){
        $('#arr_satuan' + val).text($("#arr_item" + val).select2('data')[0].uom);
    }

    function getCodeAndName(){
        $('#code').val($("#item_id").select2('data')[0].code);
        $('#name').val($("#item_id").select2('data')[0].name);
        $('.uom-unit').text($("#item_id").select2('data')[0].uom);
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
                    'account_id[]' : $('#filter_account').val(),
                    place_id : $('#filter_place').val(),
                    'currency_id[]' : $('#filter_currency').val(),
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
                { name: 'account', className: 'center-align' },
                { name: 'date', className: 'center-align' },
                { name: 'note', className: '' },
                { name: 'ref', searchable: false, orderable: false, className: 'center-align' },
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

                formData.delete("arr_type[]");
                formData.delete("arr_coa[]");
                formData.delete("arr_place[]");
                formData.delete("arr_item[]");
                formData.delete("arr_department[]");
                formData.delete("arr_warehouse[]");
                formData.delete("arr_nominal[]");

                $('input[name^="arr_type"]').each(function(index){
                    formData.append('arr_type[]',$(this).val());
                    formData.append('arr_coa[]',($('select[name^="arr_coa"]').eq(index).val() ? $('select[name^="arr_coa"]').eq(index).val() : 'NULL'));
                    formData.append('arr_place[]',$('select[name^="arr_place"]').eq(index).val());
                    formData.append('arr_item[]',($('select[name^="arr_item"]').eq(index).val() ? $('select[name^="arr_item"]').eq(index).val() : 'NULL'));
                    formData.append('arr_department[]',$('select[name^="arr_department"]').eq(index).val());
                    formData.append('arr_warehouse[]',($('select[name^="arr_warehouse"]').eq(index).val() ? $('select[name^="arr_warehouse"]').eq(index).val() : 'NULL'));
                    formData.append('arr_nominal[]',($('input[name^="arr_nominal"]').eq(index).val() ? $('input[name^="arr_nominal"]').eq(index).val() : 'NULL'));
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
                $('#place_id').val(response.place_id).formSelect();
                $('#account_id').empty().append(`
                    <option value="` + response.account_id + `">` + response.account_name + `</option>
                `);
                $('#note').val(response.note);
                $('#post_date').val(response.post_date);
                $('#due_date').val(response.due_date);
                $('#currency_id').val(response.currency_id).formSelect();
                $('#currency_rate').val(response.currency_rate);

                $('.row_coa').remove();

                $.each(response.details, function(i, val) {
                    let count = makeid(10);
                    $('#last-row-' + (val.type == '1' ? 'debit' : 'credit')).before(`
                        <tr class="row_coa">
                            <input type="hidden" name="arr_type[]" value="` + val.type + `">
                            <td>
                                <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]"></select>
                            </td>
                            <td>
                                <select class="form-control" id="arr_place` + count + `" name="arr_place[]">
                                    @foreach ($place as $row)
                                        <option value="{{ $row->id }}">{{ $row->name.' - '.$row->company->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select class="browser-default" id="arr_item` + count + `" name="arr_item[]"></select>
                            </td>
                            <td>
                                <select class="form-control" id="arr_department` + count + `" name="arr_department[]">
                                    @foreach ($department as $row)
                                        <option value="{{ $row->id }}">{{ $row->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]"></select>
                            </td>
                            <td>
                                <input name="arr_nominal[]" type="text" value="` + val.nominal + `" onkeyup="formatRupiah(this);countAll();">
                            </td>
                            <td class="center">
                                <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-coa" data-type="` + val.type + `" href="javascript:void(0);">
                                    <i class="material-icons">delete</i>
                                </a>
                            </td>
                        </tr>
                    `);
                    
                    select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
                    select2ServerSide('#arr_warehouse' + count, '{{ url("admin/select2/warehouse") }}');
                    select2ServerSide('#arr_item' + count, '{{ url("admin/select2/item") }}');
                    $('#arr_place' + count).formSelect().val(val.place_id).formSelect();
                    $('#arr_department' + count).formSelect().val(val.department_id).formSelect();
                    $('#arr_coa' + count).append(`
                        <option value="` + val.coa_id + `">` + val.coa_name + `</option>
                    `);
                    if(val.item_id){
                        $('#arr_item' + count).append(`
                            <option value="` + val.item_id + `">` + val.item_name + `</option>
                        `);
                    }
                    if(val.warehouse_id){
                        $('#arr_warehouse' + count).append(`
                            <option value="` + val.warehouse_id + `">` + val.warehouse_name + `</option>
                        `);
                    }
                });

                $('.modal-content').scrollTop(0);
                $('#code').focus();
                M.updateTextFields();
                countAll();
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

    function printData(){
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
        var search = window.table.search(), status = $('#filter_status').val(), place = $('#filter_place').val(), account = $('#filter_account').val(), currency = $('#filter_currency').val();
        
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status + "&place=" + place + "&account=" + account + "&currency=" + currency;
    }

    function printPreview(code){
        $.ajax({
            url: '{{ Request::url() }}/approval/' + code,
            type:'GET',
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            complete: function() {
                
            },
            success: function(data){
                loadingClose('.modal-content');
                $('#modal2').modal('open');
                $('#show_print').html(data);
            }
        });
    }
</script>