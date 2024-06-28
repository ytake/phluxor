<?php

declare(strict_types=1);

namespace Phluxor\Value;

final class ContextExtensions
{
    /** @var ExtensionInterface[] */
    private array $extensions;

    public function __construct()
    {
        $this->extensions = array_fill(0, 3, null);
    }

    /**
     * @param ContextExtensionID $id
     * @return ExtensionInterface|null
     */
    public function get(ContextExtensionID $id): ?ExtensionInterface
    {
        return $this->extensions[$id->value()] ?? null;
    }

    /**
     * @param ExtensionInterface $extension
     * @return void
     */
    public function set(ExtensionInterface $extension): void
    {
        $id = $extension->extensionID()->value();
        if ($id >= count($this->extensions)) {
            $newExtensions = array_fill(0, $id * 2, null);
            $this->array_copy($this->extensions, $newExtensions, count($this->extensions));
            $this->extensions = $newExtensions;
        }
        $this->extensions[$id] = $extension;
    }

    function array_copy(array $src, array &$dest, int $length): void
    {
        for ($i = 0; $i < $length; $i++) {
            $dest[$i] = $src[$i];
        }
    }
}
