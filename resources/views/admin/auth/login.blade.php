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
    <title>Login | Superior Porcelain Sukses</title>
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
</head>
<!-- END: Head-->

<body class="vertical-layout page-header-light vertical-menu-collapsible vertical-gradient-menu preload-transitions 1-column login-bg   blank-page blank-page" data-open="click" data-menu="vertical-gradient-menu" data-col="1-column">
    <script>window.localStorage.clear();</script>
	<div class="row">
        <div class="col s12">
            <div class="container">
                <div id="login-page" class="row">
                    <div class="col s12 m6 l4 z-depth-4 card-panel border-radius-6 login-card bg-opacity-8">
                        <form class="login-form" id="login_form">
                            <div class="row">
                                <div class="input-field col s12 center-align">
                                    <img src="{{ url('website/logo_web_fix.png') }}" width="80%">
                                </div>
                                <div class="input-field col s12">
                                    <h5 class="ml-4">Silahkan Masuk</h5>
                                </div>
                            </div>
                            <div class="row margin">
                                <div class="input-field col s12">
                                    <i class="material-icons prefix pt-2">person_pin</i>
                                    <input id="id_card" type="text" name="id_card">
                                    <label for="id_card" class="center-align">NIK</label>
                                </div>
                            </div>
                            <div class="row margin">
                                <div class="input-field col s12">
                                    <i class="material-icons prefix pt-2">lock_outline</i>
                                    <input id="password" type="password" name="password">
                                    <label for="password">Password</label>
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
                            <div class="row">
                                <div class="input-field col s12">
                                    <button class="btn waves-effect waves-light border-round gradient-45deg-purple-deep-orange col s12" type="submit" onclick="">Login</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-field col s6 m6 l6">
                                    <p class="margin medium-small"><a href="{{ url('admin/register') }}">Register Karyawan Non-Staff</a></p>
                                </div>
                                <div class="input-field col s6 m6 l6">
                                    <p class="margin right-align medium-small"><a href="{{ url('admin/forget') }}">Lupa Password ?</a></p>
                                </div>
                            </div>
                        </form>
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
					$('#password').attr('type', 'text');
				}else{
					$('#password').attr('type', 'password');
				}
			});
			
			$("#login_form").submit(function(event) {
				event.preventDefault();
				if($('#id_card').val() !== '' && $('#password').val() !== ''){
					$.ajax({
					 url: '{{ url("admin/login/auth") }}',
					 type: 'POST',
					 dataType: 'JSON',
					 contentType: false,
					 processData: false,
					 data: new FormData($('#login_form')[0]),
					 cache: true,
					 headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					 },
					 beforeSend: function() {
						
					 },
					 success: function(response) {
						
						if(response.status == 200) {
							setTimeout(function() {
                                @if(Request::get('url'))
                                    window.location.href = "{!! base64_decode(Request::get('url')) !!}";
                                @else
                                    location.reload();
                                @endif
							}, 1500);							
							swal({
								title: 'Success',
								text: response.message,
								icon: 'success'
							});
						} else if(response.status == 422) {
							swal({
								title: 'Validation',
								text: response.message,
								icon: 'warning'
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
						title: 'Ups, error.',
						text: 'Please fill in the forms.',
						icon: 'error'
					});
				}
			});

            function reloadPage() {
                window.location.reload();
            }

            
            setInterval(reloadPage, 3600000);
		});
	</script>
    <!-- END PAGE LEVEL JS-->
</body>

</html>