<div id="main">
    <div class="row">
        <div class="col s12">
            <div class="container">
                <div class="row">
                    <div class="col s12">
                    </div>
                </div>
            </div>
            <div class="content-overlay"></div>
        </div>
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
                <div class="section section-data-tables">
                    <!-- DataTables example -->
                    
                    <div class="row">
                        
                        <div class="col s12">
                            <div class="card">
                                <div class="card-content">
                                    <ul class="collapsible collapsible-accordion">
                                        <li>
                                            <div class="collapsible-header"><i class="material-icons">filter_list</i> FILTER</div>
                                            <div class="collapsible-body">
                                                <div class="row">
                                                    <div class="col m4 s6 ">
                                                        <label for="start-date" style="font-size:1rem;">Tanggal Mulai :</label>
                                                        <div class="input-field col s12">
                                                        <input type="date" id="start-date" name="start-date"  onchange="loadDataTable()">
                                                        </div>
                                                    </div>
                                                    <div class="col m4 s6 ">
                                                        <label for="finish-date" style="font-size:1rem;">Tanggal Akhir :</label>
                                                        <div class="input-field col s12">
                                                            <input type="date" id="finish-date" name="finish-date"  onchange="loadDataTable()">
                                                        </div>
                                                    </div>
                                                </div>  
                                            </div>
                                        </li>
                                    </ul>
                                    <h4 class="card-title">List Data</h4>
                                    <div class="row">
                                        <div class="col s12">
                                            <div id="datatable_buttons"></div>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">Refresh</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <table id="datatable_serverside" >
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Dari</th>
                                                        <th>Judul</th>
                                                        <th>Catatan</th>
                                                        <th>Waktu</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
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
        loadDataTable();
    });

    function loadDataTable() {
		window.table = $('#datatable_serverside').DataTable({
            "scrollCollapse": true,
            "scrollY": '400px',
            "responsive": false,
            "scrollX":true,
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
                    start_date : $('#start-date').val(),
                    finish_date : $('#finish-date').val(),
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
                        { name: 'from', className: 'center-align' },
                        { name: 'title', className: '' },
                        { name: 'note', className: '' },
                        { name: 'timestamp', className: 'center-align' },
                        { name: 'action', searchable: false, orderable: false, className: 'center-align' }
                    ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle' 
            ]
        });
        $('.dt-buttons').appendTo('#datatable_buttons');
        $('select[name="datatable_serverside_length"]').addClass('browser-default');
	}

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
    }

</script>