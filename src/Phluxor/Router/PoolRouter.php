<?php

declare(strict_types=1);

namespace Phluxor\Router;

use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Ref;
use Phluxor\ActorSystem\RefSet;
use Phluxor\ActorSystem\Props;

readonly class PoolRouter
{
    /**
     * @param int $poolSize
     */
    public function __construct(
        private int $poolSize
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
        $state->registerRoute($routee);
    }
}
