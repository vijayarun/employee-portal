<?php
require_once '_head.php';
require_once 'util/DBHelper.php';
require_once 'util/Helper.php';
require_once 'util/Constant.php';

$dbHelper = DBHelper::instance();

$stmt = $dbHelper->prepare(
    sprintf('SELECT COUNT(employee_id) AS totalRecords FROM %s', $dbHelper::TABLE_EMPLOYEE)
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
        sprintf('
            SELECT * FROM %s AS E
            INNER JOIN %s AS EP ON E.employee_id = EP.employee_id  
            ORDER BY employee_name ASC LIMIT %d, %d',
            $dbHelper::TABLE_EMPLOYEE,
            $dbHelper::TABLE_EMPLOYEE_PERSONAL,
            $offset,
            $perPage
        )
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
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h5 class="card-title m-0">Import History</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>SLNo.</th>
                                    <th>Employee Code</th>
                                    <th>Employee Name</th>
                                    <th>Email</th>
                                    <th>Mobile Number</th>
                                    <th>Gender</th>
                                    <th>Experience</th>
                                    <th>Salary</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                if ($count > 0) {
                                    $no = ($perPage * ($page - 1)) + 1;
                                    foreach ($models as $model) {
                                        $salary = number_format(Helper::getArrayValue($model, 'salary', 0), 2);
                                        $experience = Helper::getArrayValue($model, 'experience', 0);
                                        if ($experience === 0) {
                                            $experience = 'Fresher';
                                        } else {
                                            $experience .= ' Months';
                                        }

                                        $columns = [];

                                        $columns[] = '<td class="text-center">' . $no . '</td>';
                                        $columns[] = '<td class="text-center">' . Helper::getArrayValue($model, 'employee_code', '-') . '</td>';
                                        $columns[] = '<td class="text-center">' . Helper::getArrayValue($model, 'employee_name', '-') . '</td>';
                                        $columns[] = '<td class="text-center">' . Helper::getArrayValue($model, 'email', '-') . '</td>';
                                        $columns[] = '<td class="text-center">' . Helper::getArrayValue($model, 'mobile_number', '-') . '</td>';
                                        $columns[] = '<td class="text-center">' . Helper::getArrayValue($model, 'gender', '-') . '</td>';
                                        $columns[] = '<td class="text-center">' . $experience . '</td>';
                                        $columns[] = '<td class="text-center">' . $salary . '</td>';
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
                                        <a class="page-link"
                                           href="<?= Helper::url('', ['page' => 1]) ?>">First</a>
                                    </li>
                                    <?php for ($i = $beginPage; $i <= $endPage; $i++): ?>
                                        <li class="page-item">
                                            <a class="page-link"
                                               href="<?= Helper::url('', ['page' => $i + 1]) ?>">
                                                <?= $i + 1 ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                           href="<?= Helper::url('', ['page' => $totalPage]) ?>">Last</a>
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
<?php require_once '_footer.php' ?>
