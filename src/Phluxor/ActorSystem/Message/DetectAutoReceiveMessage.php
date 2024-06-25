<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Message;

use Phluxor\ActorSystem\ProtoBuf\PoisonPill;

final class DetectAutoReceiveMessage implements DetectMessageInterface
{
    private array $expects = [
        Restarting::class,
        Stopping::class,
        Stopped::class,
        PoisonPill::class,
    ];

    public function __construct(
        private readonly mixed $message
    ) {
    }

    public function isMatch(): bool
    {
        foreach ($this->expects as $message) {
            if ($this->message instanceof $message) {
                return true;
            }
        }
        return false;
    }
}
