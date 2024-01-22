<style>
    .select-wrapper, .select2-container {
        height:3.7rem !important;
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
            <div class="container">
                <div class="row">
                    <div class="card">
                        <div class="card-content">
                            <form class="row" id="form_data" onsubmit="return false;">
                                <div class="col s12">
                                    <div id="validation_alert" style="display:none;"></div>
                                </div>
                                <div class="col s12">
                                    <div class="row">
                                        <div class="input-field col m3 s12">
                                            <input id="start_date" name="start_date" type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. posting" value="{{ date('Y-m').'-01' }}">
                                            <label class="active" for="start_date">Tanggal Awal</label>
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <input id="finish_date" name="finish_date"  type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                            <label class="active" for="finish_date">Tanggal Akhir</label>
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <select class="form-control" id="plant" name="plant">
                                                @foreach ($place as $row)
                                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                                @endforeach
                                            </select>
                                            <label class="" for="plant"></label>
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <select class="select2 browser-default" id="item" name="item">
                                                
                                            </select>
                                            <label class="active" for="item">ITEM</label>
                                        </div>
                                        <div class="col m3">
                                            <button class="btn waves-effect waves-light submit" onclick="filter();">Cari <i class="material-icons right">file_download</i></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                </div>
                <div class="row">
                    <table class="bordered" style="font-size:10px;">
                        <thead>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Tanggal</th>
                                <th class="center-align">Masuk <div class="uomunit"></div></th>
                                <th class="center-align">Keluar <div class="uomunit"></div></th>
                                <th class="center-align">Saldo <div class="uomunit"></div></th>
                            </tr>
                        </thead>
                        <tbody id="movement_body">
                        </tbody>
                    </table>
                    <div id="export_button">
                        <button class="btn waves-effect waves-light right submit mt-2" onclick="exportExcel();">Excel<i class="material-icons right">view_list</i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        select2ServerSide('#item', '{{ url("admin/select2/item") }}');
    });
    $('#export_button').hide();
    function filter(){
        var formData = new FormData($('#form_data')[0]);
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
                $('#validation_alert_multi').html('');
                loadingOpen('.modal-content');
            },
            success: function(response) {
                $('#export_button').show();
                loadingClose('.modal-content');
                if(response.status == 200) {
                    $('#movement_body').empty();
                    $('.uomunit').empty();
                    var gtall=0;
                    
                    if(response.uomunit!=null){
                        $('.uomunit').append(`
                            (`+response.uomunit+`)
                        `);
                    }
                 
                    if (response.message.length > 0) {
                        $('#movement_body').append(`
                            <tr>
                                <td colspan="5" class="center-align">Saldo Sebelumnya</td>
                                <td colspan="1" class="right-align">`+response.latest+`</td>
                            </tr>`);
                        $.each(response.message, function(i, val) {
                            gtall+=val.grandtotal;
                            
                            $('#movement_body').append(`
                                <tr>
                                    <td class="center-align">`+(i+1)+`</td>
                                    <td >`+val.keterangan+`</td>
                                    <td >`+val.date+`</td>
                                    <td class="right-align">`+val.masuk+`</td>
                                    <td class="right-align">`+val.keluar+`</td>
                                    <td class="right-align">`+val.final+`</td>
                                </tr>
                            `);
                        });
                        $('#movement_body').append(`
                            <tr>
                                <td class="center-align" colspan="6">`+response.time+`</td>
                            </tr>
                        `);
                        M.toast({
                            html: 'filtered'
                        });
                    }else{
                        $('#movement_body').append(`
                            <tr>
                                <td colspan="6" class="center-align">BELUM ADA PERGERAKAN</td>
                            </tr>`);
                    }
                    
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

    function exportExcel(){
        var plant = $('#plant').val();
        var item = $('#item').val() ? $('#item').val():'';
        var startdate = $('#start_date').val() ? $('#start_date').val():'';
        var finishdate = $('#finish_date').val() ? $('#finish_date').val():'';
        window.location = "{{ Request::url() }}/export?plant=" + plant +"&item=" + item+"&start_date=" + startdate+"&finish_date=" + finishdate
    }
</script>