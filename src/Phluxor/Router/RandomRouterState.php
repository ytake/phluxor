<?php

declare(strict_types=1);

namespace Phluxor\Router;

use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Context\SenderInterface;
use Phluxor\ActorSystem\RefSet;
use Phluxor\Router\Exception\InvalidIndexException;

use function rand;

class RandomRouterState implements StateInterface
{
    public function __construct(
        private RefSet $routees = new RefSet(),
        private ?SenderInterface $sender = null
    ) {
    }

    public function routeMessage(mixed $message): void
    {
        $ref = $this->routees->get(rand(0, $this->routees->len()));
        if ($ref === null) {
            throw new InvalidIndexException('Invalid route index, not found routee.');
        }
        $this->sender->send($ref, $message);
    }

    public function registerRoutees(RefSet $routes): void
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
