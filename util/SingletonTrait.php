<?php

/**
 * Trait SingletonTrait
 *
 * @author A Vijay<mailvijay.vj@gmail.com>
 */
trait SingletonTrait
{

    /**
     * @var null
     */
    private static $_instance;

    /**
     * @param bool $refresh
     * @return self
     */
    public static function instance($refresh = false): self
    {
        if (self::$_instance === null || $refresh) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}