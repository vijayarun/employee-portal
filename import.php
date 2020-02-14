<?php
require_once '_head.php';
require_once 'util/DBHelper.php';
require_once 'util/Helper.php';
require_once 'util/Constant.php';

$dbHelper = DBHelper::instance();

$stmt = $dbHelper->prepare(
    sprintf('SELECT COUNT(import_id) AS totalRecords FROM %s', $dbHelper::TABLE_IMPORT)
);

$model = $stmt->fetch(PDO::FETCH_ASSOC);

$models = [];
$count = (int)Helper::getArrayValue($model, 'totalRecords', 0);
$page = (int)Helper::getArrayValue($_GET, 'page', 1);
$totalPage = 1;
$perPage = 20;

if ($count !== 0) {
    $totalPage = ceil($count / $perPage);

    $totalPage = $totalPage === 0 ? 1 : $totalPage;

    if ($page === 1 || $page === 0) {
        $offset = 0;
    } else {
        $offset = ($page - 1) * $perPage;
    }

    $stmt = $dbHelper->prepare(
        sprintf('SELECT * FROM %s ORDER BY import_id DESC LIMIT %d, %d', $dbHelper::TABLE_IMPORT, $offset, $perPage),
        );

    $models = $stmt->fetchAll(PDO::FETCH_ASSOC);

}
?>
<!-- /.navbar -->

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Import Management</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/">Home</a></li>
                        <li class="breadcrumb-item active">Import Management</li>
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
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title m-0">
                                Upload

                            </h5>
                            <a href="<?= Helper::url('import-controller', ['action' => 'download']) ?>"
                               class="float-right"
                               target="_blank">
                                Download
                            </a>
                        </div>
                        <div class="card-body">

                            <div class="col-md-6">
                                <form enctype="multipart/form-data" id="import-form">
                                    <div class="form-group">
                                        <label for="exampleInputFile">Import File <small>(*.csv)</small></label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" name="import_file" class="custom-file-input">
                                                <label class="custom-file-label">Choose file</label>
                                            </div>

                                            <div class="input-group-append">
                                                <button type="submit" class="input-group-text">
                                                    Upload
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>

                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h5 class="card-title m-0">Import History</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>SLNo.</th>
                                    <th>Import Status</th>
                                    <th>Imported At</th>
                                    <th>Total Rows</th>
                                    <th>Success</th>
                                    <th>Failure</th>
                                    <th>&nbsp;</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                if ($count > 0) {
                                    $no = ($perPage * ($page - 1)) + 1;
                                    foreach ($models as $model) {

                                        $url = Helper::url(
                                            'import-view',
                                            ['id' => Helper::getArrayValue($model, 'import_key')]
                                        );

                                        $log = Helper::getArrayValue($model, 'import_log_json');
                                        $log = json_decode($log, true, 512, JSON_THROW_ON_ERROR);
                                        $columns = [];

                                        $columns[] = "<td>$no</td>";
                                        $columns[] = '<td>' . Constant::getImportStatusLabel(Helper::getArrayValue($model, 'import_status')) . '</td>';
                                        $columns[] = '<td>' . date(
                                                'd M Y h:i A',
                                                strtotime(Helper::getArrayValue($model, 'created_at'))
                                            ) . '</td>';
                                        $columns[] = '<td class="text-center">' . Helper::getArrayValue($log, 'totalRow', '-') . '</td>';
                                        $columns[] = '<td class="text-center">' . Helper::getArrayValue($log, 'success', '-') . '</td>';
                                        $columns[] = '<td class="text-center">' . Helper::getArrayValue($log, 'failure', '-') . '</td>';
                                        $columns[] = '<td class="text-center"><a class="btn btn-sm btn-primary" href="' . $url . '">View</a></td>';
                                        echo '<tr>' . implode('', $columns) . '</tr>';
                                        $no++;
                                    }
                                } else {
                                    echo '<tr class="table-danger"><td colspan="7" class="text-center">No Records found!</td></tr>';
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="card-footer clearfix">
                            <?php if ($count > 0):
                                $maxButtonCount = 10;
                                $beginPage = max(0, $page - (int)($maxButtonCount / 2));
                                if (($endPage = $beginPage + $maxButtonCount - 1) >= $totalPage) {
                                    $endPage = $totalPage - 1;
                                    $beginPage = max(0, $endPage - $maxButtonCount + 1);
                                }


                                ?>
                                <ul class="pagination pagination-sm m-0 float-right">
                                    <li class="page-item">
                                        <a class="page-link" href="<?= Helper::url('import', ['page' => 1]) ?>">First</a>
                                    </li>
                                    <?php for ($i = $beginPage; $i <= $endPage; $i++): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= Helper::url('import', ['page' => $i + 1]) ?>">
                                                <?= $i + 1 ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= Helper::url('import', ['page' => $totalPage]) ?>">Last</a>
                                    </li>
                                </ul>

                            <?php endif; ?>
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
                }).done(function (json) {
                    json = JSON.parse(json);
                    alert(json.msg);
                    if (parseInt(json.status) === 1) {
                        window.location.reload();
                    }
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
