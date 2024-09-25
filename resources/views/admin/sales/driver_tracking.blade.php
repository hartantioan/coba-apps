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
                        <div class="col l2 m6 s6 p-3 center-align">
                            <div class="card z-depth-0 grey lighten-4 border-radius-6">
                                <div class="card-image">
                                    <img src="{{ url('website/arrive.png') }}" class="responsive-img" id="imageTracking3" alt="" style="filter:{{ in_array('3',$arrTracking) ? '' : 'grayscale(100%)' }};">
                                </div>
                                <div class="card-content center-align">
                                    Barang tiba di customer.
                                    <p class="teal-text lighten-2 truncate" id="dateTracking3">-</p>
                                </div>
                            </div>
                            @if(!in_array('3',$arrTracking))
                            <a class="mb-6 btn-floating btn-large waves-effect waves-light gradient-45deg-green-teal gradient-shadow center" href="javascript:void(0);" onclick="save('3')">
                                <i class="material-icons">check</i>
                            </a>
                            @endif
                        </div>
                        
                        <div class="col l12 m12 s12 p-3 center-align">
                            Silahkan tekan tanda centang untuk update tracking.
                        </div>
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
    });

    function save(status){
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
                $.ajax({
                    url: '{{ Request::url() }}/driver_update',
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        status: status,
                        code: '{{ $code }}',
                        driver: '{{ $driver }}',
                        phone: '{{ $phone }}',
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