<!-- BEGIN: Page Main-->
<style>
    #modal6 {
        top:0px !important;
    }
    
    #description-text ul > li {
        list-style-type: initial !important;
    }

    #description-text ul:not(.browser-default) {
        padding-left: 20px !important;
    }
</style>
<link rel="stylesheet" type="text/css" href="{{ url('app-assets/css/pages/page-timeline.css') }}">
<div id="main">
    <div class="row">
        <div class="pt-3 pb-1" id="breadcrumbs-wrapper">
            <div class="container">
                <div class="row">
                    <div class="col s8 m6 l6">
                        <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ $title }}</span></h5>
                        <ol class="breadcrumbs mb-0">
                            <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item"><a href="#">{{ Str::title(str_replace('_',' ',Request::segment(2))) }}</a>
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
                            <ul class="timeline" id="body-history-goods" style="padding-left: 4rem; padding-right:4rem;">
                                @foreach($change_log as $log)
                                    <li>
                                        <div class="timeline-badge blue">
                                            <a class="tooltipped" data-position="top" data-tooltip="{{date('d/m/y',strtotime($log->release_date))}}"><i class="material-icons white-text">disc_full</i></a>
                                        </div>
                                        <div class="timeline-panel">
                                            <div class="card m-0 hoverable gradient-45deg-orange-deep-orange" id="profile-card" style="overflow: visible;">
                                                <div class="card-content">
                                                    <div style="display:-webkit-box;">
                                                        {!!$log->user->profilePicture()!!}
                                                        <h5 class="card-title activator grey-text text-darken-4 mt-1 ml-3">{{$log->user->name}}</h5>
                                                    </div>
                                                    <p><i class="material-icons profile-card-i">restore</i><b>Version:</b> {{$log->version}}</p>
                                                    <p>Judul : {{$log->title}}</p>
                                                    <p>Description</p>
                                                    <div style="padding: 1rem" id="description-text">{!! $log->description !!}</div>
                                                    
                                                    <p><i class="material-icons profile-card-i">date_range</i>{{date('d/m/y',strtotime($log->release_date))}}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                                <li class="clearfix" style="float: none;background:red;"></li>
                            </ul>
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="content-overlay"></div>
        </div>
    </div>
</div>





