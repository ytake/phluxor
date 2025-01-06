<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Middleware\Propagator;

use Closure;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context;
use Phluxor\ActorSystem\Props;
use Phluxor\ActorSystem\Props\ContextDecoratorInterface;
use Phluxor\ActorSystem\Props\ReceiverMiddlewareInterface;
use Phluxor\ActorSystem\Props\SenderMiddlewareInterface;
use Phluxor\ActorSystem\Props\SpawnMiddlewareInterface;
use Phluxor\ActorSystem\SpawnFunctionInterface;
use Phluxor\ActorSystem\SpawnResult;

readonly class SpawnMiddlewareProcess implements SpawnFunctionInterface
{
    /**
     * @param Closure|SpawnFunctionInterface $next
     * @param SpawnMiddlewareInterface[] $spawnMiddleware
     * @param SenderMiddlewareInterface[] $senderMiddleware
     * @param ReceiverMiddlewareInterface[] $receiverMiddleware
     * @param ContextDecoratorInterface[] $contextDecorators
     */
    public function __construct(
        private Closure|SpawnFunctionInterface $next,
        private array $spawnMiddleware = [],
        private array $senderMiddleware = [],
        private array $receiverMiddleware = [],
        private array $contextDecorators = []
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(
        ActorSystem $actorSystem,
        string $id,
        Props $props,
        Context\SpawnerInterface $parentContext
    ): SpawnResult {
        if (!empty($this->spawnMiddleware)) {
            $props = $props->configure(
                Props::withSpawnMiddleware(...$this->spawnMiddleware)
            );
        }
        if (!empty($this->senderMiddleware)) {
            $props = $props->configure(
                Props::withSenderMiddleware(...$this->senderMiddleware)
            );
        }
        if (!empty($this->receiverMiddleware)) {
            $props = $props->configure(
                Props::withReceiverMiddleware(...$this->receiverMiddleware)
            );
        }
        if (!empty($this->contextDecorators)) {
            $props = $props->configure(
                Props::withContextDecorator(...$this->contextDecorators)
            );
        }
        $next = $this->next;
        return $next($actorSystem, $id, $props, $parentContext);
    }
}
