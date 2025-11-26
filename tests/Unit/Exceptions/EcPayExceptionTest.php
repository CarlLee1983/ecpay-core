<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Tests\Unit\Exceptions;

use CarlLee\EcPay\Core\Exceptions\EcPayException;
use CarlLee\EcPay\Core\Tests\TestCase;
use Exception;

/**
 * EcPayException 單元測試。
 */
class EcPayExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function 可以建立基本例外(): void
    {
        $exception = new EcPayException('測試錯誤');

        $this->assertEquals('測試錯誤', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
        $this->assertEquals([], $exception->getContext());
    }

    /**
     * @test
     */
    public function 可以建立帶有代碼的例外(): void
    {
        $exception = new EcPayException('測試錯誤', 100);

        $this->assertEquals(100, $exception->getCode());
    }

    /**
     * @test
     */
    public function 可以建立帶有前一個例外的例外(): void
    {
        $previous = new Exception('前一個錯誤');
        $exception = new EcPayException('測試錯誤', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * @test
     */
    public function 可以建立帶有上下文的例外(): void
    {
        $context = ['key' => 'value', 'number' => 123];
        $exception = new EcPayException('測試錯誤', 0, null, $context);

        $this->assertEquals($context, $exception->getContext());
    }

    /**
     * @test
     */
    public function 可以設定上下文(): void
    {
        $exception = new EcPayException('測試錯誤');
        $context = ['key' => 'value'];

        $result = $exception->setContext($context);

        $this->assertSame($exception, $result); // fluent interface
        $this->assertEquals($context, $exception->getContext());
    }

    /**
     * @test
     */
    public function 可以新增上下文(): void
    {
        $exception = new EcPayException('測試錯誤', 0, null, ['existing' => 'value']);

        $result = $exception->addContext('new_key', 'new_value');

        $this->assertSame($exception, $result); // fluent interface
        $this->assertEquals([
            'existing' => 'value',
            'new_key' => 'new_value',
        ], $exception->getContext());
    }

    /**
     * @test
     */
    public function 新增相同key的上下文應覆蓋舊值(): void
    {
        $exception = new EcPayException('測試錯誤', 0, null, ['key' => 'old_value']);

        $exception->addContext('key', 'new_value');

        $this->assertEquals(['key' => 'new_value'], $exception->getContext());
    }
}
