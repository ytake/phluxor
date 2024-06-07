<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Message;

use Phluxor\ActorSystem\Context\ContextInterface;

class DefaultContextDecorator implements ContextDecoratorFunctionInterface
{
    /**
     * @param ContextInterface $context
     * @return ContextInterface
     */
    public function __invoke(ContextInterface $context): ContextInterface
    {
        return $context;
    }
}
