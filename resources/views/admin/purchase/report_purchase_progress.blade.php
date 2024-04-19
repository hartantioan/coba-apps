<style>
    .modal {
        top:0px !important;
    }
    .select2-selection--multiple{
        overflow-y: scroll !important;
        height: auto !important;
    }
    .select2{
        height: fit-content !important;
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
                </div>
            </div>
        </div>
        <div class="col s12">
            <!-- Search for small screen-->
            <div class="container">
                <div class="section section-data-tables">
                    <div class="row">
                        <div class="col s12">
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">
                                        Progress Purchasing 
                                    </h4>
                                    <form class="row" id="form_data" onsubmit="return false;">
                                        <div class="col s12">
                                            <div id="validation_alert" style="display:none;"></div>
                                        </div>
                                        <div class="col s12">
                                            <div class="row">
                                                <div class="input-field col m4 s12">
                                                    <input id="start_date" name="start_date" type="date" placeholder="Tgl. posting" value="{{ date('Y-m').'-01' }}">
                                                    <label class="active" for="start_date">Tanggal Awal</label>
                                                </div>
                                                <div class="input-field col m4 s12">
                                                    <input id="end_date" name="end_date" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                                    <label class="active" for="end_date">Tanggal Akhir</label>
                                                </div>
                                                <div class="input-field col m4 s12">
                                                    
                                                    <select class="form-control" id="type" name="type" onchange="loadDataTable()">
                                                        <option value="all">Semua</option>
                                                        <option value="sisa">Sisa</option>
                                                    </select>
                                                    <label for="type" style="font-size:1rem;">Tipe :</label>
                                                </div>
                                                <div class="col m12 s12">
                                                </div>
                                                <div class="input-field  col m12 s12 ">
                                                    <label for="filter_group" class="active" style="font-size:1rem;">Filter Group :</label>
                                                    
                                                    <select class="select2 browser-default" multiple="multiple" id="filter_group" name="filter_group[]" onchange="loadDataTable()">
                                                        @foreach($group->whereNull('parent_id') as $c)
                                                            @if(!$c->childSub()->exists())
                                                                <option value="{{ $c->id }}"> - {{ $c->name }}</option>
                                                            @else
                                                                <optgroup label=" - {{ $c->code.' - '.$c->name }}">
                                                                @foreach($c->childSub as $bc)
                                                                    @if(!$bc->childSub()->exists())
                                                                        <option value="{{ $bc->id }}"> -  - {{ $bc->name }}</option>
                                                                    @else
                                                                        <optgroup label=" -  - {{ $bc->code.' - '.$bc->name }}">
                                                                            @foreach($bc->childSub as $bcc)
                                                                                @if(!$bcc->childSub()->exists())
                                                                                    <option value="{{ $bcc->id }}"> -  -  - {{ $bcc->name }}</option>
                                                                                @else
                                                                                    <optgroup label=" -  -  - {{ $bcc->code.' - '.$bcc->name }}">
                                                                                        @foreach($bcc->childSub as $bccc)
                                                                                            @if(!$bccc->childSub()->exists())
                                                                                                <option value="{{ $bccc->id }}"> -  -  -  - {{ $bccc->name }}</option>
                                                                                            @else
                                                                                                <optgroup label=" -  -  -  - {{ $bccc->code.' - '.$bccc->name }}">
                                                                                                    @foreach($bccc->childSub as $bcccc)
                                                                                                        @if(!$bcccc->childSub()->exists())
                                                                                                            <option value="{{ $bcccc->id }}"> -  -  -  -  - {{ $bcccc->name }}</option>
                                                                                                        @endif
                                                                                                    @endforeach
                                                                                                </optgroup>
                                                                                            @endif
                                                                                        @endforeach
                                                                                    </optgroup>
                                                                                @endif
                                                                            @endforeach
                                                                        </optgroup>
                                                                    @endif
                                                                @endforeach
                                                                </optgroup>
                                                            @endif
                                                    @endforeach
                                                    </select>
                                                </div>
                                                <div class="col m6 s12">
                                                    <button class="btn waves-effect waves-light  submit" onclick="exportExcel();">Export <i class="material-icons right">file_download</i></button>
                                                    <button class="btn waves-effect waves-light  cyan submit" onclick="filter();">Process <i class="material-icons right">list</i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="card" id="show-result" style="display:none;">
                                <div class="card-content">
                                    <div class="row">
                                        <div class="col s12" >

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="content-result">
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- END: Page Main-->
<script>
    $(function() {
        
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });
       
    });
    function filter(){
        var formData = new FormData($('#form_data')[0]);
       
        $.ajax({
            url: '{{ Request::url() }}/filter',
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
                $('#validation_alert').html('');
                loadingOpen('#main');
            },
            success: function(response) {
                loadingClose('#main');
                if(response.status == 200) {
                    $('#content-result').html('');
                    $('#content-result').append(response.message);
                    M.toast({
                        html: 'Sukses proses data'
                    });
                } else {
                    M.toast({
                        html: response.message
                    });
                }
            },
            error: function() {
                $('#main').scrollTop(0);
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
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        var type = $('#type').val();
        var group = $('#filter_group').val() ? $('#filter_group').val():'';
        window.location = "{{ Request::url() }}/export?start_date=" + startDate + "&end_date=" + endDate+ "&type=" + type+ "&group=" + group;
    }
</script>