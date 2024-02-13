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
                                Rekap Inventory 
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
                                            <label class="" for="type">Tipe Module Purchase</label>
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <select class="form-control" id="mode" name="mode">
                                                <option value="1">Exclude Deleted Data</option>
                                                <option value="2">All Data</option>
                                            </select>
                                            <label class="" for="mode">Mode Data</label>
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
                                            <button class="btn waves-effect waves-light right cyan submit mr-2" onclick="getOutstanding();" id="btn_out">Lihat Tunggakan <i class="material-icons right">list</i></button>
                                            <button class="btn waves-effect waves-light right submit" onclick="exportExcel();">Get Rekap <i class="material-icons right">file_download</i></button>
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

<!-- END: Page Main-->
<script>
     $('#btn_out').hide();
    $(document).ready(function() {
        $('#type').change(function() {
            var selectedValue = $(this).val();
           
            if (selectedValue === 'inventory/good_receipt_po') {
                $('#btn_out').show();
            } else {
                $('#btn_out').hide();
            }
        });
    });
    function exportExcel(){
        var tipe = $('#type').val();
        var search = $('#start_date').val();
        var status = $('#end_date').val();
        var mode = $('#mode').val();
        window.location = "{{ URL::to('/') }}/admin/"+tipe+"/export?start_date=" + search + "&end_date=" + status + "&mode=" + mode;
    }

    function getOutstanding(){
        var tipe = $('#type').val();
        window.location = "{{ URL::to('/') }}/admin/"+tipe+"/get_outstanding?";
    }

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