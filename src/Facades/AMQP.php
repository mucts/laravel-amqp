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
 * @mixin AMQPManager
 * @package MuCTS\Laravel\AMQP\Facades
 */
class AMQP extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AMQPManager::class;
    }
}