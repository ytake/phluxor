<?php

declare(strict_types=1);

namespace Test\Router;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Props;
use Phluxor\Router\ConfigInterface;
use Phluxor\Router\GroupRouter;
use Phluxor\Router\RouterType;
use Phluxor\Router\StateInterface;

class TestGroupRouter implements ConfigInterface
{
    public function __construct(
        private ActorSystem $system,
        private GroupRouter|null $router = null,
    ) {
    }

    public function routerType(): RouterType
    {
        // TODO: Implement routerType() method.
    }

    public function onStarter(ContextInterface $context, Props $props, StateInterface $state): void
    {
        // TODO: Implement onStarter() method.
    }

    public function createRouterState(): StateInterface
    {
        return new TestRouterState($this->system);
    }
}