<script src="{{ url('app-assets/js/sweetalert2.js') }}"></script>
<style>
    .modal {
        top:0px !important;
    }
    table > thead > tr > th {
        font-size: 13px !important;
    }
    #dropZone {
        border: 2px dashed #ccc;
    }
    #imagePreview {
        max-width: 20em;
        max-height: 20em;
        min-height: 5em;
        margin: 2px auto;
    }
    body.tab-active input:focus {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .modal-content .select2.tab-active {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
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

                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="printData();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">{{ __('translations.print') }}</span>
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
                                    <div class="collapsible-header"><i class="material-icons">filter_list</i>{{ __('translations.filter') }}</div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col m4 s12 ">
                                                <label for="filter_status" style="font-size:1rem;">Status :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_status" onchange="loadDataTable()" multiple>
                                                        <option value="1">Menunggu</option>
                                                        <option value="2">Dalam Proses</option>
                                                        <option value="3">Selesai</option>
                                                        <option value="4">Ditolak</option>
                                                        <option value="5">Ditutup</option>
                                                        <option value="6">Direvisi</option>
                                                        <option value="8">Ditutup Balik</option>
                                                        <option value="9">Dilock Procurement</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s12 ">
                                                <label for="start_date" style="font-size:1rem;">{{ __('translations.start_date') }} : </label>
                                                <div class="input-field col s12">
                                                <input type="date" max="{{ date('9999'.'-12-31') }}" id="start_date" name="start_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                            <div class="col m4 s12 ">
                                                <label for="finish_date" style="font-size:1rem;">{{ __('translations.end_date') }} :</label>
                                                <div class="input-field col s12">
                                                    <input type="date" max="{{ date('9999'.'-12-31') }}" id="finish_date" name="finish_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                            <div class="col m8 s12 ">
                                                <label for="filter_code" style="font-size:1rem;">Dari multi kode (dipisahkan tanda koma) :</label>
                                                <div class="input-field col s12">
                                                    <input type="text" placeholder="contoh : GRPO-24P1-00000001,GRPO-24P1-00000003,GRPO-24P1-00000003" id="filter_code" name="filter_code" onkeyup="loadDataTable()">
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
                                                <span class="hide-on-small-onl">{{ __('translations.refresh') }}</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-2" href="javascript:void(0);" onclick="exportExcel();">
                                                <i class="material-icons hide-on-med-and-up">view_headline</i>
                                                <span class="hide-on-small-onl">Export</span>
                                                <i class="material-icons right">view_headline</i>
                                            </a>
                                            <table id="datatable_serverside" >
                                                <thead>
                                                    <tr>
                                                        <th >#</th>
                                                        <th >{{ __('translations.code') }}</th>
                                                        <th >{{ __('translations.user') }}</th>
                                                        <th >Tipe Pembayaran</th>
                                                        <th  class="center-align">{{ __('translations.date') }}</th>
                                                        <th  class="center-align">Keterangan</th>
                                                        <th >Subtotal</th>
                                                        <th >Grandtotal</th>
                                                        <th >Lampiran</th>
                                                        <th >{{ __('translations.status') }}</th>
                                                        <th >Operasi</th>
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
                <h5>{{ __('translations.add') }}/{{ __('translations.edit') }} {{ $title }}</h5>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="row">
                            <div class="input-field col m2 s12 step1">
                                <input id="code" name="code" type="text" value="{{ $newcode }}" readonly>
                                <label class="active" for="code">No. Dokumen</label>
                            </div>
                            <div class="col m12 s12"></div>
                            <div class="input-field col m3 s12 div-account">
                                <input type="hidden" id="temp" name="temp">
                                <select class="browser-default" id="customer_id" name="customer_id" onchange="resetDetails();"></select>
                                <label class="active" for="customer_id">Customer</label>
                            </div>
                            <div class="input-field col m3 s12 step9">
                                <select class="form-control" id="payment_type" name="payment_type">
                                    <option value="1">Cash</option>
                                    <option value="2">Credit</option>
                                </select>
                                <label class="" for="payment_type">Tipe Pembayaran</label>
                            </div>
                            <div class="input-field col m3 s12 step6">
                                <input id="post_date" name="post_date" type="date" placeholder="Tgl. diterima" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);">
                                <label class="active" for="post_date">Tgl. Post</label>
                            </div>
                            <div class="col s12"></div>
                            <div class="col m4 s12 step10">
                                <label class="">Bukti Upload</label>
                                <br>
                                <input type="file" name="file" id="fileInput" accept="image/*" style="display: none;">
                                <div  class="col m8 s12 " id="dropZone" ondrop="dropHandler(event);" ondragover="dragOverHandler(event);" style="margin-top: 0.5em;height: 5em;">
                                    Drop image here or <a href="javascript:void(0);" id="uploadLink">upload</a>
                                    <br>

                                </div>
                                <a class="waves-effect waves-light cyan btn-small" style="margin-top: 0.5em;margin-left:0.2em" id="clearButton" href="javascript:void(0);">
                                   Clear
                                </a>
                            </div>
                            <div class="col m4 s12">
                                <div id="fileName"></div>
                                <img src="" alt="Preview" id="imagePreview" style="display: none;">
                            </div>
                            <div class="col m12 s12 step12">
                                <p class="mt-2 mb-2">
                                    <h5>Detail Produk</h5>
                                    <div style="overflow:auto;">
                                        <table class="bordered" style="min-width:3100px !important;" id="table-detail">
                                            <thead>
                                                <tr>
                                                    <th class="center">{{ __('translations.delete') }}</th>
                                                    <th class="center">{{ __('translations.item') }}</th>
                                                    <th class="center">Qty</th>
                                                    <th class="center">Harga</th>
                                                    <th class="center">Total</th>
                                                    <th class="center">Diskon</th>
                                                    <th class="center">Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-item">
                                                <tr id="empty-item">
                                                    <td colspan="8" class="center">
                                                        Pilih purchase order untuk memulai...
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <thead>
                                                <th colspan="6">TOTAL</th>
                                                <th class="right-align" id="total-received">0,000</th>
                                                <th colspan="1"></th>
                                            </thead>
                                        </table>
                                    </div>
                                </p>
                            </div>
                            <div class="input-field col m4 s12 step14">
                                <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note">{{ __('translations.note') }}</label>
                            </div>
                            <div class="col m4 s12">

                            </div>
                            <div class="col m4 s12 mt-1 step14" >

                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1 right" onclick="addItem()" href="javascript:void(0);">
                                    <i class="material-icons left">add</i> Tambah 1
                                </a>
                            </div>
                            <div class="col s12 mt-3">
                                <button class="btn waves-effect waves-light right submit step15" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">

        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
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
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
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
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>


<div id="modal3" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content row">
        <div class="col s12" id="show_structure">
            <div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;">

            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">{{ __('translations.close') }}</a>
    </div>
</div>


<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div>


<!-- END: Page Main-->
<script>
    const dropZone = document.getElementById('dropZone');
    const uploadLink = document.getElementById('uploadLink');
    const fileInput = document.getElementById('fileInput');
    const imagePreview = document.getElementById('imagePreview');
    const clearButton = document.getElementById('clearButton');
    const fileNameDiv = document.getElementById('fileName');
    dropZone.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', (e) => {
        handleFile(e.target.files[0]);
    });

    function dragOverHandler(event) {
        event.preventDefault();
        dropZone.style.backgroundColor = '#f0f0f0';
    }

    function dropHandler(event) {
        event.preventDefault();
        dropZone.style.backgroundColor = '#fff';

        handleFile(event.dataTransfer.files[0]);
    }

    function handleFile(file) {
        if (file) {
        const reader = new FileReader();
        const fileType = file.type.split('/')[0];
        const maxSize = 10 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('File size exceeds the maximum limit of 10 MB.');
            return;
        }

        reader.onload = () => {

            fileNameDiv.textContent = 'File uploaded: ' + file.name;

            if (fileType === 'image') {

                imagePreview.src = reader.result;
                imagePreview.style.display = 'inline-block';
                clearButton.style.display = 'inline-block';
            } else {

                imagePreview.style.display = 'none';

            }
        };

        reader.readAsDataURL(file);
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);


        fileInput.files = dataTransfer.files;

        }
    }

    clearButton.addEventListener('click', () => {
        imagePreview.src = '';
        imagePreview.style.display = 'none';
        fileInput.value = '';
        fileNameDiv.textContent = '';
    });

    document.addEventListener('paste', (event) => {
        const items = event.clipboardData.items;
        if (items) {
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    const file = items[i].getAsFile();
                    handleFile(file);
                    break;
                }
            }
        }
    });

    function displayFile(fileLink) {
        const fileType = getFileType(fileLink);

        fileNameDiv.textContent = 'File uploaded: ' + getFileName(fileLink);

        if (fileType === 'image') {

            imagePreview.src = fileLink;
            imagePreview.style.display = 'inline-block';

        } else {

            imagePreview.style.display = 'none';


            const fileExtension = getFileExtension(fileLink);
            if (fileExtension === 'pdf' || fileExtension === 'xlsx' || fileExtension === 'docx') {

                const downloadLink = document.createElement('a');
                downloadLink.href = fileLink;
                downloadLink.download = getFileName(fileLink);
                downloadLink.textContent = 'Download ' + fileExtension.toUpperCase();
                fileNameDiv.appendChild(downloadLink);
            }
        }
    }

    function getFileType(fileLink) {
        const fileExtension = getFileExtension(fileLink);
        if (fileExtension === 'jpg' || fileExtension === 'jpeg' || fileExtension === 'png' || fileExtension === 'gif') {
            return 'image';
        } else {
            return 'other';
        }
    }

    function getFileExtension(fileLink) {
        return fileLink.split('.').pop().toLowerCase();
    }

    function getFileName(fileLink) {
        return fileLink.split('/').pop();
    }
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

    var arrpod = [];

    $(function() {
        $("#table-detail th").resizable({
            minWidth: 100,
        });

        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        $('#datatable_serverside').on('click', 'button', function(event) {
            event.stopPropagation();

        });

        loadDataTable();

        $('#modal4').modal({
            onOpenStart: function(modal,trigger) {

            },
            onOpenEnd: function(modal, trigger) {
            },
            onCloseEnd: function(modal, trigger){
                $('#show_detail').empty();
            }
        });

        select2ServerSide('#customer_id', '{{ url("admin/select2/customer") }}');
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {

                getCode();
            },
            onOpenEnd: function(modal, trigger) {
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                clearButton.click();
                $('#form_data')[0].reset();
                $('#temp').val('');
                $('.row_item').each(function(){
                    $(this).remove();
                });
                $('.row_item_serial').each(function(){
                    $(this).remove();
                });
                if($('#empty-item').length == 0){
                    $('#body-item').append(`
                        <tr id="empty-item">
                            <td colspan="23" class="center">
                                Pilih purchase order untuk memulai...
                            </td>
                        </tr>
                    `);
                }
                $('#purchase_order_id,#good_scale_id,#purchase_order_detail_id').empty();
                $('#customer_id').empty();
                M.updateTextFields();
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }
                window.onbeforeunload = function() {
                    return null;
                };
                $('#total-received').text('0,000');
                arrpod = [];
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

        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
            let id = $(this).data('detail');
            if($('.row_item').length == 0){
                $('#body-item').append(`
                    <tr id="empty-item">
                        <td colspan="8" class="center">
                            Harap Tambah Item Detail terlebih dahulu
                        </td>
                    </tr>
                `);
                countAll();
                fillInArray();
            }
        });
    });

    function countAll(){
        let total = 0;
        let qty_total = 0;
        $('input[name^="arr_qty[]"]').each(function(){
            qty_total += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
        });
        $('.total-column').each(function () {
            let value = $(this).text().replaceAll(".", "").replaceAll(",", "."); // Convert to a valid number format
            let numericValue = parseFloat(value);
            if (!isNaN(numericValue)) {
                total += numericValue;
            }
        });
        $('#total-received').text(formatRupiahIni(total.toFixed(3).toString().replace('.',',')));

    }

    function resetDetails(){
        if($('.data-used').length > 0){
            $('.data-used').trigger('click');
        }else{
            $('.row_item').each(function(){
                $(this).remove();
            });
            if($('#empty-item').length == 0){
                $('#body-item').append(`
                    <tr id="empty-item">
                        <td colspan="8" class="center">
                            Pilih purchase order untuk memulai...
                        </td>
                    </tr>
                `);
            }
        }
    }

    String.prototype.replaceAt = function(index, replacement) {
        return this.substring(0, index) + replacement + this.substring(index + replacement.length);
    };

    function getCode(){
        if($('#temp').val()){
            console.log('ddd');
        }else{
            if($('#code').val().length > 7){
                $('#code').val($('#code').val().slice(0, 7));
            }
            $.ajax({
                url: '{{ Request::url() }}/get_code',
                type: 'POST',
                dataType: 'JSON',
                data: {
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

    function changeDateMinimum(val){
        if(val){
            if(!$('#temp').val()){
                let newcode = $('#code').val().replaceAt(5,val.split('-')[0].toString().substr(-2));
                if($('#code').val().substring(5, 7) !== val.split('-')[0].toString().substr(-2)){
                    if(newcode.length > 9){
                        newcode = newcode.substring(0, 9);
                    }
                }
                $('#code').val(newcode);
            }
            $('#code_place_id').trigger('change');
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
            e.diagram.nodes.each(node => {
                node.findTreeChildrenNodes().each(child => child.expandTree(10));
            });
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
            }, beforeSend: function() {
                loadingOpen('#main');
            },
            success: function(response) {
                loadingClose('#main');
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

    function simpleStructrueTree(id){
        $.ajax({
            url: '{{ Request::url() }}/simplestructuretree',
            type: 'GET',
            dataType: 'JSON',
            data: {
                id : id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('#main');
            },
            success: function(response) {
                loadingClose('#main');

                makeTreeOrg(response.message,response.link);

                $('#modal3').modal('open');
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
                    'status' : $('#filter_status').val(),
                    start_date : $('#start_date').val(),
                    finish_date : $('#finish_date').val(),
                    codes : $('#filter_code').val(),
                    'modedata' : '{{ $modedata }}',
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
                { name: 'company_id', className: 'center-align' },
                { name: 'type', className: 'center-align' },
                { name: 'receiver', className: 'center-align' },
                { name: 'date_post', className: 'center-align' },
                { name: 'date_post', className: 'center-align' },
                { name: 'document', searchable: false, orderable: false, className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'operation', searchable: false, orderable: false, className: 'center-align' },
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

                if($('#type').val() == '2'){
                    let totalScale = parseFloat($('#goodScaleQtyMax').text().replaceAll(".", "").replaceAll(",","."));
                    let totalReceived = 0;

                    $('.arr_qty_gs').each(function(index){
                        if($(this).text() == 'YA'){
                            totalReceived += parseFloat($('input[name^="arr_qty[]"]').eq(index).val().replaceAll(".", "").replaceAll(",","."));
                        }else{
                            totalReceived += (totalScale - totalReceived);
                        }
                    });

                    if(totalScale != totalReceived){
                        M.toast({
                            html: 'Mohon maaf untuk tipe Timbangan, nominal BERAT TIMBANG harus sama dengan total Qty Diterima. Qty Timbang : ' + totalScale + ', sedangkan Qty PO Diterima (item Timbang) : ' + totalReceived.toFixed(3)
                        });
                        return false;
                    }
                }

                var formData = new FormData($('#form_data')[0]), passedSerial = true;

                formData.delete("arr_department[]");
                formData.delete("arr_line[]");
                formData.delete("arr_machine[]");
                formData.delete("arr_scale[]");
                formData.delete("arr_serial[]");

                $('input[name^="arr_department"]').each(function(index){
                    formData.append('arr_department[]',($(this).val() ? $(this).val() : ''));
                });

                $('input[name^="arr_line"]').each(function(index){
                    formData.append('arr_line[]',($(this).val() ? $(this).val() : ''));
                });

                $('input[name^="arr_machine"]').each(function(index){
                    formData.append('arr_machine[]',($(this).val() ? $(this).val() : ''));
                });

                $('input[name^="arr_scale"]').each(function(index){
                    formData.append('arr_scale[]',($(this).val() ? $(this).val() : ''));
                });

                if($('input[name^="arr_serial[]"]').length > 0){
                    $('input[name^="arr_serial[]"]').each(function(index){
                        if(!$(this).val()){
                            passedSerial = false;
                        }else{
                            formData.append('arr_serial[]',$(this).val());
                            formData.append('arr_serial_item[]',$(this).data('item'));
                            formData.append('arr_serial_po[]',$(this).data('po'));
                        }
                    });
                }

                if(passedSerial){
                    var path = window.location.pathname;
                    path = path.replace(/^\/|\/$/g, '');


                    var segments = path.split('/');
                    var lastSegment = segments[segments.length - 1];

                    formData.append('lastsegment',lastSegment);

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
                            $('#modal1').scrollTop(0);
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
                        text: 'Nomor serial item tidak boleh kosong.',
                        icon: 'error'
                    });
                }
            }
        });
    }

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
    }

    function fillInArray(){
        arrpod = [];
        $('input[name^="arr_purchase[]"]').each(function(){
            arrpod.push($(this).val());
        });
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
                            <td colspan="8" class="center">
                                Pilih purchase order untuk memulai...
                            </td>
                        </tr>
                    `);
                }

                countAll();
                fillInArray();
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

        let countItem = $('.row_item').length;

        if(countItem > 59){
            swal({
                title: 'Ups!',
                text: 'Satu PR tidak boleh memiliki baris item lebih dari 60.',
                icon: 'error'
            });
            return false;
        }

        $('#empty-item').remove();
        var count = makeid(10);
        $('#body-item').append(`
            <tr class="row_item" data-id="">
                <input type="hidden" name="arr_lookable_type[]" value="">
                <input type="hidden" name="arr_lookable_id[]" value="">

                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
                <td>
                    <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                </td>
                <td>
                    <input name="arr_qty[]" onfocus="emptyThis(this);" id="rowQty` + count + `" type="text" value="0" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')">
                </td>
                <td>
                    <input name="arr_price[]" onfocus="emptyThis(this);" id="rowPrice` + count + `" type="text" value="0" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')">
                </td>
                <td class="center total-column" id="total` + count + `">
                    <input name="arr_total[]" onfocus="emptyThis(this);" id="arr_total` + count + `" type="text" value="0" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" hidden>

                </td>
                <td>
                    <input name="arr_discount[]" onfocus="emptyThis(this);" id="rowDiscount` + count + `" type="text" value="0" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')">
                </td>
                <td>
                    <input name="arr_note[]" type="text" placeholder="Keterangan...">
                </td>
            </tr>
        `);
        $('#arr_item'+ count).select2({
            placeholder: '-- Pilih ya --',
            minimumInputLength: 4,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/purchase_item") }}',
                type: 'GET',
                dataType: 'JSON',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true,
            }
        });

    }

    function getRowUnit(val){
        $("#arr_warehouse" + val).empty();
        $("#unit_stock" + val).empty();
        $("#qty_stock" + val).empty().text('-');
        if($("#arr_item" + val).val()){
            $("#unit_stock" + val).text($("#arr_item" + val).select2('data')[0].uom);
        }else{
            $("#arr_item" + val).empty();
            $("#arr_warehouse" + val).append(`
                <option value="">--Silahkan pilih item--</option>
            `);
            $("#unit_stock" + val).text('-');
        }
        countRow(val);
    }

    function countRow(id){
        if($('#arr_item' + id).val()){
            var qty = parseFloat($('#rowQty' + id).val().replaceAll(".", "").replaceAll(",","."));
            var price = parseFloat($('#rowPrice' + id).val().replaceAll(".", "").replaceAll(",","."));
            var discount = parseFloat($('#rowDiscount' + id).val().replaceAll(".", "").replaceAll(",","."));

            var total = (qty * price )- discount;

            $('#arr_total' + id).val(formatRupiahIni(total.toFixed(3).toString().replace('.',',')));
            $('#total' + id).text(formatRupiahIni(total.toFixed(3).toString().replace('.',',')));
            countAll()
        }
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

            console.log($('#temp').val());
                $('#code_place_id').val(response.code_place_id).formSelect();
                $('#code').val(response.code);
                $('#type').val(response.type).formSelect();
                if(response.customer_id){
                    $('#customer_id').empty().append(`
                        <option value="` + response.customer_id + `">` + response.customer_name + `</option>
                    `);
                }
                $('#note').val(response.note);
                $('#receiver_name').val(response.receiver_name);
                $('#post_date').val(response.post_date);
                $('#document_date').val(response.document_date);
                $('#delivery_no').val(response.delivery_no);

                if(response.details.length > 0){
                    $.each(response.details, function(i, val) {
                        var count = makeid(10);
                        arrpod.push(val.purchase_order_detail_id);
                        $('#body-item').append(`
                            <tr class="row_item" data-id="">
                                <input type="hidden" name="arr_lookable_type[]" value="">
                                <input type="hidden" name="arr_lookable_id[]" value="">

                                <td class="center">
                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                                <td>
                                    <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                                </td>
                                <td>
                                    <input name="arr_qty[]" onfocus="emptyThis(this);" id="rowQty` + count + `" type="text" value="` + val.qty + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')">
                                </td>
                                <td>
                                    <input name="arr_price[]" onfocus="emptyThis(this);" id="rowPrice` + count + `" type="text" value="` + val.price + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')">
                                </td>
                                <td class="center total-column" id="total` + count + `">
                                    <input name="arr_total[]" onfocus="emptyThis(this);" id="arr_total` + count + `" type="text" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" hidden>

                                </td>
                                <td>
                                    <input name="arr_discount[]" onfocus="emptyThis(this);" id="rowDiscount` + count + `" type="text" value="` + val.discount3 + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')">
                                </td>
                                <td>
                                    <input name="arr_note[]" type="text" placeholder="Keterangan..." value="` + val.note + `">
                                </td>
                            </tr>

                        `);

                        $('#arr_item' + count).append(`
                            <option value="` + val.item_id + `">` + val.item_name + `</option>
                        `);
                        $('#arr_item'+ count).select2({
                            placeholder: '-- Pilih ya --',
                            minimumInputLength: 4,
                            allowClear: true,
                            cache: true,
                            width: 'resolve',
                            dropdownParent: $('body').parent(),
                            ajax: {
                                url: '{{ url("admin/select2/purchase_item") }}',
                                type: 'GET',
                                dataType: 'JSON',
                                delay: 250,
                                data: function(params) {
                                    return {
                                        search: params.term,
                                        page: params.page || 1
                                    };
                                },
                                processResults: function(data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: data.items,
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true,
                            }
                        });
                    });
                }

                countAll();
                if(response.document){
                    const baseUrl = '{{ URL::to("/") }}/storage/';
                    const filePath = response.document.replace('public/', '');
                    const fileUrl = baseUrl + filePath;
                    displayFile(fileUrl);
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
        var msg = '';
        swal({
            title: "Alasan mengapa anda menghapus!",
            text: "Anda tidak bisa mengembalikan data yang telah dihapus.",
            buttons: true,
            content: "input",
        })
        .then(message => {
            if (message != "" && message != null) {
                $.ajax({
                    url: '{{ Request::url() }}/destroy',
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

    function printData(){
        var arr_id_temp=[];
        $.map(window.table.rows('.selected').nodes(), function (item) {
            var poin = $(item).find('td:nth-child(5)').text().trim();
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

    function printPreview(code,aslicode){
        swal({
            title: "Apakah Anda ingin mengeprint dokumen ini?",
            text: "Dengan Kode "+aslicode,
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
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
        });

    }

    function whatPrinting(code){
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
                window.open(data, '_blank');
            }
        });
    }

    function done(id){
        var msg = '';
        swal({
            title: "Apakah anda yakin ingin menyelesaikan dokumen ini?",
            text: "Data yang sudah terupdate tidak dapat dikembalikan.",
            icon: 'warning',
            dangerMode: true,
            buttons: true,
            content: "input",
        })
        .then(message => {
            if (message != "" && message != null) {
                $.ajax({
                    url: '{{ Request::url() }}/done',
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        id: id,
                        msg : message
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        loadingOpen('#main');
                    },
                    success: function(response) {
                        loadingClose('#main');
                        if(response.status == 200) {
                            loadDataTable();
                            M.toast({
                                html: response.message
                            });
                        } else {
                            M.toast({
                                html: response.message
                            });
                        }
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

    function exportExcel(){
        var search = table.search();
        var status = $('#filter_status').val();
        var start_date = $('#start_date').val();
        var end_date = $('#finish_date').val();
        var modedata = '{{ $modedata }}';

        window.location = "{{ Request::url() }}/export_from_page?search=" + search + "&status=" + status + "&end_date=" + end_date + "&start_date=" + start_date + "&modedata=" + modedata;

    }

    function cancelStatus(id){
        Swal.fire({
            title: "Pilih tanggal tutup!",
            input: "date",
            showCancelButton: true,
            confirmButtonText: "Lanjut",
            cancelButtonText: "Batal",
            cancelButtonColor: "#d33",
            confirmButtonColor: "#3085d6",
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: '{{ Request::url() }}/cancel_status',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { id : id, cancel_date : result.value },
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
