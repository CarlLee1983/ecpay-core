<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Contracts;

/**
 * Payload 編碼器介面。
 *
 * 定義 API 請求/回應資料的編碼與解碼行為。
 * 不同的 API 類型（電子發票、金流、物流）可實作各自的編碼邏輯。
 *
 * @example 電子發票使用 AES 加密
 * @example 金流/物流使用 CheckMacValue 簽章
 */
interface PayloadEncoderInterface
{
    /**
     * 編碼請求 Payload。
     *
     * 將原始 payload 資料轉換為 API 要求的傳輸格式。
     *
     * @param array<string, mixed> $payload 原始 payload 資料
     * @return array<string, mixed> 編碼後的 payload
     */
    public function encodePayload(array $payload): array;

    /**
     * 解碼回應資料。
     *
     * 將 API 回傳的編碼資料還原為原始格式。
     *
     * @param string $encodedData 編碼後的資料
     * @return array<string, mixed> 解碼後的資料
     */
    public function decodeData(string $encodedData): array;

    /**
     * 驗證回應資料的完整性。
     *
     * 檢查資料是否被竄改（如驗證 CheckMacValue 或解密成功）。
     *
     * @param array<string, mixed> $responseData 回應資料
     * @return bool 驗證是否通過
     */
    public function verifyResponse(array $responseData): bool;
}
