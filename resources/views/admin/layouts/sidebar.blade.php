	<!-- BEGIN: SideNav-->
    <aside class="sidenav-main nav-expanded nav-lock nav-collapsible sidenav-light sidenav-active-square">
        <div class="brand-sidebar">
            <h1 class="logo-wrapper">
                <a class="brand-logo darken-1" href="{{ url('admin/dashboard') }}">
                    <img class="hide-on-med-and-down " src="{{ url('website/logo_web_small.png') }}" alt="materialize logo" style="height: 25px;" />
                    <img class="show-on-medium-and-down hide-on-med-and-up" src="{{ url('website/logo_porselain_putih.png') }}" alt="materialize logo" style="margin-left:35px;"/>
                    <span class="logo-text hide-on-med-and-down" style="top:-10px !important;position: relative;">SUPERIOR</span>
                    <br><span class="logo-text hide-on-med-and-down" style="margin-top:-5px !important;font-size:10px;margin-left:35px;letter-spacing: 1.3px;">PORCELAIN SUKSES</span>
                </a>
                <a class="navbar-toggler" href="javascript:void(0);">
                    <i class="material-icons">radio_button_checked</i>
                </a>
            </h1>
        </div>
        <ul class="sidenav sidenav-collapsible leftside-navigation collapsible sidenav-fixed menu-shadow" id="slide-out" data-menu="menu-navigation" data-collapsible="menu-accordion">
			<li class="{{ Request::segment(2) == 'dashboard' ? 'active' : '' }} bold">
                <a class="waves-effect waves-cyan {{ Request::segment(2) == 'dashboard' ? 'active' : '' }}" href="{{ url('admin/dashboard') }}">
                    <i class="material-icons">settings_input_svideo</i><span class="menu-title" data-i18n="Dashboard">Dashboard</span>
                </a>
            @php $menu = App\Models\Menu::whereNull('parent_id')->where('status','1')->oldest('order')->get(); @endphp

            @foreach($menu as $m)
                @if($m->sub()->exists())
                    <li class="{{ Request::segment(2) == $m->url ? 'active open' : '' }} bold">
                        <a class="collapsible-header waves-effect waves-cyan " href="JavaScript:void(0)">
                            <i class="material-icons">{{ $m->icon }}</i>
                            <span class="menu-title" data-i18n="{{ $m->name }}">{{ $m->name }}</span>
                            @if($m->is_maintenance)
                                <span class="badge badge pill red float-right mr-7 tooltipped" data-position="bottom" data-tooltip="Sedang Dalam Perbaikan" style="height:30px !important;margin-top: 7px;">
                                    <i class="material-icons" style="margin-right: 0rem !important;width: auto !important;padding:2px 0px 2px 0px !important;margin-top: 4px;">build</i>
                                </span>
                            @endif
                        </a>
                        <div class="collapsible-body">
                            <ul class="collapsible collapsible-sub" data-collapsible="accordion">
                                @foreach($m->sub()->where('status','1')->oldest('order')->get() as $msub)
                                    @if($msub->sub()->exists())
                                        <li class="{{ Request::segment(3) == $msub->url ? 'active open' : '' }} bold menu-child1">
                                            <a class="collapsible-header waves-effect waves-cyan " href="JavaScript:void(0)">
                                                <i class="material-icons">{{ $msub->icon }}</i>
                                                <span class="menu-title" data-i18n="{{ $msub->name }}">{{ $msub->name }}</span>
                                                @if($msub->is_maintenance)
                                                    <span class="badge badge pill red float-right mr-7 tooltipped" data-position="bottom" data-tooltip="Sedang Dalam Perbaikan" style="height:30px !important;margin-top: 7px;">
                                                        <i class="material-icons" style="margin-right: 0rem !important;width: auto !important;padding:2px 0px 2px 0px !important;margin-top: 4px;">build</i>
                                                    </span>
                                                @endif
                                            </a>
                                            <div class="collapsible-body">
                                                <ul class="collapsible collapsible-sub" data-collapsible="accordion">
                                                    @foreach($msub->sub()->where('status','1')->oldest('order')->get() as $msub2)
                                                        @if($msub2->sub()->exists())
                                                            
                                                        @else
                                                            <li class="{{ Request::segment(4) == $msub2->url ? 'active' : '' }} menu-child2">
                                                                <a class="{{ Request::segment(4) == $msub2->url ? 'active' : '' }}" href="{{ url('admin').'/'.$m->url.'/'.$msub->url.'/'.$msub2->url }}">
                                                                    <i class="material-icons">{{ $msub2->icon }}</i>
                                                                    <span data-i18n="{{ $msub2->name }}">{{ $msub2->name }}</span>
                                                                    @if($msub2->is_maintenance)
                                                                        <span class="badge badge pill red float-right mr-7 tooltipped" data-position="bottom" data-tooltip="Sedang Dalam Perbaikan" style="height:30px !important;margin-top: 7px;">
                                                                            <i class="material-icons" style="margin-right: 0rem !important;width: auto !important;padding:2px 0px 2px 0px !important;margin-top: 4px;">build</i>
                                                                        </span>
                                                                    @endif
                                                                </a>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </li>
                                    @else
                                        <li class="{{ Request::segment(3) == $msub->url ? 'active' : '' }} menu-child1">
                                            <a class="{{ Request::segment(3) == $msub->url ? 'active' : '' }}" href="{{ url('admin').'/'.$m->url.'/'.$msub->url }}">
                                                <i class="material-icons">{{ $msub->icon }}</i>
                                                <span data-i18n="{{ $msub->name }}">{{ $msub->name }}</span>
                                                @if($msub->is_maintenance)
                                                    <span class="badge badge pill red float-right mr-7 tooltipped" data-position="bottom" data-tooltip="Sedang Dalam Perbaikan" style="height:30px !important;margin-top: 7px;">
                                                        <i class="material-icons" style="margin-right: 0rem !important;width: auto !important;padding:2px 0px 2px 0px !important;margin-top: 4px;">build</i>
                                                    </span>
                                                @endif
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </li>
                @else
                    <li class="{{ Request::segment(2) == $m->url ? 'active' : '' }}">
                        <a class="{{ Request::segment(2) == $m->url ? 'active' : '' }}" href="{{ url('admin').'/'.$m->url }}">
                            <i class="material-icons">{{ $m->icon }}</i>
                            <span data-i18n="{{ $m->name }}">{{ $m->name }}</span>
                            @if($m->is_maintenance)
                                <span class="badge badge pill red float-right mr-7 tooltipped" data-position="bottom" data-tooltip="Sedang Dalam Perbaikan" style="height:30px !important;margin-top: 7px;">
                                    <i class="material-icons" style="margin-right: 0rem !important;width: auto !important;padding:2px 0px 2px 0px !important;margin-top: 4px;">build</i>
                                </span>
                            @endif
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
        <div class="navigation-background"></div><a class="sidenav-trigger btn-sidenav-toggle btn-floating btn-medium waves-effect waves-light hide-on-large-only" href="#" data-target="slide-out"><i class="material-icons">menu</i></a>
    </aside>
    <!-- END: SideNav-->