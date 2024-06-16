<?php

declare(strict_types=1);

namespace Test\Router;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Context\SenderInterface;
use Phluxor\ActorSystem\Ref;
use Phluxor\ActorSystem\RefSet;
use Phluxor\Router\StateInterface;

class TestRouterState implements StateInterface
{
    public function __construct(
        private ActorSystem $system,
        private RefSet|null $routees = null,
        private SenderInterface|null $sender = null
    ) {
    }

    public function routeMessage(mixed $message): void
    {
        $this->routees->forEach(
            fn(int $_, Ref $ref) => $this->system->root()->send($ref, $message)
        );
    }

    public function registerRoute(RefSet $routes): void
    {
        $this->routees = $routes;
    }

    public function getRoutees(): RefSet
    {
        return $this->routees;
    }

    public function setSender(ContextInterface|SenderInterface $sender): void
    {
        $this->sender = $sender;
    }
}
