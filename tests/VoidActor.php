<?php

declare(strict_types=1);

namespace Test;

use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Message\ActorInterface;

/**
 * Class VoidActor / test only
 * @package Test
 */
class VoidActor implements ActorInterface
{
    public function receive(ContextInterface $context): void
    {
        // none
    }
}
