<?php

namespace Nuwber\Events\Amqp;

use Interop\Amqp\AmqpQueue;
use Interop\Queue\Exception\Exception;
use Nuwber\Events\Queue\Context;

class QueueFactory
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var string
     */
    private $service;
    /**
     * @var BindFactory
     */
    private $bindFactory;

    public function __construct(Context $context, BindFactory $bindFactory, string $service)
    {
        $this->context = $context;
        $this->bindFactory = $bindFactory;
        $this->service = $service;
    }

    public function make(string $routingKey, string $queueName): AmqpQueue
    {
        $queue = $this->context->createQueue($this->prefixedName($queueName));
        $queue->addFlag(AmqpQueue::FLAG_DURABLE);

        $this->context->declareQueue($queue);

        $this->bind($queue, $this->prefixedName($routingKey));

        return $queue;
    }

    public function close()
    {
        $this->context->close();
    }

    /**
     * @param string $event
     * @return string
     */
    protected function prefixedName(string $event): string
    {
        return "{$this->service}:$event";
    }

    /**
     * @param AmqpQueue $queue
     * @param string $event
     * @throws Exception
     */
    protected function bind(AmqpQueue $queue, string $event): void
    {
        $bind = $this->bindFactory->make($queue, $event);

        $this->context->bind($bind);
    }
}
