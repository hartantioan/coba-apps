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

                                                <div class="row">
                                                    <div class="col m3 s6 ">
                                                        <label for="start_date" style="font-size:1rem;">Tanggal Mulai Posting :</label>
                                                        <input type="date" max="{{ date('9999'.'-12-31') }}" id="start_date" name="start_date" value="{{ date('Y-m-d') }}">
                                                    </div>
                                                    <div class="col m3 s6 ">
                                                        <label for="finish_date" style="font-size:1rem;">Tanggal Akhir Posting :</label>
                                                        <input type="date" max="{{ date('9999'.'-12-31') }}" id="finish_date" name="finish_date" value="{{ date('Y-m-d') }}">
                                                    </div>
                                                    <div class="col m6 s6 pt-2">

                                                        <a id="export_button" class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="exportExcel();">
                                                            <i class="material-icons hide-on-med-and-up">view_list</i>
                                                            <span class="hide-on-small-onl">Excel</span>
                                                            <i class="material-icons right">view_list</i>
                                                        </a>

                                                        {{-- <a id="export_button_new" class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="exportExcelNew();">
                                                            <i class="material-icons hide-on-med-and-up">view_list</i>
                                                            <span class="hide-on-small-onl">Excel New</span>
                                                            <i class="material-icons right">view_list</i>
                                                        </a> --}}

                                                    </div>
                                                </div>
                                            </div>

                                            </div>
                                        </form>
                                    </div>
                                </li>
                            </ul>
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

<script>
    function exportExcel(){
        swal({
            title: 'ALERT',
            text: 'Mohon Jangan Diketik Terus Menerus untuk export. Excel anda sedang diproses mohon ditunggu di notifikasi untuk mendownload.',

        });
        $('#validation_alert').show();
        $('#validation_alert').append(`
            <div class="card-alert card red">
                <div class="card-content white-text">
                    <p>ALERT: MOHON TUNGGU EXPORT SELESAI. KARENA DAPAT MEMBUAT EXCEL KEDOBELAN. TERIMAKASIH</p>
                </div>
            </div>
        `);
        $('#export_button').hide();
        var finish_date = $('#finish_date').val();
        var start_date = $('#start_date').val();
        var $search = '';
        $.ajax({
            url: '{{ Request::url() }}/export',
            type: 'POST',
            dataType: 'JSON',
            data: {
                finish_date: finish_date,
                start_date : start_date,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('#main-display');
            },
            success: function(response) {
                loadingClose('#main-display');
                M.toast({
                    html: response.message
                });
            },
            error: function() {
                $('#main-display').scrollTop(0);
                loadingClose('#main-display');
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
            }
        });

        // window.location = "{{ Request::url() }}/export?start_date=" + start_date + "&place_id=" + place_id + "&warehouse_id=" + warehouse_id;

    }

    function exportExcelNew() {

        var finish_date = $('#finish_date').val();
        var start_date = $('#start_date').val();
        window.location = "{{ Request::url() }}/export_new?start_date=" + start_date + "&end_date=" + finish_date;

    }


</script>


