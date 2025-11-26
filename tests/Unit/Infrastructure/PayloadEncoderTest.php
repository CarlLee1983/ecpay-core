<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Tests\Unit\Infrastructure;

use CarlLee\EcPay\Core\Exceptions\ApiException;
use CarlLee\EcPay\Core\Exceptions\PayloadException;
use CarlLee\EcPay\Core\Infrastructure\CipherService;
use CarlLee\EcPay\Core\Infrastructure\PayloadEncoder;
use CarlLee\EcPay\Core\Tests\TestCase;

/**
 * PayloadEncoder 單元測試。
 */
class PayloadEncoderTest extends TestCase
{
    private PayloadEncoder $encoder;
    private CipherService $cipher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cipher = new CipherService(self::TEST_HASH_KEY, self::TEST_HASH_IV);
        $this->encoder = new PayloadEncoder($this->cipher);
    }

    /**
     * @test
     */
    public function 可以使用靜態方法建立實例(): void
    {
        $encoder = PayloadEncoder::create(self::TEST_HASH_KEY, self::TEST_HASH_IV);

        $this->assertInstanceOf(PayloadEncoder::class, $encoder);
    }

    /**
     * @test
     */
    public function 可以使用HashKey和HashIV建立實例(): void
    {
        $encoder = new PayloadEncoder(null, self::TEST_HASH_KEY, self::TEST_HASH_IV);

        $this->assertInstanceOf(PayloadEncoder::class, $encoder);
    }

    /**
     * @test
     */
    public function 可以正確編碼Payload(): void
    {
        $payload = [
            'MerchantID' => self::TEST_MERCHANT_ID,
            'Data' => [
                'MerchantID' => self::TEST_MERCHANT_ID,
                'RelateNumber' => 'TEST123',
            ],
        ];

        $encoded = $this->encoder->encodePayload($payload);

        $this->assertArrayHasKey('MerchantID', $encoded);
        $this->assertArrayHasKey('Data', $encoded);
        $this->assertEquals(self::TEST_MERCHANT_ID, $encoded['MerchantID']);
        // Data 應該被加密
        $this->assertIsString($encoded['Data']);
        $this->assertNotEquals($payload['Data'], $encoded['Data']);
    }

    /**
     * @test
     */
    public function 缺少Data區塊應拋出例外(): void
    {
        $this->expectException(PayloadException::class);
        $this->expectExceptionMessage('Data 區塊');

        $payload = [
            'MerchantID' => self::TEST_MERCHANT_ID,
        ];

        $this->encoder->encodePayload($payload);
    }

    /**
     * @test
     */
    public function 可以正確解碼Data(): void
    {
        $originalData = [
            'MerchantID' => self::TEST_MERCHANT_ID,
            'RelateNumber' => 'TEST123',
            'Amount' => 1000,
        ];

        // 先編碼
        $payload = ['Data' => $originalData];
        $encoded = $this->encoder->encodePayload($payload);

        // 再解碼
        $decoded = $this->encoder->decodeData($encoded['Data']);

        $this->assertEquals($originalData, $decoded);
    }

    /**
     * @test
     */
    public function 解碼無效JSON應拋出例外(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('JSON 格式無效');

        // 加密一個非 JSON 字串
        $invalidData = $this->encoder->encrypt('not a json');
        $this->encoder->decodeData($invalidData);
    }

    /**
     * @test
     */
    public function 可以直接加密字串(): void
    {
        $plainText = 'test string';
        $encrypted = $this->encoder->encrypt($plainText);

        $this->assertNotEmpty($encrypted);
        $this->assertNotEquals($plainText, $encrypted);
    }

    /**
     * @test
     */
    public function 可以直接解密字串(): void
    {
        $plainText = 'test string';
        $encrypted = $this->encoder->encrypt($plainText);
        $decrypted = $this->encoder->decrypt($encrypted);

        $this->assertEquals($plainText, $decrypted);
    }

    /**
     * @test
     */
    public function 編碼解碼應保持資料完整(): void
    {
        $testData = [
            'string' => '測試字串',
            'number' => 12345,
            'float' => 123.45,
            'boolean' => true,
            'null' => null,
            'array' => [1, 2, 3],
            'nested' => [
                'key' => 'value',
            ],
        ];

        $payload = ['Data' => $testData];
        $encoded = $this->encoder->encodePayload($payload);
        $decoded = $this->encoder->decodeData($encoded['Data']);

        $this->assertEquals($testData, $decoded);
    }
}
