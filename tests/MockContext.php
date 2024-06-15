<?php

declare(strict_types=1);

namespace Test;

use Closure;
use DateInterval;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Future;
use Phluxor\ActorSystem\Message\ActorInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\Props;
use Phluxor\ActorSystem\ReadonlyMessageHeaderInterface;
use Phluxor\ActorSystem\ReenterAfterInterface;
use Phluxor\ActorSystem\Ref;
use Phluxor\ActorSystem\SpawnResult;
use Phluxor\Value\ContextExtensionId;
use Psr\Log\LoggerInterface;

class MockContext implements \Phluxor\ActorSystem\Context\ContextInterface
{
    private ?Closure $messageHandler = null;

    /**
     * @param Closure(): mixed $closure
     * @return void
     */
    public function messageHandle(Closure $closure): void
    {
        $this->messageHandler = $closure;
    }

    public function receiveTimeout(): DateInterval
    {
        // TODO: Implement receiveTimeout() method.
    }

    public function children(): array
    {
        // TODO: Implement children() method.
    }

    public function respond(mixed $response): void
    {
        // TODO: Implement respond() method.
    }

    public function stash(): void
    {
        // TODO: Implement stash() method.
    }

    public function watch(Ref $pid): void
    {
        // TODO: Implement watch() method.
    }

    public function unwatch(Ref $pid): void
    {
        // TODO: Implement unwatch() method.
    }

    public function setReceiveTimeout(DateInterval $dateInterval): void
    {
        // TODO: Implement setReceiveTimeout() method.
    }

    public function cancelReceiveTimeout(): void
    {
        // TODO: Implement cancelReceiveTimeout() method.
    }

    public function forward(Ref $pid): void
    {
        // TODO: Implement forward() method.
    }

    public function reenterAfter(Future $future, ReenterAfterInterface $reenterAfter): void
    {
        // TODO: Implement reenterAfter() method.
    }

    public function get(ContextExtensionId $id): ContextExtensionId
    {
        // TODO: Implement get() method.
    }

    public function set(ContextExtensionId $id): void
    {
        // TODO: Implement set() method.
    }

    public function parent(): Ref|null
    {
        // TODO: Implement parent() method.
    }

    public function self(): Ref|null
    {
        // TODO: Implement self() method.
    }

    public function actor(): ActorInterface
    {
        // TODO: Implement actor() method.
    }

    public function actorSystem(): ActorSystem
    {
        // TODO: Implement actorSystem() method.
    }

    public function logger(): LoggerInterface
    {
        // TODO: Implement logger() method.
    }

    public function message(): mixed
    {
        if ($this->messageHandler != null) {
            $handle = $this->messageHandler;
            return $handle();
        }
        return null;
    }

    public function messageHeader(): ReadonlyMessageHeaderInterface
    {
        // TODO: Implement messageHeader() method.
    }

    public function receive(?MessageEnvelope $envelope): void
    {
        // TODO: Implement receive() method.
    }

    public function sender(): Ref|null
    {
        // TODO: Implement sender() method.
    }

    public function send(?Ref $pid, mixed $message): void
    {
        // TODO: Implement send() method.
    }

    public function request(?Ref $pid, mixed $message): void
    {
        // TODO: Implement request() method.
    }

    public function requestWithCustomSender(?Ref $pid, mixed $message, ?Ref $sender): void
    {
        // TODO: Implement requestWithCustomSender() method.
    }

    public function requestFuture(?Ref $pid, mixed $message, int $duration): Future
    {
        // TODO: Implement requestFuture() method.
    }

    public function spawn(Props $props): Ref|null
    {
        // TODO: Implement spawn() method.
    }

    public function spawnPrefix(Props $props, string $prefix): Ref|null
    {
        // TODO: Implement spawnPrefix() method.
    }

    public function spawnNamed(Props $props, string $name): SpawnResult
    {
        // TODO: Implement spawnNamed() method.
    }

    public function stop(?Ref $pid): void
    {
        // TODO: Implement stop() method.
    }

    public function stopFuture(?Ref $pid): Future|null
    {
        // TODO: Implement stopFuture() method.
    }

    public function poison(?Ref $pid): void
    {
        // TODO: Implement poison() method.
    }

    public function poisonFuture(?Ref $pid): Future|null
    {
        // TODO: Implement poisonFuture() method.
    }
}
