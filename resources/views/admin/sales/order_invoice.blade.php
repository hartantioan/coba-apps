<script src="{{ url('app-assets/js/sweetalert2.js') }}"></script>
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
                                                <label for="filter_type" style="font-size:1rem;">Tipe :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_type" onchange="loadDataTable()">
                                                        <option value="">{{ __('translations.all') }}</option>
                                                        <option value="1">Cash</option>
                                                        <option value="2">Credit</option>
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
                                                <label for="filter_account" style="font-size:1rem;">Customer :</label>
                                                <div class="input-field">
                                                    <select class="browser-default" id="filter_account" name="filter_account" multiple="multiple" style="width:100% !important;" onchange="loadDataTable()"></select>
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
                                            <div class="card-alert card blue">
                                                <div class="card-content white-text">
                                                    <p>Info : AR Invoice akan otomatis menarik data <i>Nomor Seri Pajak</i> yang anda set di form <b>Master Data - Akunting - Seri Pajak</b>, berdasarkan perusahaan, tanggal berlaku periode (dihitung dari tanggal posting invoice), dan nomor urut yang tersedia. Pastikan anda mengisi master data tersebut.</p>
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
                                                        <th rowspan="2">#</th>
                                                        <th rowspan="2">{{ __('translations.code') }}</th>
                                                        <th rowspan="2">{{ __('translations.user') }}</th>
                                                        <th rowspan="2">{{ __('translations.customer') }}</th>
                                                        <th rowspan="2">Alamat Penagihan & NPWP</th>
                                                        <th rowspan="2">{{ __('translations.company') }}</th>
                                                        <th colspan="3" class="center-align">{{ __('translations.date') }}</th>
                                                        <th rowspan="2">{{ __('translations.type') }}</th>
                                                        <th rowspan="2">Dokumen</th>
                                                        <th rowspan="2">Seri Pajak</th>
                                                        <th rowspan="2">{{ __('translations.note') }}</th>
                                                        <th rowspan="2">{{ __('translations.subtotal') }}</th>
                                                        <th rowspan="2">Downpayment</th>
                                                        <th rowspan="2">{{ __('translations.total') }}</th>
                                                        <th rowspan="2">{{ __('translations.tax') }}</th>
                                                        <th rowspan="2">{{ __('translations.grandtotal') }}</th>
                                                        <th rowspan="2">{{ __('translations.status') }}</th>
                                                        <th rowspan="2">By</th>
                                                        <th rowspan="2">{{ __('translations.action') }}</th>
                                                    </tr>
                                                    <tr>
                                                        <th>Post</th>
                                                        <th>Jatuh Tempo</th>
                                                        <th>Jatuh Tempo Internal</th>
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
                                        <input type="hidden" id="temp" name="temp">
                                        <input type="hidden" id="tempTaxId" name="tempTaxId">
                                        <input type="hidden" id="tempTaxPercent" name="tempTaxPercent">
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
                                        <select class="browser-default" id="account_id" name="account_id" onchange="addDueDate();getDataCustomer();"></select>
                                        <label class="active" for="account_id">{{ __('translations.customer') }}</label>
                                    </div>
                                    <div class="row">
                                        <div class="input-field col m6 s12">
                                            <select class="browser-default" id="marketing_order_delivery_process_id" name="marketing_order_delivery_process_id" onchange="getMarketingOrderDelivery();" ></select>
                                            <label class="active" for="marketing_order_delivery_process_id">Surat Jalan</label>
                                        </div>
                                    </div>
                                    <div class="input-field col m6 s12">
                                        <select class="select2 browser-default" id="user_data_id" name="user_data_id">
                                            <option value="">--Pilih customer ya--</option>
                                        </select>
                                        <label class="active" for="user_data_id">Alamat Tagih & NPWP (Otomatis dari SO paling akhir)</label>
                                    </div>
                                    <div class="input-field col m3 s12 step4">
                                        <select class="form-control" id="company_id" name="company_id">
                                            @foreach ($company as $rowcompany)
                                                <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                            @endforeach
                                        </select>
                                        <label class="" for="company_id">{{ __('translations.company') }}</label>
                                    </div>
                                    <div class="input-field col m3 s12 step6">
                                        <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);">
                                        <label class="active" for="post_date">{{ __('translations.post_date') }}</label>
                                    </div>
                                    <div class="input-field col m3 s12 step7">
                                        <input id="due_date" name="due_date" min="{{ date('Y-m-d') }}" type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. Jatuh Tempo">
                                        <label class="active" for="due_date">Tgl. Jatuh Tempo</label>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <input id="due_date_internal" name="due_date_internal" min="{{ date('Y-m-d') }}" type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. Jatuh Tempo">
                                        <label class="active" for="due_date_internal">Tgl. Jatuh Tempo (Internal)</label>
                                    </div>
                                    {{-- <div class="input-field col m1 s12 step10">
                                        <button class="btn waves-effect waves-light green" onclick="getTaxSeries();"><i class="material-icons">autorenew</i></button>
                                    </div> --}}
                                    <div class="input-field col m3 s12 step5">
                                        <select class="form-control" id="type" name="type">
                                            <option value="1">DP</option>
                                            <option value="2">Credit</option>
                                        </select>
                                        <label class="" for="type">{{ __('translations.type') }}</label>
                                    </div>
                                    <div class="col m4 s12 step10">
                                        <label class="">Bukti Upload</label>
                                        <br>
                                        <input type="file" name="file" id="fileInput" accept="image/*" style="display: none;">
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
                                    <div class="input-field col m9 s12 step2">
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col s12">
                                <fieldset>
                                    <legend>2. Pajak</legend>
                                    <div class="input-field col m3 s12 step11">
                                        <select class="browser-default" id="prefix_tax" name="prefix_tax" onchange="countAll();">
                                            <option value="010">010</option>
                                            <option value="020">020</option>
                                            <option value="030">030</option>
                                            <option value="040">040</option>
                                            <option value="050">050</option>
                                            <option value="060">060</option>
                                            <option value="070">070</option>
                                            <option value="080">080</option>
                                            <option value="090">090</option>
                                        </select>
                                        <label class="active" for="prefix_tax">Kode Transaksi Pajak</label>
                                    </div>
                                    <div class="input-field col m3 s12 step9">
                                        <input id="tax_no" name="tax_no" type="text" readonly placeholder="Auto generate : pajak > 0">
                                        <label class="active" for="tax_no">No. Seri Pajak <i class="material-icons tooltipped" data-position="bottom" data-tooltip="Info : No seri pajak diambil berdasarkan perusahaan dan tanggal posting (berlaku) dokumen." style="margin-left:5px;margin-top: 0px;position: absolute;">help_outline</i></label>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col s12">
                                <fieldset>
                                    <legend>3. Dokumen Terpakai</legend>
                                    <div class="col m3 s12 step11">
                                        <h6>Hapus untuk bisa diakses pengguna lain : <i id="list-used-data"></i></h6>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col s12">
                                <fieldset style="min-width: 100%;overflow:auto;">
                                    <legend>4. Surat Jalan</legend>
                                    <div class="col m12 s12" style="width:2500px !important;" id="table-item">
                                        <p class="mt-2 mb-2">
                                            <table class="bordered" id="table-detail">
                                                <thead>
                                                    <tr>
                                                        <th class="center">Surat Jalan</th>
                                                        <th class="center">{{ __('translations.item') }}</th>
                                                        <th class="center">Qty Terkirim</th>
                                                        <th class="center">Qty Retur</th>
                                                        <th class="center">Qty Sisa</th>
                                                        <th class="center">{{ __('translations.unit') }}</th>
                                                        <th class="center">Harga@</th>
                                                        <th class="center">{{ __('translations.total') }}</th>
                                                        <th class="center">PPN (%)</th>
                                                        <th class="center">Termasuk PPN</th>
                                                        <th class="center">{{ __('translations.tax') }}</th>
                                                        <th class="center">{{ __('translations.grandtotal') }}</th>
                                                        <th class="center">{{ __('translations.note') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="body-item">
                                                    <tr id="last-row-item">
                                                        <td colspan="13">
                                                            Silahkan tambahkan Surat Jalan...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </p>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col s12">
                                <fieldset style="min-width: 100%;overflow:auto;">
                                    <legend>5. AR Down Payment</legend>
                                    <div class="row">
                                        <div class="input-field col m5 step14">
                                            <select class="browser-default" id="marketing_order_down_payment_id" name="marketing_order_down_payment_id"></select>
                                            <label class="active" for="marketing_order_down_payment_id">AR Down Payment</label>
                                        </div>
                                        <div class="col m4 step15">
                                            <a class="waves-effect waves-light cyan btn-small mb-1 mr-1 mt-5" onclick="getMarketingDownPayment();" href="javascript:void(0);">
                                                <i class="material-icons left">add</i> AR Down Payment
                                            </a>
                                        </div>
                                    </div> 
                                    <div class="col m12 s12" style="width:1800px !important;" id="table-dp">
                                        <p class="mt-2 mb-2">
                                            <table class="bordered" id="table-detail1">
                                                <thead>
                                                    <tr>
                                                        <th class="center">No.Dokumen</th>
                                                        <th class="center">Tgl.Posting</th>
                                                        <th class="center">{{ __('translations.subtotal') }}</th>
                                                        <th class="center">Diskon</th>
                                                        <th class="center">{{ __('translations.total') }}</th>
                                                        <th class="center">{{ __('translations.tax') }}</th>
                                                        <th class="center">{{ __('translations.grandtotal') }}</th>
                                                        <th class="center">{{ __('translations.note') }}</th>
                                                        <th class="center">Digunakan</th>
                                                        <th class="center">{{ __('translations.delete') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="body-dp">
                                                    <tr id="last-row-dp">
                                                        <td colspan="10">
                                                            Silahkan tambahkan AR Down Payment...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </p>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="input-field col m4 s12 step16">
                                <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note">{{ __('translations.note') }}</label>
                            </div>
                            <div class="input-field col m4 s12">
                                <div class="card-alert card red" id="textTax" style="display:none;">
                                    <div class="card-content white-text">
                                        <p>Invoice ini akan menerbitkan <b>Nomor Seri Pajak</b>.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="input-field col m4 s12 step17">
                                <table width="100%" class="bordered">
                                    <thead>
                                        <tr>
                                            <td>Subtotal</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="subtotal" name="subtotal" type="text" value="0,00" style="text-align:right;width:100%;" readonly>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Uang Muka (AR DP)</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="downpayment" name="downpayment" type="text" value="0,00" style="text-align:right;width:100%;" readonly>
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
                                            <td>Grandtotal</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="grandtotal" name="grandtotal" type="text" value="0,00" style="text-align:right;width:100%;" readonly>
                                            </td>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="col s12 mt-3">
                                <button class="btn waves-effect waves-light right submit step18" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light purple btn-panduan" onclick="startIntro();">Panduan <i class="material-icons right">help_outline</i></button>
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
                            <th class="center-align" rowspan="2">{{ __('translations.bussiness_partner') }}</th>
                            <th class="center-align" rowspan="2">{{ __('translations.plant') }}</th>
                            <th class="center-align" rowspan="2">{{ __('translations.line') }}</th>
                            <th class="center-align" rowspan="2">{{ __('translations.engine') }}</th>
                            <th class="center-align" rowspan="2">{{ __('translations.division') }}</th>
                            <th class="center-align" rowspan="2">{{ __('translations.warehouse') }}</th>
                            <th class="center-align" rowspan="2">Proyek</th>
                            <th class="center-align" rowspan="2">Ket.1</th>
                            <th class="center-align" rowspan="2">Ket.2</th>
                            <th class="center-align" colspan="2">Mata Uang Asli</th>
                            <th class="center-align" colspan="2">Mata Uang Konversi</th>
                        </tr>
                        <tr>
                            <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Debit</th>
                            <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Kredit</th>
                            <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Debit</th>
                            <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Kredit</th>
                        </tr>
                    
                </thead>
                <tbody id="body-journal-table">
                </tbody>
            </table>
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
        $("#table-detail th,#table-detail1 th").resizable({
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
                $('#temp,#tempTaxId').val('');
                $('#tempTaxPercent').val('0,00');
                $('#account_id,#marketing_order_delivery_process_id,#marketing_order_down_payment_id').empty();
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }
                M.updateTextFields();
                window.onbeforeunload = function() {
                    return null;
                };
                countAll();
                $('#textTax').hide();
                $("#user_data_id").empty().append(`
                    <option value="">--Pilih customer ya--</option>
                `);
                $('#body-item').empty().append(`
                    <tr id="last-row-item">
                        <td colspan="13">
                            Silahkan tambahkan Surat Jalan...
                        </td>
                    </tr>
                `);
                $('#body-dp').empty().append(`
                    <tr id="last-row-dp">
                        <td colspan="10">
                            Silahkan tambahkan AR Down Payment...
                        </td>
                    </tr>
                `);
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

        $('#marketing_order_delivery_process_id').select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/marketing_order_delivery_process") }}',
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

        $('#marketing_order_down_payment_id').select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/marketing_order_down_payment_paid") }}',
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

        $('#body-dp').on('click', '.delete-data-dp', function() {
            $(this).closest('tr').remove();
            if($('.row_dp').length == 0){
                $('#body-dp').append(`
                    <tr id="last-row-dp">
                        <td colspan="10">
                            Silahkan tambahkan AR Down Payment...
                        </td>
                    </tr>
                `);
                $('#marketing_order_down_payment_id').empty();
                $('#table-dp').animate( { 
                    scrollLeft: '0' }, 
                500);
            }
            countAll();
        });
    });

    function getDataCustomer(){
        $('#user_data_id').empty();
        if($('#account_id').val()){
            if($('#account_id').select2('data')[0].billing_address.length > 0){
                $.each($('#account_id').select2('data')[0].billing_address, function(i, val) {
                    $('#user_data_id').append(`
                        <option value="` + val.id + `">` + val.npwp + ` ` + val.address + `</option>
                    `);
                });
            }else{
                $('#user_data_id').append(`
                    <option value="">--Data tidak ditemukan--</option>
                `); 
            }
        }else{
            $('#user_data_id').append(`
                <option value="">--Pilih customer ya--</option>
            `);
        }
    }

    function getMarketingOrderDelivery(){
        if($('.data-used').length > 0){
            $('.data-used').trigger('click');
        }
        if($('#marketing_order_delivery_process_id').val()){
            $.ajax({
                url: '{{ Request::url() }}/send_used_data',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: $('#marketing_order_delivery_process_id').val(),
                    type: 'marketing_order_delivery_processes',
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('#modal1');
                },
                success: function(response) {
                    loadingClose('#modal1');

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

                        let datakuy = $('#marketing_order_delivery_process_id').select2('data')[0];

                        if(datakuy.payment_type){
                            $('#type').val(datakuy.payment_type).formSelect();
                        }

                        if(!$('#account_id').val()){
                            $('#account_id').empty().append(`
                                <option value="` + datakuy.account_id + `">` + datakuy.account_name + `</option>
                            `);
                        }

                        $('#list-used-data').append(`
                            <div class="chip purple darken-4 gradient-shadow white-text">
                                ` + datakuy.code + `
                                <i class="material-icons close data-used" onclick="removeUsedData('` + datakuy.type + `','` + $('#marketing_order_delivery_process_id').val() + `')">close</i>
                            </div>
                        `);

                        $('#user_data_id').val(datakuy.user_data_id).trigger('change');
                        $('#note').val(datakuy.note);

                        addDueDateByValue(datakuy.top_customer);
                        addDueDateByValueInternal(datakuy.top_internal);

                        $.each(datakuy.details, function(i, val) {
                            var count = makeid(10);
                            $('#body-item').append(`
                                <tr class="row_item" data-id="` + $('#marketing_order_delivery_process_id').val() + `">
                                    <input type="hidden" name="arr_lookable_type[]" id="arr_lookable_type` + count + `" value="` + val.lookable_type + `">
                                    <input type="hidden" name="arr_lookable_id[]" id="arr_lookable_id` + count + `" value="` + val.lookable_id + `">
                                    <input type="hidden" name="arr_total[]" id="arr_total` + count + `" value="` + val.total + `">
                                    <input type="hidden" name="arr_tax[]" id="arr_tax` + count + `" value="` + val.tax + `">
                                    <input type="hidden" name="arr_total_after_tax[]" id="arr_total_after_tax` + count + `" value="` + val.grandtotal + `">
                                    <input type="hidden" name="arr_grandtotal[]" class="browser-default" value="0">
                                    <td>
                                        ` + val.code + `
                                    </td>
                                    <td>
                                        ` + val.item_name + `
                                    </td>
                                    <td class="center-align">
                                        ` + val.qty_do + `
                                    </td>
                                    <td class="center-align">
                                        ` + val.qty_return + `
                                    </td>
                                    <td class="center-align">
                                        ` + val.qty_sent + `
                                    </td>
                                    <td class="center-align">
                                        ` + val.unit + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.price + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.total + `
                                    </td>
                                    <td class="center-align">
                                        ` + val.percent_tax + `
                                    </td>
                                    <td class="center-align">
                                        ` + (val.is_include_tax == '1' ? 'Ya' : 'Tidak') + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.tax + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.grandtotal + `
                                    </td>
                                    <td>
                                        ` + val.note + `
                                    </td>
                                </tr>
                            `);
                            $('#tempTaxId').val(val.tax_id);
                            $('#tempTaxPercent').val(val.percent_tax);
                        });

                        /* $('#marketing_order_delivery_process_id').empty(); */

                        countAll();
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
        }
    }

    function getMarketingDownPayment(){
        if($('#marketing_order_down_payment_id').val()){
            $.ajax({
                url: '{{ Request::url() }}/send_used_data',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: $('#marketing_order_down_payment_id').val(),
                    type: 'marketing_order_down_payments',
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('#modal1');
                },
                success: function(response) {
                    loadingClose('#modal1');

                    if(response.status == 500){
                        swal({
                            title: 'Ups!',
                            text: response.message,
                            icon: 'warning'
                        });
                    }else{
                        if($('#last-row-dp').length > 0){
                            $('#last-row-dp').remove();
                        }

                        let datakuy = $('#marketing_order_down_payment_id').select2('data')[0];

                        $('#list-used-data').append(`
                            <div class="chip purple darken-4 gradient-shadow white-text">
                                ` + datakuy.code + `
                                <i class="material-icons close data-used" onclick="removeUsedData('` + datakuy.type + `','` + $('#marketing_order_down_payment_id').val() + `')">close</i>
                            </div>
                        `);

                        /* $('#due_date').val(datakuy.due_date); */

                        var count = makeid(10);

                        $('#body-dp').append(`
                            <tr class="row_dp" data-id="` + $('#marketing_order_down_payment_id').val() + `">
                                <input type="hidden" name="arr_lookable_type[]" id="arr_lookable_type` + count + `" value="` + datakuy.type + `">
                                <input type="hidden" name="arr_lookable_id[]" id="arr_lookable_id` + count + `" value="` + $('#marketing_order_down_payment_id').val() + `">
                                <td class="center-align">
                                    ` + datakuy.code + `
                                </td>
                                <td>
                                    ` + datakuy.post_date + `
                                </td>
                                <td class="right-align">
                                    ` + datakuy.subtotal + `
                                </td>
                                <td class="right-align">
                                    ` + datakuy.discount + `
                                </td>
                                <td class="right-align">
                                    ` + datakuy.total + `
                                </td>
                                <td class="right-align">
                                    ` + datakuy.tax + `
                                </td>
                                <td class="right-align">
                                    ` + datakuy.grandtotal + `
                                </td>
                                <td>
                                    ` + datakuy.note + `
                                </td>
                                <td class="center">
                                    <input name="arr_grandtotal[]" class="browser-default" type="text" value="` + datakuy.balance + `" data-nominal="` + datakuy.balance + `" onkeyup="formatRupiah(this);countAll();checkRow('` + count + `')" style="text-align:right;width:100% !important;" id="arr_grandtotal`+ count +`">
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-dp" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);

                        countAll();

                        $('#marketing_order_down_payment_id').empty();
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
        }
    }

    function addDueDate(){
        if($('#account_id').val()){
            var result = new Date($('#post_date').val());
            result.setDate(result.getDate() + parseInt($('#account_id').select2('data')[0].top_customer));
            $('#due_date').val(result.toISOString().split('T')[0]);
        }else{
            $('#due_date').val(null);
        }
    }

    function addDueDateByValue(val){
        if($('#account_id').val()){
            var result = new Date($('#post_date').val());
            result.setDate(result.getDate() + parseInt(val));
            $('#due_date').val(result.toISOString().split('T')[0]);
        }else{
            $('#due_date').val(null);
        }
    }

    function addDueDateByValueInternal(val){
        if($('#account_id').val()){
            var result = new Date($('#post_date').val());
            result.setDate(result.getDate() + parseInt(val));
            $('#due_date_internal').val(result.toISOString().split('T')[0]);
        }else{
            $('#due_date_internal').val(null);
        }
    }

    function checkRow(val){
        var total = parseFloat($('#arr_grandtotal' + val).val().replaceAll(".", "").replaceAll(",",".")), 
            totalLimit = parseFloat($('#arr_grandtotal' + val).data('nominal').toString().replaceAll(".", "").replaceAll(",","."));

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

    function getTaxSeries(){
        if($('#company_id').val() && !$('#temp').val()){
            $.ajax({
                url: '{{ Request::url() }}/get_tax_series',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    company_id: $('#company_id').val(),
                    date: $('#post_date').val(),
                    prefix_tax: $('#prefix_tax').val(),
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    loadingClose('.modal-content');
                    if(response.status == 200){
                        $('#tax_no').val(response.no);
                    }else{
                        M.toast({
                            html: response.message
                        });
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
                if(type == 'marketing_order_delivery_processes'){
                    $('.row_item[data-id="' + id + '"]').remove();
                    if($('.row_item').length == 0){
                        $('#body-item').empty().append(`
                            <tr id="last-row-item">
                                <td colspan="13">
                                    Silahkan tambahkan Surat Jalan...
                                </td>
                            </tr>
                        `);
                    }
                }else if(type == 'marketing_order_down_payments'){
                    $('.row_dp[data-id="' + id + '"]').remove();
                    if($('.row_dp').length == 0){
                        $('#body-dp').empty().append(`
                            <tr id="last-row-dp">
                                <td colspan="10">
                                    Silahkan tambahkan AR Down Payment...
                                </td>
                            </tr>
                        `);
                    }
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
            "order": [[0, 'desc']],
            "fixedColumns": {
                left: 2,
                right: 1
            },
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
                { name: 'user_data_id', className: '' },
                { name: 'company_id', className: '' },
                { name: 'post_date', className: '' },
                { name: 'due_date', className: '' },
                { name: 'due_date_internal', className: '' },
                { name: 'type', className: '' },
                { name: 'document', searchable: false, orderable: false, className: '' },
                { name: 'tax_no', className: '' },
                { name: 'note', className: '' },
                { name: 'subtotal', className: 'right-align' },
                { name: 'downpayment', className: 'right-align' },
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
                loadingOpen('#main');
            },
            complete: function() {
                
                loadingClose('#main');
            },
            success: function(response) {
                window.open(response.message, '_blank');
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
                var formData = new FormData($('#form_data')[0]), passed = true, passedTax = true;

                $('input[name^="arr_qty"]').each(function(index){
                    if(parseFloat($(this).val().replaceAll(".", "").replaceAll(",",".")) == 0){
                        passed = false;
                    }
                });

                /* if(parseFloat($('#tax').val().replaceAll(".", "").replaceAll(",",".")) > 0){
                    if(!$('#tax_no').val()){
                        passedTax = false;
                    }
                } */
                
                if(passed){
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
                                    $('input').css('border', 'none');
                                    $('input').css('border-bottom', '0.5px solid black');
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
                            text: 'Nomor seri tidak boleh kosong, ketika nominal PPN diatas 0.',
                            icon: 'warning'
                        });
                    }
                }else{
                    swal({
                        title: 'Ups!',
                        text: 'Qty tidak boleh kosong.',
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
                $('#due_date').val(response.due_date);
                $('#due_date_internal').val(response.due_date_internal);
                $('#tax_no').val(response.tax_no);
                $('#note').val(response.note);
                $('#subtotal').val(response.subtotal);
                $('#tax').val(response.tax);
                $('#total').val(response.total);
                $('#grandtotal').val(response.grandtotal);
                $('#downpayment').val(response.downpayment);
                $('#tax_no').val(response.tax_no);
                $('#type').val(response.type).formSelect();
                $('#marketing_order_delivery_process_id').empty();
                if(response.modp_code){
                    $('#marketing_order_delivery_process_id').append(`
                        <option value="` + response.marketing_order_delivery_process_id + `">` + response.modp_code + `</option>
                    `);
                }

                $('#user_data_id').empty();
                if(response.user_datas.length > 0){
                    $.each(response.user_datas, function(i, val) {
                        $('#user_data_id').append(`
                            <option value="` + val.id + `">` + val.npwp + ` ` + val.address + `</option>
                        `);
                    });

                    $('#user_data_id').val(response.user_data_id).trigger('change');
                }else{
                    $('#user_data_id').append(`
                        <option value="">--Data tidak ditemukan--</option>
                    `); 
                }

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
                                <input type="hidden" name="arr_total[]" id="arr_total` + count + `" value="` + val.total + `">
                                <input type="hidden" name="arr_tax[]" id="arr_tax` + count + `" value="` + val.tax + `">
                                <input type="hidden" name="arr_total_after_tax[]" id="arr_total_after_tax` + count + `" value="` + val.grandtotal + `">
                                <input type="hidden" name="arr_grandtotal[]" class="browser-default" value="0">
                                <td>
                                    ` + val.code + `
                                </td>
                                <td>
                                    ` + val.item_name + `
                                </td>
                                <td class="center-align">
                                    ` + val.qty_do + `
                                </td>
                                <td class="center-align">
                                    ` + val.qty_return + `
                                </td>
                                <td class="center-align">
                                    ` + val.qty_sent + `
                                </td>
                                <td class="center-align">
                                    ` + val.unit + `
                                </td>
                                <td class="right-align">
                                    ` + val.price + `
                                </td>
                                <td class="right-align">
                                    ` + val.total + `
                                </td>
                                <td class="center-align">
                                    ` + val.percent_tax + `
                                </td>
                                <td class="center-align">
                                    ` + (val.is_include_tax == '1' ? 'Ya' : 'Tidak') + `
                                </td>
                                <td class="right-align">
                                    ` + val.tax + `
                                </td>
                                <td class="right-align">
                                    ` + val.grandtotal + `
                                </td>
                                <td>
                                    ` + val.note + `
                                </td>
                            </tr>
                        `);
                    });
                }

                if(response.dps.length > 0){
                    if($('#last-row-dp').length > 0){
                        $('#last-row-dp').remove();
                    }
                    $.each(response.dps, function(i, val) {
                        var count = makeid(10);
                        $('#body-dp').append(`
                            <tr class="row_dp" data-id="` + val.id + `">
                                <input type="hidden" name="arr_lookable_type[]" id="arr_lookable_type` + count + `" value="` + val.type + `">
                                <input type="hidden" name="arr_lookable_id[]" id="arr_lookable_id` + count + `" value="` + val.id + `">
                                <td class="center-align">
                                    ` + val.code + `
                                </td>
                                <td>
                                    ` + val.post_date + `
                                </td>
                                <td class="right-align">
                                    ` + val.subtotal + `
                                </td>
                                <td class="right-align">
                                    ` + val.discount + `
                                </td>
                                <td class="right-align">
                                    ` + val.total + `
                                </td>
                                <td class="right-align">
                                    ` + val.tax + `
                                </td>
                                <td class="right-align">
                                    ` + val.grandtotal + `
                                </td>
                                <td>
                                    ` + val.note + `
                                </td>
                                <td class="center">
                                    <input name="arr_grandtotal[]" class="browser-default" type="text" value="` + val.total_used + `" data-nominal="` + val.balance + `" onkeyup="formatRupiah(this);countAll();checkRow('` + count + `')" style="text-align:right;width:100% !important;" id="arr_grandtotal`+ count +`">
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-dp" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
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
        let total = 0, tax = 0, subtotal = 0, grandtotal = 0, downpayment = 0, percentTax = parseFloat($('#tempTaxPercent').val());

        $('input[name^="arr_total[]"]').each(function(index){
            subtotal += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
        });

        $('input[name^="arr_grandtotal[]"]').each(function(index){
            downpayment += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
        });

        total = subtotal - downpayment;

        tax = Math.floor(total * (percentTax / 100));

        grandtotal = total + tax;

        $('#subtotal').val(
            (subtotal >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(subtotal).toString().replace('.',','))
        );

        $('#downpayment').val(
            (downpayment >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(downpayment).toString().replace('.',','))
        );

        $('#total').val(
            (total >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(total).toString().replace('.',','))
        );
        
        $('#tax').val(
            (tax >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(tax).toString().replace('.',','))
        );

        $('#grandtotal').val(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(grandtotal).toString().replace('.',','))
        );

        if(subtotal > 0){
            $('#textTax').show();
            getTaxSeries();
        }else{
            $('#textTax').hide();
            $('#tax_no').val('');
        }
    }

    function startIntro(){
        introJs().setOptions({
            exitOnOverlayClick : false,
            steps: [
                {
                    title : 'AR Invoice',
                    intro : 'Form ini digunakan untuk mengelola data invoice penagihan ke customer yang diambil dari lebih dari satu data Surat Jalan (DO) dikurangi dengan barang yang diretur. Anda juga bisa menambahkan dokumen AR Down Payment disini sebagai pemotong tagihan. Di form ini terdapat fitur auto-generate nomor seri pajak yang diambil dari form Master Data - Akunting - Seri Pajak sesuai dengan perusahaan dan tanggal berlaku.'
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
                    title : 'Tipe',
                    element : document.querySelector('.step5'),
                    intro : 'Tipe pembayaran cash atau kredit.' 
                },
                {
                    title : 'Tgl. Posting',
                    element : document.querySelector('.step6'),
                    intro : 'Tanggal post akan menentukan tanggal jurnal untuk beberapa form yang terhubung dengan jurnal. Hati - hati dalam menentukan tanggal posting.' 
                },
                {
                    title : 'Tgl. Jatuh Tempo',
                    element : document.querySelector('.step7'),
                    intro : 'Tanggal tenggat adalah batas tanggal berlaku untuk invoice yang dibuat.' 
                },
                {
                    title : 'Nomor Seri PPN',
                    element : document.querySelector('.step9'),
                    intro : 'Nomor seri PPN yang otomatis terbuat ketika nominal pajak diatas 0. Data ini diambil dari perusahaan dan tanggal posting yang diserasikan dengan data pada Master Data - Akunting - Seri Pajak.' 
                },
                {
                    title : 'File Lampiran',
                    element : document.querySelector('.step10'),
                    intro : 'File bukti lampiran yang ingin digunakan sebagai bukti. Hanya bisa mengupload 1 file saja.' 
                },
                {
                    title : 'Data SJ Terpakai',
                    element : document.querySelector('.step11'),
                    intro : 'Daftar SJ terpakai, fungsinya untuk mengunci data agar hanya terpakai pada form dan user aktif saat ini saja. Silahkan hapus jika ingin diakses oleh user lainnya.' 
                },
                {
                    title : 'Surat Jalan',
                    element : document.querySelector('.step12'),
                    intro : 'Daftar surat jalan yang ingin dimasukkan ke dalam pengembalian. Anda bisa menambahkan lebih dari satu surat jalan dan barang, namun hanya untuk 1 Partner Bisnis yang sama.' 
                },
                {
                    title : 'Tambah Surat Jalan',
                    element : document.querySelector('.step13'),
                    intro : 'Tombol untuk menambahkan surat jalan terpilih.'
                },
                {
                    title : 'AR Down Payment',
                    element : document.querySelector('.step14'),
                    intro : 'Daftar AR Down Payment yang digunakan untuk memotong tagihan AR Invoice.' 
                },
                {
                    title : 'Tambah AR Down Payment',
                    element : document.querySelector('.step15'),
                    intro : 'Tombol untuk menambahkan down payment terpilih.'
                },
                {
                    title : 'Keterangan',
                    element : document.querySelector('.step16'),
                    intro : 'Silahkan isi / tambahkan keterangan untuk dokumen ini untuk dimunculkan di bagian bawah tabel detail produk nantinya, ketika dicetak.' 
                },
                {
                    title : 'Informasi Nominal',
                    element : document.querySelector('.step17'),
                    intro : 'Nominal yang ada disini tidak bisa dirubah, karena otomatis dihitung dari dokumen surat jalan dan down payment yang ada pada tabel sebelumnya.' 
                },
                {
                    title : 'Tombol Simpan',
                    element : document.querySelector('.step18'),
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

    function cancelStatus(id){
        Swal.fire({
            title: "Pilih tanggal tutup!",
            input: "date",
            showCancelButton: true,
            confirmButtonText: "Lanjut",
            cancelButtonText: "Batal",
            cancelButtonColor: "#d33",
            confirmButtonColor: "#3085d6",
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: '{{ Request::url() }}/cancel_status',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { id : id, cancel_date : result.value },
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

    function exportExcel(){
        var search = table.search();
        var status = $('#filter_status').val();
        var type = $('#filter_type').val();
        var account = $('#filter_account').val();
        var company = $('#filter_company').val();
        var start_date = $('#start_date').val();
        var end_date = $('#finish_date').val();

        window.location = "{{ Request::url() }}/export_from_page?search=" + search + "&status=" + status + "&type=" + type + "&account=" + account + "&company=" + company  + "&end_date=" + end_date + "&start_date=" + start_date;
       
    }
</script>