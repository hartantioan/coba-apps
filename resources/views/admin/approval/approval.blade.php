<style>
    .modal {
        top:0px !important;
    }
    ::-webkit-scrollbar {
        width: 12px;
    }

    *::-webkit-scrollbar-track {
        background: orange;
    }

    *::-webkit-scrollbar-thumb {
        background-color: blue;
        border-radius: 20px;
        border: 1px solid orange;
    }

    #datatable_serverside tbody tr.selected {
		background-color: green !important;
		color:white !important;
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
                            <li class="breadcrumb-item active">{{ Str::title(str_replace('_',' ',Request::segment(2))) }}
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
                            <ul class="collapsible collapsible-accordion">
                                <li>
                                    <div class="collapsible-header"><i class="material-icons">filter_list</i> FILTER</div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col m4 s6 ">
                                                <label for="filter_status" style="font-size:1rem;">Filter Status :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_status" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        <option value="1">Menunggu</option>
                                                        <option value="2">Selesai</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_form" style="font-size:1rem;">Filter Form :</label>
                                                <div class="input-field">
                                                    <select class="browser-default" id="filter_form" onchange="loadDataTable()"></select>
                                                </div>
                                            </div>
                                        </div>  
                                    </div>
                                </li>
                            </ul>
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">List Data
                                        <button class="waves-effect waves-light btn mb-1 mr-1 cyan right hide btn-multi" onclick="multiApprove()">Proses</button>
                                        <button class="waves-effect waves-light btn mb-1 mr-1 right" onclick="selectAllRow()">BARIS TERPILIH : <b id="countSelected">0</b></button>
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
                                                        <th>Kode</th>
                                                        <th>Tgl.Request</th>
                                                        <th>Dari</th>
                                                        <th>Kode Ref.</th>
                                                        <th>Keterangan</th>
                                                        <th>Action</th>
                                                        <th>Status</th>
                                                        <th>Catatan</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;width:100%;top:0px !important;">
    <div class="modal-content">
        <div class="row">
            <div class="col m12 xl3 center-align">
                <h6>Form persetujuan</h6>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12 center">
                        <div class="input-field col s12">
                            <select class="form-control" id="approve_reject_revision" name="approve_reject_revision">
                                <option value="1">Setuju</option>
                                <option value="2">Tolak</option>
                                <option value="3">Revisi</option>
                            </select>
                        </div>
                        <div class="input-field col s12">
                            <input type="hidden" id="temp" name="temp">
                            <textarea id="note" name="note" placeholder="Keterangan" class="materialize-textarea"></textarea>
                            <label class="active" for="note">Keterangan</label>
                        </div>
                        <div class="col s12 mt-3">
                            <button class="btn waves-effect waves-light right submit" onclick="approve();">Kirim <i class="material-icons right">send</i></button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col m12 xl9">
                <h6 align="center">Preview Dokumen</h6>
                <div class="row">
                    <div class="col m12 s12" id="body_show" style="border-style: solid;border-color: coral;min-height:80vh;max-height:80vh;overflow:auto;width:100%;">

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal2" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;top:0px !important;">
    <div class="modal-content">
        <div class="row">
            <div class="col m12 xl12 center-align">
                <h6>Form persetujuan (multi)</h6>
                <form class="row" id="form_data_multi" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert_multi" style="display:none;"></div>
                    </div>
                    <div class="col s12 center">
                        <div class="input-field col s12" style="zoom:1.5;">
                            <select class="form-control" id="approve_reject_revision_multi" name="approve_reject_revision_multi">
                                <option value="1">Setuju</option>
                                <option value="2">Tolak</option>
                                <option value="3">Revisi</option>
                            </select>
                        </div>
                        <div class="input-field col s12">
                            <input type="hidden" name="tempMulti" id="tempMulti">
                            <textarea id="note_multi" name="note_multi" placeholder="Keterangan" class="materialize-textarea"></textarea>
                            <label class="active" for="note_multi">Keterangan</label>
                        </div>
                        <div class="col s12 mt-3">
                            <button class="btn waves-effect waves-light right submit" onclick="approveMulti();">Simpan <i class="material-icons right">send</i></button>
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

<!-- END: Page Main-->
<script>
    $(function() {
        loadDataTable();

        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                $('#approve_reject_revision').formSelect();
                $('#form_data')[0].reset();
                $('#temp').val('');
                $('#body_show').html('');
                M.updateTextFields();
                $('#tempMulti').val('');
                countSelected();
            }
        });

        $('#modal2').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) {
                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                $('#approve_reject_revision_multi').formSelect();
                $('#form_data_multi')[0].reset();
                $('#tempMulti').val('');
                M.updateTextFields();
                $('#datatable_serverside tr.selected').each(function(){
                    $(this).removeClass('selected');
                });
                countSelected();
                $('.btn-multi').addClass('hide');
            }
        });

        $('#datatable_serverside tbody').on('click', 'tr', function () {
			if($(this).find('.pick').data('id')){
				$(this).toggleClass('selected');
				
				var arrId = [];
			
				$('#datatable_serverside tr.selected').each(function(){
					if($(this).find('.pick').data('id')){
						arrId.push($(this).find('.pick').data('id'));
					}
				});
				
				$('#tempMulti').val(arrId.join());
				
				countSelected();
			}else{
				M.toast({
                    html: "Ups, anda tidak bisa memilih baris ini ya."
                });
			}
		});

        $('#datatable_serverside tbody').on('click', 'button', function (event) {
            event.stopPropagation();
        });

        select2ServerSide('#filter_form', '{{ url("admin/select2/form_user") }}');
    });

    function multiApprove(){
        $('#modal2').modal('open');
    }

    function countSelected(){
		var count = 0;
		$('#datatable_serverside tr.selected').each(function(){
			count += 1;
		});
		
		$('#countSelected').text(count);
		
		if($('#datatable_serverside tr.selected').length > 0){
			$('.btn-multi').removeClass('hide');
		}else{
			$('.btn-multi').addClass('hide');
		}
	}

    function selectAllRow(){
		$('#datatable_serverside tbody tr').each(function(){
			if($(this).find('.pick').data('id')){
				$(this).trigger('click');
			}
		});
		countSelected();
	}

    function loadDataTable() {
		window.table = $('#datatable_serverside').DataTable({
            "scrollCollapse": true,
            "scrollY": '400px',
            "responsive": false,
            "scrollX": true,
            "stateSave": $('#filter_form').val() ? false : true,
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
                    type : $('#filter_form').val()
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
                { name: 'id', searchable: false, className: 'center-align' },
                { name: 'code', className: 'center-align' },
                { name: 'date_request', className: 'center-align' },
                { name: 'user', orderable: false, className: 'center-align' },
                { name: 'code_ref', orderable: false, className: 'center-align' },
                { name: 'note_ref', orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'note', searchable: false, orderable: false, className: 'center-align' },
            ],
            "createdRow": function( row, data, dataIndex){
                if(data[9]){
                    $(row).addClass('cyan lighten-4');
                }
            },
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle' 
            ]
        });
        $('.dt-buttons').appendTo('#datatable_buttons');

        $('select[name="datatable_serverside_length"]').addClass('browser-default');
	}

    function show(destination,code){
        $.ajax({
            url:destination,
            type:'GET',
            beforeSend: function() {
                loadingOpen('#main');
            },
            complete: function() {
                
            },
            success: function(data){
                loadingClose('#main');
                $('#temp').val(code);
                $('#modal1').modal('open');
                $('#body_show').html(data);
            },
            statusCode: {
                403: function() { 
                    swal({
                        title: 'Ups! Anda tidak memiliki akses.',
                        text: 'Anda tidak dapat mengakses halaman ini. Silahkan hubungi tim terkait.',
                        icon: 'warning'
                    });
                    loadingClose('#main');
                },
            }
        });

        return false;
    }

    function approve(){
        swal({
            title: "Apakah anda yakin?",
            text: "Anda tidak bisa mengembalikan data yang tersetujui!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willApprove) {
            if (willApprove) {
                var formData = new FormData($('#form_data')[0]);
                if($('input[name^="arr_status_material_request[]"]').length > 0){
                    $('input[name^="arr_status_material_request[]"]').each(function(index){
                        if($(this).is(':checked')){
                            formData.append('arr_status_material_request[]',$(this).val());
                        }
                    });
                }
                $.ajax({
                    url: '{{ Request::url() }}/approve',
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
        });
    }

    function approveMulti(){
		
        swal({
            title: "Apakah anda yakin?",
            text: "Anda tidak bisa mengembalikan data yang tersetujui!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willApprove) {
            if (willApprove) {
                var formData = new FormData($('#form_data_multi')[0]);
                $.ajax({
                    url: '{{ Request::url() }}/approve_multi',
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
                        $('#validation_alert_multi').hide();
                        $('#validation_alert_multi').html('');
                        loadingOpen('.modal-content');
                    },
                    success: function(response) {
                        loadingClose('.modal-content');
                        if(response.status == 200) {
                            success2();
                            M.toast({
                                html: response.message
                            });
                        } else if(response.status == 422) {
                            $('#validation_alert_multi').show();
                            $('.modal-content').scrollTop(0);
                            
                            swal({
                                title: 'Ups! Validation',
                                text: 'Check your form.',
                                icon: 'warning'
                            });

                            $.each(response.error, function(i, val) {
                                $.each(val, function(i, val) {
                                    $('#validation_alert_multi').append(`
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

    function success2(){
        loadDataTable();
        $('#modal2').modal('close');
        selectAllRow();
    }
</script>