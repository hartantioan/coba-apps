<style>
    .select-wrapper, .select2-container {
        height:3.7rem !important;
    }
    .select2-selection--multiple{
        overflow-y: scroll !important;
        height: auto !important;
    }
    .select2{
        height: fit-content !important;
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

                    <div class="card ">
                        <div class="card-content">
                            <form class="row" id="form_data" onsubmit="return false;">
                                <div class="col s12">
                                    <h4>Qty</h4>
                                    <div id="validation_alert" style="display:none;"></div>
                                </div>
                                <div class="col s12">
                                    <div class="row">
                                        <div class="col m12 s12">
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <input id="date" name="date"  type="date" max="{{ date('9999'.'-12-31') }}" placeholder="" value="{{ date('Y-m-d') }}">
                                            <label class="active" for="date">Tanggal Adjust</label>
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <select class="form-control" id="plant" name="plant">
                                                <option value="all">{{ __('translations.all') }}</option>
                                                @foreach ($place as $row)
                                                    <option value="{{ $row->id }}">{{ $row->code }}</option>
                                                @endforeach
                                            </select>
                                            <label class="" for="plant">{{ __('translations.plant') }}</label>
                                        </div>

                                        <div class="input-field col m3 s12">
                                            <select class="form-control" id="warehouse" name="warehouse">
                                                <option value="all">{{ __('translations.all') }}</option>
                                                @foreach ($warehouse as $row)
                                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                                @endforeach
                                            </select>
                                            <label class="" for="warehouse">WareHouse</label>
                                        </div>

                                        <div  class="input-field col m1 mt-1" >
                                            <button class="btn waves-effect waves-light right submit mt-2" onclick="exportExcel();">Hitung Ulang Stock<i class="material-icons right">view_list</i></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card ">
                        <div class="card-content">
                            <form class="row" id="form_data" onsubmit="return false;">
                                <div class="col s12">
                                    <h4>Nominal</h4>
                                    <div id="validation_alert" style="display:none;"></div>
                                </div>
                                <div class="col s12">
                                    <div class="row">
                                        <div class="col m12 s12">
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <input id="date" name="date"  type="date" max="{{ date('9999'.'-12-31') }}" placeholder="" value="{{ date('Y-m-d') }}">
                                            <label class="active" for="date">Tanggal Adjust</label>
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <select class="form-control" id="plant" name="plant">
                                                <option value="all">{{ __('translations.all') }}</option>
                                                @foreach ($place as $row)
                                                    <option value="{{ $row->id }}">{{ $row->code }}</option>
                                                @endforeach
                                            </select>
                                            <label class="" for="plant">{{ __('translations.plant') }}</label>
                                        </div>

                                        <div class="input-field col m3 s12">
                                            <select class="form-control" id="warehouse" name="warehouse">
                                                <option value="all">{{ __('translations.all') }}</option>
                                                @foreach ($warehouse as $row)
                                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                                @endforeach
                                            </select>
                                            <label class="" for="warehouse">WareHouse</label>
                                        </div>

                                        <div  class="input-field col m1 mt-1" >
                                            <button class="btn waves-effect waves-light blue lighten-3 right submit mt-2" onclick="exportExcel();">Hitung Ulang Stock<i class="material-icons right">view_list</i></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('#export_button').hide();
    function filter(){
        var formData = new FormData($('#form_data')[0]);
        formData.append('group[]',$('#filter_group').val());
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
                loadingOpen('#main');
            },
            success: function(response) {
                $('#export_button').show();
                loadingClose('#main');
                if(response.status == 200) {
                    $('#table_body').empty();
                    if (response.message.length > 0) {
                        $.each(response.message, function(i, val) {
                            $('#table_body').append(`
                                <tr>
                                    <td class="center-align">`+(i+1)+`</td>
                                    <td >`+val.plant+`</td>
                                    <td >`+val.gudang+`</td>
                                    <td >`+val.kode+`</td>
                                    <td >`+val.item+`</td>
                                    <td >`+val.satuan+`</td>
                                    <td >`+val.area+`</td>
                                    <td >`+val.shading+`</td>
                                    <td >`+val.keterangan+`</td>
                                    <td >`+val.date+`</td>
                                    <td >`+val.lamahari+`</td>
                                </tr>
                            `);
                        });
                        $('#table_body').append(`
                                <tr>
                                    <td class="center-align" colspan="5">`+response.time+`</td>
                                </tr>
                            `);
                        M.toast({
                            html: 'filtered'
                        });
                    }else{
                        $('#table_body').append(`
                            <tr>
                                <td colspan="6" class="center-align">BELUM ADA STOCK</td>
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

</script>
