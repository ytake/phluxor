<?php

declare(strict_types=1);

namespace Phluxor\Router;

use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Message\ActorInterface;
use Phluxor\ActorSystem\Message\ProducerInterface;

class InitProducer implements ProducerInterface
{
    public function __invoke(): ActorInterface
    {
        return new class implements ActorInterface {
            public function receive(ContextInterface $context): void
            {
                // none
            }
        };
    }
}
