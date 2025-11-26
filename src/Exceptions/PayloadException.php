<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Exceptions;

/**
 * Payload 處理錯誤例外。
 *
 * 當 Payload 編碼或解碼過程發生錯誤時拋出。
 */
class PayloadException extends EcPayException
{
    /**
     * JSON 編碼失敗。
     *
     * @param string $error JSON 錯誤訊息
     * @return static
     */
    public static function jsonEncodeFailed(string $error): static
    {
        return new static(
            sprintf('JSON 編碼失敗：%s', $error),
            0,
            null,
            ['json_error' => $error]
        );
    }

    /**
     * JSON 解碼失敗。
     *
     * @param string $error JSON 錯誤訊息
     * @return static
     */
    public static function jsonDecodeFailed(string $error): static
    {
        return new static(
            sprintf('JSON 解碼失敗：%s', $error),
            0,
            null,
            ['json_error' => $error]
        );
    }

    /**
     * 無效的 Payload 結構。
     *
     * @param string $reason 原因說明
     * @return static
     */
    public static function invalidStructure(string $reason = ''): static
    {
        $message = $reason !== ''
            ? sprintf('Payload 結構無效：%s', $reason)
            : 'Payload 結構無效';

        return new static($message, 0, null, ['reason' => $reason]);
    }

    /**
     * 無效的 Payload 資料。
     *
     * @param string $reason 原因說明
     * @return static
     */
    public static function invalidData(string $reason = ''): static
    {
        $message = $reason !== ''
            ? sprintf('Payload 資料格式無效：%s', $reason)
            : 'Payload 資料格式無效';

        return new static($message, 0, null, ['reason' => $reason]);
    }

    /**
     * 缺少必要的 Data 區塊。
     *
     * @return static
     */
    public static function missingData(): static
    {
        return static::invalidStructure('缺少 Data 區塊');
    }
}
