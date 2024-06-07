<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Phluxor\ActorSystem\Context\ContextInterface;

interface AutoRespondInterface
{
    /**
     * @param ContextInterface $context
     * @return mixed
     */
    public function getAutoResponse(ContextInterface $context): mixed;
}
