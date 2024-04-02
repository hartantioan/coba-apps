	<!-- BEGIN: Footer-->

    <footer class="page-footer footer footer-static footer-light navbar-border navbar-shadow">
        <div class="footer-copyright">
            <div class="container"><span>&copy; 2023 <a href="https://superiorporcelain.co.id" target="_blank">{{ env('APP_NAME') }}</a> All rights reserved. <a href="{{ url('admin/application_update') }}" id="version-app">...</a></span><span class="right hide-on-small-only">Design and Developed by <a href="https://blessconbataringan.com">EDP - BLESSCON</a></span></div>
        </div>
    </footer>

    <!-- END: Footer-->
    
	<script src="{{ url('app-assets/vendors/data-tables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ url('app-assets/vendors/data-tables/extensions/responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ url('app-assets/vendors/data-tables/js/dataTables.select.min.js') }}"></script>
    <script src="{{ url('app-assets/vendors/data-tables/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ url('app-assets/vendors/data-tables/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ url('app-assets/js/dataTables.fixedColumns.min.js?v=0') }}"></script>
    <script src="{{ url('app-assets/vendors/formatter/jquery.formatter.min.js?v=2') }}"></script>
	<script src="{{ url('app-assets/vendors/quill/katex.min.js') }}"></script>
    <script src="{{ url('app-assets/vendors/quill/highlight.min.js') }}"></script>
	<script src="{{ url('app-assets/vendors/quill/quill.min.js') }}"></script>
	<script src="{{ url('app-assets/vendors/sweetalert/sweetalert.min.js') }}"></script>
	<script src="{{ url('app-assets/vendors/select2/select2.full.min.js') }}"></script>
    <script src="{{ url('app-assets/js/custom/waitMe.min.js') }}"></script>
    <script src="{{ url('app-assets/js/plugins.js?v=7') }}"></script>
    <script src="{{ url('app-assets/js/search.js?v=11') }}"></script>
    <script src="{{ url('app-assets/vendors/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ url('app-assets/js/custom/custom-script.js?v=128') }}"></script>
    
    <script src="{{ url('app-assets/js/custom/go-chart.js') }}"></script>
    @if(session('bo_id'))
        <script>
            $(function() {
                cekNotif('{{ URL::to('/') }}');
                setInterval(function () {
                    cekNotif('{{ URL::to('/') }}');
                },25000);
                $('.tooltipped').tooltip();
                /* checkPageMaintenance('{{ URL::to('/') }}'); */
                var sessionLifetime = {{  config('session.lifetime') }};
                
                function resetSession() {
                    fetch('/admin/flush-session', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}', // Add CSRF token if CSRF protection is enabled
                        },
                        body: JSON.stringify({}),
                    })
                    .then(response => {
                        if (response.ok) {
                            console.log('Session flushed successfully');
                        } else {
                            console.error('Failed to flush session');
                        }
                    })
                    .catch(error => {
                        console.error('An error occurred:', error);
                    });
                    location.reload();
                }
                function trackActivity() {
                    setTimeout(resetSession, sessionLifetime * 60 * 1000);
                }

                document.addEventListener("mousemove", trackActivity);
                document.addEventListener("keypress", trackActivity);

                trackActivity();
            });
        </script>
    @endif
</body>

</html>