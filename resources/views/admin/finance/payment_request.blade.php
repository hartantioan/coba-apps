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
                                                <label for="filter_place" style="font-size:1rem;">Pabrik :</label>
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
                                                <label for="filter_account" style="font-size:1rem;">Partner Bisnis :</label>
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
                                    </div>
                                </li>
                            </ul>
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">
                                        List Data
                                        <button class="btn waves-effect waves-light mr-1 float-right btn-small" onclick="loadDataTable()">
                                            Refresh
                                            <i class="material-icons left">refresh</i>
                                        </button>
                                    </h4>
                                    <div class="row">
                                        <div class="col s12">
                                            <div id="datatable_buttons"></div>
                                            <table id="datatable_serverside">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="2">#</th>
                                                        <th rowspan="2">Code</th>
                                                        <th rowspan="2">Pengguna</th>
                                                        <th rowspan="2">Partner Bisnis</th>
                                                        <th rowspan="2">Pabrik/Kantor</th>
                                                        <th rowspan="2">Kas/Bank</th>
                                                        <th colspan="3" class="center-align">Tanggal</th>
                                                        <th colspan="2" class="center-align">Mata Uang</th>
                                                        <th rowspan="2">Admin</th>
                                                        <th rowspan="2">Bayar</th>
                                                        <th rowspan="2">Dokumen</th>
                                                        <th rowspan="2">Bank Rekening</th>
                                                        <th rowspan="2">No Rekening</th>
                                                        <th rowspan="2">Pemilik Rekening</th>
                                                        <th rowspan="2">Keterangan</th>
                                                        <th rowspan="2">Status</th>
                                                        <th rowspan="2">Action</th>
                                                        <th rowspan="2">Kas/Bank Keluar</th>
                                                    </tr>
                                                    <tr>
                                                        <th>Post</th>
                                                        <th>Tenggat</th>
                                                        <th>Bayar</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
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
                                <select class="form-control" id="place_id" name="place_id">
                                    <option value="">--Kosong--</option>
                                    @foreach ($place as $rowplace)
                                        <option value="{{ $rowplace->id }}">{{ $rowplace->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="place_id">Pabrik/Kantor</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="browser-default" id="coa_source_id" name="coa_source_id"></select>
                                <label class="active" for="account_id">Kas / Bank</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="post_date" name="post_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                <label class="active" for="post_date">Tgl. Posting</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="top" name="top" min="0" type="number" value="0" readonly>
                                <label class="active" for="top">TOP (hari) Autofill</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="due_date" name="due_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. Kadaluarsa">
                                <label class="active" for="due_date">Tgl. Kadaluarsa</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="pay_date" name="pay_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. bayar">
                                <label class="active" for="pay_date">Tgl. Bayar</label>
                            </div>
                            <div class="file-field input-field col m3 s12">
                                <div class="btn">
                                    <span>Lampiran</span>
                                    <input type="file" name="document" id="document">
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text">
                                </div>
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
                            <div class="col m12">
                                <h6>Rekening (Jika transfer)</h6>
                                <div class="input-field col m3 s12">
                                    <select class="form-control" id="user_bank_id" name="user_bank_id" onchange="getRekening()">
                                        <option value="">--Pilih Partner Bisnis-</option>
                                    </select>
                                    <label class="" for="user_bank_id">Pilih Dari Daftar</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <input id="account_bank" name="account_bank" type="text" placeholder="Bank Tujuan">
                                    <label class="active" for="account_bank">Bank Tujuan</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <input id="account_no" name="account_no" type="text" placeholder="No Rekening Tujuan">
                                    <label class="active" for="account_no">No Rekening</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <input id="account_name" name="account_name" type="text" placeholder="Nama Pemilik Rekening">
                                    <label class="active" for="account_name">Nama Pemilik Rekening</label>
                                </div>
                            </div>
                            <div class="col m12 s12">
                                <p class="mt-2 mb-2">
                                    <h6>Detail Req. Dana / Uang Muka Pembelian / Invoice Pembelian</h6>
                                    <div style="overflow:auto;">
                                        <table class="bordered" style="max-width:1650px !important;">
                                            <thead>
                                                <tr>
                                                    <th class="center" width="10%">
                                                        <label>
                                                            <input type="checkbox" onclick="chooseAll(this)">
                                                            <span>Semua</span>
                                                        </label>
                                                    </th>
                                                    <th class="center">Referensi</th>
                                                    <th class="center">Tgl.Post</th>
                                                    <th class="center">Tgl.Tenggat</th>
                                                    <th class="center">Total</th>
                                                    <th class="center">PPN</th>
                                                    <th class="center">PPH</th>
                                                    <th class="center">Grandtotal</th>
                                                    <th class="center">Bayar</th>
                                                    <th class="center">Keterangan</th>
                                                    <th class="center">Coa</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-detail">
                                                <tr id="empty-detail">
                                                    <td colspan="10" class="center">
                                                        Pilih supplier/vendor untuk memulai...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </p>
                            </div>
                            <div class="input-field col m4 s12">
                                <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note">Keterangan</label>
                            </div>
                            <div class="input-field col m4 s12">
                                <h6><b>Data Terpakai</b> : <i id="list-used-data"></i></h6>
                            </div>
                            <div class="input-field col m4 s12">
                                <table width="100%" class="bordered">
                                    <thead>
                                        <tr>
                                            <td>Biaya Admin</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="admin" name="admin" type="text" value="0,000" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100%;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Total Bayar</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="grandtotal" name="grandtotal" type="text" value="0,000" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100%;">
                                            </td>
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

<div id="modal3" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Tambah Kas / Bank Out</h4>
                <form class="row" id="form_data_pay" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert_pay" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="row">
                            <div class="input-field col m3 s12">
                                <input type="hidden" id="tempPay" name="tempPay">
                                <input id="pay_date_pay" name="pay_date_pay" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. bayar">
                                <label class="active" for="pay_date_pay">Tgl. Bayar</label>
                            </div>
                            <div class="file-field input-field col m3 s12">
                                <div class="btn">
                                    <span>Lampiran</span>
                                    <input type="file" name="documentPay" id="documentPay">
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text">
                                </div>
                            </div>
                            <div class="input-field col m3 s12">
                                <textarea class="materialize-textarea" id="notePay" name="notePay" placeholder="Catatan / Keterangan" rows="1"></textarea>
                                <label class="active" for="notePay">Keterangan</label>
                            </div>
                            <div class="input-field col m12 s12">
                                <h6><b>Data Terpakai</b> : <i id="list-used-data-pay"></i></h6>
                            </div>
                            <div class="col s12 mt-3">
                                <button class="btn waves-effect waves-light right submit" onclick="savePay();">Simpan <i class="material-icons right">send</i></button>
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
                $('#post_date').attr('min','{{ date("Y-m-d") }}');
                $('#due_date').attr('min','{{ date("Y-m-d") }}');
                $('#document_date').attr('min','{{ date("Y-m-d") }}');
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
                $('.row_purchase').each(function(){
                    $(this).remove();
                });
                M.updateTextFields();
                $('#body-detail').empty().append(`
                    <tr id="empty-detail">
                        <td colspan="10" class="center">
                            Pilih supplier/vendor untuk memulai...
                        </td>
                    </tr>
                `);
                $('#account_id').empty();
                $('#total,#tax,#wtax,#grandtotal,#balance').text('0,000');
                $('#subtotal,#discount,#downpayment').val('0,000');
                $('#list-used-data').empty();
                window.onbeforeunload = function() {
                    return null;
                };
            }
        });

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

        $('#modal3').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) {
                window.onbeforeunload = function() {
                    if($('.data-used-pay').length > 0){
                        $('.data-used-pay').trigger('click');
                    }
                    return 'You will lose all changes made since your last save';
                };
            },
            onCloseEnd: function(modal, trigger){
                if($('.data-used-pay').length > 0){
                    $('.data-used-pay').trigger('click');
                }
                $('#tempPay,#pay_date_pay').val('');
                window.onbeforeunload = function() {
                    return null;
                };
            }
        });

        select2ServerSide('#account_id,#filter_account', '{{ url("admin/select2/business_partner") }}');
        select2ServerSide('#coa_source_id', '{{ url("admin/select2/coa_cash_bank") }}');
    });

    function getRekening(){
        if($('#user_bank_id').val()){
            $('#account_bank').val($('#user_bank_id').find(':selected').data('bank'));
            $('#account_no').val($('#user_bank_id').find(':selected').data('no'));
            $('#account_name').val($('#user_bank_id').find(':selected').data('name'));
        }else{
            $('#account_bank,#account_no,#account_name').val('');
        }
    }

    function getAccountInfo(){
        if($('#account_id').val()){
            $.ajax({
                url: '{{ Request::url() }}/get_account_data',
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

                    $('#body-detail').empty();
                    if(response.details.length > 0){
                        $.each(response.details, function(i, val) {
                            var count = makeid(10);
                            $('#list-used-data').append(`
                                <div class="chip purple darken-4 gradient-shadow white-text">
                                    ` + val.rawcode + `
                                    <i class="material-icons close data-used" onclick="removeUsedData('` + val.type + `',` + val.id + `)">close</i>
                                </div>
                            `);
                            $('#body-detail').append(`
                                <tr class="row_detail">
                                    <input type="hidden" name="arr_type[]" value="` + val.type + `" data-id="` + count + `">
                                    <td class="center-align">
                                        <label>
                                            <input type="checkbox" id="check` + count + `" name="arr_code[]" value="` + val.code + `" onclick="countAll();" data-id="` + count + `">
                                            <span>Pilih</span>
                                        </label>
                                    </td>
                                    <td>
                                        ` + val.rawcode + `
                                    </td>
                                    <td class="center">
                                        ` + val.post_date + `
                                    </td>
                                    <td class="center">
                                        ` + val.due_date + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.total + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.tax + `
                                    </td>
                                    <td class="right-align" id="row_wtax` + count + `">
                                        ` + val.wtax + `
                                    </td>
                                    <td class="right-align" id="row_grandtotal` + count + `">
                                        ` + val.grandtotal + `
                                    </td>
                                    <td class="center">
                                        <input id="arr_pay` + count + `" name="arr_pay[]" class="browser-default" type="text" value=" `+ val.balance + `" onkeyup="formatRupiah(this);countAll();" style="width:150px;text-align:right;">
                                    </td>
                                    <td class="center">
                                        <input id="arr_note` + count + `" name="arr_note[]" class="browser-default" type="text" style="width:150px;">
                                    </td>
                                    <td class="center">
                                        <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]" required style="width: 100%"></select>
                                    </td>
                                </tr>
                            `);

                            select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
                            if(val.coa_id){
                                $('#arr_coa' + count).append(`
                                    <option value="` + val.coa_id + `">` + val.coa_name + `</option>
                                `);
                            }

                            $('#place_id').val(val.place_id).formSelect();
                        });                        
                    }else{
                        $('#body-detail').empty().append(`
                            <tr id="empty-detail">
                                <td colspan="10" class="center">
                                    Pilih supplier/vendor untuk memulai...
                                </td>
                            </tr>
                        `);

                        $('#grandtotal,#admin').val('0,000');
                    }
                    
                    $('#user_bank_id').empty();
                    if(response.banks.length > 0){
                        $('#user_bank_id').append(`
                            <option value="">--Pilih dari daftar-</option>
                        `);
                        $.each(response.banks, function(i, val) {
                            $('#user_bank_id').append(`
                                <option value="` + val.bank_id + `" data-name="` + val.name + `" data-bank="` + val.bank_name + `" data-no="` + val.no + `">` + val.bank_name + ` - ` + val.no + ` - ` + val.name + `</option>
                            `);
                        });                        
                    }else{
                        $('#user_bank_id').append(`
                            <option value="">--Pilih Partner Bisnis-</option>
                        `);
                    }
                    $('#user_bank_id').formSelect();

                    $('#top').val(response.top);

                    addDays();
                    
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
            $('#body-detail').empty().append(`
                <tr id="empty-detail">
                    <td colspan="10" class="center">
                        Pilih supplier/vendor untuk memulai...
                    </td>
                </tr>
            `);
            $('#deposit').val('0,000');
            $('#top').val('0');
            $('#due_date').val('');
            $('#total,#tax,#wtax,#grandtotal,#balance').text('0,000');
        }
    }

    function countAll(){
        var pay = 0, admin = parseFloat($('#admin').val().replaceAll(".", "").replaceAll(",","."));
        
        if($('input[name^="arr_code"]').length > 0){
            $('input[name^="arr_code"]').each(function(){
                let element = $(this);
                if($(element).is(':checked')){
                    pay += parseFloat($('#arr_pay' + element.data('id')).val().replaceAll(".", "").replaceAll(",","."));
                }
            });
        }
        pay += admin;
        $('#grandtotal').val(formatRupiahIni(pay.toFixed(3).toString().replace('.',',')));
    }

    function chooseAll(element){
        if($(element).is(':checked')){
            $('input[name^="arr_code"]').each(function(){
                if(!$(this).is(':checked')){
                    $(this).prop( "checked", true);
                }
            });
        }else{
            $('input[name^="arr_code"]').each(function(){
                if($(this).is(':checked')){
                    $(this).prop( "checked", false);
                }
            });
        }
        countAll();
    }

    function removeUsedData(table,id){
        $.ajax({
            url: '{{ Request::url() }}/remove_used_data',
            type: 'POST',
            dataType: 'JSON',
            data: { 
                id : id,
                table : table
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                
            },
            success: function(response) {
                $('.row_item[data-id="' + id + '"]').remove();
                countAll();
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
                { name: 'user_id', className: 'center-align' },
                { name: 'account_id', className: 'center-align' },
                { name: 'place_id', className: 'center-align' },
                { name: 'coa_source_id', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'due_date', className: 'center-align' },
                { name: 'pay_date', className: 'center-align' },
                { name: 'currency_id', className: 'center-align' },
                { name: 'currency_rate', className: 'center-align' },
                { name: 'admin', className: 'right-align' },
                { name: 'grandtotal', className: 'right-align' },
                { name: 'document', className: 'center-align' },
                { name: 'account_bank', className: 'right-align' },
                { name: 'account_no', className: 'right-align' },
                { name: 'account_name', className: 'right-align' },
                { name: 'note', className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
                { name: 'cash_bank_out', searchable: false, orderable: false, className: 'center-align' },
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

                formData.delete("arr_code[]");
                formData.delete("arr_type[]");
                formData.delete("arr_pay[]");
                formData.delete("arr_note[]");
                formData.delete("arr_coa[]");

                $('input[name^="arr_code"]').each(function(){
                    if($(this).is(':checked')){
                        formData.append('arr_code[]',$(this).val());
                        formData.append('arr_type[]',$('input[name^="arr_type"][data-id="' + $(this).data('id') + '"]').val());
                        formData.append('arr_pay[]',$('#arr_pay' + $(this).data('id')).val());
                        formData.append('arr_note[]',$('#arr_note' + $(this).data('id')).val());
                        formData.append('arr_coa[]',$('#arr_coa' + $(this).data('id')).val());
                    }
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
                $('#account_id').empty().append(`
                    <option value="` + response.account_id + `">` + response.account_name + `</option>
                `);
                $('#coa_source_id').empty().append(`
                    <option value="` + response.coa_source_id + `">` + response.coa_source_name + `</option>
                `);
                $('#place_id').val(response.place_id).formSelect();
                $('#currency_id').val(response.currency_id).formSelect();
                $('#currency_rate').val(response.currency_rate);
                $('#post_date').val(response.post_date);
                $('#due_date').val(response.due_date);
                $('#pay_date').val(response.pay_date);                
                $('#note').val(response.note);
                $('#account_bank').val(response.account_bank);
                $('#account_no').val(response.account_no);
                $('#account_name').val(response.account_name);
                $('#admin').val(response.admin);
                $('#grandtotal').val(response.grandtotal);
                
                if(response.details.length > 0){
                    $('#body-detail').empty();
                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        $('#body-detail').append(`
                            <tr class="row_detail">
                                <input type="hidden" name="arr_type[]" value="` + val.type + `" data-id="` + count + `">
                                <td class="center-align">
                                    <label>
                                        <input type="checkbox" id="check` + count + `" name="arr_code[]" value="` + val.code + `" onclick="countAll();" data-id="` + count + `" checked>
                                        <span>Pilih</span>
                                    </label>
                                </td>
                                <td>
                                    ` + val.rawcode + `
                                </td>
                                <td class="center">
                                    ` + val.post_date + `
                                </td>
                                <td class="center">
                                    ` + val.due_date + `
                                </td>
                                <td class="right-align">
                                    ` + val.total + `
                                </td>
                                <td class="right-align">
                                    ` + val.tax + `
                                </td>
                                <td class="right-align" id="row_wtax` + count + `">
                                    ` + val.wtax + `
                                </td>
                                <td class="right-align" id="row_grandtotal` + count + `">
                                    ` + val.grandtotal + `
                                </td>
                                <td class="center">
                                    <input id="arr_pay` + count + `" name="arr_pay[]" class="browser-default" type="text" value=" `+ val.nominal + `" onkeyup="formatRupiah(this);countAll();" style="width:150px;text-align:right;">
                                </td>
                                <td class="center">
                                    <input id="arr_note` + count + `" name="arr_note[]" class="browser-default" type="text" style="width:150px;" value="` + val.note + `">
                                </td>
                                <td class="center">
                                    <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]" required style="width: 100%"></select>
                                </td>
                            </tr>
                        `);

                        select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
                        $('#arr_coa' + count).append(`
                            <option value="` + val.coa_id + `">` + val.coa_name + `</option>
                        `);
                    });
                }

                $('#user_bank_id').empty();
                if(response.banks.length > 0){
                    $('#user_bank_id').append(`
                        <option value="">--Pilih dari daftar-</option>
                    `);
                    $.each(response.banks, function(i, val) {
                        $('#user_bank_id').append(`
                            <option value="` + val.bank_id + `" data-name="` + val.name + `" data-bank="` + val.bank_name + `" data-no="` + val.no + `">` + val.bank_name + ` - ` + val.no + ` - ` + val.name + `</option>
                        `);
                    });                        
                }else{
                    $('#user_bank_id').append(`
                        <option value="">--Pilih Partner Bisnis-</option>
                    `);
                }
                $('#user_bank_id').formSelect();

                $('#top').val(response.top);

                $('.modal-content').scrollTop(0);
                $('#note').focus();
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

    function voidStatus(id){
        var msg = '';
        swal({
            title: "Alasan mengapa anda menutup!",
            text: "Anda tidak bisa mengembalikan data yang telah ditutup.",
            buttons: true,
            content: "input",
        })
        .then(message => {
            if (message != "" && message != null) {
                $.ajax({
                    url: '{{ Request::url() }}/void_status',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { id : id, msg : message },
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
        var search = window.table.search(), status = $('#filter_status').val(), place = $('#filter_place').val(), account = $('#filter_account').val(), currency = $('#filter_currency').val();
        
        $.ajax({
            type : "POST",
            url  : '{{ Request::url() }}/print',
            data : {
                search : search,
                status : status,
                place : place,
                'account[]' : account,
                'currency[]' : currency
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

    function addDays(){
        var result = new Date($('#post_date').val());
        result.setDate(result.getDate() + parseInt($('#top').val()));
        $('#due_date').val(result.toISOString().split('T')[0]);
    }

    function cashBankOut(code){
        $.ajax({
            url: '{{ Request::url() }}/get_payment_data',
            type: 'POST',
            dataType: 'JSON',
            data: {
                code: code
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('#main');
            },
            success: function(response) {
                loadingClose('#main');
                
                if(response.status == '200'){
                    $('#modal3').modal('open');
                    $('#tempPay').val(code);
                    $('#pay_date_pay').val(response.data.pay_date);
                    $('.modal-content').scrollTop(0);
                    M.updateTextFields();
                    $('#list-used-data-pay').append(`
                        <div class="chip purple darken-4 gradient-shadow white-text">
                            ` + response.data.code + `
                            <i class="material-icons close data-used-pay" onclick="removeUsedData('payment_requests',` + response.data.id + `)">close</i>
                        </div>
                    `);
                }else{
                    M.toast({
                        html: response.message
                    });
                }
                
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

    function savePay(){
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
                var formData = new FormData($('#form_data_pay')[0]);

                $.ajax({
                    url: '{{ Request::url() }}/create_pay',
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
                        $('#validation_alert_pay').hide();
                        $('#validation_alert_pay').html('');
                        loadingOpen('.modal-content');
                    },
                    success: function(response) {
                        loadingClose('.modal-content');

                        if(response.status == 200) {
                            successPay();
                            M.toast({
                                html: response.message
                            });
                        } else if(response.status == 422) {
                            $('#validation_alert_pay').show();
                            $('.modal-content').scrollTop(0);
                            
                            swal({
                                title: 'Ups! Validation',
                                text: 'Check your form.',
                                icon: 'warning'
                            });

                            $.each(response.error, function(i, val) {
                                $.each(val, function(i, val) {
                                    $('#validation_alert_pay').append(`
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

    function successPay(){
        loadDataTable();
        $('#modal3').modal('close');
    }
</script>