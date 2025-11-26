<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Tests\Unit;

use CarlLee\EcPay\Core\AbstractContent;
use CarlLee\EcPay\Core\Contracts\PayloadEncoderInterface;
use CarlLee\EcPay\Core\DTO\RqHeaderDto;
use CarlLee\EcPay\Core\Exceptions\EncryptionException;
use CarlLee\EcPay\Core\Exceptions\ValidationException;
use CarlLee\EcPay\Core\Infrastructure\CipherService;
use CarlLee\EcPay\Core\Infrastructure\PayloadEncoder;
use CarlLee\EcPay\Core\Tests\TestCase;

/**
 * AbstractContent 單元測試。
 */
class AbstractContentTest extends TestCase
{
    /**
     * @test
     */
    public function 可以建立實例並設定憑證(): void
    {
        $content = $this->createConcreteContent(
            self::TEST_MERCHANT_ID,
            self::TEST_HASH_KEY,
            self::TEST_HASH_IV
        );

        $this->assertEquals(self::TEST_MERCHANT_ID, $content->getMerchantID());
    }

    /**
     * @test
     */
    public function 可以設定MerchantID(): void
    {
        $content = $this->createConcreteContent();
        $result = $content->setMerchantID('NEW_MERCHANT');

        $this->assertSame($content, $result); // fluent interface
        $this->assertEquals('NEW_MERCHANT', $content->getMerchantID());
    }

    /**
     * @test
     */
    public function 可以設定HashKey(): void
    {
        $content = $this->createConcreteContent();
        $result = $content->setHashKey('NEW_HASH_KEY');

        $this->assertSame($content, $result); // fluent interface
    }

    /**
     * @test
     */
    public function 可以設定HashIV(): void
    {
        $content = $this->createConcreteContent();
        $result = $content->setHashIV('NEW_HASH_IV');

        $this->assertSame($content, $result); // fluent interface
    }

    /**
     * @test
     */
    public function 可以取得RqHeader(): void
    {
        $content = $this->createConcreteContent();
        $rqHeader = $content->getRqHeader();

        $this->assertInstanceOf(RqHeaderDto::class, $rqHeader);
    }

    /**
     * @test
     */
    public function 可以設定RqHeader(): void
    {
        $content = $this->createConcreteContent();
        $newRqHeader = new RqHeaderDto(1700000000);

        $result = $content->setRqHeader($newRqHeader);

        $this->assertSame($content, $result); // fluent interface
        $this->assertSame($newRqHeader, $content->getRqHeader());
    }

    /**
     * @test
     */
    public function 可以取得RequestPath(): void
    {
        $content = $this->createConcreteContent();

        $this->assertEquals('/test/path', $content->getRequestPath());
    }

    /**
     * @test
     */
    public function getPayload應回傳包含MerchantID和RqHeader的陣列(): void
    {
        $content = $this->createConcreteContent(
            self::TEST_MERCHANT_ID,
            self::TEST_HASH_KEY,
            self::TEST_HASH_IV
        );

        $payload = $content->getPayload();

        $this->assertArrayHasKey('MerchantID', $payload);
        $this->assertArrayHasKey('RqHeader', $payload);
        $this->assertEquals(self::TEST_MERCHANT_ID, $payload['MerchantID']);
    }

    /**
     * @test
     */
    public function 可以取得PayloadEncoder(): void
    {
        $content = $this->createConcreteContent(
            self::TEST_MERCHANT_ID,
            self::TEST_HASH_KEY,
            self::TEST_HASH_IV
        );

        $encoder = $content->getPayloadEncoder();

        $this->assertInstanceOf(PayloadEncoderInterface::class, $encoder);
        $this->assertInstanceOf(PayloadEncoder::class, $encoder);
    }

    /**
     * @test
     */
    public function 可以設定自訂PayloadEncoder(): void
    {
        $content = $this->createConcreteContent();
        $customEncoder = new PayloadEncoder(
            new CipherService(self::TEST_HASH_KEY, self::TEST_HASH_IV)
        );

        $result = $content->setPayloadEncoder($customEncoder);

        $this->assertSame($content, $result); // fluent interface
        $this->assertSame($customEncoder, $content->getPayloadEncoder());
    }

    /**
     * @test
     */
    public function getContent應回傳加密後的資料(): void
    {
        $content = $this->createConcreteContent(
            self::TEST_MERCHANT_ID,
            self::TEST_HASH_KEY,
            self::TEST_HASH_IV
        );

        $encrypted = $content->getContent();

        $this->assertArrayHasKey('MerchantID', $encrypted);
        $this->assertArrayHasKey('Data', $encrypted);
        // Data 應該是加密後的字串
        $this->assertIsString($encrypted['Data']);
    }

    /**
     * @test
     */
    public function 沒有HashKey應拋出例外(): void
    {
        $this->expectException(EncryptionException::class);
        $this->expectExceptionMessage('HashKey');

        $content = $this->createConcreteContent(self::TEST_MERCHANT_ID, '', self::TEST_HASH_IV);
        $content->getPayloadEncoder();
    }

    /**
     * @test
     */
    public function 沒有HashIV應拋出例外(): void
    {
        $this->expectException(EncryptionException::class);
        $this->expectExceptionMessage('HashIV');

        $content = $this->createConcreteContent(self::TEST_MERCHANT_ID, self::TEST_HASH_KEY, '');
        $content->getPayloadEncoder();
    }

    /**
     * @test
     */
    public function RQID_RANDOM_LENGTH常數應為5(): void
    {
        $this->assertEquals(5, AbstractContent::RQID_RANDOM_LENGTH);
    }

    /**
     * 建立具體的 Content 實作用於測試。
     *
     * @param string $merchantId
     * @param string $hashKey
     * @param string $hashIV
     * @return AbstractContent
     */
    private function createConcreteContent(
        string $merchantId = '',
        string $hashKey = '',
        string $hashIV = ''
    ): AbstractContent {
        return new class ($merchantId, $hashKey, $hashIV) extends AbstractContent {
            protected string $requestPath = '/test/path';

            protected function initContent(): void
            {
                $this->content['Data'] = [
                    'MerchantID' => $this->merchantID,
                    'TestField' => 'test_value',
                ];
            }

            protected function validation(): void
            {
                // 無額外驗證
            }
        };
    }
}
