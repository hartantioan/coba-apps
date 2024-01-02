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
                                                        <th rowspan="2" class="center-align">#</th>
                                                        <th rowspan="2" class="center-align">Code</th>
                                                        <th rowspan="2" class="center-align">Pengguna</th>
                                                        <th rowspan="2" class="center-align">Partner Bisnis</th>
                                                        <th rowspan="2" class="center-align">Perusahaan</th>
                                                        <th rowspan="2" class="center-align">Kas/Bank</th>
                                                        <th rowspan="2" class="center-align">Tanggal</th>
                                                        <th colspan="2" class="center-align">Mata Uang</th>
                                                        <th rowspan="2" class="center-align">Grandtotal</th>
                                                        <th rowspan="2" class="center-align">Dokumen</th>
                                                        <th rowspan="2" class="center-align">Keterangan</th>
                                                        <th rowspan="2" class="center-align">Status</th>
                                                        <th rowspan="2" class="center-align">Action</th>
                                                    </tr>
                                                    <tr>
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
                                    <select class="form-control" id="company_id" name="company_id">
                                        @foreach ($company as $rowcompany)
                                            <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="" for="company_id">Perusahaan</label>
                                </div>
                                <div class="input-field col m3 s12 step4">
                                    <select class="browser-default" id="account_id" name="account_id" onchange="getAccountInfo();"></select>
                                    <label class="active" for="account_id">Partner Bisnis</label>
                                </div>
                                <div class="input-field col m3 s12 step5">
                                    <select class="browser-default" id="coa_id" name="coa_id"></select>
                                    <label class="active" for="coa_id">Kas / Bank</label>
                                </div>
                                <div class="input-field col m3 s12 step6">
                                    <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);">
                                    <label class="active" for="post_date">Tgl. Posting</label>
                                </div>
                                <div class="file-field input-field col m3 s12 step8">
                                    <div class="btn">
                                        <span>Lampiran</span>
                                        <input type="file" name="document" id="document">
                                    </div>
                                    <div class="file-path-wrapper">
                                        <input class="file-path validate" type="text">
                                    </div>
                                </div>
                                <div class="input-field col m3 s12 step9">
                                    <select class="form-control" id="currency_id" name="currency_id" onchange="loadCurrency();">
                                        @foreach ($currency as $row)
                                            <option value="{{ $row->id }}" data-code="{{ $row->code }}">{{ $row->code.' '.$row->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="" for="currency_id">Mata Uang</label>
                                </div>
                                <div class="input-field col m3 s12 step10">
                                    <input id="currency_rate" name="currency_rate" type="text" value="1" onkeyup="formatRupiah(this)">
                                    <label class="active" for="currency_rate">Konversi</label>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col s12">
                        <fieldset>
                            <legend>2. Daftar Dokumen Terpakai</legend>
                            <div class="row">
                                <div class="col m12 s12">
                                    <h6><b>Data Terpakai</b> : <i id="list-used-data"></i></h6>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col s12">
                        <fieldset>
                            <legend>3. Detail Dokumen dan Nominal</legend>
                            <div class="row">
                                <div class="col m12 s12 step12">
                                    <p class="mt-2 mb-2">
                                        <h6>Detail AR Invoice / AR Down Payment / AR Credit Memo / BS.Karyawan / Coa</h6>
                                        <div style="overflow:auto;">
                                            <table class="bordered" style="max-width:1650px !important;" id="table-detail">
                                                <thead>
                                                    <tr>
                                                        <th class="center">Referensi</th>
                                                        <th class="center">Tgl.Post</th>
                                                        <th class="center">Tgl.Tenggat</th>
                                                        <th class="center">Total</th>
                                                        <th class="center">Pembulatan</th>
                                                        <th class="center">Subtotal</th>
                                                        <th class="center">Dist.Biaya</th>
                                                        <th class="center">Keterangan</th>
                                                        <th class="center">Hapus</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="body-detail">
                                                    <tr id="last-row-detail">
                                                        <td colspan="9" class="center">
                                                            <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addItem()" href="javascript:void(0);">
                                                                <i class="material-icons left">add</i> Tambah Baris
                                                            </a>
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
                            <legend>4. Keterangan dan Total</legend>
                            <div class="row">
                                <div class="input-field col m4 s12 step13">
                                    <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                    <label class="active" for="note">Keterangan</label>
                                </div>
                                <div class="input-field col m4 s12">

                                </div>
                                <div class="input-field col m4 s12 step14">
                                    <table width="100%" class="bordered">
                                        <thead>
                                            <tr>
                                                <td>Total</td>
                                                <td class="right-align">
                                                    <input class="browser-default" id="grandtotal" name="grandtotal" onfocus="emptyThis(this);" type="text" value="0,00" onkeyup="formatRupiah(this);" style="text-align:right;width:100%;" readonly>
                                                </td>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="col s12 mt-3 step15">
                                    <button class="btn waves-effect waves-light right submit" onclick="save();">Simpan <i class="material-icons right">send</i></button>
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
                        <ul class="collapsible">
                            <li class="active" id="employee-tab">
                                <div class="collapsible-header purple lightrn-1 white-text">
                                    <i class="material-icons">layers</i> BS Karyawan
                                </div>
                                <div class="collapsible-body">
                                    <div id="datatable_buttons_multi"></div>
                                    <i class="right">Gunakan *pilih semua* untuk memilih seluruh data yang anda inginkan. Atau pilih baris untuk memilih data yang ingin dipindahkan.</i>
                                    <table id="table_multi" class="display" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="center">No.OP</th>
                                                <th class="center">No.Pay.Req</th>
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
                                        <tbody id="body-detail-multi"></tbody>
                                    </table>
                                </div>
                            </li>
                            <li class="active" id="other-tab">
                                <div class="collapsible-header purple lightrn-1 white-text">
                                    <i class="material-icons">layers</i> AR Invoice / AR Down Payment / AR Credit Memo
                                </div>
                                <div class="collapsible-body">
                                    <div id="datatable_buttons_multi_other"></div>
                                    <i class="right">Gunakan *pilih semua* untuk memilih seluruh data yang anda inginkan. Atau pilih baris untuk memilih data yang ingin dipindahkan.</i>
                                    <table id="table_multi_other" class="display" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="center">No.Dokumen</th>
                                                <th class="center">Tgl.Post</th>
                                                <th class="center">Total Tagihan</th>
                                                <th class="center">Total Memo/Terpakai/Dibayar</th>
                                                <th class="center">Total Sisa</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-detail-multi-other"></tbody>
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

<div id="modal3" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
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
        </div>
        <div class="row mt-2">
            <table class="bordered Highlight striped">
                <thead>
                        <tr>
                            <th class="center-align">No</th>
                            <th class="center-align">Coa</th>
                            <th class="center-align">Perusahaan</th>
                            <th class="center-align">Partner Bisnis</th>
                            <th class="center-align">Plant</th>
                            <th class="center-align">Line</th>
                            <th class="center-align">Mesin</th>
                            <th class="center-align">Department</th>
                            <th class="center-align">Gudang</th>
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
        $("#table-detail th").resizable({
            minWidth: 100,
        });

        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        $('#modal3').modal({
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
                $('#post_date_jurnal').empty();
            }
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
                if(!$('#temp').val()){
                    loadCurrency();
                }
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                $('input').css('border', 'none');
                $('input').css('border-bottom', '0.5px solid black');
                M.updateTextFields();
                $('.row_detail').remove();
                $('#account_id,#coa_id').empty();
                $('#total,#wtax,#grandtotal').text('0,00');
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }
                window.onbeforeunload = function() {
                    return null;
                };
                countAll();
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
                
            }
        });

        $('#modal6').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                $('.collapsible').collapsible({
                    accordion:false
                });
            },
            onOpenEnd: function(modal, trigger) {
                table_multi = $('#table_multi').DataTable({
                    "responsive": true,
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

                table_multi_other = $('#table_multi_other').DataTable({
                    "responsive": true,
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
                $('#table_multi_other_wrapper > .dt-buttons').appendTo('#datatable_buttons_multi_other');
                $('select[name="table_multi_other_length"]').addClass('browser-default');
            },
            onCloseEnd: function(modal, trigger){
                $('#body-detail-multi,#body-detail-multi-other').empty();
                $('#table_multi').DataTable().clear().destroy();
                $('#table_multi_other').DataTable().clear().destroy();
                var instance = M.Collapsible.getInstance($('.collapsible')); 
                instance.close();
            }
        });

        select2ServerSide('#account_id,#filter_account', '{{ url("admin/select2/employee_customer") }}');
        select2ServerSide('#coa_id', '{{ url("admin/select2/coa_cash_bank") }}');

        $('#body-detail').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
            countAll();
            if($('.row_detail').length == 0){
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }
            }
        });
    });

    function resetBp(){
        $('#account_id').empty();
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

    function addItem(){
        var count = makeid(10);
        /* if($('.data-used').length > 0){
            $('.data-used').trigger('click');
            $('#account_id').empty();
        } */
        $('#last-row-detail').before(`
            <tr class="row_detail">
                <input type="hidden" name="arr_type_item[]" value="coas">
                <td>
                    <select class="browser-default" id="arr_coa_item` + count + `" name="arr_coa_item[]"></select>
                </td>
                <td class="center">
                    -
                </td>
                <td class="center">
                    -
                </td>
                <td class="center">
                    <input id="arr_total_item` + count + `" name="arr_total_item[]" onfocus="emptyThis(this);" data-limit="0" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countAll();" style="width:150px;text-align:right;">
                </td>
                <td class="center">
                    <input id="arr_rounding_item` + count + `" name="arr_rounding_item[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countAll();" style="width:150px;text-align:right;" readonly>
                </td>
                <td class="center">
                    <input id="arr_subtotal_item` + count + `" name="arr_subtotal_item[]" onfocus="emptyThis(this);" data-limit="0" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);" style="width:150px;text-align:right;" readonly>
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_cost_distribution` + count + `" name="arr_cost_distribution[]" onchange="applyCoa('` + count + `');"></select>
                </td>
                <td>
                    <input name="arr_note_item[]" class="materialize-textarea" type="text" placeholder="Keterangan ...">
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        select2ServerSide('#arr_coa_item' + count, '{{ url("admin/select2/coa") }}');
        select2ServerSide('#arr_cost_distribution' + count, '{{ url("admin/select2/cost_distribution") }}');
        countAll();
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
                    $('#body-detail-multi').empty();
                    if(response.details.length > 0){
                        $.each(response.details, function(i, val) {
                            var count = makeid(10);
                            if(val.type == 'outgoing_payments'){
                                $('#body-detail-multi').append(`
                                    <tr data-id="` + val.id + `" data-type="` + val.type + `">
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
                            }else{
                                $('#body-detail-multi-other').append(`
                                    <tr data-id="` + val.id + `" data-type="` + val.type + `">
                                        <td class="center">
                                            ` + val.code + `
                                        </td>
                                        <td>
                                            ` + val.post_date + `
                                        </td>
                                        <td class="right-align">
                                            ` + val.grandtotal + `
                                        </td>
                                        <td class="right-align">
                                            ` + val.memo + `
                                        </td>
                                        <td class="right-align">
                                            ` + val.balance + `
                                        </td>
                                    </tr>
                                `);
                            }
                        });
                    }
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
        }
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
                let passedEmployee = false, arr_id = [], arr_type = [], passedOther = false;
                
                $.map(table_multi.rows('.selected').nodes(), function (item) {
                    passedEmployee = true;
                    arr_id.push($(item).data('id'));
                    arr_type.push($(item).data('type'));
                });

                $.map(table_multi_other.rows('.selected').nodes(), function (item) {
                    passedOther = true;
                    arr_id.push($(item).data('id'));
                    arr_type.push($(item).data('type'));
                });

                if(passedEmployee == true && passedOther == true){
                    swal({
                        title: 'Ups!',
                        text: 'Anda tidak bisa mencampur BS Karyawan dengan AR Invoice / AR Down Payment / AR Credit Memo.',
                        icon: 'warning'
                    });
                }else if(passedEmployee == true || passedOther == true){
                    $.ajax({
                        url: '{{ Request::url() }}/get_account_data',
                        type: 'POST',
                        dataType: 'JSON',
                        data: {
                            arr_id: arr_id,
                            arr_type: arr_type,
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        beforeSend: function() {
                            loadingOpen('.modal-content');
                        },
                        success: function(response) {
                            loadingClose('.modal-content');
                            if(response.details.length > 0){
                                if($('.data-used').length > 0){
                                    $('.data-used').trigger('click');
                                }

                                $.each(response.details, function(i, val) {
                                    var count = makeid(10);
                                    $('#list-used-data').append(`
                                        <div class="chip purple darken-4 gradient-shadow white-text">
                                            ` + val.rawcode + `
                                            <i class="material-icons close data-used" onclick="removeUsedData('` + val.type + `',` + val.id + `,'` + val.rawcode + `')">close</i>
                                        </div>
                                    `);
                                    let readonly = 'readonly';
                                    let array = ['marketing_order_memos'];
                                    if(array.includes(val.type)){
                                        readonly = '';
                                    }
                                    $('#last-row-detail').before(`
                                        <tr class="row_detail" data-id="` + val.id + `" data-code="` + val.rawcode + `">
                                            <input type="hidden" name="arr_id[]" value="` + val.id + `">
                                            <input type="hidden" name="arr_type[]" value="` + val.type + `">
                                            <input type="hidden" name="arr_coa[]" value="` + val.coa_id + `">
                                            <td>
                                                ` + val.code + `
                                            </td>
                                            <td>
                                                ` + val.post_date + `
                                            </td>
                                            <td class="center-align">
                                                -
                                            </td>
                                            <td class="center">
                                                <input id="arr_total` + count + `" name="arr_total[]" onfocus="emptyThis(this);" data-limit="` + val.balance + `" class="browser-default" type="text" value="` + val.balance + `" onkeyup="formatRupiah(this);countRow('` + count + `');countAll();" style="width:150px;text-align:right;" ` + readonly + `>
                                            </td>
                                            <td class="center">
                                                <input id="arr_rounding` + count + `" name="arr_rounding[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0,00" onkeyup="formatRupiah(this);countAll();" style="width:150px;text-align:right;" ` + readonly + `>
                                            </td>
                                            <td class="center">
                                                <input id="arr_subtotal` + count + `" name="arr_subtotal[]" onfocus="emptyThis(this);" data-limit="0" class="browser-default" type="text" value="` + val.balance + `" onkeyup="formatRupiah(this);" style="width:150px;text-align:right;" readonly>
                                            </td>
                                            <td class="center">
                                                -
                                            </td>
                                            <td>
                                                <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan ...">
                                            </td>
                                            <td class="center">
                                                <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                                    <i class="material-icons">delete</i>
                                                </a>
                                            </td>
                                        </tr>
                                    `);
                                });
                                
                            }else{
                                $('#grandtotal').val('0,00');
                            }

                            $('#top').val(response.top);
                            
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
                        title: 'Ups!',
                        text: 'Silahkan, pilih data.',
                        icon: 'warning'
                    });
                }
            }
        });
    }

    function checkTotal(element){
        var nil = parseFloat($(element).val().replaceAll(".", "").replaceAll(",",".")), max = parseFloat($(element).data('grandtotal').replaceAll(".", "").replaceAll(",","."));
        if(nil > max){
            $(element).val($(element).data('grandtotal'));
        }
    }

    function countAll(){
        var total = 0, grandtotal = 0;
        
        if($('input[name^="arr_total"]').length > 0){
            $('input[name^="arr_total"]').each(function(index){
                let rowtotal = parseFloat($(this).val().replaceAll(".", "").replaceAll(",",".")) + parseFloat($('input[name^="arr_rounding"]').eq(index).val().replaceAll(".", "").replaceAll(",","."));
                total += rowtotal;
                $('input[name^="arr_subtotal"]').eq(index).val(
                    (rowtotal >= 0 ? '' : '-') + formatRupiahIni(rowtotal.toFixed(2).toString().replace('.',','))
                );
            });
        }

        grandtotal = total;

        $('#grandtotal').val(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(grandtotal.toFixed(2).toString().replace('.',','))
        );
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

    function countRow(id){
        var total = parseFloat($('#arr_total' + id).val().replaceAll(".", "").replaceAll(",",".")), 
            limit = parseFloat($('#arr_total' + id).data('limit').toString().replaceAll(".", "").replaceAll(",","."));

        if(limit > 0){
            if(total > limit){
                total = limit;
                $('#arr_total' + id).val(formatRupiahIni(total.toFixed(2).toString().replace('.',',')));
            }
        }
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
            "order": [[0, 'asc']],
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    'status' : $('#filter_status').val(),
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
                { name: 'coa_id', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'currency_id', className: 'center-align' },
                { name: 'currency_rate', className: 'center-align' },
                { name: 'grandtotal', className: 'right-align' },
                { name: 'document', className: 'center-align' },
                { name: 'note', className: '' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle',
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

                formData.delete("arr_cost_distribution[]");
                formData.delete("arr_coa[]");
                formData.delete("arr_total[]");
                formData.delete("arr_rounding[]");
                formData.delete("arr_subtotal[]");
                formData.delete("arr_note[]");
                formData.delete("arr_type[]");
                formData.delete("arr_id[]");

                let passed = true;

                if($('input[name^="arr_coa[]"]').length > 0){
                    $('input[name^="arr_coa[]"]').each(function(index){
                        formData.append('arr_coa[]',$(this).val());
                        formData.append('arr_id[]',$('input[name^="arr_id[]"]').eq(index).val());
                        formData.append('arr_cost_distribution[]','');
                        formData.append('arr_total[]',$('input[name^="arr_total[]"]').eq(index).val());
                        formData.append('arr_rounding[]',$('input[name^="arr_rounding[]"]').eq(index).val());
                        formData.append('arr_subtotal[]',$('input[name^="arr_subtotal[]"]').eq(index).val());
                        formData.append('arr_type[]',$('input[name^="arr_type[]"]').eq(index).val());
                        formData.append('arr_note[]',(
                            $('input[name^="arr_note"]').eq(index).val() ? $('input[name^="arr_note[]"]').eq(index).val() : ''
                        ));
                        if(!$(this).val() || !$('input[name^="arr_total[]"]').eq(index).val() || !$('input[name^="arr_rounding[]"]').eq(index).val() || !$('input[name^="arr_subtotal[]"]').eq(index).val()){
                            passed = false;
                        }
                    });
                }

                if($('select[name^="arr_coa_item[]"]').length > 0){
                    $('select[name^="arr_coa_item[]"]').each(function(index){
                        formData.append('arr_coa[]',$(this).val());
                        formData.append('arr_id[]',$(this).val());
                        formData.append('arr_cost_distribution[]',(
                            $('select[name^="arr_cost_distribution[]"]').eq(index).val() ? $('select[name^="arr_cost_distribution[]"]').eq(index).val() : ''
                        ));
                        formData.append('arr_total[]',$('input[name^="arr_total_item[]"]').eq(index).val());
                        formData.append('arr_rounding[]',$('input[name^="arr_rounding_item[]"]').eq(index).val());
                        formData.append('arr_subtotal[]',$('input[name^="arr_subtotal_item[]"]').eq(index).val());
                        formData.append('arr_type[]',$('input[name^="arr_type_item[]"]').eq(index).val());
                        formData.append('arr_note[]',(
                            $('input[name^="arr_note_item[]"]').eq(index).val() ? $('input[name^="arr_note_item[]"]').eq(index).val() : ''
                        ));
                        if(!$(this).val() || !$('input[name^="arr_total_item[]"]').eq(index).val() || !$('input[name^="arr_rounding_item[]"]').eq(index).val() || !$('input[name^="arr_subtotal_item[]"]').eq(index).val()){
                            passed = false;
                        }
                    });
                }

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
                        title: 'Ups!',
                        text: 'Form tidak boleh ada yang kosong.',
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
                $('#code_place_id').val(response.code_place_id).formSelect();
                $('#code').val(response.code);
                if(response.account_name){
                    $('#account_id').empty().append(`
                        <option value="` + response.account_id + `">` + response.account_name + `</option>
                    `);
                }
                $('#coa_id').empty().append(`
                    <option value="` + response.coa_id + `">` + response.coa_name + `</option>
                `);
                $('#company_id').val(response.company_id).formSelect();
                $('#currency_id').val(response.currency_id).formSelect();
                $('#currency_rate').val(response.currency_rate);
                $('#post_date').val(response.post_date);
                $('#note').val(response.note);
                $('#grandtotal').val(response.grandtotal);
                
                if(response.details.length > 0){
                    $('.row_detail').remove();
                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        let readonly = 'readonly';
                        let array = ['marketing_order_memos','coas'];
                        if(array.includes(val.type)){
                            readonly = '';
                        }
                        $('#last-row-detail').before(`
                            <tr class="row_detail">
                                <input type="hidden" name="arr_type[]" value="` + val.type + `">
                                ` + 
                                (val.type == 'coas' ? `` : 
                                `
                                <input type="hidden" name="arr_id[]" value="` + val.id + `">
                                <input type="hidden" name="arr_coa[]" value="` + val.coa_id + `">
                                `)
                                + `
                                <td>
                                    ` + (val.type == 'coas' ? `<select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]"></select>` : val.name) + `
                                </td>
                                <td class="center">
                                    ` + val.post_date + `
                                </td>
                                <td class="center">
                                    -
                                </td>
                                <td class="center">
                                    <input id="arr_total` + count + `" name="arr_total[]" onfocus="emptyThis(this);" data-limit="0" class="browser-default" type="text" value="` + val.total + `" onkeyup="formatRupiah(this);countRow('` + count + `');countAll();" style="width:150px;text-align:right;" ` + readonly + `>
                                </td>
                                <td class="center">
                                    <input id="arr_rounding` + count + `" name="arr_rounding[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.rounding + `" onkeyup="formatRupiah(this);countAll();" style="width:150px;text-align:right;" ` + readonly + `>
                                </td>
                                <td class="center">
                                    <input id="arr_subtotal` + count + `" name="arr_subtotal[]" onfocus="emptyThis(this);" data-limit="0" class="browser-default" type="text" value="` + val.subtotal + `" onkeyup="formatRupiah(this);" style="width:150px;text-align:right;" readonly>
                                </td>
                                <td class="center">
                                    ` + (val.type == 'coas' ? `<select class="browser-default" id="arr_cost_distribution` + count + `" name="arr_cost_distribution[]" onchange="applyCoa('` + count + `');"></select>` : `-`) + `
                                </td>
                                <td>
                                    <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan ..." value="` + val.note + `">
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                        if(val.type == 'coas'){
                            $('#arr_coa' + count).append(`
                                <option value="` + val.coa_id + `">` + val.name + `</option>
                            `);
                            select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
                        }
                        if(val.cost_distribution_name){
                            $('#arr_cost_distribution' + count).append(`
                                <option value="` + val.cost_distribution_id + `">` + val.cost_distribution_name + `</option>
                            `);
                        }
                        select2ServerSide('#arr_cost_distribution' + count, '{{ url("admin/select2/cost_distribution") }}');
                    });
                }

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
                })
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

        // Split the path by slashes and get the last segment
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
        // alert('coming soon!');
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
                    $('#modal3').modal('open');
                    $('#title_data').append(``+data.title+``);
                    $('#code_data').append(data.message.code);
                    $('#body-journal-table').append(data.tbody);
                    $('#user_jurnal').append(`Pengguna `+data.user);
                    $('#note_jurnal').append(`Keterangan `+data.message.note);
                    $('#ref_jurnal').append(`Referensi `+data.reference);
                    $('#post_date_jurnal').append(`Tanggal `+data.message.post_date);
                }
            }
        });
    }
    function startIntro(){
        introJs().setOptions({
            exitOnOverlayClick : false,
            steps: [
                {
                    title : 'Kas / Bank Masuk',
                    intro : ''
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
                    title : 'Perusahaan',
                    element : document.querySelector('.step3'),
                    intro : 'Perusahaan dimana dokumen ini dibuat.' 
                },
                {
                    title : 'Partner Bisnis',
                    element : document.querySelector('.step4'),
                    intro : 'Jika ingin menambahkan data baru, silahkan ke form Master Data - Organisasi - Partner Bisnis.' 
                },
                {
                    title : 'Kas / Bank',
                    element : document.querySelector('.step5'),
                    intro : 'COA bank yang akan digunakan dalam form ini.' 
                },
                {
                    title : 'Tgl. Posting',
                    element : document.querySelector('.step6'),
                    intro : 'Tanggal transfer / masuk uang.' 
                },
                {
                    title : 'Tgl. Posting',
                    element : document.querySelector('.step7'),
                    intro : 'Tanggal post akan menentukan tanggal jurnal untuk beberapa form yang terhubung dengan jurnal. Hati - hati dalam menentukan tanggal posting.' 
                },
                
                {
                    title : 'File Lampiran',
                    element : document.querySelector('.step8'),
                    intro : 'Silahkan unggah file lampiran. untuk saat ini hanya bisa mengakomodir 1 file lampiran saja. Jika ingin menambahkan file lebih dari 1, silahkan gabungkan file anda menjadi pdf.' 
                },
                {
                    title : 'Mata Uang',
                    element : document.querySelector('.step9'),
                    intro : 'Mata uang, silahkan pilih mata uang lain, untuk mata uang asing.' 
                },
                {
                    title : 'Konversi',
                    element : document.querySelector('.step10'),
                    intro : 'Nilai konversi rupiah pada saat dokumen dibuat. Nilai konversi secara otomatis diisi ketika form tambah baru dibuka pertama kali dan data diambil dari situs exchangerate.host. Pastikan kode mata uang benar di master data agar nilai konversi tidak error.'
                },
                {
                    title : 'Detail AR Invoice / AR Down Payment / BS.Karyawan / Coa',
                    element : document.querySelector('.step12'),
                    intro : 'Berisikan list dari data yang digunakan dalam form.' 
                },
                {
                    title : 'Keterangan',
                    element : document.querySelector('.step13'),
                    intro : 'Silahkan isi / tambahkan keterangan untuk dokumen ini untuk dimunculkan di bagian bawah tabel detail produk nantinya, ketika dicetak.' 
                },
                {
                    title : 'Total',
                    element : document.querySelector('.step14'),
                    intro : 'Merupakan total yang didapatkan dari penjumlahan di atas.' 
                },
                {
                    title : 'Tombol Simpan',
                    element : document.querySelector('.step15'),
                    intro : 'Silahkan tekan tombol ini untuk menyimpan data, namun pastikan data yang akan anda masukkan benar.' 
                },
            ]
        }).start();
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
</script>