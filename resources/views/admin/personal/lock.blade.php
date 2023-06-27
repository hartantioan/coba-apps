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

	<div class="row">
        <div class="col s12">
            <div class="container">
                <div id="login-page" class="row">
                    <div class="col s12 m6 l4 z-depth-4 card-panel border-radius-6 login-card bg-opacity-8">
                        <form class="login-form" id="login_form">
                            <div class="row">
                                <div class="input-field col s12 center-align">
                                    <input id="id_card" type="text" name="id_card" value="{{ session('bo_employee_no') }}" hidden>
                                    <img class="z-depth-4 circle responsive-img" width="100" src="{{ session('bo_photo') }}" alt="">
                                    <h5 class="ml-4">{{ session('bo_name') }}</h5>
                                </div>
                            </div>
                            <div class="row margin">
                                <div class="input-field col s12">
                                    <i class="material-icons prefix pt-2">lock_outline</i>
                                    <input id="password" type="password" name="password">
                                    <label for="password">Password</label>
                                </div>
                                <label>
                                    <input type="checkbox" id="showPassword"/>
                                    <span>Show Password</span>
                                </label>
                            </div>
                            <div class="row">
                                <div class="col s12 m12 l12 ml-2 mt-1">
                                    <p>
                                       
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
                                    
                                </div>
                                <div class="input-field col s6 m6 l6">
                                    <p class="margin right-align medium-small"><a href="{{ url('admin/login/forget') }}">Forgot password ?</a></p>
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
    <script src="{{ url('app-assets/js/custom/custom-script.js?v=0') }}"></script>
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
					 url: '{{ url("admin/lock/disable") }}',
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
                        if(response.status == 200){
                            window.location.href = response.url;
                        }else{
                            swal({
								title: 'Ups!',
								text: response.message,
								icon: 'warning'
							});
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
		});
        
	</script>
    @if(session('bo_id'))
     <script>
         $(function() {
             cekNotif('{{ URL::to('/') }}');
             setInterval(function () {
                 cekNotif('{{ URL::to('/') }}');
             },5000);
             $('.tooltipped').tooltip();
             checkPageMaintenance('{{ URL::to('/') }}');
         });
     </script>
    @endif
    <!-- END PAGE LEVEL JS-->
</body>

</html>