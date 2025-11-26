<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Laravel;

use Illuminate\Support\ServiceProvider;

/**
 * Core 套件的 Service Provider。
 *
 * 主要用於發布共用設定檔和註冊共用服務。
 */
class CoreServiceProvider extends ServiceProvider
{
    /**
     * 註冊服務。
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/ecpay.php', 'ecpay');
    }

    /**
     * 啟動服務。
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/ecpay.php' => $this->configPath('ecpay.php'),
            ], 'ecpay-config');
        }
    }

    /**
     * 取得設定發布路徑。
     *
     * @param string $file 檔案名稱
     * @return string
     */
    protected function configPath(string $file): string
    {
        if (function_exists('config_path')) {
            return config_path($file);
        }

        return $this->app->basePath('config/' . $file);
    }
}
