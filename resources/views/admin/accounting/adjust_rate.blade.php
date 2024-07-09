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

    .select-wrapper, .select2-container {
        height:auto !important;
    }

    .preserveLines {
        white-space: pre-line;
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
                                    <h4 class="card-title">{{ __('translations.list_data') }}</h4>
                                    <div class="row">
                                        <div class="col s12">
                                            <div class="card-alert card purple">
                                                <div class="card-content white-text">
                                                    <p>Info : Pembaruan Kurs (Adjust Kurs) akan menarik data inventori, hutang usaha belum ditagihkan, hutang usaha, dan kas dalam mata uang asing yang memiliki tunggakan sesuai tanggal posting terpilih.</p>
                                                </div>
                                            </div>
                                            <div id="datatable_buttons"></div>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">{{ __('translations.refresh') }}</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <table id="datatable_serverside" >
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>No</th>
                                                        <th>{{ __('translations.user') }}</th>
                                                        <th>{{ __('translations.company') }}</th>
                                                        <th>Tgl.Post</th>
                                                        <th>{{ __('translations.currency') }}</th>
                                                        <th>Kurs</th>
                                                        <th>{{ __('translations.note') }}</th>
                                                        <th>{{ __('translations.status') }}</th>
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
    <div class="modal-content">
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
                            <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);loadCurrency();">
                            <label class="active" for="post_date">{{ __('translations.post_date') }}</label>
                        </div>
                        <div class="input-field col s3 step7">
                            <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                            <label class="active" for="note">{{ __('translations.note') }}</label>
                        </div>
                        <div class="col s12"></div>
                        <div class="input-field col s3">
                            <select class="form-control" id="currency_id" name="currency_id" onchange="loadCurrency();resetDetailForm();">
                                @foreach ($currency as $row)
                                    <option value="{{ $row->id }}" data-code="{{ $row->code }}">{{ $row->code.' '.$row->name }}</option>
                                @endforeach
                            </select>
                            <label class="" for="currency_id">{{ __('translations.currency') }}</label>
                        </div>
                        <div class="input-field col s3">
                            <input id="currency_rate" name="currency_rate" type="text" value="1" onkeyup="formatRupiah(this);countAll();">
                            <label class="active" for="currency_rate">{{ __('translations.conversion') }}</label>
                        </div>
                        <div class="input-field col s3 step8">
                            <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="preview();" href="javascript:void(0);">
                                <i class="material-icons left">remove_red_eye</i> Ambil Data
                            </a>
                        </div>
                        <div class="col s12 step9">
                            <h5>Preview Tunggakan Dokumen</h5>
                            <table class="bordered" id="table-detail">
                                <thead>
                                    <tr>
                                        <th class="center">{{ __('translations.delete') }}</th>
                                        <th class="center">{{ __('translations.no') }}.</th>
                                        <th class="center">{{ __('translations.number') }}</th>
                                        <th class="center">{{ __('translations.type') }}</th>
                                        <th class="center">Nominal Sisa (FC)</th>
                                        <th class="center">Kurs Terakhir</th>
                                        <th class="center">Nominal Sisa (Rp)</th>
                                        <th class="center">Nominal Terbaru (Rp)</th>
                                        <th class="center">Nominal Selisih (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody id="body-detail">
                                    <tr id="empty-detail">
                                        <td colspan="9" class="center">
                                            Tekan Ambil Data untuk melihat...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col s12 mt-3">
                            <button class="btn waves-effect waves-light right submit step10" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
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
        
        $("#item_id").on("select2:unselecting", function(e) {
            $('#code').val('');
            $('#name').val('');
        });

        $('#body-detail').on('click', '.delete-data-detail', function() {
            $(this).closest('tr').remove();
            countAll();
            if($('.row_detail').length == 0){
                $('#body-detail').append(`
                    <tr id="empty-detail">
                        <td colspan="9" class="center">
                            Tekan Ambil Data untuk melihat...
                        </td>
                    </tr>
                `);
            }
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

    function resetDetailForm(){
        $('.row_detail').each(function(){
            $(this).remove();
        });
        $('#body-detail').empty().append(`
            <tr id="empty-detail">
                <td colspan="9" class="center">
                    Tekan Ambil Data untuk melihat...
                </td>
            </tr>
        `);
        $('#temp').val('');
    }

    function preview(){
        if($('#post_date').val() && $('#company_id').val()){
            $.ajax({
                url: '{{ Request::url() }}/preview',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    company_id : $('#company_id').val(),
                    post_date : $('#post_date').val(),
                    currency_id : $('#currency_id').val(),
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    loadingClose('.modal-content');

                    if(response.status == '200'){
                        resetDetailForm();

                        $('#empty-detail').remove();

                        if(response.result.length > 0){
                            $.each(response.result, function(i, val) {
                                var count = makeid(10);
                                $('#body-detail').append(`
                                    <tr class="row_detail ` + (i == (response.length - 1) ? 'teal lighten-4' : '') + `">
                                        <input type="hidden" name="arr_coa_id[]" value="` + val.coa_id + `">
                                        <input type="hidden" name="arr_type[]" value="` + val.type + `">
                                        <input type="hidden" name="arr_lookable_type[]" value="` + val.lookable_type + `">
                                        <input type="hidden" name="arr_lookable_id[]" value="` + val.lookable_id + `">
                                        <td class="center">
                                            <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                                                <i class="material-icons">delete</i>
                                            </a>
                                        </td>
                                        <td class="center">
                                            ` + (i + 1) + `
                                        </td>
                                        <td>
                                            ` + val.code + `
                                        </td>
                                        <td>
                                            ` + val.type_document + `
                                        </td>
                                        <td>
                                            <input name="arr_nominal_fc[]" onfocus="emptyThis(this);" type="text" value="` + val.nominal_fc + `" onkeyup="formatRupiah(this);" style="text-align:right;" id="arr_nominal_fc`+ count +`" readonly>
                                        </td>
                                        <td>
                                            <input name="arr_latest_rate[]" onfocus="emptyThis(this);" type="text" value="` + val.latest_rate + `" onkeyup="formatRupiah(this);" style="text-align:right;" id="arr_latest_rate`+ count +`" readonly>
                                        </td>
                                        <td>
                                            <input name="arr_nominal_rp[]" onfocus="emptyThis(this);" type="text" value="` + val.nominal_rp + `" onkeyup="formatRupiah(this);" style="text-align:right;" id="arr_nominal_rp`+ count +`" readonly>
                                        </td>
                                        <td>
                                            <input name="arr_nominal_new[]" onfocus="emptyThis(this);" type="text" value="0,00" onkeyup="formatRupiah(this);" style="text-align:right;" id="arr_nominal_new`+ count +`" readonly>
                                        </td>
                                        <td>
                                            <input name="arr_balance[]" onfocus="emptyThis(this);" type="text" value="0,00" onkeyup="formatRupiah(this);" style="text-align:right;" id="arr_balance`+ count +`" readonly>
                                        </td>
                                    </tr>
                                `);
                            });
                            $('#body-detail').append(`
                                <tr>
                                    <th class="right-align" colspan="8">{{ __('translations.total') }}</th>
                                    <th class="right-align" id="total_balance">0,00</th>
                                </tr>
                            `);
                            countAll();
                        }else{
                            resetDetailForm();
                        }
                    }else{
                        swal({
                            title: 'Ups!',
                            text: response.message,
                            icon: 'info'
                        });
                    }

                    $('.modal-content').scrollTop(0);
                    $('#note').focus();
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
            swal({
                title: 'Ups!',
                text: 'Silahkan pilih perusahaan dan periode untuk memulai.',
                icon: 'error'
            });
        }
    }

    function countAll(){
        let currency_rate = parseFloat($('#currency_rate').val().replaceAll(".", "").replaceAll(",",".")), grandtotal = 0;
        $('input[name^="arr_nominal_fc[]"]').each(function(index){
            let rowfc = parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
            let rowrp = parseFloat($('input[name^="arr_nominal_rp[]"]').eq(index).val().replaceAll(".", "").replaceAll(",","."));
            let rownew = rowfc * currency_rate;
            let rowbalance = rownew - rowrp;
            $('input[name^="arr_nominal_new[]"]').eq(index).val(
                (rownew >= 0 ? '' : '-') + formatRupiahIni(rownew.toFixed(2).toString().replace('.',','))
            );
            $('input[name^="arr_balance[]"]').eq(index).val(
                (rowbalance >= 0 ? '' : '-') + formatRupiahIni(rowbalance.toFixed(2).toString().replace('.',','))
            );
            grandtotal += rowbalance;
        });
        $('#total_balance').text(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(grandtotal.toFixed(2).toString().replace('.',','))
        );
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
                { name: 'date', className: 'center-align' },
                { name: 'currency_id', className: 'center-align' },
                { name: 'currency_rate', className: 'right-align' },
                { name: 'note', className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
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

    async function checkStock(){
        var statusStock = '';
        await $.ajax({
            url: '{{ Request::url() }}/check_stock',
            type: 'POST',
            dataType: 'JSON',
            data: {
                company_id : $('#company_id').val(),
                month : $('#month').val(),
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('#detail-fail-stock').empty();
                loadingOpen('.modal-content');
            },
            success: function(response) {
                statusStock = response.status;
                loadingClose('.modal-content');
                if(response.status == 200) {
                    $('#checkstock-progress').hide();
                    $('#checkstock-success').show();
                    
                } else {
                    $('#checkstock-progress').hide();
                    $('#checkstock-fail').show();
                    
                    $.each(response.data, function(i, val) {
                        if(val.passed == '0'){
                            $('#detail-fail-stock').append(`
                                Coa : ` + val.coa_name + ` pada tanggal ` + val.errors[0].date + ` kode jurnal ` + val.errors[0].code + ` dengan catatan  ` + val.errors[0].note + ` nominal ` + val.errors[0].balance + `.<br>
                            `);
                        }
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
        
        return statusStock;
    }

    async function checkCash(){
        var statusCash = '';
        await $.ajax({
            url: '{{ Request::url() }}/check_cash',
            type: 'POST',
            dataType: 'JSON',
            data: {
                company_id : $('#company_id').val(),
                month : $('#month').val(),
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('.modal-content');
                $('#detail-fail-cash').empty();
            },
            success: function(response) {
                statusCash = response.status;
                loadingClose('.modal-content');
                if(response.status == 200) {
                    $('#checkcash-progress').hide();
                    $('#checkcash-success').show();
                } else {
                    $('#checkcash-progress').hide();
                    $('#checkcash-fail').show();

                    $.each(response.data, function(i, val) {
                        if(val.passed == '0'){
                            $('#detail-fail-cash').append(`
                                Coa : ` + val.coa_name + ` pada tanggal ` + val.errors[0].date + ` kode jurnal ` + val.errors[0].code + ` dengan catatan  ` + val.errors[0].note + ` nominal ` + val.errors[0].balance + `.<br>
                            `);
                        }
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
        return statusCash;
    };

    async function checkQty(){
        var statusQty = '';
        await $.ajax({
            url: '{{ Request::url() }}/check_qty',
            type: 'POST',
            dataType: 'JSON',
            data: {
                company_id : $('#company_id').val(),
                month : $('#month').val(),
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('.modal-content');
                $('#detail-fail-qty').empty();
            },
            success: function(response) {
                statusQty = response.status;
                loadingClose('.modal-content');
                if(response.status == 200) {
                    $('#checkqty-progress').hide();
                    $('#checkqty-success').show();
                } else {
                    $('#checkqty-progress').hide();
                    $('#checkqty-fail').show();

                    $.each(response.data, function(i, val) {
                        if(val.passed == '0'){
                            $('#detail-fail-qty').append(`
                                Item : ` + val.item_name + ` pada tanggal ` + val.date + ` kode dokumen ` + val.code + ` dengan catatan  ` + val.note + ` jumlah ` + val.balance + `.<br>
                            `);
                        }
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
        return statusQty;
    };

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
        }).then(async function (willDelete) {
            if (willDelete) {           
                submit();
            }
        });
    }

    function submit(){
        var formData = new FormData($('#form_data')[0]);
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

                resetDetailForm();
                
                $('#temp').val(id);
                $('#code_place_id').val(response.code_place_id).formSelect();
                $('#code').val(response.code);
                $('#company_id').val(response.company_id).formSelect();
                $('#post_date').val(response.post_date);
                $('#note').val(response.note);
                $('#currency_rate').val(response.currency_rate);

                $('#empty-detail').remove();

                $.each(response.details, function(i, val) {
                    var count = makeid(10);
                    $('#body-detail').append(`
                        <tr class="row_detail ` + (i == (response.length - 1) ? 'teal lighten-4' : '') + `">
                            <input type="hidden" name="arr_coa_id[]" value="` + val.coa_id + `">
                            <input type="hidden" name="arr_type[]" value="` + val.type + `">
                            <input type="hidden" name="arr_lookable_type[]" value="` + val.lookable_type + `">
                            <input type="hidden" name="arr_lookable_id[]" value="` + val.lookable_id + `">
                            <td class="center">
                                <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                                    <i class="material-icons">delete</i>
                                </a>
                            </td>
                            <td class="center">
                                ` + (i + 1) + `
                            </td>
                            <td>
                                ` + val.code + `
                            </td>
                            <td>
                                ` + val.type_document + `
                            </td>
                            <td>
                                <input name="arr_nominal_fc[]" onfocus="emptyThis(this);" type="text" value="` + val.nominal_fc + `" onkeyup="formatRupiah(this);" style="text-align:right;" id="arr_nominal_fc`+ count +`" readonly>
                            </td>
                            <td>
                                <input name="arr_latest_rate[]" onfocus="emptyThis(this);" type="text" value="` + val.latest_rate + `" onkeyup="formatRupiah(this);" style="text-align:right;" id="arr_latest_rate`+ count +`" readonly>
                            </td>
                            <td>
                                <input name="arr_nominal_rp[]" onfocus="emptyThis(this);" type="text" value="` + val.nominal_rp + `" onkeyup="formatRupiah(this);" style="text-align:right;" id="arr_nominal_rp`+ count +`" readonly>
                            </td>
                            <td>
                                <input name="arr_nominal_new[]" onfocus="emptyThis(this);" type="text" value="` + val.nominal_new + `" onkeyup="formatRupiah(this);" style="text-align:right;" id="arr_nominal_new`+ count +`" readonly>
                            </td>
                            <td>
                                <input name="arr_balance[]" onfocus="emptyThis(this);" type="text" value="` + val.balance + `" onkeyup="formatRupiah(this);" style="text-align:right;" id="arr_balance`+ count +`" readonly>
                            </td>
                        </tr>
                    `);
                });
                $('#body-detail').append(`
                    <tr>
                        <th class="right-align" colspan="8">{{ __('translations.total') }}</th>
                        <th class="right-align" id="total_balance">` + response.total + `</th>
                    </tr>
                `);

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
                })
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
                    title : 'Penutupan Jurnal',
                    intro : 'Form ini digunakan untuk menutup jurnal per bulan / periode dengan menjurnal-balikkan coa 4,5,6,7,8 dan menyimpan nilai selisih yang menjadi Laba Rugi Berjalan. Selanjutnya akan membuat saldo coa awalan 1,2,3 untuk periode berikutnya di tanggal 1.'
                },
                {
                    title : 'Nomor Dokumen',
                    element : document.querySelector('.step1'),
                    intro : 'Nomor dokumen wajib diisikan, dengan kombinasi 4 huruf kode dokumen, tahun pembuatan dokumen, kode plant, serta nomor urut. Nomor ini bersifat unik, tidak akan sama, dan nomor urut paling belakang akan ter-reset secara otomatis berdasarkan tahun tanggal post.'
                },
                {
                    title : 'Kode Plant',
                    element : document.querySelector('.step2'),
                    intro : 'Kode plant dimana dokumen dibuat.'
                },
                {
                    title : 'Perusahaan',
                    element : document.querySelector('.step3'),
                    intro : 'Perusahaan dimana dokumen ini dibuat.'
                },
                {
                    title : 'Tgl. Posting',
                    element : document.querySelector('.step4'),
                    intro : 'Tanggal post akan menentukan tanggal jurnal untuk beberapa form yang terhubung dengan jurnal. Hati - hati dalam menentukan tanggal posting.' 
                },
                {
                    title : 'Periode / Bulan',
                    element : document.querySelector('.step5'),
                    intro : 'Periode dimana aset itu menyusut.' 
                },
                {
                    title : 'File lampiran',
                    element : document.querySelector('.step6'),
                    intro : 'File lampiran dalam bentuk pdf / gambar.'
                },
                {
                    title : 'Keterangan',
                    element : document.querySelector('.step7'),
                    intro : 'Silahkan isi / tambahkan keterangan untuk dokumen ini untuk dimunculkan di bagian bawah tabel detail produk nantinya, ketika dicetak.' 
                },
                {
                    title : 'Preview',
                    element : document.querySelector('.step8'),
                    intro : 'Digunakan untuk menarik data coa awalan 4,5,6,7,8 pada jurnal dan periode terpilih.' 
                },
                {
                    title : 'List Data',
                    element : document.querySelector('.step9'),
                    intro : 'Merupakan List Coa data tarikan dari jurnal pada periode terpilih.' 
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