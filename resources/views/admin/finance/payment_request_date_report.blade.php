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
                    <div class="card">
                        <div class="card-content">
                            <h4 class="card-title">
                                Rekap Payment Request Date Report
                            </h4>
                            <form class="row" id="form_data" onsubmit="return false;">
                                <div class="col s12">
                                    <div id="validation_alert" style="display:none;"></div>
                                </div>
                                <div class="col s12">
                                    <div class="row">
                                        <div class="input-field col m6 s12">
                                            <label for="filter_payment_request" class="active" style="font-size:1rem;">Filter Invoice :</label>
                                           
                                            <select class="select2 browser-default" multiple="multiple" id="filter_payment_request" name="filter_payment_request[]">
                                               
                                            </select>
                                           
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <input id="start_date" name="start_date" type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. posting" value="{{ date('Y-m').'-01' }}">
                                            <label class="active" for="start_date">Tanggal Awal</label>
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <input id="end_date" name="end_date"  type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                            <label class="active" for="end_date">Tanggal Akhir</label>
                                        </div>
                                        <div class="col s12 mt-3">
                                            
                                            <button class="btn waves-effect waves-light right submit" onclick="exportExcel();">Get Rekap <i class="material-icons right">file_download</i></button>
                                            <button class="btn waves-effect waves-light right cyan submit mr-2" onclick="Filter();" id="btn_out">Cari <i class="material-icons right">list</i></button>

                                        </div>
                                        
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div id="show_detail">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- END: Page Main-->
<script>
    $(document).ready(function() {
        select2ServerSide('#filter_payment_request', '{{ url("admin/select2/payment_request") }}');
        
    });
    function exportExcel(){
        
        var search = $('#start_date').val();
        var status = $('#end_date').val();
        var filter_payment_request = $('#filter_payment_request').val();
        
        window.location = "{{ Request::url() }}/export?start_date=" + search + "&end_date=" + status+"&filter_payment_request=" + filter_payment_request;
       
       
    }
    function Filter(){
        var formData = new FormData($('#form_data')[0]);
        $('#show_detail').empty();
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
                $('#show_detail').html(response);
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