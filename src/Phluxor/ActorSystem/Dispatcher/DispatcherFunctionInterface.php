<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Dispatcher;

interface DispatcherFunctionInterface
{
    /**
     * @return void
     */
    public function __invoke(): void;
}
