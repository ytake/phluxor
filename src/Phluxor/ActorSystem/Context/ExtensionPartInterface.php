<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Context;

use Phluxor\Value\ContextExtensionId;
use Phluxor\Value\ExtensionInterface;

interface ExtensionPartInterface
{
    /**
     * @param ContextExtensionId $id
     * @return ExtensionInterface
     */
    public function get(ContextExtensionId $id): ExtensionInterface;

    /**
     * @param ExtensionInterface $extension
     * @return void
     */
    public function set(ExtensionInterface $extension): void;
}
