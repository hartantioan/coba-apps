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
                                    <div class="collapsible-header"><i class="material-icons">filter_list</i>{{ __('translations.filter') }}</div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col m4 s6 ">
                                                <label for="company" style="font-size:1rem;">Perusahaan :</label>
                                                <select class="form-control" id="company" name="company">
                                                    @foreach ($company as $rowcompany)
                                                        <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col m2 s6 ">
                                                <label for="date_start" style="font-size:1rem;">{{ __('translations.start_date') }} : </label>
                                                <input type="date" max="{{ date('9999'.'-12-31') }}" id="date_start" name="date_start" value="{{ date('Y-m'.'-01') }}">
                                            </div>
                                            <div class="col m2 s6 ">
                                                <label for="date_end" style="font-size:1rem;">{{ __('translations.end_date') }} :</label>
                                                <input type="date" max="{{ date('9999'.'-12-31') }}" id="date_end" name="date_end" value="{{ date('Y-m-d') }}">
                                            </div>
                                            <div class="col m4 s6 pt-2">
                                                <a class="btn btn-small green waves-effect waves-light breadcrumbs-btn mr-3 tooltipped" href="javascript:void(0);" onclick="process();" data-position="top" data-tooltip="Proses">
                                                    <i class="material-icons center">check</i>
                                                </a>
                                                <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3 tooltipped" href="javascript:void(0);" onclick="reset();" data-position="top" data-tooltip="Reset">
                                                    <i class="material-icons center">loop</i>
                                                </a>
                                                <a class="btn btn-small blue waves-effect waves-light breadcrumbs-btn mr-3 tooltipped" href="javascript:void(0);" onclick="exportExcel();" data-position="top" data-tooltip="Export Excel">
                                                    <i class="material-icons center">view_list</i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col m4 s6 ">
                                                <label for="coa_start" style="font-size:1rem;">Dari Coa</label>
                                                <select class="browser-default" id="coa_start" name="coa_start"></select>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="coa_end" style="font-size:1rem;">Sampai Coa</label>
                                                <select class="browser-default" id="coa_end" name="coa_end"></select>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="is_closing_journal" style="font-size:1rem;">Jurnal Closing</label>
                                                <div class="input-field">
                                                    <div class="switch mb-1">
                                                        <label>
                                                            Tampilkan
                                                            <input type="checkbox" id="is_closing_journal" name="is_closing_journal" value="1">
                                                            <span class="lever"></span>
                                                            Sembunyikan
                                                        </label>
                                                    </div>
                                                </div>
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
                                            Silahkan pilih bulan, coa dan tekan tombol hijau.
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
        $('.tooltipped').tooltip();
        $('#coa_start,#coa_end').select2({
            placeholder: '-- Kosong --',
            minimumInputLength: 1,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/coa_subsidiary_ledger") }}',
                type: 'GET',
                dataType: 'JSON',
                data: function(params) {
                    return {
                        search: params.term,
                        company_id: $('#company').val(),
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.items
                    }
                }
            }
        });
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
        $('#date_start,#date_end').val('{{ date("Y-m-d") }}');
        $('#result').html('Silahkan pilih bulan, coa dan tekan tombol hijau.');
        $('#coa_start,#coa_end').empty();
        $('#is_closing_journal').prop('checked', false);
    }

    function process(){
        $.ajax({
            url: '{{ Request::url() }}/process',
            type: 'POST',
            dataType: 'JSON',
            data: {
                date_start : $('#date_start').val(),
                date_end : $('#date_end').val(),
                coa_start : $('#coa_start').val(),
                coa_end : $('#coa_end').val(),
                is_closing_journal : ($('#is_closing_journal').is(':checked') ? $('#is_closing_journal').val() : ''),
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
                    setTimeout(function () {
                        $("html, body").animate({ scrollTop: $(document).height() }, 1000);
                    }, 250);
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
        var datestart = $('#date_start').val();
        var dateend = $('#date_end').val();
        var coastart = $('#coa_start').val();
        var coaend = $('#coa_end').val();
        var is_closing_journal = ($('#is_closing_journal').is(':checked') ? $('#is_closing_journal').val() : '');
        $.ajax({
            url: '{{ Request::url() }}/export',
            type: 'POST',
            dataType: 'JSON',
            data: {
                datestart : datestart,
                dateend: dateend,
                coastart : coastart,
                coaend : coaend,
                is_closing_journal : is_closing_journal,
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
        // window.location = "{{ Request::url() }}/export?datestart=" + datestart + "&dateend=" + dateend+ "&coastart=" + coastart+ "&coaend=" + coaend + "&closing_journal=" + is_closing_journal;
    }
</script>
