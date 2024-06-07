<?php

declare(strict_types=1);

namespace Phluxor\Value;

use SplFixedArray;

final class ContextExtensions
{
    /**
     * @param SplFixedArray<ContextExtensionId> $extensions
     */
    public function __construct(
        private SplFixedArray $extensions = new SplFixedArray(100)
    ) {
    }

    /**
     * @param ContextExtensionId $id
     * @return ContextExtensionId
     */
    public function get(ContextExtensionId $id): ContextExtensionId
    {
        return $this->extensions->offsetGet($id->value());
    }

    /**
     * @param ContextExtensionId $id
     * @return void
     */
    public function set(ContextExtensionId $id): void
    {
        if ($id->value() >= $this->extensions->getSize()) {
            $this->extensions = new SplFixedArray($id->value() * 2);
        }
        $this->extensions->offsetSet($id->value(), $id);
    }
}
