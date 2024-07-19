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

    table.bordered td {
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
                            <li class="breadcrumb-item"><a href="#">{{ Str::title(str_replace('_',' ',Request::segment(3))) }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{ Str::title(str_replace('_',' ',Request::segment(4))) }}
                            </li>
                        </ol>
                    </div>
                    <div class="col s12 m6 l6">
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="print();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">{{ __('translations.print') }}</span>
                            <i class="material-icons right">local_printshop</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="exportExcel();">
                            <i class="material-icons hide-on-med-and-up">view_list</i>
                            <span class="hide-on-small-onl">Excel</span>
                            <i class="material-icons right">view_list</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3 modal-trigger" href="#modal2">
                            <i class="material-icons hide-on-med-and-up">file_download</i>
                            <span class="hide-on-small-onl">{{ __('translations.import') }}</span>
                            <i class="material-icons right">file_download</i>
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
                            <div class="card-panel">
                                <div class="row">
                                    <div class="col s12 ">
                                        <label for="filter_status" style="font-size:1.2rem;">{{ __('translations.filter_status') }} :</label>
                                        <div class="input-field inline" style="margin-top: 0;margin-bottom: 0;">
                                            <select class="form-control" id="filter_status" onchange="loadDataTable()">
                                                <option value="">{{ __('translations.all') }}</option>
                                                <option value="1">{{ __('translations.active') }}</option>
                                                <option value="2">{{ __('translations.non_active') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">{{ __('translations.list_data') }}</h4>
                                    <div class="row">
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
                                                        <th>#</th>
                                                        <th>{{ __('translations.code') }}</th>
                                                        <th>{{ __('translations.name') }}</th>
                                                        <th>{{ __('translations.item') }}</th>
                                                        <th>{{ __('translations.item_reject') }}</th>
                                                        <th>{{ __('translations.plant') }}</th>
                                                        <th>{{ __('translations.item') }}</th>
                                                        <th>Group</th>
                                                        <th>Standard BOM</th>
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
                <h4>{{ __('translations.add') }}/{{ __('translations.edit') }} Bill of Material</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="input-field col s12 m3">
                            <input type="hidden" id="temp" name="temp">
                            <select class="browser-default" id="item_id" name="item_id" onchange="getCodeAndName();"></select>
                            <label class="active" for="item_id">Item Output (Target Produksi)</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <input id="code" name="code" type="text" placeholder="Kode Bill Of Material">
                            <label class="active" for="code">{{ __('translations.code') }}</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <input id="name" name="name" type="text" placeholder="Nama Bill Of Material">
                            <label class="active" for="name">{{ __('translations.name') }}</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <input id="qty_output" name="qty_output" type="text" placeholder="Qty Output" onkeyup="formatRupiah(this)">
                            <label class="active" for="qty_output">Qty Output (Satuan Produksi)</label>
                            <div class="form-control-feedback production-unit">-</div>
                        </div>
                        <div class="input-field col s12 m3">
                            <select class="browser-default" id="item_reject_id" name="item_reject_id"></select>
                            <label class="active" for="item_reject_id">Item Reject (Jika ada)</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <select class="browser-default" id="bom_standard_id" name="bom_standard_id"></select>
                            <label class="active" for="bom_standard_id">BOM Standard (Jika ada)</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <select class="form-control" id="place_id" name="place_id">
                                @foreach($place as $b)
                                    <option value="{{ $b->id }}">{{ $b->code }}</option>
                                @endforeach
                            </select>
                            <label class="" for="place_id">{{ __('translations.plant') }}</label>
                        </div>
                        <div class="col s12 m12"></div>
                        <div class="input-field col s12 m3">
                            <select class="form-control" id="group" name="group">
                                <option value="1">Powder</option>
                                <option value="2">Green Tile</option>
                                <option value="3">Finished Good</option>
                            </select>
                            <label class="" for="group">Kelompok</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <div class="switch mb-1">
                                <label for="status">{{ __('translations.status') }}</label>
                                <label class="right">
                                    {{ __('translations.non_active') }}
                                    <input checked type="checkbox" id="status" name="status" value="1">
                                    <span class="lever"></span>
                                   {{ __('translations.active') }}
                                </label>
                            </div>
                        </div>
                        <div class="col s12">
                            <h6>Item & Resource Output</h6>
                            <table class="mt-2">
                                <tbody id="body-alternative">
                                    <tr class="row_alternative header_alternative" id="main-alternative0">
                                        <input name="arr_main_alternative[]" value="0" type="hidden">
                                        <td>
                                            <input id="arr_alternative_name0" name="arr_alternative_name[]" type="text" placeholder="Nama Alternatif">
                                        </td>
                                        <td class="center-align">
                                            <label>
                                                <input type="radio" id="arr_alternative_default0" name="arr_alternative_default" value="1" checked>
                                                <span>Default Alternatif</span>
                                            </label>
                                        </td>
                                        <td>
                                           
                                        </td>
                                    </tr>
                                    <tr class="row_alternative" id="detail-alternative0">
                                        <td colspan="3">
                                            <table class="bordered">
                                                <thead>
                                                    <tr>
                                                        <th class="center">{{ __('translations.type') }}</th>
                                                        <th class="center">Item</th>
                                                        <th class="center">{{ __('translations.qty') }}</th>
                                                        <th class="center">Satuan (Produksi)</th>
                                                        <th class="center">Deskripsi</th>
                                                        <th class="center">Issue Method</th>
                                                        <th class="center">{{ __('translations.delete') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="body-detail0">
                                                    <tr id="empty-row-detail0">
                                                        <td colspan="7" class="center">
                                                            <i>Silahkan tambahkan item...</i>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="7" class="center-align">
                                                            <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addDetail('items','0')" href="javascript:void(0);">
                                                                <i class="material-icons left">add</i> Bahan
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col s12 center mt-1">
                            <div>
                                <i>
                                    Untuk detail tipe item, harga 0, karena perhitungan diambil dari rata-rata stok berjalan. Tipe ITEM tidak perlu memilih Dist. Biaya.
                                </i>
                            </div>
                        </div>
                        <div class="col s12 mt-3">
                            
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect green waves-light mr-1 submit" onclick="addAlternative();">Tambah Alternatif <i class="material-icons right">playlist_add</i></button>
        <button class="btn waves-effect waves-light mr-1 submit" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>

<div id="modal2" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;max-width:90%;min-width:90%;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>{{ __('translations.import') }} Excel</h4>
                <div class="col s12">
                    <div id="validation_alertImport" style="display:none;"></div>
                </div>
                <form class="row" action="{{ Request::url() }}/import" method="POST" enctype="multipart/form-data" id="form_dataimport">
                    @csrf
                    <div class="file-field input-field col m6 s12">
                        <div class="btn">
                            <span>Dokumen Excel</span>
                            <input type="file" class="form-control-file" id="fileExcel" name="file">
                        </div>
                        <div class="file-path-wrapper">
                            <input class="file-path validate" type="text">
                        </div>
                    </div>
                    <div class="input-field col m6 s12">
                        <h6>Anda bisa menggunakan fitur upload dokumen excel. Silahkan klik <a href="{{ Request::url() }}/get_import_excel" target="_blank">disini</a> untuk mengunduh.</h6>
                    </div>
                    <div class="input-field col m12 s12">
                        <button type="submit" class="btn cyan btn-primary btn-block right">Kirim</button>
                    </div>
                </form>
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

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
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
        

        loadDataTable();

        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
        });
        
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
                $('ul.tabs').tabs();
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                M.updateTextFields();
                resetDetailForm();
                $('#item_id,#item_reject_id,#bom_standard_id').empty();
                $('#item_reject_id').empty();
            }
        });

        $('#modal2').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onCloseEnd: function(modal, trigger){
                $('#form_dataimport')[0].reset();
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

        $('#form_dataimport').submit(function(event) {
            event.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                type: $(this).attr('method'),
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('#validation_alertImport').hide();
                    $('#validation_alertImport').html('');
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    loadingClose('.modal-content');
                    console.log(response);
                    if(response.status === 200) {
                        successImport();
                        M.toast({
                            html: response.message
                        });
                    } else if(response.status === 400 || response.status === 432) {
                        $('#validation_alertImport').show();
                        $('.modal-content').scrollTop(0);
                        
                       
                    } else {
                        M.toast({
                            html: response.message
                        });
                    }
                },
                error: function(response) {
                    loadingClose('.modal-content');

                    if(response.status === 422) {
                        $('#validation_alertImport').show();
                        $('.modal-content').scrollTop(0);

                        swal({
                            title: 'Ups! Validation',
                            text: 'Check your form.',
                            icon: 'warning'
                        });

                        let errorMessage = '';
                        response.responseJSON.errors.forEach(function(error) {
                            errorMessage += error.errors.join('\n') + '\n';
                        });

                        $('#validation_alertImport').html(`
                            <div class="card-alert card red">
                                <div class="card-content white-text">
                                    <p>${errorMessage}</p>
                                </div>
                                <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                        `).show();
                    }else if(response.status === 400 || response.status === 432) {
                        $('#validation_alertImport').show();
                        $('.modal-content').scrollTop(0);
                       
                        let errorMessage = response.status === 400 ? 
                            `<p> Baris <b>${response.responseJSON.row}</b> </p><p>${response.responseJSON.error}</p><p> di Lembar ${response.responseJSON.sheet}</p><p> Kolom : ${response.responseJSON.column}</p>` : 
                            `<p>${response.responseJSON.message}</p><p> di Lembar ${response.responseJSON.sheet}</p>`;

                        $('#validation_alertImport').append(`
                            <div class="card-alert card red">
                                <div class="card-content white-text">
                                    ${errorMessage}
                                </div>
                                <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                        `);
                    } else {
                        M.toast({
                            html: response.message
                        });
                    }
                }
            });
        });

        
        select2ServerSide('#item_id', '{{ url("admin/select2/bom_item") }}');
        select2ServerSide('#bom_standard_id', '{{ url("admin/select2/bom_standard") }}');
        select2ServerSide('#item_reject_id', '{{ url("admin/select2/bom_item") }}');

        $("#item_id").on("select2:unselecting", function(e) {
            $('#code').val('');
            $('#name').val('');
        });
    });

    function deleteDetail(id,head){
        $('#row_detail' + id).remove();
        if($('#body-detail' + head).children().length == 0){
            $('#body-detail' + head).append(`
                <tr id="empty-row-detail` + head + `">
                    <td colspan="7" class="center">
                        <i>Silahkan tambahkan item...</i>
                    </td>
                </tr>
            `);
        }
    }

    function addAlternative(){
        $('#empty-alternative').remove();
        var count = makeid(10);
        $('#body-alternative').append(`
            <tr class="row_alternative header_alternative" id="main-alternative` + count + `">
                <input name="arr_main_alternative[]" value="` + count + `" type="hidden">
                <td>
                    <input id="arr_alternative_name` + count + `" name="arr_alternative_name[]" type="text" placeholder="Nama Alternatif">
                </td>
                <td class="center-align">
                    <label>
                        <input type="radio" id="arr_alternative_default` + count + `" name="arr_alternative_default" value="1">
                        <span>Default Alternatif</span>
                    </label>
                </td>
                <td>
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1" href="javascript:void(0);" onclick="removeAlternative('` + count + `')">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
            <tr class="row_alternative" id="detail-alternative` + count + `">
                <td colspan="3">
                    <table class="bordered table-composition">
                        <thead>
                            <tr>
                                <th class="center">{{ __('translations.type') }}</th>
                                <th class="center">Item</th>
                                <th class="center">{{ __('translations.qty') }}</th>
                                <th class="center">Satuan (Produksi)</th>
                                <th class="center">Deskripsi</th>
                                <th class="center">Issue Method</th>
                                <th class="center">{{ __('translations.delete') }}</th>
                            </tr>
                        </thead>
                        <tbody id="body-detail` + count + `">
                            <tr id="empty-row-detail` + count + `">
                                <td colspan="7" class="center">
                                    <i>Silahkan tambahkan item...</i>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" class="center-align">
                                    <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addDetail('items','` + count + `')" href="javascript:void(0);">
                                        <i class="material-icons left">add</i> Bahan
                                    </a>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
        `);
    }

    function removeAlternative(id){
        console.log($('#body-alternative').children().length);
        if($('#body-alternative').children('.header_alternative').length > 1){
            
            $('#detail-alternative' + id +',#main-alternative' + id).remove();
            if($('#body-alternative').children().length == 0){
                $('#body-alternative').append(`
                    <tr id="empty-alternative">
                        <td class="center-align" colspan="3">Data alternatif tidak ditemukan, silahkaan tambah manual alternatif menggunakan tombol hijau.</td>
                    </tr>
                `);
            }
        }else{
            M.toast({
                html: 'Minimal terdapat 1 default alternatif'
            });
        }

        if ($('#body-alternative').children('.header_alternative').length == 1) {
        
            $('[name="arr_alternative_default"]:last').prop('checked', true);
        }
    }

    function resetDetailForm(){
        $('.row_alternative').remove();
        if($('#body-alternative').children().length == 0){
            $('#body-alternative').append(`
                <tr class="row_alternative header_alternative" id="main-alternative0">
                    <input name="arr_main_alternative[]" value="0" type="hidden">
                    <td>
                        <input id="arr_alternative_name0" name="arr_alternative_name[]" type="text" placeholder="Nama Alternatif">
                    </td>
                    <td class="center-align">
                        <label>
                            <input type="radio" id="arr_alternative_default0" name="arr_alternative_default" value="1" checked>
                            <span>Default Alternatif</span>
                        </label>
                    </td>
                    <td>
                        
                    </td>
                </tr>
                <tr class="row_alternative" id="detail-alternative0">
                    <td colspan="3">
                        <table class="bordered">
                            <thead>
                                <tr>
                                    <th class="center">{{ __('translations.type') }}</th>
                                    <th class="center">Item</th>
                                    <th class="center">{{ __('translations.qty') }}</th>
                                    <th class="center">Satuan (Produksi)</th>
                                    <th class="center">Deskripsi</th>
                                    <th class="center">Issue Method</th>
                                    <th class="center">{{ __('translations.delete') }}</th>
                                </tr>
                            </thead>
                            <tbody id="body-detail0">
                                <tr id="empty-row-detail0">
                                    <td colspan="7" class="center">
                                        <i>Silahkan tambahkan item...</i>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7" class="center-align">
                                        <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addDetail('items','0')" href="javascript:void(0);">
                                            <i class="material-icons left">add</i> Bahan
                                        </a>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </td>
                </tr>
            `);
        }
    }

    function countAll(){
        $('input[name^="arr_qty[]"]').each(function(index){
            let total = 0, qty = 0, nominal = 0;
            qty = parseFloat($('input[name^="arr_qty[]"]').eq(index).val().replaceAll(".", "").replaceAll(",","."));
            nominal = parseFloat($('input[name^="arr_nominal[]"]').eq(index).val().replaceAll(".", "").replaceAll(",","."));
            total = qty * nominal;
            $('input[name^="arr_total"]').eq(index).val(
                (total >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(total).toString().replace('.',','))
            );
        });
    }

    function addDetail(param,id){
        if($('#empty-row-detail' + id).length > 0){
            $('#empty-row-detail' + id).remove();
        }
        var count = makeid(10);
        var nominal = ``, total = ``;
        if(param == 'items'){
            nominal = `<input name="arr_nominal[]" id="arr_nominal` + count + `" type="hidden" value="0,00">`;
            total = `<input name="arr_total[]" id="arr_total` + count + `" type="hidden" value="0,00" readonly>`;
        }else if(param == 'resources'){
            nominal = `<input name="arr_nominal[]" id="arr_nominal` + count + `" type="text" value="0,00" onkeyup="formatRupiahNoMinus(this);countAll()">`;
            total = `<input name="arr_total[]" id="arr_total` + count + `" type="text" value="0,00" readonly>`;
        }
        $('#body-detail' + id).append(`
            <tr class="row_detail" id="row_detail` + count + `">
                <input name="arr_alternative[]" value="` + id + `" type="hidden">
                <input name="arr_type[]" value="` + param + `" type="hidden">
                ` + (param == 'items' ? `<input id="arr_cost_distribution` + count + `" name="arr_cost_distribution[]" value="" type="hidden">` : `` ) + `<td>
                    ` + (param == 'items' ? 'Item' : 'Resource') + `
                </td>
                <td>
                    <select class="browser-default" name="arr_detail[]" id="arr_detail` + count + `" onchange="getRowUnit('` + count + `','` + param + `')"></select>
                </td>
                <td>
                    <input name="arr_qty[]" id="arr_qty` + count + `" type="text" value="0,000" onkeyup="formatRupiah(this);countAll();">
                </td>
                <td class="center">
                    <span id="arr_satuan` + count + `">-</span>
                </td>
                <td>
                    ` + nominal + `
                </td>
                <td>
                    ` + total + `
                </td>
                <td class="center">
                    ` + (param == 'resources' ? `<select class="browser-default" id="arr_cost_distribution` + count + `" name="arr_cost_distribution[]"></select>` : `` ) + `
                    
                </td>
                <td>
                    <input name="arr_description[]" type="text" placeholder="Deskripsi item material">
                </td>
                <td>
                    <select class="browser-default" id="arr_issue_method` + count + `" name="arr_issue_method[]">
                        <option value="1">MANUAL</option>
                        <option value="2">BACKFLUSH</option>
                    </select>    
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);" onclick="deleteDetail('` + count + `','` + id + `')">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        if(param == 'items'){
            select2ServerSide('#arr_detail' + count, '{{ url("admin/select2/bom_item") }}');
        }else if(param == 'resources'){
            select2ServerSide('#arr_detail' + count, '{{ url("admin/select2/resource") }}');
            select2ServerSide('#arr_cost_distribution' + count, '{{ url("admin/select2/cost_distribution") }}');
        }
    }

    function getRowUnit(val,param){
        if($("#arr_detail" + val).val()){
            $('#arr_satuan' + val).text($("#arr_detail" + val).select2('data')[0].uom);
            if(param == 'resources'){
                $('#arr_nominal' + val).val(formatRupiahIni(parseFloat($("#arr_detail" + val).select2('data')[0].cost).toFixed(2).toString().replace('.',',')));
                $('#arr_qty' + val).val(formatRupiahIni(parseFloat($("#arr_detail" + val).select2('data')[0].qty).toFixed(3).toString().replace('.',',')));
                countAll();
            }
        }else{
            $('#arr_satuan' + val).text('-');
        }
    }

    function getCodeAndName(){
        if($("#item_id").val()){
            $('#code').val($("#item_id").select2('data')[0].code);
            $('#name').val($("#item_id").select2('data')[0].name);
            $('.production-unit').text($("#item_id").select2('data')[0].uom);
        }else{
            $('#code').val('');
            $('#name').val('');
            $('.production-unit').text('-');
        }
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
                    status : $('#filter_status').val(),
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
                { name: 'code', className: '' },
                { name: 'name', className: '' },
                { name: 'item', className: '' },
                { name: 'item_reject', className: '' },
                { name: 'place', className: 'center-align' },
                { name: 'qty_output', className: 'center-align' },
                { name: 'group', className: 'center-align' },
                { name: 'bom_standard_id', className: 'center-align' },
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
        
                formData.delete("arr_alternative_default");

                $('input[name^="arr_alternative_default"]').each(function(index){
                    formData.append('arr_alternative_default[]',($(this).is(':checked') ? $(this).val() : ''));
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
                $('#code').val(response.code);
                $('#name').val(response.name);
                $('#item_id,#item_reject_id').empty();
                $('#item_id').append(`
                    <option value="` + response.item_id + `">` + response.item_name + `</option>
                `);
                if(response.item_reject_id){
                    $('#item_reject_id').append(`
                        <option value="` + response.item_reject_id + `">` + response.item_reject_name + `</option>
                    `);
                }
                if(response.bom_standard_id){
                    $('#bom_standard_id').append(`
                        <option value="` + response.bom_standard_id + `">` + response.bom_standard_name + `</option>
                    `);
                }
                $('#company_id').val(response.company_id).formSelect();
                $('#group').val(response.group).formSelect();
                $('#place_id').val(response.place_id).formSelect();
                $('#qty_output').val(response.qty_output);

                if(response.status == '1'){
                    $('#status').prop( "checked", true);
                }else{
                    $('#status').prop( "checked", false);
                }

                resetDetailForm();

                $('#empty-alternative').remove();

                $.each(response.details, function(i, val) {
                    $('#body-alternative').append(`
                        <tr class="row_alternative header_alternative" id="main-alternative` + val.code + `">
                            <input name="arr_main_alternative[]" value="` + val.code + `" type="hidden">
                            <td>
                                <input id="arr_alternative_name` + val.code + `" name="arr_alternative_name[]" type="text" placeholder="Nama Alternatif" value="` + val.name + `">
                            </td>
                            <td class="center-align">
                                <label>
                                    <input type="radio" id="arr_alternative_default` + val.code + `" name="arr_alternative_default" value="1" ` + (val.is_default ? 'checked' : '') + `>
                                    <span>Default Alternatif</span>
                                </label>
                            </td>
                            <td>
                                <a class="mb-6 btn-floating waves-effect waves-light red darken-1" href="javascript:void(0);" onclick="removeAlternative('` + val.code + `')">
                                    <i class="material-icons">delete</i>
                                </a>
                            </td>
                        </tr>
                        <tr class="row_alternative" id="detail-alternative` + val.code + `">
                            <td colspan="3">
                                <table class="bordered table-composition">
                                    <thead>
                                        <tr>
                                            <th class="center">{{ __('translations.type') }}</th>
                                            <th class="center">Item</th>
                                            <th class="center">{{ __('translations.qty') }}</th>
                                            <th class="center">Satuan (Produksi)</th>
                                            <th class="center">Deskripsi</th>
                                            <th class="center">Issue Method</th>
                                            <th class="center">{{ __('translations.delete') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body-detail` + val.code + `">
                                        <tr id="empty-row-detail` + val.code + `">
                                            <i>Silahkan tambahkan item...</i>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="7" class="center-align">
                                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addDetail('items','` + val.code + `')" href="javascript:void(0);">
                                                    <i class="material-icons left">add</i> Bahan
                                                </a>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </td>
                        </tr>
                    `);

                    $.each(val.details, function(i, value) {
                        var count = makeid(10);
                        $('#body-detail' + val.code).append(`
                            <tr class="row_detail" id="row_detail` + count + `">
                                <input name="arr_alternative[]" value="` + val.code + `" type="hidden">
                                <input name="arr_type[]" value="` + value.lookable_type + `" type="hidden">
                                ` + (value.lookable_type == 'items' ? `<input id="arr_cost_distribution` + count + `" name="arr_cost_distribution[]" value="" type="hidden">` : `` ) + `
                                <td>
                                    ` + (value.lookable_type == 'items' ? 'Item' : 'Resource') + `
                                </td>
                                <td>
                                    <select class="browser-default" name="arr_detail[]" id="arr_detail` + count + `" onchange="getRowUnit('` + count + `','` + value.lookable_type + `')"></select>
                                </td>
                                <td>
                                    <input name="arr_qty[]" id="arr_qty` + count + `" type="text" value="` + value.qty + `" onkeyup="formatRupiah(this);countAll();">
                                </td>
                                <td class="center">
                                    <span id="arr_satuan` + count + `">` + value.uom_unit + `</span>
                                </td>
                                <td>
                                    <input name="arr_description[]" type="text" placeholder="Deskripsi item material" value="` + value.description + `">
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_issue_method` + count + `" name="arr_issue_method[]">
                                        <option value="1">MANUAL</option>
                                        <option value="2">BACKFLUSH</option>
                                    </select>    
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);" onclick="deleteDetail('` + count + `','` + val.code + `')">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                        $('#arr_detail' + count).append(`
                            <option value="` + value.lookable_id + `">` + value.detail_text + `</option>
                        `);
                        if(value.lookable_type == 'items'){
                            select2ServerSide('#arr_detail' + count, '{{ url("admin/select2/bom_item") }}');
                        }else if(value.lookable_type == 'resources'){
                            select2ServerSide('#arr_detail' + count, '{{ url("admin/select2/resource") }}');
                        }
                        if(value.lookable_type == 'resources'){
                            select2ServerSide('#arr_cost_distribution' + count, '{{ url("admin/select2/cost_distribution") }}');
                        }
                        $('#arr_issue_method' + count).val(value.issue_method);
                    });
                });

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

    function successImport(){
        loadDataTable();
        $('#modal2').modal('close');
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

    function print(){
        var search = window.table.search(), status = $('#filter_status').val(), type = $('#filter_type').val(), company = $('#filter_company').val(), account = $('#filter_account').val();
        arr_id_temp=[];
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
                loadingOpen('.modal-content');
            },
            success: function(response) {
                printService.submit({
                    'type': 'INVOICE',
                    'url': response.message
                })
                
               
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

    function exportExcel(){
        var search = window.table.search();
        var status = $('#filter_status').val();
        
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status;
    }
</script>