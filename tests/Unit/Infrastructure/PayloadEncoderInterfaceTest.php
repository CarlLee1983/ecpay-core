<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Tests\Unit\Infrastructure;

use CarlLee\EcPay\Core\Contracts\PayloadEncoderInterface;
use CarlLee\EcPay\Core\Infrastructure\CipherService;
use CarlLee\EcPay\Core\Infrastructure\PayloadEncoder;
use CarlLee\EcPay\Core\Tests\TestCase;

/**
 * PayloadEncoderInterface 實作測試。
 */
class PayloadEncoderInterfaceTest extends TestCase
{
    private PayloadEncoderInterface $encoder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->encoder = new PayloadEncoder(
            new CipherService(self::TEST_HASH_KEY, self::TEST_HASH_IV)
        );
    }

    /**
     * @test
     */
    public function PayloadEncoder應實作PayloadEncoderInterface(): void
    {
        $this->assertInstanceOf(PayloadEncoderInterface::class, $this->encoder);
    }

    /**
     * @test
     */
    public function verifyResponse應在Data可解密時回傳true(): void
    {
        // 先編碼一個有效的 payload
        $payload = [
            'Data' => [
                'MerchantID' => self::TEST_MERCHANT_ID,
                'TestField' => 'test_value',
            ],
        ];

        $encoded = $this->encoder->encodePayload($payload);

        // 模擬 API 回應
        $response = [
            'RtnCode' => 1,
            'RtnMsg' => '成功',
            'Data' => $encoded['Data'],
        ];

        $this->assertTrue($this->encoder->verifyResponse($response));
    }

    /**
     * @test
     */
    public function verifyResponse應在Data無法解密時回傳false(): void
    {
        $response = [
            'RtnCode' => 1,
            'RtnMsg' => '成功',
            'Data' => 'invalid_encrypted_data',
        ];

        $this->assertFalse($this->encoder->verifyResponse($response));
    }

    /**
     * @test
     */
    public function verifyResponse應在缺少Data時回傳false(): void
    {
        $response = [
            'RtnCode' => 1,
            'RtnMsg' => '成功',
        ];

        $this->assertFalse($this->encoder->verifyResponse($response));
    }

    /**
     * @test
     */
    public function verifyResponse應在Data不是字串時回傳false(): void
    {
        $response = [
            'RtnCode' => 1,
            'Data' => ['not' => 'a string'],
        ];

        $this->assertFalse($this->encoder->verifyResponse($response));
    }

    /**
     * @test
     */
    public function verifyResponse應在Data為null時回傳false(): void
    {
        $response = [
            'RtnCode' => 1,
            'Data' => null,
        ];

        $this->assertFalse($this->encoder->verifyResponse($response));
    }
}
