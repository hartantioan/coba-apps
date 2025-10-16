<style>
    .select2-container--default .select2-selection--multiple, .select2-container--default.select2-container--focus .select2-selection--multiple {
        height: auto !important;
    }

    .select-wrapper, .select2-container {
        height:3.6rem !important;
    }

    table > thead > tr > th {
        font-size: 13px !important;
    }

    table.bordered th {
        padding: 5px !important;
    }

    .select2-container {
        min-width: 200px !important;
    }

    .modal {
        top:0px !important;
    }

    #modal3 {
        top:50px !important;
    }

    .form-control-feedback {
        right:0px !important;
    }
    body.tab-active input:focus {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .modal-content .select2.tab-active {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .input-field label.active {
        color:black;
    }

    label {
        color: black;
    }
</style>
<!-- BEGIN: Page Main-->
<div id="main">
    <div class="row">
        <div class="pt-3 pb-1" id="breadcrumbs-wrapper">
            <!-- Search for small screen-->
            <div class="container">
                <div class="row">
                    <div class="col s12 m6 l6">
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
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="printBarcode();">
                            <i class="material-icons hide-on-med-and-up">graphic_eq</i>
                            <span class="hide-on-small-onl">Barcode</span>
                            <i class="material-icons right">graphic_eq</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="print();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">Rekap</span>
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
                            <ul class="collapsible collapsible-accordion">
                                <li>
                                    <div class="collapsible-header"><i class="material-icons">filter_list</i>{{ __('translations.filter') }}</div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col m4 s12 ">
                                                <label for="filter_status" style="font-size:1rem;">{{ __('translations.filter_status') }} :</label>
                                                <div class="input-field col s12">
                                                    <select class="form-control" id="filter_status" onchange="loadDataTable()">
                                                        <option value="">{{ __('translations.all') }}</option>
                                                        <option value="1">{{ __('translations.active') }}</option>
                                                        <option value="2">{{ __('translations.non_active') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s12 ">
                                                <label for="filter_type" style="font-size:1rem;">Filter Tipe :</label>
                                                <div class="input-field col s12">
                                                    <select class="select2 browser-default" multiple="multiple" id="filter_type" name="filter_type" onchange="loadDataTable()">
                                                        <option value="" disabled>{{ __('translations.all') }}</option>
                                                        <option value="1">Item Stok</option>
                                                        <option value="2">Item Penjualan</option>
                                                        <option value="3">Item Pembelian</option>
                                                        <option value="4">Item Service</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s12 ">
                                                <label for="filter_group" style="font-size:1rem;">Filter Group :</label>
                                                <div class="input-field col s12">
                                                    <select class="select2 browser-default" multiple="multiple" id="filter_group" name="filter_group" onchange="loadDataTable()">
                                                        @foreach($group->whereNull('parent_id') as $c)
                                                            <option value="{{ $c->id }}"> - {{ $c->name }}</option>

                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <div class="card">
                                <div class="card-content">
                                    <div class="row">
                                        <div class="col s12">
                                            <h4 class="card-title">{{ __('translations.list_data') }}</h4>

                                        </div>
                                        <div class="col s12">
                                            @if ($itemsh == 1)
                                                <input type="hidden" id="adaSh" name="adaSh">
                                                <a class="btn btn-floating waves-effect waves-light red darken-4 breadcrumbs-btn right" href="javascript:void(0);" onclick="filterShade()">
                                                    <i class="material-icons hide-on-med-and-up">no shade</i>

                                                    <i class="material-icons right">sim_card_alert</i>
                                                </a>
                                            @endif
                                            @if ($itemex == 1)
                                                <input type="hidden" id="adaUnit" name="adaUnit">
                                                <a class="btn btn-floating waves-effect waves-light red darken-4 breadcrumbs-btn right" href="javascript:void(0);" onclick="filterUnit()">
                                                    <i class="material-icons hide-on-med-and-up">no unit</i>

                                                    <i class="material-icons right">perm_scan_wifi</i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
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
                                                        <th>Image</th>
                                                        <th>Grup</th>
                                                        <th>UOM</th>
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

<div id="modal3" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;max-width:90%;min-width:90%;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Shading Item - <b id="text-shading"></b></h4>
                <div class="row">
                    <div class="col s12">
                        <form class="row" id="form_data_shading" onsubmit="return false;">
                            <div class="col s12">
                                <div id="validation_alert_shading" style="display:none;"></div>
                            </div>
                            <div class="col s12">
                                <div class="row">
                                    <div class="input-field col m2 s2">
                                        <input type="hidden" id="tempShading" name="tempShading">
                                        <input id="shading_code" name="shading_code" type="text" placeholder="Kode Shading">
                                        <label class="active" for="shading_code">Kode Shading</label>
                                    </div>
                                    <div class="input-field col m2 s2">
                                        <button class="btn waves-effect waves-light right submit" onclick="saveShading();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
                                    </div>
                                    <div class="input-field col m2 s2">
                                        <h6>Daftar Shading</h6>
                                    </div>
                                    <div class="input-field col m6 s12" id="list-shading">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
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
                        <h6>Anda bisa menggunakan fitur upload dokumen excel. Silahkan klik <a href="{{-- {{ asset(Storage::url('format_imports/format_copas_ap_invoice_2.xlsx')) }} --}}{{ Request::url() }}/get_import_excel?v=0" target="_blank">disini</a> untuk mengunduh. Untuk Satuan dan Grup Item, silahkan pilih dari dropdown yang tersedia.</h6>
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

<div id="modal1" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;max-width:100%;min-width:90%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>{{ __('translations.add') }}/{{ __('translations.edit') }} Item</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12 row">
                        <div class="col s12 m8 row">
                            <div class="input-field col m4 s12">
                                <select class="browser-default" id="supplier_id" name="supplier_id" onchange="generateCode();"></select>
                                <label class="active" for="supplier_id">Supplier</label>
                            </div>
                            <div class="input-field col s12 m4">
                                <input type="hidden" id="temp" name="temp">
                                <input id="code" name="code" type="text" placeholder="Kode Item">
                                <label class="active" for="code">{{ __('translations.code') }}</label>
                            </div>
                            <div class="input-field col s12 m4">
                                <input id="name" name="name" type="text" placeholder="Nama Item">
                                <label class="active" for="name">{{ __('translations.name') }}</label>
                            </div>
                            <div class="input-field col s12 m4">
                                <input id="note" name="note" type="text" placeholder="Keterangan : sparepart, aktiva, tools, etc">
                                <label class="active" for="note">{{ __('translations.note') }}</label>
                            </div>
                            <div class="input-field col s12 m4 unit-inputs">
                                <select class="select2 browser-default" id="item_group_id" name="item_group_id">
                                    @foreach($group as $c)
                                        <option value="{{ $c->id }}"> - {{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <label class="active" for="item_group_id">Grup Item</label>
                            </div>
                            <div class="input-field col s12 m4">
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
                        </div>
                        <div class="col s12 m4 row">
                            <div class="input-field col s12" style="margin:0 0 0 0 !important;">
                                <div class="switch">
                                    <label for="is_inventory_item">Item untuk Inventori</label>
                                    <label class="right">
                                        {{ __('translations.no') }}
                                        <input type="checkbox" id="is_inventory_item" name="is_inventory_item" value="1">
                                        <span class="lever"></span>
                                        {{ __('translations.yes') }}
                                    </label>
                                </div>
                            </div>
                            <div class="input-field col s12" style="margin:0 0 0 0 !important;">
                                <div class="switch">
                                    <label for="is_sales_item">Item untuk Penjualan</label>
                                    <label class="right">
                                        {{ __('translations.no') }}
                                        <input type="checkbox" id="is_sales_item" name="is_sales_item" value="1" onclick="showSalesComposition();">
                                        <span class="lever"></span>
                                        {{ __('translations.yes') }}
                                    </label>
                                </div>
                            </div>
                            <div class="input-field col s12" style="margin:0 0 0 0 !important;">
                                <div class="switch">
                                    <label for="is_purchase_item">Item untuk Pembelian</label>
                                    <label class="right">
                                        {{ __('translations.no') }}
                                        <input type="checkbox" id="is_purchase_item" name="is_purchase_item" value="1">
                                        <span class="lever"></span>
                                        {{ __('translations.yes') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col s12 m7 row">
                            <div class="col s12">
                                <div class="input-field col s12 unit-inputs">
                                    <select class="select2 browser-default" id="uom_unit" name="uom_unit" onchange="getUnitStock();">
                                        <option value="">--Silahkan pilih--</option>
                                        @foreach ($unit as $row)
                                            <option value="{{ $row->id }}" data-code="{{ $row->code }}">{{ $row->code.' - '.$row->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="active" for="uom_unit">Satuan Stock</label>
                                </div>
                            </div>
                            <div class="col s12">
                                <div class="center">
                                    <h6>Item Jual</h6>
                                </div>
                                <table class="bordered">
                                    <thead>
                                        <tr>
                                            <th class="center" width="50%">Item Jual</th>
                                            <th class="center" width="30%">Qty Konversi</th>
                                            <th class="center">Hapus</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body-unit">
                                        <tr id="empty-unit">
                                            <td colspan="2" class="center">Silahkan tambahkan item Jual</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2" class="center">
                                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addUnit();" href="javascript:void(0);">
                                                    <i class="material-icons left">add</i> Tambah
                                                </a>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="col s12 m3 mt-3">
                            <div class="file-field input-field">
                            <div class="btn">
                                <span>Upload Dokumen</span>
                                <input type="file" id="document" name="document" accept=".pdf,.jpeg,.jpg,.png,.doc,.docx,.xls,.xlsx">
                            </div>
                            <div class="file-path-wrapper">
                                <input class="file-path validate" type="text" placeholder="Unggah file dokumen">
                            </div>
                            </div>

                            <!-- Preview Area -->
                            <div id="document-preview" style="margin-top: 20px;"></div>


                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light mr-1" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
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

<div id="modal7d" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="show_relation_table">

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
    document.getElementById('document').addEventListener('change', function (e) {
    const file = e.target.files[0];
    const previewContainer = document.getElementById('document-preview');
    previewContainer.innerHTML = ''; // clear previous preview

    if (!file) return;

    const fileType = file.type;
    const fileURL = URL.createObjectURL(file);

    if (fileType.startsWith('image/')) {
        // ✅ Preview for images
        const img = document.createElement('img');
        img.src = fileURL;
        img.style.maxWidth = '100%';
        img.style.maxHeight = '400px';
        img.alt = 'Preview Gambar';
        previewContainer.appendChild(img);
    } else if (fileType === 'application/pdf') {
        // ✅ Preview for PDFs
        const iframe = document.createElement('iframe');
        iframe.src = fileURL;
        iframe.width = '100%';
        iframe.height = '500px';
        iframe.style.border = '1px solid #ccc';
        previewContainer.appendChild(iframe);
    } else {
        // ❌ Unsupported (Word, Excel, etc.)
        const msg = document.createElement('p');
        msg.innerText = 'Preview tidak tersedia untuk file ini. Nama file: ' + file.name;
        msg.style.color = 'red';
        previewContainer.appendChild(msg);
    }
    });
</script>


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
    var selected = [], arrCode = [], arrName = [], mainUnit = '';

    $(function() {

        M.Modal.prototype._handleFocus = function (e) {
            if (!this.el.contains(e.target) && this._nthModalOpened === M.Modal._modalsOpen) {
                var s2 = 'select2-search__field';
                if (e.target.className.indexOf(s2)<0) {
                    this.el.focus();
                }
            }
        };

        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
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
                    loadingOpen('.modal-content');
                    $('#validation_alertImport').hide();
                    $('#validation_alertImport').html('');
                },
                success: function(response) {
                    if(response.status == 200) {
                        successImport();
                        M.toast({
                            html: response.message
                        });
                    } else if(response.status == 422) {
                        $('#validation_alertImport').show();
                        $('.modal-content').scrollTop(0);

                        $.each(response.error, function(i, val) {

                            $('#validation_alertImport').append(`
                                    <div class="card-alert card red">
                                        <div class="card-content white-text">
                                            <p> Line <b>` + val.row + `</b> in column <b>` + val.attribute + `</b> </p>
                                            <p> ` + val.errors[0] + `</p>
                                        </div>
                                        <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                    </div>
                                `);
                        });
                    }else if(response.status == 500) {

                        $('#validation_alertImport').show();
                        $('.modal-content').scrollTop(0);

                        $.each(response.error, function(i, val) {
                            $('#validation_alertImport').append(`
                                    <div class="card-alert card red">
                                        <div class="card-content white-text">
                                            <p> ` +val+`</p>
                                        </div>
                                        <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                    </div>
                                `);
                        });
                    }  else if(response.status == 422) {

                    } else {
                        console.log(response);
                    }
                    loadingClose('.modal-content');
                },
                error: function(response) {

                    loadingClose('.modal-content');
                    console.log(response);
                    var errors = response.responseJSON.errors;
                    var errorMessage = '';
                    if(response.status == 422) {
                        $('#validation_alertImport').show();
                        $('.modal-content').scrollTop(0);

                        swal({
                            title: 'Ups! Validation',
                            text: 'Check your form.',
                            icon: 'warning'
                        });

                        $.each(errors, function(index, error) {
                        var message = '';

                        $.each(error.errors, function(index, value) {
                            message += value + '\n';
                        });

                        errorMessage += errors.file;
                    });
                    $('#validation_alertImport').html(`
                        <div class="card-alert card red">
                            <div class="card-content white-text">
                                <p>` + errorMessage + `</p>
                            </div>
                            <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                    `).show();

                    }
                }
            });
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

        $('#modal2').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {

            },
            onCloseEnd: function(modal, trigger){
                $('#form_dataimport')[0].reset();
            }
        });

        $('#modal3').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {

            },
            onCloseEnd: function(modal, trigger){
                $('#text-shading').text('');
                $('#form_data_shading')[0].reset();
                $('#tempShading').val('');
                $('#list-shading').html('');
                loadDataTable();
            }
        });

        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {

            },
            onOpenEnd: function(modal, trigger) {
                $('#code').focus();
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#document-preview').html('');
                $('#uom_unit').val('').trigger('change');
                M.updateTextFields();
                $('.stock-unit').text('-');
                $('#type_id,#size_id,#variety_id,#pattern_id,#pallet_id,#grade_id,#brand_id,#bom_calculator_id').empty();
                $('#item-sale-show').hide();
                arrCode = [];
                arrName = [];
                $('#body-unit').empty().append(`
                    <tr id="empty-unit">
                        <td colspan="6" class="center">Silahkan tambahkan satuan konversi</td>
                    </tr>
                `);
                $('.unit-inputs').css('pointer-events','auto');
                $("#item_group_id").val($("#item_group_id option:first").val()).trigger('change');
                $('#temp').val('');
                $('#code,#name').prop('readonly',false);
                $('#body-parameter').empty().append(`
                    <tr id="empty-parameter">
                        <td colspan="4" class="center">Silahkan tambahkan parameter</td>
                    </tr>
                `);
                $('#quality_parameters').addClass('hide');
            }
        });

        $('#modal7d').modal({
            onOpenStart: function(modal,trigger) {

            },
            onOpenEnd: function(modal, trigger) {
            },
            onCloseEnd: function(modal, trigger){
                $('#show_relation_table').empty();
            }
        });

        $("#item_group_id").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        $("#filter_type").select2({
            placeholder: "Kosong untuk semua tipe.",
            dropdownAutoWidth: true,
            width: '100%',
        });

        $('#datatable_serverside tbody').on('click', 'tr', function () {
            var poin = $(this).find('td:nth-child(2)').text().trim();
            var index = $.inArray(poin, selected);
            if ( index === -1 ) {
                selected.push(poin);
            } else {
                selected.splice( index, 1 );
            }
        });

        $('.buttons-select-all[aria-controls="datatable_serverside"]').on('click', function (e) {
            selectDeselectRow();
        });

        $('.buttons-select-none[aria-controls="datatable_serverside"]').on('click', function (e) {
            selectDeselectRow();
        });

        select2ServerSide('#type_id', '{{ url("admin/select2/type") }}');
        select2ServerSide('#supplier_id', '{{ url("admin/select2/supplier_store") }}');
        select2ServerSide('#size_id', '{{ url("admin/select2/size") }}');
        select2ServerSide('#variety_id', '{{ url("admin/select2/variety") }}');
        select2ServerSide('#bom_calculator_id', '{{ url("admin/select2/bom_calculator") }}');

        $('#pattern_id').select2({
            placeholder: '-- Pilih ya --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/pattern") }}',
                type: 'GET',
                dataType: 'JSON',
                data: function(params) {
                    return {
                        search: params.term,
                        brand_id: $('#brand_id').val(),
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.items
                    }
                }
            }
        });

        select2ServerSide('#pallet_id', '{{ url("admin/select2/pallet") }}');
        select2ServerSide('#grade_id', '{{ url("admin/select2/grade") }}');
        select2ServerSide('#brand_id', '{{ url("admin/select2/brand") }}');

        /* $('.select2').each(function () {
            $(this).select2({
                dropdownParent: $(this).parent(),
            });
        }); */

        $(document).on('select2:close', '.select2', function (e) {
            var evt = "scroll.select2";
            $(e.target).parents().off(evt);
            $(window).off(evt);
        });

        $('#body-unit').on('click', '.delete-data-unit', function() {
            $(this).closest('tr').remove();
            if($('.row_unit').length == 0){
                $('#body-unit').append(`
                    <tr id="empty-unit">
                        <td colspan="2" class="center">Silahkan tambahkan item Jual</td>
                    </tr>
                `);
            }
        });

        $('#body-parameter').on('click', '.delete-data-parameter', function() {
            $(this).closest('tr').remove();
            if($('.row_parameter').length == 0){
                $('#body-parameter').append(`
                    <tr id="empty-parameter">
                        <td colspan="4" class="center">Silahkan tambahkan parameter</td>
                    </tr>
                `);
            }
        });
    });

    function showQcParameter(){
        if($('#is_quality_check').is(':checked')){
            $('#quality_parameters').removeClass('hide');
        }else{
            $('#quality_parameters').addClass('hide');
            $('.row_parameter').remove();
        }
    }

    function addParameter(){
        if($('#empty-parameter').length > 0){
            $('#empty-parameter').remove();
        }
        $('#body-parameter').append(`
            <tr class="row_parameter">
                <td>
                    <input name="arr_name_parameter[]" type="text">
                </td>
                <td>
                    <input name="arr_unit_parameter[]" type="text">
                </td>
                <td class="center-align">
                    <label>
                        <input type="checkbox" name="arr_is_affect_qty[]" value="1">
                        <span>&nbsp;</span>
                    </label>
                </td>
                <td class="center-align">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-parameter" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
    }

    function addUnit(){
        if($('#empty-unit').length > 0){
            $('#empty-unit').remove();
        }
        let count = makeid(10);
        $('#body-unit').append(`
            <tr class="row_unit">
                <td class="unit-inputs">
                    <select class="browser-default item-array" id="arr_item_conversion` + count + `" name="arr_item_conversion[]" data-id="` + count + `"></select>
                </td>
                <td>
                    <input name="arr_qty[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="0,000" onkeyup="formatRupiah(this);" style="text-align:right;width:100%;" id="rowQty`+ count +`">
                </td>
                <td class="center-align unit-inputs">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-unit" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);

        $('select[name="arr_item_conversion[]"]').select2({
            dropdownAutoWidth: true,
            width: '100%',
        });
        select2ServerSideLonger('#arr_item_conversion' + count, '{{ url("admin/select2/sales_item") }}');
    }

    function getUnitStock(){
        if($('#uom_unit').val()){
            $('.stock-unit').text($("#uom_unit").select2().find(":selected").data("code"));
        }else{
            $('.stock-unit').text('-');
        }
    }

    function shading(id,name){
        $('#text-shading').text(name);
        $('#tempShading').val(id);
        $('#modal3').modal('open');
        refreshShading(id);
    }

    function refreshShading(id){
        $.ajax({
            url: '{{ Request::url() }}/show_shading',
            type: 'POST',
            dataType: 'JSON',
            data: {
                id: id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            success: function(response) {
                loadingClose('.modal-content');

                $('#list-shading').html('');

                if(response.shadings.length > 0){
                    $.each(response.shadings, function(i, val) {
                        $('#list-shading').append(`
                            <div class="chip gradient-45deg-purple-deep-orange white-text" style="font-size: 15px !important;line-height: 30px !important;font-weight: 700 !important;">
                                ` + val.code + `
                                <i class="material-icons close" onclick="destroyShading(` + val.id + `,` + val.item_id + `,this);return false;">close</i>
                            </div>
                        `);
                    });
                    $('.chip > .close').click(function() {
                        return false;
                    });
                }else{
                    $('#list-shading').html(`
                        <div class="card-alert card red" style="margin: 0 0 0 0 !important;">
                            <div class="card-content white-text">
                                <p>Shading tidak ditemukan.</p>
                            </div>
                            <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                    `);
                }

                $('.modal-content').scrollTop(0);
                $('#shading_code').focus();
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

    function showSalesComposition(){
        if($('#is_sales_item').is(':checked')){
            $('#item-sale-show').show();
        }else{
            $('#item-sale-show').hide();
            $('#type_id,#size_id,#variety_id,#pattern_id,#pallet_id,#grade_id,#brand_id').empty();
        }
    }

    function generateCode(){
        arrCode = [];
        arrName = [];
        if($('#supplier_id').val() ){
            if($('#supplier_id').val()){
                arrCode.push($('#supplier_id').select2('data')[0].code ? $('#supplier_id').select2('data')[0].code : $('#supplier_id').find(":selected").data("code"));
                arrName.push($('#supplier_id').select2('data')[0].name ? $('#supplier_id').select2('data')[0].name : $('#supplier_id').find(":selected").data("name"));
            }
            let newCode = arrCode.join('.');
            let newName = arrName.join(' ');
            $('#code').val(newCode);
            $('#name').val(newName);
        }else{
            $('#code,#name').val('');
        }
    }

    function selectDeselectRow(){
        $.map(window.table.rows().nodes(), function (item) {
            if($(item).hasClass('selected')){
                var poin = $(item).find('td:nth-child(2)').text().trim();
                var index = $.inArray(poin, selected);
                if ( index === -1 ) {
                    selected.push(poin);
                }
            }else{
                var poinkuy = $(item).find('td:nth-child(2)').text().trim();
                var indexkuy = $.inArray(poinkuy, selected);
                if ( indexkuy >= 0 ) {
                    selected.splice( indexkuy, 1 );
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

    function documentRelation(data) {
        $.ajax({
            url: '{{ Request::url() }}/document_relation',
            type: 'GET',
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            data: {
                id: data
            },
            success: function(response) {
                $('#modal7d').modal('open');
                $('#show_relation_table').html(response);
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
            "order": [[0, 'desc']],
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    adaUnit : $('#adaUnit').val(),
                    adaShading : $('#adaSh').val(),
                    status : $('#filter_status').val(),
                    'type[]' : $('#filter_type').val(),
                    'group[]' : $('#filter_group').val()
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
                { name: 'group', className: '' },
                { name: 'image', className: '' },
                { name: 'uom', className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle',
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
            "rowCallback": function( row, data ) {
                if ( $.inArray(data[1], selected) !== -1 ) {
                    this.api().row(row).select();
                }
            }
        });
        $('.dt-buttons').appendTo('#datatable_buttons');
        $('select[name="datatable_serverside_length"]').addClass('browser-default');
	}

    function isDuplicate(arr,val){
        let ada = false;
        for(let i=0;i<arr.length;i++){
            if(arr[i] == val){
                ada = true;
            }
        }
        return ada;
    }

    function save(){

        var formData = new FormData($('#form_data')[0]), passed = true, passedSameUnit = true;

        formData.delete("arr_sell_unit[]");
        formData.delete("arr_is_affect_qty[]");
        formData.delete("arr_buy_unit[]");
        formData.delete("arr_default");

        let arrUnit = [];
        $('select[name^="arr_unit[]"]').each(function(index){
            if(!$(this).val()){
                passed = false;
            }else{
                if(isDuplicate(arrUnit,$(this).val())){
                    passedSameUnit = false;
                }
                arrUnit.push($(this).val());
            }
        });
        $('input[name^="arr_conversion[]"]').each(function(index){
            if($(this).val() == '' || parseFloat($(this).val().replaceAll(".", "").replaceAll(",",".")) == 0){
                passed = false;
            }
        });
        $('input[name^="arr_sell_unit[]"]').each(function(index){
            formData.append('arr_sell_unit[]',($(this).is(':checked') ? $(this).val() : ''));
        });
        $('input[name^="arr_buy_unit[]"]').each(function(index){
            formData.append('arr_buy_unit[]',($(this).is(':checked') ? $(this).val() : ''));
        });
        $('input[name^="arr_default"]').each(function(index){
            formData.append('arr_default[]',($(this).is(':checked') ? $(this).val() : ''));
        });
        $('input[name^="arr_is_affect_qty[]"]').each(function(index){
            formData.append('arr_is_affect_qty[]',($(this).is(':checked') ? $(this).val() : ''));
        });

        if(passedSameUnit){
            if(passed){
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
                            $('#parent_id').empty();

                            $.each(response.data, function(i, val) {
                                $('#parent_id').append(val);
                            });

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
            }else{
                swal({
                    title: 'Ups!',
                    text: 'Mohon maaf, satuan konversi tidak boleh kosong.',
                    icon: 'error'
                });
            }
        }else{
            swal({
                title: 'Ups!',
                text: 'Mohon maaf, satuan konversi tidak boleh ada yang sama.',
                icon: 'error'
            });
        }
    }

    function saveShading(){

        var formData = new FormData($('#form_data_shading')[0]);

        $.ajax({
            url: '{{ Request::url() }}/create_shading',
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
                $('#validation_alert_shading').hide();
                $('#validation_alert_shading').html('');
                loadingOpen('.modal-content');
            },
            success: function(response) {
                loadingClose('.modal-content');
                if(response.status == 200) {
                    refreshShading($('#tempShading').val());
                    M.toast({
                        html: response.message
                    });
                    $('#shading_code').val('');
                } else if(response.status == 422) {
                    $('#validation_alert_shading').show();
                    $('.modal-content').scrollTop(0);

                    swal({
                        title: 'Ups! Validation',
                        text: 'Check your form.',
                        icon: 'warning'
                    });

                    $.each(response.error, function(i, val) {
                        $.each(val, function(i, val) {
                            $('#validation_alert_shading').append(`
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

    function successImport(){
        loadDataTable();
        $('#modal2').modal('close');
    }

    function filterUnit(){
        if($('#adaUnit').val()==1){
            $('#adaUnit').val('');
        }else{
            $('#adaUnit').val('{{$itemex}}');
        }

        $('#adaSh').val('');

        loadDataTable();
    }

    function filterShade(){
        if($('#adaSh').val()==1){
            $('#adaSh').val('');
        }else{
            $('#adaSh').val('{{$itemsh}}');
        }

        $('#adaUnit').val('');

        loadDataTable();
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
                arrCode = [];
                arrName = [];

                $('#temp').val(id);
                $('#code').val(response.code);
                $('#name').val(response.name);
                $('#note').val(response.note);
                $('#item_group_id').val(response.item_group_id).trigger('change');
                $('#uom_unit').val(response.uom_unit_id).trigger('change');
                $('#warehouse_id').val(response.warehouses).trigger('change');
                $('#tolerance_gr').val(response.tolerance_gr);
                $('.stock-unit').text(response.uom_code);

                if(response.is_inventory_item == '1'){
                    $('#is_inventory_item').prop( "checked", true);
                }else{
                    $('#is_inventory_item').prop( "checked", false);
                }

                if(response.is_quality_check == '1'){
                    $('#is_quality_check').prop( "checked", true);
                }else{
                    $('#is_quality_check').prop( "checked", false);
                }
                if(response.supplier_name){
                    $('#supplier_id').empty().append(`
                        <option value="` + response.supplier_id + `" data-code="` + response.supplier_code + `" data-name="` + response.supplier_name + `">`+ response.supplier_code +`-`+ response.supplier_name + `</option>
                    `);
                }

                $('#document-preview').html(''); // clear previous

                if (response.document_url) {
                    const fileURL = response.document_url;
                    const fileName = fileURL.split('/').pop();
                    const fileExt = fileName.split('.').pop().toLowerCase();

                    if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt)) {
                        $('#document-preview').append(`<img src="${fileURL}" alt="Preview Gambar" style="max-width:100%; max-height:400px;">`);
                    } else if (fileExt === 'pdf') {
                        $('#document-preview').append(`<iframe src="${fileURL}" width="100%" height="500px" style="border:1px solid #ccc;"></iframe>`);
                    } else {
                        $('#document-preview').append(`<p>Preview tidak tersedia untuk file ini. <br><strong><a href="${fileURL}" target="_blank">${fileName}</a></strong></p>`);
                    }
                }


                if(response.is_sales_item == '1'){
                    $('#is_sales_item').trigger('click');
                    if(response.type_name){
                        $('#type_id').empty().append(`
                            <option value="` + response.type_id + `" data-code="` + response.type_code + `" data-name="` + response.type_name_real + `">` + response.type_name + `</option>
                        `);
                    }
                    if(response.size_name){
                        $('#size_id').empty().append(`
                            <option value="` + response.size_id + `" data-code="` + response.size_code + `" data-name="` + response.size_name_real + `">` + response.size_name + `</option>
                        `);
                    }
                    if(response.variety_name){
                        $('#variety_id').empty().append(`
                            <option value="` + response.variety_id + `" data-code="` + response.variety_code + `" data-name="` + response.variety_name_real + `">` + response.variety_name + `</option>
                        `);
                    }
                    if(response.pattern_name){
                        $('#pattern_id').empty().append(`
                            <option value="` + response.pattern_id + `" data-code="` + response.pattern_code + `" data-name="` + response.pattern_name_real + `">` + response.pattern_name + `</option>
                        `);
                    }
                    if(response.pallet_name){
                        $('#pallet_id').empty().append(`
                            <option value="` + response.pallet_id + `" data-code="` + response.pallet_code + `" data-name="` + response.pallet_name_real + `">` + response.pallet_name + `</option>
                        `);
                    }
                    if(response.grade_name){
                        $('#grade_id').empty().append(`
                            <option value="` + response.grade_id + `" data-code="` + response.grade_code + `" data-name="` + response.grade_name_real + `">` + response.grade_name + `</option>
                        `);
                    }
                    if(response.brand_name){
                        $('#brand_id').empty().append(`
                            <option value="` + response.brand_id + `" data-code="` + response.brand_code + `" data-name="` + response.brand_name_real + `">` + response.brand_name + `</option>
                        `);
                    }
                }else{
                    $('#is_sales_item').prop( "checked", false);
                }

                if(response.is_purchase_item == '1'){
                    $('#is_purchase_item').prop( "checked", true);
                }else{
                    $('#is_purchase_item').prop( "checked", false);
                }


                if(response.status == '1'){
                    $('#status').prop( "checked", true);
                }else{
                    $('#status').prop( "checked", false);
                }


                if(response.units.length > 0){
                    $('#body-unit').empty();

                    $.each(response.units, function(i, val) {
                        let count = makeid(10);
                        $('#body-unit').append(`
                            <tr class="row_unit">
                                <td class="unit-inputs">
                                    <select class="select2 browser-default" id="arr_item_conversion` + count + `" name="arr_item_conversion[]">

                                    </select>
                                </td>
                                <td>
                                    <input name="arr_qty[]" onfocus="emptyThis(this);" class="browser-default" type="text" value="`+val.qty_conversion+`" onkeyup="formatRupiah(this);" style="text-align:right;width:100%;" id="rowQty`+ count +`">
                                </td>
                                <td class="center-align unit-inputs">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-unit" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                        $('select[name="arr_unit[]"]').select2({
                            dropdownAutoWidth: true,
                            width: '100%',
                        });
                        $('#arr_item_conversion' + count).empty().append(`
                            <option value="` + val.item_child_id + `" >` + val.item_child_name + `</option>
                        `);
                    });
                }

                if(response.used){
                    $('#code,#name').prop('readonly',true);
                    $('.unit-inputs').css('pointer-events','none');
                }

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

    function destroyShading(id,item,element){
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
                    url: '{{ Request::url() }}/destroy_shading',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { id : id },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        loadingOpen('.modal-content');
                    },
                    success: function(response) {
                        loadingClose('.modal-content');
                        refreshShading(item);
                        M.toast({
                            html: response.message
                        });
                        $(element).parent().remove();
                    },
                    error: function() {
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

        $.map(selected, function (item) {
            arr_id_temp.push(item);
        });

        if(arr_id_temp.length > 0){
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
        }else{
            swal({
                title: 'Ups!',
                text: 'Silahkan pilih item untuk cetak rekap.',
                icon: 'warning'
            });
        }
    }

    function printBarcode(){

        var arr_id_temp = [];

        $.map(selected, function (item) {
            arr_id_temp.push(item);
        });

        if(arr_id_temp.length > 0){
            $.ajax({
                url: '{{ Request::url() }}/print_barcode',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    arr_id: arr_id_temp,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('#main');
                },
                success: function(response) {
                    loadingClose('#main');
                    window.open(response.message, '_blank');
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
        }else{
            swal({
                title: 'Ups!',
                text: 'Silahkan pilih item untuk cetak barcode.',
                icon: 'warning'
            });
        }
    }

    function exportExcel(){
        var search = window.table.search();
        var status = $('#filter_status').val();
        var type = $('#filter_type').val();
        var group = $('#filter_group').val();
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status + "&type=" + type+ "&group=" + group;
    }

</script>
