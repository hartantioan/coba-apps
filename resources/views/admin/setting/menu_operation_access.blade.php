<!-- BEGIN: Page Main-->
<div id="main">
    <div class="row">
        <div class="pt-3 pb-1" id="breadcrumbs-wrapper">
            <!-- Search for small screen-->
            <div class="container">
                <div class="row">
                    <div class="col s12 m6 l6">
                        <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ $title.' - '.$menu->fullName() }}</span></h5>
                    </div>
                    <div class="col s12 m6 l6 right-align-md">
                        <ol class="breadcrumbs mb-0">
                            <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item"><a href="#">{{ Str::ucfirst(Request::segment(2)) }}</a>
                            </li>
                            <li class="breadcrumb-item"><a href="#">{{ Str::ucfirst(Request::segment(3)) }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{ Str::title(str_replace('_',' ',Request::segment(4))) }}
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
                        <div class="col s12">
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">
                                        List Posisi/Level & Hak Akses
                                        <a href="{{ url('admin/setting/menu') }}" class="waves-effect waves-light btn gradient-45deg-purple-deep-orange gradient-shadow right">Kembali ke Menu</a>
                                    </h4>
                                    <div class="row mt-3">
                                        <div class="col s12">
                                            <table class="bordered centered">
                                                <thead>
                                                    <tr>
                                                        <th colspan="3">PEGAWAI</th>
                                                        <th colspan="4">AKSES</th>
                                                    </tr>
                                                    <tr>
                                                        <th>Code</th>
                                                        <th>Nama</th>
                                                        <th>Posisi</th>
                                                        <th>View</th>
                                                        <th>Create/Update</th>
                                                        <th>Delete</th>
                                                        <th>Void</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($user as $row)
                                                    <tr>
                                                        <td>{{ $row->employee_no }}</td>
                                                        <td style="text-align:left !important;">{{ $row->name }}</td>
                                                        <td style="text-align:left !important;">{{ $row->position->name ?? '' }}</td>
                                                        <td>
                                                            <label>
                                                                <input type="checkbox" id="checkbox{{ $row->id.'_'.$menu->id }}" value="{{ $row->id.'_'.$menu->id }}" onclick="saveAccess({{ $menu->id }},{{ $row->id }},this,'view');" {{ $row->checkMenu($menu->id,'view') ? 'checked' : '' }}/>
                                                                <span>Pilih</span>
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label>
                                                                <input type="checkbox" id="checkbox{{ $row->id.'_'.$menu->id }}" value="{{ $row->id.'_'.$menu->id }}" onclick="saveAccess({{ $menu->id }},{{ $row->id }},this,'update');" {{ $row->checkMenu($menu->id,'update') ? 'checked' : '' }}/>
                                                                <span>Pilih</span>
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label>
                                                                <input type="checkbox" id="checkbox{{ $row->id.'_'.$menu->id }}" value="{{ $row->id.'_'.$menu->id }}" onclick="saveAccess({{ $menu->id }},{{ $row->id }},this,'delete');" {{ $row->checkMenu($menu->id,'delete') ? 'checked' : '' }}/>
                                                                <span>Pilih</span>
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label>
                                                                <input type="checkbox" id="checkbox{{ $row->id.'_'.$menu->id }}" value="{{ $row->id.'_'.$menu->id }}" onclick="saveAccess({{ $menu->id }},{{ $row->id }},this,'void');" {{ $row->checkMenu($menu->id,'void') ? 'checked' : '' }}/>
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
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="content-overlay"></div>
        </div>
    </div>
</div>

<script>
    $(function() {
        
    });

    function saveAccess(menu,position,element,type){
        var nil = '';

        if($(element).is(':checked')){
            nil = $(element).val();
        }

        $.ajax({
            url: '{{ url("admin/setting/menu/operation_access/create") }}',
            type: 'POST',
            dataType: 'JSON',
            data: {
                val : nil,
                id : menu,
                ps : position,
                tp : type
            },
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

                if(response.status == '500'){
                    if($(element).is(':checked')){
                        $(element).prop( "checked", false);
                    }
                }
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
</script>