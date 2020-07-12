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
use MuCTS\Laravel\AMQP\Message;
use PhpAmqpLib\Exchange\AMQPExchangeType;

abstract class AMQPCommand extends Command
{
    protected ?string $connectionName = null;
    protected string $exchange = '';
    protected string $exchangeType = AMQPExchangeType::TOPIC;
    protected string $queue = '';
    protected string $consumerTag = '';
    protected bool $autoAsk = false;

    public function handle()
    {
        try {
            AMQP::connection($this->connectionName)
                ->setExchange($this->exchange)
                ->setExchangeType($this->exchangeType)
                ->setQueue($this->queue)
                ->setConsumerTag($this->consumerTag)
                ->setAutoAck($this->autoAsk)
                ->consume(function ($message) {
                    /** @var Message $message */
                    return static::processMessage($message);
                });
        } catch (InvalidArgumentException|ErrorException|Exception $exception) {
            Log::error('AMQPMessage consume error:' . $exception->getMessage());
        }
    }

    abstract function processMessage(Message $message);
}