<p align="center"><img src="https://images.mucts.com/image/exp_def_white.png" width="400"></p>
<p align="center">
    <a href="https://scrutinizer-ci.com/g/mucts/laravel-amqp"><img src="https://scrutinizer-ci.com/g/mucts/laravel-amqp/badges/build.png" alt="Build Status"></a>
    <a href="https://scrutinizer-ci.com/g/mucts/laravel-amqp"><img src="https://scrutinizer-ci.com/g/mucts/laravel-amqp/badges/code-intelligence.svg" alt="Code Intelligence Status"></a>
    <a href="https://scrutinizer-ci.com/g/mucts/laravel-amqp"><img src="https://scrutinizer-ci.com/g/mucts/laravel-amqp/badges/quality-score.png" alt="Scrutinizer Code Quality"></a>
    <a href="https://packagist.org/packages/mucts/laravel-amqp"><img src="https://poser.pugx.org/mucts/laravel-amqp/d/total.svg" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/mucts/laravel-amqp"><img src="https://poser.pugx.org/mucts/laravel-amqp/v/stable.svg" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/mucts/laravel-amqp"><img src="https://poser.pugx.org/mucts/laravel-amqp/license.svg" alt="License"></a>
</p>

# Laravel AMQP
> AMQPMessage SDK for Laravel 8

## Installation

### Server Requirements
>you will need to make sure your server meets the following requirements:

- `php ^8.0`
- `JSON PHP Extension`
- `Sockets PHP Extension`
- `MBString PHP Extension`
- `php-amqplib/php-amqplib 3.0`
- `laravel/framework ^8.41`


### Laravel Installation
```
composer require mucts/laravel-amqp

```

## Usage

- AMQPMessage publisher
```php
<?php
use MuCTS\Laravel\AMQP\Facades\AMQP;
use PhpAmqpLib\Exchange\AMQPExchangeType;

// send message
AMQP::connection('default')
->setExchange('test')
->setExchangeType(AMQPExchangeType::TOPIC)
->setQueue('test')
->publish('test');

```
- AMQPMessage consumer
```php
use MuCTS\Laravel\AMQP\Commands\AMQPCommand;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Exchange\AMQPExchangeType;

class test extends AMQPCommand{
    protected string $exchange = 'test';
    protected string $queue = 'test';
    protected string $exchangeType = AMQPExchangeType::TOPIC;
    protected string $consumerTag = 'consumer';
    protected ?string $connectionName = 'default';
    protected bool $autoAsk = false;

    protected function processMessage(AMQPMessage $message){
        Log::info($message->getBody());
        // message ask
        $message->ack();
        // message nack
        $message->nack(true);
    }
}
```


## Configuration
If `config/amqp.php` not exist, run below:
```
php artisan vendor:publish
```
