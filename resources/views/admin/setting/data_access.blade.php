<style>
    table > thead > tr > th {
        font-size: 13px !important;
    }

    table.bordered th {
        padding: 5px !important;
    }
</style>
<!-- BEGIN: Page Main-->
<div id="main">
    <div class="row">
        <div class="pt-3 pb-1" id="breadcrumbs-wrapper">
            <!-- Search for small screen-->
            <div class="container">
                <div class="row">
                    <div class="col s12 m6 l6">
                        <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ $title }}</span></h5>
                    </div>
                    <div class="col s12 m6 l6 right-align-md">
                        <ol class="breadcrumbs mb-0">
                            <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item"><a href="#">{{ Str::ucfirst(Request::segment(2)) }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{ Str::title(str_replace('_',' ',Request::segment(3))) }}
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12">
            <div class="container">
                <div class="section section-data-tables">
                    <!-- DataTables example -->
                    <div class="row">
                        <div class="col s12">
                            <div class="card">
                                <div class="card-content">
                                    <form class="row" id="form_data" onsubmit="return false;">
                                        <h4 class="card-title">List {{ $title }}</h4>
                                        <div class="card-alert card blue">
                                            <div class="card-content white-text">
                                                <p>Form ini akan menentukan akses setiap pegawai pada pabrik, kantor dan gudang yang dipilih. Akses tersebut berlaku pada form dan tabel transaksi.</p>
                                            </div>
                                        </div>
                                        <div class="row mt-1">
                                            <div class="input-field col s3"></div>
                                            <div class="input-field col s3">
                                                <select class="browser-default select2" id="user_id" name="user_id" onchange="refreshAccess();">
                                                    @foreach ($user as $row)
                                                        <option value="{{ $row->id }}">{{ $row->code.' '.$row->name.' Dept. '.$row->department->name.' Pos. '.$row->position->name }}</option>
                                                    @endforeach
                                                </select>
                                                <label class="active" for="user_id">Pegawai</label>
                                            </div>
                                            <div class="input-field col s3">
                                                <button class="btn waves-effect waves-light" onclick="save();">Simpan <i class="material-icons right">send</i></button>
                                            </div>
                                            <div class="input-field col s3"></div>
                                            <div class="input-field col s12" style="display:none;" id="notifchanged">
                                                <div class="card-alert card red">
                                                    <div class="card-content white-text">
                                                        <p>Anda telah melakukan perubahan. Silahkan tekan save untuk menyimpan.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-1 center-align">
                                            <div class="col s6">
                                                <h5 class="card-title center">Penempatan (Pabrik/Office)</h5>
                                                <table class="bordered centered">
                                                    <thead>
                                                        <tr>
                                                            <th rowspan="2">NO</th>
                                                            <th rowspan="2">NAMA</th>
                                                            <th rowspan="2">TIPE</th>
                                                            <th rowspan="2">PERUSAHAAN</th>
                                                            <th>AKSES</th>
                                                        </tr>
                                                        <tr>
                                                            <th>
                                                                <label>
                                                                    <input type="checkbox" onclick="checkAllPlace(this);" id="check-all-place">
                                                                    <span>Semua</span>
                                                                </label>
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($place as $row)
                                                        <tr>
                                                            <td>{{ $row->code }}</td>
                                                            <td>{{ $row->name }}</td>
                                                            <td>{{ $row->type() }}</td>
                                                            <td>{{ $row->company->name }}</td>
                                                            <td>
                                                                <label>
                                                                    <input type="checkbox" name="checkplace[]" id="checkplace{{ $row->id }}" value="{{ $row->id }}">
                                                                    <span>Pilih</span>
                                                                </label>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="col s6">
                                                <h5 class="card-title center">Gudang</h5>
                                                <table class="bordered centered">
                                                    <thead>
                                                        <tr>
                                                            <th rowspan="2">NO</th>
                                                            <th rowspan="2">NAMA</th>
                                                            <th>AKSES</th>
                                                        </tr>
                                                        <tr>
                                                            <th>
                                                                <label>
                                                                    <input type="checkbox" onclick="checkAllWarehouse(this);" id="check-all-warehouse">
                                                                    <span>Semua</span>
                                                                </label>
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($warehouse as $row)
                                                        <tr>
                                                            <td>{{ $row->code }}</td>
                                                            <td>{{ $row->name }}</td>
                                                            <td>
                                                                <label>
                                                                    <input type="checkbox" name="checkwarehouse[]" id="checkwarehouse{{ $row->id }}" value="{{ $row->id }}">
                                                                    <span>Pilih</span>
                                                                </label>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="content-overlay"></div>
        </div>
    </div>
</div>

<script>
    $(function() {
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        $('input:checkbox').click(function(){
            $('#notifchanged').show();
        });

        refreshAccess();
    });

    function save(){
        swal({
            title: "Apakah anda yakin ingin simpan?",
            text: "Silahkan cek kembali form, dan jika sudah yakin maka lanjutkan!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                var passed = false;
                $('input[name^="checkplace"]').each(function(){
                    if($(this).is(':checked')){
                        passed = true;
                    }
                });

                $('input[name^="checkwarehouse"]').each(function(){
                    if($(this).is(':checked')){
                        passed = true;
                    }
                });

                if(passed == true){
                    var formData = new FormData($('#form_data')[0]);

                    $.ajax({
                        url: '{{ Request::url() }}/create',
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
                            $('#validation_alert').hide();
                            $('#validation_alert').html('');
                            loadingOpen('.modal-content');
                        },
                        success: function(response) {
                            loadingClose('.modal-content');

                            if(response.status == 200) {
                                M.toast({
                                    html: response.message
                                });
                                $('#check-all-place,#check-all-warehouse').prop( "checked", false);
                                $('#notifchanged').hide();
                            } else if(response.status == 422) {
                                $('#validation_alert').show();
                                $('.modal-content').scrollTop(0);
                                
                                swal({
                                    title: 'Ups! Validation',
                                    text: 'Check your form.',
                                    icon: 'warning'
                                });

                                $.each(response.error, function(i, val) {
                                    $.each(val, function(i, val) {
                                        $('#validation_alert').append(`
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
                }else{
                    swal({
                        title: 'Ups! Hayo.',
                        text: 'Mohon setidaknya dipilih satu penempatan / gudang untuk menyimpan.',
                        icon: 'error'
                    });
                }
            }
        });
    }

    function reset(){
        $('#check-all-place,#check-all-warehouse').prop( "checked", false);
        $('input[name^="checkplace"]').each(function(){
            if($(this).is(':checked')){
                $(this).prop( "checked", false);
            }
        });
        $('input[name^="checkwarehouse"]').each(function(){
            if($(this).is(':checked')){
                $(this).prop( "checked", false);
            }
        });
    }

    function checkAllPlace(element){
        if($(element).is(':checked')){
            $('input[name^="checkplace"]').each(function(){
                if(!$(this).is(':checked')){
                    $(this).prop( "checked", true);
                }
            });
        }else{
            $('input[name^="checkplace"]').each(function(){
                if($(this).is(':checked')){
                    $(this).prop( "checked", false);
                }
            });
        }
    }

    function checkAllWarehouse(element){
        if($(element).is(':checked')){
            $('input[name^="checkwarehouse"]').each(function(){
                if(!$(this).is(':checked')){
                    $(this).prop( "checked", true);
                }
            });
        }else{
            $('input[name^="checkwarehouse"]').each(function(){
                if($(this).is(':checked')){
                    $(this).prop( "checked", false);
                }
            });
        }
    }

    function refreshAccess(){
        if($('#user_id').val()){
            reset();
            $.ajax({
                url: '{{ Request::url() }}/refresh',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: $('#user_id').val()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('#main');
                },
                success: function(response) {
                    loadingClose('#main');
                    if(response.places.length > 0){
                        $.each(response.places, function(i, val) {
                            $('#checkplace' + val.id).prop( "checked", true);
                        });
                    }

                    if(response.warehouses.length > 0){
                        $.each(response.warehouses, function(i, val) {
                            $('#checkwarehouse' + val.id).prop( "checked", true);
                        });
                    }
                },
                error: function() {
                    loadingClose('#main');
                    swal({
                        title: 'Ups!',
                        text: 'Check your internet connection.',
                        icon: 'error'
                    });
                }
            });
        }
    }
</script>