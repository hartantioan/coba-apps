<style>
    .modal {
        top:0px !important;
    }
    table > thead > tr > th {
        font-size: 13px !important;
    }
    #dropZone {
        border: 2px dashed #ccc;
    }
    #imagePreview {
        max-width: 20em;
        max-height: 20em;
        min-height: 5em;
        margin: 2px auto;
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

    video {
        width:100%;
    }

    #previewImage, #previewImage1, #previewImageIn {
        width:100%;
    }

    .select-wrapper, .select2-container {
        height:3.6rem !important;
    }

    #form_data_update input[type=text]:not(.browser-default) {
        border-bottom: none;
    }

    .data-input {
        border-bottom: 1px solid #9e9e9e !important;
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
                                            <div class="col m3 s6 ">
                                                <label for="filter_status" style="font-size:1rem;">Status Dokumen :</label>
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
                                                <label for="filter_status" style="font-size:1rem;">Status QC :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_status_qc" onchange="loadDataTable()">
                                                        <option value="">{{ __('translations.all') }}</option>
                                                        <option value="1">Disetujui</option>
                                                        <option value="2">Ditolak</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m3 s6 ">
                                                <label for="start_date" style="font-size:1rem;">{{ __('translations.start_date') }} : </label>
                                                <div class="input-field col s12">
                                                <input type="date" max="{{ date('9999'.'-12-31') }}" id="start_date" name="start_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                            <div class="col m3 s6 ">
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
                                    <div class="row mt-2">
                                        <div class="col s12">
                                            <div class="card-alert card green">
                                                <div class="card-content white-text">
                                                    <p>Info : Ada 3 tahapan penimbangan, yakni ketika truk timbang datang, pengecekan QC dan truk timbang pulang (jika lolos pengecekan QC).</p>
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
                                                <span class="hide-on-small-onl">{{ __('translations.refresh') }}</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-2" href="javascript:void(0);" onclick="exportExcel();">
                                                <i class="material-icons hide-on-med-and-up">view_headline</i>
                                                <span class="hide-on-small-onl">Export</span>
                                                <i class="material-icons right">view_headline</i>
                                            </a>
                                            <table id="datatable_serverside" >
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{ __('translations.code') }}</th>
                                                        <th>Ref.PO/MOD</th>
                                                        <th>{{ __('translations.user') }}</th>
                                                        <th>{{ __('translations.company') }}</th>
                                                        <th>{{ __('translations.date') }}</th>
                                                        <th>Tipe</th>
                                                        <th>No.SJ</th>
                                                        <th>No.Kendaraan</th>
                                                        <th>Supir</th>
                                                        <th>{{ __('translations.note') }}</th>
                                                        <th>Dokumen</th>
                                                        <th>Foto Masuk</th>
                                                        <th>Waktu Masuk</th>
                                                        <th>Pengecekan QC?</th>
                                                        <th>Foto QC</th>
                                                        <th>Waktu QC</th>
                                                        <th>Foto Keluar</th>
                                                        <th>Waktu Keluar</th>
                                                        <th>Status Dokumen</th>
                                                        <th>Status QC</th>
                                                        <th>Catatan QC</th>
                                                        <th>By</th>
                                                        <th>{{ __('translations.plant') }}</th>
                                                        <th>{{ __('translations.warehouse') }}</th>
                                                        <th>{{ __('translations.item') }}</th>
                                                        <th>Qty PO</th>
                                                        <th>Qty Bruto</th>
                                                        <th>Qty Tara</th>
                                                        <th>Qty Netto</th>
                                                        <th>Qty QC</th>
                                                        <th>Qty Final</th>
                                                        <th>Kadar Air (%)</th>
                                                        <th>Viskositas (detik)</th>
                                                        <th>Residu (gr)</th>
                                                        <th>{{ __('translations.unit') }}</th>
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
                                <select class="form-control" id="company_id" name="company_id">
                                    @foreach ($company as $rowcompany)
                                        <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                    @endforeach
                                </select>
                                <label class="ac" for="company_id">{{ __('translations.company') }}</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="type" name="type" onchange="changeMode(this.value);">
                                    <option value="1">Timbang Barang Masuk (Pembelian)</option>
                                    <option value="2">Timbang Barang Keluar (Penjualan)</option>
                                </select>
                            </div>
                            <div class="input-field col m3 s12 hide-inputs">
                                <select class="browser-default" id="item_id" name="item_id" onchange="getRowUnit();"></select>
                                <label class="active" for="item_id">{{ __('translations.item') }}</label>
                            </div>
                            <div class="input-field col m3 s12" id="div-account">
                                <input type="hidden" id="temp" name="temp">
                                <select class="browser-default" id="account_id" name="account_id"></select>
                                <label class="active" for="account_id">Supplier/Ekspedisi</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="place_id" name="place_id">
                                    @foreach ($place as $rowplace)
                                        <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="place_id">{{ __('translations.plant') }}</label>
                            </div>
                            <div class="input-field col m3 s12 hide-inputs">
                                <select class="browser-default" id="warehouse_id" name="warehouse_id">
                                    <option value="">--Silahkan pilih item--</option>
                                </select>
                                <label class="active" for="warehouse_id">{{ __('translations.warehouse') }}</label>
                            </div>
                            <div class="input-field col m3 s12 hide-inputs">
                                <select class="browser-default" id="purchase_order_detail_id" name="purchase_order_detail_id" onchange="getPurchaseOrderQty();"></select>
                                <label class="active" for="purchase_order_detail_id">Purchase Order</label>
                            </div>
                            <div class="input-field col m3 s12 hide">
                                <select class="browser-default" id="marketing_order_delivery_id" name="marketing_order_delivery_id"></select>
                                <label class="active" for="marketing_order_delivery_id">Jadwal Kirim</label>
                            </div>
                            <div class="input-field col m3 s12 hide-inputs">
                                <input id="qty_po" name="qty_po" type="text" onkeyup="formatRupiahNoMinus(this);" value="0,000" readonly>
                                <label class="active" for="qty_po">Qty PO</label>
                            </div>
                            <div class="input-field col m2 s9">
                                <input id="qty_in" name="qty_in" type="text" onkeyup="formatRupiahNoMinus(this);" value="0,000" readonly>
                                <label class="active" for="qty_in">Qty Bruto</label>
                            </div>
                            <div class="input-field col m1 s12 center-align">
                                <a href="javascript:void(0);" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text" onclick="stopStart();" id="btn-stop-start"><i class="material-icons right icon-stop-start">stop</i></a>
                                <label class="active">&nbsp;</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="qty_out" name="qty_out" type="text" onkeyup="formatRupiahNoMinus(this);" value="0,000" readonly>
                                <label class="active" for="qty_out">Qty Tara</label>
                            </div>
                            <div class="input-field col m3 s12 hide-inputs">
                                <select class="browser-default" id="item_unit_id" name="item_unit_id" required>
                                    <option value="">--Silahkan pilih item--</option>    
                                </select>
                                <label class="active" for="item_unit_id">{{ __('translations.unit') }}</label>
                            </div>
                            <div class="col m12 12"></div>
                            <div class="input-field col m3 s12 hide-inputs">
                                <div class="switch mb-1">
                                    <label for="status">{{ __('translations.quality_check') }}</label>
                                    <label class="right">
                                        {{ __('translations.no') }}
                                        <input type="checkbox" id="is_quality_check" name="is_quality_check" value="1" checked>
                                        <span class="lever"></span>
                                        {{ __('translations.yes') }}
                                    </label>
                                </div>
                            </div>
                            <div class="input-field col m3 s12">
                                <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note">{{ __('translations.note') }}</label>
                                <div id="charCount"></div>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. diterima" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);">
                                <label class="active" for="post_date">Tgl. Diterima</label>
                            </div>
                            <div class="input-field col m3 s12 hide-inputs">
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
                            <div class="col m12 s12 l12"></div>
                            <div class="col m4 s12 step6">
                                <label class="">Bukti Upload</label>
                                <br>
                                <input type="file" name="document" id="fileInput" accept="image/*" style="display: none;">
                                <div  class="col m8 s12 " id="dropZone" ondrop="dropHandler(event);" ondragover="dragOverHandler(event);" style="margin-top: 0.5em;height: 5em;">
                                    Drop image here or <a href="javascript:void(0);" id="uploadLink">upload</a>
                                    <br>
                                    
                                </div>
                                <a class="waves-effect waves-light cyan btn-small" style="margin-top: 0.5em;margin-left:0.2em" id="clearButton" href="javascript:void(0);">
                                   Clear
                                </a>
                            </div>
                            <div class="col m4 s12">
                                <div id="fileName"></div>
                                <img src="" alt="Preview" id="imagePreview" style="display: none;">
                            </div>
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
                            
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light mr-1 submit" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
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
                            <input type="hidden" id="tempType" name="tempType">
                            <div class="input-field col m3 s12">
                                <div id="codeUpdate" class="mt-2">

                                </div>
                                <label class="active" for="codeUpdate">{{ __('translations.code') }}</label>
                            </div>
                            <div class="input-field col m3 s12 supplier-class">
                                <div id="supplierUpdate" class="mt-2">

                                </div>
                                <label class="active" for="supplierUpdate">Supplier</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <div id="plantUpdate" class="mt-2">

                                </div>
                                <label class="active" for="plantUpdate">{{ __('translations.plant') }}</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <div id="warehouseUpdate" class="mt-2">

                                </div>
                                <label class="active" for="warehouseUpdate">{{ __('translations.warehouse') }}</label>
                            </div>
                            <div class="col m12 s12"></div>
                            <div class="input-field col m3 s12">
                                <div id="purchaseOrderUpdate" class="mt-2">

                                </div>
                                <label class="active" for="purchaseOrderUpdate">Purchase Order / Marketing Order Delivery</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <div id="qtyInUpdate" class="mt-2">

                                </div>
                                <label class="active" for="qtyInUpdate">Qty Bruto</label>
                            </div>
                            <div class="input-field col m2 s12">
                                <input id="qtyOutUpdate" class="data-input" name="qtyOutUpdate" type="text" onkeyup="formatRupiahNoMinus(this);count();" value="0,000">
                                <label class="active" for="qtyOutUpdate">Qty Tara</label>
                            </div>
                            <div class="input-field col m1 s12 center-align">
                                <a href="javascript:void(0);" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text" onclick="stopStartOut();"><i class="material-icons right icon-stop-start-out">stop</i></a>
                                <label class="active">&nbsp;</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="qtyBalanceUpdate" name="qtyBalanceUpdate" type="text" onkeyup="formatRupiahNoMinus(this);" value="0,000" readonly>
                                <label class="active" for="qtyBalanceUpdate">Qty Netto</label>
                            </div>
                            <div class="col m12 s12"></div>
                            <div class="input-field col m3 s12">
                                <div id="unitUpdate" class="mt-2">

                                </div>
                                <label class="active" for="unitUpdate">{{ __('translations.unit') }}</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <textarea class="materialize-textarea" id="noteUpdate" name="noteUpdate" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="noteUpdate">{{ __('translations.note') }}</label>
                            </div>
                            <div class="col m12 s12"></div>
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
                            <div class="col s12 mt-3">
                                
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light mr-1 submit" onclick="saveUpdate();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>

<div id="modal2" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>{{ $title }} Update Informasi</h4>
                <form class="row" id="form_data_last" onsubmit="return false;">
                    <div class="col s12">
                        <div class="row">
                            <input type="hidden" id="tempGoodScaleLast" name="tempGoodScaleLast">
                            <div class="input-field col m3 s12">
                                <div id="codeUpdateLast" class="mt-2">

                                </div>
                                <label class="active" for="codeUpdateLast">{{ __('translations.code') }}</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <div id="plantUpdateLast" class="mt-2">

                                </div>
                                <label class="active" for="plantUpdateLast">{{ __('translations.plant') }}</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <div id="warehouseUpdateLast" class="mt-2">

                                </div>
                                <label class="active" for="warehouseUpdateLast">{{ __('translations.warehouse') }}</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <div id="purchaseOrderUpdateLast" class="mt-2">

                                </div>
                                <label class="active" for="purchaseOrderUpdateLast">Purchase Order</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="deliveryNoLast" name="deliveryNoLast" type="text" placeholder="No. Pengiriman">
                                <label class="active" for="deliveryNoLast">Nomor Pengiriman / SJ</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="vehicleNoLast" name="vehicleNoLast" type="text" placeholder="No. Kendaraan">
                                <label class="active" for="vehicleNoLast">Nomor Kendaraan</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="driverLast" name="driverLast" type="text" placeholder="Nama Supir">
                                <label class="active" for="driverLast">Nama Supir</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <textarea class="materialize-textarea" id="noteUpdateLast" name="noteUpdateLast" placeholder="Catatan / Keterangan" rows="1"></textarea>
                                <label class="active" for="noteUpdateLast">{{ __('translations.note') }}</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
        <button class="btn waves-effect waves-light purple right submit" onclick="saveUpdateLast();">Update <i class="material-icons right">forward</i></button>
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

<div id="modal3" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content row">
        <div class="col s12" id="show_structure">
            <div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;">

            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
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
    const dropZone = document.getElementById('dropZone');
    const uploadLink = document.getElementById('uploadLink');
    const fileInput = document.getElementById('fileInput');
    const imagePreview = document.getElementById('imagePreview');
    const clearButton = document.getElementById('clearButton');
    const fileNameDiv = document.getElementById('fileName');
    dropZone.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', (e) => {
        handleFile(e.target.files[0]);
    });

    function dragOverHandler(event) {
        event.preventDefault();
        dropZone.style.backgroundColor = '#f0f0f0';
    }

    function dropHandler(event) {
        event.preventDefault();
        dropZone.style.backgroundColor = '#fff';

        handleFile(event.dataTransfer.files[0]);
    }

    function handleFile(file) {
        if (file) {
        const reader = new FileReader();
        const fileType = file.type.split('/')[0]; 
        const maxSize = 10 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('File size exceeds the maximum limit of 10 MB.');
            return;
        }

        reader.onload = () => {
           
            fileNameDiv.textContent = 'File uploaded: ' + file.name;

            if (fileType === 'image') {
                
                imagePreview.src = reader.result;
                imagePreview.style.display = 'inline-block';
                clearButton.style.display = 'inline-block'; 
            } else {
               
                imagePreview.style.display = 'none';
               
            }
        };

        reader.readAsDataURL(file);
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);

       
        fileInput.files = dataTransfer.files;
         
        }
    }
    
    clearButton.addEventListener('click', () => {
        imagePreview.src = ''; 
        imagePreview.style.display = 'none';
        fileInput.value = ''; 
        fileNameDiv.textContent = '';
    });

    document.addEventListener('paste', (event) => {
        const items = event.clipboardData.items;
        if (items) {
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    const file = items[i].getAsFile();
                    handleFile(file);
                    break;
                }
            }
        }
    });

    function displayFile(fileLink) {
        const fileType = getFileType(fileLink);
       
        fileNameDiv.textContent = 'File uploaded: ' + getFileName(fileLink);

        if (fileType === 'image') {
        
            imagePreview.src = fileLink;
            imagePreview.style.display = 'inline-block';
          
        } else {
         
            imagePreview.style.display = 'none';
           
            
            const fileExtension = getFileExtension(fileLink);
            if (fileExtension === 'pdf' || fileExtension === 'xlsx' || fileExtension === 'docx') {
               
                const downloadLink = document.createElement('a');
                downloadLink.href = fileLink;
                downloadLink.download = getFileName(fileLink);
                downloadLink.textContent = 'Download ' + fileExtension.toUpperCase();
                fileNameDiv.appendChild(downloadLink);
            }
        }
    }

    function getFileType(fileLink) {
        const fileExtension = getFileExtension(fileLink);
        if (fileExtension === 'jpg' || fileExtension === 'jpeg' || fileExtension === 'png' || fileExtension === 'gif') {
            return 'image';
        } else {
            return 'other';
        }
    }

    function getFileExtension(fileLink) {
        return fileLink.split('.').pop().toLowerCase();
    }

    function getFileName(fileLink) {
        return fileLink.split('/').pop();
    }
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
    let interval;

    $(function() {

        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
            
        });

        const noteTextarea = document.getElementById('note');
        const charCountDiv = document.getElementById('charCount');
        const maxChars = 50;

        noteTextarea.addEventListener('input', function() {
            const currentLength = noteTextarea.value.length;
            
            if (currentLength > maxChars) {
                noteTextarea.value = noteTextarea.value.substring(0, maxChars);
            }

            charCountDiv.textContent = `Characters remaining: ${maxChars - currentLength}`;
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
                $('.icon-stop-start-out').text('stop');
                if($('.icon-stop-start-out').parent().hasClass('green')){
                    $('.icon-stop-start-out').parent().removeClass('green').addClass('red');
                }
                getStream().then(getDevices).then(gotDevices);
            },
            onCloseEnd: function(modal, trigger){
                $('#body-item-update').empty();
                $('#supplierUpdate').text('');
                $('#codeUpdate,#plantUpdate,#warehouseUpdate,#purchaseOrderUpdate,#qtyInUpdate,#unitUpdate').text('');
                $('#qtyOutUpdate,#qtyBalanceUpdate').val('0,000');
                $('#tempPlace').val('');
                $('#tempGoodScale').val('');
                $('#tempType').val('');
                $('#previewImageIn').attr('src','');
                clearGetWeight();
                $('.icon-stop-start-out').text('play_arrow');
                if($('.icon-stop-start-out').parent().hasClass('red')){
                    $('.icon-stop-start-out').parent().removeClass('red').addClass('green');
                }
                $('#videoSource1').empty();
                $('#previewImage1').attr('src','');
                if (window.stream) {
                    window.stream.getTracks().forEach(track => {
                        track.stop();
                    });
                }
                $('.supplier-class').removeClass('hide');
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
                $('.icon-stop-start').text('stop');
                if($('.icon-stop-start').parent().hasClass('green')){
                    $('.icon-stop-start').parent().removeClass('green').addClass('red');
                }
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
                clearButton.click();
                window.onbeforeunload = function() {
                    return null;
                };
                clearGetWeight();
                $('.icon-stop-start').text('play_arrow');
                if($('.icon-stop-start').parent().hasClass('red')){
                    $('.icon-stop-start').parent().removeClass('red').addClass('green');
                }
                $('.row_item').remove();
                $('#videoSource').empty();
                $('#previewImage').attr('src','');
                if (window.stream) {
                    window.stream.getTracks().forEach(track => {
                        track.stop();
                    });
                }
                $('#div-account').removeClass('hide');
                $('#marketing_order_delivery_id').empty();
                $('#type').val('1').formSelect();
            }
        });

        $('#modal2').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) {
                
            },
            onCloseEnd: function(modal, trigger){
                $('#codeUpdateLast,#plantUpdateLast,#warehouseUpdateLast,#purchaseOrderUpdateLast').text('');
                $('#tempGoodScaleLast').val('');
                $('#form_data_last')[0].reset();
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
        /* select2ServerSide('#purchase_order_detail_id', '{{ url("admin/select2/purchase_order_detail") }}'); */
        select2ServerSide('#account_id', '{{ url("admin/select2/supplier") }}');

        $('#purchase_order_detail_id').select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/purchase_order_detail") }}',
                type: 'GET',
                dataType: 'JSON',
                data: function(params) {
                    return {
                        search : params.term,
                        account_id : $('#account_id').val(),
                        item_id : $('#item_id').val(),
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.items
                    }
                }
            }
        });

        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
        });

        $("#table-detail th").resizable({
            minWidth: 100,
        });

        select2ServerSide('#item_id', '{{ url("admin/select2/purchase_item_scale") }}');
        select2ServerSide('#marketing_order_delivery_id', '{{ url("admin/select2/marketing_order_delivery_scale") }}');
    });

    function changeMode(val){
        $('#item_id,#account_id,#purchase_order_detail_id,#marketing_order_delivery_id').empty();
        $('#warehouse_id,#item_unit_id,#delivery_no').val('');
        $('#qty_in,#qty_out').val('0,000');
        if(val == '1'){
            $('.hide-inputs').removeClass('hide');
            $('#is_quality_check').prop( "checked", true);
            $('#marketing_order_delivery_id').parent().addClass('hide');
            select2ServerSide('#account_id', '{{ url("admin/select2/supplier") }}');
        }else if(val == '2'){
            $('#is_quality_check').prop( "checked", false);
            $('.hide-inputs').addClass('hide');
            $('#marketing_order_delivery_id').parent().removeClass('hide');
            select2ServerSide('#account_id', '{{ url("admin/select2/vendor") }}');
        }
    }

    function stopStart(){
        if($('.icon-stop-start').text() == 'stop'){
            $('.icon-stop-start').text('play_arrow');
            if($('.icon-stop-start').parent().hasClass('red')){
                $('.icon-stop-start').parent().removeClass('red').addClass('green');
            }
            clearGetWeight();
        }else{
            $('.icon-stop-start').text('stop');
            if($('.icon-stop-start').parent().hasClass('green')){
                $('.icon-stop-start').parent().removeClass('green').addClass('red');
            }
            getWeight();
        }
    }

    function stopStartOut(){
        if($('.icon-stop-start-out').text() == 'stop'){
            $('.icon-stop-start-out').text('play_arrow');
            if($('.icon-stop-start-out').parent().hasClass('red')){
                $('.icon-stop-start-out').parent().removeClass('red').addClass('green');
            }
            clearGetWeight();
        }else{
            $('.icon-stop-start-out').text('stop');
            if($('.icon-stop-start-out').parent().hasClass('green')){
                $('.icon-stop-start-out').parent().removeClass('green').addClass('red');
            }
            getWeight();
        }
    }

    String.prototype.replaceAt = function(index, replacement) {
        return this.substring(0, index) + replacement + this.substring(index + replacement.length);
    };

    function count(){
        let qtyin = parseFloat($('#qtyInUpdate').text().replaceAll(".", "").replaceAll(",","."));
        let qtyout = parseFloat($('#qtyOutUpdate').val().replaceAll(".", "").replaceAll(",","."));
        let balance = qtyin - qtyout;
        $('#qtyBalanceUpdate').val(formatRupiahIni(balance.toFixed(3).toString().replace('.',',')));
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
                type: 'POST',
                dataType: 'JSON',
                data: {
                    'status' : $('#filter_status').val(),
                    status_qc : $('#filter_status_qc').val(),
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
                { name: 'code', className: 'center-align' },
                { name: 'other', searchable: false, orderable: false, className: 'center-align' },
                { name: 'name', className: 'center-align' },
                { name: 'company_id', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'type', className: 'center-align' },
                { name: 'delivery_no', className: 'center-align' },
                { name: 'vehicle_no', className: 'center-align' },
                { name: 'driver', className: 'center-align' },
                { name: 'note', className: '' },
                { name: 'document', searchable: false, orderable: false, className: 'center-align' },
                { name: 'image_in', searchable: false, orderable: false, className: 'center-align' },
                { name: 'time_scale_in', searchable: false, orderable: false, className: 'center-align' },
                { name: 'is_quality_check', searchable: false, orderable: false, className: 'center-align' },
                { name: 'image_qc', searchable: false, orderable: false, className: 'center-align' },
                { name: 'time_scale_qc', searchable: false, orderable: false, className: 'center-align' },
                { name: 'image_out', searchable: false, orderable: false, className: 'center-align' },
                { name: 'time_scale_out', searchable: false, orderable: false, className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'status_qc', searchable: false, orderable: false, className: 'center-align' },
                { name: 'note_qc', searchable: false, orderable: false, className: 'center-align' },
                { name: 'by', searchable: false, orderable: false, className: 'center-align' },
                { name: 'place_id', searchable: false, orderable: false, className: 'center-align' },
                { name: 'warehouse_id', searchable: false, orderable: false, className: 'center-align' },
                { name: 'item_id', searchable: false, orderable: false, className: 'center-align' },
                { name: 'qty_po', searchable: false, orderable: false, className: 'center-align' },
                { name: 'qty_in', searchable: false, orderable: false, className: 'center-align' },
                { name: 'qty_out', searchable: false, orderable: false, className: 'center-align' },
                { name: 'qty_balance', searchable: false, orderable: false, className: 'center-align' },
                { name: 'qty_qc', searchable: false, orderable: false, className: 'center-align' },
                { name: 'qty_final', searchable: false, orderable: false, className: 'center-align' },
                { name: 'water_content', searchable: false, orderable: false, className: 'center-align' },
                { name: 'viscosity', searchable: false, orderable: false, className: 'center-align' },
                { name: 'residue', searchable: false, orderable: false, className: 'center-align' },
                { name: 'unit', searchable: false, orderable: false, className: 'center-align' },
                { name: 'operation', searchable: false, orderable: false, className: 'right-align' },
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

    function getPurchaseOrderQty(){
        if($('#purchase_order_detail_id').val()){
            $("#qty_po").val($('#purchase_order_detail_id').select2('data')[0].qty);
            $('#item_unit_id').val($('#purchase_order_detail_id').select2('data')[0].item_unit_id);
        }else{
            $("#qty_po").val('0,000');
        }
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
                            if($('#type').val() == '1'){
                                $('#qty_in').val(response);
                            }else{
                                $('#qty_out').val(response);
                            }
                            /* countBalance(); */
                        }
                    }
                    if($('#modal6').hasClass('open')){
                        if($('#tempType').val() == '1'){
                            $('#qtyOutUpdate').val(response);
                        }else{
                            $('#qtyInUpdate').text(response);
                        }
                        countBalance();
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

    function getRowUnit(){
        $("#warehouse_id").empty();
        $('#account_id').empty();
        $("#purchase_order_detail_id").empty().trigger('change');
        $('#div-account').removeClass('hide');
        if($("#item_id").val()){
            if($("#item_id").select2('data')[0].list_warehouse.length > 0){
                $.each($("#item_id").select2('data')[0].list_warehouse, function(i, value) {
                    $('#warehouse_id').append(`
                        <option value="` + value.id + `">` + value.name + `</option>
                    `);
                });
            }else{
                $("#warehouse_id").append(`
                    <option value="">--Gudang tidak diatur di master data Grup Item--</option>
                `);
            }
            $('#item_unit_id').empty();
            if($("#item_id").select2('data')[0].buy_units.length > 0){
                $.each($("#item_id").select2('data')[0].buy_units, function(i, value) {
                    $('#item_unit_id').append(`
                        <option value="` + value.id + `">` + value.code + `</option>
                    `);
                });
            }else{
                $("#item_unit_id").append(`
                    <option value="">--Satuan tidak diatur di master data Item--</option>
                `);
            }
            if($("#item_id").select2('data')[0].is_hide){
                $('#div-account').addClass('hide');
            }else{
                $('#div-account').removeClass('hide');
            }
        }else{
            $("#item_id").empty();
            $("#item_unit_id").empty().append(`
                <option value="">--Silahkan pilih item--</option>
            `);
            $("#warehouse_id").append(`
                <option value="">--Silahkan pilih item--</option>
            `);
        }
        $('#item')
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

    function countBalance(){
        if($('#modal6').hasClass('open')){
            let balance = parseFloat($('#qtyInUpdate').text().replaceAll(".", "").replaceAll(",",".")) - parseFloat($('#qtyOutUpdate').val().replaceAll(".", "").replaceAll(",","."));
            $('#qtyBalanceUpdate').val(
                (balance >= 0 ? '' : '-') + formatRupiahIni(balance.toFixed(3).toString().replace('.',','))
            );
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

                if(passedUnit){
                    if(s){
                        var path = window.location.pathname;
                    path = path.replace(/^\/|\/$/g, '');

                    var segments = path.split('/');
                    var lastSegment = segments[segments.length - 1];
                
                    formData.append('lastsegment',lastSegment);
                    formData.append('image_in', s);
                    
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

                    formData.append('image_out', s);

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
                            loadingOpen('#modal6');
                        },
                        success: function(response) {
                            loadingClose('#modal6');
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
                            loadingClose('#modal6');
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

    function saveUpdateLast(){
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
                
                var formData = new FormData($('#form_data_last')[0]);

                $.ajax({
                    url: '{{ Request::url() }}/save_update_information',
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
                        loadingOpen('#modal2');
                    },
                    success: function(response) {
                        loadingClose('#modal2');
                        if(response.status == 200) {
                            successUpdateLast();
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
                        loadingClose('#modal2');
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
                if(response.status == '5'){
                    swal({
                        title: 'Ups!',
                        text: 'Data telah ditutup.',
                        icon: 'warning'
                    });
                }else{
                    $('#modal6').modal('open');
                    $('#tempPlace').val(response.place_id);
                    $('#tempGoodScale').val(response.id);
                    $('#tempType').val(response.type);
                    $('#previewImageIn').attr('src',response.image_in);
                    $('#codeUpdate').text(response.code);
                    $('#supplierUpdate').text(response.account_name);
                    $('#plantUpdate').text(response.place_code);
                    $('#warehouseUpdate').text(response.warehouse_name);
                    $('#purchaseOrderUpdate').text(response.purchase_code);
                    if(response.type == '1'){
                        $('#qtyInUpdate').text(response.qty_in);
                    }else{
                        $('#qtyOutUpdate').val(response.qty_out);
                    }
                    $('#unitUpdate').text(response.unit);
                    $('#noteUpdate').text(response.note);

                    if(response.is_hide){
                        $('.supplier-class').addClass('hide');
                    }else{
                        $('.supplier-class').removeClass('hide');
                    }
                    
                    $('.modal-content').scrollTop(0);
                    M.updateTextFields();
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

    function updateInformation(id){
        $.ajax({
            url: '{{ Request::url() }}/update_information',
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
                if(response.status == 200){
                    $('#modal2').modal('open');
                    $('#tempGoodScaleLast').val(response.data.id);
                    $('#codeUpdateLast').text(response.data.code);
                    $('#plantUpdateLast').text(response.data.place_code);
                    $('#warehouseUpdateLast').text(response.data.warehouse_name);
                    $('#purchaseOrderUpdateLast').text(response.data.purchase_code);
                    $('#deliveryNoLast').val(response.data.delivery_no);
                    $('#vehicleNoLast').val(response.data.vehicle_no);
                    $('#driverLast').val(response.data.driver);
                    $('#noteUpdateLast').val(response.data.note);
                    
                    $('.modal-content').scrollTop(0);
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

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
    }

    function successUpdate(){
        loadDataTable();
        $('#modal6').modal('close');
    }

    function successUpdateLast(){
        loadDataTable();
        $('#modal2').modal('close');
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
                $('#type').val(response.type).formSelect();
                $('#delivery_no').val(response.delivery_no);
                $('#vehicle_no').val(response.vehicle_no);
                $('#driver').val(response.driver);
                $('#post_date').val(response.post_date);
                $('#qty_po').val(response.qty_po);
                $('#qty_in').val(response.qty_in);
                $('#qty_out').val(response.qty_out);
                $('#company_id').val(response.company_id).formSelect();
                $('#place_id').val(response.place_id).formSelect();
                $('#account_id').empty().append(`
                    <option value="` + response.account_id + `">` + response.account_name + `</option>
                `);
                if(response.is_hide){
                    $('#div-account').addClass('hide');
                }else{
                    $('#div-account').removeClass('hide');
                }
                $('#purchase_order_detail_id').empty().append(`
                    <option value="` + response.purchase_order_detail_id + `">` + response.purchase_code + `</option>
                `);
                $('#warehouse_id').empty();
                $.each(response.list_warehouse, function(i, val) {
                    $('#warehouse_id').append(`
                        <option value="` + val.id + `">` + val.name + `</option>
                    `);
                });
                $('#item_unit_id').empty();
                $.each(response.buy_units, function(i, val) {
                    $('#item_unit_id').append(`
                        <option value="` + val.id + `">` + val.code + `</option>
                    `);
                });
                $('#item_id').append(`
                    <option value="` + response.item_id + `">` + response.item_name + `</option>
                `);

                $('#warehouse_id').val(response.warehouse_id);
                $('#item_unit_id').val(response.item_unit_id);

                if(response.is_quality_check == '1'){
                    $('#is_quality_check').prop( "checked", true);
                }else{
                    $('#is_quality_check').prop( "checked", false);
                }

                if(response.document){
                    const baseUrl = 'http://127.0.0.1:8000/storage/';
                    const filePath = response.document.replace('public/', '');
                    const fileUrl = baseUrl + filePath;
                    displayFile(fileUrl);
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
    function exportExcel(){
        var search = table.search();
        var status = $('#filter_status').val();
        var start_date = $('#start_date').val();
        var end_date = $('#finish_date').val();
        var modedata = '{{ $modedata }}';

        window.location = "{{ Request::url() }}/export_from_page?search=" + search + "&status=" + status + "&end_date=" + end_date + "&start_date=" + start_date + "&modedata=" + modedata;
       
    }
</script>