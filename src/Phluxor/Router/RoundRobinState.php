<?php

declare(strict_types=1);

namespace Phluxor\Router;

use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Context\SenderInterface;
use Phluxor\ActorSystem\Ref;
use Phluxor\ActorSystem\RefSet;
use Swoole\Atomic;

class RoundRobinState implements StateInterface
{
    public function __construct(
        private RefSet $routees = new RefSet(),
        private null|ContextInterface|SenderInterface $sender = null,
        private Atomic $index = new Atomic(-1)
    ) {
    }

    public function routeMessage(mixed $message): void
    {
        $ref = $this->roundRobinRoutee();
        if ($ref === null) {
            return;
        }
        $this->sender?->send($ref, $message);
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

    private function roundRobinRoutee(): ?Ref
    {
        $i = $this->index->add();
        if ($i < 0) {
            $this->index->set(0);
            $i = 0;
        }
        $mod = $this->routees->len();
        return $this->routees->get($i % $mod);
    }
}
