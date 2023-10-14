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
                                    <div class="collapsible-header"><i class="material-icons">filter_list</i> FILTER</div>
                                    <div class="collapsible-body">
                                        <form class="row" id="form_data_filter" onsubmit="return false;">
                                            <div class="col s12">
                                                <div id="validation_alert_multi" style="display:none;"></div>
                                            </div>
                                            <div class="col s12">
                                                <div class="row">
                                                    <div class="input-field col m4 s12">
                                                        <input id="start_date" name="start_date" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                                        <label class="active" for="start_date">Tanggal Awal</label>
                                                    </div>
                                                    <div class="input-field col m4 s12">
                                                        <input id="end_date" name="end_date"  type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
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
                                <div class="row">
                                    <div class="col s12 m6">
                                        <h4 class="card-title">
                                            Hasil
                                        </h4>
                                    </div>
                                    <div class="col s12 m6">
                                        <div class="row">
                                            <div class="col s12 m6">
                                                <span style="display: inline-flex;"><i class="material-icons" style="color: green;    font-weight: 700;">check</i><p>: Tepat Waktu</p></span>
                                            </div>
                                            <div class="col s12 m6">
                                                <span style="display: inline-flex;"><i class="material-icons" style="color: goldenrod;    font-weight: 700;">check</i><p>: Tidak Check Pulang</p></span>
                                            </div>
                                            <div class="col s12 m6">
                                                <span style="display: inline-flex;"><i class="material-icons" style="color: purple;    font-weight: 700;">check</i><p>: Tidak Check Masuk</p></span>
                                            </div>
                                            <div class="col s12 m6">
                                                <span style="display: inline-flex;"><i class="material-icons" style="color: blue;    font-weight: 700;">check</i><p>: Telat Masuk Saja</p></span>
                                            </div>
                                            <div class="col s12 m6">
                                                <span style="display: inline-flex;"><i class="material-icons" style="color: crimson;    font-weight: 700;">check</i><p>: Telat Masuk Tidak Check Pulang</p></span>
                                            </div>
                                            <div class="col s12 m6">
                                                <span style="display: inline-flex;"><i class="material-icons" style="color: red;    font-weight: 700;">close</i><p>: Absent</p></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                
                                <div class="row">
                                    <div class="col s12 m12" style="overflow: auto">
                                        <div class="result" style="width:2500px;">
                                            <table class="bordered" style="font-size:10px;">
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
                       
                        var string_table=``;
                        
                        $.each(response.message,function(i,val){
                            string_table +=`
                                <tr>
                                    <td rowspan="2">`+i+`</td>
                                    
                            `;
                           

                            $.each(val,function(j,valed){
                                console.log(valed);
                               if(valed.nama_shift.length>0){
                                string_table +=`
                                    <td colspan="`+valed['nama_shift'].length+`">`+valed.user+`</td>  
                                `;
                               }
                                
                                
                            })
                            string_table +=`
                                </tr>
                                
                                <tr> 
                            `;
                            
                            $.each(val,function(j,valed){
                                
                                console.log(valed.user);
                                $.each(valed.nama_shift,function(l,val_date){
                                    
                                    string_table +=`
                                        <td>`+val_date+`</td>
                                    `;
                                    
                                });
                                
                            
                            })
                            string_table +=`
                                </tr>
                                <tr>
                                    <td></td>
                            `;
                            $.each(val,function(j,valed){
                                
                                $.each(valed.nama_shift,function(l,val_date){
                                    /* console.log(valed.in[l]);
                                    console.log(valed.out[l]);
                                    console.log(l); */
                                    if(valed.in[l] == 1 && valed.out[l] == 1 ){
                                        
                                        string_table +=`
                                            <td style="color: green;    font-weight: 700;"><i class="material-icons right">check</i></td>
                                        `;
                                    }if(valed.in[l] == 1 && valed.out[l] == 0 ){
                                        
                                        string_table +=`
                                            <td style="color: goldenrod;    font-weight: 700;"> <i class="material-icons right">check</i></td>
                                        `;
                                    }if(valed.in[l] == 0 && valed.out[l] == 1 ){
                                        
                                        string_table +=`
                                            <td style="color: purple;    font-weight: 700;"> <i class="material-icons right">check</i></td>
                                        `;
                                    }if(valed.in[l] == 0 && valed.out[l] == 0 ){
                                       
                                        string_table +=`
                                            <td style="color: red;    font-weight: 700;"><i class="material-icons right">close</i></td>
                                        `;
                                    }if(valed.in[l] == 2 && valed.out[l] == 0 ){
                                        
                                        string_table +=`
                                            <td style="color: crimson;    font-weight: 700;"> <i class="material-icons right">check</i></td>
                                        `;
                                    }if(valed.in[l] == 2 && valed.out[l] == 1 ){
                                       
                                        string_table +=`
                                            <td style="color: blue;    font-weight: 700;"> <i class="material-icons right">check</i></td>
                                        `;
                                    }    
                                    
                                    
                                });
                                
                            
                            })
                            string_table +=`
                                </tr>
                                
                            `;
                        });
                       
                        $('#detail_kehadiran').append(string_table);
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