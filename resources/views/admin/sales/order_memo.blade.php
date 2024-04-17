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

    .select-wrapper, .select2-container {
        height:3.6rem !important;
    }

    .select2-container {
        min-width: 0px !important;
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
                                                <label for="filter_account" style="font-size:1rem;">Customer :</label>
                                                <div class="input-field">
                                                    <select class="browser-default" id="filter_account" name="filter_account" multiple="multiple" style="width:100% !important;" onchange="loadDataTable()"></select>
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
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-2" href="javascript:void(0);" onclick="exportExcel();">
                                                <i class="material-icons hide-on-med-and-up">view_headline</i>
                                                <span class="hide-on-small-onl">Export</span>
                                                <i class="material-icons right">view_headline</i>
                                            </a>
                                            <table id="datatable_serverside">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Code</th>
                                                        <th>Pengguna</th>
                                                        <th>Customer</th>
                                                        <th>Perusahaan</th>
                                                        <th>Tgl.Post</th>
                                                        <th>Tipe</th>
                                                        <th>Dokumen</th>
                                                        <th>No.Seri Pajak</th>
                                                        <th>Keterangan</th>
                                                        <th>Total</th>
                                                        <th>PPN</th>
                                                        <th>Grandtotal</th>
                                                        <th>Status</th>
                                                        <th>By</th>
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
                                        <input type="hidden" id="temp" name="temp">
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
                                        <select class="browser-default" id="account_id" name="account_id"></select>
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
                                        <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);">
                                        <label class="active" for="post_date">Tgl. Posting</label>
                                    </div>
                                    <div class="col m12 s12 l12"></div>
                                    <div class="input-field col m3 s12 step6">
                                        <select class="form-control" id="type" name="type" onchange="resetItem();">
                                            <option value="1">Potongan Nominal (Invoice)</option>
                                            <option value="2">Potongan Qty (Invoice)</option>
                                            <option value="3">Mandiri</option>
                                        </select>
                                        <label class="" for="type">Tipe Memo</label>
                                    </div>
                                    <div class="input-field col m3 s12 step7">
                                        <input id="tax_no" name="tax_no" type="text" placeholder="Nomor faktur pajak dari Customer">
                                        <label class="active" for="tax_no">No. Seri Pajak</label>
                                    </div>
                                    <div class="col m4 s12 step8">
                                        <label class="">Bukti Upload</label>
                                        <br>
                                        <input type="file" name="file" id="fileInput" accept="image/*" style="display: none;">
                                        <div  class="col m8 s12 " id="dropZone" ondrop="dropHandler(event);" ondragover="dragOverHandler(event);" style="margin-top: 0.5em;height: 5em;">
                                            Drop image here or <a href="#" id="uploadLink">upload</a>
                                            <br>
                                            
                                        </div>
                                        <a class="waves-effect waves-light cyan btn-small" style="margin-top: 0.5em;margin-left:0.2em" id="clearButton" href="javascript:void(0);">
                                           Clear
                                        </a>
                                    </div>
                                    <div class="col m4 s4 l4"></div>
            
                                    <div class="col m4 s12">
                                        <div id="fileName"></div>
                                        <img src="" alt="Preview" id="imagePreview" style="display: none;">
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col s12">
                                <fieldset>
                                    <legend>2. Dokumen Terpakai</legend>
                                    <div class="col m3 s12 step9">
                                        <h6>Hapus untuk bisa diakses pengguna lain : <i id="list-used-data"></i></h6>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col s12">
                                <fieldset style="min-width: 100%;overflow:auto;">
                                    <legend>3. Detail Dokumen Terpakai</legend>
                                    <div class="row">
                                        <div class="input-field col m2 step10">
                                            <select class="browser-default" id="marketing_order_invoice_id" name="marketing_order_invoice_id"></select>
                                            <label class="active" for="marketing_order_invoice_id">AR Invoice</label>
                                        </div>
                                        <div class="col m1 pt-1">
                                            <a class="btn-floating mb-1 waves-effect waves-light" onclick="getMarketingInvoice();" href="javascript:void(0);">
                                                <i class="material-icons">add</i>
                                            </a>
                                        </div>
                                        <div class="col m9">
                                            Untuk tipe Memo : potongan nominal dan potongan qty, maka anda bisa mengambil data dari AR Invoice.
                                        </div>
                                    </div> 
                                    <div class="col m12 s12 step11" style="max-width:2500px !important;" id="table-item">
                                        <p class="mt-2 mb-2">
                                            <table class="bordered" id="table-detail">
                                                <thead>
                                                    <tr>
                                                        <th class="center">Dokumen</th>
                                                        <th class="center">Seri Pajak</th>
                                                        <th class="center">Tgl.Post</th>
                                                        <th class="center">Keterangan</th>
                                                        <th class="center">Item</th>
                                                        <th class="center">Qty</th>
                                                        <th class="center">Satuan</th>
                                                        <th class="center">Total</th>
                                                        <th class="center">PPN</th>
                                                        <th class="center">Grandtotal (Sisa)</th>
                                                        <th class="center">Hapus</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="body-item">
                                                    <tr id="last-row-item">
                                                        <td colspan="11">
                                                            Silahkan tambahkan AR Invoice
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </p>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="input-field col m4 s12 step12">
                                <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note">Keterangan</label>
                            </div>
                            <div class="input-field col m2 s12">

                            </div>
                            <div class="input-field col m6 s12 step13">
                                <table width="100%" class="bordered">
                                    <thead>
                                        <tr class="row-mandiri" style="display:none;">
                                            <td style="font-size: 15px !important;" width="20%">Total</td>
                                            <td colspan="2" width="40%">
                                                <input class="browser-default" id="temptotal" name="temptotal" type="text" value="0,00" style="text-align:right;width:100%;font-size: 20px !important;border: 2px solid rgb(1, 184, 25);border-radius: 4px;" onkeyup="formatRupiah(this);countAll();">
                                            </td>
                                            <td class="right-align" width="40%">
                                                <input class="browser-default" id="total" name="total" type="text" value="0,00" style="text-align:right;width:100%;font-size: 20px !important;" readonly onkeyup="formatRupiah(this);countAll();">
                                            </td>
                                        </tr>
                                        <tr class="row-mandiri" style="display:none;">
                                            <td style="font-size: 15px !important;">PPN</td>
                                            <td style="font-size: 15px !important;">
                                                <select class="browser-default" id="tax_id" name="tax_id" onchange="countAll();">
                                                    <option value="0" data-id="0">-- Pilih ini jika non-PPN --</option>
                                                    @foreach ($tax as $row)
                                                        <option value="{{ $row->percentage }}" {{ $row->is_default_ppn ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td style="font-size: 15px !important;">
                                                <label>
                                                    <input type="checkbox" id="is_include_tax" name="is_include_tax[]" value="1" onclick="countAll();">
                                                    <span>Incl.PPN</span>
                                                </label>
                                            </td>
                                            <td class="right-align">
                                                <input class="browser-default" id="tax" name="tax" type="text" value="0,00" style="text-align:right;width:100%;font-size: 20px !important;" readonly onkeyup="formatRupiah(this);">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-size: 15px !important;" colspan="3">Grandtotal</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="grandtotal" name="grandtotal" type="text" value="0,00" style="text-align:right;width:100%;font-size: 20px !important;" readonly onkeyup="formatRupiah(this);">
                                            </td>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="col s12 mt-3">
                                <button class="btn waves-effect waves-light right submit step14" onclick="save();">Simpan <i class="material-icons right">send</i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light purple btn-panduan" onclick="startIntro();">Panduan <i class="material-icons right">help_outline</i></button>
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
                    
                }
            },
            onCloseEnd: function(modal, trigger){
                clearButton.click();
                $('#form_data')[0].reset();
                $('#temp').val('');
                $('#account_id').empty();
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }else{
                    $('#body-item').empty().append(`
                        <tr id="last-row-item">
                            <td colspan="11">
                                Silahkan tambahkan AR Invoice
                            </td>
                        </tr>
                    `);
                }
                M.updateTextFields();
                window.onbeforeunload = function() {
                    return null;
                };
                $('.row-mandiri').hide();
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

        select2ServerSide('#account_id,#filter_account', '{{ url("admin/select2/customer") }}');
        select2ServerSide('#coa_id', '{{ url("admin/select2/coa") }}');

        $('#marketing_order_invoice_id').select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/marketing_order_invoice") }}',
                type: 'GET',
                dataType: 'JSON',
                data: function(params) {
                    return {
                        search: params.term,
                        account_id: $('#account_id').val(),
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.items
                    }
                }
            }
        });

        $('#account_id').on('change', function() {
            if(!$(this).val()){
                $('#marketing_order_down_payment_id,#marketing_order_delivery_process_id').empty().trigger('change');
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }
            }
        });

        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
            countAll();
            if($('.row_item').length == 0){
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');   
                }                
                $('#table-item').animate( { 
                    scrollLeft: '0' }, 
                500);
            }
        });
    });

    function resetItem(){
        if($('.data-used').length > 0){
            $('.data-used').trigger('click');   
        }
        $('#body-item').empty().append(`
            <tr id="last-row-item">
                <td colspan="11">
                    Silahkan tambahkan AR Invoice
                </td>
            </tr>
        `);
        if($('#type').val() == '3'){
            $('.row-mandiri').show();
            $(".modal-content").animate({ scrollTop: $("#temptotal").height() + 500 }, "fast");
            /* $('#grandtotal').attr('readonly',false); */

        }else{
            $('.row-mandiri').hide();
            /* $('#grandtotal').attr('readonly',true); */
        }
    }

    function getMarketingInvoice(){
        if($('#type').val() !== '3'){
            if($('#marketing_order_invoice_id').val()){
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }

                let datakuy = $('#marketing_order_invoice_id').select2('data')[0];

                $.ajax({
                    url: '{{ Request::url() }}/send_used_data',
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        id: $('#marketing_order_invoice_id').val(),
                        type: datakuy.type,
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
                            if($('#last-row-item').length > 0){
                                $('#last-row-item').remove();
                            }

                            $('#list-used-data').append(`
                                <div class="chip purple darken-4 gradient-shadow white-text">
                                    ` + datakuy.code + `
                                    <i class="material-icons close data-used" onclick="removeUsedData('` + datakuy.type + `','` + $('#marketing_order_invoice_id').val() + `')">close</i>
                                </div>
                            `);

                            $.each(datakuy.details, function(i, val) {
                                var count = makeid(10);
                                $('#body-item').append(`
                                    <tr class="row_item" data-id="` + $('#marketing_order_invoice_id').val() + `">
                                        <input type="hidden" name="arr_lookable_type[]" id="arr_lookable_type` + count + `" value="` + val.type + `">
                                        <input type="hidden" name="arr_lookable_id[]" id="arr_lookable_id` + count + `" value="` + val.id + `">
                                        <input type="hidden" name="arr_is_include_tax[]" id="arr_is_include_tax` + count + `" value="` + val.is_include_tax + `">
                                        <input type="hidden" name="arr_percent_tax[]" id="arr_percent_tax` + count + `" value="` + val.percent_tax + `">
                                        <input type="hidden" name="arr_tax_id[]" id="arr_tax_id` + count + `" value="` + val.tax_id + `">
                                        <td>
                                            ` + val.code + `
                                        </td>
                                        <td>
                                            ` + val.tax_no + `
                                        </td>
                                        <td class="center">
                                            ` + datakuy.post_date + `
                                        </td>
                                        <td>
                                            <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan detail...">
                                        </td>
                                        <td>
                                            ` + val.name + `
                                        </td>
                                        <td>
                                            <input name="arr_qty[]" id="rowQty` + count + `" type="text" value="` + ($('#type').val() == '2' ? val.qty : '0' ) + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" data-qty="` + ($('#type').val() == '2' ? val.qty : '0' ) + `" style="text-align:right;" ` + ($('#type').val() == '1' ? 'readonly' : '') + `>
                                        </td>
                                        <td class="center">
                                            <span>` + val.unit + `</span>
                                        </td>
                                        <td class="center">
                                            <input name="arr_total[]" class="browser-default" type="text" value="0,00" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100% !important;" id="arr_total`+ count +`" readonly>
                                        </td>
                                        <td class="center">
                                            <input name="arr_tax[]" class="browser-default" type="text" value="0,00" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100% !important;" id="arr_tax`+ count +`" readonly>
                                        </td>
                                        <td class="center">
                                            <input name="arr_grandtotal[]" class="browser-default" type="text" value="` + val.balance + `" data-nominal="` + val.balance + `" onkeyup="formatRupiah(this);countAll();checkRow('` + count + `')" style="text-align:right;width:100% !important;" id="arr_grandtotal`+ count +`">
                                        </td>
                                        <td class="center">
                                            <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                                <i class="material-icons">delete</i>
                                            </a>
                                        </td>
                                    </tr>
                                `);
                            });

                            $('#marketing_order_invoice_id').empty();

                            countAll();
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
        }else{
            $('#marketing_order_invoice_id').empty();
            swal({
                title: 'Ups!',
                text: 'Mohon maaf, tipe mandiri tidak dapat menambahkan AR Invoice.',
                icon: 'warning'
            });
        }
    }

    function checkRow(val){
        var total = parseFloat($('#arr_grandtotal' + val).val().replaceAll(".", "").replaceAll(",",".")), 
            totalLimit = parseFloat($('#arr_grandtotal' + val).data('nominal').replaceAll(".", "").replaceAll(",","."));

        if(totalLimit > 0){
            if(total > totalLimit){
                total = totalLimit;
                $('#arr_grandtotal' + val).val(formatRupiahIni(total.toFixed(2).toString().replace('.',',')));
            }
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
                $('.row_item[data-id="' + id + '"]').remove();
                if($('.row_item').length == 0){
                    $('#body-item').empty().append(`
                        <tr id="last-row-item">
                            <td colspan="11">
                                Silahkan tambahkan AR Invoice
                            </td>
                        </tr>
                    `);
                }
                
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
                type: 'GET',
                data: {
                    'status[]' : $('#filter_status').val(),
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
                { name: 'code', className: '' },
                { name: 'user_id', className: '' },
                { name: 'account_id', className: '' },
                { name: 'company_id', className: '' },
                { name: 'post_date', className: '' },
                { name: 'type', className: '' },
                { name: 'document', searchable: false, orderable: false, className: '' },
                { name: 'tax_no', className: '' },
                { name: 'note', className: '' },
                { name: 'total', className: 'right-align' },
                { name: 'tax', className: 'right-align' },
                { name: 'grandtotal', className: 'right-align' },
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
                
                /* if($('#type').val() == '3'){
                    swal({
                        title: 'Ups!',
                        text: 'Untuk AR Memo tipe Mandiri masih belum bisa digunakan ya.',
                        icon: 'warning'
                    });
                }else{ */
                    var formData = new FormData($('#form_data')[0]), passedTax = true;
                
                    $('input[name^="arr_tax[]"]').each(function(index){
                        if(parseFloat($(this).val().replaceAll(".", "").replaceAll(",",".")) > 0){
                            if(!$('#tax_no').val()){
                                passedTax = false;
                            }
                        }
                    });

                    formData.append('real_tax',($('#type').val() == '3' ? ($('#tax_id').val() ? $('#tax_id').find(':selected').data('id') : '' ) : ''));
                    
                    if(passedTax){
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
                            text: 'Nomor seri referensi pajak tidak boleh kosong, ketika nominal PPN diatas 0.',
                            icon: 'warning'
                        });
                    }
                /* } */
    
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
                if(response.document){
                    const baseUrl = 'http://127.0.0.1:8000/storage/';
                    const filePath = response.document.replace('public/', '');
                    const fileUrl = baseUrl + filePath;
                    displayFile(fileUrl);
                }
                $('#modal1').modal('open');
                $('#temp').val(id);
                $('#code_place_id').val(response.code_place_id).formSelect();
                $('#code').val(response.code);
                $('#account_id').empty();
                $('#account_id').append(`
                    <option value="` + response.account_id + `">` + response.account_name + `</option>
                `);
                $('#company_id').val(response.company_id).formSelect();
                $('#post_date').val(response.post_date);
                $('#tax_no').val(response.tax_no);
                $('#note').val(response.note);
                $('#grandtotal').val(response.balance);
                if(response.type == '3'){
                    $('#temptotal,#total').val(response.total);
                    $('#tax').val(response.tax);
                    $('.row-mandiri').show();
                    if(response.tax_id){
                        $("#tax_id option[data-id='" + response.tax_id + "']").prop("selected",true);
                    }
                }
                $('#type').val(response.type).formSelect();

                if(response.details.length > 0){
                    if($('.data-used').length > 0){
                        $('.data-used').trigger('click');
                    }
                    if($('#last-row-item').length > 0){
                        $('#last-row-item').remove();
                    }
                    $.each(response.used, function(i, val) {
                        $('#list-used-data').append(`
                            <div class="chip purple darken-4 gradient-shadow white-text">
                                ` + val.code + `
                                <i class="material-icons close data-used" onclick="removeUsedData('` + val.type + `','` + val.id + `')">close</i>
                            </div>
                        `);
                    });
                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        $('#body-item').append(`
                            <tr class="row_item" data-id="` + val.id + `">
                                <input type="hidden" name="arr_lookable_type[]" id="arr_lookable_type` + count + `" value="` + val.lookable_type + `">
                                <input type="hidden" name="arr_lookable_id[]" id="arr_lookable_id` + count + `" value="` + val.lookable_id + `">
                                <input type="hidden" name="arr_is_include_tax[]" id="arr_is_include_tax` + count + `" value="` + val.is_include_tax + `">
                                <input type="hidden" name="arr_percent_tax[]" id="arr_percent_tax` + count + `" value="` + val.percent_tax + `">
                                <input type="hidden" name="arr_tax_id[]" id="arr_tax_id` + count + `" value="` + val.tax_id + `">
                                <td>
                                    ` + val.code + `
                                </td>
                                <td>
                                    ` + val.tax_no + `
                                </td>
                                <td class="center">
                                    ` + val.post_date + `
                                </td>
                                <td>
                                    <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan detail..." value="` + val.note + `">
                                </td>
                                <td>
                                        ` + val.item_name + `
                                </td>
                                <td>
                                    <input name="arr_qty[]" id="rowQty` + count + `" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" data-qty="` + val.qty + `" style="text-align:right;" ` + (response.type == '1' ? 'readonly' : '') + `>
                                </td>
                                <td class="center">
                                    <span>` + val.unit + `</span>
                                </td>
                                <td class="center">
                                    <input name="arr_total[]" class="browser-default" type="text" value="` + val.total + `" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100% !important;" id="arr_total`+ count +`" readonly>
                                </td>
                                <td class="center">
                                    <input name="arr_tax[]" class="browser-default" type="text" value="` + val.tax + `" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100% !important;" id="arr_tax`+ count +`" readonly>
                                </td>
                                <td class="center">
                                    <input name="arr_grandtotal[]" class="browser-default" type="text" value="` + val.grandtotal + `" data-nominal="` + val.grandtotal + `" onkeyup="formatRupiah(this);countAll();checkRow('` + count + `');" style="text-align:right;width:100% !important;" id="arr_grandtotal`+ count +`">
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                    });
                }
                countAll();
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
            qtylimit = parseFloat($('#rowQty' + id).data('qty').toString().replaceAll(".", "").replaceAll(",","."));

        if(qtylimit > 0){
            if(qty > qtylimit){
                qty = qtylimit;
                $('#rowQty' + id).val(formatRupiahIni(qty.toFixed(3).toString().replace('.',',')));
            }
        }

        let bobot = qty / qtylimit;
        let grandtotal = parseFloat($('#arr_grandtotal' + id).data('nominal').toString().replaceAll(".", "").replaceAll(",","."));
        let newgrandtotal = grandtotal * bobot;
        $('#arr_grandtotal' + id).val(formatRupiahIni(newgrandtotal.toFixed(2).toString().replace('.',',')));
        countAll();
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

    function countAll(){
        let grandtotal = 0, tax = 0;

        if($('#type').val() == '3'){
            let total = parseFloat($('#temptotal').val().replaceAll(".", "").replaceAll(",","."));
            if($('#tax_id').val() !== '0'){
                let percent_tax = parseFloat($('#tax_id').val());
                if($('#is_include_tax').is(':checked')){
                    total = Math.round(((total / (1 + (percent_tax / 100))) * 100) / 100);
                }
                tax = total * (percent_tax / 100);
            }
            $('#total').val(
                (total >= 0 ? '' : '-') + formatRupiahIni(total.toFixed(2).toString().replace('.',','))
            );
            $('#tax').val(
                (tax >= 0 ? '' : '-') + formatRupiahIni(tax.toFixed(2).toString().replace('.',','))
            );
            grandtotal = total + tax;
        }else{
            $('input[name^="arr_grandtotal[]"]').each(function(index){
                let rowgrandtotal = parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
                let percentTax = parseFloat($('input[name^="arr_percent_tax[]"]').eq(index).val());
                let rowtotal = rowgrandtotal;
                let rowtax = 0;
                if(percentTax > 0){
                    rowtotal = rowgrandtotal / ((percentTax + 100) / 100);
                    rowtax = rowtotal * (percentTax / 100);
                }
                $('input[name^="arr_total[]"]').eq(index).val(formatRupiahIni(rowtotal.toFixed(2).toString().replace('.',',')));
                $('input[name^="arr_tax[]"]').eq(index).val(formatRupiahIni(rowtax.toFixed(2).toString().replace('.',',')));
                grandtotal += rowgrandtotal;
            });
        }

        $('#grandtotal').val(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(grandtotal.toFixed(2).toString().replace('.',','))
        );
    }

    function startIntro(){
        introJs().setOptions({
            exitOnOverlayClick : false,
            steps: [
                {
                    title : 'AR Memo',
                    intro : 'Form ini digunakan untuk mengelola data credit memo untuk dokumen AR Invoice dan Piutang Usaha dari sebuah Partner Bisnis.'
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
                    intro : 'Partner bisnis tipe Pelanggan. Silahkan pilih sesuai dengan customer yang ingin ditagihkan invoice. Hati-hati ketika memilih partner bisnis maka, daftar surat jalan akan terfilter sesuai BP ini.' 
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
                    title : 'Tipe Memo',
                    element : document.querySelector('.step6'),
                    intro : 'Tipe memo dibagi menjadi 3, yang pertama adalah Tipe Nominal (Invoice) dimana memo akan memotong secara nominal AR Invoice yang terpilih. Tipe kedua adalah Tipe Qty (Invoice) dimana memo akan memotong secara qty dan nominal proporsional sesuai sisa tagihan AR Invoice terpilih. Tipe yang ketiga adalah Tipe Mandiri, yang mana digunakan untuk menerbitkan potongan penjualan untuk beberapa AR Invoice secara nominal rupiah. Khusus untuk tipe ketiga AR Memo bisa ditarik ke Kas / Bank Masuk untuk memotong piutang usaha, dan bisa ditarik ke Payment Request untuk dibayarkan kembali kepada customer.' 
                },
                {
                    title : 'Nomor Seri PPN dari Customer',
                    element : document.querySelector('.step7'),
                    intro : 'Nomor seri PPN yang anda dapatkan dari Customer sebagai referensi pemotongan pajak.' 
                },
                {
                    title : 'File Lampiran',
                    element : document.querySelector('.step8'),
                    intro : 'File bukti lampiran yang ingin digunakan sebagai bukti. Hanya bisa mengupload 1 file saja.' 
                },
                {
                    title : 'Data Dokumen Terpakai',
                    element : document.querySelector('.step9'),
                    intro : 'Daftar dokumen terpakai, fungsinya untuk mengunci data agar hanya terpakai pada form dan user aktif saat ini saja. Silahkan hapus jika ingin diakses oleh user lainnya.' 
                },
                {
                    title : 'AR Invoice',
                    element : document.querySelector('.step10'),
                    intro : 'Daftar AR Invoice yang ingin ditambahkan untuk dipotong.' 
                },
                {
                    title : 'Tabel Detail',
                    element : document.querySelector('.step11'),
                    intro : 'Daftar sementara informasi dokumen / coa yang ditambahkan dari menu sebelumnya.' 
                },
                {
                    title : 'Keterangan',
                    element : document.querySelector('.step12'),
                    intro : 'Silahkan isi / tambahkan keterangan untuk dokumen ini untuk dimunculkan di bagian bawah tabel detail produk nantinya, ketika dicetak.' 
                },
                {
                    title : 'Informasi Nominal',
                    element : document.querySelector('.step13'),
                    intro : 'Nominal yang ada disini tidak bisa dirubah, karena otomatis dihitung dari dokumen pada tabel sebelumnya.' 
                },
                {
                    title : 'Tombol Simpan',
                    element : document.querySelector('.step14'),
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
</script>