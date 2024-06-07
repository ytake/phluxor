<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Dispatcher;

use Closure;

interface DispatcherInterface
{
    /**
     * @param DispatcherFunctionInterface|Closure $fn
     * @return void
     */
    public function schedule(DispatcherFunctionInterface|Closure $fn): void;

    /**
     * @return int
     */
    public function throughput(): int;
}
