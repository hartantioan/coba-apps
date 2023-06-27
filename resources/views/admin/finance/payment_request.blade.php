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
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="loadDataTable()">
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
                                                        <option value="6">Direvisi</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_company" style="font-size:1rem;">Perusahaan :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_company" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        @foreach ($company as $rowcompany)
                                                            <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
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
                                            <div class="col m4 s6 ">
                                                <label for="start_date" style="font-size:1rem;">Start Date (Tanggal Mulai) :</label>
                                                <div class="input-field col s12">
                                                <input type="date" id="start_date" name="start_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="finish_date" style="font-size:1rem;">End Date (Tanggal Berhenti) :</label>
                                                <div class="input-field col s12">
                                                    <input type="date" id="finish_date" name="finish_date"  onchange="loadDataTable()">
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
                                                        <th rowspan="2">Perusahaan</th>
                                                        <th rowspan="2">Kas/Bank</th>
                                                        <th rowspan="2">Tipe Pembayaran</th>
                                                        <th rowspan="2">No.Cek/BG</th>
                                                        <th colspan="2" class="center-align">Tanggal</th>
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
                <h4>Tambah/Edit {{ $title }}</h4>
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
                                <select class="form-control" id="company_id" name="company_id">
                                    @foreach ($company as $rowcompany)
                                        <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="company_id">Perusahaan</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="browser-default" id="coa_source_id" name="coa_source_id"></select>
                                <label class="active" for="coa_source_id">Kas / Bank</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="payment_type" name="payment_type" onchange="showRekening();">
                                    <option value="1">Tunai</option>
                                    <option value="2">Transfer</option>
                                    <option value="3">Cek</option>
                                    <option value="4">BG</option>
                                </select>
                                <label class="" for="payment_type">Tipe Pembayaran</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="payment_no" name="payment_no" type="text" value="-">
                                <label class="active" for="payment_no">No. CEK/BG</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                <label class="active" for="post_date">Tgl. Posting</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="top" name="top" min="0" type="number" value="0" readonly>
                                <label class="active" for="top">TOP (hari) Autofill</label>
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
                            <div class="col m12" id="rekening-element" style="display:none;">
                                <h6>Rekening (Jika transfer)</h6>
                                <div class="input-field col m3 s12">
                                    <select class="form-control" id="user_bank_id" name="user_bank_id" onchange="getRekening()">
                                        <option value="">--Pilih Partner Bisnis-</option>
                                    </select>
                                    <label class="" for="user_bank_id">Pilih Dari Daftar</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <input id="account_bank" name="account_bank" type="text" placeholder="Bank Tujuan" readonly>
                                    <label class="active" for="account_bank">Bank Tujuan</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <input id="account_no" name="account_no" type="text" placeholder="No Rekening Tujuan" readonly>
                                    <label class="active" for="account_no">No Rekening</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <input id="account_name" name="account_name" type="text" placeholder="Nama Pemilik Rekening" readonly>
                                    <label class="active" for="account_name">Nama Pemilik Rekening</label>
                                </div>
                            </div>
                            <div class="col m12 s12">
                                <h6><b>Data Terpakai</b> : <i id="list-used-data"></i></h6>
                            </div>
                            <div class="col m12 s12">
                                <p class="mt-2 mb-2">
                                    <h6>Detail Req. Dana / Uang Muka Pembelian / A/P Invoice</h6>
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
                                                    <th class="center">Potongan/Memo</th>
                                                    <th class="center">Bayar</th>
                                                    <th class="center">Keterangan</th>
                                                    <th class="center">Dist.Biaya</th>
                                                    <th class="center">Coa</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-detail">
                                                <tr id="empty-detail">
                                                    <td colspan="12" class="center">
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
                            <div class="input-field col m3 s12">
                                
                            </div>
                            <div class="input-field col m5 s12">
                                <table width="100%" class="bordered">
                                    <thead>
                                        <tr>
                                            <td width="33%">Biaya Admin</td>
                                            <td width="33%">
                                                <select class="browser-default" id="cost_distribution_id" name="cost_distribution_id"></select>
                                            </td>
                                            <td class="right-align" width="33%">
                                                <input class="browser-default" id="admin" name="admin" type="text" value="0,000" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100%;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">Total Bayar</td>
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
                            <div class="col s12" id="displayDetail">
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
<div id="modal4" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="show_structure">
                <div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;">

                </div>
                <div id="visualisation">
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>
<div id="modal4_1" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;width:100%;">
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

<div style="bottom: 50px; right: 80px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-amber-amber gradient-shadow modal-trigger tooltipped"  data-position="top" data-tooltip="Range Printing" href="#modal5">
        <i class="material-icons">view_comfy</i>
    </a>
</div>

<!-- END: Page Main-->
<script>
    $(function() {
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
            
        });

        loadDataTable();

        window.table.search('{{ $code }}').draw();

        $('#modal4_1').modal({
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
                        <td colspan="12" class="center">
                            Pilih supplier/vendor untuk memulai...
                        </td>
                    </tr>
                `);
                $('#account_id,#cost_distribution_id').empty();
                $('#admin,#grandtotal').val('0,00');
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }
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
                $('#displayDetail').html('');
            }
        });

        $('#modal4').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#myDiagramDiv').remove();
                $('#show_structure').append(
                    `<div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;"></div>
                    `
                );
            }
        });

        $('#modal5').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) {
                $('#validation_alert_multi').hide();
                $('#validation_alert_multi').html('');
                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                
            }
        });

        $('#body-detail').on('click', '.delete-data-detail', function() {
            $(this).closest('tr').remove();
            countAll();
        });

        select2ServerSide('#account_id,#filter_account', '{{ url("admin/select2/business_partner") }}');
        select2ServerSide('#coa_source_id', '{{ url("admin/select2/coa_cash_bank") }}');
        select2ServerSide('#cost_distribution_id', '{{ url("admin/select2/cost_distribution") }}');
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

    function showRekening(){
        if(['2','3','4'].includes($('#payment_type').val())){
            $('#rekening-element').show();
        }else{
            $('#user_bank_id').val('').formSelect();
            $('#account_bank,#account_no,#account_name').val('');
            $('#rekening-element').hide();
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
                                    <i class="material-icons close data-used" onclick="removeUsedData('` + val.type + `',` + val.id + `,'` + val.rawcode + `')">close</i>
                                </div>
                            `);
                            $('#body-detail').append(`
                                <tr class="row_detail" data-code="` + val.rawcode + `">
                                    <input type="hidden" name="arr_type[]" value="` + val.type + `" data-id="` + count + `">
                                    ` + ( val.coa_id ? `<input type="hidden" id="arr_coa` + count + `" name="arr_coa[]" value="` + val.coa_id + `" data-id="` + count + `">` : `` ) + `
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
                                    <td class="right-align" id="row_memo` + count + `">
                                        ` + val.memo + `
                                    </td>
                                    <td class="center">
                                        <input id="arr_pay` + count + `" name="arr_pay[]" data-grandtotal="` + val.grandtotal + `" class="browser-default" type="text" value="`+ val.balance + `" onkeyup="formatRupiah(this);countAll();checkTotal(this);" style="width:150px;text-align:right;">
                                    </td>
                                    <td class="center">
                                        <input id="arr_note` + count + `" name="arr_note[]" class="browser-default" type="text" style="width:150px;" value="-">
                                    </td>
                                    <td class="center">
                                        ` + ( val.coa_id ? `-` : `<select class="browser-default" id="arr_cost_distribution` + count + `" name="arr_cost_distribution[]" onchange="applyCoa('` + count + `');"></select>` ) + `
                                    </td>
                                    <td class="center">
                                        ` + ( val.coa_id ? `-` : `<select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]" required style="width: 100%"></select>` ) + `
                                    </td>
                                </tr>
                            `);
                            
                            if(!val.coa_id){
                                select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
                                select2ServerSide('#arr_cost_distribution' + count, '{{ url("admin/select2/cost_distribution") }}');
                            }
                            
                        });                        
                    }else{
                        $('#body-detail').empty().append(`
                            <tr id="empty-detail">
                                <td colspan="12" class="center">
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
            if($('.data-used').length > 0){
                $('.data-used').trigger('click');
            }
            $('#body-detail').empty().append(`
                <tr id="empty-detail">
                    <td colspan="12" class="center">
                        Pilih supplier/vendor untuk memulai...
                    </td>
                </tr>
            `);
            $('#deposit').val('0,000');
            $('#top').val('0');
            $('#total,#tax,#wtax,#grandtotal,#balance').text('0,000');
        }
    }

    function applyCoa(code){
        if($('#arr_cost_distribution' + code).val()){
            if($('#arr_cost_distribution' + code).select2('data')[0].coa_name){
                $('#arr_coa' + code).append(`
                    <option value="` + $('#arr_cost_distribution' + code).select2('data')[0].coa_id + `">` + $('#arr_cost_distribution' + code).select2('data')[0].coa_name + `</option>
                `)
            }
        }else{
            $('#arr_coa' + code).empty();
        }
    }

    function checkTotal(element){
        var nil = parseFloat($(element).val().replaceAll(".", "").replaceAll(",",".")), max = parseFloat($(element).data('grandtotal').replaceAll(".", "").replaceAll(",","."));
        if(nil > max){
            $(element).val($(element).data('grandtotal'));
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
        $('#grandtotal').val(formatRupiahIni(pay.toFixed(2).toString().replace('.',',')));
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

    function removeUsedData(table,id,code){
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
                $('.row_detail[data-code="' + code + '"]').remove();
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
                    company_id : $('#filter_company').val(),
                    'currency_id[]' : $('#filter_currency').val(),
                    start_date : $('#start_date').val(),
                    finish_date : $('#finish_date').val(),
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
                { name: 'company_id', className: 'center-align' },
                { name: 'coa_source_id', className: 'center-align' },
                { name: 'payment_type', className: 'center-align' },
                { name: 'payment_no', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
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
                'columnsToggle',
                'selectAll',
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
                $('#modal4_1').modal('open');
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
                var formData = new FormData($('#form_data')[0]);

                formData.delete("arr_code[]");
                formData.delete("arr_type[]");
                formData.delete("arr_pay[]");
                formData.delete("arr_note[]");
                formData.delete("arr_cost_distribution[]");
                formData.delete("arr_coa[]");

                let passed = true;

                $('input[name^="arr_code"]').each(function(){
                    if($(this).is(':checked')){
                        formData.append('arr_code[]',$(this).val());
                        formData.append('arr_type[]',$('input[name^="arr_type"][data-id="' + $(this).data('id') + '"]').val());
                        formData.append('arr_pay[]',$('#arr_pay' + $(this).data('id')).val());
                        formData.append('arr_note[]',$('#arr_note' + $(this).data('id')).val());
                        formData.append('arr_coa[]',$('#arr_coa' + $(this).data('id')).val());
                        formData.append('arr_cost_distribution[]',
                            ($('#arr_cost_distribution' + $(this).data('id')).length > 0 ? 
                                ($('#arr_cost_distribution' + $(this).data('id')).val() ? $('#arr_cost_distribution' + $(this).data('id')).val() : '') 
                            : '')
                        );
                        if(!$('#arr_coa' + $(this).data('id')).val() || !$('#arr_pay' + $(this).data('id')).val()){
                            passed = false;
                        }
                    }
                });

                if(passed){
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
                        text: 'Coa atau nominal bayar tidak boleh kosong.',
                        icon: 'warning',
                    });
                }
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
                if(response.cost_distribution_name){
                    $('#cost_distribution_id').empty().append(`
                        <option value="` + response.cost_distribution_id + `">` + response.cost_distribution_name + `</option>
                    `);
                }
                $('#company_id').val(response.company_id).formSelect();
                $('#payment_type').val(response.payment_type).formSelect();
                $('#payment_no').val(response.payment_no);
                $('#currency_id').val(response.currency_id).formSelect();
                $('#currency_rate').val(response.currency_rate);
                $('#post_date').val(response.post_date);
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
                            <tr class="row_detail" data-code="` + val.rawcode + `">
                                <input type="hidden" name="arr_type[]" value="` + val.type + `" data-id="` + count + `">
                                ` + ( val.type !== 'fund_requests' ? `<input type="hidden" id="arr_coa` + count + `" name="arr_coa[]" value="` + val.coa_id + `" data-id="` + count + `">` : `` ) + `
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
                                <td class="right-align" id="row_memo` + count + `">
                                    ` + val.memo + `
                                </td>
                                <td class="center">
                                    <input id="arr_pay` + count + `" name="arr_pay[]" class="browser-default" type="text" value=" `+ val.nominal + `" onkeyup="formatRupiah(this);countAll();" style="width:150px;text-align:right;">
                                </td>
                                <td class="center">
                                    <input id="arr_note` + count + `" name="arr_note[]" class="browser-default" type="text" style="width:150px;" value="` + val.note + `">
                                </td>
                                <td class="center">
                                    ` + ( val.type !== 'fund_requests' ? `-` : `<select class="browser-default" id="arr_cost_distribution` + count + `" name="arr_cost_distribution[]" onchange="applyCoa('` + count + `');"></select>` ) + `
                                </td>
                                <td class="center">
                                    ` + ( val.type !== 'fund_requests' ? `-` : `<select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]" required style="width: 100%"></select>` ) + `
                                </td>
                            </tr>
                        `);
                        
                        if(val.type == 'fund_requests'){
                            $('#arr_coa' + count).append(`
                                <option value="` + val.coa_id + `">` + val.coa_name + `</option>
                            `);
                            if(val.cost_distribution_id){
                                $('#arr_cost_distribution' + count).append(`
                                    <option value="` + val.cost_distribution_id + `">` + val.cost_distribution_name + `</option>
                                `);
                            }
                            select2ServerSide('#arr_cost_distribution' + count, '{{ url("admin/select2/cost_distribution") }}');
                            select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
                        }
                        
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

    var printService = new WebSocketPrinter({
        onConnect: function () {
            
        },
        onDisconnect: function () {
           
        },
        onUpdate: function (message) {
            
        },
    });
    
    function printData(){
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

    function printMultiSelect(){
        var formData = new FormData($('#form_data_print_multi')[0]);
        console.log(formData);
        $.ajax({
            url: '{{ Request::url() }}/print_by_range',
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
                $('#validation_alert_multi').html('');
                loadingOpen('.modal-content');
            },
            success: function(response) {
                loadingClose('.modal-content');
                if(response.status == 200) {
                    $('#modal5').modal('close');
                   /*  printService.submit({
                        'type': 'INVOICE',
                        'url': response.message
                    }) */
                    M.toast({
                        html: response.message
                    });
                } else if(response.status == 422) {
                    $('#validation_alert_multi').show();
                    $('.modal-content').scrollTop(0);
                    console.log(response.error);
                    swal({
                        title: 'Ups! Validation',
                        text: 'Check your form.',
                        icon: 'warning'
                    });
                    
                    $.each(response.error, function(i, val) {
                        $.each(val, function(i, val) {
                            $('#validation_alert_multi').append(`
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

    function printPreview(code){
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

    function exportExcel(){
        var search = window.table.search(), status = $('#filter_status').val(), company = $('#filter_company').val(), account = $('#filter_account').val(), currency = $('#filter_currency').val();
        
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status + "&company=" + company + "&account=" + account + "&currency=" + currency;
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
                            <i class="material-icons close data-used-pay" onclick="removeUsedData('payment_requests',` + response.data.id + `,'` + response.data.code + `')">close</i>
                        </div>
                    `);
                    $('#displayDetail').html(response.html);
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

    function makeTreeOrg(data,link){
        var $ = go.GraphObject.make;

        myDiagram =
        $(go.Diagram, "myDiagramDiv",
        {
            initialContentAlignment: go.Spot.Center,
            "undoManager.isEnabled": true,
            layout: $(go.TreeLayout,
            { 
                angle: 180,
                path: go.TreeLayout.PathSource,  
                setsPortSpot: false, 
                setsChildPortSpot: false,  
                arrangement: go.TreeLayout.ArrangementHorizontal
            })
        });
        $("PanelExpanderButton", "METHODS",
            { row: 2, column: 1, alignment: go.Spot.TopRight },
            {
                visible: true,
                click: function(e, obj) {
                    var node = obj.part.parent;
                    var diagram = node.diagram;
                    var data = node.data;
                    diagram.startTransaction("Collapse/Expand Methods");
                    diagram.model.setDataProperty(data, "isTreeExpanded", !data.isTreeExpanded);
                    diagram.commitTransaction("Collapse/Expand Methods");
                }
            },
            new go.Binding("visible", "methods", function(arr) { return arr.length > 0; })
        );
        myDiagram.addDiagramListener("ObjectDoubleClicked", function(e) {
            var part = e.subject.part;
            if (part instanceof go.Link) {
                
                console.log("");
            } else if (part instanceof go.Node) {
                window.open(part.data.url);
                if (part.isTreeExpanded) {
                    part.collapseTree();
                } else {
                    part.expandTree();
                }
                console.log("Node clicked: " + part.data.key);
            }
        });
        myDiagram.nodeTemplate =
        $(go.Node, "Auto",
            {
            locationSpot: go.Spot.Center,
            fromSpot: go.Spot.AllSides,
            toSpot: go.Spot.AllSides,
            portId: "",  

            },
            { isTreeExpanded: false },  
            $(go.Shape, { fill: "lightgrey", strokeWidth: 0 },
            new go.Binding("fill", "color")),
            $(go.Panel, "Table",
            { defaultRowSeparatorStroke: "black" },
            $(go.TextBlock,
                {
                row: 0, columnSpan: 2, margin: 3, alignment: go.Spot.Center,
                font: "bold 12pt sans-serif",
                isMultiline: false, editable: true
                },
                new go.Binding("text", "name").makeTwoWay()
            ),
            $(go.TextBlock, "Properties",
                { row: 1, font: "italic 10pt sans-serif" },
                new go.Binding("visible", "visible", function(v) { return !v; }).ofObject("PROPERTIES")
            ),
            $(go.Panel, "Vertical", { name: "PROPERTIES" },
                new go.Binding("itemArray", "properties"),
                {
                row: 1, margin: 3, stretch: go.GraphObject.Fill,
                defaultAlignment: go.Spot.Left,
                }
            ),
            
            $(go.Panel, "Auto",
                { portId: "r" },
                { margin: 6 },
                $(go.Shape, "Circle", { fill: "transparent", stroke: null, desiredSize: new go.Size(8, 8) })
            ),
            ),

            $("TreeExpanderButton",
            { alignment: go.Spot.Right, alignmentFocus: go.Spot.Right, width: 14, height: 14 }
            )
        );
        myDiagram.model.root = data[0].key;
        console.log(data[0].key);

        myDiagram.addDiagramListener("InitialLayoutCompleted", function(e) {
        setTimeout(function() {
            console.log(data[0].key);
            var rootKey = data[0].key; 
            var rootNode = myDiagram.findNodeForKey(rootKey);
            if (rootNode !== null) {
                rootNode.collapseTree();
            }
        }, 100); 
        });

        myDiagram.layout = $(go.TreeLayout);

        myDiagram.addDiagramListener("InitialLayoutCompleted", e => {
            e.diagram.findTreeRoots().each(r => r.expandTree(3));
        });

        myDiagram.model = $(go.GraphLinksModel,
        {
            copiesArrays: true,
            copiesArrayObjects: true,
            nodeDataArray: data,
            linkDataArray: link
        });
            
            
    }

    function viewStructureTree(id){
        $.ajax({
            url: '{{ Request::url() }}/viewstructuretree',
            type: 'GET',
            dataType: 'JSON',
            data: { 
                id : id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            success: function(response) {
                loadingClose('.modal-content');
            
                makeTreeOrg(response.message,response.link);
                
                $('#modal4').modal('open');
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
</script>