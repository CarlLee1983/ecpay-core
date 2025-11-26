<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Contracts;

/**
 * 所有 Operation/Query 內容的共用介面。
 *
 * 定義 API 操作物件必須實作的基本方法。
 */
interface ContentInterface
{
    /**
     * 取得 API 請求路徑。
     *
     * @return string API 端點路徑
     */
    public function getRequestPath(): string;

    /**
     * 取得 Payload 資料。
     *
     * @return array<string, mixed> 請求的 payload 資料
     */
    public function getPayload(): array;

    /**
     * 取得 Payload 編碼器。
     *
     * @return PayloadEncoderInterface 用於編碼/解碼的編碼器
     */
    public function getPayloadEncoder(): PayloadEncoderInterface;

    /**
     * 設定 HashKey。
     *
     * @param string $key HashKey
     * @return static
     */
    public function setHashKey(string $key): static;

    /**
     * 設定 HashIV。
     *
     * @param string $iv HashIV
     * @return static
     */
    public function setHashIV(string $iv): static;
}
