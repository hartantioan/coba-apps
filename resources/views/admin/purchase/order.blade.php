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
                                                <label for="filter_inventory" style="font-size:1rem;">Tipe Pembelian :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_inventory" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        <option value="1">Persediaan Barang</option>
                                                        <option value="2">Jasa</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_type" style="font-size:1rem;">Tipe PO :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_type" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        <option value="1">Standart PO</option>
                                                        <option value="2">Planned PO</option>
                                                        <option value="3">Blanked PO</option>
                                                        <option value="4">Contract PO</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_shipping" style="font-size:1rem;">Tipe Pengiriman :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_shipping" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        <option value="1">Franco</option>
                                                        <option value="2">Loco</option>
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
                                                <label for="filter_payment" style="font-size:1rem;">Tipe Pembayaran :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_payment" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        <option value="1">Cash</option>
                                                        <option value="2">Credit</option>
                                                        <option value="3">CBD</option>
                                                        <option value="4">DP</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_supplier" style="font-size:1rem;">Supplier :</label>
                                                <div class="input-field">
                                                    <select class="browser-default" id="filter_supplier" name="filter_supplier" multiple="multiple" style="width:100% !important;" onchange="loadDataTable()"></select>
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
                                                        <th rowspan="2">Supplier</th>
                                                        <th rowspan="2">Tipe PO</th>
                                                        <th rowspan="2">Jenis PO</th>
                                                        <th rowspan="2">Pengiriman</th>
                                                        <th rowspan="2">Perusahaan</th>
                                                        <th colspan="2" class="center">Proforma</th>
                                                        <th colspan="2" class="center">Pembayaran</th>
                                                        <th colspan="2" class="center">Mata Uang</th>
                                                        <th colspan="2" class="center">Tanggal</th>
                                                        <th colspan="3" class="center">Penerima</th>
                                                        <th rowspan="2">Keterangan</th>
                                                        <th rowspan="2">Subtotal</th>
                                                        <th rowspan="2">Diskon</th>
                                                        <th rowspan="2">Total</th>
                                                        <th rowspan="2">PPN</th>
                                                        <th rowspan="2">PPh</th>
                                                        <th rowspan="2">Grandtotal</th>
                                                        <th rowspan="2">Status</th>
                                                        <th rowspan="2">Action</th>
                                                    </tr>
                                                    <tr>
                                                        <th>Nomor</th>
                                                        <th>Dokumen</th>
                                                        <th>Tipe</th>
                                                        <th>Termin</th>
                                                        <th>Kode</th>
                                                        <th>Konversi</th>
                                                        <th>Post</th>
                                                        <th>Kirim</th>
                                                        <th>Nama</th>
                                                        <th>Alamat</th>
                                                        <th>Telepon</th>
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
    <div class="modal-content" style="overflow-x: hidden;max-width: 100%;">
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
                                <input type="hidden" id="savesubtotal" name="savesubtotal" value="0,00">
                                <input type="hidden" id="savetotal" name="savetotal" value="0,00">
                                <input type="hidden" id="savetax" name="savetax" value="0,00">
                                <input type="hidden" id="savewtax" name="savewtax" value="0,00">
                                <input type="hidden" id="savegrandtotal" name="savegrandtotal" value="0,00">
                                <select class="browser-default" id="supplier_id" name="supplier_id" onchange="getTopSupplier();"></select>
                                <label class="active" for="supplier_id">Supplier</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="inventory_type" name="inventory_type">
                                    <option value="1">Persediaan Barang</option>
                                    <option value="2">Jasa</option>
                                </select>
                                <label class="" for="inventory_type">Tipe Pembelian</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="purchasing_type" name="purchasing_type">
                                    <option value="1">Standart PO</option>
                                    <option value="2">Planned PO</option>
                                    <option value="3">Blanked PO</option>
                                    <option value="4">Contract PO</option>
                                </select>
                                <label class="" for="purchasing_type">Kategori PO</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="shipping_type" name="shipping_type">
                                    <option value="1">Franco</option>
                                    <option value="2">Loco</option>
                                </select>
                                <label class="" for="shipping_type">Tipe Pengiriman</label>
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
                                <input id="document_no" name="document_no" type="text" placeholder="No. Dokumen">
                                <label class="active" for="document_no">No. Dokumen</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="payment_type" name="payment_type" onchange="resetTerm()">
                                    <option value="1">Cash</option>
                                    <option value="2">Credit</option>
                                    <option value="3">CBD</option>
                                    <option value="4">DP</option>
                                </select>
                                <label class="" for="payment_type">Tipe Pembayaran</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="payment_term" name="payment_term" type="number" value="0" min="0" step="1">
                                <label class="active" for="payment_term">Termin Pembayaran (hari)</label>
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
                            <div class="input-field col m3 s12">
                                <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                <label class="active" for="post_date">Tgl. Posting</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="delivery_date" name="delivery_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. kirim">
                                <label class="active" for="delivery_date">Tgl. Kirim</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="receiver_name" name="receiver_name" type="text" placeholder="Nama Penerima">
                                <label class="active" for="receiver_name">Nama Penerima</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="receiver_address" name="receiver_address" type="text" placeholder="Alamat Penerima">
                                <label class="active" for="receiver_address">Alamat Penerima</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="receiver_phone" name="receiver_phone" type="text" placeholder="Kontak Penerima">
                                <label class="active" for="receiver_phone">Kontak Penerima</label>
                            </div>
                            <div class="file-field input-field col m3 s12">
                                <div class="btn">
                                    <span>Dokumen PO</span>
                                    <input type="file" name="document_po" id="document_po">
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text">
                                </div>
                            </div>
                            <div class="col m12 s12">
                                <div class="col m3 s3">
                                    <p class="mt-2 mb-2">
                                        <h5>Purchase Request</h5>
                                        <div class="row">
                                            <div class="input-field col m12 s12">
                                                <select class="browser-default" id="purchase_request_id" name="purchase_request_id"></select>
                                                <label class="active" for="purchase_request_id">Purchase Request (Jika ada)</label>
                                            </div>
                                            <div class="col m12 12">
                                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="getDetails('po');" href="javascript:void(0);">
                                                    <i class="material-icons left">add</i> Purchase Request
                                                </a>
                                            </div>
                                        </div> 
                                    </p>
                                </div>
                                <div class="col m3 s3">
                                    <p class="mt-2 mb-2">
                                        <h5>Goods Issue / Barang Keluar</h5>
                                        <div class="row">
                                            <div class="input-field col m12 s12">
                                                <select class="browser-default" id="good_issue_id" name="good_issue_id"></select>
                                                <label class="active" for="good_issue_id">Goods Issue / Barang Keluar (Jika ada)</label>
                                            </div>
                                            <div class="col m12 12">
                                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="getDetails('gi');" href="javascript:void(0);">
                                                    <i class="material-icons left">add</i> Goods Issue
                                                </a>
                                            </div>
                                        </div>
                                    </p>
                                </div>
                                <div class="col m6 s6">
                                    <h6><b>PR/GI Terpakai</b> (hapus untuk bisa diakses pengguna lain) : <i id="list-used-data"></i></h6>
                                </div>
                            </div>
                            <div class="col m12 s12" style="overflow:auto;width:100% !important;">
                                <p class="mt-2 mb-2">
                                    <h4>Detail Produk</h4>
                                    <table class="bordered" style="width:2500px;">
                                        <thead>
                                            <tr>
                                                <th class="center">Item / Coa Jasa</th>
                                                <th class="center">Qty</th>
                                                <th class="center">Satuan</th>
                                                <th class="center">Harga</th>
                                                <th class="center">
                                                    PPN
                                                    <label class="pl-2">
                                                        <input type="checkbox" onclick="chooseAllPpn(this)">
                                                        <span style="padding-left: 25px;">Semua</span>
                                                    </label>
                                                </th>
                                                <th class="center">Termasuk PPN</th>
                                                <th class="center">
                                                    PPh
                                                    <label class="pl-2">
                                                        <input type="checkbox" onclick="chooseAllPph(this)">
                                                        <span style="padding-left: 25px;">Semua</span>
                                                    </label>
                                                </th>
                                                <th class="center">Disc1(%)</th>
                                                <th class="center">Disc2(%)</th>
                                                <th class="center">Disc3(Rp)</th>
                                                <th class="center">Subtotal</th>
                                                <th class="center">Keterangan 1</th>
                                                <th class="center">Keterangan 2</th>
                                                <th class="center">Plant</th>
                                                <th class="center">Line</th>
                                                <th class="center">Mesin</th>
                                                <th class="center">Departemen</th>
                                                <th class="center">Gudang</th>
                                                <th class="center">Hapus</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-item">
                                            <tr id="last-row-item">
                                                <td colspan="19" class="center">
                                                    <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addItem()" href="javascript:void(0);">
                                                        <i class="material-icons left">add</i> New Item
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
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
                                            <td width="50%">Subtotal</td>
                                            <td width="50%" class="right-align"><span id="subtotal">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>Discount</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="discount" name="discount" type="text" value="0" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100%;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Total</td>
                                            <td class="right-align"><span id="total">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>PPN</td>
                                            <td class="right-align"><span id="tax">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>PPh</td>
                                            <td class="right-align"><span id="wtax">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>Grandtotal</td>
                                            <td class="right-align"><span id="grandtotal">0,00</span></td>
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
            <div class="col s12" id="show_structure">
                <div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;">

                </div>
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
            <div class="col s12" id="show_detail">

            </div>
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

        $('#modal4').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#show_detail').empty();
            }
        });

        window.table.search('{{ $code }}').draw();
        
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                $('#post_date').attr('min','{{ $minDate }}');
                $('#post_date').attr('max','{{ $maxDate }}');
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
                $('#supplier_id').empty();
                $('#savesubtotal,#savetotal,#savetax,#savewtax,#savegrandtotal').val('0,00');
                $('.row_item').each(function(){
                    $(this).remove();
                });
                M.updateTextFields();
                $('#subtotal,#total,#tax,#grandtotal,#wtax').text('0,00');
                $('#purchase_request_id').empty();
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
        
        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
            countAll();
        });

        select2ServerSide('#supplier_id,#filter_supplier', '{{ url("admin/select2/supplier") }}');
        select2ServerSide('#purchase_request_id', '{{ url("admin/select2/purchase_request") }}');
        select2ServerSide('#good_issue_id', '{{ url("admin/select2/good_issue") }}');
    });
    
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

    var defaultValuePpn = 0, defaultValuePph;

    function chooseAllPpn(element){
        if($(element).is(':checked')){
            $('select[name^="arr_tax"]').each(function(){
                if(parseFloat($(this).val()) > 0){
                    defaultValuePpn = $(this).val();
                }else{
                    $(this).val(defaultValuePpn.toString()).formSelect();
                }
            });
        }else{
            $('select[name^="arr_tax"]').each(function(){
                $(this).val('0');
            });
        }
        countAll();
    }

    function chooseAllPph(element){
        if($(element).is(':checked')){
            $('select[name^="arr_wtax"]').each(function(){
                if(parseFloat($(this).val()) > 0){
                    defaultValuePph = $(this).val();
                }else{
                    $(this).val(defaultValuePph.toString()).formSelect();
                }
            });
        }else{
            $('select[name^="arr_wtax"]').each(function(){
                $(this).val('0');
            });
        }
        countAll();
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
    
    function getRowUnit(val){
        $('#tempPrice' + val).empty();
        $("#arr_warehouse" + val).empty();
        if($("#arr_item" + val).val()){
            $('#arr_unit' + val).text($("#arr_item" + val).select2('data')[0].buy_unit);
            if($("#arr_item" + val).select2('data')[0].old_prices.length > 0){
                $.each($("#arr_item" + val).select2('data')[0].old_prices, function(i, value) {
                    if($('#supplier_id').val()){
                        if(value.supplier_id == $('#supplier_id').val()){
                            $('#tempPrice' + val).append(`
                                <option value="` + value.price + `">` + value.purchase_code + ` Supplier ` + value.supplier_name + ` Tgl ` + value.post_date + `</option>
                            `);
                        }
                    }else{
                        $('#tempPrice' + val).append(`
                            <option value="` + value.price + `">` + value.purchase_code + ` Supplier ` + value.supplier_name + ` Tgl ` + value.post_date + `</option>
                        `);
                    }
                });
            }
            if($("#arr_item" + val).select2('data')[0].list_warehouse.length > 0){
                $.each($("#arr_item" + val).select2('data')[0].list_warehouse, function(i, value) {
                    $('#arr_warehouse' + val).append(`
                        <option value="` + value.id + `">` + value.name + `</option>
                    `);
                });
            }else{
                $("#arr_warehouse" + val).append(`
                    <option value="">--Gudang tidak diatur di master data Grup Item--</option>
                `);
            }
        }else{
            $('#arr_unit' + val).text('-');
            $("#arr_warehouse" + val).append(`
                <option value="">--Silahkan pilih item--</option>
            `);
        }
    }

    var tempTerm = 0;

    function resetTerm(){
        if($('#payment_type').val() == '1'){
            $('#payment_term').val('0');
        }else{
            $('#payment_term').val(tempTerm);
        }
    }

    function getTopSupplier(){
        if($("#supplier_id").val()){
            tempTerm = parseInt($("#supplier_id").select2('data')[0].top);
        }else{
            tempTerm = 0;
        }
        resetTerm();
    }

    function getDetails(type){

        let nil;

        if(type == 'po'){
            nil = $('#purchase_request_id').val();
        }else if(type == 'gi'){
            nil = $('#good_issue_id').val();
        }

        if(nil){
            $.ajax({
                url: '{{ Request::url() }}/get_details',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: nil,
                    type : type,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    loadingClose('.modal-content');

                    if(response.status == 500){
                        swal({
                            title: 'Ups!',
                            text: response.message,
                            icon: 'warning'
                        });
                    }else{
                        if(response.details.length > 0){
                            $('#list-used-data').append(`
                                <div class="chip purple darken-4 gradient-shadow white-text">
                                    ` + response.code + `
                                    <i class="material-icons close data-used" onclick="removeUsedData('` + response.id + `','` + type + `')">close</i>
                                </div>
                            `);
                            $.each(response.details, function(i, val) {
                                var count = makeid(10);
                                
                                $('#last-row-item').before(`
                                    <tr class="row_item" data-id="` + response.id + `">
                                        <input type="hidden" name="arr_data[]" value="` + val.reference_id + `">
                                        <input type="hidden" name="arr_type[]" value="` + type + `">
                                        <td>
                                            <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                                        </td>
                                        <td>
                                            <input name="arr_qty[]" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" data-qty="` + val.qty + `" style="text-align:right;width:100px;" id="rowQty`+ count +`">
                                        </td>
                                        <td class="center">
                                            <span id="arr_unit` + count + `">` + val.unit + `</span>
                                        </td>
                                        <td class="center">
                                            <input list="tempPrice` + count + `" name="arr_price[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowPrice`+ count +`">
                                            <datalist id="tempPrice` + count + `"></datalist>
                                        </td>
                                        <td>
                                            <select class="browser-default" id="arr_tax` + count + `" name="arr_tax[]" onchange="countAll();">
                                                <option value="0" data-id="0">-- Pilih ini jika non-PPN --</option>
                                                @foreach ($tax as $row)
                                                    <option value="{{ $row->percentage }}" {{ $row->is_default_ppn ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <label>
                                                <input type="checkbox" id="arr_is_include_tax` + count + `" name="arr_is_include_tax[]" value="1" onclick="countAll();">
                                                <span>Ya/Tidak</span>
                                            </label>
                                        </td>
                                        <td>
                                            <select class="browser-default" id="arr_wtax` + count + `" name="arr_wtax[]" onchange="countAll();">
                                                <option value="0" data-id="0">-- Pilih ini jika non-PPh --</option>
                                                @foreach ($wtax as $row)
                                                    <option value="{{ $row->percentage }}" {{ $row->is_default_pph ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="center">
                                            <input name="arr_disc1[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc1`+ count +`">
                                        </td>
                                        <td class="center">
                                            <input name="arr_disc2[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc2`+ count +`">
                                        </td>
                                        <td class="center">
                                            <input name="arr_disc3[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowDisc3`+ count +`">
                                        </td>
                                        <td class="center">
                                            <span id="arr_subtotal` + count + `" class="arr_subtotal">0</span>
                                        </td>
                                        <td>
                                            <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 1..." value="` + val.note + `">
                                        </td>
                                        <td>
                                            <input name="arr_note2[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 2..." value="` + val.note2 + `">
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
                                            <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                                                <option value="">--Kosong--</option>
                                                @foreach ($department as $rowdept)
                                                    <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                                                @endforeach
                                            </select>    
                                        </td>
                                        <td>
                                            <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">
                                                
                                            </select>
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
                                $('#arr_warehouse' + count).append(`
                                    <option value="` + val.warehouse_id + `">` + val.warehouse_name + `</option>
                                `);
                                select2ServerSide('#arr_item' + count, '{{ url("admin/select2/purchase_item") }}');
                                $('#arr_place' + count).val(val.place_id);
                                $('#arr_line' + count).val(val.line_id);
                                $('#arr_machine' + count).val(val.machine_id);
                                $('#arr_department' + count).val(val.department_id);
                                if(val.old_prices.length > 0){
                                    $.each(val.old_prices, function(i, value) {
                                        if($('#supplier_id').val()){
                                            if(value.supplier_id == $('#supplier_id').val()){
                                                $('#tempPrice' + count).append(`
                                                    <option value="` + value.price + `">` + value.purchase_code + ` Supplier ` + value.supplier_name + ` Tgl ` + value.post_date + `</option>
                                                `);
                                            }
                                        }else{
                                            $('#tempPrice' + count).append(`
                                                <option value="` + value.price + `">` + value.purchase_code + ` Supplier ` + value.supplier_name + ` Tgl ` + value.post_date + `</option>
                                            `);
                                        }
                                    });
                                }
                            });
                        }
                    }
                    M.updateTextFields();
                    $('#purchase_request_id,#good_issue_id').empty();
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
            /* $('.row_item').each(function(){
                $(this).remove();
            }); */
        }
    }

    function addItem(){
        var count = makeid(10);
        if($('#inventory_type').val() == '1'){
            $('#last-row-item').before(`
                <tr class="row_item">
                    <input type="hidden" name="arr_data[]" value="0">
                    <input type="hidden" name="arr_type[]" value="">
                    <td>
                        <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                    </td>
                    <td>
                        <input name="arr_qty[]" class="browser-default" type="text" value="0" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" data-qty="0" style="text-align:right;width:100px;" id="rowQty`+ count +`">
                    </td>
                    <td class="center">
                        <span id="arr_unit` + count + `">-</span>
                    </td>
                    <td class="center">
                        <input list="tempPrice` + count + `" name="arr_price[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowPrice`+ count +`">
                        <datalist id="tempPrice` + count + `"></datalist>
                    </td>
                    <td>
                        <select class="browser-default" id="arr_tax` + count + `" name="arr_tax[]" onchange="countAll();">
                            <option value="0" data-id="0">-- Pilih ini jika non-PPN --</option>
                            @foreach ($tax as $row)
                                <option value="{{ $row->percentage }}" {{ $row->is_default_ppn ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <label>
                            <input type="checkbox" id="arr_is_include_tax` + count + `" name="arr_is_include_tax[]" value="1" onclick="countAll();">
                            <span>Ya/Tidak</span>
                        </label>
                    </td>
                    <td>
                        <select class="browser-default" id="arr_wtax` + count + `" name="arr_wtax[]" onchange="countAll();">
                            <option value="0" data-id="0">-- Pilih ini jika non-PPh --</option>
                            @foreach ($wtax as $row)
                                <option value="{{ $row->percentage }}" {{ $row->is_default_pph ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="center">
                        <input name="arr_disc1[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc1`+ count +`">
                    </td>
                    <td class="center">
                        <input name="arr_disc2[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc2`+ count +`">
                    </td>
                    <td class="center">
                        <input name="arr_disc3[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowDisc3`+ count +`">
                    </td>
                    <td class="center">
                        <span id="arr_subtotal` + count + `" class="arr_subtotal">0</span>
                    </td>
                    <td>
                        <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 1...">
                    </td>
                    <td>
                        <input name="arr_note2[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 2...">
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
                        <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                            <option value="">--Kosong--</option>
                            @foreach ($department as $rowdept)
                                <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                            @endforeach
                        </select>    
                    </td>
                    <td>
                        <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">
                            <option value="">--Silahkan pilih item--</option>
                        </select>
                    </td>
                    <td class="center">
                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                            <i class="material-icons">delete</i>
                        </a>
                    </td>
                </tr>
            `);
            select2ServerSide('#arr_item' + count, '{{ url("admin/select2/purchase_item") }}');
            
        }else if($('#inventory_type').val() == '2'){

            $('#last-row-item').before(`
                <tr class="row_item">
                    <input type="hidden" name="arr_data[]" value="0">
                    <input type="hidden" name="arr_type[]" value="">
                    <td>
                        <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]"></select>
                    </td>
                    <td>
                        <input name="arr_qty[]" class="browser-default" type="text" value="0" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" data-qty="0" style="text-align:right;width:100px;" id="rowQty`+ count +`">
                    </td>
                    <td class="center">
                        <span id="arr_unit` + count + `">-</span>
                    </td>
                    <td class="center">
                        <input name="arr_price[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowPrice`+ count +`">
                    </td>
                    <td>
                        <select class="browser-default" id="arr_tax` + count + `" name="arr_tax[]" onchange="countAll();">
                            <option value="0" data-id="0">-- Pilih ini jika non-PPN --</option>
                            @foreach ($tax as $row)
                                <option value="{{ $row->percentage }}" {{ $row->is_default_ppn ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <label>
                            <input type="checkbox" id="arr_is_include_tax` + count + `" name="arr_is_include_tax[]" value="1" onclick="countAll();">
                            <span>Ya/Tidak</span>
                        </label>
                    </td>
                    <td>
                        <select class="browser-default" id="arr_wtax` + count + `" name="arr_wtax[]" onchange="countAll();">
                            <option value="0" data-id="0">-- Pilih ini jika non-PPh --</option>
                            @foreach ($wtax as $row)
                                <option value="{{ $row->percentage }}" {{ $row->is_default_pph ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="center">
                        <input name="arr_disc1[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc1`+ count +`">
                    </td>
                    <td class="center">
                        <input name="arr_disc2[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc2`+ count +`">
                    </td>
                    <td class="center">
                        <input name="arr_disc3[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowDisc3`+ count +`">
                    </td>
                    <td class="center">
                        <span id="arr_subtotal` + count + `" class="arr_subtotal">0</span>
                    </td>
                    <td>
                        <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 1...">
                    </td>
                    <td>
                        <input name="arr_note2[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 2...">
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
                        <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                            <option value="">--Kosong--</option>
                            @foreach ($department as $rowdept)
                                <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                            @endforeach
                        </select>    
                    </td>
                    <td class="center">
                        -
                    </td>
                    <td class="center">
                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                            <i class="material-icons">delete</i>
                        </a>
                    </td>
                </tr>
            `);
            select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
        }
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

    function changeDateMinimum(val){
        if(val){
            $('#due_date,#required_date').attr("min",val);
            $('input[name^="arr_required_date"]').each(function(){
                $(this).attr("min",val);
            });
        }
    }

    function removeUsedData(id,type){
        $.ajax({
            url: '{{ Request::url() }}/remove_used_data',
            type: 'POST',
            dataType: 'JSON',
            data: { 
                id : id,
                type : type,
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
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle',
                'selectAll',
                'selectNone',
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
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    status : $('#filter_status').val(),
                    inventory_type : $('#filter_inventory').val(),
                    purchasing_type : $('#filter_type').val(),
                    shipping_type : $('#filter_shipping').val(),
                    'supplier_id[]' : $('#filter_supplier').val(),
                    company_id : $('#filter_company').val(),
                    is_tax : $('#filter_is_tax').val(),
                    is_include_tax : $('#filter_is_include_tax').val(),
                    payment_type : $('#filter_payment').val(),
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
                { name: 'supplier_id', className: 'center-align' },
                { name: 'inventory_type', className: 'center-align' },
                { name: 'purchasing_type', className: 'center-align' },
                { name: 'shipping_type', className: 'center-align' },
                { name: 'company_id', className: 'center-align' },
                { name: 'document_no', className: 'center-align' },
                { name: 'document_po', className: 'center-align' },
                { name: 'payment_tye', className: 'center-align' },
                { name: 'payment_term', className: 'center-align' },
                { name: 'currency_id', className: 'center-align' },
                { name: 'currency_rate', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'delivery_date', className: 'center-align' },
                { name: 'receiver_name', className: 'center-align' },
                { name: 'receiver_address', className: 'center-align' },
                { name: 'receiver_phone', className: 'center-align' },
                { name: 'note', className: 'center-align' },
                { name: 'subtotal', className: 'right-align' },
                { name: 'discount', className: 'right-align' },
                { name: 'total', className: 'right-align' },
                { name: 'tax', className: 'right-align' },
                { name: 'wtax', className: 'right-align' },
                { name: 'grandtotal', className: 'right-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ],
        });
        $('.dt-buttons').appendTo('#datatable_buttons');

        $('select[name="datatable_serverside_length"]').addClass('browser-default');
	}

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
                var formData = new FormData($('#form_data')[0]);

                formData.delete("arr_tax[]");
                formData.delete("arr_is_include_tax[]");
                formData.delete("arr_wtax[]");
                formData.delete("arr_wtax[]");
                formData.delete("arr_note[]");
                formData.delete("arr_note2[]");
                formData.delete("arr_warehouse[]");
                formData.delete("arr_line[]");

                $('select[name^="arr_tax"]').each(function(index){
                    formData.append('arr_tax[]',$(this).val());
                    formData.append('arr_tax_id[]',$('option:selected',this).data('id'));
                    formData.append('arr_wtax_id[]',$('select[name^="arr_wtax"]').eq(index).find(':selected').data('id'));
                    formData.append('arr_is_include_tax[]',($('input[name^="arr_is_include_tax"]').eq(index).is(':checked') ? '1' : '0'));
                    formData.append('arr_wtax[]',$('select[name^="arr_wtax"]').eq(index).val());
                    formData.append('arr_note[]',($('input[name^="arr_note"]').eq(index).val() ? $('input[name^="arr_note"]').eq(index).val() : ''));
                    formData.append('arr_note2[]',($('input[name^="arr_note2"]').eq(index).val() ? $('input[name^="arr_note2"]').eq(index).val() : ''));
                    formData.append('arr_line[]',($('select[name^="arr_line"]').eq(index).val() ? $('select[name^="arr_line"]').eq(index).val() : ''));
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
                $('#supplier_id').empty();
                $('#supplier_id').append(`
                    <option value="` + response.account_id + `">` + response.supplier_name + `</option>
                `);
                $('#inventory_type').val(response.inventory_type).formSelect();
                $('#purchasing_type').val(response.purchasing_type).formSelect();
                $('#shipping_type').val(response.shipping_type).formSelect(); 
                $('#company_id').val(response.company_id).formSelect();
                $('#document_no').val(response.document_no);
                $('#payment_type').val(response.payment_type).formSelect();
                $('#payment_term').val(response.payment_term);
                $('#currency_id').val(response.currency_id).formSelect();
                $('#currency_rate').val(response.currency_rate);
                $('#post_date').val(response.post_date);
                $('#delivery_date').val(response.delivery_date);
                $('#percent_tax').val(response.percent_tax);
                $('#receiver_name').val(response.receiver_name);
                $('#receiver_address').val(response.receiver_address);
                $('#receiver_phone').val(response.receiver_phone);
                
                $('#note').val(response.note);
                $('#subtotal').text(response.subtotal);
                $('#savesubtotal').val(response.subtotal);
                $('#discount').val(response.discount);
                $('#total').text(response.total);
                $('#savetotal').val(response.total);
                $('#tax').text(response.tax);
                $('#savetax').val(response.tax);
                $('#wtax').text(response.wtax);
                $('#savewtax').val(response.wtax);
                $('#grandtotal').text(response.grandtotal);
                $('#savegrandtotal').val(response.grandtotal);
                
                if(response.details.length > 0){
                    $('.row_item').each(function(){
                        $(this).remove();
                    });

                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        if(response.inventory_type == '1'){
                            $('#last-row-item').before(`
                                <tr class="row_item">
                                    <input type="hidden" name="arr_data[]" value="` + val.reference_id + `">
                                    <input type="hidden" name="arr_type[]" value="` + val.type + `">
                                    <td>
                                        <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                                    </td>
                                    <td>
                                        <input name="arr_qty[]" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" data-qty="` + val.qty + `" style="text-align:right;width:100px;" id="rowQty`+ count +`">
                                    </td>
                                    <td class="center">
                                        <span id="arr_unit` + count + `">` + val.unit + `</span>
                                    </td>
                                    <td class="center">
                                        <input name="arr_price[]" class="browser-default" type="text" value="` + val.price + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowPrice`+ count +`">
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_tax` + count + `" name="arr_tax[]" onchange="countAll();">
                                            <option value="0" data-id="0">-- Pilih ini jika non-PPN --</option>
                                            @foreach ($tax as $row)
                                                <option value="{{ $row->percentage }}" data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <label>
                                            <input type="checkbox" id="arr_is_include_tax` + count + `" name="arr_is_include_tax[]" value="1" onclick="countAll();">
                                            <span>Ya/Tidak</span>
                                        </label>
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_wtax` + count + `" name="arr_wtax[]" onchange="countAll();">
                                            <option value="0" data-id="0">-- Pilih ini jika non-PPh --</option>
                                            @foreach ($wtax as $row)
                                                <option value="{{ $row->percentage }}" data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="center">
                                        <input name="arr_disc1[]" class="browser-default" type="text" value="` + val.disc1 + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc1`+ count +`">
                                    </td>
                                    <td class="center">
                                        <input name="arr_disc2[]" class="browser-default" type="text" value="` + val.disc2 + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc2`+ count +`">
                                    </td>
                                    <td class="center">
                                        <input name="arr_disc3[]" class="browser-default" type="text" value="` + val.disc3 + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowDisc3`+ count +`">
                                    </td>
                                    <td class="center">
                                        <span id="arr_subtotal` + count + `" class="arr_subtotal">` + val.subtotal + `</span>
                                    </td>
                                    <td>
                                        <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 1..." value="` + val.note + `">
                                    </td>
                                    <td>
                                        <input name="arr_note2[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 2..." value="` + val.note2 + `">
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
                                        <select class="browser-default" id="arr_department` + count + `" name="arr_department[]">
                                            <option value="">--Kosong--</option>
                                            @foreach ($department as $rowdept)
                                                <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                                            @endforeach
                                        </select>    
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]"></select>
                                    </td>
                                    <td class="center">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);" onclick="removeUsedData('` + val.id + `')">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                </tr>
                            `);
                            $('#arr_item' + count).append(`
                                <option value="` + val.item_id + `">` + val.item_name + `</option>
                            `);
                            select2ServerSide('#arr_item' + count, '{{ url("admin/select2/purchase_item") }}');
                            if(val.is_include_tax){
                                $('#arr_is_include_tax' + count).prop( "checked", true);
                            }
                            $('#arr_warehouse' + count).append(`
                                <option value="` + val.warehouse_id + `">` + val.warehouse_name + `</option>
                            `);
                            $('#arr_place' + count).val(val.place_id);
                            $('#arr_line' + count).val(val.line_id);
                            $('#arr_department' + count).val(val.department_id);
                            $('#arr_machine' + count).val(val.machine_id);
                            $("#arr_tax" + count + " option[data-id='" + val.tax_id + "']").prop("selected",true);
                            $("#arr_wtax" + count + " option[data-id='" + val.wtax_id + "']").prop("selected",true);

                        }else if(response.inventory_type == '2'){

                            $('#last-row-item').before(`
                                <tr class="row_item">
                                    <input type="hidden" name="arr_data[]" value="` + val.reference_id + `">
                                    <input type="hidden" name="arr_type[]" value="` + val.type + `">
                                    <td>
                                        <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]"></select>
                                    </td>
                                    <td>
                                        <input name="arr_qty[]" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" data-qty="` + val.qty + `" style="text-align:right;width:100px;" id="rowQty`+ count +`">
                                    </td>
                                    <td class="center">
                                        <span id="arr_unit` + count + `">` + val.unit + `</span>
                                    </td>
                                    <td class="center">
                                        <input name="arr_price[]" class="browser-default" type="text" value="` + val.price + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowPrice`+ count +`">
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_tax` + count + `" name="arr_tax[]" onchange="countAll();">
                                            <option value="0" data-id="0">-- Pilih ini jika non-PPN --</option>
                                            @foreach ($tax as $row)
                                                <option value="{{ $row->percentage }}" data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <label>
                                            <input type="checkbox" id="arr_is_include_tax` + count + `" name="arr_is_include_tax[]" value="1" onclick="countAll();">
                                            <span>Ya/Tidak</span>
                                        </label>
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_wtax` + count + `" name="arr_wtax[]" onchange="countAll();">
                                            <option value="0" data-id="0">-- Pilih ini jika non-PPh --</option>
                                            @foreach ($wtax as $row)
                                                <option value="{{ $row->percentage }}" data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="center">
                                        <input name="arr_disc1[]" class="browser-default" type="text" value="` + val.disc1 + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc1`+ count +`">
                                    </td>
                                    <td class="center">
                                        <input name="arr_disc2[]" class="browser-default" type="text" value="` + val.disc2 + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc2`+ count +`">
                                    </td>
                                    <td class="center">
                                        <input name="arr_disc3[]" class="browser-default" type="text" value="` + val.disc3 + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowDisc3`+ count +`">
                                    </td>
                                    <td class="center">
                                        <span id="arr_subtotal` + count + `" class="arr_subtotal">` + val.subtotal + `</span>
                                    </td>
                                    <td>
                                        <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 1..." value="` + val.note + `">
                                    </td>
                                    <td>
                                        <input name="arr_note2[]" class="materialize-textarea" type="text" placeholder="Keterangan barang 2..." value="` + val.note2 + `">
                                    </td>
                                    <td>
                                        <select class="form-control" id="arr_place` + count + `" name="arr_place[]">
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
                                        <select class="form-control" id="arr_department` + count + `" name="arr_department[]">
                                            <option value="">--Kosong--</option>
                                            @foreach ($department as $rowdept)
                                                <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                                            @endforeach
                                        </select>    
                                    </td>
                                    <td class="center">
                                        -
                                    </td>
                                    <td class="center">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                </tr>
                            `);
                            if(val.is_include_tax){
                                $('#arr_is_include_tax' + count).prop( "checked", true);
                            }
                            
                            $('#arr_coa' + count).append(`
                                <option value="` + val.coa_id + `">` + val.coa_name + `</option>
                            `);
                            select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
                            $('#arr_place' + count).val(val.place_id);
                            $('#arr_department' + count).val(val.department_id);
                            $('#arr_line' + count).val(val.line_id);
                            $('#arr_machine' + count).val(val.machine_id);
                            $("#arr_tax" + count + " option[data-id='" + val.tax_id + "']").prop("selected",true);
                            $("#arr_wtax" + count + " option[data-id='" + val.wtax_id + "']").prop("selected",true);
                        }
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

    function countRow(id){
        var qty = parseFloat($('#rowQty' + id).val().replaceAll(".", "").replaceAll(",",".")), 
            qtylimit = parseFloat($('#rowQty' + id).data('qty').toString().replaceAll(".", "").replaceAll(",",".")), 
            price = parseFloat($('#rowPrice' + id).val().replaceAll(".", "").replaceAll(",",".")), 
            disc1 = parseFloat($('#rowDisc1' + id).val().replaceAll(".", "").replaceAll(",",".")), 
            disc2 = parseFloat($('#rowDisc2' + id).val().replaceAll(".", "").replaceAll(",",".")), 
            disc3 = parseFloat($('#rowDisc3' + id).val().replaceAll(".", "").replaceAll(",","."));

        if(qtylimit > 0){
            if(qty > qtylimit){
                qty = qtylimit;
                $('#rowQty' + id).val(formatRupiahIni(qty.toFixed(3).toString().replace('.',',')));
            }
        }

        var finalpricedisc1 = price - (price * (disc1 / 100));
        var finalpricedisc2 = finalpricedisc1 - (finalpricedisc1 * (disc2 / 100));
        var finalpricedisc3 = finalpricedisc2 - disc3;

        if((finalpricedisc3 * qty).toFixed(2) >= 0){
            $('#arr_subtotal' + id).text(formatRupiahIni((finalpricedisc3 * qty).toFixed(2).toString().replace('.',',')));
        }else{
            $('#arr_subtotal' + id).text('-' + formatRupiahIni((finalpricedisc3 * qty).toFixed(2).toString().replace('.',',')));
        }

        countAll();
    }

    function countAll(){
        var subtotal = 0, tax = 0, discount = parseFloat($('#discount').val().replaceAll(".", "").replaceAll(",",".")), total = 0, grandtotal = 0, wtax = 0;

        $('.arr_subtotal').each(function(index){
			subtotal += parseFloat($(this).text().replaceAll(".", "").replaceAll(",","."));
		});

        $('.arr_subtotal').each(function(index){
            let rownominal = parseFloat($(this).text().replaceAll(".", "").replaceAll(",",".")), rowtax = 0, rowwtax = 0, rowbobot = 0, rowdiscount = 0;
            rowbobot = rownominal / subtotal;
            rowdiscount = discount * rowbobot;
            rownominal -= rowdiscount;

            if($('select[name^="arr_tax"]').eq(index).val() !== '0'){
                let percent_tax = parseFloat($('select[name^="arr_tax"]').eq(index).val());
                if($('input[name^="arr_is_include_tax"]').eq(index).is(':checked')){
                    rownominal = rownominal / (1 + (percent_tax / 100));
                }

                rowtax = rownominal * (percent_tax / 100);
            }

            if($('select[name^="arr_wtax"]').eq(index).val() !== '0'){
                let percent_wtax = parseFloat($('select[name^="arr_wtax"]').eq(index).val());
                rowwtax = rownominal * (percent_wtax / 100);
            }
            
            tax += rowtax;
            wtax += rowwtax;
            total += rownominal;
            
        });

        tax = Math.floor(tax);
        wtax = Math.floor(wtax);

        grandtotal = total + tax - wtax;

        $('#subtotal').text(
            (subtotal >= 0 ? '' : '-') + formatRupiahIni(subtotal.toFixed(2).toString().replace('.',','))
        );
        $('#savesubtotal').val(
            (subtotal >= 0 ? '' : '-') + formatRupiahIni(subtotal.toFixed(2).toString().replace('.',','))
        );
        $('#total').text(
            (total >= 0 ? '' : '-') + formatRupiahIni(total.toFixed(2).toString().replace('.',','))
        );
        $('#savetotal').val(
            (total >= 0 ? '' : '-') + formatRupiahIni(total.toFixed(2).toString().replace('.',','))
        );
        $('#tax').text(
            (tax >= 0 ? '' : '-') + formatRupiahIni(tax.toFixed(2).toString().replace('.',','))
        );
        $('#savetax').val(
            (tax >= 0 ? '' : '-') + formatRupiahIni(tax.toFixed(2).toString().replace('.',','))
        );
        $('#wtax').text(
            (wtax >= 0 ? '' : '-') + formatRupiahIni(wtax.toFixed(2).toString().replace('.',','))
        );
        $('#savewtax').val(
            (wtax >= 0 ? '' : '-') + formatRupiahIni(wtax.toFixed(2).toString().replace('.',','))
        );
        $('#grandtotal').text(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(grandtotal.toFixed(2).toString().replace('.',','))
        );
        $('#savegrandtotal').val(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(grandtotal.toFixed(2).toString().replace('.',','))
        );
    }

    var printService = new WebSocketPrinter({
        onConnect: function () {
            
        },
        onDisconnect: function () {
           
        },
        onUpdate: function (message) {
            
        },
    });

</script>