<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\DTO;

use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/**
 * 項目集合類別。
 *
 * 提供類型安全的項目 DTO 集合管理，支援迭代、過濾、映射等操作。
 * 可選擇性地限制集合只接受特定類型的 DTO。
 *
 * ## 適用範圍
 *
 * - **電子發票 (B2B/B2C)** - 發票/折讓商品明細集合
 * - **物流** (規劃中) - 出貨商品明細集合
 *
 * ## 使用範例
 *
 * ```php
 * // 建立集合並新增項目
 * $items = new ItemCollection();
 * $items->add(new InvoiceItemDto('商品A', 2, '個', 100.0, 200));
 * $items->add(new InvoiceItemDto('商品B', 1, '件', 500.0, 500));
 *
 * // 計算總金額
 * $total = array_sum($items->map(fn($item) => $item->toArray()['ItemAmount']));
 *
 * // 轉換為 API payload
 * $payload = $items->toArray();
 * ```
 *
 * @implements IteratorAggregate<int, ItemDtoInterface>
 * @see ItemDtoInterface 項目 DTO 介面
 */
class ItemCollection implements Countable, IteratorAggregate
{
    /**
     * 項目陣列。
     *
     * @var array<int, ItemDtoInterface>
     */
    protected array $items = [];

    /**
     * 允許的項目類別。
     *
     * @var class-string<ItemDtoInterface>|null
     */
    protected ?string $allowedClass = null;

    /**
     * 建立集合。
     *
     * @param array<int, ItemDtoInterface> $items 初始項目
     * @param class-string<ItemDtoInterface>|null $allowedClass 允許的類別
     */
    public function __construct(array $items = [], ?string $allowedClass = null)
    {
        $this->allowedClass = $allowedClass;

        foreach ($items as $item) {
            $this->add($item);
        }
    }

    /**
     * 新增項目。
     *
     * @param ItemDtoInterface $item 項目
     * @throws InvalidArgumentException 當項目類別不符時
     * @return static
     */
    public function add(ItemDtoInterface $item): static
    {
        if ($this->allowedClass !== null && !$item instanceof $this->allowedClass) {
            throw new InvalidArgumentException(
                sprintf('項目必須是 %s 的實例', $this->allowedClass)
            );
        }

        $this->items[] = $item;

        return $this;
    }

    /**
     * 取得所有項目。
     *
     * @return array<int, ItemDtoInterface>
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * 取得第一個項目。
     *
     * @return ItemDtoInterface|null
     */
    public function first(): ?ItemDtoInterface
    {
        return $this->items[0] ?? null;
    }

    /**
     * 取得最後一個項目。
     *
     * @return ItemDtoInterface|null
     */
    public function last(): ?ItemDtoInterface
    {
        if (empty($this->items)) {
            return null;
        }

        return $this->items[array_key_last($this->items)];
    }

    /**
     * 檢查集合是否為空。
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * 檢查集合是否不為空。
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * 取得項目數量。
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * 轉換為陣列。
     *
     * @return array<int, array<string, mixed>>
     */
    public function toArray(): array
    {
        return array_map(
            fn (ItemDtoInterface $item) => $item->toArray(),
            $this->items
        );
    }

    /**
     * 取得迭代器。
     *
     * @return Traversable<int, ItemDtoInterface>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * 使用回呼函式過濾項目。
     *
     * @param callable(ItemDtoInterface): bool $callback 過濾回呼
     * @return static 新的集合實例
     */
    public function filter(callable $callback): static
    {
        $filtered = array_filter($this->items, $callback);

        return new static(array_values($filtered), $this->allowedClass);
    }

    /**
     * 使用回呼函式轉換項目。
     *
     * @template T
     * @param callable(ItemDtoInterface): T $callback 轉換回呼
     * @return array<int, T>
     */
    public function map(callable $callback): array
    {
        return array_map($callback, $this->items);
    }

    /**
     * 清空集合。
     *
     * @return static
     */
    public function clear(): static
    {
        $this->items = [];

        return $this;
    }
}
