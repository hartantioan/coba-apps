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
    /* .select-wrapper {
        height: 60px !important;
    } */
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
                                                        <th rowspan="2">Pabrik/Kantor</th>
                                                        <th rowspan="2">Departemen</th>
                                                        <th rowspan="2">Supplier/Vendor</th>
                                                        <th colspan="3" class="center-align">Tanggal</th>
                                                        <th colspan="2" class="center-align">Mata Uang</th>
                                                        <th rowspan="2">Keterangan</th>
                                                        <th rowspan="2">Termin</th>
                                                        <th rowspan="2">Tipe Pembayaran</th>
                                                        <th rowspan="2">Rekening Penerima</th>
                                                        <th rowspan="2">Bank & No.Rek</th>
                                                        <th rowspan="2">Dokumen</th>
                                                        <th rowspan="2">Status</th>
                                                        <th rowspan="2">Action</th>
                                                    </tr>
                                                    <tr>
                                                        <th>Pengajuan</th>
                                                        <th>Kadaluwarsa</th>
                                                        <th>Pemakaian</th>
                                                        <th>Kode</th>
                                                        <th>Konversi</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="min-width:100%;max-height: 100% !important;height: 100% !important;">
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
                            <div class="input-field col m3 s12">
                                <input type="hidden" id="temp" name="temp">
                                <select class="browser-default" id="account_id" name="account_id" onchange="getAccountInfo();"></select>
                                <label class="active" for="account_id">Partner Bisnis</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="post_date" name="post_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);">
                                <label class="active" for="post_date">Tgl. Posting</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="due_date" name="due_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. posting">
                                <label class="active" for="due_date">Tgl. Kadaluwarsa</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="required_date" name="required_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. posting">
                                <label class="active" for="required_date">Tgl. Dipakai</label>
                            </div>
                            <div class="file-field input-field col m3 s12">
                                <div class="btn">
                                    <span>Lampiran</span>
                                    <input type="file" name="file" id="file" accept="image/*,.pdf">
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text">
                                </div>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="place_id" name="place_id">
                                    @foreach ($place as $rowplace)
                                        <option value="{{ $rowplace->id }}">{{ $rowplace->name.' - '.$rowplace->company->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="place_id">Pabrik/Kantor</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="department_id" name="department_id">
                                    @foreach ($department as $row)
                                        <option value="{{ $row->id }}" {{ $row->id == session('bo_department_id') ? 'selected' : '' }}>{{ $row->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="department_id">Departemen</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <textarea id="note" name="note" class="materialize-textarea" placeholder="Ulasan singkat produk..."></textarea>
                                <label class="active" for="note">Keterangan</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <textarea id="termin_note" name="termin_note" class="materialize-textarea" placeholder="Informasi termin pembayaran..."></textarea>
                                <label class="active" for="termin_note">Termin</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="payment_type" name="payment_type">
                                    <option value="1">Tunai</option>
                                    <option value="2">Transfer</option>
                                    <option value="3">Cek</option>
                                    <option value="4">BG</option>
                                </select>
                                <label class="" for="payment_type">Tipe Pembayaran</label>
                            </div>
                            <div class="col m12">
                                <h6>Rekening (Jika transfer)</h6>
                                <div class="input-field col m3 s12">
                                    <select class="form-control" id="user_bank_id" name="user_bank_id" onchange="getRekening()">
                                        <option value="">--Pilih Partner Bisnis-</option>
                                    </select>
                                    <label class="" for="user_bank_id">Pilih Dari Daftar</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <input id="name_account" name="name_account" type="text" placeholder="Rekening atas nama">
                                    <label class="active" for="name_account">Rekening Penerima</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <input id="no_account" name="no_account" type="text" placeholder="Rekening atas nama">
                                    <label class="active" for="no_account">Bank & No. Rek. Penerima</label>
                                </div>
                            </div>
                            <div class="col m12">
                                <h6>Mata Uang</h6>
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
                            </div>
                            <div class="col m12 s12">
                                <p class="mt-2 mb-2">
                                    <h4>Detail Produk</h4>
                                    <table class="bordered">
                                        <thead>
                                            <tr>
                                                <th class="center">Uraian Barang</th>
                                                <th class="center">Qty</th>
                                                <th class="center">Satuan</th>
                                                <th class="center">Harga Satuan</th>
                                                <th class="center">Harga Total</th>
                                                <th class="center">Hapus</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-item">
                                            <tr id="last-row-item">
                                                <td colspan="6" class="center">
                                                    <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addItem()" href="javascript:void(0);">
                                                        <i class="material-icons left">add</i> Tambah Detail
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </p>
                            </div>
                            <div class="input-field col m4 s12">

                            </div>
                            <div class="input-field col m4 s12">

                            </div>
                            <div class="input-field col m4 s12">
                                <table width="100%" class="bordered">
                                    <thead>
                                        <tr>
                                            <td>Total</td>
                                            <td class="right-align"><span id="total">0,000</span></td>
                                        </tr>
                                        <tr>
                                            <td>PPN</td>
                                            <td class="right-align">
                                                <input id="tax" name="tax" type="text" value="0,000" onkeyup="formatRupiah(this);count();" style="text-align:right;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>PPH</td>
                                            <td class="right-align">
                                                <input id="wtax" name="wtax" type="text" value="0,000" onkeyup="formatRupiah(this);count();" style="text-align:right;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Grandtotal</td>
                                            <td class="right-align"><span id="grandtotal">0,000</span></td>
                                        </tr>
                                    </thead>
                                </table>
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
                $('#required_date').attr('min','{{ date("Y-m-d") }}');
            },
            onOpenEnd: function(modal, trigger) {
                $('#name').focus();
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
                window.onbeforeunload = function() {
                    if($('.data-used').length > 0){
                        $('.data-used').trigger('click');
                    }
                    return 'You will lose all changes made since your last save';
                };
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                M.updateTextFields();
                $('#project_id,#warehouse_id').empty();
                $('.row_item').remove();
                $('#user_bank_id').empty().append(`
                    <option value="">--Pilih Partner Bisnis-</option>
                `);
                window.onbeforeunload = function() {
                    return null;
                };
            }
        });

        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
        });

        select2ServerSide('#account_id', '{{ url("admin/select2/business_partner") }}');
    });

    function getRekening(){
        if($('#user_bank_id').val()){
            $('#name_account').val($('#user_bank_id').find(':selected').data('name'));
            $('#no_account').val($('#user_bank_id').find(':selected').data('bankno'));
        }else{
            $('#name_account,#no_account').val('');
        }
    }

    function count(){
        let totalall = 0, grandtotal = 0, tax = parseFloat($('#tax').val().replaceAll(".", "").replaceAll(",",".")), wtax = parseFloat($('#wtax').val().replaceAll(".", "").replaceAll(",","."));
        $('input[name^="arr_qty"]').each(function(index){
            let qty = parseFloat($(this).val().replaceAll(".", "").replaceAll(",",".")), price = parseFloat($('input[name^="arr_price"]').eq(index).val().replaceAll(".", "").replaceAll(",","."));
            let total = qty * price;
            $('input[name^="arr_total"]').eq(index).val(formatRupiahIni(total.toFixed(3).toString().replace('.',',')));
            totalall += total;
        });
        grandtotal = totalall + tax - wtax;
        $('#total').text(formatRupiahIni(totalall.toFixed(3).toString().replace('.',',')));
        $('#grandtotal').text(formatRupiahIni(grandtotal.toFixed(3).toString().replace('.',',')));
    }

    function addItem(){
        var count = makeid(10);
        $('#last-row-item').before(`
            <tr class="row_item">
                <td>
                    <input name="arr_item[]" type="text" placeholder="Keterangan Barang">
                </td>
                <td>
                    <input name="arr_qty[]" type="text" value="0" onkeyup="formatRupiah(this);count();">
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_unit` + count + `" name="arr_unit[]"></select>
                </td>>
                <td class="center">
                    <input type="text" id="arr_price` + count + `" name="arr_price[]" value="0,000" onkeyup="formatRupiah(this);count();" style="text-align:right;">
                </td>
                <td class="center">
                    <input type="text" id="arr_total` + count + `" name="arr_total[]" value="0,000" onkeyup="formatRupiah(this);" readonly style="text-align:right;">
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        select2ServerSide('#arr_unit' + count, '{{ url("admin/select2/unit") }}');
    }

    function changeDateMinimum(val){
        if(val){
            $('#due_date,#required_date').attr("min",val);
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
                { name: 'place_id', className: 'center-align' },
                { name: 'department_id', className: 'center-align' },
                { name: 'account_id', className: 'center-align' },
                { name: 'date_post', className: 'center-align' },
                { name: 'date_due', className: 'center-align' },
                { name: 'date_use', className: 'center-align' },
                { name: 'currency_id', className: 'center-align' },
                { name: 'currency_rate', className: 'center-align' },
                { name: 'note', className: 'center-align' },
                { name: 'termin_note', className: 'center-align' },
                { name: 'payment_type', className: 'center-align' },
                { name: 'name_account', className: 'center-align' },
                { name: 'no_account', className: 'center-align' },
                { name: 'document', searchable: false, orderable: false, className: 'center-align' },
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

    function getAccountInfo(){
        if($('#account_id').val()){
            $.ajax({
                url: '{{ Request::url() }}/get_account_info',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: $('#account_id').val()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    loadingClose('.modal-content');
                    $('#user_bank_id').empty();
                    if(response.banks.length > 0){
                        $('#user_bank_id').append(`
                            <option value="">--Pilih dari daftar-</option>
                        `);
                        $.each(response.banks, function(i, val) {
                            $('#user_bank_id').append(`
                                <option value="` + val.bank_id + `" data-name="` + val.name + `" data-bankno="` + val.bank_name + ` - ` + val.no + `">` + val.bank_name + ` - ` + val.no + ` - ` + val.name + `</option>
                            `);
                        });                        
                    }else{
                        $('#user_bank_id').append(`
                            <option value="">--Pilih Partner Bisnis-</option>
                        `);
                    }
                    $('#user_bank_id').formSelect();
                    $('.modal-content').scrollTop(0);
                    M.updateTextFields();
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
            $('.row_item').remove();
            $('#user_bank_id').empty().append(`
                <option value="">--Pilih Partner Bisnis-</option>
            `);
        }
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
                $('#required_date').val(response.required_date);
                $('#post_date').removeAttr('min');
                $('#due_date').removeAttr('min');
                $('#required_date').removeAttr('min');
                $('#place_id').val(response.place_id).formSelect();

                if(response.project_id){
                    $('#project_id').empty();
                    $('#project_id').append(`
                        <option value="` + response.project_id + `">` + response.project_name + `</option>
                    `);
                }

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
                                <td>
                                    <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]"></select>
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
                        $('#arr_warehouse' + count).append(`
                            <option value="` + val.warehouse_id + `">` + val.warehouse_name + `</option>
                        `);
                        select2ServerSide('#arr_warehouse' + count, '{{ url("admin/select2/warehouse") }}');
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