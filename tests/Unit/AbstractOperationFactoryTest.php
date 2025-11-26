<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Tests\Unit;

use CarlLee\EcPay\Core\AbstractContent;
use CarlLee\EcPay\Core\AbstractOperationFactory;
use CarlLee\EcPay\Core\Contracts\ContentInterface;
use CarlLee\EcPay\Core\Tests\TestCase;
use InvalidArgumentException;

/**
 * AbstractOperationFactory 單元測試。
 */
class AbstractOperationFactoryTest extends TestCase
{
    private AbstractOperationFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = $this->createConcreteFactory([
            'merchant_id' => self::TEST_MERCHANT_ID,
            'hash_key' => self::TEST_HASH_KEY,
            'hash_iv' => self::TEST_HASH_IV,
        ]);
    }

    /**
     * @test
     */
    public function 可以設定憑證(): void
    {
        $factory = $this->createConcreteFactory();
        $factory->setCredentials('merchant', 'key', 'iv');

        $credentials = $factory->getCredentials();

        $this->assertEquals('merchant', $credentials['merchant_id']);
        $this->assertEquals('key', $credentials['hash_key']);
        $this->assertEquals('iv', $credentials['hash_iv']);
    }

    /**
     * @test
     */
    public function make應建立Content實例(): void
    {
        // 使用完整類別名稱來測試
        $content = $this->factory->make(TestOperation::class);

        $this->assertInstanceOf(ContentInterface::class, $content);
    }

    /**
     * @test
     */
    public function make應使用預設憑證(): void
    {
        // 使用完整類別名稱來測試
        $content = $this->factory->make(TestOperation::class);

        $this->assertInstanceOf(AbstractContent::class, $content);
        $this->assertEquals(self::TEST_MERCHANT_ID, $content->getMerchantID());
    }

    /**
     * @test
     */
    public function make應支援別名對應到完整類別(): void
    {
        // 使用別名對應到完整類別
        $this->factory->alias('test-op', TestOperation::class);
        $content = $this->factory->make('test-op');

        $this->assertInstanceOf(ContentInterface::class, $content);
    }

    /**
     * @test
     */
    public function make應支援別名(): void
    {
        $factory = $this->createConcreteFactory([
            'merchant_id' => self::TEST_MERCHANT_ID,
            'hash_key' => self::TEST_HASH_KEY,
            'hash_iv' => self::TEST_HASH_IV,
            'aliases' => [
                'my-alias' => TestOperation::class,
            ],
        ]);

        $content = $factory->make('my-alias');

        $this->assertInstanceOf(TestOperation::class, $content);
    }

    /**
     * @test
     */
    public function alias方法應註冊別名(): void
    {
        $this->factory->alias('custom', TestOperation::class);

        $content = $this->factory->make('custom');

        $this->assertInstanceOf(TestOperation::class, $content);
    }

    /**
     * @test
     */
    public function extend方法應註冊自訂解析器(): void
    {
        $this->factory->extend('custom-resolver', function (array $params, $factory) {
            return new TestOperation('custom', 'key', 'iv');
        });

        $content = $this->factory->make('custom-resolver');

        $this->assertInstanceOf(TestOperation::class, $content);
        $this->assertEquals('custom', $content->getMerchantID());
    }

    /**
     * @test
     */
    public function extend解析器必須回傳ContentInterface(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ContentInterface');

        $this->factory->extend('bad-resolver', function () {
            return 'not a content';
        });

        $this->factory->make('bad-resolver');
    }

    /**
     * @test
     */
    public function addInitializer應在建立時執行初始化邏輯(): void
    {
        $called = false;
        $passedContent = null;

        $this->factory->addInitializer(function (ContentInterface $content) use (&$called, &$passedContent) {
            $called = true;
            $passedContent = $content;
        });

        $content = $this->factory->make(TestOperation::class);

        $this->assertTrue($called);
        $this->assertSame($content, $passedContent);
    }

    /**
     * @test
     */
    public function 可以使用完整類別名稱建立(): void
    {
        $content = $this->factory->make(TestOperation::class);

        $this->assertInstanceOf(TestOperation::class, $content);
    }

    /**
     * @test
     */
    public function 找不到類別應拋出例外(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('找不到');

        $this->factory->make('NonExistentOperation');
    }

    /**
     * @test
     */
    public function 空別名應拋出例外(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('空字串');

        $this->factory->make('');
    }

    /**
     * @test
     */
    public function 別名格式錯誤應拋出例外(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('實際類別名稱');

        $this->factory->make('operations.');
    }

    /**
     * @test
     */
    public function make應支援大小寫不敏感的別名查詢(): void
    {
        // 註冊別名
        $this->factory->alias('TestOp', TestOperation::class);

        // 大小寫不敏感的查詢
        $content1 = $this->factory->make('TestOp');
        $content2 = $this->factory->make('testop');
        $content3 = $this->factory->make('TESTOP');

        $this->assertInstanceOf(ContentInterface::class, $content1);
        $this->assertInstanceOf(ContentInterface::class, $content2);
        $this->assertInstanceOf(ContentInterface::class, $content3);
    }

    /**
     * 建立具體的工廠實作用於測試。
     *
     * @param array $config
     * @return AbstractOperationFactory
     */
    private function createConcreteFactory(array $config = []): AbstractOperationFactory
    {
        return new class ($config) extends AbstractOperationFactory {
            protected function getBaseNamespace(): string
            {
                return 'CarlLee\\EcPay\\Core\\Tests\\Unit';
            }

            protected function getContentClass(): string
            {
                return TestContent::class;
            }
        };
    }
}

/**
 * 測試用的 Content 基礎類別。
 */
class TestContent extends AbstractContent
{
    protected string $requestPath = '/test';

    protected function initContent(): void
    {
        $this->content['Data'] = ['MerchantID' => $this->merchantID];
    }

    protected function validation(): void
    {
        // 無驗證
    }
}

/**
 * 測試用的具體 Operation。
 */
class TestOperation extends TestContent
{
    protected string $requestPath = '/test/operation';
}
