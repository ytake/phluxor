<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Props;

use Phluxor\ActorSystem\Message\ContextDecoratorFunctionInterface;

interface ContextDecoratorInterface
{
    /**
     * @param ContextDecoratorFunctionInterface $next
     * @return ContextDecoratorFunctionInterface
     */
    public function __invoke(
        ContextDecoratorFunctionInterface $next
    ): ContextDecoratorFunctionInterface;
}
