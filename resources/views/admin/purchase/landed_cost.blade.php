<style>
    .modal {
        top:0px !important;
    }

    table > thead > tr > th {
        font-size: 13px !important;
    }

    body.tab-active input:focus {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .modal-content .select2.tab-active {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
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
            width:85%;
        }
    }

    @media (max-width: 960px) {
        #modal4 {
            width:100%;
        }
    }
    
    .select-wrapper, .select2-container {
        height:3.7rem !important;
    }

    .dataTables_scrollHeadInner, .dataTable {
        width: 100% !important; 
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
                                                        <th rowspan="2">PPh</th>
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
                        <i>Silahkan pilih supplier / vendor untuk mengambil data dokumen GRPO & LC. Untuk Inventori Transfer, supplier / vendor kosong dan tekan tombol Tampilkan Data.</i>
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
                                <select class="browser-default" id="supplier_id" name="supplier_id"></select>
                                <label class="active" for="supplier_id">Supplier/Vendor</label>
                            </div>
                            <div class="input-field col m3 s12 step4">
                                <a href="javascript:void(0);" class="btn waves-effect waves-light cyan" onclick="getAccountData();" id="btn-show">Tampilkan Data<i class="material-icons right">assignment</i></a>
                                <label class="active">&nbsp;</label>
                            </div>
                            <div class="input-field col m3 s12 step5">
                                <select class="browser-default" id="account_id" name="account_id"></select>
                                <label class="active" for="account_id">Broker</label>
                            </div>
                            <div class="input-field col m3 s12 step6">
                                <select class="form-control" id="company_id" name="company_id">
                                    @foreach ($company as $row)
                                        <option value="{{ $row->id }}">{{ $row->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="company_id">Perusahaan</label>
                            </div>
                            <div class="input-field col m3 s12 step7">
                                <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);loadCurrency();">
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
                            <div class="input-field col m3 s12 step11">
                                <input id="reference" name="reference" type="text" placeholder="No. Referensi">
                                <label class="active" for="reference">No. Referensi</label>
                            </div>
                            <div class="col m12 s12 step12">
                                <h6><b>GRPO / Inv.Transfer / Landed Cost (Masuk) Terpakai</b> (hapus untuk bisa diakses pengguna lain) : <i id="list-used-data"></i></h6>
                            </div>
                            <div class="col m12 s12 ">
                                <p class="mt-2 mb-2">
                                    <h5 class="step13">Rincian Biaya</h5>
                                    <div style="overflow:auto;">
                                        <table class="bordered" id="table-detail">
                                            <thead>
                                                <tr>
                                                    <th class="center">No</th>
                                                    <th class="center" width="15%">Deskripsi</th>
                                                    <th class="center" width="15%">Total</th>
                                                    <th class="center">Termasuk PPN</th>
                                                    <th class="center">PPN(%)</th>
                                                    <th class="center">PPN(Rp)</th>
                                                    <th class="center">PPh(%)</th>
                                                    <th class="center">PPh(Rp)</th>
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
                                                            <input id="arr_temp_nominal{{ $row->id }}" type="hidden" value="0,00">
                                                            <input id="arr_fee_nominal{{ $row->id }}" name="arr_fee_nominal[]" type="text" value="0,00" onkeyup="formatRupiah(this);setBaseNominal({{ $row->id }});countEach({{ $row->id }});" style="height:1.5rem !important;text-align:right;">
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
                                                                    <option value="{{ $row1->percentage }}" data-id="{{ $row1->id }}">{{ $row1->name.' - '.number_format($row1->percentage,2,',','.').'%' }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="right-align">
                                                            <input id="arr_fee_tax_rp{{ $row->id }}" name="arr_fee_tax_rp[]" type="text" value="0,00" onkeyup="formatRupiah(this);" readonly style="height:1.5rem !important;text-align:right;">
                                                        </td>
                                                        <td class="center-align">
                                                            <select class="browser-default" id="arr_fee_wtax{{ $row->id }}" name="arr_fee_wtax[]" onchange="countEach({{ $row->id }});">
                                                                <option value="0" data-id="0">-- Non-PPh --</option>
                                                                @foreach ($wtax as $row2)
                                                                    <option value="{{ $row2->percentage }}" data-id="{{ $row2->id }}">{{ $row2->name.' - '.number_format($row2->percentage,2,',','.').'%' }}</option>
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
                                                            <input id="arr_temp_nominal{{ $row->id }}" type="hidden" value="0,00">
                                                            <input id="arr_fee_nominal{{ $row->id }}" name="arr_fee_nominal[]" type="text" value="0,00" onkeyup="formatRupiah(this);setBaseNominal({{ $row->id }});countEach({{ $row->id }});" style="height:1.5rem !important;text-align:right;">
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
                                                                    <option value="{{ $row1->percentage }}" data-id="{{ $row1->id }}">{{ $row1->name.' - '.number_format($row1->percentage,2,',','.').'%' }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="right-align">
                                                            <input id="arr_fee_tax_rp{{ $row->id }}" name="arr_fee_tax_rp[]" type="text" value="0,00" onkeyup="formatRupiah(this);" readonly style="height:1.5rem !important;text-align:right;">
                                                        </td>
                                                        <td class="center-align">
                                                            <select class="browser-default" id="arr_fee_wtax{{ $row->id }}" name="arr_fee_wtax[]" onchange="countEach({{ $row->id }});">
                                                                <option value="0" data-id="0">-- Non-PPh --</option>
                                                                @foreach ($wtax as $row2)
                                                                    <option value="{{ $row2->percentage }}" data-id="{{ $row2->id }}">{{ $row2->name.' - '.number_format($row2->percentage,2,',','.').'%' }}</option>
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
                            </div>
                            <div class="col m12 s12 step14">
                                <p class="mt-2 mb-2">
                                    <h5>Detail Harga per Produk</h5>
                                    <div style="overflow:auto;">
                                        <table class="bordered" id="table-detail1">
                                            <thead>
                                                <tr>
                                                    <th class="center">Ref.No</th>
                                                    <th class="center">Item</th>
                                                    <th class="center">Qty</th>
                                                    <th class="center">Satuan (UOM)</th>
                                                    <th class="center">Plant</th>
                                                    <th class="center">Line</th>
                                                    <th class="center">Mesin</th>
                                                    <th class="center">Divisi</th>
                                                    <th class="center">Gudang</th>
                                                    <th class="center">Proyek</th>
                                                    <th class="center">Qty x Harga</th>
                                                    <th class="center">Proporsional</th>
                                                    <th class="center">Coa Biaya (Stock 0)</th>
                                                    <th class="center">Harga Total</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-item">
                                                <tr id="last-row-item">
                                                    <td colspan="14" class="center">
                                                        Silahkan pilih supplier untuk memulai...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </p>
                            </div>
                            <div class="input-field col m4 s12 step15">
                                <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note">Keterangan</label>
                            </div>
                            <div class="input-field col m4 s12">

                            </div>
                            <div class="input-field col m4 s12">
                                
                            </div>
                            <div class="col s12 mt-3">
                                <button class="btn waves-effect waves-light right submit step16" onclick="save();">Simpan <i class="material-icons right">send</i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light purple" onclick="startIntro();">Panduan <i class="material-icons right">help_outline</i></button>
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

<div id="modal4" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h5>Daftar Goods Receipt PO / Landed Cost / Inventori Transfer - Masuk <b id="account_name"></b></h5>
                <div class="row">
                    <div class="col s12 mt-2">
                        <ul class="collapsible">
                            <li class="active">
                                <div class="collapsible-header purple darken-1 text-white" style="color:white;"><i class="material-icons">library_books</i>Good Receipt PO</div>
                                <div class="collapsible-body" style="display:block;">
                                    <div class="mt-2 mb-2" style="overflow:scroll;width:100% !important;">
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
                                                    <th class="center-align">PPh</th>
                                                    <th class="center-align">Grandtotal</th>
                                                    <th class="center-align">Keterangan</th>
                                                    <th class="center-align">Landed Cost</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-detail-goods-receipt"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="collapsible-header purple darken-1 text-white" style="color:white;"><i class="material-icons">library_books</i>Landed Cost</div>
                                <div class="collapsible-body">
                                    <div class="mt-2 mb-2" style="overflow:scroll;width:100% !important;">
                                        <div id="datatable_buttons_landed_cost"></div>
                                        <i class="right">Khusus untuk Landed Cost, hanya bisa diperbolehkan memilih 1 data saja.</i>
                                        <table id="table_landed_cost" class="display" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="center-align">No. LC</th>
                                                    <th class="center-align">Tgl.Post</th>
                                                    <th class="center-align">Total</th>
                                                    <th class="center-align">PPN</th>
                                                    <th class="center-align">PPh</th>
                                                    <th class="center-align">Grandtotal</th>
                                                    <th class="center-align">Keterangan</th>
                                                    <th class="center-align">Landed Cost</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-detail-landed-cost"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="collapsible-header purple darken-1 text-white" style="color:white;"><i class="material-icons">library_books</i>Inventori Transfer Masuk</div>
                                <div class="collapsible-body">
                                    <div class="mt-2 mb-2" style="overflow:scroll;width:100% !important;">
                                        <div id="datatable_buttons_inventory_transfer_in"></div>
                                        <i class="right">Gunakan *pilih semua* untuk memilih seluruh data yang anda inginkan. Atau pilih baris untuk memilih data yang ingin dipindahkan. ITI = Inventori Transfer Masuk, ITO = Inventori Transfer Keluar</i>
                                        <table id="table_inventory_transfer_in" class="display" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="center-align">No. ITI</th>
                                                    <th class="center-align">No. ITO</th>
                                                    <th class="center-align">Tgl.Post</th>
                                                    <th class="center-align">Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-detail-inventory-transfer-in"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light amber" onclick="startIntro2();">Panduan <i class="material-icons right">help_outline</i></button>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat mr-1">Close</a>
        <button class="btn waves-effect waves-light purple right submit step23" onclick="applyDocuments();">Gunakan <i class="material-icons right">forward</i></button>
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

<div id="modal6" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
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
    document.addEventListener('focusin', function (event) {
        const select2Container = event.target.closest('.modal-content .select2');
        const activeSelect2 = document.querySelector('.modal-content .select2.tab-active');
        if (event.target.closest('.modal-content')) {
            document.body.classList.add('tab-active');
        }
        
        
        if (activeSelect2 && !select2Container) {
            activeSelect2.classList.remove('tab-active');
        }

        
        if (select2Container) {
            select2Container.classList.add('tab-active');
        }
    });

    document.addEventListener('mousedown', function () {
        const activeSelect2 = document.querySelector('.modal-content .select2.tab-active');
        document.body.classList.remove('tab-active');
        if (activeSelect2) {
            activeSelect2.classList.remove('tab-active');
        }
    });
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

        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                $('#post_date').attr('min','{{ $minDate }}');
                $('#post_date').attr('max','{{ $maxDate }}');
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
                if(!$('#temp').val()){
                    loadCurrency();
                }
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                $('.row_item').each(function(){
                    $(this).remove();
                });
                $('input').css('border', 'none');
                $('input').css('border-bottom', '0.5px solid black');
                if($('#last-row-item').length == 0){
                    $('#body-item').append(`
                        <tr id="last-row-item">
                            <td colspan="14" class="center">
                                Silahkan pilih supplier untuk memulai...
                            </td>
                        </tr>
                    `);
                }
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }
                M.updateTextFields();
                $('#supplier_id,#account_id').empty();
                $('#total,#tax,#grandtotal').text('0,000');
                window.onbeforeunload = function() {
                    return null;
                };
                $('#to_address,#from_address').text('-');
                $('#temp_from_address,#temp_to_address').val('');
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

        $('#modal4_1').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#show_detail').empty();
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
                    "order": [[0, 'desc']],
                    dom: 'Blfrtip',
                    buttons: [
                        'selectAll',
                        'selectNone'
                    ],
                    select: {
                        style: 'multi'
                    },
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
                    }
                });
                table_landed_cost = $('#table_landed_cost').DataTable({
                    "responsive": true,
                    scrollY: '50vh',
                    scrollCollapse: true,
                    "iDisplayInLength": 10,
                    "order": [[0, 'desc']],
                    dom: 'Blfrtip',
                    buttons: [
                        'selectAll',
                        'selectNone'
                    ],
                    select: {
                        style: 'single'
                    },
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
                    }
                });
                table_inventory_transfer_in = $('#table_inventory_transfer_in').DataTable({
                    "responsive": true,
                    scrollY: '50vh',
                    scrollCollapse: true,
                    "iDisplayInLength": 10,
                    "order": [[0, 'desc']],
                    dom: 'Blfrtip',
                    buttons: [
                        'selectAll',
                        'selectNone'
                    ],
                    select: {
                        style: 'multi'
                    },
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
                    }
                });
                $('#table_goods_receipt_wrapper > .dt-buttons').appendTo('#datatable_buttons_goods_receipt');
                $('#table_landed_cost_wrapper > .dt-buttons').appendTo('#datatable_buttons_landed_cost');
                $('#table_inventory_transfer_in_wrapper > .dt-buttons').appendTo('#datatable_buttons_inventory_transfer_in');
                $('select[name="table_goods_receipt_length"]').addClass('browser-default');
                $('select[name="table_landed_cost_length"]').addClass('browser-default');
                $('select[name="table_inventory_transfer_in_length"]').addClass('browser-default');
                $('.collapsible').on('shown.bs.collapse', function () {
                    table_goods_receipt.columns.adjust().draw();
                    table_landed_cost.columns.adjust().draw();
                    table_inventory_transfer_in.columns.adjust().draw();
                });
            },
            onCloseEnd: function(modal, trigger){
                $('#body-detail-goods-receipt,#body-detail-landed-cost,#body-detail-inventory-transfer-in').empty();
                $('#account_name').text('');
                $('#preview_data').html('');
                $('#table_goods_receipt,#table_landed_cost,#table_inventory_transfer_in').DataTable().clear().destroy();
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
                $('#company_jurnal').empty();
                $('#post_date_jurnal').empty();
            }
        });

        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
            countAll();
        });

        $('#table_goods_receipt tbody').on('click', 'tr', function () {
            table_landed_cost.rows().deselect();
            table_inventory_transfer_in.rows().deselect();
        });

        $('#table_landed_cost tbody').on('click', 'tr', function () {
            table_goods_receipt.rows().deselect();
            table_inventory_transfer_in.rows().deselect();
        });

        $('#table_inventory_transfer_in tbody').on('click', 'tr', function () {
            table_goods_receipt.rows().deselect();
            table_landed_cost.rows().deselect();
        });

        select2ServerSide('#filter_vendor', '{{ url("admin/select2/supplier_vendor") }}');

        $('#delivery_cost_id').select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/delivery_cost") }}',
                type: 'GET',
                dataType: 'JSON',
                data: function(params) {
                    return {
                        search: params.term,
                        /* subdistrict_from : $('#temp_from_address').val(),
                        subdistrict_to : $('#temp_to_address').val(), */
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.items
                    }
                }
            }
        });

        select2ServerSide('#supplier_id', '{{ url("admin/select2/supplier") }}');
        select2ServerSide('#account_id', '{{ url("admin/select2/supplier_vendor") }}');

        $("#table-detail th,#table-detail1 th").resizable({
            minWidth: 100,
        });
    });

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
                let arr_lc_id = [], arr_gr_id = [], arr_iti = [], passed = true;
                $.map(table_goods_receipt.rows('.selected').nodes(), function (item) {
                    arr_gr_id.push($(item).data('id'));
                });

                $.map(table_landed_cost.rows('.selected').nodes(), function (item) {
                    arr_lc_id.push($(item).data('id'));
                });

                $.map(table_inventory_transfer_in.rows('.selected').nodes(), function (item) {
                    arr_iti.push($(item).data('id'));
                });
                
                if(arr_gr_id.length == 0 && arr_lc_id.length == 0 && arr_iti.length == 0){
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
                            arr_iti: arr_iti,
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

                                        $('#from_address').text(val.from_address);
                                        $('#to_address').text(val.to_address);
                                        $('#temp_from_address').val(val.subdistrict_from_id);
                                        $('#temp_to_address').val(val.subdistrict_to_id);

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
                                                    <input type="hidden" name="arr_line[]" value="` + valdetail.line_id + `">
                                                    <input type="hidden" name="arr_machine[]" value="` + valdetail.machine_id + `">
                                                    <input type="hidden" name="arr_department[]" value="` + valdetail.department_id + `">
                                                    <input type="hidden" name="arr_warehouse[]" value="` + valdetail.warehouse_id + `">
                                                    <input type="hidden" name="arr_project[]" value="` + valdetail.project_id + `">
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
                                                        ` + valdetail.line_name + `
                                                    </td>
                                                    <td class="center">
                                                        ` + valdetail.machine_name + `
                                                    </td>
                                                    <td class="center">
                                                        ` + valdetail.department_name + `
                                                    </td>
                                                    <td class="center">
                                                        ` + valdetail.warehouse_name + `
                                                    </td>
                                                    <td class="center">
                                                        ` + valdetail.project_name + `
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
                                                        <input name="arr_price[]" class="browser-default nominalitem" type="text" value="0" onkeyup="formatRupiah(this);" style="text-align:right;width:100% !important;" id="rowPrice`+ count +`" readonly>
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
                                        <td class="right-align" colspan="9">
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
    
    function getAccountData(){
        if($('.data-used').length > 0){
            $('.data-used').trigger('click');
        }
        let val = $('#supplier_id').val();
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

                if(val){
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
                                    <td class="">
                                        ` + val.landed_cost + `
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
                                    <td class="">
                                        ` + val.landed_cost + `
                                    </td>
                                </tr>
                            `);
                        });                        
                    }
                }else{

                    if(response.inventory_transfer_in.length > 0){
                        $.each(response.inventory_transfer_in, function(i, val) {
                            var count = makeid(10);
                            $('#body-detail-inventory-transfer-in').append(`
                                <tr data-id="` + val.id + `">
                                    <td class="center-align">
                                        ` + val.code_iti + `
                                    </td>
                                    <td class="center-align">
                                        ` + val.code_ito + `
                                    </td>
                                    <td class="center-align">
                                        ` + val.post_date + `
                                    </td>
                                    <td class="">
                                        ` + val.note + `
                                    </td>
                                </tr>
                            `);
                        });  
                    }

                    setTimeout(function() {
                        $('ul.tabs').tabs("select", "inventorytransferin");
                    }, 1000);
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
        
    }

    function setBaseNominal(val){
        $('#arr_temp_nominal' + val).val(
            $('#arr_fee_nominal' + val).val()
        );
    }
    
    function countEach(val){
        let rowtotal = parseFloat($('#arr_temp_nominal' + val).val().replaceAll(".", "").replaceAll(",",".")), rowtax = 0, rowwtax = 0, rowgrandtotal = 0, rowpercenttax = parseFloat($('#arr_fee_tax' + val).val()), rowpercentwtax = parseFloat($('#arr_fee_wtax' + val).val());

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
                    $('#arr_fee_nominal' + val).val(
                        $('#arr_temp_nominal' + val).val()
                    );
                    rowtotal = parseFloat($('#arr_temp_nominal' + val).val().replaceAll(".", "").replaceAll(",","."));
                }
            }
            /* rowtax = Math.floor(rowtotal * (rowpercenttax / 100)); */
            rowtax = rowtotal * (rowpercenttax / 100);
        }else{
            $('#arr_fee_nominal' + val).val(
                (rowtotal >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(rowtotal).toString().replace('.',','))
            );
        }

        if(rowpercentwtax !== 0){
            /* rowwtax = Math.floor(rowtotal * (rowpercentwtax / 100)); */
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
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle',
                'selectAll',
                'selectNone',
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
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    'status' : $('#filter_status').val(),
                    'vendor_id[]' : $('#filter_vendor').val(),
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
        });
        $('.dt-buttons').appendTo('#datatable_buttons');
        
        $('select[name="datatable_serverside_length"]').addClass('browser-default');
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
                var formData = new FormData($('#form_data')[0]), passed = true;

                formData.delete('tax_id');
                formData.delete('wtax_id');
                formData.append('tax_id',$('#tax_id').find(':selected').data('id'));
                formData.append('wtax_id',$('#wtax_id').find(':selected').data('id'));
                formData.append('percent_tax',$('#tax_id').val());
                formData.append('percent_wtax',$('#wtax_id').val());
                formData.delete('arr_fee_include_tax[]');
                formData.delete('arr_coa[]');
                formData.delete('arr_department[]');
                formData.delete('arr_line[]');
                formData.delete('arr_machine[]');

                $('input[name^="arr_fee_include_tax"]').each(function(){
                    formData.append('arr_fee_include_tax[]',
                        $(this).is(':checked') ? '1' : '0'
                    );
                });

                $('input[name^="arr_department"]').each(function(){
                    formData.append('arr_department[]',
                        $(this).val() ? $(this).val() : ''
                    );
                });

                $('input[name^="arr_line"]').each(function(){
                    formData.append('arr_line[]',
                        $(this).val() ? $(this).val() : ''
                    );
                });

                $('input[name^="arr_machine"]').each(function(){
                    formData.append('arr_machine[]',
                        $(this).val() ? $(this).val() : ''
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
                            loadingOpen('.modal-content');
                        },
                        success: function(response) {
                            $('input').css('border', 'none');
                        $('input').css('border-bottom', '0.5px solid black');
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
                $('#code_place_id').val(response.code_place_id).formSelect();
                $('#code').val(response.code);
                $('#supplier_id').empty();
                if(response.supplier_name){
                    $('#supplier_id').append(`
                        <option value="` + response.supplier_id + `">` + response.supplier_name + `</option>
                    `);
                }
                $('#account_id').empty();
                if(response.account_name){
                    $('#account_id').append(`
                        <option value="` + response.account_id + `">` + response.account_name + `</option>
                    `);
                }
                $('#reference').val(response.reference);
                $('#currency_id').val(response.currency_id).formSelect();
                $('#currency_rate').val(response.currency_rate);
                $('#company_id').val(response.company_id).formSelect();
                $('#post_date').val(response.post_date);

                $('#note').val(response.note);
                $('#total').val(response.total);
                $('#tax').val(response.tax);
                $('#wtax').val(response.wtax);
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
                                <input type="hidden" name="arr_line[]" value="` + val.line_id + `">
                                <input type="hidden" name="arr_machine[]" value="` + val.machine_id + `">
                                <input type="hidden" name="arr_department[]" value="` + val.department_id + `">
                                <input type="hidden" name="arr_warehouse[]" value="` + val.warehouse_id + `">
                                <input type="hidden" name="arr_project[]" value="` + val.project_id + `">
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
                                <td class="center">
                                    ` + val.project_name + `
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
                                    <input name="arr_price[]" class="browser-default nominalitem" type="text" value="` + val.nominal + `" onkeyup="formatRupiah(this);" style="text-align:right;width:100% !important;" id="rowPrice`+ count +`" readonly>
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
                        $('#arr_temp_nominal' + val.id).val(val.total);
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
                            <td class="right-align" colspan="9">
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
                    $('#from_address,#to_address').text('-');
                    $('#temp_from_address,#temp_to_address').val('');
                    $('#body-item').append(`
                        <tr id="last-row-item">
                            <td colspan="14" class="center">
                                Silahkan pilih supplier untuk memulai...
                            </td>
                        </tr>
                    `);
                    $('#vendor_id').empty().trigger('change');
                    $('#delivery_cost_id').val('').trigger('change');
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
                    $('#modal6').modal('open');
                    $('#title_data').append(``+data.title+``);
                    $('#code_data').append(data.code);
                    $('#body-journal-table').append(data.tbody);
                    $('#user_jurnal').append(`Pengguna : `+data.user);
                    $('#note_jurnal').append(`Keterangan : `+data.note);
                    $('#ref_jurnal').append(`Referensi : `+data.reference);
                    $('#company_jurnal').append(`Perusahaan : `+data.company);
                    $('#post_date_jurnal').append(`Tanggal : `+data.post_date);
                }
            }
        });
    }

    function startIntro(){
        introJs().setOptions({
            exitOnOverlayClick : false,
            steps: [
                {
                    title : 'Landed Cost',
                    intro : 'Form ini digunakan untuk menerbitkan persediaan barang pada hutang usaha belum ditagihkan kepada vendor pengiriman ataupun pihak jasa yang akan mempengaruhi nilai suatu persediaan barang. Contoh kasus untuk memasukkan biaya jasa pengiriman yang ditagihkan oleh pihak ketiga (berbeda dari supplier). Disini anda bisa menarik data dari GRPO, sesama Landed Cost, dan Inventori Transfer Masuk (kasus berbeda Plant).'
                },
                {
                    title : 'Nomor Dokumen',
                    element : document.querySelector('.step1'),
                    intro : 'Nomor dokumen wajib diisikan, dengan kombinasi 4 huruf kode dokumen, tahun pembuatan dokumen, kode plant, serta nomor urut. Nomor ini bersifat unik, tidak akan sama, dan nomor urut paling belakang akan ter-reset secara otomatis berdasarkan tahun tanggal post.'
                },
                {
                    title : 'Kode Plant',
                    element : document.querySelector('.step2'),
                    intro : 'Pilih kode plant untuk nomor dokumen bisa secara otomatis ter-generate.'
                },
                {
                    title : 'Supplier/Vendor',
                    element : document.querySelector('.step3'),
                    intro : 'Supplier / vendor adalah Partner Bisnis tipe penyedia barang / jasa. Jika ingin menambahkan data baru, silahkan ke form Master Data - Organisasi - Partner Bisnis.' 
                },
                {
                    title : 'Tampilkan Data',
                    element : document.querySelector('.step4'),
                    intro : 'Ada 2 mode pengambilan data, yang pertama silahkan pilih supplier / vendor lalu tekan tombol ini, untuk menampilkan data GRPO atau Landed Cost dari supplier / vendor terpilih. Yang kedua, langsung tekan tombol ini tanpa memilih supplier / vendor (biarkan pada opsi <b>--Pilih ya--</b>), maka akan muncul tunggakan data Inventori Transfer Masuk.' 
                },
                {
                    title : 'Broker',
                    element : document.querySelector('.step5'),
                    intro : 'Broker adalah perusahaan penyedia layanan ketiga, yang nantinya tagihan invoice akan dikirimkan ke broker ini.' 
                },
                {
                    title : 'Perusahaan',
                    element : document.querySelector('.step6'),
                    intro : 'Perusahaan dimana dokumen ini dibuat.' 
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
                    title : 'Nomor Referensi',
                    element : document.querySelector('.step11'),
                    intro : 'Nomor referensi surat jalan atau dokumen terkait lampiran.'
                },
                {
                    title : 'GRPO/Inv.Transfer/Landed Cost Terpakai',
                    element : document.querySelector('.step12'),
                    intro : 'Daftar dokumen referensi yang terpakai akan muncul disini jika anda menggunakan GRPO ataupun . Anda bisa menghapus dengan cara menekan tombol x pada masing-masing tombol. Fungsi lain dari fitur ini adalah, agar PR/GI tidak bisa dipakai di form selain form aktif saat ini.' 
                },
                {
                    title : 'Rincian Biaya',
                    element : document.querySelector('.step13'),
                    intro : 'Disini rincian biaya bisa dimasukkan sesuai dokumen yang ada. Fitur ini juga mengakomodir PPN dan PPh.' 
                },
                {
                    title : 'Detail Harga per Produk',
                    element : document.querySelector('.step14'),
                    intro : 'Tabel ini berisi informasi pembagian nominal biaya-biaya LC ke produk yang ada berdasarkan harga pro-rata dikalikan qty barang pada waktu Purchase Order (jika GRPO).' 
                },
                {
                    title : 'Keterangan',
                    element : document.querySelector('.step15'),
                    intro : 'Silahkan isi / tambahkan keterangan untuk dokumen ini untuk dimunculkan di bagian bawah tabel detail produk nantinya, ketika dicetak.' 
                },
                {
                    title : 'Tombol Simpan',
                    element : document.querySelector('.step16'),
                    intro : 'Silahkan tekan tombol ini untuk menyimpan data, namun pastikan data yang akan anda masukkan benar.' 
                },
            ]
        }).start();
    }

    function startIntro2(){
        introJs().setOptions({
            exitOnOverlayClick : false,
            steps: [
                {
                    title : 'Daftar GRPO / Landed Cost / Inventori Transfer - Masuk',
                    intro : 'Form ini digunakan untuk menarik data dari supplier / vendor terpilih, mulai dari GRPO, Landed Cost, maupun Inventori Transfer - Masuk, yang ingin ditambahkan biaya pengiriman disana.'
                },
                {
                    title : 'Good Receipt PO',
                    element : document.querySelector('.step19'),
                    intro : 'Pilih tab ini untuk menampilkan data GRPO dari Supplier / Vendor terpilih.'
                },
                {
                    title : 'Landed Cost',
                    element : document.querySelector('.step20'),
                    intro : 'Pilih tab ini untuk menampilkan data Landed Cost dari Supplier / Vendor terpilih.'
                },
                {
                    title : 'Inventori Transfer - Masuk',
                    element : document.querySelector('.step21'),
                    intro : 'Pilih tab ini untuk menampilkan data Inventori Transfer Masuk.'
                },
                {
                    title : 'Tabel data',
                    element : document.querySelector('.step22'),
                    intro : 'Anda bisa memilih lebih dari satu data disini.'
                },
                {
                    title : 'Tombol Gunakan',
                    element : document.querySelector('.step23'),
                    intro : 'Untuk menggunakan data, setelah anda memilih data di tabel data, silahkan tekan tombol ini untuk memindahkannya ke tabel Landed Cost sebelumnya.'
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