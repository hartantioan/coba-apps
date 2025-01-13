<!-- Leaflet CSS -->
<link
  rel="stylesheet"
  href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
/>
<style>
    .modal {
        top:0px !important;
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
                        <div class="col s12 m12 l12">
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">{{ __('translations.list_data') }}</h4>
                                    <div class="row">
                                        <div class="col s12">
                                            <div id="datatable_buttons"></div>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">{{ __('translations.refresh') }}</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <table id="datatable_serverside" >
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{ __('translations.code') }}</th>
                                                        <th>User</th>
                                                        <th>Jam Masuk</th>
                                                        <th  class="center-align">Keterangan Masuk</th>
                                                        <th  class="center-align">Jam Keluar</th>
                                                        <th>Keterangan Keluar</th>
                                                        <th>Lokasi</th>
                                                        <th>Lampiran Masuk</th>
                                                        <th>Lampiran Keluar</th>
                                                        <th>Status</th>
                                                        <th>{{ __('translations.action') }}</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col m12 s12 l8" hidden>
                            <div class="card">


                            </div>
                            <div class="card">
                                <div class="card-content">
                                    <div id="map" style="height: 400px;"></div>

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

<div id="modal1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;">
    <div class="modal-content" style="overflow-x: hidden;max-width: 100%;">
        <div class="row">
            <div class="col s12">
                <h4>{{ __('translations.add') }} {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <fieldset>
                            <legend>1. {{ __('translations.main_info') }}</legend>
                            <div class="row">
                                <div class="input-field col m2 s12 step1" id="codeDiv">
                                    <input id="code" name="code" type="text" value="{{ $newcode }}" readonly>
                                    <label class="active" for="code">No. Dokumen</label>
                                </div>
                                <div class="input-field col m1 s12 step2" id="placeDiv">
                                    <select class="form-control" id="code_place_id" name="code_place_id" onchange="getCode(this.value);">
                                        <option value="">--Pilih--</option>
                                        @foreach ($place as $rowplace)
                                            <option value="{{ $rowplace->code }}">{{ $rowplace->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="input-field col m12 s12 l12">
                                    <div class="card">
                                        <div class="card-content row" style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                            <div id="liveDateTime" style="text-align-last: center;"></div>
                                            <div id="Location" style="text-align-last: center;">Lokasi: </div>
                                            <div id="Province" style="text-align-last: center;"></div>
                                            <div style="text-align: center;">
                                                <video id="video" width="100%" height="auto" style="border: 1px solid gray;"></video>
                                            </div>

                                            <div id="sourceSelectPanel" style="display:none">
                                                <label for="sourceSelect">Change video source:</label>
                                                <select id="sourceSelect" style="max-width:400px" class="browser-default">
                                                </select>
                                            </div>

                                            <div style="margin-top: 10px; text-align: center;">
                                                <a class="btn btn-small waves-effect waves-light breadcrumbs-btn" id="startButton">Start</a>
                                                <a class="btn btn-small waves-effect waves-light breadcrumbs-btn" id="resetButton">Reset</a>
                                                <a class="btn btn-small waves-effect waves-light breadcrumbs-btn"  id="captureButton">Capture Image</a>
                                                <div id="imageContainer"></div>
                                                <div id="imageContainer2"></div>
                                            </div>


                                        </div>
                                    </div>
                                </div>
                                <div class="input-field col m3 s12 step12" id="noteInDiv">
                                    <input type="hidden" id="temp" name="temp">
                                    <input id="note_in" name="note_in" type="text" placeholder="Keterangan Masuk...">
                                    <label class="active" for="note_in">Keterangan Masuk</label>
                                </div>
                                <div class="input-field col m3 s12 step12" id="noteOutDiv">
                                    <input id="note_out" name="note_out" type="text" placeholder="Keterangan Masuk...">
                                    <label class="active" for="note_out">Keterangan Keluar</label>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light submit step30 mr-1" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat mr-2">{{ __('translations.close') }}</a>
    </div>
</div>

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div>



<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="{{ url('app-assets/js/custom/camera_scan.js') }}"></script>
<!-- END: Page Main-->
<script>

    window.addEventListener('load', function () {
        let selectedDeviceId;
        const codeReader = new ZXing.BrowserMultiFormatReader();

        codeReader.listVideoInputDevices()
        .then((videoInputDevices) => {
            const sourceSelect = document.getElementById('sourceSelect');
            selectedDeviceId = videoInputDevices[0].deviceId;
            if (videoInputDevices.length >= 1) {
            videoInputDevices.forEach((element) => {
                $('#sourceSelect').append(`<option value="${element.deviceId}">${element.label}</option>`);
            });

            sourceSelect.onchange = () => {
                selectedDeviceId = sourceSelect.value;
            };

            const sourceSelectPanel = document.getElementById('sourceSelectPanel');
            sourceSelectPanel.style.display = 'block';
            }

            document.getElementById('startButton').addEventListener('click', () => {
            codeReader.decodeFromVideoDevice(selectedDeviceId, 'video', (result, err) => {
                if (result) {
                    document.getElementById('code_barcode').value = result.text;
                }
                if (err && !(err instanceof ZXing.NotFoundException)) {

                }
            });

            });

            document.getElementById('resetButton').addEventListener('click', () => {
                codeReader.reset();
            });

            document.getElementById('captureButton').addEventListener('click', () => {
                const video = document.getElementById('video');
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');

                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;

                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                const imageData = canvas.toDataURL('image/png');

                const img = document.createElement('img');
                img.src = imageData;
                img.alt = 'Captured Image';

                document.getElementById('imageContainer').appendChild(img);


            });

        })
        .catch((err) => {

        });
    });
    var lat,long,location_form;
    $(function() {
        loadDataTable();
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                document.getElementById('codeDiv').style.display = 'block';
                document.getElementById('placeDiv').style.display = 'block';
                document.getElementById('noteInDiv').style.display = 'block';
                document.getElementById('noteOutDiv').style.display = 'none';
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
            }
        });
        const mymap = L.map('map').setView([0, 0], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
        }).addTo(mymap);


        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const latitude = position.coords.latitude;
                    lat = latitude;
                    const longitude = position.coords.longitude;
                    long = longitude;
                        let code = $('#currency_id').find(':selected').data('code');
                        $.ajax({
                            url: 'https://api.bigdatacloud.net/data/reverse-geocode-client?latitude='+latitude+'&longitude='+longitude+'&localityLanguage=en',
                            type: 'GET',
                            beforeSend: function() {

                            },
                            data: {

                            },
                            success: function(response) {
                                console.log(response);
                                $('#Location').append(response.city+` /`+response.locality);
                                $('#Province').append(response.principalSubdivision);
                                location_form = response.city+' / '+response.locality+'  '+response.principalSubdivision;
                            },
                            error: function() {
                                swal({
                                    title: 'Ups!',
                                    text: 'Check your internet connection.',
                                    icon: 'error'
                                });
                            }
                        });


                    mymap.setView([latitude, longitude], 20);

                    const marker = L.marker([latitude, longitude]).addTo(mymap);
                    marker.bindPopup("You are here!").openPopup();
                },
                function (error) {
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            console.error("User denied the request for Geolocation.");
                            break;
                        case error.POSITION_UNAVAILABLE:
                            console.error("Location information is unavailable.");
                            break;
                        case error.TIMEOUT:
                            console.error("The request to get user location timed out.");
                            break;
                        case error.UNKNOWN_ERROR:
                            console.error("An unknown error occurred.");
                            break;
                    }
                },
                {
                    enableHighAccuracy: true,
                    maximumAge: 0,
                    timeout: 10000
                }
            );
        } else {
            console.error("Geolocation is not supported by this browser.");
        }
    });

    let now = new Date("{{ date('Y/m/d H:i:s') }}");

    /* function updateDateTime() {
        now.setSeconds(now.getSeconds() + 1000);
        document.getElementById('liveDateTime').textContent = now.toLocaleString();
    }

    setInterval(updateDateTime, 1000); */

    setInterval(function() {
        now.setSeconds(now.getSeconds() + 1);
        document.getElementById('liveDateTime').textContent = now.getHours() +':' + now.getMinutes() + ':' + now.getSeconds();
    }, 1000);

    function save(){
        var formData = new FormData($('#form_data')[0]), passed = true;
        var imageElement = document.getElementById('imageContainer').querySelector('img');
        if (!imageElement) {
            swal({
                title: 'Ups!',
                text: 'No image found.',
                icon: 'warning'
            });
            return 0;
        }
        var imageData = imageElement.src;
        formData.append('location',location_form);
        formData.append('latitude',lat);
        formData.append('longitude',long);
        formData.append('img',imageData);
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
                loadingOpen('#modal1');
            },
            success: function(response) {
                $('input').css('border', 'none');
                $('input').css('border-bottom', '0.5px solid black');
                loadingClose('#modal1');
                if(response.status == 200) {
                    success();
                    M.toast({
                        html: response.message
                    });
                } else if(response.status == 422) {
                    $('#validation_alert').show();
                    $('.modal-content').scrollTop(0);
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
                $('.modal-content').scrollTop(0);
                loadingClose('#modal1');
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
            }
        });
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
            "order": [[1, 'asc']],
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    status : $('#filter_status').val(),
                    account_id : $('#filter_account').val(),
                    start_date : $('#start_date').val(),
                    finish_date : $('#finish_date').val(),
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
                { name: 'datein', className: 'center-align' },
                { name: 'note_in', className: 'center-align' },
                { name: 'dateout', className: 'center-align' },
                { name: 'note_out', className: 'center-align' },
                { name: 'location', className: 'center-align' },
                { name: 'img_in', className: 'center-align' },
                { name: 'img_out', className: 'center-align' },
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

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
    }

    function getCode(val){
        if(val){
            if($('#temp').val()){
                let newcode = $('#code').val().replaceAt(7,val);
                $('#code').val(newcode);
            }else{
                if($('#code').val().length > 7){
                    $('#code').val($('#code').val().slice(0, 7));
                }
                $.ajax({
                    url: '{{ Request::url() }}/get_code',
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        val: $('#code').val() + val,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        loadingOpen('.modal-content');
                    },
                    success: function(response) {
                        loadingClose('.modal-content');
                        $('#code').val(response);
                    },
                    error: function() {
                        swal({
                            title: 'Ups!',
                            text: 'Check your internet connection.',
                            icon: 'error'
                        });
                    }
                });
            }
        }
    }


    function doneVisit(id){
        $.ajax({
            url: '{{ Request::url() }}/visit_out',
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

                document.getElementById('codeDiv').style.display = 'none';
                document.getElementById('placeDiv').style.display = 'none';
                document.getElementById('noteOutDiv').style.display = 'block';
                document.getElementById('noteInDiv').style.display = 'none';


                $('.modal-content').scrollTop(0);
                $('#note').focus();
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

    function voidStatus(id){
        var msg = '';
        swal({
            title: "Alasan mengapa anda menutup!",
            text: "Anda tidak bisa mengembalikan data yang telah ditutup.",
            buttons: true,
            content: "input",
        })
        .then(message => {
            if (message != "" && message != null) {
                $.ajax({
                    url: '{{ Request::url() }}/void_status',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { id : id, msg : message },
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
