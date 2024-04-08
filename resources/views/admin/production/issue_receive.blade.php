<style>
    .modal {
        top:0px !important;
    }

    .select-wrapper, .select2-container {
        height:3.6rem !important;
    }
    
    table.bordered th {
        padding: 5px !important;
    }

    body.tab-active input:focus {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .modal-content .select2.tab-active {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    #table-detail-item td, #table-detail-item th{
        padding: 5px 5px;
        border: 1px solid rgba(10, 10, 10, 1) !important;
    }

    #table-detail-row td, #table-detail-row th{
        padding: 5px 5px;
        border: 1px solid rgba(10, 10, 10, 1) !important;
    }

    #sticky {
        position: -webkit-sticky;
        position: sticky;
        top: 0;
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
                                                    <input type="date" max="{{ date('9999'.'-12-31') }}" id="start_date" name="start_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="finish_date" style="font-size:1rem;">End Date (Tanggal Akhir Posting) :</label>
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
                                                        <th>Tgl.Post</th>
                                                        <th>No.PROD</th>
                                                        <th>No.Jadwal</th>
                                                        <th>Shift</th>
                                                        <th>Line</th>
                                                        <th>Group</th>
                                                        <th>Plant</th>
                                                        <th>Gudang</th>
                                                        <th>Area</th>
                                                        <th>Dokumen</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;">
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
                                        <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                        <label class="active" for="post_date">Tgl. Post</label>
                                    </div>
                                    <div class="file-field input-field col m3 s12 step5">
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
                        <div class="row mt-3" id="sticky" style="z-index:99 !important;background-color: #ffffff !important;border-radius:30px !important;">
                            <div class="col s12">
                                <fieldset>
                                    <legend>2. Order Produksi</legend>
                                    <div class="input-field col m3 s12 step6">
                                        <select class="browser-default" id="production_order_id" name="production_order_id"></select>
                                        <label class="active" for="production_order_id">Daftar Order Produksi</label>
                                    </div>
                                    <div class="col m2 s12 step7">
                                        <a class="waves-effect waves-light cyan btn-small mb-1 mr-1 mt-5" onclick="getProductionOrder();" href="javascript:void(0);">
                                            <i class="material-icons left">add</i> Order Produksi
                                        </a>
                                    </div>
                                    <div class="col m4 s12 step8">
                                        <h6>Data Terpakai : <i id="list-used-data"></i></h6>
                                    </div>
                                    <div class="col m12">
                                        <div class="row">
                                            <div class="col m4 s12">
                                                Shift : <b id="output-shift">-</b>
                                            </div>
                                            <div class="col m4 s12">
                                                Grup : <b id="output-group">-</b>
                                            </div>
                                            <div class="col m4 s12">
                                                Line : <b id="output-line">-</b>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12 step9">
                                <fieldset style="min-width: 100%;">
                                    <legend>3. Detail Item Issue Receive</legend>
                                    <div class="col m12 s12" style="overflow:auto;width:100% !important;">
                                        <ul class="collapsible">
                                            <li class="active">
                                                <div class="collapsible-header red darken-1 text-white" style="color:white;"><i class="material-icons">file_upload</i>ISSUE</div>
                                                <div class="collapsible-body" style="display:block;">
                                                    <div class="" style="overflow:auto;width:100% !important;">
                                                        <p class="mt-2 mb-2">
                                                            <table class="bordered" style="border: 1px solid;" id="table-detail-item-issue">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="center">No.</th>
                                                                        <th class="center">Item/Coa</th>
                                                                        <th class="center">Qty Planned</th>
                                                                        <th class="center">Qty Real</th>
                                                                        <th class="center">Satuan Produksi</th>
                                                                        <th class="center">Plant & Gudang</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="body-item-issue">
                                                                    <tr id="last-row-item-issue">
                                                                        <td class="center-align" colspan="6">
                                                                            Silahkan tambahkan Order Produksi untuk memulai...
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </p>
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="collapsible-header green darken-1 text-white" style="color:white;"><i class="material-icons">file_download</i>RECEIVE</div>
                                                <div class="collapsible-body">
                                                    <div class="" style="overflow:auto;width:100% !important;">
                                                        <p class="mt-2 mb-2">
                                                            <table class="bordered" style="border: 1px solid;width:1750px !important;" id="table-detail-item-receive">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="center" width="5%">No.</th>
                                                                        <th class="center" width="15%">Item/Coa</th>
                                                                        <th class="center" width="10%">Qty Planned (Prod.)</th>
                                                                        <th class="center" width="10%">Qty Real (Prod.)</th>
                                                                        <th class="center" width="10%">Qty UoM</th>
                                                                        <th class="center" width="10%">Qty Jual</th>
                                                                        <th class="center" width="10%">Qty Pallet</th>
                                                                        <th class="center" width="15%">Shading</th>
                                                                        <th class="center" width="15%">Batch</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="body-item-receive">
                                                                    <tr id="last-row-item-receive">
                                                                        <td class="center-align" colspan="9">
                                                                            Silahkan tambahkan Order Produksi untuk memulai...
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </p>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12 mt-3">
                                <button class="btn waves-effect waves-light right submit step10" onclick="save();">Simpan <i class="material-icons right">send</i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light purple btn-panduan" onclick="startIntro();">Panduan <i class="material-icons right">help_outline</i></button>
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
    
    $(function() {

        $("#table-detail-item-issue th,#table-detail-item-receive th").resizable({
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
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
                $('.tabs').tabs({
                    onShow: function () {
                        
                    }
                });
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
                M.updateTextFields();
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }
                $('input').css('border', 'none');
                $('input').css('border-bottom', '0.5px solid black');
                window.onbeforeunload = function() {
                    return null;
                };
                $('#output-shift,#output-line,#output-group').text('-');
                $('#production_order_id').empty();
                $('#body-item-issue').empty().append(`
                    <tr id="last-row-item-issue">
                        <td class="center-align" colspan="6">
                            Silahkan tambahkan Order Produksi untuk memulai...
                        </td>
                    </tr>
                `);
                $('#body-item-receive').empty().append(`
                    <tr id="last-row-item-receive">
                        <td class="center-align" colspan="9">
                            Silahkan tambahkan Order Produksi untuk memulai...
                        </td>
                    </tr>
                `);
            }
        });

        select2ServerSide('#production_order_id', '{{ url("admin/select2/production_order") }}');
    });

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
            beforeSend: function() {
                
            },
            success: function(response) {
                $('.row_item_issue[data-id="' + id + '"],.row_item_receive[data-id="' + id + '"]').remove();
                $('#body-item-issue').empty().append(`
                    <tr id="last-row-item-issue">
                        <td class="center-align" colspan="6">
                            Silahkan tambahkan Order Produksi untuk memulai...
                        </td>
                    </tr>
                `);
                $('#body-item-receive').empty().append(`
                    <tr id="last-row-item-receive">
                        <td class="center-align" colspan="9">
                            Silahkan tambahkan Order Produksi untuk memulai...
                        </td>
                    </tr>
                `);
                $('#production_order_id').empty();
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

    function getProductionOrder(){
        if($('#production_order_id').val()){
            let datakuy = $('#production_order_id').select2('data')[0];
            $.ajax({
                url: '{{ Request::url() }}/send_used_data',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: $('#production_order_id').val(),
                    type: datakuy.table,
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

                        $('#list-used-data').append(`
                            <div class="chip purple darken-4 gradient-shadow white-text">
                                ` + datakuy.code + `
                                <i class="material-icons close data-used" onclick="removeUsedData('` + datakuy.table + `','` + $('#production_order_id').val() + `')">close</i>
                            </div>
                        `);

                        $('.row_item_issue,.row_item_receive').remove();
                        
                        $('#last-row-item-issue,#last-row-item-receive').remove();

                        let no_issue = $('.row_item_issue').length + 1;
                        let no_receive = $('.row_item_receive').length + 1;

                        $.each(datakuy.detail_issue, function(i, val) {
                            var count = makeid(10);
                            let optionStock = `<select class="browser-default" id="arr_item_stock_id` + count + `" name="arr_item_stock_id[]">`;
                            if(val.list_stock.length > 0){
                                $.each(val.list_stock, function(i, valkuy) {
                                    optionStock += `<option value="` + valkuy.id + `" data-qty="` + valkuy.qty_production_raw + `">` + valkuy.warehouse + ` - ` + valkuy.qty_production + `</option>`;
                                });
                            }else{
                                optionStock += `<option value="">--Maaf, item ini tidak memiliki stock--</option>`;
                            }
                            optionStock += `</select>`;
                            $('#body-item-issue').append(`
                                <tr class="row_item_issue" data-id="` + $('#production_order_id').val() + `">
                                    <input type="hidden" name="arr_type[]" value="1">
                                    <input type="hidden" name="arr_lookable_type[]" value="` + val.lookable_type + `">
                                    <input type="hidden" name="arr_lookable_id[]" value="` + val.lookable_id + `">
                                    <input type="hidden" name="arr_production_detail_id[]" value="` + val.id + `">
                                    <input type="hidden" name="arr_bom_detail_id[]" value="` + val.bom_detail_id + `">
                                    <input type="hidden" name="arr_nominal[]" data-standard="` + val.nominal + `" value="` + val.nominal + `">
                                    <input type="hidden" name="arr_total[]" data-standard="` + val.total + `" value="` + val.total + `">
                                    <input type="hidden" name="arr_shading[]" value="">
                                    <input type="hidden" name="arr_batch[]" value="">
                                    ` + (val.lookable_type == 'coas' ? `<input name="arr_qty[]" type="hidden" value="0">` : '') + `
                                    <td class="center-align">
                                        ` + no_issue + `
                                    </td>
                                    <td>
                                        ` + val.lookable_code + ` - ` + val.lookable_name + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.qty + `
                                    </td>
                                    <td class="center">
                                        ` + (val.lookable_type == 'items' ? `<input name="arr_qty[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiah(this);checkRowQty('` + count + `');" style="text-align:right;width:100%;" id="rowQty`+ count +`" required>` : '-') + `
                                    </td>
                                    <td class="center">
                                        ` + val.lookable_unit + `
                                    </td>
                                    <td class="center">
                                        ` + optionStock + `
                                    </td>
                                </tr>
                            `);
                            no_issue++;
                            $('#rowQty' + count).trigger('keyup');
                        });

                        var count = makeid(10);

                        let datalist = `<datalist id="list-shading` + count + `">`;
                        if(datakuy.list_shading.length > 0){
                            $.each(datakuy.list_shading, function(i, valkuy) {
                                datalist += `<option value="` + valkuy.code + `">` + valkuy.code + `</option>`;
                            });
                        }
                        datalist += `</datalist>`;

                        $('#body-item-receive').append(`
                            <tr class="row_item_receive" data-id="` + $('#production_order_id').val() + `">
                                <input type="hidden" name="arr_type[]" value="2">
                                <input type="hidden" name="arr_lookable_type[]" value="items">
                                <input type="hidden" name="arr_lookable_id[]" value="` + datakuy.item_receive_id + `">
                                <input type="hidden" name="arr_production_detail_id[]" value="">
                                <input type="hidden" name="arr_bom_detail_id[]" value="">
                                <input type="hidden" name="arr_nominal[]" data-standard="0,00" value="0,00">
                                <input type="hidden" name="arr_total[]" data-standard="0,00" value="0,00">                                
                                <td class="center-align">
                                    ` + no_receive + `
                                </td>
                                <td>
                                    ` + datakuy.item_receive_code + ` - ` + datakuy.item_receive_name + `
                                </td>
                                <td class="right-align">
                                    ` + datakuy.item_receive_qty + `
                                </td>
                                <td class="center">
                                    <div class="input-field col s10">
                                        <input name="arr_qty[]" onfocus="emptyThis(this);" type="text" value="` + datakuy.item_receive_qty + `" onkeyup="formatRupiah(this);countRow(this);" style="text-align:right;width:100%;margin: 0 0 0 0 !important;height:1.25rem !important;font-size:0.9rem !important;" id="rowQty`+ count +`" data-id="` + count + `" data-production="` + datakuy.production_convert + `" data-sell="` + datakuy.sell_convert + `" data-pallet="` + datakuy.pallet_convert + `" data-qtystandard="` + datakuy.item_receive_qty + `" required>
                                        <div class="form-control-feedback" id="production-unit` + count + `" style="right:-30px;top:-10px;">` + datakuy.item_receive_unit_production + `</div>
                                    </div>
                                </td>
                                <td class="right-align">
                                    <b id="uom-unit` + count + `">-</b>&nbsp;<b>` + datakuy.item_receive_unit_uom + `</b>
                                </td>
                                <td class="right-align">
                                    <b id="sell-unit` + count + `">-</b>&nbsp;<b>` + datakuy.item_receive_unit_sell + `</b>
                                </td>
                                <td class="right-align">
                                    <b id="pallet-unit` + count + `">-</b>&nbsp;<b>` + datakuy.item_receive_unit_pallet + `</b>
                                </td>
                                <td class="center">
                                    <input list="list-shading` + count + `" name="arr_shading[]" class="browser-default" id="arr_shading` + count + `" type="text" placeholder="Kode Shading..." style="width:100%;" required>
                                    ` + datalist + `
                                </td>
                                <td class="center">
                                    <input name="arr_batch[]" class="browser-default" type="text" placeholder="Nomor batch..." style="width:100%;" required>
                                    <select class="browser-default" id="arr_item_stock_id` + count + `" name="arr_item_stock_id[]" style="display:none">
                                        <option value="">--Maaf, item ini tidak memiliki stock--</option>
                                    </select>
                                </td>
                            </tr>
                        `);
                        no_receive++;
                        $('#rowQty' + count).trigger('keyup');
                        /* $('#production_order_id').empty(); */
                        $('#output-shift').empty().text(datakuy.shift);
                        $('#output-group').empty().text(datakuy.group);
                        $('#output-line').empty().text(datakuy.line);
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
            $('#output-shift,#output-line,#output-group').text('-');
            $('#body-item-issue').empty().append(`
                <tr id="last-row-item-issue">
                    <td class="center-align" colspan="6">
                        Silahkan tambahkan Order Produksi untuk memulai...
                    </td>
                </tr>
            `);
            $('#body-item-receive').empty().append(`
                <tr id="last-row-item-receive">
                    <td class="center-align" colspan="9">
                        Silahkan tambahkan Order Produksi untuk memulai...
                    </td>
                </tr>
            `);
        }
    }

    function checkRowQty(val){
        if($('#arr_item_stock_id' + val).val()){
            let qtyMax = parseFloat($('#arr_item_stock_id' + val).find(':selected').data('qty').replaceAll(".", "").replaceAll(",","."));
            let qtyNow = parseFloat($('#rowQty' + val).val().replaceAll(".", "").replaceAll(",","."));
            if(qtyNow > qtyMax){
                $('#rowQty' + val).val(
                    (qtyMax >= 0 ? '' : '-') + formatRupiahIni(qtyMax.toFixed(3).toString().replace('.',','))
                );
            }
        }
    }

    function countRow(element){
        let val = $(element).data('id');
        if($(element).val()){
            let productionConvert = $(element).data('production'), sellConvert = $(element).data('sell'), palletConvert = $(element).data('pallet');
            let qtyProduction = parseFloat($(element).val().replaceAll(".", "").replaceAll(",","."));
            let qtyUom = qtyProduction * productionConvert;
            let qtySell = qtyUom / sellConvert;
            let qtyPallet = qtySell / palletConvert;
            let qtyStandard = parseFloat($(element).data('qtystandard').replaceAll(".", "").replaceAll(",","."));
            let bobot = qtyProduction / qtyStandard;
            $('input[name^="arr_nominal[]"]').each(function(index){
                let currentNominal = parseFloat($(this).data('standard').replaceAll(".", "").replaceAll(",","."));
                $(this).val(formatRupiahIni((currentNominal * bobot).toFixed(2).toString().replace('.',',')));
            });
            $('input[name^="arr_total[]"]').each(function(index){
                let currentNominal = parseFloat($(this).data('standard').replaceAll(".", "").replaceAll(",","."));
                $(this).val(formatRupiahIni((currentNominal * bobot).toFixed(2).toString().replace('.',',')));
            });
            $('#uom-unit' + val).text(formatRupiahIni(qtyUom.toFixed(3).toString().replace('.',',')));
            $('#sell-unit' + val).text(formatRupiahIni(qtySell.toFixed(3).toString().replace('.',',')));
            $('#pallet-unit' + val).text(formatRupiahIni(qtyPallet.toFixed(3).toString().replace('.',',')));
        }else{
            $('#uom-unit' + val + ',#sell-unit' + val + ',#pallet-unit' + val).text('-');
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
                { name: 'post_date', className: 'center-align' },
                { name: 'production_order_id', searchable: false, orderable: false, className: 'center-align' },
                { name: 'production_schedule_id', searchable: false, orderable: false, className: 'center-align' },
                { name: 'shift', searchable: false, orderable: false, className: 'center-align' },
                { name: 'line', searchable: false, orderable: false, className: 'center-align' },
                { name: 'group', searchable: false, orderable: false, className: 'center-align' },
                { name: 'plant_id', searchable: false, orderable: false, className: 'center-align' },
                { name: 'warehouse_id', searchable: false, orderable: false, className: 'center-align' },
                { name: 'area_id', searchable: false, orderable: false, className: 'center-align' },
                { name: 'document', searchable: false, orderable: false, className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
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
                
                var formData = new FormData($('#form_data')[0]);

                /* for (var pair of formData.entries()) {
                    console.log(pair[0]+ ', ' + pair[1]); 
                } */
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
                $('#production_order_id').empty().append(`
                    <option value="` + response.production_order_id + `">` + response.production_order_code + `</option>
                `);

                $('.row_item_issue,.row_item_receive').remove();
                        
                $('#last-row-item-issue,#last-row-item-receive').remove();

                let no_issue = $('.row_item_issue').length + 1;
                let no_receive = $('.row_item_receive').length + 1;

                $.each(response.detail_issue, function(i, val) {
                    var count = makeid(10);
                    let optionStock = `<select class="browser-default" id="arr_item_stock_id` + count + `" name="arr_item_stock_id[]">`;
                    if(val.list_stock.length > 0){
                        $.each(val.list_stock, function(i, valkuy) {
                            optionStock += `<option value="` + valkuy.id + `" ` + (val.item_stock_id == valkuy.id ? 'selected' : '') + ` data-qty="` + valkuy.qty_production_raw + `">` + valkuy.warehouse + ` - ` + valkuy.qty_production + `</option>`;
                        });
                    }else{
                        optionStock += `<option value="">--Maaf, item ini tidak memiliki stock--</option>`;
                    }
                    optionStock += `</select>`;
                    $('#body-item-issue').append(`
                        <tr class="row_item_issue" data-id="` + response.production_order_id + `">
                            <input type="hidden" name="arr_type[]" value="` + val.type + `">
                            <input type="hidden" name="arr_lookable_type[]" value="` + val.lookable_type + `">
                            <input type="hidden" name="arr_lookable_id[]" value="` + val.lookable_id + `">
                            <input type="hidden" name="arr_production_detail_id[]" value="` + val.id + `">
                            <input type="hidden" name="arr_bom_detail_id[]" value="` + val.bom_detail_id + `">
                            <input type="hidden" name="arr_nominal[]" data-standard="` + val.nominal_standard + `" value="` + val.nominal + `">
                            <input type="hidden" name="arr_total[]" data-standard="` + val.total_standard + `" value="` + val.total + `">
                            <input type="hidden" name="arr_shading[]" value="">
                            <input type="hidden" name="arr_batch[]" value="">
                            ` + (val.lookable_type == 'coas' ? `<input name="arr_qty[]" type="hidden" value="0">` : '') + `
                            <td class="center-align">
                                ` + no_issue + `
                            </td>
                            <td>
                                ` + val.lookable_code + ` - ` + val.lookable_name + `
                            </td>
                            <td class="right-align">
                                ` + val.qty_standard + `
                            </td>
                            <td class="center">
                                ` + (val.lookable_type == 'items' ? `<input name="arr_qty[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiah(this);checkRowQty('` + count + `');" style="text-align:right;width:100%;" id="rowQty`+ count +`" required>` : '-') + `
                            </td>
                            <td class="center">
                                ` + val.lookable_unit + `
                            </td>
                            <td class="center">
                                ` + optionStock + `
                            </td>
                        </tr>
                    `);
                    no_issue++;
                });

                var count = makeid(10);
                $('#body-item-receive').append(`
                    <tr class="row_item_receive" data-id="` + $('#production_order_id').val() + `">
                        <input type="hidden" name="arr_type[]" value="2">
                        <input type="hidden" name="arr_lookable_type[]" value="items">
                        <input type="hidden" name="arr_lookable_id[]" value="` + response.item_receive_id + `">
                        <input type="hidden" name="arr_production_detail_id[]" value="">
                        <input type="hidden" name="arr_bom_detail_id[]" value="">
                        <input type="hidden" name="arr_nominal[]" data-standard="0,00" value="0,00">
                        <input type="hidden" name="arr_total[]" data-standard="0,00" value="0,00">                                
                        <td class="center-align">
                            ` + no_receive + `
                        </td>
                        <td>
                            ` + response.item_receive_code + ` - ` + response.item_receive_name + `
                        </td>
                        <td class="right-align">
                            ` + response.item_receive_qty + `
                        </td>
                        <td class="center">
                            <div class="input-field col s10">
                                <input name="arr_qty[]" onfocus="emptyThis(this);" type="text" value="` + response.qty + `" onkeyup="formatRupiah(this);countRow(this);" style="text-align:right;width:100%;margin: 0 0 0 0 !important;height:1.25rem !important;font-size:0.9rem !important;" id="rowQty`+ count +`" data-id="` + count + `" data-production="` + response.production_convert + `" data-sell="` + response.sell_convert + `" data-pallet="` + response.pallet_convert + `" data-qtystandard="` + response.item_receive_qty + `" required>
                                <div class="form-control-feedback" id="production-unit` + count + `" style="right:-30px;top:-10px;">` + response.item_receive_unit_production + `</div>
                            </div>
                        </td>
                        <td class="right-align">
                            <b id="uom-unit` + count + `">-</b>&nbsp;<b>` + response.item_receive_unit_uom + `</b>
                        </td>
                        <td class="right-align">
                            <b id="sell-unit` + count + `">-</b>&nbsp;<b>` + response.item_receive_unit_sell + `</b>
                        </td>
                        <td class="right-align">
                            <b id="pallet-unit` + count + `">-</b>&nbsp;<b>` + response.item_receive_unit_pallet + `</b>
                        </td>
                        <td class="center">
                            <input name="arr_shading[]" class="browser-default" type="text" placeholder="Kode Shading..." style="width:100%;" value="` + response.shading + `" required>
                        </td>
                        <td class="center">
                            <input name="arr_batch[]" class="browser-default" type="text" placeholder="Nomor batch..." style="width:100%;" value="` + response.batch_no + `" required>
                            <select class="browser-default" id="arr_item_stock_id` + count + `" name="arr_item_stock_id[]" style="display:none">
                                <option value="">--Maaf, item ini tidak memiliki stock--</option>
                            </select>
                        </td>
                    </tr>
                `);
                no_receive++;
                $('#rowQty' + count).trigger('keyup');
                $('#output-shift').empty().text(response.shift);
                $('#output-group').empty().text(response.group);
                $('#output-line').empty().text(response.line);
                M.updateTextFields();
                $('.modal-content').scrollTop(0);
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
                    title : 'Issue Receive Produksi',
                    intro : 'Form ini digunakan untuk mengelola data hasil produksi untuk bahan yang digunakan maupun hasil dari produksi. Satu Order Produksi hanya bisa ditarik ke satu dokumen Issue Receive Produksi.'
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
                    title : 'Tgl. Posting',
                    element : document.querySelector('.step4'),
                    intro : 'Tanggal posting yang akan muncul pada saat dokumen dicetak, difilter atau diproses pada form lainnya.' 
                },
                {
                    title : 'File Lampiran',
                    element : document.querySelector('.step5'),
                    intro : 'Silahkan unggah file lampiran. Untuk saat ini hanya bisa mengakomodir 1 file lampiran saja. Jika ingin menambahkan file lebih dari 1, silahkan gabungkan file anda menjadi pdf.' 
                },
                {
                    title : 'Daftar Order Produksi',
                    element : document.querySelector('.step6'),
                    intro : 'Silahkan pilih dokumen Order Produksi yang ingin ditarik komposisi bahannya dari BOM untuk diproses.' 
                },
                {
                    title : 'Tombol tambah Order Produksi',
                    element : document.querySelector('.step7'),
                    intro : 'Tombol untuk menambahkan data BOM dari Order Produksi terpilih ke dalam tabel Issue dan Receive.' 
                },
                {
                    title : 'Data Order Produksi Terpakai',
                    element : document.querySelector('.step8'),
                    intro : 'Data Order Produksi yang terpakai pada saat ditambahkan ke dalam sistem sesuai dengan pengguna aktif saat ini. Silahkan hapus bisa diakses oleh pengguna lainnya.' 
                },
                {
                    title : 'Detail Issue Item/Resource dan Receive Item',
                    element : document.querySelector('.step9'),
                    intro : 'Berisi detail item/resource issue dan receive.'
                },
                {
                    title : 'Tombol Simpan',
                    element : document.querySelector('.step10'),
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

    function done(id){
        swal({
            title: "Apakah anda yakin ingin menyelesaikan dokumen ini?",
            text: "Data yang sudah terupdate tidak dapat dikembalikan.",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                $.ajax({
                    url: '{{ Request::url() }}/done',
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