<?php
declare (strict_types=1);

namespace PXCommon\Context;

/**
 * Class Context
 * @package PXCommon\Context
 */
class Context
{
    protected static array $header;

    /**
     * @param string $k
     * @return mixed
     */
    public static function get(string $k = ''): mixed
    {
        if(!$k) {
            return self::$header;
        }else{
            return self::$header[$k] ?? null;
        }
    }

    /**
     * @param $k
     * @param $v
     */
    public static function set($k, $v)
    {
        self::$header[$k] = $v;
    }

    /**
     *
     * @param mixed $k
     * @param mixed $v
     */
    public function __set(mixed $k, mixed $v)
    {
        self::set($k, $v);
    }

    /**
     * @param mixed $k
     * @return mixed
     */
    public function __get(mixed $k)
    {
        return self::get($k);
    }
}