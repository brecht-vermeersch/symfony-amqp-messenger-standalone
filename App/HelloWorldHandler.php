<?php

namespace App;

class HelloWorldHandler
{
    public function __invoke(HelloWorldMessage $message): void
    {
        echo "$message->content\n";
        sleep(1);
    }
}