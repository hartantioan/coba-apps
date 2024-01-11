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
                            <li class="breadcrumb-item active">{{ Str::title(str_replace('_',' ',Request::segment(3))) }}
                            </li>
                        </ol>
                    </div>
                    <div class="col s4 m6 l6">
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="printData();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">Print</span>
                            <i class="material-icons right">local_printshop</i>
                        </a>
                        
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="loadDataTable()">
                            <i class="material-icons hide-on-med-and-up">refresh</i>
                            <span class="hide-on-small-onl">Refresh</span>
                            <i class="material-icons right">refresh</i>
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
                                                <label for="filter_company" style="font-size:1rem;">Plant :</label>
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
                                                <label for="start_date" style="font-size:1rem;">Tanggal Mulai :</label>
                                                <div class="input-field col s12">
                                                <input type="date" id="start_date" name="start_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="finish_date" style="font-size:1rem;">Tanggal Akhir :</label>
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
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">Refresh</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <table id="datatable_serverside">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Code</th>
                                                        <th>Pengguna</th>
                                                        <th>Perusahaan</th>
                                                        <th>Tgl.Post</th>
                                                        <th>Keterangan</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Tambah/Edit {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="row">
                            <div class="input-field col m3 s12 step1">
                                <input type="hidden" id="temp" name="temp">
                                <select class="form-control" id="company_id" name="company_id">
                                    @foreach ($company as $rowcompany)
                                        <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="company_id">Perusahaan</label>
                            </div>
                            <div class="input-field col m3 s12 step2">
                                <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                <label class="active" for="post_date">Tgl. Posting</label>
                            </div>
                            <div class="input-field col m3 s12 step3">
                                <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note">Keterangan</label>
                            </div>
                            <div class="col m12 s12 step4">
                                <div class="col m6 s6">
                                    <p class="mt-2 mb-2">
                                        <h6>Fund Request / Permohonan Dana (Tipe BS)</h6>
                                        <div class="row">
                                            <div class="input-field col m8 s8">
                                                <select class="browser-default" id="fund_request_id" name="fund_request_id">&nbsp;</select>
                                            </div>
                                            <div class="col m4 s4 mt-4">
                                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="getFundRequest();" href="javascript:void(0);">
                                                    <i class="material-icons left">add</i> Tambah
                                                </a>
                                            </div>
                                        </div>
                                    </p>
                                </div>
                                <div class="col m6 s6">
                                    <b>FR Terpakai</b> (hapus untuk bisa diakses pengguna lain) : <i id="list-used-data"></i>
                                </div>
                            </div>
                            <div class="col m12 s12 step5">
                                <p class="mt-2 mb-2">
                                    <h6>Detail Fund Request / Permohonan Dana (Tipe BS)</h6>
                                    <div style="overflow:auto;">
                                        <table class="bordered" style="max-width:2200px !important;">
                                            <thead>
                                                <tr>
                                                    <th class="center">Referensi</th>
                                                    <th class="center">Partner Bisnis</th>
                                                    <th class="center">Tgl.Post</th>
                                                    <th class="center">Tgl.Req.Bayar</th>
                                                    <th class="center">Total</th>
                                                    <th class="center">Coa Debit</th>
                                                    <th class="center">Dist.Debit</th>
                                                    <th class="center">Coa Kredit</th>
                                                    <th class="center">Total</th>
                                                    <th class="center">PPN</th>
                                                    <th class="center">Termasuk PPN</th>
                                                    <th class="center">PPh</th>
                                                    <th class="center">Grandtotal</th>
                                                    <th class="center">Dibayarkan</th>
                                                    <th class="center">Sisa</th>
                                                    <th class="center">Keterangan</th>
                                                    <th class="center">Hapus</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-detail">
                                                <tr id="empty-detail">
                                                    <td colspan="17" class="center">
                                                        Pilih Fund Request / Permohonan Dana untuk memulai...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </p>
                            </div>
                            <div class="input-field col m4 s12">
                            </div>
                            <div class="input-field col m4 s12">
                            </div>
                            <div class="input-field col m4 s12 step6">
                                <table width="100%" class="bordered">
                                    <thead>
                                        <tr>
                                            <td>Total</td>
                                            <td class="right-align"><span id="total">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>PPN</td>
                                            <td class="right-align"><span id="tax">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>PPh</td>
                                            <td class="right-align"><span id="wtax">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>Grandtotal</td>
                                            <td class="right-align"><span id="grandtotal">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>Bayar</td>
                                            <td class="right-align"><span id="pay">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>Sisa</td>
                                            <td class="right-align"><span id="balance">0,00</span></td>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="col s12 mt-3 step7">
                                <button class="btn waves-effect waves-light right submit" onclick="save();">Simpan <i class="material-icons right">send</i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light purple " onclick="startIntro();">Panduan <i class="material-icons right">help_outline</i></button>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal2" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="show_print">
                
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal4_1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
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

<div id="modal4" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="show_structure">
                <div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;">

                </div>
                <div id="visualisation">
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal6" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
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
        <div class="divider mb-1 mt-2"></div>
        <div class="row">
            <div class="col" id="user_jurnal">
            </div>
            <div class="col" id="post_date_jurnal">
            </div>
            <div class="col" id="note_jurnal">
            </div>
            <div class="col" id="ref_jurnal">
            </div>
            <div class="col" id="company_jurnal">
            </div>
        </div>
        <div class="row mt-2">
            <table class="bordered Highlight striped">
                <thead>
                        <tr>
                            <th class="center-align">No</th>
                            <th class="center-align">Coa</th>
                            <th class="center-align">Partner Bisnis</th>
                            <th class="center-align">Plant</th>
                            <th class="center-align">Line</th>
                            <th class="center-align">Mesin</th>
                            <th class="center-align">Department</th>
                            <th class="center-align">Gudang</th>
                            <th class="center-align">Proyek</th>
                            <th class="center-align">Keterangan</th>
                            <th class="center-align">Debit</th>
                            <th class="center-align">Kredit</th>
                        </tr>
                    
                </thead>
                <tbody id="body-journal-table">
                </tbody>
            </table>
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

<!-- END: Page Main-->
<script>
    $(function() {
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        loadDataTable();

        window.table.search('{{ $code }}').draw();

        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                $('#post_date').attr('min','{{ $minDate }}');
                $('#post_date').attr('max','{{ $maxDate }}');
                $('#due_date').attr('min','{{ date("Y-m-d") }}');
                $('#document_date').attr('min','{{ date("Y-m-d") }}');
            },
            onOpenEnd: function(modal, trigger) {
                $('#name').focus();
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
                window.onbeforeunload = function() {
                    if($('.data-used').length > 0){
                        $('.data-used').trigger('click');
                    }
                    return 'You will lose all changes made since your last save';
                };
            },
            onCloseStart: function(modal, trigger){
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                $('input').css('border', 'none');
                $('input').css('border-bottom', '0.5px solid black');
                M.updateTextFields();
                $('#body-detail').empty().append(`
                    <tr id="empty-detail">
                        <td colspan="10" class="center">
                            Pilih Fund Request / Permohonan Dana untuk memulai...
                        </td>
                    </tr>
                `);
                window.onbeforeunload = function() {
                    return null;
                };
            }
        });

        $('#modal2').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) {
                window.print();
            },
            onCloseEnd: function(modal, trigger){
                $('#show_print').html('');
            }
        });

        $('#modal4_1').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#show_detail').empty();
            }
        });

        $('#modal6').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#title_data').empty();
                $('#code_data').empty();             
                $('#body-journal-table').empty();
                $('#user_jurnal').empty();
                $('#note_jurnal').empty();
                $('#ref_jurnal').empty();
                $('#company_jurnal').empty();
                $('#post_date_jurnal').empty();
            }
        });

        $('#modal4').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#myDiagramDiv').remove();
                $('#show_structure').append(
                    `<div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;"></div>
                    `
                );
            }
        });

        $('#body-detail').on('click', '.delete-data-detail', function() {
            $(this).closest('tr').remove();
            countAll();
            if($('.row_detail').length == 0){
                $('#body-detail').append(`
                    <tr id="empty-detail">
                        <td colspan="17" class="center">
                            Pilih Fund Request / Permohonan Dana untuk memulai...
                        </td>
                    </tr>
                `);
            }
        });

        select2ServerSide('#fund_request_id', '{{ url("admin/select2/fund_request_bs_close") }}');
    });

    function voidStatus(id){
        var msg = '';
        swal({
            title: "Alasan mengapa anda menutup!",
            text: "Anda tidak bisa mengembalikan data yang telah ditutup.",
            buttons: true,
            content: "input",
        })
        .then(message => {
            if (message != "" && message != null) {
                $.ajax({
                    url: '{{ Request::url() }}/void_status',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { id : id, msg : message },
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
                    'account_id[]' : $('#filter_account').val(),
                    company_id : $('#filter_company').val(),
                    'currency_id[]' : $('#filter_currency').val(),
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
                { name: 'company_id', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'note', className: 'center-align' },
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
                $('#modal4_1').modal('open');
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

    function getFundRequest(){
        if($('#fund_request_id').val()){
            $.ajax({
                url: '{{ Request::url() }}/get_fund_request',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: $('#fund_request_id').val()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    loadingClose('.modal-content');
                    if(response.status == 500){
                        swal({
                            title: 'Ups!',
                            text: response.message,
                            icon: 'warning'
                        });
                        $('#fund_request_id').empty();
                    }else{
                        $('#empty-detail').remove();

                        if(!$('.data-used[data-code="' + response.rawcode + '"]').length > 0){
                            $('#list-used-data').append(`
                                <div class="chip purple darken-4 gradient-shadow white-text">
                                    ` + response.rawcode + `
                                    <i class="material-icons close data-used" data-code="` + response.rawcode + `"" onclick="removeUsedData('` + response.id + `')">close</i>
                                </div>
                            `);
                        }

                        var count = makeid(10);

                        $('#body-detail').append(`
                            <tr class="row_detail" data-fr="` + response.id + `">
                                <input type="hidden" name="arr_fund_request[]" value="` + response.code + `">
                                <td>
                                    ` + response.rawcode + `
                                </td>
                                <td class="center">
                                    ` + response.bp_name + `
                                </td>
                                <td class="center">
                                    ` + response.post_date + `
                                </td>
                                <td class="center">
                                    ` + response.required_date + `
                                </td>
                                <td class="center">
                                    ` + response.grandtotal + `
                                </td>
                                <td class="center">
                                    <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]" required style="width: 100%"></select>
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_cost_distribution` + count + `" name="arr_cost_distribution[]" style="width:150px;">
                                        <option value="">--Kosong--</option>
                                        @foreach ($distribution as $row)
                                            <option value="{{ $row->id }}">{{ $row->code.' - '.$row->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="center">
                                    ` + response.coa_list + `
                                </td>
                                <td class="center">
                                    <input id="arr_total` + count + `" name="arr_total[]" class="browser-default" type="text" value="` + response.balance + `" onkeyup="cekRow(this);formatRupiah(this);countAll();" data-limit="` + response.balance + `" style="width:150px;text-align:right;">
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_tax` + count + `" name="arr_tax[]" onchange="countAll();" style="width:150px;">
                                        <option value="0" data-id="0">-- Pilih ini jika non-PPN --</option>
                                        @foreach ($tax as $row)
                                            <option value="{{ $row->percentage }}" {{ $row->is_default_ppn ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_include_tax` + count + `" name="arr_include_tax[]" onchange="countAll();" style="width:150px;">
                                        <option value="0">Tidak</option>
                                        <option value="1">Ya</option>
                                    </select>
                                </td>
                                <td>
                                    <select class="browser-default" id="arr_wtax` + count + `" name="arr_wtax[]" onchange="countAll();" style="width:150px;">
                                        <option value="0" data-id="0">-- Pilih ini jika non-PPh --</option>
                                        @foreach ($wtax as $row)
                                            <option value="{{ $row->percentage }}" {{ $row->is_default_pph ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name.' - '.number_format($row->percentage,2,',','.').'%' }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="center">
                                    <input id="arr_grandtotal` + count + `" name="arr_grandtotal[]" class="browser-default" type="text" value="` + response.balance + `" onkeyup="formatRupiah(this);" style="width:150px;text-align:right;" readonly>
                                </td>
                                <td class="center">
                                    <input id="arr_nominal` + count + `" name="arr_nominal[]" class="browser-default" type="text" value="` + response.balance + `" onkeyup="formatRupiah(this);" style="width:150px;text-align:right;" readonly>
                                </td>
                                <td class="center">
                                    <input id="arr_balance` + count + `" name="arr_balance[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);" style="width:150px;text-align:right;" readonly>
                                </td>
                                <td>
                                    <input name="arr_note[]" class="browser-default" type="text" placeholder="Keterangan..." value=" - " style="width:100%;">
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                        select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
                        $('#fund_request_id').empty();
                        $('.modal-content').scrollTop(0);
                        M.updateTextFields();
                        countAll();
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
            });
        }else{

        }
    }

    function countAll(){
        var total = 0, tax = 0, grandtotal = 0, balance = 0, wtax = 0, pay = 0;
        
        $('select[name^="arr_coa"]').each(function(index){
            var rowgrandtotal = 0, rowtotal = parseFloat($('input[name^="arr_total"]').eq(index).val().replaceAll(".", "").replaceAll(",",".")), rowtax = 0, rowwtax = 0, percent_tax = parseFloat($('select[name^="arr_tax"]').eq(index).val()), percent_wtax = parseFloat($('select[name^="arr_wtax"]').eq(index).val()), rowpay = parseFloat($('input[name^="arr_nominal"]').eq(index).val().replaceAll(".", "").replaceAll(",",".")), rowbalance = 0;
            if(percent_tax > 0 && $('select[name^="arr_include_tax"]').eq(index).val() == '1'){
                rowtotal = rowtotal / (1 + (percent_tax / 100));
            }
            rowtax = rowtotal * (percent_tax / 100);
            rowwtax = rowtotal * (percent_wtax / 100);
            total += rowtotal;
            tax += rowtax;
            wtax += rowwtax;
            rowgrandtotal = rowtotal + rowtax - rowwtax;
            grandtotal += rowgrandtotal;
            rowbalance = rowgrandtotal - rowpay;
            pay += rowpay;
            balance += rowbalance;
            
            $('input[name^="arr_grandtotal"]').eq(index).val(
                (rowgrandtotal >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(rowgrandtotal).toString().replace('.',','))
            );
            $('input[name^="arr_balance"]').eq(index).val(
                (rowbalance >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(rowbalance).toString().replace('.',','))
            );
        });
        
        $('#total').text(
            (total >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(total).toString().replace('.',','))
        );
        $('#tax').text(
            (tax >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(tax).toString().replace('.',','))
        );
        $('#wtax').text(
            (wtax >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(wtax).toString().replace('.',','))
        );
        $('#grandtotal').text(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(grandtotal).toString().replace('.',','))
        );
        $('#pay').text(
            (pay >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(pay).toString().replace('.',','))
        );
        $('#balance').text(
            (balance >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(balance).toString().replace('.',','))
        );
    }

    function cekRow(element){
        /* if($(element).val()){
            let val = parseFloat($(element).val().replaceAll(".", "").replaceAll(",",".")), limit = parseFloat($(element).data('limit').replaceAll(".", "").replaceAll(",","."));
            if(val > limit){
                $(element).val(
                    (limit >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(limit).toString().replace('.',','))
                );
            }
        } */
    }

    function removeUsedData(id){
        $.ajax({
            url: '{{ Request::url() }}/remove_used_data',
            type: 'POST',
            dataType: 'JSON',
            data: { 
                id : id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                
            },
            success: function(response) {
                $('.row_detail[data-fr="' + id + '"]').remove();
                if($('.row_detail').length == 0 && $('#empty-detail').length == 0){
                    $('#body-detail').append(`
                        <tr id="empty-detail">
                            <td colspan="10" class="center">
                                Pilih Fund Request / Permohonan Dana untuk memulai...
                            </td>
                        </tr>
                    `);
                }
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

    function printPreview(code,aslicode){
        swal({
            title: "Apakah Anda ingin mengeprint dokumen ini?",
            text: "Dengan Kode "+aslicode,
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                $.ajax({
                    url: '{{ Request::url() }}/print_individual/' + code,
                    type:'GET',
                    beforeSend: function() {
                        loadingOpen('.modal-content');
                    },
                    complete: function() {
                        
                    },
                    success: function(data){
                        loadingClose('.modal-content');
                        printService.submit({
                            'type': 'INVOICE',
                            'url': data
                        })
                    }
                });  
            }
        });
        
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

                if(response.status == 500){
                    swal({
                        title: 'Ups!',
                        text: response.message,
                        icon: 'warning'
                    });
                    $('#payment_request_id').empty();
                }else{
                    $('#modal1').modal('open');
                    $('#temp').val(id);
                    $('#company_id').val(response.company_id).formSelect();
                    $('#post_date').val(response.post_date);
                    $('#note').val(response.note);

                    if(response.details.length > 0){
                        $('.row_detail').each(function(){
                            $(this).remove();
                        });
                        $('#empty-detail').remove();

                        $.each(response.details, function(i, val) {
                            var count = makeid(10);

                            $('#body-detail').append(`
                                <tr class="row_detail" data-fr="` + val.id + `">
                                    <input type="hidden" name="arr_fund_request[]" value="` + val.code + `">
                                    <td>
                                        ` + val.rawcode + `
                                    </td>
                                    <td class="center">
                                        ` + val.bp_name + `
                                    </td>
                                    <td class="center">
                                        ` + val.post_date + `
                                    </td>
                                    <td class="center">
                                        ` + val.required_date + `
                                    </td>
                                    <td class="center">
                                        ` + val.grandtotal + `
                                    </td>
                                    <td class="center">
                                        <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]" required style="width: 100%"></select>
                                    </td>
                                    <td class="center">
                                        ` + val.coa_list + `
                                    </td>
                                    <td class="center">
                                            <input id="arr_nominal` + count + `" name="arr_nominal[]" class="browser-default" type="text" value="` + val.balance + `" onkeyup="cekRow(this);formatRupiah(this);" style="width:150px;text-align:right;">
                                        </td>
                                    <td>
                                        <input name="arr_note[]" class="browser-default" type="text" placeholder="Keterangan..." value=" - " style="width:100%;">
                                    </td>
                                    <td class="center">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                </tr>
                            `);
                            $('#arr_coa' + count).append(`
                                <option value="` + val.coa_id + `">` + val.coa_name + `</option>
                            `);
                            select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
                        });
                    }

                    $('.modal-content').scrollTop(0);
                    $('#note').focus();
                    M.updateTextFields();
                }
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

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
    }

    function save(){
		swal({
            title: "Apakah anda yakin ingin simpan?",
            text: "Silahkan cek kembali form, dan jika sudah yakin maka lanjutkan!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                var formData = new FormData($('#form_data')[0]);

                $.ajax({
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
                        $('input').css('border', 'none');
                        $('input').css('border-bottom', '0.5px solid black');
                        if(response.status == 200) {
                            success();
                            M.toast({
                                html: response.message
                            });
                        } else if(response.status == 422) {
                            $('#validation_alert').show();
                            $('.modal-content').scrollTop(0);
                            $.each(response.error, function(field, errorMessage) {
                                $('#' + field).addClass('error-input');
                                $('#' + field).css('border', '1px solid red');
                                
                            });
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
                });
            }
        });
    }

    function printData(){
        var search = window.table.search(), status = $('#filter_status').val(), company = $('#filter_company').val(), start_date = $('#start_date').val(), finish_date = $('#finish_date').val();
        
        $.ajax({
            type : "POST",
            url  : '{{ Request::url() }}/print',
            data : {
                search : search,
                status : status,
                company : company,
                start_date : start_date,
                finish_date : finish_date,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            cache: false,
            success: function(data){
                var w = window.open('about:blank');
                w.document.open();
                w.document.write(data);
                w.document.close();
            }
        });
    }

    function makeTreeOrg(data,link){
        var $ = go.GraphObject.make;

        myDiagram =
        $(go.Diagram, "myDiagramDiv",
        {
            initialContentAlignment: go.Spot.Center,
            "undoManager.isEnabled": true,
            layout: $(go.TreeLayout,
            { 
                angle: 180,
                path: go.TreeLayout.PathSource,  
                setsPortSpot: false, 
                setsChildPortSpot: false,  
                arrangement: go.TreeLayout.ArrangementHorizontal
            })
        });
        $("PanelExpanderButton", "METHODS",
            { row: 2, column: 1, alignment: go.Spot.TopRight },
            {
                visible: true,
                click: function(e, obj) {
                    var node = obj.part.parent;
                    var diagram = node.diagram;
                    var data = node.data;
                    diagram.startTransaction("Collapse/Expand Methods");
                    diagram.model.setDataProperty(data, "isTreeExpanded", !data.isTreeExpanded);
                    diagram.commitTransaction("Collapse/Expand Methods");
                }
            },
            new go.Binding("visible", "methods", function(arr) { return arr.length > 0; })
        );
        myDiagram.addDiagramListener("ObjectDoubleClicked", function(e) {
            var part = e.subject.part;
            if (part instanceof go.Link) {
                
              
            } else if (part instanceof go.Node) {
                window.open(part.data.url);
                if (part.isTreeExpanded) {
                    part.collapseTree();
                } else {
                    part.expandTree();
                }
              
            }
        });
        myDiagram.nodeTemplate =
        $(go.Node, "Auto",
            {
            locationSpot: go.Spot.Center,
            fromSpot: go.Spot.AllSides,
            toSpot: go.Spot.AllSides,
            portId: "",  

            },
            { isTreeExpanded: false },  
            $(go.Shape, { fill: "lightgrey", strokeWidth: 0 },
            new go.Binding("fill", "color")),
            $(go.Panel, "Table",
            { defaultRowSeparatorStroke: "black" },
            $(go.TextBlock,
                {
                row: 0, columnSpan: 2, margin: 3, alignment: go.Spot.Center,
                font: "bold 12pt sans-serif",
                isMultiline: false, editable: true
                },
                new go.Binding("text", "name").makeTwoWay()
            ),
            $(go.TextBlock, "Properties",
                { row: 1, font: "italic 10pt sans-serif" },
                new go.Binding("visible", "visible", function(v) { return !v; }).ofObject("PROPERTIES")
            ),
            $(go.Panel, "Vertical", { name: "PROPERTIES" },
                new go.Binding("itemArray", "properties"),
                {
                row: 1, margin: 3, stretch: go.GraphObject.Fill,
                defaultAlignment: go.Spot.Left,
                }
            ),
            
            $(go.Panel, "Auto",
                { portId: "r" },
                { margin: 6 },
                $(go.Shape, "Circle", { fill: "transparent", stroke: null, desiredSize: new go.Size(8, 8) })
            ),
            ),

            $("TreeExpanderButton",
            { alignment: go.Spot.Right, alignmentFocus: go.Spot.Right, width: 14, height: 14 }
            )
        );
        myDiagram.model.root = data[0].key;
        

        myDiagram.addDiagramListener("InitialLayoutCompleted", function(e) {
        setTimeout(function() {
        
            var rootKey = data[0].key; 
            var rootNode = myDiagram.findNodeForKey(rootKey);
            if (rootNode !== null) {
                rootNode.collapseTree();
            }
        }, 100); 
        });

        myDiagram.layout = $(go.TreeLayout);

        myDiagram.addDiagramListener("InitialLayoutCompleted", e => {
           e.diagram.findTreeRoots().each(r => r.expandTree(3));
            e.diagram.nodes.each(node => {
                node.findTreeChildrenNodes().each(child => child.expandTree(10));
            });
        });

        myDiagram.model = $(go.GraphLinksModel,
        {
            copiesArrays: true,
            copiesArrayObjects: true,
            nodeDataArray: data,
            linkDataArray: link
        });
            
            
    }

    function viewStructureTree(id){
        $.ajax({
            url: '{{ Request::url() }}/viewstructuretree',
            type: 'GET',
            dataType: 'JSON',
            data: { 
                id : id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            success: function(response) {
                loadingClose('.modal-content');
            
                makeTreeOrg(response.message,response.link);
                
                $('#modal4').modal('open');
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

    function viewJournal(id){
        $.ajax({
            url: '{{ Request::url() }}/view_journal/' + id,
            type:'GET',
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
                    $('#code_data').append(data.message.code);
                    $('#body-journal-table').append(data.tbody);
                    $('#user_jurnal').append(`Pengguna : `+data.user);
                    $('#note_jurnal').append(`Keterangan : `+data.message.note);
                    $('#ref_jurnal').append(`Referensi : `+data.reference);
                    $('#company_jurnal').append(`Perusahaan : `+data.company);
                    $('#post_date_jurnal').append(`Tanggal : `+data.message.post_date);
                }
            }
        });
    }

    function whatPrinting(code){
        $.ajax({
            url: '{{ Request::url() }}/print_individual/' + code,
            type:'GET',
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            complete: function() {
                
            },
            success: function(data){
                loadingClose('.modal-content');
                window.open(data, '_blank');
            }
        });
    }
</script>