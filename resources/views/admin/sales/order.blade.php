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

    .select2-container {
        height:3.6rem !important;
    }

    .select-wrapper {
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
                        
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="printData();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">{{ __('translations.print') }}</span>
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
                                    <div class="collapsible-header"><i class="material-icons">filter_list</i>{{ __('translations.filter') }}</div>
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
                                                <label for="filter_type" style="font-size:1rem;">Tipe Penjualan :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_type" onchange="loadDataTable()">
                                                        <option value="">{{ __('translations.all') }}</option>
                                                        <option value="1">Proyek</option>
                                                        <option value="2">Retail</option>
                                                        <option value="3">Khusus</option>
                                                        <option value="4">Sampel</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_payment" style="font-size:1rem;">Tipe Pembayaran :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_payment" onchange="loadDataTable()">
                                                        <option value="">{{ __('translations.all') }}</option>
                                                        <option value="1">Cash Before Delivery</option>
                                                        <option value="2">Credit</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_delivery" style="font-size:1rem;">Tipe Pengiriman :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_delivery" onchange="loadDataTable()">
                                                        <option value="">{{ __('translations.all') }}</option>
                                                        <option value="1">Loco</option>
                                                        <option value="2">Franco</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_company" style="font-size:1rem;">Perusahaan :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_company" onchange="loadDataTable()">
                                                        <option value="">{{ __('translations.all') }}</option>
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
                                                        <option value="" disabled>{{ __('translations.all') }}</option>
                                                        @foreach ($currency as $row)
                                                            <option value="{{ $row->id }}">{{ $row->code }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="start_date" style="font-size:1rem;">{{ __('translations.start_date') }} : </label>
                                                <div class="input-field col s12">
                                                <input type="date" max="{{ date('9999'.'-12-31') }}" id="start_date" name="start_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="finish_date" style="font-size:1rem;">{{ __('translations.end_date') }} :</label>
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
                                            <div class="card-alert card green">
                                                <div class="card-content white-text">
                                                    <p>Info : Pada form ini akan ada inputan prosentase DP(%) yang mana akan menjadi acuan pro rata pengecekan DP dan limit kredit pelanggan dalam pembuatan AR Invoice secara otomatis setelah Surat Jalan dibuat (mengacu pada pengaturan Master Data - Organisasi - Partner Bisnis untuk seting pelanggan - Auto Generate SJ > AR Invoice = Ya). Contoh : jika Marketing Order Delivery dibuat dari Sales Order yang memiliki DP (100%), maka akan dilakukan pengecekan terhadap AR Down Payment yang tersisa berdasarkan pro rata prosentase DP. Jika syarat tidak terpenuhi maka AR Invoice tidak akan terbuat otomatis. Jika tidak ada DP (0%) maka, akan dilakukan pengecekan terhadap sisa kredit pelanggan, jika syarat tidak terpenuhi maka AR Invoice tidak akan terbuat otomatis.</p>
                                                </div>
                                            </div>
                                            <div id="datatable_buttons"></div>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">{{ __('translations.refresh') }}</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-2" href="javascript:void(0);" onclick="exportExcel();">
                                                <i class="material-icons hide-on-med-and-up">view_headline</i>
                                                <span class="hide-on-small-onl">Export</span>
                                                <i class="material-icons right">view_headline</i>
                                            </a>
                                            <table id="datatable_serverside">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{ __('translations.code') }}</th>
                                                        <th>Petugas</th>
                                                        <th>{{ __('translations.customer') }}</th>
                                                        <th>{{ __('translations.company') }}</th>
                                                        <th>{{ __('translations.type') }}</th>
                                                        <th>Tgl.Post</th>
                                                        <th>Valid Hingga</th>
                                                        <th>Proyek</th>
                                                        <th>Lampiran</th>
                                                        <th>No.Dokumen</th>
                                                        <th>Tipe Pengiriman</th>
                                                        <th>Pengirim</th>
                                                        <th>Tipe Transport</th>
                                                        <th>Tgl.Kirim</th>
                                                        <th>Tipe Pembayaran</th>
                                                        <th>TOP.Internal</th>
                                                        <th>TOP.Customer</th>
                                                        <th>Alamat Penagihan</th>
                                                        <th>{{ __('translations.outlet') }}</th>
                                                        <th>Alamat Tujuan</th>
                                                        <th>Provinsi Tujuan</th>
                                                        <th>Kota Tujuan</th>
                                                        <th>Kecamatan Tujuan</th>
                                                        <th>KelurahanTujuan</th>
                                                        <th>Sales</th>
                                                        <th>{{ __('translations.currency') }}</th>
                                                        <th>{{ __('translations.conversion') }}</th>
                                                        <th>% DP</th>
                                                        <th>Catatan Internal</th>
                                                        <th>Catatan Eksternal</th>
                                                        <th>Diskon</th>
                                                        <th>{{ __('translations.total') }}</th>
                                                        <th>{{ __('translations.tax') }}</th>
                                                        <th>Total Stlh PPN</th>
                                                        <th>Rounding</th>
                                                        <th>{{ __('translations.grandtotal') }}</th>
                                                        <th>{{ __('translations.status') }}</th>
                                                        <th>By</th>
                                                        <th>{{ __('translations.action') }}</th>
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
                <h4>{{ __('translations.add') }}/{{ __('translations.edit') }} {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="row">
                            <div class="col s12">
                                <fieldset>
                                    <legend>1. {{ __('translations.main_info') }}</legend>
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
                                        <label class="active" for="account_id">{{ __('translations.customer') }}</label>
                                    </div>
                                    <div class="input-field col m3 s12 step4">
                                        <select class="select2 browser-default" id="billing_address" name="billing_address">
                                            <option value="">--Pilih customer ya--</option>
                                        </select>
                                        <label class="active" for="billing_address">Alamat Penagihan</label>
                                    </div>
                                    <div class="input-field col m3 s12 step5">
                                        <select class="form-control" id="company_id" name="company_id">
                                            @foreach ($company as $rowcompany)
                                                <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                            @endforeach
                                        </select>
                                        <label class="" for="company_id">{{ __('translations.company') }}</label>
                                    </div>
                                    <div class="input-field col m3 s12 step6">
                                        <select class="form-control" id="type" name="type">
                                            <option value="1">Proyek</option>
                                            <option value="2">Retail</option>
                                            <option value="3">Khusus</option>
                                            <option value="4">Sampel</option>
                                        </select>
                                        <label class="" for="type">Tipe Penjualan</label>
                                    </div>
                                    <div class="input-field col m3 s12 step7">
                                        <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);loadCurrency();">
                                        <label class="active" for="post_date">{{ __('translations.post_date') }}</label>
                                    </div>
                                    <div class="input-field col m3 s12 step8">
                                        <input id="valid_date" name="valid_date" min="{{ date('Y-m-d') }}" type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. Valid">
                                        <label class="active" for="valid_date">Valid Hingga</label>
                                    </div>
                                    <div class="input-field col m3 s12 step9">
                                        <select class="browser-default" id="project_id" name="project_id"></select>
                                        <label for="project_id" class="active">Link Proyek (Jika ada) :</label>
                                    </div>
                                    <div class="input-field col m3 s12 step10">
                                        <input id="document_no" name="document_no" type="text" placeholder="No. Referensi dokumen...">
                                        <label class="active" for="document_no">No. Referensi</label>
                                    </div>
                                    <div class="input-field col m3 s12 ">
                                        
                                    </div>
                                    <div class="input-field col m3 s12 ">
                                        
                                    </div>
                                    <div class="input-field col m3 s12 right-align ">
                                        <h6>Deposit : <b><span id="limit">0,00</span></b></h6>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col s12">
                                <fieldset>
                                    <legend>2. Pengiriman</legend>
                                    <div class="input-field col m3 s12 step11">
                                        <select class="form-control" id="type_delivery" name="type_delivery" onchange="applyDelivery(this.value);">
                                            <option value="2">Franco</option>
                                            <option value="1">Loco</option>
                                        </select>
                                        <label class="" for="type_delivery">Tipe Pengiriman</label>
                                    </div>
                                    <div class="input-field col m3 s12 step12">
                                        <select class="browser-default" id="sender_id" name="sender_id"></select>
                                        <label class="active" for="sender_id">Ekspedisi</label>
                                    </div>
                                    <div class="input-field col m3 s12 step13">
                                        <select class="browser-default" id="transportation_id" name="transportation_id"></select>
                                        <label class="active" for="transportation_id">Tipe Transport</label>
                                    </div>
                                    <div class="input-field col m3 s12 step14">
                                        <input id="delivery_date" name="delivery_date" min="{{ date('Y-m-d') }}" type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. Kirim">
                                        <label class="active" for="delivery_date">Tgl.Kirim</label>
                                    </div>
                                    <div class="input-field col m3 s12 step15">
                                        <select class="browser-default" id="outlet_id" name="outlet_id" onchange="getOutletAddress();"></select>
                                        <label class="active" for="outlet_id">{{ __('translations.outlet') }}</label>
                                    </div>
                                    <div class="input-field col m3 s12 step16">
                                        <textarea class="materialize-textarea" id="destination_address" name="destination_address" placeholder="Alamat Tujuan" rows="3"></textarea>
                                        <label class="active" for="destination_address">Alamat Tujuan Kirim</label>
                                    </div>
                                    <div class="input-field col m3 s12 step17">
                                        <select class="browser-default" id="province_id" name="province_id" onchange="getCity();"></select>
                                        <label class="active" for="province_id">{{ __('translations.province') }}</label>
                                    </div>
                                    <div class="input-field col m3 s12 step18">
                                        <select class="select2 browser-default" id="city_id" name="city_id" onchange="getDistrict();">
                                            <option value="">--{{ __('translations.select') }}--</option>
                                        </select>
                                        <label class="active" for="city_id">{{ __('translations.city') }}</label>
                                    </div>
                                    <div class="input-field col m3 s12 step19">
                                        <select class="select2 browser-default" id="district_id" name="district_id" onchange="getSubdistrict();">
                                            <option value="">--{{ __('translations.select') }}--</option>
                                        </select>
                                        <label class="active" for="district_id">{{ __('translations.subdistrict') }}</label>
                                    </div>
                                    <div class="input-field col m3 s12 step20">
                                        <select class="select2 browser-default" id="subdistrict_id" name="subdistrict_id">
                                            <option value="">--{{ __('translations.select') }}--</option>
                                        </select>
                                        <label class="active" for="subdistrict_id">{{ __('translations.urban_village') }}</label>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col s12">
                                <fieldset>
                                    <legend>3. Pembayaran</legend>
                                    <div class="input-field col m3 s12 step21">
                                        <select class="form-control" id="payment_type" name="payment_type" onchange="resetTerm()">
                                            <option value="2">Credit</option>
                                            <option value="1">Cash Before Delivery</option>
                                        </select>
                                        <label class="" for="payment_type">Tipe Pembayaran</label>
                                    </div>                   
                                    <div class="input-field col m3 s12 step22">
                                        <input id="top_internal" name="top_internal" type="number" value="0" min="0" step="1">
                                        <label class="active" for="top_internal">TOP Internal (hari)</label>
                                    </div>
                                    <div class="input-field col m3 s12 step23">
                                        <input id="top_customer" name="top_customer" type="number" value="0" min="0" step="1">
                                        <label class="active" for="top_customer">TOP Customer (hari)</label>
                                    </div>
                                  
                                    <div class="input-field col m3 s12 step25">
                                        <select class="form-control" id="currency_id" name="currency_id" onchange="loadCurrency();">
                                            @foreach ($currency as $row)
                                                <option value="{{ $row->id }}" data-code="{{ $row->code }}">{{ $row->code.' '.$row->name }}</option>
                                            @endforeach
                                        </select>
                                        <label class="" for="currency_id">{{ __('translations.currency') }}</label>
                                    </div>
                                    <div class="input-field col m3 s12 step26">
                                        <input id="currency_rate" name="currency_rate" type="text" value="1" onkeyup="formatRupiah(this)">
                                        <label class="active" for="currency_rate">{{ __('translations.conversion') }}</label>
                                    </div>
                                    <div class="input-field col m3 s12 step27">
                                        <input id="percent_dp" name="percent_dp" type="text" value="0,00" onkeyup="formatRupiah(this);" style="text-align:right;">
                                        <label class="active" for="percent_dp">Prosentase DP (%)</label>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col s12">
                                <fieldset>
                                    <legend>4. Lain-lain</legend>
                                    <div class="file-field input-field col m3 s12 step28">
                                        <div class="btn">
                                            <span>Dokumen PO</span>
                                            <input type="file" name="document_so" id="document_so">
                                        </div>
                                        <div class="file-path-wrapper">
                                            <input class="file-path validate" type="text">
                                        </div>
                                    </div>
                                    <div class="input-field col m3 s12 step29">
                                        <select class="browser-default" id="sales_id" name="sales_id"></select>
                                        <label class="active" for="sales_id">Sales</label>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col s12 step30">
                                <fieldset style="min-width: 100%;">
                                    <legend>5. Produk Detail</legend>
                                    <div class="col m12 s12" style="overflow:auto;width:100% !important;" id="table-item">
                                        <p class="mt-2 mb-2">
                                            <table class="bordered" style="width:3500px;" id="table-detail">
                                                <thead>
                                                    <tr>
                                                        <th class="center">{{ __('translations.item') }}</th>
                                                        <th class="center">{{ __('translations.plant') }}</th>
                                                        <th class="center">Qty Skrg</th>
                                                        <th class="center">Qty Sementara</th>
                                                        <th class="center">Satuan UoM</th>
                                                        <th class="center">Qty Pesanan</th>
                                                        <th class="center">Satuan Pesanan</th>
                                                        <th class="center">Qty Konversi</th>
                                                        <th class="center">{{ __('translations.price') }}</th>
                                                       
                                                        <th class="center">
                                                            PPN
                                                            <label class="pl-2">
                                                                <input type="checkbox" onclick="chooseAllPpn(this)">
                                                                <span style="padding-left: 25px;">{{ __('translations.all') }}</span>
                                                            </label>
                                                        </th>
                                                        <th class="center">Termasuk PPN</th>
                                                        <th class="center">Disc1(%)</th>
                                                        <th class="center">Disc2(%)</th>
                                                        <th class="center">Disc3(Rp)</th>
                                                        <th class="center">{{ __('translations.final_price') }}</th>
                                                        <th class="center">{{ __('translations.total') }}</th>
                                                        <th class="center">{{ __('translations.note') }}</th>
                                                        <th class="center">{{ __('translations.delete') }}</th>
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
                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1 mt-1 step31" onclick="addItem()" href="javascript:void(0);">
                                    <i class="material-icons left">add</i> Tambah Baris
                                </a>
                            </div>
                            <div class="input-field col m4 s12 step32">
                                <textarea class="materialize-textarea" id="note_internal" name="note_internal" placeholder="Catatan / Keterangan Internal" rows="3"></textarea>
                                <label class="active" for="note_internal">Keterangan Internal</label>
                            </div>
                            <div class="input-field col m4 s12 step33">
                                <textarea class="materialize-textarea" id="note_external" name="note_external" placeholder="Catatan / Keterangan Eksternal" rows="3"></textarea>
                                <label class="active" for="note_external">Keterangan Eksternal</label>
                            </div>
                            <div class="input-field col m4 s12">

                            </div>
                            <div class="input-field col m4 s12 step34">
                                <table width="100%" class="bordered">
                                    <thead>
                                        
                                        {{-- <tr>
                                            <td>Diskon</td>
                                            <td class="right-align"> --}}
                                                <input class="browser-default" id="discount" name="discount" type="hidden" value="0,00" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100%;">
                                            {{-- </td>
                                        </tr> --}}
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
                                        {{-- <tr>
                                            <td>Total Setelah Pajak</td>
                                            <td class="right-align"> --}}
                                                <input class="browser-default" id="total_after_tax" name="total_after_tax" type="hidden" value="0,00" style="text-align:right;width:100%;" readonly>
                                            {{-- </td>
                                        </tr> --}}
                                        <tr style="display:none;">
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
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light purple btn-panduan" onclick="startIntro();">Panduan <i class="material-icons right">help_outline</i></button>
        <button class="btn waves-effect waves-light right submit step35" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
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
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
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
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
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
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
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
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat mr-1">{{ __('translations.close') }}</a>
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
    var city = [], district = [], subdistrict = [];

    $(function() {
        $("#table-detail th").resizable({
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
                if(!$('#temp').val()){
                    loadCurrency();
                }
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                $('#limit').text('0,00');
                $('#account_id,#sender_id,#sales_id,#project_id,#transportation_id,#outlet_id').empty();
                $('#total,#tax,#grandtotal,#rounding,#balance').val('0,00');
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
                $('#province_id,#subdistrict_id,#district_id,#city_id').empty().append(`
                    <option value="">--{{ __('translations.select') }}--</option>
                `);
                $('#billing_address').empty().append(`
                    <option value="">--Pilih customer ya--</option>
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
        select2ServerSide('#project_id', '{{ url("admin/select2/project") }}');
        select2ServerSide('#transportation_id', '{{ url("admin/select2/transportation") }}');
        select2ServerSide('#outlet_id', '{{ url("admin/select2/outlet") }}');
    });

    function getOutletAddress(){
        if($('#outlet_id').val()){
            $('#province_id,#subdistrict_id,#district_id,#city_id').empty();
            $('#destination_address').val($('#outlet_id').select2('data')[0].address);
            $('#province_id').empty().append(`
                <option value="` + $('#outlet_id').select2('data')[0].province_id + `">` + $('#outlet_id').select2('data')[0].province_name + `</option>
            `);
            $.each($('#outlet_id').select2('data')[0].cities, function(i, val) {
                $('#city_id').append(`
                    <option value="` + val.id + `" ` + ($('#outlet_id').select2('data')[0].city_id == val.id ? 'selected' : '') + `>` + val.name + `</option>
                `);
            });
            let index = -1;
            $.each($('#outlet_id').select2('data')[0].cities, function(i, val) {
                if(val.id == $('#outlet_id').select2('data')[0].city_id){
                    index = i;
                }
            });
            if(index >= 0){
                $.each($('#outlet_id').select2('data')[0].cities[index].district, function(i, value) {
                    let selected = '';
                    $('#district_id').append(`
                        <option value="` + value.id + `" ` + (value.id == $('#outlet_id').select2('data')[0].district_id ? 'selected' : '') + ` data-subdistrict='` + JSON.stringify(value.subdistrict) + `'>` + value.name + `</option>
                    `);
                    if(value.id == $('#outlet_id').select2('data')[0].district_id){
                        subdistrict = value.subdistrict;
                    }
                });

                $.each(subdistrict, function(i, value) {
                    $('#subdistrict_id').append(`
                        <option value="` + value.id + `" ` + (value.id == $('#outlet_id').select2('data')[0].subdistrict_id ? 'selected' : '') + `>` + value.name + `</option>
                    `);
                });
            }
        }else{
            $('destination_address').val('');
            $('#province_id,#subdistrict_id,#district_id,#city_id').empty().append(`
                <option value="">--{{ __('translations.select') }}--</option>
            `);
        }
    }

    function applyDelivery(val){
        if(val == '1'){
            $('#transportation_id').empty();
        }
    }

    function resetTerm(){
        if($('#payment_type').val() == '1'){
            $('#top_internal').val('0');
            $('#top_customer').val('0');
        }else{
            if($('#account_id').val()){
                $('#top_internal').val($('#account_id').select2('data')[0].top_internal);
                $('#top_customer').val($('#account_id').select2('data')[0].top_customer);
            }else{
                $('#top_internal').val('0');
                $('#top_customer').val('0');
            }
        }
    }

    function getTopCustomer(){
        if($('#account_id').val()){
            $('#top_internal').val($('#account_id').select2('data')[0].top_internal);
            $('#top_customer').val($('#account_id').select2('data')[0].top_customer);
            $('#limit').text($('#account_id').select2('data')[0].deposit);
            var result = new Date($('#post_date').val());
            result.setDate(result.getDate() + parseInt($('#account_id').select2('data')[0].top_customer));
            $('#valid_date').val(result.toISOString().split('T')[0]);
            $('#billing_address').empty();
            if($('#account_id').select2('data')[0].billing_address.length > 0){
                $.each($('#account_id').select2('data')[0].billing_address, function(i, val) {
                    $('#billing_address').append(`
                        <option value="` + val.id + `">` + val.npwp + ` ` + val.address + `</option>
                    `);
                });
            }else{
                $('#billing_address').append(`
                    <option value="">--Data tidak ditemukan--</option>
                `); 
            }
        }else{
            $('#top_internal,#top_customer').val('0');
            $('#valid_date').val('{{ date("Y-m-d") }}');
            $('#billing_address').empty().append(`
                <option value="">--Pilih customer ya--</option>
            `);
        }
    }

    function getCity(){
        $('#city_id,#subdistrict_id,#district_id').empty().append(`
            <option value="">--{{ __('translations.select') }}--</option>
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
            district = [];
            subdistrict = [];
        }
    }

    function getDistrict(){
        $('#subdistrict_id,#district_id').empty().append(`
            <option value="">--{{ __('translations.select') }}--</option>
        `);
        if($('#city_id').val()){
            let index = -1;

            $.each(city, function(i, val) {
                if(val.id == $('#city_id').val()){
                    index = i;
                }
            });

            $.each(city[index].district, function(i, value) {
                $('#district_id').append(`
                    <option value="` + value.id + `" data-subdistrict='` + JSON.stringify(value.subdistrict) + `'>` + value.name + `</option>
                `);
            });
        }else{
            district = [];
            subdistrict = [];
        }
    }

    function getSubdistrict(){
        $('#subdistrict_id').empty().append(`
            <option value="">--{{ __('translations.select') }}--</option>
        `);
        if($('#district_id').val()){
            
            let index = -1;

            $.each(city, function(i, val) {
                if(val.id == $('#city_id').val()){
                    index = i;
                }
            });

            $.each(city[index].district, function(i, value) {
                if(value.id == $('#district_id').val()){
                    subdistrict = value.subdistrict;
                }
            });

            $.each(subdistrict, function(i, value) {
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
        $('#tempPrice' + nil).empty();
        $("#arr_warehouse" + nil).empty();
        if($("#arr_item" + nil).val()){

            $('#arr_uom_unit' + nil).empty().append($("#arr_item" + nil).select2('data')[0].uom);
            $('#arr_qty_now' + nil).empty().append($("#arr_item" + nil).select2('data')[0].stock_now);
            $('#arr_qty_temporary' + nil).empty().append($("#arr_item" + nil).select2('data')[0].stock_com);

            $('#arr_unit' + nil).empty();

            $.each($("#arr_item" + nil).select2('data')[0].sell_units, function(i, value) {
                $('#arr_unit' + nil).append(`
                    <option value="` + value.id + `" data-conversion="` + value.conversion + `">` + value.code + `</option>
                `);
            });

            if($("#arr_item" + nil).select2('data')[0].list_warehouse.length > 0){
                $.each($("#arr_item" + nil).select2('data')[0].list_warehouse, function(i, value) {
                    $('#arr_warehouse' + nil).append(`
                        <option value="` + value.id + `">` + value.name + `</option>
                    `);
                });
            }else{
                $("#arr_warehouse" + nil).append(`
                    <option value="">--Gudang tidak diatur di master data Grup Item--</option>
                `);
            }

            if($("#arr_item" + nil).select2('data')[0].old_prices.length > 0){
                $.each($("#arr_item" + nil).select2('data')[0].old_prices, function(i, value) {
                    if($('#account_id').val()){
                        if(value.customer_id == $('#account_id').val()){
                            $('#tempPrice' + nil).append(`
                                <option value="` + value.price + `">` + value.sales_code + ` Supplier ` + value.customer_name + ` Tgl ` + value.post_date + `</option>
                            `);
                        }
                    }else{
                        $('#tempPrice' + nil).append(`
                            <option value="` + value.price + `">` + value.sales_code + ` Supplier ` + value.customer_name + ` Tgl ` + value.post_date + `</option>
                        `);
                    }
                });
            }
            if($("#arr_item" + nil).select2('data')[0].list_outletprice.length > 0){
                if($('#account_id').val() && $('#outlet_id').val()){
                    let enough = false;
                    $.each($("#arr_item" + nil).select2('data')[0].list_outletprice, function(i, value) {
                        if(value.account_id == $('#account_id').val() && value.outlet_id == $('#outlet_id').val() && enough == false){
                            $("#rowPrice" + nil).val(value.price);
                            $("#rowDisc1" + nil).val(value.percent_discount_1);
                            $("#rowDisc2" + nil).val(value.percent_discount_2);
                            $("#rowDisc3" + nil).val(value.discount_3);
                            $("#arr_final_price" + nil).val(value.final_price);
                            enough = true;
                        }
                    });
                }
            }
        }else{
            $('#arr_uom_unit' + nil).empty().append(`-`);
            $('#arr_qty_now' + nil).empty().append(`-`);
            $('#arr_qty_temporary' + nil).empty().append(`-`);
            $('#arr_unit' + nil).empty().append(`
                <option value="">--Silahkan pilih item--</option>
            `);
            $("#arr_warehouse" + nil).append(`
                <option value="">--Silahkan pilih item--</option>
            `);
            $("#rowPrice" + nil).val('0,00');
            $("#rowDisc1" + nil).val('0');
            $("#rowDisc2" + nil).val('0');
            $("#rowDisc3" + nil).val('0');
            $("#arr_final_price" + nil).val('0,00');
        }
    }

    function addItem(){
        if($('#code_place_id').val()){
            var selectedValue = $('#code_place_id').val();
            var selectedText = $('#code_place_id option:selected').text();
            var count = makeid(10);
            $('#last-row-item').remove();
            $('#body-item').append(`
                <tr class="row_item">
                    <input type="hidden" name="arr_tax_nominal[]" id="arr_tax_nominal` + count + `" value="0,00">
                    <input type="hidden" name="arr_grandtotal[]" id="arr_grandtotal` + count + `" value="0,00">
                    <td>
                        <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                    </td>
                    <td class="center-align">
                        <select class="browser-default" id="arr_place` + count + `" name="arr_place[]">
                            <option value="` + selectedValue + `">` + selectedText + `</option>
                            
                        </select>
                    </td>
                    <td class="right-align" id="arr_qty_now` + count + `">0,000</td>
                    <td class="right-align" id="arr_qty_temporary` + count + `">0,000</td>
                    <td class="center-align" id="arr_uom_unit` + count + `">-</td>
                    <td>
                        <input name="arr_qty[]" class="browser-default" type="text" value="0" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" data-qty="0" style="text-align:right;width:100px;" id="rowQty`+ count +`">
                    </td>
                    <td class="center">
                        <select class="browser-default" id="arr_unit` + count + `" name="arr_unit[]" onchange="countRow('` + count + `');">
                            <option value="">--Silahkan pilih item--</option>
                        </select>
                    </td>
                    <td class="center">
                        <div name="arr_konversi[]"  style="text-align:right;" id="arr_konversi`+ count +`">
                    </td>
                    <td class="center">
                        <input list="tempPrice` + count + `" name="arr_price[]" class="browser-default" type="text" value="0,00" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowPrice`+ count +`">
                        <datalist id="tempPrice` + count + `"></datalist>
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
        }else{
            swal({
                title: '!',
                text: 'Harap Pilih Plant Terlebih dahulu.',
                icon: 'error'
            });
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
                type: 'POST',
                data: {
                    'status[]' : $('#filter_status').val(),
                    type : $('#filter_type').val(),
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
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
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
                { name: 'type', className: '' },
                { name: 'post_date', className: '' },
                { name: 'valid_date', className: '' },
                { name: 'project_id', searchable: false, orderable: false, className: '' },
                { name: 'document', className: '' },
                { name: 'document_no', className: '' },
                { name: 'delivery_type', className: '' },
                { name: 'sender_id', className: '' },
                { name: 'transportation_id', className: '' },
                { name: 'delivery_date', className: '' },
                { name: 'payment_type', className: '' },
                { name: 'top_internal', className: '' },
                { name: 'top_customer', className: '' },
               
                { name: 'billing_address', className: '' },
                { name: 'outlet_id', className: '' },
                { name: 'destination_address', className: '' },
                { name: 'province_id', className: '' },
                { name: 'city_id', className: '' },
                { name: 'district_id', className: '' },
                { name: 'subdistrict_id', className: '' },
                { name: 'sales_id', className: '' },
                { name: 'currency_id', className: '' },
                { name: 'currency_rate', className: 'right-align' },
                { name: 'percent_dp', className: 'center-align' },
                { name: 'note_internal', className: '' },
                { name: 'note_external', className: '' },
                { name: 'discount', className: 'right-align' },
                { name: 'total', className: 'right-align' },
                { name: 'tax', className: 'right-align' },
                { name: 'grandtotal', className: 'right-align' },
                { name: 'rounding', className: 'right-align' },
                { name: 'balance', className: 'right-align' },
              { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'by', searchable: false, orderable: false, className: 'center-align' },
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
                formData.delete("arr_tax_nominal[]");
                formData.delete("arr_grandtotal[]");
                formData.delete("arr_item[]");
                formData.delete("arr_unit[]");
                formData.delete("arr_qty[]");
                formData.delete("arr_price[]");
                formData.delete("arr_tax[]");
                formData.delete("arr_is_include_tax[]");
                formData.delete("arr_disc1[]");
                formData.delete("arr_disc2[]");
                formData.delete("arr_disc3[]");
                formData.delete("arr_final_price[]");
                formData.delete("arr_total[]");
                formData.delete("arr_note[]");
                
                if($('select[name^="arr_place[]"]').length > 0){
                    $('select[name^="arr_place[]"]').each(function(index){
                        formData.append('arr_place[]',$(this).val());
                        formData.append('arr_tax_nominal[]',$('input[name^="arr_tax_nominal"]').eq(index).val());
                        formData.append('arr_grandtotal[]',$('input[name^="arr_grandtotal"]').eq(index).val());
                        formData.append('arr_item[]',$('select[name^="arr_item"]').eq(index).val());
                        formData.append('arr_unit[]',($('select[name^="arr_unit[]"]').eq(index).val() ? $('select[name^="arr_unit[]"]').eq(index).val() : '' ));
                        formData.append('arr_qty[]',$('input[name^="arr_qty"]').eq(index).val());
                        formData.append('arr_price[]',$('input[name^="arr_price"]').eq(index).val());
                       
                        formData.append('arr_tax[]',$('select[name^="arr_tax"]').eq(index).val());
                        formData.append('arr_tax_id[]',$('option:selected','select[name^="arr_tax"]').eq(index).data('id'));
                        formData.append('arr_is_include_tax[]',($('input[name^="arr_is_include_tax"]').eq(index).is(':checked') ? '1' : '0'));
                        formData.append('arr_disc1[]',$('input[name^="arr_disc1"]').eq(index).val());
                        formData.append('arr_disc2[]',$('input[name^="arr_disc2"]').eq(index).val());
                        formData.append('arr_disc3[]',$('input[name^="arr_disc3"]').eq(index).val());
                       
                        formData.append('arr_final_price[]',$('input[name^="arr_final_price"]').eq(index).val());
                        formData.append('arr_total[]',$('input[name^="arr_total"]').eq(index).val());
                        formData.append('arr_note[]',$('input[name^="arr_note[]"]').eq(index).val());
                        if(!$('select[name^="arr_item"]').eq(index).val()){
                            passed = false;
                        }
                        if(!$(this).val()){
                            passed = false;
                        }
                        if(!$('select[name^="arr_unit"]').eq(index).val()){
                            passed = false;
                        }
                    });
                }else{
                    passed = false;
                }

                if(passed == true){
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
                            $('input').css('border', 'none');
                            $('input').css('border-bottom', '0.5px solid black');
                            loadingClose('#modal1');
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
                        title: 'Ups!',
                        text: 'Item / stok / plant / satuan tidak boleh kosong.',
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
                $('#limit').text(response.deposit);
                $('#company_id').val(response.company_id).formSelect();
                $('#type').val(response.type).formSelect();
                $('#post_date').val(response.post_date);
                $('#valid_date').val(response.valid_date);
                $('#document_no').val(response.document_no);
                $('#type_delivery').val(response.type_delivery).formSelect();
                $('#sender_id').empty().append(`<option value="` + response.sender_id + `">` + response.sender_name + `</option>`);
                $('#delivery_date').val(response.delivery_date);
                $('#transportation_id').empty().append(`
                    <option value="` + response.transportation_id + `">` + response.transportation_name + `</option>
                `);
                $('#outlet_id').empty().append(`
                    <option value="` + response.outlet_id + `">` + response.outlet_name + `</option>
                `);
                $('#billing_address').empty();
                $.each(response.user_data, function(i, val) {
                    $('#billing_address').append(`
                        <option value="` + val.id + `" ` + (val.id == response.user_data_id ? 'selected' : '') + `>` + val.npwp + ` ` + val.address + `</option>
                    `);
                });
                $('#destination_address').val(response.destination_address);
                $('#province_id').empty().append(`<option value="` + response.province_id + `">` + response.province_name + `</option>`);
                $('#subdistrict_id,#district_id,#city_id').empty().append(`
                    <option value="">--{{ __('translations.select') }}--</option>
                `);
                $('#project_id').empty();
                if(response.project_name){
                    $('#project_id').append(`
                        <option value="` + response.project_id + `">` + response.project_name + `</option>
                    `);
                }
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
                    $.each(response.cities[index].district, function(i, value) {
                        let selected = '';
                        $('#district_id').append(`
                            <option value="` + value.id + `" ` + (value.id == response.district_id ? 'selected' : '') + ` data-subdistrict='` + JSON.stringify(value.subdistrict) + `'>` + value.name + `</option>
                        `);
                        if(value.id == response.district_id){
                            subdistrict = value.subdistrict;
                        }
                    });

                    $.each(subdistrict, function(i, value) {
                        $('#subdistrict_id').append(`
                            <option value="` + value.id + `" ` + (value.id == response.subdistrict_id ? 'selected' : '') + `>` + value.name + `</option>
                        `);
                    });
                }
                $('#payment_type').val(response.payment_type).formSelect();
                $('#top_internal').val(response.top_internal);
                $('#top_customer').val(response.top_customer);
                $('#is_guarantee').val(response.is_guarantee).formSelect();
                $('#currency_id').val(response.currency_id).formSelect();
                $('#currency_rate').val(response.currency_rate);
                $('#percent_dp').val(response.percent_dp);
                $('#sales_id').empty().append(`<option value="` + response.sales_id + `">` + response.sales_name + `</option>`);
                $('#note_internal').val(response.note_internal);
                $('#note_external').val(response.note_external);
            
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
                                <input type="hidden" name="arr_tax_nominal[]" id="arr_tax_nominal` + count + `" value="` + val.tax + `">
                                <input type="hidden" name="arr_grandtotal[]" id="arr_grandtotal` + count + `" value="` + val.grandtotal + `">
                                <td>
                                    <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                                </td>
                                <td class="center-align">
                                    <select class="browser-default" id="arr_place` + count + `" name="arr_place[]">
                                        @foreach ($place as $rowplace)
                                            <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="right-align" id="arr_qty_now` + count + `">` + val.qty_now + `</td>
                                <td class="right-align" id="arr_qty_temporary` + count + `">` + val.qty_commited + `</td>
                                <td class="center-align" id="arr_uom_unit` + count + `">` + val.uom + `</td>
                                <td>
                                    <input name="arr_qty[]" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" data-qty="0" style="text-align:right;width:100px;" id="rowQty`+ count +`">
                                </td>
                                <td class="center">
                                    <select class="browser-default" id="arr_unit` + count + `" name="arr_unit[]" onchange="countRow('` + count + `');"></select>
                                </td>
                                <td class="center">
                                    <div name="arr_konversi[]"  style="text-align:right;" id="arr_konversi`+ count +`">
                                </td>
                                <td class="center">
                                    <input list="tempPrice` + count + `" name="arr_price[]" class="browser-default" type="text" value="` + val.price + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowPrice`+ count +`">
                                    <datalist id="tempPrice` + count + `"></datalist>
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
                        $('#arr_place' + count).val(val.place_id);
                        $("#arr_tax" + count + " option[data-id='" + val.tax_id + "']").prop("selected",true);
                        if(val.is_include_tax){
                            $('#arr_is_include_tax' + count).prop( "checked", true);
                        }
                        $('#arr_item' + count).append(`
                            <option value="` + val.item_id + `">` + val.item_name + `</option>
                        `);
                        select2ServerSide('#arr_item' + count, '{{ url("admin/select2/sales_item") }}');
                        $.each(val.sell_units, function(i, value) {
                            $('#arr_unit' + count).append(`
                                <option value="` + value.id + `" data-conversion="` + value.conversion + `">` + value.code + `</option>
                            `);
                        });
                        $('#arr_unit' + count).val(val.item_unit_id);
                        $('#rowQty' + count).trigger('keyup');
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

    function countRow(id){
        var qty = parseFloat($('#rowQty' + id).val().replaceAll(".", "").replaceAll(",",".")),
            conversion = parseFloat($('#arr_unit' + id).find(':selected').data('conversion').toString()),
            qtylimit = parseFloat($('#rowQty' + id).data('qty').toString().replaceAll(".", "").replaceAll(",",".")), 
            price = parseFloat($('#rowPrice' + id).val().replaceAll(".", "").replaceAll(",",".")),
            disc1 = parseFloat($('#rowDisc1' + id).val().replaceAll(".", "").replaceAll(",",".")), 
            disc2 = parseFloat($('#rowDisc2' + id).val().replaceAll(".", "").replaceAll(",",".")), 
            disc3 = parseFloat($('#rowDisc3' + id).val().replaceAll(".", "").replaceAll(",","."));

        qtylimit = (qtylimit / conversion).toFixed(3);
        var qtykonversi = qty * conversion.toFixed(3);
        if(qtylimit > 0){
            if(qty > qtylimit){
                qty = qtylimit;
                $('#rowQty' + id).val(formatRupiahIni(parseFloat(qty).toFixed(3).toString().replace('.',',')));
            }
        }
        $('#arr_konversi' + id).text(formatRupiahIni(parseFloat(qtykonversi).toFixed(3).toString().replace('.',',')) + ' m2');
        price = price;

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
        var subtotal = 0, tax = 0, total = 0, grandtotal = 0, rounding = parseFloat($('#rounding').val().replaceAll(".", "").replaceAll(",",".")), total_after_tax = 0;

        $('input[name^="arr_total"]').each(function(index){
			subtotal += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
		});

        $('input[name^="arr_total"]').each(function(index){
            let rownominal = parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));

            let percent_tax = parseFloat($('select[name^="arr_tax"]').eq(index).val());
            if($('input[name^="arr_is_include_tax"]').eq(index).is(':checked')){
                rownominal = rownominal / (1 + (percent_tax / 100));
            }
            tax += rownominal * (percent_tax / 100);
        });

        total = subtotal;
        
        tax = Math.floor(tax);

        total_after_tax = total + tax;

        grandtotal = total_after_tax + rounding;

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
            /* M.toast({
                html: 'Aplikasi penghubung printer tidak terinstall. Silahkan hubungi tim EDP.'
            }); */
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
                    title : 'Alamat penagihan',
                    element : document.querySelector('.step4'),
                    intro : 'Silahkan pilih alamat penagihan yang diambil dari master data partner bisnis pada detail alamat penagihan.' 
                },
                {
                    title : 'Perusahaan',
                    element : document.querySelector('.step5'),
                    intro : 'Perusahaan dimana dokumen ini dibuat.' 
                },
                {
                    title : 'Tgl. Posting',
                    element : document.querySelector('.step7'),
                    intro : 'Tanggal post akan menentukan tanggal jurnal untuk beberapa form yang terhubung dengan jurnal. Hati - hati dalam menentukan tanggal posting.' 
                },
                {
                    title : 'Tgl. Valid SO',
                    element : document.querySelector('.step8'),
                    intro : 'Tanggal berlaku SO / Penawaran, set sesuai dengan masa berlaku yang diinginkan.' 
                },
                {
                    title : 'Proyek (Jika ada)',
                    element : document.querySelector('.step9'),
                    intro : 'Silahkan pilih proyek ini jika penjualan ingin dihubungkan dengan data Proyek. Data proyek bisa ditambahkan pada form Master Data - Administrasi - Proyek.' 
                },
                {
                    title : 'No. Referensi',
                    element : document.querySelector('.step10'),
                    intro : 'No referensi bisa diisikan dengan no dokumen PO dari customer atau dokumen terkait lainnya yang mendukung penjualan ini.' 
                },
                {
                    title : 'Tipe Pengiriman',
                    element : document.querySelector('.step11'),
                    intro : 'Ada 2 macam tipe pengiriman, yakni yang pertama adalah Franco adalah biaya pengiriman barang dibebankan pada penjual. Sedangkan Loco, adalah kebalikan dari Franco, dimana biaya pengiriman barang dibebankan kepada customer.'
                },
                {
                    title : 'Ekspedisi',
                    element : document.querySelector('.step12'),
                    intro : 'Ekspedisi adalah pihak partner bisnis tipe pengirim, silahkan tambahkan jika tidak ada, di Menu Master Data - Organisasi - Partner Bisnis.' 
                },
                {
                    title : 'Tipe Transport',
                    element : document.querySelector('.step13'),
                    intro : 'Tipe kendaraan yang digunakan dalam pengiriman barang nantinya.' 
                },
                {
                    title : 'Tgl. Kirim',
                    element : document.querySelector('.step14'),
                    intro : 'Tanggal perkiraan pengiriman barang dari gudang.' 
                },
                {
                    title : 'Outlet',
                    element : document.querySelector('.step15'),
                    intro : 'Tempat tujuan barang akan dikirimkan dalam bentuk toko / supermarket / distributor.' 
                },
                {
                    title : 'Alamat Tujuan',
                    element : document.querySelector('.step16'),
                    intro : 'Alamat tujuan adalah alamat dimana barang ingin dikirimkan.' 
                },
                {
                    title : 'Provinsi',
                    element : document.querySelector('.step17'),
                    intro : 'Provinsi dimana barang ingin dikirimkan (berdasarkan alamat tujuan).' 
                },
                {
                    title : 'Kota',
                    element : document.querySelector('.step18'),
                    intro : 'Kota dimana barang ingin dikirimkan (berdasarkan alamat tujuan).' 
                },
                {
                    title : 'Kecamatan',
                    element : document.querySelector('.step19'),
                    intro : 'Kecamatan dimana barang ingin dikirimkan (berdasarkan alamat tujuan).' 
                },
                {
                    title : 'Kelurahan',
                    element : document.querySelector('.step20'),
                    intro : 'Kelurahan dimana barang ingin dikirimkan (berdasarkan alamat tujuan).' 
                },
                {
                    title : 'Tipe Pembayaran',
                    element : document.querySelector('.step21'),
                    intro : 'Tipe pembayaran SO. Untuk Cash, maka TOP Internal dan TOP Customer akan menjadi 0. Untuk, tipe Credit, maka TOP Internal dan TOP Customer bisa diedit.' 
                },
                {
                    title : 'TOP (Term of Payment) Internal',
                    element : document.querySelector('.step22'),
                    intro : 'Tenggat pembayaran internal dalam satuan hari, untuk Finance.'
                },
                {
                    title : 'TOP (Term of Payment) Customer',
                    element : document.querySelector('.step23'),
                    intro : 'Tenggat pembayaran customer dalam satuan hari.'
                },
                {
                    title : 'Mata Uang',
                    element : document.querySelector('.step25'),
                    intro : 'Mata uang, silahkan pilih mata uang lain, untuk mata uang asing.' 
                },
                {
                    title : 'Konversi',
                    element : document.querySelector('.step26'),
                    intro : 'Nilai konversi rupiah pada saat dokumen dibuat. Nilai konversi secara otomatis diisi ketika form tambah baru dibuka pertama kali dan data diambil dari situs exchangerate.host. Pastikan kode mata uang benar di master data agar nilai konversi tidak error.'
                },
                {
                    title : 'Persen DP',
                    element : document.querySelector('.step27'),
                    intro : 'Persen Down Payment yang akan menjadi acuan pengecekan credit limit Customer pada saat barang akan dijadwalkan pengirimannya. Silahkan isikan 0, jika tagihan akan dibayarkan secara kredit dan pengecekan akan didasarkan pada limit credit Customer. Silahkan isikan 100 jika tagihan adalah dibayarkan dengan 100% down payment.'
                },
                {
                    title : 'File Lampiran',
                    element : document.querySelector('.step28'),
                    intro : 'Silahkan unggah file lampiran. untuk saat ini hanya bisa mengakomodir 1 file lampiran saja. Jika ingin menambahkan file lebih dari 1, silahkan gabungkan file anda menjadi pdf.' 
                },
                {
                    title : 'Sales',
                    element : document.querySelector('.step29'),
                    intro : 'Inputan ini digunakan untuk mengatur sales terkait dengan penjualan. Data diambil dari Partner Bisnis tipe Karyawan / Pegawai.' 
                },
                {
                    title : 'Detail produk',
                    element : document.querySelector('.step30'),
                    intro : 'Silahkan tambahkan produk anda disini, lengkap dengan keterangan detail tentang produk tersebut. Hati-hati dalam menentukan Plant, dan Gudang Tujuan, karena itu nantinya akan menentukan dimana barang ketika diterima.' 
                },
                {
                    title : 'Tambah Baris',
                    element : document.querySelector('.step31'),
                    intro : 'Untuk menambahkan baris produk yang ingin diinput silahkan tekan tombol ini.' 
                },
                {
                    title : 'Keterangan Internal',
                    element : document.querySelector('.step32'),
                    intro : 'Silahkan isi / tambahkan keterangan internal untuk dokumen ini untuk catatan antar departemen (internal perusahaan) saja.' 
                },
                {
                    title : 'Keterangan Eksternal',
                    element : document.querySelector('.step33'),
                    intro : 'Silahkan isi / tambahkan keterangan eksternal untuk dokumen ini dan kepentingan luar perusahaan.' 
                },
                {
                    title : 'Informasi Total',
                    element : document.querySelector('.step34'),
                    intro : 'Nominal diskon, untuk diskon yang ingin dimunculkan di dalam dokumen ketika dicetak. Sedangkan untuk Rounding akan menambah atau mengurangi nilai grandtotal sesuai inputan pengguna.' 
                },
                {
                    title : 'Tombol Simpan',
                    element : document.querySelector('.step35'),
                    intro : 'Silahkan tekan tombol ini untuk menyimpan data, namun pastikan data yang akan anda masukkan benar.' 
                },
            ]
        }).start();
    }

    function duplicate(id){
        swal({
            title: "Apakah anda yakin ingin salin?",
            text: "Pastikan item yang ingin anda salin sudah sesuai!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
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
                        $('#account_id').empty();
                        $('#account_id').append(`
                            <option value="` + response.account_id + `">` + response.account_name + `</option>
                        `);
                        $('#company_id').val(response.company_id).formSelect();
                        $('#post_date').val(response.post_date);
                        $('#valid_date').val(response.valid_date);
                        $('#document_no').val(response.document_no);
                        $('#type_delivery').val(response.type_delivery).formSelect();
                        $('#sender_id').empty().append(`<option value="` + response.sender_id + `">` + response.sender_name + `</option>`);
                        $('#delivery_date').val(response.delivery_date);
                        $('#shipment_address').val(response.shipment_address);
                        $('#billing_address').empty();
                        $.each(response.user_data, function(i, val) {
                            $('#billing_address').append(`
                                <option value="` + val.id + `" ` + (val.id == response.user_data_id ? 'selected' : '') + `>` + val.title + ` ` + val.content + `</option>
                            `);
                        });
                        $('#destination_address').val(response.destination_address);
                        $('#province_id').empty().append(`<option value="` + response.province_id + `">` + response.province_name + `</option>`);
                        $('#subdistrict_id,#district_id,#city_id').empty().append(`
                            <option value="">--{{ __('translations.select') }}--</option>
                        `);
                        $('#project_id').empty();
                        if(response.project_name){
                            $('#project_id').append(`
                                <option value="` + response.project_id + `">` + response.project_name + `</option>
                            `);
                        }
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
                            $.each(response.cities[index].district, function(i, value) {
                                let selected = '';
                                $('#district_id').append(`
                                    <option value="` + value.id + `" ` + (value.id == response.district_id ? 'selected' : '') + ` data-subdistrict='` + JSON.stringify(value.subdistrict) + `'>` + value.name + `</option>
                                `);
                                if(value.id == response.district_id){
                                    subdistrict = value.subdistrict;
                                }
                            });

                            $.each(subdistrict, function(i, value) {
                                $('#subdistrict_id').append(`
                                    <option value="` + value.id + `" ` + (value.id == response.subdistrict_id ? 'selected' : '') + `>` + value.name + `</option>
                                `);
                            });
                        }
                        $('#payment_type').val(response.payment_type).formSelect();
                        $('#top_internal').val(response.top_internal);
                        $('#top_customer').val(response.top_customer);
                        $('#is_guarantee').val(response.is_guarantee).formSelect();
                        $('#currency_id').val(response.currency_id).formSelect();
                        $('#currency_rate').val(response.currency_rate);
                        $('#percent_dp').val(response.percent_dp);
                        $('#sales_id').empty().append(`<option value="` + response.sales_id + `">` + response.sales_name + `</option>`);
                        $('#note_internal').val(response.note_internal);
                        $('#note_external').val(response.note_external);
                        
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
                                        <input type="hidden" name="arr_tax_nominal[]" id="arr_tax_nominal` + count + `" value="` + val.tax + `">
                                        <input type="hidden" name="arr_grandtotal[]" id="arr_grandtotal` + count + `" value="` + val.grandtotal + `">
                                        <td>
                                            <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                                        </td>
                                        <td class="center-align">
                                            <select class="browser-default" id="arr_place` + count + `" name="arr_place[]">
                                                @foreach ($place as $rowplace)
                                                    <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="right-align" id="arr_qty_now` + count + `">` + val.item_stock_qty + `</td>
                                        <td class="right-align" id="arr_qty_temporary` + count + `">` + val.item_stock_qty + `</td>
                                        <td class="center-align" id="arr_uom_unit` + count + `">` + val.uom + `</td>
                                        <td>
                                            <input name="arr_qty[]" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" data-qty="0" style="text-align:right;width:100px;" id="rowQty`+ count +`">
                                        </td>
                                        
                                        <td class="center">
                                            <select class="browser-default" id="arr_unit` + count + `" name="arr_unit[]" onchange="countRow('` + count + `');"></select>
                                        </td>
                                        <td class="center">
                                            <div name="arr_konversi[]"  style="text-align:right;" id="arr_konversi`+ count +`">
                                        </td>
                                        <td class="center">
                                            <input list="tempPrice` + count + `" name="arr_price[]" class="browser-default" type="text" value="` + val.price + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowPrice`+ count +`">
                                            <datalist id="tempPrice` + count + `"></datalist>
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
                                $('#arr_place' + count).val(val.place_id);
                                $("#arr_tax" + count + " option[data-id='" + val.tax_id + "']").prop("selected",true);
                                if(val.is_include_tax){
                                    $('#arr_is_include_tax' + count).prop( "checked", true);
                                }
                                $('#arr_item' + count).append(`
                                    <option value="` + val.item_id + `">` + val.item_name + `</option>
                                `);
                                select2ServerSide('#arr_item' + count, '{{ url("admin/select2/sales_item") }}');
                                $.each(val.sell_units, function(i, value) {
                                    $('#arr_unit' + count).append(`
                                        <option value="` + value.id + `" data-conversion="` + value.conversion + `">` + value.code + `</option>
                                    `);
                                });
                                $('#arr_unit' + count).val(val.item_unit_id);
                            });
                        }
                        
                        $('.modal-content').scrollTop(0);
                        $('#note').focus();
                        M.updateTextFields();

                        $('#code_place_id').val(response.code_place_id).formSelect().trigger('change');
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
        });
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

    function done(id){
        var msg = '';
        swal({
            title: "Apakah anda yakin ingin menyelesaikan dokumen ini?",
            text: "Data yang sudah terupdate tidak dapat dikembalikan.",
            icon: 'warning',
            dangerMode: true,
            buttons: true,
            content: "input",
        })
        .then(message => {
            if (message != "" && message != null) {
                $.ajax({
                    url: '{{ Request::url() }}/done',
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        id: id,
                        msg : message
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        loadingOpen('#main');
                    },
                    success: function(response) {
                        loadingClose('#main');
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
        });
    }
    /* function exportExcel(){
        var search = table.search();
        var status = $('#filter_status').val();
        var type_buy = $('#filter_inventory').val();
        var type_deliv = $('#filter_delivery').val();
        var company = $('#filter_company').val();
        var type_pay = $('#filter_payment').val();
        var supplier = $('#filter_account').val();
        var sender = $('#filter_sender').val();
        var sales = $('#filter_sales').val();
        var currency = $('#filter_currency').val();
        var start_date = $('#start_date').val();
        var end_date = $('#finish_date').val();
        var modedata = '{{ $modedata }}';

        window.location = "{{ Request::url() }}/export_from_page?search=" + search + "&status=" + status + "&type_buy=" + type_buy + "&type_deliv=" + type_deliv + "&company=" + company + "&type_pay=" + type_pay + "&supplier=" + supplier + "&currency=" + currency + "&end_date=" + end_date + "&start_date=" + start_date + "&modedata=" + modedata;
       
    } */
</script>