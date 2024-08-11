<?php

declare(strict_types=1);

namespace Phluxor\Swoole\OpenTelemetry;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\Context\ContextStorageScopeInterface;
use OpenTelemetry\Context\ExecutionContextAwareInterface;

readonly class SwooleContextStorage implements ContextStorageInterface, ExecutionContextAwareInterface
{
    private SwooleContextHandler $handler;

    /**
     * @param ContextStorageInterface&ExecutionContextAwareInterface $storage
     */
    public function __construct(
        private ContextStorageInterface $storage
    ) {
        $this->handler = new SwooleContextHandler($storage);
    }

    public function fork($id): void
    {
        $this->handler->switchToActiveCoroutine();

        $this->storage->fork($id);
    }

    public function switch($id): void
    {
        $this->handler->switchToActiveCoroutine();

        $this->storage->switch($id);
    }

    public function destroy($id): void
    {
        $this->handler->switchToActiveCoroutine();

        $this->storage->destroy($id);
    }

    public function scope(): ?ContextStorageScopeInterface
    {
        $this->handler->switchToActiveCoroutine();

        if (($scope = $this->storage->scope()) === null) {
            return null;
        }

        return new SwooleContextScope($scope, $this->handler);
    }

    public function current(): ContextInterface
    {
        $this->handler->switchToActiveCoroutine();

        return $this->storage->current();
    }

    public function attach(ContextInterface $context): ContextStorageScopeInterface
    {
        $this->handler->switchToActiveCoroutine();
        $this->handler->splitOffChildCoroutines();

        $scope = $this->storage->attach($context);

        return new SwooleContextScope($scope, $this->handler);
    }
}
