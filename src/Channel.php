<?php

namespace Nuwber\Events;

use Illuminate\Container\Container;
use Interop\Amqp\AmqpQueue;
use Interop\Amqp\AmqpTopic;
use Interop\Amqp\Impl\AmqpBind;
use Nuwber\Events\Queue\Context;

class Channel
{
    const TOPIC_TYPES = [
        AmqpTopic::TYPE_DIRECT,
        AmqpTopic::TYPE_TOPIC,
        AmqpTopic::TYPE_FANOUT,
        AmqpTopic::TYPE_HEADERS
    ];

    private Context $context;
    private string $service;

    public function __construct(Context $context)
    {
        $this->context = $context;

        $config = Container::getInstance()->get('config');
        $default = $config->get('rabbitevents.default');
        $this->service = $config->get("rabbitevents.connections.$default.service");
    }

    public function assertExchange(string $name, string $type): AmqpTopic
    {
        $topic = $this->context->createTopic($name);

        if (in_array($type, self::TOPIC_TYPES)) {
            $topic->setType($type);
        } else {
            $topic->setType(AmqpTopic::TYPE_DIRECT);
        }

        $topic->addFlag(AmqpTopic::FLAG_DURABLE);

        $this->context->declareTopic($topic);

        return $topic;
    }

    public function assertQueue(string $name): AmqpQueue
    {
        $queue = $this->context->createQueue($this->prefixedName($name));

        $queue->addFlag(AmqpQueue::FLAG_DURABLE);

        $this->context->declareQueue($queue);

        return $queue;
    }

    public function bindQueue(AmqpQueue $queue, AmqpTopic $exchange, string $routingKey): AmqpBind
    {
        $bind = new AmqpBind($exchange, $queue, $this->prefixedName($routingKey));

        $this->context->bind($bind);

        return $bind;
    }

    /**
     * @param string $event
     * @return string
     */
    protected function prefixedName(string $event): string
    {
        if ($this->startsWith($event, $this->service)) {
            return $event;
        }

        return "{$this->service}:$event";
    }

    /**
     * String starts with
     *
     * @param string $haystack
     * @param string $needle
     * @return boolean
     */
    private function startsWith(string $haystack, string $needle): bool
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }
}
