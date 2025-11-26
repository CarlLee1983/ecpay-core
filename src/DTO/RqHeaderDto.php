<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\DTO;

use InvalidArgumentException;

/**
 * 代表 RqHeader 結構的 Value Object。
 *
 * 用於封裝 API 請求標頭中的時間戳等資訊。
 */
final class RqHeaderDto
{
    /**
     * 時間戳。
     *
     * @var int
     */
    private int $timestamp;

    /**
     * 建立 RqHeader DTO。
     *
     * @param int|null $timestamp Unix 時間戳，預設為當前時間
     */
    public function __construct(?int $timestamp = null)
    {
        $this->setTimestamp($timestamp ?? time());
    }

    /**
     * 從陣列建立 DTO。
     *
     * @param array<string, mixed> $header 標頭資料
     * @return self
     * @throws InvalidArgumentException 當缺少必要欄位時
     */
    public static function fromArray(array $header): self
    {
        if (!isset($header['Timestamp'])) {
            throw new InvalidArgumentException('RqHeader timestamp is required.');
        }

        return new self((int) $header['Timestamp']);
    }

    /**
     * 設定時間戳。
     *
     * @param int $timestamp Unix 時間戳
     * @return self
     * @throws InvalidArgumentException 當時間戳無效時
     */
    public function setTimestamp(int $timestamp): self
    {
        if ($timestamp <= 0) {
            throw new InvalidArgumentException('RqHeader timestamp must be greater than 0.');
        }

        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * 取得時間戳。
     *
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * 轉換為 payload 格式。
     *
     * @return array<string, int>
     */
    public function toPayload(): array
    {
        return [
            'Timestamp' => $this->timestamp,
        ];
    }

    /**
     * 轉換為陣列。
     *
     * @deprecated 建議使用 toPayload()
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return $this->toPayload();
    }
}
