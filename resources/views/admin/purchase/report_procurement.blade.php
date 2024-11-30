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
        select2ServerSide('#item_id', '{{ url("admin/select2/item_rm_sm") }}');
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


    function reset(){
        $('#form_data_filter')[0].reset();

    }
</script>
