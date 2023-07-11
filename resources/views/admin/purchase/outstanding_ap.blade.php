<!-- BEGIN: Page Main-->
<div id="main">
    <div class="row">
        <div class="content-wrapper-before blue-grey lighten-5"></div>
        <div class="col s12">
            <div class="container">
                <div class="section">
                    
                    <div class="row">
                        <div class="col s12 m12 l12">
                            <div class="card padding-4 animate fadeLeft">
                                <div class="row">
                                    <form class="row" id="form_data_filter" onsubmit="return false;">
                                        <div class="col s12">
                                            <div id="validation_alert_multi" style="display:none;"></div>
                                        </div>
                                        <div class="col s12">
                                            <div class="row">
                                                <div class="col m4 s6 ">
                                                    <label for="date" style="font-size:1rem;">Tanggal Posting :</label>
                                                    <div class="input-field col s12">
                                                    <input type="date" id="date" name="date">
                                                    </div>
                                                </div>
                                                <div class="col m4 s6 ">
                                                    
                                                </div>
                                                <div class="col m4 s6 ">
                                                    <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="filterByDate();">
                                                        <i class="material-icons hide-on-med-and-up">search</i>
                                                        <span class="hide-on-small-onl">Filter</span>
                                                        <i class="material-icons right">search</i>
                                                    </a>
                                                    <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="exportExcel();">
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
                            </div>
                        </div>
                        <div class="col s12 m12" style="overflow: auto">
                            <div class="result" style="width:2500px;">
                                <table class="bordered" style="font-size:10px;">
                                    <thead>
                                        <tr>
                                            <th class="center-align">No.</th>
                                            <th class="center-align">No Invoice</th>
                                            <th class="center-align">Supplier/Vendor</th>
                                            <th class="center-align">No PO</th>
                                            <th class="center-align">TGL Post</th>
                                            <th class="center-align">TGL Terima</th>
                                            <th class="center-align">TOP(Hari)</th>
                                            <th class="center-align">TGL Tenggat</th>
                                            <th class="center-align">Nama Item</th>
                                            <th class="center-align">Note 1</th>
                                            <th class="center-align">Note 2</th>
                                            <th class="center-align">Qty</th>
                                            <th class="center-align">Satuan</th>
                                            <th class="center-align">Harga Satuan</th>
                                            <th class="center-align">Total</th>
                                            <th class="center-align">PPN</th>
                                            <th class="center-align">PPH</th>
                                            <th class="center-align">Grandtotal</th>
                                            <th class="center-align">Dibayar</th>
                                            <th class="center-align">Sisa</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detail_invoice">
                                        {{-- @foreach($itemstocks as $key => $row)
                                            <tr>
                                                <td class="center-align">{{ ($key + 1) }}</td>
                                                <td class="center-align">{{ $row->item->name }}</td>
                                                <td class="center-align">{{ $row->place->name.' - '.$row->place->company->name }}</td>
                                                <td class="center-align">{{ $row->warehouse->name }}</td>
                                                <td class="center-align">{{ number_format($row->qty,3,',','.').' '.$row->item->uomUnit->code }}</td>
                                                <td class="right-align">{{ number_format($row->valueNow(),3,',','.') }}</td>
                                            </tr>
                                        @endforeach --}}
                                    </tbody>
                                </table>
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
        var date = $('#date').val();
        window.location = "{{ Request::url() }}/export?date=" + date;
    }
    function filterByDate(){
        var formData = new FormData($('#form_data_filter')[0]);
        $.ajax({
            url: '{{ Request::url() }}/filter_by_date',
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
                    $('#detail_invoice').empty();
                    $.each(response.message, function(i, val) {
                        console.log(val);
                        $('#detail_invoice').append(`
                            <tr>
                                <td class="center-align" rowspan="`+val.details.length+`">`+(i+1)+`</td>
                                <td  rowspan="`+val.details.length+`">`+val.code+`</td>
                                <td  rowspan="`+val.details.length+`">`+val.vendor+`</td>
                                <td >`+val.details[0].po+`</td>
                                <td class="center-align" rowspan="`+val.details.length+`">`+val.post_date+`</td>
                                <td class="center-align" rowspan="`+val.details.length+`">`+val.rec_date+`</td>
                                <td class="center-align">`+val.details[0].top+`</td>
                                <td class="center-align" rowspan="`+val.details.length+`">`+val.due_date+`</td>
                                <td >`+val.details[0].item_name+`</td>
                                <td >`+val.details[0].note1+`</td>
                                <td >`+val.details[0].note2+`</td>
                                <td class="center-align">`+val.details[0].qty+`</td>
                                <td class="center-align">`+val.details[0].unit+`</td>
                                <td class="right-align">`+val.details[0].price_o+`</td>
                                <td class="right-align">`+val.details[0].total+`</td>
                                <td class="right-align">`+val.details[0].ppn+`</td>
                                <td class="right-align">`+val.details[0].pph+`</td>
                                <td class="right-align" rowspan="`+val.details.length+`">`+val.grandtotal+`</td>
                                <td class="right-align" rowspan="`+val.details.length+`">`+val.payed+`</td>
                                <td class="right-align" rowspan="`+val.details.length+`">`+val.sisa+`</td>
                            </tr>
                        `);
                        $.each(val.details,function(j, details) {
                            if(j>0){
                                $('#detail_invoice').append(`
                                    <td >`+val.details[j].po+`</td>
                                    <td class="center-align">`+val.details[j].top+`</td>
                                    <td >`+val.details[j].item_name+`</td>
                                    <td >`+val.details[j].note1+`</td>
                                    <td >`+val.details[j].note2+`</td>
                                    <td class="center-align">`+val.details[j].qty+`</td>
                                    <td class="center-align">`+val.details[j].unit+`</td>
                                    <td class="right-align">`+val.details[j].price_o+`</td>
                                    <td class="right-align">`+val.details[j].total+`</td>
                                    <td class="right-align">`+val.details[j].ppn+`</td>
                                    <td class="right-align">`+val.details[j].pph+`</td>
                                    
                                `);
                            }
                        });
                    });
                    
           
                    M.toast({
                        html: 'filtered'
                    });
                } else if(response.status == 422) {
                    $('#validation_alert_multi').show();
                    $('.modal-content').scrollTop(0);
                    console.log(response.error);
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
</script>