<?php
require_once '_head.php';
require_once 'util/DBHelper.php';
require_once 'util/Helper.php';
require_once 'util/Constant.php';

$dbHelper = DBHelper::instance();

$stmt = $dbHelper->prepare(
    sprintf('SELECT import_log_json FROM %s WHERE import_key = ?', $dbHelper::TABLE_IMPORT),
    [Helper::getArrayValue($_GET, 'id')]
);

$model = $stmt->fetch(PDO::FETCH_ASSOC);

$logs = Helper::getArrayValue($model, 'import_log_json', '[]');
$logs = json_decode($logs, true, 512, JSON_THROW_ON_ERROR);
$logs = Helper::getArrayValue($logs, 'errors', []);
?>
<!-- /.navbar -->

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Import Management - Log</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/">Home</a></li>
                        <li class="breadcrumb-item"><a href="/import">Import Management</a></li>
                        <li class="breadcrumb-item active">Log</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h5 class="card-title m-0">Import Log</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>Row No.</th>
                                    <th>Column</th>
                                    <th>Message</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                if (count($logs) > 0) {
                                    foreach ($logs as $log) {
                                        $columns = [];
                                        $columns[] = '<td class="text-center">' . Helper::getArrayValue($log, 'row', '-') . '</td>';
                                        $columns[] = '<td class="text-center">' . Helper::getArrayValue($log, 'column', '-') . '</td>';
                                        $columns[] = '<td class="text-center">' . Helper::getArrayValue($log, 'msg', '-') . '</td>';
                                        echo '<tr>' . implode('', $columns) . '</tr>';
                                    }
                                } else {
                                    echo '<tr class="table-danger"><td colspan="7" class="text-center">No Records found!</td></tr>';
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    runOnLoad(function () {
        $("#import-form").validate({
            rules: {
                import_file: {
                    required: true,
                    accept: 'text/csv'
                }
            },
            submitHandler: function (form) {
                let formData = new FormData(form), $btn = $(form).find('[type="submit"]');
                $.ajax({
                    url: '<?= Helper::url('import-controller', ['action' => 'upload']) ?>',
                    method: 'POST',
                    processData: false,
                    contentType: false,

                    data: formData,
                    beforeSend: function () {
                        $btn.data('data-text', $btn.text())
                            .prop('disabled', true)
                            .html('<span class="spinner-grow spinner-grow-sm"></span>&nbsp; Uploading ...');
                    },
                }).done(function () {
                    console.log('done');
                }).always(function () {
                    setTimeout(function () {
                        $btn.prop('disabled', false)
                            .text($btn.data('data-text'));
                    }, 1000);
                });
            }
        });
    });
</script>
<?php require_once '_footer.php' ?>
