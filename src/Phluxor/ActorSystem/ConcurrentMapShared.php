<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use ArrayIterator;
use ArrayObject;
use Swoole\Lock;

class ConcurrentMapShared extends ArrayObject
{
    /**
     * @param Lock $mutex
     * @param array $array
     * @param int $flags
     * @param class-string $iteratorClass
     */
    public function __construct(
        private readonly Lock $mutex = new Lock(Lock::RWLOCK),
        array $array = [],
        int $flags = 0,
        string $iteratorClass = ArrayIterator::class
    ) {
        parent::__construct($array, $flags, $iteratorClass);
    }

    public function lock(): void
    {
        $this->mutex->lock();
    }

    public function unlock(): void
    {
        $this->mutex->unlock();
    }
}
