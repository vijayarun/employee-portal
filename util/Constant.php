<?php

require_once __DIR__ . '/Helper.php';

/**
 * Class Constant
 *
 * @author  A Vijay <mailvijay.vj@gmail.com>
 */
class Constant
{
    public const IMPORT_STATUS_PENDING = 1;
    public const IMPORT_STATUS_INITIALIZED = 2;
    public const IMPORT_STATUS_PROCESSED = 3;

    /**
     * @return array
     */
    public static function getImportStatusList(): array
    {
        return [
            self::IMPORT_STATUS_PENDING => 'Pending',
            self::IMPORT_STATUS_INITIALIZED => 'Initialized',
            self::IMPORT_STATUS_PROCESSED => 'Processed',
        ];
    }

    /**
     * @param $type
     * @return mixed|null
     */
    public static function getImportStatusLabel($type)
    {
        return Helper::getArrayValue(self::getImportStatusList(), $type, '-');
    }
}