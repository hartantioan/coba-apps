<style>
    .modal {
        top:0px !important;
    }

    table > thead > tr > th {
        font-size: 13px !important;
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
                                                <label for="filter_account" style="font-size:1rem;">Ekspedisi :</label>
                                                <div class="input-field">
                                                    <select class="browser-default" id="filter_account" name="filter_account" multiple="multiple" style="width:100% !important;" onchange="loadDataTable()"></select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_marketing_order_delivery" style="font-size:1rem;">Jadwal Kirim:</label>
                                                <div class="input-field">
                                                    <select class="browser-default" id="filter_marketing_order_delivery" name="filter_marketing_order_delivery" multiple="multiple" style="width:100% !important;" onchange="loadDataTable()"></select>
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
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">Refresh</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <table id="datatable_serverside">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Code</th>
                                                        <th>Petugas</th>
                                                        <th>Customer</th>
                                                        <th>Perusahaan</th>
                                                        <th>Ekspedisi</th>
                                                        <th>Sales Order</th>
                                                        <th>MOD</th>
                                                        <th>Tgl.Post</th>
                                                        <th>Nama Supir</th>
                                                        <th>No.HP/WA Supir</th>
                                                        <th>Tipe Kendaraan</th>
                                                        <th>Nopol Kendaraan</th>
                                                        <th>Catatan Internal</th>
                                                        <th>Catatan Eksternal</th>
                                                        <th>Tgl.Kembali SJ</th>
                                                        <th>Bukti Kembali</th>
                                                        <th>Alamat Tujuan</th>
                                                        <th>Tracking</th>
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
                                        <input type="hidden" id="tempSwitch" name="tempSwitch">
                                        <select class="browser-default" id="marketing_order_delivery_id" name="marketing_order_delivery_id" onchange="getMarketingOrderDelivery()"></select>
                                        <label class="active" for="marketing_order_delivery_id">Jadwal Kirim</label>
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
                                        <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);">
                                        <label class="active" for="post_date">Tgl. Posting</label>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col s12">
                                <fieldset>
                                    <legend>2. Pengiriman</legend>
                                    <div class="input-field col m12 s12 row">
                                        <h6><b>Info Pengiriman MOD</b></h6>
                                        <div class="input-field col m3 s12">
                                            <span id="info-sender">-</span>
                                            <label class="active" for="">Broker</label>
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <span id="info-outlet">-</span>
                                            <label class="active" for="">Outlet</label>
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <span id="info-address">-</span>
                                            <label class="active" for="">Alamat</label>
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <span id="info-province">-</span>
                                            <label class="active" for="">Provinsi</label>
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <span id="info-city">-</span>
                                            <label class="active" for="">Kota/Kabupaten</label>
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <span id="info-district">-</span>
                                            <label class="active" for="">Kecamatan</label>
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <span id="info-subdistrict">-</span>
                                            <label class="active" for="">Kelurahan</label>
                                        </div>
                                    </div>
                                    <div class="input-field col m3 s12 step6">
                                        <select class="browser-default select2" id="user_driver_id" name="user_driver_id" onchange="getDriverInformation();">
                                            <option value="">--Silakan pilih MOD--</option>
                                        </select>
                                        <label class="active" for="user_driver_id">Daftar Supir</label>
                                    </div>
                                    <div class="input-field col m3 s12 step7">
                                        <input id="driver_name" name="driver_name" type="text" placeholder="Nama supir">
                                        <label class="active" for="driver_name">Nama Supir</label>
                                    </div>
                                    <div class="input-field col m3 s12 step8">
                                        <input id="driver_hp" name="driver_hp" type="text" placeholder="HP/WA Supir">
                                        <label class="active" for="driver_hp">HP/WA Supir</label>
                                    </div>
                                    <div class="input-field col m3 s12 step9">
                                        <input id="vehicle_name" name="vehicle_name" type="text" placeholder="Tipe Kendaraan">
                                        <label class="active" for="vehicle_name">Tipe Kendaraan</label>
                                    </div>
                                    <div class="input-field col m3 s12 step10">
                                        <input id="vehicle_no" name="vehicle_no" type="text" placeholder="Nopol Kendaraan">
                                        <label class="active" for="vehicle_no">Nopol Kendaraan</label>
                                    </div>
                                    <div class="input-field col m9 s12">
                                        Silahkan pilih <b>Kosong</b> di daftar supir dan isikan manual nama supir dan HP/WA supir jika data supir tidak ditemukan. Atau anda bisa menambahkannya terlebih dahulu di form <b>Master Data - Organisasi - Partner Bisnis</b>.
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col s12">
                                <fieldset>
                                    <legend>3. Jadwal Kirim Terpakai</legend>
                                    <div class="col m3 s12 step11">
                                        <h6>Hapus untuk bisa diakses pengguna lain : <i id="list-used-data"></i></h6>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col s12 step12">
                                <fieldset style="min-width: 100%;">
                                    <legend>4. Produk Detail</legend>
                                    <div class="col m12 s12" style="overflow:auto;width:100% !important;" id="table-item">
                                        <p class="mt-2 mb-2">
                                            <table class="bordered" id="table-detail">
                                                <thead>
                                                    <tr>
                                                        <th class="center">Item</th>
                                                        <th class="center">Dari Plant</th>
                                                        <th class="center">Qty Pesanan</th>
                                                        <th class="center">Satuan</th>
                                                        <th class="center">Keterangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="body-item">
                                                    <tr id="last-row-item">
                                                        <td colspan="5">
                                                            Silahkan pilih Jadwal Kirim...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </p>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="input-field col m4 s12 step13">
                                <textarea class="materialize-textarea" id="note_internal" name="note_internal" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note_internal">Keterangan Internal</label>
                            </div>
                            <div class="input-field col m4 s12 step14">
                                <textarea class="materialize-textarea" id="note_external" name="note_external" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note_external">Keterangan Eksternal</label>
                            </div>
                            <div class="input-field col m4 s12">

                            </div>
                            <div class="input-field col m4 s12">
                                
                            </div>
                            <div class="col s12 mt-3">
                                <button class="btn waves-effect waves-light right submit step15" onclick="save();">Simpan <i class="material-icons right">send</i></button>
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
            <table class="bordered Highlight striped">
                <thead>
                        <tr>
                            <th class="center-align">No</th>
                            <th class="center-align">Coa</th>
                            <th class="center-align">Partner Bisnis</th>
                            <th class="center-align">Plant</th>
                            <th class="center-align">Line</th>
                            <th class="center-align">Mesin</th>
                            <th class="center-align">Department</th>
                            <th class="center-align">Gudang</th>
                            <th class="center-align">Proyek</th>
                            <th class="center-align">Keterangan</th>
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

<div id="modal7" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="show_tracking">
                <div class="col l2 m2 s2 p-3">
                    <div class="card z-depth-0 grey lighten-4 border-radius-6">
                        <div class="card-image">
                            <img src="{{ url('website/document.png') }}" class="responsive-img" id="imageTracking1" alt="" style="filter:grayscale(100%);">
                        </div>
                        <div class="card-content center-align">
                            Dokumen SJ telah dibuat.
                            <p class="teal-text lighten-2 truncate" id="dateTracking1">-</p>
                        </div>
                    </div>
                </div>
                <div class="col l2 m2 s2 p-3">
                    <div class="card z-depth-0 grey lighten-4 border-radius-6">
                        <div class="card-image">
                            <img src="{{ url('website/delivery.png') }}" class="responsive-img" id="imageTracking2" alt="" style="filter:grayscale(100%);">
                        </div>
                        <div class="card-content center-align">
                            Barang telah dikirimkan.
                            <p class="teal-text lighten-2 truncate" id="dateTracking2">-</p>
                        </div>
                    </div>
                </div>
                <div class="col l2 m2 s2 p-3">
                    <div class="card z-depth-0 grey lighten-4 border-radius-6">
                        <div class="card-image">
                            <img src="{{ url('website/arrive.png') }}" class="responsive-img" id="imageTracking3" alt="" style="filter:grayscale(100%);">
                        </div>
                        <div class="card-content center-align">
                            Barang tiba di customer.
                            <p class="teal-text lighten-2 truncate" id="dateTracking3">-</p>
                        </div>
                    </div>
                </div>
                <div class="col l2 m2 s2 p-3">
                    <div class="card z-depth-0 grey lighten-4 border-radius-6">
                        <div class="card-image">
                            <img src="{{ url('website/unload.png') }}" class="responsive-img" id="imageTracking4" alt="" style="filter:grayscale(100%);">
                        </div>
                        <div class="card-content center-align">
                            Barang selesai dibongkar.
                            <p class="teal-text lighten-2 truncate" id="dateTracking4">-</p>
                        </div>
                    </div>
                </div>
                <div class="col l2 m2 s2 p-3">
                    <div class="card z-depth-0 grey lighten-4 border-radius-6">
                        <div class="card-image">
                            <img src="{{ url('website/returned.png') }}" class="responsive-img" id="imageTracking5" alt="" style="filter:grayscale(100%);">
                        </div>
                        <div class="card-content center-align">
                            Surat Jalan telah kembali.
                            <p class="teal-text lighten-2 truncate" id="dateTracking5">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col s5">
                <h6>Update Tracking {{ $title }}</h6>
                <form class="row" id="form_data_tracking" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert_tracking" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="row">
                            <div class="col s12">
                                <div class="input-field col m6 s12">
                                    <input type="hidden" name="tempTracking" id="tempTracking">
                                    <select class="browser-default" id="status_tracking" name="status_tracking">
                                        <option value="1">Dokumen SJ telah dibuat</option>
                                        <option value="2">Barang telah dikirimkan</option>
                                        <option value="3">Barang tiba di customer</option>
                                        <option value="4">Barang selesai dibongkar</option>
                                        <option value="5">Surat Jalan telah kembali</option>
                                    </select>
                                    <label class="active" for="status_tracking">Status Tracking</label>
                                </div>
                                <div class="file-field input-field col m6 s12">
                                    <button class="btn waves-effect waves-light teal submit" onclick="saveTracking();">Simpan <i class="material-icons right">send</i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col s7">
                <h6>Update {{ $title }} Kembali</h6>
                <form class="row" id="form_data_return" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert_return" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="row">
                            <div class="col s12">
                                <div class="input-field col m4 s12">
                                    <input id="post_date_return" name="post_date_return" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                    <label class="active" for="post_date_return">Tgl.Kembali</label>
                                </div>
                                <div class="file-field input-field col m4 s12">
                                    <div class="btn">
                                        <span>Dokumen SJ</span>
                                        <input type="file" name="document" id="document" accept="image/*,.pdf">
                                    </div>
                                    <div class="file-path-wrapper">
                                        <input class="file-path validate" type="text">
                                    </div>
                                </div>
                                <div class="file-field input-field col m4 s12">
                                    <button class="btn waves-effect waves-light teal submit" onclick="saveReturn();">Simpan <i class="material-icons right">send</i></button>
                                </div>
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
        $("#table-detail th").resizable({
            minWidth: 100,
        });

        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        $('#datatable_serverside').on('click', 'button,a', function(event) {
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
            },
            onOpenEnd: function(modal, trigger) { 
                $('#driver_name').focus();
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
                $('#temp,#tempSwitch').val('');
                $('#marketing_order_delivery_id').empty();
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }
                M.updateTextFields();
                window.onbeforeunload = function() {
                    return null;
                };
                $('#user_driver_id').empty().append(`<option value="">--Silakan pilih MOD--</option>`);
                $('#driver_name,#driver_hp').prop("readonly", false);
                $('#info-sender').text('-');
                $('#info-outlet').text('-');
                $('#info-address').text('-');
                $('#info-province').text('-');
                $('#info-city').text('-');
                $('#info-district').text('-');
                $('#info-subdistrict').text('-');
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

        $('#modal7').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
                $('#status_tracking option:not([disabled]):first').attr("selected",true);
            },
            onCloseEnd: function(modal, trigger){
                $('#tempTracking').val('');
                $("#status_tracking option").attr("disabled",false);
                $("#status_tracking option").attr("selected",false);
                $('#form_data_return')[0].reset();
                for (let i = 1; i <= 5; i++) {
                    $('#imageTracking' + i).css("filter", "grayscale(100%)");
                    $('#dateTracking' + i).text('-');
                }
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

        select2ServerSide('#account_id,#filter_account', '{{ url("admin/select2/supplier_vendor") }}');
        select2ServerSide('#marketing_order_delivery_id,#filter_marketing_order_delivery', '{{ url("admin/select2/marketing_order_delivery") }}');
    });

    function getMarketingOrderDelivery(){
        if($('#marketing_order_delivery_id').val()){
            $.ajax({
                url: '{{ Request::url() }}/get_marketing_order_delivery',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: $('#marketing_order_delivery_id').val(),
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

                        $('#marketing_order_delivery_id').empty();
                    }else{
                        $('#post_date').val(response.delivery_date);
                        $('#info-sender').text(response.sender);
                        $('#info-outlet').text(response.outlet);
                        $('#info-address').text(response.address);
                        $('#info-province').text(response.province);
                        $('#info-city').text(response.city);
                        $('#info-district').text(response.district);
                        $('#info-subdistrict').text(response.subdistrict);
                        $('#note_internal').val(response.note_internal);
                        $('#note_external').val(response.note_external);

                        if(response.drivers.length > 0){
                            $('#user_driver_id').empty().append(`
                                <option value="">--Kosong / Tambah baru--</option>
                            `);
                            $.each(response.drivers, function(i, val) {
                                $('#user_driver_id').append(`
                                    <option value="` + val.id + `" data-driver="` + val.name + `" data-hp="` + val.hp + `">` + val.name + ` - ` + val.hp + `</value>
                                `);
                            });
                            $('#user_driver_id').trigger('change');
                        }else{
                            $('#user_driver_id').empty().append(`
                                <option value="">--Data supir tidak ditemukan--</option>
                            `);
                        }

                        if(response.details.length > 0){
                            $('#body-item').empty();
                            $('#list-used-data').append(`
                                <div class="chip purple darken-4 gradient-shadow white-text">
                                    ` + response.code + `
                                    <i class="material-icons close data-used" onclick="removeUsedData('` + response.id + `')">close</i>
                                </div>
                            `);
                            $.each(response.details, function(i, val) {
                                var count = makeid(10);
                                
                                $('#body-item').append(`
                                    <tr class="row_item" data-id="` + response.id + `">
                                        <td>
                                            ` + val.item_name + `
                                        </td>
                                        <td id="arr_warehouse_name` + count + `">
                                            ` + val.place_name + ` - ` + val.warehouse_name + `
                                        </td>
                                        <td class="center-align">
                                            ` + val.qty + `
                                        </td>
                                        <td class="center-align">
                                            <span id="arr_unit` + count + `">` + val.unit + `</span>
                                        </td>
                                        <td>
                                            ` + val.note + `
                                        </td>
                                    </tr>
                                `);
                            });
                        }
                    }
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
            $('#user_driver_id').empty().append(`
                <option value="">--Kosong / Tambah baru--</option>
            `);
            $('#post_date').val('{{ date("Y-m-d") }}');
            $('#info-sender,#info-outlet,#info-address,#info-province,#info-city,#info-district,#info-subdistrict').text('-');
        }
    }

    function getDriverInformation(){
        if($('#user_driver_id').val()){
            $('#driver_name').val($("#user_driver_id").select2('data')[0].element.attributes['data-driver'].value).prop("readonly", true);
            $('#driver_hp').val($("#user_driver_id").select2('data')[0].element.attributes['data-hp'].value).prop("readonly", true);
        }else{
            $('#driver_name,#driver_hp').val('').prop("readonly", false);
        }
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

    function getTracking(val){
        if(val){
            $.ajax({
                url: '{{ Request::url() }}/get_tracking',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    code: val,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    $('#modal7').modal('open');
                    loadingClose('.modal-content');
                    $('#tempTracking').val(val);
                    $.each(response.tracking, function(i, val) {
                        $("#status_tracking option[value='"+ val.status + "']").attr("disabled","disabled");
                        $('#imageTracking' + val.status).css("filter", "");
                        $('#dateTracking' + val.status).text(val.date);
                    });
                    $('#status_tracking option:not([disabled]):first').attr("selected",true);
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

    function saveTracking(){
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
                var formData = new FormData($('#form_data_tracking')[0]), passed = true;
                
                $.ajax({
                    url: '{{ Request::url() }}/update_tracking',
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
                        $('#validation_alert_tracking').hide();
                        $('#validation_alert_tracking').html('');
                        loadingOpen('.modal-content');
                    },
                    success: function(response) {
                        loadingClose('.modal-content');
                        if(response.status == 200) {
                            $('#form_data_tracking')[0].reset();
                            M.toast({
                                html: response.message
                            });
                            $("#status_tracking option[value='"+ response.param + "']").attr("disabled","disabled");
                            $('#status_tracking option:not([disabled]):first').attr("selected",true);
                            $('#imageTracking' + response.param).css("filter", "");
                            $('#dateTracking' + response.param).text(response.date);
                        } else if(response.status == 422) {
                            $('#validation_alert_tracking').show();
                            $('.modal-content').scrollTop(0);
                            
                            swal({
                                title: 'Ups! Validation',
                                text: 'Check your form.',
                                icon: 'warning'
                            });

                            $.each(response.error, function(i, val) {
                                $.each(val, function(i, val) {
                                    $('#validation_alert_tracking').append(`
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

    function saveReturn(){
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
                var formData = new FormData($('#form_data_return')[0]);

                formData.append('tempTracking',$('#tempTracking').val());
                
                $.ajax({
                    url: '{{ Request::url() }}/update_return',
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
                        $('#validation_alert_return').hide();
                        $('#validation_alert_return').html('');
                        loadingOpen('.modal-content');
                    },
                    success: function(response) {
                        loadingClose('.modal-content');
                        if(response.status == 200) {
                            $('#form_data_return')[0].reset();
                            M.toast({
                                html: response.message
                            });
                            $("#status_tracking option[value='"+ response.param + "']").attr("disabled","disabled");
                            $('#status_tracking option:not([disabled]):first').attr("selected",true);
                            $('#imageTracking' + response.param).css("filter", "");
                            $('#dateTracking' + response.param).text(response.date);
                        } else if(response.status == 422) {
                            $('#validation_alert_return').show();
                            $('.modal-content').scrollTop(0);
                            
                            swal({
                                title: 'Ups! Validation',
                                text: 'Check your form.',
                                icon: 'warning'
                            });

                            $.each(response.error, function(i, val) {
                                $.each(val, function(i, val) {
                                    $('#validation_alert_return').append(`
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

    function removeUsedData(id){
        $.ajax({
            url: '{{ Request::url() }}/remove_used_data',
            type: 'POST',
            dataType: 'JSON',
            data: { 
                id : id,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                
            },
            success: function(response) {
                $('.row_item[data-id="' + id + '"]').remove();
                if($('.row_item').length == 0){
                    $('#body-item').empty().append(`
                        <tr id="last-row-item">
                            <td colspan="7">
                                Silahkan pilih Jadwal Kirim...
                            </td>
                        </tr>
                    `);
                    $('#marketing_order_id').empty();
                    $('#delivery_date').val('{{ date("Y-m-d") }}');
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
                    'status[]' : $('#filter_status').val(),
                    'account_id[]' : $('#filter_account').val(),
                    'marketing_order_delivery_id[]' : $('#filter_marketing_order_delivery').val(),
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
                { name: 'code', className: '' },
                { name: 'user_id', className: '' },
                { name: 'customer_id', searchable: false, orderable: false, className: '' },
                { name: 'company_id', className: '' },
                { name: 'account_id', className: '' },
                { name: 'marketing_order_no', searchable: false, orderable: false, className: '' },
                { name: 'marketing_order_delivery_no', searchable: false, orderable: false, className: '' },
                { name: 'post_date', className: '' },
                { name: 'driver_name', className: '' },
                { name: 'driver_no', className: '' },
                { name: 'vehicle_name', className: '' },
                { name: 'vehicle_no', className: '' },
                { name: 'note_internal', className: '' },
                { name: 'note_external', className: '' },
                { name: 'return_date', searchable: false, orderable: false, className: '' },
                { name: 'document', searchable: false, orderable: false, className: '' },
                { name: 'delivery_address', searchable: false, orderable: false, className: '' },
                { name: 'status_tracking', searchable: false, orderable: false, className: 'center-align' },
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
                            $.each(response.error, function(field, errorMessage) {
                                $('#' + field).addClass('error-input');
                                $('#' + field).css('border', '1px solid red');
                                
                            });
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

    function successTracking(){
        loadDataTable();
        $('#modal7').modal('close');
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
                $('#marketing_order_delivery_id').empty();
                $('#marketing_order_delivery_id').append(`
                    <option value="` + response.marketing_order_delivery_id + `">` + response.marketing_order_delivery_code + `</option>
                `);
                $('#company_id').val(response.company_id).formSelect();
                $('#post_date').val(response.post_date);
                $('#note_internal').val(response.note_internal);
                $('#note_external').val(response.note_external);
                $('#vehicle_name').val(response.vehicle_name);
                $('#vehicle_no').val(response.vehicle_no);
                $('#info-sender').text(response.sender);
                $('#info-outlet').text(response.outlet);
                $('#info-address').text(response.address);
                $('#info-province').text(response.province);
                $('#info-city').text(response.city);
                $('#info-district').text(response.district);
                $('#info-subdistrict').text(response.subdistrict);

                if(response.drivers.length > 0){
                    $('#user_driver_id').empty().append(`
                        <option value="">--Kosong / Tambah baru--</option>
                    `);
                    $.each(response.drivers, function(i, val) {
                        $('#user_driver_id').append(`
                            <option value="` + val.id + `" data-driver="` + val.name + `" data-hp="` + val.hp + `">` + val.name + ` - ` + val.hp + `</value>
                        `);
                    });
                    $('#user_driver_id').trigger('change');
                }

                $('#user_driver_id').val(response.user_driver_id).trigger('change');

                $('#driver_name').val(response.driver_name);
                $('#driver_hp').val(response.driver_hp);
                
                if(response.details.length > 0){
                    $('#last-row-item').remove();

                    $('.row_item').each(function(){
                        $(this).remove();
                    });

                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        $('#body-item').append(`
                            <tr class="row_item" data-id="` + response.id + `">
                                <td>
                                    ` + val.item_name + `
                                </td>
                                <td id="arr_warehouse_name` + count + `">
                                    ` + val.place_name + ` - ` + val.warehouse_name + `
                                </td>
                                <td class="center-align">
                                    ` + val.qty + `
                                </td>
                                <td class="center-align">
                                    <span id="arr_unit` + count + `">` + val.unit + `</span>
                                </td>
                                <td>
                                    ` + val.note + `
                                </td>
                            </tr>
                        `);
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
                    data: { id : id, msg : message  },
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
            qtylimit = parseFloat($('#rowQty' + id).data('qty').toString().replaceAll(".", "").replaceAll(",","."));

        if(qtylimit > 0){
            if(qty > qtylimit){
                qty = qtylimit;
                $('#rowQty' + id).val(formatRupiahIni(qty.toFixed(3).toString().replace('.',',')));
            }
        }
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
                    title : 'Surat Jalan',
                    intro : 'Form ini digunakan untuk mengatur surat jalan pengiriman yang memuat informasi supir dan kendaraan. Terdapat fitur tracking status pengiriman, yakni, 1 Dokumen SJ dibuat, 2 Barang telah dikirimkan, 3 Barang tiba dicustomer, 4 Barang selesai dibongkar, 5 SJ telah kembali.'
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
                    title : 'Jadwal Kirim',
                    element : document.querySelector('.step3'),
                    intro : 'Dokumen ini adalah terusan dari Jadwal Kirim, maka silahkan pilih jadwal kirim untuk mendapatkan informasi data item yang ingin dikirimkan.' 
                },
                {
                    title : 'Perusahaan',
                    element : document.querySelector('.step4'),
                    intro : 'Perusahaan dimana dokumen ini dibuat.' 
                },
                {
                    title : 'Tgl. Posting',
                    element : document.querySelector('.step5'),
                    intro : 'Tanggal post akan menentukan tanggal jurnal untuk beberapa form yang terhubung dengan jurnal. Hati - hati dalam menentukan tanggal posting.' 
                },
                {
                    title : 'Daftar Supir',
                    element : document.querySelector('.step6'),
                    intro : 'Daftar supir dari master data Partner Bisnis yang mengikat pada ekspedisi. Silahkan pilih opsi sesuai nama driver jika sudah diset di master data. Jika belum ada, maka pilih kosong / tambah manual / data supir tidak ditemukan.' 
                },
                {
                    title : 'Nama Supir',
                    element : document.querySelector('.step7'),
                    intro : 'Nama Supir akan otomatis terisi jika daftar supir dipilih dan inputan tidak bisa dirubah. Jika daftar supir kosong, maka anda bisa menambahkan nama supir di form ini secara manual (diketik) lalu ketika disimpan akan secara otomatis disimpan ke sistem sesuai ekspedisi terpilih.' 
                },
                {
                    title : 'HP/WA Supir',
                    element : document.querySelector('.step8'),
                    intro : 'HP/WA Supir akan otomatis terisi jika daftar supir dipilih dan inputan tidak bisa dirubah. Jika daftar supir kosong, maka anda bisa menambahkan HP/WA supir di form ini secara manual (diketik) lalu ketika disimpan akan secara otomatis disimpan ke sistem sesuai ekspedisi terpilih.' 
                },
                {
                    title : 'Tipe Kendaraan',
                    element : document.querySelector('.step9'),
                    intro : 'Tipe kendaraan atau nama kendaraan untuk mengangkut barang. Contoh, Hino Deluxe Ban Double' 
                },
                {
                    title : 'Nomor Polisi Kendaraan',
                    element : document.querySelector('.step10'),
                    intro : 'Nomor polisi kendaraan untuk mengangkut barang. Contoh, L 9229 KL.' 
                },
                {
                    title : 'Jadwal Kirim Terpakai',
                    element : document.querySelector('.step11'),
                    intro : 'Silahkan hapus jadwal kirim terpakai agar bisa digunakan di form lainnya. Fitur ini disediakan agar, 1 jadwal kirim hanya bisa diakses di 1 form sehingga mengurangi potensi double data.' 
                },
                {
                    title : 'Produk Detail',
                    element : document.querySelector('.step12'),
                    intro : 'Data yang tampil disini adalah data tarikan dari Sales Order. Disini anda bisa memilih ulang barang diambil dari stok yang mana (kolom Stock), menentukan Qty pesanan sesuai keadaan pengiriman, dan menambahkan keterangan baru.'
                },
                {
                    title : 'Keterangan Internal',
                    element : document.querySelector('.step13'),
                    intro : 'Silahkan isi / tambahkan keterangan internal untuk dokumen ini untuk catatan antar departemen (internal perusahaan) saja.' 
                },
                {
                    title : 'Keterangan Eksternal',
                    element : document.querySelector('.step14'),
                    intro : 'Silahkan isi / tambahkan keterangan eksternal untuk dokumen ini dan kepentingan luar perusahaan.' 
                },
                {
                    title : 'Tombol Simpan',
                    element : document.querySelector('.step15'),
                    intro : 'Silahkan tekan tombol ini untuk menyimpan data, namun pastikan data yang akan anda masukkan benar.' 
                },
            ]
        }).start();
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

    function switchDocument(id){
        $.ajax({
            url: '{{ Request::url() }}/show',
            type: 'POST',
            dataType: 'JSON',
            data: {
                id: id,
                type: 'switch',
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('#main');
            },
            success: function(response) {
                loadingClose('#main');
                if(response.responseStatus == 200){
                    $('#modal1').modal('open');
                    $('#temp').val(id);
                    $('#tempSwitch').val('1');
                    $('#code_place_id').val(response.code_place_id).formSelect();
                    $('#code').val(response.code);
                    $('#marketing_order_delivery_id').empty();
                    $('#marketing_order_delivery_id').append(`
                        <option value="` + response.marketing_order_delivery_id + `">` + response.marketing_order_delivery_code + `</option>
                    `);
                    $('#company_id').val(response.company_id).formSelect();
                    $('#post_date').val(response.post_date);
                    $('#note_internal').val(response.note_internal);
                    $('#note_external').val(response.note_external);
                    $('#vehicle_name').val(response.vehicle_name);
                    $('#vehicle_no').val(response.vehicle_no);
                    $('#info-sender').text(response.sender);
                    $('#info-outlet').text(response.outlet);
                    $('#info-address').text(response.address);
                    $('#info-province').text(response.province);
                    $('#info-city').text(response.city);
                    $('#info-district').text(response.district);
                    $('#info-subdistrict').text(response.subdistrict);

                    if(response.drivers.length > 0){
                        $('#user_driver_id').empty().append(`
                            <option value="">--Kosong / Tambah baru--</option>
                        `);
                        $.each(response.drivers, function(i, val) {
                            $('#user_driver_id').append(`
                                <option value="` + val.id + `" data-driver="` + val.name + `" data-hp="` + val.hp + `">` + val.name + ` - ` + val.hp + `</value>
                            `);
                        });
                        $('#user_driver_id').trigger('change');
                    }

                    $('#user_driver_id').val(response.user_driver_id).trigger('change');

                    $('#driver_name').val(response.driver_name);
                    $('#driver_hp').val(response.driver_hp);
                    
                    if(response.details.length > 0){
                        $('#last-row-item').remove();

                        $('.row_item').each(function(){
                            $(this).remove();
                        });

                        $.each(response.details, function(i, val) {
                            var count = makeid(10);
                            $('#body-item').append(`
                                <tr class="row_item" data-id="` + response.id + `">
                                    <td>
                                        ` + val.item_name + `
                                    </td>
                                    <td id="arr_warehouse_name` + count + `">
                                        ` + val.place_name + ` - ` + val.warehouse_name + `
                                    </td>
                                    <td class="center-align">
                                        ` + val.qty + `
                                    </td>
                                    <td class="center-align">
                                        <span id="arr_unit` + count + `">` + val.unit + `</span>
                                    </td>
                                    <td>
                                        ` + val.note + `
                                    </td>
                                </tr>
                            `);
                        });
                    }
                    
                    $('.modal-content').scrollTop(0);
                    $('#note').focus();
                    M.updateTextFields();
                }else{
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