<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use DateInterval;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Message\ActorInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\Value\ContextExtensionId;
use Psr\Log\LoggerInterface;
use Swoole\Atomic\Long;
use Throwable;

use function sprintf;

class ActorContext implements
    ActorSystem\Context\ContextInterface,
    ActorSystem\Context\SenderInterface,
    ActorSystem\Mailbox\MessageInvokerInterface,
    ActorSystem\Context\SpawnerInterface,
    SupervisorInterface
{
    private const int stateAlive = 0;
    private const int stateRestarting = 1;
    private const int stateStopping = 2;
    private const int stateStopped = 3;

    /** @var ActorContextExtras|null */
    private ActorContextExtras|null $extras = null;

    /** @var Long */
    private readonly Long $state;

    /**
     * @param ActorSystem $actorSystem
     * @param Props $props
     * @param Ref|null $parent
     * @param ActorInterface|null $actor
     * @param Ref|null $self
     * @param DateInterval $receiveTimeout
     * @param mixed|null $messageOrEnvelope
     */
    public function __construct(
        private readonly ActorSystem $actorSystem,
        private readonly Props $props,
        private readonly Ref|null $parent = null,
        private ActorInterface|null $actor = null,
        private Ref|null $self = null,
        private DateInterval $receiveTimeout = new DateInterval('PT0S'),
        private mixed $messageOrEnvelope = null,
    ) {
        $this->state = new Long(self::stateAlive);
        $this->incarnateActor();
    }

    public function ensureExtras(): ActorContextExtras
    {
        if ($this->extras == null) {
            $ctxd = $this;
            if ($this->props != null && $this->props->contextDecoratorChain() != null) {
                $c = $this->props->contextDecoratorChain();
                $ctxd = $c($this);
            }
            $this->extras = new ActorContextExtras($ctxd);
        }
        return $this->extras;
    }

    /**
     * @return ActorSystem
     */
    public function actorSystem(): ActorSystem
    {
        return $this->actorSystem;
    }

    public function logger(): LoggerInterface
    {
        return $this->actorSystem()->getLogger();
    }

    public function parent(): Ref|null
    {
        return $this->parent;
    }

    public function self(): Ref|null
    {
        return $this->self;
    }

    /**
     * @param Ref $pid
     * @return void
     */
    public function setSelf(Ref $pid): void
    {
        $this->self = $pid;
    }

    public function sender(): Ref|null
    {
        return MessageEnvelope::unwrapEnvelopeSender($this->messageOrEnvelope);
    }

    public function actor(): ActorInterface
    {
        if ($this->actor == null) {
            throw new ActorSystem\Exception\ActorErrorException(
                "should not call actor before incarnateActor()"
            );
        }
        return $this->actor;
    }

    public function receiveTimeout(): DateInterval
    {
        return $this->receiveTimeout;
    }

    /**
     * @return Ref[]
     */
    public function children(): array
    {
        if ($this->extras == null) {
            return [];
        }
        return $this->extras->childrenValues();
    }

    /**
     * @param mixed $response
     * @return void
     */
    public function respond(mixed $response): void
    {
        if ($this->sender() == null) {
            $this->actorSystem->getDeadLetter()->sendUserMessage(null, $response);
            return;
        }
        $this->send($this->sender(), $response);
    }

    public function stash(): void
    {
        // TODO: Implement stash() method.
    }

    /**
     * @param Ref $pid
     * @return void
     */
    public function watch(Ref $pid): void
    {
        $pid->sendSystemMessage(
            $this->actorSystem,
            new ActorSystem\ProtoBuf\Watch(['watcher' => $this->self()?->protobufPid()])
        );
    }

    public function unwatch(Ref $pid): void
    {
        $pid->sendSystemMessage(
            $this->actorSystem,
            new ActorSystem\ProtoBuf\Unwatch(['watcher' => $this->self()?->protobufPid()])
        );
    }

    public function setReceiveTimeout(DateInterval $dateInterval): void
    {
        if ($dateInterval->s < 1) {
            $dateInterval = new DateInterval('PT0S');
        }
        if ($dateInterval->s == $this->receiveTimeout->s) {
            return;
        }
        $this->receiveTimeout = $dateInterval;
        $this->ensureExtras();
        if ($this->extras != null) {
            $this->extras->stopReceiveTimeoutTimer();
            if ($this->receiveTimeout->s > 0) {
                if ($this->extras->receiveTimeoutTimer() == null) {
                    $this->extras->initReceiveTimeoutTimer($dateInterval->s, function () {
                        $this->receiveTimeoutHandler();
                    });
                } else {
                    $this->extras->resetReceiveTimeoutTimer($dateInterval->s);
                }
            }
        }
    }

    public function cancelReceiveTimeout(): void
    {
        if ($this->extras == null || $this->ensureExtras()->receiveTimeoutTimer() == null) {
            return;
        }
        $this->ensureExtras()->killReceiveTimeoutTimer();
        $this->receiveTimeout = new DateInterval('PT0S');
    }

    public function receiveTimeoutHandler(): void
    {
        if ($this->extras != null && $this->extras->receiveTimeoutTimer() != null) {
            $this->cancelReceiveTimeout();
            $this->send($this->self, new ActorSystem\Message\ReceiveTimeout());
        }
    }

    /**
     * @param Ref $pid
     * @return void
     */
    public function forward(Ref $pid): void
    {
        if ($this->messageOrEnvelope instanceof ActorSystem\Message\SystemMessageInterface) {
            $this->logger()->error(
                "SystemMessage cannot be forwarded",
                ['message' => $this->messageOrEnvelope]
            );
            return;
        }
        $this->sendUserMessage($pid, $this->messageOrEnvelope);
    }

    public function reenterAfter(Future $future, ReenterAfterInterface $reenterAfter): void
    {
        $r = $future->result();
        $wrapper = fn() => $reenterAfter($r->value(), $r->error());
        $message = $this->messageOrEnvelope;
        if ($this->self == null) {
            throw new ActorSystem\Exception\ActorReferenceErrorException("self is null");
        }
        $future->continueWith(function (FutureResult $result) use ($wrapper, $message) {
            $this->self?->sendSystemMessage(
                $this->actorSystem,
                new ActorSystem\Message\Continuation(
                    message: $message,
                    function: $wrapper
                )
            );
        });
    }

    public function message(): mixed
    {
        return MessageEnvelope::unwrapEnvelopeMessage($this->messageOrEnvelope);
    }

    public function messageHeader(): ReadonlyMessageHeaderInterface
    {
        return MessageEnvelope::unwrapEnvelopeHeader($this->messageOrEnvelope);
    }

    public function send(?Ref $pid, mixed $message): void
    {
        $this->sendUserMessage($pid, $message);
    }

    public function sendUserMessage(?Ref $pid, mixed $message): void
    {
        if ($this->props->senderMiddlewareChain() != null) {
            $chain = $this->props->senderMiddlewareChain();
            $chain($this->ensureExtras()->context(), $pid, MessageEnvelope::wrapEnvelope($message));
        } else {
            if ($pid != null) {
                $pid->sendUserMessage($this->actorSystem, $message);
            } else {
                $this->actorSystem->getDeadLetter()->sendUserMessage($pid, $message);
            }
        }
    }

    /**
     * tell & ask
     * @param Ref|null $pid
     * @param mixed $message
     * @return void
     */
    public function request(?Ref $pid, mixed $message): void
    {
        $this->sendUserMessage($pid, new MessageEnvelope(null, $message, $this->self()));
    }

    /**
     * specify sender pid / actor
     * @param Ref|null $pid
     * @param mixed $message
     * @param Ref|null $sender
     * @return void
     */
    public function requestWithCustomSender(?Ref $pid, mixed $message, ?Ref $sender): void
    {
        $this->sendUserMessage($pid, new MessageEnvelope(null, $message, $sender));
    }

    public function requestFuture(?Ref $pid, mixed $message, int $duration): Future
    {
        $future = Future::create($this->actorSystem, $duration);
        if ($future->pid() == null) {
            $this->logger()->error("request future: pid is null");
            return $future;
        }
        $this->sendUserMessage($pid, new MessageEnvelope(null, $message, $future->pid()));
        return $future;
    }

    public function receive(?MessageEnvelope $envelope): void
    {
        $this->messageOrEnvelope = $envelope;
        $this->defaultReceive();
        // release message
        $this->messageOrEnvelope = null;
    }

    public function defaultReceive(): void
    {
        if ($this->actor == null) {
            throw new ActorSystem\Exception\ActorErrorException("actor is null");
        }
        $msg = $this->message();
        switch (true) {
            case $msg instanceof ActorSystem\ProtoBuf\PoisonPill:
                $this->stop($this->self);
                break;
            case $msg instanceof AutoRespondInterface:
                if ($this->props->contextDecoratorChain() != null) {
                    $this->actor->receive($this->ensureExtras()->context());
                } else {
                    $this->actor->receive($this);
                }
                $this->respond($msg->getAutoResponse($this));
                break;
            default:
                if ($this->props->contextDecoratorChain() != null) {
                    $this->actor->receive($this->ensureExtras()->context());
                    return;
                }
                $this->actor->receive($this);
        }
    }

    /**
     * @param Props $props
     * @return Ref|null
     */
    public function spawn(Props $props): Ref|null
    {
        $result = $this->spawnNamed($props, $this->actorSystem->getProcessRegistry()->nextId());
        if ($result->isError() != null) {
            throw $result->isError();
        }
        return $result->getRef();
    }

    public function spawnPrefix(Props $props, string $prefix): Ref|null
    {
        $result = $this->spawnNamed($props, $prefix . $this->actorSystem->getProcessRegistry()->nextId());
        if ($result->isError() != null) {
            throw $result->isError();
        }
        return $result->getRef();
    }

    public function spawnNamed(Props $props, string $name): SpawnResult
    {
        if ($props->getGuardianStrategy() != null) {
            throw new \RuntimeException("props used to spawn child cannot have GuardianStrategy");
        }
        $id = "";
        if ($this->self != null) {
            $id = $this->self->protobufPid()->getId();
        }
        if ($this->props->spawnMiddlewareChain() != null) {
            $r = $this->props->spawnMiddlewareChain()($this->actorSystem, sprintf("%s/%s", $id, $name), $props, $this);
        } else {
            $r = $props->spawn($this->actorSystem, sprintf("%s/%s", $id, $name), $this);
        }
        if ($r->isError() instanceof ActorSystem\Exception\SpawnErrorException) {
            throw $r->isError();
        }
        if ($r->getRef() == null) {
            throw new ActorSystem\Exception\SpawnErrorException("spawned child pid is null");
        }
        $this->ensureExtras()->addChild($r->getRef());
        return $r;
    }

    /**
     * stop will stop actor immediately regardless of existing user messages in mailbox.
     * @param Ref|null $pid
     * @return void
     */
    public function stop(?Ref $pid): void
    {
        if ($pid == null) {
            return;
        }
        $pid->ref($this->actorSystem)?->stop($pid);
    }

    /**
     * stopFuture will stop actor immediately regardless of existing user messages in mailbox, and return its future.
     * @param Ref|null $pid
     * @return Future|null
     */
    public function stopFuture(?Ref $pid): Future|null
    {
        if ($pid == null) {
            return null;
        }
        $future = Future::create($this->actorSystem, 10);
        if ($future->pid() == null) {
            $this->logger()->error("stop future: pid is null");
            return null;
        }
        $pid->sendSystemMessage(
            $this->actorSystem,
            new ActorSystem\ProtoBuf\Watch([
                'watcher' => $future->pid()->protobufPid()
            ])
        );
        $this->stop($pid);
        return $future;
    }

    public function poison(?Ref $pid): void
    {
        if ($pid == null) {
            $this->logger()->error("poison pid is null");
            return;
        }
        $pid->sendUserMessage(
            $this->actorSystem,
            new ActorSystem\ProtoBuf\PoisonPill()
        );
    }

    public function poisonFuture(?Ref $pid): Future|null
    {
        if ($pid == null) {
            return null;
        }
        $future = Future::create($this->actorSystem, 10);
        if ($future->pid() == null) {
            $this->logger()->error("poison future: pid is null");
            return null;
        }
        $pid->sendSystemMessage(
            $this->actorSystem,
            new ActorSystem\ProtoBuf\Watch([
                'watcher' => $future->pid()->protobufPid()
            ])
        );
        $this->poison($pid);
        return $future;
    }

    public function invokeUserMessage(mixed $message): void
    {
        if ($this->state->get() === self::stateStopped) {
            return;
        }
        $influenceTimeout = true;
        if ($this->receiveTimeout->s > 0) {
            if ($message instanceof ActorSystem\Message\NotInfluenceReceiveTimeoutInterface) {
                $influenceTimeout = false;
            } else {
                $this->ensureExtras()->stopReceiveTimeoutTimer();
            }
        }
        $this->processMessage($message);
        if ($this->receiveTimeout->s > 0 && $influenceTimeout) {
            $this->ensureExtras()->resetReceiveTimeoutTimer($this->receiveTimeout->s);
        }
    }

    private function processMessage(mixed $message): void
    {
        $receiverMiddlewareChain = $this->props->getReceiverMiddlewareChain();
        if ($receiverMiddlewareChain != null) {
            $receiverMiddlewareChain(
                $this->ensureExtras()->context(),
                MessageEnvelope::wrapEnvelope($message)
            );
            return;
        }
        $contextDecoratorChain = $this->props->contextDecoratorChain();
        if ($contextDecoratorChain != null) {
            $this->ensureExtras()->context()->receive(MessageEnvelope::wrapEnvelope($message));
            return;
        }

        $this->messageOrEnvelope = $message;
        $this->defaultReceive();
        // release message
        $this->messageOrEnvelope = null;
    }

    public function incarnateActor(): void
    {
        $this->state->set(self::stateAlive);
        $this->actor = $this->props->producer($this->actorSystem);
        // open telemetry
    }

    public function invokeSystemMessage(mixed $message): void
    {
        $msg = $message;
        if ($message instanceof QueueResult) {
            $msg = $message->value();
        }
        switch (true) {
            case $msg instanceof ActorSystem\Message\Continuation:
                $this->messageOrEnvelope = $msg->getMessage();
                $msg->getFunction()();
                $this->messageOrEnvelope = null;
                break;
            case $msg instanceof ActorSystem\Message\Started:
                $this->invokeUserMessage($msg);
                break;
            case $msg instanceof ActorSystem\ProtoBuf\Watch:
                $this->handleWatch($msg);
                break;
            case $msg instanceof ActorSystem\ProtoBuf\Unwatch:
                $this->handleUnwatch($msg);
                break;
            case $msg instanceof ActorSystem\ProtoBuf\Stop:
                $this->handleStop();
                break;
            case $msg instanceof ActorSystem\ProtoBuf\Terminated:
                $this->handleTerminated($msg);
                break;
            case $msg instanceof ActorSystem\Message\Failure:
                $this->handleFailure($msg);
                break;
            case $msg instanceof ActorSystem\Message\Restart:
                $this->handleRestart();
                break;
            default:
                $this->logger()->error("unknown system message", ['message' => $msg]);
        }
    }

    /**
     * @param Message\Failure|null $failure
     * @return void
     */
    private function handleRootFailure(ActorSystem\Message\Failure|null $failure): void
    {
        if ($failure == null) {
            return;
        }
        $strategy = new ActorSystem\Strategy\OneForOneStrategy(
            maxNrOfRetries: 10,
            withinDuration: new DateInterval('PT10S'),
            decider: new ActorSystem\Supervision\DefaultDecider()
        );
        $strategy->handleFailure(
            $this->actorSystem,
            $this,
            $failure->getWho(),
            new ActorSystem\Child\RestartStatistics(),
            $failure->getReason(),
            $failure->getMessage()
        );
    }

    //
    private function handleWatch(ActorSystem\ProtoBuf\Watch $msg): void
    {
        $watcher = $msg->getWatcher();
        if ($watcher != null) {
            if ($this->state->get() >= self::stateStopping) {
                (new Ref($watcher))->sendSystemMessage(
                    $this->actorSystem,
                    new ActorSystem\ProtoBuf\Terminated([
                        'who' => $this->self?->protobufPid()
                    ])
                );
            } else {
                $this->ensureExtras()->watch(new Ref($watcher));
            }
        }
    }

    private function handleUnwatch(ActorSystem\ProtoBuf\Unwatch $msg): void
    {
        if ($this->extras == null) {
            return;
        }
        $watcher = $msg->getWatcher();
        if ($watcher != null) {
            $this->extras->unwatch(new Ref($watcher));
        }
    }

    private function handleRestart(): void
    {
        $this->state->set(self::stateRestarting);
        $this->invokeUserMessage(new ActorSystem\Message\Restarting());
        $this->stopAllChildren();
        $this->tryRestartOrTerminate();
        // add metrics
    }

    private function handleStop(): void
    {
        if ($this->state->get() >= self::stateStopping) {
            return;
        }
        $this->state->set(self::stateStopping);
        try {
            $this->invokeUserMessage(new ActorSystem\Message\Stopping());
        } catch (Throwable $e) {
            // finalizing throwables / logging
            $this->logger()->error("stopping error", ['exception' => $e->getTraceAsString()]);
        }
        $this->stopAllChildren();
        $this->tryRestartOrTerminate();
    }

    private function handleTerminated(ActorSystem\ProtoBuf\Terminated $msg): void
    {
        if ($this->extras != null) {
            $who = $msg->getWho();
            if ($who != null) {
                $this->extras->removeChild(new Ref($who));
            }
        }
        $this->invokeUserMessage($msg);
        $this->tryRestartOrTerminate();
    }

    /**
     * offload the supervision completely to the supervisor strategy.
     * @param ActorSystem\Message\Failure $msg
     * @return void
     */
    private function handleFailure(ActorSystem\Message\Failure $msg): void
    {
        $actor = $this->actor;
        if ($actor instanceof SupervisorStrategyInterface) {
            $actor->handleFailure(
                $this->actorSystem,
                $this,
                $msg->getWho(),
                $msg->getRestartStatistics(),
                $msg->getReason(),
                $msg->getMessage()
            );
            return;
        }
        $this->props->getSupervisorStrategy()->handleFailure(
            $this->actorSystem,
            $this,
            $msg->getWho(),
            $msg->getRestartStatistics(),
            $msg->getReason(),
            $msg->getMessage()
        );
    }

    private function stopAllChildren(): void
    {
        if ($this->extras == null) {
            return;
        }
        $pids = $this->extras->childrenValues();
        for ($i = count($pids) - 1; $i >= 0; $i--) {
            $pids[$i]->sendSystemMessage(
                $this->actorSystem,
                new ActorSystem\ProtoBuf\Stop()
            );
        }
    }

    private function tryRestartOrTerminate(): void
    {
        switch ($this->state->get()) {
            case self::stateRestarting:
                $this->cancelReceiveTimeout();
                $this->restart();
                break;
            case self::stateStopping:
                $this->cancelReceiveTimeout();
                $this->finalizeStop();
                break;
        }
    }

    /**
     * TODO: implement stash
     * @return void
     */
    private function restart(): void
    {
        $this->incarnateActor();
        $this->self?->sendSystemMessage($this->actorSystem, new ActorSystem\Message\ResumeMailbox());
        $this->invokeUserMessage(new ActorSystem\Message\Started());
        // TODO stash implementation
        // if ($this->extras != null) {
        //
        // }
    }

    private function finalizeStop(): void
    {
        if ($this->self != null) {
            $this->actorSystem->getProcessRegistry()->remove($this->self);
        }
        $this->invokeUserMessage(new ActorSystem\Message\Stopped());

        $otherStopped = new ActorSystem\ProtoBuf\Terminated([
            'who' => $this->self?->protobufPid()
        ]);
        $this->extras?->watchers()->forEach(
            function (int $index, Ref $pid) use ($otherStopped) {
                $pid->sendSystemMessage(
                    $this->actorSystem,
                    $otherStopped
                );
            }
        );
        //
        $this->parent?->sendSystemMessage(
            $this->actorSystem,
            $otherStopped
        );
        $this->state->set(self::stateStopped);
    }

    public function escalateFailure(mixed $reason, mixed $message): void
    {
        $reports = ['self' => $this->self, 'reason' => $reason];
        if ($reason instanceof Throwable) {
            $reports = ['self' => $this->self, 'reason' => $reason, 'stack' => $reason->getTraceAsString()];
        }
        $this->logger()->info("Recovering", $reports);
        if ($this->actorSystem->config()->developerSupervisionLogging()) {
            $this->logger()->error(
                "Supervision",
                ['actor' => $this->self, 'message' => $message, 'exception' => $reason]
            );
        }
        if ($this->self == null) {
            return;
        }
        $failure = new ActorSystem\Message\Failure(
            who: $this->self,
            reason: $reason,
            restartStatistics: $this->ensureExtras()->restartStats(),
            message: $message
        );
        $this->self->sendSystemMessage($this->actorSystem, new ActorSystem\Message\SuspendMailbox());
        if ($this->parent == null) {
            $this->handleRootFailure($failure);
        } else {
            $this->parent->sendSystemMessage($this->actorSystem, $failure);
        }
    }

    public function restartChildren(Ref ...$pids): void
    {
        foreach ($pids as $pid) {
            $pid->sendSystemMessage($this->actorSystem, new ActorSystem\Message\Restart());
        }
    }

    public function stopChildren(Ref ...$pids): void
    {
        foreach ($pids as $pid) {
            $pid->sendSystemMessage($this->actorSystem, new ActorSystem\ProtoBuf\Stop());
        }
    }

    public function resumeChildren(Ref ...$pids): void
    {
        foreach ($pids as $pid) {
            $pid->sendSystemMessage($this->actorSystem, new ActorSystem\Message\ResumeMailbox());
        }
    }

    public function get(ContextExtensionId $id): ContextExtensionId
    {
        return $this->ensureExtras()->extensions()->get($id);
    }

    public function set(ContextExtensionId $id): void
    {
        $this->ensureExtras()->extensions()->set($id);
    }

    public function __toString(): string
    {
        if ($this->self == null) {
            return "";
        }
        return $this->self->protobufPid()->serializeToString();
    }
}
