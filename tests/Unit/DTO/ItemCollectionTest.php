<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Tests\Unit\DTO;

use CarlLee\EcPay\Core\DTO\ItemCollection;
use CarlLee\EcPay\Core\DTO\ItemDtoInterface;
use CarlLee\EcPay\Core\Tests\TestCase;
use InvalidArgumentException;

/**
 * ItemCollection 單元測試。
 */
class ItemCollectionTest extends TestCase
{
    /**
     * @test
     */
    public function 可以建立空集合(): void
    {
        $collection = new ItemCollection();

        $this->assertTrue($collection->isEmpty());
        $this->assertFalse($collection->isNotEmpty());
        $this->assertEquals(0, $collection->count());
    }

    /**
     * @test
     */
    public function 可以新增項目(): void
    {
        $collection = new ItemCollection();
        $item = $this->createMockItem(['name' => 'Test']);

        $result = $collection->add($item);

        $this->assertSame($collection, $result); // fluent interface
        $this->assertEquals(1, $collection->count());
        $this->assertFalse($collection->isEmpty());
        $this->assertTrue($collection->isNotEmpty());
    }

    /**
     * @test
     */
    public function 可以使用初始項目建立集合(): void
    {
        $items = [
            $this->createMockItem(['name' => 'Item 1']),
            $this->createMockItem(['name' => 'Item 2']),
        ];

        $collection = new ItemCollection($items);

        $this->assertEquals(2, $collection->count());
    }

    /**
     * @test
     */
    public function 可以取得所有項目(): void
    {
        $items = [
            $this->createMockItem(['name' => 'Item 1']),
            $this->createMockItem(['name' => 'Item 2']),
        ];

        $collection = new ItemCollection($items);

        $this->assertCount(2, $collection->all());
        $this->assertSame($items[0], $collection->all()[0]);
        $this->assertSame($items[1], $collection->all()[1]);
    }

    /**
     * @test
     */
    public function 可以取得第一個項目(): void
    {
        $items = [
            $this->createMockItem(['name' => 'First']),
            $this->createMockItem(['name' => 'Second']),
        ];

        $collection = new ItemCollection($items);

        $this->assertSame($items[0], $collection->first());
    }

    /**
     * @test
     */
    public function 空集合取得第一個項目應回傳null(): void
    {
        $collection = new ItemCollection();

        $this->assertNull($collection->first());
    }

    /**
     * @test
     */
    public function 可以取得最後一個項目(): void
    {
        $items = [
            $this->createMockItem(['name' => 'First']),
            $this->createMockItem(['name' => 'Last']),
        ];

        $collection = new ItemCollection($items);

        $this->assertSame($items[1], $collection->last());
    }

    /**
     * @test
     */
    public function 空集合取得最後一個項目應回傳null(): void
    {
        $collection = new ItemCollection();

        $this->assertNull($collection->last());
    }

    /**
     * @test
     */
    public function 可以轉換為陣列(): void
    {
        $items = [
            $this->createMockItem(['name' => 'Item 1', 'price' => 100]),
            $this->createMockItem(['name' => 'Item 2', 'price' => 200]),
        ];

        $collection = new ItemCollection($items);
        $array = $collection->toArray();

        $this->assertCount(2, $array);
        $this->assertEquals(['name' => 'Item 1', 'price' => 100], $array[0]);
        $this->assertEquals(['name' => 'Item 2', 'price' => 200], $array[1]);
    }

    /**
     * @test
     */
    public function 可以迭代集合(): void
    {
        $items = [
            $this->createMockItem(['name' => 'Item 1']),
            $this->createMockItem(['name' => 'Item 2']),
        ];

        $collection = new ItemCollection($items);
        $iteratedItems = [];

        foreach ($collection as $item) {
            $iteratedItems[] = $item;
        }

        $this->assertCount(2, $iteratedItems);
        $this->assertSame($items[0], $iteratedItems[0]);
        $this->assertSame($items[1], $iteratedItems[1]);
    }

    /**
     * @test
     */
    public function 可以過濾項目(): void
    {
        $items = [
            $this->createMockItem(['name' => 'Apple', 'price' => 100]),
            $this->createMockItem(['name' => 'Banana', 'price' => 50]),
            $this->createMockItem(['name' => 'Cherry', 'price' => 150]),
        ];

        $collection = new ItemCollection($items);
        $filtered = $collection->filter(function (ItemDtoInterface $item) {
            $data = $item->toArray();
            return $data['price'] >= 100;
        });

        $this->assertEquals(2, $filtered->count());
        $this->assertNotSame($collection, $filtered); // 應回傳新實例
    }

    /**
     * @test
     */
    public function 可以映射項目(): void
    {
        $items = [
            $this->createMockItem(['name' => 'Item 1', 'price' => 100]),
            $this->createMockItem(['name' => 'Item 2', 'price' => 200]),
        ];

        $collection = new ItemCollection($items);
        $prices = $collection->map(function (ItemDtoInterface $item) {
            return $item->toArray()['price'];
        });

        $this->assertEquals([100, 200], $prices);
    }

    /**
     * @test
     */
    public function 可以清空集合(): void
    {
        $items = [
            $this->createMockItem(['name' => 'Item 1']),
            $this->createMockItem(['name' => 'Item 2']),
        ];

        $collection = new ItemCollection($items);
        $result = $collection->clear();

        $this->assertSame($collection, $result); // fluent interface
        $this->assertTrue($collection->isEmpty());
        $this->assertEquals(0, $collection->count());
    }

    /**
     * @test
     */
    public function 指定allowedClass時應驗證類型(): void
    {
        // 建立一個具體的 ItemDto 類別來測試
        $concreteItem = new class implements ItemDtoInterface {
            public function toArray(): array
            {
                return ['type' => 'concrete'];
            }
        };

        // 取得具體類別名稱
        $allowedClass = get_class($concreteItem);

        // 建立一個不同類型的 DTO
        $differentItem = new class implements ItemDtoInterface {
            public function toArray(): array
            {
                return ['type' => 'different'];
            }
        };

        // 使用具體類別建立集合，然後嘗試加入不同類型
        $collection = new ItemCollection([$concreteItem], $allowedClass);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('必須是');

        $collection->add($differentItem);
    }

    /**
     * @test
     */
    public function count應實作Countable介面(): void
    {
        $items = [
            $this->createMockItem(['name' => 'Item 1']),
            $this->createMockItem(['name' => 'Item 2']),
            $this->createMockItem(['name' => 'Item 3']),
        ];

        $collection = new ItemCollection($items);

        $this->assertCount(3, $collection); // 使用 assertCount 測試 Countable
    }

    /**
     * 建立 Mock ItemDtoInterface。
     *
     * @param array<string, mixed> $data
     * @return ItemDtoInterface
     */
    private function createMockItem(array $data): ItemDtoInterface
    {
        $mock = $this->createMock(ItemDtoInterface::class);
        $mock->method('toArray')->willReturn($data);

        return $mock;
    }
}
