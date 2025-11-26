<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Exceptions;

/**
 * 加解密錯誤例外。
 *
 * 當 AES 加解密過程發生錯誤時拋出。
 */
class EncryptionException extends EcPayException
{
    /**
     * 金鑰無效。
     *
     * @param string $keyName 金鑰名稱 (HashKey 或 HashIV)
     * @return static
     */
    public static function invalidKey(string $keyName): static
    {
        return new static(
            sprintf('%s 不得為空', $keyName),
            0,
            null,
            ['key_name' => $keyName]
        );
    }

    /**
     * 加密失敗。
     *
     * @param string $reason 失敗原因
     * @return static
     */
    public static function encryptionFailed(string $reason = ''): static
    {
        $message = 'AES 加密失敗';
        if ($reason !== '') {
            $message .= '：' . $reason;
        }

        return new static($message);
    }

    /**
     * 解密失敗。
     *
     * @param string $reason 失敗原因
     * @return static
     */
    public static function decryptionFailed(string $reason = ''): static
    {
        $message = 'AES 解密失敗';
        if ($reason !== '') {
            $message .= '：' . $reason;
        }

        return new static($message, 0, null, ['reason' => $reason]);
    }
}
