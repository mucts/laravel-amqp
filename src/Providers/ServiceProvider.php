<?php
/**
 * This file is part of the mucts.com.
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * @version 1.0
 * @author herry<yuandeng@aliyun.com>
 * @copyright © 2020  MuCTS.com All Rights Reserved.
 */

namespace MuCTS\Laravel\AMQP\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider as Provider;
use MuCTS\Laravel\AMQP\AMQPManager;

class ServiceProvider extends Provider implements DeferrableProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/../config/amqp.php', 'amqp'
        );
        $this->app->singleton(AMQPManager::class, function ($app) {
            return new AMQPManager($app);
        });
        $this->app->alias(AMQPManager::class, 'amqp');
    }

    public function boot()
    {
        if (!file_exists(config_path('amqp.php'))) {
            $this->publishes([
                dirname(__DIR__) . '/../config/amqp.php' => config_path('amqp.php'),
            ], 'config');
        }
    }

    public function provides()
    {
        return [AMQPManager::class, 'amqp'];
    }
}