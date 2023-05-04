<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/orgchart/3.4.0/css/jquery.orgchart.css" integrity="sha512-uZZTHC0DOuA/6dYv9ZiCIfEFAyRqFgFFqEnDQVrTS3QG6tvefQ5uoFpVvpqbuTUvZWY/9pm/UyuPYDBVDFnGog==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
    #chart-container {
        position: relative;
        height: 420px;
        border: 1px solid #aaa;
        margin: 0.5rem;
        overflow: auto;
        text-align: center;
    }
</style>
    <!-- BEGIN: Page Main-->
<div id="main">
    <div class="row">
        <div class="content-wrapper-before blue-grey lighten-5"></div>
        <div class="col s12">
            <div class="container">
                <div class="section">
                    
                    <div class="row">
                        <div class="col s12 m6 l4">
                            <div class="card padding-4 animate fadeLeft">
                                <div class="row">
                                    <div class="col s5 m5">
                                        <h5 class="mb-0">1885</h5>
                                        <p class="no-margin">New</p>
                                        <p class="mb-0 pt-8">1,12,900</p>
                                    </div>
                                    <div class="col s7 m7 right-align">
                                        <i class="material-icons background-round mt-5 mb-5 gradient-45deg-purple-amber gradient-shadow white-text">perm_identity</i>
                                        <p class="mb-0">Total Clients</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col s12 m6 l4">
                            <div class="card padding-4 animate fadeLeft">
                                <div class="row">
                                    <div class="col s5 m5">
                                        <h5 class="mb-0">1885</h5>
                                        <p class="no-margin">New</p>
                                        <p class="mb-0 pt-8">1,12,900</p>
                                    </div>
                                    <div class="col s7 m7 right-align">
                                        <i class="material-icons background-round mt-5 mb-5 gradient-45deg-purple-amber gradient-shadow white-text">perm_identity</i>
                                        <p class="mb-0">Total Clients</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col s12 m6 l4">
                            <div class="card padding-4 animate fadeLeft">
                                <div class="row">
                                    <div class="col s5 m5">
                                        <h5 class="mb-0">1885</h5>
                                        <p class="no-margin">New</p>
                                        <p class="mb-0 pt-8">1,12,900</p>
                                    </div>
                                    <div class="col s7 m7 right-align">
                                        <i class="material-icons background-round mt-5 mb-5 gradient-45deg-purple-amber gradient-shadow white-text">perm_identity</i>
                                        <p class="mb-0">Total Clients</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col s12 m12 l12">
                            <div class="card padding-4 animate fadeLeft">
                                <div class="row">
                                    <div class="col s12 m12">
                                        <h5>TRIAL HARGA RATA-RATA / COGS / HPP</h5>
                                        <div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;">

                                        </div>
                                    </div>
                                    <div class="col s12 m12">
                                        <h5>TRIAL STOK REALTIME</h5>
                                        <table class="bordered" style="font-size:10px;">
                                            <thead>
                                                <tr>
                                                    <th class="center-align">No.</th>
                                                    <th class="center-align">Item</th>
                                                    <th class="center-align">Site</th>
                                                    <th class="center-align">Gudang</th>
                                                    <th class="center-align">Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($itemstocks as $key => $row)
                                                    <tr>
                                                        <td class="center-align">{{ ($key + 1) }}</td>
                                                        <td class="center-align">{{ $row->item->name }}</td>
                                                        <td class="center-align">{{ $row->place->name.' - '.$row->place->company->name }}</td>
                                                        <td class="center-align">{{ $row->warehouse->name }}</td>
                                                        <td class="center-align">{{ number_format($row->qty,3,',','.').' '.$row->item->uomUnit->code }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="intro">
                    <div class="row">
                        <div class="col s12">
                            
                        </div>
                    </div>
                </div>
                <!-- / Intro -->
            </div>
            <div class="content-overlay"></div>
        </div>
    </div>
</div>

<script id="code">
    function init() {
      const $ = go.GraphObject.make;

      myDiagram =
        $(go.Diagram, "myDiagramDiv",
          {
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

      myDiagram.nodeTemplate =
        $(go.Node, "Auto",
            {
            locationSpot: go.Spot.Center,
            fromSpot: go.Spot.AllSides,
            toSpot: go.Spot.AllSides
            },
            $(go.Shape, { fill: "lightyellow" }),
            $(go.Panel, "Table",
            { defaultRowSeparatorStroke: "black" },
        
            $(go.TextBlock,
                {
                row: 0, columnSpan: 2, margin: 3, alignment: go.Spot.Center,
                font: "bold 12pt sans-serif",
                isMultiline: false, editable: true
                },
                new go.Binding("text", "name").makeTwoWay()),
            
            $(go.TextBlock, "Properties",
                { row: 1, font: "italic 10pt sans-serif" },
                new go.Binding("visible", "visible", v => !v).ofObject("PROPERTIES")),
            $(go.Panel, "Vertical", { name: "PROPERTIES" },
                new go.Binding("itemArray", "properties"),
                {
                row: 1, margin: 3, stretch: go.GraphObject.Fill,
                defaultAlignment: go.Spot.Left, background: "lightyellow",
                
                }
            ),
            $("PanelExpanderButton", "PROPERTIES",
                { row: 1, column: 1, alignment: go.Spot.TopRight, visible: false },
                new go.Binding("visible", "properties", arr => arr.length > 0)),
            
            $(go.TextBlock, "Methods",
                { row: 2, font: "italic 10pt sans-serif" },
                new go.Binding("visible", "visible", v => !v).ofObject("METHODS")),
            $(go.Panel, "Vertical", { name: "METHODS" },
                new go.Binding("itemArray", "methods"),
                {
                row: 2, margin: 3, stretch: go.GraphObject.Fill,
                defaultAlignment: go.Spot.Left, background: "lightyellow",
                
                }
            ),
            $("PanelExpanderButton", "METHODS",
                { row: 2, column: 1, alignment: go.Spot.TopRight, visible: false },
                new go.Binding("visible", "methods", arr => arr.length > 0))
            )
        );



     

      var nodedata = [
        {
          key: 1,
          name: "BankAccount",
          properties: [
            { name: "owner", type: "String" },
            { name: "balance", type: "Currency", default: "0" }
          ],
          methods: [
            { name: "deposit", parameters: [{ name: "amount", type: "Currency" }] },
            { name: "withdraw", parameters: [{ name: "amount", type: "Currency" }] }
          ]
        },
        {
          key: 11,
          name: "Person",
          properties: [
            { name: "name", type: "String" },
            { name: "halo", type: "Date" }
          ],
          methods: [
            { name: "getCurrentAge", type: "int" }
          ]
        },
        {
          key: 12,
          name: "Student",
          properties: [
            { name: "classes", type: "List<Course>"}
          ],
          methods: [
            { name: "attend", parameters: [{ name: "class", type: "Course" }]},
            { name: "sleep" }
          ]
        },
        {
          key: 13,
          name: "Professor",
          properties: [
            { name: "classes", type: "List<Course>" }
          ],
          methods: [
            { name: "teach", parameters: [{ name: "class", type: "Course" }] }
          ]
        },
        {
          key: 14,
          name: "Course",
          properties: [
            { name: "name", type: "String" },
            { name: "description", type: "String" },
            { name: "professor", type: "Professor" },
            { name: "location", type: "String" },
            { name: "times", type: "List<Time>"},
            { name: "prerequisites", type: "List<Course>" },
            { name: "students", type: "List<Student>" }
          ]
        }
      ];
      var linkdata = [
        { from: 12, to: 11, relationship: "generalization" },
        { from: 13, to: 11, relationship: "generalization" },
        { from: 14, to: 1, relationship: "generalization" },
        { from: 14, to: 11, relationship: "generalization" }
      ];
      myDiagram.model = new go.GraphLinksModel(
        {
          copiesArrays: true,
          copiesArrayObjects: true,
          nodeDataArray: nodedata,
          linkDataArray: linkdata
        });
    
    }

    window.addEventListener('DOMContentLoaded', init);
</script>