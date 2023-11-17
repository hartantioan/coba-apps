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
    .chart-container {
        width: 100%;
        display: inline-block;
        text-align: center;
    }
    .chart-container{
        margin: 10%
    }
    .select2 {
    height: 3.6rem !important;
    }
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
                                            <table id="datatable_serverside" class="display responsive-table wrap">
                                                <thead>
                                                    <tr>
                                                        <th >#</th>
                                                        <th >Kode WO</th>
                                                        <th >Tempat</th>
                                                        <th >Equipment</th>
                                                        <th >Nama Aktivitas</th>
                                                        <th >Area</th>
                                                        <th  class="center-align">Requested By</th>
                                                        <th >Tipe Maintenance</th>
                                                        <th >Prioritas</th>
                                                        <th >Tipe WO</th>
                                                        <th >Tanggal selesai yang diharapkan</th>
                                                        <th >Tanggal Permintaan</th>
                                                        <th >Waktu Perbaikan</th>
                                                        <th >Detail Keterangan</th>
                                                        <th >Hasil yang Diharapkan</th>
                                                        <th >Status</th>
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
                <h4>Add/Edit {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="row">
                            <div class="input-field col m6 s12">
                                <select class="form-control" id="area_id" name="area_id">
                                    @foreach ($area as $rowarea)
                                        <option value="{{ $rowarea->id }}">{{ $rowarea->name}}</option>
                                    @endforeach
                                </select>    
                                <label for="area_id">Area</label>
                            </div>
                            <div class="input-field col m6 s12">
                                <select class="form-control" id="place_id" name="place_id">
                                    @foreach ($place as $rowplace)
                                        <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                                    @endforeach
                                </select>    
                                <label for="place_id">Plant</label>
                            </div>
                            <div class="input-field col m4 s12">
                                <select class="form-control" id="activity_id" name="activity_id">
                                    @foreach ($activity as $rowactivity)
                                        <option value="{{ $rowactivity->id }}">{{ $rowactivity->title}}</option>
                                    @endforeach
                                </select>
                                <label for="activity_id">Tipe Aktivitas</label>    
                            </div>
                            <div class="input-field col m3 s12">
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
                            <div class="input-field col m4 s12">
                                <input type="hidden" id="user_id" name="user_id" value="{{session('bo_id')}}">
                                <input type="text" placeholder="Nama Peng-request" id="user_name" value="{{session('bo_name')}}" disabled>
                                <label class="active" for="request_by">Requester</label>
                            </div>
                            <div class="input-field col m4 s12">
                                <input id="request_date" name="request_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. dokumen" value="{{ date('Y-m-d') }}" onchange="changeDateMinimum(this.value);">
                                <label class="active" for="request_date">Tgl. Request</label>
                            </div>

                            <div class="input-field col m3 s12">
                                <input id="estimated_fix_time" name="estimated_fix_time" type="number" placeholder="Estimasi waktu yang diperlukan">
                                <label class="active" for="estimated_fix_time">Estimasi Waktu Selesai</label>
                            </div>
                            <div class="input-field col m1 s12">
                               
                                <p>Min</p>
                              
                            </div>
                            <div class="input-field col m4 s12">
                                <input id="suggested_completion_date" name="suggested_completion_date" min="{{ date('Y-m-d') }}" type="date" placeholder="Tanggal selesai yang diharapkan" value="{{ date('Y-m-d') }}">
                                <label class="active" for="suggested_completion_date">Tgl Selesai yang diharapkan</label>
                            </div>
                            <div class="input-field col m4 s12">
                                <select class="form-control" id="priority" name="priority">    
                                        <option value="1">Low</option>
                                        <option value="2">Medium</option>
                                        <option value="3">High</option>
                                </select>
                                <label  for="priority">Tingkat Prioritas</label>      
                            </div>
                            <div class="input-field col m4 s12">
                                <select class="form-control" id="maintenance_type" name="maintenance_type">    
                                        <option value="1">Preventive</option>
                                        <option value="2">Corrective</option>
                                        <option value="3">Utility</option>
                                </select>
                                <label  for="maintenance_type">Tipe Maintenance</label>     
                            </div>
                            <div class="input-field col m4 s12">
                                <select class="form-control" id="work_order_type" name="work_order_type">    
                                        <option value="1">MWR</option>
                                        <option value="2">Abnormal</option>
                                        <option value="3">DT</option>
                                </select>    
                                <label  for="work_order_type">Tipe WO</label>   
                            </div>
                            <div class="input-field col m6 s12">
                                <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note">Detail Issue</label>
                            </div>
                            <div class="input-field col m6 s6">
                                <textarea class="materialize-textarea" id="expected_result" name="expected_result" placeholder="" rows="3"></textarea>
                                <label class="active" for="note">Expected Result</label>
                            </div>
                            <div class="input-field col m4 s12">
                                <input type="hidden" id="temp" name="temp">
                                <select class="browser-default" id="equipment_id" name="equipment_id" onchange="getEquipmentPart()">&nbsp;</select>
                                <label class="active" for="equipment_id">Equipment</label>
                            </div>
                            <div class="input-field col m2 s12">
                            </div>
                            <div class="col m6 s12">
                                <label for="actual_start_time">Actual Start Time (date and time):</label>
                                 <input type="datetime-local" id="actual_start_time" name="actual_start_time">
                            </div>
                            <div class="input-field col m2 s12">
                            </div>
                            <div class="col m6 s12">
                                <label for="actual_finish_time">Actual Finish Time (date and time):</label>
                                 <input type="datetime-local" id="actual_finish_time" name="actual_finish_time">
                            </div>
                            <div class="col m12 s12">
                                <label for="solution">Solusi </label>
                                 <input type="text" id="solution" name="solution">
                            </div>
                            <div class="col m12 s12">
                                <div class="card-panel">
                                    <ul class="tabs">
                                        <li class="tab">
                                            <a href="#part-tabs" class="" id="part-tabs-btn">
                                            <span>Part Equipment</span>
                                            </a>
                                        </li>
                                        <li class="tab">
                                            <a href="#attachment-tabs" class="">
                                            <span>Attachment</span>
                                            </a>
                                        </li>
                                        <li class="tab">
                                            <a href="#PIC-tabs" class="" id="btn-pic">
                                            <span>PIC</span>
                                            </a>
                                        </li>
                                        <li class="tab">
                                            <a href="#Req-tabs" class="">
                                            <span>Req. Sparepart</span>
                                            </a>
                                        </li>
                                        <li class="indicator" style="left: 0px; right: 0px;"></li>
                                    </ul>
                                    <div id="part-tabs" style="display: block;" class="">
                                        <div class="card-panel">
                                            <p class="mt-2 mb-2">
                                                <h6>Part Equipment</h6>
                                                <div style="overflow:auto;">
                                                    <table class="bordered" style="max-width:1650px !important;">
                                                        <thead>
                                                            <tr>
                                                                <th class="center" width="10%">
                                                                    <label>
                                                                        <input type="checkbox" onclick="chooseAll(this)">
                                                                        <span>Semua</span>
                                                                    </label>
                                                                </th>
                                                                <th class="center">Code</th>
                                                                <th class="center">Nama</th>
                                                                <th class="center">Spesifikasi</th>
                                                                <th class="center">Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="body-detail">
                                                            <tr id="empty-detail">
                                                                <td colspan="10" class="center">
                                                                Pilih Equipment untuk memilih part..
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </p>
                                        </div>
                                    </div>
                                    <div id="attachment-tabs" style="display: none;" class="">
                                        <div class="card-panel">
                                        <div class="row">
                                            <div class="file-field input-field col m8 s12">
                                                <div class="btn col m3 s12">
                                                    <span>Lampiran Bukti</span>
                                                    <input type="file" name="attachment" id="document" maxlength="1000000" accept="image/png, image/jpg">
                                                </div>
                                                <div class="file-path-wrapper col m6 s12">
                                                    <input class="file-path validate" id="file-name" type="text" >
                                                </div>
                                            </div>
                                            <div class="col m4 s12">
                                                <button id="add-attachment" class="btn" type="button">Add Attachment</button>
                                            </div>
                                            <div class="col m4 s12">
                                                <div id="previewImg"></div>
                                            </div>
                                            <div class="col m12 s12">
                                                <div style="overflow:auto;">
                                                    <table class="bordered" style="max-width:1650px !important;">
                                                        <thead>
                                                            <tr>
                                                                <th class="center">No</th>
                                                                <th class="center">Deskripsi</th>
                                                                <th class="center">Created At</th>
                                                                <th class="center">Display</th>
                                                                <th class="center">Action</th>
                                                                
                                                            </tr>
                                                        </thead>
                                                        <tbody id="body-attachment">
                                                            <tr id="empty-attachment-detail">
                                                                <td colspan="10" class="center">
                                                                Tidak ada lampiran
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                    <div id="PIC-tabs" style="display: none;" class="">
                                        <div class="card-panel">
                                        <div class="row">
                                            <div class="input-field col m4 s12">
                                                <input type="hidden" id="temp_wo" name="temp_wo">
                                                <select class="browser-default" id="pic_id" name="pic_id" onchange="assignUser()">&nbsp;</select>
                                                <label class="active" for="pic_id">Pilih PIC</label>
                                            </div>
                                            <div class="input-field col m4 s12">
                                                
                                            </div>
                                            <div class="input-field col m4 s12">
                                                <button id="btn-save-pic" class="btn waves-effect waves-light right submit" onclick="savePIC();">Simpan <i class="material-icons right">send</i></button>
                                            </div>
                                            <div class="col m12 s12">
                                                <div style="overflow:auto;">
                                                    <table class="bordered" style="max-width:1650px !important;">
                                                        <thead>
                                                            <tr>
                                                                <th class="center">Nama</th>
                                                                <th class="center">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="body-pic">
                                                            <tr id="empty-pic-detail">
                                                                <td colspan="10" class="center">
                                                                Blm ada PIC
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                    <div id="Req-tabs" style="display: none;" class="">
                                        <div class="card-panel">
                                        <div class="row">
                                            <div class="col m12 s12">
                                                <div style="overflow:auto;">
                                                    <table class="bordered" style="max-width:1650px !important;">
                                                        <thead>
                                                            <tr>
                                                                <th class="center">Item</th>
                                                                <th class="center" colspan="5">Information</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="body-request">
                                                            <tr id="empty-request-detail">
                                                                <td colspan="10" class="center">
                                                                Blm ada Request
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col s12 mt-3">
                                <button id="btn-save" class="btn waves-effect waves-light right submit" onclick="save();">Simpan <i class="material-icons right">send</i></button>
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
    <a id="btn-add" class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div>


<script>
    var rowNumber=0;
    var href = "";
    $(function() {
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });
        $("#btn-add").click(function(){
            $("#btn-save").show();
            $("#btn-save-pic").hide();
        });
        
        const addAttachmentBtn = document.getElementById('add-attachment');
        var attachmentCount = 0;
        addAttachmentBtn.addEventListener('click', () => {

            var base64image = "";
            var input = document.getElementById("document");
            
            var fReader = new FileReader();
            fReader.readAsDataURL(input.files[0]);
            fReader.onload =  function(e){
                $.ajax({
                    url: '{{ Request::url() }}/get_decode',
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        base64: e.target.result
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        const currentDate = new Date();
                        const year = currentDate.getFullYear();
                        const month = currentDate.getMonth() + 1;
                        const day = currentDate.getDate();
                        const hours = currentDate.getHours();
                        const minutes = currentDate.getMinutes();
                        const seconds = currentDate.getSeconds();
                        href = response.result;
                        base64image = e.target.result;
                        var fileInput = document.getElementById('document');
                        var filepath = document.getElementById('file-name');
                        const file = fileInput.files[0];
                        if (!filepath.value)
                        {
                            alert("File Tidak boleh Kosong");
                            return;
                        }
                        
                        $("#empty-attachment-detail").remove();
                        $('#body-attachment').append(`
                                <tr class="row_detail">
                                    <input type="hidden" name="arr_ada[]" value="0">
                                    <input type="hidden" name="arr_typefile[]" value="file">
                                    <td >
                                        ` + (rowNumber+1) + `
                                    </td>
                                    <input type="hidden" name="arr_file_name[]" value="`+file.name+`">
                                    <td>
                                        ` + file.name + `
                                    </td>
                                    <input type="hidden" name="arr_file_path[]" value="`+base64image+`">
                                    <td>
                                        ` + day +`-`+month+`-`+year+`
                                    </td>
                                    <td>
                                        <a href="`+  href +`" target="_blank"><i class="material-icons">attachment</i></a>',
                                    </td>
                                    <td>
                                        <button class="btn red" type="button" onclick="removeAttachment(this)">Remove</button>
                                    </td>
                                    
                                </tr>
                            `);
                        $('#file-name').val('');
                    },
                    error: function() {
                        swal({
                            title: 'Ups!',
                            text: 'Check your internet connection.',
                            icon: 'error'
                        });
                    }
                });               
            };
            
        });
        
        loadDataTable();

        window.table.search('{{ $code }}').draw();

        $('#modal4').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#show_detail').empty();
            }
        });

        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                $('#attachment-tabs').hide();
                $('#PIC-tabs').hide();
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
                $('#form_data')[0].reset();
                $('#temp').val('');
                M.updateTextFields();
                $('#area_id').attr("disabled", false).formSelect();
                $('#place_id').attr("disabled", false).formSelect();
                $('#activity_id').attr("disabled", false).formSelect();
                $('#user_id').attr("disabled", false);
                $('#suggested_completion_date').attr("disabled", false);
                $('#estimated_fix_time').attr("disabled", false);
                $('#request_date').attr("disabled", false);
                $('#priority').attr("disabled", false).formSelect();
                $('#maintenance_type').attr("disabled", false).formSelect();
                $('#work_order_type').attr("disabled", false).formSelect();
                $('#note').attr("disabled", false);
                $('#expected_result').attr("disabled", false);
                $('#equipment_id').attr("disabled", false);
                
                $('#btn-pic').css('pointer-events', 'none');
                $('.row_pic_detail').each(function(){
                    $(this).remove();
                });
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

        select2ServerSide('#equipment_id', '{{ url("admin/select2/equipment") }}');
        select2ServerSide('#pic_id', '{{ url("admin/select2/employee") }}');
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

    function removeAttachment(button) {
        const row = button.parentNode.parentNode;
        
        const tableBody = document.getElementById('body-attachment');
        const rowNumber = tableBody.childElementCount;
        const arrIdInput = row.querySelector('input[name="arr_ada[]"]');
        const arrIdValue = arrIdInput ? arrIdInput.value : null;
        
        if(arrIdValue != 0){
            
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
                        url: '{{ Request::url() }}/delete_attachment',
                        type: 'POST',
                        dataType: 'JSON',
                        data: { id : arrIdValue },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        beforeSend: function() {
                            loadingOpen('#main');
                        },
                        success: function(response) {
                            loadingClose('#main');
                            row.parentNode.removeChild(row);
                            if(rowNumber < 1){
                                $('#body-attachment').empty().append(`
                                    <tr id="empty-attachment-detail">
                                        <td colspan="10" class="center">
                                        Tidak ada lampiran
                                        </td>
                                    </tr>
                                `);
                            }
                            M.toast({
                                html: response.message
                            });
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
        }else{
            row.parentNode.removeChild(row);
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
        

    }

    function removeUser(button) {
        const row = button.parentNode.parentNode;
        row.parentNode.removeChild(row);
        const tableBody = document.getElementById('body-pic');
        const rowNumber = tableBody.childElementCount;
        if(rowNumber < 1){
            $('#body-pic').empty().append(`
                <tr id="empty-pic-detail">
                    <td colspan="10" class="center">
                       Blm ada PIC
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
            "order": [[0, 'asc']],
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
                { name: 'place_id', className: 'center-align' },
                { name: 'equipment_id', className: 'center-align' },
                { name: 'activity_id', className: 'center-align' },
                { name: 'area_id', className: 'center-align' },
                { name: 'user_id', className: 'center-align' },
                { name: 'maintenance_type', className: 'center-align' },
                { name: 'priority', className: '' },
                { name: 'work_order_type', searchable: false, orderable: false, className: 'center-align' },
                { name: 'suggested_completion_date', className: 'center-align' },
                { name: 'estimated_fix_time', className: 'center-align' },
                { name: 'request_date', className: 'center-align' },
                { name: 'detail_issue', className: 'center-align' },
                { name: 'expected_result', className: 'center-align' },
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

    function getEquipmentPart(){
        if($('#equipment_id').val()){
            $.ajax({
                url: '{{ Request::url() }}/get_equipment_part',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: $('#equipment_id').val()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    loadingClose('.modal-content');

                    $('#body-detail').empty();
                    if(response.length > 0){
                        $.each(response, function(i, val) {
                            var count = makeid(10);
                            $('#body-detail').append(`
                                <tr class="row_detail">
                                    <input type="hidden" name="arr_type[]" value="` + val.type + `" data-id="` + count + `">
                                    <td class="center-align">
                                        <label>
                                            <input type="checkbox" id="check` + count + `" name="arr_code[]" value="` + val.code + `" data-id="` + count + `">
                                            <span>Pilih</span>
                                        </label>
                                    </td>
                                    <td>
                                        ` + val.rawcode + `
                                    </td>
                                    <td class="center">
                                        ` + val.name + `
                                    </td>
                                    <td class="center">
                                        ` + val.specification + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.status + `
                                    </td>
                                </tr>
                            `);
                        });                        
                    }else{
                        $('#body-detail').empty().append(`
                            <tr id="empty-detail">
                                <td colspan="10" class="center">
                                    Pilih supplier/vendor untuk memulai...
                                </td>
                            </tr>
                        `);
                    }
                    
                    $('#top').val(response.top);

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
                        Pilih Equipment untuk melihat part
                    </td>
                </tr>
            `);
        }
    }

    

    function savePIC(){
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
                    url: '{{ Request::url() }}/save_user',
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
    var pic = [];
    function show(id){
        pic= [];
        $('#btn-pic').css('pointer-events', 'none');
        $("#btn-save").show();
        $("#btn-save-pic").hide();
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
                $('#code_place_id').val(response.code_place_id).formSelect();
                $('#code').val(response.code);
                $('#area_id').val(response.area_id).formSelect();
                $('#place_id').val(response.place_id).formSelect();
                $('#activity_id').val(response.activity_id).formSelect();
                $('#equipment_id').empty().append(`
                    <option value="` + response.equipment_id + `">` + response.equipment_name + `</option>
                `);
                $('#user_name').val(response.user_name);
                $('#suggested_completion_date').val(response.suggested_completion_date);
                $('#estimated_fix_time').val(response.estimated_fix_time);
                $('#request_date').val(response.request_date);
                $('#priority').val(response.priority).formSelect();
                $('#work_order_type').val(response.work_order_type).formSelect();
                $('#maintenance_type').val(response.maintenance_type).formSelect();
                $('#note').val(response.note);
                $('#actual_start_time').val(response.actual_finish);
                $('#actual_finish_time').val(response.actual_start);
                $('#expected_result').val(response.expected_result);
                $('#solution').val(response.solution);
                if(response.equipment_part.length > 0){
                    
                    $.each(response.equipment_part, function(i, val) {
                        var count = makeid(10);
                        $('#body-detail').append(`
                                <tr class="row_detail">
                                    <input type="hidden" name="arr_type[]" value="` + val.type + `" data-id="` + count + `">
                                    <td class="center-align">
                                        <label>
                                            <input type="checkbox" id="check` + count + `" name="arr_code[]" value="` + val.code + `" data-id="` + count + `">
                                            <span>Pilih</span>
                                        </label>
                                    </td>
                                    <td>
                                        ` + val.rawcode + `
                                    </td>
                                    <td class="center">
                                        ` + val.name + `
                                    </td>
                                    <td class="center">
                                        ` + val.specification + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.status + `
                                    </td>
                                </tr>
                            `);
                            $.each(response.work_order_part_detail, function(i, val_detail_part) {
                                if(val.id==val_detail_part.part_id){
                                    $('#check' + count).prop( "checked", true);
                                }
                            });
                    });
                }
               /*  //mengambil dan membuat table dibagian ini; */
                if(response.person_in_charge.length > 0){
                    var count = makeid(10);
                    $('#empty-pic-detail').remove();
                   
                    $.each(response.person_in_charge, function(i, val) {
                        pic.push(val);
                        $('#body-pic').append(`
                                <tr class="row_pic_detail">
                                    <input type="hidden" name="arr_types[]" value="pic" data-id="` + count + `">
                                    <input type="hidden" name="arr_id[]" value="`+val.pic_id+`" data-id="` + count + `">
                                    <td>
                                        ` + val.name + `
                                    </td>
                                    <td class="center">
                                        <button class="btn red" type="button" onclick="removeUser(this)">Remove</button>
                                    </td>
                                </tr>
                            `);
                        
                    });
                }

                if(response.attachments.length > 0){
                    $('#empty-attachment-detail').remove();
                    $.each(response.attachments, function(i, val_file) {
                        
                       
                        $('#body-attachment').append(`
                            <tr class="row_detail">
                                <input type="hidden" name="arr_ada[]" value="`+val_file.id+`">
                                <input type="hidden" name="arr_typefile[]" value="file">
                                <td >
                                    ` + (rowNumber+1) + `
                                </td>
                                <input type="hidden" name="arr_file_name[]" value="`+val_file.file_name+`">
                                <td>
                                    ` + val_file.file_name + `
                                </td>
                                <input type="hidden" name="arr_file_path[]" value="`+val_file.path+`">
                                <td>
                                    ` +  val_file.created_at + `
                                </td>
                                <td>
                                <a href="`+  val_file.attachment +`" target="_blank"><i class="material-icons">attachment</i></a>',
                                </td>
                                <td>
                                    <button class="btn red" type="button" onclick="removeAttachment(this)">Remove</button>
                                </td>
                            </tr>
                        `);
                        rowNumber++;
                    });
                    
                }

                if(response.requested_sparepart.length > 0){
                    $('#empty-request-detail').remove();
                    $.each(response.requested_sparepart, function(i, val_sparepart) {
                        
                        $('#body-request').append(`
                            <tr class="row_detail">
                                <td rowspan="`+(val_sparepart.spareparts.length+1)+`">
                                    Request No.`+val_sparepart.code+`
                                </td>
                                <td>
                                    Nama
                                </td>
                                <td>
                                    Qty Request
                                </td>
                                <td>
                                    Qty Usage
                                </td>
                                <td>
                                    Qty Return
                                </td>
                                <td>
                                    Qty Repair
                                </td>
                            </tr>
                        `);
                        $.each(val_sparepart.spareparts, function(i, val_spare) {
                            $('#body-request').append(`
                            <tr class="row_detail">
                                <td>
                                `+val_spare.name+`
                                </td>
                                <td>
                                `+val_spare.qty_request+`
                                </td>
                                <td>
                                    `+val_spare.qty_usage+`
                                </td>  
                                <td>
                                    `+val_spare.qty_return+`
                                </td>  
                                <td>
                                    `+val_spare.qty_repair+`
                                </td>
                            </tr>
                            `);
                        });
                    });
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

    function addPIC(id){
        show(id);
        $('#temp_wo').val(id);
        $("#btn-save").hide();
        $("#btn-save-pic").show();
        $('#area_id').attr("disabled", true);
        $('#place_id').attr("disabled", true);
        $('#activity_id').attr("disabled", true);
        $('#user_id').attr("disabled", true);
        $('#suggested_completion_date').attr("disabled", true);
        $('#estimated_fix_time').attr("disabled", true);
        $('#request_date').attr("disabled", true);
        $('#priority').attr("disabled", true);
        $('#maintenance_type').attr("disabled", true);
        $('#work_order_type').attr("disabled", true);
        $('#note').attr("disabled", true);
        $('#expected_result').attr("disabled", true);
        $('#equipment_id').attr("disabled", true);
        $('#btn-pic').css('pointer-events', 'auto');
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

    function assignUser(){
        if($('#pic_id').val()){
            $.ajax({
                url: '{{ Request::url() }}/get_pic',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: $('#pic_id').val()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    loadingClose('.modal-content');
                    $('#empty-pic-detail').remove();
                    if(response.length > 0){
                        $.each(response, function(i, val) {
                            
                            var count = makeid(10);
                            var picExist = false;
                            $.each(pic,function(i,vals){
                                
                                if(val.name == vals.name){
                                    picExist = true;
                                    
                                    return true;
                                }
                            });
                            if(picExist == false){
                                pic.push(val);
                                $('#body-pic').append(`
                                <tr class="row_pic_detail">
                                    <input type="hidden" name="arr_types[]" value="pic" data-id="` + count + `">
                                    <input type="hidden" name="arr_id[]" value="`+val.id+`" data-id="` + count + `">
                                    <td>
                                        ` + val.name + `
                                    </td>
                                    <td class="center">
                                        <button class="btn red" type="button" onclick="removeUser(this)">Remove</button>
                                    </td>
                                </tr>
                            `);
                            }
                            
                        });                        
                    }else{
                        $('#body-pic').empty().append(`
                            <tr id="empty-detail">
                                <td colspan="10" class="center">
                                    Blm ada User
                                </td>
                            </tr>
                        `);
                    }
                    
                    $('#top').val(response.top);

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
            $('#body-pic').empty().append(`
                <tr id="empty-detail">
                    <td colspan="10" class="center">
                        Blm ada User
                    </td>
                </tr>
            `);
        }
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