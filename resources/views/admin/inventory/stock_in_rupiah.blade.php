<div id="main">
    <div class="row">
        <div class="pt-3 pb-1" id="breadcrumbs-wrapper">
            <!-- Search for small screen-->
            <div class="container">
                <div class="row">
                    <div class="card ">
                        <div class="card-content">
                            <h4 class="card-title">
                                Pergerakan Inventory 
                            </h4>
                            <form class="row" id="form_data" onsubmit="return false;">
                                <div class="col s12">
                                    <div id="validation_alert" style="display:none;"></div>
                                </div>
                                <div class="col s12">
                                    <div class="row">
                                        <div class="input-field col m3 s12">
                                            <input id="start_date" name="start_date" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                            <label class="active" for="start_date">Tanggal Awal</label>
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <input id="finish_date" name="finish_date"  type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                            <label class="active" for="finish_date">Tanggal Akhir</label>
                                        </div>
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
                                            <select class="browser-default item-array" id="item_id" name="item_id">
                                                
                                            </select>
                                            <label class="active" for="item_id">ITEM</label>
                                        </div>
                                        <div class="col col m3 s12 mt-2">
                                            <button class="btn waves-effect waves-light right submit" onclick="filter();">Cari <i class="material-icons right">file_download</i></button>
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
                                <th class="center-align">Item</th>
                                <th class="center-align">date</th>
                                <th class="center-align">Harga Pokok Penjualan</th>
                                <th class="center-align">Qty Final</th>
                                <th class="center-align">Total Harga Final</th>
                            </tr>
                        </thead>
                        <tbody id="table_body">
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
    $('#export_button').hide();
    $(function() {
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
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
                loadingClose('.modal-content');
                if(response.status == 200) {
                    $('#table_body').empty();            
                    if (response.message.length > 0) {
                        $.each(response.message, function(i, val) {     
                            $('#table_body').append(`
                                <tr>
                                    <td class="center-align">`+(i+1)+`</td>
                                    <td >`+val.item+`</td>
                                    <td >`+val.date+`</td>
                                    <td class="right-align">`+val.final+`</td>
                                    <td class="right-align">`+val.qtyfinal+`</td>
                                    <td class="right-align">`+val.totalfinal+`</td>
                                </tr>
                            `);
                        });
                        $('#table_body').append(`
                            <tr>
                                <td class="center-align" colspan="6">`+response.time+`</td>
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

    function exportExcel(){
        var plant = $('#plant').val();
        var warehouse = $('#warehouse').val();
        var item = $('#item_id').val() ? $('#item_id').val():'';
        var startdate = $('#start_date').val() ? $('#start_date').val():'';
        var finishdate = $('#finish_date').val() ? $('#finish_date').val():'';
        window.location = "{{ Request::url() }}/export?plant=" + plant + "&warehouse=" + warehouse+"&item=" + item+"&start_date=" + startdate+"&finish_date=" + finishdate
    }
</script>