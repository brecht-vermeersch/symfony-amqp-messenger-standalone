<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpTransportFactory;
use Symfony\Component\Messenger\Worker;

class HelloWorldMessage
{
    public string $content = "Hello world!";
}

class HelloWorldHandler
{
    public function __invoke(HelloWorldMessage $message): void
    {
        echo $message->content;
    }
}

$dns = 'amqp://guest:guest@host.docker.internal:5672/%2f/messages';
$options = [
    'exchange' => [
        'name' => 'foo',
        'type' => \AMQP_EX_TYPE_FANOUT
    ],
    'queues' => [
        'bar' => [],
    ]
];
$transport = (new AmqpTransportFactory)->createTransport($dns, $options, new Serializer());

$transport->send(new Envelope(new HelloWorldMessage(), [
    new AmqpStamp('messages')
]));

$bus = new MessageBus([
    new HandleMessageMiddleware(new HandlersLocator([
        HelloWorldMessage::class => [new HelloWorldHandler()],
    ])),
]);

$worker = new Worker([$transport], $bus);
$worker->run();