<style>
    .modal {
        top:0px !important;
    }

    table > thead > tr > th {
        font-size: 13px !important;
    }

    body.tab-active input:focus {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    .modal-content .select2.tab-active {
        outline: 2px solid green !important; /* Adjust the color and style as needed */
        border-radius: 5px !important;
    }

    table.bordered th {
        padding: 5px !important;
    }

    .browser-default {
        height: 2rem !important;
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
                            <li class="breadcrumb-item active">{{ Str::title(str_replace('_',' ',Request::segment(3))) }}
                            </li>
                        </ol>
                    </div>
                    <div class="col s4 m6 l6">
                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="printData();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">Print</span>
                            <i class="material-icons right">local_printshop</i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12">
            <div class="container">
                <div class="section section-data-tables">
                    <!-- DataTables example -->
                    <div class="row">
                        <div class="col s12">
                            <ul class="collapsible collapsible-accordion">
                                <li>
                                    <div class="collapsible-header"><i class="material-icons">filter_list</i> FILTER</div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col m4 s6 ">
                                                <label for="filter_status" style="font-size:1rem;">Status :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_status" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
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
                                                <label for="filter_company" style="font-size:1rem;">Plant :</label>
                                                <div class="input-field">
                                                    <select class="form-control" id="filter_company" onchange="loadDataTable()">
                                                        <option value="">Semua</option>
                                                        @foreach ($company as $rowcompany)
                                                            <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="start_date" style="font-size:1rem;">Tanggal Mulai :</label>
                                                <div class="input-field col s12">
                                                <input type="date" max="{{ date('9999'.'-12-31') }}" id="start_date" name="start_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                            <div class="col m4 s6 ">
                                                <label for="finish_date" style="font-size:1rem;">Tanggal Akhir :</label>
                                                <div class="input-field col s12">
                                                    <input type="date" max="{{ date('9999'.'-12-31') }}" id="finish_date" name="finish_date"  onchange="loadDataTable()">
                                                </div>
                                            </div>
                                        </div>  
                                    </div>
                                </li>
                            </ul>
                            <div class="card">
                                <div class="card-content">
                                    <h4 class="card-title">
                                        List Data
                                    </h4>
                                    <div class="row">
                                        <div class="col s12">
                                            <div id="datatable_buttons"></div>
                                            <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right" href="javascript:void(0);" onclick="loadDataTable();">
                                                <i class="material-icons hide-on-med-and-up">refresh</i>
                                                <span class="hide-on-small-onl">Refresh</span>
                                                <i class="material-icons right">refresh</i>
                                            </a>
                                            <table id="datatable_serverside">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Code</th>
                                                        <th>Pengguna</th>
                                                        <th>Perusahaan</th>
                                                        <th>Tgl.Post</th>
                                                        <th>Keterangan</th>
                                                        <th>Mata Uang</th>
                                                        <th>Konversi</th>
                                                        <th>Total</th>
                                                        <th>PPN</th>
                                                        <th>PPh</th>
                                                        <th>Grandtotal</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="content-overlay"></div>
        </div>
    </div>
</div>

<div id="modal1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content" style="overflow-x: hidden;max-width: 100%;">
        <div class="row">
            <div class="col s12">
                <h4>Tambah/Edit {{ $title }}</h4>
                <form class="row" id="form_data" onsubmit="return false;">
                    <div class="col s12">
                        <div id="validation_alert" style="display:none;"></div>
                    </div>
                    <div class="col s12">
                        <div class="row">
                            <div class="input-field col m2 s12">
                                <input id="code" name="code" type="text" value="{{ $newcode }}" readonly>
                                <label class="active" for="code">No. Dokumen</label>
                            </div>
                            <div class="input-field col m1 s12">
                                <select class="form-control" id="code_place_id" name="code_place_id" onchange="getCode(this.value);">
                                    <option value="">--Pilih--</option>
                                    @foreach ($place as $rowplace)
                                        <option value="{{ $rowplace->code }}">{{ $rowplace->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="input-field col m3 s12 step1">
                                <input type="hidden" id="temp" name="temp">
                                <select class="form-control" id="company_id" name="company_id">
                                    @foreach ($company as $rowcompany)
                                        <option value="{{ $rowcompany->id }}">{{ $rowcompany->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="company_id">Perusahaan</label>
                            </div>
                            <div class="input-field col m3 s12 step2">
                                <input id="post_date" name="post_date" min="{{ $minDate }}" max="{{ $maxDate }}" type="date" placeholder="Tgl. posting" value="{{ date('Y-m-d') }}" onchange="loadCurrency();">
                                <label class="active" for="post_date">Tgl. Posting</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <select class="form-control" id="currency_id" name="currency_id" onchange="loadCurrency();">
                                    @foreach ($currency as $row)
                                        <option value="{{ $row->id }}" data-code="{{ $row->code }}">{{ $row->code.' '.$row->name }}</option>
                                    @endforeach
                                </select>
                                <label class="" for="currency_id">Mata Uang</label>
                            </div>
                            <div class="input-field col m3 s12">
                                <input id="currency_rate" name="currency_rate" type="text" value="1" onkeyup="formatRupiah(this)">
                                <label class="active" for="currency_rate">Konversi</label>
                            </div>
                            <div class="input-field col m3 s12 step3">
                                <textarea class="materialize-textarea" id="note" name="note" placeholder="Catatan / Keterangan" rows="3"></textarea>
                                <label class="active" for="note">Keterangan</label>
                            </div>
                            <div class="col m12 s12 step4">
                                <div class="col m4 s4">
                                    <p class="mt-2 mb-2">
                                        <h6>Outgoing Payment / Kas Keluar</h6>
                                        <div class="row">
                                            <div class="col m12 s12 mt-4">
                                                <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="getData();" href="javascript:void(0);">
                                                    <i class="material-icons left">add</i> Ambil Data
                                                </a>
                                            </div>
                                        </div>
                                    </p>
                                </div>
                                <div class="col m8 s8">
                                    <b>FR Terpakai</b> (hapus untuk bisa diakses pengguna lain) : <i id="list-used-data"></i>
                                </div>
                            </div>
                            <div class="col m12 s12 step5">
                                <p class="mt-2 mb-2">
                                    <h6>Detail Outgoing Payment / Kas Keluar (Tipe BS)</h6>
                                    <div style="overflow:auto;">
                                        <table class="bordered" style="min-width:1800px !important;">
                                            <thead>
                                                <tr>
                                                    <th class="center">Referensi</th>
                                                    <th class="center">Partner Bisnis</th>
                                                    <th class="center">Tgl.Bayar</th>
                                                    <th class="center">Total</th>
                                                    <th class="center">Dipakai</th>
                                                    <th class="center">Sisa</th>
                                                    <th class="center">Keterangan</th>
                                                    <th class="center">Hapus</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-detail-op">
                                                <tr id="empty-detail">
                                                    <td colspan="8">
                                                        Pilih Outgoing Payment untuk memulai...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </p>
                            </div>
                            <div class="col m12 s12 step5">
                                <p class="mt-2 mb-2">
                                    <h6>Detail Pembiayaan</h6>
                                    <div style="overflow:auto;">
                                        <table class="bordered" style="min-width:2800px !important;">
                                            <thead>
                                                <tr>
                                                    <th class="center">Coa</th>
                                                    <th class="center">Dist.Biaya</th>
                                                    <th class="center">Plant</th>
                                                    <th class="center">Line</th>
                                                    <th class="center">Mesin</th>
                                                    <th class="center">Divisi</th>
                                                    <th class="center">Proyek</th>
                                                    <th class="center">Ket.1</th>
                                                    <th class="center">Ket.2</th>
                                                    <th class="center">Debit FC</th>
                                                    <th class="center">Kredit FC</th>
                                                    <th class="center">Debit Rp</th>
                                                    <th class="center">Kredit Rp</th>
                                                    <th class="center">Hapus</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body-detail">
                                                <tr id="last-row-detail">
                                                    <td colspan="14">
                                                        <a class="waves-effect waves-light cyan btn-small mb-1 mr-1" onclick="addItem()" href="javascript:void(0);">
                                                            <i class="material-icons left">add</i> Tambah Detail
                                                        </a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </p>
                            </div>
                            <div class="input-field col m4 s12">
                            </div>
                            <div class="input-field col m4 s12">
                            </div>
                            <div class="input-field col m4 s12 step6">
                                <table width="100%" class="bordered">
                                    <thead>
                                        <tr>
                                            <td width="40%">Total OP</td>
                                            <td class="right-align gradient-45deg-red-pink"><span id="total_op">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>Total Terpakai</td>
                                            <td class="right-align gradient-45deg-teal-cyan"><span id="grandtotal">0,00</span></td>
                                        </tr>
                                        <tr>
                                            <td>Selisih</td>
                                            <td class="right-align gradient-45deg-purple-pink"><span id="balance">0,00</span></td>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="col s12 mt-3 step7">
                                <button class="btn waves-effect waves-light right submit" onclick="save();">Simpan <i class="material-icons right">send</i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn waves-effect waves-light purple " onclick="startIntro();">Panduan <i class="material-icons right">help_outline</i></button>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal2" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="show_print">
                
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal4_1" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="show_detail">

            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal4" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row">
            <div class="col s12" id="show_structure">
                <div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;">

                </div>
                <div id="visualisation">
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div id="modal5" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-header ml-2">
        <h5>Daftar Tunggakan Dokumen</h5>
    </div>
    <div class="modal-content" style="overflow-x: hidden;max-width: 100%;">
        <div class="row">
            <div class="col s12">
                <div class="row">
                    <div class="col s12 mt-2 mb-5">
                        <div id="datatable_buttons_multi"></div>
                        <i class="right">Gunakan *pilih semua* untuk memilih seluruh data yang anda inginkan. Atau pilih baris untuk memilih data yang ingin dipindahkan.</i>
                        <table id="table_multi" class="display" width="100%">
                            <thead>
                                <tr>
                                    <th class="center-align">Kode Dokumen</th>
                                    <th class="center-align">Partner Bisnis</th>
                                    <th class="center-align">Tgl.Post</th>
                                    <th class="center-align">Total</th>
                                    <th class="center-align">PPN</th>
                                    <th class="center-align">PPh</th>
                                    <th class="center-align">Grandtotal</th>
                                    <th class="center-align">Terpakai</th>
                                    <th class="center-align">Sisa</th>
                                    <th class="center-align">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="body-detail-multi"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat mr-1">Close</a>
        <button class="btn waves-effect waves-light purple right submit" onclick="applyDocuments();">Gunakan <i class="material-icons right">forward</i></button>
    </div>
</div>

<div id="modal6" class="modal modal-fixed-footer" style="min-width:90%;max-height: 100% !important;height: 100% !important;width:100%;">
    <div class="modal-content">
        <div class="row" >
            <div class="col m3 s12">
                
            </div>
            <div class="col m6 s12">
                <h4 id="title_data" style="text-align:center"></h4>
                <h5 id="code_data" style="text-align:center"></h5>
            </div>
            <div class="col m3 s12 right-align">
                <img src="{{ url('website/logo_web_fix.png') }}" width="40%" height="60%">
            </div>
        </div>
        <div class="divider mb-1 mt-2"></div>
        <div class="row">
            <div class="col" id="user_jurnal">
            </div>
            <div class="col" id="post_date_jurnal">
            </div>
            <div class="col" id="note_jurnal">
            </div>
            <div class="col" id="ref_jurnal">
            </div>
            <div class="col" id="company_jurnal">
            </div>
        </div>
        <div class="row mt-2">
            <table class="bordered Highlight striped" style="zoom:0.7;">
                <thead>
                        <tr>
                            <th class="center-align" rowspan="2">No</th>
                            <th class="center-align" rowspan="2">Coa</th>
                            <th class="center-align" rowspan="2">Partner Bisnis</th>
                            <th class="center-align" rowspan="2">Plant</th>
                            <th class="center-align" rowspan="2">Line</th>
                            <th class="center-align" rowspan="2">Mesin</th>
                            <th class="center-align" rowspan="2">Divisi</th>
                            <th class="center-align" rowspan="2">Gudang</th>
                            <th class="center-align" rowspan="2">Proyek</th>
                            <th class="center-align" rowspan="2">Ket.1</th>
                            <th class="center-align" rowspan="2">Ket.2</th>
                            <th class="center-align" colspan="2">Mata Uang Asli</th>
                            <th class="center-align" colspan="2">Mata Uang Konversi</th>
                        </tr>
                        <tr>
                            <th class="center-align">Debit</th>
                            <th class="center-align">Kredit</th>
                            <th class="center-align">Debit</th>
                            <th class="center-align">Kredit</th>
                        </tr>
                    
                </thead>
                <tbody id="body-journal-table">
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
    <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger" href="#modal1">
        <i class="material-icons">add</i>
    </a>
</div>

<!-- END: Page Main-->
<script>
    document.addEventListener('focusin', function (event) {
        const select2Container = event.target.closest('.modal-content .select2');
        const activeSelect2 = document.querySelector('.modal-content .select2.tab-active');
        if (event.target.closest('.modal-content')) {
            document.body.classList.add('tab-active');
        }
        
        if (activeSelect2 && !select2Container) {
            activeSelect2.classList.remove('tab-active');
        }
        
        if (select2Container) {
            select2Container.classList.add('tab-active');
        }
    });

    document.addEventListener('mousedown', function () {
        const activeSelect2 = document.querySelector('.modal-content .select2.tab-active');
        document.body.classList.remove('tab-active');
        if (activeSelect2) {
            activeSelect2.classList.remove('tab-active');
        }
    });
    $(function() {
        $(".select2").select2({
            dropdownAutoWidth: true,
            width: '100%',
        });

        loadDataTable();

        window.table.search('{{ $code }}').draw();

        $('#modal1').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                $('#post_date').attr('min','{{ $minDate }}');
                $('#post_date').attr('max','{{ $maxDate }}');
                $('#due_date').attr('min','{{ date("Y-m-d") }}');
                $('#document_date').attr('min','{{ date("Y-m-d") }}');
            },
            onOpenEnd: function(modal, trigger) {
                $('#name').focus();
                $('#validation_alert').hide();
                $('#validation_alert').html('');
                M.updateTextFields();
                window.onbeforeunload = function() {
                    if($('.data-used').length > 0){
                        $('.data-used').trigger('click');
                    }
                    return 'You will lose all changes made since your last save';
                };
                if(!$('#temp').val()){
                    loadCurrency();
                }
            },
            onCloseStart: function(modal, trigger){
                if($('.data-used').length > 0){
                    $('.data-used').trigger('click');
                }
            },
            onCloseEnd: function(modal, trigger){
                $('#form_data')[0].reset();
                $('#temp').val('');
                $('input').css('border', 'none');
                $('input').css('border-bottom', '0.5px solid black');
                M.updateTextFields();
                $('#body-detail-op').empty().append(`
                    <tr id="empty-detail">
                        <td colspan="8">
                            Pilih Outgoing Payment untuk memulai...
                        </td>
                    </tr>
                `);
                $('.row_detail').remove();
                window.onbeforeunload = function() {
                    return null;
                };
                $('#total').text('0,00');
                $('#tax').text('0,00');
                $('#wtax').text('0,00');
                $('#grandtotal').text('0,00');
                $('#total_op').text('0,00');
                $('#balance').text('0,00');
            }
        });

        $('#modal2').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) {
                window.print();
            },
            onCloseEnd: function(modal, trigger){
                $('#show_print').html('');
            }
        });

        $('#modal4_1').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#show_detail').empty();
            }
        });

        $('#modal6').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#title_data').empty();
                $('#code_data').empty();             
                $('#body-journal-table').empty();
                $('#user_jurnal').empty();
                $('#note_jurnal').empty();
                $('#ref_jurnal').empty();
                $('#company_jurnal').empty();
                $('#post_date_jurnal').empty();
            }
        });

        $('#modal5').modal({
            dismissible: false,
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) {
                table_multi = $('#table_multi').DataTable({
                    "ordering": false,
                    scrollY: '50vh',
                    scrollCollapse: true,
                    "iDisplayInLength": 10,
                    dom: 'Blfrtip',
                    buttons: [
                        'selectAll',
                        'selectNone'
                    ],
                    "language": {
                        "lengthMenu": "Menampilkan _MENU_ data per halaman",
                        "zeroRecords": "Data tidak ditemukan / kosong",
                        "info": "Menampilkan halaman _PAGE_ / _PAGES_ dari total _TOTAL_ data",
                        "infoEmpty": "Data tidak ditemukan / kosong",
                        "infoFiltered": "(disaring dari _MAX_ total data)",
                        "search": "Cari",
                        "paginate": {
                            first:      "<<",
                            previous:   "<",
                            next:       ">",
                            last:       ">>"
                        },
                        "buttons": {
                            selectAll: "Pilih semua",
                            selectNone: "Hapus pilihan"
                        },
                        "select": {
                            rows: "%d baris terpilih"
                        }
                    },
                    select: {
                        style: 'multi'
                    }
                });
                $('#table_multi_wrapper > .dt-buttons').appendTo('#datatable_buttons_multi');
                $('select[name="table_multi_length"]').addClass('browser-default');
            },
            onCloseEnd: function(modal, trigger){
                $('#body-detail-multi').empty();
                $('#table_multi').DataTable().clear().destroy();
            }
        });

        $('#modal4').modal({
            onOpenStart: function(modal,trigger) {
                
            },
            onOpenEnd: function(modal, trigger) { 
            },
            onCloseEnd: function(modal, trigger){
                $('#myDiagramDiv').remove();
                $('#show_structure').append(
                    `<div id="myDiagramDiv" style="border: 1px solid black; width: 100%; height: 600px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;"></div>
                    `
                );
            }
        });

        $('#body-detail-op').on('click', '.delete-data-detail', function() {
            $(this).closest('tr').remove();
            countAll();
        });

        $('#body-detail').on('click', '.delete-data-detail', function() {
            $(this).closest('tr').remove();
            countAll();
        });

        select2ServerSide('#fund_request_id', '{{ url("admin/select2/fund_request_bs_close") }}');
    });

    function voidStatus(id){
        var msg = '';
        swal({
            title: "Alasan mengapa anda menutup!",
            text: "Anda tidak bisa mengembalikan data yang telah ditutup.",
            buttons: true,
            content: "input",
        })
        .then(message => {
            if (message != "" && message != null) {
                $.ajax({
                    url: '{{ Request::url() }}/void_status',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { id : id, msg : message },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        loadingOpen('#main');
                    },
                    success: function(response) {
                        loadingClose('#main');
                        M.toast({
                            html: response.message
                        });
                        loadDataTable();
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
        });
    }

    function changePlace(element){
        $(element).parent().next().find('select[name="arr_machine[]"] option').show();
        if($(element).val()){
            $(element).parent().prev().find('select[name="arr_place[]"]').val($(element).find(':selected').data('place'));
            $(element).parent().next().find('select[name="arr_machine[]"] option[data-line!="' + $(element).val() + '"]').hide();
        }else{
            $(element).parent().prev().find('select[name="arr_place[]"]').val($(element).parent().prev().find('select[name="arr_place[]"] option:first').val());
        }
    }

    function changeLine(element){
        if($(element).val()){
            $(element).parent().prev().find('select[name="arr_line[]"]').val($(element).find(':selected').data('line')).trigger('change');
        }else{
            $(element).parent().prev().find('select[name="arr_line[]"]').val($(element).parent().prev().find('select[name="arr_line[]"] option:first').val()).trigger('change');
        }
    }

    function destroy(id){
        var msg = '';
        swal({
            title: "Alasan mengapa anda menghapus!",
            text: "Anda tidak bisa mengembalikan data yang telah dihapus.",
            buttons: true,
            content: "input",
        })
        .then(message => {
            if (message != "" && message != null) {
                $.ajax({
                    url: '{{ Request::url() }}/destroy',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { id : id, msg : message },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        loadingOpen('#main');
                    },
                    success: function(response) {
                        loadingClose('#main');
                        M.toast({
                            html: response.message
                        });
                        loadDataTable();
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
        });
    }

    function getCode(val){
        if(val){
            if($('#temp').val()){
                let newcode = $('#code').val().replaceAt(7,val);
                $('#code').val(newcode);
            }else{
                if($('#code').val().length > 7){
                    $('#code').val($('#code').val().slice(0, 7));
                }
                $.ajax({
                    url: '{{ Request::url() }}/get_code',
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        val: $('#code').val() + val,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        loadingOpen('.modal-content');
                    },
                    success: function(response) {
                        loadingClose('.modal-content');
                        $('#code').val(response);
                    },
                    error: function() {
                        swal({
                            title: 'Ups!',
                            text: 'Check your internet connection.',
                            icon: 'error'
                        });
                    }
                });
            }
        }
    }

    function loadDataTable() {
		window.table = $('#datatable_serverside').DataTable({
            "scrollCollapse": true,
            "scrollY": '400px',
            "responsive": false,
            "scrollX": true,
            "stateSave": true,
            "serverSide": true,
            "deferRender": true,
            "destroy": true,
            "iDisplayInLength": 10,
            "fixedColumns": {
                left: 2,
                right: 1
            },
            "order": [[0, 'desc']],
            ajax: {
                url: '{{ Request::url() }}/datatable',
                type: 'GET',
                data: {
                    status : $('#filter_status').val(),
                    'account_id[]' : $('#filter_account').val(),
                    company_id : $('#filter_company').val(),
                    'currency_id[]' : $('#filter_currency').val(),
                    start_date : $('#start_date').val(),
                    finish_date : $('#finish_date').val(),
                },
                beforeSend: function() {
                    loadingOpen('#datatable_serverside');
                },
                complete: function() {
                    loadingClose('#datatable_serverside');
                },
                error: function() {
                    loadingClose('#datatable_serverside');
                    swal({
                        title: 'Ups!',
                        text: 'Check your internet connection.',
                        icon: 'error'
                    });
                }
            },
            columns: [
                { name: 'id', searchable: false, className: 'center-align details-control' },
                { name: 'code', className: 'center-align' },
                { name: 'user_id', className: 'center-align' },
                { name: 'company_id', className: 'center-align' },
                { name: 'post_date', className: 'center-align' },
                { name: 'note', className: '' },
                { name: 'currency_id', className: '' },
                { name: 'currency_rate', className: 'right-align' },
                { name: 'total', className: 'right-align' },
                { name: 'tax', className: 'right-align' },
                { name: 'wtax', className: 'right-align' },
                { name: 'grandtotal', className: 'right-align' },
                { name: 'status', searchable: false, orderable: false, className: 'center-align' },
                { name: 'action', searchable: false, orderable: false, className: 'center-align' },
            ],
            dom: 'Blfrtip',
            buttons: [
                'columnsToggle'
            ]
        });
        $('.dt-buttons').appendTo('#datatable_buttons');
        
        $('select[name="datatable_serverside_length"]').addClass('browser-default');
	}

    function rowDetail(data) {
        $.ajax({
            url: '{{ Request::url() }}/row_detail',
            type: 'GET',
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            data: {
                id: data
            },
            success: function(response) {
                $('#modal4_1').modal('open');
                $('#show_detail').html(response);
                loadingClose('.modal-content');
            },
            error: function() {
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
            }
        });
	}

    function getData(){
        $.ajax({
            url: '{{ Request::url() }}/get_data',
            type: 'POST',
            dataType: 'JSON',
            data: {
                id: $('#fund_request_id').val()
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            success: function(response) {
                loadingClose('.modal-content');
                if(response.status == 500){
                    swal({
                        title: 'Ups!',
                        text: response.message,
                        icon: 'warning'
                    });
                }else{
                    $('#modal5').modal('open');

                    if(response.data.length > 0){
                        $.each(response.data, function(i, val) {
                            $('#body-detail-multi').append(`
                                <tr data-type="` + val.type + `" data-id="` + val.id + `">
                                    <td>
                                        ` + val.code + `
                                    </td>
                                    <td>
                                        ` + val.name + `
                                    </td>
                                    <td>
                                        ` + val.post_date + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.total + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.tax + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.wtax + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.grandtotal + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.used + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.balance + `
                                    </td>
                                    <td class="">
                                        ` + val.note + `
                                    </td>
                                </tr>
                            `);
                        });
                    }
                    
                    $('.modal-content').scrollTop(0);
                    M.updateTextFields();
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

    function applyDocuments(){
        swal({
            title: "Apakah anda yakin?",
            text: "Jika sudah ada di dalam tabel detail Outgoing Payment / Kas Keluar, maka akan tergantikan dengan pilihan baru anda saat ini.",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                let arr_id = [], arr_type = [];
                $.map(table_multi.rows('.selected').nodes(), function (item) {
                    arr_id.push($(item).data('id'));
                    arr_type.push($(item).data('type'));
                });

                $.ajax({
                    url: '{{ Request::url() }}/get_account_data',
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        arr_id: arr_id,
                        arr_type: arr_type,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        loadingOpen('.modal-content');
                    },
                    success: function(response) {
                        loadingClose('.modal-content');
                        $('#empty-detail').remove();
                        $('.row_op').remove();
                        if(response.details.length > 0){
                            $.each(response.details, function(i, val) {
                                var count = makeid(10);
                                $('#list-used-data').append(`
                                    <div class="chip purple darken-4 gradient-shadow white-text">
                                        ` + val.code + `
                                        <i class="material-icons close data-used" onclick="removeUsedData('` + val.type + `',` + val.id + `)">close</i>
                                    </div>
                                `);
                                $('#body-detail-op').append(`
                                    <tr class="row_op" data-id="` + val.id + `">
                                        <input type="hidden" name="arr_type[]" value="` + val.type + `" id="arr_type` + count + `">
                                        <input type="hidden" name="arr_id[]" value="` + val.id + `" id="arr_id` + count + `">
                                        <td>
                                            ` + val.code + `
                                        </td>
                                        <td>
                                            ` + val.bp + `
                                        </td>
                                        <td class="center">
                                            ` + val.post_date + `
                                        </td>
                                        <td class="right-align">
                                            ` + val.total + `
                                        </td>
                                        <td class="right-align">
                                            ` + val.used + `
                                        </td>
                                        <td class="center">
                                            <input name="arr_nominal[]" onfocus="emptyThis(this);" class="browser-default" type="text" data-max="` + val.balance + `" value="` + val.balance + `" onkeyup="formatRupiah(this);checkRow('` + count + `');countAll();" style="text-align:right;width:100%;" id="arr_nominal` + count + `">
                                        </td>
                                        <td class="center">
                                            <input name="arr_note_source[]" class="browser-default" type="text" value="` + val.note + `" style="width:100%;" id="arr_note_source` + count + `">
                                        </td>
                                        <td class="center">
                                            <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                                                <i class="material-icons">delete</i>
                                            </a>
                                        </td>
                                    </tr>
                                `);
                            });
                            countAll();
                        }else{

                        }
                        
                        $('.modal-content').scrollTop(0);
                        M.updateTextFields();

                        $('#modal5').modal('close');
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
        });
    }

    function addItem(){
        var count = makeid(10);
        $('#last-row-detail').before(`
            <tr class="row_detail">
                <td class="">
                    <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]"></select>
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_cost_distribution_cost` + count + `" name="arr_cost_distribution_cost[]"></select> 
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_place` + count + `" name="arr_place[]">
                        @foreach ($place as $rowplace)
                            <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select class="browser-default" id="arr_line` + count + `" name="arr_line[]" onchange="changePlace(this);">
                        <option value="">--Kosong--</option>
                        @foreach ($line as $rowline)
                            <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                        @endforeach
                    </select>    
                </td>
                <td>
                    <select class="browser-default" id="arr_machine` + count + `" name="arr_machine[]" onchange="changeLine(this);">
                        <option value="">--Kosong--</option>
                        @foreach ($machine as $rowmachine)
                            <option value="{{ $rowmachine->id }}" data-line="{{ $rowmachine->line_id }}">{{ $rowmachine->name }}</option>
                        @endforeach
                    </select>    
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_division` + count + `" name="arr_division[]">
                        <option value="">--Kosong--</option>
                        @foreach ($division as $row)
                            <option value="{{ $row->id }}">{{ $row->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="center">
                    <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                </td>
                <td>
                    <input type="text" name="arr_note[]" placeholder="Keterangan 1..." data-id="` + count + `">
                </td>
                <td>
                    <input type="text" name="arr_note2[]" placeholder="Keterangan 2..." data-id="` + count + `">
                </td>
                <td class="center">
                    <input class="browser-default" type="text" name="arr_nominal_debit_fc[]" value="0,00" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();">
                </td>
                <td class="right-align">
                    <input class="browser-default" type="text" name="arr_nominal_credit_fc[]" value="0,00" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();">
                </td>
                <td class="right-align">
                    <input class="browser-default" type="text" name="arr_nominal_debit[]" value="0,00" data-id="` + count + `" onkeyup="formatRupiah(this);" readonly>
                </td>
                <td class="right-align">
                    <input class="browser-default" type="text" name="arr_nominal_credit[]" value="0,00" data-id="` + count + `" onkeyup="formatRupiah(this);" readonly>
                </td>
                <td class="center">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
        select2ServerSide('#arr_project' + count, '{{ url("admin/select2/project") }}');
        select2ServerSide('#arr_cost_distribution_cost' + count, '{{ url("admin/select2/cost_distribution") }}');
    }

    function checkRow(code){
        var nil = parseFloat($('#arr_nominal' + code).val().replaceAll(".", "").replaceAll(",",".")), max = parseFloat($('#arr_nominal' + code).data('max').replaceAll(".", "").replaceAll(",","."));
        if(nil > max){
            $('#arr_nominal' + code).val($('#arr_nominal' + code).data('max'));
        }
    }

    function countAll(){
        var total_op = 0, grandtotal = 0, balance = 0, currency_rate = parseFloat($('#currency_rate').val().replaceAll(".", "").replaceAll(",","."));

        $('input[name^="arr_nominal[]"]').each(function(index){
            total_op += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
        });

        balance = total_op;

        $('input[name^="arr_nominal_debit_fc[]"]').each(function(index){
            let rowtotal = parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
            let conversion = rowtotal * currency_rate;
            $('input[name^="arr_nominal_debit[]"]').eq(index).val(
                (conversion >= 0 ? '' : '-') + formatRupiahIni(conversion.toFixed(2).toString().replace('.',','))
            );
            grandtotal += rowtotal;
        });

        $('input[name^="arr_nominal_credit_fc[]"]').each(function(index){
            let rowtotal = parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
            let conversion = rowtotal * currency_rate;
            $('input[name^="arr_nominal_credit[]"]').eq(index).val(
                (conversion >= 0 ? '' : '-') + formatRupiahIni(conversion.toFixed(2).toString().replace('.',','))
            );
            grandtotal -= rowtotal;
        });

        balance = balance - grandtotal;

        $('#grandtotal').text(
            (grandtotal >= 0 ? '' : '-') + formatRupiahIni(grandtotal.toFixed(2).toString().replace('.',','))
        );
        $('#total_op').text(
            (total_op >= 0 ? '' : '-') + formatRupiahIni(total_op.toFixed(2).toString().replace('.',','))
        );
        $('#balance').text(
            (balance >= 0 ? '' : '-') + formatRupiahIni(balance.toFixed(2).toString().replace('.',','))
        );
    }

    function cekRow(element){
        /* if($(element).val()){
            let val = parseFloat($(element).val().replaceAll(".", "").replaceAll(",",".")), limit = parseFloat($(element).data('limit').replaceAll(".", "").replaceAll(",","."));
            if(val > limit){
                $(element).val(
                    (limit >= 0 ? '' : '-') + formatRupiahIni(roundTwoDecimal(limit).toString().replace('.',','))
                );
            }
        } */
    }

    function removeUsedData(type,id){
        $.ajax({
            url: '{{ Request::url() }}/remove_used_data',
            type: 'POST',
            dataType: 'JSON',
            data: { 
                type : type,
                id : id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                
            },
            success: function(response) {
                $('.row_op[data-id="' + id + '"]').remove();
                if($('.row_op').length == 0 && $('#empty-detail').length == 0){
                    $('#body-detail-op').append(`
                        <tr id="empty-detail">
                            <td colspan="8">
                                Pilih Outgoing Payment untuk memulai...
                            </td>
                        </tr>
                    `);
                }
            },
            error: function() {
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
            }
        });
    }

    function printPreview(code,aslicode){
        swal({
            title: "Apakah Anda ingin mengeprint dokumen ini?",
            text: "Dengan Kode "+aslicode,
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                $.ajax({
                    url: '{{ Request::url() }}/print_individual/' + code,
                    type:'GET',
                    beforeSend: function() {
                        loadingOpen('.modal-content');
                    },
                    complete: function() {
                        
                    },
                    success: function(data){
                        loadingClose('.modal-content');
                        printService.submit({
                            'type': 'INVOICE',
                            'url': data
                        })
                    }
                });  
            }
        });
        
    }

    function show(id){
        $.ajax({
            url: '{{ Request::url() }}/show',
            type: 'POST',
            dataType: 'JSON',
            data: {
                id: id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('#main');
            },
            success: function(response) {
                loadingClose('#main');

                if(response.status == 500){
                    swal({
                        title: 'Ups!',
                        text: response.message,
                        icon: 'warning'
                    });
                    $('#payment_request_id').empty();
                }else{
                    $('#modal1').modal('open');
                    $('#temp').val(id);
                    $('#code').val(response.code);
                    $('#code_place_id').val(response.code_place_id).formSelect();
                    $('#company_id').val(response.company_id).formSelect();
                    $('#post_date').val(response.post_date);
                    $('#note').val(response.note);

                    if(response.details.length > 0){
                        $('.row_detail,.row_op').each(function(){
                            $(this).remove();
                        });
                        $('#empty-detail').remove();

                        $.each(response.details, function(i, val) {
                            var count = makeid(10);
                            $('#body-detail-op').append(`
                                <tr class="row_op" data-id="` + val.id + `">
                                    <input type="hidden" name="arr_type[]" value="` + val.type + `" id="arr_type` + count + `">
                                    <input type="hidden" name="arr_id[]" value="` + val.id + `" id="arr_id` + count + `">
                                    <td>
                                        ` + val.code + `
                                    </td>
                                    <td>
                                        ` + val.bp + `
                                    </td>
                                    <td class="center">
                                        ` + val.post_date + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.total + `
                                    </td>
                                    <td class="right-align">
                                        ` + val.used + `
                                    </td>
                                    <td class="center">
                                        <input name="arr_nominal[]" onfocus="emptyThis(this);" class="browser-default" type="text" data-max="` + val.balance + `" value="` + val.nominal + `" onkeyup="formatRupiah(this);checkRow('` + count + `');countAll();" style="text-align:right;width:100%;" id="arr_nominal` + count + `">
                                    </td>
                                    <td class="center">
                                        <input name="arr_note_source[]" class="browser-default" type="text" value="` + val.note + `" style="width:100%;" id="arr_note_source` + count + `">
                                    </td>
                                    <td class="center">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                </tr>
                            `);
                        });
                    }

                    if(response.costs.length > 0){
                        $.each(response.costs, function(i, val){
                            var count = makeid(10);
                            $('#last-row-detail').before(`
                                <tr class="row_detail">
                                    <td class="">
                                        <select class="browser-default" id="arr_coa` + count + `" name="arr_coa[]"></select>
                                    </td>
                                    <td class="center">
                                        <select class="browser-default" id="arr_cost_distribution_cost` + count + `" name="arr_cost_distribution_cost[]"></select> 
                                    </td>
                                    <td class="center">
                                        <select class="browser-default" id="arr_place` + count + `" name="arr_place[]">
                                            @foreach ($place as $rowplace)
                                                <option value="{{ $rowplace->id }}">{{ $rowplace->code }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_line` + count + `" name="arr_line[]" onchange="changePlace(this);">
                                            <option value="">--Kosong--</option>
                                            @foreach ($line as $rowline)
                                                <option value="{{ $rowline->id }}" data-place="{{ $rowline->place_id }}">{{ $rowline->name }}</option>
                                            @endforeach
                                        </select>    
                                    </td>
                                    <td>
                                        <select class="browser-default" id="arr_machine` + count + `" name="arr_machine[]" onchange="changeLine(this);">
                                            <option value="">--Kosong--</option>
                                            @foreach ($machine as $rowmachine)
                                                <option value="{{ $rowmachine->id }}" data-line="{{ $rowmachine->line_id }}">{{ $rowmachine->name }}</option>
                                            @endforeach
                                        </select>    
                                    </td>
                                    <td class="center">
                                        <select class="browser-default" id="arr_division` + count + `" name="arr_division[]">
                                            <option value="">--Kosong--</option>
                                            @foreach ($division as $row)
                                                <option value="{{ $row->id }}">{{ $row->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="center">
                                        <select class="browser-default" id="arr_project` + count + `" name="arr_project[]"></select>
                                    </td>
                                    <td>
                                        <input type="text" name="arr_note[]" placeholder="Keterangan 1..." value="` + val.note + `" data-id="` + count + `">
                                    </td>
                                    <td>
                                        <input type="text" name="arr_note2[]" placeholder="Keterangan 2..." value="` + val.note2 + `" data-id="` + count + `">
                                    </td>
                                    <td class="center">
                                        <input class="browser-default" type="text" name="arr_nominal_debit_fc[]" value="` + val.nominal_debit_fc + `" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();">
                                    </td>
                                    <td class="right-align">
                                        <input class="browser-default" type="text" name="arr_nominal_credit_fc[]" value="` + val.nominal_credit_fc + `" data-id="` + count + `" onkeyup="formatRupiah(this);countAll();">
                                    </td>
                                    <td class="right-align">
                                        <input class="browser-default" type="text" name="arr_nominal_debit[]" value="` + val.nominal_debit + `" data-id="` + count + `" onkeyup="formatRupiah(this);" readonly>
                                    </td>
                                    <td class="right-align">
                                        <input class="browser-default" type="text" name="arr_nominal_credit[]" value="` + val.nominal_credit + `" data-id="` + count + `" onkeyup="formatRupiah(this);" readonly>
                                    </td>
                                    <td class="center">
                                        <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-detail" href="javascript:void(0);">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                </tr>
                            `);
                            if(val.coa_id){
                                $('#arr_coa' + count).append(`
                                    <option value="` + val.coa_id + `">` + val.coa_name + `</option>
                                `);
                            }
                            $('#arr_percent_tax' + count).val(val.percent_tax);
                            $('#arr_percent_wtax' + count).val(val.percent_wtax);
                            $('#arr_include_tax' + count).val(val.include_tax);
                            if(val.cost_distribution_id){
                                $('#arr_cost_distribution_cost' + count).append(`
                                    <option value="` + val.cost_distribution_id + `">` + val.cost_distribution_name + `</option>
                                `);
                            }
                            $('#arr_place' + count).val(val.place_id);
                            $('#arr_line' + count).val(val.line_id);
                            $('#arr_machine' + count).val(val.machine_id);
                            $('#arr_division' + count).val(val.division_id);
                            if(val.project_id){
                                $('#arr_project' + count).append(`
                                    <option value="` + val.project_id + `">` + val.project_name + `</option>
                                `);
                            }
                            select2ServerSide('#arr_coa' + count, '{{ url("admin/select2/coa") }}');
                            select2ServerSide('#arr_project' + count, '{{ url("admin/select2/project") }}');
                            select2ServerSide('#arr_cost_distribution_cost' + count, '{{ url("admin/select2/cost_distribution") }}');
                        });
                    }

                    $('#grandtotal').text(response.grandtotal);
                    $('#total_op').text(response.total_op);
                    $('#balance').text(response.balance);

                    $('.modal-content').scrollTop(0);
                    $('#note').focus();
                    M.updateTextFields();
                }
            },
            error: function() {
                $('.modal-content').scrollTop(0);
                loadingClose('#main');
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
            }
        });
    }

    function success(){
        loadDataTable();
        $('#modal1').modal('close');
    }

    function save(){
		swal({
            title: "Apakah anda yakin ingin simpan?",
            text: "Silahkan cek kembali form, dan jika sudah yakin maka lanjutkan!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                var formData = new FormData($('#form_data')[0]);

                formData.delete("arr_coa[]");
                formData.delete("arr_cost_distribution_cost[]");
                formData.delete("arr_project[]");
                let passed = true;
                $('select[name^="arr_coa[]"]').each(function(index){
                    if(!$(this).val() || !$('input[name^="arr_nominal_debit_fc[]"]').eq(index).val() || !$('input[name^="arr_nominal_credit_fc[]"]').eq(index).val()){
                        passed = false;
                    }
                    formData.append('arr_coa[]',$(this).val());
                    formData.append('arr_project[]',($('select[name^="arr_project[]"]').eq(index).val() ? $('select[name^="arr_project[]"]').eq(index).val() : ''));
                    formData.append('arr_cost_distribution_cost[]',($('select[name^="arr_cost_distribution_cost[]"]').eq(index).val() ? $('select[name^="arr_cost_distribution_cost[]"]').eq(index).val() : ''));
                });

                var path = window.location.pathname;
                path = path.replace(/^\/|\/$/g, '');

                
                var segments = path.split('/');
                var lastSegment = segments[segments.length - 1];
            
                formData.append('lastsegment',lastSegment);

                if(passed){
                    $.ajax({
                        url: '{{ Request::url() }}/create',
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
                            $('#validation_alert').hide();
                            $('#validation_alert').html('');
                            loadingOpen('.modal-content');
                        },
                        success: function(response) {
                            loadingClose('.modal-content');
                            $('input').css('border', 'none');
                            $('input').css('border-bottom', '0.5px solid black');
                            if(response.status == 200) {
                                success();
                                M.toast({
                                    html: response.message
                                });
                            } else if(response.status == 422) {
                                $('#validation_alert').show();
                                $('.modal-content').scrollTop(0);
                                $.each(response.error, function(field, errorMessage) {
                                    $('#' + field).addClass('error-input');
                                    $('#' + field).css('border', '1px solid red');
                                    
                                });
                                swal({
                                    title: 'Ups! Validation',
                                    text: 'Check your form.',
                                    icon: 'warning'
                                });

                                $.each(response.error, function(i, val) {
                                    $.each(val, function(i, val) {
                                        $('#validation_alert').append(`
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
                }else{
                    swal({
                        title: 'Ups!',
                        text: 'Inputan nominal/coa tidak boleh kosong.',
                        icon: 'error'
                    });
                }
            }
        });
    }

    function printData(){
        var search = window.table.search(), status = $('#filter_status').val(), company = $('#filter_company').val(), start_date = $('#start_date').val(), finish_date = $('#finish_date').val();
        
        $.ajax({
            type : "POST",
            url  : '{{ Request::url() }}/print',
            data : {
                search : search,
                status : status,
                company : company,
                start_date : start_date,
                finish_date : finish_date,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            cache: false,
            success: function(data){
                var w = window.open('about:blank');
                w.document.open();
                w.document.write(data);
                w.document.close();
            }
        });
    }

    function makeTreeOrg(data,link){
        var $ = go.GraphObject.make;

        myDiagram =
        $(go.Diagram, "myDiagramDiv",
        {
            initialContentAlignment: go.Spot.Center,
            "undoManager.isEnabled": true,
            layout: $(go.TreeLayout,
            { 
                angle: 180,
                path: go.TreeLayout.PathSource,  
                setsPortSpot: false, 
                setsChildPortSpot: false,  
                arrangement: go.TreeLayout.ArrangementHorizontal
            })
        });
        $("PanelExpanderButton", "METHODS",
            { row: 2, column: 1, alignment: go.Spot.TopRight },
            {
                visible: true,
                click: function(e, obj) {
                    var node = obj.part.parent;
                    var diagram = node.diagram;
                    var data = node.data;
                    diagram.startTransaction("Collapse/Expand Methods");
                    diagram.model.setDataProperty(data, "isTreeExpanded", !data.isTreeExpanded);
                    diagram.commitTransaction("Collapse/Expand Methods");
                }
            },
            new go.Binding("visible", "methods", function(arr) { return arr.length > 0; })
        );
        myDiagram.addDiagramListener("ObjectDoubleClicked", function(e) {
            var part = e.subject.part;
            if (part instanceof go.Link) {
                
              
            } else if (part instanceof go.Node) {
                window.open(part.data.url);
                if (part.isTreeExpanded) {
                    part.collapseTree();
                } else {
                    part.expandTree();
                }
              
            }
        });
        myDiagram.nodeTemplate =
        $(go.Node, "Auto",
            {
            locationSpot: go.Spot.Center,
            fromSpot: go.Spot.AllSides,
            toSpot: go.Spot.AllSides,
            portId: "",  

            },
            { isTreeExpanded: false },  
            $(go.Shape, { fill: "lightgrey", strokeWidth: 0 },
            new go.Binding("fill", "color")),
            $(go.Panel, "Table",
            { defaultRowSeparatorStroke: "black" },
            $(go.TextBlock,
                {
                row: 0, columnSpan: 2, margin: 3, alignment: go.Spot.Center,
                font: "bold 12pt sans-serif",
                isMultiline: false, editable: true
                },
                new go.Binding("text", "name").makeTwoWay()
            ),
            $(go.TextBlock, "Properties",
                { row: 1, font: "italic 10pt sans-serif" },
                new go.Binding("visible", "visible", function(v) { return !v; }).ofObject("PROPERTIES")
            ),
            $(go.Panel, "Vertical", { name: "PROPERTIES" },
                new go.Binding("itemArray", "properties"),
                {
                row: 1, margin: 3, stretch: go.GraphObject.Fill,
                defaultAlignment: go.Spot.Left,
                }
            ),
            
            $(go.Panel, "Auto",
                { portId: "r" },
                { margin: 6 },
                $(go.Shape, "Circle", { fill: "transparent", stroke: null, desiredSize: new go.Size(8, 8) })
            ),
            ),

            $("TreeExpanderButton",
            { alignment: go.Spot.Right, alignmentFocus: go.Spot.Right, width: 14, height: 14 }
            )
        );
        myDiagram.model.root = data[0].key;
        

        myDiagram.addDiagramListener("InitialLayoutCompleted", function(e) {
        setTimeout(function() {
        
            var rootKey = data[0].key; 
            var rootNode = myDiagram.findNodeForKey(rootKey);
            if (rootNode !== null) {
                rootNode.collapseTree();
            }
        }, 100); 
        });

        myDiagram.layout = $(go.TreeLayout);

        myDiagram.addDiagramListener("InitialLayoutCompleted", e => {
           e.diagram.findTreeRoots().each(r => r.expandTree(3));
            e.diagram.nodes.each(node => {
                node.findTreeChildrenNodes().each(child => child.expandTree(10));
            });
        });

        myDiagram.model = $(go.GraphLinksModel,
        {
            copiesArrays: true,
            copiesArrayObjects: true,
            nodeDataArray: data,
            linkDataArray: link
        });
            
            
    }

    function viewStructureTree(id){
        $.ajax({
            url: '{{ Request::url() }}/viewstructuretree',
            type: 'GET',
            dataType: 'JSON',
            data: { 
                id : id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            success: function(response) {
                loadingClose('.modal-content');
            
                makeTreeOrg(response.message,response.link);
                
                $('#modal4').modal('open');
            },
            error: function() {
                swal({
                    title: 'Ups!',
                    text: 'Check your internet connection.',
                    icon: 'error'
                });
            }
        });
    }

    function viewJournal(id){
        $.ajax({
            url: '{{ Request::url() }}/view_journal/' + id,
            type:'GET',
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            complete: function() {
                
            },
            success: function(data){
                loadingClose('.modal-content');
                if(data.status == '500'){
                    M.toast({
                        html: data.message
                    });
                }else{
                    $('#modal6').modal('open');
                    $('#title_data').append(``+data.title+``);
                    $('#code_data').append(data.code);
                    $('#body-journal-table').append(data.tbody);
                    $('#user_jurnal').append(`Pengguna : `+data.user);
                    $('#note_jurnal').append(`Keterangan : `+data.note);
                    $('#ref_jurnal').append(`Referensi : `+data.reference);
                    $('#company_jurnal').append(`Perusahaan : `+data.company);
                    $('#post_date_jurnal').append(`Tanggal : `+data.post_date);
                }
            }
        });
    }

    function whatPrinting(code){
        $.ajax({
            url: '{{ Request::url() }}/print_individual/' + code,
            type:'GET',
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            complete: function() {
                
            },
            success: function(data){
                loadingClose('.modal-content');
                window.open(data, '_blank');
            }
        });
    }
</script>