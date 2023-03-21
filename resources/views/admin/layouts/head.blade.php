<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<!-- BEGIN: Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="Superior Porcelain Sukses.">
    <meta name="keywords" content="Superior Porcelain Sukses.">
    <meta name="author" content="ThemeSelect">
	<meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ env('APP_NAME') }} | {{ $title }}</title>
    <link rel="apple-touch-icon" href="{{ url('website/logo_web_small.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ url('website/logo_web_small.png') }}">
    <!-- <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"> -->
    <!-- BEGIN: VENDOR CSS-->
    <link rel="stylesheet" type="text/css" href="{{ url('app-assets/vendors/vendors.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ url('app-assets/vendors/sweetalert/sweetalert.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('app-assets/vendors/animate-css/animate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('app-assets/vendors/chartist-js/chartist.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('app-assets/vendors/chartist-js/chartist-plugin-tooltip.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ url('app-assets/vendors/flag-icon/css/flag-icon.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('app-assets/vendors/quill/katex.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('app-assets/vendors/quill/monokai-sublime.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('app-assets/vendors/data-tables/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('app-assets/vendors/data-tables/extensions/responsive/css/responsive.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('app-assets/vendors/data-tables/css/select.dataTables.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ url('app-assets/vendors/quill/quill.snow.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('app-assets/vendors/quill/quill.bubble.css') }}">
	<link rel="stylesheet" href="{{ url('app-assets/vendors/select2/select2.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ url('app-assets/vendors/select2/select2-materialize.css?v=10') }}" type="text/css">
    <!-- END: VENDOR CSS-->
    <!-- BEGIN: Page Level CSS-->
    <link rel="stylesheet" type="text/css" href="{{ url('app-assets/css/themes/vertical-modern-menu-template/materialize.css?v=2') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('app-assets/css/themes/vertical-modern-menu-template/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('app-assets/css/pages/dashboard-modern.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('app-assets/css/pages/intro.css?v=7') }}">
	<link rel="stylesheet" type="text/css" href="{{ url('app-assets/css/pages/data-tables.css?v=2') }}">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
    <!-- END: Page Level CSS-->
    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="{{ url('app-assets/css/custom/custom.css?v=39') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('app-assets/css/custom/waitMe.min.css') }}">
    <!-- END: Custom CSS-->
	<script src="{{ url('app-assets/js/vendors.min.js') }}"></script>
</head>
<!-- END: Head-->

<body class="vertical-layout page-header-light vertical-menu-collapsible vertical-modern-menu preload-transitions 2-columns   " data-open="click" data-menu="vertical-modern-menu" data-col="2-columns">
<div class="loader" id="loader">
	<div class="preloader-wrapper big active">
		<div class="spinner-layer spinner-blue-only">
			<div class="circle-clipper left">
				<div class="circle"></div>
			</div>
			<div class="gap-patch">
				<div class="circle"></div>
			</div>
			<div class="circle-clipper right">
				<div class="circle"></div>
			</div>
		</div>
	</div>
</div>