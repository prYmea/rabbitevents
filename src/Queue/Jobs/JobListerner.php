<?php

namespace Nuwber\Events\Queue\Jobs;

use Illuminate\Support\Arr;
use Interop\Amqp\AmqpMessage;
use Nuwber\Events\Queue\Manager;

abstract class JobListener
{
    private Manager $queue;
    private AmqpMessage $message;

    abstract public function run(string $routingKey, array $payload);

    /**
     * Handle the event.
     *
     * @param  array $payload
     * @param  array $data
     * @return void
     */
    public function handle(array $payload, array $data)
    {
        $this->message = Arr::get($data, 'message');
        $this->queue = Arr::get($data, 'queue');

        $routingKey = (string) Arr::get($data, 'routingKey');

        $this->run(routingKey: $routingKey, payload: $payload);
    }

    /*-----------------------------------------------------------------------------------------
     Helper methods
    -----------------------------------------------------------------------------------------*/

    /**
     * Acknowledge message
     *
     * @return void
     */
    public function acknowledge(): void
    {
        $this->queue?->acknowledge($this->message);
    }

    /**
     * Reject message
     *
     * @param boolean $requeue
     * @return void
     */
    public function reject(bool $requeue = false): void
    {
        $this->queue?->reject($this->message, $requeue);
    }
}
