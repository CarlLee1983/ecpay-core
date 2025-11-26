<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Exceptions;

use Exception;
use Throwable;

/**
 * 綠界 ECPay SDK 基礎例外類別。
 *
 * 所有套件相關例外的父類別，提供額外的上下文資訊功能。
 */
class EcPayException extends Exception
{
    /**
     * 額外的錯誤上下文資訊。
     *
     * @var array<string, mixed>
     */
    protected array $context = [];

    /**
     * 建立例外實例。
     *
     * @param string $message 錯誤訊息
     * @param int $code 錯誤代碼
     * @param Throwable|null $previous 前一個例外
     * @param array<string, mixed> $context 額外上下文資訊
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * 取得錯誤上下文資訊。
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * 設定錯誤上下文資訊。
     *
     * @param array<string, mixed> $context
     * @return static
     */
    public function setContext(array $context): static
    {
        $this->context = $context;

        return $this;
    }

    /**
     * 新增上下文資訊。
     *
     * @param string $key
     * @param mixed $value
     * @return static
     */
    public function addContext(string $key, mixed $value): static
    {
        $this->context[$key] = $value;

        return $this;
    }
}
