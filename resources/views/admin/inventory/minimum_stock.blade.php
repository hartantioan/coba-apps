<style>
    .select-wrapper, .select2-container {
        height:3.7rem !important;
    }
    .modal {
        top:0px !important;
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
                                            <select class="form-control" id="plant" name="plant">
                                                <option value="all">SEMUA</option>
                                                @foreach ($place as $row)
                                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                                @endforeach
                                            </select>
                                            <label class="" for="plant">Plant</label>
                                           
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
                                            <select class="browser-default" id="item_id" name="item_id"></select>
                                            <label for="item_id" class="active" style="font-size:1rem;">Item :</label>
                                        </div>
                                        <div class="input-field col m4 s6 ">
                                            <label for="filter_group" class="active" style="font-size:1rem;">Filter Group :</label>
                                            <select class="select2 browser-default" multiple="multiple" id="filter_group" name="filter_group[]">
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
                                        <div class="col m3 mt-2">
                                            <button class="btn waves-effect waves-light submit" onclick="filter();">Cari <i class="material-icons right">file_download</i></button>
                                        </div>
                                        <div class="col m3 mt-2" id="export_button">
                                            <button class="btn waves-effect waves-light right submit mt-2" onclick="exportExcel();">Excel<i class="material-icons right">view_list</i></button>
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
                                <th rowspan="2" class="center-align">No.</th>
                                <th rowspan="2" class="center-align">Plant</th>
                                <th rowspan="2" class="center-align">Gudang</th>
                                <th rowspan="2" class="center-align">Kode Item</th>
                                <th rowspan="2" class="center-align">Nama Item</th>
                                <th colspan="3" class="center-align">Stock</th>
                                <th rowspan="2" class="center-align">Required</th>
                                <th rowspan="2" class="center-align">Satuan</th>
                                <th rowspan="2" class="center-align">Detail</th>
                            </tr>
                            <tr>
                                <th class="center-align">Minimum</th>
                                <th class="center-align">Maximum</th>
                                <th class="center-align">Qty</th>
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

<div id="modal2" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="detail">
                <table class="bordered" style="font-size:10px;">
                    <thead>
                        <tr>
                            <th class="center-align">No.</th>
                            <th class="center-align">Plant</th>
                            <th class="center-align">Warehouse</th>
                            <th class="center-align">Stock</th>
            
                        </tr>
                    </thead>
                    <tbody id="table_body_warehouse">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<script>
    $('#export_button').hide();
    $(function() {
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        $('#modal2').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#table_body_warehouse').empty();
            }
        });

        select2ServerSide('#item_id', '{{ url("admin/select2/item") }}');
    });
 
    function filter(){
      
        var formData = new FormData($('#form_data')[0]);
        formData.append('item_id',$('#item_id').val());
        formData.append('warehouse',$('#warehouse').val());
        formData.append('plant',$('#plant').val());
        formData.append('item_group_id[]',$('#filter_group').val());
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
                            console.log(val);
                            if(val.perlu  == 1){
                                var butn =`<td >
                                        <button class="mb-6 btn-floating waves-effect waves-light gradient-45deg-amber-amber" onclick="detail('`+val.item_id+`')">
                                        <i class="material-icons">search</i>
                                        </button>
                                    </td>`
                            }else{
                                var butn =`<td >
                                    <button class="mb-6 btn-floating waves-effect waves-light gradient-45deg-amber-amber" onclick="detail('`+val.item_id+`')" disabled>
                                    <i class="material-icons">search</i>
                                    </button>
                                </td>`
                            }   
                            $('#table_body').append(`
                                <tr>
                                    <td class="center-align">`+(i+1)+`</td>
                                    <td >`+val.plant+`</td>
                                    <td >`+val.gudang+`</td>
                                    <td >`+val.kode+`</td>
                                    <td >`+val.item+`</td>
                                    <td >`+val.minimum+`</td>
                                    <td >`+val.maximum+`</td>
                                    <td >`+val.final+`</td>
                                    <td >`+val.needed+`</td>
                                    <td >`+val.satuan+`</td>
                                    `+butn+`
                                </tr>
                            `);
                        });
                        $('#table_body').append(`
                                <tr>
                                    <td class="center-align" colspan="4">`+response.time+`</td>
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

    function detail(id){
        $.ajax({
            url: '{{ Request::url() }}/show_detail',
            type: 'POST',
            dataType: 'JSON',
            data: {
                id: id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('#main');
            },
            success: function(response) {
                loadingClose('#main');
                console.log(response);
                $.each(response.message, function(i, val) {
                    $('#table_body_warehouse').append(`
                        <tr>
                            <td class="center-align">`+(i+1)+`</td>
                            <td >`+val.plant+`</td>
                            <td >`+val.nama+`</td>
                            <td >`+val.stock+`</td>
                        </tr>
                    `);
                });
                $('#modal2').modal('open');
               
                M.updateTextFields();
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
        var item_group_id = $('#filter_group').val();
        var item_id = $('#item_id').val();
        window.location = "{{ Request::url() }}/export?plant=" + plant + "&warehouse=" + warehouse+ "&item_id=" + item_id+ "&item_group_id=" + item_group_id
    }
</script>