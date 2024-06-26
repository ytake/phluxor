<?php

declare(strict_types=1);

namespace Phluxor\Router;

use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Context\SenderInterface;
use Phluxor\ActorSystem\RefSet;

interface StateInterface
{
    public function routeMessage(mixed $message): void;

    public function registerRoutees(RefSet $routees): void;

    public function getRoutees(): RefSet;

    public function setSender(ContextInterface|SenderInterface $sender): void;
}
