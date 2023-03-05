<?php

require __DIR__ . '/../vendor/autoload.php';

use App\HelloWorldHandler;
use App\HelloWorldMessage;
use App\Logger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\EventListener\StopWorkerOnMessageLimitListener;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpTransportFactory;
use Symfony\Component\Messenger\Worker;

$transport = (new AmqpTransportFactory)->createTransport(
    'amqp://guest:guest@host.docker.internal:5672/%2f/messages',
    [
        'exchange' => [
            'name' => 'foo',
            'type' => \AMQP_EX_TYPE_FANOUT
        ],
        'queues' => [
            'bar' => [],
        ]
    ],
    new Serializer()
);

$bus = new MessageBus([
    new HandleMessageMiddleware(new HandlersLocator([
        HelloWorldMessage::class => [new HelloWorldHandler()],
    ])),
]);

$logger = new Logger();

$eventDispatcher = new EventDispatcher();
$eventDispatcher->addSubscriber(new StopWorkerOnMessageLimitListener(5, $logger));

for ($i = 0; $i < 5; $i++) {
    $transport->send(new Envelope(new HelloWorldMessage(), [new AmqpStamp('messages')]));
}

(new Worker([$transport], $bus, $eventDispatcher, $logger))->run();