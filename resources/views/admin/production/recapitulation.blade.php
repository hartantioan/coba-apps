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
                                        Rekap Produksi 
                                    </h4>
                                    <form class="row" id="form_data" onsubmit="return false;">
                                        <div class="col s12">
                                            <div id="validation_alert" style="display:none;"></div>
                                        </div>
                                        <div class="col s12">
                                            <div class="row">
                                                <div class="input-field col m3 s12">
                                                    <select class="form-control" id="type" name="type">
                                                        @foreach ($menus as $row)
                                                            <option value="{{ $row->fullUrl() }}">{{ $row->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <label class="" for="type">Tipe Module Produksi</label>
                                                </div>
                                                <div class="input-field col m3 s12">
                                                    <select class="form-control" id="mode" name="mode">
                                                        <option value="1">Exclude Deleted Data</option>
                                                        <option value="2">All Data</option>
                                                    </select>
                                                    <label class="" for="mode">Mode Data</label>
                                                </div>
                                                <div class="input-field col m3 s12">
                                                    <input id="start_date" name="start_date" type="date" placeholder="Tgl. posting" value="{{ date('Y-m').'-01' }}">
                                                    <label class="active" for="start_date">Tanggal Awal</label>
                                                </div>
                                                <div class="input-field col m3 s12">
                                                    <input id="end_date" name="end_date" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                                    <label class="active" for="end_date">Tanggal Akhir</label>
                                                </div>
                                                <div class="col s12 mt-3">
                                                    <button class="btn waves-effect waves-light right submit" onclick="exportExcel();">Download Rekap <i class="material-icons right">file_download</i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="card" id="show-result" style="display:none;">
                                <div class="card-content">
                                    <h4 class="card-title">
                                        Daftar Tunggakan Dokumen
                                    </h4>
                                    <div class="row">
                                        <div class="col s12" id="content-result">

                                        </div>
                                    </div>
                                </div>
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

    function getOutstanding(){
        var tipe = $('#type').val();
        window.location = "{{ URL::to('/') }}/admin/"+tipe+"/get_outstanding?";
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
</script>