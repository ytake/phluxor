<?php

declare(strict_types=1);

namespace Test;

use Phluxor\ActorSystem\Message\ActorInterface;
use Phluxor\ActorSystem\Message\ProducerInterface;

class NullProducer implements ProducerInterface
{
    public function __invoke(): ActorInterface
    {
        return new VoidActor();
    }
}
