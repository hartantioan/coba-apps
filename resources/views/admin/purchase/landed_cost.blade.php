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
    #chart-container,#chart-invoice {
        position: relative;
        height: 250px;
        margin: 0.5rem;
        overflow: auto;
        text-align:-webkit-right;
    }
    @media (min-width: 960px) {
        #modal4 {
            width:75%;
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
                                                <label for="filter_vendor" style="font-size:1rem;">Vendor/Ekspedisi :</label>
                                                <div class="input-field">
                                                    <select class="browser-default" id="filter_vendor" name="filter_vendor" multiple="multiple" style="width:100% !important;" onchange="loadDataTable()"></select>
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
                                                        <th rowspan="2">Supplier/Vendor</th>
                                                        <th rowspan="2">Broker</th>
                                                        <th rowspan="2">Perusahaan</th>
                                                        <th rowspan="2">Tanggal</th>
                                                        <th rowspan="2">No. Referensi</th>
                                                        <th colspan="2" class="center">Mata Uang</th>
                                                        <th rowspan="2">Keterangan</th>
                                                        <th rowspan="2">Dokumen</th>
                                                        <th rowspan="2">Total</th>
                                                        <th rowspan="2">PPN</th>
                                                        <th rowspan="2">PPH</th>
                                                        <th rowspan="2">Grandtotal</th>
                                                        <th rowspan="2">Status</th>
                                                        <th rowspan="2">Action</th>
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
                        <i>Silahkan pilih supplier / vendor untuk mengambil data dokumen GRPO atau LC.</i>
                        <div class="row">
                            <div class="input-field col m3 s12">
                                <input type="hidden" id="temp" name="temp">
                                <select class="browser-default" id="supplier_id" name="supplier_id" onchange="getAccountData(this.value);"></select>
                                <label class="active" for="supplier_id">Supplier/Vendor</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="browser-default" id="vendor_id" name="vendor_id"></select>
                                <label class="active" for="vendor_id">Broker</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="company_id" name="company_id">
                                    @foreach ($company as $row)
                                        <option value="{{ $row->id }}">{{ $row->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="company_id">Perusahaan</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="post_date" name="post_date" min="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                <label class="active" for="post_date">Tgl. Posting</label>
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
                            <div class="input-field col m3 s12">
                                <input id="reference" name="reference" type="text" placeholder="No. Referensi">
                                <label class="active" for="reference">No. Referensi</label>
                            </div>
                            <div class="col m12 s12">
                                <h6><b>GRPO Terpakai</b> (hapus untuk bisa diakses pengguna lain) : <i id="list-used-data"></i></h6>
                            </div>
                            <div class="col m12 s12">
                                <p class="mt-2 mb-2">
                                    <h5>Rincian Biaya</h5>
                                    <div style="overflow:auto;">
                                        <table class="bordered">
                                            <thead>
                                                <tr>
                                                    <th class="center">No</th>
                                                    <th class="center" width="15%">Deskripsi</th>
                                                    <th class="center" width="15%">Total</th>
                                                    <th class="center">Termasuk PPN</th>
                                                    <th class="center">PPN(%)</th>
                                                    <th class="center">PPN(Rp)</th>
                                                    <th class="center">PPH(%)</th>
                                                    <th class="center">PPH(Rp)</th>
                                                    <th class="center" width="15%">Grandtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="9"><h6>A. Local</h6></td>
                                                </tr>
                                                @foreach ($landedcostfee->where('type','1') as $key => $row)
                                                    <tr>
                                                        <input type="hidden" name="arr_fee_id[]" value="{{ $row->id }}">
                                                        <td class="center-align">
                                                            {{ $loop->iteration }}.
                                                        </td>
                                                        <td>
                                                            {{ $row->name }}
                                                        </td>
                                                        <td class="center-align">
                                                            <input id="arr_fee_nominal{{ $row->id }}" name="arr_fee_nominal[]" type="text" value="0,00" onkeyup="formatRupiah(this);countEach({{ $row->id }});" style="height:1.5rem !important;text-align:right;">
                                                        </td>
                                                        <td class="center-align">
                                                            <div class="switch mb-1">
                                                                <label>
                                                                    <input type="checkbox" id="arr_fee_include_tax{{ $row->id }}" name="arr_fee_include_tax[]" value="1" onclick="countEach({{ $row->id }});">
                                                                    <span class="lever"></span>
                                                                </label>
                                                            </div>
                                                        </td>
                                                        <td class="center-align">
                                                            <select class="browser-default" id="arr_fee_tax{{ $row->id }}" name="arr_fee_tax[]" onchange="countEach({{ $row->id }});">
                                                                <option value="0" data-id="0">-- Non-PPN --</option>
                                                                @foreach ($tax as $row1)
                                                                    <option value="{{ $row1->percentage }}" data-id="{{ $row1->id }}">{{ $row1->code }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="right-align">
                                                            <input id="arr_fee_tax_rp{{ $row->id }}" name="arr_fee_tax_rp[]" type="text" value="0,00" onkeyup="formatRupiah(this);" readonly style="height:1.5rem !important;text-align:right;">
                                                        </td>
                                                        <td class="center-align">
                                                            <select class="browser-default" id="arr_fee_wtax{{ $row->id }}" name="arr_fee_wtax[]" onchange="countEach({{ $row->id }});">
                                                                <option value="0" data-id="0">-- Non-PPH --</option>
                                                                @foreach ($wtax as $row2)
                                                                    <option value="{{ $row2->percentage }}" data-id="{{ $row2->id }}">{{ $row2->code }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="right-align">
                                                            <input id="arr_fee_wtax_rp{{ $row->id }}" name="arr_fee_wtax_rp[]" type="text" value="0,00" onkeyup="formatRupiah(this);" readonly style="height:1.5rem !important;text-align:right;">
                                                        </td>
                                                        <td class="center-align">
                                                            <input id="arr_fee_grandtotal{{ $row->id }}" name="arr_fee_grandtotal[]" type="text" value="0,00" onkeyup="formatRupiah(this);" readonly style="height:1.5rem !important;text-align:right;">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                <tr>
                                                    <td colspan="9"><h6>B. Impor</h6></td>
                                                </tr>
                                                @foreach ($landedcostfee->where('type','2') as $key => $row)
                                                    <tr>
                                                        <input type="hidden" name="arr_fee_id[]" value="{{ $row->id }}">
                                                        <td class="center-align">
                                                            {{ $loop->iteration }}.
                                                        </td>
                                                        <td>
                                                            {{ $row->name }}
                                                        </td>
                                                        <td class="center-align">
                                                            <input id="arr_fee_nominal{{ $row->id }}" name="arr_fee_nominal[]" type="text" value="0,00" onkeyup="formatRupiah(this);countEach({{ $row->id }});" style="height:1.5rem !important;text-align:right;">
                                                        </td>
                                                        <td class="center-align">
                                                            <div class="switch mb-1">
                                                                <label>
                                                                    <input type="checkbox" id="arr_fee_include_tax{{ $row->id }}" name="arr_fee_include_tax[]" value="1" onclick="countEach({{ $row->id }});">
                                                                    <span class="lever"></span>
                                                                </label>
                                                            </div>
                                                        </td>
                                                        <td class="center-align">
                                                            <select class="browser-default" id="arr_fee_tax{{ $row->id }}" name="arr_fee_tax[]" onchange="countEach({{ $row->id }});">
                                                                <option value="0" data-id="0">-- Non-PPN --</option>
                                                                @foreach ($tax as $row1)
                                                                    <option value="{{ $row1->percentage }}" data-id="{{ $row1->id }}">{{ $row1->code }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="right-align">
                                                            <input id="arr_fee_tax_rp{{ $row->id }}" name="arr_fee_tax_rp[]" type="text" value="0,00" onkeyup="formatRupiah(this);" readonly style="height:1.5rem !important;text-align:right;">
                                                        </td>
                                                        <td class="center-align">
                                                            <select class="browser-default" id="arr_fee_wtax{{ $row->id }}" name="arr_fee_wtax[]" onchange="countEach({{ $row->id }});">
                                                                <option value="0" data-id="0">-- Non-PPH --</option>
                                                                @foreach ($wtax as $row2)
                                                                    <option value="{{ $row2->percentage }}" data-id="{{ $row2->id }}">{{ $row2->code }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="right-align">
                                                            <input id="arr_fee_wtax_rp{{ $row->id }}" name="arr_fee_wtax_rp[]" type="text" value="0,00" onkeyup="formatRupiah(this);" readonly style="height:1.5rem !important;text-align:right;">
                                                        </td>
                                                        <td class="center-align">
                                                            <input id="arr_fee_grandtotal{{ $row->id }}" name="arr_fee_grandtotal[]" type="text" value="0,00" onkeyup="formatRupiah(this);" readonly style="height:1.5rem !important;text-align:right;">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td class="center" colspan="2">
                                                        Total
                                                    </td>
                                                    <td class="center">
                                                        <input id="total" name="total" type="text" value="0,00" onkeyup="formatRupiah(this);" readonly style="height:1.5rem !important;text-align:right;">
                                                    </td>
                                                    <td class="center">
                                                        -
                                                    </td>
                                                    <td class="center">
                                                        -
                                                    </td>
                                                    <td class="center">
                                                        <input id="tax" name="tax" type="text" value="0,00" onkeyup="formatRupiah(this);" readonly style="height:1.5rem !important;text-align:right;">
                                                    </td>
                                                    <td class="center">
                                                        -
                                                    </td>
                                                    <td class="center">
                                                        <input id="wtax" name="wtax" type="text" value="0,00" onkeyup="formatRupiah(this);" readonly style="height:1.5rem !important;text-align:right;">
                                                    </td>
                                                    <td class="center">
                                                        <input id="grandtotal" name="grandtotal" type="text" value="0,00" onkeyup="formatRupiah(this);" readonly style="height:1.5rem !important;text-align:right;">
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </p>
                                <p class="mt-2 mb-2">
                                    <h5>Detail Harga per Produk</h5>
                                    <div style="overflow:auto;">
                                        <table class="bordered">
                                            <thead>
                                                <tr>
                                                    <th class="center">Ref.No</th>
                                                    <th class="center">Item</th>
                                                    <th class="center">Qty</th>
                                                    <th class="center">Satuan (UOM)</th>
                                                    <th class="center">Plant</th>
                                                    <th class="center">Departemen</th>
                                                    <th class="center">Gudang</th>
                                                    <th class="center">Qty x Harga</th>
                                                    <th class="center">Proporsional</th>
                                                    <th class="center">Coa Biaya (Stock 0)</th>
                                                    <th class="center">Harga Total</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-item">
                                                <tr id="last-row-item">
                                                    <td colspan="11" class="center">
                                                        Silahkan pilih supplier untuk memulai...
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
        <h5>Daftar Goods Receipt PO & Landed Cost <b id="account_name"></b></h5>
    </div>
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <div class="row">
                    <div class="col s12 mt-2">
                        <ul class="tabs">
                            <li class="tab col m6"><a class="active" href="#goodsreceipt">Goods Receipt PO</a></li>
                            <li class="tab col m6"><a href="#landedcost">Landed Cost</a></li>
                        </ul>
                        <div id="goodsreceipt" class="col s12 active">
                            <p class="mt-2 mb-2">
                                <div id="datatable_buttons_goods_receipt"></div>
                                <i class="right">Gunakan *pilih semua* untuk memilih seluruh data yang anda inginkan. Atau pilih baris untuk memilih data yang ingin dipindahkan.</i>
                                <table id="table_goods_receipt" class="display" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="center-align">No. GRPO</th>
                                            <th class="center-align">No. SJ</th>
                                            <th class="center-align">Tgl.Post</th>
                                            <th class="center-align">Total</th>
                                            <th class="center-align">PPN</th>
                                            <th class="center-align">PPH</th>
                                            <th class="center-align">Grandtotal</th>
                                            <th class="center-align">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body-detail-goods-receipt"></tbody>
                                </table>
                            </p>
                        </div>
                        <div id="landedcost" class="col s12">
                            <p class="mt-2 mb-2">
                                <div id="datatable_buttons_landed_cost"></div>
                                <i class="right">Khusus untuk Landed Cost, hanya bisa diperbolehkan memilih 1 data saja.</i>
                                <table id="table_landed_cost" class="display" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="center-align">No. LC</th>
                                            <th class="center-align">Tgl.Post</th>
                                            <th class="center-align">Total</th>
                                            <th class="center-align">PPN</th>
                                            <th class="center-align">PPH</th>
                                            <th class="center-align">Grandtotal</th>
                                            <th class="center-align">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body-detail-landed-cost"></tbody>
                                </table>
                            </p>
                        </div>
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
        
        window.table.search('{{ $code }}').draw();

        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                $('#post_date').attr('min','{{ date("Y-m-d") }}');
                $('#document_date').attr('min','{{ date("Y-m-d") }}');
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
                $('.row_item').each(function(){
                    $(this).remove();
                });
                if($('#last-row-item').length == 0){
                    $('#body-item').append(`
                        <tr id="last-row-item">
                            <td colspan="11" class="center">
                                Silahkan pilih supplier untuk memulai...
                            </td>
                        </tr>
                    `);
                }
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }
                M.updateTextFields();
                $('#supplier_id,#vendor_id').empty();
                $('#total,#tax,#grandtotal').text('0,000');
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
                
            },
            onOpenEnd: function(modal, trigger) {
                table_goods_receipt = $('#table_goods_receipt').DataTable({
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
                table_landed_cost = $('#table_landed_cost').DataTable({
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
                        style: 'single'
                    }
                });
                $('#table_goods_receipt_wrapper > .dt-buttons').appendTo('#datatable_buttons_goods_receipt');
                $('#table_landed_cost_wrapper > .dt-buttons').appendTo('#datatable_buttons_landed_cost');
                $('select[name="table_goods_receipt_length"]').addClass('browser-default');
                $('select[name="table_landed_cost_length"]').addClass('browser-default');
                $('.tabs').tabs({
                    onShow: function () {
                        table_goods_receipt.columns.adjust().draw();
                        table_landed_cost.columns.adjust().draw();
                    }
                });
            },
            onCloseEnd: function(modal, trigger){
                $('#body-detail-goods-receipt,#body-detail-landed-cost').empty();
                $('#account_name').text('');
                $('#preview_data').html('');
                $('#table_goods_receipt,#table_landed_cost').DataTable().clear().destroy();
            }
        });

        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
            countAll();
        });

        $('#table_goods_receipt tbody').on('click', 'tr', function () {
            table_landed_cost.rows().deselect();
        });

        $('#table_landed_cost tbody').on('click', 'tr', function () {
            table_goods_receipt.rows().deselect();
        });

        select2ServerSide('#vendor_id,#filter_vendor', '{{ url("admin/select2/supplier_vendor") }}');
        select2ServerSide('#supplier_id', '{{ url("admin/select2/supplier") }}')
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
                let arr_lc_id = [], arr_gr_id = [], passed = true;
                $.map(table_goods_receipt.rows('.selected').nodes(), function (item) {
                    arr_gr_id.push($(item).data('id'));
                });

                $.map(table_landed_cost.rows('.selected').nodes(), function (item) {
                    arr_lc_id.push($(item).data('id'));
                });
                
                if(arr_gr_id.length == 0 && arr_lc_id.length == 0){
                    passed = false;
                }

                if(passed){
                    $.ajax({
                        url: '{{ Request::url() }}/get_good_receipt',
                        type: 'POST',
                        dataType: 'JSON',
                        data: {
                            arr_gr_id: arr_gr_id,
                            arr_lc_id: arr_lc_id,
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        beforeSend: function() {
                            loadingOpen('.modal-content');
                        },
                        success: function(response) {
                            loadingClose('.modal-content');
                            
                            if($('.data-used').length > 0){
                                $('.data-used').trigger('click');
                            }

                            $('.row_item').each(function(){
                                $(this).remove();
                            });

                            $('#last-row-item').remove();
                            var totalall = 0, totalproporsional = 0, passed = true, errormessage = [];
                            $.each(response, function(i, val) {
                                if(val.status == 500){
                                    passed = false;
                                    errormessage.push(val.message);
                                }
                                $.each(val.details, function(i, valdetail) {
                                    totalall += valdetail.totalrow;
                                });
                            });

                            if(passed){
                                $.each(response, function(i, val) {
                                    if(val.details.length > 0){
                                        $('#list-used-data').append(`
                                            <div class="chip purple darken-4 gradient-shadow white-text">
                                                ` + val.code + `
                                                <i class="material-icons close data-used" onclick="removeUsedData('` + val.lookable_type + `','` + val.id + `')">close</i>
                                            </div>
                                        `);

                                        $.each(val.details, function(i, valdetail) {
                                            var count = makeid(10), rowproporsional = (valdetail.totalrow / totalall) * 100;
                                            totalproporsional += rowproporsional;
                                            $('#body-item').append(`
                                                <tr class="row_item">
                                                    <input type="hidden" name="arr_item[]" value="` + valdetail.item_id + `">
                                                    <input type="hidden" name="arr_total[]" value="` + valdetail.totalrow + `">
                                                    <input type="hidden" name="arr_qty[]" value="` + valdetail.qtyRaw + `">
                                                    <input type="hidden" name="arr_stock[]" value="` + valdetail.stock + `">
                                                    <input type="hidden" name="arr_place[]" value="` + valdetail.place_id + `">
                                                    <input type="hidden" name="arr_department[]" value="` + valdetail.department_id + `">
                                                    <input type="hidden" name="arr_warehouse[]" value="` + valdetail.warehouse_id + `">
                                                    <input type="hidden" name="arr_lookable_id[]" value="` + valdetail.lookable_id + `">
                                                    <input type="hidden" name="arr_lookable_type[]" value="` + valdetail.lookable_type + `">
                                                    <td>
                                                    ` + val.code + ` 
                                                    </td>
                                                    <td>
                                                    ` + valdetail.item_name + ` 
                                                    </td>
                                                    <td class="center">
                                                        ` + valdetail.qty + `
                                                    </td>
                                                    <td class="center">
                                                        ` + valdetail.unit + `
                                                    </td>
                                                    <td class="center">
                                                        ` + valdetail.place_name + `
                                                    </td>
                                                    <td class="center">
                                                        ` + valdetail.department_name + `
                                                    </td>
                                                    <td class="center">
                                                        ` + valdetail.warehouse_name + `
                                                    </td>
                                                    <td class="right-align">
                                                        ` + formatRupiahIni(roundTwoDecimal(valdetail.totalrow).toString().replace('.',',')) + `
                                                    </td>
                                                    <td class="right-align">
                                                        ` + formatRupiahIni(roundTwoDecimal(rowproporsional).toString().replace('.',',')) + `
                                                    </td>
                                                    <td class="center">
                                                        <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]" style="width: 100%"></select>
                                                    </div>
                                                    <td class="center">
                                                        <input name="arr_price[]" class="browser-default nominalitem" type="text" value="0" onkeyup="formatRupiah(this);" style="text-align:right;width:100% !important;" id="rowPrice`+ count +`">
                                                    </td>
                                                </tr>
                                            `);
                                            if(valdetail.stock == 0){
                                                $('#arr_coa' + count).append(`
                                                    <option value="` + valdetail.coa_id + `" class="center-align">` + valdetail.coa_name + `</option>
                                                `).select();
                                            }else{
                                                $('#arr_coa' + count).append(`
                                                    <option value="" class="center-align">-- Stok tersedia --</option>
                                                `).select();
                                            }
                                        });
                                    }

                                    if(val.lookable_type == 'landed_costs'){
                                        $('#vendor_id').empty().append(`
                                            <option value="` + val.account_id + `">` + val.account_name + `</option>
                                        `);
                                    }
                                });

                                $('#body-item').append(`
                                    <tr class="row_item">
                                        <td class="right-align" colspan="7">
                                            TOTAL
                                        </td>
                                        <td class="right-align">
                                            ` + formatRupiahIni(roundTwoDecimal(totalall).toString().replace('.',',')) + `
                                        </td>
                                        <td class="right-align">
                                            ` + formatRupiahIni(roundTwoDecimal(totalproporsional).toString().replace('.',',')) + `
                                        </td>
                                        <td class="center">
                                            -
                                        </td>
                                        <td class="center">
                                            -
                                        </td>
                                    </tr>
                                `);
                                
                                countAll();
                                $('.modal-content').scrollTop(0);
                                M.updateTextFields();
                                $('#modal4').modal('close');
                            }else{
                                $.each(errormessage, function(i, val) {
                                    M.toast({
                                        html: val
                                    });
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
                        text: 'Silahkan pilih data terlebih dahulu.',
                        icon: 'error'
                    });
                }
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
                    $('#account_name').text($('#supplier_id').select2('data')[0].text);

                    if(response.goods_receipt.length > 0){
                        $.each(response.goods_receipt, function(i, val) {
                            $('#body-detail-goods-receipt').append(`
                                <tr data-id="` + val.id + `">
                                    <td class="center">
                                        ` + val.code + `
                                    </td>
                                    <td class="center">
                                        ` + val.delivery_no + `
                                    </td>
                                    <td class="right-align">
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
                                    <td class="">
                                        ` + val.note + `
                                    </td>
                                </tr>
                            `);
                        });
                    }

                    if(response.landed_cost.length > 0){
                        $.each(response.landed_cost, function(i, val) {
                            var count = makeid(10);
                            $('#body-detail-landed-cost').append(`
                                <tr data-id="` + val.id + `">
                                    <td class="center">
                                        ` + val.code + `
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
                                    <td class="">
                                        ` + val.note + `
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
            $('.row_item').remove();
        }
    }
    
    function countEach(val){
        let rowtotal = parseFloat($('#arr_fee_nominal' + val).val().replaceAll(".", "").replaceAll(",",".")), rowtax = 0, rowwtax = 0, rowgrandtotal = 0, rowpercenttax = parseFloat($('#arr_fee_tax' + val).val()), rowpercentwtax = parseFloat($('#arr_fee_wtax' + val).val());

        if(rowpercenttax !== 0){
            if($('#arr_fee_include_tax' + val).is(':checked')){
                rowtotal = rowtotal / (1 + (rowpercenttax / 100));
                if($(":focus").attr('name') !== 'arr_fee_nominal[]'){
                    $('#arr_fee_nominal' + val).val(
                        (rowtotal >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(rowtotal).toString().replace('.',','))
                    );
                }
            }else{
                if(parseFloat($('#arr_fee_tax_rp' + val).val().replaceAll(".", "").replaceAll(",",".")) > 0){
                    rowtotal = parseFloat($('#arr_fee_nominal' + val).val().replaceAll(".", "").replaceAll(",",".")) + parseFloat($('#arr_fee_tax_rp' + val).val().replaceAll(".", "").replaceAll(",","."));
                    $('#arr_fee_nominal' + val).val(
                        (rowtotal >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(rowtotal).toString().replace('.',','))
                    );
                }
            }
            rowtax = rowtotal * (rowpercenttax / 100);
        }

        if(rowpercentwtax !== 0){
            rowwtax = rowtotal * (rowpercentwtax / 100);
        }

        rowgrandtotal = rowtotal + rowtax - rowwtax;

        $('#arr_fee_tax_rp' + val).val(
            (rowtax >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(rowtax).toString().replace('.',','))
        );

        $('#arr_fee_wtax_rp' + val).val(
            (rowwtax >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(rowwtax).toString().replace('.',','))
        );
        
        $('#arr_fee_grandtotal' + val).val(
            (rowgrandtotal >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(rowgrandtotal).toString().replace('.',','))
        );

        countAll();
    }

    function countAll(){
        var total = 0, tax = 0, wtax = 0, grandtotal = 0;

        $('input[name^="arr_fee_nominal"]').each(function(index){
            total += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
            tax += parseFloat($('input[name^="arr_fee_tax_rp"]').eq(index).val().replaceAll(".", "").replaceAll(",","."));
            wtax += parseFloat($('input[name^="arr_fee_wtax_rp"]').eq(index).val().replaceAll(".", "").replaceAll(",","."));
            grandtotal += parseFloat($('input[name^="arr_fee_grandtotal"]').eq(index).val().replaceAll(".", "").replaceAll(",","."));
        });

        $('#total').val(
            (total >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(total).toString().replace('.',','))
        );
        $('#tax').val(
            (tax >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(tax).toString().replace('.',','))
        );
        $('#wtax').val(
            (wtax >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(wtax).toString().replace('.',','))
        );
        $('#grandtotal').val(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(grandtotal).toString().replace('.',','))
        );
        
        if($('input[name^="arr_total"]').length > 0){
            let totalall = 0;
            $('input[name^="arr_total"]').each(function(){
                totalall += parseFloat($(this).val());
            });

            $('input[name^="arr_total"]').each(function(index){
                let totalrow = (parseFloat($(this).val()) / totalall) * total;
                $('input[name^="arr_price"]:eq(' + index + ')').val(
                    (totalrow >= 0 ? '' : '-') + formatRupiahIni(totalrow.toFixed(2).toString().replace('.',','))
                );
            })
        }
    }

    function changeDateMinimum(val){
        if(val){
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
                    status : $('#filter_status').val(),
                    'vendor_id[]' : $('#filter_vendor').val(),
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
                { name: 'vendor_id', className: 'center-align' },
                { name: 'company_id', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'no_reference', className: 'center-align' },
                { name: 'currency_id', className: 'center-align' },
                { name: 'currency_rate', className: 'center-align' },
                { name: 'note', className: 'center-align' },
                { name: 'document', className: 'center-align' },
                { name: 'total', className: 'right-align' },
                { name: 'tax', className: 'right-align' },
                { name: 'wtax', className: 'right-align' },
                { name: 'grandtotal', className: 'right-align' },
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
                var formData = new FormData($('#form_data')[0]), passed = true;

                formData.delete('tax_id');
                formData.delete('wtax_id');
                formData.append('tax_id',$('#tax_id').find(':selected').data('id'));
                formData.append('wtax_id',$('#wtax_id').find(':selected').data('id'));
                formData.append('percent_tax',$('#tax_id').val());
                formData.append('percent_wtax',$('#wtax_id').val());
                formData.delete('arr_fee_include_tax[]');
                formData.delete('arr_coa[]');

                $('input[name^="arr_fee_include_tax"]').each(function(){
                    formData.append('arr_fee_include_tax[]',
                        $(this).is(':checked') ? '1' : '0'
                    );
                });

                $('select[name^="arr_coa"]').each(function(index){
                    formData.append('arr_coa[]',
                        $(this).val() ? $(this).val() : ''
                    );
                    if($('input[name^="arr_stock"]').eq(index).val() == 0){
                        passed = !$(this).val() ? false : true;
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
                        title: 'Ups, cek form detail anda!',
                        text: 'Salah satu produk memiliki stok 0, silahkan pilih coa biaya.',
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
                $('#vendor_id').empty();
                $('#vendor_id').append(`
                    <option value="` + response.account_id + `">` + response.vendor_name + `</option>
                `);
                $('#supplier_id').empty();
                $('#supplier_id').append(`
                    <option value="` + response.supplier_id + `">` + response.supplier_name + `</option>
                `);
                $('#reference').val(response.reference);
                $('#currency_id').val(response.currency_id).formSelect();
                $('#currency_rate').val(response.currency_rate);
                $('#company_id').val(response.company_id).formSelect();
                $('#post_date').val(response.post_date);

                $('#note').val(response.note);
                $('#total').val(response.total);
                $('#tax').val(response.tax);
                $('#grandtotal').val(response.grandtotal);

                var totalproporsional = 0, totalall = 0;
                
                if(response.details.length > 0){
                    $('#last-row-item').remove();

                    $('.row_item').each(function(){
                        $(this).remove();
                    });

                    $.each(response.details, function(i, val) {
                        totalall += val.totalrow;
                    });

                    $.each(response.details, function(i, val) {
                        var count = makeid(10), rowproporsional = (val.totalrow / totalall) * 100;
                        totalproporsional += rowproporsional;
                        $('#body-item').append(`
                            <tr class="row_item">
                                <input type="hidden" name="arr_item[]" value="` + val.item_id + `">
                                <input type="hidden" name="arr_total[]" value="` + val.totalrow + `">
                                <input type="hidden" name="arr_qty[]" value="` + val.qtyRaw + `">
                                <input type="hidden" name="arr_stock[]" value="` + val.stock + `">
                                <input type="hidden" name="arr_place[]" value="` + val.place_id + `">
                                <input type="hidden" name="arr_department[]" value="` + val.department_id + `">
                                <input type="hidden" name="arr_warehouse[]" value="` + val.warehouse_id + `">
                                <input type="hidden" name="arr_lookable_id[]" value="` + val.lookable_id + `">
                                <input type="hidden" name="arr_lookable_type[]" value="` + val.lookable_type + `">
                                <td>
                                ` + response.code + ` 
                                </td>
                                <td>
                                ` + val.item_name + ` 
                                </td>
                                <td class="center">
                                    ` + val.qty + `
                                </td>
                                <td class="center">
                                    ` + val.unit + `
                                </td>
                                <td class="center">
                                    ` + val.place_name + `
                                </td>
                                <td class="center">
                                    ` + val.department_name + `
                                </td>
                                <td class="center">
                                    ` + val.warehouse_name + `
                                </td>
                                <td class="right-align">
                                    ` + formatRupiahIni(roundTwoDecimal(val.totalrow).toString().replace('.',',')) + `
                                </td>
                                <td class="right-align">
                                    ` + formatRupiahIni(roundTwoDecimal(rowproporsional).toString().replace('.',',')) + `
                                </td>
                                <td class="center">
                                    <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]" style="width: 100%"></select>
                                </div>
                                <td class="center">
                                    <input name="arr_price[]" class="browser-default nominalitem" type="text" value="` + val.nominal + `" onkeyup="formatRupiah(this);" style="text-align:right;width:100% !important;" id="rowPrice`+ count +`">
                                </td>
                            </tr>
                        `);
                            
                        if(val.stock == 0){
                            if(val.coa_id){
                                $('#arr_coa' + count).append(`
                                    <option value="` + val.coa_id + `">` + val.coa_name + `</option>
                                `);
                            }
                        }else{
                            $('#arr_coa' + count).append(`
                                <option value="" class="center-align">-- Stok tersedia --</option>
                            `).select();
                        }
                    });

                    $.each(response.fees, function(i, val) {
                        $('#arr_fee_nominal' + val.id).val(val.total);
                        if(val.is_include_tax == '1'){
                            $('#arr_fee_include_tax' + val.id).prop('checked',true);
                        }else{
                            $('#arr_fee_include_tax' + val.id).prop('checked',false);
                        }
                        $('#arr_fee_tax' + val.id).val(val.percent_tax);
                        $('#arr_fee_tax_rp' + val.id).val(val.tax);
                        $('#arr_fee_wtax' + val.id).val(val.percent_wtax);
                        $('#arr_fee_wtax_rp' + val.id).val(val.wtax);
                        $('#arr_fee_grandtotal' + val.id).val(val.grandtotal);
                    });

                    $('#body-item').append(`
                        <tr class="row_item">
                            <td class="right-align" colspan="7">
                                TOTAL
                            </td>
                            <td class="right-align">
                                ` + formatRupiahIni(roundTwoDecimal(totalall).toString().replace('.',',')) + `
                            </td>
                            <td class="right-align">
                                ` + formatRupiahIni(roundTwoDecimal(totalproporsional).toString().replace('.',',')) + `
                            </td>
                            <td class="center">
                                -
                            </td>
                            <td class="center">
                                -
                            </td>
                        </tr>
                    `);
                    
                    /* countAll(); */
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
        var search = window.table.search(), status = $('#filter_status').val(), vendor = $('#filter_vendor').val(), currency = $('#filter_currency').val();
        
        $.ajax({
            type : "POST",
            url  : '{{ Request::url() }}/print',
            data : {
                search : search,
                status : status,
                'vendor_id[]' : vendor,
                'currency_id[]' : currency
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
        var search = window.table.search(), status = $('#filter_status').val(), vendor = $('#filter_vendor').val(), currency = $('#filter_currency').val();
        
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status + "&vendor=" + vendor + "&currency=" + currency;
    }

    function removeUsedData(type,id){
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
                $('.row_item').each(function(){
                    $(this).remove();
                });
                if($('#last-row-item').length == 0){
                    $('#body-item').append(`
                        <tr id="last-row-item">
                            <td colspan="11" class="center">
                                Silahkan pilih supplier untuk memulai...
                            </td>
                        </tr>
                    `);
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
</script>