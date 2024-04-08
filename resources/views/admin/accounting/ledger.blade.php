<style>
    #modal2 {
        top:0px !important;
    }
    #text-grandtotal {
        font-size: 50px !important;
        font-weight: 800;
    }
    .select-wrapper, .select2-container {
        height:3rem !important;
    }
    .btn-small {
        padding: 0 1rem !important;
    }
    #data_detail > table > tbody > td{
        padding:2px !important;
    }
</style>
<!-- BEGIN: Page Main-->
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
                <div class="section section-data-tables">
                    <div class="row">
                        <div class="col s12">
                            <ul class="collapsible collapsible-accordion">
                                <li class="active">
                                    <div class="collapsible-header"><i class="material-icons">filter_list</i> FILTER</div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col m3 s6 ">
                                                <label for="company" style="font-size:1rem;">Perusahaan :</label>
                                                <select class="form-control" id="company" name="company" onchange="loadDataTable();">
                                                    @foreach ($company as $rowcompany)
                                                        <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col m3 s6 ">
                                                <label for="coa" style="font-size:1rem;">Coa :</label>
                                                <select class="browser-default" id="coa" name="coa" onchange="loadDataTable();"></select>
                                            </div>
                                            <div class="col m2 s6 ">
                                                <label for="start_date" style="font-size:1rem;">Tanggal Mulai :</label>
                                                <input type="date" max="{{ date('9999'.'-12-31') }}" id="start_date" name="start_date" value="{{ date('Y-m'.'-01') }}" onchange="loadDataTable();">
                                            </div>
                                            <div class="col m2 s6 ">
                                                <label for="finish_date" style="font-size:1rem;">Tanggal Akhir :</label>
                                                <input type="date" max="{{ date('9999'.'-12-31') }}" id="finish_date" name="finish_date" value="{{ date('Y-m-d') }}" onchange="loadDataTable();">
                                            </div>
                                            <div class="col m2 s6 pt-2">
                                                <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="reset();">
                                                    <i class="material-icons center">loop</i>
                                                </a>
                                                <a class="btn btn-small blue waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="exportExcel();">
                                                    <i class="material-icons center">view_list</i>
                                                </a>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="is_closing_journal" style="font-size:1rem;">Jurnal Closing</label>
                                                <div class="input-field">
                                                    <div class="switch mb-1">
                                                        <label>
                                                            Tampilkan
                                                            <input type="checkbox" id="is_closing_journal" name="is_closing_journal" value="1">
                                                            <span class="lever"></span>
                                                            Sembunyikan
                                                        </label>
                                                    </div>
                                                </div>
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
                                                        <th>Coa</th>
                                                        <th>Perusahaan</th>
                                                        <th>Saldo Awal</th>
                                                        <th>Debit</th>
                                                        <th>Kredit</th>
                                                        <th>Saldo khir</th>
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
        </div>
    </div>
</div>

<div id="modal1" class="modal modal-fixed-footer bottom-sheet">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <table class="bordered">
                    <thead>
                        <tr>
                            <th class="center-align">No.</th>
                            <th class="center-align">No Invoice</th>
                            <th class="center-align">Supplier/Vendor</th>
                            <th class="center-align">TGL Post</th>
                            <th class="center-align">TGL Terima</th>
                            <th class="center-align">TGL Jatuh Tempo</th>
                            <th class="center-align">Jatuh Tempo (Hari)</th>
                            <th class="center-align">Grandtotal</th>
                            <th class="center-align">Memo</th>
                            <th class="center-align">Dibayar</th>
                            <th class="center-align">Sisa</th>
                        </tr>
                    </thead>
                    <tbody id="show_detail"></tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal2" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="data_detail">

            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<script>
    $(function(){
        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
        });

        $('#modal2').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#data_detail').empty();
            }
        });

        loadDataTable();

        select2ServerSide('#coa', '{{ url("admin/select2/coa") }}');
    });

    function exportExcel(){
        var start_date = $('#start_date').val(), finish_date = $('#finish_date').val(), coa_id = ($('#coa').val() ? $('#coa').val() : ''), company_id = ($('#company').val() ? $('#company').val() : ''), search = window.table.search();
        window.location = "{{ Request::url() }}/export?start_date=" + start_date + "&end_date=" + finish_date + "&coa_id=" + coa_id + "&company_id=" + company_id + "&search=" + search;
    }

    function reset(){
        $('#company').val($("#company option:first").val()).formSelect();
        $('#coa').empty();
        $('#start_date,#finish_date').val('{{ date("Y-m-d") }}');
        loadDataTable();
    }

    function loadDataTable() {
		window.table = $('#datatable_serverside').DataTable({
            "scrollCollapse": true,
            "scrollY": '400px',
            "responsive": false,
            "scrollX": true,
            /* "stateSave": true, */
            "serverSide": true,
            "deferRender": true,
            "destroy": true,
            "iDisplayInLength": 10,
            "order": [[1, 'asc']],
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    coa : $('#coa').val(),
                    company : $('#company').val(),
                    start_date : $('#start_date').val(),
                    finish_date : $('#finish_date').val(),
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
            columns: [
                { name: 'id', searchable: false, className: 'center-align details-control' },
                { name: 'coa', className: '' },
                { name: 'company', className: '' },
                { name: 'balance', searchable: false, orderable: false, className: 'right-align' },
                { name: 'debit', searchable: false, orderable: false, className: 'right-align' },
                { name: 'credit', searchable: false, orderable: false, className: 'right-align' },
                { name: 'final', searchable: false, orderable: false, className: 'right-align' },
            ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle',
                'selectNone' 
            ],
            select: {
                style: 'multi'
            },
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
                id: data,
                start_date: $('#start_date').val(),
                finish_date: $('#finish_date').val(),
            },
            success: function(response) {
                $('#modal2').modal('open');
                $('#data_detail').html(response);
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