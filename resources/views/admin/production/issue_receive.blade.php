<style>
    .modal {
        top:0px !important;
    }

    .select-wrapper, .select2-container {
        height:3.6rem !important;
    }
    
    table.bordered th {
        padding: 5px !important;
    }

    #table-detail-item td, #table-detail-item th{
        padding: 5px 5px;
        border: 1px solid rgba(10, 10, 10, 1) !important;
    }

    #table-detail-row td, #table-detail-row th{
        padding: 5px 5px;
        border: 1px solid rgba(10, 10, 10, 1) !important;
    }

    #sticky {
        position: -webkit-sticky;
        position: sticky;
        top: 0;
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
                        
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="printData();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">Print</span>
                            <i class="material-icons right">local_printshop</i>
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
                                                <div class="input-field col s12">
                                                    <select class="form-control" id="filter_status" onchange="loadDataTable()" multiple>
                                                        <option value="1">Menunggu</option>
                                                        <option value="2">Dalam Proses</option>
                                                        <option value="3">Selesai</option>
                                                        <option value="4">Ditolak</option>
                                                        <option value="5">Ditutup</option>
                                                        <option value="6">Direvisi</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="start_date" style="font-size:1rem;">Start Date (Tanggal Mulai Posting) :</label>
                                                <div class="input-field col s12">
                                                    <input type="date" id="start_date" name="start_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="finish_date" style="font-size:1rem;">End Date (Tanggal Akhir Posting) :</label>
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
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">Refresh</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <table id="datatable_serverside" class="display responsive-table wrap">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Code</th>
                                                        <th>Pengguna</th>
                                                        <th>Perusahaan</th>
                                                        <th>Tgl.Post</th>
                                                        <th>Dokumen</th>
                                                        <th>Status</th>
                                                        <th>Operasi</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;">
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
                                    <div class="input-field col m2 s12 step1">
                                        <input type="hidden" id="temp" name="temp">
                                        <input id="code" name="code" type="text" value="{{ $newcode }}" readonly>
                                        <label class="active" for="code">No. Dokumen</label>
                                    </div>
                                    <div class="input-field col m1 s12 step2">
                                        <select class="form-control" id="code_place_id" name="code_place_id" onchange="getCode(this.value);">
                                            <option value="">--Pilih--</option>
                                            @foreach ($place as $rowplace)
                                                <option value="{{ $rowplace->code }}">{{ $rowplace->code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="input-field col m3 s12 step3">
                                        <select class="form-control" id="company_id" name="company_id">
                                            @foreach ($company as $row)
                                                <option value="{{ $row->id }}">{{ $row->name }}</option>
                                            @endforeach
                                        </select>
                                        <label class="" for="company_id">Perusahaan</label>
                                    </div>
                                    <div class="input-field col m3 s12 step6">
                                        <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                        <label class="active" for="post_date">Tgl. Post</label>
                                    </div>
                                    <div class="file-field input-field col m3 s12 step7">
                                        <div class="btn">
                                            <span>File</span>
                                            <input type="file" name="file" id="file">
                                        </div>
                                        <div class="file-path-wrapper">
                                            <input class="file-path validate" type="text">
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <div class="row mt-3" id="sticky" style="z-index:99 !important;background-color: #ffffff !important;border-radius:30px !important;">
                            <div class="col s12">
                                <fieldset>
                                    <legend>2. Order Produksi</legend>
                                    <div class="input-field col m3 s12 step8">
                                        <select class="browser-default" id="production_order_id" name="production_order_id"></select>
                                        <label class="active" for="production_order_id">Daftar Order Produksi</label>
                                    </div>
                                    <div class="col m2 s12 step9">
                                        <a class="waves-effect waves-light cyan btn-small mb-1 mr-1 mt-5" onclick="getProductionOrder();" href="javascript:void(0);">
                                            <i class="material-icons left">add</i> Order Produksi
                                        </a>
                                    </div>
                                    <div class="col m4 s12 step10">
                                        <h6>Data Terpakai : <i id="list-used-data"></i></h6>
                                    </div>
                                    <div class="col m12">
                                        <div class="row">
                                            <div class="col m4 s12">
                                                Shift : <b id="output-shift">-</b>
                                            </div>
                                            <div class="col m4 s12">
                                                Grup : <b id="output-group">-</b>
                                            </div>
                                            <div class="col m4 s12">
                                                Line : <b id="output-line">-</b>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12">
                                <fieldset>
                                    <legend>3. Detail Item Issue Receive</legend>
                                    <div class="col m12 s12 step12" style="overflow:auto;width:100% !important;">
                                        <ul class="tabs">
                                            <li class="tab col m3"><a class="active step19" href="#issue">Issue (Terpakai)</a></li>
                                            <li class="tab col m3"><a class="step20" href="#receive">Receive (Terima)</a></li>
                                        </ul>
                                        <div class="row step22">
                                            <div id="issue" class="col s12 active">
                                                <p class="mt-2 mb-2">
                                                    <table class="bordered" style="border: 1px solid;" id="table-detail-item-issue">
                                                        <thead>
                                                            <tr>
                                                                <th class="center">No.</th>
                                                                <th class="center">Item/Coa</th>
                                                                <th class="center">Qty Planned</th>
                                                                <th class="center">Qty Real</th>
                                                                <th class="center">Satuan Produksi</th>
                                                                <th class="center">Plant & Gudang</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="body-item-issue">
                                                            <tr id="last-row-item-issue">
                                                                <td class="center-align" colspan="6">
                                                                    Silahkan tambahkan Order Produksi untuk memulai...
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </p>
                                            </div>
                                            <div id="receive" class="col s12">
                                                <p class="mt-2 mb-2">
                                                    <table class="bordered" style="border: 1px solid;" id="table-detail-item-receive">
                                                        <thead>
                                                            <tr>
                                                                <th class="center" width="5%">No.</th>
                                                                <th class="center" width="15%">Item/Coa</th>
                                                                <th class="center" width="10%">Qty Planned</th>
                                                                <th class="center" width="14%">Qty Real</th>
                                                                <th class="center" width="13%">Qty UoM</th>
                                                                <th class="center" width="13%">Qty Jual</th>
                                                                <th class="center" width="15%">Shading</th>
                                                                <th class="center" width="15%">Batch</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="body-item-receive">
                                                            <tr id="last-row-item-receive">
                                                                <td class="center-align" colspan="8">
                                                                    Silahkan tambahkan Order Produksi untuk memulai...
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12 mt-3">
                                <button class="btn waves-effect waves-light right submit step13" onclick="save();">Simpan <i class="material-icons right">send</i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light purple" onclick="startIntro();">Panduan <i class="material-icons right">help_outline</i></button>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Tutup</a>
    </div>
</div>

<div id="modal2" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
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
<div id="modal3" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="show_structure">
                <div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;">

                </div>
                <div id="visualisation">
                </div>
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

<div id="modal5" class="modal modal-fixed-footer" style="height: 70% !important;width:50%">
    <div class="modal-header ml-6 mt-2">
        <h6>Range Printing</h6>
    </div>
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <form class="row" id="form_data_print_multi" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert_multi" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <ul class="tabs">
                            <li class="tab">
                                <a href="#range-tabs" class="" id="part-tabs-btn">
                                <span>By No</span>
                                </a>
                            </li>
                            <li class="tab">
                                <a href="#date-tabs" class="">
                                <span>By Date</span>
                                </a>
                            </li>
                            <li class="indicator" style="left: 0px; right: 0px;"></li>
                        </ul>
                        <div id="range-tabs" style="display: block;" class="">                           
                            <div class="row ml-2 mt-2">
                                <div class="row">
                                    <div class="input-field col m4 s12">
                                        <input id="range_start" name="range_start" min="0" type="number" placeholder="1">
                                        <label class="" for="range_end">No Awal</label>
                                    </div>
                                    
                                    <div class="input-field col m4 s12">
                                        <input id="range_end" name="range_end" min="0" type="number" placeholder="1">
                                        <label class="active" for="range_end">No akhir</label>
                                    </div>
                                    <div class="input-field col m4 s12">
                                        <label>
                                            <input name="type_date" type="radio" checked value="1"/>
                                            <span>Dengan range biasa</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                <div class="input-field col m8 s12">
                                    <input id="range_comma" name="range_comma" type="text" placeholder="1,2,5....">
                                    <label class="" for="range_end">Masukkan angka dengan koma</label>
                                </div>
                               
                                <div class="input-field col m4 s12">
                                    <label>
                                        <input name="type_date" type="radio" value="2"/>
                                        <span>Dengan Range koma</span>
                                    </label>
                                </div>
                                </div>
                                <div class="col s12 mt-3">
                                    <button class="btn waves-effect waves-light right submit" onclick="printMultiSelect();">Print <i class="material-icons right">send</i></button>
                                </div>
                            </div>                         
                        </div>
                        <div id="date-tabs" style="display: none;" class="">
                            
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat mr-1">Close</a>
    </div>
</div>

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div>

<div style="bottom: 50px; right: 80px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-amber-amber gradient-shadow modal-trigger tooltipped"  data-position="top" data-tooltip="Range Printing" href="#modal5">
        <i class="material-icons">view_comfy</i>
    </a>
</div>

<!-- END: Page Main-->
<script>
    
    $(function() {

        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();
            
        });

        loadDataTable();

        window.table.search('{{ $code }}').draw();

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

        $('#modal4').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#show_detail').empty();
            }
        });

        $('#modal5').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) {
                $('#validation_alert_multi').hide();
                $('#validation_alert_multi').html('');
                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                
            }
        });

        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                $('#post_date').attr('min','{{ $minDate }}');
                $('#post_date').attr('max','{{ $maxDate }}');
            },
            onOpenEnd: function(modal, trigger) {
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
                $('.tabs').tabs({
                    onShow: function () {
                        
                    }
                });
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
                M.updateTextFields();
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }
                window.onbeforeunload = function() {
                    return null;
                };
            }
        });

        select2ServerSide('#production_order_id', '{{ url("admin/select2/production_order") }}');
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
                
                
            } else if (part instanceof go.Node) {
                window.open(part.data.url);
                if (part.isTreeExpanded) {
                    part.collapseTree();
                } else {
                    part.expandTree();
                }
                
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
        

        myDiagram.addDiagramListener("InitialLayoutCompleted", function(e) {
        setTimeout(function() {
            
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

    function removeUsedData(type,id){
        $.ajax({
            url: '{{ Request::url() }}/remove_used_data',
            type: 'POST',
            dataType: 'JSON',
            data: { 
                id : id,
                type : type,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                
            },
            success: function(response) {
                $('.row_item_issue[data-id="' + id + '"],.row_item_receive[data-id="' + id + '"]').remove();
                $('#body-item-issue').empty().append(`
                    <tr id="last-row-item-issue">
                        <td class="center-align" colspan="6">
                            Silahkan tambahkan Order Produksi untuk memulai...
                        </td>
                    </tr>
                `);
                $('#body-item-receive').empty().append(`
                    <tr id="last-row-item-receive">
                        <td class="center-align" colspan="8">
                            Silahkan tambahkan Order Produksi untuk memulai...
                        </td>
                    </tr>
                `);
                $('#production_order_id').empty();
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

    function getProductionOrder(){
        if($('#production_order_id').val()){
            let datakuy = $('#production_order_id').select2('data')[0];
            $.ajax({
                url: '{{ Request::url() }}/send_used_data',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: $('#production_order_id').val(),
                    type: datakuy.table,
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
                    }else{

                        $('#list-used-data').append(`
                            <div class="chip purple darken-4 gradient-shadow white-text">
                                ` + datakuy.code + `
                                <i class="material-icons close data-used" onclick="removeUsedData('` + datakuy.table + `','` + $('#production_order_id').val() + `')">close</i>
                            </div>
                        `);

                        $('.row_item_issue,.row_item_receive').remove();
                        
                        $('#last-row-item-issue,#last-row-item-receive').remove();

                        let no_issue = $('.row_item_issue').length + 1;
                        let no_receive = $('.row_item_receive').length + 1;

                        $.each(datakuy.detail_issue, function(i, val) {
                            var count = makeid(10);
                            $('#body-item-issue').append(`
                                <tr class="row_item_issue" data-id="` + $('#production_order_id').val() + `">
                                    <input type="hidden" name="arr_type[]" value="1">
                                    <input type="hidden" name="arr_lookable_type[]" value="` + val.lookable_type + `">
                                    <input type="hidden" name="arr_lookable_id[]" value="` + val.lookable_id + `">
                                    <input type="hidden" name="arr_production_detail_id[]" value="` + val.id + `">
                                    <input type="hidden" name="arr_bom_detail_id[]" value="` + val.bom_detail_id + `">
                                    <input type="hidden" name="arr_nominal[]" value="` + val.nominal + `">
                                    <input type="hidden" name="arr_total[]" value="` + val.total + `">
                                    <input type="hidden" name="arr_shading[]" value="">
                                    <td class="center-align">
                                        ` + no_issue + `
                                    </td>
                                    <td>
                                        ` + val.lookable_code + ` - ` + val.lookable_name + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.qty + `
                                    </td>
                                    <td class="center">
                                        <input name="arr_qty[]" class="browser-default" type="text" value="` + val.qty + `" onkeyup="formatRupiah(this);" style="text-align:right;width:100%;" id="rowQty`+ count +`">
                                    </td>
                                    <td class="center">
                                        ` + val.lookable_unit + `
                                    </td>
                                    <td class="center">
                                        -
                                    </td>
                                </tr>
                            `);
                        });

                        var count = makeid(10);
                        $('#body-item-receive').append(`
                            <tr class="row_item_issue" data-id="` + $('#production_order_id').val() + `">
                                <input type="hidden" name="arr_type[]" value="2">
                                <input type="hidden" name="arr_lookable_type[]" value="items">
                                <input type="hidden" name="arr_lookable_id[]" value="` + datakuy.item_receive_id + `">
                                <input type="hidden" name="arr_production_detail_id[]" value="">
                                <input type="hidden" name="arr_bom_detail_id[]" value="">
                                <input type="hidden" name="arr_nominal[]" value="0,00">
                                <input type="hidden" name="arr_total[]" value="0,00">
                                <td class="center-align">
                                    ` + no_receive + `
                                </td>
                                <td>
                                    ` + datakuy.item_receive_code + ` - ` + datakuy.item_receive_name + `
                                </td>
                                <td class="right-align">
                                    ` + datakuy.item_receive_qty + `
                                </td>
                                <td class="center">
                                    <div class="input-field col s10">
                                        <input name="arr_qty[]" type="text" value="` + datakuy.item_receive_qty + `" onkeyup="formatRupiah(this);" style="text-align:right;width:100%;margin: 0 0 0 0 !important;height:inherit !important;font-size:0.9rem !important;" id="rowQty`+ count +`">
                                        <div class="form-control-feedback production-unit" style="right:-30px;top:-10px;">-</div>
                                    </div>
                                </td>
                                <td class="right-align">
                                    -
                                </td>
                                <td class="center">
                                    -
                                </td>
                                <td class="center">
                                    <input name="arr_shading[]" class="browser-default" type="text" placeholder="Kode Shading..." style="text-align:right;width:100%;">
                                </td>
                                <td class="center">
                                    -
                                </td>
                            </tr>
                        `);

                        $('#production_order_id').empty();
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
            $('#body-item-issue').empty().append(`
                <tr id="last-row-item-issue">
                    <td class="center-align" colspan="6">
                        Silahkan tambahkan Order Produksi untuk memulai...
                    </td>
                </tr>
            `);
            $('#body-item-receive').empty().append(`
                <tr id="last-row-item-receive">
                    <td class="center-align" colspan="8">
                        Silahkan tambahkan Order Produksi untuk memulai...
                    </td>
                </tr>
            `);
        }
    }

    function getRowUnit(val){
        let type = $('#arr_lookable_type' + val).val();
        if(type == 'items'){
            if($('#arr_lookable_id' + val).val()){
                $('#arr_unit' + val).text($("#arr_lookable_id" + val).select2('data')[0].uom);
            }else{
                $('#arr_unit' + val).text('-');
            }
        }else{
            $('#arr_unit' + val).text('-');
        }
    }

    function addItemIssue(type,code,psd_id,lookable){
        let count = makeid(10);
        $('#last-row-issue' + code).before(`
            <tr class="row_item_issue_detail" data-id="` + psd_id + `">
                <input type="hidden" name="arr_psd[]" id="arr_psd` + count + `" value="` + psd_id + `">
                <input type="hidden" name="arr_type[]" id="arr_type` + count + `" value="` + type + `">
                <input type="hidden" name="arr_lookable_type[]" id="arr_lookable_type` + count + `" value="` + lookable + `">
                <input name="arr_batch_no[]" type="hidden" value="">
                <th class="">
                    <select class="browser-default" id="arr_lookable_id` + count + `" name="arr_lookable_id[]" onchange="getRowUnit('` + count + `');"></select>
                </th>
                <th class="center-align">
                    <input name="arr_nominal[]" class="browser-default" type="text" value="0" onkeyup="formatRupiahNoMinus(this);" style="text-align:right;">    
                </th>
                <th class="center-align" id="arr_unit` + count + `">
                    -
                </th>
                <th class="center-align">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item-issue" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>    
                </th>
            </tr>
        `);
        if(lookable == 'coas'){
            select2ServerSide('#arr_lookable_id' + count, '{{ url("admin/select2/coa") }}');
        }else if(lookable == 'items'){
            select2ServerSide('#arr_lookable_id' + count, '{{ url("admin/select2/item") }}');
        }

        $('.body-item-issue').on('click', '.delete-data-item-issue', function() {
            $(this).closest('tr').remove();
        });
    }

    function addItem(type,code,psd_id){
        let count = makeid(10);
        
        $('#last-row-receive' + code).before(`
            <tr class="row_item_receive_detail" data-id="` + psd_id + `">
                <input type="hidden" name="arr_psd[]" id="arr_psd` + count + `" value="` + psd_id + `">
                <input type="hidden" name="arr_type[]" id="arr_type` + count + `" value="` + type + `">
                <input type="hidden" name="arr_lookable_type[]" id="arr_lookable_type` + count + `" value="items">
                <th class="">
                    <select class="browser-default" id="arr_lookable_id` + count + `" name="arr_lookable_id[]" onchange="getRowUnit('` + count + `');" required></select>    
                </th>
                <th class="center-align">
                    <input name="arr_nominal[]" class="browser-default" type="text" value="0" onkeyup="formatRupiahNoMinus(this);" style="text-align:right;" required>
                </th>
                <th class="center-align" id="arr_unit` + count + `">
                    -
                </th>
                <th class="center-align">
                    <input name="arr_batch_no[]" class="browser-default" type="text" placeholder="Nomor Produksi..." required>
                </th>
                <th class="center-align">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item-receive" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>    
                </th>
            </tr>
        `);

        select2ServerSide('#arr_lookable_id' + count, '{{ url("admin/select2/item") }}');

        $('.body-item-receive').on('click', '.delete-data-item-receive', function() {
            $(this).closest('tr').remove();
        });
    }

    function printMultiSelect(){
        var formData = new FormData($('#form_data_print_multi')[0]);
        var table = $('#datatable_serverside').DataTable();
        var data = table.data().toArray();
        var etNumbers = data.map(item => item[2]);
        formData.append('tabledata',etNumbers);
        $.ajax({
            url: '{{ Request::url() }}/print_by_range',
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
                $('#validation_alert_multi').html('');
                loadingOpen('.modal-content');
            },
            success: function(response) {
                loadingClose('.modal-content');
                if(response.status == 200) {
                    $('#modal5').modal('close');
                    /*  printService.submit({
                        'type': 'INVOICE',
                        'url': response.message
                    }) */
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

    function printData(){
        var arr_id_temp=[];
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
            },
            success: function(response) {
                printService.submit({
                    'type': 'INVOICE',
                    'url': response.message
                });
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
            "order": [[0, 'asc']],
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    'status' : $('#filter_status').val(),
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
                { name: 'code', className: 'center-align' },
                { name: 'user_id', className: 'center-align' },
                { name: 'company_id', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'document', searchable: false, orderable: false, className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'operation', searchable: false, orderable: false, className: 'center-align' },
            ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle',
                'selectAll',
                'selectNone',
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

                formData.delete("arr_batch_no[]");

                $('input[name^="arr_batch_no[]"]').each(function(index){
                    formData.append('arr_batch_no[]',($(this).val() ? $(this).val() : ''));
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
                $('#code_place_id').val(response.code_place_id).formSelect();
                $('#code').val(response.code);
                $('#post_date').val(response.post_date);
                $('#company_id').val(response.company_id).formSelect();

                if(response.details.length > 0){
                    $('.row_item').each(function(){
                        $(this).remove();
                    });

                    if($('#last-row-item').length > 0){
                        $('#last-row-item').remove();
                    }

                    let no = 1, arrCode = [];

                    $.each(response.details, function(i, val) {
                        var count = makeid(10);

                        let issueDetail = ``, receiveDetail = ``;

                        $.each(val.details_issue, function(i, detail) {
                            var countDetail = makeid(10);

                            arrCode.push({'type' : detail.lookable_type, 'code' : countDetail });

                            issueDetail += `<tr class="row_item_issue_detail" data-id="` + val.psd_id + `">
                                    <input type="hidden" name="arr_psd[]" id="arr_psd` + countDetail + `" value="` + val.psd_id + `">
                                    <input type="hidden" name="arr_type[]" id="arr_type` + countDetail + `" value="` + detail.type + `">
                                    <input type="hidden" name="arr_lookable_type[]" id="arr_lookable_type` + countDetail + `" value="` + detail.lookable_type + `">
                                    <input name="arr_batch_no[]" type="hidden" value="">
                                    <th class="">
                                        <select class="browser-default" id="arr_lookable_id` + countDetail + `" name="arr_lookable_id[]" onchange="getRowUnit('` + countDetail + `');"><option value="` + detail.lookable_id + `">` + detail.name + `</option></select>
                                    </th>
                                    <th class="center-align">
                                        <input name="arr_nominal[]" class="browser-default" type="text" value="` + detail.nominal + `" onkeyup="formatRupiahNoMinus(this);" style="text-align:right;">    
                                    </th>
                                    <th class="center-align" id="arr_unit` + countDetail + `">
                                        ` + detail.unit + `
                                    </th>
                                    <th class="center-align">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item-issue" href="javascript:void(0);">
                                            <i class="material-icons">delete</i>
                                        </a>    
                                    </th>
                                </tr>`;
                        });

                        $.each(val.details_receive, function(i, detail) {
                            var countDetail = makeid(10);

                            arrCode.push({'type' : detail.lookable_type, 'code' : countDetail });

                            receiveDetail += `<tr class="row_item_receive_detail" data-id="` + val.psd_id + `">
                                    <input type="hidden" name="arr_psd[]" id="arr_psd` + countDetail + `" value="` + val.psd_id + `">
                                    <input type="hidden" name="arr_type[]" id="arr_type` + countDetail + `" value="` + detail.type + `">
                                    <input type="hidden" name="arr_lookable_type[]" id="arr_lookable_type` + countDetail + `" value="items">
                                    <th class="">
                                        <select class="browser-default" id="arr_lookable_id` + countDetail + `" name="arr_lookable_id[]" onchange="getRowUnit('` + countDetail + `');" required><option value="` + detail.lookable_id + `">` + detail.name + `</option></select>    
                                    </th>
                                    <th class="center-align">
                                        <input name="arr_nominal[]" class="browser-default" type="text" value="` + detail.nominal + `" onkeyup="formatRupiahNoMinus(this);" style="text-align:right;" required>
                                    </th>
                                    <th class="center-align" id="arr_unit` + countDetail + `">
                                        ` + detail.unit + `
                                    </th>
                                    <th class="center-align">
                                        <input name="arr_batch_no[]" class="browser-default" type="text" placeholder="Nomor Produksi..." value="` + detail.batch_no + `" required>
                                    </th>
                                    <th class="center-align">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item-receive" href="javascript:void(0);">
                                            <i class="material-icons">delete</i>
                                        </a>    
                                    </th>
                                </tr>`;
                        });
                        
                        $('#body-item').append(`
                            <tr class="row_item" data-id="` + val.ps_id + `" data-detail="` + val.ps_id + `">
                                <th class="center-align" rowspan="3">` + no + `.</th>
                                <th class="center-align">` + val.production_date + `</th>
                                <th class="center-align">` + val.shift + `</th>
                                <th class="center-align">` + val.item_name + `</th>
                                <th class="right-align">` + val.qty + `</th>
                                <th class="center-align">` + val.unit + `</th>
                                <th class="center-align" rowspan="3">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" data-id="` + val.ps_id + `" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </th>
                            </tr>
                            <tr class="row_item_issue">
                                <td class="center-align" colspan="5" style="background-color:#ff7a7a;">
                                    <h6><b>Issue Item</b></h6>
                                    <table class="bordered" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="center-align">COA/Item</th>
                                                <th class="center-align">Harga/Qty</th>
                                                <th class="center-align">Satuan</th>
                                                <th class="center-align">Hapus</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-item-issue-` + count + `" class="body-item-issue">
                                            ` + issueDetail + `
                                            <tr id="last-row-issue`+ count +`">
                                                <td class="center-align" colspan="4">
                                                    <a class="waves-effect waves-light teal btn-small mb-1 mr-1" onclick="addItemIssue('1','` + count + `',` + val.psd_id + `,'coas');" href="javascript:void(0);">
                                                        <i class="material-icons left">add</i> Coa
                                                    </a>
                                                    <a class="waves-effect waves-light teal btn-small mb-1 mr-1" onclick="addItemIssue('1','` + count + `',` + val.psd_id + `,'items');" href="javascript:void(0);">
                                                        <i class="material-icons left">add</i> Item
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr class="row_item_receive">
                                <td class="center-align" colspan="5" style="background-color:#63ff80;">
                                    <h6><b>Receive Item</b></h6>
                                    <table class="bordered" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="center-align">Item</th>
                                                <th class="center-align">Qty</th>
                                                <th class="center-align">Satuan</th>
                                                <th class="center-align">Batch No</th>
                                                <th class="center-align">Hapus</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-item-receive-` + count + `" class="body-item-receive">
                                            ` + receiveDetail + `
                                            <tr id="last-row-receive`+ count +`">
                                                <td class="center-align" colspan="5">
                                                    <a class="waves-effect waves-light teal btn-small mb-1 mr-1" onclick="addItem('2','` + count + `',` + val.psd_id + `);" href="javascript:void(0);">
                                                        <i class="material-icons left">add</i> Item
                                                    </a>    
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        `);

                        no++;
                    });

                    $.each(arrCode, function(i, detail) {
                        if(detail['type'] == 'coas'){
                            select2ServerSide('#arr_lookable_id' + detail['code'], '{{ url("admin/select2/coa") }}');
                        }else if(detail['type'] == 'items'){
                            select2ServerSide('#arr_lookable_id' + detail['code'], '{{ url("admin/select2/item") }}');
                        }
                    });

                    $('.body-item-issue').on('click', '.delete-data-item-issue', function() {
                        $(this).closest('tr').remove();
                    });
                    $('.body-item-receive').on('click', '.delete-data-item-receive', function() {
                        $(this).closest('tr').remove();
                    });
                }
                
                $('.modal-content').scrollTop(0);
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

    String.prototype.replaceAt = function(index, replacement) {
        return this.substring(0, index) + replacement + this.substring(index + replacement.length);
    };

    function getCode(val){
        if(val){
            if($('#temp').val()){
                let newcode = $('#code').val().replaceAt(7,val);
                $('#code').val(newcode);
            }else{
                if($('#code').val().length > 7){
                    $('#code').val($('#code').val().slice(0, 7));
                }
                $.ajax({
                    url: '{{ Request::url() }}/get_code',
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        val: $('#code').val() + val,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        loadingOpen('.modal-content');
                    },
                    success: function(response) {
                        loadingClose('.modal-content');
                        $('#code').val(response);
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
        }
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

    function printPreview(code){
        $.ajax({
            url: '{{ Request::url() }}/print_individual/' + code,
            type:'GET',
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            complete: function() {
                
            },
            success: function(data){
                loadingClose('.modal-content');
                printService.submit({
                    'type': 'INVOICE',
                    'url': data
                })
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

    function startIntro(){
        introJs().setOptions({
            exitOnOverlayClick : false,
            steps: [
                {
                    title : 'Jadwal Produksi',
                    intro : 'Form ini digunakan untuk mengelola data penjadwalan produksi sesuai .'
                },
                {
                    title : 'Nomor Dokumen',
                    element : document.querySelector('.step1'),
                    intro : 'Nomor dokumen wajib diisikan, dengan kombinasi 4 huruf kode dokumen, tahun pembuatan dokumen, kode plant, serta nomor urut. Nomor ini bersifat unik, tidak akan sama, dan nomor urut paling belakang akan ter-reset secara otomatis berdasarkan tahun tanggal post.'
                },
                {
                    title : 'Kode Plant',
                    element : document.querySelector('.step2'),
                    intro : 'Pilih kode plant untuk nomor dokumen bisa secara otomatis ter-generate.'
                },
                {
                    title : 'Perusahaan',
                    element : document.querySelector('.step3'),
                    intro : 'Perusahaan dimana dokumen ini dibuat.' 
                },
                {
                    title : 'Plant',
                    element : document.querySelector('.step4'),
                    intro : 'Plant dimana produksi akan dijalankan.' 
                },
                {
                    title : 'Mesin',
                    element : document.querySelector('.step5'),
                    intro : 'Mesin yang digunakan dalam proses produksi. Akan otomatis terisi berdasarkan daftar mesin yang menempel pada data Plant terpilih.' 
                },
                {
                    title : 'Tgl. Posting',
                    element : document.querySelector('.step6'),
                    intro : 'Tanggal posting yang akan muncul pada saat dokumen dicetak, difilter atau diproses pada form lainnya.' 
                },
                {
                    title : 'File Lampiran',
                    element : document.querySelector('.step7'),
                    intro : 'Silahkan unggah file lampiran. Untuk saat ini hanya bisa mengakomodir 1 file lampiran saja. Jika ingin menambahkan file lebih dari 1, silahkan gabungkan file anda menjadi pdf.' 
                },
                {
                    title : 'Marketing Order Plan',
                    element : document.querySelector('.step8'),
                    intro : 'Silahkan pilih MOP yang ingin diproses produksinya. Anda bisa memilih lebih dari satu MOP untuk satu kali transaksi dokumen Jadwal Produksi.' 
                },
                {
                    title : 'Tombol tambah MOP',
                    element : document.querySelector('.step9'),
                    intro : 'Tombol untuk menambahkan data item MOP ke dalam tabel 3 Detail Target Produksi.' 
                },
                {
                    title : 'Data MOP Terpakai',
                    element : document.querySelector('.step10'),
                    intro : 'Data MOP yang terpakai pada saat ditambahkan ke dalam sistem sesuai dengan pengguna aktif saat ini. Silahkan hapus agar MOP bisa diakses oleh pengguna lainnya.' 
                },
                {
                    title : 'Detail Target Produksi',
                    element : document.querySelector('.step11'),
                    intro : 'Berisi detail produk / item yang ingin dijadikan target proses Produksi.'
                },
                {
                    title : 'Detail Shift',
                    element : document.querySelector('.step12'),
                    intro : 'Berisi detail produk / item yang ingin dijadikan target proses Produksi serta shift yang ingin dicatat.'
                },
                {
                    title : 'Tombol Simpan',
                    element : document.querySelector('.step13'),
                    intro : 'Silahkan tekan tombol ini untuk menyimpan data, namun pastikan data yang akan anda masukkan benar.' 
                },
            ]
        }).start();
    }
</script>