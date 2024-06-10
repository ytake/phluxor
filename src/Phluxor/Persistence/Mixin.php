<?php

declare(strict_types=1);

namespace Phluxor\Persistence;

use Google\Protobuf\Internal\Message;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Context\ReceiverInterface;
use Phluxor\ActorSystem\Context\ReceiverPartInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\Persistence\Message\ReplayComplete;
use Phluxor\Persistence\Message\RequestSnapshot;
use RuntimeException;

trait Mixin
{
    /** @var int */
    private int $eventIndex = 0;

    /** @var bool */
    private bool $recovering = true;

    /** @var string */
    private string $name = '';

    /** @var ReceiverPartInterface */
    private ReceiverPartInterface $receiver;

    /** @var ProviderStateInterface|null */
    private ProviderStateInterface|null $providerState = null;

    public function recovering(): bool
    {
        return $this->recovering;
    }

    /**
     * @param ProviderInterface $provider
     * @param ContextInterface|ReceiverInterface $context
     * @return void
     */
    public function init(
        ProviderInterface $provider,
        ContextInterface|ReceiverInterface $context
    ): void {
        if ($this->providerState == null) {
            $this->providerState = $provider->getState();
        }
        $receiver = $context;
        $name = $context->self()?->protobufPid()->getId() ?? '';
        if ($name === '') {
            throw new RuntimeException('Name is empty');
        }
        $this->name = $name;
        $this->providerState->restart();
        $this->receiver = $receiver;
        $result = $this->providerState->getSnapshot($this->name());
        if ($result->isOk()) {
            $this->eventIndex = $result->getEventIndex();
            $this->receiver->receive(new MessageEnvelope(header: null, message: $result->getSnapshot()));
        }
        $this->providerState->getEvents(
            $this->name(),
            $this->eventIndex,
            0,
            function (mixed $event) use ($receiver) {
                $this->receiver->receive(new MessageEnvelope(header: null, message: $event));
                $this->eventIndex++;
            }
        );
        $this->recovering = false;
        $this->receiver->receive(new MessageEnvelope(header: null, message: new ReplayComplete()));
    }

    public function persistenceReceive(Message $message): void
    {
        $this->providerState?->persistenceEvent($this->name(), $this->eventIndex, $message);
        if ($this->eventIndex % $this->providerState?->getSnapshotInterval() === 0) {
            $this->receiver->receive(
                new MessageEnvelope(header: null, message: new RequestSnapshot())
            );
        }
        $this->eventIndex++;
    }

    public function persistenceSnapshot(Message $snapshot): void
    {
        $this->providerState?->persistenceSnapshot($this->name(), $this->eventIndex, $snapshot);
    }

    public function name(): string
    {
        return $this->name;
    }
}
