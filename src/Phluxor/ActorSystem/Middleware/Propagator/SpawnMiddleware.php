<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Middleware\Propagator;

use Closure;
use Phluxor\ActorSystem\Props\ContextDecoratorInterface;
use Phluxor\ActorSystem\Props\ReceiverMiddlewareInterface;
use Phluxor\ActorSystem\Props\SenderMiddlewareInterface;
use Phluxor\ActorSystem\Props\SpawnMiddlewareInterface;
use Phluxor\ActorSystem\SpawnFunctionInterface;

class SpawnMiddleware implements SpawnMiddlewareInterface
{
    /** @var SpawnMiddlewareInterface[] */
    private array $spawnMiddleware = [];

    /** @var SenderMiddlewareInterface[] */
    private array $senderMiddleware = [];

    /** @var ReceiverMiddlewareInterface[] */
    private array $receiverMiddleware = [];

    /** @var ContextDecoratorInterface[] */
    private array $contextDecorators = [];

    public function setSpawnMiddleware(SpawnMiddlewareInterface ...$middleware): self
    {
        $this->spawnMiddleware = array_merge($this->spawnMiddleware, $middleware);
        return $this;
    }

    public function setSenderMiddleware(SenderMiddlewareInterface ...$middleware): self
    {
        $this->senderMiddleware = array_merge($this->senderMiddleware, $middleware);
        return $this;
    }

    public function setReceiverMiddleware(ReceiverMiddlewareInterface ...$middleware): self
    {
        $this->receiverMiddleware = array_merge($this->receiverMiddleware, $middleware);
        return $this;
    }

    public function setContextDecorator(ContextDecoratorInterface ...$decorators): self
    {
        $this->contextDecorators = array_merge($this->contextDecorators, $decorators);
        return $this;
    }

    /**
     * @param Closure|SpawnFunctionInterface $next
     * @return Closure|SpawnFunctionInterface
     */
    public function __invoke(Closure|SpawnFunctionInterface $next): Closure|SpawnFunctionInterface
    {
        return new SpawnMiddlewareProcess(
            $next,
            $this->spawnMiddleware,
            $this->senderMiddleware,
            $this->receiverMiddleware,
            $this->contextDecorators
        );
    }
}
