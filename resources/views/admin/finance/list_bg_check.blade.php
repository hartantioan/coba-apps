<!-- BEGIN: Page Main-->
<style>
    body.tab-active input:focus {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .modal-content .select2.tab-active {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
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
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="print();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">{{ __('translations.print') }}</span>
                            <i class="material-icons right">local_printshop</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="exportExcel();">
                            <i class="material-icons hide-on-med-and-up">view_list</i>
                            <span class="hide-on-small-onl">Excel</span>
                            <i class="material-icons right">view_list</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="loadDataTable();">
                            <i class="material-icons hide-on-med-and-up">refresh</i>
                            <span class="hide-on-small-onl">{{ __('translations.refresh') }}</span>
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
                            <div class="card-panel">
                                <div class="row">
                                    <div class="col s12 ">
                                        <label for="filter_status" style="font-size:1.2rem;">{{ __('translations.filter_status') }} :</label>
                                        <div class="input-field inline" style="margin-top: 0;margin-bottom: 0;">
                                            <select class="form-control" id="filter_status" onchange="loadDataTable()">
                                                <option value="">{{ __('translations.all') }}</option>
                                                <option value="1">{{ __('translations.active') }}</option>
                                                <option value="2">{{ __('translations.non_active') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">{{ __('translations.list_data') }}</h4>
                                    <div class="row">
                                        <div class="col s12">
                                            <div id="datatable_buttons"></div>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">{{ __('translations.refresh') }}</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <table id="datatable_serverside" >
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Code</th>
                                                        <th>User</th>
                                                        <th>Customer</th>
                                                        <th>Company</th>
                                                        <th>Post Date</th>
                                                        <th>Valid Date</th>
                                                        <th>Pay Date</th>
                                                        <th>Bank Source Name</th>
                                                        <th>Bank Source No</th>
                                                        <th>Document Number</th>
                                                        <th>Document</th>
                                                        <th>Note</th>
                                                        <th>Nominal</th>
                                                        <th>Grandtotal</th>
                                                        <th>Status</th>
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
            <div class="content-overlay"></div>
        </div>
    </div>
</div>

<div id="modal1" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>{{ __('translations.add') }}/{{ __('translations.edit') }} {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" ></div>
                    </div>
                    <div class="col s12">
                        <div class="input-field col s12 m5">
                            <input type="hidden" id="temp" name="temp">
                            <input id="code" name="code" type="text" placeholder="Kode">
                            <label class="active" for="code">{{ __('translations.code') }}</label>
                        </div>
                        <div class="input-field col s12 m4">
                            <select class="browser-default" id="account_id" name="account_id"></select>
                            <label class="active" for="account_id">Customer</label>
                        </div>
                        <div class="input-field col s12 m3 employee_inputs" id="company_select">
                            <select id="company_id" name="company_id">
                                @foreach($company as $row)
                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                @endforeach
                            </select>
                            <label for="company_id">{{ __('translations.company') }}</label>
                        </div>
                        <div class="input-field col s12"></div>
                        <div class="input-field col s12 m3 employee_inputs">
                            <input id="post_date" name="post_date" type="date">
                            <label class="active" for="post_date">Tgl. Post</label>
                        </div>
                        <div class="input-field col s12 m3 employee_inputs">
                            <input id="valid_until_date" name="valid_until_date" type="date">
                            <label class="active" for="valid_until_date">Valid Date</label>
                        </div>
                        <div class="input-field col s12 m3 " >
                            <input id="bank_source_name" name="bank_source_name" type="text" placeholder="Kontak Kantor">
                            <label class="active" for="bank_source_name">Nama Bank</label>
                        </div>
                        <div class="input-field col s12 m3 " >
                            <input id="bank_source_no" name="bank_source_no" type="text" placeholder="Kontak Kantor">
                            <label class="active" for="bank_source_no">Rek Bank</label>
                        </div>
                        <div class="input-field col s12 m3 " >
                            <input id="document" name="document" type="text" placeholder="Kontak Kantor">
                            <label class="active" for="document">Dokumen</label>
                        </div>
                        <div class="input-field col s12 m3 " >
                            <input id="document_no" name="document_no" type="text" placeholder="Kontak Kantor">
                            <label class="active" for="document_no">No Dokumen</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <input id="nominal" name="nominal" type="text" value="0" placeholder="Limit Kredit" onkeyup="formatRupiah(this)">
                            <label class="active" for="nominal">Nominal</label>
                        </div>
                        <div class="input-field col m3 s12 step23">
                            <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                            <label class="active" for="note">{{ __('translations.note') }}</label>
                        </div>
                        <div class="input-field col m3 s12 step24">
                            
                        </div>
                        <div class="input-field col m6 s12 step25">
                            <table width="100%" class="bordered">
                                <thead>
                                    {{-- <tr>
                                        <td width="33%"></td>
                                        <td width="33%" class="center-align">Mata Uang Asli</td>
                                        <td width="33%" class="center-align">Mata Uang Konversi</td>
                                    </tr>
                                    <tr>
                                        <td>Subtotal Sblm Diskon</td>
                                        <td class="right-align"><span id="subtotal">0,00</span></td>
                                        <td class="right-align"><span id="subtotal-convert">0,00</span></td>
                                    </tr>
                                    <tr>
                                        <td>Diskon</td>
                                        <td class="right-align">
                                            <input class="browser-default" onfocus="emptyThis(this);" id="discount" name="discount" type="text" value="0" onkeyup="formatRupiahNominal(this);countAll();" style="text-align:right;width:100%;">
                                        </td>
                                        <td class="right-align"><span id="discount-convert">0,00</span></td>
                                    </tr>
                                    <tr>
                                        <td>Subtotal Setelah Diskon</td>
                                        <td class="right-align"><span id="total">0,00</span></td>
                                        <td class="right-align"><span id="total-convert">0,00</span></td>
                                    </tr>
                                    <tr>
                                        <td>PPN</td>
                                        <td class="right-align"><span id="tax">0,00</span></td>
                                        <td class="right-align"><span id="tax-convert">0,00</span></td>
                                    </tr>
                                    <tr>
                                        <td>PPh</td>
                                        <td class="right-align">
                                            <input class="browser-default" onfocus="emptyThis(this);" id="wtax" name="wtax" type="text" value="0,00" onkeyup="formatRupiahNominal(this);countGrandtotal(this.value);" style="text-align:right;width:100%;">
                                            <td class="right-align"><span id="wtax-convert">0,00</span></td>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Pembulatan</td>
                                        <td class="right-align">
                                            <input class="browser-default" id="rounding" name="rounding" type="text" value="0,00" onkeyup="formatRupiahNominal(this);countAll();" style="text-align:right;width:100%;">
                                            <td class="right-align"><span id="rounding-convert">0,00</span></td>
                                        </td>
                                    </tr> --}}
                                    <tr>
                                        <td>Grandtotal</td>
                                        <td class="right-align"><span id="grandtotal">0,00</span></td>
                                        <td class="right-align"><span id="grandtotal-convert">0,00</span></td>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="input-field col s12 m6">
                            <div class="switch mb-1">
                                <label for="order">{{ __('translations.status') }}</label>
                                <label>
                                    {{ __('translations.non_active') }}
                                    <input checked type="checkbox" id="status" name="status" value="1">
                                    <span class="lever"></span>
                                   {{ __('translations.active') }}
                                </label>
                            </div>
                        </div>
                        <div class="col s12 mt-3">
                            <button class="btn waves-effect waves-light right submit" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div>

<!-- END: Page Main-->
<script>
    document.addEventListener('focusin', function (event) {
        const select2Container = event.target.closest('.modal-content .select2');
        const activeSelect2 = document.querySelector('.modal-content .select2.tab-active');
        if (event.target.closest('.modal-content')) {
            document.body.classList.add('tab-active');
        }
        
        
        if (activeSelect2 && !select2Container) {
            activeSelect2.classList.remove('tab-active');
        }

        
        if (select2Container) {
            select2Container.classList.add('tab-active');
        }
    });

    document.addEventListener('mousedown', function () {
        const activeSelect2 = document.querySelector('.modal-content .select2.tab-active');
        document.body.classList.remove('tab-active');
        if (activeSelect2) {
            activeSelect2.classList.remove('tab-active');
        }
    });
    $(function() {
        loadDataTable();
        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();     
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
                $('#province_id').empty();
                $('#city_id').empty();
                M.updateTextFields();
                $('#city_id').empty();
                $('#subdistrict_id').empty().append(`
                    <option value="">--{{ __('translations.select') }}--</option>
                `);
            }
        });

        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });
        select2ServerSide('#account_id', '{{ url("admin/select2/customer") }}');

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
                { name: 'id', searchable: false, className: 'center-align details-control' },
                { name: 'name', className: 'center-align' },
                { name: 'order', className: 'center-align' },
                { name: 'code', className: 'center-align' },
                { name: 'name', className: 'center-align' },
                { name: 'order', className: 'center-align' },
                { name: 'code', className: 'center-align' },
                { name: 'name', className: 'center-align' },
                { name: 'order', className: 'center-align' },
                { name: 'code', className: 'center-align' },
                { name: 'name', className: 'center-align' },
                { name: 'order', className: 'center-align' },
                { name: 'code', className: 'center-align' },
                { name: 'name', className: 'center-align' },
                { name: 'order', className: 'center-align' },
                
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
    
    function getSubdistrict(){
        if($('#city_id').val()){
            $('#subdistrict_id').empty();
            $.each($('#city_id').select2('data')[0].subdistrict, function(i, value) {
                $('#subdistrict_id').append(`
                    <option value="` + value.id + `">` + value.code + ` ` + value.name + `</option>
                `);
            });
        }else{
            $('#subdistrict_id').empty().append(`
                <option value="">--{{ __('translations.select') }}--</option>
            `);
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
                $('#account_id').append(`
                    <option value="` + response.account_id + `">` + response.account_name + `</option>
                `);
                $('#code').val(response.code);
                $('#company_id').val(response.company_id).formSelect();
                $('#document_no').val(response.document_no);
                $('#document').val(response.document);
                $('#nominal').val(response.nominal);
                $('#post_date').val(response.post_date);
                $('#note').val(response.note);
                $('#bank_source_name').val(response.bank_source_name);
                $('#bank_source_no').val(response.bank_source_no);
                $('#valid_until_date').val(response.valid_until_date);
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
        
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status;
    }
</script>