<?php
declare (strict_types=1);

namespace PXCommon\Encrypt;

use Exception;
use PXCommon\Codec\Base64;

class XRsa
{
    /**
     * 公钥
     * @var
     */
    protected mixed $publicKey;

    /**
     * 私钥
     * @var
     */
    protected mixed $privateKey;

    public function __construct(string $publicKey = '', string $privateKey = '')
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    /**
     * 创建密钥对
     * @param int $keySize
     * @return XRsaKeys
     */
    public static function createKeyPair(int $keySize = 2048): XRsaKeys
    {
        $config = [
            'private_key_bits' => $keySize,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'config' => dirname(__FILE__) . '/openssl.cnf',
            'encrypt_key' => false,
        ];
        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $privateKey, '', $config);
        $publicKeyDetail = openssl_pkey_get_details($res);
        $xRsaKeys = new XRsaKeys();
        $xRsaKeys->setPublicKey($publicKeyDetail['key']);
        $xRsaKeys->setPrivateKey($privateKey);
        return $xRsaKeys;
    }

    /**
     * 公钥加密
     * @param string $data
     * @return string
     * @throws Exception
     */
    public function publicEncrypt(string $data): string
    {
        openssl_public_encrypt($data, $encrypted, $this->publicKey);

        if ($encrypted === false) {
            throw new Exception('could not encrypt the data.');
        }
        return Base64::urlSafeBase64Encode($encrypted);
    }

    /**
     * 公钥解密
     * @param string $data
     * @return mixed
     * @throws Exception
     */
    public function publicDecrypt(string $data): string
    {
        openssl_public_decrypt(Base64::urlSafeBase64Decode($data), $decrypted, $this->publicKey, OPENSSL_PKCS1_PADDING);

        if ($decrypted === false) {
            throw new Exception('Could not decrypt the data.');
        }

        return $decrypted;
    }

    /**
     * 私钥加密
     * @param mixed $data
     * @return string
     * @throws Exception
     */
    public function privateEncrypt(string $data): string
    {
        openssl_private_encrypt($data, $encrypted, $this->privateKey,OPENSSL_PKCS1_PADDING);

        if ($encrypted === false) {
            throw new Exception('could not encrypt the data.');
        }

        return Base64::urlSafeBase64Encode($encrypted);
    }

    /**
     * 私钥解密
     * @param mixed $data
     * @return string
     * @throws Exception
     */
    public function privateDecrypt(string $data): string
    {
        openssl_private_decrypt(Base64::urlSafeBase64Decode($data), $decrypted, $this->privateKey);

        if ($decrypted === false) {
            throw new Exception('Could not decrypt the data.');
        }

        return $decrypted;
    }

    /**
     * 签名
     * @param string $data
     * @return string
     * @throws Exception
     */
    public function sign(string $data): string
    {
        openssl_sign($data, $sign, $this->privateKey, OPENSSL_ALGO_SHA256);
        if ($sign === false) {
            throw new Exception('Could not decrypt the data.');
        }
        return Base64::urlSafeBase64Encode($sign);
    }

    /**
     * 验签
     * @param string $data
     * @param string $sign
     * @return bool
     */
    public function verify(string $data, string $sign): bool
    {
        return (bool)openssl_verify($data, Base64::urlSafeBase64Decode($sign), $this->publicKey, OPENSSL_ALGO_SHA256);
    }
}