<style>
    .modal {
        top:0px !important;
    }

    .select-wrapper, .select2-container {
        height:3.6rem !important;
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
                                                        <th>Pengguna</th>
                                                        <th>Code</th>
                                                        <th>Perusahaan</th>
                                                        <th>Customer</th>
                                                        <th>Outlet</th>
                                                        <th>Tanggal</th>
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
                            <div class="col s12">
                                <fieldset>
                                    <legend>1. Informasi Utama</legend>
                                    <div class="input-field col s3">
                                        <select class="form-control" id="company_id" name="company_id">
                                            @foreach ($company as $rowcompany)
                                                <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                            @endforeach
                                        </select>
                                        <label class="" for="company_id">Perusahaan</label>
                                    </div>
                                    <div class="input-field col s3">
                                        <input type="hidden" id="temp" name="temp">
                                        <select class="browser-default" id="account_id" name="account_id"></select>
                                        <label class="active" for="account_id">Customer</label>
                                    </div>
                                    <div class="input-field col s3">
                                        <select class="browser-default" id="outlet_id" name="outlet_id"></select>
                                        <label class="active" for="outlet_id">Outlet</label>
                                    </div>
                                    <div class="input-field col s3">
                                        <input id="date" name="date" type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl.posting" value="{{ date('Y-m-d') }}">
                                        <label class="active" for="date">Tgl. Posting</label>
                                    </div>
                                    <div class="input-field col s3">
                                        <input id="note" name="note" type="text" placeholder="Keterangan">
                                        <label class="active" for="note">Keterangan</label>
                                    </div>
                                    <div class="input-field col s3">
                                        <div class="switch mb-1">
                                            <label for="status">Status</label>
                                            <label>
                                                Non-Active
                                                <input checked type="checkbox" id="status" name="status" value="1">
                                                <span class="lever"></span>
                                                Active
                                            </label>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12">
                                <fieldset style="min-width: 100%;overflow:auto;">
                                    <legend>2. Detail Item</legend>
                                    <div class="col m12 s12" style="overflow:auto;width:100% !important;" id="table-item">
                                        <p class="mt-2 mb-2">
                                            <table class="bordered">
                                                <thead>
                                                    <tr>
                                                        <th class="center">Item</th>
                                                        <th class="center">Harga</th>
                                                        <th class="center">Margin</th>
                                                        <th class="center">Disc1(%)</th>
                                                        <th class="center">Disc2(%)</th>
                                                        <th class="center">Disc3(Rp)</th>
                                                        <th class="center">Harga Final</th>
                                                        <th class="center">Hapus</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="body-item">
                                                    <tr id="last-row-item">
                                                        <td colspan="8">
                                                            Silahkan tambahkan baris ...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </p>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col m12 s12 center">
                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1 mt-1 step31" onclick="addItem()" href="javascript:void(0);">
                                    <i class="material-icons left">add</i> Tambah Baris
                                </a>
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
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        loadDataTable();
        
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
                $('#code').focus();
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
                window.onbeforeunload = function() {
                    if($('.row_item').length > 0){
                        $('.row_item').remove();
                    }
                    return 'You will lose all changes made since your last save';
                };
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                M.updateTextFields();
                $('#previewUrl').html();
                $('#province_id,#subdistrict_id,#district_id,#city_id').empty().append(`
                    <option value="">--Pilih ya--</option>
                `);
                $('#account_id,#outlet_id').empty();
                if($('.row_item').length > 0){
                    $('.row_item').remove();
                }
                $('#last-row-item').remove();
                $('#body-item').append(`
                    <tr id="last-row-item">
                        <td colspan="8">
                            Silahkan tambahkan baris ...
                        </td>
                    </tr>
                `);
                window.onbeforeunload = function() {
                    return null;
                };
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

        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
            if($('.row_item').length == 0){
                $('#body-item').append(`
                    <tr id="last-row-item">
                        <td colspan="8">
                            Silahkan tambahkan baris ...
                        </td>
                    </tr>
                `);
            }
        });

        select2ServerSide('#account_id,#filter_account', '{{ url("admin/select2/customer") }}');
        select2ServerSide('#outlet_id,#filter_outlet', '{{ url("admin/select2/outlet") }}');

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
    
    function addItem(){
        var count = makeid(10);
        $('#last-row-item').remove();
        $('#body-item').append(`
            <tr class="row_item">
                <td>
                    <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" required></select>
                </td>
                <td class="center">
                    <input name="arr_price[]" class="browser-default" type="text" value="0,00" onkeyup="formatRupiah(this);countRow('` + count + `');" style="text-align:right;" id="rowPrice`+ count +`">
                </td>
                <td class="center">
                    <input name="arr_margin[]" class="browser-default" type="text" value="0,00" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowMargin`+ count +`">
                </td>
                <td class="center">
                    <input name="arr_disc1[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc1`+ count +`">
                </td>
                <td class="center">
                    <input name="arr_disc2[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc2`+ count +`">
                </td>
                <td class="center">
                    <input name="arr_disc3[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowDisc3`+ count +`">
                </td>
                <td class="center">
                    <input name="arr_final_price[]" class="browser-default" type="text" value="0,00" style="text-align:right;" id="arr_final_price`+ count +`" readonly>
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        select2ServerSide('#arr_item' + count, '{{ url("admin/select2/sales_item") }}');
    }

    function countRow(id){
        var price = parseFloat($('#rowPrice' + id).val().replaceAll(".", "").replaceAll(",",".")),
            margin = parseFloat($('#rowMargin' + id).val().replaceAll(".", "").replaceAll(",",".")),
            disc1 = parseFloat($('#rowDisc1' + id).val().replaceAll(".", "").replaceAll(",",".")), 
            disc2 = parseFloat($('#rowDisc2' + id).val().replaceAll(".", "").replaceAll(",",".")), 
            disc3 = parseFloat($('#rowDisc3' + id).val().replaceAll(".", "").replaceAll(",","."));
        
        price = price - margin;

        var finalpricedisc1 = price - (price * (disc1 / 100));
        var finalpricedisc2 = finalpricedisc1 - (finalpricedisc1 * (disc2 / 100));
        var finalpricedisc3 = finalpricedisc2 - disc3;

        if(finalpricedisc3 >= 0){
            $('#arr_final_price' + id).val(formatRupiahIni(finalpricedisc3.toFixed(2).toString().replace('.',',')));
        }else{
            $('#arr_final_price' + id).val('-' + formatRupiahIni(finalpricedisc3.toFixed(2).toString().replace('.',',')));
        }
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
                { name: 'id', searchable: false, className: 'center-align details-control' },
                { name: 'user_id', className: '' },
                { name: 'code', className: '' },
                { name: 'company_id', className: '' },
                { name: 'account_id', className: '' },
                { name: 'outlet_id', className: '' },
                { name: 'date', className: '' },
                { name: 'note', className: '' },
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
			
                var formData = new FormData($('#form_data')[0]), passed = true;

                formData.delete("arr_item[]");

                if($('select[name^="arr_item[]"]').length > 0){
                    $('select[name^="arr_item[]"]').each(function(index){
                        if($(this).val()){
                            formData.append('arr_item[]',$(this).val());
                        }else{
                            passed = false;
                        }
                    });
                }
                
                if(passed == true){
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
                }else{
                    swal({
                        title: 'Ups!',
                        text: 'Item tidak boleh kosong.',
                        icon: 'warning'
                    });
                }
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
                $('#company_id').val(response.company_id).formSelect();
                $('#account_id').empty().append(`
                    <option value="` + response.account_id + `">` + response.account_name + `</option>
                `);
                $('#outlet_id').empty().append(`
                    <option value="` + response.outlet_id + `">` + response.outlet_name + `</option>
                `);
                $('#date').val(response.date);
                $('#note').val(response.note);
                if(response.status == '1'){
                    $('#status').prop( "checked", true);
                }else{
                    $('#status').prop( "checked", false);
                }

                if($('#last-row-item').length > 0){
                    $('#last-row-item').remove();
                }

                if($('.row_item').length > 0){
                    $('.row_item').remove();
                }

                $.each(response.details, function(i, val) {
                    var count = makeid(10);
                    
                    $('#body-item').append(`
                        <tr class="row_item">
                            <td>
                                <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" required></select>
                            </td>
                            <td class="center">
                                <input name="arr_price[]" class="browser-default" type="text" value="` + val.price + `" onkeyup="formatRupiah(this);countRow('` + count + `');" style="text-align:right;" id="rowPrice`+ count +`">
                            </td>
                            <td class="center">
                                <input name="arr_margin[]" class="browser-default" type="text" value="` + val.margin + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowMargin`+ count +`">
                            </td>
                            <td class="center">
                                <input name="arr_disc1[]" class="browser-default" type="text" value="` + val.percent_discount_1 + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc1`+ count +`">
                            </td>
                            <td class="center">
                                <input name="arr_disc2[]" class="browser-default" type="text" value="` + val.percent_discount_2 + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc2`+ count +`">
                            </td>
                            <td class="center">
                                <input name="arr_disc3[]" class="browser-default" type="text" value="` + val.discount_3 + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowDisc3`+ count +`">
                            </td>
                            <td class="center">
                                <input name="arr_final_price[]" class="browser-default" type="text" value="` + val.final_price + `" style="text-align:right;" id="arr_final_price`+ count +`" readonly>
                            </td>
                            <td class="center">
                                <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                    <i class="material-icons">delete</i>
                                </a>
                            </td>
                        </tr>
                    `);
                    $('#arr_item' + count).append(`
                        <option value="` + val.item_id + `">` + val.item_name + `</option>
                    `);
                    select2ServerSide('#arr_item' + count, '{{ url("admin/select2/sales_item") }}');
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
</script>