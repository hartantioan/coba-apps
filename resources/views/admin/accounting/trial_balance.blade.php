<style>
    #modal2 {
        top:0px !important;
    }
    #text-grandtotal {
        font-size: 50px !important;
        font-weight: 800;
    }
    .select-wrapper, .select2-container {
        height:3rem !important;
    }
    .btn-small {
        padding: 0 1rem !important;
    }
    #data_detail > table > tbody > td{
        padding:2px !important;
    }
    table {
        border-collapse: separate !important;
    }
    table.bordered th, table.bordered td {
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
                            <li class="breadcrumb-item"><a href="#">{{ Str::title(str_replace('_',' ',Request::segment(3))) }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{ Str::title(str_replace('_',' ',Request::segment(4))) }}
                            </li>
                        </ol>
                    </div>
                    <div class="col s4 m6 l6">
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12">
            <div class="container">
                <div class="section section-data-tables">
                    <div class="row">
                        <div class="col s12">
                            <ul class="collapsible collapsible-accordion">
                                <li class="active">
                                    <div class="collapsible-header"><i class="material-icons">filter_list</i> FILTER</div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col m3 s6 ">
                                                <label for="company" style="font-size:1rem;">Perusahaan :</label>
                                                <select class="form-control" id="company" name="company">
                                                    @foreach ($company as $rowcompany)
                                                        <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col m2 s6 ">
                                                <label for="level" style="font-size:1rem;">Level :</label>
                                                <select class="form-control" id="level" name="level">
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                </select>
                                            </div>
                                            <div class="col m2 s6 ">
                                                <label for="month_start" style="font-size:1rem;">Bulan Mulai :</label>
                                                <input type="month" id="month_start" name="month_start" value="{{ date('Y-m'.'-01') }}">
                                            </div>
                                            <div class="col m2 s6 ">
                                                <label for="month_end" style="font-size:1rem;">Bulan Akhir :</label>
                                                <input type="month" id="month_end" name="month_end" value="{{ date('Y-m') }}">
                                            </div>
                                            <div class="col m2 s6 pt-2">
                                                <a class="btn btn-small green waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="process();">
                                                    <i class="material-icons center">check</i>
                                                </a>
                                                <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="reset();">
                                                    <i class="material-icons center">loop</i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">
                                        Hasil
                                    </h4>
                                    <div class="row">
                                        <div class="col s12 center-align" id="result" style="overflow:auto;">
                                            Silahkan pilih bulan dan tekan tombol hijau.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function(){
        
    });

    function exportExcel(){
        if($('.row_detail').length > 0){
            var date = $('#date').val();
            window.location = "{{ Request::url() }}/export?date=" + date;
        }else{
            swal({
                title: 'Ups!',
                text: 'Silahkan filter laporan terlebih dahulu ges.',
                icon: 'warning'
            });
        }
    }

    function reset(){
        $('#company').val($("#company option:first").val()).formSelect();
        $('#level').val($("#level option:first").val()).formSelect();
        $('#month_start,#month_end').val('{{ date("Y-m") }}');
        $('#result').html('
            Silahkan pilih bulan dan tekan tombol hijau.
        ');
    }

    function process(){
        $.ajax({
            url: '{{ Request::url() }}/process',
            type: 'POST',
            dataType: 'JSON',
            data: {
                level : $('#level').val(),
                company_id : $('#company').val(),
                month_start : $('#month_start').val(),
                month_end : $('#month_end').val(),
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
                    M.toast({
                        html: response.message
                    });
                    $('#result').html(response.html);
                } else {
                    M.toast({
                        html: response.message
                    });
                }
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
</script>