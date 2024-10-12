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
                                                        <label for="start_date" style="font-size:1rem;">Tanggal Awal :</label>
                                                        <input type="date" max="{{ date('9999'.'-12-31') }}" id="start_date" name="start_date" value="{{ date('Y-m-d') }}">
                                                    </div>
                                                    <div class="col m3 s6 ">
                                                        <label for="finish_date" style="font-size:1rem;">Tanggal Akhir :</label>
                                                        <input type="date" max="{{ date('9999'.'-12-31') }}" id="finish_date" name="finish_date" value="{{ date('Y-m-d') }}">
                                                    </div>
                                                    <div class="col m6 s6 pt-2">
                                                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="exportExcel();">
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
                                    <div class="col s12 m12" style="overflow: auto">
                                        <div class="result" style="width:2500px;">
                                            <table class="bordered" style="font-size:10px;">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('translations.no') }}.</th>
                                                        <th>No. Dokumen</th>
                                                        <th>{{ __('translations.status') }}</th>
                                                        <th>Voider</th>
                                                        <th>Tgl. Void</th>
                                                        <th>Ket. Void</th>
                                                        <th>Deleter</th>
                                                        <th>Tgl. Delete</th>
                                                        <th>Ket. Delete</th>
                                                        <th>Doner</th>
                                                        <th>Tgl. Done</th>
                                                        <th>Ket. Done</th>
                                                        <th>NIK</th>
                                                        <th>{{ __('translations.user') }}</th>
                                                        <th>{{ __('translations.post_date') }}</th>
                                                        <th>Status Kirim</th>
                                                        <th>Tgl. Kirim</th>
                                                        <th>Tipe Pengiriman</th>
                                                        <th>Ekspedisi</th>
                                                        <th>Pelanggan</th>
                                                        <th>Kode Item</th>
                                                        <th>{{ __('translations.item') }}</th>
                                                        <th>Plant</th>
                                                        <th>Qty Konversi</th>
                                                        <th>Satuan Konversi</th>
                                                        <th>Qty </th>
                                                        <th>{{ __('translations.unit') }}</th>
                                                        <th>Note Internal</th>
                                                        <th>Note External</th>
                                                        <th>Note </th>
                                                        <th>No.SJ</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="detail_mod">
                                                    <tr>
                                                        <td class="center-align" colspan="20">Silahkan pilih tanggal dan tekan tombol filter.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div> --}}
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
    function exportExcel(){

        var status = $('#filter_status').val();
        var type_buy = $('#filter_inventory').val();
        var type_deliv = $('#filter_delivery').val();
        var company = $('#filter_company').val();
        var type_pay = $('#filter_payment').val();
        var supplier = $('#filter_account').val();
        var sender = $('#filter_sender').val();
        var sales = $('#filter_sales').val();
        var currency = $('#filter_currency').val();
        var start_date = $('#start_date').val();
        var end_date = $('#finish_date').val();
        var account_id = $('#filter_account').val();
        var $search = '';
        window.location = "{{ Request::url() }}/export?start_date=" + start_date+"&end_date=" + end_date + "&status=" + status + "&type_buy=" + type_buy + "&type_deliv=" + type_deliv + "&company=" + company + "&type_pay=" + type_pay + "&supplier=" + supplier + "&currency=" + currency + "&start_date=" + start_date;

    }
    function exportCsv(){
        var start_date = $('#start_date').val(), end_date = $('#end_date').val();
        window.location = "{{ Request::url() }}/export_csv?start_date=" + start_date + "&end_date=" + end_date;
    }
    function filter(){
        var formData = new FormData($('#form_data_filter')[0]);
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
                loadingOpen('#main-display');
            },
            success: function(response) {
                loadingClose('#main-display');
                if(response.status == 200) {
                    $('#detail_mod').empty();
                    if(response.content.length > 0){
                        $.each(response.content, function(i, val) {
                            $('#detail_mod').append(`
                                <tr>
                                    <td>`+val.no+`</td>
                                    <td>`+val.no_document+`</td>
                                    <td>`+val.status+`</td>
                                    <td>`+val.voider+`</td>
                                    <td>`+val.tgl_void+`</td>
                                    <td>`+val.ket_void+`</td>
                                    <td>`+val.deleter+`</td>
                                    <td>`+val.tgl_delete+`</td>
                                    <td>`+val.ket_delete+`</td>
                                    <td>`+val.doner+`</td>
                                    <td>`+val.tgl_done+`</td>
                                    <td>`+val.ket_done+`</td>
                                    <td>`+val.nik+`</td>
                                    <td>`+val.user+`</td>
                                    <td>`+val.post_date+`</td>
                                    <td>`+val.status_kirim+`</td>
                                    <td>`+val.tgl_kirim+`</td>
                                    <td>`+val.tipe_pengiriman+`</td>
                                    <td>`+val.ekspedisi+`</td>
                                    <td>`+val.pelanggan+`</td>
                                    <td>`+val.kode_item+`</td>
                                    <td>`+val.item+`</td>
                                    <td>`+val.plant+`</td>
                                    <td>`+val.qty_konversi+`</td>
                                    <td>`+val.satuan_konversi+`</td>
                                    <td>`+val.qty+`</td>
                                    <td>`+val.unit+`</td>
                                    <td>`+val.note_internal+`</td>
                                    <td>`+val.note_external+`</td>
                                    <td>`+val.note+`</td>
                                    <td>`+val.no_sj+`</td>
                                </tr>
                            `);
                        });
                        $('#detail_mod').append(`
                            <tr>
                                <td class="" colspan="20">Waktu Proses : <b>` + response.execution_time + ` Detik</b></td>
                            </tr>
                        `);
                    }else{
                        $('#detail_mod').append(`
                            <tr>
                                <td class="center-align" colspan="20">Data tidak ditemukan.</td>
                            </tr>
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

    function reset(){
        $('#form_data_filter')[0].reset();

    }
</script>
