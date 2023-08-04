    <!-- BEGIN: Header-->
    <header class="page-topbar" id="header">
        <div class="navbar navbar-fixed">
            <nav class="navbar-main navbar-color nav-collapsible sideNav-lock navbar-dark gradient-45deg-indigo-purple no-shadow" style="background: linear-gradient(45deg, #e19e4b, #6d2b17d8) !important;">
                <div class="nav-wrapper">
                    <div class="header-search-wrapper hide-on-med-and-down"><i class="material-icons">search</i>
                        <input class="header-search-input z-depth-2" type="text" name="Search" placeholder="Cari menu" data-search="template-list">
                        <ul class="search-list collection display-none"></ul>
                    </div>
                    <ul class="navbar-list right">
                        <li class="tooltipped" data-position="bottom" data-tooltip="Menu">
                            <a class="waves-effect waves-block waves-light" href="{{ url('admin/menu') }}" style="line-height: 1;">
                                <i class="material-icons approve-icon">dashboard</i>
                            </a>
                        </li>
                        <li class="hide-on-med-and-down tooltipped" data-position="bottom" data-tooltip="Full screen">
                            <a class="waves-effect waves-block waves-light toggle-fullscreen" href="javascript:void(0);"><i class="material-icons">settings_overscan</i>
                            </a>
                        </li>
                        <li class="hide-on-large-only search-input-wrapper">
                            <a class="waves-effect waves-block waves-light search-button" href="javascript:void(0);"><i class="material-icons">search</i></a>
                        </li>
                        <li class="tooltipped" data-position="bottom" data-tooltip="Approval">
                            <a class="waves-effect waves-block waves-light" href="{{ url('admin/approval') }}" style="line-height: 1;">
                                <i class="material-icons approve-icon">verified_user<small class="notification-badge approval-count">0</small></i>
                            </a>
                        </li>
                        <li class="tooltipped" data-position="bottom" data-tooltip="Notifikasi">
                            <a class="waves-effect waves-block waves-light notification-button" href="javascript:void(0);" data-target="notifications-dropdown" onclick="seeNotif('{{ URL::to('/') }}')">
                                <i class="material-icons">notifications_none<small class="notification-badge notif-count">0</small></i>
                            </a>
                        </li>
                        <li>
                            <a class="waves-effect waves-block waves-light profile-button" href="javascript:void(0);" data-target="profile-dropdown">
                                <span class="avatar-status avatar-online"><img src="{{ session('bo_photo') }}" alt="avatar"><i></i></span>
                            </a>
                        </li>
                    </ul>
                    <!-- notifications-dropdown-->
                    <ul class="dropdown-content" id="notifications-dropdown" style="max-height:550px !important;">
                        <li>
                            <h6>NOTIFIKASI<span class="new badge notif-count">0</span></h6>
                        </li>
                        <li class="divider" id="notifications-divider">
                        </li>
                        <li><a href="{{ URL::to('/') }}/admin/personal/notification" style="color: black;text-align:center">VIEW ALL</a></li>
                        <li id="notification-none">
                            <h6><b>Anda belum memiliki notifikasi.</b></h6>
                        </li>
                    </ul>
                    <!-- profile-dropdown-->
                    <ul class="dropdown-content" id="profile-dropdown">
                        <li>
                            <a class="grey-text text-darken-1" href="{{ url('admin/personal/profile') }}"><i class="material-icons">person_outline</i> Profil</a>
                        </li>
                        <li>
                            <a class="grey-text text-darken-1" href="{{ url('admin/personal/chat') }}"><i class="material-icons">chat_bubble_outline</i> Obrolan</a>
                        </li>
                        <li>
                            <a class="grey-text text-darken-1" href="{{ url('admin/personal/personal_fund_request') }}"><i class="material-icons">monetization_on</i> Req. Dana
                                {{-- <span class="badge badge pill red float-right mr-1 tooltipped" data-position="bottom" data-tooltip="Sedang Dalam Perbaikan" style="height:30px !important;margin-top: -5px;">
                                    <i class="material-icons" style="margin-right: 0rem !important;width: auto !important;padding:2px 0px 2px 0px !important;margin-top: -20px;">build</i>
                                </span> --}}
                            </a>
                        </li>
                        <li class="divider" tabindex="0"></li>
                        <li><a class="grey-text text-darken-1" href="{{ url('admin/lock/enable') }}"><i class="material-icons">lock_outline</i> Kunci</a></li>
                        <li><a class="grey-text text-darken-1" href="{{ url('admin/logout') }}"><i class="material-icons">keyboard_tab</i> Logout</a></li>
                    </ul>
                </div>
                <nav class="display-none search-sm">
                    <div class="nav-wrapper">
                        <form id="navbarForm">
                            <div class="input-field search-input-sm">
                                <input class="search-box-sm mb-0" type="search" required="" id="search" placeholder="Cari menu" data-search="template-list">
                                <label class="label-icon" for="search"><i class="material-icons search-sm-icon">search</i></label><i class="material-icons search-sm-close">close</i>
                                <ul class="search-list collection search-list-sm display-none"></ul>
                            </div>
                        </form>
                    </div>
                </nav>
            </nav>
        </div>
    </header>
    <!-- END: Header-->

    <ul class="display-none" id="search-not-found">
        <li class="auto-suggestion"><a class="collection-item display-flex align-items-center" href="#"><span class="material-icons">error_outline</span><span class="member-info">No results found.</span></a></li>
    </ul>