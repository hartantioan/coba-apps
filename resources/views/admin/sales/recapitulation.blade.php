<style>
    .modal {
        top:0px !important;
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
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12">
            <div class="container">
                <div class="section">

                    <div class="row">
                        <div class="col s12 m12 l12" id="main-display">
                            <ul class="collapsible collapsible-accordion">
                                <li class="active">
                                    <div class="collapsible-header"><i class="material-icons">filter_list</i>{{ __('translations.filter') }}</div>
                                    <div class="collapsible-body">
                                        <form class="row" id="form_data_filter" onsubmit="return false;">
                                            <div class="col s12">
                                                <div id="validation_alert_multi" style="display:none;"></div>
                                            </div>
                                            <div class="col s12">
                                                <div class="card-alert card green">
                                                    <div class="card-content white-text">
                                                        <p>INFO : Untuk Ekspor CSV Pajak, data yang diambil adalah sesuai urutan dari ARDP (AR Down Payment) kemudian ARIN (AR Invoice).</p>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col m3 s6 ">
                                                        <label for="start_date" style="font-size:1rem;">Tanggal Mulai Posting :</label>
                                                        <input type="date" max="{{ date('9999'.'-12-31') }}" id="start_date" name="start_date" value="{{ date('Y-m-d') }}">
                                                    </div>
                                                    <div class="col m3 s6 ">
                                                        <label for="end_date" style="font-size:1rem;">Tanggal Akhir Posting :</label>
                                                        <input type="date" max="{{ date('9999'.'-12-31') }}" id="end_date" name="end_date" value="{{ date('Y-m-d') }}">
                                                    </div>
                                                    <div class="col m7 s6 pt-2">
                                                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="filterByDate();">
                                                            <i class="material-icons hide-on-med-and-up">search</i>
                                                            <span class="hide-on-small-onl">Filter</span>
                                                            <i class="material-icons right">search</i>
                                                        </a>
                                                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="reset();">
                                                            <i class="material-icons hide-on-med-and-up">loop</i>
                                                            <span class="hide-on-small-onl">Reset</span>
                                                            <i class="material-icons right">loop</i>
                                                        </a>
                                                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="exportExcel();">
                                                            <i class="material-icons hide-on-med-and-up">view_list</i>
                                                            <span class="hide-on-small-onl">Excel</span>
                                                            <i class="material-icons right">view_list</i>
                                                        </a>
                                                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="exportCsv();">
                                                            <i class="material-icons hide-on-med-and-up">view_list</i>
                                                            <span class="hide-on-small-onl">CSV PAJAK</span>
                                                            <i class="material-icons right">view_list</i>
                                                        </a>

                                                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="exportXml();">
                                                            <i class="material-icons hide-on-med-and-up">view_list</i>
                                                            <span class="hide-on-small-onl">XML</span>
                                                            <i class="material-icons right">view_list</i>
                                                        </a>

                                                        <a class="btn btn-small waves-effect green waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="showUpdateTaxCode();">
                                                            <i class="material-icons hide-on-med-and-up">view_list</i>
                                                            <span class="hide-on-small-onl">Update Seri Pajak</span>
                                                            <i class="material-icons right">spellcheck</i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                    </div>
                                    </form>
                        </div>
                        </li>
                        </ul>
                    </div>
                    <div class="card">
                        <div class="card-content">
                            <h4 class="card-title">
                                Hasil
                            </h4>
                            <div class="row">
                                <div class="col s12 m12" style="overflow: auto">
                                    <div class="result" style="width:2500px;">
                                        <table class="bordered" style="font-size:10px;">
                                            <thead>
                                                <tr>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.no') }}.</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">No Invoice</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.customer') }}</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Tgl.Post</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">TOP</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Note</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.total') }}</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.tax') }}</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.grandtotal') }}</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Terjadwal</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Terkirim</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Retur</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Invoice</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Memo</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Dibayar</th>
                                                    <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Sisa</th>
                                                </tr>
                                            </thead>
                                            <tbody id="detail_invoice">
                                                <tr>
                                                    <td class="center-align" colspan="20">Silahkan pilih tanggal dan tekan tombol filter.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="intro">
                <div class="row">
                    <div class="col s12">

                    </div>
                </div>
            </div>
            <!-- / Intro -->
        </div>
        <div class="content-overlay"></div>
    </div>
</div>
</div>

<div id="modal1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content" style="overflow-x: hidden;max-width: 100%;">
        <div class="row">
            <div class="col s12" style="overflow:auto;width:100% !important;">
                <h6>Anda bisa menggunakan fitur copy paste dari format excel.</h6>
                <table class="bordered" id="table-detail1" style="min-width:600px;max-width:600px;">
                    <thead>
                        <tr>
                            <th class="center" style="width:250px;">Kode Dokumen ARIN / ARDP</th>
                            <th class="center" style="width:250px;">No. Seri Faktur</th>
                            <th class="center" style="width:100px;">Hapus</th>
                        </tr>
                    </thead>
                    <tbody id="body-multi">
                        <tr id="last-multi">
                            <td colspan="3" class="center">
                                
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
        <a class="waves-effect waves-light red btn-small mb-1 mr-1" onclick="addMulti()" href="javascript:void(0);">
            <i class="material-icons left">add</i> Tambah Multi Baris
        </a>
        <button class="btn waves-effect waves-light right submit" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
    </div>
</div>

<script>
    $(function() {
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {

            },
            onOpenEnd: function(modal, trigger) {
                window.onbeforeunload = function() {
                    return 'You will lose all changes made since your last save';
                };
            },
            onCloseEnd: function(modal, trigger){
                M.updateTextFields();
                $('.row_multi').remove();
                window.onbeforeunload = function() {
                    return null;
                };
            }
        });

        $('#body-multi').on('click', '.delete-data-multi', function() {
            $(this).closest('tr').remove();
        });
    });

    function exportExcel() {
        var start_date = $('#start_date').val(),
            end_date = $('#end_date').val();
        window.location = "{{ Request::url() }}/export?start_date=" + start_date + "&end_date=" + end_date;
    }

    function exportCsv() {
        var start_date = $('#start_date').val(),
            end_date = $('#end_date').val();
        window.location = "{{ Request::url() }}/export_csv?start_date=" + start_date + "&end_date=" + end_date;
    }

    function exportXml() {
        var start_date = $('#start_date').val(),
            end_date = $('#end_date').val(),
            invoice_no = $('#invoice_no').val();
        window.location = "{{ Request::url() }}/export_xml?start_date=" + start_date + "&end_date=" + end_date + "&invoice_no=" + invoice_no;
    }

    function filterByDate() {
        var formData = new FormData($('#form_data_filter')[0]);
        $.ajax({
            url: '{{ Request::url() }}/filter_by_date',
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
                loadingOpen('#main-display');
            },
            success: function(response) {
                loadingClose('#main-display');
                if (response.status == 200) {
                    $('#detail_invoice').empty();
                    if (response.content.length > 0) {
                        $.each(response.content, function(i, val) {
                            $('#detail_invoice').append(`
                                <tr>
                                    <td class="center-align">` + (i + 1) + `</td>
                                    <td>` + val.code + `</td>
                                    <td>` + val.customer + `</td>
                                    <td class="center-align">` + val.post_date + `</td>
                                    <td class="center-align">` + val.top + `</td>
                                    <td>` + val.note + `</td>
                                    <td class="right-align">` + val.total + `</td>
                                    <td class="right-align">` + val.tax + `</td>
                                    <td class="right-align">` + val.grandtotal + `</td>
                                    <td class="right-align">` + val.schedule + `</td>
                                    <td class="right-align">` + val.sent + `</td>
                                    <td class="right-align">` + val.return+`</td>
                                    <td class="right-align">` + val.invoice + `</td>
                                    <td class="right-align">` + val.memo + `</td>
                                    <td class="right-align">` + val.payment + `</td>
                                    <td class="right-align">` + val.balance + `</td>
                                </tr>
                            `);
                        });
                        $('#detail_invoice').append(`
                            <tr>
                                <td class="" colspan="20">Waktu Proses : <b>` + response.execution_time + ` Detik</b></td>
                            </tr>
                        `);
                    } else {
                        $('#detail_invoice').append(`
                            <tr>
                                <td class="center-align" colspan="20">Data tidak ditemukan.</td>
                            </tr>
                        `);
                    }

                    M.toast({
                        html: 'Sukses proses data'
                    });
                } else {
                    M.toast({
                        html: response.message
                    });
                }
            },
            error: function() {
                $('#main-display').scrollTop(0);
                loadingClose('#main-display');
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
            }

        });
    }

    function showUpdateTaxCode(){
        $('#modal1').modal('open');
    }

    function reset() {
        $('#form_data_filter')[0].reset();
        $('#detail_invoice').html('').append(`
            <tr>
                <td class="center-align" colspan="20">Silahkan pilih tanggal dan tekan tombol filter.</td>
            </tr>
        `);
    }

    function addMulti(){
        var count = 0;
        swal({
            title: "Input Jumlah Baris Yang Diinginkan!",
            text: "Maksimal tambah multi adalah 100 baris.",
            buttons: true,
            content: {
                element: "input",
                attributes: {
                    min: 1,
                    max: 100,
                    type: "number",
                    value: 1,
                }
            },
            closeOnClickOutside: false,
        })
        .then(() => {
            if ($('.swal-content__input').val() != "" && $('.swal-content__input').val() != null) {
                count = parseInt($('.swal-content__input').val());
                if(parseInt(count) > 100){
                    swal({
                        title: 'Baris tidak boleh lebih dari 100.',
                        icon: 'error'
                    });
                }else{
                    if(count > 0){

                    }
                    for(var i = 0;i < count;i++){
                        $('#last-multi').before(`
                            <tr class="row_multi">
                                <td>
                                    <input type="text" name="arr_multi_code[]" placeholder="KODE DOKUMEN ARIN/ARDP">
                                </td>
                                <td>
                                    <input type="text" name="arr_multi_serial[]" placeholder="KODE SERI PAJAK">
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-multi" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                    }

                    $('#body-multi :input').off('paste');
                    $('#body-multi :input').on('paste', function (e) {
                        var $start = $(this);
                        var source;

                        if (window.clipboardData !== undefined) {
                            source = window.clipboardData;
                        } else {
                            source = e.originalEvent.clipboardData;
                        }
                        var data = source.getData("Text");
                        if (data.length > 0) {
                            if (data.indexOf("\t") > -1) {
                                var columns = data.split("\n");
                                $.each(columns, function () {
                                    var values = this.split("\t");
                                    $.each(values, function () {
                                        $start.val(this);
                                        if($start.closest('td').next('td').find('input')[0] != undefined) {
                                            $start = $start.closest('td').next('td').find('input');
                                        }else{
                                            return false;
                                        }
                                    });
                                    $start = $start.closest('td').parent().next('tr').children('td:first').find('input');
                                });
                                e.preventDefault();
                            }
                            M.toast({
                                html: 'Sukses ditempel.'
                            });
                        }
                    });
                }
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
                var formData = new FormData(), passed = true;

                formData.delete("arr_multi_code[]");
                formData.delete("arr_multi_serial[]");

                $('input[name^="arr_multi_code[]"]').each(function(index){
                    if($(this).val()){
                        formData.append('arr_multi_code[]',$(this).val());
                        formData.append('arr_multi_serial[]',($('input[name^="arr_multi_serial[]"]').eq(index).val() ? $('input[name^="arr_multi_serial"]').eq(index).val() : ''));
                    }
                });

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
                            loadingOpen('.modal-content');
                        },
                        success: function(response) {
                            loadingClose('.modal-content');
                            if(response.status == 200) {
                                success();
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
                        text: 'Perusahaan, tanggal posting, mata uang, konversi, coa, nominal mata uang asing, nominal konversi tidak boleh kosong.',
                        icon: 'error'
                    });
                }
            }
        });
    }

    function success(){
        $('#modal1').modal('close');
    }
</script>