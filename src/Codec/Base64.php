<?php
declare (strict_types=1);

namespace PXCommon\Codec;

class Base64
{

    /**
     * url safe base64 encode
     * @param mixed $data
     * @return array|string
     */
    public static function urlSafeBase64Encode(mixed $data): array|string
    {
        return str_replace(array('+', '/'), array('-', '_'), base64_encode($data));
    }

    /**
     * url safe base64 decode
     * @param mixed $data
     * @return array|string
     */
    public static function urlSafeBase64Decode(mixed $data): array|string
    {
        return base64_decode(str_replace(array('-', '_'), array('+', '/'), $data));
    }
}