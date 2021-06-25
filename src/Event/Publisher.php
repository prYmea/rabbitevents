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
    private $context;

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
        $event->prepare(new Channel($this->context));

        $this->transport()->send(
            MessageFactory::make($event->publishEventKey(), $event->toPublish())
        );
    }

    /**
     * @return Transport
     */
    protected function transport(): Transport
    {
        return $this->context->transport();
    }
}
