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
                                    <div class="row mt-2">
                                        <div class="col s12">
                                            <div id="datatable_buttons"></div>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">Refresh</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <table id="datatable_serverside" class="display responsive-table wrap">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="2">#</th>
                                                        <th rowspan="2">Pengguna</th>
                                                        <th rowspan="2">Supplier/Vendor</th>
                                                        <th rowspan="2">Perusahaan</th>
                                                        <th rowspan="2">Code</th>
                                                        <th rowspan="2">Penerima</th>
                                                        <th colspan="2" class="center-align">Tanggal</th>
                                                        <th rowspan="2">Keterangan</th>
                                                        <th rowspan="2">No.Surat Jalan</th>
                                                        <th rowspan="2">Dokumen</th>
                                                        <th rowspan="2">Status</th>
                                                        <th rowspan="2">Operasi</th>
                                                    </tr>
                                                    <tr>
                                                        <th>Pengajuan</th>
                                                        <th>Dokumen</th>
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
                <h5>Tambah/Edit {{ $title }}</h5>
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
                                <select class="browser-default" id="account_id" name="account_id" onchange="resetDetails();"></select>
                                <label class="active" for="account_id">Supplier</label>
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
                                <input id="receiver_name" name="receiver_name" type="text" placeholder="Nama Penerima">
                                <label class="active" for="receiver_name">Nama Penerima</label>
                            </div>
                            
                            <div class="input-field col m3 s12 step6">
                                <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. diterima" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);">
                                <label class="active" for="post_date">Tgl. Diterima</label>
                            </div>
                            <div class="input-field col m3 s12 step8">
                                <input id="document_date" name="document_date" min="{{ date('Y-m-d') }}" type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. dokumen">
                                <label class="active" for="document_date">Tgl. Dokumen</label>
                            </div>
                            <div class="input-field col m3 s12 step9">
                                <input id="delivery_no" name="delivery_no" type="text" placeholder="No. Surat Jalan">
                                <label class="active" for="delivery_no">No. Surat Jalan</label>
                            </div>
                            <div class="file-field input-field col m3 s12 step10">
                                <div class="btn">
                                    <span>Lampiran Bukti</span>
                                    <input type="file" name="document" id="document">
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text">
                                </div>
                            </div>
                            <div class="col m12 s12 step11">
                                <div class="col m6 s6">
                                    <p class="mt-2 mb-2">
                                        <h5>Purchase Order</h5>
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
                                    <h6><b>PO Terpakai</b> (hapus untuk bisa diakses pengguna lain) : <i id="list-used-data"></i></h6>
                                </div>
                            </div>
                            <div class="col m12 s12 step12">
                                <p class="mt-2 mb-2">
                                    <h5>Detail Produk</h5>
                                    <div style="overflow:auto;">
                                        <table class="bordered" style="min-width:2500px !important;" id="table-detail">
                                            <thead>
                                                <tr>
                                                    <th class="center">Item</th>
                                                    <th class="center">Qty PO</th>
                                                    <th class="center">Satuan PO</th>
                                                    <th class="center">Qty Stok</th>
                                                    <th class="center">Satuan Stok</th>
                                                    <th class="center">Keterangan 1</th>
                                                    <th class="center">Keterangan 2</th>
                                                    <th class="center">Remark</th>
                                                    <th class="center">Plant</th>
                                                    <th class="center">Line</th>
                                                    <th class="center">Mesin</th>
                                                    <th class="center">Departemen</th>
                                                    <th class="center">Gudang</th>
                                                    <th class="center">Timbangan</th>
                                                    <th class="center">Hapus</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-item">
                                                <tr id="empty-item">
                                                    <td colspan="15" class="center">
                                                        Pilih purchase order untuk memulai...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </p>
                            </div>
                            <div class="col m12 s12 step13">
                                <p class="mt-2 mb-2">
                                    <h5>Detail Nomor Serial (Item Aktiva)</h5>
                                    <div class="card-alert card red">
                                        <div class="card-content white-text">
                                            <p>Hati-hati! Jumlah nomor serial akan berubah ketika anda mengubah qty barang. Jika ada data pada input nomor serial, maka data akan hilang ditimpa dengan inputan baru. Pastikan anda tidak mengisi nomor serial jika qty belum diatur.</p>
                                        </div>
                                    </div>
                                    <div style="overflow:auto;">
                                        <table class="bordered" style="min-width:100%;" id="table-serial">
                                            <tbody id="body-item-serial">
                                                <tr id="empty-item-serial">
                                                    <td class="center">
                                                        Pilih purchase order untuk memulai...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </p>
                            </div>
                            <div class="input-field col m4 s12 step14">
                                <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note">Keterangan</label>
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

        window.table.search('{{ $code }}').draw();

        $('#modal4').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#show_detail').empty();
            }
        });

        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                $('#post_date').attr('min','{{ $minDate }}');
                $('#post_date').attr('max','{{ $maxDate }}');
                $('#document_date').attr('min','{{ date("Y-m-d") }}');
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
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                $('.row_item').each(function(){
                    $(this).remove();
                });
                $('.row_item_serial').each(function(){
                    $(this).remove();
                });
                if($('#empty-item').length == 0){
                    $('#body-item').append(`
                        <tr id="empty-item">
                            <td colspan="15" class="center">
                                Pilih purchase order untuk memulai...
                            </td>
                        </tr>
                    `);
                }
                if($('#empty-item-serial').length == 0){
                    $('#body-item-serial').append(`
                        <tr id="empty-item-serial">
                            <td class="center">
                                Pilih purchase order untuk memulai...
                            </td>
                        </tr>
                    `);
                }
                $('#purchase_order_id').empty();
                $('#account_id').empty();
                M.updateTextFields();
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
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

        select2ServerSide('#account_id', '{{ url("admin/select2/supplier") }}');

        $('#purchase_order_id').select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/purchase_order") }}',
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

        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
            
            if($('.row_item').length == 0){
                $('#body-item').append(`
                    <tr id="empty-item">
                        <td colspan="15" class="center">
                            Pilih purchase order untuk memulai...
                        </td>
                    </tr>
                `);
                $('#purchase_order_id').empty();
            }
        });
    });

    function resetDetails(){
        if($('#account_id').val()){
            if($('.data-used').length > 0){
                $('.data-used').trigger('click');
            }else{
                $('.row_item').each(function(){
                    $(this).remove();
                });
                $('.row_item_serial').each(function(){
                    $(this).remove();
                });
                if($('#empty-item').length == 0){
                    $('#body-item').append(`
                        <tr id="empty-item">
                            <td colspan="15" class="center">
                                Pilih purchase order untuk memulai...
                            </td>
                        </tr>
                    `);
                }
                if($('#empty-item-serial').length == 0){
                    $('#body-item-serial').append(`
                        <tr id="empty-item-serial">
                            <td class="center">
                                Pilih purchase order untuk memulai...
                            </td>
                        </tr>
                    `);
                }
            }
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

    function getPurchaseOrderAll(val){
        if(val){
            $.ajax({
                url: '{{ Request::url() }}/get_purchase_order_all',
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

                    if(response.length > 0){
                        $('#empty-item').remove();
                        $.each(response, function(i, valmain) {
                            $('#list-used-data').append(`
                                <div class="chip purple darken-4 gradient-shadow white-text">
                                    ` + valmain.code + `
                                    <i class="material-icons close data-used" onclick="removeUsedData('` + valmain.id + `')">close</i>
                                </div>
                            `);

                            $.each(valmain.details, function(i, val) {
                                var count = makeid(10);
                                $('#body-item').append(`
                                    <tr class="row_item" data-po="` + valmain.id + `">
                                        <input type="hidden" name="arr_item[]" id="arr_item` + count + `" value="` + val.item_id + `">
                                        <input type="hidden" name="arr_purchase[]" value="` + val.purchase_order_detail_id + `">
                                        <input type="hidden" name="arr_place[]" id="arr_place` + count + `" value="` + val.place_id + `">
                                        <input type="hidden" name="arr_department[]" value="` + val.department_id + `">
                                        <input type="hidden" name="arr_warehouse[]" id="arr_warehouse` + count + `" value="` + val.warehouse_id + `">
                                        <td>
                                            ` + val.item_name + `
                                        </td>
                                        <td>
                                            <input name="arr_qty[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiah(this);" style="text-align:right;width:100px;">
                                        </td>
                                        <td class="center">
                                            <span>` + val.unit + `</span>
                                        </td>
                                        <td>
                                            <input name="arr_note[]" class="browser-default" type="text" placeholder="Keterangan..." value="` + valmain.code + `" style="width:100%;">
                                        </td>
                                        <td>
                                            <input name="arr_remark[]" class="browser-default" type="text" placeholder="Keterangan..." value="-" style="width:100%;">
                                        </td>
                                        <td class="center">
                                            <span>` + val.place_name + `</span>
                                        </td>
                                        <td class="center">
                                            <span>` + val.department_name + `</span>
                                        </td>
                                        <td class="center">
                                            <span>` + val.warehouse_name + `</span>
                                        </td>
                                        <td class="center">
                                            <select class="browser-default" id="arr_scale` + count + `" name="arr_scale[]"></select>
                                        </td>
                                        <td class="center">
                                            <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                                <i class="material-icons">delete</i>
                                            </a>
                                        </td>
                                    </tr>
                                `);

                                $('#arr_scale' + count).select2({
                                    placeholder: '-- Pilih ya --',
                                    minimumInputLength: 1,
                                    allowClear: true,
                                    cache: true,
                                    width: 'resolve',
                                    dropdownParent: $('body').parent(),
                                    ajax: {
                                        url: '{{ url("admin/select2/good_scale_item") }}',
                                        type: 'GET',
                                        dataType: 'JSON',
                                        data: function(params) {
                                            return {
                                                search: params.term,
                                                item: $('#arr_item' + count).val(),
                                                place: $('#arr_place' + count).val(),
                                                warehouse: $('#arr_warehouse' + count).val(),
                                            };
                                        },
                                        processResults: function(data) {
                                            return {
                                                results: data.items
                                            }
                                        }
                                    }
                                });
                            });
                        });
                    }
                    $('#purchase_order_id').empty();
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
            if($('.data-used').length > 0){
                $('.data-used').trigger('click');
            }
            $('.row_item').each(function(){
                $(this).remove();
            });
            if($('.row_item').length == 0 && $('#empty-item').length == 0){
                $('#body-item').append(`
                    <tr id="empty-item">
                        <td colspan="15" class="center">
                            Pilih purchase order untuk memulai...
                        </td>
                    </tr>
                `);
            }
        }
    }
    var nodeTemplate = function(data) {
        return `
            <div class="title">${data.name}</div>
            <div class="content">${data.title}<br>Tanggal ${data.date}<br> Nominal : ${data.grandtotal}<br></div>
        `;
    };

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
            success: function(response) {
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
                { name: 'name', className: 'center-align' },
                { name: 'account_id', className: 'center-align' },
                { name: 'company_id', className: 'center-align' },
                { name: 'receiver', className: 'center-align' },
                { name: 'date_post', className: 'center-align' },
                { name: 'date_doc', className: 'center-align' },
                { name: 'note', className: '' },
                { name: 'delivery_no', className: 'center-align' },
                { name: 'document', searchable: false, orderable: false, className: 'center-align' },
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
                var formData = new FormData($('#form_data')[0]), passedSerial = true;

                formData.delete("arr_department[]");
                formData.delete("arr_line[]");
                formData.delete("arr_machine[]");
                formData.delete("arr_scale[]");
                formData.delete("arr_serial[]");

                $('input[name^="arr_department"]').each(function(index){
                    formData.append('arr_department[]',($(this).val() ? $(this).val() : ''));
                });

                $('input[name^="arr_line"]').each(function(index){
                    formData.append('arr_line[]',($(this).val() ? $(this).val() : ''));
                });

                $('input[name^="arr_machine"]').each(function(index){
                    formData.append('arr_machine[]',($(this).val() ? $(this).val() : ''));
                });

                $('select[name^="arr_scale"]').each(function(index){
                    formData.append('arr_scale[]',($(this).val() ? $(this).val() : ''));
                });

                if($('input[name^="arr_serial[]"]').length > 0){
                    $('input[name^="arr_serial[]"]').each(function(index){
                        if(!$(this).val()){
                            passedSerial = false;
                        }else{
                            formData.append('arr_serial[]',$(this).val());
                            formData.append('arr_serial_item[]',$(this).data('item'));
                            formData.append('arr_serial_po[]',$(this).data('po'));
                        }
                    });
                }
                
                if(passedSerial){
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
                        text: 'Nomor serial item tidak boleh kosong.',
                        icon: 'error'
                    });
                }
            }
        });
    }

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
    }

    function adjustSerial(element,podid,itemid){
        let countSerialPo = $('input[name^="arr_serial[]"][data-po="' + podid + '"]').length,
        conversion = parseFloat($(element).data('conversion').toString().replaceAll(".", "").replaceAll(",",".")),
        qty = parseFloat($(element).val().replaceAll(".", "").replaceAll(",",".")),
        code = $(element).data('code');

        let qtyConversion = conversion * qty;

        $('#qty_stock' + code).text(formatRupiahIni(qtyConversion.toFixed(3).toString().replace('.',',')));
        
        if($(element).data('activa')){
            if(qty !== countSerialPo){
                $('input[name^="arr_serial[]"][data-po="' + podid + '"]').remove();
            }
            for(let i = 1;i<=qty;i++){
                $('td[data-pod="' + podid + '"]').append(`
                    <input name="arr_serial[]" class="browser-default" type="text" placeholder="Nomor serial item..." value="" style="width:150px;" required data-item="` + itemid + `" data-po="`+ podid +`">
                `);
            }
        }
    }

    function getPurchaseOrder(){
        let val = $('#purchase_order_id').val();

        if(val){
            $.ajax({
                url: '{{ Request::url() }}/get_purchase_order',
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
                    if(response.status == 500){
                        swal({
                            title: 'Ups!',
                            text: response.message,
                            icon: 'warning'
                        });
                        $('#purchase_order_id').empty();
                    }else{
                        if(response.details.length > 0){
                            $('#receiver_name').val(response.receiver_name);
                            $('#account_id').empty().append(`
                                <option value="` + response.account_id + `">` + response.account_name + `</option>
                            `);
                            $('#empty-item').remove();

                            $('#list-used-data').append(`
                                <div class="chip purple darken-4 gradient-shadow white-text">
                                    ` + response.code + `
                                    <i class="material-icons close data-used" onclick="removeUsedData('` + response.id + `')">close</i>
                                </div>
                            `);

                            $.each(response.details, function(i, val) {
                                var count = makeid(10);
                                $('#body-item').append(`
                                    <tr class="row_item" data-po="` + response.id + `">
                                        <input type="hidden" name="arr_item[]" id="arr_item` + count + `"  value="` + val.item_id + `">
                                        <input type="hidden" name="arr_purchase[]" value="` + val.purchase_order_detail_id + `">
                                        <input type="hidden" name="arr_place[]" id="arr_place` + count + `" value="` + val.place_id + `">
                                        <input type="hidden" name="arr_line[]" value="` + val.line_id + `">
                                        <input type="hidden" name="arr_machine[]" value="` + val.machine_id + `">
                                        <input type="hidden" name="arr_department[]" value="` + val.department_id + `">
                                        <input type="hidden" name="arr_warehouse[]" id="arr_warehouse` + count + `" value="` + val.warehouse_id + `">
                                        <td>
                                            ` + val.item_name + `
                                        </td>
                                        <td>
                                            <input name="arr_qty[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiah(this);adjustSerial(this,` + val.purchase_order_detail_id + `,` + val.item_id + `);" style="text-align:right;width:100px;" data-activa="` + val.is_activa + `" data-code="` + count + `" data-conversion="` + val.qty_conversion + `">
                                        </td>
                                        <td class="center">
                                            <span>` + val.unit + `</span>
                                        </td>
                                        <td class="center" id="qty_stock` + count + `">
                                            ` + val.qty_stock + `
                                        </td>
                                        <td class="center" id="unit_stock` + count + `">
                                            ` + val.unit_stock + `
                                        </td>
                                        <td>
                                            <input name="arr_note[]" type="text" placeholder="Keterangan..." value="` + val.note + `" style="width:100%;" readonly>
                                        </td>
                                        <td>
                                            <input name="arr_note2[]" type="text" placeholder="Keterangan..." value="` + val.note2 + `" style="width:100%;" readonly>
                                        </td>
                                        <td>
                                            <input name="arr_remark[]" class="browser-default" type="text" placeholder="Keterangan..." value="-" style="width:100%;">
                                        </td>
                                        <td class="center">
                                            <span>` + val.place_name + `</span>
                                        </td>
                                        <td class="center">
                                            <span>` + val.line_name + `</span>
                                        </td>
                                        <td class="center">
                                            <span>` + val.machine_name + `</span>
                                        </td>
                                        <td class="center">
                                            <span>` + val.department_name + `</span>
                                        </td>
                                        <td class="center">
                                            <span>` + val.warehouse_name + `</span>
                                        </td>
                                        <td class="center">
                                            <select class="browser-default" id="arr_scale` + count + `" name="arr_scale[]"></select>
                                        </td>
                                        <td class="center">
                                            <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                                <i class="material-icons">delete</i>
                                            </a>
                                        </td>
                                    </tr>
                                `);

                                $('#arr_scale' + count).select2({
                                    placeholder: '-- Pilih ya --',
                                    minimumInputLength: 1,
                                    allowClear: true,
                                    cache: true,
                                    width: 'resolve',
                                    dropdownParent: $('body').parent(),
                                    ajax: {
                                        url: '{{ url("admin/select2/good_scale_item") }}',
                                        type: 'GET',
                                        dataType: 'JSON',
                                        data: function(params) {
                                            return {
                                                search: params.term,
                                                item: $('#arr_item' + count).val(),
                                                place: $('#arr_place' + count).val(),
                                                warehouse: $('#arr_warehouse' + count).val(),
                                            };
                                        },
                                        processResults: function(data) {
                                            return {
                                                results: data.items
                                            }
                                        }
                                    }
                                });
                            });
                        }
                        if(response.serials.length > 0){
                            $('#empty-item-serial').remove();
                            $.each(response.serials, function(i, val) {
                                var count = makeid(10);
                                let columns = '';
                                for(let i = 1;i<=response.maxcolumn;i++){
                                    columns += (i > val.qty_serial ? `` : `<input name="arr_serial[]" class="browser-default" type="text" placeholder="Nomor serial item..." value="" style="width:150px;" required data-item="` + val.item_id + `" data-po="`+ val.purchase_order_detail_id +`">`);
                                }
                                $('#body-item-serial').append(`
                                    <tr class="row_item_serial" data-po="` + response.id + `">
                                        <td style="width:200px !important;">
                                            ` + val.item_name + `
                                        </td>
                                        <td data-pod="` + val.purchase_order_detail_id + `">` + columns + `</td>
                                    </tr>
                                `);
                            });
                        }
                        $('#purchase_order_id').empty();
                        $('.modal-content').scrollTop(0);
                        M.updateTextFields();
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
            
            /* $('.row_item').each(function(){
                $(this).remove();
            });
            if($('.row_item').length == 0 && $('#empty-item').length == 0){
                $('#body-item').append(`
                    <tr id="empty-item">
                        <td colspan="15" class="center">
                            Pilih purchase order untuk memulai...
                        </td>
                    </tr>
                `);
            } */
        }
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
                $('.row_item_serial[data-po="' + id + '"]').remove();
                if($('.row_item').length == 0 && $('#empty-item').length == 0){
                    $('#body-item').append(`
                        <tr id="empty-item">
                            <td colspan="15" class="center">
                                Pilih purchase order untuk memulai...
                            </td>
                        </tr>
                    `);
                }

                if($('.row_item_serial').length == 0 && $('#empty-item-serial').length == 0){
                    $('#body-item-serial').append(`
                        <tr id="empty-item-serial">
                            <td class="center">
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
                $('.row_item').each(function(){
                    $(this).remove();
                });
            },
            success: function(response) {
                loadingClose('#main');
                $('#modal1').modal('open');
                $('#temp').val(id);
                $('#code_place_id').val(response.code_place_id).formSelect();
                $('#code').val(response.code);
                $('#account_id').empty().append(`
                    <option value="` + response.account_id + `">` + response.account_name + `</option>
                `);
                $('#company_id').val(response.company_id).formSelect();
                $('#note').val(response.note);
                $('#receiver_name').val(response.receiver_name);
                $('#post_date').val(response.post_date);
                $('#document_date').val(response.document_date);
                $('#delivery_no').val(response.delivery_no);
                $('#document_date').removeAttr('min');
                
                if(response.details.length > 0){
                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        $('#body-item').append(`
                            <tr class="row_item">
                                <input type="hidden" name="arr_item[]" id="arr_item` + count + `" value="` + val.item_id + `">
                                <input type="hidden" name="arr_purchase[]" value="` + val.purchase_order_detail_id + `">
                                <input type="hidden" name="arr_place[]" id="arr_place` + count + `" value="` + val.place_id + `">
                                <input type="hidden" name="arr_line[]" value="` + val.line_id + `">
                                <input type="hidden" name="arr_machine[]" value="` + val.machine_id + `">
                                <input type="hidden" name="arr_department[]" value="` + val.department_id + `">
                                <input type="hidden" name="arr_warehouse[]" id="arr_warehouse` + count + `" value="` + val.warehouse_id + `">
                                <td>
                                    ` + val.item_name + `
                                </td>
                                <td>
                                    <input name="arr_qty[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiah(this);adjustSerial(this,` + val.purchase_order_detail_id + `,` + val.item_id + `);" style="text-align:right;width:100px;" data-activa="` + val.is_activa + `" data-code="` + count + `" data-conversion="` + val.qty_conversion + `">
                                </td>
                                <td class="center">
                                    <span>` + val.unit + `</span>
                                </td>
                                <td class="center" id="qty_stock` + count + `">
                                    ` + val.qty_stock + `
                                </td>
                                <td class="center" id="unit_stock` + count + `">
                                    ` + val.unit_stock + `
                                </td>
                                <td>
                                    <input name="arr_note[]" type="text" placeholder="Keterangan..." value="` + val.note + `" style="width:100%;" readonly>
                                </td>
                                <td>
                                    <input name="arr_note2[]" type="text" placeholder="Keterangan..." value="` + val.note2 + `" style="width:100%;" readonly>
                                </td>
                                <td>
                                    <input name="arr_remark[]" class="browser-default" type="text" placeholder="Keterangan..." value="` + val.remark + `"  style="width:100%;">
                                </td>
                                <td class="center">
                                    <span>` + val.place_name + `</span>
                                </td>
                                <td class="center">
                                    <span>` + val.line_name + `</span>
                                </td>
                                <td class="center">
                                    <span>` + val.machine_name + `</span>
                                </td>
                                <td class="center">
                                    <span>` + val.department_name + `</span>
                                </td>
                                <td class="center">
                                    <span>` + val.warehouse_name + `</span>
                                </td>
                                <td class="center">
                                    <select class="browser-default" id="arr_scale` + count + `" name="arr_scale[]"></select>
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                        if(val.good_scale_detail_id){
                            $('#arr_scale' + count).append(`
                                <option value="` + val.good_scale_detail_id + `">` + val.good_scale_detail_name + `</option>
                            `);
                        }
                        $('#arr_scale' + count).select2({
                            placeholder: '-- Pilih ya --',
                            minimumInputLength: 1,
                            allowClear: true,
                            cache: true,
                            width: 'resolve',
                            dropdownParent: $('body').parent(),
                            ajax: {
                                url: '{{ url("admin/select2/good_scale_item") }}',
                                type: 'GET',
                                dataType: 'JSON',
                                data: function(params) {
                                    return {
                                        search: params.term,
                                        item: $('#arr_item' + count).val(),
                                        place: $('#arr_place' + count).val(),
                                        warehouse: $('#arr_warehouse' + count).val(),
                                    };
                                },
                                processResults: function(data) {
                                    return {
                                        results: data.items
                                    }
                                }
                            }
                        });
                    });
                }

                if(response.serials.length > 0){
                    $('#empty-item-serial').remove();
                    $.each(response.serials, function(i, val) {
                        var count = makeid(10);
                        let columns = '';
                        $.each(val.list_serial_number, function(i, value) {
                            columns += `<input name="arr_serial[]" class="browser-default" type="text" placeholder="Nomor serial item..." value="` + value + `" style="width:150px;" required data-item="` + val.item_id + `" data-po="`+ val.purchase_order_detail_id +`">`;
                        });
                        $('#body-item-serial').append(`
                            <tr class="row_item_serial" data-po="` + val.purchase_order_id + `">
                                <td style="width:200px !important;">
                                    ` + val.item_name + `
                                </td>
                                <td data-pod="` + val.purchase_order_detail_id + `">` + columns + `</td>
                            </tr>
                        `);
                    });
                }

                $('#empty-item').remove();
                
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
            var poin = $(item).find('td:nth-child(5)').text().trim();
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
                    title : 'Transfer Antar Gudang - Masuk',
                    intro : 'Pencatatan Barang Masuk dari gudang pada form ini'
                },
                {
                    title : 'Nomor Dokumen',
                    element : document.querySelector('.step1'),
                    intro : 'Nomor dokumen wajib diisikan, dengan kombinasi 4 huruf kode dokumen, tahun pembuatan dokumen, kode plant, serta nomor urut. Nomor ini bersifat unik, tidak akan sama, dan nomor urut paling belakang akan ter-reset secara otomatis berdasarkan tahun tanggal post.'
                },
                {
                    title : 'Kode Plant',
                    element : document.querySelector('.step2'),
                    intro : 'Kode plant dimana dokumen dibuat'
                },
                {
                    title : 'Supplier',
                    element : document.querySelector('.step3'),
                    intro : 'Supplier  terkait dalam GRPO'
                },
                {
                    title : 'Perusahaan',
                    element : document.querySelector('.step4'),
                    intro : 'Perusahaan dimana dokumen ini dibuat atau diperuntukkan' 
                },
                {
                    title : 'Nama Penerima',
                    element : document.querySelector('.step5'),
                    intro : 'Nama penerima grpo' 
                },
                {
                    title : 'Tgl. Diterima',
                    element : document.querySelector('.step6'),
                    intro : 'Tanggal grpo diterima,harap hati hati jangan sampai salah.' 
                },
                {
                    title : 'Tgl. Dokumen',
                    element : document.querySelector('.step8'),
                    intro : 'Tanggal yang digunakan saat dokumen ini nanti di print' 
                },
                {
                    title : 'No. Surat Jalan',
                    element : document.querySelector('.step9'),
                    intro : 'No surat jalan GRPO terkait jika ada' 
                },
                {
                    title : 'Lampiran Bukti',
                    element : document.querySelector('.step10'),
                    intro : 'Silahkan unggah file lampiran. untuk saat ini hanya bisa mengakomodir 1 file lampiran saja. Jika ingin menambahkan file lebih dari 1, silahkan gabungkan file anda menjadi pdf.' 
                },
                {
                    title : 'Purchase Order',
                    element : document.querySelector('.step11'),
                    intro : 'Pemilihan PO yang akan digunakan dalam GRPO yang akan dibuat' 
                },
                {
                    title : 'Detail Produk',
                    element : document.querySelector('.step12'),
                    intro : 'List produk yang terkait dengan po ataupun grpo.' 
                },
                {
                    title : 'Detail Nomor Serial',
                    element : document.querySelector('.step13'),
                    intro : 'Tabel ini untuk mengelola data nomor serial jika item yang ditarik dari PO adalah item dari Grup Aktiva.' 
                },
                {
                    title : 'Keterangan',
                    element : document.querySelector('.step14'),
                    intro : 'Silahkan isi / tambahkan keterangan untuk dokumen ini untuk dimunculkan di bagian bawah tabel detail produk nantinya, ketika dicetak.' 
                },
                {
                    title : 'Tombol Simpan',
                    element : document.querySelector('.step15'),
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