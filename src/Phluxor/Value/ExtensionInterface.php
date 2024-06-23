<?php

declare(strict_types=1);

namespace Phluxor\Value;

interface ExtensionInterface
{
    /**
     * @return ContextExtensionId
     */
    public function extensionID(): ContextExtensionId;
}
