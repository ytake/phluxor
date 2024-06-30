<?php

declare(strict_types=1);

namespace Phluxor\Router;

use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Context\SenderInterface;
use Phluxor\ActorSystem\Ref;
use Phluxor\ActorSystem\RefSet;

class BroadcastRouterState implements StateInterface
{
    public function __construct(
        private RefSet $routees = new RefSet(),
        private ?SenderInterface $sender = null
    ) {
    }

    public function routeMessage(mixed $message): void
    {
        $this->routees->forEach(
            fn(int $int, Ref $ref) => $this->sender->send($ref, $message)
        );
    }

    public function registerRoutees(RefSet $routees): void
    {
        $this->routees = $routees;
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
