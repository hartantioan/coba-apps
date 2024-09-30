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
                                                <div class="col m4 s6 ">
                                                    <label for="filter_status" style="font-size:1rem;">Status :</label>
                                                    <div class="input-field">
                                                        <select class="form-control" id="filter_status" onchange="loadDataTable()" multiple>
                                                            <option value="1">Menunggu</option>
                                                            <option value="2">Dalam Proses</option>
                                                            <option value="3">Selesai</option>
                                                            <option value="4">Ditolak</option>
                                                            <option value="5">Ditutup</option>
                                                            <option value="6">Direvisi</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col m4 s6 ">
                                                    <label for="filter_type" style="font-size:1rem;">Tipe :</label>
                                                    <div class="input-field">
                                                        <select class="form-control" id="filter_type" onchange="loadDataTable()">
                                                            <option value="">{{ __('translations.all') }}</option>
                                                            <option value="1">Cash</option>
                                                            <option value="2">Credit</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col m4 s6 ">
                                                    <label for="filter_company" style="font-size:1rem;">Perusahaan :</label>
                                                    <div class="input-field">
                                                        <select class="form-control" id="filter_company" onchange="loadDataTable()">
                                                            <option value="">{{ __('translations.all') }}</option>
                                                            @foreach ($company as $rowcompany)
                                                                <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col m4 s6 ">
                                                    <label for="filter_account" style="font-size:1rem;">Customer :</label>
                                                    <div class="input-field">
                                                        <select class="browser-default" id="filter_account" name="filter_account" multiple="multiple" style="width:100% !important;" onchange="loadDataTable()"></select>
                                                    </div>
                                                </div>
                                                    <div class="col m3 s6 ">
                                                        <label for="start_date" style="font-size:1rem;">Tanggal Mulai Posting :</label>
                                                        <input type="date" max="{{ date('9999'.'-12-31') }}" id="start_date" name="start_date" value="{{ date('Y-m-d') }}">
                                                    </div>
                                                    <div class="col m3 s6 ">
                                                        <label for="finish_date" style="font-size:1rem;">Tanggal Akhir Posting :</label>
                                                        <input type="date" max="{{ date('9999'.'-12-31') }}" id="finish_date" name="finish_date" value="{{ date('Y-m-d') }}">
                                                    </div>
                                                    <div class="col m6 s6 pt-2">
                                                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn mr-3" href="javascript:void(0);" onclick="filter();">
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
                                <div class="row">
                                    <div class="col s12 m12" style="overflow: auto">
                                        <div class="result" style="width:2500px;">
                                            <table class="bordered" style="font-size:10px;">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Kode</th>
                                                        <th>Pengguna</th>
                                                        <th>Voider</th>
                                                        <th>Tgl Void</th>
                                                        <th>Ket Void</th>
                                                        <th>Deleter</th>
                                                        <th>Tgl Delete</th>
                                                        <th>Ket Delete</th>
                                                        <th>Doner</th>
                                                        <th>Tgl Done</th>
                                                        <th>Ket Done</th>
                                                        <th>Tgl Posting</th>
                                                        <th>Pelanggan</th>
                                                        <th>Perusahaan</th>
                                                        <th>Alamat Penagihan & NPWP</th>
                                                        <th>Jatuh Tempo</th>
                                                        <th>Jatuh Tempo Internal</th>
                                                        <th>Jenis</th>
                                                        <th>Seri Pajak</th>
                                                        <th>Catatan</th>
                                                        <th>Subtotal</th>
                                                        <th>Downpayment</th>
                                                        <th>Total</th>
                                                        <th>PPN</th>
                                                        <th>Grandtotal</th>
                                                        <th>Status</th>

                                                    </tr>
                                                </thead>
                                                <tbody id="detail_invoice">
                                                    <tr>
                                                        <td class="center-align" colspan="20">Silahkan pilih tanggal dan tekan tombol filter.</td>
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
        var search = table.search();
        var status = $('#filter_status').val();
        var account_id = $('#filter_account').val();
        var company = $('#filter_company').val();
        var marketing_order = $('#filter_marketing_order').val();
        var start_date = $('#start_date').val();
        var end_date = $('#finish_date').val();

        window.location = "{{ Request::url() }}/export?search=" + search + "&status=" + status + "&account_id=" + account_id + "&company=" + company + "&marketing_order=" + marketing_order + "&end_date=" + end_date + "&start_date=" + start_date ;
       
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
                    $('#detail_invoice').empty();
                    console.log(response);
                    if(response.content.length > 0){
                        $.each(response.content, function(i, val) {
                            $('#detail_invoice').append(`
                                <tr>
                                    <td>`+val.no+`</td>
                                    <td>`+val.kode+`</td>
                                    <td>`+val.pengguna+`</td>
                                    <td>`+val.voider+`</td>
                                    <td>`+val.tgl_void+`</td>
                                    <td>`+val.ket_void+`</td>
                                    <td>`+val.deleter+`</td>
                                    <td>`+val.tgl_delete+`</td>
                                    <td>`+val.ket_delete+`</td>
                                    <td>`+val.doner+`</td>
                                    <td>`+val.tgl_done+`</td>
                                    <td>`+val.ket_done+`</td>
                                    <td>`+val.tgl_posting+`</td>
                                    <td>`+val.pelanggan+`</td>
                                    <td>`+val.perusahaan+`</td>
                                    <td>`+val.alamat_penagihan+`</td>
                                    <td>`+val.jatuh_tempo+`</td>
                                    <td>`+val.jatuh_tempo_internal+`</td>
                                    <td>`+val.jenis+`</td>
                                    <td>`+val.seri_pajak+`</td>
                                    <td>`+val.catatan+`</td>
                                    <td>`+val.subtotal+`</td>
                                    <td>`+val.downpayment+`</td>
                                    <td>`+val.total+`</td>
                                    <td>`+val.ppn+`</td>
                                    <td>`+val.grandtotal+`</td>
                                    <td>`+val.status+`</td>
                                </tr>
                            `);
                        });
                        $('#detail_invoice').append(`
                            <tr>
                                <td class="" colspan="20">Waktu Proses : <b>` + response.execution_time + ` Detik</b></td>
                            </tr>
                        `);
                    }else{
                        $('#detail_invoice').append(`
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
        $('#detail_invoice').html('').append(`
            <tr>
                <td class="center-align" colspan="20">Silahkan pilih tanggal dan tekan tombol filter.</td>
            </tr>
        `);
    }
</script>