<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Middleware\Propagator;

use Closure;
use Phluxor\ActorSystem\Props\ContextDecoratorInterface;
use Phluxor\ActorSystem\Props\ReceiverMiddlewareInterface;
use Phluxor\ActorSystem\Props\SenderMiddlewareInterface;
use Phluxor\ActorSystem\Props\SpawnMiddlewareInterface;

class MiddlewarePropagation
{
    /** @var Closure|SpawnMiddlewareInterface[]  */
    private array $spawnMiddleware = [];

    /** @var Closure|SenderMiddlewareInterface[] */
    private array $senderMiddleware = [];

    /** @var Closure|ReceiverMiddlewareInterface[] */
    private array $receiverMiddleware = [];

    /** @var Closure|ContextDecoratorInterface[] */
    private array $contextDecorators = [];

    public function __construct(
        private SpawnMiddleware $spawnMiddlewareProcess = new SpawnMiddleware()
    ){ }

    public function setSpawnMiddleware(Closure|SpawnMiddlewareInterface ...$middleware): MiddlewarePropagation
    {
        $this->spawnMiddleware = array_merge($this->spawnMiddleware, $middleware);
        return $this;
    }

    public function setItselfForwarded(): MiddlewarePropagation
    {
        return $this->setSpawnMiddleware($this->spawnMiddleware());
    }

    public function setSenderMiddleware(Closure|SenderMiddlewareInterface ...$middleware): MiddlewarePropagation
    {
        $this->senderMiddleware = array_merge($this->senderMiddleware, $middleware);
        return $this;
    }

    public function setReceiverMiddleware(Closure|ReceiverMiddlewareInterface ...$middleware): MiddlewarePropagation
    {
        $this->receiverMiddleware = array_merge($this->receiverMiddleware, $middleware);
        return $this;
    }

    public function setContextDecorator(Closure|ContextDecoratorInterface ...$decorators): MiddlewarePropagation
    {
        $this->contextDecorators = array_merge($this->contextDecorators, $decorators);
        return $this;
    }

    /**
     * @return SpawnMiddlewareInterface
     */
    public function spawnMiddleware(): SpawnMiddlewareInterface
    {
        return $this->spawnMiddlewareProcess
            ->setSpawnMiddleware(...$this->spawnMiddleware)
            ->setSenderMiddleware(...$this->senderMiddleware)
            ->setReceiverMiddleware(...$this->receiverMiddleware)
            ->setContextDecorator(...$this->contextDecorators);
    }
}
