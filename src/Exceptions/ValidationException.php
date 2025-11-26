<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Exceptions;

/**
 * 驗證錯誤例外。
 *
 * 當資料驗證失敗時拋出。
 */
class ValidationException extends EcPayException
{
    /**
     * 驗證錯誤清單。
     *
     * @var array<string, array<string>>
     */
    protected array $errors = [];

    /**
     * 驗證失敗的欄位名稱。
     *
     * @var string|null
     */
    protected ?string $field = null;

    /**
     * 建立驗證例外。
     *
     * @param string $message 錯誤訊息
     * @param string|null $field 失敗的欄位名稱
     * @param array<string, mixed> $context 額外上下文資訊
     * @return static
     */
    public static function make(
        string $message,
        ?string $field = null,
        array $context = []
    ): static {
        $exception = new static($message, 0, null, $context);
        $exception->field = $field;

        if ($field !== null) {
            $exception->addContext('field', $field);
        }

        return $exception;
    }

    /**
     * 從錯誤清單建立例外。
     *
     * @param array<string, array<string>> $errors 錯誤清單
     * @return static
     */
    public static function withErrors(array $errors): static
    {
        $exception = new static('資料驗證失敗');
        $exception->errors = $errors;
        $exception->setContext(['errors' => $errors]);

        return $exception;
    }

    /**
     * 必填欄位缺失。
     *
     * @param string $field 欄位名稱
     * @return static
     */
    public static function requiredField(string $field): static
    {
        return static::make("{$field} 為必填欄位。", $field);
    }

    /**
     * 欄位為必填但為空（requiredField 的別名）。
     *
     * @param string $field 欄位名稱
     * @return static
     */
    public static function required(string $field): static
    {
        return static::requiredField($field);
    }

    /**
     * 欄位格式錯誤。
     *
     * @param string $field 欄位名稱
     * @param string $expectedFormat 預期格式說明
     * @return static
     */
    public static function invalidFormat(string $field, string $expectedFormat): static
    {
        return static::make(
            sprintf('欄位 %s 格式錯誤，預期格式：%s', $field, $expectedFormat),
            $field,
            ['expected_format' => $expectedFormat]
        );
    }

    /**
     * 欄位值無效。
     *
     * @param string $field 欄位名稱
     * @param string $reason 原因說明
     * @return static
     */
    public static function invalid(string $field, string $reason = ''): static
    {
        $message = $reason !== ''
            ? "{$field} 格式無效：{$reason}"
            : "{$field} 格式無效。";

        return static::make($message, $field);
    }

    /**
     * 欄位值超出長度限制。
     *
     * @param string $field 欄位名稱
     * @param int $maxLength 最大長度
     * @return static
     */
    public static function tooLong(string $field, int $maxLength): static
    {
        return static::make(
            "{$field} 不可超過 {$maxLength} 個字元。",
            $field,
            ['max_length' => $maxLength]
        );
    }

    /**
     * 欄位值不在允許範圍內。
     *
     * @param string $field 欄位名稱
     * @param array<int|string> $allowedValues 允許的值
     * @return static
     */
    public static function notInRange(string $field, array $allowedValues): static
    {
        $values = implode(', ', $allowedValues);

        return static::make(
            "{$field} 必須為下列值之一：{$values}",
            $field,
            ['allowed_values' => $allowedValues]
        );
    }

    /**
     * 取得驗證錯誤清單。
     *
     * @return array<string, array<string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * 取得驗證失敗的欄位名稱。
     *
     * @return string|null
     */
    public function getField(): ?string
    {
        return $this->field;
    }
}
