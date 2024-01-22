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

    .switch {
        height: 3.45rem !important;
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
                                            <div class="col m3 s6 ">
                                                <label for="filter_account" style="font-size:1rem;">Supplier/Vendor :</label>
                                                <div class="input-field">
                                                    <select class="browser-default" id="filter_account" name="filter_account" multiple="multiple" style="width:100% !important;" onchange="loadDataTable()"></select>
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
                                    <div class="row">
                                        <div class="col s12">
                                            <div class="card-alert card red">
                                                <div class="card-content white-text">
                                                    <p>Info : AP Memo ketika menarik AP Invoice yang berisi data Good Receipt PO maka qty dari gudang akan dikurangi sesuai qty yang diinputkan pada memo.</p>
                                                </div>
                                            </div>
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
                                                        <th>No</th>
                                                        <th>Pengguna</th>
                                                        <th>Partner Bisnis</th>
                                                        <th>Perusahaan</th>
                                                        <th>Tgl.Post</th>
                                                        <th>No.Pajak Balikan</th>
                                                        <th>Tgl.Retur</th>
                                                        <th>Keterangan</th>
                                                        <th>Total</th>
                                                        <th>PPN</th>
                                                        <th>PPh</th>
                                                        <th>Pembulatan</th>
                                                        <th>Grandtotal</th>
                                                        <th>Dokumen</th>
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
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Tambah/Edit {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
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
                                <select class="browser-default" id="account_id" name="account_id"></select>
                                <label class="active" for="account_id">Partner Bisnis</label>
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
                            <div class="input-field col m3 s12 step6"> 
                                <input id="return_tax_no" name="return_tax_no" type="text" placeholder="Nomor Faktur Pajak Balikan">
                                <label class="active" for="return_tax_no">No. Faktur Pajak Balikan</label>
                            </div>
                            <div class="input-field col m3 s12 step7"> 
                                <input id="return_date" name="return_date" type="date" max="{{ date('Y'.'-12-31') }}" >
                                <label class="active" for="return_date">Tgl. Retur</label>
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
                            <div class="col m12 s12">
                                <div class="col m3 s4 step9">
                                    <p class="mt-2 mb-2">
                                        <h6>A/P Invoice</h6>
                                        <div class="row">
                                            <div class="input-field col m12 s12">
                                                <select class="browser-default" id="purchase_invoice_id" name="purchase_invoice_id"></select>
                                                <label class="active" for="purchase_invoice_id">A/P Invoice (Jika ada)</label>
                                            </div>
                                            <div class="col m12 12">
                                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="getDetails('pi');" href="javascript:void(0);">
                                                    <i class="material-icons left">add</i> A/P Invoice
                                                </a>
                                            </div>
                                        </div> 
                                    </p>
                                </div>
                                <div class="col m3 s4 step10">
                                    <p class="mt-2 mb-2">
                                        <h6>Purchase Down Payment (Uang Muka PO)</h6>
                                        <div class="row">
                                            <div class="input-field col m12 s12">
                                                <select class="browser-default" id="purchase_down_payment_id" name="purchase_down_payment_id"></select>
                                                <label class="active" for="purchase_down_payment_id">Purchase Down Payment (Jika ada)</label>
                                            </div>
                                            <div class="col m12 12">
                                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="getDetails('podp');" href="javascript:void(0);">
                                                    <i class="material-icons left">add</i> PO DP
                                                </a>
                                            </div>
                                        </div>
                                    </p>
                                </div>
                                <div class="col m6 s4">
                                    <h6><b>PI/PODP Terpakai</b> (hapus untuk bisa diakses pengguna lain) : <i id="list-used-data"></i></h6>
                                </div>
                            </div>
                            <div class="col m12 s12 step11">
                                <p class="mt-2 mb-2">
                                    <h6>Detail Transaksi</h6>
                                    <div style="overflow:auto;">
                                        <table class="bordered" id="table-detail" style="min-width:2500px;">
                                            <thead>
                                                <tr>
                                                    <th class="center">Ref.Dokumen / Deskripsi</th>
                                                    <th class="center">No. Faktur Pajak</th>
                                                    <th class="center">No. Bukti Potong</th>
                                                    <th class="center">Tgl. Bukti Potong</th>
                                                    <th class="center">No. SPK</th>
                                                    <th class="center">No. Invoice Vendor</th>
                                                    <th class="center">Tgl.Post</th>
                                                    <th class="center">Keterangan 1</th>
                                                    <th class="center">Keterangan 2</th>
                                                    <th class="center">Edit Qty</th>
                                                    <th class="center">Edit Nominal</th>
                                                    <th class="center">Total</th>
                                                    <th class="center">PPN</th>
                                                    <th class="center">PPh</th>
                                                    <th class="center">Grandtotal</th>
                                                    <th class="center">Hapus</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-detail">
                                                <tr id="last-row-detail">
                                                    <td colspan="16" class="center">
                                                        Silahkan pilih A/P Invoice atau A/P Down Payment
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </p>
                            </div>
                            <div class="input-field col m4 s12 step12">
                                <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note">Keterangan</label>
                            </div>
                            <div class="input-field col m4 s12">

                            </div>
                            <div class="input-field col m4 s12 step13">
                                <table width="100%" class="bordered">
                                    <thead>
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
                                            <td>Pembulatan</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="rounding" name="rounding" type="text" value="0,00" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100%;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Grandtotal</td>
                                            <td class="right-align"><span id="grandtotal">0,00</span></td>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="col s12 mt-3 ">
                                <button class="btn waves-effect waves-light right submit step14" onclick="save();">Simpan <i class="material-icons right">send</i></button>
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
        <div class="col s12" id="show_structure">
            <div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;">

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
                            <th class="center-align" rowspan="2">No</th>
                            <th class="center-align" rowspan="2">Coa</th>
                            <th class="center-align" rowspan="2">Partner Bisnis</th>
                            <th class="center-align" rowspan="2">Plant</th>
                            <th class="center-align" rowspan="2">Line</th>
                            <th class="center-align" rowspan="2">Mesin</th>
                            <th class="center-align" rowspan="2">Department</th>
                            <th class="center-align" rowspan="2">Gudang</th>
                            <th class="center-align" rowspan="2">Proyek</th>
                            <th class="center-align" rowspan="2">Keterangan</th>
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
                $('#due_date').attr('min','{{ date("Y-m-d") }}');
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
                $('.row_purchase').each(function(){
                    $(this).remove();
                });
                $('input').css('border', 'none');
                $('input').css('border-bottom', '0.5px solid black');
                M.updateTextFields();
                $('#account_id').empty();
                $('#total,#tax,#grandtotal').text('0,000');
                $('#subtotal').val('0,000');
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }

                $('.row_detail').remove();
                if($('#last-row-detail').length == 0){
                    $('#body-detail').append(`
                        <tr id="last-row-detail">
                            <td colspan="16" class="center">
                                Silahkan pilih A/P Invoice atau A/P Down Payment
                            </td>
                        </tr>
                    `);
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

        $('#modal4_1').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#show_detail').empty();
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

        $('#purchase_invoice_id').select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/purchase_invoice_memo") }}',
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

        $('#purchase_down_payment_id').select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/purchase_down_payment_memo") }}',
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

        $('#body-detail').on('click', '.delete-data-detail', function() {
            $(this).closest('tr').remove();
            if($('.row_detail').length == 0){
                $('#body-detail').append(`
                    <tr id="last-row-detail">
                        <td colspan="16" class="center">
                            Silahkan pilih A/P Invoice atau A/P Down Payment
                        </td>
                    </tr>
                `);
            }
            countAll();
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

    function getDetails(type){

        let nil;

        if(type == 'pi'){
            nil = $('#purchase_invoice_id').val();
        }else if(type == 'podp'){
            nil = $('#purchase_down_payment_id').val();
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

                        $('#last-row-detail').remove();

                        $('#list-used-data').append(`
                            <div class="chip purple darken-4 gradient-shadow white-text">
                                ` + response.rawcode + `
                                <i class="material-icons close data-used" onclick="removeUsedData('` + response.id + `','` + response.type + `')">close</i>
                            </div>
                        `);

                        if(type == 'podp'){
                            var count = makeid(10);
                            $('#body-detail').append(`
                                <tr class="row_detail" data-id="` + response.id + `">
                                    <input type="hidden" name="arr_type[]" value="` + response.type + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_code[]" value="` + response.id + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_is_include_tax[]" value="` + response.is_include_tax + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_id_tax[]" value="` + response.tax_id + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_id_wtax[]" value="` + response.wtax_id + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_percent_tax[]" value="` + response.percent_tax + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_percent_wtax[]" value="` + response.percent_wtax + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_limit[]" value="` + response.balance + `" data-id="` + count + `">
                                    <td>
                                        ` + response.rawcode + `
                                    </td>
                                    <td>
                                        ` + response.tax_no + `
                                    </td>
                                    <td>
                                        ` + response.tax_cut_no + `
                                    </td>
                                    <td>
                                        ` + response.cut_date + `
                                    </td>
                                    <td>
                                        ` + response.spk_no + `
                                    </td>
                                    <td>
                                        ` + response.invoice_no + `
                                    </td>
                                    <td>
                                        ` + response.post_date + `
                                    </td>
                                    <td>
                                        <input name="arr_description[]" type="text" placeholder="Keterangan 1" value="` + response.note + `">
                                    </td>
                                    <td>
                                        <input name="arr_description2[]" type="text" placeholder="Keterangan 2" value="` + response.note2 + `">
                                    </td>
                                    <td>
                                        <input type="text" name="arr_qty[]" onfocus="emptyThis(this);" value="1" data-id="` + count + `" onkeyup="formatRupiah(this);countQty(this);" style="text-align:right;" data-max="1" readonly>
                                    </td>
                                    <td>
                                        <input type="text" name="arr_nominal[]" onfocus="emptyThis(this);" value="` + response.balanceformat + `" data-id="` + count + `" onkeyup="formatRupiah(this);countNominal(this);" style="text-align:right;" data-max="` + response.balanceformat + `">
                                    </td>
                                    <td>
                                        <input type="text" name="arr_total[]" onfocus="emptyThis(this);" value="` + response.balanceformat + `" data-id="` + count + `" onkeyup="formatRupiah(this);" style="text-align:right;" readonly>
                                    </td>
                                    <td>
                                        <input type="text" name="arr_tax[]" onfocus="emptyThis(this);" value="` + response.tax + `" data-id="` + count + `" onkeyup="formatRupiah(this);" style="text-align:right;">
                                    </td>
                                    <td>
                                        <input type="text" name="arr_wtax[]" onfocus="emptyThis(this);" value="` + response.wtax + `" data-id="` + count + `" onkeyup="formatRupiah(this);" style="text-align:right;">
                                    </td>
                                    <td>
                                        <input type="text" name="arr_grandtotal[]" onfocus="emptyThis(this);" value="` + response.grandtotal + `" data-id="` + count + `" onkeyup="formatRupiah(this);" style="text-align:right;" readonly>
                                    </td>
                                    <td class="center">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                </tr>
                            `);
                        }else if(type == 'pi'){
                            $.each(response.details, function(i, val) {
                                var count = makeid(10);
                                $('#body-detail').append(`
                                    <tr class="row_detail" data-id="` + response.id + `">
                                        <input type="hidden" name="arr_type[]" value="` + val.type + `" data-id="` + count + `">
                                        <input type="hidden" name="arr_code[]" value="` + val.id + `" data-id="` + count + `">
                                        <input type="hidden" name="arr_is_include_tax[]" value="` + val.is_include_tax + `" data-id="` + count + `">
                                        <input type="hidden" name="arr_id_tax[]" value="` + val.tax_id + `" data-id="` + count + `">
                                        <input type="hidden" name="arr_id_wtax[]" value="` + val.wtax_id + `" data-id="` + count + `">
                                        <input type="hidden" name="arr_percent_tax[]" value="` + val.percent_tax + `" data-id="` + count + `">
                                        <input type="hidden" name="arr_percent_wtax[]" value="` + val.percent_wtax + `" data-id="` + count + `">
                                        <input type="hidden" name="arr_limit[]" value="` + val.balance + `" data-id="` + count + `">
                                        <td>
                                            ` + response.rawcode + `
                                        </td>
                                        <td>
                                            ` + response.tax_no + `
                                        </td>
                                        <td>
                                            ` + response.tax_cut_no + `
                                        </td>
                                        <td>
                                            ` + response.cut_date + `
                                        </td>
                                        <td>
                                            ` + response.spk_no + `
                                        </td>
                                        <td>
                                            ` + response.invoice_no + `
                                        </td>
                                        <td>
                                            ` + val.post_date + `
                                        </td>
                                        <td>
                                            <input name="arr_description[]" type="text" placeholder="Keterangan 1" value="` + val.note + `">
                                        </td>
                                        <td>
                                            <input name="arr_description2[]" type="text" placeholder="Keterangan 2" value="` + val.note2 + `">
                                        </td>
                                        <td>
                                            <input type="text" name="arr_qty[]" onfocus="emptyThis(this);" value="` + val.qty + `" data-id="` + count + `" onkeyup="formatRupiah(this);countQty(this);" style="text-align:right;" data-max="` + val.qty + `">
                                        </td>
                                        <td>
                                            <input type="text" name="arr_nominal[]" onfocus="emptyThis(this);" value="` + val.balanceformat + `" data-id="` + count + `" onkeyup="formatRupiah(this);countNominal(this);" style="text-align:right;" data-max="` + val   .balanceformat + `">
                                        </td>
                                        <td>
                                            <input type="text" name="arr_total[]" onfocus="emptyThis(this);" value="` + val.balanceformat + `" data-id="` + count + `" onkeyup="formatRupiah(this);" style="text-align:right;" readonly>
                                        </td>
                                        <td>
                                            <input type="text" name="arr_tax[]" onfocus="emptyThis(this);" value="` + val.tax + `" data-id="` + count + `" onkeyup="formatRupiah(this);" style="text-align:right;" readonly>
                                        </td>
                                        <td>
                                            <input type="text" name="arr_wtax[]" onfocus="emptyThis(this);" value="` + val.wtax + `" data-id="` + count + `" onkeyup="formatRupiah(this);" style="text-align:right;" readonly>
                                        </td>
                                        <td>
                                            <input type="text" name="arr_grandtotal[]" onfocus="emptyThis(this);" value="` + val.grandtotal + `" data-id="` + count + `" onkeyup="formatRupiah(this);" style="text-align:right;" readonly>
                                        </td>
                                        <td class="center">
                                            <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                                                <i class="material-icons">delete</i>
                                            </a>
                                        </td>
                                    </tr>
                                `);
                            });
                        }
                    }
                    M.updateTextFields();
                    $('#purchase_invoice_id,#purchase_down_payment_id').empty();
                    countAll();
                    $('#account_id').empty().append(`
                        <option value="` + response.account_id + `">` + response.account_name + `</option>
                    `);
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
            
        }
    }

    function countQty(element){
        let qty = parseFloat($(element).val().replaceAll(".", "").replaceAll(",","."));
        let max = parseFloat($(element).data('max').replaceAll(".", "").replaceAll(",","."));
        let id = $(element).data('id');
        if(qty > max){
            qty = max;
            $(element).val($(element).data('max'));
        }
        let bobot = qty / max;
        let newTotal = bobot * parseFloat($('input[name^="arr_nominal[]"][data-id="' + $(element).data('id') + '"]').data('max').replaceAll(".", "").replaceAll(",","."));
        $('input[name^="arr_nominal[]"][data-id="' + $(element).data('id') + '"]').val(
            (newTotal >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(newTotal).toString().replace('.',','))
        );
        countAll();
    }

    function countNominal(element){
        let qty = parseFloat($(element).val().replaceAll(".", "").replaceAll(",","."));
        let max = parseFloat($(element).data('max').replaceAll(".", "").replaceAll(",","."));
        let id = $(element).data('id');
        if(qty > max){
            qty = max;
            $(element).val($(element).data('max'));
        }
        let bobot = qty / max;
        let newTotal = bobot * parseFloat($('input[name^="arr_qty[]"][data-id="' + $(element).data('id') + '"]').data('max').replaceAll(".", "").replaceAll(",","."));
        $('input[name^="arr_qty[]"][data-id="' + $(element).data('id') + '"]').val(
            (newTotal >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(newTotal).toString().replace('.',','))
        );
        countAll();
    }

    function countAll(){
        var total = 0, tax = 0, grandtotal = 0, balance = 0, wtax = 0, rounding = parseFloat($('#rounding').val().replaceAll(".", "").replaceAll(",","."));
        
        if($('input[name^="arr_code"]').length > 0){
            $('input[name^="arr_code"]').each(function(){
                let element = $(this);
                var rowgrandtotal = 0, rowtotal = 0, rowtax = 0, rowwtax = 0, percent_tax = parseFloat($('input[name^="arr_percent_tax"][data-id="' + element.data('id') + '"]').val().replaceAll(".", "").replaceAll(",",".")), percent_wtax = parseFloat($('input[name^="arr_percent_wtax"][data-id="' + element.data('id') + '"]').val().replaceAll(".", "").replaceAll(",",".")), rowlimit = parseFloat($('input[name^="arr_limit"][data-id="' + element.data('id') + '"]').val().replaceAll(".", "").replaceAll(",","."));
                rowtotal = parseFloat($('input[name^="arr_nominal"][data-id="' + element.data('id') + '"]').val().replaceAll(".", "").replaceAll(",","."));
                if(percent_tax > 0 && $('input[name^="arr_is_include_tax"][data-id="' + element.data('id') + '"]').val() == '1'){
                    rowtotal = rowtotal / (1 + (percent_tax / 100));
                }
                if(rowtotal > rowlimit){
                    rowtotal = rowlimit;
                    $('input[name^="arr_nominal"][data-id="' + element.data('id') + '"]').val(
                        (rowtotal >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(rowtotal).toString().replace('.',','))
                    );
                }
                rowtax = (percent_tax / 100) * rowtotal;
                rowwtax = (percent_wtax / 100) * rowtotal;
                rowgrandtotal = rowtotal + rowtax - rowwtax;
                total += rowtotal;
                tax += rowtax;
                wtax += rowwtax;
                grandtotal += rowgrandtotal;
                $('input[name^="arr_total"][data-id="' + element.data('id') + '"]').val(
                    (rowtotal >= 0 ? '' : '-') + formatRupiahIni(rowtotal.toFixed(2).toString().replace('.',','))
                );
                $('input[name^="arr_tax"][data-id="' + element.data('id') + '"]').val(
                    (rowtax >= 0 ? '' : '-') + formatRupiahIni(rowtax.toFixed(2).toString().replace('.',','))
                );
                $('input[name^="arr_wtax"][data-id="' + element.data('id') + '"]').val(
                    (rowwtax >= 0 ? '' : '-') + formatRupiahIni(rowwtax.toFixed(2).toString().replace('.',','))
                );
                $('input[name^="arr_grandtotal"][data-id="' + element.data('id') + '"]').val(
                    (rowgrandtotal >= 0 ? '' : '-') + formatRupiahIni(rowgrandtotal.toFixed(2).toString().replace('.',','))
                );
            });
        }

        grandtotal += rounding;

        $('#total').text(
            (total >= 0 ? '' : '-') + formatRupiahIni(total.toFixed(2).toString().replace('.',','))
        );
        $('#tax').text(
            (tax >= 0 ? '' : '-') + formatRupiahIni(tax.toFixed(2).toString().replace('.',','))
        );
        $('#wtax').text(
            (wtax >= 0 ? '' : '-') + formatRupiahIni(wtax.toFixed(2).toString().replace('.',','))
        );
        $('#grandtotal').text(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(grandtotal.toFixed(2).toString().replace('.',','))
        );
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
                $('.row_detail[data-id="' + id + '"]').remove();
                $('#last-row-detail').remove();
                if($('.row_detail').length == 0 || $('#last-row-detail').length == 0){
                    $('#body-detail').append(`
                        <tr id="last-row-detail">
                            <td colspan="16" class="center">
                                Silahkan pilih A/P Invoice atau A/P Down Payment
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
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    'status' : $('#filter_status').val(),
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
                { name: 'code', className: 'center-align' },
                { name: 'user_id', className: 'center-align' },
                { name: 'account_id', className: 'center-align' },
                { name: 'company_id', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'return_tax_no', className: 'center-align' },
                { name: 'return_date', className: 'center-align' },
                { name: 'note', className: 'center-align' },
                { name: 'total', className: 'right-align' },
                { name: 'tax', className: 'right-align' },
                { name: 'wtax', className: 'right-align' },
                { name: 'rounding', className: 'right-align' },
                { name: 'grandtotal', className: 'right-align' },
                { name: 'document', searchable: false, orderable: false, className: 'center-align' },
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
                var formData = new FormData($('#form_data')[0]);

                var passed = true;

                $('input[name^="arr_code"]').each(function(i){
                    if($('input[name^="arr_description"]').eq(i).val() == '' || $('input[name^="arr_total"]').eq(i).val() == '' || $('input[name^="arr_tax"]').eq(i).val() == '' || $('input[name^="arr_wtax"]').eq(i).val() == '' || $('input[name^="arr_grandtotal"]').eq(i).val() == '' || $('input[name^="arr_qty"]').eq(i).val() == ''){
                        passed = false;
                    }                    
                });

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
                    M.toast({
                        html: 'Silahkan cek detail form anda. Pastikan tidak ada data yang kosong.'
                    });
                }
            }
        });
    }

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
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
                $('#type').val(response.type).formSelect();
                $('#company_id').val(response.company_id).formSelect();
                $('#post_date').val(response.post_date);
                $('#return_tax_no').val(response.return_tax_no);
                $('#return_date').val(response.return_date);
                
                $('#note').val(response.note);
                
                if(response.details.length > 0){
                    $('#last-row-detail').remove();
                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        $('#body-detail').append(`
                            <tr class="row_detail" data-id="` + response.id + `">
                                <input type="hidden" name="arr_type[]" value="` + val.type + `" data-id="` + count + `">
                                <input type="hidden" name="arr_code[]" value="` + val.id + `" data-id="` + count + `">
                                <input type="hidden" name="arr_is_include_tax[]" value="` + val.is_include_tax + `" data-id="` + count + `">
                                <input type="hidden" name="arr_id_tax[]" value="` + val.tax_id + `" data-id="` + count + `">
                                <input type="hidden" name="arr_id_wtax[]" value="` + val.wtax_id + `" data-id="` + count + `">
                                <input type="hidden" name="arr_percent_tax[]" value="` + val.percent_tax + `" data-id="` + count + `">
                                <input type="hidden" name="arr_percent_wtax[]" value="` + val.percent_wtax + `" data-id="` + count + `">
                                <input type="hidden" name="arr_limit[]" value="` + val.balance + `" data-id="` + count + `">
                                <td>
                                    ` + val.rawcode + `
                                </td>
                                <td>
                                    ` + val.tax_no + `
                                </td>
                                <td>
                                    ` + val.tax_cut_no + `
                                </td>
                                <td>
                                    ` + val.cut_date + `
                                </td>
                                <td>
                                    ` + val.spk_no + `
                                </td>
                                <td>
                                    ` + val.invoice_no + `
                                </td>
                                <td>
                                    ` + val.post_date + `
                                </td>
                                <td>
                                    <input name="arr_description[]" type="text" placeholder="Keterangan 1" value="` + val.note + `">
                                </td>
                                <td>
                                    <input name="arr_description[]" type="text" placeholder="Keterangan 2" value="` + val.note2 + `">
                                </td>
                                <td>
                                    <input type="text" name="arr_qty[]" onfocus="emptyThis(this);" value="` + val.qty + `" data-id="` + count + `" onkeyup="formatRupiah(this);countQty(this);" style="text-align:right;" data-max="` + val.qty_max + `" readonly>
                                </td>
                                <td>
                                    <input type="text" name="arr_nominal[]" onfocus="emptyThis(this);" value="` + val.total + `" data-id="` + count + `" onkeyup="formatRupiah(this);countNominal(this);" style="text-align:right;" data-max="` + val.balanceformat + `">
                                </td>
                                <td>
                                    <input type="text" name="arr_total[]" onfocus="emptyThis(this);" value="` + val.total + `" data-id="` + count + `" onkeyup="formatRupiah(this);" style="text-align:right;" readonly>
                                </td>
                                <td>
                                    <input type="text" name="arr_tax[]" onfocus="emptyThis(this);" value="` + val.tax + `" data-id="` + count + `" onkeyup="formatRupiah(this);" style="text-align:right;" readonly>
                                </td>
                                <td>
                                    <input type="text" name="arr_wtax[]" onfocus="emptyThis(this);" value="` + val.wtax + `" data-id="` + count + `" onkeyup="formatRupiah(this);" style="text-align:right;" readonly>
                                </td>
                                <td>
                                    <input type="text" name="arr_grandtotal[]" onfocus="emptyThis(this);" value="` + val.grandtotal + `" data-id="` + count + `" onkeyup="formatRupiah(this);" style="text-align:right;" readonly>
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
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
                countAll();
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
                    title : 'Memo',
                    intro : 'Form ini digunakan untuk membuat memo dari down payment atau purchase invoice yang terkait '
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
                    title : 'Perusahaan',
                    element : document.querySelector('.step4'),
                    intro : 'Perusahaan tempat memo ini dibuat atau diperuntukkan' 
                },
                {
                    title : 'Tgl. Posting',
                    element : document.querySelector('.step5'),
                    intro : 'Tanggal post akan menentukan tanggal jurnal untuk beberapa form yang terhubung dengan jurnal. Hati - hati dalam menentukan tanggal posting.' 
                },
                {
                    title : 'Faktur Pajak Balikan',
                    element : document.querySelector('.step6'),
                    intro : 'Nomor faktur pajak balikan.' 
                },
                {
                    title : 'Tgl. Retur Dokumen Pajak',
                    element : document.querySelector('.step7'),
                    intro : 'Tanggal retur dokumen pajak balikan.' 
                },
                {
                    title : 'File Lampiran',
                    element : document.querySelector('.step8'),
                    intro : 'Silahkan unggah file lampiran. untuk saat ini hanya bisa mengakomodir 1 file lampiran saja. Jika ingin menambahkan file lebih dari 1, silahkan gabungkan file anda menjadi pdf.' 
                },
                {
                    title : 'A/P Invoice',
                    element : document.querySelector('.step9'),
                    intro : 'Berfungsi untuk menambahkan invoice yang masih memiliki sisa saldo yang perlu dibayar' 
                },
                {
                    title : 'Purchase Down Payment',
                    element : document.querySelector('.step10'),
                    intro : 'Berfungsi untuk menambahkan Down Payment yang belum digunakan sebelumnya untuk ditambahkan di memo.' 
                },
                {
                    title : 'Detail Transaksi',
                    element : document.querySelector('.step11'),
                    intro : 'Menampilkan Invoice dan PODP yang telah ditambahkan pada inputan AP/invoice & PODP',
                },
                {
                    title : 'Keterangan',
                    element : document.querySelector('.step12'),
                    intro : 'Silahkan isi / tambahkan keterangan untuk dokumen ini untuk dimunculkan di bagian bawah tabel detail produk nantinya, ketika dicetak.'
                },
                {
                    title : 'Total PPN PPh Grandtotal',
                    element : document.querySelector('.step13'),
                    intro : 'Menampilkan ppn pph total dari semua detail terkait secara terotomasi',
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
</script>