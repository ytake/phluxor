<?php

declare(strict_types=1);

namespace Phluxor\Mspc;

class Node
{
    /** @var Node|null  */
    private Node|null $next = null;

    /**
     * @param mixed|null $val
     */
    public function __construct(
        private mixed $val = null
    ) {
    }

    /**
     * @return Node|null
     */
    public function getNext(): ?Node
    {
        return $this->next;
    }

    /**
     * @param Node|null $next
     * @return void
     */
    public function replaceNext(?Node $next): void
    {
        $this->next = $next;
    }

    /**
     * @return mixed
     */
    public function value(): mixed
    {
        return $this->val;
    }

    /**
     * @param mixed $val
     * @return void
     */
    public function replaceValue(mixed $val): void
    {
        $this->val = $val;
    }
}
