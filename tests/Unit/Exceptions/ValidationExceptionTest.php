<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Tests\Unit\Exceptions;

use CarlLee\EcPay\Core\Exceptions\ValidationException;
use CarlLee\EcPay\Core\Tests\TestCase;

/**
 * ValidationException 單元測試。
 */
class ValidationExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function 可以使用make方法建立例外(): void
    {
        $exception = ValidationException::make('驗證失敗', 'field_name', ['extra' => 'info']);

        $this->assertEquals('驗證失敗', $exception->getMessage());
        $this->assertEquals('field_name', $exception->getField());
        $this->assertArrayHasKey('extra', $exception->getContext());
        $this->assertArrayHasKey('field', $exception->getContext());
    }

    /**
     * @test
     */
    public function make方法不指定欄位時field應為null(): void
    {
        $exception = ValidationException::make('驗證失敗');

        $this->assertNull($exception->getField());
    }

    /**
     * @test
     */
    public function 可以使用withErrors方法建立例外(): void
    {
        $errors = [
            'email' => ['Email 格式無效', 'Email 已被使用'],
            'password' => ['密碼太短'],
        ];

        $exception = ValidationException::withErrors($errors);

        $this->assertEquals('資料驗證失敗', $exception->getMessage());
        $this->assertEquals($errors, $exception->getErrors());
        $this->assertArrayHasKey('errors', $exception->getContext());
    }

    /**
     * @test
     */
    public function 可以使用requiredField方法建立例外(): void
    {
        $exception = ValidationException::requiredField('MerchantID');

        $this->assertStringContainsString('MerchantID', $exception->getMessage());
        $this->assertStringContainsString('必填', $exception->getMessage());
        $this->assertEquals('MerchantID', $exception->getField());
    }

    /**
     * @test
     */
    public function 可以使用required方法建立例外(): void
    {
        $exception = ValidationException::required('RelateNumber');

        $this->assertStringContainsString('RelateNumber', $exception->getMessage());
        $this->assertStringContainsString('必填', $exception->getMessage());
        $this->assertEquals('RelateNumber', $exception->getField());
    }

    /**
     * @test
     */
    public function 可以使用invalidFormat方法建立例外(): void
    {
        $exception = ValidationException::invalidFormat('InvoiceDate', 'yyyy-mm-dd');

        $this->assertStringContainsString('InvoiceDate', $exception->getMessage());
        $this->assertStringContainsString('格式錯誤', $exception->getMessage());
        $this->assertStringContainsString('yyyy-mm-dd', $exception->getMessage());
        $this->assertEquals('InvoiceDate', $exception->getField());
        $this->assertEquals('yyyy-mm-dd', $exception->getContext()['expected_format']);
    }

    /**
     * @test
     */
    public function 可以使用invalid方法建立例外(): void
    {
        $exception = ValidationException::invalid('TaxType', '必須為數字');

        $this->assertStringContainsString('TaxType', $exception->getMessage());
        $this->assertStringContainsString('格式無效', $exception->getMessage());
        $this->assertStringContainsString('必須為數字', $exception->getMessage());
        $this->assertEquals('TaxType', $exception->getField());
    }

    /**
     * @test
     */
    public function invalid方法不指定原因時應有預設訊息(): void
    {
        $exception = ValidationException::invalid('TaxType');

        $this->assertStringContainsString('TaxType', $exception->getMessage());
        $this->assertStringContainsString('格式無效', $exception->getMessage());
    }

    /**
     * @test
     */
    public function 可以使用tooLong方法建立例外(): void
    {
        $exception = ValidationException::tooLong('RelateNumber', 30);

        $this->assertStringContainsString('RelateNumber', $exception->getMessage());
        $this->assertStringContainsString('30', $exception->getMessage());
        $this->assertStringContainsString('字元', $exception->getMessage());
        $this->assertEquals('RelateNumber', $exception->getField());
        $this->assertEquals(30, $exception->getContext()['max_length']);
    }

    /**
     * @test
     */
    public function 可以使用notInRange方法建立例外(): void
    {
        $allowedValues = [1, 2, 3, 9];
        $exception = ValidationException::notInRange('TaxType', $allowedValues);

        $this->assertStringContainsString('TaxType', $exception->getMessage());
        $this->assertStringContainsString('1, 2, 3, 9', $exception->getMessage());
        $this->assertEquals('TaxType', $exception->getField());
        $this->assertEquals($allowedValues, $exception->getContext()['allowed_values']);
    }

    /**
     * @test
     */
    public function getErrors預設應回傳空陣列(): void
    {
        $exception = ValidationException::required('field');

        $this->assertEquals([], $exception->getErrors());
    }
}
