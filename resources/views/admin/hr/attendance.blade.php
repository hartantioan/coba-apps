<style>
    .grid {
        position: relative;
        margin: 0 auto;
        /*padding: 1em 0 4em;*/
        /*max-width: 1000px;*/
        list-style: none;
        text-align: center;
    }

    /* Common style */
    .grid figure {
        position: relative;
        float: left;
        overflow: hidden;
        margin: 10px 1%;
        min-width: 320px;
        max-width: 480px;
        max-height: 360px;
        width: 100%;
        /*background: #3085a3;*/
        text-align: center;
        cursor: pointer;
    }

    body.tab-active input:focus {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .modal-content .select2.tab-active {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .grid figure img {
        position: relative;
        display: block;
        min-height: 100%;
        max-width: 100%;
        opacity: 0.8;
    }

    .grid figure figcaption {
        padding: 2em;
        color: #fff;
        text-transform: uppercase;
        font-size: 1.25em;
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
    }

    .grid figure figcaption::before,
    .grid figure figcaption::after {
        pointer-events: none;
    }

    .grid figure figcaption,
    .grid figure figcaption > a {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    /* Anchor will cover the whole item by default */
    /* For some effects it will show as a button */
    .grid figure figcaption > a {
        z-index: 8;
        text-indent: 200%;
        white-space: nowrap;
        font-size: 0;
        opacity: 0;
    }

    .grid figure h2 {
        word-spacing: -0.15em;
        font-weight: 300;
    }

    .grid figure h2 span {
        font-weight: 800;
    }

    .grid figure h2,
    .grid figure p {
        margin: 0;
    }

    .grid figure p {
        letter-spacing: 1px;
        font-size: 68.5%;
    }

    .modal {
        top:0px !important;
    }
    figure.effect-dexter {
        background: -webkit-linear-gradient(top, rgba(37,141,200,1) 0%, rgba(104,60,19,1) 100%);
        background: linear-gradient(to bottom, rgba(37,141,200,1) 0%,rgba(104,60,19,1) 100%); 
    }

    figure.effect-dexter img {
        -webkit-transition: opacity 0.35s;
        transition: opacity 0.35s;
    }
    
    figure.effect-dexter:hover img {
        opacity: 0.4;
    }
    figure.clicked img {
        opacity: 0.4;
    }

    figure.effect-dexter figcaption::after {
        position: absolute;
        right: 30px;
        bottom: 30px;
        left: 30px;
        height: -webkit-calc(50% - 30px);
        height: calc(50% - 30px);
        border: 7px solid #fff;
        content: '';
        -webkit-transition: -webkit-transform 0.35s;
        transition: transform 0.35s;
        -webkit-transform: translate3d(0,-100%,0);
        transform: translate3d(0,-100%,0);
    }

    figure.effect-dexter:hover figcaption::after {
        -webkit-transform: translate3d(0,0,0);
        transform: translate3d(0,0,0);
    }

    figure.clicked figcaption::after {
        -webkit-transform: translate3d(0,0,0);
        transform: translate3d(0,0,0);
    }

    figure.effect-dexter figcaption {
        padding: 3em;
        text-align: left;
    }

    figure.effect-dexter p {
        position: absolute;
        right: 60px;
        bottom: 60px;
        left: 60px;
        opacity: 0;
        -webkit-transition: opacity 0.35s, -webkit-transform 0.35s;
        transition: opacity 0.35s, transform 0.35s;
        -webkit-transform: translate3d(0,-100px,0);
        transform: translate3d(0,-100px,0);
    }

    figure.effect-dexter:hover p {
        opacity: 1;
        -webkit-transform: translate3d(0,0,0);
        transform: translate3d(0,0,0);
    }

    figure.clicked p {
        opacity: 1;
        -webkit-transform: translate3d(0,0,0);
        transform: translate3d(0,0,0);
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
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="loadDataTable();">
                            <i class="material-icons hide-on-med-and-up">refresh</i>
                            <span class="hide-on-small-onl">Refresh</span>
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
                                        <div id="card-stats" class="row">
                                            @foreach($machine as $key=>$row)
                                                @if (intval($row->log_counts) > 10000 && intval($row->log_counts) < 20000)
                                                <div class="col s12 m6 xl3">
                                                    <div class="card">
                                                        <div class="card-content orange lighten-1 white-text" style="height: 8rem">
                                                            <p class=" white-text"  style="margin: 0.8rem 0 0.6rem 0;font-weight: 600;">{{$row->name}}</p>
                                                            <p>{{$row->ip_address}}</p>
                                                            <p class="card-stats-compare ">
                                                                <i class="material-icons" style="color: #cc0018">warning</i>
                                                                <span class="text-lighten-5 " style="color: #cc0018">Data sudah melebihi 10000</span>
                                                            </p>
                                                        </div>
                                                        <div class="card-action orange">
                                                            <div class="row">
                                                                <div class="col s12">
                                                                    <p class="card-stats-compare">
                                                                        <span class="" style="font-size: 1rem">Total Log Absen: </span>
                                                                        <span class="" style="font-size: 1rem;font-weight:600">{{$row->log_counts}}</span>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @elseif (intval($row->log_counts) > 20000)
                                                    <div class="col s12 m6 xl3">
                                                        <div class="card">
                                                            <div class="card-content red accent-2 white-text" style="height: 8rem">
                                                                <p class=" white-text"  style="margin: 0.8rem 0 0.6rem 0;font-weight: 600;">{{$row->name}}</p>
                                                                <p>{{$row->ip_address}}</p>
                                                                <p class="card-stats-compare ">
                                                                    <i class="material-icons" style="color: #7d0211">warning</i>
                                                                    <span class="text-lighten-5 " style="color: #7d0211">Data sudah melebihi 20000</span>
                                                                </p>
                                                            </div>
                                                            <div class="card-action red darken-1">
                                                                <div class="row">
                                                                    <div class="col s12">
                                                                        <p class="card-stats-compare">
                                                                            <span class="" style="font-size: 1rem">Total Log Absen: </span>
                                                                            <span class="" style="font-size: 1rem;font-weight:600">{{$row->log_counts}}</span>
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                <div class="col s12 m6 xl3">
                                                    <div class="card">
                                                       <div class="card-content green lighten-1 white-text" style="height: 8rem">
                                                            <p class=" white-text"  style="margin: 0.8rem 0 0.6rem 0;font-weight: 600;">{{$row->name}}</p>
                                                            <p>{{$row->ip_address}}</p>
                                                            
                                                        </div>
                                                       <div class="card-action green">
                                                            <div class="row">
                                                                <div class="col s12">
                                                                    <p class="card-stats-compare">
                                                                        <span class="" style="font-size: 1rem">Total Log Absen: </span>
                                                                        <span class="" style="font-size: 1rem;font-weight:600">{{$row->log_counts}}</span>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                 </div>
                                                @endif
                                            @endforeach
                                            
                                            
                                         </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">List Data</h4>
                                    <div class="row">
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
                                                        <th>NIK</th>
                                                        <th>Nama</th>
                                                        <th>Tanggal</th>
                                                        <th>Tipe</th>
                                                        <th>Lokasi</th>
                                                        <th>Longitude</th>
                                                        <th>Latitude</th>
            
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
                <h4>Sinkronisasi {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12" style="padding-left: 2em;padding-right:2em;">
                        @foreach($machine as $key=>$row)
                        <div class="col s12 m4 grid">
                            <figure class="effect-dexter" style="height: 20em;" data-machine-id="{{ $row->id }}" data-machine-ip="{{$row->ip_address}}">
                                <img src="{{ url('website/mesinfingerprint'.$key.'.jpg') }}" alt="img19" >
                                <figcaption>
                                <h5 style="color: white">{{$row->name}} </h5>
                                <p>{{$row->location}}-{{$row->code}}</p>
                                <a href="#">View more</a>
                                </figcaption>
                            </figure>
                        </div>
                        @endforeach
                        <div class="col s12 mt-3">
                            <button class="btn waves-effect waves-light right submit" onclick="save();">Simpan <i class="material-icons right">send</i></button>
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

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div>

<!-- END: Page Main-->
<script>
    document.addEventListener('focusin', function (event) {
        const select2Container = event.target.closest('.modal-content .select2');
        const activeSelect2 = document.querySelector('.modal-content .select2.tab-active');
        if (event.target.closest('.modal-content')) {
            document.body.classList.add('tab-active');
        }
        
        // Remove highlighting from previous Select2 input
        if (activeSelect2 && !select2Container) {
            activeSelect2.classList.remove('tab-active');
        }

        // Add highlighting to the new Select2 input
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
    var clickedFigures = [];
    var selectedMachines=[];
    var selectedIP=[];
    $(document).ready(function() {
        $(document).on('click', '.effect-dexter', function() {
            var machineId = $(this).data('machine-id');
            var machineIP = $(this).data('machine-ip');
            if (selectedMachines.includes(machineId)) {
                selectedMachines = selectedMachines.filter(id => id !== machineId);
            } else {
                selectedMachines.push(machineId);
            }
            if (selectedIP.includes(machineIP)) {
                selectedIP = selectedIP.filter(id => id !== machineIP);
            } else {
                selectedIP.push(machineIP);
            }
            if (!$(this).hasClass('clicked')) {

            $(this).addClass('clicked');
           
            clickedFigures.push(this);
            } else {
            $(this).removeClass('clicked');
          
            clickedFigures.splice(clickedFigures.indexOf(this), 1);
            }
        });
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
            columns: [
                { name: 'id', searchable: false, className: 'center-align details-control' },
                { name: 'code', className: 'center-align' },
                { name: 'employee_no', className: 'center-align' },
                { name: 'employee_no', className: 'center-align' },
                { name: 'date', className: 'center-align' },
                { name: 'verify_type', className: 'center-align' },
                { name: 'location', className: 'center-align' },
                { name: 'latitude', searchable: false, orderable: false, className: 'center-align' },
                { name: 'longitude', searchable: false, orderable: false, className: 'center-align' },
            ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle' 
            ]
        });
        $('.dt-buttons').appendTo('#datatable_buttons');

        $('select[name="datatable_serverside_length"]').addClass('browser-default');
	}

    function save(){
		console.log(selectedIP);
        
        /* var formData = new FormData($('#form_data')[0]); */
        
        $.ajax({
            url: '{{ Request::url() }}/syncron',
            type: 'POST',
            data: JSON.stringify({
                ip_address: selectedIP,
                id_machines: selectedMachines
            }),
            contentType: 'application/json',
            dataType: 'json', 
            processData: false,
            cache: true,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('#datatable_serverside');
            },
            complete: function() {
                loadingClose('#datatable_serverside');
            },
            success: function(response) {
                loadingClose('.modal-content');
                console.log(response);
                if(response.status == 200) {
                    success();
                    /* M.toast({
                        html: response.success
                    }); */
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
                }
            },
            error: function() {
                loadingClose('#datatable_serverside');
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
                $('#code').val(response.code);
                $('#name').val(response.name);
                $('#ip_address').val(response.ip_address);
                $('#port').val(response.port);
                $('#location').val(response.location);
                if(response.status == '1'){
                    $('#status').prop( "checked", true);
                }else{
                    $('#status').prop( "checked", false);
                }
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