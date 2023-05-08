	<!-- BEGIN: Footer-->

    <footer class="page-footer footer footer-static footer-light navbar-border navbar-shadow">
        <div class="footer-copyright">
            <div class="container"><span>&copy; 2023 <a href="https://blessconbataringan.com" target="_blank">BLESSCON</a> All rights reserved.</span><span class="right hide-on-small-only">Design and Developed by <a href="https://blessconbataringan.com">BLESSCON</a></span></div>
        </div>
    </footer>

    <!-- END: Footer-->
    
	<script src="{{ url('app-assets/vendors/data-tables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ url('app-assets/vendors/data-tables/extensions/responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ url('app-assets/vendors/data-tables/js/dataTables.select.min.js') }}"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>
    <script src="{{ url('app-assets/vendors/formatter/jquery.formatter.min.js?v=2') }}"></script>
	<script src="{{ url('app-assets/vendors/quill/katex.min.js') }}"></script>
    <script src="{{ url('app-assets/vendors/quill/highlight.min.js') }}"></script>
	<script src="{{ url('app-assets/vendors/quill/quill.min.js') }}"></script>
	<script src="{{ url('app-assets/vendors/sweetalert/sweetalert.min.js') }}"></script>
	<script src="{{ url('app-assets/vendors/select2/select2.full.min.js') }}"></script>
    <script src="{{ url('app-assets/js/custom/waitMe.min.js') }}"></script>
    <script src="{{ url('app-assets/js/plugins.js?v=7') }}"></script>
    <script src="{{ url('app-assets/js/search.js?v=11') }}"></script>
    <script src="{{ url('app-assets/js/custom/custom-script.js?v=68') }}"></script>
    
    <script src="{{ url('app-assets/js/custom/go-chart.js') }}"></script>
    @if(session('bo_id'))
        <script>
            $(function() {
                cekNotif('{{ URL::to('/') }}');
                setInterval(function () {
                    cekNotif('{{ URL::to('/') }}');
                },5000);
                $('.tooltipped').tooltip();
                checkPageMaintenance('{{ URL::to('/') }}');
            });
        </script>
    @endif
</body>

</html>