<!-- BEGIN: Page Main-->
<div id="main">
    <div class="row">
        <div class="pt-3 pb-1" id="breadcrumbs-wrapper">
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
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="loadDataTable();">
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
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">List Antrian Request Repair</h4>
                                    <div class="row">
                                        <div class="col s12">
                                            <div id="datatable_buttons_request"></div>
                                            <table id="datatable_serverside_request" class="display responsive-table wrap">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Code</th>
                                                        <th>Item</th>
                                                        <th>Keluhan</th>
                                                        <th>Tanggal</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">List Maintenance Hardware</h4>
                                    <div class="row">
                                        <div class="col s12">
                                            <div id="datatable_buttons"></div>
                                            <table id="datatable_serverside" class="display responsive-table wrap">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Kode</th>
                                                        <th>Item</th>
                                                        <th>Requester</th>
                                                        <th>Start Date</th>
                                                        <th>Finish Date</th>
                                                        <th>PIC</th>
                                                        <th>Solution</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Tambah/Edit {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="input-field col s6">
                            <input id="name" name="name" type="text" placeholder="Nama" value="{{session('bo_name')}}" readonly>
                            <label class="active" for="name">Nama</label>
                        </div>
                        <div class="input-field col s6">
                            <input type="hidden" id="temp" name="temp"> 
                            <input type="hidden" id="temp_request" name="temp_request"> 
                            <input id="item_name" name="item_name" type="text" placeholder="" readonly>
                            <label class="active" for="item_name">Nama Item</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="start_date" name="start_date" min="{{ $minDate }}" max="{{ $maxDate}}" type="date" placeholder="Tgl. mulai" value="{{ date('Y-m-d') }}">
                            <label class="active" for="start_date">Tgl. Mulai</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="end_date" name="end_date" min="{{ $minDate }}" type="date" value="{{ date('Y-m-d') }}">
                            <label class="active" for="end_date">Perkiraan Selesai</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="solution" name="solution" type="text" placeholder="Keterangan">
                            <label class="active" for="solution">Solusi</label>
                        </div>
                        <div class="input-field col s12">
                            <div class="row">
                                <div class="file-field input-field col m8 s12">
                                    <div class="btn col m3 s12">
                                        <span>Lampiran Bukti</span>
                                        <input type="file" name="attachment" id="document" maxlength="1000000" accept="image/png, image/jpg">
                                    </div>
                                    <div class="file-path-wrapper col m6 s12">
                                        <input class="file-path validate" id="file-name" type="text" >
                                    </div>
                                </div>
                                <div class="col m4 s12">
                                    <button id="add-attachment" class="btn" type="button">Add Attachment</button>
                                </div>
                                <div class="col m4 s12">
                                    <div id="previewImg"></div>
                                </div>
                                <div class="col m12 s12">
                                    <div style="overflow:auto;">
                                        <table class="bordered" style="max-width:1650px !important;">
                                            <thead>
                                                <tr>
                                                    <th class="center">No</th>
                                                    <th class="center">Deskripsi</th>
                                                    <th class="center">Created At</th>
                                                    <th class="center">Display</th>
                                                    <th class="center">Action</th>
                                                    
                                                </tr>
                                            </thead>
                                            <tbody id="body-attachment">
                                                <tr id="empty-attachment-detail">
                                                    <td colspan="10" class="center">
                                                        Tidak ada lampiran
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col s12 mt-3">
                            <button class="btn waves-effect waves-light right submit" onclick="save();">Simpan <i class="material-icons right">send</i></button>
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

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div>

<!-- END: Page Main-->
<script>
    var tabledata,tabledatarequest;
    var rowNumber=0;
    $(function() {
        loadDataTable();
        
        select2ServerSide('#user_id', '{{ url("admin/select2/employee") }}');

        const addAttachmentBtn = document.getElementById('add-attachment');
        var attachmentCount = 0;
        addAttachmentBtn.addEventListener('click', () => {

            

            var base64image = "";
            var input = document.getElementById("document");
            
            var fReader = new FileReader();
            fReader.readAsDataURL(input.files[0]);
            fReader.onload =  function(e){
                $.ajax({
                    url: '{{ Request::url() }}/get_decode',
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        base64: e.target.result
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        const currentDate = new Date();
                        const year = currentDate.getFullYear();
                        const month = currentDate.getMonth() + 1;
                        const day = currentDate.getDate();
                        const hours = currentDate.getHours();
                        const minutes = currentDate.getMinutes();
                        const seconds = currentDate.getSeconds();
                        href = response.result;
                        base64image = e.target.result;
                        var fileInput = document.getElementById('document');
                        var filepath = document.getElementById('file-name');
                        const file = fileInput.files[0];
                        if (!filepath.value)
                        {
                            alert("File Tidak boleh Kosong");
                            return;
                        }
                        
                        $("#empty-attachment-detail").remove();
                        $('#body-attachment').append(`
                                <tr class="row_detail">
                                    <input type="hidden" name="arr_ada[]" value="0">
                                    <input type="hidden" name="arr_typefile[]" value="file">
                                    <td >
                                        ` + (rowNumber+1) + `
                                    </td>
                                    <input type="hidden" name="arr_file_name[]" value="`+file.name+`">
                                    <td>
                                        ` + file.name + `
                                    </td>
                                    <input type="hidden" name="arr_file_path[]" value="`+base64image+`">
                                    <td>
                                        ` + day +`-`+month+`-`+year+`
                                    </td>
                                    <td>
                                        <a href="`+  href +`" target="_blank"><i class="material-icons">attachment</i></a>',
                                    </td>
                                    <td>
                                        <button class="btn red" type="button" onclick="removeAttachment(this)">Remove</button>
                                    </td>
                                    
                                </tr>
                        `);
                        rowNumber++;
                        $('#file-name').val('');
                    },
                    error: function() {
                        swal({
                            title: 'Ups!',
                            text: 'Check your internet connection.',
                            icon: 'error'
                        });
                    }
                });               
            };
            
        });
        
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
                $('#name').focus();
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                $('#temp_request').val('');
                $('.row_pic_detail').each(function(){
                    $(this).remove();
                });
                $('.row_detail').each(function(){
                    $(this).remove();
                });
                M.updateTextFields();
            }
        });

        

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

    function loadDataTable() {
		tabledata = $('#datatable_serverside').DataTable({
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
                    status : $('#filter_status').val()
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
                { name: 'request_repair_hardware_items_usage_id', className: 'center-align' },
                { name: 'request_repair_hardware_items_usage_id', className: 'center-align' },
                { name: 'start_date', className: 'center-align' },
                { name: 'end_date', className: 'center-align' },
                { name: 'solution', className: 'center-align' },
                { name: 'user_id', className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle' 
            ]
        });
        $('#datatable_serverside_wrapper > .dt-buttons').appendTo('#datatable_buttons');
        $('select[name="datatable_serverside_length"]').addClass('browser-default');

        tabledatarequest = $('#datatable_serverside_request').DataTable({
            "responsive": true,
            "stateSave": true,
            "serverSide": true,
            "deferRender": true,
            "destroy": true,
            "iDisplayInLength": 10,
            "order": [[0, 'asc']],
            ajax: {
                url: '{{ Request::url() }}/datatable_request',
                type: 'GET',
                data: {
                    status : $('#filter_status').val()
                },
                beforeSend: function() {
                    loadingOpen('#datatable_serverside_request');
                },
                complete: function() {
                    loadingClose('#datatable_serverside_request');
                },
                error: function() {
                    loadingClose('#datatable_serverside_request');
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
                { name: 'hardware_item_id', className: 'center-align' },
                { name: 'complaint', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle' 
            ]
        });
        $('#datatable_serverside_request_wrapper > .dt-buttons').appendTo('#datatable_buttons_request');

        $('select[name="datatable_serverside_request_length"]').addClass('browser-default');
	}

    function removeAttachment(button) {
        const row = button.parentNode.parentNode;
        
        const tableBody = document.getElementById('body-attachment');
        const rowNumber = tableBody.childElementCount;
        const arrIdInput = row.querySelector('input[name="arr_ada[]"]');
        const arrIdValue = arrIdInput ? arrIdInput.value : null;
        
        if(arrIdValue != 0){
            
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
                        url: '{{ Request::url() }}/delete_attachment',
                        type: 'POST',
                        dataType: 'JSON',
                        data: { id : arrIdValue },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        beforeSend: function() {
                            loadingOpen('#main');
                        },
                        success: function(response) {
                            loadingClose('#main');
                            row.parentNode.removeChild(row);
                            if(rowNumber < 1){
                                $('#body-attachment').empty().append(`
                                    <tr id="empty-attachment-detail">
                                        <td colspan="10" class="center">
                                        Tidak ada lampiran
                                        </td>
                                    </tr>
                                `);
                            }
                            M.toast({
                                html: response.message
                            });
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
        }else{
            row.parentNode.removeChild(row);
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
        

    }

    function save(){
			
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

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
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
                $('#name').val(response.user_name);
                $('#item_name').val(response.request);
                $('#solution').val(response.solution);

                if(response.attachments.length > 0){
                    $('#empty-attachment-detail').remove();
                    $.each(response.attachments, function(i, val_file) {
                        
                       
                        $('#body-attachment').append(`
                            <tr class="row_detail">
                                <input type="hidden" name="arr_ada[]" value="`+val_file.id+`">
                                <input type="hidden" name="arr_typefile[]" value="file">
                                <td >
                                    ` + (rowNumber+1) + `
                                </td>
                                <input type="hidden" name="arr_file_name[]" value="`+val_file.file_name+`">
                                <td>
                                    ` + val_file.file_name + `
                                </td>
                                <input type="hidden" name="arr_file_path[]" value="`+val_file.path+`">
                                <td>
                                    ` +  val_file.created_at + `
                                </td>
                                <td>
                                <a href="`+  val_file.attachment +`" target="_blank"><i class="material-icons">attachment</i></a>',
                                </td>
                                <td>
                                    <button class="btn red" type="button" onclick="removeAttachment(this)">Remove</button>
                                </td>
                            </tr>
                        `);
                        rowNumber++;
                    });
                    
                }
          
                $('.modal-content').scrollTop(0);
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

    function show_request(id){
        $.ajax({
            url: '{{ Request::url() }}/show_request',
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
                $('#temp_request').val(id);
                $('#item_name').val(response.item);
                
                $('.modal-content').scrollTop(0);
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
</script>