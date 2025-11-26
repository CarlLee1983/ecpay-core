<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Contracts;

/**
 * 封裝對 EcPay API 的命令介面。
 *
 * 此介面定義了所有 API 命令必須實作的方法，
 * 與 ContentInterface 功能相似，但語義上更強調「命令」的概念。
 */
interface CommandInterface
{
    /**
     * 取得 API 路徑。
     *
     * @return string API 端點路徑
     */
    public function getRequestPath(): string;

    /**
     * 取得未加密的請求 payload。
     *
     * @return array<string, mixed> 請求的 payload 資料
     */
    public function getPayload(): array;

    /**
     * 取得可用於編碼/解碼的 PayloadEncoder。
     *
     * @return PayloadEncoderInterface 編碼器實例
     */
    public function getPayloadEncoder(): PayloadEncoderInterface;

    /**
     * 調整 HashKey，讓命令採用客戶端提供的金鑰。
     *
     * @param string $key HashKey
     * @return static
     */
    public function setHashKey(string $key): static;

    /**
     * 調整 HashIV，讓命令採用客戶端提供的金鑰。
     *
     * @param string $iv HashIV
     * @return static
     */
    public function setHashIV(string $iv): static;
}
