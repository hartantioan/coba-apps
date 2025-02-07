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
                                                <label for="filter_broker" style="font-size:1rem;">Filter Broker:</label>
                                                <div class="input-field col s12">
                                                    <select class="select2 browser-default" multiple="multiple" id="filter_broker" name="filter_broker" onchange="loadDataTable()">
                                                        <option value="" disabled>{{ __('translations.all') }}</option>
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
                                            <!-- Filter Icon -->
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col s12">
                                            <!-- Card Alert -->
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
                                                        <th>{{ __('translations.name') }}</th>
                                                        <th>Tipe</th>
                                                        <th>{{ __('translations.code') }}</th>
                                                        <th>Cabang Broker</th>
                                                        <th>Broker</th>
                                                        <th>Approval</th>
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

{{-- Modal 3 --}}
<div id="modal3" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;max-width:90%;min-width:90%;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>{{-- Title --}}</h4>
                <div class="row">
                    <div class="col s12">
                        <form class="row" id="form_" onsubmit="return false;">
                            <div class="col s12">
                                
                            </div>
                            <div class="col s12">
                                <div class="row">
                                    <div class="input-field col m2 s2">
                                    </div>
                                    <div class="input-field col m2 s2">
                                    </div>
                                    <div class="input-field col m2 s2">
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

{{-- Modal Import Excel --}}
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
                        <h6>Anda bisa menggunakan fitur upload dokumen excel. Silahkan klik <a href="{{-- {{ asset(Storage::url('format_imports/format_copas_ap_invoice_2.xlsx')) }} --}}{{ Request::url() }}/get_import_excel" target="_blank">disini</a> untuk mengunduh.</h6>
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

{{-- Modal create/update --}}
<div id="modal1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;">
    <div class="modal-content" style="overflow-x: hidden !important;">
        <div class="row">
            <div class="col s12">
                <h4>{{ __('translations.add') }} {{ $title }}</h4>
                {{-- <div class="card-alert card blue">
                    <div class="card-content white-text">
                        <p>Info : Untuk penambahan BP Supplier & Ekspedisi dibuka akses hanya pak Sandi.</p>
                    </div>
                </div> --}}
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">

                        <div class="input-field col s12 m3">
                            <b id="type">-</b>
                            <label class="active" for="type">Tipe Partner Bisnis</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <input type="hidden" id="temp" name="temp">
                            <b id="name">-</b>
                            <label class="active" for="name">{{ __('translations.name') }}</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <b id="code_customer_mitra">-</b>
                            <label class="active" for="code_customer_mitra">Kode Customer Mitra</label>
                        </div>
                        <div class="input-field col s12 m3 customer_inputs">
                            <b id="type_body">-</b>
                            <label class="active" for="type_body">Tipe Perusahaan Customer</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <b id="phone">-</b>
                            <label class="active" for="phone">{{ __('translations.phone_number') }}</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <b id="email">-</b>
                            <label class="active" for="email">Email</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <b id="address">-</b>
                            <label class="active" for="address">{{ __('translations.address') }}</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <b id="id_card">-</b>
                            <label class="active" for="id_card">No KTP / Identitas</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <b id="id_card_address">-</b>
                            <label class="active" for="id_card_address">Alamat KTP / Identitas</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <b id="sales_payment_type">-</b>
                            <label class="active" for="sales_payment_type">Tipe Pembayaran</label>
                        </div>
                        <div class="input-field col s12 m3 other_inputs" style="">
                            <b id="pic">-</b>
                            <label class="active" for="pic">PIC</label>
                        </div>
                        <div class="input-field col s12 m3 other_inputs" style="display:none;">
                            <b id="pic_position">-</b>
                            <label class="active" for="pic_position">Jabatan PIC</label>
                        </div>
                        <div class="input-field col s12 m3 other_inputs" style="display:none;>
                            <b id="pic_no">-</b>
                            <label class="active" for="pic_no">Kontak PIC</label>
                        </div>
                        <div class="input-field col s12 m3 other_inputs" style="display:none;">
                            <b id="pic_finance">-</b>
                            <label class="active" for="pic_finance">PIC Finance</label>
                        </div>
                        <div class="input-field col s12 m3 other_inputs" style="display:none;">
                            <b id="no_pic_finance">-</b>
                            <label class="active" for="no_pic_finance">No PIC Finance</label>
                        </div>
                        <div class="input-field col s12 m3 other_inputs" style="display:none;">
                            <b id="office_no">-</b>
                            <label class="active" for="office_no">Kontak Kantor</label>
                        </div>
                        <br>
                        <div class="input-field col s12 m3">
                            <b id="limit_credit">-</b>
                            <label class="active" for="limit_credit">Limit Kredit</label>
                        </div>
                        <div class="input-field col s12 m3 other_inputs" style="display:none;">
                            <b id="top">-</b>
                            <label class="active" for="top">TOP (Tempo Pembayaran)</label>
                        </div>
                        <div class="input-field col s12 m3 other_inputs" style="display:none;">
                            <b id="top_internal">-</b>
                            <label class="active" for="top_internal">TOP Internal</label>
                        </div>
                        <br>
                        <div class="input-field col s12 m3">
                            <b id="province_id">-</b>
                            <label class="active" for="province_id">{{ __('translations.province') }} PIC</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <b id="city_id">-</b>
                            <label class="active" for="city_id">{{ __('translations.city') }} PIC</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <b id="district_id">-</b>
                            <label class="active" for="district_id">{{ __('translations.district') }} PIC</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <b id="country_id">-</b>
                            <label class="active" for="country_id">Negara Asal</label>
                        </div>
                    
                        <div class="col s12 mt-1">
                            <div class="input-field col s12 m3">
                                <div class="switch mb-1">
                                    <label for="status">{{ __('translations.status') }}</label>
                                    <label>
                                        {{ __('translations.non_active') }}
                                        <input checked type="checkbox" id="status" name="status" value="1">
                                        <span class="lever" disabled></span>
                                        {{ __('translations.active') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col s12 mt-3">
                            <ul class="tabs">
                                <li class="tab col m2"><a class="active" href="#dataform">Alamat Penagihan</a></li>
                                <li class="tab col m3" style=""><a href="#destinationform">Alamat Pengiriman Barang</a></li>
                                <li class="tab col m3" style=""><a href="#destinationdocform">Alamat Pengiriman Dokumen</a></li>
                            </ul>
                            <div id="dataform" class="col s12 active" style="overflow:auto;min-width:100%;">
                                <h5 class="center">Daftar Alamat Penagihan</h5>
                                <p class="mt-2 mb-2">
                                    <table class="bordered" style="min-width:100%;">
                                        <thead>
                                            <tr>
                                                <th class="center">Default</th>
                                                <th class="center">Nama (Sesuai NPWP)</th>
                                                <th class="center">{{ __('translations.note') }}</th>
                                                <th class="center">NPWP</th>
                                                <th class="center">{{ __('translations.address') }}</th>
                                                <th class="center">Negara</th>
                                                <th class="center">{{ __('translations.province') }}</th>
                                                <th class="center">{{ __('translations.city') }}</th>
                                                <th class="center">{{ __('translations.district') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-info">
                                        </tbody>
                                    </table>
                                </p>
                            </div>
                            <div id="destinationform" class="col s12" style="overflow:auto;min-width:100%;">
                                <h5 class="center">Daftar Alamat Pengiriman Barang</h5>
                                <p class="mt-2 mb-2">
                                    <table class="bordered" style="min-width:100%;">
                                        <thead>
                                            <tr>
                                                <th class="center">Default</th>
                                                <th class="center">{{ __('translations.address') }}</th>
                                                <th class="center">Negara</th>
                                                <th class="center">{{ __('translations.province') }}</th>
                                                <th class="center">{{ __('translations.city') }}</th>
                                                <th class="center">{{ __('translations.district') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-destination">
                                        </tbody>
                                    </table>
                                </p>
                            </div>
                            <div id="destinationdocform" class="col s12" style="overflow:auto;min-width:100%;">
                                <h5 class="center">Daftar Alamat Pengiriman Dokumen</h5>
                                <p class="mt-2 mb-2">
                                    <table class="bordered" style="min-width:100%;">
                                        <thead>
                                            <tr>
                                                <th class="center">Default</th>
                                                <th class="center">{{ __('translations.address') }}</th>
                                                <th class="center">Negara</th>
                                                <th class="center">{{ __('translations.province') }}</th>
                                                <th class="center">{{ __('translations.city') }}</th>
                                                <th class="center">{{ __('translations.district') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-destination-doc">
                                        </tbody>
                                    </table>
                                </p>
                            </div>
                        </div>
                        <div class="col s12 mt-3">
                            <button class="btn waves-effect waves-light right submit" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
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

{{-- Modal rowDetail --}}
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

{{-- Modal Relation --}}
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

<div hidden style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
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
                M.updateTextFields();
                
                $('#temp').val('');
                $('#code').text('-');
                $('#type').text('-');
                $('#post_date').text('-');
                $('#valid_date').text('-');
                $('#document_no').text('-');
                $('#branch_code').text('-');
                $('#type_delivery').text('-');
                $('#delivery_date').text('-');
                $('#delivery_schedule').text('-');
                $('#delivery_address').text('--');
                $('#delivery_province').text('-');
                $('#delivery_city').text('-');
                $('#delivery_district').text('-');
                $('#payment_type').text('-');
                $('#dp_type').text('-');
                $('#percent_dp').text('-');
                $('#total').text('-');
                $('#tax').text('-');
                $('#grandtotal').text('-');
                window.onbeforeunload = function() {
                    return null;
                };
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

        $("#filter_broker").select2({
            placeholder: "Kosong untuk semua Broker.",
            dropdownAutoWidth: true,
            width: '100%',
        });

        /*
        select2ServerSide('#country_id', '{{ url("admin/select2/country") }}');
        select2ServerSide('#province_id', '{{ url("admin/select2/province") }}');
        select2ServerSide('#city_id', '{{ url("admin/select2/city") }}');
        
        $('#district_id').select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/district_by_city") }}',
                type: 'GET',
                dataType: 'JSON',
                data: function(params) {
                    return {
                        search: params.term,
                        city: $("#city_id").select2().find(":selected").data("code"),
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.items
                    }
                }
            }
        });
        */

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

        select2ServerSide('#filter_broker', '{{ url("admin/select2/broker") }}');
        
        $(document).on('select2:close', '.select2', function (e) {
            var evt = "scroll.select2";
            $(e.target).parents().off(evt);
            $(window).off(evt);
        });
    });

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
            "scrollCollapse"  : true,
            "scrollY"         : '400px',
            "responsive"      : false,
            "scrollX"         : true,
            "stateSave"       : true,
            "serverSide"      : true,
            "deferRender"     : true,
            "destroy"         : true,
            "iDisplayInLength": 7,
            "order"           : [[0, 'desc']],
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    status    : $('#filter_status').val(),
                    'broker[]'  : $('#filter_broker').val(),
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
                { name: 'name', className: '' },
                { name: 'type', className: '' },
                { name: 'code', className: '' },
                { name: 'branch_code', className: '' },
                { name: 'mitra', className: '' },
                { name: 'status_approval', searchable: false, orderable: false, className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ],
            dom    : 'Blfrtip',
            buttons: [
                'columnsToggle',
                'selectAll',
                'selectNone'
            ],
            "language": {
                "lengthMenu"  : "Menampilkan _MENU_ data per halaman",
                "zeroRecords" : "Data tidak ditemukan / kosong",
                "info"        : "Menampilkan halaman _PAGE_ / _PAGES_ dari total _TOTAL_ data",
                "infoEmpty"   : "Data tidak ditemukan / kosong",
                "infoFiltered": "(disaring dari _MAX_ total data)",
                "search"      : "Cari",
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

/*
    function save(){
        var formData = new FormData($('#form_data')[0]), passed = true, passedSameUnit = true;
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
    }
    */

    function changeMode(element){
        if($(element).val() == '1'){
            $('.other_inputs').hide();
            $('.customer_inputs').hide();
            $('.employee_inputs').show();
        }else{
            $('.other_inputs').show();
            $('.employee_inputs').hide();
            if($(element).val() == '2' || $(element).val() == '5' ){
                $('.customer_inputs').show();
            }else{
                $('.customer_inputs').hide();
            }
        }
    }

    /*
    function getCity(){
        $('#city_id,#district_id').empty().append(`
            <option value="">--{{ __('translations.select') }}--</option>
        `);
        if($('#province_id').val()){
            $.each($('#province_id').select2('data')[0].cities, function(i, value) {
                $('#city_id').append(`
                    <option value="` + value.id + `" data-code="` + value.code + `">` + value.code + ` - `  + value.name + `</option>
                `);
            });
        }
    }

    function getDistrict(){
        $('#district_id').empty().append(`
            <option value="">--{{ __('translations.select') }}--</option>
        `);
        if($('#city_id').val()){
            console.log($("#city_id").select2().find(":selected").data("district"));
            $.each($("#city_id").select2().find(":selected").data("district"), function(i, value) {
                $('#district_id').append(`
                    <option value="` + value.id + `">` + value.code + ` - ` + value.name + `</option>
                `);
            });
        }
    }
    */

    function save(){
		swal({
            title     : "Apakah anda yakin ingin simpan?",
            text      : "Silahkan cek kembali form, dan jika sudah yakin maka lanjutkan!",
            icon      : 'warning',
            dangerMode: true,
            buttons   : {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                var formData = new FormData($('#form_data')[0]);

                $.ajax({
                    url        : '{{ Request::url() }}/create',
                    type       : 'POST',
                    dataType   : 'JSON',
                    data       : formData,
                    contentType: false,
                    processData: false,
                    cache      : true,
                    headers    : {
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
                            window.onbeforeunload = function() {
                                return null;
                            };
                            window.location = "{{ URL::to('/admin/master_data/master_organization/user?mitra_customer_code=') }}" + $('#temp').val();
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

    function successImport(){
        loadDataTable();
        $('#modal2').modal('close');
    }

    function show(code){
        $.ajax({
            url     : '{{ Request::url() }}/show',
            type    : 'POST',
            dataType: 'JSON',
            data: {
                code: code
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
                console.log(response);
                $('.customer_inputs').show();
                
                $('#type').text('PELANGGAN');
                $('#temp').val(response.code);
                $('#code_customer_mitra').text(response.code);
                $('#name').text(response.name);
                $("#type_body").text(response.type);
                $('#xxx').val(response.branch_code);
                $('#phone').text(response.phone);
                $('#email').text(response.email);
                $('#address').text(response.address);
                $('#id_card').text(response.id_card);
                $('#pic').text(response.pic_name);
                $('#id_card_address').text(response.pic_address);
                $('#limit_credit').text(response.limit_credit);
                $('#top').text(response.top);
                $('#top_internal').text(response.top_internal);
                
                $('#province_id').text(response.province_name);
                $('#city_id').text(response.city_name);
                $('#district_id').text(response.district_name);
                $('#country_id').text(response.country_name);

                /*
                if((response.type).toUpperCase() == 'PERORANGAN'){ $('#type_body').val("3").change(); }
                $('#note').val(response.note);
                
                */

                if(response.datas.length > 0){
                    $.each(response.datas, function(i, val) {
                        $('#body-info').html(`
                            <tr class="row_destination">
                                <td class="center">
                                    <label>
                                        <input class="with-gap" name="check_destination" type="checkbox" value="` + i + `" ` + (val.is_default == '1' ? 'checked' : '') + ` disabled>
                                        <span>Default</span>
                                    </label>
                                </td>
                                <td class="center">
                                    <label> `+val.name+`</label>
                                </td>
                                <td class="center">
                                    <label> `+val.notes+`</label>
                                </td>
                                <td class="center">
                                    <label> `+val.npwp+`</label>
                                </td>
                                <td class="center">
                                    <label> `+val.address+`</label>
                                </td>
                                <td class="center">
                                    <label> `+val.country_name+`</label>
                                </td>
                                <td class="center">
                                    <label> `+val.province_name+`</label>
                                </td>
                                <td class="center">
                                    <label> `+val.city_name+`</label>
                                </td>
                                <td class="center">
                                    <label> `+val.district_name+`</label>
                                </td>
                            </tr>
                        `);
                    });
                }

                if(response.destinations.length > 0){
                    $.each(response.datas, function(i, val) {
                        $('#body-destination').html(`
                            <tr class="row_destination">
                                <td class="center">
                                    <label>
                                        <input class="with-gap" name="check_destination" type="checkbox" value="` + i + `" ` + (val.is_default == '1' ? 'checked' : '') + ` disabled>
                                        <span>Default</span>
                                    </label>
                                </td>
                                <td class="center">
                                    <label> `+val.address+`</label>
                                </td>
                                <td class="center">
                                    <label> `+val.country_name+`</label>
                                </td>
                                <td class="center">
                                    <label> `+val.province_name+`</label>
                                </td>
                                <td class="center">
                                    <label> `+val.city_name+`</label>
                                </td>
                                <td class="center">
                                    <label> `+val.district_name+`</label>
                                </td>
                            </tr>
                        `);
                    });
                }

                if(response.documents.length > 0){
                    $.each(response.datas, function(i, val) {
                        $('#body-destination-doc').html(`
                            <tr class="row_destination">
                                <td class="center">
                                    <label>
                                        <input class="with-gap" name="check_destination" type="checkbox" value="` + i + `" ` + (val.is_default == '1' ? 'checked' : '') + ` disabled>
                                        <span>Default</span>
                                    </label>
                                </td>
                                <td class="center">
                                    <label> `+val.address+`</label>
                                </td>
                                <td class="center">
                                    <label> `+val.country_name+`</label>
                                </td>
                                <td class="center">
                                    <label> `+val.province_name+`</label>
                                </td>
                                <td class="center">
                                    <label> `+val.city_name+`</label>
                                </td>
                                <td class="center">
                                    <label> `+val.district_name+`</label>
                                </td>
                            </tr>
                        `);
                    });
                }
               
               if(response.status == '1'){
                    $('#status').prop( "checked", true);
                }else{
                    $('#status').prop( "checked", false);
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
                    text : 'Check your internet connection.',
                    icon : 'error'
                });
            }
        });
    }

    function destroy(id){
        swal({
            title     : "Apakah anda yakin?",
            text      : "Anda tidak bisa mengembalikan data yang terhapus!",
            icon      : 'warning',
            dangerMode: true,
            buttons   : {
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

        var search = window.table.search(), status = $('#filter_status').val(), broker = $('#filter_broker').val(), company = $('#filter_company').val(), account = $('#filter_account').val();
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
                    printService.submit({
                        'type': 'INVOICE',
                        'url': response.message
                    })
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
        var broker = $('#filter_broker').val();
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status + "&broker=" + broker;
    }

</script>
