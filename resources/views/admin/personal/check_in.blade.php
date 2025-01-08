<!-- Leaflet CSS -->
<link
  rel="stylesheet"
  href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
/>
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
                                    <h4 class="card-title"  style="text-align-last: center;">Detail Absen</h4>
                                    <div id="liveDateTime" style="text-align-last: center;"></div>
                                    <div id="Location" style="text-align-last: center;">Lokasi: </div>
                                    <div id="Province" style="text-align-last: center;"></div>
                                    <div class="row" style="margin-top:25px;">
                                        <div class="col s12" style="text-align-last: center;">
                                            <a class="waves-effect waves-light btn-large" style="margin-left: auto;" onclick="save();"><i class="material-icons left">assignment_turned_in</i>Absen</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col m12 s12 l8">
                            <div class="card">

                                <div class="card-content row" style="display: flex; flex-direction: column; align-items: center; justify-content: center;">

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
                                    </div>


                                </div>
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

                // Set canvas size to video dimensions
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;

                // Draw the current video frame onto the canvas
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                // Convert canvas to an image (base64 data URL)
                const imageData = canvas.toDataURL('image/png');

                // Create an img element to display the captured image
                const img = document.createElement('img');
                img.src = imageData;
                img.alt = 'Captured Image';

                // Append the captured image to the DOM (for example, inside a div with id 'imageContainer')
                document.getElementById('imageContainer').appendChild(img);

                // Optional: If you want to save the image, you can provide a download link
                const link = document.createElement('a');
                link.href = imageData;
                link.download = 'captured_image.png';
                link.textContent = 'Download Image';
                document.getElementById('imageContainer').appendChild(link);
            });

        })
        .catch((err) => {

        });
    });
    var lat,long,location_form;
   $(function() {
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
        var imageData = document.getElementById('imageContainer').querySelector('img')?.src;
        $.ajax({
            url: '{{ Request::url() }}/create',
            type: 'POST',
            dataType: 'JSON',
            data: {
                location    : location_form,
                latitude    : lat,
                longitude   : long,
                img         : imageData,
            },

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

                    });
                } else if(response.status == 422) {
                    M.toast({
                        html: response.message,

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

</script>
