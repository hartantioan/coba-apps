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
                                            <div class="col m4 s12 ">
                                            </div>
                                            <div class="col m4 s12 ">
                                                <label for="start_date" style="font-size:1rem;">{{ __('translations.start_date') }} : </label>
                                                <div class="input-field col s12">
                                                <input type="date" max="{{ date('9999'.'-12-31') }}" id="start_date" name="start_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                            <div class="col m4 s12 ">
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
                                    <div class="row mt-2">
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
                                            <table id="datatable_serverside" >
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{ __('translations.code') }}</th>
                                                        <th>{{ __('translations.user') }}</th>
                                                        <th  class="center-align">{{ __('translations.date') }}</th>
                                                        <th  class="center-align">Keterangan</th>
                                                        <th >Grandtotal</th>
                                                        <th >Lampiran</th>
                                                        <th >Operasi</th>
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
                <h5>{{ __('translations.add') }}/{{ __('translations.edit') }} {{ $title }}</h5>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="row">
                            <div class="input-field col m2 s12 step1">
                                <input id="temp" name="temp" type="text" hidden>
                                <label class="active" for="code">No. Dokumen</label>
                                <input id="code" name="code" type="text" readonly>
                            </div>
                            <div class="col m12 s12"></div>
                            <div class="input-field col m3 s12 step6">
                                <input id="post_date" name="post_date" type="date" placeholder="Tgl. diterima" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);">
                                <label class="active" for="post_date">Tgl. Post</label>
                            </div>
                            <div class="file-field input-field col m12 s12 step18">
                                <div class="btn">
                                    <span>Dokumen Lampiran</span>
                                    <input type="file" name="file[]" id="file" multiple accept=".pdf, .xlsx, .xls, .jpeg, .jpg, .png, .gif, .word">
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text">
                                </div>
                            </div>
                            <div class="col s12"></div>
                            <div class="col m12 s12 step12">
                                <p class="mt-2 mb-2">
                                    <h5>Detail Produk</h5>
                                    <div style="overflow:auto;">
                                        <table class="bordered" id="table-detail">
                                            <thead>
                                                <tr>
                                                    <th class="center">{{ __('translations.delete') }}</th>
                                                    <th class="center">Tipe Pengeluaran</th>
                                                    <th class="center">Total</th>
                                                    <th class="center">Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-item">
                                                <tr id="empty-item">
                                                    <td colspan="4" class="center">
                                                        TAMBAH ITEM....
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <thead>
                                                <th colspan="2">TOTAL</th>
                                                <th class="right-align" id="grandtotal">0,000</th>
                                                <th colspan="1"></th>
                                            </thead>
                                        </table>
                                    </div>
                                </p>
                            </div>
                            <div class="input-field col m4 s12 step14">
                                <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note">{{ __('translations.note') }}</label>
                            </div>
                            <div class="col m4 s12">

                            </div>
                            <div class="col m4 s12 mt-1 step14" >

                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1 right" onclick="addItem()" href="javascript:void(0);">
                                    <i class="material-icons left">add</i> Tambah 1
                                </a>
                            </div>
                            <div class="col s12 mt-3">
                                <button class="btn waves-effect waves-light right submit step15" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
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

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div>

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

    var arrpod = [];

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

        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {

            },
            onOpenEnd: function(modal, trigger) {
                getCode();
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                $('.row_item').each(function(){
                    $(this).remove();
                });
                if($('#empty-item').length == 0){
                    $('#body-item').append(`
                        <tr id="empty-item">
                            <td colspan="23" class="center">
                                Pilih purchase order untuk memulai...
                            </td>
                        </tr>
                    `);
                }
                M.updateTextFields();
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }
                window.onbeforeunload = function() {
                    return null;
                };
                $('#grandtotal').text('0,000');
                arrpod = [];
            }
        });

        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
            let id = $(this).data('detail');
            if($('.row_item').length == 0){
                $('#body-item').append(`
                    <tr id="empty-item">
                        <td colspan="8" class="center">
                            Harap Tambah Item Detail terlebih dahulu
                        </td>
                    </tr>
                `);
                countAll();
                fillInArray();
            }
        });
    });

    function countAll(){
        let total = 0;
        $('input[name^="arr_total"]').each(function () {
            let value = $(this).val().replaceAll(".", "").replaceAll(",", "."); // Convert to a valid number format
            let numericValue = parseFloat(value);
            if (!isNaN(numericValue)) {
                total += numericValue;
            }
        });
        $('#grandtotal').text(formatRupiahIni(total.toFixed(3).toString().replace('.',',')));
    }

    function resetDetails(){
        if($('.data-used').length > 0){
            $('.data-used').trigger('click');
        }else{
            $('.row_item').each(function(){
                $(this).remove();
            });
            if($('#empty-item').length == 0){
                $('#body-item').append(`
                    <tr id="empty-item">
                        <td colspan="8" class="center">
                            Pilih purchase order untuk memulai...
                        </td>
                    </tr>
                `);
            }
        }
    }

    String.prototype.replaceAt = function(index, replacement) {
        return this.substring(0, index) + replacement + this.substring(index + replacement.length);
    };

    function getCode(){
        if($('#temp').val()){

        }else{
            if($('#code').val().length > 7){
                $('#code').val($('#code').val().slice(0, 7));
            }
            $.ajax({
                url: '{{ Request::url() }}/get_code',
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
                    codes : $('#filter_code').val(),
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
                { name: 'company_id', className: 'center-align' },
                { name: 'type', className: 'center-align' },
                { name: 'receiver', className: 'center-align' },
                { name: 'document', searchable: false, orderable: false, className: 'center-align' },
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

                formData.delete("arr_total[]");
                formData.delete("arr_note[]");

                $('input[name^="arr_total"]').each(function(index){
                    formData.append('arr_total[]',($(this).val() ? $(this).val() : ''));
                });

                $('input[name^="arr_note"]').each(function(index){
                    formData.append('arr_note[]',($(this).val() ? $(this).val() : ''));
                });

                var path = window.location.pathname;
                path = path.replace(/^\/|\/$/g, '');

                var segments = path.split('/');
                var lastSegment = segments[segments.length - 1];

                formData.append('lastsegment',lastSegment);
                formData.append('grandtotal',$('#grandtotal').text());

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
                        $('#modal1').scrollTop(0);
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

    function addItem(){

        let countItem = $('.row_item').length;

        if(countItem > 59){
            swal({
                title: 'Ups!',
                text: 'Satu PR tidak boleh memiliki baris item lebih dari 60.',
                icon: 'error'
            });
            return false;
        }

        $('#empty-item').remove();
        var count = makeid(10);
        $('#body-item').append(`
            <tr class="row_item" data-id="">
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
                <td>
                    <select class="browser-default item-array" id="arr_expense_type` + count + `" name="arr_expense_type[]"></select>
                </td>
                <td class="center total-column" id="total` + count + `">
                    <input name="arr_total[]" onfocus="emptyThis(this);" id="arr_total` + count + `" type="text" value="0" onkeyup="formatRupiahNoMinus(this);countAll()">
                </td>
                <td>
                    <input name="arr_note[]" type="text" placeholder="Keterangan...">
                </td>
            </tr>
        `);
        $('#arr_expense_type'+ count).select2({
            placeholder: '-- Pilih ya --',
            minimumInputLength: 4,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/expense_type") }}',
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

    }

    function countRow(id){
        if($('#arr_item' + id).val()){
            var qty = parseFloat($('#rowQty' + id).val().replaceAll(".", "").replaceAll(",","."));
            var price = parseFloat($('#rowPrice' + id).val().replaceAll(".", "").replaceAll(",","."));
            var discount = parseFloat($('#rowDiscount' + id).val().replaceAll(".", "").replaceAll(",","."));

            var total = (qty * price )- discount;

            $('#arr_total' + id).val(formatRupiahIni(total.toFixed(3).toString().replace('.',',')));
            $('#total' + id).text(formatRupiahIni(total.toFixed(3).toString().replace('.',',')));
            countAll()
        }
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
                console.log(response);
                $('#code').val(response.code);
                $('#note').val(response.note);
                $('#post_date').val(response.post_date);

                if(response.details.length > 0){
                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        $('#body-item').append(`
                            <tr class="row_item" data-id="">
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                                <td>
                                    <select class="browser-default item-array" id="arr_expense_type` + count + `" name="arr_expense_type[]"></select>
                                </td>
                                <td class="center total-column" id="total` + count + `">
                                    <input name="arr_total[]" onfocus="emptyThis(this);" id="arr_total` + count + `" type="text"  value="` + val.total + `" onkeyup="formatRupiahNoMinus(this);countAll()" >

                                </td>
                                <td>
                                    <input name="arr_note[]" type="text" placeholder="Keterangan..." value="` + val.note + `">
                                </td>
                            </tr>

                        `);

                        $('#arr_expense_type' + count).append(`
                            <option value="` + val.expense_type_id + `">` + val.expense_type_name + `</option>
                        `);
                        $('#arr_expense_type'+ count).select2({
                            placeholder: '-- Pilih ya --',
                            minimumInputLength: 4,
                            allowClear: true,
                            cache: true,
                            width: 'resolve',
                            dropdownParent: $('body').parent(),
                            ajax: {
                                url: '{{ url("admin/select2/expense_type") }}',
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
                    });
                }

                countAll();
                if(response.document){
                    const baseUrl = '{{ URL::to("/") }}/storage/';
                    const filePath = response.document.replace('public/', '');
                    const fileUrl = baseUrl + filePath;
                    displayFile(fileUrl);
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

    function setStock(val){
        if($("#arr_item_stock" + val).val()){
            let qtyMax = parseFloat($('#qty_stock' + val).val().replaceAll(".", "").replaceAll(",","."));
            let qtyInput = parseFloat($('#rowQty' + val).val().replaceAll(".", "").replaceAll(",","."));
            if(qtyInput > qtyMax){
                $('#rowQty' + val).val(formatRupiahIni(qtyMax.toFixed(3).toString().replace('.',',')));
            }

        }else{
            $('#rowQty' + val).val('0,000');
        }
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

    function exportExcel(){
        var search = table.search();
        var status = $('#filter_status').val();
        var start_date = $('#start_date').val();
        var end_date = $('#finish_date').val();
        var modedata = '{{ $modedata }}';

        window.location = "{{ Request::url() }}/export_from_page?search=" + search + "&status=" + status + "&end_date=" + end_date + "&start_date=" + start_date + "&modedata=" + modedata;

    }
</script>
