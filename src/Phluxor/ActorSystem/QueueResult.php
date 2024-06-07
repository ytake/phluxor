<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

final readonly class QueueResult
{
    /**
     * @param mixed $buffer
     * @param bool $isOk
     */
    public function __construct(
        private mixed $buffer,
        private bool $isOk
    ) {
    }

    public function value(): mixed
    {
        return $this->buffer;
    }

    public function isOk(): bool
    {
        return $this->isOk;
    }

    public function valueIsNull(): bool
    {
        return $this->buffer === null;
    }
}
