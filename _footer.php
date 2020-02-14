            <!-- Main Footer -->
            <footer class="main-footer">
                <!-- To the right -->
                <div class="float-right d-none d-sm-inline">
                    &nbsp;
                </div>
                <!-- Default to the left -->
                <strong>Copyright &copy; <?= date('Y') ?> <a href="http://localhost">Mobiotics</a>.</strong> All rights reserved.
            </footer>
        </div>
        <!-- ./wrapper -->

        <!-- REQUIRED SCRIPTS -->

        <!-- jQuery -->
        <script src="/assets/js/jquery.min.js"></script>
        <!-- Bootstrap 4 -->
        <script src="/assets/js/bootstrap.bundle.min.js"></script>
        <script src="/assets/js/jquery.validate.min.js"></script>
        <script src="/assets/js/additional-methods.min.js"></script>
        <script src="/assets/js/jquery.simplePagination.js"></script>
        <!-- AdminLTE App -->
        <script src="/assets/js/adminlte.min.js"></script>

        <script type="text/javascript">
            $(document).ready(function(){
                $.validator.setDefaults({
                    errorElement: 'span',
                    errorPlacement: function (error, element) {
                        error.addClass('invalid-feedback');
                        element.closest('.form-group').append(error);
                    },
                    highlight: function (element, errorClass, validClass) {
                        $(element).addClass('is-invalid');
                    },
                    unhighlight: function (element, errorClass, validClass) {
                        $(element).removeClass('is-invalid');
                    }
                });

                exeOnLoad.apply(arguments);
            });
        </script>
    </body>
</html>