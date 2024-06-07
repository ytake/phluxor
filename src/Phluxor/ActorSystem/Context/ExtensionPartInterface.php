<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Context;

use Phluxor\Value\ContextExtensionId;

interface ExtensionPartInterface
{
    /**
     * @param ContextExtensionId $id
     * @return ContextExtensionId
     */
    public function get(ContextExtensionId $id): ContextExtensionId;

    public function set(ContextExtensionId $id): void;
}
