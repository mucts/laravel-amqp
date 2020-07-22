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

namespace MuCTS\Laravel\AMQP;


use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class Message
 *
 * @mixin AMQPMessage
 * @package MuCTS\Laravel\AMQP
 */
class Message
{
    private AMQPMessage $message;

    public function __construct(AMQPMessage $message)
    {
        $this->message = $message;
    }

    /**
     * Acknowledges one or more messages
     */
    public function ack()
    {
        $this->message->delivery_info['channel']->basic_ack($this->message->delivery_info['delivery_tag']);
    }

    /**
     *  Rejects one or several received messages
     */
    public function nack()
    {
        $this->message->delivery_info['channel']->basic_nack($this->message->delivery_info['delivery_tag']);
    }

    /**
     * Send a message cancel the consumer.
     */
    public function cancel()
    {
        $this->message->delivery_info['channel']->basic_cancel($this->message->delivery_info['consumer_tag']);
    }

    /**
     * Get AMQPMessage
     *
     * @return AMQPMessage
     */
    public function getMessage(){
        return $this->message;
    }

    /**
     * Dynamically pass methods to the message.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return $this->message->{$method}(...$arguments);
    }

    /**
     * Dynamically pass value to the message.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->message->{$name};
    }
}