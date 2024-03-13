<style>
    .modal {
        top:0px !important;
    }
    table > thead > tr > th {
        font-size: 13px !important;
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
    .select2-selection { overflow: hidden; }

</style>
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
                                                        <th >#</th>
                                                        <th >Kode Req. Sparepart</th>
                                                        <th >Request By</th>
                                                        <th >WO Code</th>
                                                        <th>Area</th>
                                                        <th >Request Date</th>
                                                        <th >Summary Issue</th>
                                                        <th  class="center-align">Status</th>
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
                            <div class="input-field col m5 s12">
                                <input type="hidden" id="temp" name="temp">
                                <select class="browser-default" id="work_order_id" name="work_order_id" onchange="getWO_info();removeBodydetail()">&nbsp;</select>
                                <label class="active" for="work_order_id">Work Order</label>
                            </div>
                            <div class="input-field col m2 s12">
                                
                            </div>
                            <div class="input-field col m5 s12">
                                <input type="hidden" id="user_id" name="user_id" value="">
                                <input type="text" placeholder="Nama Peng-request" id="user_name" value="" readonly>
                                <label class="active" for="request_by">Requester WO</label>
                            </div>
                            <div class="input-field col m5 s12">
                                <input id="equipment_id" name="equipment_id"  type="text" placeholder="Equipment Name" readonly>
                                <label class="active" for="suggested_completion_date">Equipment</label>
                            </div>
                            <div class="input-field col m2 s12">
                                
                            </div>
                            <div class="input-field col m5 s12">
                                <input id="request_date" name="request_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. dokumen" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);">
                                <label class="active" for="request_date">Tgl. Request</label>
                            </div>
                            <div class="input-field col m5 s12">
                                <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note">Detail Issue</label>
                            </div>
                            <div class="input-field col m2 s12">
                                
                            </div>
                            <div class="input-field col m4 s12">
                                <input id="code" name="code" type="text" value="{{ $newcode }}" readonly>
                                <label class="active" for="code">No. Dokumen</label>
                            </div>
                            <div class="input-field col m1 s12">
                                <select class="form-control" id="code_place_id" name="code_place_id" onchange="getCode(this.value);">
                                    <option value="">--Pilih--</option>
                                    @foreach ($place as $rowplace)
                                        <option value="{{ $rowplace->code }}">{{ $rowplace->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col m12 s12">
                                <div style="overflow:auto;">
                                    <table class="bordered" style="max-width:1650px !important;">
                                        <thead>
                                            <tr>
                                                <th class="center" colspan="1">Nama Part</th>
                                                <th class="center" colspan="7">Informasi Part</th>
                                            </tr>
                                            <tr class="row_header_part">
                                                <td>
                                                    Kode Equipment Part
                                                </td>
                                                <td>
                                                    Nama Sparepart
                                                </td>
                                                <td>
                                                    Stock
                                                </td>
                                                <td>
                                                    Qty Request
                                                </td>
                                                <td>
                                                    Qty Return
                                                </td>
                                                <td>
                                                    Qty Usage
                                                </td>
                                                <td>
                                                    Qty Repair
                                                </td>
                                                <td>
                                                    Action
                                                </td>
                                            </tr>
                                        </thead>
                                        <tbody id="body-detail">
                                            <tr id="empty-detail">
                                                <td colspan="10" class="center">
                                                   Pilih WO untuk menampilkan Equipment part...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
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


<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div>


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

        window.table.search('{{ $code }}').draw();

        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
              
            },
            onOpenEnd: function(modal, trigger) {
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
                window.onbeforeunload = function() {
                    return 'You will lose all changes made since your last save';
                };
            },
            onCloseEnd: function(modal, trigger){
                $('#btn_add_sparepart').prop('disabled', false);
                $('#form_data')[0].reset();
                $('#temp').val('');
                M.updateTextFields();
                $('#body-detail').empty();
                $("#work_order_id").empty();
                $('.row_detail').each(function(){
                    $(this).remove();
                });
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
        $('#modal4').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#show_detail').empty();
            }
        });

        select2ServerSide('#work_order_id', '{{ url("admin/select2/workorder") }}');
       
        $('#body-item').on('click', '.delete-data-item', function() {
            $(this).closest('tr').remove();
            
            if($('.row_item').length == 0){
                $('#body-item').append(`
                    <tr id="empty-item">
                        <td colspan="8" class="center">
                            Pilih purchase order untuk memulai...
                        </td>
                    </tr>
                `);
                $('#purchase_order_id').empty();
            }
        });
    });

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

    function changeDateMinimum(val){
        if(val){
            let newcode = $('#code').val().replaceAt(5,val.split('-')[0].toString().substr(-2));
            if($('#code').val().substring(5, 7) !== val.split('-')[0].toString().substr(-2)){
                if(newcode.length > 9){
                    newcode = newcode.substring(0, 9);
                }
            }
            $('#code').val(newcode);
            $('#code_place_id').trigger('change');
        }
    }

    list_sparepart=[];
    temp_spareparts=[];
    function removeBodydetail(){
        $('#body-detail').empty();
    }

    function takeSparepart(count){

        $('#arr_item'+count).empty();
        $('#arr_item' + count).append(`
            <option>Silahkan pilih sparepart</option>
        `);
        var temp_val = $('#equipmentpart'+count).val();
        var temp_sparepart = list_sparepart[temp_val].sparepart;
        $.each(temp_sparepart, function(index, value) {
            $('#arr_item' + count).append(`
                <option value="` + index + `">` + value.name + `</option>
            `);
        });
        $('#arr_item'+count).select2(
        {
            placeholder: "Kosong untuk semua tipe.",
            dropdownAutoWidth: true,
            width: '100%',
        });
    }

    function add_sparepart(){
        if($('#work_order_id').val()){
           
            $('#empty-detail').remove();
            var count = makeid(10);
            
            
            $('#body-detail').append(`
                <tr class="row_detail">
                    <td>
                        <select class="browser-default select2" id="equipmentpart` + count + `" name="equipmentpart" onchange="takeSparepart('` + count + `')">
                                    
                        </select>
                    </td>
                    <td id="select_item` + count + `">
        
                    </td>
                    <td>
                        
                        <select class="browser-default select2" id="arr_stock` + count + `" name="arr_stock[]">
                            <option>Silahkan pilih stock</option>
                        </select>
                    </td>
                    <td>
                        <input name="arr_qty_req[]" id="arr_qty_req`+ count +`" type="text" value="0">
                    </td>
                    <td>
                        <input name="arr_qty_return[]" id="arr_qty_return`+ count +`"  type="text" readonly>
                    </td>  
                    <td>
                        <input name="arr_qty_usage[]" id="arr_qty_usage`+ count +`" type="text" readonly>
                    </td>  
                    <td>
                        <input name="arr_qty_repair[]" id="arr_qty_repair`+ count +`" type="text" readonly>
                    </td>
                    <input type="hidden" name="arr_code[]" id="arr_code` + count + `" >
                    <input type="hidden" name="arr_type[]">
                    <td class="center-align">
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="deleteRow(this)"><i class="material-icons dp48">delete</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="add_sparepart(this)"><i class="material-icons dp48">add</i></button>
                    </td>     
                </tr>
            `);

            $.each(list_sparepart, function(i, value) {
                $('#equipmentpart'+count).append(`
                    <option value="`+i+`"> `+value.code+` - `+value.name+`</option>
                `);
                
            });

            var temp_val = $('#equipmentpart'+count).val();
           
            
            $('#select_item'+count).append(`
                <select class="browser-default select2" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')">
                    <option>Silahkan pilih sparepart</option>
                </select>
            `);
            var temp_sparepart = list_sparepart[temp_val].sparepart;

            $.each(temp_sparepart, function(index, value) {
                $('#arr_item' + count).append(`
                    <option value="` + index + `">` + value.name + `</option>
                `);
            });
            $('#arr_item'+count).select2(
            {
                placeholder: "Kosong untuk semua tipe.",
                dropdownAutoWidth: true,
                width: '100%',
            });
        }else{
            alert("Harap pilih work order terlebih dahulu")
        }
        
    }

    function getRowUnit(val){
        var temp_val = $('#equipmentpart'+val).val();
        var temp_sparepart = list_sparepart[temp_val].sparepart;
        
        if($("#arr_item" + val).val()){
            $("#arr_code" + val).val(temp_sparepart[$("#arr_item" + val).val()].code);
            var temp_stock=temp_sparepart[$("#arr_item" + val).val()].stock;
            $('#arr_stock' + val).empty();
            
            $.each(temp_stock, function(index, value) {
                $('#arr_stock' + val).append(`
                    <option value="` + value.id + `">` + value.qty + ` - ` + value.warehouse + `</option>
                `);
            });
        }else{
            
        }
    }

    function getWO_info_edit(){
        if($('#work_order_id').val()){
            
            $.ajax({
                url: '{{ Request::url() }}/get_work_order_info',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: $('#work_order_id').val()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    loadingClose('.modal-content');
                    $('#user_id').empty();
                    $('#user_name').empty();
                    
                    if(response.user_name){
                        $('#user_name').val(response.user_name);
                        $('#equipment_id').val(response.equipment_name);
                    }
                   
                    
                    if(response.equipment_part.length > 0){
                       
                        list_sparepart=response.equipment_part;
                       

                    }else{
                        $('#body-detail').empty().append(`
                            <tr id="empty-detail">
                                <td colspan="10" class="center">
                                   Work Order tidak mencantumkan Equipment part
                                </td>
                            </tr>
                        `);
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
            
            $('#body-detail').empty().append(`
                <tr id="empty-detail">
                    <td colspan="10" class="center">
                        Silahkan memilih WO terlebih dahulu
                    </td>
                </tr>
            `);
        }
    }

    function getWO_info(){
       
        if($('#work_order_id').val()){
            
            $.ajax({
                url: '{{ Request::url() }}/get_work_order_info',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: $('#work_order_id').val()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    loadingClose('.modal-content');
                    $('#user_id').empty();
                    $('#user_name').empty();
                    
                    if(response.user_name){
                        $('#user_name').val(response.user_name);
                        $('#equipment_id').val(response.equipment_name);
                    }
                   
                    
                    if(response.equipment_part.length > 0){
                       
                        list_sparepart=response.equipment_part;
                       
                        $('#empty-detail').remove();
                        var count = makeid(10);
                        
                        
                        $('#body-detail').append(`
                            <tr class="row_detail">
                                <td>
                                    <select class="browser-default select2" id="equipmentpart` + count + `" name="equipmentpart"  onchange="takeSparepart('` + count + `')">
                                                
                                    </select>
                                </td>
                                <td id="select_item` + count + `">
                                    
                                </td>
                                <td>
                                    
                                    <select class="browser-default select2" id="arr_stock` + count + `" name="arr_stock[]">
                                        <option>Silahkan pilih stock</option>
                                    </select>
                                </td>
                                <td>
                                    <input name="arr_qty_req[]" id="arr_qty_req`+ count +`" type="text" value="0">
                                </td>
                                <td>
                                    <input name="arr_qty_return[]" id="arr_qty_return`+ count +`"  type="text" readonly>
                                </td>  
                                <td>
                                    <input name="arr_qty_usage[]" id="arr_qty_usage`+ count +`" type="text" readonly>
                                </td>  
                                <td>
                                    <input name="arr_qty_repair[]" id="arr_qty_repair`+ count +`" type="text" readonly>
                                </td>
                                <input type="hidden" name="arr_code[]" id="arr_code` + count + `" >
                                <input type="hidden" name="arr_type[]">
                                <td class="center-align">
                                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="deleteRow(this)"><i class="material-icons dp48">delete</i></button>
                                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="add_sparepart(this)"><i class="material-icons dp48">add</i></button>
                                </td>     
                            </tr>
                        `);
                        
                        $('#equipmentpart').empty();
                        $.each(response.equipment_part, function(i, value) {
                            $('#equipmentpart'+count).append(`
                                <option value="`+i+`"> `+value.code+` - `+value.name+`</option>
                            `);
                            
                        });
                            
                        $('#equipmentpart').select2(
                            {
                                placeholder: "Kosong untuk semua tipe.",
                                dropdownAutoWidth: true,
                                width: '100%',
                            }
                        );

                        var temp_val = $('#equipmentpart'+count).val();

                        $('#select_item'+count).append(`
                            <select class="browser-default select2" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')">
                                <option>Silahkan pilih sparepart</option>
                            </select>
                        `);
                        var temp_sparepart = list_sparepart[temp_val].sparepart;
                        
                        $.each(temp_sparepart, function(index, value) {
                            $('#arr_item' + count).append(`
                                <option value="` + index + `">` + value.name + `</option>
                            `);
                        });
                        $('#arr_item'+count).select2(
                        {
                            placeholder: "Kosong untuk semua tipe.",
                            dropdownAutoWidth: true,
                            width: '100%',
                        });

                    }else{
                        $('#body-detail').empty().append(`
                            <tr id="empty-detail">
                                <td colspan="10" class="center">
                                   Work Order tidak mencantumkan Equipment part
                                </td>
                            </tr>
                        `);
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
            
            $('#body-detail').empty().append(`
                <tr id="empty-detail">
                    <td colspan="10" class="center">
                        Silahkan memilih WO terlebih dahulu
                    </td>
                </tr>
            `);
        }
        
    }
    function removeAttachment(button) {
        const row = button.parentNode.parentNode;
        row.parentNode.removeChild(row);
        const tableBody = document.getElementById('body-attachment');
        const rowNumber = tableBody.childElementCount;
        if(rowNumber < 1){
            $('#body-attachment').empty().append(`
                <tr id="empty-attachment-detail">
                    <td colspan="10" class="center">
                       Tidak ada lampiran
                    </td>
                </tr>
            `);
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
                { name: 'work_order_id', className: 'center-align' },
                { name: 'area', className: 'center-align' },
                { name: 'request_date', className: 'center-align' },
                { name: 'summary_issue', className: 'center-align' },
                { name: 'status', className: 'center-align' },
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
                            alert("422");
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
    }

    function deleteRow(button) {
        $(button).closest('tr').remove();
    }
    function returnUsage(val){
        var qtyReturn = parseInt($("#arr_qty_return" + val).val());
        var qtyReq = parseInt($('#arr_qty_req' + val).val());

        if(qtyReturn > qtyReq) {
            qtyReturn = qtyReq;
            $("#arr_qty_return" + val).val(qtyReq);
        }

        var qtyUsage = qtyReq - qtyReturn;
        $('#arr_qty_usage' + val).val(qtyUsage);
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
                $('#user_name').val(response.user_name);
                $('#equipment_id').val(response.equipment_name);
                $('#work_order_id').empty().append(`
                    <option value="` + response.work_order_id + `">` + response.work_order_code + `</option>
                `);
                $('#request_date').val(response.request_date);
                getWO_info_edit();
                
                

                if(response.equipment_part.length > 0){
                    
                    if(response.status == '1'){
                        $.each(response.equipment_part, function(index_ep, val) {
                           
                            $.each(val.sparepart, function(i, val_spare) {
                                var count = makeid(10);
                                var temp_sparepart = response.equipment_part[index_ep].sparepart;
                                      
                                $.each(response.request_sp_detail, function(idex, val_request_sp_detail) {
                                    
                                    if(val_spare.id==val_request_sp_detail.equipment_sparepart_id){
                                        
                                        
                                        $('#body-detail').append(`
                                            <tr class="row_detail">
                                                <td>
                                                `+val.code+`
                                                </td>
                                                <td>
                                                    <select class="browser-default select2" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `',` + index_ep + `)" readonly>
                                                        <option value="` + i + `">` + val_spare.name + `</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="browser-default select2" id="arr_stock` + count + `" name="arr_stock[]" readonly>
                                                        <option value="` + val_request_sp_detail.stock.id + `">` + val_request_sp_detail.stock.qty + ` - ` + val_request_sp_detail.stock.warehouse + `</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input name="arr_qty_req[]" id="arr_qty_req`+ count +`" type="text" value="0" >
                                                </td>
                                                <td>
                                                    <input name="arr_qty_return[]" id="arr_qty_return`+ count +`"  type="text" readonly>
                                                </td>  
                                                <td>
                                                    <input name="arr_qty_usage[]" id="arr_qty_usage`+ count +`" type="text" readonly >
                                                </td>  
                                                <td>
                                                    <input name="arr_qty_repair[]" id="arr_qty_repair`+ count +`" type="text" readonly>
                                                </td>
                                                <input type="hidden" name="arr_code[]" id="arr_code` + count + `" >
                                                <input type="hidden" name="arr_type[]">
                                                <td class="center-align">
                                                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete"  onclick="deleteRow(this)"><i class="material-icons dp48">delete</i></button>
                                                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="add_sparepart(this)"><i class="material-icons dp48">add</i></button>
                                                </td>     
                                            </tr>
                                        `);
                                        
                                        var temp_stock=temp_sparepart[$('#arr_item' + count).val()].stock;
                                        
                                       
                                        $("#arr_code" + count).val(temp_sparepart[$('#arr_item' + count).val()].code);
                                        $.each(temp_stock, function(index, value) {
                                            if(value.id != val_request_sp_detail.stock.id){
                                                $('#arr_stock' + count).append(`
                                                    <option value="` + value.id + `">` + value.qty + ` - ` + value.warehouse + `</option>
                                                `);
                                            }
                                        });
                                        $.each(temp_sparepart, function(index, value) {
                                            if(value.name != val_spare.name){
                                                $('#arr_item' + count).append(`
                                                    <option value="` + index + `">` + value.name + `</option>
                                                `);
                                            }
                                            
                                        });
                                        $('#arr_item'+count).select2(
                                        {
                                            placeholder: "Kosong untuk semua tipe.",
                                            dropdownAutoWidth: true,
                                            width: '100%',
                                        });

                                        
                                        $('input[name^="arr_qty_req"][id="arr_qty_req'+ count +'"]').val(val_request_sp_detail.qty_request);
                                        $('input[name^="arr_qty_usage"][id="arr_qty_usage'+ count +'"]').val(val_request_sp_detail.qty_usage);
                                        $('input[name^="arr_qty_return"][id="arr_qty_return'+ count +'"]').val(val_request_sp_detail.qty_return);
                                        $('input[name^="arr_qty_repair"][id="arr_qty_repair'+ count +'"]').val(val_request_sp_detail.qty_repair);
                                            
                                    }
                                });
                            });                  
                        });
                    }
                    if(response.status == '2'){
                        $('#btn_add_sparepart').prop('disabled', true);
                        
                        $.each(response.equipment_part, function(index_ep, val) {
                            
                            $.each(val.sparepart, function(i, val_spare) {
                                var count = makeid(10);
                                var temp_sparepart = response.equipment_part[index_ep].sparepart;
                                   
                                $.each(response.request_sp_detail, function(idex, val_request_sp_detail) {
                                    if(val_spare.id==val_request_sp_detail.equipment_sparepart_id){
                                        
                                        $('#body-detail').append(`
                                            <tr class="row_detail">
                                                <td>
                                                `+val.code+`
                                                </td>
                                                <td>
                                                    <select class="browser-default select2" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `',` + index_ep + `)" readonly>
                                                        <option value="` + i + `">` + val_spare.name + `</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="browser-default select2" id="arr_stock` + count + `" name="arr_stock[]" readonly>
                                                        <option value="` + val_request_sp_detail.stock.id + `">` + val_request_sp_detail.stock.qty + ` - ` + val_request_sp_detail.stock.warehouse + `</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input name="arr_qty_req[]" id="arr_qty_req`+ count +`" type="number" value="0" readonly>
                                                </td>
                                                <td>
                                                    <input name="arr_qty_return[]" id="arr_qty_return`+ count +`"  type="number" onkeyup="returnUsage('` + count + `')" onchange="returnUsage('` + count + `')">
                                                    
                                                </td>  
                                                <td>
                                                    <input name="arr_qty_usage[]" id="arr_qty_usage`+ count +`" type="number" min="0" readonly>
                                                </td>  
                                                <td>
                                                    <input name="arr_qty_repair[]" id="arr_qty_repair`+ count +`" type="text" >
                                                </td>
                                                <input type="hidden" name="arr_code[]" id="arr_code` + count + `" >
                                                <input type="hidden" name="arr_type[]">
                                                <td class="center-align">
                                                    <button disabled type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete"  onclick="deleteRow(this)"><i class="material-icons dp48">delete</i></button>
                                                </td>     
                                            </tr>
                                        `);
                                        
                                      
                                        var temp_stock=temp_sparepart[$('#arr_item' + count).val()].stock;
                                        
                                        $("#arr_code" + count).val(temp_sparepart[$('#arr_item' + count).val()].code);
                                        $.each(temp_stock, function(index, value) {
                                            if(value.id != val_request_sp_detail.stock.id){
                                                $('#arr_stock' + count).append(`
                                                    <option value="` + value.id + `" disabled>` + value.qty + ` - ` + value.warehouse + `</option>
                                                `);
                                            }
                                        });
                                        $.each(temp_sparepart, function(index, value) {
                                            if(value.name != val_spare.name){
                                                $('#arr_item' + count).append(`
                                                    <option value="` + index + `" disabled>` + value.name + `</option>
                                                `);
                                            }
                                            
                                        });
                                        $('#arr_item'+count).select2(
                                        {
                                            placeholder: "Kosong untuk semua tipe.",
                                            dropdownAutoWidth: true,
                                            width: '100%',
                                        });

                                        
                                        $('input[name^="arr_qty_req"][id="arr_qty_req'+ count +'"]').val(val_request_sp_detail.qty_request);
                                        $('input[name^="arr_qty_usage"][id="arr_qty_usage'+ count +'"]').val(val_request_sp_detail.qty_usage);
                                        $('input[name^="arr_qty_return"][id="arr_qty_return'+ count +'"]').val(val_request_sp_detail.qty_return);
                                        $('input[name^="arr_qty_repair"][id="arr_qty_repair'+ count +'"]').val(val_request_sp_detail.qty_repair);
                                            
                                    }
                                });
                            });                  
                        });
                        
                      
                    }
                    
                   
                    
                }

                $('#empty-detail').remove();
                
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
</script>