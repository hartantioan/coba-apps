<style>
    .modal {
        top:0px !important;
    }
    table > thead > tr > th {
        font-size: 13px !important;
    }

    body.tab-active input:focus {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .modal-content .select2.tab-active {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
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
                            <li class="breadcrumb-item"><a href="#">{{ Str::title(str_replace('_',' ',Request::segment(3))) }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{ Str::title(str_replace('_',' ',Request::segment(4))) }}
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
                                                <label for="start_date" style="font-size:1rem;">{{ __('translations.start_date') }} : </label>
                                                <div class="input-field col s12">
                                                <input class="form-control" type="date" max="{{ date('9999'.'-12-31') }}" id="start_date" name="start_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="finish_date" style="font-size:1rem;">{{ __('translations.end_date') }} :</label>
                                                <div class="input-field col s12">
                                                    <input class="form-control" type="date" max="{{ date('9999'.'-12-31') }}" id="finish_date" name="finish_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">{{ __('translations.list_data') }}</h4>
                                    <div class="row">
                                        <div class="col s12">
                                            <div class="card-alert card purple">
                                                <div class="card-content white-text">
                                                    <p>Info 1 : Data yang anda tambahkan disini akan mempengaruhi nilai awal dan saldo buku aset terpilih.</p>
                                                </div>
                                            </div>
                                            <div class="card-alert card cyan">
                                                <div class="card-content white-text">
                                                    <p>Info 2 : Aset yang bisa dikapitalisasi hanyalah aset yang belum pernah dikapitalisasikan.</p>
                                                </div>
                                            </div>
                                            <div class="card-alert card red">
                                                <div class="card-content white-text">
                                                    <p>Info 3 : Hati-hati! Pada detail aset <b>Distribusi Biaya</b> jika dipilih maka akan menimpa pengaturan plant, gudang, line, mesin, dan departemen.</p>
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
                                            <table id="datatable_serverside" >
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>No</th>
                                                        <th>{{ __('translations.user') }}</th>
                                                        <th>{{ __('translations.company') }}</th>
                                                        <th>{{ __('translations.currency') }}</th>
                                                        <th>{{ __('translations.conversion') }}</th>
                                                        <th>Tgl.Kapitalisasi</th>
                                                        <th>{{ __('translations.note') }}</th>
                                                        <th>{{ __('translations.status') }}</th>
                                                        <th>By</th>
                                                        <th>{{ __('translations.action') }}</th>
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
    <div class="modal-content" style="overflow-x:hidden !important;">
        <div class="row">
            <div class="col s12">
                <h4>{{ __('translations.add') }}/{{ __('translations.edit') }} {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="input-field col s2 step1">
                            <input id="code" name="code" type="text" value="{{ $newcode }}" readonly>
                            <label class="active" for="code">No. Dokumen</label>
                        </div>
                        <div class="input-field col s1 step2">
                            <select class="form-control" id="code_place_id" name="code_place_id" onchange="getCode(this.value);">
                                <option value="">--Pilih--</option>
                                @foreach ($place as $rowplace)
                                    <option value="{{ $rowplace->code }}">{{ $rowplace->code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-field col s3 step3">
                            <input type="hidden" id="temp" name="temp">
                            <select class="form-control" id="company_id" name="company_id">
                                @foreach($company as $rowcompany)
                                    <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                @endforeach
                            </select>
                            <label class="" for="company_id">{{ __('translations.company') }}</label>
                        </div>
                        <div class="input-field col s3 step4">
                            <select class="form-control" id="currency_id" name="currency_id" onchange="loadCurrency();">
                                @foreach ($currency as $row)
                                    <option value="{{ $row->id }}" data-code="{{ $row->code }}">{{ $row->code.' '.$row->name }}</option>
                                @endforeach
                            </select>
                            <label class="" for="currency_id">{{ __('translations.currency') }}</label>
                        </div>
                        <div class="input-field col s3 step5">
                            <input id="currency_rate" name="currency_rate" type="text" value="1" onkeyup="formatRupiah(this)">
                            <label class="active" for="currency_rate">{{ __('translations.conversion') }}</label>
                        </div>
                        <div class="input-field col s3 step6">
                            <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);loadCurrency();">
                            <label class="active" for="post_date">{{ __('translations.post_date') }}</label>
                        </div>
                        <div class="input-field col m3 step7">
                            <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                            <label class="active" for="note">{{ __('translations.note') }}</label>
                        </div>
                        <div class="col m12 s12 step8">
                            <div class="col m6 s6">
                                <p class="mt-2 mb-2">
                                    <h4>Aset</h4>
                                    <div class="row">
                                        <div class="input-field col m6 s6">
                                            <select class="browser-default" id="asset_id" name="asset_id"></select>
                                            <label class="active" for="asset_id">Aset</label>
                                        </div>
                                        <div class="col m6 s6 mt-4">
                                            <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addAsset();" href="javascript:void(0);">
                                                <i class="material-icons left">add</i> Tambah Aset
                                            </a>
                                        </div>
                                    </div>
                                </p>
                            </div>

                            <div class="input-field col m3 s12 step4">
                                <a href="javascript:void(0);" class="btn waves-effect waves-light cyan" onclick="getAccountData();" id="btn-show">Tampilkan Data<i class="material-icons right">assignment</i></a>
                                <label class="active">&nbsp;</label>
                            </div>
                        </div>
                        <div class="col s12">
                            <div class="mt-2 mb-2" style="overflow:scroll;width:100% !important;">
                                <table class="bordered" id="table-detail" style="min-width:3250px !important;">
                                    <thead>
                                        <tr>
                                            <th class="center">{{ __('translations.no') }}.</th>
                                            <th class="center">Kode Aset</th>
                                            <th class="center">Nama Aset</th>
                                            <th class="center">Plant Aset</th>
                                            <th class="center">Plant Biaya</th>
                                            <th class="center">{{ __('translations.line') }}</th>
                                            <th class="center">{{ __('translations.engine') }}</th>
                                            <th class="center">Departemen</th>
                                            <th class="center">Proyek</th>
                                            <th class="center">Dist.Biaya</th>
                                            <th class="center">Harga@</th>
                                            <th class="center">{{ __('translations.qty') }}</th>
                                            <th class="center">{{ __('translations.unit') }}</th>
                                            <th class="center">{{ __('translations.total') }}</th>
                                            <th class="center">{{ __('translations.note') }}</th>
                                            <th class="center">{{ __('translations.delete') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body-asset">
                                        <tr id="empty-detail">
                                            <td colspan="16" class="center">
                                                Pilih aset untuk memulai...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="right">
                                <h5>TOTAL <span id="total">0,00</span></h5>
                            </div>
                        </div>
                        <div class="col s12 mt-3 step9">
                            <button class="btn waves-effect waves-light right submit" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
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

<div id="modal_asset" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h5>Daftar Asset </h5>
                <div class="row">
                    <div class="col s12 mt-2">
                        <ul class="collapsible">
                            <li class="active">
                                <div class="collapsible-header purple darken-1 text-white" style="color:white;"><i class="material-icons">library_books</i>Asset</div>
                                <div class="collapsible-body" style="display:block;">
                                    <div class="mt-2 mb-2" style="overflow:scroll;width:100% !important;">
                                        <div id="datatable_asset"></div>
                                        <i class="right">Gunakan *pilih semua* untuk memilih seluruh data yang anda inginkan. Atau pilih baris untuk memilih data yang ingin dipindahkan.</i>
                                        <table id="table_asset" class="display" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">No</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Kode</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Nama</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Grup</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Tanggal Pengkapitalan</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Nominal</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Akumulasi Penyusutan</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Saldo</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Sisa Penyusutan</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Metode</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Catatan </th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Kode Inventaris </th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Status </th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-detail-asset"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </li>

                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light amber" onclick="startIntro2();">Panduan <i class="material-icons right">help_outline</i></button>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat mr-1">{{ __('translations.close') }}</a>
        <button class="btn waves-effect waves-light purple right submit step23" onclick="applyDocuments();">Gunakan <i class="material-icons right">forward</i></button>
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
    $(function() {
        $("#table-detail th").resizable({
            minWidth: 100,
        });

        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();

        });

        $('#modal_asset').modal({
            onOpenStart: function(modal,trigger) {

            },
            onOpenEnd: function(modal, trigger) {
                table_asset = $('#table_asset').DataTable({
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
                $('#table_asset_wrapper > .dt-buttons').appendTo('#datatable_buttons_asset');
                $('select[name="table_asset_length"]').addClass('browser-default');
                $('.collapsible').on('shown.bs.collapse', function () {
                    table_asset.columns.adjust().draw();
                });
            },
            onCloseEnd: function(modal, trigger){
                $('#body-detail-asset').empty();
                $('#preview_data').html('');
                $('#table_asset').DataTable().clear().destroy();
            }
        });

        loadDataTable();

        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {

            },
            onOpenEnd: function(modal, trigger) {
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
                window.onbeforeunload = function() {
                    return 'You will lose all changes made since your last save';
                };
                if(!$('#temp').val()){
                    loadCurrency();
                }
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('input').css('border', 'none');
                $('input').css('border-bottom', '0.5px solid black');
                $('#temp').val('');
                $('#total').text('0,00');
                M.updateTextFields();
                resetDetailForm();
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

        select2ServerSide('#asset_id', '{{ url("admin/select2/asset_capitalization") }}');

        $("#item_id").on("select2:unselecting", function(e) {
            $('#code').val('');
            $('#name').val('');
        });

        $('#body-asset').on('click', '.delete-data-asset', function() {
            $(this).closest('tr').remove();
            reInitializedAsset();
        });
    });

    String.prototype.replaceAt = function(index, replacement) {
        return this.substring(0, index) + replacement + this.substring(index + replacement.length);
    };

    function getAccountData(){
        if($('.data-used').length > 0){
            $('.data-used').trigger('click');
        }

        $.ajax({
            url: '{{ Request::url() }}/get_account_data',
            type: 'POST',
            dataType: 'JSON',
            data: {
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            success: function(response) {
                loadingClose('.modal-content');
                $('#modal_asset').modal('open');

                if(response.asset.length > 0){
                        $.each(response.asset, function(i, val) {
                            $('#body-detail-asset').append(`
                                <tr data-id="` + val.id + `">
                                    <td class="center">
                                        ` + i+1 + `
                                    </td>
                                    <td class="center">
                                        ` + val.code + `
                                    </td>
                                    <td class="center">
                                        ` + val.name + `
                                    </td>
                                    <td class="center">
                                        ` + val.group + `
                                    </td>
                                    <td class="center">
                                        ` + val.date + `
                                    </td>
                                    <td class="center">
                                        ` + val.nominal + `
                                    </td>
                                    <td class="center">
                                        ` + val.accumulation_total + `
                                    </td>
                                    <td class="center">
                                        ` + val.book_balance + `
                                    </td>
                                    <td class="center">
                                        ` + val.count_balance + `
                                    </td>
                                    <td class="center">
                                        ` + val.method + `
                                    </td>
                                    <td class="center">
                                        ` + val.note + `
                                    </td>
                                    <td class="">
                                        ` + val.item_code + `
                                    </td>
                                    <td class="">
                                        ` + val.status + `
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
                let arr_asset_id = [], passed = true;
                $.map(table_asset.rows('.selected').nodes(), function (item) {
                    arr_asset_id.push($(item).data('id'));
                });



                if(arr_asset_id.length == 0 ){
                    passed = false;
                }

                if(passed){
                    $.ajax({
                        url: '{{ Request::url() }}/get_asset',
                        type: 'POST',
                        dataType: 'JSON',
                        data: {
                            arr_asset_id: arr_asset_id,
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        beforeSend: function() {
                            loadingOpen('.modal-content');
                        },
                        success: function(response) {
                            loadingClose('.modal-content');

                            if($('.data-used').length > 0){
                                $('.data-used').trigger('click');
                            }

                            $('.last_row_item').remove();

                            if(!$('#temp').val()){
                                $('.row_item').each(function(){
                                    $(this).remove();
                                });
                            }

                            $('#last-row-item').remove();


                            if(passed){
                                $.each(response, function(i, val) {

                                    if(val.details.length > 0){
                                        $('#list-used-data').append(`
                                            <div class="chip purple darken-4 gradient-shadow white-text">
                                                ` + valdetail.code + `
                                                <i class="material-icons close data-used" onclick="removeUsedData('` + valdetail.lookable_type + `','` + valdetail.id + `')">close</i>
                                            </div>
                                        `);

                                        $.each(val.details, function(i, valdetail) {
                                            $('#empty-detail').remove();
                                            var count = makeid(10);
                                            var no = $('.row_asset').length + 1;
                                            $('#body-asset').append(`
                                                <tr class="row_asset">
                                                    <input type="hidden" name="arr_asset_id[]" value="` +val.id + `">
                                                    <td class="center">
                                                        ` + no + `
                                                    </td>
                                                    <td>
                                                        ` +valdetail.code + `
                                                    </td>
                                                    <td>
                                                        ` +valdetail.name + `
                                                    </td>
                                                    <td>
                                                        ` +valdetail.place_code + `
                                                    </td>
                                                    <td>
                                                        <select class="browser-default" id="arr_place` + count + `" name="arr_place[]" style="width:200px !important;">
                                                            <option value="">--{{ __('translations.empty') }}--</option>
                                                            @foreach ($place as $row)
                                                                <option value="{{ $row->id }}">{{ $row->code }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="browser-default" id="arr_line` + count + `" name="arr_line[]" style="width:200px !important;" onchange="changePlace(this);">
                                                            <option value="">--{{ __('translations.empty') }}--</option>
                                                            @foreach ($line as $rowline)
                                                                <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="browser-default" id="arr_machine` + count + `" name="arr_machine[]" style="width:200px !important;" onchange="changeLine(this);">
                                                            <option value="">--{{ __('translations.empty') }}--</option>
                                                            @foreach ($machine as $row)
                                                                <option value="{{ $row->id }}" data-line="{{ $row->line_id }}">{{ $row->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="browser-default" id="arr_department` + count + `" name="arr_department[]" style="width:200px !important;">
                                                            <option value="">--{{ __('translations.empty') }}--</option>
                                                            @foreach ($department as $row)
                                                                <option value="{{ $row->id }}">{{ $row->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                                                    </td>
                                                    <td class="center">
                                                        <select class="browser-default" id="arr_cost_distribution_cost` + count + `" name="arr_cost_distribution_cost[]" onchange="applyCoa('` + count + `');"></select>
                                                    </td>
                                                    <td class="center">
                                                        <input type="text" id="arr_price` + count + `" name="arr_price[]" onfocus="emptyThis(this);" value="0" onkeyup="formatRupiah(this);count();">
                                                    </td>
                                                    <td class="center">
                                                        <input type="text" id="arr_qty` + count + `" name="arr_qty[]" onfocus="emptyThis(this);" value="1" onkeyup="formatRupiah(this);count();" readonly>
                                                    </td>
                                                    <td class="center">
                                                        <select class="browser-default" id="arr_unit` + count + `" name="arr_unit[]"></select>
                                                    </td>
                                                    <td class="center">
                                                        <input type="text" id="arr_total` + count + `" name="arr_total[]" onfocus="emptyThis(this);" value="0,000" onkeyup="formatRupiah(this);" readonly>
                                                    </td>
                                                    <td>
                                                        <input name="arr_note[]" type="text" placeholder="Keterangan">
                                                    </td>
                                                    <td class="center">
                                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-asset" href="javascript:void(0);">
                                                            <i class="material-icons">delete</i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            `);
                                            select2ServerSide('#arr_unit' + count, '{{ url("admin/select2/unit") }}');
                                            select2ServerSide('#arr_project' + count, '{{ url("admin/select2/project") }}');
                                            select2ServerSide('#arr_cost_distribution_cost' + count, '{{ url("admin/select2/cost_distribution") }}');
                                            $('#asset_id').empty();
                                            reInitializedAsset();
                                        });
                                    }



                                });
                                $('#modal_asset').modal('close');
                            }else{
                                $.each(errormessage, function(i, val) {
                                    M.toast({
                                        html: val
                                    });
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
                        text: 'Silahkan pilih data terlebih dahulu.',
                        icon: 'error'
                    });
                }
            }
        });
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

    function count(){
        let total = 0;
        $('input[name^="arr_price"]').each(function(index){
            let price = parseFloat($(this).val().replaceAll(".", "").replaceAll(",",".")), qty = parseFloat($('input[name^="arr_qty"]').eq(index).val().replaceAll(".", "").replaceAll(",","."));
            $('input[name^="arr_total"]').eq(index).val(formatRupiahIni((price * qty).toFixed(2).toString().replace('.',',')));
            total += price * qty;
        });
        $('#total').text(formatRupiahIni(total.toFixed(2).toString().replace('.',',')));
    }

    function resetDetailForm(){
        $('.row_asset').each(function(){
            $(this).remove();
        });
        $('#body-asset').empty().append(`
            <tr id="empty-detail">
                <td colspan="16" class="center">
                    Pilih aset untuk memulai...
                </td>
            </tr>
        `);
    }

    function addAsset(){
        if($('#asset_id').val()){
            $('#empty-detail').remove();
            var count = makeid(10);
            var no = $('.row_asset').length + 1;
            $('#body-asset').append(`
                <tr class="row_asset">
                    <input type="hidden" name="arr_asset_id[]" value="` + $("#asset_id").select2('data')[0].id + `">
                    <td class="center">
                        ` + no + `
                    </td>
                    <td>
                        ` + $("#asset_id").select2('data')[0].code + `
                    </td>
                    <td>
                        ` + $("#asset_id").select2('data')[0].name + `
                    </td>
                    <td>
                        ` + $("#asset_id").select2('data')[0].place_code + `
                    </td>
                    <td>
                        <select class="browser-default" id="arr_place` + count + `" name="arr_place[]" style="width:200px !important;">
                            <option value="">--{{ __('translations.empty') }}--</option>
                            @foreach ($place as $row)
                                <option value="{{ $row->id }}">{{ $row->code }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="browser-default" id="arr_line` + count + `" name="arr_line[]" style="width:200px !important;" onchange="changePlace(this);">
                            <option value="">--{{ __('translations.empty') }}--</option>
                            @foreach ($line as $rowline)
                                <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="browser-default" id="arr_machine` + count + `" name="arr_machine[]" style="width:200px !important;" onchange="changeLine(this);">
                            <option value="">--{{ __('translations.empty') }}--</option>
                            @foreach ($machine as $row)
                                <option value="{{ $row->id }}" data-line="{{ $row->line_id }}">{{ $row->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="browser-default" id="arr_department` + count + `" name="arr_department[]" style="width:200px !important;">
                            <option value="">--{{ __('translations.empty') }}--</option>
                            @foreach ($department as $row)
                                <option value="{{ $row->id }}">{{ $row->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                    </td>
                    <td class="center">
                        <select class="browser-default" id="arr_cost_distribution_cost` + count + `" name="arr_cost_distribution_cost[]" onchange="applyCoa('` + count + `');"></select>
                    </td>
                    <td class="center">
                        <input type="text" id="arr_price` + count + `" name="arr_price[]" onfocus="emptyThis(this);" value="0" onkeyup="formatRupiah(this);count();">
                    </td>
                    <td class="center">
                        <input type="text" id="arr_qty` + count + `" name="arr_qty[]" onfocus="emptyThis(this);" value="1" onkeyup="formatRupiah(this);count();" readonly>
                    </td>
                    <td class="center">
                        <select class="browser-default" id="arr_unit` + count + `" name="arr_unit[]"></select>
                    </td>
                    <td class="center">
                        <input type="text" id="arr_total` + count + `" name="arr_total[]" onfocus="emptyThis(this);" value="0,000" onkeyup="formatRupiah(this);" readonly>
                    </td>
                    <td>
                        <input name="arr_note[]" type="text" placeholder="Keterangan">
                    </td>
                    <td class="center">
                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-asset" href="javascript:void(0);">
                            <i class="material-icons">delete</i>
                        </a>
                    </td>
                </tr>
            `);
            select2ServerSide('#arr_unit' + count, '{{ url("admin/select2/unit") }}');
            select2ServerSide('#arr_project' + count, '{{ url("admin/select2/project") }}');
            select2ServerSide('#arr_cost_distribution_cost' + count, '{{ url("admin/select2/cost_distribution") }}');
            $('#asset_id').empty();
            reInitializedAsset();
        }
    }

    function reInitializedAsset(){
        let arr = [];

        $('input[name^="arr_asset_id[]"]').each(function(index){
            arr.push($(this).val());
        });

        $('#asset_id').select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/asset_capitalization") }}',
                type: 'GET',
                dataType: 'JSON',
                data: function(params) {
                    return {
                        search: params.term,
                        arr_id: arr,
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
                    'modedata' : '{{ $modedata }}',
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
                { name: 'place', className: 'center-align' },
                { name: 'currency_id', className: 'center-align' },
                { name: 'currency_rate', className: 'center-align' },
                { name: 'date', className: 'center-align' },
                { name: 'note', className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'by', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
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

                formData.delete("arr_line[]");
                formData.delete("arr_place[]");
                formData.delete("arr_machine[]");
                formData.delete("arr_department[]");
                formData.delete("arr_project[]");
                formData.delete("arr_cost_distribution_cost[]");

                $('select[name^="arr_line[]"]').each(function(index){
                    formData.append('arr_line[]',($(this).val() ? $(this).val() : ''));
                });
                $('select[name^="arr_place[]"]').each(function(index){
                    formData.append('arr_place[]',($(this).val() ? $(this).val() : ''));
                });
                $('select[name^="arr_machine[]"]').each(function(index){
                    formData.append('arr_machine[]',($(this).val() ? $(this).val() : ''));
                });
                $('select[name^="arr_department[]"]').each(function(index){
                    formData.append('arr_department[]',($(this).val() ? $(this).val() : ''));
                });
                $('select[name^="arr_project[]"]').each(function(index){
                    formData.append('arr_project[]',($(this).val() ? $(this).val() : ''));
                });
                $('select[name^="arr_cost_distribution_cost[]"]').each(function(index){
                    formData.append('arr_cost_distribution_cost[]',($(this).val() ? $(this).val() : ''));
                });

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
                        $('input').css('border', 'none');
                        $('input').css('border-bottom', '0.5px solid black');
                        loadingClose('.modal-content');
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
                $('#place_id').val(response.place_id).formSelect();
                $('#currency_id').val(response.currency_id).formSelect();
                $('#currency_rate').val(response.currency_rate);
                $('#post_date').val(response.post_date);
                $('#note').val(response.note);

                resetDetailForm();

                $('#empty-detail').remove();

                $.each(response.details, function(i, val) {
                    var count = makeid(10);
                    var no = $('.row_asset').length + 1;
                    $('#body-asset').append(`
                        <tr class="row_asset">
                            <input type="hidden" name="arr_asset_id[]" value="` + val.asset_id + `">
                            <td class="center">
                                ` + no + `
                            </td>
                            <td>
                                ` + val.asset_code + `
                            </td>
                            <td>
                                ` + val.asset_name + `
                            </td>
                            <td>
                                ` + val.place_code + `
                            </td>
                            <td>
                                <select class="browser-default" id="arr_place` + count + `" name="arr_place[]" style="width:200px !important;">
                                    <option value="">--{{ __('translations.empty') }}--</option>
                                    @foreach ($place as $row)
                                        <option value="{{ $row->id }}">{{ $row->code }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select class="browser-default" id="arr_line` + count + `" name="arr_line[]" style="width:200px !important;" onchange="changePlace(this);">
                                    <option value="">--{{ __('translations.empty') }}--</option>
                                    @foreach ($line as $rowline)
                                        <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select class="browser-default" id="arr_machine` + count + `" name="arr_machine[]" style="width:200px !important;" onchange="changeLine(this);">
                                    <option value="">--{{ __('translations.empty') }}--</option>
                                    @foreach ($machine as $row)
                                        <option value="{{ $row->id }}" data-line="{{ $row->line_id }}">{{ $row->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select class="browser-default" id="arr_department` + count + `" name="arr_department[]" style="width:200px !important;">
                                    <option value="">--{{ __('translations.empty') }}--</option>
                                    @foreach ($department as $row)
                                        <option value="{{ $row->id }}">{{ $row->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                            </td>
                            <td class="center">
                                <select class="browser-default" id="arr_cost_distribution_cost` + count + `" name="arr_cost_distribution_cost[]" onchange="applyCoa('` + count + `');"></select>
                            </td>
                            <td class="center">
                                <input type="text" id="arr_price` + count + `" name="arr_price[]" onfocus="emptyThis(this);" value="` + val.price + `" onkeyup="formatRupiah(this);count();">
                            </td>
                            <td class="center">
                                <input type="text" id="arr_qty` + count + `" name="arr_qty[]" onfocus="emptyThis(this);" value="` + val.qty + `" onkeyup="formatRupiah(this);count();" readonly>
                            </td>
                            <td class="center">
                                <select class="browser-default" id="arr_unit` + count + `" name="arr_unit[]"></select>
                            </td>
                            <td class="center">
                                <input type="text" id="arr_total` + count + `" name="arr_total[]" onfocus="emptyThis(this);" value="` + val.total + `" onkeyup="formatRupiah(this);" readonly>
                            </td>
                            <td>
                                <input name="arr_note[]" type="text" placeholder="Keterangan" value="` + val.note + `">
                            </td>
                            <td class="center">
                                <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-asset" href="javascript:void(0);">
                                    <i class="material-icons">delete</i>
                                </a>
                            </td>
                        </tr>
                    `);
                    $('#arr_unit' + count).append(`
                        <option value="` + val.unit_id + `">` + val.unit_name + `</option>
                    `);
                    $('#arr_place' + count).val(val.place_id);
                    $('#arr_line' + count).val(val.line_id);
                    $('#arr_machine' + count).val(val.machine_id);
                    $('#arr_department' + count).val(val.department_id);
                    if(val.project_id){
                        $('#arr_project' + count).append(`
                            <option value="` + val.project_id + `">` + val.project_name + `</option>
                        `);
                    }
                    if(val.cost_distribution_id){
                        $('#arr_cost_distribution_cost' + count).append(`
                            <option value="` + val.cost_distribution_id + `">` + val.cost_distribution_name + `</option>
                        `);
                    }
                    select2ServerSide('#arr_project' + count, '{{ url("admin/select2/project") }}');
                    select2ServerSide('#arr_cost_distribution_cost' + count, '{{ url("admin/select2/cost_distribution") }}');
                    select2ServerSide('#arr_unit' + count, '{{ url("admin/select2/unit") }}');
                    $('#asset_id').empty();
                });
                $('#total').text(response.grandtotal);
                $('.modal-content').scrollTop(0);
                $('#code').focus();
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
                    title : 'Kapitalisasi',
                    intro : 'Form untuk menambahkan aset kapital ke perusahaan'
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
                    title : 'Perusahaan',
                    element : document.querySelector('.step3'),
                    intro : 'Perusahaan tempat ini dibuat atau diperuntukkan'
                },
                {
                    title : 'Mata Uang',
                    element : document.querySelector('.step4'),
                    intro : 'Mata Uang yang digunakan dalam mendefinisikan'
                },
                {
                    title : 'Konversi',
                    element : document.querySelector('.step5'),
                    intro : 'Konversi mata uang pada form ini'
                },
                {
                    title : 'Tgl. Posting',
                    element : document.querySelector('.step6'),
                    intro : 'Tanggal Tenggat dari grpo pada form'
                },
                {
                    title : 'Keterangan',
                    element : document.querySelector('.step7'),
                    intro : 'Silahkan isi / tambahkan keterangan untuk dokumen ini untuk dimunculkan di bagian bawah tabel detail produk nantinya, ketika dicetak.'
                },
                {
                    title : 'Aset',
                    element : document.querySelector('.step8'),
                    intro : 'Pemilihan Aset yang akan digunakan dalam pembuatan form'
                },
                {
                    title : 'Tombol Simpan',
                    element : document.querySelector('.step9'),
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

    function changePlace(element){
        $(element).parent().next().find('select[name="arr_machine[]"] option').show();
        if($(element).val()){
            $(element).parent().prev().find('select[name="arr_place[]"]').val($(element).find(':selected').data('place'));
            $(element).parent().next().find('select[name="arr_machine[]"] option[data-line!="' + $(element).val() + '"]').hide();
        }else{
            $(element).parent().prev().find('select[name="arr_place[]"]').val($(element).parent().prev().find('select[name="arr_place[]"] option:first').val());
        }
    }

    function changeLine(element){
        if($(element).val()){
            $(element).parent().prev().find('select[name="arr_line[]"]').val($(element).find(':selected').data('line')).trigger('change');
        }else{
            $(element).parent().prev().find('select[name="arr_line[]"]').val($(element).parent().prev().find('select[name="arr_line[]"] option:first').val()).trigger('change');
        }
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

        window.location = "{{ Request::url() }}/export_from_page?search=" + search + "&status=" + status + "&type_buy=" + type_buy + "&type_deliv=" + type_deliv + "&company=" + company + "&type_pay=" + type_pay + "&supplier=" + supplier + "&currency=" + currency + "&end_date=" + end_date + "&start_date=" + start_date + "&modedata=" + modedata;

    }
</script>
