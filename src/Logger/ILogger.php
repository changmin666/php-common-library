<?php
declare (strict_types=1);

namespace PXCommon\Logger;

interface ILogger
{
    public static function info(string $msg, array $context = [], bool $lazy = true);
    public static function debug(string $msg, array $context = [], bool $lazy = true);
    public static function notice(string $msg, array $context = [], bool $lazy = true);
    public static function warning(string $msg, array $context = [], bool $lazy = true);
    public static function error(string $msg, array $context = [], bool $lazy = true);
    public static function critical(string $msg, array $context = [], bool $lazy = true);
    public static function alert(string $msg, array $context = [], bool $lazy = true);
    public static function emergency(string $msg, array $context = [], bool $lazy = true);
    public static function sql(string $msg, array $context = [], bool $lazy = true);

}