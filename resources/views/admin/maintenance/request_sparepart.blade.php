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
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="printData();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">Print</span>
                            <i class="material-icons right">local_printshop</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="exportExcel();">
                            <i class="material-icons hide-on-med-and-up">view_list</i>
                            <span class="hide-on-small-onl">Excel</span>
                            <i class="material-icons right">view_list</i>
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
                                                    </select>
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
                                        <button class="btn waves-effect waves-light mr-1 float-right btn-small" onclick="loadDataTable()">
                                            Refresh
                                            <i class="material-icons left">refresh</i>
                                        </button>
                                    </h4>
                                    <div class="row mt-2">
                                        <div class="col s12">
                                            <div id="datatable_buttons"></div>
                                            <table id="datatable_serverside" class="display responsive-table wrap">
                                                <thead>
                                                    <tr>
                                                        <th >#</th>
                                                        <th >Kode Req. Sparepart</th>
                                                        <th >Request By</th>
                                                        <th >WO Code</th>
                                                        <th>Area</th>
                                                        <th >Request Date</th>
                                                        <th >Summary Issue</th>
                                                        <th  class="center-align">Status</th>
                                                        <th >Operasi</th>
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
                            <div class="input-field col m4 s12">
                                <input type="hidden" id="temp" name="temp">
                                <select class="browser-default" id="work_order_id" name="work_order_id" onchange="getWO_info()">&nbsp;</select>
                                <label class="active" for="work_order_id">Work Order</label>
                            </div>
                            <div class="input-field col m5 s12">
                                
                            </div>
                            <div class="input-field col m3 s12">
                                <input type="hidden" id="user_id" name="user_id" value="">
                                <input type="text" placeholder="Nama Peng-request" id="user_name" value="" readonly>
                                <label class="active" for="request_by">Requester WO</label>
                            </div>
                            <div class="input-field col m4 s12">
                                <input id="equipment_id" name="equipment_id"  type="text" placeholder="Equipment Name" readonly>
                                <label class="active" for="suggested_completion_date">Equipment</label>
                            </div>
                            <div class="input-field col m4 s12">
                                <input id="request_date" name="request_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. dokumen" value="{{ date('Y-m-d') }}">
                                <label class="active" for="request_date">Tgl. Request</label>
                            </div>
                            <div class="input-field col m6 s12">
                                <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note">Detail Issue</label>
                            </div>
                            <div class="col m12 s12">
                                <div style="overflow:auto;">
                                    <table class="bordered" style="max-width:1650px !important;">
                                        <thead>
                                            <tr>
                                                <th class="center" colspan="1">Nama Part</th>
                                                <th class="center" colspan="7">Informasi Part</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-detail">
                                            <tr id="empty-detail">
                                                <td colspan="10" class="center">
                                                   Pilih WO untuk menampilkan Equipment part...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col s12 mt-3">
                                <button class="btn waves-effect waves-light right submit" onclick="save();">Simpan <i class="material-icons right">send</i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal2" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;width:100%;">
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

<div id="modal3" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;width:100%;">
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


<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div>


<script>
    $(function() {
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        $('#datatable_serverside').on('click', 'td.details-control', function() {
            var tr    = $(this).closest('tr');
            var badge = tr.find('button.btn-floating');
            var icon  = tr.find('i');
            var row   = table.row(tr);

            if(row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
                badge.first().removeClass('red');
                badge.first().addClass('green');
                icon.first().html('add');
            } else {
                row.child(rowDetail(row.data())).show();
                tr.addClass('shown');
                badge.first().removeClass('green');
                badge.first().addClass('red');
                icon.first().html('remove');
            }
        });
        
        loadDataTable();

        window.table.search('{{ $code }}').draw();

        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
              
            },
            onOpenEnd: function(modal, trigger) {
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
            
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                M.updateTextFields();
                $('#body-detail').empty();
                $("#work_order_id").val("");
                $('.row_detail').each(function(){
                    $(this).remove();
                });
                
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
        $('#modal3').modal({
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

        select2ServerSide('#work_order_id', '{{ url("admin/select2/workorder") }}');
       
        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
            
            if($('.row_item').length == 0){
                $('#body-item').append(`
                    <tr id="empty-item">
                        <td colspan="8" class="center">
                            Pilih purchase order untuk memulai...
                        </td>
                    </tr>
                `);
                $('#purchase_order_id').empty();
            }
        });
    });

    function getWO_info(){
        if($('#work_order_id').val()){
            $.ajax({
                url: '{{ Request::url() }}/get_work_order_info',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: $('#work_order_id').val()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    loadingClose('.modal-content');
                    $('#user_id').empty();
                    $('#user_name').empty();

                    if(response.user_name){
                        $('#user_name').val(response.user_name);
                        $('#equipment_id').val(response.equipment_name);
                    }
                    $('#body-detail').empty();
                    if(response.spare_part.length > 0){
                        $.each(response.spare_part, function(i, val) {
                            $('#body-detail').append(`
                                <tr class="row_header_part">
                                    <td rowspan="`+(val.sparepart.length+1)+`">
                                        `+val.name+`
                                    </td>
                                    <td>
                                        Kode
                                    </td>
                                    <td>
                                        Nama
                                    </td>
                                    <td>
                                        Qty Request
                                    </td>
                                    <td>
                                        Qty Usage
                                    </td>
                                    <td>
                                        Qty Return
                                    </td>
                                    <td>
                                        Qty Repair
                                    </td>
                                    <td>
                                        Pilih
                                    </td>
                                </tr>
                            `);
                            
                            $.each(val.sparepart, function(i, val_spare) {
                                var count = makeid(10);
                                $('#body-detail').append(`
                                    <tr class="row_detail">
                                        <td>
                                        `+val_spare.rawcode+`
                                        </td>
                                        <td>
                                        `+val_spare.name+`
                                        </td>
                                        <td>
                                            <input name="arr_qty_req[]" data-sparepart="` + val_spare.id + `" type="text" value="0">
                                        </td>
                                        <td>
                                            <input name="arr_qty_usage[]" data-sparepart="` + val_spare.id + `" type="text" value="0">
                                        </td>  
                                        <td>
                                            <input name="arr_qty_return[]" data-sparepart="` + val_spare.id + `" type="text" value="0">
                                        </td>  
                                        <td>
                                            <input name="arr_qty_repair[]" data-sparepart="` + val_spare.id + `" type="text" value="0">
                                        </td>
                                        <input type="hidden" name="arr_type[]" value="`+val_spare.type+`" data-id="`+count+`">
                                        <td class="center-align">
                                            <label>
                                                <input type="checkbox" id="check`+count+`" name="arr_code[]" value="`+val_spare.code+`" data-id="`+count+`">
                                                <span>Pilih</span>
                                            </label>
                                        </td>     
                                    </tr>
                                `);
                            });
                        }); 
                 
                    }else{
                        $('#body-detail').empty().append(`
                            <tr id="empty-detail">
                                <td colspan="10" class="center">
                                   Work Order tidak mencantumkan Equipment part
                                </td>
                            </tr>
                        `);
                    }
                    
                    $('.modal-content').scrollTop(0);
                    M.updateTextFields();
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
            $('#body-detail').empty();
            $('#body-detail').empty().append(`
                <tr id="empty-detail">
                    <td colspan="10" class="center">
                        Silahkan memilih WO terlebih dahulu
                    </td>
                </tr>
            `);
        }
    }

    function removeAttachment(button) {
        const row = button.parentNode.parentNode;
        row.parentNode.removeChild(row);
        const tableBody = document.getElementById('body-attachment');
        const rowNumber = tableBody.childElementCount;
        if(rowNumber < 1){
            $('#body-attachment').empty().append(`
                <tr id="empty-attachment-detail">
                    <td colspan="10" class="center">
                       Tidak ada lampiran
                    </td>
                </tr>
            `);
        }
    }

    function loadDataTable() {
		window.table = $('#datatable_serverside').DataTable({
            "responsive": false,
            "scrollX": true,
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
                    status : $('#filter_status').val(),
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
                { name: 'work_order_id', className: 'center-align' },
                { name: 'area', className: 'center-align' },
                { name: 'request_date', className: 'center-align' },
                { name: 'summary_issue', className: 'center-align' },
                { name: 'status', className: 'center-align' },
                { name: 'operation', searchable: false, orderable: false, className: 'center-align' },
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
        var content = '';
        $.ajax({
            url: '{{ Request::url() }}/row_detail',
            type: 'GET',
            async: false,
            data: {
                id: $(data[0]).data('id')
            },
            success: function(response) {
                content += response;
            },
            error: function() {
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
            }
        });

        return content;
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
                        if(response.status == 200) {
                            success();
                            M.toast({
                                html: response.message
                            });
                        } else if(response.status == 422) {
                            alert("422");
                            $('#validation_alert').show();
                            $('.modal-content').scrollTop(0);
                            
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

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
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
                $('.row_item[data-po="' + id + '"]').remove();
                if($('.row_item').length == 0 && $('#empty-item').length == 0){
                    $('#body-item').append(`
                        <tr id="empty-item">
                            <td colspan="8" class="center">
                                Pilih purchase order untuk memulai...
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

    function chooseAll(element){
        if($(element).is(':checked')){
            $('input[name^="arr_code"]').each(function(){
                if(!$(this).is(':checked')){
                    $(this).prop( "checked", true);
                }
            });
        }else{
            $('input[name^="arr_code"]').each(function(){
                if($(this).is(':checked')){
                    $(this).prop( "checked", false);
                }
            });
        }
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
                $('#modal1').modal('open');
                $('#temp').val(id);
                $('#user_name').val(response.user_name);
                $('#equipment_id').val(response.equipment_name);
                $('#work_order_id').empty().append(`
                    <option value="` + response.work_order_id + `">` + response.work_order_code + `</option>
                `);
                $('#request_date').val(response.request_date);
                
                if(response.equipment_part.length > 0){
                   
                    $.each(response.equipment_part, function(i, val) {
                        var count = makeid(10);
                        
                        $('#body-detail').append(`
                            <tr class="row_header_part">
                                <td rowspan="`+(val.sparepart.length+1)+`">
                                    `+val.name+`
                                </td>
                                <td>
                                    Kode
                                </td>
                                <td>
                                    Nama
                                </td>
                                <td>
                                    Qty Request
                                </td>
                                <td>
                                    Qty Usage
                                </td>
                                <td>
                                    Qty Return
                                </td>
                                <td>
                                    Qty Repair
                                </td>
                                <td>
                                    Pilih
                                </td>
                            </tr>
                        `);

                        $.each(val.sparepart, function(i, val_spare) {
                            var count = makeid(10);
                            $('#body-detail').append(`
                                <tr class="row_detail">
                                    <td>
                                    `+val_spare.rawcode+`
                                    </td>
                                    <td>
                                    `+val_spare.name+`
                                    </td>
                                    <td>
                                        <input name="arr_qty_req[]" data-sparepart="` + val_spare.id + `"  type="text" value="0">
                                    </td>
                                    <td>
                                        <input name="arr_qty_usage[]" data-sparepart="` + val_spare.id + `" type="text" value="0">
                                    </td>  
                                    <td>
                                        <input name="arr_qty_return[]" data-sparepart="` + val_spare.id + `"  type="text" value="0">
                                    </td>  
                                    <td>
                                        <input name="arr_qty_repair[]" data-sparepart="` + val_spare.id + `"  type="text" value="0">
                                    </td>
                                    <input type="hidden" name="arr_type[]" value="`+val_spare.type+`" data-id="`+count+`">
                                    <td class="center-align">
                                        <label>
                                            <input type="checkbox" id="check`+count+`" name="arr_code[]" value="`+val_spare.code+`" data-id="`+count+`">
                                            <span>Pilih</span>
                                        </label>
                                    </td>     
                                </tr>
                            `);
                            
                            $.each(response.request_sp_detail, function(i, val_request_sp_detail) {
                            if(val_spare.id==val_request_sp_detail.equipment_sparepart_id){
                                $('#check' + count).prop( "checked", true);
                                $('input[name^="arr_qty_req"][data-sparepart="' + val_spare.id + '"]').val(val_request_sp_detail.qty_request);
                                $('input[name^="arr_qty_usage"][data-sparepart="' + val_spare.id + '"]').val(val_request_sp_detail.qty_usage);
                                $('input[name^="arr_qty_return"][data-sparepart="' + val_spare.id + '"]').val(val_request_sp_detail.qty_return);
                                $('input[name^="arr_qty_repair"][data-sparepart="' + val_spare.id + '"]').val(val_request_sp_detail.qty_repair);
                            }
                        });

                        });
                            //untuk checklist
                      
                        
                        
                    });
                }

                $('#empty-detail').remove();
                
                $('.modal-content').scrollTop(0);
                $('#note').focus();
                M.updateTextFields();
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

    function printPreview(code){
        $.ajax({
            url: '{{ Request::url() }}/approval/' + code,
            type:'GET',
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            complete: function() {
                
            },
            success: function(data){
                loadingClose('.modal-content');
                $('#modal2').modal('open');
                $('#show_print').html(data);
            }
        });
    }

    function printData(){
        var search = window.table.search();
        var status = $('#filter_status').val();
        
        $.ajax({
            type : "POST",
            url  : '{{ Request::url() }}/print',
            data : {
                search : search,
                status : status,
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

    function exportExcel(){
        var search = window.table.search();
        var status = $('#filter_status').val();
        
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status;
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
                
                console.log("");
            } else if (part instanceof go.Node) {
                window.open(part.data.url);
                if (part.isTreeExpanded) {
                    part.collapseTree();
                } else {
                    part.expandTree();
                }
                console.log("Node clicked: " + part.data.key);
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
        console.log(data[0].key);

        myDiagram.addDiagramListener("InitialLayoutCompleted", function(e) {
        setTimeout(function() {
            console.log(data[0].key);
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
                
                $('#modal3').modal('open');
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