# ECPay Core - 綠界科技 SDK 共用核心套件

[![PHP Version](https://img.shields.io/badge/php-%5E8.3-blue)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

## 簡介

`ecpay-core` 是綠界科技 (ECPay) SDK 系列套件的共用核心，提供所有子套件（電子發票、金流、物流）共用的基礎類別、介面、例外處理與加解密服務。

## 套件架構

```
carllee1983/ecpay (全家桶)
├── carllee1983/ecpay-core (本套件)
├── carllee1983/ecpay-einvoice (電子發票)
│   ├── carllee1983/ecpay-einvoice-b2b
│   └── carllee1983/ecpay-einvoice-b2c
├── carllee1983/ecpay-payment (金流) [開發中]
└── carllee1983/ecpay-logistics (物流) [開發中]
```

## 安裝

```bash
composer require carllee1983/ecpay-core
```

> **注意**：一般使用者不需要直接安裝此套件，它會作為其他 ECPay 套件的依賴自動安裝。

## 提供的功能

### 基礎類別

- `AbstractContent` - 所有 API 操作的抽象基礎類別
- `AbstractOperationFactory` - 工廠模式抽象類別
- `Request` - HTTP 請求處理
- `Response` - API 回應封裝

### 介面 (Contracts)

- `ContentInterface` - 內容物件介面
- `CommandInterface` - 命令介面
- `OperationFactoryInterface` - 工廠介面

### 例外處理 (Exceptions)

- `EcPayException` - 基礎例外
- `ApiException` - API 錯誤
- `ConfigurationException` - 設定錯誤
- `EncryptionException` - 加解密錯誤
- `ValidationException` - 驗證錯誤
- `PayloadException` - Payload 錯誤

### 基礎設施 (Infrastructure)

- `CipherService` - AES 加解密服務
- `PayloadEncoder` - Payload 編碼器

### DTO

- `ItemCollection` - 項目集合
- `ItemDtoInterface` - 項目 DTO 介面
- `RqHeaderDto` - 請求標頭 DTO

### Laravel 支援

- `CoreServiceProvider` - 核心 Service Provider（自動發布共用設定）
- `AbstractEcPayServiceProvider` - 抽象 Service Provider（子套件繼承）
- `RegistersOperations` Trait - 操作註冊輔助

## Laravel 整合

安裝後，Laravel 會自動註冊 `CoreServiceProvider`。

### 發布設定檔

```bash
php artisan vendor:publish --tag=ecpay-config
```

這會發布 `config/ecpay.php` 共用設定檔，包含：

- `environment` - 環境設定（sandbox/production）
- `verify_ssl` - SSL 驗證開關
- `http.timeout` - HTTP 請求逾時設定

### 環境變數

```env
ECPAY_ENVIRONMENT=sandbox
ECPAY_VERIFY_SSL=true
ECPAY_HTTP_TIMEOUT=30
ECPAY_HTTP_CONNECT_TIMEOUT=10
```

## 開發子套件

如果你要開發新的 ECPay 子套件，請繼承核心類別：

```php
<?php

namespace YourNamespace;

use CarlLee\EcPay\Core\AbstractContent;

abstract class Content extends AbstractContent
{
    // 你的子套件特有邏輯
}
```

```php
<?php

namespace YourNamespace\Factories;

use CarlLee\EcPay\Core\AbstractOperationFactory;
use YourNamespace\Content;

class OperationFactory extends AbstractOperationFactory
{
    protected function getBaseNamespace(): string
    {
        return 'YourNamespace';
    }

    protected function getContentClass(): string
    {
        return Content::class;
    }
}
```

## 授權

MIT License

