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
                                                <div class="row">
                                                    <div class="col m3 s6 ">
                                                        <label for="date" style="font-size:1rem;">Tanggal Posting :</label>
                                                        <input type="date" max="{{ date('9999'.'-12-31') }}" id="date" name="date" value="{{ date('Y-m-d') }}">
                                                    </div>
                                                    <div class="col m4 s6 pt-2">
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
                                                        <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">TGL Post</th>
                                                        <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Jatuh Tempo</th>
                                                        <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Keterangan</th>
                                                        <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Total</th>
                                                        <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Dibayar</th>
                                                        <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Sisa</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="detail_invoice">
                                                    <tr>
                                                        <td class="center-align" colspan="8">Silahkan pilih tanggal dan tekan tombol filter.</td>
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

<script>
    function exportExcel(){
        var date = $('#date').val();
        window.location = "{{ Request::url() }}/export?date=" + date;
    }
    function filterByDate(){
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
                $('#validation_alert_multi').html('');
                loadingOpen('#main');
            },
            success: function(response) {
                loadingClose('#main');
                if(response.status == 200) {
                    $('#detail_invoice').empty();
                    if(response.data.length > 0){
                        $.each(response.data, function(i, val) {
                            $('#detail_invoice').append(`
                                <tr>
                                    <td class="center-align">`+(i+1)+`.</td>
                                    <td>`+val.code+`</td>
                                    <td>`+val.customer+`</td>
                                    <td class="center-align">`+val.post_date+`</td>
                                    <td class="center-align">`+val.due_date+`</td>
                                    <td>`+val.note+`</td>
                                    <td class="right-align">`+val.total+`</td>
                                    <td class="right-align">`+val.payment+`</td>
                                    <td class="right-align">`+val.balance+`</td>
                                </tr>
                            `);
                        });
                        $('#detail_invoice').append(`
                            <tr>
                                <td class="right-align" colspan="18"><b><h6>TOTAL</h6></b></td>
                                <td class="right-align"><b><h6>` + response.grandtotal + `</h6></b></td>
                            </tr>
                        `);
                        $('#detail_invoice').append(`
                            <tr>
                                <td class="" colspan="19">Waktu Proses : <b>` + response.execution_time + ` Detik</b></td>
                            </tr>
                        `);
                    }else{
                        $('#detail_invoice').append(`
                            <tr>
                                <td class="center-align" colspan="19">Data tidak ditemukan.</td>
                            </tr>
                        `);
                    }
           
                    M.toast({
                        html: 'Sukses proses data'
                    });
                } else if(response.status == 422) {
                    $('#validation_alert_multi').show();
                    $('#main').scrollTop(0);
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
        $('#detail_invoice').html('').append(`
            <tr>
                <td class="center-align" colspan="19">Silahkan pilih tanggal dan tekan tombol filter.</td>
            </tr>
        `);
    }
</script>