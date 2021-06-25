<?php

namespace Nuwber\Events\Event;

use Illuminate\Support\Arr;
use Interop\Amqp\AmqpMessage;
use Nuwber\Events\Queue\Manager;

abstract class Consumer
{
    private Manager $queue;
    private AmqpMessage $message;

    abstract public function run(string $routingKey, array $payload);

    /**
     * Service name
     *
     * @return string
     */
    abstract public static function service(): string;

    /**
     * Build service prefix
     *
     * @param string $suffix
     * @return string
     */
    public static function servicePrefix(string $suffix = ''): string
    {
        $service = static::service();

        return "$service:$suffix";
    }

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
        $prefix = static::servicePrefix();

        if ($this->startsWith($routingKey, $prefix)) {
            $this->run(
                routingKey: substr($routingKey, strlen($prefix)),
                payload: $payload
            );
        }
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
