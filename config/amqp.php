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

return [

    /*
    |--------------------------------------------------------------------------
    | Default amqp Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's amqp API supports an assortment of back-ends via a single
    | API, giving you convenient access to each back-end using the same
    |  syntax for every one. Here you may define a default connection.
    |
    */
    'default' => env('AMQP_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Qmqp Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection information for each server that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with Laravel. You are free to add more.
    |
    */
    'connections' => [
        "default" => [
            "host" => env('AMQP_HOST', 'localhost'),
            'port' => env('AMQP_PORT', 5672),
            'user' => env('AMQP_USER', 'guest'),
            'password' => env('AMQP_PASSWORD', 'guest'),
            'vhost' => env('AMQP_VHOST', '/')
        ]
    ]
];