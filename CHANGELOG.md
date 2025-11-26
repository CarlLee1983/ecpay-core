# Changelog

所有重要的變更都會記錄在此文件中。

格式基於 [Keep a Changelog](https://keepachangelog.com/zh-TW/1.0.0/)，
並遵循 [Semantic Versioning](https://semver.org/lang/zh-TW/)。

## [Unreleased]

## [1.0.0] - 2024-XX-XX

### 新增

- 初始版本
- `AbstractContent` - API 操作的抽象基礎類別
- `AbstractOperationFactory` - 工廠模式抽象類別
- `Request` - HTTP 請求處理
- `Response` - API 回應封裝
- `ContentInterface` - 內容物件介面
- `CommandInterface` - 命令介面
- `OperationFactoryInterface` - 工廠介面
- `EcPayException` - 基礎例外類別
- `ApiException` - API 錯誤例外
- `ConfigurationException` - 設定錯誤例外
- `EncryptionException` - 加解密錯誤例外
- `ValidationException` - 驗證錯誤例外
- `PayloadException` - Payload 錯誤例外
- `CipherService` - AES 加解密服務
- `PayloadEncoder` - Payload 編碼器
- `ItemCollection` - 項目集合類別
- `ItemDtoInterface` - 項目 DTO 介面
- `RqHeaderDto` - 請求標頭 DTO
- `CoreServiceProvider` - Laravel Service Provider
- `AbstractEcPayServiceProvider` - 抽象 Service Provider
- `RegistersOperations` Trait - 操作註冊輔助

### Laravel 支援

- 自動發現 `CoreServiceProvider`
- 可發布共用設定檔 `config/ecpay.php`

