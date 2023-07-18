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
                                                    <div class="col m3 s6 ">
                                                        <label for="date" style="font-size:1rem;">Tanggal Posting :</label>
                                                        <input type="date" id="date" name="date" value="{{ date('Y-m-d') }}">
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
                                <h6>Untuk melihat detail tagihan pada masing-masing nominal, silahkan klik/tap kotak nominal berwarna <span class="blue-text text-darken-2">biru</span>.</h6>
                                <div class="row">
                                    <div class="col s12 m12" style="overflow: auto">
                                        <div class="result" style="width:2500px;">
                                            <table class="bordered" style="font-size:10px;">
                                                <thead>
                                                    <tr>
                                                        <th class="center-align">No.</th>
                                                        <th class="center-align">No Invoice</th>
                                                        <th class="center-align">Supplier/Vendor</th>
                                                        <th class="center-align">No Referensi</th>
                                                        <th class="center-align">TGL Post</th>
                                                        <th class="center-align">TGL Terima</th>
                                                        <th class="center-align">TOP(Hari)</th>
                                                        <th class="center-align">TGL Tenggat</th>
                                                        <th class="center-align">Nama Item</th>
                                                        <th class="center-align">Note 1</th>
                                                        <th class="center-align">Note 2</th>
                                                        <th class="center-align">Qty</th>
                                                        <th class="center-align">Satuan</th>
                                                        <th class="center-align">Harga Satuan</th>
                                                        <th class="center-align">Total</th>
                                                        <th class="center-align">PPN</th>
                                                        <th class="center-align">PPH</th>
                                                        <th class="center-align">Grandtotal</th>
                                                        <th class="center-align">Dibayar</th>
                                                        <th class="center-align">Sisa</th>
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
                    $('#detail_invoice').empty();
                    if(response.message.length > 0){
                        var gtall=0;
                        $.each(response.message, function(i, val) {
                            gtall+=val.grandtotal;
                            $('#detail_invoice').append(`
                                <tr>
                                    <td class="center-align" rowspan="`+val.details.length+`">`+(i+1)+`</td>
                                    <td  rowspan="`+val.details.length+`">`+val.code+`</td>
                                    <td  rowspan="`+val.details.length+`">`+val.vendor+`</td>
                                    <td >`+val.details[0].po+`</td>
                                    <td class="center-align" rowspan="`+val.details.length+`">`+val.post_date+`</td>
                                    <td class="center-align" rowspan="`+val.details.length+`">`+val.rec_date+`</td>
                                    <td class="center-align">`+val.details[0].top+`</td>
                                    <td class="center-align" rowspan="`+val.details.length+`">`+val.due_date+`</td>
                                    <td >`+val.details[0].item_name+`</td>
                                    <td >`+val.details[0].note1+`</td>
                                    <td >`+val.details[0].note2+`</td>
                                    <td class="center-align">`+val.details[0].qty+`</td>
                                    <td class="center-align">`+val.details[0].unit+`</td>
                                    <td class="right-align">`+val.details[0].price_o+`</td>
                                    <td class="right-align">`+val.details[0].total+`</td>
                                    <td class="right-align">`+val.details[0].ppn+`</td>
                                    <td class="right-align">`+val.details[0].pph+`</td>
                                    <td class="right-align" rowspan="`+val.details.length+`">`+val.grandtotal+`</td>
                                    <td class="right-align" rowspan="`+val.details.length+`">`+val.payed+`</td>
                                    <td class="right-align" rowspan="`+val.details.length+`">`+val.sisa+`</td>
                                </tr>
                            `);
                            $.each(val.details,function(j, details) {
                                if(j>0){
                                    $('#detail_invoice').append(`
                                        <td >`+val.details[j].po+`</td>
                                        <td class="center-align">`+val.details[j].top+`</td>
                                        <td >`+val.details[j].item_name+`</td>
                                        <td >`+val.details[j].note1+`</td>
                                        <td >`+val.details[j].note2+`</td>
                                        <td class="center-align">`+val.details[j].qty+`</td>
                                        <td class="center-align">`+val.details[j].unit+`</td>
                                        <td class="right-align">`+val.details[j].price_o+`</td>
                                        <td class="right-align">`+val.details[j].total+`</td>
                                        <td class="right-align">`+val.details[j].ppn+`</td>
                                        <td class="right-align">`+val.details[j].pph+`</td>
                                        
                                    `);
                                }
                            });
                        });
                        $('#detail_invoice').append(`
                            <tr>
                                <td colspan="20" class="right-align"><h6><b>Grandtotal : `+response.totalall+`</b></h6></td>
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