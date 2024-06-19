<?php

declare(strict_types=1);

namespace Phluxor\Router;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Ref;
use Phluxor\ActorSystem\RefSet;
use Swoole\Atomic\Long;
use Swoole\Lock;

class Process implements ActorSystem\ProcessInterface
{
    private const int RUNNING = 0;
    private const int STOPPING = 1;

    public function __construct(
        private ActorSystem $actorSystem,
        private ?StateInterface $state = null,
        private ?Ref $parent = null,
        private ?Ref $router = null,
        private Lock $mutex = new Lock(Lock::MUTEX),
        private RefSet $watchers = new RefSet(),
        private Long $stopping = new Long(self::RUNNING),
    ) {
    }

    public function sendUserMessage(?Ref $pid, mixed $message): void
    {
        $envelope = ActorSystem\Message\MessageEnvelope::unwrapEnvelope($message);
        $msg = $envelope['message'];
        if ($msg instanceof ActorSystem\ProtoBuf\PoisonPill) {
            $this->poison($pid);
            return;
        }
        if ((new RouterMessage($msg))->isManagementMessage()) {
            $this->state->routeMessage($message);
            return;
        }
        $r = $this->actorSystem->getProcessRegistry()->get($this->router);
        if (!$r->isProcess()) {
            return;
        }
        $r->getProcess()->sendUserMessage($pid, $message);
    }

    public function sendSystemMessage(Ref $pid, mixed $message): void
    {
        switch (true) {
            case $message instanceof ActorSystem\ProtoBuf\Watch:
                if ($this->stopping->get() == self::STOPPING) {
                    $r = $this->actorSystem->getProcessRegistry()->get($message->getWatcher());
                    if ($r->isProcess()) {
                        $watcher = $message->getWatcher();
                        if ($watcher != null) {
                            $r->getProcess()->sendSystemMessage(
                                new Ref($watcher),
                                new ActorSystem\ProtoBuf\Terminated(['who' => $pid])
                            );
                        }
                    }
                }
                $this->mutex->lock();
                $this->watchers->add(new Ref($message->getWatcher()));
                $this->mutex->unlock();
                break;
            case $message instanceof ActorSystem\ProtoBuf\Unwatch:
                $this->mutex->lock();
                $this->watchers->remove(new Ref($message->getWatcher()));
                $this->mutex->unlock();
                break;
            case $message instanceof ActorSystem\ProtoBuf\Stop:
                $terminate = new ActorSystem\ProtoBuf\Terminated(['who' => $pid]);
                $this->mutex->lock();
                $this->watchers->forEach(function (int $_, Ref $ref) use ($terminate) {
                    if (!$ref->equal($this->parent)) {
                        $r = $this->actorSystem->getProcessRegistry()->get($ref);
                        if ($r->isProcess()) {
                            $r->getProcess()->sendSystemMessage($ref, $terminate);
                        }
                    }
                });
                if ($this->parent != null) {
                    $r = $this->actorSystem->getProcessRegistry()->get($this->parent);
                    if ($r->isProcess()) {
                        $r->getProcess()->sendSystemMessage($this->parent, $terminate);
                    }
                }
                $this->mutex->unlock();
                break;
            default:
                $r = $this->actorSystem->getProcessRegistry()->get($this->router);
                if ($r->isProcess()) {
                    $r->getProcess()->sendSystemMessage($pid, $message);
                }
                break;
        }
    }

    public function stop(Ref $pid): void
    {
        $current = $this->stopping->get();
        if ($current === self::STOPPING) {
            return;
        }
        $this->stopping->cmpset($current, self::STOPPING);
        $this->actorSystem->root()->stopFuture($this->router)->wait();
        $this->actorSystem->getProcessRegistry()->remove($pid);
        $this->sendSystemMessage($pid, new ActorSystem\ProtoBuf\Stop());
    }

    public function poison(Ref $pid): void
    {
        $current = $this->stopping->get();
        if ($current === self::STOPPING) {
            return;
        }
        $this->stopping->cmpset($current, self::STOPPING);
        $this->actorSystem->root()->poisonFuture($this->router)->wait();
        $this->actorSystem->getProcessRegistry()->remove($pid);
        $this->sendSystemMessage($pid, new ActorSystem\ProtoBuf\Stop());
    }

    public function setState(StateInterface $state): void
    {
        $this->state = $state;
    }

    /**
     * @param Ref|null $parent
     * @return void
     */
    public function setParent(?Ref $parent): void
    {
        $this->parent = $parent;
    }

    public function setRouter(Ref $router): void
    {
        $this->router = $router;
    }

    public function getState(): StateInterface
    {
        return $this->state;
    }
}
