<style>
    .progress {
		min-height: 20px;
		overflow: hidden;
		position: relative;
		span {
			position: relative;
			float:left;
			color: #fff;
			padding: 8px;
			z-index: 99999;
			i {
				width: inherit;
				font-size: inherit;
				position: relative;
				top: 2px;
				margin-left: 8px;
			}
		}
		.determinate {
			width: 0;
			transition: width 1s ease-in-out;
			padding: 1px;
			position: relative;
			color: #fff;
			text-align: right;
			white-space: nowrap;
		}
	}
    @keyframes grow {
        from {
            width: 0;
        }
    }
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
                        <div class="col s6 m6">
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">
                                        AR Down Payment
                                    </h4>
                                    <div class="row">
                                        <div class="col s12">
                                            <div id="datatable_buttons_downpayment"></div>
                                            <table id="datatable_serverside_downpayment">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('translations.code') }}</th>
                                                        <th>{{ __('translations.customer') }}</th>
                                                        <th>Payment</th>
                                                        <th>{{ __('translations.action') }}</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col s6 m6">
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">
                                        AR Invoice
                                    </h4>
                                    <div class="row">
                                        <div class="col s12">
                                            <div id="datatable_buttons_invoice"></div>
                                            <table id="datatable_serverside_invoice">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('translations.code') }}</th>
                                                        <th>{{ __('translations.customer') }}</th>
                                                        <th>Payment</th>
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
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="show_detail">

            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>

<script>
    var table_dp, table_inv;
    $(function() {
        loadDataTableDownpayment();
        loadDataTableInvoice();

        $('#modal1').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#show_detail').empty();
            }
        });
    });

    function loadDataTableDownpayment(){
        table_dp = $('#datatable_serverside_downpayment').DataTable({
            "scrollCollapse": true,
            "scrollY": '400px',
            "responsive": false,
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
            },
            ajax: {
                url: '{{ Request::url() }}/datatable_downpayment',
                type: 'GET',
                data: {

                },
                beforeSend: function() {
                    loadingOpen('#datatable_serverside_downpayment');
                },
                complete: function() {
                    loadingClose('#datatable_serverside_downpayment');
                },
                error: function() {
                    loadingClose('#datatable_serverside_downpayment');
                    swal({
                        title: 'Ups!',
                        text: 'Check your internet connection.',
                        icon: 'error'
                    });
                }
            },
            columns: [
                { name: 'code', className: '' },
                { name: 'account_id', className: '' },
                { name: 'payment', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ],
        });
        $('.dt-buttons').find("[aria-controls='datatable_serverside_downpayment']").appendTo('#datatable_buttons_downpayment');

        $('select[name="datatable_serverside_downpayment_length"]').addClass('browser-default');
    }

    function loadDataTableInvoice(){
        table_inv = $('#datatable_serverside_invoice').DataTable({
            "scrollCollapse": true,
            "scrollY": '400px',
            "responsive": false,
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
            },
            ajax: {
                url: '{{ Request::url() }}/datatable_invoice',
                type: 'GET',
                data: {

                },
                beforeSend: function() {
                    loadingOpen('#datatable_serverside_invoice');
                },
                complete: function() {
                    loadingClose('#datatable_serverside_invoice');
                },
                error: function() {
                    loadingClose('#datatable_serverside_invoice');
                    swal({
                        title: 'Ups!',
                        text: 'Check your internet connection.',
                        icon: 'error'
                    });
                }
            },
            columns: [
                { name: 'code', className: '' },
                { name: 'account_id', className: '' },
                { name: 'payment', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ],
        });
        $('.dt-buttons').find("[aria-controls='datatable_serverside_invoice']").appendTo('#datatable_buttons_invoice');

        $('select[name="datatable_serverside_invoice_length"]').addClass('browser-default');
    }

    function showDownpayment(code){
        getData(code,'dp');
    }

    function showInvoice(code){
        getData(code,'inv');
    }

    function getData(code,mode){
        $.ajax({
            url: '{{ Request::url() }}/show',
            type: 'POST',
            dataType: 'JSON',
            data: {
                code: code,
                mode: mode
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            success: function(response) {
                loadingClose('.modal-content');
                $('#modal1').modal('open');
                $('#show_detail').html(response);
                $('.modal-content').scrollTop(0);
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
</script>