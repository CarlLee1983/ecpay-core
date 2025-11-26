<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Tests\Unit\Exceptions;

use CarlLee\EcPay\Core\Exceptions\ConfigurationException;
use CarlLee\EcPay\Core\Tests\TestCase;

/**
 * ConfigurationException 單元測試。
 */
class ConfigurationExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function 可以使用missingConfig方法建立例外(): void
    {
        $exception = ConfigurationException::missingConfig('merchant_id');

        $this->assertStringContainsString('缺少必要的設定項', $exception->getMessage());
        $this->assertStringContainsString('merchant_id', $exception->getMessage());
        $this->assertEquals('merchant_id', $exception->getContext()['missing_key']);
    }

    /**
     * @test
     */
    public function 可以使用invalidValue方法建立例外(): void
    {
        $exception = ConfigurationException::invalidValue('timeout', -1, '必須為正整數');

        $this->assertStringContainsString('timeout', $exception->getMessage());
        $this->assertStringContainsString('無效', $exception->getMessage());
        $this->assertStringContainsString('必須為正整數', $exception->getMessage());
        $this->assertEquals('timeout', $exception->getContext()['key']);
        $this->assertEquals(-1, $exception->getContext()['value']);
        $this->assertEquals('必須為正整數', $exception->getContext()['reason']);
    }

    /**
     * @test
     */
    public function invalidValue不指定原因時應有預設訊息(): void
    {
        $exception = ConfigurationException::invalidValue('environment', 'invalid');

        $this->assertStringContainsString('environment', $exception->getMessage());
        $this->assertStringContainsString('無效', $exception->getMessage());
    }
}
