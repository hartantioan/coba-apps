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
            <div class="container">
                <div class="row">
                    <div class="card ">
                        <div class="card-content">
                            <form class="row" id="form_data" onsubmit="return false;">
                                <div class="col s12">
                                    <div id="validation_alert" style="display:none;"></div>
                                </div>
                                <div class="col s12">
                                    <div class="row">


                                        <div class="input-field col m3 s12">
                                        </div>
                                        <div class="col m12 s12">
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <input id="start_date" name="start_date" type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. posting" value="{{ date('Y-m').'-01' }}">
                                            <label class="active" for="start_date">Tanggal Awal</label>
                                        </div>
                                        <div class="input-field col m3 s12">
                                            <input id="end_date" name="end_date"  type="date" max="{{ date('9999'.'-12-31') }}" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}">
                                            <label class="active" for="end_date">Tanggal Akhir</label>
                                        </div>
                                        <div class="input-field col m3 mt-1">
                                            <button class="btn waves-effect waves-light submit" onclick="filter();">Cari <i class="material-icons right">file_download</i></button>
                                        </div>

                                        <div  class="input-field col m3 mt-1" id="export_button">
                                            <button class="btn waves-effect waves-light right submit " onclick="exportExcel();">Excel<i class="material-icons right">view_list</i></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <table class="bordered" style="font-size:10px;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No Dokumen</th>
                                <th>Status</th>
                                <th>Voider</th>
                                <th>Void Date</th>
                                <th>Void Note</th>
                                <th>Deleter</th>
                                <th>Delete Date</th>
                                <th>Delete Note</th>
                                <th>Name</th>
                                <th>Post Date</th>
                                <th>Required Date</th>
                                <th>Business Partner</th>
                                <th>Type</th>
                                <th>Division</th>
                                <th>Company ID</th>
                                <th>Note</th>
                                <th>Termin Note</th>
                                <th>Payment Type</th>
                                <th>No Account</th>
                                <th>Name Account</th>
                                <th>Bank Account</th>
                                <th>Deskripsi</th>
                                <th>Qty</th>
                                <th>Unit</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                                <th>PPN</th>
                                <th>PPH</th>
                                <th>Grand Total</th>
                                <th>No Preq</th>
                                <th>No OPYM</th>
                                <th>TGL Bayar</th>
                            </tr>
                        </thead>
                        <tbody id="table_body">
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('#export_button').show();
    function filter(){
        var formData = new FormData($('#form_data')[0]);
        formData.append('group[]',$('#filter_group').val());
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
                $('#validation_alert_multi').html('');
                loadingOpen('#main');
            },
            success: function(response) {
                $('#export_button').show();
                loadingClose('#main');
                if(response.status == 200) {
                    $('#table_body').empty();
                    if (response.message.length > 0) {
                        $.each(response.message, function(i, val) {
                            $('#table_body').append(`
                                <tr>
                                    <td class="center-align">` + (i + 1) + `</td>
                                    <td>` + val.no_dokumen + `</td>
                                    <td>` + val.status + `</td>
                                    <td>` + val.voider + `</td>
                                    <td>` + val.void_date + `</td>
                                    <td>` + val.void_note + `</td>
                                    <td>` + val.deleter + `</td>
                                    <td>` + val.delete_date + `</td>
                                    <td>` + val.delete_note + `</td>
                                    <td>` + val.name + `</td>
                                    <td>` + val.post_date + `</td>
                                    <td>` + val.required_date + `</td>
                                    <td>` + val.bussiness_partner + `</td>
                                    <td>` + val.type + `</td>
                                    <td>` + val.division + `</td>
                                    <td>` + val.company_id + `</td>
                                    <td>` + val.note + `</td>
                                    <td>` + val.termin_note + `</td>
                                    <td>` + val.payment_type + `</td>
                                    <td>` + val.no_account + `</td>
                                    <td>` + val.name_account + `</td>
                                    <td>` + val.bank_account + `</td>
                                    <td>` + val.dekripsi + `</td>
                                    <td>` + val.qty + `</td>
                                    <td>` + val.unit + `</td>
                                    <td>` + val.harga + `</td>
                                    <td>` + val.subtotal + `</td>
                                    <td>` + val.ppn + `</td>
                                    <td>` + val.pph + `</td>
                                    <td>` + val.grandtotal + `</td>
                                    <td>` + val.no_preq + `</td>
                                    <td>` + val.no_opym + `</td>
                                    <td>` + val.tgl_bayar + `</td>
                                </tr>
                            `);
                        });
                        $('#table_body').append(`
                                <tr>
                                    <td class="center-align" colspan="30">`+response.time+`</td>
                                </tr>
                            `);
                        M.toast({
                            html: 'filtered'
                        });
                    }else{
                        $('#table_body').append(`
                            <tr>
                                <td colspan="6" class="center-align">BELUM ADA STOCK</td>
                            </tr>`);
                    }

                } else if(response.status == 422) {
                    $('#validation_alert_multi').show();
                    $('.modal-content').scrollTop(0);

                    swal({
                        title: 'Ups! Validation',
                        text: 'Check your form.',
                        icon: 'warning'
                    });

                    $.each(response.error, function(i, val) {
                        $.each(val, function(i, val) {
                            $('#validation_alert_multi').append(`
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
    $(function() {
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        select2ServerSide('#item_id', '{{ url("admin/select2/sales_item") }}');
    });
    function exportExcel(){
        var tipe = $('#type').val();
        var search = $('#start_date').val();
        var status = $('#end_date').val();
        window.location = "{{ Request::url() }}/export?start_date=" + search + "&end_date=" + status;

    }
</script>
