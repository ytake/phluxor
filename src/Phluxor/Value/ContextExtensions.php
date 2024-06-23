<?php

declare(strict_types=1);

namespace Phluxor\Value;

use SplFixedArray;

final readonly class ContextExtensions
{
    /**
     * @param SplFixedArray<ExtensionInterface> $extensions
     */
    public function __construct(
        private SplFixedArray $extensions = new SplFixedArray(100)
    ) {
    }

    /**
     * @param ContextExtensionId $id
     * @return ExtensionInterface
     */
    public function get(ContextExtensionId $id): ExtensionInterface
    {
        return $this->extensions->offsetGet($id->value());
    }

    /**
     * @param ExtensionInterface $extension
     * @return void
     */
    public function set(ExtensionInterface $extension): void
    {
        $extensionId = $extension->extensionID();
        $this->extensions->offsetSet($extensionId->value(), $extension);
    }
}
