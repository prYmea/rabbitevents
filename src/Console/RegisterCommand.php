<?php

namespace Nuwber\Events\Console;

use Illuminate\Console\Command;
use Nuwber\Events\Amqp\BindFactory;
use Nuwber\Events\Amqp\QueueFactory;
use Nuwber\Events\Amqp\Connection;

class ListenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitevents:register';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register RabbitMQ bindings';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $connection = $this->laravel['config']['rabbitevents.default'];

        $config = $this->laravel['config']->get("rabbitevents.connections.$connection");
        $context = (new Connection($config))->createContext();

        $factory = new QueueFactory(
            context: $context,
            bindFactory: new BindFactory($context),
            service: $config['service']
        );

        $bindings = $this->laravel['config']->get('rabbitevents.bindings', []);

        foreach ($bindings as $queue => $routingKey) {
            $factory->make(routingKey: $routingKey, queueName: $queue);
        }

        $factory->close();
    }
}
