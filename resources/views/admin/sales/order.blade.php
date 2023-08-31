<style>
    .modal {
        top:0px !important;
    }

    table > thead > tr > th {
        font-size: 13px !important;
    }

    .select-wrapper, .select2-container {
        height:3.9rem !important;
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
                                                <label for="filter_inventory" style="font-size:1rem;">Tipe Penjualan :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_sales_type" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        <option value="1">Standar SO</option>
                                                        <option value="2">Cash / POS</option>
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
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_delivery" style="font-size:1rem;">Tipe Pengiriman :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_delivery" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        <option value="1">Loco</option>
                                                        <option value="2">Franco</option>
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
                                                <label for="filter_supplier" style="font-size:1rem;">Customer :</label>
                                                <div class="input-field">
                                                    <select class="browser-default" id="filter_account" name="filter_account" multiple="multiple" style="width:100% !important;" onchange="loadDataTable()"></select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_supplier" style="font-size:1rem;">Pengirim / Ekspedisi :</label>
                                                <div class="input-field">
                                                    <select class="browser-default" id="filter_sender" name="filter_sender" multiple="multiple" style="width:100% !important;" onchange="loadDataTable()"></select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_supplier" style="font-size:1rem;">Sales :</label>
                                                <div class="input-field">
                                                    <select class="browser-default" id="filter_sales" name="filter_sales" multiple="multiple" style="width:100% !important;" onchange="loadDataTable()"></select>
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
                                                        <th>#</th>
                                                        <th>Code</th>
                                                        <th>Petugas</th>
                                                        <th>Customer</th>
                                                        <th>Perusahaan</th>
                                                        <th>Tipe Sales</th>
                                                        <th>Tgl.Post</th>
                                                        <th>Valid Hingga</th>
                                                        <th>Lampiran</th>
                                                        <th>No.Dokumen</th>
                                                        <th>Tipe Pengiriman</th>
                                                        <th>Pengirim</th>
                                                        <th>Tgl.Kirim</th>
                                                        <th>Tipe Pembayaran</th>
                                                        <th>TOP.Internal</th>
                                                        <th>TOP.Customer</th>
                                                        <th>Bergaransi</th>
                                                        <th>Alamat Pengiriman</th>
                                                        <th>Alamat Penagihan</th>
                                                        <th>Alamat Tujuan</th>
                                                        <th>Provinsi Tujuan</th>
                                                        <th>Kota Tujuan</th>
                                                        <th>Kec./Kel.Tujuan</th>
                                                        <th>Sales</th>
                                                        <th>Mata Uang</th>
                                                        <th>Konversi</th>
                                                        <th>% DP</th>
                                                        <th>Catatan</th>
                                                        <th>Subtotal</th>
                                                        <th>Diskon</th>
                                                        <th>Total</th>
                                                        <th>PPN</th>
                                                        <th>Total Stlh PPN</th>
                                                        <th>Rounding</th>
                                                        <th>Grandtotal</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
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
                            <div class="col s12">
                                <fieldset>
                                    <legend>1. Informasi Utama</legend>
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
                                        <select class="browser-default" id="account_id" name="account_id" onchange="getTopCustomer();"></select>
                                        <label class="active" for="account_id">Customer</label>
                                    </div>
                                    <div class="input-field col m3 s12 step4">
                                        <select class="form-control" id="company_id" name="company_id">
                                            @foreach ($company as $rowcompany)
                                                <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                            @endforeach
                                        </select>
                                        <label class="" for="company_id">Perusahaan</label>
                                    </div>
                                    <div class="input-field col m3 s12 step5">
                                        <select class="form-control" id="type_sales" name="type_sales">
                                            <option value="1">Standar SO</option>
                                            <option value="2">Cash / POS</option>
                                        </select>
                                        <label class="" for="type_sales">Tipe Penjualan</label>
                                    </div>
                                    <div class="input-field col m3 s12 step6">
                                        <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);">
                                        <label class="active" for="post_date">Tgl. Posting</label>
                                    </div>
                                    <div class="input-field col m3 s12 step7">
                                        <input id="valid_date" name="valid_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. Valid">
                                        <label class="active" for="valid_date">Valid Hingga</label>
                                    </div>
                                    <div class="input-field col m3 s12 step8">
                                        <input id="document_no" name="document_no" type="text" placeholder="No. Referensi dokumen...">
                                        <label class="active" for="document_no">No. Referensi</label>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col s12">
                                <fieldset>
                                    <legend>2. Pengiriman</legend>
                                    <div class="input-field col m3 s12 step9">
                                        <select class="form-control" id="type_delivery" name="type_delivery">
                                            <option value="1">Loco</option>
                                            <option value="2">Franco</option>
                                        </select>
                                        <label class="" for="type_delivery">Tipe Pengiriman</label>
                                    </div>
                                    <div class="input-field col m3 s12 step10">
                                        <select class="browser-default" id="sender_id" name="sender_id"></select>
                                        <label class="active" for="sender_id">Broker</label>
                                    </div>
                                    <div class="input-field col m3 s12 step11">
                                        <input id="delivery_date" name="delivery_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. Kirim">
                                        <label class="active" for="delivery_date">Tgl.Kirim</label>
                                    </div>
                                    <div class="input-field col m3 s12 step12">
                                        <textarea class="materialize-textarea" id="shipment_address" name="shipment_address" placeholder="Alamat Pengiriman (Ekspedisi)" rows="3"></textarea>
                                        <label class="active" for="shipment_address">Alamat Pengiriman (Ekspedisi)</label>
                                    </div>
                                    <div class="input-field col m3 s12 step13">
                                        <textarea class="materialize-textarea" id="billing_address" name="billing_address" placeholder="Alamat Penagihan" rows="3"></textarea>
                                        <label class="active" for="delivery_date">Alamat Penagihan</label>
                                    </div>
                                    <div class="input-field col m3 s12 step14">
                                        <textarea class="materialize-textarea" id="destination_address" name="destination_address" placeholder="Alamat Tujuan" rows="3"></textarea>
                                        <label class="active" for="destination_address">Alamat Tujuan</label>
                                    </div>
                                    <div class="input-field col m3 s12 step15">
                                        <select class="browser-default" id="province_id" name="province_id" onchange="getCity();"></select>
                                        <label class="active" for="province_id">Provinsi</label>
                                    </div>
                                    <div class="input-field col m3 s12 step16">
                                        <select class="select2 browser-default" id="city_id" name="city_id" onchange="getSubdistrict();">
                                            <option value="">--Pilih ya--</option>
                                        </select>
                                        <label class="active" for="city_id">Kota</label>
                                    </div>
                                    <div class="input-field col m3 s12 step17">
                                        <select class="select2 browser-default" id="subdistrict_id" name="subdistrict_id">
                                            <option value="">--Pilih ya--</option>
                                        </select>
                                        <label class="active" for="district_id">Kelurahan</label>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col s12">
                                <fieldset>
                                    <legend>3. Pembayaran</legend>
                                    <div class="input-field col m3 s12 step18">
                                        <select class="form-control" id="payment_type" name="payment_type" onchange="resetTerm()">
                                            <option value="1">Cash</option>
                                            <option value="2">Credit</option>
                                        </select>
                                        <label class="" for="payment_type">Tipe Pembayaran</label>
                                    </div>                   
                                    <div class="input-field col m3 s12 step19">
                                        <input id="top_internal" name="top_internal" type="number" value="0" min="0" step="1">
                                        <label class="active" for="top_internal">TOP Internal (hari)</label>
                                    </div>
                                    <div class="input-field col m3 s12 step20">
                                        <input id="top_customer" name="top_customer" type="number" value="0" min="0" step="1">
                                        <label class="active" for="top_customer">TOP Customer (hari)</label>
                                    </div>
                                    <div class="input-field col m3 s12 step21">
                                        <select class="form-control" id="is_guarantee" name="is_guarantee">
                                            <option value="1">Ya</option>
                                            <option value="2">Tidak</option>
                                        </select>
                                        <label class="" for="is_guarantee">Bergaransi</label>
                                    </div>
                                    <div class="input-field col m3 s12 step22">
                                        <select class="form-control" id="currency_id" name="currency_id">
                                            @foreach ($currency as $row)
                                                <option value="{{ $row->id }}">{{ $row->code.' '.$row->name }}</option>
                                            @endforeach
                                        </select>
                                        <label class="" for="currency_id">Mata Uang</label>
                                    </div>
                                    <div class="input-field col m3 s12 step23">
                                        <input id="currency_rate" name="currency_rate" type="text" value="1" onkeyup="formatRupiah(this)">
                                        <label class="active" for="currency_rate">Konversi</label>
                                    </div>
                                    <div class="input-field col m3 s12 step24">
                                        <input id="percent_dp" name="percent_dp" type="number" value="0" min="0" max="100">
                                        <label class="active" for="percent_dp">Prosentase DP (%)</label>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col s12">
                                <fieldset>
                                    <legend>4. Lain-lain</legend>
                                    <div class="file-field input-field col m3 s12 step25">
                                        <div class="btn">
                                            <span>Dokumen PO</span>
                                            <input type="file" name="document_so" id="document_so">
                                        </div>
                                        <div class="file-path-wrapper">
                                            <input class="file-path validate" type="text">
                                        </div>
                                    </div>
                                    <div class="input-field col m3 s12 step26">
                                        <select class="browser-default" id="sales_id" name="sales_id"></select>
                                        <label class="active" for="sales_id">Sales</label>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col s12 step27">
                                <fieldset style="min-width: 100%;">
                                    <legend>5. Produk Detail</legend>
                                    <div class="col m12 s12" style="overflow:auto;width:100% !important;" id="table-item">
                                        <p class="mt-2 mb-2">
                                            <table class="bordered" style="width:2500px;">
                                                <thead>
                                                    <tr>
                                                        <th class="center">Item</th>
                                                        <th class="center">Stock</th>
                                                        <th class="center">Plant & Gudang</th>
                                                        <th class="center">Qty Skrg</th>
                                                        <th class="center">Qty Sementara</th>
                                                        <th class="center">Qty Pesanan</th>
                                                        <th class="center">Satuan</th>
                                                        <th class="center">Harga</th>
                                                        <th class="center">Margin</th>
                                                        <th class="center">
                                                            PPN
                                                            <label class="pl-2">
                                                                <input type="checkbox" onclick="chooseAllPpn(this)">
                                                                <span style="padding-left: 25px;">Semua</span>
                                                            </label>
                                                        </th>
                                                        <th class="center">Termasuk PPN</th>
                                                        <th class="center">Disc1(%)</th>
                                                        <th class="center">Disc2(%)</th>
                                                        <th class="center">Disc3(Rp)</th>
                                                        <th class="center">Biaya Lain</th>
                                                        <th class="center">Harga Final</th>
                                                        <th class="center">Total</th>
                                                        <th class="center">Keterangan</th>
                                                        <th class="center">Hapus</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="body-item">
                                                    <tr id="last-row-item">
                                                        <td colspan="19">
                                                            Silahkan tambahkan baris ...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </p>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col m12 s12 center">
                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1 mt-1 step28" onclick="addItem()" href="javascript:void(0);">
                                    <i class="material-icons left">add</i> Tambah Baris
                                </a>
                            </div>
                            <div class="input-field col m4 s12 step29">
                                <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note">Keterangan</label>
                            </div>
                            <div class="input-field col m4 s12">

                            </div>
                            <div class="input-field col m4 s12 step30">
                                <table width="100%" class="bordered">
                                    <thead>
                                        <tr>
                                            <td width="50%">Subtotal</td>
                                            <td width="50%" class="right-align">
                                                <input class="browser-default" id="subtotal" name="subtotal" type="text" value="0,00" style="text-align:right;width:100%;" readonly>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Diskon</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="discount" name="discount" type="text" value="0,00" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100%;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Total</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="total" name="total" type="text" value="0,00" style="text-align:right;width:100%;" readonly>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>PPN</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="tax" name="tax" type="text" value="0,00" style="text-align:right;width:100%;" readonly>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Total Setelah Pajak</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="total_after_tax" name="total_after_tax" type="text" value="0,00" style="text-align:right;width:100%;" readonly>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Rounding</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="rounding" name="rounding" type="text" value="0,00" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100%;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Grandtotal</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="grandtotal" name="grandtotal" type="text" value="0,00" style="text-align:right;width:100%;" readonly>
                                            </td>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="col s12 mt-3">
                                <button class="btn waves-effect waves-light right submit step31" onclick="save();">Simpan <i class="material-icons right">send</i></button>
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

    var city = [], subdistrict = [];

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
                $('#account_id,#sender_id,#sales_id').empty();
                $('#subtotal,#discount,#total,#tax,#grandtotal,#rounding,#balance').val('0,00');
                $('.row_item').each(function(){
                    $(this).remove();
                });
                countAll();
                if($('.row_item').length == 0 && $('#last-row-item').length == 0){
                    $('#body-item').append(`
                        <tr id="last-row-item">
                            <td colspan="19">
                                Silahkan tambahkan baris ...
                            </td>
                        </tr>
                    `);
                    $('#table-item').animate( { 
                        scrollLeft: '0' }, 
                    500);
                }
                M.updateTextFields();
                $('#subdistrict_id,#city_id').empty().append(`
                    <option value="">--Pilih ya--</option>
                `);
                window.onbeforeunload = function() {
                    return null;
                };
                city = [];
                subdistrict = [];
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
            if($('.row_item').length == 0){
                $('#body-item').append(`
                    <tr id="last-row-item">
                        <td colspan="19">
                            Silahkan tambahkan baris ...
                        </td>
                    </tr>
                `);
                $('#table-item').animate( { 
                    scrollLeft: '0' }, 
                500);
            }
        });

        select2ServerSide('#account_id,#filter_account', '{{ url("admin/select2/customer") }}');
        select2ServerSide('#sales_id,#filter_sales', '{{ url("admin/select2/employee") }}');
        select2ServerSide('#sender_id,#filter_sender', '{{ url("admin/select2/supplier_vendor") }}');
        select2ServerSide('#province_id', '{{ url("admin/select2/province") }}');
    });

    function getTopCustomer(){
        if($('#account_id').val()){
            $('#top_internal').val($('#account_id').select2('data')[0].top_internal);
            $('#top_customer').val($('#account_id').select2('data')[0].top_customer);
        }else{
            $('#top_internal,#top_customer').val('0');
        }
    }

    function getCity(){
        $('#city_id,#subdistrict_id').empty().append(`
            <option value="">--Pilih ya--</option>
        `);
        if($('#province_id').val()){
            city = $('#province_id').select2('data')[0].cities;
            $.each(city, function(i, val) {
                $('#city_id').append(`
                    <option value="` + val.id + `">` + val.name + `</option>
                `);
            });
        }else{
            city = [];
        }
    }

    function getSubdistrict(){
        $('#subdistrict_id').empty().append(`
            <option value="">--Pilih ya--</option>
        `);
        if($('#city_id').val()){
            let index = -1;

            $.each(city, function(i, val) {
                if(val.id == $('#city_id').val()){
                    index = i;
                }
            });

            $.each(city[index].subdistrict, function(i, value) {
                $('#subdistrict_id').append(`
                    <option value="` + value.id + `">` + value.name + `</option>
                `);
            });
        }else{
            subdistrict = [];
        }
    }
    
    function printMultiSelect(){
        var formData = new FormData($('#form_data_print_multi')[0]);
        
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
    
    function getRowUnit(nil){
        if($("#arr_item" + nil).val()){
            $('#arr_item_stock' + nil).empty();
            if($("#arr_item" + nil).select2('data')[0].stock_list.length){
                $.each($("#arr_item" + nil).select2('data')[0].stock_list, function(i, value) {
                    $('#arr_item_stock' + nil).append(`
                        <option value="` + value.id + `" data-qty="` + value.qty_raw + `" data-qtycom="` + value.qty_commited + `" data-warehouse="` + value.warehouse + `" data-p="` + value.place_id + `" data-w="` + value.warehouse_id + `">` + value.warehouse + ` - ` + value.qty + `</option>
                    `);
                });
            }else{
                $('#arr_item_stock' + nil).append(`
                    <option value="" disabled selected>--Data stok tidak ditemukan--</option>
                `);
            }

            $('#arr_unit' + nil).text($("#arr_item" + nil).select2('data')[0].sell_unit);

        }else{
            $('#arr_item_stock' + nil).empty().append(`
                <option value="">--Silahkan pilih item--</option>
            `);
        }
        $('#arr_item_stock' + nil).trigger('change');
    }

    function getPlaceWarehouse(element,nil){
        if($(element).val()){
            $('#arr_place' + nil).val($(element).find(':selected').data('p'));
            $('#arr_warehouse' + nil).val($(element).find(':selected').data('w'));
            $("#arr_warehouse_name" + nil).text($(element).find(':selected').data('warehouse'));
            $("#arr_qty_now" + nil).text($(element).find(':selected').data('qty'));
            let balance = formatRupiahIni((parseFloat($(element).find(':selected').data('qty').replaceAll(".", "").replaceAll(",",".")) - parseFloat($(element).find(':selected').data('qtycom').replaceAll(".", "").replaceAll(",","."))).toFixed(3).toString().replace('.',','));
            $("#rowQty" + nil).attr('data-qty',balance);
            $("#arr_qty_temporary" + nil).text(balance);
        }else{
            $('#arr_place' + nil).val('');
            $('#arr_warehouse' + nil).val('');
            $("#arr_warehouse_name" + nil).text('-');
            $("#arr_qty_now" + nil).text('0,000');
            $("#arr_qty_temporary" + nil).text('0,000');
            $("#rowQty" + nil).attr('data-qty','0');
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

    function addItem(){
        var count = makeid(10);
        $('#last-row-item').remove();
        $('#body-item').append(`
            <tr class="row_item">
                <input type="hidden" name="arr_place[]" id="arr_place` + count + `">
                <input type="hidden" name="arr_warehouse[]" id="arr_warehouse` + count + `">
                <input type="hidden" name="arr_tax_nominal[]" id="arr_tax_nominal` + count + `" value="0,00">
                <input type="hidden" name="arr_grandtotal[]" id="arr_grandtotal` + count + `" value="0,00">
                <td>
                    <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                </td>
                <td>
                    <select class="browser-default" id="arr_item_stock` + count + `" name="arr_item_stock[]" style="width:200px !important;" onchange="getPlaceWarehouse(this,'` + count + `');">
                        <option value="">--Silahkan pilih item--</option>
                    </select>
                </td>
                <td class="center-align" id="arr_warehouse_name` + count + `">-</td>
                <td class="right-align" id="arr_qty_now` + count + `">0,000</td>
                <td class="right-align" id="arr_qty_temporary` + count + `">0,000</td>
                <td>
                    <input name="arr_qty[]" class="browser-default" type="text" value="0" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" data-qty="0" style="text-align:right;width:100px;" id="rowQty`+ count +`">
                </td>
                <td class="center">
                    <span id="arr_unit` + count + `">-</span>
                </td>
                <td class="center">
                    <input list="tempPrice` + count + `" name="arr_price[]" class="browser-default" type="text" value="0,00" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowPrice`+ count +`">
                    <datalist id="tempPrice` + count + `"></datalist>
                </td>
                <td class="center">
                    <input list="tempMargin` + count + `" name="arr_margin[]" class="browser-default" type="text" value="0,00" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowMargin`+ count +`">
                    <datalist id="tempMargin` + count + `"></datalist>
                </td>
                <td>
                    <select class="browser-default" id="arr_tax` + count + `" name="arr_tax[]" onchange="countRow('` + count + `')();">
                        <option value="0" data-id="0">-- Pilih ini jika non-PPN --</option>
                        @foreach ($tax as $row)
                            <option value="{{ $row->percentage }}" {{ $row->is_default_ppn ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <label>
                        <input type="checkbox" id="arr_is_include_tax` + count + `" name="arr_is_include_tax[]" value="1" onclick="countRow('` + count + `');">
                        <span>Ya/Tidak</span>
                    </label>
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
                    <input name="arr_other_fee[]" class="browser-default" type="text" value="0,00" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="arr_other_fee`+ count +`">
                </td>
                <td class="center">
                    <input name="arr_final_price[]" class="browser-default" type="text" value="0,00" style="text-align:right;" id="arr_final_price`+ count +`" readonly>
                </td>
                <td class="center">
                    <input name="arr_total[]" class="browser-default" type="text" value="0,00" style="text-align:right;" id="arr_total`+ count +`" readonly>
                </td>
                <td>
                    <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang...">
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        select2ServerSide('#arr_item' + count, '{{ url("admin/select2/sales_item") }}');
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
            "scrollCollapse": true,
            "scrollY": '400px',
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
                    status : $('#filter_status').val(),
                    sales_type : $('#filter_sales_type').val(),
                    delivery_type : $('#filter_delivery').val(),
                    payment_type : $('#filter_payment').val(),
                    'account_id[]' : $('#filter_account').val(),
                    'sender_id[]' : $('#filter_sender').val(),
                    'sales_id[]' : $('#filter_sales').val(),
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
                { name: 'code', className: '' },
                { name: 'user_id', className: '' },
                { name: 'account_id', className: '' },
                { name: 'company_id', className: '' },
                { name: 'sales_type', className: '' },
                { name: 'post_date', className: '' },
                { name: 'valid_date', className: '' },
                { name: 'document', className: '' },
                { name: 'document_no', className: '' },
                { name: 'delivery_type', className: '' },
                { name: 'sender_id', className: '' },
                { name: 'delivery_date', className: '' },
                { name: 'payment_type', className: '' },
                { name: 'top_internal', className: '' },
                { name: 'top_customer', className: '' },
                { name: 'is_guarantee', className: '' },
                { name: 'shipment_address', className: '' },
                { name: 'billing_address', className: '' },
                { name: 'destination_address', className: '' },
                { name: 'province_id', className: '' },
                { name: 'city_id', className: '' },
                { name: 'subdistrict_id', className: '' },
                { name: 'sales_id', className: '' },
                { name: 'currency_id', className: '' },
                { name: 'currency_rate', className: 'right-align' },
                { name: 'percent_dp', className: 'center-align' },
                { name: 'note', className: '' },
                { name: 'subtotal', className: 'right-align' },
                { name: 'discount', className: 'right-align' },
                { name: 'total', className: 'right-align' },
                { name: 'tax', className: 'right-align' },
                { name: 'grandtotal', className: 'right-align' },
                { name: 'rounding', className: 'right-align' },
                { name: 'balance', className: 'right-align' },
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
                var formData = new FormData($('#form_data')[0]), passed = true;

                formData.delete("arr_place[]");
                formData.delete("arr_warehouse[]");
                formData.delete("arr_tax_nominal[]");
                formData.delete("arr_grandtotal[]");
                formData.delete("arr_item[]");
                formData.delete("arr_item_stock[]");
                formData.delete("arr_qty[]");
                formData.delete("arr_price[]");
                formData.delete("arr_margin[]");
                formData.delete("arr_tax[]");
                formData.delete("arr_is_include_tax[]");
                formData.delete("arr_disc1[]");
                formData.delete("arr_disc2[]");
                formData.delete("arr_disc3[]");
                formData.delete("arr_other_fee[]");
                formData.delete("arr_final_price[]");
                formData.delete("arr_total[]");
                formData.delete("arr_note[]");
                
                if($('input[name^="arr_place"]').length > 0){
                    $('input[name^="arr_place"]').each(function(index){
                        formData.append('arr_place[]',$(this).val());
                        formData.append('arr_warehouse[]',$('input[name^="arr_warehouse"]').eq(index).val());
                        formData.append('arr_tax_nominal[]',$('input[name^="arr_tax_nominal"]').eq(index).val());
                        formData.append('arr_grandtotal[]',$('input[name^="arr_grandtotal"]').eq(index).val());
                        formData.append('arr_item[]',$('select[name^="arr_item"]').eq(index).val());
                        formData.append('arr_item_stock[]',$('select[name^="arr_item_stock"]').eq(index).val());
                        formData.append('arr_qty[]',$('input[name^="arr_qty"]').eq(index).val());
                        formData.append('arr_price[]',$('input[name^="arr_price"]').eq(index).val());
                        formData.append('arr_margin[]',$('input[name^="arr_margin"]').eq(index).val());
                        formData.append('arr_tax[]',$('select[name^="arr_tax"]').eq(index).val());
                        formData.append('arr_tax_id[]',$('option:selected','select[name^="arr_tax"]').eq(index).data('id'));
                        formData.append('arr_is_include_tax[]',($('input[name^="arr_is_include_tax"]').eq(index).is(':checked') ? '1' : '0'));
                        formData.append('arr_disc1[]',$('input[name^="arr_disc1"]').eq(index).val());
                        formData.append('arr_disc2[]',$('input[name^="arr_disc2"]').eq(index).val());
                        formData.append('arr_disc3[]',$('input[name^="arr_disc3"]').eq(index).val());
                        formData.append('arr_other_fee[]',$('input[name^="arr_other_fee"]').eq(index).val());
                        formData.append('arr_final_price[]',$('input[name^="arr_final_price"]').eq(index).val());
                        formData.append('arr_total[]',$('input[name^="arr_total"]').eq(index).val());
                        formData.append('arr_note[]',$('input[name^="arr_note"]').eq(index).val());
                        if(!$('select[name^="arr_item"]').eq(index).val()){
                            passed = false;
                        }
                    });
                }else{
                    passed = false;
                }

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
                    swal({
                        title: 'Ups!',
                        text: 'Item tidak boleh kosong.',
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
                $('#account_id').empty();
                $('#account_id').append(`
                    <option value="` + response.account_id + `">` + response.account_name + `</option>
                `);
                $('#company_id').val(response.company_id).formSelect();
                $('#type_sales').val(response.type_sales).formSelect();
                $('#post_date').val(response.post_date);
                $('#valid_date').val(response.valid_date);
                $('#document_no').val(response.document_no);
                $('#type_delivery').val(response.type_delivery).formSelect();
                $('#sender_id').empty().append(`<option value="` + response.sender_id + `">` + response.sender_name + `</option>`);
                $('#delivery_date').val(response.delivery_date);
                $('#shipment_address').val(response.shipment_address);
                $('#billing_address').val(response.billing_address);
                $('#destination_address').val(response.destination_address);
                $('#province_id').empty().append(`<option value="` + response.province_id + `">` + response.province_name + `</option>`);
                $('#subdistrict_id,#city_id').empty().append(`
                    <option value="">--Pilih ya--</option>
                `);
                $.each(response.cities, function(i, val) {
                    $('#city_id').append(`
                        <option value="` + val.id + `">` + val.name + `</option>
                    `);
                });
                $('#city_id').val(response.city_id).formSelect();
                let index = -1;
                $.each(response.cities, function(i, val) {
                    if(val.id == response.city_id){
                        index = i;
                    }
                });
                if(index >= 0){
                    $.each(response.cities[index].subdistrict, function(i, value) {
                        $('#subdistrict_id').append(`
                            <option value="` + value.id + `">` + value.name + `</option>
                        `);
                    });
                }
                $('#subdistrict_id').val(response.subdistrict_id).formSelect();
                $('#payment_type').val(response.payment_type).formSelect();
                $('#top_internal').val(response.top_internal);
                $('#top_customer').val(response.top_customer);
                $('#is_guarantee').val(response.is_guarantee).formSelect();
                $('#currency_id').val(response.currency_id).formSelect();
                $('#currency_rate').val(response.currency_rate);
                $('#sales_id').empty().append(`<option value="` + response.sales_id + `">` + response.sales_name + `</option>`);
                $('#note').val(response.note);
                $('#subtotal').val(response.subtotal);
                $('#discount').val(response.discount);
                $('#total').val(response.total);
                $('#tax').val(response.tax);
                $('#total_after_tax').val(response.total_after_tax);
                $('#rounding').val(response.rounding);
                $('#grandtotal').val(response.grandtotal);
                
                if(response.details.length > 0){
                    $('#last-row-item').remove();
                    $('.row_item').each(function(){
                        $(this).remove();
                    });

                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        $('#body-item').append(`
                            <tr class="row_item">
                                <input type="hidden" name="arr_place[]" id="arr_place` + count + `" value="` + val.place_id + `">
                                <input type="hidden" name="arr_warehouse[]" id="arr_warehouse` + count + `" value="` + val.warehouse_id + `">
                                <input type="hidden" name="arr_tax_nominal[]" id="arr_tax_nominal` + count + `" value="` + val.tax + `">
                                <input type="hidden" name="arr_grandtotal[]" id="arr_grandtotal` + count + `" value="` + val.grandtotal + `">
                                <td>
                                    <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_item_stock` + count + `" name="arr_item_stock[]" style="width:200px !important;" onchange="getPlaceWarehouse(this,'` + count + `');">
                                        <option value="">--Silahkan pilih item--</option>
                                    </select>
                                </td>
                                <td class="center-align" id="arr_warehouse_name` + count + `">` + val.item_stock_name + `</td>
                                <td class="right-align" id="arr_qty_now` + count + `">` + val.item_stock_qty + `</td>
                                <td class="right-align" id="arr_qty_temporary` + count + `">` + val.item_stock_qty + `</td>
                                <td>
                                    <input name="arr_qty[]" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" data-qty="0" style="text-align:right;width:100px;" id="rowQty`+ count +`">
                                </td>
                                <td class="center">
                                    <span id="arr_unit` + count + `">` + val.unit + `</span>
                                </td>
                                <td class="center">
                                    <input list="tempPrice` + count + `" name="arr_price[]" class="browser-default" type="text" value="` + val.price + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowPrice`+ count +`">
                                    <datalist id="tempPrice` + count + `"></datalist>
                                </td>
                                <td class="center">
                                    <input list="tempMargin` + count + `" name="arr_margin[]" class="browser-default" type="text" value="` + val.margin + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowMargin`+ count +`">
                                    <datalist id="tempMargin` + count + `"></datalist>
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_tax` + count + `" name="arr_tax[]" onchange="countRow('` + count + `');">
                                        <option value="0" data-id="0">-- Pilih ini jika non-PPN --</option>
                                        @foreach ($tax as $row)
                                            <option value="{{ $row->percentage }}" {{ $row->is_default_ppn ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <label>
                                        <input type="checkbox" id="arr_is_include_tax` + count + `" name="arr_is_include_tax[]" value="1" onclick="countRow('` + count + `');">
                                        <span>Ya/Tidak</span>
                                    </label>
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
                                    <input name="arr_other_fee[]" class="browser-default" type="text" value="` + val.other_fee + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="arr_other_fee`+ count +`">
                                </td>
                                <td class="center">
                                    <input name="arr_final_price[]" class="browser-default" type="text" value="` + val.final_price + `" style="text-align:right;" id="arr_final_price`+ count +`" readonly>
                                </td>
                                <td class="center">
                                    <input name="arr_total[]" class="browser-default" type="text" value="` + val.total + `" style="text-align:right;" id="arr_total`+ count +`" readonly>
                                </td>
                                <td>
                                    <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang..." value="` + val.note + `">
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                        $.each(val.list_stock, function(i, value) {
                            $('#arr_item_stock' + count).append(`
                                <option value="` + value.id + `" data-qty="` + value.qty_raw + `" data-qtycom="` + value.qty_commited + `" data-warehouse="` + value.warehouse + `" data-p="` + value.place_id + `" data-w="` + value.warehouse_id + `">` + value.warehouse + ` - ` + value.qty + `</option>
                            `);
                        });
                        $('#arr_item_stock' + count).val(val.item_stock_id).formSelect();
                        $("#arr_tax" + count + " option[data-id='" + val.tax_id + "']").prop("selected",true);
                        if(val.is_include_tax){
                            $('#arr_is_include_tax' + count).prop( "checked", true);
                        }
                        $('#arr_item' + count).append(`
                            <option value="` + val.item_id + `">` + val.item_name + `</option>
                        `);
                        select2ServerSide('#arr_item' + count, '{{ url("admin/select2/sales_item") }}');
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
            margin = parseFloat($('#rowMargin' + id).val().replaceAll(".", "").replaceAll(",",".")),
            disc1 = parseFloat($('#rowDisc1' + id).val().replaceAll(".", "").replaceAll(",",".")), 
            disc2 = parseFloat($('#rowDisc2' + id).val().replaceAll(".", "").replaceAll(",",".")), 
            disc3 = parseFloat($('#rowDisc3' + id).val().replaceAll(".", "").replaceAll(",","."));

        if(qtylimit > 0){
            if(qty > qtylimit){
                qty = qtylimit;
                $('#rowQty' + id).val(formatRupiahIni(qty.toFixed(3).toString().replace('.',',')));
            }
        }
        
        price = price - margin;

        var finalpricedisc1 = price - (price * (disc1 / 100));
        var finalpricedisc2 = finalpricedisc1 - (finalpricedisc1 * (disc2 / 100));
        var finalpricedisc3 = finalpricedisc2 - disc3;
        var rowtotal = (finalpricedisc3 * qty).toFixed(2);
        var rowtax = 0;

        if($('#arr_tax' + id).val() !== '0'){
            let percent_tax = parseFloat($('#arr_tax' + id).val());
            if($('#arr_is_include_tax' + id).is(':checked')){
                rowtotal = rowtotal / (1 + (percent_tax / 100));
            }
            rowtax = rowtotal * (percent_tax / 100);
        }

        $('#arr_tax_nominal' + id).val(rowtax.toFixed(2));
        $('#arr_grandtotal' + id).val((parseFloat(rowtax) + parseFloat(rowtotal)).toFixed(2));

        if(finalpricedisc3 >= 0){
            $('#arr_final_price' + id).val(formatRupiahIni(finalpricedisc3.toFixed(2).toString().replace('.',',')));
        }else{
            $('#arr_final_price' + id).val('-' + formatRupiahIni(finalpricedisc3.toFixed(2).toString().replace('.',',')));
        }

        if(rowtotal >= 0){
            $('#arr_total' + id).val(formatRupiahIni(roundTwoDecimal(rowtotal).toString().replace('.',',')));
        }else{
            $('#arr_total' + id).val('-' + formatRupiahIni(roundTwoDecimal(rowtotal).toString().replace('.',',')));
        }

        countAll();
    }

    function countAll(){
        var subtotal = 0, tax = 0, discount = parseFloat($('#discount').val().replaceAll(".", "").replaceAll(",",".")), total = 0, grandtotal = 0, rounding = parseFloat($('#rounding').val().replaceAll(".", "").replaceAll(",",".")), total_after_tax = 0;

        $('input[name^="arr_total"]').each(function(index){
			subtotal += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
            tax += parseFloat($('input[name^="arr_tax_nominal"]').eq(index).val());;
		});

        total = subtotal - discount;
        
        tax = Math.floor(tax);

        total_after_tax = total + tax;

        grandtotal = total_after_tax + rounding;

        $('#subtotal').val(
            (subtotal >= 0 ? '' : '-') + formatRupiahIni(subtotal.toFixed(2).toString().replace('.',','))
        );
        $('#total').val(
            (total >= 0 ? '' : '-') + formatRupiahIni(total.toFixed(2).toString().replace('.',','))
        );
        $('#tax').val(
            (tax >= 0 ? '' : '-') + formatRupiahIni(tax.toFixed(2).toString().replace('.',','))
        );
        $('#total_after_tax').val(
            (total_after_tax >= 0 ? '' : '-') + formatRupiahIni(total_after_tax.toFixed(2).toString().replace('.',','))
        );
        $('#grandtotal').val(
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

    function startIntro(){
        introJs().setOptions({
            exitOnOverlayClick : false,
            steps: [
                {
                    title : 'Marketing Order',
                    intro : 'Form ini digunakan untuk menambahkan dokumen SO atau Penawaran kepada Customer sesuai pesanan yang diinginkan.'
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
                    title : 'Customer',
                    element : document.querySelector('.step3'),
                    intro : 'Customer adalah Partner Bisnis tipe penyedia pelanggan. Jika ingin menambahkan data baru, silahkan ke form Master Data - Organisasi - Partner Bisnis.' 
                },
                {
                    title : 'Perusahaan',
                    element : document.querySelector('.step4'),
                    intro : 'Perusahaan dimana dokumen ini dibuat.' 
                },
                {
                    title : 'Tipe Penjualan',
                    element : document.querySelector('.step5'),
                    intro : 'Tipe Penjualan ada 2 macam, yang pertama adalah <b>Standar SO</b>, yakni SO yang dibuat sesuai pada umumnya, ada pembelian dan pembayaran yang berjangka dari Customer. Sedangkan tipe Cash / POS, adalah SO yang dibuat pada saat Customer membeli langsung secara cash ataupun secara retail / eceran.' 
                },
                {
                    title : 'Tgl. Posting',
                    element : document.querySelector('.step6'),
                    intro : 'Tanggal post akan menentukan tanggal jurnal untuk beberapa form yang terhubung dengan jurnal. Hati - hati dalam menentukan tanggal posting.' 
                },
                {
                    title : 'Tgl. Valid SO',
                    element : document.querySelector('.step7'),
                    intro : 'Tanggal berlaku SO / Penawaran, set sesuai dengan masa berlaku yang diinginkan.' 
                },
                {
                    title : 'No. Referensi',
                    element : document.querySelector('.step8'),
                    intro : 'No referensi bisa diisikan dengan no dokumen PO dari customer atau dokumen terkait lainnya yang mendukung penjualan ini.' 
                },
                {
                    title : 'Tipe Pengiriman',
                    element : document.querySelector('.step9'),
                    intro : 'Ada 2 macam tipe pengiriman, yakni yang pertama adalah Franco adalah biaya pengiriman barang dibebankan pada penjual. Sedangkan Loco, adalah kebalikan dari Franco, dimana biaya pengiriman barang dibebankan kepada customer.'
                },
                {
                    title : 'Broker',
                    element : document.querySelector('.step10'),
                    intro : 'Broker adalah pihak ekspedisi pengirim, silahkan tambahkan jika tidak ada, di Menu Master Data - Organisasi - Partner Bisnis.' 
                },
                {
                    title : 'Tgl. Kirim',
                    element : document.querySelector('.step11'),
                    intro : 'Tanggal perkiraan pengiriman barang dari gudang.' 
                },
                {
                    title : 'Alamat Pengiriman (Ekspedisi)',
                    element : document.querySelector('.step12'),
                    intro : 'Isi alamat pengiriman jika dikirimkan ke pihak ketiga sebagai pengirim. Contoh, jika Customer meminta mengirimkan barang ke ekspedisi langganan dia, di jl. Pahlawan, maka alamat ini diisi dengan alamat pihak ketiga tersebut.' 
                },
                {
                    title : 'Alamat Penagihan',
                    element : document.querySelector('.step13'),
                    intro : 'Alamat penagihan adalah alamat dimana dokumen penagihan invoice dikirimkan.' 
                },
                {
                    title : 'Alamat Tujuan',
                    element : document.querySelector('.step14'),
                    intro : 'Alamat tujuan adalah alamat dimana barang ingin dikirimkan.' 
                },
                {
                    title : 'Provinsi',
                    element : document.querySelector('.step15'),
                    intro : 'Provinsi dimana barang ingin dikirimkan (berdasarkan alamat tujuan).' 
                },
                {
                    title : 'Kota',
                    element : document.querySelector('.step16'),
                    intro : 'Kota dimana barang ingin dikirimkan (berdasarkan alamat tujuan).' 
                },
                {
                    title : 'Kelurahan',
                    element : document.querySelector('.step17'),
                    intro : 'Kelurahan dimana barang ingin dikirimkan (berdasarkan alamat tujuan).' 
                },
                {
                    title : 'Tipe Pembayaran',
                    element : document.querySelector('.step18'),
                    intro : 'Tipe pembayaran SO. Untuk Cash, maka TOP Internal dan TOP Customer akan menjadi 0. Untuk, tipe Credit, maka TOP Internal dan TOP Customer bisa diedit.' 
                },
                {
                    title : 'TOP (Term of Payment) Internal',
                    element : document.querySelector('.step19'),
                    intro : 'Tenggat pembayaran internal dalam satuan hari, untuk Finance.'
                },
                {
                    title : 'TOP (Term of Payment) Customer',
                    element : document.querySelector('.step20'),
                    intro : 'Tenggat pembayaran customer dalam satuan hari.'
                },
                {
                    title : 'Garansi',
                    element : document.querySelector('.step21'),
                    intro : 'Apakah SO ini bergaransi atau tidak.'
                },
                {
                    title : 'Mata Uang',
                    element : document.querySelector('.step22'),
                    intro : 'Mata uang, silahkan pilih mata uang lain, untuk mata uang asing.' 
                },
                {
                    title : 'Konversi',
                    element : document.querySelector('.step23'),
                    intro : 'Nilai konversi rupiah pada saat Sales Order dibuat.'
                },
                {
                    title : 'Persen DP',
                    element : document.querySelector('.step24'),
                    intro : 'Persen Down Payment yang akan menjadi acuan pengecekan credit limit Customer pada saat barang akan dijadwalkan pengirimannya. Silahkan isikan 0, jika tagihan akan dibayarkan secara kredit dan pengecekan akan didasarkan pada limit credit Customer. Silahkan isikan 100 jika tagihan adalah dibayarkan dengan 100% down payment.'
                },
                {
                    title : 'File Lampiran',
                    element : document.querySelector('.step25'),
                    intro : 'Silahkan unggah file lampiran. untuk saat ini hanya bisa mengakomodir 1 file lampiran saja. Jika ingin menambahkan file lebih dari 1, silahkan gabungkan file anda menjadi pdf.' 
                },
                {
                    title : 'Sales',
                    element : document.querySelector('.step26'),
                    intro : 'Inputan ini digunakan untuk mengatur sales terkait dengan penjualan. Data diambil dari Partner Bisnis tipe Karyawan / Pegawai.' 
                },
                {
                    title : 'Detail produk',
                    element : document.querySelector('.step27'),
                    intro : 'Silahkan tambahkan produk anda disini, lengkap dengan keterangan detail tentang produk tersebut. Hati-hati dalam menentukan Plant, dan Gudang Tujuan, karena itu nantinya akan menentukan dimana barang ketika diterima.' 
                },
                {
                    title : 'Tambah Baris',
                    element : document.querySelector('.step28'),
                    intro : 'Untuk menambahkan baris produk yang ingin diinput silahkan tekan tombol ini.' 
                },
                {
                    title : 'Keterangan',
                    element : document.querySelector('.step29'),
                    intro : 'Silahkan isi / tambahkan keterangan untuk dokumen ini untuk dimunculkan di bagian bawah tabel detail produk nantinya, ketika dicetak.' 
                },
                {
                    title : 'Diskon & Rounding',
                    element : document.querySelector('.step30'),
                    intro : 'Nominal diskon, untuk diskon yang ingin dimunculkan di dalam dokumen ketika dicetak. Diskon ini mengurangi subtotal. Sedangkan untuk Rounding akan menambah atau mengurangi nilai grandtotal sesuai inputan pengguna.' 
                },
                {
                    title : 'Tombol Simpan',
                    element : document.querySelector('.step31'),
                    intro : 'Silahkan tekan tombol ini untuk menyimpan data, namun pastikan data yang akan anda masukkan benar.' 
                },
            ]
        }).start();
    }
</script>