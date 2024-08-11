<?php

declare(strict_types=1);

namespace Phluxor\Swoole\OpenTelemetry;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\ContextStorageScopeInterface;
use OpenTelemetry\Context\ScopeInterface;

readonly class SwooleContextScope implements ScopeInterface, ContextStorageScopeInterface
{
    public function __construct(
        private ContextStorageScopeInterface $scope,
        private SwooleContextHandler $handler
    ) {
    }

    public function offsetExists($offset): bool
    {
        return $this->scope->offsetExists($offset);
    }

    /**
     * @phan-suppress PhanUndeclaredClassAttribute
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->scope->offsetGet($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->scope->offsetSet($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->scope->offsetUnset($offset);
    }

    public function context(): ContextInterface
    {
        return $this->scope->context();
    }

    public function detach(): int
    {
        $this->handler->switchToActiveCoroutine();

        return $this->scope->detach();
    }
}
