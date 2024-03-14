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
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="printData();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">Print</span>
                            <i class="material-icons right">local_printshop</i>
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
                                                <label for="start_date" style="font-size:1rem;">Tanggal Mulai :</label>
                                                <div class="input-field col s12">
                                                <input type="date" max="{{ date('9999'.'-12-31') }}" id="start_date" name="start_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="finish_date" style="font-size:1rem;">Tanggal Akhir :</label>
                                                <div class="input-field col s12">
                                                    <input type="date" max="{{ date('9999'.'-12-31') }}" id="finish_date" name="finish_date"  onchange="loadDataTable()">
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
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">Refresh</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
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
                                                        <th rowspan="2">Total</th>
                                                        <th rowspan="2">Pembulatan</th>
                                                        <th rowspan="2">Admin</th>
                                                        <th rowspan="2">Grandtotal</th>
                                                        <th rowspan="2">Bayar</th>
                                                        <th rowspan="2">Sisa</th>
                                                        <th rowspan="2">Dokumen</th>
                                                        <th rowspan="2">Bank Rekening</th>
                                                        <th rowspan="2">No Rekening</th>
                                                        <th rowspan="2">Pemilik Rekening</th>
                                                        <th rowspan="2">Keterangan</th>
                                                        <th rowspan="2">Reimburse</th>
                                                        <th rowspan="2">Status</th>
                                                        <th rowspan="2">Kas/Bank Keluar</th>
                                                        <th rowspan="2">Action</th>
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
    <div class="modal-content" style="overflow-x:hidden !important;">
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
                                <div class="input-field col m2 s12 step1">
                                    <input id="code" name="code" type="text" value="{{ $newcode }}" readonly>
                                    <label class="active" for="code">No. Dokumen</label>
                                </div>
                                <div class="input-field col m1 s12 step2">
                                    <select class="form-control" id="code_place_id" name="code_place_id" onchange="getCode(this.value);">
                                        <option value="">--Pilih--</option>
                                        @foreach ($place as $rowplace)
                                            <option value="{{ $rowplace->code }}">{{ $rowplace->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="input-field col m3 s12 step3">
                                    <input type="hidden" id="temp" name="temp">
                                    <select class="browser-default" id="account_id" name="account_id" onchange="getAccountInfo();"></select>
                                    <label class="active" for="account_id">Partner Bisnis</label>
                                </div>
                                <div class="input-field col m3 s12">
                                    <a href="javascript:void(0);" class="btn waves-effect waves-light cyan" onclick="getAccountInfo();" id="btn-show">Tampilkan Data<i class="material-icons right">assignment</i></a>
                                    <label class="active">&nbsp;</label>
                                </div>
                                <div class="input-field col m3 s12 step4">
                                    <select class="form-control" id="payment_type" name="payment_type" onchange="showRekening();">
                                        <option value="2">Transfer</option>
                                        <option value="1">Tunai</option>
                                        <option value="3">Cek/BG</option>
                                        {{-- <option value="4">BG</option> --}}
                                        <option value="5">Rekonsiliasi Hutang</option>
                                        {{-- <option value="6">Rekonsiliasi Dengan Dokumen</option> --}}
                                    </select>
                                    <label class="" for="payment_type">Tipe Pembayaran</label>
                                </div>
                                <div class="input-field col m6 s12 l6 op-element step5">
                                    <select class="browser-default" id="coa_source_id" name="coa_source_id"></select>
                                    <label class="active" for="coa_source_id">Kas / Bank</label>
                                    <span class="helper-text" data-error="wrong" data-success="right">Pilih kosong jika rekonsiliasi dengan piutang karyawan dan sisa bayar = 0.</span>
                                </div>
                                <div class="input-field col m3 s12 step6">
                                    <select class="form-control" id="company_id" name="company_id">
                                        @foreach ($company as $rowcompany)
                                            <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="" for="company_id">Perusahaan</label>
                                </div>
                                <div class="input-field col m3 s12 op-element step7">
                                    <input id="payment_no" name="payment_no" type="text" value="-">
                                    <label class="active" for="payment_no">No. CEK/BG</label>
                                </div>
                                <div class="input-field col m3 s12 step7_1">
                                    <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);loadCurrency();">
                                    <label class="active" for="post_date">Tgl. Posting</label>
                                </div>
                                <div class="input-field col m3 s12 op-element step8">
                                    <input id="top" name="top" min="0" type="number" value="0" readonly>
                                    <label class="active" for="top">TOP (hari) Autofill</label>
                                </div>
                                <div class="input-field col m3 s12 op-element step9">
                                    <input id="pay_date" name="pay_date" type="date" value="{{ date('Y-m-d') }}" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. bayar">
                                    <label class="active" for="pay_date">Tgl. Bayar</label>
                                </div>
                                <div class="file-field input-field col m3 s12 step10">
                                    <div class="btn">
                                        <span>Lampiran</span>
                                        <input type="file" name="document" id="document">
                                    </div>
                                    <div class="file-path-wrapper">
                                        <input class="file-path validate" type="text">
                                    </div>
                                </div>
                                <div class="input-field col m3 s12 step11">
                                    <select class="form-control" id="currency_id" name="currency_id" onchange="loadCurrency();countAfterLoadCurrency();">
                                        @foreach ($currency as $row)
                                            <option value="{{ $row->id }}" data-code="{{ $row->code }}">{{ $row->code.' '.$row->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="" for="currency_id">Mata Uang</label>
                                </div>
                                <div class="input-field col m3 s12 step12">
                                    <input id="currency_rate" name="currency_rate" type="text" value="1" onkeyup="formatRupiah(this);countAll();">
                                    <label class="active" for="currency_rate">Konversi</label>
                                </div>
                                <div class="input-field col m3 s12 step13">
                                    <select class="form-control" id="is_reimburse" name="is_reimburse" onchange="changeReimburse()">
                                        <option value="2">Tidak</option>
                                        <option value="1">Ya</option>
                                    </select>
                                    <label class="" for="is_reimburse">Apakah Reimburse?</label>
                                </div>
                                <div class="col m12" id="rekening-element">
                                    <h6>Rekening (Jika transfer)</h6>
                                    <div class="input-field col m3 s12 step14">
                                        <select class="browser-default" id="user_bank_id" name="user_bank_id" onchange="getRekening()">
                                            <option value="">--Pilih Partner Bisnis-</option>
                                        </select>
                                        <label class="active" for="user_bank_id">Pilih Dari Daftar</label>
                                    </div>
                                    <div class="input-field col m3 s12 step15">
                                        <input id="account_bank" name="account_bank" type="text" placeholder="Bank Tujuan">
                                        <label class="active" for="account_bank">Bank Tujuan</label>
                                    </div>
                                    <div class="input-field col m3 s12 step16">
                                        <input id="account_no" name="account_no" type="text" placeholder="No Rekening Tujuan">
                                        <label class="active" for="account_no">No Rekening</label>
                                    </div>
                                    <div class="input-field col m3 s12 step17">
                                        <input id="account_name" name="account_name" type="text" placeholder="Nama Pemilik Rekening">
                                        <label class="active" for="account_name">Nama Pemilik Rekening</label>
                                    </div>
                                </div>
                                <div class="col m12 s12">
                                    <h6><b>Data Terpakai</b> : <i id="list-used-data"></i></h6>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col s12">
                        <fieldset style="min-width: 100%;">
                            <legend>2. Daftar Dokumen Terpakai</legend>
                            <div class="row">
                                <div class="col m12 s12">
                                    <ul class="collapsible">
                                        <li class="active step18" id="main-tab">
                                            <div class="collapsible-header purple darken-1 text-white" style="color:white;"><i class="material-icons">library_books</i>BS Karyawan / AP DP / AP Invoice / AR Memo Terpakai</div>
                                            <div class="collapsible-body" style="display:block;">
                                                <div class="mt-2 mb-2" style="overflow:scroll;width:100% !important;">
                                                    <table class="bordered" style="min-width:2250px !important;" id="table-detail">
                                                        <thead>
                                                            <tr>
                                                                <th class="center">Referensi</th>
                                                                <th class="center">Tgl.Post</th>
                                                                <th class="center">Tgl.Jatuh Tempo</th>
                                                                <th class="center">Total</th>
                                                                <th class="center">PPN</th>
                                                                <th class="center">PPh</th>
                                                                <th class="center">Grandtotal</th>
                                                                <th class="center">Potongan/Memo</th>
                                                                <th class="center">Bayar</th>
                                                                <th class="center">Keterangan</th>
                                                                <th class="center">Hapus</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="body-detail">
                                                            <tr id="empty-detail">
                                                                <td colspan="11">
                                                                    Pilih partner bisnis untuk memulai...
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col m12 s12 hide" id="cost-tab">
                                    <h6>Daftar Pembiayaan BS Karyawan - Dokumen Lengkap</h6>
                                    <p class="mt-2 mb-2">
                                        <div style="overflow:scroll;width:100% !important;">
                                            <table class="bordered" style="min-width:2800px !important;">
                                                <thead>
                                                    <tr>
                                                        <th class="center">Coa</th>
                                                        <th class="center">Dist.Biaya</th>
                                                        <th class="center">Plant</th>
                                                        <th class="center">Line</th>
                                                        <th class="center">Mesin</th>
                                                        <th class="center">Divisi</th>
                                                        <th class="center">Proyek</th>
                                                        <th class="center">Ket.1</th>
                                                        <th class="center">Ket.2</th>
                                                        <th class="center">Debit FC</th>
                                                        <th class="center">Kredit FC</th>
                                                        <th class="center">Debit Rp</th>
                                                        <th class="center">Kredit Rp</th>
                                                        <th class="center">Hapus</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="body-detail-cost">
                                                    <tr id="last-row-detail-cost">
                                                        <td colspan="14">
                                                            Pilih partner bisnis untuk memulai...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <a class="waves-effect waves-light cyan btn-small mb-1 mt-1" onclick="addCoa()" href="javascript:void(0);">
                                            <i class="material-icons left">add</i> Tambah Coa
                                        </a>
                                    </p>
                                </div>
                                <div class="col m12 s12 step20">
                                    <p class="mt-2 mb-2 ">
                                        <h6>Dibayar dengan Outgoing Payment Piutang Karyawan (Jika Ada) - <small>Fitur ini akan men-jurnal entrikan hutang/biaya pada piutang yang anda pilih secara otomatis, ketika disetujui.</small></h6>
                                        <div>
                                            <table class="bordered" style="max-width:1650px !important;" id="table-detail2">
                                                <thead>
                                                    <tr>
                                                        <th class="center" width="10%">
                                                            <label>
                                                                <input type="checkbox" onclick="chooseAllOtherPayment(this)">
                                                                <span>Semua</span>
                                                            </label>
                                                        </th>
                                                        <th class="center">Kode Out. Payment</th>
                                                        <th class="center">Kode Payment Req.</th>
                                                        <th class="center">Bisnis Partner</th>
                                                        <th class="center">Tgl.Post</th>
                                                        <th class="center">Coa Kas/Bank</th>
                                                        <th class="center">Admin</th>
                                                        <th class="center">Total</th>
                                                        <th class="center">Grandtotal</th>
                                                        <th class="center">Digunakan</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="body-detail-payment">
                                                    <tr id="empty-detail-payment">
                                                        <td colspan="10" class="center">
                                                            Pilih partner bisnis untuk memulai...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </p>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col s12">
                        <fieldset>
                            <legend>3. Lain-lain</legend>
                            <div class="row">
                                <div class="input-field col m4 s12 step21">
                                    <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                    <label class="active" for="note">Keterangan</label>
                                </div>
                                <div class="input-field col m2 s12">
                                    
                                </div>
                                <div class="input-field col m6 s12 step22">
                                    <table width="100%" class="bordered">
                                        <thead>
                                            <tr>
                                                <td colspan="2">Total</td>
                                                <td class="right-align">
                                                    <input class="browser-default" id="total" name="total" onfocus="emptyThis(this);" type="text" value="0,00" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100%;" readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">Pembulatan</td>
                                                <td class="right-align">
                                                    <input class="browser-default" id="rounding" name="rounding" onfocus="emptyThis(this);" type="text" value="0,00" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100%;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="33%">Biaya Admin</td>
                                                <td width="33%">
                                                    <select class="browser-default" id="cost_distribution_id" name="cost_distribution_id"></select>
                                                </td>
                                                <td class="right-align" width="33%">
                                                    <input class="browser-default" id="admin" name="admin" onfocus="emptyThis(this);" type="text" value="0,00" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100%;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">Grandtotal</td>
                                                <td class="right-align">
                                                    <input class="browser-default" id="grandtotal" name="grandtotal" onfocus="emptyThis(this);" type="text" value="0,00" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100%;" readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">Bayar dengan Piutang / dijadikan Biaya</td>
                                                <td class="right-align">
                                                    <input class="browser-default" id="payment" name="payment" onfocus="emptyThis(this);" type="text" value="0,00" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100%;" readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">Sisa Harus Bayar</td>
                                                <td class="right-align">
                                                    <input class="browser-default" id="balance" name="balance" onfocus="emptyThis(this);" type="text" value="0,00" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100%;" readonly>
                                                </td>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="col s12 mt-3">
                                    <button class="btn waves-effect waves-light right submit step23" onclick="save();">Simpan <i class="material-icons right">send</i></button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light purple " onclick="startIntro();">Panduan <i class="material-icons right">help_outline</i></button>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal2" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
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

<div id="modal3" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
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
                            <div class="input-field col m2 s12">
                                <input id="codePay" name="codePay" type="text" value="{{ $newcodePay }}">
                                <label class="active" for="codePay">No. Dokumen</label>
                            </div>
                            <div class="input-field col m1 s12">
                                <select class="form-control" id="pay_code_place_id" name="pay_code_place_id" onchange="getCodePay(this.value);">
                                    <option value="">--Pilih--</option>
                                    @foreach ($place as $rowplace)
                                        <option value="{{ $rowplace->code }}">{{ $rowplace->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="input-field col m3 s12">
                                <input type="hidden" id="tempPay" name="tempPay">
                                <input id="pay_date_pay" name="pay_date_pay" type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. bayar">
                                <label class="active" for="pay_date_pay">Tgl. Bayar</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="currency_id_pay" name="currency_id_pay">
                                    @foreach ($currency as $row)
                                        <option value="{{ $row->id }}" data-code="{{ $row->code }}">{{ $row->code.' '.$row->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="currency_id_pay">Mata Uang</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="currency_rate_pay" name="currency_rate_pay" type="text" value="1" onkeyup="formatRupiah(this);convertBalance();">
                                <label class="active" for="currency_rate_pay">Konversi</label>
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
                            <div class="col s12 mt-3">
                                <h6>
                                    <b>Data Terpakai</b> : <i id="list-used-data-pay"></i>
                                    <button class="btn waves-effect waves-light right submit" onclick="savePay();">Simpan <i class="material-icons right">send</i></button>
                                </h6>
                            </div>
                            <div class="col s12" id="displayDetail">
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
<div id="modal4" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
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
<div id="modal4_1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
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

<div id="modal6" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-header ml-2">
        <h5>Daftar Tunggakan Dokumen <b id="account_name"></b></h5>
    </div>
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <div class="row">
                    <div class="col s12 mt-2">
                        <ul class="collapsible-modal6">
                            <li class="active">
                                <div class="collapsible-header purple lightrn-1 white-text">
                                    <i class="material-icons">layers</i> Fund Requests / AP Down Payment / AP Invoice / AR Memo
                                </div>
                                <div class="collapsible-body" style="zoom:0.8;">
                                    <div id="datatable_buttons_multi"></div>
                                    <i class="right">Gunakan *pilih semua* untuk memilih seluruh data yang anda inginkan. Atau pilih baris untuk memilih data yang ingin dipindahkan.</i>
                                    <table id="table_multi" class="display" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="center-align">Kode Dokumen</th>
                                                <th class="center-align">Tgl.Post</th>
                                                <th class="center-align">Total</th>
                                                <th class="center-align">PPN</th>
                                                <th class="center-align">PPh</th>
                                                <th class="center-align">Grandtotal</th>
                                                <th class="center-align">Downpayment</th>
                                                <th class="center-align">Rounding</th>
                                                <th class="center-align">Sisa</th>
                                                <th class="center-align">Memo</th>
                                                <th class="center-align">Final</th>
                                                <th class="center-align">Keterangan</th>
                                                <th class="center-align">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-detail-multi"></tbody>
                                    </table>
                                </div>
                            </li>
                            <li class="active">
                                <div class="collapsible-header blue lightrn-1 white-text">
                                    <i class="material-icons">layers</i> Piutang Karyawan Yang Sudah Terbayar
                                </div>
                                <div class="collapsible-body">
                                    <div id="datatable_buttons_multi_op"></div>
                                    <i class="right">Gunakan *pilih semua* untuk memilih seluruh data yang anda inginkan. Atau pilih baris untuk memilih data yang ingin dipindahkan.</i>
                                    <table id="table_multi_op" class="display" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="center">Kode Out. Payment</th>
                                                <th class="center">Kode Payment Req.</th>
                                                <th class="center">Bisnis Partner</th>
                                                <th class="center">Tgl.Post</th>
                                                <th class="center">Coa Kas/Bank</th>
                                                <th class="center">Admin</th>
                                                <th class="center">Total</th>
                                                <th class="center">Grandtotal</th>
                                                <th class="center">Terpakai</th>
                                                <th class="center">Sisa</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-detail-multi-op"></tbody>
                                    </table>
                                </div>
                            </li>                        
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat mr-1" onclick="resetBp();">Close</a>
        <button class="btn waves-effect waves-light purple right submit" onclick="applyDocuments();">Gunakan <i class="material-icons right">forward</i></button>
    </div>
</div>

<div id="modal5" class="modal modal-fixed-footer" style="height: 70% !important;width:50%">
    <div class="modal-header ml-6 mt-2">
        <h6>Range Printing</h6>
    </div>
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <form class="row" id="form_data_print_multi" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert_multi" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <ul class="tabs">
                            <li class="tab">
                                <a href="#range-tabs" class="" id="part-tabs-btn">
                                <span>By No</span>
                                </a>
                            </li>
                            <li class="tab">
                                <a href="#date-tabs" class="">
                                <span>By Date</span>
                                </a>
                            </li>
                            <li class="indicator" style="left: 0px; right: 0px;"></li>
                        </ul>
                        <div id="range-tabs" style="display: block;" class="">                           
                            <div class="row ml-2 mt-2">
                                <div class="row">
                                    <div class="input-field col m2 s12">
                                        <p>{{ $menucode }}</p>
                                    </div>
                                    <div class="input-field col m2 s12">
                                        <select class="form-control" id="code_place_range" name="code_place_range">
                                            <option value="">--Pilih--</option>
                                            @foreach ($place as $rowplace)
                                                <option value="{{ $rowplace->code }}">{{ $rowplace->code }}</option>
                                            @endforeach
                                        </select>
                                        <label class="" for="code_place_range">Plant / Place</label>
                                    </div>
                                    <div class="input-field col m2 s12">
                                        <input id="year_range" name="year_range" min="0" type="number" placeholder="23">
                                        <label class="active" for="year_range">Tahun</label>
                                    </div>
                                    <div class="input-field col m1 s12">
                                        <input id="range_start" name="range_start" min="0" type="number" placeholder="1">
                                        <label class="" for="range_end">No Awal</label>
                                    </div>
                                    
                                    <div class="input-field col m1 s12">
                                        <input id="range_end" name="range_end" min="0" type="number" placeholder="1">
                                        <label class="active" for="range_end">No akhir</label>
                                    </div>
                                    <div class="input-field col m2 s12">
                                        <label>
                                            <input name="type_date" type="radio" checked value="1"/>
                                            <span>Dengan range biasa</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                <div class="input-field col m8 s12">
                                    <input id="range_comma" name="range_comma" type="text" placeholder="1,2,5....">
                                    <label class="" for="range_end">Masukkan angka dengan koma</label>
                                </div>
                               
                                <div class="input-field col m1 s12">
                                    <label>
                                        <input name="type_date" type="radio" value="2"/>
                                        <span>Dengan Range koma</span>
                                    </label>
                                </div>
                                </div>
                                <div class="col s12 mt-3">
                                    <button class="btn waves-effect waves-light right submit" onclick="printMultiSelect();">Print <i class="material-icons right">send</i></button>
                                </div>
                            </div>                         
                        </div>
                        <div id="date-tabs" style="display: none;" class="">
                            
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat mr-1">Close</a>
    </div>
</div>

<div id="modal7" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row" >
            <div class="col m3 s12">
                
            </div>
            <div class="col m6 s12">
                <h4 id="title_data" style="text-align:center"></h4>
                <h5 id="code_data" style="text-align:center"></h5>
            </div>
            <div class="col m3 s12 right-align">
                <img src="{{ url('website/logo_web_fix.png') }}" width="40%" height="60%">
            </div>
        </div>
        <div class="divider mb-1 mt-2"></div>
        <div class="row">
            <div class="col" id="user_jurnal">
            </div>
            <div class="col" id="post_date_jurnal">
            </div>
            <div class="col" id="note_jurnal">
            </div>
            <div class="col" id="ref_jurnal">
            </div>
            <div class="col" id="company_jurnal">
            </div>
        </div>
        <div class="row mt-2">
            <table class="bordered Highlight striped" style="zoom:0.7;">
                <thead>
                        <tr>
                            <th class="center-align" rowspan="2">No</th>
                            <th class="center-align" rowspan="2">Coa</th>
                            <th class="center-align" rowspan="2">Partner Bisnis</th>
                            <th class="center-align" rowspan="2">Plant</th>
                            <th class="center-align" rowspan="2">Line</th>
                            <th class="center-align" rowspan="2">Mesin</th>
                            <th class="center-align" rowspan="2">Divisi</th>
                            <th class="center-align" rowspan="2">Gudang</th>
                            <th class="center-align" rowspan="2">Proyek</th>
                            <th class="center-align" rowspan="2">Ket.1</th>
                            <th class="center-align" rowspan="2">Ket.2</th>
                            <th class="center-align" colspan="2">Mata Uang Asli</th>
                            <th class="center-align" colspan="2">Mata Uang Konversi</th>
                        </tr>
                        <tr>
                            <th class="center-align">Debit</th>
                            <th class="center-align">Kredit</th>
                            <th class="center-align">Debit</th>
                            <th class="center-align">Kredit</th>
                        </tr>
                    
                </thead>
                <tbody id="body-journal-table">
                </tbody>
            </table>
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
    var arrRekening = [];

    $(function() {

        $("#table-detail th,#table-detail1 th,#table-detail2 th").resizable({
            minWidth: 100,
        });

        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
        });

        loadDataTable();

        window.table.search('{{ $code }}').draw();

        $('#modal7').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#title_data').empty();
                $('#code_data').empty();             
                $('#body-journal-table').empty();
                $('#user_jurnal').empty();
                $('#note_jurnal').empty();
                $('#ref_jurnal').empty();
                $('#company_jurnal').empty();
                $('#post_date_jurnal').empty();
            }
        });

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
                        $('#body-detail-payment').empty().append(`
                            <tr id="empty-detail-payment">
                                <td colspan="10" class="center">
                                    Pilih partner bisnis untuk memulai...
                                </td>
                            </tr>
                        `);
                    }
                    return 'You will lose all changes made since your last save';
                };
                if(!$('#temp').val()){
                    loadCurrency();
                }
                /* $('.collapsible').collapsible(); */
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                $('.row_purchase').each(function(){
                    $(this).remove();
                });
                $('input').css('border', 'none');
                $('input').css('border-bottom', '0.5px solid black');
                $('.row_detail_coa').remove();
                M.updateTextFields();
                $('#body-detail').empty().append(`
                    <tr id="empty-detail">
                        <td colspan="11">
                            Pilih partner bisnis untuk memulai...
                        </td>
                    </tr>
                `);
                $('#account_id,#cost_distribution_id,#coa_source_id').empty();
                $('#admin,#grandtotal').val('0,00');
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }
                $('#body-detail-payment').empty().append(`
                    <tr id="empty-detail-payment">
                        <td colspan="10" class="center">
                            Pilih partner bisnis untuk memulai...
                        </td>
                    </tr>
                `);
                window.onbeforeunload = function() {
                    return null;
                };

                if(!$('#cost-tab').hasClass('hide')){
                    $('#cost-tab').addClass('hide');
                }

                $('#body-detail-cost').empty().append(`
                    <tr id="last-row-detail-cost">
                        <td colspan="14">
                            Pilih partner bisnis untuk memulai...
                        </td>
                    </tr>
                `);

                arrRekening = [];
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
                if($('#pay_code_place_id option').length > 1){
                    $("#pay_code_place_id").val($("#pay_code_place_id option").eq(1).val()).formSelect().trigger('change');
                }
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

        $('#modal6').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                $('.collapsible-modal6').collapsible({
                    accordion:false
                });
            },
            onOpenEnd: function(modal, trigger) {
                table_multi = $('#table_multi').DataTable({
                    /* "responsive": true, */
                    "ordering": false,
                    scrollY: '50vh',
                    scrollCollapse: true,
                    "iDisplayInLength": 10,
                    dom: 'Blfrtip',
                    buttons: [
                        'selectAll',
                        'selectNone'
                    ],
                    "language": {
                        "lengthMenu": "Menampilkan _MENU_ data per halaman",
                        "zeroRecords": "Data tidak ditemukan / kosong",
                        "info": "Menampilkan halaman _PAGE_ / _PAGES_ dari total _TOTAL_ data",
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
                    }
                });
                $('#table_multi_wrapper > .dt-buttons').appendTo('#datatable_buttons_multi');
                $('select[name="table_multi_length"]').addClass('browser-default');

                table_multi_op = $('#table_multi_op').DataTable({
                    /* "responsive": true, */
                    "ordering": false,
                    scrollY: '50vh',
                    scrollCollapse: true,
                    "iDisplayInLength": 10,
                    dom: 'Blfrtip',
                    buttons: [
                        'selectAll',
                        'selectNone'
                    ],
                    "language": {
                        "lengthMenu": "Menampilkan _MENU_ data per halaman",
                        "zeroRecords": "Data tidak ditemukan / kosong",
                        "info": "Menampilkan halaman _PAGE_ / _PAGES_ dari total _TOTAL_ data",
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
                    }
                });
                $('#table_multi_op_wrapper > .dt-buttons').appendTo('#datatable_buttons_multi_op');
                $('select[name="table_multi_op_length"]').addClass('browser-default');
            },
            onCloseEnd: function(modal, trigger){
                $('#body-detail-multi,#body-detail-multi-op').empty();
                $('#account_name').text('');
                $('#preview_data').html('');
                $('#table_multi').DataTable().clear().destroy();
                $('#table_multi_op').DataTable().clear().destroy();
            }
        });

        $('#table_multi,#table_multi_op').on('click', 'input', function(event) {
            event.stopPropagation();
        });

        $('#body-detail').on('click', '.delete-data-detail', function() {
            $(this).closest('tr').remove();
            countAll();
        });

        $('#body-detail-cost').on('click', '.delete-data-detail', function() {
            $(this).closest('tr').remove();
            countAll();
        });

        select2ServerSide('#account_id,#filter_account', '{{ url("admin/select2/business_partner") }}');
        
        $('#coa_source_id').select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/coa_cash_bank") }}',
                type: 'GET',
                dataType: 'JSON',
                data: function(params) {
                    return {
                        search: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.items
                    }
                }
            }
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

        select2ServerSide('#cost_distribution_id', '{{ url("admin/select2/cost_distribution") }}');
    });

    String.prototype.replaceAt = function(index, replacement) {
        return this.substring(0, index) + replacement + this.substring(index + replacement.length);
    };

    function convertBalance(){
        let currency_rate = parseFloat($('#currency_rate_pay').val().replaceAll(".", "").replaceAll(",","."));
        let realBalance = parseFloat($('#real-balance').text().replaceAll(".", "").replaceAll(",","."));
        let convertBalance = currency_rate * realBalance;
        $('#convert-balance').text(
            (convertBalance >= 0 ? '' : '-') + formatRupiahIni(convertBalance.toFixed(2).toString().replace('.',','))
        );
    }

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

    function getCodePay(val){
        if(val){
            if($('#codePay').val().length > 7){
                $('#codePay').val($('#codePay').val().slice(0, 7));
            }
            $.ajax({
                url: '{{ Request::url() }}/get_code_pay',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    val: $('#codePay').val() + val,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    loadingClose('.modal-content');
                    $('#codePay').val(response);
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

    function changeDateMinimum(val){
        if(val){
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

    function changeDateMinimumPay(val){
        if(val){
            let newcode = $('#codePay').val().replaceAt(5,val.split('-')[0].toString().substr(-2));
            if($('#codePay').val().substring(5, 7) !== val.split('-')[0].toString().substr(-2)){
                if(newcode.length > 9){
                    newcode = newcode.substring(0, 9);
                }
            }
            $('#codePay').val(newcode).trigger('keyup');
        }
    }

    function resetBp(){
        /* $('#account_id').empty();
        $('#user_bank_id').empty();
        $('#user_bank_id').append(`
            <option value="">--Pilih Partner Bisnis-</option>
        `); */
    }

    function addCoa(){
        var countdetail = makeid(10);
        if($('#last-row-detail-cost').length > 0){
            $('#last-row-detail-cost').remove();
        }
        $('#body-detail-cost').append(`
            <tr class="row_detail_cost">
                <td class="">
                    <select class="browser-default" id="arr_coa_cost` + countdetail + `" name="arr_coa_cost[]"></select>
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_cost_distribution_cost` + countdetail + `" name="arr_cost_distribution_cost[]"></select> 
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_place` + countdetail + `" name="arr_place[]">
                        @foreach ($place as $rowplace)
                            <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select class="browser-default" id="arr_line` + countdetail + `" name="arr_line[]" onchange="changePlace(this);">
                        <option value="">--Kosong--</option>
                        @foreach ($line as $rowline)
                            <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                        @endforeach
                    </select>    
                </td>
                <td>
                    <select class="browser-default" id="arr_machine` + countdetail + `" name="arr_machine[]" onchange="changeLine(this);">
                        <option value="">--Kosong--</option>
                        @foreach ($machine as $rowmachine)
                            <option value="{{ $rowmachine->id }}" data-line="{{ $rowmachine->line_id }}">{{ $rowmachine->name }}</option>
                        @endforeach
                    </select>    
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_division` + countdetail + `" name="arr_division[]">
                        <option value="">--Kosong--</option>
                        @foreach ($department as $row)
                            <option value="{{ $row->id }}">{{ $row->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_project` + countdetail + `" name="arr_project[]"></select>
                </td>
                <td>
                    <input type="text" name="arr_note_cost[]" placeholder="Keterangan 1..." value="" data-id="` + countdetail + `">
                </td>
                <td>
                    <input type="text" name="arr_note_cost2[]" placeholder="Keterangan 2..." value="" data-id="` + countdetail + `">
                </td>
                <td class="center">
                    <input class="browser-default" type="text" name="arr_nominal_debit_fc[]" value="0,00" data-id="` + countdetail + `" onkeyup="formatRupiah(this);countAll();">
                </td>
                <td class="right-align">
                    <input class="browser-default" type="text" name="arr_nominal_credit_fc[]" value="0,00" data-id="` + countdetail + `" onkeyup="formatRupiah(this);countAll();">
                </td>
                <td class="right-align">
                    <input class="browser-default" type="text" name="arr_nominal_debit[]" value="0,00" data-id="` + countdetail + `" onkeyup="formatRupiah(this);" readonly>
                </td>
                <td class="right-align">
                    <input class="browser-default" type="text" name="arr_nominal_credit[]" value="0,00" data-id="` + countdetail + `" onkeyup="formatRupiah(this);" readonly>
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        select2ServerSide('#arr_coa_cost' + countdetail, '{{ url("admin/select2/coa") }}');
        select2ServerSide('#arr_project' + countdetail, '{{ url("admin/select2/project") }}');
        select2ServerSide('#arr_cost_distribution_cost' + countdetail, '{{ url("admin/select2/cost_distribution") }}');
    }

    function getRekening(){
        if($('#user_bank_id').val()){
            $('#account_bank').val($('#user_bank_id').select2('data')[0].bank);
            $('#account_no').val($('#user_bank_id').select2('data')[0].no);
            $('#account_name').val($('#user_bank_id').select2('data')[0].name);
            $('#account_bank,#account_no,#account_name').prop('readonly',true);
        }else{
            $('#account_bank,#account_no,#account_name').val('');
            $('#account_bank,#account_no,#account_name').prop('readonly',false);
        }
    }

    function showRekening(){
        if(['2','3','4'].includes($('#payment_type').val())){
            $('#rekening-element').show();
        }else{
            $('#user_bank_id').val('').trigger('change');
            $('#account_bank,#account_no,#account_name').val('');
            $('#rekening-element').hide();
        }

        if($('#payment_type').val() == '5'){
            $('#coa_source_id').empty();
            $('#cost_distribution_id').attr('disabled', true);
            $('#admin').prop("readonly", true);
            $('#payment_type').val('5').formSelect();
        }else{
            $('#admin').prop("readonly", false);
        }
    }

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

    function getAccountInfo(){
        if($('#account_id').val()){
            $.ajax({
                url: '{{ Request::url() }}/get_account_info',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: $('#account_id').val(),
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    loadingClose('.modal-content');
                    $('#modal6').modal('open');
                    $('#account_name').text($('#account_id').select2('data')[0].text);
                    $('#top').val(response.top);

                    if(response.details.length > 0){
                        $.each(response.details, function(i, val) {
                            $('#body-detail-multi').append(`
                                <tr data-type="` + val.type + `" data-id="` + val.id + `" data-document="` + val.document_status + `">
                                    <td class="center">
                                        ` + val.rawcode + `
                                    </td>
                                    <td class="center">
                                        ` + val.post_date + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.total + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.tax + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.wtax + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.grandtotal + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.downpayment + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.rounding + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.balance + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.memo + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.final + `
                                    </td>
                                    <td class="center-align">
                                        ` + val.note + `
                                    </td>
                                    <td class="center-align">
                                        ` + val.status_document + `
                                    </td>
                                </tr>
                            `);
                        });
                    }

                    $('#body-detail-multi-op').empty();
                    if(response.payments.length > 0){
                        $.each(response.payments, function(i, val) {
                            var count = makeid(10);
                            $('#body-detail-multi-op').append(`
                                <tr data-id="` + val.id + `">
                                    <td class="center">
                                        ` + val.code + `
                                    </td>
                                    <td class="center">
                                        ` + val.payment_request_code + `
                                    </td>
                                    <td>
                                        ` + val.name + `
                                    </td>
                                    <td>
                                        ` + val.post_date + `
                                    </td>
                                    <td>
                                        ` + val.coa_name + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.admin + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.total + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.grandtotal + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.used + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.balance + `
                                    </td>
                                </tr>
                            `);
                        });
                    }

                    $('#user_bank_id').empty();

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
            $('#top').val('0');
            $('#user_bank_id').empty().append(`
                <option value="">--Pilih Partner Bisnis-</option>
            `);
            if($('.data-used').length > 0){
                $('.data-used').trigger('click');
            }
            $('#rekening-element').show();
            countAll();
        }
    }

    function checkDiff(arr) {
        return !arr.every(e => e == arr[0]);
    }

    function applyDocuments(){
        swal({
            title: "Apakah anda yakin?",
            text: "Jika sudah ada di dalam tabel detail form, maka akan tergantikan dengan pilihan baru anda saat ini.",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                let arr_id = [], arr_type = [], sametype = true, arr_op_id = [], arr_qty_duplicate = [], passedSelected = true, arr_status = [];
                $.map(table_multi.rows('.selected').nodes(), function (item) {
                    arr_id.push($(item).data('id'));
                    arr_status.push($(item).data('document'));
                    arr_qty_duplicate.push(($('#arr_qty_duplicate' + $(item).data('id')).length > 0 ? $('#arr_qty_duplicate' + $(item).data('id')).val() : '1'));
                    arr_type.push($(item).data('type'));
                });

                if(checkDiff(arr_status)){
                    passedSelected = false;
                }

                if(passedSelected){
                    $.map(table_multi_op.rows('.selected').nodes(), function (item) {
                        arr_op_id.push($(item).data('id'));
                    });

                    $.ajax({
                        url: '{{ Request::url() }}/get_account_data',
                        type: 'POST',
                        dataType: 'JSON',
                        data: {
                            arr_id: arr_id,
                            arr_type: arr_type,
                            arr_op_id: arr_op_id,
                            arr_qty_duplicate: arr_qty_duplicate,
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        beforeSend: function() {
                            loadingOpen('.modal-content');
                        },
                        success: function(response) {
                            loadingClose('.modal-content');

                            $('#empty-detail').remove();
                            $('.row_detail_cost').remove();
                            $('#body-detail').empty();
                            if(response.details.length > 0){
                                $.each(response.details, function(i, val) {
                                    $('.row_detail[data-account!="' + val.account_code + '"]').remove();
                                    var count = makeid(10);
                                    $('#list-used-data').append(`
                                        <div class="chip purple darken-4 gradient-shadow white-text">
                                            ` + val.rawcode + `
                                            <i class="material-icons close data-used" onclick="removeUsedData('` + val.type + `',` + val.id + `,'` + val.rawcode + `')">close</i>
                                        </div>
                                    `);
                                    $('#body-detail').append(`
                                        <tr class="row_detail" data-code="` + val.rawcode + `" data-account="` + val.account_code + `">
                                            <input type="hidden" name="arr_id[]" value="` + val.id + `" data-id="` + count + `">
                                            <input type="hidden" name="arr_type[]" value="` + val.type + `" data-id="` + count + `">
                                            <input type="hidden" name="arr_account_bank[]" value="` + val.bank_account + `" data-id="` + count + `">
                                            <input type="hidden" name="arr_account_no[]" value="` + val.no_account + `" data-id="` + count + `">
                                            <input type="hidden" name="arr_account_name[]" value="` + val.name_account + `" data-id="` + count + `">
                                            <input type="hidden" name="arr_code[]" value="` + val.code + `">
                                            <input type="hidden" name="arr_coa[]" value="` + val.coa_id + `"">
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
                                                <input id="arr_pay` + count + `" name="arr_pay[]" onfocus="emptyThis(this);" data-grandtotal="` + val.balance + `" class="browser-default" type="text" value="`+ val.balance_duplicate + `" onkeyup="formatRupiah(this);countAll();checkTotal(this);" style="width:150px;text-align:right;">
                                            </td>
                                            <td class="center">
                                                <input id="arr_note` + count + `" name="arr_note[]" class="" type="text" style="width:350px;" value="` + val.note + `">
                                            </td>
                                            <td class="center">
                                                <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                                                    <i class="material-icons">delete</i>
                                                </a>
                                            </td>
                                        </tr>
                                    `);

                                    if(val.document_status == '2'){
                                        $('#cost-tab').removeClass('hide');
                                        if($('#last-row-detail-cost').length > 0){
                                            $('#last-row-detail-cost').remove();
                                        }
                                        $.each(val.list_details, function(i, value) {
                                            var countdetail = makeid(10);
                                            $('#body-detail-cost').append(`
                                                <tr class="row_detail_cost">
                                                    <td class="">
                                                        <select class="browser-default" id="arr_coa_cost` + countdetail + `" name="arr_coa_cost[]"></select>
                                                    </td>
                                                    <td class="center">
                                                        <select class="browser-default" id="arr_cost_distribution_cost` + countdetail + `" name="arr_cost_distribution_cost[]"></select> 
                                                    </td>
                                                    <td class="center">
                                                        <select class="browser-default" id="arr_place` + countdetail + `" name="arr_place[]">
                                                            @foreach ($place as $rowplace)
                                                                <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="browser-default" id="arr_line` + countdetail + `" name="arr_line[]" onchange="changePlace(this);">
                                                            <option value="">--Kosong--</option>
                                                            @foreach ($line as $rowline)
                                                                <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                                                            @endforeach
                                                        </select>    
                                                    </td>
                                                    <td>
                                                        <select class="browser-default" id="arr_machine` + countdetail + `" name="arr_machine[]" onchange="changeLine(this);">
                                                            <option value="">--Kosong--</option>
                                                            @foreach ($machine as $rowmachine)
                                                                <option value="{{ $rowmachine->id }}" data-line="{{ $rowmachine->line_id }}">{{ $rowmachine->name }}</option>
                                                            @endforeach
                                                        </select>    
                                                    </td>
                                                    <td class="center">
                                                        <select class="browser-default" id="arr_division` + countdetail + `" name="arr_division[]">
                                                            <option value="">--Kosong--</option>
                                                            @foreach ($department as $row)
                                                                <option value="{{ $row->id }}">{{ $row->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="center">
                                                        <select class="browser-default" id="arr_project` + countdetail + `" name="arr_project[]"></select>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="arr_note_cost[]" placeholder="Keterangan 1..." value="` + value.note + `" data-id="` + countdetail + `">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="arr_note_cost2[]" placeholder="Keterangan 2..." value="" data-id="` + countdetail + `">
                                                    </td>
                                                    <td class="center">
                                                        <input class="browser-default" type="text" name="arr_nominal_debit_fc[]" value="` + (value.type == '1' ? value.nominal : '0,00') + `" data-id="` + countdetail + `" onkeyup="formatRupiah(this);countAll();">
                                                    </td>
                                                    <td class="right-align">
                                                        <input class="browser-default" type="text" name="arr_nominal_credit_fc[]" value="` + (value.type == '2' ? value.nominal : '0,00') + `" data-id="` + countdetail + `" onkeyup="formatRupiah(this);countAll();">
                                                    </td>
                                                    <td class="right-align">
                                                        <input class="browser-default" type="text" name="arr_nominal_debit[]" value="` + (value.type == '1' ? value.nominal : '0,00') + `" data-id="` + countdetail + `" onkeyup="formatRupiah(this);" readonly>
                                                    </td>
                                                    <td class="right-align">
                                                        <input class="browser-default" type="text" name="arr_nominal_credit[]" value="` + (value.type == '2' ? value.nominal : '0,00') + `" data-id="` + countdetail + `" onkeyup="formatRupiah(this);" readonly>
                                                    </td>
                                                    <td class="center">
                                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                                                            <i class="material-icons">delete</i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            `);
                                            if(value.coa_id){
                                                $('#arr_coa_cost' + countdetail).append(`
                                                    <option value="` + value.coa_id + `">` + value.coa_name + `</option>
                                                `);
                                            }
                                            if(value.place_id){
                                                $('#arr_place' + countdetail).val(value.place_id);
                                            }
                                            if(value.line_id){
                                                $('#arr_line' + countdetail).val(value.line_id);
                                            }
                                            if(value.machine_id){
                                                $('#arr_machine' + countdetail).val(value.machine_id);
                                            }
                                            if(value.division_id){
                                                $('#arr_division' + countdetail).val(value.division_id);
                                            }
                                            if(value.project_id){
                                                $('#arr_project' + countdetail).append(`
                                                    <option value="` + value.project_id + `">` + value.project_name + `</option>
                                                `);
                                            }
                                            select2ServerSide('#arr_coa_cost' + countdetail, '{{ url("admin/select2/coa") }}');
                                            select2ServerSide('#arr_project' + countdetail, '{{ url("admin/select2/project") }}');
                                            select2ServerSide('#arr_cost_distribution_cost' + countdetail, '{{ url("admin/select2/cost_distribution") }}');
                                        });
                                    }else{
                                        $('#body-detail-cost').empty().append(`
                                            <tr id="last-row-detail-cost">
                                                <td colspan="14">
                                                    Pilih partner bisnis untuk memulai...
                                                </td>
                                            </tr>
                                        `);
                                        $('#cost-tab').addClass('hide');
                                    }

                                    if(!$('#temp').val()){
                                        $('#currency_id').val(val.currency_id).formSelect();
                                        $('#currency_rate').val(val.currency_rate);
                                        $('#account_no').val(val.no_account);
                                        $('#account_bank').val(val.bank_account);
                                        $('#account_name').val(val.name_account);
                                        $('#note').val(val.remark);
                                        if(val.is_reimburse){
                                            $('#is_reimburse').val(val.is_reimburse).formSelect();
                                            if(val.raw_due_date){
                                                $('#pay_date').val(val.raw_due_date);
                                            }
                                        }
                                    }
                                });
                                
                            }else{
                                $('#body-detail').empty().append(`
                                    <tr id="empty-detail">
                                        <td colspan="11">
                                            Pilih partner bisnis untuk memulai...
                                        </td>
                                    </tr>
                                `);

                                $('#grandtotal,#admin').val('0,00');
                            }

                            $('#body-detail-payment').empty();
                            if(response.payments.length > 0){
                                $.each(response.payments, function(i, val) {
                                    var count = makeid(10);
                                    $('#list-used-data').append(`
                                        <div class="chip purple darken-4 gradient-shadow white-text">
                                            ` + val.rawcode + `
                                            <i class="material-icons close data-used" onclick="removeUsedData('` + val.type + `',` + val.id + `,'` + val.rawcode + `')">close</i>
                                        </div>
                                    `);
                                    $('#body-detail-payment').append(`
                                        <tr class="row_detail_payment" data-id="` + val.id + `" data-code="` + val.rawcode + `">
                                            <td class="center-align">
                                                <label>
                                                    <input type="checkbox" id="arr_cd_payment` + count + `" name="arr_cd_payment[]" value="` + val.id + `" onclick="countAll();" data-id="` + count + `" checked>
                                                    <span>Pilih</span>
                                                </label>
                                            </td>
                                            <td class="center">
                                                ` + val.code + `
                                            </td>
                                            <td class="center">
                                                ` + val.payment_request_code + `
                                            </td>
                                            <td>
                                                ` + val.name + `
                                            </td>
                                            <td>
                                                ` + val.post_date + `
                                            </td>
                                            <td>
                                                ` + val.coa_name + `
                                            </td>
                                            <td class="right-align">
                                                ` + val.admin + `
                                            </td>
                                            <td class="right-align">
                                                ` + val.total + `
                                            </td>
                                            <td class="right-align">
                                                ` + val.grandtotal + `
                                            </td>
                                            <td class="center-align">
                                                <input id="arr_payment` + count + `" name="arr_payment[]" onfocus="emptyThis(this);" data-balance="` + val.balance + `" class="browser-default" type="text" value="`+ val.balance + `" onkeyup="formatRupiah(this);countAll();checkTotal(this);" style="width:150px;text-align:right;">
                                            </td>
                                        </tr>
                                    `);
                                });
                            }else{
                                $('#body-detail-payment').append(`
                                    <tr id="empty-detail-payment">
                                        <td colspan="10" class="center">
                                            Pilih partner bisnis untuk memulai...
                                        </td>
                                    </tr>
                                `);
                            }
                            
                            $('.modal-content').scrollTop(0);
                            M.updateTextFields();

                            $('#modal6').modal('close');
                            countAll();
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
                        title: 'Ups! Hayo.',
                        text: 'Maaf, dokumen yang ingin dipakai STATUS harus sama, tidak boleh berbeda.',
                        icon: 'warning'
                    });
                }
            }
        });
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
        var nil = parseFloat($(element).val().replaceAll(".", "").replaceAll(",",".")), max = 0;
        if($(element).data('grandtotal')){
            max = parseFloat($(element).data('grandtotal').replaceAll(".", "").replaceAll(",","."));
        }
        if($(element).data('balance')){
            max = parseFloat($(element).data('balance').replaceAll(".", "").replaceAll(",","."));
        }
        if(nil > max){
            $(element).val($(element).data('grandtotal'));
        }
    }

    function countAfterLoadCurrency(){
        setTimeout(function() {
            countAll();
        }, 1000);
    }

    function countAll(){

        var total = 0, rounding = parseFloat($('#rounding').val().replaceAll(".", "").replaceAll(",",".")), admin = parseFloat($('#admin').val().replaceAll(".", "").replaceAll(",",".")), grandtotal = 0, payment = 0, balance = 0;
        
        $('input[name^="arr_cd_payment"]').each(function(){
            if($(this).is(':checked')){
                payment += parseFloat($('#arr_payment' + $(this).data('id')).val().replaceAll(".", "").replaceAll(",","."));
            }
        });

        if($('#main-tab').hasClass('active')){
            $('input[name^="arr_pay[]"]').each(function(){
                total += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
            });
        }

        if(!$('#cost-tab').hasClass('hide')){
            let currencyRate = parseFloat($('#currency_rate').val().replaceAll(".", "").replaceAll(",","."));
            $('input[name^="arr_nominal_debit_fc[]"]').each(function(index){
                let nominal = parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
                let nominalConvert = currencyRate * nominal;
                $('input[name^="arr_nominal_debit[]"]').eq(index).val(
                    (nominalConvert >= 0 ? '' : '-') + formatRupiahIni(nominalConvert.toFixed(2).toString().replace('.',','))
                );
            });
            $('input[name^="arr_nominal_credit_fc[]"]').each(function(index){
                let nominal = parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
                let nominalConvert = currencyRate * nominal;
                $('input[name^="arr_nominal_credit[]"]').eq(index).val(
                    (nominalConvert >= 0 ? '' : '-') + formatRupiahIni(nominalConvert.toFixed(2).toString().replace('.',','))
                );
            });
        }

        grandtotal = total + admin + rounding;
        balance = grandtotal - payment;
        $('#total').val(formatRupiahIni(total.toFixed(2).toString().replace('.',',')));
        $('#grandtotal').val(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(grandtotal.toFixed(2).toString().replace('.',','))
        );
        $('#payment').val(
            (payment >= 0 ? '' : '-') + formatRupiahIni(payment.toFixed(2).toString().replace('.',','))
        );
        $('#balance').val(
            (balance >= 0 ? '' : '-') + formatRupiahIni(balance.toFixed(2).toString().replace('.',','))
        );
        
    }

    function chooseAllGas(element){
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

    function chooseAllOtherPayment(element){
        if($(element).is(':checked')){
            $('input[name^="arr_cd_payment"]').each(function(){
                if(!$(this).is(':checked')){
                    $(this).prop( "checked", true);
                }
            });
        }else{
            $('input[name^="arr_cd_payment"]').each(function(){
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
                $('.row_detail_payment[data-code="' + code + '"]').remove();
                if($('.row_detail').length == 0){
                    $('#body-detail').empty().append(`
                        <tr id="empty-detail">
                            <td colspan="11">
                                Pilih partner bisnis untuk memulai...
                            </td>
                        </tr>
                    `);
                }
                
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
                    'account_id[]' : $('#filter_account').val(),
                    company_id : $('#filter_company').val(),
                    'currency_id[]' : $('#filter_currency').val(),
                    start_date : $('#start_date').val(),
                    finish_date : $('#finish_date').val(),
                    'modedata' : '{{ $modedata }}',
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
                { name: 'user_id', className: '' },
                { name: 'account_id', className: '' },
                { name: 'company_id', className: '' },
                { name: 'coa_source_id', className: '' },
                { name: 'payment_type', className: 'center-align' },
                { name: 'payment_no', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'pay_date', className: 'center-align' },
                { name: 'currency_id', className: 'center-align' },
                { name: 'currency_rate', className: 'center-align' },
                { name: 'total', className: 'right-align' },
                { name: 'rounding', className: 'right-align' },
                { name: 'admin', className: 'right-align' },
                { name: 'grandtotal', className: 'right-align' },
                { name: 'payment', className: 'right-align' },
                { name: 'balance', className: 'right-align' },
                { name: 'document', className: 'center-align' },
                { name: 'account_bank', className: '' },
                { name: 'account_no', className: '' },
                { name: 'account_name', className: '' },
                { name: 'note', className: '' },
                { name: 'is_reimburse', className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'cash_bank_out', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
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
                "info": "Menampilkan halaman _PAGE_ / _PAGES_ dari total _TOTAL_ data",
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

    function checkRekening(){
        arrRekening = [];
        $('input[name^="arr_code"]').each(function(){
            if($(this).is(':checked')){
                if($('input[name^="arr_account_no[]"][data-id="' + $(this).data('id') + '"]').val()){
                    let checkIndex = checkArray(arrRekening,$('input[name^="arr_account_no[]"][data-id="' + $(this).data('id') + '"]').val());
                    if(checkIndex < 0){
                        arrRekening.push($('input[name^="arr_account_no[]"][data-id="' + $(this).data('id') + '"]').val());
                    }
                }
            }
        });
        if(arrRekening.length > 1){
            return false;
        }else{
            return true;
        }
    }

    function checkArray(arr,val){
        let index = -1;
        for(let i = 0;i<arr.length;i++){
            if(val == arr[i]){
                index = i;
            }
        }
        return index;
    }

    function save(){
        let rekeningWarning = '';
        if(checkRekening() == false){
            rekeningWarning = 'Terdapat lebih dari 1 data rekening yaitu : ' + arrRekening.join(", ");
        }
		swal({
            title: "Apakah anda yakin ingin simpan?",
            text: rekeningWarning,
            icon: 'warning',
            dangerMode: true,
            buttons: {
                cancel: 'Tidak, jangan!',
                delete: 'Ya, lanjutkan!',
            }
        }).then(function (willDelete) {
            if (willDelete) {

                let passedCoaCost = true, passedCostWithBs = true;

                $('select[name^="arr_coa_cost[]"]').each(function(index){
                    if(!$(this).val()){
                        passedCoaCost = false;
                    }
                });

                if(!$('#cost-tab').hasClass('hide')){
                    let grandtotal = parseFloat($('#total').val().replaceAll(".", "").replaceAll(",","."));
                    $('input[name^="arr_nominal_debit_fc[]"]').each(function(index){
                        let nominal = parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
                        grandtotal -= nominal;
                    });
                    $('input[name^="arr_nominal_credit_fc[]"]').each(function(index){
                        let nominal = parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
                        grandtotal += nominal;
                    });
                    if(grandtotal !== 0){
                        passedCostWithBs = false;
                    }
                }

                if(passedCoaCost){
                    if(passedCostWithBs){
                        var formData = new FormData($('#form_data')[0]);

                        formData.delete("arr_cost_distribution_cost[]");
                        formData.delete("arr_line[]");
                        formData.delete("arr_machine[]");
                        formData.delete("arr_division[]");
                        formData.delete("arr_project[]");

                        $('select[name^="arr_cost_distribution_cost[]"]').each(function(index){
                            formData.append("arr_cost_distribution_cost[]",($(this).val() ? $(this).val() : ''));
                            formData.append("arr_line[]",($('select[name^="arr_line[]"]').eq(index).val() ? $('select[name^="arr_line[]"]').eq(index).val() : ''));
                            formData.append("arr_machine[]",($('select[name^="arr_machine[]"]').eq(index).val() ? $('select[name^="arr_machine[]"]').eq(index).val() : ''));
                            formData.append("arr_division[]",($('select[name^="arr_division[]"]').eq(index).val() ? $('select[name^="arr_division[]"]').eq(index).val() : ''));
                        });

                        $('select[name^="arr_project[]"]').each(function(index){
                            formData.append("arr_project[]",($(this).val() ? $(this).val() : ''));
                        });

                        var path = window.location.pathname;
                        path = path.replace(/^\/|\/$/g, '');
                        
                        var segments = path.split('/');
                        var lastSegment = segments[segments.length - 1];
                    
                        formData.append('lastsegment',lastSegment);
                        
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
                                loadingOpen('#modal1');
                            },
                            success: function(response) {
                                loadingClose('#modal1');
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
                                    
                                    swal({
                                        title: 'Ups! Validation',
                                        text: 'Check your form.',
                                        icon: 'warning'
                                    });
                                    $.each(response.error, function(field, errorMessage) {
                                        $('#' + field).addClass('error-input');
                                        $('#' + field).css('border', '1px solid red');
                                        
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
                                loadingClose('#modal1');
                                swal({
                                    title: 'Ups!',
                                    text: 'Check your internet connection.',
                                    icon: 'error'
                                });
                            }
                        });
                    }else{
                        swal({
                            title: 'Ups! hayo',
                            text: 'Terdapat selisih antara daftar biaya dengan nominal dokumen terpakai.',
                            icon: 'warning'
                        });
                    }
                }else{
                    swal({
                        title: 'Ups! hayo',
                        text: 'Coa Biaya tidak boleh kosong',
                        icon: 'warning'
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
                $('#temp').val(id);
                $('#modal1').modal('open');
                $('#code_place_id').val(response.code_place_id).formSelect();
                $('#code').val(response.code);
                $('#account_id').empty().append(`
                    <option value="` + response.account_id + `">` + response.account_name + `</option>
                `);
                if(response.coa_source_name){
                    $('#coa_source_id').empty().append(`
                        <option value="` + response.coa_source_id + `">` + response.coa_source_name + `</option>
                    `);
                }
                if(response.cost_distribution_name){
                    $('#cost_distribution_id').empty().append(`
                        <option value="` + response.cost_distribution_id + `">` + response.cost_distribution_name + `</option>
                    `);
                }
                $('#company_id').val(response.company_id).formSelect();
                $('#payment_no').val(response.payment_no);
                $('#currency_id').val(response.currency_id).formSelect();
                $('#payment_type').val(response.payment_type).formSelect();
                $('#currency_rate').val(response.currency_rate);
                $('#post_date').val(response.post_date);
                $('#pay_date').val(response.pay_date);                
                $('#note').val(response.note);
                $('#account_bank').val(response.account_bank);
                $('#account_no').val(response.account_no);
                $('#account_name').val(response.account_name);
                $('#total').val(response.total);
                $('#rounding').val(response.rounding);
                $('#admin').val(response.admin);
                $('#grandtotal').val(response.grandtotal);
                $('#payment').val(response.payment);
                $('#balance').val(response.balance);
                $('#is_reimburse').val(response.is_reimburse).trigger('change').formSelect();
                
                if(response.details.length > 0){
                    $('#body-detail').empty();
                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        $('#body-detail').append(`
                            <tr class="row_detail" data-code="` + val.rawcode + `" data-account="` + val.account_code + `">
                                <input type="hidden" name="arr_id[]" value="` + val.id + `" data-id="` + count + `">
                                <input type="hidden" name="arr_type[]" value="` + val.type + `" data-id="` + count + `">
                                <input type="hidden" name="arr_account_bank[]" value="` + val.bank_account + `" data-id="` + count + `">
                                <input type="hidden" name="arr_account_no[]" value="` + val.no_account + `" data-id="` + count + `">
                                <input type="hidden" name="arr_account_name[]" value="` + val.name_account + `" data-id="` + count + `">
                                <input type="hidden" name="arr_code[]" value="` + val.code + `">
                                <input type="hidden" name="arr_coa[]" value="` + val.coa_id + `"">
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
                                    <input id="arr_pay` + count + `" name="arr_pay[]" data-grandtotal="` + val.balance + `" onfocus="emptyThis(this);" class="browser-default" type="text" value=" `+ val.nominal + `" onkeyup="formatRupiah(this);countAll();checkTotal(this);" style="width:150px;text-align:right;">
                                </td>
                                <td class="center">
                                    <input id="arr_note` + count + `" name="arr_note[]" class="browser-default" type="text" style="width:350px;" value="` + val.note + `">
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                        
                    });
                }

                $('#body-detail-payment').empty();
                if(response.payments.length > 0){
                    $.each(response.payments, function(i, val) {
                        var count = makeid(10);
                        $('#body-detail-payment').append(`
                            <tr data-id="` + val.id + `">
                                <td class="center-align">
                                    <label>
                                        <input type="checkbox" id="arr_cd_payment` + count + `" name="arr_cd_payment[]" value="` + val.id + `" onclick="countAll();" data-id="` + count + `" checked>
                                        <span>Pilih</span>
                                    </label>
                                </td>
                                <td class="center">
                                    ` + val.code + `
                                </td>
                                <td class="center">
                                    ` + val.payment_request_code + `
                                </td>
                                <td>
                                    ` + val.name + `
                                </td>
                                <td>
                                    ` + val.post_date + `
                                </td>
                                <td>
                                    ` + val.coa_name + `
                                </td>
                                <td class="right-align">
                                    ` + val.admin + `
                                </td>
                                <td class="right-align">
                                    ` + val.total + `
                                </td>
                                <td class="right-align">
                                    ` + val.grandtotal + `
                                </td>
                                <td class="center-align">
                                    <input id="arr_payment` + count + `" name="arr_payment[]" onfocus="emptyThis(this);" data-balance="` + val.balance + `" class="browser-default" type="text" value="`+ val.nominal + `" onkeyup="formatRupiah(this);countAll();checkTotal(this);" style="width:150px;text-align:right;">
                                </td>
                            </tr>
                        `);
                    });
                }else{
                    $('#body-detail-payment').append(`
                        <tr id="empty-detail-payment">
                            <td colspan="10" class="center">
                                Pilih partner bisnis untuk memulai...
                            </td>
                        </tr>
                    `);
                }

                if(response.costs.length > 0){
                    $('#cost-tab').removeClass('hide');
                    $('#body-detail-cost').empty();
                    $.each(response.costs, function(i, val) {
                        var countdetail = makeid(10);
                        $('#body-detail-cost').append(`
                            <tr class="row_detail">
                                <td class="">
                                    <select class="browser-default" id="arr_coa_cost` + countdetail + `" name="arr_coa_cost[]"></select>
                                </td>
                                <td class="center">
                                    <select class="browser-default" id="arr_cost_distribution_cost` + countdetail + `" name="arr_cost_distribution_cost[]"></select> 
                                </td>
                                <td class="center">
                                    <select class="browser-default" id="arr_place` + countdetail + `" name="arr_place[]">
                                        @foreach ($place as $rowplace)
                                            <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_line` + countdetail + `" name="arr_line[]" onchange="changePlace(this);">
                                        <option value="">--Kosong--</option>
                                        @foreach ($line as $rowline)
                                            <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                                        @endforeach
                                    </select>    
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_machine` + countdetail + `" name="arr_machine[]" onchange="changeLine(this);">
                                        <option value="">--Kosong--</option>
                                        @foreach ($machine as $rowmachine)
                                            <option value="{{ $rowmachine->id }}" data-line="{{ $rowmachine->line_id }}">{{ $rowmachine->name }}</option>
                                        @endforeach
                                    </select>    
                                </td>
                                <td class="center">
                                    <select class="browser-default" id="arr_division` + countdetail + `" name="arr_division[]">
                                        <option value="">--Kosong--</option>
                                        @foreach ($department as $row)
                                            <option value="{{ $row->id }}">{{ $row->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="center">
                                    <select class="browser-default" id="arr_project` + countdetail + `" name="arr_project[]"></select>
                                </td>
                                <td>
                                    <input type="text" name="arr_note_cost[]" placeholder="Keterangan 1..." value="` + val.note + `" data-id="` + countdetail + `">
                                </td>
                                <td>
                                    <input type="text" name="arr_note_cost2[]" placeholder="Keterangan 2..." value="` + val.note2 + `" data-id="` + countdetail + `">
                                </td>
                                <td class="center">
                                    <input class="browser-default" type="text" name="arr_nominal_debit_fc[]" value="` + val.nominal_debit_fc + `" data-id="` + countdetail + `" onkeyup="formatRupiah(this);countAll();">
                                </td>
                                <td class="right-align">
                                    <input class="browser-default" type="text" name="arr_nominal_credit_fc[]" value="` + val.nominal_credit_fc + `" data-id="` + countdetail + `" onkeyup="formatRupiah(this);countAll();">
                                </td>
                                <td class="right-align">
                                    <input class="browser-default" type="text" name="arr_nominal_debit[]" value="` + val.nominal_debit + `" data-id="` + countdetail + `" onkeyup="formatRupiah(this);" readonly>
                                </td>
                                <td class="right-align">
                                    <input class="browser-default" type="text" name="arr_nominal_credit[]" value="` + val.nominal_credit + `" data-id="` + countdetail + `" onkeyup="formatRupiah(this);" readonly>
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                        if(val.coa_id){
                            $('#arr_coa_cost' + countdetail).append(`
                                <option value="` + val.coa_id + `">` + val.coa_name + `</option>
                            `);
                        }
                        if(val.cost_distribution_id){
                            $('#arr_cost_distribution_cost' + countdetail).append(`
                                <option value="` + val.cost_distribution_id + `">` + val.cost_distribution_name + `</option>
                            `);
                        }
                        if(val.place_id){
                            $('#arr_place' + countdetail).val(val.place_id);
                        }
                        if(val.line_id){
                            $('#arr_line' + countdetail).val(val.line_id);
                        }
                        if(val.machine_id){
                            $('#arr_machine' + countdetail).val(val.machine_id);
                        }
                        if(val.division_id){
                            $('#arr_division' + countdetail).val(val.division_id);
                        }
                        if(val.project_id){
                            $('#arr_project' + countdetail).append(`
                                <option value="` + val.project_id + `">` + val.project_name + `</option>
                            `);
                        }
                        select2ServerSide('#arr_coa_cost' + countdetail, '{{ url("admin/select2/coa") }}');
                        select2ServerSide('#arr_project' + countdetail, '{{ url("admin/select2/project") }}');
                        select2ServerSide('#arr_cost_distribution_cost' + countdetail, '{{ url("admin/select2/cost_distribution") }}');
                    });
                }

                $('#user_bank_id').empty();
                if(response.banks.length > 0){
                    $('#user_bank_id').append(`
                        <option value="">--Pilih dari daftar-</option>
                    `);
                    $.each(response.banks, function(i, val) {
                        $('#user_bank_id').append(`
                            <option value="` + val.id + `" data-name="` + val.name + `" data-bank="` + val.bank_name + `" data-no="` + val.no + `">` + val.bank_name + ` - ` + val.no + ` - ` + val.name + `</option>
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

    var printService = new WebSocketPrinter({
        onConnect: function () {
            
        },
        onDisconnect: function () {
            /* M.toast({
                html: 'Aplikasi penghubung printer tidak terinstall. Silahkan hubungi tim EDP.'
            }); */
        },
        onUpdate: function (message) {
            
        },
    });
    
    function printData(){
        var arr_id_temp=[];
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
            },
            success: function(response) {
                printService.submit({
                    'type': 'INVOICE',
                    'url': response.message
                });
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

    function printMultiSelect(){
        var formData = new FormData($('#form_data_print_multi')[0]);
        var table = $('#datatable_serverside').DataTable();
        var data = table.data().toArray();
        var etNumbers = data.map(item => item[1]);
        var path = window.location.pathname;
        path = path.replace(/^\/|\/$/g, '');

        
        var segments = path.split('/');
        var lastSegment = segments[segments.length - 1];
        formData.append('tabledata',etNumbers);
        formData.append('lastsegment',lastSegment);
        swal({
            title: "Apakah Anda ingin mengeprint dokumen ini?",
            text: "pastikan bahwa isian sudah benar.",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
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
                    $('#currency_id_pay').val(response.data.currency_id).formSelect();
                    $('#currency_rate_pay').val(response.data.currency_rate);
                    $('#notePay').val(response.data.note);
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
                
                
            } else if (part instanceof go.Node) {
                window.open(part.data.url);
                if (part.isTreeExpanded) {
                    part.collapseTree();
                } else {
                    part.expandTree();
                }
                
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
        

        myDiagram.addDiagramListener("InitialLayoutCompleted", function(e) {
        setTimeout(function() {
            
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
            e.diagram.nodes.each(node => {
                node.findTreeChildrenNodes().each(child => child.expandTree(10));
            });
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

    function startIntro(){
        introJs().setOptions({
            exitOnOverlayClick : false,
            steps: [
                {
                    title : 'Payment Request',
                    intro : 'Form ini digunakan untuk mengelola data permintaan bayar BS Karyawan, Pinjaman Karyawan, AP DP, AP Invoice, AR Memo, dan Rekonsiliasi Piutang Karyawan.'
                },
                {
                    title : 'Nomor Dokumen',
                    element : document.querySelector('.step1'),
                    intro : 'Nomor dokumen wajib diisikan, dengan kombinasi 4 huruf kode dokumen, tahun pembuatan dokumen, kode plant, serta nomor urut. Nomor ini bersifat unik, tidak akan sama, dan nomor urut paling belakang akan ter-reset secara otomatis berdasarkan tahun tanggal post.'
                },
                {
                    title : 'Kode Plant',
                    element : document.querySelector('.step2'),
                    intro : 'Kode plant dimana dokumen dibuat'
                },
                {
                    title : 'Partner Bisnis',
                    element : document.querySelector('.step3'),
                    intro : 'Partner bisnis yang berkaitan dengan form' 
                },
                {
                    title : 'Tipe Pembayaran',
                    element : document.querySelector('.step4'),
                    intro : 'Jenis pembayaran yang digunakan dalam form ini' 
                },
                {
                    title : 'Kas / Bank',
                    element : document.querySelector('.step5'),
                    intro : 'COA bank yang akan digunakan dalam form ini.' 
                },
                {
                    title : 'Perusahaan',
                    element : document.querySelector('.step6'),
                    intro : 'Perusahaan dimana dokumen ini dibuat.' 
                },
                {
                    title : 'No. CEK/BG',
                    element : document.querySelector('.step7'),
                    intro : 'No Cek pembayaran terkait form ini.' 
                },
                {
                    title : 'Tgl. Posting',
                    element : document.querySelector('.step7_1'),
                    intro : 'Tanggal post akan menentukan tanggal jurnal untuk beberapa form yang terhubung dengan jurnal. Hati - hati dalam menentukan tanggal posting.' 
                },
                {
                    title : 'TOP (hari) Autofill',
                    element : document.querySelector('.step8'),
                    intro : 'Hari didapatkan dari inputan yang dipilih sebelumnya' 
                },
                {
                    title : 'Tgl. Bayar',
                    element : document.querySelector('.step9'),
                    intro : 'Tanggal pembayaran form ditentukan.' 
                },
                {
                    title : 'File Lampiran',
                    element : document.querySelector('.step10'),
                    intro : 'Silahkan unggah file lampiran. untuk saat ini hanya bisa mengakomodir 1 file lampiran saja. Jika ingin menambahkan file lebih dari 1, silahkan gabungkan file anda menjadi pdf.' 
                },
                {
                    title : 'Mata Uang',
                    element : document.querySelector('.step11'),
                    intro : 'Mata uang, silahkan pilih mata uang lain, untuk mata uang asing.' 
                },
                {
                    title : 'Konversi',
                    element : document.querySelector('.step12'),
                    intro : 'Nilai konversi rupiah pada saat dokumen dibuat. Nilai konversi secara otomatis diisi ketika form tambah baru dibuka pertama kali dan data diambil dari situs exchangerate.host. Pastikan kode mata uang benar di master data agar nilai konversi tidak error.'
                },
                {
                    title : 'Reimburse',
                    element : document.querySelector('.step13'),
                    intro : 'Pilih Ya jika Payment Request ini adalah klaim reimburse karyawan dan memunculkan no rekening seluruh pegawai. Sebaliknya pilih Tidak, jika payment request bukan reimburse dan daftar rekening yang muncul adalah rekening milih Partner Bisnis terpilih.'
                },
                {
                    title : 'Pilih Partner Bisnis Rekening',
                    element : document.querySelector('.step14'),
                    intro : 'Pemilihan partner bisnis untuk memilih bank tujuan.'
                },
                {
                    title : 'Bank Tujuan',
                    element : document.querySelector('.step15'),
                    intro : 'Pemilihan bank untuk proses transfer dengan form terkait.'
                },
                {
                    title : 'No rekening',
                    element : document.querySelector('.step16'),
                    intro : 'No Rekening dari bank yang dipilih.'
                },
                {
                    title : 'Nama Pemilik Rekening',
                    element : document.querySelector('.step17'),
                    intro : 'Nama pemilik rekening dari no rekening yang diinput'
                },
                {
                    title : 'Outgoing Payment Piutang Karyawan',
                    element : document.querySelector('.step18'),
                    intro : 'List OP yang dibayar jika ada' 
                },
                {
                    title : 'Rekonsiliasi Biaya',
                    element : document.querySelector('.step19'),
                    intro : 'List data piutang yang ingin dibayar.' 
                },
                {
                    title : 'Keterangan',
                    element : document.querySelector('.step20'),
                    intro : 'Silahkan isi / tambahkan keterangan untuk dokumen ini untuk dimunculkan di bagian bawah tabel detail produk nantinya, ketika dicetak.' 
                },
                {
                    title : 'Rincian Biaya',
                    element : document.querySelector('.step21'),
                    intro : 'Disini rincian biaya bisa dimasukkan sesuai dokumen yang ada. Fitur ini juga mengakomodir PPN dan PPh.' 
                },
                {
                    title : 'Tombol Simpan',
                    element : document.querySelector('.step22'),
                    intro : 'Silahkan tekan tombol ini untuk menyimpan data, namun pastikan data yang akan anda masukkan benar.' 
                },
            ]
        })
        .onbeforechange(function(targetElement){
            if(this._currentStep == '13'){
                $('#payment_type').val('2').change();
            }
            if(this._currentStep == '19'){
              
                window.scrollTo(0, 0);
                document.getElementById('opdata').style.display = 'none';
                document.getElementById('costdata').style.display = 'block';
            }
        })
        .start();
    }

    function whatPrinting(code){
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
                window.open(data, '_blank');
            }
        });
    }

    function viewJournal(id){
        $.ajax({
            url: '{{ Request::url() }}/view_journal/' + id,
            type:'GET',
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            complete: function() {
                
            },
            success: function(data){
                loadingClose('.modal-content');
                if(data.status == '500'){
                    M.toast({
                        html: data.message
                    });
                }else{
                    $('#modal7').modal('open');
                    $('#title_data').append(``+data.title+``);
                    $('#code_data').append(data.message.code);
                    $('#body-journal-table').append(data.tbody);
                    $('#user_jurnal').append(`Pengguna : `+data.user);
                    $('#note_jurnal').append(`Keterangan : `+data.message.note);
                    $('#ref_jurnal').append(`Referensi : `+data.reference);
                    $('#company_jurnal').append(`Perusahaan : `+data.company);
                    $('#post_date_jurnal').append(`Tanggal : `+data.message.post_date);
                }
            }
        });
    }
</script>