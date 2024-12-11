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
                <div class="section">

                    <div class="row">
                        <div class="col s12 m12 l12" id="main-display">
                            <ul class="collapsible collapsible-accordion">
                                <li class="active">
                                <div class="collapsible-header"><i class="material-icons">filter_list</i>{{ __('translations.filter') }}</div>
                                <div class="collapsible-body">
                                    <form class="row" id="form_data_filter" onsubmit="return false;">
                                        <div class="col s12">
                                            <div id="validation_alert_multi" style="display:none;"></div>
                                        </div>
                                        <div class="col s12">


                                        </div>

                                        <div class="col m3 s6 ">
                                            <label for="start_date" style="font-size:1rem;">Tanggal Awal :</label>
                                            <input type="date" max="{{ date('9999'.'-12-31') }}" id="start_date" name="start_date" value="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="col m3 s6 ">
                                            <label for="finish_date" style="font-size:1rem;">Tanggal Akhir :</label>
                                            <input type="date" max="{{ date('9999'.'-12-31') }}" id="finish_date" name="finish_date" value="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="col m6 s6 pt-2">
                                            <a id="export_button" class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="exportExcel();">
                                                <i class="material-icons hide-on-med-and-up">view_list</i>
                                                <span class="hide-on-small-onl">Excel</span>
                                                <i class="material-icons right">view_list</i>
                                            </a>
                                        </div>
                                </div>
                        </div>

                    </div>
                    </form>
                </div>
                </li>
                </ul>
            </div>
            {{-- <div class="card">
                <div class="card-content">
                    <h4 class="card-title">
                        Hasil
                    </h4>
                    <div class="row">
                        <div class="col s12 m12">
                            <div class="result" style="overflow: auto !important;width:100% !important;">
                                Silakan Klik View
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

        </div>
    </div>
</div>
</div>
</div>
</div>


<!-- / Intro -->
</div>
<div class="content-overlay"></div>
</div>
</div>
</div>

<script>
    $(function() {
        select2ServerSide('#sales_id,#filter_sales', '{{ url("admin/select2/employee") }}');
        select2ServerSide('#sender_id,#filter_sender', '{{ url("admin/select2/vendor") }}');
        select2ServerSide('#account_id,#filter_account', '{{ url("admin/select2/customer") }}');
    });



    function exportExcel() {


        var finish_date = $('#finish_date').val();
        var start_date = $('#start_date').val();
        window.location = "{{ Request::url() }}/export?start_date=" + start_date + "&finish_date=" + finish_date;

    }


    function viewList() {

        let urlgas = '';
        var formData = new FormData($('#form_data_filter')[0]);
        urlgas = '{{ Request::url() }}/view';

        $.ajax({
            url: urlgas,
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
                if (response.status == 200) {
                    $('.result').html('');
                    if (response.content) {
                        $('.result').html(response.content);
                    } else {
                        $('.result').append(`
                        Silahkan Klik Button View / View (Compare With Stock).
                     `);
                    }
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

    function reset() {
        $('#form_data_filter')[0].reset();

    }
</script>
