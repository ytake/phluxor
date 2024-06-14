<?php

declare(strict_types=1);

namespace Phluxor\Router;

use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Ref;
use Phluxor\ActorSystem\RefSet;
use Phluxor\ActorSystem\Props;

readonly class GroupRouter
{
    /**
     * @param RefSet $routees
     */
    public function __construct(
        private RefSet $routees
    ) {
    }

    public function routerType(): RouterType
    {
        return RouterType::GroupRouterType;
    }

    public function onStarter(ContextInterface $context, Props $props, StateInterface $state): void
    {
        $this->routees->forEach(
            fn(int $int, Ref $pid) => $context->watch($pid)
        );
        $state->setSender($context);
        $state->registerRoute($this->routees);
    }
}
