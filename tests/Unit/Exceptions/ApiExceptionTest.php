<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Tests\Unit\Exceptions;

use CarlLee\EcPay\Core\Exceptions\ApiException;
use CarlLee\EcPay\Core\Tests\TestCase;
use Exception;

/**
 * ApiException 單元測試。
 */
class ApiExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function 可以使用make方法建立例外(): void
    {
        $exception = ApiException::make('測試錯誤', 100, ['key' => 'value']);

        $this->assertEquals('測試錯誤', $exception->getMessage());
        $this->assertEquals(100, $exception->getCode());
        $this->assertEquals(['key' => 'value'], $exception->getResponseData());
        $this->assertArrayHasKey('response', $exception->getContext());
    }

    /**
     * @test
     */
    public function 可以使用fromResponse方法建立例外(): void
    {
        $responseData = [
            'RtnCode' => 10000001,
            'RtnMsg' => 'API 錯誤訊息',
        ];

        $exception = ApiException::fromResponse(10000001, 'API 錯誤訊息', $responseData);

        $this->assertStringContainsString('10000001', $exception->getMessage());
        $this->assertStringContainsString('API 錯誤訊息', $exception->getMessage());
        $this->assertEquals(10000001, $exception->getCode());
        $this->assertEquals($responseData, $exception->getResponseData());
    }

    /**
     * @test
     */
    public function 可以使用requestFailed方法建立例外(): void
    {
        $previous = new Exception('連線錯誤');
        $exception = ApiException::requestFailed('網路連線失敗', $previous);

        $this->assertStringContainsString('HTTP 請求失敗', $exception->getMessage());
        $this->assertStringContainsString('網路連線失敗', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * @test
     */
    public function 可以使用invalidResponse方法建立例外(): void
    {
        $exception = ApiException::invalidResponse('JSON 解析失敗');

        $this->assertStringContainsString('API 回應格式無效', $exception->getMessage());
        $this->assertStringContainsString('JSON 解析失敗', $exception->getMessage());
    }

    /**
     * @test
     */
    public function invalidResponse不帶原因時應有預設訊息(): void
    {
        $exception = ApiException::invalidResponse();

        $this->assertStringContainsString('API 回應格式無效', $exception->getMessage());
    }

    /**
     * @test
     */
    public function getRtnCode應回傳回應代碼(): void
    {
        $exception = ApiException::fromResponse(10000001, '錯誤', ['RtnCode' => 10000001]);

        $this->assertEquals(10000001, $exception->getRtnCode());
    }

    /**
     * @test
     */
    public function getRtnCode無回應資料時應回傳null(): void
    {
        $exception = ApiException::requestFailed('錯誤');

        $this->assertNull($exception->getRtnCode());
    }

    /**
     * @test
     */
    public function getRtnMsg應回傳回應訊息(): void
    {
        $exception = ApiException::fromResponse(1, '成功訊息', [
            'RtnCode' => 1,
            'RtnMsg' => '成功訊息',
        ]);

        $this->assertEquals('成功訊息', $exception->getRtnMsg());
    }

    /**
     * @test
     */
    public function getRtnMsg無回應資料時應回傳null(): void
    {
        $exception = ApiException::requestFailed('錯誤');

        $this->assertNull($exception->getRtnMsg());
    }

    /**
     * @test
     */
    public function getResponseData應回傳完整回應資料(): void
    {
        $responseData = [
            'RtnCode' => 1,
            'RtnMsg' => '成功',
            'Data' => ['InvoiceNo' => 'AB12345678'],
        ];

        $exception = ApiException::fromResponse(1, '成功', $responseData);

        $this->assertEquals($responseData, $exception->getResponseData());
    }
}
