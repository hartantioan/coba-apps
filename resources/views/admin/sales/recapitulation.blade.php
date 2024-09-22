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
                                                        <label for="start_date" style="font-size:1rem;">Tanggal Mulai Posting :</label>
                                                        <input type="date" max="{{ date('9999'.'-12-31') }}" id="start_date" name="start_date" value="{{ date('Y-m-d') }}">
                                                    </div>
                                                    <div class="col m3 s6 ">
                                                        <label for="end_date" style="font-size:1rem;">Tanggal Akhir Posting :</label>
                                                        <input type="date" max="{{ date('9999'.'-12-31') }}" id="end_date" name="end_date" value="{{ date('Y-m-d') }}">
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

<script>
    function exportExcel(){
        var start_date = $('#start_date').val(), end_date = $('#end_date').val();
        window.location = "{{ Request::url() }}/export?start_date=" + start_date + "&end_date=" + end_date;
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
                loadingOpen('#main-display');
            },
            success: function(response) {
                loadingClose('#main-display');
                if(response.status == 200) {
                    $('#detail_invoice').empty();
                    if(response.content.length > 0){
                        $.each(response.content, function(i, val) {
                            $('#detail_invoice').append(`
                                <tr>
                                    <td class="center-align">`+(i+1)+`</td>
                                    <td>`+val.code+`</td>
                                    <td>`+val.customer+`</td>
                                    <td class="center-align">`+val.post_date+`</td>
                                    <td class="center-align">`+val.top+`</td>
                                    <td>`+val.note+`</td>
                                    <td class="right-align">`+val.total+`</td>
                                    <td class="right-align">`+val.tax+`</td>
                                    <td class="right-align">`+val.grandtotal+`</td>
                                    <td class="right-align">`+val.schedule+`</td>
                                    <td class="right-align">`+val.sent+`</td>
                                    <td class="right-align">`+val.return+`</td>
                                    <td class="right-align">`+val.invoice+`</td>
                                    <td class="right-align">`+val.memo+`</td>
                                    <td class="right-align">`+val.payment+`</td>
                                    <td class="right-align">`+val.balance+`</td>
                                </tr>
                            `);
                        });
                        $('#detail_invoice').append(`
                            <tr>
                                <td class="" colspan="20">Waktu Proses : <b>` + response.execution_time + ` Detik</b></td>
                            </tr>
                        `);
                    }else{
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

    function reset(){
        $('#form_data_filter')[0].reset();
        $('#detail_invoice').html('').append(`
            <tr>
                <td class="center-align" colspan="20">Silahkan pilih tanggal dan tekan tombol filter.</td>
            </tr>
        `);
    }
</script>