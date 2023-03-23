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
                                                <label for="filter_is_tax" style="font-size:1rem;">Ber-PPN? :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_is_tax" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        <option value="1">Ya</option>
                                                        <option value="2">Tidak</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_is_include_ppn" style="font-size:1rem;">Termasuk PPN? :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_is_include_ppn" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        <option value="1">Ya</option>
                                                        <option value="0">Tidak</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_vendor" style="font-size:1rem;">Vendor/Ekspedisi :</label>
                                                <div class="input-field">
                                                    <select class="browser-default" id="filter_vendor" name="filter_vendor" multiple="multiple" style="width:100% !important;" onchange="loadDataTable()"></select>
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
                                                        <th rowspan="2">Vendor</th>
                                                        <th rowspan="2">PO No.</th>
                                                        <th rowspan="2">GR No.</th>
                                                        <th rowspan="2">Pabrik/Kantor</th>
                                                        <th colspan="2" class="center">Tanggal</th>
                                                        <th rowspan="2">No. Referensi</th>
                                                        <th colspan="2" class="center">Mata Uang</th>
                                                        <th colspan="3" class="center">Pajak</th>
                                                        <th rowspan="2">Keterangan</th>
                                                        <th rowspan="2">Dokumen</th>
                                                        <th rowspan="2">Total</th>
                                                        <th rowspan="2">Pajak</th>
                                                        <th rowspan="2">Grandtotal</th>
                                                        <th rowspan="2">Status</th>
                                                        <th rowspan="2">Action</th>
                                                    </tr>
                                                    <tr>
                                                        <th>Post</th>
                                                        <th>Tenggat</th>
                                                        <th>Kode</th>
                                                        <th>Konversi</th>
                                                        <th>Ya/Tidak</th>
                                                        <th>Termasuk</th>
                                                        <th>Prosentase</th>
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
                                <select class="browser-default" id="good_receipt_id" name="good_receipt_id" onchange="getGoodReceipt(this.value)"></select>
                                <label class="active" for="good_receipt_id">Penerimaan PO / Good Receipt</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="browser-default" id="vendor_id" name="vendor_id"></select>
                                <label class="active" for="vendor_id">Vendor/Ekspedisi</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="reference" name="reference" type="text" placeholder="No. Referensi">
                                <label class="active" for="reference">No. Referensi</label>
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
                                <input id="post_date" name="post_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                <label class="active" for="post_date">Tgl. Posting</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="due_date" name="due_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. tenggat">
                                <label class="active" for="due_date">Tgl. Tenggat</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <div class="switch mb-1">
                                    <label class="active" for="is_tax">Ber-PPN?</label>
                                    <label>
                                        Tidak
                                        <input type="checkbox" id="is_tax" name="is_tax" value="1" onclick="countEach();">
                                        <span class="lever"></span>
                                        Ya
                                    </label>
                                </div>
                            </div>
                            <div class="input-field col m3 s12">
                                <div class="switch mb-1">
                                    <label class="active" for="is_include_tax">Termasuk Pajak?</label>
                                    <label>
                                        Tidak
                                        <input type="checkbox" id="is_include_tax" name="is_include_tax" value="1" onclick="countEach();">
                                        <span class="lever"></span>
                                        Ya
                                    </label>
                                </div>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="percent_tax" name="percent_tax" type="text" value="0" onkeyup="formatRupiah(this);countEach();">
                                <label class="active" for="percent_tax">Prosentase Tax</label>
                            </div>
                            <div class="col m12 s12">
                                <h4>Nominal</h4>
                                <div class="row">
                                    <div class="input-field col m3 s12">
                                        <input id="total" name="total" type="text" value="0,000" onkeyup="formatRupiah(this);countEach();">
                                        <label class="active" for="total">Total (Sebelum Pajak) <i>*Isi disini</i></label>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <input id="tax" name="tax" type="text" value="0,000" onkeyup="formatRupiah(this);" readonly>
                                        <label class="active" for="tax">Pajak</label>
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <input id="grandtotal" name="grandtotal" type="text" value="0,000" onkeyup="formatRupiah(this);" readonly>
                                        <label class="active" for="grandtotal">Grandtotal</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col m12 s12">
                                <p class="mt-2 mb-2">
                                    <h4>Detail Harga per Produk</h4>
                                    <h6 class="center">Perhitungan otomatis akan didasarkan pada satuan stok/produksi (UOM) dan dihitung berdasarkan harga landed cost sebelum pajak. Silahkan masukkan nilai *Total*. Anda juga bisa memasukkan data nominal per barang secara langsung.</h6>
                                    <div style="overflow:auto;">
                                        <table class="bordered">
                                            <thead>
                                                <tr>
                                                    <th class="center">Item</th>
                                                    <th class="center">Qty</th>
                                                    <th class="center">Satuan (UOM)</th>
                                                    <th class="center">Harga Total</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-item">
                                                <tr id="last-row-item">
                                                    <td colspan="4" class="center">
                                                        Silahkan pilih penerimaan barang / good receipt po...
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
    var arrQty = [];
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
                $('#required_date').attr('min','{{ date("Y-m-d") }}');
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
                $('.row_item').each(function(){
                    $(this).remove();
                });
                $('#body-item').append(`
                    <tr id="last-row-item">
                        <td colspan="4" class="center">
                            Silahkan pilih penerimaan barang / good receipt po...
                        </td>
                    </tr>
                `);
                M.updateTextFields();
                $('#total,#tax,#grandtotal').text('0,000');
                $('#good_receipt_id').empty();
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

        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
            countAll();
        });

        select2ServerSide('#vendor_id,#filter_vendor', '{{ url("admin/select2/vendor") }}');
        select2ServerSide('#good_receipt_id', '{{ url("admin/select2/good_receipt") }}');
    });

    function getGoodReceipt(val){
        if(val){
            $.ajax({
                url: '{{ Request::url() }}/get_good_receipt',
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
                    
                    if(response.status == 500){
                        swal({
                            title: 'Ups!',
                            text: response.message,
                            icon: 'warning'
                        });
                        $('#purchase_request_id').empty();
                    }else{
                        if(response.length > 0){

                            $('.row_item').each(function(){
                                $(this).remove();
                            });

                            arrQty = [];

                            $('#last-row-item').remove();

                            $.each(response, function(i, val) {
                                arrQty.push(val.qtyRaw);
                                var count = makeid(10);
                                $('#body-item').append(`
                                    <tr class="row_item">
                                        <input type="hidden" name="arr_item[]" value="` + val.item_id + `">
                                        <input type="hidden" name="arr_qty[]" value="` + val.qtyRaw + `">
                                        <td>
                                        ` + val.item_name + ` 
                                        </td>
                                        <td class="center">
                                            ` + val.qty + `
                                        </td>
                                        <td class="center">
                                            ` + val.unit + `
                                        </td>
                                        <td class="center">
                                            <input name="arr_price[]" class="browser-default nominalitem" type="text" value="0" onkeyup="formatRupiah(this);countRow();" style="text-align:right;width:100% !important;" id="rowPrice`+ count +`">
                                        </td>
                                    </tr>
                                `);
                            });
                        }
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
            $('.row_item').each(function(){
                $(this).remove();
            });
            $('#body-item').append(`
                <tr id="last-row-item">
                    <td colspan="4" class="center">
                        Silahkan pilih penerimaan barang / good receipt po...
                    </td>
                </tr>
            `);
            arrQty = [];
        }
    }

    function countRow(){
        var tax = 0, percent_tax = parseFloat($('#percent_tax').val().replaceAll(".", "").replaceAll(",",".")), grandtotal = 0, total = 0;

        if($('.nominalitem').length > 0){
            $('input[name^="arr_price"]').each(function(){
                total += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
            });
        }

        if($('#is_tax').is(':checked')){
            if($('#is_include_tax').is(':checked')){
                total = total / (1 + (percent_tax / 100));
            }
            tax = total * (percent_tax / 100);
        }

        grandtotal = total + tax;
        
        $('#total').val(
            (tax >= 0 ? '' : '-') + formatRupiahIni(total.toFixed(3).toString().replace('.',','))
        );
        $('#tax').val(
            (tax >= 0 ? '' : '-') + formatRupiahIni(tax.toFixed(3).toString().replace('.',','))
        );
        $('#grandtotal').val(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(grandtotal.toFixed(3).toString().replace('.',','))
        );
    }

    function countEach(){
        
        var tax = 0, percent_tax = parseFloat($('#percent_tax').val().replaceAll(".", "").replaceAll(",",".")), grandtotal = 0, total = parseFloat($('#total').val().replaceAll(".", "").replaceAll(",","."));

        if($('#is_tax').is(':checked')){
            if($('#is_include_tax').is(':checked')){
                total = total / (1 + (percent_tax / 100));
            }
            tax = total * (percent_tax / 100);
        }

        grandtotal = total + tax;
        
        $('#tax').val(
            (tax >= 0 ? '' : '-') + formatRupiahIni(tax.toFixed(3).toString().replace('.',','))
        );
        $('#grandtotal').val(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(grandtotal.toFixed(3).toString().replace('.',','))
        );

        if($('.nominalitem').length > 0){
            if(arrQty.length > 0){
                let totalqty = 0;
                for(let i=0;i<arrQty.length;i++){
                    totalqty += parseFloat(arrQty[i]);
                }

                for(let i=0;i<arrQty.length;i++){
                    let totalrow = (parseFloat(arrQty[i]) / totalqty) * total;
                    $('input[name^="arr_price"]:eq(' + i + ')').val(formatRupiahIni(totalrow.toFixed(3).toString().replace('.',',')));
                }
            }
        }
    }

    function changeDateMinimum(val){
        if(val){
            $('#due_date,#document_date,#required_date').attr("min",val);
            $('input[name^="arr_required_date"]').each(function(){
                $(this).attr("min",val);
            });
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
                    is_tax : $('#filter_is_tax').val(),
                    is_include_tax : $('#filter_is_include_tax').val(),
                    'vendor_id[]' : $('#filter_vendor').val(),
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
                { name: 'vendor_id', className: 'center-align' },
                { name: 'purchase_order_id', className: 'center-align' },
                { name: 'good_receipt_id', className: 'center-align' },
                { name: 'place_id', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'due_date', className: 'center-align' },
                { name: 'no_reference', className: 'center-align' },
                { name: 'currency_id', className: 'center-align' },
                { name: 'currency_rate', className: 'center-align' },
                { name: 'is_tax', className: 'center-align' },
                { name: 'is_include_tax', className: 'center-align' },
                { name: 'percent_tax', className: 'center-align' },
                { name: 'note', className: 'center-align' },
                { name: 'document', className: 'center-align' },
                { name: 'total', className: 'right-align' },
                { name: 'tax', className: 'right-align' },
                { name: 'grandtotal', className: 'right-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle' /* or colvis */
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
                $('#good_receipt_id').empty();
                $('#good_receipt_id').append(`
                    <option value="` + response.good_receipt_id + `">` + response.good_receipt_note + `</option>
                `);
                $('#vendor_id').empty();
                $('#vendor_id').append(`
                    <option value="` + response.account_id + `">` + response.vendor_name + `</option>
                `);
                $('#reference').val(response.reference);
                $('#currency_id').val(response.currency_id).formSelect();
                $('#currency_rate').val(response.currency_rate);
                $('#post_date').val(response.post_date);
                $('#due_date').val(response.due_date);
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
                $('#total').val(response.total);
                $('#tax').val(response.tax);
                $('#grandtotal').val(response.grandtotal);
                
                if(response.details.length > 0){
                    $('#last-row-item').remove();

                    $('.row_item').each(function(){
                        $(this).remove();
                    });

                    arrQty = [];

                    $.each(response.details, function(i, val) {
                        arrQty.push(val.qtyRaw);
                        var count = makeid(10);
                        $('#body-item').append(`
                            <tr class="row_item">
                                <input type="hidden" name="arr_item[]" value="` + val.item_id + `">
                                <input type="hidden" name="arr_qty[]" value="` + val.qtyRaw + `">
                                <td>
                                    ` + val.item_name + ` 
                                </td>
                                <td class="center">
                                    ` + val.qty + `
                                </td>
                                <td class="center">
                                    ` + val.unit + `
                                </td>
                                <td class="center">
                                    <input name="arr_price[]" class="browser-default nominalitem" type="text" value="` + val.nominal + `" onkeyup="formatRupiah(this);countRow();" style="text-align:right;width:100% !important;" id="rowPrice`+ count +`">
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

    function countAll(){
        var subtotal = 0, tax = 0, percent_tax = parseFloat($('#percent_tax').val().replaceAll(".", "").replaceAll(",",".")), discount = parseFloat($('#discount').val().replaceAll(".", "").replaceAll(",",".")), total = 0, grandtotal = 0;

        $('.arr_subtotal').each(function(){
			subtotal += parseFloat($(this).text().replaceAll(".", "").replaceAll(",","."));
		});

        total = subtotal - discount;

        if($('#is_tax').is(':checked')){
            if($('#is_include_tax').is(':checked')){
                total = total / (1 + (percent_tax / 100));
            }
            tax = total * (percent_tax / 100);
        }

        grandtotal = total + tax;
        
        $('#subtotal').text(
            (subtotal >= 0 ? '' : '-') + formatRupiahIni(subtotal.toFixed(3).toString().replace('.',','))
        );
        $('#total').text(
            (total >= 0 ? '' : '-') + formatRupiahIni(total.toFixed(3).toString().replace('.',','))
        );
        $('#tax').text(
            (tax >= 0 ? '' : '-') + formatRupiahIni(tax.toFixed(3).toString().replace('.',','))
        );
        $('#grandtotal').text(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(grandtotal.toFixed(3).toString().replace('.',','))
        );
    }

    function printData(){
        var search = window.table.search(), status = $('#filter_status').val(), is_tax = $('#filter_is_tax').val(), is_include_tax = $('#filter_is_include_ppn').val(), vendor = $('#filter_vendor').val(), currency = $('#filter_currency').val();
        
        $.ajax({
            type : "POST",
            url  : '{{ Request::url() }}/print',
            data : {
                search : search,
                status : status,
                is_tax : is_tax,
                is_include_tax : is_include_tax,
                'vendor_id[]' : vendor,
                'currency_id[]' : currency
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
        var search = window.table.search(), status = $('#filter_status').val(), is_tax = $('#filter_is_tax').val(), is_include_tax = $('#filter_is_include_ppn').val(), vendor = $('#filter_vendor').val(), currency = $('#filter_currency').val();
        
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status + "&is_tax=" + is_tax + "&is_include_tax=" + is_include_tax + "&vendor=" + vendor + "&currency=" + currency;
    }
</script>