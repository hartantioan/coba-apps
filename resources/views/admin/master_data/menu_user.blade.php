<!-- BEGIN: Page Main-->
<style>
    body.tab-active input:focus {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }
    .modal {
        top:0px !important;
    }
    .modal-content .select2.tab-active {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }
    .sticky-container {
        position: -webkit-sticky; 
        position: sticky;
        top: 0;
        z-index: 1000;
        background-color: white; 
        padding: 10px 0; 
        border-bottom: 1px solid #ddd; 
    }
    .navbar-fixed nav {
        position:unset !important;
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
                            <li class="breadcrumb-item"><a href="#">{{ Str::title(str_replace('_',' ',Request::segment(3))) }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{ Str::title(str_replace('_',' ',Request::segment(4))) }}
                            </li>
                        </ol>
                    </div>
                    <div class="col s4 m6 l6">
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="print();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">{{ __('translations.print') }}</span>
                            <i class="material-icons right">local_printshop</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="exportExcel();">
                            <i class="material-icons hide-on-med-and-up">view_list</i>
                            <span class="hide-on-small-onl">Excel</span>
                            <i class="material-icons right">view_list</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="loadDataTable();">
                            <i class="material-icons hide-on-med-and-up">refresh</i>
                            <span class="hide-on-small-onl">{{ __('translations.refresh') }}</span>
                            <i class="material-icons right">refresh</i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12">
            <div class="container row">
                <div class="col s12 m4">
                    <table class="bordered" id="table-akses">
                        <thead style="background-color:rgb(176, 212, 212) !important;">
                            <tr>
                                <th width="20%" class="center" {{-- rowspan="2" --}} colspan="2">Menu</th>
                            </tr>
                        </thead>
                        <tbody id="table-menu">
                            @foreach($menu as $m)
                                @if($m->sub()->exists())
                                    <tr>
                                        <td>
                                            {{ $m->name }}
                                        </td>
                                        <td>
                                            @if (!$m->childHasChild())
                                                <label>
                                                    <input type="checkbox" class="checkboxUse" onclick="checkAllUse(this,{{ $m->id }},'use')" data-id="{{ $m->id }}"/>
                                                    <span>Pilih</span>
                                                </label>
                                            @endif
                                        </td>
                                       
                                    </tr>
                                    @foreach($m->sub()->where('status','1')->oldest('order')->get() as $msub)
                                        @if($msub->sub()->exists())
                                            <tr>
                                                <td>
                                                    {!! '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$msub->name !!}
                                                </td>
                                                <td>
                                                    <label>
                                                        <input type="checkbox" class="checkboxUse" onclick="checkAllUse(this,{{ $msub->id }},'use')" data-id="{{ $msub->id }}"/>
                                                        <span>Pilih</span>
                                                    </label>
                                                </td>
                                                
                                            </tr>
                                            @foreach($msub->sub()->where('status','1')->oldest('order')->get() as $msub2)
                                                @if($msub2->sub()->exists())

                                                @else
                                                    <tr>
                                                        <td>
                                                            {!! '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$msub2->name !!}
                                                        </td>
                                                        <td class="center">
                                                            <label>
                                                                <input type="checkbox" name="checkboxUse[]" id="checkboxUse{{ $msub2->id }}" value="{{ $msub2->id }}" data-parent="{{ $msub2->parentsub->id }}"/>
                                                                <span>Pilih</span>
                                                            </label>
                                                            
                                                        </td>
                                                        
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @else
                                            <tr>
                                                <td>
                                                    {!! '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$msub->name !!}
                                                </td>
                                                <td class="center">
                                                    <label>
                                                        <input type="checkbox" name="checkboxUse[]" id="checkboxUse{{ $msub->id }}" value="{{ $msub->id }}" data-parent="{{ $msub->parentsub->id }}" />
                                                        <span>Pilih</span>
                                                    </label>
                                                    
                                                </td>
                                                
                                            </tr>
                                        @endif
                                    @endforeach
                                @else
                                    <tr>
                                        <td>
                                            {!! $m->name !!}
                                        </td>
                                        <td class="center">
                                            <label>
                                                <input type="checkbox" name="checkboxUse[]" id="checkboxUse{{ $m->id }}" value="{{ $m->id }}" data-parent=""/>
                                                <span>Pilih</span>
                                            </label>
                                        </td>
                                        
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="col s12 m8 sticky-container">
                    <div class="card">
                        <div class="card-content">
                            <div class="row">
                                <div class="col s12">
                                    <button class="btn waves-effect waves-light right submit" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
                                    <table style="width: 100%;line-height: inherit;text-align: left;" >
                                        <thead>
                                            <tr>
                                                <th>View</th>
                                                <th>Create/Update/Duplicate</th>
                                                <th>Delete</th>
                                                <th>Void</th>
                                                <th>Journal</th>
                                                <th>Laporan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <label>
                                                        <input type="checkbox" id="checkboxView"/>
                                                        <span>Pilih</span>
                                                    </label>
                                                </td>
                                                <td>
                                                  
                                                    <label>
                                                        <input type="checkbox" id="checkboxUpdate" />
                                                        <span>Pilih</span>
                                                    </label>
                                                    
                                                </td>
                                                <td>
                                                    
                                                    <label>
                                                        <input type="checkbox" id="checkboxDelete"/>
                                                        <span>Pilih</span>
                                                    </label>
                                                    
                                                </td>
                                                <td>
                                                    
                                                    <label>
                                                        <input type="checkbox" id="checkboxVoid"/>
                                                        <span>Pilih</span>
                                                    </label>
                                                    
                                                </td>
                                                <td>
                                                   
                                                    <label>
                                                        <input type="checkbox" id="checkboxJournal"/>
                                                        <span>Pilih</span>
                                                    </label>
                                                    
                                                </td>
                                                <td>
                                                    
                                                    <label>
                                                        <input type="checkbox" id="checkboxReport"/>
                                                        <span>Pilih</span>
                                                    </label>
                                                    
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table id="datatable_serverside" style="width: 100%;line-height: inherit;text-align: left;" >
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ __('translations.name') }}</th>
                                                <th>Username</th>
                                                <th>NIK/Code</th>
                                                <th>{{ __('translations.type') }}</th>
                                                <th>Grup</th>
                                                <th>Posisi</th>
                                                <th>{{ __('translations.status') }}</th>
                                                <th>{{ __('translations.action') }}</th>
                                            </tr>
                                        </thead>
                                    </table>
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
<div id="modal3" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>{{ __('translations.add') }}/{{ __('translations.edit') }} Hak Akses - <span id="tempname"></span></h4>
                <form class="row" id="form_data_access" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert_access" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <input type="hidden" id="tempuseraccess" name="tempuseraccess">
                        <div class="col s12">
                            <ul class="tabs">
                                <li class="tab col m4"><a class="active" href="#accessform">Akses Form/Menu</a></li>
                                <li class="tab col m4"><a href="#accessdata">Akses Data</a></li>
                                <li class="tab col m4"><a href="#copyaccess">Salin Akses ke BP lain</a></li>
                            </ul>
                            <div id="accessform" class="col s12 active">
                                <p class="mt-2 mb-2">
                                    <table class="bordered" id="table-menu-access">
                                        <thead style="position:sticky;top: 40px !important;background-color:rgb(176, 212, 212) !important;">
                                            <tr>
                                                <th width="20%" class="center" rowspan="3">Menu</th>
                                                <th width="80%" class="center" colspan="6">Akses</th>
                                            </tr>
                                            <tr>
                                                <th width="13%" class="center">View</th>
                                                <th width="13%" class="center">Create/Update/Duplicate</th>
                                                <th width="13%" class="center">Delete</th>
                                                <th width="13%" class="center">Void</th>
                                                <th width="13%" class="center">Journal</th>
                                                <th width="13%" class="center">Laporan</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-menu">
                                            @foreach($menu as $m)
                                                @if($m->sub()->exists())
                                                    <tr>
                                                        <td>
                                                            {{ $m->name }}
                                                        </td>
                                                        <td>
                                                            @if (!$m->childHasChild())
                                                                <label>
                                                                    <input type="checkbox" class="checkboxView" onclick="checkAll(this,{{ $m->id }},'view')" data-id="{{ $m->id }}"/>
                                                                    <span>Pilih</span>
                                                                </label>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (!$m->childHasChild())
                                                            <label>
                                                                <input type="checkbox" class="checkboxUpdate" onclick="checkAll(this,{{ $m->id }},'update')"/>
                                                                <span>Pilih</span>
                                                            </label>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (!$m->childHasChild())
                                                            <label>
                                                                <input type="checkbox" class="checkboxDelete" onclick="checkAll(this,{{ $m->id }},'delete')"/>
                                                                <span>Pilih</span>
                                                            </label>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (!$m->childHasChild())
                                                            <label>
                                                                <input type="checkbox" class="checkboxVoid" onclick="checkAll(this,{{ $m->id }},'void')"/>
                                                                <span>Pilih</span>
                                                            </label>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (!$m->childHasChild())
                                                            <label>
                                                                <input type="checkbox" class="checkboxJournal" onclick="checkAll(this,{{ $m->id }},'journal')"/>
                                                                <span>Pilih</span>
                                                            </label>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (!$m->childHasChild())
                                                            <label>
                                                                <input type="checkbox" class="checkboxJournal" onclick="checkAll(this,{{ $m->id }},'report')"/>
                                                                <span>Pilih</span>
                                                            </label>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @foreach($m->sub()->where('status','1')->oldest('order')->get() as $msub)
                                                        @if($msub->sub()->exists())
                                                            <tr>
                                                                <td>
                                                                    {!! '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$msub->name !!}
                                                                </td>
                                                                <td>
                                                                    <label>
                                                                        <input type="checkbox" class="checkboxView" onclick="checkAll(this,{{ $msub->id }},'view')" data-id="{{ $msub->id }}"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                </td>
                                                                <td>
                                                                    <label>
                                                                        <input type="checkbox" class="checkboxUpdate" onclick="checkAll(this,{{ $msub->id }},'update')"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                </td>
                                                                <td>
                                                                    <label>
                                                                        <input type="checkbox" class="checkboxDelete" onclick="checkAll(this,{{ $msub->id }},'delete')"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                </td>
                                                                <td>
                                                                    <label>
                                                                        <input type="checkbox" class="checkboxVoid" onclick="checkAll(this,{{ $msub->id }},'void')"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                </td>
                                                                <td>
                                                                    <label>
                                                                        <input type="checkbox" class="checkboxJournal" onclick="checkAll(this,{{ $msub->id }},'journal')"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                </td>
                                                                <td>
                                                                    <label>
                                                                        <input type="checkbox" class="checkboxReport" onclick="checkAll(this,{{ $msub->id }},'journal')"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                </td>
                                                            </tr>
                                                            @foreach($msub->sub()->where('status','1')->oldest('order')->get() as $msub2)
                                                                @if($msub2->sub()->exists())
    
                                                                @else
                                                                    <tr>
                                                                        <td>
                                                                            {!! '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$msub2->name !!}
                                                                        </td>
                                                                        <td class="center">
                                                                            <label>
                                                                                <input type="checkbox" name="checkboxView[]" id="checkboxView{{ $msub2->id }}" value="{{ $msub2->id }}" data-parent="{{ $msub2->parentsub->id }}" onclick="showDataView(this);"/>
                                                                                <span>Pilih</span>
                                                                            </label>
                                                                            @if ($msub2->type == '1')
                                                                            <div class="switch">
                                                                                <label>
                                                                                    Tidak
                                                                                    <input type="checkbox" name="checkboxViewData[]" id="checkboxViewData{{ $msub2->id }}" value="{{ $msub2->id }}" data-parent="{{ $msub2->parentsub->id }}" disabled>
                                                                                    <span class="lever"></span>
                                                                                    Semua Data
                                                                                </label>
                                                                            </div>
                                                                            @endif
                                                                        </td>
                                                                        <td class="center">
                                                                            <label>
                                                                                <input type="checkbox" name="checkboxUpdate[]" id="checkboxUpdate{{ $msub2->id }}" value="{{ $msub2->id }}" data-parent="{{ $msub2->parentsub->id }}"/>
                                                                                <span>Pilih</span>
                                                                            </label>
                                                                        </td>
                                                                        <td class="center">
                                                                            <label>
                                                                                <input type="checkbox" name="checkboxDelete[]" id="checkboxDelete{{ $msub2->id }}" value="{{ $msub2->id }}" data-parent="{{ $msub2->parentsub->id }}"/>
                                                                                <span>Pilih</span>
                                                                            </label>
                                                                        </td>
                                                                        <td class="center">
                                                                            @if ($msub2->type == '1')
                                                                            <label>
                                                                                <input type="checkbox" name="checkboxVoid[]" id="checkboxVoid{{ $msub2->id }}" value="{{ $msub2->id }}" data-parent="{{ $msub2->parentsub->id }}"/>
                                                                                <span>Pilih</span>
                                                                            </label>
                                                                            @endif
                                                                        </td>
                                                                        <td class="center">
                                                                            @if ($msub2->type == '1')
                                                                            <label>
                                                                                <input type="checkbox" name="checkboxJournal[]" id="checkboxJournal{{ $msub2->id }}" value="{{ $msub2->id }}" data-parent="{{ $msub2->parentsub->id }}"/>
                                                                                <span>Pilih</span>
                                                                            </label>
                                                                            @endif
                                                                        </td>
                                                                        <td class="center">
                                                                            @if ($msub2->type == '1')
                                                                            <label>
                                                                                <input type="checkbox" name="checkboxReport[]" id="checkboxReport{{ $msub2->id }}" value="{{ $msub2->id }}" data-parent="{{ $msub2->parentsub->id }}" onclick="showDataReport(this);"/>
                                                                                <span>Pilih</span>
                                                                            </label>
                                                                            <div class="switch">
                                                                                <label>
                                                                                    Tidak
                                                                                    <input type="checkbox" name="checkboxReportData[]" id="checkboxReportData{{ $msub2->id }}" value="{{ $msub2->id }}" data-parent="{{ $msub2->parentsub->id }}" disabled>
                                                                                    <span class="lever"></span>
                                                                                    Semua Data
                                                                                </label>
                                                                            </div>
                                                                            <div class="switch">
                                                                                <label>
                                                                                    Tidak
                                                                                    <input type="checkbox" name="checkboxShowNominal[]" id="checkboxShowNominal{{ $msub2->id }}" value="{{ $msub2->id }}" data-parent="{{ $msub2->parentsub->id }}" disabled>
                                                                                    <span class="lever"></span>
                                                                                    Nominal
                                                                                </label>
                                                                            </div>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            <tr>
                                                                <td>
                                                                    {!! '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$msub->name !!}
                                                                </td>
                                                                <td class="center">
                                                                    <label>
                                                                        <input type="checkbox" name="checkboxView[]" id="checkboxView{{ $msub->id }}" value="{{ $msub->id }}" data-parent="{{ $msub->parentsub->id }}" onclick="showDataView(this);"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                    @if ($msub->type == '1')
                                                                    <div class="switch">
                                                                        <label>
                                                                            Tidak
                                                                            <input type="checkbox" name="checkboxViewData[]" id="checkboxViewData{{ $msub->id }}" value="{{ $msub->id }}" data-parent="{{ $msub->parentsub->id }}" disabled>
                                                                            <span class="lever"></span>
                                                                            Semua Data
                                                                        </label>
                                                                    </div>
                                                                    @endif
                                                                </td>
                                                                <td class="center">
                                                                    <label>
                                                                        <input type="checkbox" name="checkboxUpdate[]" id="checkboxUpdate{{ $msub->id }}" value="{{ $msub->id }}" data-parent="{{ $msub->parentsub->id }}"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                </td>
                                                                <td class="center">
                                                                    <label>
                                                                        <input type="checkbox" name="checkboxDelete[]" id="checkboxDelete{{ $msub->id }}" value="{{ $msub->id }}" data-parent="{{ $msub->parentsub->id }}"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                </td>
                                                                <td class="center">
                                                                    @if ($msub->type == '1')
                                                                    <label>
                                                                        <input type="checkbox" name="checkboxVoid[]" id="checkboxVoid{{ $msub->id }}" value="{{ $msub->id }}" data-parent="{{ $msub->parentsub->id }}"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                    @endif
                                                                </td>
                                                                <td class="center">
                                                                    @if ($msub->type == '1')
                                                                    <label>
                                                                        <input type="checkbox" name="checkboxJournal[]" id="checkboxJournal{{ $msub->id }}" value="{{ $msub->id }}" data-parent="{{ $msub->parentsub->id }}"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                    @endif
                                                                </td>
                                                                <td class="center">
                                                                    @if ($msub->type == '1')
                                                                    <label>
                                                                        <input type="checkbox" name="checkboxReport[]" id="checkboxReport{{ $msub->id }}" value="{{ $msub->id }}" data-parent="{{ $msub->parentsub->id }}" onclick="showDataReport(this);"/>
                                                                        <span>Pilih</span>
                                                                    </label>
                                                                    <div class="switch">
                                                                        <label>
                                                                            Tidak
                                                                            <input type="checkbox" name="checkboxReportData[]" id="checkboxReportData{{ $msub->id }}" value="{{ $msub->id }}" data-parent="{{ $msub->parentsub->id }}" disabled>
                                                                            <span class="lever"></span>
                                                                            Semua Data
                                                                        </label>
                                                                    </div>
                                                                    <div class="switch">
                                                                        <label>
                                                                            Tidak
                                                                            <input type="checkbox" name="checkboxShowNominal[]" id="checkboxShowNominal{{ $msub->id }}" value="{{ $msub->id }}" data-parent="{{ $msub->parentsub->id }}" disabled>
                                                                            <span class="lever"></span>
                                                                            Nominal
                                                                        </label>
                                                                    </div>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td>
                                                            {!! $m->name !!}
                                                        </td>
                                                        <td class="center">
                                                            <label>
                                                                <input type="checkbox" name="checkboxView[]" id="checkboxView{{ $m->id }}" value="{{ $m->id }}" data-parent=""/>
                                                                <span>Pilih</span>
                                                            </label>
                                                        </td>
                                                        <td class="center">
                                                            <label>
                                                                <input type="checkbox" name="checkboxUpdate[]" id="checkboxUpdate{{ $m->id }}" value="{{ $m->id }}" data-parent=""/>
                                                                <span>Pilih</span>
                                                            </label>
                                                        </td>
                                                        <td class="center">
                                                            <label>
                                                                <input type="checkbox" name="checkboxDelete[]" id="checkboxDelete{{ $m->id }}" value="{{ $m->id }}" data-parent=""/>
                                                                <span>Pilih</span>
                                                            </label>
                                                        </td>
                                                        <td class="center">
                                                            <label>
                                                                <input type="checkbox" name="checkboxVoid[]" id="checkboxVoid{{ $m->id }}" value="{{ $m->id }}" data-parent=""/>
                                                                <span>Pilih</span>
                                                            </label>
                                                        </td>
                                                        <td class="center">
                                                            <label>
                                                                <input type="checkbox" name="checkboxJournal[]" id="checkboxJournal{{ $m->id }}" value="{{ $m->id }}" data-parent=""/>
                                                                <span>Pilih</span>
                                                            </label>
                                                        </td>
                                                        <td class="center">
                                                            <label>
                                                                <input type="checkbox" name="checkboxReport[]" id="checkboxReport{{ $m->id }}" value="{{ $m->id }}" data-parent=""/>
                                                                <span>Pilih</span>
                                                            </label>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </p>
                            </div>
                            <div id="accessdata" class="col s12">
                                <div class="row mt-1 center-align">
                                    <div class="col s12">
                                        <h5 class="card-title center">Penempatan (Plant)</h5>
                                        <table class="bordered centered">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2">NO</th>
                                                    <th rowspan="2">NAMA</th>
                                                    <th rowspan="2">TIPE</th>
                                                    <th rowspan="2">PERUSAHAAN</th>
                                                    <th>AKSES</th>
                                                </tr>
                                                <tr>
                                                    <th>
                                                        <label>
                                                            <input type="checkbox" onclick="checkAllPlace(this);" id="check-all-place">
                                                            <span>{{ __('translations.all') }}</span>
                                                        </label>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($place as $row)
                                                <tr>
                                                    <td>{{ $row->code }}</td>
                                                    <td>{{ $row->name }}</td>
                                                    <td>{{ $row->type() }}</td>
                                                    <td>{{ $row->company->name }}</td>
                                                    <td>
                                                        <label>
                                                            <input type="checkbox" name="checkplace[]" id="checkplace{{ $row->id }}" value="{{ $row->id }}">
                                                            <span>Pilih</span>
                                                        </label>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col s12">
                                        <h5 class="card-title center">{{ __('translations.warehouse') }}</h5>
                                        <table class="bordered centered">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2">NO</th>
                                                    <th rowspan="2">NAMA</th>
                                                    <th>AKSES</th>
                                                </tr>
                                                <tr>
                                                    <th>
                                                        <label>
                                                            <input type="checkbox" onclick="checkAllWarehouse(this);" id="check-all-warehouse">
                                                            <span>{{ __('translations.all') }}</span>
                                                        </label>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($warehouse as $row)
                                                <tr>
                                                    <td>{{ $row->code }}</td>
                                                    <td>{{ $row->name }}</td>
                                                    <td>
                                                        <label>
                                                            <input type="checkbox" name="checkwarehouse[]" id="checkwarehouse{{ $row->id }}" value="{{ $row->id }}">
                                                            <span>Pilih</span>
                                                        </label>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div id="copyaccess" class="col s12">
                                <h5 align="center">Silahkan pilih target karyawan untuk menerima salinan.</h5>
                                <div class="row">
                                    <div class="input-field col s12">
                                        <select class="browser-default" multiple id="arr_user" name="arr_user[]"></select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col s12 mt-3">
                            
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat mr-1">{{ __('translations.close') }}</a>
        <button class="btn waves-effect waves-light right submit" onclick="saveAccess();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
    </div>
</div>





<!-- END: Page Main-->
<script>
    var selectedRows = {};
    document.addEventListener('focusin', function (event) {
        const select2Container = event.target.closest('.modal-content .select2');
        const activeSelect2 = document.querySelector('.modal-content .select2.tab-active');
        if (event.target.closest('.modal-content')) {
            document.body.classList.add('tab-active');
        }
        
        
        if (activeSelect2 && !select2Container) {
            activeSelect2.classList.remove('tab-active');
        }

        
        if (select2Container) {
            select2Container.classList.add('tab-active');
        }
    });

    document.addEventListener('mousedown', function () {
        const activeSelect2 = document.querySelector('.modal-content .select2.tab-active');
        document.body.classList.remove('tab-active');
        if (activeSelect2) {
            activeSelect2.classList.remove('tab-active');
        }
    });
    $(function() {
        
        loadDataTable();

        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
            
        });

        $('#modal3').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
                $('.tabs').tabs();
                $('.modal-content').scrollTop(0);
            },
            onCloseEnd: function(modal, trigger){
                $('#tempuseraccess').val('');
                $('#tempname').text('');
                $('#form_data_access input:checkbox').prop( "checked", false);
                $('#form_data_access input[name="checkboxViewData[]"]').prop( "disabled", true);
                $('#arr_user').empty();
            }
        });

    });

    function checkAll(element,parent,mode){
        var param = '';
        if(mode == 'view'){
            param = 'checkboxView';
        }
        if(mode == 'update'){
            param = 'checkboxUpdate';
        }
        if(mode == 'delete'){
            param = 'checkboxDelete';
        }
        if(mode == 'void'){
            param = 'checkboxVoid';
        }
        if(mode == 'journal'){
            param = 'checkboxJournal';
        }
        
        if($(element).is(':checked')){
            $('input[name^="' + param + '"][data-parent="' + parent + '"]').each(function(){
                if(!$(this).is(':checked')){
                    $(this).prop( "checked", true);
                }
            });
        }else{
            $('input[name^="' + param + '"][data-parent="' + parent + '"]').each(function(){
                if($(this).is(':checked')){
                    $(this).prop( "checked", false);
                }
            });
        }
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
            "order": [[0, 'desc']],
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    status : $('#filter_status').val(),
                    type : $('#filter_type').val(),
                    group : $('#group_type').val()
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
                { name: 'name', className: '' },
                { name: 'username', className: 'center-align' },
                { name: 'id_card', className: 'center-align' },
                { name: 'type', searchable: false, orderable: false, className: 'center-align' },
                { name: 'group_id', searchable: false, orderable: false, className: 'center-align' },
                { name: 'group_id', searchable: false, orderable: false, className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'right-align' },
            ],
            dom: 'Blfrtip',
           
            "language": {
                "lengthMenu": "Menampilkan _MENU_ data per halaman",
                "zeroRecords": "Data tidak ditemukan / kosong",
                "info": "Menampilkan halaman _PAGE_ / _PAGES_ dari total _TOTAL_ data",
                "infoEmpty": "Data tidak ditemukan / kosong",
                "infoFiltered": "(disaring dari _MAX_ total data)",
                "search": "Cari",
                "paginate": {
                    first:      "<<",
                    previous:   "<",
                    next:       ">",
                    last:       ">>"
                },
                
                "select": {
                    rows: Object.keys(selectedRows).length+"baris terpilih"
                }
            },
            select: {
                style: 'multi'
            },
            rowCallback: function(row, data) {
            var id = data[0]; 
            if (selectedRows[id]) {
                $(row).addClass('selected');
            }
        }
        });

        $('#datatable_serverside tbody').on('click', 'tr', function() {
            var id = window.table.row(this).data()[0];
            if ($(this).hasClass('selected')) {
                $(this).removeClass('selected');
                delete selectedRows[id];
            } else {
                $(this).addClass('selected');
                selectedRows[id] =  window.table.row(this).data()[3];
            }
        });

        window.table.on('draw', function() {
            $('#datatable_serverside tbody tr').each(function() {
                var id = window.table.row(this).data()[0];
                if (selectedRows[id]) {
                    $(this).addClass('selected');
                }
            });
        });
       
        $('select[name="datatable_serverside_length"]').addClass('browser-default');
	}

    function access(id,name){
        $('#modal3').modal('open');
        $('#tempuseraccess').val(id);
        $('#tempname').text(name);

		$.ajax({
			 url: '{{ Request::url() }}/get_access',
			 type: 'POST',
			 dataType: 'JSON',
			 data: {
				id: id
			 },
			 headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			 },
			 beforeSend: function() {
				loadingOpen('.modal-content');
			 },
			 success: function(response) {
				loadingClose('.modal-content');

                if(response.menus.length > 0){
                    $.each(response.menus, function(i, val) {
                        $('#checkbox' + val.type + val.menu_id).prop( "checked", true);
                        if(val.type == 'View'){
                            $('#checkbox' + val.type + 'Data' + val.menu_id).prop( "disabled", false);
                            if(val.mode == 'all'){
                                $('#checkbox' + val.type + 'Data' + val.menu_id).prop( "checked", true);
                            }
                        }
                        if(val.type == 'Report'){
                            $('#checkbox' + val.type + 'Data' + val.menu_id).prop( "disabled", false);
                            if(val.mode == 'all'){
                                $('#checkbox' + val.type + 'Data' + val.menu_id).prop( "checked", true);
                            }
                            $('#checkboxShowNominal' + val.menu_id).prop( "disabled", false);
                            if(val.show_nominal == '1'){
                                $('#checkboxShowNominal' + val.menu_id).prop( "checked", true);
                            }
                        }
                    });
                }

                if(response.places.length > 0){
                    $.each(response.places, function(i, val) {
                        $('#checkplace' + val.id).prop( "checked", true);
                    });
                }

                if(response.warehouses.length > 0){
                    $.each(response.warehouses, function(i, val) {
                        $('#checkwarehouse' + val.id).prop( "checked", true);
                    });
                }
			 },
			 error: function() {
				loadingClose('.modal-content');
				swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
			 }
		});
    }

    function saveAccess(){
		swal({
            title: "Apakah anda yakin simpan akses?",
            text: "Hati-hati dalam menentukan hak akses!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                
                var formData = new FormData($('#form_data_access')[0]);
                
                $.ajax({
                    url: '{{ Request::url() }}/create_access',
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
                        $('#validation_alert_access').hide();
                        $('#validation_alert_access').html('');
                        loadingOpen('#modal3');
                    },
                    success: function(response) {
                        loadingClose('#modal3');

                        if(response.status == 200) {
                            $('#modal3').modal('close');
                            M.toast({
                                html: response.message
                            });
                        } else if(response.status == 422) {
                            $('#validation_alert_access').show();
                            $('.modal-content').scrollTop(0);
                            
                            swal({
                                title: 'Ups! Validation',
                                text: 'Check your form.',
                                icon: 'warning'
                            });

                            $.each(response.error, function(i, val) {
                                $.each(val, function(i, val) {
                                    $('#validation_alert_access').append(`
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
                        loadingClose('#modal3');
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
    function save(){
        const checkedCheckboxes = document.querySelectorAll('input[name="checkboxUse[]"]:checked');
        const checkedDataMenu = [];
        checkedCheckboxes.forEach((checkbox) => {
            const id = checkbox.value;
            const parentId = checkbox.getAttribute('data-parent');
            checkedDataMenu.push(id);
        });

        var view = $('#checkboxView').prop("checked") ? 1 : 0;
        var update = $('#checkboxUpdate').prop("checked") ? 1 : 0;
        var deletes = $('#checkboxDelete').prop("checked") ? 1 : 0;
        var voids = $('#checkboxVoid').prop("checked") ? 1 : 0;
        var journal = $('#checkboxJournal').prop("checked") ? 1 : 0;
        var report = $('#checkboxReport').prop("checked") ? 1 : 0;

        $.ajax({
            url: '{{ Request::url() }}/save_access_batch',
            type: 'POST',
            dataType: 'JSON',
            data: {
                view: view,
                update:update,
                deletes:deletes,
                voids:voids,
                journal:journal,
                report:report,
                menu:checkedDataMenu,
                user:selectedRows,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('#main');
            },
            success: function(response) {
                loadingClose('#main');
                window.location.reload();
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
        /* $.ajax({
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
                loadingOpen('.modal-content');
            },
            success: function(response) {
                loadingClose('.modal-content');
                if(response.status == 200) {
                    success();
                    M.toast({
                        html: response.message
                    });
                } else if(response.status == 422) {
                    $('#validation_alert').show();
                    $('.modal-content').scrollTop(0);
                    
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
                loadingClose('.modal-content');
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
            }
        }); */
    }

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
    }

    function show(id){
        $.ajax({
            url: '{{ Request::url() }}/show',
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
                $('#code').val(response.code);
                $('#line_id').val(response.line_id).formSelect();
                $('#name').val(response.name);
                $('#note').val(response.note);
                if(response.status == '1'){
                    $('#status').prop( "checked", true);
                }else{
                    $('#status').prop( "checked", false);
                }
                $('.modal-content').scrollTop(0);
                $('#code').focus();
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

    function destroy(id){
        swal({
            title: "Apakah anda yakin?",
            text: "Anda tidak bisa mengembalikan data yang terhapus!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                $.ajax({
                    url: '{{ Request::url() }}/destroy',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { id : id },
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

    var printService = new WebSocketPrinter({
        onConnect: function () {
            
        },
        onDisconnect: function () {
            /* M.toast({
                html: 'Aplikasi penghubung printer tidak terinstall. Silahkan hubungi tim EDP.'
            }); */
        },
        onUpdate: function (message) {
            
        },
    });

    function print(){
        var search = window.table.search(), status = $('#filter_status').val(), type = $('#filter_type').val(), company = $('#filter_company').val(), account = $('#filter_account').val();
        arr_id_temp=[];
        $.map(window.table.rows('.selected').nodes(), function (item) {
            var poin = $(item).find('td:nth-child(2)').text().trim();
            arr_id_temp.push(poin);
           
        });
        
        $.ajax({
            url: '{{ Request::url() }}/print',
            type: 'POST',
            dataType: 'JSON',
            data: {
                arr_id: arr_id_temp,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            success: function(response) {
                printService.submit({
                    'type': 'INVOICE',
                    'url': response.message
                })
                
               
            },
            error: function() {
                $('.modal-content').scrollTop(0);
                loadingClose('.modal-content');
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
            }
        });
    }

    function exportExcel(){
        var search = window.table.search();
        var status = $('#filter_status').val();
        
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status;
    }

    function checkAllUse(element,parent,mode){
        var param = '';
        if(mode == 'use'){
            param = 'checkboxUse';
        }
        
        if($(element).is(':checked')){
            $('input[name^="' + param + '"][data-parent="' + parent + '"]').each(function(){
                if(!$(this).is(':checked')){
                    $(this).prop( "checked", true);
                }
            });
        }else{
            $('input[name^="' + param + '"][data-parent="' + parent + '"]').each(function(){
                if($(this).is(':checked')){
                    $(this).prop( "checked", false);
                }
            });
        }
    }

    function checkAllPlace(element){
        if($(element).is(':checked')){
            $('input[name^="checkplace"]').each(function(){
                if(!$(this).is(':checked')){
                    $(this).prop( "checked", true);
                }
            });
        }else{
            $('input[name^="checkplace"]').each(function(){
                if($(this).is(':checked')){
                    $(this).prop( "checked", false);
                }
            });
        }
    }

    function showDataView(element){
        if($(element).is(':checked')){
            $(element).parent().parent().find('input[name="checkboxViewData[]"]').prop('disabled',false);
        }else{
            if($(element).parent().parent().find('input[name="checkboxViewData[]"]').is(':checked')){
                $(element).parent().parent().find('input[name="checkboxViewData[]"]').prop('checked',false);
            }
            $(element).parent().parent().find('input[name="checkboxViewData[]"]').prop('disabled',true);
        }
    }

    function showDataReport(element){
        if($(element).is(':checked')){
            $(element).parent().parent().find('input[name="checkboxReportData[]"]').prop('disabled',false);
            $(element).parent().parent().find('input[name="checkboxShowNominal[]"]').prop('disabled',false);
        }else{
            if($(element).parent().parent().find('input[name="checkboxReportData[]"]').is(':checked')){
                $(element).parent().parent().find('input[name="checkboxReportData[]"]').prop('checked',false);
                $(element).parent().parent().find('input[name="checkboxShowNominal[]"]').prop('checked',false);
            }
            $(element).parent().parent().find('input[name="checkboxReportData[]"]').prop('disabled',true);
            $(element).parent().parent().find('input[name="checkboxShowNominal[]"]').prop('disabled',true);
        }
    }

    function checkAllWarehouse(element){
        if($(element).is(':checked')){
            $('input[name^="checkwarehouse"]').each(function(){
                if(!$(this).is(':checked')){
                    $(this).prop( "checked", true);
                }
            });
        }else{
            $('input[name^="checkwarehouse"]').each(function(){
                if($(this).is(':checked')){
                    $(this).prop( "checked", false);
                }
            });
        }
    }
</script>