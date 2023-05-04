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
                                                <label for="filter_account" style="font-size:1rem;">Supplier/Vendor :</label>
                                                <div class="input-field">
                                                    <select class="browser-default" id="filter_account" name="filter_account" multiple="multiple" style="width:100% !important;" onchange="loadDataTable()"></select>
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
                                                        <th rowspan="2">Sup/Ven</th>
                                                        <th rowspan="2">Perusahaan</th>
                                                        <th colspan="4" class="center-align">Tanggal</th>
                                                        <th rowspan="2">Tipe</th>
                                                        <th rowspan="2">Dokumen</th>
                                                        <th rowspan="2">Keterangan</th>
                                                        <th rowspan="2">No.Faktur Pajak</th>
                                                        <th rowspan="2">No.Bukti Potong</th>
                                                        <th rowspan="2">Tgl.Bukti Potong</th>
                                                        <th rowspan="2">No.SPK</th>
                                                        <th rowspan="2">No.Invoice</th>
                                                        <th rowspan="2">Subtotal</th>
                                                        <th colspan="2" class="center-align">Diskon</th>
                                                        <th rowspan="2">Total</th>
                                                        <th rowspan="2">PPN</th>
                                                        <th rowspan="2">PPH</th>
                                                        <th rowspan="2">Grandtotal</th>
                                                        <th rowspan="2">Downpayment</th>
                                                        <th rowspan="2">Balance</th>
                                                        <th rowspan="2">Status</th>
                                                        <th rowspan="2">Action</th>
                                                    </tr>
                                                    <tr>
                                                        <th>Post</th>
                                                        <th>Terima</th>
                                                        <th>Tenggat</th>
                                                        <th>Dokumen</th>
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
                                <select class="browser-default" id="account_id" name="account_id" onchange="getGrLcPo(this.value);"></select>
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
                                <select class="form-control" id="company_id" name="company_id">
                                    @foreach ($company as $rowcompany)
                                        <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="company_id">Perusahaan</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="post_date" name="post_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                <label class="active" for="post_date">Tgl. Posting</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="received_date" name="received_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. Terima" value="{{ date('Y-m-d') }}" onchange="addDays();">
                                <label class="active" for="received_date">Tgl. Terima</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="top" name="top" min="0" type="number" value="0" onchange="addDays();">
                                <label class="active" for="top">TOP (hari) Autofill dari GRPO</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="due_date" name="due_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. Kadaluarsa">
                                <label class="active" for="due_date">Tgl. Kadaluarsa</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="document_date" name="document_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. dokumen">
                                <label class="active" for="document_date">Tgl. Dokumen</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="tax_no" name="tax_no" type="text" placeholder="Nomor faktur pajak...">
                                <label class="active" for="tax_no">No. Faktur Pajak</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="tax_cut_no" name="tax_cut_no" type="text" placeholder="Nomor bukti potong...">
                                <label class="active" for="tax_cut_no">No. Bukti Potong</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="cut_date" name="cut_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tgl. Bukti potong">
                                <label class="active" for="cut_date">Tgl. Bukti Potong</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="spk_no" name="spk_no" type="text" placeholder="Nomor SPK...">
                                <label class="active" for="spk_no">No. SPK</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="invoice_no" name="invoice_no" type="text" placeholder="Nomor Invoice dari Suppplier/Vendor">
                                <label class="active" for="invoice_no">No. Invoice</label>
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
                            <div class="col m12 s12">
                                <p class="mt-2 mb-2">
                                    <h6>Detail Good Receipt PO / Landed Cost / Purchase Order Jasa</h6>
                                    <div style="overflow:auto;">
                                        <table class="bordered" style="width:1600px !important;">
                                            <thead>
                                                <tr>
                                                    <th class="center">
                                                        <label>
                                                            <input type="checkbox" onclick="chooseAll(this)">
                                                            <span>Semua</span>
                                                        </label>
                                                    </th>
                                                    <th class="center">GR/LC/PO No.</th>
                                                    <th class="center">NO.PO</th>
                                                    <th class="center">No.SJ</th>
                                                    <th class="center">Item / Coa Jasa</th>
                                                    <th class="center">Tgl.Post</th>
                                                    <th class="center">Tgl.Tenggat</th>
                                                    <th class="center">Total</th>
                                                    <th class="center">PPN</th>
                                                    <th class="center">PPH</th>
                                                    <th class="center">Grandtotal</th>
                                                    <th class="center">Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-detail">
                                                <tr id="empty-detail">
                                                    <td colspan="12" class="center">
                                                        Pilih supplier/vendor untuk memulai...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </p>
                            </div>
                            <div class="col m12 s12">
                                <p class="mt-2 mb-2">
                                    <h5>Detail Down Payment Bisnis Partner</h5>
                                    <div style="overflow:auto;">
                                        <table class="bordered">
                                            <thead>
                                                <tr>
                                                    <th class="center">
                                                        <label>
                                                            <input type="checkbox" onclick="chooseAllDp(this)">
                                                            <span>Semua</span>
                                                        </label>
                                                    </th>
                                                    <th class="center">Purchase DP No.</th>
                                                    <th class="center">Tgl.Post</th>
                                                    <th class="center">Nominal</th>
                                                    <th class="center">Sisa</th>
                                                    <th class="center">Dipakai</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-detail-dp">
                                                <tr id="empty-detail-dp">
                                                    <td colspan="6" class="center">
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
                                            <td>Uang Muka</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="downpayment" name="downpayment" type="text" value="0,00" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100%;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Pembulatan</td>
                                            <td class="right-align">
                                                <input class="browser-default" id="rounding" name="rounding" type="text" value="0,00" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100%;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Grandtotal</td>
                                            <td class="right-align"><span id="balance">0,00</span></td>
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

<div id="modal3" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="col s12" id="show_structure">
            <div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;">

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
                window.onbeforeunload = function() {
                    return 'You will lose all changes made since your last save';
                };
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
                        <td colspan="12" class="center">
                            Pilih supplier/vendor untuk memulai...
                        </td>
                    </tr>
                `);
                $('#account_id').empty();
                $('#total,#tax,#wtax,#balance').text('0,00');
                $('#subtotal,#discount,#downpayment').val('0,00');
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
                $('#myDiagramDiv').remove();
                $('#show_structure').append(
                    `<div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;"></div>
                    `
                );
            }
        });

        select2ServerSide('#account_id,#filter_account', '{{ url("admin/select2/supplier_vendor") }}');
    });

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
            beforeSend: function() {
                
            },
            success: function(response) {
                loadingClose('.modal-content');
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

    function getGrLcPo(val){
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

                    $('#body-detail,#body-detail-dp').empty();
                    if(response.details.length > 0){
                        $.each(response.details, function(i, val) {
                            var count = makeid(10);
                            $('#body-detail').append(`
                                <tr class="row_detail">
                                    <input type="hidden" name="arr_type[]" value="` + val.type + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_total[]" value="` + val.total + `" data-id="` + count + `">
                                    <input type="hidden" name="arr_grandtotal[]" value="` + val.grandtotal + `" data-id="` + count + `">
                                    <td class="center-align">
                                        <label>
                                            <input type="checkbox" id="check` + count + `" name="arr_code[]" value="` + val.code + `" onclick="countAll();" data-id="` + count + `">
                                            <span>Pilih</span>
                                        </label>
                                    </td>
                                    <td class="center">
                                        ` + val.rawcode + `
                                    </td>
                                    <td class="center">
                                        ` + val.purchase_no + `
                                    </td>
                                    <td class="center">
                                        ` + val.delivery_no + `
                                    </td>
                                    <td class="">
                                        ` + val.list_item + `
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
                                    <td class="right-align" id="row_tax` + count + `">
                                        <input type="text" name="arr_tax[]" value="` + val.tax + `" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();">
                                    
                                    </td>
                                    <td class="right-align" id="row_wtax` + count + `">
                                        <input type="text" name="arr_wtax[]" value="` + val.wtax + `" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();">
                                    </td>
                                    <td class="right-align" id="row_grandtotal` + count + `">
                                        ` + val.grandtotal + `
                                    </td>
                                    <td>
                                        ` + val.info + `
                                    </td>
                                </tr>
                            `);

                            $('#top').val(val.top);
                        });                        
                    }else{
                        $('#body-detail').empty().append(`
                            <tr id="empty-detail">
                                <td colspan="12" class="center">
                                    Pilih supplier/vendor untuk memulai...
                                </td>
                            </tr>
                        `);

                        $('#total,#tax,#balance').text('0,00');
                    }

                    if(response.downpayments.length > 0){
                        $.each(response.downpayments, function(i, val) {
                            var count = makeid(10);
                            $('#body-detail-dp').append(`
                                <tr class="row_detail_dp">
                                    <td class="center-align">
                                        <label>
                                            <input type="checkbox" id="check` + count + `" name="arr_dp_code[]" value="` + val.code + `" onclick="countAll();" data-id="` + count + `">
                                            <span>Pilih</span>
                                        </label>
                                    </td>
                                    <td class="center">
                                        ` + val.rawcode + `
                                    </td>
                                    <td class="center">
                                        ` + val.post_date + `
                                    </td>
                                    <td class="center">
                                        ` + val.grandtotal + `
                                    </td>
                                    <td class="center">
                                        ` + val.balance + `
                                    </td>
                                    <td class="center">
                                        <input name="arr_nominal[]" class="browser-default" type="text" value="` + val.balance + `" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100% !important;" id="rowNominal`+ count +`">
                                    </td>
                                </tr>
                            `);
                        });                        
                    }else{
                        $('#body-detail-dp').empty().append(`
                            <tr id="empty-detail-dp">
                                <td colspan="6" class="center">
                                    Pilih supplier/vendor untuk memulai...
                                </td>
                            </tr>
                        `);

                        $('#downpayment').val('0,00');
                    }

                    addDays();
                    
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
                    <td colspan="12" class="center">
                        Pilih supplier/vendor untuk memulai...
                    </td>
                </tr>
            `);
            $('#top').val('0');
            $('#due_date').val('');
            $('#total,#tax,#wtax,#balance').text('0,00');
        }
    }

    function countAll(){
        var total = 0, tax = 0, grandtotal = 0, balance = 0, wtax = 0, downpayment = 0, rounding = parseFloat($('#rounding').val().replaceAll(".", "").replaceAll(",","."));
        
        if($('input[name^="arr_code"]').length > 0){
            $('input[name^="arr_code"]').each(function(){
                var rowgrandtotal = 0;
                let element = $(this);
                if($(element).is(':checked')){
                    total += parseFloat($('input[name^="arr_total"][data-id="' + element.data('id') + '"]').val().replaceAll(".", "").replaceAll(",","."));
                    tax += parseFloat($('input[name^="arr_tax"][data-id="' + element.data('id') + '"]').val().replaceAll(".", "").replaceAll(",","."));
                    wtax += parseFloat($('input[name^="arr_wtax"][data-id="' + element.data('id') + '"]').val().replaceAll(".", "").replaceAll(",","."));
                    rowgrandtotal = parseFloat($('input[name^="arr_total"][data-id="' + element.data('id') + '"]').val().replaceAll(".", "").replaceAll(",",".")) + parseFloat($('input[name^="arr_tax"][data-id="' + element.data('id') + '"]').val().replaceAll(".", "").replaceAll(",",".")) - parseFloat($('input[name^="arr_wtax"][data-id="' + element.data('id') + '"]').val().replaceAll(".", "").replaceAll(",","."));
                    grandtotal += rowgrandtotal;
                    $('input[name^="arr_grandtotal"][data-id="' + element.data('id') + '"]').val(formatRupiahIni(roundTwoDecimal(rowgrandtotal).toString().replace('.',',')));
                    $('#row_grandtotal' + element.data('id')).text(formatRupiahIni(roundTwoDecimal(rowgrandtotal).toString().replace('.',',')));
                }
            });
        }

        if($('input[name^="arr_dp_code"]').length > 0){
            $('input[name^="arr_dp_code"]').each(function(index){
                if($(this).is(':checked')){
                    downpayment += parseFloat($('input[name^="arr_nominal"]').eq(index).val().replaceAll(".", "").replaceAll(",","."));
                }
            });
        }

        balance = grandtotal - downpayment + rounding;

        $('#downpayment').val(formatRupiahIni(roundTwoDecimal(downpayment).toString().replace('.',',')));
        $('#total').text(formatRupiahIni(roundTwoDecimal(total).toString().replace('.',',')));
        $('#tax').text(formatRupiahIni(roundTwoDecimal(tax).toString().replace('.',',')));
        $('#wtax').text(formatRupiahIni(roundTwoDecimal(wtax).toString().replace('.',',')));
        $('#balance').text(
            (balance >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(balance).toString().replace('.',','))
        );
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

    function chooseAllDp(element){
        if($(element).is(':checked')){
            $('input[name^="arr_dp_code"]').each(function(){
                if(!$(this).is(':checked')){
                    $(this).prop( "checked", true);
                }
            });
        }else{
            $('input[name^="arr_dp_code"]').each(function(){
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
                    company_id : $('#filter_company').val(),
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
                { name: 'company_id', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'received_date', className: 'center-align' },
                { name: 'due_date', className: 'center-align' },
                { name: 'document_date', className: 'center-align' },
                { name: 'type', className: 'center-align' },
                { name: 'document', searchable: false, orderable: false, className: 'center-align' },
                { name: 'note', className: 'center-align' },
                { name: 'tax_no', className: 'center-align' },
                { name: 'tax_cut_no', className: 'center-align' },
                { name: 'cut_date', className: 'center-align' },
                { name: 'spk_no', className: 'center-align' },
                { name: 'invoice_no', className: 'center-align' },
                { name: 'subtotal', className: 'right-align' },
                { name: 'percent_discount', className: 'right-align' },
                { name: 'nominal_discount', className: 'right-align' },
                { name: 'total', className: 'right-align' },
                { name: 'tax', className: 'right-align' },
                { name: 'wtax', className: 'right-align' },
                { name: 'grandtotal', className: 'right-align' },
                { name: 'downpayment', className: 'right-align' },
                { name: 'balance', className: 'right-align' },
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

                formData.delete("arr_code[]");
                formData.delete("arr_type[]");
                formData.delete("arr_total[]");
                formData.delete("arr_tax[]");
                formData.delete("arr_wtax[]");
                formData.delete("arr_grandtotal[]");
                formData.delete("arr_dp_code[]");
                formData.delete("arr_nominal[]");

                $('input[name^="arr_code"]').each(function(){
                    if($(this).is(':checked')){
                        formData.append('arr_code[]',$(this).val());
                        formData.append('arr_type[]',$('input[name^="arr_type"][data-id="' + $(this).data('id') + '"]').val());
                        formData.append('arr_total[]',$('input[name^="arr_total"][data-id="' + $(this).data('id') + '"]').val());
                        formData.append('arr_tax[]',$('input[name^="arr_tax"][data-id="' + $(this).data('id') + '"]').val());
                        formData.append('arr_wtax[]',$('input[name^="arr_wtax"][data-id="' + $(this).data('id') + '"]').val());
                        formData.append('arr_grandtotal[]',$('input[name^="arr_grandtotal"][data-id="' + $(this).data('id') + '"]').val());
                        
                    }
                });

                $('input[name^="arr_dp_code"]').each(function(index){
                    if($(this).is(':checked')){
                        formData.append('arr_dp_code[]',$(this).val());
                        formData.append('arr_nominal[]',$('input[name^="arr_nominal"]').eq(index).val());
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
                $('#account_id').empty();
                $('#account_id').append(`
                    <option value="` + response.account_id + `">` + response.account_name + `</option>
                `);
                $('#type').val(response.type).formSelect();
                $('#company_id').val(response.company_id).formSelect();
                $('#post_date').val(response.post_date);
                $('#received_date').val(response.received_date);
                $('#due_date').val(response.due_date);
                $('#document_date').val(response.document_date);                
                $('#note').val(response.note);
                $('#tax_no').val(response.tax_no);
                $('#tax_cut_no').val(response.tax_cut_no);
                $('#cut_date').val(response.cut_date);
                $('#spk_no').val(response.spk_no);
                $('#invoice_no').val(response.invoice_no);
                $('#downpayment').val(response.downpayment);
                
                if(response.details.length > 0){
                    $('#body-detail').empty();
                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        $('#body-detail').append(`
                            <tr class="row_detail">
                                <input type="hidden" name="arr_type[]" value="` + val.type + `" data-id="` + count + `">
                                <input type="hidden" name="arr_total[]" value="` + val.total + `" data-id="` + count + `">
                                <input type="hidden" name="arr_tax[]" value="` + val.tax + `" data-id="` + count + `">
                                <input type="hidden" name="arr_wtax[]" value="` + val.wtax + `" data-id="` + count + `">
                                <input type="hidden" name="arr_grandtotal[]" value="` + val.grandtotal + `" data-id="` + count + `">
                                <td class="center-align">
                                    <label>
                                        <input type="checkbox" id="check` + count + `" name="arr_code[]" value="` + val.code + `" onclick="countAll();" data-id="` + count + `" checked>
                                        <span>Pilih</span>
                                    </label>
                                </td>
                                <td class="center">
                                    ` + val.rawcode + `
                                </td>
                                <td class="center">
                                    ` + val.purchase_no + `
                                </td>
                                <td class="center">
                                    ` + val.delivery_no + `
                                </td>
                                <td class="">
                                    ` + val.list_item + `
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
                                <td class="right-align" id="row_tax` + count + `">
                                    <input type="text" name="arr_tax[]" value="` + val.tax + `" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();">
                                
                                </td>
                                <td class="right-align" id="row_wtax` + count + `">
                                    <input type="text" name="arr_wtax[]" value="` + val.wtax + `" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();">
                                </td>
                                <td class="right-align" id="row_grandtotal` + count + `">
                                    ` + val.grandtotal + `
                                </td>
                                <td>
                                    ` + val.info + `
                                </td>
                            </tr>
                        `);
                    });
                }

                if(response.downpayments.length > 0){
                    $('#body-detail-dp').empty();
                    $.each(response.downpayments, function(i, val) {
                        var count = makeid(10);
                        $('#body-detail-dp').append(`
                            <tr class="row_detail_dp">
                                <td class="center-align">
                                    <label>
                                        <input type="checkbox" id="check` + count + `" name="arr_dp_code[]" value="` + val.code + `" onclick="countAll();" data-id="` + count + `" checked>
                                        <span>Pilih</span>
                                    </label>
                                </td>
                                <td class="center">
                                    ` + val.rawcode + `
                                </td>
                                <td class="center">
                                    ` + val.post_date + `
                                </td>
                                <td class="center">
                                    ` + val.grandtotal + `
                                </td>
                                <td class="center">
                                    ` + val.nominal + `
                                </td>
                                <td class="center">
                                    <input name="arr_nominal[]" class="browser-default" type="text" value="` + val.nominal + `" onkeyup="formatRupiah(this);countAll();" style="text-align:right;width:100% !important;" id="rowNominal`+ count +`">
                                </td>
                            </tr>
                        `);
                    });                        
                }

                $('.modal-content').scrollTop(0);
                $('#note').focus();
                M.updateTextFields();
                countAll();
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
        var search = window.table.search(), status = $('#filter_status').val(), type = $('#filter_type').val(), company = $('#filter_company').val(), account = $('#filter_account').val();
        
        $.ajax({
            type : "POST",
            url  : '{{ Request::url() }}/print',
            data : {
                search : search,
                status : status,
                type : type,
                company : company,
                'account[]' : account,
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
        var search = window.table.search(), status = $('#filter_status').val(), type = $('#filter_type').val(), company = $('#filter_company').val(), account = $('#filter_account').val();
        
        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status + "&type=" + type + "&company=" + company + "&account=" + account;
    }

    function addDays(){
        if($('#top').val()){
            var result = new Date($('#received_date').val());
            result.setDate(result.getDate() + parseInt($('#top').val()));
            $('#due_date').val(result.toISOString().split('T')[0]);
        }else{
            $('#due_date').val(null);
        }
    }
</script>