<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Tests\Unit;

use CarlLee\EcPay\Core\Exceptions\ApiException;
use CarlLee\EcPay\Core\Response;
use CarlLee\EcPay\Core\Tests\TestCase;

/**
 * Response 單元測試。
 */
class ResponseTest extends TestCase
{
    /**
     * @test
     */
    public function 可以建立空的Response(): void
    {
        $response = new Response();

        $this->assertEquals(0, $response->getCode());
        $this->assertEquals('', $response->getMessage());
        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
    }

    /**
     * @test
     */
    public function 可以使用資料建立Response(): void
    {
        $data = [
            'RtnCode' => 1,
            'RtnMsg' => '成功',
        ];

        $response = new Response($data);

        $this->assertEquals(1, $response->getCode());
        $this->assertEquals('成功', $response->getMessage());
        $this->assertTrue($response->isSuccess());
        $this->assertFalse($response->isError());
    }

    /**
     * @test
     */
    public function 可以使用fromArray靜態方法建立(): void
    {
        $data = ['RtnCode' => 1, 'RtnMsg' => '成功'];
        $response = Response::fromArray($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccess());
    }

    /**
     * @test
     */
    public function 可以設定資料(): void
    {
        $response = new Response();
        $data = ['RtnCode' => 1, 'RtnMsg' => '成功'];

        $result = $response->setData($data);

        $this->assertSame($response, $result); // fluent interface
        $this->assertEquals($data, $response->getData());
    }

    /**
     * @test
     */
    public function success方法應正確判斷成功狀態(): void
    {
        $successResponse = new Response(['RtnCode' => 1]);
        $failResponse = new Response(['RtnCode' => 0]);
        $errorResponse = new Response(['RtnCode' => 10000001]);

        $this->assertTrue($successResponse->success());
        $this->assertFalse($failResponse->success());
        $this->assertFalse($errorResponse->success());
    }

    /**
     * @test
     */
    public function isSuccess應與success相同(): void
    {
        $response = new Response(['RtnCode' => 1]);

        $this->assertEquals($response->success(), $response->isSuccess());
    }

    /**
     * @test
     */
    public function isError應與success相反(): void
    {
        $successResponse = new Response(['RtnCode' => 1]);
        $errorResponse = new Response(['RtnCode' => 0]);

        $this->assertFalse($successResponse->isError());
        $this->assertTrue($errorResponse->isError());
    }

    /**
     * @test
     */
    public function 可以取得回應資料(): void
    {
        $data = [
            'RtnCode' => 1,
            'RtnMsg' => '成功',
            'Data' => ['InvoiceNo' => 'AB12345678'],
        ];

        $response = new Response($data);

        $this->assertEquals($data, $response->getData());
    }

    /**
     * @test
     */
    public function 可以取得解密後的Data欄位(): void
    {
        $data = [
            'RtnCode' => 1,
            'RtnMsg' => '成功',
            'Data' => ['InvoiceNo' => 'AB12345678'],
        ];

        $response = new Response($data);

        $this->assertEquals(['InvoiceNo' => 'AB12345678'], $response->getDecodedData());
    }

    /**
     * @test
     */
    public function getDecodedData無Data時應回傳null(): void
    {
        $response = new Response(['RtnCode' => 1, 'RtnMsg' => '成功']);

        $this->assertNull($response->getDecodedData());
    }

    /**
     * @test
     */
    public function 可以取得指定欄位(): void
    {
        $response = new Response([
            'RtnCode' => 1,
            'CustomField' => 'custom_value',
        ]);

        $this->assertEquals('custom_value', $response->get('CustomField'));
        $this->assertNull($response->get('NonExistent'));
        $this->assertEquals('default', $response->get('NonExistent', 'default'));
    }

    /**
     * @test
     */
    public function 可以檢查欄位是否存在(): void
    {
        $response = new Response([
            'RtnCode' => 1,
            'Exists' => null,
        ]);

        $this->assertTrue($response->has('RtnCode'));
        $this->assertTrue($response->has('Exists')); // 即使值為 null
        $this->assertFalse($response->has('NotExists'));
    }

    /**
     * @test
     */
    public function 可以轉換為陣列(): void
    {
        $data = ['RtnCode' => 1, 'RtnMsg' => '成功'];
        $response = new Response($data);

        $this->assertEquals($data, $response->toArray());
    }

    /**
     * @test
     */
    public function 可以轉換為JSON(): void
    {
        $data = ['RtnCode' => 1, 'RtnMsg' => '成功'];
        $response = new Response($data);

        $json = $response->toJson();

        $this->assertJson($json);
        $this->assertEquals($data, json_decode($json, true));
    }

    /**
     * @test
     */
    public function toJson可以指定選項(): void
    {
        $data = ['RtnCode' => 1, 'RtnMsg' => '成功'];
        $response = new Response($data);

        $json = $response->toJson(JSON_PRETTY_PRINT);

        $this->assertStringContainsString("\n", $json);
    }

    /**
     * @test
     */
    public function throw方法成功時應回傳自身(): void
    {
        $response = new Response(['RtnCode' => 1, 'RtnMsg' => '成功']);

        $result = $response->throw();

        $this->assertSame($response, $result);
    }

    /**
     * @test
     */
    public function throw方法錯誤時應拋出例外(): void
    {
        $response = new Response(['RtnCode' => 10000001, 'RtnMsg' => '錯誤訊息']);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('錯誤訊息');

        $response->throw();
    }

    /**
     * @test
     */
    public function onError成功時不應執行回呼(): void
    {
        $response = new Response(['RtnCode' => 1]);
        $called = false;

        $result = $response->onError(function () use (&$called) {
            $called = true;
        });

        $this->assertSame($response, $result);
        $this->assertFalse($called);
    }

    /**
     * @test
     */
    public function onError錯誤時應執行回呼(): void
    {
        $response = new Response(['RtnCode' => 0]);
        $called = false;
        $passedResponse = null;

        $response->onError(function ($r) use (&$called, &$passedResponse) {
            $called = true;
            $passedResponse = $r;
        });

        $this->assertTrue($called);
        $this->assertSame($response, $passedResponse);
    }

    /**
     * @test
     */
    public function onSuccess成功時應執行回呼(): void
    {
        $response = new Response(['RtnCode' => 1]);
        $called = false;
        $passedResponse = null;

        $result = $response->onSuccess(function ($r) use (&$called, &$passedResponse) {
            $called = true;
            $passedResponse = $r;
        });

        $this->assertSame($response, $result);
        $this->assertTrue($called);
        $this->assertSame($response, $passedResponse);
    }

    /**
     * @test
     */
    public function onSuccess錯誤時不應執行回呼(): void
    {
        $response = new Response(['RtnCode' => 0]);
        $called = false;

        $response->onSuccess(function () use (&$called) {
            $called = true;
        });

        $this->assertFalse($called);
    }

    /**
     * @test
     */
    public function SUCCESS_CODE常數應為1(): void
    {
        $this->assertEquals(1, Response::SUCCESS_CODE);
    }
}
