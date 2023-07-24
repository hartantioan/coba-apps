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
</style>
<div id="main">
    <div class="row">
        <div class="pt-3 pb-1" id="breadcrumbs-wrapper">
            <div class="container">
                <div class="row">
                    <div class="col s8 m6 l6">
                        <h6 class="breadcrumbs-title mt-3 mb-0"><span>{{ $title }}</span></h6>
                    </div>
                    <div class="col s4 m6 l6">
                        
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12">
            <div class="container">
                <div class="section section-data-tables">
                    <!-- DataTables example -->
                    <div class="row">
                        <div class="col m12 s12" id="body_show" style="border-style: solid;border-color: coral;min-height:50vh;max-height:70vh;overflow:auto;width:100%;zoom:0.80;">

                        </div>
                        @if($status == '1')
                            <div class="col m12 s12 mt-3 center-align input-field">
                                <textarea class="materialize-textarea" id="reason" name="reason" placeholder="Tulis alasan anda disini. (Wajib diisi)"></textarea>
                            </div>
                            <div class="col m4 s4 mt-2 center-align">
                                <a class="mb-6 btn-floating btn-large waves-effect waves-light gradient-45deg-green-teal gradient-shadow center" href="javascript:void(0);" onclick="save('1')">
                                    <i class="material-icons">check</i>
                                </a>
                                <div>SETUJU</div>
                            </div>
                            <div class="col m4 s4 mt-2 center-align">
                                <a class="mb-6 btn-floating btn-large waves-effect waves-light gradient-45deg-amber-amber gradient-shadow center" href="javascript:void(0);" onclick="save('3')">
                                    <i class="material-icons">create</i>
                                </a>
                                <div>REVISI</div>
                            </div>
                            <div class="col m4 s4 mt-2 center-align">
                                <a class="mb-6 btn-floating btn-large waves-effect waves-light gradient-45deg-purple-deep-orange gradient-shadow center" href="javascript:void(0);" onclick="save('2')">
                                    <i class="material-icons">clear</i>
                                </a>
                                <div>TOLAK</div>
                            </div>
                        @else
                            <div class="col m12 mt-2">
                                Dokumen ini telah anda <b>{{ $approval->statusApproval() }}</b> pada tanggal <b>{{ date('d/m/y H:i:s',strtotime($approval->date_process)) }}</b> dengan keterangan : <b>{{ $approval->note }}</b>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="content-overlay"></div>
        </div>
    </div>
</div>
<script>
    $(function() {
        M.updateTextFields();
        $.ajax({
            url:'{{ $url }}',
            type:'GET',
            beforeSend: function() {
                loadingOpen('#main');
            },
            complete: function() {
                
            },
            success: function(data){
                loadingClose('#main');
                $('#body_show').html(data);
            },
            statusCode: {
                403: function() { 
                    swal({
                        title: 'Ups! Anda tidak memiliki akses.',
                        text: 'Anda tidak dapat mengakses halaman ini. Silahkan hubungi tim terkait.',
                        icon: 'warning'
                    });
                    loadingClose('#main');
                },
            }
        });
    });

    @if($status == '1')
        function save(type){
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
                    if($('#reason').val()){
                        $.ajax({
                            url: '{{ URL::to("/") }}/admin/approval/approve',
                            type: 'POST',
                            dataType: 'JSON',
                            data: {
                                approve_reject_revision: type,
                                note: $('#reason').val(),
                                temp: '{{ Request::get("c") }}',
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
                                    success();
                                    M.toast({
                                        html: response.message
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
                    }else{
                        M.toast({
                            html: 'Alasan/keterangan tidak boleh kosong.'
                        });
                    }
                }
            });
        }

        function success(){
            location.reload();
        }
    @endif
</script>