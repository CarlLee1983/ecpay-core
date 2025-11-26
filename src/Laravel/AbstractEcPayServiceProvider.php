<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Laravel;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

/**
 * 抽象 Service Provider。
 *
 * 子套件繼承此類別並實作特定的註冊邏輯。
 */
abstract class AbstractEcPayServiceProvider extends ServiceProvider
{
    /**
     * 取得設定檔名稱（不含路徑和副檔名）。
     *
     * @return string 例如 'ecpay-einvoice-b2b'
     */
    abstract protected function getConfigName(): string;

    /**
     * 取得設定檔路徑。
     *
     * @return string 完整路徑，例如 __DIR__ . '/../../config/ecpay.php'
     */
    abstract protected function getConfigPath(): string;

    /**
     * 取得服務別名前綴。
     *
     * @return string 例如 'ecpay-b2b' 或 'ecpay'
     */
    abstract protected function getServicePrefix(): string;

    /**
     * 取得工廠介面 FQCN。
     *
     * @return class-string
     */
    abstract protected function getFactoryInterface(): string;

    /**
     * 註冊工廠實例。
     */
    abstract protected function registerFactory(): void;

    /**
     * 註冊客戶端實例。
     */
    abstract protected function registerClient(): void;

    /**
     * 註冊協調器。
     */
    abstract protected function registerCoordinator(): void;

    /**
     * 註冊服務。
     */
    public function register(): void
    {
        $this->mergeConfigFrom($this->getConfigPath(), $this->getConfigName());

        $this->configureRequest();
        $this->registerFactory();
        $this->registerClient();
        $this->registerOperationBindings();
        $this->registerCoordinator();
    }

    /**
     * 啟動服務。
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->getConfigPath() => $this->publishPath($this->getConfigName() . '.php'),
            ], $this->getConfigName() . '-config');
        }
    }

    /**
     * 根據設定檔配置 Request 類別。
     *
     * 子類別可覆寫此方法以自訂 Request 設定。
     */
    protected function configureRequest(): void
    {
        // 子類別可覆寫
    }

    /**
     * 將設定檔內的便利別名註冊至容器。
     */
    protected function registerOperationBindings(): void
    {
        $bindings = $this->app['config']->get($this->getConfigName() . '.bindings', []);
        $prefix = $this->getServicePrefix();

        foreach ($bindings as $name => $alias) {
            $serviceId = strpos($name, $prefix . '.') === 0 ? $name : $prefix . '.' . $name;

            $this->app->bind($serviceId, function (Application $app) use ($alias) {
                $factory = $app->make($this->getFactoryInterface());

                return $factory->make((string) $alias);
            });
        }
    }

    /**
     * 將設定值轉為可呼叫的初始化邏輯。
     *
     * @param mixed $initializer 初始化器設定
     * @param Application $app 應用程式實例
     * @return callable|null
     */
    protected function resolveInitializer(mixed $initializer, Application $app): ?callable
    {
        if (is_string($initializer) && class_exists($initializer)) {
            $callable = $app->make($initializer);
            if (is_callable($callable)) {
                return $callable;
            }
        }

        if (is_callable($initializer)) {
            return $initializer;
        }

        return null;
    }

    /**
     * 取得設定發布路徑。
     *
     * @param string $file 檔案名稱
     * @return string
     */
    protected function publishPath(string $file): string
    {
        if (function_exists('config_path')) {
            return config_path($file);
        }

        return $this->app->basePath('config/' . $file);
    }

    /**
     * 從設定檔取得值。
     *
     * @param string $key 設定鍵名（相對於 configName）
     * @param mixed $default 預設值
     * @return mixed
     */
    protected function config(string $key, mixed $default = null): mixed
    {
        return $this->app['config']->get($this->getConfigName() . '.' . $key, $default);
    }
}
