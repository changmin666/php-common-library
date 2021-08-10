<?php
declare (strict_types=1);

namespace PXCommon\StringUtil;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

class StringUtil
{
    const PHONE_NUMBER = 1;

    /**
     * 字符串脱敏
     * @param string $str
     * @param int $type
     * @return string
     */
    public static function remSensitive(string $str, int $type = 0): string
    {
        $result = '';
        switch ($type) {
            case  self::PHONE_NUMBER :
                $result = self::remPhoneSensitive($str);
                break;
            default:
        }
        return $result;
    }

    /**
     * 字符串前缀判断
     * @param string $str
     * @param string $prefix
     * @return bool
     */
    public static function hasPrefix(string $str, string $prefix): bool
    {
        $subStr = substr($str, 0, strlen($prefix));
        return $subStr == $prefix;
    }

    /**
     * 手机号码验证（全球）
     * @param string $str
     * @param string $defaultRegion
     * @return bool
     */
    public static function checkPhoneNumber(string $str, string $defaultRegion = 'CN'): bool
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $swissNumberProto = $phoneUtil->parse($str, $defaultRegion);
            if ($phoneUtil->isValidNumber($swissNumberProto)) {
                return true;
            }
        } catch (NumberParseException $e) {

        }
        return false;
    }


    /**
     * 手机号脱敏
     * @param string $str
     * @return string|null
     */
    private static function remPhoneSensitive(string $str): ?string
    {
        if (self::checkPhoneNumber($str)) {
            return preg_replace('/(\d{3})\d{4}(\d{4})/', '$1****$2', $str);
        }
        return $str;
    }

    /**
     * @param string $unclaimedWords
     * @param string $separator
     * @return string
     */
    public static function toCamelize(string $unclaimedWords, string $separator = '_'): string
    {
        $unclaimedWords = $separator . str_replace($separator, " ", strtolower($unclaimedWords));
        return ltrim(str_replace(" ", "", ucwords($unclaimedWords)), $separator);
    }

    /**
     * @param string $camelCaps
     * @param string $separator
     * @return string
     */
    public static function toUnCamelize(string $camelCaps, string $separator = '_'): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }
}