<?php
declare (strict_types=1);

namespace PXCommon\IDGen;

class Number
{
    /**
     * 高精度计算
     * @param mixed $n1
     * @param string $symbol
     * @param mixed $n2
     * @param int $scale
     * @return string|null
     */
    public static function hpCalc(mixed $n1, string $symbol, mixed $n2, int $scale = 2): ?string
    {
        $n1 = (string)$n1;
        $n2 = (string)$n2;
        return match ($symbol) {
            "+" => bcadd($n1, $n2, $scale),
            "-" => bcsub($n1, $n2, $scale),
            "*" => bcmul($n1, $n2, $scale),
            "/" => bcdiv($n1, $n2, $scale),
            "%" => bcmod($n1, $n2, $scale),
            default => "",
        };
    }
}