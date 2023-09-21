<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<!-- BEGIN: Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="...">
    <meta name="keywords" content="...">
    <meta name="author" content="ThemeSelect">
	<meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- <!-- hilangkan ini bahaya -->
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <!-- end hilangkan --> --}}
    <title>Register | Superior Porcelain Sukses</title>
    <link rel="apple-touch-icon" href="{{ url('website/logo_web_small.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ url('website/logo_web_small.png') }}">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- BEGIN: VENDOR CSS-->
    <link rel="stylesheet" type="text/css" href="{{ url('app-assets/vendors/vendors.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ url('app-assets/vendors/sweetalert/sweetalert.css') }}">
    <!-- END: VENDOR CSS-->
    <!-- BEGIN: Page Level CSS-->
    <link rel="stylesheet" type="text/css" href="{{ url('app-assets/css/themes/vertical-gradient-menu-template/materialize.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('app-assets/css/themes/vertical-gradient-menu-template/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('app-assets/css/pages/login.css') }}">
    <!-- END: Page Level CSS-->
    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="{{ url('app-assets/css/custom/custom.css') }}">
    <!-- END: Custom CSS-->
    <style>
        #main_body {
            zoom:0.9;
            margin-top: 30px;
        }
        @media(max-width: 800px) {
            #main_body {
                padding: 50px;
                zoom:0.8;
            }
        }
    </style>
</head>
<!-- END: Head-->

<body class="vertical-layout page-header-light vertical-menu-collapsible vertical-gradient-menu preload-transitions 1-column login-bg   blank-page blank-page" data-open="click" data-menu="vertical-gradient-menu" data-col="1-column">

	<div class="row">
        <div class="col s12" id="main_body">
            <div class="container">
                <div id="login-page" class="row">
                    <div class="col s12 m10 l8 z-depth-4 card-panel border-radius-6 login-card bg-opacity-8">
                        <div class="col s12 center-align" style="padding:10px;">
                            <img src="{{ url('website/logo_web_fix.png') }}" width="30%">
                            <div>Silahkan pilih registrasi melalui form atau upload file PDF.</div>
                        </div>
                        <div class="col s12 m6 l6">
                            <form class="login-form" id="register_form">
                                <div class="row">
                                    <div class="input-field col s12 center-align">
                                        <h5 class="ml-4">Form</h5>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col s12">
                                        <div id="validation_alert_form" style="display:none;"></div>
                                    </div>
                                    <div class="input-field col s6">
                                        <i class="material-icons prefix pt-2">person_pin</i>
                                        <input id="name" type="text" name="name">
                                        <label for="name" class="center-align">Nama Lengkap</label>
                                    </div>
                                    <div class="input-field col s6">
                                        <i class="material-icons prefix pt-2">account_circle</i>
                                        <input id="username" type="text" name="username">
                                        <label for="username" class="center-align">Username (Tanpa Spasi)</label>
                                    </div>
                                    <div class="input-field col s6">
                                        <i class="material-icons prefix pt-2">lock_outline</i>
                                        <input id="password" type="password" name="password">
                                        <label for="password">Password</label>
                                    </div>
                                    <div class="input-field col s6">
                                        <i class="material-icons prefix pt-2">lock_outline</i>
                                        <input id="password_confirm" type="password" name="password_confirm">
                                        <label for="password_confirm">Konfirmasi</label>
                                    </div>
                                    <div class="input-field col s12">
                                        <i class="material-icons prefix pt-2">location_on</i>
                                        <input id="address" type="text" name="address">
                                        <label for="address" class="center-align">Alamat Lengkap</label>
                                    </div>
                                    <div class="input-field col s6">
                                        <i class="material-icons prefix pt-2">mail_outline</i>
                                        <input id="email" type="email" name="email">
                                        <label for="email" class="center-align">Email</label>
                                    </div>
                                    <div class="input-field col s6">
                                        <i class="material-icons prefix pt-2">phone</i>
                                        <input id="hp" type="text" name="hp">
                                        <label for="hp" class="center-align">HP / Telepon</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col s12 m12 l12 ml-2 mt-1">
                                        <p>
                                            <label>
                                                <input type="checkbox" id="showPassword"/>
                                                <span>Lihat Password</span>
                                            </label>
                                        </p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="input-field col s12">
                                        <button class="btn waves-effect waves-light border-round gradient-45deg-purple-deep-orange col s12" type="submit" onclick="">Register</button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="input-field col s6 m6 l6">
                                        Non Staff Employee Only
                                    </div>
                                    <div class="input-field col s6 m6 l6">
                                        <p class="margin right-align medium-small"><a href="{{ url('admin/login') }}">Login</a></p>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col s12 m6 l6">
                            <form class="login-form" id="register_upload">
                                <div class="row">
                                    <div class="input-field col s12 center-align">
                                        <h5 class="ml-4">Upload</h5>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col s12">
                                        <div id="validation_alert_upload" style="display:none;"></div>
                                    </div>
                                    <div class="file-field input-field col s12">
                                        <div class="btn">
                                            <span>Document</span>
                                            <input type="file" name="document" id="document">
                                        </div>
                                        <div class="file-path-wrapper">
                                            <input class="file-path validate" type="text">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="input-field col s12">
                                        <button class="btn waves-effect waves-light border-round gradient-45deg-purple-deep-orange col s12" type="submit" onclick="">Kirim</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-overlay"></div>
        </div>
    </div>

    <!-- END: Footer-->
    <!-- BEGIN VENDOR JS-->
    <script src="{{ url('app-assets/js/vendors.min.js') }}"></script>
    <!-- BEGIN VENDOR JS-->
    <!-- BEGIN PAGE VENDOR JS-->
	<script src="{{ url('app-assets/vendors/sweetalert/sweetalert.min.js') }}"></script>
    <script src="{{ url('app-assets/vendors/chartjs/chart.min.js') }}"></script>
    <!-- END PAGE VENDOR JS-->
    <!-- BEGIN THEME  JS-->
    <script src="{{ url('app-assets/js/plugins.js') }}"></script>
    <script src="{{ url('app-assets/js/search.js') }}"></script>
    <script src="{{ url('app-assets/js/custom/custom-script.js') }}"></script>
    <!-- END THEME  JS-->
    <!-- BEGIN PAGE LEVEL JS-->
	<script>
		$(function() {

			$('#showPassword').click(function(){
				if($(this).is(':checked')){
					$('#password, #password_confirm').attr('type', 'text');
				}else{
					$('#password, #password_confirm').attr('type', 'password');
				}
			});
			
			$("#register_form").submit(function(event) {
				event.preventDefault();
                let formData = new FormData($('#register_form')[0]);
                formData.append('type','form');
				if($('#password').val() == $('#password_confirm').val()){
					$.ajax({
                        url: '{{ Request::url() }}/save',
                        type: 'POST',
                        dataType: 'JSON',
                        contentType: false,
                        processData: false,
                        data: formData,
                        cache: true,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        beforeSend: function() {
                            $('#validation_alert_form').hide();
                            $('#validation_alert_form').html('');
                        },
                        success: function(response) {
                            if(response.status == 200) {				
                                swal({
                                    title: 'Success',
                                    text: response.message,
                                    icon: 'success'
                                }).then(function(){
                                    location.reload();
                                });
                            } else if(response.status == 422) {
                                $('#validation_alert_form').show();
                                
                                swal({
                                    title: 'Ups! Validation',
                                    text: 'Check your form.',
                                    icon: 'warning'
                                });

                                $.each(response.error, function(i, val) {
                                    $.each(val, function(i, val) {
                                        $('#validation_alert_form').append(`
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
                            
                            }
                        },
                        error: function() {
                            
                            swal({
                                title: 'Ups!',
                                text: 'Check your internet connection.',
                                icon: 'error'
                            });
                        }
				  });
				}else{
					swal({
						title: 'Ups, hayo.',
						text: 'Password dan konfirmasinya tidak sama. Silahkan cek kembali.',
						icon: 'error'
					});
				}
			});
		});
	</script>
    <!-- END PAGE LEVEL JS-->
</body>

</html>