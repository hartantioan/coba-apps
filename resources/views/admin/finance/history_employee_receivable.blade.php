<style>
    #text-grandtotal {
        font-size: 50px !important;
        font-weight: 800;
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
                                            <div class="card-alert card red">
                                                <div class="card-content white-text">
                                                    <p>Info Penting! : Data yang ditampilkan disini hanya FREQ dengan tipe <b>PEMBAYARAN</b> dan tipe Dokumen <b>TIDAK LENGKAP</b>. Karena itu yang merupakan syarat BS Karyawan terjadi.</p>
                                                </div>
                                            </div>
                                            <div class="col s12">
                                                <div class="row">
                                                    <div class="col m3 s12 ">
                                                        <label for="date" style="font-size:1rem;">Tanggal Mulai FREQ :</label>
                                                        <input type="date" max="{{ date('9999'.'-12-31') }}" id="start_date" name="start_date" value="{{ date('Y-m-d') }}">
                                                    </div>
                                                    <div class="col m3 s12 ">
                                                        <label for="date" style="font-size:1rem;">Tanggal Akhir FREQ :</label>
                                                        <input type="date" max="{{ date('9999'.'-12-31') }}" id="end_date" name="end_date" value="{{ date('Y-m-d') }}">
                                                    </div>
                                                    <div class="col m6 s12 pt-2">
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
                                                    </div>
                                                    <div class="col m12 s12 ">
                                                        <label for="filter_supplier" style="font-size:1rem;">Karyawan :</label>
                                                        <div class="input-field">
                                                            <select class="browser-default" id="account_id" name="account_id[]" multiple="multiple" style="width:100% !important;"></select>
                                                        </div>
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
                                        <div class="result">
                                            <table class="bordered" style="font-size:10px;">
                                                <thead id="head_detail">
                                                    <tr>
                                                        <th class="center-align">{{ __('translations.no') }}.</th>
                                                        <th class="center-align">No.FREQ</th>
                                                        <th class="center-align">Karyawan</th>
                                                        <th class="center-align">Tgl.Pengajuan</th>
                                                        <th class="center-align">Tgl.Req.Bayar</th>
                                                        <th class="center-align">{{ __('translations.note') }}</th>
                                                        <th class="center-align">{{ __('translations.grandtotal') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="detail-result">
                                                    <tr>
                                                        <td class="center-align" colspan="7">Silahkan pilih tanggal dan tekan tombol filter.</td>
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
            </div>
        </div>
    </div>
</div>

<script>
    $(function(){
        select2ServerSide('#account_id', '{{ url("admin/select2/employee") }}');
    });

    function exportExcel(){
        if($('.row_detail').length > 0){
            var start_date = $('#start_date').val(), end_date = $('#end_date').val(), account_id = $('#account_id').val();
            window.location = "{{ Request::url() }}/export?start_date=" + start_date + "&end_date=" + end_date + "&account_id=" + account_id;
        }else{
            swal({
                title: 'Ups!',
                text: 'Silahkan filter laporan terlebih dahulu ges.',
                icon: 'warning'
            });
        }
    }

    function filterByDate(){
        var formData = new FormData($('#form_data_filter')[0]);
        $.ajax({
            url: '{{ Request::url() }}/filter',
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
                $('#validation_alert').html('');
                loadingOpen('#main');
            },
            success: function(response) {
                loadingClose('#main');
                if(response.status == 200) {
                    $('#detail-result').html('');
                    if(response.content.length > 0){
                        $.each(response.content, function(i, val) {
                            let detail = `<table class="bordered" style="font-size:10px;">
                                                <thead id="head_detail">
                                                    <tr>
                                                        <th class="center-align">No.Dokumen</th>
                                                        <th class="center-align">Tgl.Post</th>
                                                        <th class="center-align">Status</th>
                                                        <th class="center-align">Keterangan</th>
                                                        <th class="center-align">Nominal</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="detail-result">`;
                            if(val.details.length > 0){
                                $.each(val.details, function(j, value) {
                                    detail += `
                                        <tr>
                                            <td>` + value.no + `</td>
                                            <td>` + value.post_date + `</td>
                                            <td class="center-align">` + value.status + `</td>
                                            <td>` + value.note + `</td>
                                            <td class="right-align">` + value.nominal + `</td>
                                        </tr>
                                    `;
                                });
                            }else{
                                detail += `<tr>
                                            <td class="center-align" colspan="5">Data pemakaian tidak ditemukan.</td>
                                        </tr>`;
                            }
                            detail += `</tbody></table>`;
                            $('#detail-result').append(`
                                <tr class="row_detail" style="background-color:` + getRandomColor() + `;font-weight:700;">
                                    <td class="center-align" rowspan="2">` + (i+1) + `</td>
                                    <td>` + val.code + `</td>
                                    <td>` + val.employee_name + `</td>
                                    <td>` + val.post_date + `</td>
                                    <td>` + val.required_date + `</td>
                                    <td>` + val.note + `</td>
                                    <td class="right-align">` + val.grandtotal + `</td>
                                </tr>
                                <tr>
                                    <td colspan="6">` + detail + `</td>
                                </tr
                            `);
                        });
                        $('#detail-result').append(`
                            <tr id="text-grandtotal">
                                <td class="center-align" colspan="7">Waktu proses : ` + response.execution_time  + ` detik</td>
                            </tr>
                        `);
                    }else{
                        $('#detail-result').append(`
                            <tr>
                                <td class="center-align" colspan="7">Data tidak ditemukan.</td>
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
                $('#main').scrollTop(0);
                loadingClose('#main');
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
            }
        });
    }

    function reset(){
        $('#form_data_filter')[0].reset();
        $('#detail-result').html('').append(`
            <tr>
                <td class="center-align" colspan="7">Silahkan pilih tanggal dan tekan tombol filter.</td>
            </tr>
        `);
    }
</script>