<?php

declare(strict_types=1);

namespace Phluxor\Router;

use Override;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\RefSet;
use Phluxor\ActorSystem\Props;
use Phluxor\Router\Exception\RouterStateNotFoundException;

class PoolRouter implements ConfigInterface
{
    /**
     * @param int $poolSize
     */
    public function __construct(
        private readonly int $poolSize
    ) {
    }

    public function routerType(): RouterType
    {
        return RouterType::PoolRouterType;
    }

    public function onStarter(ContextInterface $context, Props $props, StateInterface $state): void
    {
        $routee = new RefSet();
        for ($i = 0; $i < $this->poolSize; $i++) {
            $routee->add($context->spawn($props));
        }
        $state->setSender($context);
        $state->registerRoutees($routee);
    }

    #[Override] public function createRouterState(): StateInterface
    {
        throw new RouterStateNotFoundException('Router state not found.');
    }
}
