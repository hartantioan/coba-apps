<style>
    #text-grandtotal {
        font-size: 50px !important;
        font-weight: 800;
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
                <div class="section">
                    <div class="row">
                        <div class="col s12 m12 l12" id="main-display">
                            <ul class="collapsible collapsible-accordion">
                                <li class="active">
                                    <div class="collapsible-header"><i class="material-icons">filter_list</i> FILTER</div>
                                    <div class="collapsible-body">
                                        <form class="row" id="form_data_filter" onsubmit="return false;">
                                            <div class="col s12">
                                                <div class="row">
                                                    <div class="col m3 s6 ">
                                                        <label for="date" style="font-size:1rem;">Tanggal Batas :</label>
                                                        <input type="date" id="date" name="date" value="{{ date('Y-m-d') }}">
                                                    </div>
                                                    <div class="col m4 s6 pt-2">
                                                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="filterByDate();">
                                                            <i class="material-icons hide-on-med-and-up">search</i>
                                                            <span class="hide-on-small-onl">Filter</span>
                                                            <i class="material-icons right">search</i>
                                                        </a>
                                                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="reset();">
                                                            <i class="material-icons hide-on-med-and-up">loop</i>
                                                            <span class="hide-on-small-onl">Reset</span>
                                                            <i class="material-icons right">loop</i>
                                                        </a>
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
                        <div class="card">
                            <div class="card-content">
                                <h4 class="card-title">
                                    Hasil
                                </h4>
                                <h6>Untuk melihat detail tagihan pada masing-masing nominal, silahkan klik/tap kotak nominal berwarna <span class="blue-text text-darken-2">biru</span>.</h6>
                                <div class="row">
                                    <div class="col s12 m12" style="overflow: auto">
                                        <div class="result">
                                            <table class="bordered" style="font-size:10px;">
                                                <thead id="head_detail">
                                                    <tr>
                                                        <th rowspan="2" class="center-align">No.</th>
                                                        <th rowspan="2" class="center-align">Supplier</th>
                                                        <th colspan="5" class="center-align">Nominal Jatuh Tempo (Dari Tgl. Posting dan Tgl. Tenggat)</th>
                                                        <th rowspan="2" class="center-align">Total</th>
                                                    </tr>
                                                    <tr>
                                                        <th class="center-align">Belum Jatuh Tempo</th>
                                                        <th class="center-align">1-30 Hari</th>
                                                        <th class="center-align">31-60 Hari</th>
                                                        <th class="center-align">61-90 Hari</th>
                                                        <th class="center-align">Diatas 90 Hari</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="detail-result">
                                                    <tr>
                                                        <td class="center-align" colspan="8">Silahkan pilih tanggal dan tekan tombol filter.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
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

<div id="modal1" class="modal modal-fixed-footer bottom-sheet">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <table class="bordered">
                    <thead>
                        <tr>
                            <th class="center-align">No.</th>
                            <th class="center-align">No Invoice</th>
                            <th class="center-align">Supplier/Vendor</th>
                            <th class="center-align">TGL Post</th>
                            <th class="center-align">TGL Terima</th>
                            <th class="center-align">TGL Jatuh Tempo</th>
                            <th class="center-align">Jatuh Tempo (Hari)</th>
                            <th class="center-align">Grandtotal</th>
                            <th class="center-align">Memo</th>
                            <th class="center-align">Dibayar</th>
                            <th class="center-align">Sisa</th>
                        </tr>
                    </thead>
                    <tbody id="show_detail"></tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<script>
    $(function(){
        $('#modal1').modal({
            opacity: .25,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) {
                
            },
            onCloseEnd: function(modal, trigger){
                $('#show_detail').empty();
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
    function filterByDate(){
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
                $('#validation_alert').html('');
                loadingOpen('#main-display');
            },
            success: function(response) {
                loadingClose('#main-display');
                if(response.status == 200) {
                    $('#detail-result').html('');
                    if(response.content.length > 0){
                        let balance0 = 0, balance30 = 0, balance60 = 0, balance90 = 0, balanceOver = 0, grandtotal = 0;
                        $.each(response.content, function(i, val) {
                            $('#detail-result').append(`
                                <tr class="row_detail">
                                    <td class="center-align">` + (i+1) + `</td>
                                    <td>` + val.customer_name + `</td>
                                    <td class="right-align ` + (val.balance0 > 0 ? 'gradient-45deg-yellow-teal blue-text text-darken-2' : '') + `" onclick="detailShow(this)" data-invoice="` + val.arrInvoiceBalance0.filter(Boolean).join() + `">` + formatRupiahIni(val.balance0.toFixed(2).toString().replace('.',',')) + `</td>
                                    <td class="right-align ` + (val.balance30 > 0 ? 'gradient-45deg-yellow-teal blue-text text-darken-2' : '') + `" onclick="detailShow(this)" data-invoice="` + val.arrInvoiceBalance30.filter(Boolean).join() + `">` + formatRupiahIni(val.balance30.toFixed(2).toString().replace('.',',')) + `</td>
                                    <td class="right-align ` + (val.balance60 > 0 ? 'gradient-45deg-yellow-teal blue-text text-darken-2' : '') + `" onclick="detailShow(this)" data-invoice="` + val.arrInvoiceBalance60.filter(Boolean).join() + `">` + formatRupiahIni(val.balance60.toFixed(2).toString().replace('.',',')) + `</td>
                                    <td class="right-align ` + (val.balance90 > 0 ? 'gradient-45deg-yellow-teal blue-text text-darken-2' : '') + `" onclick="detailShow(this)" data-invoice="` + val.arrInvoiceBalance90.filter(Boolean).join() + `">` + formatRupiahIni(val.balance90.toFixed(2).toString().replace('.',',')) + `</td>
                                    <td class="right-align ` + (val.balanceOver > 0 ? 'gradient-45deg-yellow-teal blue-text text-darken-2' : '') + `" onclick="detailShow(this)" data-invoice="` + val.arrInvoiceBalanceOver.filter(Boolean).join() + `">` + formatRupiahIni(val.balanceOver.toFixed(2).toString().replace('.',',')) + `</td>
                                    <td class="right-align">` + formatRupiahIni(val.total.toFixed(2).toString().replace('.',',')) + `</td>
                                </tr>
                            `);
                            balance0 += val.balance0;
                            balance30 += val.balance30;
                            balance60 += val.balance60;
                            balance90 += val.balance90;
                            balanceOver += val.balanceOver;
                            grandtotal += val.total;
                        });
                        $('#detail-result').append(`
                            <tr id="text-grandtotal">
                                <td class="right-align" colspan="2">Total</td>
                                <td class="right-align">` + formatRupiahIni(balance0.toFixed(2).toString().replace('.',',')) + `</td>
                                <td class="right-align">` + formatRupiahIni(balance30.toFixed(2).toString().replace('.',',')) + `</td>
                                <td class="right-align">` + formatRupiahIni(balance60.toFixed(2).toString().replace('.',',')) + `</td>
                                <td class="right-align">` + formatRupiahIni(balance90.toFixed(2).toString().replace('.',',')) + `</td>
                                <td class="right-align">` + formatRupiahIni(balanceOver.toFixed(2).toString().replace('.',',')) + `</td>
                                <td class="right-align">` + formatRupiahIni(grandtotal.toFixed(2).toString().replace('.',',')) + `</td>
                            </tr>
                        `);
                        $('#detail-result').append(`
                            <tr id="text-grandtotal">
                                <td class="center-align" colspan="8">Waktu proses : ` + response.execution_time  + ` detik</td>
                            </tr>
                        `);
                    }else{
                        $('#detail-result').append(`
                            <tr>
                                <td class="center-align" colspan="8">Data tidak ditemukan.</td>
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

    function detailShow(element){
        if($(element).data('invoice')){
            let invoice = $(element).data('invoice'), date = $('#date').val();
            $.ajax({
                url: '{{ Request::url() }}/show_detail',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    invoice: invoice,
                    date: date,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    loadingOpen('.modal-content');
                },
                success: function(response) {
                    loadingClose('.modal-content');
                    $('#show_detail').empty();
                    if(response.status == 200) {
                        if(response.result.length > 0){
                            $.each(response.result, function(i, val) {
                                $('#show_detail').append(`
                                    <tr>
                                        <td class="center-align">` + (i+1) + `</td>
                                        <td>` + val.code + `</td>
                                        <td>` + val.vendor + `</td>
                                        <td class="center-align">` + val.post_date + `</td>
                                        <td class="center-align">` + val.rec_date + `</td>
                                        <td class="center-align">` + val.due_date + `</td>
                                        <td class="center-align">` + val.due_days + `</td>
                                        <td class="right-align">` + val.grandtotal + `</td>
                                        <td class="right-align">` + val.memo + `</td>
                                        <td class="right-align">` + val.paid + `</td>
                                        <td class="right-align">` + val.balance + `</td>
                                    </tr>
                                `);
                            });
                            $('#show_detail').append(`
                                <tr id="text-grandtotal">
                                    <td class="right-align" colspan="10">Total</td>
                                    <td class="right-align">` + response.grandtotal + `</td>
                                </tr>
                            `);
                            $('#modal1').modal('open');
                        }                  
                    }else{
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
            $('#modal1').modal('open');
        }
    }

    function reset(){
        $('#form_data_filter')[0].reset();
        $('#detail-result').html('').append(`
            <tr>
                <td class="center-align" colspan="8">Silahkan pilih tanggal dan tekan tombol filter.</td>
            </tr>
        `);
    }
</script>