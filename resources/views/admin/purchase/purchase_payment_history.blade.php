<style>
    .modal {
        top:0px !important;
    }

    table > thead > tr > th {
        font-size: 13px !important;
    }

    table.bordered th {
        padding: 5px !important;
    }

    .browser-default {
        height: 2rem !important;
    }
    #modal5{
        position: fixed;
        top: 50% !important;
        left: 50% !important;
        /* bring your own prefixes */
        transform: translate(-50%, -50%) !important;
    }
    
    @media (min-width: 960px) {
        #modal4 {
            width:60%;
        }
    }

    @media (max-width: 960px) {
        #modal4 {
            width:100%;
        }
    }
</style>
<link rel="stylesheet" type="text/css" href="{{ url('app-assets/css/pages/page-timeline.css') }}">
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
                            <li class="breadcrumb-item active">{{ Str::title(str_replace('_',' ',Request::segment(3))) }}
                            </li>
                        </ol>
                    </div>
                    <div class="col s4 m6 l6">
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                            <i class="material-icons hide-on-med-and-up">refresh</i>
                            <span class="hide-on-small-onl">Refresh</span>
                            <i class="material-icons right">refresh</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="printData();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">Print</span>
                            <i class="material-icons right">local_printshop</i>
                        </a>
                        
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
                                            <div class="col m4 s6 ">
                                                <label for="filter_status" style="font-size:1rem;">Status :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_status" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        <option value="1">Menunggu</option>
                                                        <option value="2">Dalam Proses</option>
                                                        <option value="3">Selesai</option>
                                                        <option value="4">Ditolak</option>
                                                        <option value="5">Ditutup</option>
                                                        <option value="6">Direvisi</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_type" style="font-size:1rem;">Tipe :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_type" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        <option value="1">Cash</option>
                                                        <option value="2">Credit</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_company" style="font-size:1rem;">Perusahaan :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_company" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        @foreach ($company as $rowcompany)
                                                            <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_account" style="font-size:1rem;">Supplier/Vendor :</label>
                                                <div class="input-field">
                                                    <select class="browser-default" id="filter_account" name="filter_account" multiple="multiple" style="width:100% !important;" onchange="loadDataTable()"></select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="start_date" style="font-size:1rem;">Start Date (Tanggal Mulai) :</label>
                                                <div class="input-field col s12">
                                                <input type="date" id="start_date" name="start_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="finish_date" style="font-size:1rem;">End Date (Tanggal Berhenti) :</label>
                                                <div class="input-field col s12">
                                                    <input type="date" id="finish_date" name="finish_date"  onchange="loadDataTable()">
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
                                            <table id="datatable_serverside">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="2">#</th>
                                                        <th rowspan="2">Code</th>
                                                        <th rowspan="2">Pengguna</th>
                                                        <th rowspan="2">Sup/Ven</th>
                                                        <th rowspan="2">Perusahaan</th>
                                                        <th colspan="4" class="center-align">Tanggal</th>
                                                        <th rowspan="2">Tipe</th>
                                                        <th rowspan="2">Dokumen</th>
                                                        <th rowspan="2">Keterangan</th>
                                                        <th rowspan="2">No.Faktur Pajak</th>
                                                        <th rowspan="2">No.Bukti Potong</th>
                                                        <th rowspan="2">Tgl.Bukti Potong</th>
                                                        <th rowspan="2">No.SPK</th>
                                                        <th rowspan="2">No.Invoice</th>
                                                        <th rowspan="2">Subtotal</th>
                                                        <th colspan="2" class="center-align">Diskon</th>
                                                        <th rowspan="2">Total</th>
                                                        <th rowspan="2">PPN</th>
                                                        <th rowspan="2">PPh</th>
                                                        <th rowspan="2">Grandtotal</th>
                                                        <th rowspan="2">Downpayment</th>
                                                        <th rowspan="2">Balance</th>
                                                        <th rowspan="2">Status</th>
                                                        <th rowspan="2">Action</th>
                                                    </tr>
                                                    <tr>
                                                        <th>Post</th>
                                                        <th>Terima</th>
                                                        <th>Tenggat</th>
                                                        <th>Dokumen</th>
                                                        <th>Prosentase</th>
                                                        <th>Nominal</th>
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
<div id="modal6" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;width:100%;top:0 !important;">
    <div class="modal-content">
        <div class="row" >
            <div class="col m3 s12">
                
            </div>
            <div class="col m6 s12">
                <h4 id="title_data" style="text-align:center"></h4>
                <h5 id="code_data" style="text-align:center"></h5>
            </div>
            <div class="col m3 s12 right-align">
                <img src="{{ url('website/logo_web_fix.png') }}" width="40%" height="60%">
            </div>
        </div>
        <div class="row">
        <div class="col s12 m6 l6 xl3">
            <div class="card gradient-45deg-blue-grey-blue gradient-shadow min-height-100 white-text animate fadeLeft">
                <div class="padding-4">
                    <div class="row">
                        <div class="col">
                            <h6 style="color: white">GrandTotal</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col s12 m12 l12 xl12  right-align">
                            <p style="font-weight: bold;font-size: large;" id="grandtotal"></p>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12 m6 l6 xl3">
            <div class="card gradient-45deg-blue-indigo gradient-shadow min-height-100 white-text animate fadeLeft">
                <div class="padding-4">
                    <div class="row">
                        <div class="col">
                            <h6 style="color: white">Downpayment</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col s12 m12 l12 xl12  right-align">
                            <p style="font-weight: bold;font-size: large;" id="downpayment"></p>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12 m6 l6 xl3">
            <div class="card gradient-45deg-indigo-blue gradient-shadow min-height-100 white-text animate fadeRight">
                <div class="padding-4">
                    <div class="row">
                        <div class="col">
                            <h6 style="color: white">Tagihan</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col s12 m12 l12 xl12  right-align">
                            <p style="font-weight: bold;font-size: large;" id="tagihan"></p>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12 m6 l6 xl3">
            <div class="card gradient-45deg-indigo-purple gradient-shadow min-height-100 white-text animate fadeRight">
                <div class="padding-4">
                    <div class="row">
                        <div class="col">
                            <h6 style="color: white">Memo</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col s12 m12 l12 xl12  right-align">
                            <p style="font-weight: bold;font-size: large;" id="memo"></p>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12 m6 l6 xl3">
            <div class="card gradient-45deg-deep-purple-blue gradient-shadow min-height-100 white-text animate fadeRight">
                <div class="padding-4">
                    <div class="row">
                        <div class="col">
                            <h6 style="color: white" >Dibayarkan</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col s12 m12 l12 xl12  right-align">
                            <p style="font-weight: bold;font-size: large;" id="dibayarkan"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12 m6 l6 xl3">
            <div class="card gradient-45deg-indigo-light-blue gradient-shadow min-height-100 white-text animate fadeRight">
                <div class="padding-4">
                    <div class="row">
                        <div class="col">
                            <h6 style="color: white">Kurang Bayar</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col s12 m12 l12 xl12  right-align">
                            <p style="font-weight: bold;font-size: large;" id="kurangbayar"></p>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        </div>
        <ul class="tabs">
            <li class="tab">
                <a href="#history" class="" id="part-tabs-btn">
                    <span>User History</span>
                </a>
            </li>
            <li class="indicator" style="left: 0px; right: 0px;"></li>
        </ul>
        <div class="row mt-2">
            
            <div id="history" style="display: block;" class="">
                <ul class="">
                    <li class="tab">
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" id="table-btn">
                        <span>Tabel</span>
                        </a>
                    </li>
                    <li class="tab">
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" id="timeline-btn" style="margin-bottom: 2rem;">
                        <span>Time Line</span>
                        </a>
                    </li>
                    <li class="indicator" style="left: 0px; right: 0px;"></li>
                </ul>
                <ul class="timeline" id="body_history" style="padding-left: 4rem; padding-right:4rem;">
                    
                    
                </ul>
                <table class="bordered Highlight striped" id="table_history">
                    <thead>
                            <tr>
                                <th class="center-align">No</th>
                                <th class="center-align">Code Outgoing Payment</th>
                                <th class="center-align">Date</th>
                                <th class="center-align">Code Payment Request</th>
                                <th class="center-align">Nominal</th>
                            </tr>                  
                    </thead>
                    <tbody id="body_history_table">
                    </tbody>
                </table>
                
            </div> 
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div>
<div style="bottom: 50px; right: 80px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-amber-amber gradient-shadow modal-trigger tooltipped"  data-position="top" data-tooltip="Range Printing" href="#modal5">
        <i class="material-icons">view_comfy</i>
    </a>
</div>
<!-- END: Page Main-->
<script>
    var table_multi, table_multi_dp;
    $(function() {
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
            
        });

        loadDataTable();

        window.table.search('{{ $code }}').draw();

        $('#modal6').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#title_data').empty();
                $('#body_history_table').empty();
                $('#body_history').empty();
                $('#grandtotal').empty();
                $('#downpayment').empty();
                $('#tagihan').empty();
                $('#memo').empty();
                $('#dibayarkan').empty();
                $('#kurangbayar').empty();
            }
        });

        $('#body-multi').on('click', '.delete-data-multi', function() {
            $(this).closest('tr').remove();
            countAllMulti();
        });

        select2ServerSide('#account_id,#filter_account', '{{ url("admin/select2/supplier_vendor") }}');
    });

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
            "order": [[0, 'asc']],
            dom: 'Blfrtip',
            buttons: [
                'selectNone'
            ],
            "language": {
                "lengthMenu": "Menampilkan _MENU_ data per halaman",
                "zeroRecords": "Data tidak ditemukan / kosong",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
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
            select: {
                style: 'multi',
                selector: 'td:not(.btn-floating)'
            },
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    status : $('#filter_status').val(),
                    type : $('#filter_type').val(),
                    'account_id[]' : $('#filter_account').val(),
                    company_id : $('#filter_company').val(),
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
            columns: [
                { name: 'id', searchable: false, className: 'center-align details-control' },
                { name: 'code', className: 'center-align' },
                { name: 'user_id', className: 'center-align' },
                { name: 'account_id', className: 'center-align' },
                { name: 'company_id', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'received_date', className: 'center-align' },
                { name: 'due_date', className: 'center-align' },
                { name: 'document_date', className: 'center-align' },
                { name: 'type', className: 'center-align' },
                { name: 'document', searchable: false, orderable: false, className: 'center-align' },
                { name: 'note', className: 'center-align' },
                { name: 'tax_no', className: 'center-align' },
                { name: 'tax_cut_no', className: 'center-align' },
                { name: 'cut_date', className: 'center-align' },
                { name: 'spk_no', className: 'center-align' },
                { name: 'invoice_no', className: 'center-align' },
                { name: 'subtotal', className: 'right-align' },
                { name: 'percent_discount', className: 'right-align' },
                { name: 'nominal_discount', className: 'right-align' },
                { name: 'total', className: 'right-align' },
                { name: 'tax', className: 'right-align' },
                { name: 'wtax', className: 'right-align' },
                { name: 'grandtotal', className: 'right-align' },
                { name: 'downpayment', className: 'right-align' },
                { name: 'balance', className: 'right-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle' 
            ]
        });
        $('.dt-buttons').appendTo('#datatable_buttons');
        
        $('select[name="datatable_serverside_length"]').addClass('browser-default');
	}

    function viewHistory(code){
        $('#table_history').show();
        $('#body_history').hide();

        $('#table-btn').click(function() {
            $('#table_history').show();
            $('#body_history').hide();
        });

        $('#timeline-btn').click(function() {
            $('#table_history').hide();
            $('#body_history').show();
        });
        $.ajax({
            url: '{{ Request::url() }}/view_history_payment',
            type: 'POST',
            dataType: 'JSON',
            data: {
                code: code
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            complete: function() {
                
            },
            success: function(data){
                loadingClose('.modal-content');
                if(data.status == '500'){
                    M.toast({
                        html: data.message
                    });
                }else{
                    $('#modal6').modal('open');
                    $('#title_data').append(``+data.title+``);
                    $('#body_history').append(data.tbody);
                    $('#grandtotal').append(data.grandtotal);
                    $('#downpayment').append(data.downpayment)
                    $('#tagihan').append(data.tagihan);
                    $('#memo').append(data.memo);
                    $('#dibayarkan').append(data.dibayar);
                    $('#kurangbayar').append(data.kurangbayar);
                    $('#body_history_table').append(data.tbody1);
                    $(".tooltipped").tooltip({
                        delay: 50
                    });
                }
            }
        });
    }

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
    }

    
</script>