<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Tests\Unit\DTO;

use CarlLee\EcPay\Core\DTO\RqHeaderDto;
use CarlLee\EcPay\Core\Tests\TestCase;
use InvalidArgumentException;

/**
 * RqHeaderDto 單元測試。
 */
class RqHeaderDtoTest extends TestCase
{
    /**
     * @test
     */
    public function 可以建立實例並使用當前時間(): void
    {
        $before = time();
        $dto = new RqHeaderDto();
        $after = time();

        $this->assertGreaterThanOrEqual($before, $dto->getTimestamp());
        $this->assertLessThanOrEqual($after, $dto->getTimestamp());
    }

    /**
     * @test
     */
    public function 可以指定時間戳建立實例(): void
    {
        $timestamp = 1700000000;
        $dto = new RqHeaderDto($timestamp);

        $this->assertEquals($timestamp, $dto->getTimestamp());
    }

    /**
     * @test
     */
    public function 可以從陣列建立實例(): void
    {
        $timestamp = 1700000000;
        $dto = RqHeaderDto::fromArray(['Timestamp' => $timestamp]);

        $this->assertEquals($timestamp, $dto->getTimestamp());
    }

    /**
     * @test
     */
    public function 從陣列建立時缺少Timestamp應拋出例外(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RqHeader timestamp is required');

        RqHeaderDto::fromArray([]);
    }

    /**
     * @test
     */
    public function 可以設定時間戳(): void
    {
        $dto = new RqHeaderDto();
        $newTimestamp = 1700000000;

        $result = $dto->setTimestamp($newTimestamp);

        $this->assertSame($dto, $result); // 應回傳自身（fluent interface）
        $this->assertEquals($newTimestamp, $dto->getTimestamp());
    }

    /**
     * @test
     */
    public function 設定零或負數時間戳應拋出例外(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be greater than 0');

        $dto = new RqHeaderDto();
        $dto->setTimestamp(0);
    }

    /**
     * @test
     */
    public function 設定負數時間戳應拋出例外(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be greater than 0');

        $dto = new RqHeaderDto();
        $dto->setTimestamp(-1);
    }

    /**
     * @test
     */
    public function toPayload應回傳正確格式(): void
    {
        $timestamp = 1700000000;
        $dto = new RqHeaderDto($timestamp);

        $payload = $dto->toPayload();

        $this->assertIsArray($payload);
        $this->assertArrayHasKey('Timestamp', $payload);
        $this->assertEquals($timestamp, $payload['Timestamp']);
    }

    /**
     * @test
     */
    public function toArray應與toPayload相同(): void
    {
        $timestamp = 1700000000;
        $dto = new RqHeaderDto($timestamp);

        $this->assertEquals($dto->toPayload(), $dto->toArray());
    }
}
