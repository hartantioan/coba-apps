<style>
    .modal {
        top:0px !important;
    }

    body.tab-active input:focus {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .modal-content .select2.tab-active {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .modal table.bordered th, table.bordered td {
        padding: 5px !important;
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
                                                <label for="filter_status" style="font-size:1rem;">Filter Status :</label>
                                                <div class="input-field col s12">
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
                                                <label for="start_date" style="font-size:1rem;">Start Date (Tanggal Mulai Posting) :</label>
                                                <div class="input-field col s12">
                                                    <input type="date" id="start_date" name="start_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="finish_date" style="font-size:1rem;">End Date (Tanggal Akhir Posting) :</label>
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
                                    <div class="row mt-2">
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
                                            <table id="datatable_serverside" >
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Code</th>
                                                        <th>Pengguna</th>
                                                        <th>Perusahaan</th>
                                                        <th>Plant</th>
                                                        <th>Tgl.Post</th>
                                                        <th>Keterangan</th>
                                                        <th>Dokumen</th>
                                                        <th>Status</th>
                                                        <th>By</th>
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
                                        <select class="form-control" id="company_id" name="company_id">
                                            @foreach ($company as $row)
                                                <option value="{{ $row->id }}">{{ $row->name }}</option>
                                            @endforeach
                                        </select>
                                        <label class="" for="company_id">Perusahaan</label>
                                    </div>
                                    <div class="input-field col m3 s12 step4">
                                        <select class="form-control" id="place_id" name="place_id">
                                            @foreach ($place as $rowplace)
                                                <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                                            @endforeach
                                        </select>
                                        <label class="" for="place_id">Plant</label>
                                    </div>
                                    <div class="input-field col m3 s12 step5">
                                        <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                        <label class="active" for="post_date">Tgl. Post</label>
                                    </div>
                                    <div class="file-field input-field col m3 s12 step6">
                                        <div class="btn">
                                            <span>File</span>
                                            <input type="file" name="file" id="file">
                                        </div>
                                        <div class="file-path-wrapper">
                                            <input class="file-path validate" type="text">
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12">
                                <fieldset>
                                    <legend>2. MOP Terpakai</legend>
                                    <div class="input-field col m3 s12 step7">
                                        <select class="browser-default" id="marketing_order_plan_id" name="marketing_order_plan_id"></select>
                                        <label class="active" for="marketing_order_plan_id">Marketing Order Produksi</label>
                                    </div>
                                    <div class="col m2 s12 step8">
                                        <a class="waves-effect waves-light cyan btn-small mb-1 mr-1 mt-5" onclick="getMarketingOrderPlan();" href="javascript:void(0);">
                                            <i class="material-icons left">add</i> MOP
                                        </a>
                                    </div>
                                    <div class="col m5 s12 step9">
                                        <h6>Hapus untuk bisa diakses pengguna lain : <i id="list-used-data"></i></h6>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12">
                                <fieldset style="min-width: 100%;">
                                    <legend>3. Detail Target Produksi</legend>
                                    <div class="col m12 s12 step10" style="overflow:auto;width:100% !important;">
                                        <p class="mt-2 mb-2">
                                            <table class="bordered" id="table-detail" style="min-width:100%;">
                                                <thead>
                                                    <tr>
                                                        <th class="center" style="min-width:75px;">Hapus</th>
                                                        <th class="center" style="min-width:150px;">MOP</th>
                                                        <th class="center" style="min-width:150px;">Item</th>
                                                        <th class="center" style="min-width:150px;">Qty MOP</th>
                                                        <th class="center" style="min-width:150px;">Qty dalam Proses</th>
                                                        <th class="center" style="min-width:150px;">Satuan UoM</th>
                                                        <th class="center" style="min-width:150px;">Remark</th>
                                                        <th class="center" style="min-width:150px;">Tgl.Request</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="body-item">
                                                    <tr id="last-row-item">
                                                        <td colspan="8">
                                                            Silahkan tambahkan Marketing Order Produksi...
                                                        </td>
                                                    </tr>
                                                    <tr id="total-row-target">
                                                        <td class="right-align" colspan="2">
                                                            TOTAL :
                                                        </td>
                                                        <td class="right-align" id="data-foot">
                                                            0,000
                                                        </td>
                                                        <td colspan="5"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </p>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12">
                                <fieldset style="min-width: 100%;">
                                    <legend>4. Jadwal Produksi</legend>
                                    <div class="card-alert card gradient-45deg-purple-amber">
                                        <div class="card-content white-text">
                                            <p>Info : Item yang muncul pada jadwal produksi dibawah adalah, daftar item yang hanya memiliki BOM yang aktif.</p>
                                        </div>
                                    </div>
                                    <ul class="tabs step11">
                                        <li class="tab col m6 s12 l6"><a class="active" href="#normal">Normal</a></li>
                                        <li class="tab col m6 s12 l6"><a href="#powder">Powder</a></li>
                                    </ul>
                                    <div id="normal" class="col m12 s12 active" style="overflow:auto;width:100% !important;">
                                        <p class="mt-2 mb-2">
                                            <table class="bordered" style="min-width:100%;">
                                                <thead>
                                                    <tr>
                                                        <th colspan="3">NORMAL BOM</th>
                                                    </tr>
                                                    <tr>
                                                        <th class="center">Hapus</th>
                                                        <th class="center">Target</th>
                                                        <th>Data Shift & BOM</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="body-item-detail-normal" id="body-item-detail-normal">
                                                    <tr class="last-row-item-detail-normal">
                                                        <td colspan="3">
                                                            Silahkan tambahkan Marketing Order Produksi...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </p>
                                    </div>
                                    <div id="powder" class="col s12" style="overflow:auto;min-width:100%;">
                                        <p class="mt-2 mb-2">
                                            <table class="bordered" style="min-width:100%;">
                                                <thead>
                                                    <tr>
                                                        <th colspan="3">POWDER BOM</th>
                                                    </tr>
                                                    <tr>
                                                        <th class="center">Hapus</th>
                                                        <th class="center">Target</th>
                                                        <th>Data Shift & BOM</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="body-item-detail-powder" id="body-item-detail-powder">
                                                    <tr class="last-row-item-detail-powder">
                                                        <td colspan="3">
                                                            Silahkan tambahkan Marketing Order Produksi...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </p>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12">
                                <fieldset style="min-width: 100%;">
                                    <legend>5. Peta BOM</legend>
                                    <div class="card-alert card gradient-45deg-yellow-green">
                                        <div class="card-content">
                                            <p>Info : Peta BOM diambil dari relasi BOM yang diatur pada master data. Jika ada lebih dari 1 bom yang ter-set, maka bom terbaru yang akan digunakan, dan jika ada lebih dari 1 alternatif, maka alternatif default yang akan dipakai.</p>
                                        </div>
                                    </div>
                                    <div class="col m12 s12">
                                        <ol id="list-bom-relation">
                                            
                                        </ol>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12 m6 l4">
                                <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note">Keterangan</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light purple btn-panduan mr-1" onclick="startIntro();">Panduan <i class="material-icons right">help_outline</i></button>
        <button class="btn waves-effect waves-light mr-1 submit step12" onclick="save();">Simpan <i class="material-icons right">send</i></button>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Tutup</a>
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
                <div id="visualisation">
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
    var listfgsfg = [];

    $(function() {

        var previous = '';

        $("#table-detail th,#table-detail2 th,#table-detail3 th,#table-detail4 th,#table-detail5 th").resizable({
            minWidth: 100,
        });

        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
            
        });

        loadDataTable();

        window.table.search('{{ $code }}').draw();

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

        $('#modal4').modal({
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

        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                $('#post_date').attr('min','{{ $minDate }}');
                $('#post_date').attr('max','{{ $maxDate }}');
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
                $('.tabs').tabs({
                    onShow: function () {
                        
                    }
                });
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                $('input').css('border', 'none');
                $('input').css('border-bottom', '0.5px solid black');
                M.updateTextFields();
                $('#project_id,#warehouse_id').empty();
                $('.row_item,.row_item_detail').remove();
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }
                window.onbeforeunload = function() {
                    return null;
                };
                $('.row_item').each(function(){
                    $(this).remove();
                });

                $('.row_item_detail').each(function(){
                    $(this).remove();
                });

                $('#list-bom-relation').empty();

                if($('#last-row-item').length == 0){
                    $('#total-row-target').before(`
                        <tr id="last-row-item">
                            <td colspan="8">
                                Silahkan tambahkan Marketing Order Produksi...
                            </td>
                        </tr>
                    `);
                }
                
                if($('.last-row-item-detail').length == 0){
                    $('#total-row-detail').before(`
                        <tr class="last-row-item-detail">
                            <td colspan="11">
                                Silahkan tambahkan Marketing Order Produksi...
                            </td>
                        </tr>
                    `);
                }
                $('#marketing_order_plan_id').empty();

                $('#body-item-detail-normal').empty().append(`
                    <tr class="last-row-item-detail-normal">
                        <td colspan="3">
                            Silahkan tambahkan Marketing Order Produksi...
                        </td>
                    </tr>
                `);

                $('#body-item-detail-powder').empty().append(`
                    <tr class="last-row-item-detail-powder">
                        <td colspan="3">
                            Silahkan tambahkan Marketing Order Produksi...
                        </td>
                    </tr>
                `);
            }
        });

        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
            let id = $(this).data('id');
            if($('.row_item[data-id="' + id + '"]').length == 0){
                $('.data-used[data-id="' + id + '"]').trigger('click');
            }
            $('.row_detail_item[data-id="' + id + '"]').remove();
            countTarget();
        });

        $('.body-item-detail-normal').on('click', '.delete-data-item-detail-normal', function() {
            $('#list-bom-' + $(this).data('id')).remove();
            $(this).closest('tr').remove();
        });

        $('.body-item-detail-powder').on('click', '.delete-data-item-detail-powder', function() {
            $('#list-bom-' + $(this).data('id')).remove();
            $(this).closest('tr').remove();
        });

        $('#marketing_order_plan_id').select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/marketing_order_plan") }}',
                type: 'GET',
                dataType: 'JSON',
                data: function(params) {
                    return {
                        search: params.term,
                        place_id: $('#place_id').val(),
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

    function countTarget(){
        let total = 0;
        $('input[name^="arr_qty[]"]').each(function(){
            total += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
        });
        $('#data-foot').text(
            (total >= 0 ? '' : '-') + formatRupiahIni(total.toString().replace('.',','))
        );
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
            success: function(response) {
                $('.row_item[data-id="' + id + '"]').remove();
                if($('.row_item').length == 0){
                    if($('#last-row-item').length == 0){
                        $('#total-row-target').before(`
                            <tr id="last-row-item">
                                <td colspan="8">
                                    Silahkan tambahkan Marketing Order Produksi...
                                </td>
                            </tr>
                        `);
                    }
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

    function getRowUnit(val,type){
        $("#arr_warehouse" + val).empty();
        $('#arr_unit' + val).empty();
        $('#list-bom-' + val).remove();
        if($("#arr_item_detail_id" + val).val()){
            if($("#arr_item_detail_id" + val).select2('data')[0].list_warehouse.length > 0){
                $.each($("#arr_item_detail_id" + val).select2('data')[0].list_warehouse, function(i, value) {
                    $('#arr_warehouse' + val).append(`
                        <option value="` + value.id + `">` + value.name + `</option>
                    `);
                });
            }else{
                $("#arr_warehouse" + val).append(`
                    <option value="">--Gudang tidak diatur di master data Grup Item--</option>
                `);
            }
            $('#arr_unit' + val).text($("#arr_item_detail_id" + val).select2('data')[0].uom);
            $('#list-bom-relation').append(
                `<li id="list-bom-` + val + `">(` + type + `) ` + $("#arr_item_detail_id" + val).select2('data')[0].list_bom + `</li>`
            );
        }else{
            $("#arr_item_detail_id" + val).empty();
            $("#arr_warehouse" + val).append(`
                <option value="">--Silahkan pilih item--</option>
            `);
            $('#arr_unit' + val).text('-');
        }
    }

    function addItem(type){
        if($('.last-row-item-detail-' + type).length > 0){
            $('.last-row-item-detail-' + type).remove();
        }
        var count = makeid(10);
        $('#total-row-detail-' + type).before(`
            <tr class="row_item_detail">
                <input type="hidden" name="arr_type[]" value="` + type + `">
                <td class="center-align">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item-detail-` + type + `" data-id="` + count +`" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
                <td>
                    <select class="browser-default" id="arr_item_detail_id` + count + `" name="arr_item_detail_id[]" onchange="getRowUnit('` + count + `','` + type + `')" required></select>
                </td>
                <td class="right-align">
                    <input name="arr_detail_qty[]" onfocus="emptyThis(this);" id="arr_detail_qty` + count + `" type="text" value="0,000" onkeyup="formatRupiahNoMinus(this);" required style="width:100%;text-align:right;">
                </td>
                <td class="center-align" id="arr_unit` + count + `">
                    -
                </td>
                <td class="">
                    <select class="browser-default" id="arr_bom` + count + `" name="arr_bom[]"></select>
                </td>
                <td>
                    <select class="browser-default" id="arr_line` + count + `" name="arr_line[]">
                        <option value="">--Kosong--</option>
                        @foreach ($line as $rowline)
                            <option value="{{ $rowline->id }}">{{ $rowline->name }}</option>
                        @endforeach
                    </select>    
                </td>
                <td>
                    <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">
                        <option value="">--Silahkan pilih item--</option>
                    </select>
                </td>
                <td class="">
                    <input name="arr_start_date[]" type="datetime-local" value="{{ date('Y-m-d H:i:s') }}" required>
                </td>
                <td class="">
                    <input name="arr_end_date[]" type="datetime-local" value="{{ date('Y-m-d H:i:s') }}" required>
                </td>
                <td class="">
                    <select class="browser-default" id="arr_shift` + count + `" name="arr_shift[]"></select>
                </td>
                <td class="">
                    <input name="arr_group[]" type="text" required>
                </td>
                <td class="">
                    <input name="arr_note[]" type="text" required>
                </td>
            </tr>
        `);

        select2ServerSide('#arr_item_detail_id' + count, '{{ url("admin/select2/item_has_bom") }}');

        $('#arr_bom' + count).select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/bom_by_item") }}',
                type: 'GET',
                dataType: 'JSON',
                data: function(params) {
                    return {
                        search: params.term,
                        item_id: $('#arr_item_detail_id' + count).val(),
                        place_id: $('#place_id').val(),
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.items
                    }
                }
            }
        });
    }

    function getMarketingOrderPlan(){
        if($('#marketing_order_plan_id').val()){
            let mop = $('#marketing_order_plan_id').select2('data')[0];
            $.ajax({
                url: '{{ Request::url() }}/send_used_data',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: $('#marketing_order_plan_id').val()
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
                        if($('.last-row-item-detail').length > 0){
                            $('.last-row-item-detail').remove();
                        }

                        $('#list-used-data').append(`
                            <div class="chip purple darken-4 gradient-shadow white-text">
                                ` + mop.code + `
                                <i class="material-icons close data-used" data-id="` + mop.id + `" onclick="removeUsedData('` + mop.table + `','` + $('#marketing_order_plan_id').val() + `')">close</i>
                            </div>
                        `);
                        
                        if(mop.details.length > 0){
                            $('#body-item-detail-normal').empty();
                            $('#body-item-detail-powder').empty();
                        }
                        
                        $.each(mop.details, function(i, val) {
                            var count = makeid(10);

                            $('#total-row-target').before(`
                                <tr class="row_item" data-id="` + mop.id + `">
                                    <input type="hidden" name="arr_id[]" id="arr_id` + count + `" value="` + val.mopd_id + `">
                                    <td class="center-align">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" data-id="` + val.mopd_id + `" href="javascript:void(0);">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                    <td>
                                        ` + mop.code + `
                                    </td>
                                    <td>
                                        ` + val.item_code + ` - ` + val.item_name + `
                                        ` + ( val.has_bom ? '' : '<br><span style="color:red;font-weight:800;">Belum memiliki BOM.</span>' ) + `<br>
                                    </td>
                                    <td class="right-align">
                                        <input name="arr_qty[]" onfocus="emptyThis(this);" id="arr_qty` + count + `" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinus(this);" required style="width:100%;text-align:right;" readonly>
                                    </td>
                                    <td class="right-align">
                                        0,000
                                    </td>
                                    <td class="center-align">
                                        ` + val.uom + `
                                    </td>
                                    <td class="">
                                        ` + val.note + `
                                    </td>
                                    <td class="center-align">
                                        ` + val.request_date + `
                                    </td>
                                </tr>
                            `);
                            
                            let datanormal = `<table class="bordered"><thead><tr>`;

                            $.each(val.list_bom, function(i, detail) {
                                if(!detail.type){
                                    datanormal += `
                                        <th class="center" style="min-width:150px !important;">Item</th>
                                        <th class="center" style="min-width:150px !important;">Qty</th>
                                        <th class="center" style="min-width:150px !important;">Satuan UoM</th>
                                        <th class="center" style="min-width:150px !important;">BOM</th>
                                        <th class="center" style="min-width:150px !important;">Line</th>
                                        <th class="center" style="min-width:150px !important;">Gudang</th>
                                        <th class="center" style="min-width:150px !important;">Tgl.Mulai</th>
                                        <th class="center" style="min-width:150px !important;">Tgl.Selesai</th>
                                        <th class="center" style="min-width:150px !important;">Remark</th>
                                    `;
                                }
                            });

                            datanormal += `</tr></thead><tbody><tr>`;

                            let arrCountNormal = [];

                            $.each(val.list_bom, function(i, detail) {
                                if(!detail.type){
                                    var count = makeid(10);
                                    arrCountNormal.push(count);
                                    let randomColor = getRandomColor();
                                    datanormal += `
                                        <input type="hidden" name="arr_type[]" value="normal">
                                        <input type="hidden" name="arr_detail_id[]" id="arr_detail_id` + count + `" value="` + val.mopd_id + `">
                                        <td style="background-color:` + randomColor + `;">
                                            <select class="browser-default" id="arr_item_detail_id` + count + `" name="arr_item_detail_id[]" onchange="getRowUnit('` + count + `','normal')" required></select>
                                        </td>
                                        <td style="background-color:` + randomColor + `;" class="right-align">
                                            <input name="arr_detail_qty[]" onfocus="emptyThis(this);" id="arr_detail_qty` + count + `" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinus(this);" required style="width:100%;text-align:right;">
                                        </td>
                                        <td style="background-color:` + randomColor + `;" class="center-align" id="arr_unit` + count + `">
                                            -
                                        </td>
                                        <td style="background-color:` + randomColor + `;" class="">
                                            <select class="browser-default" id="arr_bom` + count + `" name="arr_bom[]"></select>
                                        </td>
                                        <td style="background-color:` + randomColor + `;">
                                            <select class="browser-default" id="arr_line` + count + `" name="arr_line[]">
                                                <option value="">--Kosong--</option>
                                                @foreach ($line as $rowline)
                                                    <option value="{{ $rowline->id }}">{{ $rowline->name }}</option>
                                                @endforeach
                                            </select>    
                                        </td>
                                        <td style="background-color:` + randomColor + `;">
                                            <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">
                                                <option value="">--Silahkan pilih item--</option>
                                            </select>
                                        </td>
                                        <td style="background-color:` + randomColor + `;" class="">
                                            <input name="arr_start_date[]" type="datetime-local" value="{{ date('Y-m-d H:i:s') }}" required>
                                        </td>
                                        <td style="background-color:` + randomColor + `;" class="">
                                            <input name="arr_end_date[]" type="datetime-local" value="{{ date('Y-m-d H:i:s') }}" required>
                                        </td>
                                        <td style="background-color:` + randomColor + `;" class="">
                                            <input name="arr_note[]" type="text" required>
                                        </td>
                                    `;
                                }
                            });

                            datanormal += `</tr></tbody></table>`;

                            $('#body-item-detail-normal').append(`
                                <tr class="row_detail_item" data-id="` + val.mopd_id + `">
                                    <td class="center-align">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item-detail-normal" href="javascript:void(0);">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                    <td style="min-width:250px !important;">
                                        ` + val.item_code + ` - ` + val.item_name + `
                                    </td>
                                    <td>
                                       ` + datanormal + `
                                    </td>
                                </tr>
                            `);

                            $.each(arrCountNormal, function(i, row) {
                                select2ServerSide('#arr_item_detail_id' + row, '{{ url("admin/select2/item_has_bom") }}');
                                $('#arr_bom' + row).select2({
                                    placeholder: '-- Kosong --',
                                    minimumInputLength: 1,
                                    allowClear: true,
                                    cache: true,
                                    width: 'resolve',
                                    dropdownParent: $('body').parent(),
                                    ajax: {
                                        url: '{{ url("admin/select2/bom_by_item") }}',
                                        type: 'GET',
                                        dataType: 'JSON',
                                        data: function(params) {
                                            return {
                                                search: params.term,
                                                item_id: $('#arr_item_detail_id' + row).val(),
                                                place_id: $('#place_id').val(),
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

                            let datapowder = `<table class="bordered"><thead><tr>`;

                            $.each(val.list_bom, function(i, detail) {
                                if(detail.type){
                                    datapowder += `
                                        <th class="center" style="min-width:150px !important;">Item</th>
                                        <th class="center" style="min-width:150px !important;">Qty</th>
                                        <th class="center" style="min-width:150px !important;">Satuan UoM</th>
                                        <th class="center" style="min-width:150px !important;">BOM</th>
                                        <th class="center" style="min-width:150px !important;">Line</th>
                                        <th class="center" style="min-width:150px !important;">Gudang</th>
                                        <th class="center" style="min-width:150px !important;">Tgl.Mulai</th>
                                        <th class="center" style="min-width:150px !important;">Tgl.Selesai</th>
                                        <th class="center" style="min-width:150px !important;">Remark</th>
                                    `;
                                }
                            });

                            datapowder += `</tr></thead><tbody><tr>`;

                            let arrCountPowder = [];

                            $.each(val.list_bom, function(i, detail) {
                                if(detail.type){
                                    var count = makeid(10);
                                    arrCountPowder.push(count);
                                    let randomColor = getRandomColor();
                                    datapowder += `
                                        <input type="hidden" name="arr_type[]" value="powder">
                                        <input type="hidden" name="arr_detail_id[]" id="arr_detail_id` + count + `" value="` + val.mopd_id + `">
                                        <td style="background-color:` + randomColor + `;">
                                            <select class="browser-default" id="arr_item_detail_id` + count + `" name="arr_item_detail_id[]" onchange="getRowUnit('` + count + `','normal')" required></select>
                                        </td>
                                        <td style="background-color:` + randomColor + `;" class="right-align">
                                            <input name="arr_detail_qty[]" onfocus="emptyThis(this);" id="arr_detail_qty` + count + `" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinus(this);" required style="width:100%;text-align:right;">
                                        </td>
                                        <td style="background-color:` + randomColor + `;" class="center-align" id="arr_unit` + count + `">
                                            -
                                        </td>
                                        <td style="background-color:` + randomColor + `;" class="">
                                            <select class="browser-default" id="arr_bom` + count + `" name="arr_bom[]"></select>
                                        </td>
                                        <td style="background-color:` + randomColor + `;">
                                            <select class="browser-default" id="arr_line` + count + `" name="arr_line[]">
                                                <option value="">--Kosong--</option>
                                                @foreach ($line as $rowline)
                                                    <option value="{{ $rowline->id }}">{{ $rowline->name }}</option>
                                                @endforeach
                                            </select>    
                                        </td>
                                        <td style="background-color:` + randomColor + `;">
                                            <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">
                                                <option value="">--Silahkan pilih item--</option>
                                            </select>
                                        </td>
                                        <td style="background-color:` + randomColor + `;" class="">
                                            <input name="arr_start_date[]" type="datetime-local" value="{{ date('Y-m-d H:i:s') }}" required>
                                        </td>
                                        <td style="background-color:` + randomColor + `;" class="">
                                            <input name="arr_end_date[]" type="datetime-local" value="{{ date('Y-m-d H:i:s') }}" required>
                                        </td>
                                        <td style="background-color:` + randomColor + `;" class="">
                                            <input name="arr_note[]" type="text" required>
                                        </td>
                                    `;
                                }
                            });

                            datapowder += `</tr></tbody></table>`;

                            $('#body-item-detail-powder').append(`
                                <tr class="row_detail_item" data-id="` + val.mopd_id + `">
                                    <td class="center-align">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item-detail-powder" href="javascript:void(0);">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                    <td style="min-width:250px !important;">
                                        ` + val.item_code + ` - ` + val.item_name + `
                                    </td>
                                    <td>
                                        ` + datapowder + `
                                    </td>
                                </tr>
                            `);

                            $.each(arrCountPowder, function(i, row) {
                                select2ServerSide('#arr_item_detail_id' + row, '{{ url("admin/select2/item_has_bom") }}');
                                $('#arr_bom' + row).select2({
                                    placeholder: '-- Kosong --',
                                    minimumInputLength: 1,
                                    allowClear: true,
                                    cache: true,
                                    width: 'resolve',
                                    dropdownParent: $('body').parent(),
                                    ajax: {
                                        url: '{{ url("admin/select2/bom_by_item_powder") }}',
                                        type: 'GET',
                                        dataType: 'JSON',
                                        data: function(params) {
                                            return {
                                                search: params.term,
                                                item_id: $('#arr_item_detail_id' + row).val(),
                                                place_id: $('#place_id').val(),
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

                        countTarget();

                        $('#marketing_order_plan_id').empty();
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

        }
    }

    function getRandomColor() {
        color = "hsl(" + Math.random() * 360 + ", 100%, 75%)";
        return color;
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
                { name: 'user_id', className: 'center-align' },
                { name: 'company_id', className: 'center-align' },
                { name: 'plant_id', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'note', className: '' },
                { name: 'document', searchable: false, orderable: false, className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'by', searchable: false, orderable: false, className: 'center-align' },
                { name: 'operation', searchable: false, orderable: false, className: 'center-align' },
            ],
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
                
                var formData = new FormData($('#form_data')[0]), passed = true;

                $('input[name^="arr_qty[]"]').each(function(index){
                    if(!$(this).val()){
                        passed = false;
                    }
                });

                $('input[name^="arr_detail_qty[]"]').each(function(index){
                    if(!$(this).val()){
                        passed = false;
                    }
                    if(!$('select[name^="arr_bom[]"]').eq(index).val()){
                        passed = false;
                    }
                    if(!$('select[name^="arr_item_detail_id[]"]').eq(index).val()){
                        passed = false;
                    }
                    if(!$('select[name^="arr_warehouse[]"]').eq(index).val()){
                        passed = false;
                    }
                    if(!$('input[name^="arr_start_date[]"]').eq(index).val()){
                        passed = false;
                    }
                    if(!$('input[name^="arr_end_date[]"]').eq(index).val()){
                        passed = false;
                    }
                    if(!$('select[name^="arr_line[]"]').eq(index).val()){
                        passed = false;
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
                        title: 'Ups! Maaf.',
                        text: 'Qty target produksi, qty jadwal produksi, bom, tanggal mulai produksi, tanggal selesai produksi, item, line, dan gudang tidak boleh kosong.',
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
                $('#post_date').val(response.post_date);
                $('#company_id').val(response.company_id).formSelect();
                $('#place_id').val(response.place_id).formSelect();
                $('#note').val(response.note);

                if(response.targets.length > 0){
                    $('.row_item').each(function(){
                        $(this).remove();
                    });

                    $('.row_item_detail').each(function(){
                        $(this).remove();
                    });

                    if($('#last-row-item').length > 0){
                        $('#last-row-item').remove();
                    }

                    if($('.last-row-item-detail').length > 0){
                        $('.last-row-item-detail').remove();
                    }

                    if(response.targets.length > 0){
                        $('#body-item-detail-normal').empty();
                        $('#body-item-detail-powder').empty();
                    }

                    $.each(response.targets, function(i, val) {
                        var count = makeid(10);

                        $('#total-row-target').before(`
                            <tr class="row_item" data-id="` + val.id + `">
                                <input type="hidden" name="arr_id[]" id="arr_id` + count + `" value="` + val.mopd_id + `">
                                <td>
                                    ` + val.mop_code + `
                                </td>
                                <td>
                                    ` + val.item_code + ` - ` + val.item_name + `
                                    ` + ( val.has_bom ? '' : '<br><span style="color:red;font-weight:800;">Belum memiliki BOM.</span>' ) + `<br>
                                </td>
                                <td class="right-align">
                                    <input name="arr_qty[]" onfocus="emptyThis(this);" id="arr_qty` + count + `" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinus(this);" required style="width:100%;text-align:right;" readonly>
                                </td>
                                <td class="right-align">
                                    0,000
                                </td>
                                <td class="center-align">
                                    ` + val.uom + `
                                </td>
                                <td class="">
                                    ` + val.note + `
                                </td>
                                <td class="center-align">
                                    ` + val.request_date + `
                                </td>
                                <td class="center-align">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" data-id="` + val.id + `" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);

                        let datanormal = `<table class="bordered"><thead><tr>`;
                        
                        $.each(val.details, function(i, detail) {
                            if(detail.type == 'normal'){
                                datanormal += `
                                    <th class="center" style="min-width:150px !important;">Item</th>
                                    <th class="center" style="min-width:150px !important;">Qty</th>
                                    <th class="center" style="min-width:150px !important;">Satuan UoM</th>
                                    <th class="center" style="min-width:150px !important;">BOM</th>
                                    <th class="center" style="min-width:150px !important;">Line</th>
                                    <th class="center" style="min-width:150px !important;">Gudang</th>
                                    <th class="center" style="min-width:150px !important;">Tgl.Mulai</th>
                                    <th class="center" style="min-width:150px !important;">Tgl.Selesai</th>
                                    <th class="center" style="min-width:150px !important;">Remark</th>
                                `;
                            }
                        });

                        datanormal += `</tr></thead><tbody><tr>`;

                        let arrCountNormal = [];

                        $.each(val.details, function(i, detail) {
                            if(detail.type == 'normal'){
                                var count = makeid(10);
                                arrCountNormal.push(count);
                                let randomColor = getRandomColor();
                                datanormal += `
                                    <input type="hidden" name="arr_type[]" value="normal">
                                    <input type="hidden" name="arr_detail_id[]" id="arr_detail_id` + count + `" value="` + detail.mopd_id + `">
                                    <td style="background-color:` + randomColor + `;">
                                        <select class="browser-default" id="arr_item_detail_id` + count + `" name="arr_item_detail_id[]" onchange="getRowUnit('` + count + `','normal')" required></select>
                                    </td>
                                    <td style="background-color:` + randomColor + `;" class="right-align">
                                        <input name="arr_detail_qty[]" onfocus="emptyThis(this);" id="arr_detail_qty` + count + `" type="text" value="` + detail.qty + `" onkeyup="formatRupiahNoMinus(this);" required style="width:100%;text-align:right;">
                                    </td>
                                    <td style="background-color:` + randomColor + `;" class="center-align" id="arr_unit` + count + `">
                                        ` + detail.uom + `
                                    </td>
                                    <td style="background-color:` + randomColor + `;" class="">
                                        <select class="browser-default" id="arr_bom` + count + `" name="arr_bom[]"></select>
                                    </td>
                                    <td style="background-color:` + randomColor + `;">
                                        <select class="browser-default" id="arr_line` + count + `" name="arr_line[]">
                                            <option value="">--Kosong--</option>
                                            @foreach ($line as $rowline)
                                                <option value="{{ $rowline->id }}">{{ $rowline->name }}</option>
                                            @endforeach
                                        </select>    
                                    </td>
                                    <td style="background-color:` + randomColor + `;">
                                        <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">
                                            <option value="">--Silahkan pilih item--</option>
                                        </select>
                                    </td>
                                    <td style="background-color:` + randomColor + `;" class="">
                                        <input name="arr_start_date[]" type="datetime-local" required value="` + detail.start_date + `">
                                    </td>
                                    <td style="background-color:` + randomColor + `;" class="">
                                        <input name="arr_end_date[]" type="datetime-local" required value="` + detail.end_date + `">
                                    </td>
                                    <td style="background-color:` + randomColor + `;" class="">
                                        <input name="arr_note[]" type="text" required value="` + detail.note + `">
                                    </td>
                                `;
                            }else{
                                arrCountNormal.push('');
                            }
                        });

                        datanormal += `</tr></tbody></table>`;

                        $('#body-item-detail-normal').append(`
                            <tr class="row_detail_item" data-id="` + val.mopd_id + `">
                                <td class="center-align">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item-detail-normal" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                                <td style="min-width:250px !important;">
                                    ` + val.item_code + ` - ` + val.item_name + `
                                </td>
                                <td>
                                ` + datanormal + `
                                </td>
                            </tr>
                        `);

                        $.each(val.details, function(i, detail) {
                            if(detail.type == 'normal' && arrCountNormal[i] !== ''){
                                if(detail.item_id){
                                    $('#arr_item_detail_id' + arrCountNormal[i]).append(`
                                        <option value="` + detail.item_id + `">` + detail.item_code + `</value>
                                    `);
                                }
                                if(detail.bom_id){
                                    $('#arr_bom' + arrCountNormal[i]).append(`
                                        <option value="` + detail.bom_id + `">` + detail.bom_code + `</value>
                                    `);
                                }
                                if(detail.line_id){
                                    $('#arr_line' + arrCountNormal[i]).val(detail.line_id);
                                }
                                if(detail.warehouse_id){
                                    $('#arr_warehouse' + arrCountNormal[i]).empty();
                                    $.each(detail.list_warehouse, function(j, value) {
                                        $('#arr_warehouse' + arrCountNormal[i]).append(`
                                            <option value="` + value.id + `">` + value.name + `</option>
                                        `);
                                    });
                                    $('#arr_warehouse' + arrCountNormal[i]).val(detail.warehouse_id);
                                }
                                select2ServerSide('#arr_item_detail_id' + arrCountNormal[i], '{{ url("admin/select2/item_has_bom") }}');
                                $('#arr_bom' + arrCountNormal[i]).select2({
                                    placeholder: '-- Kosong --',
                                    minimumInputLength: 1,
                                    allowClear: true,
                                    cache: true,
                                    width: 'resolve',
                                    dropdownParent: $('body').parent(),
                                    ajax: {
                                        url: '{{ url("admin/select2/bom_by_item") }}',
                                        type: 'GET',
                                        dataType: 'JSON',
                                        data: function(params) {
                                            return {
                                                search: params.term,
                                                item_id: $('#arr_item_detail_id' + arrCountNormal[i]).val(),
                                                place_id: $('#place_id').val(),
                                            };
                                        },
                                        processResults: function(data) {
                                            return {
                                                results: data.items
                                            }
                                        }
                                    }
                                });
                            }
                        });

                        let datapowder = `<table class="bordered"><thead><tr>`;
                        
                        $.each(val.details, function(i, detail) {
                            if(detail.type == 'powder'){
                                datapowder += `
                                    <th class="center" style="min-width:150px !important;">Item</th>
                                    <th class="center" style="min-width:150px !important;">Qty</th>
                                    <th class="center" style="min-width:150px !important;">Satuan UoM</th>
                                    <th class="center" style="min-width:150px !important;">BOM</th>
                                    <th class="center" style="min-width:150px !important;">Line</th>
                                    <th class="center" style="min-width:150px !important;">Gudang</th>
                                    <th class="center" style="min-width:150px !important;">Tgl.Mulai</th>
                                    <th class="center" style="min-width:150px !important;">Tgl.Selesai</th>
                                    <th class="center" style="min-width:150px !important;">Remark</th>
                                `;
                            }
                        });

                        datapowder += `</tr></thead><tbody><tr>`;

                        let arrCountPowder = [];

                        $.each(val.details, function(i, detail) {
                            if(detail.type == 'powder'){
                                var count = makeid(10);
                                arrCountPowder.push(count);
                                let randomColor = getRandomColor();
                                datapowder += `
                                    <input type="hidden" name="arr_type[]" value="normal">
                                    <input type="hidden" name="arr_detail_id[]" id="arr_detail_id` + count + `" value="` + detail.mopd_id + `">
                                    <td style="background-color:` + randomColor + `;">
                                        <select class="browser-default" id="arr_item_detail_id` + count + `" name="arr_item_detail_id[]" onchange="getRowUnit('` + count + `','normal')" required></select>
                                    </td>
                                    <td style="background-color:` + randomColor + `;" class="right-align">
                                        <input name="arr_detail_qty[]" onfocus="emptyThis(this);" id="arr_detail_qty` + count + `" type="text" value="` + detail.qty + `" onkeyup="formatRupiahNoMinus(this);" required style="width:100%;text-align:right;">
                                    </td>
                                    <td style="background-color:` + randomColor + `;" class="center-align" id="arr_unit` + count + `">
                                        ` + detail.uom + `
                                    </td>
                                    <td style="background-color:` + randomColor + `;" class="">
                                        <select class="browser-default" id="arr_bom` + count + `" name="arr_bom[]"></select>
                                    </td>
                                    <td style="background-color:` + randomColor + `;">
                                        <select class="browser-default" id="arr_line` + count + `" name="arr_line[]">
                                            <option value="">--Kosong--</option>
                                            @foreach ($line as $rowline)
                                                <option value="{{ $rowline->id }}">{{ $rowline->name }}</option>
                                            @endforeach
                                        </select>    
                                    </td>
                                    <td style="background-color:` + randomColor + `;">
                                        <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">
                                            <option value="">--Silahkan pilih item--</option>
                                        </select>
                                    </td>
                                    <td style="background-color:` + randomColor + `;" class="">
                                        <input name="arr_start_date[]" type="datetime-local" required value="` + detail.start_date + `">
                                    </td>
                                    <td style="background-color:` + randomColor + `;" class="">
                                        <input name="arr_end_date[]" type="datetime-local" required value="` + detail.end_date + `">
                                    </td>
                                    <td style="background-color:` + randomColor + `;" class="">
                                        <input name="arr_note[]" type="text" required value="` + detail.note + `">
                                    </td>
                                `;
                            }else{
                                arrCountPowder.push('');
                            }
                        });

                        datapowder += `</tr></tbody></table>`;

                        $('#body-item-detail-powder').append(`
                            <tr class="row_detail_item" data-id="` + val.mopd_id + `">
                                <td class="center-align">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item-detail-normal" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                                <td style="min-width:250px !important;">
                                    ` + val.item_code + ` - ` + val.item_name + `
                                </td>
                                <td>
                                ` + datapowder + `
                                </td>
                            </tr>
                        `);

                        $.each(val.details, function(i, detail) {
                            if(detail.type == 'powder' && arrCountPowder[i] !== ''){
                                if(detail.item_id){
                                    $('#arr_item_detail_id' + arrCountPowder[i]).append(`
                                        <option value="` + detail.item_id + `">` + detail.item_code + `</value>
                                    `);
                                }
                                if(detail.bom_id){
                                    $('#arr_bom' + arrCountPowder[i]).append(`
                                        <option value="` + detail.bom_id + `">` + detail.bom_code + `</value>
                                    `);
                                }
                                if(detail.line_id){
                                    $('#arr_line' + arrCountPowder[i]).val(detail.line_id);
                                }
                                if(detail.warehouse_id){
                                    $('#arr_warehouse' + arrCountPowder[i]).empty();
                                    $.each(detail.list_warehouse, function(j, value) {
                                        $('#arr_warehouse' + arrCountPowder[i]).append(`
                                            <option value="` + value.id + `">` + value.name + `</option>
                                        `);
                                    });
                                    $('#arr_warehouse' + arrCountPowder[i]).val(detail.warehouse_id);
                                }
                                select2ServerSide('#arr_item_detail_id' + arrCountPowder[i], '{{ url("admin/select2/item_has_bom") }}');
                                $('#arr_bom' + arrCountPowder[i]).select2({
                                    placeholder: '-- Kosong --',
                                    minimumInputLength: 1,
                                    allowClear: true,
                                    cache: true,
                                    width: 'resolve',
                                    dropdownParent: $('body').parent(),
                                    ajax: {
                                        url: '{{ url("admin/select2/bom_by_item") }}',
                                        type: 'GET',
                                        dataType: 'JSON',
                                        data: function(params) {
                                            return {
                                                search: params.term,
                                                item_id: $('#arr_item_detail_id' + arrCountPowder[i]).val(),
                                                place_id: $('#place_id').val(),
                                            };
                                        },
                                        processResults: function(data) {
                                            return {
                                                results: data.items
                                            }
                                        }
                                    }
                                });
                            }
                        });
                    });

                    countTarget();
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
                    title : 'Jadwal Produksi',
                    intro : 'Form ini digunakan untuk mengelola data penjadwalan produksi sesuai .'
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
                    title : 'Perusahaan',
                    element : document.querySelector('.step3'),
                    intro : 'Perusahaan dimana dokumen ini dibuat.' 
                },
                {
                    title : 'Plant',
                    element : document.querySelector('.step4'),
                    intro : 'Plant dimana produksi akan dijalankan.' 
                },
                {
                    title : 'Tgl. Posting',
                    element : document.querySelector('.step5'),
                    intro : 'Tanggal posting yang akan muncul pada saat dokumen dicetak, difilter atau diproses pada form lainnya.' 
                },
                {
                    title : 'File Lampiran',
                    element : document.querySelector('.step6'),
                    intro : 'Silahkan unggah file lampiran. Untuk saat ini hanya bisa mengakomodir 1 file lampiran saja. Jika ingin menambahkan file lebih dari 1, silahkan gabungkan file anda menjadi pdf.' 
                },
                {
                    title : 'Marketing Order Produksi',
                    element : document.querySelector('.step7'),
                    intro : 'Silahkan pilih MOP yang ingin diproses produksinya. Anda bisa memilih lebih dari satu MOP untuk satu kali transaksi dokumen Jadwal Produksi.' 
                },
                {
                    title : 'Tombol tambah MOP',
                    element : document.querySelector('.step8'),
                    intro : 'Tombol untuk menambahkan data item MOP ke dalam tabel 3 Detail Target Produksi.' 
                },
                {
                    title : 'Data MOP Terpakai',
                    element : document.querySelector('.step9'),
                    intro : 'Data MOP yang terpakai pada saat ditambahkan ke dalam sistem sesuai dengan pengguna aktif saat ini. Silahkan hapus agar MOP bisa diakses oleh pengguna lainnya.' 
                },
                {
                    title : 'Detail Target Produksi',
                    element : document.querySelector('.step10'),
                    intro : 'Berisi detail produk / item yang ingin dijadikan target proses Produksi.'
                },
                {
                    title : 'Detail Shift',
                    element : document.querySelector('.step11'),
                    intro : 'Berisi detail produk / item yang ingin dijadikan target proses Produksi serta shift yang ingin dicatat.'
                },
                {
                    title : 'Tombol Simpan',
                    element : document.querySelector('.step12'),
                    intro : 'Silahkan tekan tombol ini untuk menyimpan data, namun pastikan data yang akan anda masukkan benar.' 
                },
            ]
        }).start();
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

    function updatePrevious(element){
        previous = $(element).val();
    }

    function updateDocumentStatus(code,element){
        var status = $(element).val();
        if($(element).val()){
            swal({
                title: "Apakah anda yakin ingin update status?",
                text: "Untuk status PROSES, maka akan otomatis membuat PDO.",
                icon: 'warning',
                dangerMode: true,
                buttons: {
                    cancel: 'Tidak, jangan!',
                    delete: 'Ya, lanjutkan!'
                }
            }).then(function (willDelete) {
                if (willDelete) {
                    $.ajax({
                        type : "POST",
                        url  : '{{ Request::url() }}/update_document_status',
                        data : {
                            code : code,
                            status : status,
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        cache: false,
                        beforeSend: function() {
                            loadingOpen('#datatable_serverside');
                        },
                        success: function(data){
                            loadingClose('#datatable_serverside');
                            if(data.status == '200'){
                                M.toast({
                                    html: data.message
                                });
                                $('#pod-' + code).text(data.value);
                            }else{
                                if(data.status == '422'){
                                    $(element).val(data.value);
                                }
                                swal({
                                    title: 'Ups!',
                                    text: data.message,
                                    icon: 'warning'
                                });
                                $(element).val(previous);
                            }
                        }
                    });
                }else{
                    $(element).val(previous);
                }
            });
        }else{
            $(element).val(previous);
        }
    }
</script>