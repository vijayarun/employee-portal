<?php

require_once __DIR__ . '/SingletonTrait.php';
require_once __DIR__ . '/DBHelper.php';
require_once __DIR__ . '/Constant.php';

/**
 * Class ImportHelper
 *
 * @author A Vijay<mailvijay.vj@gmail.com>
 */
class ImportHelper
{
    use SingletonTrait;

    /**
     * @var string|null
     */
    private ?string $importKey;
    /**
     * @var array
     */
    private array $errors = [];
    /**
     * @var string
     */
    private string $message = 'Processed Successfully';
    /**
     * @var int
     */
    private int $success = 0;
    /**
     * @var int
     */
    private int $failure = 0;

    /**
     * @param $message
     * @return ImportHelper
     */
    public function setMessage($message): ImportHelper
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return ImportHelper
     */
    public function addSuccess(): ImportHelper
    {
        $this->success++;
        return $this;
    }

    /**
     * @return $this
     */
    public function addFailure(): self
    {
        $this->failure++;
        return $this;
    }

    /**
     * @param $row
     * @param $column
     * @param $msg
     * @return $this
     */
    public function addError($row, $column, $msg): self
    {
        $this->errors[] = [
            'row' => $row,
            'column' => $column,
            'msg' => $msg
        ];

        return $this;
    }

    /**
     * @param string $importKey
     * @return ImportHelper
     */
    public function setImportKey(string $importKey): ImportHelper
    {
        $this->importKey = $importKey;
        return $this;
    }

    /**
     * @return array
     */
    public function prepareLog(): array
    {
        return [
            'success' => $this->success,
            'failure' => $this->failure,
            'totalRow' => $this->success + $this->failure,
            'errors' => $this->errors,
            'message' => $this->message

        ];
    }

    /**
     * @return $this
     */
    public function init(): self
    {
        if ($this->importKey === null) {
            throw new \RuntimeException('Required value importKey is missing');
        }
        $dbHelper = DBHelper::instance();

        $dbHelper->prepare(
            sprintf('UPDATE %s SET import_status = ?, modified_at = ? WHERE import_key = ?', $dbHelper::TABLE_IMPORT),
            [
                Constant::IMPORT_STATUS_INITIALIZED,
                date('Y-m-d H:i:s'),
                $this->importKey,
            ]
        );

        return $this;
    }

    /**
     *
     */
    public function save(): void
    {
        if ($this->importKey === null) {
            throw new \RuntimeException('Required value importKey is missing');
        }

        $dbHelper = DBHelper::instance();

        $dbHelper->prepare(
            sprintf(
                'UPDATE %s SET import_status = ?, import_log_json = ?, modified_at = ? WHERE import_key = ?',
                $dbHelper::TABLE_IMPORT
            ),
            [
                Constant::IMPORT_STATUS_PROCESSED,
                json_encode($this->prepareLog(), JSON_THROW_ON_ERROR, 512),
                date('Y-m-d H:i:s'),
                $this->importKey,
            ]
        );
    }
}