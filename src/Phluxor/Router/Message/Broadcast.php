<?php

declare(strict_types=1);

namespace Phluxor\Router\Message;

final readonly class Broadcast
{
    /**
     * @param mixed $message
     */
    public function __construct(
        private mixed $message
    ) {
    }

    public function getMessage(): mixed
    {
        return $this->message;
    }
}
