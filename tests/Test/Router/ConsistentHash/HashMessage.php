<?php

declare(strict_types=1);

namespace Test\Router\ConsistentHash;

use Phluxor\Router\ConsistentHash\HasherInterface;

use function md5;

readonly class HashMessage implements HasherInterface
{
    public function __construct(
        public string $message
    ) {
    }

    public function hash(): string
    {
        return md5($this->message);
    }
}
