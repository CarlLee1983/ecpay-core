<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\DTO;

/**
 * 項目 DTO 介面。
 *
 * 定義可被 ItemCollection 管理的項目物件必須實作的方法。
 * 此介面為通用設計，適用於各種需要項目明細的場景。
 *
 * ## 適用範圍
 *
 * - **電子發票 (B2B/B2C)**
 *   - 發票商品明細 (InvoiceItemDto)
 *   - 折讓商品明細 (AllowanceItemDto)
 *
 * - **物流** (規劃中)
 *   - 出貨商品明細
 *
 * ## 實作範例
 *
 * ```php
 * final class MyItemDto implements ItemDtoInterface
 * {
 *     public function __construct(
 *         private string $name,
 *         private int $quantity,
 *         private float $price
 *     ) {}
 *
 *     public function toArray(): array
 *     {
 *         return [
 *             'ItemName' => $this->name,
 *             'ItemCount' => $this->quantity,
 *             'ItemPrice' => $this->price,
 *         ];
 *     }
 * }
 * ```
 *
 * @see ItemCollection 項目集合類別
 */
interface ItemDtoInterface
{
    /**
     * 轉換為陣列格式。
     *
     * 將 DTO 物件轉換為可用於 API 請求或序列化的陣列。
     * 實作類別應回傳符合對應 API 規格的欄位結構。
     *
     * @return array<string, mixed> 項目資料陣列
     */
    public function toArray(): array;
}
