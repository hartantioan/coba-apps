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
    #modal5{
        position: fixed;
        top: 50% !important;
        left: 50% !important;
        /* bring your own prefixes */
        transform: translate(-50%, -50%) !important;
    }
    
    @media (min-width: 960px) {
        #modal4 {
            width:60%;
        }
    }

    @media (max-width: 960px) {
        #modal4 {
            width:100%;
        }
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
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                            <i class="material-icons hide-on-med-and-up">refresh</i>
                            <span class="hide-on-small-onl">Refresh</span>
                            <i class="material-icons right">refresh</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="printData();">
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
                                                <label for="filter_type" style="font-size:1rem;">Tipe :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_type" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        <option value="1">Cash</option>
                                                        <option value="2">Credit</option>
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
                                                <label for="filter_account" style="font-size:1rem;">Supplier/Vendor :</label>
                                                <div class="input-field">
                                                    <select class="browser-default" id="filter_account" name="filter_account" multiple="multiple" style="width:100% !important;" onchange="loadDataTable()"></select>
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
                                                        <th rowspan="2">Sup/Ven</th>
                                                        <th rowspan="2">Perusahaan</th>
                                                        <th colspan="4" class="center-align">Tanggal</th>
                                                        <th rowspan="2">Tipe</th>
                                                        <th rowspan="2">Dokumen</th>
                                                        <th rowspan="2">Keterangan</th>
                                                        <th rowspan="2">No.Faktur Pajak</th>
                                                        <th rowspan="2">No.Bukti Potong</th>
                                                        <th rowspan="2">Tgl.Bukti Potong</th>
                                                        <th rowspan="2">No.SPK</th>
                                                        <th rowspan="2">No.Invoice</th>
                                                        <th rowspan="2">Subtotal</th>
                                                        <th colspan="2" class="center-align">Diskon</th>
                                                        <th rowspan="2">Total</th>
                                                        <th rowspan="2">PPN</th>
                                                        <th rowspan="2">PPH</th>
                                                        <th rowspan="2">Grandtotal</th>
                                                        <th rowspan="2">Downpayment</th>
                                                        <th rowspan="2">Balance</th>
                                                        <th rowspan="2">Status</th>
                                                        <th rowspan="2">Action</th>
                                                    </tr>
                                                    <tr>
                                                        <th>Post</th>
                                                        <th>Terima</th>
                                                        <th>Tenggat</th>
                                                        <th>Dokumen</th>
                                                        <th>Prosentase</th>
                                                        <th>Nominal</th>
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
                <i>Silahkan pilih supplier / vendor untuk mengambil data dokumen GRPO, PO Jasa, LC, atau PO DP.</i>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="row">
                            <div class="input-field col m3 s12">
                                <input type="hidden" id="temp" name="temp">
                                <select class="browser-default" id="account_id" name="account_id" onchange="getAccountData(this.value);"></select>
                                <label class="active" for="account_id">Supplier / Vendor</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="type" name="type">
                                    <option value="1">Cash</option>
                                    <option value="2">Credit</option>
                                </select>
                                <label class="" for="type">Tipe</label>
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
                                <input id="post_date" name="post_date" min="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                <label class="active" for="post_date">Tgl. Posting</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="received_date" name="received_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. Terima" value="{{ date('Y-m-d') }}" onchange="addDays();">
                                <label class="active" for="received_date">Tgl. Terima</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="top" name="top" min="0" type="number" value="0" onchange="addDays();">
                                <label class="active" for="top">TOP (hari) Autofill dari GRPO</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="due_date" name="due_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. Kadaluarsa">
                                <label class="active" for="due_date">Tgl. Kadaluarsa</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="document_date" name="document_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. dokumen">
                                <label class="active" for="document_date">Tgl. Dokumen</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="tax_no" name="tax_no" type="text" placeholder="Nomor faktur pajak...">
                                <label class="active" for="tax_no">No. Faktur Pajak</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="tax_cut_no" name="tax_cut_no" type="text" placeholder="Nomor bukti potong...">
                                <label class="active" for="tax_cut_no">No. Bukti Potong</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="cut_date" name="cut_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. Bukti potong">
                                <label class="active" for="cut_date">Tgl. Bukti Potong</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="spk_no" name="spk_no" type="text" placeholder="Nomor SPK...">
                                <label class="active" for="spk_no">No. SPK</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="invoice_no" name="invoice_no" type="text" placeholder="Nomor Invoice dari Suppplier/Vendor">
                                <label class="active" for="invoice_no">No. Invoice</label>
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
                            <div class="col m12 s12">
                                <p class="mt-2 mb-2">
                                    <h6>Detail Goods Receipt PO / Landed Cost / Purchase Order Jasa / Coa</h6>
                                    <div style="overflow:auto;">
                                        <table class="bordered" style="width:2800px !important;">
                                            <thead>
                                                <tr>
                                                    <th class="center">GR/LC/PO/Coa No.</th>
                                                    <th class="center">NO.PO</th>
                                                    <th class="center">No.SJ</th>
                                                    <th class="center">Item / Coa Jasa</th>
                                                    <th class="center">Satuan</th>
                                                    <th class="center">Qty Diterima</th>
                                                    <th class="center">Qty Kembali</th>
                                                    <th class="center">Qty Sisa</th>
                                                    <th class="center">Harga@</th>
                                                    <th class="center">Tgl.Post</th>
                                                    <th class="center">Tgl.Tenggat</th>
                                                    <th class="center">Total</th>
                                                    <th class="center">PPN (%)</th>
                                                    <th class="center">Termasuk PPN</th>
                                                    <th class="center">PPN (Rp)</th>
                                                    <th class="center">PPH (%)</th>
                                                    <th class="center">PPH (Rp)</th>
                                                    <th class="center">Grandtotal</th>
                                                    <th class="center">Keterangan</th>
                                                    <th class="center">Plant</th>
                                                    <th class="center">Line</th>
                                                    <th class="center">Mesin</th>
                                                    <th class="center">Departemen</th>
                                                    <th class="center">Gudang</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-detail">
                                                <tr id="last-row-detail">
                                                    <td colspan="24">
                                                        <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addItem()" href="javascript:void(0);">
                                                            <i class="material-icons left">add</i> Pembulatan Manual
                                                        </a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </p>
                            </div>
                            <div class="col m12 s12">
                                <p class="mt-2 mb-2">
                                    <h5>Detail Down Payment Bisnis Partner</h5>
                                    <div style="overflow:auto;">
                                        <table class="bordered">
                                            <thead>
                                                <tr>
                                                    <th class="center">Purchase DP No.</th>
                                                    <th class="center">Tgl.Post</th>
                                                    <th class="center">Nominal</th>
                                                    <th class="center">Sisa</th>
                                                    <th class="center">Dipakai</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-detail-dp">
                                                <tr id="empty-detail-dp">
                                                    <td colspan="5" class="center">
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

                            </div>
                            <div class="input-field col m4 s12">
                                <table width="100%" class="bordered">
                                    <thead>
                                        <tr>
                                            <td>Total</td>
                                            <td class="right-align"><span id="total">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>PPN</td>
                                            <td class="right-align"><span id="tax">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>PPH</td>
                                            <td class="right-align"><span id="wtax">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>Uang Muka</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="downpayment" name="downpayment" type="text" value="0,00" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100%;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Pembulatan</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="rounding" name="rounding" type="text" value="0,00" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100%;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Grandtotal</td>
                                            <td class="right-align"><span id="balance">0,00</span></td>
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
        <div class="col s12" id="show_structure">
            <div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;">

            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal4" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;">
    <div class="modal-header ml-2">
        <h5>Daftar Tunggakan Dokumen <b id="account_name"></b></h5>
    </div>
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <div class="row">
                    <div class="col s12 mt-2">
                        <ul class="collapsible">
                            <li class="active">
                                <div class="collapsible-header purple lightrn-1 white-text">
                                    <i class="material-icons">layers</i> Goods Receipt / Landed Cost / Purchase Order (Jasa)
                                </div>
                                <div class="collapsible-body">
                                    <div id="datatable_buttons_multi"></div>
                                    <i class="right">Gunakan *pilih semua* untuk memilih seluruh data yang anda inginkan. Atau pilih baris untuk memilih data yang ingin dipindahkan.</i>
                                    <table id="table_multi" class="display" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="center-align">GR/LC/PO No.</th>
                                                <th class="center-align">Tgl.Post</th>
                                                <th class="center-align">Grandtotal</th>
                                                <th class="center-align">Ter-Invoice</th>
                                                <th class="center-align">Sisa</th>
                                                <th class="center-align">Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-detail-multi"></tbody>
                                    </table>
                                </div>
                            </li>
                            <li class="active">
                                <div class="collapsible-header cyan white-text">
                                    <i class="material-icons">more</i> Purchase Down Payment
                                </div>
                                <div class="collapsible-body">
                                    <div id="datatable_buttons_multi_dp"></div>
                                    <table id="table_multi_dp" class="display" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="center">Purchase DP No.</th>
                                                <th class="center">Tgl.Post</th>
                                                <th class="center">Nominal</th>
                                                <th class="center">Sisa</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-detail-dp-multi"></tbody>
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
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat mr-1">Close</a>
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
                                    <div class="input-field col m4 s12">
                                        <input id="range_start" name="range_start" min="0" type="number" placeholder="1">
                                        <label class="" for="range_end">No Awal</label>
                                    </div>
                                    
                                    <div class="input-field col m4 s12">
                                        <input id="range_end" name="range_end" min="0" type="number" placeholder="1">
                                        <label class="active" for="range_end">No akhir</label>
                                    </div>
                                    <div class="input-field col m4 s12">
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
                               
                                <div class="input-field col m4 s12">
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

<div id="modal6" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;width:100%;">
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
                            <th class="center-align">Bisnis Partner</th>
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
    var table_multi, table_multi_dp;
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

        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
            
        });

        loadDataTable();

        window.table.search('{{ $code }}').draw();

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
                $('.row_detail').remove();
                $('#account_id').empty();
                $('#total,#tax,#wtax,#balance').text('0,00');
                $('#subtotal,#discount,#downpayment').val('0,00');
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
            },
            onCloseEnd: function(modal, trigger){
                $('#myDiagramDiv').remove();
                $('#show_structure').append(
                    `<div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;"></div>
                    `
                );
            }
        });

        $('#modal4').modal({
            onOpenStart: function(modal,trigger) {
                $('.collapsible').collapsible({
                    accordion:false
                });
            },
            onOpenEnd: function(modal, trigger) {
                table_multi = $('#table_multi').DataTable({
                    "responsive": true,
                    scrollY: '50vh',
                    scrollCollapse: true,
                    "iDisplayInLength": 10,
                    "order": [[0, 'asc']],
                    dom: 'Blfrtip',
                    buttons: [
                        'selectAll',
                        'selectNone'
                    ],
                    language: {
                        buttons: {
                            selectAll: "Pilih semua",
                            selectNone: "Hapus pilihan"
                        }
                    },
                    select: {
                        style: 'multi'
                    }
                });
                table_multi_dp = $('#table_multi_dp').DataTable({
                    "responsive": true,
                    scrollY: '50vh',
                    scrollCollapse: true,
                    "iDisplayInLength": 10,
                    "order": [[0, 'asc']],
                    dom: 'Blfrtip',
                    buttons: [
                        'selectAll',
                        'selectNone'
                    ],
                    language: {
                        buttons: {
                            selectAll: "Pilih semua",
                            selectNone: "Hapus pilihan"
                        }
                    },
                    select: {
                        style: 'multi'
                    }
                });
                $('#table_multi_wrapper > .dt-buttons').appendTo('#datatable_buttons_multi');
                $('#table_multi_dp_wrapper > .dt-buttons').appendTo('#datatable_buttons_multi_dp');
                $('select[name="table_multi_length"]').addClass('browser-default');
                $('select[name="table_multi_dp_length"]').addClass('browser-default');
            },
            onCloseEnd: function(modal, trigger){
                $('#body-detail-multi,#body-detail-dp-multi').empty();
                $('#account_name').text('');
                $('#preview_data').html('');
                $('#table_multi,#table_multi_dp').DataTable().clear().destroy();
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

        select2ServerSide('#account_id,#filter_account', '{{ url("admin/select2/supplier_vendor") }}');
    });

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
                let passed = false, arr_id = [], arr_type = [], sametype = true;
                $.map(table_multi.rows('.selected').nodes(), function (item) {
                    passed = true;
                    arr_id.push($(item).data('id'));
                    arr_type.push($(item).data('type'));
                });

                $.map(table_multi_dp.rows('.selected').nodes(), function (item) {
                    arr_id.push($(item).data('id'));
                    arr_type.push($(item).data('type'));
                });
                
                if(arr_type.length > 0){
                    let arrResult = arr_type.filter((element, index) => {
                        return arr_type.indexOf(element) === index;
                    });

                    let indexkuy = arrResult.indexOf('purchase_down_payments');
                    arrResult.splice(indexkuy, 1);

                    if(arrResult.length > 1){
                        sametype = false;
                    }
                }

                if(passed == true){
                    if(sametype == true){
                        $.ajax({
                            url: '{{ Request::url() }}/get_gr_lc',
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
                                $('.row_detail').remove();
                                $('#body-detail-dp').empty();
                                $('.row_detail').remove();
                                if(response.details.length > 0){
                                    $.each(response.details, function(i, val) {
                                        var count = makeid(10);
                                        $('#last-row-detail').before(`
                                            <tr class="row_detail">
                                                <input type="hidden" name="arr_type[]" value="` + val.type + `" data-id="` + count + `">
                                                <input type="hidden" name="arr_price[]" value="` + val.price + `" data-id="` + count + `">
                                                <input type="hidden" name="arr_total[]" value="` + val.total + `" data-id="` + count + `">
                                                <input type="hidden" name="arr_grandtotal[]" value="` + val.grandtotal + `" data-id="` + count + `">
                                                <input type="hidden" name="arr_tax[]" value="` + val.tax + `" data-id="` + count + `">
                                                <input type="hidden" name="arr_wtax[]" value="` + val.wtax + `" data-id="` + count + `">
                                                <input type="hidden" id="arr_place` + count + `" name="arr_place[]" value="` + val.place_id + `" data-id="` + count + `">
                                                <input type="hidden" id="arr_line` + count + `" name="arr_line[]" value="` + val.line_id + `" data-id="` + count + `">
                                                <input type="hidden" id="arr_machine` + count + `" name="arr_machine[]" value="` + val.machine_id + `" data-id="` + count + `">
                                                <input type="hidden" id="arr_department` + count + `" name="arr_department[]" value="` + val.department_id + `" data-id="` + count + `">
                                                <input type="hidden" id="arr_warehouse` + count + `" name="arr_warehouse[]" value="` + val.warehouse_id + `" data-id="` + count + `">
                                                <input type="hidden" name="arr_code[]" value="` + val.id + `" data-id="` + count + `">
                                                <input type="hidden" name="arr_temp_qty[]" value="` + val.qty_balance + `" data-id="` + count + `">
                                                <td class="center">
                                                    ` + val.rawcode + `
                                                </td>
                                                <td class="center">
                                                    ` + val.purchase_no + `
                                                </td>
                                                <td class="center">
                                                    ` + val.delivery_no + `
                                                </td>
                                                <td class="">
                                                    ` + val.name + `
                                                </td>
                                                <td class="center">
                                                    ` + val.buy_unit + `
                                                </td>
                                                <td class="center">
                                                    ` + val.qty_received + `
                                                </td>
                                                <td class="center">
                                                    ` + val.qty_returned + `
                                                </td>
                                                <td class="center">
                                                    <input class="browser-default" type="text" name="arr_qty[]" value="` + val.qty_balance + `" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();">
                                                </td>
                                                <td class="right-align">
                                                    ` + val.price + `
                                                </td>
                                                <td class="center">
                                                    ` + val.post_date + `
                                                </td>
                                                <td class="center">
                                                    ` + val.due_date + `
                                                </td>
                                                <td class="right-align row_total" id="row_total` + count + `">
                                                    ` + val.total + `
                                                </td>
                                                <td class="center">
                                                    <select class="browser-default" id="arr_percent_tax` + count + `" name="arr_percent_tax[]" data-id="` + count + `" onchange="countAll();">
                                                        <option value="0" data-id="">-- Non-PPN --</option>
                                                        @foreach ($tax as $row1)
                                                            <option value="{{ $row1->percentage }}" data-id="{{ $row1->id }}">{{ $row1->code.' - '.number_format($row1->percentage,2,',','.').'%' }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="center">
                                                    <select class="browser-default" id="arr_include_tax` + count + `" name="arr_include_tax[]" data-id="` + count + `" onchange="countAll();">
                                                        <option value="0">Tidak</option>
                                                        <option value="1">Ya</option>
                                                    </select>
                                                </td>
                                                <td class="right-align" id="row_tax` + count + `">
                                                    ` + val.tax + `
                                                </td>
                                                <td class="center">
                                                    <select class="browser-default" id="arr_percent_wtax` + count + `" name="arr_percent_wtax[]" data-id="` + count + `" onchange="countAll();">
                                                        <option value="0" data-id="">-- Non-PPH --</option>
                                                        @foreach ($wtax as $row2)
                                                            <option value="{{ $row2->percentage }}" data-id="{{ $row2->id }}">{{ $row2->code.' - '.number_format($row2->percentage,2,',','.').'%' }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="right-align" id="row_wtax` + count + `">
                                                    ` + val.wtax + `    
                                                </td>
                                                <td class="right-align row_grandtotal" id="row_grandtotal` + count + `">
                                                    ` + val.grandtotal + `
                                                </td>
                                                <td>
                                                    <input class="browser-default" type="text" name="arr_note[]" value="` + val.info + `" data-id="` + count + `">
                                                </td>
                                                <td class="center">
                                                    ` + val.place_name + `
                                                </td>
                                                <td class="center">
                                                    ` + val.line_name + `
                                                </td>
                                                <td class="center">
                                                    ` + val.machine_name + `
                                                </td>
                                                <td class="center">
                                                    ` + val.department_name + `
                                                </td>
                                                <td class="center">
                                                    ` + val.warehouse_name + `
                                                </td>
                                            </tr>
                                        `);

                                        $('#arr_percent_tax' + count).val(val.percent_tax);
                                        $('#arr_percent_wtax' + count).val(val.percent_wtax);
                                        $('#arr_include_tax' + count).val(val.include_tax);

                                        $('#top').val(val.top);
                                    });                        
                                }else{
                                    $('.row_detail').remove();
                                    $('#total,#tax,#balance').text('0,00');
                                }

                                if(response.downpayments.length > 0){
                                    $.each(response.downpayments, function(i, val) {
                                        var count = makeid(10);
                                        $('#body-detail-dp').append(`
                                            <tr class="row_detail_dp">
                                                <input type="hidden" name="arr_dp_code[]" value="` + val.code + `" data-id="` + count + `">
                                                <td class="center">
                                                    ` + val.rawcode + `
                                                </td>
                                                <td class="center">
                                                    ` + val.post_date + `
                                                </td>
                                                <td class="center">
                                                    ` + val.grandtotal + `
                                                </td>
                                                <td class="center">
                                                    ` + val.balance + `
                                                </td>
                                                <td class="center">
                                                    <input name="arr_nominal[]" class="browser-default" type="text" value="` + val.balance + `" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100% !important;" id="rowNominal`+ count +`">
                                                </td>
                                            </tr>
                                        `);
                                    });                        
                                }else{
                                    $('#body-detail-dp').empty().append(`
                                        <tr id="empty-detail-dp">
                                            <td colspan="6" class="center">
                                                Pilih supplier/vendor untuk memulai...
                                            </td>
                                        </tr>
                                    `);

                                    $('#downpayment').val('0,00');
                                }

                                addDays();
                                
                                $('.modal-content').scrollTop(0);
                                M.updateTextFields();

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
                        $('#modal4').modal('close');
                    }else{
                        swal({
                            title: 'Ups!',
                            text: 'Anda tidak boleh memilih tipe dokumen yang berbeda.',
                            icon: 'warning'
                        });
                    }
                }else{
                    swal({
                        title: 'Ups!',
                        text: 'Silahkan, pilih GRPO, LC, atau PO Jasa yang ingin anda masukkan.',
                        icon: 'warning'
                    });
                }
            }
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
                
            },
            success: function(response) {
                loadingClose('.modal-content');
                makeTreeOrg(response.message,response.link);

                $('#modal3').modal('open');
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

    function getAccountData(val){
        if(val){
            $.ajax({
                url: '{{ Request::url() }}/get_account_data',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: val
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    loadingClose('.modal-content');
                    $('#modal4').modal('open');
                    $('#account_name').text($('#account_id').select2('data')[0].text);

                    if(response.details.length > 0){
                        $.each(response.details, function(i, val) {
                            $('#body-detail-multi').append(`
                                <tr data-type="` + val.type + `" data-id="` + val.id + `">
                                    <td class="center">
                                        ` + val.code + `
                                    </td>
                                    <td class="center">
                                        ` + val.post_date + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.grandtotal + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.invoice + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.balance + `
                                    </td>
                                    <td class="center-align">
                                        ` + val.info + `
                                    </td>
                                </tr>
                            `);
                        });
                    }

                    if(response.downpayments.length > 0){
                        $.each(response.downpayments, function(i, val) {
                            var count = makeid(10);
                            $('#body-detail-dp-multi').append(`
                                <tr data-type="` + val.type + `" data-id="` + val.id + `">
                                    <td class="center">
                                        ` + val.rawcode + `
                                    </td>
                                    <td class="center">
                                        ` + val.post_date + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.grandtotal + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.balance + `
                                    </td>
                                </tr>
                            `);
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
            $('#body-detail-dp').empty();
            $('.row_detail').remove();
            countAll();
        }
    }

    function addItem(){
        var count = makeid(10);
        $('#last-row-detail').before(`
            <tr class="row_detail">
                <input type="hidden" name="arr_code[]" value="" data-id="` + count + `">
                <input type="hidden" name="arr_type[]" value="coas" data-id="` + count + `">
                <input type="hidden" name="arr_total[]" value="0" data-id="` + count + `">
                <input type="hidden" name="arr_tax[]" value="0" data-id="` + count + `">
                <input type="hidden" name="arr_wtax[]" value="0" data-id="` + count + `">
                <input type="hidden" name="arr_grandtotal[]" value="0" data-id="` + count + `">
                <input type="hidden" name="arr_temp_qty[]" value="1" data-id="` + count + `">
                <td class="center">
                    <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]"></select>
                </td>
                <td class="center">
                    -
                </td>
                <td class="center">
                    -
                </td>
                <td class="center">
                    -
                </td>
                <td class="center">
                    -
                </td>
                <td class="center">
                    -
                </td>
                <td class="center">
                    -
                </td>
                <td class="center">
                    <input class="browser-default" type="text" name="arr_qty[]" value="0" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();">
                </td>
                <td class="center">
                    <input class="browser-default" type="text" name="arr_price[]" value="0" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();">
                </td>
                <td class="center">
                    -
                </td>
                <td class="center">
                    -
                </td>
                <td class="right-align row_total" id="row_total` + count + `">
                    0
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_percent_tax` + count + `" name="arr_percent_tax[]" data-id="` + count + `" onchange="countAll();">
                        <option value="0" data-id="">-- Non-PPN --</option>
                        @foreach ($tax as $row1)
                            <option value="{{ $row1->percentage }}" data-id="{{ $row1->id }}">{{ $row1->code.' - '.number_format($row1->percentage,2,',','.').'%' }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_include_tax` + count + `" name="arr_include_tax[]" data-id="` + count + `" onchange="countAll();">
                        <option value="0">Tidak</option>
                        <option value="1">Ya</option>
                    </select>
                </td>
                <td class="right-align" id="row_tax` + count + `">
                    <input class="browser-default" type="text" name="arr_tax[]" value="0" data-id="` + count + `" onkeyup="formatRupiah(this);" readonly>
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_percent_wtax` + count + `" name="arr_percent_wtax[]" data-id="` + count + `" onchange="countAll();">
                        <option value="0" data-id="">-- Non-PPH --</option>
                        @foreach ($wtax as $row2)
                            <option value="{{ $row2->percentage }}" data-id="{{ $row2->id }}">{{ $row2->code.' - '.number_format($row2->percentage,2,',','.').'%' }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="right-align" id="row_wtax` + count + `">
                    <input class="browser-default" type="text" name="arr_wtax[]" value="0" data-id="` + count + `" onkeyup="formatRupiah(this);" readonly>
                </td>
                <td class="right-align row_grandtotal" id="row_grandtotal` + count + `">
                    0
                </td>
                <td>
                    <input type="text" name="arr_note[]" value="Pembulatan ..." data-id="` + count + `">
                </td>
                <td class="center">
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
                        @foreach ($machine as $rowmachine)
                            <option value="{{ $rowmachine->id }}" data-line="{{ $rowmachine->line_id }}">{{ $rowmachine->name }}</option>
                        @endforeach
                    </select>    
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                        @foreach ($department as $rowdept)
                            <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">
                        <option value="">--Kosong--</option>
                        @foreach ($warehouse as $row)
                            <option value="{{ $row->id }}">{{ $row->name }}</option>
                        @endforeach
                    </select>
                </td>
            </tr>
        `);
        select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
    }

    function changePlace(element){
        if($(element).val()){
            $(element).parent().prev().find('select[name="arr_place[]"]').val($(element).find(':selected').data('place'));
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

    function countAll(){
        var total = 0, tax = 0, grandtotal = 0, balance = 0, wtax = 0, downpayment = 0, rounding = parseFloat($('#rounding').val().replaceAll(".", "").replaceAll(",","."));
        
        $('input[name^="arr_code"]').each(function(){
            let element = $(this);
            var rowgrandtotal = 0, rowtotal = 0, rowtax = 0, rowwtax = 0, percent_tax = parseFloat($('select[name^="arr_percent_tax"][data-id="' + element.data('id') + '"]').val()), percent_wtax = parseFloat($('select[name^="arr_percent_wtax"][data-id="' + element.data('id') + '"]').val()), rowprice = parseFloat($('input[name^="arr_price"][data-id="' + element.data('id') + '"]').val().replaceAll(".", "").replaceAll(",",".")), rowqty = parseFloat($('input[name^="arr_qty"][data-id="' + element.data('id') + '"]').val().replaceAll(".", "").replaceAll(",","."));
            rowtotal = rowprice * rowqty;
            if(percent_tax > 0 && $('#arr_include_tax' + element.data('id')).val() == '1'){
                rowtotal = rowtotal / (1 + (percent_tax / 100));
            }
            rowtax = rowtotal * (percent_tax / 100);
            rowwtax = rowtotal * (percent_wtax / 100);
            $('input[name^="arr_total"][data-id="' + element.data('id') + '"]').val(
                (rowtotal >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(rowtotal).toString().replace('.',','))
            );
            $('#row_total' + element.data('id')).text(
                (rowtotal >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(rowtotal).toString().replace('.',','))
            );
            $('input[name^="arr_tax"][data-id="' + element.data('id') + '"]').val(
                (rowtax >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(rowtax).toString().replace('.',','))
            );
            $('#row_tax' + element.data('id')).text(
                (rowtax >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(rowtax).toString().replace('.',','))
            );
            $('input[name^="arr_wtax"][data-id="' + element.data('id') + '"]').val(
                (rowwtax >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(rowwtax).toString().replace('.',','))
            );
            $('#row_wtax' + element.data('id')).text(
                (rowwtax >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(rowwtax).toString().replace('.',','))
            );
            total += rowtotal;
            tax += rowtax;
            wtax += rowwtax;
            rowgrandtotal = rowtotal + rowtax - rowwtax;
            grandtotal += rowgrandtotal;
            $('input[name^="arr_grandtotal"][data-id="' + element.data('id') + '"]').val(
                (rowgrandtotal >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(rowgrandtotal).toString().replace('.',','))
            );
            $('#row_grandtotal' + element.data('id')).text(
                (rowgrandtotal >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(rowgrandtotal).toString().replace('.',','))
            );
        });

        $('input[name^="arr_dp_code"]').each(function(index){
            downpayment += parseFloat($('input[name^="arr_nominal"]').eq(index).val().replaceAll(".", "").replaceAll(",","."));
        });

        balance = grandtotal - downpayment + rounding;

        $('#downpayment').val(
            (downpayment >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(downpayment).toString().replace('.',','))
        );
        $('#total').text(
            (total >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(total).toString().replace('.',','))
        );
        $('#tax').text(
            (tax >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(tax).toString().replace('.',','))
        );
        $('#wtax').text(
            (wtax >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(wtax).toString().replace('.',','))
        );
        $('#balance').text(
            (balance >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(balance).toString().replace('.',','))
        );
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
            dom: 'Blfrtip',
            buttons: [
                'selectNone'
            ],
            language: {
                buttons: {
                    selectNone: "Hapus pilihan"
                }
            },
            select: {
                style: 'multi',
                selector: 'td:not(.btn-floating)'
            },
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    status : $('#filter_status').val(),
                    type : $('#filter_type').val(),
                    'account_id[]' : $('#filter_account').val(),
                    company_id : $('#filter_company').val(),
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
                { name: 'post_date', className: 'center-align' },
                { name: 'received_date', className: 'center-align' },
                { name: 'due_date', className: 'center-align' },
                { name: 'document_date', className: 'center-align' },
                { name: 'type', className: 'center-align' },
                { name: 'document', searchable: false, orderable: false, className: 'center-align' },
                { name: 'note', className: 'center-align' },
                { name: 'tax_no', className: 'center-align' },
                { name: 'tax_cut_no', className: 'center-align' },
                { name: 'cut_date', className: 'center-align' },
                { name: 'spk_no', className: 'center-align' },
                { name: 'invoice_no', className: 'center-align' },
                { name: 'subtotal', className: 'right-align' },
                { name: 'percent_discount', className: 'right-align' },
                { name: 'nominal_discount', className: 'right-align' },
                { name: 'total', className: 'right-align' },
                { name: 'tax', className: 'right-align' },
                { name: 'wtax', className: 'right-align' },
                { name: 'grandtotal', className: 'right-align' },
                { name: 'downpayment', className: 'right-align' },
                { name: 'balance', className: 'right-align' },
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

    function rowDetail(id, element) {
        var content = '';
        $.ajax({
            url: '{{ Request::url() }}/row_detail',
            type: 'GET',
            async: false,
            data: {
                id: id
            },
            success: function(response) {
                var tr    = $(element).closest('tr');
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
                    row.child(response).show();
                    tr.addClass('shown');
                    badge.first().removeClass('green');
                    badge.first().addClass('red');
                    icon.first().html('remove');
                }
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
                var formData = new FormData($('#form_data')[0]), passedQty = true, passed = true;

                formData.delete("arr_code[]");
                formData.delete("arr_type[]");
                formData.delete("arr_total[]");
                formData.delete("arr_tax[]");
                formData.delete("arr_wtax[]");
                formData.delete("arr_grandtotal[]");
                formData.delete("arr_dp_code[]");
                formData.delete("arr_nominal[]");
                formData.delete("arr_note[]");
                formData.delete("arr_place[]");
                formData.delete("arr_line[]");
                formData.delete("arr_machine[]");
                formData.delete("arr_department[]");
                formData.delete("arr_warehouse[]");

                $('select[name^="arr_percent_tax"]').each(function(){
                    formData.append('arr_tax_id[]',($(this).find(':selected').data('id') ? $(this).find(':selected').data('id') : ''));
                });

                $('select[name^="arr_percent_wtax"]').each(function(){
                    formData.append('arr_wtax_id[]',($(this).find(':selected').data('id') ? $(this).find(':selected').data('id') : ''));
                });

                $('input[name^="arr_code"]').each(function(){
                    passed = true;                    
                    if($('input[name^="arr_type"][data-id="' + $(this).data('id') + '"]').val() == 'coas'){
                        if($('#arr_coa' + $(this).data('id')).val()){
                            formData.append('arr_code[]',$('#arr_coa' + $(this).data('id')).val());
                        }else{
                            passed = false;
                        }
                    }else{
                        formData.append('arr_code[]',$(this).val());
                    }
                    if(passed == true){
                        formData.append('arr_type[]',$('input[name^="arr_type"][data-id="' + $(this).data('id') + '"]').val());
                        formData.append('arr_total[]',$('input[name^="arr_total"][data-id="' + $(this).data('id') + '"]').val());
                        formData.append('arr_tax[]',$('input[name^="arr_tax"][data-id="' + $(this).data('id') + '"]').val());
                        formData.append('arr_wtax[]',$('input[name^="arr_wtax"][data-id="' + $(this).data('id') + '"]').val());
                        formData.append('arr_grandtotal[]',$('input[name^="arr_grandtotal"][data-id="' + $(this).data('id') + '"]').val());
                        formData.append('arr_note[]',$('input[name^="arr_note"][data-id="' + $(this).data('id') + '"]').val());
                        formData.append('arr_place[]',$('#arr_place' + $(this).data('id')).val());
                        formData.append('arr_line[]',($('#arr_line' + $(this).data('id')).val() ? $('#arr_line' + $(this).data('id')).val() : ''));
                        formData.append('arr_machine[]',($('#arr_machine' + $(this).data('id')).val() ? $('#arr_machine' + $(this).data('id')).val() : ''));
                        formData.append('arr_department[]',($('#arr_department' + $(this).data('id')).val() ? $('#arr_department' + $(this).data('id')).val() : ''));
                        formData.append('arr_warehouse[]',($('#arr_warehouse' + $(this).data('id')).val() ? $('#arr_warehouse' + $(this).data('id')).val() : ''));
                    }
                    let qtyreal = parseFloat($('input[name^="arr_temp_qty"][data-id="' + $(this).data('id') + '"]').val().replaceAll(".", "").replaceAll(",",".")), qtynow = parseFloat($('input[name^="arr_qty"][data-id="' + $(this).data('id') + '"]').val().replaceAll(".", "").replaceAll(",","."));

                    if(qtynow > qtyreal){
                        passedQty = false;
                    }
                });

                $('input[name^="arr_dp_code"]').each(function(index){
                    formData.append('arr_dp_code[]',$(this).val());
                    formData.append('arr_nominal[]',$('input[name^="arr_nominal"]').eq(index).val());
                });

                if(passedQty == true){
                    if(passed == true){
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
                            html: 'Silahkan cek detail form anda. Pastikan tidak ada data yang kosong.'
                        });
                    }
                }else{
                    M.toast({
                        html: 'Salah satu item melebihi jumlah qty dari yang seharusnya.'
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
                $('#account_id').empty();
                $('#account_id').append(`
                    <option value="` + response.account_id + `">` + response.account_name + `</option>
                `);
                $('#type').val(response.type).formSelect();
                $('#company_id').val(response.company_id).formSelect();
                $('#post_date').val(response.post_date);
                $('#received_date').val(response.received_date);
                $('#due_date').val(response.due_date);
                $('#document_date').val(response.document_date);                
                $('#note').val(response.note);
                $('#tax_no').val(response.tax_no);
                $('#tax_cut_no').val(response.tax_cut_no);
                $('#cut_date').val(response.cut_date);
                $('#spk_no').val(response.spk_no);
                $('#invoice_no').val(response.invoice_no);
                $('#downpayment').val(response.downpayment);
                
                if(response.details.length > 0){
                    $('.row_detail').remove();
                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        if(val.type == 'coas'){
                            $('#last-row-detail').before(`
                                <tr class="row_detail">
                                    <input type="hidden" name="arr_code[]" value="" data-id="` + count + `">
                                    <input type="hidden" name="arr_type[]" value="` + val.type + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_total[]" value="` + val.total + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_tax[]" value="` + val.tax + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_wtax[]" value="` + val.wtax + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_grandtotal[]" value="` + val.grandtotal + `" data-id="` + count + `">
                                    <td class="center">
                                        <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]"></select>
                                    </td>
                                    <td class="center">
                                        -
                                    </td>
                                    <td class="center">
                                        -
                                    </td>
                                    <td class="center">
                                        -
                                    </td>
                                    <td class="center">
                                        -
                                    </td>
                                    <td class="center">
                                        -
                                    </td>
                                    <td class="center">
                                        -
                                    </td>
                                    <td class="center">
                                        <input class="browser-default" type="text" name="arr_qty[]" value="` + val.qty_balance + `" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();" style="width:75px !important;">
                                    </td>
                                    <td class="center">
                                        <input class="browser-default" type="text" name="arr_price[]" value="` + val.price + `" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();">
                                    </td>
                                    <td class="center">
                                        -
                                    </td>
                                    <td class="center">
                                        -
                                    </td>
                                    <td class="right-align row_total" id="row_total` + count + `">
                                        ` + val.total + `
                                    </td>
                                    <td class="center">
                                        <select class="browser-default" id="arr_percent_tax` + count + `" name="arr_percent_tax[]" data-id="` + count + `" onchange="countAll();">
                                            <option value="0" data-id="">-- Non-PPN --</option>
                                            @foreach ($tax as $row1)
                                                <option value="{{ $row1->percentage }}" data-id="{{ $row1->id }}">{{ $row1->code.' - '.number_format($row1->percentage,2,',','.').'%' }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="center">
                                        <select class="browser-default" id="arr_include_tax` + count + `" name="arr_include_tax[]" data-id="` + count + `" onchange="countAll();">
                                            <option value="0">Tidak</option>
                                            <option value="1">Ya</option>
                                        </select>
                                    </td>
                                    <td class="right-align" id="row_tax` + count + `">
                                        <input class="browser-default" type="text" name="arr_tax[]" value="` + val.tax + `" data-id="` + count + `" onkeyup="formatRupiah(this);" readonly>
                                    </td>
                                    <td class="center">
                                        <select class="browser-default" id="arr_percent_wtax` + count + `" name="arr_percent_wtax[]" data-id="` + count + `" onchange="countAll();">
                                            <option value="0" data-id="">-- Non-PPH --</option>
                                            @foreach ($wtax as $row2)
                                                <option value="{{ $row2->percentage }}" data-id="{{ $row2->id }}">{{ $row2->code.' - '.number_format($row2->percentage,2,',','.').'%' }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="right-align" id="row_wtax` + count + `">
                                        <input class="browser-default" type="text" name="arr_wtax[]" value="` + val.wtax + `" data-id="` + count + `" onkeyup="formatRupiah(this);" readonly>
                                    </td>
                                    <td class="right-align row_grandtotal" id="row_grandtotal` + count + `">
                                        ` + val.grandtotal + `
                                    </td>
                                    <td>
                                        <input type="text" name="arr_note[]" value="` + val.info + `" data-id="` + count + `">
                                    </td>
                                    <td class="center">
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
                                            @foreach ($machine as $rowmachine)
                                                <option value="{{ $rowmachine->id }}" data-line="{{ $rowmachine->line_id }}">{{ $rowmachine->name }}</option>
                                            @endforeach
                                        </select>    
                                    </td>
                                    <td class="center">
                                        <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                                            @foreach ($department as $rowdept)
                                                <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="center">
                                        <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">
                                            <option value="">--Kosong--</option>
                                            @foreach ($warehouse as $row)
                                                <option value="{{ $row->id }}">{{ $row->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            `);
                            $('#arr_percent_wtax' + count).val(val.percent_wtax);
                            $('#arr_percent_tax' + count).val(val.percent_tax);
                            $('#arr_include_tax' + count).val(val.include_tax);
                            $('#arr_place' + count).val(val.place_id);
                            $('#arr_line' + count).val(val.line_id);
                            $('#arr_machine' + count).val(val.machine_id);
                            $('#arr_department' + count).val(val.department_id);
                            $('#arr_warehouse' + count).val(val.warehouse_id);
                            $('#arr_coa' + count).append(`
                                <option value="` + val.lookable_id + `">` + val.name + `</option>
                            `);
                            select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
                        }else{
                            $('#last-row-detail').before(`
                                <tr class="row_detail">
                                    <input type="hidden" name="arr_type[]" value="` + val.type + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_price[]" value="` + val.price + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_total[]" value="` + val.total + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_grandtotal[]" value="` + val.grandtotal + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_tax[]" value="` + val.tax + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_wtax[]" value="` + val.wtax + `" data-id="` + count + `">
                                    <input type="hidden" id="arr_place` + count + `" name="arr_place[]" value="` + val.place_id + `" data-id="` + count + `">
                                    <input type="hidden" id="arr_line` + count + `" name="arr_line[]" value="` + val.line_id + `" data-id="` + count + `">
                                    <input type="hidden" id="arr_machine` + count + `" name="arr_machine[]" value="` + val.machine_id + `" data-id="` + count + `">
                                    <input type="hidden" id="arr_department` + count + `" name="arr_department[]" value="` + val.department_id + `" data-id="` + count + `">
                                    <input type="hidden" id="arr_warehouse` + count + `" name="arr_warehouse[]" value="` + val.warehouse_id + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_code[]" value="` + val.id + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_temp_qty[]" value="` + val.qty_balance + `" data-id="` + count + `">
                                    <td class="center">
                                        ` + val.rawcode + `
                                    </td>
                                    <td class="center">
                                        ` + val.purchase_no + `
                                    </td>
                                    <td class="center">
                                        ` + val.delivery_no + `
                                    </td>
                                    <td class="">
                                        ` + val.name + `
                                    </td>
                                    <td class="center">
                                        ` + val.buy_unit + `
                                    </td>
                                    <td class="center">
                                        ` + val.qty_received + `
                                    </td>
                                    <td class="center">
                                        ` + val.qty_returned + `
                                    </td>
                                    <td class="center">
                                        <input class="browser-default" type="text" name="arr_qty[]" value="` + val.qty_balance + `" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();">
                                    </td>
                                    <td class="right-align">
                                        ` + val.price + `
                                    </td>
                                    <td class="center">
                                        ` + val.post_date + `
                                    </td>
                                    <td class="center">
                                        ` + val.due_date + `
                                    </td>
                                    <td class="right-align row_total" id="row_total` + count + `">
                                        ` + val.total + `
                                    </td>
                                    <td class="center">
                                        <select class="browser-default" id="arr_percent_tax` + count + `" name="arr_percent_tax[]" data-id="` + count + `" onchange="countAll();">
                                            <option value="0">-- Non-PPN --</option>
                                            @foreach ($tax as $row1)
                                                <option value="{{ $row1->percentage }}" data-id="{{ $row1->id }}">{{ $row1->code.' - '.number_format($row1->percentage,2,',','.').'%' }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="center">
                                        <select class="browser-default" id="arr_include_tax` + count + `" name="arr_include_tax[]" data-id="` + count + `" onchange="countAll();">
                                            <option value="0">Tidak</option>
                                            <option value="1">Ya</option>
                                        </select>
                                    </td>
                                    <td class="right-align" id="row_tax` + count + `">
                                        ` + val.tax + `
                                    </td>
                                    <td class="center">
                                        <select class="browser-default" id="arr_percent_wtax` + count + `" name="arr_percent_wtax[]" data-id="` + count + `" onchange="countAll();">
                                            <option value="0">-- Non-PPH --</option>
                                            @foreach ($wtax as $row2)
                                                <option value="{{ $row2->percentage }}" data-id="{{ $row2->id }}">{{ $row2->code.' - '.number_format($row2->percentage,2,',','.').'%' }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="right-align" id="row_wtax` + count + `">
                                        ` + val.wtax + `    
                                    </td>
                                    <td class="right-align row_grandtotal" id="row_grandtotal` + count + `">
                                        ` + val.grandtotal + `
                                    </td>
                                    <td>
                                        <input class="browser-default" type="text" name="arr_note[]" value="` + val.info + `" data-id="` + count + `">
                                    </td>
                                    <td class="center">
                                        -
                                    </td>
                                    <td class="center">
                                        -
                                    </td>
                                    <td class="center">
                                        -
                                    </td>
                                    <td class="center">
                                        -
                                    </td>
                                    <td class="center">
                                        -
                                    </td>
                                </tr>
                            `);

                            $('#arr_percent_wtax' + count).val(val.percent_wtax);
                            $('#arr_percent_tax' + count).val(val.percent_tax);
                            $('#arr_include_tax' + count).val(val.include_tax);
                        }
                    });
                }

                if(response.downpayments.length > 0){
                    $('#body-detail-dp').empty();
                    $.each(response.downpayments, function(i, val) {
                        var count = makeid(10);
                        $('#body-detail-dp').append(`
                            <tr class="row_detail_dp">
                                <input type="hidden" name="arr_dp_code[]" value="` + val.code + `" data-id="` + count + `">
                                <td class="center">
                                    ` + val.rawcode + `
                                </td>
                                <td class="center">
                                    ` + val.post_date + `
                                </td>
                                <td class="center">
                                    ` + val.grandtotal + `
                                </td>
                                <td class="center">
                                    ` + val.nominal + `
                                </td>
                                <td class="center">
                                    <input name="arr_nominal[]" class="browser-default" type="text" value="` + val.nominal + `" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100% !important;" id="rowNominal`+ count +`">
                                </td>
                            </tr>
                        `);
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
        arr_id_invoice=[];
        $.map(window.table.rows('.selected').nodes(), function (item) {
            var poin = $(item).find('td:nth-child(2)').text().trim();
            arr_id_invoice.push(poin);
        });
        $.ajax({
            url: '{{ Request::url() }}/print',
            type: 'POST',
            dataType: 'JSON',
            data: {
                arr_id: arr_id_invoice,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            success: function(response) {
                $.each(response.data, function(i, val) {
                    if(i%2==0){
                        printService.submit({
                            'type': 'INVOICE',
                            'url': val
                        })
                    }else{
                        printService.submit({
                            'type': 'INVOICE',
                            'url': val
                        })
                    }
                });
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

    function exportExcel(){
        var search = window.table.search(), status = $('#filter_status').val(), type = $('#filter_type').val(), company = $('#filter_company').val(), account = $('#filter_account').val();
        
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status + "&type=" + type + "&company=" + company + "&account=" + account;
    }

    function addDays(){
        if($('#top').val()){
            var result = new Date($('#received_date').val());
            result.setDate(result.getDate() + parseInt($('#top').val()));
            $('#due_date').val(result.toISOString().split('T')[0]);
        }else{
            $('#due_date').val(null);
        }
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
                $('#modal6').modal('open');
                $('#title_data').append(``+data.title+``);
                $('#code_data').append(data.message.code);
                $('#body-journal-table').append(data.tbody);
                $('#user_jurnal').append(`Pengguna `+data.user);
                $('#note_jurnal').append(`Keterangan `+data.message.note);
                $('#ref_jurnal').append(`Referensi `+data.reference);
                $('#post_date_jurnal').append(`Tanggal `+data.message.post_date);
                
                


                console.log(data);
            }
        });
    }
</script>