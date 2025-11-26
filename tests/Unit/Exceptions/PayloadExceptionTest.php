<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Tests\Unit\Exceptions;

use CarlLee\EcPay\Core\Exceptions\PayloadException;
use CarlLee\EcPay\Core\Tests\TestCase;

/**
 * PayloadException 單元測試。
 */
class PayloadExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function 可以使用jsonEncodeFailed方法建立例外(): void
    {
        $exception = PayloadException::jsonEncodeFailed('Malformed UTF-8');

        $this->assertStringContainsString('JSON 編碼失敗', $exception->getMessage());
        $this->assertStringContainsString('Malformed UTF-8', $exception->getMessage());
        $this->assertEquals('Malformed UTF-8', $exception->getContext()['json_error']);
    }

    /**
     * @test
     */
    public function 可以使用jsonDecodeFailed方法建立例外(): void
    {
        $exception = PayloadException::jsonDecodeFailed('Syntax error');

        $this->assertStringContainsString('JSON 解碼失敗', $exception->getMessage());
        $this->assertStringContainsString('Syntax error', $exception->getMessage());
        $this->assertEquals('Syntax error', $exception->getContext()['json_error']);
    }

    /**
     * @test
     */
    public function 可以使用invalidStructure方法建立例外(): void
    {
        $exception = PayloadException::invalidStructure('缺少必要欄位');

        $this->assertStringContainsString('Payload 結構無效', $exception->getMessage());
        $this->assertStringContainsString('缺少必要欄位', $exception->getMessage());
        $this->assertEquals('缺少必要欄位', $exception->getContext()['reason']);
    }

    /**
     * @test
     */
    public function invalidStructure不指定原因時應有預設訊息(): void
    {
        $exception = PayloadException::invalidStructure();

        $this->assertStringContainsString('Payload 結構無效', $exception->getMessage());
    }

    /**
     * @test
     */
    public function 可以使用invalidData方法建立例外(): void
    {
        $exception = PayloadException::invalidData('資料類型錯誤');

        $this->assertStringContainsString('Payload 資料格式無效', $exception->getMessage());
        $this->assertStringContainsString('資料類型錯誤', $exception->getMessage());
        $this->assertEquals('資料類型錯誤', $exception->getContext()['reason']);
    }

    /**
     * @test
     */
    public function invalidData不指定原因時應有預設訊息(): void
    {
        $exception = PayloadException::invalidData();

        $this->assertStringContainsString('Payload 資料格式無效', $exception->getMessage());
    }

    /**
     * @test
     */
    public function 可以使用missingData方法建立例外(): void
    {
        $exception = PayloadException::missingData();

        $this->assertStringContainsString('Payload 結構無效', $exception->getMessage());
        $this->assertStringContainsString('Data 區塊', $exception->getMessage());
    }
}
