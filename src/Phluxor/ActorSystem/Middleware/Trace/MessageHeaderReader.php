<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Middleware\Trace;

use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use Phluxor\ActorSystem\Message\MessageHeader;

readonly class MessageHeaderReader implements PropagationGetterInterface
{
    public function __construct(
        private MessageHeader $header
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function keys($carrier): array
    {
        return $this->header->keys();
    }

    /**
     * {@inheritdoc}
     */
    public function get($carrier, string $key): ?string
    {
        $value = $carrier[$key] ??= null;
        return $this->header->get($key) ?? $value;
    }
}
