<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core;

use CarlLee\EcPay\Core\Exceptions\ApiException;

/**
 * API 回應封裝類別。
 *
 * 提供統一的方式處理綠界 API 回應資料。
 */
class Response
{
    /**
     * 成功的回傳代碼。
     */
    public const int SUCCESS_CODE = 1;

    /**
     * 回應資料。
     *
     * @var array<string, mixed>
     */
    protected array $data = [
        'RtnCode' => 0,
        'RtnMsg' => '',
    ];

    /**
     * 建立 Response 實例。
     *
     * @param array<string, mixed> $data 回應資料
     */
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->setData($data);
        }
    }

    /**
     * 從 API 回應建立 Response。
     *
     * @param array<string, mixed> $data 回應資料
     * @return static
     */
    public static function fromArray(array $data): static
    {
        return new static($data);
    }

    /**
     * 設定回應資料。
     *
     * @param array<string, mixed> $data
     * @return static
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * 檢查回應是否成功。
     *
     * @return bool
     */
    public function success(): bool
    {
        return $this->data['RtnCode'] == self::SUCCESS_CODE;
    }

    /**
     * success() 的別名。
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success();
    }

    /**
     * 檢查回應是否為錯誤。
     *
     * @return bool
     */
    public function isError(): bool
    {
        return !$this->success();
    }

    /**
     * 取得回應訊息。
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->data['RtnMsg'] ?? '';
    }

    /**
     * 取得回應代碼。
     *
     * @return int
     */
    public function getCode(): int
    {
        return (int) ($this->data['RtnCode'] ?? 0);
    }

    /**
     * 取得回應資料。
     *
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * 取得解密後的 Data 欄位。
     *
     * @return array<string, mixed>|null
     */
    public function getDecodedData(): ?array
    {
        return $this->data['Data'] ?? null;
    }

    /**
     * 取得指定欄位的值。
     *
     * @param string $key 欄位名稱
     * @param mixed $default 預設值
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * 檢查是否存在指定欄位。
     *
     * @param string $key 欄位名稱
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * 轉換為陣列。
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * 轉換為 JSON 字串。
     *
     * @param int $options JSON 編碼選項
     * @return string
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->data, $options) ?: '{}';
    }

    /**
     * 當回應為錯誤時拋出例外。
     *
     * @return static
     * @throws ApiException 當回應為錯誤時
     */
    public function throw(): static
    {
        if ($this->isError()) {
            throw ApiException::fromResponse(
                $this->getCode(),
                $this->getMessage(),
                $this->data
            );
        }

        return $this;
    }

    /**
     * 當回應為錯誤時執行回呼。
     *
     * @param callable(static): void $callback
     * @return static
     */
    public function onError(callable $callback): static
    {
        if ($this->isError()) {
            $callback($this);
        }

        return $this;
    }

    /**
     * 當回應為成功時執行回呼。
     *
     * @param callable(static): void $callback
     * @return static
     */
    public function onSuccess(callable $callback): static
    {
        if ($this->isSuccess()) {
            $callback($this);
        }

        return $this;
    }
}
