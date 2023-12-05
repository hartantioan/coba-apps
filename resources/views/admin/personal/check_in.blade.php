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

<!-- END: Page Main-->
<script>
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
        console.log(location_form +' ' + lat + ' ' + long +'');
        $.ajax({
            url: '{{ Request::url() }}/create',
            type: 'POST',
            dataType: 'JSON',
            data: { 
                location    : location_form,
                latitude    : lat,
                longitude   : long
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