<?php


require_once __DIR__ . './../util/Helper.php';
require_once __DIR__ . './../util/RabbitMQHelper.php';
require_once __DIR__ . './../util/DBHelper.php';
require_once __DIR__ . './../util/UploadHelper.php';
require_once __DIR__ . './../util/ImportHelper.php';

use PhpAmqpLib\Message\AMQPMessage;

$rabbitMQHelper = RabbitMQHelper::instance();
$dbHelper = DBHelper::instance();

$process = static function (AMQPMessage $msg) use ($rabbitMQHelper, $dbHelper) {
    $importHelper = ImportHelper::instance(true);

    $msgBody = json_decode($msg->body, true, 512, JSON_THROW_ON_ERROR);

    $importKey = Helper::getArrayValue($msgBody, 'import_key');

    $stmt = $dbHelper->prepare(sprintf('SELECT * FROM %s WHERE import_key = "%s"', $dbHelper::TABLE_IMPORT, $importKey));

    if ($stmt === false) {
        die('Unable to process the query');
    }
    $model = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($model === false) {
        echo "Unable to find the Requested Import Task \n";
        goto ack;
    }

    $importHelper->setImportKey($importKey)->init();

    $uploadHelper = UploadHelper::instance();
    $path = $uploadHelper->getAbsPath(Helper::getArrayValue($model, 'import_file_path'));

    if (!$uploadHelper->check($path, true)) {
        $importHelper->setMessage('Unable to find the Requested Import file');
        echo "Unable to find the Requested Import file \n";
        goto ack;
    }

    $handle = fopen($path, 'rb');
    if ($handle === false) {
        $importHelper->setMessage('Unable to read the import file');
        echo "Unable to read the import file\n";
        goto ack;
    }

    $row = 0;
    while (($data = fgetcsv($handle, 0)) !== FALSE) {
        $row++;
        if ($row === 1) {
            continue;
        }
        $attributes = [];
        foreach ($data as $key => $value) {
            switch ($key) {
                case 0:
                    if (empty($value)) {
                        $importHelper->addError(
                            $row,
                            'Employee Code',
                            'Employee Code is required'
                        );
                        break;
                    }
                    $attributes['employee_code'] = $value;
                    break;
                case 1:
                    if (empty($value)) {
                        $importHelper->addError(
                            $row,
                            'Employee Name',
                            'Employee Name is required'
                        );
                        break;
                    }
                    $attributes['employee_name'] = $value;
                    break;
                case 2:
                    if (empty($value)) {
                        $importHelper->addError(
                            $row,
                            'Email',
                            'Email is required'
                        );
                        break;
                    }
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $importHelper->addError(
                            $row,
                            'Email',
                            'Invalid Email Address'
                        );
                        break;
                    }
                    $attributes['email'] = $value;
                    break;
                case 3:
                    if (!empty($value) && !preg_match('/^[0-9]{6,10}+$/', $value)) {
                        $importHelper->addError(
                            $row,
                            'Mobile Number',
                            'Invalid Mobile Number'
                        );
                        break;
                    }
                    $attributes['mobile_number'] = $value;
                    break;
                case 4:
                    if (empty($value)) {
                        $importHelper->addError(
                            $row,
                            'Gender',
                            'Gender is required'
                        );
                        break;
                    }
                    if (!in_array(strtolower($value), ['male', 'female'], true)) {
                        $importHelper->addError(
                            $row,
                            'Gender',
                            'Invalid Gender'
                        );
                    }
                    $attributes['gender'] = strtoupper($value);
                    break;
                case 5:
                    if (empty($value)) {
                        $value = 0;
                    }
                    if (!filter_var($value, FILTER_VALIDATE_INT)) {
                        $importHelper->addError(
                            $row,
                            'Experience',
                            'Invalid Experience value'
                        );
                    }
                    $attributes['experience'] = $value;
                    break;
                case 6:
                    if (empty($value)) {
                        $importHelper->addError(
                            $row,
                            'Salary',
                            'Salary is required'
                        );
                        break;
                    }
                    if (!filter_var($value, FILTER_VALIDATE_INT)) {
                        $importHelper->addError(
                            $row,
                            'Salary',
                            'Invalid Salary'
                        );
                    }
                    $attributes['salary'] = $value;
                    break;
            }
        }
        if (count($attributes) !== 7) {
            $importHelper->addFailure();
            continue;
        }

        /**
         * Checking whether employee exists or not
         */
        $stmt = $dbHelper->prepare(
            sprintf('SELECT employee_id FROM %s WHERE employee_code = ?', $dbHelper::TABLE_EMPLOYEE),
            [$attributes['employee_code']]
        );

        $modelEmployee = $stmt->fetch(PDO::FETCH_ASSOC);

        $employeeAttribute = array_diff_key($attributes, array_flip(['email', 'mobile_number', 'gender']));
        if ($modelEmployee === false) {
            $employeeAttribute['employee_key'] = Helper::uuid();
            $employeeAttribute['created_at'] = date('Y-m-d H:i:s');

            $employeeId = $dbHelper->insert($dbHelper::TABLE_EMPLOYEE, $employeeAttribute);

        } else {
            $employeeId = Helper::getArrayValue($modelEmployee, 'employee_id');

            $dbHelper->prepare(
                sprintf(
                    'UPDATE %s SET employee_name = ?, experience = ?, salary = ?, modified_at = ? WHERE employee_id = ?',
                    $dbHelper::TABLE_EMPLOYEE
                ),
                [
                    $employeeAttribute['employee_name'],
                    $employeeAttribute['experience'],
                    $employeeAttribute['salary'],
                    date('Y-m-d H:i:s'),
                    $employeeId,
                ]
            );
        }

        /**
         * Checking whether employee Personal details exists or not
         */

        $stmt = $dbHelper->prepare(
            sprintf('SELECT employee_personal_id FROM %s WHERE employee_id = ?', $dbHelper::TABLE_EMPLOYEE_PERSONAL),
            [$employeeId]
        );

        $modelEmpPersonal = $stmt->fetch(PDO::FETCH_ASSOC);

        unset($attributes['employee_name'], $attributes['experience'], $attributes['employee_code'], $attributes['salary']);
        $empPersonalAttribute = $attributes;

        if ($modelEmpPersonal === false) {
            $empPersonalAttribute['employee_id'] = $employeeId;
            $dbHelper->insert($dbHelper::TABLE_EMPLOYEE_PERSONAL, $empPersonalAttribute);
        } else {
            $dbHelper->prepare(
                sprintf(
                    'UPDATE %s SET email = ?, mobile_number = ?, gender = ? WHERE employee_id = ?',
                    $dbHelper::TABLE_EMPLOYEE_PERSONAL
                ),
                [
                    $empPersonalAttribute['email'],
                    $empPersonalAttribute['mobile_number'],
                    $empPersonalAttribute['gender'],
                    $employeeId,
                ]
            );
        }
        $importHelper->addSuccess();
    }

    if ($row <= 1) {
        $importHelper->setMessage('Import file must have one row to process');
    }
    fclose($handle);
    ack:
    echo "Queue Acknowledged\n";
    $importHelper->save();
    $rabbitMQHelper->ack($msg);
};


try {
    $rabbitMQHelper->consume($rabbitMQHelper::QUEUE_IMPORT, $process);
} catch (Exception $exception) {
    die($exception->getMessage());
}