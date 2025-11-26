<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Contracts;

/**
 * 工廠介面：定義操作物件的建立方式。
 *
 * 所有子套件的工廠類別應實作此介面。
 */
interface OperationFactoryInterface
{
    /**
     * 建立操作物件。
     *
     * @param string $target 目標類別名稱或別名
     * @param array<int, mixed> $parameters 建構參數
     * @return ContentInterface 操作物件實例
     */
    public function make(string $target, array $parameters = []): ContentInterface;

    /**
     * 註冊自訂解析器。
     *
     * @param string $alias 別名
     * @param callable $resolver 解析器
     */
    public function extend(string $alias, callable $resolver): void;

    /**
     * 註冊類別別名。
     *
     * @param string $alias 別名
     * @param string $class 完整類別名稱
     */
    public function alias(string $alias, string $class): void;

    /**
     * 新增初始化程式。
     *
     * @param callable $initializer 初始化回呼
     */
    public function addInitializer(callable $initializer): void;

    /**
     * 設定商店憑證。
     *
     * @param string $merchantId 商店代號
     * @param string $hashKey HashKey
     * @param string $hashIV HashIV
     */
    public function setCredentials(string $merchantId, string $hashKey, string $hashIV): void;
}
