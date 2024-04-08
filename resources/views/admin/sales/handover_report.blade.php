<style>
    .modal {
        top:0px !important;
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
                            <li class="breadcrumb-item active">{{ Str::title(str_replace('_',' ',Request::segment(3))) }}
                            </li>
                        </ol>
                    </div>
                    <div class="col s4 m6 l6">
                        
                        {{-- <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="exportExcel();">
                            <i class="material-icons hide-on-med-and-up">view_list</i>
                            <span class="hide-on-small-onl">Excel</span>
                            <i class="material-icons right">view_list</i>
                        </a> --}}
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
                            <ul class="collapsible collapsible-accordion">
                                <li>
                                    <div class="collapsible-header"><i class="material-icons">filter_list</i> FILTER</div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="input-field col m3 s12">
                                                <label class="active" for="account_id">Customer</label>
                                                <select class="browser-default " id="account_id" name="account_id" onchange="loadDataTable()"></select>
                                            </div>
                                            <div class="input-field col m3 s12">
                                                <label class="active" for="type">Status</label>
                                                <select id="type" name="type" onchange="loadDataTable()">
                                                    <option value="">Semua</option>
                                                    <option value="1">Belum TT Invoice</option>
                                                    <option value="2">Belum Kwitansi</option>
                                                    <option value="3">Sudah Kwitansi & Belum TT Kwitansi</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">
                                        List Data
                                    </h4>
                                    <div class="row mt-2">
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
                                                        <th>Invoice</th>
                                                        <th>Customer</th>
                                                        <th>Tanggal</th>
                                                        <th>Tagihan</th>
                                                        <th>Sisa</th>
                                                        <th>TT.Invoice</th>
                                                        <th>Kwitansi</th>
                                                        <th>TT.Kwitansi</th>
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

<div id="modal4" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="show_detail">

            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<script>
    $(function() {
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });
        $('#modal4').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#show_detail').empty();
            }
        });
        loadDataTable();
        select2ServerSide('#account_id', '{{ url("admin/select2/customer") }}');
    });

    /* function exportExcel(){
        var search = window.table.search();
        var item = $('#item_id').val() ? $('#item_id').val():'';
        window.location = "{{ Request::url() }}/export?search=" + search + "&item=" + item;
    } */

    function loadDataTable() {
		window.table = $('#datatable_serverside').DataTable({
            "responsive": false,
            "scrollX": true,
            "stateSave": true,
            "serverSide": true,
            "deferRender": true,
            "destroy": true,
            "iDisplayInLength": 10,
            "order": [[0, 'desc']],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle',
            ],
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
                "buttons": {
                    selectAll: "Pilih semua",
                    selectNone: "Hapus pilihan"
                },
                "select": {
                    rows: "%d baris terpilih"
                }
            },
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    account_id : $('#account_id').val(),
                    type : $('#type').val(),
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
                { name: 'id', className: '', searchable: false },
                { name: 'code', className: '', searchable: false, orderable: false },
                { name: 'account_id', className: '', searchable: false, orderable: false },
                { name: 'date', className: 'center-align', searchable: false, orderable: false },
                { name: 'grandtotal', className: 'right-align' },
                { name: 'balance', className: 'right-align', searchable: false, orderable: false },
                { name: 'handover_invoice', className: 'center-align', searchable: false, orderable: false },
                { name: 'receipt', className: 'center-align', searchable: false, orderable: false },
                { name: 'handover_receipt', className: 'center-align', searchable: false, orderable: false },
            ],
        });
        $('.dt-buttons').appendTo('#datatable_buttons');

        $('select[name="datatable_serverside_length"]').addClass('browser-default');
	}

    function rowDetail(data) {
        $.ajax({
            url: '{{ Request::url() }}/row_detail',
            type: 'GET',
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            data: {
                id: data
            },
            success: function(response) {
                $('#modal4').modal('open');
                $('#show_detail').html(response);
                loadingClose('.modal-content');
            },
            error: function() {
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
            }
        });
	}
</script>