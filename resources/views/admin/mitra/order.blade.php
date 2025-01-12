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

    .select2-container {
        height:3.6rem !important;
    }

    .select-wrapper {
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
                                                <label for="filter_type" style="font-size:1rem;">Tipe Penjualan :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_type" onchange="loadDataTable()">
                                                        <option value="">{{ __('translations.all') }}</option>
                                                        <option value="1">Proyek</option>
                                                        <option value="2">Retail</option>
                                                        <option value="3">Khusus</option>
                                                        <option value="4">Sample</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_payment" style="font-size:1rem;">Tipe Pembayaran :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_payment" onchange="loadDataTable()">
                                                        <option value="">{{ __('translations.all') }}</option>
                                                        <option value="1">DP</option>
                                                        <option value="2">Credit</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_delivery" style="font-size:1rem;">Tipe Pengiriman :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_delivery" onchange="loadDataTable()">
                                                        <option value="">{{ __('translations.all') }}</option>
                                                        <option value="1">Loco</option>
                                                        <option value="2">Franco</option>
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
                                            <div class="card-alert card green">
                                                <div class="card-content white-text">
                                                    <p>Info : -</p>
                                                </div>
                                            </div>
                                            <div id="datatable_buttons"></div>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">{{ __('translations.refresh') }}</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <a class='dropdown-trigger btn btn-small waves-effect waves-light breadcrumbs-btn right mr-2' href='#' data-target='dropdown1'>

                                                <i class="material-icons hide-on-med-and-up">view_headline</i>
                                                <span class="hide-on-small-onl">Export</span>
                                                <i class="material-icons right">view_headline</i>
                                            </a>

                                            <ul id='dropdown1' class='dropdown-content'>
                                                <li><a href="javascript:void(0);" onclick="exportExcel();">
                                                    <i class="material-icons">view_headline</i>
                                                    <i class="material-icons hide-on-med-and-up">view_headline</i>Export
                                                    </a>
                                                </li>
                                            </ul>
                                            <table id="datatable_serverside">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{ __('translations.code') }}</th>
                                                        <th>Petugas</th>
                                                        <th>{{ __('translations.customer') }}</th>
                                                        <th>{{ __('translations.type') }}</th>
                                                        <th>Tgl.Post</th>
                                                        <th>Valid Hingga</th>
                                                        <th>No.Dokumen</th>
                                                        <th>Kode Branch</th>
                                                        <th>Tipe Pengiriman</th>
                                                        <th>Tgl.Kirim</th>
                                                        <th>Jadwal Pengiriman</th>
                                                        <th>Alamat Tujuan</th>
                                                        <th>Provinsi Tujuan</th>
                                                        <th>Kota Tujuan</th>
                                                        <th>Kecamatan Tujuan</th>
                                                        <th>Tipe Pembayaran</th>
                                                        <th>Tipe DP</th>
                                                        <th>Catatan</th>
                                                        <th>{{ __('translations.total') }}</th>
                                                        <th>{{ __('translations.tax') }}</th>
                                                        <th>{{ __('translations.grandtotal') }}</th>
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
                                    <legend>1. Info Utama</legend>
                                    <div class="input-field col m2 s12 step1">
                                        <input type="hidden" id="temp" name="temp">
                                        <input id="code" name="code" type="text" value="{{ $newcode }}" readonly>
                                        <label class="active" for="code">No. Dokumen</label>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col s12">
                                <fieldset>
                                    <legend>2. Pengiriman</legend>
                                    
                                </fieldset>
                            </div>
                            <div class="col s12">
                                <fieldset>
                                    <legend>3. Pembayaran</legend>
                                    
                                </fieldset>
                            </div>
                            <div class="col s12">
                                <fieldset>
                                    <legend>4. Lain-lain</legend>
                                    
                                </fieldset>
                            </div>
                            <div class="col s12 step30">
                                <fieldset style="min-width: 100%;">
                                    <legend>5. Produk Detail</legend>
                                    <div class="col m12 s12" style="overflow:auto;width:100% !important;" id="table-item">
                                        <p class="mt-2 mb-2">
                                            <table class="bordered" style="width:3600px;" id="table-detail">
                                                <thead>
                                                    <tr>
                                                        <th class="center">No.</th>
                                                        <th class="center">Kode Item</th>
                                                        <th class="center">Nama Item</th>
                                                        <th class="center">Qty Pesan</th>
                                                        <th class="center">Satuan Stok</th>
                                                        <th class="center">Harga Satuan</th>
                                                        <th class="center">% PPN</th>
                                                        <th class="center">Total</th>
                                                        <th class="center">PPN</th>
                                                        <th class="center">Grandtotal</th>
                                                        <th class="center">Keterangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="body-item">
                                                    <tr id="last-row-item">
                                                        <td colspan="11">
                                                            Silahkan tambahkan baris ...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </p>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="input-field col m4 s12 step32">
                                
                                <label class="active" for="note_internal">Keterangan Internal</label>
                            </div>
                            <div class="input-field col m4 s12 step33">

                                <label class="active" for="note_external">Keterangan Eksternal</label>
                            </div>
                            <div class="input-field col m4 s12">

                            </div>
                            <div class="input-field col m4 s12 step34">
                                <table width="100%" class="bordered">
                                    <thead>
                                        <tr>
                                            <td>Total</td>
                                            <td class="right-align" id="total">
                                                0,00
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>PPN</td>
                                            <td class="right-align" id="tax">
                                                0,00
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Grandtotal</td>
                                            <td class="right-align" id="grandtotal">
                                                0,00
                                            </td>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light purple btn-panduan" onclick="startIntro();">Panduan <i class="material-icons right">help_outline</i></button>
        <button class="btn waves-effect waves-light right submit step35" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
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

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-amber-amber gradient-shadow modal-trigger tooltipped"  data-position="top" data-tooltip="Range Printing" href="#modal5">
        <i class="material-icons">view_comfy</i>
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
    var city = [], district = [];

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
                
            },
            onOpenEnd: function(modal, trigger) {
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                window.onbeforeunload = function() {
                    if($('.data-used').length > 0){
                        $('.data-used').trigger('click');
                    }
                    return 'You will lose all changes made since your last save';
                };
                if(!$('#temp').val()){

                }
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                $('.row_item').each(function(){
                    $(this).remove();
                });
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
    });

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
                type: 'POST',
                data: {
                    'status[]' : $('#filter_status').val(),
                    type : $('#filter_type').val(),
                    delivery_type : $('#filter_delivery').val(),
                    payment_type : $('#filter_payment').val(),
                    start_date : $('#start_date').val(),
                    finish_date : $('#finish_date').val(),
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
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
                { name: 'user_id', className: '' },
                { name: 'account_id', className: '' },
                { name: 'type', className: '' },
                { name: 'post_date', className: '' },
                { name: 'valid_date', className: '' },
                { name: 'document_no', className: '' },
                { name: 'branch_code', className: '' },
                { name: 'delivery_type', className: '' },
                { name: 'delivery_date', className: '' },
                { name: 'delivery_schedule', className: '' },
                { name: 'destination_address', className: '' },
                { name: 'province_id', className: '' },
                { name: 'city_id', className: '' },
                { name: 'district_id', className: '' },
                { name: 'payment_type', className: '' },
                { name: 'dp_type', className: '' },
                { name: 'note', className: '' },
                { name: 'total', className: 'right-align' },
                { name: 'tax', className: 'right-align' },
                { name: 'grandtotal', className: 'right-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'by', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
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
            $('#delivery_date').attr('min',$('#post_date').val());
        }
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
                var formData = new FormData($('#form_data')[0]), passed = true;

                formData.delete("arr_place[]");
                formData.delete("arr_tax_nominal[]");
                formData.delete("arr_grandtotal[]");
                formData.delete("arr_item[]");
                formData.delete("arr_unit[]");
                formData.delete("arr_qty[]");
                formData.delete("arr_qty_uom[]");
                formData.delete("arr_price_list[]");
                formData.delete("arr_price_delivery[]");
                formData.delete("arr_price_type_bp[]");
                formData.delete("arr_price[]");
                formData.delete("arr_tax[]");
                formData.delete("arr_is_include_tax[]");
                formData.delete("arr_disc1[]");
                formData.delete("arr_disc2[]");
                formData.delete("arr_disc3[]");
                formData.delete("arr_final_price[]");
                formData.delete("arr_total[]");
                formData.delete("arr_note[]");

                if($('select[name^="arr_place[]"]').length > 0){
                    $('select[name^="arr_place[]"]').each(function(index){
                        formData.append('arr_place[]',$(this).val());
                        formData.append('arr_tax_nominal[]',$('input[name^="arr_tax_nominal"]').eq(index).val());
                        formData.append('arr_grandtotal[]',$('input[name^="arr_grandtotal"]').eq(index).val());
                        formData.append('arr_item[]',$('select[name^="arr_item"]').eq(index).val());
                        formData.append('arr_unit[]',($('select[name^="arr_unit[]"]').eq(index).val() ? $('select[name^="arr_unit[]"]').eq(index).val() : '' ));
                        formData.append('arr_qty[]',$('input[name^="arr_qty[]"]').eq(index).val());
                        formData.append('arr_qty_uom[]',$('input[name^="arr_qty_uom[]"]').eq(index).val());
                        formData.append('arr_price[]',$('input[name^="arr_price[]"]').eq(index).val());
                        formData.append('arr_price_list[]',$('input[name^="arr_price_list[]"]').eq(index).val());
                        formData.append('arr_price_delivery[]',$('input[name^="arr_price_delivery[]"]').eq(index).val());
                        formData.append('arr_price_type_bp[]',$('input[name^="arr_price_type_bp[]"]').eq(index).val());

                        formData.append('arr_tax[]',$('select[name^="arr_tax"]').eq(index).val());
                        formData.append('arr_tax_id[]',$('option:selected','select[name^="arr_tax"]').eq(index).data('id'));
                        formData.append('arr_is_include_tax[]',($('input[name^="arr_is_include_tax"]').eq(index).is(':checked') ? '1' : '0'));
                        formData.append('arr_disc1[]',$('input[name^="arr_disc1"]').eq(index).val());
                        formData.append('arr_disc2[]',$('input[name^="arr_disc2"]').eq(index).val());
                        formData.append('arr_disc3[]',$('input[name^="arr_disc3"]').eq(index).val());

                        formData.append('arr_final_price[]',$('input[name^="arr_final_price"]').eq(index).val());
                        formData.append('arr_total[]',$('input[name^="arr_total"]').eq(index).val());
                        formData.append('arr_note[]',$('input[name^="arr_note[]"]').eq(index).val());
                        if(!$('select[name^="arr_item"]').eq(index).val()){
                            passed = false;
                        }
                        if(!$(this).val()){
                            passed = false;
                        }
                        if(!$('select[name^="arr_unit"]').eq(index).val()){
                            passed = false;
                        }
                    });
                }else{
                    passed = false;
                }

                if(passed == true){
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
                }else{
                    swal({
                        title: 'Ups!',
                        text: 'Item / stok / plant / satuan tidak boleh kosong.',
                        icon: 'warning'
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
                loadingClose('#main');
                resetTerm();
                $('#modal1').modal('open');
                $('#temp').val(id);
                $('#code_place_id').val(response.code_place_id).formSelect();
                $('#code').val(response.code);
                $('#account_id').empty();
                $('#account_id').append(`
                    <option value="` + response.account_id + `">` + response.account_name + `</option>
                `);
                $('#limit').text(response.deposit);
                $('#company_id').val(response.company_id).formSelect();
                $('#delivery_schedule').val(response.delivery_schedule).formSelect();
                $('#type').val(response.type).formSelect();
                $('#post_date').val(response.post_date);
                $('#valid_date').val(response.valid_date);
                $('#document_no').val(response.document_no);
                $('#type_delivery').val(response.type_delivery).formSelect();
                if(response.sender_name){
                    $('#sender_id').empty().append(`<option value="` + response.sender_id + `">` + response.sender_name + `</option>`);
                }
                $('#delivery_date').val(response.delivery_date);
                $('#delivery_date').attr('min',response.post_date);
                if(response.transportation_name){
                    $('#transportation_id').empty().append(`
                        <option value="` + response.transportation_id + `">` + response.transportation_name + `</option>
                    `);
                }
                if(response.outlet_name){
                    $('#outlet_id').empty().append(`
                        <option value="` + response.outlet_id + `">` + response.outlet_name + `</option>
                    `);
                }
                $('#billing_address').empty();
                $.each(response.user_data, function(i, val) {
                    $('#billing_address').append(`
                        <option value="` + val.id + `" ` + (val.id == response.user_data_id ? 'selected' : '') + `>` + val.npwp + ` ` + val.address + `</option>
                    `);
                });
                $('#destination_address').val(response.destination_address);
                $('#province_id').empty().append(`<option value="` + response.province_id + `">` + response.province_name + `</option>`);
                $('#district_id,#city_id').empty().append(`
                    <option value="">--{{ __('translations.select') }}--</option>
                `);
                $('#project_id').empty();
                if(response.project_name){
                    $('#project_id').append(`
                        <option value="` + response.project_id + `">` + response.project_name + `</option>
                    `);
                }
                $.each(response.cities, function(i, val) {
                    $('#city_id').append(`
                        <option value="` + val.id + `">` + val.name + `</option>
                    `);
                });
                $('#city_id').val(response.city_id).formSelect();
                let index = -1;
                $.each(response.cities, function(i, val) {
                    if(val.id == response.city_id){
                        index = i;
                    }
                });
                if(index >= 0){
                    $.each(response.cities[index].district, function(i, value) {
                        let selected = '';
                        $('#district_id').append(`
                            <option value="` + value.id + `" ` + (value.id == response.district_id ? 'selected' : '') + `>` + value.name + `</option>
                        `);
                    });
                }
                $('#payment_type').val(response.payment_type).trigger('change');
                $('#dp_type').val(response.dp_type);
                $('#top_internal').val(response.top_internal);
                $('#top_customer').val(response.top_customer);
                $('#is_guarantee').val(response.is_guarantee).formSelect();
                $('#currency_id').val(response.currency_id).formSelect();
                $('#currency_rate').val(response.currency_rate);
                $('#percent_dp').val(response.percent_dp);
                $('#sales_id').empty().append(`<option value="` + response.sales_id + `">` + response.sales_name + `</option>`);
                if(response.broker_name){
                    $('#broker_id').empty().append(`<option value="` + response.broker_id + `">` + response.broker_name + `</option>`);
                }
                $('#note_internal').val(response.note_internal);
                $('#note_external').val(response.note_external);
                $('#phone').val(response.phone);
                $('#total').val(response.total);
                $('#tax').val(response.tax);
                $('#total_after_tax').val(response.total_after_tax);
                $('#rounding').val(response.rounding);
                $('#grandtotal').val(response.grandtotal);

                if(response.details.length > 0){
                    $('#last-row-item').remove();
                    $('.row_item').each(function(){
                        $(this).remove();
                    });

                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        $('#body-item').append(`
                            <tr class="row_item">
                                <input type="hidden" name="arr_tax_nominal[]" id="arr_tax_nominal` + count + `" value="` + val.tax + `">
                                <input type="hidden" name="arr_grandtotal[]" id="arr_grandtotal` + count + `" value="` + val.grandtotal + `">
                                <input name="arr_price[]" type="hidden" value="` + val.price + `" id="rowPrice`+ count +`">
                                <td>
                                    <label>
                                        <input type="checkbox" id="arr_is_include_tax` + count + `" name="arr_is_include_tax[]" value="1" onclick="countRow('` + count + `');">
                                        <span>Ya/Tidak</span>
                                    </label>
                                </td>
                                <td>
                                    <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                                </td>
                                <td class="center-align">
                                    <select class="browser-default" id="arr_place` + count + `" name="arr_place[]">
                                        @foreach ($place as $rowplace)
                                            <option value="{{ $rowplace->code }}">{{ $rowplace->code }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="right-align" id="arr_qty_now` + count + `">` + val.qty_now + `</td>
                                <td class="right-align" id="arr_qty_temporary` + count + `">` + val.qty_commited + `</td>
                                <td class="center">
                                    <input name="arr_qty_uom[]" type="text" value="` + val.qty_uom + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" data-qty="0" style="text-align:right;" id="rowQtyUom`+ count +`">
                                </td>
                                <td class="center-align" id="arr_uom_unit` + count + `">` + val.uom + `</td>
                                <td class="center">
                                    <input name="arr_price_delivery[]" type="text" value="` + val.price_delivery + `" style="text-align:right;border-bottom:none;" id="rowPriceDelivery`+ count +`" readonly>
                                </td>
                                <td class="center">
                                    <input name="arr_price_type_bp[]" type="text" value="` + val.price_type_bp + `" style="text-align:right;border-bottom:none;" id="rowPriceTypeBp`+ count +`" readonly>
                                </td>
                                <td class="center">
                                    <input name="arr_price_list[]" type="text" value="` + val.price_list + `" onkeyup="formatRupiah(this);countRow('` + count + `');" style="text-align:right;" id="rowPriceList`+ count +`">
                                </td>
                                <td class="right-align">
                                    <b id="tempPrice` + count + `">` + val.price + `</b>
                                </td>
                                <td>
                                    <input name="arr_qty[]" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinus(this);" style="text-align:right;border-bottom:none;" id="rowQty`+ count +`" readonly>
                                </td>
                                <td class="center">
                                    <select class="browser-default" id="arr_unit` + count + `" name="arr_unit[]" onchange="countRow('` + count + `');">
                                        <option value="">--Silahkan pilih item--</option>
                                    </select>
                                </td>
                                <td>
                                    
                                </td>
                                <td class="center">
                                    <input name="arr_disc1[]" class="browser-default" type="text" value="` + val.disc1 + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc1`+ count +`">
                                </td>
                                <td class="center">
                                    <input name="arr_disc2[]" class="browser-default" type="text" value="` + val.disc2 + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc2`+ count +`">
                                </td>
                                <td class="center">
                                    <input name="arr_disc3[]" class="browser-default" type="text" value="` + val.disc3 + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowDisc3`+ count +`">
                                </td>

                                <td class="center">
                                    <input name="arr_final_price[]" class="browser-default" type="text" value="` + val.final_price + `" style="text-align:right;" id="arr_final_price`+ count +`" readonly>
                                </td>
                                <td class="center">
                                    <input name="arr_total[]" class="browser-default" type="text" value="` + val.total + `" style="text-align:right;" id="arr_total`+ count +`" readonly>
                                </td>
                                <td>
                                    <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang..." value="` + val.note + `">
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                        $('#arr_place' + count).val(val.place_code);
                        $("#arr_tax" + count + " option[data-id='" + val.tax_id + "']").prop("selected",true);
                        if(val.is_include_tax){
                            $('#arr_is_include_tax' + count).prop( "checked", true);
                        }
                        $('#arr_item' + count).append(`
                            <option value="` + val.item_id + `">` + val.item_name + `</option>
                        `);
                        select2ServerSide('#arr_item' + count, '{{ url("admin/select2/sales_item") }}');
                        $.each(val.sell_units, function(i, value) {
                            $('#arr_unit' + count).append(`
                                <option value="` + value.id + `" data-conversion="` + value.conversion + `">` + value.code + `</option>
                            `);
                        });
                        $('#arr_unit' + count).val(val.item_unit_id);
                        /* $('#rowQtyUom' + count).trigger('keyup'); */
                    });
                }

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
                    title : 'Marketing Order',
                    intro : 'Form ini digunakan untuk menambahkan dokumen SO atau Penawaran kepada Customer sesuai pesanan yang diinginkan.'
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
                    title : 'Customer',
                    element : document.querySelector('.step3'),
                    intro : 'Customer adalah Partner Bisnis tipe penyedia pelanggan. Jika ingin menambahkan data baru, silahkan ke form Master Data - Organisasi - Partner Bisnis.'
                },
                {
                    title : 'Alamat penagihan',
                    element : document.querySelector('.step4'),
                    intro : 'Silahkan pilih alamat penagihan yang diambil dari master data partner bisnis pada detail alamat penagihan.'
                },
                {
                    title : 'Perusahaan',
                    element : document.querySelector('.step5'),
                    intro : 'Perusahaan dimana dokumen ini dibuat.'
                },
                {
                    title : 'Tgl. Posting',
                    element : document.querySelector('.step7'),
                    intro : 'Tanggal post akan menentukan tanggal jurnal untuk beberapa form yang terhubung dengan jurnal. Hati - hati dalam menentukan tanggal posting.'
                },
                {
                    title : 'Tgl. Valid SO',
                    element : document.querySelector('.step8'),
                    intro : 'Tanggal berlaku SO / Penawaran, set sesuai dengan masa berlaku yang diinginkan.'
                },
                {
                    title : 'Proyek (Jika ada)',
                    element : document.querySelector('.step9'),
                    intro : 'Silahkan pilih proyek ini jika penjualan ingin dihubungkan dengan data Proyek. Data proyek bisa ditambahkan pada form Master Data - Administrasi - Proyek.'
                },
                {
                    title : 'No. Referensi',
                    element : document.querySelector('.step10'),
                    intro : 'No referensi bisa diisikan dengan no dokumen PO dari customer atau dokumen terkait lainnya yang mendukung penjualan ini.'
                },
                {
                    title : 'Tipe Pengiriman',
                    element : document.querySelector('.step11'),
                    intro : 'Ada 2 macam tipe pengiriman, yakni yang pertama adalah Franco adalah biaya pengiriman barang dibebankan pada penjual. Sedangkan Loco, adalah kebalikan dari Franco, dimana biaya pengiriman barang dibebankan kepada customer.'
                },
                {
                    title : 'Ekspedisi',
                    element : document.querySelector('.step12'),
                    intro : 'Ekspedisi adalah pihak partner bisnis tipe pengirim, silahkan tambahkan jika tidak ada, di Menu Master Data - Organisasi - Partner Bisnis.'
                },
                {
                    title : 'Tipe Transport',
                    element : document.querySelector('.step13'),
                    intro : 'Tipe kendaraan yang digunakan dalam pengiriman barang nantinya.'
                },
                {
                    title : 'Tgl. Kirim',
                    element : document.querySelector('.step14'),
                    intro : 'Tanggal perkiraan pengiriman barang dari gudang.'
                },
                {
                    title : 'Outlet',
                    element : document.querySelector('.step15'),
                    intro : 'Tempat tujuan barang akan dikirimkan dalam bentuk toko / supermarket / distributor.'
                },
                {
                    title : 'Alamat Tujuan',
                    element : document.querySelector('.step16'),
                    intro : 'Alamat tujuan adalah alamat dimana barang ingin dikirimkan.'
                },
                {
                    title : 'Provinsi',
                    element : document.querySelector('.step17'),
                    intro : 'Provinsi dimana barang ingin dikirimkan (berdasarkan alamat tujuan).'
                },
                {
                    title : 'Kota',
                    element : document.querySelector('.step18'),
                    intro : 'Kota dimana barang ingin dikirimkan (berdasarkan alamat tujuan).'
                },
                {
                    title : 'Kecamatan',
                    element : document.querySelector('.step19'),
                    intro : 'Kecamatan dimana barang ingin dikirimkan (berdasarkan alamat tujuan).'
                },
                {
                    title : 'Tipe Pembayaran',
                    element : document.querySelector('.step21'),
                    intro : 'Tipe pembayaran SO. Untuk Cash, maka TOP Internal dan TOP Customer akan menjadi 0. Untuk, tipe Credit, maka TOP Internal dan TOP Customer bisa diedit.'
                },
                {
                    title : 'TOP (Term of Payment) Internal',
                    element : document.querySelector('.step22'),
                    intro : 'Tenggat pembayaran internal dalam satuan hari, untuk Finance.'
                },
                {
                    title : 'TOP (Term of Payment) Customer',
                    element : document.querySelector('.step23'),
                    intro : 'Tenggat pembayaran customer dalam satuan hari.'
                },
                {
                    title : 'Mata Uang',
                    element : document.querySelector('.step25'),
                    intro : 'Mata uang, silahkan pilih mata uang lain, untuk mata uang asing.'
                },
                {
                    title : 'Konversi',
                    element : document.querySelector('.step26'),
                    intro : 'Nilai konversi rupiah pada saat dokumen dibuat. Nilai konversi secara otomatis diisi ketika form tambah baru dibuka pertama kali dan data diambil dari situs exchangerate.host. Pastikan kode mata uang benar di master data agar nilai konversi tidak error.'
                },
                {
                    title : 'Persen DP',
                    element : document.querySelector('.step27'),
                    intro : 'Persen Down Payment yang akan menjadi acuan pengecekan credit limit Customer pada saat barang akan dijadwalkan pengirimannya. Silahkan isikan 0, jika tagihan akan dibayarkan secara kredit dan pengecekan akan didasarkan pada limit credit Customer. Silahkan isikan 100 jika tagihan adalah dibayarkan dengan 100% down payment.'
                },
                {
                    title : 'File Lampiran',
                    element : document.querySelector('.step28'),
                    intro : 'Silahkan unggah file lampiran. untuk saat ini hanya bisa mengakomodir 1 file lampiran saja. Jika ingin menambahkan file lebih dari 1, silahkan gabungkan file anda menjadi pdf.'
                },
                {
                    title : 'Sales',
                    element : document.querySelector('.step29'),
                    intro : 'Inputan ini digunakan untuk mengatur sales terkait dengan penjualan. Data diambil dari Partner Bisnis tipe Karyawan / Pegawai.'
                },
                {
                    title : 'Detail produk',
                    element : document.querySelector('.step30'),
                    intro : 'Silahkan tambahkan produk anda disini, lengkap dengan keterangan detail tentang produk tersebut. Hati-hati dalam menentukan Plant, dan Gudang Tujuan, karena itu nantinya akan menentukan dimana barang ketika diterima.'
                },
                {
                    title : 'Tambah Baris',
                    element : document.querySelector('.step31'),
                    intro : 'Untuk menambahkan baris produk yang ingin diinput silahkan tekan tombol ini.'
                },
                {
                    title : 'Keterangan Internal',
                    element : document.querySelector('.step32'),
                    intro : 'Silahkan isi / tambahkan keterangan internal untuk dokumen ini untuk catatan antar departemen (internal perusahaan) saja.'
                },
                {
                    title : 'Keterangan Eksternal',
                    element : document.querySelector('.step33'),
                    intro : 'Silahkan isi / tambahkan keterangan eksternal untuk dokumen ini dan kepentingan luar perusahaan.'
                },
                {
                    title : 'Informasi Total',
                    element : document.querySelector('.step34'),
                    intro : 'Nominal diskon, untuk diskon yang ingin dimunculkan di dalam dokumen ketika dicetak. Sedangkan untuk Rounding akan menambah atau mengurangi nilai grandtotal sesuai inputan pengguna.'
                },
                {
                    title : 'Tombol Simpan',
                    element : document.querySelector('.step35'),
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
    function exportExcel(){
        var search = table.search();
        var status = $('#filter_status').val();
        var type_buy = $('#filter_inventory').val();
        var type_deliv = $('#filter_delivery').val();
        var company = $('#filter_company').val();
        var type_pay = $('#filter_payment').val();
        var supplier = $('#filter_account').val();
        var sender = $('#filter_sender').val();
        var sales = $('#filter_sales').val();
        var currency = $('#filter_currency').val();
        var start_date = $('#start_date').val();
        var end_date = $('#finish_date').val();
        var modedata = '{{ $modedata }}';

        window.location = "{{ Request::url() }}/export_from_page?search=" + search + "&status=" + status + "&type_buy=" + type_buy + "&type_deliv=" + type_deliv + "&company=" + company + "&type_pay=" + type_pay + "&supplier=" + supplier + "&currency=" + currency + "&end_date=" + end_date + "&start_date=" + start_date + "&modedata=" + modedata;

    }
</script>
