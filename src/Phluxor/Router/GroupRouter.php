<?php

declare(strict_types=1);

namespace Phluxor\Router;

use Override;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Ref;
use Phluxor\ActorSystem\RefSet;
use Phluxor\ActorSystem\Props;
use Phluxor\Router\Exception\RouterStateNotFoundException;

class GroupRouter implements ConfigInterface
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
        $state->registerRoutees($this->routees);
    }

    #[Override] public function createRouterState(): StateInterface
    {
        throw new RouterStateNotFoundException('Router state not found.');
    }
}
