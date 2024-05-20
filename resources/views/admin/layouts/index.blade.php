@include('admin.layouts.head', $data)
@include('admin.layouts.header', $data)
@include('admin.layouts.sidebar', $data)
@include($data['content'], $data)
@include('admin.layouts.footer', $data)

<div id="modal_reminder" class="modal modal-fixed-footer" style="max-height: 100% !important;height: 80% !important;width:80% !important;background:#fee13e;">
    <div class="modal-content">
        <div class="row">
                <div class="row" style="display: flex; flex-direction: column; justify-content: center; align-items: center; height: 10vh;">
                    <i class="material-icons dp48" style="color: #f50808; font-size: 14vh;">error</i>
                    <div style="text-align: center;">
                    <p style="margin: 0;">UNDONE TASK</p>
                    </div>
                </div>
                <table id="table_reminder" >
                    <thead>
                        <tr>
                            
                            <th>Nama</th>
                            <th>Keterangan</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Akhir</th>
                            <th>Umur</th>
                            <th>Limit</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="body-reminder"></tbody>
                </table>
               
            
        </div>
    </div>
    <div class="modal-footer" style="background: #fee13e">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>
<script>
 $(function() {
    $('#modal_reminder').modal({
        dismissible: false,
        onOpenStart: function(modal,trigger) {
            
        },
        onOpenEnd: function(modal, trigger) { 
            $('#name').focus();
            $('#validation_alert').hide();
            $('#validation_alert').html('');
            M.updateTextFields();
        },
        onCloseEnd: function(modal, trigger){
           
       
            M.updateTextFields();
        }
    });
    let reminder = {!! json_encode(session('bo_reminder')) !!};
    if (reminder!=null && Array.isArray(reminder) && reminder.length > 0) {
        
        loadDataTableReminder();
        $('#modal_reminder').modal('open');
    }
   
 });

 function loadDataTableReminder(){
    $.ajax({
        url: `${window.location.origin}/admin/reminder`,
        type: 'POST',
        dataType: 'JSON',
        data: {},
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            loadingOpen('.modal-content');
        },
        success: function(response) {
            loadingClose('.modal-content');

            if(response.length > 0){
                console.log(response);
                $.each(response, function(i, val) {
                    
                    $('#body-reminder').append(`
                        <tr>
                            <td>` + val.name + `</td>
                            <td>` + val.note + `</td>
                            <td>` + val.start_date + `</td>
                            <td>` + val.end_date + `</td>
                            <td>` + val.age + `</td>
                            <td>` + val.age_limit_reminder + `</td>
                            <td>` + val.status + `</td>
                            <td>` + val.button + `</td>
                        </tr>
                    `);
                });
            }

            table_multi = $('#table_reminder').DataTable({
                "responsive": true,
                scrollY: '50vh',
                scrollCollapse: true,
                "iDisplayInLength": 10,
                dom: 'Blfrtip',
              
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
                    
                    
                },
                
              
            });
            $('#table_reminder_wrapper > .dt-buttons').appendTo('#datatable_buttons_multi');
            $('select[name="table_reminder_length"]').addClass('browser-default');
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

function show_one_time(id){
    var baseUrl = window.location.origin;
    var redirectUrl = baseUrl + "/admin/personal/task?code=" + id;
    window.location.href = redirectUrl;    
}
function destroy_one_time(id){
    swal({
        title: "Apakah anda yakin?",
        text: "Anda tidak bisa mengembalikan data yang terhapus!",
        icon: 'warning',
        dangerMode: true,
        buttons: {
        cancel: 'Tidak, jangan!',
        delete: 'Ya, lanjutkan!'
        }
    }).then(function (willDelete) {
        if (willDelete) {
            $.ajax({
                url: `${window.location.origin}/admin/task/destroy`,
                type: 'POST',
                dataType: 'JSON',
                data: { id : id },
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
document.addEventListener('DOMContentLoaded', function() {
    const sidenav = document.getElementById('sidenav');
    const fixedActionBtn = document.getElementById('announcement_div');

    function adjustButtonPosition() {
        const sidenavWidth = sidenav.offsetWidth;
        fixedActionBtn.style.left = `calc(${sidenavWidth}px + 19px)`;
    }

    
    adjustButtonPosition();

   
    window.addEventListener('resize', adjustButtonPosition);


    document.querySelector('.sidenav-main').addEventListener('transitionend', adjustButtonPosition);
});

</script>