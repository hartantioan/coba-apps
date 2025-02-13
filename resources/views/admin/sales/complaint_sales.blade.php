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
                            <li class="breadcrumb-item active">{{ Str::title(str_replace('_',' ',Request::segment(3))) }}
                            </li>
                        </ol>
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
                                    <h4 class="card-title">
                                        List Data
                                    </h4>
                                    <div class="row">
                                        <div class="col s12">

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
                                                        <th >#</th>
                                                        <th >{{ __('translations.code') }}</th>
                                                        <th >{{ __('translations.user') }}</th>
                                                        <th >PIC</th>
                                                        <th >Tanggal Post</th>
                                                        <th >Tanggal Komplain</th>
                                                        <th >Dokumen</th>
                                                        <th  class="center">SJ</th>
                                                        <th  class="center">Keterangan</th>
                                                        <th  class="center">Keterangan Komplain</th>
                                                        <th  class="center">Solusi</th>
                                                        <th  class="center">Status</th>
                                                        <th >{{ __('translations.action') }}</th>
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

                            <div class="input-field col m3 s12 ">
                                <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);">
                                <label class="active" for="post_date">{{ __('translations.post_date') }}</label>
                            </div>
                            <div class="input-field col m3 s12 ">
                                <input id="complaint_date" name="complaint_date" type="date" placeholder="Tgl. komplain">
                                <label class="active" for="complaint_date">Tgl. Komplain</label>
                            </div>
                            <div class="input-field col m3 s12 ">
                                <input id="marketing_order_delivery_process_date" name="marketing_order_delivery_process_date" type="date" placeholder="Tgl. SJ" readonly>
                                <label class="active" for="marketing_order_delivery_process_date">Tgl. Surat Jalan</label>
                            </div>
                            <div class="input-field col m5">
                                <select class="browser-default" id="account_id" name="account_id"></select>
                                <label class="active" for="account_id" style="font-size:1rem;">PIC :</label>
                            </div>
                            <div class="input-field col m5">
                                <select class="browser-default" id="lookable_id" name="lookable_id" onchange="getMODSj();"></select>
                                <label class="active" for="lookable_id" style="font-size:1rem;">No SJ :</label>
                            </div>
                            <div class="input-field col m2 s12 step1">
                                <input id="plant" name="plant" type="text" value="-" readonly>
                                <label class="active" for="plant" style="font-size:1rem;">Plant :</label>
                            </div>
                            <div class="col m12 s12 ">
                            </div>
                            <div class="input-field col m3 s12 ">
                                <p><strong>SO SJ:</strong> <span id="so_sj_value"></span></p>
                            </div>
                            <div class="input-field col m3 s12 ">
                                <p><strong>Nama Customer:</strong> <span id="customer_name_value"></span></p>
                            </div>

                            <div class="col s12"></div>
                            <div class="input-field col m3 s12 ">
                                <p><strong>GrandTotal SO:</strong> <span id="grandtotal_value"></span></p>
                            </div>
                            <div class="input-field col m3 s12 ">
                                <p><strong>Volume Pengiriman (m2):</strong> <span id="volume_value"></span></p>
                            </div>
                            <div class="input-field col m3 s12 ">
                                <p><strong>Qty Pengiriman (box):</strong> <span id="qty_value"></span></p>
                            </div>
                            <div class="file-field input-field col m12 s12 step18">
                                <div class="btn">
                                    <span>Dokumen PO</span>
                                    <input type="file" name="file[]" id="file" multiple accept=".pdf, .xlsx, .xls, .jpeg, .jpg, .png, .gif, .word">
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text">
                                </div>
                            </div>
                            <div class="col m12 s12 step22" style="overflow:auto;width:100% !important;">
                                <p class="mt-2 mb-2">
                                    <h4>Detail Produk</h4>
                                    <table class="bordered" style="width:4000px;font-size:0.9rem !important;" id="table-detail">
                                        <thead>
                                            <tr>
                                                <th>{{ __('translations.delete') }}</th>
                                                <th>{{ __('translations.no') }}.</th>
                                                <th>Item</th>
                                                <th>Ketidaksesuaian Warna</th>
                                                <th>Ketidaksesuaian Motif</th>
                                                <th>Ketidaksesuaian Ukuran</th>
                                                <th>Patah/Rusak</th>
                                                <th>Ketidaksesuaian Qty</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-item">
                                            <tr id="last-row-item">
                                            </tr>
                                            <tr>
                                                <td>
                                                    Grandtotal :
                                                </td>
                                                <td id="grandtotal_detail">
                                                    0
                                                </td>
                                                <td>
                                                    Percentage: <span id="percentage_value"></span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </p>
                            </div>
                            <div class="input-field col m3 s12 step23">
                                <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note">{{ __('translations.note') }}</label>
                            </div>
                            <div class="input-field col m3 s12 step24">
                                <textarea class="materialize-textarea preserveLines" id="solution" name="solution" placeholder="Solusi" rows="3"></textarea>
                                <label class="active" for="solution">Solusi</label>
                            </div>
                            <div class="col s12"></div>
                            <div class="input-field col m5">
                                <select class="browser-default" id="marketing_order_id_complaint" name="marketing_order_id_complaint"></select>
                                <label class="active" for="marketing_order_id_complaint">No SO Pengganti Komplain :</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <textarea class="materialize-textarea preserveLines" id="note_complaint" name="note_complaint" placeholder="note_complaint" rows="3"></textarea>
                                <label class="active" for="note_complaint">Keterangan Komplain</label>
                            </div>
                            <div class="col s12 mt-3">
                                <button class="btn waves-effect waves-light right submit step26" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
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
        <div class="row">
            <div class="col s12">
                <form class="row" id="form_done" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert_done" style="display:none;"></div>
                    </div>
                    <p class="mt-2 mb-2">
                        <h4>Detail Penutupan Permintaan Pembelian per Item</h4>
                        <input type="hidden" id="tempDone" name="tempDone">
                        <table class="bordered" style="width:100%;">
                            <thead>
                                <tr>
                                    <th class="center">Tutup</th>
                                    <th class="center">{{ __('translations.item') }}</th>
                                    <th class="center">{{ __('translations.unit') }}</th>
                                    <th class="center">Qty Order</th>
                                    <th class="center">Qty Diterima</th>
                                    <th class="center">Qty Gantungan</th>
                                </tr>
                            </thead>
                            <tbody id="body-done"></tbody>
                        </table>
                    </p>
                    <button class="btn waves-effect waves-light right submit" onclick="saveDone();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
                </form>
                <p>Info : Item yang tertutup akan dianggap sudah diterima / masuk gudang secara keseluruhan, sehingga tidak akan muncul di form Penerimaan PO / Goods Receipt.</p>
            </div>
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
    var mode = '';
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
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        $('#datatable_serverside').on('click', 'button', function(event) {
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
                $('#due_date').attr('min','{{ date("Y-m-d") }}');
                $('#required_date').attr('min','{{ date("Y-m-d") }}');
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
                if(!$('#temp').val()){
                    loadCurrency();
                    $('#company_id').trigger('change');
                }
                /* $('#pr-show,#gi-show,#sj-show').show(); */
                $('#inventory_type').formSelect().trigger('change');
                changeDateMinimum($('#post_date').val());
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('.row_item').remove();
                $('#percentage_value').text('0%');
                $('#grandtotal_detail').text(0);
                $('#lookable_id,#marketing_order_id_complaint').empty();
                document.getElementById("so_sj_value").innerText = "0";
                document.getElementById("customer_name_value").innerText = "0";
                document.getElementById("grandtotal_value").innerText =  "0";
                document.getElementById("volume_value").innerText =  "0";
                document.getElementById("qty_value").innerText =  "0";
                document.getElementById("plant").value = '-';
                document.getElementById("marketing_order_delivery_process_date").value = '-';
                window.onbeforeunload = function() {
                    return null;
                };
                mode = '';
                $('#button-add-item').show();
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
                $('#validation_alert_done').hide();
                $('#validation_alert_done').html('');
            },
            onCloseEnd: function(modal, trigger){
                $('#body-done').empty();
                $('#tempDone').val('');
            }
        });

        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
            updateGrandTotal();
        });

        $('#marketing_order_id_complaint').select2({
            placeholder: '-- Pilih ya --',
            minimumInputLength: 4,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/marketing_order_complaint") }}',
                type: 'GET',
                dataType: 'JSON',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true,
            }
        });

        $('#lookable_id').select2({
            placeholder: '-- Pilih ya --',
            minimumInputLength: 4,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/marketing_order_delivery_process_complaint") }}',
                type: 'GET',
                dataType: 'JSON',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true,
            }
        });

        select2ServerSide('#account_id', '{{ url("admin/select2/employee") }}');

        $("#table-detail th").resizable({
            minWidth: 100,
        });
    });

    function getMODSj() {
        document.getElementById("so_sj_value").innerText = "0";
        document.getElementById("customer_name_value").innerText = "0";
        document.getElementById("grandtotal_value").innerText =  "0";
        document.getElementById("volume_value").innerText =  "0";
        document.getElementById("qty_value").innerText =  "0";
        document.getElementById("plant").value = '-';
        document.getElementById("marketing_order_delivery_process_date").value = '-';
        $('.row_item').remove();
        $('#percentage_value').text('0%');
        $('#grandtotal_detail').text('0');
        if ($('#lookable_id').val()) {
            $('#grand-total').text('0');
            $.ajax({
                type: "POST",
                url: '{{ Request::url() }}/get_mod_sj',
                data: {
                    id: $('#lookable_id').val(),
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                cache: false,
                beforeSend: function () {
                    loadingOpen('.modal-content');
                },
                success: function (data) {
                    loadingClose('.modal-content');
                    if (data.status == '200') {
                        document.getElementById("so_sj_value").innerText = data.sales_order || "No Data";
                        document.getElementById("customer_name_value").innerText = data.customer || "No Data";
                        document.getElementById("grandtotal_value").innerText = data.grandtotal_so || "No Data";
                        document.getElementById("volume_value").innerText = data.qty_m2 || "No Data";
                        document.getElementById("qty_value").innerText = data.box['total_box'] || "No Data";
                        document.getElementById("plant").value = data.plant;
                        document.getElementById("marketing_order_delivery_process_date").value = data.tgl_sj;

                        if (data.detail.length > 0) {

                            let no = $('.row_item').length + 1;
                            $.each(data.detail, function (i, val) {
                                var count = makeid(10);
                                $('#last-row-item').before(`
                                    <tr class="row_item" data-id="` + data.id + `" style="background-color:` + getRandomColor() + `;">
                                        <input type="hidden" name="arr_lookable_id[]" id="arr_lookable_id` + count + `" value="` + val.id + `">
                                        <input type="hidden" name="arr_lookable_type[]" id="arr_lookable_type` + count + `" value="` + val.lookable_type + `">
                                        <input type="hidden" name="arr_box_conversion[]" id="arr_box_conversion` + count + `" value="` + val.box_conversion + `">
                                        <input type="hidden" name="arr_m2_conversion[]" id="arr_m2_conversion` + count + `" value="` + val.m2_conversion + `">
                                        <input type="hidden" name="arr_sale_conversion[]" id="arr_sale_conversion` + count + `" value="` + val.sale_conversion + `">
                                        <td class="center" >
                                            <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                                <i class="material-icons">delete</i>
                                            </a>
                                        </td>
                                        <td id="row-main` + count + `" >
                                            ` + no + `
                                        </td>
                                        <td>
                                            ` + val.item + `
                                        </td>
                                        <td>
                                            <input name="arr_qty_color_mistake[]" class="browser-default qty-input" value="0" type="text" onkeyup="formatRupiahNoMinus(this); countRow('` + count + `'); updateGrandTotal();" style="text-align:right;" id="qty_color_mistake` + count + `" >
                                            <span> m²</span>
                                            <div>
                                                <span id="color_mistake_box` + count + `">Box: 0 </span>
                                                <span id="color_mistake_m2` + count + `"> pcs: 0</span>
                                            </div>
                                        </td>
                                        <td>
                                            <input name="arr_qty_motif_mistake[]" class="browser-default qty-input" value="0" type="text" onkeyup="formatRupiahNoMinus(this); countRow('` + count + `'); updateGrandTotal();" style="text-align:right;" id="qty_motif_mistake` + count + `" >
                                            <span> m²</span>
                                            <div>
                                                <span id="motif_mistake_box` + count + `">Box: 0 </span>
                                                <span id="motif_mistake_m2` + count + `"> pcs: 0</span>
                                            </div>
                                        </td>
                                        <td>
                                            <input name="arr_qty_size_mistake[]" class="browser-default qty-input" value="0" type="text" onkeyup="formatRupiahNoMinus(this); countRow('` + count + `'); updateGrandTotal();" style="text-align:right;" id="qty_size_mistake` + count + `" >
                                            <span> m²</span>
                                            <div>
                                                <span id="size_mistake_box` + count + `">Box: 0 </span>
                                                <span id="size_mistake_m2` + count + `"> pcs: 0</span>
                                            </div>
                                        </td>
                                        <td>
                                            <input name="arr_qty_broken[]" class="browser-default qty-input" value="0" type="text" onkeyup="formatRupiahNoMinus(this); countRow('` + count + `'); updateGrandTotal();" style="text-align:right;" id="qty_broken` + count + `" >
                                            <span> m²</span>
                                            <div>
                                                <span id="broken_box` + count + `">Box: 0 </span>
                                                <span id="broken_m2` + count + `"> pcs: 0</span>
                                            </div>
                                        </td>
                                        <td>
                                            <input name="arr_qty_mistake[]" class="browser-default qty-input" value="0" type="text" onkeyup="formatRupiahNoMinus(this); countRow('` + count + `'); updateGrandTotal();" style="text-align:right;" id="qty_mistake` + count + `" >
                                            <span> m²</span>
                                            <div>
                                                <span id="mistake_box` + count + `">Box: 0 </span>
                                                <span id="mistake_m2` + count + `"> pcs: 0</span>
                                            </div>
                                        </td>
                                        <td>
                                            <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan ">
                                        </td>
                                    </tr>
                                `);

                                no++;
                            });
                        }
                    } else {
                        M.toast({
                            html: data.message
                        });
                    }
                }
            });
        }
    }

    function updateGrandTotal() {
        let total = 0;


        $('.qty-input').each(function () {

            let value = $(this).val().replace(/\./g, '').replace(',', '.');
            value = parseFloat(value);

            if (!isNaN(value)) {
                total += value;
            }
        });
        var volume = parseFloat($('#volume_value').text().replace('.', '').replace(',', '.'));
        var percentage = (total / volume) * 100;
        percentage = percentage.toFixed(2);
        $('#percentage_value').text(percentage + '%');
        $('#grandtotal_detail').text(formatRupiahIni(total.toFixed(2).toString().replace('.',',')));
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
                loadingOpen('#main');
            },
            success: function(response) {
                loadingClose('#main');

                makeTreeOrg(response.message,response.link);

                $('#modal3').modal('open');
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

    function simpleStructrueTree(id){
        $.ajax({
            url: '{{ Request::url() }}/simplestructuretree',
            type: 'GET',
            dataType: 'JSON',
            data: {
                id : id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('#main');
            },
            success: function(response) {
                loadingClose('#main');

                makeTreeOrg(response.message,response.link);

                $('#modal3').modal('open');
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

    var tempTerm = 0;

    function getDetails(type){
    }

    function applyConversion(id){
        if($('#rowQty' + id).data('stockqty')){
            let qtyRaw = parseFloat($('#rowQty' + id).data('stockqty').toString().replaceAll(".", "").replaceAll(",",".")), conversion = parseFloat($('#arr_unit' + id).find(':selected').data('conversion'));
            let newQty = qtyRaw / conversion;
            $('#rowQty' + id).data('qty',formatRupiahIni(newQty.toFixed(2).toString().replace('.',',')));
            $('#rowQty' + id).val(formatRupiahIni(newQty.toFixed(2).toString().replace('.',',')));
        }
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
            if(!$('#temp').val()){
                let newcode = $('#code').val().replaceAt(5,val.split('-')[0].toString().substr(-2));
                if($('#code').val().substring(5, 7) !== val.split('-')[0].toString().substr(-2)){
                    if(newcode.length > 9){
                        newcode = newcode.substring(0, 9);
                    }
                }
                $('#code').val(newcode);
            }
            $('#code_place_id').trigger('change');
            $('#delivery_date').val(val);
            $('#delivery_date').attr('min',val);
        }
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
                { name: 'account', className: '' },
                { name: 'account', className: '' },
                { name: 'post_date', className: 'center-align' },
                { name: 'complaint_date', className: 'center-align' },
                { name: 'attachment', className: 'center-align' },
                { name: 'lookable', className: 'center-align' },
                { name: 'note', className: 'center-align' },
                { name: 'note_complaint', className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'right-align' },
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
                let passedUpload = true;
                var files = document.getElementById('file');

                if(files.files.length > 0){
                    for (var i = 0; i < files.files.length; i++) {
                        var imageSize = files.files[i].size;
                        if(Math.round(imageSize/1024) >= 7168){
                            passedUpload = false;
                        }
                    }
                }

                let countItem = $('.row_item').length;

                if(countItem > 60){
                    swal({
                        title: 'Ups!',
                        text: 'Maaf, tidak bisa menyimpan lebih dari 60 item.',
                        icon: 'error'
                    });
                    return false;
                }

                if(passedUpload){
                    var formData = new FormData($('#form_data')[0]), passedUnit = true;

                    formData.delete("arr_qty_color_mistake[]");
                    formData.delete("arr_lookable_id[]");
                    formData.delete("arr_lookable_type[]");
                    formData.delete("arr_qty_motif_mistake[]");
                    formData.delete("arr_qty_size_mistake[]");
                    formData.delete("arr_qty_broken[]");
                    formData.delete("arr_qty_mistake[]");
                    formData.delete("arr_note[]");
                    var selectedDataId = $('#lookable_id').select2('data')[0].type;
                    formData.append('lookable_type',selectedDataId);

                    var volume = parseFloat($('#volume_value').text().replace('.', '').replace(',', '.'));
                    var grandTotalText = $('#grandtotal_detail').text();
                    var grandTotalCleaned = grandTotalText.replace('Rp', '').replace(/\./g, '').replace(',', '.');
                    var grandTotalValue = parseFloat(grandTotalCleaned);

                    if(volume < grandTotalValue){
                        swal({
                            title: 'Ups!',
                            text: 'Total Komplain lebih dari Grandtotal M2 Kirim',
                            icon: 'error'
                        });
                        return false;
                    }

                    $('input[name^="arr_qty_color_mistake"]').each(function(index){
                        formData.append('arr_qty_color_mistake[]',$(this).val());
                        formData.append('arr_lookable_id[]',$('input[name^="arr_lookable_id[]"]').eq(index).val());
                        formData.append('arr_lookable_type[]',$('input[name^="arr_lookable_type[]"]').eq(index).val());
                        formData.append('arr_qty_motif_mistake[]',$('input[name^="arr_qty_motif_mistake[]"]').eq(index).val());
                        formData.append('arr_qty_size_mistake[]',$('input[name^="arr_qty_size_mistake[]"]').eq(index).val());
                        formData.append('arr_qty_broken[]',$('input[name^="arr_qty_broken[]"]').eq(index).val());
                        formData.append('arr_qty_mistake[]',$('input[name^="arr_qty_mistake[]"]').eq(index).val());
                        formData.append('arr_note[]',$('input[name^="arr_note[]"]').eq(index).val());
                    });
                    var percentageText = $('#percentage_value').text();
                    var percentageNumber = parseFloat(percentageText.replace('%', ''));
                    formData.append('percent',percentageNumber);
                    if(passedUnit){
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
                            text: 'Salah satu item belum diatur satuannya.',
                            icon: 'error'
                        });
                    }
                }else{
                    swal({
                        title: 'Ups!',
                        text: 'Ukuran masing-masing file adalah maksimal 2048 Kb / 2 Mb.',
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
                console.log(response);
                loadingClose('#main');
                $('#modal1').modal('open');
                $('#temp').val(id);
                $('#code_place_id').val(response.code_place_id).formSelect();
                $('#code').val(response.code);
                $('#account_id').empty();
                $('#account_id').append(`
                    <option value="` + response.account_id + `">` + response.customer + `</option>
                `);
                $('#lookable_id').empty();
                $('#lookable_id').append(`
                    <option value="` + response.lookable_id + `">` + response.choosen_sj + `</option>
                `);
                if(response.marketing_order_id_complaint){
                    $('#marketing_order_id_complaint').empty().append(`
                        <option value="` + response.marketing_order_id_complaint + `">` + response.choosen_so + `</option>
                    `);
                }
                $('#inventory_type').val(response.inventory_type).formSelect();
                $('#shipping_type').val(response.shipping_type).formSelect();
                $('#company_id').val(response.company_id).formSelect();
                $('#document_no').val(response.document_no);
                $('#payment_type').val(response.payment_type).formSelect();
                $('#payment_term').val(response.payment_term);
                $('#currency_id').val(response.currency_id).formSelect();
                $('#currency_rate').val(response.currency_rate);
                $('#post_date').val(response.post_date);
                $('#complaint_date').val(response.complaint_date);
                $('#marketing_order_delivery_process_date').val(response.lookable_date);

                document.getElementById("so_sj_value").innerText = response.sj_code || "No Data";
                document.getElementById("customer_name_value").innerText = response.customer || "No Data";
                document.getElementById("grandtotal_value").innerText = response.grandtotal_so || "No Data";
                document.getElementById("volume_value").innerText = response.qty_m2 || "No Data";
                document.getElementById("qty_value").innerText = response.box['total_box'] || "No Data";
                document.getElementById("plant").value = response.plant;

                $('#note').val(response.note);
                $('#solution').val(response.solution);
                M.textareaAutoResize($('#note_external'));
                $('#note_complaint').text(response.note_complaint);

                tempTerm = response.top_master;

                if(response.details.length > 0){
                    let no = $('.row_item').length + 1;
                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        $('#last-row-item').before(`
                            <tr class="row_item" data-id="` + val.id + `" style="background-color:` + getRandomColor() + `;">
                                <input type="hidden" name="arr_lookable_id[]" id="arr_lookable_id` + count + `" value="` + val.id + `">
                                <input type="hidden" name="arr_lookable_type[]" id="arr_lookable_type` + count + `" value="` + val.lookable_type + `">
                                <td class="center" >
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                                <td id="row-main` + count + `" >
                                    ` + (i+1) + `
                                </td>
                                <td>
                                    ` + val.item + `
                                </td>
                                <td>
                                    <input name="arr_qty_color_mistake[]" class="browser-default qty-input" value="`+val.qty_color_mistake+`" type="text" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `');updateGrandTotal();" style="text-align:right;" id="qty_color_mistake` + count + `">
                                    <span> m²</span>
                                </td>
                                <td>
                                    <input name="arr_qty_motif_mistake[]" class="browser-default qty-input" value="`+val.qty_motif_mistake+`" type="text" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `');updateGrandTotal();" style="text-align:right;" id="qty_motif_mistake` + count + `">
                                    <span> m²</span>
                                </td>
                                <td>
                                    <input name="arr_qty_size_mistake[]" class="browser-default qty-input" value="`+val.qty_size_mistake+`" type="text" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `');updateGrandTotal();" style="text-align:right;" id="qty_size_mistake` + count + `">
                                    <span> m²</span>
                                </td>
                                <td>
                                    <input name="arr_qty_broken[]" class="browser-default qty-input" value="`+val.qty_broken+`" type="text" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `');updateGrandTotal();" style="text-align:right;" id="qty_broken` + count + `">
                                    <span> m²</span>
                                </td>
                                <td>
                                    <input name="arr_qty_mistake[]" class="browser-default qty-input" value="`+val.qty_mistake+`" type="text" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `');updateGrandTotal();" style="text-align:right;" id="qty_mistake` + count + `">
                                    <span> m²</span>
                                </td>
                                <td>
                                    <input name="arr_note[]" class="materialize-textarea" type="text" value="`+val.note+`" placeholder="Keterangan ">
                                </td>
                            </tr>
                        `);
                    });
                }


                updateGrandTotal();
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

    function doneStatus(id){
        var msg = '';
        $.ajax({
            url: '{{ Request::url() }}/done_status',
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

    function countRow(id){
        if($('#arr_lookable_id' + id).val()){
            var qtyColorMistake = parseFloat($('#qty_color_mistake' + id).val().replace(/\./g, '').replace(',', '.'));
            var qtyMotifMistake = parseFloat($('#qty_motif_mistake' + id).val().replace(/\./g, '').replace(',', '.'));
            var qtySizeMistake = parseFloat($('#qty_size_mistake' + id).val().replace(/\./g, '').replace(',', '.'));
            var qtyBrokenMistake = parseFloat($('#qty_broken' + id).val().replace(/\./g, '').replace(',', '.'));
            var qtyMistake = parseFloat($('#qty_mistake' + id).val().replace(/\./g, '').replace(',', '.'));

            var boxConversion = parseFloat($('#arr_box_conversion' + id).val()) || 0;

            var m2Conversion = parseFloat($('#arr_m2_conversion' + id).val()) || 0;

            var saleConversion = parseFloat($('#arr_sale_conversion' + id).val()) || 0;

            var totalColorBox = qtyColorMistake /saleConversion * boxConversion;
            var totalColorM2 = qtyColorMistake / m2Conversion;

            var totalMotifBox = qtyMotifMistake/saleConversion * boxConversion;
            var totalMotifM2 = qtyMotifMistake / m2Conversion;

            var totalSizeBox = qtySizeMistake /saleConversion* boxConversion;
            var totalSizeM2 = qtySizeMistake / m2Conversion;

            var totalBrokenBox = qtyBrokenMistake/saleConversion * boxConversion;
            var totalBrokenM2 = qtyBrokenMistake / m2Conversion;

            var totalMistakeBox = qtyMistake/saleConversion * boxConversion;
            var totalMistakeM2 = qtyMistake / m2Conversion;

            $('#color_mistake_box' + id).text('Box: ' + totalColorBox.toFixed(2));
            $('#color_mistake_m2' + id).text('pcs: ' + totalColorM2.toFixed(2));

            $('#motif_mistake_box' + id).text('Box: ' + totalMotifBox.toFixed(2));
            $('#motif_mistake_m2' + id).text('pcs: ' + totalMotifM2.toFixed(2));

            $('#size_mistake_box' + id).text('Box: ' + totalSizeBox.toFixed(2));
            $('#size_mistake_m2' + id).text('pcs: ' + totalSizeM2.toFixed(2));

            $('#broken_box' + id).text('Box: ' + totalBrokenBox.toFixed(2));
            $('#broken_m2' + id).text('pcs: ' + totalBrokenM2.toFixed(2));

            $('#mistake_box' + id).text('Box: ' + totalMistakeBox.toFixed(2));
            $('#mistake_m2' + id).text('pcs: ' + totalMistakeM2.toFixed(2));
        }
    }


    function countGrandtotal(val){
        let total = parseFloat($('#savetotal').val().replaceAll(".", "").replaceAll(",",".")), tax = parseFloat($('#savetax').val().replaceAll(".", "").replaceAll(",",".")), wtax = parseFloat(val.replaceAll(".", "").replaceAll(",","."));
        $('#savewtax').val(val);
        let grandtotal = total + tax - wtax;
        $('#grandtotal').text(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(grandtotal.toFixed(2).toString().replace('.',','))
        );
        $('#savegrandtotal').val(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(grandtotal.toFixed(2).toString().replace('.',','))
        );
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
                    title : 'Purchase Order',
                    intro : 'Form ini digunakan untuk menambahkan pembelian barang dan jasa berdasarkan Purchase Request ataupun Good Issue / Barang Keluar. Silahkan ikuti panduan ini untuk penjelasan mengenai isian form.'
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
                    title : 'Supplier',
                    element : document.querySelector('.step3'),
                    intro : 'Supplier adalah Partner Bisnis tipe penyedia barang / jasa. Jika ingin menambahkan data baru, silahkan ke form Master Data - Organisasi - Partner Bisnis.'
                },
                {
                    title : 'Tipe Pembelian',
                    element : document.querySelector('.step4'),
                    intro : 'Tipe Pembelian berisi barang atau jasa, silahkan pilih barang jika pembelian adalah untuk barang yang ada wujudnya, dan pilih jasa, jika tipe pembelian adalah jasa. Hati-hati karena tipe pembelian barang maka, detail produk di tabel akan otomatis mengambil data dari master data item, sedangkan tipe pembelian jasa akan otomatis ke biaya / COA.'
                },

                {
                    title : 'Tipe Pengiriman',
                    element : document.querySelector('.step6'),
                    intro : 'Franco, merupakan kegiatan jual beli barang di mana biaya pengiriman ditanggung oleh penjual. Sedangkan dalam Loco pembeli yang mendatangi gudang barang dan menanggung semua biaya.'
                },
                {
                    title : 'Perusahaan',
                    element : document.querySelector('.step7'),
                    intro : 'Perusahaan dimana dokumen ini dibuat.'
                },
                {
                    title : 'No. Dokumen',
                    element : document.querySelector('.step8'),
                    intro : 'No dokumen bisa diisikan dengan no dokumen penawaran dari supplier.'
                },
                {
                    title : 'Tipe Pembayaran',
                    element : document.querySelector('.step9'),
                    intro : 'Tipe pembayaran PO, silahkan pilih sesuai keadaan.'
                },
                {
                    title : 'Termin Pembayaran',
                    element : document.querySelector('.step10'),
                    intro : 'Berapa hari termin pembayaran sejak dokumen diterima. Otomatis terisi, ketika anda memilih supplier dan tipe pembayaran Credit.'
                },
                {
                    title : 'Tgl. Terima',
                    element : document.querySelector('.stepreceive'),
                    intro : 'Tanggal untuk menentukan tanggal terima dari order.'
                },
                {
                    title : 'Mata Uang',
                    element : document.querySelector('.step11'),
                    intro : 'Mata uang, silahkan pilih mata uang lain, untuk mata uang asing.'
                },
                {
                    title : 'Konversi',
                    element : document.querySelector('.step12'),
                    intro : 'Nilai konversi rupiah pada saat Purchase Order dibuat. Nilai konversi secara otomatis diisi ketika form tambah baru dibuka pertama kali dan data diambil dari situs exchangerate.host. Pastikan kode mata uang benar di master data agar nilai konversi tidak error.'
                },
                {
                    title : 'Tgl. Posting',
                    element : document.querySelector('.step13'),
                    intro : 'Tanggal post akan menentukan tanggal jurnal untuk beberapa form yang terhubung dengan jurnal. Hati - hati dalam menentukan tanggal posting.'
                },
                {
                    title : 'Tgl. Kirim',
                    element : document.querySelector('.step14'),
                    intro : 'Tanggal perkiraan kirim barang dari Supplier.'
                },
                {
                    title : 'Nama Penerima',
                    element : document.querySelector('.step15'),
                    intro : 'Nama penerima di gudang atau atas nama pembeli.'
                },
                {
                    title : 'Alamat Penerima',
                    element : document.querySelector('.step16'),
                    intro : 'Bisa diisikan dengan alamat gudang atau drop point barang.'
                },
                {
                    title : 'Kontak Penerima',
                    element : document.querySelector('.step17'),
                    intro : 'Nomor telepon atau hp dari yang menerima barang di gudang, atau drop point barang.'
                },
                {
                    title : 'File Lampiran',
                    element : document.querySelector('.step18'),
                    intro : 'Silahkan unggah file lampiran. untuk saat ini hanya bisa mengakomodir 1 file lampiran saja. Jika ingin menambahkan file lebih dari 1, silahkan gabungkan file anda menjadi pdf.'
                },
                {
                    title : 'Purchase Request',
                    element : document.querySelector('.step19'),
                    intro : 'Pilih ini jika ingin menarik data dari Purchase Request aktif yang masih memiliki tunggakan.'
                },
                {
                    title : 'Good Issue / Barang Keluar',
                    element : document.querySelector('.step20'),
                    intro : 'Pilih ini jika ingin menarik data dari Good Issue / Barang Keluar aktif yang ingin menerbitkan barang baru dengan status REPAIR. Gunakan untuk produk yang keluar karena diperbaiki.'
                },
                {
                    title : 'Surat Jalan',
                    element : document.querySelector('.stepsj'),
                    intro : 'Pilih ini jika ingin menarik data dari Marketing Order Delivery Process / Surat Jalan aktif .'
                },
                {
                    title : 'PR/GI Terpakai',
                    element : document.querySelector('.step21'),
                    intro : 'Daftar dokumen referensi yang terpakai akan muncul disini jika anda menggunakan Purchase Request ataupun Good Issue. Anda bisa menghapus dengan cara menekan tombol x pada masing-masing tombol. Fungsi lain dari fitur ini adalah, agar PR/GI tidak bisa dipakai di form selain form aktif saat ini.'
                },
                {
                    title : 'Detail produk',
                    element : document.querySelector('.step22'),
                    intro : 'Silahkan tambahkan produk anda disini, lengkap dengan keterangan detail tentang produk tersebut. Hati-hati dalam menentukan Plant, dan Gudang Tujuan, karena itu nantinya akan menentukan dimana barang ketika diterima.'
                },
                {
                    title : 'Keterangan',
                    element : document.querySelector('.step23'),
                    intro : 'Silahkan isi / tambahkan keterangan untuk dokumen ini untuk dimunculkan di bagian bawah tabel detail produk nantinya, ketika dicetak.'
                },
                {
                    title : 'Keterangan Eksternal',
                    element : document.querySelector('.step24'),
                    intro : 'Keterangan tambahan yang hanya muncul pada saat dokumen dicetak.'
                },
                {
                    title : 'Tabel Informasi Total Transaksi',
                    element : document.querySelector('.step25'),
                    intro : 'Nominal diskon, untuk diskon yang ingin dimunculkan di dalam dokumen ketika dicetak. Diskon ini mengurangi subtotal. Nominal PPh bisa disesuaikan dengan kebutuhan.'
                },
                {
                    title : 'Tombol Simpan',
                    element : document.querySelector('.step26'),
                    intro : 'Silahkan tekan tombol ini untuk menyimpan data, namun pastikan data yang akan anda masukkan benar.'
                },
            ]
        })/* .onbeforechange(function(targetElement){
            alert(this._currentStep);
        }) */.start();
    }

    function whatPrinting(code) {
        $.ajax({
            url: '{{ Request::url() }}/print_individual/' + code,
            type: 'GET',
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            complete: function() {

            },
            success: function(data) {
                loadingClose('.modal-content');

                var newWindow = window.open('', '_blank', 'toolbar=no,scrollbars=yes,resizable=yes,width=800,height=600');

                newWindow.document.write('<iframe id="pdfFrame" src="' + data + '#toolbar=0" style="width:100%; height:100vh; border:none;"></iframe>');

                newWindow.onload = function() {
                    var iframe = newWindow.document.getElementById('pdfFrame');

                    newWindow.document.addEventListener('contextmenu', function(e) {
                        e.preventDefault();
                    });

                    iframe.contentDocument.oncontextmenu = function(){
                        return false;
                    };
                    iframe.onload = function() {
                        iframe.contentWindow.document.addEventListener('contextmenu', function(e) {
                            e.preventDefault();
                        });

                        iframe.contentWindow.document.addEventListener('keydown', function(e) {
                            if (e.ctrlKey && e.key === 'p') {
                                e.preventDefault();
                            }
                        });
                    };

                    newWindow.addEventListener('keydown', function(e) {
                        if (e.ctrlKey && e.key === 'p') {
                            e.preventDefault();
                        }
                    });
                };
            }
        });
    }

    function whatPrintingChi(code){
        $.ajax({
            url: '{{ Request::url() }}/print_individual_chi/' + code,
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

    function exportExcel(){
        var search = table.search();
        var status = $('#filter_status').val();
        var type_buy = $('#filter_inventory').val();
        var type_deliv = $('#filter_shipping').val();
        var company = $('#filter_company').val();
        var type_pay = $('#filter_payment').val();
        var supplier = $('#filter_supplier').val();
        var currency = $('#filter_currency').val();
        var start_date = $('#start_date').val();
        var end_date = $('#finish_date').val();
        var modedata = '{{ $modedata }}';

        window.location = "{{ Request::url() }}/export_from_page?search=" + search + "&status=" + status + "&type_buy=" + type_buy + "&type_deliv=" + type_deliv + "&company=" + company + "&type_pay=" + type_pay + "&supplier=" + supplier + "&currency=" + currency + "&end_date=" + end_date + "&start_date=" + start_date + "&modedata=" + modedata;

    }

    function formatRupiahNominal(angka){
        let decimal = 2;
        if($('#currency_id').val() !== '1'){
            decimal = 11;
        }
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
            if(split[1].length > decimal){
                rupiah = rupiah + ',' + split[1].slice(0,decimal);
            }else{
                rupiah = rupiah + ',' + split[1];
            }
        }else{
            rupiah = rupiah;
        }

        angka.value = sign == '-' ? sign + rupiah : rupiah;
    }
</script>
