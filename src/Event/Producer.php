<?php

namespace Nuwber\Events\Event;

use Illuminate\Container\Container;
use Nuwber\Events\Channel;
use Nuwber\Events\Event\Publishable;
use Nuwber\Events\Event\ShouldPublish;
use Nuwber\Events\Event\Testing\PublishableEventTesting;
use Nuwber\Events\RabbitEventsServiceProvider;

abstract class Producer implements ShouldPublish
{
    use Publishable, PublishableEventTesting;

    /**
     * Exchange name
     *
     * @return string
     */
    public static function exchange(): string
    {
        $config = Container::getInstance()->get('config');
        $default = $config->get('rabbitevents.default');

        return $config->get(
            "rabbitevents.connections.$default.exchange",
            RabbitEventsServiceProvider::DEFAULT_EXCHANGE_NAME
        );
    }

    /**
     * Exchange type
     * 'direct', 'topic', 'fanout', 'headers'
     *
     * @return string
     */
    public static function exchangeType(): string
    {
        return 'topic';
    }

    /**
     * Queue
     *
     * @return string
     */
    abstract public static function queue(): string;

    /**
     * Routing key
     *
     * @return string
     */
    abstract public static function routingKey(): string;

    /**
     * Publish key
     *
     * @return string
     */
    abstract public static function publishKey(): string;


    /**
     * Prepare any necessary exchange/queue before publish
     *
     * @param Channel $channel
     * @return void
     */
    public function prepare(Channel $channel): void
    {
        $queue = $channel->assertQueue(static::queue());
        $exchange = $channel->assertExchange(static::exchange(), static::exchangeType());
        $channel->bindQueue(queue: $queue, exchange: $exchange, routingKey: static::routingKey());
    }

    /**
     * Event name that the same as RammitMQ's routing key. Example: `item.created`.
     *
     * @return string
     */
    public function publishEventKey(): string
    {
        $config = Container::getInstance()->get('config');
        $default = $config->get('rabbitevents.default');
        $service = $config->get("rabbitevents.connections.$default.service");
        $key = static::publishKey();

        return "$service:$key";
    }
}
