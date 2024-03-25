<style>
    .modal {
        top:0px !important;
    }
</style>
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
                            <li class="breadcrumb-item active">{{ Str::title(str_replace('_',' ',Request::segment(3))) }}
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12">
            <!-- Search for small screen-->
            <div class="container">
                <div class="section section-data-tables">
                    <div class="row">
                        <div class="col s12">
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">
                                        Progress Purchasing 
                                    </h4>
                                    <form class="row" id="form_data" onsubmit="return false;">
                                        <div class="col s12">
                                            <div id="validation_alert" style="display:none;"></div>
                                        </div>
                                        <div class="col s12">
                                            <div class="row">
                                                <div class="input-field col m4 s12">
                                                    <input id="start_date" name="start_date" type="date" placeholder="Tgl. posting" value="{{ date('Y-m').'-01' }}">
                                                    <label class="active" for="start_date">Tanggal Awal</label>
                                                </div>
                                                <div class="input-field col m4 s12">
                                                    <input id="end_date" name="end_date" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                                    <label class="active" for="end_date">Tanggal Akhir</label>
                                                </div>
                                                <div class="col m4 s12 mt-3">
                                                    <button class="btn waves-effect waves-light right submit" onclick="exportExcel();">Export <i class="material-icons right">file_download</i></button>
                                                    <button class="btn waves-effect waves-light right cyan submit mr-2" onclick="filter();">Process <i class="material-icons right">list</i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="card" id="show-result" style="display:none;">
                                <div class="card-content">
                                    <div class="row">
                                        <div class="col s12" >

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="content-result">
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- END: Page Main-->
<script>
     
    function exportExcel(){
        var tipe = $('#type').val();
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        var mode = $('#mode').val();
        window.location = "{{ URL::to('/') }}/admin/"+tipe+"/export?start_date=" + startDate + "&end_date=" + endDate + "&mode=" + mode;
    }

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
                $('#validation_alert').html('');
                loadingOpen('#main-display');
            },
            success: function(response) {
                loadingClose('#main-display');
                if(response.status == 200) {
                    $('#content-result').html('');
                    $('#content-result').append(response.message);
                    // if(response.message.length > 0){
                    //     $('#content-result').append(`<tr>`);
                    //     $.each(response.message, function(i, item_req) {
                    //         $('#content-result').append(
                    //             `<td rowspan="`+response.rowspan+`">`+item_req['item']+`<td>`
                    //             `<td rowspan="`+response.rowspan+`">`+item_req['item_code']+`<td>`
                    //             `<td rowspan="`+response.rowspan+`">`+item_req['ir_code']+`<td>`
                    //             `<td rowspan="`+response.rowspan+`">`+item_req['ir_date']+`<td>`
                    //             `<td rowspan="`+response.rowspan+`">`+item_req['ir_qty']+`<td>`
                    //         );
                    //         if(item_req['pr'].length > 0){
                    //             $('#content-result').append(
                    //                 `<td rowspan="`+$item_req['pr'][0]['po'].length+`">`+$item_req['pr'][0]['pr_code']+`<td>`
                    //                 `<td rowspan="`+$item_req['pr'][0]['po'].length+`">`+$item_req['pr'][0]['pr_date']+`<td>`
                    //                 `<td rowspan="`+$item_req['pr'][0]['po'].length+`">`+$item_req['pr'][0]['pr_qty']+`<td>`
                    //             );
                                
                    //             $.each(item_req['pr'], function(i, pr) {
                    //                 if(i == 0){
                    //                     $('#content-result').append(
                    //                         `<td>`+item_req['item']+`<td>`
                    //                         `<td>`+item_req['item_code']+`<td>`
                    //                         `<td>`+item_req['ir_code']+`<td>`
                    //                         `<td>`+item_req['ir_date']+`<td>`
                    //                         `<td>`+item_req['ir_qty']+`<td>`
                    //                     );
                    //                 }
                                    
                    //             });
                    //         }else{
                    //             $('#content-result').append(
                    //                 `<td><td>`
                    //                 `<td><td>`
                    //                 `<td><td>`
                    //                 `<td><td>`
                    //                 `<td><td>`
                    //                 `<td><td>`
                    //                 `<td><td>`
                    //                 `<td><td>`
                    //                 `<td><td>`
                    //             );
                    //             span_ir = 1;
                    //         }
                    //         $('#content-result').append(`
                    //             </tr>
                    //         `);
                    //     });
                       
                    // }else{
                        
                    // }
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

    /* function getOutstanding(){
        $('#show-result').hide();
        $('#content-result').html('');
        var tipekuy = $('#type').val();
        var startDatekuy = $('#start_date').val();
        var endDatekuy = $('#end_date').val();

        $.ajax({
            url: "{{ URL::to('/') }}/admin/" + tipekuy + "/get_outstanding",
            type: 'POST',
            dataType: 'JSON',
            data: {
                type: tipekuy,
                startDate: startDatekuy,
                endDate: endDatekuy,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('#main');
            },
            success: function(response) {
                loadingClose('#main');
                if(response.status == '200'){
                    $('#show-result').show();
                    $('#content-result').html(response.content);
                }else{
                    swal({
                        title: 'Ups!',
                        text: response.message,
                        icon: 'error'
                    });
                }
            },
            error: function(xhr, status) {
                loadingClose('#main');
                if(xhr.status == '404'){
                    swal({
                        title: 'Mohon maaf!',
                        text: 'Laporan Tunggakan pada Modul ' + $( "#type option:selected" ).text() + ' belum siap. Sementara hanya untuk Permintaan Pembelian dan Order Pembelian',
                        icon: 'warning'
                    });
                }else{
                    swal({
                        title: 'Ups!',
                        text: 'Check your internet connection.',
                        icon: 'error'
                    });
                }
            }
        });
    } */

    function printPreview(code,aslicode){
        swal({
            title: "Apakah Anda ingin mengeprint dokumen ini?",
            text: "Dengan Kode "+aslicode,
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                $.ajax({
                    url: '{{ Request::url() }}/print_individual/' + code,
                    type:'GET',
                    beforeSend: function() {
                        loadingOpen('.modal-content');
                    },
                    complete: function() {
                        
                    },
                    success: function(data){
                        loadingClose('.modal-content');
                        printService.submit({
                            'type': 'INVOICE',
                            'url': data
                        })
                    }
                });  
            }
        });
        
    }

    function exportExcel(){
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        window.location = "{{ Request::url() }}/export?start_date=" + startDate + "&end_date=" + endDate;
    }
</script>