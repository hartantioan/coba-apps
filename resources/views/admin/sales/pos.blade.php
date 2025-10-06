<script src="{{ url('app-assets/js/sweetalert2.js') }}"></script>
<style>
    .group-filter.active {
        background-color: #4a148c !important; /* dark purple */
    }

    .hidden {
        display: none !important;
    }
    .search-bar {
        margin-bottom: 20px;
    }

    .group-buttons {
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .group-buttons .btn {
        background-color: #1a075f;
        text-transform: none;
    }

    .item-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 10px;
    }

    .item-card {
        height: 100px;
        border-radius: 8px;
        transition: box-shadow 0.2s ease;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 10px;
        text-align: center;
    }

    .item-card:hover {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        cursor: pointer;
    }

    .item-card span {
        font-weight: 600;
        font-size: 14px;
        line-height: 1.2;
    }

    .item-card .price {
        margin-top: 6px;
        font-size: 13px;
    }

    .modal {
        top: 50% !important;
        transform: translateY(-50%) !important;
        width: 400px !important;
        max-height: 400px !important;
        overflow-y: auto !important;
        border-radius: 8px;
    }

    /* Optional: Adjust modal content padding */
    .modal .modal-content {
        padding: 20px;
    }

    /* Optional: Sticky footer for modal actions */
    .modal .modal-footer {
        position: sticky;
        bottom: 0;
        background: #fff;
        padding: 10px 20px;
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

                        <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="whatPrinting();">
                            <i class="material-icons hide-on-med-and-up">local_printshop</i>
                            <span class="hide-on-small-onl">{{ __('translations.print') }}</span>
                            <i class="material-icons right">local_printshop</i>
                        </a>

                    </div>
                </div>
            </div>
        </div>
        <div class="col s12">
            <div class="container">
                <div class="col xl9 m8 s12">
                    <div class="card">
                        <div class="card-content px-36">
                            <form id="form_data" onsubmit="return false;">
                                <!-- Top Fields -->
                                <div class="row mb-3">
                                    <div class="col xl3 m12 s12">
                                        <div class="input-field">
                                            <label class="active">Invoice Code</label>
                                            <h6 id="code" class="mt-0 mb-0" style="font-weight: bold;">INV-XXXX</h6>
                                        </div>
                                    </div>

                                    <div class="col xl3 m6 s12">
                                        <div class="input-field">
                                            <input id="post_date" name="post_date" type="date" max="{{ date('9999-12-31') }}"
                                                value="{{ date('Y-m-d') }}">
                                            <label for="post_date" class="active">Tanggal</label>
                                        </div>
                                    </div>

                                    <div class="col xl6 m6 s12">
                                        <div class="input-field">
                                            <select id="store_customer_id" name="store_customer_id" class="browser-default">
                                                <option value="" disabled selected>-- Pilih Customer --</option>
                                                <!-- dynamic options -->
                                            </select>
                                            <label for="store_customer_id" class="active">{{ __('translations.customer') }}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="invoice-product-details mb-3">
                                    <div class="row">
                                        <div class="col m12 s12 step7">
                                            <p class="mt-2 mb-2">

                                                <div>
                                                    <table class="bordered" id="table-detail">
                                                        <thead>
                                                            <tr>
                                                                <th class="center">{{ __('translations.item') }}</th>
                                                                <th class="center">{{ __('translations.qty') }}</th>
                                                                <th class="center">Harga</th>
                                                                <th class="center">Diskon</th>
                                                                <th class="center">Total</th>
                                                                <th class="center">{{ __('translations.delete') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="body-item">
                                                            <tr id="last-row-item">
                                                                <td colspan="6">

                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </p>
                                            {{-- <button class="waves-effect waves-light cyan btn-small mb-1 mr-1 right mt-1" onclick="addItem()" href="javascript:void(0);">
                                                <i class="material-icons left">add</i> Tambah Item
                                            </button> --}}
                                        </div>
                                    </div>
                                </div>
                                <div class="invoice-subtotal">
                                    <div class="row">
                                        <div class="col m5 s12">

                                        </div>
                                        <div class="col xl4 m7 s12 offset-xl3">
                                            <ul>
                                                <li class="display-flex justify-content-between">
                                                    <span class="invoice-subtotal-title">Subtotal</span>
                                                    <h6 class="invoice-subtotal-value"  id="invoice-subtotal">00.00</h6>
                                                </li>
                                                <li class="display-flex justify-content-between">
                                                    <span class="invoice-subtotal-title">Discount</span>
                                                    <h6 class="invoice-subtotal-value"  id="invoice-discount">- 00.00</h6>
                                                </li>
                                                <li>
                                                    <div class="divider mt-2 mb-2"></div>
                                                </li>
                                                <li class="display-flex justify-content-between">
                                                    <span class="invoice-subtotal-title">Invoice Total</span>
                                                    <input name="discount" id="discount" hidden>
                                                    <input name="grandtotal" id="grandtotal" hidden>
                                                    <h6 class="invoice-subtotal-value" id="invoice-total">00.00</h6>
                                                </li>
                                                <li class=" mt-2">
                                                    <button class="btn btn-block waves-effect waves-light submit" onclick="save();">{{ __('translations.save') }} <i class="material-icons right">send</i></button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                 <div class="col xl3 m4 s12">
                    <div class="card blue-grey lighten-5" style="padding: 20px;">
                        <div class="input-field mb-3">
                            <input id="scan_barcode" name="scan_barcode" type="text" placeholder="Arahkan cursor disini untuk scan..">
                            <label class="active" for="scan_barcode">Scan Barcode</label>
                        </div>

                        <!-- Add Customer Button with Icon -->
                        <div class="center-align mb-2">
                            <a class="btn waves-effect waves-light modal-trigger tooltipped"
                            href="#addCustomerModal"
                            data-position="top"
                            data-tooltip="Tambah customer baru">
                                <i class="material-icons left">person_add</i> Add Customer
                            </a>
                        </div>

                        <!-- Reset Button -->
                        <div class="center-align mt-5">
                            <a class="btn red lighten-1 waves-effect waves-light" onclick="resetInvoice()">
                                <i class="material-icons left">refresh</i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-overlay"></div>
        </div>
        <div class="col s12">
            <div class="container">
            <div class="card">
                <div class="card-content">

                    <!-- Search Bar -->
                    <div class="input-field search-bar">
                        <input type="text" id="search_bar" class="validate" placeholder="Search items...">
                    </div>

                    <!-- Group Filter Buttons -->
                    <div class="group-buttons">
                        <button class="btn group-filter" data-group="all">All</button>
                        @foreach($groups as $group)
                            <button class="btn small group-filter" data-group="{{ $group->id }}">{{ $group->name }}</button>
                        @endforeach

                    </div>

                    <!-- Items Grid -->
                    <div class="item-grid" id="items-container">
                        @foreach($items as $item)
                            @php
                                $priceList = $item->storeItemPriceList->first();
                            @endphp

                            <div class="card item-card" data-group="{{ $item->itemGroup->id }}" data-name="{{ $item->name }}" onclick="getDataItem('{{ $item->code }}')">
                                <span>{{ $item->name }}</span>
                                <div class="price">
                                    @if($priceList)
                                        {{ number_format($priceList->price, 2) }}
                                    @else
                                        <span class="red-text">Belum dibuat harga di master</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>
        </div>
    </div>
</div>

<div id="addCustomerModal" class="modal" style="width: 400px; max-height: 400px;">
    <form id="form_data_add" onsubmit="return false;">
        <div class="modal-content">
            <h5>Add New Customer</h5>

            <div class="input-field">
                <input id="name" name="name" type="text" placeholder="e.g. John Doe">
                <label for="name" class="active">Customer Name</label>
            </div>

            <div class="input-field">
                <input id="no_telp" name="no_telp" type="text" placeholder="e.g. 08123456789">
                <label for="no_telp" class="active">Phone Number</label>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn waves-effect waves-light" onclick="saveCustomer();">
                {{ __('translations.save') }}
                <i class="material-icons right">send</i>
            </button>
            <a href="javascript:void(0);" class="modal-close waves-effect btn-flat">
                {{ __('translations.close') }}
            </a>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search_bar');
        const groupButtons = document.querySelectorAll('.group-filter');
        const items = document.querySelectorAll('.item-card');

        // Filter by group
        groupButtons.forEach(button => {
            button.addEventListener('click', () => {
                const group = button.getAttribute('data-group');

                // Remove 'active' class from all buttons
                groupButtons.forEach(btn => btn.classList.remove('active'));

                // Add 'active' to clicked one
                button.classList.add('active');

                // Filter items
                items.forEach(item => {
                    const itemGroup = item.getAttribute('data-group');
                    if (group === 'all' || itemGroup === group) {
                        item.classList.remove('hidden');
                    } else {
                        item.classList.add('hidden');
                    }
                });
            });
        });


        // Filter by search
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.toLowerCase();
            items.forEach(item => {
                const name = item.getAttribute('data-name').toLowerCase();
                if (name.includes(query)) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        });
    });
</script>




<!-- END: Page Main-->
<script>

    var arrCode = [];
    $(function() {
        getCode();

        select2ServerSide('#store_customer_id', '{{ url("admin/select2/store_customer") }}');
        $('#body-item').on('click', '.delete-data-item', function() {
            var codeToRemove = $(this).closest('tr').data('code');
            $(this).closest('tr').remove();
            arrCode = arrCode.filter(function(code) {
                return code !== codeToRemove;
            });
            if($('.row_item').length == 0){
                $('#body-item').append(`
                    <tr id="last-row-item">
                        <td class="center-align" colspan="14">
                            Silahkan tambahkan Order Produksi untuk memulai...
                        </td>
                    </tr>
                `);
            }
            countAll();
        });
        $("#scan_barcode").on( "change", function(e) {
            if($(this).val()){
                let code = $(this).val();
                $.ajax({
                    url: '{{ Request::url() }}/get_pallet_barcode_by_scan',
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        code: code,
                        batch_used: arrCode,
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
                            if(response.errors){
                                $.each(response.errors, function(i, val) {
                                    M.toast({
                                        html: val
                                    });
                                });
                            }
                        }else{
                            if(response.length > 0){
                                if($('.row_item').length == 0){
                                    $('#body-item').empty();
                                }
                                $.each(response, function(i, val) {
                                    let existingRow = $(`.row_item[data-code="${val.code}"]`);
                                    if (existingRow.length) {
                                        // If row already exists, increment the quantity
                                        let qtyInput = existingRow.find('input[name="arr_qty[]"]');
                                        let currentQty = parseFloat(qtyInput.val().replaceAll(".", "").replaceAll(",", ".")) || 0;
                                        let dicountInput = existingRow.find('input[name="arr_discount_item[]"]');
                                        let currentDiscount = parseFloat(dicountInput.val().replaceAll(".", "").replaceAll(",", ".")) || 0;
                                        qtyInput.val(currentQty + 1).trigger('keyup');
                                        dicountInput.val(currentDiscount * (currentQty+1)).trigger('keyup');
                                    } else {
                                        // If not exists, create a new row
                                        let count = makeid(10);
                                        let no = $('.row_item').length + 1;

                                        $('#body-item').append(`
                                            <tr class="row_item" data-code="` + val.code + `">
                                                <input type="hidden" name="arr_item_id[]" value="` + val.item_id + `" id="arr_item` + count + `">
                                                <td>
                                                    ` + val.item_name + `
                                                </td>
                                                <td>
                                                    <input name="arr_qty[]" onfocus="emptyThis(this);" id="rowQty` + count + `" type="text" value="1" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')">
                                                </td>
                                                <td>
                                                    <input name="arr_price[]" onfocus="emptyThis(this);" id="rowPrice` + count + `" type="text" value="` + val.price + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')">
                                                </td>
                                                <td>
                                                    <input name="arr_total[]" onfocus="emptyThis(this);" id="arr_total` + count + `" type="text" value="0" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" hidden>
                                                    <input name="arr_discount_item[]" onfocus="emptyThis(this);" id="rowDiscountItem` + count + `" type="text" value="` + val.discount + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" hidden>
                                                    <input name="arr_discount[]" onfocus="emptyThis(this);" id="rowDiscount` + count + `" type="text" value="` + val.discount + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')">
                                                </td>
                                                <td class="center total-column" id="total` + count + `">

                                                </td>
                                                <td class="center-align">
                                                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                                        <i class="material-icons">delete</i>
                                                    </a>
                                                </td>
                                            </tr>
                                        `);
                                        countRow(count);
                                        arrCode.push(val.code);
                                    }
                                });

                                $('.modal-content').scrollTop($("#body-item").offset().top);
                                countAll();
                            }


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
            e.preventDefault();
            $('#scan_barcode').val('');
        });

        $('#addCustomerModal').modal({
            onOpenStart: function(modal,trigger) {

            },
            onOpenEnd: function(modal, trigger) {

            },
            onCloseEnd: function(modal, trigger){
                $('#form_data_add')[0].reset();
            }
        });
    });

    function addItem(){

        let countItem = $('.row_item').length;

        if(countItem > 59){
            swal({
                title: 'Ups!',
                text: 'Satu PR tidak boleh memiliki baris item lebih dari 60.',
                icon: 'error'
            });
            return false;
        }

        $('#empty-item').remove();
        var count = makeid(10);
        $('#body-item').append(`
            <tr class="row_item" data-id="">
                <td>
                    <select class="browser-default item-array" id="arr_item` + count + `" name="arr_item[]" onchange="getRowUnit('` + count + `')"></select>
                </td>
                <td>
                    <input name="arr_qty[]" onfocus="emptyThis(this);" id="rowQty` + count + `" type="text" value="1" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')">
                </td>
                <td>
                    <input name="arr_price[]" onfocus="emptyThis(this);" id="rowPrice` + count + `" type="text" value="` + val.price + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')">
                </td>
                <td>
                    <input name="arr_total[]" onfocus="emptyThis(this);" id="arr_total` + count + `" type="text" value="0" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" hidden>
                    <input name="arr_discount_item[]" onfocus="emptyThis(this);" id="rowDiscountItem` + count + `" type="text" value="` + val.discount + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" hidden>
                    <input name="arr_discount[]" onfocus="emptyThis(this);" id="rowDiscount` + count + `" type="text" value="` + val.discount + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')">
                </td>
                <td class="center total-column" id="total` + count + `">
                </td>
                <td class="center-align">
                    <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            </tr>
        `);
        $('#arr_item'+ count).select2({
            placeholder: '-- Pilih ya --',
            minimumInputLength: 4,
            allowClear: true,
            cache: true,
            width: 'resolve',
            dropdownParent: $('body').parent(),
            ajax: {
                url: '{{ url("admin/select2/purchase_item") }}',
                type: 'GET',
                dataType: 'JSON',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true,
            }
        });

    }


    function resetInvoice(){
         swal({
            title: "Apakah anda Sudah Print?",
            text: "Silahkan cek kembali, dan jika sudah yakin maka lanjutkan!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                getCode()
                $('#form_data')[0].reset();
                $('#store_customer_id').empty();
                document.getElementById('invoice-subtotal').innerText = '00.00';
                document.getElementById('invoice-discount').innerText = '00.00';
                document.getElementById('invoice-total').innerText = '00.00';
                $('.row_item').each(function(){
                    $(this).remove();
                });
            }
        });
    }

    String.prototype.replaceAt = function(index, replacement) {
        return this.substring(0, index) + replacement + this.substring(index + replacement.length);
    };

    function getCode(val){
        $.ajax({
            url: '{{ Request::url() }}/get_code',
            type: 'POST',
            dataType: 'JSON',
            data: {
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                loadingOpen('.modal-content');
            },
            success: function(response) {
                loadingClose('.modal-content');
                $('#code').text(response);
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

    function saveCustomer(){
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
                var formData = new FormData($('#form_data_add')[0]);
                $.ajax({
                    url: '{{ Request::url() }}/create_store_customer',
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
                        loadingOpen('#modal1');
                    },
                    success: function(response) {
                        loadingClose('#modal1');
                        if(response.status == 200) {

                            $('#addCustomerModal').modal('close');
                            M.toast({
                                html: response.message
                            });
                        } else if(response.status == 422) {
                            $('#validation_alert').show();
                            $('.modal-content').scrollTop(0);

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
                        loadingOpen('#modal1');
                    },
                    success: function(response) {
                        if(response.status == 200) {
                            M.toast({
                                html: response.message
                            });
                        } else if(response.status == 422) {
                            $('#validation_alert').show();
                            $('.modal-content').scrollTop(0);

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
                        $('#modal1').scrollTop(0);
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
    var printService = new WebSocketPrinter({
        onConnect: function () {

        },
        onDisconnect: function () {
            /* M.toast({
                html: 'Aplikasi penghubung printer tidak terinstall. Silahkan hubungi tim EDP.'
            }); */
        },
        onUpdate: function (message) {

        },
    });

    function printData(){
        var arr_id_temp=[];
        $.map(window.table.rows('.selected').nodes(), function (item) {
            var poin = $(item).find('td:nth-child(5)').text().trim();
            arr_id_temp.push(poin);

        });
        $.ajax({
            url: '{{ Request::url() }}/print',
            type: 'POST',
            dataType: 'JSON',
            data: {
                arr_id: arr_id_temp,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
            },
            success: function(response) {
                printService.submit({
                    'type': 'INVOICE',
                    'url': response.message
                });
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

    function changemulti(){
        var arr_id_temp=[];
        $.map(window.table.rows('.selected').nodes(), function (item) {
            var poin = $(item).find('td:nth-child(2)').text().trim();
            arr_id_temp.push(poin);

        });
        var msg = '';
        swal({
            title: "Apakah anda yakin membuka multi LC?",
            text: "Data yang sudah terupdate bisa dikembalikan dengan menekan tombol ini.",
            buttons: true,
        })
        .then(message => {
            if (message != "" && message != null) {
                $.ajax({
                    url: '{{ Request::url() }}/update_multiple_lc',
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        arr_id: arr_id_temp,
                        msg : message
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
                            loadDataTable();
                            M.toast({
                                html: response.message
                            });
                        } else {
                            M.toast({
                                html: response.message
                            });
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
        });
    }

    function printMultiSelect(){
        var formData = new FormData($('#form_data_print_multi')[0]);
        var table = $('#datatable_serverside').DataTable();
        var data = table.data().toArray();
        var etNumbers = data.map(item => item[1]);
        var path = window.location.pathname;
        path = path.replace(/^\/|\/$/g, '');


        var segments = path.split('/');
        var lastSegment = segments[segments.length - 1];
        formData.append('tabledata',etNumbers);
        formData.append('lastsegment',lastSegment);
        swal({
            title: "Apakah Anda ingin mengeprint dokumen ini?",
            text: "pastikan bahwa isian sudah benar.",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: 'Tidak, jangan!',
            delete: 'Ya, lanjutkan!'
            }
        }).then(function (willDelete) {
            if (willDelete) {
                    $.ajax({
                    url: '{{ Request::url() }}/print_by_range',
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
                        loadingOpen('.modal-content');
                    },
                    success: function(response) {
                        loadingClose('.modal-content');
                        if(response.status == 200) {
                            $('#modal5').modal('close');
                        /*  printService.submit({
                                'type': 'INVOICE',
                                'url': response.message
                            }) */
                            M.toast({
                                html: response.message
                            });
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
        });

    }

    function printPreview(code){
        swal({
            title: "Apakah Anda ingin mengeprint dokumen ini?",
            text: "",
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

    function whatPrinting(){
        var code = $('#code').text();
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


    function countRow(id){
        if($('#arr_item' + id).val()){
            var qty = parseFloat($('#rowQty' + id).val().replaceAll(".", "").replaceAll(",","."));
            var price = parseFloat($('#rowPrice' + id).val().replaceAll(".", "").replaceAll(",","."));
            var discount = parseFloat($('#rowDiscount' + id).val().replaceAll(".", "").replaceAll(",","."));
            var total = (qty * price )- discount;
            console.log(total);

            $('#arr_total' + id).val(formatRupiahIni(total.toFixed(3).toString().replace('.',',')));
            $('#total' + id).text(formatRupiahIni(total.toFixed(3).toString().replace('.',',')));
            countAll()
        }
    }

    function getDataItem(code){
        $.ajax({
            url: '{{ Request::url() }}/get_pallet_barcode_by_scan',
            type: 'POST',
            dataType: 'JSON',
            data: {
                code: code,
                batch_used: arrCode,
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
                    if(response.errors){
                        $.each(response.errors, function(i, val) {
                            M.toast({
                                html: val
                            });
                        });
                    }
                }else{
                    if(response.length > 0){
                        if($('.row_item').length == 0){
                            $('#body-item').empty();
                        }
                        $.each(response, function(i, val) {
                            let existingRow = $(`.row_item[data-code="${val.code}"]`);
                            console.log(val.code);
                            console.log(existingRow);
                            if (existingRow.length) {
                                // If row already exists, increment the quantity
                                let qtyInput = existingRow.find('input[name="arr_qty[]"]');
                                let currentQty = parseFloat(qtyInput.val().replaceAll(".", "").replaceAll(",", ".")) || 0;
                                let dicountInput = existingRow.find('input[name="arr_discount_item[]"]');
                                let currentDiscount = parseFloat(dicountInput.val().replaceAll(".", "").replaceAll(",", ".")) || 0;
                                qtyInput.val(currentQty + 1).trigger('keyup');
                                dicountInput.val(currentDiscount * (currentQty+1)).trigger('keyup');
                            } else {
                                // If not exists, create a new row
                                let count = makeid(10);
                                let no = $('.row_item').length + 1;

                                $('#body-item').append(`
                                    <tr class="row_item" data-code="` + val.code + `">
                                        <input type="hidden" name="arr_item_id[]" value="` + val.item_id + `" id="arr_item` + count + `">
                                        <td>
                                            ` + val.item_name + `
                                        </td>
                                        <td>
                                            <input name="arr_qty[]" onfocus="emptyThis(this);" id="rowQty` + count + `" type="text" value="1" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')">
                                        </td>
                                        <td>
                                            <input name="arr_price[]" onfocus="emptyThis(this);" id="rowPrice` + count + `" type="text" value="` + val.price + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')">
                                        </td>
                                        <td>
                                            <input name="arr_total[]" onfocus="emptyThis(this);" id="arr_total` + count + `" type="text" value="0" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" hidden>
                                            <input name="arr_discount_item[]" onfocus="emptyThis(this);" id="rowDiscountItem` + count + `" type="text" value="` + val.discount + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')" hidden>
                                            <input name="arr_discount[]" onfocus="emptyThis(this);" id="rowDiscount` + count + `" type="text" value="` + val.discount + `" onkeyup="formatRupiahNoMinus(this);countRow('` + count + `')">
                                        </td>
                                        <td class="center total-column" id="total` + count + `">

                                        </td>
                                        <td class="center-align">
                                            <a class="mb-6 btn-floating waves-effect waves-light red darken-1 delete-data-item" href="javascript:void(0);">
                                                <i class="material-icons">delete</i>
                                            </a>
                                        </td>
                                    </tr>
                                `);
                                countRow(count);
                                arrCode.push(val.code);
                            }
                        });

                        $('.modal-content').scrollTop($("#body-item").offset().top);
                        countAll();
                    }


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

    function countAll(){
        let total = 0;
        let qty_total = 0;
        let discount_total = 0;
        let subtotal = 0;

        $('input[name="arr_qty[]"]').each(function(index) {
            let qty = parseFloat($(this).val().replaceAll(".", "").replaceAll(",", ".")) || 0;
            let price = parseFloat($('input[name="arr_price[]"]').eq(index).val().replaceAll(".", "").replaceAll(",", ".")) || 0;

            subtotal += qty * price;
        });
        $('input[name^="arr_discount[]"]').each(function(){
            discount_total += parseFloat($(this).val().replaceAll(".", "").replaceAll(",","."));
        });
        $('.total-column').each(function () {
            let value = $(this).text().replaceAll(".", "").replaceAll(",", "."); // Convert to a valid number format
            let numericValue = parseFloat(value);
            if (!isNaN(numericValue)) {
                total += numericValue;
            }
        });

        $('#grandtotal').val(total);
        $('#discount').val(discount_total);
        $('#invoice-total').text(formatRupiahIni(total.toFixed(3).toString().replace('.',',')));
        $('#invoice-subtotal').text(formatRupiahIni(subtotal.toFixed(3).toString().replace('.',',')));
        $('#invoice-discount').text(formatRupiahIni(discount_total.toFixed(3).toString().replace('.',',')));

    }
</script>
