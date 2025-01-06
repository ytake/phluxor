<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Middleware\Trace;

use OpenTelemetry\Context\Propagation\PropagationSetterInterface;
use Phluxor\ActorSystem\Message\MessageHeader;

readonly class MessageHeaderWriter implements PropagationSetterInterface
{
    public function __construct(
        private MessageHeader $header
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function set(&$carrier, string $key, string $value): void
    {
        $this->header->set($key, $value);
        $carrier[$key] = $value;
    }
}
