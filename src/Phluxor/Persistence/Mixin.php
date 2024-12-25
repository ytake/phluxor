<?php

declare(strict_types=1);

namespace Phluxor\Persistence;

use Google\Protobuf\Internal\Message;
use Phluxor\ActorSystem\ActorContext;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Context\ReceiverInterface;
use Phluxor\ActorSystem\Context\ReceiverPartInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\Ref;
use Phluxor\Persistence\Message\OfferSnapshot;
use Phluxor\Persistence\Message\ReplayCompleted;
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
            $messageEnvelope = new MessageEnvelope(header: null, message: $result->getSnapshot());
            $this->receiver->receive($messageEnvelope);
            $offerSnapshot = new OfferSnapshot($result->getSnapshot());
            $this->receiveRecover($offerSnapshot);
        }
        $this->providerState->getEvents(
            $this->name(),
            $this->eventIndex,
            0,
            function (mixed $event) use ($receiver) {
                $messageEnvelope = new MessageEnvelope(header: null, message: $event);
                $this->receiver->receive($messageEnvelope);
                $this->receiveRecover($messageEnvelope->getMessage());
                $this->eventIndex++;
            }
        );
        $this->recovering = false;
        $messageEnvelope = new MessageEnvelope(header: null, message: new ReplayCompleted());
        $this->receiver->receive($messageEnvelope);
        $this->receiveRecover($messageEnvelope->getMessage());
    }

    public function persistenceReceive(Message $message): void
    {
        $this->providerState?->persistenceEvent($this->name(), $this->eventIndex, $message);
        if ($this->eventIndex % $this->providerState?->getSnapshotInterval() === 0) {
            $envelope = new MessageEnvelope(header: null, message: new RequestSnapshot());
            if($this->receiver instanceof ActorContext) {
                $sender = $this->receiver->sender();
                // if the sender is set in the context, do not rewrite the sender
                if ($sender instanceof Ref) {
                    $envelope = new MessageEnvelope(
                        header: null,
                        message: new RequestSnapshot(),
                        sender: $sender
                    );
                }
            };
            $this->receiver->receive($envelope);
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

    /**
     * @param mixed $message
     * @return void
     */
    public function receiveRecover(
        mixed $message
    ): void {
        // Implement this method
    }
}
