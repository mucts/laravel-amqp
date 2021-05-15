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


use Closure;
use ErrorException;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class AMQPManager
 *
 * @mixin AMQPStreamConnection
 * @package MuCTS\Laravel\AMQP
 */
class AMQPManager
{
    /**
     * @var Application|Container
     */
    private Application|Container $app;

    /**
     * AMQPMessage Connection Name
     *
     * @var string|null
     */
    protected ?string $connection = null;

    /**
     * AMQPQueue Name
     * 队列名
     *
     * @var string
     */
    protected string $queue = '';

    /**
     * AMQPExchange Name
     * 交换机
     *
     * @var string
     */
    protected string $exchange = '';

    /**
     * AMQPExchange Type
     * 交换机类型
     *
     * @var string
     */
    protected string $exchangeType = '';

    /**
     * Consumer identifier
     * 用户标签
     *
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
     * AMQPMessage vhost
     *
     * @var string|null
     */
    protected ?string $vhost = null;

    /**
     * Auto Ack
     * 自动消费
     *
     * @var bool
     */
    protected bool $autoAck = false;

    /**
     * RabbitMQ constructor.
     * @param Application|Container $app
     */
    public function __construct(Container|Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get default connection
     *
     * @return string
     */
    public function getDefaultConnection(): string
    {
        return $this->app['config']['amqp.default'];
    }

    /**
     * amqp connection
     *
     * @param string|null $name
     * @return $this
     * @throws Exception
     */
    public function connection(?string $name = null): static
    {
        $this->connection = $name ?: $this->getDefaultConnection();
        return $this;
    }

    /**
     * Get the configuration for a connection.
     *
     * @param string $name
     * @return array
     * @throws InvalidArgumentException
     */
    protected function configuration(string $name): array
    {
        $name        = $name ?: $this->getDefaultConnection();
        $connections = $this->app['config']['amqp.connections'];
        if (is_null($config = Arr::get($connections, $name))) {
            throw new InvalidArgumentException("RabbitMQ connection [{$name}] not configured.");
        }
        return $config;
    }

    /**
     * Make the amqp connection instance.
     *
     * @param string $name
     * @return AMQPStreamConnection
     * @throws Exception
     */
    protected function makeConnection(string $name): AMQPStreamConnection
    {
        $config = $this->configuration($name);
        if (isset($config['host'])) $config = [$config];
        if ($this->getVhost()) {
            foreach ($config as &$conf) {
                $conf['vhost'] = $this->getVhost();
            }
        }
        return AMQPStreamConnection::create_connection($config);
    }

    /**
     * Get Connection
     *
     * @return AMQPStreamConnection
     * @throws Exception
     */
    protected function getConnection(): AMQPStreamConnection
    {
        $name = $this->connection ?: $this->getDefaultConnection();
        return $this->makeConnection($name);
    }

    /**
     * Start Connection
     *
     * @return array
     * @throws Exception
     */
    protected function startConnection(): array
    {
        $connection = $this->getConnection();
        $channel    = $connection->channel();

        /**
         * The following code is the same both in the consumer and the producer.
         * In this way we are sure we always have a queue to consume from and an
         * exchange where to publish messages.
         *
         * name: $queue
         * passive: false
         * durable: true the queue will survive server restarts
         * exclusive: false the queue can be accessed in other channels
         * auto_delete: false the queue won't be deleted once the channel is closed.
         */
        $channel->queue_declare($this->getQueue(), false, true, false, false);

        /**
         * name: $exchange
         * type: direct
         * passive: false
         * durable: true the exchange will survive server restarts
         * auto_delete: false the exchange won't be deleted once the channel is closed.
         */
        $channel->exchange_declare($this->getExchange(), $this->getExchangeTyp(), false, true, false);

        $channel->queue_bind($this->getQueue(), $this->getExchange(), $this->getRouteKey());

        return [$connection, $channel];
    }

    /**
     * set queue name
     *
     * @param string $queue
     * @return $this
     */
    public function setQueue(string $queue): static
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * get queue name
     *
     * @return string
     */
    public function getQueue(): string
    {
        if (empty($this->queue)) {
            throw new InvalidArgumentException("'queue' key is required.");
        }
        return $this->queue;
    }

    /**
     * Set exchange name
     *
     * @param string $exchange
     * @return $this
     */
    public function setExchange(string $exchange): static
    {
        $this->exchange = $exchange;
        return $this;
    }

    /**
     * Get exchange name
     *
     * @return string
     */
    public function getExchange(): string
    {
        if (empty($this->exchange)) {
            throw new InvalidArgumentException("'exchange' key is required.");
        }
        return $this->exchange;
    }

    /**
     * Set exchange name
     *
     * @param string $exchangeType
     * @return $this
     */
    public function setExchangeType(string $exchangeType): static
    {
        $this->exchangeType = $exchangeType;
        return $this;
    }

    /**
     * Get exchange name
     *
     * @return string
     */
    public function getExchangeTyp(): string
    {
        if (empty($this->exchangeType)) {
            throw new InvalidArgumentException("'exchange type' key is required.");
        }
        return $this->exchangeType;
    }

    /**
     * Set Route Key
     *
     * @param string $routeKey
     * @return $this
     */
    public function setRouteKey(string $routeKey): static
    {
        $this->routeKey = $routeKey;
        return $this;
    }

    /**
     * Get Route Key
     *
     * @return string
     */
    public function getRouteKey(): string
    {
        return $this->routeKey;
    }

    /**
     * Set Consumer identifier
     *
     * @param string $consumerTag
     * @return AMQPManager
     */
    public function setConsumerTag(string $consumerTag): static
    {
        $this->consumerTag = $consumerTag;
        return $this;
    }

    /**
     * Get Consumer identifier
     *
     * @return string
     */
    public function getConsumerTag(): string
    {
        return $this->consumerTag;
    }

    /**
     * Set auto ask
     *
     * @param bool $autoAsk
     * @return $this
     */
    public function setAutoAck(bool $autoAsk): static
    {
        $this->autoAck = $autoAsk;
        return $this;
    }

    /**
     * Get auto ask
     *
     * @return bool
     */
    public function getAutoAck(): bool
    {
        return $this->autoAck;
    }

    /**
     * Set vhost
     *
     * @param string|null $vhost
     * @return $this
     */
    public function setVhost(?string $vhost): static
    {
        $this->vhost = $vhost;
        return $this;
    }

    /**
     * Get vhost
     *
     * @return string|null
     */
    public function getVhost(): ?string
    {
        return $this->vhost;
    }

    /**
     * Amqp Message Consumer
     *
     * @param callable $processMessage
     * @throws ErrorException
     * @throws Exception
     */
    public function consume(callable $processMessage)
    {
        /** @var AMQPStreamConnection $connection */
        /** @var AMQPChannel $channel */
        list($connection, $channel) = $this->startConnection();

        /**
         *  queue: Queue from where to get the messages
         * consumer_tag: Consumer identifier
         * no_local: Don't receive messages published by this consumer.
         * no_ack: If set to true, automatic acknowledgement mode will be used by this consumer.See https://www.rabbitmq.com/confirms.html for details.
         * exclusive: Request exclusive consumer access, meaning only this consumer can access the queue
         * nowait:
         * callback: A PHP Callback
         */
        $channel->basic_consume($this->getQueue(),
            $this->getConsumerTag(),
            false, $this->getAutoAck(),
            false,
            false,
            function (AMQPMessage $message) use ($processMessage) {
                return $processMessage($message);
            });

        register_shutdown_function($this->shutdown(), $channel, $connection);

        // Loop as long as the channel has callbacks registered
        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }

    /**
     * shutdown
     *
     * @return Closure
     */
    public function shutdown(): Closure
    {
        /**
         * @param AMQPChannel $channel
         * @param AbstractConnection $connection
         * @throws Exception
         */
        return function (AMQPChannel $channel, AbstractConnection $connection) {
            $channel->close();
            $connection->close();
        };
    }

    /**
     * Amqp Message Publisher
     *
     * @param array|string|AMQPMessage|Collection $message
     * @throws Exception
     */
    public function publish(array|string|Collection|AMQPMessage $message)
    {
        /** @var AMQPStreamConnection $connection */
        /** @var AMQPChannel $channel */
        list($connection, $channel) = $this->startConnection();

        if (!$message instanceof AMQPMessage) {
            if ($message instanceof Collection) $message = $message->toJson();
            elseif (is_array($message) || is_object($message)) $message = json_encode($message);
            $message = new AMQPMessage($message, ['content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
        }

        $channel->basic_publish($message, $this->getExchange(), $this->getRouteKey());

        $channel->close();
        $connection->close();
    }


    /**
     * Dynamically pass methods to the default connection.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws Exception
     */
    public function __call(string $method, array $parameters)
    {
        return $this->getConnection()->{$method}(...$parameters);
    }

    /**
     * Dynamically pass value to the default connection.
     *
     * @param $name
     * @return mixed
     * @throws Exception
     */
    public function __get($name)
    {
        return $this->getConnection()->{$name};
    }
}