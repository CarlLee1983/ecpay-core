<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core;

use CarlLee\EcPay\Core\Contracts\ContentInterface;
use CarlLee\EcPay\Core\Contracts\PayloadEncoderInterface;
use CarlLee\EcPay\Core\DTO\RqHeaderDto;
use CarlLee\EcPay\Core\Exceptions\EncryptionException;
use CarlLee\EcPay\Core\Exceptions\ValidationException;
use CarlLee\EcPay\Core\Infrastructure\CipherService;
use CarlLee\EcPay\Core\Infrastructure\PayloadEncoder;

/**
 * 所有 Content 類別的抽象基礎類別。
 *
 * 提供 API 操作物件的共用屬性和方法。
 */
abstract class AbstractContent implements ContentInterface
{
    /**
     * RqID 隨機字串長度。
     */
    public const int RQID_RANDOM_LENGTH = 5;

    /**
     * API 請求路徑。
     *
     * @var string
     */
    protected string $requestPath = '';

    /**
     * 商店代號。
     *
     * @var string
     */
    protected string $merchantID = '';

    /**
     * HashKey。
     *
     * @var string
     */
    protected string $hashKey = '';

    /**
     * HashIV。
     *
     * @var string
     */
    protected string $hashIV = '';

    /**
     * 內容資料。
     *
     * @var array<string, mixed>
     */
    protected array $content = [];

    /**
     * 請求標頭 DTO。
     *
     * @var RqHeaderDto
     */
    protected RqHeaderDto $rqHeader;

    /**
     * Payload 編碼器。
     *
     * @var PayloadEncoderInterface|null
     */
    protected ?PayloadEncoderInterface $payloadEncoder = null;

    /**
     * 建立 Content 實例。
     *
     * @param string $merchantId 商店代號
     * @param string $hashKey HashKey
     * @param string $hashIV HashIV
     */
    public function __construct(string $merchantId = '', string $hashKey = '', string $hashIV = '')
    {
        $this->setMerchantID($merchantId);
        $this->setHashKey($hashKey);
        $this->setHashIV($hashIV);

        $this->rqHeader = new RqHeaderDto();

        $this->content = [
            'MerchantID' => $this->merchantID,
            'RqHeader' => $this->rqHeader->toPayload(),
        ];

        $this->initContent();
    }

    /**
     * 初始化內容。
     *
     * 子類別可覆寫此方法來初始化特定內容。
     */
    protected function initContent(): void
    {
        // 子類別實作
    }

    /**
     * 驗證資料。
     *
     * 子類別應實作此方法來驗證特定欄位。
     *
     * @throws ValidationException 當驗證失敗時
     */
    abstract protected function validation(): void;

    /**
     * @inheritDoc
     */
    public function getRequestPath(): string
    {
        return $this->requestPath;
    }

    /**
     * 設定商店代號。
     *
     * @param string $id 商店代號
     * @return static
     */
    public function setMerchantID(string $id): static
    {
        $this->merchantID = $id;

        return $this;
    }

    /**
     * 取得商店代號。
     *
     * @return string
     */
    public function getMerchantID(): string
    {
        return $this->merchantID;
    }

    /**
     * @inheritDoc
     */
    public function setHashKey(string $key): static
    {
        $this->hashKey = $key;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setHashIV(string $iv): static
    {
        $this->hashIV = $iv;

        return $this;
    }

    /**
     * 取得 RqHeader DTO。
     *
     * @return RqHeaderDto
     */
    public function getRqHeader(): RqHeaderDto
    {
        return $this->rqHeader;
    }

    /**
     * 設定 RqHeader DTO。
     *
     * @param RqHeaderDto $rqHeader
     * @return static
     */
    public function setRqHeader(RqHeaderDto $rqHeader): static
    {
        $this->rqHeader = $rqHeader;
        $this->syncRqHeader();

        return $this;
    }

    /**
     * 設定自訂的 PayloadEncoder。
     *
     * @param PayloadEncoderInterface $payloadEncoder
     * @return static
     */
    public function setPayloadEncoder(PayloadEncoderInterface $payloadEncoder): static
    {
        $this->payloadEncoder = $payloadEncoder;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        $this->validation();
        $this->syncRqHeader();

        return $this->content;
    }

    /**
     * 取得加密後的內容。
     *
     * @return array<string, mixed>
     */
    public function getContent(): array
    {
        $payload = $this->getPayload();
        $encoder = $this->getPayloadEncoder();

        return $encoder->encodePayload($payload);
    }

    /**
     * @inheritDoc
     */
    public function getPayloadEncoder(): PayloadEncoderInterface
    {
        return $this->payloadEncoder ?? $this->buildPayloadEncoder();
    }

    /**
     * 產生預設的 PayloadEncoder。
     *
     * @return PayloadEncoder
     * @throws EncryptionException 當金鑰無效時
     */
    protected function buildPayloadEncoder(): PayloadEncoder
    {
        $this->validateCredentials();

        return new PayloadEncoder(
            new CipherService($this->hashKey, $this->hashIV)
        );
    }

    /**
     * 驗證基礎參數。
     *
     * @param bool $requireCredentials 是否需要驗證金鑰
     * @throws ValidationException 當 MerchantID 為空時
     * @throws EncryptionException 當金鑰無效時
     */
    protected function validatorBaseParam(bool $requireCredentials = false): void
    {
        if (empty($this->content['MerchantID']) || empty($this->content['Data']['MerchantID'])) {
            throw ValidationException::requiredField('MerchantID');
        }

        if ($requireCredentials) {
            $this->validateCredentials();
        }
    }

    /**
     * 驗證加密金鑰。
     *
     * @throws EncryptionException 當金鑰無效時
     */
    protected function validateCredentials(): void
    {
        if (empty($this->hashKey)) {
            throw EncryptionException::invalidKey('HashKey');
        }

        if (empty($this->hashIV)) {
            throw EncryptionException::invalidKey('HashIV');
        }
    }

    /**
     * 同步 RqHeader 至內容陣列。
     */
    protected function syncRqHeader(): void
    {
        $this->content['RqHeader'] = $this->rqHeader->toPayload();
    }

    /**
     * 產生 RqID。
     *
     * @return string
     */
    protected function getRqID(): string
    {
        [$usec, $sec] = explode(' ', microtime());
        $usec = str_replace('.', '', $usec);

        return $sec . $this->randomString(self::RQID_RANDOM_LENGTH) . $usec . $this->randomString(self::RQID_RANDOM_LENGTH);
    }

    /**
     * 產生隨機字串。
     *
     * @param int $length 長度
     * @return string
     */
    protected function randomString(int $length = 32): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        if ($length < 0) {
            return '';
        }

        $charactersLength = strlen($characters) - 1;
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[random_int(0, $charactersLength)];
        }

        return $string;
    }

    /**
     * 與 .NET 相容的 URL encode 轉換。
     *
     * @param string $param
     * @return string
     */
    protected function transUrlencode(string $param): string
    {
        $search = ['%2d', '%5f', '%2e', '%21', '%2a', '%28', '%29'];
        $replace = ['-', '_', '.', '!', '*', '(', ')'];

        return str_replace($search, $replace, $param);
    }
}
