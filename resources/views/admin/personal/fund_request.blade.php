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
    .select-wrapper, .select2-container {
        height:3.6rem !important;
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
                                            <select class="form-control" id="filter_status" onchange="loadDataTable()" multiple>
                                                <option value="1">Menunggu</option>
                                                <option value="2">Dalam Proses</option>
                                                <option value="3">Selesai</option>
                                                <option value="4">Ditolak</option>
                                                <option value="5">Ditutup</option>
                                                <option value="6">Direvisi</option>
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
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">Refresh</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <table id="datatable_serverside" class="display responsive-table wrap">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="2">#</th>
                                                        <th rowspan="2">Code</th>
                                                        <th rowspan="2">Plant</th>
                                                        <th rowspan="2">Divisi</th>
                                                        <th rowspan="2">Partner Bisnis</th>
                                                        <th rowspan="2">Tipe</th>
                                                        <th colspan="2" class="center-align">Tanggal</th>
                                                        <th colspan="2" class="center-align">Mata Uang</th>
                                                        <th rowspan="2">Keterangan</th>
                                                        <th rowspan="2">Termin</th>
                                                        <th rowspan="2">Tipe Pembayaran</th>
                                                        <th rowspan="2">No.Dokumen</th>
                                                        <th rowspan="2">Tgl.Dokumen</th>
                                                        <th rowspan="2">No.Faktur Pajak</th>
                                                        <th rowspan="2">No.Bukti Potong</th>
                                                        <th rowspan="2">Tgl.Bukti Potong</th>
                                                        <th rowspan="2">No.SPK</th>
                                                        <th rowspan="2">No.Invoice</th>
                                                        <th rowspan="2">Reimburse</th>
                                                        <th rowspan="2">Rekening Penerima</th>
                                                        <th rowspan="2">No.Rek</th>
                                                        <th rowspan="2">Bank</th>
                                                        <th rowspan="2">Total</th>
                                                        <th rowspan="2">PPN</th>
                                                        <th rowspan="2">PPh</th>
                                                        <th rowspan="2">Grandtotal</th>
                                                        <th rowspan="2">Diterima</th>
                                                        <th rowspan="2">Dipakai</th>
                                                        <th rowspan="2">Sisa</th>
                                                        <th rowspan="2">Lampiran</th>
                                                        <th rowspan="2">Dokumen</th>
                                                        <th rowspan="2">Status</th>
                                                        <th rowspan="2">Action</th>
                                                    </tr>
                                                    <tr>
                                                        <th>Pengajuan</th>
                                                        <th>Request Pembayaran</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;">
    <div class="modal-content" style="overflow-x: hidden;max-width: 100%;">
        <div class="row">
            <div class="col s12">
                <h4>Tambah/Edit {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <fieldset>
                            <legend>1. Informasi Utama</legend>
                            <div class="row">
                                <div class="input-field col m2 s12">
                                    <input id="code" name="code" type="text" value="{{ $newcode }}" readonly>
                                    <label class="active" for="code">No. Dokumen</label>
                                </div>
                                <div class="input-field col m1 s12">
                                    <select class="form-control" id="code_place_id" name="code_place_id" onchange="getCode(this.value);">
                                        <option value="">--Pilih--</option>
                                        @foreach ($place as $rowplace)
                                            <option value="{{ $rowplace->code }}">{{ $rowplace->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="input-field col m3 s12">
                                    <select class="form-control" id="type" name="type" onchange="applyBpList();">
                                        <option value="1">Pembayaran</option>
                                        <option value="2">Pinjaman</option>
                                    </select>
                                    <label class="" for="type">Tipe Permohonan</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <input type="hidden" id="temp" name="temp">
                                    <input type="hidden" id="tempLimit" value="0">
                                    <select class="browser-default" id="account_id" name="account_id" onchange="getAccountInfo();"></select>
                                    <label class="active" for="account_id">Partner Bisnis</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);loadCurrency();">
                                    <label class="active" for="post_date">Tgl. Posting</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <input id="required_date" name="required_date" max="{{ date('Y-m-d', strtotime('12/31')) }}" type="date" placeholder="Tgl. posting">
                                    <label class="active" for="required_date">Tgl. Request Pembayaran</label>
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
                                            <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                                        @endforeach
                                    </select>
                                    <label class="" for="place_id">Plant</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <select class="form-control" id="division_id" name="division_id">
                                        @foreach ($division as $row)
                                            <option value="{{ $row->id }}" {{ $row->id == session('bo_division_id') ? 'selected' : '' }}>{{ $row->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="" for="division_id">Divisi</label>
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
                                        <option value="2">Transfer</option>
                                        <option value="1">Tunai</option>
                                        <option value="3">Cek</option>
                                        <option value="4">BG</option>
                                        <option value="5">Credit</option>
                                    </select>
                                    <label class="" for="payment_type">Tipe Pembayaran</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <input id="document_no" name="document_no" type="text" placeholder="No. Dokumen">
                                    <label class="active" for="document_no">No. Dokumen (Opsional)</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <input id="document_date" name="document_date" min="{{ date('Y-m-d') }}" type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. dokumen">
                                    <label class="active" for="document_date">Tgl. Dokumen (Opsional)</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <input id="tax_no" name="tax_no" type="text" placeholder="Nomor faktur pajak...">
                                    <label class="active" for="tax_no">No. Faktur Pajak (Opsional)</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <input id="tax_cut_no" name="tax_cut_no" type="text" placeholder="Nomor bukti potong...">
                                    <label class="active" for="tax_cut_no">No. Bukti Potong (Opsional)</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <input id="cut_date" name="cut_date" min="{{ date('Y-m-d') }}" type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. Bukti potong">
                                    <label class="active" for="cut_date">Tgl. Bukti Potong (Opsional)</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <input id="spk_no" name="spk_no" type="text" placeholder="Nomor SPK...">
                                    <label class="active" for="spk_no">No. SPK (Opsional)</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <input id="invoice_no" name="invoice_no" type="text" placeholder="Nomor Invoice dari Suppplier/Vendor">
                                    <label class="active" for="invoice_no">No. Invoice (Opsional)</label>
                                </div>
                                <div class="input-field col m3 s12 right-align">
                                    <h6>Limit BS : <b><span id="limit">0,00</span></b></h6>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col s12">
                        <fieldset>
                            <legend>2. Rekening & Mata Uang</legend>
                            <div class="row">
                                <div class="col m12">
                                    <div class="input-field col m3 s12">
                                        <select class="form-control" id="is_reimburse" name="is_reimburse" onchange="changeReimburse()">
                                            <option value="2">Tidak</option>
                                            <option value="1">Ya</option>
                                        </select>
                                        <label class="" for="is_reimburse">Apakah Reimburse?</label>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <select class="browser-default" id="user_bank_id" name="user_bank_id" onchange="getRekening()">
                                            <option value="">--Pilih Partner Bisnis-</option>
                                        </select>
                                        <label class="active" for="user_bank_id">Pilih Dari Daftar</label>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <input id="name_account" name="name_account" type="text" placeholder="Rekening atas nama">
                                        <label class="active" for="name_account">Rekening Penerima</label>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <input id="no_account" name="no_account" type="text" placeholder="Rekening atas nama">
                                        <label class="active" for="no_account">No. Rek. Penerima</label>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <input id="bank_account" name="bank_account" type="text" placeholder="Bank Tujuan">
                                        <label class="active" for="bank_account">Bank Tujuan</label>
                                    </div>
                                </div>
                                <div class="col m12">
                                    <h6>Mata Uang</h6>
                                    <div class="input-field col m3 s12">
                                        <select class="form-control" id="currency_id" name="currency_id" onchange="loadCurrency();">
                                            @foreach ($currency as $row)
                                                <option value="{{ $row->id }}" data-code="{{ $row->code }}">{{ $row->code.' '.$row->name }}</option>
                                            @endforeach
                                        </select>
                                        <label class="" for="currency_id">Mata Uang</label>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <input id="currency_rate" name="currency_rate" type="text" value="1" onkeyup="formatRupiah(this)">
                                        <label class="active" for="currency_rate">Konversi</label>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col s12">
                        <fieldset style="min-width: 100%;overflow:auto;">
                            <legend>3. Detail Dokumen</legend>
                            <div class="row">
                                <div class="col m12 s12">
                                    <p class="mt-2 mb-2">
                                        <h4>Detail Produk</h4>
                                        <table class="bordered" id="table-detail" style="width:2800px;font-size:0.9rem !important;">
                                            <thead>
                                                <tr>
                                                    <th class="center">Uraian Barang</th>
                                                    <th class="center">Qty</th>
                                                    <th class="center">Satuan</th>
                                                    <th class="center">Harga Satuan</th>
                                                    <th class="center">PPN</th>
                                                    <th class="center">Incl.PPN</th>
                                                    <th class="center">PPh</th>
                                                    <th class="center">Subtotal</th>
                                                    <th class="center">Plant</th>
                                                    <th class="center">Line</th>
                                                    <th class="center">Mesin</th>
                                                    <th class="center">Divisi</th>
                                                    <th class="center">Proyek</th>
                                                    <th class="center">Hapus</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-item">
                                                <tr id="last-row-item">
                                                    <td colspan="14">
                                                        <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addItem()" href="javascript:void(0);">
                                                            <i class="material-icons left">add</i> Tambah Detail
                                                        </a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </p>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col s12">
                        <fieldset>
                            <legend>4. Konfirmasi</legend>
                            <div class="row">
                                <div class="col m12 s12 row">
                                    <div class="input-field col m9 s12">
                                        <p class="mt-2 mb-2">
                                            <h6>Checklist Lampiran</h6>
                                            @foreach ($menu->checklistDocument as $row)
                                                <label style="margin: 0 5px 0 0;position:inherit !important;">
                                                    <input class="validate" required="" type="checkbox" name="arr_checklist_box[]" value="{{ $row->id }}">
                                                    <span>{{ $row->title.' '.$row->type() }}</span>
                                                    @if($row->is_other)
                                                        <input type="text" name="arr_checklist_note[]" style="width: 200px;height:1.5rem;">
                                                    @else
                                                        <input type="hidden" name="arr_checklist_note[]">
                                                    @endif
                                                </label>
                                                <br>
                                            @endforeach
                                        </p>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <table width="100%" class="bordered">
                                            <thead>
                                                <tr>
                                                    <td>Total</td>
                                                    <td class="right-align">
                                                        <input class="browser-default" id="total" name="total" onfocus="emptyThis(this);" type="text" value="0,00" onkeyup="formatRupiah(this);count();" style="text-align:right;" readonly>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>PPN</td>
                                                    <td class="right-align">
                                                        <input class="browser-default" id="tax" name="tax" onfocus="emptyThis(this);" type="text" value="0,00" onkeyup="formatRupiah(this);count();" style="text-align:right;" readonly>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>PPh</td>
                                                    <td class="right-align">
                                                        <input class="browser-default" id="wtax" name="wtax" onfocus="emptyThis(this);" type="text" value="0,00" onkeyup="formatRupiah(this);count();" style="text-align:right;" readonly>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Grandtotal</td>
                                                    <td class="right-align">
                                                        <input class="browser-default" id="grandtotal" name="grandtotal" onfocus="emptyThis(this);" type="text" value="0,00" onkeyup="formatRupiah(this);" style="text-align:right;" readonly>
                                                    </td>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                    <div class="col s12 mt-3">
                                        
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light right submit" onclick="save();">Simpan <i class="material-icons right">send</i></button>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat mr-2">Close</a>
    </div>
</div>

<div id="modal4" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="show_detail">

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

        $("#table-detail th").resizable({
            minWidth: 100,
        });

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
                $('#post_date').attr('min','{{ $minDate }}');
                $('#post_date').attr('max','{{ $maxDate }}');
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
                if(!$('#temp').val()){
                    loadCurrency();
                }
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('input').css('border', 'none');
                $('input').css('border-bottom', '0.5px solid black');
                $('#temp').val('');
                $('#tempLimit').val('0');
                $('#limit').text('0,00');
                M.updateTextFields();
                $('#project_id,#warehouse_id').empty();
                $('.row_item').remove();
                $('#user_bank_id').empty().append(`
                    <option value="">--Pilih Partner Bisnis-</option>
                `);
                window.onbeforeunload = function() {
                    return null;
                };
                applyBpList();
            }
        });

        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
            count();
        });

        $('#user_bank_id').select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/user_bank_by_account") }}',
                type: 'GET',
                dataType: 'JSON',
                data: function(params) {
                    return {
                        search: params.term,
                        account_id : $('#account_id').val()
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.items
                    }
                }
            }
        });

        applyBpList();
    });

    function changeReimburse(){
        $('#user_bank_id').empty();
        if($('#is_reimburse').val() == '1'){
            select2ServerSide('#user_bank_id', '{{ url("admin/select2/all_user_bank") }}');
        }else if($('#is_reimburse').val() == '2'){
            $('#user_bank_id').select2({
                placeholder: '-- Kosong --',
                minimumInputLength: 1,
                allowClear: true,
                cache: true,
                width: 'resolve',
                dropdownParent: $('body').parent(),
                ajax: {
                    url: '{{ url("admin/select2/user_bank_by_account") }}',
                    type: 'GET',
                    dataType: 'JSON',
                    data: function(params) {
                        return {
                            search: params.term,
                            account_id : $('#account_id').val()
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.items
                        }
                    }
                }
            });
        }
    }

    function applyBpList(){
        if($('#type').val() == '1'){
            select2ServerSide('#account_id', '{{ url("admin/select2/user") }}');
        }else if($('#type').val() == '2'){
            select2ServerSide('#account_id', '{{ url("admin/select2/employee") }}');
        }
        if($('#account_id').val()){
            $('#account_id').empty();
        }
    }

    var printService = new WebSocketPrinter({
        onConnect: function () {
        var document = $('#filter-document').val();
            
        },
        onDisconnect: function () {
           
        },
        onUpdate: function (message) {
            
        },
    });

    function getRekening(){
        if($('#user_bank_id').val()){
            $('#bank_account').val($('#user_bank_id').select2('data')[0].bank);
            $('#no_account').val($('#user_bank_id').select2('data')[0].no);
            $('#name_account').val($('#user_bank_id').select2('data')[0].name);
            $('#bank_account,#no_account,#name_account').prop('readonly',true);
        }else{
            $('#bank_account,#no_account,#name_account').val('');
            $('#bank_account,#no_account,#name_account').prop('readonly',false);
        }
    }

    function count(){
        let totalall = 0, grandtotalall = 0, taxall = 0, wtaxall = 0;
        $('input[name^="arr_qty"]').each(function(index){
            let row_percent_tax = 0, row_percent_wtax = 0, row_total = 0, row_tax = 0, row_wtax = 0, row_grandtotal = 0;
            let qty = parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
            let price = parseFloat($('input[name^="arr_price"]').eq(index).val().replaceAll(".", "").replaceAll(",","."));
            row_total = qty * price;
            row_percent_tax = $('select[name^="arr_tax_id"]').eq(index).find(':selected').data('value');
            row_percent_wtax = $('select[name^="arr_wtax_id"]').eq(index).find(':selected').data('value');
            if(row_percent_tax > 0){
                if($('select[name^="arr_is_include_tax"]').eq(index).val() == '1'){
                    row_total = row_total / (1 + (row_percent_tax / 100));
                }
                row_tax = Math.floor(row_total * (row_percent_tax / 100));
            }
            if(row_percent_wtax > 0){
                row_wtax = Math.floor(row_total * (row_percent_wtax / 100));
            }
            row_grandtotal = row_total + row_tax - row_wtax;
            $('input[name^="arr_percent_tax"]').eq(index).val(row_percent_tax);
            $('input[name^="arr_percent_wtax"]').eq(index).val(row_percent_wtax);
            $('input[name^="arr_tax"]').eq(index).val(row_tax);
            $('input[name^="arr_wtax"]').eq(index).val(row_wtax);
            $('input[name^="arr_total"]').eq(index).val(formatRupiahIni(row_total.toFixed(2).toString().replace('.',',')));
            $('input[name^="arr_grandtotal"]').eq(index).val(row_grandtotal);
            totalall += row_total;
            taxall += row_tax;
            wtaxall += row_wtax;
            grandtotalall += row_grandtotal;
        });
        $('#total').val(formatRupiahIni(totalall.toFixed(2).toString().replace('.',',')));
        $('#tax').val(formatRupiahIni(taxall.toString().replace('.',',')));
        $('#wtax').val(formatRupiahIni(wtaxall.toString().replace('.',',')));
        $('#grandtotal').val(formatRupiahIni(grandtotalall.toFixed(2).toString().replace('.',',')));
    }

    function addItem(){
        var count = makeid(10);
        $('#last-row-item').before(`
            <tr class="row_item">
                <input type="hidden" name="arr_percent_tax[]" value="0.00000" id="arr_percent_tax` + count + `">
                <input type="hidden" name="arr_percent_wtax[]" value="0.00000" id="arr_percent_wtax` + count + `">
                <input type="hidden" name="arr_tax[]" value="0" id="arr_tax` + count + `">
                <input type="hidden" name="arr_wtax[]" value="0" id="arr_wtax` + count + `">
                <input type="hidden" name="arr_grandtotal[]" value="0" id="arr_grandtotal` + count + `">
                <td>
                    <textarea class="materialize-textarea" name="arr_item[]" type="text" placeholder="Keterangan Barang"></textarea>
                </td>
                <td>
                    <input name="arr_qty[]" onfocus="emptyThis(this);" type="text" value="0" onkeyup="formatRupiahNoMinus(this);count();">
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_unit` + count + `" name="arr_unit[]"></select>
                </td>>
                <td class="center">
                    <input type="text" id="arr_price` + count + `" name="arr_price[]" onfocus="emptyThis(this);" value="0,00" onkeyup="formatRupiah(this);count();" style="text-align:right;">
                </td>
                <td>
                    <select class="browser-default" id="arr_tax_id` + count + `" name="arr_tax_id[]" onchange="count();">
                        <option value="0" data-value="0">-- Pilih ini jika non-PPN --</option>
                        @foreach ($tax as $row)
                            <option value="{{ $row->id }}" {{ $row->is_default_ppn ? 'selected' : '' }} data-value="{{ $row->percentage }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select class="browser-default" id="arr_is_include_tax` + count + `" name="arr_is_include_tax[]" onchange="count();">
                        <option value="0">--Tidak--</option>
                        <option value="1">--Ya--</option>
                    </select>
                </td>
                <td>
                    <select class="browser-default" id="arr_wtax_id` + count + `" name="arr_wtax_id[]" onchange="count();">
                        <option value="0" data-value="0">-- Pilih ini jika non-PPh --</option>
                        @foreach ($wtax as $row)
                        <option value="{{ $row->id }}" {{ $row->is_default_pph ? 'selected' : '' }} data-value="{{ $row->percentage }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="center">
                    <input type="text" id="arr_total` + count + `" name="arr_total[]" onfocus="emptyThis(this);" value="0,00" onkeyup="formatRupiah(this);" readonly style="text-align:right;">
                </td>
                <td>
                    <select class="browser-default" id="arr_place` + count + `" name="arr_place[]">
                        @foreach ($place as $rowplace)
                            <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                        @endforeach
                    </select>    
                </td>
                <td>
                    <select class="browser-default" id="arr_line` + count + `" name="arr_line[]" onchange="changePlace(this);">
                        <option value="">--Kosong--</option>
                        @foreach ($line as $rowline)
                            <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                        @endforeach
                    </select>    
                </td>
                <td>
                    <select class="browser-default" id="arr_machine` + count + `" name="arr_machine[]" onchange="changeLine(this);">
                        <option value="">--Kosong--</option>
                        @foreach ($machine as $row)
                            <option value="{{ $row->id }}" data-line="{{ $row->line_id }}">{{ $row->name }}</option>
                        @endforeach    
                    </select>
                </td>
                <td>
                    <select class="browser-default" id="arr_division` + count + `" name="arr_division[]">
                        <option value="">--Kosong--</option>
                        @foreach ($division as $rowdiv)
                            <option value="{{ $rowdiv->id }}">{{ $rowdiv->name }}</option>
                        @endforeach
                    </select>    
                </td>
                <td>
                    <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        select2ServerSide('#arr_unit' + count, '{{ url("admin/select2/unit") }}');
        select2ServerSide('#arr_project' + count, '{{ url("admin/select2/project") }}');
    }

    function changePlace(element){
        $(element).parent().next().find('select[name="arr_machine[]"] option').show();
        if($(element).val()){
            $(element).parent().prev().find('select[name="arr_place[]"]').val($(element).find(':selected').data('place'));
            $(element).parent().next().find('select[name="arr_machine[]"] option[data-line!="' + $(element).val() + '"]').hide();
        }else{
            $(element).parent().prev().find('select[name="arr_place[]"]').val($(element).parent().prev().find('select[name="arr_place[]"] option:first').val());
        }
    }

    function changeLine(element){
        if($(element).val()){
            $(element).parent().prev().find('select[name="arr_line[]"]').val($(element).find(':selected').data('line')).trigger('change');
        }else{
            $(element).parent().prev().find('select[name="arr_line[]"]').val($(element).parent().prev().find('select[name="arr_line[]"] option:first').val()).trigger('change');
        }
    }

    String.prototype.replaceAt = function(index, replacement) {
        return this.substring(0, index) + replacement + this.substring(index + replacement.length);
    };

    function getCode(val){
        if(val){
            if($('#temp').val()){
                let newcode = $('#code').val().replaceAt(7,val);
                $('#code').val(newcode);
            }else{
                if($('#code').val().length > 7){
                    $('#code').val($('#code').val().slice(0, 7));
                }
                $.ajax({
                    url: '{{ Request::url() }}/get_code',
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        val: $('#code').val() + val,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        loadingOpen('.modal-content');
                    },
                    success: function(response) {
                        loadingClose('.modal-content');
                        $('#code').val(response);
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
        }
    }

    function changeDateMinimum(val){
        if(val){
            $('#required_date').attr("min",val);
            $('input[name^="arr_required_date"]').each(function(){
                $(this).attr("min",val);
            });

            let newcode = $('#code').val().replaceAt(5,val.split('-')[0].toString().substr(-2));
            if($('#code').val().substring(5, 7) !== val.split('-')[0].toString().substr(-2)){
                if(newcode.length > 9){
                    newcode = newcode.substring(0, 9);
                }
            }
            $('#code').val(newcode);
            $('#code_place_id').trigger('change');
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
            "fixedColumns": {
                left: 2,
                right: 1
            },
            "order": [[0, 'desc']],
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    'status[]' : $('#filter_status').val(),
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
                { name: 'division_id', className: 'center-align' },
                { name: 'account_id', className: 'center-align' },
                { name: 'type', className: 'center-align' },
                { name: 'date_post', className: 'center-align' },
                { name: 'date_use', className: 'center-align' },
                { name: 'currency_id', className: 'center-align' },
                { name: 'currency_rate', className: 'center-align' },
                { name: 'note', className: '' },
                { name: 'termin_note', className: 'center-align' },
                { name: 'payment_type', className: 'center-align' },
                { name: 'document_no', className: '' },
                { name: 'document_date', className: '' },
                { name: 'tax_no', className: '' },
                { name: 'tax_cut_no', className: '' },
                { name: 'cut_date', className: '' },
                { name: 'spk_no', className: '' },
                { name: 'invoice_no', className: '' },
                { name: 'is_reimburse', className: '' },
                { name: 'name_account', className: 'center-align' },
                { name: 'no_account', className: 'center-align' },
                { name: 'bank_account', className: 'center-align' },
                { name: 'total', className: 'right-align' },
                { name: 'tax', className: 'right-align' },
                { name: 'wtax', className: 'right-align' },
                { name: 'grandtotal', className: 'right-align' },
                { name: 'received', searchable: false, orderable: false, className: 'right-align' },
                { name: 'used', searchable: false, orderable: false, className: 'right-align' },
                { name: 'balance', searchable: false, orderable: false, className: 'right-align' },
                { name: 'document', searchable: false, orderable: false, className: 'center-align' },
                { name: 'document_status', searchable: false, orderable: false, className: 'center-align' },
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

    function getAccountInfo(){
        if($('#account_id').val()){
            if((parseFloat($('#account_id').select2('data')[0].balance_limit) > 0 && $('#account_id').select2('data')[0].type == '1') || ['2','3','4'].includes($('#account_id').select2('data')[0].type)){
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
                        $('#tempLimit').val($('#account_id').select2('data')[0].balance_limit);
                        $('#limit').text(
                            formatRupiahIni($('#account_id').select2('data')[0].balance_limit.toString().replace('.',','))
                        );
                        if(response.banks.length > 0){
                            /* $('#user_bank_id').append(`
                                <option value="">--Pilih dari daftar-</option>
                            `);
                            $.each(response.banks, function(i, val) {
                                $('#user_bank_id').append(`
                                    <option value="` + val.id + `" data-name="` + val.name + `" data-bank="` + val.bank + `" data-no="` + val.no + `">` + val.bank + ` - ` + val.no + ` - ` + val.name + `</option>
                                `);
                            }); */                        
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
                $('#limit').text('0,00');
                $('#tempLimit').val('0');
                swal({
                    title: 'Ups! Sisa limit BS adalah ' + $('#account_id').select2('data')[0].balance_limit,
                    text: 'Maaf Partner Bisnis tidak bisa ditambahkan.',
                    icon: 'warning'
                });
                $('#account_id').empty();
                $('.row_item').remove();
                $('#user_bank_id').empty().append(`
                    <option value="">--Pilih Partner Bisnis-</option>
                `);
            }
        }else{
            $('#limit').text('0,00');
            $('#tempLimit').val('0');
            $('.row_item').remove();
            $('#user_bank_id').empty().append(`
                <option value="">--Pilih Partner Bisnis-</option>
            `);
        }
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
                
                var formData = new FormData($('#form_data')[0]), passedLimit = true, limit = parseFloat($('#tempLimit').val()), grandtotal = parseFloat($('#grandtotal').val().replaceAll(".", "").replaceAll(",","."));

                /* if(grandtotal > limit && $('#type').val() == '1'){
                    passedLimit = false;
                } */

                if(passedLimit){
                    var path = window.location.pathname;
                    path = path.replace(/^\/|\/$/g, '');

                    var segments = path.split('/');
                    var lastSegment = segments[segments.length - 1];
                
                    formData.append('lastsegment',lastSegment.toString().replace('personal_',''));
                    formData.delete("arr_project[]");
                    formData.delete("arr_checklist_box[]");
                    formData.delete("arr_checklist_note[]");

                    $('select[name^="arr_project[]"]').each(function(index){
                        formData.append('arr_project[]',($(this).val() ? $(this).val() : ''));
                    });

                    $('input[name^="arr_checklist_box[]"]').each(function(index){
                        if($(this).is(':checked')){
                            formData.append('arr_checklist_box[]',$(this).val());
                            formData.append('arr_checklist_note[]',$('input[name^="arr_checklist_note[]"]').eq(index).val());
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
                            $('input').css('border', 'none');
                            $('input').css('border-bottom', '0.5px solid black');
                            if(response.status == 200) {
                                success();
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
                }else{
                    swal({
                        title: 'Ups! Maaf.',
                        text: 'Nominal grandtotal melebihi batas nominal pengajuan BS.',
                        icon: 'warning'
                    });
                }
            }
        });
    }

    function finish(id){
		swal({
            title: "Apakah anda yakin ingin simpan?",
            text: "Status akan dirubah menjadi SELESAI!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                $.ajax({
                    url: '{{ Request::url() }}/finish',
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        code : id,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        loadingOpen('.container');
                    },
                    success: function(response) {
                        loadingClose('.container');
                        if(response.status == 200) {
                            loadDataTable();
                            M.toast({
                                html: response.message
                            });
                        } else {
                            M.toast({
                                html: response.message
                            });
                        }
                    },
                    error: function() {
                        $('.container').scrollTop(0);
                        loadingClose('.container');
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
                $('#code_place_id').val(response.code_place_id).attr('readonly',true).formSelect();
                $('#code').val(response.code);
                $('#note').val(response.note);
                $('#post_date').val(response.post_date);
                $('#required_date').val(response.required_date);
                $('#required_date').removeAttr('min');
                $('#place_id').val(response.place_id).formSelect();
                $('#division_id').val(response.division_id).formSelect();
                $('#termin_note').val(response.termin_note);
                $('#payment_type').val(response.payment_type).formSelect();
                $('#is_reimburse').val(response.is_reimburse).formSelect();
                $('#document_no').val(response.document_no);
                $('#document_date').val(response.document_date);
                $('#tax_no').val(response.tax_no);
                $('#tax_cut_no').val(response.tax_cut_no);
                $('#cut_date').val(response.cut_date);
                $('#spk_no').val(response.spk_no);
                $('#invoice_no').val(response.invoice_no);
                $('#name_account').val(response.name_account);
                $('#no_account').val(response.no_account);
                $('#bank_account').val(response.bank_account);
                $('#currency_id').val(response.currency_id).formSelect();
                $('#currency_rate').val(response.currency_rate);
                $('#total').val(response.total);
                $('#tax').val(response.tax);
                $('#wtax').val(response.wtax);
                $('#grandtotal').val(response.grandtotal);
                $('#account_id').empty().append(`
                    <option value="` + response.account_id + `">` + response.account_name + `</option>
                `);
                $('#limit').text(response.limit_credit);
                $('#tempLimit').val(response.limit_credit_raw);

                if(response.details.length > 0){
                    $('.row_item').each(function(){
                        $(this).remove();
                    });

                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        $('#last-row-item').before(`
                            <tr class="row_item">
                                <input type="hidden" name="arr_percent_tax[]" value="` + val.percent_tax + `" id="arr_percent_tax` + count + `">
                                <input type="hidden" name="arr_percent_wtax[]" value="` + val.percent_wtax + `" id="arr_percent_wtax` + count + `">
                                <input type="hidden" name="arr_tax[]" value="` + val.tax + `" id="arr_tax` + count + `">
                                <input type="hidden" name="arr_wtax[]" value="` + val.wtax + `" id="arr_wtax` + count + `">
                                <input type="hidden" name="arr_grandtotal[]" value="` + val.grandtotal + `" id="arr_grandtotal` + count + `">
                                <td>
                                    <textarea class="materialize-textarea" name="arr_item[]" type="text" placeholder="Keterangan Barang">` + val.item + `</textarea>
                                </td>
                                <td>
                                    <input name="arr_qty[]" onfocus="emptyThis(this);" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinus(this);count();">
                                </td>
                                <td class="center">
                                    <select class="browser-default" id="arr_unit` + count + `" name="arr_unit[]"></select>
                                </td>>
                                <td class="center">
                                    <input type="text" id="arr_price` + count + `" name="arr_price[]" onfocus="emptyThis(this);" value="` + val.price + `" onkeyup="formatRupiah(this);count();" style="text-align:right;">
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_tax_id` + count + `" name="arr_tax_id[]" onchange="count();">
                                        <option value="0" data-value="0">-- Pilih ini jika non-PPN --</option>
                                        @foreach ($tax as $row)
                                            <option value="{{ $row->id }}" {{ $row->is_default_ppn ? 'selected' : '' }} data-value="{{ $row->percentage }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_is_include_tax` + count + `" name="arr_is_include_tax[]" onchange="count();">
                                        <option value="0">--Tidak--</option>
                                        <option value="1">--Ya--</option>
                                    </select>
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_wtax_id` + count + `" name="arr_wtax_id[]" onchange="count();">
                                        <option value="0" data-value="0">-- Pilih ini jika non-PPh --</option>
                                        @foreach ($wtax as $row)
                                        <option value="{{ $row->id }}" {{ $row->is_default_pph ? 'selected' : '' }} data-value="{{ $row->percentage }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="center">
                                    <input type="text" id="arr_total` + count + `" name="arr_total[]" onfocus="emptyThis(this);" value="` + val.total + `" onkeyup="formatRupiah(this);" readonly style="text-align:right;">
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_place` + count + `" name="arr_place[]">
                                        @foreach ($place as $rowplace)
                                            <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                                        @endforeach
                                    </select>    
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_line` + count + `" name="arr_line[]" onchange="changePlace(this);">
                                        <option value="">--Kosong--</option>
                                        @foreach ($line as $rowline)
                                            <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                                        @endforeach
                                    </select>    
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_machine` + count + `" name="arr_machine[]" onchange="changeLine(this);">
                                        <option value="">--Kosong--</option>
                                        @foreach ($machine as $row)
                                            <option value="{{ $row->id }}" data-line="{{ $row->line_id }}">{{ $row->name }}</option>
                                        @endforeach    
                                    </select>
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_division` + count + `" name="arr_division[]">
                                        <option value="">--Kosong--</option>
                                        @foreach ($division as $rowdiv)
                                            <option value="{{ $rowdiv->id }}">{{ $rowdiv->name }}</option>
                                        @endforeach
                                    </select>    
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                        $('#arr_unit' + count).append(`
                            <option value="` + val.unit_id + `">` + val.unit_name + `</option>
                        `);
                        select2ServerSide('#arr_unit' + count, '{{ url("admin/select2/unit") }}');
                        $('#arr_tax_id' + count).val(val.tax_id);
                        $('#arr_wtax_id' + count).val(val.wtax_id);
                        $('#arr_is_include_tax' + count).val(val.is_include_tax);
                        if(val.place_id){
                            $('#arr_place' + count).val(val.place_id);
                        }
                        if(val.line_id){
                            $('#arr_line' + count).val(val.line_id);
                        }
                        if(val.machine_id){
                            $('#arr_machine' + count).val(val.machine_id);
                        }
                        if(val.division_id){
                            $('#arr_division' + count).val(val.division_id);
                        }
                        if(val.project_id){
                            $('#arr_project' + count).append(`
                                <option value="` + val.project_id + `">` + val.project_name + `</option>
                            `);
                        }
                    });
                }

                if(response.checklist.length > 0){
                    $.each(response.checklist, function(i, val) {
                        $('input[name^="arr_checklist_box[]"]').each(function(index){
                            if(val.id == $(this).val()){
                                $(this).prop( "checked", true);
                                $('input[name^="arr_checklist_note[]"]').eq(index).val(val.note);
                            }
                        });
                    });
                }
                
                $('.modal-content').scrollTop(0);
                $('#note').focus();
                M.updateTextFields();
                $('#post_date').trigger('change');
                /* count(); */
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
        var msg = '';
        swal({
            title: "Alasan mengapa anda menghapus!",
            text: "Anda tidak bisa mengembalikan data yang telah dihapus.",
            buttons: true,
            content: "input",
        })
        .then(message => {
            if (message != "" && message != null) {
                $.ajax({
                    url: '{{ Request::url() }}/destroy',
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

    function printPreview(code,aslicode){
        swal({
            title: "Apakah Anda ingin mengeprint dokumen ini?",
            text: "Dengan Kode "+aslicode,
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                $.ajax({
                    url: '{{ Request::url() }}/print_individual/' + code,
                    type:'GET',
                    beforeSend: function() {
                        loadingOpen('.modal-content');
                    },
                    complete: function() {
                        
                    },
                    success: function(data){
                        loadingClose('.modal-content');
                        printService.submit({
                            'type': 'INVOICE',
                            'url': data
                        })
                    }
                });  
            }
        });
        
    }

    function whatPrinting(code){
        $.ajax({
            url: window.location.origin + '/admin/finance/fund_request/print_individual/' + code,
            type:'GET',
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            complete: function() {
                
            },
            success: function(data){
                loadingClose('.modal-content');
                window.open(data, '_blank');
            }
        });
    }
</script>