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
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                            <i class="material-icons hide-on-med-and-up">refresh</i>
                            <span class="hide-on-small-onl">Refresh</span>
                            <i class="material-icons right">refresh</i>
                        </a>
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="printData();">
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
                                                <label for="start_date" style="font-size:1rem;">Start Date (Tanggal Mulai) :</label>
                                                <div class="input-field col s12">
                                                <input type="date" id="start_date" name="start_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="finish_date" style="font-size:1rem;">End Date (Tanggal Berhenti) :</label>
                                                <div class="input-field col s12">
                                                    <input type="date" id="finish_date" name="finish_date"  onchange="loadDataTable()">
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
                                    </h4>
                                    <div class="row mt-2">
                                        <div class="col s12">
                                            <div id="datatable_buttons"></div>
                                            <table id="datatable_serverside" class="display responsive-table wrap">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="2">#</th>
                                                        <th rowspan="2">Pengguna</th>
                                                        <th rowspan="2">Supplier/Vendor</th>
                                                        <th rowspan="2">Perusahaan</th>
                                                        <th rowspan="2">Code</th>
                                                        <th rowspan="2">Penerima</th>
                                                        <th colspan="3" class="center-align">Tanggal</th>
                                                        <th rowspan="2">Keterangan</th>
                                                        <th rowspan="2">No.Surat Jalan</th>
                                                        <th rowspan="2">Dokumen</th>
                                                        <th rowspan="2">Status</th>
                                                        <th rowspan="2">Operasi</th>
                                                    </tr>
                                                    <tr>
                                                        <th>Pengajuan</th>
                                                        <th>Kadaluwarsa</th>
                                                        <th>Dokumen</th>
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
                            <div class="input-field col m3 s12">
                                <input type="hidden" id="temp" name="temp">
                                <select class="browser-default" id="account_id" name="account_id" onchange="/* getPurchaseOrderAll(this.value); */"></select>
                                <label class="active" for="account_id">Supplier</label>
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
                                <input id="receiver_name" name="receiver_name" type="text" placeholder="Nama Penerima">
                                <label class="active" for="receiver_name">Nama Penerima</label>
                            </div>
                            
                            <div class="input-field col m3 s12">
                                <input id="post_date" name="post_date" min="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. diterima" value="{{ date('Y-m-d') }}">
                                <label class="active" for="post_date">Tgl. Diterima</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="due_date" name="due_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. tenggat">
                                <label class="active" for="due_date">Tgl. Tenggat</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="document_date" name="document_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. dokumen">
                                <label class="active" for="document_date">Tgl. Dokumen</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="delivery_no" name="delivery_no" type="text" placeholder="No. Surat Jalan">
                                <label class="active" for="delivery_no">No. Surat Jalan</label>
                            </div>
                            <div class="file-field input-field col m3 s12">
                                <div class="btn">
                                    <span>Lampiran Bukti</span>
                                    <input type="file" name="document" id="document">
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text">
                                </div>
                            </div>
                            <div class="col m12 s12">
                                <div class="col m6 s6">
                                    <p class="mt-2 mb-2">
                                        <h4>Purchase Order</h4>
                                        <div class="row">
                                            <div class="input-field col m6 s7">
                                                <select class="browser-default" id="purchase_order_id" name="purchase_order_id">&nbsp;</select>
                                            </div>
                                            <div class="col m6 s6 mt-4">
                                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="getPurchaseOrder();" href="javascript:void(0);">
                                                    <i class="material-icons left">add</i> Tambah PO
                                                </a>
                                            </div>
                                        </div>
                                    </p>
                                </div>
                                <div class="col m6 s6">
                                    <h6><b>PO Terpakai</b> (hapus untuk bisa diakses pengguna lain) : <i id="list-used-data"></i></h6>
                                </div>
                            </div>
                            <div class="col m12 s12">
                                <p class="mt-2 mb-2">
                                    <h4>Detail Produk</h4>
                                    <div style="overflow:auto;">
                                        <table class="bordered">
                                            <thead>
                                                <tr>
                                                    <th class="center">Item</th>
                                                    <th class="center">Qty</th>
                                                    <th class="center">Satuan</th>
                                                    <th class="center">Keterangan</th>
                                                    <th class="center">Remark</th>
                                                    <th class="center">Plant</th>
                                                    <th class="center">Line</th>
                                                    <th class="center">Mesin</th>
                                                    <th class="center">Departemen</th>
                                                    <th class="center">Gudang</th>
                                                    <th class="center">Hapus</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-item">
                                                <tr id="empty-item">
                                                    <td colspan="11" class="center">
                                                        Pilih purchase order untuk memulai...
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
    <div class="modal-content row">
        <div class="col s12" id="show_structure">
            <div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;">

            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal6" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row" >
            <div class="col m3 s12">
                
            </div>
            <div class="col m6 s12">
                <h4 id="title_data" style="text-align:center"></h4>
                <h5 id="code_data" style="text-align:center"></h5>
            </div>
            <div class="col m3 s12 right-align">
                <img src="{{ url('website/logo_web_fix.png') }}" width="40%" height="60%">
            </div>
        </div>
        <div class="divider mb-1 mt-2"></div>
        <div class="row">
            <div class="col" id="user_jurnal">
            </div>
            <div class="col" id="post_date_jurnal">
            </div>
            <div class="col" id="note_jurnal">
            </div>
            <div class="col" id="ref_jurnal">
            </div>
        </div>
        <div class="row mt-2">
            <table class="bordered Highlight striped">
                <thead>
                        <tr>
                            <th class="center-align">No</th>
                            <th class="center-align">Coa</th>
                            <th class="center-align">Perusahaan</th>
                            <th class="center-align">Bisnis Partner</th>
                            <th class="center-align">Plant</th>
                            <th class="center-align">Line</th>
                            <th class="center-align">Mesin</th>
                            <th class="center-align">Department</th>
                            <th class="center-align">Gudang</th>
                            <th class="center-align">Debit</th>
                            <th class="center-align">Kredit</th>
                        </tr>
                    
                </thead>
                <tbody id="body-journal-table">
                </tbody>
            </table>
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

        window.table.search('{{ $code }}').draw();

        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                $('#post_date').attr('min','{{ date("Y-m-d") }}');
                $('#due_date').attr('min','{{ date("Y-m-d") }}');
                $('#document_date').attr('min','{{ date("Y-m-d") }}');
            },
            onOpenEnd: function(modal, trigger) {
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
                $('.row_item').each(function(){
                    $(this).remove();
                });
                if($('#empty-item').length == 0){
                    $('#body-item').append(`
                        <tr id="empty-item">
                            <td colspan="11" class="center">
                                Pilih purchase order untuk memulai...
                            </td>
                        </tr>
                    `);
                }
                $('#purchase_order_id').empty();
                $('#account_id').empty();
                M.updateTextFields();
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }
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

        $('#modal6').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#title_data').empty();
                $('#code_data').empty();             
                $('#body-journal-table').empty();
                $('#user_jurnal').empty();
                $('#note_jurnal').empty();
                $('#ref_jurnal').empty();
                $('#post_date_jurnal').empty();
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

        select2ServerSide('#purchase_order_id', '{{ url("admin/select2/purchase_order") }}');
        select2ServerSide('#account_id', '{{ url("admin/select2/supplier") }}');

        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
            
            if($('.row_item').length == 0){
                $('#body-item').append(`
                    <tr id="empty-item">
                        <td colspan="11" class="center">
                            Pilih purchase order untuk memulai...
                        </td>
                    </tr>
                `);
                $('#purchase_order_id').empty();
            }
        });
    });

    function getPurchaseOrderAll(val){
        if(val){
            $.ajax({
                url: '{{ Request::url() }}/get_purchase_order_all',
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

                    if(response.length > 0){
                        $('#empty-item').remove();
                        $.each(response, function(i, valmain) {
                            $('#list-used-data').append(`
                                <div class="chip purple darken-4 gradient-shadow white-text">
                                    ` + valmain.code + `
                                    <i class="material-icons close data-used" onclick="removeUsedData('` + valmain.id + `')">close</i>
                                </div>
                            `);

                            $.each(valmain.details, function(i, val) {
                                var count = makeid(10);
                                $('#body-item').append(`
                                    <tr class="row_item" data-po="` + valmain.id + `">
                                        <input type="hidden" name="arr_item[]" value="` + val.item_id + `">
                                        <input type="hidden" name="arr_purchase[]" value="` + val.purchase_order_detail_id + `">
                                        <input type="hidden" name="arr_place[]" value="` + val.place_id + `">
                                        <input type="hidden" name="arr_department[]" value="` + val.department_id + `">
                                        <input type="hidden" name="arr_warehouse[]" value="` + val.warehouse_id + `">
                                        <td>
                                            ` + val.item_name + `
                                        </td>
                                        <td>
                                            <input name="arr_qty[]" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiah(this);" style="text-align:right;width:100px;">
                                        </td>
                                        <td class="center">
                                            <span>` + val.unit + `</span>
                                        </td>
                                        <td>
                                            <input name="arr_note[]" class="browser-default" type="text" placeholder="Keterangan..." value="` + valmain.code + `" style="width:100%;">
                                        </td>
                                        <td>
                                            <input name="arr_remark[]" class="browser-default" type="text" placeholder="Keterangan..." value="-" style="width:100%;">
                                        </td>
                                        <td class="center">
                                            <span>` + val.place_name + `</span>
                                        </td>
                                        <td class="center">
                                            <span>` + val.department_name + `</span>
                                        </td>
                                        <td class="center">
                                            <span>` + val.warehouse_name + `</span>
                                        </td>
                                        <td class="center">
                                            <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                                <i class="material-icons">delete</i>
                                            </a>
                                        </td>
                                    </tr>
                                `);
                            });
                        });
                    }
                    $('#purchase_order_id').empty();
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
            if($('.data-used').length > 0){
                $('.data-used').trigger('click');
            }
            $('.row_item').each(function(){
                $(this).remove();
            });
            if($('.row_item').length == 0 && $('#empty-item').length == 0){
                $('#body-item').append(`
                    <tr id="empty-item">
                        <td colspan="11" class="center">
                            Pilih purchase order untuk memulai...
                        </td>
                    </tr>
                `);
            }
        }
    }
    var nodeTemplate = function(data) {
        return `
            <div class="title">${data.name}</div>
            <div class="content">${data.title}<br>Tanggal ${data.date}<br> Nominal : ${data.grandtotal}<br></div>
        `;
    };

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
            success: function(response) {
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
                    start_date : $('#start_date').val(),
                    finish_date : $('#finish_date').val(),
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
                { name: 'account_id', className: 'center-align' },
                { name: 'company_id', className: 'center-align' },
                { name: 'code', className: 'center-align' },
                { name: 'receiver', className: 'center-align' },
                { name: 'date_post', className: 'center-align' },
                { name: 'date_due', className: 'center-align' },
                { name: 'date_doc', className: 'center-align' },
                { name: 'note', className: '' },
                { name: 'delivery_no', className: 'center-align' },
                { name: 'document', searchable: false, orderable: false, className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
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

                formData.delete("arr_department[]");
                formData.delete("arr_line[]");
                formData.delete("arr_machine[]");

                $('input[name^="arr_department"]').each(function(index){
                    formData.append('arr_department[]',($(this).val() ? $(this).val() : ''));
                });

                $('input[name^="arr_line"]').each(function(index){
                    formData.append('arr_line[]',($(this).val() ? $(this).val() : ''));
                });

                $('input[name^="arr_machine"]').each(function(index){
                    formData.append('arr_machine[]',($(this).val() ? $(this).val() : ''));
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

    function getPurchaseOrder(){
        let val = $('#purchase_order_id').val();

        if(val){
            $.ajax({
                url: '{{ Request::url() }}/get_purchase_order',
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
                        $('#purchase_order_id').empty();
                    }else{
                        if(response.details.length > 0){
                            $('#receiver_name').val(response.receiver_name);
                            $('#account_id').empty().append(`
                                <option value="` + response.account_id + `">` + response.account_name + `</option>
                            `);
                            $('#empty-item').remove();

                            $('#list-used-data').append(`
                                <div class="chip purple darken-4 gradient-shadow white-text">
                                    ` + response.code + `
                                    <i class="material-icons close data-used" onclick="removeUsedData('` + response.id + `')">close</i>
                                </div>
                            `);

                            $.each(response.details, function(i, val) {
                                var count = makeid(10);
                                $('#body-item').append(`
                                    <tr class="row_item" data-po="` + response.id + `">
                                        <input type="hidden" name="arr_item[]" value="` + val.item_id + `">
                                        <input type="hidden" name="arr_purchase[]" value="` + val.purchase_order_detail_id + `">
                                        <input type="hidden" name="arr_place[]" value="` + val.place_id + `">
                                        <input type="hidden" name="arr_line[]" value="` + val.line_id + `">
                                        <input type="hidden" name="arr_machine[]" value="` + val.machine_id + `">
                                        <input type="hidden" name="arr_department[]" value="` + val.department_id + `">
                                        <input type="hidden" name="arr_warehouse[]" value="` + val.warehouse_id + `">
                                        <td>
                                            ` + val.item_name + `
                                        </td>
                                        <td>
                                            <input name="arr_qty[]" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiah(this);" style="text-align:right;width:100px;">
                                        </td>
                                        <td class="center">
                                            <span>` + val.unit + `</span>
                                        </td>
                                        <td>
                                            <input name="arr_note[]" class="browser-default" type="text" placeholder="Keterangan..." value="` + response.code + `" style="width:100%;">
                                        </td>
                                        <td>
                                            <input name="arr_remark[]" class="browser-default" type="text" placeholder="Keterangan..." value="-" style="width:100%;">
                                        </td>
                                        <td class="center">
                                            <span>` + val.place_name + `</span>
                                        </td>
                                        <td class="center">
                                            <span>` + val.line_name + `</span>
                                        </td>
                                        <td class="center">
                                            <span>` + val.machine_name + `</span>
                                        </td>
                                        <td class="center">
                                            <span>` + val.department_name + `</span>
                                        </td>
                                        <td class="center">
                                            <span>` + val.warehouse_name + `</span>
                                        </td>
                                        <td class="center">
                                            <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                                <i class="material-icons">delete</i>
                                            </a>
                                        </td>
                                    </tr>
                                `);
                            });
                        }
                        $('#purchase_order_id').empty();
                        $('.modal-content').scrollTop(0);
                        M.updateTextFields();
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
            
            /* $('.row_item').each(function(){
                $(this).remove();
            });
            if($('.row_item').length == 0 && $('#empty-item').length == 0){
                $('#body-item').append(`
                    <tr id="empty-item">
                        <td colspan="11" class="center">
                            Pilih purchase order untuk memulai...
                        </td>
                    </tr>
                `);
            } */
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
                $('.row_item[data-po="' + id + '"]').remove();
                if($('.row_item').length == 0 && $('#empty-item').length == 0){
                    $('#body-item').append(`
                        <tr id="empty-item">
                            <td colspan="11" class="center">
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
                $('.row_item').each(function(){
                    $(this).remove();
                });
            },
            success: function(response) {
                loadingClose('#main');
                $('#modal1').modal('open');
                $('#temp').val(id);
                $('#account_id').empty().append(`
                    <option value="` + response.account_id + `">` + response.account_name + `</option>
                `);
                $('#company_id').val(response.company_id).formSelect();
                $('#note').val(response.note);
                $('#receiver_name').val(response.receiver_name);
                $('#post_date').val(response.post_date);
                $('#due_date').val(response.due_date);
                $('#document_date').val(response.document_date);
                $('#delivery_no').val(response.delivery_no);
                $('#post_date').removeAttr('min');
                $('#due_date').removeAttr('min');
                $('#document_date').removeAttr('min');
                
                if(response.details.length > 0){
                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        $('#body-item').append(`
                            <tr class="row_item">
                                <input type="hidden" name="arr_item[]" value="` + val.item_id + `">
                                <input type="hidden" name="arr_purchase[]" value="` + val.purchase_order_detail_id + `">
                                <input type="hidden" name="arr_place[]" value="` + val.place_id + `">
                                <input type="hidden" name="arr_line[]" value="` + val.line_id + `">
                                <input type="hidden" name="arr_machine[]" value="` + val.machine_id + `">
                                <input type="hidden" name="arr_department[]" value="` + val.department_id + `">
                                <input type="hidden" name="arr_warehouse[]" value="` + val.warehouse_id + `">
                                <td>
                                    ` + val.item_name + `
                                </td>
                                <td>
                                    <input name="arr_qty[]" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiah(this);" style="text-align:right;width:100px;">
                                </td>
                                <td class="center">
                                    <span>` + val.unit + `</span>
                                </td>
                                <td>
                                    <input name="arr_note[]" class="browser-default" type="text" placeholder="Keterangan..." value="` + val.note + `" style="width:100%;">
                                </td>
                                <td>
                                    <input name="arr_remark[]" class="browser-default" type="text" placeholder="Keterangan..." value="` + val.remark + `"  style="width:100%;">
                                </td>
                                <td class="center">
                                    <span>` + val.place_name + `</span>
                                </td>
                                <td class="center">
                                    <span>` + val.line_name + `</span>
                                </td>
                                <td class="center">
                                    <span>` + val.machine_name + `</span>
                                </td>
                                <td class="center">
                                    <span>` + val.department_name + `</span>
                                </td>
                                <td class="center">
                                    <span>` + val.warehouse_name + `</span>
                                </td>
                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        `);
                    });
                }

                $('#empty-item').remove();
                
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

    function viewJournal(id){
        $.ajax({
            url: '{{ Request::url() }}/view_journal/' + id,
            type:'GET',
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            complete: function() {
                
            },
            success: function(data){
                loadingClose('.modal-content');
                $('#modal6').modal('open');
                $('#title_data').append(``+data.title+``);
                $('#code_data').append(data.message.code);
                $('#body-journal-table').append(data.tbody);
                $('#user_jurnal').append(`Pengguna `+data.user);
                $('#note_jurnal').append(`Keterangan `+data.message.note);
                $('#ref_jurnal').append(`Referensi `+data.reference);
                $('#post_date_jurnal').append(`Tanggal `+data.message.post_date);
                
                


                console.log(data);
            }
        });
    }
</script>