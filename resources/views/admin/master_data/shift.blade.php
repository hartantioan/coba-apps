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
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="print();">
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
                            <div class="card-panel">
                                <div class="row">
                                    <div class="col s12 ">
                                        <label for="filter_status" style="font-size:1.2rem;">Filter Status :</label>
                                        <div class="input-field inline" style="margin-top: 0;margin-bottom: 0;">
                                            <select class="form-control" id="filter_status" onchange="loadDataTable()">
                                                <option value="">Semua</option>
                                                <option value="1">Aktif</option>
                                                <option value="2">Non-Aktif</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">List Data</h4>
                                    <div class="row">
                                        <div class="col s12">
                                            <div id="datatable_buttons"></div>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">Refresh</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <table id="datatable_serverside" class="display responsive-table wrap">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Kode</th>
                                                        <th>Plant</th>
                                                    
                                                        <th>Nama</th>
                                                       
                                                        <th>Time In</th>
                                                        <th>Time Out</th>
                                                        <th>Toleransi</th>
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
                <h4>Tambah/Edit Shift</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="input-field col s4">
                            <input type="hidden" id="temp" name="temp">
                            <input id="name" name="name" type="text" placeholder="Nama Shift">
                            <label class="active" for="name">Nama</label>
                        </div>
                        
                        
                        <div class="input-field col s4">
                            <input id="time_in" name="time_in" type="text" placeholder="Time in" class="timepicker">
                            <label class="active" for="time_in">Time In</label>
                        </div>
                        <div class="input-field col s4">
                            <input id="time_out" name="time_out" type="text" placeholder="Time out" class="timepicker">
                            <label class="active" for="time_out">Time Out</label>
                        </div>
                        <div class="input-field col s4">
                            <input id="tolerant" name="tolerant" type="text" placeholder="Toleransi Waktu(jam)">
                            <label class="active" for="tolerant">Toleransi</label>
                        </div>
                        {{-- <div class="input-field col s4">
                            <input id="total_shift" name="total_shift" type="number" placeholder="Total Shift">
                            <label class="active" for="total_shift">Total Shift</label>
                        </div> --}}
                        <div class="input-field col s4">
                            <select class="form-control" id="place_id" name="place_id">
                                @foreach($place as $b)
                                    <option value="{{ $b->id }}">{{ $b->code }}</option>
                                @endforeach
                            </select>
                            <label class="" for="place_id">Plant</label>
                        </div>
                        {{-- <div class="input-field col s4">
                            <select class="form-control" id="department_id" name="department_id">
                                @foreach($department as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                            <label class="" for="department_id">Departemen</label>
                        </div> --}}
                        <div class="input-field col s4">
                            <div class="switch mb-1">
                                <label for="is_next_day">Next Day</label>
                                <label class="right">
                                    Non-Active
                                    <input  type="checkbox" id="is_next_day" name="is_next_day" value="1">
                                    <span class="lever"></span>
                                    Active
                                </label>
                            </div>
                        </div>
                        
                        <div class="input-field col s4">
                            <div class="switch mb-1">
                                <label for="status">Status</label>
                                <label class="right">
                                    Non-Active
                                    <input checked type="checkbox" id="status" name="status" value="1">
                                    <span class="lever"></span>
                                    Active
                                </label>
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

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div>

<!-- END: Page Main-->
<script>
    $(function() {
        
        
        loadDataTable();

        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();    
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
        
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
                $('#code').focus();
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                $('#coa_id').val('').trigger('change');
                M.updateTextFields();
            }
        });

        $('.timepicker').timepicker({
            container: "body",
            twelveHour: false
        });
    });

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
                { name: 'id', searchable: false, className: 'center-align' },
                { name: 'code', className: 'center-align' },
                { name: 'place', className: 'center-align' },
               /*  { name: 'department', className: 'center-align' }, */
                { name: 'name', className: 'center-align' },
               
                { name: 'time_in', className: 'center-align' },
                { name: 'time_out', className: 'center-align' },
                { name: 'Toleransi', className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle',
                'selectNone' 
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
            select: {
                style: 'multi'
            },
        });
        $('.dt-buttons').appendTo('#datatable_buttons');
        $('select[name="datatable_serverside_length"]').addClass('browser-default');
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
                $('#name').val(response.name);
                $('#place_id').val(response.place_id).formSelect();
                /* $('#department_id').val(response.department_id).formSelect(); */
                $('#tolerant').val(response.tolerant);
                $('#time_in').val(response.time_in);
                $('#time_out').val(response.time_out);
             

                if(response.status == '1'){
                    $('#status').prop( "checked", true);
                }else{
                    $('#status').prop( "checked", false);
                }

                $('.modal-content').scrollTop(0);
                $('#name').focus();
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

    var printService = new WebSocketPrinter({
        onConnect: function () {
            
        },
        onDisconnect: function () {
            /* M.toast({
                html: 'Aplikasi penghubung printer tidak terinstall. Silahkan hubungi tim EDP.'
            }); */
        },
        onUpdate: function (message) {
            
        },
    });

    function print(){
        var search = window.table.search(), status = $('#filter_status').val(), type = $('#filter_type').val(), company = $('#filter_company').val(), account = $('#filter_account').val();
        arr_id_temp=[];
        $.map(window.table.rows('.selected').nodes(), function (item) {
            var poin = $(item).find('td:nth-child(2)').text().trim();
            arr_id_temp.push(poin);
           
        });
        
        $.ajax({
            url: '{{ Request::url() }}/print',
            type: 'POST',
            dataType: 'JSON',
            data: {
                arr_id: arr_id_temp,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            success: function(response) {
                printService.submit({
                    'type': 'INVOICE',
                    'url': response.message
                })
                
               
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

    function exportExcel(){
        var search = window.table.search();
        var status = $('#filter_status').val();
        var type = $('#filter_type').val();
        
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status + "&type=" + type;
    }
</script>