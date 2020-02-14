<?php

/**
 * Class Helper
 *
 * @author  A Vijay <mailvijay.vj@gmail.com>
 */
class Helper
{
    /**
     * @param array $array
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public static function getArrayValue(array $array, $key, $default = null)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }
        return $default;
    }

    /**
     * @param $route
     * @param array $params
     * @return string
     */
    public static function url($route, array $params = []): string
    {
        $queryString = '';
        if ($params !== []) {
            $queryString = '?' . http_build_query($params);
        }

        return sprintf('%s%s', ltrim($route, '/'), $queryString);
    }

    /**
     * @return string
     */
    public static function uuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * @param int $length
     * @return string
     */
    public static function generateRandomString($length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}