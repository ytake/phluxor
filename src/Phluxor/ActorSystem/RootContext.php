<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Closure;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context\SenderInterface;
use Phluxor\ActorSystem\Context\SpawnerInterface;
use Phluxor\ActorSystem\Message\ActorInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\Message\SenderFunctionInterface;
use Phluxor\ActorSystem\Props\SenderMiddlewareInterface;
use Phluxor\ActorSystem\Props\SpawnMiddlewareInterface;
use Psr\Log\LoggerInterface;

class RootContext implements
    ActorSystem\Context\SpawnerInterface,
    ActorSystem\Context\SenderInterface,
    ActorSystem\Context\StopperPartInterface
{
    /** @var Closure(SenderInterface, Pid, MessageEnvelope): void|SenderFunctionInterface|null */
    private Closure|SenderFunctionInterface|null $senderMiddleware;

    /** @var Closure(ActorSystem, string, Props, SpawnerInterface): SpawnResult|SpawnFunctionInterface|null */
    private Closure|SpawnFunctionInterface|null $spawnMiddleware = null;
    private SupervisorStrategyInterface|null $guardianStrategy = null;

    /**
     * @param ActorSystem $actorSystem
     * @param string[] $headers
     * @param Closure[]|SenderMiddlewareInterface[]  $senderMiddlewares
     */
    public function __construct(
        private readonly ActorSystem $actorSystem,
        private array $headers = [],
        array $senderMiddlewares = []
    ) {
        $this->senderMiddleware = makeSenderMiddlewareChain(
            $senderMiddlewares,
            new ActorSystem\Middleware\DefaultRootContextSender($this->actorSystem),
        );
    }

    public function copy(): RootContext
    {
        return $this;
    }

    public function actorSystem(): ActorSystem
    {
        return $this->actorSystem;
    }

    public function logger(): LoggerInterface
    {
        return $this->actorSystem->getLogger();
    }

    public function withHeader(string $key, string $value): RootContext
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * @param SenderMiddlewareInterface ...$middleware
     * @return $this
     */
    public function withSenderMiddleware(SenderMiddlewareInterface ...$middleware): RootContext
    {
        $this->senderMiddleware = makeSenderMiddlewareChain(
            $middleware,
            new ActorSystem\Middleware\DefaultRootContextSender($this->actorSystem),
        );
        return $this;
    }

    /**
     * @param Closure|SpawnMiddlewareInterface ...$middleware
     * @return $this
     */
    public function withSpawnMiddleware(Closure|SpawnMiddlewareInterface ...$middleware): RootContext
    {
        $this->spawnMiddleware = makeSpawnMiddlewareChain(
            $middleware,
            new ActorSystem\Spawner\SpawnFunction()
        );
        return $this;
    }

    /**
     * @param SupervisorStrategyInterface $supervisorStrategy
     * @return $this
     */
    public function withGuardian(SupervisorStrategyInterface $supervisorStrategy): RootContext
    {
        $this->guardianStrategy = $supervisorStrategy;
        return $this;
    }

    public function parent(): Pid|null
    {
        return null;
    }

    public function self(): Pid|null
    {
        if ($this->guardianStrategy != null) {
            return $this->actorSystem->getGuardiansValue()->getGuardianPid($this->guardianStrategy);
        }
        return null;
    }

    public function sender(): Pid|null
    {
        return null;
    }

    /**
     * @return ActorInterface
     */
    public function actor(): ActorInterface
    {
        throw new ActorSystem\Exception\RootContextActorException(
            'RootContext cannot be used as an actor'
        );
    }

    public function message(): mixed
    {
        return null;
    }

    public function messageHeader(): ReadonlyMessageHeaderInterface
    {
        return new ActorSystem\Message\MessageHeader($this->headers);
    }

    /**
     * @param Pid|null $pid
     * @param mixed $message
     * @return void
     */
    public function send(?Pid $pid, mixed $message): void
    {
        $this->sendUserMessage($pid, $message);
    }

    /**
     * @param Pid|null $pid
     * @param mixed $message
     * @return void
     */
    public function request(?Pid $pid, mixed $message): void
    {
        $this->sendUserMessage($pid, $message);
    }

    public function requestWithCustomSender(?Pid $pid, mixed $message, ?Pid $sender): void
    {
        $env = new MessageEnvelope(
            header: null,
            message: $message,
            sender: $sender,
        );
        $this->sendUserMessage($pid, $env);
    }

    public function requestFuture(?Pid $pid, mixed $message, int $duration): Future
    {
        $future = Future::create($this->actorSystem, $duration);
        $env = new MessageEnvelope(
            header: null,
            message: $message,
            sender: $future->pid(),
        );
        $this->sendUserMessage($pid, $env);
        return $future;
    }

    private function sendUserMessage(?Pid $pid, mixed $envelope): void
    {
        if ($this->senderMiddleware != null) {
            $call = $this->senderMiddleware;
            $call($this, $pid, $envelope);
        } else {
            $pid?->sendUserMessage($this->actorSystem, $envelope);
        }
    }

    /**
     * starts a new actor based on props and named with a unique id.
     * @param Props $props
     * @return Pid|null
     */
    public function spawn(Props $props): Pid|null
    {
        $result = $this->spawnNamed($props, $this->actorSystem->getProcessRegistry()->nextId());
        if ($result->isError() != null) {
            throw $result->isError();
        }
        return $result->getPid();
    }

    /**
     * starts a new actor based on props and named with a unique id.
     * @param Props $props
     * @param string $prefix
     * @return Pid|null
     */
    public function spawnPrefix(Props $props, string $prefix): Pid|null
    {
        $result = $this->spawnNamed($props, $prefix . $this->actorSystem->getProcessRegistry()->nextId());
        if ($result->isError() != null) {
            throw $result->isError();
        }
        return $result->getPid();
    }

    /**
     * starts a new actor based on props and named using the specified name
     * @param Props $props
     * @param string $name
     * @return SpawnResult
     */
    public function spawnNamed(Props $props, string $name): SpawnResult
    {
        $rt = $this;
        if ($props->getGuardianStrategy() != null) {
            $rt = $rt->copy()->withGuardian($props->getGuardianStrategy());
        }
        if ($rt->spawnMiddleware != null) {
            $call = $rt->spawnMiddleware;
            return $call($this->actorSystem, $name, $props, $rt);
        }
        return $props->spawn($this->actorSystem, $name, $rt);
    }

    /**
     * @param Pid|null $pid
     * @return void
     */
    public function stop(?Pid $pid): void
    {
        if ($pid == null) {
            return;
        }
        $pid->ref($this->actorSystem)?->stop($pid);
    }

    public function stopFuture(?Pid $pid): Future|null
    {
        $future = Future::create($this->actorSystem, 10);
        if ($pid != null) {
            $pid->sendSystemMessage(
                $this->actorSystem,
                new ActorSystem\ProtoBuf\Watch([
                    'watcher' => $future->pid()?->protobufPid(),
                ])
            );
            $this->stop($pid);
            return $future;
        }
        return null;
    }

    public function poison(?Pid $pid): void
    {
        $pid?->sendUserMessage($this->actorSystem(), new ActorSystem\ProtoBuf\PoisonPill());
    }

    public function poisonFuture(?Pid $pid): Future|null
    {
        $future = Future::create($this->actorSystem, 10);
        if ($pid != null) {
            $pid->sendSystemMessage(
                $this->actorSystem(),
                new ActorSystem\ProtoBuf\Watch([
                    'watcher' => $future->pid()?->protobufPid(),
                ])
            );
            $this->poison($pid);
            return $future;
        }
        return null;
    }
}
