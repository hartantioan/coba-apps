<style>
    table > thead > tr > th {
        font-size: 13px !important;
    }

    table.bordered th {
        padding: 5px !important;
    }
</style>
<!-- BEGIN: Page Main-->
<div id="main">
    <div class="row">
        <div class="pt-3 pb-1" id="breadcrumbs-wrapper">
            <!-- Search for small screen-->
            <div class="container">
                <div class="row">
                    <div class="col s12 m6 l6">
                        <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ $title }}</span></h5>
                    </div>
                    <div class="col s12 m6 l6 right-align-md">
                        <ol class="breadcrumbs mb-0">
                            <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item"><a href="#">{{ Str::ucfirst(Request::segment(2)) }}</a>
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
                        <div class="col s12">
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">List {{ $title }}</h4>
                                    <div class="card-alert card blue">
                                        <div class="card-content white-text">
                                            <p>Form ini digunakan untuk mengunduh data hak akses semua karyawan (kosongi) atau perorangan.</p>
                                        </div>
                                    </div>
                                    <div class="row mt-1">
                                        <div class="input-field col s6">
                                            <select class="browser-default select2" id="user_id" name="user_id[]" multiple>
                                                <option value=""></option>
                                                @foreach ($user as $row)
                                                    <option value="{{ $row->id }}">{{ $row->employee_no.' '.$row->name }}</option>
                                                @endforeach
                                            </select>
                                            <label class="active" for="user_id">Pegawai</label>
                                        </div>
                                        <div class="input-field col s3">
                                            <button class="btn waves-effect waves-light" onclick="process();">Unduh <i class="material-icons right">archive</i></button>
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
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        $('input:checkbox').click(function(){
            $('#notifchanged').show();
        });

        refreshAccess();
    });

    function process(){
        var employees = $('#filter_group').val() ? $('#filter_group').val():'';
        window.location = "{{ Request::url() }}/export?plant=" + plant + "&warehouse=" + warehouse+"&item=" + item +"&group=" + group;
    }
</script>