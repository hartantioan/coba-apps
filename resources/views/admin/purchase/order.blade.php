<style>
    .modal {
        top:0px !important;
    }

    table > thead > tr > th {
        font-size: 13px !important;
    }

    #chart-container {
        position: relative;
        height: 420px;
        margin: 0.5rem;
        overflow: auto;
        text-align:-webkit-right;
    }
    .orgchart { background: #fff; }
    .select2-container {
        min-width:250px !important;
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
                                                <label for="filter_inventory" style="font-size:1rem;">Tipe Pembelian :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_inventory" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        <option value="1">Persediaan Barang</option>
                                                        <option value="2">Jasa</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_type" style="font-size:1rem;">Tipe PO :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_type" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        <option value="1">Standart PO</option>
                                                        <option value="2">Planned PO</option>
                                                        <option value="3">Blanked PO</option>
                                                        <option value="4">Contract PO</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_shipping" style="font-size:1rem;">Tipe Pengiriman :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_shipping" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        <option value="1">Franco</option>
                                                        <option value="2">Loco</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_company" style="font-size:1rem;">Perusahaan :</label>
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
                                                <label for="filter_payment" style="font-size:1rem;">Tipe Pembayaran :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_payment" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        <option value="1">Cash</option>
                                                        <option value="2">Credit</option>
                                                        <option value="3">CBD</option>
                                                        <option value="4">DP</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="filter_supplier" style="font-size:1rem;">Supplier :</label>
                                                <div class="input-field">
                                                    <select class="browser-default" id="filter_supplier" name="filter_supplier" multiple="multiple" style="width:100% !important;" onchange="loadDataTable()"></select>
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
                                            <div id="datatable_buttons"></div>
                                            <table id="datatable_serverside">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="2">#</th>
                                                        <th rowspan="2">Code</th>
                                                        <th rowspan="2">Pengguna</th>
                                                        <th rowspan="2">Supplier</th>
                                                        <th rowspan="2">Tipe PO</th>
                                                        <th rowspan="2">Jenis PO</th>
                                                        <th rowspan="2">Pengiriman</th>
                                                        <th rowspan="2">Perusahaan</th>
                                                        <th colspan="2" class="center">Proforma</th>
                                                        <th colspan="2" class="center">Pembayaran</th>
                                                        <th colspan="2" class="center">Mata Uang</th>
                                                        <th colspan="2" class="center">Tanggal</th>
                                                        <th colspan="3" class="center">Penerima</th>
                                                        <th rowspan="2">Keterangan</th>
                                                        <th rowspan="2">Subtotal</th>
                                                        <th rowspan="2">Diskon</th>
                                                        <th rowspan="2">Total</th>
                                                        <th rowspan="2">PPN</th>
                                                        <th rowspan="2">PPH</th>
                                                        <th rowspan="2">Grandtotal</th>
                                                        <th rowspan="2">Status</th>
                                                        <th rowspan="2">Action</th>
                                                    </tr>
                                                    <tr>
                                                        <th>Nomor</th>
                                                        <th>Dokumen</th>
                                                        <th>Tipe</th>
                                                        <th>Termin</th>
                                                        <th>Kode</th>
                                                        <th>Konversi</th>
                                                        <th>Post</th>
                                                        <th>Kirim</th>
                                                        <th>Nama</th>
                                                        <th>Alamat</th>
                                                        <th>Telepon</th>
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
    <div class="modal-content" style="overflow-x: hidden;max-width: 100%;">
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
                                <input type="hidden" id="savesubtotal" name="savesubtotal" value="0,00">
                                <input type="hidden" id="savetotal" name="savetotal" value="0,00">
                                <input type="hidden" id="savetax" name="savetax" value="0,00">
                                <input type="hidden" id="savewtax" name="savewtax" value="0,00">
                                <input type="hidden" id="savegrandtotal" name="savegrandtotal" value="0,00">
                                <select class="browser-default" id="supplier_id" name="supplier_id" onchange="getTopSupplier();"></select>
                                <label class="active" for="supplier_id">Supplier</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="inventory_type" name="inventory_type">
                                    <option value="1">Persediaan Barang</option>
                                    <option value="2">Jasa</option>
                                </select>
                                <label class="" for="inventory_type">Tipe Pembelian</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="purchasing_type" name="purchasing_type">
                                    <option value="1">Standart PO</option>
                                    <option value="2">Planned PO</option>
                                    <option value="3">Blanked PO</option>
                                    <option value="4">Contract PO</option>
                                </select>
                                <label class="" for="purchasing_type">Kategori PO</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="shipping_type" name="shipping_type">
                                    <option value="1">Franco</option>
                                    <option value="2">Loco</option>
                                </select>
                                <label class="" for="shipping_type">Tipe Pengiriman</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="company_id" name="company_id">
                                    @foreach ($company as $rowcompany)
                                        <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="company_id">Perusahaan</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="document_no" name="document_no" type="text" placeholder="No. Dokumen">
                                <label class="active" for="document_no">No. Dokumen</label>
                            </div>
                            <div class="file-field input-field col m3 s12">
                                <div class="btn">
                                    <span>Dokumen PO</span>
                                    <input type="file" name="document_po" id="document_po">
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text">
                                </div>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="payment_type" name="payment_type">
                                    <option value="1">Cash</option>
                                    <option value="2">Credit</option>
                                    <option value="3">CBD</option>
                                    <option value="4">DP</option>
                                </select>
                                <label class="" for="payment_type">Tipe Pembayaran</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="payment_term" name="payment_term" type="number" value="0" min="0" step="1">
                                <label class="active" for="payment_term">Termin Pembayaran (hari)</label>
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
                                <input id="post_date" name="post_date" min="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                <label class="active" for="post_date">Tgl. Posting</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="delivery_date" name="delivery_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. kirim">
                                <label class="active" for="due_date">Tgl. Kirim</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="receiver_name" name="receiver_name" type="text" placeholder="Nama Penerima">
                                <label class="active" for="receiver_name">Nama Penerima</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="receiver_address" name="receiver_address" type="text" placeholder="Alamat Penerima">
                                <label class="active" for="receiver_address">Alamat Penerima</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="receiver_phone" name="receiver_phone" type="text" placeholder="Kontak Penerima">
                                <label class="active" for="receiver_phone">Kontak Penerima</label>
                            </div>
                            <div class="col m12 s12">
                                <div class="col m6 s6">
                                    <p class="mt-2 mb-2">
                                        <h4>Purchase Request</h4>
                                        <div class="row">
                                            <div class="input-field col m6 s6">
                                                <select class="browser-default" id="purchase_request_id" name="purchase_request_id"></select>
                                                <label class="active" for="purchase_request_id">Purchase Request (Jika ada)</label>
                                            </div>
                                            <div class="col m6 s6 mt-4">
                                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="getPurchaseRequest();" href="javascript:void(0);">
                                                    <i class="material-icons left">add</i> Tambah PR
                                                </a>
                                            </div>
                                        </div>
                                    </p>
                                </div>
                                <div class="col m6 s6">
                                    <h6><b>PR Terpakai</b> (hapus untuk bisa diakses pengguna lain) : <i id="list-used-data"></i></h6>
                                </div>
                            </div>
                            <div class="col m12 s12" style="overflow:auto;width:100% !important;">
                                <p class="mt-2 mb-2">
                                    <h4>Detail Produk</h4>
                                    <table class="bordered" style="width:2500px;">
                                        <thead>
                                            <tr>
                                                <th class="center">Item / Coa Jasa</th>
                                                <th class="center">Qty</th>
                                                <th class="center">Satuan</th>
                                                <th class="center">Harga</th>
                                                <th class="center">PPN</th>
                                                <th class="center">Termasuk PPN</th>
                                                <th class="center">PPH</th>
                                                <th class="center">Disc1(%)</th>
                                                <th class="center">Disc2(%)</th>
                                                <th class="center">Disc3(Rp)</th>
                                                <th class="center">Subtotal</th>
                                                <th class="center">Keterangan</th>
                                                <th class="center">Site</th>
                                                <th class="center">Departemen</th>
                                                <th class="center">Gudang</th>
                                                <th class="center">Hapus</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-item">
                                            <tr id="last-row-item">
                                                <td colspan="16" class="center">
                                                    <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addItem()" href="javascript:void(0);">
                                                        <i class="material-icons left">add</i> New Item
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
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
                                        <tr>
                                            <td width="50%">Subtotal</td>
                                            <td width="50%" class="right-align"><span id="subtotal">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>Discount</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="discount" name="discount" type="text" value="0" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100%;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Total</td>
                                            <td class="right-align"><span id="total">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>PPN</td>
                                            <td class="right-align"><span id="tax">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>PPH</td>
                                            <td class="right-align"><span id="wtax">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>Grandtotal</td>
                                            <td class="right-align"><span id="grandtotal">0,00</span></td>
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

<div id="modal3" class="modal modal-fixed-footer" style="max-height: 75% !important;height: 75% !important;width:75%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="show_structure">
                <div id="chart-container"></div>
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
        var chartContainer = $('#chart-container');

        chartContainer.on('click', '.node', function(event) {
            
            window.open($(this).data('nodeData').url);

        });
        
        
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
                $('#required_date').attr('min','{{ date("Y-m-d") }}');
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
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                $('#savesubtotal,#savetotal,#savetax,#savewtax,#savegrandtotal').val('0,00');
                $('.row_item').each(function(){
                    $(this).remove();
                });
                M.updateTextFields();
                $('#subtotal,#total,#tax,#grandtotal,#wtax').text('0,00');
                $('#purchase_request_id').empty();
                $('#list-used-data').empty();
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

        $('#modal3').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#chart-container').empty();
            }
        });
        
        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
            countAll();
        });

        select2ServerSide('#supplier_id,#filter_supplier', '{{ url("admin/select2/supplier") }}');
        select2ServerSide('#purchase_request_id', '{{ url("admin/select2/purchase_request") }}');
    });
    

    function makeTreeOrg(data){
        $('#chart-container').orgchart({
            'nodeContent': 'title',
            'data': data,
            'direction': 'r2l',
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
                makeTreeOrg(response.message);
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
    
    function getRowUnit(val){
        if($("#arr_item" + val).val()){
            $('#arr_unit' + val).text($("#arr_item" + val).select2('data')[0].buy_unit);
        }else{
            $('#arr_unit' + val).text('-');
        }
    }

    function getTopSupplier(){
        if($("#supplier_id").val()){
            $('#payment_term').val($("#supplier_id").select2('data')[0].top);
        }else{
            $('#payment_term').val('0');
        }
    }

    function getPurchaseRequest(){
        let nil = $('#purchase_request_id').val();

        if(nil){
            $.ajax({
                url: '{{ Request::url() }}/get_purchase_request',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: nil
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
                        if(response.details.length > 0){
                            $('#list-used-data').append(`
                                <div class="chip purple darken-4 gradient-shadow white-text">
                                    ` + response.code + `
                                    <i class="material-icons close data-used" onclick="removeUsedData('` + response.id + `')">close</i>
                                </div>
                            `);
                            $.each(response.details, function(i, val) {
                                var count = makeid(10);
                                
                                $('#last-row-item').before(`
                                    <tr class="row_item" data-id="` + response.id + `">
                                        <input type="hidden" name="arr_purchase[]" value="` + val.purchase_request_detail_id + `">
                                        <td>
                                            <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                                        </td>
                                        <td>
                                            <input name="arr_qty[]" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowQty`+ count +`">
                                        </td>
                                        <td class="center">
                                            <span id="arr_unit` + count + `">` + val.unit + `</span>
                                        </td>
                                        <td class="center">
                                            <input name="arr_price[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowPrice`+ count +`">
                                        </td>
                                        <td>
                                            <select class="browser-default" id="arr_tax` + count + `" name="arr_tax[]" onchange="countAll();">
                                                <option value="0" data-id="0">-- Pilih ini jika non-PPN --</option>
                                                @foreach ($tax as $row)
                                                    <option value="{{ $row->percentage }}" {{ $row->is_default_ppn ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <label>
                                                <input type="checkbox" id="arr_is_include_tax` + count + `" name="arr_is_include_tax[]" value="1" onclick="countAll();">
                                                <span>Ya/Tidak</span>
                                            </label>
                                        </td>
                                        <td>
                                            <select class="browser-default" id="arr_wtax` + count + `" name="arr_wtax[]" onchange="countAll();">
                                                <option value="0" data-id="0">-- Pilih ini jika non-PPH --</option>
                                                @foreach ($wtax as $row)
                                                    <option value="{{ $row->percentage }}" {{ $row->is_default_pph ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name }}</option>
                                                @endforeach
                                            </select>
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
                                            <span id="arr_subtotal` + count + `" class="arr_subtotal">0</span>
                                        </td>
                                        <td>
                                            <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang ..." value="` + val.note + ` ` + response.code + ` ke Gudang ` + val.warehouse_name + `">
                                        </td>
                                        <td>
                                            <select class="form-control" id="arr_place` + count + `" name="arr_place[]">
                                                @foreach ($place as $rowplace)
                                                    <option value="{{ $rowplace->id }}">{{ $rowplace->name.' - '.$rowplace->company->name }}</option>
                                                @endforeach
                                            </select>    
                                        </td>
                                        <td>
                                            <select class="form-control" id="arr_department` + count + `" name="arr_department[]">
                                                @foreach ($department as $rowdept)
                                                    <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                                                @endforeach
                                            </select>    
                                        </td>
                                        <td>
                                            <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]"></select>
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
                                $('#arr_warehouse' + count).append(`
                                    <option value="` + val.warehouse_id + `">` + val.warehouse_name + `</option>
                                `);
                                select2ServerSide('#arr_item' + count, '{{ url("admin/select2/purchase_item") }}');
                                select2ServerSide('#arr_warehouse' + count, '{{ url("admin/select2/warehouse") }}');
                                $('#arr_place' + count).val(val.place_id).formSelect();
                                $('#arr_department' + count).val(val.department_id).formSelect();
                            });
                        }

                        $('#purchase_request_id').empty();
                    }
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
        }
    }

    function addItem(){
        var count = makeid(10);
        if($('#inventory_type').val() == '1'){
            $('#last-row-item').before(`
                <tr class="row_item">
                    <input type="hidden" name="arr_purchase[]" value="0">
                    <td>
                        <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                    </td>
                    <td>
                        <input name="arr_qty[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowQty`+ count +`">
                    </td>
                    <td class="center">
                        <span id="arr_unit` + count + `">-</span>
                    </td>
                    <td class="center">
                        <input name="arr_price[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowPrice`+ count +`">
                    </td>
                    <td>
                        <select class="browser-default" id="arr_tax` + count + `" name="arr_tax[]" onchange="countAll();">
                            <option value="0" data-id="0">-- Pilih ini jika non-PPN --</option>
                            @foreach ($tax as $row)
                                <option value="{{ $row->percentage }}" {{ $row->is_default_ppn ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <label>
                            <input type="checkbox" id="arr_is_include_tax` + count + `" name="arr_is_include_tax[]" value="1" onclick="countAll();">
                            <span>Ya/Tidak</span>
                        </label>
                    </td>
                    <td>
                        <select class="browser-default" id="arr_wtax` + count + `" name="arr_wtax[]" onchange="countAll();">
                            <option value="0" data-id="0">-- Pilih ini jika non-PPH --</option>
                            @foreach ($wtax as $row)
                                <option value="{{ $row->percentage }}" {{ $row->is_default_pph ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name }}</option>
                            @endforeach
                        </select>
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
                        <span id="arr_subtotal` + count + `" class="arr_subtotal">0</span>
                    </td>
                    <td>
                        <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang ...">
                    </td>
                    <td>
                        <select class="form-control" id="arr_place` + count + `" name="arr_place[]">
                            @foreach ($place as $rowplace)
                                <option value="{{ $rowplace->id }}">{{ $rowplace->name.' - '.$rowplace->company->name }}</option>
                            @endforeach
                        </select>    
                    </td>
                    <td>
                        <select class="form-control" id="arr_department` + count + `" name="arr_department[]">
                            @foreach ($department as $rowdept)
                                <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                            @endforeach
                        </select>    
                    </td>
                    <td>
                        <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]"></select>
                    </td>
                    <td class="center">
                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                            <i class="material-icons">delete</i>
                        </a>
                    </td>
                </tr>
            `);
            select2ServerSide('#arr_item' + count, '{{ url("admin/select2/purchase_item") }}');
            select2ServerSide('#arr_warehouse' + count, '{{ url("admin/select2/warehouse") }}');
            $('#arr_place' + count + ',#arr_department' + count).formSelect();
            
        }else if($('#inventory_type').val() == '2'){

            $('#last-row-item').before(`
                <tr class="row_item">
                    <input type="hidden" name="arr_purchase[]" value="0">
                    <td>
                        <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]"></select>
                    </td>
                    <td>
                        <input name="arr_qty[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowQty`+ count +`">
                    </td>
                    <td class="center">
                        <span id="arr_unit` + count + `">-</span>
                    </td>
                    <td class="center">
                        <input name="arr_price[]" class="browser-default" type="text" value="0" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowPrice`+ count +`">
                    </td>
                    <td>
                        <select class="browser-default" id="arr_tax` + count + `" name="arr_tax[]" onchange="countAll();">
                            <option value="0" data-id="0">-- Pilih ini jika non-PPN --</option>
                            @foreach ($tax as $row)
                                <option value="{{ $row->percentage }}" {{ $row->is_default_ppn ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <label>
                            <input type="checkbox" id="arr_is_include_tax` + count + `" name="arr_is_include_tax[]" value="1" onclick="countAll();">
                            <span>Ya/Tidak</span>
                        </label>
                    </td>
                    <td>
                        <select class="browser-default" id="arr_wtax` + count + `" name="arr_wtax[]" onchange="countAll();">
                            <option value="0" data-id="0">-- Pilih ini jika non-PPH --</option>
                            @foreach ($wtax as $row)
                                <option value="{{ $row->percentage }}" {{ $row->is_default_pph ? 'selected' : '' }} data-id="{{ $row->id }}">{{ $row->name }}</option>
                            @endforeach
                        </select>
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
                        <span id="arr_subtotal` + count + `" class="arr_subtotal">0</span>
                    </td>
                    <td>
                        <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang ...">
                    </td>
                    <td>
                        <select class="form-control" id="arr_place` + count + `" name="arr_place[]">
                            @foreach ($place as $rowplace)
                                <option value="{{ $rowplace->id }}">{{ $rowplace->name.' - '.$rowplace->company->name }}</option>
                            @endforeach
                        </select>    
                    </td>
                    <td>
                        <select class="form-control" id="arr_department` + count + `" name="arr_department[]">
                            @foreach ($department as $rowdept)
                                <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                            @endforeach
                        </select>    
                    </td>
                    <td class="center">
                        -
                    </td>
                    <td class="center">
                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                            <i class="material-icons">delete</i>
                        </a>
                    </td>
                </tr>
            `);
            $('#arr_place' + count + ',#arr_department' + count).formSelect();
            select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
        }
    }

    function changeDateMinimum(val){
        if(val){
            $('#due_date,#required_date').attr("min",val);
            $('input[name^="arr_required_date"]').each(function(){
                $(this).attr("min",val);
            });
        }
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
                $('.row_item[data-id="' + id + '"]').remove();
                countAll();
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
                    inventory_type : $('#filter_inventory').val(),
                    purchasing_type : $('#filter_type').val(),
                    shipping_type : $('#filter_shipping').val(),
                    'supplier_id[]' : $('#filter_supplier').val(),
                    company_id : $('#filter_company').val(),
                    is_tax : $('#filter_is_tax').val(),
                    is_include_tax : $('#filter_is_include_tax').val(),
                    payment_type : $('#filter_payment').val(),
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
                { name: 'supplier_id', className: 'center-align' },
                { name: 'inventory_type', className: 'center-align' },
                { name: 'purchasing_type', className: 'center-align' },
                { name: 'shipping_type', className: 'center-align' },
                { name: 'company_id', className: 'center-align' },
                { name: 'document_no', className: 'center-align' },
                { name: 'document_po', className: 'center-align' },
                { name: 'payment_tye', className: 'center-align' },
                { name: 'payment_term', className: 'center-align' },
                { name: 'currency_id', className: 'center-align' },
                { name: 'currency_rate', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'delivery_date', className: 'center-align' },
                { name: 'receiver_name', className: 'center-align' },
                { name: 'receiver_address', className: 'center-align' },
                { name: 'receiver_phone', className: 'center-align' },
                { name: 'note', className: 'center-align' },
                { name: 'subtotal', className: 'right-align' },
                { name: 'discount', className: 'right-align' },
                { name: 'total', className: 'right-align' },
                { name: 'tax', className: 'right-align' },
                { name: 'wtax', className: 'right-align' },
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

                formData.delete("arr_tax[]");
                formData.delete("arr_is_include_tax[]");
                formData.delete("arr_wtax[]");

                $('select[name^="arr_tax"]').each(function(index){
                    formData.append('arr_tax[]',$(this).val());
                    formData.append('arr_tax_id[]',$('option:selected',this).data('id'));
                    formData.append('arr_wtax_id[]',$('select[name^="arr_wtax"]').eq(index).find(':selected').data('id'));
                    formData.append('arr_is_include_tax[]',($('input[name^="arr_is_include_tax"]').eq(index).is(':checked') ? '1' : '0'));
                    formData.append('arr_wtax[]',$('select[name^="arr_wtax"]').eq(index).val());
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
                $('#inventory_type').val(response.inventory_type).formSelect();
                $('#purchasing_type').val(response.purchasing_type).formSelect();
                $('#shipping_type').val(response.shipping_type).formSelect();
                $('#company_id').val(response.company_id).formSelect();
                $('#document_no').val(response.document_no);
                $('#payment_type').val(response.payment_type).formSelect();
                $('#payment_term').val(response.payment_term);
                $('#currency_id').val(response.currency_id).formSelect();
                $('#currency_rate').val(response.currency_rate);
                $('#post_date').val(response.post_date);
                $('#delivery_date').val(response.delivery_date);
                $('#percent_tax').val(response.percent_tax);
                $('#receiver_name').val(response.receiver_name);
                $('#receiver_address').val(response.receiver_address);
                $('#receiver_phone').val(response.receiver_phone);
                
                $('#note').val(response.note);
                $('#subtotal').text(response.subtotal);
                $('#savesubtotal').val(response.subtotal);
                $('#discount').val(response.discount);
                $('#total').text(response.total);
                $('#savetotal').val(response.total);
                $('#tax').text(response.tax);
                $('#savetax').val(response.tax);
                $('#wtax').text(response.wtax);
                $('#savewtax').val(response.wtax);
                $('#grandtotal').text(response.grandtotal);
                $('#savegrandtotal').val(response.grandtotal);
                
                if(response.details.length > 0){
                    $('.row_item').each(function(){
                        $(this).remove();
                    });

                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        if(response.inventory_type == '1'){
                            $('#last-row-item').before(`
                                <tr class="row_item">
                                    <input type="hidden" name="arr_purchase[]" value="` + val.purchase_request_detail_id + `">
                                    <td>
                                        <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                                    </td>
                                    <td>
                                        <input name="arr_qty[]" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowQty`+ count +`">
                                    </td>
                                    <td class="center">
                                        <span id="arr_unit` + count + `">` + val.unit + `</span>
                                    </td>
                                    <td class="center">
                                        <input name="arr_price[]" class="browser-default" type="text" value="` + val.price + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowPrice`+ count +`">
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_tax` + count + `" name="arr_tax[]" onchange="countAll();">
                                            <option value="0" data-id="0">-- Pilih ini jika non-PPN --</option>
                                            @foreach ($tax as $row)
                                                <option value="{{ $row->percentage }}" data-id="{{ $row->id }}">{{ $row->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <label>
                                            <input type="checkbox" id="arr_is_include_tax` + count + `" name="arr_is_include_tax[]" value="1" onclick="countAll();">
                                            <span>Ya/Tidak</span>
                                        </label>
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_wtax` + count + `" name="arr_wtax[]" onchange="countAll();">
                                            <option value="0" data-id="0">-- Pilih ini jika non-PPH --</option>
                                            @foreach ($wtax as $row)
                                                <option value="{{ $row->percentage }}" data-id="{{ $row->id }}">{{ $row->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="center">
                                        <input name="arr_disc1[]" class="browser-default" type="text" value="` + val.disc1 + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc1`+ count +`">
                                    </td>
                                    <td class="center">
                                        <input name="arr_disc2[]" class="browser-default" type="text" value="` + val.disc2 + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc2`+ count +`">
                                    </td>
                                    <td class="center">
                                        <input name="arr_disc3[]" class="browser-default" type="text" value="` + val.disc3 + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowDisc3`+ count +`">
                                    </td>
                                    <td class="center">
                                        <span id="arr_subtotal` + count + `" class="arr_subtotal">` + val.subtotal + `</span>
                                    </td>
                                    <td>
                                        <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang ..." value="` + val.note + `">
                                    </td>
                                    <td>
                                        <select class="form-control" id="arr_place` + count + `" name="arr_place[]">
                                            @foreach ($place as $rowplace)
                                                <option value="{{ $rowplace->id }}">{{ $rowplace->name.' - '.$rowplace->company->name }}</option>
                                            @endforeach
                                        </select>    
                                    </td>
                                    <td>
                                        <select class="form-control" id="arr_department` + count + `" name="arr_department[]">
                                            @foreach ($department as $rowdept)
                                                <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                                            @endforeach
                                        </select>    
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_warehouse` + count + `" name="arr_warehouse[]"></select>
                                    </td>
                                    <td class="center">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);" onclick="removeUsedData('` + val.id + `')">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                </tr>
                            `);
                            $('#arr_item' + count).append(`
                                <option value="` + val.item_id + `">` + val.item_name + `</option>
                            `);
                            select2ServerSide('#arr_item' + count, '{{ url("admin/select2/purchase_item") }}');
                            if(val.is_include_tax){
                                $('#arr_is_include_tax' + count).prop( "checked", true);
                            }
                            
                            $('#arr_warehouse' + count).append(`
                                <option value="` + val.warehouse_id + `">` + val.warehouse_name + `</option>
                            `);
                            select2ServerSide('#arr_warehouse' + count, '{{ url("admin/select2/warehouse") }}');
                            $('#arr_place' + count).val(val.place_id).formSelect();
                            $('#arr_department' + count).val(val.department_id).formSelect();
                            $("#arr_tax" + count + " option[data-id='" + val.tax_id + "']").prop("selected",true);
                            $("#arr_wtax" + count + " option[data-id='" + val.wtax_id + "']").prop("selected",true);

                        }else if(response.inventory_type == '2'){

                            $('#last-row-item').before(`
                                <tr class="row_item">
                                    <input type="hidden" name="arr_purchase[]" value="` + val.purchase_request_detail_id + `">
                                    <td>
                                        <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]"></select>
                                    </td>
                                    <td>
                                        <input name="arr_qty[]" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowQty`+ count +`">
                                    </td>
                                    <td class="center">
                                        <span id="arr_unit` + count + `">` + val.unit + `</span>
                                    </td>
                                    <td class="center">
                                        <input name="arr_price[]" class="browser-default" type="text" value="` + val.price + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowPrice`+ count +`">
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_tax` + count + `" name="arr_tax[]" onchange="countAll();">
                                            <option value="0" data-id="0">-- Pilih ini jika non-PPN --</option>
                                            @foreach ($tax as $row)
                                                <option value="{{ $row->percentage }}" data-id="{{ $row->id }}">{{ $row->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <label>
                                            <input type="checkbox" id="arr_is_include_tax` + count + `" name="arr_is_include_tax[]" value="1" onclick="countAll();">
                                            <span>Ya/Tidak</span>
                                        </label>
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_wtax` + count + `" name="arr_wtax[]" onchange="countAll();">
                                            <option value="0" data-id="0">-- Pilih ini jika non-PPH --</option>
                                            @foreach ($wtax as $row)
                                                <option value="{{ $row->percentage }}" data-id="{{ $row->id }}">{{ $row->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="center">
                                        <input name="arr_disc1[]" class="browser-default" type="text" value="` + val.disc1 + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc1`+ count +`">
                                    </td>
                                    <td class="center">
                                        <input name="arr_disc2[]" class="browser-default" type="text" value="` + val.disc2 + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;width:100px;" id="rowDisc2`+ count +`">
                                    </td>
                                    <td class="center">
                                        <input name="arr_disc3[]" class="browser-default" type="text" value="` + val.disc3 + `" onkeyup="formatRupiah(this);countRow('` + count + `')" style="text-align:right;" id="rowDisc3`+ count +`">
                                    </td>
                                    <td class="center">
                                        <span id="arr_subtotal` + count + `" class="arr_subtotal">` + val.subtotal + `</span>
                                    </td>
                                    <td>
                                        <input name="arr_note[]" class="materialize-textarea" type="text" placeholder="Keterangan barang ..." value="` + val.note + `">
                                    </td>
                                    <td>
                                        <select class="form-control" id="arr_place` + count + `" name="arr_place[]">
                                            @foreach ($place as $rowplace)
                                                <option value="{{ $rowplace->id }}">{{ $rowplace->name.' - '.$rowplace->company->name }}</option>
                                            @endforeach
                                        </select>    
                                    </td>
                                    <td>
                                        <select class="form-control" id="arr_department` + count + `" name="arr_department[]">
                                            @foreach ($department as $rowdept)
                                                <option value="{{ $rowdept->id }}">{{ $rowdept->name }}</option>
                                            @endforeach
                                        </select>    
                                    </td>
                                    <td class="center">
                                        -
                                    </td>
                                    <td class="center">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                </tr>
                            `);
                            if(val.is_include_tax){
                                $('#arr_is_include_tax' + count).prop( "checked", true);
                            }
                            
                            $('#arr_coa' + count).append(`
                                <option value="` + val.coa_id + `">` + val.coa_name + `</option>
                            `);
                            select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
                            $('#arr_place' + count).val(val.place_id).formSelect();
                            $('#arr_department' + count).val(val.department_id).formSelect();
                            $("#arr_tax" + count + " option[data-id='" + val.tax_id + "']").prop("selected",true);
                            $("#arr_wtax" + count + " option[data-id='" + val.wtax_id + "']").prop("selected",true);
                        }
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

    function countRow(id){
        var qty = parseFloat($('#rowQty' + id).val().replaceAll(".", "").replaceAll(",",".")), price = parseFloat($('#rowPrice' + id).val().replaceAll(".", "").replaceAll(",",".")), disc1 = parseFloat($('#rowDisc1' + id).val().replaceAll(".", "").replaceAll(",",".")), disc2 = parseFloat($('#rowDisc2' + id).val().replaceAll(".", "").replaceAll(",",".")), disc3 = parseFloat($('#rowDisc3' + id).val().replaceAll(".", "").replaceAll(",","."));

        var finalpricedisc1 = price - (price * (disc1 / 100));
        var finalpricedisc2 = finalpricedisc1 - (finalpricedisc1 * (disc2 / 100));
        var finalpricedisc3 = finalpricedisc2 - disc3;

        if((finalpricedisc3 * qty).toFixed(2) >= 0){
            $('#arr_subtotal' + id).text(formatRupiahIni((finalpricedisc3 * qty).toFixed(2).toString().replace('.',',')));
        }else{
            $('#arr_subtotal' + id).text('-' + formatRupiahIni((finalpricedisc3 * qty).toFixed(2).toString().replace('.',',')));
        }

        countAll();
    }

    function countAll(){
        var subtotal = 0, tax = 0, discount = parseFloat($('#discount').val().replaceAll(".", "").replaceAll(",",".")), total = 0, grandtotal = 0, wtax = 0;

        $('.arr_subtotal').each(function(index){
			subtotal += parseFloat($(this).text().replaceAll(".", "").replaceAll(",","."));
		});

        $('.arr_subtotal').each(function(index){
            let rownominal = parseFloat($(this).text().replaceAll(".", "").replaceAll(",",".")), rowtax = 0, rowwtax = 0, rowbobot = 0, rowdiscount = 0;
            rowbobot = rownominal / subtotal;
            rowdiscount = discount * rowbobot;
            rownominal -= rowdiscount;

            if($('select[name^="arr_tax"]').eq(index).val() !== '0'){
                let percent_tax = parseFloat($('select[name^="arr_tax"]').eq(index).val());
                if($('input[name^="arr_is_include_tax"]').eq(index).is(':checked')){
                    rownominal = rownominal / (1 + (percent_tax / 100));
                }

                rowtax = rownominal * (percent_tax / 100);
            }

            if($('select[name^="arr_wtax"]').eq(index).val() !== '0'){
                let percent_wtax = parseFloat($('select[name^="arr_wtax"]').eq(index).val());
                rowwtax = rownominal * (percent_wtax / 100);
            }
            
            tax += rowtax;
            wtax += rowwtax;
            total += rownominal;
            grandtotal += rownominal + rowtax - rowwtax;
        });

        $('#subtotal').text(
            (subtotal >= 0 ? '' : '-') + formatRupiahIni(subtotal.toFixed(2).toString().replace('.',','))
        );
        $('#savesubtotal').val(
            (subtotal >= 0 ? '' : '-') + formatRupiahIni(subtotal.toFixed(2).toString().replace('.',','))
        );
        $('#total').text(
            (total >= 0 ? '' : '-') + formatRupiahIni(total.toFixed(2).toString().replace('.',','))
        );
        $('#savetotal').val(
            (total >= 0 ? '' : '-') + formatRupiahIni(total.toFixed(2).toString().replace('.',','))
        );
        $('#tax').text(
            (tax >= 0 ? '' : '-') + formatRupiahIni(tax.toFixed(2).toString().replace('.',','))
        );
        $('#savetax').val(
            (tax >= 0 ? '' : '-') + formatRupiahIni(tax.toFixed(2).toString().replace('.',','))
        );
        $('#wtax').text(
            (wtax >= 0 ? '' : '-') + formatRupiahIni(wtax.toFixed(2).toString().replace('.',','))
        );
        $('#savewtax').val(
            (wtax >= 0 ? '' : '-') + formatRupiahIni(wtax.toFixed(2).toString().replace('.',','))
        );
        $('#grandtotal').text(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(grandtotal.toFixed(2).toString().replace('.',','))
        );
        $('#savegrandtotal').val(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(grandtotal.toFixed(2).toString().replace('.',','))
        );
    }

    function printData(){
        var search = window.table.search(), status = $('#filter_status').val(), inventory = $('#filter_inventory').val(), type = $('#filter_type').val(), shipping = $('#filter_shipping').val(), company = $('#filter_company').val(), payment = $('#filter_payment').val(), supplier = $('#filter_supplier').val(), currency = $('#filter_currency').val();
        
        $.ajax({
            type : "POST",
            url  : '{{ Request::url() }}/print',
            data : {
                search : search,
                status : status,
                inventory : inventory,
                type : type,
                shipping : shipping,
                company : company,
                payment : payment,
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
        var search = window.table.search(), status = $('#filter_status').val(), inventory = $('#filter_inventory').val(), type = $('#filter_type').val(), shipping = $('#filter_shipping').val(), company = $('#filter_company').val(), payment = $('#filter_payment').val(), supplier = $('#filter_supplier').val(), currency = $('#filter_currency').val();
        
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status + "&inventory=" + inventory + "&type=" + type + "&shipping=" + shipping + "&company=" + company + "&payment=" + payment + "&supplier=" + supplier + "&currency=" + currency;
    }
</script>