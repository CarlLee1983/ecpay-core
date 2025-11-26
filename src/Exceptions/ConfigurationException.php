<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Exceptions;

/**
 * 設定錯誤例外。
 *
 * 當 SDK 設定不正確時拋出。
 */
class ConfigurationException extends EcPayException
{
    /**
     * 缺少必要設定項。
     *
     * @param string $key 設定鍵名
     * @return static
     */
    public static function missingConfig(string $key): static
    {
        return new static(
            sprintf('缺少必要的設定項：%s', $key),
            0,
            null,
            ['missing_key' => $key]
        );
    }

    /**
     * 設定值無效。
     *
     * @param string $key 設定鍵名
     * @param mixed $value 無效的值
     * @param string $reason 原因說明
     * @return static
     */
    public static function invalidValue(string $key, mixed $value, string $reason = ''): static
    {
        $message = sprintf('設定項 %s 的值無效', $key);
        if ($reason !== '') {
            $message .= '：' . $reason;
        }

        return new static(
            $message,
            0,
            null,
            [
                'key' => $key,
                'value' => $value,
                'reason' => $reason,
            ]
        );
    }
}
