<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Tests\Unit\Infrastructure;

use CarlLee\EcPay\Core\Exceptions\EncryptionException;
use CarlLee\EcPay\Core\Infrastructure\CipherService;
use CarlLee\EcPay\Core\Tests\TestCase;

/**
 * CipherService 單元測試。
 */
class CipherServiceTest extends TestCase
{
    private CipherService $cipher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cipher = new CipherService(self::TEST_HASH_KEY, self::TEST_HASH_IV);
    }

    /**
     * @test
     */
    public function 可以正確加密資料(): void
    {
        $plainText = 'Hello, ECPay!';
        $encrypted = $this->cipher->encrypt($plainText);

        $this->assertNotEmpty($encrypted);
        $this->assertNotEquals($plainText, $encrypted);
        // 加密結果應為 Base64 編碼
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9+\/=]+$/', $encrypted);
    }

    /**
     * @test
     */
    public function 可以正確解密資料(): void
    {
        $plainText = 'Hello, ECPay!';
        $encrypted = $this->cipher->encrypt($plainText);
        $decrypted = $this->cipher->decrypt($encrypted);

        $this->assertEquals($plainText, $decrypted);
    }

    /**
     * @test
     */
    public function 加密後解密應回傳原始資料(): void
    {
        $testCases = [
            'simple text',
            '中文測試',
            '{"key": "value", "number": 123}',
            '',
        ];

        foreach ($testCases as $plainText) {
            if ($plainText === '') {
                continue; // 空字串會在 decrypt 時拋出例外
            }
            $encrypted = $this->cipher->encrypt($plainText);
            $decrypted = $this->cipher->decrypt($encrypted);
            $this->assertEquals($plainText, $decrypted, "Failed for: {$plainText}");
        }
    }

    /**
     * @test
     */
    public function 空的HashKey應拋出例外(): void
    {
        $this->expectException(EncryptionException::class);
        $this->expectExceptionMessage('HashKey 不得為空');

        new CipherService('', self::TEST_HASH_IV);
    }

    /**
     * @test
     */
    public function 空的HashIV應拋出例外(): void
    {
        $this->expectException(EncryptionException::class);
        $this->expectExceptionMessage('HashIV 不得為空');

        new CipherService(self::TEST_HASH_KEY, '');
    }

    /**
     * @test
     */
    public function 解密空字串應拋出例外(): void
    {
        $this->expectException(EncryptionException::class);
        $this->expectExceptionMessage('資料為空');

        $this->cipher->decrypt('');
    }

    /**
     * @test
     */
    public function 解密無效Base64應拋出例外(): void
    {
        $this->expectException(EncryptionException::class);
        $this->expectExceptionMessage('Base64 解碼失敗');

        $this->cipher->decrypt('!!!invalid-base64!!!');
    }

    /**
     * @test
     */
    public function 解密無效加密資料應拋出例外(): void
    {
        $this->expectException(EncryptionException::class);
        $this->expectExceptionMessage('AES 解密失敗');

        // 有效的 Base64 但不是有效的加密資料
        $this->cipher->decrypt('SGVsbG8gV29ybGQ=');
    }

    /**
     * @test
     */
    public function 不同金鑰無法解密(): void
    {
        $plainText = 'secret data';
        $encrypted = $this->cipher->encrypt($plainText);

        $differentCipher = new CipherService('differentKey123!', 'differentIV1234!');

        $this->expectException(EncryptionException::class);
        $differentCipher->decrypt($encrypted);
    }
}
