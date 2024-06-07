<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Throwable;

interface ReenterAfterInterface
{
    /**
     * Invokes the method with the given parameters.
     *
     * @param mixed $res The result parameter.
     * @param Throwable|null $error The error parameter, set to null if no error occurred.
     * @return void
     */
    public function __invoke(mixed $res, Throwable|null $error): void;
}
