<?php

declare(strict_types=1);

namespace Phluxor\Value;

use Swoole\Atomic;

final class ContextExtensionId
{
    /** @var int */
    private int $id;

    /**
     * @param int $value
     */
    public function __construct(int $value = 1)
    {
        $id = new Atomic($value);
        $this->id = $id->add();
    }

    public function value(): int
    {
        return $this->id;
    }
}
