<?php

namespace Nuwber\Events\Event;

use Illuminate\Support\Arr;
use Interop\Amqp\AmqpMessage;
use Nuwber\Events\Queue\Manager;

abstract class Consumer
{
    abstract public function run(string $routingKey, array $payload, AmqpMessage $message, Manager $queue);

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
        $routingKey = (string) Arr::get($data, 'routingKey');
        $prefix = static::servicePrefix();

        if ($this->startsWith($routingKey, $prefix)) {
            $this->run(
                routingKey: substr($routingKey, strlen($prefix)),
                payload: $payload,
                message: Arr::get($data, 'message'),
                queue: Arr::get($data, 'queue'),
            );
        }
    }

    /*-----------------------------------------------------------------------------------------
     Helper methods
    -----------------------------------------------------------------------------------------*/

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
