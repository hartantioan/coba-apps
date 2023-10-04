<style>
    .modal {
        top:0px !important;
    }

    .select-wrapper, .select2-container {
        height:3.6rem !important;
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
                                                    <select class="form-control" id="filter_status" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
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
                                            <table id="datatable_serverside" class="display responsive-table wrap">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Pengguna</th>
                                                        <th>Code</th>
                                                        <th>Perusahaan</th>
                                                        <th>Mesin</th>
                                                        <th>Tgl.Produksi</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="min-width:100%;max-height: 100% !important;height: 100% !important;">
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
                                    <div class="input-field col m3 s12 step2">
                                        <select class="form-control" id="place_id" name="place_id" onchange="getMachine()">
                                            <option value="">--Pilih--</option>
                                            @foreach ($place as $rowplace)
                                                <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                                            @endforeach
                                        </select>
                                        <label class="" for="place_id">Plant</label>
                                    </div>
                                    <div class="input-field col m3 s12 step4">
                                        <select class="form-control" id="machine_id" name="machine_id">
                                            <option value="">--Silahkan pilih Plant--</option>
                                        </select>
                                        <label class="" for="machine_id">Mesin</label>
                                    </div>
                                    <div class="input-field col m3 s12 step5">
                                        <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                        <label class="active" for="post_date">Tgl. Produksi</label>
                                    </div>
                                    <div class="file-field input-field col m3 s12 step9">
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
                        <div class="row">
                            <div class="col s12">
                                <fieldset>
                                    <legend>2. MOP Terpakai</legend>
                                    <div class="input-field col m4 s12 step12">
                                        <select class="browser-default" id="marketing_order_plan_id" name="marketing_order_plan_id"></select>
                                        <label class="active" for="marketing_order_plan_id">Marketing Order Plan</label>
                                    </div>
                                    <div class="col m2 s12 step13">
                                        <a class="waves-effect waves-light cyan btn-small mb-1 mr-1 mt-5" onclick="getMarketingOrderPlan();" href="javascript:void(0);">
                                            <i class="material-icons left">add</i> MOP
                                        </a>
                                    </div>
                                    <div class="col m6 s12 step8">
                                        <h6>Hapus untuk bisa diakses pengguna lain : <i id="list-used-data"></i></h6>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12">
                                <fieldset>
                                    <legend>3. Detail Target Produksi</legend>
                                    <div class="col m12 s12 step10" style="overflow:auto;width:100% !important;">
                                        <p class="mt-2 mb-2">
                                            <table class="bordered">
                                                <thead>
                                                    <tr>
                                                        <th class="center">MOP</th>
                                                        <th class="center">Item</th>
                                                        <th class="center">Qty (Satuan Jual)</th>
                                                        <th class="center">Qty (Satuan UOM)</th>
                                                        <th class="center">Qty (Satuan Pallet)</th>
                                                        <th class="center">Tgl.Request</th>
                                                        <th class="center">Remark</th>
                                                        <th class="center">Hapus</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="body-item">
                                                    <tr id="last-row-item">
                                                        <td class="center-align" colspan="8">
                                                            Silahkan tambahkan Marketing Order Plan...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </p>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12">
                                <fieldset>
                                    <legend>4. Detail Shift</legend>
                                    <div class="col m12 s12 step10" style="overflow:auto;width:100% !important;">
                                        <p class="mt-2 mb-2">
                                            <table class="bordered">
                                                <thead>
                                                    <tr>
                                                        <th class="center">Shift</th>
                                                        <th class="center">Item Target FG</th>
                                                        <th class="center">Qty (Satuan UOM)</th>
                                                        <th class="center">Satuan</th>
                                                        <th class="center">Hapus</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="body-item-detail">
                                                    <tr id="last-row-item-detail">
                                                        <td class="center-align" colspan="5">
                                                            <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addShift();" href="javascript:void(0);">
                                                                <i class="material-icons left">add</i> Shift
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </p>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12 mt-3">
                                <button class="btn waves-effect waves-light right submit step11" onclick="save();">Simpan <i class="material-icons right">send</i></button>
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

<div id="modal4" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 100% !important;width:100%;">
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
    var machines = @json($machine);
    
    $(function() {

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
                M.updateTextFields();
                $('#project_id,#warehouse_id').empty();
                $('.row_item').remove();
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }
                window.onbeforeunload = function() {
                    return null;
                };
            }
        });

        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
            let id = $(this).data('id');
            if($('.row_item[data-id="' + id + '"]').length == 0){
                $('.data-used[data-id="' + id + '"]').trigger('click');
            }
        });

        $('#body-item-detail').on('click', '.delete-data-item-detail', function() {
            $(this).closest('tr').remove();
        });

        select2ServerSide('#marketing_order_plan_id', '{{ url("admin/select2/marketing_order_plan") }}');
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

    function getMachine(){
        $('#machine_id').empty();
        if($('#place_id').val()){
            let place_id = $('#place_id').val();
            if(machines.length > 0){
                for(let i = 0;i<machines.length;i++){
                    if(machines[i].place_id.toString() === place_id.toString()){
                        $('#machine_id').append(`
                            <option value="` + machines[i].id + `">` + machines[i].code + ` - ` + machines[i].name + `</option>
                        `);
                    }
                }
            }else{
                $('#machine_id').append(`
                    <option value="">--Silahkan pilih Plant--</option>
                `);
            }
        }else{
            $('#machine_id').append(`
                <option value="">--Silahkan pilih Plant--</option>
            `);
        }
        $('#machine_id').formSelect();
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
                $('.row_item[data-id="' + id + '"]').remove();
                if($('.row_item').length == 0){
                    $('#body-item').empty().append(`
                        <tr id="last-row-item">
                            <td class="center-align" colspan="8">
                                Silahkan tambahkan Marketing Order Plan...
                            </td>
                        </tr>
                    `);
                    $('#marketing_order_plan_id').empty();
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

    function setRow(val){
        if($('#arr_item_detail_id' + val).val()){
            $('#arr_qty_detail' + val).val($('#arr_item_detail_id' + val).find(":selected").data("qty"));
            $('#text-unit' + val).text($('#arr_item_detail_id' + val).find(":selected").data("unit"));
            checkRow(val);
        }else{
            $('#arr_qty_detail' + val).val('0,000');
            $('#text-unit' + val).text('-');
        }
    }

    function checkRow(val){
        let mopd_id = $('#arr_item_detail_id' + val).find(":selected").data("mopd");
        let qtyMax = parseFloat($('input[name^="arr_qty[]"][data-mopd="' + mopd_id + '"]').val().toString().replaceAll(".", "").replaceAll(",","."));
        let qtyRow = parseFloat($('#arr_qty_detail' + val).val().toString().replaceAll(".", "").replaceAll(",","."));
        let otherQty = 0;
        $('select[name^="arr_item_detail_id[]"]').each(function(index){
            if($(this).val()){
                if($('#arr_item_detail_id' + val).find(":selected").data("mopd") == mopd_id && $(this).attr('id') !== 'arr_item_detail_id' + val){
                    otherQty += parseFloat($('input[name^="arr_qty_detail[]"]').eq(index).val().toString().replaceAll(".", "").replaceAll(",","."));
                }
            }
        });
        let qtyTotal = qtyRow + otherQty;
        let qtyBalance = qtyMax - otherQty;
        if(qtyTotal > qtyMax && otherQty == 0){
            $('#arr_qty_detail' + val).val(formatRupiahIni(qtyMax.toFixed(3).toString().replace('.',',')));
        }else if(otherQty >= qtyMax){
            $('#arr_qty_detail' + val).val('0,000');
        }

        if(otherQty > 0 && otherQty < qtyMax && qtyTotal > qtyMax){
            $('#arr_qty_detail' + val).val(formatRupiahIni(qtyBalance.toFixed(3).toString().replace('.',',')));
        }
    }

    function addShift(){
        if($('.row_item').length > 0){
            let arrItem = [];

            $('input[name^="arr_item_id[]"]').each(function(index){
                let arr = {
                    'mopd_id'   : $('input[name^="arr_id[]"]').eq(index).val(),
                    'mop_code'  : $('input[name^="arr_code[]"]').eq(index).val(),
                    'item_id'   : $(this).val(),
                    'item_name' : $('input[name^="arr_item_name[]"]').eq(index).val() + ' - ' + $('input[name^="arr_code[]"]').eq(index).val() + ' - ' + $('input[name^="arr_qty[]"]').eq(index).val(),
                    'qty'       : $('input[name^="arr_qty[]"]').eq(index).val(),
                    'unit'      : $('input[name^="arr_item_unit[]"]').eq(index).val(),
                };

                arrItem.push(arr);
            });

            var count = makeid(10);

            let optionItem = `<select class="browser-default" id="arr_item_detail_id` + count + `" name="arr_item_detail_id[]" onchange="setRow('` + count + `')">`;
            
            optionItem += `<option value="">--Pilih item--</option>`;

            $.each(arrItem, function(i, val) {
                optionItem += `<option value="` + val['item_id'] + `" data-mopd="` + val['mopd_id'] + `" data-qty="` + val['qty'] + `" data-unit="` + val['unit'] + `">` + val['item_name'] + `</option>`;
            });
            
            optionItem += `</select>`;

            $('#last-row-item-detail').before(`
                <tr class="row_item_detail">
                    <td>
                        <select class="browser-default item-array" id="arr_shift` + count + `" name="arr_shift[]"></select>
                    </td>
                    <td class="center-align">
                        ` + optionItem + `
                    </td>
                    <td class="center-align">
                        <input name="arr_qty_detail[]" id="arr_qty_detail` + count + `" type="text" value="0,000" onkeyup="formatRupiahNoMinus(this);checkRow('` + count + `')" required style="text-align:right;">
                    </td>
                    <td class="center-align" id="text-unit` + count + `">
                        -
                    </td>
                    <td class="center-align">
                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item-detail" href="javascript:void(0);">
                            <i class="material-icons">delete</i>
                        </a>
                    </td>
                </tr>
            `);
            select2ServerSide('#arr_shift' + count, '{{ url("admin/select2/shift") }}');
            /* $("#arr_item_detail_id" + count).select2({
                dropdownAutoWidth: true,
                width: '100%',
            }); */
        }else{
            swal({
                title: 'Ups! Hayo.',
                text: 'Silahkan tambahkan satu atau lebih Marketing Order Plan.',
                icon: 'warning'
            });
        }
    }

    function changeQty(val){
        let mopd_id = $('#arr_id' + val).val();
        let qtyUom = parseFloat($('#arr_qty' + val).val().toString().replaceAll(".", "").replaceAll(",","."));
        let qtyMax = parseFloat($('#arr_qty' + val).data('max').toString().replaceAll(".", "").replaceAll(",","."));
        if(qtyUom > qtyMax){
            qtyUom = qtyMax;
            $('#arr_qty' + val).val(formatRupiahIni(qtyMax.toFixed(3).toString().replace('.',',')));
        }
        let sell_convert = parseFloat($('#arr_sell_convert' + val).val());
        let pallet_convert = parseFloat($('#arr_pallet_convert' + val).val());
        let qtySell = qtyUom / sell_convert;
        let qtyPallet = qtySell / pallet_convert;
        $('#qty_in_sell' + val).text(formatRupiahIni(qtySell.toFixed(3).toString().replace('.',',')));
        $('#qty_in_pallet' + val).text(formatRupiahIni(qtyPallet.toFixed(3).toString().replace('.',',')));
        if($('.row_item_detail').length > 0){
            $('select[name^="arr_item_detail_id[]"]').each(function(index){
                if($(this).val()){
                    if($(this).select2().find(":selected").data("mopd") == mopd_id){
                        $('input[name^="arr_qty_detail[]"]').eq(index).val('0,000');
                    }
                }
            });
        }
    }

    function getMarketingOrderPlan(){
        if($('#marketing_order_plan_id').val()){
            let mop = $('#marketing_order_plan_id').select2('data')[0];
            $.ajax({
                url: '{{ Request::url() }}/send_used_data',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: $('#marketing_order_plan_id').val(),
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
                        if($('#last-row-item').length > 0){
                            $('#last-row-item').remove();
                        }

                        $('#list-used-data').append(`
                            <div class="chip purple darken-4 gradient-shadow white-text">
                                ` + mop.code + `
                                <i class="material-icons close data-used" data-id="` + mop.id + `" onclick="removeUsedData('` + mop.table + `','` + $('#marketing_order_plan_id').val() + `')">close</i>
                            </div>
                        `);

                        $.each(mop.details, function(i, val) {
                            var count = makeid(10);
                            $('#body-item').append(`
                                <tr class="row_item" data-id="` + mop.id + `">
                                    <input type="hidden" name="arr_id[]" id="arr_id` + count + `" value="` + val.mopd_id + `">
                                    <input type="hidden" name="arr_code[]" id="arr_code` + count + `" value="` + mop.code + `">
                                    <input type="hidden" name="arr_item_id[]" id="arr_item_id` + count + `" value="` + val.item_id + `">
                                    <input type="hidden" name="arr_item_name[]" id="arr_item_name` + count + `" value="` + val.item_name + `">
                                    <input type="hidden" name="arr_item_unit[]" id="arr_item_unit` + count + `" value="` + val.unit_uom + `">
                                    <input type="hidden" name="arr_sell_convert[]" id="arr_sell_convert` + count + `" value="` + val.sell_convert + `">
                                    <input type="hidden" name="arr_pallet_convert[]" id="arr_pallet_convert` + count + `" value="` + val.pallet_convert + `">
                                    <td>
                                        ` + mop.code + `
                                    </td>
                                    <td>
                                        ` + val.item_name + `
                                    </td>
                                    <td class="right-align">
                                        <b id="qty_in_sell` + count + `">` + val.qty_in_sell + `</b> ` + val.unit_sell + `
                                    </td>
                                    <td class="right-align">
                                        <input name="arr_qty[]" id="arr_qty` + count + `" type="text" value="` + val.qty_in_uom + `" onkeyup="formatRupiahNoMinus(this);changeQty('`+ count +`')" required style="width:75%;text-align:right;" data-mopd="` + val.mopd_id + `" data-max="` + val.qty_in_uom + `">
                                        ` + val.unit_uom + `
                                    </td>
                                    <td class="right-align">
                                        <b id="qty_in_pallet` + count + `">` + val.qty_in_pallet + `</b> ` + val.unit_pallet + `
                                    </td>
                                    <td class="center-align">
                                        ` + val.request_date + `
                                    </td>
                                    <td class="">
                                        ` + val.note + `
                                    </td>
                                    <td class="center-align">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" data-id="` + mop.id + `" href="javascript:void(0);">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                </tr>
                            `);
                        });

                        $('#marketing_order_plan_id').empty();
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

        }
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
            var poin = $(item).find('td:nth-child(3)').text().trim();
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
                { name: 'user_id', className: 'center-align' },
                { name: 'code', className: 'center-align' },
                { name: 'company_id', className: 'center-align' },
                { name: 'machine_id', className: 'center-align' },
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
                $('#date_start').val(response.start_date);
                $('#date_end').val(response.end_date);
                $('#company_id').val(response.company_id).formSelect();
                $('#place_id').val(response.place_id).formSelect();
                $('#type').val(response.type).formSelect();

                if(response.details.length > 0){
                    $('.row_item').each(function(){
                        $(this).remove();
                    });

                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        $('#last-row-item').before(`
                            <tr class="row_item">
                                <td>
                                    <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')" required></select>
                                </td>
                                <td>
                                    <input name="arr_qty[]" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinus(this)" required>
                                </td>
                                <td class="center">
                                    <span id="arr_satuan` + count + `">` + val.unit + `</span>
                                </td>
                                <td>
                                    <input name="arr_request_date[]" type="date" value="` + val.request_date + `" min="{{ date('Y-m-d') }}" required>
                                </td>
                                <td>
                                    <input name="arr_note[]" type="text" placeholder="Keterangan barang..." value="` + val.note + `" required>
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

    function duplicate(id){
        swal({
            title: "Apakah anda yakin ingin salin?",
            text: "Pastikan item yang ingin anda salin sudah sesuai!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
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
                        
                        $('#code_place_id').val(response.code_place_id).formSelect();
                        $('#code').val(response.code);
                        $('#post_date').val(response.post_date);
                        $('#date_start').val(response.start_date);
                        $('#date_end').val(response.end_date);
                        $('#company_id').val(response.company_id).formSelect();
                        $('#place_id').val(response.place_id).formSelect();
                        $('#type').val(response.type).formSelect();

                        if(response.details.length > 0){
                            $('.row_item').each(function(){
                                $(this).remove();
                            });

                            $.each(response.details, function(i, val) {
                                var count = makeid(10);
                                $('#last-row-item').before(`
                                    <tr class="row_item">
                                        <td>
                                            <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')" required></select>
                                        </td>
                                        <td>
                                            <input name="arr_qty[]" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinus(this)" required>
                                        </td>
                                        <td class="center">
                                            <span id="arr_satuan` + count + `">` + val.unit + `</span>
                                        </td>
                                        <td>
                                            <input name="arr_request_date[]" type="date" value="` + val.request_date + `" min="{{ date('Y-m-d') }}" required>
                                        </td>
                                        <td>
                                            <input name="arr_note[]" type="text" placeholder="Keterangan barang..." value="` + val.note + `" required>
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
                        }
                        
                        $('.modal-content').scrollTop(0);
                        M.updateTextFields();

                        $('#code_place_id').val(response.code_place_id).formSelect().trigger('change');
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
        });
    }

    function getRowUnit(val){
        if($("#arr_item" + val).val()){
            $('#arr_satuan' + val).text($("#arr_item" + val).select2('data')[0].sell_unit);
        }else{
            $('#arr_satuan' + val).text('-');
        }
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

    function addItem(){
        var count = makeid(10);
        $('#last-row-item').before(`
            <tr class="row_item">
                <td>
                    <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')" required></select>
                </td>
                <td>
                    <input name="arr_qty[]" type="text" value="0" onkeyup="formatRupiahNoMinus(this)" required>
                </td>
                <td class="center">
                    <span id="arr_satuan` + count + `">-</span>
                </td>
                <td>
                    <input name="arr_request_date[]" type="date" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required>
                </td>
                <td>
                    <input name="arr_note[]" type="text" placeholder="Keterangan barang..." value="-" required>
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
           
        },
        onUpdate: function (message) {
            
        },
    });

    function startIntro(){
        introJs().setOptions({
            exitOnOverlayClick : false,
            steps: [
                {
                    title : 'Marketing Order Plan',
                    intro : 'Form ini digunakan untuk mengelola data pengajuan produksi dari tim Marketing kepada tim PPIC / Produksi.'
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
                    intro : 'Plant dimana request dikirimkan.' 
                },
                {
                    title : 'Tgl. Posting',
                    element : document.querySelector('.step5'),
                    intro : 'Tanggal posting yang akan muncul pada saat dokumen dicetak, difilter atau diproses pada form lainnya.' 
                },
                {
                    title : 'Tgl. Mulai Periode',
                    element : document.querySelector('.step6'),
                    intro : 'Tanggal mulai periode perkiraan produksi.' 
                },
                {
                    title : 'Tgl. Akhir Periode',
                    element : document.querySelector('.step7'),
                    intro : 'Tanggal akhir periode perkiraan produksi.' 
                },
                {
                    title : 'Tipe Produksi',
                    element : document.querySelector('.step8'),
                    intro : 'Tanggal akhir periode perkiraan produksi.' 
                },
                {
                    title : 'File Lampiran',
                    element : document.querySelector('.step9'),
                    intro : 'Silahkan unggah file lampiran. untuk saat ini hanya bisa mengakomodir 1 file lampiran saja. Jika ingin menambahkan file lebih dari 1, silahkan gabungkan file anda menjadi pdf.' 
                },
                {
                    title : 'Detail produk',
                    element : document.querySelector('.step10'),
                    intro : 'Silahkan tambahkan produk anda disini, lengkap dengan keterangan detail tentang produk tersebut. Qty yang diisikan adalah qty dengan satuan jual.' 
                },
                {
                    title : 'Tombol Simpan',
                    element : document.querySelector('.step11'),
                    intro : 'Silahkan tekan tombol ini untuk menyimpan data, namun pastikan data yang akan anda masukkan benar.' 
                },
            ]
        }).start();
    }
</script>