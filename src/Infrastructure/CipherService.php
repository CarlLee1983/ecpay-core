<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Infrastructure;

use CarlLee\EcPay\Core\Exceptions\EncryptionException;

/**
 * AES 加解密服務。
 *
 * 負責處理綠界 API 所需的 AES-128-CBC 加解密操作。
 */
class CipherService
{
    /**
     * 加密演算法。
     */
    private const CIPHER_ALGORITHM = 'AES-128-CBC';

    /**
     * HashKey。
     *
     * @var string
     */
    private readonly string $hashKey;

    /**
     * HashIV。
     *
     * @var string
     */
    private readonly string $hashIV;

    /**
     * 建立加解密服務。
     *
     * @param string $hashKey HashKey
     * @param string $hashIV HashIV
     * @throws EncryptionException 當金鑰無效時
     */
    public function __construct(string $hashKey, string $hashIV)
    {
        if ($hashKey === '') {
            throw EncryptionException::invalidKey('HashKey');
        }

        if ($hashIV === '') {
            throw EncryptionException::invalidKey('HashIV');
        }

        $this->hashKey = $hashKey;
        $this->hashIV = $hashIV;
    }

    /**
     * 進行 AES/CBC/PKCS7 加密。
     *
     * @param string $data 要加密的資料
     * @return string Base64 編碼的加密結果
     * @throws EncryptionException 當加密失敗時
     */
    public function encrypt(string $data): string
    {
        $encrypted = \openssl_encrypt(
            $data,
            self::CIPHER_ALGORITHM,
            $this->hashKey,
            OPENSSL_RAW_DATA,
            $this->hashIV
        );

        if ($encrypted === false) {
            throw EncryptionException::encryptionFailed();
        }

        return \base64_encode($encrypted);
    }

    /**
     * 進行 AES/CBC/PKCS7 解密。
     *
     * @param string $data Base64 編碼的加密資料
     * @return string 解密後的原文
     * @throws EncryptionException 當解密失敗時
     */
    public function decrypt(string $data): string
    {
        if ($data === '') {
            throw EncryptionException::decryptionFailed('資料為空');
        }

        $decoded = \base64_decode($data, true);
        if ($decoded === false) {
            throw EncryptionException::decryptionFailed('Base64 解碼失敗');
        }

        $decrypted = \openssl_decrypt(
            $decoded,
            self::CIPHER_ALGORITHM,
            $this->hashKey,
            OPENSSL_RAW_DATA,
            $this->hashIV
        );

        if ($decrypted === false) {
            throw EncryptionException::decryptionFailed('AES 解密失敗');
        }

        return $decrypted;
    }
}
