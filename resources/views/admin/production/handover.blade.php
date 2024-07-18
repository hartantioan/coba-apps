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

    /* #sticky {
        position: -webkit-sticky;
        position: sticky;
        top: 50px;
    } */

    #table-detail-item input {
        height:2rem !important;
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
                                                <label for="filter_status" style="font-size:1rem;">{{ __('translations.filter_status') }} :</label>
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
                                        </div> 
                                        <div class="col s12">
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-1" href="javascript:void(0);" onclick="loadDataTable();">
                                                <span class="hide-on-small-onl">{{ __('translations.refresh') }}</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn" href="javascript:void(0);" onclick="exportExcel();">
                                                <span class="hide-on-small-onl">Export</span>
                                                <i class="material-icons right">view_headline</i>
                                            </a>
                                            <table id="datatable_serverside" >
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{ __('translations.code') }}</th>
                                                        <th>{{ __('translations.user') }}</th>
                                                        <th>{{ __('translations.company') }}</th>
                                                        <th>Tgl.Post</th>
                                                        <th>{{ __('translations.note') }}</th>
                                                        <th>No.Receive FG</th>
                                                        <th>Dokumen</th>
                                                        <th>{{ __('translations.status') }}</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;">
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
                                        <label class="" for="company_id">{{ __('translations.company') }}</label>
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
                                    <div class="input-field col m3 s12">
                                        <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                        <label class="active" for="note">{{ __('translations.note') }}</label>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <select class="browser-default" id="production_fg_receive_id" name="production_fg_receive_id"></select>
                                        <label class="active" for="production_fg_receive_id">Daftar Receive FG</label>
                                    </div>
                                    <div class="col m6 s12">
                                        <h6><b>Receive FG Terpakai</b> (hapus untuk bisa diakses pengguna lain) : <i id="list-used-data"></i></h6>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s6">
                                <fieldset style="min-width: 100%;">
                                    <legend>2a. Dari Item dan Area</legend>
                                    <div class="input-field col m6 s12">
                                        <select class="browser-default" id="production_fg_receive_detail_id" name="production_fg_receive_detail_id" onchange="getItemInformation();"></select>
                                        <label class="active" for="production_fg_receive_detail_id">Item Receive FG</label>
                                    </div>
                                    <div class="input-field col m6 s12">
                                        <select class="browser-default" id="area_id" name="area_id"></select>
                                        <label class="active" for="area_id">Area</label>
                                    </div>
                                    <div class="input-field col m6 s12">
                                        <div class="form-control-feedback" id="qty-unit">-</div>
                                        <input id="qty" name="qty" type="text" data-max="0,000" value="0,000" onkeyup="formatRupiahNoMinus(this);checkQtyMax();">
                                        <label class="active" for="qty">Qty Input</label>
                                    </div>
                                    <div class="col m6 s12">
                                        <a class="waves-effect waves-light cyan btn-small mt-5 mr-1" onclick="getItem();" href="javascript:void(0);"><i class="material-icons left">add</i> Tambah</a>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col s6">
                                <fieldset>
                                    <legend>2b. Dari Scan Barcode</legend>
                                    <div class="input-field col m12 s12">
                                        <input id="text-barcode" name="text-barcode" type="text" value="" placeholder="Silahkan arahkan cursor disini dan mulai scan...">
                                        <label class="active" for="text-barcode">Scan disini</label>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12 step9">
                                <fieldset style="min-width: 100%;">
                                    <legend>3. Detail Item Receive FG & Palet</legend>
                                    <div class="col m12 s12">
                                        <div class="card-alert card gradient-45deg-purple-amber">
                                            <div class="card-content white-text">
                                                <p>Info : Qty diterima adalah otomatis perhitungan dari Qty input dikurangi Qty reject. Silahkan isi qty reject jika anda ingin nilai barang qty reject ditambahkan ke nilai Qty diterima.</p>
                                            </div>
                                        </div>
                                        <div class="col s12" style="overflow:auto;min-width:100%;">
                                            <p class="mt-2 mb-2">
                                                <table class="bordered" style="border: 1px solid;min-width:2000px !important;" id="table-detail-item">
                                                    <thead>
                                                        <tr>
                                                            <th class="center" width="25px">{{ __('translations.delete') }}</th>
                                                            <th class="center" width="25px">No.</th>
                                                            <th class="center" width="150px">No.Batch Palet/Curah</th>
                                                            <th class="center" width="150px">Kode Item</th>
                                                            <th class="center" width="150px">Nama Item</th>
                                                            <th class="center" width="100px">{{ __('translations.shading') }}</th>
                                                            <th class="center" width="100px">Qty Diterima</th>
                                                            <th class="center" width="100px">Satuan</th>
                                                            <th class="center" width="100px">{{ __('translations.plant') }}</th>
                                                            <th class="center" width="100px">{{ __('translations.warehouse') }}</th>
                                                            <th class="center" width="100px">{{ __('translations.area') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="body-item">
                                                        <tr id="last-row-item">
                                                            <td class="center-align" colspan="11">
                                                                Silahkan tambahkan Receive FG untuk memulai...
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </p>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light purple btn-panduan mr-1" onclick="startIntro();">Panduan <i class="material-icons right">help_outline</i></button>
        <button class="btn waves-effect waves-light mr-1 submit step10" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
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
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
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

        $("#table-detail-item th,#table-detail-item-receive th").resizable({
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
                window.onbeforeunload = function() {
                    return 'You will lose all changes made since your last save';
                };
                $('.disable-class').css('pointer-events','none');
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                M.updateTextFields();
                window.onbeforeunload = function() {
                    return null;
                };
                $('#production_fg_receive_id').empty();
                $('#body-item').empty().append(`
                    <tr id="last-row-item">
                        <td class="center-align" colspan="11">
                            Silahkan tambahkan Receive FG untuk memulai...
                        </td>
                    </tr>
                `);
                $('#body-issue').empty().append(`
                    <tr id="last-row-issue">
                        <td class="center-align" colspan="3">
                            Silahkan tambah dengan tombol dibawah
                        </td>
                    </tr>
                `);
                $('#qty').data('max','0,000');
            }
        });
        
        select2ServerSide('#production_fg_receive_id', '{{ url("admin/select2/production_fg_receive") }}');
        select2ServerSide('#area_id', '{{ url("admin/select2/area") }}');

        $('#production_fg_receive_detail_id').select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/production_fg_receive_detail") }}',
                type: 'GET',
                dataType: 'JSON',
                data: function(params) {
                    return {
                        search: params.term,
                        fgr_id: $('#production_fg_receive_id').val(),
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
                    <tr id="last-row-item">
                        <td class="center-align" colspan="11">
                            Silahkan tambahkan Receive FG untuk memulai...
                        </td>
                    </tr>
                `);
            }
        });

        $("#text-barcode").on( "keypress", function(e) {
            var key = e.which;
            if(key == 13){
                if($(this).val()){
                    let code = $(this).val();
                    $.ajax({
                        url: '{{ Request::url() }}/get_scan_barcode',
                        type: 'POST',
                        dataType: 'JSON',
                        data: {
                            code: code,
                            fgr_id: $('#production_fg_receive_id').val(),
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        beforeSend: function() {
                            loadingOpen('#modal1');
                        },
                        success: function(response) {
                            loadingClose('#modal1');
                            if(response.status == 200){
                                if($('#last-row-item').length > 0){
                                    $('#last-row-item').remove();
                                }
                                let no = $('.row_item').length + 1;
                                let count = makeid(10);
                                $('#body-item').append(`
                                    <tr class="row_item">
                                        <input type="hidden" name="arr_prfd_id[]" value="` + response.data.id + `">
                                        <input type="hidden" name="arr_item_id[]" value="` + response.data.item_id + `">
                                        <td class="center">
                                            <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);" data-id="` + count + `">
                                                <i class="material-icons">delete</i>
                                            </a>
                                        </td>
                                        <td class="center-align">
                                            ` + no + `
                                        </td>
                                        <td>
                                            ` + response.data.pallet_no + `
                                        </td>
                                        <td>
                                            ` + response.data.item_code + `
                                        </td>
                                        <td>
                                            ` + response.data.item_name + `
                                        </td>
                                        <td>
                                            ` + response.data.shading + `
                                        </td>
                                        <td class="center">
                                            <input name="arr_qty[]" type="text" value="` + response.data.qty + `" style="text-align:right;width:100%;" id="arr_qty`+ count +`" data-item="` + response.data.item_id + `" onkeyup="formatRupiahNoMinus(this);">
                                        </td>
                                        <td class="center-align">
                                            ` + response.data.unit + `
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
                                                <option value="">--Silahkan pilih item--</option>
                                            </select>
                                        </td>
                                        <td class="center-align">
                                            <select class="browser-default" id="arr_area_id` + count + `" name="arr_area_id[]"></select>
                                        </td>
                                    </tr>
                                `);

                                if(response.data.list_warehouse.length > 0){
                                    $('#arr_warehouse' + count).empty();
                                    $.each(response.data.list_warehouse, function(i, val) {
                                        $('#arr_warehouse' + count).append(`
                                            <option value="` + val.id + `">` + val.name + `</option>
                                        `);
                                    });
                                }

                                select2ServerSide('#arr_area_id' + count, '{{ url("admin/select2/area") }}');

                                $('#arr_qty_reject' + count).trigger('keyup');

                                $('.modal-content').scrollTop(400);
                                M.updateTextFields();

                            }else{
                                swal({
                                    title: 'Ups!',
                                    text: response.message,
                                    icon: 'error'
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
                }
                e.preventDefault();
            }
            $('#text-barcode').val('');
        });
    });

    function getItemInformation(){
        if($("#production_fg_receive_detail_id").val()){
            $("#qty-unit").text($("#production_fg_receive_detail_id").select2('data')[0].unit);
            let originalTotal = parseFloat($("#production_fg_receive_detail_id").select2('data')[0].qty.toString().replaceAll(".", "").replaceAll(",","."));
            let item_id = $("#production_fg_receive_detail_id").select2('data')[0].item_id;
            $('input[name^="arr_qty[]"][data-item="' + item_id + '"]').each(function(index){
                originalTotal -= parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
            });
            if(originalTotal > 0){
                $('#qty').val(
                    (originalTotal >= 0 ? '' : '-') + formatRupiahIni(originalTotal.toFixed(3).toString().replace('.',','))
                );
                $('#qty').data('max',(originalTotal >= 0 ? '' : '-') + formatRupiahIni(originalTotal.toFixed(3).toString().replace('.',',')));
            }else{
                $("#qty-unit").text('-');
                $('#qty').val('0,000');
                $('#qty').data('max','0,000');
                swal({
                    title: 'Ups!',
                    text: 'Qty telah 100% dipakai.',
                    icon: 'warning'
                });
            }
        }else{
            $("#qty-unit").text('-');
            $('#qty').val('0,000');
            $('#qty').data('max','0,000');
        }
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

    function checkQtyMax(){
        let qtyMax = parseFloat($('#qty').data('max').replaceAll(".", "").replaceAll(",","."));
        let qtyNow = parseFloat($('#qty').val().replaceAll(".", "").replaceAll(",","."));
        if(qtyNow > qtyMax){
            $('#qty').val(formatRupiahIni(qtyMax.toFixed(3).toString().replace('.',',')));
        }
    }

    function getItem(){
        if($('#production_fg_receive_detail_id').val() && $('#area_id').val() && parseFloat($('#qty').val().replaceAll(".", "").replaceAll(",",".")) > 0){
            let datakuy = $('#production_fg_receive_detail_id').select2('data')[0];
            $('#last-row-item').remove();

            var count = makeid(10);

            let no = $('.row_item').length + 1;

            var count = makeid(10);

            $('#body-item').append(`
                <tr class="row_item">
                    <input type="hidden" name="arr_prfd_id[]" value="` + $('#production_fg_receive_detail_id').val() + `">
                    <input type="hidden" name="arr_item_id[]" value="` + datakuy.item_id + `">
                    <input type="hidden" name="arr_area_id[]" value="` + $('#area_id').val() + `">
                    <td class="center">
                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);" data-id="` + count + `">
                            <i class="material-icons">delete</i>
                        </a>
                    </td>
                    <td class="center-align">
                        ` + no + `
                    </td>
                    <td>
                        ` + datakuy.pallet_no + `
                    </td>
                    <td>
                        ` + datakuy.item_code + `
                    </td>
                    <td>
                        ` + datakuy.item_name + `
                    </td>
                    <td>
                        ` + datakuy.shading + `
                    </td>
                    <td class="center">
                        <input name="arr_qty[]" onfocus="emptyThis(this);" type="text" value="` + $('#qty').val() + `" style="text-align:right;width:100%;border-bottom: none;" id="arr_qty`+ count +`" readonly data-item="` + datakuy.item_id + `">
                    </td>
                    <td class="center-align">
                        ` + datakuy.unit + `
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
                            <option value="">--Silahkan pilih item--</option>
                        </select>
                    </td>
                    <td class="center-align">
                        ` + $('#area_id').select2('data')[0].text + `
                    </td>
                </tr>
            `);

            if(datakuy.list_warehouse.length > 0){
                $('#arr_warehouse' + count).empty();
                $.each(datakuy.list_warehouse, function(i, val) {
                    $('#arr_warehouse' + count).append(`
                        <option value="` + val.id + `">` + val.name + `</option>
                    `);
                });
            }

            $('#arr_qty_reject' + count).trigger('keyup');

            $('#production_fg_receive_detail_id,#area_id').empty();
            $('#qty').val('0,000');
        }else{
            $('#body-item').empty().append(`
                <tr id="last-row-item">
                    <td class="center-align" colspan="11">
                        Silahkan tambahkan Receive FG untuk memulai...
                    </td>
                </tr>
            `);
        }
    }

    function removeBatch(element){
        $(element).parent().parent().remove();
    }

    function addIssue(){
        if($('#production_order_detail_id').val()){
            if($('#last-row-issue').length > 0){
                $('#last-row-issue').remove();
            }
            let no = $('.row_issue').length + 1;
            let count = makeid(10);
            $('#body-issue').append(`
                <tr class="row_issue">
                    <td class="center-align">
                        ` + no + `
                    </td>
                    <td>
                        <select class="browser-default" id="arr_production_issue_id` + count + `" name="arr_production_issue_id[]"></select>
                    </td>
                    <td class="center">
                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-issue" href="javascript:void(0);" data-id="` + count + `">
                            <i class="material-icons">delete</i>
                        </a>
                    </td>
                </tr>
            `);
            $('#arr_production_issue_id' + count).select2({
                placeholder: '-- Kosong --',
                minimumInputLength: 1,
                allowClear: true,
                cache: true,
                width: 'resolve',
                dropdownParent: $('body').parent(),
                ajax: {
                    url: '{{ url("admin/select2/production_issue") }}',
                    type: 'GET',
                    dataType: 'JSON',
                    data: function(params) {
                        return {
                            search: params.term,
                            pod_id: $('#production_order_detail_id').val(),
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.items
                        }
                    }
                }
            });
        }else{
            swal({
                title: 'Ups!',
                text: 'Silahkan pilih Production Order terlebih dahulu.',
                icon: 'warning'
            });
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
                { name: 'note', className: '' },
                { name: 'production_fg_receive_id', searchable: false, orderable: false, className: 'center-align' },
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
                
                var formData = new FormData($('#form_data')[0]);

                let passedQty = true;
                let passedInput = true;

                $('input[name^="arr_qty_received[]"]').each(function(index){
                    if(parseFloat($(this).val().replaceAll(".", "").replaceAll(",",".")) <= 0){
                        passedQty = false;
                    }
                    if(parseFloat($('input[name^="arr_qty[]"]').eq(index).val().replaceAll(".", "").replaceAll(",",".")) <= 0 || !$('input[name^="arr_qty[]"]').eq(index).val()){
                        passedQty = false;
                    }
                    if(!$('input[name^="arr_qty_reject[]"]').eq(index).val()){
                        passedQty = false;
                    }
                    if(!$('*[name^="arr_area_id[]"]').eq(index).val()){
                        passedInput = false;
                    }
                });
                
                if(!passedQty){
                    swal({
                        title: 'Ups! Maaf.',
                        text: 'Qty diterima tidak boleh kosong.',
                        icon: 'error'
                    });

                    return false;
                }

                if(!passedInput){
                    swal({
                        title: 'Ups! Maaf.',
                        text: 'Area tidak boleh kosong.',
                        icon: 'error'
                    });

                    return false;
                }

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
                        loadingClose('#modal1');
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
                $('#note').val(response.note);
                $('#production_fg_receive_id').empty().append(`
                    <option value="` + response.production_fg_receive_id + `">` + response.production_fg_receive_code + `</option>
                `);

                $('.row_item').remove();
                        
                $('#last-row-item').remove();

                $.each(response.details, function(i, val) {
                    var count = makeid(10);

                    $('#body-item').append(`
                        <tr class="row_item">
                            <input type="hidden" name="arr_prfd_id[]" value="` + val.prfd_id + `">
                            <input type="hidden" name="arr_item_id[]" value="` + val.item_id + `">
                            <input type="hidden" name="arr_area_id[]" value="` + val.area_id + `">
                            <td class="center">
                                <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);" data-id="` + count + `">
                                    <i class="material-icons">delete</i>
                                </a>
                            </td>
                            <td class="center-align">
                                ` + (i+1) + `
                            </td>
                            <td>
                                ` + val.pallet_no + `
                            </td>
                            <td>
                                ` + val.item_code + `
                            </td>
                            <td>
                                ` + val.item_name + `
                            </td>
                            <td>
                                ` + val.shading + `
                            </td>
                            <td class="center">
                                <input name="arr_qty[]" onfocus="emptyThis(this);" type="text" value="` + val.qty + `" style="text-align:right;width:100%;border-bottom: none;" id="arr_qty`+ count +`" readonly data-item="` + val.item_id + `">
                            </td>
                            <td class="center-align">
                                ` + val.unit + `
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
                                    <option value="">--Silahkan pilih item--</option>
                                </select>
                            </td>
                            <td class="center-align">
                                ` + val.area_code + `
                            </td>
                        </tr>
                    `);

                    if(val.list_warehouse.length > 0){
                        $('#arr_warehouse' + count).empty();
                        $.each(val.list_warehouse, function(i, value) {
                            $('#arr_warehouse' + count).append(`
                                <option value="` + value.id + `">` + value.name + `</option>
                            `);
                        });
                    }

                    $('#arr_place' + count).val(val.place_id);
                    $('#arr_warehouse' + count).val(val.warehouse_id);
                });

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

    function formatRupiahNominal(angka){
        let val = angka.value ? angka.value : '';
        var number_string = val.replace(/[^,\d]/g, '').toString(),
        sign = val.charAt(0),
        split   		= number_string.toString().split(','),
        sisa     		= parseFloat(split[0]).toString().length % 3,
        rupiah     		= parseFloat(split[0]).toString().substr(0, sisa),
        ribuan     		= parseFloat(split[0]).toString().substr(sisa).match(/\d{3}/gi);
    
        if(ribuan){
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        if(split[1] != undefined){
            if(split[1].length > 2){
                rupiah = rupiah + ',' + split[1].slice(0,2);
            }else{
                rupiah = rupiah + ',' + split[1];
            }
        }else{
            rupiah = rupiah;
        }
    
        angka.value = sign == '-' ? sign + rupiah : rupiah;
    }
</script>