<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Exceptions;

use Throwable;

/**
 * API 回應錯誤例外。
 *
 * 當綠界 API 回傳非成功狀態或請求失敗時拋出。
 */
class ApiException extends EcPayException
{
    /**
     * 原始回應資料。
     *
     * @var array<string, mixed>|null
     */
    protected ?array $responseData = null;

    /**
     * 建立 API 例外。
     *
     * @param string $message 錯誤訊息
     * @param int $code 錯誤代碼
     * @param array<string, mixed>|null $responseData API 回應資料
     * @param Throwable|null $previous 前一個例外
     * @return static
     */
    public static function make(
        string $message,
        int $code = 0,
        ?array $responseData = null,
        ?Throwable $previous = null
    ): static {
        $exception = new static($message, $code, $previous);
        $exception->responseData = $responseData;

        if ($responseData !== null) {
            $exception->addContext('response', $responseData);
        }

        return $exception;
    }

    /**
     * 從 API 回應建立例外。
     *
     * @param int $rtnCode 回傳代碼
     * @param string $rtnMsg 回傳訊息
     * @param array<string, mixed> $responseData 完整回應資料
     * @return static
     */
    public static function fromResponse(
        int $rtnCode,
        string $rtnMsg,
        array $responseData = []
    ): static {
        return static::make(
            "API 回傳錯誤 [{$rtnCode}]：{$rtnMsg}",
            $rtnCode,
            $responseData
        );
    }

    /**
     * HTTP 請求失敗。
     *
     * @param string $reason 原因說明
     * @param Throwable|null $previous 前一個例外
     * @return static
     */
    public static function requestFailed(string $reason, ?Throwable $previous = null): static
    {
        return static::make("HTTP 請求失敗：{$reason}", 0, null, $previous);
    }

    /**
     * 回應格式無效。
     *
     * @param string $reason 原因說明
     * @return static
     */
    public static function invalidResponse(string $reason = ''): static
    {
        $message = $reason !== ''
            ? "API 回應格式無效：{$reason}"
            : 'API 回應格式無效';

        return static::make($message);
    }

    /**
     * 取得 API 回應資料。
     *
     * @return array<string, mixed>|null
     */
    public function getResponseData(): ?array
    {
        return $this->responseData;
    }

    /**
     * 取得 API 回傳代碼（RtnCode）。
     *
     * @return int|null
     */
    public function getRtnCode(): ?int
    {
        if ($this->responseData === null) {
            return null;
        }

        return isset($this->responseData['RtnCode'])
            ? (int) $this->responseData['RtnCode']
            : null;
    }

    /**
     * 取得 API 回傳訊息（RtnMsg）。
     *
     * @return string|null
     */
    public function getRtnMsg(): ?string
    {
        if ($this->responseData === null) {
            return null;
        }

        return $this->responseData['RtnMsg'] ?? null;
    }
}
