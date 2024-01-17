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

    video {
        width:100%;
    }

    #previewImage, #previewImage1, #previewImageIn {
        width:100%;
    }

    .select-wrapper, .select2-container {
        height:3.7rem !important;
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
                                            <div class="col m3 s6 ">
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
                                            <div class="col m3 s6 ">
                                                <label for="start_date" style="font-size:1rem;">Tanggal Mulai :</label>
                                                <div class="input-field col s12">
                                                <input type="date" max="{{ date('Y'.'-12-31') }}" id="start_date" name="start_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                            <div class="col m3 s6 ">
                                                <label for="finish_date" style="font-size:1rem;">Tanggal Akhir :</label>
                                                <div class="input-field col s12">
                                                    <input type="date" max="{{ date('Y'.'-12-31') }}" id="finish_date" name="finish_date"  onchange="loadDataTable()">
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
                                    <div class="row mt-2">
                                        <div class="col s12">
                                            <div class="card-alert card green">
                                                <div class="card-content white-text">
                                                    <p>Info : Ada 2 tahapan penimbangan, yakni ketika truk timbang datang, dan truk timbang pulang.</p>
                                                </div>
                                            </div>
                                            {{-- <div class="card-alert card purple">
                                                <div class="card-content white-text">
                                                    <p>Info 2 : Timbangan truk bisa ditambahkan dengan 2 cara, cara yang pertama tarik data dari Purchase Order. Sedangkan cara yang kedua, dengan menambahkan secara manual item / barangnya, namun kemudian setelah tahap 2 penimbangan bisa di linkkan dengan PO agar dokumen bisa ditarik ke GRPO.</p>
                                                </div>
                                            </div>
                                            <div class="card-alert card blue">
                                                <div class="card-content white-text">
                                                    <p>Info 3 : Pada saat timbangan pulang, jika PO sudah ditentukan maka, GRPO akan otomatis terbuat berdasarkan informasi dokumen Timbangan.</p>
                                                </div>
                                            </div> --}}
                                            <div id="datatable_buttons"></div>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">Refresh</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <table id="datatable_serverside" class="display responsive-table wrap">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Code</th>
                                                        <th>Ref.PO</th>
                                                        <th>Pengguna</th>
                                                        <th>Supplier</th>
                                                        <th>Perusahaan</th>
                                                        <th>Tanggal</th>
                                                        <th>No.SJ</th>
                                                        <th>No.Kendaraan</th>
                                                        <th>Supir</th>
                                                        <th>Keterangan</th>
                                                        <th>Dokumen</th>
                                                        <th>Foto Masuk</th>
                                                        <th>Foto Keluar</th>
                                                        <th>Status</th>
                                                        <th>Operasi</th>
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
                <h4>{{ $title }} Datang</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="row">
                            <div class="input-field col m2 s12">
                                <input id="code" name="code" type="text" value="{{ $newcode }}" readonly>
                                <label class="active" for="code">No. Dokumen</label>
                            </div>
                            <div class="input-field col m1 s12">
                                <select class="form-control" id="code_place_id" name="code_place_id" onchange="getCode(this.value);">
                                    <option value="">--Pilih--</option>
                                    @foreach ($place as $rowplace)
                                        <option value="{{ $rowplace->code }}">{{ $rowplace->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="input-field col m3 s12">
                                <input type="hidden" id="temp" name="temp">
                                <select class="browser-default" id="account_id" name="account_id"></select>
                                <label class="active" for="account_id">Supplier</label>
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
                                <select class="form-control" id="place_id" name="place_id">
                                    @foreach ($place as $rowplace)
                                        <option value="{{ $rowplace->id }}">{{ $rowplace->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="place_id">Plant</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. diterima" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);">
                                <label class="active" for="post_date">Tgl. Diterima</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="delivery_no" name="delivery_no" type="text" placeholder="No. Pengiriman">
                                <label class="active" for="delivery_no">Nomor Pengiriman / SJ</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="vehicle_no" name="vehicle_no" type="text" placeholder="No. Kendaraan">
                                <label class="active" for="vehicle_no">Nomor Kendaraan</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="driver" name="driver" type="text" placeholder="Nama Supir">
                                <label class="active" for="driver">Nama Supir</label>
                            </div>
                            <div class="file-field input-field col m3 s12">
                                <div class="btn">
                                    <span>Lampiran Bukti</span>
                                    <input type="file" name="document" id="document">
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text">
                                </div>
                            </div>
                            {{-- <div class="col m12 s12">
                                <div class="col m6 s6">
                                    <p class="mt-2 mb-2">
                                        <h6>Dari Purchase Order (Jika ada informasi)</h6>
                                        <div class="row">
                                            <div class="input-field col m6 s7">
                                                <select class="browser-default" id="purchase_order_id" name="purchase_order_id">&nbsp;</select>
                                            </div>
                                            <div class="col m6 s6 mt-4">
                                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="getPurchaseOrder();" href="javascript:void(0);">
                                                    <i class="material-icons left">add</i> Tambah PO
                                                </a>
                                            </div>
                                        </div>
                                    </p>
                                </div>
                                <div class="col m6 s6">
                                    <p class="mt-2 mb-2">
                                        <h6>Dari AI</h6>
                                        <div class="row">
                                            <div class="input-field col m6 s7">
                                                <b>Untuk mencari PO terkait sesuai dengan <i>Supplier</i> dan <i>Plant</i> yang dipilih diurutkan berdasarkan qty gantungan PO terbesar, dan kode PO.</b>
                                            </div>
                                            <div class="col m6 s6 mt-4">
                                                <a class="waves-effect waves-light pink darken-1 btn-small mb-1 mr-1" onclick="getPurchaseOrderAi();" href="javascript:void(0);">
                                                    <i class="material-icons left">find_replace</i> Proses dengan AI
                                                </a>
                                            </div>
                                        </div>
                                    </p>
                                </div>                                
                            </div> --}}
                            <div class="col m12 s12">
                                <div class="input-field col m4 s12 select">
                                    <select id="videoSource" name="videoSource" class="browser-default"></select>
                                    <label for="videoSource" class="active">Sumber Kamera: </label>
                                    <div class="mt-3 center">
                                        <a class="btn waves-effect waves-light" href="javascript:void(0)" id="takePhoto">Ambil Gambar <i class="material-icons right">add_a_photo</i></a>
                                    </div>
                                </div>
                                <div class="input-field col m4 s12">
                                    <h6>Layar Kamera :</h6>
                                    <video id="video" autoplay muted playsinline></video>
                                </div>
                                <div class="input-field col m4 s12">
                                    <h6>Hasil Foto :</h6>
                                    <img id="previewImage" src="">
                                </div>
                            </div>
                            <div class="col m12 s12">
                                <h6><b>PO Terpakai</b> (hapus untuk bisa diakses pengguna lain) : <i id="list-used-data"></i></h6>
                            </div>
                            <div class="col m12 s12">
                                <p class="mt-2 mb-2">
                                    <h4>Detail Produk</h4>
                                    <div style="overflow:auto;">
                                        <table class="bordered" style="width:1800px;" id="table-detail">
                                            <thead>
                                                <tr>
                                                    <th class="center">Item</th>
                                                    <th class="center">Qty PO</th>
                                                    <th class="center">Timbang Datang</th>
                                                    <th class="center">Timbang Pulang</th>
                                                    <th class="center">Qty Netto</th>
                                                    <th class="center">Satuan</th>
                                                    <th class="center">Keterangan 1</th>
                                                    <th class="center">Keterangan 2</th>
                                                    <th class="center">Plant</th>
                                                    <th class="center">Gudang</th>
                                                    <th class="center">Hapus</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-item">
                                                <tr id="last-row-item">
                                                    <td colspan="11" class="center">
                                                        <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addItem()" href="javascript:void(0);">
                                                            <i class="material-icons left">add</i> Item Manual
                                                        </a>
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

<div id="modal6" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>{{ $title }} Keluar</h4>
                <form class="row" id="form_data_update" onsubmit="return false;">
                    <div class="col s12">
                        <div class="row">
                            <input type="hidden" id="tempPlace">
                            <input type="hidden" id="tempGoodScale" name="tempGoodScale">
                            <div class="col m1 s12">
                                Kode
                            </div>
                            <div class="col m3 s12" id="codeUpdate">

                            </div>
                            <div class="col m1 s12">
                                Supplier
                            </div>
                            <div class="col m3 s12" id="supplierUpdate">

                            </div>
                            <div class="col m1 s12">
                                Kamera:
                            </div>
                            <div class="col m3 s12">
                                <select id="videoSource1" name="videoSource1" class="browser-default"></select>
                            </div>
                            <div class="col m12 s12">
                                <div class="input-field col m4 s12">
                                    <h6>Foto Masuk :</h6>
                                    <img id="previewImageIn" src="">
                                </div>
                                <div class="input-field col m4 s12 center">
                                    <h6>Layar Kamera :</h6>
                                    <video id="video1" autoplay muted playsinline></video>
                                    <a class="btn waves-effect waves-light mt-3" href="javascript:void(0)" id="takePhoto1">Ambil Gambar <i class="material-icons right">add_a_photo</i></a>
                                </div>
                                <div class="input-field col m4 s12">
                                    <h6>Hasil Foto :</h6>
                                    <img id="previewImage1" src="">
                                </div>
                            </div>
                            <div class="col m12 s12">
                                <p class="mt-2 mb-2">
                                    <h4>Detail Produk</h4>
                                    <div style="overflow:auto;">
                                        <table class="bordered" style="width:1800px;">
                                            <thead>
                                                <tr>
                                                    {{-- <th class="center" style="width:250px !important;">Link PO</th> --}}
                                                    <th class="center">Item</th>
                                                    <th class="center">Qty PO</th>
                                                    <th class="center">Timbang Datang</th>
                                                    <th class="center">Timbang Pulang</th>
                                                    <th class="center">Qty Nett</th>
                                                    <th class="center">Satuan</th>
                                                    <th class="center">Keterangan 1</th>
                                                    <th class="center">Keterangan 2</th>
                                                    <th class="center">Plant</th>
                                                    <th class="center">Gudang</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-item-update">
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                </p>
                            </div>
                            <div class="col s12 mt-3">
                                <button class="btn waves-effect waves-light right submit" onclick="saveUpdate();">Simpan <i class="material-icons right">send</i></button>
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

<div id="modal2" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <div class="row">
                    <div class="col s12 mt-2">
                        <div id="datatable_buttons_purchase_order"></div>
                        <i class="right">Pilih salah satu data dari tabel dibawah untuk bisa diproses di form sebelumnya.</i>
                        <table id="table_purchase_order" class="display" width="100%">
                            <thead>
                                <tr>
                                    <th class="center-align">No. PO</th>
                                    <th class="center-align">Tgl.Post</th>
                                    <th class="center-align">Nama Penerima</th>
                                    <th class="center-align">Alamat Penerima</th>
                                    <th class="center-align">Kontak Penerima</th>
                                    <th class="center-align">% Sisa (GRPO)</th>
                                    <th class="center-align">Detail Barang</th>
                                </tr>
                            </thead>
                            <tbody id="body-detail-purchase-order"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
        <button class="btn waves-effect waves-light purple right submit" onclick="applyDocuments();">Gunakan <i class="material-icons right">forward</i></button>
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

<div id="modal3" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content row">
        <div class="col s12" id="show_structure">
            <div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;">

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
<script src="{{ url('app-assets/js/custom/timbangan.js') }}"></script>
<script>
    let interval;

    $(function() {
        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
            
        });

        loadDataTable();

        window.table.search('{{ $code }}').draw();

        $('#modal4').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#show_detail').empty();
            },
            dismissible:false,
        });

        $('#modal6').modal({
            onOpenStart: function(modal,trigger) {
            },
            onOpenEnd: function(modal, trigger) { 
                getWeight();
                getStream().then(getDevices).then(gotDevices);
            },
            onCloseEnd: function(modal, trigger){
                $('#body-item-update').empty();
                $('#supplierUpdate').text('');
                $('#codeUpdate').text('');
                $('#tempPlace').val('');
                $('#tempGoodScale').val('');
                $('#previewImageIn').attr('src','');
                clearGetWeight();
                $('#videoSource1').empty();
                $('#previewImage1').attr('src','');
                if (window.stream) {
                    window.stream.getTracks().forEach(track => {
                        track.stop();
                    });
                }
            },
            dismissible:false,
        });
        
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                $('#post_date').attr('min','{{ $minDate }}');
                $('#post_date').attr('max','{{ $maxDate }}');
            },
            onOpenEnd: function(modal, trigger) {
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
                window.onbeforeunload = function() {
                    if($('.data-used').length > 0){
                        $('.data-used').trigger('click');
                    }
                    return 'You will lose all changes made since your last save';
                };
                getWeight();
                getStream().then(getDevices).then(gotDevices);
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                $('#account_id').empty();
                M.updateTextFields();
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }
                window.onbeforeunload = function() {
                    return null;
                };
                clearGetWeight();
                $('.row_item').remove();
                $('#videoSource').empty();
                $('#previewImage').attr('src','');
                if (window.stream) {
                    window.stream.getTracks().forEach(track => {
                        track.stop();
                    });
                }
            }
        });

        $('#modal2').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) {
                table_purchase_order = $('#table_purchase_order').DataTable({
                    "responsive": true,
                    scrollY: '50vh',
                    scrollCollapse: true,
                    "iDisplayInLength": 10,
                    "ordering": false,
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
                $('#table_purchase_order_wrapper > .dt-buttons').appendTo('#datatable_buttons_purchase_order');
                $('select[name="table_purchase_order_length"]').addClass('browser-default');
            },
            onCloseEnd: function(modal, trigger){
                $('#body-detail-purchase-order').empty();
                $('#table_purchase_order').DataTable().clear().destroy();
            },
            dismissible:false,
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
                
            },
            dismissible:false,
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
            },
            dismissible:false,
        });

        select2ServerSide('#purchase_order_id', '{{ url("admin/select2/purchase_order") }}');
        select2ServerSide('#account_id', '{{ url("admin/select2/supplier") }}');

        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
        });

        $("#table-detail th").resizable({
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
                    'status' : $('#filter_status').val(),
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
                { name: 'other', searchable: false, orderable: false, className: 'center-align' },
                { name: 'name', className: 'center-align' },
                { name: 'account_id', className: 'center-align' },
                { name: 'company_id', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'delivery_no', className: 'center-align' },
                { name: 'vehicle_no', className: 'center-align' },
                { name: 'driver', className: 'center-align' },
                { name: 'note', className: '' },
                { name: 'document', searchable: false, orderable: false, className: 'center-align' },
                { name: 'image_in', searchable: false, orderable: false, className: 'center-align' },
                { name: 'image_out', searchable: false, orderable: false, className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'operation', searchable: false, orderable: false, className: 'center-align' },
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

    function getPurchaseOrderAi(){
        if($('#account_id').val() && $('#place_id').val()){
            $.ajax({
                url: '{{ Request::url() }}/get_purchase_order_ai',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    account_id:  $('#account_id').val(),
                    place_id: $('#place_id').val(),
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    loadingClose('.modal-content');
                    $('#modal2').modal('open');

                    if(response.length > 0){
                        $.each(response, function(i, val) {
                            $('#body-detail-purchase-order').append(`
                                <tr data-id="` + val.id + `">
                                    <td class="center">
                                        ` + val.code + `
                                    </td>
                                    <td class="center-align">
                                        ` + val.post_date + `
                                    </td>
                                    <td class="center-align">
                                        ` + val.receiver_name + `
                                    </td>
                                    <td class="">
                                        ` + val.receiver_address + `
                                    </td>
                                    <td class="center-align">
                                        ` + val.receiver_phone + `
                                    </td>
                                    <td class="center-align">
                                        ` + val.percent_balance + `
                                    </td>
                                    <td class="">
                                        ` + val.description + `
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
            swal({
                title: 'Ups!',
                text: 'Silahkan pilih supplier dan plant terlebih dahulu.',
                icon: 'warning'
            });
        }
    }

    function addItem(){
        if($('.row_item[data-po!=""]').length > 0){
            $('#account_id,#purchase_order_id').empty();
            if($('.data-used').length > 0){
                $('.data-used').trigger('click');
            }
        }
        var count = makeid(10);
        $('#last-row-item').before(`
            <tr class="row_item" data-po="">
                <input type="hidden" name="arr_purchase[]" value="">
                <td>
                    <select class="browser-default" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                </td>
                <td class="right-align">
                    0
                </td>
                <td class="center-align">
                    <input name="arr_qty_in[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);" style="text-align:right;width:100px;" id="arr_qty_in` + count + `" data-id="` + count + `" readonly>
                </td>
                <td class="center-align">
                    <input name="arr_qty_out[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);" style="text-align:right;width:100px;" id="arr_qty_out` + count + `" data-id="` + count + `" readonly>
                </td>
                <td class="right-align">
                    <span id="qtyBalance` + count + `">0,000</span>
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_satuan` + count + `" name="arr_satuan[]" required>
                        <option value="">--Silahkan pilih item--</option>    
                    </select>
                </td>
                <td>
                    <input name="arr_note[]" class="browser-default" type="text" placeholder="Keterangan 1..." style="width:100%;">
                </td>
                <td>
                    <input name="arr_note2[]" class="browser-default" type="text" placeholder="Keterangan 2..." style="width:100%;">
                </td>
                <td>
                    <select class="browser-default" id="arr_place` + count + `" name="arr_place[]">
                        @foreach ($place as $rowplace)
                            <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                        @endforeach
                    </select>    
                </td>
                <td>
                    <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">
                        @foreach ($warehouse as $rowwarehouse)
                            <option value="{{ $rowwarehouse->id }}">{{ $rowwarehouse->code }}</option>
                        @endforeach
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
    }

    function getWeight(){
        if(interval){
            clearInterval(interval);
        }
        interval = setInterval(function () {
            let place_id;
            if($('#modal1').hasClass('open')){
                place_id = $('#place_id').val();
            }
            if($('#modal6').hasClass('open')){
                place_id = $('#tempPlace').val();
            }
            $.ajax({
                url: '{{ Request::url() }}/get_weight',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    place_id : place_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if($('#modal1').hasClass('open')){
                        if(!$('#temp').val()){
                            $('input[name^="arr_qty_in"]').each(function(){
                                $(this).val(response);
                                countBalance(this);
                            });
                        }
                    }
                    if($('#modal6').hasClass('open')){
                        $('input[name^="arr_qty_out"]').each(function(){
                            $(this).val(response);
                            countBalance(this);
                        });
                    }
                }
            });
        },1000);
    }

    function clearGetWeight(){
        if(interval){
            clearInterval(interval);
        }
    }

    function getRowUnit(val){
        $("#arr_warehouse" + val).empty();
        if($("#arr_item" + val).val()){
            $('#arr_satuan' + val).text($("#arr_item" + val).select2('data')[0].buy_unit);
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
            $('#arr_satuan' + val).empty();
            if($("#arr_item" + val).select2('data')[0].buy_units.length > 0){
                $.each($("#arr_item" + val).select2('data')[0].buy_units, function(i, value) {
                    $('#arr_satuan' + val).append(`
                        <option value="` + value.id + `">` + value.code + `</option>
                    `);
                });
            }else{
                $("#arr_satuan" + val).append(`
                    <option value="">--Satuan tidak diatur di master data Item--</option>
                `);
            }
        }else{
            $("#arr_item" + val).empty();
            $("#arr_satuan" + val).empty().append(`
                <option value="">--Silahkan pilih item--</option>
            `);
            $("#arr_warehouse" + val).append(`
                <option value="">--Silahkan pilih item--</option>
            `);
        }
    }

    function getPurchaseOrder(){
        if($('#purchase_order_id').val()){
            $.ajax({
                url: '{{ Request::url() }}/get_purchase_order',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: $('#purchase_order_id').val()
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
                        $('#purchase_order_id').empty();
                    }else{
                        if(response.details.length > 0){
                            if($('.data-used').length > 0){
                                $('.data-used').trigger('click');
                            }

                            if($('.row_item').length){
                                $('.row_item').remove();
                            }

                            $('#account_id').empty().append(`
                                <option value="` + response.account_id + `">` + response.account_name + `</option>
                            `);

                            $('#list-used-data').append(`
                                <div class="chip purple darken-4 gradient-shadow white-text">
                                    ` + response.code + `
                                    <i class="material-icons close data-used" onclick="removeUsedData('` + response.id + `')">close</i>
                                </div>
                            `);

                            $.each(response.details, function(i, val) {
                                var count = makeid(10);
                                $('#last-row-item').before(`
                                    <tr class="row_item" data-po="` + response.id + `">
                                        <input type="hidden" name="arr_item[]" value="` + val.item_id + `">
                                        <input type="hidden" name="arr_purchase[]" value="` + val.purchase_order_detail_id + `">
                                        <input type="hidden" name="arr_place[]" value="` + val.place_id + `">
                                        <input type="hidden" name="arr_warehouse[]" value="` + val.warehouse_id + `">
                                        <td>
                                            ` + val.item_name + `
                                        </td>
                                        <td class="right-align">
                                            ` + val.qty + `
                                        </td>
                                        <td class="center-align">
                                            <input name="arr_qty_in[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);" style="text-align:right;width:100px;" id="arr_qty_in` + count + `" data-id="` + count + `" readonly>
                                        </td>
                                        <td class="center-align">
                                            <input name="arr_qty_out[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);" style="text-align:right;width:100px;" id="arr_qty_out` + count + `" data-id="` + count + `" readonly>
                                        </td>
                                        <td class="right-align">
                                            <span id="qtyBalance` + count + `">0,000</span>
                                        </td>
                                        <td class="center">
                                            <span>` + val.unit + `</span>
                                        </td>
                                        <td>
                                            <input name="arr_note[]" class="browser-default" type="text" placeholder="Keterangan 1..." value="` + val.note + `" style="width:100%;">
                                        </td>
                                        <td>
                                            <input name="arr_note2[]" class="browser-default" type="text" placeholder="Keterangan 2..." value="` + val.note2 + `" style="width:100%;">
                                        </td>
                                        <td class="center">
                                            <span>` + val.place_name + `</span>
                                        </td>
                                        <td class="center">
                                            <span>` + val.warehouse_name + `</span>
                                        </td>
                                        <td class="center">
                                            <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                                <i class="material-icons">delete</i>
                                            </a>
                                        </td>
                                    </tr>
                                `);

                                $('#place_id').val(val.place_id).formSelect();
                            });
                        }
                        $('.modal-content').scrollTop(0);
                        M.updateTextFields();
                        $('#modal2').modal('close');
                        $('.tooltipped').tooltip();
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
                text: 'Silahkan pilih purchase order terlebih dahulu.',
                icon: 'warning'
            });
        }
    }

    function countBalance(element){
        let balance = parseFloat($('#arr_qty_in' + $(element).data('id')).val().replaceAll(".", "").replaceAll(",",".")) - parseFloat($('#arr_qty_out' + $(element).data('id')).val().replaceAll(".", "").replaceAll(",","."));
        $('#qtyBalance' + $(element).data('id')).text(
            (balance >= 0 ? '' : '-') + formatRupiahIni(balance.toFixed(3).toString().replace('.',','))
        );
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
                let po_id, passed = true;
                $.map(table_purchase_order.rows('.selected').nodes(), function (item) {
                    po_id = $(item).data('id');
                });
                
                if(po_id){
                    $.ajax({
                        url: '{{ Request::url() }}/get_purchase_order',
                        type: 'POST',
                        dataType: 'JSON',
                        data: {
                            id: po_id
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
                                $('#purchase_order_id').empty();
                            }else{
                                if(response.details.length > 0){
                                    if($('.data-used').length > 0){
                                        $('.data-used').trigger('click');
                                    }

                                    if($('.row_item').length){
                                        $('.row_item').remove();
                                    }

                                    $('#list-used-data').append(`
                                        <div class="chip purple darken-4 gradient-shadow white-text">
                                            ` + response.code + `
                                            <i class="material-icons close data-used" onclick="removeUsedData('` + response.id + `')">close</i>
                                        </div>
                                    `);

                                    $.each(response.details, function(i, val) {
                                        var count = makeid(10);
                                        $('#last-row-item').before(`
                                            <tr class="row_item" data-po="` + response.id + `">
                                                <input type="hidden" name="arr_item[]" value="` + val.item_id + `">
                                                <input type="hidden" name="arr_purchase[]" value="` + val.purchase_order_detail_id + `">
                                                <input type="hidden" name="arr_place[]" value="` + val.place_id + `">
                                                <input type="hidden" name="arr_warehouse[]" value="` + val.warehouse_id + `">
                                                <td>
                                                    ` + val.item_name + `
                                                </td>
                                                <td class="right-align">
                                                    ` + val.qty + `
                                                </td>
                                                <td class="center-align">
                                                    <input name="arr_qty_in[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);" style="text-align:right;width:100px;" id="arr_qty_in` + count + `" data-id="` + count + `" readonly>
                                                </td>
                                                <td class="center-align">
                                                    <input name="arr_qty_out[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);" style="text-align:right;width:100px;" id="arr_qty_out` + count + `" data-id="` + count + `" readonly>
                                                </td>
                                                <td class="right-align">
                                                    <span id="qtyBalance` + count + `">0,000</span>
                                                </td>
                                                <td class="center">
                                                    <span>` + val.unit + `</span>
                                                </td>
                                                <td>
                                                    <input name="arr_note[]" class="browser-default" type="text" placeholder="Keterangan 1..." value="` + val.note + `" style="width:100%;">
                                                </td>
                                                <td>
                                                    <input name="arr_note2[]" class="browser-default" type="text" placeholder="Keterangan 2..." value="` + val.note2 + `" style="width:100%;">
                                                </td>
                                                <td class="center">
                                                    <span>` + val.place_name + `</span>
                                                </td>
                                                <td class="center">
                                                    <span>` + val.warehouse_name + `</span>
                                                </td>
                                                <td class="center">
                                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                                        <i class="material-icons">delete</i>
                                                    </a>
                                                </td>
                                            </tr>
                                        `);
                                    });

                                    $('.tooltipped').tooltip();
                                }
                                $('.modal-content').scrollTop(0);
                                M.updateTextFields();
                                $('#modal2').modal('close');
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

    function removeUsedData(id){
        $.ajax({
            url: '{{ Request::url() }}/remove_used_data',
            type: 'POST',
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
                $('.row_item[data-po="' + id + '"]').remove();
                if($('.row_item').length == 0 && $('#empty-item').length == 0){
                    $('#body-item').append(`
                        <tr id="empty-item">
                            <td colspan="12" class="center">
                                Pilih purchase order untuk memulai...
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
                
                var formData = new FormData($('#form_data')[0]), passedUnit = true;

                var s = $('#previewImage').attr('src') ? $('#previewImage').attr('src') : '';

                $('select[name^="arr_satuan[]"]').each(function(index){
                    if(!$(this).val()){
                        passedUnit = false;
                    }
                });

                if(passedUnit){
                    if(s){
                        formData.delete("arr_item[]");
                        formData.delete("arr_purchase[]");
                        formData.delete("arr_qty_in[]");
                        formData.delete("arr_qty_out[]");
                        formData.delete("arr_note[]");
                        formData.delete("arr_note2[]");

                        formData.append('image_in', s);

                        $('[name^="arr_item"]').each(function(index){
                            formData.append('arr_item[]',($(this).val() ? $(this).val() : ''));
                            formData.append('arr_purchase[]',($('[name^="arr_purchase"]').eq(index).val() ? $('[name^="arr_purchase"]').eq(index).val() : ''));
                            formData.append('arr_qty_in[]',($('[name^="arr_qty_in"]').eq(index).val() ? $('[name^="arr_qty_in"]').eq(index).val() : ''));
                            formData.append('arr_qty_out[]',($('[name^="arr_qty_out"]').eq(index).val() ? $('[name^="arr_qty_out"]').eq(index).val() : ''));
                            formData.append('arr_note[]',($('[name^="arr_note"]').eq(index).val() ? $('[name^="arr_note"]').eq(index).val() : ''));
                            formData.append('arr_note2[]',($('[name^="arr_note2"]').eq(index).val() ? $('[name^="arr_note2"]').eq(index).val() : ''));
                        });
                        var path = window.location.pathname;
                    path = path.replace(/^\/|\/$/g, '');

                    // Split the path by slashes and get the last segment
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
                            cache: false,
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
                            text: 'Foto cctv timbang masuk tidak boleh kosong.',
                            icon: 'warning'
                        });
                    }
                }else{
                    swal({
                        title: 'Ups!',
                        text: 'Salah satu item belum diatur satuannya.',
                        icon: 'error'
                    });
                }
            }
        });
    }

    function saveUpdate(){
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
                
                var formData = new FormData($('#form_data_update')[0]);

                var s = $('#previewImage1').attr('src') ? $('#previewImage1').attr('src') : '';

                if(s){

                    formData.delete("arr_pod[]");

                    formData.append('image_out', s);

                    $('[name^="arr_pod"]').each(function(index){
                        formData.append('arr_pod[]',($('[name^="arr_pod"]').eq(index).val() ? $('[name^="arr_pod"]').eq(index).val() : ''));
                    });

                    $.ajax({
                        url: '{{ Request::url() }}/save_update',
                        type: 'POST',
                        dataType: 'JSON',
                        data: formData,
                        contentType: false,
                        processData: false,
                        cache: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        beforeSend: function() {
                            loadingOpen('.modal-content');
                        },
                        success: function(response) {
                            loadingClose('.modal-content');
                            if(response.status == 200) {
                                successUpdate();
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
                        text: 'Foto cctv timbang keluar tidak boleh kosong.',
                        icon: 'warning'
                    });
                }
            }
        });
    }

    function update(id){
        $.ajax({
            url: '{{ Request::url() }}/update',
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
                $('#modal6').modal('open');
                $('#tempPlace').val(response.place_id);
                $('#tempGoodScale').val(response.id);
                $('#previewImageIn').attr('src',response.image_in);
                $('#codeUpdate').text(response.code);
                $('#supplierUpdate').text(response.supplier_name);

                if(response.details.length > 0){
                    $('#body-item-update').empty();

                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        $('#body-item-update').append(`
                            <tr class="row_item_update">
                                ` + (val.purchase_order_detail_id ? `<input type="hidden" name="arr_pod[]" value=` + val.purchase_order_detail_id + `>` : `` ) + `
                                <input type="hidden" name="arr_good_scale_detail[]" value="` + val.id + `">
                                <input type="hidden" name="arr_qty_in[]" value="` + val.qty_in + `" id="arr_qty_in` + count + `">
                                <input type="hidden" name="arr_pod[]" value="" id="arr_pod` + count + `">
                                <td>
                                    ` + val.item_name + `
                                </td>
                                <td class="right-align">
                                    ` + val.qty_po + `
                                </td>
                                <td class="center-align">
                                    ` + val.qty_in + `
                                </td>
                                <td class="center-align">
                                    <input name="arr_qty_out[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.qty_out + `" onkeyup="formatRupiah(this);" style="text-align:right;width:100px;" id="arr_qty_out` + count + `" data-id="` + count + `" readonly>
                                </td>
                                <td class="right-align">
                                    <span id="qtyBalance` + count + `">0,000</span>
                                </td>
                                <td class="center">
                                    <span id="arr_satuan` + count + `">` + val.unit + `</span>
                                </td>
                                <td>
                                    ` + val.note + `
                                </td>
                                <td>
                                    ` + val.note2 + `
                                </td>
                                <td>
                                    ` + val.place_name + `
                                </td>
                                <td>
                                    ` + val.warehouse_name + `
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
                loadingClose('#main');
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
            }
        });
    }

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
    }

    function successUpdate(){
        loadDataTable();
        $('#modal6').modal('close');
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
                $('#note').val(response.note);
                $('#delivery_no').val(response.delivery_no);
                $('#vehicle_no').val(response.vehicle_no);
                $('#driver').val(response.driver);
                $('#post_date').val(response.post_date);
                $('#company_id').val(response.company_id).formSelect();
                $('#place_id').val(response.place_id).formSelect();
                $('#account_id').empty().append(`
                    <option value="` + response.account_id + `">` + response.account_name + `</option>
                `);

                if(response.details.length > 0){
                    $('.row_item').each(function(){
                        $(this).remove();
                    });

                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        
                        if(val.purchase_order_detail_id){
                            $('#last-row-item').before(`
                                <tr class="row_item" data-po="` + response.id + `">
                                    <input type="hidden" name="arr_item[]" value="` + val.item_id + `">
                                    <input type="hidden" name="arr_purchase[]" value="` + val.purchase_order_detail_id + `">
                                    <input type="hidden" name="arr_place[]" value="` + val.place_id + `">
                                    <input type="hidden" name="arr_warehouse[]" value="` + val.warehouse_id + `">
                                    <td>
                                        ` + val.item_name + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.qty_po + `
                                    </td>
                                    <td class="center-align">
                                        <input name="arr_qty_in[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.qty_in + `" onkeyup="formatRupiah(this);" style="text-align:right;width:100px;" id="arr_qty_in` + count + `" data-id="` + count + `" readonly>
                                    </td>
                                    <td class="center-align">
                                        <input name="arr_qty_out[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.qty_out + `" onkeyup="formatRupiah(this);" style="text-align:right;width:100px;" id="arr_qty_out` + count + `" data-id="` + count + `" readonly>
                                    </td>
                                    <td class="right-align">
                                        <span id="qtyBalance` + count + `">` + val.qty_balance + `</span>
                                    </td>
                                    <td class="center">
                                        <select class="browser-default" id="arr_satuan` + count + `" name="arr_satuan[]" required>
                                            <option value="">--Silahkan pilih item--</option>    
                                        </select>
                                    </td>
                                    <td>
                                        <input name="arr_note[]" class="browser-default" type="text" placeholder="Keterangan 1..." style="width:100%;" value="` + val.note + `">
                                    </td>
                                    <td>
                                        <input name="arr_note2[]" class="browser-default" type="text" placeholder="Keterangan 2..." style="width:100%;" value="` + val.note2 + `">
                                    </td>
                                    <td class="center">
                                        <span>` + val.place_name + `</span>
                                    </td>
                                    <td class="center">
                                        <span>` + val.warehouse_name + `</span>
                                    </td>
                                    <td class="center">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                </tr>
                            `);
                        }else{
                            $('#last-row-item').before(`
                                <tr class="row_item" data-po="">
                                    <input type="hidden" name="arr_purchase[]" value="">
                                    <td>
                                        <select class="browser-default" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                                    </td>
                                    <td class="right-align">
                                        0
                                    </td>
                                    <td class="center-align">
                                        <input name="arr_qty_in[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.qty_in + `" onkeyup="formatRupiah(this);" style="text-align:right;width:100px;" id="arr_qty_in` + count + `" data-id="` + count + `" readonly>
                                    </td>
                                    <td class="center-align">
                                        <input name="arr_qty_out[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.qty_out + `" onkeyup="formatRupiah(this);" style="text-align:right;width:100px;" id="arr_qty_out` + count + `" data-id="` + count + `" readonly>
                                    </td>
                                    <td class="right-align">
                                        <span id="qtyBalance` + count + `">` + val.qty_balance + `</span>
                                    </td>
                                    <td class="center">
                                        <select class="browser-default" id="arr_satuan` + count + `" name="arr_satuan[]" required>
                                            <option value="">--Silahkan pilih item--</option>    
                                        </select>
                                    </td>
                                    <td>
                                        <input name="arr_note[]" class="browser-default" type="text" placeholder="Keterangan 1..." style="width:100%;" value="` + val.note + `">
                                    </td>
                                    <td>
                                        <input name="arr_note2[]" class="browser-default" type="text" placeholder="Keterangan 2..." style="width:100%;" value="` + val.note2 + `">
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_place` + count + `" name="arr_place[]">
                                            @foreach ($place as $rowplace)
                                                <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                                            @endforeach
                                        </select>    
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]"></select>
                                    </td>
                                    <td class="center">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                </tr>
                            `);

                            $('#arr_place' + count).val(val.place_id);

                            $.each(val.list_warehouse, function(i, value) {
                                $('#arr_warehouse' + count).append(`
                                    <option value="` + value.id + `">` + value.name + `</option>
                                `);
                            });

                            $('#arr_item' + count).empty().append(`
                                <option value="` + val.item_id + `">` + val.item_name + `</option>
                            `);

                            $('#arr_warehouse' + count).val(val.warehouse_id);

                            select2ServerSide('#arr_item' + count, '{{ url("admin/select2/purchase_item") }}');
                        }

                        if(val.buy_units.length > 0){
                            $('#arr_satuan' + count).empty();
                            $.each(val.buy_units, function(i, value) {
                                $('#arr_satuan' + count).append(`
                                    <option value="` + value.id + `" ` + (value.id == val.item_unit_id ? 'selected' : '') + `>` + value.code + `</option>
                                `);
                            });
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
    
    function printData(){
        let arr_id_temp=[];
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

</script>