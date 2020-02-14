<?php

require_once __DIR__ . '/util/Helper.php';
require_once __DIR__ . '/util/DBHelper.php';
require_once __DIR__ . '/util/Constant.php';
require_once __DIR__ . '/util/UploadHelper.php';
require_once __DIR__ . '/util/RabbitMQHelper.php';

$action = Helper::getArrayValue($_GET, 'action');

switch ($action) {
    case 'upload':
        $response = ['status' => 0, 'msg' => ''];

        $file = Helper::getArrayValue($_FILES, 'import_file', []);

        if ($file === []) {
            $response['msg'] = 'Required file param is missing';
            goto skip;
        }
        #TODO: Check whether the uploaded file CSV

        $uploadHelper = UploadHelper::instance();
        $uploadHelper->setPath($uploadHelper::TYPE_IMPORT)
            ->setFile($file);

        if (!$uploadHelper->upload()) {
            $response['msg'] = 'Unable to upload the file';
            goto skip;
        }
        $path = $uploadHelper->getPath(true);
        $path = $uploadHelper->getRealPath($path);

        $importKey = Helper::uuid();
        $dbHelper = DBHelper::instance();
        $dbHelper->insert($dbHelper::TABLE_IMPORT, [
            'import_key' => $importKey,
            'import_file_name' => Helper::getArrayValue($file, 'name'),
            'import_file_path' => $path,
            'import_status' => Constant::IMPORT_STATUS_PENDING,
            'import_log_json' => '[]',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $response['status'] = 1;
        $rabbitMQHelper = RabbitMQHelper::instance();
        $rabbitMQHelper->publish($rabbitMQHelper::QUEUE_IMPORT, [
            'import_key' => $importKey
        ]);

        skip:
        echo json_encode($response, JSON_THROW_ON_ERROR, 512);

        break;

    case 'download':
        header( 'Content-Type: text/csv' );
        header( 'Content-Disposition: attachment;filename=import-template.csv');

        $out = fopen('php://output', 'wb');
        fputcsv($out, ['Employee Code', 'Employee Name', 'Email', 'Mobile Number', 'Gender', 'Experience', 'Salary']);
        fclose($out);
        break;
    default:
        break;
}

