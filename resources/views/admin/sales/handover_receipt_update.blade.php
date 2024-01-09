<style>
    ::-webkit-scrollbar {
        width: 12px;
    }

    *::-webkit-scrollbar-track {
        background: orange;
    }

    *::-webkit-scrollbar-thumb {
        background-color: blue;
        border-radius: 20px;
        border: 1px solid orange;
    }
    @media only screen and (max-width: 600px){
        #main {
            min-height: calc(100% - 55px) !important;
        }
    }

    td, th {
        vertical-align: top;
    }

    @media only screen and (min-width: 993px){
        #main.main-full {
            padding-left: 0px;
        }
    }

    input[type=text]:not(.browser-default) {
        height: 2rem;
    }
</style>
<div id="main">
    <div class="row">
        <div class="pt-3 pb-1" id="breadcrumbs-wrapper">
            <div class="container">
                <div class="row">
                    <div class="col s8 m8 l8">
                        <h6 class="breadcrumbs-title mt-3 mb-0"><span>{{ $title }}</span></h6>
                    </div>
                    <div class="col s4 m4 l4">
                        <img src="{{ url('website/logo_web_fix.png') }}" width="100%">
                    </div>
                    <div class="col s12 m12 l12 center-align">
                        <span style="font-weight:900;font-size:20px !important;">#{{ $data->code }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12">
            <div class="container">
                <div class="section section-data-tables">
                    <!-- DataTables example -->
                    @if($data->status_tracking)
                    <div class="row">
                        <div class="col l12 m12 s12 p-3 center-align">
                            Data telah diupdate. Anda tidak bisa melakukan perubahan.
                        </div>
                    </div>
                    @endif
                    @foreach($data->marketingOrderHandoverReceiptDetail as $key => $row)
                    <div class="row mb-2" style="border:1px solid black;border-radius:10px;">
                        <div class="col l3 m3 s3 p-3">
                            No.
                        </div>
                        <div class="col l9 m9 s9 p-3">
                            {{ ($key + 1) }}
                        </div>
                        <div class="col l3 m3 s3 p-3">
                            Kwitansi
                        </div>
                        <div class="col l9 m9 s9 p-3">
                            {{ $row->marketingOrderReceipt->code }}
                        </div>
                        <div class="col l3 m3 s3 p-3">
                            Customer
                        </div>
                        <div class="col l9 m9 s9 p-3">
                            {{ $row->marketingOrderReceipt->account->name }}
                        </div>
                        <div class="col l3 m3 s3 p-3">
                            Alamat
                        </div>
                        <div class="col l9 m9 s9 p-3">
                            {{ $row->marketingOrderReceipt->account->address }}
                        </div>
                        <div class="col l3 m3 s3 p-3">
                            Tgl.Post
                        </div>
                        <div class="col l9 m9 s9 p-3">
                            {{ date('d/m/y',strtotime($row->marketingOrderReceipt->post_date)) }}
                        </div>
                        <div class="col l3 m3 s3 p-3">
                            Tagihan
                        </div>
                        <div class="col l9 m9 s9 p-3">
                            {{ number_format($row->marketingOrderReceipt->grandtotal,2,',','.') }}
                        </div>
                        <div class="col l3 m3 s3 p-3">
                            Kembali Ke
                        </div>
                        <div class="col l9 m9 s9 p-3">
                            <div class="switch">
                                <label>
                                  Admin Penagihan
                                  <input type="checkbox" name="arr_receipt[]" data-id="{{ $row->id }}" value="1" {{ $data->status_tracking ? 'disabled' : '' }} {{ $row->status == '2' ? 'checked' : '' }}>
                                  <span class="lever"></span>
                                  Customer
                                </label>
                            </div>
                        </div>
                        <div class="col l3 m3 s3 p-3">
                            Keterangan
                        </div>
                        <div class="col l9 m9 s9 p-3">
                            <input id="note{{ $row->id }}" name="arr_note[]" type="text" value="{{ $row->note }}" {{ $data->status_tracking ? 'disabled' : '' }}>
                        </div>
                    </div>
                    @endforeach
                    @if(!$data->status_tracking)
                    <div class="row">
                        <div class="col l12 m12 s12 p-3 right-align">
                            <button class="btn waves-effect waves-light right" onclick="save();">Simpan <i class="material-icons right">send</i></button>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="content-overlay"></div>
        </div>
    </div>
</div>
<script>
    $(function() {
        M.updateTextFields();
    });

    function save(){
        swal({
            title: "Apakah anda yakin?",
            text: "Anda tidak bisa mengembalikan data yang sudah terupdate!",
            icon: 'warning',
            dangerMode: true,
            buttons: {
                cancel: 'Tidak, jangan!',
                delete: 'Ya, lanjutkan!'
            }
        }).then(function (willApprove) {
            if (willApprove) {
                let arr_status = [], arr_receipt = [], arr_note = [];
                $('input[name^="arr_receipt[]"]').each(function(index){
                    arr_status.push(($(this).is(':checked') ? '2' : '1'));
                    arr_receipt.push($(this).data('id'));
                    arr_note.push($('input[name^="arr_note[]"]').eq(index).val());
                });
                $.ajax({
                    url: '{{ Request::url() }}/courier_update',
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        code: '{{ $code }}',
                        arr_status: arr_status,
                        arr_receipt: arr_receipt,
                        arr_note: arr_note,
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
                                html: response.message,
                                completeCallback: function(){
                                    success();
                                }
                            });
                        } else {
                            M.toast({
                                html: response.message
                            });
                        }
                    },
                    error: function(response) {
                        $('#main').scrollTop(0);
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

    function success(){
        location.reload();
    }
</script>