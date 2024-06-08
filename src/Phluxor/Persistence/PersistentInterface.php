<?php

declare(strict_types=1);

namespace Phluxor\Persistence;

use Google\Protobuf\Internal\Message;
use Phluxor\ActorSystem\Context\ContextInterface;

interface PersistentInterface
{
    /**
     * @param ProviderInterface $provider
     * @param ContextInterface $context
     * @return void
     */
    public function init(ProviderInterface $provider, ContextInterface $context): void;

    /**
     * @param Message $message
     * @return void
     */
    public function persistenceReceive(Message $message): void;

    /**
     * @param Message $snapshot
     * @return void
     */
    public function persistenceSnapshot(Message $snapshot): void;

    public function recovering(): bool;

    public function name(): string;
}
