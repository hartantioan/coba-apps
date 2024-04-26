<style>
    .modal {
        top:0px !important;
    }

    table > thead > tr > th {
        font-size: 13px !important;
    }


    body.tab-active input:focus {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .modal-content .select2.tab-active {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
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

                                        <label for="filter_type" style="font-size:1.2rem;">Filter Tipe :</label>
                                        <div class="input-field inline" style="margin-top: 0;margin-bottom: 0;">
                                            <select class="form-control" id="filter_type" onchange="loadDataTable()">
                                                <option value="">Semua</option>
                                                <option value="1">Perakitan</option>
                                                <option value="2">Penjualan</option>
                                                <option value="3">Produksi</option>
                                                <option value="4">Template</option>
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
                                            <table id="datatable_serverside" >
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Kode</th>
                                                        <th>Nama</th>
                                                        <th>Item</th>
                                                        <th>Plant</th>
                                                        <th>Gudang</th>
                                                        <th>Line</th>
                                                        <th>Mesin</th>
                                                        <th>Qty Output</th>
                                                        <th>Qty Rencana</th>
                                                        <th>Type</th>
                                                        <th>Valid Dari</th>
                                                        <th>Valid Sampai</th>
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
                <h4>Tambah/Edit Bill of Material</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="input-field col s4 m3">
                            <input type="hidden" id="temp" name="temp">
                            <select class="browser-default" id="item_id" name="item_id" onchange="getCodeAndName();"></select>
                            <label class="active" for="item_id">Item Input (Target)</label>
                        </div>
                        <div class="input-field col s4 m3">
                            <input id="code" name="code" type="text" placeholder="Kode Bill Of Material">
                            <label class="active" for="code">Kode</label>
                        </div>
                        <div class="input-field col s4 m3">
                            <input id="name" name="name" type="text" placeholder="Nama Bill Of Material">
                            <label class="active" for="name">Nama</label>
                        </div>
                        <div class="input-field col s4 m3">
                            <input id="qty_output" name="qty_output" type="text" placeholder="Qty Output" onkeyup="formatRupiah(this)">
                            <label class="active" for="qty_output">Qty Output (Satuan Produksi)</label>
                            <div class="form-control-feedback production-unit">-</div>
                        </div>
                        <div class="input-field col s4 m3">
                            <input id="qty_planned" name="qty_planned" type="text" placeholder="Rata-rata Qty Produksi" onkeyup="formatRupiah(this)">
                            <label class="active" for="qty_planned">Rata-rata Qty Produksi (Satuan Produksi)</label>
                            <div class="form-control-feedback production-unit">-</div>
                        </div>
                        <div class="input-field col s4 m3">
                            <select class="form-control" id="type" name="type">
                                <option value="">-- Pilih salah satu --</option>
                                <option value="1">Perakitan</option>
                                <option value="2">Penjualan</option>
                                <option value="3">Produksi</option>
                                <option value="4">Template</option>
                            </select>
                            <label class="" for="type">Tipe</label>
                        </div>
                        <div class="input-field col s4 m3">
                            <select class="form-control" id="place_id" name="place_id" onchange="changeLineMachine(this);">
                                @foreach($place as $b)
                                    <option value="{{ $b->id }}">{{ $b->code }}</option>
                                @endforeach
                            </select>
                            <label class="" for="place_id">Plant</label>
                        </div>
                        <div class="input-field col s4 m3">
                            <select class="form-control" id="line_id" name="line_id" onchange="changePlace(this);">
                                <option value="" data-place="">--Kosong--</option>
                                @foreach($line as $b)
                                    <option value="{{ $b->id }}" data-place="{{ $b->place_id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                            <label class="" for="line_id">Line</label>
                        </div>
                        <div class="input-field col s4 m3">
                            <select class="form-control" id="machine_id" name="machine_id" onchange="changeLine(this);">
                                <option value="" data-line="">--Kosong--</option>
                                @foreach($machine as $b)
                                    <option value="{{ $b->id }}" data-line="{{ $b->line_id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                            <label class="" for="machine_id">Mesin</label>
                        </div>
                        <div class="input-field col s4 m3">
                            <select class="form-control" id="warehouse_id" name="warehouse_id">
                                @foreach($warehouse as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                            <label class="" for="warehouse_id">Gudang</label>
                        </div>
                        <div class="input-field col s4 m3">
                            <input id="valid_from" name="valid_from" type="date" value="{{ date('Y-m-d') }}">
                            <label class="active" for="valid_from">Valid Dari</label>
                        </div>
                        <div class="input-field col s4 m3">
                            <input id="valid_to" name="valid_to" type="date" value="{{ date('Y-m-d') }}">
                            <label class="active" for="valid_to">Valid Hingga</label>
                        </div>
                        <div class="input-field col s4 m3">
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
                        <div class="col s12">
                            <h6>Item & Resource Output</h6>
                            <table class="bordered mt-2">
                                <thead>
                                    <tr>
                                        <th class="center">Tipe</th>
                                        <th class="center">Item/Resource</th>
                                        <th class="center">Qty</th>
                                        <th class="center">Satuan (Produksi)</th>
                                        <th class="center">Nominal</th>
                                        <th class="center">Total</th>
                                        <th class="center">Deskripsi</th>
                                        <th class="center">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody id="body-detail">
                                    <tr id="empty-row-detail">
                                        <td colspan="8" class="center">
                                            <i>Silahkan tambahkan item / resource...</i>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="col s12 center mt-1">
                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addDetail('items')" href="javascript:void(0);">
                                    <i class="material-icons left">add</i> Bahan
                                </a>
                                <a class="waves-effect waves-light red btn-small mb-1 mr-1" onclick="addDetail('resources')" href="javascript:void(0);">
                                    <i class="material-icons left">add</i> Resource
                                </a>
                                <div>
                                    <i>
                                        Untuk detail tipe item, harga 0, karena perhitungan diambil dari rata-rata stok berjalan.
                                    </i>
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
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
                $('ul.tabs').tabs();
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                M.updateTextFields();
                resetDetailForm();
                $('#item_id').empty();
            }
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
        
        select2ServerSide('#item_id', '{{ url("admin/select2/item") }}');

        $("#item_id").on("select2:unselecting", function(e) {
            $('#code').val('');
            $('#name').val('');
        });

        $('#body-detail').on('click', '.delete-data-detail', function() {
            $(this).closest('tr').remove();
            if($('.row_detail').length == 0){
                $('#body-detail').append(`
                    <tr id="empty-row-detail">
                        <td colspan="8" class="center">
                            <i>Silahkan tambahkan item / resource...</i>
                        </td>
                    </tr>
                `);
            }
        });
    });

    function resetDetailForm(){
        $('.row_detail').each(function(){
            $(this).remove();
        });
        if($('.row_detail').length == 0 && $('#empty-row-detail').length == 0){
            $('#body-detail').append(`
                <tr id="empty-row-detail">
                    <td colspan="8" class="center">
                        <i>Silahkan tambahkan item / resource...</i>
                    </td>
                </tr>
            `);
        }
    }

    function countAll(){
        $('input[name^="arr_qty"]').each(function(index){
            let total = 0, qty = 0, nominal = 0;
            qty = parseFloat($('input[name^="arr_qty"]').eq(index).val().replaceAll(".", "").replaceAll(",","."));
            nominal = parseFloat($('input[name^="arr_nominal"]').eq(index).val().replaceAll(".", "").replaceAll(",","."));
            total = qty * nominal;
            $('input[name^="arr_total"]').eq(index).val(
                (total >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(total).toString().replace('.',','))
            );
        });
    }

    function addDetail(param){
        if($('#empty-row-detail').length > 0){
            $('#empty-row-detail').remove();
        }
        var count = makeid(10);
        $('#body-detail').append(`
            <tr class="row_detail">
                <input name="arr_type[]" value="` + param + `" type="hidden">
                <td>
                    ` + (param == 'items' ? 'Item' : 'Resource') + `
                </td>
                <td>
                    <select class="browser-default" name="arr_detail[]" id="arr_detail` + count + `" onchange="getRowUnit('` + count + `','` + param + `')"></select>
                </td>
                <td>
                    <input name="arr_qty[]" id="arr_qty` + count + `" type="text" value="0,000" onkeyup="formatRupiah(this);countAll();">
                </td>
                <td class="center">
                    <span id="arr_satuan` + count + `">-</span>
                </td>
                <td>
                    <input name="arr_nominal[]" id="arr_nominal` + count + `" type="text" value="0,00">
                </td>
                <td>
                    <input name="arr_total[]" id="arr_total` + count + `" type="text" value="0,00" readonly>
                </td>
                <td>
                    <input name="arr_description[]" type="text" placeholder="Deskripsi item material">
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        if(param == 'items'){
            select2ServerSide('#arr_detail' + count, '{{ url("admin/select2/item") }}');
        }else if(param == 'resources'){
            select2ServerSide('#arr_detail' + count, '{{ url("admin/select2/resource") }}');
        }
    }

    function getRowUnit(val,param){
        if($("#arr_detail" + val).val()){
            $('#arr_satuan' + val).text($("#arr_detail" + val).select2('data')[0].uom);
            if(param == 'resources'){
                $('#arr_nominal' + val).val(formatRupiahIni(parseFloat($("#arr_detail" + val).select2('data')[0].cost).toFixed(2).toString().replace('.',',')));
                $('#arr_qty' + val).val(formatRupiahIni(parseFloat($("#arr_detail" + val).select2('data')[0].qty).toFixed(3).toString().replace('.',',')));
                countAll();
            }
        }else{
            $('#arr_satuan' + val).text('-');
        }
    }

    function getCodeAndName(){
        if($("#item_id").val()){
            $('#code').val($("#item_id").select2('data')[0].code);
            $('#name').val($("#item_id").select2('data')[0].name);
            $('.production-unit').text($("#item_id").select2('data')[0].uom);
        }else{
            $('#code').val('');
            $('#name').val('');
            $('.production-unit').text('-');
        }
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
            "fixedColumns": {
                left: 2,
                right: 1
            },
            "order": [[0, 'desc']],
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    status : $('#filter_status').val(),
                    type : $('#filter_type').val()
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
                { name: 'name', className: 'center-align' },
                { name: 'item', className: 'center-align' },
                { name: 'place', className: 'center-align' },
                { name: 'warehouse', className: 'center-align' },
                { name: 'line', className: 'center-align' },
                { name: 'machine', className: 'center-align' },
                { name: 'qty_output', className: 'center-align' },
                { name: 'qty_convert', className: 'center-align' },
                { name: 'type', searchable: false, className: 'center-align' },
                { name: 'valid_from', searchable: false, className: 'center-align' },
                { name: 'valid_to', searchable: false, className: 'center-align' },
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
                        loadingOpen('#modal1');
                    },
                    success: function(response) {
                        loadingClose('#modal1');
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
                        loadingClose('#modal1');
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

    function changePlace(element){
        if($(element).val()){
            $('#place_id').val($(element).find(':selected').data('place'));
            $('#machine_id option[data-line!="' + $(element).val() + '"]').hide();
        }else{
            $('#place_id').val($('#place_id option:first').val()).formSelect();
        }
    }

    function changeLine(element){
        if($(element).val()){
            $('#line_id').val($(element).find(':selected').data('line')).formSelect().trigger('change');
        }else{
            $('#line_id').val($('#line_id option:first').val()).formSelect().trigger('change');
        }
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
                $('#code').val(response.code);
                $('#name').val(response.name);
                $('#item_id').empty();
                $('#item_id').append(`
                    <option value="` + response.item_id + `">` + response.item_name + `</option>
                `);
                $('#company_id').val(response.company_id).formSelect();
                $('#place_id').val(response.place_id).formSelect();
                $('#line_id').val(response.line_id).formSelect();
                $('#warehouse_id').val(response.warehouse_id).formSelect();
                $('#machine_id').val(response.machine_id).formSelect();
                $('#valid_from').val(response.valid_from);
                $('#valid_to').val(response.valid_to);
                $('#qty_output').val(response.qty_output);
                $('#qty_planned').val(response.qty_planned);
                $('#type').val(response.type).formSelect();

                if(response.status == '1'){
                    $('#status').prop( "checked", true);
                }else{
                    $('#status').prop( "checked", false);
                }

                resetDetailForm();

                $.each(response.details, function(i, val) {
                    if($('#empty-row-detail').length > 0){
                        $('#empty-row-detail').remove();
                    }
                    var count = makeid(10);
                    $('#body-detail').append(`
                        <tr class="row_detail">
                            <input name="arr_type[]" value="` + val.lookable_type + `" type="hidden">
                            <td>
                                ` + (val.lookable_type == 'items' ? 'Item' : 'Resource') + `
                            </td>
                            <td>
                                <select class="browser-default" name="arr_detail[]" id="arr_detail` + count + `" onchange="getRowUnit('` + count + `','` + val.lookable_type + `')"></select>
                            </td>
                            <td>
                                <input name="arr_qty[]" id="arr_qty` + count + `" type="text" value="` + val.qty + `" onkeyup="formatRupiah(this);countAll();">
                            </td>
                            <td class="center">
                                <span id="arr_satuan` + count + `">` + val.uom_unit + `</span>
                            </td>
                            <td>
                                <input name="arr_nominal[]" id="arr_nominal` + count + `" type="text" value="` + val.nominal + `">
                            </td>
                            <td>
                                <input name="arr_total[]" id="arr_total` + count + `" type="text" value="` + val.total + `" readonly>
                            </td>
                            <td>
                                <input name="arr_description[]" type="text" placeholder="Deskripsi item material" value="` + val.description + `">
                            </td>
                            <td class="center">
                                <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                                    <i class="material-icons">delete</i>
                                </a>
                            </td>
                        </tr>
                    `);
                    $('#arr_detail' + count).append(`
                        <option value="` + val.lookable_id + `">` + val.detail_text + `</option>
                    `);
                    if(val.lookable_type == 'items'){
                        select2ServerSide('#arr_detail' + count, '{{ url("admin/select2/item") }}');
                    }else if(val.lookable_type == 'resources'){
                        select2ServerSide('#arr_detail' + count, '{{ url("admin/select2/resource") }}');
                    }
                });

                $('.modal-content').scrollTop(0);
                $('#code').focus();
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