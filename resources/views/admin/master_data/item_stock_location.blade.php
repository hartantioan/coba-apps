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
                    <div class="card ">
                        <div class="card-content">
                            <form class="row" id="form_data" onsubmit="return false;">
                                <div class="col s12">
                                    <div id="validation_alert" style="display:none;"></div>
                                </div>
                                <div class="col s12">
                                    <div class="row">
                                        <div class="input-field col m3 s12">
                                            <select class="browser-default" id="place_id" name="place_id">
                                                <option value="">--Pilih--</option>
                                                @foreach ($place as $rowplace)
                                                    <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <select class="browser-default" id="warehouse_id" name="warehouse_id">
                                                <option value="">--Pilih--</option>
                                                @foreach ($warehouse as $rowwarehouse)
                                                    <option value="{{ $rowwarehouse->id }}">{{ $rowwarehouse->code }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="input-field col m3 s12">
                                        </div>
                                        <div class="col m3 mt-2">
                                            <button class="btn waves-effect waves-light submit" onclick="filter();">Cari <i class="material-icons right">file_download</i></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                </div>
                <div class="row">
                    <form id="form_body">
                        <table class="bordered" style="font-size:10px;">
                            <thead>
                                <tr>
                                    <th class="center-align">No.</th>
                                    <th class="center-align">Item</th>
                                    <th class="center-align">Stock</th>
                                    <th class="center-align">Plant</th>
                                    <th class="center-align">Gudang</th>
                                    <th class="center-align">Area</th>
                                    <th class="center-align">Shading</th>
                                    <th class="center-align">UOM</th>
                                    <th class="center-align">Lokasi</th>
                                    <th class="center-align">Action</th>
                                </tr>
                            
                            </thead>
                            
                                <tbody id="table_body">
                                </tbody>
                            
                        </table>
                    </form>
                    <div id="saveall_button">
                        <button class="btn waves-effect waves-light right submit mt-2" onclick="saveAll();">SAVE ALL<i class="material-icons right">unarchive</i></button>
                    </div>
                    <div id="export_button">
                        <button class="btn waves-effect waves-light right submit mt-2" onclick="exportExcel();">Excel<i class="material-icons right">view_list</i></button>
                    </div>
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
    var item_stock_id = null;
    $('#export_button').hide();
    $('#saveall_button').hide();
    
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
                $('#saveall_button').show();
                loadingClose('.modal-content');
                if(response.status == 200) {
                    $('#table_body').empty();            
                    if (response.message.length > 0) {
                        item_stock_id = response.item_stock_id;
                        $.each(response.message, function(i, val) {  
                            console.log(val);
                            
                            var butn =`<td >
                                <button class="mb-6 btn-floating waves-effect waves-light gradient-45deg-green-teal" onclick="save1('`+val.item_id+`')">
                                <i class="material-icons">sd_card</i>
                                </button>
                            </td>`;

                            
                            $('#table_body').append(`
                                <tr>
                                    <td class="center-align">`+(i+1)+`</td>
                                    <td >`+val.item+`</td>
                                    <td >`+val.stock+`</td>
                                    <td >`+val.plant+`</td>
                                    <td >`+val.gudang+`</td>
                                    <td >`+val.area+`</td>
                                    <td >`+val.shading+`</td>
                                    <td >`+val.satuan+`</td>
                                    <td ><input id="arr_loc`+val.item_id+`" name="arr_loc[]" type="text" value="`+val.location+`"></td>
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

    function save1(id){
        console.log(item_stock_id);
        var lokasi = $('#arr_loc'+id).val();
        $.ajax({
            url: '{{ Request::url() }}/save1',
            type: 'POST',
            dataType: 'JSON',
            data: {
                id: id,
                lokasi:lokasi,
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

    function saveAll(){
        var formData = new FormData($('#form_body')[0]);
        console.log(formData);
        formData.append('item_stock_id',item_stock_id);
        $.ajax({
            url: '{{ Request::url() }}/saveAll',
            type: 'POST',
            dataType: 'JSON',
            data:formData,
            contentType: false,
            processData: false,
            cache: true,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('#main');
            },
            success: function(response) {
                loadingClose('#main');
                console.log(response);
              
               
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
        var plant = $('#place_id').val();
        var warehouse = $('#warehouse_id').val();
        
        window.location = "{{ Request::url() }}/export?plant=" + plant + "&warehouse=" + warehouse
    }
</script>