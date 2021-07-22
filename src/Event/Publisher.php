<?php

namespace Nuwber\Events\Event;

use JsonException;
use Nuwber\Events\Channel;
use Nuwber\Events\Queue\Context;
use Nuwber\Events\Queue\Message\Factory as MessageFactory;
use Nuwber\Events\Queue\Message\Transport;

class Publisher
{
    /**
     * @var Context
     */
    protected $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Publish event to
     *
     * @param ShouldPublish $event
     *
     * @throws JsonException
     */
    public function publish(ShouldPublish $event): void
    {
        $sendEventFn = function () use ($event) {
            $event->prepare(new Channel($this->context));

            $this->transport()->send(
                MessageFactory::make($event->publishEventKey(), $event->toPublish())
            );
        };

        if (app()->bound('db.transactions') && property_exists($event, 'afterCommit') && $event->afterCommit) {
            app()->make('db.transactions')->addCallback($sendEventFn);
        } else {
            $sendEventFn();
        }
    }

    /**
     * @return Transport
     */
    protected function transport(): Transport
    {
        return $this->context->transport();
    }
}
