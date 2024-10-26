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

    #table-detail-item-issue input {
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
                                                <label for="filter_line" style="font-size:1rem;">Line :</label>
                                                <div class="input-field col s12">
                                                    <select class="form-control" id="filter_line" onchange="loadDataTable()">
                                                        @foreach ($line as $row)
                                                            <option value="{{ $row->id }}">{{ $row->code }}</option>
                                                        @endforeach
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
                                                <span class="hide-on-small-onl">{{ __('translations.refresh') }}</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                           
                                            <table id="datatable_serverside" >
                                                <thead>
                                                    <tr>
                                                        <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">#</th>
                                                        <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.code') }}</th>
                                                        <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.user') }}</th>
                                                        <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.company') }}</th>
                                                        <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.post_date') }}</th>
                                                        <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.note') }}</th>
                                                        <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">No.PROD</th>
                                                        <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">No.Jadwal</th>
                                                        <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Item</th>
                                                        <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Shift</th>
                                                        <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Waktu Mulai Produksi</th>
                                                        <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Waktu Selesai Produksi</th>
                                                        <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.line') }}</th>
                                                        <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Group</th>
                                                        <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.plant') }}</th>
                                                        <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.document') }}</th>
                                                        <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.status') }}</th>
                                                        <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.by') }}</th>
                                                        <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.operation') }}</th>
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
    <div class="modal-content" style="overflow:auto !important;">
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
                                    <div class="input-field col m3 s12">
                                        <select class="form-control" id="place_id" name="place_id">
                                            @foreach ($place as $row)
                                                <option value="{{ $row->id }}">{{ $row->code }}</option>
                                            @endforeach
                                        </select>
                                        <label class="" for="place_id">{{ __('translations.plant') }}</label>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <select class="form-control" id="line_id" name="line_id">
                                            @foreach ($line as $row)
                                                <option value="{{ $row->id }}">{{ $row->code }}</option>
                                            @endforeach
                                        </select>
                                        <label class="" for="line_id">{{ __('translations.line') }}</label>
                                    </div>
                                    <div class="input-field col m2 s12">
                                        <select class="browser-default" id="production_order_detail_id" name="production_order_detail_id" onclick="redefinition()" onchange="getProductionOrder();"></select>
                                        <label class="active" for="production_order_detail_id">Daftar Order Produksi</label>
                                    </div>
                                    <div class="input-field col m1 s12 center-align">
                                        <a href="javascript:void(0);" class="btn-floating mb-1 btn-flat waves-effect waves-light pink accent-2 white-text" onclick="getAccountData('1');" id="btn-show"><i class="material-icons right">receipt</i></a>
                                        <label class="active">&nbsp;</label>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <select class="browser-default" id="shift_id" name="shift_id"></select>
                                        <label class="active" for="shift_id">Shift</label>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <input id="group" name="group" type="text" placeholder="Grup">
                                        <label class="active" for="group">Grup</label>
                                    </div>
                                    <div class="input-field col m3 s12 step4">
                                        <input id="post_date" name="post_date" min="{{ $minDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}" onchange="applyStartEndDate();">
                                        <label class="active" for="post_date">Tgl. Post</label>
                                    </div>
                                    <div class="col m12 s12 "></div>
                                    <div class="input-field col m3 s12 step4">
                                        <input id="start_process_time" name="start_process_time" type="datetime-local" placeholder="Tgl. Mulai Produksi">
                                        <label class="active" for="start_process_time">Tgl. Mulai Produksi</label>
                                    </div>
                                    <div class="input-field col m3 s12 step4">
                                        <input id="end_process_time" name="end_process_time" type="datetime-local" placeholder="Tgl. Selesai Produksi">
                                        <label class="active" for="end_process_time">Tgl. Selesai Produksi</label>
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
                                </fieldset>
                            </div>
                        </div>
                        <div class="row mt-3" id="sticky" style="z-index:99 !important;border-radius:30px !important;">
                            <div class="col s12">
                                <fieldset>
                                    <legend>2. Target Produksi</legend>
                                    <div class="col m12">
                                        <div class="row">
                                            <div class="col m4 s12">
                                                Line : <b id="output-line">-</b>
                                            </div>
                                            <div class="col m4 s12">
                                                Target Item SFG/FG : <b id="output-fg">-</b>
                                            </div>
                                            <div class="col m4 s12">
                                                Qty : <b id="output-qty">-</b>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12 step9">
                                <fieldset style="min-width: 100%;">
                                    <legend>3. Detail Item Issue</legend>
                                    <div class="col m12 s12">
                                        <div class="card-alert card gradient-45deg-purple-amber">
                                            <div class="card-content white-text">
                                                <p>Info : Item/Resource akan diambil otomatis dari BOM item Production Order. Anda tetap bisa menambahkan manual item/resource malalui tombol yang disediakan.</p>
                                            </div>
                                        </div>
                                        <div class="card-alert card gradient-45deg-deep-orange-orange">
                                            <div class="card-content white-text">
                                                <p>Info : Nominal item adalah 0. Karena harga didapatkan dari rata-rata cogs item terbaru ketika disimpan.</p>
                                            </div>
                                        </div>
                                        <div class="col s12" style="overflow:auto;min-width:100%;">
                                            <p class="mt-2 mb-2">
                                                <table class="bordered" style="border: 1px solid;width:1500px !important;" id="table-detail-item-issue">
                                                    <thead>
                                                        <tr>
                                                            <th class="center">{{ __('translations.no') }}.</th>
                                                            <th class="center">Item/Resource</th>
                                                            <th class="center">Qty Planned</th>
                                                            <th class="center" width="150px">Persen (%)</th>
                                                            <th class="center">Qty Real</th>
                                                            <th class="center">Satuan Produksi</th>
                                                            <th class="center">Plant</th>
                                                            <th class="center">Gudang</th>
                                                            <th class="center">{{ __('translations.delete') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="body-item-issue">
                                                        <tr id="last-row-item-issue">
                                                            <td class="center-align" colspan="9">
                                                                Silahkan tambahkan Order Produksi untuk memulai...
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th colspan="9">
                                                                <a class="waves-effect waves-light blue btn-small mb-1 mr-1" onclick="addLine('items')" href="javascript:void(0);">
                                                                    <i class="material-icons left">add</i> Tambah Item
                                                                </a>
                                                                {{-- <a class="waves-effect waves-light purple btn-small mb-1 mr-1" onclick="addLine('resources')" href="javascript:void(0);">
                                                                    <i class="material-icons left">add</i> Tambah Resource
                                                                </a> --}}
                                                            </th>
                                                        </tr>
                                                    </tfoot>
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
        <b id="title-modal" style="position:absolute;left:15px;top:15px;">-</b>
        <button class="btn waves-effect waves-light purple btn-panduan mr-1" onclick="startIntro();">Panduan <i class="material-icons right">help_outline</i></button>
        <button class="btn waves-effect waves-light mr-1 submit step10" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Tutup</a>
    </div>
</div>

<div id="modal_edit" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;">
    <div class="modal-content" style="overflow:auto !important;">
        <div class="row">
            <div class="col s12">
                <h4>{{ __('translations.add') }}/{{ __('translations.edit') }} {{ $title }}</h4>
                <form class="row" id="form_data_edit" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="row">
                            <div class="col s12">
                                <fieldset>
                                    <legend>1. {{ __('translations.main_info') }}</legend>
                                    <div class="input-field col m2 s12 step1">
                                        <input type="hidden" id="temp_edit" name="temp_edit">
                                        <input id="code_edit" name="code" type="text" value="{{ $newcode }}" readonly>
                                        <label class="active" for="code_edit">No. Dokumen </label>
                                    </div>
                                    <div class="input-field col m1 s12 step2">
                                        <select class="form-control" id="code_place_id_edit" name="code_place_id" onchange="getCode(this.value);" readonly>
                                            <option value="">--Pilih--</option>
                                            @foreach ($place as $rowplace)
                                                <option value="{{ $rowplace->code }}">{{ $rowplace->code }}</option>
                                            @endforeach
                                        </select>
                                        <label class="active" for="code_place_id_edit">Tempat Kode </label>
                                    </div>
                                    <div class="input-field col m3 s12 step3">
                                        <select class="form-control" id="company_id_edit" name="company_id" readonly>
                                            @foreach ($company as $row)
                                                <option value="{{ $row->id }}">{{ $row->name }}</option>
                                            @endforeach
                                        </select>
                                        <label class="" for="company_id_edit">{{ __('translations.company') }} </label>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <select class="form-control" id="place_id_edit" name="place_id" readonly>
                                            @foreach ($place as $row)
                                                <option value="{{ $row->id }}">{{ $row->code }}</option>
                                            @endforeach
                                        </select>
                                        <label class="" for="place_id_edit">{{ __('translations.plant') }} </label>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <select class="form-control" id="line_id_edit" name="line_id">
                                            @foreach ($line as $row)
                                                <option value="{{ $row->id }}">{{ $row->code }}</option>
                                            @endforeach
                                        </select>
                                        <label class="" for="line_id_edit">{{ __('translations.line') }} </label>
                                    </div>
                                    <div class="input-field col m2 s12">
                                        <select class="browser-default" id="production_order_detail_id_edit" name="production_order_detail_id" onclick="redefinition()" onchange="getProductionOrder();" readonly></select>
                                        <label class="active" for="production_order_detail_id_edit">Daftar Order Produksi </label>
                                    </div>
    
                                    <div class="input-field col m3 s12">
                                        
                                        <select class="browser-default" id="shift_id_edit" name="shift_id_edit"></select>
                                        <label class="active" for="shift_id_edit">Shift</label>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <input id="group_edit" name="group_edit" type="text" placeholder="Grup">
                                        <label class="active" for="group_edit">Grup</label>
                                    </div>

                                    <div class="input-field col m3 s12 step4">
                                        <input id="post_date_edit" name="post_date" min="{{ $minDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}" onchange="applyStartEndDate();" readonly>
                                        <label class="active" for="post_date_edit">Tgl. Post </label>
                                    </div>
                                    <div class="col m12 s12 "></div>
                                    {{-- <div class="input-field col m3 s12 step4">
                                        <input id="start_process_time_edit" name="start_process_time" type="datetime-local" placeholder="Tgl. Mulai Produksi">
                                        <label class="active" for="start_process_time_edit">Tgl. Mulai Produksi </label>
                                    </div>
                                    <div class="input-field col m3 s12 step4">
                                        <input id="end_process_time_edit" name="end_process_time" type="datetime-local" placeholder="Tgl. Selesai Produksi">
                                        <label class="active" for="end_process_time_edit">Tgl. Selesai Produksi </label>
                                    </div> --}}
                                  
                                    <div class="input-field col m3 s12">
                                        <textarea class="materialize-textarea" id="note_edit" name="note_edit" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                        <label class="active" for="note_edit">{{ __('translations.note') }}</label>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <div class="row mt-3" id="sticky" style="z-index:99 !important;border-radius:30px !important;">
                            <div class="col s12">
                                <fieldset>
                                    <legend>2. Target Produksi</legend>
                                    <div class="col m12">
                                        <div class="row">
                                            <div class="col m4 s12">
                                                Line : <b id="output-line_edit">-</b>
                                            </div>
                                            <div class="col m4 s12">
                                                Target Item SFG/FG : <b id="output-fg_edit">-</b>
                                            </div>
                                            <div class="col m4 s12">
                                                Qty : <b id="output-qty_edit">-</b>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col s12 step9">
                            <fieldset style="min-width: 100%;">
                                <legend>3. Detail Item Issue</legend>
                                <div class="col m12 s12">
                                    <div class="card-alert card gradient-45deg-purple-amber">
                                        <div class="card-content white-text">
                                            <p>Info : Item/Resource akan diambil otomatis dari BOM item Production Order. Anda tetap bisa menambahkan manual item/resource malalui tombol yang disediakan.</p>
                                        </div>
                                    </div>
                                    <div class="card-alert card gradient-45deg-deep-orange-orange">
                                        <div class="card-content white-text">
                                            <p>Info : Nominal item adalah 0. Karena harga didapatkan dari rata-rata cogs item terbaru ketika disimpan.</p>
                                        </div>
                                    </div>
                                    <div class="col s12" style="overflow:auto;min-width:100%;">
                                        <p class="mt-2 mb-2">
                                            <table class="bordered" style="border: 1px solid;width:1500px !important;" id="table-detail-item-issue_edit">
                                                <thead>
                                                    <tr>
                                                        <th class="center">{{ __('translations.no') }}.</th>
                                                        <th class="center">Item/Resource</th>
                                                        <th class="center">Qty Planned</th>
                                                        <th class="center" width="150px">Persen (%)</th>
                                                        <th class="center">Qty Real</th>
                                                        <th class="center">Satuan Produksi</th>
                                                        <th class="center">Plant</th>
                                                        <th class="center">Gudang</th>
                                                        <th class="center">{{ __('translations.delete') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="body-item-issue_edit">
                                                    <tr id="last-row-item-issue_edit">
                                                        
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="9">
                                                            
                                                        </th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </p>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <b id="title-modal" style="position:absolute;left:15px;top:15px;">-</b>
        <button class="btn waves-effect waves-light mr-1 submit step10" onclick="saveEdit();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
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

<div id="modal_pdo" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h5>Daftar Order Produksi <b id="account_name"></b></h5>
                <div class="row">
                    <div class="col s12 mt-2">
                        <ul class="collapsible">
                            <li class="active">
                                <div class="collapsible-header purple lightrn-1 white-text">
                                    <i class="material-icons">layers</i>List Production Order
                                </div>
                                <div class="collapsible-body">
                                    <div id="datatable_buttons_multi"></div>
                                    <i class="right">Pilih salah satu item untuk digunakan di Production Issue.</i>
                                    <table id="table_pdo" class="display" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">No. Production Order</th>
                                                <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Pengguna</th>
                                                <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Tanggal Post</th>
                                                <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Target Item</th>
                                                <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Catatan</th>
                                                <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-detail-pdo"></tbody>
                                    </table>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat mr-1">{{ __('translations.close') }}</a>
        <button class="btn waves-effect waves-light purple right submit" onclick="applyDocuments('main');">Gunakan <i class="material-icons right">forward</i></button>
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

        $('#modal_pdo').modal({
            onOpenStart: function(modal,trigger) {
                $('.collapsible').collapsible({
                    accordion:false
                });
            },
            onOpenEnd: function(modal, trigger) {
                table_multi = $('#table_pdo').DataTable({
                    "responsive": true,
                    scrollY: '50vh',
                    scrollCollapse: true,
                    "iDisplayInLength": 10,
                    "order": [[0, 'desc']],
                    dom: 'Blfrtip',
                    buttons: [
                        'selectAll',
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
                        },
                        "select": {
                        }
                    },
                    select: {
                        style: 'single'
                    }
                });
                
                $('#table_pdo_wrapper > .dt-buttons').appendTo('#datatable_buttons_multi');
                $('select[name="table_pdo_length"]').addClass('browser-default');
            },
            onCloseEnd: function(modal, trigger){
                $('#body-detail-pdo').empty();
                
                $('#table_pdo').DataTable().clear().destroy();
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
                applyStartEndDate();
            },
            onOpenEnd: function(modal, trigger) {
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
                window.onbeforeunload = function() {
                    return 'You will lose all changes made since your last save';
                };
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                M.updateTextFields();
                $('input').css('border', 'none');
                $('input').css('border-bottom', '0.5px solid black');
                window.onbeforeunload = function() {
                    return null;
                };
                $('#output-line,#output-fg,#output-qty').text('-');
                $('#production_order_detail_id,#shift_id').empty();
                $('#body-item-issue').empty().append(`
                    <tr id="last-row-item-issue">
                        <td class="center-align" colspan="9">
                            Silahkan tambahkan Order Produksi untuk memulai...
                        </td>
                    </tr>
                `);
                $('#body-item-receive').empty().append(`
                    <tr id="last-row-item-receive">
                        <td colspan="11">
                            Silahkan tambahkan Order Produksi untuk memulai...
                        </td>
                    </tr>
                `);
                $('#title-modal').text('-');
            }
        });

        $('#modal_edit').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                $('#post_date').attr('min','{{ $minDate }}');
                $('#post_date').attr('max','{{ $maxDate }}');
                applyStartEndDate();
            },
            onOpenEnd: function(modal, trigger) {
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
                window.onbeforeunload = function() {
                    return 'You will lose all changes made since your last save';
                };
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data_edit')[0].reset();
                $('#shift_id_edit').empty();
                $('#group_edit').val('');
                $('#code_edit').empty();
                $('#temp_edit').val('');


                $('#body-item-issue_edit').empty().append(`
                    <tr id="last-row-item-issue">
                        <td class="center-align" colspan="9">
                            Silahkan tambahkan Order Produksi untuk memulai...
                        </td>
                    </tr>
                `);
                $('#body-item-receive_edit').empty().append(`
                    <tr id="last-row-item-receive">
                        <td colspan="11">
                            Silahkan tambahkan Order Produksi untuk memulai...
                        </td>
                    </tr>
                `);
                M.updateTextFields();
         
            }
        });

        select2ServerSide('#production_order_detail_id', '{{ url("admin/select2/production_order_detail") }}');

        select2ServerSide('#shift_id', '{{ url("admin/select2/shift_production") }}');
        select2ServerSide('#shift_id_edit', '{{ url("admin/select2/shift_production") }}');
        $('#body-item-issue').on('click', '.delete-data-item-issue', function() {
            let id = $(this).data('id');
            $('.row_item_batch[data-code="' + id + '"]').remove();
            $(this).closest('tr').remove();
        });
    });

    function setInitialValue(initialId) {

        $.ajax({
            url: '{{ url("admin/select2/production_order_detail") }}',
            type: 'GET',
            dataType: 'JSON',
            data: {
                id: initialId
            },
            success: function(data) {
                if (data.items[0] && data.items[0].id) {
                    let newOption = new Option(data.items[0].text, data.items[0].id, true, true);
                    $('#production_order_detail_id').append(newOption);
                    getProductionOrder(data.items[0]);
                }
            }
        });
    }

    function applyDocuments(type){
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
                let passed = true, arr_id = [], arr_type = [], sametype = true;

                if(type == 'main'){
                    $.map(table_multi.rows('.selected').nodes(), function (item) {
                        arr_id.push($(item).data('id'));
                        setInitialValue($(item).data('id'));
                    });
                }
                $('#modal_pdo').modal('close');
            }
        });
    }

    function getAccountData(kind){
        $.ajax({
            url: '{{ Request::url() }}/get_account_data',
            type: 'POST',
            dataType: 'JSON',
            data: {
                id: $('#account_id').val()
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            success: function(response) {
                loadingClose('.modal-content');

                if(kind == '1'){
                    $('#modal_pdo').modal('open');
                
                    if(response.details.length > 0){
                        $.each(response.details, function(i, val) {
                            $('#body-detail-pdo').append(`
                                <tr data-id="` + val.id + `">
                                    <td>
                                        ` + val.code + `
                                    </td>
                                    <td>
                                        ` + val.user + `
                                    </td>
                                    <td class="center">
                                        ` + val.post_date + `
                                    </td>
                                    <td class="center">
                                        ` + val.item_receive_name + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.note1 + `
                                    </td>
                                    <td class="center">
                                        ` + val.status + `
                                    </td>
                                </tr>
                            `);
                        });
                    }
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
    }

    function applyStartEndDate(){
        let date = $('#post_date').val();
        $('#start_process_time,#end_process_time').val(date + 'T00:00');
    }

    function addLine(type){
        if($('#production_order_detail_id').val()){
            let no_issue = $('.row_item_issue').length + 1;
            var count = makeid(10);
            $('#body-item-issue').append(`
                <tr class="row_item_issue" data-id="">
                    <input type="hidden" name="arr_type[]" value="1">
                    <input type="hidden" name="arr_lookable_type[]" value="` + type + `">
                    <input type="hidden" name="arr_production_order_detail_id[]" value="` + $('#production_order_detail_id').val() + `">
                    <input type="hidden" name="arr_bom_id[]" value="0">
                    <input type="hidden" name="arr_bom_detail_id[]" value="0">
                    <input type="hidden" name="arr_qty_bom[]" value="0,000">
                    <input type="hidden" name="arr_nominal_bom[]" value="0,00">
                    <input type="hidden" name="arr_total_bom[]" value="0,00">
                    <td class="center-align">
                        ` + no_issue + `
                    </td>
                    <td>
                        <select class="browser-default" id="arr_lookable_id` + count + `" name="arr_lookable_id[]" onchange="getRowUnit('` + count + `','` + type +`')"></select>
                    </td>
                    <td class="right-align arr-planned">
                        0,000
                    </td>
                    <td class="center">
                        <input name="arr_percentage[]" class="browser-default" type="text" value="0" onkeyup="formatRupiahNoMinus(this);" style="text-align:right;width:100%;" id="rowPercent`+ count +`" readonly>
                    </td>
                    <td class="center">
                        <input name="arr_qty[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0,000" onkeyup="formatRupiahNoMinus(this);convertQtyToPercent();" style="text-align:right;width:100%;" id="rowQty`+ count +`" required data-id="` + count + `">
                    </td>
                    <td class="center" id="arr_unit` + count + `">
                        -
                    </td>
                    <td class="center">
                        <select class="browser-default" id="arr_place` + count + `" name="arr_place[]">
                            @foreach ($place as $rowplace)
                                <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="center">
                        <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">
                            <option value="">--Silahkan pilih item--</option>
                        </select>
                    </td>
                    <td class="center">
                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item-issue" href="javascript:void(0);" data-id="` + count + `">
                            <i class="material-icons">delete</i>
                        </a>
                    </td>
                </tr>
            `);

            if(type == 'items'){
                select2ServerSideLonger('#arr_lookable_id' + count, '{{ url("admin/select2/item_for_production_issue") }}');
            }else if(type == 'resources'){
                select2ServerSide('#arr_lookable_id' + count, '{{ url("admin/select2/resource") }}');
            }
        }else{
            swal({
                title: 'Ups!',
                text: 'Silahkan pilih Production Order terlebih dahulu.',
                icon: 'error'
            });
        }
    }

    function getRowUnit(val,type){
        $("#arr_unit" + val).empty();
        $("#arr_stock" + val).empty();
        $('#arr_warehouse' + val).empty().append(`
            <option value="">--Silahkan pilih item--</option>
        `);
        $('.row_item_batch[data-code="' + val + '"]').remove();
        if($("#arr_lookable_id" + val).val()){
            $("#arr_unit" + val).text($("#arr_lookable_id" + val).select2('data')[0].uom);
            if(type == 'items'){
                let optionStock = '<select class="browser-default" id="arr_item_stock_id' + val + '" name="arr_item_stock_id[]" required>';
                if($("#arr_lookable_id" + val).select2('data')[0].stock_list.length > 0){
                    $.each($("#arr_lookable_id" + val).select2('data')[0].stock_list, function(i, value) {
                        optionStock += '<option value="' + value.id + '" data-qty="' + value.qty_raw + '">' + value.name + ' ' + value.shading + ' ' + value.qty + '</option>';
                    });
                }else{
                    optionStock += '<option value="0" data-qty="0,000">--Maaf, item ini tidak memiliki stock--</option>';
                }
                optionStock += '</select>';
                $("#arr_stock" + val).html(optionStock);
                if($('select#arr_warehouse' + val).length > 0){
                    $('#arr_warehouse' + val).empty();
                    $.each($("#arr_lookable_id" + val).select2('data')[0].list_warehouse, function(i, value) {
                        $('#arr_warehouse' + val).append(`
                            <option value="` + value.id + `">` + value.name + `</option>
                        `);
                    });
                }
                if($("#arr_lookable_id" + val).select2('data')[0].has_batch){
                    $('#arr_lookable_id' + val).parent().prev().attr('rowspan','2');
                    $('#arr_lookable_id' + val).parent().parent().parent().append(`
                        <tr class="row_item_batch gradient-45deg-yellow-green" data-id="` + $('#production_order_detail_id').val() + `" data-code="` + val + `">
                            <td colspan="2" class="right-align">
                                Ambil dari Batch : 
                            </td>
                            <td colspan="6">
                                <div class="row">
                                    <div class="input-field col m3 s12">
                                        <select class="browser-default" id="arr_batch` + val + `" name="arr_batch[]" data-id="` + val + `"></select>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <a href="javascript:void(0);" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text" onclick="addBatch('` + val + `');" id="btn-show"><i class="material-icons right">add_circle_outline</i></a>
                                        <label class="active">&nbsp;</label>
                                    </div>
                                    <div class="col m12 s12">
                                        <table class="bordered" style="width:500px !important;">
                                            <thead>
                                                <tr>
                                                    <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">No.Batch</th>
                                                    <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Qty Dipakai</th>
                                                    <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.delete') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="table-batch` + val + `"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `);
                    $('#arr_batch' + val).select2({
                        placeholder: '-- Kosong --',
                        minimumInputLength: 1,
                        allowClear: true,
                        cache: true,
                        width: 'resolve',
                        dropdownParent: $('body').parent(),
                        ajax: {
                            url: '{{ url("admin/select2/production_batch") }}',
                            type: 'GET',
                            dataType: 'JSON',
                            data: function(params) {
                                return {
                                    search: params.term,
                                    item_id: $("#arr_lookable_id" + val).val(),
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
                    $('#arr_lookable_id' + val).parent().prev().removeAttr('rowspan');
                }
            }
            if(type == 'resources'){
                $("#arr_stock" + val).html(`
                    <select class="browser-default" id="arr_item_stock_id` + val + `" name="arr_item_stock_id[]">
                        <option value="0" data-qty="0,000">--Maaf, item ini tidak memiliki stock--</option>
                    </select>
                `);
            }
        }else{
            $("#arr_unit" + val).text('-');
            $("#arr_stock" + val).text('-');
            $('#arr_lookable_id' + val).parent().prev().removeAttr('rowspan');
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

    function getProductionOrder(data){
        data = data || '';
        $('#body-item-issue').empty().append(`
            <tr id="last-row-item-issue">
                <td class="center-align" colspan="9">
                    Silahkan tambahkan Order Produksi untuk memulai...
                </td>
            </tr>
        `);
        $('#body-item-receive').empty().append(`
            <tr id="last-row-item-receive">
                <td colspan="11">
                    Silahkan tambahkan Order Produksi untuk memulai...
                </td>
            </tr>
        `);
        
        $('#note').val('');
        
        if($('#production_order_detail_id').val()){
           
            let datakuy = data !== '' ? data : $('#production_order_detail_id').select2('data')[0];
            $('#note').val(datakuy.note);
            $('#title-modal').text(datakuy.bom_group);

            $('#last-row-item-issue,#last-row-item-receive').remove();

            var count = makeid(10);

            if(datakuy.bom_detail.length > 0){
                let no_issue = $('.row_item_issue').length + 1;

                $.each(datakuy.bom_detail, function(i, val) {
                    var count = makeid(10);

                    let warehouse = `<select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">`;
                    if(val.list_warehouse.length > 0){
                        $.each(val.list_warehouse, function(i, valkuy) {
                            warehouse += `<option value="` + valkuy.id + `">` + valkuy.name + `</option>`;
                        });
                    }else{
                        warehouse += `<option value="0" data-qty="0,000">--Maaf, item ini tidak memiliki stock--</option>`;
                    }
                    warehouse += `</select>`;

                    $('#body-item-issue').append(`
                        <tr class="row_item_issue" data-id="` + $('#production_order_detail_id').val() + `">
                            <input type="hidden" name="arr_lookable_type[]" value="` + val.lookable_type + `">
                            <input type="hidden" name="arr_lookable_id[]" value="` + val.lookable_id + `" data-id="` + count + `">
                            <input type="hidden" name="arr_production_order_detail_id[]" value="` + datakuy.id + `">
                            <input type="hidden" name="arr_bom_id[]" value="` + datakuy.bom_id + `">
                            <input type="hidden" name="arr_bom_detail_id[]" value="` + val.bom_detail_id + `">
                            <input type="hidden" name="arr_qty_bom[]" value="` + val.qty_bom + `">
                            <input type="hidden" name="arr_nominal_bom[]" value="` + val.nominal_bom + `">
                            <input type="hidden" name="arr_total_bom[]" value="` + val.total_bom + `">
                            <td class="center-align" ` + (val.has_batch ? `rowspan="2"` : ``) + `>
                                ` + no_issue + `
                            </td>
                            <td>
                                ` + val.name + `
                            </td>
                            <td class="right-align arr-planned" id="rowPlanned` + count + `">
                                ` + val.qty_planned + `
                            </td>
                            <td class="center">
                                <input name="arr_percentage[]" class="browser-default" type="text" value="0" onkeyup="formatRupiahNoMinus(this);" style="text-align:right;width:100%;" id="rowPercent`+ count +`" readonly>
                            </td>
                            <td class="center">
                                <input name="arr_qty[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.qty_planned + `" onkeyup="formatRupiahNoMinus(this);convertQtyToPercent();" style="text-align:right;width:100%;" id="rowQty`+ count +`" required data-id="` + count + `">
                            </td>
                            <td class="center" id="arr_unit` + count + `">
                                ` + val.unit + `
                            </td>
                            <td class="center">
                                <select class="browser-default" id="arr_place` + count + `" name="arr_place[]">
                                    @foreach ($place as $rowplace)
                                        <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="center">
                                ` + warehouse + `
                            </td>
                            <td class="center">
                                <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item-issue" href="javascript:void(0);" data-id="` + count + `">
                                    <i class="material-icons">delete</i>
                                </a>
                            </td>
                        </tr>
                    ` +
                        (val.has_batch ? `<tr class="row_item_batch gradient-45deg-yellow-green" data-id="` + $('#production_order_detail_id').val() + `" data-code="` + count + `">
                            <td colspan="2" class="right-align">
                                Ambil dari Batch : 
                            </td>
                            <td colspan="6">
                                <div class="row">
                                    <div class="input-field col m3 s12">
                                        <select class="browser-default" id="arr_batch` + count + `" name="arr_batch[]" data-id="` + count + `"></select>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <a href="javascript:void(0);" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text" onclick="addBatch('` + count + `');" id="btn-show"><i class="material-icons right">add_circle_outline</i></a>
                                        <label class="active">&nbsp;</label>
                                    </div>
                                    <div class="col m12 s12">
                                        <table class="bordered" style="width:500px !important;">
                                            <thead>
                                                <tr>
                                                    <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">No.Batch</th>
                                                    <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Qty Dipakai</th>
                                                    <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.delete') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="table-batch` + count + `"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>` : ``)
                    );
                    $('#rowQty' + count).trigger('keyup');
                    if(val.has_batch){
                        $('#arr_batch' + count).select2({
                            placeholder: '-- Kosong --',
                            minimumInputLength: 1,
                            allowClear: true,
                            cache: true,
                            width: 'resolve',
                            dropdownParent: $('body').parent(),
                            ajax: {
                                url: '{{ url("admin/select2/production_batch") }}',
                                type: 'GET',
                                dataType: 'JSON',
                                data: function(params) {
                                    return {
                                        search: params.term,
                                        item_id: val.lookable_id,
                                        place_id: $('#arr_place' + count).val(),
                                        warehouse_id: $('#arr_warehouse' + count).val(),
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
                    no_issue++;
                });
                $('#output-line').empty().text(datakuy.line);
                $('#output-fg').empty().text(datakuy.item_receive_code + ' - ' + datakuy.item_receive_name);
                $('#output-qty').empty().text(datakuy.item_receive_qty + ' - ' + datakuy.item_receive_unit_uom);
                M.updateTextFields();
            }else{
                M.toast({
                    html: 'Mohon maaf, PDO terpilih tidak memiliki bom material MANUAL.'
                });
            }
        }else{
            $('#title-modal').text('-');
            $('#output-line,#output-fg,#output-qty').text('-');
        }
    }

    function removeBatch(element,code){
        $(element).parent().parent().remove();
        let arr_batch_id = [];

        $('input[name^="arr_batch_id[]"]').each(function(index){
            arr_batch_id.push($(this).val());
        });

        $('#arr_batch' + code).select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/production_batch") }}',
                type: 'GET',
                dataType: 'JSON',
                data: function(params) {
                    return {
                        search: params.term,
                        item_id: $('input[name^="arr_lookable_id[]"][data-id="' + code + '"]').val(),
                        arr_batch_id: arr_batch_id,
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

    function addBatch(code){
        if($('#arr_batch' + code).val()){
            let data = $('#arr_batch' + code).select2('data')[0];
            let count = makeid(10);
            $('#table-batch' + code).append(`
                <tr>
                    <input type="hidden" name="arr_batch_id[]" id="arr_batch_id` + count + `" value="` + data.id + `">
                    <td>` + data.code + `</td>
                    <td>
                        <input name="arr_qty_batch[]" class="qty-batch-` + code + `" type="text" value="` + data.qty + `" onkeyup="formatRupiahNoMinus(this);checkQtyBatch('` + count + `')" data-qty="` + data.qty + `" data-id="` + count + `" class="" id="rowBatch`+ count +`" style="text-align:right;">    
                    </td>
                    <td>
                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1" href="javascript:void(0);" onclick="removeBatch(this,'` + code + `');">
                            <i class="material-icons">delete</i>
                        </a> 
                    </td>
                </tr>
            `);
            $('#arr_batch' + code).empty();

            let arr_batch_id = [];

            $('input[name^="arr_batch_id[]"]').each(function(index){
                arr_batch_id.push($(this).val());
            });

            $('#arr_batch' + code).select2({
                placeholder: '-- Kosong --',
                minimumInputLength: 1,
                allowClear: true,
                cache: true,
                width: 'resolve',
                dropdownParent: $('body').parent(),
                ajax: {
                    url: '{{ url("admin/select2/production_batch") }}',
                    type: 'GET',
                    dataType: 'JSON',
                    data: function(params) {
                        return {
                            search: params.term,
                            item_id: $('input[name^="arr_lookable_id[]"][data-id="' + code + '"]').val(),
                            arr_batch_id: arr_batch_id,
                            place_id: $('#arr_place' + code).val(),
                            warehouse_id: $('#arr_warehouse' + code).val(),
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
            
        }
    }

    function convertQtyToPercent(){
        let total = 0;
        $('input[name="arr_qty[]"]').each(function(index){
            total += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
        });
        let totalpercent = 100;
        $('input[name="arr_qty[]"]').each(function(index){
            let val = parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
            let percent = Math.round((((val / total) * 100) + Number.EPSILON) * 100) / 100;
            if(totalpercent > percent){
                percent = percent;
                totalpercent -= percent;
            }else{
                percent = Math.round((totalpercent + Number.EPSILON) * 100) / 100;
            }
            $('input[name="arr_percentage[]"]').eq(index).val(
                (percent >= 0 ? '' : '-') + formatRupiahIni(percent.toString().replace('.',','))
            );
        });
    }

    function checkQtyBatch(code){
        let qtyMax = parseFloat($('#rowBatch' + code).data('qty').replaceAll(".", "").replaceAll(",","."));
        let qty = parseFloat($('#rowBatch' + code).val().replaceAll(".", "").replaceAll(",","."));
        if(qty > qtyMax){
            $('#rowBatch' + code).val(
                (qtyMax >= 0 ? '' : '-') + formatRupiahIni(qtyMax.toFixed(3).toString().replace('.',','))
            );
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
                    line : $('#filter_line').val(),
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
                { name: 'production_order_detail_id', searchable: false, orderable: false, className: 'center-align' },
                { name: 'production_schedule_id', searchable: false, orderable: false, className: 'center-align' },
                { name: 'item_id', searchable: false, orderable: false, className: 'center-align' },
                { name: 'shift', searchable: false, orderable: false, className: 'center-align' },
                { name: 'start_process_time', searchable: false, orderable: false, className: 'center-align' },
                { name: 'end_process_time', searchable: false, orderable: false, className: 'center-align' },
                { name: 'line', searchable: false, orderable: false, className: 'center-align' },
                { name: 'group', searchable: false, orderable: false, className: 'center-align' },
                { name: 'plant_id', searchable: false, orderable: false, className: 'center-align' },
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

    function saveEdit(){
        var formData = new FormData($('#form_data_edit')[0]);
        $.ajax({
            url: '{{ Request::url() }}/save_edit',
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
                loadingOpen('#modal_edit');
            },
            success: function(response) {
                loadingClose('#modal_edit');
                $('input').css('border', 'none');
                $('input').css('border-bottom', '0.5px solid black');
                if(response.status == 200) {
                    loadDataTable();
                    $('#modal_edit').modal('close');
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
                formData.delete("arr_batch_id[]");
                formData.delete("arr_qty_batch[]");

                let passedInput = true, passedStock = true;

                $('*[name^="arr_batch[]"]').each(function(index){
                    let id = $(this).data('id');
                    if($('.qty-batch-' + id).length == 0){
                        passedInput = false;
                    }
                    let qtyNeeded = parseFloat($('#rowQty' + id).val().replaceAll(".", "").replaceAll(",","."));
                    let qtyBatch = 0;
                    $('.qty-batch-' + id).each(function(index){
                        qtyBatch += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
                    });
                    if(qtyNeeded.toFixed(3) !== qtyBatch.toFixed(3)){
                        passedStock = false;
                    }
                });
                
                $('*[name^="arr_qty[]"]').each(function(index){
                    let id = $(this).data('id');
                    if($('.qty-batch-' + id).length > 0){
                        $('.qty-batch-' + id).each(function(){
                            let idkuy = $(this).data('id');
                            formData.append('arr_batch_index[]',index);
                            formData.append('arr_batch_id[]',$('#arr_batch_id' + idkuy).val());
                            formData.append('arr_qty_batch[]',$(this).val());
                        });
                    }
                });

                $('*[name^="arr_qty[]"]').each(function(index){
                    let val = $(this).val();
                    if($('*[name^="arr_lookable_type[]"]').eq(index).val() == 'items' && $('*[name^="arr_type[]"]').eq(index).val() == '1'){
                        if(val == '0'){
                            passedStock = false;
                        }
                    }
                });

                if(!passedStock){
                    swal({
                        title: 'Ups! Maaf.',
                        text: 'Terdapat stok Item ISSUE yang tidak ditemukan datanya. Terdapat qty batch yang tidak sesuai dengan qty real pemakaian.',
                        icon: 'error'
                    });
                    return;
                }

                if(!passedInput){
                    swal({
                        title: 'Ups! Maaf.',
                        text: 'Batch untuk item WIP tidak boleh kosong.',
                        icon: 'error'
                    });
                }else{
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
            }
        });
    }

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
    }
    function redefinition(){
        select2ServerSide('#production_order_detail_id', '{{ url("admin/select2/production_order_detail") }}');
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
                $('#place_id').val(response.place_id).formSelect();
                $('#line_id').val(response.line_id).formSelect();
                $('#shift_id').empty().append(`
                    <option value="` + response.shift_id + `">` + response.shift_name + `</option>
                `);
                $('#group').val(response.group);
                $('#company_id').val(response.company_id).formSelect();
                $('#start_process_time').val(response.start_process_time);
                $('#end_process_time').val(response.end_process_time);
                $('#note').val(response.note);
                $('#production_order_detail_id').empty().append(`
                    <option value="` + response.production_order_detail_id + `">` + response.production_order_detail_code + `</option>
                `);

                $('#output-line').empty().text(response.line_code);
                $('#output-fg').empty().text(response.target_item);
                $('#output-qty').empty().text(response.target_qty);

                $('#title-modal').text(response.bom_group);

                $('.row_item_issue').remove();
                        
                $('#last-row-item-issue').remove();

                let no_issue = $('.row_item_issue').length + 1;

                $.each(response.detail_issue, function(i, val) {
                    var count = makeid(10);
                    let warehouse = `<select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]">`;
                    if(val.list_warehouse.length > 0){
                        $.each(val.list_warehouse, function(i, valkuy) {
                            warehouse += `<option value="` + valkuy.id + `" ` + (valkuy.id == val.warehouse_id ? 'selected' : '') + `>` + valkuy.name + `</option>`;
                        });
                    }else{
                        warehouse += `<option value="0" data-qty="0,000">--Maaf, item ini tidak memiliki stock--</option>`;
                    }
                    warehouse += `</select>`;
                    $('#body-item-issue').append(`
                        <tr class="row_item_issue" data-id="` + val.id + `">
                            <input type="hidden" name="arr_lookable_type[]" value="` + val.lookable_type + `">
                            <input type="hidden" name="arr_lookable_id[]" value="` + val.lookable_id + `" data-id="` + count + `">
                            <input type="hidden" name="arr_production_order_detail_id[]" value="` + val.id + `">
                            <input type="hidden" name="arr_bom_id[]" value="` + (val.bom_id ? val.bom_id : '0' ) + `">
                            <input type="hidden" name="arr_bom_detail_id[]" value="` + (val.bom_detail_id ? val.bom_detail_id : '0' ) + `">
                            <input type="hidden" name="arr_qty_bom[]" value="` + (val.bom_id ? val.qty_bom : '0,000') + `">
                            <input type="hidden" name="arr_nominal_bom[]" value="` + (val.bom_id ? val.nominal_bom : '0,00') + `">
                            <input type="hidden" name="arr_total_bom[]" value="` + (val.bom_id ? val.total_bom : '0,00') + `">
                            <td class="center-align" ` + (val.has_batch ? `rowspan="2"` : ``) + `>
                                ` + no_issue + `
                            </td>
                            <td>
                                ` + val.lookable_code + ` - ` + val.lookable_name + `
                            </td>
                            <td class="right-align" id="rowPlanned` + count + `">
                                ` + val.qty_planned + `
                            </td>
                            <td class="center">
                                <input name="arr_percentage[]" class="browser-default" type="text" value="0" onkeyup="formatRupiahNoMinus(this);" style="text-align:right;width:100%;" id="rowPercent`+ count +`" readonly>
                            </td>
                            <td class="center">
                                <input name="arr_qty[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinus(this);convertQtyToPercent()" style="text-align:right;width:100%;" id="rowQty`+ count +`" data-id="` + count + `" required data-id="` + count + `">
                            </td>
                            <td class="center" id="arr_unit` + count + `">
                                ` + val.lookable_unit + `
                            </td>
                            <td class="center">
                                <select class="browser-default" id="arr_place` + count + `" name="arr_place[]">
                                    @foreach ($place as $rowplace)
                                        <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="center" id="arr_stock` + count + `">
                                ` + warehouse + `
                            </td>
                            <td class="center">
                                <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item-issue" href="javascript:void(0);">
                                    <i class="material-icons">delete</i>
                                </a>
                            </td>
                        </tr>
                    ` +
                        (val.has_batch ? `<tr class="row_item_batch gradient-45deg-yellow-green" data-id="` + $('#production_order_detail_id').val() + `" data-code="` + count + `">
                            <td colspan="2" class="right-align">
                                Ambil dari Batch : 
                            </td>
                            <td colspan="6">
                                <div class="row">
                                    <div class="input-field col m3 s12">
                                        <select class="browser-default" id="arr_batch` + count + `" name="arr_batch[]" data-id="` + count + `"></select>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <a href="javascript:void(0);" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text" onclick="addBatch('` + count + `');" id="btn-show"><i class="material-icons right">add_circle_outline</i></a>
                                        <label class="active">&nbsp;</label>
                                    </div>
                                    <div class="col m12 s12">
                                        <table class="bordered" style="width:500px !important;">
                                            <thead>
                                                <tr>
                                                    <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">No.Batch</th>
                                                    <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Qty Dipakai</th>
                                                    <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.delete') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="table-batch` + count + `"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>` : ``)
                    );
                    $('#rowQty' + count).trigger('keyup');
                    if(val.has_batch){

                        let arr_batch_id = [];

                        $.each(val.list_batch, function(i, value) {
                            arr_batch_id.push(value.production_batch_id);
                        });

                        $('#arr_batch' + count).select2({
                            placeholder: '-- Kosong --',
                            minimumInputLength: 1,
                            allowClear: true,
                            cache: true,
                            width: 'resolve',
                            dropdownParent: $('body').parent(),
                            ajax: {
                                url: '{{ url("admin/select2/production_batch") }}',
                                type: 'GET',
                                dataType: 'JSON',
                                data: function(params) {
                                    return {
                                        search: params.term,
                                        item_id: val.lookable_id,
                                        arr_batch_id: arr_batch_id,
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
                    $.each(val.list_batch, function(i, value) {
                        let countdetail = makeid(10);
                        $('#table-batch' + count).append(`
                            <tr>
                                <input type="hidden" name="arr_batch_id[]" id="arr_batch_id` + countdetail + `" value="` + value.production_batch_id + `">
                                <td>` + value.production_batch_code + `</td>
                                <td>
                                    <input name="arr_qty_batch[]" class="qty-batch-` + count + `" type="text" value="` + value.qty + `" onkeyup="formatRupiahNoMinus(this);checkQtyBatch('` + countdetail + `')" data-qty="` + value.max_qty + `" data-id="` + countdetail + `" class="" id="rowBatch`+ countdetail +`" style="text-align:right;">    
                                </td>
                                <td>
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1" href="javascript:void(0);" onclick="removeBatch(this,'` + count + `');">
                                        <i class="material-icons">delete</i>
                                    </a> 
                                </td>
                            </tr>
                        `);
                    });
                    $('#arr_place' + count).val(val.place_id);
                    no_issue++;
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

    function showEdit(id){

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
                $('#modal_edit').modal('open');
                $('#temp_edit').val(id);
                $('#code_edit').append(response.code);
                $('#shift_id_edit').empty().append(`
                    <option value="` + response.shift_id + `">` + response.shift_name + `</option>
                `);
                $('#group_edit').val(response.group);
                $('#note_edit').val(response.note);

                $('#code_place_id_edit').val(response.code_place_id).formSelect();
                $('#code_edit').val(response.code);
                $('#post_date_edit').val(response.post_date);
                $('#place_id_edit').val(response.place_id).formSelect();
                $('#line_id_edit').val(response.line_id).formSelect();
                
                $('#company_id_edit').val(response.company_id).formSelect();
                $('#start_process_time_edit').val(response.start_process_time);
                $('#end_process_time_edit').val(response.end_process_time);
                $('#note_edit').val(response.note);
                $('#production_order_detail_id_edit').empty().append(`
                    <option value="` + response.production_order_detail_id + `">` + response.production_order_detail_code + `</option>
                `);

                $('#output-line_edit').empty().text(response.line_code);
                $('#output-fg_edit').empty().text(response.target_item);
                $('#output-qty_edit').empty().text(response.target_qty);

                $('#title-modal_edit').text(response.bom_group);

                $('.row_item_issue_edit').remove();
                $('#last-row-item-issue_edit').remove();

                let no_issue = $('.row_item_issue_edit').length + 1;

                $.each(response.detail_issue, function(i, val) {
                    var count = makeid(10);
                    let warehouse = `<select class="browser-default" id="arr_warehouse_edit` + count + `" name="arr_warehouse[]">`;
                    if (val.list_warehouse.length > 0) {
                        $.each(val.list_warehouse, function(i, valkuy) {
                            warehouse += `<option value="` + valkuy.id + `" ` + (valkuy.id == val.warehouse_id ? 'selected' : '') + `>` + valkuy.name + `</option>`;
                        });
                    } else {
                        warehouse += `<option value="0" data-qty="0,000">--Maaf, item ini tidak memiliki stock--</option>`;
                    }
                    warehouse += `</select>`;
                    $('#body-item-issue_edit').append(`
                        <tr class="row_item_issue_edit" data-id="` + val.id + `">
                            <input type="hidden" name="arr_lookable_type[]" value="` + val.lookable_type + `" readonly>
                            <input type="hidden" name="arr_lookable_id[]" value="` + val.lookable_id + `" data-id="` + count + `" readonly>
                            <input type="hidden" name="arr_production_order_detail_id[]" value="` + val.id + `" readonly>
                            <input type="hidden" name="arr_bom_id[]" value="` + (val.bom_id ? val.bom_id : '0' ) + `" readonly>
                            <input type="hidden" name="arr_bom_detail_id[]" value="` + (val.bom_detail_id ? val.bom_detail_id : '0' ) + `" readonly>
                            <input type="hidden" name="arr_qty_bom[]" value="` + (val.bom_id ? val.qty_bom : '0,000') + `" readonly>
                            <input type="hidden" name="arr_nominal_bom[]" value="` + (val.bom_id ? val.nominal_bom : '0,00') + `" readonly>
                            <input type="hidden" name="arr_total_bom[]" value="` + (val.bom_id ? val.total_bom : '0,00') + `" readonly>
                            <td class="center-align" ` + (val.has_batch ? `rowspan="2"` : ``) + ` readonly>
                                ` + no_issue + `
                            </td>
                            <td>
                                ` + val.lookable_code + ` - ` + val.lookable_name + `
                            </td>
                            <td class="right-align" id="rowPlanned_edit` + count + `">
                                ` + val.qty_planned + `
                            </td>
                            <td class="center">
                                <input name="arr_percentage[]" class="browser-default" type="text" value="0" onkeyup="formatRupiahNoMinus(this);" style="text-align:right;width:100%;" id="rowPercent_edit`+ count +`" readonly>
                            </td>
                            <td class="center">
                                <input name="arr_qty[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinus(this);convertQtyToPercent()" style="text-align:right;width:100%;" id="rowQty_edit`+ count +`" data-id="` + count + `" required data-id="` + count + `"  readonly>
                            </td>
                            <td class="center" id="arr_unit_edit` + count + `"  readonly>
                                ` + val.lookable_unit + `
                            </td>
                            <td class="center">
                                <select class="browser-default" id="arr_place_edit` + count + `" name="arr_place[]"  readonly>
                                    @foreach ($place as $rowplace)
                                        <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="center" id="arr_stock_edit` + count + `">
                                ` + warehouse + `
                            </td>
                            <td class="center">
                                <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item-issue" href="javascript:void(0);">
                                    <i class="material-icons">delete</i>
                                </a>
                            </td>
                        </tr>
                        ` +
                        (val.has_batch ? `<tr class="row_item_batch_edit gradient-45deg-yellow-green" data-id="` + $('#production_order_detail_id_edit').val() + `" data-code="` + count + `">
                            <td colspan="2" class="right-align">
                                Ambil dari Batch : 
                            </td>
                            <td colspan="6">
                                <div class="row">
                                    <div class="input-field col m3 s12">
                                        <select class="browser-default" id="arr_batch_edit` + count + `" name="arr_batch[]" data-id="` + count + `"  readonly></select>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <a href="javascript:void(0);" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text" onclick="addBatch('` + count + `');" id="btn-show_edit"><i class="material-icons right">add_circle_outline</i></a>
                                        <label class="active">&nbsp;</label>
                                    </div>
                                    <div class="col m12 s12">
                                        <table class="bordered" style="width:500px !important;">
                                            <thead>
                                                <tr>
                                                    <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">No.Batch</th>
                                                    <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Qty Dipakai</th>
                                                    <th style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.delete') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="table-batch_edit` + count + `"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>` : ``)
                    );
                    $('#rowQty_edit' + count).trigger('keyup');
                    if (val.has_batch) {
                        let arr_batch_id = [];

                        $.each(val.list_batch, function(i, value) {
                            arr_batch_id.push(value.production_batch_id);
                        });

                        $('#arr_batch_edit' + count).select2({
                            placeholder: '-- Kosong --',
                            minimumInputLength: 1,
                            allowClear: true,
                            cache: true,
                            width: 'resolve',
                            dropdownParent: $('body').parent(),
                            ajax: {
                                url: '{{ url("admin/select2/production_batch") }}',
                                type: 'GET',
                                dataType: 'JSON',
                                data: function(params) {
                                    return {
                                        search: params.term,
                                        item_id: val.lookable_id,
                                        arr_batch_id: arr_batch_id,
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
                    $.each(val.list_batch, function(i, value) {
                        let countdetail = makeid(10);
                        $('#table-batch_edit' + count).append(`
                            <tr>
                                <input type="hidden" name="arr_batch_id[]" id="arr_batch_id_edit` + countdetail + `" value="` + value.production_batch_id + `">
                                <td>` + value.production_batch_code + `</td>
                                <td>
                                    <input name="arr_qty_batch[]" class="qty-batch-edit-` + count + `" type="text" value="` + value.qty + `" onkeyup="formatRupiahNoMinus(this);checkQtyBatch('` + countdetail + `')" data-qty="` + value.max_qty + `" data-id="` + countdetail + `" class="" id="rowBatch_edit`+ countdetail +`" style="text-align:right;"  readonly>    
                                </td>
                                <td>
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1" href="javascript:void(0);" onclick="removeBatch(this,'` + count + `');">
                                        <i class="material-icons">delete</i>
                                    </a> 
                                </td>
                            </tr>
                        `);
                    });
                    $('#arr_place_edit' + count).val(val.place_id);
                    no_issue++;
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