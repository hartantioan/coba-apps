<style>
    .select-wrapper, .select2-container {
        height:3.7rem !important;
    }
    .select2-selection--multiple{
        overflow-y: scroll !important;
        height: auto !important;
    }
    .select2{
        height: fit-content !important;
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
                                                    <div class="input-field col m3 s12 step3">
                                                        <input type="hidden" id="temp" name="temp">
                                                        <select class="browser-default" id="item_id" name="item_id" ></select>
                                                        <label class="active" for="item_id">Item RM / SM</label>
                                                    </div>
                                                    <div class="col m12 s12"></div>
                                                    <div class="col m3 s6 ">
                                                        <label for="start_date" style="font-size:1rem;">Tanggal Awal :</label>
                                                        <input type="date" max="{{ date('9999'.'-12-31') }}" id="start_date" name="start_date" value="{{ date('Y-m-d') }}">
                                                    </div>
                                                    <div class="col m3 s6 ">
                                                        <label for="finish_date" style="font-size:1rem;">Tanggal Akhir :</label>
                                                        <input type="date" max="{{ date('9999'.'-12-31') }}" id="finish_date" name="finish_date" value="{{ date('Y-m-d') }}">
                                                    </div>
                                                    <div class="col m6 s6 pt-2">
                                                        {{-- <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="filter();">
                                                            <i class="material-icons hide-on-med-and-up">search</i>
                                                            <span class="hide-on-small-onl">Filter</span>
                                                            <i class="material-icons right">search</i>
                                                        </a>
                                                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="reset();">
                                                            <i class="material-icons hide-on-med-and-up">loop</i>
                                                            <span class="hide-on-small-onl">Reset</span>
                                                            <i class="material-icons right">loop</i>
                                                        </a> --}}
                                                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="exportExcel();">
                                                            <i class="material-icons hide-on-med-and-up">view_list</i>
                                                            <span class="hide-on-small-onl">Excel</span>
                                                            <i class="material-icons right">view_list</i>
                                                        </a>
                                                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="exportExcelTransport();">
                                                            <i class="material-icons hide-on-med-and-up">view_list</i>
                                                            <span class="hide-on-small-onl">Excel Jasa Kirim</span>
                                                            <i class="material-icons right">view_list</i>
                                                        </a>
                                                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="whatPrinting();">
                                                            <i class="material-icons hide-on-med-and-up">picture_as_pdf</i>
                                                            <span class="hide-on-small-onl">PDF</span>
                                                            <i class="material-icons right">picture_as_pdf</i>
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

                    </div>
                </div>

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
                                                    <div class="input-field col m12 s12 step3">
                                                        <select class="browser-default" id="item_id_multi" name="item_id_multi"  multiple></select>
                                                        <label class="active" for="item_id_multi">Item RM / SM</label>
                                                    </div>
                                                    <div class="col m12 s12"></div>
                                                    <div class="col m3 s6 ">
                                                        <label for="start_date" style="font-size:1rem;">Tanggal Awal :</label>
                                                        <input type="date" max="{{ date('9999'.'-12-31') }}" id="start_date_multi" name="start_date_multi" value="{{ date('Y-m-d') }}">
                                                    </div>
                                                    <div class="col m3 s6 ">
                                                        <label for="finish_date" style="font-size:1rem;">Tanggal Akhir :</label>
                                                        <input type="date" max="{{ date('9999'.'-12-31') }}" id="finish_date_multi" name="finish_date_multi" value="{{ date('Y-m-d') }}">
                                                    </div>
                                                    <div class="col m6 s6 pt-2">

                                                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="whatPrintingPDF();">
                                                            <i class="material-icons hide-on-med-and-up">picture_as_pdf</i>
                                                            <span class="hide-on-small-onl">PDF</span>
                                                            <i class="material-icons right">picture_as_pdf</i>
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
        select2ServerSide('#item_id', '{{ url("admin/select2/item_rm_sm") }}');
        select2ServerSide('#item_id_multi', '{{ url("admin/select2/item_rm_sm") }}');
    });
    function exportExcel(){

        var item_id = $('#item_id').val();
        if (!item_id) {
            alert("Item ID cannot be empty. Please select an item.");
            return false;
        }
        var start_date = $('#start_date').val();
        var end_date = $('#finish_date').val();
        window.location = "{{ Request::url() }}/export?start_date=" + start_date+"&end_date=" + end_date + "&item_id=" + item_id;

    }

    function exportExcelTransport(){

        var item_id = $('#item_id').val();
        if (!item_id) {
            alert("Item ID cannot be empty. Please select an item.");
            return false;
        }
        var start_date = $('#start_date').val();
        var end_date = $('#finish_date').val();
        window.location = "{{ Request::url() }}/export_transport_service?start_date=" + start_date+"&end_date=" + end_date + "&item_id=" + item_id;

    }

    function whatPrinting() {
        var item_id = $('#item_id').val();
        if (!item_id) {
            alert("Item ID cannot be empty. Please select an item.");
            return false;
        }
        var start_date = $('#start_date').val();
        var end_date = $('#finish_date').val();

        var url = '{{ Request::url() }}/print_individual' +
            '?start_date=' + encodeURIComponent(start_date) +
            '&finish_date=' + encodeURIComponent(end_date) +
            '&item_id=' + encodeURIComponent(item_id);

        $.ajax({
            url: url,
            type: 'GET',
            beforeSend: function () {
                loadingOpen('.modal-content');
            },
            success: function (data) {
                loadingClose('.modal-content');
                console.log(data);
                var link = document.createElement('a');
                link.href = data.message;
                link.download = data.file_name;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
            error: function (xhr) {
                loadingClose('.modal-content');
                alert('Failed to generate ZIP. Please try again.');
            }
        });
    }

    function whatPrintingPDF() {
        var item_id = $('#item_id_multi').val();
        if (!item_id) {
            alert("Item ID cannot be empty. Please select an item.");
            return false;
        }
        var start_date = $('#start_date_multi').val();
        var end_date = $('#finish_date_multi').val();

        var url = '{{ Request::url() }}/print_multi_pdf' +
            '?start_date=' + encodeURIComponent(start_date) +
            '&finish_date=' + encodeURIComponent(end_date) +
            '&item_multi=' + encodeURIComponent(item_id);

        $.ajax({
            url: url,
            type: 'GET',
            beforeSend: function () {
                loadingOpen('.modal-content');
            },
            success: function (data) {
                loadingClose('.modal-content');
                console.log(data);
                var link = document.createElement('a');
                link.href = data.message;
                link.download = data.file_name;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
            error: function (xhr) {
                loadingClose('.modal-content');
                alert('Failed to generate ZIP. Please try again.');
            }
        });
    }


    function reset(){
        $('#form_data_filter')[0].reset();

    }
</script>
