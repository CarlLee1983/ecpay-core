<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Laravel\Concerns;

use Illuminate\Contracts\Foundation\Application;

/**
 * 操作註冊輔助 Trait。
 *
 * 提供 Service Provider 註冊操作別名的共用邏輯。
 */
trait RegistersOperations
{
    /**
     * 註冊操作別名。
     *
     * @param string $configName 設定檔名稱
     * @param string $prefix 服務前綴
     * @param class-string $factoryInterface 工廠介面
     */
    protected function registerOperations(
        string $configName,
        string $prefix,
        string $factoryInterface
    ): void {
        $bindings = $this->app['config']->get($configName . '.bindings', []);

        foreach ($bindings as $name => $alias) {
            $serviceId = strpos($name, $prefix . '.') === 0 ? $name : $prefix . '.' . $name;

            $this->app->bind($serviceId, function (Application $app) use ($factoryInterface, $alias) {
                $factory = $app->make($factoryInterface);

                return $factory->make((string) $alias);
            });
        }
    }
}
