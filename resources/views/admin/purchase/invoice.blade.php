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
                                                <label for="filter_branch" style="font-size:1rem;">Cabang :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_branch" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        @foreach ($branch as $rowbranch)
                                                            <option value="{{ $rowbranch->id }}">{{ $rowbranch->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_plant" style="font-size:1rem;">Pabrik :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_plant" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        @foreach ($plant as $rowplant)
                                                            <option value="{{ $rowplant->id }}">{{ $rowplant->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_department" style="font-size:1rem;">Departemen :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_department" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        @foreach ($department as $rowdepartment)
                                                            <option value="{{ $rowdepartment->id }}">{{ $rowdepartment->name }}</option>
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
                                                <label for="filter_currency" style="font-size:1rem;">Mata Uang :</label>
                                                <div class="input-field">
                                                    <select class="select2 browser-default" multiple="multiple" id="filter_currency" name="filter_currency" onchange="loadDataTable()">
                                                        <option value="" disabled>Semua</option>
                                                        @foreach ($currency as $row)
                                                            <option value="{{ $row->id }}">{{ $row->code }}</option>
                                                        @endforeach
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
                                    <div class="row">
                                        <div class="col s12">
                                            <table id="datatable_serverside">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="2">#</th>
                                                        <th rowspan="2">Code</th>
                                                        <th rowspan="2">Pengguna</th>
                                                        <th rowspan="2">Sup/Ven</th>
                                                        <th rowspan="2">Cabang</th>
                                                        <th rowspan="2">Pabrik</th>
                                                        <th rowspan="2">Departemen</th>
                                                        <th colspan="3" class="center-align">Tanggal</th>
                                                        <th rowspan="2">Tipe</th>
                                                        <th colspan="2" class="center-align">Mata Uang</th>
                                                        <th rowspan="2">Dokumen</th>
                                                        <th rowspan="2">Keterangan</th>
                                                        <th rowspan="2">Subtotal</th>
                                                        <th colspan="2" class="center-align">Diskon</th>
                                                        <th rowspan="2">Total</th>
                                                        <th rowspan="2">Pajak</th>
                                                        <th rowspan="2">Grandtotal</th>
                                                        <th rowspan="2">Downpayment</th>
                                                        <th rowspan="2">Balance</th>
                                                        <th rowspan="2">Status</th>
                                                        <th rowspan="2">Action</th>
                                                    </tr>
                                                    <tr>
                                                        <th>Post</th>
                                                        <th>Tenggat</th>
                                                        <th>Dokumen</th>
                                                        <th>Kode</th>
                                                        <th>Konversi</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>Add/Edit {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="row">
                            <div class="input-field col m3 s12">
                                <input type="hidden" id="temp" name="temp">
                                <select class="browser-default" id="account_id" name="account_id" onchange="getGoodReceiptLandedCost(this.value);"></select>
                                <label class="active" for="account_id">Supplier / Vendor</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="type" name="type">
                                    <option value="1">Cash</option>
                                    <option value="2">Credit</option>
                                </select>
                                <label class="" for="type">Tipe</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="branch_id" name="branch_id">
                                    <option value="">--Kosong--</option>
                                    @foreach ($branch as $rowbranch)
                                        <option value="{{ $rowbranch->id }}" {{ $rowbranch->id == session('bo_branch_id') ? 'selected' : '' }}>{{ $rowbranch->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="branch_id">Cabang</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="plant_id" name="plant_id">
                                    <option value="">--Kosong--</option>
                                    @foreach ($plant as $rowplant)
                                        <option value="{{ $rowplant->id }}" {{ $rowplant->id == session('bo_plant_id') ? 'selected' : '' }}>{{ $rowplant->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="plant_id">Pabrik</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="department_id" name="department_id">
                                    <option value="">--Kosong--</option>
                                    @foreach ($department as $rowdepartment)
                                        <option value="{{ $rowdepartment->id }}" {{ $rowdepartment->id == session('bo_department_id') ? 'selected' : '' }}>{{ $rowdepartment->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="department_id">Departemen</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="post_date" name="post_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                <label class="active" for="post_date">Tgl. Posting</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="due_date" name="due_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. Kadaluarsa">
                                <label class="active" for="due_date">Tgl. Kadaluarsa</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="document_date" name="document_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. dokumen">
                                <label class="active" for="document_date">Tgl. Dokumen</label>
                            </div>
                            <div class="file-field input-field col m3 s12">
                                <div class="btn">
                                    <span>Lampiran</span>
                                    <input type="file" name="document" id="document">
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text">
                                </div>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="currency_id" name="currency_id">
                                    @foreach ($currency as $row)
                                        <option value="{{ $row->id }}">{{ $row->code.' '.$row->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="currency_id">Mata Uang</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="currency_rate" name="currency_rate" type="text" value="1" onkeyup="formatRupiah(this)">
                                <label class="active" for="currency_rate">Konversi</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="deposit" name="deposit" type="text" value="0,000" onkeyup="formatRupiah(this);" readonly>
                                <label class="active" for="deposit">Supp/Ven. Sisa Deposit <i>(Autofill)</i></label>
                            </div>
                            <div class="col m12 s12">
                                <p class="mt-2 mb-2">
                                    <h4>Detail Good Receipt / Landed Cost</h4>
                                    <div style="overflow:auto;">
                                        <table class="bordered">
                                            <thead>
                                                <tr>
                                                    <th class="center">
                                                        <label>
                                                            <input type="checkbox" onclick="chooseAll(this)">
                                                            <span>Semua</span>
                                                        </label>
                                                    </th>
                                                    <th class="center">GR/LC No.</th>
                                                    <th class="center">Tgl.Post</th>
                                                    <th class="center">Tgl.Tenggat</th>
                                                    <th class="center">Total</th>
                                                    <th class="center">Pajak</th>
                                                    <th class="center">Grandtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-detail">
                                                <tr id="empty-detail">
                                                    <td colspan="7" class="center">
                                                        Pilih supplier/vendor untuk memulai...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </p>
                            </div>
                            <div class="input-field col m4 s12">
                                <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note">Keterangan</label>
                            </div>
                            <div class="input-field col m4 s12">

                            </div>
                            <div class="input-field col m4 s12">
                                <table width="100%" class="bordered">
                                    <thead>
                                        {{-- <tr>
                                            <td width="50%">Subtotal</td>
                                            <td width="50%" class="right-align">
                                                <input class="browser-default" id="subtotal" name="subtotal" type="text" value="0,000" onkeyup="formatRupiah(this);" style="text-align:right;width:100%;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Discount</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="discount" name="discount" type="text" value="0,000" onkeyup="formatRupiah(this);" style="text-align:right;width:100%;">
                                            </td>
                                        </tr> --}}
                                        <tr>
                                            <td>Total</td>
                                            <td class="right-align"><span id="total">0,000</span></td>
                                        </tr>
                                        <tr>
                                            <td>Pajak</td>
                                            <td class="right-align"><span id="tax">0,000</span></td>
                                        </tr>
                                        <tr>
                                            <td>Grandtotal</td>
                                            <td class="right-align"><span id="grandtotal">0,000</span></td>
                                        </tr>
                                        <tr>
                                            <td>Uang Muka</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="downpayment" name="downpayment" type="text" value="0,000" onkeyup="formatRupiah(this);" style="text-align:right;width:100%;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Sisa</td>
                                            <td class="right-align"><span id="balance">0,000</span></td>
                                        </tr>
                                    </thead>
                                </table>
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

        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                $('#post_date').attr('min','{{ date("Y-m-d") }}');
                $('#due_date').attr('min','{{ date("Y-m-d") }}');
                $('#document_date').attr('min','{{ date("Y-m-d") }}');
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
                $('.row_purchase').each(function(){
                    $(this).remove();
                });
                M.updateTextFields();
                $('#body-detail').empty().append(`
                    <tr id="empty-detail">
                        <td colspan="7" class="center">
                            Pilih supplier/vendor untuk memulai...
                        </td>
                    </tr>
                `);
                $('#account_id').empty();
                $('#total,#tax,#grandtotal,#balance').text('0,000');
                $('#subtotal,#discount,#downpayment').val('0,000');
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

        select2ServerSide('#account_id,#filter_account', '{{ url("admin/select2/supplier_vendor") }}');
    });

    function getGoodReceiptLandedCost(val){
        if(val){
            $.ajax({
                url: '{{ Request::url() }}/get_gr_lc',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: val
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    loadingClose('.modal-content');

                    $('#deposit').val(response.deposit);
                    
                    if(response.details.length > 0){
                        $('#body-detail').empty();
                        $.each(response.details, function(i, val) {
                            var count = makeid(10);
                            $('#body-detail').append(`
                                <tr class="row_detail">
                                    <input type="hidden" name="arr_type" value="` + val.type + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_total" value="` + val.total + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_tax" value="` + val.tax + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_grandtotal" value="` + val.grandtotal + `" data-id="` + count + `">
                                    <td class="center-align">
                                        <label>
                                            <input type="checkbox" id="check` + count + `" name="arr_code[]" value="` + val.code + `" onclick="countAll();" data-id="` + count + `">
                                            <span>Pilih</span>
                                        </label>
                                    </td>
                                    <td>
                                        ` + val.rawcode + `
                                    </td>
                                    <td class="center">
                                        ` + val.post_date + `
                                    </td>
                                    <td class="center">
                                        ` + val.due_date + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.total + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.tax + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.grandtotal + `
                                    </td>
                                </tr>
                            `);
                        });
                    }
                    
                    countAll();
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
            $('#body-detail').empty().append(`
                <tr id="empty-detail">
                    <td colspan="7" class="center">
                        Pilih supplier/vendor untuk memulai...
                    </td>
                </tr>
            `);
        }
    }

    function countAll(){
        var total = 0, tax = 0, grandtotal = 0, downpayment = parseFloat($('#downpayment').val().replaceAll(".", "").replaceAll(",",".")), balance = 0;
        
        if($('input[name^="arr_code"]').length > 0){
            $('input[name^="arr_code"]').each(function(){
                let element = $(this);
                if($(element).is(':checked')){
                    ada = true;
                    total += parseFloat($('input[name^="arr_code"][id=' +  + ']' + element.data('id')).val().replaceAll(".", "").replaceAll(",","."));
                }
            });
        }

        $('input[name^="arr_total"]').each(function(){
            total += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
        });

        $('input[name^="arr_tax"]').each(function(){
            tax += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
        });

        $('input[name^="arr_total"]').each(function(){
            grandtotal += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
        });

        balance = grandtotal - downpayment;

        $('#total').text(formatRupiahIni(total.toFixed(3).toString().replace('.',',')));
        $('#tax').text(formatRupiahIni(tax.toFixed(3).toString().replace('.',',')));
        $('#grandtotal').text(formatRupiahIni(grandtotal.toFixed(3).toString().replace('.',',')));
        $('#balance').text(formatRupiahIni(balance.toFixed(3).toString().replace('.',',')));
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
        countAll();
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
                    type : $('#filter_type').val(),
                    'account_id[]' : $('#filter_account').val(),
                    branch_id : $('#filter_branch').val(),
                    plant_id : $('#filter_plant').val(),
                    department_id : $('#filter_department').val(),
                    'currency_id[]' : $('#filter_currency').val(),
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
                { name: 'branch_id', className: 'center-align' },
                { name: 'plant_id', className: 'center-align' },
                { name: 'department_id', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'due_date', className: 'center-align' },
                { name: 'document_date', className: 'center-align' },
                { name: 'type', className: 'center-align' },
                { name: 'currency_id', className: 'center-align' },
                { name: 'currency_rate', className: 'center-align' },
                { name: 'document', searchable: false, orderable: false, className: 'center-align' },
                { name: 'note', className: 'center-align' },
                { name: 'subtotal', className: 'right-align' },
                { name: 'percent_discount', className: 'right-align' },
                { name: 'nominal_discount', className: 'right-align' },
                { name: 'total', className: 'right-align' },
                { name: 'tax', className: 'right-align' },
                { name: 'grandtotal', className: 'right-align' },
                { name: 'downpayment', className: 'right-align' },
                { name: 'balance', className: 'right-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ]
        });
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

                formData.delete("arr_code[]");
                formData.delete("arr_nominal[]");
                formData.delete("arr_note[]");

                $('input[name^="arr_code"]').each(function(){
                    if($(this).is(':checked')){
                        formData.append('arr_code[]',$(this).val());
                        formData.append('arr_nominal[]',$('#rowNominal' + $(this).data('id')).val());
                        formData.append('arr_note[]',$('#rowNote' + $(this).data('id')).val());
                    }
                });

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
        });
    }

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
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
                $('#supplier_id').empty();
                $('#supplier_id').append(`
                    <option value="` + response.account_id + `">` + response.supplier_name + `</option>
                `);
                $('#type').val(response.type).formSelect();
                $('#branch_id').val(response.branch_id).formSelect();
                $('#plant_id').val(response.plant_id).formSelect();
                $('#department_id').val(response.department_id).formSelect();
                $('#currency_id').val(response.currency_id).formSelect();
                $('#currency_rate').val(response.currency_rate);
                $('#post_date').val(response.post_date);
                $('#due_date').val(response.due_date);
                $('#document_date').val(response.document_date);
                $('#percent_tax').val(response.percent_tax);
                
                if(response.is_tax == '1'){
                    $('#is_tax').prop( "checked", true);
                }else{
                    $('#is_tax').prop( "checked", false);
                }

                if(response.is_include_tax == '1'){
                    $('#is_include_tax').prop( "checked", true);
                }else{
                    $('#is_include_tax').prop( "checked", false);
                }
                
                $('#note').val(response.note);
                $('#grandtotal').text(response.grandtotal);
                $('#total').text(response.total);
                $('#tax').text(response.tax);
                $('#subtotal').val(response.subtotal);
                $('#discount').text(response.discount);
                
                if(response.details.length > 0){
                    $('#body-purchase').empty();
                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        $('#body-purchase').append(`
                            <tr class="row_purchase">
                                <td class="center-align">
                                    <label>
                                        <input type="checkbox" checked id="check` + count + `" name="arr_code[]" value="` + val.purchase_order_encrypt + `" onclick="" data-id="` + count + `">
                                        <span>Pilih</span>
                                    </label>
                                </td>
                                <td>
                                    ` + val.purchase_order_code + `
                                </td>
                                <td>
                                    ` + val.purchase_request_code + `
                                </td>
                                <td class="center">
                                    ` + val.post_date + `
                                </td>
                                <td class="center">
                                    ` + val.delivery_date + `
                                </td>
                                <td class="center">
                                    <input name="arr_note[]" class="browser-default" type="text" value="` + val.note + `" style="width:100%;" id="rowNote` + count + `">
                                </td>
                                <td class="center">
                                    ` + val.total + `
                                </td>
                                <td class="center">
                                    <input name="arr_nominal[]" class="browser-default" type="text" value="` + val.total_dp + `" onkeyup="formatRupiah(this);" style="text-align:right;width:100%;" id="rowNominal` + count + `">
                                </td>
                            </tr>
                        `);
                    });
                }
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

    function printData(){
        var search = window.table.search(), status = $('#filter_status').val(), type = $('#filter_type').val(), branch = $('#filter_branch').val(), plant = $('#filter_plant').val(), department = $('#filter_department').val(), supplier = $('#filter_supplier').val(), currency = $('#filter_currency').val(), is_tax = $('#filter_is_tax').val(), is_include_tax = $('#filter_is_include_ppn').val();
        
        $.ajax({
            type : "POST",
            url  : '{{ Request::url() }}/print',
            data : {
                search : search,
                status : status,
                type : type,
                branch : branch,
                plant : plant,
                department : department,
                is_tax : is_tax,
                is_include_tax : is_include_tax,
                'supplier[]' : supplier,
                'currency[]' : currency
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
        var search = window.table.search(), status = $('#filter_status').val(), type = $('#filter_type').val(), branch = $('#filter_branch').val(), plant = $('#filter_plant').val(), department = $('#filter_department').val(), supplier = $('#filter_supplier').val(), currency = $('#filter_currency').val(), is_tax = $('#filter_is_tax').val(), is_include_tax = $('#filter_is_include_ppn').val();
        
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status + "&type=" + type + "&branch=" + branch + "&plant=" + plant + "&department=" + department + "&is_tax=" + is_tax + "&is_include_tax=" + is_include_tax + "&supplier=" + supplier + "&currency=" + currency;
    }
</script>