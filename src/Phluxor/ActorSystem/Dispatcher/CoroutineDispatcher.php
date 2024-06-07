<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Dispatcher;

use Closure;

use function go;

readonly class CoroutineDispatcher implements DispatcherInterface
{
    public function __construct(
        private int $throughput = 0
    ) {
    }

    public function schedule(DispatcherFunctionInterface|Closure $fn): void
    {
        go($fn);
    }

    public function throughput(): int
    {
        return $this->throughput;
    }
}
