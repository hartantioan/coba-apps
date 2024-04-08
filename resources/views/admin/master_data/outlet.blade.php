<style>
    .modal {
        top:0px !important;
    }

    .select-wrapper, .select2-container {
        height:3.9rem !important;
    }

    body.tab-active input:focus {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .modal-content .select2.tab-active {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
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
        </div>
        <div class="col s12">
            <div class="container">
                <div class="section section-data-tables">
                    <!-- DataTables example -->
                    <div class="row">
                        <div class="col s12">
                            <div class="card-panel">
                                <div class="row">
                                    <div class="col s12 ">
                                        <label for="filter_status" style="font-size:1.2rem;">Filter Status :</label>
                                        <div class="input-field inline" style="margin-top: 0;margin-bottom: 0;">
                                            <select class="form-control" id="filter_status" onchange="loadDataTable()">
                                                <option value="">Semua</option>
                                                <option value="1">Aktif</option>
                                                <option value="2">Non-Aktif</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">List Data</h4>
                                    <div class="row">
                                        <div class="col s12">
                                            <div id="datatable_buttons"></div>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">Refresh</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <table id="datatable_serverside" >
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Pengguna</th>
                                                        <th>Code</th>
                                                        <th>Nama</th>
                                                        <th>Tipe</th>
                                                        <th>Alamat</th>
                                                        <th>Telepon</th>
                                                        <th>Provinsi</th>
                                                        <th>Kota</th>
                                                        <th>Kecamatan</th>
                                                        <th>Kelurahan</th>
                                                        <th>Gmap</th>
                                                        <th>Status</th>
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
                        <div class="input-field col s3">
                            <input type="hidden" id="temp" name="temp">
                            <input id="code" name="code" type="text" placeholder="Kode">
                            <label class="active" for="code">Kode</label>
                        </div>
                        <div class="input-field col s3">
                            <input id="name" name="name" type="text" placeholder="Nama">
                            <label class="active" for="name">Nama</label>
                        </div>
                        <div class="input-field col s3">
                            <select class="browser-default" id="type" name="type">
                                <option value="1">Supermarket</option>
                                <option value="2">Hypermarket</option>
                                <option value="3">Minimarket</option>
                                <option value="4">Koperasi</option>
                                <option value="5">Toko Online</option>
                                <option value="6">Marketplace</option>
                                <option value="7">Institusi</option>
                            </select>
                            <label class="active" for="type">Tipe</label>
                        </div>
                        <div class="input-field col s3">
                            <input id="phone" name="phone" type="text" placeholder="Telepon">
                            <label class="active" for="phone">Telepon</label>
                        </div>
                        <div class="input-field col s3">
                            <textarea id="address" name="address" class="materialize-textarea" placeholder="Alamat Lengkap"></textarea>
                            <label class="active" for="address">Alamat Lengkap</label>
                        </div>
                        <div class="input-field col s3">
                            <select class="browser-default" id="province_id" name="province_id" onchange="getCity();"></select>
                            <label class="active" for="province_id">Provinsi</label>
                        </div>
                        <div class="input-field col s3">
                            <select class="select2 browser-default" id="city_id" name="city_id" onchange="getDistrict();">
                                <option value="">--Pilih ya--</option>
                            </select>
                            <label class="active" for="city_id">Kota</label>
                        </div>
                        <div class="input-field col s3">
                            <select class="select2 browser-default" id="district_id" name="district_id" onchange="getSubdistrict();">
                                <option value="">--Pilih ya--</option>
                            </select>
                            <label class="active" for="district_id">Kecamatan</label>
                        </div>
                        <div class="input-field col s3">
                            <select class="select2 browser-default" id="subdistrict_id" name="subdistrict_id">
                                <option value="">--Pilih ya--</option>
                            </select>
                            <label class="active" for="subdistrict_id">Kelurahan</label>
                        </div>
                        <div class="input-field col s3">
                            <input id="link_gmap" name="link_gmap" type="text" placeholder="Link Google Map">
                            <label class="active" for="link_gmap">Link Google Map</label>
                        </div>
                        <div class="input-field col s3">
                            <div class="switch mb-1">
                                <label for="order">Status</label>
                                <label>
                                    Non-Active
                                    <input checked type="checkbox" id="status" name="status" value="1">
                                    <span class="lever"></span>
                                    Active
                                </label>
                            </div>
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

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div>

<!-- END: Page Main-->
<script>
    document.addEventListener('focusin', function (event) {
        const select2Container = event.target.closest('.modal-content .select2');
        const activeSelect2 = document.querySelector('.modal-content .select2.tab-active');
        if (event.target.closest('.modal-content')) {
            document.body.classList.add('tab-active');
        }
        
        
        if (activeSelect2 && !select2Container) {
            activeSelect2.classList.remove('tab-active');
        }

        
        if (select2Container) {
            select2Container.classList.add('tab-active');
        }
    });

    document.addEventListener('mousedown', function () {
        const activeSelect2 = document.querySelector('.modal-content .select2.tab-active');
        document.body.classList.remove('tab-active');
        if (activeSelect2) {
            activeSelect2.classList.remove('tab-active');
        }
    });
    $(function() {
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
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
                M.updateTextFields();
                $('#previewUrl').html();
                $('#province_id,#subdistrict_id,#district_id,#city_id').empty().append(`
                    <option value="">--Pilih ya--</option>
                `);
            }
        });

        select2ServerSide('#province_id', '{{ url("admin/select2/province") }}');

    });

    function getCity(){
        $('#city_id,#subdistrict_id,#district_id').empty().append(`
            <option value="">--Pilih ya--</option>
        `);
        if($('#province_id').val()){
            city = $('#province_id').select2('data')[0].cities;
            $.each(city, function(i, val) {
                $('#city_id').append(`
                    <option value="` + val.id + `">` + val.name + `</option>
                `);
            });
        }else{
            city = [];
            district = [];
            subdistrict = [];
        }
    }

    function getDistrict(){
        $('#subdistrict_id,#district_id').empty().append(`
            <option value="">--Pilih ya--</option>
        `);
        if($('#city_id').val()){
            let index = -1;

            $.each(city, function(i, val) {
                if(val.id == $('#city_id').val()){
                    index = i;
                }
            });

            $.each(city[index].district, function(i, value) {
                $('#district_id').append(`
                    <option value="` + value.id + `" data-subdistrict='` + JSON.stringify(value.subdistrict) + `'>` + value.name + `</option>
                `);
            });
        }else{
            district = [];
            subdistrict = [];
        }
    }

    function getSubdistrict(){
        $('#subdistrict_id').empty().append(`
            <option value="">--Pilih ya--</option>
        `);
        if($('#district_id').val()){
            
            let index = -1;

            $.each(city, function(i, val) {
                if(val.id == $('#city_id').val()){
                    index = i;
                }
            });

            $.each(city[index].district, function(i, value) {
                if(value.id == $('#district_id').val()){
                    subdistrict = value.subdistrict;
                }
            });

            $.each(subdistrict, function(i, value) {
                $('#subdistrict_id').append(`
                    <option value="` + value.id + `">` + value.name + `</option>
                `);
            });
        }else{
            subdistrict = [];
        }
    }

    function loadDataTable() {
		window.table = $('#datatable_serverside').DataTable({
            "scrollCollapse": true,
            "scrollY": '400px',
            "responsive": false,
            "scrollX": true,
            "stateSave": true,
            "serverSide": true,
            "deferRender": true,
            "destroy": true,
            "iDisplayInLength": 10,
            "order": [[0, 'desc']],
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    status : $('#filter_status').val()
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
                { name: 'user_id', className: '' },
                { name: 'code', className: '' },
                { name: 'name', className: '' },
                { name: 'type', className: '' },
                { name: 'address', className: '' },
                { name: 'phone', className: '' },
                { name: 'province', className: '' },
                { name: 'city', className: '' },
                { name: 'district', className: '' },
                { name: 'subdistrict', className: '' },
                { name: 'link_gmap', className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
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
                $('#code').val(response.code);
                $('#name').val(response.name);
                $('#type').val(response.type);
                $('#address').val(response.address);
                $('#phone').val(response.phone);
                $('#link_gmap').val(response.link_gmap);
                if(response.status == '1'){
                    $('#status').prop( "checked", true);
                }else{
                    $('#status').prop( "checked", false);
                }
                $('#province_id').empty().append(`<option value="` + response.province_id + `">` + response.province_name + `</option>`);
                $('#subdistrict_id,#district_id,#city_id').empty().append(`
                    <option value="">--Pilih ya--</option>
                `);
                $.each(response.cities, function(i, val) {
                    $('#city_id').append(`
                        <option value="` + val.id + `">` + val.name + `</option>
                    `);
                });
                $('#city_id').val(response.city_id).formSelect();
                let index = -1;
                $.each(response.cities, function(i, val) {
                    if(val.id == response.city_id){
                        index = i;
                    }
                });
                if(index >= 0){
                    $.each(response.cities[index].district, function(i, value) {
                        let selected = '';
                        $('#district_id').append(`
                            <option value="` + value.id + `" ` + (value.id == response.district_id ? 'selected' : '') + ` data-subdistrict='` + JSON.stringify(value.subdistrict) + `'>` + value.name + `</option>
                        `);
                        if(value.id == response.district_id){
                            subdistrict = value.subdistrict;
                        }
                    });

                    $.each(subdistrict, function(i, value) {
                        $('#subdistrict_id').append(`
                            <option value="` + value.id + `" ` + (value.id == response.subdistrict_id ? 'selected' : '') + `>` + value.name + `</option>
                        `);
                    });
                }
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