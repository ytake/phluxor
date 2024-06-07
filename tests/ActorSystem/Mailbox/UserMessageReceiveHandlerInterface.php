<?php

namespace Test\ActorSystem\Mailbox;

interface UserMessageReceiveHandlerInterface
{
    /**
     * @param mixed $message
     * @return void
     */
    public function __invoke(mixed $message): void;
}
