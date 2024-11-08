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
                                    <div id="validation_alert" style="display:none;"></div>
                                </div>
                                <div class="col s12">
                                    <div class="row">
                                        <div class="input-field col m3 s12">
                                            <select class="form-control" id="type" name="type">
                                                <option value="all">SEMUA</option>
                                                <option value="final">FINAL</option>
                                            </select>
                                            <label class="" for="type">{{ __('translations.type') }}</label>
                                        </div>
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
                                                <option value="all">SEMUA</option>
                                                @foreach ($place as $row)
                                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                                @endforeach
                                            </select>
                                            <label class="" for="plant">{{ __('translations.plant') }}</label>
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <select class="form-control" id="warehouse" name="warehouse">
                                                <option value="all">SEMUA</option>
                                                @foreach ($warehouse as $row)
                                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                                @endforeach
                                            </select>
                                            <label class="" for="warehouse">WareHouse</label>
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <select class="browser-default item-array" id="item_id" name="item_id">

                                            </select>
                                            <label class="active" for="item_id">ITEM</label>
                                        </div>
                                        <div class="input-field  col m12 s12 ">
                                            <label for="filter_group" class="active" style="font-size:1rem;">Filter Group :</label>

                                            <select class="select2 browser-default" multiple="multiple" id="filter_group" name="filter_group[]" onchange="loadDataTable()">
                                                @foreach($group->whereNull('parent_id') as $c)
                                                    @if(!$c->childSub()->exists())
                                                        <option value="{{ $c->id }}"> - {{ $c->name }}</option>
                                                    @else
                                                        <optgroup label=" - {{ $c->code.' - '.$c->name }}">
                                                        @foreach($c->childSub as $bc)
                                                            @if(!$bc->childSub()->exists())
                                                                <option value="{{ $bc->id }}"> -  - {{ $bc->name }}</option>
                                                            @else
                                                                <optgroup label=" -  - {{ $bc->code.' - '.$bc->name }}">
                                                                    @foreach($bc->childSub as $bcc)
                                                                        @if(!$bcc->childSub()->exists())
                                                                            <option value="{{ $bcc->id }}"> -  -  - {{ $bcc->name }}</option>
                                                                        @else
                                                                            <optgroup label=" -  -  - {{ $bcc->code.' - '.$bcc->name }}">
                                                                                @foreach($bcc->childSub as $bccc)
                                                                                    @if(!$bccc->childSub()->exists())
                                                                                        <option value="{{ $bccc->id }}"> -  -  -  - {{ $bccc->name }}</option>
                                                                                    @else
                                                                                        <optgroup label=" -  -  -  - {{ $bccc->code.' - '.$bccc->name }}">
                                                                                            @foreach($bccc->childSub as $bcccc)
                                                                                                @if(!$bcccc->childSub()->exists())
                                                                                                    <option value="{{ $bcccc->id }}"> -  -  -  -  - {{ $bcccc->name }}</option>
                                                                                                @endif
                                                                                            @endforeach
                                                                                        </optgroup>
                                                                                    @endif
                                                                                @endforeach
                                                                            </optgroup>
                                                                        @endif
                                                                    @endforeach
                                                                </optgroup>
                                                            @endif
                                                        @endforeach
                                                        </optgroup>
                                                    @endif
                                            @endforeach
                                            </select>

                                        </div>
                                        <div class="col col m1 s12 mt-1">
                                            <button class="btn waves-effect waves-light submit" onclick="filter();">Cari <i class="material-icons right">file_download</i></button>
                                        </div>
                                        <div  class="col col m2 s6 mt-1" id="export_button">
                                            <button class="btn waves-effect waves-light right submit mt-2" onclick="exportExcel();">Excel<i class="material-icons right">view_list</i></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
                <div class="row" id="table_laporan">
                    <table class="bordered" style="font-size:10px;">
                        <thead id="t_head">
                            <tr>
                                <th class="center-align">{{ __('translations.no') }}.</th>
                                <th class="center-align">{{ __('translations.date') }}</th>
                                <th class="center-align">{{ __('translations.plant') }}</th>
                                <th class="center-align">{{ __('translations.warehouse') }}</th>
                                <th class="center-align">{{ __('translations.code') }}</th>
                                <th class="center-align">Nama Item</th>
                                <th class="center-align">{{ __('translations.unit') }}</th>
                                <th class="center-align">Area</th>
                                <th class="center-align">Shading</th>
                                <th class="center-align">Batch Produksi</th>
                                <th class="center-align">No. Dokumen</th>
                                <th class="center-align">{{ __('translations.qty') }}</th>
                                <th class="center-align">Harga </th>
                                <th class="center-align">{{ __('translations.total') }}</th>
                                <th class="center-align">Cumulative Qty.</th>
                                <th class="center-align">Cumulative Value</th>
                            </tr>
                        </thead>
                        <tbody id="table_body">
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        $('#type').on('change', function () {
            var selectedType = $(this).val();

            if (selectedType === 'final') {
                $('#start_date').prop('disabled', true);

            } else {
                $('#start_date').prop('disabled', false);

            }
        });
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });
        select2ServerSide('#item_id', '{{ url("admin/select2/simple_item") }}');
    });
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
                loadingClose('#main');
                if(response.status == 200) {
                    $('#table_body').empty();


                    if (response.message.length > 0) {

                        if($('#type').val() == 'final'){
                            $('#t_head').empty();
                            $('#t_head').append(`
                                <tr>
                                    <th class="center-align">{{ __('translations.no') }}.</th>
                                    <th class="center-align">{{ __('translations.plant') }}</th>
                                    <th class="center-align">{{ __('translations.warehouse') }}</th>
                                    <th class="center-align">{{ __('translations.code') }}</th>
                                    <th class="center-align">Nama Item</th>
                                    <th class="center-align">{{ __('translations.unit') }}</th>
                                    <th class="center-align">Area</th>
                                    <th class="center-align">Shading</th>
                                    <th class="center-align">Batch Produksi</th>
                                    <th class="center-align">Cumulative Qty.</th>
                                    <th class="center-align">Cumulative Value</th>
                                </tr>`);
                            $.each(response.message, function(i, val) {

                                $('#table_body').append(`
                                    <tr>
                                        <td class="center-align">`+(i+1)+`</td>
                                        <td >`+val.plant+`</td>
                                        <td >`+val.warehouse+`</td>
                                        <td >`+val.kode+`</td>
                                        <td >`+val.item+`</td>
                                        <td class="center-align">`+val.satuan+`</td>
                                        <td class="center-align">`+val.area+`</td>
                                        <td class="center-align">`+val.shading+`</td>
                                        <td class="center-align">`+val.production_batch+`</td>
                                        <td class="right-align">`+val.cum_qty+`</td>
                                        <td class="right-align">`+val.cum_val+`</td>
                                    </tr>
                                `);
                            });
                            $('#table_body').append(`
                                <tr>
                                    <td class="center-align" colspan="6"></td>
                                    <td class="center-align">Total</td>
                                    <td class="right-align">`+response.alltotal+`</td>
                                </tr>
                            `);
                        }else{
                            var processedItems = [];
                            $('#t_head').empty();
                            $('#t_head').append(`
                                <tr>
                                    <th class="center-align">{{ __('translations.no') }}.</th>
                                    <th class="center-align">{{ __('translations.date') }}</th>
                                    <th class="center-align">{{ __('translations.plant') }}</th>
                                    <th class="center-align">{{ __('translations.warehouse') }}</th>
                                    <th class="center-align">{{ __('translations.code') }}</th>
                                    <th class="center-align">Nama Item</th>
                                    <th class="center-align">{{ __('translations.unit') }}</th>
                                    <th class="center-align">Area</th>
                                    <th class="center-align">Shading</th>
                                    <th class="center-align">Batch Produksi</th>
                                    <th class="center-align">No. Dokumen</th>
                                    <th class="center-align">{{ __('translations.qty') }}</th>
                                    <th class="center-align">Harga </th>
                                    <th class="center-align">{{ __('translations.total') }}</th>
                                    <th class="center-align">Cumulative Qty.</th>
                                    <th class="center-align">Cumulative Value</th>
                                </tr>`);
                                var itung = 0;
                                $.each(response.message, function(i, val) {
                                if(val.perlu == 1){
                                    $('#table_body').append(`
                                        <tr>
                                            <td class="center-align">`+(itung+1)+`</td>
                                            <td class="center-align"></td>
                                            <td class="center-align"></td>
                                            <td class="center-align"></td>
                                            <td class="center-align">` + val.kode + `</td>
                                            <td class="">` + val.item + `</td>
                                            <td class="center-align">` + val.satuan + `</td>
                                            <td class="center-align"></td>
                                            <td class="center-align"></td>
                                            <td class="center-align"></td>
                                            <td class="center-align">Saldo Awal</td>
                                            <td class="center-align"></td>
                                            <td class="center-align"></td>
                                            <td class="center-align"></td>
                                            <td class="right-align">` + val.last_qty + `</td>
                                            <td class="right-align">` + val.last_nominal + `</td>
                                        </tr>`
                                    );
                                    itung++;
                                }else{
                                    $('#table_body').append(`
                                        <tr>
                                            <td class="center-align"></td>
                                            <td >`+val.date+`</td>
                                            <td >`+val.plant+`</td>
                                            <td >`+val.warehouse+`</td>
                                            <td >`+val.kode+`</td>
                                            <td >`+val.item+`</td>
                                            <td class="center-align">`+val.satuan+`</td>
                                            <td class="center-align">`+val.area+`</td>
                                            <td class="center-align">`+val.shading+`</td>
                                            <td class="center-align">`+val.production_batch+`</td>
                                            <td >`+val.document+`</td>
                                            <td class="right-align">`+val.qty+`</td>
                                            <td class="right-align">`+val.final+`</td>
                                            <td class="right-align">`+val.total+`</td>
                                            <td class="right-align">`+val.cum_qty+`</td>
                                            <td class="right-align">`+val.cum_val+`</td>
                                        </tr>
                                    `);
                                }

                            });

                        }

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
                loadingClose('#main');
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
        var warehouse = $('#warehouse').val();
        var item = $('#item_id').val() ? $('#item_id').val():'';
        var type = $('#type').val() ? $('#type').val():'';
        var group = $('#filter_group').val() ? $('#filter_group').val():'';
        var startdate = $('#start_date').val() ? $('#start_date').val():'';
        var finishdate = $('#finish_date').val() ? $('#finish_date').val():'';
        window.location = "{{ Request::url() }}/export?plant=" + plant + "&warehouse=" + warehouse+"&item=" + item+"&start_date=" + startdate+"&finish_date=" + finishdate+ "&type=" + type+ "&group=" + group;
    }
</script>
