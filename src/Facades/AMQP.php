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

namespace MuCTS\Laravel\AMQP\Facades;


use Illuminate\Support\Facades\Facade;
use MuCTS\Laravel\AMQP\AMQPManager;

/**
 * Class AMQP
 *
 * @mixin AMQPManager
 * @method static AMQPManager connection(?string $name = null)
 * @method static AMQPManager setQueue(string $queue)
 * @method static AMQPManager setExchange(string $exchange)
 * @method static AMQPManager setExchangeType(string $exchangeType)
 * @method static AMQPManager setConsumerTag(string $consumerTag)
 * @method static AMQPManager setAutoAck(bool $autoAsk)
 * @method static AMQPManager setRouteKey(string $routeKey)
 * @package MuCTS\Laravel\AMQP\Facades
 */
class AMQP extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AMQPManager::class;
    }
}