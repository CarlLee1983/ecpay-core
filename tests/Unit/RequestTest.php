<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Tests\Unit;

use CarlLee\EcPay\Core\Request;
use CarlLee\EcPay\Core\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * Request 單元測試。
 */
class RequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Request::reset();
    }

    protected function tearDown(): void
    {
        Request::reset();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function 可以建立Request實例(): void
    {
        $request = new Request('https://example.com', ['key' => 'value']);

        $this->assertEquals('https://example.com', $request->getUrl());
        $this->assertEquals(['key' => 'value'], $request->getContent());
    }

    /**
     * @test
     */
    public function 可以設定URL(): void
    {
        $request = new Request();
        $result = $request->setUrl('https://example.com');

        $this->assertSame($request, $result); // fluent interface
        $this->assertEquals('https://example.com', $request->getUrl());
    }

    /**
     * @test
     */
    public function 可以設定Content(): void
    {
        $request = new Request();
        $content = ['key' => 'value'];

        $result = $request->setContent($content);

        $this->assertSame($request, $result); // fluent interface
        $this->assertEquals($content, $request->getContent());
    }

    /**
     * @test
     */
    public function 可以設定HttpClient(): void
    {
        $client = new Client();
        Request::setHttpClient($client);

        $this->assertSame($client, Request::getHttpClient());
    }

    /**
     * @test
     */
    public function 可以設定VerifySsl(): void
    {
        Request::setVerifySsl(false);
        $this->assertFalse(Request::getVerifySsl());

        Request::setVerifySsl(true);
        $this->assertTrue(Request::getVerifySsl());
    }

    /**
     * @test
     */
    public function reset應清除靜態設定(): void
    {
        $client = new Client();
        Request::setHttpClient($client);
        Request::setVerifySsl(false);

        Request::reset();

        $this->assertNull(Request::getHttpClient());
        $this->assertTrue(Request::getVerifySsl());
    }

    /**
     * @test
     */
    public function MIN_TLS_VERSION常數應正確設定(): void
    {
        $this->assertEquals(CURL_SSLVERSION_TLSv1_1, Request::MIN_TLS_VERSION);
    }

    /**
     * @test
     */
    public function DEFAULT_TIMEOUT常數應為30(): void
    {
        $this->assertEquals(30, Request::DEFAULT_TIMEOUT);
    }

    /**
     * @test
     */
    public function DEFAULT_CONNECT_TIMEOUT常數應為10(): void
    {
        $this->assertEquals(10, Request::DEFAULT_CONNECT_TIMEOUT);
    }

    /**
     * @test
     */
    public function send應發送POST請求並回傳陣列(): void
    {
        $responseData = ['RtnCode' => 1, 'RtnMsg' => '成功'];

        $mock = new MockHandler([
            new Response(200, [], json_encode($responseData)),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        Request::setHttpClient($client);

        $request = new Request('https://example.com', ['test' => 'data']);
        $result = $request->send();

        $this->assertEquals($responseData, $result);
    }

    /**
     * @test
     */
    public function send可以覆蓋URL和Content(): void
    {
        $responseData = ['RtnCode' => 1];

        $mock = new MockHandler([
            new Response(200, [], json_encode($responseData)),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        Request::setHttpClient($client);

        $request = new Request('https://original.com', ['original' => 'data']);
        $result = $request->send('https://override.com', ['override' => 'data']);

        $this->assertEquals($responseData, $result);
    }
}
