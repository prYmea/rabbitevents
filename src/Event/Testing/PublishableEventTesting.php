<?php

namespace Nuwber\Events\Event\Testing;

use Illuminate\Container\Container;
use Nuwber\Events\Event\ShouldPublish;

trait PublishableEventTesting
{
    public static function fake(): void
    {
        Container::getInstance()->instance(static::class, \Mockery::spy(static::class));
    }

    public static function assertPublished(string $event, array $payload): void
    {
        Container::getInstance()->get(static::class)
            ->shouldHaveReceived()
            ->publish(\Mockery::on(function (ShouldPublish $object) use ($event, $payload) {
                return $object instanceof static
                    && $object->publishEventKey() == $event
                    && $object->toPublish() == $payload;
            }))
            ->once();
    }

    public static function assertNotPublished(): void
    {
        Container::getInstance()->get(static::class)
            ->shouldNotHaveReceived()
            ->publish(\Mockery::type(static::class));
    }
}
