<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Message;

final readonly class MessageBatch implements MessageBatchInterface
{
    public function __construct(
        private mixed $messages
    ) {
    }

    public function getMessages(): mixed
    {
        return $this->messages;
    }
}
