<!-- BEGIN: Page Main-->
<style>
   
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
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12">
            <div class="container">
                <div class="section">
                    
                    <div class="row">
                        <div class="col s12 m12 l12" id="main-display">
                            <ul class="collapsible collapsible-accordion">
                                <li class="active">
                                    <div class="collapsible-header"><i class="material-icons">filter_list</i> FILTER</div>
                                    <div class="collapsible-body">
                                        <form class="row" id="form_data_filter" onsubmit="return false;">
                                            <div class="col s12">
                                                <div id="validation_alert_multi" style="display:none;"></div>
                                            </div>
                                            <div class="col s12">
                                                <div class="row">
                                                    <div class="input-field col m4 s12">
                                                        <input type="hidden" id="temp" name="temp">
                                                        <select class="browser-default" id="period_id" name="period_id"></select>
                                                        <label class="active" for="period_id">Period</label>
                                                    </div>
                                                    
                                                    <div class="col m4 s6 pt-2">
                                                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="filterByDate();">
                                                            <i class="material-icons hide-on-med-and-up">search</i>
                                                            <span class="hide-on-small-onl">Filter</span>
                                                            <i class="material-icons right">search</i>
                                                        </a>
                                                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="reset();">
                                                            <i class="material-icons hide-on-med-and-up">loop</i>
                                                            <span class="hide-on-small-onl">Reset</span>
                                                            <i class="material-icons right">loop</i>
                                                        </a>
                                                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="exportExcel();">
                                                            <i class="material-icons hide-on-med-and-up">view_list</i>
                                                            <span class="hide-on-small-onl">Excel</span>
                                                            <i class="material-icons right">view_list</i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            </div>
                                        </form>  
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="card">
                            <div class="card-content">
                                <h4 class="card-title">List Data</h4>
                                <div class="row">
                                    <div class="col s12">
                                        <div id="datatable_buttons"></div>
                                        <table id="datatable_serverside" class="display responsive-table wrap">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nama</th>
                                                    <th>Jumlah Shift</th>
                                                    <th>t1</th>
                                                    <th>t2</th>
                                                    <th>t3</th>
                                                    <th>t4</th>
                                                    <th>Tepat waktu</th>
                                                    <th>Ijin Kusus</th>
                                                    <th>Sakit</th>
                                                    <th>Dinas Keluar</th>
                                                    <th>Cuti</th>
                                                    <th>Dispen</th>
                                                    <th>Alpha</th>
                                                    <th>WFH</th>
                                                    <th>Datang Tepat Waktu</th>
                                                    <th>Pulang Tepat Waktu</th>
                                                    <th>Lupa Check Clock Pulang</th>
                                                    <th>Lupa Check Clock Datang</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="intro">
                    <div class="row">
                        <div class="col s12">
                            
                        </div>
                    </div>
                </div>
                <!-- / Intro -->
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
        select2ServerSide('#period_id', '{{ url("admin/select2/period") }}');

    });

    function exportExcel(){
        var date = $('#date').val();
        window.location = "{{ Request::url() }}/export?date=" + date;
    }
    function filterByDate(){
        loadDataTable();
    }

    function loadDataTable() {
		window.table = $('#datatable_serverside').DataTable({
            "scrollCollapse": true,
            "scrollY": '400px',
            "responsive": true,
            "stateSave": true,
            "serverSide": true,
            "deferRender": true,
            "destroy": true,
            "iDisplayInLength": 10,
            "order": [[0, 'asc']],
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    period_id: $('#period_id').val()
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
                { name: 'user_id', className: 'center-align' },
                { name: 'period_id', className: 'center-align' },
                { name: 'effective_day', className: 'center-align' },
                { name: 't1', className: 'center-align' },
                { name: 't2', className: 'center-align' },
                { name: 't3', className: 'center-align' },
                { name: 't4', className: 'center-align' },
                { name: 'absent', className: 'center-align' },
                { name: 'special_occasion', className: 'center-align' },
                { name: 'sick', className: 'center-align' },
                { name: 'outstation', className: 'center-align' },
                { name: 'furlough', className: 'center-align' },
                { name: 'dispen', className: 'center-align' },
                { name: 'alpha', className: 'center-align' },
                { name: 'wfh', className: 'center-align' },
                { name: 'arrived_on_time', className: 'center-align' },
                { name: 'out_on_time', className: 'center-align' },
                { name: 'out_log_forget', className: 'center-align' },
                { name: 'arrived_forget', className: 'center-align' },
                ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle' 
            ]
        });
        $('.dt-buttons').appendTo('#datatable_buttons');

        $('select[name="datatable_serverside_length"]').addClass('browser-default');
	}

    function reset(){
        $('#form_data_filter')[0].reset();
    }
</script>