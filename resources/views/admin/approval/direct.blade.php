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
                        <div class="col m12 s12" id="body_show" style="border-style: solid;border-color: coral;min-height:50vh;max-height:70vh;overflow:auto;width:100%;zoom:0.7;">

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
</script>