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
    private ?StateInterface $state = null;

    public function __construct(
        private ActorSystem $system,
        private GroupRouter|null $router = null,
    ) {
    }

    public function routerType(): RouterType
    {
        return RouterType::GroupRouterType;
    }

    public function onStarter(ContextInterface $context, Props $props, StateInterface $state): void
    {
        // TODO: Implement onStarter() method.
    }

    public function setRouterState(StateInterface $state): void
    {
        $this->state = $state;
    }

    public function createRouterState(): StateInterface
    {
        if ($this->state !== null) {
            return $this->state;
        }
        return new TestRouterState($this->system);
    }
}