<?php

declare(strict_types=1);

namespace Phluxor\Router;

use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Props;

interface ConfigInterface
{
    public function routerType(): RouterType;

    public function onStarter(
        ContextInterface $context,
        Props $props,
        StateInterface $state
    ): void;

    public function createRouterState(): StateInterface;
}
