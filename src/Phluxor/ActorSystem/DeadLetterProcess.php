<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\ProtoBuf\Stop;

readonly class DeadLetterProcess implements ProcessInterface
{
    /**
     * @param ActorSystem $actorSystem
     */
    public function __construct(
        private ActorSystem $actorSystem
    ) {
        $this->initialize();
    }

    public function sendUserMessage(?Pid $pid, mixed $message): void
    {
        $m = ActorSystem\Message\MessageEnvelope::wrapEnvelope($message);
        $this->actorSystem->getEventStream()?->publish(
            new DeadLetterEvent(
                $pid,
                $m->getMessage(),
                $m->getSender()
            )
        );
    }

    public function sendSystemMessage(Pid $pid, mixed $message): void
    {
        $this->actorSystem->getEventStream()?->publish(
            new DeadLetterEvent(
                $pid,
                $message,
                null
            )
        );
    }

    public function stop(Pid $pid): void
    {
        $this->sendSystemMessage($pid, new Stop());
    }

    private function initialize(): void
    {
        $throttle = new Throttle(
            $this->actorSystem->config()->deadLetterThrottleCount(),
            $this->actorSystem->config()->deadLetterThrottleInterval(),
            function (int $i) {
                $this->actorSystem->getLogger()->info("deadletter", ["throttled" => $i]);
            }
        );
        $this->actorSystem->getProcessRegistry()->add($this, "deadletter");
        $this->actorSystem->getEventStream()?->subscribe(function (mixed $message) use ($throttle) {
            if ($message instanceof DeadLetterEvent) {
                if (!$message->isNoSender()) {
                    $this->actorSystem->root()->send(
                        $message->getSender(),
                        new ActorSystem\ProtoBuf\DeadLetterResponse()
                    );
                }
                if ($this->actorSystem->config()->deadLetterRequestLogging() && !$message->isNoSender()) {
                    if ($throttle->shouldThrottle() == Valve::Open) {
                        $this->actorSystem->getLogger()->info(
                            "deadletter",
                            [
                                "message" => $message->getMessage(),
                                "sender" => $message->getSender(),
                                "pid" => $message->getPid()
                            ]
                        );
                    }
                }
            }
        });
        $this->actorSystem->getEventStream()?->subscribe(function (mixed $message) {
            if ($message instanceof DeadLetterEvent) {
                $m = $message->getMessage();
                if ($m instanceof ActorSystem\ProtoBuf\Watch) {
                    if ($m->getWatcher() != null) {
                        $pid = new Pid($m->getWatcher());
                        $pid->sendSystemMessage(
                            $this->actorSystem,
                            new ActorSystem\ProtoBuf\Terminated([
                                "who" => $message->getPid(),
                                'why'  => ActorSystem\ProtoBuf\TerminatedReason::NotFound
                            ])
                        );
                    }
                }
            }
        });
    }
}
