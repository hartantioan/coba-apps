<!-- BEGIN: Page Main-->
<style>

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
                            <li class="breadcrumb-item"><a href="#">{{ Str::title(str_replace('_',' ',Request::segment(3))) }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{ Str::title(str_replace('_',' ',Request::segment(4))) }}
                            </li>
                        </ol>
                    </div>
                    <div class="col s4 m6 l6">
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="loadDataTable();">
                            <i class="material-icons hide-on-med-and-up">refresh</i>
                            <span class="hide-on-small-onl">{{ __('translations.refresh') }}</span>
                            <i class="material-icons right">refresh</i>
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
                            <div class="card-panel">
                                <div class="row">
                                    <div class="col s12 ">
                                        <label for="filter_status" style="font-size:1.2rem;">{{ __('translations.filter_status') }} :</label>
                                        <div class="input-field inline" style="margin-top: 0;margin-bottom: 0;">
                                            <select class="form-control" id="filter_status" onchange="loadDataTable()">
                                                <option value="">{{ __('translations.all') }}</option>
                                                <option value="1">{{ __('translations.active') }}</option>
                                                <option value="2">{{ __('translations.non_active') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">{{ __('translations.list_data') }}</h4>
                                    <div class="row">
                                        <div class="col s12">
                                            <div id="datatable_buttons"></div>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-2" href="javascript:void(0);" onclick="exportExcel();">
                                                <i class="material-icons hide-on-med-and-up">view_headline</i>
                                                <span class="hide-on-small-onl">Export</span>
                                                <i class="material-icons right">view_headline</i>
                                            </a>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">{{ __('translations.refresh') }}</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <table id="datatable_serverside" >
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{ __('translations.code') }}</th>
                                                        <th>{{ __('translations.name') }}</th>
                                                        <th>Nama Lain</th>
                                                        <th>Grup</th>
                                                        <th>Gambar</th>
                                                        <th>{{ __('translations.action') }}</th>
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

<div id="modal1" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <h4>{{ __('translations.add') }}/{{ __('translations.edit') }} {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="input-field col s12 m12">
                            <input type="hidden" id="temp" name="temp">
                        </div>
                        <div class="col m4 s12 step6">
                            <label class="">Bukti Upload</label>
                            <br>
                            <input type="file" name="file" id="fileInput" style="display: none;">
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
                        <div class="col s12 mt-3">
                            <button class="btn waves-effect waves-light right submit" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
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

<div id="modal_upload" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <form class="row" id="form_data_upload" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('translations.code') }}</th>
                                
                                    <th>Gambar</th>
                                </tr>
                            </thead>
                            <tbody id="lembar_upload">

                            </tbody>
                        </table>
                        <div class="input-field col s12 m12">
                            <input type="hidden" id="temp" name="temp">
                        </div>
                        
                        <div class="col s12 mt-3">
                            <button class="btn waves-effect waves-light right submit" onclick="saveMulti();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
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

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" onclick="uploadMulti()">
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
    $(function() {
        loadDataTable();
        
        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
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
                imagePreview.src = ''; 
                imagePreview.style.display = 'none';
                M.updateTextFields();
            }
        });

        $('#modal_upload').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            
                M.updateTextFields();
            },
            onCloseEnd: function(modal, trigger){
                $('#lembar_upload').html('');
                M.updateTextFields();
            }
        });

    });

    function loadDataTable() {
		window.table = $('#datatable_serverside').DataTable({
            "scrollCollapse": true,
            "scrollY": '400px',
            "responsive": true,
            "stateSave": true,
            "serverSide": true,
            "deferRender": true,
            "destroy": true,
            "iDisplayInLength": 10,
            "order": [[0, 'desc']],
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    status : $('#filter_status').val()
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
            select: {
                style: 'multi'
            },
            columns: [
                { name: 'id', searchable: false, className: 'center-align details-control' },
                { name: 'code', className: 'center-align' },
                { name: 'name', className: 'center-align' },
                { name: 'type', className: 'center-align' },
                { name: 'note', className: 'center-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle' 
            ]
        });
        $('.dt-buttons').appendTo('#datatable_buttons');

        $('select[name="datatable_serverside_length"]').addClass('browser-default');
	}
    
    function uploadMulti(){
        var arr_id_temp=[];
        $.map(window.table.rows('.selected').nodes(), function (item) {
            var poin = $(item).find('td:nth-child(2)').text().trim();
            arr_id_temp.push(poin);
        });
        $.each(arr_id_temp, function(i, val) {
            var count = makeid(10);
            $('#lembar_upload').append(`
                <tr>
                    <input type="hidden" name="arr_id[]" id="arr_id` + count + `" value="` + val + `">
                    <td>` + i + `</td>
                    <td>` + val + `</td>
                    <td>
                    <div class="col m4 s12 step6">
                        <br>
                        <input type="file" name="arr_file[]" id="fileInput`+count+`" style="display: none;">
                        <div  class="col m8 s12 " id="dropZone`+count+`" ondrop="dropHandler(event);" ondragover="dragOverHandler(event);" style="margin-top: 0.5em;height: 5em;border: 2px dashed #ccc;">
                            Drop image here or <a href="javascript:void(0);" id="uploadLink`+count+`">upload</a>
                            <br>
                            
                        </div>
                        <a class="waves-effect waves-light cyan btn-small" style="margin-top: 0.5em;margin-left:0.2em" id="clearButton`+count+`" href="javascript:void(0);">
                            Clear
                        </a>
                    </div>
                    <div class="col m4 s12">
                        <div id="fileName`+count+`"></div>
                        <img src="" alt="Preview" id="imagePreview`+count+`" style="display: none;max-width: 20em;
                        max-height: 20em;
                        min-height: 5em;
                        margin: 2px auto;">
                    </div>    
                    </td
                </tr>
            `);

            const dropZone = document.getElementById('dropZone'+count);
            const uploadLink = document.getElementById('uploadLink'+count);
            const fileInput = document.getElementById('fileInput'+count);
            const imagePreview = document.getElementById('imagePreview'+count);
            const clearButton = document.getElementById('clearButton'+count);
            const fileNameDiv = document.getElementById('fileName'+count);
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
        });

        
        $('#modal_upload').modal('open');
    }

    function saveMulti(){
			
        var formData = new FormData($('#form_data_upload')[0]);
        
        $.ajax({
            url: '{{ Request::url() }}/save_multi',
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

    function save(){
			
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
                imagePreview.src = response.images; 
                imagePreview.style.display = 'inline-block';
                $('.modal-content').scrollTop(0);
                $('#code').focus();
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

    function exportExcel(){
        var search = table.search();
        var status = $('#filter_status').val();

        window.location = "{{ Request::url() }}/export_from_page?search=" + search + "&status=" + status;
       
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
</script>