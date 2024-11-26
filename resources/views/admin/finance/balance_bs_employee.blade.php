<style>
    #text-grandtotal {
        font-size: 50px !important;
        font-weight: 800;
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
                                                <div class="row">
                                                    <div class="col m4 s6 pt-2">
                                                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="filter();">
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
                                                </div>
                                            </div>
                                        </form>
                                    </div>
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
                                    <div class="card-alert card red">
                                        <div class="card-content white-text">
                                            <b>Info : Sisa BS Karyawan dihitung dari Limit Credit pada Master Data dikurangi pemakaian BS karyawan pada FREQ baik itu tipe LENGKAP maupun TIDAK LENGKAP</b>
                                        </div>
                                    </div>
                                    <div class="col s12 m12" style="overflow: auto">
                                        <div class="result">
                                            <table class="bordered" style="font-size:10px;">
                                                <thead id="head_detail">
                                                    <tr>
                                                        <th class="center-align">{{ __('translations.no') }}.</th>
                                                        <th class="center-align">NIK</th>
                                                        <th class="center-align">Nama</th>
                                                        <th class="center-align">Limit</th>
                                                        <th class="center-align">Pemakaian</th>
                                                        <th class="center-align">Sisa</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="detail-result">
                                                    <tr>
                                                        <td class="center-align" colspan="6">Silahkan pilih tanggal dan tekan tombol filter.</td>
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
        
    });

    function exportExcel(){
        if($('.row_detail').length > 0){
            var date = $('#date').val();
            window.location = "{{ Request::url() }}/export?date=" + date;
        }else{
            swal({
                title: 'Ups!',
                text: 'Silahkan filter laporan terlebih dahulu ges.',
                icon: 'warning'
            });
        }
    }

    function filter(){
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
                            $('#detail-result').append(`
                                <tr class="row_detail">
                                    <td class="center-align">` + (i+1) + `</td>
                                    <td>` + val.code + `</td>
                                    <td>` + val.name + `</td>
                                    <td class="right-align">` + val.limit + `</td>
                                    <td class="right-align">` + val.usage + `</td>
                                    <td class="right-align">` + val.balance + `</td>
                                </tr>
                            `);
                        });
                        $('#detail-result').append(`
                            <tr id="text-grandtotal">
                                <td class="center-align" colspan="6">Waktu proses : ` + response.execution_time  + ` detik</td>
                            </tr>
                        `);
                    }else{
                        $('#detail-result').append(`
                            <tr>
                                <td class="center-align" colspan="6">Data tidak ditemukan.</td>
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
                <td class="center-align" colspan="13">Silahkan pilih tanggal dan tekan tombol filter.</td>
            </tr>
        `);
    }
</script>