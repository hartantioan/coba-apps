<!-- BEGIN: Page Main-->
<style>
    /* td:first-child, .fixed
	{
	  position:sticky;
	  left:0px;
	  background-color:#c5c3c3;
	} */
        thead th {
        position: sticky;
        background-color: #c5c3c3;
        z-index: 2;
    }
    .fixed {
        left: 10px;
        z-index: 20;
    }

    .fixed2 {
        left: 100px; /* Adjust as needed */
        z-index: 20;
    }

    .fixed1 {
        left: 200px; /* Adjust as needed */
        z-index: 20;
    }
    td:first-child,
    {
        position: sticky;
        left: 10px;
        background-color: #c5c3c3;
    }
    td:nth-child(2) {
        position: sticky;
        left: 100px;
        background-color: #c5c3c3;
    }
    td:nth-child(3) {
        position: sticky;
        left: 200px;
        background-color: #c5c3c3;
    }
</style>

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
                                                    <div class="input-field col m4 s12">
                                                        <input id="start_date" name="start_date" type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                                        <label class="active" for="start_date">Tanggal Awal</label>
                                                    </div>
                                                    <div class="input-field col m4 s12">
                                                        <input id="end_date" name="end_date"  type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                                        <label class="active" for="end_date">Tanggal Akhir</label>
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
                                                        <th class="center-align fixed">{{ __('translations.no') }}.</th>
                                                        <th class="center-align fixed2">NIK</th>
                                                        <th class="center-align fixed1">{{ __('translations.name') }}</th>
                                                        <th class="center-align">{{ __('translations.date') }}</th>
                                                        <th class="center-align">Nama Shift</th>
                                                        <th class="center-align">Shift Awal</th>
                                                        <th class="center-align">Shift Masuk</th>
                                                        <th class="center-align">Check In</th>
                                                        <th class="center-align">Menit Terlambat</th>
                                                        <th class="center-align">{{ __('translations.type') }}</th>
                                                        <th class="center-align">Shift Pulang</th>
                                                        <th class="center-align">Check Out</th>
                                                        <th class="center-align">Shift Akhir</th>
                                                        <th class="center-align">Menit Mendahului</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="detail_kehadiran">
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
                loadingOpen('#main-display');
            },
            success: function(response) {
                loadingClose('#main-display');
                if(response.status == 200) {
                    
                    const dateKeys = Object.keys(response.message);
                    const responseDataLength = dateKeys.length;
                    
                    $('#detail_kehadiran').empty();
                    if(responseDataLength > 0){
                        $.each(response.message,function(i,val){
                            $.each(val,function(j,valed){
                                
                                var itung=0;
                                $.each(valed.nama_shift,function(k,isi){
                                   
                                    let perbedaanJamMasuk = valed.perbedaan_jam_masuk[k];
                                    if (typeof perbedaan_jam_masuk === 'undefined'||typeof perbedaan_jam_masuk === '') {
                                        if(valed.in[k]==1){
                                            perbedaanJamMasuk = 'tepat waktu';
                                        }
                                        
                                    }
                                    let perbedaanJamKeluar = valed.perbedaan_jam_keluar[k];
                                    if (typeof perbedaan_jam_keluar === 'undefined'||typeof perbedaan_jam_keluar === '') {
                                        if(valed.out[k]==1){
                                            perbedaanJamKeluar="tepat waktu"
                                        }
                                       
                                    }
                                  
                                    $('#detail_kehadiran').append(`
                                        <tr>
                                            
                                            <td class="center-align">`+(j+1)+`</td>
                                            <td class="center-align">`+valed.user_no+`</td>
                                            <td class="center-align">`+valed.user+`</td>
                                            
                                            <td class="center-align">`+valed.date+`</td>
                                            <td class="center-align">`+valed.nama_shift[itung]+`</td>
                                            <td class="center-align">`+valed.min_time_in[itung]+`</td>
                                            <td class="center-align">`+valed.time_in[itung]+`</td>
                                            <td class="center-align">`+valed.jam_masuk[Object.keys(valed.jam_masuk)[itung]]+`</td>
                                            <td class="center-align">`+perbedaanJamMasuk+`</td>
                                            <td class="center-align">`+valed.tipe[itung]+`</td>
                                            <td class="center-align">`+valed.time_out[itung]+`</td>
                                            <td class="center-align">`+valed.jam_keluar[Object.keys(valed.jam_keluar)[itung]]+`</td>
                                            <td class="center-align">`+valed.max_time_out[itung]+`</td>
                                            <td class="center-align">`+perbedaanJamKeluar+`</td>
                                        </tr>
                                    `);
                                    itung++;
                                })
                                
                                
                            })
                        })
                        
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
                } else if(response.status == 422) {
                    $('#validation_alert_multi').show();
                    $('#main-display').scrollTop(0);
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