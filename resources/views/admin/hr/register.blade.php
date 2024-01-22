<style>
    .select-wrapper, .select2-container {
        height:3.6rem !important;
    }

    .switch {
        height: 3.45rem !important;
    }

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
                    <div class="col s4 m6 l6">
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
                            <ul class="collapsible collapsible-accordion">
                                <li>
                                    <div class="collapsible-header"><i class="material-icons">filter_list</i> FILTER</div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col s12 ">
                                                <label for="filter_status" style="font-size:1.2rem;">Filter Status :</label>
                                                <div class="input-field inline" style="margin-top: 0;margin-bottom: 0;">
                                                    <select class="form-control" id="filter_status" onchange="loadDataTable()" multiple>
                                                        <option value="1">Menunggu</option>
                                                        <option value="2">Ditolak</option>
                                                        <option value="3">Disetujui</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div> 
                                    </div>
                                </li>
                            </ul>
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">List Data</h4>
                                    <div class="row">
                                        <div class="col s12">
                                            <div class="card-alert card purple">
                                                <div class="card-content white-text">
                                                    <p>Info : Terdapat dua cara pendaftaran, yang pertama adalah berdasarkan inputan form dimana nama lengkap dan user akan muncul sesuai inputan dari pengguna. Yang kedua adalah berdasarkan upload berkas, sehingga berkas tidak akan kosong. HRD wajib merubah data pendaftar jika menggunakan cara yang kedua upload melalui tombol edit.</p>
                                                </div>
                                            </div>
                                            <div id="datatable_buttons"></div>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">Refresh</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <table id="datatable_serverside" class="display responsive-table wrap">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Code</th>
                                                        <th>Nama Lengkap</th>
                                                        <th>Nama Pengguna</th>
                                                        <th>Alamat</th>
                                                        <th>Email</th>
                                                        <th>HP/Telepon</th>
                                                        <th>Berkas</th>
                                                        <th>Status</th>
                                                        <th>Registrasi</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
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

<div id="modal1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Tambah/Edit {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="input-field col s6">
                            <input type="hidden" id="temp" name="temp">
                            <input id="name" name="name" type="text" placeholder="Nama Lengkap">
                            <label class="active" for="name">Nama Lengkap</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="username" name="username" type="text" placeholder="Nama Pengguna">
                            <label class="active" for="username">Nama Pengguna</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="password" name="password" type="text" placeholder="Silahkan kosongi jika tidak ingin dirubah...">
                            <label class="active" for="password">Password</label>
                        </div>
                        <div class="input-field col s6">
                            <textarea id="address" name="address" class="materialize-textarea" placeholder="Alamat lengkap pengguna"></textarea>
                            <label class="active" for="address">Alamat</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="email" name="email" type="email" placeholder="Email">
                            <label class="active" for="email">Email</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="hp" name="hp" type="text" placeholder="HP / Telepon">
                            <label class="active" for="hp">HP / Telepon</label>
                        </div>
                        <div class="input-field col s6">
                            <select id="status" name="status">
                                <option value="1">Menunggu</option>
                                <option value="2">Ditolak</option>
                                <option value="3">Diterima</option>
                            </select>
                            <label for="status">Status</label>
                        </div>
                        <div class="input-field col s6">
                            <div class="switch mb-1">
                                <label for="add_to_user">Tambahkan ke Partner Bisnis</label>
                                <label>
                                    <input type="checkbox" id="add_to_user" name="add_to_user" value="1">
                                    <span class="lever"></span>
                                    Ya
                                </label>
                                <span style="margin-left:25px;">Notifikasi akun untuk login sistem akan dikirimkan ke email terdaftar.</span>
                            </div>
                        </div>
                        <div class="input-field col s4 employee_inputs" style="display:none;">
                            <input id="id_card" name="id_card" type="text" placeholder="No KTP" class="ktp">
                            <label class="active" for="id_card">No KTP / Identitas</label>
                        </div>
                        <div class="input-field col s4 employee_inputs" style="display:none;">
                            <input id="id_card_address" name="id_card_address" type="text" placeholder="Alamat KTP">
                            <label class="active" for="id_card_address">Alamat KTP / Identitas</label>
                        </div>
                        <div class="input-field col s4 employee_inputs" style="display:none;">
                            <input id="tax_id" name="tax_id" type="text" placeholder="No. NPWP" class="npwp">
                            <label class="active" for="tax_id">No. NPWP</label>
                        </div>
                        <div class="input-field col s4 employee_inputs" style="display:none;">
                            <input id="tax_name" name="tax_name" type="text" placeholder="Nama di NPWP">
                            <label class="active" for="tax_name">Nama NPWP</label>
                        </div>
                        <div class="input-field col s4 employee_inputs" style="display:none;">
                            <input id="tax_address" name="tax_address" type="text" placeholder="Alamat di NPWP">
                            <label class="active" for="tax_address">Alamat NPWP</label>
                        </div>
                        <div class="input-field col s4 employee_inputs" style="display:none;">
                            <select id="married_status" name="married_status">
                                <option value="1">Single</option>
                                <option value="2">Menikah</option>
                                <option value="3">Cerai</option>
                            </select>
                            <label for="married_status">Status Pernikahan</label>
                        </div>
                        <div class="input-field col s4 employee_inputs" style="display:none;">
                            <input id="married_date" name="married_date" type="date" max="{{ date('9999'.'-12-31') }}">
                            <label class="active" for="married_date">Tgl.Pernikahan</label>
                        </div>
                        <div class="input-field col s4 employee_inputs" style="display:none;">
                            <input id="children" name="children" type="number" value="0">
                            <label class="active" for="children">Jumlah Anak</label>
                        </div>
                        <div class="input-field col s4 employee_inputs" style="display:none;">
                            <select id="company_id" name="company_id">
                                @foreach($company as $row)
                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                @endforeach
                            </select>
                            <label for="company_id">Perusahaan</label>
                        </div>
                        <div class="input-field col s4 employee_inputs" style="display:none;">
                            <select id="gender" name="gender">
                                <option value="1">Laki-laki</option>
                                <option value="2">Wanita</option>
                                <option value="3">Lainnya</option>
                            </select>
                            <label for="gender">Jenis Kelamin</label>
                        </div>
                        <div class="input-field col s4 employee_inputs" style="display:none;">
                            <input id="limit_credit" name="limit_credit" type="text" value="0" placeholder="Limit Kredit" onkeyup="formatRupiah(this)">
                            <label class="active" for="limit_credit">Limit Kredit Supplier / BS Karyawan</label>
                        </div>
                        <div class="input-field col s4 employee_inputs" style="display:none;">
                            <select class="browser-default" id="province_id" name="province_id" onchange="getCity();"></select>
                            <label class="active" for="province_id">Provinsi</label>
                        </div>
                        <div class="input-field col s4 employee_inputs" style="display:none;">
                            <select class="select2 browser-default" id="city_id" name="city_id" onchange="getDistrict();">
                                <option value="">--Pilih ya--</option>
                            </select>
                            <label class="active" for="city_id">Kota/Kabupaten</label>
                        </div>
                        <div class="input-field col s4 employee_inputs" style="display:none;">
                            <select class="select2 browser-default" id="district_id" name="district_id" onchange="getSubdistrict();">
                                <option value="">--Pilih ya--</option>
                            </select>
                            <label class="active" for="district_id">Kecamatan</label>
                        </div>
                        <div class="input-field col s4 employee_inputs" style="display:none;">
                            <select class="select2 browser-default" id="subdistrict_id" name="subdistrict_id">
                                <option value="">--Pilih ya--</option>
                            </select>
                            <label class="active" for="subdistrict_id">Kelurahan</label>
                        </div>
                        <div class="input-field col s4 employee_inputs" style="display:none;">
                            <select class="browser-default" id="country_id" name="country_id"></select>
                            <label class="active" for="country_id">Negara Asal</label>
                        </div>
                        <div class="input-field col s4 employee_inputs" style="display:none;">
                            <select class="form-control" id="group_id" name="group_id">
                                @foreach ($groups as $row)
                                    <option value="{{ $row->id }}">{{ $row->code.' - '.$row->name }}</option>
                                @endforeach
                            </select>
                            <label for="group_id">Kelompok Partner Bisnis</label>
                        </div>
                        <div class="input-field col s4 employee_inputs" style="display:none;">
                            <select class="form-control" id="place_id" name="place_id">
                                @foreach ($place as $rowplace)
                                    <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                                @endforeach
                            </select>
                            <label class="" for="place_id">Plant (Untuk nomor pegawai)</label>
                        </div>
                        <div class="col s12 mt-3">
                            <button class="btn waves-effect waves-light right submit" onclick="save();">Simpan <i class="material-icons right">send</i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

{{-- <div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div> --}}

<!-- END: Page Main-->
<script>
    $(function() {
        $(".select2").select2({
            dropdownParent: $('#modal1'),
        });

        $('.select2').on('select2:open', function (e) {
            const evt = "scroll.select2";
            $(e.target).parents().off(evt);
            $(window).off(evt);
        });

        loadDataTable();
        
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) {
                $('#code').focus();
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                $('#province_id').empty();
                $('#city_id,#district_id,#subdistrict_id').empty().append(`
                    <option value="">--Pilih ya--</option>
                `);
                $('.employee_inputs').hide();
                M.updateTextFields();
            }
        });

        select2ServerSide('#country_id', '{{ url("admin/select2/country") }}');
        select2ServerSide('#province_id', '{{ url("admin/select2/province") }}');

        $('#add_to_user').click(function(){
            if($(this).is(':checked')){
                $('.employee_inputs').show();
            }else{
                $('.employee_inputs').hide();
            }
        });
    });

    function getCity(){
        $('#city_id,#district_id,#subdistrict_id').empty().append(`
            <option value="">--Pilih ya--</option>
        `);
        if($('#province_id').val()){
            $.each($('#province_id').select2('data')[0].cities, function(i, value) {
                $('#city_id').append(`
                    <option value="` + value.id + `" data-district='` + JSON.stringify(value.district) + `'>` + value.code + ` - `  + value.name + `</option>
                `);
            });
        }
    }

    function getDistrict(){
        $('#district_id,#subdistrict_id').empty().append(`
            <option value="">--Pilih ya--</option>
        `);
        if($('#city_id').val()){
            $.each($("#city_id").select2().find(":selected").data("district"), function(i, value) {
                $('#district_id').append(`
                    <option value="` + value.id + `" data-subdistrict='` + JSON.stringify(value.subdistrict) + `'>` + value.code + ` - ` + value.name + `</option>
                `);
            });
        }
    }

    function getSubdistrict(){
        $('#subdistrict_id').empty().append(`
            <option value="">--Pilih ya--</option>
        `);
        if($('#district_id').val()){
            $.each($("#district_id").select2().find(":selected").data("subdistrict"), function(i, value) {
                $('#subdistrict_id').append(`
                    <option value="` + value.id + `">` + value.code + ` - ` + value.name + `</option>
                `);
            });
        }
    }

    function loadDataTable() {
		window.table = $('#datatable_serverside').DataTable({
            "scrollCollapse": true,
            "scrollY": '400px',
            "responsive": true,
            "stateSave": true,
            "serverSide": true,
            "deferRender": true,
            "destroy": true,
            "iDisplayInLength": 10,
            "fixedColumns": {
                left: 2,
                right: 1
            },
            "order": [[0, 'desc']],
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    'status' : $('#filter_status').val()
                },
                beforeSend: function() {
                    loadingOpen('#datatable_serverside');
                },
                complete: function() {
                    loadingClose('#datatable_serverside');
                },
                error: function() {
                    loadingClose('#datatable_serverside');
                    swal({
                        title: 'Ups!',
                        text: 'Check your internet connection.',
                        icon: 'error'
                    });
                }
            },
            columns: [
                { name: 'id', searchable: false, className: 'center-align details-control' },
                { name: 'code', className: 'center-align' },
                { name: 'name', className: 'center-align' },
                { name: 'username', className: 'center-align' },
                { name: 'address', className: 'center-align' },
                { name: 'email', className: 'center-align' },
                { name: 'hp', className: 'center-align' },
                { name: 'document', searchable: false, orderable: false, className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'registration', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle' 
            ]
        });
        $('.dt-buttons').appendTo('#datatable_buttons');
        
        $('select[name="datatable_serverside_length"]').addClass('browser-default');
	}



    function save(){

        swal({
            title: "Apakah anda yakin simpan?",
            text: "Silahkan periksa kembali data, dan pastikan sudah benar.",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
			
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
                            success();
                            M.toast({
                                html: response.message
                            });
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
            }
        });
    }

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
    }

    function show(id){
        $.ajax({
            url: '{{ Request::url() }}/show',
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
                $('#modal1').modal('open');
                $('#temp').val(id);
                $('#name').val(response.name);
                $('#username').val(response.username);
                $('#address').val(response.address);
                $('#email').val(response.email);
                $('#hp').val(response.hp);
                $('#status').val(response.status).formSelect();
                $('.modal-content').scrollTop(0);
                $('#code').focus();
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

    function destroy(id){
        swal({
            title: "Apakah anda yakin?",
            text: "Anda tidak bisa mengembalikan data yang terhapus!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                $.ajax({
                    url: '{{ Request::url() }}/destroy',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { id : id },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        loadingOpen('#main');
                    },
                    success: function(response) {
                        loadingClose('#main');
                        M.toast({
                            html: response.message
                        });
                        loadDataTable();
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
        });
    }
</script>