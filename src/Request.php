<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core;

use CarlLee\EcPay\Core\Exceptions\ApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * HTTP 請求處理類別。
 *
 * 根據綠界 API 介接注意事項：
 * - 僅支援 HTTPS (443 port) 連線
 * - 使用 HTTP POST 方式傳送
 * - 支援 TLS 1.1 以上加密通訊協定
 */
class Request
{
    /**
     * 最低支援的 TLS 版本（TLS 1.1）。
     */
    public const int MIN_TLS_VERSION = CURL_SSLVERSION_TLSv1_1;

    /**
     * 預設請求逾時時間（秒）。
     */
    public const int DEFAULT_TIMEOUT = 30;

    /**
     * 預設連線逾時時間（秒）。
     */
    public const int DEFAULT_CONNECT_TIMEOUT = 10;

    /**
     * 請求 URL。
     *
     * @var string
     */
    protected string $url = '';

    /**
     * 請求內容。
     *
     * @var array<string, mixed>
     */
    protected array $content = [];

    /**
     * HTTP 客戶端實例。
     *
     * @var Client|null
     */
    protected static ?Client $client = null;

    /**
     * 是否啟用 SSL 驗證。
     *
     * @var bool
     */
    protected static bool $verifySsl = true;

    /**
     * 設定 HTTP 客戶端實例。
     *
     * @param Client|null $client
     */
    public static function setHttpClient(?Client $client): void
    {
        self::$client = $client;
    }

    /**
     * 取得 HTTP 客戶端實例。
     *
     * @return Client|null
     */
    public static function getHttpClient(): ?Client
    {
        return self::$client;
    }

    /**
     * 設定是否啟用 SSL 驗證。
     *
     * @param bool $verify
     */
    public static function setVerifySsl(bool $verify): void
    {
        self::$verifySsl = $verify;
    }

    /**
     * 取得 SSL 驗證設定。
     *
     * @return bool
     */
    public static function getVerifySsl(): bool
    {
        return self::$verifySsl;
    }

    /**
     * 重置靜態設定。
     */
    public static function reset(): void
    {
        self::$client = null;
        self::$verifySsl = true;
    }

    /**
     * 建立 Request 實例。
     *
     * @param string $url 請求 URL
     * @param array<string, mixed> $content 請求內容
     */
    public function __construct(string $url = '', array $content = [])
    {
        $this->url = $url;
        $this->content = $content;
    }

    /**
     * 發送請求至綠界伺服器。
     *
     * @param string $url 請求 URL（可選，覆蓋建構時設定）
     * @param array<string, mixed> $content 請求內容（可選，覆蓋建構時設定）
     * @return array<string, mixed> 回應資料
     * @throws ApiException 當請求失敗時
     */
    public function send(string $url = '', array $content = []): array
    {
        try {
            if (self::$client === null) {
                self::$client = $this->createDefaultClient();
            }

            $sendContent = $content ?: $this->content;
            $response = self::$client->request(
                'POST',
                $url ?: $this->url,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'body' => json_encode($sendContent),
                ]
            );

            $body = (string) $response->getBody();
            $decoded = json_decode($body, true);

            if (!is_array($decoded)) {
                throw ApiException::invalidResponse('回應不是有效的 JSON');
            }

            return $decoded;
        } catch (RequestException $exception) {
            if ($exception->hasResponse()) {
                $response = $exception->getResponse();
                if ($response !== null) {
                    throw ApiException::requestFailed(
                        $response->getBody()->getContents(),
                        $exception
                    );
                }
            }

            throw ApiException::requestFailed($exception->getMessage(), $exception);
        }
    }

    /**
     * 建立預設的 HTTP 客戶端。
     *
     * @return Client
     */
    protected function createDefaultClient(): Client
    {
        return new Client([
            'verify' => self::$verifySsl,
            'curl' => [
                CURLOPT_SSLVERSION => self::MIN_TLS_VERSION,
            ],
            'timeout' => self::DEFAULT_TIMEOUT,
            'connect_timeout' => self::DEFAULT_CONNECT_TIMEOUT,
        ]);
    }

    /**
     * 取得請求 URL。
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * 設定請求 URL。
     *
     * @param string $url
     * @return static
     */
    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * 取得請求內容。
     *
     * @return array<string, mixed>
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * 設定請求內容。
     *
     * @param array<string, mixed> $content
     * @return static
     */
    public function setContent(array $content): static
    {
        $this->content = $content;

        return $this;
    }
}
