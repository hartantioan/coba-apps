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
                                                        <select class="browser-default" id="period_id" name="period_id" onchange="thead()"></select>
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
                                    <div class="col s12" id="table_monthly">
                                        <div id="datatable_buttons"></div>
                                        <table id="datatable_serverside" class="display responsive-table wrap">
                                            <thead id="thead_shift">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nama</th>
                                                    <th>Jumlah Shift</th>
                                                    <th>Tepat waktu</th>
                                                    <th>Ijin Kusus</th>
                                                    <th>Sakit</th>
                                                    <th>Dinas Keluar</th>
                                                    <th>Cuti</th>
                                                    <th>Dispen</th>
                                                    <th>Alpha</th>
                                                    <th>WFH</th>
                                                    <th>Ijin Datang Telat</th>
                                                    <th>Ijin Pulang Cepat</th>
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
    var column_name=[];
    var column_data_table;
    $(function() {
        
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });
        select2ServerSide('#period_id', '{{ url("admin/select2/period") }}');
        if('{{ $code }}'){
            $('#temp').val('{{$code}}');
           
            
        }
        @if ($code)
            column_data_table =[
                { name: 'user_id', className: 'center-align' },
                { name: 'period_id', className: 'center-align' },
                { name: 'effective_day', className: 'center-align' },
                { name: 'absent', className: 'center-align' },
                { name: 'special_occasion', className: 'center-align' },
                { name: 'sick', className: 'center-align' },
                { name: 'outstation', className: 'center-align' },
                { name: 'furlough', className: 'center-align' },
                { name: 'dispen', className: 'center-align' },
                { name: 'alpha', className: 'center-align' },
                { name: 'wfh', className: 'center-align' },
                { name: 'leave_early', className: 'center-align' },
                { name: 'late', className: 'center-align' },
                { name: 'arrived_on_time', className: 'center-align' },
                { name: 'out_on_time', className: 'center-align' },
                { name: 'out_log_forget', className: 'center-align' },
                { name: 'arrived_forget', className: 'center-align' },
            ];
            var punish_code = @json($punishment_code);
                var string =`
                    <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Jumlah Shift</th>
                `;
                $.each(punish_code,function(i,val){
                    string+=`<th>`+val+`</th>`;
                    column_data_table.push({ name: val, className: 'center-align' });
                });
                string+=`<th>Tepat waktu</th>
                        <th>Ijin Kusus</th>
                        <th>Sakit</th>
                        <th>Dinas Keluar</th>
                        <th>Cuti</th>
                        <th>Dispen</th>
                        <th>Alpha</th>
                        <th>WFH</th>
                        <th>Ijin Datang Telat</th>
                        <th>Ijin Pulang Cepat</th>
                        <th>Datang Tepat Waktu</th>
                        <th>Pulang Tepat Waktu</th>
                        <th>Lupa Check Clock Pulang</th>
                        <th>Lupa Check Clock Datang</th>
                    </tr>`;
            $('#thead_shift').empty();
            $('#thead_shift').append(string);
            
            loadDataTable();
        @endif
    });
    
    function thead(){
        $('#thead_shift').empty();
        console.log($("#period_id").val());
        column_name = [];
        column_data_table =[
            { name: 'user_id', className: 'center-align' },
            { name: 'period_id', className: 'center-align' },
            { name: 'effective_day', className: 'center-align' },
            { name: 'absent', className: 'center-align' },
            { name: 'special_occasion', className: 'center-align' },
            { name: 'sick', className: 'center-align' },
            { name: 'outstation', className: 'center-align' },
            { name: 'furlough', className: 'center-align' },
            { name: 'dispen', className: 'center-align' },
            { name: 'alpha', className: 'center-align' },
            { name: 'wfh', className: 'center-align' },
            { name: 'leave_early', className: 'center-align' },
            { name: 'late', className: 'center-align' },
            { name: 'arrived_on_time', className: 'center-align' },
            { name: 'out_on_time', className: 'center-align' },
            { name: 'out_log_forget', className: 'center-align' },
            { name: 'arrived_forget', className: 'center-align' },
        ];
        if($("#period_id").val()){
            if($("#period_id").select2('data')[0].punishment_code.length>0){
                $.each($("#period_id").select2('data')[0].punishment_code, function(i, value) {
                    column_name.push(value);
                });
            }else{
                column_name=[];
            }
        }else{
            column_name=[];
        }
        var string =`
            <tr>
            <th>#</th>
            <th>Nama</th>
            <th>Jumlah Shift</th>
        `;
        $.each(column_name,function(i,val){
            string+=`<th>`+val+`</th>`;
            column_data_table.push({ name: val, className: 'center-align' });
        });
        string+=`<th>Tepat waktu</th>
                <th>Ijin Kusus</th>
                <th>Sakit</th>
                <th>Dinas Keluar</th>
                <th>Cuti</th>
                <th>Dispen</th>
                <th>Alpha</th>
                <th>WFH</th>
                <th>Ijin Datang Telat</th>
                <th>Ijin Pulang Cepat</th>
                <th>Datang Tepat Waktu</th>
                <th>Pulang Tepat Waktu</th>
                <th>Lupa Check Clock Pulang</th>
                <th>Lupa Check Clock Datang</th>
            </tr>`;
            $('#thead_shift').empty();
            $('#thead_shift').append(string);
        // $.ajax({
        //     url: '{{ Request::url() }}/takePlant',
        //     type: 'POST',
        //     dataType: 'JSON',
        //     data: { 
        //         id : $('#period_id').val()
        //     },
        //     headers: {
        //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //     },
        //     beforeSend: function() {
        //         loadingOpen('.modal-content');
        //     },
        //     success: function(response) {
        //         loadingClose('.modal-content');
        //         $('#thead_shift').append(response.message);
        //         console.log(response.message);
        //     },
        //     error: function() {
        //         swal({
        //             title: 'Ups!',
        //             text: 'Check your internet connection.',
        //             icon: 'error'
        //         });
        //     }
        // });
        
    }

    function exportExcel(){
        var date = $('#date').val();
        window.location = "{{ Request::url() }}/export?date=" + date;
    }
    function filterByDate(){
        $('#temp').val('');
        
        loadDataTable();
    }

    function loadDataTable() {
        if(window.table){
            $('#table_monthly').empty();
            $('#table_monthly').append(`
                <div id="datatable_buttons"></div>
                <table id="datatable_serverside" class="display responsive-table wrap">
                    <thead id="thead_shift">
                        
                    </thead>
                </table>
            `);
            thead();
            console.log(column_data_table);
            window.table = $('#datatable_serverside').DataTable({
                "scrollCollapse": true,
                "scrollY": '400px',
                "responsive": false,
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
                        temp:$('#temp').val(),
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
                columns: column_data_table,
                dom: 'Blfrtip',
                buttons: [
                    'columnsToggle' 
                ]
            });
            $('.dt-buttons').appendTo('#datatable_buttons');

            $('select[name="datatable_serverside_length"]').addClass('browser-default');

            
        }else{
            console.log(column_data_table);
            window.table = $('#datatable_serverside').DataTable({
                "scrollCollapse": true,
                "scrollY": '400px',
                "responsive": false,
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
                        temp:$('#temp').val(),
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
                columns: column_data_table,
                dom: 'Blfrtip',
                buttons: [
                    'columnsToggle' 
                ]
            });
            $('.dt-buttons').appendTo('#datatable_buttons');

            $('select[name="datatable_serverside_length"]').addClass('browser-default');
        }
        
	}

    function reset(){
        $('#form_data_filter')[0].reset();
    }
</script>