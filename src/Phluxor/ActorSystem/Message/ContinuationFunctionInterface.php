<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Message;

interface ContinuationFunctionInterface
{
    /**
     * @return void
     */
    public function __invoke(): void;
}
