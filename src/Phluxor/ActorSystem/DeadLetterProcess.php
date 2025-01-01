<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\ProtoBuf\Stop;
use Phluxor\Metrics\ActorMetrics;
use Phluxor\Metrics\PhluxorMetrics;

readonly class DeadLetterProcess implements ProcessInterface
{
    use ActorSystem\Metrics\MetricsSystemTrait;

    /**
     * @param ActorSystem $actorSystem
     */
    public function __construct(
        private ActorSystem $actorSystem
    ) {
        $this->initialize();
    }

    public function sendUserMessage(?Ref $pid, mixed $message): void
    {
        $metricsSystem = $this->enabledMetricsSystem($this->actorSystem);
        if ($metricsSystem) {
            $instruments = $metricsSystem->metrics()->find(PhluxorMetrics::INTERNAL_ACTOR_METRICS);
            if ($instruments instanceof ActorMetrics) {
                $instruments->getDeadLetterCounter()
                    ->add(
                        1,
                        Attributes::create([
                            'address' => $this->actorSystem->address(),
                            'messagetype' => get_debug_type($message),
                        ])
                    );
            }
        }
        $m = ActorSystem\Message\MessageEnvelope::wrapEnvelope($message);
        $this->actorSystem->getEventStream()?->publish(
            new DeadLetterEvent(
                $pid,
                $m->getMessage(),
                $m->getSender()
            )
        );
    }

    public function sendSystemMessage(Ref $pid, mixed $message): void
    {
        $this->actorSystem->getEventStream()?->publish(
            new DeadLetterEvent(
                $pid,
                $message,
                null
            )
        );
    }

    public function stop(Ref $pid): void
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
                        $message->sender,
                        new ActorSystem\ProtoBuf\DeadLetterResponse()
                    );
                }
                if ($this->actorSystem->config()->deadLetterRequestLogging() && $message->isNoSender()) {
                    if ($throttle->shouldThrottle() == Valve::Open) {
                        $this->actorSystem->getLogger()->info(
                            "deadletter",
                            [
                                "message" => $message->message,
                                "sender" => (string) $message->sender,
                                "pid" => (string) $message->ref
                            ]
                        );
                    }
                }
            }
        });
        $this->actorSystem->getEventStream()?->subscribe(function (mixed $message) {
            if ($message instanceof DeadLetterEvent) {
                $m = $message->message;
                if ($m instanceof ActorSystem\ProtoBuf\Watch) {
                    if ($m->getWatcher() != null) {
                        $pid = new Ref($m->getWatcher());
                        $pid->sendSystemMessage(
                            $this->actorSystem,
                            new ActorSystem\ProtoBuf\Terminated([
                                "who" => $message->ref,
                                'why' => ActorSystem\ProtoBuf\TerminatedReason::NotFound
                            ])
                        );
                    }
                }
            }
        });
    }
}
