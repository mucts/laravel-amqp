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

namespace MuCTS\Laravel\AMQP\Commands;


use ErrorException;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use MuCTS\Laravel\AMQP\Facades\AMQP;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

abstract class AMQPCommand extends Command
{
    /**
     * AMQPMessage Connection Name
     *
     * @var string|null
     */
    protected ?string $connection = null;

    /**
     * AMQPExchange Name
     * @var string
     */
    protected string $exchange = '';

    /**
     * AMQPExchange Type
     * @var string
     */
    protected string $exchangeType = AMQPExchangeType::TOPIC;

    /**
     * AMQPQueue Name
     * @var string
     */
    protected string $queue = '';

    /**
     * Consumer identifier
     * @var string
     */
    protected string $consumerTag = '';

    /**
     * AMQPMessage Route Key
     * 路由键
     *
     * @var string
     */
    protected string $routeKey = '';

    /**
     * AMQPMessage Vhost
     *
     * @var string|null
     */
    protected ?string $vhost = null;

    /**
     * Auto Ack
     * @var bool
     */
    protected bool $autoAsk = false;

    public function handle()
    {
        try {
            AMQP::connection($this->connection)
                ->setExchange($this->exchange)
                ->setExchangeType($this->exchangeType)
                ->setQueue($this->queue)
                ->setConsumerTag($this->consumerTag)
                ->setAutoAck($this->autoAsk)
                ->setRouteKey($this->routeKey)
                ->setVhost($this->vhost)
                ->consume(function (AMQPMessage $message) {
                    return static::processMessage($message);
                });
        } catch (InvalidArgumentException | ErrorException | Exception $exception) {
            Log::error('AMQPMessage consume error:' . $exception->getMessage());
        }
    }

    /**
     * Process Message
     *
     * @param AMQPMessage $message
     * @return mixed
     */
    abstract protected function processMessage(AMQPMessage $message);
}