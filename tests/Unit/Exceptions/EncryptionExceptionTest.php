<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Tests\Unit\Exceptions;

use CarlLee\EcPay\Core\Exceptions\EncryptionException;
use CarlLee\EcPay\Core\Tests\TestCase;

/**
 * EncryptionException 單元測試。
 */
class EncryptionExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function 可以使用invalidKey方法建立例外(): void
    {
        $exception = EncryptionException::invalidKey('HashKey');

        $this->assertStringContainsString('HashKey', $exception->getMessage());
        $this->assertStringContainsString('不得為空', $exception->getMessage());
        $this->assertEquals('HashKey', $exception->getContext()['key_name']);
    }

    /**
     * @test
     */
    public function 可以使用encryptionFailed方法建立例外(): void
    {
        $exception = EncryptionException::encryptionFailed('演算法不支援');

        $this->assertStringContainsString('AES 加密失敗', $exception->getMessage());
        $this->assertStringContainsString('演算法不支援', $exception->getMessage());
    }

    /**
     * @test
     */
    public function encryptionFailed不指定原因時應有預設訊息(): void
    {
        $exception = EncryptionException::encryptionFailed();

        $this->assertStringContainsString('AES 加密失敗', $exception->getMessage());
    }

    /**
     * @test
     */
    public function 可以使用decryptionFailed方法建立例外(): void
    {
        $exception = EncryptionException::decryptionFailed('資料損毀');

        $this->assertStringContainsString('AES 解密失敗', $exception->getMessage());
        $this->assertStringContainsString('資料損毀', $exception->getMessage());
        $this->assertEquals('資料損毀', $exception->getContext()['reason']);
    }

    /**
     * @test
     */
    public function decryptionFailed不指定原因時應有預設訊息(): void
    {
        $exception = EncryptionException::decryptionFailed();

        $this->assertStringContainsString('AES 解密失敗', $exception->getMessage());
    }
}
