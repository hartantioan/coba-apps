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
            <div class="container">
                <div class="section section-data-tables">
                    <!-- DataTables example -->
                    <div class="row">
                        <div class="col m12 s12 l4">
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">Info Profil Pengguna</h4>
                                    <div class="row" style="margin-top:25px;">
                                        <div class="col m12 s12">
                                            <div class="row">
                                                <div class="col m12 s12 center-align">
                                                    <img class="responsive-img circle z-depth-5" width="120" src="{{ $data->photo() }}"
                                                    alt="">
                                                    <br>
                                                    <a class="waves-effect waves-light btn mt-5 border-radius-4" onclick="alert('kamingsun!')"> Non-Aktifkan</a>
                                                </div>
                                            </div>
                                            <div class="row mt-5">
                                                <div class="col s12"><hr></div>
                                                <div class="col s4">Nama</div><div class="col s8">: {{ $data->name }}</div>
                                                <div class="col s4">NIK</div><div class="col s8">: {{ $data->employee_no }}</div>
                                                <div class="col s4">Username</div><div class="col s8">: {{ $data->username }}</div>
                                                <div class="col s4">Email</div><div class="col s8">: {{ $data->email }}</div>
                                                <div class="col s4">HP</div><div class="col s8">: {{ $data->phone }}</div>
                                                <div class="col s4">Alamat</div><div class="col s8">: {{ $data->address }}</div>
                                                <div class="col s4">Kota</div><div class="col s8">: {{ $data->city()->exists() ? $data->city->name : '' }}</div>
                                                <div class="col s4">Provinsi</div><div class="col s8">: {{ $data->province()->exists() ? $data->province->name : '' }}</div>
                                                <div class="col s12"><hr></div>
                                                <div class="col s4">Perusahaan</div><div class="col s8">: {{ $data->company()->exists() ? $data->company->name : '' }}</div>
                                                <div class="col s4">Penempatan</div><div class="col s8">: {{ $data->place_id ? $data->place->code : '-' }}</div>
                                                <div class="col s4">Departemen</div><div class="col s8">: {{ $data->department_id ? $data->department->name : '-' }}</div>
                                                <div class="col s4">Posisi</div><div class="col s8">: {{ $data->position_id ? $data->position->name : '-' }}</div>
                                                <div class="col s12"><hr></div>
                                                <div class="col s4">Update Pass</div><div class="col s8">: <span class="{{ $data->needChangePassword() ? 'badge red' : 'badge gradient-45deg-light-blue-cyan' }}">{{ $data->last_change_password }}</span></div>
                                                <div class="col s12"><hr></div>
                                                <div class="col s4">Limit Kredit BS</div><div class="col s8">: {{ number_format($data->limit_credit,2,',','.') }}</div>
                                                <div class="col s4">BS Terpakai</div><div class="col s8">: {{ number_format($data->count_limit_credit,2,',','.') }}</div>
                                                <div class="col s4">BS Sisa</div><div class="col s8">: {{ number_format($data->limit_credit - $data->count_limit_credit,2,',','.') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">Tanda Tangan</h4>
                                    <div class="row" style="margin-top:25px;">
                                        <div class="col m12 s12">
                                            <form class="row" id="form_data_sign" onsubmit="return false;">
                                                <div class="file-field input-field col s12">
                                                    <div class="btn">
                                                        <span>Pilih dari File</span>
                                                        <input type="file" name="sign" id="sign" accept="image/x-png,image/jpg,image/jpeg">
                                                    </div>
                                                    <div class="file-path-wrapper">
                                                        <input class="file-path validate" type="text">
                                                        <label class="" for="">Rekomendasi PNG</label>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="col m12 s12">
                                            <hr>
                                        </div>
                                        <div class="col m12 s12 center-align">
                                            ATAU TTD LANGSUNG
                                        </div>
                                        <div class="col m12 s12">
                                            <hr>
                                        </div>
                                        <div class="col m12 s12 center-align">
                                            <canvas id="signature-pad" class="signature-pad" style="border: 3px solid rgb(0, 0, 0);"></canvas>
                                            <center class="mt-3">
                                                <a href="{{ $data->signature ? asset(Storage::url($data->signature)) : asset("website/empty.png") }}" id="preview_image" data-lightbox="Brand" data-title="Preview Image">
                                                    <img src="{{ $data->signature ? asset(Storage::url($data->signature)) : asset("website/empty.png") }}" style="max-width:100%;">
                                                </a>
                                            </center>
                                        </div>
                                        <div class="col m12 s12 center-align">
                                            <button class="waves-effect waves-light btn mb-1 mr-1" onclick="undoSign()">Undo</button>
                                            <button class="waves-effect waves-light btn mb-1 cyan mr-1" onclick="clearSign()">Reset</button>
                                            <button class="waves-effect waves-light btn mb-1 green mr-1" onclick="saveSign()">Save</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col m12 s12 l8">
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">Form Update</h4>
                                    <div class="row" style="margin-top:25px;">
                                        <div class="col s12">
                                            <form class="row" id="form_data" onsubmit="return false;">
                                                <div class="col s12">
                                                    <div id="validation_alert" style="display:none;"></div>
                                                </div>
                                                <div class="col s12">
                                                    <div class="input-field col s6">
                                                        <input id="name" name="name" type="text" placeholder="Nama Alat" value="{{ $data->name }}">
                                                        <label class="active" for="name">Nama</label>
                                                    </div>
                                                    
                                                    <div class="input-field col s6">
                                                        <input id="address" name="address" type="text" placeholder="Alamat" value="{{ $data->address }}">
                                                        <label class="active" for="address">Alamat</label>
                                                    </div>
                                                    <div class="input-field col s6">
                                                        <select class="browser-default" id="province_id" name="province_id">
                                                            @if($data->province_id)
                                                            <option value="{{ $data->province_id }}">{{ $data->province->name }}</option>
                                                            @endif
                                                        </select>
                                                        <label class="active" for="province_id">Provinsi</label>
                                                    </div>
                                                    <div class="input-field col s6">
                                                        <select class="browser-default" id="city_id" name="city_id">
                                                            @if($data->city_id)
                                                            <option value="{{ $data->city_id }}">{{ $data->city->name }}</option>
                                                            @endif
                                                        </select>
                                                        <label class="active" for="city_id">Kota/Kabupaten</label>
                                                    </div>
                                                    <div class="input-field col s6">
                                                        <input id="phone" name="phone" type="text" placeholder="HP" value="{{ $data->phone }}">
                                                        <label class="active" for="phone">HP</label>
                                                    </div>
                                                    <div class="input-field col s6">
                                                        <input id="id_card" name="id_card" type="text" placeholder="KTP / SIM / No. Identitas" value="{{ $data->id_card }}">
                                                        <label class="active" for="id_card">KTP / SIM / No. Identitas</label>
                                                    </div>
                                                    <div class="input-field col s6">
                                                        <input id="old_password" name="old_password" type="password" placeholder="(Kosongkan jika tidak dirubah)">
                                                        <label class="active" for="old_password">Old Password</label>
                                                    </div>
                                                    <div class="input-field col s6">
                                                        <input id="new_password" name="new_password" type="password" placeholder="(Kosongkan jika tidak dirubah)">
                                                        <label class="active" for="new_password">New Password</label>
                                                    </div>
                                                    <div class="file-field input-field col s6">
                                                        <div class="btn">
                                                            <span>Pilih Foto</span>
                                                            <input type="file" name="file" id="file" accept="image/x-png,image/jpg,image/jpeg">
                                                        </div>
                                                        <div class="file-path-wrapper">
                                                            <input class="file-path validate" type="text">
                                                            <label class="" for="">Rekomendasi ukuran 1:1</label>
                                                        </div>
                                                    </div>
                                                    <div class="input-field col s6 center-align">
                                                        <div class="input-field" id="previewImg">
                                                            <img id="previewImage" src="{{ url('website/empty.png') }}" alt="..." width="150px">
                                                        </div>
                                                        <label class="active" for="">Preview Foto Profil</label>
                                                    </div>
                                                    <div class="col s12 mt-3">
                                                        <button class="btn waves-effect waves-light right submit" onclick="save();">Simpan <i class="material-icons right">send</i></button>
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
            </div>
            <div class="content-overlay"></div>
        </div>
    </div>
</div>

<script src="{{ url('app-assets/js/signature_pad.min.js') }}"></script>
<!-- END: Page Main-->
<script>
    $(function() {
        select2ServerSide('#province_id', '{{ url("admin/select2/province") }}');
        select2ServerSide('#city_id', '{{ url("admin/select2/city") }}');
        M.updateTextFields();

        $("#file").on('change', function () {

			if (typeof (FileReader) != "undefined") {
				var image_holder = $("#previewImg");
				image_holder.empty();

				var reader = new FileReader();
				reader.onload = function (e) {
					$("<img />", {
						"src": e.target.result,
						"class": "thumb-image",
						"width": "300px"
					}).appendTo(image_holder);
				};
				image_holder.show();
				reader.readAsDataURL($(this)[0].files[0]);
			} else {
				alert("This browser does not support FileReader.");
			}
		});

    });

    function save(){
        swal({
            title: "Apakah anda yakin?",
            text: "Yakin ingin menyimpan perubahan?!!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tunggu dulu!',
            delete: 'Ya, gas!'
            }
        }).then(function (willApprove) {
            if (willApprove) {
                var formData = new FormData($('#form_data')[0]);
                $.ajax({
                    url: '{{ Request::url() }}/update',
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
                        loadingOpen('#main');
                    },
                    success: function(response) {
                        loadingClose('#main');
                        if(response.status == 200) {
                            M.toast({
                                html: response.message,
                                completeCallback: location.reload()
                            });
                        } else if(response.status == 422) {
                            $('#validation_alert').show();
                            $('#main').scrollTop(0);
                            $.each(response.error, function(field, errorMessage) {
                                $('#' + field).addClass('error-input');
                                $('#' + field).css('border', '1px solid red');
                                
                            });
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
                        $('#main').scrollTop(0);
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

    var canvas = document.getElementById('signature-pad');
	   
    function resizeCanvas() {
        var parentWidth = $(canvas).parent().outerWidth() - 50;
        canvas.setAttribute("width", parentWidth);
        canvas.style.background = "white";
        this.signaturePad = new SignaturePad(canvas);
    }

    window.onresize = resizeCanvas;
    resizeCanvas();

    var signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)'
    });

    function undoSign(){
        var data = signaturePad.toData();
        if (data) {
            data.pop();
            signaturePad.fromData(data);
        }
    }

    function clearSign(){
        signaturePad.clear();
    }

    function saveSign(){
        if(signaturePad.isEmpty()){
            var fd = new FormData();
            var files = $('#sign')[0].files;
            if(files.length > 0 ){
                fd.append('file',files[0]);
                
                $.ajax({
                    url: "{{ Request::url() }}/upload_sign",
                    type: 'POST',
                    data: fd,
                    contentType: false,
                    processData: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $('#validation_alert_sign').hide();
                        loadingOpen('#main');
                    },
                    success: function(response){
                        loadingClose('#main');
                        if(response.status == 200) {
                            M.toast({
                                html: response.message,
                                completeCallback: location.reload()
                            });
                        } else if(response.status == 422) {
                            $('#validation_alert_sign').show();
                            $('#main').scrollTop(0);
                            
                            swal({
                                title: 'Ups! Validation',
                                text: 'Check your form.',
                                icon: 'warning'
                            });

                            $.each(response.error, function(i, val) {
                                $.each(val, function(i, val) {
                                    $('#validation_alert_sign').append(`
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
                            notif('error', 'bg-danger', response.message);
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
            }else{
                swal({
                    title: 'Ups!',
                    text: 'Pilih file atau tanda tangan langsung.',
                    icon: 'warning'
                });
            }
        }else{
            var signdata = signaturePad.toDataURL('image/png');
            
            $.ajax({
                url: "{{ Request::url() }}/upload_sign",
                type: 'POST',
                dataType: 'JSON',
                data: {signdata:signdata},
                cache: true,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('#validation_alert_sign').hide();
                    loadingOpen('#main');
                },
                success: function(response) {
                    loadingClose('#main');
                    if(response.status == 200) {
                        M.toast({
                            html: response.message,
                            completeCallback: location.reload()
                        });
                    } else if(response.status == 422) {
                        $('#validation_alert_sign').show();
                        $('#main').scrollTop(0);
                        
                        swal({
                            title: 'Ups! Validation',
                            text: 'Check your form.',
                            icon: 'warning'
                        });

                        $.each(response.error, function(i, val) {
                            $.each(val, function(i, val) {
                                $('#validation_alert_sign').append(`
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
                        notif('error', 'bg-danger', response.message);
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