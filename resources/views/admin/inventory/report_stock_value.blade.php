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
                                    </form>
                                </div>
                        </div>

                    </div>
                    </form>
                </div>
                </li>
                </ul>
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
                                        <form class="row" id="form_data_item_move" onsubmit="return false;">
                                            <div class="col s12">
                                                <div id="validation_alert" style="display:none;"></div>
                                            </div>
                                            <div class="col s12">

                                                <div class="row">
                                                    <div class="input-field col m4 s12">
                                                        <select class="form-control" id="type" name="type">
                                                            <option value="all">DENGAN MUTASI</option>
                                                            <option value="final">TANPA MUTASI (FINAL)</option>
                                                        </select>
                                                        <label for="type">{{ __('translations.type') }}</label>
                                                    </div>

                                                    <div class="input-field col m4 s12">
                                                        <input id="start_date_item_move" name="start_date_item_move" type="date" max="{{ date('9999-12-31') }}" placeholder="Tgl. posting" value="{{ date('Y-m').'-01' }}">
                                                        <label class="active" for="start_date_item_move">Tanggal Awal</label>
                                                    </div>

                                                    <div class="input-field col m4 s12">
                                                        <input id="finish_date_item_move" name="finish_date_item_move" type="date" max="{{ date('9999-12-31') }}" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                                        <label class="active" for="finish_date_item_move">Tanggal Akhir</label>
                                                    </div>

                                                    <div class="col m12 s12"></div>


                                                    <div class="input-field col m4 s12">
                                                        <select class="select2 browser-default" id="item_id_item_move" name="item_id">
                                                        </select>
                                                        <label class="active" for="item_id_item_move">ITEM</label>
                                                    </div>

                                                    <div class="col m12 s12"></div>

                                                    <div class="col m1" id="export_button">
                                                        <button class="btn waves-effect waves-light right submit mt-2" onclick="exportExcelItemMove();">Excel<i class="material-icons right">view_list</i></button>
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
            </div>

        </div>
    </div>
</div>
<div class="content-overlay"></div>

<script>
    $(function() {
        select2ServerSide('#sales_id,#filter_sales', '{{ url("admin/select2/employee") }}');
        select2ServerSide('#sender_id,#filter_sender', '{{ url("admin/select2/vendor") }}');
        select2ServerSide('#account_id,#filter_account', '{{ url("admin/select2/customer") }}');
    });



    function exportExcel() {

        var status = $('#filter_status').val();
        var finish_date = $('#finish_date').val();
        var start_date = $('#start_date').val();
        window.location = "{{ Request::url() }}/export?start_date=" + start_date + "&finish_date=" + finish_date+ "&status=" + status;

    }

    function reset() {
        $('#form_data_filter')[0].reset();

    }
</script>

<script>
    $(function() {
        $('#type_item_move').on('change', function() {
            var selectedType = $(this).val();

            if (selectedType === 'final') {
                $('#start_date_item_move').prop('disabled', true);
            } else {
                $('#start_date_item_move').prop('disabled', false);
            }
        });

        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        select2ServerSide('#item_id_item_move', '{{ url("admin/select2/simple_item") }}');
    });
    function exportExcelItemMove() {
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
        var item = $('#item_id_item_move').val() ? $('#item_id_item_move').val() : '';
        var type = $('#type_item_move').val() ? $('#type_item_move').val() : '';
        var startdate = $('#start_date_item_move').val() ? $('#start_date_item_move').val() : '';
        var finishdate = $('#finish_date_item_move').val() ? $('#finish_date_item_move').val() : '';
        $.ajax({
            url: '{{ Request::url() }}/export_item_move',
            type: 'POST',
            dataType: 'JSON',
            data: {
                item : item,
                type : type,
                startdate : startdate,
                finishdate : finishdate,
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
    }
</script>
