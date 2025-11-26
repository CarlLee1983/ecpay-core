<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Infrastructure;

use CarlLee\EcPay\Core\Contracts\PayloadEncoderInterface;
use CarlLee\EcPay\Core\Exceptions\ApiException;
use CarlLee\EcPay\Core\Exceptions\PayloadException;

/**
 * Payload 編碼器（電子發票用）。
 *
 * 負責將領域資料轉換為綠界電子發票 API 要求的傳輸格式。
 * 使用 AES-128-CBC 加密 Data 區塊。
 */
class PayloadEncoder implements PayloadEncoderInterface
{
    /**
     * 加解密服務。
     *
     * @var CipherService
     */
    private readonly CipherService $cipherService;

    /**
     * 建立 Payload 編碼器。
     *
     * @param CipherService|null $cipherService 加解密服務（可透過 HashKey/IV 自動建立）
     * @param string $hashKey HashKey（當 cipherService 為 null 時使用）
     * @param string $hashIV HashIV（當 cipherService 為 null 時使用）
     */
    public function __construct(
        ?CipherService $cipherService = null,
        string $hashKey = '',
        string $hashIV = ''
    ) {
        if ($cipherService !== null) {
            $this->cipherService = $cipherService;
        } else {
            $this->cipherService = new CipherService($hashKey, $hashIV);
        }
    }

    /**
     * 從 HashKey 和 HashIV 建立編碼器。
     *
     * @param string $hashKey HashKey
     * @param string $hashIV HashIV
     * @return static
     */
    public static function create(string $hashKey, string $hashIV): static
    {
        return new static(new CipherService($hashKey, $hashIV));
    }

    /**
     * 將內容轉成 ECPay 要求的傳輸格式。
     *
     * @param array<string, mixed> $payload 原始 payload
     * @return array<string, mixed> 編碼後的 payload
     * @throws PayloadException 當 payload 無效時
     */
    public function encodePayload(array $payload): array
    {
        if (!isset($payload['Data'])) {
            throw PayloadException::missingData();
        }

        $encodedData = json_encode($payload['Data']);
        if ($encodedData === false) {
            throw PayloadException::invalidData('JSON 編碼失敗');
        }

        $encodedData = urlencode($encodedData);
        $encodedData = $this->transUrlencode($encodedData);

        $payload['Data'] = $this->cipherService->encrypt($encodedData);

        return $payload;
    }

    /**
     * 將回傳的 Data 還原為陣列欄位。
     *
     * @param string $encryptedData 加密的資料
     * @return array<string, mixed> 解密後的資料
     * @throws ApiException 當解密或解析失敗時
     */
    public function decodeData(string $encryptedData): array
    {
        $decrypted = $this->cipherService->decrypt($encryptedData);
        $urlDecoded = urldecode($decrypted);

        // PHP 8.3: 使用 json_validate() 先驗證 JSON 格式
        if (!json_validate($urlDecoded)) {
            throw ApiException::fromResponse(0, '回應資料 JSON 格式無效');
        }

        $decoded = json_decode($urlDecoded, true);

        if (!is_array($decoded)) {
            throw ApiException::fromResponse(0, '回應資料解析失敗');
        }

        return $decoded;
    }

    /**
     * 加密資料。
     *
     * @param string $data 原始資料
     * @return string 加密後的資料
     */
    public function encrypt(string $data): string
    {
        return $this->cipherService->encrypt($data);
    }

    /**
     * 解密資料。
     *
     * @param string $data 加密的資料
     * @return string 解密後的資料
     */
    public function decrypt(string $data): string
    {
        return $this->cipherService->decrypt($data);
    }

    /**
     * @inheritDoc
     *
     * 對於 AES 加密的電子發票 API，驗證方式為嘗試解密 Data 欄位。
     * 若解密成功且 JSON 格式正確，則視為驗證通過。
     */
    public function verifyResponse(array $responseData): bool
    {
        if (!isset($responseData['Data']) || !is_string($responseData['Data'])) {
            return false;
        }

        try {
            $this->decodeData($responseData['Data']);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * 與 .NET 相容的 URL encode 轉換。
     *
     * @param string $param 編碼後的字串
     * @return string 轉換後的字串
     */
    private function transUrlencode(string $param): string
    {
        $search = ['%2d', '%5f', '%2e', '%21', '%2a', '%28', '%29'];
        $replace = ['-', '_', '.', '!', '*', '(', ')'];

        return str_replace($search, $replace, $param);
    }
}
